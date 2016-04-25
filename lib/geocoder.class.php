<?php
class geocoder {
	public static function lookup( $string, $proxy = null ){
	   $string = str_replace(" ", "+", urlencode($string));
	   $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $string . "&sensor=false";
	 
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL, $details_url);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   if ( $proxy ) curl_setopt($ch, CURLOPT_PROXY, $proxy);

	   $response = json_decode(curl_exec($ch), true);
	 
	   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
	   if ( $response['status'] == 'ZERO_RESULTS' ) {
		return null;
	   } elseif ($response['status'] != 'OK') {
		throw new Exception( 'Response:'.$response['status'] );
	   }
	 
	   $geometry = $response['results'][0]['geometry'];
	 
	   $array = array(
	       $geometry['location']['lat'],
	       $geometry['location']['lng'],
	   );
	 
	   return $array;
	}
}
