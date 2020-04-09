<?php

require "common.php";

@mkdir("my");
@mkdir("my/compare");

$categs=["exact","narrower","related","broader","none"];


foreach(array_merge(
    ["lesk","lesk_lemma","lesk_stem","lesk_catvar","my","vn","vn1","graph1","graph2","graph3","graph4","graph5","bert","bertm","bertall","dt","dtp"],
    array_keys($combinations)) as $method){


//foreach(["graph1"] as $method){
$fp=fopen("my/$method.${DFILE}.tsv","r");
if($fp===false){
    echo "Method [$method] not computed. SKIP\n";
    continue;
}

$fgold=fopen("$DTYPE/${DFILE}.tsv","r");
if($fgold===false){
    die("\nFATAL ERROR\nGOLD not provided. Is TRAIN folder present?\n\n");
}

$base="my/compare/$method.${DFILE}";
foreach($categs as $c){
    file_put_contents("$base.$c.FP","");
    file_put_contents("$base.$c.FN","");
    file_put_contents("$base.$c.TP","");
}

$num=0;
$accnum=0;
$TP=0;
$FP=0;
$FN=0;

$cTP=[];
$cFP=[];
$cFN=[];


foreach($categs as $c){$cTP[$c]=0;$cFP[$c]=0;$cFN[$c]=0;}

while(!feof($fgold)){
    $dgold=fgetcsv($fgold,0,"\t");
    if($dgold===false)break;
    if(count($dgold)!=5)continue;
    
    $data=fgetcsv($fp,0,"\t");
    if($data===false){echo "WARN: [$method] Not all lines are computed\n";break;}
    if(count($data)!=5)continue;
    
    $num++;
    if($data[4]==$dgold[4]){
//    var_dump($data);var_dump($dgold);die();
	$accnum++;
    }
    
    if($dgold[4]=="none"){
	if($data[4]!="none")$FP++;
	else $TP++;
    }else{
	if($data[4]=="none")$FN++;
	else $TP++;
    }
    
    foreach($categs as $c){
	if($dgold[4]==$c){
	    if($data[4]==$c){
		$cTP[$c]++;
		file_put_contents("$base.$c.TP",$num."\t".implode("\t",$dgold)."\n",FILE_APPEND);
	    }else{
		$cFN[$c]++;
		file_put_contents("$base.$c.FN",$num."\t".implode("\t",$dgold)."\n",FILE_APPEND);
	    }
	}else{
	    if($data[4]==$c){
		$cFP[$c]++;
		file_put_contents("$base.$c.FP",$num."\t".implode("\t",$dgold)."\n",FILE_APPEND);
	    }
	}
    }
}

fclose($fgold);
fclose($fp);

$P=round(100.0*floatval($TP)/floatval($TP+$FP),2);
$R=round(100.0*floatval($TP)/floatval($TP+$FN),2);

$F1=2* $P * $R / ($P+$R);
$F1=round($F1,2);

$ACC=round(floatval($accnum*100)/floatval($num),2);

echo "$method\n\tNUM=$num\tACC=$ACC\tTP=$TP\tFP=$FP\tFN=$FN\tP=$P\tR=$R\tF1=$F1\n";
foreach($categs as $c){
    echo "\t$c\tTP=${cTP[$c]}\tFP=${cFP[$c]}\tFN=${cFN[$c]}\n";
}
}
