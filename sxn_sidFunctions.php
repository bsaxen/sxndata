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
    //if($waterOut == SXN_NO_VALUE) return;
    $smokeTemp   = lib_getLatestValue($smokeTemp_sid);
    //if($smokeTemp == SXN_NO_VALUE) return;
    $outdoorTemp = lib_getLatestValue($outdoorTemp_sid);
    //if($outdoorTemp == SXN_NO_VALUE) return;
    //$indoorTemp  = lib_getLatestValue($indoorTemp_sid);
           
// Get current time and date
    $dtz = new DateTimeZone("Europe/Stockholm"); //Your timezone
    $now = new DateTime(date("Y-m-d H:i:s"), $dtz);
    $snow =  $now->format("Y-m-d H:i:s");

// Recall time and date for latest order
    $prev = lib_recall($waterOut_sid); 
    if($prev == 0)$prev = $snow;
        
    $diff = strtotime($snow) - strtotime($prev);
    //echo("diff=$diff $snow $prev<br>");
        
    $delta = 35 - $outdoorTemp -  $waterOut;
    
        
    if($diff > 1200) // 20 minutes for order to effect the temperature  
    {
    //echo("Algo: $delta<br>"); 
      if($smokeTemp > 25.0) // Only control if Heater is ON
      {
        //if($delta > 1.0 && $indoorTemp < 19.8) // Increase Heat
        if($delta > 1.0)
        {
            $order = "NBC_STEPPER_CTRL 1 2 20";
            insertOrder($waterOut_sid,$order);
            lib_remember($waterOut_sid,$snow); 
        }  
        //if($delta < -1.0 && $indoorTemp > 20.2) // Decrease Heat
        if($delta < -1.0)
        {
            $order = "NBC_STEPPER_CTRL 2 2 20";
            insertOrder($waterOut_sid,$order); 
            lib_remember($waterOut_sid,$snow); 
        } 
      }
    }
         
    } // doIt
} // Class

//======================================================
class template {
// 
// Name your class!
//======================================================
    function doIt() {
        
        // Write your code here
        
        }
}