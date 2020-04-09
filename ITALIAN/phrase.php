<?php

$POSMAP=[
"VB" => "V",
"NN" => "N",
"PRP" => "N",
"DT" => "D",
"CD" => "C",
"JJ" => "J",
"RB" => "R",
];

$PHRASERULES=[
    ['CD', 'JJ', 'NN'],                   
    ['CD', 'NN'],                         
    ['CD', 'RB', 'JJ', 'NN'],             
    ['DT', 'CD', 'JJ', 'NN'],             
    ['DT', 'CD', 'NN'],                   
    ['DT', 'CD', 'RB', 'JJ', 'NN'],       
    ['DT', 'JJ', 'CD', 'JJ', 'NN'],       
    ['DT', 'JJ', 'CD', 'NN'],             
    ['DT', 'JJ', 'CD', 'RB', 'JJ', 'NN'], 
    ['DT', 'JJ', 'NN'],                   
    ['DT', 'JJ', 'RB', 'JJ', 'NN'],       
    ['DT', 'NN'],                         
    ['DT', 'RB', 'JJ', 'NN'],             
    ['DT', 'RB', 'VB', 'NN'],             
    ['DT', 'VB', 'NN'],                   
    ['JJ', 'CD', 'JJ', 'NN'],             
    ['JJ', 'CD', 'NN'],                   
    ['JJ', 'CD', 'RB', 'JJ', 'NN'],       
    ['JJ', 'NN'],                         
    ['JJ', 'RB', 'JJ', 'NN'],             
    ['NN',]                               
];

$PHRASERULESPREP=[];

function preparePhraseRules(){
    global $PHRASERULES,$PHRASERULESPREP,$POSMAP;
    
    if(!empty($PHRASERULESPREP))return ;
    
    foreach($PHRASERULES as $rule){
	$s="";
	foreach($rule as $r)$s.="[".$POSMAP[$r]."]+";
	$PHRASERULESPREP[]=['rule'=>$s,'start'=>0,'len'=>0];
    }
}

function getPhrases($tokens){
    global $POSMAP,$PHRASERULESPREP;
    
    preparePhraseRules();
    
    $s="";
    foreach($tokens as $t){
	$p=$t['pos'];
	if(isset($POSMAP[$p]))$s.=$POSMAP[$p];
	else $s.="O";
    }
    
    $phrases=[];
    $rules=$PHRASERULESPREP;
    $start=0;
    while(count($rules)>0){
	$bestMatch=-1;
	
	foreach($rules as $k=>$r){
	    $matches=[];
	    if(preg_match("/${r['rule']}/",$s,$matches,PREG_OFFSET_CAPTURE,$start)===1){
		$rules[$k]['start']=$matches[0][1];
		$rules[$k]['len']=strlen($matches[0][0]);
		
		if($bestMatch==-1 || 
		    $rules[$k]['start']<$rules[$bestMatch]['start'] || 
		    ($rules[$k]['start']==$rules[$bestMatch]['start'] && $rules[$k]['len']>$rules[$bestMatch]['len'])
		)$bestMatch=$k;
	    }else{
		unset($rules[$k]);
	    }
	}
	
	if($bestMatch>-1){
	    for($i=$start;$i<$rules[$bestMatch]['start'];$i++){
		$phrases[]=$tokens[$i];
	    }
	    $np="";
	    for($i=$rules[$bestMatch]['start'];$i<$rules[$bestMatch]['start']+$rules[$bestMatch]['len'];$i++){
		if(strlen($np)>0)$np.=" ";
		$np.=$tokens[$i]['w'];
	    }
	    $phrases[]=["w"=>$np,"pos"=>"NP"];
	    $start=$rules[$bestMatch]['start']+$rules[$bestMatch]['len'];
	}
    }
    for($i=$start;$i<strlen($s);$i++){
	$phrases[]=$tokens[$i];
    }
    
    return $phrases;
}

function displayPhrases($phrases){
    $ret="";
    foreach($phrases as $t){
	$ret.="${t['pos']}:[${t['w']}] ";
    }
    return $ret;
}
