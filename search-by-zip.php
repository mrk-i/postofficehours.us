<?php
include 'config.php';
require_once('lib/Twig/Autoloader.php');
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates');

//check if caching is set
if ($set_caching == 0) {
    $cache_directory = array('cache' => false);
} else {
    $cache_directory = array('cache' => 'cache');
}
$zipcode = $_POST["zipcode"];
//1. Check if we have ZIP code coordinates already in the database
// If not, use google geocoding API
//3. Perform a distance search in the DB
//set caching directory


if (strlen($zipcode) > 4) {

    $search_query = $_POST["zipcode"];
    $search_distance = $_POST["radius"]; // miles
#####

    require_once( "lib/geocoder.class.php" );

    mysql_connect($db_host, $username, $password) or die("Could not connect: " . mysql_error());
    mysql_select_db($database);

#####

    $query = sprintf("SELECT lat, lng FROM usps_offices WHERE zip = '%s' LIMIT 1", mysql_real_escape_string($search_query));
    $result = mysql_query($query);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }

    $row = mysql_fetch_array($result, MYSQL_NUM);
    list ( $lat, $lng ) = $row;
    mysql_free_result($result);

// if the coordinates for the zip code were not found - performing geocoding
    if (!( $lat && $lng )) {
        try {
            list ( $lat, $lng ) = geocoder::lookup($search_query);
            if (is_null($lat) || is_null($lng)) {
                echo "Nothing was found! It might be a problem with geolocation function\n";
            }
        } catch (Exception $e) {
            // some error ocurred: limit exceeded, etc.
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

// finding shops in the vicinity
    if ($lat && $lng) {
        $query = sprintf("SELECT id,`name`,city,address,state,zip,lat,lng,zip4,( 3959 * acos( cos( radians(%s) ) * 
            cos( radians( lat ) ) * cos( radians( lng ) - radians(%s) ) + 
            sin( radians(%s) ) * sin( radians( lat ) ) ) ) AS distance
		FROM usps_offices HAVING distance < %s ORDER BY distance", $lat, $lng, $lat, $search_distance
        );
        //echo $query;//debug
        $result = mysql_query($query);
        if (!$result) {
            die('Invalid query: ' . mysql_error());
        }
        $i = 0;
        //define arrays to prevent web server notice errros!!!
        $name=array();$name_for_subfolde=  array(); $city=array(); $zip=array(); $distance=array();$address=array();
        $state=array();$store_lat=array();$store_lng=array();$name_for_subfolder=array();
        $row=array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            //print_r($row);//debug  
            $name[$i] = $row["name"];
            $name_for_subfolder[$i] = str_replace(" ", '-', $name[$i]);
            $city[$i] = $row["city"];
            $zip[$i] = $row["zip"];
            $store_lat[$i] = $row["lat"];
            $store_lng[$i] = $row["lng"];
            $distance[$i] = round($row["distance"], 2);
            $address[$i] = $row["address"];
            $state[$i] = $row["state"];
            $id[$i] = $row["id"];
            $i++;
        }
        $number_of_store_nearby = count($name);
        mysql_free_result($result);
    } else {
        print "Can't geocode search query.";
    }
    $twig = new Twig_Environment($loader, $cache_directory);
    $twig->display('header.twig', array('title' => $global_meta_title, 'check_form_function' => 1, 'set_google_maps_for_serach' =>1,
        'set_search_description' =>1,'domain' => $domain));
    $twig->display('search-zip.twig', array('nearby_store' => $name, 'city' => $city, 'zip' => $zip, 'distance' => $distance
        , 'number_of_stores' => $number_of_store_nearby, 'subfolder' => $name_for_subfolder,'name'=>$name, 
        'print' => 1, 'zipCode' => $zipcode, 'radius' => $_POST["radius"],'latitude' =>$store_lat,'longitude' =>$store_lng, 
        'address' => $address, 'state' => $state,'domain' => $domain,'id'=>$id));
    $twig->display('site-bar.twig', array('domain' => $domain));
    $twig->display('footer.twig',array('domain' => $domain));
} else {
    $twig = new Twig_Environment($loader, $cache_directory);
    $twig->display('header.twig', array('title' => $global_meta_title, 'check_form_function' => 1,'set_search_description' =>1,'domain' => $domain));
    $twig->display('search-zip.twig', array('print' => 0,'domain' => $domain));
    $twig->display('site-bar.twig', array('domain' => $domain));
    $twig->display('footer.twig',array('domain' => $domain));
}
?>
