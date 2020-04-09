<?php

require_once "common.php";
require_once "Porter2.php";
require_once "catvar.php";

$stoplist=loadStat("my/$DFILE/stoplist.txt");
//$wordStat=loadStat("my/$DFILE/words.txt");
use markfullmer\porter2\Porter2;

loadCatvar();

$ignore=array_flip(["the","to","not"]);

function catvarIntersect($v1,$v2){

    $intersect=0;
    foreach($v1 as $a){
	foreach($v2 as $b){
	    if(count(array_intersect($a,$b))>0)$intersect++;
	}
    }
    
    return $intersect;

}

function processWord($data,$line){
    global $stoplist,$DFILE,$DTYPE,$ignore;
    
    $vec1w=[];
    foreach($data['def1'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    $l=getCatvar($w);
	    if(count($l)==0 || isset($ignore[$w]))continue;
	    $vecw[]=$l;
	}
	$vec1w[]=$vecw;
    }

    $vec2w=[];
    foreach($data['def2'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    $l=getCatvar($w);
	    if(count($l)==0 || isset($ignore[$w]))continue;
	    $vecw[]=$l;
	}
	$vec2w[]=$vecw;
    }
    
    //echo $data['word']." ".$data['type']."\n";
    $max_dist=0;
    $numw=0;
    foreach($vec1w as $i=>$v1){
	foreach($vec2w as $j=>$v2){
	
	    $intersect=catvarIntersect($vec1w[$i],$vec2w[$j]);
	    if($intersect>$max_dist){
		$max_dist=$intersect;
		$numw=max(count($vec1w[$i]),count($vec2w[$j]));
	    }
	}
    }
    
	$decision="none";
	if($max_dist>5 || $max_dist==$numw && $max_dist>2)$decision="exact";
	else if($max_dist>4)$decision="narrower";
	else if($max_dist>3)$decision="related";

	file_put_contents("my/lesk_catvar.$DFILE.tsv",implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}



//loadVectorsGZ("cc.en.300.vec.gz");
file_put_contents("my/lesk_catvar.${DFILE}.tsv","");
$num=processTSV("my/preprocess/$DTYPE/$DFILE.tsv","processWord",false);
