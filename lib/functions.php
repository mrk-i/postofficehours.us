<?php


//return current url
function current_url() {
    $isHTTPS = ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" );
    $isPort = ( isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));

    $port = ( $isPort ) ? ( ':' . $_SERVER["SERVER_PORT"] ) : '';

    //On some setups like nginx and php-fastcgi, REQUEST_URI include the query string  
    if (($pos = strpos($_SERVER['REQUEST_URI'], '?')) === false) {
        // REQUEST_URI include the query string, it should be appended:  

        $isQuery = ( isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"] != '');
        $query = ( $isQuery ) ? ( '?' . $_SERVER["QUERY_STRING"] ) : '';

        $url = ( $isHTTPS ? 'https://' : 'http://')
                . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"] . $query;
    } else {
        // the query string is already included in $_SERVER["REQUEST_URI"], no need to append it  
        $url = ( $isHTTPS ? 'https://' : 'http://')
                . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];
    }

    return $url;
}

//extract_state_name function will extract a part of text between two delimeters /georgia/ it will return georgia
function extract_state_name($string, $start, $end) {
    $pos = stripos($string, $start);
    $str = substr($string, $pos);
    $str_two = substr($str, strlen($start));
    $second_pos = stripos($str_two, $end);
    $str_three = substr($str_two, 0, $second_pos);
    $state_name = trim($str_three); // remove whitespaces
    return $state_name;
}

//function that checks if a subdomain exists in URL
function hasSubdomain($url) {
    $parsed = parse_url($url);
    $exploded = explode('.', $parsed["host"]);
    return (count($exploded) > 2);
}

//get subdomain from url, if subdomain exist it will return 1, url must include http://
function get_subdomain($url) {
    $parsedUrl = parse_url($url);
    $host = explode('.', $parsedUrl['host']);
    $subdomain = $host[0];
    return $subdomain;
}

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


 /**
     * Format State
     *
     * Note: Does not format addresses, only states. $input should be as exact as possible, problems
     * will probably arise in long strings, example 'I live in Kentukcy' will produce Indiana.
     *
     * @example echo myClass::format_state( 'Florida', 'abbr'); // FL
     * @example echo myClass::format_state( 'we\'re from georgia' ) // Georgia
     * 
     * @param  string $input  Input to be formatted
     * @param  string $format Accepts 'abbr' to output abbreviated state, default full state name.
     * @return string          Formatted state on success, 
     */
    function format_state( $input, $format = '' ) {
        if( ! $input || empty( $input ) )
            return;

        $states = array (
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District Of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        foreach( $states as $abbr => $name ) {
            if ( preg_match( "/\b($name)\b/", ucwords( strtolower( $input ) ), $match ) )  {
                if( 'abbr' == $format ){ 
                    return $abbr;
                } 
                else return $name;
            }
            elseif( preg_match("/\b($abbr)\b/", strtoupper( $input ), $match) ) {                    
                if( 'abbr' == $format ){ 
                    return $abbr;
                } 
                else return $name;
            } 
        }
        return;
    }
    