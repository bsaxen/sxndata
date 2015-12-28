/*

*/

#include "Arduino.h"
#include "sxndata.h"

sxndata::sxndata(int pin)
{
  pinMode(pin, OUTPUT);
  _pin = pin;
}

void sxndata::sendToGwy(int mid,int sid,float value,int other)
{
  digitalWrite(_pin, HIGH);
  delay(250);
  digitalWrite(_pin, LOW);
  delay(250);  
}

void sxndata::recSerial()
{
  digitalWrite(_pin, HIGH);
  delay(1000);
  digitalWrite(_pin, LOW);
  delay(250);
}