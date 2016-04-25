<?php

if ( !isset( $argv[1] ) ) {
	die("Usage: $argv[0] path/to/target/dir\n");
}

$path = realpath( $argv[1] );
$re = "/" . preg_quote( $path, "/" ) . preg_quote( DIRECTORY_SEPARATOR, "/" ) . "?/";
$result = array();

$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST );
foreach ( $objects as $file => $obj ) {
	if ( $obj->getFilename() != "index.html" ) continue;

	$dir = str_replace( $obj->getPath(), "", $path );
	$dir = $obj->getPath();
	$dir = preg_replace( $re, "", $dir );

	$pieces = explode( DIRECTORY_SEPARATOR, $dir );
	if ( count( $pieces ) == 3 ) {
		array_unshift( $pieces, "US" );
	} elseif ( count( $pieces ) != 4 ) {
		continue;
	}

	$content = file_get_contents( $obj->getPathname() );

	$matches = array();

	if ( !preg_match( "/<h1>(.*?)<\/h1>/", $content, $matches ) ) {
		#print "Can't find company name: $file\n";
		continue;
	}
	
	$company_name = trim( preg_replace( "/.*\//", "", $matches[1] ) );
	
	if ( !preg_match( "/<h2>Where To Find Us<\/h2>(.*)<h2>/s", $content, $matches ) ) {
		#print "Can't find company address: $file\n";
		continue;
	}

	$lines = explode( "\n", $matches[1] );

	$addr1 = trim( preg_replace( "/<.*>/", "", $lines[1] ) );
	$addr2 = trim( preg_replace( "/<.*>/", "", $lines[2] ) );
	$phone = trim( preg_replace( "/<.*>/", "", $lines[4] ) );

	array_push( $result, array( $pieces[0], $pieces[1], $pieces[2], $pieces[3], $company_name, $addr1, $phone ) );
}

#####

function sort_func( $a, $b ) {
	for ( $i = 0; $i < 4; $i++ ) {
		$res = strcmp( $a[$i], $b[$i] );
		if ( $res ) return $res;
	}
}

usort( $result, "sort_func" );

$result_fh = fopen( "result.csv", "w" );
foreach ( $result as $row ) {
	fputcsv( $result_fh, $row );
}
fclose( $result_fh );
?>
