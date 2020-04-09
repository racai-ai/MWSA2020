from pathlib import Path
from sentence_transformers import SentenceTransformer
import scipy

DFILE="spanish"
DTYPE="train"

print("Running for %s / %s"%(DTYPE,DFILE))

print("Loading BERT model")
model = SentenceTransformer('/data/wordembeddings/BERT/distiluse-base-multilingual-cased')

print("Begin annotation")

with Path("my/sentences/%s_%s_sentences.txt"%(DTYPE,DFILE)).open('r') as reader:
    lines = (line.strip().split('\t') for line in reader)
    sentences=dict((int(number), sentence) for number,sentence in lines)

lnum=0
with Path("my/sentences/%s_%s_pairs_dist.txt"%(DTYPE,DFILE)).open('w') as writer:
    with Path("my/sentences/%s_%s_pairs.txt"%(DTYPE,DFILE)).open('r') as reader:
        lines = (line.strip().split('\t') for line in reader)
        for n1,n2 in lines:
            lnum+=1
            print("Line %d"%(lnum))
            n1=int(n1)
            n2=int(n2)
            s1=sentences[n1]
            s2=sentences[n2]
            se=model.encode([s1,s2])
            d=scipy.spatial.distance.cosine(se[0],se[1])
            writer.write("%f\n"%(d));
