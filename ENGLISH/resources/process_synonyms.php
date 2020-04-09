<?php

require_once "../common.php";

$synonyms=[];

$fp=fopen("51155-0.txt","r");
$currentK="";
while(!feof($fp)){
    $line=fgets($fp);
    if($line===false)break;
    
    if(startsWith($line,"KEY:")){
	$currentK=trim(substr($line,4));
	$currentK=mb_strtolower(trim($currentK,"."));
    }else if(startsWith($line,"SYN:")){
	$syns=trim(substr($line,4));
	$syns=mb_strtolower(trim($syns,"."));
	$sArr=[];
	foreach(explode(",",$syns) as $s){
	    $s=trim($s);
	    if(strlen($s)>0)$sArr[]=$s;
	}
	
	if(!isset($synonyms[$currentK]))$synonyms[$currentK]=$sArr;
	else $synonyms[$currentK]=array_merge($synonyms[$currentK],$sArr);
    }
}
fclose($fp);

file_put_contents("synonyms.json",json_encode($synonyms,JSON_PRETTY_PRINT));

