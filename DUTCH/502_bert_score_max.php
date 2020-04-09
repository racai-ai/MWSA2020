<?php

require_once "common.php";

$sentences=[];
foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_sentences.txt")) as $line){
    $data=explode("\t",$line);
    if(count($data)!==2)continue;
    $id=intval($data[0]);
    $sent=$data[1];
    $sentences[$sent]=$id;
}

$pairs=[];
foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_pairs.txt")) as $line){
    $data=explode("\t",$line);
    if(count($data)!==2)continue;
    $n1=intval($data[0]);
    $n2=intval($data[1]);
    $pairs[]=[$n1,$n2];
}

$pairs_dist=[];
$n=0;
foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_pairs_dist.txt")) as $line){
    $pairs_dist[$pairs[$n][0]."_".$pairs[$n][1]]=floatval($line);
    $n++;
}


function processWord($data,$line,$lnum){
    global $pairs_dist,$sentences,$DFILE;

    $maxDist=0;
    foreach($data['def1'] as $d1){
	if(!isset($sentences[$d1])){var_dump($d1);die();}
	$n1=$sentences[$d1];
	foreach($data['def2'] as $d2){
	    $n2=$sentences[$d2];
	    $d=$pairs_dist[$n1."_".$n2];
	    if($d>$maxDist)$maxDist=$d;
	}
    }
    
    
    $decision="none";
    if($maxDist<0.35)$decision="exact";
    //else if($maxDist<0.25)$decision="narrower";
    //else if($maxDist<0.35)$decision="broader";
    //else if($maxDist<0.4)$decision="related";
    
    file_put_contents("my/bertm.$DFILE.tsv",implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}

file_put_contents("my/bertm.${DFILE}.tsv","");
processTSV("$DTYPE/$DFILE.tsv");
