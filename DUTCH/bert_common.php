<?php

require_once "common.php";

$sentences=[];
$pairs=[];
$pairs_dist=[];
$bertDistAll=[];

function bert_loadSentences(){
    global $DTYPE,$DFILE,$sentences;
    $sentences=[];
    foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_sentences.txt")) as $line){
	$data=explode("\t",$line);
	if(count($data)!==2)continue;
	$id=intval($data[0]);
	$sent=$data[1];
	$sentences[$sent]=$id;
    }
}

function bert_loadPairs(){
    global $DTYPE,$DFILE,$pairs;
    $pairs=[];
    foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_pairs.txt")) as $line){
	$data=explode("\t",$line);
	if(count($data)!==2)continue;
	$n1=intval($data[0]);
	$n2=intval($data[1]);
	$pairs[]=[$n1,$n2];
    }
}

function bert_loadPairsDist(){
    global $DTYPE,$DFILE,$pairs_dist,$pairs,$sentences;
    $pairs_dist=[];
    $n=0;
    foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_pairs_dist.txt")) as $line){
	$pairs_dist[$pairs[$n][0]."_".$pairs[$n][1]]=floatval($line);
	$n++;
    }
}

function bert_loadDistAll(){
    global $bertDistAll,$DTYPE,$DFILE;
    $bertDistAll=[];
    foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_all_dist.txt")) as $line){
	$bertDistAll[]=floatval($line);
    }
}

function bert_getData($data){
    global $pairs,$pairs_dist,$sentences;

    $minDist=1;
    $maxDist=0;
    $sum=0.0;
    $num=0;
    foreach($data['def1'] as $d1){
	if(!isset($sentences[$d1])){var_dump($d1);die("Unknown BERT sentence");}
	$n1=$sentences[$d1];
	foreach($data['def2'] as $d2){
	    $n2=$sentences[$d2];
	    $d=$pairs_dist[$n1."_".$n2];
	    if($d<$minDist)$minDist=$d;
	    if($d>$maxDist)$maxDist=$d;
	    $sum+=$d;
	    $num++;
	}
    }
    
    $med=0;
    if($num==0){var_dump($data);}
    else $med=$sum/floatval($num);
    
    return ["minDist"=>$minDist,"maxDist"=>$maxDist,"medDist"=>$med];
}

