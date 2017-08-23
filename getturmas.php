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
$faculdade_codigo[0]='feup';	// Codigo da Faculdade
$curso_id[0]='742';			// Id do curso
$periodo_id='2';			// Id do semestre (2->1º semestre 3->2ºsemestre)
$force_update=FALSE;		// Forcar actualizacoes da cache
$force_all=FALSE;			// Ir buscar todos
$force_all_start=0;			// A partir de algum, é provavel que o script faça timeout a meio e não vá buscar tudo.
$username='';				// Username do sigarra
$password='';				// Password do sigarra
$filename='default.json';	// Ficheiro da cache
$errordebug=true;			//fazer echos

$ch=null; //variável do curl



if (parsePOST()) {
	if ($force_all)
	{
		file_put_contents('log.txt', 'FORCE ALL '.$_POST['anolectivo'].' '.$_POST['periodo'].' '.date("Y-m-d H:i:s").' '.$username."\r\n", FILE_APPEND | LOCK_EX);
		login($username,$password);
		updateAll($force_all_start,$periodo_id,$anolectivo);
		logout();
	}
	else {
		$file_contents=file_get_contents($filename);

		if ($file_contents===FALSE || $force_update||$file_contents=="null") { // Ficheiro nao existe ou update forcado
			file_put_contents('log.txt', $_POST['curso'].' '.$_POST['anolectivo'].' '.$_POST['periodo'].' '.date("Y-m-d H:i:s").' '.$force_update."\r\n", FILE_APPEND | LOCK_EX);
			login($username,$password);
			queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$filename);
			logout();
		}
		else {
			
			echo $file_contents;
			$today = getdate();
			file_put_contents('log.txt', $_POST['curso'].' '.$_POST['anolectivo'].' '.$_POST['periodo'].' '.date("Y-m-d H:i:s"). "\r\n", FILE_APPEND | LOCK_EX);
			chmod('log.txt',0644);
		}
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
			$force_all,
			$force_all_start,
			$username,
			$password,
			$filename;

	if (isset($_POST['force_update'])) {
		$force_update=($_POST['force_update']=='true'?TRUE:FALSE);
	}
	
	if (isset($_POST['force_all'])) {
		$force_all=($_POST['force_all']=='true'?TRUE:FALSE);
	}
	
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username=$_POST['username'];
		$password=$_POST['password'];
	}
	else if ($force_update||$force_all) {
		return FALSE; // Erro: Necessita do username e password
	}

	if (isset($_POST['anolectivo'])) {
		$anolectivo=$_POST['anolectivo'];
	}
	else {
		return FALSE; // Erro: Necessita do ano lectivo
	}
	
	if (isset($_POST['fulljson'])) {
		$errordebug=false;
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

	if (isset($_POST['curso'])&&!$force_all) {
		switch($_POST['curso'])
		{
			case 'feup-MIEIC': $force_all_start=0; $faculdade_codigo[0]='feup';$curso_id[0]='742';break;
			//case 'feup-CINF': $force_all_start=0;  $faculdade_codigo[0]='flup';$curso_id[0]='454'; $faculdade_codigo[1]='feup';$curso_id[1]='454';break;
			case 'feup-LCEEMG': $force_all_start=1; $faculdade_codigo[0]='feup';$curso_id[0]='738';break;
			case 'feup-MEMG': $force_all_start=2; $faculdade_codigo[0]='feup';$curso_id[0]='739';break;
			case 'feup-MIB': $force_all_start=3; $faculdade_codigo[0]='feup';$curso_id[0]='728';break;
			case 'feup-MIEC': $force_all_start=4; $faculdade_codigo[0]='feup';$curso_id[0]='740';break;
			case 'feup-MIEA': $force_all_start=5; $faculdade_codigo[0]='feup';$curso_id[0]='726';break;
			case 'feup-MIEEC': $force_all_start=6; $faculdade_codigo[0]='feup';$curso_id[0]='741';break;
			case 'feup-MIEIG': $force_all_start=7; $faculdade_codigo[0]='feup';$curso_id[0]='725';break;
			case 'feup-MIEM': $force_all_start=8; $faculdade_codigo[0]='feup';$curso_id[0]='743';break;
			case 'feup-MIEMM': $force_all_start=9; $faculdade_codigo[0]='feup';$curso_id[0]='744';break;
			case 'feup-MIEQ': $force_all_start=10; $faculdade_codigo[0]='feup';$curso_id[0]='745';break;
	
			case 'fcup-LAP': $force_all_start=20; $faculdade_codigo[0]='fcup';$curso_id[0]='1011';break;
			case 'fcup-LAST': $force_all_start=21; $faculdade_codigo[0]='fcup';$curso_id[0]='956';break;
			case 'fcup-LB': $force_all_start=22; $faculdade_codigo[0]='fcup';$curso_id[0]='884';break;
			case 'fcup-LBQ': $force_all_start=23; $faculdade_codigo[0]='fcup';$curso_id[0]='863';break;
			case 'fcup-LCC': $force_all_start=24; $faculdade_codigo[0]='fcup';$curso_id[0]='885';break;
			case 'fcup-LCE': $force_all_start=25; $faculdade_codigo[0]='fcup';$curso_id[0]='886';break;
			case 'fcup-LCTA': $force_all_start=26; $faculdade_codigo[0]='fcup';$curso_id[0]='887';break;
			case 'fcup-LF': $force_all_start=27; $faculdade_codigo[0]='fcup';$curso_id[0]='888';break;
			case 'fcup-LG': $force_all_start=28; $faculdade_codigo[0]='fcup';$curso_id[0]='889';break;
			case 'fcup-LM': $force_all_start=29; $faculdade_codigo[0]='fcup';$curso_id[0]='864';break;
			case 'fcup-LQ': $force_all_start=30; $faculdade_codigo[0]='fcup';$curso_id[0]='865';break;
			case 'fcup-MIERS': $force_all_start=31; $faculdade_codigo[0]='fcup';$curso_id[0]='870';break;
			case 'fcup-MIEF': $force_all_start=32; $faculdade_codigo[0]='fcup';$curso_id[0]='890';break;

			case 'flup-ARQU': $force_all_start=40; $faculdade_codigo[0]='flup';$curso_id[0]='339';break;
			case 'flup-CINF': $force_all_start=41; $faculdade_codigo[0]='flup';$curso_id[0]='454'; $faculdade_codigo[1]='feup';$curso_id[1]='454';break;
			case 'flup-CC': $force_all_start=42; $faculdade_codigo[0]='flup';$curso_id[0]='455';$faculdade_codigo[1]='fep';$curso_id[1]='455';$faculdade_codigo[2]='feup';$curso_id[2]='455';$faculdade_codigo[3]='fbaup';$curso_id[3]='455';break;
			case 'flup-CL': $force_all_start=43; $faculdade_codigo[0]='flup';$curso_id[0]='460';break;
			case 'flup-EPL': $force_all_start=44; $faculdade_codigo[0]='flup';$curso_id[0]='459';break;
			case 'flup-FILO': $force_all_start=45; $faculdade_codigo[0]='flup';$curso_id[0]='340';break;
			case 'flup-GEOGR': $force_all_start=46; $faculdade_codigo[0]='flup';$curso_id[0]='341';break;
			case 'flup-HISTO': $force_all_start=47; $faculdade_codigo[0]='flup';$curso_id[0]='342';break;
			case 'flup-HART': $force_all_start=48; $faculdade_codigo[0]='flup';$curso_id[0]='453';break;
			case 'flup-LA': $force_all_start=49; $faculdade_codigo[0]='flup';$curso_id[0]='456';break;
			case 'flup-LRI': $force_all_start=50; $faculdade_codigo[0]='flup';$curso_id[0]='458';break;
			case 'flup-LLC': $force_all_start=51; $faculdade_codigo[0]='flup';$curso_id[0]='457';break;
			case 'flup-SOCI': $force_all_start=52; $faculdade_codigo[0]='flup';$curso_id[0]='452';break;
			
			case 'fbaup-AP': $force_all_start=60;$faculdade_codigo[0]='fbaup';$curso_id[0]='1315';break;
			case 'fbaup-DC': $force_all_start=61;$faculdade_codigo[0]='fbaup';$curso_id[0]='1314';break;
	
			default : return FALSE; // Erro: Curso desconhecido
		}
	}

	$filename=$_POST['curso'].$_POST['anolectivo'].$_POST['periodo'].'.json';
	return TRUE;
}


function login($username,$password){
	global $ch;
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
	//echo $loginresult;
}

function logout(){
	global $ch;

	//fechar a sessao
	$url= 'https://sigarra.up.pt/feup/pt/vld_validacao.sair';
	$fieldstr = '';
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,5);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
    $logoutresult = curl_exec($ch);
    curl_close($ch);
}

//Query FEUP
function queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$filename) {
	global $ch;
	
	$fcicount=count($faculdade_codigo);
	for ($fci=0;$fci<$fcicount;$fci++)
	{
		//POST para sacar as turmas
		$url= 'https://sigarra.up.pt/'.$faculdade_codigo[$fci].'/pt/hor_geral.lista_turmas_curso';
		$fieldstr = 'pv_curso_id='.$curso_id[$fci].'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anolectivo;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,3);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
		$turmasresult = curl_exec($ch);
		
		//Parse para sacar os links	
		$dom = new DOMDocument;
		@$dom->loadHTML($turmasresult);
		
		$xp = new DOMXpath($dom);
		
		$nodesanos=$xp->query('//table/tr/td/table[@class="tabela"]');
		for ($anoi=0;$anoi<$nodesanos->length;$anoi++)
		{
			$ano=$xp->query('.//th',$nodesanos->item($anoi))->item(0)->nodeValue;
			
			$nodes = $xp->query('.//a[@class="t"]',$nodesanos->item($anoi));
			for ($i=0;$i<$nodes->length;$i++)
			{
				$turma_nome=$nodes->item($i)->nodeValue;
				$str=$nodes->item($i)->attributes->getNamedItem("href")->nodeValue;
				$j=strpos($str,'=')+1;
				$turma_id=substr($str,$j,strpos($str,'&')-$j);
				//echo  $turma_nome." ".$turma_id." ";
				
				//POST para sacar o horario
				$url= 'https://sigarra.up.pt/'.$faculdade_codigo[$fci].'/pt/hor_geral.turmas_view';
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
				
				//cuidado com inspector de elementos, ele pode dizer q existe um tbody entre o table e o tr mas se virem o source ele não está lá
				//fazer //table/tbody/tr/td não selecionaria nenhum TD porque nenhum tr é filho de tbody
				//relativamente a existirem varias classes num elemento ver http://stackoverflow.com/questions/1390568/how-to-match-attributes-that-contain-a-certain-string
				$nodeslinksemana=$xp2->query('//td[@valign="top"]/table[@class="horario-semanas ecra"]/tr[@class="d"]/td/a');
				//isto vai buscar aquele bloco que mostra as semanas para horários diferentes. se exisitir, pega no 2º link e reabre nessa página
				if ($nodeslinksemana->length>0&&$faculdade_codigo[0]=='feup'){
					//recomeçar
					$url='https://sigarra.up.pt/'.$faculdade_codigo[$fci].'/pt/'.$nodeslinksemana->item(1)->attributes->getNamedItem('href')->nodeValue;
					$fieldstr = "";
					//echo 'x'.$url.'</br>';
					//Get para sacar o horario
					curl_setopt($ch,CURLOPT_URL,$url);
					curl_setopt($ch,CURLOPT_HTTPGET,true);
					
					$horarioresult=curl_exec($ch);
					
					$dom2 = new DOMDocument;
					@$dom2->loadHTML($horarioresult);
					//Scrap todas as rows
					$xp2 = new DOMXpath($dom2);
				}
				else{
					//echo 'y'.$url.'?'.$fieldstr .'</br>';
				}
				$nodesrow = $xp2->query('//td[@valign="top"]/div[1]/table[@class="horario"]/tr');
				//echo $horarioresult;
				$rowspan=array(0,0,0,0,0,0,0,0); //rowspan para as colunas, 0->horas 1-6-> segunda a sabado, 7-> força a saida do while pk e sempre 0
				//Comecar as 8 da manha
				$hora=8.0;
				
				//Comecar na row 2, a 1 tem os dias (o xpath comeca a 1 e nao a 0, por isso aumentar o ciclo para <= tb)
				for($row=2; $row<=$nodesrow->length;$row++)
				{	
					
					$nodescol=$xp2->query('//td[@valign="top"]/div[1]/table[@class="horario"]/tr['.$row.']/td'); //Nao usar child, por causa dos whitespaces nodes.
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
						if ($tipo=='TP'||$tipo=='TE'||$tipo=='T'||$tipo=='P'||$tipo=='PR'||$tipo=='L'||$tipo=='PL'||$tipo=='OT'||$tipo=='TC'||$tipo=='S')
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
							
							
							//traduzir o tipo para nao ter de atualizar o JS sempre que atualizo no scrapper
							if ($tipo=='TE') $tipo='T';
							if ($tipo=='PR') $tipo='P';
							
							if (!is_array($horarios[$ano][$asigla][$tipo])) $horarios[$ano][$asigla][$tipo]=array();
							array_push($horarios[$ano][$asigla][$tipo], array('dia'=>$dia,'hora'=>$hora,'nome'=>$anome,'sigla'=>$asigla,'tipo'=>$tipo,'turma'=>$turma_nome,'turmac'=>$turma_cadeira,'duracao'=>$aduracao,'sala'=>$asala,'profsig'=>$aprofsig,'prof'=>$aprofnome));
							//gravar o nome da cadeira dentro do objecto da cadeira para facilitar extraçao no js
							$horarios[$ano][$asigla]['nome']=$anome;
							
							//echo "<p>".$dia." ".$hora." ".$anome." ".$asigla." ".$tipo." ".$turma_nome." ".$turma_cadeira." ".$aduracao." ".$asala." ".$aprofsig." ".$aprofnome."</p>";
						}
						else{ //tipo desconhecido
							if (!empty($nodetd->attributes->getNamedItem('rowspan')->nodeValue))
							{  //este tipo tem rowspan, escrever no error log e acrescentar o span
								$rowspan[$dia]=$nodetd->attributes->getNamedItem('rowspan')->nodeValue-1;
								file_put_contents('error_log.txt', 'Unknown type with span '.$tipo." ".$_POST['curso'].' '.$_POST['anolectivo'].' '.$_POST['periodo'].' '.date("Y-m-d H:i:s").' '.$url."?".$fieldstr."\r\n", FILE_APPEND | LOCK_EX);
								if ($errordebug) echo 'Unknown type with span '.$tipo." ".$_POST['curso'].$url."?".$fieldstr."</br>";
							}
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
				
				//TODO verificar as aulas sobrepostas
				
				/*$nodesrow = $xp2->query('//td[@valign="top"]/div[2]/table[@class="tabela"]/tr');
				for($row=2; $row<$nodesrow->length;$row++)//começar na 3ª linha, caso não exista o length é 0 e o ciclo não entra.
				{	
					
					$nodescol=$xp2->query('.//td',$nodesrow->item($row)); 
					$dia=;
					for ($col=1;$col<$nodescol->length;$col++)
					{
						while ($rowspan[$dia]>0)
						{ //compensar os dias que sao comidos pelo rowspan
							$rowspan[$dia]--;
							$dia++;
						}
						
						//scrap do horário
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
							
							if (!is_array($horarios[$ano][$asigla][$tipo])) $horarios[$ano][$asigla][$tipo]=array();
							array_push($horarios[$ano][$asigla][$tipo], array('dia'=>$dia,'hora'=>$hora,'nome'=>$anome,'sigla'=>$asigla,'tipo'=>$tipo,'turma'=>$turma_nome,'turmac'=>$turma_cadeira,'duracao'=>$aduracao,'sala'=>$asala,'profsig'=>$aprofsig,'prof'=>$aprofnome));
							//gravar o nome da cadeira dentro do objecto da cadeira para facilitar extraçao no js
							$horarios[$ano][$asigla]['nome']=$anome;
							
							//echo "<p>".$dia." ".$hora." ".$anome." ".$asigla." ".$tipo." ".$turma_nome." ".$turma_cadeira." ".$aduracao." ".$asala." ".$aprofsig." ".$aprofnome."</p>";
						}
						
						$dia++;
					}
					
					$hora=$hora+0.5;
				}
				*/
			}
		}
	}
	
	if (!empty($horarios)) {
		file_put_contents($filename,json_encode($horarios,JSON_PRETTY_PRINT));
		chmod($filename,0644);
	}
	echo json_encode($horarios,JSON_PRETTY_PRINT);
}

function updateAll($force_all_start,$periodo_id,$anolectivo){
	$faculdade_codigo=null;$curso_id=null; //limpar o selecionado no parse_post()
	
	
	if ($force_all_start<=0) {
		$curso='feup-MIEIC'; $faculdade_codigo[0]='feup';$curso_id[0]='742';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=1) {
		$curso='feup-LCEEMG'; $faculdade_codigo[0]='feup';$curso_id[0]='738';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=2) {
		$curso='feup-MEMG'; $faculdade_codigo[0]='feup';$curso_id[0]='739';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=3) {
		$curso='feup-MIB'; $faculdade_codigo[0]='feup';$curso_id[0]='728';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=4) {
		$curso='feup-MIEC'; $faculdade_codigo[0]='feup';$curso_id[0]='740';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=5) {
		$curso='feup-MIEA'; $faculdade_codigo[0]='feup';$curso_id[0]='726';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=6) {
		$curso='feup-MIEEC'; $faculdade_codigo[0]='feup';$curso_id[0]='741';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=7) {
		$curso='feup-MIEIG'; $faculdade_codigo[0]='feup';$curso_id[0]='725';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=8) {
		$curso='feup-MIEM'; $faculdade_codigo[0]='feup';$curso_id[0]='743';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=9) {
		$curso='feup-MIEMM'; $faculdade_codigo[0]='feup';$curso_id[0]='744';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=10) {
		$curso='feup-MIEQ'; $faculdade_codigo[0]='feup';$curso_id[0]='745';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}


	
	
	if ($force_all_start<=20) {
		$curso='fcup-LAP'; $faculdade_codigo[0]='fcup';$curso_id[0]='1011';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=21) {
		$curso='fcup-LAST'; $faculdade_codigo[0]='fcup';$curso_id[0]='956';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=22) {
		$curso='fcup-LB'; $faculdade_codigo[0]='fcup';$curso_id[0]='884';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=23) {
		$curso='fcup-LBQ'; $faculdade_codigo[0]='fcup';$curso_id[0]='863';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=24) {
		$curso='fcup-LCC'; $faculdade_codigo[0]='fcup';$curso_id[0]='885';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=25) {
		$curso='fcup-LCE'; $faculdade_codigo[0]='fcup';$curso_id[0]='886';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=26) {
		$curso='fcup-LCTA'; $faculdade_codigo[0]='fcup';$curso_id[0]='887';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=27) {
		$curso='fcup-LF'; $faculdade_codigo[0]='fcup';$curso_id[0]='888';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=28) {
		$curso='fcup-LG'; $faculdade_codigo[0]='fcup';$curso_id[0]='889';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=29) {
		$curso='fcup-LM'; $faculdade_codigo[0]='fcup';$curso_id[0]='864';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=30) {
		$curso='fcup-LQ'; $faculdade_codigo[0]='fcup';$curso_id[0]='865';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=31) {
		$curso='fcup-MIERS'; $faculdade_codigo[0]='fcup';$curso_id[0]='870';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=32) {
		$curso='fcup-MIEF'; $faculdade_codigo[0]='fcup';$curso_id[0]='890';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}


	
	
	
	if ($force_all_start<=40) {
		$curso='flup-ARQU'; $faculdade_codigo[0]='flup';$curso_id[0]='339';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=41) {
		$curso='flup-CINF'; $faculdade_codigo[0]='flup';$curso_id[0]='454'; $faculdade_codigo[1]='feup';$curso_id[1]='454';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	} $faculdade_codigo=null;$curso_id=null;

	if ($force_all_start<=42) {
		$curso='flup-CC'; $faculdade_codigo[0]='flup';$curso_id[0]='455';$faculdade_codigo[1]='fep';$curso_id[1]='455';$faculdade_codigo[2]='feup';$curso_id[2]='455';$faculdade_codigo[3]='fbaup';$curso_id[3]='455';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}$faculdade_codigo=null;$curso_id=null;

	if ($force_all_start<=43) {
		$curso='flup-CL'; $faculdade_codigo[0]='flup';$curso_id[0]='460';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=44) {
		$curso='flup-EPL'; $faculdade_codigo[0]='flup';$curso_id[0]='459';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=45) {
		$curso='flup-FILO'; $faculdade_codigo[0]='flup';$curso_id[0]='340';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=46) {
		$curso='flup-GEOGR'; $faculdade_codigo[0]='flup';$curso_id[0]='341';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=47) {
		$curso='flup-HISTO'; $faculdade_codigo[0]='flup';$curso_id[0]='342';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=48) {
		$curso='flup-HART'; $faculdade_codigo[0]='flup';$curso_id[0]='453';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=49) {
		$curso='flup-LA'; $faculdade_codigo[0]='flup';$curso_id[0]='456';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=50) {
		$curso='flup-LRI'; $faculdade_codigo[0]='flup';$curso_id[0]='458';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=51) {
		$curso='flup-LLC'; $faculdade_codigo[0]='flup';$curso_id[0]='457';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=52) {
		$curso='flup-SOCI'; $faculdade_codigo[0]='flup';$curso_id[0]='452';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}


	
	
	if ($force_all_start<=60) {
		$curso='fbaup-AP';$faculdade_codigo[0]='fbaup';$curso_id[0]='1315';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

	if ($force_all_start<=61) {
		$curso='fbaup-DC';$faculdade_codigo[0]='fbaup';$curso_id[0]='1314';
		queryFEUP($faculdade_codigo,$curso_id,$periodo_id,$anolectivo,$curso.$_POST['anolectivo'].$_POST['periodo'].'.json'); echo "<br>";
	}

}

?>
