#==================================================
# RPI_elvis.py
# 2015-12-11 
#==================================================
# SWID platform,application,version
# platform: 1=arduino(RPi), 2=raspberryPi(arduino), 3=raspberryPi(standalone), 4=arduino(standalone), 5=android
swid = 21301
devid = 9999

import time
import datetime
import RPi.GPIO as GPIO
import httplib

GPIO.setmode(GPIO.BOARD)
GPIO.setup(12, GPIO.OUT) # Write file led
GPIO.setup(16, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(18, GPIO.OUT) # HTTP req led
#-------------------
#	3.3		5.0	-------->to 5.0 v
#	-		-	
#	-		GND	-------->to ground
#	7		8
#	-		10
#	17		12	-------->to LED (write to file)
#	-		-
#	-		16	-------->to signal
#	-		18	-------->to LED (HTTP request)
#-------------------
t1 = 0
t2 = 0
dt = 0
p1 = 0
p2 = 0
dp = 0
#==================================================
# Configuration
#==================================================
sid       = 5
boti      = 100
nb_server = 'http://78.67.160.17/'
log_local  = 1
log_server = 1
#==================================================

#==================================================
def pulseEvent(x):
	global t1,t2,dt,log_file,nb_server,p1,p2,dp
	nb_warning = '-'
	t2 = t1
	t1 = time.time()
	dt = t1 - t2
	elpow = 3600/dt

	if log_server == 1:
		GPIO.output(18,True)
		p1 = time.time()
		conn = httplib.HTTPConnection(nb_server)
		#t_req = "/nabton/NDSServer/NDSCollector.php?mid=1&sid=%d&data=%.2f" % (sid,elpow)
		t_req = "/sxndata/index.php?mid=1&swid=%d&devid=%d&nsid=1&sid1=%d&dat1=%.2f" % (swid,devid,sid,elpow)
		try:
			conn.request("GET", t_req)
			try:
				r1 = conn.getresponse()
				#print ("x%sx z%sz " % (r1.status, r1.reason))
				#data1 = r1.read()
				#print data1
				if r1.status == 200:
					GPIO.output(18,False)
			except:
				print "exeption GET"
		except:
			print "exception HTTP"
		conn.close()
		p2 = time.time()
		dp = p2 - p1
		maxpow = 36000/dp
		if maxpow < elpow:
			nb_warning = '**** Slow connection ****'
		
	if log_local == 1:
		GPIO.output(12,True)
		with open(log_file, 'a') as out_file:
			ts = datetime.datetime.fromtimestamp(t1).strftime('%H:%M:%S')
			out_file.write("%s %.3f %.3f %s\n" % (ts,elpow,dp,nb_warning))
			out_file.close()
			GPIO.output(12,False)
			
	#print("interrupt detected: %8.3f Power: %.3f Mode: %d,%d Answ=%.3f" % (dt,elpow,log_local,log_server,dp))

#==================================================
# Interrupt
GPIO.add_event_detect(16, GPIO.FALLING, callback=pulseEvent,bouncetime=boti)
#==================================================
while True:
	try:
		today = datetime.date.today()
		log_file = "data-%d-%s.nbc" % (sid,today)
		time.sleep(1)
		#print "Wait"
	except KeyboardInterrupt:
		GPIO.cleanup()
		quit()
#==================================================		
GPIO.cleanup()

