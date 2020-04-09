<?php

require_once "common.php";
//require_once "vectors_oanc.php";
require_once "lemma.php";
require_once "lesk_common.php";
require_once "bert_common.php";
//require_once "synonyms_all.php";
//require_once "synonyms_context.php";
//require_once "synonyms_dict.php";
//require_once "graph_common.php";
//require_once "we_common.php";
require_once "split_common.php";
require_once "pos.php";
require_once "first_pos_common.php";

echo "Load stoplist\n";
$stoplist=loadStat("my/$DFILE/stoplist.txt");

echo "Load word stat\n";
$wordStat=loadStat("my/$DFILE/words.txt");

echo "Load lemma database\n";
loadLemmas();

echo "Load BERT data\n";
bert_loadSentences();
bert_loadPairs();
bert_loadPairsDist();
bert_loadDistAll();

echo "Load synonyms\n";
//loadSynonyms();
//loadSynonymsDict();

echo "Load POS\n";
loadPOSTags("resources/cgn.pos");

firstPOS_Init();

echo "Creating features\n";

function getPOSNumeric($pos){
    if($pos=="verb")return 0;
    if($pos=="adjective")return 1;
    if($pos=="adverb")return 2;
    if($pos=="noun")return 3;
    var_dump($pos);die("Unknown POS");
}

function getResultNumeric($type){
    if($type=="exact")return 4;
    if($type=="narrower")return 3;
    if($type=="broader")return 2;
    if($type=="related")return 1;
    if($type=="none")return 0;
    var_dump($type);die("Unknown result");
    return 0;
}

$features=[];
$featureNames=[];

function addFeature($name,$value){
    global $features,$featureNames;
    
    $features[]=$value;
    $featureNames[$name]=true;
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
    global $DTYPE,$DFILE,$bertDistAll,$features,$featureNames;
    
    if($DTYPE=="train")$result=getResultNumeric($data['type']);
    $features=[];

    $r=lesk_getData($data,false); // no lemma
    addFeature("maxCountDiff",abs($r['maxCount2']-$r['maxCount1']));
    addFeature("minCountDiff",abs($r['minCount2']-$r['minCount1']));
#    addFeature("totalCountDiff",abs($r['totalCount2']-$r['totalCount1']));
    addFeature("lesk",$r['intersect']);

    $lw1=getWords($line[2]);
    $lw2=getWords($line[3]);
    addFeature("totalCountDiff",abs(count($lw1)-count($lw2)));
    
    //$features[]=abs(count($data['def1'])-count($data['def2']));
    
/*    $o1=0;$o2=0;
    foreach($lw1 as $w)if(strcasecmp($w,"or")==0)$o1++;
    foreach($lw2 as $w)if(strcasecmp($w,"or")==0)$o2++;
    addFeature("numOrDiff",abs($o1-$o2));
*/
/*    $a1=0;$a2=0;
    foreach($lw1 as $w)if(strcasecmp($w,"also")==0)$a1++;
    foreach($lw2 as $w)if(strcasecmp($w,"also")==0)$a2++;
    addFeature("numAlsoDiff",abs($a1-$a2));
*/
/*    $and1=0;$and2=0;
    foreach($lw1 as $w)if(strcasecmp($w,"and")==0)$and1++;
    foreach($lw2 as $w)if(strcasecmp($w,"and")==0)$and2++;
    addFeature("numAndDiff",abs($and1-$and2));
*/
    
    addFeature("numCommaDiff",abs(substr_count($line[2],",")-substr_count($line[3],",")));

    $r=lesk_getData($data,true); // with lemma
    addFeature("leskLemma",$r['intersect']);
    
    $r=lesk_getData($data,false,true); // with stem
    addFeature("leskStem",$r['intersect']);

    $r=bert_getData($data);
    addFeature("BERTMinDist",round($r['minDist'],2));
    addFeature("BERTMaxDist",round($r['maxDist'],2));
    addFeature("BERTAvgDist",round($r['medDist'],2));
    
    addFeature("BERTDistAll",round($bertDistAll[$lnum-1],2));
    
//    $r=graph_getData($data);
//    addFeature("graphScore",$r['dist']);
    
    addFeature("POS",getPOSNumeric($data['pos']));
    
    //$r=we_getData($data);
    //$features[]=round($r['dist'],2);
    
    $r=split_getData($data);
    addFeature("single_multiple",$r['type']);
    
//    $r=firstPOS_getData($data);
//    addFeature("firstPOS",$r['same']);
    
    file_put_contents("my/dt/${DTYPE}_${DFILE}_features.txt",implode("\t",$features)."\n",FILE_APPEND);
    if($DTYPE=="train")file_put_contents("my/dt/${DTYPE}_${DFILE}_result.txt",$result."\n",FILE_APPEND);

}

@mkdir("my/dt");
file_put_contents("my/dt/${DTYPE}_${DFILE}_features.txt","");
if($DTYPE=="train")file_put_contents("my/dt/${DTYPE}_${DFILE}_result.txt","");
processTSV("$DTYPE/$DFILE.tsv");

file_put_contents("my/dt/${DTYPE}_${DFILE}_featurenames.txt",implode("\n",array_keys($featureNames)));


//saveSynonyms();
