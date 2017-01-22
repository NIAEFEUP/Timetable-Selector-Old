<?php
/*
Timetable Selector Updater By NIFEUP
*/

$errordebug=false;
$ch=null; //variável do curl
$anolectivo=date('n')>6?date('Y'):date('Y')-1;
$periodo_id=date('n')>6?2:3;
$faculdades = array('fbaup', 'fcnaup', 'fcup', 'fep', 'feup', 'ffup', 'flup', 'fpceup');

// dafeup, faup, fmup, fmdup, icbas precisam de uma conta activa da respectiva faculdade


set_time_limit(0); //demora bastante mais que 30s(m?) a atualizar todos os horarios
echo '----- STARTING UPDATE -----<br>';
login($_POST['username'],$_POST['password']);
foreach ($faculdades as $faculdade_codigo) {
	queryCursos($faculdade_codigo, 'MI', $anolectivo, $periodo_id); //Mestrados Integrados
	queryCursos($faculdade_codigo, 'L', $anolectivo, $periodo_id);	//Licenciaturas
}
logout();
echo '----- UPDATE COMPLETE -----<br>';


function login($username,$password){
	global $ch;
	//Iniciar Sessao dos posts
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_COOKIEJAR ,null);

	//POST para fazer login
	$url= 'https://sigarra.up.pt/feup/pt/vld_validacao.validacao';
	$fieldstr = 'p_user='.$username.'&p_pass='.$password;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
	$loginresult = curl_exec($ch);
	// echo $loginresult;
}

function logout(){
	global $ch;
	//fechar a sessao
	$url= 'https://sigarra.up.pt/feup/pt/vld_validacao.sair';
	$fieldstr = '';
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
	$logoutresult = curl_exec($ch);
	curl_close($ch);
}

//Query Cursos
function queryCursos($faculdade_codigo, $tipo, $anolectivo, $periodo_id){
	global $ch;
	//POST para sacar a lista de cursos
	$url= 'https://sigarra.up.pt/'.$faculdade_codigo.'/pt/cur_geral.cur_tipo_curso_view';
	$fieldstr = 'pv_tipo_sigla='.$tipo.'&pv_ano_lectivo='.$anolectivo;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
	// echo $url.$fieldstr;
	$cursosresult = curl_exec($ch);

	$dom = new DOMDocument;
	@$dom->loadHTML($cursosresult);
	$xpath = new DOMXpath($dom);

	$nodesCursos = $xpath->query('//*[@id="'.$tipo.'_a"]/li');
	foreach ($nodesCursos as $curso) {
		$faculdade_codigo2=array();
		$faculdade_codigo2[0]=$faculdade_codigo;
		$links = $xpath->query('.//a', $curso);
		parse_str($links[0]->getAttribute("href"), $param);
		$sigla = fetchSigla($faculdade_codigo, $param['pv_curso_id'], $anolectivo);
		$sigla = str_replace('MI:','MI', $sigla); //fcup
		$sigla = str_replace('L:','L', $sigla);	//fcup
		for ($i=1, $j=1; $i < $links->length; $i++) {
			if($links[$i]->getAttribute("title")!="Página Web"){
				$faculdade_codigo2[$j]=strtolower($links[$i]->nodeValue);
				$j++;
			}
		}
		queryTurmas($faculdade_codigo2, $param['pv_curso_id'], $anolectivo, $periodo_id, $sigla);
	}
}

//procura sigla do curso
function fetchSigla($faculdade_codigo, $curso_id, $anolectivo){
	global $ch;
	$url= 'https://sigarra.up.pt/'.$faculdade_codigo.'/pt/cur_geral.cur_view';
	$fieldstr = '&pv_ano_lectivo='.$anolectivo.'&pv_curso_id='.$curso_id;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldstr);
	$siglaresult = curl_exec($ch);
	$dom = new DOMDocument;
	@$dom->loadHTML($siglaresult);
	$xpath = new DOMXpath($dom);

	$plaintxt = $xpath->query('//table[@class="formulario"]')->item(0)->nodeValue;
	$tokens = preg_split('/[\s\n]+/', $plaintxt, 0, PREG_SPLIT_NO_EMPTY);
	for ($i=0; $i < count($tokens); $i++) {
		if($tokens[$i]=="Sigla:"){
			return $tokens[$i+1];
		}
	}
}

//Query Turmas
function queryTurmas($faculdade_codigo,$curso_id,$anolectivo,$periodo_id,$sigla) {
	global $ch;
	$horarios=null;
	$periodo=$periodo_id-1;
	$filename = $faculdade_codigo[0].'-'.$sigla.$anolectivo.$periodo.'.json';

	$fcicount=count($faculdade_codigo); //Cursos em conjunto com várias faculdades
	for ($fci=0;$fci<$fcicount;$fci++)
	{
		//POST para sacar as turmas
		$url= 'https://sigarra.up.pt/'.$faculdade_codigo[$fci].'/pt/hor_geral.lista_turmas_curso';
		$fieldstr = 'pv_curso_id='.$curso_id.'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anolectivo;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,1);
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

				//POST para sacar o horario
				$url= 'https://sigarra.up.pt/'.$faculdade_codigo[$fci].'/pt/hor_geral.turmas_view';
				$fieldstr = 'pv_turma_id='.$turma_id.'&pv_periodos='.$periodo_id.'&pv_ano_lectivo='.$anolectivo;
				curl_setopt($ch,CURLOPT_URL,$url);
				curl_setopt($ch,CURLOPT_POST,1);
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
			}
		}
	}

	if (!empty($horarios)) {
		file_put_contents($filename,json_encode($horarios,JSON_PRETTY_PRINT));
		chmod($filename,0644);
		echo 'Success: '.$faculdade_codigo[0].' - '.$sigla.'. '.$filename.' was created.<br>';
	}else{
		echo 'Error: '.$faculdade_codigo[0].' - '.$sigla.'. Could not retrieve data.<br>';
	}
}

?>
