<?php

function getSynonymsFromWN($word){
    $data=file_get_contents("http://relate.racai.ro/index.php?path=rownws&word=${word}&sid=&wn=en");
    $data=@json_decode($data,TRUE);
    if(empty($data) || !isset($data['senses']))return [];
    
    $syn=[];
    foreach($data['senses'] as $sense){
	foreach(explode(",",$sense['literal']) as $lit){
	    if(!isset($syn[$lit]))$syn[$lit]=true;
	}
    }
    
    unset($syn[$word]);
    
    return array_keys($syn);
}

$SYNONYMS=[];
$SYNONYMS_changed=false;

function getSynonyms($word){
    global $SYNONYMS,$SYNONYMS_changed;
    if(isset($SYNONYMS[$word]))return $SYNONYMS[$word];
    
    $SYNONYMS[$word]=getSynonymsFromWN($word);
    $SYNONYMS_changed=true;
    return $SYNONYMS[$word];
}

function loadSynonyms(){
    global $SYNONYMS;
    if(is_file("synonyms.json"))
	$SYNONYMS=json_decode(file_get_contents("synonyms.json"),true);
}

function saveSynonyms(){
    global $SYNONYMS,$SYNONYMS_changed;
    if($SYNONYMS_changed)
	file_put_contents("synonyms.json",json_encode($SYNONYMS));
}

