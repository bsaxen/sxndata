<?php
//======================================================
// sxn_sidFunctions.php
//======================================================

//======================================================
class controlSaxenHeater {
// CSH
// Control of Heating system in Kil, Runnevål
//======================================================
    function doIt() {
    lib_log("CSH","----- Start -----\n");          
    // Get current time and date
    $dtz = new DateTimeZone("Europe/Stockholm"); //Your timezone
    $now = new DateTime(date("Y-m-d H:i:s"), $dtz);
    $snow =  $now->format("Y-m-d H:i:s");    
    
    $waterIn_sid     = 3;// 3
    $waterOut_sid    = 1;// 1
    $smokeTemp_sid   = 4;// 4
    $outdoorTemp_sid = 6;// 6
    $indoorTemp_sid  = 2;// 2
        
    $time1 = 1200; // sec
    $time2 = 43200; //sec
        
    // Memeories
    //-------------------------------
    $labelLatestOrderTime     = "CSH_latestOrderTime";
    $labelLatestTargetTime    = "CSH_latestTargetTime";
    $labelTargetValue         = "CSH_targetValue";

    //======================================================== 
        
    // Recall time and date for latest order
    $prev = lib_recall($labelLatestOrderTime); 
    if($prev == 'void')
    {
        $prev = $snow;
        lib_remember($labelLatestOrderTime,$snow); 
    }
    $diff = strtotime($snow) - strtotime($prev);
    lib_log("CSH","Order latest: $snow [$prev] $diff($time1)\n");  
        
    // Recall time and date for latest targetValue update
    $prev = lib_recall($labelLatestTargetTime); 
    if($prev == 'void')
    {
        $prev = $snow;
        lib_remember($labelLatestTargetTime,$snow); 
    }
    $diff2 = strtotime($snow) - strtotime($prev);
    lib_log("CSH","TargetValue latest update: [$prev] $diff2($time2)\n");      
        
    //========================================================     
    //$waterIn     = lib_getLatestValue($waterIn_sid);
    $waterOut    = lib_getLatestValue($waterOut_sid);
    if($waterOut == SXN_NO_VALUE)
    {
        lib_log("CSH","No value for WaterOut\n");
        lib_log("CSH","-----End-----\n"); 
        //return;
    }
    $smokeTemp   = lib_getLatestValue($smokeTemp_sid);
    if($smokeTemp == SXN_NO_VALUE)
    {
        lib_log("CSH","No value for SmokeTemp\n");
        lib_log("CSH","-----End-----\n"); 
        //return;
    }
    $outdoorTemp = lib_getLatestValue($outdoorTemp_sid);
    if($outdoorTemp == SXN_NO_VALUE) 
    {
        lib_log("CSH","No value for OutdoorTemp\n"); 
        lib_log("CSH","-----End-----\n"); 
        //return;
    }    
    $indoorTemp  = lib_getLatestValue($indoorTemp_sid);
    if($outdoorTemp == SXN_NO_VALUE) 
    {
        lib_log("CSH","No value for IndoorTemp\n"); 
        lib_log("CSH","-----End-----\n"); 
        //return;
    }    
    //======================================================== 
        
    $targetValue = lib_recall($labelTargetValue);
    if($targetValue == 'void')
    {
        $targetValue = 40;
        lib_log("CSH","TargetValue=default($targetValue)\n");
        lib_remember($labelTargetValue,$targetValue); 
    }
        
        
        
    $delta = $targetValue - $outdoorTemp -  $waterOut;
 
    $logmsg = "TargetValue=".$targetValue."\n"; lib_log("CSH",$logmsg);
    $logmsg = "Indoor=".$indoorTemp."\n";       lib_log("CSH",$logmsg);
    $logmsg = "WaterOut=".$waterOut."\n";       lib_log("CSH",$logmsg);
    $logmsg = "OutdoorTemp=".$outdoorTemp."\n"; lib_log("CSH",$logmsg);
    $logmsg = "SmokeTemp=".$smokeTemp."\n";     lib_log("CSH",$logmsg);
    $logmsg = "Delta=".$delta."\n";             lib_log("CSH",$logmsg);
        
    //echo("diff=$diff $snow $prev<br>");
        
    lib_log("CSH","Action:");    
    if($diff > $time1) // 20 minutes for order to effect the temperature  
    {
    //echo("Algo: $delta<br>");
      lib_log("CSH","*");
      if($smokeTemp > 25.0) // Only control if Heater is ON
      {
        lib_log("CSH","!");
        if($indoorTemp < 19.5) // Increase Heat
        {
           if($delta > 1.0)
           {
              lib_log("CSH","stepper +");
              $order = "NBC_STEPPER_CTRL 1 2 20";
              insertOrder($waterOut_sid,$order);
              lib_remember($labelLatestOrderTime,$snow); 
           }
           else if($diff2 > $time2) // 12 hours
           {
              $targetValue++;
              if($targetValue > 50)$targetValue = 50;
              lib_remember($labelTargetValue,$targetValue); 
              lib_remember($labelLatestTargetTime,$snow); 
              $logmsg = "\nIncreased TargetValue=$targetValue\n";
              lib_log("CSH",$logmsg);
           }
        }
        if($indoorTemp > 20.5) // Decrease Heat
        {
           if($delta < -1.0)
           {
              lib_log("CSH","stepper -");
              $order = "NBC_STEPPER_CTRL 2 2 20";
              insertOrder($waterOut_sid,$order); 
              lib_remember($labelLatestOrderTime,$snow); 
           } 
           else if($diff2 > $time2) // 12 hours
           {
              $targetValue--;
              if($targetValue < 10)$targetValue = 10;
              lib_remember($labelTargetValue,$targetValue); 
              lib_remember($labelLatestTargetTime,$snow); 
              $logmsg = "\nDecreased TargetValue=$targetValue\n";
              lib_log("CSH",$logmsg);
           }
        }
      }
    }
    lib_log("CSH","\n");
    lib_log("CSH","-----End-----\n");     
    } // doIt
} // Class

//======================================================
class autoMowerMap 
{
//======================================================
    function doIt() 
    {
        
        $sid_lat = 801; 
        $sid_lon = 802; 
        $sid_battery_status = 803; 
        $latitude    = lib_getLatestValue($sid_lat);
        if($latitude == SXN_NO_VALUE)
        {
            lib_log("CSH","No value for latitud t\n");
            lib_log("CSH","-----End-----\n"); 
        }
        $longitude    = lib_getLatestValue($sid_lon);
        if($longitude == SXN_NO_VALUE)
        {
            lib_log("CSH","No value for longitude t\n");
            lib_log("CSH","-----End-----\n"); 
        }
        $battery_status    = lib_getLatestValue($sid_battery_status);
        if($battery_status == SXN_NO_VALUE)
        {
            lib_log("CSH","No value for battery status t\n");
            lib_log("CSH","-----End-----\n"); 
        }
        echo("Mapping ");
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
