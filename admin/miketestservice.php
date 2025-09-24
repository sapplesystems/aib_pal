<?php
define('APIKEY',"87fc0d6d9689d84ab48f583175f9522dg");
require_once dirname(__FILE__) . '/config/config.php';
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
function aibSearchRequest($postData){
    $curlObj = curl_init();
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => AIB_SEARCH_URL,
        CURLOPT_FRESH_CONNECT => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 0,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_POSTFIELDS => http_build_query($postData)
    );
    curl_setopt_array($curlObj, $options);
    $result = curl_exec($curlObj);
    $resultFinal = str_replace('<status><status_value>OK</status_value></status>', '', $result);
    require_once 'xmlToArray.php';
    $resultDataArray = xml2array($resultFinal);
    curl_close($curlObj);
    return $resultDataArray;
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

	$sessionKey = $Result["info"];
$search_filter = array(
	array('name'=>'aibftype',"value"=>"rec"),
	array('name'=>'aibftype',"value"=>"sg"),
	array('name'=>'aibftype',"value"=>"col"),
	array('name'=>'aibftype',"value"=>"IT"),
	array('name'=>'is_advertisements',"value"=>"Y"),
	array('name'=>'aib:private',"value"=>"Y"),
	array('name'=>'link_class',"value"=>"public"),
	array('name'=>'aibftype',"value"=>"scrpbkent"),
	array('name'=>'scrapbook_type','value'=>'private'),
	);

		$postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "opt_sort" => 'ID',
            "parent" =>223,
            "opt_get_files" => 'Y',
	"opt_deref_links" =>'Y',
            "opt_get_property" => 'Y' ,
            "opt_get_link_source_properties" => 'Y' ,
            "opt_get_long_prop" => 'Y',
			"opt_get_prop_count" => 'Y',
			"opt_prop_count_set" => json_encode($search_filter),
			"opt_get_root_folder"=>'Y',
		"opt_follow_links" => "Y",
        );
		echo "<pre>"; print_r($postData); echo "</pre>";
        $apiResponse = aib_request($postData, 'browse');
		echo "<pre>"; print_r($apiResponse); echo "</pre>"; exit;












/*

 $search_filter = array(array('name'=>'aibftype',"value"=>"rec"),array('name'=>'aibftype',"value"=>"sg"),array('name'=>'aibftype',"value"=>"col"));	
         $postData = array( 
            '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'delete_item',
                '_session' => $sessionKey,
                'obj_id' => 12322353235
        );
        $apiResponse = aib_request($postData, 'browse');
		echo '<pre>';
	print_r($postData );
	print_r($apiResponse ); 
	
   /* echo '<pre>';
	print_r($postData );
	print_r($apiResponse );/*
	 $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get",
                "obj_id" =>23,
                "opt_get_files" => 'Y',
                "opt_get_field" => 'Y'
            );
            $apiResponse = aib_request($postData, 'browse'); 
	 echo '<pre>';
	print_r($postData );
	print_r($apiResponse );
	 $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get",
                "obj_id" =>355,
                "opt_get_files" => 'Y',
                "opt_get_field" => 'Y'
            );
            $apiResponse = aib_request($postData, 'browse'); 
	 echo '<pre>';
	print_r($postData );
	print_r($apiResponse );
	/*$searchPostData = array(
                "_key" => APIKEY,
                "_session"=>time()-100,
                "phrase"=>"New",
                "pagenum"=>1,
                "perpage"=> 100,
                "_indexcfg"=>'ar_19'
            );
		
		 $apiResponse = aibSearchRequest($searchPostData);
		 
		  echo '<pre>';
	print_r($searchPostData );
	print_r($apiResponse );
	
	$searchPostData = array(
                "_key" => APIKEY,
                "_session"=>time()-100,
                "phrase"=>"test",
                "pagenum"=>1,
                "perpage"=> 100,
                "_indexcfg"=>'ar_114'
            );
		
		 $apiResponse = aibSearchRequest($searchPostData);
		 
		  echo '<pre>';
	print_r($searchPostData );
	print_r($apiResponse ); 
	*/
	 /*$postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "send", 
            "_user" => 1,
            "to" => 'pphukan@sapple.co.in',
            "from" => 'support@archiveinabox.com',
            "reply" =>'no-reply@archiveinabox.com',
            "subject" => 'test mail',
            "body" => 'test mail',
            "is_html" => 'Y'
        );
        $apiResponse = aib_request($postData,'email');
		  echo '<pre>';
	print_r($postData );
	print_r($apiResponse ); 
?>
