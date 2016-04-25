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

$twig->display('header.twig', array('title' => $global_meta_title,'domain' => $domain,'report'=>1));
$twig->display('report.twig',array('domain' => $domain));
$twig->display('site-bar.twig',array('domain' => $domain));
$twig->display('footer.twig',array('domain' => $domain));

