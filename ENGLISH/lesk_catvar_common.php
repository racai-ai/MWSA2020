<?php

use markfullmer\porter2\Porter2;

//loadCatvar();

$ignoreCatvar=array_flip(["the","to","not"]);

function catvarIntersect($v1,$v2){

    $intersect=0;
    foreach($v1 as $a){
	foreach($v2 as $b){
	    if(count(array_intersect($a,$b))>0)$intersect++;
	}
    }
    
    return $intersect;

}

function leskCatvar_getData($data){
    global $stoplist,$DFILE,$DTYPE,$ignoreCatvar;
    
    $vec1w=[];
    foreach($data['def1'] as $d){
	$words=getWords($d);
	$vecw=[];
	foreach($words as $w){
	    $l=getCatvar($w);
	    if(count($l)==0 || isset($ignoreCatvar[$w]))continue;
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
	    if(count($l)==0 || isset($ignoreCatvar[$w]))continue;
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
    
    
    return [
	"intersect" => $max_dist
    ];
}
