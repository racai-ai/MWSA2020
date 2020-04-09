<?php

use markfullmer\porter2\Porter2;

$CATVAR=[];
$CATVAR_STEM=[];

function loadCatvar(){
    global $CATVAR,$CATVAR_STEM;

    echo "Loading CATVAR 2.1\n";
    
    $fp=fopen("resources/catvar/catvar21.signed","r");
    while(!feof($fp)){
	$line=fgets($fp);
	if($line===false)break;
	$line=trim($line);
	$data=explode("#",$line);
	
	$clus=[];
	foreach($data as $w){
	    $pos=strrpos($w,"_");
	    $w=substr($w,0,$pos);
	    $clus[]=$w;
	    
	    $stem=Porter2::stem(mb_strtolower($w));
	    if(!isset($CATVAR_STEM[$stem]))$CATVAR_STEM[$stem]=[];
	    if(!isset($CATVAR_STEM[$stem][count($CATVAR)]))$CATVAR_STEM[$stem][count($CATVAR)]=true; 
	}
	$CATVAR[]=$clus;
    }
    fclose($fp);
    echo "Loaded ".count($CATVAR)." clusters\n";
}

function getCatvar($w){
    global $CATVAR,$CATVAR_STEM;
    
    $stem=Porter2::stem(mb_strtolower($w));
    if(!isset($CATVAR_STEM[$stem]))return [];
    
    $ret=[];
    foreach($CATVAR_STEM[$stem] as $k=>$t){
	$ret=array_merge($ret,$CATVAR[$k]);
    }
    return array_unique($ret);
}
