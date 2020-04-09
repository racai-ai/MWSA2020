<?php

function getSenseScore($definition,$context){
    $d=str_replace(";"," ",$definition);
    $d=str_replace("'"," ",$definition);
    $w1=getWords($d);
    
    $in=array_intersect($w1,$context);
    return count($in);
}

function getSynonymsFromWN($word,$context){
    $data=file_get_contents("http://relate.racai.ro/index.php?path=rownws&word=${word}&sid=&wn=en");
    $data=@json_decode($data,TRUE);
    if(empty($data) || !isset($data['senses']))return [];
    
    $syn=[];
    $bestSense=-1;
    $bestScore=-1;
    foreach($data['senses'] as $k=>$sense){
	$score=getSenseScore($sense['definition'],$context);
	if($bestSense==-1 || $bestScore<$score){
	    $bestSense=$k;
	    $bestScore=$score;
	}
    }
    
    $sense=$data['senses'][$bestSense];
    foreach(explode(",",$sense['literal']) as $lit){
        if(!isset($syn[$lit]))$syn[$lit]=true;
    }
    $rels=array_flip(["hyponym","hypernym","verb_group"]);
    foreach($sense['relations'] as $rel){
	if(!isset($rels[$rel['rel']]))continue;
	foreach(explode(",",$rel['tliteral']) as $lit){
    	    if(!isset($syn[$lit]))$syn[$lit]=true;
	}
    }
    
    unset($syn[$word]);
    
    return array_keys($syn);
}

$SYNONYMSContext=[];
$SYNONYMSContext_changed=false;

function getSynonymsContext($word,$context){
    global $SYNONYMSContext,$SYNONYMSContext_changed;
    $cstring=implode("_",$context);
    if(isset($SYNONYMSContext[$word]) && isset($SYNONYMSContext[$word][$cstring]))
	return $SYNONYMSContext[$word][$cstring];
    
    if(!isset($SYNONYMSContext[$word]))$SYNONYMSContext[$word]=[];
    $SYNONYMSContext[$word][$cstring]=getSynonymsFromWN($word,$context);
    $SYNONYMSContext_changed=true;
    return $SYNONYMSContext[$word][$cstring];
}

function loadSynonymsContext(){
    global $SYNONYMSContext;
    if(is_file("synonyms_context.json"))
	$SYNONYMSContext=json_decode(file_get_contents("synonyms_context.json"),true);
}

function saveSynonymsContext(){
    global $SYNONYMSContext,$SYNONYMSContext_changed;
    if($SYNONYMSContext_changed)
	file_put_contents("synonyms_context.json",json_encode($SYNONYMSContext));
}

if(!function_exists("getSynonyms")){
    echo "WARN: Using synonyms_context\n";
    
    function getSynonyms($word,$context){return getSynonymsContext($word,$context);}
    function loadSynonyms(){loadSynonymsContext();}
    function saveSynonyms(){saveSynonymsContext();}
}
