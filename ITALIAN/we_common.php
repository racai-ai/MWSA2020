<?php

require_once "common.php";
require_once "vectors_oanc.php";

loadVectors();

// idee : renunta la cuvinte care apar rar (poate din wikipedia)
// explicatiile ar tb sa foloseasca cuvinte frecvente

function we_getData($data){
    global $stoplist,$wordStat,$DFILE,$DTYPE;
    
    $vec1=[];
    $vec1w=[];
    foreach($data['def1'] as $d){
	$words=getWords($d);
	$vec=[];
	$vecw=[];
	foreach($words as $w){
	    if(strlen($w)<2 || isset($stoplist[$w]))continue;
	
	    $vecw[]=mb_strtolower($w);
	    addToVec($vec,getWordVector(mb_strtolower($w)));
	}
	$vec1[]=$vec;
	$vec1w[]=$vecw;
    }

    $vec2=[];
    $vec2w=[];
    foreach($data['def2'] as $d){
	$words=getWords($d);
	$vec=[];
	$vecw=[];
	foreach($words as $w){
	    if(strlen($w)<2 || isset($stoplist[$w]))continue;
	
	    $vecw[]=mb_strtolower($w);
	    addToVec($vec,getWordVector(mb_strtolower($w)));
	}
	$vec2[]=$vec;
	$vec2w[]=$vecw;
    }
    
    $done=false;
    $max_dist=0;
    foreach($vec1 as $i=>$v1){
	foreach($vec2 as $j=>$v2){
	
	    $intersect=array_intersect($vec1w[$i],$vec2w[$j]);
	    $found=false;
	    if(count(array_diff($vec1w[$i],$intersect))<4 && count(array_diff($vec2w[$j],$intersect))<4){
	    foreach($intersect as $w){
		if(isset($wordStat[$w]) && $wordStat[$w]<20){$found=true;break;}
	    }
	    }
	    
	    if($found){
		$max_dist=1;
		    $done=true;
	    }
	
	    $dist=getDistance($v1,$v2);
	    if($dist>$max_dist)$max_dist=$dist;
	}
    }
    
    return ["dist"=>$max_dist];
}
