<?php
/*
Scrapper de vagas no sifeup
@Author: Diogo Basto (ei09082@fe.up.pt)
*/

//params 
//curso 


//tratar parametros da pagina
	switch($_GET['curso'])
	{
		case 'feup-MIEIC': $faculdade_codigo[0]='feup';$curso_id[0]='742';break;
			//case 'feup-CINF':  $faculdade_codigo[0]='flup';$curso_id[0]='454'; $faculdade_codigo[1]='feup';$curso_id[1]='454';break;
			case 'feup-LCEEMG': $faculdade_codigo[0]='feup';$curso_id[0]='738';break;
			case 'feup-MEMG': $faculdade_codigo[0]='feup';$curso_id[0]='739';break;
			case 'feup-MIB': $faculdade_codigo[0]='feup';$curso_id[0]='728';break;
			case 'feup-MIEC': $faculdade_codigo[0]='feup';$curso_id[0]='740';break;
			case 'feup-MIEA': $faculdade_codigo[0]='feup';$curso_id[0]='726';break;
			case 'feup-MIEEC': $faculdade_codigo[0]='feup';$curso_id[0]='741';break;
			case 'feup-MIEIG': $faculdade_codigo[0]='feup';$curso_id[0]='725';break;
			case 'feup-MIEM': $faculdade_codigo[0]='feup';$curso_id[0]='743';break;
			case 'feup-MIEMM': $faculdade_codigo[0]='feup';$curso_id[0]='744';break;
			case 'feup-MIEQ': $faculdade_codigo[0]='feup';$curso_id[0]='745';break;
	
			case 'fcup-LAP': $faculdade_codigo[0]='fcup';$curso_id[0]='1011';break;
			case 'fcup-LAST': $faculdade_codigo[0]='fcup';$curso_id[0]='956';break;
			case 'fcup-LB': $faculdade_codigo[0]='fcup';$curso_id[0]='884';break;
			case 'fcup-LBQ': $faculdade_codigo[0]='fcup';$curso_id[0]='863';break;
			case 'fcup-LCC': $faculdade_codigo[0]='fcup';$curso_id[0]='885';break;
			case 'fcup-LCE': $faculdade_codigo[0]='fcup';$curso_id[0]='886';break;
			case 'fcup-LCTA': $faculdade_codigo[0]='fcup';$curso_id[0]='887';break;
			case 'fcup-LF': $faculdade_codigo[0]='fcup';$curso_id[0]='888';break;
			case 'fcup-LG': $faculdade_codigo[0]='fcup';$curso_id[0]='889';break;
			case 'fcup-LM': $faculdade_codigo[0]='fcup';$curso_id[0]='864';break;
			case 'fcup-LQ': $faculdade_codigo[0]='fcup';$curso_id[0]='865';break;
			case 'fcup-MIERS': $faculdade_codigo[0]='fcup';$curso_id[0]='870';break;
			case 'fcup-MIEF': $faculdade_codigo[0]='fcup';$curso_id[0]='890';break;

			case 'flup-ARQU': $faculdade_codigo[0]='flup';$curso_id[0]='339';break;
			case 'flup-CINF': $faculdade_codigo[0]='flup';$curso_id[0]='454'; $faculdade_codigo[1]='feup';$curso_id[1]='454';break;
			case 'flup-CC': $faculdade_codigo[0]='flup';$curso_id[0]='455';$faculdade_codigo[1]='fep';$curso_id[1]='455';$faculdade_codigo[2]='feup';$curso_id[2]='455';$faculdade_codigo[3]='fbaup';$curso_id[3]='455';break;
			case 'flup-CL': $faculdade_codigo[0]='flup';$curso_id[0]='460';break;
			case 'flup-EPL': $faculdade_codigo[0]='flup';$curso_id[0]='459';break;
			case 'flup-FILO': $faculdade_codigo[0]='flup';$curso_id[0]='340';break;
			case 'flup-GEOGR': $faculdade_codigo[0]='flup';$curso_id[0]='341';break;
			case 'flup-HISTO': $faculdade_codigo[0]='flup';$curso_id[0]='342';break;
			case 'flup-HART': $faculdade_codigo[0]='flup';$curso_id[0]='453';break;
			case 'flup-LA': $faculdade_codigo[0]='flup';$curso_id[0]='456';break;
			case 'flup-LRI': $faculdade_codigo[0]='flup';$curso_id[0]='458';break;
			case 'flup-LLC': $faculdade_codigo[0]='flup';$curso_id[0]='457';break;
			case 'flup-SOCI': $faculdade_codigo[0]='flup';$curso_id[0]='452';break;
			
			case 'fbaup-AP':$faculdade_codigo[0]='fbaup';$curso_id[0]='1315';break;
			case 'fbaup-DC':$faculdade_codigo[0]='fbaup';$curso_id[0]='1314';break;
	
			default : return FALSE; // Erro: Curso desconhecido
	}
	
	
//Query FEUP
	
	
	
	
	
	$fcicount=count($faculdade_codigo);
	for ($fci=0;$fci<$fcicount;$fci++)
	{
		$url= 'http://sigarra.up.pt/'.$faculdade_codigo[$fci].'/pt/it_geral.vagas?pv_curso_id='.$curso_id[$fci];
		
		$vagasresult = file_get_contents ( $url);

		//Parse para sacar os links	
		$dom = new DOMDocument;
		@$dom->loadHTML($vagasresult);
		
		$xp = new DOMXpath($dom);
		$nodestr = $xp->query('//table[@class="tabela"]/tr');
		
		$prevclasse="a";
		for ($i=2;$i<$nodestr->length;$i++)
		{
			$tr=$nodestr->item($i);
			$classe=$tr->attributes->getNamedItem('class')->nodeValue;
			$nodestd=$xp->query('.//td',$tr);
			if ($prevclasse!=$classe)
			{
				$j=2;
				$nomeaula=$nodestd->item(0)->nodeValue;
				$nomeaula=preg_replace("/\s*\([a-zA-Z0-9]+\)\s*/","",$nomeaula);	
				$prevclasse=$classe;
				//echo $nomeaula;
			}
			else $j=0;
			for(;$j<$nodestd->length;$j++)
			{
				$tdtext=$nodestd->item($j)->nodeValue;
				if ($tdtext!=" - ")
				{
					$turma=preg_replace("/\s*\([a-zA-Z0-9]+\)\s*/","",$tdtext);
					preg_match("/\(\d+\)/",$tdtext,$matches);
					$nrvagas=trim($matches[0]," ()");
					$vagas[$nomeaula][$turma]=$nrvagas;
					//echo $turma."-".$nrvagas."<br>";
				}
			}
			
		
		}
	}
	
	echo json_encode($vagas);

?>