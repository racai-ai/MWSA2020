<?php

require_once "common.php";
require_once "vendor/autoload.php";

// idee : renunta la cuvinte care apar rar (poate din wikipedia)
// explicatiile ar tb sa foloseasca cuvinte frecvente

$stoplist=loadStat("my/$DFILE/stoplist.txt");
//$wordStat=loadStat("my/$DFILE/words.txt");

use Wamania\Snowball\StemmerFactory;

$stemmer = StemmerFactory::create('it');

function processWord($data,$line){
    global $stoplist,$DFILE,$DTYPE,$stemmer;
    
    $vec1w=[];
    foreach($data['def1'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    $l=$stemmer->stem(mb_strtolower($w));
	    if(strlen($w)<2 || isset($stoplist[mb_strtolower($w)]))continue;// || isset($stoplist[$l]))continue;
	
	    $vecw[]=$l;
	}
	$vec1w[]=$vecw;
    }

    $vec2w=[];
    foreach($data['def2'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    $l=$stemmer->stem(mb_strtolower($w));
	    if(strlen($w)<2 || isset($stoplist[mb_strtolower($w)]))continue;// || isset($stoplist[$l]))continue;
	
	    $vecw[]=$l;
	}
	$vec2w[]=$vecw;
    }
    
    //echo $data['word']." ".$data['type']."\n";
    $max_dist=0;
    $numw=0;
    foreach($vec1w as $i=>$v1){
	foreach($vec2w as $j=>$v2){
	
	    $intersect=array_intersect($vec1w[$i],$vec2w[$j]);
	    if(count($intersect)>$max_dist){
		$max_dist=count($intersect);
		$numw=max(count($vec1w[$i]),count($vec2w[$j]));
	    }
	}
    }
    
	$decision="none";
	if($max_dist>3 || $max_dist==$numw && $max_dist>2)$decision="exact";
	else if($max_dist>2)$decision="narrower";
	else if($max_dist>1)$decision="related";

	file_put_contents("my/lesk_stem.$DFILE.tsv",implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}



//loadVectorsGZ("cc.en.300.vec.gz");
file_put_contents("my/lesk_stem.${DFILE}.tsv","");
$num=processTSV("my/preprocess/$DTYPE/$DFILE.tsv","processWord",false);
