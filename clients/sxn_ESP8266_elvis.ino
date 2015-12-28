#include <ESP8266WiFi.h>
//==================================================
// sxn_ESP8266_elvis.ino
// 2015-12-27
//==================================================
//History
//==================================================

// char* ssid     = "bridge";
//const char* password = "6301166614";
const char* ssid     = "TeliaGateway30-91-8F-2B-51-83";
const char* password = "C150C3A2F2";
const char* host = "78.67.160.17";
const char* clientName = "ESPInterrupt";
IPAddress ipAddress;

const int PIN_INTERRUPT  = 4;
const int PIN_LED_PULSE  = 5;
const int PIN_LED_STATUS = 14;

unsigned long time1, time2, data;
//==================================================
#define SWID 2015
#define DEVID 1218 
#define SIDN  1   // No of SIDs
#define SID1 901  
#define SID2 902  
#define SID3 903 
#define SID4 904 
#define SID5 905  
#define SID6 906
#define SID7 907
#define SID8 908
int g_device_delay = 20;
int g_debug = 0;
//==================================================
// This code supports 2 decimals only
#define NFLOAT 2  // No of decimals i float value
// Arduino states
#define MAX_SID 10
#define MAX_ORDERS 100
int g_sids[10] = {SIDN,SID1,SID2,SID3,SID4,SID5,SID6,SID7,SID8};

// Arduino-RPi protocol
#define NABTON_DATA     1 
#define NABTON_LATEST   2 
#define NABTON_MAILBOX  3 
volatile int g_sendData = LOW;
volatile int g_watt;
char g_sIp[40];
//=================================================
void NB_sendFloatData(int sid, float value)
//=================================================
{

    char sValue[15];
    digitalWrite(PIN_LED_STATUS,HIGH);
    dtostrf(value,7, NFLOAT, sValue);
 
    WiFiClient client;
    const int httpPort = 80;
    if (!client.connect(host, httpPort)) {
      Serial.println("connection failed");
      return;
    }
 
    String urlPath        = "/sxndata/index.php";
    String arduinoSim     = "?mid=1&nsid="+String(SIDN)+"&sid1="+String(sid)+"&dat1="+String(sValue);
    String nameString     = "&name=" + String(clientName);;
    String ipString       = "&ip=" + String(g_sIp);
 
    client.print(String("GET ") + urlPath + arduinoSim + nameString + ipString + " HTTP/1.1\r\n" +
                 "Host: " + host + "\r\n" +
                 "Connection: close\r\n\r\n");
    delay(10);
//    while(client.available()){
//      String line = client.readStringUntil('\r');
//      if(g_debug == 1)Serial.print(line);
//      // TBD receive SID mail
//    }
    
    digitalWrite(PIN_LED_STATUS,LOW);
} 
//======================================
void pulse()
//======================================
{ 
      time2 = time1;
      time1 = millis();
      data = time1 - time2;
      if(data < 100)
      {
        time1 = time2;
        return;
      }
      float watt = 3600.0/data*1000.0;
      digitalWrite(PIN_LED_PULSE,HIGH);
      digitalWrite(PIN_LED_PULSE,LOW);
      g_watt = watt;
      g_sendData = HIGH;
}
//==================================================
void setup() {
//==================================================

  pinMode(PIN_LED_PULSE, OUTPUT);
  pinMode(PIN_LED_STATUS, OUTPUT);
  pinMode(PIN_INTERRUPT, INPUT); 

  
  Serial.begin(115200);

  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");  
  Serial.println("IP address: ");
  ipAddress = WiFi.localIP();
  Serial.println(ipAddress);
  sprintf(g_sIp,"%d.%d.%d.%d", ipAddress[0],ipAddress[1],ipAddress[2],ipAddress[3]);

  attachInterrupt(digitalPinToInterrupt(PIN_INTERRUPT), pulse, FALLING);
  
  digitalWrite(PIN_LED_PULSE,LOW);
  digitalWrite(PIN_LED_STATUS,LOW);
}

int count=0;
//==================================================
void loop() {
//==================================================
    if(g_sendData == HIGH)
    {
      g_sendData = LOW;
      NB_sendFloatData(SID1, g_watt);
    }
}
