<?php

$DFILE="dutch";
$DTYPE="test";
@mkdir("my");
@mkdir("my/$DFILE");
@mkdir("my/explain");

$combinations=[
//"c1" => ["add:lesk","add:my","add:lesk_lemma","filter:2","set:exact:my"],
"c2" => ["set:my","set:none:lesk"],
//"c3" => ["add:exact:c2","add:exact:graph1"],
"c4" => ["add:exact:c2","add:exact:graph3"],
"lesk_graph4_graph3" => ["set:exact:graph4","set:exact:graph3","set:none:lesk","set:exact:c4"],
//"c5" => ["add:lesk","add:my","add:graph4","add:graph3","filter:2","set:narrower:graph4","set:broader:graph4","set:related:graph4",]
"c6" => ["add:exact:my","add:exact:lesk","add:exact:bert","add:lesk_graph4_graph3","filter:2"],
"c7" => ["add:exact:bert","add:exact:bertm","add:exact:lesk","add:exact:my","filter:2","add:exact:graph4","add:exact:graph3"],
"dt_my" => ["add:exact:dt","add:exact:c4"],
];

$firstExplain=true;

function logExplain($method,$lnum,$data){
    global $firstExplain,$DFILE;
    
    $fname="my/explain/$method.$DFILE.explain";
    
    if($firstExplain){file_put_contents($fname,"");$firstExplain=false;}
    
    $msg="";
    if($lnum===false)$msg="$data\n";
    else $msg="$lnum;$data\n";
    
    file_put_contents($fname,$msg,FILE_APPEND);
}

function processTSV($fname,$callback="processWord",$doCorrections=true){
    $fp=fopen($fname,"r");
    
    if(!$fp){
	echo "Invalid filename [$fname]\n";
	die();
    }
    
    $num=0;
    while(!feof($fp)){
	$data=fgetcsv($fp,0,"\t");
	if($data===NULL || $data===FALSE)break;
	
	if(count($data)<4)continue;
	
	$num++;
	$p=[
	    "word"=>$data[0],
	    "pos"=>$data[1],
	    "def1"=>getDefs($data[2],$doCorrections),
	    "def2"=>getDefs($data[3],$doCorrections)
	];
	if(isset($data[4]))$p['type']=$data[4];
	$callback($p,$data,$num);
    }
    
    fclose($fp);
    
    return $num;

}

$REPLACEMENTS=["&eacute;" => "è"];
$UNKNOWN_ESCAPE=[];

function getOrDefs($def){
    $pos=strpos($def," or ");
    if($pos===false)return [$def];
    
    if($def[$pos-1]==','){
	$ret=[];
	foreach(getOrDefs(substr($def,$pos+4)) as $d)$ret[]=substr($def,0,$pos+4).$d;
	return $ret;
    }
    
    $prev=substr($def,0,$pos);
    $p=strrpos($prev," "); if($p===false)$p=-1;
    $word=substr($prev,$p+1);
    if($p===-1)$prev="";
    else $prev=substr($prev,0,$p);
    
    $next=substr($def,$pos+4);
    $p=strpos($next," "); if($p===false)$p=strlen($next);
    $wnext=substr($next,0,$p);
    if($p==strlen($next))$next="";
    else $next=substr($next,$p+1);
    
    $d1=trim($prev." ".$word." ".$next);
    $d2=trim($prev." ".$wnext." ".$next);
    
    $ret=[];
    $ret=array_merge($ret,getOrDefs($d1));
    $ret=array_merge($ret,getOrDefs($d2));
    $ret=array_unique($ret);
    return $ret;
}

function getDefs($def,$doCorrections){
    // some definitions end in "." or ";"
    global $REPLACEMENTS,$UNKNOWN_ESCAPE;

    if($doCorrections){
    
    $pos=strpos($def,"/|\\");
    if($pos!==false)$def=trim(substr($def,$pos+3));
    
    $matches=[];
    if(preg_match_all("/[&][^;]+[;]/",$def,$matches)>0){
	foreach($matches[0] as $m){
	    if(isset($REPLACEMENTS[$m]))$def=str_replace($m,$REPLACEMENTS[$m],$def);
	    else {$UNKNOWN_ESCAPE[$m]=true; $def=str_replace($m,"",$def);}
	}
    }
    
    $def=str_replace("'s "," own ",$def); // one's, individual's ...
    //$def=str_replace("'s "," ",$def); // one's, individual's ...
    $def=str_replace("etc."," ",$def);
    $def=str_replace("esp."," ",$def);
    $def=str_replace("specif."," ",$def);
    $def=str_replace("and the like"," ",$def);
    $def=str_replace("characterized by"," ",$def);
    $def=str_replace("indicative of"," ",$def);
    $def=str_replace("marked by"," ",$def);
    $def=str_replace("for example"," ",$def);
    $def=preg_replace("/[(][-0-9]*[)]/"," ",$def);
    $def=preg_replace("/[(]used[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]of[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]such[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]usually[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]as[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]see[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]often[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]including[^)]*[)]/"," ",$def);
    $def=preg_replace("/[(]aproximately[^)]*[)]/"," ",$def);
    
    $def=trim($def," .;");
    $defs=[];
    foreach(explode(";",$def) as $d){
	$d=trim($d);
	if(strlen($d)==0)continue;
	
	if(startsWith($d,"--") || startsWith($d,"e.g."))continue;
	
	if(startsWith($d,"also"))$d=substr($d,4);
	if(startsWith($d,"or"))$d=substr($d,2);
	
	if(startsWith($d,"(")){
	    $pos=strpos($d,")");
	    $d=substr($d,$pos+1);
	}
	
	if(endsWith($d,")")){
	    $pos=strrpos($d,"(");
	    $d=substr($d,0,$pos);
	}
	
	$d=str_replace("("," ",$d);
	$d=str_replace(")"," ",$d);
	
	$d=trim($d,", ");
	if(strlen($d)==0)continue;
	
	//$d=preg_replace("/[(][^)]+[)]/","",$d);
	
	$defs[]=$d;
    }
    
    $alldefs=[];
    //foreach($defs as $def){
//	$alldefs=array_merge($alldefs,getOrDefs($def));
//    }
    $alldefs=$defs;
    
    }else $alldefs=explode(";",$def); // without corrections
    
    return $alldefs;
}

function loadStat($fname){
    $ret=[];
    foreach(explode("\n",file_get_contents($fname)) as $line){
        $line=trim($line);
        if(strlen($line)==0)continue;
        $data=explode(";",$line);
        if(count($data)!=2)continue;
        $ret[$data[0]]=$data[1];
    }
    return $ret;
}


function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function saveStat(&$vec, $fname){
    arsort($vec);

    file_put_contents($fname,"");
    foreach($vec as $w=>$n){
        file_put_contents($fname,"$w;$n\n",FILE_APPEND);
    }

}

function saveList(&$vec, $fname){
    arsort($vec);

    file_put_contents($fname,"");
    foreach($vec as $w=>$n){
        file_put_contents($fname,"$w\n",FILE_APPEND);
    }

}

function loadList($fname){
    $ret=[];
    foreach(explode("\n",file_get_contents($fname)) as $line){
        $line=trim($line);
        if(strlen($line)>0)$ret[]=$line;
    }
    return $ret;
}

//$wordBoundaryS=" ,!?();:/\\-[]—";
$wordBoundaryS=" .,;:/\\()[]—{}";
$wordBoundary=[];
for($i=0;$i<mb_strlen($wordBoundaryS);$i++)$wordBoundary[mb_substr($wordBoundaryS,$i,1)]=true;

function getWords($line){
    global $wordBoundary;
        $words=[];
        $w="";
        for($i=0;$i<mb_strlen($line);$i++){
            $c=mb_substr($line,$i,1);
            if(isset($wordBoundary[$c])){
                if(strlen($w)>0 && !is_numeric($w)){
                    $words[]=$w;
                }
                $w="";
                if($c!=" ")$words[]=$c;
            }else $w.=$c;
        }
        if(strlen($w)>0)$words[]=$w;
        return $words;
}

