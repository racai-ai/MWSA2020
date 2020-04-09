<?php

require_once "common.php";
//require_once "vectors.php";
require_once "verbnet.php";
require_once "lemma.php";

$stoplist=loadStat("my/$DFILE/stoplist.txt");

echo "Loading VERBNET\n";
loadVerbnet();
echo "Loading LEMMA list\n";
loadLemmas();

function getClass($d){
    global $VERBNET_CLASSES;

    $words=getWords($d);
    $vecw=[];
    foreach($words as $w){
        if(strlen($w)<2 || isset($stoplist[$w]))continue;

        $w=getLemma(mb_strtolower($w));
        if(isset($VERBNET_CLASSES[$w]))return $VERBNET_CLASSES[$w];
    }

    //echo "Unknown class ".var_export($d,true)."\n";
    return [];
}

function getClassesForDefinitions($def){
    $classes2=[];
    foreach($def as $d){
	$classes2=array_merge($classes2,getClass($d));
    }

//    if(count($classes2)==0)var_dump($def);

    return $classes2;
}

function processWord($data,$line){
    global $stoplist,$DFILE,$DTYPE,$VERBNET,$VERBNET_CLASSES;

    $classes1=getClassesForDefinitions($data['def1']);
    $classes2=getClassesForDefinitions($data['def2']);
    $intersect=array_intersect($classes1,$classes2);

    $decision="none";
    if(count($intersect)>0)$decision="exact";
    else if(count($classes1)==0 || count($classes2)==0)$decision="narrower";

    file_put_contents("my/vn.$DFILE.tsv",
	implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}

//loadVectorsGZ("cc.en.300.vec.gz");
file_put_contents("my/vn.${DFILE}.tsv","");
$num=processTSV("my/preprocess/$DTYPE/$DFILE.tsv","processWord",false);

echo "UNKNOWN escape sequences:\n";
var_dump($UNKNOWN_ESCAPE);
