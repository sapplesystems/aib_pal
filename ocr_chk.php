<?php
define('APIKEY',"87fc0d6d9689d84ab48f583175f9522dg");
// Function to call server
// -----------------------
function aib_request($LocalPostData,$FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "http://develop.archiveinabox.com:80/api/".$FunctionSet.".php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
	);

	curl_setopt_array($CurlObj,$Options);
	$Result = curl_exec($CurlObj);
	if ($Result == false)
	{
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
	}
	else
	{
		$OutData = json_decode($Result,true);
	}

	curl_close($CurlObj);
	return($OutData);
}

	// Generate key

	$PostData = array(
		"_id" => "test",
		"_key" => APIKEY,
		"_user" => 1,
	);

	// Make AIB request

	$Result = aib_request($PostData,"session");

	// Check for request errors

	if ($Result["status"] != "OK")
	{
		print("ERROR: Cannot get session key; ".$Result["info"]."\n");
		exit(0);
	}

	$sessionKey = $Result["info"];
 $postData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $_REQUEST['id']
        );
		
        $apiResponse = aib_request($postData, 'browse');
		echo '<pre>';
		print_r($postData);  
print("RESPONSE: \n");
  print_r($apiResponse);
/*  $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => 71698,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_get_property" => 'Y' ,
            "opt_deref_links" => 'Y',
            "opt_get_link_source_properties" => 'Y' ,
            "opt_get_long_prop" => 'Y',
            "opt_get_prop_count" => 'Y',
            "opt_prop_count_set" => json_encode($search_filter),
            "opt_get_root_folder"=> 'Y',
            "opt_get_root_prop"=> 'Y'
        );
		
        $apiResponse = aib_request($postData, 'browse');
		echo '<pre>';
		print_r($postData);  
print("RESPONSE: \n");
  print_r($apiResponse);
  
	  $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => 76572,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_get_property" => 'Y' ,
            "opt_deref_links" => 'Y',
            "opt_get_link_source_properties" => 'Y' ,
            "opt_get_long_prop" => 'Y',
            "opt_get_prop_count" => 'Y',
            "opt_prop_count_set" => json_encode($search_filter),
            "opt_get_root_folder"=> 'Y',
            "opt_get_root_prop"=> 'Y'
        );
		
        $apiResponse = aib_request($postData, 'browse');
		echo '<pre>';
		print_r($postData);  
print("RESPONSE: \n");
  print_r($apiResponse);*/