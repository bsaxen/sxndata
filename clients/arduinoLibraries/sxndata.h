/*
  
*/
#ifndef sxndata_h
#define sxndata_h

#include "Arduino.h"

class sxndata
{
  public:
    Morse(int pin);
    int sendToGwy(int mid,int sid,float value,int other);
    void recSerial();
  private:
    int _pin;
};

#endif