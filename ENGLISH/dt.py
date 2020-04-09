# -*- coding: utf-8 -*-
from sklearn import tree
from sklearn.model_selection import cross_val_score
from sklearn.model_selection import GridSearchCV
from sklearn.metrics import classification_report
from pathlib import Path
import numpy as np
import matplotlib.pyplot as plt
import pydotplus
import collections

DTYPE="train"
DFILE="english_nuig"
K=10
#DT="none"

def loadFeatures(fname):
    ret=[]
    infile=Path(fname).open(mode="r")
    for line in infile:
        data=[]
        for s in line.strip().split("\t"):
            data.append(float(s))
        ret.append(data)
    infile.close()
    return ret

def loadResults(fname):
    ret=[]
    infile=Path(fname).open(mode="r")
    for line in infile:
        ret.append(int(line.strip()))
    infile.close()
    return ret

def loadFeatureNames(fname):
    ret=[]
    infile=Path(fname).open(mode="r")
    for line in infile:
        ret.append(line.strip())
    infile.close()
    return ret


dataX=loadFeatures("my/dt/%s_%s_features.txt"%(DTYPE,DFILE))
dataY=loadResults("my/dt/%s_%s_result.txt"%(DTYPE,DFILE))
featureNames=loadFeatureNames("my/dt/%s_%s_featurenames.txt"%(DTYPE,DFILE))

#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=6,max_features='auto',min_samples_leaf=11,min_samples_split=4)
#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=4,max_features=0.8,min_samples_leaf=14,min_samples_split=9)
#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=6,max_features=0.8,min_samples_leaf=12,min_samples_split=11)
#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=6,max_features='auto',min_samples_leaf=6,min_samples_split=2)
# 88.51
#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=7,max_features='auto',min_samples_leaf=3,min_samples_split=2)
#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=9,max_features='auto',min_samples_leaf=10,min_samples_split=2)

# cu stem
#dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=8,max_features='auto',min_samples_leaf=14,min_samples_split=4)
dt=tree.DecisionTreeClassifier(criterion="entropy",max_depth=10,max_features='auto',min_samples_leaf=3,min_samples_split=2)



print("K-fold cross validation (K=%d)"%(K))
scores=cross_val_score(dt,dataX,dataY,cv=K)
print("Accuracy: %0.2f (+/- %0.2f)" % (scores.mean(), scores.std() * 2))

print("Training")
dt.fit(dataX,dataY)

print("Predict")
y=dt.predict(dataX)
with Path("my/dt/%s_%s_dt.txt"%(DTYPE,DFILE)).open("w") as writer:
    for i in range(0,len(y)):
        writer.write("%d\n"%(y[i]))

print("Saving image")
plt.figure(dpi=600)
tree.plot_tree(dt,filled=True)
plt.savefig("my/dt_pyplot.png")

# Visualize data
dot_data = tree.export_graphviz(dt,
                                feature_names=featureNames,
                                out_file=None,
                                filled=True,
                                rounded=True,
                                class_names=["none","related","broader","narrower","exact"],
                                rotate=True
                                )
graph = pydotplus.graph_from_dot_data(dot_data)

colors = ('turquoise', 'orange')
edges = collections.defaultdict(list)

for edge in graph.get_edge_list():
    edges[edge.get_source()].append(int(edge.get_destination()))

for edge in edges:
    edges[edge].sort()    
    for i in range(2):
        dest = graph.get_node(str(edges[edge][i]))[0]
        dest.set_fillcolor(colors[i])

graph.write_png('my/dt_graphviz.png')

