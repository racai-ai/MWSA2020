<?php

require_once "common.php";
//require_once "vectors.php";
require_once "verbnet.php";
require_once "lemma.php";
require_once "pos.php";
require_once "phrase.php";
require_once "graph.php";
require_once "synonyms.php";

loadSynonyms();

$stoplist=loadStat("my/$DFILE/stoplist.txt");

$stoplist=array_merge($stoplist,["make"=>true]);
unset($stoplist['one']);
unset($stoplist['have']);

$stoplist=array_flip(["to","a","the","in","of","like",
"especially","causing","cause","capable","resulting","selected","for","try","by","if","or","as",
"with","purpose","etc","esp","relating","pertaining"]);

$lemmaStoplist=array_flip(["make"]);

//echo "Loading VERBNET\n";
//loadVerbnet();
echo "Loading LEMMA list\n";
loadLemmas();
//echo "Loading POS tags\n";
//loadPOSTags("resources/oanc.pos");
echo "Running\n";

function addSingleDef($def,$type){
    global $stoplist,$lemmaStoplist;
    
    $words=getWords($def);
    $prev=false;
    foreach($words as $w){
	$w=mb_strtolower($w);
	if(strlen($w)<=2 || isset($stoplist[$w]))continue;
	
	addNode($w,$type);
	
	$l=getLemma($w);
	addNode($l,"LEMMA");
	
	addEdge($w,$l);
	
	if(!isset($lemmaStoplist[$l])){
	    $syn=getSynonyms($l);
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
    global $GRAPH,$stoplist;
    
    emptyGraph();
    addSingleDef($d1,"D1");
    addSingleDef($d2,"D2");
    propagateLabel("D1");

    $wordsD2=[];
    $words=getWords($d2);
    foreach($words as $w){
	$w=mb_strtolower($w);
	if(strlen($w)<=2 || isset($stoplist[$w]))continue;
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
	if(strlen($w)<=2 || isset($stoplist[$w]))continue;
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



function processWord($data,$line,$lnum){
    global $stoplist,$DFILE,$DTYPE,$VERBNET,$VERBNET_CLASSES,$GRAPH;

    logExplain("graph1",$lnum,"START");

    $dist=-1;
    foreach($data['def1'] as $d1){
	foreach($data['def2'] as $d2){
	    $d=processDefPair($d1,$d2);
	    logExplain("graph1",false,"$d [$d1] [$d2]");
	    $c1=getCountByLabel('D1');
	    $c2=getCountByLabel('D2');
	    logExplain("graph1",false,"count(D1)=$c1 count(D2)=$c2");
	    $ma=max($c1,$c2);
	    $mi=min($c1,$c2);
	    if($ma-$mi>1)$d=-1;
	    logExplain("graph1",false,json_encode($GRAPH,JSON_PRETTY_PRINT));
	    if($dist==-1 || ($d>=0 && $d<$dist))$dist=$d;
	}
    }

    //if($found)die();
    logExplain("graph1",$lnum,$dist);

    $decision="none";
    if($dist>-1 && $dist<2)$decision="exact";
    //else if($dist>-1 && $dist<3)$decision="narrower";
    //else if($dist>-1 && $dist<4)$decision="related";

    file_put_contents("my/graph1.$DFILE.tsv",
	implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}

//loadVectorsGZ("cc.en.300.vec.gz");
file_put_contents("my/graph1.${DFILE}.tsv","");
$num=processTSV("my/preprocess/$DTYPE/$DFILE.tsv","processWord",false);

echo "UNKNOWN escape sequences:\n";
var_dump($UNKNOWN_ESCAPE);

saveSynonyms();
