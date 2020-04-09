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

    file_put_contents("my/preprocess/$DTYPE/$DFILE.tsv",implode("\t",
	array_merge(
	    [$data['word'],$data['pos'],implode(";",$data['def1']),implode(";",$data['def2'])],
	    isset($data['type'])?[$data['type']]:[]
	))."\n",
	FILE_APPEND
    );
}

@mkdir("my");
@mkdir("my/preprocess");

foreach(["train","test"] as $DTYPE){
    @mkdir("my/preprocess/$DTYPE");
    file_put_contents("my/preprocess/$DTYPE/$DFILE.tsv","");
    processTSV("$DTYPE/$DFILE.tsv");
}
