<?php

require_once "common.php";

$dist=[];
foreach(explode("\n",file_get_contents("my/sentences/${DTYPE}_${DFILE}_all_dist.txt")) as $line){
    $dist[]=floatval($line);
}


function processWord($data,$line,$lnum){
    global $dist,$DFILE;

    $d=$dist[$lnum-1];
    
    
    $decision="none";
    if($d<0.25)$decision="exact";
    //else if($d<0.26)$decision="narrower";
    //else if($d<0.28)$decision="broader";
    //else if($d<0.3)$decision="related";
    
    file_put_contents("my/bertall.$DFILE.tsv",implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}

file_put_contents("my/bertall.${DFILE}.tsv","");
processTSV("$DTYPE/$DFILE.tsv");
