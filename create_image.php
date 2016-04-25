<?php

$img_path="images/";
$full_state_name="Oregon";
$state="OR";
$filename=strtolower($full_state_name)."-post-offices".".png";

if (file_exists($img_path.$filename)){
    print 'file exists';
}
else{
$site_img=create_img($state,$full_state_name);
}
echo $site_img;

function create_img ($state,$full_state_name){
// Set the content-type, olny if you want to show image in browser
//header('Content-Type: image/png');
// The text to draw
$text=$full_state_name;
$text1=$state;
// Replace path by your own font path
$font = './ttf/arial.ttf';
$font_state = './ttf/UNIVERSAL-COLLEGE.ttf';
$font_size=20;
$font_size_state=28;
// Create the image
//$imgg_width='160';
//$imgg_height='160';
//$img = imagecreatetruecolor($imgg_width,$imgg_height);
$img=imagecreatefrompng ( "images/category.png" );
// Create some colors
$white = imagecolorallocate($img, 255, 255, 255);
$grey = imagecolorallocate($img, 128, 128, 128);
$black = imagecolorallocate($img, 0, 0, 0);
//imagefilledrectangle($img, 0, 0, 399, 29, $black);
//$text=wrap($font_size,0,$font,$text,$imgg_width);
$size_state = imagettfbbox(25, 17, $font_state, $text1);
$size = imagettfbbox(30, 15, $font, $text);
// Add some shadow to the text
imagettftext($img, $font_size_state, 0, abs($size_state[3]), abs($size_state[5]), $grey, $font_state, $text1);
// Add the text
imagettftext($img, $font_size, 10, abs($size[3]),abs($size[5]), $gray, $font, $text);
imagettftext($img, $font_size, 10, abs($size[0]),abs($size[5]), $white, $font, $text);
// output image to browser
//imagepng($img);
// save image
$save_as="images/".strtolower($full_state_name)."-post-offices".".png";
imagepng($img,$save_as);
imagedestroy($img);
return $save_as;
}

