<?php

require_once "common.php";
//require_once "vectors.php";
require_once "verbnet.php";
require_once "lemma.php";
require_once "pos.php";
require_once "phrase.php";

$stoplist=loadStat("my/$DFILE/stoplist.txt");

echo "Loading VERBNET\n";
loadVerbnet();
echo "Loading LEMMA list\n";
loadLemmas();
echo "Loading POS tags\n";
loadPOSTags("resources/oanc.pos");
echo "Running\n";

/*$tokens=tagWords(getWords("I went to see the doctor."));
echo "TOKENS:\n".displayPOS($tokens)."\n";
$phrases=getPhrases($tokens);
echo "PHRASES:\n".displayPhrases($phrases)."\n";
$frame=getVerbnetFrame($phrases);
echo "MATCH:\n".displayVerbnetFrame($frame)."\n";
die();
*/

function getVNFrame($d){
    global $VERBNET_CLASSES,$VERBNET;

    logExplain("vn1",false,$d);

    $words=getWords($d);
    $tokens=tagWords($words);
    logExplain("vn1",false,displayPOS($tokens));
    $phrases=getPhrases($tokens);
    logExplain("vn1",false,displayPhrases($phrases));
    
    $frame=getVerbnetFrame($phrases);
    logExplain("vn1",false,displayVerbnetFrame($frame));

    return $frame;
}

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
	//$classes2=array_merge($classes2,getClass($d));
	$classes2=array_merge($classes2,getClass($d));
    }

//    if(count($classes2)==0)var_dump($def);

    return $classes2;
}

function getFrameClass($frame){
    global $VERBNET_CLASSES;
    foreach($frame as $f){
	if($f['type']=='VERB'){
	    return $VERBNET_CLASSES[getLemma(mb_strtolower($f['w']))];
	}
    }
    return false;
}

function processWord($data,$line,$lnum){
    global $stoplist,$DFILE,$DTYPE,$VERBNET,$VERBNET_CLASSES;

    logExplain("vn1",$lnum,"START");

    $classes1=getClassesForDefinitions($data['def1']);
    $classes2=getClassesForDefinitions($data['def2']);
    $intersect=array_intersect($classes1,$classes2);

    $def1=$data['def1'];
    $def2=$data['def2'];
    $found=false;
    foreach($def1 as $d1){
	$f1=getVNFrame($d1);
	if(empty($f1))continue;
	$c1=getFrameClass($f1);
	echo "1 => ".displayVerbnetFrame($f1)."\n";
	foreach($def2 as $d2){
	    $f2=getVNFrame($d2);
	    if(empty($f2))continue;
	    $c2=getFrameClass($f2);
	    if(count(array_intersect($c1,$c2))==0)continue;
	    
	    echo "2 => ".displayVerbnetFrame($f2)."\n";
	    $found=true;
	}
	
	echo "**********\n";
    }

    //if($found)die();
    logExplain("vn1",$lnum,var_export($intersect,true));

    $decision="none";
    if($found)$decision="exact";
    else if(count($classes1)==0 || count($classes2)==0)$decision="narrower";

    file_put_contents("my/vn1.$DFILE.tsv",
	implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}

//loadVectorsGZ("cc.en.300.vec.gz");
file_put_contents("my/vn1.${DFILE}.tsv","");
$num=processTSV("my/preprocess/$DTYPE/$DFILE.tsv","processWord",false);

echo "UNKNOWN escape sequences:\n";
var_dump($UNKNOWN_ESCAPE);
