// Check the project license on Github
// Sorry for the portuguese comments but I think it's pretty straightforward
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


var curso,ano_lectivo,periodo;
var cadeiras;
var aulas;
var aulaid;
	
function Aula(jsonobj){
	this.dia=Number(jsonobj.dia);
	this.horarow=(Number(jsonobj.hora)-8)*2+1;
	this.cadeira=jsonobj.sigla;
	this.cadeiran=jsonobj.nome;
	this.tipo=jsonobj.tipo;
	this.turma=jsonobj.turma;
	this.turmac=jsonobj.turmac;
	this.duracao=Number(jsonobj.duracao);
	this.horaf=this.horarow+this.duracao-1;
	this.sala=jsonobj.sala;
	this.prof=jsonobj.prof;
	this.profsig=jsonobj.profsig;
	this.vagas="?";
	this.repetido=false;
	this.txtdia=''+(this.dia+1)+'ª';
	this.txthora=''+((this.horarow+this.horarow%2)/2+7)+':'+(((this.horarow-1)%2)*3)+'0';
	this.id=aulaid;
	aulas[aulaid]=false;
	aulaid++;
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
	this.stwidth=bloco.outerWidth()-2;
	this.stheight=bloco.outerHeight()*this.duracao-2;
	
}

function Cadeira(sigla,jsonobj){
	this.showteoricas=true;
	this.teoricas=new Array();
	this.praticas=new Array();
	this.nome=sigla;
	this.nomec=jsonobj.nome;
	this.turmaselect="-";
	this.data=jsonobj;
}
Cadeira.prototype.addMoreTurmas=function(jsonobj){
	this.addTurmas();
	this.data=jsonobj;
}
Cadeira.prototype.addTurmas=function(){
	if (typeof this.data.T!="undefined"){
	for (var i=0;i<this.data.T.length;i++)
		this.teoricas.push(new Aula(this.data.T[i]));}
	if (typeof this.data.P!="undefined"){
	for (var i=0;i<this.data.P.length;i++)
		this.praticas.push(new Aula(this.data.P[i]));}
	if (typeof this.data.TP!="undefined"){
	for (var i=0;i<this.data.TP.length;i++)
		this.praticas.push(new Aula(this.data.TP[i]));}
	if (typeof this.data.L!="undefined"){
	for (var i=0;i<this.data.L.length;i++)
		this.praticas.push(new Aula(this.data.L[i]));}
	if (typeof this.data.PL!="undefined"){
	for (var i=0;i<this.data.PL.length;i++)
		this.praticas.push(new Aula(this.data.PL[i]));}
	if (typeof this.data.OT!="undefined"){
	for (var i=0;i<this.data.OT.length;i++)
		this.praticas.push(new Aula(this.data.OT[i]));}
}
Cadeira.prototype.selectorhtml=function(){
	this.addTurmas();
	var str='';
	str+='<div class="classselectorwrapper">';
	str+='<div class="classselector" data-cadeira="'+this.nome+'">';
	str+='<span>'+this.nomec+' ('+this.nome+')'+'</span>';
	//	str+=this.nomec+' ('+this.nome+')';
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
			if (this.praticas[i].vagas!='?') str+=' ('+this.praticas[i].vagas+')';
			str+='</option>';
		}
	}
	if (this.praticas.length==0) str+='<option value="teoricas">só teoricas</option>'
	str+='</select>';
	if (this.teoricas.length!=0) str+='<label><input class="mostrarteoricas" value="'+this.nome+'" type="checkbox" data-cadeira="'+this.nome+'" checked/>Mostrar Teoricas</label>'
	str+='</div>';
	
	//str+='<div class="selectorwarning" data-cadeira="'+this.nome+'">';
	str+='<img class="selectorwarning" data-cadeira="'+this.nome+'"src="error.png" alt="conflito" title="conflito">';
	//str+='</div>';
	str+='</div>';
	
	return str;
}
Cadeira.prototype.showTurma=function(){
	$('.aula[data-cadeira="'+this.nome+'"]').remove();
	$('.selectorwarning[data-cadeira="'+this.nome+'"]').removeClass("selectorwarningoverlap");
			
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
			
			aulas[aula.id]=false;
			
			if(aula.turma==turmaselect)
			{
				var flag=false;
				$('#content').append(aula.horariohtml());
				aulas[aula.id]=true;
			}
		});
	}
	verOverlap();
}
Cadeira.prototype.previewTurma=function(turma){
	if (turma!="-" && turma!=this.turmaselect){
		$.each(this.praticas,function(i,aula){
			if(aula.turma==turma)
			{
				$('#content').append(aula.horariopreviewhtml());		
			}
		});
	}
}

Aula.prototype.horariohtml=function(){
	var str='';
	str+='<div id="aula'+this.id+'" class="'+this.tipoh+' aula" data-dia="'+this.dia+'" data-horai="'+this.horarow+'"  data-horaf="'+this.horaf+'" data-cadeira="'+this.cadeira+'" style="left:'+this.stleft+'px;top:'+this.sttop+'px;height:'+this.stheight+'px;width:'+this.stwidth+'px;"><div class="aulawrapper">';
	str+='<p class="aula"><span class="aula"><abbr title="'+this.cadeiran+'">'+this.cadeira+'</abbr></span><span class="aula"><abbr title="'+this.prof+'">'+this.profsig+'</abbr></span></p>';
	str+='<p class="aula"><span class="aula">'+this.sala+'</span><span class="aula">'+this.turmac+'</span></p>';
	str+='</div></div>';
	return str;
}
Aula.prototype.horariopreviewhtml=function(){
	var str='';
	str+='<div id="aula'+this.id+'" class="'+this.tipoh+' aula aulapreview" data-dia="'+this.dia+'" data-horai="'+this.horarow+'"  data-horaf="'+this.horaf+'" data-cadeira="'+this.cadeira+'" style="left:'+this.stleft+'px;top:'+this.sttop+'px;height:'+this.stheight+'px;width:'+this.stwidth+'px;"><div class="aulawrapper">';
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

function verOverlap(){
	var flag;
	var i;
	var divaula,divaula2;
	var strconf;
	for (var j=0;j<aulaid;j++)
	{
		if (aulas[j]==true)
		{
			divaula2=$('#aula'+j);
			flag=false;
			dia2=divaula2.data("dia");
			horai2=divaula2.data("horai");
			horaf2=divaula2.data("horaf");
			strconf="Conflitos: ";
			for (i=0;i<aulaid;i++)
			{
				if (aulas[i]==true && j!=i)
				{
					divaula=$('#aula'+i);
					dia1=divaula.data("dia");
					horai1=divaula.data("horai");
					horaf1=divaula.data("horaf");
					if (dia1==dia2&&(
					(horai1>=horai2&& horaf1<=horaf2)||
					(horai1<=horai2&& horaf1>=horaf2)||
					(horai1<=horaf2&& horaf1>=horai2)))
					{
						divaula.addClass("aulaoverlap");
						$('.selectorwarning[data-cadeira="'+divaula.data("cadeira")+'"]').addClass("selectorwarningoverlap");
						strconf+=divaula.data("cadeira")+" ";
						flag=true;
					}
				}
			}
			if (flag)
			{
				divaula2.addClass("aulaoverlap");
				var img=$('.selectorwarning[data-cadeira="'+divaula2.data("cadeira")+'"]');
				img.addClass("selectorwarningoverlap");
				img.attr({alt:strconf,title:strconf});
				
			}
			else
			{
				divaula2.removeClass("aulaoverlap");
				//só fazer remove caso nenhuma aula desta cadeira tenha conflito, acontece em casos da aula ter mais que uma prática p.e. SRSI
				if (!$('.aula[data-cadeira="'+divaula2.data("cadeira")+'"]').hasClass("aulaoverlap"))
					$('.selectorwarning[data-cadeira="'+divaula2.data("cadeira")+'"]').removeClass("selectorwarningoverlap");
				
			}
		}
	}
}


$(document).ready(function(){
	generateTimetable();
	$.blockUI.defaults.css = {};
	if (!window.location.hash){
		//verificar data para automatizar seleção de ano e semestre
		var today = new Date();
		var mm = today.getMonth()+1; //January is 0!
		var ano_l = today.getFullYear();
		var semestre=1;
		if (mm<8) {//janeiro a julho selecionar 2º semestre, agosto a dezembro 1º
			ano_l=ano_l-1
			semestre=2;
		}
		$('#anoselect').val(ano_l);
		$('#anoselect').prop('disabled', true);
		$('#semestreselect').filter('[value='+semestre+']').prop('checked', true);
		$('#semestreselect').prop('disabled', true);
		
		$.blockUI({ message: $('#promptcurso') }); 
		
	}
	else
	{
	
		//Parse hash string to load timetable
		var str=window.location.hash.slice(1); //remove '#' from string
		var args=str.split("~");
		$.blockUI({message:$('#loading')}); 
		loadTimetable(args);
		
	}
	
	$('#cursook').click(function(){
		$.blockUI({message:$('#loading')});
		
		curso=$('#cursoselect option:selected').val();
		ano_lectivo=$('#anoselect option:selected').val();
		periodo=$('#semestreselect option:selected').val();
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
	
	$(document).on('mouseenter','.classselector select option',function(event){
		//console.log("enter"+this.value+this.parentElement.getAttribute('data-cadeira'));
		cadeiras[this.parentElement.getAttribute('data-cadeira')].previewTurma(this.value);
	});
	$(document).on('mouseleave','.classselector select option',function(event){
		$('div.aulapreview[data-cadeira="'+this.parentElement.getAttribute('data-cadeira')+'"]').remove();
	});
	
	$(document).on('mouseenter','.classselector',function(event){
		var cadeira=$(this).data("cadeira");
		$('div.aula[data-cadeira="'+cadeira+'"]').addClass("mouseoverselect");
		
	});
	$(document).on('mouseleave','.classselector',function(event){
		var cadeira=$(this).data("cadeira");
		$('div.aula[data-cadeira="'+cadeira+'"]').removeClass("mouseoverselect");
	});
	
	$(document).on('click','input.listcadano',function(event){
		var ano=$(this).attr('value');
		var checked=$(this).prop("checked");
		//alert(ano);
		if (checked==true) $('input.listcad[data-ano="'+ano+'"]').prop("checked",true);
		else  $('input.listcad[data-ano="'+ano+'"]').prop("checked",false);
	});
	
	$('#saveTT').click(saveTimetable);
	
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
							var str=$('select[data-cadeira="'+cadeiras[i].nome+'"] option[value="'+cadeiras[i].praticas[j].turma+'"]').html();
							var pat=/\([\?0-9]+\)/;
							if (str.search(pat)==-1) str+=' ('+vagas+')';
							else str=str.replace(pat,'('+vagas+')');
							$('select[data-cadeira="'+cadeiras[i].nome+'"] option[value="'+cadeiras[i].praticas[j].turma+'"]').html(str);
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
	aulas={};
	aulaid=0;
	$.each(data,function(ano,data2){
		$('#listcadeiras').append('<div class="listcadano" id="divlistcadeiras'+ano.replace(" ","_")+'"><p class="listcadanop"><label><input class="listcadano" value="'+ano.replace(" ","_")+'" type="checkbox"/>'+ano+'</label></p><ul  id="listcadeiras'+ano.replace(" ","_")+'"></ul></div>');
		nrcad=3;
		
		$.each(data2,function(cadeira,obj){
			
			if (typeof cadeiras[cadeira] == 'undefined' ) cadeiras[cadeira]=new Cadeira(cadeira,obj);
			else cadeiras[cadeira].addMoreTurmas(obj);
			$('#listcadeiras'+ano.replace(" ","_")).append('<li class="listcad"><label><input class="listcad" data-ano="'+ano.replace(" ","_")+'" value="'+cadeira+'" type="checkbox"/><abbr title="'+obj.nome+'">'+cadeira+'</abbr></label></li>');
			nrcad++;
		});
		nrcol= Math.round(nrcad/8);
		divwidth=85*nrcol;
		var align="center";
		if (nrcol==1) align="left";
		
		$('#divlistcadeiras'+ano.replace(" ","_")).css({
			minWidth : divwidth+"px"
		});	
		$('#divlistcadeiras'+ano.replace(" ","_")+' p').css({
			minWidth : divwidth+"px",
			textAlign : align
		});
		$('#listcadeiras'+ano.replace(" ","_")).css({
			padding:"0px",
			mozColumns:nrcol+" 75px",
			webkitColumns:nrcol+" 75px",
			columns:nrcol+" 75px"
		});	
		
	});

}


function addCadeiras(){
	$('input.listcad:checked').each(function(index,element){
		$('#selectorsdiv').append(cadeiras[$(element).val()].selectorhtml());
	});
	//possibilidade de fazer um each para os unchecked e destruir os objectos para limpar memoria
	
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

function loadTimetable(args){
	var infos=args[0].split(".");
	if (infos.length!=3) 
	{
		$('#promptcursoerror').show();
		$.blockUI({message:$('#promptcurso')});
		return;
	}
	curso=infos[0];
	ano_lectivo=infos[1];
	periodo=infos[2];
    
	$.post("getturmas.php",{curso:curso,anolectivo:ano_lectivo,periodo:periodo,username:"",password:""},
		
		function(data){
			//console.log(data);
			if (data=="null")
			{
				$('#promptcursoerror').show();
				$.blockUI({message:$('#promptcurso')});
			}else{
				data = JSON.parse(data);
				//vai carregar cadeiras a mais do que as que preciso e fazer html que não vou ver mas não tou para rescrever a função
				parse_horario(data);
				
				for (var i=1;i<args.length;i++)
				{
					var cnome=args[i].split(".")[0];
					if (typeof cadeiras[cnome] != 'undefined')
					{
						var cadeira=cadeiras[cnome];
						$('#selectorsdiv').append(cadeira.selectorhtml());	
						if (args[i].split(".").length==2)
						{
							var turma=args[i].split(".")[1];
							$('.classselector select.turmaselect[data-cadeira="'+cnome+'"]').val(turma);
							cadeira.turmaselect=turma;
							cadeira.showTurma();
						}
					}
				}
				
				$.unblockUI();
			}
	}).error(
	function(){
		$('#promptcursoerror').show();
		$.blockUI({message:$('#promptcurso')});
	});

}

function saveTimetable(){
	var stringsave=curso+"."+ano_lectivo+"."+periodo;
	$('.classselector select.turmaselect').each(function(index){
		var cadeira=this.getAttribute('data-cadeira');
		var aula=this.options[this.selectedIndex].value;
		stringsave+="~"+cadeira+"."+aula;
	});
	window.location.hash=stringsave;
}
