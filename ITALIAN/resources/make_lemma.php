<?php

$fp=fopen("paisa.annotated.CoNLL.utf8","r");

$lemma=[];

while(!feof($fp)){
    $line=fgets($fp);
    if($line===false)break;
    
    if($line[0]=="#")continue;
    
    $data=explode("\t",$line);
    if(count($data)<7)continue;
    
    $word=mb_strtolower($data[1]);
    $l=mb_strtolower($data[2]);
    
    if(!isset($lemma[$l]))$lemma[$l]=['c'=>0,'w'=>[]];
    $lemma[$l]['c']++;
    if(!isset($lemma[$l]['w'][$word]))$lemma[$l]['w'][$word]=true; 
}
fclose($fp);

$fout=fopen("lemma.txt","w");
foreach($lemma as $l=>$data){
    fwrite($fout,"$l/${data['c']} -> ".implode(",",array_keys($data['w']))."\n");
}
fclose($fout);
