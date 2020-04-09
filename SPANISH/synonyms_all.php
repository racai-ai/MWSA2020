<?php

function getSynonyms($word,$context){
    return array_unique(array_merge(getSynonymsDict($word,$context),getSynonymsContext($word,$context)));
}

function loadSynonyms(){
    loadSynonymsDict();
    loadSynonymsContext();
}

function saveSynonyms(){
    saveSynonymsDict();
    saveSynonymsContext();
}

require_once "synonyms_context.php";
require_once "synonyms_dict.php";

$newsyns=[
["someone", "person","boy","girl","man","woman","craftsman","one"],
["device","something","thing"],
["make","render","pay"],
["area","spot"],
["protuberance","organ"],
["property","condition","fact","quality","symptom","trait"],
["murder","crime"],
["manner","behavior","behaviour"], // american english=behavior, commonwealth english=behaviour
["speak","plead","defend"],
["order","body","organization","unit"],
["workshop","place"],
["process","production"],
["wait","lie"],
["coin","money"], // in wordnet coin=metal_money
["quantity","bundle"],
["statement","expression"],
];

foreach($newsyns as $data){
    foreach($data as $b){
        if(!isset($SYNONYMSDict[$b]))$SYNONYMSDict[$b]=$data;
        else $SYNONYMSDict[$b]=array_merge($SYNONYMSDict[$b],$data);
    }
}

