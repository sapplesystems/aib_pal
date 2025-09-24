<?php
require_once dirname(__FILE__) . '/config/config.php';
$message = (array)json_decode(MESSAGE);
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
switch ($_REQUEST['mode']) {
    case 'authenticate_user':
        $responseData = array('status' => 'error', 'message' => 'Login fail');
        parse_str($_POST['formData'], $postData);
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'get_profile',
            "_user" => 1,
            "user_login" => ($postData['username'] != '') ? $postData['username'] : 'CodyFrance'
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            $_SESSION['aib']['session_key'] = $apiResponse['session'];
            $_SESSION['aib']['user_data'] = $apiResponse['info'];
            $responseData = array('status' => 'success', 'message' => 'Login successfully');
        }
        print json_encode($responseData);
        break;
    case 'login_user':
        parse_str($_POST['formData'], $postData);
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'login',
            '_user' => 1,
            "user_login" => $postData['username'],
            "user_pass" => $postData['password']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'users');
        $_SESSION['aib']['session_key'] = $apiResponse['session'];
        if ($apiResponse['status'] == 'OK') {
            $postDataLogin = array(
                "_key" => APIKEY,
                "_session" => $apiResponse['session'],
                "_op" => 'get_profile',
                "_user" => 1,
                "user_login" => $postData['username']
            );
            $apiResponse = aibServiceRequest($postDataLogin, 'users');
            if ($apiResponse['status'] == 'OK') {
                $_SESSION['aib']['session_key'] = $apiResponse['session'];
                $_SESSION['aib']['user_data'] = $apiResponse['info'];
            }
            $responseData = array('status' => 'success', 'message' => 'Login successfully');
        } else {
            $responseData = array('status' => 'error', 'message' => $apiResponse['info']);
        }
        print json_encode($responseData);
        break;
    case 'list_tree_items':
        $folderId = $_POST['folder_id'];
        $itemId = $_POST['itemId'];
        
        //Filter Options        
         $filter['state'] = !empty($_POST['state'])?$_POST['state']:"";
         $filter['county'] = !empty($_POST['county'])?$_POST['county']:"";
         $filter['city'] = !empty($_POST['city'])?$_POST['city']:"";
         $filter['zip'] = !empty($_POST['zip'])?$_POST['zip']:"";        
        //Filter Options
        
        $treeDataArray = getTreeData($folderId);
        
        $itemData = getItemData($folderId);
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folderId,
            "opt_get_files" => 'Y',
	    "opt_deref_links" =>'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        foreach ($apiResponse['info']['records'] as $key => $dataArray) {
            if($dataArray['item_type'] == 'AG'){
                $itemProperty = getItemProperty($dataArray['item_id']);
                $apiResponse['info']['records'][$key]['property_list'] = $itemProperty;
                if($itemProperty['status'] == 0){
                    unset($apiResponse['info']['records'][$key]);
                }
            }
            if($dataArray['item_type'] == 'CO'){
                $itemProperty = getItemProperty($dataArray['item_id']);                
                $apiResponse['info']['records'][$key]['co_property'] = $itemProperty;
            }
            if(strtolower($dataArray['item_title']) == 'advertisements'){
                unset($apiResponse['info']['records'][$key]);
            }
        }
        if ($itemData['item_type'] == 'RE') {
            $treeDataArray = getTreeData($folderId);
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $ThumbID = false;
                $PrimaryID = false;
                foreach ($dataArray["files"] as $FileRecord) {
                    if ($FileRecord["file_type"] == 'tn') {
                        $ThumbID = $FileRecord["file_id"];
                        $apiResponse['info']['records'][$key]['tn_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                    if ($FileRecord["file_type"] == 'pr') {
                        $PrimaryID = $FileRecord["file_id"];
                        $apiResponse['info']['records'][$key]['pr_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                }
            }
        }
        
        //Apply Filter Rules
          $apiResponse=applyUserFilter($apiResponse,$filter);
        //Apply Filter Rules
        //echo "<pre>";print_r($apiResponse);
        if ($apiResponse['status'] == 'OK' && $itemData['item_type'] != 'RE') {    
            include_once TEMPLATE_PATH . 'home-content.php';
        } else {
            $detailsPageData = array();            
            include_once TEMPLATE_PATH . 'details-description.php';
        }
        break;
    case 'register_new_user':
        parse_str($_POST['formData'], $postDataArray);
        $fieldsArray=array("phoneNumber","redactionsEmailAddress","reprintEmailAddress","contactEmailAddress",
           "websiteURL","physicalAddressLine1","mailingAddressLine1","physicalAddressLine2","mailingAddressLine2","physicalCity","mailingCity",
            "physicalState","mailingState","physicalZip","mailingZip","federalTaxIDNumber","sateTaxIDNumber","entityOrganization","entityOrganizationOther",
            "CEO","CEO_firstName","CEO_lastName","CEO_email","executiveDirector","executiveDirector_firstName","executiveDirector_lastName","executiveDirector_email",
            "precident","precident_firstName","precident_lastName","precident_email","otherExecutive","otherExecutive_firstName","otherExecutive_lastName",
            "otherExecutive_email","sameAsPhysicalAddress","boardOfDirectors","committees","society_state");
        $postRegArray=array();
        $count=3;
        foreach($fieldsArray as $fields){
            $postRegArray['propname_'.$count]=$fields;
            $postRegArray['propval_'.$count]=(string)$postDataArray[$fields];
            $count++;
        }
        $postDataArray['name']=$postDataArray['title']. " ".$postDataArray['firstName']." ".$postDataArray['lastName'];
        $userTypeArray = array('society'=>'A','municipality'=>'A','publisher'=>'A','user'=>'X');
        $responseData = array('status' => 'error', 'message' => 'All fields are required.');
        if (!empty($postDataArray)) {
            if($postDataArray['user_type'] == 'A'){//society
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user'=>1,
                    '_op'=>'create_item',
                    '_session' => $sessionKey,
                    'parent' =>1,
                    'item_title'=>$postDataArray['society_name'],
                    'item_class'=>'ag',
                    'item_owner_id' => 1,
                    'item_owner_group' => 1,
                    'opt_allow_dup' => 'N',
                );
                $apiResponseAG = aibServiceRequest($postDataItem,'browse');
                if($apiResponseAG['status'] == 'OK'){
                    $newArchiveId = $apiResponseAG['info'];
                    $apiRequestData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_op" => "create_profile",
                        "_user" => 1,
                        "user_login" => $postDataArray['register_username'],
                        "user_type" => $postDataArray['user_type'],
                        "user_pass" => $postDataArray['register_user_password'],
                        "user_title" => $postDataArray['name'],
                        "user_top_folder" => $newArchiveId,
                        "user_primary_group" => "-1"
                    );
                    $apiResponse = aibServiceRequest($apiRequestData, 'users');
                    $apiRequestDataItem = array(
                        '_key' => APIKEY,
                        '_user'=>1,
                        '_op'=>'set_item_prop',
                        '_session' => $sessionKey,
                        'obj_id'=> $newArchiveId,
                        'propname_1'=>'status',
                        'propval_1'=>0,
                        'propname_2'=>'archive_user_id',
                        'propval_2'=>$apiResponse['info']
                    );
                    $apiRequestDataItem=array_merge($apiRequestDataItem,$postRegArray);
                    $apiResponse = aibServiceRequest($apiRequestDataItem,'browse');
                    if($apiResponse['status'] == 'OK'){
                        $responseData = array('status' => 'success', 'message' => 'Your profile created successfully, Please login here.');
                    }else{
                        $responseData = array('status' => 'error', 'message' => 'Something went wrong on API side, Please try again.');
                    }
                }
            }
        }
        print json_encode($responseData);
        break;
    case 'register_normal_user':
        parse_str($_POST['formData'], $postDataArray);
        if(!empty($postDataArray)){
            $apiRequestData = array(
                "_key"               => APIKEY,
                "_session"           => $sessionKey,
                "_op"                => "create_profile",
                "_user"              => 1,
                "user_login"         => $postDataArray['register_username'],
                "user_type"          => 'X',
                "user_pass"          => $postDataArray['register_user_password'],
                "user_title"         => $postDataArray['name'],
                "user_top_folder"    => 2,
                "user_primary_group" => "-1"
            );
            $apiResponse = aibServiceRequest($apiRequestData, 'users');
            if($apiResponse['status'] == 'OK'){
                $responseData = array('status'=>'success','message'=>'Assistant created successfully');
            }else{
                $responseData = array('status'=>'error','message'=>$message[$apiResponse['info']]);
            }
        }
        print json_encode($responseData);
        break;
    case 'logout_user':
        unset($_SESSION);
        session_destroy();
        break;
    case 'get_tree_data':
        $folderId = $_POST['folder_id'];
        $treeDataArray = getTreeData($folderId);
        if (!empty($treeDataArray)) {
            include_once TEMPLATE_PATH . 'tree.php';
        }
        break;
    case 'get_archive_prop_details':
        $archive_id = $_POST['archive_id'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $archive_id
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'browse');
        $itemDetails = getItemData($archive_id);
        $itemDetails['prop_details'] = $apiResponse['info']['records'];
        $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $itemDetails['prop_details']['archive_logo_image'];
        $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $itemDetails['prop_details']['archive_header_image'];
        $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $itemDetails['prop_details']['archive_details_image'];
        $_SESSION['archive_request_reprint_text'] = $itemDetails['prop_details']['archive_request_reprint_text'];
        print json_encode($itemDetails);
        break;

    case 'get_advertisement':
        $folder_id = $_POST['folder_id'];
        $rootId = $_POST['rootId'];
        $itemDetail=getItemData($folder_id);
        if($itemDetail['item_type']=='RE'){
         $itemDetaildata= getTreeData($folder_id);  
         $folder_id=$itemDetaildata[count($itemDetaildata)-2]['item_id'];
        }
        getAdvertisementHetarchive($folder_id,$rootId);        
        break;
    case 'get_item_complete_details':
        $item_id = $_POST['item_id'];
        if($item_id){
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get",
                "obj_id" => $item_id,
                "opt_get_files" => 'Y',
                "opt_get_field" => 'Y'
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            if($apiResponse['status'] == 'OK'){
                include_once TEMPLATE_PATH.'item_description.php';
            }
        }
        break;
    case 'submit_request':
        parse_str($_POST['formData'], $postData);
        $item_id = $_POST['item_id'];
        switch($postData['request_type']){
            case 'RP':
                $name = $postData['cus_first_name'].' '.$postData['cus_last_name'];
                $email = $postData['cus_email'];
                $phone = '';
                $info  = array('item_link'=>$postData['cus_page_link'],'comment'=>$postData['cus_comments'],'page_used'=>$postData['cus_page_used']);
                break;
            case 'RD':
                $name = $postData['first_name'].' '.$postData['last_name'];
                $email = $postData['email'];
                $phone = $postData['phone_number'];
                $info  = array('item_link'=>$postData['article_link'],'comment'=>$postData['comments']);
                $string = $postData['article_link'];
                $stringArray = explode('?', $string);
                if(strpos($stringArray[1],'&')){
                    $subStringArray = explode('&',$stringArray[1]);
                    $itemArray = explode('=',$subStringArray[0]);
                    $item_id = $itemArray[1];
                }else{
                    $subStringArray = explode('=',$stringArray[1]);
                    $item_id = $subStringArray[1];
                }
                break;
            case 'CT':
                $name = $postData['contact_first_name'].' '.$postData['contact_last_name'];
                $email = $postData['contact_email'];
                $phone = '';
                $info  = array('item_link'=>$postData['contact_subject'],'comment'=>$postData['contact_comments']);
                break;
        }
        $info['status'] = 0;
        $responseArray = ['status'=>'error','message'=>'All fields are required.'];
        $user_ip_address = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?: getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
        if(!empty($postData) && $item_id!='' ){
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "req_store",
                "req_type" => $postData['request_type'],
                "req_name" => $name,
                "req_phone"=> $phone,
                "req_email" => $email,
                "req_ipaddr" => $user_ip_address,
                "req_info" => json_encode($info),
                "req_item" => $item_id
            );
            $apiResponse = aibServiceRequest($postData, 'custreq');
            $responseArray = ['status'=>'error','message'=>'Some things went wrong! Please try again.'];
            if($apiResponse['status'] == 'OK'){
                $responseArray = ['status'=>'success','message'=>'We have accepted your request, Will back to you shortly.'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'get_all_archive' :
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list", 
            "parent" => 1
        );
        $apiResponse = aibServiceRequest($postData,'browse');
        foreach($apiResponse['info']['records'] as $key=>$archiveGroup){
            $archivePropertyDetails = getItemProperty($archiveGroup['item_id']);
            if($archivePropertyDetails['status'] != 1){
                unset($apiResponse['info']['records'][$key]);
                continue;
            }
            $postItemData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list", 
                "parent" => $archiveGroup['item_id']
            );
            $apiResponseItemData = aibServiceRequest($postItemData,'browse');
            $apiResponse['info']['records'][$key]['archive'] = $apiResponseItemData['info']['records'];
        }
        $requestType = 'ar';
        include_once TEMPLATE_PATH.'item_listing.php';
        break;
    case 'search_data':
        $search_text = $_POST['search_text'];
        $archive_id  = $_POST['archive_id'];
        $archive_type= $_POST['archive_type'];
        $current_page = $_POST['current_page'];
        $record_per_page = $_POST['record_per_page'];
        if($search_text && $archive_id && $archive_type){
            $searchPostData = array(
                "_key" => APIKEY,
                "_session"=>time()-100,
                "phrase"=>$search_text,
                "pagenum"=>$current_page,
                "perpage"=> $record_per_page,
                "_indexcfg"=>$archive_type.'_'.$archive_id
            );
            $apiResponse = aibSearchRequest($searchPostData);
        }
        if(!empty($apiResponse['resultset']['doc'])){
            foreach($apiResponse['resultset']['doc'] as $key=>$searchDataArray){ 
                list($type, $title) = explode(":", $searchDataArray['title']);
                if($type == 'File'){
                   $itemParents = getTreeData($searchDataArray['uri']);
                   $parentDetails = array_slice($itemParents, -2, 1);
                   $apiResponse['resultset']['doc'][$key]['parent_id'] = $parentDetails[0]['item_id'];
                }
            }
        }
        include_once TEMPLATE_PATH.'search_result_data.php';
        break;
    case 'get_item_archive_group_details':
        $record_id = $_POST['item_id'];
        $archivePropertyDetails = array();
        if($record_id){
            $itemParents = getTreeData($record_id);
            $archive_id  = $itemParents[1]['item_id'];
            $archivePropertyDetails = getItemProperty($archive_id);
        }
        print json_encode($archivePropertyDetails);
        break;
    case 'get_item_details':
        $item_id = $_POST['item_id'];
        if($item_id){
            $itemDetails = getItemData($item_id);
            print json_encode($itemDetails);
        }
        break;
        
    case 'check_duplicate_item':
        $society_state = $_POST['society_state'];
        $society_name = $_POST['society_name'];
        $responseData = array('status'=>'error','message'=>'Item not deleted.');
        if($society_state!="" && $society_name!=""){
             $postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" =>1,// $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "list", 
                    "parent" => 1,  
                    "opt_get_property" => 'Y'
                );
              $apiResponse = aibServiceRequest($postData,'browse');
              $records=$apiResponse['info']['records'];
              foreach($records as $record){
                  if(trim($record['item_title'])==trim($society_name)  && trim($record['properties']['society_state'])==trim($society_state)){
                     echo "false";
                     exit;
                  }
              }              
        }
        echo "true";        
        break; 
    case  'get_archive_search_items':
            $responseData = array('status'=>'error','message'=>'Item not deleted.');
            $postData = array(
                   "_key" => APIKEY,
                   "_session" => $sessionKey,
                   "_user" =>1,
                   "_op" => "list", 
                   "parent" => 1,  
                   "opt_get_property" => 'N'
               );
             $apiResponse = aibServiceRequest($postData,'browse');
             $records=$apiResponse['info']['records'];
             
             $location=array();
             $stateList=array();
             $countyList=array();
             $cityList=array();             
             $zipList=array();
             foreach($records as $record){
                 $record['fullProp']=getItemProperty($record['item_id']);
                 if($record['fullProp']['status']==1){
                    $state=ucwords(strtolower($record['fullProp']['archive_display_state']));
                    if(!empty($state)){
                        $stateList[$state]=$state;
                    }                 
                    $city=ucwords(strtolower($record['fullProp']['archive_display_city']));
                    if(!empty($city))
                       $cityList[$city]=$city;

                    $zip=ucwords(strtolower($record['fullProp']['archive_display_zip']));

                    if(!empty($zip))
                       $zipList[$zip]=$zip;
                    
                    $county=ucwords(strtolower($record['fullProp']['archive_display_county']));

                    if(!empty($county))
                       $countyList[$county]=$county;

                    $location['State']=$stateList;
                    $location['City']=$cityList;
                    $location['Zip']=$zipList;
                    $location['County']=$countyList;
                 }
             }
            print json_encode($location);
           
        break;
}

/*
 * @Author: Sapple Systems
 * @method: getItemData()
 * @params: $folderId(int)
 * @return: (array)$apiResponse
 */

function getItemData($folderId = '') {
    if ($folderId) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get",
            "obj_id" => $folderId,
            "opt_get_field" => 'Y',
            "opt_get_files" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');

        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'][0];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: getTreeData()
 * @params: $folderId(int)
 * @return: (array)$apiResponse
 */

function getTreeData($folderId = '') {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_path",
            "obj_id" => $folderId
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

function aibSearchRequest($postData){
    $curlObj = curl_init();
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => 'http://develop.archiveinabox.com:80/cgi-bin/estsearchutil',
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

/*
 * @Author: Sapple Systems
 * @method: aibServiceRequest()
 * @params1: $postData(array)
 * @params2: $fileName(string)
 * @return: (array)$apiResponse
 */

function aibServiceRequest($postData, $fileName) {
    $curlObj = curl_init();
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => APIURL . $fileName . ".php",
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
    if(isset($outData['info']) && $outData['info'] == 'EXPIRED'){
        unset($_SESSION);
        session_destroy();
        header('Location: home.php');
        exit;
    }else{
        return($outData);
    }
}


function get_advertisement($folder_id) {

    $dataTree = getTreeData($folder_id);
    $folder_id = $dataTree[1]['item_id'];
           
    //print_r($dataTree);
    $sessionKey = $_SESSION['aib']['session_key'];
    if (!(isset($_SESSION['adverstisement'][$folder_id]) and count($_SESSION['adverstisement'][$folder_id]))) {
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folder_id,
            "opt_get_files" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');


        foreach ($apiResponse['info']['records'] as $key => $dataArray) {
            if ($dataArray['item_type'] == 'AR') {

                $apiRequestDataNewPro = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'get_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $dataArray['item_id']
                );
                $apiResponsePro = aibServiceRequest($apiRequestDataNewPro, 'browse');
                if (isset($apiResponsePro['info']['records']['type']) and $apiResponsePro['info']['records']['type'] == 'A') {
                    $postData1 = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "list",
                        "parent" => $dataArray['item_id'],
                        "opt_get_files" => 'Y'
                    );
                    $apiResponse1 = aibServiceRequest($postData1, 'browse');
                    //	echo '<pre>';print_R($apiResponse1);
                    if (isset($apiResponse1['info']['records'][0]['item_type']) and $apiResponse1['info']['records'][0]['item_type'] == 'CO') {
                        $postData2 = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "list",
                            "parent" => $apiResponse1['info']['records'][0]['item_id'],
                            "opt_get_files" => 'Y'
                        );
                        $apiResponse2 = aibServiceRequest($postData2, 'browse');
                        //echo '<pre>';print_R($apiResponse2);
                        if (isset($apiResponse2['info']['records'][0]['item_type']) and $apiResponse2['info']['records'][0]['item_type'] == 'SG') {
                            $postData3 = array(
                                "_key" => APIKEY,
                                "_session" => $sessionKey,
                                "_user" => 1,
                                "_op" => "list",
                                "parent" => $apiResponse2['info']['records'][0]['item_id'],
                                "opt_get_files" => 'Y'
                            );
                            $apiResponse3 = aibServiceRequest($postData3, 'browse');
                            //echo '<pre>';print_R($apiResponse3);
                            if ($apiResponse3['status'] == 'OK') {
                                foreach ($apiResponse3['info']['records'] as $adItem) {
                                    $postData4 = array(
                                        "_key" => APIKEY,
                                        "_session" => $sessionKey,
                                        "_user" => 1,
                                        "_op" => "list",
                                        "parent" => $adItem['item_id'],
                                        "opt_get_files" => 'Y'
                                    );
                                    $apiResponse4 = aibServiceRequest($postData4, 'browse');
                                    //echo '<pre>';print_R($apiResponse4);
                                    $itemDetail = getItemDetailsWithProp($adItem['item_id']);
                                    //echo '<pre>';print_R($itemDetail);
                                    $itemArray = array();
                                    if ($apiResponse4['status'] == 'OK') {
                                        foreach ($apiResponse4['info']['records'] as $itemdeatil) {
                                            if ($itemdeatil['files'][0]['file_type'] == 'pr')
                                                $itemArray['id'] = $itemdeatil['files'][0]['file_id'];
                                            else if ($itemdeatil['files'][1]['file_type'] == 'pr')
                                                $itemArray['id'] = $itemdeatil['files'][1]['file_id'];
                                            else if ($itemdeatil['files'][2]['file_type'] == 'pr')
                                                $itemArray['id'] = $itemdeatil['files'][2]['file_id'];
                                        }
                                    }
                                    if (isset($itemDetail['fields'][0]['field_title']) and $itemDetail['fields'][0]['field_title'] == 'URL') {
                                        $itemArray['url'] = $itemDetail['fields'][0]['field_value'];
                                    }

                                    $advertisementArray[] = $itemArray;
                                }
                            }
                        }
                    }
                }
            }
        }
        //echo "<pre>";print_r($advertisementArray); exit;
        $_SESSION['adverstisement'][$folder_id] = $advertisementArray;
    }
    if ((isset($_SESSION['adverstisement'][$folder_id]) and count($_SESSION['adverstisement'][$folder_id]))) {
        foreach ($_SESSION['adverstisement'][$folder_id] as $add) {
            if($add['id'] != ''){
                if (isset($add['url']) and $add['url'] != '') {
                    echo '<a href="' . $add['url'] . '" target="_blank"> <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /></a><br><br>';
                } else {
                    echo ' <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /><br><br>';
                }
            }
        }
    }
}

function getItemListRec($folder_id){
    $sessionKey = $_SESSION['aib']['session_key'];
    $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folder_id,
            "opt_get_files" => 'Y'
        );
         $apiResponse = aibServiceRequest($postData, 'browse');
         $records = $apiResponse['info']['records'];
         //print_r($records);
         if(isset($records[0]['item_type']) && $records[0]['item_type']=="RE"){
                return $records = $apiResponse['info']['records'];
         }else{
             if(isset($records[0]['item_id']) && $records[0]['item_id']>0){
                 return getItemListRec($records[0]['item_id']);
             }else{
                 return $records;
             }
         }
}


function getAdvertisementHetarchive($folder_id,$rootId) {
    $depth=array("AR","CO","SG","RE","IT");
    $depthLength=count($depth);
    
    $sessionKey = $_SESSION['aib']['session_key'];
    //if (!(isset($_SESSION['adverstisement'][$folder_id]) && count($_SESSION['adverstisement'][$folder_id]))) {
       $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folder_id,
            "opt_get_files" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $records = $apiResponse['info']['records'];
        
         //echo "<pre>";print_r($records); exit;
        foreach($records as $key => $dataArray){           
            if(isset($dataArray['item_title']) && ($dataArray['item_title']=='Advertisements' || $dataArray['item_title']=='advertisements')){
               $item_type=trim($dataArray['item_type']);
               $index=array_search($item_type, $depth);
                if($index<3){
                    $addrecords=getItemListRec($dataArray['item_id']);                    
                }else{
                    $addrecords=$dataArray;
                }
            }  
        }
    //echo "<pre>";
    //print_r($addrecords);exit;
        foreach ($addrecords as $adItem) {
                $postData4 = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => 1,
                    "_op" => "list",
                    "parent" => $adItem['item_id'],
                    "opt_get_files" => 'Y'
                );
                $apiResponse4 = aibServiceRequest($postData4, 'browse');
                //echo '<pre>';print_R($apiResponse4); 
                $itemDetail = getItemDetailsWithProp($adItem['item_id']);
                //echo '<pre>';print_R($itemDetail);
                $itemArray = array();
                if ($apiResponse4['status'] == 'OK') {
                    $apiRecords=$apiResponse4['info']['records'];
                    foreach ($apiRecords as $itemdeatil) {
                       
                        if ($itemdeatil['files'][0]['file_type'] == 'pr')
                            $itemArray['id'] = $itemdeatil['files'][0]['file_id'];
                        else if ($itemdeatil['files'][1]['file_type'] == 'pr')
                            $itemArray['id'] = $itemdeatil['files'][1]['file_id'];
                        else if ($itemdeatil['files'][2]['file_type'] == 'pr')
                            $itemArray['id'] = $itemdeatil['files'][2]['file_id'];
                    }
                }
                
                if (isset($itemDetail['prop_details']['_url']) && $itemDetail['prop_details']['_url'] != '') {
                    if (false === strpos($itemDetail['prop_details']['_url'], '://')) {
                        $itemDetail['prop_details']['_url'] = 'http://' . $itemDetail['prop_details']['_url'];
                    }
                    $itemArray['url'] = $itemDetail['prop_details']['_url'];
                }
                $advertisementArray[] = $itemArray;
            }
            
            $_SESSION['adverstisement'][$folder_id] = $advertisementArray;
      //}//checksession
            randomAdvertisement($_SESSION['adverstisement'],$folder_id);
            
            /*if ((isset($_SESSION['adverstisement'][$folder_id]) && count($_SESSION['adverstisement'][$folder_id])>0)) {
                foreach ($_SESSION['adverstisement'][$folder_id] as $add) {
                    if (isset($add['url']) && $add['url'] != '') {
                        echo '<a href="' . $add['url'] . '" target="_blank"> <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /></a><br><br>';
                    } else {
                        echo ' <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /><br><br>';
                    }
                }
            }else{               
                //If No Addvertisment
                $itemParentArray=getTreeData($folder_id);
                
                $firstFolderId=$itemParentArray[1]['item_id'];
                if ((isset($_SESSION['adverstisement'][$firstFolderId]) && count($_SESSION['adverstisement'][$firstFolderId]))) {
                    foreach ($_SESSION['adverstisement'][$firstFolderId] as $add) {
                        if (isset($add['url']) && $add['url'] != '') {
                            echo '<a href="' . $add['url'] . '" target="_blank"> <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /></a><br><br>';
                        } else {
                            echo ' <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /><br><br>';
                        }
                    }
                }
           }*/
            
}

function randomAdvertisement($ad,$folder_id){
    $maxLength=3;
    $itemParentArray=getTreeData($folder_id);
    $firstFolderId=$itemParentArray[1]['item_id'];
    
    if ((!isset($ad[$folder_id]) || count($ad[$folder_id])<=0)) { 
        $ad[$folder_id]=array();
    }
    if ((!isset($ad[$firstFolderId]) || count($ad[$firstFolderId])<=0)) { 
        $ad[$firstFolderId]=array();
    }
    $adList=array_merge($ad[$folder_id],$ad[$firstFolderId]);
    //echo "<pre>";print_r($adList);
    //$adList= array_unique($adList);
    shuffle($adList);
    $count=1;
    $storage="";
    foreach ($adList as $add) {
        if($storage!="" && $storage ==$add['id']){
            break;
        }else{
            $storage=$add['id'];
        }
        if($count>$maxLength) break;
        if (isset($add['url']) && $add['url'] != '') {
            echo '<a href="' . $add['url'] . '" target="_blank"> <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /></a><br><br>';
        } else {
            echo ' <img style="" src="http://develop.archiveinabox.com/get_thumb.php?id=' . $add['id'] . '" alt="" /><br><br>';
        }
        $count++;
    }
}

function getItemProperty($item_id = null){
    if($item_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user'=>1,
            '_op'=>'get_item_prop',
            '_session' => $sessionKey,
            'obj_id'=> $item_id
        );
        $apiResponse = aibServiceRequest($apiRequestData,'browse');
        $itemDetails = $apiResponse['info']['records'];
        return $itemDetails;
    }
}

function getItemDetailsWithProp($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'browse');
        $itemDetails = getItemData($item_id);
        $itemDetails['prop_details'] = $apiResponse['info']['records'];
        return $itemDetails;
    } else {
        return false;
    }
}

function applyUserFilter($apiResponse,$filter){
    if(!empty($filter)){
        foreach($apiResponse['info']['records'] as $key=>$apiResponseVal){
            
            $matchStatus=true;
            $state= ucwords(strtolower($apiResponseVal['property_list']['archive_display_state']));
            if(!empty($filter['state']) && $filter['state']!=$state){
            $matchStatus=false;
            }
            $county= ucwords(strtolower($apiResponseVal['property_list']['archive_display_county']));
            if(!empty($filter['county']) && $filter['county']!=$county){
            $matchStatus=false;
            }
            $city= ucwords(strtolower($apiResponseVal['property_list']['archive_display_city']));
            if(!empty($filter['city']) && $filter['city']!=$city){ 
                $matchStatus=false;
            }
            
            if(!empty($filter['zip']) && $filter['zip']!=$apiResponseVal['property_list']['archive_display_zip']){ 
                $matchStatus=false;
            }
            if(!$matchStatus){
                unset($apiResponse['info']['records'][$key]);
            }
        }
    }
    return $apiResponse;
}
