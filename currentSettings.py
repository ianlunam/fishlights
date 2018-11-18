#!/usr/bin/python
import json


DATASTORE = '/etc/fishtimer/current.data'
try:
  with open(DATASTORE, "r") as f:
    GLOBAL_SEND = json.load(f)
    print("Happy data: {}".format(GLOBAL_SEND))
except:
  pass

