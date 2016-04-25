<?php

include 'config.php';
$set_caching = 0;
$domain = "postofficehours.us";
require_once('lib/Twig/Autoloader.php');
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
// Include ezSQL core DB wrapper
include_once "lib/ez_sql_core.php";
// Include ezSQL database specific component
include_once "lib/ez_sql_mysql.php";

//check if caching is set
if (set_caching == 0) {
    $cache_directory = array('cache' => false);
} else {
    $cache_directory = array('cache' => 'cache');
}
//set caching directory
$twig = new Twig_Environment($loader, $cache_directory);

$db = new ezSQL_mysql($username,$password,$database, 'localhost');
//$db = new ezSQL_mysql('root','','usps','localhost');
$data = $db->get_results("SELECT * FROM `latest_changes` ORDER BY id DESC");
$i = 0;
foreach ($data as $value){
    $latest_update_id[$i]=$value->id;
    $latest_update_post_office_id[$i]=$value->post_office_id;
    $latest_update_city[$i]=str_replace(" ", "-",$value->city);
    $latest_update_states[$i]=$value->states;
    $latest_update_date[$i]=$value->date_added;
    $latest_update_date_timestamp[$i]=strtotime($value->date_added);
    $days_between[$i] = dateDiff(time(), $latest_update_date_timestamp[$i]);
    $i++;
}
//$now = time();
//$start = $now;
//$end = strtotime('2015-01-20');
//$days_between = ceil(abs($end - $start) / 86400);
//echo dateDiff($now, $end);
//var_dump($latest_update_date);
$number_last_updated=count($data);
$twig->display('header.twig', array('set_last_updates_description'=>1, 'domain' => $domain,'title' => $global_meta_title));
        $twig->display('latest_changes.twig',array('domain' => $domain,'start_date'=>$start,'end_date'=>$end,'days_between'=>$days_between,
            'current_time'=>$now,'latest_update_id'=>$latest_update_id,'latest_update_post_office_id'=>$latest_update_post_office_id,
            'latest_update_city'=>$latest_update_city,'latest_update_states'=>$latest_update_states,'latest_update_date'=>$latest_update_date,
            'number'=>$number_last_updated,'timestamp'=>$latest_update_date_timestamp,'domain' => $domain));
        $twig->display('site-bar.twig',array('domain' => $domain));        
        $twig->display('footer.twig',array('domain' => $domain));
// Set timezone
  date_default_timezone_set("UTC");
 
  // Time format is UNIX timestamp or
  // PHP strtotime compatible strings
  function dateDiff($time1, $time2, $precision = 6) {
    // If not numeric then convert texts to unix timestamps
    if (!is_int($time1)) {
      $time1 = strtotime($time1);
    }
    if (!is_int($time2)) {
      $time2 = strtotime($time2);
    }
 
    // If time1 is bigger than time2
    // Then swap time1 and time2
    if ($time1 > $time2) {
      $ttime = $time1;
      $time1 = $time2;
      $time2 = $ttime;
    }
 
    // Set up intervals and diffs arrays
    $intervals = array('year','month','day','hour','minute','second');
    $diffs = array();
 
    // Loop thru all intervals
    foreach ($intervals as $interval) {
      // Create temp time from time1 and interval
      $ttime = strtotime('+1 ' . $interval, $time1);
      // Set initial values
      $add = 1;
      $looped = 0;
      // Loop until temp time is smaller than time2
      while ($time2 >= $ttime) {
        // Create new temp time from time1 and interval
        $add++;
        $ttime = strtotime("+" . $add . " " . $interval, $time1);
        $looped++;
      }
 
      $time1 = strtotime("+" . $looped . " " . $interval, $time1);
      $diffs[$interval] = $looped;
    }
    
        $count = 0;
        $times = array();
        // Loop thru all diffs
        foreach ($diffs as $interval => $value) {
          // Break if we have needed precission
          if ($count >= $precision) {
     break;
          }
          // Add value and interval 
          // if value is bigger than 0
          if ($value > 0) {
     // Add s if value is not 1
     if ($value != 1) {
       $interval .= "s";
     }
     // Add value and interval to times array
     $times[] = $value . " " . $interval;
     $count++;
          }
        }
 
    // Return string with times
    return implode(", ", $times);
  }
