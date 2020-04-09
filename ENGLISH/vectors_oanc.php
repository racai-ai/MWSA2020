<?php

$wordVectors=[];
$zeroVector=[];

function loadVectors(){
    global $wordVectors,$zeroVector;

    echo "Loading OANC vectors\n";
    $fp=fopen("OANC_Written.csv","r");
    $sz=false;
    while(!feof($fp)){
	$line=fgetcsv($fp);
	if($line===false)break;
	if(count($line)!=101)continue;
	$data=array_slice($line,1);
	foreach($data as $k=>$v)$data[$k]=floatval($v);
	$wordVectors[$line[0]]=$data;
	if($sz===false)$sz=count($data);
    }
    fclose($fp);
    
    echo "Loaded ".count($wordVectors)." vectors\n";
    
    for($i=0;$i<$sz;$i++)$zeroVector[]=floatval(0.0);
}


function getWordVector($w){
    global $wordVectors,$zeroVector;
    
    if(!is_string($w))return false;
    
    if(!isset($wordVectors[$w]))return $zeroVector;
    
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
