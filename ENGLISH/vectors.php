<?php

$wordVectors=[];

function loadVectorsGZ($fname){
    global $wordVectors;

    echo "Loading word vectors from [$fname] ... ";
    
    $fp=gzopen($fname,"r");
    
    $n=0;
    while(!gzeof($fp)){
	$line=gzgets($fp);
	if($line===false)break;
	
	$data=explode(" ",$line);
	if(count($data)<10)continue;
	
	$n++;
	if($n%1000==0)echo "Line $n\n";
	
	$wordVectors[$data[0]]=[];
	for($i=1;$i<count($data);$i++)
	    $wordVectors[$data[0]][]=floatval($data[$i]);
    }
    
    gzclose($fp);
    
    echo "DONE\n";
}

function getWordVectorFromService($w){
    $data=file_get_contents("http://127.0.0.1:8023/wordvectors_get?w1=$w");
    if(!is_string($data) || empty($data))return false;
    $data=explode(" ",trim($data));
    if(count($data)<100)return false;
    
    return array_slice($data,1);
}

function getWordVector($w){
    global $wordVectors;
    
    if(!is_string($w))return false;
    
    if(!isset($wordVectors[$w])){
	$wordVectors[$w]=getWordVectorFromService($w);
    }
    
    return $wordVectors[$w];
}

function addToVec(&$vec,$vec2){
    if(!is_array($vec2))return false;
    
    if(empty($vec)){$vec=$vec2; return true;}
    
    for($i=0;$i<count($vec);$i++){
	$vec[$i]+=$vec2[$i];
    }
    return true;
}

function getDistance($vec1,$vec2){
    if(!is_array($vec1) || !is_array($vec2) || empty($vec1) || empty($vec2))return false;

    $S1=0;
    $S2=0;
    $S3=0;
    for($i=0;$i<count($vec1);$i++){
	$S1+=$vec1[$i] * $vec2[$i];
	$S2+=$vec1[$i] * $vec1[$i];
	$S3+=$vec2[$i] * $vec2[$i];
    }
    
    if($S2==0 || $S3==0)return 0.0;
    
    $d=$S1/(sqrt($S2)*sqrt($S3));
    
    return $d;
}
