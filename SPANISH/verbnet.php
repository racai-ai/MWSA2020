<?php

$VERBNET=[];
$VERBNET_CLASSES=[];

function loadVerbnetFile($fname){
    global $VERBNET,$VERBNET_CLASSES;
    
//    echo "VERBNET Reading $fname\n";
    $fdata=file_get_contents($fname);
    $xml = new SimpleXMLElement($fdata);
    
    
    $words=[];
    $frames=[];
    
    foreach($xml->xpath("/VNCLASS/MEMBERS/MEMBER") as $node){
	foreach($node->attributes() as $n=>$v){
	    if($n=="name"){
		$words[]="".$v;
		break;
	    }
	}
    }
    
    foreach($xml->xpath("/VNCLASS/FRAMES/FRAME/SYNTAX") as $node){
	$frame=[];
	foreach($node->children() as $n=>$v){
	    $frame[]=["".$n,"".$v->attributes()['value']];
	}
	$frames[]=$frame;
    }
    
    foreach($words as $w){
	if(!isset($VERBNET[$w]))$VERBNET[$w]=[];
	$VERBNET[$w]=array_merge($VERBNET[$w],$frames);
    }
    
    foreach($words as $w){
	if(!isset($VERBNET_CLASSES[$w]))$VERBNET_CLASSES[$w]=[];
	$VERBNET_CLASSES[$w][]=$fname;
    }
}

function loadVerbnet(){
//    echo "VERBNET loading";
    foreach(glob("resources/verbnet/verbnet3.3/*.xml") as $fname){
	loadVerbnetFile($fname);
    }
}

function displayVerbnetFrame($frame){
    $ret="";
    foreach($frame as $f){
	$ret.="${f['w']}:${f['type']} ";
    }
    return $ret;
}

function displayVerbnetFrameDef($frame){
    $ret="";
    foreach($frame as $f){
	$ret.="${f[0]}:${f[1]} ";
    }
    return $ret;
}

function matchVerbnetFrame($phrases,$vbPos,$frame){
    $fVbPos=-1;
    foreach($frame as $k=>$d){
	if($d[0]=="VERB"){$fVbPos=$k;break;}
    }
    if($fVbPos==-1)return false;
    
    $retFrame=[['w'=>$phrases[$vbPos]['w'], 'type'=>"VERB"]];
    $j=$vbPos-1;
    for($i=$fVbPos-1;$i>=0;$i--){
	if($j<0)return false;
	
	if($frame[$i][0]=='PREP'){
	    if($phrases[$j]['pos']=='IN' || $phrases[$j]['pos']=='TO'){
		if(empty($frame[$i][1]) || isset(array_flip(explode(" ",$frame[$i][1]))[$phrases[$j]['w']])){
		    $j--; continue;
		}
	    }
	    return false;
	}else if($frame[$i][0]=='ADV'){
	    if(startsWith($phrases[$j]['pos'],'RB')){
		array_unshift($retFrame,['w'=>$phrases[$j]['w'],'type'=>$frame[$i][1]]); $j--;
		continue;
	    }
	    return false;
	}else if($frame[$i][0]=='ADJ'){
	    if(startsWith($phrases[$j]['pos'],'JJ')){
		array_unshift($retFrame,['w'=>$phrases[$j]['w'],'type'=>$frame[$i][1]]); $j--;
		continue;
	    }
	    return false;
	}else if($frame[$i][0]=='LEX'){
	    if($phrases[$j]['w']==$frame[$i][1]){
		$j--;
		continue;
	    }
	    return false;
	}else if($frame[$i][0]=='NP'){
	    if($phrases[$j]['pos']=='NP'){
		array_unshift($retFrame,['w'=>$phrases[$j]['w'],'type'=>$frame[$i][1]]); $j--;
		continue;
	    }
	    if($j>0 && $phrases[$j-1]['pos']=='NP'){
		array_unshift($retFrame,['w'=>$phrases[$j-1]['w'],'type'=>$frame[$i][1]]);
		$j-=2;
		continue;
	    }
	    return false;
	}else{
	    echo "Unknown type\n";
	    var_dump($frame[$i]);
	    die("VERBNET");
	}
    }
    

    // after the verb
    $j=$vbPos+1;
    for($i=$fVbPos+1;$i<count($frame);$i++){
	if($j>=count($phrases))return false;
	
	if($frame[$i][0]=='PREP'){
	    if($phrases[$j]['pos']=='IN' || $phrases[$j]['pos']=='TO'){
		if(empty($frame[$i][1]) || isset(array_flip(explode(" ",$frame[$i][1]))[$phrases[$j]['w']])){
		    $j++; continue;
		}
	    }
	    return false;
	}else if($frame[$i][0]=='ADV'){
	    if(startsWith($phrases[$j]['pos'],'RB')){
		array_push($retFrame,['w'=>$phrases[$j]['w'],'type'=>$frame[$i][1]]); $j++;
		continue;
	    }
	    return false;
	}else if($frame[$i][0]=='ADJ'){
	    if(startsWith($phrases[$j]['pos'],'JJ')){
		array_push($retFrame,['w'=>$phrases[$j]['w'],'type'=>$frame[$i][1]]); $j++;
		continue;
	    }
	    return false;
	}else if($frame[$i][0]=='LEX'){
	    if($phrases[$j]['w']==$frame[$i][1]){
		$j++;
		continue;
	    }
	    return false;
	}else if($frame[$i][0]=='NP'){
	    if($phrases[$j]['pos']=='NP'){
		array_push($retFrame,['w'=>$phrases[$j]['w'],'type'=>$frame[$i][1]]); $j++;
		continue;
	    }
	    if($j<=count($phrases)-2 && $phrases[$j+1]['pos']=='NP'){
		array_push($retFrame,['w'=>$phrases[$j+1]['w'],'type'=>$frame[$i][1]]);
		$j+=2;
		continue;
	    }
	    return false;
	}else{
	    echo "Unknown type\n";
	    var_dump($frame[$i]);
	    die();
	}
    }


    return $retFrame;
}

function getVerbnetFrame($phrases){
    global $VERBNET;

    $vbPos=-1;
    foreach($phrases as $k=>$ph){
	if($ph['pos']=='VB'){
	    $vbPos=$k;
	    break;
	}
    }
    
    if($vbPos===-1){
	echo "No VERB\n";
	return [];
    }
    
    $verb=getLemma(mb_strtolower($phrases[$vbPos]['w']));
    if(!isset($VERBNET[$verb])){
	echo "Not in VERBNET [$verb]\n";
	return [];
    }
    
    $frames=$VERBNET[$verb];
    
    $finalMatch=[];
    foreach($frames as $frame){
	//displayVerbnetFrameDef($frame);
	$matched=matchVerbnetFrame($phrases,$vbPos,$frame);
	if($matched!==false){
	    if(count($matched)>count($finalMatch))$finalMatch=$matched;
	}
    }
    
    return $finalMatch;
    
}

//loadVerbnetFile("resources/verbnet/verbnet3.3/absorb-39.8.xml");
//var_dump($VERBNET);
//loadVerbnet();

//var_dump($VERBNET_CLASSES);
