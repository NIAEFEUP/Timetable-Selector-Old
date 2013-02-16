function Aula(jsonobj){
	this.dia=Number(jsonobj.dia)+1;
	this.horarow=(Number(jsonobj.hora)-8)*2+1;
	this.cadeira=jsonobj.sigla;
	this.cadeiran=jsonobj.nome;
	this.tipo=jsonobj.tipo;
	this.turma=jsonobj.turma;
	this.turmac=jsonobj.turmac;
	this.duracao=Number(jsonobj.duracao);
	this.sala=jsonobj.sala;
	this.prof=jsonobj.prof;
	this.profsig=jsonobj.profsig;
	
	this.txtdia=''+this.dia+'ª';
	this.txthora=''+((this.horarow+this.horarow%2)/2+7)+':'+(((this.horarow-1)%2)*3)+'0';
	if (this.tipo=="T") this.tipoh="teorica"; 
	if (this.tipo=="TP") this.tipoh="teoricopratica";
	if (this.tipo=="L") this.tipoh="laboratorio";
	if (this.tipo=="P") this.tipoh="pratica";
	
	this.stleft=100*this.dia-67;
	this.sttop=23+23*this.horarow;
	this.stheight=22*this.duracao-4;
	
}

function Cadeira(sigla,jsonobj){
	this.showteoricas=true;
	this.teoricas=new Array();
	this.praticas=new Array();
	this.nome=sigla;
	this.nomec=jsonobj.nome;
	this.turmaselect="-";
	if (typeof jsonobj.T!="undefined"){
	for (var i=0;i<jsonobj.T.length;i++)
		this.teoricas.push(new Aula(jsonobj.T[i]));}
	if (typeof jsonobj.P!="undefined"){
	for (var i=0;i<jsonobj.P.length;i++)
		this.praticas.push(new Aula(jsonobj.P[i]));}
	if (typeof jsonobj.TP!="undefined"){
	for (var i=0;i<jsonobj.TP.length;i++)
		this.praticas.push(new Aula(jsonobj.TP[i]));}
	if (typeof jsonobj.L!="undefined"){
	for (var i=0;i<jsonobj.L.length;i++)
		this.praticas.push(new Aula(jsonobj.L[i]));}
	
}
Cadeira.prototype.selectorhtml=function(){
	var str='';
	str+='<div class=classselector>';
	str+='<p>'+this.nomec+' ('+this.nome+')'+'</p>';
	str+='<select class="turmaselect" data-cadeira="'+this.nome+'">';
	str+='<option value="-">-----</option>';
	for (var i=0;i<this.praticas.length;i++)
	{
		str+='<option value="'+this.praticas[i].turma+'">'+this.praticas[i].turma+' - '+this.praticas[i].profsig+' '+this.praticas[i].txtdia+' '+this.praticas[i].txthora+'</option>';
	}
	if (this.praticas.length==0) str+='<option value="teoricas">só teoricas</option>'
	str+='</select>';
	str+='<label><input class="mostrarteoricas" value="'+this.nome+'" type="checkbox" data-cadeira="'+this.nome+'" checked/>Mostrar Teoricas</label>'
	str+='</div>';
	return str;
}
Cadeira.prototype.showTurma=function(){
	$('.aula[data-cadeira="'+this.nome+'"]').remove();
	var turmaselect=this.turmaselect;
	if(turmaselect!="-"){
		if(this.showteoricas)
		{
			$.each(this.teoricas,function(i,aula){
				if(aula.turma==turmaselect||turmaselect=="teoricas")
				{
					$('#horariotable').append(aula.horariohtml());
				}
			});
		}
		$.each(this.praticas,function(i,aula){
			if(aula.turma==turmaselect)
			{
				$('#horariotable').append(aula.horariohtml());
			}
		});
	}
}
Aula.prototype.horariohtml=function(){
	var str='';
	str+='<div class="'+this.tipoh+' aula" data-cadeira="'+this.cadeira+'" style="left:'+this.stleft+';top:'+this.sttop+';height:'+this.stheight+';"><div class="aulawrapper">';
	str+='<p class="aula"><span class="aula"><abbr title="'+this.cadeiran+'">'+this.cadeira+'</abbr></span><span class="aula"><abbr title="'+this.prof+'">'+this.profsig+'</abbr></span></p>';
	str+='<p class="aula"><span class="aula">'+this.sala+'</span><span class="aula">'+this.turmac+'</span></p>';
	str+='</div></div>';
	return str;
}


var cadeiras;

$(document).ready(function(){
	$.blockUI.defaults.css = {};
	$.blockUI({ message: $('#promptcurso') }); 

	
	$('#cursook').click(function(){
		$.blockUI({message:$('#loading')});
		
		var curso=$('#cursoselect option:selected').val();
		var ano_lectivo=$('#anoselect option:selected').val();
		var periodo=$('#semestreselect option:selected').val();
		$.getJSON("/~ei09082/TTC/"+curso+ano_lectivo+periodo+".json",function(data){
			parse_horario(data);
			$.blockUI({message:$('#promptcadeiras')});
		});
	});
	
	$('#cadeirasok').click(function(){
		addCadeiras();
		$.unblockUI();
	});
	
	$(document).on('change','.classselector select.turmaselect',function(event){
		cadeiras[this.getAttribute('data-cadeira')].turmaselect=this.options[this.selectedIndex].value;
		cadeiras[this.getAttribute('data-cadeira')].showTurma();
	});
	
	$(document).on('change','.classselector input.mostrarteoricas',function(event){
		cadeiras[this.getAttribute('data-cadeira')].showteoricas=this.checked;
		cadeiras[this.getAttribute('data-cadeira')].showTurma();
	});
	
});

//fazer parse do json, colocar as cadeiras no vector
function parse_horario(data){
	cadeiras={};
	$.each(data,function(cadeira,obj){
		cadeiras[cadeira]=new Cadeira(cadeira,obj);
		$('#listcadeiras').append('<span class="listcad"><input class="listcad" value="'+cadeira+'" type="checkbox"/><abbr title="'+obj.nome+'">'+cadeira+'</abbr></span>');
		
	});

}

function addCadeiras(){
	$('input.listcad:checked').each(function(index,element){
		$('#selectorsdiv').append(cadeiras[$(element).val()].selectorhtml());
	});
}

