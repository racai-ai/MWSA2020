<?php

require_once "common.php";

function detectSingleWord($def1){
    $ignore=array_flip(["to","a","an","the"]);

    $singleWord=0;
    $notSingleWord=false;
    foreach($def1 as $d1){
	$n=0;
	foreach(getWords($d1) as $w){
	    if(isset($ignore[$w]))continue;
	    $n++;
	}
	if($n==1)$singleWord++;
	else if($n>2)$notSingleWord=true;
    }

    if($notSingleWord)return false;
    if($singleWord>=count($def1)-1)return true;
    return false;
}


function split_getData($data){
    
    
    $d1=detectSingleWord($data['def1']);
    $d2=detectSingleWord($data['def2']);

    $type=0;    
    if($d1 && $d2)$type=0;
    else if($d1)$type=1;
    else if($d2)$type=1;
    else $type=2;
    
    return ["type"=>$type];
}
