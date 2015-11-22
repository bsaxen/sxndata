//==================================================
// sxn_temperature.ino
//==================================================
// History
//==================================================
// Software Id: 11001 2015-11-21	First version
//==================================================
// SWID application,version
//==================================================
#define SWID 11001
#define DEVID 9999
#define NFLOAT 2  // No of decimals i float value
#define SIDN 1    // No of SIDs
#define SID1 901  
#define SID2 902  
#define SID3 903  
#define SID4 904  
#define SID5 905 
#define SID6 906
#define SID7 907
#define SID8 908
//==================================================
#define MAX_SID 10
#define MAX_ORDERS 100
int g_sids[10] = {SIDN,SID1,SID2,SID3,SID4,SID5,SID6,SID7,SID8};
int g_device_delay = 10;
// Arduino-RPi protocol
#define NABTON_DATA     1 
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
  int ixSid = 0,i;
  char msg1[100],msg2[50],checksum[20];
     strcpy(msg1," ");
     strcpy(msg2," ");
     digitalWrite(5,HIGH);
     sprintf(msg1,"?mid=%d&nsid=%d&sid1=%d",mid,nsensors,sid);
     if(mid == NABTON_DATA)
     {
       int part1 = int(data);
       float ftemp = (data - part1);
       for(i=1;i<=NFLOAT;i++)ftemp=ftemp*10;
       int part2 = ftemp; 
       if(part2 < 0)part2 = part2*(-1.0);
       if(part2 < 10)
         sprintf(msg2,"&devid=%d&swid=%d&data=%d.0%d",DEVID,SWID,part1,part2);
       else 
         sprintf(msg2,"&devid=%d&swid=%d&data=%d.%d",DEVID,SWID,part1,part2);
       if(part2 < 0)part2 = part2*(-1.0);
       strcat(msg1,msg2);
     }
     sprintf(checksum,": %d",strlen(msg1));
     strcat(msg1,checksum);

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
     sscanf(nbbuff,"%d %d",&mid,&sid);
     sprintf(dr[3],"%d",nx);
     if(sid == SID1) // Check if control sid correct
     {
       if(strstr(nbbuff,"NB_DEVICE_DELAY") != NULL)
       {
          strcpy(dr[3],"DLY");
          sscanf(nbbuff,"%d %d %s %d",&mid,&sid,command,&g_device_delay);
          sprintf(dr[1],"%d",g_device_delay);
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
  Serial.begin(9600);
  NB_serialFlush();
  pinMode(3, OUTPUT);
  pinMode(5, OUTPUT);
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
  g_sids[1] = SID1;
  g_sids[2] = SID2;
  g_sids[3] = SID3;
  g_sids[4] = SID4;
  g_sids[5] = SID5;
  g_sids[6] = SID6;
  g_sids[7] = SID7;
  g_sids[8] = SID8;
  
  sprintf(dl[1],"%d",SWID);
  sprintf(dm[1],"%d",DEVID);
  sprintf(dr[1],"%d",g_device_delay);
  for(i=1;i<=SIDN;i++)
  {
    sprintf(dr[i+1],"%d",g_sids[i]);
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
      tempC = sensors.getTempC(device[i-1]);    
      str = String(tempC);
      str.toCharArray(dl[i+1],8); 
      if(tempC != -127)NB_sendToGwy(NABTON_DATA,g_sids[i],tempC,0);
      delay(g_device_delay*1000);  
      recSerial(); 
      NB_oledDraw();
    }
}

