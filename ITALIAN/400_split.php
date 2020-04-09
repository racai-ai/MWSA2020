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

$single_single=[];
$single_multiple=[];
$multiple_single=[];
$multiple_multiple=[];

function processWord($data,$line,$lnum){
    global $single_single,$single_multiple,$multiple_single,$multiple_multiple,$DFILE;
    
    
    $d1=detectSingleWord($data['def1']);
    $d2=detectSingleWord($data['def2']);
    
    if($d1 && $d2){
	$single_single[$lnum]=true;
	file_put_contents("split/SS_$DFILE.tsv",implode("\t",$line)."\n",FILE_APPEND);
    }else if($d1){
	$single_multiple[$lnum]=true;
	file_put_contents("split/SM_$DFILE.tsv",implode("\t",$line)."\n",FILE_APPEND);
    }else if($d2){
	$multiple_single[$lnum]=true;
	file_put_contents("split/MS_$DFILE.tsv",implode("\t",$line)."\n",FILE_APPEND);
    }else{
	 $multiple_multiple[$lnum]=true;
	file_put_contents("split/MM_$DFILE.tsv",implode("\t",$line)."\n",FILE_APPEND);
    }
}

file_put_contents("split/SS_$DFILE.tsv","");
file_put_contents("split/SM_$DFILE.tsv","");
file_put_contents("split/MS_$DFILE.tsv","");
file_put_contents("split/MM_$DFILE.tsv","");
processTSV("$DTYPE/$DFILE.tsv");

echo "SS=".count($single_single)." SM=".count($single_multiple)." MS=".count($multiple_single)." MM=".count($multiple_multiple)."\n";
