<?php
//======================================================
// sxn_sidFunctions.php
//======================================================

//======================================================
class controlSaxenHeater {
// 
// Control of Heating system in Kil, Runnevål
//======================================================
    function doIt() {
    
    $waterIn_sid     = 3;// 3
    $waterOut_sid    = 1;// 1
    $smokeTemp_sid   = 4;// 4
    $outdoorTemp_sid = 6;// 6
    $indoorTemp_sid  = 2;// 2
        
        
    // Read latest value of all SIDs
    //-------------------------------
    
    //$waterIn     = lib_getLatestValue($waterIn_sid);
    $waterOut    = lib_getLatestValue($waterOut_sid);
    if($waterOut == SXN_NO_VALUE) return;
    //$smokeTemp   = lib_getLatestValue($smokeTemp_sid);
    $outdoorTemp = lib_getLatestValue($outdoorTemp_sid);
    if($outdoorTemp == SXN_NO_VALUE) return;
    //$indoorTemp  = lib_getLatestValue($indoorTemp_sid);
    
         
    // Make a decision
    //-------------------------------
    // Algorithm Water_out = -Temp_outdoor + 37     This is same as Åstenäs
        
    $delta = 40 - $outdoorTemp -  $waterOut;
    
    //echo("Algo: $delta<br>"); 
    if($smokeTemp_sid > 30.0) // Only control if Heater is ON
    {
        if($delta > 2 && $indoorTemp_sid < 20.0) // Higher shunt 
        {
            $order = "NBC_STEPPER_CTRL 1 2 10";
            insertOrder($waterOut_sid,$order); 
        }  
        if($delta < -2 && $indoorTemp_sid > 21.0) 
        {
            $order = "NBC_STEPPER_CTRL 2 2 10";
            insertOrder($waterOut_sid,$order); 
        } // Lower shunt
    }
         
    }
}

//======================================================
class template {
// 
// Name your class!
//======================================================
    function doIt() {
        
        // Write your code here
        
        }
}