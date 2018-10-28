#!/usr/bin/python
import pickle

data = pickle.load( open( "pickle.data", "rb" ) )
print("{}".format(data))
