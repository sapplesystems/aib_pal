<?php

define('APIKEY', "87fc0d6d9689d84ab48f583175f9522dg");
ini_set('max_execution_time', 1000);
// Function to call server
// -----------------------
function aibServiceRequest($LocalPostData, $FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "http://develop.archiveinabox.com/api/" . $FunctionSet . ".php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
	);

	curl_setopt_array($CurlObj, $Options);
	$Result = curl_exec($CurlObj);
	if ($Result == false) {
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
	} else {
		$OutData = json_decode($Result, true);
	}

	curl_close($CurlObj);
	return ($OutData);
}

$PostData = array(
	"_id" => "test",
	"_key" => APIKEY,
	"_user" => 1,
);

// Make AIB request

$Result = aibServiceRequest($PostData, "session");

// Check for request errors

if ($Result["status"] != "OK") {
	print("ERROR: Cannot get session key; " . $Result["info"] . "\n");
	exit(0);
}

$sessionKey = $Result["info"];



	$postData = array(
		"_key" => APIKEY,
		"_session" => $sessionKey,
		"_user" => 1,
		"_op" => "items_with_prop",
		"prop_name" => 'itemrecord_lat',
	);
	// Service request to get item tree data        
	$apiResponse = aibServiceRequest($postData, 'browse');

foreach($apiResponse['info']['records'] as $r){
	echo '<pre>==='. $r;
		$postDataPath = array(
			"_key" => APIKEY,
			"_session" => $sessionKey,
			"_user" => 1,
			"_op" => "get_path",
			"obj_id" => $r
		);
		// Service request to get item tree data        
		$apiResponsePath = aibServiceRequest($postDataPath, 'browse');

		$society_id_context=0;
		if ($apiResponsePath['status'] == 'OK' and isset($apiResponsePath['info']['records'][1])) {


			$society_id_context=$apiResponsePath['info']['records'][1]['item_id'];
			$postDataLocation = array(
			"_key" => APIKEY,
			"_session" => $sessionKey,
			"_user" => 1,
			"_op" => "get",
			"obj_id" => $r,

			);

			$apiResponseLocation = aibServiceRequest($postDataLocation, 'locationsearch');

			$itemrecord_lat=$apiResponseLocation['lat'];
			$itemrecord_lng=$apiResponseLocation['lon'];
			if(trim($itemrecord_lat)!='' and trim($itemrecord_lng)!='' ){
				$postDataLocation = array(
				"_key" => APIKEY,
				"_session" => $sessionKey,
				"_user" => 1,
				"_op" => "set",
				"obj_id" => $r,
				"lat" =>$itemrecord_lat,
				"lon" =>	$itemrecord_lng,
				"alt" =>1,
				"context"=>	$society_id_context
				);
				print_R($postDataLocation);echo '</pre>';
				// Service request to get item tree data        
				$apiResponseLocation = aibServiceRequest($postDataLocation, 'locationsearch');
			}
			
		}	

		
		
	}
	

	