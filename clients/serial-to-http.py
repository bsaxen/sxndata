#!/usr/bin/python
#==================================================
# serial-to-http.py
# Author: Benny Saxen
#==================================================
# apt-get install python-pip
# pip install pyserial
#==================================================
# History
#==================================================

#==================================================
#swid = N/A
#devid = N/A
import serial
import httplib
#import time

#============================================
# Read configuration
#============================================
print 'Version 2015-11-21'
file = open('serial-to-http.cfg','r')
nb=0
sxn_debug = 'YES';
for line in file:
    words=line.split()
    print words
    if words[0] == 'SXN_DEBUG':
        sxn_debug = words[1]
        print '-_- Debug Mode On ' + sxn_debug
    if words[0] == 'SXN_SERVER':
        sxn_server = words[1]
        nb=nb+1
        print '-_- SXN Server is defined: ' + sxn_server
    if words[0] == 'SXN_PATH':
        sxn_path = words[1]
        nb=nb+1
        print '-_- SXN Path defined: ' + sxn_path
    if words[0] == 'SXN_DEVICE':
        sxn_device = words[1]
        print '-_- Device defined: ' + sxn_device
    
ser = serial.Serial(sxn_device, 9600, timeout=None)
t_req = ser.readline().decode('utf-8')[:-2]
#time.sleep(5)    
while 1:
    t_req = ser.readline().decode('utf-8')[:-2]
    if t_req:
        if sxn_debug == 'YES':
            print t_req
        url=t_req.split(":")
        if sxn_debug == 'YES':
            print 'x'+url[0]+'xz'+url[1]+'z'
        if url[0][0] == '?' and url[1][0] == ' ':
            slen = len(url[0])
            if sxn_debug == 'YES':
                print url[1]
            cs = eval(url[1])
            if sxn_debug == 'YES':
                print ("len=%d cs=%d" % (slen, cs))
            if url[0][0] == '?' and cs == slen:
                if nb == 2:
                    req = sxn_path + url[0]
                    if sxn_debug == 'YES':
                        print 'y'+req+'y'
                    conn = httplib.HTTPConnection(sxn_server)
                    try:
                        conn.request("GET", req)
                        try:
                            r1 = conn.getresponse()
                            if sxn_debug == 'YES':
                                print ("SXN-_- %s %s " % (r1.status, r1.reason))
                            data1 = r1.read()
                            if sxn_debug == 'YES':
                                print data1
                            ser.write(data1+'\r\n')
                        except:
                            print '-_- No response from nb server'
                    except:
                        print '-_- Not able to connect to nb server'
                    conn.close()
                else:
                    print 'No NB Server configured'
            else:
                print 'No match'
        else:
            print "Out of sync"
ser.close()
# End of file
