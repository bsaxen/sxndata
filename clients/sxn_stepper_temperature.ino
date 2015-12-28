//==================================================
// sxn_stepper_temperature.ino
//==================================================
// History
//==================================================
// Software Id: 11201 2015-11-21	First version
// 2015-12-09: support sidApp controlSaxenHeater
// 2015-12-18: increased stepper resolution, corrected NB_sendToGateway
//==================================================
#define SWID 2015
#define DEVID 1218 
#define SIDN  4   // No of SIDs
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
// Arduino Control Protocol
#define ON_STEPPER_DIR   4
#define ON_STEPPER_STEPS 5
#define ON_STEPPER_SPEED 6

//=================================================
//
// D0 RX used for serial communication to server (Raspberry Pi)
// D1 TX used for serial communication to server (Raspberry Pi)
// D2
// D3 Status Pin - Serial data received
// D4 IR Data
// D5 Status Pin - Serial data sent
// D6 DIR Stepper
// D7 STEP Stepper
// D8 SLEEP Stepper
// D9 One Wire Data
// D10  RX Bluetooth device
// D11  TX Bluetooth device
// D12 
// D13
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
//=================================================
//==================================================
// Nabton Client Application Configuration
//==================================================
#include <OneWire.h>
#include <DallasTemperature.h>
#include <SoftwareSerial.h>
//#include <IRremote.h>
#include <U8glib.h>

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
// Stepper Motor
//==================================================
int DIR   = 6; 
int STEP  = 7;
int SLEEP = 8;
//==================================================
// OLED I2C
//==================================================

//U8GLIB_SSD1306_128X64 u8g(13, 11, 10, 9);// SW SPI protocol(4 pins): SCK = 13, MOSI = 11, CS = 10, A0 = 9	
//U8GLIB_SSD1306_128X64 u8g(U8G_I2C_OPT_NONE); // Small display I2C protocol (2 pins)
U8GLIB_SH1106_128X64 u8g(U8G_I2C_OPT_NONE); // Large display
char dl[5][8],dm[5][8],dr[5][8];
//=================================================
void NB_oledDraw() 
//=================================================
{
 u8g.firstPage();  
  do {
        draw();
  } while( u8g.nextPage() ); 
}
//================================================
void stepCW(int steps,int dd)
//================================================
{
  int i;
  digitalWrite(DIR, LOW);   
  digitalWrite(SLEEP, HIGH); // Set the Sleep mode to AWAKE.
  for(i=0;i<=steps;i++)
    {
      delayMicroseconds(200);
      digitalWrite(STEP, HIGH); 
      //delay(dd);     
      digitalWrite(STEP, LOW);
      delay(dd);  
    }  
  digitalWrite(DIR, LOW);   
  digitalWrite(SLEEP, LOW); // Set the Sleep mode to SLEEP.   
}

//================================================
void stepCCW(int steps,int dd)
//================================================
{
  int i;
  digitalWrite(DIR, HIGH);                 
  digitalWrite(SLEEP, HIGH); // Set the Sleep mode to AWAKE.
  for(i=0;i<=steps;i++)
    {
      delayMicroseconds(200);
      digitalWrite(STEP, HIGH);   
      //delay(dd);       
      digitalWrite(STEP, LOW); 
      delay(dd);     
    }  
  digitalWrite(DIR, LOW);   
  digitalWrite(SLEEP, LOW); // Set the Sleep mode to SLEEP.    
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
void NB_serialFlush()
//=================================================
{
  while(Serial.available() > 0) {
    char t = Serial.read();
  }
}   

//=================================================
void NB_sendToGwy(int mid, int sid, float data, int other)
//=================================================
{
  int ixSid = 0,i,negative=0;
  char msg1[100],msg2[50],checksum[20];
     strcpy(msg1," ");
     strcpy(msg2," ");
     digitalWrite(5,HIGH);
     // Mandatory part of message
     sprintf(msg1,"?mid=%d&nsid=%d&sid1=%d",mid,1,sid);
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
           sprintf(msg2,"&devid=%d&swid=%d&dat1=%d.0%d",DEVID,SWID,part1,part2);
         else 
           sprintf(msg2,"&devid=%d&swid=%d&dat1=%d.%d",DEVID,SWID,part1,part2);
       }
       if(negative == 1)
       {
         if(part2 < 10)
           sprintf(msg2,"&devid=%d&swid=%d&dat1=-%d.0%d",DEVID,SWID,part1,part2);
         else 
           sprintf(msg2,"&devid=%d&swid=%d&dat1=-%d.%d",DEVID,SWID,part1,part2);
       }
       strcat(msg1,msg2);
     }
    
     // Create checksum
     sprintf(checksum,": %d",strlen(msg1));
     strcat(msg1,checksum);
     
     // Send meassage
     Serial.println(msg1);
     digitalWrite(5,LOW);
}
//=================================================
void recSerial()
//=================================================
{
  int i,k=0,ttemp,nx=0,nm=0;
  nx = Serial.available();
  char nbbuff[50],msg[5][100],command[48],stemp[100];
  int mid, sid;
  int dir,steps,vel;
  
  if (nx > 0) 
  {
     digitalWrite(3, HIGH); 
     Serial.readBytes(nbbuff,nx);
     sscanf(nbbuff,"%d",&sid);
     if(sid == SID1) // Check if control sid correct
     {
       if(strstr(nbbuff,"NBC_DEVICE_DELAY") != NULL)
       {
          strcpy(dr[3],"DLY");
          sscanf(nbbuff,"%d %s %d",&sid,command,&g_device_delay);
          sprintf(dr[3],"%d",g_device_delay);
       }
       if(strstr(nbbuff,"NBC_STEPPER_CTRL") != NULL)
       {
          strcpy(dr[3],"SCL");
          sscanf(nbbuff,"%d %s %d %d %d",&sid,command,&dir,&steps,&vel);
          if(dir==1)strcpy(dr[4],"STP>");
          if(dir==2)strcpy(dr[4],"STP<"); 
          if(dir==1)strcpy(dr[1],"hi");
          if(dir==2)strcpy(dr[1],"low<");   
          NB_oledDraw();        
          if(steps > 0 || steps < 1000)
          {
             if(dir == 1) stepCW(steps,vel);
             if(dir == 2) stepCCW(steps,vel);
          }
       }
       strcpy(dr[4],"-");
       NB_oledDraw();
     }
     digitalWrite(3, LOW); 
  }
}

//=================================================
void clearOled()
//================================================= 
{
  int i;
  for(i=1;i<=4;i++)
  {
    strcpy(dl[i]," ");
    strcpy(dm[i]," ");
    strcpy(dr[i]," ");
  }
}

//=================================================
void setup()
//================================================= 
{
  int i;
  float tempC;  
  String str;
  Serial.begin(9600);
  NB_serialFlush();
  pinMode(3, OUTPUT);
  pinMode(5, OUTPUT);
  pinMode(DIR, OUTPUT);
  pinMode(STEP, OUTPUT);
  pinMode(SLEEP, OUTPUT);
  digitalWrite(DIR, LOW);
  digitalWrite(STEP, LOW);
  digitalWrite(SLEEP, LOW);
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
      str.toCharArray(dl[i],8); 
  }
  g_sids[1] = SID1;
  g_sids[2] = SID2;
  g_sids[3] = SID3;
  g_sids[4] = SID4;
  g_sids[5] = SID5;
  g_sids[6] = SID6;
  g_sids[7] = SID7;
  g_sids[8] = SID8;

  //sprintf(dr[1],"%d",SWID);
  sprintf(dr[2],"%d",DEVID);
  sprintf(dr[3],"%d",g_device_delay);
  for(i=1;i<=SIDN;i++)
  {
    sprintf(dm[i],"%d",g_sids[i]);
  }

  NB_oledDraw();
}

//=================================================
void loop()
//=================================================
{
  int i;
  float tempC;
  String str;
  
    sensors.requestTemperatures();
    for(i=1;i<=nsensors;i++)
    {
      strcpy(dl[i]," ");  
      tempC = sensors.getTempC(device[i-1]);     
      str = String(tempC);
      str.toCharArray(dl[i],8); 
      if(tempC != -127)NB_sendToGwy(NABTON_DATA,g_sids[i],tempC,0);
      strcpy(dm[i],"*");
      NB_oledDraw();
      delay(2000);  
      recSerial();
      sprintf(dm[i],"%d",g_sids[i]);
      NB_oledDraw();
    }
    delay(g_device_delay*1000); // delay in steps of seconds   
}
// End of file
