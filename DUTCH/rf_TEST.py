# -*- coding: utf-8 -*-
from sklearn import tree
from sklearn import ensemble
from sklearn.model_selection import cross_val_score
from sklearn.model_selection import GridSearchCV
from sklearn.metrics import classification_report
from pathlib import Path
import numpy as np
import matplotlib.pyplot as plt
import pydotplus
import collections

DTYPE="train"
DFILE="dutch"
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

# 89.1
#dt=ensemble.RandomForestClassifier(n_estimators=1000,criterion="entropy",max_depth=7,max_features='auto',min_samples_leaf=3,min_samples_split=2)
#dt=ensemble.RandomForestClassifier(n_estimators=100,criterion="entropy",max_depth=7,max_features=0.8,min_samples_leaf=3,min_samples_split=2)

# cu stem
dt=ensemble.RandomForestClassifier(n_estimators=1000,criterion="entropy",max_depth=5,max_features='auto',min_samples_leaf=3,min_samples_split=2)


print("K-fold cross validation (K=%d)"%(K))
scores=cross_val_score(dt,dataX,dataY,cv=K)
print("Accuracy: %0.2f (+/- %0.2f)" % (scores.mean()*100.0, scores.std() * 2 * 100.00))

print("Training on train")
dt.fit(dataX,dataY)

print("Predict on train")
y=dt.predict(dataX)
with Path("my/dt/%s_%s_dt.txt"%(DTYPE,DFILE)).open("w") as writer:
    for i in range(0,len(y)):
        writer.write("%d\n"%(y[i]))

dataX=loadFeatures("my/dt/test_%s_features.txt"%(DFILE))
print("Predict on test")
y=dt.predict(dataX)
with Path("my/dt/test_%s_dt.txt"%(DFILE)).open("w") as writer:
    for i in range(0,len(y)):
        writer.write("%d\n"%(y[i]))
