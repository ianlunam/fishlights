#!/usr/bin/python
from __future__ import division
from repeatedtimer import RepeatedTimer
from time import sleep

from array import array
from ola.ClientWrapper import ClientWrapper
from ola.DMXConstants import DMX_MIN_SLOT_VALUE, DMX_MAX_SLOT_VALUE

import sys, pickle, json, os, datetime

SEND_INTERVAL = 0.25
SAVE_INTERVAL = 5
CALC_INTERVAL = 1
UNIVERSE = 1
DATASTORE = "pickle.data"
SCHEDULE = "schedule.conf"

FULLOFF = [DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE,DMX_MIN_SLOT_VALUE]
FULLON = [DMX_MAX_SLOT_VALUE,DMX_MAX_SLOT_VALUE,DMX_MAX_SLOT_VALUE,DMX_MAX_SLOT_VALUE]

# Convert HH:MM into seconds so far today
def stringToSeconds(stuff):
  asTime = datetime.datetime.strptime("{}:00".format(stuff), "%H:%M:%S")
  return (asTime.hour * 3600) + (asTime.minute * 60) + asTime.second

# Send data array (assumes channels are in numerical order starting at 1)
def sendDMX(universe, data, client):
  output = array('B')
  for x in data:
    output.append(x)
  client.SendDmx(universe, output)
  sys.stdout.flush()

# Save universe and current settings
def saveData(sender):
  pickle.dump([sender.getArgs()[0], sender.getArgs()[1]], open(DATASTORE, "wb"))
    
def calculate(sender, client):
  # Get schedule from sender data attribute
  loadedData = sender.getData()
  lastMod = loadedData[0]
  schedule = loadedData[1]

  # Reload schedule file if changed
  if lastMod < os.path.getmtime(SCHEDULE):
    print("Loading config from {}".format(SCHEDULE))
    with open(SCHEDULE, 'r') as schedule_file:
      schedule = json.load(schedule_file)
      # Store new schedule in sender object data attribute
      sender.setData([os.path.getmtime(SCHEDULE), schedule])

  now = (datetime.datetime.now().hour * 3600) + (datetime.datetime.now().minute * 60) + datetime.datetime.now().second
  liveSettings = sender.getArgs()
  changed = False
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
        currently = liveSettings[1][int(channel['channel'])]
        if currently > shouldBe:
          liveSettings[1][int(channel['channel'])] = max(min(currently - 1, DMX_MAX_SLOT_VALUE), DMX_MIN_SLOT_VALUE)
          changed = True
        elif currently < shouldBe:
          liveSettings[1][int(channel['channel'])] = max(min(currently + 1, DMX_MAX_SLOT_VALUE), DMX_MIN_SLOT_VALUE)
          changed = True
        break
      prevKey = key
  if changed:
    # Store new settings in sender object args attribute
    sender.setArgs(liveSettings[0], liveSettings[1], client)
    
print("Starting...")
# Defaults
savedData = [UNIVERSE, FULLOFF]
# Previous saved state
try:
  savedData = pickle.load(open(DATASTORE, "rb"))
except:
  pass

# DMX client by OLA
client = ClientWrapper().Client()

# Start threads
sender = RepeatedTimer(SEND_INTERVAL, sendDMX, savedData[0], savedData[1], client)
saver = RepeatedTimer(SAVE_INTERVAL, saveData, sender)

# Loop forever
try:
  while True:
    calculate(sender, client)
    sleep(CALC_INTERVAL)
finally:
  saver.stop()
  sender.stop()
  print("... the end")

