<?php

$GRAPH=[];

function emptyGraph(){
    global $GRAPH;
    $GRAPH=[];
}

function addNode($name,$label){
    global $GRAPH;
    
    if(isset($GRAPH[$name])){
	if(!in_array($label,$GRAPH[$name]['label']))
	    $GRAPH[$name]['label'][]=$label;
	return ;
    }
    
    $GRAPH[$name]=[
	'name'=>$name,
	'edges'=>[],
	'label'=>[$label],
	'dist'=>-1
    ];
}

function addEdge($n1, $n2){
    global $GRAPH;
    
    if(!isset($GRAPH[$n1]) || !isset($GRAPH[$n2])){
	die("\nERROR: GRAPH: NODES DO NOT EXIST IN GRAPH\n");
    }
    
    if($n1==$n2)return ;
    
    $GRAPH[$n1]['edges'][$n2]=true;
    $GRAPH[$n2]['edges'][$n1]=true;
}

function propagateLabel($label){
    global $GRAPH;
    
    foreach($GRAPH as $k=>&$node)$node['dist']=-1;
    
    for($isChange=true;$isChange;){
	$isChange=false;
	
	foreach($GRAPH as $k=>&$node){
	    if($node['dist']==-1){
		if(in_array($label,$node['label'])){
		    $node['dist']=0;
		    $isChange=true;
		}else{
		    foreach($node['edges'] as $n=>$t){
			$neigh=$GRAPH[$n];
			if($neigh['dist']>=0 && ($node['dist']==-1 || $node['dist']>$neigh['dist']+1)){
			    $node['dist']=$neigh['dist']+1;
			    $isChange=true;
			}
		    }
		}
	    }
	}
    }
}

function computeDistancesFromLabel($label){
    global $GRAPH;
    
    $dist=[];
    foreach($GRAPH as $k=>$node){
	if(!in_array($label,$node['label']))continue;
	
	if(!isset($dist[$node['dist']]))$dist[$node['dist']]=0;
	$dist[$node['dist']]++;
    }
    return $dist;
}

function computeDistancesFromWords($words){
    global $GRAPH;
    $dist=[];
    foreach($words as $w){
	if(!isset($GRAPH[$w]))continue;
	$node=$GRAPH[$w];
	
	if(!isset($dist[$node['dist']]))$dist[$node['dist']]=0;
	$dist[$node['dist']]++;

    }
    return $dist;
}

function getCountByLabel($label){
    global $GRAPH;

    $c=0;
    foreach($GRAPH as $k=>$node)
	if(in_array($label,$node['label']))$c++;

    return $c;
}
