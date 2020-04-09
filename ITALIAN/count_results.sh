#!/bin/sh

LANG=italian
TYPE=test

for s in 'exact' 'none' 'narrower' 'broader' 'related'
do 
    echo $s
    cat my/dt.$LANG.tsv | awk 'BEGIN {FS="\t"}; {print $5};' | grep $s | wc -l
done

echo "Total linii:"
cat $TYPE/$LANG.tsv | wc -l

echo "Distinct words:"
cat $TYPE/$LANG.tsv | awk 'BEGIN {FS="\t"}; {print $1}' | uniq | wc -l
