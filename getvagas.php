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