<?php

require_once "common.php";

$sentences=[];
$pairs=[];
$sentNum=0;

function addPair($n1,$n2){
    global $pairs;

    foreach($pairs as $pair)if($pair[0]==$n1 && $pair[1]==$n2)return ;
    $pairs[]=[$n1,$n2];

}

function processWord($data,$line,$lnum){
    global $sentences,$pairs,$sentNum;
    
    foreach($data['def1'] as $d1){
    
	if(strlen($d1)==0){var_dump($data);die("err2");}
    
	if(!isset($sentences[$d1])){$sentences[$d1]=$sentNum; $sentNum++;}
	$n1=$sentences[$d1];
	
	foreach($data['def2'] as $d2){
	    if(strlen($d2)==0)die("err3");

	    if(!isset($sentences[$d2])){$sentences[$d2]=$sentNum; $sentNum++;}
	    $n2=$sentences[$d2];
	
	    addPair($n1,$n2);
	
	}
    }
}

foreach(["train","test"] as $DTYPE){

$sentences=[];
$pairs=[];
$sentNum=0;

$fp=fopen("my/sentences/${DTYPE}_${DFILE}_sentences.txt","r");
if($fp!==false){
    $isData=false;
    while(!feof($fp)){
	$line=fgets($fp);
	if($line===false)break;
	$data=explode("\t",trim($line));
	if(count($data)!==2)continue;
	$sentences[$data[1]]=intval($data[0]);
	$sentNum=max($sentNum,intval($data[0]));
	$isData=true;
    }
    if($isData)$sentNum++;
    fclose($fp);
}

$fp=fopen("my/sentences/${DTYPE}_${DFILE}_pairs.txt","r");
if($fp!==false){
    while(!feof($fp)){
	$line=fgets($fp);
	if($line===false)break;
	$data=explode("\t",trim($line));
	if(count($data)!==2)continue;
	$pairs[]=[intval($data[0]),intval($data[1])];
    }
    fclose($fp);
}

processTSV("$DTYPE/$DFILE.tsv");

@mkdir("my/sentences");
$fout=fopen("my/sentences/${DTYPE}_${DFILE}_sentences.txt","w");
foreach($sentences as $sent=>$id){
    fwrite($fout,"$id\t$sent\n");
    if(empty($sent))die("err");
}
fclose($fout);

$fout=fopen("my/sentences/${DTYPE}_${DFILE}_pairs.txt","w");
foreach($pairs as $p)fwrite($fout,"${p[0]}\t${p[1]}\n");
fclose($fout);

}
