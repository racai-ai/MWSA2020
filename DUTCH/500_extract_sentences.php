<?php

require_once "common.php";

$sentences=[];
$pairs=[];
$sentNum=0;

function processWord($data,$line,$lnum){
    global $sentences,$pairs,$sentNum;
    
    foreach($data['def1'] as $d1){
	if(!isset($sentences[$d1])){$sentences[$d1]=$sentNum; $sentNum++;}
	$n1=$sentences[$d1];
	
	foreach($data['def2'] as $d2){
	    if(!isset($sentences[$d2])){$sentences[$d2]=$sentNum; $sentNum++;}
	    $n2=$sentences[$d2];
	
	    $pairs[]=[$n1,$n2];
	
	}
    }
}

processTSV("$DTYPE/$DFILE.tsv");

@mkdir("my/sentences");
$fout=fopen("my/sentences/${DTYPE}_${DFILE}_sentences.txt","w");
foreach($sentences as $sent=>$id)fwrite($fout,"$id\t$sent\n");
fclose($fout);

$fout=fopen("my/sentences/${DTYPE}_${DFILE}_pairs.txt","w");
foreach($pairs as $p)fwrite($fout,"${p[0]}\t${p[1]}\n");
fclose($fout);
