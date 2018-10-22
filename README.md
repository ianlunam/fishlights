# FishLights README
> A project to control aquarium lights by computer

Having followed a similar path to [aquacrazed's](https://www.nano-reef.com/forums/topic/381724-fluval-evo-135-crazy-sps-growth-after-lighting-upgrade/) lighting upgrade, but not wanting to lay out on multiple ramp timers, especially if they didn't give me much control, I decided to make something myself.

## Hardware
- Asus Eeebox (£40 - eBay)
- Lixada USB to DMX Interface Adapter (£16 - Amazon)
- JOYLIT 4 Channel DMX512 Decoder (£19 - Amazon) though only currently using 3 channels
- Sunydeal 90W DC 15V-24V Laptop Power Supply (£18 - Amazon)
- DC Pigtail 2.1x5.5mm Cables 10xMale and 10xFemale (£7 - Amazon)
- 5A Strip Connector Block (£1 - eBay)
- 2.1x5.5mm Y Cable Female to Male x 2 (£4.41 - Amazon) because running 2 pairs of strips
- 24v-12v Stepdown converter (£14 - Amazon) because the old lights are 12v and the new are 24v.

Total: ~£120

Could have saved money with:
- a cheaper power supply. 60w would have been enough.
- cheaper decoder. There are bare board ones around £10
- used an existing PC
- had all the LEDs at one power rating

might be possible for ~£60

## Software
- Ubuntu
- [OLA](https://www.openlighting.org/ola/) python libraries
- Some code knicked from [stackoverflow](https://stackoverflow.com/a/13151299) for threading

## Files
- fishlights.service - systemd daemon startup script
- fishtimer.py - the code that does the work
- repeatedtimer.py - code from stackoverflow with extra methods for data control
- schedule.conf - json format schedule information
- pickle.data - data store for last known state for recovery

## Threads
- data sender - sends data to DMX units every SEND_INTERVAL seconds (default: 0.25)
- data saver - saves current state in pickle file every SAVE_INTERVAL seconds (default: 5)
- main thread - calculates what current state should be every CALC_INTERVAL seconds (default: 1)

## Features
- doesn't allow changes faster than 1 step per CALC_INTERVAL seconds
- reloads state on restart
- reloads schedule.conf on change
- supports up to 512 channels (DMX512 standard) per universe by daisy-chaining DMX units
- supports 1 DMX universe - UNIVERSE

## Calculations
- for each channel
..* finds time inbetween schedule points
..* determines what value should be now
..* ensures value between DMX_MIN_SLOT_VALUE and DMX_MAX_SLOT_VALUE
..* ensures value no more than current + or - 1
- updates data sender

## Joylit unit wiring
- female pigtail connected to INPUT V- (black) and V+ (red) for power supply
- 3x male pigtails for channels
..* red connected to COM(+) (used 1 section of block connector and stuffed them all in)
..* black connected to CH1(-), CH2(-), CH3(-)
- DIP switches - 1=down, all others up (sets channels 1-4)

## Stepdown Converter wiring
- female pigtail connected to stepdown converter input (using 2 sections of block connector)
- male pigtail connected to stepdown converter output (using 2 sections of block connector)

## Plug it all together
- Plug the power supply into the female pigtail
- Plug the Y connecters into CH1's and CH2's pigtails, and the pairs of 24v LEDs into those (I used CH1 for white and CH2 for blue)
- Plug the stepdown converter to CH3's pigtail and connect the 12v LEDs to that

## Computer setup
- I installed ubuntu 16.04.5 LTS as the operating system
- Installed [OLA from github](https://github.com/OpenLightingProject/ola) including python modules (requires some IT knowledge)
- Plug USB DMX interface into USB socket on server
- Plug interface into DMX Decoder
- ```lsusb``` in my case calls Lixada unit ```Van Ooijen Technische Informatica shared ID for use with libusb``` for some reason

## OLA Configuring universe
- ```ola_dev_info``` should show devices list. Note device number of your unit. Mine shows as:```Device 8: Anyma USB Device
  port 0, OUT```
- To patch universe 1 to this device: ```ola_patch --patch --device DEVID --port 0 --universe 1``` replacing DEVID with the device number above, 8 in my case

## Installing my code
- copy all files somewhere permanent belonging to your user. I used /home/ian/bin/
- change User and Group lines in fishlights.service to match your user and group
- change path to files in WorkingDirectory and ExecStart lines in fishlights.service to match where you put the files
- copy fishlights.service to /etc/systemd/system/ (requires root permission and/or use of sudo)
- get systemd to reload the new config ```systemctl daemon-reload``` as root or using sudo

## Adjust schedule in schedule.conf
- file format is json
- schedules is top level element and contains repeating groups for each schedule
- each schedule contains "channel", the value of which is the channel number
- each schedule contains "schedule" which is a repeating group of times
- each time if in the format "HH:MM" and has a value of the "channel" setting at that time
..* values are between 0 and 255 and are steps on the dimmer knob
..* 0=off
..* 255=on full
- if two consecutive times have different values the dimmer will transition from one value to the other progressively over the period of time
..* the fastest it will transition is at 1 step per second
..* if this takes longer than the specified period then so-be-it, it takes as long as it takes
..* the longer the period the more gradual the change will be
..* by default the fastest transition from off (0) to on (255) will take around 255 seconds (4 minutes 15 seconds)
- in the default config channel 2 sits at a dimmer setting of 50 through the middle of the day (that's my blue light)

## Starting the system
- ```systemctl start fishlights.service``` as root or using sudo
