<?php

require_once "common.php";

$LEMMA=[];

function loadLemmas(){
    global $LEMMA;
    
    echo "Loading lemma database\n";
    
    $fin=fopen("resources/lemma.txt","r");
    while(!feof($fin)){
	$line=fgets($fin);
	if($line===false)break;
	
	$line=trim($line);
	if(strlen($line)==0 || startsWith($line,";"))continue;
	
	$data=explode("->",$line);
	if(count($data)!=2)continue;
	
	$l=explode("/",trim($data[0]));
	if(count($l)!=2)continue;
	
	$lemma=$l[0];
	
	$words=explode(",",trim($data[1]));
	if(count($words)<1)continue;
	
	foreach($words as $w){
	    $w=trim($w);
	    if(strlen($w)==0)continue;
	    if(!isset($LEMMA[$w]))
		$LEMMA[$w]=$lemma;
	}
	
    }
    fclose($fin);
    
    echo "Loaded ".count($LEMMA)." lemmatized words\n";
}

function getLemma($w){
    global $LEMMA;
    if(isset($LEMMA[$w]))return $LEMMA[$w];
    //echo "LEMMA not found [$w]\n";
    return $w;
}
