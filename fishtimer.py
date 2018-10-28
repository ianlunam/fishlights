#!/usr/bin/python
from __future__ import division
from time import sleep

from array import array
from ola.ClientWrapper import ClientWrapper
from ola.DMXConstants import DMX_MIN_SLOT_VALUE, DMX_MAX_SLOT_VALUE, \
                             DMX_UNIVERSE_SIZE

import sys, pickle, json, os, datetime

SEND_INTERVAL = 250
SAVE_EVERY = 20
LAST_COUNT = 0
CALC_INTERVAL = 1000
UNIVERSE = 1
DATASTORE = "pickle.data"
SCHEDULE = "schedule.conf"

FULLOFF = [DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE]
FULLON = [DMX_MAX_SLOT_VALUE,DMX_MAX_SLOT_VALUE,DMX_MAX_SLOT_VALUE,DMX_MIN_SLOT_VALUE]
BLUEON = [DMX_MIN_SLOT_VALUE,DMX_MAX_SLOT_VALUE,DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE]
WHITEON = [DMX_MAX_SLOT_VALUE,DMX_MIN_SLOT_VALUE,DMX_MAX_SLOT_VALUE,DMX_MIN_SLOT_VALUE]

def stringToSeconds(stuff):
  asTime = datetime.datetime.strptime("{}:00".format(stuff), "%H:%M:%S")
  return (asTime.hour * 3600) + (asTime.minute * 60) + asTime.second

def sendDMX():
  output = array('B')
  for x in GLOBAL_SEND:
    output.append(x)
  WRAPPER.Client().SendDmx(UNIVERSE, output)
  sys.stdout.flush()
  WRAPPER.AddEvent(SEND_INTERVAL, sendDMX)

def receiveData(data):
  global LAST_COUNT
  LAST_COUNT += 1
  if LAST_COUNT >= SAVE_EVERY:
    pickle.dump(GLOBAL_SEND, open(DATASTORE, "wb"))
    LAST_COUNT = 0

def calculate():
  global GLOBAL_SEND
  global GLOBAL_CONFIG
  lastMod = GLOBAL_CONFIG[0]
  schedule = GLOBAL_CONFIG[1]
  if lastMod < os.path.getmtime(SCHEDULE):
    print("Loading config from {}".format(SCHEDULE))
    with open(SCHEDULE, 'r') as schedule_file:
      schedule = json.load(schedule_file)
      GLOBAL_CONFIG = ([os.path.getmtime(SCHEDULE), schedule])

  now = (datetime.datetime.now().hour * 3600) + (datetime.datetime.now().minute * 60) + datetime.datetime.now().second
  for channel in schedule['schedules']:
    prevKey = 0
    for key in sorted(channel['schedule']):
      keyTime = stringToSeconds(key)
      if keyTime > now:
        prevTime = stringToSeconds(prevKey)
        prevSetting = int(channel['schedule'][prevKey])
        nextSetting = int(channel['schedule'][key])
        progress = (now - prevTime) / (keyTime - prevTime)
        difference =  nextSetting - prevSetting
        shouldBe = round(prevSetting + (difference * progress))
        currently = GLOBAL_SEND[int(channel['channel'])]
        if currently > shouldBe:
          GLOBAL_SEND[int(channel['channel'])] = max(min(currently - 1, DMX_MAX_SLOT_VALUE), DMX_MIN_SLOT_VALUE)
          print("Updating {} to {}".format(channel['channel'], GLOBAL_SEND[int(channel['channel'])]))
        elif currently < shouldBe:
          GLOBAL_SEND[int(channel['channel'])] = max(min(currently + 1, DMX_MAX_SLOT_VALUE), DMX_MIN_SLOT_VALUE)
          print("Updating {} to {}".format(channel['channel'], GLOBAL_SEND[int(channel['channel'])]))
        break
      prevKey = key
  WRAPPER.AddEvent(CALC_INTERVAL, calculate)
    
print("Starting...")
GLOBAL_SEND = FULLOFF
try:
  GLOBAL_SEND = pickle.load(open(DATASTORE, "rb"))
except:
  pass

GLOBAL_CONFIG = [0,0]
WRAPPER = ClientWrapper()

WRAPPER.AddEvent(SEND_INTERVAL, sendDMX)
WRAPPER.AddEvent(CALC_INTERVAL, calculate)
WRAPPER.Client().RegisterUniverse(UNIVERSE, WRAPPER.Client().REGISTER, receiveData)

# Kick it all off
WRAPPER.Run()
