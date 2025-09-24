<?php
define('APIKEY',"87fc0d6d9689d84ab48f583175f9522d");

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
function aib_search_request($LocalPostData)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "http://develop.archiveinabox.com:80/cgi-bin/estsearchutil",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
	);

	curl_setopt_array($CurlObj,$Options);
	echo '===='.$Result = curl_exec($CurlObj);
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
// #########
// MAIN CODE
// #########

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

	$SessionKey = $Result["info"];

/*
echo '<br><br><br><br><br>Create Comment Thread';
	
	$userdata=array();
	$userdata["_key"] = APIKEY;
	$userdata["_session"] = $SessionKey;
	$userdata["_op"] = "cmnt_newthrd";
	$userdata["_user"] = "1";
	$userdata["user_id"] = "1";
	$userdata["parent_item"] = "1";
	$userdata["title"] = "test thread Surya";
	$Result = aib_request($userdata,"comments");
	
	echo '<pre>';
	print_r($userdata);
	echo '</pre>';
	if ($Result["status"] != "OK")
	{
		
		print("ERROR: Cannot list items; ".$Result["info"]."\n");
		//exit(0);
	}
	echo '<pre>';
	print_r($Result);
	echo '</pre>';
	
	*//////////6591
	
	echo '<br><br><br><br><br>List Comment Threads';
	
	$userdata=array();
	$userdata["_key"] = APIKEY;
	$userdata["_session"] = $SessionKey;
	$userdata["_op"] = "cmnt_lthread";
	$userdata["_user"] = "1";
	$userdata["user_id"] = "1";
	$userdata["parent_item"] = "1";
	//$userdata["title"] = "test thread Surya";
	$Result = aib_request($userdata,"comments");
	
	echo '<pre>';
	print_r($userdata);
	echo '</pre>';
	if ($Result["status"] != "OK")
	{
		
		print("ERROR: Cannot list items; ".$Result["info"]."\n");
		//exit(0);
	}
	echo '<pre>';
	print_r($Result);
	echo '</pre>';
	
	echo '<br><br><br><br><br>Add Comment To Thread';
	
	$userdata=array();
	$userdata["_key"] = APIKEY;
	$userdata["_session"] = $SessionKey;
	$userdata["_op"] = "cmnt_addcmnt";
	$userdata["_user"] = "1";
	$userdata["user_id"] = "1";
	$userdata["parent_thread"] = "6591";
	$userdata["comment_title"] = "test comment_title";
	$userdata["comment_text"] = "test comment_text";
	
	//$userdata["title"] = "test thread Surya";
	$Result = aib_request($userdata,"comments");
	
	echo '<pre>';
	print_r($userdata);
	echo '</pre>';
	if ($Result["status"] != "OK")
	{
		
		print("ERROR: Cannot list items; ".$Result["info"]."\n");
		//exit(0);
	}
	echo '<pre>';
	print_r($Result);
	echo '</pre>';