<?php

define('APIKEY', "93167f8656d6cd7c211e7efdfe6e5d4d");
//ini_set('max_execution_time', 1000);
// Function to call server
// -----------------------
function aibServiceRequest($LocalPostData, $FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "https://www.archiveinabox.com/api/" . $FunctionSet . ".php",
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


function aib_request($sessionKey, $item_id)
{
	$postData = array(
		"_key" => APIKEY,
		"_session" => $sessionKey,
		"_user" => 1,
		"_op" => "list",
		"parent" => $item_id,
	);
	// Service request to get item tree data        
	$apiResponse = aibServiceRequest($postData, 'browse');
	if ($apiResponse["status"] != "OK") {
	print("ERROR: Cannot get session key; " . $apiResponse["info"] . "\n");
	exit(0);
}
	//echo '<pre>';
	//print_r($postData);
	//print_r($apiResponse);

	static $ar_count = 0;
	static $co_count = 0;
	static $sg_count = 0;
	static $re_count = 0;
	static $it_count = 0;
	static $sg_count_arr = array();
	static $re_count_arr = array();
	static $it_count_arr = array();
	if (!empty($apiResponse) && $apiResponse['status'] == 'OK') {
		foreach ($apiResponse['info']['records'] as $k1 => $v1) {
			$item_id = $v1['item_id'];
			if ($v1['item_type'] == 'AR') {
				$ar_count++;
			} else if ($v1['item_type'] == 'CO') {
				$co_count++;
			} else if ($v1['item_type'] == 'SG') {
				$sg_count++;
				$sg_count_arr[]= $item_id ;
			} else if ($v1['item_type'] == 'RE') {
				$re_count++;
				$re_count_arr[]= $item_id ;
			} else if ($v1['item_type'] == 'IT') {
				$it_count++;
				$it_count_arr[]= $item_id ;
			}
			echo '<br>'.$item_id;
			aib_request($sessionKey, $item_id);
		}
	}

	return [
		'ar_count' => $ar_count,
		'co_count' => $co_count,
		'sg_count' => $sg_count,
		're_count' => $re_count,
		'it_count' => $it_count,
		'sg_count_arr' => $sg_count_arr,
		're_count_arr' => $re_count_arr,
		'it_count_arr' => $it_count_arr
	];
}

 $result = aib_request($sessionKey, 539319);
 echo '<pre>';
 print_r($result);


