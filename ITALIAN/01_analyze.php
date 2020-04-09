<?php

require_once "common.php";

$letters=[];

function processLetters($w){
    global $letters;

    for($i=0;$i<mb_strlen($w);$i++){
        $l=mb_substr($w,$i,1);
        if($l==' ')continue;
        if(!isset($letters[$l]))$letters[$l]=1;
        else $letters[$l]++;
    }
}

$wordsStat=[];
$wordsDefStat=[];

function makeWordStat($d){
    global $wordsStat;
    $words=getWords($d);
    foreach($words as $word){
	if(!isset($wordsStat[$word]))$wordsStat[$word]=0;
	$wordsStat[$word]++;
    }
}

function makeWordDefStat($d){
    global $wordsDefStat;
    $words=getWords($d);
    foreach($words as $word){
	if(!isset($wordsDefStat[$word]))$wordsDefStat[$word]=0;
	$wordsDefStat[$word]++;
    }
}

function processWord($data,$line){

    processLetters($data['word']);
    makeWordDefStat($data['word']);
    foreach($data['def1'] as $d){
	processLetters($d);
	makeWordStat($d);
    }
    foreach($data['def2'] as $d){
	processLetters($d);
	makeWordStat($d);
    }

}


$num=processTSV("$DTYPE/$DFILE.tsv");
$max=round((floatval($num)/100.0)*3.0);
echo "Make stoplist >$max\n";

$stoplist=[];
foreach($wordsStat as $w=>$v)if($v>$max)$stoplist[$w]=1;

saveStat($letters,"my/$DFILE/letters.txt");
saveStat($wordsStat,"my/$DFILE/words.txt");
saveStat($wordsDefStat,"my/$DFILE/words_def.txt");
saveStat($stoplist,"my/$DFILE/stoplist.txt");
