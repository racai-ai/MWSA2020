<?php

require_once "common.php";
require_once "pos.php";
require_once "synonyms_context.php";
require_once "synonyms_dict.php";
require_once "lemma.php";

loadLemmas();

loadPOSTags("resources/oanc.pos");
loadSynonyms();
loadSynonymsDict();

$POSTAGS['one']='NN';
$POSTAGS['reference']='JJ';
$POSTAGS['morbid']='JJ';
$POSTAGS['elective']='JJ';
$POSTAGS['nonelective']='JJ';
$POSTAGS['royal']='JJ';
$POSTAGS['twining']='JJ';
$POSTAGS['kind']='IGN';
$POSTAGS['standard']='NN';
$POSTAGS['flatbottom']='JJ';
$POSTAGS['furred']='JJ';
$POSTAGS['long-eared']='JJ';
$POSTAGS['measure']='VB';
$POSTAGS['round']='JJ';
$POSTAGS['repeat']='VB';
$POSTAGS['quik']='JJ';

$newsyns=[
["someone", "person","boy","girl","man","woman","craftsman","one"],
["device","something","thing"],
["make","render","pay"],
["area","spot"],
["protuberance","organ"],
["property","condition","fact","quality","symptom","trait"],
["murder","crime"],
["manner","behavior","behaviour"], // american english=behavior, commonwealth english=behaviour
["speak","plead","defend"],
["order","body","organization","unit"],
["workshop","place"],
["process","production"],
["wait","lie"],
["coin","money"], // in wordnet coin=metal_money
["quantity","bundle"],
["statement","expression"],
];

//var_dump(getDefs("a, b, or c"));die();
//var_dump(getLemma("coins"));die();

foreach($newsyns as $data){
    foreach($data as $b){
	if(!isset($SYNONYMSDict[$b]))$SYNONYMSDict[$b]=$data;
	else $SYNONYMSDict[$b]=array_merge($SYNONYMSDict[$b],$data);
    }
}


$DT=[];

function loadDT($data,$line,$lnum){
    global $DT;
    $DT[$lnum]=$line[4];
}

$GOLD=[];

function loadGOLD($data,$line,$lnum){
    global $GOLD;
    $GOLD[$lnum]=$line[4];
}

/*
data:
  word => the word (first column)
  pos => part of speech
  def1 => first definitions list
  def2 => second definitions list
  type => exact/narrower/broader/related/none

line:
  csv line, split into columns
  
lnum:
  line number, starts at 1
*/
function processWord($data,$line,$lnum){
    global $DTYPE,$DFILE,$DT,$GOLD;
    
    if($DT[$lnum]!='exact'){
	file_put_contents("my/dtp.$DFILE.tsv",implode("\t",$line)."\n",FILE_APPEND);
	return ;
    }
    
    if($data['pos']!='verb' && $data['pos']!='noun'){
	file_put_contents("my/dtp.$DFILE.tsv",implode("\t",$line)."\n",FILE_APPEND);
	return ;
    }
    
    //echo "**************************************\n";
    //var_dump($data['pos']);
    
    $search1=[];$search2=[];
    $syn1=[];$syn2=[];
    
    foreach($data['def1'] as $d){
	$w=getWords($d);
	$tags=tagWords($w,false);
	$found=false;
	foreach($tags as $t){
	    if($data['pos']=='verb' && $t['pos']=='VB'){$found=$t;break;}
	    if($data['pos']=='noun' && $t['pos']=='NN'){$found=$t;break;}
	}
	if($found===false){
	    foreach($tags as $t){
		if($t['pos']!='NNP' && $t['pos']!='IN' && $t['pos']!='DT' && $t['pos']!='TO'){$found=$t;break;}
	    }
	}
	if($found!==false){
	    $search1[]=$found;
	    $syn1[]=array_merge(getSynonyms(getLemma($found['w']),$w),getSynonymsDict(getLemma($found['w']),$w));
	}
    }

    foreach($data['def2'] as $d){
	$w=getWords($d);
	$tags=tagWords($w,false);
	$found=false;
	foreach($tags as $t){
	    if($data['pos']=='verb' && $t['pos']=='VB'){$found=$t;break;}
	    if($data['pos']=='noun' && $t['pos']=='NN'){$found=$t;break;}
	}
	if($found===false){
	    foreach($tags as $t){
		if($t['pos']!='NNP' && $t['pos']!='IN' && $t['pos']!='DT' && $t['pos']!='TO'){$found=$t;break;}
	    }
	}
	if($found!==false){
	    $search2[]=$found;
	    $syn2[]=array_merge(getSynonyms(getLemma($found['w']),$w),getSynonymsDict(getLemma($found['w'],$w)));
	}
    }
    
    $found=false;
    foreach($search1 as $k1=>$se1){
	$found=false;
	$s1=$syn1[$k1];
	$l1=getLemma($se1['w']);
	foreach($search2 as $k2=>$se2){
	    $s2=$syn2[$k2];
	    
	    if($se1['w']==$se2['w']){$found=true; break;}
	    
	    foreach($s1 as $currents1){
	    foreach($s2 as $currents2){
		if($currents2==$l1 || $currents2==$currents1){$found=true;break;}
	    }}
	    if($found)break;
	}
	//if(!$found)break;
	if($found)break;
    }
    
    $decision="exact";
    if(!$found){
	$decision="none";
	if(strcasecmp($GOLD[$lnum],'exact')==0){
	    logExplain("dtp",$lnum,var_export($data,true));
	    logExplain("dtp",$lnum,var_export($search1,true));
	    logExplain("dtp",$lnum,var_export($search2,true));
	}
    }
    
    file_put_contents("my/dtp.$DFILE.tsv",implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
    //var_dump($search1);var_dump($search2);
    
}

processTSV("$DTYPE/${DFILE}.tsv","loadGOLD");
processTSV("my/dt.${DFILE}.tsv","loadDT");
file_put_contents("my/dtp.$DFILE.tsv","");
processTSV("my/dt.$DFILE.tsv");

saveSynonyms();
