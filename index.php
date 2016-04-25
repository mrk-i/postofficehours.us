<?php

include 'config.php';
include_once 'lib/functions.php';
require_once ('lib/Mobile_Detect.php');
// Include ezSQL core DB wrapper
include_once "lib/ez_sql_core.php";
// Include ezSQL database specific component
include_once "lib/ez_sql_mysql.php";
require_once('lib/Twig/Autoloader.php');
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');

//check if caching is set
if ($set_caching == 0) {
    $cache_directory = array('cache' => false);
} else {
    $cache_directory = array('cache' => 'cache');
}
//set caching directory
$twig = new Twig_Environment($loader, $cache_directory);
$url = current_url();
//$reverse = strrev( $url );
////check if we have / at the end of url if not add
//if ($reverse[0]=="/"){
////do nothing    
//}
//else{
//    $end_of_url ="/";
//    $url=$url.$end_of_url;
//    header("HTTP/1.1 301 Moved Permanently");
//    header("Location: $url");
//}

$current_dir = $_SERVER['REQUEST_URI'];    
//    echo " "; //THIS is a bug without this EMPTY echo bellow if will not work ????????!!!!!!????????????!!!!!!
//    //we need to disable displaying warings becouse this bug
//    ini_set('display_errors','off');
//    if(rtrim($current_dir)!=="/"){
//    //do nothing    
//    }
//    else{
//        $end_of_url ="/";
//        $url=$current_dir.$end_of_url;
//        header("HTTP/1.1 301 Moved Permanently");
//        header("Location: $url");
//    }

//render index page
if (($_SERVER['REQUEST_URI'] === '/index.php') OR ($_SERVER['REQUEST_URI'] === '/') OR hasSubdomain($url) == 1) {

    //Get URL
    //check if we have a subdomina first
    $subdomain = hasSubdomain($url); //returns 1 if we have a subdomain in url    
    //if we have subdomain execute this    
    if ($subdomain == 1) {
        //check if we have a subfolder on a subdomain    
        $numberSlashs = substr_count($url, '/');
        //echo $numberSlashs;//debug
        if ($numberSlashs === 5) {   //////////////////////////////////////////OLD URL with ID///////////////////////////////////////////////////////////////
            
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
                $error_404=1;
            
        }         
        elseif ($numberSlashs === 3){
            header("HTTP/1.0 404 Not Found");
                echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
                $error_404_subdomain=1;
        }
        elseif ($numberSlashs === 4) {
            //get subdomain name            
            $subdomain_name = ucfirst(get_subdomain(current_url()));          
            $state_from_subdomain = str_replace("/","",rtrim($_SERVER['REQUEST_URI'], "/")); 
            $subdomain_name_url=$subdomain_name;
            if(strpos($subdomain_name,"-")){
                $subdomain_name=str_replace("-"," ",$subdomain_name);
            }
            $db = new ezSQL_mysql($username,$password,$database, 'localhost');
            $store_names = $db->get_results("SELECT `id`,`address`, `name`, `lat`, `lng`, `zip` FROM `usps_offices` WHERE `city`='$subdomain_name' and `state`='$state_from_subdomain'");
            //check db connection
            if(isset($db->last_error)){
                echo $db->last_error;
                die();        
            }
            if($store_names){ 
                //echo "cities";//debug
                $i = 0;
                foreach ($store_names as $store) {
                    // Access data using column names as associative array keys
                    $store_name[$i] = $store->name;
                    $store_name_for_url[$i] = ltrim(str_replace(" ", "-", $store->name),"-");
                    $store_address[$i]=$store->address;
                    $address_for_url[$i]=str_replace(" ", "-", $store->address);
                    $lat[$i]=$store->lat;
                    $lng[$i]=$store->lng;
                    $zip[$i]=$store->zip;
                    $primary[$i]=$store->id;
                    $i++;
                }
                $numbers_of_stores_in_city = count($store_name);
                //We need subfolder name for bredcrumbs:                                                                 
                //set variables for bredcrumbs

                $url_1 = str_replace(lcfirst($subdomain_name) . ".", "", $url);
                $subfolder_letter = strtolower(substr($subdomain_name, 0, 1));
                //for breadcrumbs in subdomains we need name of states as well, take from DB
                $state = $db->get_results("SELECT `state` FROM `usps_offices` WHERE `city`='$subdomain_name'");
                $state_name = $state[0]->state;                
                $detect = new Mobile_Detect;
                $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
                //echo $detect->isMobile(); echo $detect->isTablet;
                //echo $deviceType;//debug
                //show MOBILE subdomain page with office details
                //$state_head=$twig->loadTemplate('subdomain_head.twig'); 
                if ($deviceType=="phone"){
                $twig->display('m/m_header.twig', array('title' => $global_meta_title, 'meta_title' => $subdomain_name, 'multiple_places' => 1,'domain' => $domain,
                    'number' => $numbers_of_stores_in_city,'subdomain' => $subdomain_name,'subdomain_title' => 1,'state' => $state_from_subdomain));                
                $twig->display('m/m_subdomain.twig', array('store' => $store_name, 'title' => $global_meta_title,'domain' => $domain,
                    'number' => $numbers_of_stores_in_city, 'subdomain' => $subdomain_name,'this_subdomain'=>$subdomain_name_url, 'main_url' => $url_1,
                    'state' => $state_from_subdomain, 'first_letter' => $subfolder_letter, 'subdomain_url' => $url,
                    'store_link' => $store_name_for_url, 'latitude' => $lat, 'longitude' => $lng,'address' => $store_address,'url_address'=>$address_for_url,'primary'=>$primary,
                    'zip'=>$zip));
                $twig->display('m/m_footer.twig', array('domain' => $domain));
                }
                else{
                $twig->display('header.twig', array('title' => $global_meta_title, 'meta_title' => $subdomain_name, 'multiple_places' => 1,'domain' => $domain,
                    'number' => $numbers_of_stores_in_city,'subdomain' => $subdomain_name,'subdomain_title' => 1,'state' => $state_from_subdomain));                
                $twig->display('subdomain.twig', array('store' => $store_name, 'title' => $global_meta_title,'domain' => $domain,
                    'number' => $numbers_of_stores_in_city, 'subdomain' => $subdomain_name,'this_subdomain'=>$subdomain_name_url, 'main_url' => $url_1,
                    'state' => $state_from_subdomain, 'first_letter' => $subfolder_letter, 'subdomain_url' => $url,
                    'store_link' => $store_name_for_url, 'latitude' => $lat, 'longitude' => $lng,'address' => $store_address,'url_address'=>$address_for_url,'primary'=>$primary,
                    'zip'=>$zip));
                $twig->display('site-bar.twig',array('domain' => $domain));
                $twig->display('footer.twig', array('domain' => $domain));    
                }
            }
            else{
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
                $error_404=1;
            }            
        }
        elseif ($numberSlashs === 6){//xxxxxxxxxxxxxx New Office Detail Page xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
            //get subdomain name            
            $subdomain_name = ucfirst(get_subdomain(current_url()));            
            $subdomain_for_url=$subdomain_name;
            //echo $subdomain_for_url;
            $str=trim($_SERVER['REQUEST_URI'],'/');           
            //echo $state_from_subdomain;
            $get_info = explode("/", $str);            
            //var_dump($get_info);
            if(strpos($subdomain_name,"-")){
                $subdomain_name=str_replace("-"," ",$subdomain_name);
            }
            if(strpos($get_info[0],"-")){
                $this_state=str_replace("-"," ",$get_info[0]);
            }
            else{
                $this_state=$get_info[0];
            }
            if(strpos($get_info[1],"-")){
                $this_name=str_replace("-"," ",$get_info[1]);
            }
            else{
                $this_name=$get_info[1];
            }
            if(strpos($get_info[2],"-")){
                //det address and zip code
                $address_details = explode( "-",$get_info[2]);
                //take last elemnt from array - zip code
                $this_zip_code = end($address_details);
                //remove last elemnt of array - zip code
                array_pop($address_details);
                $this_address =  implode(" ",$address_details);
            }                        
            $db = new ezSQL_mysql($username,$password,$database, 'localhost');            
            //echo $this_address; echo $this_zip_code;//debug
            $store_details = $db->get_row("SELECT * FROM `usps_offices` WHERE `address` LIKE BINARY '%$this_address%' and zip=$this_zip_code");
            //check db connection
            if(isset($db->last_error)){
                echo $db->last_error;
                die();        
            }            
            $search_distance=3;
            //var_dump($store_details);
            if($store_details){
                $store_address = $store_details->address;
                $store_phone_number = $store_details->phone;
                $store_zip_code = $store_details->zip;
                $store_city = $store_details->city;
                $store_state = $store_details->state;
                $lat= $store_details->lat;
                $lng= $store_details->lng;
                $parking=$store_details->parking;
                $retailHours=$store_details->retailHours;
                $lastCollectionHours=$store_details->LastCollectionHours;
                $onlineServices=$store_details->onlineServices;
                $groupNode=$store_details->groupNode;
                $id=$store_details->id;
                // get related offices
                $distance_query = sprintf("SELECT id,`name`,city,address,state,parking,zip,lat,lng,zip4,( 3959 * acos( cos( radians(%s) ) * 
                                  cos( radians( lat ) ) * cos( radians( lng ) - radians(%s) ) + sin( radians(%s) ) * sin( radians( lat ) ) ) ) AS distance
                                  FROM usps_offices HAVING distance < %s ORDER BY distance", $lat, $lng, $lat, $search_distance);
                $related_offices=$db->get_results($distance_query);
                $i=0;
                foreach ($related_offices as $related) {
                    $related_name[$i]=  trim($related->name);
                    $url_related_name[$i]=ltrim(str_replace(" ","-",$related->name),"-");
                    $related_distance[$i]=  round($related->distance,4);
                    $related_state[$i]=$related->state;
                    $related_id[$i]=$related->id;
                    $related_city[$i]=$related->city;
                    $related_address_content[$i] = str_replace("-"," ",$related->address);
                    $related_address_url[$i]=str_replace(" ","-",$related->address);                    
                    $related_zip[$i]=$related->zip;
                    $related_parking[$i]=$related->parking;
                    $url_related_city[$i]=str_replace(" ","-",$related->city);
                    $i++;
                }        
                //var_dump($related_parking);
                //get subfolder letter for breadcrumbs from store_city 
                $subfolder_letter = strtolower(substr($store_city, 0, 1));
                //detect device
                $detect = new Mobile_Detect;
                $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
                //echo $detect->isMobile(); echo $detect->isTablet;
                //echo $deviceType;//debug
                //show MOBILE page with store details
                if ($deviceType=="phone"){
                    $twig->display('m/m_header.twig', array('single_place' =>1,'lat' => $lat,'zip' => $store_zip_code,'address' => $store_address, 'state' => $store_state,
                   'city' => $store_city,'lng' => $lng, 'store_name' => $this_name,'store_description' =>1,'domain' => $domain));                
                $twig->display('m/m_store_details.twig',array('subdomain' => $subdomain_name, 'store_name' => $this_name,
                    'address' => $store_address, 'phone' => $store_phone_number, 'zip' => $store_zip_code, 'city' => $store_city,
                    'state' => $store_state, 'first_letter' => $subfolder_letter, 'domain' => $domain, 'lat' => $lat, 
                    'lng' => $lng,'parking'=>$parking,'retailHours'=>$retailHours,'lastCollectionHours'=>$lastCollectionHours,'onlineServices'=>$onlineServices,
                    'groupNode'=>$groupNode,'related_office'=>$related_name,'related_distance'=>$related_distance,'related_state'=>$related_state,'related_id'=>$related_id,
                    'related_city'=>$related_city,'url_related_name'=>$url_related_name,'url_related_city'=>$url_related_city,'id'=>$id,'related_address_url'=>$related_address_url,
                    'related_address_content'=>$related_address_content,'related_zip'=>$related_zip,'related_parking'=>$related_parking,'subdomain_url'=>$subdomain_for_url));                
                $twig->display('m/m_footer.twig',array('domain' => $domain));
                    
                }
                else{
                //show DESKTOP page with store details 
                $twig->display('header.twig', array('single_place' =>1,'lat' => $lat,'zip' => $store_zip_code,'address' => $store_address, 'state' => $store_state,
                   'city' => $store_city,'lng' => $lng, 'store_name' => $this_name,'store_description' =>1,'domain' => $domain,'m_head'=>0));                
                $twig->display('store_details.twig',array('subdomain' => $subdomain_name, 'store_name' => $this_name,
                    'address' => $store_address, 'phone' => $store_phone_number, 'zip' => $store_zip_code, 'city' => $store_city,
                    'state' => $store_state, 'first_letter' => $subfolder_letter, 'domain' => $domain, 'lat' => $lat, 
                    'lng' => $lng,'parking'=>$parking,'retailHours'=>$retailHours,'lastCollectionHours'=>$lastCollectionHours,'onlineServices'=>$onlineServices,
                    'groupNode'=>$groupNode,'related_office'=>$related_name,'related_distance'=>$related_distance,'related_state'=>$related_state,'related_id'=>$related_id,
                    'related_city'=>$related_city,'url_related_name'=>$url_related_name,'url_related_city'=>$url_related_city,'id'=>$id,'related_address_url'=>$related_address_url,
                    'related_address_content'=>$related_address_content,'related_zip'=>$related_zip,'related_parking'=>$related_parking,'subdomain_url'=>$subdomain_for_url));
                $twig->display('site-bar.twig',array('domain' => $domain,'show_image'=>1,'state' => strtolower($store_state),'subdomain' => $subdomain_name,'city' => $store_city));
                $twig->display('footer.twig',array('domain' => $domain));
                }
            }//xxxxxxxxxxxxxx END of New Office Detail Page xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
            else{
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
                $error_404=1;
                echo "<br>"."No post office in our Databse";
            }
        }
         elseif ($numberSlashs === 7){                          
            $url_for_kml=$_SERVER['REQUEST_URI'];
            $kml=explode("/",$url_for_kml);
            //remove last element of array if empty
            if (end($kml)==""){array_pop($kml);}            
            if (end($kml)=="kml"){
                $subdomain_name = ucfirst(get_subdomain(current_url()));            
                $subdomain_for_url=$subdomain_name;
                $str=trim($_SERVER['REQUEST_URI'],'/');           
                $get_info = explode("/", $str);            
                //var_dump($get_info);
                if(strpos($subdomain_name,"-")){
                    $subdomain_name=str_replace("-"," ",$subdomain_name);
                }
                if(strpos($get_info[0],"-")){
                    $this_state=str_replace("-"," ",$get_info[0]);
                }
                else{
                    $this_state=$get_info[0];
                }
                if(strpos($get_info[1],"-")){
                    $this_name_url=$get_info[1];
                    $this_name=str_replace("-"," ",$get_info[1]);
                }
                else{
                    $this_name=$get_info[1];
                }
                if(strpos($get_info[2],"-")){
                    //det address and zip code
                    $address_details = explode( "-",$get_info[2]);
                    //take last elemnt from array - zip code
                    $this_zip_code = end($address_details);
                    //remove last elemnt of array - zip code
                    array_pop($address_details);
                    $this_address =  implode(" ",$address_details);
                } 
                
                $db = new ezSQL_mysql($username,$password,$database, 'localhost');               
                $store_details = $db->get_row("SELECT lat,lng FROM `usps_offices` WHERE `address` LIKE BINARY '%$this_address%' and zip=$this_zip_code");
                //check db connection
                if(isset($db->last_error)){
                    echo $db->last_error;
                    die();        
                }                 
                if($store_details){
                    $lng=$store_details->lng;
                    $lat=$store_details->lat;
                    $twig->display('kml.twig',array('domain' => $domain,'post_office_name'=>$this_name,'post_office_name_url'=>$this_name_url,
                        'office_address'=>$this_address,'state'=>$this_state,'zip'=>$this_zip_code,'office_address_url'=>str_replace(" ", "-",$this_address),
                        'city_url'=>$subdomain_for_url,'city'=>  str_replace("-", " ",$subdomain_for_url),'lat'=>$lat,'lng'=>$lng));
                    //echo $this_name;echo $this_address;echo $this_state;echo $this_zip_code;
                    }
                else{
                    header("HTTP/1.0 404 Not Found");
                    echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
                    $error_404=1;
                    }
                
            }
            else{
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
                $error_404=1;
                }
        }
        
        elseif ($numberSlashs > 7){
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
            $error_404=1;
            }
    }
    // otherwise display main page
    else {
        $detect = new Mobile_Detect;
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
        //echo $detect->isMobile(); echo $detect->isTablet;
        //echo $deviceType;//debug
        //show MOBILE page with store details
        if ($deviceType=="phone"){
        $twig->display('m/m_header.twig', array('title' => $global_meta_title, 'meta_title' => "usa",'domain' => $domain,'main'=>1,'error_404'=>$error_404));
        //$twig->display('site-bar.twig',array('domain' => $domain));
        $twig->display('m/m_main.twig',array('domain' => $domain,'main'=>1));
        $twig->display('m/m_footer.twig',array('domain' => $domain));
        }
        else{
        $twig->display('header.twig', array('title' => $global_meta_title, 'meta_title' => "usa",'domain' => $domain,'main'=>1,'error_404'=>$error_404));
        //$twig->display('site-bar.twig',array('domain' => $domain));
        $twig->display('main.twig',array('domain' => $domain,'main'=>1));
        $twig->display('footer.twig',array('domain' => $domain));
        }
    }
}
//if not: create static pages for subdirectories
else {
    ///Begin STATE pages ////////////////////////////////////////////////////////////////////////////////////
    //echo "state cities";//debug
    /* strip current directory from url */    
    $current_dir = $_SERVER['REQUEST_URI'];    
    echo " "; //THIS is a bug without this EMPTY echo bellow if will not work ????????!!!!!!????????????!!!!!!
    //we need to disable displaying warings becouse this bug
    ini_set('display_errors','off');
    if(rtrim($current_dir)!=="/"){
    //do nothing    
    }
    else{
        $end_of_url ="/";
        $url=$current_dir.$end_of_url;
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $url");
    }
    $subfolder = substr_count($current_dir, '/');    
    $handle_state=explode("/", $current_dir);
    $state=$handle_state[1];
    $subfolder_name=$handle_state[2];
    //check if we are in first or sec. subdirectory
    if ($subfolder == 2 or $subfolder == 3 and $subfolder_name!="") { 
        //default subfolder A
        //$subfolder='a';
        if (preg_match("/[b-z]/",$subfolder_name)){ 
            $subfolder=$subfolder_name;                
        }
        else{
            $subfolder_name="a";
        }
        // Initialise database object and establish a connection
        // at the same time - db_user / db_password / db_name / db_host
        $db = new ezSQL_mysql($username,$password,$database, 'localhost');         
        $store_number = $db->get_var("SELECT COUNT( state ) AS storeNumbres FROM `usps_offices` WHERE state = '$state'"); 
        //check db connection
        if(isset($db->last_error)){
            echo $db->last_error;
            die();        
        }         
        $cities = $db->get_results("SELECT `city`,address,name,zip FROM `usps_offices` WHERE state='$state'");
        //var_dump($cities)."<br><br><br><br>";
        if ($cities){
            /// we need to show cities now in state page, this is code from city list - START city list code////////
            //******************************************************************************************************
            //Get list of all cities
            while ($i < count($cities)) {
                $first_letter_of_city = substr($cities[$i]->city, 0, 1);
                if ($first_letter_of_city == strtoupper($subfolder_name)) {
                    $city[$i] = $cities[$i]->city;  
                    $this_address[$i] = $cities[$i]->address;
                    $this_name[$i] = $cities[$i]->name;
                    $this_zip[$i] = $cities[$i]->zip;                    
                }        
                $i++;
            }
            $number_of_post_offices=count($city);
            //set counter again to zero and use
            $i = 0;                
            //check array for duplicated elements (cities), count and create a new array without duplicated elements
            if($number_of_post_offices>0){
                $stores_in_city = (array_count_values($city));    

                //var_dump($stores_in_city);
                foreach ($stores_in_city as $city_name => $store_numbers) {
                    //get number of offices
                    $store_numbers_city[$i]=$store_numbers;
                    //get city names
                    $city_name_1[$i] = $city_name;                                        
                    //create seo urls for links
                    $seo_url_stores_in_city[$i]= str_replace(" ", "-",$city_name_1[$i]);
                    $i++;
                }
                //get details for city_name_1 (details for stores in each city)
                foreach ($city_name_1 as $city_name_1_key=>$city_name_1_value){
                   //echo $key.":";//debug
                    $i=0;
                    foreach ($city as $city_key=>$city_value){                        
                        if ($city_name_1_value==$city_value){
                            $address_in_city[$city_name_1_value][$i] = $this_address[$city_key];
                            $address_in_city_seo[$city_name_1_value][$i] = ltrim(str_replace(" ", "-", $address_in_city[$city_name_1_value][$i]),"-");
                            $name_in_city[$city_name_1_value][$i] = $this_name[$city_key];
                            $name_in_city_seo[$city_name_1_value][$i]= ltrim(str_replace(" ", "-", $name_in_city[$city_name_1_value][$i]),"-");
                            $zip_in_city[$city_name_1_value][$i] = $this_zip[$city_key]; 
                            $i++;                           
                        }      
                    }
                     //echo count($address_in_city[$city_name_1_value]);//debug
                     //slice BIG arrays into smaller arrays
                     if (count($address_in_city[$city_name_1_value])>=200 ){
                         $address_in_city[$city_name_1_value]=array_slice($address_in_city[$city_name_1_value], 0, floor(count($address_in_city[$city_name_1_value])/10));
                         $split[$i]=1;
                     }
                     elseif (count($address_in_city[$city_name_1_value])>100 and count($address_in_city[$city_name_1_value])<200){
                         $address_in_city[$city_name_1_value]=array_slice($address_in_city[$city_name_1_value], 0, floor(count($address_in_city[$city_name_1_value])/6));
                         $split[$i]=1;
                     }

                }                
            }   
            $i = 0;            
            //create image for page or use available image
            $img_path="images/";
            $full_state_name=$state;
            $state_img=format_state( $state, 'abbr');
            $filename=strtolower($full_state_name)."-post-offices".".png";
            if (file_exists($img_path.$filename)){
                $site_img=$img_path.$filename;
            }
            else{
            $site_img=create_img($state_img,$full_state_name);
            }
            $number_of_cities = count($stores_in_city);
//            
//            //************START PAGINATION*******************************************************************
//            ////************************************SET VALUES FOR PAGINATION********************************************************* 
//            //we need number of cites first
//            //var_dump($stores_in_city);
//            $url_for_pn = $_SERVER['REQUEST_URI'];
//            $pn_elements= explode('/', $url_for_pn);
//            array_pop($pn_elements);            
//            $nr = $number_of_cities;
//            if (isset($pn_elements[3])) { // Get pn from URL vars if it is present
//                $pn = preg_replace('#[^0-9]#i', '', $pn_elements[3]); // filter everything but numbers for security(new)
//            } else { // If the pn URL variable is not present force it to be value of page number 1
//                $pn = 1;
//            }
//            //echo "number of cities:".$nr."<br>";//debug
//            //echo "page number:".$pn."<br>";//debug
//            //This is where we set how many database items to show on each page
//            $itemsPerPage = 6;
//            // Get the value of the last page in the pagination result set
//            $lastPage = ceil($nr / $itemsPerPage);
//            // Be sure URL variable $pn(page number) is no lower than page 1 and no higher than $lastpage
//            if ($pn < 1) { // If it is less than 1
//                $pn = 1; // force if to be 1
//            } else if ($pn > $lastPage) { // if it is greater than $lastpage
//                $pn = $lastPage; // force it to be $lastpage's value
//            }
//            //echo "last page:".$lastPage."<br>";//debug
//            $centerPages = "";
//            $sub1 = $pn - 1;
//            $sub2 = $pn - 2;
//            $add1 = $pn + 1;
//            $add2 = $pn + 2;
//            if ($pn == 1) {
//                $centerPages .= '&nbsp; <span class="pagNumActive">' . $pn . '</span> &nbsp;';
//                for ( $counter = 2; $counter <= $lastPage; $counter++ ) {
//                    $centerPages .= '&nbsp; <a href="http://' . $domain."/".$state."/".$subfolder_name."/"  .$counter . '/">' . $counter . '</a> &nbsp;';
//                    }
//            } 
//            else if ($pn == $lastPage) {
//                $centerPages .= '&nbsp; <a href="http://' . $domain."/".$state."/".$subfolder_name."/"  . $sub1 . '/">' . $sub1 . '</a> &nbsp;';
//                $centerPages .= '&nbsp; <span class="pagNumActive">' . $pn . '</span> &nbsp;';
//            } 
//            else if ($pn > 2 && $pn < ($lastPage - 1)) {
//                for ( $counter = 1; $counter <= $lastPage; $counter++ ) {
//                    if($pn!=$counter){
//                        $centerPages .= '&nbsp; <a href="http://' . $domain."/".$state."/".$subfolder_name."/"  .$counter . '/">' . $counter . '</a> &nbsp;';
//                    }
//                    else{
//                    $centerPages .= '&nbsp; <span class="pagNumActive">' . $pn . '</span> &nbsp;';    
//                    }
//                    }
//            } else if ($pn > 1 && $pn < $lastPage) {
//                $centerPages .= '&nbsp; <a href="http://' . $domain."/".$state."/".$subfolder_name."/"  . $sub1 . '/">' . $sub1 . '</a> &nbsp;';
//                $centerPages .= '&nbsp; <span class="pagNumActive">' . $pn . '</span> &nbsp;';
//                $centerPages .= '&nbsp; <a href="http://' . $domain."/".$state."/".$subfolder_name."/"  . $add1 . '/">' . $add1 . '</a> &nbsp;';
//            }
//            // This line sets the "LIMIT" range... the 2 values we place to choose a range of rows from database in our query
//            $limit = 'LIMIT ' . ($pn - 1) * $itemsPerPage . ',' . $itemsPerPage;   
//            //echo $limit;//debug
//            //**************END setting VALUES  for Pagination************************************************
//            //END city list code *****************************************************************************
//            $query_cities=$db->get_results("SELECT `city` FROM `usps_offices` WHERE state='$state' $limit");            
//            //var_dump($query);//debug
////            foreach ($query as $id => $query->author){
////                $authors[$id]=$query[$id]->author;
////                $author_url[$id]=str_replace(" ", "-", $authors[$id]);
////                }        
//            $paginationDisplay = ""; // Initialize the pagination output variable
//            // This code runs only if the last page variable is ot equal to 1, if it is only 1 page we require no paginated links to display
//            if ($lastPage != "1") {
//                // This shows the user what page they are on, and the total number of pages
//                $paginationDisplay .= 'Page <strong>' . $pn . '</strong> of ' . $lastPage . '&nbsp;  &nbsp;  &nbsp; ';
//                // If we are not on page 1 we can place the Back button
//                if ($pn != 1) {
//                    $previous = $pn - 1;
//                    $paginationDisplay .= '&nbsp;  <a href="http://' . $domain."/".$state."/".$subfolder_name."/". $previous ."/". '"> Back</a> ';
//                    }
//                // Lay in the clickable numbers display here between the Back and Next links
//                $paginationDisplay .= '<span class="paginationNumbers">' . $centerPages . '</span>';
//                // If we are not on the very last page we can place the Next button
//                if ($pn != $lastPage) {
//                    $nextPage = $pn + 1;
//                    $paginationDisplay .= '&nbsp;  <a href="http://' . $domain."/".$state."/".$subfolder_name."/". $nextPage ."/". '"> Next</a> ';
//                    }
//               }
//               
//               //**************************************END PAGINATION************************************************
//        ///////////////////////////////////////////////////////////////////////////////////////////////////////

            //Get number of offices in all cities that begin with same letter
            $b = 0;
            $c = 0;
            foreach (range('A', 'Z') as $abc_letter) {
                for ($i = 0; $i < $store_number; $i++) {
                    $first_letter_of_city = substr($cities[$i]->city, 0, 1);
                    if ($abc_letter == $first_letter_of_city) {
                        $equal_first_letter[$b++] = $first_letter_of_city;
                    }
                }
                $number_of_stores_in_city[$c] = count($equal_first_letter);
                $c++;
                $b = 0;
                unset($equal_first_letter);
            } 
            $detect = new Mobile_Detect;
            $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
            //display list of cities in state for mobile
             if ($deviceType=="phone"){
                $twig->display('m/m_header.twig', array('state_header' =>1, 'title' => $global_meta_title, 'meta_title' => $state,'domain' => $domain,'state_title'=>1,
                'number_of_stores_on_record' => $store_number,'three_cities_title'=>$city_name_1[0].", ".$city_name_1[1].", ".$city_name_1[2]."...",'first_letter' => $subfolder_name,
                'number' => $number_of_cities));                
                $twig->display('m/m_states.twig', array('headline' => $state, 'current_dir' => $state, 'store_numbers' => $number_of_stores_in_city,
                'number_of_stores_on_record' => $store_number, 'breadcrumb' => $breadcrumbs,'domain' => $domain,'first_letter' => $subfolder_name,
                'city' => $city_name_1, 'number' => $number_of_cities, 'city_name' => $seo_url_stores_in_city,
                'number_of_offices'=>$store_numbers_city,'paginationDisplay'=>$paginationDisplay,
                        'total_office_number'=>$number_of_post_offices,'image'=>$site_img,'multi_address'=>$address_in_city,'multi_address_seo'=>$address_in_city_seo,
                'multi_zip'=>$zip_in_city,'multi_name'=>$name_in_city,'multi_name_seo'=>$name_in_city_seo,'short_state'=>$state_img,'split_yes'=>$split));
                //$twig->display('site-bar.twig',array('domain' => $domain));
                $twig->display('m/m_footer.twig', array('domain' => $domain));
                }
                else{
            //display list of cities in state
                $twig->display('header.twig', array('state_header' =>1, 'title' => $global_meta_title, 'meta_title' => $state,'domain' => $domain,'state_title'=>1,
                'number_of_stores_on_record' => $store_number,'three_cities_title'=>$city_name_1[0].", ".$city_name_1[1].", ".$city_name_1[2]."...",'first_letter' => $subfolder_name,
                'number' => $number_of_cities));
                $twig->display('states.twig', array('headline' => $state, 'current_dir' => $state, 'store_numbers' => $number_of_stores_in_city,
                'number_of_stores_on_record' => $store_number, 'breadcrumb' => $breadcrumbs,'domain' => $domain,'first_letter' => $subfolder_name,
                'city' => $city_name_1, 'number' => $number_of_cities, 'city_name' => $seo_url_stores_in_city,'domain' => $domain,
                'number_of_offices'=>$store_numbers_city,'paginationDisplay'=>$paginationDisplay,
                        'total_office_number'=>$number_of_post_offices,'image'=>$site_img,'multi_address'=>$address_in_city,'multi_address_seo'=>$address_in_city_seo,
                'multi_zip'=>$zip_in_city,'multi_name'=>$name_in_city,'multi_name_seo'=>$name_in_city_seo,'short_state'=>$state_img,'split_yes'=>$split));
                $twig->display('site-bar.twig',array('domain' => $domain));
                $twig->display('footer.twig',array('domain' => $domain));
                }
        }
        else{
            //echo "problem with database, please go back";
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
            $error_404=1;
        }
    } 
    else {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
        $error_404=1;
        //BELLOW IS OLD CITY LIST CODE
//        //cut off first subdirectory name
//        $state_name = extract_state_name($current_dir, '/', '/');
//        //get sec. subdirectory name from array - one letter will be first element of revers array        
//        $reverse = strrev($current_dir);
//        $get_subfoler=  explode("/", $reverse);
//        //save first element (subfolder name) into variable
//        $subfolder = $reverse[0];
//        //sub-folder has to match a-Z characters and only 1 char in size
//        if (preg_match("/[a-z]/",$subfolder)and strlen($get_subfoler[0])==1){                
//            //connect to DB
//            $db = new ezSQL_mysql($username,$password, $database, 'localhost');
//            $cities = $db->get_results("SELECT `city`,`address` FROM `usps_offices` WHERE state='$state_name'");
//            if ($cities){
//                //echo $state_name;//debug
//                $i = 0;
//                //$first_letter = substr($cities[$i]->city, 0, 1);
//                //echo $first_letter;
//                while ($i < count($cities)) {
//                    $first_letter_of_city = substr($cities[$i]->city, 0, 1);
//                    if ($first_letter_of_city == strtoupper($subfolder)) {
//                        $city[$i] = $cities[$i]->city;                        
//                    }        
//                    $i++;
//                }
//                $number_of_post_offices=count($city);
//                //set counter again to zero and use
//                $i = 0;                
//                //check array for duplicated elements, count and create a new array without duplicated elements
//                if($number_of_post_offices>0){
//                    $stores_in_city = (array_count_values($city));    
//
//                    //var_dump($stores_in_city);
//                    foreach ($stores_in_city as $city_name => $store_numbers) {
//                          //get number of offices
//                          $store_numbers_city[$i]=$store_numbers;
//                          //get city names
//                          $city_name_1[$i] = $city_name;
//                          //create seo urls for links
//                          $seo_url_stores_in_city[$i]= str_replace(" ", "-",$city_name_1[$i]);
//                          $i++;
//    //                    $numbers_of_stores_in_city[$i] = "{$city_name} {$store_numbers}\n";
//    //                    $i++;
//                    }
//                }
//                //create image for page or use available image
//                //echo format_state( $state_name, 'abbr');
//                $img_path="images/";
//                $full_state_name=$state_name;
//                $state=format_state( $state_name, 'abbr');
//                $filename=strtolower($full_state_name)."-post-offices".".png";
//                if (file_exists($img_path.$filename)){
//                    $site_img=$img_path.$filename;
//                }
//                else{
//                $site_img=create_img($state,$full_state_name);
//                }
//                //city list display results
//                $number_of_cities = count($stores_in_city);
//                $twig->display('header.twig', array('city_title' =>1,'first_letter' => $subfolder,'state' => $state_name,'domain' => $domain,'number' => $number_of_cities,
//                    'total_office_number'=>$number_of_post_offices));
//                $twig->display('city_list.twig', array('headline' => $state_name, 'state' => $state_name, 'first_letter' => $subfolder,
//                    'city' => $city_name_1, 'number' => $number_of_cities, 'city_name' => $seo_url_stores_in_city,'domain' => $domain,'number_of_offices'=>$store_numbers_city,
//                    'total_office_number'=>$number_of_post_offices,'image'=>$site_img));
//                $twig->display('site-bar.twig',array('domain' => $domain));
//                $twig->display('footer.twig',array('domain' => $domain));
//            }
//            else{
//            header("HTTP/1.0 404 Not Found");
//            echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
//            $error_404=1;
//        }
//        }
//        else{
//            header("HTTP/1.0 404 Not Found");
//            echo "404 Not Found"."<br><a href=http://".$domain.">$domain</a>";
//            $error_404=1;
//        }
    }
}