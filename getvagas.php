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
		default : echo 'Error';exit();
	}
	
	
//Query FEUP
	
	
	
	
	
	
	$url= 'http://sigarra.up.pt/feup/pt/it_geral.vagas?pv_curso_id='.$curso_id;
	
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
	
	
	echo json_encode($vagas);

?>