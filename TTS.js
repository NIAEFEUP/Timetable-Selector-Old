// Campos do horário
var diasSemana=new Array("Segunda","Terça","Quarta","Quinta","Sexta","Sábado");
var diasSemanaMin=new Array("seg","ter","qua","qui","sex","sab");
var horas=new Array(
	"8:00 - 8:30","8:30 - 9:00","9:00 - 9:30","9:30 - 10:00","10:00 - 10:30","10:30 - 11:00",
	"11:00 - 11:30","11:30 - 12:00","12:00 - 12:30","12:30 - 13:00","13:00 - 13:30","13:30 - 14:00",
	"14:00 - 14:30","14:30 - 15:00","15:00 - 15:30","15:30 - 16:00","16:00 - 16:30","16:30 - 17:00",
	"17:00 - 17:30","17:30 - 18:00","18:00 - 18:30","18:30 - 19:00","19:00 - 19:30","19:30 - 20:00",
	"20:00 - 20:30");
var horasMin=new Array(
	"8","8-5","9","9-5","10","10-5","11","11-5","12","12-5","13","13-5","14","14-5","15","15-5",
	"16","16-5","17","17-5","18","18-5","19","19-5","20","20-5");

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
	this.vagas="?";
	this.repetido=false;
	this.txtdia=''+this.dia+'ª';
	this.txthora=''+((this.horarow+this.horarow%2)/2+7)+':'+(((this.horarow-1)%2)*3)+'0';
	if (this.tipo=="T") this.tipoh="teorica"; 
	if (this.tipo=="TP") this.tipoh="teoricopratica";
	if (this.tipo=="L") this.tipoh="laboratorio";
	if (this.tipo=="P") this.tipoh="pratica";
	if (this.tipo=="PL") this.tipoh="praticalaboratorial";
	if (this.tipo=="OT") this.tipoh="orientacaotutorial";
	
	/*this.stleft=100*this.dia-68;
	this.sttop=23+23*this.horarow;
	this.stheight=23*this.duracao-9;*/
	var diaString=diasSemanaMin[this.dia-1];
	var horaString=horasMin[this.horarow-1];
	var bloco=$("#horario"+diaString+horaString);
	this.stleft=bloco.offset().left-1;
	this.sttop=bloco.offset().top-1;
	this.stwidth=bloco.outerWidth()-1;
	this.stheight=bloco.outerHeight()*this.duracao-1;
	
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
	if (typeof jsonobj.PL!="undefined"){
	for (var i=0;i<jsonobj.PL.length;i++)
		this.praticas.push(new Aula(jsonobj.PL[i]));}
		if (typeof jsonobj.PL!="undefined"){
	for (var i=0;i<jsonobj.OT.length;i++)
		this.praticas.push(new Aula(jsonobj.OT[i]));}
	
}
Cadeira.prototype.selectorhtml=function(){
	var str='';
	str+='<div class=classselector>';
	//str+='<p>'+this.nomec+' ('+this.nome+')'+'</p>';
	str+=this.nomec+' ('+this.nome+')';
	str+='<select class="turmaselect" data-cadeira="'+this.nome+'">';
	str+='<option value="-">-----</option>';
	for (var i=0;i<this.praticas.length;i++)
	{
		if (!this.praticas[i].repetido){
			str+='<option value="'+this.praticas[i].turma+'">'+this.praticas[i].selecttext()
			for (var j=i+1;j<this.praticas.length;j++)
			{
				if (this.praticas[j].turma==this.praticas[i].turma)
				{
					this.praticas[j].repetido=true;
					str+=this.praticas[j].selecttextrepetida();
				}
			}
			str+=' ('+this.praticas[i].vagas+')</option>';
		}
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
					$('#content').append(aula.horariohtml());
				}
			});
		}
		$.each(this.praticas,function(i,aula){
			if(aula.turma==turmaselect)
			{
				$('#content').append(aula.horariohtml());
			}
		});
	}
}
Aula.prototype.horariohtml=function(){
	var str='';
	str+='<div class="'+this.tipoh+' aula" data-cadeira="'+this.cadeira+'" style="left:'+this.stleft+'px;top:'+this.sttop+'px;height:'+this.stheight+'px;width:'+this.stwidth+'px;"><div class="aulawrapper">';
	str+='<p class="aula"><span class="aula"><abbr title="'+this.cadeiran+'">'+this.cadeira+'</abbr></span><span class="aula"><abbr title="'+this.prof+'">'+this.profsig+'</abbr></span></p>';
	str+='<p class="aula"><span class="aula">'+this.sala+'</span><span class="aula">'+this.turmac+'</span></p>';
	str+='</div></div>';
	return str;
}
Aula.prototype.selecttext=function(){
	var str=this.turma+' - '+this.profsig+' '+this.txtdia+' '+this.txthora;
	return str;
}
Aula.prototype.selecttextrepetida=function(){
	var str=' + '+this.txtdia+' '+this.txthora;
	return str;
}

var curso;
var cadeiras;

$(document).ready(function(){
	generateTimetable();

	$.blockUI.defaults.css = {};
	$.blockUI({ message: $('#promptcurso') }); 

	
	$('#cursook').click(function(){
		$.blockUI({message:$('#loading')});
		
		curso=$('#cursoselect option:selected').val();
		var ano_lectivo=$('#anoselect option:selected').val();
		var periodo=$('#semestreselect option:selected').val();
		var username=$('#username').val();
		var password=$('#password').val();
		/*$.getJSON(baseURL+"/"+curso+ano_lectivo+periodo+".json",function(data){
			parse_horario(data);
			$.blockUI({message:$('#promptcadeiras')});
		}).error(function(){
			$('#promptcursoerror').show();
			$.blockUI({message:$('#promptcurso')});
		});*/
		$.post("getturmas.php",{curso:curso,anolectivo:ano_lectivo,periodo:periodo,username:username,password:password},
			
			function(data){
				//console.log(data);
				if (data=="null")
				{
					$('#promptcursoerror').show();
					$.blockUI({message:$('#promptcurso')});
				}else{
					data = JSON.parse(data);
					parse_horario(data);
					$.blockUI({message:$('#promptcadeiras')});
				}
		}).error(
		function(){
			$('#promptcursoerror').show();
			$.blockUI({message:$('#promptcurso')});
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
	
	$(document).on('mouseenter','.aula',function(event){
		$(this).addClass("mouseoveraula");
	});
	$(document).on('mouseleave','.aula',function(event){
		$(this).removeClass("mouseoveraula");
	});
	
	$('#updatevagasbtn').click(function(){
		$.getJSON("getvagas.php",{curso:curso},function(data){
			$.each(data,function(cadeira,obj){
				var i="";
				for (var key in cadeiras) if (cadeiras[key].nomec==cadeira) {i=key;break;}
				if (i!=""){
					$.each(obj,function(turma,vagas){
						var j;
						for (j=0;j<cadeiras[i].praticas.length&&cadeiras[i].praticas[j].turma!=turma;j++);
						if (j<cadeiras[i].praticas.length){
							cadeiras[i].praticas[j].vagas=vagas;
							$('select[data-cadeira="'+cadeiras[i].nome+'"] option[value="'+cadeiras[i].praticas[j].turma+'"]').html(cadeiras[i].praticas[j].selecttext());
						}
					});
				}
			});
			
		});
	});
	
});

//fazer parse do json, colocar as cadeiras no vector
function parse_horario(data){
	cadeiras={};
	$.each(data,function(ano,data2){
	$('#listcadeiras').append('<div class="listcadano"><p class="listcadanop">'+ano+'</p><ul  id="listcadeiras'+ano.replace(" ","_")+'"></ul></div>');
		$.each(data2,function(cadeira,obj){
			
			cadeiras[cadeira]=new Cadeira(cadeira,obj);
			$('#listcadeiras'+ano.replace(" ","_")).append('<li class="listcad"><label><input class="listcad" value="'+cadeira+'" type="checkbox"/><abbr title="'+obj.nome+'">'+cadeira+'</abbr></label></li>');
			
		});
	});

}

function addCadeiras(){
	$('input.listcad:checked').each(function(index,element){
		$('#selectorsdiv').append(cadeiras[$(element).val()].selectorhtml());
	});
}

// Cria a tabela o horário
function generateTimetable() {
	var linha="";
	linha+="<tr><th>Horas</th>";
	for (d in diasSemana) {
		linha+="<th>"+diasSemana[d]+"</th>";
	}
	linha+="</tr>";
	$("#horario").append(linha);

	for (h in horas) {
		linha="<tr><th>"+horas[h]+"</th>";
		for (d in diasSemana) {
			linha+="<td id='horario"+diasSemanaMin[d]+horasMin[h]+"'></td>"
		}
		linha+="</tr>";
		$("#horario").append(linha);
	}

}

