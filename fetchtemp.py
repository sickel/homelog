#!/usr/bin/python

"""
 Fetching json data  - storing them in a postgres database 

	Data source, e.g. an arduino temperature logger

  Expecting to receive a json string with a "temp"-array listing the
  temperatures from the sensors (it may also contain other information, but
  that will be discarded )
  e.g.
  {"temp":[40.50,20.50,-0.50],
   "address":["2852932640040","28A9D0264006D","2895D76E40050"]}

  The data source is not supposed to have a real time clock. If timestamps are
  needed, they must be set by the database (or added in in this script)

"""

import serial
import time
import logging
import logging.handlers
import ConfigParser
import os
import urllib
import json
import sys

pid=str(os.getpid())
appname="["+pid+"] storedata "
import psycopg2

pid=str(os.getpid())
appname="["+pid+"] storedata "
my_logger = logging.getLogger('MyLogger')
my_logger.setLevel(logging.DEBUG)
handler = logging.handlers.SysLogHandler(address = '/dev/log')
my_logger.addHandler(handler)
my_logger.debug(appname+': Starting up ')


config = ConfigParser.RawConfigParser()
config.read('/etc/homedata/fetchtemp.cfg')

"""
/etc/homedata/fetchtemp.cfg:
[server]
url=http://192.168.0.177/json
[database]
dbhost=localhost
dbusername=username
dbpassword=password
dbdatabase=database 
[system]
waittime=880
shortsleep=5
"""

dbdatabase=config.get('database','dbdatabase')	
dbusername=config.get('database','dbusername')
dbhost=config.get('database','dbhost')
dbpassword=config.get('database','dbpassword')
sleeptime=float(config.get('system','waittime'))
shortsleep=float(config.get('system','shortsleep'))
connstring="dbname='"+dbdatabase+"' user='"+dbusername+"' host='"+dbhost+"' password='"+dbpassword+"'"
#
dbtype='pgsql';

try:
   conn = psycopg2.connect(connstring)
   cur=conn.cursor()
except:
  my_logger.critical(appname+": Unable to connect to the database")
  sys.exit(2)

url=config.get('server','url')
# nosleep=false

while 1:
  nosleep=False
  my_logger.debug(appname+": Fetching data from "+url)
  try:
      f = urllib.urlopen(url)
      jsondata=json.load(f)
  except:
      my_logger.error(appname+"Could not fetch data from"+url)
      nosleep=True
# print jsondata
# print jsondata['temp']
# print jsondata['temp'][2]


# sql="insert into temps(temp,sensoraddr)values(%s,%s)";
  sql="select addmeasure(%s,(select id::integer from sensor where sensoraddr=%s))";
  for(i,val) in enumerate(jsondata['temp']):
      try:
          cur.execute(sql,(val,jsondata['address'][i]))
      except:
          my_logger.error(appname+": error 92 :"+str(sys.exc_info()))
  try:
      conn.commit()
  except:
      my_logger.error(appname+": error 96 :"+str(sys.exc_info()))
  my_logger.debug(appname+": Finished storing")
  if(nosleep):
      time.sleep(shortsleep)
  else:
      time.sleep(sleeptime)
