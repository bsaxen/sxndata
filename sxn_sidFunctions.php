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
    
    //$waterIn_sid     = 3;// 3
    $waterOut_sid    = 901;// 1
    //$smokeTemp_sid   = 4;// 4
    $outdoorTemp_sid = 902;// 6
    //$indoorTemp_sid  = 2;// 2
        
        
    // Read latest value of all SIDs
    //-------------------------------
    
    //$waterIn     = lib_getLatestValue($waterIn_sid);
    $waterOut    = lib_getLatestValue($waterOut_sid);
    if($waterOut == SXN_NO_VALUE) return;
    //$smokeTemp   = lib_getLatestValue($smokeTemp_sid);
    $outdoorTemp = lib_getLatestValue($outdoorTemp_sid);
    if($outdoorTemp == SXN_NO_VALUE) return;
    //$indoorTemp  = lib_getLatestValue($indoorTemp_sid);
    //echo("$waterIn<br>$waterOut<br>$smokeTemp<br>$outdoorTemp<br>$indoorTemp<br>");
    
        
        
    // Make a decision
    //-------------------------------
    // Algorithm Water_out = -Temp_outdoor + 37     This is same as Åstenäs
        
    $delta = 40 - $outdoorTemp -  $waterOut;
    
    echo("Algo: $delta<br>"); 
    if($delta > 2)  $order = "NBC_STEPPER_CTRL 1 2 5";  // Higher shunt
    if($delta < -2) $order = "NBC_STEPPER_CTRL 2 2 5"; // Lower shunt
        
    // Create order
    //-------------------------------
    
        // NBC_STEPPER_CTRL 1 2 5
      insertOrder($waterOut_sid,$order);    
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