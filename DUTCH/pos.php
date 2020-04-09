<?php

$POSTAGS=[];

function loadPOSTags($fname){
    global $POSTAGS;
    
    $fp=fopen($fname,"r");
    while(!feof($fp)){
	$line=fgets($fp);
	if($line===false)break;
	
	$line=trim($line);
	$data=explode("\t",$line);
	if(count($data)!==2)continue;
	
	$w=$data[0];
	$pdata=explode(",",$data[1]);
	$pdata=explode(":",$pdata[0]);
	$pos=$pdata[0];
	
	$POSTAGS[$w]=$pos;
    }
    fclose($fp);

}

function tagWords($words,$lowercase=true){
    global $POSTAGS;
    $data=[];
    $prev=false;
    foreach($words as $k=>$t){
	$next=false;
	if($k<count($words)-1)$next=$words[$k+1];
	
	$pos="NN";
	if($lowercase){
	    $w=trim(mb_strtolower($t));
	    if(isset($POSTAGS[$w]))$pos=$POSTAGS[$w];
	    else{
		 echo "WARN: POS not found for [$t]\n";
		if(strpos($t,"-")!==false)$pos="JJ";
	    }
	}else{
	    $w=trim($t);
	    if(mb_substr($w,0,1)==mb_strtoupper(mb_substr($w,0,1)))$pos="NNP";
	    else{
		if(isset($POSTAGS[$w]))$pos=$POSTAGS[$w];
		else{
		     echo "WARN: POS not found for [$t]\n";
		     if(strpos($t,"-")!==false)$pos="JJ";
		}
	    }
	}
	if($prev=="to" && $pos=="NN")$pos="VB";
	if($pos=="DT" && $next==="of")$pos="NN";
	
	$data[]=["w"=>$w,"pos"=>$pos];
	$prev=$t;
    }
    return $data;
}

function displayPOS($tokens){
    $ret="";
    foreach($tokens as $t){
	$ret.="${t['pos']}:${t['w']} ";
    }
    return $ret;
}

