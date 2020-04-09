<?php

$ANTONYMS=[];

function loadAntonyms(){
    global $ANTONYMS;
    
    echo "Loading Antonyms\n";
    
    $prefix="";
    
    foreach(explode("\n",file_get_contents("resources/antonyms.txt")) as $line){
	$line=trim($line);
	if(strlen($line)==0)continue;
	
	if(endsWith($line,":")){$prefix=substr($line,0,strlen($line)-1);continue;}
	
	$ANTONYMS[$line]=$prefix.$line;
	
    }
}

function getDefsNot($def){
    global $ANTONYMS;
    
    $ret=[];
    
    $ret[]=$def;
    
    $words=getWords($def);
    for($i=0;$i<count($words);$i++){
    
	$w=$words[$i];
	$ant=false;
	if(isset($ANTONYMS[$w]))$ant=$ANTONYMS[$w];
	else if(isset($ANTONYMS[$w."ly"]))$ant=$ANTONYMS[$w."ly"];
	
	if($ant!==false && $i>0 && strcasecmp($words[$i-1],"not")==0){
	    $w1=array_merge(array_slice($words,0,$i-1),[$ant],array_slice($words,$i+1));
	    $ret[]=implode(" ",$w1);
	    break;
	}else if($ant!==false && $i>1 && strcasecmp($words[$i-2],"not")==0 && strcasecmp($words[$i-1],"yet")==0){
	    $w1=array_merge(array_slice($words,0,$i-2),[$ant],array_slice($words,$i+1));
	    $ret[]=implode(" ",$w1);
	    break;
	}
    
    }
    
    return $ret;
}

