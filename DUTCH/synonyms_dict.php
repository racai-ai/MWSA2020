<?php

$SYNONYMSDict=[];

function getSynonymsDict($word){
    global $SYNONYMSDict;
    if(isset($SYNONYMSDict[$word]))return $SYNONYMSDict[$word];
    return [];
}

function loadSynonymsDict(){
    global $SYNONYMSDict;
    if(is_file("resources/synonyms.json"))
	$SYNONYMSDict=json_decode(file_get_contents("resources/synonyms.json"),true);
}

function saveSynonymsDict(){
}

