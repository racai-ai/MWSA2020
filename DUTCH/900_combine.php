<?php

require_once "common.php";


$allowedAnn=array_flip(["none","narrower","broader","exact","related"]);
$ann=[];
function combine($fname,$type,$param){
    global $ann,$allowedAnn;
    
    echo "    COMBINE [$type] [$param] [$fname]\n";
    
    if($param!==false && !isset($allowedAnn[$param]))$param=false;
    
    $fp=fopen($fname,"r");

    $num=0;
    while(!feof($fp)){
	$data=fgetcsv($fp,0,"\t");
	if($data===false)break;
	if(count($data)!=5)continue;
	
	if($param===false || $data[4]==$param){
	
	if($type=="add"){
	    if(!isset($ann[$num]))$ann[$num]=[];
	    if(!isset($ann[$num][$data[4]]))$ann[$num][$data[4]]=0;
	    $ann[$num][$data[4]]++;
	}else if($type=="set"){
	    $ann[$num]=[];
	    $ann[$num][$data[4]]=1;
	}
	
	}
	$num++;
    }
    fclose($fp);
}

function filter($n){
    global $ann;
    
    echo "    FILTER [$n]\n";
    
    $annf=[];
    foreach($ann as $k=>$v){
	$mpos=false;
	foreach($v as $kv=>$vv){
	    if($mpos===false || $vv>$v[$mpos])$mpos=$kv;
	}
	if($v[$mpos]>=$n)$annf[$k]=[$mpos=>$v[$mpos]];
    }
    $ann=$annf;
}

function processWord($data,$line){
    global $ann,$num,$DFILE,$destFname;
    
    $a=false;
    if(!isset($ann[$num]))$a="none";
    else{
    foreach($ann[$num] as $k=>$v){
	if($a===false || $ann[$num][$a]<$v)$a=$k;
    }
    }

    file_put_contents("my/$destFname.$DFILE.tsv",
	implode("\t",array_merge(array_slice($line,0,4),[$a]))."\n",FILE_APPEND);
    $num++;
}


foreach($combinations as $name=>$combine){
    echo "$name\n";
    $ann=[];
    foreach($combine as $method){
	$mdata=explode(":",$method);
	$fname=$mdata[count($mdata)-1];
	$type=$mdata[0];
	$param=false;
	if(count($mdata)>2)$param=$mdata[1];
	else if($type=="filter" && count($mdata)>1)$param=$mdata[1];

	$num=0;

	if($type=="add" || $type=="set"){
	    combine("my/$fname.$DFILE.tsv",$type,$param);
	}else if($type=="filter"){
	    filter(intval($param));
	}
    }

    $destFname=$name;
    $num=0;
    echo "    WRITING\n";
    file_put_contents("my/$name.$DFILE.tsv","");
    processTSV("$DTYPE/$DFILE.tsv");

}
