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
    
    $waterIn     = lib_getLatestValue($waterIn_sid);
    $waterOut    = lib_getLatestValue($waterOut_sid);
    $smokeTemp   = lib_getLatestValue($smokeTemp_sid);
    $outdoorTemp = lib_getLatestValue($outdoorTemp_sid);
    $indoorTemp  = lib_getLatestValue($indoorTemp_sid);
   
    // Read latest status
    echo("$waterIn<br>$waterOut<br>$smokeTemp<br>$outdoorTemp<br>$indoorTemp<br>");
    
    // Make a decision
    
    // Algorithm Water_out = -Temp_outdoor + 37     This is same as Åstenäs
        
    $wanted_WaterOutTemp = 37 - $outdoorTemp;
    $delta = $wanted_WaterOutTemp -  $waterOut;
    
    echo("Algo: $delta<br>"); 
    
    // Create order
    
    
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