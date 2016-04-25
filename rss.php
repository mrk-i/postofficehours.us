<?php
include 'config.php';
// You should use an autoloader instead of including the files directly.
// This is done here only to make the examples work out of the box.
include 'feeds/Item.php';
include 'feeds/Feed.php';
include 'feeds/RSS2.php';
// Include ezSQL core DB wrapper
include_once "lib/ez_sql_core.php";
// Include ezSQL database specific component
include_once "lib/ez_sql_mysql.php";

date_default_timezone_set('UTC');

use \FeedWriter\RSS2;

// Creating an instance of RSS2 class.
$my_site_feed = new RSS2;

// Setting some basic channel elements. These three elements are mandatory.
$my_site_feed->setTitle('Last updates postofficehours.us');
$my_site_feed->setLink('http://postofficehours.us');
$my_site_feed->setDescription('Last changes and updates - post office open hours USA');

// Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0,
// which were set above.
//$my_site_feed->setImage('Testing & Checking the Feed Writer project', 'https://github.com/mibe/FeedWriter', 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Rss-feed.svg/256px-Rss-feed.svg.png');

// Use core setChannelElement() function for other optional channel elements.
// See http://www.rssboard.org/rss-specification#optionalChannelElements
// for other optional channel elements. Here the language code for American English and
$my_site_feed->setChannelElement('language', 'en-US');

// The date when this feed was lastly updated. The publication date is also set.
$my_site_feed->setDate(date(DATE_RSS, time()));
$my_site_feed->setChannelElement('pubDate', date(\DATE_RSS, strtotime('2015-04-22')));

// You can add additional link elements, e.g. to a PubSubHubbub server with custom relations.
// It's recommended to provide a backlink to the feed URL.
//$my_site_feed->setSelfLink('http://example.com/myfeed');
//$my_site_feed->setAtomLink('http://pubsubhubbub.appspot.com', 'hub');

// You can add more XML namespaces for more custom channel elements which are not defined
// in the RSS 2 specification. Here the 'creativeCommons' element is used. There are much more
// available. Have a look at this list: http://feedvalidator.org/docs/howto/declare_namespaces.html
$my_site_feed->addNamespace('creativeCommons', 'http://backend.userland.com/creativeCommonsRssModule');
$my_site_feed->setChannelElement('creativeCommons:license', 'http://www.creativecommons.org/licenses/by/1.0');

// If you want you can also add a line to publicly announce that you used
// this fine piece of software to generate the feed. ;-)
$my_site_feed->addGenerator();

// Here we are done setting up the feed. What's next is adding some feed items.
$db = new ezSQL_mysql($username,$password,$database, 'localhost');
//$db = new ezSQL_mysql('root','','usps','localhost');
$data = $db->get_results("SELECT * FROM `latest_changes` ORDER BY id DESC LIMIT 40");

foreach ($data as $value){
    $latest_update_id[$i]=$value->id;
    $latest_update_post_office_id[$i]=$value->post_office_id;
   
    $latest_update_states[$i]=$value->states;
    $latest_update_city=str_replace(" ", "-",$value->city);

//// Create items
$newItem = $my_site_feed->createNewItem();

// Add basic elements to the feed item
// These are again mandatory for a valid feed.
$newItem->setTitle('Post office open hours in '.$value->city." ".$value->states);
$newItem->setLink('http://'.$latest_update_city.".".$domain."/".$value->states."/");
$newItem->setDescription($value->states.' new hours of operation for few post offices in '.$value->city);

// The following method calls add some optional elements to the feed item.

// Let's set the publication date of this item. You could also use a UNIX timestamp or
// an instance of PHP's DateTime class.
$newItem->setDate($value->date_added);
// Now add the feed item to the main feed.
$my_site_feed->addItem($newItem);
}

// You can also attach a media object to a feed item. You just need the URL, the byte length
// and the MIME type of the media. Here's a quirk: The RSS2 spec says "The url must be an http url.".
// Other schemes like ftp, https, etc. produce an error in feed validators.
//$newItem->setEnclosure('http://upload.wikimedia.org/wikipedia/commons/4/49/En-us-hello-1.ogg', 11779, 'audio/ogg');

// If you want you can set the name (and email address) of the author of this feed item.
//$newItem->setAuthor('Anis uddin Ahmad', 'admin@ajaxray.com');

// You can set a globally unique identifier. This can be a URL or any other string.
// If you set permaLink to true, the identifier must be an URL. The default of the
// permaLink parameter is false.
//$newItem->setId('http://example.com/URL/to/article', true);

// Use the addElement() method for other optional elements.
// This here will add the 'source' element. The second parameter is the value of the element
// and the third is an array containing the element attributes.
//$newItem->addElement('source', 'Mike\'s page', array('url' => 'http://www.example.com'));



// Another method to add feeds items is by using an array which contains key-value pairs
// of every item element. Elements which have attributes cannot be added by this way.
//$newItem = $my_site_feed->createNewItem();
//$newItem->addElementArray(array('title'=> 'The 2nd item', 'link' => 'http://www.google.com', 'description' => 'Just another test.'));
//$my_site_feed->addItem($newItem);
//
//// OK. Everything is done. Now generate the feed.
//// If you want to send the feed directly to the browser, use the printFeed() method.
//$myFeed = $my_site_feed->generateFeed();
//
//// Do anything you want with the feed in $myFeed. Why not send it to the browser? ;-)
//// You could also save it to a file if you don't want to invoke your script every time.
//echo $myFeed;
$my_site_feed->printFeed(); 
