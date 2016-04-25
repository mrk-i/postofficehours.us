<?php
//take weird fonts from DB and cut off
//include 'config.php';
////////////////////////////////////////
// Include ezSQL core DB wrapper
include_once "lib/ez_sql_core.php";
include_once "lib/functions.php";
// Include ezSQL database specific component
include_once "lib/ez_sql_mysql.php";
//$db = new ezSQL_mysql($username,$password,$database,'localhost');
$username="gnews_post";
$password="sMp0n64a#";
$database="gnews_usps";
//$username="root";
//$password="";
//$database="usps";

$db = new ezSQL_mysql($username,$password,$database,'localhost');
$get_models = $db->get_results("SELECT `primary`,name FROM `usps_offices` WHERE `name` LIKE BINARY '%#%'");
$i=0;
//var_dump($get_models);
foreach ($get_models as $model){
    $url[$i]=$model->name;
    $id[$i]=$model->primary;
    echo $id[$i]." ".$url[$i].'<br>';
    $db->query("UPDATE usps_offices SET name = REPLACE(name, BINARY '#', 'Nbr' ) WHERE `primary`='$id[$i]'");
    $i++;
}
echo "Elements cleaned:".$i;
//var_dump($clean_hd_model);

?>
