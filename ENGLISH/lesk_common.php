<?php

require_once "common.php";
require_once "Porter2.php";
//require_once "vectors.php";

// idee : renunta la cuvinte care apar rar (poate din wikipedia)
// explicatiile ar tb sa foloseasca cuvinte frecvente

$stoplist=loadStat("my/$DFILE/stoplist.txt");
//$wordStat=loadStat("my/$DFILE/words.txt");

use markfullmer\porter2\Porter2;

function lesk_getData($data,$useLemma=false,$useStem=false){
    global $stoplist;
    
    $vec1w=[];
    $maxCount1=0;
    $minCount1=999999;
    $totalCount1=0;
    foreach($data['def1'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    if(strlen($w)<2 || isset($stoplist[$w]))continue;
	    if(!$useLemma){
		if($useStem){
		    $vecw[]=Porter2::stem(mb_strtolower($w));
		}else
		    $vecw[]=mb_strtolower($w);
	    }else 
		$vecw[]=getLemma(mb_strtolower($w));
	}
	$vec1w[]=$vecw;
	if(count($vecw)>$maxCount1)$maxCount1=count($vecw);
	if(count($vecw)<$minCount1)$minCount1=count($vecw);
	$totalCount1+=count($vecw);
    }

    $vec2w=[];
    $maxCount2=0;
    $minCount2=999999;
    $totalCount2=0;
    foreach($data['def2'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    if(strlen($w)<2 || isset($stoplist[$w]))continue;
	
	    if(!$useLemma){
		if($useStem){
		    $vecw[]=Porter2::stem(mb_strtolower($w));
		}else
		    $vecw[]=mb_strtolower($w);
	    }else 
		$vecw[]=getLemma(mb_strtolower($w));
	}
	$vec2w[]=$vecw;
	if(count($vecw)>$maxCount2)$maxCount2=count($vecw);
	if(count($vecw)<$minCount2)$minCount2=count($vecw);
	$totalCount2+=count($vecw);
    }
    
    $max_dist=0;
    foreach($vec1w as $i=>$v1){
	foreach($vec2w as $j=>$v2){
	
	    $intersect=array_intersect($vec1w[$i],$vec2w[$j]);
	    if(count($intersect)>$max_dist)$max_dist=count($intersect);
	}
    }
    
    return [
	"intersect"=>$max_dist,
	"maxCount1"=>$maxCount1,
	"maxCount2"=>$maxCount2,
	"minCount1"=>$minCount1,
	"minCount2"=>$minCount2,
	"totalCount1"=>$totalCount1,
	"totalCount2"=>$totalCount2
    ];
}
