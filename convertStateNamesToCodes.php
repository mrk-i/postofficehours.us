<?php

include_once "lib/ez_sql_core.php";
// Include ezSQL database specific component
include_once "lib/ez_sql_mysql.php";
$username="root";
$password="";
$database="usps";
//$username="gnews_post";
//$password="sMp0n64a#";
//$database="gnews_usps";
$db = new ezSQL_mysql($username,$password,$database,'localhost');


for ($i = 18264; $i <= 78458; $i++){
    $store_details = $db->get_row("SELECT state FROM usps_offices_tmp where `primary`='$i'");
    $state_name=$store_details->state;
    $state=getStateName($state_name);  
    $update=$db->query("UPDATE usps_offices_tmp SET state='$state' WHERE `primary`='$i'");    
    echo $i."state:".$state_name."change to:".$state."<br>";  
}

function getStateName($state){  
         if ($state=="AK"){ return "Alaska"; }  
         if ($state=="AL"){ return "Alabama"; }  
         if ($state=="AR"){ return "Arkansas"; }  
         if ($state=="AZ"){ return "Arizona"; }  
         if ($state=="CA"){ return "California"; }  
         if ($state=="CO"){ return "Colorado"; }  
         if ($state=="CT"){ return "Connecticut"; }  
         if ($state=="DC"){ return "District of Columbia"; }  
         if ($state=="DE"){ return "Delaware"; }  
         if ($state=="FL"){ return "Florida"; }  
         if ($state=="GA"){ return "Georgia"; }  
         if ($state=="HI"){ return "Hawaii"; }  
         if ($state=="IA"){ return "Iowa"; }  
         if ($state=="ID"){ return "Idaho"; }  
         if ($state=="IL"){ return "Illinois"; }  
         if ($state=="IN"){ return "Indiana"; }  
         if ($state=="KS"){ return "Kansas"; }  
         if ($state=="KY"){ return "Kentucky"; }  
         if ($state=="LA"){ return "Louisiana"; }  
           if ($state=="MA"){ return "Massachusetts"; }  
           if ($state=="MD"){ return "Maryland"; }  
           if ($state=="ME"){ return "Maine"; }  
           if ($state=="MI"){ return "Michigan"; }  
           if ($state=="MN"){ return "Minnesota"; }  
           if ($state=="MO"){ return "Missouri"; }  
           if ($state=="MS"){ return "Mississippi"; }  
           if ($state=="MT"){ return "Montana"; }  
           if ($state=="NC"){ return "North Carolina"; }  
           if ($state=="ND"){ return "North Dakota"; }  
           if ($state=="NE"){ return "Nebraska"; }  
           if ($state=="NH"){ return "New Hampshire"; }  
           if ($state=="NJ"){ return "New Jersey"; }  
           if ($state=="NM"){ return "New Mexico"; }  
           if ($state=="NV"){ return "Nevada"; }  
           if ($state=="NY"){ return "New York"; }  
           if ($state=="OH"){ return "Ohio"; }  
           if ($state=="OK"){ return "Oklahoma"; }  
           if ($state=="OR"){ return "Oregon"; }  
           if ($state=="PA"){ return "Pennsylvania"; }  
           if ($state=="RI"){ return "Rhode Island"; }  
           if ($state=="SC"){ return "South Carolina"; }  
           if ($state=="SD"){ return "South Dakota"; }  
           if ($state=="TN"){ return "Tennessee"; }  
           if ($state=="TX"){ return "Texas"; }  
           if ($state=="UT"){ return "Utah"; }  
           if ($state=="VA"){ return "Virginia"; }  
           if ($state=="VT"){ return "Vermont"; }  
           if ($state=="WA"){ return "Washington"; }  
           if ($state=="WI"){ return "Wisconsin"; }  
           if ($state=="WV"){ return "West Virginia"; }  
         if ($state=="WY"){ return "Wyoming"; }  
      
      }  