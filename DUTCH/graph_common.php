<?php

require_once "common.php";
//require_once "vectors.php";
require_once "verbnet.php";
require_once "lemma.php";
require_once "pos.php";
require_once "phrase.php";
require_once "graph.php";
//require_once "synonyms.php";
require_once "synonyms_context.php";

loadSynonyms();


$graphStoplist=array_flip(["to","a","the","in","of","like",
"especially","causing","cause","capable","resulting","selected","for","try","by","if","or","as",
"with","purpose","etc","esp","relating","pertaining"]);

$lemmaStoplist=[];//array_flip(["make","be"]);


function addSingleDef($def,$type){
    global $graphStoplist,$lemmaStoplist;
    
    $words=getWords($def);
    $prev=false;
    foreach($words as $w){
	$w=mb_strtolower($w);
	if(strlen($w)<2 || isset($graphStoplist[$w]))continue;
	
	addNode($w,$type);
	
	$l=getLemma($w);
	addNode($l,"LEMMA");
	
	addEdge($w,$l);
	
	if(!isset($lemmaStoplist[$l])){
	    $syn=getSynonyms($l,$words);
	    foreach($syn as $s){
		addNode($s,"SYN");
		addEdge($l,$s);
	    }
	}
	
	if($prev!==false)addEdge($w,$prev);
	$prev=$w;
    }
}

function processDefPair($d1,$d2){
    global $GRAPH,$graphStoplist;
    
    emptyGraph();
    addSingleDef($d1,"D1");
    addSingleDef($d2,"D2");
    propagateLabel("D1");

    $wordsD2=[];
    $words=getWords($d2);
    foreach($words as $w){
	$w=mb_strtolower($w);
	if(strlen($w)<=2 || isset($graphStoplist[$w]))continue;
	$wordsD2[]=$w;
    }
    $distVector=computeDistancesFromWords($wordsD2);
    ksort($distVector);
    $dist1=-1; 
    if(count($distVector)>0 && array_keys($distVector)[0]!=-1)
	$dist1=array_keys($distVector)[count($distVector)-1];
    
    propagateLabel("D2");
    $wordsD1=[];
    $words=getWords($d1);
    foreach($words as $w){
	$w=mb_strtolower($w);
	if(strlen($w)<=2 || isset($graphStoplist[$w]))continue;
	$wordsD1[]=$w;
    }
    $distVector=computeDistancesFromWords($wordsD1);
    ksort($distVector);
    $dist2=-1; 
    if(count($distVector)>0 && array_keys($distVector)[0]!=-1)
	$dist2=array_keys($distVector)[count($distVector)-1];
    
    $d=$dist1;
    if($dist2==-1 || $dist2>$d)$d=$dist2;
    return $d;
}



function graph_getData($data){

    $dist=-1;
    $def1="";
    $def2="";
    foreach($data['def1'] as $d1){
	$def1.=" ".$d1;
    }
    $def1=trim($def1);
    
    foreach($data['def2'] as $d2){
	$def2.=" ".$d2;
    }
    $def2=trim($def2);
    
    $d=processDefPair($def1,$def2);
    $c1=getCountByLabel('D1');
    $c2=getCountByLabel('D2');
    $ma=max($c1,$c2);
    $mi=min($c1,$c2);
    //if($ma-$mi>6)$d=-1;
    //if($dist==-1 || ($d>=0 && $d<$dist))$dist=$d;
    
    return ["dist"=>$d,"ma"=>$ma,"mi"=>$mi];
}
