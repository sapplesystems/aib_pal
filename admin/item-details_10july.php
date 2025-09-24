<?php 
    require_once dirname(__FILE__) . '/config/config.php';
    $folderId = $_REQUEST['folder_id'];
    unset($_SESSION['tree_data']);
    if(!function_exists('aibServiceRequest')){
        function aibServiceRequest($postData, $fileName) {
            $curlObj = curl_init();
            $options = array(
                CURLOPT_POST => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_URL => AIB_SERVICE_URL.'/api/' . $fileName . ".php",
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
    if (!isset($_SESSION['aib']['session_key'])){
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );
        $apiResponse = aibServiceRequest($postData, 'session');
        if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
            $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
        }
    }else{
        $sessionKey = $_SESSION['aib']['session_key'];
    }
    $parentDetails = array();
    if ($folderId != '' && $sessionKey != '') {
        if(isset($_SESSION['tree_data'][$folderId])){
            $parentDetails = $_SESSION['tree_data'][$folderId];
        }else{
            $sessionKey = $_SESSION['aib']['session_key'];      
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get_path",
                "obj_id" => $folderId,
                "opt_get_property" => 'Y'
            );       
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK') {
                $_SESSION['tree_data'][$folderId] = $apiResponse['info']['records'];
                $parentDetails = $apiResponse['info']['records'];
            }
        } 
    }
    $themeName = isset($parentDetails[1]['properties']['details_page_design']) ? $parentDetails[1]['properties']['details_page_design'] : '';
    //$themeName = 'custom1';
   /* if($themeName == 'custom'){
        include_once 'details.php';
    }elseif($themeName == 'custom1'){
        include_once 'details_custom.php';
    }else{
        include_once 'details_default.php';
    }*/
	include_once 'details_default.php';
?>