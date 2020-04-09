<?php

require_once "common.php";

$type="";

$check=array_flip(["which","who","that"]);

function processOr($d){

    $words=getWords($d);
    
    $base=[];
    
    $ret=[];
    
    for($i=0;$i<count($words);$i++){
	$w=$words[$i];
	if($w=="or"){
	
	    if($base[count($base)-1]==",")array_pop($base);
	    $ret[]=implode(" ",$base);
	
	    if($words[$i+1]=="to")$i++;
	    else if($words[$i+1]=="the" && $base[count($base)-2]=="the")$i++;
	    else if($words[$i+1]=="who"){
		for($j=count($base)-1;$j>=0;$j--)
		    if($base[$j]=="who"){$base=array_slice($base,0,$j+1);break;}
	    }
	
	    $ret[]=implode(" ",array_merge(array_slice($base,0,count($base)-1),array_slice($words,$i+1)));
	    break;
	}else {
	    $base[]=$w;
	}
    }
    
    return $ret;

}

function processDef($d){
    global $type,$check,$DFILE,$DTYPE;
    if(strpos($d,' or ')!==false){
        file_put_contents("my/analysis/or.$type.${DFILE}.tsv",$d."\n",FILE_APPEND);
        $data=processOr($d);
        foreach($data as $opt){
    	    file_put_contents("my/analysis/or.$type.${DFILE}.tsv","===> $opt"."\n",FILE_APPEND);
        }
    	    file_put_contents("my/analysis/or.$type.${DFILE}.tsv","\n",FILE_APPEND);
        
    }

    $prev="";
    foreach(getWords($d) as $w){
	if( (endsWith($w,"ing") || isset($check[$w])) && $prev==","){
    	    file_put_contents("my/analysis/dot.$type.${DFILE}.tsv",$d."\n",FILE_APPEND);
	}
	
	$prev=$w;
    }

    if(strpos($d,' and ')!==false){
        file_put_contents("my/analysis/and.$type.${DFILE}.tsv",$d."\n",FILE_APPEND);
    }

}

function processWord($data,$line,$lnum){
    global $type;
    

    foreach($data['def1'] as $d1)processDef($d1);
    
    foreach($data['def2'] as $d2)processDef($d2);
    
    

}

@mkdir("my/analysis");

$type="train";
file_put_contents("my/analysis/or.$type.$DFILE.tsv","");
file_put_contents("my/analysis/dot.$type.${DFILE}.tsv","");
file_put_contents("my/analysis/and.$type.$DFILE.tsv","");
$num=processTSV("train/$DFILE.tsv");

$type="test";
file_put_contents("my/analysis/or.$type.$DFILE.tsv","");
file_put_contents("my/analysis/dot.$type.${DFILE}.tsv","");
file_put_contents("my/analysis/and.$type.$DFILE.tsv","");
$num=processTSV("test/$DFILE.tsv");

