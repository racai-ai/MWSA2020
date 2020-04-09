<?php

require_once "common.php";
require_once "vectors.php";

// idee : renunta la cuvinte care apar rar (poate din wikipedia)
// explicatiile ar tb sa foloseasca cuvinte frecvente

$stoplist=loadStat("my/$DFILE/stoplist.txt");
$wordStat=loadStat("my/$DFILE/words.txt");

function processWord($data,$line){
    global $stoplist,$wordStat,$DFILE,$DTYPE;
    
    $vec1=[];
    $vec1w=[];
    foreach($data['def1'] as $d){
	$words=getWords($d);
	$vec=[];
	$vecw=[];
	foreach($words as $w){
	    if(strlen($w)<2 || isset($stoplist[$w]))continue;
	
	    $vecw[]=mb_strtolower($w);
	    addToVec($vec,getWordVector(mb_strtolower($w)));
	}
	$vec1[]=$vec;
	$vec1w[]=$vecw;
    }

    $vec2=[];
    $vec2w=[];
    foreach($data['def2'] as $d){
	$words=getWords($d);
	$vec=[];
	$vecw=[];
	foreach($words as $w){
	    if(strlen($w)<2 || isset($stoplist[$w]))continue;
	
	    $vecw[]=mb_strtolower($w);
	    addToVec($vec,getWordVector(mb_strtolower($w)));
	}
	$vec2[]=$vec;
	$vec2w[]=$vecw;
    }
    
    echo $data['word']." ".$data['type']."\n";
    $done=false;
    $max_dist=0;
    foreach($vec1 as $i=>$v1){
	foreach($vec2 as $j=>$v2){
	
	    $intersect=array_intersect($vec1w[$i],$vec2w[$j]);
	    $found=false;
	    if(count(array_diff($vec1w[$i],$intersect))<4 && count(array_diff($vec2w[$j],$intersect))<4){
	    foreach($intersect as $w){
		if(isset($wordStat[$w]) && $wordStat[$w]<20){$found=true;break;}
	    }
	    }
	    
	    if($found){
		echo "[FOUND]\n";
		if(!$done){
		    file_put_contents("my/my.$DFILE.tsv",implode("\t",array_merge(array_slice($line,1,4),["exact"]))."\n",FILE_APPEND);
		    $done=true;
		}
	    }
	
	    $dist=getDistance($v1,$v2);
	    if($dist>$max_dist)$max_dist=$dist;
	    echo $dist." [".implode(" ",$vec1w[$i])."] [".implode(" ",$vec2w[$j])."]\n";
	}
    }
    
    if(!$done){
	$decision="none";
	if($max_dist>0.8)$decision="exact";
	else if($max_dist>0.6)$decision="narrower";
	else if($max_dist>0.5)$decision="broader";
	else if($max_dist>0.4)$decision="related";

	file_put_contents("my/my.$DFILE.tsv",implode("\t",array_merge(array_slice($line,1,4),[$decision]))."\n",FILE_APPEND);
    }
}



//loadVectorsGZ("cc.en.300.vec.gz");
file_put_contents("my/my.${DFILE}.tsv","");
$num=processTSV("$DTYPE/$DFILE.tsv");
