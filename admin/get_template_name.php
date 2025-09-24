<?php

//$folder_id = $_REQUEST['folder-id'];
unset($_SESSION['tree_data']);
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
if (!isset($_SESSION['aib']['session_key'])) {
    $postData = array(
        "_id" => APIUSER,
        "_key" => APIKEY
    );
    $apiResponse = aibServiceRequest($postData, 'session');
    if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
        $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
    }
} else {
    $sessionKey = $_SESSION['aib']['session_key'];
}
$parentDetails = array();
if ($folder_id != '' && $sessionKey != '') {
    if (isset($_SESSION['tree_data'][$folder_id])) {
        $parentDetails = $_SESSION['tree_data'][$folder_id];
    } else {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_path",
            "obj_id" => $folder_id,
            "opt_get_property" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            $_SESSION['tree_data'][$folder_id] = $apiResponse['info']['records'];
            $parentDetails = $apiResponse['info']['records'];
        }
    }
}

$themeName = isset($parentDetails[1]['properties']['details_page_design']) ? $parentDetails[1]['properties']['details_page_design'] : '';
$societyTemp = isset($parentDetails[1]['properties']['custom_template']) ? $parentDetails[1]['properties']['custom_template'] : '';
if ($_SESSION['aib']['user_data']['user_type'] && ($_SESSION['aib']['user_data']['user_type'] == 'R' || $_SESSION['aib']['user_data']['user_type'] == 'A') && $_REQUEST['society_template']) {
    $societyTemp = $_REQUEST['society_template'];
}
?>