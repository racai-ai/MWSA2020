from pathlib import Path
from sentence_transformers import SentenceTransformer
import scipy

DFILE="english_nuig"
DTYPE="test"

print("Running for %s / %s"%(DTYPE,DFILE))

print("Loading BERT model")
model = SentenceTransformer('/data/wordembeddings/BERT/bert-large-nli-stsb-mean-tokens')

print("Begin annotation")

lnum=0
with Path("my/sentences/%s_%s_all_dist.txt"%(DTYPE,DFILE)).open('w') as writer:
    with Path("%s/%s.tsv"%(DTYPE,DFILE)).open('r') as reader:
        lines = (line.strip().split('\t') for line in reader)
        for word,pos,d1,d2 in lines:
            lnum+=1
            print("Line %d"%(lnum))
            se=model.encode([d1,d2])
            d=scipy.spatial.distance.cosine(se[0],se[1])
            writer.write("%f\n"%(d));
