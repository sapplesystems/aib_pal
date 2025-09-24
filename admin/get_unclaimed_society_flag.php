<?php

//$folder_id = $_REQUEST['folder-id'];
error_reporting(E_ALL);
if (!function_exists('aibServiceRequest')) {

    function aibServiceRequest($postData, $fileName) {
        $curlObj = curl_init();
        $options = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => AIB_SERVICE_URL . '/api/' . $fileName . ".php",
            CURLOPT_FRESH_CONNECT => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 0,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_POSTFIELDS => http_build_query($postData)
        );
        curl_setopt_array($curlObj, $options);
        $result = curl_exec($curlObj);
        if ($result == false) {
            $outData = array("status" => "ERROR", "info" => curl_error($curlObj));
        } else {
            $outData = json_decode($result, true);
        }
        curl_close($curlObj);
        return ($outData);
    }

}
$is_unclaimed_society = '';
if ($folder_id != '' && $sessionKey != '') {
	
	$postData = array(
		"_key" => APIKEY,
		"_session" => $sessionKey,
		"_user" => 1,
		"_op" => "get_path",
		"obj_id" => $folder_id
	);
	// Service request to get item tree data        
	$apiResponse = aibServiceRequest($postData, 'browse');
	
	if(isset( $apiResponse['info']['records'][1]['item_id']))
	{
		$folder_id_parent = $apiResponse['info']['records'][1]['item_id'];
		
		$postData2 = array(
			"_key" => APIKEY,
			"_session" => $sessionKey,
			"_user" => 1,
			"_op" => "get",
			"obj_id" => $folder_id_parent,
			"opt_get_property" => 'Y'
		);
		
		$society_unclaimed = aibServiceRequest($postData2, 'browse');
		if ($society_unclaimed['info']['records'][0]['properties']['society_for_claim'] && $society_unclaimed['info']['records'][0]['properties']['society_for_claim'] == '1') {
			$is_unclaimed_society = '1';
		}
	}
}
?>