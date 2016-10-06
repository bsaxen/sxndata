#!/usr/bin/env python
from __future__ import print_function
from datetime import date, datetime, timedelta
import mysql.connector
import socket

#================================================
#TCP_IP = '127.0.0.1'
#TCP_PORT = 5005
#BUFFER_SIZE = 20  # Normally 1024, but we want fast response

#s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
#s.bind((TCP_IP, TCP_PORT))
#s.listen(1)
#conn, addr = s.accept()
#print 'Connection address:', addr
#while 1:
#   data = conn.recv(BUFFER_SIZE)
#   if not data: break
#   print "received data:", data
#   conn.send(data)  # echo
#conn.close()
#================================================
UDP_IP = "127.0.0.1"
UDP_PORT = 5005

sock = socket.socket(socket.AF_INET, # Internet
                     socket.SOCK_DGRAM) # UDP
sock.bind((UDP_IP, UDP_PORT))
  
while True:
    data, addr = sock.recvfrom(1024) # buffer size is 1024 bytes
    print "received message:", data
#================================================

cnx = mysql.connector.connect(user='scott', password='tiger',
                              host='127.0.0.1',
                              database='employees')

#cnx = mysql.connector.connect(user='scott', database='employees')
cursor = cnx.cursor()

timestamp = datetime.now().date()

sql = "INSERT INTO SID%d (value) VALUES (%f)" % (sid,value)

cursor.execute(sql)

cnx.commit()

cursor.close()
cnx.close()

