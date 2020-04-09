<?php

require_once "common.php";

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
    global $DTYPE,$DFILE;
    
    var_dump($data);die();
}

processTSV("$DTYPE/$DFILE.tsv");
