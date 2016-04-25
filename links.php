<?php
$set_caching = 0;
$domain = "postofficehours.us";
require_once('lib/Twig/Autoloader.php');
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');

//check if caching is set
if (set_caching == 0) {
    $cache_directory = array('cache' => false);
} else {
    $cache_directory = array('cache' => 'cache');
}
//set caching directory
$twig = new Twig_Environment($loader, $cache_directory);
require_once ('lib/Mobile_Detect.php');
$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
//echo $detect->isMobile(); echo $detect->isTablet;
if ($deviceType=="phone"){
    $twig->display('m/m_header.twig', array('set_links_title'=>1, 'domain' => $domain,'title' => $global_meta_title));
        $twig->display('m/m_links.twig',array('domain' => $domain,));        
        $twig->display('m/m_footer.twig',array('domain' => $domain));
}
else{
$twig->display('header.twig', array('set_links_title'=>1, 'domain' => $domain,'title' => $global_meta_title));
        $twig->display('links.twig',array('domain' => $domain,));
        $twig->display('site-bar.twig',array('domain' => $domain));        
        $twig->display('footer.twig',array('domain' => $domain));
}