<?php
include (dirname(__FILE__) . '/../config.php');
//Access to Read from DB
// Include ezSQL core DB wrapper
include_once (dirname(__FILE__) . '/../lib/ez_sql_core.php');
// Include ezSQL database specific component
include_once (dirname(__FILE__) . '/../lib/ez_sql_mysql.php');

$random_id=mt_rand(5, 78458);
echo $random_id."<br>";
$current_time = time();

//get data from usps_offices
$db = new ezSQL_mysql($username,$password,$database, 'localhost');
//$db = new ezSQL_mysql('root','','usps','localhost');
//Read from database
$data = $db->get_results("SELECT `id`,`city`, `state` FROM `usps_offices` WHERE `primary`='$random_id'");
$state=$data[0]->state;
$id=$data[0]->id;
$city=$data[0]->city;
//write data to latest_changes
$db->query("INSERT INTO latest_changes (post_office_id, date_added, city, states) VALUES ('$id',FROM_UNIXTIME($current_time),'$city','$state')");
