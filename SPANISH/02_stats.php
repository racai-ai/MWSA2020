<?php

require_once "common.php";

$pos=[];

function processWord($data,$line){

    global $pos;

    $p=$data['pos'];
    if(!isset($pos[$p]))$pos[$p]=["total"=>0,"exact"=>0,"broader"=>0,"narrower"=>0,"related"=>0,"none"=>0];

    if(isset($data['type'])){
	$t=$data['type'];
	if(!isset($pos[$p][$t]))$pos[$p][$t]=0;
	$pos[$p][$t]++;
    }
    
    $pos[$p]["total"]++;
}


$num=processTSV("train/$DFILE.tsv");

echo "TRAIN:\n";
echo "       POS\tTotal\tExact\tNarrower\tBroader\tRelated\tNone\n";
foreach($pos as $p=>$t){
    echo str_pad($p,10)."\t${t['total']}\t${t['exact']}\t${t['narrower']}\t${t['broader']}\t${t['related']}\t${t['none']}\n";
}


$pos=[];
$num=processTSV("test/$DFILE.tsv");

echo "TEST:\n";
echo "       POS\tTotal\tExact\tNarrower\tBroader\tRelated\tNone\n";
foreach($pos as $p=>$t){
    echo str_pad($p,10)."\t${t['total']}\t${t['exact']}\t${t['narrower']}\t${t['broader']}\t${t['related']}\t${t['none']}\n";
}
