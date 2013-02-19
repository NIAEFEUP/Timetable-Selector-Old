<?php
/*
Timetable Selector By NIFEUP
@Author: Diogo Basto (ei09082@fe.up.pt)
*/

//params 
//pv_curso_id 742 -> MIEIC
//pv_periodos 2 ->1º semestre 3->2ºsemestre
//pv_ano_lectivo 2012

//username e password do sifeup
	$username=$_POST['username'];
	$password=$_POST['password'];
	
//tratar parametros da pagina
	$anoletivo=$_POST['anolectivo'];
	switch($_POST['curso'])
	{
		case 'MIEIC': $curso_id='742';break;
		case 'CINF': $curso_id='454';break;
		case 'LCEEMG': $curso_id='738';break;
		case 'MEMG': $curso_id='739';break;
		case 'MIB': $curso_id='728';break;
		case 'MIEC': $curso_id='740';break;
		case 'MIEA': $curso_id='726';break;
		case 'MIEEC': $curso_id='741';break;
		case 'MIEIG': $curso_id='725';break;
		case 'MIEM': $curso_id='743';break;
		case 'MIEMM': $curso_id='744';break;
		case 'MIEQ': $curso_id='745';break;
		default : echo 'Error';exit();
	}
	switch($_POST['periodo'])
	{
		case '1': $periodo_id='2';break;
		case '2': $periodo_id='3';break;
		default : echo 'Error';exit();
	}
	
	
//Query FEUP
	
	
	
	
	//Iniciar Sessao dos posts
    $ch = curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch,CURLOPT_COOKIEJAR ,null); 
	
	//POST para fazer login
	$url= 'https://sigarra.up.pt/feup/pt/vld_validacao.validacao';
	$fieldstr = 'p_user='.$username.'&p_pass='.$password.'&p_app=162&p_amo=55&p_address=web_page.Inicial'; //O App e o Amo pertencem ao form hidden de login, talvez seja preciso ir ao homepage buscalos primeiro
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,5);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
    $loginresult = curl_exec($ch);
	
	//POST para sacar as turmas
	$url= 'https://sigarra.up.pt/feup/pt/hor_geral.lista_turmas_curso';
	$fieldstr = 'pv_curso_id='.$curso_id.'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anoletivo;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,3);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
    $turmasresult = curl_exec($ch);
	
	//Parse para sacar os links	
	$dom = new DOMDocument;
	@$dom->loadHTML($turmasresult);
	
	$xp = new DOMXpath($dom);
	$nodes = $xp->query('//a[@class="t"]');
	
	
	for ($i=0;$i<$nodes->length;$i++)
	{
		$turma_nome=$nodes->item($i)->nodeValue;
		$str=$nodes->item($i)->attributes->getNamedItem("href")->nodeValue;
		$j=strpos($str,'=')+1;
		$turma_id=substr($str,$j,strpos($str,'&')-$j);
		//echo  "<p>".$turma_nome." ".$turma_id."</p>";
		
		//POST para sacar o horario
		$url= 'https://sigarra.up.pt/feup/pt/hor_geral.turmas_view';
		$fieldstr = 'pv_turma_id='.$turma_id.'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anoletivo; 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,3);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
		$horarioresult=curl_exec($ch);
		
		//SCRAP
		$dom2 = new DOMDocument;
		@$dom2->loadHTML($horarioresult);
		//Scrap todas as rows
		$xp2 = new DOMXpath($dom2);
		$nodesrow = $xp2->query('//table[@class="tabela"]/tr');
		
		$rowspan=array(0,0,0,0,0,0,0,0); //rowspan para as colunas, 0->horas 1-6-> segunda a sabado, 7-> força a saida do while pk e sempre 0
		//Comecar as 8 da manha
		$hora=8.0;
		//Comecar na row 2, a 1 tem os dias (o xpath comeca a 1 e nao a 0, por isso aumentar o ciclo para <= tb)
		for($row=2; $row<=$nodesrow->length;$row++)
		{	
			$nodescol=$xp2->query('//div/table[@class="tabela"]/tr['.$row.']/td'); //Nao usar child, por causa dos whitespaces nodes.
			$dia=1;
			for ($col=1;$col<$nodescol->length;$col++)
			{
				while ($rowspan[$dia]>0)
				{ //compensar os dias que sao comidos pelo rowspan
					$rowspan[$dia]--;
					$dia++;
				}
				
				//scrap do td
				$nodetd=$nodescol->item($col);
				$tipo=$nodetd->attributes->getNamedItem('class')->nodeValue;
				if ($tipo=='TP'||$tipo=='T'||$tipo=='P'||$tipo=='L')
				{	//se for uma aula
					//contar o rowspan/duracao da aula
					$aduracao=$nodetd->attributes->getNamedItem('rowspan')->nodeValue;
					$rowspan[$dia]=$aduracao-1;
					//nome da aula
					$anome=$xp2->query('./b/acronym/@title',$nodetd)->item(0)->nodeValue;
					$asigla=$xp2->query('./b/acronym/a',$nodetd)->item(0)->nodeValue;	
					//sala -> usar // em vez de / nestes querys porque os br's fo**m tudo (literalmente)
					$asala=$xp2->query('.//table/tr/td/a',$nodetd)->item(0)->nodeValue;
					//professor 
					$aprofsig=$xp2->query('.//table/tr/td[3]//a',$nodetd)->item(0)->nodeValue;
					$aprofnome=$xp2->query('.//table/tr/td[3]/acronym/@title',$nodetd)->item(0)->nodeValue;
					//turma da cadeira
					$turma_cadeira=$xp2->query('.//span/a',$nodetd)->item(0)->nodeValue;
					//passar tudo para o array
					if (!is_array($horarios[$asigla][$tipo])) $horarios[$asigla][$tipo]=array();
					array_push($horarios[$asigla][$tipo], array('dia'=>$dia,'hora'=>$hora,'nome'=>$anome,'sigla'=>$asigla,'tipo'=>$tipo,'turma'=>$turma_nome,'turmac'=>$turma_cadeira,'duracao'=>$aduracao,'sala'=>$asala,'profsig'=>$aprofsig,'prof'=>$aprofnome));
					//gravar o nome da cadeira dentro do objecto da cadeira para facilitar extraçao no js
					$horarios[$asigla]['nome']=$anome;
					
					//echo "<p>".$dia." ".$hora." ".$anome." ".$asigla." ".$tipo." ".$turma_nome." ".$turma_cadeira." ".$aduracao." ".$asala." ".$aprofsig." ".$aprofnome."</p>";
				}
				
				$dia++;
			}
			while ($rowspan[$dia]>0)
			{//executar isto no final mais uma vez, podem haver colunas no final com rowspan
				$rowspan[$dia]--;
				$dia++;
			}
			$hora=$hora+0.5;
		}
		
	}
	
	//fechar a sessao
    curl_close($ch);
	$filename=''.$_POST['curso'].$_POST['anolectivo'].$_POST['periodo'].'.json';
	file_put_contents($filename,json_encode($horarios));
	chmod($filename,0664);
	echo json_encode($horarios);

?>