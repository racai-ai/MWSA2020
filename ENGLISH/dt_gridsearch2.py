# -*- coding: utf-8 -*-
from sklearn import tree
from sklearn.model_selection import cross_val_score
from sklearn.model_selection import GridSearchCV
from sklearn.metrics import classification_report
from pathlib import Path
import numpy as np
import matplotlib.pyplot as plt

DTYPE="train"
DFILE="english_nuig"
K=10

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

dataX=loadFeatures("my/dt/%s_%s_features.txt"%(DTYPE,DFILE))
dataY=loadResults("my/dt/%s_%s_result.txt"%(DTYPE,DFILE))


#dt=tree.DecisionTreeClassifier(max_depth=10,min_samples_split=4,min_samples_leaf=2)
#dt=tree.DecisionTreeClassifier()
#print("K-fold cross validation (K=%d)"%(K))
#scores=cross_val_score(dt,dataX,dataY,cv=K)
#print("Accuracy: %0.2f (+/- %0.2f)" % (scores.mean(), scores.std() * 2))

tunedParameters = [{
    'max_depth': [7,8,9],
    "min_samples_split":[2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20],
    "min_samples_leaf":[2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20],
    "criterion":["entropy"],
    "max_features":["auto",0.9,0.8]
}]

dt=GridSearchCV(tree.DecisionTreeClassifier(), tunedParameters,cv=5,n_jobs=200)
dt.fit(dataX,dataY)
print("Best parameters set found on development set:")
print()
print(dt.best_params_)
print()
print("Grid scores on development set:")
print()
means = dt.cv_results_['mean_test_score']
stds = dt.cv_results_['std_test_score']
for mean, std, params in zip(means, stds, dt.cv_results_['params']):
    print("%0.3f (+/-%0.03f) for %r"
              % (mean, std * 2, params))
print()


print("Detailed classification report:")
print()
print("The model is trained on the full development set.")
print("The scores are computed on the full evaluation set.")
print()
y_true, y_pred = dataY, dt.predict(dataX)
print(classification_report(y_true, y_pred))
print()

print("Training")
#dt.fit(dataX,dataY)

print("Predict")
y=dt.predict(dataX)
with Path("my/dt/%s_%s_dt.txt"%(DTYPE,DFILE)).open("w") as writer:
    for i in range(0,len(y)):
        writer.write("%d\n"%(y[i]))

#print("Saving image")
#plt.figure()
#tree.plot_tree(dt,filled=True)
#plt.savefig("my/dt.png")
