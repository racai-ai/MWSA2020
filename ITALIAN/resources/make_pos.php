<?php

function getPos($p){
    if($p=="A")return "JJ";
    if($p=="B")return "RB";
    if($p=="C")return "CC";
    if($p=="D")return "DT";
    if($p=="E")return "IN";
    if($p=="F")return "SYM";
    if($p=="I")return "UH";
    if($p=="N")return "CD";
    if($p=="P")return "PRP";
    if($p=="R")return "RP";
    if($p=="S")return "NN";
    if($p=="T")return "PDT";
    if($p=="V")return "VB";
    if($p=="X")return "SYM";
    
    return "NN";
}

$fp=fopen("paisa.annotated.CoNLL.utf8","r");

$pos=[];

while(!feof($fp)){
    $line=fgets($fp);
    if($line===false)break;
    
    if($line[0]=="#")continue;
    
    $data=explode("\t",$line);
    if(count($data)<7)continue;
    
    $word=mb_strtolower($data[1]);
    $p=getPos($data[3]);
    
    if(!isset($pos[$word]))$pos[$word]=[];
    if(!isset($pos[$word][$p]))$pos[$word][$p]=0;
    $pos[$word][$p]++;
}
fclose($fp);

$fout=fopen("paisa.pos","w");
foreach($pos as $w=>$data){
    $r="";
    foreach($data as $p=>$c){
	if(strlen($r)>0)$r.=",";
	$r.="$p:$c";
    }
    fwrite($fout,"$w\t$r\n");
}
fclose($fout);
