<?php

session_start();
require_once 'config/config.php';
ini_set('display_errors', 0);
$message = (array) json_decode(MESSAGE);

function _recaptchaSiteVerify($captcha_response){
	$SecurityOk = true;
						// Create form fields
	$LocalPostData = http_build_query(array(
		"secret" => RC_SECRET_KEY,
		"response" => $captcha_response
	));
						// Set up HTTP options and encapsulate form data
	$LocalOpts = array("http" =>
		array(
			"method" => "POST",
			"header" => "Content-type: application/x-www-form-urlencoded",
			"content" => $LocalPostData
		));
						// Create stream context using options
	$LocalStreamContext = stream_context_create($LocalOpts);
						// Request data
	// Re-captcha response processing
	if ($LocalStreamContext != false)
	{
		$LocalResult = file_get_contents("https://www.google.com/recaptcha/api/siteverify",false,$LocalStreamContext);
							// If there was data returned, check
		if ($LocalResult != false)
		{
								// Decode security string to associative array
			$SecurityInfo = json_decode($LocalResult,true);
								// If the "success" entry is FALSE, it means the verification failed.  Set
								// error message and set flag so the form is reloaded.
			if (isset($SecurityInfo["success"]) == true)
			{
				if ($SecurityInfo["success"] == false)
				{
					$SecurityOk = false;
					$result = array('status' => 'error', 'message' => 'You are Robot!');
				}
				else
				{
					$SecurityOk = true;
					$result = array('status' => 'success', 'message' => 'success');
				}
			}
			else
			{
				$SecurityOk = false;
				$result = array('status' => 'error', 'message' => 'You are Robot!');
			}
		}
		else
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
		}
	}
	else
	{
		$SecurityOk = false;
		$result = array('status' => 'error', 'message' => 'You are Robot!');
	}
	// If security is still ok, then check for email addresses to block on
						// Decline specific sets of addresses
	while(true)
	{
		if ($SecurityOk === false)
		{
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
							// Anyone using a .ru domain (Russia)
		if (preg_match("/[\.][Rr][Uu]$/",$EmailAddress) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
		if (preg_match("/[\.][Ss][Uu]$/",$EmailAddress) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
		$SecurityOk = true;
		$result = array('status' => 'success', 'message' => 'success');
		break;
	}
	// Block on field content
						// Block based on comment content
	$LowerComment = strtolower($Comments);
	while(true)
	{
		if ($SecurityOk === false)
		{
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
							// Keywords
		if (preg_match("/payday[^a-z0-9]+loan/",$LowerComment) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
		if (preg_match("/bad[^a-z0-9]+credit/",$LowerComment) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
		if (preg_match("/website[^a-z0-9]+template/",$LowerComment) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
		if (preg_match("/html[^a-z0-9]+template/",$LowerComment) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
							// Embedded web links.  Pattern is "href" followed by zero or more non-equals sign characters,
							// followed by equals, followed by zero or more non-quote characters, followed by value, followed
							// by quotes.
		if (preg_match("/href[^=]*[\=][^\"\']*[\'\"][^\'\"]+[\'\"]/",$Comments) == 1)
		{
			$SecurityOk = false;
			$result = array('status' => 'error', 'message' => 'You are Robot!');
			break;
		}
		$SecurityOk = true;
		$result = array('status' => 'success', 'message' => 'success');
		break;
	}
	return $result;
	// Error message
	if ($SecurityOk == false)
	{
		$result = array('status' => 'error', 'message' => 'You are Robot!');
		print("<p><b>There was an error confirming you're not a robot.  Please click ");
		$URL = "/contact_form.php?lastname=$LastName&firstname=$FirstName&email=$EmailAddress";
		$URL .= "&usage=$Usage&comments=$Comments";
		print("<a href=\"$URL\"><font color='red'>HERE</font></a> to return to the request form.</b>");
		print("</p>");
		exit(0);
	}
}

function recaptchaSiteVerify($captcha_response){
	$result = array();
	if (isset($captcha_response) && ! empty($captcha_response)) {
        $data = array(
            'secret' => RC_SECRET_KEY,
            'response' => $captcha_response
        );
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);
        if ($response == true) {
            $result = array('status' => 'success', 'message' => 'success');
        } else {
            $result = array('status' => 'error', 'message' => 'You are Robot!');
        }
    } else {
        $result = array('status' => 'error', 'message' => 'You are Robot!');
    }
	return $result;
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
switch ($_REQUEST['mode']) {
    case 'login_user':
        parse_str($_POST['formData'], $postData);
        $responseData = array('status' => 'error', 'message' => 'You are Robot!');
        $terms_condition_prop = getItemDetailsWithProp(1);
        $terms_condition = $terms_condition_prop['prop_details']['timestamp'];
        $termCondition = 'Y';
        $current_time = time();
        $time_diff = $current_time-$postData['timestamp_value'];  
	if($time_diff > TIMESTAMP_3 ){ 
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
            $apiUserProp = getUsersAllProperty($apiResponse['info']['user_id']);
            if ($apiResponse['info']['user_type'] == 'R') {
                $apiResponse['info']['user_top_folder'] = 1;
            }
            $userTopFolder = $apiResponse['info']['user_top_folder'];
            $userType = $apiResponse['info']['user_type'];
            if ($apiResponse['info']['user_type'] == 'U') {
                $archiveStatus = true;
            } else {
                $archiveStatus = checkArchiveStatus($userTopFolder, $userType);
            }
			 if(!in_array($apiUserProp['status'], $apiUserProp)){
              $setUserStatus = setUserProfileStatus($apiResponse['info']['user_id'],'a');
              if($setUserStatus){ $userStatus = 'a';}
            }
            if($apiUserProp['status'] == 'a'  || $userStatus == 'a'){
                if ($archiveStatus) {
                    $apiRequestDataNew = array(
                        '_key' => APIKEY,
                        '_user' => 1,
                        '_op' => 'get_item_prop',
                        '_session' => $apiResponse['session'],
                        'obj_id' => $apiResponse['info']['user_top_folder']
                    );
                    $apiResponseNew = aibServiceRequest($apiRequestDataNew, 'browse');
                    if ($apiResponseNew['status'] == 'OK') {
                        if (isset($apiResponseNew['info']['records']['archive_logo_image']) and $apiResponseNew['info']['records']['archive_logo_image'] != '') {
                            $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $apiResponseNew['info']['records']['archive_logo_image'];
                            $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $apiResponseNew['info']['records']['archive_header_image'];
                            $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $apiResponseNew['info']['records']['archive_details_image'];
                        }
                    }
                    if ($apiResponse['status'] == 'OK') {
                        $redirect_url = 'manage_my_archive.php';
						$itemTitleName = getItemData($apiResponse['info']['user_top_folder']);    
                        if($apiResponse['info']['user_type'] == 'U'){
                            $redirect_url = (isset($apiUserProp['profile_completed']) && $apiUserProp['profile_completed'] == 'yes') ? 'manage_my_archive.php' : 'public_user_details.php';
                        }
                        if($apiResponse['info']['user_type'] == 'S'){
                            $redirect_url = 'assistant_index.php';
                        }
                        if($apiResponse['info']['user_type'] == 'U' || $apiResponse['info']['user_type'] == 'S' || $apiResponse['info']['user_type'] == 'A'){
                             $usert_timestamp =  isset($apiUserProp['timestamp'])?$apiUserProp['timestamp']:'';
                            if($terms_condition > $usert_timestamp){$termCondition ='N';}
							
							$item_title = $itemTitleName['item_title'];
                        }
				
                        $_SESSION['aib']['session_key'] = $apiResponse['session'];
                        $_SESSION['aib']['user_data'] = $apiResponse['info'];
                        $_SESSION['aib']['user_data']['user_prop'] = $apiUserProp;
						$_SESSION['aib']['user_data']['item_title']=$item_title ;
                        $_SESSION['aib']['user_data']['terms_condition'] = $termCondition;
                    }
                    $responseData = array('status' => 'success', 'message' => 'Login successfully','redirect_url'=>$redirect_url);
                } else {
                    $responseData = array('status' => 'error', 'message' => 'Temporarly your archive group is deactivated.');
                }
            }else{
                $responseData = array('status' => 'error', 'message' => 'Your account is deactivated.');
            }
        } else {
            $responseData = array('status' => 'error', 'message' =>$message[$apiResponse['info']]);
        }
		}
        print json_encode($responseData);
        break;
    case 'add_archive_form_data':
        parse_str($_POST['formData'], $postData);
        $apiRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'create_item',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "parent" => $_SESSION['aib']['user_data']['user_top_folder'],
            "item_title" => $postData['group_title'],
            "item_type" => 'ar',
            "item_source" => 'I'
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'browse');
        break;
    case 'listing_archive_form_data':
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_usage',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "form_id" => 2
        );
        $apiResponse = aibServiceRequest($apiGetData, 'browse');
        break;
    case 'assistant_archive_item_list':
        $userId = $_SESSION['aib']['user_data']['user_type'];
        $folderId = $_SESSION['aib']['user_data']['user_top_folder'];
        if ($folderId == 1) {
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $folderId
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            foreach ($apiResponse['info']['records'] as $key => $archiveGroup) {
                $postItemData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "list",
                    "parent" => $archiveGroup['item_id']
                );
                $apiResponseItemData = aibServiceRequest($postItemData, 'browse');
                $apiResponse['info']['records'][$key]['archive'] = $apiResponseItemData['info']['records'];
            }
        } else {
            $itemDetails = getItemData($folderId);
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $folderId
            );
            $apiResponseItemData = aibServiceRequest($postData, 'browse');
            $apiResponse['info']['records'][0] = $itemDetails;
            $apiResponse['info']['records'][0]['archive'] = $apiResponseItemData['info']['records'];
        }
        $requestType = $_POST['type'];
        include 'item_listing.php';
        break;
    case 'get_parent_item_list':
        $folder_title = $_POST['type'];
        $folderId = $_POST['parent_id'];
        if ($folder_title == 'ar') {
            $itemDetails = getItemData($folderId);
            $parent_title = $itemDetails['item_title'];
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $folderId
            );
            $apiResponseItemData = aibServiceRequest($postData, 'browse');
        } else {
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $folderId
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            foreach ($apiResponse['info']['records'] as $key => $archiveGroup) {
                $postItemData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "list",
                    "parent" => $archiveGroup['item_id']
                );
                $apiResponseItemData = aibServiceRequest($postItemData, 'browse');
                $apiResponse['info']['records'][$key]['child'] = $apiResponseItemData['info']['records'];
            }
        } 
		include_once TEMPLATE_PATH . 'move_item_listing.php';
        break;
    case 'get_child_item_list':
        $id = $_POST['id'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $data = [];
        foreach ($apiResponse['info']['records'] as $key => $archiveGroup) {
            if ($archiveGroup['item_type'] == 'SG') {
                array_push($data, $archiveGroup);
            }
        }
        print json_encode($data);
        break;
    case 'move_item':
        $parentId = $_POST['parent_id'];
        $itemId = $_POST['item_id'];
        $postDataItem = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'move_item',
            '_session' => $sessionKey,
            'parent' => $parentId,
            'obj_id' => $itemId
        );
        $result = aibServiceRequest($postDataItem, "browse");
        $responseData = array('status' => 'error', 'message' => 'Something went wrong, Item not moved.');
        if ($result['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Item moved successfully.');
        }
        print json_encode($responseData);
        break;
    //Get the Assistant/Administrator/Public user list
    case 'assistant_list':
        //Find the user top folder(Access level), If not present in request get from loggedin user
        $topFolderId = (isset($_POST['parent_id']) && $_POST['parent_id'] != '') ? $_POST['parent_id'] : $_SESSION['aib']['user_data']['user_top_folder'];
        $userId = $_SESSION['aib']['user_data']['user_id'];
        //Check for request type S: Assistant, A: Administrator, U: Public user
        switch ($_POST['type']) {
            //Case for Assistant
            case 'S':
                //Get the details of user top folder
                $itemDetails = getItemData($topFolderId);
                $subAdminData = array();
                //Check if top folder is AG then find their all archive
                if ($itemDetails['item_type'] == 'AG') {
                    // Create requestData for getting AG childs
                    $postData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $userId,
                        "_op" => "list",
                        "parent" => $topFolderId
                    );
                    $apiResponse = aibServiceRequest($postData, 'browse');
                    //Get All assistant list assigned to AG directly 
                    $archiveGroupAssistant = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_op" => 'list_profiles',
                        "_user" => $userId,
                        "user_type" => 'S',
                        "user_top_folder" => $topFolderId,
                        "opt_include_sub" => 'Y'
                    );
                    $archiveGroupAssistantList = aibServiceRequest($archiveGroupAssistant, 'users');
                    $subAdminData = array_merge($subAdminData, $archiveGroupAssistantList['info']['records']);
                } else {
                    $apiResponse['info']['records'][0]['item_id'] = $topFolderId;
                }
                //Loop over all archive(AR) and find list of assistant assigned to AR
                if (!empty($apiResponse['info']['records'])) {
                    foreach ($apiResponse['info']['records'] as $archiveKey => $archiveData) {
                        // Create requestData for getting user profile
                        $apiGetData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_op" => 'list_profiles',
                            "_user" => $userId,
                            "user_type" => 'S',
                            "user_top_folder" => $archiveData['item_id'],
                            "opt_include_sub" => 'Y'
                        );
                        // Service request for get user profile
                        $apiResponse = aibServiceRequest($apiGetData, 'users');
                        $subAdminData = array_merge($subAdminData, $apiResponse['info']['records']);
                    }
                }
                $subAdminDataNew = array();
                //Find every assistant assigned but not completed items
                foreach ($subAdminData as $data) {
                    // Create requestData for getting user profile
                    $uncompleteRequestData = array(
                        '_key' => APIKEY,
                        '_user' => $userId,
                        '_op' => 'data_entry_waiting',
                        '_session' => $sessionKey,
                        'user_id' => $data['user_id']
                    );
                    // Service request for get uncomplete data
                    $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
                    if (trim($uncompleteResponse['status']) == 'OK') {
                        $data['Waiting'] = count($uncompleteResponse['info']['records']);
                    } else {
                        $data['Waiting'] = 0;
                    }
                    // Find every assistant's assigned completed items
                    $completeRequestData = array(
                        '_key' => APIKEY,
                        '_user' => $userId,
                        '_op' => 'data_entry_complete',
                        '_session' => $sessionKey,
                        'user_id' => $data['user_id']
                    );
                    // Service request for get complete data
                    $completeResponse = aibServiceRequest($completeRequestData, 'dataentry');
                    if ($completeResponse['status'] == 'OK') {
                        $data['Complete'] = count($completeResponse['info']['records']);
                    } else {
                        $data['Complete'] = 0;
                    }
                    $subAdminDataNew[] = $data;
                }
                print json_encode($subAdminDataNew);
                break;
            // Case for Administrator
            case 'A':
                // Get list of all users having user_type A(Administrator)
                $apiGetData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'list_profiles',
                    "_user" => $userId,
                    "user_type" => 'A',
                    "opt_include_sub" => 'Y',
                    "_prop"=>'Y'
                );
                // Service request for get Administrator list data
                $apiResponse = aibServiceRequest($apiGetData, 'users');
                $responseData = $apiResponse['info']['records'];
                // Get the Archive Group data
                $userListWithArchiveGroup = Array();
                foreach ($responseData as $key => $value) {
                    $archiveData = getItemData($value['user_top_folder']);
                    if (!empty($archiveData)) {
                        $value['item_title'] = $archiveData['item_title'];
                        $value['user_pro_type'] = $value['_properties']['type'];
                        $userListWithArchiveGroup[] = $value;
                    }
                }
                print json_encode($userListWithArchiveGroup);
                break;
            // Case for Public user
            case 'U':
                //Get list of all users having user_type U(Public user)
                $apiGetData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'list_profiles',
                    "_user" => $userId,
                    "user_type" => 'U',
                    "opt_include_sub" => 'Y',
                    "_prop"=>'Y'
                );
                $apiResponse = aibServiceRequest($apiGetData, 'users');
                $responseData = $apiResponse['info']['records'];  
                // Get the Archive Group data
                $userListWithArchiveGroup = Array();
                foreach ($responseData as $key => $value) {
                    // Get user profile status
                    $user_status = getUserProfileProperties($value['user_id'],'status');
                    $value['status'] = isset($user_status['status']) ? $user_status['status'] : '';
                    // Get user archive details
                    $archiveData = getItemData($value['user_top_folder']);  
                    if (!empty($archiveData)) {
                        $value['item_title'] = $archiveData['item_title'];
			$value['ebay_status'] = $archiveData['properties']['ebay_status'];
                        $userListWithArchiveGroup[] = $value;
                    }
                } 
                print json_encode($userListWithArchiveGroup);
                break;
        }
        break;
    case 'forms-list':
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_list',
            "_user" => $_SESSION['aib']['user_data']['user_id']
        );
        if ($_SESSION['aib']['user_data']['user_type'] != 'R') {
            $apiGetData['form_owner_id'] = $_SESSION['aib']['user_data']['user_top_folder'];
        }
        $apiResponse = aibServiceRequest($apiGetData, 'fields');
        $responseData = $apiResponse['info']['records'];
        print json_encode($responseData);
        break;
    case 'fields-list':
        $systemDefaultHideFields = $GLOBALS['aib_hide_predef_field_list'];
        $type = $_POST['type'];
        $finalresponse = [];
        $archive_group_id = (isset($_POST['arch_id']) && $_POST['arch_id'] != '') ? $_POST['arch_id'] : $_SESSION['aib']['user_data']['user_top_folder'];
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'field_list',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
        );
        $apiResponse = aibServiceRequest($apiGetData, 'fields');
        $responseData = $apiResponse['info']['records'];
        if ($type != 'manage') {
            $responseDataNew = array();
            $childata = getItemChild($archive_group_id);
            $childata[] = $archive_group_id;
            foreach ($responseData as $responseKey => $dataArray) {
                if ($dataArray['_disabled'] == '_N') {
                    unset($responseData[$responseKey]);
                    continue;
                }
                if($dataArray['field_owner_type'] == 'S' && array_key_exists($dataArray['field_symbolic_name'],$systemDefaultHideFields)){
                    unset($responseData[$responseKey]);
                    continue;
                }
                if ($dataArray['field_owner_type'] == 'S' || $dataArray['field_owner_type'] == 'R') {
                    continue;
                }
                if (!in_array($dataArray['field_owner_id'], $childata)) {
                    unset($responseData[$responseKey]);
                }
            }
            if (isset($_REQUEST['type']) and $_REQUEST['type'] == 'edit' and isset($_SESSION['filedArray'])) {
                foreach ($responseData as $data) {
                    if (!in_array($data['field_id'], $_SESSION['filedArray']))
                        $responseDataNew[] = $data;
                }
            }
            else {
                $responseDataNew = $responseData; //print json_encode($responseData);
            }
            $responseArray = array();
            $storage = "";
            foreach ($responseDataNew as $response) {
                if ($response['field_owner_type'] == 'I') {
                    if ($storage == "" || $storage != $response['field_owner_id']) {
                        $itemData = array();
                        $itemData = getItemData($response['field_owner_id']);
                        $storage = $response['field_owner_id'];
                    }
                    $responseArray[$itemData['item_title']][] = $response;
                } else {
                    $responseArray[$response['field_owner_type']][] = $response;
                }
            }
            unset($responseArray['']);
            print json_encode($responseArray);
        } else {
            if ($_SESSION['aib']['user_data']['user_type'] != 'R') {
                $childata = getItemChild($_SESSION['aib']['user_data']['user_top_folder']);
                $childata[] = $_SESSION['aib']['user_data']['user_top_folder'];
                foreach ($responseData as $responseKey => $dataArray) {
                    if (!in_array($dataArray['field_owner_id'], $childata)) {
                        unset($responseData[$responseKey]);
                        continue;
                    }
                }
            }
            $childata = getItemChild($archive_group_id);
            $childata[] = $archive_group_id;
            foreach ($responseData as $responseKey=>$newresponseData) {
                if($archive_group_id == 'S' || $archive_group_id == 'R'){
                    if($newresponseData['field_owner_type']!=$archive_group_id) {
                        unset($responseData[$responseKey]);
                        continue;
                    }
                }else{
                    if (!in_array($newresponseData['field_owner_id'], $childata)) {
                        unset($responseData[$responseKey]);
                        continue;
                    }
                }
                if ($newresponseData['_disabled'] == '_N') {
                    unset($responseData[$responseKey]);
                    continue;
                }
                if($newresponseData['field_owner_type'] == 'S' && array_key_exists($newresponseData['field_symbolic_name'],$systemDefaultHideFields)){
                    unset($responseData[$responseKey]);
                    continue;
                }
                if($newresponseData['field_owner_id'] != '' && $newresponseData['field_owner_id'] != '-1'  && $newresponseData['field_owner_id'] != '-2'){
                    $ownerTitle = getItemData($newresponseData['field_owner_id'], 1);
                    $newresponseData['display_owner_title'] = $ownerTitle['item_title'];
                }else{$newresponseData['display_owner_title'] = 'Root';}
                $finalresponse[] = $newresponseData;
            }
            print json_encode($finalresponse);
        }
        break;
    case 'update_profile':
        parse_str($_POST['formData'], $postData);
        $passwordDataArray = array();
        $userProperty = [];
        if (isset($postData['profile_paswd']) && $postData['profile_paswd'] != '') {
            $passwordDataArray = array("new_user_password" => $postData['profile_paswd']);
        }
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'update_profile',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'user_id' => $_SESSION['aib']['user_data']['user_id'],
            "new_user_title" => $postData['profile_name']
        );
        $mergedDataArray = array_merge($requestPostData, $passwordDataArray);
        $apiResponse = aibServiceRequest($mergedDataArray, 'users');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Profile updated successfully.'); 
            if(isset($postData['term_service']) && !empty($postData['term_service'])){
                 $userProperty[0]['name'] = 'term_service';
                 $userProperty[0]['value'] = $postData['term_service'];
                if(isset($postData['occasional_update']) && $postData['occasional_update'] == 'Y'){
                    $userProperty[1]['name'] = 'occasional_update';
                    $userProperty[1]['value'] = $postData['occasional_update'];
                }
                $postRequestData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" =>$_SESSION['aib']['user_data']['user_id'],
                    "_op" => "set_profile_prop_batch",
                    "user_id" =>$_SESSION['aib']['user_data']['user_id'],
                    "property_list" => json_encode($userProperty)
                );
                $apiResponseProfile = aibServiceRequest($postRequestData, 'users');
                if($apiResponseProfile['status']=='OK'){
                    $responseData = array('status' => 'success', 'message' => 'Profile updated successfully.');  
                }
            }
        } else {
            $responseData = array('status' => 'error', 'message' => 'Profile has not updated.');
        }
        print json_encode($responseData);
        break;
    /* Create Fields Section Start */
    case 'create_fields':
        parse_str($_POST['formData'], $postData);
        $field_owner_type = 'I';
        $feld_class = '';
        $field_owner_id = $postData['field_owner_name'];
        if (trim($postData['field_owner_name']) == 'R' || trim($postData['field_owner_name']) == 'S') {
            $field_owner_type = $postData['field_owner_name'];
            if(trim($postData['field_owner_name']) == 'R'){
                $field_owner_id = '-2';
                $feld_class = 'recommended';
            }else{
                $field_owner_id = '-1';
                $feld_class = 'traditional';
            }
        } 
        if(isset($_SESSION['aib']['user_data']['user_type']) and $_SESSION['aib']['user_data']['user_type']=='U'){	
            $field_owner_id=$_SESSION['aib']['user_data']['user_top_folder'];
        }
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'field_create',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            "field_title" => $postData['field_name'],
//            "field_format" => $postData['field_format_detail'],
            "field_size" => $postData['field_display_width_name'],
            "field_owner_type" => $field_owner_type,
            "field_owner_id" => $field_owner_id,
            "field_data_type" => $postData['field_type_name'],
            "field_class" =>$feld_class
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        if ($apiResponse['status'] == 'OK') {
            if ($_SESSION['aib']['user_data']['user_type'] == 'U') {
                updateUserProperty($_SESSION['aib']['user_data']['user_id'], 'total_field_created', $apiResponse['info']);
            }
            $responseData = array('status' => 'success', 'message' => 'Field added successfully');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Field not added.');
        }
        print json_encode($responseData);
        break;
    /* Create Fields Section End */
    /* Create Forms Section Start */
    case 'create_forms':
        parse_str($_POST['formData'], $postData);
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_create',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "form_name" => $postData['form_name'],
            "form_owner_id" => $postData['archive_name'],
            "form_owner_type" => 'I'
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        if ($apiResponse['status'] == 'OK') {
            $formId = $apiResponse['info'];
            if ($_SESSION['aib']['user_data']['user_type'] == 'U') {
                updateUserProperty($_SESSION['aib']['user_data']['user_id'], 'total_template_created', $formId);
            }
            $count = 1;
            foreach ($_POST['selectvalue'] as $fieldIndex => $fieldValue) {
                $requestPostData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'form_add_field',
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    "form_id" => $formId,
                    "field_id" => $fieldValue,
                    "field_sort_order" => $count
                );
                $apiResponse = aibServiceRequest($requestPostData, 'fields');
                $count ++;
            }
            $responseData = array('status' => 'success', 'message' => 'Forms added successfully');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Forms added not successfully.');
        }
        print json_encode($responseData);
        break;
    /* Create Forms Section End */
    case 'get_assistant':
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'get_profile',
            '_user' => 1,
            'user_id' => $_POST['get_user_id']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'users');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Field updated successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Field not updated.');
        }
        print json_encode($responseData);
        break;
    /*  GetItem Assistant Section End  */
    /*  GetItem Fields Section Start  */
    case 'get_fields':
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'field_get',
            '_user' => 1,
            'field_id' => $_POST['edit_field_id']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        $recordget = $apiResponse['info']['records'];
        $itemTitle = getItemData($recordget[0]['field_owner_id']);
        include 'edit_manage_field.php';
        break;
    /*  GetItem Fields Section End  */
    /*  GetItem Forms Section Start  */
    case 'get_forms':
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_get',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'form_id' => $_POST['edit_forms_id']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        $formdata = $apiResponse['info']['records'];
        $form_owner_id = $formdata[0]['form_owner_id'];
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_list_fields',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'form_id' => $_POST['edit_forms_id']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        $fielddata = $apiResponse['info']['records'];
        $filedArray = array();
        if (count($fielddata)) {
            foreach ($fielddata as $filed) {
                $filedArray[] = $filed['form_field_id'];
            }
        }
        $_SESSION['filedArray'] = $filedArray;
        include 'edit_manage_form.php';
        break;
    /*  GetItem Forms Section End  */
    /*  GetItem Forms get lists Section Start  */
    case 'get_custom_form_fields':
        $form_id = $_POST['form_id'];
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_list_fields',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'form_id' => $form_id
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        $mydataarr = $apiResponse['info']['records'];
        include 'form_get_list.php';
        break;
    /* Get Scrapbook Properties */
    case'get_scrpbook_prop':
        $item_id = $_POST['id'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
        $responseData = $apiResponseProps['info']['records']['share_user'];
        print json_encode($responseData);
        break;

    /*  GetItem Forms get lists Section End  */
    case 'update-manage-field':
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'field_modify',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'field_id' => $_POST['update_field_id'],
            'field_title' => $_POST['updatefieldtitle'],
            'field_data_type' => $_POST['updatefieldtype'],
            //'field_format' => $_POST['updatefieldformat'],
            'field_size' => $_POST['updatefieldsize'],
            'field_owner_type' => $_POST['updatefieldowner'],
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Field updated successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Field not updated.');
        }
        print json_encode($responseData);
        break;
    /*  GetItem Fields Section End  */
    /*  GetItem Forms get lists Section End  */
    case 'update-manage-form':
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_modify',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'form_id' => $_POST['edit_forms_id'],
            'form_name' => $_POST['edit_form_name']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'fields');
        if ($apiResponse['status'] == 'OK') {  
            $NewFields = $_REQUEST['edit_field_id'];  
            $oldFields = explode(',', $_REQUEST['fields_on_form']);
            $NewAdded = array_diff($NewFields, $oldFields);  
            $Removed = array_diff($oldFields, $NewFields); 
			$updateFieldId = array_diff($NewFields, $NewAdded);   
			foreach ($updateFieldId as $key=>$value) {
				$updateOrderRecord =  array(
					"_key" => APIKEY,
					"_session" => $sessionKey,
					"_op" => 'form_modify_field',
					'_user' => $_SESSION['aib']['user_data']['user_id'],
					'form_id' => $_POST['edit_forms_id'],
					'field_id'=>$value,
					'field_sort_order'=>$key+1
				);
				$orderResponseApi = aibServiceRequest($updateOrderRecord, 'fields');
			} 
			foreach ($NewAdded as $fieldNew) {
				if ($fieldNew != '') {
					$sortOrder = array_search($fieldNew, $NewFields); 
					$requestPostData = array(
						"_key" => APIKEY,
						"_session" => $sessionKey,
						"_op" => 'form_add_field',
						'_user' => $_SESSION['aib']['user_data']['user_id'],
						"form_id" => $_POST['edit_forms_id'],
						"field_id" => $fieldNew,
						"field_sort_order" => $sortOrder+1
					); 
				   $apiResponse = aibServiceRequest($requestPostData, 'fields');
				}
			} 
            if (count($Removed)) {
                foreach ($Removed as $fieldRem) {
                    if ($fieldRem != '') {
                        $requestPostData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_op" => 'form_del_field',
                            '_user' => $_SESSION['aib']['user_data']['user_id'],
                            "form_id" => $_POST['edit_forms_id'],
                            "field_id" => $fieldRem
                        );
                        $apiResponse = aibServiceRequest($requestPostData, 'fields');
                    }
                }
            }
            $responseData = array('status' => 'success', 'message' => 'Template has been updated successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Template not updated.');
        }
        print json_encode($responseData);
        break;
    /*  GetItem Fields Section End  */
    /*  Delete Assistant Section Start  */
    case 'delete_assistant':
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'update_profile',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "user_id" => $_POST['data_user_id']
        );
        $apiResponse = aibServiceRequest($apiGetData, 'users');
        if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Assistant has been deleted successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Assistant not deleted.');
        }
        print json_encode($responseData);
        break;
    /*  Delete Assistant Section End  */
    /*  Delete Fields Section Start  */
    case 'delete_fields':
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'field_del',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "field_id" => $_POST['field_id']
        );
        $apiResponse = aibServiceRequest($apiGetData, 'fields');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Fields has been deleted successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Fields not deleted.');
        }
        print json_encode($responseData);
        break;
    /*  Delete Fields Section End  */
    /*  Delete Forms Section Start  */
    case 'delete_forms':
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'form_del',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "form_id" => $_POST['forms_id']
        );
        $apiResponse = aibServiceRequest($apiGetData, 'fields');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Forms deleted successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Forms deleted not successfully.');
        }
        print json_encode($responseData);
        break;
    /*  Delete Forms Section End  */
    case 'list_tree_items':
        $folderId = $_POST['folder_id'];
        if($_POST['record_edit_link'] != ''){
            $_SESSION['record_edit_link'] = $_POST['record_edit_link'].'&return_to=item_list';
        }
        $itemData = getItemData($folderId);
        if ($itemData['item_type'] == 'AR') {
            $archiveDetails = getItemDetailsWithProp($itemData['item_id']); 
            if (isset($archiveDetails['prop_details']['type']) && $archiveDetails['prop_details']['type'] == 'A') {
                $_SESSION['type'] = "A";
            } else {
                unset($_SESSION['type']);
            }
        }
        $search_filter = array(array('name'=>'aibftype',"value"=>"rec"),array('name'=>'aibftype',"value"=>"sg"),array('name'=>'aibftype',"value"=>"col"),array('name'=>'aibftype',"value"=>"IT"),array('name'=>'link_class',"value"=>"public"),array('name'=>'visible_to_public',"value"=>0),array('name'=>'aib:visible',"value"=>"N"));
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $folderId,
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
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($itemData['item_id'] == 1) {
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $apiRequestData = array(
                    '_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'get_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $dataArray['item_id']
                );
                $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
                $apiResponse['info']['records'][$key]['prop_details'] = $apiResponseProps['info']['records'];
            }
        }
        $treeDataArray = getTreeData($folderId);  
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
						$apiResponse['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                    if ($FileRecord["file_type"] == 'pr') {
                        $PrimaryID = $FileRecord["file_id"];
                        $apiResponse['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                }
            }
        }
        foreach ($apiResponse['info']['records'] as $itemKey => $itemDataArray) {
            if($_SESSION['aib']['user_data']['user_type'] == 'A'){
                if(isset($itemDataArray['properties']['visible_to_public']) && $itemDataArray['properties']['visible_to_public'] == '0'){
                    unset($apiResponse['info']['records'][$itemKey]);
                    continue;
                }
                if(isset($itemDataArray['properties']['publish_status']) && $itemDataArray['properties']['publish_status'] == 'N'){
                    unset($apiResponse['info']['records'][$itemKey]);
                    continue;
                }
            }
            if($_SESSION['aib']['user_data']['user_type'] == 'A'){
                if(isset($itemDataArray['properties']['aib:visible']) && $itemDataArray['properties']['aib:visible'] == 'N'){
                    unset($apiResponse['info']['records'][$itemKey]);
                    continue;
                }
            }
            /* $itemCount = getItemChildCount($itemDataArray['item_id'], $itemData['item_type']);
            if ($itemCount) {
                $apiResponse['info']['records'][$itemKey]['child_count'] = $itemCount['child_count'];
                if ($itemCount['sg_count'] != 0) {
                    $apiResponse['info']['records'][$itemKey]['sg_count'] = $itemCount['sg_count'];
                }
            }*/
            if($_SESSION['aib']['user_data']['user_type'] == 'U'){
                if($itemDataArray['item_type'] == 'SG'){
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'];
                }
                if($itemDataArray['item_type'] == 'RE'){
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][4]['count']-$itemDataArray['property_counts'][5]['count'];
                }
            }else{
                $disabledCount = 0;
                $visileFalseCount = 0;
                if($_SESSION['aib']['user_data']['user_type'] == 'A'){
                    $disabledCount = $itemDataArray['property_counts'][6]['count'];
                    $visileFalseCount = isset($itemDataArray['property_counts'][7]['count']) ? $itemDataArray['property_counts'][7]['count'] : 0;
                }
                if($itemData['item_type'] == 'AG'){
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][3]['count']-$disabledCount;
                }
                if($itemData['item_type'] == 'AR'){
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][2]['count']-$disabledCount;
                }
                if($itemData['item_type'] == 'CO'){
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count']-($disabledCount+$visileFalseCount);
                    if($itemDataArray['property_counts'][2]['count'] != 0){
                        $apiResponse['info']['records'][$itemKey]['sg_count']= $itemDataArray['property_counts'][2]['count']-$disabledCount;
                    }
                }
                if($itemData['item_type'] == 'SG'){
                   if( $itemDataArray['item_type']=='SG'){
			$apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count']-$disabledCount;
                    }else{
                        $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][4]['count']-($itemDataArray['property_counts'][5]['count']+$disabledCount+$visileFalseCount);
                    }
                    if($itemDataArray['property_counts'][2]['count'] != 0){
                        $apiResponse['info']['records'][$itemKey]['sg_count']= $itemDataArray['property_counts'][2]['count']-$disabledCount;
                    }
                }
            }
            if($apiResponse['info']['records'][$itemKey]['item_title']=='shared out of system'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if($apiResponse['info']['records'][$itemKey]['item_title']=='society-share'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if(isset($itemDataArray['link_properties']['link_class']) && $itemDataArray['link_properties']['link_class'] == 'related_content'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if(isset($itemDataArray['link_properties']['link_class']) && $itemDataArray['link_properties']['link_class'] == 'historical_connection'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if(isset($itemDataArray['link_properties']['link_class']) && $itemDataArray['link_properties']['link_class'] == 'public'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if(isset($itemDataArray['link_properties']['link_class']) && $itemDataArray['link_properties']['link_class'] == 'scrapbook'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if($apiResponse['info']['records'][$itemKey]['item_title']=='aib-shared'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if($apiResponse['info']['records'][$itemKey]['item_title']=='Scrapbooks'){
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            if($_SESSION['aib']['user_data']['user_type'] == 'U' && ($itemDataArray['item_title'] == 'Scrapbooks')){
                unset($apiResponse['info']['records'][$itemKey]);
            }
            if($itemData['item_type'] == 'AG' || $itemData['item_type'] == 'AR' || $itemData['item_type'] == 'CO'){
                if($apiResponse['info']['records'][$itemKey]['is_link'] == 'Y'){
                    unset($apiResponse['info']['records'][$itemKey]);
                }
            }
            if($itemData['item_type'] == 'RE'){
                if($apiResponse['info']['records'][$itemKey]['item_type'] != 'IT'){
                    unset($apiResponse['info']['records'][$itemKey]);
                }
            }
        }  
	//Vishnu
        $ebayCheckCondition = '';    
        if($apiResponse['info']['records'][0]['item_type'] != 'AR'){
            if(empty($apiResponse['info']['records'][0]['root_info']['archive_group'])){ 
                $ebayApiStatus = getItemData($apiResponse['info']['records'][0]['root_info']['archive']['item_id']); 
                $ebayCheckCondition = $ebayApiStatus['properties']['ebay_status']; 
            }else{
                $ebayApiStatus = getItemData($apiResponse['info']['records'][0]['root_info']['archive_group']['item_id']); 
                $ebayCheckCondition = $ebayApiStatus['properties']['ebay_status'];
            }  
        }   
        if ($apiResponse['status'] == 'OK') {
            $finalDataArray = $apiResponse['info']['records'];
            include_once TEMPLATE_PATH . 'archive_listing.php';
        }
        break;
    case 'create_new_items':
        parse_str($_POST['formData'], $postData);
        $aditionalProp = array();
        $aditionalProp['propname_1'] = 'sort_by';
        $aditionalProp['propval_1'] = (isset($postData['default_sorting_by']) && $postData['default_sorting_by'] != '') ? $postData['default_sorting_by'] : 'TITLE';
        switch ($postData['item_request_type']) {
            case 'ar':
                $item_title = $postData['item_title'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements_ar'];
                $aditionalProp['propname_3'] = 'is_advertisements';
                $aditionalProp['propval_3'] = ($item_title == 'Advertisements') ? 'Y' : 'N';
                break;
            case 'col':
                $item_title = $postData['collection_name'];
                $aditionalProp['propname_2'] = 'visible_to_public';
                $aditionalProp['propval_2'] = $postData['visible_to_public_co'];
                $aditionalProp['propname_3'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_3'] = $postData['display_archive_level_advertisements_co'];
                $aditionalProp['propname_4'] = 'is_advertisements';
                $aditionalProp['propval_4'] = ($item_title == 'Advertisements') ? 'Y' : 'N';
                break;
            case 'sg':
                $item_title = $postData['sub_group_name'];
                $aditionalProp['propname_2'] = 'visible_to_public';
                $aditionalProp['propval_2'] = $postData['visible_to_public_sg'];
                $aditionalProp['propname_3'] = 'is_advertisements';
                $aditionalProp['propval_3'] = ($item_title == 'Advertisements') ? 'Y' : 'N';
                if($_SESSION['aib']['user_data']['user_type'] != 'U'){
                    $aditionalProp['propname_4'] = 'display_archive_level_advertisements';
                    $aditionalProp['propval_4'] = $postData['display_archive_level_advertisements_sg'];
                }
                break;
            default :
                $item_title = $postData['item_title'];
                $aditionalProp['propname_2'] = 'code';
                $aditionalProp['propval_2'] = $postData['item_code'];
                break;
        }
        $responseData = array('status' => 'error', 'message' => 'All fields are required');
        if (!empty($postData)) {
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            $postDataItem = array(
                '_key' => APIKEY,
                '_user' => $user_id,
                '_op' => 'create_item',
                '_session' => $sessionKey,
                'parent' => $postData['parent_id'],
                'item_title' => $item_title,
                'item_class' => $postData['item_request_type'],
                    //'item_owner_id' => $user_id,
                    //'item_owner_group' => 1,
                    //'opt_allow_dup' => 'N',
            );
            $apiResponse = aibServiceRequest($postDataItem, 'browse');
            $responseData = array('status' => 'error', 'message' => 'Item not created, Please try again');
            if ($apiResponse['status'] == 'OK') {
                if (!empty($aditionalProp)) {
                    $archive_id = $apiResponse['info'];
                    $aditionalProp['_key'] = APIKEY;
                    $aditionalProp['_user'] = $user_id;
                    $aditionalProp['_op'] = 'set_item_prop';
                    $aditionalProp['_session'] = $sessionKey;
                    $aditionalProp['obj_id'] = $archive_id;
                    $apiResponse = aibServiceRequest($aditionalProp, 'browse');
                }
                $responseData = array('status' => 'success', 'message' => 'Item created successfully.');
            }
			}
        print json_encode($responseData);
        break;
    case 'archive_lists':
        $folderId = $_POST['folder_id'];
        $itemData = getItemData($folderId);
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folderId,
            "opt_get_files" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $treeDataArray = getTreeData($folderId);
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
        if ($apiResponse['status'] == 'OK') {
            $finalDataArray = $apiResponse['info']['records'];
        }
        print json_encode($apiResponse);
        break;
    case 'list_tree_items_records':
        $folderId = $_POST['folder_id'];
        $itemData = getItemData($folderId);
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $folderId,
            "opt_get_files" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $treeDataArray = getTreeData($folderId);
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
        if ($apiResponse['status'] == 'OK') {
            $finalDataArray = $apiResponse['info']['records'];
            include_once TEMPLATE_PATH . 'add_record_tree.php';
        }
        break;
    case 'add_assistant':
        parse_str($_POST['formData'], $postData);
        $responseData = array('status' => 'error', 'message' => 'Assistant not created.');
        if (!empty($postData)) {
            $responseStatus = createNewUser('S', $postData);
            if ($responseStatus['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Assistant created successfully');
            } else {
                $responseData = array('status' => 'error', 'message' => $message[$responseStatus['info']]);
            }
        }
        print json_encode($responseData);
        break;  
    case 'add_administrator':
        parse_str($_POST['formData'], $postData);
        $responseData = array('status' => 'error', 'message' => 'Administrator not created.');
        if (!empty($postData)) {
            $responseStatus = createNewUser('A', $postData);
            if ($responseStatus['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Administrator created successfully.');
            } else {
                $responseData = array('status' => 'error', 'message' => $message[$responseStatus['info']]);
            }
        }
        print json_encode($responseData);
        break;
    case 'archive_group_list':
        $folderId = $_POST['folder_id'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $folderId,
            "opt_get_files" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $responseData = [];
        if ($apiResponse['status'] == 'OK') {
            $responseData = $apiResponse['info']['records'];
        }
        print json_encode($responseData);
        break;
    case 'get_client_by_id':
        $clientId = $_POST['client_id'];
        $detailsData = getItemData($clientId);
        print json_encode($detailsData);
        break;
	case 'store_ocr_value_field':  
		parse_str($_POST['formData'], $formData);
		$responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again'); 
		
		$postData = array(
						"_key" => APIKEY,
						"_session" => $sessionKey,
						"_user" => "1",
						"_op" => "store_named_fields",
						"obj_id" => $formData['object_id'],
					);
		$postData["field_list"] = json_encode(array(
			array("name" => "ocrtxt", "value" => $formData['ocr_value']),
		));  
		//print_R($postData);
	
		$apiResponse = aibServiceRequest($postData, 'fields');   	//print_R($apiResponse);
		if($apiResponse['status']=='OK'){
			$responseData = array('status' => 'success', 'message' => 'Value store successfully');
		}else{
			$responseData = array('status' => 'error', 'message' => $apiResponse['info']);
		} 
        print json_encode($responseData);
        break;
		
	case 'get_ocr_field_name':   
		$responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again'); 
		$postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => "1",
                    "_op" => "get_named_fields",
                    "obj_id" => $_POST['obj_id'],
                    "field_list" => json_encode(array('ocrtxt')),
                );  
		$apiResponse = aibServiceRequest($postData, 'fields'); 
		if($apiResponse['status']=='OK'){
                    $responseData = array('status' => 'success', 'value' => urldecode($apiResponse['info']['records'][0]['value']));
		}
		print json_encode($responseData);
        break;
		
	case 'run_ocr_onfolder':   
		$responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again'); 
		$postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "markocr",
                    "item_id_list" => $_POST['obj_id'],
                    "child_items" => 'Y',
                    "opt_recursive" => 'N',
					"skip_processed" => 'Y'
                );  
		$apiResponse = aibServiceRequest($postData, 'recordop'); 
		if($apiResponse['status']=='OK'){
                    $responseData = array('status' => 'success', 'value' => urldecode($apiResponse['info']['records'][0]['value']));
		}
		print json_encode($responseData);
        break;
		
	case 'run_ocr_onfolder_flag':   
		$responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again'); 
		$ids = getItemChildToOCR($_POST['obj_id']);
		for($i=0; $i<count($ids); $i++){
			$postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "set_item_prop",
                    "obj_id" => $ids[$i],//$_POST['obj_id'],
                    "opt_long" => 'Y',
					"propname_1" => 'ocr_flag',
					"propval_1" => '1'
                );  
			$apiResponse = aibServiceRequest($postData, 'browse'); 
		}
		
		$html = '';
		if($apiResponse['status']=='OK'){
			if($_SESSION['aib']['user_data']['user_type']=='R'){
				$html .= '<span data-ocr-folder-id="'.$_POST['obj_id'].'" class="reset-ocr-onfolder"><img title="Reset OCR" src="'.IMAGE_PATH.'Reset_OCR.png" alt="" /></span>';
			}
			$html .= '<span data-ocr-folder-id="'.$_POST['obj_id'].'"><img title="OCRED" src="'.IMAGE_PATH.'OCRed.png" alt="" /></span>';
			//$html .= '<span data-ocr-folder-id="'.$dataArray['item_id'].'" class="run-ocr-onfolder"><img title="Run OCR" src="'.IMAGE_PATH.'Run_OCR.png" alt="" /></span>';
			$responseData = array('status' => 'success', 'value' => urldecode($apiResponse['info']['records'][0]['value']), 'html'=>$html);
		}
		print json_encode($responseData);
        break;
		
	case 'reset_ocr_onfolder':   
		$responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again'); 
		
		$postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "clearocr",
                    "item_id_list" => $_POST['obj_id'],
                    "child_items" => 'Y',
                    "opt_recursive" => 'N',
					"skip_processed" => 'Y'
                );  
		$apiResponse = aibServiceRequest($postData, 'recordop'); 
		
		$ids = getItemChildToOCR($_POST['obj_id']);
		for($i=0; $i<count($ids); $i++){
			$postData = array(
						"_key" => APIKEY,
						"_session" => $sessionKey,
						"_user" => $_SESSION['aib']['user_data']['user_id'],
						"_op" => "set_item_prop",
						"obj_id" => $ids[$i],//$_POST['obj_id'],
						"opt_long" => 'Y',
						"propname_1" => 'ocr_flag',
						"propval_1" => '0'
					);  
			$apiResponse = aibServiceRequest($postData, 'browse'); 
		}
		$html = '';
		if($apiResponse['status']=='OK'){
			if($_SESSION['aib']['user_data']['user_type']=='R'){
				$html .= '<span data-ocr-folder-id="'.$_POST['obj_id'].'" class="reset-ocr-onfolder"><img title="Reset OCR" src="'.IMAGE_PATH.'Reset_OCR.png" alt="" /></span>';
			}
			//$html .= '<span data-ocr-folder-id="'.$dataArray['item_id'].'" class="perform-ocr-onfolder"><img title="OCRED" src="'.IMAGE_PATH.'OCRed.png" alt="" /></span>';
			$html .= '<span data-ocr-folder-id="'.$_POST['obj_id'].'" class="run-ocr-onfolder"><img title="Run OCR" src="'.IMAGE_PATH.'Run_OCR.png" alt="" /></span>';
			$responseData = array('status' => 'success', 'value' => urldecode($apiResponse['info']['records'][0]['value']), 'html'=>$html);
		}
		print json_encode($responseData);
        break;
		
	case 'term_condition_add_form':
		$responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again');
		$termCond =  addslashes($_POST['terms_conditions']); 
			$apiRequestDataItem = array(
				 '_key' => APIKEY,
				 '_user' => $_SESSION['aib']['user_data']['user_id'],
				 '_op' => 'set_item_prop',
				 '_session' => $sessionKey,
				 'obj_id' => 1,
				 'opt_long' => 'Y',
				 'propname_1' => 'timestamp',
				 'propval_1' => time() 
			 );
			 
			if($_POST['form_type'] =='PC'){
				$apiRequestDataItem['propname_2'] = 'privacy_and_cookies';
				$apiRequestDataItem['propval_2'] = $termCond;
				$message = 'Privacy & cookies successfully added';
			}
			else if($_POST['form_type'] =='DMCA'){
				$apiRequestDataItem['propname_2'] = 'DMCA_value';
				$apiRequestDataItem['propval_2'] = $termCond;
				$message = 'DMCA successfully added';
			}
			else if($_POST['form_type'] =='DMCA_COUNTER'){
				$apiRequestDataItem['propname_2'] = 'dmca_counter_notice';
				$apiRequestDataItem['propval_2'] = $termCond;
				$message = 'DMCA counter notice successfully added';
			}
			else{
				$apiRequestDataItem['propname_2'] = 'terms_and_conditions';
				$apiRequestDataItem['propval_2'] = $termCond;
				$message = 'Terms & condition successfully added';
			}
			
			$apiResponse = aibServiceRequest($apiRequestDataItem, 'browse'); 
		   
			if($apiResponse['status'] == 'OK'){
			$responseData = array('status' => 'success', 'message' => $message);
			}
			else{
				$responseData = array('status' => 'error', 'message' => $apiResponse['info']);
			}
        print json_encode($responseData);
        break;
		
	case 'get_term_and_condition': 
            $responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again');
            $apiResponse =  getItemDetailsWithProp($_POST['user_id']);  
			if($_POST['form_type'] =='PC'){
				$propertyValue = stripslashes($apiResponse['prop_details']['privacy_and_cookies']);
			}
			else if($_POST['form_type'] =='DMCA'){
				$propertyValue = stripslashes($apiResponse['prop_details']['DMCA_value']);
			}
			else if($_POST['form_type'] =='DMCA_COUNTER'){
				$propertyValue = stripslashes($apiResponse['prop_details']['dmca_counter_notice']);
			}
			else{
				$propertyValue = stripslashes($apiResponse['prop_details']['terms_and_conditions']);
			}
			
			
            if($apiResponse['prop_details']) { 
                $responseData = array('status' => 'success', 'message' => $propertyValue);
            }  
            print json_encode($responseData);
        break;
		
    case 'get_user_by_id':
        $user_id = $_POST['user_id'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_profile",
            "user_id" => $user_id
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            $userProperty = getUserProfileProperties($user_id, 'email');
            $apiResponse['info']['property'] = $userProperty;
            print json_encode($apiResponse['info']);
        }
        break;
    case 'update_user_profile':
        parse_str($_POST['formData'], $postData);
        $passwordDataArray = array();
        if (isset($postData['user_password']) && $postData['user_password'] != '') {
            $passwordDataArray = array("new_user_password" => $postData['user_password']);
        }
        $responseData = array('status' => 'error', 'message' => 'Invalid post data');
        if (!empty($postData)) {
            $oldEmail = getUserProfileProperties($postData['user_id'],'email');
            $requestPostData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => 'update_profile',
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                'user_id' => $postData['user_id'],
                "new_user_title" => $postData['user_login']
            );
            $mergedDataArray = array_merge($requestPostData, $passwordDataArray);
            $apiResponse = aibServiceRequest($mergedDataArray, 'users');
            $responseData = array('status' => 'error', 'message' => 'Profile not updated.');
            if ($apiResponse['status'] == 'OK') {
                $userProperty[0]['name']  = 'email';
                $userProperty[0]['value'] = $postData['user_email'];
                if(isset($postData['user_access_type']) && $postData['user_access_type'] != ''){
                    $userProperty[1]['name']  = 'type';
                    $userProperty[1]['value'] = $postData['user_access_type'];
                }
                $requestUpdatePropertyData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "set_profile_prop_batch",
                    "user_id" => $postData['user_id'],
                    "property_list" =>json_encode($userProperty)
                );
                $result = aibServiceRequest($requestUpdatePropertyData,"users");
                if($result['status']=='OK'){
                    $email_content='';
                    if(trim($oldEmail['email']) != trim($postData['user_email'])){
                        $email_content = 'Your email-id have been changed from '.$oldEmail['email'].' to '.$postData['user_email'];
                    }
                        $title = $postData['user_login'];
                        $email_data = [];
                        $email_data['to'] = $postData['user_email'];
                        $email_data['from'] =  ADMIN_EMAIL;  
                        $email_data['reply'] = ADMIN_EMAIL;
                        $email_data['subject']='ArchiveInBox update user details';
                        $email_template = file_get_contents(EMAIL_PATH."/update.html");
                        $email_template = str_replace('#username#',$title,$email_template);
                        $email_template = str_replace('#email_change#',$email_content,$email_template);
                        $email = sendMail($email_data,$email_template);
                        if($email){
                              $responseData = array('status' => 'success', 'message' => 'Profile updated successfully.');
                            } 
                        }
                        if($postData['user_id'] == $_SESSION['aib']['user_data']['user_id']){
                            unset($_SESSION);
                            session_destroy();
                        }
            } else {
                $responseData = array('status' => 'error', 'message' => $message[$responseStatus['info']]);
            }
        }
        print json_encode($responseData);
        break;
    case 'get_assistant_list':
        $archive_id = $_POST['archive_id'];
        $itemParentList = getTreeData($archive_id,true,1);
        $archive_id = (isset($itemParentList[1]['item_id']) && $itemParentList[1]['item_id'] != '') ? $itemParentList[1]['item_id'] : $archive_id;
        $userId = $_SESSION['aib']['user_data']['user_id'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $userId,
            "_op" => "list",
            "parent" => $archive_id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $subAdminData = array();
        if (!empty($apiResponse['info']['records'])) {
            foreach ($apiResponse['info']['records'] as $archiveKey => $archiveData) {
                $apiGetData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'list_profiles',
                    "_user" => $userId,
                    "user_type" => 'S',
                    "user_top_folder" => $archiveData['item_id'],
                    "opt_include_sub" => 'Y'
                );
                $apiResponse = aibServiceRequest($apiGetData, 'users');
                $subAdminData = array_merge($subAdminData, $apiResponse['info']['records']);
            }
			$apiGetData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'list_profiles',
                    "_user" => $userId,
                    "user_type" => 'S',
                    "user_top_folder" => $archive_id,
                    "opt_include_sub" => 'Y'
                );
				
                $apiResponse = aibServiceRequest($apiGetData, 'users');
                $subAdminData = array_merge($subAdminData, $apiResponse['info']['records']);
        }
        print json_encode($subAdminData);
        break;

        case 'get_public_user_email':
            $apiGetData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => 'list_profiles',
                "_user" => ($userId == 1) ? $userId : 1,
                "user_type" => 'U',
                "opt_include_sub" => 'Y'
            );
            $apiResponse = aibServiceRequest($apiGetData, 'users');
            $responseData = $apiResponse['info']['records'];
            $userListWithArchiveGroup = Array();
            foreach ($responseData as $key=> $value) {
                $archiveData = getItemData($value['user_top_folder']);
                    $value['item_title'] = $archiveData['item_title'];
                    $userListWithArchiveGroup[] = $value;
            }
            print json_encode($userListWithArchiveGroup);
            break;
       
    case 'assign_assistant_to_sub_group':
        $apiRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'data_entry_mark_folder_todo',
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "user_id" => $_POST['assistant'],
            "parent" => implode(',', $_POST['sub_group'])
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'dataentry');
        $responseData = array('status' => 'error', 'message' => 'Assistant not assigned.');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Assistant successfully assigned to sub groups');
        }
        print json_encode($responseData);
        break;
    case 'delete_user_profile':
        $user_profile_id = $_POST['user_profile_id'];
        $requestingUserId = $_SESSION['aib']['user_data']['user_id'];
        $apiRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'delete_profile',
            "_user" => $requestingUserId,
            "user_id" => $user_profile_id
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'users');
        $responseData = array('status' => 'error', 'message' => 'User profile not deleted.');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'User profile deleted successfully.');
        }
        print json_encode($responseData);
        break;
    case 'update_archive_group_details':
        $responseData = array('status' => 'error', 'message' => 'Something went wrong! Please try again');
        parse_str($_POST['formData'], $postData);
        $removeKey = ['color_code','color_code_selected_for','description_background','heading_font_size','content_font_size','button_background','button_font_size','heading_font_color','content_font_color','button_font_color'];
        $postData = array_diff_key($postData, array_flip($removeKey)); 
        $postData['archive_watermark_text'] = str_pad($postData['archive_watermark_text'],30,' ',STR_PAD_BOTH);
        $postData['archive_request_reprint_text'] = $_POST['reprint_request_data'];
        $postData = array_map('addslashes', $postData);
        if (isset($postData['archive_id']) && $postData['archive_id'] != '') {
            $archive_id = $postData['archive_id'];
            if (isset($postData['archive_name']) && $postData['archive_name'] != '') {
                $arcive_title = $postData['archive_name'];
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'modify_item',
                    '_session' => $sessionKey,
                    'obj_id' => $archive_id,
                    'item_title' => $arcive_title,
                    'opt_allow_dup' => 'Y',
                );
                $apiResponse = aibServiceRequest($postDataItem, 'browse');
                unset($postData['archive_item_title']);
            }
            $tags = $postData['archive_tags'];
            if($tags != ''){
                updateItemTags($tags, $archive_id);
            }
            unset($postData['archive_tags']);
            $dataArray = array_slice($postData, 2);
            if (!empty($dataArray)) {
                $apiRequestData = array();
                $count = 1;
                foreach ($dataArray as $fieldTitle => $fieldValue) {
                    $apiRequestData['propname_' . $count] = $fieldTitle;
                    $apiRequestData['propval_' . $count] = $fieldValue;
                    $count++;
                }
            }
            if(isset($postData['custom_template'])){
                $apiRequestData['custom_template']= $postData['custom_template'];
            }
            if(isset($postData['details_page_design'])){
                $apiRequestData['details_page_design']= $postData['details_page_design'];
            }
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $archive_id,
                'opt_long' => 'Y'
            );
            $finalApiRequestData = array_merge($apiRequestDataItem, $apiRequestData);
            $apiResponse = aibServiceRequest($finalApiRequestData, 'browse');
            $responseData = array('status' => 'error', 'message' => 'Archive details not updated');
            if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Archive details updated successfully');
            }
        }
        print json_encode($responseData);
        break;

    case'set_scrpbook_prop':
        $id = $_POST['id'];
        $share_type = $_POST['type'];
        $shared_user_list = '';
        if(isset($_POST['email_id'])&& $_POST['email_id'] !='' && $share_type =='records'){
            $username= checkEmailExist($_POST['email_id']);
            if(!empty($username)){
              $shared_user_list = $username;  
            }
            $to = $_POST['email_id']; 
            $apiResponse['status']= 'OK';
        }else{
            $username = checkEmailExist($_POST['email_id']);
            if(!empty($username)){
              $shared_user_list = $username;  
            }  
            $to = $_POST['email_id'];
            $apiResponse['status']= 'OK';
        }
        $apiListResponse = getItemListRecord($_SESSION['aib']['user_data']['user_top_folder']);  
        foreach($apiListResponse['info']['records'] as $record){
            $shareData[$record['item_id']] = $record['item_title'];
        }
        $anony_title = "shared out of system";
        if(in_array($anony_title, $shareData)){	
            $outOfsytemId = array_search($anony_title, $shareData); 
        }
        else{
            $postDataItem = array(
                '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'create_item',
                '_session' => $sessionKey,
                'parent' => $_SESSION['aib']['user_data']['user_top_folder'],
                'item_title' => $anony_title,
                'item_type' => 'sg'
            );
            $apiItemResponse = aibServiceRequest($postDataItem, 'browse');
            $outOfsytemId = $apiItemResponse['info'];
            $propertyVisible = [
                '_key' => APIKEY,
                '_user'=>$_SESSION['aib']['user_data']['user_id'],
                '_op'=>'set_item_prop',
                '_session'=>$sessionKey,
                'obj_id'=>$outOfsytemId,
                'propname_1'=>'aib:visible',
                'propval_1'=>'N'
            ];  
            $apiVisibleResponse = aibServiceRequest($propertyVisible, 'browse');  
        } 
        $shared_user = json_encode($shared_user_list);
        if($share_type =='records' && empty($shared_user_list)){
            $titleResponse = getItemData($_POST['id']);  
            $title_record  = $titleResponse['item_title'];   
            $itemDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'create_item',
                '_session' => $sessionKey,
                'parent' => $outOfsytemId,
                'item_title' => $title_record,
                'item_type' => 'rec'
            );
            $itemResponse = aibServiceRequest($itemDataItem, 'browse');
            $propertyArray = [
                'share_email'=>$_POST['email_id'],
                'share_thoumb_id'=>$_POST['id'],
                'share_created_date'=>time(),
				'item_created_date'=>$titleResponse['item_create_stamp'],
                'share_title'=>$title_record,
                'share_type'=>$_POST['type'],
                'share_item_id'=>$_POST['id'],
                'share_message'=>$_POST['share_massage']    
            ]; 
            $k =1;
            foreach($propertyArray as $proKey=>$proValue){
                $aditionalProp['propname_'.$k] = $proKey;
                $aditionalProp['propval_'.$k] =  $proValue;
                $k++;	 
            }
            $aditionalProp['_key'] = APIKEY;
            $aditionalProp['_user'] = $_SESSION['aib']['user_data']['user_id'];
            $aditionalProp['_op'] = 'set_item_prop';
            $aditionalProp['_session'] = $sessionKey;
            $aditionalProp['obj_id'] = $itemResponse['info'];
            $apiResponse = aibServiceRequest($aditionalProp, 'browse');  
        }else{
            $createdShareResponse = [];
            if(!empty($shared_user_list)){ 
                $createdShareResponse = sharedRecordWithUser($shared_user_list, $id);
            }   
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $id,
                'opt_long' => 'Y'
            );
            if($share_type =='scrapbook'){
                $scrapbookDetails = getItemDetailsWithProp($id);
                $previousUser     = (isset($scrapbookDetails['prop_details']['share_user']) && !empty($scrapbookDetails['prop_details']['share_user'])) ? json_decode($scrapbookDetails['prop_details']['share_user']) : array();
                $prevAnonyUser    = (isset($scrapbookDetails['prop_details']['share_user_email']) && !empty($scrapbookDetails['prop_details']['share_user_email'])) ? json_decode($scrapbookDetails['prop_details']['share_user_email']) : array();
                if(empty($shared_user_list)){
                    $prevAnonyUser  = array_merge($prevAnonyUser, array($_POST['email_id']));
                    $apiRequestDataItem['propname_1'] = 'share_user_email';
                    $apiRequestDataItem['propval_1'] = json_encode($prevAnonyUser);
                }else{
                    $previousUser  = array_merge($shared_user_list,$previousUser);
                    $apiRequestDataItem['propname_1'] = 'share_user';
                    $apiRequestDataItem['propval_1'] = json_encode($previousUser);
                }
            }else{
                $apiRequestDataItem['propname_1'] = 'share_user';
                $apiRequestDataItem['propval_1'] = (!empty($shared_user_list))? $shared_user: $anony_title;
            }
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse'); 
        }
        if ($apiResponse['status'] == 'OK') {
            $itemTopParents = getTreeData($id,true, 1);
            $email_data = [];
            $email_data['to'] = $to;
            $email_data['from'] =  ADMIN_EMAIL;  
            $email_data['reply'] = $to;
            $email_template = file_get_contents(EMAIL_PATH."/share.html");
            $name = (isset($itemTopParents[1]['item_title']) && $itemTopParents[1]['item_title']!= '') ? $itemTopParents[1]['item_title'] :$_SESSION['aib']['user_data']['user_title'];
            $date =  date("F j, Y");
            $email_template = str_replace('#name#',$name, $email_template);
            $email_template = str_replace('#date#',$date, $email_template);
            $data_id = getTreeData($_POST['id']);
            $archive_details = getItemDetailsWithProp($data_id[1]['item_id']); 
            if($archive_details['prop_details']['archive_header_image']){
            $header_image = HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_header_image'];
            $header_image = '<img style="width:100%;" src="'.$header_image.'" alt="Image" />';    
            }else{
             $header_image = '<img style="width:100%;" src="'.HOST_ROOT_IMAGE_PATH.'mail-template_header.jpg" alt="Image" />';    
            }
            $thoumb = '<img height="100" style="position: absolute;left: 50%;top: 50%;transform: translateX(-50%) translateY(-50%);height: 80px;" src="'.HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_logo_image'].'" alt="ArchiveInABox Logo" />';
            $img1 = '<img style="width:100%;" src="'. THUMB_URL . '?item_id=' . $_POST['id'].'&download=1 " alt="Image" />';
            $img2= '<img style="width:100%;" src="'.HOST_ROOT_IMAGE_PATH.'systemAdmin-header-img.jpg" alt="Image" />';
            if(!empty($archive_details['prop_details']['archive_logo_image'])){
                $email_template = str_replace('#archive_logo#',$thoumb, $email_template);  
            }else{
                $email_template = str_replace('#archive_logo#','', $email_template);    
            }
            if(isset($share_type) && $share_type == 'scrapbook'){
                $linkUrl = (isset($_SESSION['aib']['user_data']['user_type']) &&  $_SESSION['aib']['user_data']['user_type'] != 'U') ? 'home.php' : 'people.php';
                $userDetailsForArchive = getUserProfile($_SESSION['aib']['user_data']['user_id'],'');
                $email_data['subject'] = 'ArchiveInABox: Scrapbook shared with you !!!';
                $url ='<a style="word-break:break-all;" href="'.HOST_PATH.$linkUrl.'?folder_id='.$userDetailsForArchive['user_top_folder'].'&show_scrapbook=1" target="_blank">'.HOST_PATH.$linkUrl.'?folder_id='.$userDetailsForArchive['user_top_folder'].'</a>';
                $email_template = str_replace('#item_images#',$img2, $email_template);
            }else{
                $url ='<a style="word-break:break-all;" href="'.HOST_PATH.'item-details.php?folder_id='.$_POST['id'].'&share=1" target="_blank">'.HOST_PATH.'item-details.php?folder_id='.$_POST['id'].'&share=1</a>';
                $email_data['subject'] = 'ArchiveInABox: '.$name.' has shared an archive item !'; 
                $email_template = str_replace('#item_images#',$img1, $email_template);
            }
            $email_template = str_replace('#header_images#',$header_image, $email_template);
            $email_template = str_replace('#url_details#',$url, $email_template);
            $massage_display = '';
            $massage = '';
            if(!empty($_POST['share_massage'])){
              $massage_display = 'Message:';  
              $massage = $_POST['share_massage'];
            }
            $email_template = str_replace('#share_massage#',$massage, $email_template);
            $email_template = str_replace('#massage_display#',$massage_display, $email_template);
            $email = sendMail($email_data,$email_template);
            if($email){
                if($share_type == 'records'){
                    $responseData = array('status' => 'success', 'message' => 'Record shared successfully'); 
                }else{
                    $responseData = array('status' => 'success', 'message' => 'Scrapbook shared successfully');    
                }
            }
        }
        print json_encode($responseData);
        break;
    case 'get_archive_prop_details':
            $archive_id = $_POST['archive_id'];
            $user_id = (isset($_POST['user_id']) && $_POST['user_id'] != '') ? $_POST['user_id'] : $_SESSION['aib']['user_data']['user_id'];
            $tags = getItemTags($archive_id);
            $userProfileData = [];
            if(isset($user_id) && !empty($user_id)){
                $userProfileData = getUserProfileById($user_id);
                $userProfileData['user_properties'] = getUsersAllProperty($user_id);
            }
            $itemDetails = getItemDetailsWithProp($archive_id);
            $itemDetails['properties'] = array_map('stripslashes',$itemDetails['properties']);
            $itemDetails['prop_details'] = array_map('stripslashes',$itemDetails['prop_details']);
            $itemDetails['properties']['archive_details_description'] = stripslashes($itemDetails['properties']['archive_details_description']);
            $data = array_merge($userProfileData,$itemDetails);
            $data['prop_details']['archive_tags'] = $tags;
            print json_encode($data);
        break;
    case 'update_user_details':
        parse_str($_POST['formData'], $postData);
         if (!empty($postData)) {
            $postData['profile_completed'] = 'yes';
            $userId = $_SESSION['aib']['user_data']['user_id'];
            $title = $postData['firstName'].' '.$postData['lastName'];
            $old_email = $_SESSION['aib']['user_data']['user_prop']['email'];
            $to = $postData['email'];
            if(trim($old_email)!= trim($to)){
                    $email_content = 'Your email-id have been changed from '.$old_email.' to '.$to;
                }else{
                    $email_content='';
                }
            $email_data = [];
            $email_data['to'] = $to;
            $email_data['from'] =  ADMIN_EMAIL;  
            $email_data['reply'] = ADMIN_EMAIL;
            $email_data['subject']='ArchiveInBox update user details';
            $email_template = file_get_contents(EMAIL_PATH."/update.html");
            $email_template = str_replace('#username#',$title,$email_template);
            $email_template = str_replace('#email_change#',$email_content,$email_template);
            
			$removeKeys = array('username', 'user_id','password','confirm_pass');
			foreach($removeKeys as $key) {
				unset($postData[$key]);
			}
			$i = 0;
			$postData['occasional_update'] = isset($postData['occasional_update'])?$postData['occasional_update']:'N';
			foreach($postData as $key=>$val){
				$userProperty[$i]['name'] = $key;
				$userProperty[$i]['value'] = ($val!= '') ? $val : ' ' ;   
				$i++; 
			}
			$postRequestData = array(
				"_key" => APIKEY,
				"_session" => $sessionKey,
				"_user" =>$userId,
				"_op" => "set_profile_prop_batch",
				"user_id" => $userId,
				"property_list" => json_encode($userProperty)
			);
			$apiUserPropertyResponse = aibServiceRequest($postRequestData, "users");
			if($apiUserPropertyResponse['status']=='OK'){
				  $email = sendMail($email_data,$email_template);
						if($email){
						 $responseData = array('status' => 'success', 'message' => 'Your profile updated successfully.');
						}
			  } 
         }
        print json_encode($responseData);
        break;
    case'password_change':
        parse_str($_POST['formData'], $postData);
        $userId = $_SESSION['aib']['user_data']['user_id'];
        $user_name = $_SESSION['aib']['user_data']['user_title'];
        $to = $_SESSION['aib']['user_data']['user_prop']['email'];
        $email_data = [];
        $email_data['to'] = $to;
        $email_data['from'] =  ADMIN_EMAIL;  
        $email_data['reply'] = ADMIN_EMAIL;
        $email_data['subject']='ArchiveInBox changed password';
        $email_template = file_get_contents(EMAIL_PATH."/change_pass.html");
        $email_template = str_replace('#username#',$user_name,$email_template);
        $url = 'id='.$user_id.'&flg=undo_email&'.$old_email.'&undo';
        $link = '<a href="javascript:void(0);" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to Undo</a>'; 
        $email_template = str_replace('#undo_pass#',$link,$email_template);
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'login',
            '_user' => 1,
            "user_login" => $_SESSION['aib']['user_data']['user_login'],
            "user_pass" => $postData['old_pass']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'users');
        if($apiResponse['status']=='OK'){
                $postPassData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => "update_profile",
                    "_user" => 1,
                    "user_id" => $userId,
                    "new_user_password" =>$postData['new_pass'] 
                );
              $apiChangePassword = aibServiceRequest($postPassData, 'users');
                    if($apiChangePassword['status']=='OK'){
                    $email = sendMail($email_data,$email_template);
                        if($email){
                        $responseData = array('status' => 'success', 'message' => 'Your password has been changed successfully.');       
                          }
                    }
        }else{
           $responseData = array('status' => 'error', 'message' => 'Your old password is not correct.');    
        }
        print json_encode($responseData);
        break;
        
    case 'get_user_details':
        $user_id = $_POST['user_id'];
        $userProfileData = [];
        $postData = array(
             "_key" => APIKEY,
             "_session" => $sessionKey,
             "_op" => "get_profile",
             "_user" => 1,
             "user_id" => $user_id
         );
        $apiResponse = aibServiceRequest($postData, 'users');
        if($apiResponse['status']=='OK'){ 
            $postPropData = array(
                   "_key" => APIKEY,
                   "_session" => $sessionKey,
                   "_op" => "list_profile_prop",
                   "_user" => 1,
                   "user_id" => $user_id
               );
             $apiProResponse = aibServiceRequest($postPropData, 'users');
             if($apiProResponse['status']=='OK'){
                $itemDetails = $apiProResponse['info']['records'] ;
                foreach($apiProResponse['info']['records'] as $key=>$dataArray){
                 $userProfileData[$dataArray['property_name']] = $dataArray['property_value'];
                }
            }
              $user_tiemstamp = isset( $userProfileData['timestamp'])? $userProfileData['timestamp']:'';
              $terms_condition = getSuperAdminAllProperty(1); 
              if($terms_condition['timestamp'] > $userProfileData['timestamp'] ){ $userProfileData['term_service'] = ''; }
            
        }
        print json_encode($userProfileData);
        break;
    case 'update_items':
        $item_type = $_POST['item_type'];
        $item_id = $_POST['item_id'];
        $parent_id = $_POST['parent_id'];
        $treeDataArray = getTreeData($parent_id);
        $itemDetails = getItemDetailsWithProp($item_id);
        if ($item_type == 'RE' || $item_type == 'IT') {
            if ($item_type == 'IT') {
                $item_id = $parent_id;
            }
            $parentDetails = getItemData($parent_id);
            $itemDetails['parent_details'] = $parentDetails;
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $item_id,
                "opt_get_files" => 'Y',
                "opt_get_first_thumb" => 'Y'
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK') {
                if (!empty($apiResponse['info']['records'])) {
                    foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                        foreach ($dataArray["files"] as $FileRecord) {
                            if ($FileRecord["file_type"] == 'tn') {
                                $ThumbID = $FileRecord["file_id"];
                                $itemDetails['files_records'][$key]['tn_file_id'] = $FileRecord["file_id"];
                                continue;
                            }
                            if ($FileRecord["file_type"] == 'pr') {
                                $itemDetails['files_records'][$key]['pr_file_id'] = $FileRecord["file_id"];
                                continue;
                            }
                        }
                    }
                }
            }
        }
        $display_image_pr_id = '';
        if ($item_type == 'RE') {
            $display_image_pr_id = $itemDetails['files_records'][0]['pr_file_id'];
        }
        if ($item_type == 'IT') {
            $display_image_pr_id = '';
            foreach ($itemDetails['files'] as $fileArray) {
                if ($fileArray["file_type"] == 'pr') {
                    $display_image_pr_id = $fileArray["file_id"];
                    break;
                }
            }
        }
        //print_array($itemDetails, true);
        if ($itemDetails) {
            include_once TEMPLATE_PATH . 'edit_item.php';
        }
        break;
    case 'update_items_by_id':
        parse_str($_POST['formData'], $postData);
        $aditionalProp = array();
        $aditionalProp['propname_1'] = 'sort_by';
        $aditionalProp['propval_1'] = (isset($postData['default_sorting_by']) && $postData['default_sorting_by'] != '') ? $postData['default_sorting_by'] : 'TITLE';
        switch ($postData['item_type']) {
            case 'AR':
                $item_title = $postData['item_title'];
                $aditionalProp['propname_2'] = 'code';
                $aditionalProp['propval_2'] = $postData['item_code'];
                $aditionalProp['propname_3'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_3'] = $postData['display_archive_level_advertisements'];
                $item_class = 'ar';
                break;
            case 'CO':
                $item_title = $postData['collection_name'];
                $aditionalProp['propname_2'] = 'visible_to_public';
                $aditionalProp['propval_2'] = $postData['visible_to_public'];
                $aditionalProp['propname_3'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_3'] = $postData['display_archive_level_advertisements'];
                $item_class = 'col';
                break;
            case 'SG':
                $item_title = $postData['sub_group_name'];
                $aditionalProp['propname_2'] = 'visible_to_public';
                $aditionalProp['propval_2'] = $postData['visible_to_public'];
                $aditionalProp['propname_3'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_3'] = $postData['display_archive_level_advertisements'];
                $item_class = 'sg';
                break;
            case 'RE':
            case 'IT':
                $item_title = $postData['record_title'];
                break;

            default :
                $item_title = $postData['item_title'];
                $aditionalProp['propname_2'] = 'code';
                $aditionalProp['propval_2'] = $postData['item_code'];
                $item_class = strtolower($postData['item_type']);
                break;
        }
        $responseData = array('status' => 'error', 'message' => 'All fields are required');
        if (!empty($postData)) {
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            $postDataItem = array(
                '_key' => APIKEY,
                '_user' => $user_id,
                '_op' => 'modify_item',
                '_session' => $sessionKey,
                'obj_id' => $postData['item_id'],
                'item_title' => $item_title,
                'opt_allow_dup' => 'Y',
            );
            $apiResponse = aibServiceRequest($postDataItem, 'browse');
            $responseData = array('status' => 'error', 'message' => 'Item not updated, Please try again');
            if ($apiResponse['status'] == 'OK') {
                if (isset($postData['form_fields']) && count($postData['form_fields']) > 0) {
                    $ItemFieldsStatus = updateItemFieldsData($postData['form_fields'], $postData['item_id']);
                }
                if (!empty($aditionalProp)) {
                    $archive_id = $apiResponse['info'];
                    $aditionalProp['_key'] = APIKEY;
                    $aditionalProp['_user'] = $user_id;
                    $aditionalProp['_op'] = 'set_item_prop';
                    $aditionalProp['_session'] = $sessionKey;
                    $aditionalProp['obj_id'] = $archive_id;
                    $apiResponse = aibServiceRequest($aditionalProp, 'browse');
                }
                $responseData = array('status' => 'success', 'message' => 'Item updated successfully.');
            }
        }
        print json_encode($responseData);
        break;
    case 'delete_item_by_id':
        $item_id = $_POST['item_id'];
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $responseData = array('status' => 'success', 'message' => 'Item not deleted, Please try again');
        if ($item_id) {
            $postDataItem = array(
                '_key' => APIKEY,
                '_user' => $user_id,
                '_op' => 'delete_item',
                '_session' => $sessionKey,
                'obj_id' => $item_id
            );
            $apiResponse = aibServiceRequest($postDataItem, 'browse');
            if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Item deleted successfully.');
            }
        }  
        print json_encode($responseData);
        break;
    case 'assistant_index_data':
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $uncompleteRequestData = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'data_entry_waiting',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
        $uncompleteResponseData = [];
        $current_uncomplete_id = '';
        unset($_SESSION['aib']['uncomplete_data']);
        if (!empty($uncompleteResponse['info']['records'])) {
            foreach ($uncompleteResponse['info']['records'] as $key => $uncompleteData) {
                if ($current_uncomplete_id != $uncompleteData['item_parent_id']) {
                    $current_uncomplete_id = $uncompleteData['item_parent_id'];
                    $parentDetails = getItemData($uncompleteData['item_parent_id']);
                    $itemTreeData = getTreeData($uncompleteData['item_parent_id'], true);
                    $uncompleteResponseData[$uncompleteData['item_parent_id']]['sub_group'] = $parentDetails['item_title'];
                    $uncompleteResponseData[$uncompleteData['item_parent_id']]['archive'] = $itemTreeData[1]['item_title'];
                }
                $recordItems = getAllItemRecords($uncompleteData['item_id']);
                $uncompleteData['item_records'] = $recordItems;
                $uncompleteResponseData[$uncompleteData['item_parent_id']]['list'][] = $uncompleteData;
            }
        }
        $completeRequestData = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'data_entry_complete',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        $completeResponse = aibServiceRequest($completeRequestData, 'dataentry');
        $completeResponseData = [];
        $current_complete_id = '';
        if (!empty($completeResponse['info']['records'])) {
            foreach ($completeResponse['info']['records'] as $key => $completeData) {
                if ($current_complete_id != $completeData['item_parent_id']) {
                    $current_complete_id = $completeData['item_parent_id'];
                    $parentDetails = getItemData($completeData['item_parent_id']);
                    $itemTreeData = getTreeData($completeData['item_parent_id'], true);
                    $completeResponseData[$completeData['item_parent_id']]['sub_group'] = $parentDetails['item_title'];
                    $completeResponseData[$completeData['item_parent_id']]['archive'] = $itemTreeData[1]['item_title'];
                }
                $completeResponseData[$completeData['item_parent_id']]['list'][] = $completeData;
            }
        }
        include_once TEMPLATE_PATH . 'assistant_index_listing.php';
        break;
    case 'list_get_item':
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $uncompleteRequestData = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'data_entry_waiting',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
        foreach ($uncompleteResponse['info']['records'] as $key => $item) {
            $getitemRequestData = array(
                '_key' => APIKEY,
                '_user' => $user_id,
                '_op' => 'get',
                '_session' => $sessionKey,
                'obj_id' => $item['item_id']
            );
            $apiResponse = aibServiceRequest($getitemRequestData, 'browse');
            $mydataarray[] = $apiResponse['info']['records'];
        }
        include 'get_items_list.php';
        break;
    /*  GetItem items get lists Section End  */
    case 'edit_get_item':
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'modify_item',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'item_title' => $_POST['edit_items_name'],
            'obj_id' => $_POST['edit_item_id']
        );
        $apiResponse = aibServiceRequest($requestPostData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'Item updated successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Item not updated.');
        }
        print json_encode($responseData);
        break;
    case 'edit_assistant_uncomplete_data':
        $parent_id = $_POST['parent_id'];
        $item_id = $_POST['item_id'];
        $treeDataArray = getTreeData($parent_id);
        if (!$item_id) {
            getUncompleteDataForParent($parent_id);
            $uncompleteParentDataList = $_SESSION['aib']['uncomplete_data'][$parent_id];
            if (!empty($uncompleteParentDataList)) {
                $uncompleteListData = array();
                $count = 0;
                foreach ($uncompleteParentDataList as $key => $uncompleteList) {
                    if ($count > 0)
                        break;
                    $uncompleteItemDetails = getUncompleteItemDetails($uncompleteList['item_id'], $parent_id);
                    if ($uncompleteItemDetails) {
                        $uncompleteListData[] = $uncompleteItemDetails;
                    }
                    $count++;
                }
            }
        } else {
            $uncompleteItemDetails = getUncompleteItemDetails($item_id, $parent_id);
            if ($uncompleteItemDetails) {
                $uncompleteListData[] = $uncompleteItemDetails;
            }
        }

        $itemDetails = $uncompleteListData[0];
        $item_id = $itemDetails['item_id'];
        $item_type = $itemDetails['item_type'];
        $display_image_pr_id = '';
        if ($item_type == 'RE') {
            if (!empty($itemDetails['files_records'])) {
                $display_image_pr_id = $itemDetails['files_records'][0]['pr_file_id'];
            }
        }
        if ($item_type == 'IT') {
            $display_image_pr_id = '';
            foreach ($itemDetails['files'] as $fileArray) {
                if ($fileArray["file_type"] == 'pr') {
                    $display_image_pr_id = $fileArray["file_id"];
                    break;
                }
            }
        }
        $itemDetails['tags'] = getItemTags($itemDetails['item_id']);
        include_once TEMPLATE_PATH . 'edit_item.php';
        break;
    case 'mark_item_as_complete':
        $parent_id = $_POST['parent_id'];
        if ($parent_id) {
            $requestPostData = array(
                '_key' => APIKEY,
                '_session' => $sessionKey,
                '_op' => 'data_entry_mark_complete',
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                'obj_id' => $parent_id
            );
            $apiResponse = aibServiceRequest($requestPostData, 'dataentry');
        }
        break;
    case 'update_assistant_item':
        parse_str($_POST['formData'], $postData);
        $item_title = $postData['record_title'];
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $postDataItem = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'modify_item',
            '_session' => $sessionKey,
            'obj_id' => $postData['item_id'],
            'item_title' => $item_title,
            'opt_allow_dup' => 'Y',
        );
        $apiResponse = aibServiceRequest($postDataItem, 'browse');
        if ($apiResponse['status'] == 'OK') {
            if (isset($postData['form_fields']) && count($postData['form_fields']) > 0) {
                $ItemFieldsStatus = updateItemFieldsData($postData['form_fields'], $postData['item_id']);
            }
            if(isset($postData['record_tags'])){
                $itemTagsStatus = updateItemTags($postData['record_tags'], $postData['item_id']);
            }
            $markCompletedStatus = markItemAsCompleted($postData);
            if ($markCompletedStatus) {
                $nextItemId = getNextUncompleteItemToEdit();
            }
            print json_encode(array('status' => $nextItemId['status'], 'item_id' => $nextItemId['item_id'], 'parent_id' => $nextItemId['parent_id']));
        } else {
            print json_encode(array('status' => 'error', 'message' => $apiResponse['info'], 'item_id' => '', 'parent_id' => ''));
        }
        break;
    case 'upload_croped_image':
        $response = array('status' => 'error', 'image' => '');
        $apiRequestDataItem = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'set_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $_POST['archive_id']
        );
        switch ($_REQUEST['type']) {
            case 'logo':
                $imageObj = $_FILES['archive_logo_image'];
                $x_coordinate = $_POST['archive_logo_x'];
                $y_coordinate = $_POST['archive_logo_y'];
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'logo');
                $apiRequestDataItem['propname_1'] = 'archive_logo_image';
                $apiRequestDataItem['propval_1'] = $uploadedImage;
                $response = array('status' => 'success', 'image' => IMAGE_TARGET_PATH . $uploadedImage, 'type' => 'logo');
                break;
            case 'banner':
                $imageObj = $_FILES['archive_header_image'];
                $x_coordinate = $_POST['archive_header_x'];
                $y_coordinate = $_POST['archive_header_y'];
                $crop_height = isset($_POST['baner_crop_height'])?$_POST['baner_crop_height']:400;
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'banner',$crop_height);
                $apiRequestDataItem['propname_1'] = 'archive_header_image';
                $apiRequestDataItem['propval_1'] = $uploadedImage;
                $response = array('status' => 'success', 'image' => IMAGE_TARGET_PATH . $uploadedImage, 'type' => 'banner');
                break;
            case 'content':
                $imageObj = $_FILES['archive_details_image'];
                $x_coordinate = $_POST['archive_details_x'];
                $y_coordinate = $_POST['archive_details_y'];
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'content');
                $apiRequestDataItem['propname_1'] = 'archive_details_image';
                $apiRequestDataItem['propval_1'] = $uploadedImage;
                $response = array('status' => 'success', 'image' => IMAGE_TARGET_PATH . $uploadedImage, 'type' => 'content');
                break;
            case 'archive_group_thumb':
                $imageObj = $_FILES['archive_group_thumb'];
                $x_coordinate = $_POST['archive_group_thumb_x'];
                $y_coordinate = $_POST['archive_group_thumb_y'];
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'archive_group_thumb');
                $apiRequestDataItem['propname_1'] = 'archive_group_thumb';
                $apiRequestDataItem['propval_1'] = $uploadedImage;
                $response = array('status' => 'success', 'image' => IMAGE_TARGET_PATH . $uploadedImage, 'type' => 'archive_group_thumb');
                break;
            case 'archive_group_details_thumb':
                $imageObj = $_FILES['archive_group_details_thumb'];
                $x_coordinate = $_POST['archive_group_details_thumb_x'];
                $y_coordinate = $_POST['archive_group_details_thumb_y'];
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'archive_group_details_thumb');
                $apiRequestDataItem['propname_1'] = 'archive_group_details_thumb';
                $apiRequestDataItem['propval_1'] = $uploadedImage;
                $response = array('status' => 'success', 'image' => IMAGE_TARGET_PATH . $uploadedImage, 'type' => 'archive_group_details_thumb');
                break;
            case 'historical_connection_logo':
                $imageObj = $_FILES['historical_connection_logo'];
                $x_coordinate = $_POST['historical_connection_logo_x'];
                $y_coordinate = $_POST['historical_connection_logo_y'];
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'historical_connection_logo');
                $apiRequestDataItem['propname_1'] = 'historical_connection_logo';
                $apiRequestDataItem['propval_1'] = $uploadedImage;
                $response = array('status' => 'success', 'image' => IMAGE_TARGET_PATH . $uploadedImage, 'type' => 'historical_connection_logo');
                break;
        }
        $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        print json_encode($response);
        break;
		
	case 'forget_password_email':
        parse_str($_POST['formData'], $postData);
		$responseData = array('status' => 'error', 'message' => 'Some things went wrong! Please try again.');
		$sessionKey = $_SESSION['aib']['session_key']; 
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user'=>1,
            '_op'=>'get_profile',
            '_session' => $sessionKey,
            'user_login'=>$postData['forget_user_id']
            
        ); 
        $apiResponse = aibServiceRequest($apiRequestData,'users');  
		$emailid = '';
		if($apiResponse['status'] ==  'OK'){ 
			$userEmail = getUsersAllProperty($apiResponse['info']['user_id']); 
		 	$email_data = [];
			$email_template = file_get_contents(EMAIL_PATH."/forget_password.html"); 
			$email_data['to'] = $userEmail['email'];
			$email_data['from'] =  ADMIN_EMAIL;  
			$email_data['reply'] = ADMIN_EMAIL;
			$email_data['subject'] = 'ArchiveInABox: Change Password';
			$email_template = str_replace('#username#',$postData['forget_user_id'],$email_template);
			$url = 'id='.$apiResponse['info']['user_id'].'&type=forget';
			$link = '<a href="'.HOST_PATH.'thank-you.php?'.urlencode($url).'" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to Reset Your Password</a>';
			$email_template = str_replace('#confirm_email#',$link,$email_template);
			$email = sendMail($email_data,$email_template);	
			if($email){
				$responseData = array('status'=>'success','message'=>'Please check your registered email  to change your password');
            }
		}
		else{
			$responseData = array('status'=>'error','message'=>'Username is not found !');
		}
		print json_encode($responseData);
        break;	
    case 'get_list_data':
        $select_type = (isset($_POST['select_type']) && !empty($_POST['select_type'])) ? $_POST['select_type'] :'';
        $archive_id = (isset($_POST['archive_group_id']) && $_POST['archive_group_id'] != '' ) ? $_POST['archive_group_id'] : '';    
        $request_data_type = $_POST['request_data_type'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'req_list',
            '_session' => $sessionKey,
            'req_type' => $request_data_type
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'custreq'); 
        if ($apiResponse['status'] == 'OK') {
            $listDataArray = $apiResponse['records']; 
            $finalDataArray = array();
            if (!empty($listDataArray)) {
                $count = 0;
                foreach ($listDataArray as $key => $dataArray) {
                    $item_id = $dataArray['req_item'];

					if($_SESSION['aib']['user_data']['user_top_folder']>1 and $_SESSION['aib']['user_data']['user_login']!='root' and $_SESSION['aib']['user_data']['user_type']!='R')
					{
						if($item_id!=$_SESSION['aib']['user_data']['user_top_folder'])
							{
								continue;
								}
						}
                    $itemArchiveDetails = getTreeData($item_id, true);   
                    $item_archive_group_id = isset($itemArchiveDetails[1]) ? $itemArchiveDetails[1]['item_id'] : $item_id; 
                   
                    $image_link ='';
                    if ($request_data_type == 'RP') {
                            $itemDetails = getItemData($item_id);
                            if (!empty($itemDetails['files'])) {
                                foreach ($itemDetails['files'] as $key => $fileDataArray) {
                                    if ($fileDataArray['file_type'] == 'pr') {
                                        $file_id = $fileDataArray['file_id'];
                                        $image_link = THUMB_URL . '?id=' . $file_id . '&download=1';
                                    }
                                }
                            } else {
                                 $image_link = 'not found';
                            }
                        }
                $infoData = (array) json_decode($dataArray['req_info']); 
                
                if($select_type == 'Archive'){
                if ( !empty($archive_id) && $item_archive_group_id == $archive_id) {
                    $finalDataArray[$count] = array_merge($dataArray, $infoData);
                    $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']);
		    $finalDataArray[$count]['image_link'] =  $image_link;
                    if(!isset($infoData['item_link'])){
                            $finalDataArray[$count]['item_link'] = '...';
                    } 
                     $count ++;
                  }else{unset($finalDataArray[$count]);}
                }else if($select_type == 'Corporate'){
                         if($infoData['search_type']=='C'){
                            $finalDataArray[$count] = array_merge($dataArray, $infoData);
                            $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']);
                            $finalDataArray[$count]['image_link'] =  $image_link;
                            if(!isset($infoData['item_link'])){
                            $finalDataArray[$count]['item_link'] = '...';
                             } 
                            $count ++;  
                         }else{unset($finalDataArray[$count]);}
                }
                else{
                    $finalDataArray[$count] = array_merge($dataArray, $infoData);
                    $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']); 
		    $finalDataArray[$count]['image_link'] =  $image_link;
                     if(!isset($infoData['item_link'])){
                      $finalDataArray[$count]['item_link'] = '...';
                    } 
                      $count ++;
                   } 
                }
            }
        }
        print json_encode($finalDataArray);
        break;
		
	case 'get_report_list_data':
        $select_type = (isset($_POST['select_type']) && $_POST['select_type']!='' )?$_POST['select_type'] : '';    
//        $archive_id = (isset($_POST['archive_group_id']) && $_POST['archive_group_id'] != '' ) ? $_POST['archive_group_id'] : $_SESSION['aib']['user_data']['user_top_folder']; 
         $archive_id = (isset($_POST['archive_group_id']) && $_POST['archive_group_id'] != '' ) ? $_POST['archive_group_id'] : ''; 
         $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'req_list',
            '_session' => $sessionKey,
            'req_type' => $_POST['request_data_type']
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'custreq');  
        if ($apiResponse['status'] == 'OK') {
            $listDataArray = $apiResponse['records'];   
            $finalDataArray = array();
            if (!empty($listDataArray)) {
                   $count = 0;
                   foreach ($listDataArray as $key => $dataArray) {
					   
                    $item_id = $dataArray['req_item'];
					if($_SESSION['aib']['user_data']['user_top_folder']>1 and $_SESSION['aib']['user_data']['user_login']!='root' and $_SESSION['aib']['user_data']['user_type']!='R')
					{
						if($item_id!=$_SESSION['aib']['user_data']['user_top_folder'])
							{
								continue;
								}
						} 
                     if ($request_data_type == 'RC' || $request_data_type == 'RD') {
                            $itemDetails = getItemData($item_id);
                            if (!empty($itemDetails['files'])) {
                                foreach ($itemDetails['files'] as $key => $fileDataArray) {
                                    if ($fileDataArray['file_type'] == 'pr') {
                                        $file_id = $fileDataArray['file_id'];
                                        $finalDataArray[$count]['image_link'] = THUMB_URL . '?id=' . $file_id . '&download=1';
                                    }
                                }
                            } else {
                                $finalDataArray[$count]['image_link'] = 'not found';
                            }
                        }
                        $infoData = (array) json_decode($dataArray['req_info']);
                        if($select_type == 'Archive'){
                            $del_item = (array) json_decode($dataArray['req_info']);    
                              if (!empty($archive_id) &&  $del_item['item_id'] == $archive_id) {   
                                  $finalDataArray[$count] = array_merge($dataArray, $infoData);  
                                  $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']);
                                   $count ++; 
                              } else {
                                  unset($finalDataArray[$count]);
                              } 
                        }else if($select_type == 'People'){
                            if(isset($infoData['search_type']) && $infoData['search_type'] == 'P') {
                             if(!empty($dataArray) && !empty($infoData) ){
                                 $finalDataArray[$count] = array_merge($dataArray, $infoData);  
                                 $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']);
                                  $count ++;
                            }else{ unset($finalDataArray[$count]);}  
                               }
                        } else{
                           $finalDataArray[$count] = array_merge($dataArray, $infoData); 
                           $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']);
                        $count ++;
                        }
                } 
            }
        }
        print json_encode($finalDataArray);
        break;	
    case 'get_data_by_request_id':
        $request_id = $_POST['request_id'];
        if ($request_id) {
            $apiRequestData = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'req_get',
                '_session' => $sessionKey,
                'req_id' => $request_id
            );
            $apiResponse = aibServiceRequest($apiRequestData, 'custreq');
            if ($apiResponse['status'] == 'OK') {
                $dataArray = $apiResponse['records'][0];
                $infoData = (array) json_decode($dataArray['req_info']);
                $dataArray = array_merge($dataArray, $infoData);
            }
        }
        if (isset($_POST['type']) && $_POST['type'] == 'edit_request') {
            include_once TEMPLATE_PATH . 'edit_request.php';
        } else {
            include_once TEMPLATE_PATH . 'request_details.php';
        }
        break;
    case 'delete_request_data':
        $request_id = $_POST['request_id'];
        $responseData = array('status' => 'error', 'message' => 'Request Id is required.');
        if ($request_id) {
            $apiRequestData = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'req_del',
                '_session' => $sessionKey,
                'req_id' => $request_id
            );
            $apiResponse = aibServiceRequest($apiRequestData, 'custreq');
            $responseData = array('status' => 'error', 'message' => 'Some things went wrong.');
            if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Request deleted successfully.');
            }
        }
        print json_encode($responseData);
        break;
    case 'update_request_data':
        parse_str($_POST['formData'], $postData);
        $request_id = $postData['request_id'];
        switch ($postData['request_type']) {
            case 'RP':
                $info = array('item_link' => $postData['request_link'], 'comment' => $postData['request_comments'], 'page_used' => $postData['request_page_used']);
                break;
            case 'RD':
                $info = array('item_link' => $postData['request_link'], 'comment' => $postData['request_comments']);
                break;
            case 'CT':
                $info = array('item_link' => $postData['request_subject'], 'comment' => $postData['request_comments']);
                break; 
        }
        $user_ip_address = getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?: getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR');
        if (!empty($postData)) {
            $requestPostData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "req_mod",
                "req_id" => $request_id,
                "req_name" => $postData['request_name'],
                "req_phone" => $postData['request_phone'],
                "req_email" => $postData['request_email'],
                "req_ipaddr" => $user_ip_address,
                "req_info" => json_encode($info)
            );
            $apiResponse = aibServiceRequest($requestPostData, 'custreq');
            $responseData = array('status' => 'error', 'message' => 'Request not updated.');
            if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Request updated successfully.');
            }
        }
        print json_encode($responseData);
        break;
    case 'update_archive_group_status':
        $archive_id = $_POST['archive_id'];
        $current_status = $_POST['current_status'];
        $status = ($current_status == 0) ? 1 : 0;
       
        if ($archive_id) {
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $archive_id,
                'propname_1' => 'status',
                'propval_1' => $status
            );
            if($current_status ==0){
                $archiveUserId = getItemDetailsWithProp($archive_id);  
                $userEmailId = getUsersAllProperty($archiveUserId['properties']['archive_user_id']); 
                $userName = getUserProfileById($archiveUserId['properties']['archive_user_id']); 
            
                $email_data = [];
                $email_template = file_get_contents(EMAIL_PATH."/enable_society.html");  
                $email_template = str_replace('#societyname#',$archiveUserId['item_title'],$email_template); 
                $email_template = str_replace('#username#',$userName['user_login'],$email_template); 
                $link = '<a href="'.HOST_PATH.'admin/login.php" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Login</a>';
                $email_template = str_replace('#login#',$link,$email_template);
                $email_data['to'] = $userEmailId['email'];
                $email_data['reply'] = ADMIN_EMAIL;
                $email_data['from'] =  ADMIN_EMAIL;   
                $email_data['subject'] = 'Society Approved';   
                $sendEmail = sendMail($email_data,$email_template);
            
            }
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        }
        break;
    case 'update_item_current_status':
        $item_id = $_POST['item_id'];
        $current_status = $_POST['current_status'];
        $status = ($current_status == 0) ? 1 : 0;
        if($item_id){
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $item_id,
                'propname_1' => 'visible_to_public',
                'propval_1' => $status
            );
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        }
        break;
    case 'update_archive_group_ebay_status':
        $archive_id = $_POST['archive_id']; 
        $status = $_POST['current_ebay_status'];
       
        if ($archive_id) {
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $archive_id,
                'propname_1' => 'ebay_status',
                'propval_1' => $status
            ); 
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');       
        }
        break;
    case 'update_archive_publish_status':
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_DEFAULT, FILTER_SANITIZE_SPECIAL_CHARS);
        $status     = filter_input(INPUT_POST, 'current_publish_status', FILTER_DEFAULT, FILTER_SANITIZE_SPECIAL_CHARS);
        if ($item_id) {
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $item_id,
                'propname_1' => 'publish_status',
                'propval_1' => $status
            ); 
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse'); 
            echo "<pre>"; print_r($apiResponse); echo "</pre>"; exit;
        }
        break;
        
    case 'change_request_status':
        $request_id = $_POST['request_id'];
        $status = $_POST['status'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'req_get',
            '_session' => $sessionKey,
            'req_id' => $request_id
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'custreq');
        if ($apiResponse['status'] == 'OK') {
            $dataArray = $apiResponse['records'][0];
            $infoData = (array) json_decode($dataArray['req_info']); 
			if(isset($_POST['folder_id'])){
				$item_par_id = getTreeData($_POST['folder_id']);  
				$infoData['item_id'] = $item_par_id[1]['item_id'];
			}
			
            $requestPostData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "req_mod",
                "req_id" => $request_id,
		'req_status'=>$status,
                "req_info" => json_encode($infoData)
            );
            $apiResponse = aibServiceRequest($requestPostData, 'custreq');
            $responseData = array('status' => 'error', 'message' => 'Request not updated.');
            if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Request updated successfully.');
            }
        }
        print json_encode($responseData);
        break;
		
	case 'change_report_status':
        $request_id = $_POST['request_id']; 
		$responseData = array('status' => 'error', 'message' => 'Request not updated.');
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'req_mod',
            '_session' => $sessionKey,
            'req_id' => $request_id,
			'req_status'=>$_POST['status']
        ); 
        $apiResponse = aibServiceRequest($apiRequestData, 'custreq');  
        if ($apiResponse['status'] == 'OK') {  
                $responseData = array('status' => 'success', 'message' => 'Your request has been updated successfully.');
        }
        print json_encode($responseData);
        break;	
    case 'admin_assistant_assignment':
        $assistant_id = $_POST['assistant_id'];
        $archive_group_id = $_POST['archive_group_id'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "get_profile",
            "user_id" => $assistant_id
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            $assistantAssignedArchive = $apiResponse['info']['user_top_folder'];
            $archiveDetails = getItemData($assistantAssignedArchive);
            $assistantDetails = $apiResponse['info'];
//            if ($archiveDetails['item_type'] == 'AG') {
//                $allSubgroupsArray = getAGAllSubgroup($assistantAssignedArchive);
//            } else {
//                $allSubgroupsArray = getAllSubgroup($assistantAssignedArchive);
//            }
            $assignedSubGroups = getAssignedSubGroups($assistant_id);
            include_once TEMPLATE_PATH . 'assistant_management.php';
        }
        break;
    case 'get_assistant_completed_record_list':
        $parent_id = $_POST['parent_id'];
        $type = $_POST['type'];
        $listDataArray = [];
        if ($parent_id) {
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $parent_id
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK') {
                $listDataArray = $apiResponse['info']['records'];
            }
        }
        if ($type == 'record') {
            $completeRequestData = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'data_entry_complete',
                '_session' => $sessionKey,
                'user_id' => $_SESSION['aib']['user_data']['user_id']
            );
            $completeResponse = aibServiceRequest($completeRequestData, 'dataentry');
            $assistantCompletedData = $completeResponse['info']['records'];
            $mainArray = Array();
            if (!empty($assistantCompletedData)) {
                foreach ($listDataArray as $listKey => $listData) {
                    foreach ($assistantCompletedData as $completedData) {
                        if ($completedData['item_id'] == $listData['item_id']) {
                            $mainArray[] = $listData;
                            break;
                        }
                    }
                }
            }
            $listDataArray = $mainArray;
        }
        print json_encode($listDataArray);
        break;
    case 'edit_assistant_completed_data':
        $record_id = $_POST['record_id'];
        $item_id = $_POST['item_id'];
        $treeDataArray = getTreeData($record_id);
        if (!$item_id) {
            $itemDetails = getUncompleteItemDetails($record_id);
        } else {
            $itemDetails = getUncompleteItemDetails($item_id, $record_id);
        }
        $item_id = $itemDetails['item_id'];
        $item_type = $itemDetails['item_type'];
        $display_image_pr_id = '';
        if ($item_type == 'RE') {
            if (!empty($itemDetails['files_records'])) {
                $display_image_pr_id = $itemDetails['files_records'][0]['pr_file_id'];
            }
        }
        if ($item_type == 'IT') {
            $display_image_pr_id = '';
            foreach ($itemDetails['files'] as $fileArray) {
                if ($fileArray["file_type"] == 'pr') {
                    $display_image_pr_id = $fileArray["file_id"];
                    break;
                }
            }
        }
		$itemDetails['tags'] = getItemTags($itemDetails['item_id']);
        include_once TEMPLATE_PATH . 'edit_assistant_completed_item.php';
        break;
    case 'update_assistant_completed_item':
        parse_str($_POST['formData'], $postData);
        $item_title = $postData['record_title'];
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $postDataItem = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'modify_item',
            '_session' => $sessionKey,
            'obj_id' => $postData['item_id'],
            'item_title' => $item_title,
            'opt_allow_dup' => 'Y',
        );
        $apiResponse = aibServiceRequest($postDataItem, 'browse');
        if ($apiResponse['status'] == 'OK') {
            if (isset($postData['form_fields']) && count($postData['form_fields']) > 0) {
                $ItemFieldsStatus = updateItemFieldsData($postData['form_fields'], $postData['item_id']);
            }
			if(isset($postData['record_tags']) && $postData['record_tags'] != ''){
                $itemTagsStatus = updateItemTags($postData['record_tags'], $postData['item_id']);
            }
            print json_encode(array('status' => 'success', 'message' => 'Updated successfully'));
        } else {
            print json_encode(array('status' => 'error', 'message' => 'Not updated'));
        }
        break;
    case 'login_as_other_user':
        $user_id = $_POST['user_id'];
        if ($user_id) {
            $postDataLogin = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => 'get_profile',
                "_user" => (isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1,
                "user_id" => $user_id
            );
            $apiResponse = aibServiceRequest($postDataLogin, 'users');
            $apiUserProp = getUsersAllProperty($apiResponse['info']['user_id']);
            if ($apiResponse['status'] == 'OK') {
				unset($_SESSION['aib']['user_data']['item_title']);
                $alternetUserData = $apiResponse['info'];  
				$itemTitleName = getItemData($alternetUserData['user_top_folder']); 
				$loggedInUserData = $_SESSION['aib']['user_data'];
                $_SESSION['aib']['user_data'] = $alternetUserData; 
                $_SESSION['aib']['user_data']['user_prop'] = $apiUserProp;
                $_SESSION['aib']['previous_user_data'] = $loggedInUserData;
				if($alternetUserData['user_type'] == 'U' || $alternetUserData['user_type'] == 'S' || $alternetUserData['user_type'] == 'A'){
					$_SESSION['aib']['user_data']['item_title'] = $itemTitleName['item_title'];
				}
                print json_encode(array('status' => 'success', 'message' => 'Loggedin successfully.'));
            } else {
                print json_encode(array('status' => 'error', 'message' => 'Not able to loggedin'));
            }
        }
        break;

    case 'resume_user_session':
        $user_id = $_POST['user_id'];
        if ($user_id) {
            $userDataArray = $_SESSION['aib']['previous_user_data'];
            $_SESSION['aib']['user_data'] = $userDataArray;
            unset($_SESSION['aib']['previous_user_data']);
            print json_encode(array('status' => 'success', 'message' => 'Loggedin successfully.'));
        } else {
            print json_encode(array('status' => 'error', 'message' => 'Not able to loggedin'));
        }
        break;
    case 'perform_ocr_on_folder':
        $folder_id = $_POST['folder_id'];
        if ($folder_id) {
            $requestDataArray = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "markocr",
                "item_id_list" => $folder_id,
                "opt_recursive" => 'Y'
            );
            $apiResponse = aibServiceRequest($requestDataArray, 'recordop');
            if ($apiResponse['status'] == 'OK') {
                print json_encode(array('status' => 'success', 'message' => 'OCR performed successfully.'));
            } else {
                print json_encode(array('status' => 'error', 'message' => 'OCR not performed.'));
            }
        }
        break;

    case 'update_archive_registration_details':
        $responseData = array('status' => 'error', 'message' => 'Something went wrong! Please try again');
        parse_str($_POST['formData'], $postData);
        $fieldsArray = array("phoneNumber", "faxNumber", "redactionsEmailAddress", "reprintEmailAddress", "contactEmailAddress",
            "websiteURL", "physicalAddressLine1", "mailingAddressLine1", "physicalAddressLine2", "mailingAddressLine2", "physicalCity", "mailingCity",
            "physicalState", "mailingState", "physicalZip", "mailingZip", "federalTaxIDNumber", "sateTaxIDNumber", "entityOrganization", "entityOrganizationOther",
            "CEO", "CEO_firstName", "CEO_lastName", "CEO_email", "executiveDirector", "executiveDirector_firstName", "executiveDirector_lastName", "executiveDirector_email",
            "precident", "precident_firstName", "precident_lastName", "precident_email", "otherExecutive", "otherExecutive_firstName", "otherExecutive_lastName",
            "otherExecutive_email", "sameAsPhysicalAddress", "boardOfDirectors", "committees", "society_state");
        $apiRequestData = array();
        $email_condition = $postData['user_old_email'] != $postData['register_emailId'];
        $email_change_message ='';
        $email_template = file_get_contents(EMAIL_PATH."/email_change.html");
        $email_data['to'] = $postData['register_emailId'];
        $email_data['from'] = ADMIN_EMAIL;
        $email_data['reply'] = ADMIN_EMAIL;
        $email_data['subject'] = 'ArchiveInABox: Change Email-Id'; 
        $email_template = str_replace('#username#',$postData['register_username1'], $email_template);
        if (isset($postData['archive_id']) && $postData['archive_id'] != '') {
            $archive_id = $postData['archive_id'];
            $count = 1;
            foreach ($fieldsArray as $fields) {
                $apiRequestData['propname_' . $count] = $fields;
                $apiRequestData['propval_' . $count] = (string) $postData[$fields];
                $count++;
            }
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $archive_id,
                'opt_long' => 'Y'
            );
            $finalApiRequestData = array_merge($apiRequestDataItem, $apiRequestData);
            $apiResponse = aibServiceRequest($finalApiRequestData, 'browse');
            $user_title = $postData['title'] . " " . $postData['firstName'] . " " . $postData['lastName'];
            if (!empty($postData) && isset($postData['archive_user_id'])) {
                $requestPostData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'update_profile',
                    '_user' => 1,
                    'user_id' => $postData['archive_user_id'],
                    'new_user_title' => $user_title
                        //'new_user_owner' =>$postData['archive_name']
                );
                $apiResponseProfile = aibServiceRequest($requestPostData, 'users');
                  $url = 'id='.$apiResponseProfile['info'].'&flg=change_email&'.$postData['register_emailId'].'&'.$postData['user_old_email'];
                  $link = '<a href="'.HOST_PATH.'thank-you.php?'.urlencode($url).'" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>'; 
                  $email_template = str_replace('#confirm_email#',$link, $email_template);
                 if($apiResponseProfile['status']=='OK'){
                      $userProperty[0]['name'] = 'timestamp';
                      $userProperty[0]['value'] = $postData['timestamp'];
                    if(isset($postData['term_service']) && !empty($postData['term_service'])){
                        $userProperty[1]['name'] = 'term_service';
                        $userProperty[1]['value'] = $postData['term_service'];
                       if(isset($postData['occasional_update']) && $postData['occasional_update'] == 'Y'){
                           $userProperty[2]['name'] = 'occasional_update';
                           $userProperty[2]['value'] = $postData['occasional_update'];
                       }
                   }
                    $postRequestData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" =>$apiResponseProfile['info'],
                    "_op" => "set_profile_prop_batch",
                    "user_id" => $postData['archive_user_id'],
                    "property_list" => json_encode($userProperty)
                    );
                    $apiResponseProfile = aibServiceRequest($postRequestData, 'users');
                   }
                if (isset($postData['item_id'])) {
                    $postDataItem = array(
                        '_key' => APIKEY,
                        '_user' => $postData['archive_user_id'],
                        '_op' => 'modify_item',
                        '_session' => $sessionKey,
                        'obj_id' => $postData['item_id'],
                        'item_title' => $postData['society_name'],
                        'opt_allow_dup' => 'Y',
                    );
                    $apiResponseItem = aibServiceRequest($postDataItem, 'browse');
                }
            }
            if ($apiResponse['status'] == 'OK') {
               if($email_condition){
                      $email_change_message = 'An email has been sent on the new email id that you have switched to. Please confirm your new email for the change to take effect';
                      $message = 'You have update your email-id successfully. Please click on the link below to confirm change your email';
                      $email_template = str_replace('#message#',$message, $email_template);
                      $email = sendMail($email_data,$email_template);
                      if($email){
                          $responseData = array('status' => 'success', 'message' => 'Archive details updated successfully','change_email_message'=>$email_change_message);
                      }else{
                           $responseData = array('status' => 'error', 'message' => 'Archive details updated successfully but Email have not send');
                      }
                       }
                $responseData = array('status' => 'success', 'message' => 'Archive details updated successfully','change_email_message'=>$email_change_message);       
               }else{
                   $responseData = array('status' => 'error', 'message' => 'Archive details not updated');
               }
               
        }
        print json_encode($responseData);
        break;

    case 'check_duplicate_item':
        $society_state = $_POST['society_state'];
        $society_name = $_POST['society_name'];
        $responseData = array('status' => 'error', 'message' => 'Item not deleted.');
        if ($society_state != "" && $society_name != "") {
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => 1, //$folderId
                "opt_get_property" => 'Y'
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            $records = $apiResponse['info']['records'];
            foreach ($records as $record) {
                if (trim($record['item_title']) == trim($society_name) && trim($record['properties']['society_state']) == trim($society_state)) {
                    echo "false";
                    exit;
                }
            }
        }
        echo "true";
        break;

    case 'assistant_unassign_subgroup': 
        $assistant_id = $_POST['assistant_id'];
        $sub_group_id = $_POST['sub_group_id'];
		foreach($_POST['sub_group_id'] as $sub_group_id){
			if ($assistant_id && $sub_group_id) {
				$requestPostData = array(
					"_key" => APIKEY,
					"_session" => $sessionKey,
					"_op" => 'data_entry_unmark_folders',
					'_user' => $_SESSION['aib']['user_data']['user_id'],
					'user_id' => $assistant_id,
					'item_id_list' => $sub_group_id
				);
				$apiResponseStatus = aibServiceRequest($requestPostData, 'dataentry');
				$responseData = array('status' => 'error', 'message' => 'Something went wrong');
				if ($apiResponseStatus['status'] == 'OK') {
					$responseData = array('status' => 'success', 'message' => 'Sub-group unassigned successfully.');
				}
			}
		}
        print json_encode($responseData);
        break;
    case 'user_archive_listing':
        $item_id = $_POST['item_id'];
        if ($item_id) {
            $postItemData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $item_id
            );
            $apiResponseData = aibServiceRequest($postItemData, 'browse');
            if ($apiResponseData['status'] == 'OK') {
                if (!empty($apiResponseData['info']['records'])) {
                    print json_encode($apiResponseData['info']['records']);
                }
            }
        }
        break;
	case 'user_scrapbookid_record':
        $scrapbook_id = $_POST['scrapbook_id'];  
        if ($scrapbook_id) {
			$responsedata = getItemDetailsWithProp($scrapbook_id);  
			if($responsedata){
				$record['scrap_name'] = $responsedata['item_title'];
				$record['scrap_type'] = $responsedata['prop_details']['scrapbook_type'];
				print json_encode($record);
			} 
        }
        break;	

    case 'check_field_count_public_user':
        $userData = $_SESSION['aib']['user_data'];
        $userCount = 0;
        if ($userData['user_type'] == 'U') {
            $userCount = getUserTotalTypeCount($userData['user_id'], 'total_field_created');
        }
        echo $userCount;
        exit;
        break;

    case 'check_template_count_public_user':
        $userData = $_SESSION['aib']['user_data'];
        $userCount = 0;
        if ($userData['user_type'] == 'U') {
            $userCount = getUserTotalTypeCount($userData['user_id'], 'total_template_created');
        }
        echo $userCount;
        exit;
        break;
    //Scrapbook Section Start  
    case 'list_user_scrapbook':
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "scrpbk_lst",
            "user_id" => $_SESSION['aib']['user_data']['user_id']
        );
        $apiResponseData = aibServiceRequest($postRequestData, 'scrapbook');
        $userScrapbookList = [];
        if ($apiResponseData['status'] == 'OK') {
			$userScrapbookParent = '';
			foreach($apiResponseData['info']['records'] as $key=>$dataArray){
				if($dataArray['item_title'] == 'Scrapbooks'){
					$userScrapbookParent = $dataArray['item_id'];
					break;
				}
			}
            //$userScrapbookParent = $apiResponseData['info']['records'][0]['item_id'];
            if ($userScrapbookParent) {
                $userScrapbookList = getItemChildWithData($userScrapbookParent);
            }
        }
        if(!empty($userScrapbookList)){
            foreach($userScrapbookList as $scrapKey=>$scrapbookDataArray){
                $sharedWith = [];
                if(!empty($scrapbookDataArray['properties']['share_user_email'])){
                    $anynomusUser = json_decode($scrapbookDataArray['properties']['share_user_email']);
                    if(!empty($anynomusUser)){
                        foreach($anynomusUser as $dataArray){
                            $sharedWith[] = 'anonymous('.$dataArray.')';
                        }
                    } 
                }
                if(!empty($scrapbookDataArray['properties']['share_user'])){
                    $sharedWithUser  = json_decode($scrapbookDataArray['properties']['share_user']);
                    if(is_array($sharedWithUser) && !empty($sharedWithUser)){
                        $sharedWith      = array_merge($sharedWith, $sharedWithUser);
                    }
                }
                $userScrapbookList[$scrapKey]['shared_with'] = implode(', ', $sharedWith);
            }
        }
        print json_encode($userScrapbookList);
        break;
    case 'add_new_scrapbook':
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error', 'message' => 'Some fields are missing'];
        if (!empty($postData)) {
            $postItemData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "scrpbk_new",
                "user_id" => $_SESSION['aib']['user_data']['user_id'],
                "title" => $postData['scrapbook_title']
            );
            $apiResponseData = aibServiceRequest($postItemData, 'scrapbook');
            $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
            if ($apiResponseData['status'] == 'OK') {
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $apiResponseData['info'],
                    'propname_1' => 'scrapbook_type',
                    'propval_1' => $postData['scrapbook_type'],
                    'propname_2' => 'aibftype',
                    'propval_2' => 'sg'
                );
                $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
                $responseArray = ['status' => 'success', 'message' => 'Scrapbook created successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
	case 'update_new_scrapbook':
        parse_str($_POST['formData'], $postData);  
        $responseArray = ['status' => 'error', 'message' => 'Some fields are missing'];
        if (!empty($postData)) {
            $postItemData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "scrpbk_upd",
                "user_id" => $_SESSION['aib']['user_data']['user_id'],
		"obj_id" => $postData['edit_scrapbook_id'],
                "new_title" => $postData['scrapbook_title']
            );
            $apiResponseData = aibServiceRequest($postItemData, 'scrapbook');  
            $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
            if ($apiResponseData['status'] == 'OK') {
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $postData['edit_scrapbook_id'],
                    'propname_1' => 'scrapbook_type',
                    'propval_1' => $postData['scrapbook_type'] 
                ); 
                $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');  
                $responseArray = ['status' => 'success', 'message' => 'Scrapbook Updated successfully.'];
            }
        }
        print json_encode($responseArray);
        break;	
    case 'delete_user_scrapbook':
        $scrapbook_id = $_POST['scrapbook_id'];
        if ($scrapbook_id) {
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "delete_item",
                "obj_id" => $scrapbook_id
            );
            $apiResponseData = aibServiceRequest($postRequestData, 'browse');
            $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
            if ($apiResponseData['status'] == 'OK') {
                $responseArray = ['status' => 'success', 'message' => 'Scrapbook deleted successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'delete_entry_from_scrapbook':
        $scrapbook_item_id = $_POST['scrapbook_item_id'];
        if ($scrapbook_item_id) {
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "delete_item",
                "obj_id" => $scrapbook_item_id
            );
            $apiResponseData = aibServiceRequest($postRequestData, 'browse');
            $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
            if ($apiResponseData['status'] == 'OK') {
                $responseArray = ['status' => 'success', 'message' => 'Item deleted from scrapbook successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'list_scrapbook_entries':
        $scrapbook_id = $_POST['scrapbook_id'];
        $scrapbook_ref= $_POST['scrapbook_ref'];
        if ($scrapbook_id) {
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list",
                "parent" => $scrapbook_id,
                "opt_deref_links" =>'Y',
                "opt_get_property" => 'Y',
                "opt_get_long_prop" => 'Y'
              );
            $apiResponseData = aibServiceRequest($postRequestData, 'browse');
            if ($apiResponseData['status'] == 'OK') {
                $responseDataArray = $apiResponseData['info']['records'];
                $finalDataArray = [];
                $count = 0;
                foreach ($responseDataArray as $dataArray) {
                    $linkTitle = $dataArray['link_title'];
                    $ext_url = $dataArray['final_deref_stp_url'];
                    $ext_thoumb = $dataArray['final_deref_stp_thumb'];
                    $link_id   =  $dataArray['link_id'];
                    $postData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "get",
                        "obj_id" => $dataArray['item_id'],
                        "opt_get_field" => 'Y',
                        "opt_get_files" => 'Y',
                        "opt_get_property" => 'Y'
                    );
                    $item_property = getItemDetailsWithProp($dataArray['item_id']);
                    $apiResponse = aibServiceRequest($postData, 'browse');
                    foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                                 $recordsItemParent = getTreeData($dataArray['item_id'],'',1);
                                 $apiResponse['info']['records'][$key]['item_parents'] = $recordsItemParent[count($recordsItemParent)- 2]['item_id'];
                                 $apiResponse['info']['records'][$key]['top_parents']  = $recordsItemParent[0]['item_title'];
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
										 $apiResponse['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                                         continue;
                                     }
                                     if ($FileRecord["file_type"] == 'pr') {
                                         $PrimaryID = $FileRecord["file_id"];
                                         $apiResponse['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                                         continue;
                                     }
                                 }
                    }
                   
                    $finalDataArray[$count] = $apiResponse['info']['records'][0];
                    $finalDataArray[$count]['scrapbook_title'] = $linkTitle;
                    $finalDataArray[$count]['item_details'] = $item_property;
                    $finalDataArray[$count]['final_deref_stp_thumb'] = $ext_thoumb;
                    $finalDataArray[$count]['final_deref_stp_url'] = $ext_url;
                    $finalDataArray[$count]['link_id'] = $link_id;
                    $count ++;
                      
                }
                include_once TEMPLATE_PATH . 'scrapbook_iem_listing.php';
            }
        }
        break;
    //Scrapbook Section End

    //
    //Shared link section start 
    case 'shared_record_list':
        $shareType = isset($_POST['share_type']) ? $_POST['share_type'] : "shared_from_user";
        $folder_id = $_POST['folder_id'];
        $finalDataArray  = [];
        if($folder_id == ''){
            $finalDataArray = getSharedRecord($shareType);
        }else{
            $finalDataArray = getSharedRecordItems($folder_id);
        } 
        $dataArray =[];
        foreach($finalDataArray as $key =>$val ){
            if($val['refrence_details']['item_type'] == 'scrpbkent'){
                unset($finalDataArray[$key]);
                continue;
            }
            if($val['item_type'] == 'RE' || $val['item_type'] == 'IT' ){
                $linkDetails = getItemData($val['refrence_details']['link_id'],1);
                $val['refrence_details']['link_create_date'] = date('m/d/Y H:i:s A', $linkDetails['item_create_stamp']);
                $linkUserLogin = ($shareType == 'shared_from_user') ? $val['user_details_name'] : $val['refrence_details']['link_owner_login'];
                if(isset($linkUserLogin) && $linkUserLogin!= ''){
                    $linkUserDetails = getUserProfile('', $linkUserLogin);
                    $user_id = $linkUserDetails['user_id'];
                    $userPropertyDetails = getUsersAllProperty($user_id);
                    $val['refrence_details']['link_owner_email'] = isset($userPropertyDetails['email']) ? $userPropertyDetails['email'] : '';
                }
                if(isset($val['refrence_details']['properties']['shared_with_user_id']) && $val['refrence_details']['properties']['shared_with_user_id'] != ''){
                    $user_id = $val['refrence_details']['properties']['shared_with_user_id'];
                    $userDataArray   = getUserProfile($user_id, '');
                    $user_top_folder = $userDataArray['user_top_folder'];
                    $agDetails = getItemData($user_top_folder, 1);
                    $val['refrence_details']['link_owner_email'] = isset($userDataArray['properties']['email']) ? urldecode($userDataArray['properties']['email']) : '';
                    $val['user_details_name']  = $userDataArray['user_login'];
                    $val['user_archive_group'] = $agDetails['item_title'];
                }
               $dataArray[$key] =  $val;
            }
        }	
        $apiListResponse =  getItemListRecord($_SESSION['aib']['user_data']['user_top_folder']);
        foreach($apiListResponse['info']['records'] as $record){
                $shareData[$record['item_id']] = $record['item_title'];
        }  
        $anony_title = "shared out of system";
        if(in_array($anony_title, $shareData)){	 
          $outOfsytemId = array_search($anony_title, $shareData); 
        } 
        $anonyResponse =  getItemListRecord($outOfsytemId);  
        foreach($anonyResponse['info']['records'] as $recorddata){ 
            $apiRequestData = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'get_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $recorddata['item_id']
            );
            $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
            $apiResponsePropsData['records'] = $apiResponseProps['info']['records'];
            $apiResponsePropsData['item_id'] = $recorddata['item_id'];
            $finalArrayResponse[] = $apiResponsePropsData;
        }  
        include_once TEMPLATE_PATH . 'shared_archive_listing.php';
        break;
		
	case 'admin_historical_connections':
            $PostDataArray = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => "1",
                "_op" => "share_list",
                "user_id" => $_SESSION['aib']['user_data']['user_id'],
                "opt_get_files" => "Y",
                "opt_get_property" => "Y",
                "perspective" => 'item_share'
            );
            $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
            if (isset($_REQUEST['perspective']) && in_array($_REQUEST['perspective'], array('shared_to_user','shared_from_user'))) {
                $PostDataArray['perspective'] = $_REQUEST['perspective'];
            } else {
                $PostDataArray['perspective'] = 'item_share';
            }	
            
		if (!(isset($apiResponse['status']) || $apiResponse['status'] == 'OK')) {
			break;
		}
		$connections_list = $apiResponse['info']['records'];
		$dataArray = [];
		$imagePathView = IMAGE_PATH.'view.png';
		$imagePathEdit= IMAGE_PATH.'edit_icon.png';
		$imagePathDelete = IMAGE_PATH.'delete_icon.png';
		$user_top_folder = $_SESSION['aib']['user_data']['user_top_folder'];
		foreach ($connections_list as $key=>$value) {
                    if($value['item_ref'] == '-1' || $value['item_parent'] == '-1'){
                        unset($connections_list[$key]);
                        continue;
                    }
                    $item_parent = $value['item_parent'];
                    $item_ref    = $value['item_ref'];
                    if(isset($value['properties']['default_connection_by']) && $value['properties']['default_connection_by'] == 'aib'){
                        $item_parent = $value['item_ref'];
                        $item_ref    = $value['item_parent'];
                    }
			$postData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "get_path",
                            "obj_id" => $item_parent
			);
			$item_tree_data = aibServiceRequest($postData, 'browse');
			$postData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "get_path",
                            "obj_id" => $item_ref
			);
			$item_tree_ref = aibServiceRequest($postData, 'browse');
			if (!(isset($item_tree_data['status']) || $item_tree_data['status'] == 'OK') || !(isset($item_tree_ref['status']) || $item_tree_ref['status'] == 'OK')) {
                            continue;
			}
			$skipFirst = $item_data = $item_tree_data['info']['records'];
                        if($_REQUEST['perspective'] == 'shared_to_user'){
                            if ($item_data[1]['item_id']  != $user_top_folder) {
                                continue;
                            }
                        }elseif($_REQUEST['perspective'] == 'shared_from_user'){
                            if ($item_data[1]['item_id']  == $user_top_folder) {
                                continue;
                            }
                        }
			unset($skipFirst[0]);
			$item_data_path = implode('/',array_column($skipFirst, 'item_title'));
			if (in_array($item_data[count($item_data)-1]['item_type'], ['AG','AR','CO','SG','RE'])) {
                            $item_data_href = HOST_PATH.'home.php?folder_id='.$item_data[count($item_data)-2]['item_id'];
			} else {
                            $item_data_href = HOST_PATH.'item-details.php?folder_id='.$item_data[count($item_data)-2]['item_id'].'&itemId='.$item_data[count($item_data)-1]['item_id'];
			}
			$skipFirst = $item_ref = $item_tree_ref['info']['records'];
			unset($skipFirst[0]);
			$item_ref_path = implode('/',array_column($skipFirst, 'item_title'));
			if (in_array($item_ref[count($item_ref)-1]['item_type'], ['AG','AR','CO','SG','RE'])) {
                            $item_ref_href = HOST_PATH.'home.php?folder_id='.$item_ref[count($item_ref)-2]['item_id'];
			} else {
                            $item_ref_href = HOST_PATH.'item-details.php?folder_id='.$item_ref[count($item_ref)-2]['item_id'].'&itemId='.$item_ref[count($item_ref)-1]['item_id'];
			}
                        
                        $original_archive_id = $item_data[1]['item_id'];
                        $original_archive_title = $item_data[1]['item_title'];
                        $original_content = '<a href="'.$item_data_href.'">'.$item_data_path.'</a>';
                        $connection_location = '<a href="'.$item_ref_href.'">'.$item_ref_path.'</a>';
                        $archiveBlockID = $item_ref[1]['item_id'];
                        $action_view = '<span class="manage-archive-group"><a href="'.$item_data_href.'" target="_blank"><img title="Manage archive Data" src="'.$imagePathView.'" alt="" /></a></span>';
                        $block_button = false;
			
			$connection_type = '';
			if ($value['properties']['link_class']=='public') {
                            $connection_type = 'Public Connection';
			} else if ($item_data[1]['item_id']  == $user_top_folder) {
                            $connection_type = 'My Historical Connection to Others';
                            $block_button = true;
			} else{
                            $connection_type = 'Other\'s Historical Connection to Me';
			}
			
			$dataArray[$key]['original_content'] = $original_content;
			$dataArray[$key]['connection_location'] = $connection_location;
			$dataArray[$key]['connection_type'] = $connection_type;
			$dataArray[$key]['connection_owner'] = $original_archive_title.'<BR><b>'.$value['link_owner_title'].'</b>';
			$dataArray[$key]['created'] = date('m/d/Y', explode('.', $value['item_create_stamp'])[0]);
			
			$archiveItemID = $value['item_id'];
			$dataArray[$key]['actions'] = '';
			//$dataArray[$key]['actions'] .= '<span class="manage-archive-group"><a href="javascripts:;"><img title="Manage archive Data" src="'.$imagePathEdit.'" alt="" /></a></span>';
			//$dataArray[$key]['actions'] .= '<span data-field-delete-id="'.$archiveItemID.'"  class="delete_listing_item"><img title="Delete" src="'.$imagePathDelete.'" alt="" /></span> ';
			$dataArray[$key]['actions'] .= $action_view.' ';
			
			if ($block_button) {
                            $postRequestData = array(
                                "_key" => APIKEY,
                                "_session" => $sessionKey,
                                "_user" =>1,
                                "_op" => "get_item_prop",
                                "obj_id" =>$user_top_folder
                            );
                            $apiResponseProfile = aibServiceRequest($postRequestData, 'browse');
                            $property_values = $apiResponseProfile['status']=='OK'?json_decode($apiResponseProfile['info']['records']['blocked_archives'], true):[];
                            if (empty($property_values) || !in_array($archiveBlockID, $property_values)) {
                                    $dataArray[$key]['actions'] .= '<span block_connection="true" data-field-block-id="'.$archiveBlockID.'" class="block_historical_connection"><a href="javascript:;"><i class="glyphicon glyphicon-ban-circle"></i></a></span>';
                            } else {
                                    $dataArray[$key]['actions'] .= '<span block_connection="false" data-field-block-id="'.$archiveBlockID.'" class="block_historical_connection"><a href="javascript:;"><i class="glyphicon glyphicon-ban-circle" style="color:#d0d0d0"></i></a></span>';
                            }
			}
		}
		
        include_once TEMPLATE_PATH . 'historical_connection_listing.php';
		break;

	case 'delete_historical_connection':
            $item_id = $_POST['item_id'];
            $responseData = array('status' => 'success', 'message' => 'Item not deleted, Please try again');
            if ($item_id) {
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'delete_item',
                    '_session' => $sessionKey,
                    'obj_id' => $item_id
                );
                $apiResponse = aibServiceRequest($postDataItem, 'browse');
                if ($apiResponse['status'] == 'OK') {
                    $responseData = array('status' => 'success', 'message' => 'Item deleted successfully.');
                }
            }  
            print json_encode($responseData);
            break;

	case 'block_historical_connection':
            $item_id = $_POST['item_id'];
            $block = $_POST['block_connection'];
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            $user_top_folder = $_SESSION['aib']['user_data']['user_top_folder'];
            $blocked_archives = $_SESSION['aib']['user_data']['properties']['blocked_archives'];
            if ($item_id) {
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" =>1,
                "_op" => "get_item_prop",
                "obj_id" =>$user_top_folder
            );
            $apiResponseProfile = aibServiceRequest($postRequestData, 'browse');
                if ($apiResponseProfile['status']=='OK') {
                    $property_values = isset($apiResponseProfile['info']['records']['blocked_archives']) ? json_decode($apiResponseProfile['info']['records']['blocked_archives'], true): [];
                    if ($block == 'true') {
                        $property_values[] = $item_id;
                        $message = 'Archive blocked successfully.';
                    }else{
                        $property_values = array_diff($property_values, array($item_id));
                        $message = 'Archive unblocked successfully.';
                    }
                    $postRequestData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" =>1,
                        "_op" => "set_item_prop",
                        "obj_id" =>$user_top_folder,
                        "propname_1" => 'blocked_archives',
                        "propval_1" => json_encode($property_values)
                    );
                    $apiResponseProfile = aibServiceRequest($postRequestData, 'browse');
                    if ($apiResponseProfile['status'] == 'OK') {
                        $responseData = array('status' => 'success', 'message' => 'Archive blocked successfully.');
                    }
                    }
		}
		print json_encode($responseData);
		break;
    case 'change_public_user_status':
        $user_id = (isset($_POST['user_id']) && $_POST['user_id'] != '') ? $_POST['user_id'] : '';
        $status  = ($_POST['current_status'] == 'd') ? 'a' : 'd';
        if($user_id){
            $userStatus = setUserProfileStatus($user_id, $status);
            $responseArray = ['status' => 'error', 'message' => 'User status not changed, Please try again.'];
            if($userStatus){
                $responseArray = ['status' => 'success', 'message' => 'User status changed successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
        
    case 'delete_shared_record':
        $ref_item_id = (isset($_POST['ref_item_id']) && $_POST['ref_item_id'] != '') ? $_POST['ref_item_id'] : '';
        $item_id     = (isset($_POST['item_id']) && $_POST['item_id'] != '') ? $_POST['item_id'] : '';
        $user_to_remove = trim($_POST['user_to_remove']);
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
        $shared_user_list = isset($apiResponseProps['info']['records']['share_user']) ? json_decode($apiResponseProps['info']['records']['share_user']) : array();
        if(in_array($user_to_remove, $shared_user_list)){
            $founddUser = array_search($user_to_remove,$shared_user_list);
            unset($shared_user_list[$founddUser]);
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $item_id,
                'opt_long' => 'Y',
                'propname_1' => 'share_user',
                'propval_1' => json_encode($shared_user_list)
            );
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        }
        if($ref_item_id){
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "share_delete",
                "obj_id" => $ref_item_id
            );
            $apiResponse = aibServiceRequest($postRequestData, 'sharing');
            $responseArray = ['status' => 'error', 'message' => 'Shared item not deleted.'];
            if($apiResponse['status'] == 'OK'){
                $responseArray = ['status' => 'success', 'message' => 'Shared item deleted successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    
    case 'get_related_recors_listing':
        $record_id         = $_POST['record_id'];
        $recordDetails     = getItemData($record_id);
        $recordAllParents  = getTreeData($record_id, 'yes');
        $record_sg_id      = $recordAllParents[count($recordAllParents)-2]['item_id'];
        $user_archive_id   = $recordAllParents[1]['item_id'];
        $login_user_type   = $_SESSION['aib']['user_data']['user_type'];
        if($login_user_type == 'U'){
            $allSubgroupsArray = getUsersAllSubGroupsListing($user_archive_id, $record_sg_id);
        }else{
            $allSubgroupsArray = getAGAllSubgroup($user_archive_id, $record_sg_id);
        }
        /*$PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'shared_from_user',
            "user_id" => -1
        );*/
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'item_share',
            "user_id" => -1,
            "item_id" => $record_id,
            "opt_get_property"=> 'Y'
        );
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');  
        $assignedSubGroups = [];
        if($apiResponse['status'] == 'OK'){
            foreach($apiResponse['info']['records'] as $key=>$assignedArray){
                if($assignedArray['item_ref'] == $record_id and $assignedArray['properties']['link_class']=='related_content'){
                    $assignedSubGroups[$assignedArray['item_id']] = $assignedArray['item_parent'];
                }
            }
        }
        $previous_related_parent = json_encode($assignedSubGroups);
        include_once TEMPLATE_PATH . 'set_related_records.php';
        break;
        
    case 'set_related_recors_listing':
        $record_id = $_POST['record_id'];
        $selected_parent = $_POST['selected_parent'];
        $previousGeneratedArray = [];
        $previous_selected_parent = (array)json_decode($_POST['previous_selected_parent']);
        foreach($previous_selected_parent as $prevKey=>$previousId){
            $previousGeneratedArray[$prevKey] = $previousId;
        }
        $sharing_user = $shared_user = $_SESSION['aib']['user_data']['user_id'];
        if(!empty($selected_parent) && $record_id != '') {
            foreach ($selected_parent as $key => $parent_id) {
                if (!in_array($parent_id, $previousGeneratedArray)) {
                    $checkedForShared = checkForAlreadySharedWithUser($shared_user, $record_id, $parent_id);
                    if ($checkedForShared == 0) {
                        $PostData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => "1",
                            "_op" => "share_create",
                            "share_target" => $parent_id,
                            "share_source" => $record_id,
                            "share_title" => "Shared $record_id at " . date('Y-m-d h:i:s'),
                            "share_item_user" => $sharing_user,
                        );
                        $apiResponse = aibServiceRequest($PostData, 'sharing');
                        $responseArray = ['status' => 'error', 'message' => 'Related record not saved.'];
                        if ($apiResponse['status'] == 'OK') {
                            $PostDataProp = array(
                                "_key" => APIKEY,
                                "_session" => $sessionKey,
                                "_user" => 1,
                                "_op" => "set_item_prop",
                                "obj_id" => $apiResponse['info'],
                                "propname_1" => 'link_class',
                                "propval_1" => 'related_content'
                            );
                            $apiResponseProp = aibServiceRequest($PostDataProp, 'browse');
                            $responseArray = ['status' => 'success', 'message' => 'Related record saved successfully.'];
                        }
                    }
                } else {
                    $key = array_search($parent_id, $previousGeneratedArray);
                    unset($previousGeneratedArray[$key]);
                }
            }
        }
        if(!empty($previousGeneratedArray)){
            foreach($previousGeneratedArray as $prevKey=>$item_id){
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'delete_item',
                    '_session' => $sessionKey,
                    'obj_id' => $prevKey
                );
                $apiResponse = aibServiceRequest($postDataItem, 'browse');
            }
        }
        print json_encode(['status'=>'success','message'=>'Related item saved successfully.']);
        break;
    case 'save_ebay_url':
        $record_id = $_POST['record_id'];
        $url = $_POST['url'];
        $ebay_sale_options = $_POST['ebay_sale_options'];
        $date = Date('Y-m-d H:i:s');
        if($_SESSION['aib']['user_data']['user_type']=='U'){
            $userProp = getUserProfile($_SESSION['aib']['user_data']['user_id']);
            $userEbay = urldecode($userProp['properties']['ebay_sale_count']);
            $userEbayArray = json_decode($userEbay,true);
            $countEbayLink = sizeof($userEbayArray);
            $ebay_count = [];
            if(!empty($userEbayArray)){ 
                $ebay_count = $userEbayArray;
            }
            if($countEbayLink >= EBAY_LINK_LIMIT){
               if(!in_array($record_id, $ebay_count)){
                   $responseArray = ['status' => 'error', 'message' => 'You can add maximum '.EBAY_LINK_LIMIT.' ebay link.'];
                    print json_encode($responseArray);
                    break;
               }
            }
        } 
        if($url != '' && $ebay_sale_options != ''&& $date!=''){
            $apiRequestEbayUrl = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $record_id,
                'propname_1' => 'ebay_url',
                'propval_1' => $url,
                'propname_2' => 'ebay_sale_options',
                'propval_2' => $ebay_sale_options,
                'propname_3' => 'ebay_record_date',
                'propval_3' => $date
            );
            $apiResponse = aibServiceRequest($apiRequestEbayUrl, 'browse');
             if($apiResponse['status']=='OK'){
                    if($_SESSION['aib']['user_data']['user_type']=='U' && !in_array($record_id, array_keys($ebay_count))){
                        $ebay_count[$record_id]= 0;
                        $setEbayCountProp = setEbayCountUserProp('ebay_sale_count',json_encode($ebay_count));
                   } 
                $responseArray = ['status' => 'success', 'message' => 'Ebay url successfully saved.']; 
            }else{
                $responseArray = ['status' => 'error', 'message' => 'Ebay url not saved.'];    
            }
        }else{
            $responseArray = ['status' => 'error', 'message' => 'Ebay url is required.'];
        }
        print json_encode($responseArray);
        break;
    case 'check_ebay_link_save':
        $apiRequestDataNew = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $_POST['record_id']
        );
        $apiResponseNew = aibServiceRequest($apiRequestDataNew, 'browse'); 
        if($apiResponseNew['status']=='OK'){
            $responseArray = $apiResponseNew['info']['records'];   
        }
        print json_encode($responseArray);
        break;
    case 'get_user_notifier_listing':
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $apiRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $user_id,
            "_op" => "notifier_def_list",
            "user_id" => $user_id
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'notifiers');
        $notifierDataArray = [];
        if($apiResponse['status'] == 'OK'){
            foreach($apiResponse['info']['records'] as $key => $dataArray){
                $dataArray['keyword_title'] =  urldecode($dataArray['keyword']);
				$dataArray['item_title'] =  urldecode($dataArray['item_title']);
                $notifierDataArray[$key] = $dataArray;  
            }
        } 
        print json_encode($notifierDataArray);
        break;
    case 'society_listing_for_public_user':
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => 1
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        include 'item_listing.php';
        break;
	case 'get_notitify_edit_id': 
		echo $_POST['notifier_id'];
		/* $apiRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_POST['notifier_id'],
            "_op" => "notifier_def_list",
            "user_id" => $_POST['notifier_id']
        );
        $apiResponse = aibServiceRequest($apiRequestData, 'notifiers');
		echo "<pre>";print_r($apiResponse); die; */
        break;	
		
	case 'delete_user_notitify':  
                $keyword = urldecode($_POST['keyword']);
		$apiRequestData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => "notifier_def_del",
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "user_id" => $_POST['user_id'],
                    "parent_id"=> $_POST['parent_id'],
                    "keywords"=> $keyword
                );  
		$responseArray = ['status' => 'error', 'message' => 'Some things went wrong with the API'];
                $apiResponse = aibServiceRequest($apiRequestData, 'notifiers');
		if($apiResponse['status'] == 'OK'){
			 $responseArray = ['status' => 'success', 'message' => 'Notifier deleted successfully.'];
		}   
		print json_encode($responseArray);
        break;		
    case 'add_new_notifier':
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error', 'message' => 'Missing required data.'];
        if(!empty($postData) && $postData['total_notifire'] < 10){
        $parent = (isset($postData['archive_group']) && $postData['archive_group']!= '') ? $postData['archive_group'] : '-1';
            $parent = (isset($postData['archive_group']) && $postData['archive_group']!= '') ? $postData['archive_group'] : '-1';
            $PostRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "notifier_def_add",
                "user_id" => $_SESSION['aib']['user_data']['user_id'],
                "parent_id" =>$parent,
                "keywords" =>$postData['new_tags']
            );
            $apiResponse = aibServiceRequest($PostRequestData,"notifiers");
            $responseArray = ['status' => 'error', 'message' => 'Some things went wrong with the API'];
            if($apiResponse['status'] == 'OK'){
            $responseArray = ['status' => 'success', 'message' => 'Notifier added successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
		
		
     case 'get_public_connections':
        $record_id         = $_POST['record_id'];
        $recordAllParents  = getTreeData($record_id, 'yes');
        $user_archive_id   = $recordAllParents[1]['item_id'];
        $login_user_type   = $_SESSION['aib']['user_data']['user_type'];
		 $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => 1,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_deref_links" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
	
		$societyDataArray=array();
		if($apiResponse['status'] == 'OK'){
            foreach($apiResponse['info']['records'] as $key=>$society){
				$societyData=array();
				if($user_archive_id !=$society['item_id'])
				{
					$societyData['info']=$society;
					
							//$allSubgroupsArray = getAGAllSubgroup($society['item_id']);
						
					//$societyData['data']=$allSubgroupsArray;
					$societyDataArray[]=$societyData;
				}
			}
		}
		 $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'shared_from_user',
            "user_id" => $_SESSION['aib']['user_data']['user_id']
        );
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
		 if($apiResponse['status'] == 'OK'){
            foreach($apiResponse['info']['records'] as $key=>$assignedArray){
                if($assignedArray['item_ref'] == $record_id){
                    $assignedSubGroups[] = $assignedArray['item_parent'];
                }
            }
        }
		/*echo'<pre>';
		print_r( $assignedSubGroups );echo '</pre>';*/
		 /*echo'<pre>';
		print_r( $apiResponse );echo '</pre>';
		
		die;
      
		
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'shared_from_user',
            "user_id" => $_SESSION['aib']['user_data']['user_id']
        );
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        $assignedSubGroups = [];
        if($apiResponse['status'] == 'OK'){
            foreach($apiResponse['info']['records'] as $key=>$assignedArray){
                if($assignedArray['item_ref'] == $record_id){
                    $assignedSubGroups[$assignedArray['item_id']] = $assignedArray['item_parent'];
                }
            }
        }
        $previous_related_parent = json_encode($assignedSubGroups);*/
        include_once TEMPLATE_PATH . 'set_public_connections.php';
        break;	
    case'update_assis_details':
            parse_str($_POST['formData'], $postData);
            $email_condition = $postData['user_old_email'] != $postData['email'];
            $email_template = file_get_contents(EMAIL_PATH."/email_change.html");
            $email_data['to'] = $postData['email'];
            $email_data['from'] = ADMIN_EMAIL;
            $email_data['reply'] = ADMIN_EMAIL;
            $email_data['subject'] = 'ArchiveInABox: Change Email-Id'; 
            $email_template = str_replace('#username#',$postData['username'], $email_template);
            $user_id = $postData['user_id'];
            $old_email = $postData['user_old_email'];
            unset($postData['user_id']);
            if($postData['user_old_email'] == $postData['email']){
               unset($postData['user_old_email']);
               unset($postData['email']);
            }
            $userProperty=[];
            foreach($postData as $key=>$val){
                  $userProperty[$i]['name'] = $key;
                  $userProperty[$i]['value'] = $val;   
                  $i++;  
              } 
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" =>$user_id,
                "_op" => "set_profile_prop_batch",
                "user_id" =>$user_id,
                "property_list" => json_encode($userProperty)
            );
            $apiResponseProfile = aibServiceRequest($postRequestData, 'users');
            if($apiResponseProfile['status']=='OK'){
                if($email_condition){
                        $url = 'id='.$user_id.'&flg=change_email&'.$postData['email'].'&'.$old_email;
                        $link = '<a href="'.HOST_PATH.'thank-you.php?'.urlencode($url).'" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>'; 
                        $email_template = str_replace('#confirm_email#',$link, $email_template);
                        $message = 'You have update your email-id successfully. Please click on the link below to confirm change your email';
                        $email_template = str_replace('#message#',$message, $email_template);
                        $email = sendMail($email_data,$email_template);
                        if($email){
                             $responseData = array('status' => 'success', 'message' => 'Your details updated successfully');
                        }
                }else{
                     $responseData = array('status' => 'success', 'message' => 'Your details updated successfully');
                }
            }else{
                 $responseData = array('error' => 'Error', 'message' => 'Your details have not updated ');
            }
            print json_encode($responseData);
        break;
    case'get_user_share_link_listing':
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "share_list",
            "perspective" => 'shared_from_user',
            "user_id" => $_SESSION['aib']['user_data']['user_id'],
            "opt_get_property" => 'Y',
            "opt_get_thumb" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'sharing');
        $dataArray = [];
        $i =0;
        
        foreach($apiResponse['info']['records'] as $key=>$val){
            if(isset($val['properties']['link_class'])&& !empty($val['properties']['link_class']) && $val['properties']['link_class'] == 'public' ){
               $dataArray[$i] = $val; 
               $dataArray[$i]['create_date_time'] = date('m/d/Y H:i:s A', $val['item_create_stamp']);
               $itemTitle =getItemData($val['item_parent'],1); 
               if($itemTitle['properties']['aib:private']=='Y')
               $dataArray[$i]['itemTitle'] = '<img style="width:16px;" src="public/images/private-record.png" title="Private record" alt="Private Records">&nbsp;'.$itemTitle['item_title'];
	    else
	       $dataArray[$i]['itemTitle'] = $itemTitle['item_title']; 
               ++$i;
            }
        }
        print json_encode($dataArray);
        break;
		
		
	case'get_user_share_with_me_listing':
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "share_list",
            "perspective" => 'shared_to_user',
            "user_id" => $_SESSION['aib']['user_data']['user_id'],
            "opt_get_property" => 'Y' 
        );
        $apiResponse = aibServiceRequest($postData, 'sharing');
        $dataArray = [];
        $i =0; 
        foreach($apiResponse['info']['records'] as $key=>$val){
            if(isset($val['properties']['link_class'])&& !empty($val['properties']['link_class']) && $val['properties']['link_class'] == 'public' ){
               $dataArray[$i] = $val; 
               $dataArray[$i]['create_date_time'] = date('m/d/Y H:i:s A', $val['item_create_stamp']);
               $itemTitle =getItemData($val['item_parent'],1); 
               $dataArray[$i]['itemTitle'] = $itemTitle['item_title'];
               if($itemTitle['properties']['aib:private']=='Y')
              	  $dataArray[$i]['itemTitle'] = '<img style="width:16px;" src="public/images/private-record.png" title="Private record" alt="Private Records">&nbsp;'.$itemTitle['item_title'];
	      else
		 $dataArray[$i]['itemTitle'] = $itemTitle['item_title'];   
               ++$i;
            }
        }  
        print json_encode($dataArray);
        break;	
    case'unlink_share_link':
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "share_delete",
            "obj_id" => $_POST['obj_id']
        );
        $apiResponse = aibServiceRequest($postData, 'sharing');
        $responseData = array('status' => 'error', 'message' => 'Something went wrong');
        if($apiResponse['status'] == 'OK'){
        $responseData = array('status' => 'success', 'message' => 'You have unlinked successfully');    
        }
         print json_encode($responseData);
        break;
    case'get_user_report_listing':
        $search_type = (isset($_POST['search_type']) && !empty($_POST['search_type']))? $_POST['search_type']:'';
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "req_list",
            "req_type" => "RPC",
            "req_name" =>'Test request'
        );
        $result = aibServiceRequest($postData,"custreq");
        $dataArray = array();
        $i = 0;
        foreach($result['records'] as $key=>$val){ 
            if(!empty($val['req_info'])){
                $info = (array)json_decode($val['req_info']);
                //array_push($info, $val['req_id']);
                array_unshift($info,$val['req_id'], $val['req_status']);
                 if(!empty($search_type) && $search_type == 'Archive'){
                     if(isset($info['search_type']) && $info['search_type'] =='A' ){
                     $dataArray[]= $info; 
                       ++$i;
                     }
                }else if(!empty($search_type) && $search_type == 'People' ){
                    if(isset($info['search_type']) && $info['search_type'] =='P'){
                    $dataArray[]= $info; 
                       ++$i;
                    }
                }else{
                    $dataArray[]= $info;  
                      ++$i;
                }
            }
          
        }
        print json_encode($dataArray);
        break;
    case'get_user_report':
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "req_get",
            "req_id" => $_POST['req_id']
	);
        $result = aibServiceRequest($postData,"custreq");
        $dataArray = json_decode($result['records'][0]['req_info']);
        print json_encode($dataArray);
        break; 
    case 'delete_report_data':
        $request_id = $_POST['request_id'];
        $userReportData = getUserReportData($request_id);
        $reportDataArray= (array)json_decode($userReportData['req_info']);
        $responseData = array('status' => 'error', 'message' => 'Reported user not deleted.');  
        if(!empty($reportDataArray)){
            $reportedUserDetails = getUserProfile('', $reportDataArray['user_reported']);
            $reported_user_id    = $reportedUserDetails['user_id'];
            $reported_url        = $reportDataArray['report_url'];
            $reportedUrlArray    = explode('?', $reported_url, 2);
            $reportedIdArray     = explode('&',$reportedUrlArray[1],2);
            $reportedId          = explode('=', $reportedIdArray[0]);
            $record_id             = $reportedId[1];
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list",
                "parent" => $record_id,
		"opt_get_property" =>'Y'
            );
            $publicConnectionApiResponse = aibServiceRequest($postData, 'browse');
            if($publicConnectionApiResponse['status'] == 'OK'){
                foreach($publicConnectionApiResponse['info']['records'] as $dataArray){
                    if($dataArray['is_link'] == 'Y' && $dataArray['item_ref'] != '-1' && $dataArray['properties']['owner'] == $reported_user_id){
                        $deleteConnectionId = $dataArray['item_id'];
                        $postData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "delete_item",
                            "obj_id" =>$deleteConnectionId
                        );
                        aibServiceRequest($postData, 'browse');
                        $requestPostData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "req_mod",
                            "req_id" => $request_id,
                            "req_status" => 'COMPLETED'
                        );
                        $apiResponse = aibServiceRequest($requestPostData, 'custreq');
                        $responseData = array('status' => 'success', 'message' => 'Reported user deleted successfully.');
                    }
                }
            }
        }
        print json_encode($responseData);
        break;
    case 'change_user_report_status':
        $request_id = $_POST['request_id'];
        $status     = $_POST['status'];
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "req_mod",
            "req_id" => $request_id,
            "req_status" => $status
        );
        $apiResponse = aibServiceRequest($requestPostData, 'custreq');
        $responseData = array('status' => 'error', 'message' => 'Status not changed');
        if($apiResponse['status'] == 'OK'){
            $responseData = array('status' => 'success', 'message' => 'Status changed successfully.');
        }
        print json_encode($responseData);
        break;
    case 'share_record_scrapbook_list':
        $shareType = isset($_POST['share_type']) ? $_POST['share_type'] : "shared_from_userr";
        $folder_id = $_POST['folder_id'];
        $finalDataArray  = [];
        if($folder_id == ''){
            $finalDataArray = getSharedRecord($shareType);
        }else{
            $finalDataArray = getSharedRecordItems($folder_id);
        }
        $dataArray =[];
        $i = 0;
        foreach($finalDataArray as $key => $dataValue){
           $parentTitle = getTreeData($dataValue['item_id'],'false',1);
           if($parentTitle[2]['item_title'] == 'Scrapbooks' && $dataValue['is_link'] == 'Y'){
              $linkDetails = getItemData($dataValue['link_id'],1);
              $dataValue['link_create_date']= date('m/d/Y H:i:s A', $linkDetails['item_create_stamp']);
              $dataArray[$i] = $dataValue;
               ++$i;
           }
        }
        print json_encode($dataArray);
        break;
    case 'delete_shared_scrapbook':
        $ref_item_id = (isset($_POST['ref_id']) && $_POST['ref_id'] != '') ? $_POST['ref_id'] : '';
        $item_id     = (isset($_POST['item_id']) && $_POST['item_id'] != '') ? $_POST['item_id'] : '';
        $user_to_remove = trim($_SESSION['aib']['user_data']['user_login']);
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
        $shared_user_list = isset($apiResponseProps['info']['records']['share_user']) ? json_decode($apiResponseProps['info']['records']['share_user']) : array();
        if(in_array($user_to_remove, $shared_user_list)){
            $founddUser = array_search($user_to_remove,$shared_user_list);
            unset($shared_user_list[$founddUser]);
            $apiRequestDataItem = array(
                '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'set_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $item_id,
                'opt_long' => 'Y',
                'propname_1' => 'share_user',
                'propval_1' => json_encode($shared_user_list)
            );
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        }
        if($ref_item_id){
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "share_delete",
                "obj_id" => $ref_item_id
            );
            $apiResponse = aibServiceRequest($postRequestData, 'sharing');
            $responseArray = ['status' => 'error', 'message' => 'Shared item not deleted.'];
            if($apiResponse['status'] == 'OK'){
                $responseArray = ['status' => 'success', 'message' => 'Shared item deleted successfully.'];
            }
        }
        print json_encode($responseArray);
        break;  
    case 'get_archive_prop_data':
        if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
         $postPropData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => "list_profile_prop",
                "_user" => 1,
                "user_id" => $_POST['user_id']
            );
           $apiProResponse = aibServiceRequest($postPropData, 'users');
           $userPropData = [];
           foreach($apiProResponse['info']['records'] as $key=>$dataArray){
                    $userPropData[$dataArray['property_name']] = $dataArray['property_value'];
                   }
        }
         $user_tiemstamp = isset($userPropData['timestamp'])? $userPropData['timestamp']:'';
         $terms_condition = getSuperAdminAllProperty(1);
         if($terms_condition['timestamp'] > $user_tiemstamp ){ $userPropData['term_service'] = ''; }
         print json_encode($userPropData);
        break;
    case 'reset_user_session' :
        unset($_SESSION);
        session_destroy();
        break;
    case 'change_field_status':
        $field_id     = $_POST['field_id'];
        $field_status = $_POST['field_status'];
        $api_op       = ($field_status == 'N') ? 'field_disable' : 'field_enable';
        if($field_id){
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => $api_op,
                "field_id" => $field_id
            );
            $apiResponse = aibServiceRequest($postData,"fields");
            $responseArray = ['status' => 'error', 'message' => 'Field status not changed.'];
            if($apiResponse['status'] == 'OK'){
                $responseArray = ['status' => 'success', 'message' => 'Field status changed successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    case'update_user_term_condition':
        $user_id = $_POST['user_id'];
        $userProperty=[];
        $userProperty[0]['name'] = 'timestamp';
        $userProperty[0]['value'] = time();
        $postRequestData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" =>$user_id,
                    "_op" => "set_profile_prop_batch",
                    "user_id" => $user_id,
                    "property_list" => json_encode($userProperty)
                    );
                    $apiResponseProfile = aibServiceRequest($postRequestData, 'users');
                     $responseArray = ['status' => 'error', 'message' => 'Field status not changed.'];
                     if($apiResponseProfile['status'] =='OK'){
                      $_SESSION['aib']['user_data']['terms_condition'] = 'Y';
                      $responseArray = ['status' => 'success','message' => 'Your term & condition checked, updated.'];    
                     }
        print json_encode($responseArray);
        break;
    case 'submit_request':  
        parse_str($_POST['formData'], $postData);
		$responseArray = array('status' => 'error', 'message' => 'You are Robot!');
		if($postData['captcha-response']){
			$responseArray = _recaptchaSiteVerify($postData['captcha-response']);
			//$responseArray = recaptchaSiteVerify($postData['captcha-response']);
			if($responseArray['status']!='success'){
				print json_encode($responseArray);
				break;
			}
		}
		$current_time = time();
		$time_diff = $current_time-$postData['timestamp_value'];  
		if($time_diff > TIMESTAMP_1 ){ 
                $succes_message = 'Your report has been submitted. Thank you.';
                $email_data = [];
                $email_template = file_get_contents(EMAIL_PATH."/emailer.html");
                $name_image = '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'first-name.png" alt="" />';
                $email_image = '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'email.png" alt="" />';
                $comment_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'comment.png" alt="" />';
                $link_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'link.png" alt="ArchiveInABox Logo" />';
                $email_template = str_replace('#name_image#',$name_image,$email_template);
                $email_template = str_replace('#last_image#',$name_image,$email_template);
                $email_template = str_replace('#email_image#',$email_image,$email_template);
                $email_template = str_replace('#comment_image#',$comment_image,$email_template);
		        $requestData = 'Your request has been submitted with the following details.';
		        $requestDataAdmin = 'A contact request has been submitted for the following record :';
        switch($postData['request_type']){
            case 'RD': 
                $email_data['from'] =  $postData['email'];
                $email_data['reply'] = $postData['email'];
                $email_data['subject'] = 'Content Removal Request';
                $phone = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">'.$postData['phone_number'].'</div><div style="clear:both;"></div>';
                $sub = 'Article Link';
                $phone_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="ArchiveInABox Logo" />';
                $email_template = str_replace('#phone_image#',$phone_image,$email_template);
                $email_template = str_replace('#sub_link_image#',$link_image,$email_template);
                $email_template = str_replace('#first_name#',$postData['first_name'],$email_template);
                $email_template = str_replace('#last_name#',$postData['last_name'],$email_template);
                $email_template = str_replace('#email#',$postData['email'],$email_template);
                $email_template = str_replace('#phone#',$phone,$email_template);
                $email_template = str_replace('#sub_link#',$sub,$email_template);
                $email_template = str_replace('#sub_link_name#',$postData['article_link'],$email_template);
                $email_template = str_replace('#used_page#','',$email_template);
                $email_template = str_replace('#comment#',$postData['comments'],$email_template);
               
		        $requestDataAdmin = 'A content removal request has been submitted for the following record :';
                $name = $postData['first_name'].' '.$postData['last_name'];
                $email = $postData['email'];
                $phone = $postData['phone_number'];
		        $string = $postData['article_link'];
				// Convert string to array                
				$stringArray = explode('?', $string);
				$encryptedQryStringArr=$stringArray[1];
				$encryptedQryString = explode('=', $encryptedQryStringArr);
				$decrypted_string = decryptQueryString($encryptedQryString[1]);
				
				if(isset($decrypted_string['folder_id']) and $decrypted_string['folder_id']!='')
				{
					$item_id =$decrypted_string['folder_id'];
				}
                /*$stringArray = explode('?', $string);
                if(strpos($stringArray[1],'&')){
                    $subStringArray = explode('&',$stringArray[1]);
                    $itemArray = explode('=',$subStringArray[0]);
                    $item_id = $itemArray[1];
                }else{
                    $subStringArray = explode('=',$stringArray[1]);
                    $item_id = $subStringArray[1];
                }*/
		        $item_par_id = getTreeData($item_id, true, 1);
                $info  = array('item_link'=>$postData['article_link'],'comment'=>$postData['comments'],'item_id'=>$item_par_id[1]['item_id']);
                $succes_message = 'Your request has been submitted. Thank you';
				
				$AdministratorEmailId = getArchiveAdministratorEmails($item_par_id[1]['item_id']);  
				if($AdministratorEmailId != ''){
					$emailaddress['societyEmail'] =  $AdministratorEmailId;
				}
				$emailaddress['userEmail'] = $postData['email'];
                break;
		    case 'RC':
                    $succes_message = 'Your report has been submitted . Thank you.'; 
                    $email_data['from'] =  $postData['email'];
                    $email_data['reply'] = $postData['email'];
                    $email_data['subject'] = 'Report Content';
                    $phone = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">'.$postData['phone_number'].'</div><div style="clear:both;"></div>';
                    $sub = 'Article Link';
                    $phone_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="ArchiveInABox Logo" />';
                    $reason = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'reason.png" alt="ArchiveInABox Logo" /><strong>Reason</strong></div>
                    <div style="width:70%; float:left; font-size:14px; padding:5px;">'.$postData['reporting_reason'].'</div>
                    <div style="clear:both;"></div>';
                    $email_template = str_replace('#phone_image#',$phone_image,$email_template);
                    $email_template = str_replace('#sub_link_image#',$link_image,$email_template);
                    $email_template = str_replace('#first_name#',$postData['first_name'],$email_template); 
                    $email_template = str_replace('#last_name#',$postData['last_name'],$email_template);
                    $email_template = str_replace('#email#',$postData['email'],$email_template);
                    $email_template = str_replace('#phone#',$phone,$email_template); 
                    $email_template = str_replace('#sub_link#',$sub,$email_template);
                    $email_template = str_replace('#sub_link_name#',$postData['article_link'],$email_template);
                    $email_template = str_replace('#used_page#',$reason,$email_template);
                    $email_template = str_replace('#comment#',$postData['comments'],$email_template);
                    $requestData = 'Your report has been submitted with the following details.'; 
		            $requestDataAdmin = 'A  Report content request has been submitted for the following record :';
					
                    $name = $postData['first_name'];
                    $email = $postData['email'];
                    $phone = $postData['phone_number'];
                    $string = $postData['article_link'];
                  
					 // Convert string to array                
						$stringArray = explode('?', $string);
						$encryptedQryStringArr=$stringArray[1];
						$encryptedQryString = explode('=', $encryptedQryStringArr);
						$decrypted_string = decryptQueryString($encryptedQryString[1]);
						
						if(isset($decrypted_string['folder_id']) and $decrypted_string['folder_id']!='')
						{
							$item_id =$decrypted_string['folder_id'];
						}
                   /* if(strpos($stringArray[1],'&')){
                        $subStringArray = explode('&',$stringArray[1]);
                        $itemArray = explode('=',$subStringArray[0]);
                        $item_id = $itemArray[1];
                    }else{
                        $subStringArray = explode('=',$stringArray[1]);
                        $item_id = $subStringArray[1];
                    }*/
                    $item_par_id = getTreeData($item_id, true, 1);
                    $info  = array('item_link'=>$postData['article_link'],'comment'=>$postData['comments'],'reporting_reason'=>$postData['reporting_reason'],'item_id'=>$item_par_id[1]['item_id']);
					
                    $AdministratorEmailId = getArchiveAdministratorEmails($item_par_id[1]['item_id']);
                    if($AdministratorEmailId != ''){
                            $emailaddress['societyEmail'] =  $AdministratorEmailId;
                    }
                    $emailaddress['userEmail'] = $postData['email'];
                
                break;
            case 'CS':
                $succes_message = 'Your contact request has been submitted.Thank you';
                //$postData['request_type'] = 'CT';
                $search_type = (isset($postData['search_type']) && !empty($postData['search_type']))? $postData['search_type'] : '';
                $name = $postData['contact_us_name']; 
                $phone = $postData['front_contact_us_phone'];
                $email = $postData['front_contact_us_email'];
                $info  = array('organization_name'=>$postData['organization_name'],'comment'=>$postData['comments'],'timestamp_value'=>$postData['timestamp_value']);
                $phone_image_div = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">'.$phone.'</div><div style="clear:both;"></div>';
            
                $email_data['from'] =  ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                $email_data['subject'] = 'Contact Request';
                
                $email_template = file_get_contents(EMAIL_PATH."/contact_us.html");
                $name_image = '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'first-name.png" alt="" />';
                $email_image = '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'email.png" alt="" />';
                $comment_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'comment.png" alt="" />';
                $phone_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="ArchiveInABox Logo" />';
                $crop_image = '<img style="float:left; margin-right:4px;" src="'.HOST_ROOT_ICON_PATH.'corporate.png" alt="" />';
               
                $email_template = str_replace('#name_image#',$name_image,$email_template);
                $email_template = str_replace('#crop_image#',$crop_image,$email_template);
                $email_template = str_replace('#email_image#',$email_image,$email_template);
                $email_template = str_replace('#comment_image#',$comment_image,$email_template);
                $email_template = str_replace('#phone_image#',$phone_image,$email_template);
              
                $email_template = str_replace('#first_name#',$name,$email_template); 
                $email_template = str_replace('#crop_name#',$postData['organization_name'],$email_template);
                $email_template = str_replace('#email#',$email,$email_template);
                $email_template = str_replace('#phone#',$phone_image_div,$email_template);
                $email_template = str_replace('#comment#',$postData['comments'],$email_template);
                $requestDataAdmin='The following user has requested to contact you with the details provided below:';
                $email_template = str_replace('#request#',$requestDataAdmin,$email_template);
                $email_template1 = file_get_contents(EMAIL_PATH."/contact_response.html");
                $email_data1['to'] = $email;
                $email_data1['from'] =  ADMIN_EMAIL;
                $email_data1['reply'] = ADMIN_EMAIL;
                $email_data1['subject'] = 'Contact Request Submitted';
                break;
        }
        $info['status'] = 0;
        $searchType = '';
        if(isset($item_par_id[0]['item_title'])){
            $searchType = 'P';
            if($item_par_id[0]['item_title'] == 'ARCHIVE GROUP'){
                $searchType = 'A';
            }
        }
        if(isset($postData['search_type']) && !empty($postData['search_type'])){   
            $searchType = 'C'; 
        }
        $info['search_type'] = $searchType;
        $responseArray = ['status'=>'error','message'=>'All fields are required.'];
        $user_ip_address = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?: getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
           
			if(!empty($postData)){
				$postDataRequest = array(
					"_key" => APIKEY,
					"_session" => $sessionKey,
					"_user" => 1,
					"_op" => "req_store",
					"req_type" => $postData['request_type'],
					"req_name" => $name,
					"req_phone"=> $phone,
					"req_email" => $email,
					"req_ipaddr" => $user_ip_address,
					"req_info" => json_encode($info)
				);
                                if(empty($search_type)){
                                    $postDataRequest['req_item'] = $item_id ;
                                }
				$apiResponse = aibServiceRequest($postDataRequest, 'custreq');
				$responseArray = ['status'=>'error','message'=>'Some things went wrong! Please try again.'];
				if($apiResponse['status'] == 'OK'){
                     if(isset($postData['search_type']) && !empty($postData['search_type']) && $postData['search_type']=='C'){
                           $email_data['to'] = BUSINESS_EMAIL;
                           $sendEmail = sendMail($email_data,$email_template);
                           sendMail($email_data1,$email_template1);
                      }else{
						  if($postData['request_type']=='RD' || $postData['request_type']=='CT')
							{
								 foreach($emailaddress as $key=>$value){
									if($key =='societyEmail'){
										$email_data['to'] = $value;
										$email_template = str_replace('#request#',$requestDataAdmin,$email_template);
									}else{
										$email_data['to'] = $value;
										$email_template = str_replace($requestDataAdmin,'#request#',$email_template);
										$email_template = str_replace('#request#',$requestData,$email_template);
									}
									$email_data['reply'] = ADMIN_EMAIL;
									$email_data['from'] =  ADMIN_EMAIL;
									$sendEmail = sendMail($email_data,$email_template);
									unset($email_data['to']);
								}
							}
							else
							{
								 $email_data1['to'] = BUSINESS_EMAIL;
							
												// Send email to user                         
												$sendEmail = sendMail($email_data,$email_template);
												// Send email to admin
												sendMail($email_data1,$email_template1);
								
								}
                    }
					if($sendEmail){
						$responseArray = ['status'=>'success','message'=>$succes_message];
					}
				}
			}
		}
        print json_encode($responseArray);
        break;

    case 'submit_trouble_request':
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error','message' => 'Missing required field(s).'];
		if($postData['captcha-response']){
			$responseArray = _recaptchaSiteVerify($postData['captcha-response']);
			//$responseArray = recaptchaSiteVerify($postData['captcha-response']);
			if($responseArray['status']!='success'){
				print json_encode($responseArray);
				break;
			}
		}
        if(!empty($postData)){
            $user_ip_address = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?: getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
            $otherInfo = [
                'organization'=> $postData['organization'],
                'user_type'   => $postData['user_type'],
                'type_of_trouble' => $postData['type_of_trouble'],
                'your_computer' => $postData['your_computer'],
                'browser_type'  => $postData['browser_type'],
                'internet_connection' => $postData['internet_connection'],
                'comment' => ($postData['your_message'] != '') ? $postData['your_message'] : ''
            ];
            $postDataRequest = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "req_store",
                "req_type" => 'TT',//Trouble Ticket
                "req_name" => $postData['name'],
                "req_phone"=> ($postData['phone'] != '') ? $postData['phone']: '',
                "req_email" => $postData['trouble_email'],
                "req_ipaddr" => $user_ip_address,
                "req_info" => json_encode($otherInfo),
                "req_item" => '-1'
            );
            $apiResponse = aibServiceRequest($postDataRequest, 'custreq');
            $responseArray = ['status' => 'error','message' => 'There is some error in your request, Please check and submit again.'];
            if($apiResponse['status'] == 'OK'){
                $email_data = [];
                $email_data['from'] =  ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                $email_data['to'] = BUSINESS_EMAIL;
                $email_data['subject'] = 'Trouble ticket request';
                $email_template = file_get_contents(EMAIL_PATH."/trouble_ticket.html");

                $email_template = str_replace('#name#',        $postData['name'],$email_template);
                $email_template = str_replace('#organization#',$postData['organization'],$email_template);
                $email_template = str_replace('#email#',       $postData['trouble_email'],$email_template);
                $email_template = str_replace('#phone#',       $postData['phone'],$email_template);
                $email_template = str_replace('#user_type#',   $postData['user_type'],$email_template);
                $email_template = str_replace('#trouble_type#',$postData['type_of_trouble'],$email_template);
                $email_template = str_replace('#computer_type#',$postData['your_computer'],$email_template);
                $email_template = str_replace('#browser_type#',$postData['browser_type'],$email_template);
                $email_template = str_replace('#internet_connection#',$postData['internet_connection'],$email_template);
                $email_template = str_replace('#message#',$postData['your_message'],$email_template);

                $email_template = str_replace('#name_icon#',         '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'first-name.png" alt="" />'     ,$email_template);
                $email_template = str_replace('#organization_icon#', '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'society.png" alt="" />',$email_template);
                $email_template = str_replace('#email_icon#',        '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'email.png" alt="" />',$email_template);
                $email_template = str_replace('#phone_icon#',        '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'phone-number.png" alt="" />',$email_template);
                $email_template = str_replace('#user_type_icon#',    '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'first-name.png" alt="" />',$email_template);
                $email_template = str_replace('#trouble_type_icon#', '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'reason.png" alt="" />',$email_template);
                $email_template = str_replace('#computer_type_icon#','<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'computer.png" alt="" />',$email_template);
                $email_template = str_replace('#browser_type_icon#', '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'browser.png" alt="" />',$email_template);
                $email_template = str_replace('#internet_connection_icon#','<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'internet.png" alt="" />',$email_template);
                $email_template = str_replace('#message_icon#',       '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'comment.png" alt="" />',$email_template);

                $sendEmail = sendMail($email_data,$email_template);
                $responseArray = ['status' => 'success','message' => 'Your trouble ticket request has been submitted successfully. .'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'submit_society_trouble_request':
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error','message' => 'Missing required field(s).'];
        if(!empty($postData)){
            $user_ip_address = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?: getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
            $userType = ['A'=>'Administrator','S'=>'Assistant'];
            $otherInfo = [
                'user_type'   => $userType[$_SESSION['aib']['user_data']['user_type']],
                'type_of_trouble' => $postData['type_of_trouble'],
                'your_computer' => $postData['your_computer'],
                'browser_type'  => $postData['browser_type'],
                'comment' => ($postData['your_message'] != '') ? $postData['your_message'] : '',
                'archive_group_id'=>$_SESSION['aib']['user_data']['user_top_folder']
            ];
            $postDataRequest = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "req_store",
                "req_type" => 'STT',//Society Trouble Ticket
                "req_name" => $postData['name'],
                "req_phone"=> ($postData['phone'] != '') ? $postData['phone']: '',
                "req_email" => $postData['trouble_email'],
                "req_ipaddr" => $user_ip_address,
                "req_info" => json_encode($otherInfo),
                "req_item" => '-1'
            );
            $apiResponse = aibServiceRequest($postDataRequest, 'custreq');
            $responseArray = ['status' => 'error','message' => 'There is some error in your request, Please check and submit again.'];
            if($apiResponse['status'] == 'OK'){
                $email_data = [];
                $email_data['from'] =  ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                $email_data['to'] = BUSINESS_EMAIL;
                $email_data['subject'] = 'Society Trouble ticket request';
                $email_template = file_get_contents(EMAIL_PATH."/society_trouble_ticket.html");
                $email_template = str_replace('#name#',        $postData['name'],$email_template);
                $email_template = str_replace('#email#',       $postData['trouble_email'],$email_template);
                $email_template = str_replace('#user_type#',   $userType[$_SESSION['aib']['user_data']['user_type']],$email_template);
                $email_template = str_replace('#computer_type#',$postData['your_computer'],$email_template);
                $email_template = str_replace('#browser_type#',$postData['browser_type'],$email_template);
                $email_template = str_replace('#message#',$postData['your_message'],$email_template);
                $email_template = str_replace('#name_icon#',         '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'first-name.png" alt="" />'     ,$email_template);
                $email_template = str_replace('#email_icon#',        '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'email.png" alt="" />',$email_template);
                $email_template = str_replace('#user_type_icon#',    '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'first-name.png" alt="" />',$email_template);
                $email_template = str_replace('#computer_type_icon#','<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'computer.png" alt="" />',$email_template);
                $email_template = str_replace('#browser_type_icon#', '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'browser.png" alt="" />',$email_template);
                $email_template = str_replace('#message_icon#',       '<img style="float:left; margin-right:4px;"  src="'.HOST_ROOT_ICON_PATH.'comment.png" alt="" />',$email_template);
				
				/* auto generated email template for request user */
				   $responseData = [];
				   $responseData['from'] =  ADMIN_EMAIL;
				   $responseData['reply'] = ADMIN_EMAIL;
				   $responseData['to'] = $postData['trouble_email'];
				   $responseData['subject'] = 'Trouble ticket response';
				   $response_template = file_get_contents(EMAIL_PATH."/response.html");
				   $response_template = str_replace('#response_message#',"Thank you for writing to us, we will get back to you soon.",$response_template);
				   $sendEmail = sendMail($responseData,$response_template);
               /** auto generated email template for request user **/
			   
			   
                $sendEmail = sendMail($email_data,$email_template);
                $responseArray = ['status' => 'success','message' => 'Your trouble ticket request has been submitted successfully. .'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'get_state_country':
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $_POST['parent_id']
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $state_country =[];
        foreach($apiResponse['info']['records'] as $key=>$value){
            array_push($state_country, utf8_decode(urldecode($value['item_title'])));
        }
        print json_encode($state_country);    
        break;   
    case'ebay_remove_link':
        $record_id = $_POST['record_id'];
        $responseArray = ['status' => 'error','message' => 'Something went wronge.'];
        if($_SESSION['aib']['user_data']['user_type']=='U'){
           $userProp = getUserProfile($_SESSION['aib']['user_data']['user_id']);
           $userEbay = urldecode($userProp['properties']['ebay_sale_count']);
           $userEbayArray = json_decode($userEbay,true);
           if(count($userEbayArray) > 1){
                unset($userEbayArray[$record_id]);
                $setEbayCountProp = setEbayCountUserProp('ebay_sale_count',json_encode($userEbayArray));
         }else{
             $propName = 'ebay_sale_count';
             deleteUserProp($_SESSION['aib']['user_data']['user_id'] ,$propName);
         }
        } 
        $apiRequestEbayUrl = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'del_item_prop',
                '_session' => $sessionKey,
                'obj_id' => $record_id,
                'propname_1' => 'ebay_url',
                'propname_2' => 'ebay_sale_options',
                'propname_3' => 'ebay_record_date'
            );
            $apiResponse = aibServiceRequest($apiRequestEbayUrl, 'browse');
            if($apiResponse['status'] == 'OK'){
               $responseArray = ['status' => 'success','message' => 'Ebay link has been removed successfully.'];
            }
            print json_encode($responseArray);
        break;
    case'check_user_exist':
        $responseArray = ['status' => 'error','message' => 'Username has been not exist.'];
        $userProfile = getUserProfile('',$_POST['username']);
        if(!empty($userProfile)){
            $responseArray = ['status' => 'success','message' => 'Username has been already exist.'];
        }
        print json_encode($responseArray);
        break;
    case 'update_archive_font_color':
        $responseArray = ['status' => 'error','message' => 'Missing field or value'];
        if(!empty($_POST)){
            $field_name  = (isset($_POST['field_name']) && $_POST['field_name'] != '') ? $_POST['field_name'] : '';
            $field_value = (isset($_POST['field_value']) && $_POST['field_value'] != '') ? $_POST['field_value'] : '';
            $archive_id  = (isset($_POST['archive_id']) && $_POST['archive_id'] != '' && is_numeric($_POST['archive_id'])) ? $_POST['archive_id'] : '';
            if($field_name != '' && $field_value != '' && $archive_id != ''){
                $postData = array(
                    '_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $archive_id,
                    'propname_1' => $field_name,
                    'propval_1' => $field_value,
                );
                // Service request for set item property
                $apiResponse = aibServiceRequest($postData, 'browse');
                if ($apiResponse['status'] == 'OK') {
                    $responseArray = ['status' => 'success','message' => 'Updated successfully.'];
                }
            }
        }
        print json_encode($responseArray);
        break;
    case 'remove_society_images':
        $item_id       = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_SPECIAL_CHARS);
        $property_name = filter_input(INPUT_POST, 'property_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $responseArray = ['status' => 'error','message' => 'Missing required fields.'];
        if($item_id && $property_name){
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "del_item_prop",
                "obj_id" => $item_id,
                "propname_1" => $property_name
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            if($apiResponse['status'] == 'OK'){
                $responseArray = ['status' => 'success','message' => 'Removed successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
        
    case 'change_item_parent':
        $item_id      = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_SPECIAL_CHARS);
        $item_type    = filter_input(INPUT_POST, 'item_type', FILTER_SANITIZE_SPECIAL_CHARS);
        $itemParents  = getTreeData($item_id, 1);
        $itemParentId = $itemParents[count($itemParents)-2]['item_id'];
        $itemWhereMovedParent = $itemParents[1]['item_id'];
        $leftItemList  = getItemChildList($itemParentId);
        $rightItemList = getItemChildList($itemWhereMovedParent);
        $accessArray   = array('CO'=>'AR','SG'=>'CO','RE'=>'SG','IT'=>'RE');
        include_once TEMPLATE_PATH . 'move_item.php';
        break;
    case 'get_more_child':
        $item_id      = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_SPECIAL_CHARS);
        $item_type    = filter_input(INPUT_POST, 'item_type', FILTER_SANITIZE_SPECIAL_CHARS);
        $selected_type= filter_input(INPUT_POST, 'selected_type', FILTER_SANITIZE_SPECIAL_CHARS);
        $selected_item= filter_input(INPUT_POST, 'selected_item', FILTER_SANITIZE_SPECIAL_CHARS);
        $itemParents  = getTreeData($selected_item);
        $itemParentId = $itemParents[count($itemParents)-2]['item_id'];
        $returnType   = ($item_type == 'SG') ? 'sg' : '';
        $childList    = getItemChildList($item_id, $returnType);
        $accessArray  = array('CO'=>'AR','SG'=>'CO','RE'=>'SG','IT'=>'RE');
        include_once TEMPLATE_PATH . 'get_child_list.php';
        break;
    case 'move_item_parent':
        $selectedParent = filter_input(INPUT_POST, 'selected_parent', FILTER_DEFAULT, FILTER_SANITIZE_SPECIAL_CHARS);
        $selectedItems  = filter_input(INPUT_POST, 'selected_items', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if(!empty($selectedItems) && $selectedParent != ''){
            foreach($selectedItems as $item_id){
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'move_item',
                    '_session' => $sessionKey,
                    'parent' => $selectedParent,
                    'obj_id' => $item_id
                );
                $result = aibServiceRequest($postDataItem, "browse");
            }
            $responseArray = ['status' => 'success','message' => 'Item moved successfully.'];
        }else{
            $responseArray = ['status' => 'error','message' => 'Item not moved'];
        }
        print json_encode($responseArray);
        break;
}


function getItemChildList($item_id = null, $return_type = null){
    if($item_id != null){
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $item_id,
            "opt_get_property" => 'Y' ,
            "opt_deref_links" => 'Y',
            "opt_get_long_prop" =>'Y'
        );
        // Service request for get list of data
	$apiResponse = aibServiceRequest($postData, 'browse');
        if($apiResponse['status'] == 'OK'){
            if(!empty($apiResponse['info']['records'])){
                foreach($apiResponse['info']['records'] as $key=>$dataArray){
                    if(isset($dataArray['link_id'])){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if($dataArray['item_title'] == 'society-share' || $dataArray['item_title'] == 'Scrapbooks' || $dataArray['item_title'] == 'aib-shared' || $dataArray['item_title'] == 'shared out of system' || $dataArray['item_title'] == 'advertisements'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'related_content'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'historical_connection'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'public'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'scrapbook'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(isset($dataArray['properties']['publish_status']) && $dataArray['properties']['publish_status'] == 'N'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if($return_type == 'sg' && $dataArray['item_type'] != 'SG'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                return array_values($apiResponse['info']['records']);
            }
        }
    }
}
/*
* @Author: Sapple Systems
* @Method: getUserReportData()
* @Params1: $request_id(int) -- Id of the requested item from requests.
* @Description: This function is used to get the complete data of a requested item.
* @Dependent API: custreq.php
* @OP: req_get
* @Return: (array)$result -- Request details of requesting ID.
*/
function getUserReportData($request_id){
    if($request_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create requestData
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "req_get",
            "req_id" => $request_id
	);
        // Service request for getting request details of requesting ID
        $result = aibServiceRequest($postData,"custreq");
        return $result['records'][0];
    }
}

/*
* @Author: Sapple Systems
* @Method: getItemChildWithData()
* @Params1: $item_id(int) -- Id of the item from ftree.
* @Description: This function is used to get the list of all childs of requesting item.
* @Dependent API: browse.php
* @OP: list
* @Return: (array)$apiResponse -- All child list with their property value.
*/
function getItemChildWithData($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create requestData for child list
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id,
            "opt_get_property" => 'Y',
            "opt_get_long_prop" => 'Y'
        );
        // Service request for getting child list.
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    } else {
        return false;
    }
}

/*
* @Author: Sapple Systems
* @Method: getUserTotalTypeCount()
* @Params1: $user_id(int) -- Id of the user from ftree_user.
* @Params2: $property_name(string) -- Name of the property for which getting the value.
* @Description: This function is used to get the property value of a requesting property name for the requesting user.
* @Dependent API: users.php
* @OP: get_profile_prop
* @Return: (array)$apiResponse -- property name and value.
*/
function getUserTotalTypeCount($user_id = null, $property_name = null) {
    if ($user_id && $property_name) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create requestData for property value
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "get_profile_prop",
            "user_id" => $user_id,
            "property_name" => $property_name
        );
        // Service request for getting property value.
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['property_value'];
        } else {
            return '0';
        }
    }
}

/*
* @Author: Sapple Systems
* @Method: updateUserProperty()
* @Params1: $user_id(int) -- Id of the user from ftree_user.
* @Params2: $property_name(string) -- Name of the property for which getting the value.
* @Params3: $created_id(int) -- Its a form Id for counting how many forms created by the requesting user.
* @Description: This function is used to store the property value of total form created count for requesting user.
* @Dependent API: users.php
* @OP: set_profile_prop_batch
* @Return: (boolean)true/false.
*/
function updateUserProperty($user_id = null, $property_name = null, $created_id = null) {
    if ($user_id && $property_name) {
        $sessionKey = $_SESSION['aib']['session_key'];
        //Get the property value of requesting user for passed property name from function params
        $previousCount = getUserTotalTypeCount($user_id, $property_name);
        //Get the property value of requesting user for passed property name from function params
        $previousCreated = getUserTotalTypeCount($user_id, $property_name . '_id');
        $current_count = $previousCount + 1;
        $created = $previousCreated . ',' . $created_id;
        if ($previousCreated == 0) {
            $created = $created_id;
        }
        $userProperty[0]['name'] = $property_name;
        $userProperty[0]['value'] = $current_count;
        $userProperty[1]['name'] = $property_name . '_id';
        $userProperty[1]['value'] = $created;
        // Create requestData for create/update profile property in a batch
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "set_profile_prop_batch",
            "user_id" => $user_id,
            "property_list" => json_encode($userProperty)
        );
        // Service request for save property value.
        $apiResponse = aibServiceRequest($postRequestData, "users");
        return true;
    }
}

/*
* @Author: Sapple Systems
* @Method: getUsersAllProperty()
* @Params1: $user_id(int) -- Id of the user from ftree_user.
* @Description: This function is used to get the all property of a user.
* @Dependent API: users.php
* @OP: list_profile_prop
* @Return: (array)$propertyList
*/
function getUsersAllProperty($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create requestData
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list_profile_prop",
            "user_id" => $user_id
        );
        // Rervice request for getting user property list.
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            $propertyList = [];
            //Make array in pair of key(property name) value(Property value)
            foreach ($apiResponse['info']['records'] as $propertyData) {
                $propertyList[$propertyData['property_name']] = $propertyData['property_value'];
            }
            return $propertyList;
        }
    }
}

/*
* @Author: Sapple Systems
* @Method: getSuperAdminAllProperty()
* @Params1: $user_id(int) -- Id of the user from ftree_user.
* @Description: This function is used to get the all property of a user.
* @Dependent API: users.php
* @OP: list_profile_prop
* @Return: (array)$propertyList
*/
function getSuperAdminAllProperty($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create requestData
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list_profile_prop",
            "user_id" => $user_id
        );
        // Service request for getting user property list.
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            $propertyList = [];
            //Make array in pair of key(property name) value(Property value)
            foreach ($apiResponse['info']['records'] as $propertyData) {
                $propertyList[$propertyData['property_name']] = $propertyData['property_value'];
            }
            return $propertyList;
        }
    }
}

/*
* @Author: Sapple Systems
* @Method: getAllSubgroup()
* @Params1: $archive_id(int) -- Id of the archive(item) from ftree.
* @Description: This function is used to get the all sub groups of request archive ID.
* @Dependent API: browse.php
* @OP: list
* @Return: (array)$responseDataArray
*/
function getAllSubgroup($archive_id) {
    if ($archive_id) {
        // Get the archive details
        $archiveDetails = getItemData($archive_id);
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for get the all collection of an archive
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $archive_id
        );
        // Service request for get the collection list
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            $responseDataArray = [];
            // Set archive details as parent deetails
            $responseDataArray['parent'] = $archiveDetails;
            $collectionList = $apiResponse['info']['records'];
            if (!empty($collectionList)) {
                $collection_count = 0;
                // Loop over collection list to get respective sub groups.
                foreach ($collectionList as $collectionKey => $collectionData) {
                    $responseDataArray['parent']['collection'][$collection_count] = $collectionData;
                    // Create request for get the all sub groups of a collection.
                    $postDataCollection = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $_SESSION['aib']['user_data']['user_id'],
                        "_op" => "list",
                        "parent" => $collectionData['item_id']
                    );
                    // Service request for get the sub groups list of a collection.
                    $apiResponseCollection = aibServiceRequest($postDataCollection, 'browse');
                    if ($apiResponseCollection['status'] == 'OK') {
                        $subGroupList = $apiResponseCollection['info']['records'];
                        $sub_group_count = 0;
                        // Loop over sub group response and set in the associative array at their respective position.
                        foreach ($subGroupList as $sub_group_key => $subgroupData) {
                            $responseDataArray['parent']['collection'][$collection_count]['sub_groups'][$sub_group_count] = $subgroupData;
                            $sub_group_count++;
                        }
                    }
                    $collection_count++;
                }
            }
        }
        return $responseDataArray;
    }
    return false;
}

/*
* @Author: Sapple Systems
* @Method: getAGAllSubgroup()
* @Params1: $archiveGroupId(int) -- Id of the archive group(item) from ftree.
* @Params2: $record_sg_id(int) -- Id of Sub group which one not to include in resultant array.
* @Description: This function is used to get the all sub groups of request Archive Group ID and skip the sub group when will appear in 2nd params of the function.
* @Dependent API: browse.php
* @OP: list
* @Return: (array)$responseDataArray
*/
function getAGAllSubgroup($archiveGroupId = null, $record_sg_id = null) {
    if ($archiveGroupId) {
        // Get the archive group details
        $archiveDetails = getItemData($archiveGroupId);
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting all archive of the archive group
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $archiveGroupId
        );
        // Service request for list all archive 
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            $responseDataArray = [];
            $responseDataArray['parent'] = $archiveDetails;
            $archiveList = $apiResponse['info']['records'];
            if (!empty($archiveList)) {
                $archive_count = 0;
                // Loop over archive to get their collection list.
                foreach ($archiveList as $key => $archiveData) {
                    $responseDataArray['parent']['archive'][$archive_count] = $archiveData;
                    // Create request for getting list of collections.
                    $postDataArchive = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $_SESSION['aib']['user_data']['user_id'],
                        "_op" => "list",
                        "parent" => $archiveData['item_id']
                    );
                    // Service request for getting collection list.
                    $apiResponseArchive = aibServiceRequest($postDataArchive, 'browse');
                    if ($apiResponseArchive['status'] == 'OK') {
                        $collectionList = $apiResponseArchive['info']['records'];
                        if (!empty($collectionList)) {
                            $collection_count = 0;
                            // Loop over collection and find their sub groups.
                            foreach ($collectionList as $collectionKey => $collectionData) {
                                $responseDataArray['parent']['archive'][$archive_count]['collection'][$collection_count] = $collectionData;
                                // Create request for getting list of sub groups
                                $postDataCollection = array(
                                    "_key" => APIKEY,
                                    "_session" => $sessionKey,
                                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                                    "_op" => "list",
                                    "parent" => $collectionData['item_id']
                                );
                                // Service request for getting sub group list.
                                $apiResponseCollection = aibServiceRequest($postDataCollection, 'browse');
                                if ($apiResponseCollection['status'] == 'OK') {
                                    $subGroupList = $apiResponseCollection['info']['records'];
                                    $sub_group_count = 0;
                                    foreach ($subGroupList as $sub_group_key => $subgroupData) {
                                        if($record_sg_id != null){
                                            // Check for request sub group id if present skip from the loop to ignore thar sub group.
                                            if($subgroupData['item_id'] == $record_sg_id){
                                                continue;
                                            }
                                        }
                                        // Skip for sub group named with shared out of system
                                        if($subgroupData['item_title'] != 'shared out of system'){
                                            $responseDataArray['parent']['archive'][$archive_count]['collection'][$collection_count]['sub_groups'][$sub_group_count] = $subgroupData;
                                            $sub_group_count++;
                                        }
                                    }
                                }
                                $collection_count++;
                            }
                        }
                    }
                    $archive_count ++;
                }
            }
        }
        return $responseDataArray;
    }
    return false;
}

/*
* @Author: Sapple Systems
* @Method: getSubgroupsOfSubGroup()
* @Params1: $item_id(int) -- Id of the sub group(item) from ftree.
* @Description: This function is used to get the list of all sub groups of a sub group to nth level.
* @Dependent API: browse.php
* @OP: list
*/
function getSubgroupsOfSubGroup($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting sub group list.
        $postDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
        // Service request for getting sub group list
        $apiResponseArray = aibServiceRequest($postDataArray, 'browse');
        if ($apiResponseArray['status'] == 'OK') {
            $listDataArray = $apiResponseArray['info']['records'];
            $subgroupDataArray = array();
            if (!empty($listDataArray)) {
                $count = 0;
                // Loop on sub group list and check this SG has a sub group
                foreach ($listDataArray as $key => $dataArray) {
                    if ($dataArray['item_type'] == 'SG' || $dataArray['item_type'] == 'sg') {
                        $subgroupDataArray[$count] = $dataArray;
                        $count ++;
                        getSubgroupsOfSubGroup($dataArray['item_id']);
                    }
                }
            }
        }
    }
}

/*
* @Author: Sapple Systems
* @Method: getAssignedSubGroups()
* @Params1: $assistant_id(int) -- Id of the user(Assistant) from ftree_user.
* @Description: This function is used to list all completed and pending data of the requesting user(Assistant).
* @Dependent API: dataentry.php
* @OP: data_entry_waiting/data_entry_complete
* @Return: (array)$assignedSubGroups -- List of all completed/pending data.
*/
function getAssignedSubGroups($assistant_id) {
    $sessionKey = $_SESSION['aib']['session_key'];
    $assignedSubGroups = [];
    // Create request for list pending data.
    $uncompleteRequestData = array(
        '_key' => APIKEY,
        '_user' => $_SESSION['aib']['user_data']['user_id'],
        '_op' => 'data_entry_waiting',
        '_session' => $sessionKey,
        'user_id' => $assistant_id
    );
    // Service request for list all pending data.
    $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
    if ($uncompleteResponse['status'] == 'OK') {
        $unCompleteDataArray = $uncompleteResponse['info']['records'];
        if (!empty($unCompleteDataArray)) {
            // Loop over uncomplete data and find their details.
            foreach ($unCompleteDataArray as $uncompleteKey => $uncompleteData) {
                // Get item details.
                $itemDetails = getItemData($uncompleteData['item_parent_id']);
                $assignedSubGroups[$itemDetails['item_id']] = $itemDetails['item_title'];
            }
        }
    }
    // Create request for list all completed data.
    $completeRequestData = array(
        '_key' => APIKEY,
        '_user' => $_SESSION['aib']['user_data']['user_id'],
        '_op' => 'data_entry_complete',
        '_session' => $sessionKey,
        'user_id' => $assistant_id
    );
    // Service request for list all completed data
    $completeResponse = aibServiceRequest($completeRequestData, 'dataentry');
    if ($completeResponse['status'] == 'OK') {
        $completeDataArray = $completeResponse['info']['records'];
        if (!empty($completeDataArray)) {
            // Loop over completed data and find their details.
            foreach ($completeDataArray as $completeKey => $completeData) {
                // Get item details.
                $itemDetails = getItemData($completeData['item_parent_id']);
                $assignedSubGroups[$itemDetails['item_id']] = $itemDetails['item_title'];
            }
        }
    }
    return $assignedSubGroups;
}

/*
* @Author: Sapple Systems
* @Method: cropAndUploadImage()
* @Params1: $imageObj(obj) -- Objects of uploading image.
* @Params2: $x_coordinate(float) -- x-coordinate of the image from where cropped.
* @Params3: $y_coordinate(float) -- y-coordinate of the image from where cropped.
* @Params4: $type(string) -- Type of the image to uploaded it may be (logo, banner, content, archive_group_thumb )
* @Description: This function is used crop the image with given coordinates.
* @Return: (string)$fileName -- Cropped file name.
*/
function cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, $type, $crop_height = '') {
    // Set image height, width according to type.
    $dimensionArray = array('logo' => array('width' => 200, 'height' => 200), 'banner' => array('width' => 1600, 'height' => $crop_height), 'content' => array('width' => 645, 'height' => 430), 'archive_group_thumb' => array('width' => 400, 'height' => 400), 'archive_group_details_thumb' => array('width' => 400, 'height' => 400),'historical_connection_logo' => array('width' => 200, 'height' => 200));
    $inFile = $imageObj['tmp_name'];
    // Generate the unique file name with time()
    $fileName = time() . '_' . $imageObj['name'];
    $fileName=str_replace(' ','_',$fileName);
    $fileName=preg_replace('/[^A-Za-z0-9\-_.]/', '', $fileName);
    $fileNameWithPath = IMAGE_TARGET_PATH . $fileName;
    // Create object of imegic class with original file.
    $image = new Imagick($inFile);
    // Crop the original image with given co-ordinates and mentioned height, width
    $image->cropImage($dimensionArray[$type]['width'], $dimensionArray[$type]['height'], $x_coordinate, $y_coordinate);
    // Saved the cropped image.
    $image->writeImage($fileNameWithPath);
    return $fileName;
}

/*
* @Author: Sapple Systems
* @Method: getUncompleteDataForParent()
* @Params1: $parent_id(int) -- Id of the item(Sub group) from ftree.
* @Description: This function is used to list all pending data of give item id(Sub group).
* @Dependent API: dataentry.php
* @OP: data_entry_waiting
*/
function getUncompleteDataForParent($parent_id = null) {
    if ($parent_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        // Create request for list all pending data.
        $uncompleteRequestData = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'data_entry_waiting',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        // Service request for list all pending data.
        $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
        unset($_SESSION['aib']['uncomplete_data']);
        // Loop over pending data and their records and set into the session
        foreach ($uncompleteResponse['info']['records'] as $key => $uncompleteData) {
            if ($parent_id == $uncompleteData['item_parent_id']) {
                $recordItems = getAllItemRecords($uncompleteData['item_id']);
                $uncompleteData['item_records'] = $recordItems;
                $_SESSION['aib']['uncomplete_data'][$uncompleteData['item_parent_id']][$uncompleteData['item_id']] = $uncompleteData;
            }
        }
    }
}

/*
* @Author: Sapple Systems
* @Method: markItemAsCompleted()
* @Params1: $postData(array) -- Item complete data which is requested to mark as completed.
* @Description: This function is used to mark an item as completed.
* @Return: (boolean) true/false
*/
function markItemAsCompleted($postData) {
    // Check if item already in session data
    if (array_key_exists($postData['parent_id'], $_SESSION['aib']['uncomplete_data'])) {
        // Loop over a parent Id and mark them as completed.
        foreach ($_SESSION['aib']['uncomplete_data'][$postData['parent_id']] as $parentKey => $mainDataArray) {
            if ($postData['item_type'] == 'RE') {
                if ($postData['item_id'] == $parentKey) {
                    $_SESSION['aib']['uncomplete_data'][$postData['parent_id']][$parentKey]['completed'] = 'yes';
                    break;
                }
            } elseif ($postData['item_type'] == 'IT') {
                foreach ($mainDataArray['item_records'] as $itemKey => $itemArray) {
                    if ($postData['item_id'] == $itemArray['item_id']) {
                        $_SESSION['aib']['uncomplete_data'][$postData['parent_id']][$parentKey]['item_records'][$itemKey]['completed'] = 'yes';
                        break;
                    }
                }
            }
        }
    } else {
        // Loop over data if key does not present in the session.
        foreach ($_SESSION['aib']['uncomplete_data'] as $parentKey => $parentDataList) {
            // Loop over a parent list data and mark as completed.
            foreach ($parentDataList as $recordKey => $recordDataList) {
                if ($recordKey == $postData['parent_id']) {
                    if ($postData['item_type'] == 'RE') {
                        $_SESSION['aib']['uncomplete_data'][$parentKey][$postData['parent_id']]['completed'] = 'yes';
                        break;
                    } elseif ($postData['item_type'] == 'IT') {
                        foreach ($recordDataList['item_records'] as $itemKey => $itemArray) {
                            if ($postData['item_id'] == $itemArray['item_id']) {
                                $_SESSION['aib']['uncomplete_data'][$parentKey][$postData['parent_id']]['item_records'][$itemKey]['completed'] = 'yes';
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    return true;
}

/*
 * @Author: Sapple Systems
 * @Method: getNextUncompleteItemToEdit()
 * @Description: This function is used to get net item to be completed.
 * @Return: (array)
 */
function getNextUncompleteItemToEdit() {
    // Get uncomplete data from session.
    $unCompleteListData = $_SESSION['aib']['uncomplete_data'];
    // Nested foreach to get next record/item to be completed.
    foreach ($unCompleteListData as $key => $unCompleteData) {
        foreach ($unCompleteData as $unCompKey => $dataArray) {
            if (array_key_exists('completed', $dataArray)) {
                if (array_key_exists('item_records', $dataArray) && !empty($dataArray['item_records'])) {
                    $itemCount = 0;
                    foreach ($dataArray['item_records'] as $itemKey => $data) {
                        // Check for completed if not, Then return item to be completed.
                        if (array_key_exists('completed', $data)) {
                            $itemCount++;
                            continue;
                        } else {
                            return array('status' => 'success', 'item_id' => $data['item_id'], 'parent_id' => $dataArray['item_id']);
                        }
                    }
                    if (count($dataArray['item_records']) == $itemCount) {
                        // Mark record as completed.
                        markRecordAsComplete($unCompKey);
                    }
                } else {
                    // Mark record as completed.
                    markRecordAsComplete($unCompKey);
                }
            } else {
                return array('status' => 'success', 'item_id' => $dataArray['item_id'], 'parent_id' => $key);
            }
        }
    }
    return array('status' => 'completed', 'item_id' => '', 'parent_id' => '');
}

/*
* @Author: Sapple Systems
* @Method: markRecordAsComplete()
* @Params1: $item_id(int) -- Id of the item(Sub group) from ftree.
* @Description: This function is used to mark an item as completed.
* @Dependent API: dataentry.php
* @OP: data_entry_mark_complete
* @Return: (boolean) true/false
*/
function markRecordAsComplete($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for mark item as completed.
        $requestPostData = array(
            '_key' => APIKEY,
            '_session' => $sessionKey,
            '_op' => 'data_entry_mark_complete',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'obj_id' => $item_id
        );
        // Service request for mark item as completed.
        $apiResponse = aibServiceRequest($requestPostData, 'dataentry');
        return true;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getAllItemRecords()
 * @params1: $item_id(int)---- Id of the ftree item
 * @Description: This function is used get the child data of given item Id.
 * @Dependent API: browse.php
 * @Op: list
 * @Return: (array)$apiResponse
 */
function getAllItemRecords($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for list items
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
        // Service request for list items
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: updateItemFieldsData()
 * @params1: $formFieldData(array)------Array of fields with their id and values
 * @params2: $item_id(int)---- Id of the ftree item
 * @Description: This function is used to update the item fields data.
 * @Dependent API: browse.php
 * @op: store_item_fields
 * @return: (array)$apiResponse
 */
function updateItemFieldsData($formFieldData = array(), $item_id = null) {
    if ($item_id && !empty($formFieldData)) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for store item fields
        $requestApiData = array(
            '_key' => APIKEY,
            '_session' => $sessionKey,
            '_op' => 'store_item_fields',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'obj_id' => $item_id
        );
        $count = 1;
        // Create request for store item fields
        foreach ($formFieldData as $fieldKey => $fieldData) {
            $requestApiData['field_id_' . $count] = $fieldData['id'];
            $requestApiData['field_value_' . $count] = $fieldData['field_value'];
            $count++;
        }
        // Service request for store item fields
        $apiResponse = aibServiceRequest($requestApiData, 'browse');
        return true;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getUncompleteItemDetails()
 * @Params1: $item_id(int) -- item id to get the data from ftree
 * @Params2: $parent_id(int) -- item parent id to get the data from ftree
 * @Description: This function is used to get uncomplete item complete details
 * @Dependent API: browse
 * @OP: list
 * @Return: (array)$itemDetails -- Complete details of requesting item
 */
function getUncompleteItemDetails($item_id = null, $parent_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Get item details
        $itemDetails = getItemDetailsWithProp($item_id);
        // Get parent details
        $parentDetails = getItemData($parent_id);
        $itemDetails['parent_details'] = $parentDetails;
        if ($itemDetails['item_type'] == 'RE' || $itemDetails['item_type'] == 'IT') {
            if ($itemDetails['item_type'] == 'IT') {
                $item_id = $parent_id;
            }
            // Create request list all items
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "list",
                "parent" => $item_id,
                "opt_get_files" => 'Y',
                "opt_get_first_thumb" => 'Y'
            );
            // Service request for list all items
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK') {
                if (!empty($apiResponse['info']['records'])) {
                    // Loop over list items and get thum file id and primary file id
                    foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                        foreach ($dataArray["files"] as $FileRecord) {
                            // Check for thumb Id
                            if ($FileRecord["file_type"] == 'tn') {
                                $ThumbID = $FileRecord["file_id"];
                                $itemDetails['files_records'][$key]['tn_file_id'] = $FileRecord["file_id"];
                                continue;
                            }
                            // Check for primary file Id
                            if ($FileRecord["file_type"] == 'pr') {
                                $itemDetails['files_records'][$key]['pr_file_id'] = $FileRecord["file_id"];
                                continue;
                            }
                        }
                    }
                }
            }
        }
        return $itemDetails;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getItemDetailsWithProp()
 * @Params1: $item_id(int) -- Item id to get item details ftree
 * @Description: This function is used to get item details with all property
 * @Dependent API: browse
 * @OP: get_item_prop
 * @Return: (array)$itemDetails with all property
 */

function getItemDetailsWithProp($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for get item property
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        // Service request for list item property
        $apiResponse = aibServiceRequest($apiRequestData, 'browse');
        // Get item details
        $itemDetails = getItemData($item_id,1);
        $itemDetails['prop_details'] = $apiResponse['info']['records'];
        return $itemDetails;
    } else {
        return false;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: createNewUser()
 * @Params1: $userType(string) -- ('S'=>Assistant,'A'=>Administrator)
 * @Params2: $dataArray(array) -- Details about new users to be created.
 * @Description: This function is used to create new users (Assistant/Administrator)
 * @Dependent API: users.php
 * @OP: create_profile
 * @Return: (array)$apiResponse
 */

function createNewUser($userType = 'A', $dataArray) {
    $email_content =[];
    $email_template = file_get_contents(EMAIL_PATH."/assistant_registration.html");
    if (!empty($dataArray)) {
        $sessionKey = $_SESSION['aib']['session_key'];
        if ($_SESSION['aib']['user_data']['user_prop']['type'] == 'primary' && $userType == 'A') {
            $user = 1;
        } else {
            $user = $_SESSION['aib']['user_data']['user_id'];
        }
        $archiveCode = getArchiveCode($dataArray['archive_name']);
//        $suffix = ($userType == 'A') ? 'admin' : 'assist';
        // Add suffix in user name if Assistant then assist, If administrator then admin
//        $dataArray['login_data'] = (strpos($dataArray['login_data'], $suffix.'_'.$archiveCode) !== false) ? $dataArray['login_data']  : $dataArray['login_data'].'_'.$suffix.'_'.$archiveCode;
        // Set default passwword to AIB
        $defaultPassword = 'AIB';
        // Prepared mail data
        $email_data['to'] = $dataArray['user_email'];
        $email_data['from'] = ADMIN_EMAIL;
        $email_data['reply'] = $dataArray['user_email'];
        $email_data['subject'] = 'User Registration';
        if($userType == 'S'){
        $title = 'assistant';  
        $archive_id =  getTreeData($dataArray['archive_name'],true,1);
        $archive_details = getItemDetailsWithProp($archive_id[1]['item_id']); 
       }else{
            $title = 'administrator'; 
            $archive_details = getItemDetailsWithProp($dataArray['archive_name']);
       }
       // Prepaired mail body.
        $header_image = HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_header_image'];
        $header_image = '<img style="width:100%;" src="'.$header_image.'" alt="Image" />';
        $email_template = str_replace('#group_name#',$archive_details['item_title'], $email_template); 
        $email_template = str_replace('#username#',$dataArray['login_data'], $email_template);
        $email_template = str_replace('#header_images#',$header_image, $email_template);
        $email_template = str_replace('#title#',$title, $email_template);
        $thoumb = '<img height="100" style="position: absolute;left: 50%;top: 50%;transform: translateX(-50%) translateY(-50%);height: 80px;" src="'.HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_logo_image'].'" alt="ArchiveInABox Logo" />';
        $email_template = str_replace('#thumb#',$thoumb, $email_template);
        // Create request for save new user data
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $user,
            "_op" => "create_profile",
            "user_login" => $dataArray['login_data'],
            "user_pass" => $defaultPassword,
            "user_type" => $userType,
            "user_title" => $dataArray['login_data'],
            "user_top_folder" => $dataArray['archive_name'],
            "user_primary_group" => -1
        );
        // Service request for save new user's data
        $apiResponse = aibServiceRequest($postData, 'users');
        if($apiResponse['status'] == 'OK'){
            // Create user profile property
            $userProperty[0]['name'] = 'email';
            $userProperty[0]['value'] = $dataArray['user_email'];
            $userProperty[1]['name'] = 'status';
            $userProperty[1]['value'] = 'd';
            if ($userType == 'A') {
                $userProperty[2]['name'] = 'type';
                $userProperty[2]['value'] = $dataArray['user_type'];
            }
            // Create user profile property
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "set_profile_prop_batch",
                "user_id" => $apiResponse['info'],
                "property_list" =>json_encode($userProperty)
            );
            $result = aibServiceRequest($postRequestData, "users");
            $url = 'id='.$apiResponse['info'].'&type='.$userType;
            $link = '<a href="'.HOST_PATH.'thank-you.php?'.urlencode($url).'" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>';
            $email_template = str_replace('#confirm_email#',$link,$email_template);
            if($result['status'] =='OK'){
                // Send an email to newly created user.
                $email = sendMail($email_data,$email_template);
                if($email){
                    return $apiResponse;
                }
            }
        }
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getTreeData()
 * @Params1: $folderId(int) -- item id from ftree.
 * @Params2: $assistant(int) -- Check frro whom I am getting the data IF assistant is true, Remove top 2 parents.
 * @Params3: $uid(int) -- requesting user id if not present then logged in user id will be used.
 * @Description: This function is used to get all parents(Bottom to top) of an item.
 * @Dependent API: browse.php.
 * @OP: get_path
 * @return: (array)$apiResponse -- list of all parents.
 */

function getTreeData($folderId = '', $assistant = false,$uid="") {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Check for $uid if not present logged in user id will be used.
        if(empty($uid)){
           $uid= (isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1;
        }
        // Create request for getting parents
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $uid,
            "_op" => "get_path",
            "obj_id" => $folderId
        );
        // Service request for getting parents
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            if ($assistant) {
                return $apiResponse['info']['records'];
            } else {
                if ($_SESSION['aib']['user_data']['user_type'] == 'A') {
                    $apiResponse['info']['records'] = array_slice($apiResponse['info']['records'], 1);
                }
                if ($_SESSION['aib']['user_data']['user_type'] == 'S') {
                    $apiResponse['info']['records'] = array_slice($apiResponse['info']['records'], 2);
                }
                return $apiResponse['info']['records'];
            }
        }
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getRecursiveItemRecord()
 * @Params1: $sessionKey (string) -- Session key used for authenticate the API
 * @Params2: $folderId (int) -- item id from ftree
 * @Description: This function is used to get item child and childs of child.
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array) $finalReturn
 * 
 */
function getRecursiveItemRecord($sessionKey, $folderId) {
    // Create request for getting child list.
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => $_SESSION['aib']['user_data']['user_id'],
        "_op" => "list",
        "parent" => $folderId,
        "opt_sort" => 'TITLE'
    );
    $Record = array();
    // Service request for getting child list.
    $apiResponse = aibServiceRequest($postData, 'browse');
    foreach ($apiResponse['info']['records'] as $key => $dataArray) {
        // Create request for getting child of child list.
        $postData1 = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $dataArray['item_id'],
            "opt_sort" => 'TITLE'
        );
        // Service request for getting childs of child.
        $subResponse = aibServiceRequest($postData1, 'browse');
        if (!empty($subResponse['info']['records'])) {
            foreach ($subResponse['info']['records'] as $key1 => $dataArray1) {
                $Record[$key1]['subItem'] = $dataArray1['item_title'];
                $Record[$key1]['subItemId'] = $dataArray1['item_id'];
            }
            $subRecord['Data'][$key]['ResultItemDetail'] = $dataArray['item_title'];
            $subRecord['Data'][$key]['ResultItemId'] = $dataArray['item_id'];
            $subRecord['Data'][$key]['subItem'] = $Record;
        } else {
            $subRecord['Data'][$key]['ResultItemDetail'] = $dataArray['item_title'];
            $subRecord['Data'][$key]['ResultItemId'] = $dataArray['item_id'];
            $subRecord['Data'][$key]['subItem'] = '';
        }
    }
    $finalReturn = $subRecord['Data'];
    return $finalReturn;
}

/*
 * @Author: Sapple Systems
 * @Method: getItemData()
 * @Params1: $folderId(int) -- Item id from ftree
 * @Params2: $uid(int) -- Requesting user id if not present logged in user will be used.
 * @Description: This function is used to get the item details of requesting item id
 * @Dependent API: browse.php
 * @OP: get
 * @Return: (array)$apiResponse
 */
function getItemData($folderId = '',$uid="") {
    if ($folderId) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Check for user Id if not present logged-in user id will be used.
        if(empty($uid)){
            $uid=(isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1;
        }
        // Create request for getting item details.
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $uid,
            "_op" => "get",
            "obj_id" => $folderId,
            "opt_get_field" => 'Y',
            "opt_get_files" => 'Y',
            "opt_get_property" => 'Y'
        );
        // Service request for getting item details.
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'][0];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getItemChild
 * @Params1: $item_id(int) -- item id from the ftree.
 * @Description: This function is used to get the child list of the requesting item.
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array) $itemId
 */
function getItemChild($item_id = null) {
    if ($item_id) {
        $itemId = [];
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting child list.
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
        // Service request for getting child list.
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            foreach ($apiResponse['info']['records'] as $record) {
                if ($record['item_type'] == 'AR') {
                    $itemId[] = $record['item_id'];
                }
            }
        }
        return $itemId;
    }
}

function getItemChildToOCR($item_id = null) {
    if ($item_id) {
        $itemId = [];
		$itemId[] = $item_id;
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting child list.
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id,
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
        // Service request for getting child list.
        $apiResponse = aibServiceRequest($postData, 'browse');
		//echo '<pre>';print_r($apiResponse['info']['records']);exit;
        if ($apiResponse['status'] == 'OK') {
            foreach ($apiResponse['info']['records'] as $record) {
                if ($record['item_type'] == 'RE' || $record['item_type'] == 'IT') {
					$itemId[] = $record['item_id'];
                }
            }
        }
        return $itemId;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getItemChildCount()
 * @Params1: $item_id(int) -- Requesting item id from ftree.
 * @Params2: $item_type(string) -- Item type of requesting item Id(type: AG, AR, CO, SG, RE)
 * @Params3: $UID(int) -- Requesting user Id if not present used from logged-in user Id
 * @Description: This function is used the getting child count of requesting item's child.
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array) -- count of child for every parents.
 */
function getItemChildCount($item_id = null, $item_type = null,$UID="") {
    if ($item_type != RE) {
        if ($item_id) {
            $sessionKey = $_SESSION['aib']['session_key'];
            // Check for user id and if not present set from logged in user 
            if(empty($UID)){
                $UID=$_SESSION['aib']['user_data']['user_id'];
            }
            // Create request for getting requesting item childs.
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $UID,
                "_op" => "list",
                "parent" => $item_id,
                "opt_get_property" => 'Y',
                "opt_get_long_prop"=> 'Y'
            );
            // Service request for getting requesting item childs.
            $apiResponse = aibServiceRequest($postData, 'browse');
            foreach($apiResponse['info']['records'] as $key=> $dataArray){
                if($item_type == 'AG' || $item_type == 'AR'){
                    // Unset key If is_link is true
                    if($dataArray['is_link'] == 'Y'){
                        unset($apiResponse['info']['records'][$key]);
                    }
                }
                // Check for link class if it is public removed from the list
                if($item_type == 'SG'){
                    if(isset($dataArray['properties']['link_class']) && $dataArray['properties']['link_class'] == 'public'){
                        unset($apiResponse['info']['records'][$key]);
                    }
                }
            }
            $sgCount = 0;
            // Check for Sub groups has another sub groups
            if ($item_type == 'CO' || $item_type == 'SG') {
                foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                    if ($dataArray['item_type'] == 'SG') {
                        $sgCount ++;
                    }
                }
            }
            return ['child_count' => (count($apiResponse['info']['records']) - $sgCount), 'sg_count' => $sgCount];
        }
    } else {
        return false;
    }
}

/*
 * @Author: Sapple Systems 
 * @Method: print_array
 * @Params1: $dataArray(array) -- array to be printed on screen
 * @Params2: $exit(boolean) -- Check if true, Will break the code execution after the function called.
 * @Description: This function is used to print the array on the screen.
 */
function print_array($dataArray = [], $exit = false) {
    echo "<pre>";
    print_r($dataArray);
    echo "</pre>";
    if ($exit)
        exit;
}

/*
 * @Author: Sapple Systems
 * @Method: checkArchiveStatus()
 * @Params1: $userTopFolder(int) -- Logged-in user access level(user_top_folder frrom ftree_user)
 * @Params2: $userType(string) -- Loggid-in user type(user_type from ftree_user it may be(R,A,S,U))
 * @Description: This function is used to check the archive status either its active or not.
 * @Return: (boolean) true/false
 */
function checkArchiveStatus($userTopFolder = null, $userType = null) {
    if ($userTopFolder && $userType) {
        // Check for Root user if true return true.
        if ($userType == 'R') {
            return true;
        } else {
            // Get item details with list of all property
            $archiveDetails = getItemDetailsWithProp($userTopFolder);
            if ($archiveDetails['item_type'] == 'AG') {
                return ($archiveDetails['prop_details']['status'] == 1) ? true : false;
            } else {
                // Get the item all top parents
                $parentsData = getTreeData($userTopFolder);
                $archiveGroupId = isset($parentsData[1]['item_id']) ? $parentsData[1]['item_id'] : $parentsData[0]['item_id'];
                $archiveDetails = getItemDetailsWithProp($archiveGroupId);
                return ($archiveDetails['prop_details']['status'] == 1) ? true : false;
            }
        }
    } else {
        return false;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: aibServiceRequest()
 * @Params1: $postData(array) -- required data in array format to call an API
 * @Params2: $fileName(string) -- File name of the API from it is being called
 * @Params3: $mail(boolean) true/false
 * $return: (array) $outData -- Response from the API
 */
function aibServiceRequest($postData, $fileName, $mail = null) {
    $curlObj = curl_init();
    // Create data for C-url
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
    // Set options for c-url
    curl_setopt_array($curlObj, $options);
    // Execute c-url request
    $result = curl_exec($curlObj);
    if ($result == false) {
        $outData = array("status" => "ERROR", "info" => curl_error($curlObj));
    } else {
        $outData = json_decode($result, true);
    }
    curl_close($curlObj);
    if (isset($outData['info']) && $outData['info'] == 'EXPIRED') {
        generateSession();
    }
    return($outData);
}

/*
 * @Author: Sapple Systems
 * @Method: generateSession()
 * @Dependent API: session.php
 */
function generateSession(){
    // Create request for generating session key
    $postData = array(
        "_id" => APIUSER,
        "_key" => APIKEY
    );
    // Service request for generating session key
    $apiResponse = aibServiceRequest($postData, 'session');
    if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
        $_SESSION['aib']['session_key'] = $apiResponse['info'];
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getUserProfile()
 * @Params1: $userId(int) -- User id from ftree_user
 * @Params2: $loginId(string) user user's name/ logged-in ID to get details
 * @Description: This function is used to get the user profile details by user_id/user_name
 * @Dependent API: users.php
 * @OP: get_profile
 * @Return: (array)$apiResponse['info'] -- contains the requesting user id details.
 */
function getUserProfile($userId='',$loginId=''){
    $sessionKey = $_SESSION['aib']['session_key'];
    // Create request for getting user details.
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "get_profile"            
    );
    // Check for user_id If present append into the requesting array.
    if(!empty($userId)){
        $postData["user_id"]=$userId;
    }
    // Check for user_name if present append into the requesting array.
    if(!empty($loginId)){
        $postData["user_login"]=$loginId;
    }
    // Service request for getting user deetails
    $apiResponse = aibServiceRequest($postData, 'users');
    if ($apiResponse['status'] == 'OK') {
        return $apiResponse['info'];
    }
}

/*
 * @Author: Sapple Systems
 * @Method: sendMail()
 * @Params1: $email_content(array) -- It contains the requird data for sending an email.
 * @Params2: $template (string) -- It contains the html to be send.
 * @Description: This function is used to send the email.
 * @Dependent API: email.php
 * @OP: send
 * @Return: (boolean) true/false
 */
function sendMail($email_content,$template){
    $sessionKey = $_SESSION['aib']['session_key'];
    // Check for requesting parameters as both are rrequired for send an email.
    if(!empty($template) && !empty($email_content)){
        // Create the required data and replaced the contents like logo, header image
        $year = date("Y");
        $logo = '<img src="'.HOST_ROOT_IMAGE_PATH.'logo-aib.png" alt="ArchiveInABox Logo" />';
        $header_image = '<img style="width:100%;" src="'.HOST_ROOT_IMAGE_PATH.'mail-template_header.jpg" alt="Image" />';
        $template = str_replace('#logo#', $logo, $template);
        $template = str_replace('#header_images#', $header_image, $template);
        $template = str_replace('#year#', $year, $template);
        // Create request data for send an email.
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "send", 
            "_user" => 1,
            "to" => $email_content['to'],
            "from" => $email_content['from'],
            "reply" => $email_content['reply'],
            "subject" => $email_content['subject'],
            "body" => $template,
            "is_html" => 'Y'
        );
        // Service request for send an email.
        $apiResponse = aibServiceRequest($postData,'email','send');
        return true;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getUserProfileProperties()
 * @Params1: $user_id(int) -- user id from ftree_user
 * @Params2: $property_name(string) -- property name which value to be find.
 * @Description: This function is used to get the propert value of a user whose property name is passed
 * @Dependent API: users.php
 * @OP: get_profile_prop
 * @Return: (array)$userPropertyList -- property name as key and property value as value in the array.
 */
function getUserProfileProperties($user_id = null, $property_name){
    if($user_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request data for getting property value.
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_profile_prop", //list_profile_prop
            "user_id" => $user_id,
            "property_name" =>$property_name
        );
        // Service request for getting property value.
        $apiResponse = aibServiceRequest($postData, 'users');
        $userPropertyList = [];
        if($apiResponse['status'] == 'OK'){
            $userPropertyList[$apiResponse['info']['property_name']] = $apiResponse['info']['property_value'];
        }
        return $userPropertyList;
    }
}

/*
 * @Author: Sapple Systems 
 * @Method: getUserProfileById()
 * @Params1: $user_id(int) -- user id whose profile data to be find from ftree_user
 * @Description: This function is used to get the user details by Id
 * @Dependent API: users.php
 * @OP: get_profile
 * @Return: (array) $apiResponse['info'] -- details of the user.
 */
function getUserProfileById($user_id = null){
    if($user_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting user details.
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_profile",
            "user_id" =>$user_id
        );
        // Service request for getting user details.
        $apiResponse = aibServiceRequest($postData, 'users');
        if($apiResponse['status'] == 'OK'){
            return $apiResponse['info'];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @Method: setUserProfileStatus()
 * @Params1: $user_id(int) -- user_id from ftree_user
 * @Params2: $status(string) -- status to be updated for a user
 * @Description: This function is used to set the profile status as requested user
 * @Dependent API: users.php
 * @OP: set_profile_prop
 * @Return: (boolean) True/False
 */
function setUserProfileStatus($user_id, $status){
    $userDetails = getUserProfileById($user_id);
    $user_archive_id = $userDetails['user_top_folder'];
    $sessionKey = $_SESSION['aib']['session_key'];
    // Create request for set user profile property.
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => $_SESSION['aib']['user_data']['user_id'],
        "_op" => "set_profile_prop",
        "user_id" =>$user_id,
        "property_name" => 'status',
        "property_value"=> $status
    );
    // Service request for set user profile property.
    $apiResponse = aibServiceRequest($postData, 'users');
    if($apiResponse['status'] == 'OK'){
        return setUserArchiveStatus($user_archive_id, $status);
    }
}

/*
 * @Author: Sapple Systems
 * @Method: setUserArchiveStatus()
 * @Params1: $archive_id(int) -- Item id from ftree
 * @Params2: $status(string) -- Status to be updated for an item
 * @Description: This function is used to set status of an item(Archive/Archive Group)
 * @Dependent API: browse.php
 * @OP: set_item_prop
 * @Return: (boolean) true/false
 */
function setUserArchiveStatus($archive_id = null, $status){
    if($archive_id){
        $status = ($status == 'd') ? 0 : 1;
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for set item property
        $postData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'set_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $archive_id,
            'propname_1' => 'status',
            'propval_1' => $status,
        );
        // Service request for set item property
        $apiResponse = aibServiceRequest($postData, 'browse');
        if($apiResponse['status'] == 'OK'){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: sharedRecordWithUser()
 * @Params1: $sharedUserList(array) -- list of user with whom record to be shared.
 * @Params2: $record_id(array) -- item id from ftree to be shared.
 * @Description: This function is used to share the record with users
 * @Dependent API: sharing.php
 * @OP: share_create
 * @Return: (array)$responseArray
 */
function sharedRecordWithUser($sharedUserList = [], $record_id = null){
    if(!empty($sharedUserList)){
        $sharing_user = $_SESSION['aib']['user_data']['user_id'];
        $sessionKey = $_SESSION['aib']['session_key'];
        $responseArray = [];
        // Loop over the user list with whom record has to be shared.
        foreach($sharedUserList as $userKey=>$user_login){
            $userDetails = getUserProfile('',$user_login);
            $shared_user = $userDetails['user_id'];
            $shared_user_archive_id = $userDetails['user_top_folder'];
            // Check for record already shared with user.
            $checkedForShared = checkForAlreadySharedWithUser($shared_user, $record_id,$shared_user_archive_id);
            if($checkedForShared == 0){
                // Create request for share the record.
                $PostData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => "1",
                    "_op" => "share_create",
                    "share_target" => $shared_user_archive_id,
                    "share_source" => $record_id,
                    "share_title" => "Shared $record_id at ".date('Y-m-d h:i:s'),
                    "share_item_user" => $sharing_user,
                );
                // Service request for share the record.
                $apiResponse = aibServiceRequest($PostData, 'sharing');
                $responseArray[$shared_user.'_'.$user_login] = $apiResponse['info'];
            }
        }
        return $responseArray;
    }
}

/*
 * @Author: Sapple systems
 * @Method: getSharedRecord();
 * @Params1: $shareType(string) -- share type may be share_with_others/share_with_me
 * @Description: This function is used to get the list of item according to share_type
 * @Dependent API: sharing.php
 * @OP: share_list
 * @Return: (array)$finalDataArray -- List of shared type items from ftree
 */
function getSharedRecord($shareType = null){
    $finalDataArray = [];
    if($shareType){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting share type records.
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => $shareType,
            "user_id" => $_SESSION['aib']['user_data']['user_id'],
            "opt_get_property"=>'Y'
        );
        // Service request for getting share type records.
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
	if ($apiResponse['status'] == 'OK') {
            $sharedDataArray = $apiResponse['info']['records'];
            $count = 0;
            // Loop over the responsed data and prepared data for display.
            foreach($sharedDataArray as $key=>$dataArray){
                if($dataArray['item_ref'] != '-1' && $dataArray['properties']['link_class'] != 'public' && $dataArray['properties']['link_class'] != 'related_content'){
                    $recordDetails = getItemData($dataArray['item_ref'],1);
                    // Get item top parents
                    $completeParentDetails = getTreeData($dataArray['item_ref'],true,1);
                    if($recordDetails['item_type'] == 'IT'){
                        $recordDetails['item_record_parent'] = $completeParentDetails[count($completeParentDetails)-2]['item_id'];
                    }
                    // Get item details
                    $parentDetails = getItemData($dataArray['item_parent'],1);
                    $sharedUserId  = $parentDetails['item_user_id'];
                    $recordDetails['user_details_name'] =$parentDetails['item_title'];
                    if($sharedUserId == '' || $sharedUserId == '-1'){
                        $parentDetails = getItemData($completeParentDetails[1]['item_id'],1);
                        $sharedUserId  = $parentDetails['item_user_id'];
                    }
                    // Get user profile details
                    $sharedUserProfile = getUserProfileById($sharedUserId);
                    $recordDetails['user_details'] = $sharedUserProfile;
                    $recordDetails['refrence_details'] = $dataArray;
                    // Get item child counts.
                    $itemCount = getItemChildCount($dataArray['item_ref'], 'SG',1);
                    $recordDetails['child_count'] = $itemCount['child_count'];
                    $recordDetails['refrence_item_id'] = $dataArray['item_id'];
                    $finalDataArray[$count] = $recordDetails;
                    $count ++;
                }
            }
        }
    }
	
    return $finalDataArray;
}

/*
 * @Author: Sapple systems
 * @Method: getSharedRecordItems();
 * @Params1: $folder_id(int) -- Item id from ftree
 * @Description: This function is used to get the list of childs.
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array)$finalDataArray -- List of childs of requesting item
 */
function getSharedRecordItems($folder_id = null){
    $finalDataArray = [];
    if($folder_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for getting child list.
        $postDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folder_id,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_deref_links" => 'Y'
        );
        // Service request for getting child list.
        $apiResponse = aibServiceRequest($postDataArray, 'browse');
        if ($apiResponse['status'] == 'OK') {
            // Loop over the response data for getting files
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                // Loop over the files and get the thumb Id
                foreach ($dataArray["files"] as $FileRecord) {
                    // Check for file type.
                    if ($FileRecord["file_type"] == 'tn') {
                        $apiResponse['info']['records'][$key]['tn_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                    // Check for file type.
                    if ($FileRecord["file_type"] == 'pr') {
                        $apiResponse['info']['records'][$key]['pr_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                }
            }
            $finalDataArray = $apiResponse['info']['records'];
        }
    }
    return $finalDataArray;
}

/*
 * @Author: Sapple Systems
 * @Method: checkForAlreadySharedWithUser()
 * @Params1: $shared_user_id(int) -- user id from ftree_user
 * @Params2: $record_id(int) -- record id to be checked from ftree
 * @Params3: $shared_user_archive_id(int) --  archive if the item from ftree
 * @Description: This function is used for already shared with the requesting user id
 * @Dependent API: sharing.php
 * @OP: share_list
 * @Return: (int)$count
 */
function checkForAlreadySharedWithUser($shared_user_id = null, $record_id = null, $shared_user_archive_id = null){
    if($shared_user_id && $record_id && $shared_user_archive_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for get share list
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'shared_from_user',
            "user_id" => $_SESSION['aib']['user_data']['user_id'],
        );
        $count = 0;
        // Service request for get share list.
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        if ($apiResponse['status'] == 'OK') {
            $sharedWithOtherList = $apiResponse['info']['records'];
            if(!empty($sharedWithOtherList)){
                foreach($sharedWithOtherList as $key=>$sharedDataArray){
                    if($sharedDataArray['item_ref'] == $record_id && $sharedDataArray['item_parent'] == $shared_user_archive_id){
                        $count ++;
                        break;
                    }
                }
            }
        }
        return $count;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getUsersAllSubGroupsListing()
 * @Params1: $user_archive_id(int) -- Requesting archive id from ftree
 * @Params2: $record_sg_id(id) -- Sub group id to be skipped
 * @Description: This function is used to get a sub group listing for a user
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array)$subGroupListArray -- List of subgroups for  user.
 */
function getUsersAllSubGroupsListing($user_archive_id = null, $record_sg_id = null){
    $subGroupListArray = [];
    if($user_archive_id != null){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request data for getting list
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $user_archive_id
        );
        // Service request for list call.
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            foreach($apiResponse['info']['records'] as $key=>$dataArray){
                if($record_sg_id != null){
                    // Check for skipped sub group Id and removed if found.
                    if($dataArray['item_id'] == $record_sg_id){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                if($dataArray['item_type'] != 'SG'){
                    unset($apiResponse['info']['records'][$key]);
                }
                if($dataArray['item_title'] == 'shared out of system'){
                    unset($apiResponse['info']['records'][$key]);
                }
            }
            $subGroupListArray = $apiResponse['info']['records'];
        }
    }
    return $subGroupListArray;
}

/*
 * @Author: Sapple Systems
 * @Method: checkEmailExist()
 * @Params1: $email(string) -- email to validate in the db exists or not.
 * @Description: This function is used to validate the requesting email is present in the db or not.
 * @Dependent API: users.php
 * @OP: user_matching_prop
 * @Return: (array) list of user names.
 */
function checkEmailExist($email){
     $sessionKey = $_SESSION['aib']['session_key'];
     // Create request for  get matching profile.
     $apiEmailSearch = array(
                '_key' => APIKEY,
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                '_op' => 'user_matching_prop',
                '_session' => $sessionKey,
                'property_name' => 'email',
                'property_value' =>$email
            );  
            // Service request for get matching profile.
            $apiResponseEmail = aibServiceRequest($apiEmailSearch, 'users'); 
            $usernmae =[];
            $i = 0;
            if($apiResponseEmail['status']=='OK' && count($apiResponseEmail['info']['records'])>0){
                foreach($apiResponseEmail['info']['records'] as $key => $dataVal){
                    if($dataVal['user_type'] == 'U'){
                       $usernmae[$i] =  $dataVal['user_login']; 
                       ++$i;
                    }
                }
            }else{
                 $usernmae = '';  
            }
    return $usernmae;
}

/*
 * @Author: Sapple Systems
 * @Method: emailList()
 * @Params1: $shared_user_list(array) -- list of users name
 * @Description: This function is used to get the list of emails found with user name
 * @Return: (array)$emails -- list of found email
 */
function emailList($shared_user_list){
    $emails = [];
    // Loop over shared_user_list
    foreach($shared_user_list as $user_login){
        // Get user profile details by user_name
        $userDetails = getUserProfile('',$user_login);
        if(!empty($userDetails)){
            // Get user profile proprty (email)
            $user_email = getUserProfileProperties($userDetails['user_id'], 'email');
            $emails[] = $user_email['email'];
        }
    }
    return $emails;
}

/*
 * @Author: Sapple Systems
 * @Method: getArchiveCode()
 * @Params1: $archive_id(int) -- item id from ftree
 * @Description: This function is used to get code of an item.
 * @Return: (string) $archive_code -- code of an item
 */
function getArchiveCode($archive_id = null){
    $archive_code = '';
    if($archive_id){
        // Get all parents
        $treeData = getTreeData($archive_id, true,1);
        $archive_name = $treeData[1]['item_title'];
        $archiveArray = explode(' ', $archive_name);
        foreach($archiveArray as $archive_string){
            $archive_code .=$archive_string[0];
        }
    }
    return $archive_code;
}

/*
 * @Author: Sapple Systems
 * @Method: getItemListRec()
 * @Params1: $folder_id(int) -- item id from ftree
 * @Description: This function is used to get the child records
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array)$records -- List of records.
 */
function getItemListRec($folder_id){
    $sessionKey = $_SESSION['aib']['session_key'];
    // Create request for list the data
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "list",
        "parent" => $folder_id,
        "opt_get_files" => 'Y'
    );
    // Service request for list the data.
    $apiResponse = aibServiceRequest($postData, 'browse');
    $records = $apiResponse['info']['records'];
    return $records;
}

/*
 * @Author: Sapple Systems
 * @Method: getItemTags()
 * @Params1: $item_id(int) -- item id from ftrre
 * @Description: This function is used to get the tags for an item
 * @Dependent API: tags.php
 * @OP: tags_get
 * @return: (string)comma seperated tags
 */
function getItemTags($item_id = null){
    if($item_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for tags.
        $postData = array(
            "_key"     => APIKEY,
            "_session" => $sessionKey,
            "_user"    => 1,
            "_op"      => "tags_get",
            "obj_id"   => $item_id
        );
        // Service request for tags.
        $apiResponse = aibServiceRequest($postData, 'tags');
        if($apiResponse['status'] == 'OK'){
            return (!empty($apiResponse['info']['records'])) ? implode(',',$apiResponse['info']['records']) : '';
        }
    }
}

/*
 * @Author: Sapple Systems
 * @Method: updateItemTags()
 * @params1: $tags(string) -- list of comma seperated tags to be add for an item.
 * @params2: $item_id(int) -- item id from ftree
 * @Description: This function is used to add tags for an item.
 * @Dependent API: tags.php
 * @OP: tags_add
 * @return: (boolean)true/false
 */
function updateItemTags($tags = null, $item_id = null){
    if($tags != null && $item_id != null){
        $sessionKey = $_SESSION['aib']['session_key'];
        // Create request for add tags
        $postData = array(
            "_key"        => APIKEY,
            "_session"    => $sessionKey,
            "_user"       => 1,
            "_op"         => "tags_add",
            "obj_id"      => $item_id,
            "tags"        => $tags,
            "opt_replace" => 'Y'
        );
        // Service request for add tags.
        $apiResponse = aibServiceRequest($postData, 'tags');
        return ($apiResponse['status'] == 'OK') ? true : false;
    }
}

/*
 * @Author: Sapple Systems
 * @Method: getItemListRecord
 * @Params1: $parent_id(int) -- Item Id from ftree
 * @Description: This function is used to get the child of a requesting parent.
 * @Dependent API: browse.php
 * @OP: list
 * @Return: (array)$apiResponse -- list of data
 */
function getItemListRecord($parent_id = null){
	$sessionKey = $_SESSION['aib']['session_key'];
        // Create request for get list of data
	$anonyPost = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $parent_id,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_get_property" => 'Y' ,
            "opt_deref_links" => 'Y'
        );
        // Service request for get list of data
	$apiResponse = aibServiceRequest($anonyPost, 'browse');	
	return $apiResponse;
	
}

/*
 * @Author: Sapple Systems
 * @Method: getArchiveAdministratorEmails()
 * @Params1: $folder_id(int) -- itrm id from ftree
 * @Params2: $user_type(string) -- requesting user type
 * @Description: This function is used to find the email Id of archive user
 * @Dependent API: users.php
 * @OP: list_profiles
 * @Return: (string)$return -- Comma seperated email Id 
 */
function getArchiveAdministratorEmails($folder_id = null,$user_type=''){
	$sessionKey = $_SESSION['aib']['session_key'];
        // Create request for list profiles
	$postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list_profiles",
            "user_type" => ($user_type !='')?$user_type:'A',
            "user_top_folder" => $folder_id,
            "_prop" => 'Y'
        );
        // Service request for list profiles
        $apiResponse = aibServiceRequest($postData, 'users'); 
	 if ($apiResponse['status'] == 'OK') {
		foreach($apiResponse['info']['records'] as $record){
                    if(isset($record['_properties']['email']) && !empty($record['_properties']['email'])){
			$emailId[] =  urldecode($record['_properties']['email']);
                    }
		}
	 	 
	} 
	$return =  implode(',',$emailId); 
	return $return; 	
}

/*
 * @Author: Sapple Systems
 * @Method: setEbayCountUserProp()
 * @Params1: $prop_name(string) -- property name of an item to be set
 * @Params2: $prop_val(string) -- Property value to be saved.
 * @Description: This function is used to add property of a public user for ebay count.
 * @Dependent API: users.php
 * @OP: set_profile_prop
 * @Return: (string)$apiResponse['status'] -- Api response.
 */
function setEbayCountUserProp($prop_name='',$prop_val = ''){
    $sessionKey = $_SESSION['aib']['session_key'];
    // Create request for set profile property
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "set_profile_prop",
        "user_id" =>$_SESSION['aib']['user_data']['user_id'],
        "property_name" => $prop_name,
        "property_value"=> $prop_val
    );
    // Service request for set profile property
    $apiResponse = aibServiceRequest($postData, 'users');
    return $apiResponse['status']; 
}

/*
 * @Author: Sapple Systems
 * @Method: deleteUserProp()
 * @Params1: $userId(int) -- user id from ftree_user
 * @Params2: $propName(string) -- Property name to be deleted for requesting user.
 * @Description: This function is used to delete the property for user
 * @Dependent API: users.php
 * @OP: del_profile_prop
 * @Return: (string)$apiResponse['status'] -- Api response.
 */
function deleteUserProp($userId ='',$propName =''){
    $sessionKey = $_SESSION['aib']['session_key'];
    // Create request for delete property
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "del_profile_prop",
        "user_id" =>$userId,
        "property_name" => $propName
    );
    // Service request for delete property.
    $apiResponse = aibServiceRequest($postData, 'users');
    return $apiResponse['status']; 
}