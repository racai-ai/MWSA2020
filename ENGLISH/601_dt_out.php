<?php

require_once "common.php";

$dt=explode("\n",file_get_contents("my/dt/${DTYPE}_${DFILE}_dt.txt"));

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
    global $DTYPE,$DFILE,$dt;
    
    
    $decision="none";
    $n=intval($dt[$lnum-1]);
    if($n==4)$decision="exact";
    else if($n==3)$decision="narrower";
    else if($n==2)$decision="broader";
    else if($n==1)$decision="related";
    
    file_put_contents("my/dt.$DFILE.tsv",implode("\t",array_merge(array_slice($line,0,4),[$decision]))."\n",FILE_APPEND);
}

file_put_contents("my/dt.${DFILE}.tsv","");

processTSV("$DTYPE/$DFILE.tsv");
