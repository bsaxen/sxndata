//==================================================
// sxn_temperature_ethShield.ino
//==================================================
// History
//==================================================
// Software Id: 41001 2015-11-21  First version
// 2015-12-18: increased stepper resolution, corrected NB_sendToGateway
// 2015-12-28: Added name and ip in url
// 2016-01-06: Configurable server ip address
// 2016-01-08: Central configuration
//==================================================
#define NFLOAT 2  // No of decimals i float value
#define NSID  2   // No of SIDs
#define SID1 901  // Temp 1 and Control SID
#define SID2 902  // Temp 2
#define SID3 903  // Temp 3
#define SID4 904  // Temp 4
#define SID5 905  // 
#define SID6 906
#define SID7 907
#define SID8 908
int g_debug = 1;
const char* g_clientName = "ArduinoEthShield-test";
int g_device_delay = 3;
//==================================================
#define MAX_SID 8
//#define MAX_ORDERS 100
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
//char g_confServer[] = "192.168.1.97"; 
char g_confServer[80] ; 
char g_server[80]; 
int  g_sids[10] = {NSID,SID1,SID2,SID3,SID4,SID5,SID6,SID7,SID8};
char g_sIp[80];
char g_rbuf[4000];
int g_device_state = 0;
  
// Arduino-RPi protocol
#define NABTON_DATA     1 
#define NABTON_LATEST   2 
#define NABTON_MAILBOX  3 

#define LED_INTERNET  2
#define LED_REC_DATA  3
#define LED_SEND_DATA  5
#define LED_DEVICE_STATUS 12
#define LED_CONFIGURED 13

#define S_STARTED    1
#define S_NO_NETWORK 2
#define S_NETWORK    3
#define S_INTERNET   4
#define S_CONFIGURED 5
//=================================================
//
// D0 RX used for serial communication to server (Raspberry Pi)
// D1 TX used for serial communication to server (Raspberry Pi)
// D2 LED Internet
// D3 LED - Serial data received
// D4 IR Data
// D5 LED - Serial data sent
// D6 DIR Stepper
// D7 STEP Stepper
// D8 SLEEP Stepper
// D9 One Wire Data
// D10  RX Bluetooth device
// D11  TX Bluetooth device
// D12  LED Device status
// D13  LED Device Configured
//
// A0
// A1
// A2
// A3
// A4 SDA I2C OLED
// A5 SCL I2C OLED

// MEGA
// D20 SDA I2C OLED
// D21 SCL I2C OLED
// 
//=================================================
#include <stdio.h>
#include <SPI.h>
#include <Ethernet.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <SoftwareSerial.h>
//#include <IRremote.h>
#include <U8glib.h>

//=================================================
// Ethernet
//=================================================
IPAddress ip(192, 168, 1, 131); // If no DHCP
//IPAddress ip;
IPAddress ipAddress;
EthernetClient client;
//=================================================
// One Wire
//=================================================

#define ONE_WIRE_BUS 9
#define TEMPERATURE_PRECISION 12
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);
DeviceAddress device[MAX_SID];
int nsensors = 0;
//==================================================
// OLED I2C
//==================================================

//U8GLIB_SSD1306_128X64 u8g(13, 11, 10, 9);// SW SPI protocol(4 pins): SCK = 13, MOSI = 11, CS = 10, A0 = 9 
//U8GLIB_SSD1306_128X64 u8g(U8G_I2C_OPT_NONE); // Small display I2C protocol (2 pins)
U8GLIB_SH1106_128X64 u8g(U8G_I2C_OPT_NONE); // Large display
char dl[8][8],dm[8][8],dr[8][8];
//=================================================
void software_Reset() // Restarts program from beginning but does not reset the peripherals and registers
//=================================================
{
asm volatile ("  jmp 0");  
}  

//=================================================
void setState(int st) 
//=================================================
{
 g_device_state = st;
 if(g_debug == 1)Serial.print("Device State = ");Serial.println(st);
}
//=================================================
void NB_oledDraw() 
//=================================================
{
 u8g.firstPage();  
  do {
        draw();
  } while( u8g.nextPage() ); 
}
//=================================================
void draw()
//=================================================
{
  // Horizontal pixels: 0 - 120
  // Vertical pixels: 0 - 63
  //u8g.setFont(u8g_font_6x10);
  u8g.setFont(u8g_font_unifont);
  //u8g.setFont(u8g_font_osb21);
  /*u8g.drawStr( 0, 1, ".....");
  u8g.drawStr( 45, 1, ".....");
  u8g.drawStr( 90, 1, ".....");
  
  u8g.drawStr( 0, 63, "_____");
  u8g.drawStr( 45,63, "_____");
  u8g.drawStr( 90,63, "_____");*/
  
  u8g.drawStr( 0, 10, dl[1]);
  u8g.drawStr( 0, 27, dl[2]);
  u8g.drawStr( 0, 45, dl[3]);
  u8g.drawStr( 0, 62, dl[4]);

  u8g.drawStr( 45, 10, dm[1]);
  u8g.drawStr( 45, 27, dm[2]);
  u8g.drawStr( 45, 45, dm[3]);
  u8g.drawStr( 45, 62, dm[4]);

  u8g.drawStr( 90, 10, dr[1]);
  u8g.drawStr( 90, 27, dr[2]);
  u8g.drawStr( 90, 45, dr[3]);
  u8g.drawStr( 90, 62, dr[4]);  

}
//=================================================
void blinkLed(int led,int number, int onTime)
//================================================= 
{
  int i;
  for(i=0;i<number;i++)
  {
    digitalWrite(led,HIGH);
    delay(onTime);
    digitalWrite(led,LOW);
    delay(onTime);
  }
}
//=================================================
void NB_serialFlush()
//=================================================
{
  //Serial.print("flush:"); 
  while(Serial.available() > 0) {
    char t = Serial.read();
    //Serial.print(t); 
  }
  //Serial.println(":flushed"); 
}   

//=================================================
int NB_sendToGwy(int mid, int sid, float data, int other)
//=================================================
{
  //Serial.print("sid=");Serial.println(sid);
  int ixSid = 0,i,negative=0;
  char msg1[100],msg2[50],checksum[20];
     strcpy(msg1," ");
     strcpy(msg2," ");
     digitalWrite(LED_SEND_DATA,HIGH);
     // Mandatory part of message
     sprintf(msg1,"GET /sxndata/index.php?mid=%d&nsid=%d&sid1=%d",mid,1,sid);
if(g_debug==1){Serial.print("data:");Serial.println(data);}      
     if(mid == NABTON_DATA)
     {
       negative = 0;
       if(data < 0.0)
       {
          negative = 1;
          data = data*(-1.0);
       }
       // Get non-decimal part
       int part1 = floor(data);
if(g_debug==1){Serial.print("part1:");Serial.println(part1);}       
       // Get decimalpart
       float ftemp = (data - part1);
       for(i=1;i<=NFLOAT;i++)ftemp=ftemp*10;
if(g_debug==1){Serial.print("ftemp:");Serial.println(ftemp);}   
       int part2 = round(ftemp);
if(g_debug==1){Serial.print("part2:");Serial.println(part2);}          
       // if negative
       if(negative == 0)
       {
         if(part2 < 10)
           sprintf(msg2,"&name=%s&ip=%s&dat1=%d.0%d",g_clientName,g_sIp,part1,part2);
         else 
           sprintf(msg2,"&name=%s&ip=%s&dat1=%d.%d",g_clientName,g_sIp,part1,part2);
       }
       if(negative == 1)
       {
         if(part2 < 10)
           sprintf(msg2,"&name=%s&ip=%s&dat1=-%d.0%d",g_clientName,g_sIp,part1,part2);
         else 
           sprintf(msg2,"&name=%s&ip=%s&dat1=-%d.%d",g_clientName,g_sIp,part1,part2);
       }
       strcat(msg1,msg2);
     }

    digitalWrite(LED_INTERNET,LOW);
    client.stop();
    if(client.connect(g_server, 80))
     {
       digitalWrite(LED_INTERNET,HIGH);
       if(g_debug==1){Serial.print("msg1=");Serial.println(msg1);}
       client.println(msg1);
       //client.println("Host: config.nabton.com");
       client.println("Connection: close");
       client.println();
     }

     digitalWrite(LED_SEND_DATA,LOW);
     return(other);
}
//=================================================
void clearOled()
//================================================= 
{
  int i;
  for(i=1;i<=4;i++)
  {
    strcpy(dl[i],"-");
    strcpy(dm[i],"-");
    strcpy(dr[i],"-");
  }
}

//=================================================
void setup()
//================================================= 
{
  int i;
  float tempC;  
  String str;
  char c,stemp[40],msg[100];

 
  // disable SD SPI
  pinMode(4, OUTPUT);
  digitalWrite(4, HIGH);

  strcpy(g_confServer,"config.nabton.com");
  //strcpy(g_confServer,"192.168.1.97");
  
  Serial.begin(9600);
  //NB_serialFlush();

  pinMode(LED_INTERNET,      OUTPUT);
  pinMode(LED_REC_DATA,      OUTPUT);
  pinMode(LED_SEND_DATA,     OUTPUT);
  pinMode(LED_DEVICE_STATUS, OUTPUT);
  pinMode(LED_CONFIGURED,    OUTPUT);

  blinkLed(LED_DEVICE_STATUS,1,100);
  blinkLed(LED_CONFIGURED,   1,100);
  blinkLed(LED_INTERNET,     1,100);
  blinkLed(LED_SEND_DATA,    1,100);
  blinkLed(LED_REC_DATA,     1,100);
  
  setState(S_STARTED);
  // OLED
//=================================================

  if ( u8g.getMode() == U8G_MODE_R3G3B2 ) {
    u8g.setColorIndex(255);     // white
  }
  else if ( u8g.getMode() == U8G_MODE_GRAY2BIT ) {
    u8g.setColorIndex(3);         // max intensity
  }
  else if ( u8g.getMode() == U8G_MODE_BW ) {
    u8g.setColorIndex(1);         // pixel on
  }
  else if ( u8g.getMode() == U8G_MODE_HICOLOR ) {
    u8g.setHiColorByRGB(255,255,255);
  }
  clearOled();
// One Wire
//=================================================

  sensors.begin();
  nsensors = sensors.getDeviceCount();
  if(nsensors > 0)
  {
    for(i=0;i<nsensors;i++)
    {
      sensors.getAddress(device[i], i);
      sensors.setResolution(device[i], TEMPERATURE_PRECISION);
    }
  }

  sensors.requestTemperatures();
  for(i=1;i<=nsensors;i++)
  {
      tempC = sensors.getTempC(device[i-1]);    
      str = String(tempC);
      str.toCharArray(dl[i+1],8); 
  }

  sprintf(dr[1],"%d",g_device_delay);
  for(i=1;i<=NSID;i++)
  {
    sprintf(dr[i+1],"%d",g_sids[i]);
  }
  NB_oledDraw();
  
  // start the Ethernet connection:
  if (Ethernet.begin(mac) == 0) {
    Serial.println("Failed to configure Ethernet using DHCP");
    // no point in carrying on, so do nothing forevermore:
    // try to congifure using IP address instead of DHCP:
    Ethernet.begin(mac, ip);
    setState(S_NO_NETWORK);
  }
    
  Serial.print("Local address: ");
  ipAddress = Ethernet.localIP();
  sprintf(g_sIp,"%d.%d.%d.%d", ipAddress[0],ipAddress[1],ipAddress[2],ipAddress[3]);
  Serial.println(ipAddress);
  sprintf(dm[1],"?");
  NB_oledDraw();
  digitalWrite(LED_DEVICE_STATUS,HIGH);
  setState(S_NETWORK);
  delay(1000);


while (g_device_state != S_CONFIGURED)
{
// ====== Get global configuration data ========
     digitalWrite(LED_CONFIGURED,LOW);
     Serial.print("URL for config: ");Serial.println(g_confServer);
     int res = client.connect(g_confServer, 80);
     Serial.print("URL connect: ");Serial.println(res);
    if(res == 1)
     {
       setState(S_INTERNET);
       digitalWrite(LED_INTERNET,HIGH);
       //sprintf(msg,"GET /index.php?nsid=%d&sid1=%d",NSID,SID1);
       //if(g_debug==1){Serial.print("msg=");Serial.println(msg);}
       client.println("GET http://config.nabton.com/sercon.html");
       //client.println("GET /nabton/nabtonServer/sercon.html");
       client.println("Host: config.nabton.com");
       client.println("Connection: close");
       client.println();
       //digitalWrite(LED_INTERNET,LOW);      
     }
 
      delay(2000);  
      
      int nbytes = client.available();
      //Serial.print(nbytes); 
      int count = 0;
      
      for(i=0;i<nbytes;i++) 
      {
         c = client.read();
         //Serial.print(c);
         g_rbuf[count] = c;
         count++;
      }
      g_rbuf[count] = '\0';
      Serial.print("-"); 
      Serial.print(g_rbuf);
      Serial.println("*"); 
      if(strstr(g_rbuf,"SERCON") != NULL)
      {
        sscanf(g_rbuf,"%s %s %d",stemp,g_server,&g_device_delay);
        blinkLed(LED_CONFIGURED,nbytes,100);
        setState(S_CONFIGURED);
        digitalWrite(LED_CONFIGURED,HIGH);
      }
      client.stop();
  
      strcpy(g_rbuf," ");
// ====== End global configuration data ========
} // end while

     
  sprintf(dm[1],"CO");
  NB_oledDraw();
  digitalWrite(LED_DEVICE_STATUS,LOW);
  delay(1000);
}
//=================================================
void loop()
//=================================================
{
    int i,j,nbytes,count;
    float tempC;
    char c='1';
    char *p;
    char *str;

    digitalWrite(LED_DEVICE_STATUS,HIGH);
    sensors.requestTemperatures();
   
    for(j=0;j<nsensors;j++)
    {
      //blinkLed(LED_REC_DATA,1,100);
      tempC = sensors.getTempCByIndex(j);
      dtostrf(tempC,5, NFLOAT, dl[2+j]);
      if(tempC != -127)j= NB_sendToGwy(NABTON_DATA,g_sids[j+1],tempC,j);   
      delay(2000); // wait for HTTP response  
      nbytes = client.available();
      //Serial.print(nbytes); 
      count = 0;
      
      for(i=0;i<nbytes;i++) 
      {
         c = client.read();
         //Serial.print(c);
         g_rbuf[count] = c;
         count++;
      }
      g_rbuf[count] = '\0';
      Serial.print("-"); 
      Serial.print(g_rbuf);
      Serial.println("*"); 
      if(strstr(g_rbuf,"DATA") != NULL) blinkLed(LED_REC_DATA,nbytes,20);
      if(strstr(g_rbuf,"CONFIG") != NULL)
      {
        blinkLed(LED_CONFIGURED,5,500);
        software_Reset();
      }
      strcpy(g_rbuf," ");
      //c = '1';
      NB_oledDraw();
      delay(g_device_delay*1000);  
    }
    digitalWrite(LED_DEVICE_STATUS,LOW);
}

