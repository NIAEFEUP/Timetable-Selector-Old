<?php
/*
Timetable Selector By NIFEUP
@Author: Diogo Basto (ei09082@fe.up.pt)
*/

//params 
//pv_curso_id 742 -> MIEIC
//pv_periodos 2 ->1º semestre 3->2ºsemestre
//pv_ano_lectivo 2012

// variaveis
$anolectivo='2012';			// Ano lectivo
$faculdade_codigo='feup';	// Codigo da Faculdade
$curso_id='742';			// Id do curso
$periodo_id='2';			// Id do semestre (2->1º semestre 3->2ºsemestre)
$force_update=FALSE;		// Forcar actualizacoes da cache
$username='';				// Username do sigarra
$password='';				// Password do sigarra
$filename='default.json';	// Ficheiro da cache



if (parsePOST()) {
	$file_contents=file_get_contents($filename);
	if ($file_contents===FALSE || $force_update) { // Ficheiro nao existe ou update forcado
		queryFEUP($username,$password,$faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$filename);

	}
	else {
		echo $file_contents;
	}
}

//Parsing e verificação dos argumentos
// TODO Sistema de erros mais verboso
// TODO Sistema mais puro (sem variaveis globais)
function parsePOST() {

	global 	$anolectivo,
			$faculdade_codigo,
			$curso_id,
			$periodo_id,
			$force_update,
			$username,
			$password,
			$filename;

	if (isset($_POST['force_update'])) {
		$force_update=($_POST['force_update']=='true'?TRUE:FALSE);
	}
	
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username=$_POST['username'];
		$password=$_POST['password'];
	}
	else if ($force_update) {
		return FALSE; // Erro: Necessita do username e password
	}

	if (isset($_POST['anolectivo'])) {
		$anolectivo=$_POST['anolectivo'];
	}
	else {
		return FALSE; // Erro: Necessita do ano lectivo
	}

	if (isset($_POST['periodo'])) {
		switch($_POST['periodo'])
		{
			case '1': $periodo_id='2';break;
			case '2': $periodo_id='3';break;
			default : return FALSE; // Erro: periodo desconhecido
		}
	}
	else {
		return FALSE; // Erro: Necessita do periodo
	}

	if (isset($_POST['curso'])) {
		switch($_POST['curso'])
		{
			case 'feup-MIEIC': $faculdade_codigo='feup';$curso_id='742';break;
			case 'feup-CINF': $faculdade_codigo='feup';$curso_id='454';break;
			case 'feup-LCEEMG': $faculdade_codigo='feup';$curso_id='738';break;
			case 'feup-MEMG': $faculdade_codigo='feup';$curso_id='739';break;
			case 'feup-MIB': $faculdade_codigo='feup';$curso_id='728';break;
			case 'feup-MIEC': $faculdade_codigo='feup';$curso_id='740';break;
			case 'feup-MIEA': $faculdade_codigo='feup';$curso_id='726';break;
			case 'feup-MIEEC': $faculdade_codigo='feup';$curso_id='741';break;
			case 'feup-MIEIG': $faculdade_codigo='feup';$curso_id='725';break;
			case 'feup-MIEM': $faculdade_codigo='feup';$curso_id='743';break;
			case 'feup-MIEMM': $faculdade_codigo='feup';$curso_id='744';break;
			case 'feup-MIEQ': $faculdade_codigo='feup';$curso_id='745';break;
	
			case 'fcup-LAP': $faculdade_codigo='fcup';$curso_id='1011';break;
			case 'fcup-LAST': $faculdade_codigo='fcup';$curso_id='956';break;
			case 'fcup-LB': $faculdade_codigo='fcup';$curso_id='884';break;
			case 'fcup-LBQ': $faculdade_codigo='fcup';$curso_id='863';break;
			case 'fcup-LCC': $faculdade_codigo='fcup';$curso_id='885';break;
			case 'fcup-LCE': $faculdade_codigo='fcup';$curso_id='886';break;
			case 'fcup-LCTA': $faculdade_codigo='fcup';$curso_id='887';break;
			case 'fcup-LF': $faculdade_codigo='fcup';$curso_id='888';break;
			case 'fcup-LG': $faculdade_codigo='fcup';$curso_id='889';break;
			case 'fcup-LM': $faculdade_codigo='fcup';$curso_id='864';break;
			case 'fcup-LQ': $faculdade_codigo='fcup';$curso_id='865';break;
			case 'fcup-MIERS': $faculdade_codigo='fcup';$curso_id='870';break;
			case 'fcup-MIEF': $faculdade_codigo='fcup';$curso_id='890';break;

			case 'flup-ARQU': $faculdade_codigo='flup';$curso_id='339';break;
			case 'flup-CINF': $faculdade_codigo='flup';$curso_id='454';break;
			case 'flup-CC': $faculdade_codigo='flup';$curso_id='455';break;
			case 'flup-CL': $faculdade_codigo='flup';$curso_id='460';break;
			case 'flup-EPL': $faculdade_codigo='flup';$curso_id='459';break;
			case 'flup-FILO': $faculdade_codigo='flup';$curso_id='340';break;
			case 'flup-GEOGR': $faculdade_codigo='flup';$curso_id='341';break;
			case 'flup-HISTO': $faculdade_codigo='flup';$curso_id='342';break;
			case 'flup-HART': $faculdade_codigo='flup';$curso_id='453';break;
			case 'flup-LA': $faculdade_codigo='flup';$curso_id='456';break;
			case 'flup-LRI': $faculdade_codigo='flup';$curso_id='458';break;
			case 'flup-LLC': $faculdade_codigo='flup';$curso_id='457';break;
			case 'flup-SOCI': $faculdade_codigo='flup';$curso_id='452';break;
			
			case 'fbaup-AP':$faculdade_codigo='fbaup';$curso_id='1315';break;
			case 'fbaup-DC':$faculdade_codigo='fbaup';$curso_id='1314';break;
	
			default : return FALSE; // Erro: Curso desconhecido
		}
	}

	$filename=$_POST['curso'].$_POST['anolectivo'].$_POST['periodo'].'.json';
	return TRUE;
}

//Query FEUP
function queryFEUP($username,$password,$faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$filename) {
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
	$url= 'https://sigarra.up.pt/'.$faculdade_codigo.'/pt/hor_geral.lista_turmas_curso';
	$fieldstr = 'pv_curso_id='.$curso_id.'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anolectivo;
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
		$url= 'https://sigarra.up.pt/'.$faculdade_codigo.'/pt/hor_geral.turmas_view';
		$fieldstr = 'pv_turma_id='.$turma_id.'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anolectivo; 
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
				if ($tipo=='TP'||$tipo=='T'||$tipo=='P'||$tipo=='L'||$tipo=='PL'||$tipo=='OT')
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
	$url= 'https://sigarra.up.pt/feup/pt/vld_validacao.sair';
	$fieldstr = '';
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,5);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
    $logoutresult = curl_exec($ch);
    curl_close($ch);
	file_put_contents($filename,json_encode($horarios));
	chmod($filename,0644);
	echo json_encode($horarios);
}

?>
