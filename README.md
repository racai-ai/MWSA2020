# MWSA2020
This repository contains the system that participated in the Monolingual Word Sense Alignment competition in 2020

Each folder contains the files used for the competition. Generally there are the same files (the system's modules). They are supposed to be executed in order of their start number, as detailed below. For each language there is a difference in the RandomForest parameters (rf_TEST.py and rf.py)  as well as in the features used (600_prepare_dt.php). Some modules may not be used for a certain language.

There are some files which are not actually used but contains some other ideas explored for the task. These either produced worst results or maybe were not completely explored due to lack of time.

common.php
- used in all php files. Contains the variables DFILE and DTYPE setting the corpus to be used.
- DTYPE should be first set to train, execute evrything then set to test and re-execute everything

01_analyze.php
- will analyze the corpus and construct statistics that will be used later (saved in the "my" folder). Should be used with DTYPE=train in common.php
 
02_stats.php
- displays statistics on both train and test

03_preprocess.php
- preprocess the corpus (cleanup, split definitions into sub-definitions)

04_analysis.php
- intermediary analysis (not used)

10_process.php
- process using regular word embeddings (not used)

100_lesk.php
- Lesk algorithm on words

110_lesk_lemma.php
- Lesk algorithm on lemma

120_lesk_stem.php
- Lesk algorithm on stems

130_lesk_catvar.php
- Lesk algorithm using CatVar

200_vn.php
- attempt at using VerbNet (not used)

201_vn.php
- attempt at using VerbNet (not used)

300_graph.php
- Graph-based implementation (not used)
310_graph2.php
- Graph-based implementation (not used)
320_graph3.php
- Graph-based implementation
330_graph4.php
- Graph-based implementation (not used)
340_graph5.php
- Graph-based implementation (not used)

400_split.php
- view some statistics regarding definition complexity (not used)

500_extract_sentences.php
- extract sub-sentences for computing BERT-based scores

bert_dist_all.py
bert_dist.py
test_bert_dist_all.py
test_bert_dist.py
- these Python processes must be executed after 500_extract_sentences.php and will compute scores using BERT-like embeddings. 
They require the SentenceTransformers (https://github.com/UKPLab/sentence-transformers) package to be installed.
Additionally the pre-trained bert-large-nli-stsb-mean-tokens (https://www.kaggle.com/marceloo/bertlargenlistsbmeantokens) model is used. 
The location of the model must be set in each of the files.  

501_bert_score.php
502_bert_score_max.php
503_bert_score_all.php
- based on the result from the above python scripts, BERT-based scores will be computed

600_prepare_dt.php
- prepare a file with the features used in DecisionTree / RandomForest

rf.py
rf_TEST.py
- will run a RandomForest algorithm on the prepared features on train and test

601_dt_out.php
- processes DecisionTree / RandomForest output and produces final results

700_postprocess.php
- post processing script (not used)

900_combine.php
- a way to combine multiple algorithms output using a voting mechanism (not used)

999_compare.php
- compare with gold annotations and display statistics
