<?php
// config.php file has been included here to access all globally defined variables
require_once dirname(__FILE__) . '/config/config.php';

function log_debug_message($Msg) {
    $Handle = fopen("/tmp/services_debug.txt", "a+");
    if ($Handle !== false) {
        fputs($Handle, date("Y-m-d H:i:s") . " / " . sprintf("%0.6lf", microtime(true)) . ": " . $Msg . "\n");
        fclose($Handle);
    }
}

function _recaptchaSiteVerify($captcha_response) {
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
    if ($LocalStreamContext != false) {
        $LocalResult = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $LocalStreamContext);
        // If there was data returned, check
        if ($LocalResult != false) {
            // Decode security string to associative array
            $SecurityInfo = json_decode($LocalResult, true);
            // If the "success" entry is FALSE, it means the verification failed.  Set
            // error message and set flag so the form is reloaded.
            if (isset($SecurityInfo["success"]) == true) {
                if ($SecurityInfo["success"] == false) {
                    $SecurityOk = false;
                    $result = array('status' => 'error', 'message' => 'You are Robot!');
                } else {
                    $SecurityOk = true;
                    $result = array('status' => 'success', 'message' => 'success');
                }
            } else {
                $SecurityOk = false;
                $result = array('status' => 'error', 'message' => 'You are Robot!');
            }
        } else {
            $SecurityOk = false;
            $result = array('status' => 'error', 'message' => 'You are Robot!');
        }
    } else {
        $SecurityOk = false;
        $result = array('status' => 'error', 'message' => 'You are Robot!');
    }
    // If security is still ok, then check for email addresses to block on
    // Decline specific sets of addresses
    while (true) {
        if ($SecurityOk === false) {
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        // Anyone using a .ru domain (Russia)
        if (preg_match("/[\.][Rr][Uu]$/", $EmailAddress) == 1) {
            $SecurityOk = false;
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        if (preg_match("/[\.][Ss][Uu]$/", $EmailAddress) == 1) {
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
    while (true) {
        if ($SecurityOk === false) {
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        // Keywords
        if (preg_match("/payday[^a-z0-9]+loan/", $LowerComment) == 1) {
            $SecurityOk = false;
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        if (preg_match("/bad[^a-z0-9]+credit/", $LowerComment) == 1) {
            $SecurityOk = false;
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        if (preg_match("/website[^a-z0-9]+template/", $LowerComment) == 1) {
            $SecurityOk = false;
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        if (preg_match("/html[^a-z0-9]+template/", $LowerComment) == 1) {
            $SecurityOk = false;
            $result = array('status' => 'error', 'message' => 'You are Robot!');
            break;
        }
        // Embedded web links.  Pattern is "href" followed by zero or more non-equals sign characters,
        // followed by equals, followed by zero or more non-quote characters, followed by value, followed
        // by quotes.
        if (preg_match("/href[^=]*[\=][^\"\']*[\'\"][^\'\"]+[\'\"]/", $Comments) == 1) {
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
    if ($SecurityOk == false) {
        $result = array('status' => 'error', 'message' => 'You are Robot!');
        print("<p><b>There was an error confirming you're not a robot.  Please click ");
        $URL = "/contact_form.php?lastname=$LastName&firstname=$FirstName&email=$EmailAddress";
        $URL .= "&usage=$Usage&comments=$Comments";
        print("<a href=\"$URL\"><font color='red'>HERE</font></a> to return to the request form.</b>");
        print("</p>");
        exit(0);
    }
}

function recaptchaSiteVerify($captcha_response) {
    $result = array();
    if (isset($captcha_response) && !empty($captcha_response)) {
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

$message = (array) json_decode(MESSAGE);
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
        $responseData = array('status' => 'error', 'message' => 'Login failed');
        // Parse $_POST['formData'] into $postData variable       
        parse_str($_POST['formData'], $postData);
        // Request array to get user profile         
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'get_profile',
            "_user" => 1,
            "user_login" => ($postData['username'] != '') ? $postData['username'] : 'CodyFrance'
        );
        // Service request to get user profile
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            $_SESSION['aib']['session_key'] = $apiResponse['session'];
            $_SESSION['aib']['user_data'] = $apiResponse['info'];
            $responseData = array('status' => 'success', 'message' => 'Successfully longed in');
        }
        print json_encode($responseData);
        break;
    case 'login_user':
        // Parse $_POST['formData'] into $postData variable 
        parse_str($_POST['formData'], $postData);
        // Get Item details with all property of ftree item         
        $terms_condition_prop = getItemDetailsWithProp(1);
        $terms_condition = $terms_condition_prop['prop_details']['timestamp'];
        $termCondition = 'Y';
        $responseData = array('status' => 'error', 'message' => 'You are Robot!');
        $current_time = time();
        $time_diff = $current_time - $postData['timestamp_value'];
        if ($time_diff > TIMESTAMP_2) {
            // Request array to login details by username & password                   
            $requestPostData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => 'login',
                '_user' => 1,
                "user_login" => $postData['username'],
                "user_pass" => $postData['password']
            );
            // Service request to login details by username & password         
            $apiResponse = aibServiceRequest($requestPostData, 'users');
            $_SESSION['aib']['session_key'] = $apiResponse['session'];
            if ($apiResponse['status'] == 'OK') {
                // Request array to get user profile            
                $postDataLogin = array(
                    "_key" => APIKEY,
                    "_session" => $apiResponse['session'],
                    "_op" => 'get_profile',
                    "_user" => 1,
                    "user_login" => $postData['username']
                );
                // Service request to get user profile
                $apiResponse = aibServiceRequest($postDataLogin, 'users');

                $item_arr = getItemData($apiResponse['info']['user_top_folder']);
                $item_title = $item_arr['item_title'];
                $claimed_user = $apiResponse['info']['properties']['claimed_user'];
                $unclaimed_society_id = $apiResponse['info']['properties']['unclaimed_society_id'];
                $admin_action = $apiResponse['info']['properties']['admin_action'];
                $claimed_user_approved = $apiResponse['info']['properties']['claimed_user_approved'];

                // Get user all property details by user id            
                $apiUserProp = getUsersAllProperty($apiResponse['info']['user_id']);
                if (!in_array($apiUserProp['status'], $apiUserProp)) {
                    // Set user profile status a ('active')               
                    $setUserStatus = setUserProfileStatus($apiResponse['info']['user_id'], 'a');
                    if ($setUserStatus) {
                        $userStatus = 'a';
                    }
                }
                if ($apiResponse['info']['user_type'] == 'R') {
                    $apiResponse['info']['user_top_folder'] = 1;
                }
                $userTopFolder = $apiResponse['info']['user_top_folder'];
                $userType = $apiResponse['info']['user_type'];
                if ($apiResponse['info']['user_type'] == 'U' || $apiResponse['info']['user_type'] == 'S') {
                    $archiveStatus = true;
                } else {
                    // Check archive status for admin user               
                    $archiveStatus = checkArchiveStatus($userTopFolder, $userType);
                }

                if ($claimed_user_approved == 'N') {//$claimed_user == '1' && $unclaimed_society_id != '' && 
                    $responseData = array('status' => 'error', 'message' => 'Your request to become the admin for ' . $item_title . ' is still pending. Once you have been approved we will send you an email to alert you and your changes will be displayed on the public side.');
                } else {
                    if ($apiUserProp['status'] == 'a' || $userStatus == 'a') {
                        if ($archiveStatus) {
                            // Request array to get ftree item property                     
                            $apiRequestDataNew = array(
                                '_key' => APIKEY,
                                '_user' => 1,
                                '_op' => 'get_item_prop',
                                '_session' => $apiResponse['session'],
                                'obj_id' => $apiResponse['info']['user_top_folder']
                            );
                            // Service request to get item property                   
                            $apiResponseNew = aibServiceRequest($apiRequestDataNew, 'browse');
                            if ($apiResponseNew['status'] == 'OK') {
                                if (isset($apiResponseNew['info']['records']['archive_logo_image']) and $apiResponseNew['info']['records']['archive_logo_image'] != '') {
                                    $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $apiResponseNew['info']['records']['archive_logo_image'];
                                    $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $apiResponseNew['info']['records']['archive_header_image'];
                                    $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $apiResponseNew['info']['records']['archive_details_image'];
                                }
                            }
                            if ($apiResponse['status'] == 'OK') {
                                // Get item data of ftree                        
                                $itemTitleName = getItemData($apiResponse['info']['user_top_folder']);
                                if ($apiResponse['info']['user_type'] == 'U' || $apiResponse['info']['user_type'] == 'S' || $apiResponse['info']['user_type'] == 'A') {
                                    $usert_timestamp = isset($apiUserProp['timestamp']) ? $apiUserProp['timestamp'] : '';
                                    if ($terms_condition > $usert_timestamp) {
                                        $termCondition = 'N';
                                    }
                                    $item_title = $itemTitleName['item_title'];
                                }
                                $_SESSION['aib']['session_key'] = $apiResponse['session'];
                                $_SESSION['aib']['user_data'] = $apiResponse['info'];
                                $_SESSION['aib']['user_data']['user_prop'] = $apiUserProp;
                                $_SESSION['aib']['user_data']['item_title'] = $item_title;
                                $_SESSION['aib']['user_data']['terms_condition'] = $termCondition;
                            }
                            $responseData = array('status' => 'success', 'message' => 'Login successfully');
                        } else {
                            $responseData = array('status' => 'error', 'message' => 'temporarly your archive group is deactivated.');
                        }
                    } else {
                        $responseData = array('status' => 'error', 'message' => 'Your account is deactivated.');
                    }
                }
            } else {
                $responseData = array('status' => 'error', 'message' => $message[$apiResponse['info']]);
            }
        }
        print json_encode($responseData);
        break;
    case 'list_tree_items':
        $folderId = isset($_POST['folder_id']) ? $_POST['folder_id'] : "";
        //unset($_SESSION['load_more_count']); exit;
        $start_result = 0;
        $sbgroup = [];
        $scraapbookArray = [];
        $count = 0;
        // Defined count variable to show data on page         
        //$resultCount = PUBLIC_COUNT_PER_PAGE;
        $resultCount = PUBLIC_COUNT_PER_PAGE + ($_SESSION['load_more_count'][$folderId] * PUBLIC_COUNT_PER_PAGE);
        if (isset($_POST['start_result']) && $_POST['start_result'] != '') {
            $start_result = $_POST['start_result'] + $resultCount;
        }
        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : "";
        $share = isset($_POST['share']) ? $_POST['share'] : "0";
        if ($itemId != '')
            $share = 0;
        $detailPagecontent = isset($_POST['detailPagecontent']) ? $_POST['detailPagecontent'] : "N";
        //Filter Options        
        $filter['state'] = !empty($_POST['state']) ? $_POST['state'] : "";
        $filter['county'] = !empty($_POST['county']) ? $_POST['county'] : "";
        $filter['city'] = !empty($_POST['city']) ? $_POST['city'] : "";
        $filter['zip'] = !empty($_POST['zip']) ? $_POST['zip'] : "";
        // Get item tree data of ftree    
        $treeDataArray = getTreeData($folderId);
        $root_parent_id = $treeDataArray[1]['item_id'];
        $_SESSION['clicked_item'][$treeDataArray[count($treeDataArray) - 2]['item_id']] = $folderId;
        $scrapbookItem = ($treeDataArray[count($treeDataArray) - 2]['item_title'] == 'Scrapbooks') ? 'yes' : 'no';
        $clickedItem = isset($_SESSION['clicked_item'][$folderId]) ? $_SESSION['clicked_item'][$folderId] : '';
        // Get item property of ftree item         
        $userPropertyArray = $treeDataArray[1]['properties'];
        if (isset($treeDataArray[1])) {
            $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_logo_image'];
            $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_header_image'];
            $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_details_image'];
        }
        // Get item data of ftree item        
        $itemData = getItemData($folderId);
        $shortBy = (isset($itemData['properties']['sort_by']) && $itemData['properties']['sort_by'] != '') ? $itemData['properties']['sort_by'] : 'TITLE';
        //Start changes for optimize call
        switch ($itemData['item_type']) {
            case 'IT':
                $requiredParams = [];
                break;
            case 'AG':
                $search_filter = array(array('name' => 'aibftype', "value" => "col"), array('name' => 'is_advertisements', "value" => "Y"), array('name' => 'visible_to_public', "value" => 0));
                $requiredParams = ['opt_get_prop_count' => 'Y', 'opt_prop_count_set' => json_encode($search_filter)];
                break;
            case 'AR':
                $search_filter = array(array('name' => 'aibftype', "value" => "sg"), array('name' => 'is_advertisements', "value" => "Y"), array('name' => 'visible_to_public', "value" => 0));
                $requiredParams = ['opt_get_prop_count' => 'Y', 'opt_prop_count_set' => json_encode($search_filter)];
                break;
            case 'CO':
                $search_filter = array(array('name' => 'aibftype', "value" => "rec"), array('name' => 'aibftype', "value" => "sg"), array('name' => 'is_advertisements', "value" => "Y"), array('name' => 'visible_to_public', "value" => 0), array('name' => 'aib:private', "value" => "Y"));
                $requiredParams = ['opt_get_prop_count' => 'Y', 'opt_prop_count_set' => json_encode($search_filter)];
                break;
            case 'SG':
                $search_filter = array(array('name' => 'aibftype', "value" => "rec"), array('name' => 'aibftype', "value" => "sg"), array('name' => 'aibftype', "value" => "IT"), array('name' => 'is_advertisements', "value" => "Y"), array('name' => 'aib:private', "value" => "Y"), array('name' => 'link_class', "value" => "public"), array('name' => 'link_class', "value" => "historical_connection"));
                $requiredParams = ['opt_get_prop_count' => 'Y', 'opt_get_files' => 'Y', 'opt_prop_count_set' => json_encode($search_filter)];
                break;
            case 'RE':
                $requiredParams = ['opt_get_files' => 'Y'];
                break;
            default :
                break;
        }
        //End changes for optimize call
        // Request array to get tree item data of an item of ftree
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "opt_sort" => $shortBy,
            "parent" => $folderId,
            "opt_deref_links" => 'Y',
            "opt_get_property" => 'Y',
            "opt_get_link_source_properties" => 'Y',
            "opt_get_long_prop" => 'Y',
            "opt_get_root_folder" => 'Y'
        );
        // Service request to get tree item data of an item of ftree 
        $completeRequestData = array_merge($postData, $requiredParams);
        $apiResponse = aibServiceRequest($completeRequestData, 'browse');
        //echo $_SESSION['aib']['user_data']['user_type'].' - '.$_SESSION['aib']['user_data']['user_top_folder'].' - '.$root_parent_id;exit;
        if (isset($apiResponse['info']['records'])) {
            // Used foreach loop over api response and check conditions   
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $itemProperty = $dataArray['properties'];
                // check visible to public condition & unset $apiResponse['info']['records'][$key]      
                if (($_SESSION['aib']['user_data']['user_type'] == 'R') || ($_SESSION['aib']['user_data']['user_type'] == 'A' && $_SESSION['aib']['user_data']['user_top_folder'] == $root_parent_id)) {
                    // admin can see the unvisible and private item
                } else {
                    if (isset($dataArray['properties']['aib:visible']) && $dataArray['properties']['aib:visible'] == 'N') {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }

                if (isset($dataArray['properties']['publish_status']) && $dataArray['properties']['publish_status'] == 'N') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check visible to public condition & unset $apiResponse['info']['records'][$key]  
                if (($_SESSION['aib']['user_data']['user_type'] == 'R') || ($_SESSION['aib']['user_data']['user_type'] == 'A' && $_SESSION['aib']['user_data']['user_top_folder'] == $root_parent_id)) {
                    // admin can see the unvisible and private item
                } else {
                    if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0') {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }

                // check item type cmntset & unset $apiResponse['info']['records'][$key] 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'cmntset') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['link_properties']['link_class']) && in_array($dataArray['link_properties']['link_class'], array('related_content', 'historical_connection', 'scrapbook'))) {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check item type AG & unset $apiResponse['info']['records'][$key] 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'AG') {
                    $apiResponse['info']['records'][$key]['property_list'] = $itemProperty;
                    if ($itemProperty['status'] == 0) {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                // check item title advertisements & unset $apiResponse['info']['records'][$key] 
                if ($dataArray['item_title'] == 'Scrapbooks') {
                    $scraapbookArray[] = $apiResponse['info']['records'][$key];
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (in_array(strtolower($dataArray['item_title']), array('advertisements', 'shared out of system'))) {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check item type RE 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'RE') {
                    $apiResponse['info']['records'][$key]['RE_property'] = $itemProperty;
                    $sheare_user = json_decode($itemProperty['share_user']);
                    // check item type RE & unset $apiResponse['info']['records'][$key] 
                    if (($_SESSION['aib']['user_data']['user_type'] == 'R') || ($_SESSION['aib']['user_data']['user_type'] == 'A' && $_SESSION['aib']['user_data']['user_top_folder'] == $root_parent_id)) {
                        // admin can see the unvisible and private item
                    } else {
                        if ($itemData['item_type'] == 'RE' && $itemProperty['aib:visible'] == 'N') {
                            unset($apiResponse['info']['records'][$key]);
                            continue;
                        } elseif ($itemProperty['aib:visible'] == 'N' || ($itemProperty['aib:visible'] == 'Y' && $itemProperty['aib:private'] == 'Y')) {
                            unset($apiResponse['info']['records'][$key]);
                            continue;
                        }
                    }
                }
                // check item type IT 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'IT') {
                    $index = count($treeDataArray) - 1;
                    $treeData = $treeDataArray[$index];
                    $view_item_id = $folderId;
                    if (!empty($treeData['item_id'])) {
                        $view_item_id = $treeData['item_id'];
                    }
                    // item property due to tree data
                    // $itemProperty=getItemProperty($view_item_id);   
                    $sheare_user = json_decode($itemProperty['share_user']);
                    if ($_SESSION['aib']['user_data']['user_type'] == 'R') { //Pass
                    } else if (($_SESSION['aib']['user_data']['user_type'] == 'A' || $_SESSION['aib']['user_data']['user_type'] == 'U') && $treeDataArray[1]['item_id'] == $_SESSION['aib']['user_data']['user_top_folder']) {
                        
                    } else {
                        if ($apiResponse['properties']['aib:visible'] == 'N') {
                            echo "false";
                            exit;
                        }
                    }
                }
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'SG' && end($treeDataArray)['item_type'] == 'SG') {
                    $sbgroup[$count] = $apiResponse['info']['records'][$key];
                    unset($apiResponse['info']['records'][$key]);
                    $count++;
                }
            }
        }
        $apiResponse['info']['records'] = array_values($apiResponse['info']['records']);
        if (isset($itemData['item_type']) && in_array($itemData['item_type'], array('RE', 'SG'))) {
            // Used foreach loop over api response            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                if (!empty($dataArray["files"])) {
                    $ThumbID = false;
                    $PrimaryID = false;
                    // Used foreach loop over $dataArray["files"]                 
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
                    if ($itemId != '' && $dataArray['item_id'] == $itemId) {
                        $itemArrayKey = $key;
                    }
                }
            }
        }
        if (isset($apiResponse['info']['records'])) {
            $subgroupRecordId = array();
            $arrKey = 0;
            // Used foreach loop over api response & check conditions 
            foreach ($apiResponse['info']['records'] as $itemKey => $itemDataArray) {
                //  Check item type AG              
                if ($itemData['item_type'] == 'AG') {
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'] - ($itemDataArray['property_counts'][2]['count'] + $itemDataArray['property_counts'][3]['count']);
                }
                //  Check item type AR
                if ($itemData['item_type'] == 'AR') {
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'] - ($itemDataArray['property_counts'][2]['count'] + $itemDataArray['property_counts'][3]['count']);
                }
                //  Check item type CO
                if ($itemData['item_type'] == 'CO') {
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'] - ($itemDataArray['property_counts'][3]['count'] + $itemDataArray['property_counts'][4]['count'] + $itemDataArray['property_counts'][5]['count']);
                    if ($itemDataArray['property_counts'][2]['count'] != 0) {
                        $apiResponse['info']['records'][$itemKey]['sg_count'] = $itemDataArray['property_counts'][2]['count'] - ($itemDataArray['property_counts'][3]['count']);
                    }
                }
                //  Check item type SG
                if ($itemData['item_type'] == 'SG') {
                    if ($itemDataArray['item_type'] == 'SG') {
                        $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'];
                    } else {
                        $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][3]['count'] - ($itemDataArray['property_counts'][4]['count'] + $itemDataArray['property_counts'][5]['count'] + $itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][7]['count']);
                    }
                    if ($itemDataArray['property_counts'][2]['count'] != 0) {
                        $apiResponse['info']['records'][$itemKey]['sg_count'] = $itemDataArray['property_counts'][2]['count'] - ($itemDataArray['property_counts'][4]['count']);
                    }
                    if (count($sbgroup) > 0) {
                        $apiResponse['info']['records'][$itemKey]['sg_count'] = count($sbgroup);
                    }
                }
                if (in_array($itemData['item_type'], array('AG', 'AR', 'CO'))) {
                    if ($apiResponse['info']['records'][$itemKey]['is_link'] == 'Y') {
                        unset($apiResponse['info']['records'][$itemKey]);
                        continue;
                    }
                }
                if ($itemData['item_type'] == 'RE') {
                    if ($apiResponse['info']['records'][$itemKey]['item_type'] != 'IT') {
                        unset($apiResponse['info']['records'][$itemKey]);
                        continue;
                    }
                }
                $logintopFolder = (isset($_SESSION['aib']['user_data']['user_top_folder']) && $_SESSION['aib']['user_data']['user_top_folder'] != '') ? $_SESSION['aib']['user_data']['user_top_folder'] : 0;

                $itemTopFolder = -100;
                if (isset($apiResponse['info']['records'][$itemKey]['root_info']['archive_group']['item_id']) and $apiResponse['info']['records'][$itemKey]['root_info']['archive_group']['item_id'] != '') {
                    $itemTopFolder = $apiResponse['info']['records'][$itemKey]['root_info']['archive_group']['item_id'];
                } elseif (isset($apiResponse['info']['records'][$itemKey]['root_info']['archive']['item_id']) and $apiResponse['info']['records'][$itemKey]['root_info']['archive']['item_id'] != '') {
                    $itemTopFolder = $apiResponse['info']['records'][$itemKey]['root_info']['archive']['item_id'];
                }
                $private_records = '';
                if (($_SESSION['aib']['user_data']['user_type'] == 'R') || ($_SESSION['aib']['user_data']['user_type'] == 'A' && $_SESSION['aib']['user_data']['user_top_folder'] == $root_parent_id)) {
                    // admin can see the unvisible and private item
                } else {
                    if ($apiResponse['info']['records'][$itemKey]['properties']['aib:private'] == 'Y' and ! in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user) == true and ( $share == 0 and $itemId != $apiResponse['info']['records'][$itemKey]['item_id']) and $logintopFolder != $itemTopFolder) {
                        $private_records = 'yes';
                        unset($apiResponse['info']['records'][$itemKey]);
                        continue;
                    }
                }

                if ($itemData['item_type'] == 'SG') {
                    $subgroupRecordId[$arrKey] = $itemDataArray['item_id'];
                    $arrKey++;
                }
            }
        }

        // VIshnu
        $ebayCheckCondition = '';
        // Get item data of ftree item
        //$ebayApiStatus = getItemData($apiResponse['info']['records'][0]['root_info']['archive_group']['item_id']); 
        $ebayApiStatus = $treeDataArray[1];
        if ($apiResponse['info']['records'][0]['item_type'] != 'AR') {
            $ebayCheckCondition = $ebayApiStatus['properties']['ebay_status'];
        }
        // Apply Filter Rules
        // Get item details with all property of ftree item
        $userProfileImage = getItemDetailsWithProp($_POST['previousId']);
        $user_real_image = $userProfileImage['properties']['archive_group_thumb'];
        // User image path       
        $UserImagePath = HOST_PATH . 'admin/tmp/' . $user_real_image;
        // Apply filter on api response          
        $apiResponse = applyUserFilter($apiResponse, $filter);
        $_SESSION['data_detail_page'] = $apiResponse;
        if (count($subgroupRecordId) > 0) {
            $_SESSION['subgroup_record_ids'] = $subgroupRecordId;
        }

        if ($itemData['item_type'] == 'RE') {
            $totalPageOfItem = 1;
            if (isset($_POST['total_item_page']) && $_POST['total_item_page'] != '') {
                $totalPageOfItem = $_POST['total_item_page'];
            }
            $start = 0;
            if (isset($_POST['start']) && $_POST['start'] != '') {
                if ($_POST['start'] == 0) {
                    $start = ITEM_COUNT_PER_PAGE;
                } else {
                    $start = $_POST['start'] + ITEM_COUNT_PER_PAGE;
                }
            }
            $totalItems = count($apiResponse['info']['records']);
            $totalRow = ($itemArrayKey + 1) / 3;
            if ($totalRow === intval($totalRow)) {
                $start = (intval($totalRow) - 1) * 3;
                $totalPageOfItem = intval($totalRow);
            } else {
                $start = intval($totalRow) * 3;
                $totalPageOfItem = intval($totalRow) + 1;
            }
            $apiResponse = apiFilterPaginationData($apiResponse, $start, ITEM_COUNT_PER_PAGE);
            $arrRecordsId = $_SESSION['subgroup_record_ids'];
            if ($treeDataArray[1]['item_type'] == 'AR' && isset($_SESSION['pepole_record_ids'])) {
                $arrRecordsId = $_SESSION['pepole_record_ids'];
            }
            if (isset($_POST['recordIdsArr']) && !empty($_POST['recordIdsArr'])) {
                $arrRecordsId = json_decode($_POST['recordIdsArr'], true);
            }
            if (count($arrRecordsId) > 0) {
                $subArrayKey = array_search($folderId, $arrRecordsId);
                end($arrRecordsId);
                $Lastkey = key($arrRecordsId);
                $nextKeyId = '';
                $preKeyId = '';
                if ($subArrayKey > 0) {
                    $preKeyId = $arrRecordsId[$subArrayKey - 1];
                }
                if (intval($Lastkey) > intval($subArrayKey)) {
                    $nextKeyId = $arrRecordsId[$subArrayKey + 1];
                }
            }
        }
        $countArchive = '';
        $countArchive = count($apiResponse['info']['records']);
        if ($apiResponse['status'] == 'OK' && $itemData['item_type'] != 'RE') {
            // home-content.php file has been included to show details of collection , subgroup & record of an archive
            if ($itemData['item_type'] == 'AG') {
                $archiveGroupId = $treeDataArray[1]['item_id'];
                $totalArchive = count($apiResponse['info']['records']);
                $societyScrapbookListing = getSocietyScrapbookListing($archiveGroupId);
            }
            $totalRecords = count($apiResponse['info']['records']);
            $totalCount = PUBLIC_COUNT_PER_PAGE + ($_SESSION['load_more_count'][$folderId] * PUBLIC_COUNT_PER_PAGE);
            $apiResponse = apiFilterArchivePaginationData($apiResponse, $start_result, $totalCount);
            $start_result = ($totalCount - PUBLIC_COUNT_PER_PAGE);
            if (count($sbgroup) != 0) {
                foreach ($sbgroup as $key => $value) {
                    $sbgroup[$key]['child_count'] = $value['property_counts'][1]['count'] - ($value['property_counts'][5]['count'] + $value['property_counts'][6]['count'] + $value['property_counts'][7]['count']);
                    $sbgroup[$key]['sg_count'] = $value['property_counts'][2]['count'] - ($value['property_counts'][5]['count']);
                }
                if (!empty($apiResponse['info']['records'])) {
                    $apiResponse['info']['records'] = array_merge($apiResponse['info']['records'], $sbgroup);
                } else {
                    $apiResponse['info']['records'] = $sbgroup;
                }
            }
            include_once TEMPLATE_PATH . 'home-content.php';
        } else {
            $detailsPageData = array();
            // details-description.php has been included to show item details  
            if (isset($_POST['start']) && $_POST['start'] != '') {
                include_once TEMPLATE_PATH . 'load-more-item.php';
            } else {
                $themeName = isset($treeDataArray[1]['properties']['details_page_design']) ? $treeDataArray[1]['properties']['details_page_design'] : '';
                //$themeName = 'custom1';
                if ($themeName == 'custom') {
                    include_once TEMPLATE_PATH . 'details-content.php';
                } elseif ($themeName == 'custom1') {
                    include_once TEMPLATE_PATH . 'details-custom-content.php';
                } else {
                    include_once TEMPLATE_PATH . 'details-description.php';
                }
            }
        }
        break;

    case 'list_tree_items_detail_pagination' :
        $folderId = isset($_POST['folder_id']) ? $_POST['folder_id'] : "";
        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : "";
        $share = isset($_POST['share']) ? $_POST['share'] : "0";
        if ($itemId != '')
            $share = 0;
        $detailPagecontent = isset($_POST['detailPagecontent']) ? $_POST['detailPagecontent'] : "N";
        //Filter Options        
        $filter['state'] = !empty($_POST['state']) ? $_POST['state'] : "";
        $filter['county'] = !empty($_POST['county']) ? $_POST['county'] : "";
        $filter['city'] = !empty($_POST['city']) ? $_POST['city'] : "";
        $filter['zip'] = !empty($_POST['zip']) ? $_POST['zip'] : "";
        // Get item tree data of ftree    
        $treeDataArray = getTreeData($folderId);
        // Get item property of ftree item         
        //$userPropertyArray = getItemProperty($treeDataArray[1]['item_id']);
        $userPropertyArray = $treeDataArray[1]['properties'];
        if (isset($treeDataArray[1])) {
            $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_logo_image'];
            $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_header_image'];
            $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_details_image'];
        }
        // Get item data of ftree item        
        $itemData = getItemData($folderId);
        $apiResponse = $_SESSION['data_detail_page'];
        $totalPageOfItem = 1;
        if (isset($_POST['total_item_page']) && $_POST['total_item_page'] != '') {
            $totalPageOfItem = $_POST['total_item_page'];
        }
        if (isset($_POST['start']) && $_POST['start'] != '') {
            if ($_POST['start'] == 0) {
                $start = ITEM_COUNT_PER_PAGE;
            } else {
                $start = $_POST['start'] + ITEM_COUNT_PER_PAGE;
            }
        }
        if (isset($_POST['action']) && $_POST['action'] == 'Back') {
            $start = $start - 6;
        }
        $apiResponse = apiFilterPaginationData($apiResponse, $start, ITEM_COUNT_PER_PAGE);
        if ($apiResponse['status'] == 'OK' && $itemData['item_type'] != 'RE') {
            // home-content.php file has been included to show details of collection , subgroup & record of an archive             
            include_once TEMPLATE_PATH . 'home-content.php';
        } else {
            $detailsPageData = array();
            // details-description.php has been included to show item details   

            if (isset($_POST['start']) && $_POST['start'] != '') {
                include_once TEMPLATE_PATH . 'load-more-item.php';
            } else {
                include_once TEMPLATE_PATH . 'details-description.php';
            }
        }
        break;
    case 'list_tree_items_with_pagination' :
        $folderId = isset($_POST['folder_id']) ? $_POST['folder_id'] : "";
        $_SESSION['load_more_count'][$folderId] = ($_SESSION['load_more_count'][$folderId] + 1);
        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : "";
        $share = isset($_POST['share']) ? $_POST['share'] : "0";
        if ($itemId != '')
            $share = 0;
        $detailPagecontent = isset($_POST['detailPagecontent']) ? $_POST['detailPagecontent'] : "N";
        //Filter Options        
        $filter['state'] = !empty($_POST['state']) ? $_POST['state'] : "";
        $filter['county'] = !empty($_POST['county']) ? $_POST['county'] : "";
        $filter['city'] = !empty($_POST['city']) ? $_POST['city'] : "";
        $filter['zip'] = !empty($_POST['zip']) ? $_POST['zip'] : "";
        // Get item tree data of ftree    
        $treeDataArray = getTreeData($folderId);
        // Get item property of ftree item 
        $userPropertyArray = $treeDataArray[1]['properties'];
        //$userPropertyArray = getItemProperty($treeDataArray[1]['item_id']);
        if (isset($treeDataArray[1])) {
            $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_logo_image'];
            $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_header_image'];
            $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_details_image'];
        }
        // Get item data of ftree item        
        $itemData = getItemData($folderId);
        $apiResponse = $_SESSION['data_detail_page'];
        $totalPageOfItem = 1;
        if (isset($_POST['total_item_page']) && $_POST['total_item_page'] != '') {
            $totalPageOfItem = $_POST['total_item_page'];
        }
        if (isset($_POST['start_result'])) {
            if ($_POST['start_result'] == 0) {
                $start = PUBLIC_COUNT_PER_PAGE;
            } else {
                $start = $_POST['start_result'] + PUBLIC_COUNT_PER_PAGE;
            }
        }
        $apiResponse = apiFilterArchivePaginationData($apiResponse, $start, PUBLIC_COUNT_PER_PAGE);
        include_once TEMPLATE_PATH . 'load-more-item.php';
        break;
    case 'list_tree_public_items':
        // Defined start result page variable for public         
        $stratPage = 0;
        $countArchive = 0;
        // Defined count variable to show data on page     
        $folderId = !empty($_POST['folder_id']) ? $_POST['folder_id'] : "";
        $itemId = !empty($_POST['itemId']) ? $_POST['itemId'] : "";
        $filter['state'] = !empty($_POST['state']) ? $_POST['state'] : "";
        $filter['county'] = !empty($_POST['county']) ? $_POST['county'] : "";
        $filter['city'] = !empty($_POST['city']) ? $_POST['city'] : "";
        $filter['zip'] = !empty($_POST['zip']) ? $_POST['zip'] : "";
        // Get ftree data        
        $treeDataArray = getTreeData($folderId);
        $parentIndex = $treeDataArray[count($treeDataArray) - 2];
        $currentParentId = isset($parentIndex['item_id']) ? $parentIndex['item_id'] : '';
        // Get ftree data
        $itemData = getItemData($folderId);
        $search_filter = array(array('name' => 'aibftype', "value" => "rec"), array('name' => 'aibftype', "value" => "sg"), array('name' => 'aibftype', "value" => "col"), array('name' => 'aibftype', "value" => "IT"), array('name' => 'aibftype', "value" => "scrpbkent"), array('name' => 'aib:private', "value" => "Y"), array('name' => 'link_class', "value" => "public"), array('name' => 'visible_to_public', "value" => 0));
        // Request array to get item tree details of a ftree item       
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "opt_sort" => 'TITLE',
            "parent" => $folderId,
            "opt_get_files" => 'Y',
            "opt_deref_links" => 'Y',
            "opt_get_property" => 'Y',
            "opt_get_long_prop" => 'Y',
            "opt_get_prop_count" => 'Y',
            "opt_get_link_source_properties" => 'Y',
            "opt_prop_count_set" => json_encode($search_filter)
        );
        // Service request to get item tree details of a ftree item 
        $apiResponse = aibServiceRequest($postData, 'browse');
        if (isset($apiResponse['info']['records'])) {
            // Used foreach loop on api response & check conditions            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $defaultItemProperty = $dataArray['properties']; //getItemProperty($dataArray['item_id']);
                // Check root user & unset $apiResponse['info']['records'][$key]                
                if ($folderId == PUBLIC_USER_ROOT) {
                    if (!isset($defaultItemProperty['status']) || $defaultItemProperty['status'] == 0) {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                $apiResponse['info']['records'][$key]['default_property'] = $defaultItemProperty;
                // Get item details with all item property                 
                $item_property = getItemDetailsWithProp($dataArray['item_id']);
                // Check item title equal to advertisements   
                if (in_array(strtolower($dataArray['item_title']), array('advertisements', 'shared out of system'))) {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // Check visible_to_public equal to 0
                if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // Check link_class equal to related_content
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'related_content') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // Check item_type equal to RE 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'RE') {
                    $itemProperty = $dataArray['properties'];
                    $apiResponse['info']['records'][$key]['RE_property'] = $itemProperty;
                    $sheare_user = json_decode($itemProperty['share_user']);
                    if ($itemProperty['aib:visible'] == 'N' || ($itemProperty['aib:visible'] == 'Y' && $itemProperty['aib:private'] == 'Y' && (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) == true)) {
                        unset($apiResponse['info']['records'][$key]);
                    }
                }
                if (isset($dataArray['is_link']) && $dataArray['is_link'] == 'Y') {
                    // unset($apiResponse['info']['records'][$key]);
                }
            }
        }
        // Get item tree data by folder id ( item parent id)        
        $treeDataArray = getTreeData($folderId);
        // Check item title Scrapbooks         
        if ($treeDataArray[count($treeDataArray) - 1]['item_title'] == 'Scrapbooks') {
            // Used foreach loop over api response for get item details with all property of each array element            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                // Get item details with all property                 
                $item_property = getItemDetailsWithProp($dataArray['item_id']);
                // Check scrapbook type public               
                if ($item_property['prop_details']['scrapbook_type'] != 'public') {
                    if (!empty($_SESSION['aib']['user_data']['user_login']))
                        for ($i = 0; $i < count($item_property['prop_details']['share_user']); $i++) {
                            if ($item_property['prop_details']['share_user'][$i] != $_SESSION['aib']['user_data']['user_login']) {
                                unset($apiResponse['info']['records'][$key]);
                            }
                        }
                }
            }
        }
        if ($itemData['item_type'] == 'SG' && $treeDataArray[count($treeDataArray) - 2]['item_title'] == 'Scrapbooks') {
            $count = 0;
            // Used foreach loop over api response & get item details of each array element 
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                // Request array to get item details with all property & with all file                
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
                // Get item details with all property  
                //$item_property = getItemDetailsWithProp($dataArray['item_id']);
                $item_property['prop_details'] = $dataArray['properties'];
                // Service request to get item details with all property & with all file
                $apiResponseItem = aibServiceRequest($postData, 'browse');
                // Used foreach loop over api response & check api response record  property count equal to each data array property_counts conditions
                foreach ($apiResponseItem['info']['records'] as $key => $dataItemArray) {
                    $apiResponseItem['info']['records'][$key]['property_counts'] = $dataArray['property_counts'];
                    // Get item tree data of ftree item    
                    $recordsItemParent = getTreeData($dataItemArray['item_id']);
                    if ($dataItemArray['item_type'] == 'IT') {
                        $apiResponseItem['info']['records'][$key]['item_parent_id'] = $recordsItemParent[count($recordsItemParent) - 2]['item_id']; //$item_property['prop_details']['item_parent'];
                    }
                    $apiResponseItem['info']['records'][$key]['item_parent_refrence_id'] = $dataArray['item_id'];
                    $apiResponseItem['info']['records'][$key]['scrapbook_item'] = 'Y';
                    if (trim($dataArray['link_title']) and $dataArray['link_title'] != '') {
                        $apiResponseItem['info']['records'][$key]['scrapbook_title'] = $dataArray['link_title'];
                    } else {
                        $apiResponseItem['info']['records'][$key]['scrapbook_title'] = $dataArray['item_title'];
                    }
                    $apiResponseItem['info']['records'][$key]['final_deref_stp_thumb'] = $dataArray['final_deref_stp_thumb'];
                    $apiResponseItem['info']['records'][$key]['final_deref_stp_url'] = $dataArray['final_deref_stp_url'];

                    $ThumbID = false;
                    $PrimaryID = false;
                    // Used foreach loop over $dataItemArray["files"] & check each arrary element tn_file_id = file_id                     
                    foreach ($dataItemArray["files"] as $FileRecord) {
                        if ($FileRecord["file_type"] == 'tn') {
                            $ThumbID = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['tn_file_id'] = $FileRecord["file_id"];
                            continue;
                        }
                        if ($FileRecord["file_type"] == 'pr') {
                            $PrimaryID = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['pr_file_id'] = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                            continue;
                        }
                        if ($FileRecord["file_type"] == 'pr') {
                            $PrimaryID = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                            continue;
                        }
                    }
                    $apiResponse['info']['records'][$count] = $apiResponseItem['info']['records'][$key];
                    if ($recordsItemParent[count($recordsItemParent) - 2]['properties']['aib:visible'] == 'N') {
                        // Get item details with all property of ftree item                        
                        $scrapbookDetails = getItemDetailsWithProp($folderId);
                        if ($scrapbookDetails['prop_details']['scrapbook_type'] == 'public') {
                            $sheare_user = json_decode($scrapbookDetails['prop_details']['share_user']);
                            if (!empty($sheare_user) && !empty($_SESSION['aib']['user_data']['user_login'])) {
                                if (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) {
                                    unset($apiResponse['info']['records'][$count]);
                                }
                            } else {
                                unset($apiResponse['info']['records'][$count]);
                            }
                        }
                    }
                }
                $count++;
            }
        }

        if ($itemData['item_type'] == 'RE') {
            // Used foreach loop over api response to check file type tn & pr for each array element            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $ThumbID = false;
                $PrimaryID = false;
                foreach ($dataArray["final_deref_files"] as $FileRecord) {
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
        // Apply public user filter on api response         
        $apiResponse = applyPublicUserFilter($apiResponse, $filter);
        // Used foreach loop over api response & check conditions        
        foreach ($apiResponse['info']['records'] as $itemKey => $itemDataArray) {
            // Check conditions aib:private == 'Y' & aib:visible == 'N' & unset $apiResponse['info']['records'][$itemKey]              
            if ($itemDataArray['properties']['aib:private'] == 'Y' || $itemDataArray['properties']['aib:visible'] == 'N') {
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            // Check item type SG            
            if ($itemDataArray['item_type'] == 'SG') {
                $apiResponse['info']['records'][$itemKey]['child_count'] = ($itemDataArray['property_counts'][1]['count'] - $itemDataArray['property_counts'][6]['count']) . ' Rec(s)';
            }
            // Check item type SG
            if ($itemDataArray['item_type'] == 'RE') {
                $apiResponse['info']['records'][$itemKey]['child_count'] = ($itemDataArray['property_counts'][4]['count'] - ($itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][7]['count'])) . ' Item(s)';
            }
        }
        $firstTimeDataArray = [];
        $recordIdsArray = [];
        $indexKey = 0;
        // Check parent id is equal to root id        
        if ($currentParentId == PUBLIC_USER_ROOT) {//6499
            // Used foreach loop over api response to check conditions             
            foreach ($apiResponse['info']['records'] as $recordKey => $recordDataArray) {
                // Check to set link_id & unset $apiResponse['info']['records'][$recordKey]                 
                if (isset($recordDataArray['link_id'])) {
                    unset($apiResponse['info']['records'][$recordKey]);
                    continue;
                }
                // Check item type SG                
                if ($recordDataArray['item_type'] == 'SG') {
                    $firstTimeDataArray['sub_groups'][] = $recordDataArray;
                }
                // Check item type RE 
                if ($recordDataArray['item_type'] == 'RE') {
                    $firstTimeDataArray['records'][] = $recordDataArray;
                }
                // Check item title Scrapbooks
                if ($recordDataArray['item_title'] == 'Scrapbooks') {
                    // Get archive owner details of ftree archive                    
                    $itemOwner = getArchiveOwner($folderId);
                    $itemUserId = $itemOwner['user_id'];
                    // Request array to get item list of ftree parent item                    
                    $scrapbookPostData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "list",
                        "parent" => $recordDataArray['item_id'],
                        "opt_get_files" => 'Y',
                        "opt_deref_links" => 'Y',
                        "opt_get_property" => 'Y',
                        "opt_get_long_prop" => 'Y',
                        "opt_get_prop_count" => 'Y',
                        "opt_get_link_source_properties" => 'Y',
                        "opt_prop_count_set" => json_encode($search_filter),
                        "opt_follow_links" => 'Y'
                    );
                    // Service request to get item list of ftree parent item
                    $apiResponseScrapbook = aibServiceRequest($scrapbookPostData, 'browse');
                    // Used foreach loop over api response & check condions                    
                    foreach ($apiResponseScrapbook['info']['records'] as $key => $dataArray) {
                        // Check set & not empty link title                         
                        if (isset($dataArray['link_title']) && $dataArray['link_title'] != '') {
                            $apiResponseScrapbook['info']['records'][$key]['item_title'] = $dataArray['link_title'];
                        }
                        if (isset($_SESSION['aib']['user_data']['user_id']) && $_SESSION['aib']['user_data']['user_id'] != $itemUserId && $_SESSION['aib']['user_data']['user_type'] == 'U' && $dataArray['is_link'] == 'N') {
                            $apiResponseScrapbook['info']['records'][$key]['show_copy_link'] = 'yes';
                            $apiResponseScrapbook['info']['records'][$key]['item_user_id'] = $itemUserId;
                        }
                        $apiResponseScrapbook['info']['records'][$key]['child_count'] = ($dataArray['property_counts'][0]['count'] - $dataArray['property_counts'][6]['count']) . ' Item(s)';
                        // Get item details with all item property     
                        //$item_property = getItemDetailsWithProp($dataArray['item_id']);  
                        $item_property['prop_details'] = $dataArray['properties'];
                        if ($item_property['prop_details']['scrapbook_type'] != 'public' and $_SESSION['aib']['user_data']['user_top_folder'] != $_REQUEST['folder_id']) {
                            $sheare_user = json_decode($item_property['prop_details']['share_user']);
                            if (!empty($sheare_user) && !empty($_SESSION['aib']['user_data']['user_login'])) {
                                // Check user login exist in $share_user or not    
                                if (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) {
                                    unset($apiResponseScrapbook['info']['records'][$key]);
                                }
                            } else {
                                unset($apiResponseScrapbook['info']['records'][$key]);
                            }
                        }
                    }
                    $firstTimeDataArray['scrapbook'] = $apiResponseScrapbook['info']['records'];
                }
                if ($recordDataArray['item_type'] == 'RE') {
                    $recordIdsArray[$indexKey] = $recordDataArray['item_id'];
                    $indexKey++;
                }
            }
        }
        foreach ($apiResponse['info']['records'] as $recordKey => $recordId) {
            if ($recordId['item_type'] == 'RE') {
                $recordIdsArray[$indexKey] = $recordId['item_id'];
                $indexKey++;
            }
        }
        $_SESSION['pepole_record_ids'] = $recordIdsArray;
        $ebayCheckCondition = $itemData['properties']['ebay_status'];
        if (isset($_POST['start']) && $_POST['start'] != '') {
            if ($_POST['start'] == 0 || $_POST['start'] == '') {
                $stratPage = PUBLIC_COUNT_PER_PAGE;
            } else {
                $stratPage = $_POST['start'] + PUBLIC_COUNT_PER_PAGE;
            }
        }
        if (end($treeDataArray)['item_type'] == 'AR') {
            if (isset($_SESSION['public_data_detail_page'])) {
                unset($_SESSION['public_data_detail_page']);
            }
            $subGroup = $firstTimeDataArray['sub_groups'];
            $scrapBook = $firstTimeDataArray['scrapbook'];
            unset($firstTimeDataArray['sub_groups']);
            unset($firstTimeDataArray['scrapbook']);
            $peopleRecordCount = count($firstTimeDataArray['records']);
            $_SESSION['public_record_data_detail_page'] = $firstTimeDataArray;
            $firstTimeDataArray = apiFilterRecordPaginationData($firstTimeDataArray, $stratPage, PUBLIC_COUNT_PER_PAGE);
            $firstTimeDataArray['sub_groups'] = $subGroup;
            $firstTimeDataArray['scrapbook'] = $scrapBook;
        } else {
            $countArchive = count($apiResponse['info']['records']);
            $_SESSION['public_data_detail_page'] = $apiResponse;
            $apiResponse = apiFilterArchivePaginationData($apiResponse, $stratPage, PUBLIC_COUNT_PER_PAGE);
        }

        if ($apiResponse['status'] == 'OK' && $itemData['item_type'] != 'RE') {
            // people-content.php file has been included here to show sub-group & records of public user            
            include_once TEMPLATE_PATH . 'people-content.php';
        } else {
            $detailsPageData = array();
            // details-description.php file has been included here to show item details            
            include_once TEMPLATE_PATH . 'details-description.php';
        }
        break;
    case 'public_list_tree_items_with_pagination' :
        if (isset($_POST['start'])) {
            if ($_POST['start'] == 0) {
                $start_result = PUBLIC_COUNT_PER_PAGE;
            } else {
                $start_result = $_POST['start'] + PUBLIC_COUNT_PER_PAGE;
            }
        }
        if ($_POST['group_type'] == 'IT' || $_POST['people_sub_group_type'] == 'SG') {
            $apiResponse = apiFilterArchivePaginationData($_SESSION['public_data_detail_page'], $start_result, PUBLIC_COUNT_PER_PAGE);
        } else {
            $apiResponse['info'] = apiFilterRecordPaginationData($_SESSION['public_record_data_detail_page'], $start_result, PUBLIC_COUNT_PER_PAGE);
        }

        include_once TEMPLATE_PATH . 'people-more-load-content.php';
        break;
    case 'register_new_user':
        // Parse $_POST['formData'] into $postDataArray         
        parse_str($_POST['formData'], $postDataArray);

        $email_data = [];
        // Get registration.html file html content in a variable $email_template       
        $email_template = file_get_contents(EMAIL_PATH . "/registration.html");
        $email_data['to'] = $postDataArray['register_emailId'];
        $email_data['from'] = ADMIN_EMAIL;
        $email_data['reply'] = ADMIN_EMAIL;
        $email_data['subject'] = 'ArchiveInABox: User Registration';
        $email_template = str_replace('#username#', $postDataArray['register_username'], $email_template);
        $fieldsArray = array("phoneNumber", "redactionsEmailAddress", "reprintEmailAddress", "contactEmailAddress",
            "websiteURL", "physicalAddressLine1", "mailingAddressLine1", "physicalAddressLine2", "mailingAddressLine2", "physicalCity", "mailingCity",
            "physicalState", "mailingState", "physicalZip", "mailingZip", "federalTaxIDNumber", "sateTaxIDNumber", "entityOrganization", "entityOrganizationOther",
            "CEO", "CEO_firstName", "CEO_lastName", "CEO_email", "executiveDirector", "executiveDirector_firstName", "executiveDirector_lastName", "executiveDirector_email",
            "precident", "precident_firstName", "precident_lastName", "precident_email", "otherExecutive", "otherExecutive_firstName", "otherExecutive_lastName",
            "otherExecutive_email", "sameAsPhysicalAddress", "boardOfDirectors", "committees", "society_state", "preferred_time_zone");
        $postRegArray = array();
        $count = 3;
        // Used foreach loop over $fieldsArray & set propname & propval        
        if (empty($postDataArray['archive_id'])) {
            foreach ($fieldsArray as $fields) {
                $postRegArray['propname_' . $count] = $fields;
                $postRegArray['propval_' . $count] = (string) $postDataArray[$fields];
                $count++;
            }
        }

        $postDataArray['name'] = $postDataArray['title'] . " " . $postDataArray['firstName'] . " " . $postDataArray['lastName'];
        $userTypeArray = array('society' => 'A', 'municipality' => 'A', 'publisher' => 'A', 'user' => 'X');
        $responseData = array('status' => 'error', 'message' => 'All fields are required.');
        // Request array to get user profile     
        $checkUserAlready = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_profile',
            '_session' => $sessionKey,
            'user_login' => $postDataArray['register_username']
        );

        // Service request to get user profile  
        $confirmUserReg = aibServiceRequest($checkUserAlready, 'users');
        if ($confirmUserReg['status'] != 'OK') {
            if (!empty($postDataArray)) {
                if ($postDataArray['user_type'] == 'A') {//society
                    // Request array to create item for society ( Archive )    
                    if (!$postDataArray['archive_id'] || empty($postDataArray['archive_id'])) {
                        $postDataItem = array(
                            '_key' => APIKEY,
                            '_user' => 1,
                            '_op' => 'create_item',
                            '_session' => $sessionKey,
                            'parent' => 1,
                            'item_title' => $postDataArray['society_name'],
                            'item_class' => 'ag',
                            'item_owner_id' => 1,
                            'item_owner_group' => 1,
                            'opt_allow_dup' => 'N',
                        );
                        // Service request to create item for society ( Archive )                                         
                        $apiResponseAG = aibServiceRequest($postDataItem, 'browse');
                    } else {
                        $apiResponseAG['status'] = 'OK';
                        $apiResponseAG['info'] = $postDataArray['archive_id'];

                        /* $uRequestApiData['_key'] = APIKEY;
                          $uRequestApiData['_session'] = $sessionKey;
                          $uRequestApiData['_user'] = 1;
                          $uRequestApiData['_op'] = 'modify_item';
                          $uRequestApiData['obj_id'] = $postDataArray['archive_id'];
                          $uRequestApiData['item_title'] = $postDataArray['society_name'];
                          $uFile_name = 'browse';
                          $UapiResponse = aibServiceRequest($uRequestApiData, $uFile_name); */
                    }
                    if ($apiResponseAG['status'] == 'OK') {
                        $newArchiveId = $apiResponseAG['info'];
                        // Request array to create user profile (Admin)                                               
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
                        // Service request to create user profile (Admin)                                            
                        $apiResponse = aibServiceRequest($apiRequestData, 'users');

                        $url = 'id=' . $apiResponse['info'] . '&flg=new_registration';
                        $userProperty[0]['name'] = 'type';
                        $userProperty[0]['value'] = 'primary';
                        $userProperty[1]['name'] = 'email';
                        $userProperty[1]['value'] = $postDataArray['register_emailId'];
                        $userProperty[2]['name'] = 'timestamp';
                        $userProperty[2]['value'] = $postDataArray['timestamp'];
                        if (isset($postDataArray['term_service']) && !empty($postDataArray['term_service'])) {
                            array_push($userProperty, ['name' => 'term_service', 'value' => $postDataArray['term_service']]);
                            if (isset($postDataArray['occasional_update']) && $postDataArray['occasional_update'] == 'Y') {
                                array_push($userProperty, ['name' => 'occasional_update', 'value' => $postDataArray['occasional_update']]);
                            }
                        }
                        if ($postDataArray['request_type'] == 'CLAIM') {
                            array_push($userProperty, ['name' => 'claimed_user', 'value' => '1']);
                            array_push($userProperty, ['name' => 'unclaimed_society_id', 'value' => $postDataArray['archive_id']]);
                            array_push($userProperty, ['name' => 'claimed_user_approved', 'value' => 'N']);
                            array_push($userProperty, ['name' => 'phone_no', 'value' => $postDataArray['phoneNumber']]);
                        }
                        if (isset($postDataArray['unclaimed_society'])) {
                            array_push($userProperty, ['name' => 'claimed_user2', 'value' => '1']);
                        }
                        //echo '<pre>';print_r($userProperty);exit;
                        // Request array to set user property                                                 
                        $postUserPropData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "set_profile_prop_batch",
                            "user_id" => $apiResponse['info'],
                            "property_list" => json_encode($userProperty)
                        );
                        // Service request to set user property                                           
                        $apiResponseUserProp = aibServiceRequest($postUserPropData, 'users');
                        // Request array to set item property
                        $apiRequestDataItem = array(
                            '_key' => APIKEY,
                            '_user' => 1,
                            '_op' => 'set_item_prop',
                            '_session' => $sessionKey,
                            'obj_id' => $newArchiveId,
                            'propname_1' => 'status',
                            'propval_1' => ($postDataArray['archive_id']) ? 1 : 0,
                                //'propname_2' => 'archive_user_id',
                                //'propval_2' => $apiResponse['info']
                        );

                        if ($postDataArray['request_type'] != 'CLAIM') {
                            $apiRequestDataItem['propname_2'] = 'archive_user_id';
                            $apiRequestDataItem['propval_2'] = $apiResponse['info'];
                        }

                        $apiRequestDataItem = array_merge($apiRequestDataItem, $postRegArray);
                        if (isset($postDataArray['unclaimed_society'])) {
                            $apiRequestDataItem['propname_' . $count] = 'society_for_claim';
                            $apiRequestDataItem['propval_' . $count] = '1';
                            $count++;
                            $apiRequestDataItem['propname_' . $count] = 'temp_user_created_by_admin';
                            $apiRequestDataItem['propval_' . $count] = $apiResponse['info'];
                            $count++;
                            $apiRequestDataItem['propname_' . $count] = 'temp_society_created_by_admin';
                            $apiRequestDataItem['propval_' . $count] = $postDataArray['society_name'];
                        }

                        $add_url = '';
                        if ($postDataArray['request_type'] == 'CLAIM') {
                            $apiRequestDataItem['propname_' . $count] = 'society_for_claim';
                            $apiRequestDataItem['propval_' . $count] = '0';

                            $apiRequestDataItemLong = array(
                                '_key' => APIKEY,
                                '_user' => 1,
                                '_op' => 'set_item_prop',
                                '_session' => $sessionKey,
                                'obj_id' => $newArchiveId,
                                'opt_long' => 'Y',
                                'propname_1' => 'temp_claim_data_' . $postDataArray['register_username'],
                                'propval_1' => $postDataArray['temp_claim_data'],
                                'propname_2' => 'society_state',
                                'propval_2' => $postDataArray['society_state'],
                            );
                            aibServiceRequest($apiRequestDataItemLong, 'browse');

                            $add_url = '&claim_verification=' . $postDataArray['archive_id'];
                        }
                        // Service request to set item property
                        $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');

                        /* SETTING ALL THE SAME PROPERTY AS LOGN PROPERTY */
                        //$apiRequestDataItem['opt_long'] = 'Y'
                        //$apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
                        /* END */

                        $link = '<a href="' . HOST_PATH . 'thank-you.html?' . urlencode($url . $add_url) . '" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>';

                        /* if ($postDataArray['request_type'] == 'CLAIM') {
                          $link = '<a href="' . HOST_PATH . 'thankyou.html?' . urlencode($url . $add_url) . '" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>';
                          } */

                        $email_template = str_replace('#confirm_email#', $link, $email_template);
                        if ($apiResponse['status'] == 'OK') {
                            // Send email to user regarding to user registration                                                 
                            $email = sendMail($email_data, $email_template);
                            if ($email) {
                                $responseData = array('status' => 'success', 'message' => 'Your profile created successfully, Please login here.', 'archive_id' => $newArchiveId);
                            } else {
                                $responseData = array('status' => 'error', 'message' => 'Something went wrong, Please enter correct email id.');
                            }
                        } else {
                            $responseData = array('status' => 'error', 'message' => 'Something went wrong on API side, Please try again.');
                        }
                    }
                }
            }
        } else {
            $responseData = array('status' => 'error', 'message' => 'User Name is Already Registered !');
        }
        unset($_SESSION['data1']);
        unset($_SESSION['data2']);
        print json_encode($responseData);
        break;
    case 'register_normal_user':
        // Parse $_POST['formData'] in to $postDataArray variable         
        parse_str($_POST['formData'], $postDataArray);
        $responseData = array('status' => 'error', 'message' => 'You are Robot!');
        // Get timestamp ( Current date & time)         
        $current_time = time();
        $time_diff = $current_time - $postDataArray['timestamp_value'];
        if ($time_diff > TIMESTAMP_5) {
            $email_data = [];
            // Get registration.html file html content in a variable $email_template
            $email_template = file_get_contents(EMAIL_PATH . "/registration.html");
            if (!empty($postDataArray)) {
                // Request array to create user profile             
                $apiRequestData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => "create_profile",
                    "_user" => 1,
                    "user_login" => $postDataArray['register_username'],
                    "user_type" => 'U',
                    "user_pass" => '',
                    "user_title" => $postDataArray['register_username'],
                    "user_primary_group" => "-1",
                    "opt_create_home" => "Y",
                    "opt_home_type" => 'ar'
                );
                // Service request to create user profile 
                $apiResponse = aibServiceRequest($apiRequestData, 'users');
                if ($apiResponse['status'] == 'OK') {
                    $userProperty[0]['name'] = 'package';
                    $userProperty[0]['value'] = 'basic';
                    $userProperty[1]['name'] = 'email';
                    $userProperty[1]['value'] = $postDataArray['register_email'];
                    $userProperty[2]['name'] = 'status';
                    $userProperty[2]['value'] = 'd';
                    $userProperty[3]['name'] = 'timestamp';
                    $userProperty[3]['value'] = $postDataArray['timestamp_value'];

                    if (isset($postDataArray['term_service']) && !empty($postDataArray['term_service'])) {
                        array_push($userProperty, ['name' => 'term_service', 'value' => $postDataArray['term_service']]);
                        if (isset($postDataArray['occasional_update']) && $postDataArray['occasional_update'] == 'Y') {
                            array_push($userProperty, ['name' => 'occasional_update', 'value' => $postDataArray['occasional_update']]);
                        }
                    }

                    if ($postDataArray['request_type'] == 'CLAIM') {
                        array_push($userProperty, ['name' => 'claimed_user', 'value' => '1']);
                        array_push($userProperty, ['name' => 'unclaimed_society_id', 'value' => $postDataArray['archive_id']]);
                        array_push($userProperty, ['name' => 'claimed_user_approved', 'value' => 'N']);
                    }
                    // Request array to set user profile property             
                    $postRequestData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "set_profile_prop_batch",
                        "user_id" => $apiResponse['info'],
                        "property_list" => json_encode($userProperty)
                    );
                    // Service request to set user profile property
                    $userPropertyStatus = aibServiceRequest($postRequestData, "users");
                    $email_data['to'] = $postDataArray['register_email'];
                    $email_data['from'] = ADMIN_EMAIL;
                    $email_data['reply'] = ADMIN_EMAIL;
                    $email_data['subject'] = 'ArchiveInABox: User Registration';
                    $email_template = str_replace('#username#', $postDataArray['register_username'], $email_template);

                    $url = 'id=' . $apiResponse['info'] . '&type=U';
                    $link = '<a href="' . HOST_PATH . 'thank-you.html?' . urlencode($url) . '" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>';
                    $email_template = str_replace('#confirm_email#', $link, $email_template);
                    // Send registration email to user                
                    $email = sendMail($email_data, $email_template);
                    if ($email) {
                        $responseData = array('status' => 'success', 'message' => 'Your profile created successfully');
                    }
                } else {
                    $responseData = array('status' => 'error', 'message' => $message[$apiResponse['info']]);
                }
            }
        }
        print json_encode($responseData);
        break;
    case 'logout_user':
        unset($_SESSION);
        // All set session distroy         
        session_destroy();
        break;
    case 'forget_password_email':
        // Parse $_POST['formData'] in to $postData variable            
        parse_str($_POST['formData'], $postData);
        $responseData = array('status' => 'error', 'message' => 'Some things went wrong! Please try again.');
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile        
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_profile',
            '_session' => $sessionKey,
            'user_login' => $postData['forget_user_id']
        );
        // Service request to get user profile
        $apiResponse = aibServiceRequest($apiRequestData, 'users');
        $emailid = '';
        if ($apiResponse['status'] == 'OK') {
            $userEmail = getUsersAllProperty($apiResponse['info']['user_id']);
            $email_data = [];
            // Get forget_password.html file html content in a variable $email_template
            $email_template = file_get_contents(EMAIL_PATH . "/forget_password.html");
            $email_data['to'] = $userEmail['email'];
            $email_data['from'] = ADMIN_EMAIL;
            $email_data['reply'] = ADMIN_EMAIL;
            $email_data['subject'] = 'ArchiveInABox: Change Password';
            $email_template = str_replace('#username#', $postData['forget_user_id'], $email_template);
            $url = 'id=' . $apiResponse['info']['user_id'] . '&type=forget';
            $link = '<a href="' . HOST_PATH . 'thank-you.html?' . urlencode($url) . '" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to Reset Your Password</a>';
            $email_template = str_replace('#confirm_email#', $link, $email_template);
            // Send email to user regarding to Change password                        
            $email = sendMail($email_data, $email_template);
            if ($email) {
                $responseData = array('status' => 'success', 'message' => 'Please check your registered email  to change your password');
            }
        } else {
            $responseData = array('status' => 'error', 'message' => 'Username is not found !');
        }
        print json_encode($responseData);
        break;
    case 'get_tree_data':
        $folderId = $_POST['folder_id'];
        // Get ftree data         
        $treeDataArray = getTreeData($folderId);
        if (!empty($treeDataArray)) {
            // tree.php file has been included here to apply filter             
            include_once TEMPLATE_PATH . 'tree.php';
        }
        break;
    case 'set_pagenumber_data':
        $folderid = $_POST['folder_id'];
        $_SESSION['pagination']["page_number_id_" . $folderid] = $_POST['current_page_num'];
        break;
    case 'get_settable_pagenumber':
        $folderid = $_POST['folder_id'];
        $set = 1;
        $pagereturnId = $_SESSION['pagination']["page_number_id_" . $folderid];
        // Used foreach loop over $_SESSION['pagination'] & unset $_SESSION['pagination']['page_number_id_'.$key] if $set != 1                  
        foreach ($_SESSION['pagination'] as $key => $value) {
            if ($set != 1) {
                unset($_SESSION['pagination']['page_number_id_' . $key]);
            }
            if ($key == 'page_number_id_' . $folderid && $value = $pagereturnId) {
                $set = 2;
            }
        }
        print $pagereturnId;
        break;
    case 'get_public_tree_data':
        $folderId = $_POST['folder_id'];
        // Get item tree data of ftree item         
        $treeDataArray = getTreeData($folderId);
        if (!empty($treeDataArray)) {
            // public_tree.php file has been included here to apply filter             
            include_once TEMPLATE_PATH . 'public_tree.php';
        }
        break;
    case 'get_term_and_condition':
        $responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again');
        // Get item details with all property                 
        $apiResponse = getItemDetailsWithProp($_POST['user_id']);
        $type = (isset($_POST['type']) && !empty($_POST['type'])) ? $_POST['type'] : '';
        if ($apiResponse['prop_details']) {
            if ($type == 'PC') {
                $responseData = array('status' => 'success', 'message' => stripslashes($apiResponse['prop_details']['privacy_and_cookies']));
            } else if ($type == 'DMCA') {
                $responseData = array('status' => 'success', 'message' => stripslashes($apiResponse['prop_details']['DMCA_value']));
            } else if ($type == 'DCN') {
                $responseData = array('status' => 'success', 'message' => stripslashes($apiResponse['prop_details']['dmca_counter_notice']));
            } else {
                $responseData = array('status' => 'success', 'message' => stripslashes($apiResponse['prop_details']['terms_and_conditions']));
            }
        }
        print json_encode($responseData);
        break;
    case 'get_claimed_popup_message':
        $responseData = array('status' => 'error', 'message' => 'Something went wrong, Please try again');
        // Get item details with all property                 
        $apiResponse = getItemDetailsWithProp($_POST['user_id']);
        $type = (isset($_POST['type']) && !empty($_POST['type'])) ? $_POST['type'] : '';
        if ($apiResponse['prop_details']) {
            $responseData = array('status' => 'success', 'message' => stripslashes($apiResponse['prop_details']['claimed_message']));
        }
        print json_encode($responseData);
        break;
    case 'get_archive_prop_details':
        $archive_id = $_POST['archive_id'];
        // Get item data of ftree item                
        $itemDetails = getItemData($archive_id);
        $itemDetails['properties'] = array_map('stripslashes', $itemDetails['properties']);
        $itemDetails['prop_details'] = array_map('stripslashes', $itemDetails['properties']);
        $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $itemDetails['prop_details']['archive_logo_image'];
        $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $itemDetails['prop_details']['archive_header_image'];
        $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $itemDetails['prop_details']['archive_details_image'];
        $_SESSION['archive_request_reprint_text'] = $itemDetails['prop_details']['archive_request_reprint_text'];
        if (isset($itemDetails['item_title']) && $itemDetails['item_title'] != '') {
            $_SESSION['archive_item_title'] = $itemDetails['item_title'];
        }
        print json_encode($itemDetails);
        break;
    case 'get_society_details':
        $folder_id = $_POST['folder_id'];
        $responseArray = [];
        if ($folder_id) {
            // Get item tree data of ftree item            
            $parentDetails = getTreeData($folder_id);
            if (isset($parentDetails[1])) {
                $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $parentDetails[1]['properties']['archive_logo_image'];
                $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $parentDetails[1]['properties']['archive_header_image'];
                $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $parentDetails[1]['properties']['archive_details_image'];
                $responseArray = ['logo' => $parentDetails[1]['properties']['archive_logo_image'], 'banner' => $parentDetails[1]['properties']['archive_header_image']];
            }
        }
        print json_encode($responseArray);
        break;
    case 'get_advertisement':
        $folder_id = $_POST['folder_id'];
        $rootId = $_POST['rootId'];
        // Get item details of a ftree item by item id       
        $itemDetail = getItemData($folder_id);
        if ($itemDetail['item_type'] == 'RE') {
            // Get item tree data of ftree item            
            $itemDetaildata = getTreeData($folder_id);
            $folder_id = $itemDetaildata[count($itemDetaildata) - 2]['item_id'];
        }
        // Get archive's advertisment         
        getAdvertisementHetarchive($folder_id, $rootId);
        break;
    case 'User_confirm_password_Change':
        // Parse $_POST['formData'] in to $postData variable          
        parse_str($_POST['formData'], $postData);
        // Request array to update user profile        
        $postDataLogin = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'update_profile',
            "_user" => 1,
            "user_id" => $postData['user_login_id'],
            "new_user_password" => $postData['user_password']
        );
        // Service request to update user profile 
        $apiResponse = aibServiceRequest($postDataLogin, 'users');
        if ($apiResponse['status'] == 'OK') {
            $userProperty[0]['name'] = 'email_verify';
            $userProperty[0]['value'] = 'yes';
            if (isset($postData['term_service']) && !empty($postData['term_service'])) {
                $userProperty[1]['name'] = 'term_service';
                $userProperty[1]['value'] = $postData['term_service'];
                if (isset($postData['occasional_update']) && $postData['occasional_update'] == 'Y') {
                    $userProperty[2]['name'] = 'occasional_update';
                    $userProperty[2]['value'] = $postData['occasional_update'];
                }
            }
            // Request array to set user profile property            
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "set_profile_prop_batch",
                "user_id" => $apiResponse['info'],
                "property_list" => json_encode($userProperty)
            );
            // Service request to set user profile property
            $userPropertyStatus = aibServiceRequest($postRequestData, "users");
            $response['respnose'] = '';
            if ($userPropertyStatus['status'] == 'OK') {
                $response['respnose'] = 'success';
            }
        }
        print(json_encode($response));
        break;
    case 'get_item_complete_details':
        $item_id = $_POST['item_id'];
        // Get item tree data of ftree item        
        $itemParents = getTreeData($item_id);
        $themeName = isset($itemParents[1]['properties']['details_page_design']) ? $itemParents[1]['properties']['details_page_design'] : '';
        $item_top_parents = $itemParents[1]['item_id'];
        $displayEditOption = '';
        if ($_SESSION['aib']['user_data']['user_type'] == 'R') {
            $displayEditOption = 'yes';
        } elseif ($_SESSION['aib']['user_data']['user_type'] == 'S') {
            // Get records list which is assigned to assistant user            
            $assignedRecords = getAssistantAssignedRecords($_SESSION['aib']['user_data']['user_id']);
            $recordId = $itemParents[count($itemParents) - 2]['item_id'];
            // Check $recordId exist in $assignedRecords             
            if (in_array($recordId, $assignedRecords)) {
                $displayEditOption = 'yes';
            }
        } elseif ($_SESSION['aib']['user_data']['user_top_folder'] == $item_top_parents) {
            $displayEditOption = 'yes';
        }
        $ref_id = $_POST['ref_id'];
        if ($item_id) {
            // Request array to get tags            
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "tags_get",
                "obj_id" => $item_id
            );
            // Service request to get tags
            $apiTagResponse = aibServiceRequest($postData, 'tags');
            // Request array to get item details of ftree item            
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get",
                "obj_id" => $item_id,
                "opt_get_files" => 'Y',
                "opt_get_field" => 'Y'
            );
            // Service request to get item details of ftree item 
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK' || $apiTagResponse['status'] == 'OK') {
                // Request array to get item details of ftree item   
                $postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => 1,
                    "_op" => "get",
                    "obj_id" => $ref_id,
                    "opt_get_files" => 'Y',
                    "opt_get_field" => 'Y'
                );
                // Service request to get item details of ftree item    
                $apiResponseRef = aibServiceRequest($postData, 'browse');
                // item_description.php file has been inluded here to show item details with tag description             
                include_once TEMPLATE_PATH . 'item_description.php';
            }
        }
        break;

    case 'get_item_complete_details2':
        $item_id = $_POST['item_id'];
        // Get item tree data of ftree item        
        $itemParents = getTreeData($item_id);
        $themeName = isset($itemParents[1]['properties']['details_page_design']) ? $itemParents[1]['properties']['details_page_design'] : '';
        $item_top_parents = $itemParents[1]['item_id'];
        $displayEditOption = '';
        if ($_SESSION['aib']['user_data']['user_type'] == 'R') {
            $displayEditOption = 'yes';
        } elseif ($_SESSION['aib']['user_data']['user_type'] == 'S') {
            // Get records list which is assigned to assistant user            
            $assignedRecords = getAssistantAssignedRecords($_SESSION['aib']['user_data']['user_id']);
            $recordId = $itemParents[count($itemParents) - 2]['item_id'];
            // Check $recordId exist in $assignedRecords             
            if (in_array($recordId, $assignedRecords)) {
                $displayEditOption = 'yes';
            }
        } elseif ($_SESSION['aib']['user_data']['user_top_folder'] == $item_top_parents) {
            $displayEditOption = 'yes';
        }
        $ref_id = $_POST['ref_id'];
        if ($item_id) {
            // Request array to get tags            
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "tags_get",
                "obj_id" => $item_id
            );
            // Service request to get tags
            $apiTagResponse = aibServiceRequest($postData, 'tags');
            // Request array to get item details of ftree item            
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get",
                "obj_id" => $item_id,
                "opt_get_files" => 'Y',
                "opt_get_field" => 'Y'
            );
            // Service request to get item details of ftree item 
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK' || $apiTagResponse['status'] == 'OK') {
                // Request array to get item details of ftree item   
                $postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => 1,
                    "_op" => "get",
                    "obj_id" => $ref_id,
                    "opt_get_files" => 'Y',
                    "opt_get_field" => 'Y'
                );
                // Service request to get item details of ftree item    
                $apiResponseRef = aibServiceRequest($postData, 'browse');
                // item_description.php file has been inluded here to show item details with tag description             
                include_once TEMPLATE_PATH . 'item_description2.php';
            }
        }
        break;

    case 'submit_request':
        // Parse $_POST['formData'] in to $postData variable          
        parse_str($_POST['formData'], $postData);
        //echo '<pre>';print_r($postData);exit;
        $responseArray = array('status' => 'error', 'message' => 'You are Robot!');
        if ($postData['captcha-response']) {
            $responseArray = _recaptchaSiteVerify($postData['captcha-response']);
            //$responseArray = recaptchaSiteVerify($postData['captcha-response']);
            if ($responseArray['status'] != 'success') {
                print json_encode($responseArray);
                break;
            }
        }
        // Get current date & time (timestamp)        
        $current_time = time();
        $time_diff = $current_time - $postData['timestamp_value'];
        if ($time_diff > TIMESTAMP_1) {
            if (isset($_POST['item_id'])) {
                $item_id = $_POST['item_id'];
            }

            $succes_message = 'Your report has been submitted. Thank you.';
            $email_data = [];
            // Get emailer.html html file contents       
            $email_template = file_get_contents(EMAIL_PATH . "/emailer.html");
            $name_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'first-name.png" alt="" />';
            $email_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'email.png" alt="" />';
            $comment_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'comment.png" alt="" />';
            $link_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'link.png" alt="ArchiveInABox Logo" />';
            $email_template = str_replace('#name_image#', $name_image, $email_template);
            $email_template = str_replace('#last_image#', $name_image, $email_template);
            $email_template = str_replace('#email_image#', $email_image, $email_template);
            $email_template = str_replace('#comment_image#', $comment_image, $email_template);
            $requestData = 'Your request has been submitted with the following details.';
            $requestDataAdmin = 'A contact request has been submitted for the following record :';
            switch ($postData['request_type']) {
                case 'RP':
                    $succes_message = 'Your request has been submitted.Thank you';
                    $email_data['from'] = $postData['cus_email'];
                    $email_data['reply'] = $postData['cus_email'];
                    $email_data['subject'] = 'Reprint Request';
                    $sub = 'Page Link';
                    $pageUsedOptions = [
                        'BUS' => "Business -- Promote your business by reprinting portion of articles",
                        'BUSR' => "Business -- Research content to be used as a component",
                        'MED' => "Media -- Television, radio, print news, public relations",
                        'MEDG' => "Media -- Professional Genealogy Services",
                        'MEDA' => "Media -- Any activity where you receive payment for use of content",
                        'PER' => "Personal -- Single scrapbook, family history document",
                        'PERE' => "Personal -- Educational or hobby use",
                        'OTH' => "Other -- Describe in detail in the 'comments' section below"
                    ];
                    $used_page = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'comment.png" alt="ArchiveInABox Logo" /><strong>Page Used As</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">' . $pageUsedOptions[$postData['cus_page_used']] . '</div>
                <div style="clear:both;"></div>';
                    $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                    $email_template = str_replace('#sub_link_image#', $link_image, $email_template);
                    $email_template = str_replace('#first_name#', $postData['cus_first_name'], $email_template);
                    $email_template = str_replace('#last_name#', $postData['cus_last_name'], $email_template);
                    $email_template = str_replace('#email#', $postData['cus_email'], $email_template);
                    $email_template = str_replace('#phone#', '', $email_template);
                    $email_template = str_replace('#sub_link#', $sub, $email_template);
                    $email_template = str_replace('#sub_link_name#', $postData['cus_page_link'], $email_template);
                    $email_template = str_replace('#used_page#', $used_page, $email_template);
                    $email_template = str_replace('#comment#', $postData['cus_comments'], $email_template);
                    $requestDataAdmin = 'A reprint request has been submitted for the following record :';

                    $name = $postData['cus_first_name'] . ' ' . $postData['cus_last_name'];
                    $email = $postData['cus_email'];
                    $phone = '';
                    $info = array('item_link' => $postData['cus_page_link'], 'comment' => $postData['cus_comments'], 'page_used' => $postData['cus_page_used']);
                    $string = $postData['cus_page_link'];
                    // Convert string to array                
                    $stringArray = explode('?', $string);
                    /* $item_id = $_POST['item_id'];
                      if($item_id == '' ){
                      if(strpos($stringArray[1],'&')){
                      // Convert string to array
                      $subStringArray = explode('&',$stringArray[1]);
                      // Convert string to array
                      $itemArray = explode('=',$subStringArray[0]);
                      $item_id = $itemArray[1];
                      }else{
                      // Convert string to array
                      $subStringArray = explode('=',$stringArray[1]);
                      $item_id = $subStringArray[1];
                      }
                      } */
                    // Convert string to array                
                    $stringArray = explode('?', $string);
                    $encryptedQryStringArr = $stringArray[1];
                    $encryptedQryString = explode('=', $encryptedQryStringArr);
                    if ($encryptedQryString[1])
                        $decrypted_string = decryptQueryString($encryptedQryString[1]);

                    if (isset($decrypted_string['folder_id']) and $decrypted_string['folder_id'] != '') {
                        $item_id = $decrypted_string['folder_id'];
                    }

                    // Get item tree data of ftree item				
                    $item_par_id = getTreeData($item_id);
                    // Get archive admin email id's           
                    if ($item_par_id[1]['item_id'] != '') {
                        $AdministratorEmailId = getArchiveAdministratorEmails($item_par_id[1]['item_id']);
                        //$item_id=$item_par_id[1]['item_id'];
                    }


                    if ($AdministratorEmailId != '') {
                        $emailaddress['societyEmail'] = $AdministratorEmailId;
                    }
                    if ($AdministratorEmailId != '') {
                        $emailaddress['societyEmail'] = $AdministratorEmailId;
                    }
                    $emailaddress['userEmail'] = $postData['cus_email'];
                    break;
                case 'RD':
                    $email_data['from'] = $postData['email'];
                    $email_data['reply'] = $postData['email'];
                    $email_data['subject'] = 'Content Removal Request';
                    $phone = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">' . $postData['phone_number'] . '</div><div style="clear:both;"></div>';
                    $sub = 'Article Link';
                    $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                    $email_template = str_replace('#phone_image#', $phone_image, $email_template);
                    $email_template = str_replace('#sub_link_image#', $link_image, $email_template);
                    $email_template = str_replace('#first_name#', $postData['first_name'], $email_template);
                    $email_template = str_replace('#last_name#', $postData['last_name'], $email_template);
                    $email_template = str_replace('#email#', $postData['email'], $email_template);
                    $email_template = str_replace('#phone#', $phone, $email_template);
                    $email_template = str_replace('#sub_link#', $sub, $email_template);
                    $email_template = str_replace('#sub_link_name#', $postData['article_link'], $email_template);
                    $email_template = str_replace('#used_page#', '', $email_template);
                    $email_template = str_replace('#comment#', $postData['comments'], $email_template);

                    $requestDataAdmin = 'A content removal request has been submitted for the following record :';
                    $name = $postData['first_name'] . ' ' . $postData['last_name'];
                    $email = $postData['email'];
                    $phone = $postData['phone_number'];
                    $string = $postData['article_link'];

                    // Convert string to array                
                    $stringArray = explode('?', $string);
                    $encryptedQryStringArr = $stringArray[1];
                    $encryptedQryString = explode('=', $encryptedQryStringArr);
                    if ($encryptedQryString[1])
                        $decrypted_string = decryptQueryString($encryptedQryString[1]);

                    if (isset($decrypted_string['folder_id']) and $decrypted_string['folder_id'] != '') {
                        $item_id = $decrypted_string['folder_id'];
                    }

                    /* if(strpos($stringArray[1],'&')){
                      // Convert string to array
                      $subStringArray = explode('&',$stringArray[1]);
                      // Convert string to array
                      $itemArray = explode('=',$subStringArray[0]);
                      $item_id = $itemArray[1];
                      }else{
                      // Convert string to array
                      $subStringArray = explode('=',$stringArray[1]);
                      $item_id = $subStringArray[1];
                      } */
                    // Get item tree data of ftree item                
                    $item_par_id = getTreeData($item_id);
                    $info = array('item_link' => $postData['article_link'], 'comment' => $postData['comments'], 'item_id' => $item_par_id[1]['item_id']);
                    $succes_message = 'Your request has been submitted. Thank you';
                    // Get archive admin email id's				
                    if ($item_par_id[1]['item_id'] != '') {
                        $AdministratorEmailId = getArchiveAdministratorEmails($item_par_id[1]['item_id']);
                        $item_id = $item_par_id[1]['item_id'];
                    }


                    if ($AdministratorEmailId != '') {
                        $emailaddress['societyEmail'] = $AdministratorEmailId;
                    }
                    $emailaddress['userEmail'] = $postData['email'];
                    break;
                case 'CT':
                    if ($_POST['item_id'] != '') {
                        // Get archive admin email id's	
                        $AdministratorEmailId = getArchiveAdministratorEmails($_POST['item_id']);
                        if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'U') {
                            // Get archive admin email id's	
                            $AdministratorEmailId = getArchiveAdministratorEmails($_POST['item_id'], $_POST['type']);
                        }
                        if ($AdministratorEmailId != '') {
                            $emailaddress['societyEmail'] = $AdministratorEmailId;
                        }
                        $emailaddress['userEmail'] = $postData['contact_email'];
                    } else {
                        $emailaddress['userEmail'] = $postData['contact_email'] . ',' . BUSINESS_EMAIL;
                    }
                    $succes_message = 'Your contact request has been submitted.Thank you';
                    $email_data['from'] = $postData['contact_email'];
                    $email_data['reply'] = $postData['contact_email'];
                    $email_data['subject'] = 'Contact Request';
                    $sub = 'Subject';
                    $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                    $link_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'link.png" alt="ArchiveInABox Logo" />';
                    $email_template = str_replace('#sub_link_image#', $comment_image, $email_template);
                    $email_template = str_replace('#first_name#', $postData['contact_first_name'], $email_template);
                    $email_template = str_replace('#last_name#', $postData['contact_last_name'], $email_template);
                    $email_template = str_replace('#email#', $postData['contact_email'], $email_template);
                    $email_template = str_replace('#phone#', '', $email_template);
                    $email_template = str_replace('#sub_link#', $sub, $email_template);
                    $email_template = str_replace('#sub_link_name#', $postData['contact_subject'], $email_template);
                    $email_template = str_replace('#used_page#', '', $email_template);
                    $email_template = str_replace('#comment#', $postData['contact_comments'], $email_template);
                    //                    $requestDataAdmin='The following user has requested to contact you with the details provided below:';
                    $requestData = 'Your request has been submitted to "' . trim($_SESSION['archive_item_title']) . '" with the following details.';
                    $requestDataAdmin = 'A contact request has been submitted to "' . trim($_SESSION['archive_item_title']) . '" with the following details:';
                    $name = $postData['contact_first_name'] . ' ' . $postData['contact_last_name'];
                    $email = $postData['contact_email'];
                    $phone = '';
                    $info = array('item_link' => $postData['contact_subject'], 'comment' => $postData['contact_comments']);
                    break;

                case 'CLAIM':
                    $item_id = $_POST['item_id'];
                    /* $verification_code = md5(date('Ymd') . '-' . time() . '-' . $item_id);
                      $encrypted_params = encryptQueryString('folder_id=' . $item_id . '&verification_code=' . $verification_code);
                      $verification_url = 'http://develop.archiveinabox.com/register.html?q=' . $encrypted_params;
                      $emailaddress['userEmail'] = $postData['claim_email_id'];
                      $succes_message = 'Your claim society has been submitted. Thank you';
                      $email_data['from'] = $postData['claim_email_id'];
                      $email_data['reply'] = $postData['claim_email_id'];
                      $email_data['subject'] = 'Contact Request';
                      $sub = 'Claim Society Verification';
                      $comment = 'Click on the verification link to complete the registration process.';
                      $comment .= $verification_url;
                      $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                      $link_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'link.png" alt="ArchiveInABox Logo" />';
                      $email_template = str_replace('#sub_link_image#', $comment_image, $email_template);
                      $email_template = str_replace('#first_name#', $postData['claim_first_name'], $email_template);
                      $email_template = str_replace('#last_name#', $postData['claim_last_name'], $email_template);
                      $email_template = str_replace('#email#', $postData['claim_email_id'], $email_template);
                      $email_template = str_replace('#phone#', '', $email_template);
                      $email_template = str_replace('#sub_link#', $sub, $email_template);
                      $email_template = str_replace('#sub_link_name#', $sub, $email_template);
                      $email_template = str_replace('#used_page#', '', $email_template);
                      $email_template = str_replace('#comment#', $comment, $email_template);
                      //                    $requestDataAdmin='The following user has requested to contact you with the details provided below:';
                      $requestData = 'Your request has been submitted to "' . trim($_SESSION['archive_item_title']) . '" with the following details.';
                      $requestDataAdmin = 'A contact request has been submitted to "' . trim($_SESSION['claim_historical_society_name']) . '" with the following details:'; */
                    $name = $postData['claim_first_name'] . ' ' . $postData['claim_last_name'];
                    $email = $postData['register_email'];
                    $phone = $postData['claim_phone_no'];
                    //$info = array('item_link' => $sub, 'comment' => $comment);
                    break;

                case 'RC':
                    $succes_message = 'Your report has been submitted . Thank you.';
                    $email_data['from'] = $postData['email'];
                    $email_data['reply'] = $postData['email'];
                    $email_data['subject'] = 'Report Content';
                    $phone = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">' . $postData['phone_number'] . '</div><div style="clear:both;"></div>';
                    $sub = 'Article Link';
                    $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                    $reason = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'reason.png" alt="ArchiveInABox Logo" /><strong>Reason</strong></div>
                               <div style="width:70%; float:left; font-size:14px; padding:5px;">' . $postData['reporting_reason'] . '</div>
                               <div style="clear:both;"></div>';
                    $email_template = str_replace('#phone_image#', $phone_image, $email_template);
                    $email_template = str_replace('#sub_link_image#', $link_image, $email_template);
                    $email_template = str_replace('#first_name#', $postData['first_name'], $email_template);
                    $email_template = str_replace('#last_name#', $postData['last_name'], $email_template);
                    $email_template = str_replace('#email#', $postData['email'], $email_template);
                    $email_template = str_replace('#phone#', $phone, $email_template);
                    $email_template = str_replace('#sub_link#', $sub, $email_template);
                    $email_template = str_replace('#sub_link_name#', $postData['article_link'], $email_template);
                    $email_template = str_replace('#used_page#', $reason, $email_template);
                    $email_template = str_replace('#comment#', $postData['comments'], $email_template);
                    $requestData = 'Your report has been submitted with the following details.';
                    $requestDataAdmin = 'A  Report content request has been submitted for the following record :';

                    $name = $postData['first_name'];
                    $email = $postData['email'];
                    $phone = $postData['phone_number'];
                    $string = $postData['article_link'];
                    // Convert string to array                    
                    $stringArray = explode('?', $string);
                    if (strpos($stringArray[1], '&')) {
                        // Convert string to array    
                        $subStringArray = explode('&', $stringArray[1]);
                        // Convert string to array    
                        $itemArray = explode('=', $subStringArray[0]);
                        $item_id = $itemArray[1];
                    } else {
                        // Convert string to array    
                        $subStringArray = explode('=', $stringArray[1]);
                        $item_id = $subStringArray[1];
                    }
                    // Convert string to array    
                    $item_par_id = getTreeData($item_id);
                    $info = array('item_link' => $postData['article_link'], 'comment' => $postData['comments'], 'reporting_reason' => $postData['reporting_reason'], 'item_id' => $item_par_id[1]['item_id']);
                    // Get archive admin email id's		
                    $AdministratorEmailId = getArchiveAdministratorEmails($item_par_id[1]['item_id']);
                    if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'U') {
                        // Get archive admin email id's  
                        $AdministratorEmailId = getArchiveAdministratorEmails($_POST['item_id'], $_POST['type']);
                    }
                    if ($AdministratorEmailId != '') {
                        $emailaddress['societyEmail'] = $AdministratorEmailId;
                    }
                    $emailaddress['userEmail'] = $postData['email'];

                    break;
                case 'CS':
                    $succes_message = 'Your contact request has been submitted.Thank you';
                    //$postData['request_type'] = 'CT';
                    $item_id = (isset($postData['item_id']) && $postData['item_id'] != '') ? $postData['item_id'] : '-1';
                    $search_type = (isset($postData['search_type']) && !empty($postData['search_type'])) ? $postData['search_type'] : '';
                    $name = $postData['contact_us_name'];
                    $phone = $postData['front_contact_us_phone'];
                    $email = $postData['front_contact_us_email'];
                    $info = array('organization_name' => $postData['organization_name'], 'comment' => $postData['comments'], 'timestamp_value' => $postData['timestamp_value']);
                    $phone_image_div = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">' . $phone . '</div><div style="clear:both;"></div>';

                    $email_data['from'] = ADMIN_EMAIL;
                    $email_data['reply'] = ADMIN_EMAIL;
                    $email_data['subject'] = 'Contact Request';
                    // Get contact_us.html file html content                 
                    $email_template = file_get_contents(EMAIL_PATH . "/contact_us.html");
                    $name_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'first-name.png" alt="" />';
                    $email_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'email.png" alt="" />';
                    $comment_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'comment.png" alt="" />';
                    $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                    $crop_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'corporate.png" alt="" />';

                    $email_template = str_replace('#name_image#', $name_image, $email_template);
                    $email_template = str_replace('#crop_image#', $crop_image, $email_template);
                    $email_template = str_replace('#email_image#', $email_image, $email_template);
                    $email_template = str_replace('#comment_image#', $comment_image, $email_template);
                    $email_template = str_replace('#phone_image#', $phone_image, $email_template);

                    $email_template = str_replace('#first_name#', $name, $email_template);
                    $email_template = str_replace('#crop_name#', $postData['organization_name'], $email_template);
                    $email_template = str_replace('#email#', $email, $email_template);
                    $email_template = str_replace('#phone#', $phone_image_div, $email_template);
                    $email_template = str_replace('#comment#', $postData['comments'], $email_template);
                    $requestDataAdmin = 'The following user has requested to contact you with the details provided below:';
                    $email_template = str_replace('#request#', $requestDataAdmin, $email_template);
                    // Get contact_response.html file html content 
                    $email_template1 = file_get_contents(EMAIL_PATH . "/contact_response.html");
                    $email_data1['to'] = $email;
                    $email_data1['from'] = ADMIN_EMAIL;
                    $email_data1['reply'] = ADMIN_EMAIL;
                    $email_data1['subject'] = 'Contact Request Submitted';
                    break;
            }
            $info['status'] = 0;
            $searchType = '';
            if (isset($item_par_id[0]['item_title'])) {
                $searchType = 'P';
                if ($item_par_id[0]['item_title'] == 'ARCHIVE GROUP') {
                    $searchType = 'A';
                }
            }
            $info['search_type'] = $searchType;
            $responseArray = ['status' => 'error', 'message' => 'All fields are required.'];
            $user_ip_address = getenv('HTTP_CLIENT_IP')? : getenv('HTTP_X_FORWARDED_FOR')? : getenv('HTTP_X_FORWARDED')? : getenv('HTTP_FORWARDED_FOR')? : getenv('HTTP_FORWARDED')? : getenv('REMOTE_ADDR');
            if (!empty($postData)) {
                // Request array to store request               
                $postDataRequest = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => 1,
                    "_op" => "req_store",
                    "req_type" => $postData['request_type'],
                    "req_name" => $name,
                    "req_phone" => $phone,
                    "req_email" => $email,
                    "req_ipaddr" => $user_ip_address,
                    "req_info" => json_encode($info),
                    "req_item" => $item_id
                );
                // Service request to store request  
                $apiResponse = aibServiceRequest($postDataRequest, 'custreq');
                $responseArray = ['status' => 'error', 'message' => 'Some things went wrong! Please try again.'];
                if ($apiResponse['status'] == 'OK') {
                    if (isset($postData['search_type']) && !empty($postData['search_type']) && $postData['search_type'] == 'C') {
                        $email_data['to'] = BUSINESS_EMAIL;

                        // Send email to user                         
                        $sendEmail = sendMail($email_data, $email_template);
                        // Send email to admin
                        sendMail($email_data1, $email_template1);
                    } else {
                        // Used foreach loop over $emailaddress to check evry key of array is equal to societyEmail conditions
                        /// Incase of socity page contact us and content removal request mail send to socity admin 
                        if ($postData['request_type'] == 'RD' || $postData['request_type'] == 'CT') {
                            /*
                              add_admin_email variable with if block added to send mail only for request type RD
                              added on 31 May 2019 by Mansij
                             */
                            $add_admin_email = '';
                            if ($postData['request_type'] == 'RD') {
                                $add_admin_email = ',' . ADMIN_EMAIL;
                            }
                            /* add_admin_email end here */


                            foreach ($emailaddress as $key => $value) {
                                if ($key == 'societyEmail') {
                                    $email_data['to'] = $value . $add_admin_email;
                                    $email_template = str_replace('#request#', $requestDataAdmin, $email_template);
                                } else {
                                    $email_data['to'] = $value;
                                    //$email_template = str_replace($requestDataAdmin,'#request#',$email_template);
                                    $email_template = str_replace('#request#', $requestDataAdmin, $email_template);
                                    $email_template = str_replace('#request#', $requestData, $email_template);
                                }

                                /*
                                  if block added not to set the reply and from variable only for request type RD
                                  added on 31 May 2019 by Mansij
                                 */
                                if ($postData['request_type'] != 'RD') {
                                    $email_data['reply'] = ADMIN_EMAIL;
                                    $email_data['from'] = ADMIN_EMAIL;
                                }
                                /* end here */

                                // Send email to user
                                $sendEmail = sendMail($email_data, $email_template);
                                unset($email_data['to']);
                            }
                        } else {
                            $email_data['to'] = BUSINESS_EMAIL . ',' . $emailaddress['userEmail'];
                            $email_template = str_replace('#request#', $requestDataAdmin, $email_template);
                            // Send email to user                         
                            $sendEmail = sendMail($email_data, $email_template);
                            // Send email to admin
                            sendMail($email_data1, $email_template1);
                        }
                    }
                    if ($sendEmail) {
                        $responseArray = ['status' => 'success', 'message' => $succes_message];
                    }
                }
            }
        }
        print json_encode($responseArray);
        break;
    case 'get_all_archive' :
        $parent_id = (isset($_POST['folder_id']) && $_POST['folder_id'] != '') ? $_POST['folder_id'] : HISTORICAL_SOCITY_ROOT;
        if ($parent_id != HISTORICAL_SOCITY_ROOT) {
            $itemParents = getTreeData($parent_id);
            $parent_id = $itemParents[1]['item_id'];
        }
        // Request array to get item list of ftree parent item        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $parent_id,
            "opt_get_property" => 'Y',
            "opt_get_long_prop" => 'Y'
        );
        // Service request to get item list of ftree parent item 
        $apiResponse = aibServiceRequest($postData, 'browse');
        $filter['state'] = !empty($_POST['state']) ? $_POST['state'] : "";
        $filter['county'] = !empty($_POST['county']) ? $_POST['county'] : "";
        $filter['city'] = !empty($_POST['city']) ? $_POST['city'] : "";
        $filter['zip'] = !empty($_POST['zip']) ? $_POST['zip'] : "";
        // Used foreach loop over api response & check conditions 
        foreach ($apiResponse['info']['records'] as $key => $dataArray) {
            // Check set $dataArray['item_source_type'] & $dataArray['item_source_type'] == 'L'
            if (isset($dataArray['item_source_type']) && $dataArray['item_source_type'] == 'L') {
                unset($apiResponse['info']['records'][$key]);
                continue;
            }
            // Check array item title equal to advertisements & unset $apiResponse['info']['records'][$key]
            if (in_array(strtolower($dataArray['item_title']), array('advertisements', 'scrapbooks', 'society-share'))) {
                unset($apiResponse['info']['records'][$key]);
                continue;
            }
            if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0') {
                unset($apiResponse['info']['records'][$key]);
                continue;
            }
            if (isset($dataArray['properties']['publish_status']) && $dataArray['properties']['publish_status'] == 'N') {
                unset($apiResponse['info']['records'][$key]);
                continue;
            }
            if (isset($dataArray['properties']['status']) && $dataArray['properties']['status'] != 1) {
                unset($apiResponse['info']['records'][$key]);
                continue;
            }
        }

        // Apply user filter on api response             
        $apiResponse = applyUserFilterOriData($apiResponse, $filter);
        if ($parent_id == HISTORICAL_SOCITY_ROOT) {
            // Used for each loop over api response & check conditions            
            foreach ($apiResponse['info']['records'] as $key => $archiveGroup) {
                // Request array to get item list of ftree                 
                $postItemData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => 1,
                    "_op" => "list",
                    "parent" => $archiveGroup['item_id']
                );
                // Service request to get item list of ftree 
                $apiResponseItemData = aibServiceRequest($postItemData, 'browse');
                $apiResponse['info']['records'][$key]['archive'] = $apiResponseItemData['info']['records'];
            }
        } else {
            // Get item details of ftree item           
            $archiveDetails = getItemData($parent_id);
            $archiveData = $apiResponse['info']['records'];
            $archiveDataInfo['info']['records'][0] = $archiveDetails;
            $archiveDataInfo['info']['records'][0]['archive'] = $archiveData;
            $apiResponse = $archiveDataInfo;
        }
        $requestType = 'ar';
        if (isset($_POST['page']) && $_POST['page'] == 'search') {
            $requestType = (isset($_POST['folder_id']) && $_POST['folder_id'] == 1) ? 'ag' : 'ar';
        }
        // item_listing.php file has been included here to show archive listing          
        include_once TEMPLATE_PATH . 'item_listing.php';
        break;
    case 'get_all_public_archive' :
        $folder_id = (isset($_POST['folder_id']) && $_POST['folder_id'] != '') ? $_POST['folder_id'] : PUBLIC_USER_ROOT;
        // Request array to get item list of ftree item       
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folder_id //6499
        );
        // Service request to get item list of ftree item 
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($folder_id != PUBLIC_USER_ROOT) {
//            $userArchive = getItemData($folder_id);
            $userArchive = getTreeData($folder_id);
            unset($apiResponse['info']['records']);
            $apiResponse['info']['records'][0] = $userArchive[1];
        }
        $requestType = 'ag';
        // item_listing.php file has been included here to show archive listing    
        include_once TEMPLATE_PATH . 'item_listing.php';
        break;
    case 'search_data':
        $search_text = !empty($_POST['search_text']) ? $_POST['search_text'] : "";
        $archive_id = !empty($_POST['archive_id']) ? $_POST['archive_id'] : "";
        $archive_type = !empty($_POST['archive_type']) ? $_POST['archive_type'] : "";
        $current_page = !empty($_POST['current_page']) ? $_POST['current_page'] : "";
        $record_per_page = !empty($_POST['record_per_page']) ? $_POST['record_per_page'] : "";
        $source = !empty($_POST['source']) ? $_POST['source'] : "";
        $current_item_id = !empty($_POST['current_item_id']) ? $_POST['current_item_id'] : '';
        $previousYouWere = getTreeData($current_item_id);
        unset($previousYouWere[0]);
        $apiResponse = "";
        if ($search_text && $archive_id && $archive_type) {
            // Request array to get search  data            
            $searchPostData = array(
                "_key" => APIKEY,
                "_session" => time() - 100,
                "phrase" => $search_text,
                "pagenum" => $current_page,
                "perpage" => 100,
                "_indexcfg" => $archive_type . '_' . $archive_id
            );
            // Service request to get search data 
            $apiResponse = aibSearchRequest($searchPostData);
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadXML($apiResponse);
        $searchResultResponse = array();
        foreach ($dom->getElementsByTagName('doc') as $item) {
            $searchResultData = array();
            for ($i = 0; $i < $item->childNodes->length; ++$i) {
                $child = $item->childNodes->item($i);
                if ($child->nodeType == XML_ELEMENT_NODE) {
                    $searchResultData[$child->tagName] = $child->nodeValue;
                }
            }
            $searchResultResponse[] = $searchResultData;
        }
        $snipTextDataArray = [];
        if (!empty($searchResultResponse)) {
            // Used foreach loop over api response to check conditions  
            foreach ($searchResultResponse as $key => $searchDataArray) {
                $resultIDArray[] = $searchDataArray['uri'];
                $resultScoreArray[$searchDataArray['uri']] = $searchDataArray['score'];
                $snipTextDataArray[$searchDataArray['uri']] = $searchDataArray['snippet'];
            }
            // Convert array to string             
            $resultIDString = implode(',', $resultIDArray);
            // Request array to get item list of ftree parent item            
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list",
                "opt_sort" => 'ID',
                "parent" => -1,
                "opt_get_files" => 'Y',
                "opt_deref_links" => 'Y',
                "opt_get_property" => 'Y',
                "opt_get_long_prop" => 'Y',
                "item_list" => $resultIDString
            );
            // Service request to get item list of ftree parent item  
            $apiResponseResultSearch = aibServiceRequest($postData, 'browse');
            if ($apiResponseResultSearch['status'] == 'OK') {
                $removeCondition = array('advertisements', 'Scrapbooks', 'Comments', 'Advertisement');
                $removeConditionType = array('cmnt', 'cmntthrd', 'cmntset');
                $allowedItemType = array('AG', 'AR');
                // Used foreach loop over api response & unset $apiResponseResultSearch['info']['records'] & check conditions  
                $visitedItem = json_decode($_COOKIE['visited_items']);
                foreach ($apiResponseResultSearch['info']['records'] as $key => $result) {
                    $itemParents = getTreeData($result['item_id']);
                    unset($itemParents[0]);
                    array_pop($itemParents);
                    $apiResponseResultSearch['info']['records'][$key]['item_location'] = $itemParents;
                    if (in_array($result['item_id'], $visitedItem)) {
                        $apiResponseResultSearch['info']['records'][$key]['link_visited'] = 'yes';
                    }
                    if (isset($result['properties']['aib:private']) and $result['properties']['aib:private'] == 'Y') {
                        unset($apiResponseResultSearch['info']['records'][$key]);
                        continue;
                    } else if (in_array($result['item_title'], $removeCondition) || in_array($result['item_type'], $removeConditionType) || in_array($result['item_type'], $allowedItemType) || $result['item_parent'] == '-1') {
                        unset($apiResponseResultSearch['info']['records'][$key]);
                        continue;
                    }
                    if (isset($result['properties']['publish_status']) && $result['properties']['publish_status'] == 'N') {
                        unset($apiResponseResultSearch['info']['records'][$key]);
                        continue;
                    }
                }
            }
        }
        // search_result_data.php file has been included here to show search data        
        include_once TEMPLATE_PATH . 'search_result_data.php';
        break;
    case 'get_item_archive_group_details':
        $record_id = $_POST['item_id'];
        $archivePropertyDetails = array();
        if ($record_id) {
            // Get item tree data of ftree item             
            $itemParents = getTreeData($record_id);
            $archive_id = $itemParents[1]['item_id'];
            // Get item details with property            
            $itemDetails = getItemData($archive_id);  //getItemData
            $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $itemDetails['properties']['archive_logo_image'];
            $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $itemDetails['properties']['archive_header_image'];
            $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $itemDetails['properties']['archive_details_image'];
            $_SESSION['archive_request_reprint_text'] = $itemDetails['properties']['archive_request_reprint_text'];
            // Get item property	 
            //$archivePropertyDetails = getItemProperty($archive_id);
        }
        print json_encode($itemDetails['properties']);
        break;
    case 'get_item_details':
        $item_id = $_POST['item_id'];
        if ($item_id) {
            // Get item details of ftree item            
            $itemDetails = getItemData($item_id);
            $itemDetails['itemTitle'] = urldecode($itemDetails['item_title']);
            print json_encode($itemDetails);
        }
        break;
    case 'check_duplicate_item':
        $society_state = !empty($_POST['society_state']) ? $_POST['society_state'] : "";
        $society_name = !empty($_POST['society_name']) ? $_POST['society_name'] : "";
        $archive_id = !empty($_POST['archive_id']) ? $_POST['archive_id'] : "";
        $responseData = array('status' => 'error', 'message' => 'Item not deleted.');
        if ($society_state != "" && $society_name != "") {
            // Request array to get item list of ftree parent item              
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list",
                "parent" => 1,
                "opt_get_property" => 'Y',
                "opt_get_long_prop" => 'Y'
            );
            // Service request to get item list of ftree parent item
            $apiResponse = aibServiceRequest($postData, 'browse');
            //$records=$apiResponse['info']['records'];
            // Used foreach loop over $records & check conditions             
            /* if ($archive_id) {
              foreach ($apiResponse['info']['records'] as $record) {
              if (trim($record['item_id']) == trim($archive_id) && trim($record['item_title']) == trim($society_name) && trim($record['properties']['society_state']) == trim($society_state)) {
              echo "true";
              exit;
              }
              }
              } */
            foreach ($apiResponse['info']['records'] as $record) {
                if (trim($record['item_id']) != trim($archive_id) && trim($record['item_title']) == trim($society_name) && trim($record['properties']['society_state']) == trim($society_state)) {
                    echo "false";
                    exit;
                }
            }
        }
        echo "true";
        break;
    case 'get_archive_search_items':
        $responseData = array('status' => 'error', 'message' => 'Item not deleted.');
        // Request array to get item list of ftree parent item        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => 1,
            "opt_get_property" => 'N'
        );
        // Service request to get item list of ftree parent item  
        $apiResponse = aibServiceRequest($postData, 'browse');
        $records = $apiResponse['info']['records'];

        $filterState = !empty($_REQUEST['State']) ? ucwords(strtolower($_REQUEST['State'])) : "";
        $filterCounty = !empty($_REQUEST['County']) ? ucwords(strtolower($_REQUEST['County'])) : "";
        $filterCity = !empty($_REQUEST['City']) ? ucwords(strtolower($_REQUEST['City'])) : "";
        $filterZip = !empty($_REQUEST['Zip']) ? ucwords(strtolower($_REQUEST['Zip'])) : "";

        $location = array();
        $stateList = array();
        $countyList = array();
        $cityList = array();
        $zipList = array();
        // Used foreach loop over $records to check contion for each array element         
        foreach ($records as $record) {
            // Get item property of ftree item            
            $record['fullProp'] = getItemProperty($record['item_id']);
            if ($record['fullProp']['status'] == 1) {
                $state = ucwords(strtolower($record['fullProp']['archive_display_state']));
                $county = ucwords(strtolower($record['fullProp']['archive_display_county']));
                $city = ucwords(strtolower($record['fullProp']['archive_display_city']));
                $zip = ucwords(strtolower($record['fullProp']['archive_display_zip']));
                if (!empty($filterState) && $filterState != $state) {
                    continue;
                }
                if (!empty($filterCounty) && trim($filterCounty) != $county) {
                    //echo  $filterCounty."==".$record['fullProp']['archive_display_county'];
                    continue;
                }
                if (!empty($filterCity) && $filterCity != $city) {
                    continue;
                }
                if (!empty($filterZip) && $filterZip != $zip) {
                    continue;
                }
                if (!empty($state)) {
                    $stateList[$state] = $state;
                }

                if (!empty($city))
                    $cityList[$city] = $city;
                if (!empty($zip))
                    $zipList[$zip] = $zip;
                if (!empty($county))
                    $countyList[$county] = $county;

                $location['State'] = $stateList;
                $location['City'] = $cityList;
                $location['Zip'] = $zipList;
                $location['County'] = $countyList;
                $location['selected'] = $_REQUEST;
            }
        }
        print json_encode($location);
        break;
    case 'get_public_archive_search_items':
        $responseData = array('status' => 'error', 'message' => 'Item not deleted.');
        // Request array to get item list of ftree parent item 
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => PUBLIC_USER_ROOT, //6499
            "opt_get_property" => 'Y',
            "opt_get_long_prop" => 'Y'
        );
        // Service request to get item list of ftree parent item 
        $apiResponse = aibServiceRequest($postData, 'browse');
        $records = $apiResponse['info']['records'];

        $filterState = !empty($_REQUEST['State']) ? ucwords(strtolower($_REQUEST['State'])) : "";
        $filterCounty = !empty($_REQUEST['County']) ? ucwords(strtolower($_REQUEST['County'])) : "";
        $filterCity = !empty($_REQUEST['City']) ? ucwords(strtolower($_REQUEST['City'])) : "";
        $filterZip = !empty($_REQUEST['Zip']) ? ucwords(strtolower($_REQUEST['Zip'])) : "";

        $location = array();
        $stateList = array();
        $countyList = array();
        $cityList = array();
        $zipList = array();
        // Used for each loop over $records & check conditions        
        foreach ($records as $record) {
            $record['fullProp'] = $record['properties']; //getItemProperty($record['item_id']);
            // if($record['fullProp']['status']==1){
            $state = ucwords(strtolower($record['fullProp']['archive_display_state']));
            $county = ucwords(strtolower($record['fullProp']['archive_display_county']));
            $city = ucwords(strtolower($record['fullProp']['archive_display_city']));
            $zip = ucwords(strtolower($record['fullProp']['archive_display_zip']));
            if (!empty($filterState) && $filterState != $state) {
                continue;
            }
            if (!empty($filterCounty) && trim($filterCounty) != $county) {
                //echo  $filterCounty."==".$record['fullProp']['archive_display_county'];
                continue;
            }
            if (!empty($filterCity) && $filterCity != $city) {
                continue;
            }
            if (!empty($filterZip) && $filterZip != $zip) {
                continue;
            }
            if (!empty($state)) {
                $stateList[$state] = $state;
            }

            if (!empty($city))
                $cityList[$city] = $city;
            if (!empty($zip))
                $zipList[$zip] = $zip;
            if (!empty($county))
                $countyList[$county] = $county;

            $location['State'] = $stateList;
            $location['City'] = $cityList;
            $location['Zip'] = $zipList;
            $location['County'] = $countyList;
            $location['selected'] = $_REQUEST;
            //}
        }
        print json_encode($location);
        break;
    case 'get_user_scrapbook_listing':
        $userScrapbookList = [];
        if (isset($_SESSION['aib']['user_data']['user_id'])) {
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            // Request array to get scrapbook list            
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $user_id,
                "_op" => "scrpbk_lst",
                "user_id" => $user_id
            );
            // Service request to get scrapbook list
            $apiResponseData = aibServiceRequest($postRequestData, 'scrapbook');
            if ($apiResponseData['status'] == 'OK') {
                $userScrapbookParent = '';
                // Used foreach loop over api response to check item title = Scrapbooks of each array element                 
                foreach ($apiResponseData['info']['records'] as $key => $dataArray) {
                    if ($dataArray['item_title'] == 'Scrapbooks') {
                        $userScrapbookParent = $dataArray['item_id'];
                        break;
                    }
                }
                if ($userScrapbookParent) {
                    // Get Item child data with property                    
                    $userScrapbookList = getItemChildWithData($userScrapbookParent);
                    // Used foreach loop over $userScrapbookList & unset $userScrapbookList[$key]                     
                    foreach ($userScrapbookList as $key => $scrapbookData) {
                        if (isset($scrapbookData['item_ref']) && $scrapbookData['item_ref'] > 1) {
                            unset($userScrapbookList[$key]);
                            continue;
                        }
                    }
                    $userScrapbookList = array_values($userScrapbookList);
                }
            }
            $responseArray = ['status' => 'success', 'message' => 'Success', 'data' => $userScrapbookList];
        } else {
            $responseArray = ['status' => 'login', 'message' => 'You must login to add an item to scrapbook', 'data' => $userScrapbookList];
        }
        print json_encode($responseArray);
        break;
    case'check_scrapbook_record':
        $userScrapbookParent = [];
        $scrapbookList = [];
        $responseArray = ['status' => '', 'data' => ''];
        if (isset($_SESSION['aib']['user_data']['user_id'])) {
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            // Request array to get scrapbook list             
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $user_id,
                "_op" => "scrpbk_lst",
                "user_id" => $user_id
            );
            // Service request to get scrapbook list
            $apiResponseData = aibServiceRequest($postRequestData, 'scrapbook');
            if ($apiResponseData['status'] == 'OK') {
                $userScrapbookParent = '';
                // Used foreach loop over api response & check  item title of each array element = 'Scrapbooks'                
                foreach ($apiResponseData['info']['records'] as $key => $dataArray) {
                    if ($dataArray['item_title'] == 'Scrapbooks') {
                        $userScrapbookParent = $dataArray['item_id'];
                        break;
                    }
                }
                if ($userScrapbookParent) {
                    // Get item child data with property                 
                    $userScrapbookList = getItemChildWithData($userScrapbookParent);
                }
                // Used to foreach loop over $userScrapbookList to get scrapbook list of each array element              
                foreach ($userScrapbookList as $key => $dataArray) {
                    // Request array to get scrapbook entries                
                    $postRequestData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $_SESSION['aib']['user_data']['user_id'],
                        "_op" => "scrpbk_lstent",
                        "obj_id" => $dataArray['item_id'],
                        "opt_get_property" => 'Y'
                    );
                    // Service request to get scrapbook entries  
                    $apiResponseData = aibServiceRequest($postRequestData, 'scrapbook');
                    // Used foreach loop over api response to store $scrapbook['item_ref'] value in $scrapbookList[] of each array element                  
                    foreach ($apiResponseData['info']['records'] as $key => $scrapbook) {
                        $scrapbookList[] = $scrapbook['item_ref'];
                    }
                }
                $responce = 0;
                if (!empty($_SESSION["scrapbook_ref"])) {
                    unset($_SESSION["scrapbook_ref"]);
                }
                $_SESSION["scrapbook_ref"] = $scrapbookList;
                if (isset($_POST['record_d']) && !empty($_POST['record_d'])) {
                    if (in_array($_POST['record_d'], $scrapbookList)) {
                        $responce = 1;
                        $responseArray = ['status' => 'exist', 'data' => $responce];
                    }
                }
            }
        }
        print json_encode($responseArray);
        break;
    case 'add_item_to_scrapbook':
        $responseArray = array('status' => 'error', 'message' => 'You are Robot!');
        // Current Date & time (timestamp)        
        $current_time = time();
        $time_diff = $current_time - $_POST['timestamp_value'];
        if ($time_diff > TIMESTAMP_6) {
            if (isset($_SESSION['aib']['user_data']['user_id'])) {
                if ($_POST['scrap_name'] != '') {
                    // Request array to create new scrapbook                      
                    $postItemData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $_SESSION['aib']['user_data']['user_id'],
                        "_op" => "scrpbk_new",
                        "user_id" => $_SESSION['aib']['user_data']['user_id'],
                        "title" => $_POST['scrap_name']
                    );
                    // Service request to create new scrapbook 
                    $apiResponseData = aibServiceRequest($postItemData, 'scrapbook');
                    $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
                    if ($apiResponseData['status'] == 'OK') {
                        // Request array to set item property                        
                        $apiRequestDataItem = array(
                            '_key' => APIKEY,
                            '_user' => $_SESSION['aib']['user_data']['user_id'],
                            '_op' => 'set_item_prop',
                            '_session' => $sessionKey,
                            'obj_id' => $apiResponseData['info'],
                            'propname_1' => 'scrapbook_type',
                            'propval_1' => 'public',
                            'propname_2' => 'aibftype',
                            'propval_2' => 'sg'
                        );
                        // Service request to set item property
                        $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
                        $apiResponseData['info'];
                    }
                }
                $item_id = !empty($_POST['item_id']) ? $_POST['item_id'] : "";
                $entry_title = !empty($_POST['entry_title']) ? $_POST['entry_title'] : "";
                $scrapbook_id = !empty($_POST['scrap_name']) ? $apiResponseData['info'] : $_POST['scrapbook_id'];
                $item_parent = !empty($_POST['item_parent']) ? $_POST['item_parent'] : "";
                // Get item child list data                
                $scrapbookItemList = getItemChildWithData($scrapbook_id);
                $itemInList = '';
                if (!empty($scrapbookItemList)) {
                    // Used foreach loop over $scrapbookItemList to check $scrapbookListArray['item_ref'] equal to $item_id                     
                    foreach ($scrapbookItemList as $scrKey => $scrapbookListArray) {
                        if ($scrapbookListArray['item_ref'] == $item_id) {
                            $itemInList = 'yes';
                            break;
                        }
                    }
                }
                // Request array to create new scrapbook entry for a user               
                if ($itemInList == '') {
                    $postRequestData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "scrpbk_addent",
                        "obj_id" => $scrapbook_id,
                        "title" => $entry_title,
                        "target" => $item_id
                    );
                    // Service request to create new scrapbook entry for a user 
                    $apiResponse = aibServiceRequest($postRequestData, 'scrapbook');
                    $responseArray = ['status' => 'error', 'message' => 'Something went wrong, Please try again'];
                    if ($apiResponse['status'] == 'OK') {
                        // Request array to set item property                         
                        $apiRequestDataItem = array(
                            '_key' => APIKEY,
                            '_user' => 1,
                            '_op' => 'set_item_prop',
                            '_session' => $sessionKey,
                            'obj_id' => $apiResponse['info'],
                            'propname_1' => 'item_parent',
                            'propval_1' => $item_parent
                        );
                        // Service request to set item property
                        $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
                        $responseArray = ['status' => 'success', 'message' => 'Item added to scrapbook successfully.'];
                    }
                } else {
                    $responseArray = ['status' => 'error', 'message' => 'Item already in your scrapbook.'];
                }
            } else {
                $responseArray = ['status' => 'login', 'message' => 'You must login to add an item to scrapbook'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'list_comment_threads':
        $parent_folder_id = $_POST['parent_folder_id'];
        $action = $_POST['action'];
        // Request array to get list comment threat for ftree item       
        if ($parent_folder_id) {
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1, //$_SESSION['aib']['user_data']['user_id'],
                "_op" => "cmnt_lthread",
                "parent_item" => $parent_folder_id,
                "opt_sort" => "ID"
            );
            // Service request to get list comment threat for ftree item
            $apiResponse = aibServiceRequest($postRequestData, 'comments');

            if ($apiResponse['status'] == 'OK') {
                $commentThreads = array();
                if (!empty($apiResponse['info']['records'])) {
                    $commentThreads = $apiResponse['info']['records'];
                    $commentList = array();
                    // Used foreach loop over $commentThreads to get $commentList                 
                    foreach ($commentThreads as $commentThread) {
                        // Get recursive comment thread                  
                        $commentThread['commentsThreads'] = listCommentsInThreadRecursive($commentThread['item_id']); //listCommentsInThread($commentThread['item_id']); 
                        $commentList[] = $commentThread;
                    }
                    if ($action == 'hit') {
                        $commentLists = $commentList;
                        $commentList = array();
                        $commentList[] = array_pop($commentLists);
                    }
                }
            } else {
                $commentList = $apiResponse;
            }
            // comment-description.php file has been included here to show recursive comment              
            include_once TEMPLATE_PATH . 'comment-description.php';
            //print json_encode($commentList);
        }
        break;
    case 'create_comment_thread':
        $parent_folder_id = $_POST['parent_folder_id'];
        $title = generateCommentTitle($_SESSION['aib']['user_data']['user_id']);
        if ($parent_folder_id && !empty($title)) {
            // Request array to create a new comment thread for a tree item
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "cmnt_newthrd",
                "parent_item" => $parent_folder_id,
                "title" => $title,
                "user_id" => $_SESSION['aib']['user_data']['user_id']
            );
            // Service request to create a new comment thread for a tree item
            $apiResponse = aibServiceRequest($postRequestData, 'comments');
            if ($apiResponse['status'] == 'OK') {
                //Add first comment in the comment thread
                $post['parent_thread'] = $apiResponse['info'];
                $post['comment_text'] = $_POST['comment'];
                $post['title'] = $title;
                $apiResponseInnerThread = addCommentToThread($post);

                $apiResponseInnerThread['parent_thread'] = $post['parent_thread'];
                $apiResponseInnerThread['comment_text'] = $post['comment_text'];
                $detailsTitle = explode("-", $post['title']);
                $userName = $detailsTitle[0];
                $time = date('d F Y H:i a', $detailsTitle[1]);
                $apiResponseInnerThread['userName'] = $userName;
                $apiResponseInnerThread['time'] = $time;
                $apiResponseInnerThread['item_id'] = $apiResponseInnerThread['info'];
            } else {
                $apiResponse['API'] = "create_comment_thread";
                print json_encode($apiResponse); //Issue Found
                break;
            }
        }
        print json_encode($apiResponseInnerThread);
        break;
    case 'add_comment_to_thread':
        // Generate comment title            
        $_POST['title'] = generateCommentTitle($_SESSION['aib']['user_data']['user_id']);
        // Add comment to threads           
        $apiResponse = addCommentToThread($_POST);
        $apiResponse['parent_thread'] = $_POST['parent_thread'];
        $apiResponse['comment_text'] = $_POST['comment_text'];
        // Convert string to array              
        $detailsTitle = explode("-", $_POST['title']);
        $userName = $detailsTitle[0];
        $time = date('d F Y H:i a', $detailsTitle[1]);
        $apiResponse['userName'] = $userName;
        $apiResponse['time'] = $time;
        $apiResponse['item_id'] = $apiResponse['info'];

        print json_encode($apiResponse);
        break;
    case 'delete_comment_from_thread':
        $obj_id = $_POST['obj_id'];
        // Request array to delete comment            
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1, //$_SESSION['aib']['user_data']['user_id']
            "_op" => "cmnt_delcmnt",
            "obj_id" => $obj_id
        );
        // Service request to delete comment 
        $apiResponse = aibServiceRequest($postRequestData, 'comments');
        $apiResponse['item_id'] = $obj_id;
        print json_encode($apiResponse);

        break;
    case 'active_public_user':
        $id = $_POST['id'];
        $flg = $_POST['flg'];
        $unclaimed_society_id = $_POST['unclaimed_society_id'];
        $status = 'a';
        // Set user profile status (a/d)            
        $active_user = setUserProfileStatus($id, $status, $flg);

        if ($unclaimed_society_id) {
            $userProperty[0]['name'] = 'email_verify';
            $userProperty[0]['value'] = 'yes';
            // Request array to set user profile property            
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "set_profile_prop_batch",
                "user_id" => $id,
                "property_list" => json_encode($userProperty)
            );
            // Service request to set user profile property
            $userPropertyStatus = aibServiceRequest($postRequestData, "users");
            print_r($userPropertyStatus);
            exit;
        }

        // Get user all property           
        $user_property = getUsersAllProperty($id);
        if ($user_property) {
            $responseArray = $user_property;
        } else {
            $responseArray = ['status' => 'error', 'message' => 'error'];
        }
        print json_encode($responseArray);
        break;

    case 'delete_claimed_user_status':
        $id = $_POST['id'];
        if ($id) {
            $apiRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => 'delete_profile',
                "_user" => 1,
                "user_id" => $id
            );
            $apiResponse = aibServiceRequest($apiRequestData, 'users');
            $responseArray = $apiResponse;
        } else {
            $responseArray = ['status' => 'error', 'message' => 'error'];
        }
        print json_encode($responseArray);
        break;

    case 'claimed_user_status':
        $id = $_POST['id'];
        $flg = $_POST['flg'];
        $username = $_POST['username'];
        $item_id = $_POST['item_id'];
		$society_name_new = '';
		$society_name_old = '';
        
        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_item_prop",
            "obj_id" => $item_id,
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $temp_claim_data = $apiResponse['info']['records']['temp_claim_data_' . $username];
        $temp_claim_admin_data_prop = ($apiResponse['info']['records']['temp_claim_admin_data']) ? $apiResponse['info']['records']['temp_claim_admin_data'] : '';
        $archive_user_id = $apiResponse['info']['records']['archive_user_id'];
        $temp_user_created_by_admin = $apiResponse['info']['records']['temp_user_created_by_admin'];
        $temp_society_created_by_admin = $apiResponse['info']['records']['temp_society_created_by_admin'];
		$society_name_old = $temp_society_created_by_admin;
        if ($archive_user_id != $temp_user_created_by_admin && $flg == 'Y') {
            $user_info_post_data = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get_profile",
                "user_id" => $archive_user_id
            );
            $user_info_resp = aibServiceRequest($user_info_post_data, 'users');
            $responseArray = ['status' => 'error', 'message' => 'You already approved ' . $user_info_resp['info']['user_title'] . '. Please reject this user.'];
        } else {
            $temp_data = json_decode($temp_claim_data, true);
            $temp_data_count = count($temp_data);

            $fieldsArray = array("phoneNumber", "redactionsEmailAddress", "reprintEmailAddress", "contactEmailAddress",
                "websiteURL", "physicalAddressLine1", "mailingAddressLine1", "physicalAddressLine2", "mailingAddressLine2", "physicalCity", "mailingCity",
                "physicalState", "mailingState", "physicalZip", "mailingZip", "federalTaxIDNumber", "sateTaxIDNumber", "entityOrganization", "entityOrganizationOther",
                "CEO", "CEO_firstName", "CEO_lastName", "CEO_email", "executiveDirector", "executiveDirector_firstName", "executiveDirector_lastName", "executiveDirector_email",
                "precident", "precident_firstName", "precident_lastName", "precident_email", "otherExecutive", "otherExecutive_firstName", "otherExecutive_lastName",
                "otherExecutive_email", "sameAsPhysicalAddress", "boardOfDirectors", "committees", "society_state", "preferred_time_zone");
            $postRegArray = array();
            $temp_claim_admin_data = array();
            $count = 1;
            //echo '<pre>';
            //print_r($apiResponse);
            if ($temp_data_count && $flg == 'Y') {
                $fieldsArray_count = count($fieldsArray);
                $cc = 1;
                for ($f = 0; $f < $fieldsArray_count; $f++) {
                    $temp_claim_admin_data['propname_' . $cc] = $fieldsArray[$f];
                    $temp_claim_admin_data['propval_' . $cc] = (string) $apiResponse['info']['records'][$fieldsArray[$f]];
                    $cc++;
                }
                $temp_claim_admin_data['propname_' . $cc] = 'archive_user_id';
                $temp_claim_admin_data['propval_' . $cc] = $temp_user_created_by_admin;
                //$cc++;
                //$temp_claim_admin_data['propname_' . $cc] = 'item_title_by_admin';
                //$temp_claim_admin_data['propval_' . $cc] = $temp_society_created_by_admin;

                for ($x = 0; $x < $temp_data_count; $x++) {
                    $key = $temp_data[$x]['name'];
                    $value = $temp_data[$x]['value'];
					if($key == 'society_name'){
						$society_name_new = $value;
						$uRequestApiData['_key'] = APIKEY;
						$uRequestApiData['_session'] = $sessionKey;
						$uRequestApiData['_user'] = 1;
						$uRequestApiData['_op'] = 'modify_item';
						$uRequestApiData['obj_id'] = $item_id;
						$uRequestApiData['item_title'] = $value;
						$uFile_name = 'browse';
						$UapiResponse = aibServiceRequest($uRequestApiData, $uFile_name);
					}
                    if (in_array($key, $fieldsArray)) {
                        $postRegArray['propname_' . $count] = $key;
                        $postRegArray['propval_' . $count] = (string) $value;
                        $count++;
                    }
                }
            } else {
                $temp_claim_admin_data_arr = json_decode($temp_claim_admin_data_prop, true);
                $temp_claim_admin_data_arr_count = count($temp_claim_admin_data_arr);
                if (is_array($temp_claim_admin_data_arr) && $temp_claim_admin_data_arr_count > 0) {
                    for ($x = 0; $x < $temp_claim_admin_data_arr_count; $x++) {
                        $postRegArray['propname_' . $count] = $temp_claim_admin_data_arr['propname_' . $count];
                        $postRegArray['propval_' . $count] = (string) $temp_claim_admin_data_arr['propval_' . $count];
                        $count++;
                    }
					//$org_soc_name_by_admin = (string) $temp_claim_admin_data_arr['propval_' . $count];
					$uRequestApiData['_key'] = APIKEY;
					$uRequestApiData['_session'] = $sessionKey;
					$uRequestApiData['_user'] = 1;
					$uRequestApiData['_op'] = 'modify_item';
					$uRequestApiData['obj_id'] = $item_id;
					$uRequestApiData['item_title'] = $temp_society_created_by_admin;
					$uFile_name = 'browse';
					$UapiResponse = aibServiceRequest($uRequestApiData, $uFile_name);
                }
            }
            //print_r($temp_claim_admin_data);
            //print_r($postRegArray);
            //exit;

            if ($item_id) {
                //$archiveData = getItemData($value['user_top_folder']);
                //$unclaimed_society = $archiveData['properties']['unclaimed_society'];
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $item_id,
                    'propname_' . $count => 'society_for_claim',
                    'propval_' . $count => ($flg == 'N') ? '1' : '0',
                );
                $apiRequestDataItem = array_merge($apiRequestDataItem, $postRegArray);
                $resp = aibServiceRequest($apiRequestDataItem, 'browse');

                /* UPDATE THE USER CREATED BY ADMIN AT THE TIME OF SOCITY CREATION */
                $api = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'opt_long' => 'Y',
                    'obj_id' => $item_id,
                    'propname_1' => 'archive_user_id',
                    'propval_1' => $temp_user_created_by_admin,
                );
                $apiresp = aibServiceRequest($api, 'browse');
                /* END */
            }

            $status = $flg;
            // Set user profile status (a/d)            
            $active_user = setUserProfileClaimedStatus($id, $status);
            // Get user all property           
            $user_property = getUsersAllProperty($id);
            if ($user_property) {
                if ($status == 'Y') {
                    /* $apiRequestData = array(
                      "_key" => APIKEY,
                      "_session" => $sessionKey,
                      "_op" => 'delete_profile',
                      "_user" => 1,
                      "user_id" => $temp_user_created_by_admin
                      );
                      $apiResponse = aibServiceRequest($apiRequestData, 'users'); */

                    $apiRequestDataItem = array(
                        '_key' => APIKEY,
                        '_user' => 1,
                        '_op' => 'set_item_prop',
                        '_session' => $sessionKey,
                        'opt_long' => 'Y',
                        'obj_id' => $item_id,
                        'propname_1' => 'archive_user_id',
                        'propval_1' => $id,
                        'propname_2' => 'temp_claim_admin_data',
                        'propval_2' => json_encode($temp_claim_admin_data),
                    );
                    $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');

                    $email_template = file_get_contents(EMAIL_PATH . "/claimed_registration_approval.html");
                    $email_template = str_replace('#username#', $username, $email_template);
                    $email_template = str_replace('#confirm_email#', $user_property['email'], $email_template);
                    $email_data = [];
                    $email_data['to'] = $user_property['email'];
                    $email_data['from'] = ADMIN_EMAIL;
                    $email_data['reply'] = ADMIN_EMAIL;
                    $email_data['subject'] = 'ArchiveInABox: User Registration Approved';
                    $email = sendMail($email_data, $email_template);
                }
                $responseArray = ['status' => 'success', 'message' => 'success', 'society_name_new' => $society_name_new, 'society_name_old' => $society_name_old];
            } else {
                $responseArray = ['status' => 'error', 'message' => 'error'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'search_data_by_tags':
        $searched_tags = (isset($_POST['search_tags']) && $_POST['search_tags'] != '') ? $_POST['search_tags'] : '';
        $current_item_id = !empty($_POST['current_item_id']) ? $_POST['current_item_id'] : '';
        $previousYouWere = getTreeData($current_item_id);
        unset($previousYouWere[0]);
        $searchedTagsArray = [];
        if ($searched_tags != '') {
            $searchedTags = explode(',', $searched_tags);
            if (!empty($searchedTags)) {
                $loopCount = 0;
                foreach ($searchedTags as $tag) {
                    $boolean = 'OR';
                    if ($loopCount > 0) {
                        $boolean = 'AND';
                    }
                    $searchedTagsArray[$loopCount]['boolean'] = $boolean;
                    $searchedTagsArray[$loopCount]['method'] = 'EXACT';
                    $searchedTagsArray[$loopCount]['value'] = $tag;
                    $loopCount ++;
                }
            }
        }
        $finalDataArray = [];
        if ($searched_tags != '') {
            // Request array to find tags            
            $postDataItem = array(
                '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'tags_find_boolean',
                '_session' => $sessionKey,
                'tag_spec' => json_encode($searchedTagsArray) //json_encode($searchedTagsArray)
            );
            // Service request to find tags 
            $apiResponse = aibServiceRequest($postDataItem, 'tags');
            if ($apiResponse['status'] == 'OK') {
                if (!empty($apiResponse['info']['records'])) {
                    // Used for each loop over api response & check condition & get item data     
                    $visitedItem = json_decode($_COOKIE['visited_items']);
                    foreach ($apiResponse['info']['records'] as $key => $filterDataArray) {
                        // Get item data 
                        $itemDetails = getItemData($key);
                        $parents = getTreeData($key);
                        unset($parents[0]);
                        array_pop($parents);
                        $itemDetails['item_location'] = $parents;
                        if (in_array($key, $visitedItem)) {
                            $itemDetails['link_visited'] = 'yes';
                        }
                        if ($itemDetails['item_type'] == 'RE') {
                            $finalDataArray[] = $itemDetails;
                        }
                        // Check item type IT                        
                        if ($itemDetails['item_type'] == 'IT') {
                            // Get item tree data                            
                            $itemParents = getTreeData($itemDetails['item_id']);
                            $itemDetails['item_parent'] = $itemParents[count($itemParents) - 2]['item_id'];
                            // Used foreach loop over $itemDetails["files"] & check conditions                             
                            foreach ($itemDetails["files"] as $FileRecord) {
                                // Check $FileRecord["file_type"] == 'tn'                                
                                if ($FileRecord["file_type"] == 'tn') {
                                    $itemDetails['tn_file_id'] = $FileRecord["file_id"];
                                    continue;
                                }
                                // Check $FileRecord["file_type"] == 'pr'                                 
                                if ($FileRecord["file_type"] == 'pr') {
                                    $itemDetails['pr_file_id'] = $FileRecord["file_id"];
                                    continue;
                                }
                            }
                            $finalDataArray[] = $itemDetails;
                        }
                    }
                }
            }
        }
        // search_result_tags_data.php file has been included here to show tags search data        
        include_once TEMPLATE_PATH . 'search_result_tags_data.php';
        break;
    case 'share_item':
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            unset($_POST['username']);
        }
        if (isset($_POST['share_type']) && $_POST['share_type'] == 'record')
            $data_type_cond = 'record';
        $responseArray = array('status' => 'error', 'message' => 'You are Robot!');
        // Current date & time (timestamp)       
        $current_time = time();
        $time_diff = $current_time - $_REQUEST['timestamp_value'];
        if ($time_diff > TIMESTAMP_4) {
            $sessionId = $_SESSION['aib']['user_data']['user_id'];
            // Get record list            
            $apiListResponse = getItemListRecord($_SESSION['aib']['user_data']['user_top_folder']);
            // Used foreach loop over api response to check $shareData[$record['item_id']] = $record['item_title']              
            foreach ($apiListResponse['info']['records'] as $record) {
                $shareData[$record['item_id']] = $record['item_title'];
            }
            $anony_title = "shared out of system";
            if (in_array($anony_title, $shareData)) {
                $outOfsytemId = array_search($anony_title, $shareData);
            } else {
                // Request array to create item                
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'create_item',
                    '_session' => $sessionKey,
                    'parent' => $_SESSION['aib']['user_data']['user_top_folder'],
                    'item_title' => $anony_title,
                    'item_type' => 'sg'
                );
                // Service request to create item
                $apiItemResponse = aibServiceRequest($postDataItem, 'browse');
                $outOfsytemId = $apiItemResponse['info'];

                $propertyVisible = ['_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $outOfsytemId,
                    'propname_1' => 'aib:visible',
                    'propval_1' => 'N'
                ];
                $apiVisibleResponse = aibServiceRequest($propertyVisible, 'browse');
            }

            ///////////////////////////////////

            $shared_user_list = [];
            if (isset($_POST['username']) && !empty($_POST['username'])) {
                $shared_user_list = $_REQUEST['username'];
                if (isset($shared_user_list) && !empty($shared_user_list)) {
                    $emails = emailList($shared_user_list);
                }
                // Convert array to string                  
                $to = implode(",", $emails);
            } else {
                if ($_POST['email']) {
                    // Check email exist                     
                    $username = checkEmailExist($_POST['email']);
                    if ($username != '') {
                        $shared_user_list = $username;
                    }
                    $to = $_POST['email'];
                    $apiResponse['status'] = 'OK';
                }
            }
            if (!empty($shared_user_list) && !empty($_POST['item_id'])) {
                // Share record with user             
                sharedRecordWithUser($shared_user_list, $_POST['item_id']);
                $shared_user = json_encode($shared_user_list);
                if ($data_type_cond) {
                    $obj_id = $_POST['item_id'];
                } else {
                    $obj_id = $_POST['folder_id'];
                }
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $_POST['item_id'],
                    'opt_long' => 'Y',
                    'propname_1' => 'share_user',
                    'propval_1' => $shared_user,
                    'propname_2' => 'share_message',
                    'propval_2' => $_POST['share_massage'],
                    'propname_3' => 'share_created_date',
                    'propval_3' => time()
                );
                $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
            } else {
                // Get Item data                 
                $titleResponse = getItemData($_POST['new_fol_id']);
                $title_record = $titleResponse['item_title'];
                if (isset($_POST['share_url_link'])) {
                    $checkItemId = $_POST['folder_id'];
                } else {
                    $checkItemId = $_POST['item_id'];
                }
                $itemDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'create_item',
                    '_session' => $sessionKey,
                    'parent' => $outOfsytemId,
                    'item_title' => $title_record,
                    'item_type' => 'rec'
                );
                $itemResponse = aibServiceRequest($itemDataItem, 'browse');
                $propertyArray = [ 'share_email' => $_POST['email'],
                    'share_user' => $_POST['sharing_name'],
                    'share_thoumb_id' => $_POST['thoumb_id'],
                    'item_created_date' => $titleResponse['item_create_stamp'],
                    'share_created_date' => time(),
                    'share_title' => $title_record,
                    'share_type' => $_POST['share_type'],
                    'share_item_id' => $_POST['item_id'],
                    'share_message' => $_POST['share_massage']
                ];
                $k = 1;
                // Used foreach loop over $propertyArray to get propert name & property value for each array element                 
                foreach ($propertyArray as $proKey => $proValue) {
                    $aditionalProp['propname_' . $k] = $proKey;
                    $aditionalProp['propval_' . $k] = $proValue;
                    $k++;
                }
                if (isset($_POST['share_url_link'])) {
                    $aditionalProp['propname_9'] = 'share_url_link';
                    $aditionalProp['propval_9'] = $_POST['folder_id'];
                }

                $aditionalProp['_key'] = APIKEY;
                $aditionalProp['_user'] = 1;
                $aditionalProp['_op'] = 'set_item_prop';
                $aditionalProp['_session'] = $sessionKey;
                $aditionalProp['obj_id'] = $itemResponse['info'];
                $apiResponse = aibServiceRequest($aditionalProp, 'browse');
            }

            if ($apiResponse['status'] == 'OK') {
                if ($data_type_cond) {
                    $obj_id = $_POST['item_id'];
                } else {
                    $obj_id = $_POST['folder_id'];
                }
                // Get item tree data                
                $itemTopParents = getTreeData($obj_id);
                $email_data = [];
                $email_data['to'] = $to;
                $email_data['from'] = ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                // Get html content of share.html file                
                $email_template = file_get_contents(EMAIL_PATH . "/share.html");
                $name = (isset($itemTopParents[1]['item_title']) && $itemTopParents[1]['item_title'] != '') ? $itemTopParents[1]['item_title'] : 'ArchiveInABox';
                $date = date("F j, Y");
                $email_template = str_replace('#name#', $name, $email_template);
                $email_template = str_replace('#date#', $date, $email_template);
                $email_template = str_replace('#archive_logo#', '', $email_template);
                if ($data_type_cond) {
                    $main_page = 'home.html';
                    $itemTopParents = getTreeData($_POST['folder_id']);
                    if ($itemTopParents[0]['item_title'] == '_STDUSER') {
                        $main_page = 'people.html';
                    }
                    $email_data['subject'] = 'ArchiveInABox: ' . $_POST['sharing_name'] . ' has shared an archive record';
                    //if(isset($_POST['user_type']) && !empty($_POST['user_type'] && $_POST['user_type']=='public' ) ){$main_page = 'people.php';}
                    $url = '<a style="word-break:break-all;" href="' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1') . '" target="_blank">' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1') . '</a>';
                    if (isset($_POST['search_text']) && $_POST['search_text'] != '') {
                        $url = '<a style="word-break:break-all;" href="' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '" target="_blank">' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '</a>';
                    }
                    $img = '<img style="width:100%;" src="' . $_POST['thoumb_id'] . '&download=1 " alt="Image" />';
                } else {
                    $email_data['subject'] = 'ArchiveInABox: ' . $_POST['sharing_name'] . ' has shared an archive item';
                    $url = '<a style="word-break:break-all;" href="' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1') . '" target="_blank">' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1') . '</a>';
                    if (isset($_POST['search_text']) && $_POST['search_text'] != '') {
                        $url = '<a style="word-break:break-all;" href="' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '" target="_blank">' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '</a>';
                    }
                    $img = '<img style="width:100%;" src="' . THUMB_URL . '?id=' . $_REQUEST['thoumb_id'] . '&download=1" alt="Image" />';
                }
                $email_template = str_replace('#url_details#', $url, $email_template);
                $email_template = str_replace('#item_images#', $img, $email_template);
                $massage_display = '';
                $massage = '';
                if (!empty($_POST['share_massage'])) {
                    $massage_display = 'Message:';
                    $massage = $_POST['share_massage'];
                }
                $email_template = str_replace('#share_massage#', $massage, $email_template);
                $email_template = str_replace('#massage_display#', $massage_display, $email_template);
                // Send email to user                 
                $email = sendMail($email_data, $email_template);
                if ($email) {
                    $responseArray = ['status' => 'success', 'message' => 'Item shared successfully.'];
                } else {
                    $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
                }
            } else {
                $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
            }
        }
        print json_encode($responseArray);
        break;
    case'share_get_records_prop':
        $item_id = $_POST['id'];
        // Request array to get item property            
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => isset($_SESSION['aib']['user_data']['user_id']) ? $_SESSION['aib']['user_data']['user_id'] : 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        // Service request to get item property 
        $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
        $responseData = $apiResponseProps['info']['records']['share_user'];
        print json_encode($responseData);
        break;
    case 'get_public_user_email':
        // Request array to get list of matching user profiles          
        $apiGetData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'list_profiles',
            "_user" => 1,
            "user_type" => 'U',
            "opt_include_sub" => 'Y'
        );
        // Service request to get list of matching user profiles
        $apiResponse = aibServiceRequest($apiGetData, 'users');
        $responseData = $apiResponse['info']['records'];
        $userListWithArchiveGroup = Array();
        // Used foreach loop apply over api response to get item details            
        foreach ($responseData as $key => $value) {
            // Get item data                 
            $archiveData = getItemData($value['user_top_folder']);
            $value['item_title'] = $archiveData['item_title'];
            $userListWithArchiveGroup[] = $value;
        }
        print json_encode($userListWithArchiveGroup);
        break;
    case 'get_related_items':
        $record_id = $_POST['record_id'];
        $parentDetails = getTreeData($record_id);
        $themeName = isset($parentDetails[1]['properties']['details_page_design']) ? $parentDetails[1]['properties']['details_page_design'] : '';
        // Get item data        
        $itemDetails = getItemData($record_id);
        $item_user_id = $itemDetails['item_user_id'];
        // Get user profile by id        
        $userDetails = getUserProfileById($item_user_id);
        $relatedItemsData = [];
        // Request array to get all shared list         
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'item_share',
            "user_id" => -1,
            "item_id" => $record_id,
            "opt_get_property" => 'Y'
        );
        // Service request to get all shared list         
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        if ($apiResponse['status'] == 'OK') {
            // Used foreach loop over api response to get item details & 1st thumb id of each array element            
            foreach ($apiResponse['info']['records'] as $relatedDataArray) {
                if ($relatedDataArray['properties']['link_class'] == 'related_content') {
                    if ($relatedDataArray['item_ref'] == $record_id) {
                        // Get item data                         
//                        $dataArray=getItemData($relatedDataArray['item_parent']);
                        $dataArray = $relatedDataArray["parent_info"];
                        // Get item tree data                         
                        $itemTopParents = getTreeData($relatedDataArray['item_parent']);
                        $dataArray['top_parent'] = $itemTopParents[0]['item_title'];
                        // Get sub-group 1st thumb id  
                        if (in_array($dataArray['item_type'], array('SG', 'IT'))) {
                            $dataArray['thumbId'] = getSubGroupFirstItemThumb($relatedDataArray['item_parent']);
                        } elseif ($dataArray['item_type'] == 'RE') {
                            $dataArray['thumbId'] = $dataArray['item_id'];
                        } else {
                            $dataArray['ag_thumb'] = isset($itemTopParents[1]['properties']['archive_group_thumb']) ? $itemTopParents[1]['properties']['archive_group_thumb'] : '';
                        }
                        $relatedItemsData[] = $dataArray;
                    }
                }
            }
        }
        //$parentRelatedItems = getAllParentsRelatedItems($record_id);
        //$relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        // related_items.php file has been included here to show related items       
        include_once TEMPLATE_PATH . 'related_items.php';
        break;

    case 'get_historical_connection':
        $record_id = $_POST['record_id'];
        // Get item data        
        $relatedItemsData = getHistoricalConnectionLists($record_id);
        $parentRelatedItems = getAllParentsRelatedItems($record_id);
        if (!empty($parentRelatedItems)) {
            $relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        }
        $newFillteredArray = [];
        foreach ($relatedItemsData as $itemData) {
            $newFillteredArray[$itemData['item_id']] = $itemData;
        }
        $relatedItemsData = $newFillteredArray;
        // related_items.php file has been included here to show related items       
        include_once TEMPLATE_PATH . 'historical_connection.php';
        break;
    case 'get_historical_connection_item':
        $record_id = $_POST['folder_id'];
        $relatedItemsData = getHistoricalConnectionLists($record_id);
        $parentRelatedItems = getAllParentsRelatedItems($record_id);
        if (!empty($parentRelatedItems)) {
            $relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        }
        $newFillteredArray = [];
        foreach ($relatedItemsData as $itemData) {
            $_parent_id = getParentOfTreeData($itemData['item_id']);
            $itemDetails = getItemDetailsWithProp($_parent_id[1]['item_id']);
            $newFillteredArray[$itemData['item_id']] = $itemData;
            $newFillteredArray[$itemData['item_id']]['properties'] = $itemDetails['properties'];
        }
        $relatedItemsData = $newFillteredArray;
        // related_items.php file has been included here to show related items       
        include_once TEMPLATE_PATH . 'historical_connection_item.php';
        break;
    case 'get_historical_connection_item_details':
        $record_id = $_POST['folder_id'];
        $relatedItemsData = getHistoricalConnectionLists($record_id);
        $parentRelatedItems = getAllParentsRelatedItems($record_id);
        if (!empty($parentRelatedItems)) {
            $relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        }
        $newFillteredArray = [];
        foreach ($relatedItemsData as $itemData) {
            $newFillteredArray[$itemData['item_id']] = $itemData;
        }
        $relatedItemsData = $newFillteredArray;
        // related_items.php file has been included here to show related items       
        include_once TEMPLATE_PATH . 'historical_connection_item_details.php';
        break;
    case 'get_historical_connection_item_details_top':
        $record_id = $_POST['folder_id'];
        $relatedItemsData = getHistoricalConnectionLists($record_id);
        $parentRelatedItems = getAllParentsRelatedItems($record_id);
        if (!empty($parentRelatedItems)) {
            $relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        }
        $newFillteredArray = [];
        foreach ($relatedItemsData as $itemData) {
            $newFillteredArray[$itemData['item_id']] = $itemData;
        }
        $relatedItemsData = $newFillteredArray;
        // related_items.php file has been included here to show related items       
        include_once TEMPLATE_PATH . 'historical_connection_item_details_top.php';
        break;
    case 'list_historical_connections':
        $record_id = $_POST['folder_id'];
        $relatedItemsData = getHistoricalConnectionLists($record_id);
        $parentRelatedItems = getAllParentsRelatedItems($record_id);
        if (!empty($parentRelatedItems)) {
            $relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        }
        $newFillteredArray = [];
        foreach ($relatedItemsData as $itemData) {
            $_parent_id = getParentOfTreeData($itemData['item_id']);
            $itemDetails = getItemDetailsWithProp($_parent_id[1]['item_id']);
            $newFillteredArray[$itemData['item_id']] = $itemData;
            $newFillteredArray[$itemData['item_id']]['_parent_id'] = $_parent_id;
            $newFillteredArray[$itemData['item_id']]['properties'] = $itemDetails['properties'];
        }
        $relatedItemsData = $newFillteredArray;
        // related_items.php file has been included here to show related items       
        include_once TEMPLATE_PATH . 'list_historical_connections.php';
        break;
    case 'get_public_connections':
        $record_id = $_POST['record_id'];
        $parentDetails = getTreeData($record_id);
        $themeName = isset($parentDetails[1]['properties']['details_page_design']) ? $parentDetails[1]['properties']['details_page_design'] : '';
        // Get item data        
        $itemDetails = getItemData($record_id);
        $item_user_id = $itemDetails['item_user_id'];
        $publicConnectionDataArray = array();
        /*         * Public Connection Call* */
        // Request array to get item list of ftree parent item         
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $record_id,
            "opt_get_property" => 'Y'
        );

        // Set up item and user profile cache

        $LocalUserProfileCache = array();
        $LocalItemCache = array();
        $publicConnectioData = array();
        // Service request to get item list of ftree parent item    
        $publicConnectionApiResponse = aibServiceRequest($postData, 'browse');
        if ($publicConnectionApiResponse['status'] == 'OK') {
            // Used foreach loop over api response get item property & get user profile                
            foreach ($publicConnectionApiResponse['info']['records'] as $publicConnection) {
                if (isset($publicConnection['properties']['link_class']) and $publicConnection['properties']['link_class'] == 'public') {
                    // Request array to get item details of ftree item
                    $LocalItem = $publicConnection["properties"]["public_folder"];
                    if (isset($LocalItemCache[$LocalItem]) == false) {
                        $postData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "get",
                            "obj_id" => $publicConnection['properties']['public_folder'],
                            "opt_get_property" => "Y"
                        );
                        // Service request to get item details of ftree item
                        $userProfileApiResponse = aibServiceRequest($postData, 'browse');
                        $LocalItemCache[$LocalItem] = $userProfileApiResponse;
                    } else {
                        $userProfileApiResponse = $LocalItemCache[$LocalItem];
                    }

                    if ($userProfileApiResponse['status'] == 'OK' and $userProfileApiResponse['info']['records'][0]['properties']['archive_group_thumb']) {
                        $publicConnection['archive_group_thumb'] = $userProfileApiResponse['info']['records'][0]['properties']['archive_group_thumb'];
                    }

                    $LocalUser = $publicConnection["properties"]["owner"];
                    if (isset($LocalUserProfileCache[$LocalUser]) == false) {
                        // Request array to get user profile by user id 
                        $postData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "get_profile",
                            "user_id" => $publicConnection['properties']['owner'],
                        );
                        // Service request to get user profile by user id 
                        $publicConnectionDataArray[] = $publicConnection['properties']['owner'];
                        $userProfileApiResponse1 = aibServiceRequest($postData, 'users');
                        $LocalUserProfileCache[$LocalUser] = $userProfileApiResponse;
                    } else {
                        $userProfileApiResponse = $LocalUserProfileCache[$LocalUser];
                    }

                    $publicConnectionDataArray[] = $publicConnection['properties']['owner'];
                    if ($userProfileApiResponse1['status'] == 'OK' and $userProfileApiResponse1['info']['user_login']) {
                        $publicConnection['user_title'] = $userProfileApiResponse1['info']['user_login'];
                    }
                    $publicConnectioData[] = $publicConnection;
                }
            }
        }
        $_SESSION['aib']['publicConnectioData'] = $publicConnectionDataArray;
        // public_connections.php file has been included here to show public connection of item                  
        include_once TEMPLATE_PATH . 'public_connections.php';
        break;
    case 'add_record_to_scrapbook':
        if (isset($_SESSION['aib']['user_data']['user_id'])) {
            if ($_POST['scrap_name'] != '') {
                // Request array to create new scrapbook for a user                
                $postItemData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "scrpbk_new",
                    "user_id" => $_SESSION['aib']['user_data']['user_id'],
                    "title" => $_POST['scrap_name']
                );
                // Service request to create new scrapbook for a user   
                $apiResponseData = aibServiceRequest($postItemData, 'scrapbook');
                $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
                if ($apiResponseData['status'] == 'OK') {
                    // Request array to set item property                     
                    $apiRequestDataItem = array(
                        '_key' => APIKEY,
                        '_user' => $_SESSION['aib']['user_data']['user_id'],
                        '_op' => 'set_item_prop',
                        '_session' => $sessionKey,
                        'obj_id' => $apiResponseData['info'],
                        'propname_1' => 'scrapbook_type',
                        'propval_1' => 'public',
                        'propname_2' => 'aibftype',
                        'propval_2' => 'sg'
                    );
                    // Service request to set item property   
                    $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
                    $apiResponseData['info'];
                }
            }
            $record_id = !empty($_POST['record_id']) ? $_POST['record_id'] : "";
            $entry_title = !empty($_POST['entry_title']) ? $_POST['entry_title'] : "";
            $scrapbook_id = !empty($_POST['scrap_name']) ? $apiResponseData['info'] : $_POST['scrapbook_id'];
            $record_parent = !empty($_POST['record_parent']) ? $_POST['record_parent'] : "";
            $page_type = !empty($_POST['usert_type']) ? $_POST['usert_type'] : "society";
            // Request array to add new scrapbook entry for a user                        
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "scrpbk_addent",
                "obj_id" => $scrapbook_id,
                "title" => $entry_title,
                "target" => $record_id
            );
            // Service request to add new scrapbook entry for a user
            $apiResponse = aibServiceRequest($postRequestData, 'scrapbook');
            $responseArray = ['status' => 'error', 'message' => 'Something went wrong, Please try again'];
            if ($apiResponse['status'] == 'OK') {
                // Request array to set item property                             
                $apiRequestDataRecord = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $apiResponse['info'],
                    'propname_1' => 'record_parent',
                    'propval_1' => $record_parent,
                    'propname_2' => 'rediect_page',
                    'propval_2' => $page_type
                );
                // Service request to set item property    
                $apiResponse = aibServiceRequest($apiRequestDataRecord, 'browse');
                if ($apiResponse['status'] == 'OK') {
                    $responseArray = ['status' => 'success', 'message' => 'Record added to scrapbook successfully.'];
                }
            }
        } else {
            $responseArray = ['status' => 'login', 'message' => 'You must login to add an item to scrapbook'];
        }
        print json_encode($responseArray);
        break;
    case 'get_parent_id':
        $id = $_REQUEST['folder_id'];
        // Get item tree data of ftree item        
        $parentId = getTreeData($id);
        $parent_id = $parentId[count($parentId) - 2]['item_id']; //$parentId[4]['item_id']
        print json_encode($parent_id);
        break;
    case'user_change_email':
        $user_id = $_POST['user_id'];
        $current_email = $_POST['current_email'];
        $old_email = $_POST['old_email'];
        if (trim($old_email) == 'undo') {
            $old_email = TESTEMAIL;
        }
        $userProperty[0]['name'] = 'email';
        $userProperty[0]['value'] = $current_email;
        $userProperty[1]['name'] = 'old_email';
        $userProperty[1]['value'] = $old_email;
        // Request array to set user profile property            
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "set_profile_prop_batch",
            "user_id" => $user_id,
            "property_list" => json_encode($userProperty)
        );
        // Service request to set user profile property 
        $apiResponseProfile = aibServiceRequest($postRequestData, 'users');
        if ($apiResponseProfile['status'] == 'OK') {
            $email_data = [];
            // get html content of email_change.html file                 
            $email_template = file_get_contents(EMAIL_PATH . "/email_change.html");
            $email_data['to'] = $old_email;
            $email_data['from'] = ADMIN_EMAIL;
            $email_data['reply'] = ADMIN_EMAIL;
            $email_data['subject'] = 'ArchiveInABox: Change Email-Id';
            $email_template = str_replace('#username#', $postData['register_username1'], $email_template);
            $url = 'id=' . $user_id . '&flg=undo_email&' . $old_email . '&undo';
            $link = '<a href="' . HOST_PATH . 'thank-you.html?' . urlencode($url) . '" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to Undo</a>';

            $email_template = str_replace('#confirm_email#', $link, $email_template);
            $message = 'Your email id have changed successfully. your new email_id : ' . $current_email . '. If you want to undo your email-id please click on undo link';
            $email_template = str_replace('#message#', $message, $email_template);
            // Send email to user regarding to change email id                
            $email = sendMail($email_data, $email_template);
            if ($email) {
                $responseData = array('status' => 'success', 'message' => 'change email successfully');
            }
        }
        print json_encode($responseData);
        break;
    case 'user_welcome_message':
        // Get user all property            
        $apiResponse = getUsersAllProperty($_POST['user_id']);
        // Get user profile            
        $userTopFolderId = getUserProfile($_POST['user_id']);
        // Get item data            
        $titleName = getItemData($userTopFolderId['user_top_folder']);
        if (isset($userTopFolderId['user_type']) && $userTopFolderId['user_type'] == 'A') {
            $email_data1 = [];
            $email_data1['to'] = BUSINESS_EMAIL;
            $email_data1['from'] = ADMIN_EMAIL;
            $email_data1['reply'] = ADMIN_EMAIL;
            $email_data1['subject'] = 'ArchiveInABox: New Society Registered';
            // Get html file content of archive_confirm.html                   
            $email_template1 = file_get_contents(EMAIL_PATH . "/archive_confirm.html");
            $contact_name_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'first-name.png" alt="" />';
            $contact_email_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'email.png" alt="" />';
            $phonenumber_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="" />';
            $city_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'city.png" alt="ArchiveInABox Logo" />';
            $state_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'state.png" alt="ArchiveInABox Logo" />';
            $society_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'society.png" alt="ArchiveInABox Logo" />';

            $email_template1 = str_replace('#society_image#', $society_image, $email_template1);
            $email_template1 = str_replace('#society_username_image#', $contact_name_image, $email_template1);
            $email_template1 = str_replace('#name_image#', $contact_name_image, $email_template1);
            $email_template1 = str_replace('#email_image#', $contact_email_image, $email_template1);
            $email_template1 = str_replace('#city_image#', $city_image, $email_template1);
            $email_template1 = str_replace('#state_image#', $state_image, $email_template1);
            $email_template1 = str_replace('#phone_image#', $phonenumber_image, $email_template1);
            $requestData1 = 'The Primary account for a historical society has been validated.Please find the details below';
            $email_template1 = str_replace('#request#', $requestData1, $email_template1);
            $email_template1 = str_replace('#society_name#', $titleName['item_title'], $email_template1);
            $email_template1 = str_replace('#society_username#', $userTopFolderId['user_login'], $email_template1);
            $email_template1 = str_replace('#contact_name#', $userTopFolderId['user_title'], $email_template1);
            $email_template1 = str_replace('#contact_email#', $apiResponse['email'], $email_template1);
            $email_template1 = str_replace('#city_name#', $titleName['properties']['physicalCity'], $email_template1);
            $email_template1 = str_replace('#state_number#', $titleName['properties']['physicalState'], $email_template1);
            $email_template1 = str_replace('#phone_number#', $titleName['properties']['phoneNumber'], $email_template1);
        }
        $username = $titleName['item_title'];

        if ($apiResponse['email'] != '') {
            if ($_POST['type'] == 'U') {
                $email_template = file_get_contents(EMAIL_PATH . "/welcome_message.html");
            } elseif ($_POST['type'] == 'S') {
                $username = $userTopFolderId['user_login'];
                // Get html file content of welcome_assistant.html
                $email_template = file_get_contents(EMAIL_PATH . "/welcome_assistant.html");
            } else {
                // Get html file content of welcome_society.html   
                $email_template = file_get_contents(EMAIL_PATH . "/welcome_society.html");
            }
            $email_data = [];
            $email_data['to'] = $apiResponse['email'];
            $email_data['from'] = ADMIN_EMAIL;
            $email_data['reply'] = ADMIN_EMAIL;
            $email_data['subject'] = 'ArchiveInABox: Welcome ' . $username;
            $email_template = str_replace('#username#', $username, $email_template);
            // Send welcome email to user                     
            $email = sendMail($email_data, $email_template);
            // Send email admin with user details                    
            $email1 = sendMail($email_data1, $email_template1);
            if ($email) {
                $responseData = array('status' => 'success', 'message' => 'change email successfully');
            }
        }
        break;
    case 'link_user_public_connection':
        if (isset($_SESSION['aib']['user_data']['user_id'])) {
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            if (!in_array($user_id, $_SESSION['aib']['publicConnectioData'])) {
                $item_id = $_REQUEST['item_id'];
                $user_top_folder = $_SESSION['aib']['user_data']['user_top_folder'];
                $user_id = $_SESSION['aib']['user_data']['user_id'];
                $LinkProperties = array(
                    array("name" => "link_class", "value" => "public"),
                    array("name" => "owner", "value" => $user_id),
                    array("name" => "public_folder", "value" => $user_top_folder),
                );
                // Request array to create new share with item                     
                $postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $user_id,
                    "_op" => "share_create",
                    "share_target" => $item_id,
                    "share_source" => $user_top_folder,
                    "share_title" => "Shared $user_top_folder at " . microtime(true),
                    "share_item_user" => $user_id,
                    "share_properties" => json_encode($LinkProperties),
                );
                // Service request to create new share with item 
                $apiResponse = aibServiceRequest($postData, 'sharing');
                $ThumbID = 0;
                if ($apiResponse['status'] == 'OK') {
                    $_SESSION['aib']['publicConnectioData'][] = $user_id;
                    $responseArray = ['status' => 'success', 'message' => 'Public connection has been added successfully.'];
                } else {
                    
                }
            } else {
                $responseArray = ['status' => 'success', 'message' => 'Already have a public connection'];
            }
        } else {
            $responseArray = ['status' => 'login', 'message' => 'You must login to add Public connection'];
        }
        print json_encode($responseArray);
        break;
    case'report_to_user':
        // Parse $_POST['formData'] in to $postData        
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error', 'message' => 'Something went wrong'];
        $urlImage = '<img style="width:100%;" src="' . RECORD_THUMB_URL . '?id=' . $postData['default_url_image_id'] . '" alt="Image" />';
        if (!empty($postData['url_image_id'])) {
            $urlImage = '<img style="width:100%;" src="' . THUMB_URL . '?id=' . $postData['url_image_id'] . '" alt="Image" />';
        }
        // Get item details with all property            
        $image_url = getItemDetailsWithProp($_SESSION['aib']['user_data']['user_top_folder']);
        $rep_image = $image_url['properties']['archive_group_thumb'];
        if (isset($image_url['properties']['archive_group_thumb']) && $rep_image != '') {
            $repUserImage = HOST_PATH . 'admin/tmp/' . $rep_image;
        } else {
            $repUserImage = HOST_ROOT_IMAGE_PATH . 'avatar.png';
        }
        $email_data = [];
        $email_data['to'] = SAPPLE_EMAIL;
        $email_data['from'] = ADMIN_EMAIL;
        $email_data['reply'] = ADMIN_EMAIL;
        $email_data['subject'] = 'ArchiveInABox: Item Report';
        $rep_username = $_SESSION['aib']['user_data']['user_login'];
        $rep_email = $_SESSION['aib']['user_data']['user_prop']['email'];
        $userImage = HOST_PATH . $postData['user_image'];
        $useImage = '<img style="width:100%;" src="' . $userImage . '" alt="Image" />';
        $repoImage = '<img style="width:100%;" src="' . $repUserImage . '" alt="Image" />';
        // Get html file content of report.html             
        $email_template = file_get_contents(EMAIL_PATH . "/report.html");
        $email_template = str_replace('#userimage#', $userImage, $email_template);
        $email_template = str_replace('#username#', $_POST['username'], $email_template);
        $email_template = str_replace('#itemUrl#', $_POST['item_url'], $email_template);
        $email_template = str_replace('#repUsername#', $rep_username, $email_template);
        $email_template = str_replace('#repEmail#', $rep_email, $email_template);
        $email_template = str_replace('#reason#', $postData['report_reason'], $email_template);
        $email_template = str_replace('#repImage#', $repoImage, $email_template);
        $email_template = str_replace('#useImage#', $useImage, $email_template);
        $email_template = str_replace('#URLImage#', $urlImage, $email_template);
        $userProperty = [];
        $userProperty['user_reported'] = $_POST['username'];
        $userProperty['user_reporting'] = $rep_username;
        $userProperty['report_reason'] = $postData['report_reason'];
        $userProperty['report_url'] = $_POST['item_url'];
        $string = $_POST['item_url'];
        // Convert string to array            
        $stringArray = explode('?', $string);
        if (strpos($stringArray[1], '&')) {
            // Convert string to array 
            $subStringArray = explode('&', $stringArray[1]);
            // Convert string to array 
            $itemArray = explode('=', $subStringArray[0]);
            $item_id = $itemArray[1];
        } else {
            // Convert string to array 
            $subStringArray = explode('=', $stringArray[1]);
            $item_id = $subStringArray[1];
        }
        // Get item tree data of ftree item                
        $item_par_id = getTreeData($item_id);
        $searchType = '';
        if (isset($item_par_id[0]['item_title'])) {
            $searchType = 'A';
            if ($item_par_id[0]['item_title'] == '_STDUSER') {
                $searchType = 'P';
            }
        }
        $userProperty['search_type'] = $searchType;
        // Request array to store report request                
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "req_store",
            "req_type" => "RPC",
            "req_name" => "Test request",
            "req_info" => json_encode($userProperty)
        );
        // Service request to store report request
        $result = aibServiceRequest($postData, "custreq");
        if ($result['status'] == 'OK') {
            // Send email to user                     
            $email = sendMail($email_data, $email_template);
            if ($email) {
                $responseArray = ['status' => 'success', 'message' => 'Your report has been submitted successfully'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'share_people_record':
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            unset($_POST['username']);
        }
        $data_type_cond = isset($_POST['share_type']) && $_POST['share_type'] == 'record';
        $responseArray = array('status' => 'error', 'message' => 'You are Robot!');
        // Current date & time        
        $current_time = time();
        $time_diff = $current_time - $_REQUEST['timestamp_value'];
        if ($time_diff > TIMESTAMP_4) {
            $sessionId = $_SESSION['aib']['user_data']['user_id'];
            // get record list            
            $apiListResponse = getItemListRecord($_SESSION['aib']['user_data']['user_top_folder']);
            // Used foreach loop over api response to create new item            
            foreach ($apiListResponse['info']['records'] as $record) {
                $shareData[$record['item_id']] = $record['item_title'];
            }
            $anony_title = "shared out of system";
            if (in_array($anony_title, $shareData)) {
                $outOfsytemId = array_search($anony_title, $shareData);
            } else {
                // Request array to create item                
                $postDataItem = array(
                    '_key' => APIKEY,
                    '_user' => $sessionId,
                    '_op' => 'create_item',
                    '_session' => $sessionKey,
                    'parent' => $_SESSION['aib']['user_data']['user_top_folder'],
                    'item_title' => $anony_title,
                    'item_type' => 'sg'
                );
                // Service request to create item 
                $apiItemResponse = aibServiceRequest($postDataItem, 'browse');
                $outOfsytemId = $apiItemResponse['info'];
                $propertyVisible = ['_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $outOfsytemId,
                    'propname_1' => 'aib:visible',
                    'propval_1' => 'N'
                ];
                $apiVisibleResponse = aibServiceRequest($propertyVisible, 'browse');
            }
            $shared_user_list = [];
            if (isset($_POST['username']) && !empty($_POST['username'])) {
                $shared_user_list = $_REQUEST['username'];
                if (isset($shared_user_list) && !empty($shared_user_list)) {
                    $emails = emailList($shared_user_list);
                }
                // Convert string to array                     
                $to = implode(",", $emails);
            } else {
                if (isset($sessionId) && !empty($sessionId)) {
                    // Check email exist                        
                    $username = checkEmailExist($_POST['email']);
                    if ($username != '') {
                        $shared_user_list[] = $username;
                    }
                }
                $to = $_POST['email'];
                $apiResponse['status'] = 'OK';
            }
            if (!empty($shared_user_list) && !empty($_POST['item_id'])) {
                // Share record with user                 
                sharedRecordWithUser($shared_user_list, $_POST['item_id']);
                $shared_user = json_encode($shared_user_list);
                if ($data_type_cond) {
                    $obj_id = $_POST['item_id'];
                } else {
                    $obj_id = $_POST['folder_id'];
                }
                // Request array to set item property                   
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $obj_id,
                    'opt_long' => 'Y',
                    'propname_1' => 'share_user',
                    'propval_1' => $shared_user,
                    'propname_2' => 'share_message',
                    'propval_2' => $_POST['share_massage'],
                    'propname_3' => 'share_created_date',
                    'propval_3' => time()
                );
                // Service request to set item property
                $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
            } else {
                $title_record = '';
                // Get record list                    
                $titleResponse = getItemListRecord($_POST['new_fol_id']);
                // Used foreach loop over api response to check conditions                    
                foreach ($titleResponse['info']['records'] as $recordVal) {
                    if ($recordVal['item_id'] == $_POST['item_id']) {
                        $title_record = $recordVal['item_title'];
                    }
                }
                // Request array to create item                    
                $itemDataItem = array(
                    '_key' => APIKEY,
                    '_user' => $sessionId,
                    '_op' => 'create_item',
                    '_session' => $sessionKey,
                    'parent' => $outOfsytemId,
                    'item_title' => $title_record,
                    'item_type' => 'rec'
                );
                // Service request to create item  
                $itemResponse = aibServiceRequest($itemDataItem, 'browse');
                // Get item data                     
                $itemCreatedTime = getItemData($_POST['thoumb_id']);
                $propertyArray = [ 'share_email' => $_POST['email'],
                    'share_user' => $_POST['sharing_name'],
                    'share_thoumb_id' => $_POST['thoumb_id'],
                    'item_created_date' => $itemCreatedTime['item_create_stamp'],
                    'share_created_date' => time(),
                    'share_title' => $title_record,
                    'share_type' => $_POST['share_type'],
                    'share_item_id' => $_POST['item_id'],
                    'share_message' => $_POST['share_massage']
                ];
                $k = 1;
                // Used foreach loop over $propertyArray to set property name & property values                     
                foreach ($propertyArray as $proKey => $proValue) {
                    $aditionalProp['propname_' . $k] = $proKey;
                    $aditionalProp['propval_' . $k] = $proValue;
                    $k++;
                }
                $aditionalProp['_key'] = APIKEY;
                $aditionalProp['_user'] = $sessionId;
                $aditionalProp['_op'] = 'set_item_prop';
                $aditionalProp['_session'] = $sessionKey;
                $aditionalProp['obj_id'] = $itemResponse['info'];
                $apiResponse = aibServiceRequest($aditionalProp, 'browse');
            }
            if ($apiResponse['status'] == 'OK') {
                $email_data = [];
                $email_data['to'] = $to;
                $email_data['from'] = ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                // Get html content of share.html file                    
                $email_template = file_get_contents(EMAIL_PATH . "/share.html");
                $name = isset($_SESSION['aib']['user_data']['user_title']) ? $_SESSION['aib']['user_data']['user_title'] : 'ArchiveInABox';
                $date = date("F j, Y");
                $email_template = str_replace('#name#', $name, $email_template);
                $email_template = str_replace('#date#', $date, $email_template);
                $email_template = str_replace('#archive_logo#', '', $email_template);
                if ($data_type_cond) {
                    $email_data['subject'] = 'ArchiveInABox: ' . $_POST['sharing_name'] . ' has shared an archive record';
                    $main_page = 'people.html';
                    if (isset($_POST['user_type']) && !empty($_POST['user_type'] && $_POST['user_type'] == 'public')) {
                        $main_page = 'people.html';
                    }
                    $url = '<a style="word-break:break-all;" href="' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1') . '" target="_blank">' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1') . '</a>';
                    $img = '<img style="width:100%;" src="' . $_POST['thoumb_id'] . '&download=1 " alt="Image" />';
                } else {
                    $email_data['subject'] = 'ArchiveInABox: ' . $_POST['sharing_name'] . ' has shared an archive item';
                    $url = '<a style="word-break:break-all;" href="' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1') . '" target="_blank">' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1') . '</a>';
                    $img = '<img style="width:100%;" src="' . THUMB_URL . '?id=' . $_REQUEST['thoumb_id'] . '&download=1" alt="Image" />';
                }
                $email_template = str_replace('#url_details#', $url, $email_template);
                $email_template = str_replace('#item_images#', $img, $email_template);
                $massage_display = '';
                $massage = '';
                if (!empty($_POST['share_massage'])) {
                    $massage_display = 'Message:';
                    $massage = $_POST['share_massage'];
                }
                $email_template = str_replace('#share_massage#', $massage, $email_template);
                $email_template = str_replace('#massage_display#', $massage_display, $email_template);
                // Send email to user                    
                $email = sendMail($email_data, $email_template);
                if ($email) {
                    $responseArray = ['status' => 'success', 'message' => 'Item shared successfully .'];
                } else {
                    $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
                }
            } else {
                $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
            }
        }
        print json_encode($responseArray);
        break;
    case'update_user_term_condition':
        $user_id = $_POST['user_id'];
        $userProperty = [];
        $userProperty[0]['name'] = 'timestamp';
        $userProperty[0]['value'] = time();
        // Request array to set user profile property       
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $user_id,
            "_op" => "set_profile_prop_batch",
            "user_id" => $user_id,
            "property_list" => json_encode($userProperty)
        );
        // Service request to set user profile property     
        $apiResponseProfile = aibServiceRequest($postRequestData, 'users');
        $responseArray = ['status' => 'error', 'message' => 'Field status not changed.'];
        if ($apiResponseProfile['status'] == 'OK') {
            $_SESSION['aib']['user_data']['terms_condition'] = 'Y';
            $responseArray = ['status' => 'success', 'message' => 'Your term & condition checked updated.'];
        }
        print json_encode($responseArray);
        break;
    case 'submit_trouble_request':
        // Parse $_POST['formData'] in to $postData           
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error', 'message' => 'Missing required field(s).'];
        if ($postData['captcha-response']) {
            $responseArray = _recaptchaSiteVerify($postData['captcha-response']);
            //$responseArray = recaptchaSiteVerify($postData['captcha-response']);
            if ($responseArray['status'] != 'success') {
                print json_encode($responseArray);
                break;
            }
        }
        if (!empty($postData)) {
            $user_ip_address = getenv('HTTP_CLIENT_IP')? : getenv('HTTP_X_FORWARDED_FOR')? : getenv('HTTP_X_FORWARDED')? : getenv('HTTP_FORWARDED_FOR')? : getenv('HTTP_FORWARDED')? : getenv('REMOTE_ADDR');
            $otherInfo = [
                'organization' => $postData['organization'],
                'user_type' => $postData['user_type'],
                'type_of_trouble' => $postData['type_of_trouble'],
                'your_computer' => $postData['your_computer'],
                'browser_type' => $postData['browser_type'],
                'internet_connection' => $postData['internet_connection'],
                'comment' => ($postData['your_message'] != '') ? $postData['your_message'] : ''
            ];
            // Request array to store report request            
            $postDataRequest = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "req_store",
                "req_type" => ($postData['user_type'] == 'Administrator' || $postData['user_type'] == 'Assistant') ? 'STT' : 'TT', //Trouble Ticket
                "req_name" => $postData['name'],
                "req_phone" => ($postData['phone'] != '') ? $postData['phone'] : '',
                "req_email" => $postData['trouble_email'],
                "req_ipaddr" => $user_ip_address,
                "req_info" => json_encode($otherInfo),
                "req_item" => '-1'
            );
            // Service request to store report request
            $apiResponse = aibServiceRequest($postDataRequest, 'custreq');
            $responseArray = ['status' => 'error', 'message' => 'There is some error in your request, Please check and submit again.'];
            if ($apiResponse['status'] == 'OK') {
                $email_data = [];
                $email_data['from'] = ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                $email_data['to'] = BUSINESS_EMAIL;
                $email_data['subject'] = 'Trouble ticket request';
                $email_template = file_get_contents(EMAIL_PATH . "/trouble_ticket.html");

                $email_template = str_replace('#name#', $postData['name'], $email_template);
                $email_template = str_replace('#organization#', $postData['organization'], $email_template);
                $email_template = str_replace('#email#', $postData['trouble_email'], $email_template);
                $email_template = str_replace('#phone#', $postData['phone'], $email_template);
                $email_template = str_replace('#user_type#', $postData['user_type'], $email_template);
                $email_template = str_replace('#trouble_type#', $postData['type_of_trouble'], $email_template);
                $email_template = str_replace('#computer_type#', $postData['your_computer'], $email_template);
                $email_template = str_replace('#browser_type#', $postData['browser_type'], $email_template);
                $email_template = str_replace('#internet_connection#', $postData['internet_connection'], $email_template);
                $email_template = str_replace('#message#', $postData['your_message'], $email_template);

                $email_template = str_replace('#name_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'first-name.png" alt="" />', $email_template);
                $email_template = str_replace('#organization_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'society.png" alt="" />', $email_template);
                $email_template = str_replace('#email_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'email.png" alt="" />', $email_template);
                $email_template = str_replace('#phone_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="" />', $email_template);
                $email_template = str_replace('#user_type_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'first-name.png" alt="" />', $email_template);
                $email_template = str_replace('#trouble_type_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'reason.png" alt="" />', $email_template);
                $email_template = str_replace('#computer_type_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'computer.png" alt="" />', $email_template);
                $email_template = str_replace('#browser_type_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'browser.png" alt="" />', $email_template);
                $email_template = str_replace('#internet_connection_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'internet.png" alt="" />', $email_template);
                $email_template = str_replace('#message_icon#', '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'comment.png" alt="" />', $email_template);
                // Send email archive owner

                /* auto generated email template for request user */
                $responseData = [];
                $responseData['from'] = ADMIN_EMAIL;
                $responseData['reply'] = ADMIN_EMAIL;
                $responseData['to'] = $postData['trouble_email'];
                $responseData['subject'] = 'Trouble ticket response';
                $response_template = file_get_contents(EMAIL_PATH . "/response.html");
                $response_template = str_replace('#response_message#', "Thank you for writing to us, we will get back to you soon.", $response_template);
                $sendEmail = sendMail($responseData, $response_template);
                /** auto generated email template for request user * */
                $sendEmail = sendMail($email_data, $email_template);
                $responseArray = ['status' => 'success', 'message' => 'Your trouble ticket request has been submitted successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'submit_comment_report':
        // Parse $_POST['formData'] in to $postData
        parse_str($_POST['formData'], $postData);
        $responseArray = ['status' => 'error', 'message' => 'Missing required field(s).'];
        if (!empty($postData)) {
            $string = $postData['item_url'];
            //  Convert String to array           
            $stringArray = explode('?', $string);
            if (strpos($stringArray[1], '&')) {
                //  Convert String to array 
                $subStringArray = explode('&', $stringArray[1]);
                //  Convert String to array 
                $itemArray = explode('=', $subStringArray[0]);
                $item_id = $itemArray[1];
            } else {
                //  Convert String to array 
                $subStringArray = explode('=', $stringArray[1]);
                $item_id = $subStringArray[1];
            }
            // Get item tree data of ftree item             
            $itemType = getTreeData($item_id);
            if ($itemType[0]['item_title'] == 'ARCHIVE GROUP') {
                $itemValue = 'S';
            } else if ($itemType[0]['item_title'] == '_STDUSER') {
                $itemValue = 'P';
            }
            $user_ip_address = getenv('HTTP_CLIENT_IP')? : getenv('HTTP_X_FORWARDED_FOR')? : getenv('HTTP_X_FORWARDED')? : getenv('HTTP_FORWARDED_FOR')? : getenv('HTTP_FORWARDED')? : getenv('REMOTE_ADDR');
            $otherInfo = [
                'comment_id' => $postData['comment_id'],
                'reporting_reason_comment' => $postData['reporting_reason_comment'],
                'item_link' => $postData['item_url'],
                'search_type' => $itemValue,
                'item_id' => $itemType[1]['item_id'],
                'comment' => ($postData['rp_message'] != '') ? $postData['rp_message'] : ''
            ];
            // Request array to store comment report             
            $postDataRequest = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "req_store",
                "req_type" => 'ICR', //item comment report
                "req_name" => $postData['rp_first_name'] . ' ' . $postData['rp_last_name'],
                "req_phone" => ($postData['rp_phone'] != '') ? $postData['rp_phone'] : '',
                "req_email" => $postData['rp_email'],
                "req_ipaddr" => $user_ip_address,
                "req_info" => json_encode($otherInfo),
                "req_item" => $itemType[1]['item_id']
            );
            // Service request to store comment report 
            $apiResponse = aibServiceRequest($postDataRequest, 'custreq');
            $responseArray = ['status' => 'error', 'message' => 'There is some error in your request, Please check and submit again.'];
            if ($apiResponse['status'] == 'OK') {

                $email_data = [];
                // Get html file content of emailer.html file                 
                $email_template = file_get_contents(EMAIL_PATH . "/emailer.html");
                $name_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'first-name.png" alt="" />';
                $email_image = '<img style="float:left; margin-right:4px;"  src="' . HOST_ROOT_ICON_PATH . 'email.png" alt="" />';
                $comment_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'comment.png" alt="" />';
                $link_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'link.png" alt="ArchiveInABox Logo" />';
                $email_template = str_replace('#name_image#', $name_image, $email_template);
                $email_template = str_replace('#last_image#', $name_image, $email_template);
                $email_template = str_replace('#email_image#', $email_image, $email_template);
                $email_template = str_replace('#comment_image#', $comment_image, $email_template);
                $requestDataAdmin = 'A comment report has been submitted for the following record :';

                $email_data['from'] = ADMIN_EMAIL;
                $email_data['reply'] = ADMIN_EMAIL;
                $email_data['to'] = BUSINESS_EMAIL;
                $email_data['subject'] = 'Report Comment';
                $phone = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" /><strong>Phone Number</strong></div><div style="width:70%; float:left; font-size:14px; padding:5px;">' . $postData['rp_phone'] . '</div><div style="clear:both;"></div>';
                $sub = 'Article Link';
                $phone_image = '<img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'phone-number.png" alt="ArchiveInABox Logo" />';
                $reason = '<div style="width:30%; float:left; font-size:14px; padding:5px;"><img style="float:left; margin-right:4px;" src="' . HOST_ROOT_ICON_PATH . 'reason.png" alt="ArchiveInABox Logo" /><strong>Reason</strong></div>
                           <div style="width:70%; float:left; font-size:14px; padding:5px;">' . $postData['reporting_reason_comment'] . '</div>
                           <div style="clear:both;"></div>';
                $email_template = str_replace('#phone_image#', $phone_image, $email_template);
                $email_template = str_replace('#sub_link_image#', $link_image, $email_template);
                $email_template = str_replace('#first_name#', $postData['rp_first_name'], $email_template);
                $email_template = str_replace('#last_name#', $postData['rp_last_name'], $email_template);
                $email_template = str_replace('#email#', $postData['rp_email'], $email_template);
                $email_template = str_replace('#phone#', $phone, $email_template);
                $email_template = str_replace('#sub_link#', $sub, $email_template);
                $email_template = str_replace('#sub_link_name#', $postData['item_url'], $email_template);
                $email_template = str_replace('#used_page#', $reason, $email_template);
                $email_template = str_replace('#comment#', $postData['rp_message'], $email_template);
                $email_template = str_replace('#request#', $requestDataAdmin, $email_template);

                /* auto generated email template for request user */
                $responseData = [];
                $responseData['from'] = ADMIN_EMAIL;
                $responseData['reply'] = ADMIN_EMAIL;
                $responseData['to'] = $postData['rp_email'];
                $responseData['subject'] = 'Report comment response';
                $response_template = file_get_contents(EMAIL_PATH . "/response.html");
                $response_template = str_replace('#response_message#', "Thank you for writing to us, we will get back to you soon.", $response_template);
                $sendEmail = sendMail($responseData, $response_template);
                /** auto generated email template for request user * */
                // Send email to archive owner                
                $sendEmail = sendMail($email_data, $email_template);
                $responseArray = ['status' => 'success', 'message' => 'Your comment Report  has been submitted successfully. .'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'update_comments_by_id':
        $commentText = trim($_POST['comment_text']);
        $comment_id = $_POST['comment_id'];
        $responseArray = ['status' => 'error', 'message' => 'Comment not updated, Please try again.'];
        if ($commentText != '' && $comment_id != '') {
            // Request array to comment update            
            $postCommentData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "cmnt_upd",
                "obj_id" => $comment_id,
                "new_comment_text" => $commentText
            );
            // Service request to comment update 
            $apiResponse = aibServiceRequest($postCommentData, 'comments');
            if ($apiResponse['status'] == 'OK') {
                $responseArray = ['status' => 'success', 'message' => 'Comment updated successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'update_item_fields_tags_data':
        $data_type = $_POST['data_type'];
        $item_id = $_POST['item_id'];
        $field_id = $_POST['field_id'];
        $updated_text = $_POST['updated_value'];
        $requestApiData = array(
            '_key' => APIKEY,
            '_session' => $sessionKey,
            '_user' => 1
        );
        $file_name = '';
        if ($data_type == 'fields') {
            $requestApiData['_op'] = 'store_item_fields';
            $requestApiData['obj_id'] = $item_id;
            $requestApiData['field_id_1'] = $field_id;
            $requestApiData['field_value_1'] = $updated_text;
            $file_name = 'browse';
        } elseif ($data_type == 'tags') {
            $requestApiData['_op'] = 'tags_add';
            $requestApiData['obj_id'] = $item_id;
            $requestApiData['tags'] = $updated_text;
            $requestApiData['opt_replace'] = 'Y';
            $file_name = 'tags';
        } else {
            $requestApiData['_op'] = 'modify_item';
            $requestApiData['obj_id'] = $item_id;
            $requestApiData['item_title'] = $updated_text;
            $requestApiData['opt_allow_dup'] = 'Y';
            $file_name = 'browse';
        }
        $responseArray = ['status' => 'error', 'message' => 'Item data not updated.'];
        if ($file_name != '') {
            $apiResponse = aibServiceRequest($requestApiData, $file_name);
            if ($apiResponse['status'] == 'OK') {
                $responseArray = ['status' => 'success', 'message' => 'Item data updated successfully.'];
            }
        }
        print json_encode($responseArray);
        break;
    case 'copy_scrapbook':
        $scrapbook_id = $_POST['scrapbook_id'];
        $scrapbook_parent_id = $_POST['scrapbook_parent_id'];
        $scrapbook_title = $_POST['scrapbook_title'];
        $item_user_id = $_POST['item_user_id'];
        $user_archive_id = $_SESSION['aib']['user_data']['user_top_folder'];
        // Get Item child data        
        $userRootItem = getItemChildWithData($user_archive_id);
        $shareTarget = '';
        // Used foreach loop over $userRootItem to check conditions         
        foreach ($userRootItem as $rootData) {
            if ($rootData['item_title'] == 'Scrapbooks') {
                $shareTarget = $rootData['item_id'];
                break;
            }
        }
        if ($shareTarget == '') {
            //  Create new scrapbook for user           
            $shareTarget = createUserScrapbookRoot($_SESSION['aib']['user_data']['user_id']);
        }
        // Get item child with all property data        
        $userScrapbookList = getItemChildWithData($shareTarget);
        $itemInList = '';
        if (!empty($userScrapbookList)) {
            // Used foreach loop over $userScrapbookList & check conditions             
            foreach ($userScrapbookList as $scrKey => $scrapbookListArray) {
                if ($scrapbookListArray['item_ref'] == $scrapbook_id) {
                    $itemInList = 'yes';
                    break;
                }
            }
        }
        if ($itemInList == '') {
            $LinkProperties = array(
                array("name" => "link_class", "value" => "scrapbook_copy"),
                array("name" => "scrapbook_type", "value" => "public")
            );
            // Request array to create new share            
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "share_create",
                "share_target" => $shareTarget,
                "share_source" => $scrapbook_id,
                "share_title" => $scrapbook_title,
                "share_item_user" => $item_user_id,
                "share_properties" => json_encode($LinkProperties),
            );
            // Service request to create new share
            $apiResponse = aibServiceRequest($postData, 'sharing');
            $responseArray = ['status' => 'error', 'message' => 'Scrapbook not copied, Please try again.'];
            if ($apiResponse['status'] == 'OK') {
                $responseArray = ['status' => 'success', 'message' => 'Scrapbook copied successfully.'];
            }
        } else {
            $responseArray = ['status' => 'error', 'message' => 'Scrapbook already in your list.'];
        }
        print json_encode($responseArray);
        break;
    case 'get_state_country':
        // Request array to get item list of ftree parent item        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $_POST['parent_id']
        );
        // Service request to get item list of ftree parent item 
        $apiResponse = aibServiceRequest($postData, 'browse');
        $state_country = [];
        // Used foreach loop over api response to push each array value in $state_country        
        foreach ($apiResponse['info']['records'] as $key => $value) {
            array_push($state_country, utf8_decode(urldecode($value['item_title'])));
        }
        $state_country = array('Alabama', 'Alaska', 'American Samoa', 'Arizona', 'CA', 'Alabama', 'Alaska', 'American Samoa', 'Arizona', 'Arkansas', 'Baker Island', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Guam', 'Hawaii', 'Howland Island');
        print json_encode($state_country);
        break;
    case 'get_saved_scrapbook_title':
        $item_id = $_POST['item_id'];
        $savedTitle = '';
        if ($_SESSION['aib']['user_data']['user_type'] == 'U') {
            $user_id = $_SESSION['aib']['user_data']['user_type'];
            $user_archive_id = $_SESSION['aib']['user_data']['user_top_folder'];
            // Get item child with all property data            
            $userRootItem = getItemChildWithData($user_archive_id);
            $userScrapbookParent = '';
            // Used foreach loop over $userRootItem to check item_title equal to Scrapbooks of each array element               
            foreach ($userRootItem as $rootData) {
                if ($rootData['item_title'] == 'Scrapbooks') {
                    $userScrapbookParent = $rootData['item_id'];
                    break;
                }
            }
            if ($userScrapbookParent != '') {
                // Get item child with all property data    
                $scrapbookChildData = getItemChildWithData($userScrapbookParent);
                // Used foreach loop on $scrapbookChildData to get item child of ftree item                  
                foreach ($scrapbookChildData as $scrapbookData) {
                    if ($scrapbookData['item_ref'] == '-1' && $scrapbookData['item_type'] == 'SG') {
                        // Get item child with all property data    
                        $scrapbookEntries = getItemChildWithData($scrapbookData['item_id']);
                        // Used foreach loop over $scrapbookEntries to get item tree data                          
                        foreach ($scrapbookEntries as $data) {
                            $itemParents = getTreeData($item_id, false, 1);
                            $itemParentId = $itemParents[count($itemParents) - 2]['item_id'];
                            if ($data['item_ref'] == $item_id || $data['item_ref'] == $itemParentId) {
                                $savedTitle = $data['item_title'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        echo $savedTitle;
        break;
    case'record_id_session':
        $response = RecordsIdsSession($_POST['folder_id']);
        print json_encode($response);
        break;
    case 'image_data_with_highlight':
        $item_type = isset($_POST['item_type']) ? $_POST['item_type'] : '';
        $request_pr_file_id = isset($_POST['pr_file_id']) ? $_POST['pr_file_id'] : '';
        $data_url = isset($_POST['data_url']) ? $_POST['data_url'] : '';
        $searchString = isset($_POST['searchString']) ? $_POST['searchString'] : '';
        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        $window_height = isset($_POST['height']) ? $_POST['height'] : '';
        $searchHighLightData = getSearchHighlightData($item_id, $searchString);
        $urlImage = '';
        if ($item_id != '') {
            $itemPropData = getFileId($item_id);
            $pr_file_id = isset($itemPropData['file_id']) ? $itemPropData['file_id'] : $request_pr_file_id;
            if (isset($itemPropData['file_data']['item_source_info']) && $itemPropData['file_data']['item_source_info'] != '') {
                $urlImage = urldecode($itemPropData['file_data']['item_source_info']);
            }
        }

        include_once TEMPLATE_PATH . 'image_highlight.php';
        break;
    case'image_with_original_data':
        $item_type = isset($_POST['item_type']) ? $_POST['item_type'] : '';
        $pr_file_id = isset($_POST['pr_file_id']) ? $_POST['pr_file_id'] : '';
        $searchString = isset($_POST['searchString']) ? $_POST['searchString'] : '';
        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        $searchHighLightData = getSearchHighlightData($item_id, $searchString);
        include_once TEMPLATE_PATH . 'original_image.php';
        break;
    case'highlight_image_prop':
        $searchHighLightData = getHighlightImageProp($_POST['item_id'], $_POST['searchString'], $_POST['height'], $_POST['width']);
        print json_encode($searchHighLightData['rect']);
        break;
    case'get_ocr_text_value':
        $responseArray = ['status' => 'error', 'message' => 'No ocr values found.'];
        if ($_POST['item_id'] != '') {
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => "get_named_fields",
                "_user" => 1,
                "obj_id" => $_POST['item_id']
            );
            $apiResponse = aibServiceRequest($postData, 'fields');
            if ($apiResponse['status'] == 'OK') {
                $responseArray = ['status' => 'success', 'message' => 'OCR Values', 'data' => $apiResponse['info']['records']];
            }
        }
        print json_encode($responseArray);
        break;

    case 'get_all_society_admin':
        $loggedInUser = isset($_SESSION['aib']['user_data']['user_id']) ? $_SESSION['aib']['user_data']['user_id'] : '';
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list_profiles",
            "user_type" => 'A',
            "_prop" => 'Y'
        );
        // Service request to get item list of ftree parent item 
        $apiResponse = aibServiceRequest($postData, 'users');
        $societyAdminListArray = [];
        if ($apiResponse['status'] == 'OK') {
            if (!empty($apiResponse['info']['records'])) {
                foreach ($apiResponse['info']['records'] as $dataArray) {
                    if ($loggedInUser != $dataArray['user_id']) {
                        $societyAdminListArray[$dataArray['user_id']] = $dataArray['user_login'] . '(' . urldecode($dataArray['_properties']['email']) . ')';
                    }
                }
            }
        }
        print json_encode($societyAdminListArray);
        break;

    case 'share_item_with_society_admin':
        $record_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        $requested_time = $_POST['timestamp_value'];
        $current_time = time();
        $time_diff = $current_time - $requested_time;
        $sessionKey = $_SESSION['aib']['session_key'];
        $data_type_cond = false;
        if (isset($_POST['share_type']) && $_POST['share_type'] == 'record') {
            $data_type_cond = 'record';
        }
        $responseArray = array('status' => 'error', 'message' => 'You are Robot!');
        if ($time_diff > TIMESTAMP_4) {
            if (!empty($_POST['selected_society'])) {
                $shared_user = [];
                $shared_user_email = [];
                $count = 0;
                foreach ($_POST['selected_society'] as $societyAdminId) {
                    $adminDetails = getAdminTopFolder($societyAdminId);
                    if (!empty($adminDetails)) {
                        $shared_user[$count] = $adminDetails['user_login'];
                        $shared_user_email[$count] = $adminDetails['properties']['email'];
                        $adminShareFolder = societyShareFolder($adminDetails['user_top_folder']);
                        if ($adminShareFolder) {
                            shareItemWithAdmin($societyAdminId, $record_id, $adminShareFolder);
                        }
                    }
                    $count ++;
                }
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $record_id,
                    'opt_long' => 'Y',
                    'propname_1' => 'share_user',
                    'propval_1' => json_encode($shared_user),
                    'propname_2' => 'share_message',
                    'propval_2' => $_POST['share_massage'],
                    'propname_3' => 'share_created_date',
                    'propval_3' => time()
                );
                if (isset($_POST['share_url_link'])) {
                    $apiRequestDataItem['propname_4'] = 'share_url_link';
                    $apiRequestDataItem['propval_4'] = $_POST['folder_id'];
                }
                $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
                if ($apiResponse['status'] == 'OK') {
                    if ($data_type_cond) {
                        $obj_id = $_POST['item_id'];
                    } else {
                        $obj_id = $_POST['folder_id'];
                    }
                    // Get item tree data                
                    $itemTopParents = getTreeData($obj_id);
                    $email_data = [];
                    $email_data['to'] = urldecode(implode(',', $shared_user_email));
                    $email_data['from'] = ADMIN_EMAIL;
                    $email_data['reply'] = ADMIN_EMAIL;
                    // Get html content of share.html file                
                    $email_template = file_get_contents(EMAIL_PATH . "/share.html");
                    $name = (isset($itemTopParents[1]['item_title']) && $itemTopParents[1]['item_title'] != '') ? $itemTopParents[1]['item_title'] : 'ArchiveInABox';
                    $date = date("F j, Y");
                    $email_template = str_replace('#name#', $name, $email_template);
                    $email_template = str_replace('#date#', $date, $email_template);
                    $email_template = str_replace('#archive_logo#', '', $email_template);
                    if ($data_type_cond) {
                        $main_page = 'home.html';
                        $itemTopParents = getTreeData($_POST['folder_id']);
                        if ($itemTopParents[0]['item_title'] == '_STDUSER') {
                            $main_page = 'people.html';
                        }
                        $email_data['subject'] = 'ArchiveInABox: ' . $_POST['sharing_name'] . ' has shared an archive record';
                        $url = '<a style="word-break:break-all;" href="' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1') . '" target="_blank">' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1') . '</a>';
                        if (isset($_POST['search_text']) && $_POST['search_text'] != '') {
                            $url = '<a style="word-break:break-all;" href="' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '" target="_blank">' . HOST_PATH . $main_page . '?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&record_id=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '</a>';
                        }
                        $img = '<img style="width:100%;" src="' . $_POST['thoumb_id'] . '&download=1 " alt="Image" />';
                    } else {
                        $email_data['subject'] = 'ArchiveInABox: ' . $_POST['sharing_name'] . ' has shared an archive item';
                        $url = '<a style="word-break:break-all;" href="' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1') . '" target="_blank">' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1') . '</a>';
                        if (isset($_POST['search_text']) && $_POST['search_text'] != '') {
                            $url = '<a style="word-break:break-all;" href="' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '" target="_blank">' . HOST_PATH . 'item-details.html?q=' . encryptQueryString('folder_id=' . $_POST['folder_id'] . '&itemId=' . $_POST['item_id'] . '&share=1&search_text=' . $_POST['search_text']) . '</a>';
                        }
                        $img = '<img style="width:100%;" src="' . THUMB_URL . '?id=' . $_REQUEST['thoumb_id'] . '&download=1" alt="Image" />';
                    }
                    $email_template = str_replace('#url_details#', $url, $email_template);
                    $email_template = str_replace('#item_images#', $img, $email_template);
                    $massage_display = '';
                    $massage = '';
                    if (!empty($_POST['share_massage'])) {
                        $massage_display = 'Message:';
                        $massage = $_POST['share_massage'];
                    }
                    $email_template = str_replace('#share_massage#', $massage, $email_template);
                    $email_template = str_replace('#massage_display#', $massage_display, $email_template);
                    // Send email to user     
                    $email = sendMail($email_data, $email_template);
                    if ($email) {
                        $responseArray = ['status' => 'success', 'message' => 'Item shared successfully.'];
                    } else {
                        $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
                    }
                } else {
                    $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
                }
            }
        }
        print json_encode($responseArray);
        break;
    case 'get_society_complete_tree':
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $user_top_folder = $_SESSION['aib']['user_data']['user_top_folder'];
        if ($user_id != '' && $user_top_folder != '') {
            include('config/aib.php');
            include("include/folder_tree.php");
            include("include/fields.php");
            include('include/aib_util.php');
            aib_open_db();
            $TreeNavInfo = array();
            $TreeNavInfo = aib_generate_tree_nav_div($GLOBALS["aib_db"], 1, $user_top_folder, "fetch_tree_children", "aib-nav-tree-ul", "aib-nav-tree-li", "aib-nav-tree-li", "aib-nav-tree-li");
            aib_close_db();
            ?>
            <input type="hidden" name="parent_list" id="parent_list" value="">
            <div class='aib-selected-tree-items' id='aib-selected-tree-items'> </div>	
            <script type='text/javascript' src='js/aib.js'></script>
            <script src="js/vendor/jquery.ui.widget.js"></script>
            <script type="text/javascript">
            <?php
            print("
// Global to hold current form
var CurrentFormID = -1;
// Global to hold field values
var UserDefFieldValues = {};
// Global to hold already-loaded subtrees
");
// If there is already nav info, initialize array with data, else empty array.
            if (count(array_keys($TreeNavInfo)) > 0) {
                print("
var CheckedTreeItems = {" . $TreeNavInfo["init_item"] . ":'Y'};
");
            } else {
                print("
var CheckedTreeItems = {};
");
            }
            if (isset($TreeNavInfo["idlist"]) == true) {
                if (count($TreeNavInfo["idlist"]) > 0) {
                    print(" var NavLoadedMap = { ");
                    foreach ($TreeNavInfo["idlist"] as $ItemID) {
                        print(" $ItemID:'Y',");
                    }
                    print(" };");
                } else {
                    print("var NavLoadedMap = {};");
                }
            } else {
                print("	var NavLoadedMap = {};	");
            }
            print(" var InitCheckedDisplay = false;");
            ?>
                var InitCheckedDisplay = false;
                // TREE NAVIGATION FUNCTIONS
                // Global to hold already-loaded subtrees	
                // Fetch children for tree
                function fetch_tree_children(LocalEvent, RefObj, ItemID) {
                    $(RefObj).css('list-style-image', "url('/images/button-open.png')");
                    var QueryParam = {};
                    var ChildList;
                    LocalEvent.stopPropagation();
                    if (NavLoadedMap[ItemID] == undefined) {
                        NavLoadedMap[ItemID] = 'Y';
                        QueryParam['o'] = '63686c';
                        QueryParam['i'] = '31';
                        QueryParam['pi'] = ItemID;
                        aib_ajax_request('/services/treenav.php', QueryParam, fetch_tree_children_result);
                        return;
                    }
                    var cl = document.getElementById('aib_navlist_childof_' + ItemID);
                    ChildList = $('#aib_navlist_childof_' + ItemID);
                    if (ChildList !== undefined && cl) {
                        if (ChildList.css('display') != 'none') {
                            ChildList.css('display', 'none');
                            $(RefObj).css('list-style-image', "url('/images/button-closed.png')");
                        } else {
                            ChildList.css('display', 'block');
                            $(RefObj).css('list-style-image', "url('/images/button-open.png')");
                        }
                    }
                }
                // Set checkbox for tree item, preventing bubble-up
                function set_tree_checkbox(LocalEvent, RefObj) {
                    var ElementID;
                    LocalEvent.stopPropagation();
                    ElementID = $(RefObj).attr('id');
                    ElementID = ElementID.replace('aib_item_checkbox_', '', ElementID);
                    if ($(RefObj).is(':checked') == true) {
                        $(RefObj).prop('checked', true);
                        CheckedTreeItems[ElementID] = 'Y';
                    }
                    else {
                        $(RefObj).prop('checked', false);
                        CheckedTreeItems[ElementID] = 'N';
                    }
                    show_checked_tree_items();
                    getRecordsList(ElementID);
                }
                // Callback for tree children fetch
                function fetch_tree_children_result(InData) {
                    var ElementID;
                    var ItemID;
                    if (InData['status'] != 'OK') {
                        alert('ERROR PROCESSING CHILD REQUEST: ' + InData['info']['msg']);
                        return;
                    }
                    ItemID = InData['info']['item_id'];
                    ElementID = 'aib_navlist_entry_' + ItemID;
                    $('#' + ElementID).append(InData['info']['html']);
                    $('#' + ElementID + ' li[id^="aib_navlist_entry_"]').each(function () {
                        if ($(this).find("input:checkbox").length === 0) {
                            var id = $(this).attr('id').split("_").pop(-1);
                            $(this).prepend('<input type="checkbox" data-item-id="' + id + '" value="' + id + '" checkbox-type="custom" class="custom-checkbox-append" />');
                        }
                    });
                    show_checked_tree_items();
                }
                // Show a list of all checked tree items using AJAX to retrieve HTML from
                // a back-end HTML generator.
                function show_checked_tree_items() {
                    var CheckedItemsList;
                    var Size;
                    var Counter;
                    var IDValue;
                    var QueryParam = {};
                    var IDList = [];
                    var Key;
                    // Get a list of all checked items
                    for (Key in CheckedTreeItems) {
                        if (CheckedTreeItems[Key] == 'Y') {
                            IDList.push(Key);
                        }
                    }
                    // Generate an unsorted list in display area to show items
                    QueryParam['idlist'] = IDList.join(',');
                    QueryParam['o'] = '67736c';
                    QueryParam['i'] = '31';
                    aib_ajax_request('/services/treenav.php', QueryParam, show_selected_tree_items);
                    return;
                }
                function show_selected_tree_items(InData) {
                    if (InData['status'] != 'OK') {
                        $('#aibselectedtreeitems').html("ERROR: Can't get list");
                        return;
                    }
                    $('#aib-selected-tree-items').html(InData['info']['html']);
                    post_process_form();
                }
                // Copy the selected items array to input form
                function post_process_form() {
                    var IDList = [];
                    var Key;
                    // Get a list of all checked items
                    for (Key in CheckedTreeItems) {
                        if (CheckedTreeItems[Key] == 'Y') {
                            IDList.push(Key);
                        }
                    }
                    $('#parent_list').val(IDList.join(','));
                    return(true);
                }
                // If the checked display area hasn't been initialized, do so here
                if (InitCheckedDisplay == false) {
                    InitCheckedDisplay = true;
                    show_checked_tree_items();
                }
            </script>
            <?php
            echo $TreeNavInfo['html'];
        }
        break;

    case 'get_sub_group_records':
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($item_id) {
            $itemDetails = getItemData($item_id);
            $dataRecordsArray = getItemListRecord($item_id);
            if (!empty($dataRecordsArray)) {
                foreach ($dataRecordsArray['info']['records'] as $key => $dataArray) {
                    if ($dataArray['item_type'] != 'RE') {
                        unset($dataRecordsArray['info']['records'][$key]);
                        continue;
                    }
                    if ($dataArray['properties']['aib:visible'] == 'N' || ($dataArray['properties']['aib:visible'] == 'Y' && $dataArray['properties']['aib:private'] == 'Y')) {
                        unset($dataRecordsArray['info']['records'][$key]);
                        continue;
                    }
                    if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'cmntset') {
                        unset($dataRecordsArray['info']['records'][$key]);
                        continue;
                    }
                    if (in_array($dataArray['link_properties']['link_class'], array('related_content', 'historical_connection', 'scrapbook'))) {
                        unset($dataRecordsArray['info']['records'][$key]);
                        continue;
                    }
                }
                include_once TEMPLATE_PATH . 'list-content.php';
            }
        }
        break;
    case 'connect_item_with_other_society_item':
        $connecting_item_id = filter_input(INPUT_POST, 'connecting_item', FILTER_SANITIZE_SPECIAL_CHARS);
        $connectingItemsArray = explode(',', $connecting_item_id);
        $selected_items = filter_input(INPUT_POST, 'selected_items', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $checkForBlock = getTreeData($connectingItemsArray[0]);
        $blockedArchived = isset($checkForBlock[1]['properties']['blocked_archives']) ? json_decode($checkForBlock[1]['properties']['blocked_archives'], true) : [];
        if (!in_array($_SESSION['aib']['user_data']['user_top_folder'], $blockedArchived)) {
            $user_id = $_SESSION['aib']['user_data']['user_id'];
            $responseArray = ['status' => 'error', 'message' => 'Something went wrong.'];
            $errorCount = 0;
            if (isset($_SESSION['aib']['user_data']['user_type']) && $_SESSION['aib']['user_data']['user_type'] == 'A') {
                if (!empty($connectingItemsArray)) {
                    foreach ($connectingItemsArray as $connecting_item) {
                        $connecting_item_parents = getTreeData($connecting_item);
                        $connectingAdmin = getArchiveOwner($connecting_item_parents[1]['item_id'], 'A');
                        if ($connecting_item_parents[1]['item_id'] != $_SESSION['aib']['user_data']['user_top_folder']) {
                            if (!empty($selected_items)) {
                                foreach ($selected_items as $key => $item_id) {
                                    $connectionStatus = checkForSocietyConnection($item_id, $connecting_item);
                                    if ($connectionStatus) {
                                        $LinkProperties = array(
                                            array("name" => "link_class", "value" => "historical_connection"),
                                            array("name" => "owner", "value" => $user_id),
                                            array("name" => "public_folder", "value" => $connecting_item),
                                            array("name" => "connection_type", "value" => "society_related_content"),
                                            array("name" => "connecting_ag", "value" => $connecting_item_parents[1]['item_id']),
                                            array("name" => "requesting_ag", "value" => $_SESSION['aib']['user_data']['user_top_folder'])
                                        );
                                        $LinkPropertiesRev = array(
                                            array("name" => "link_class", "value" => "historical_connection"),
                                            array("name" => "owner", "value" => $connectingAdmin['user_id']),
                                            array("name" => "public_folder", "value" => $connecting_item),
                                            array("name" => "connection_type", "value" => "society_related_content"),
                                            array("name" => "connecting_ag", "value" => $_SESSION['aib']['user_data']['user_top_folder']),
                                            array("name" => "requesting_ag", "value" => $connecting_item_parents[1]['item_id']),
                                            array("name" => "default_connection_by", "value" => 'aib')
                                        );
                                        $PostData = array(
                                            "_key" => APIKEY,
                                            "_session" => $sessionKey,
                                            "_user" => "1",
                                            "_op" => "share_create",
                                            "share_target" => $connecting_item,
                                            "share_source" => $item_id,
                                            "share_title" => "Shared $item_id at " . date('Y-m-d h:i:s'),
                                            "share_item_user" => $user_id,
                                            "share_properties" => json_encode($LinkProperties)
                                        );
                                        $PostDataReverse = array(
                                            "_key" => APIKEY,
                                            "_session" => $sessionKey,
                                            "_user" => "1",
                                            "_op" => "share_create",
                                            "share_target" => $item_id,
                                            "share_source" => $connecting_item,
                                            "share_title" => "Shared $connecting_item at " . date('Y-m-d h:i:s'),
                                            "share_item_user" => $connectingAdmin['user_id'],
                                            "share_properties" => json_encode($LinkPropertiesRev)
                                        );
                                        $apiResponse = aibServiceRequest($PostData, 'sharing');
                                        $apiResponseReverse = aibServiceRequest($PostDataReverse, 'sharing');
                                        $responseArray = ['status' => 'success', 'message' => 'Connected successfully.'];
                                    } else {
                                        $responseArray = ['status' => 'error', 'message' => 'Already have a connection.'];
                                    }
                                }
                            } else {
                                $responseArray = ['status' => 'error', 'message' => 'Please select an item to make connection'];
                            }
                        } else {
                            $responseArray = ['status' => 'error', 'message' => 'You can\'t connect with your own item.'];
                        }
                    }
                } else {
                    $responseArray = ['status' => 'error', 'message' => 'Please select an item to connect with'];
                }
            } else {
                $responseArray = ['status' => 'error', 'message' => 'You will have to logged in with administrator account.'];
            }
        } else {
            $responseArray = ['status' => 'error', 'message' => 'You can\'t make a connection as connecting society has been blocked your society.'];
        }
        print json_encode($responseArray);
        break;
    case 'item_visited':
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_SPECIAL_CHARS);
        $visitedItem = json_decode($_COOKIE['visited_items']);
        if (!in_array($item_id, $visitedItem)) {
            $visitedItem[] = $item_id;
        }
        setcookie('visited_items', json_encode($visitedItem), time() + 86400, "/");
        break;
    case 'get_encrypted_string':
        $queryString = $_POST['queryString'];
        echo $encryptedString = encryptQueryString($queryString);
        break;
    case 'get_decrypted_string':
        $query_string = $_POST['query_string'];
        $decrypted_string = decryptQueryString($query_string);
        print json_encode($decrypted_string);
        break;
    case 'get_encrypted_url':
        $query_string = $_POST['queryString'];
        $item_id = $_POST['item_id'];
        $decrypted_string = decryptQueryString($query_string);
        if (!empty($decrypted_string)) {
            $count = 0;
            $url = '';
            foreach ($decrypted_string as $key => $value) {
                if ($count == 0) {
                    if ($key == 'folder_id') {
                        $url .= 'folder_id=' . $item_id;
                    } else {
                        $url .= $key . '=' . $value;
                    }
                } else {
                    if ($key == 'folder_id') {
                        $url .= '&folder_id=' . $item_id;
                    } else {
                        $url .= '&' . $key . '=' . $value;
                    }
                }
                $count ++;
            }
            echo $encryptedString = encryptQueryString($url);
        }
        break;
    case 'people_encrypted_string':
        $query_string = $_POST['queryString'];
        $previous_id = $_POST['previous_id'];
        $current_id = $_POST['current_item'];
        $previous = ($previous_id != '') ? $previous_id . ',' . $current_id : $current_id;
        $decrypted_string = decryptQueryString($query_string);
        if (!empty($decrypted_string)) {
            $count = 0;
            $url = '';
            foreach ($decrypted_string as $key => $value) {
                if ($count == 0) {
                    $url .= $key . '=' . $value;
                } else {
                    $url .= '&' . $key . '=' . $value;
                }
                $count ++;
            }
        }
        $url = ($url != '') ? $url . '&previous=' . $previous : $url . 'previous=' . $previous;
        echo $encryptedString = encryptQueryString($url);
        break;
}

function getHistoricalConnectionLists($record_id = null) {
    $relatedItemsData = [];
    if ($record_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'item_share',
            "user_id" => -1,
            "item_id" => $record_id,
            "opt_get_property" => 'Y'
        );
        // Service request to get all shared list         
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        if ($apiResponse['status'] == 'OK') {
            foreach ($apiResponse['info']['records'] as $relatedDataArray) {
                if ($relatedDataArray['properties']['link_class'] == 'historical_connection') {
                    if ($relatedDataArray['item_ref'] == $record_id) {
                        $dataArray = $relatedDataArray["parent_info"];
//                        $dataArray = getItemData($relatedDataArray['item_parent']);                        
                        $itemTopParents = getTreeData($relatedDataArray['item_parent']);
                        $dataArray['top_parent'] = $itemTopParents[0]['item_title'];
                        $dataArray['item_society'] = $itemTopParents[1]['item_title'];
                        // Get sub-group 1st thumb id  
                        if (in_array($dataArray['item_type'], array('IT'))) {
                            $dataArray['thumbId'] = getSubGroupFirstItemThumb($relatedDataArray['item_parent']);
                        } elseif ($dataArray['item_type'] == 'RE') {
                            $dataArray['thumbId'] = $dataArray['item_id'];
                        } else {
                            $dataArray['ag_thumb'] = isset($itemTopParents[1]['properties']['historical_connection_logo']) ? $itemTopParents[1]['properties']['historical_connection_logo'] : '';
                        }
                        $relatedItemsData[] = $dataArray;
                    }
                }
            }
        }
    }
    return $relatedItemsData;
}

function getRecordRelatedItems($record_id = null) {
    if ($record_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $itemDetails = getItemData($record_id);
        $item_user_id = $itemDetails['item_user_id'];
        // Get user profile by id        
        $userDetails = getUserProfileById($item_user_id);
        $relatedItemsData = [];
        // Request array to get all shared list         
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'item_share',
            "user_id" => -1,
            "item_id" => $record_id,
            "opt_get_property" => 'Y'
        );
        // Service request to get all shared list         
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        if ($apiResponse['status'] == 'OK') {
            // Used foreach loop over api response to get item details & 1st thumb id of each array element            
            foreach ($apiResponse['info']['records'] as $relatedDataArray) {
                if ($relatedDataArray['properties']['link_class'] == 'related_content') {
                    if ($relatedDataArray['item_ref'] == $record_id) {
                        // Get item data                         
//                        $dataArray=getItemData($relatedDataArray['item_parent']);
                        $dataArray = $relatedDataArray["parent_info"];
                        // Get item tree data                         
                        $itemTopParents = getTreeData($relatedDataArray['item_parent']);
                        $dataArray['top_parent'] = $itemTopParents[0]['item_title'];
                        // Get sub-group 1st thumb id  
                        if (in_array($dataArray['item_type'], array('SG', 'IT'))) {
                            $dataArray['thumbId'] = getSubGroupFirstItemThumb($relatedDataArray['item_parent']);
                        } elseif ($dataArray['item_type'] == 'RE') {
                            $dataArray['thumbId'] = $dataArray['item_id'];
                        } else {
                            $dataArray['ag_thumb'] = isset($itemTopParents[1]['properties']['archive_group_thumb']) ? $itemTopParents[1]['properties']['archive_group_thumb'] : '';
                        }
                        $relatedItemsData[] = $dataArray;
                    }
                }
            }
        }
        $parentRelatedItems = getAllParentsRelatedItems($record_id);
        $relatedItemsData = array_merge($parentRelatedItems, $relatedItemsData);
        return count($relatedItemsData);
    }
}

function getAllParentsRelatedItems($item_id = null) {
    if ($item_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $recordParents = getTreeData($item_id);
        array_pop($recordParents);
        unset($recordParents[0]);
        if (!empty($recordParents)) {
            $parentsRelatedData = [];
            foreach ($recordParents as $parentsData) {
                $record_id = $parentsData['item_id'];
                $itemDetails = getItemData($record_id);
                $item_user_id = $itemDetails['item_user_id'];
                $PostDataArray = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => "1",
                    "_op" => "share_list",
                    "perspective" => 'item_share',
                    "user_id" => -1,
                    "item_id" => $record_id,
                    "opt_get_property" => 'Y'
                );
                $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
                if ($apiResponse['status'] == 'OK') {
                    if (!empty($apiResponse['info']['records'])) {
                        foreach ($apiResponse['info']['records'] as $relatedDataArray) {
                            if ($relatedDataArray['properties']['link_class'] == 'historical_connection') {
                                if ($relatedDataArray['item_ref'] == $record_id) {
                                    $dataArray = $relatedDataArray["parent_info"];
//                                    $dataArray=getItemData($relatedDataArray['item_parent']);                    
                                    $itemTopParents = getTreeData($relatedDataArray['item_parent']);
                                    $dataArray['top_parent'] = $itemTopParents[0]['item_title'];
                                    $dataArray['item_society'] = $itemTopParents[1]['item_title'];
                                    if (in_array($dataArray['item_type'], array('IT'))) {
                                        $dataArray['thumbId'] = getSubGroupFirstItemThumb($relatedDataArray['item_parent']);
                                    } elseif ($dataArray['item_type'] == 'RE') {
                                        $dataArray['thumbId'] = $dataArray['item_id'];
                                    } else {
                                        $dataArray['ag_thumb'] = isset($itemTopParents[1]['properties']['historical_connection_logo']) ? $itemTopParents[1]['properties']['historical_connection_logo'] : '';
                                    }
                                    $parentsRelatedData[] = $dataArray;
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                }
            }
            return $parentsRelatedData;
        }
    }
}

function checkForSocietyConnection($item_id = null, $connecting_item = null) {
    if ($item_id != null && $connecting_item != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'item_share',
            "user_id" => '-1',
            "item_id" => $item_id
        );
        // Service request to get item list of ftree    
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        if ($apiResponse['status'] == 'OK') {
            if (!empty($apiResponse['info']['records'])) {
                $status = true;
                foreach ($apiResponse['info']['records'] as $dataArray) {
                    if ($dataArray['item_ref'] == $item_id && $dataArray['item_parent'] == $connecting_item) {
                        $status = false;
                        break;
                    }
                }
                return $status;
            } else {
                return true;
            }
        }
    }
}

function getAdminTopFolder($societyAdminId = null) {
    if ($societyAdminId != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "get_profile",
            "_user" => 1,
            "user_id" => $societyAdminId,
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info'];
        }
    }
}

function societyShareFolder($archive_group_id = null) {
    if ($archive_group_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "list",
            "_user" => 1,
            "parent" => $archive_group_id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            if (!empty($apiResponse['info']['records'])) {
                $society_share = '';
                $shareDetails = array();
                foreach ($apiResponse['info']['records'] as $dataArray) {
                    if ($dataArray['item_title'] == 'society-share') {
                        $society_share = 'yes';
                        $shareDetails = $dataArray;
                        break;
                    }
                }
                if ($society_share == 'yes') {
                    return $shareId = getSharedFolderId($shareDetails['item_id']);
                } else {
                    return $shareId = createShareFolder($archive_group_id);
                }
            } else {
                return $shareId = createShareFolder($archive_group_id);
            }
        }
    }
}

function getSharedFolderId($item_id = null) {
    if ($item_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "list",
            "_user" => 1,
            "parent" => $item_id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            if (!empty($apiResponse['info']['records'])) {
                $society_share = '';
                $shareDetails = array();
                foreach ($apiResponse['info']['records'] as $dataArray) {
                    if ($dataArray['item_title'] == 'society-share') {
                        $society_share = 'yes';
                        $shareDetails = $dataArray;
                        break;
                    }
                }
                if ($society_share == 'yes') {
                    $postDataSg = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_op" => "list",
                        "_user" => 1,
                        "parent" => $shareDetails['item_id']
                    );
                    $apiResponseSg = aibServiceRequest($postDataSg, 'browse');
                    if ($apiResponseSg['status'] == 'OK') {
                        if (!empty($apiResponseSg['info']['records'])) {
                            foreach ($apiResponseSg['info']['records'] as $dataArray) {
                                if ($dataArray['item_title'] == 'society-share') {
                                    return $dataArray['item_id'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function createShareFolder($archive_group_id = null) {
    if ($archive_group_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'create_item',
            '_session' => $sessionKey,
            'parent' => $archive_group_id,
            'item_title' => 'society-share',
            'item_class' => 'ar'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            setVisibleProperty($apiResponse['info']);
            $postDataCol = array(
                '_key' => APIKEY,
                '_user' => 1,
                '_op' => 'create_item',
                '_session' => $sessionKey,
                'parent' => $apiResponse['info'],
                'item_title' => 'society-share',
                'item_class' => 'col'
            );
            $apiResponseCol = aibServiceRequest($postDataCol, 'browse');
            if ($apiResponseCol['status'] == 'OK') {
                setVisibleProperty($apiResponseCol['info']);
                $postDataSg = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'create_item',
                    '_session' => $sessionKey,
                    'parent' => $apiResponseCol['info'],
                    'item_title' => 'society-share',
                    'item_class' => 'sg'
                );
                $apiResponseSg = aibServiceRequest($postDataSg, 'browse');
                if ($apiResponseSg['status'] == 'OK') {
                    setVisibleProperty($apiResponseSg['info']);
                    return $apiResponseSg['info'];
                }
            }
        }
    }
}

function setVisibleProperty($item_id = null) {
    if ($item_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $propertyVisible = [
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'set_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id,
            'propname_1' => 'aib:visible',
            'propval_1' => 'N'
        ];
        $apiVisibleResponse = aibServiceRequest($propertyVisible, 'browse');
        return true;
    }
}

function getSearchHighlightData($item_id = '', $searchString = '') {
    if ($item_id != '' && $searchString != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "highlights",
            "_user" => 1,
            "obj_id" => $item_id,
            "word_list" => $searchString,
            "display_width" => 0,
            "display_height" => 0,
            "file_class" => "pr"
        );
        $apiResponse = aibServiceRequest($postData, 'generate_highlight_overlay');
        if ($apiResponse['status'] == 'OK') {
            return json_decode($apiResponse['info'], true);
        }
    }
}

function getHighlightImageProp($item_id = '', $searchString = '', $height = '', $width = '') {
    if ($item_id != '' && $searchString != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "highlights",
            "_user" => 1,
            "obj_id" => $item_id,
            "word_list" => $searchString,
            "display_width" => $height,
            "display_height" => $width,
            "file_class" => "pr"
        );
        $apiResponse = aibServiceRequest($postData, 'generate_highlight_overlay');
        if ($apiResponse['status'] == 'OK') {
            return json_decode($apiResponse['info'], true);
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: getSubGroupFirstItemThumb()
 * @params1: $item_id(int)---- Id of the ftree item
 * @Description: This function is used to get thumb id of an item.
 * @Dependent API: browse.php
 * @op: list
 * @return: (int) $ThumbID
 */

function getSubGroupFirstItemThumb($item_id) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request to get item list with 1st thumb id      
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $item_id,
            "opt_get_property" => 'Y'
        );
        // Service request to get item list with 1st thumb       
        $apiResponse = aibServiceRequest($postData, 'browse');
        $ThumbID = 0;
        if ($apiResponse['status'] == 'OK') {
            // used foreach loop to get 1st thumb id            
            foreach ($apiResponse['info']['records'] as $rec) {
                if (isset($rec['item_id']) and $rec['item_id'] != '' and $rec['item_type'] == 'RE' && $rec['item_source_type'] != 'L') {
                    $ThumbID = $rec['item_id'];
                    break;
                } elseif (isset($rec['item_id']) && $rec['item_type'] == 'RE' && $rec['item_source_type'] != 'L') {
                    $ThumbID = $rec['item_ref'];
                    break;
                } elseif (isset($rec['item_id']) and $rec['item_id'] != '' and $rec['item_type'] == 'IT' and $rec['item_source_type'] == 'L' && $rec['properties']['link_class'] != 'related_content') {
                    $ThumbID = $rec['item_ref'];
                    break;
                } elseif (isset($rec['item_id']) and $rec['item_id'] != '' and $rec['item_type'] == 'IT') {
                    $ThumbID = $rec['item_id'];
                    break;
                }
            }
            return $ThumbID;
        }
    } else {
        return 0;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getItemChildWithData ()
 * @params1: $item_id(int)---- Id of the ftree item
 * @Description: This function is used to get item child list 
 * @Dependent API: browse.php
 * @op: list
 * @return: (array) $apiResponse['info']['records']
 */

function getItemChildWithData($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request to get item's child list        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $item_id
        );
        // Service request to get item's child data list       
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
 * @method: getItemData ()
 * @params1: $folderId (int)---- Id of the ftree item
 * @Description: This function is used to get item data with property 
 * @Dependent API: browse.php
 * @op: get
 * @return: (array) $apiResponse['info']['records'][0] ( 1st array element value)
 */

function getItemData($folderId = '') {
    if ($folderId) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request to get item data        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get",
            "obj_id" => $folderId,
            "opt_get_field" => 'Y',
            "opt_get_files" => 'Y',
            "opt_get_property" => 'Y'
        );
        // Service request to get item data by item id        
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'][0];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: getTreeData()
 * @params1: $folderId (int)---- Id of the ftree item
 * @Description: This function is used to get item tree data of an ftree item 
 * @Dependent API: browse.php
 * @op: get_path
 * @return: (array) $apiResponse['info']['records']
 */

function getTreeData($folderId = '') {
    if ($folderId != '') {
        if (isset($_SESSION['tree_data'][$folderId])) {
            return $_SESSION['tree_data'][$folderId];
        } else {
            $sessionKey = $_SESSION['aib']['session_key'];
            // Request array to get item tree data        
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get_path",
                "obj_id" => $folderId,
                "opt_get_property" => 'Y'
            );
            // Service request to get item tree data        
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK') {
                $_SESSION['tree_data'][$folderId] = $apiResponse['info']['records'];
                return $apiResponse['info']['records'];
            }
        }
    }
}

function getParentOfTreeData($folderId = '') {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get item tree data        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_path",
            "obj_id" => $folderId
        );
        // Service request to get item tree data        
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

/*
 * @Author: Sapple Systems and STP
 * @method: getTreeDataBrief()
 * @params1: $folderId (int)---- Id of the ftree item
 * @Description: This function is used to get item tree data of an ftree item, without any properties (brief)
 * @Dependent API: browse.php
 * @op: get_path
 * @return: (array) $apiResponse['info']['records']
 */

function getTreeDataBrief($folderId = '') {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get item tree data        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_path",
            "obj_id" => $folderId,
        );
        // Service request to get item tree data        
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: aibSearchRequest()
 * @params1: $postData (array)---- Requested array to search data
 * @Description: This function is used to get search item data
 * @return: (array) $resultDataArray
 */

function aibSearchRequest($postData) {
    // Create a new curl resource
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
    // Set multiple options for a curl transfer
    curl_setopt_array($curlObj, $options);
    $result = curl_exec($curlObj);
    $resultFinal = str_replace('<status><status_value>OK</status_value></status>', '', $result);
    curl_close($curlObj);
    return $resultFinal;
}

/*
 * @Author: Sapple Systems
 * @method: aibServiceRequest()
 * @params1: $postData(array) --- Requested array to get api response
 * @params2: $fileName(string) --- File name 
 * @params3: $mail (string) (optional) --- mail 
 * @return: (array)$apiResponse
 */

function aibServiceRequest($postData, $fileName, $mail = null) {
    // Create a new curl resource
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
    // Set multiple options for a curl transfer
    curl_setopt_array($curlObj, $options);
    $result = curl_exec($curlObj);
    if ($result == false) {
        $outData = array("status" => "ERROR", "info" => curl_error($curlObj));
    } else {
        $outData = json_decode($result, true);
    }
    // close curl resource
    curl_close($curlObj);
    if (isset($outData['info']) && $outData['info'] == 'EXPIRED' && $mail == null) {
        unset($_SESSION);
        session_destroy();
        header('Location: home.php');
        exit;
    } else {
        return ($outData);
    }
}

/*
 * @Author: Sapple Systems
 * @method: get_advertisement ()
 * @params1: $folderId (int)---- Id of the ftree item
 * @Description: This function is used to get advertisement data list
 * @Dependent API: browse.php
 * @op: list
 * @return: html file with advertisement list 
 */

function get_advertisement($folder_id) {
    // Get Item tree data   
    $dataTree = getTreeData($folder_id);
    $folder_id = $dataTree[1]['item_id'];
    $sessionKey = $_SESSION['aib']['session_key'];
    if (!(isset($_SESSION['adverstisement'][$folder_id]) and count($_SESSION['adverstisement'][$folder_id]))) {
        // Prepare request araray to get           
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $folder_id,
            "opt_get_files" => 'Y'
        );
        // Service request to get advertisement list           
        $apiResponse = aibServiceRequest($postData, 'browse');
        // Used foreach loop on api response & get item property        
        foreach ($apiResponse['info']['records'] as $key => $dataArray) {
            if ($dataArray['item_type'] == 'AR') {
                // Prepare request array to get item property                 
                $apiRequestDataNewPro = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'get_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $dataArray['item_id']
                );
                // Service request to get item property                 
                $apiResponsePro = aibServiceRequest($apiRequestDataNewPro, 'browse');
                if (isset($apiResponsePro['info']['records']['type']) and $apiResponsePro['info']['records']['type'] == 'A') {
                    // Prepare request array to get item list for archive                   
                    $postData1 = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "list",
                        "parent" => $dataArray['item_id'],
                        "opt_get_files" => 'Y'
                    );
                    // Service request to get item list for archive                    
                    $apiResponse1 = aibServiceRequest($postData1, 'browse');
                    if (isset($apiResponse1['info']['records'][0]['item_type']) and $apiResponse1['info']['records'][0]['item_type'] == 'CO') {
                        // Prepare request array to get item list for collection
                        $postData2 = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_user" => 1,
                            "_op" => "list",
                            "parent" => $apiResponse1['info']['records'][0]['item_id'],
                            "opt_get_files" => 'Y'
                        );
                        // Service request to get item list for collection 
                        $apiResponse2 = aibServiceRequest($postData2, 'browse');
                        if (isset($apiResponse2['info']['records'][0]['item_type']) and $apiResponse2['info']['records'][0]['item_type'] == 'SG') {
                            // Prepare request array to get item list for sub-group
                            $postData3 = array(
                                "_key" => APIKEY,
                                "_session" => $sessionKey,
                                "_user" => 1,
                                "_op" => "list",
                                "parent" => $apiResponse2['info']['records'][0]['item_id'],
                                "opt_get_files" => 'Y'
                            );
                            // Service request to get item list for sub-group
                            $apiResponse3 = aibServiceRequest($postData3, 'browse');
                            if ($apiResponse3['status'] == 'OK') {
                                // Used foreach loop over api response to get item list, item property                                 
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
                                    $itemDetail = getItemDetailsWithProp($adItem['item_id']);
                                    $itemArray = array();
                                    if ($apiResponse4['status'] == 'OK') {
                                        // Used foreach loop over api response & check conditions                                       
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

        $_SESSION['adverstisement'][$folder_id] = $advertisementArray;
    }
    if ((isset($_SESSION['adverstisement'][$folder_id]) and count($_SESSION['adverstisement'][$folder_id]))) {
        // Used foreach loop over $_SESSION['adverstisement'][$folder_id] to get thumb url       
        foreach ($_SESSION['adverstisement'][$folder_id] as $add) {
            if ($add['id'] != '') {
                if (isset($add['url']) and $add['url'] != '') {
                    echo '<a href="' . $add['url'] . '" target="_blank"> <img style="" src="' . THUMB_URL . '?id=' . $add['id'] . '" alt="" /></a><br><br>';
                } else {
                    echo ' <img style="" src="' . THUMB_URL . '?id=' . $add['id'] . '" alt="" /><br><br>';
                }
            }
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: get_getItemListRec ()
 * @params1: $folderId (int)---  Parent item id
 * @Description: This function is used to get record item with all file 
 * @Dependent API: browse.php
 * @op: list
 * @return: (array) $records 
 */

function getItemListRec($folder_id) {
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to get list with all file    
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "list",
        "parent" => $folder_id,
        "opt_get_files" => 'Y'
    );
    // Service request array to get list with all file   
    $apiResponse = aibServiceRequest($postData, 'browse');
    $records = $apiResponse['info']['records'];
    if (isset($records[0]['item_type']) && $records[0]['item_type'] == "RE") {
        return $records = $apiResponse['info']['records'];
    } else {
        if (isset($records[0]['item_id']) && $records[0]['item_id'] > 0) {
            return getItemListRec($records[0]['item_id']);
        } else {
            return $records;
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: getAdvertisementHetarchive()
 * @params1: $folderId (int)---  Parent item id
 * @ params2: $rootId(int)--- root id
 * @Description: function is used to get advertisement list according to item id
 * @Dependent API: browse.php
 * @op: list
 * @return: (array) $records --- advertisement list
 */

function getAdvertisementHetarchive($folder_id, $rootId) {
    $depth = array("AR", "CO", "SG", "RE", "IT");
    $depthLength = count($depth);
    $addrecords = array();
    $advertisementArray = array();
    // Prepare request array to get item list with all file   
    $sessionKey = $_SESSION['aib']['session_key'];
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "list",
        "parent" => $folder_id,
        "opt_get_files" => 'Y'
    );
    // Service request to get item list with all file            
    $apiResponse = aibServiceRequest($postData, 'browse');
    $records = $apiResponse['info']['records'];
    // Used foreach loop over api response to check array index          
    foreach ($records as $key => $dataArray) {
        if (isset($dataArray['item_title']) && ($dataArray['item_title'] == 'Advertisements' || $dataArray['item_title'] == 'advertisements')) {
            $item_type = trim($dataArray['item_type']);
            // Check item type exist in array $depth              
            $index = array_search($item_type, $depth);
            if ($index < 3) {
                $addrecords = getItemListRec($dataArray['item_id']);
            } else {
                $addrecords = $dataArray;
            }
        }
    }
    // Used foreach loop over api response to get item details for each array element         
    foreach ($addrecords as $adItem) {
        // Request array to get item list for each parent item id            
        $postData4 = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $adItem['item_id'],
            "opt_get_files" => 'Y'
        );
        // Service request array to get item list for each parent item id 
        $apiResponse4 = aibServiceRequest($postData4, 'browse');
        // get item details with all property            
        $itemDetail = getItemDetailsWithProp($adItem['item_id']);
        $itemArray = array();
        if ($apiResponse4['status'] == 'OK') {
            $apiRecords = $apiResponse4['info']['records'];
            // Used for each loop over api response & check conditions                
            foreach ($apiRecords as $itemdeatil) {
                if ($itemdeatil['files'][0]['file_type'] == 'tn')
                    $itemArray['id'] = $itemdeatil['files'][0]['file_id'];
                else if ($itemdeatil['files'][1]['file_type'] == 'tn')
                    $itemArray['id'] = $itemdeatil['files'][1]['file_id'];
                else if ($itemdeatil['files'][2]['file_type'] == 'tn')
                    $itemArray['id'] = $itemdeatil['files'][2]['file_id'];
            }
        }
        // Start changes for Advertisement link
        if (!empty($itemDetail['fields'])) {
            foreach ($itemDetail['fields'] as $fieldData) {
                if ($fieldData['field_title'] == 'Advertisements_link') {
                    $advertisementsLink = $fieldData['field_value'];
                    if (false === strpos($advertisementsLink, '://')) {
                        $advertisementsLink = 'http://' . $fieldData['field_value'];
                    }
                    $itemArray['url'] = $advertisementsLink;
                    break;
                }
            }
        }
        // End changes for Advertisement link
        /* if (isset($itemDetail['prop_details']['_url']) && $itemDetail['prop_details']['_url'] != '') {
          if (false === strpos($itemDetail['prop_details']['_url'], '://')) {
          $itemDetail['prop_details']['_url'] = 'http://' . $itemDetail['prop_details']['_url'];
          }
          $itemArray['url'] = $itemDetail['prop_details']['_url'];
          } */
        $advertisementArray[] = $itemArray;
    }
    $_SESSION['adverstisement'][$folder_id] = $advertisementArray;
    // get image of random adevrtisement            
    randomAdvertisement($_SESSION['adverstisement'], $folder_id);
}

/*
 * @Author: Sapple Systems
 * @method: randomAdvertisement()
 * @params1: $ad (array)
 * @ params2: $folder_id (int)--- item id of ftree
 * @Description: This function is used to advertisement according to item id
 * @Dependent API: browse.php
 * @op: list
 * @return: (array) $records --- image with anchor tag
 */

function randomAdvertisement($ad, $folder_id) {
    $maxLength = 3;
    // Get item tree data    
    $itemParentArray = getTreeData($folder_id);
    // Get item details with property     
    $parentWithProps = getItemDetailsWithProp($folder_id);
    $firstFolderId = isset($itemParentArray[1]['item_id']) ? $itemParentArray[1]['item_id'] : "";
    if ((!isset($ad[$folder_id]) || count($ad[$folder_id]) <= 0)) {
        $ad[$folder_id] = array();
    }
    if ((!isset($ad[$firstFolderId]) || count($ad[$firstFolderId]) <= 0)) {
        $ad[$firstFolderId] = array();
    }
    if ($parentWithProps['item_type'] == 'AR' || $parentWithProps['item_type'] == 'CO' || $parentWithProps['item_type'] == 'SG') {
        if (isset($parentWithProps['prop_details']['display_archive_level_advertisements']) && $parentWithProps['prop_details']['display_archive_level_advertisements'] == 1) {
            $adList = array_merge($ad[$folder_id], $ad[$firstFolderId]);
        } else {
            $adList = $ad[$folder_id];
        }
    } else {
        $adList = array_merge($ad[$folder_id], $ad[$firstFolderId]);
    }
    shuffle($adList);
    $count = 1;
    $storage = "";
    // Used foreach loop over $adList & check conditions   
    foreach ($adList as $add) {
        if ($storage != "" && $storage == $add['id']) {
            break;
        } else {
            $storage = $add['id'];
        }
        if ($count > $maxLength)
            break;
        if (isset($add['url']) && $add['url'] != '') {
            echo '<a href="' . $add['url'] . '" target="_blank"> <img style="" src="' . THUMB_URL . '?id=' . $add['id'] . '" alt="" /></a><br><br>';
        } else {
            echo ' <img style="" src="' . THUMB_URL . '?id=' . $add['id'] . '" alt="" /><br><br>';
        }
        $count++;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getItemProperty ()
 * @params1: $item_id(int)---- Id of the ftree item
 * @Description: This function is used to get item property 
 * @Dependent API: browse.php
 * @op: get_item_prop
 * @return: (array) $itemDetails
 */

function getItemProperty($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request to get item property         
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        // Service request to get item property         
        $apiResponse = aibServiceRequest($apiRequestData, 'browse');
        $itemDetails = $apiResponse['info']['records'];
        return $itemDetails;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getItemDetailsWithProp ()
 * @params1: $item_id(int)---- Id of the ftree item
 * @Description: This function is used to get item details with all property  
 * @Dependent API: browse.php
 * @op: get_item_prop
 * @return: (array) $itemDetails
 */

function getItemDetailsWithProp($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Prepare request to get item details with all property       
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $item_id
        );
        // Service request to get item details with all property
        $apiResponse = aibServiceRequest($apiRequestData, 'browse');
        // Get item data of item id         
        $itemDetails = getItemData($item_id);
        $itemDetails['prop_details'] = $apiResponse['info']['records'];
        return $itemDetails;
    } else {
        return false;
    }
}

/*
 * @Author: Sapple Systems
 * @method: get_item_prop_byuser ()
 * @params1: $item_id(int)---- Id of the ftree item
 * @Description: This function is used to get item property by user   
 * @Dependent API: browse.php
 * @op: get_item_prop
 * @return: (array) $responseData
 */

function get_item_prop_byuser($item_id) {
    // Prepare request array to get item property    
    $apiRequestData = array(
        '_key' => APIKEY,
        '_user' => $_SESSION['aib']['user_data']['user_id'],
        '_op' => 'get_item_prop',
        '_session' => $sessionKey,
        'obj_id' => $item_id
    );
    //service request to get item property
    $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
    $responseData = $apiResponseProps['info']['records']['share_user'];
    return $responseData;
}

/*
 * @Author: Sapple Systems
 * @method: applyUserFilter ()
 * @params1: $apiResponse (array)---- Array where we apply filter 
 * @params2: $filter (string)---- Filter , which is applied to get data  
 * @Description: This function is used to get filtered archive list   
 * @return: (array) Filtered user list
 */

function applyUserFilter($apiResponse, $filter) {
    if (!empty($filter)) {
        // Used foreach loop over api response to apply filter on each array element		
        foreach ($apiResponse['info']['records'] as $key => $apiResponseVal) {

            $matchStatus = true;
            $state = isset($apiResponseVal['property_list']['archive_display_state']) ? ucwords(strtolower($apiResponseVal['property_list']['archive_display_state'])) : "";
            // Check state match condition           
            if (!empty($filter['state']) && $filter['state'] != $state) {
                $matchStatus = false;
            }
            // Check county match condition 
            $county = isset($apiResponseVal['property_list']['archive_display_county']) ? ucwords(strtolower($apiResponseVal['property_list']['archive_display_county'])) : "";
            if (!empty($filter['county']) && $filter['county'] != $county) {
                $matchStatus = false;
            }
            // Check city match condition
            $city = isset($apiResponseVal['property_list']['archive_display_city']) ? ucwords(strtolower($apiResponseVal['property_list']['archive_display_city'])) : "";
            if (!empty($filter['city']) && $filter['city'] != $city) {
                $matchStatus = false;
            }
            // Check zip match condition
            if (!empty($filter['zip']) && $filter['zip'] != $apiResponseVal['property_list']['archive_display_zip']) {
                $matchStatus = false;
            }
            if (!$matchStatus) {
                unset($apiResponse['info']['records'][$key]);
            }
        }
    }
    return $apiResponse;
}

/*
 * @Author: Sapple Systems
 * @method: applyUserFilterOriData()
 * @params1: $apiResponse (array)---- Array where we apply filter 
 * @params2: $filter (string)---- Filter , which is applied to get data  
 * @Description: This function is used to get filtered archive list 
 * @return: (array) Filtered user list
 */

function applyUserFilterOriData($apiResponse, $filter) {
    if (!empty($filter)) {
        //print_r($apiResponse['info']['records']);
        // Used foreach loop over api response & check conditions        
        foreach ($apiResponse['info']['records'] as $key => $apiResponseVal) {

            $matchStatus = true;
            $state = isset($apiResponseVal['properties']['archive_display_state']) ? ucwords(strtolower($apiResponseVal['properties']['archive_display_state'])) : "";
            // Check state match condition
            if (!empty($filter['state']) && $filter['state'] != $state) {
                $matchStatus = false;
            }
            $county = isset($apiResponseVal['properties']['archive_display_county']) ? ucwords(strtolower($apiResponseVal['properties']['archive_display_county'])) : "";
            // Check county match condition
            if (!empty($filter['county']) && $filter['county'] != $county) {
                $matchStatus = false;
            }
            $city = isset($apiResponseVal['properties']['archive_display_city']) ? ucwords(strtolower($apiResponseVal['properties']['archive_display_city'])) : "";
            // Check city match condition
            if (!empty($filter['city']) && $filter['city'] != $city) {
                $matchStatus = false;
            }
            // Check zip match condition
            if (!empty($filter['zip']) && $filter['zip'] != $apiResponseVal['properties']['archive_display_zip']) {
                $matchStatus = false;
            }
            if (!$matchStatus) {
                unset($apiResponse['info']['records'][$key]);
            }
        }
    }
    return $apiResponse;
}

/*
 * @Author: Sapple Systems
 * @method: applyPublicUserFilter()
 * @params1: $apiResponse (array)---- Array where we apply filter 
 * @params2: $filter (string)---- Filter , which is applied to get data  
 * @Description: This function is used to get filtered archive list for people 
 * @return: (array) Filtered user list
 */

function applyPublicUserFilter($apiResponse, $filter) {
    if (!empty($filter)) {
        // Used foreach loop over api response & check conditions        
        foreach ($apiResponse['info']['records'] as $key => $apiResponseVal) {

            $matchStatus = true;
            $state = isset($apiResponseVal['default_property']['archive_display_state']) ? ucwords(strtolower($apiResponseVal['default_property']['archive_display_state'])) : "";
            // Check state match condition
            if (!empty($filter['state']) && $filter['state'] != $state) {
                $matchStatus = false;
            }
            $county = isset($apiResponseVal['default_property']['archive_display_county']) ? ucwords(strtolower($apiResponseVal['default_property']['archive_display_county'])) : "";
            // Check county match condition
            if (!empty($filter['county']) && $filter['county'] != $county) {
                $matchStatus = false;
            }
            $city = isset($apiResponseVal['default_property']['archive_display_city']) ? ucwords(strtolower($apiResponseVal['default_property']['archive_display_city'])) : "";
            // Check city match condition
            if (!empty($filter['city']) && $filter['city'] != $city) {
                $matchStatus = false;
            }
            // Check zip match condition
            if (!empty($filter['zip']) && $filter['zip'] != $apiResponseVal['default_property']['archive_display_zip']) {
                $matchStatus = false;
            }
            if (!$matchStatus) {
                unset($apiResponse['info']['records'][$key]);
            }
        }
    }
    return $apiResponse;
}

/*
 * @Author: Sapple Systems
 * @method: getItemChildCount ()
 * @params1: $item_id(int)---- Id of the ftree item
 * @params1: $item_type (string)---- Type of ftree item  
 * @Description: This function is used to get child count of ftree item
 * @Dependent API: browse.php
 * @op: list
 * @return: (int) child_count
 */

function getItemChildCount($item_id = null, $item_type = null) {
    if ($item_type != "RE") {
        if ($item_id) {
            $sessionKey = $_SESSION['aib']['session_key'];
            // Request array to get item list           
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list",
                "parent" => $item_id,
                "opt_get_property" => 'Y',
                "opt_get_long_prop" => 'Y'
            );
            // Service request array to get item list
            $apiResponse = aibServiceRequest($postData, 'browse');
            $sgCount = 0;
            // Used foreach loop over api response & check conditions           
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                if ($dataArray['item_title'] == 'Advertisements') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if ($item_type == 'AG' || $item_type == 'AR') {
                    if ($dataArray['is_link'] == 'Y') {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                if ($item_type == 'SG') {
                    if (isset($dataArray['properties']['link_class']) && $dataArray['properties']['link_class'] == 'public') {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
            }
            $deactiveRecords = 0;
            if ($item_type == 'CO' || $item_type == 'SG') {
                // Used foreach loop over api response to check item type                
                foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                    if ($dataArray['item_type'] == 'SG') {
                        $sgCount ++;
                    }
                    if ($dataArray['item_type'] == 'RE') {
                        $itemProperty = $dataArray['properties']; //getItemProperty($dataArray["item_id"]);
                        $sheare_user = json_decode($itemProperty['share_user']);
                        if ($itemProperty['aib:visible'] == 'N' || ($itemProperty['aib:visible'] == 'Y' && $itemProperty['aib:private'] == 'Y' && (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) == true)) {
                            $deactiveRecords++;
                        }
                    }
                }
            }
            $actualRecords = count($apiResponse['info']['records']) - $sgCount - $deactiveRecords;
            return ['child_count' => ($actualRecords), 'sg_count' => $sgCount];
        }
    } else {
        return false;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getProfileInfo ()
 * @params1: $userId (int)---- User Id
 * @Description: This function is used to get user profile details 
 * @Dependent API: users.php
 * @op: get_profile
 * @return: (array) $apiResponse
 */

function getProfileInfo($userId) {
    if (isset($userId)) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile information        
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'get_profile',
            '_session' => $sessionKey,
            'user_id' => $userId
        );
        // Service request to get user profile information        
        $apiResponse = aibServiceRequest($apiRequestData, 'users');
        return $apiResponse;
    }
}

/*
 * @Author: Sapple Systems
 * @method: generateCommentTitle()
 * @params1: $userId (int)---- User Id
 * @Description: This function is used to get user comment title 
 * @return: (string) $userTitle
 */

function generateCommentTitle($userId) {
    // Get user profile details   
    $apiResponse = getProfileInfo($userId);
    if (isset($apiResponse['info'])) {
        $userTitle = $apiResponse['info']['user_title'] . "-" . time() . "-" . $userId;
        return $userTitle;
    }
}

/*
 * @Author: Sapple Systems
 * @method: addCommentToThread()
 * @params1: $userId (int)---- User Id
 * @Description: This function is used to save user comment
 * @Dependent API: comments.php
 * @op: cmnt_addcmnt
 * @return: (array) $apiResponse
 */

function addCommentToThread($post) {
    $parent_thread = $post['parent_thread'];
    $comment_text = $post['comment_text'];
    $comment_title = $post['title'];
    if ($parent_thread) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to save user comment            
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "cmnt_addcmnt",
            "parent_thread" => $parent_thread,
            "comment_title" => $comment_title,
            "comment_text" => $comment_text,
            "user_id" => $_SESSION['aib']['user_data']['user_id']
        );
        // Service request array to save user comment 
        $apiResponse = aibServiceRequest($postRequestData, 'comments');
        return $apiResponse;
    }
}

function listCommentsInThread($object_id) {
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to get comment thread list         
    if ($object_id) {
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1, //$_SESSION['aib']['user_data']['user_id'],
            "_op" => "cmnt_lstcmnt",
            "parent_thread" => $object_id
        );
        // Service request array to get comment thread list  
        $apiResponse = aibServiceRequest($postRequestData, 'comments');
        return $responseArray = $apiResponse['info']['records'];
    }
}

/*
 * @Author: Sapple Systems
 * @method: listCommentsInThreadRecursive()
 * @params1: $object_id (int)---- Parent Thread id 
 * @Description: This function is used get recursive comment list 
 * @return: (array) $mainArray
 */

function listCommentsInThreadRecursive($object_id) {
    $responseArray = listCommentsInThread($object_id);
    if (isset($responseArray) && count($responseArray) > 0) {
        // Used foreach loop over $responseArray to get $mainArray array         
        foreach ($responseArray as $responseInArray) {
            $responseInArray['commentsThreads'] = listCommentsInThreadRecursive($responseInArray['item_id']);
            $mainArray[] = $responseInArray;
        }
        return $mainArray;
    }
}

/*
 * @Author: Sapple Systems
 * @method: sendMail ()
 * @params1: $email_content (array)---- Email content ( To, subject, reply, From ) 
 * @params2: $template (html Template)---- Html email template
 * @Description: This function is used send email 
 * @Dependent API: email.php
 * @op: send
 * @return: Boolean response (True , False ) 
 */

function sendMail($email_content, $template) {
    $sessionKey = $_SESSION['aib']['session_key'];
    if (!empty($template) && !empty($email_content)) {
        $year = date("Y");
        $logo = '<img src="' . HOST_ROOT_IMAGE_PATH . 'logo-aib.png" alt="ArchiveInABox Logo" />';
        $header_image = '<img style="width:100%;" src="' . HOST_ROOT_IMAGE_PATH . 'mail-template_header.jpg" alt="Image" />';
        // Set Default email template logo       
        $template = str_replace('#logo#', $logo, $template);
        // Set Default email template header image
        $template = str_replace('#header_images#', $header_image, $template);
        // Set Default email template year
        $template = str_replace('#year#', $year, $template);
        // Request array to send email       
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
        // Service request to send email 
        $apiResponse = aibServiceRequest($postData, 'email', 'send');
        return true;
    }
}

/*
 * @Author: Sapple Systems
 * @method: setUserProfileStatus ()
 * @params1: $user_id (int)---- User Id
 * @params2: $status (string)---- Active(a) ,Deactive(d) 
 * @Description: This function is used set user profile property 
 * @Dependent API: users.php
 * @op: set_profile_prop
 * @return: Boolean response (True , False ) 
 */

function setUserProfileClaimedStatus($user_id, $status) {
    $userDetails = getUserProfileById($user_id);
    $user_archive_id = $userDetails['user_top_folder'];
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to set user profile property

    $ClaimedStatusProperty[0]['name'] = 'claimed_user_approved';
    $ClaimedStatusProperty[0]['value'] = $status;
    $ClaimedStatusProperty[1]['name'] = 'admin_action';
    $ClaimedStatusProperty[1]['value'] = '1';
    $ClaimedStatusProperty[2]['name'] = 'claimed_user';
    $ClaimedStatusProperty[2]['value'] = '1';
    $ClaimedStatusProperty[3]['name'] = 'claimed_user2';
    $ClaimedStatusProperty[3]['value'] = '1';
    if ($status == 'Y') {
        $ClaimedStatusProperty[2]['name'] = 'claimed_user';
        $ClaimedStatusProperty[2]['value'] = '2';
        $ClaimedStatusProperty[3]['name'] = 'claimed_user2';
        $ClaimedStatusProperty[3]['value'] = '2';
    }
    $postUserPropData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "set_profile_prop_batch",
        "user_id" => $user_id,
        "property_list" => json_encode($ClaimedStatusProperty)
    );

    // Service request to set user property                                           
    $apiResponse = aibServiceRequest($postUserPropData, 'users');

    if ($apiResponse['status'] == 'OK') {
        if ($flg == 0) {
            // Set User archive status a/d                
            return setUserArchiveStatus($user_archive_id, $status);
        } else {
            return true;
        }
    }
}

function setUserProfileStatus($user_id, $status, $flg = '') {
    // Get user profile by user id    
    $userDetails = getUserProfileById($user_id);
    $user_archive_id = $userDetails['user_top_folder'];
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to set user profile property         
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "set_profile_prop",
        "user_id" => $user_id,
        "property_name" => 'status',
        "property_value" => $status
    );
    // Service request array to set user profile property        
    $apiResponse = aibServiceRequest($postData, 'users');
    if ($apiResponse['status'] == 'OK') {
        if ($flg == 0) {
            // Set User archive status a/d                
            return setUserArchiveStatus($user_archive_id, $status);
        } else {
            return true;
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: getUserProfileById ()
 * @params1: $user_id (int)---- User Id 
 * @Description: This function is used get user profile 
 * @Dependent API: users.php
 * @op: get_profile
 * @return:(array) $apiResponse['info'];
 */

function getUserProfileById($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_profile",
            "user_id" => $user_id
        );
        // Service request array to get user profile        
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info'];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: setUserArchiveStatus()
 * @params1: $user_id (int)---- User Id
 * @params2: $status (string)---- Active(a) ,Deactive(d) 
 * @Description: This function is used set user archive status (a/d) 
 * @Dependent API: browse.php
 * @op: set_item_prop
 * @return: Boolean response (True, False ) 
 */

function setUserArchiveStatus($archive_id = null, $status) {
    if ($archive_id) {
        $status = ($status == 'd') ? 0 : 1;
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to set item property         
        $postData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'set_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $archive_id,
            'propname_1' => 'status',
            'propval_1' => $status,
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getUsersAllProperty ()
 * @params1: $user_id (int)---- User Id 
 * @Description: This function is used get user all property 
 * @Dependent API: users.php
 * @op: list_profile_prop
 * @return: (array)$propertyList
 */

function getUsersAllProperty($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile property list        
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list_profile_prop",
            "user_id" => $user_id
        );
        // Service request array to get user profile property list 
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            $propertyList = [];
            // Used foreach loop over api response to get $propertyList            
            foreach ($apiResponse['info']['records'] as $propertyData) {
                $propertyList[$propertyData['property_name']] = $propertyData['property_value'];
            }
            return $propertyList;
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: checkArchiveStatus ()
 * @params1: $userTopFolder (int)----  Archive Id 
 * @params2: $userType (String)----  User Type (A, U, S)
 * @Description: This function is used get user all property 
 * @return: Boolean (True / False )
 */

function checkArchiveStatus($userTopFolder = null, $userType = null) {
    if ($userTopFolder && $userType) {
        if ($userType == 'R') {
            return true;
        } else {
            // Get item details with all property            
            $archiveDetails = getItemDetailsWithProp($userTopFolder);
            if ($archiveDetails['item_type'] == 'AG') {
                return ($archiveDetails['prop_details']['status'] == 1) ? true : false;
            } else {
                // Get tree data                 
                $parentsData = getTreeData($userTopFolder);
                $archiveGroupId = isset($parentsData[1]['item_id']) ? $parentsData[1]['item_id'] : $parentsData[0]['item_id'];
                // Get item details with all property
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
 * @method: checkEmailExist ()
 * @params1: $email (string)----  email id
 * @Description: This function is used get user username if email id exist in database 
 * @Dependent API: users.php
 * @op: user_matching_prop
 * @return: (array) $usernmae (user username)
 */

function checkEmailExist($email) {
    $sessionKey = $_SESSION['aib']['session_key'];
    $apiEmailSearch = array(
        '_key' => APIKEY,
        '_user' => (isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1,
        '_op' => 'user_matching_prop',
        '_session' => $sessionKey,
        'property_name' => 'email',
        'property_value' => $email
    );
    $apiResponseEmail = aibServiceRequest($apiEmailSearch, 'users');
    if ($apiResponseEmail['status'] == 'OK' && count($apiResponseEmail['info']['records']) > 0) {
        $i = 0;
        foreach ($apiResponseEmail['info']['records'] as $key => $dataVal) {
            if ($dataVal['user_type'] == 'U') {
                $usernmae[$i] = $dataVal['user_login'];
                ++$i;
            }
        }
    } else {
        $usernmae = '';
    }
    return $usernmae;
}

/*
 * @Author: Sapple Systems
 * @method: emailList()
 * @params1: $shared_user_list (array)---- shared username list
 * @Description: This function is used get user username if email id exist in database 
 * @return: (array) $emails (user emails)
 */

function emailList($shared_user_list) {
    $emails = [];
    // Used foreach loop over share user list  to get user profile of each user    
    foreach ($shared_user_list as $user_login) {
        // Get user profile data            
        $userDetails = getUserProfile('', $user_login);
        if (!empty($userDetails)) {
            // Get user profile property             
            $user_email = getUserProfileProperties($userDetails['user_id'], 'email');
            $emails[] = $user_email['email'];
        }
    }
    return $emails;
}

/*
 * @Author: Sapple Systems
 * @method: getUserProfile ()
 * @params1: $userId (int)----  User Id
 * @params2: $loginId (string)----  User login Id
 * @Description: This function is used get user profile by user id or user login id
 * @Dependent API: users.php
 * @op: get_profile
 * @return: (array) $apiResponse['info']
 */

function getUserProfile($userId = '', $loginId = '') {
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to get user profile    
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "get_profile"
    );
    if (!empty($userId)) {
        $postData["user_id"] = $userId;
    }
    if (!empty($loginId)) {
        $postData["user_login"] = $loginId;
    }
    // Service request to get user profile    
    $apiResponse = aibServiceRequest($postData, 'users');
    if ($apiResponse['status'] == 'OK') {
        return $apiResponse['info'];
    }
}

/*
 * @Author: Sapple Systems
 * @method: getUserProfileProperties()
 * @params1: $userId (int)----  User Id
 * @params2: $property_name (string)----  Property name
 * @Description: This function is used get user profile property by user id and property name
 * @Dependent API: users.php
 * @op: get_profile_prop
 * @return: (array) $userPropertyList
 */

function getUserProfileProperties($user_id = null, $property_name) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile property         
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_profile_prop", //list_profile_prop
            "user_id" => $user_id,
            "property_name" => $property_name
        );
        // Service request array to get user profile property
        $apiResponse = aibServiceRequest($postData, 'users');
        $userPropertyList = [];
        if ($apiResponse['status'] == 'OK') {
            $userPropertyList[$apiResponse['info']['property_name']] = $apiResponse['info']['property_value'];
        }
        return $userPropertyList;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getUserProfileProperties()
 * @params1: $sharedUserList (array)----  Shared user list
 * @params2: $record_id (int)---- Record id
 * @Description: This function is used create share link with record  
 * @Dependent API: sharing.php
 * @op: share_create
 * @return: (array) $apiResponse
 */

function sharedRecordWithUser($sharedUserList = [], $record_id = null) {
    if (!empty($sharedUserList)) {
        $sharing_user = $_SESSION['aib']['user_data']['user_id'];
        $sessionKey = $_SESSION['aib']['session_key'];
        // Used foreach loop over shared user list to get user profile of each user        
        foreach ($sharedUserList as $userKey => $user_login) {
            // Get user profile            
            $userDetails = getUserProfile('', $user_login);
            $shared_user = $userDetails['user_id'];
            $shared_user_archive_id = $userDetails['user_top_folder'];
            // Check,   record already shared with user            
            $checkedForShared = checkForAlreadySharedWithUser($shared_user, $record_id, $shared_user_archive_id);
            if ($checkedForShared == 0) {
                // Request array to create share link with record & user             
                $postData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => "1",
                    "_op" => "share_create",
                    "share_target" => $shared_user_archive_id,
                    "share_source" => $record_id,
                    "share_title" => "Shared $record_id at " . date('Y-m-d h:i:s'),
                    "share_item_user" => (isset($sharing_user) && !empty($sharing_user) ) ? $sharing_user : 1,
                );
                // Service request array to create share link with record & user                
                $apiResponse = aibServiceRequest($postData, 'sharing');
            }
        }
    }
}

function shareItemWithAdmin($shared_user = null, $record_id = null, $share_id = null) {
    if ($shared_user != null && $record_id != null && $share_id != null) {
        $sharing_user = $_SESSION['aib']['user_data']['user_id'];
        $sessionKey = $_SESSION['aib']['session_key'];
        $checkedForShared = checkForAlreadySharedWithUser($shared_user, $record_id, $share_id);
        if ($checkedForShared == 0) {
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => "1",
                "_op" => "share_create",
                "share_target" => $share_id,
                "share_source" => $record_id,
                "share_title" => "Shared $record_id at " . date('Y-m-d h:i:s'),
                "share_item_user" => (isset($sharing_user) && !empty($sharing_user) ) ? $sharing_user : 1,
            );
            // Service request array to create share link with record & user                
            $apiResponse = aibServiceRequest($postData, 'sharing');
            if ($apiResponse['status'] == 'OK') {
                $shared_id = $apiResponse['info'];
                $apiRequestDataItem = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $shared_id,
                    'propname_1' => 'shared_with_user_id',
                    'propval_1' => $shared_user
                );
                $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
            }
        }
    }
    return true;
}

/*
 * @Author: Sapple Systems
 * @method: getArchiveAdministratorEmails ()
 * @params1: $folder_id (int)---- Item id of ftree data
 * @params2: $user_type (string)---- User type (A, U ,S)
 * @Description: This function is used to get  admin�s email id
 * @Dependent API: users.php
 * @op: list_profiles
 * @return: (string) $return � comma separate email id�s 
 */

function getArchiveAdministratorEmails($folder_id = null, $user_type = '') {
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to user profile list        
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "list_profiles",
        "user_type" => ($user_type != '') ? $user_type : 'A',
        "user_top_folder" => $folder_id,
        "_prop" => 'Y'
    );
    // Service request to get user profile list        
    $apiResponse = aibServiceRequest($postData, 'users');
    if ($apiResponse['status'] == 'OK') {
        // Used foreach loop over api response to get email id of each user             
        foreach ($apiResponse['info']['records'] as $record) {
            if (isset($record['_properties']['email']) && !empty($record['_properties']['email'])) {
                $emailId[] = urldecode($record['_properties']['email']);
            }
        }
    }
    $return = implode(',', $emailId);
    return $return;
}

/*
 * @Author: Sapple Systems
 * @method: checkForAlreadySharedWithUser ()
 * @params1: $shared_user_id (int)---- User Id
 * @params2: $record_id (int)---- Item id of ftree
 * @params3: $shared_user_archive_id (int)---- archive id of ftree
 * @Description: This function is used to get no of share user 
 * @Dependent API: sharing.php
 * @op: share_list
 * @return: (int) $count� no of shared user
 */

function checkForAlreadySharedWithUser($shared_user_id = null, $record_id = null, $shared_user_archive_id = null) {
    if ($shared_user_id && $record_id && $shared_user_archive_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get share list        
        $PostDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => "1",
            "_op" => "share_list",
            "perspective" => 'shared_from_user',
            "user_id" => $_SESSION['aib']['user_data']['user_id'],
        );
        $count = 0;
        // Service request to get share list        
        $apiResponse = aibServiceRequest($PostDataArray, 'sharing');
        if ($apiResponse['status'] == 'OK') {
            $sharedWithOtherList = $apiResponse['info']['records'];
            if (!empty($sharedWithOtherList)) {
                // Used foreach loop over share with other list to get count                 
                foreach ($sharedWithOtherList as $key => $sharedDataArray) {
                    if ($sharedDataArray['item_ref'] == $record_id && $sharedDataArray['item_parent'] == $shared_user_archive_id) {
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
 * @method: getItemListRecord ()
 * @params1: $parent_id (int)---- Item id of ftree
 * @Description: This function is used to get record list 
 * @Dependent API: browse.php
 * @op: list
 * @return: (array) $apiResponse
 */

function getItemListRecord($parent_id = null) {
    $sessionKey = $_SESSION['aib']['session_key'];
    // Request array to get item list of ftree        
    $anonyPost = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "list",
        "parent" => $parent_id,
        "opt_get_files" => 'Y',
        "opt_get_first_thumb" => 'Y',
        "opt_get_property" => 'Y',
        "opt_deref_links" => 'Y'
    );
    // Service request to get item list of ftree    
    $apiResponse = aibServiceRequest($anonyPost, 'browse');
    return $apiResponse;
}

/*
 * @Author: Sapple Systems
 * @method: getSuperAdminAllProperty ()
 * @params1: $user_id (int)---- User Id (super admin)
 * @Description: This function is used to all property of super admin
 * @Dependent API: users.php
 * @op: list_profile_prop
 * @return: (array) $propertyList ---- All super admin property list
 */

function getSuperAdminAllProperty($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile list        
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list_profile_prop",
            "user_id" => $user_id
        );
        // Service request array to get user profile list 
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            $propertyList = [];
            // Used foreach loop over api response to get property list             
            foreach ($apiResponse['info']['records'] as $propertyData) {
                $propertyList[$propertyData['property_name']] = $propertyData['property_value'];
            }
            return $propertyList;
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: getAssistantAssignedRecords ()
 * @params1: $user_id (int)---- User Id (assistant)
 * @Description: This function is used to get all record list to assign assistant 
 * @Dependent API: dataentry.php
 * @op: data_entry_waiting
 * @return: (array) $assignedRecords---- Record to assign assistant  
 */

function getAssistantAssignedRecords($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get uncompleted data entry items for user        
        $uncompleteRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'data_entry_waiting',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        // Service request to get uncompleted data entry items for user
        $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
        $assignedRecords = [];
        if ($uncompleteResponse['status'] == 'OK') {
            // Used foreach loop over api response to get complete data entry             
            foreach ($uncompleteResponse['info']['records'] as $unCompleteDataArray) {
                $assignedRecords[] = $unCompleteDataArray['item_id'];
            }
        }
        // Request array to get completed data entry items for user
        $completeRequestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'data_entry_complete',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        // Service request to get completed data entry items for user
        $completeResponse = aibServiceRequest($completeRequestData, 'dataentry');
        if ($completeResponse['status'] == 'OK') {
            // Used foreach loop over api response to get $assignedRecords             
            foreach ($completeResponse['info']['records'] as $completeDataArray) {
                $assignedRecords[] = $completeDataArray['item_id'];
            }
        }
        return $assignedRecords;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getArchiveOwner ()
 * @params1: $folderId (int)---- Item id of ftree
 * @Description: This function is used to get archive owner details with property 
 * @Dependent API: users.php
 * @op: list_profiles
 * @return: (array) $apiResponse['info']['records'][0]----  Archive owner details 
 */

function getArchiveOwner($folderId = null, $type = 'U') {
    if ($folderId) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to get user profile list        
        $requestData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_session' => $sessionKey,
            '_op' => 'list_profiles',
            'user_type' => $type,
            'user_top_folder' => $folderId
        );
        // Service request to get user profile list  
        $apiResponse = aibServiceRequest($requestData, 'users');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'][0];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function createUserScrapbookRoot($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request array to create scrapbook for public user         
        $postDataItem = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'create_item',
            '_session' => $sessionKey,
            'parent' => $_SESSION['aib']['user_data']['user_top_folder'],
            'item_title' => 'Scrapbooks',
            'item_owner_id' => $user_id,
            'item_owner_group' => 1
        );
        // Service request to create scrapbook for public user
        $apiResponse = aibServiceRequest($postDataItem, 'browse');
        return $apiResponse['info'];
    }
}

function apiFilterPaginationData($apiResponse, $start, $ITEM_COUNT_PER_PAGE) {
    $returnArray['status'] = $apiResponse['status'];
    for ($c = $start; $c < ($start + $ITEM_COUNT_PER_PAGE); $c++) {
        if (isset($apiResponse['info']['records'][$c]))
            $returnArray['info']['records'][] = $apiResponse['info']['records'][$c];
    }
    return $returnArray;
}

function apiFilterArchivePaginationData($apiResponse, $start, $ITEM_COUNT_PER_PAGE) {
    $returnArray['status'] = $apiResponse['status'];
    $apiResponse['info']['records'] = array_values($apiResponse['info']['records']);
    for ($c = $start; $c < ($start + $ITEM_COUNT_PER_PAGE); $c++) {
        if (isset($apiResponse['info']['records'][$c]))
            $returnArray['info']['records'][] = $apiResponse['info']['records'][$c];
    }
    return $returnArray;
}

function apiFilterRecordPaginationData($apiResponse, $start, $ITEM_COUNT_PER_PAGE) {
    $returnArray['status'] = $apiResponse['status'];
    $apiResponse['records'] = array_values($apiResponse['records']);
    for ($c = $start; $c < ($start + $ITEM_COUNT_PER_PAGE); $c++) {
        if (isset($apiResponse['records'][$c]))
            $returnArray['records'][] = $apiResponse['records'][$c];
    }
    return $returnArray;
}

function RecordsIdsSession($folderId) {
    $responseData = array('status' => 'error', 'message' => 'record ids session has been not prepared');
    $treeDataArray = getTreeData($folderId);
    $parentIndex = $treeDataArray[count($treeDataArray) - 2];
    $currentParentId = isset($parentIndex['item_id']) ? $parentIndex['item_id'] : '';
    $itemData = getItemData($currentParentId);
    // Get ftree data
    $search_filter = array(array('name' => 'aibftype', "value" => "rec"), array('name' => 'aibftype', "value" => "sg"), array('name' => 'aibftype', "value" => "col"), array('name' => 'aibftype', "value" => "IT"), array('name' => 'aibftype', "value" => "scrpbkent"), array('name' => 'aib:private', "value" => "Y"), array('name' => 'link_class', "value" => "public"), array('name' => 'visible_to_public', "value" => 0));
    // Request array to get item tree details of a ftree item       
    $sessionKey = $_SESSION['aib']['session_key'];

    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "list",
        "parent" => $currentParentId,
        "opt_get_files" => 'Y',
        "opt_deref_links" => 'Y',
        "opt_get_property" => 'Y',
        "opt_get_long_prop" => 'Y',
        "opt_get_prop_count" => 'Y',
        "opt_get_link_source_properties" => 'Y',
        "opt_prop_count_set" => json_encode($search_filter)
    );
    // Service request to get item tree details of a ftree item 
    $apiResponse = aibServiceRequest($postData, 'browse');
    if ($parentIndex['item_type'] == 'AR') {
        if (isset($apiResponse['info']['records'])) {
            // Used foreach loop on api response & check conditions            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $defaultItemProperty = $dataArray['properties']; //getItemProperty($dataArray['item_id']);
                // Check root user & unset $apiResponse['info']['records'][$key]                
                if ($folderId == PUBLIC_USER_ROOT) {
                    if (!isset($defaultItemProperty['status']) || $defaultItemProperty['status'] == 0) {
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                $apiResponse['info']['records'][$key]['default_property'] = $defaultItemProperty;
                // Get item details with all item property                 
                $item_property = getItemDetailsWithProp($dataArray['item_id']);
                // Check item title equal to advertisements                 
                if (strtolower($dataArray['item_title']) == 'advertisements') {
                    unset($apiResponse['info']['records'][$key]);
                }
                // Check visible_to_public equal to 0
                if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }

                if (isset($dataArray['properties']['publish_status']) && $dataArray['properties']['publish_status'] == 'N') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // Check link_class equal to related_content
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'related_content') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // Check item_title equal to shared out of system
                if (strtolower($dataArray['item_title']) == 'shared out of system') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // Check item_type equal to RE 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'RE') {
                    $itemProperty = $dataArray['properties'];
                    $apiResponse['info']['records'][$key]['RE_property'] = $itemProperty;
                    $sheare_user = json_decode($itemProperty['share_user']);
                    if ($itemProperty['aib:visible'] == 'N' || ($itemProperty['aib:visible'] == 'Y' && $itemProperty['aib:private'] == 'Y' && (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) == true)) {
                        unset($apiResponse['info']['records'][$key]);
                    }
                }
                if (isset($dataArray['is_link']) && $dataArray['is_link'] == 'Y') {
                    // unset($apiResponse['info']['records'][$key]);
                }
            }
        }
        // Get item tree data by folder id ( item parent id)        
        $treeDataArray = getTreeData($folderId);
        // Check item title Scrapbooks         
        if ($treeDataArray[count($treeDataArray) - 1]['item_title'] == 'Scrapbooks') {
            // Used foreach loop over api response for get item details with all property of each array element            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                // Get item details with all property                 
                $item_property = getItemDetailsWithProp($dataArray['item_id']);
                // Check scrapbook type public               
                if ($item_property['prop_details']['scrapbook_type'] != 'public') {
                    if (!empty($_SESSION['aib']['user_data']['user_login']))
                        for ($i = 0; $i < count($item_property['prop_details']['share_user']); $i++) {
                            if ($item_property['prop_details']['share_user'][$i] != $_SESSION['aib']['user_data']['user_login']) {
                                unset($apiResponse['info']['records'][$key]);
                            }
                        }
                }
            }
        }
        if ($itemData['item_type'] == 'SG' && $treeDataArray[count($treeDataArray) - 2]['item_title'] == 'Scrapbooks') {
            $count = 0;
            // Used foreach loop over api response & get item details of each array element 
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                // Request array to get item details with all property & with all file                
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
                // Get item details with all property  
                $item_property = getItemDetailsWithProp($dataArray['item_id']);
                // Service request to get item details with all property & with all file
                $apiResponseItem = aibServiceRequest($postData, 'browse');
                // Used foreach loop over api response & check api response record  property count equal to each data array property_counts conditions
                foreach ($apiResponseItem['info']['records'] as $key => $dataItemArray) {
                    $apiResponseItem['info']['records'][$key]['property_counts'] = $dataArray['property_counts'];
                    // Get item tree data of ftree item    
                    $recordsItemParent = getTreeData($dataItemArray['item_id']);
                    if ($dataItemArray['item_type'] == 'IT') {
                        $apiResponseItem['info']['records'][$key]['item_parent_id'] = $recordsItemParent[count($recordsItemParent) - 2]['item_id']; //$item_property['prop_details']['item_parent'];
                    }
                    $apiResponseItem['info']['records'][$key]['item_parent_refrence_id'] = $dataArray['item_id'];
                    $apiResponseItem['info']['records'][$key]['scrapbook_item'] = 'Y';
                    if (trim($dataArray['link_title']) and $dataArray['link_title'] != '') {
                        $apiResponseItem['info']['records'][$key]['scrapbook_title'] = $dataArray['link_title'];
                    } else {
                        $apiResponseItem['info']['records'][$key]['scrapbook_title'] = $dataArray['item_title'];
                    }
                    $apiResponseItem['info']['records'][$key]['final_deref_stp_thumb'] = $dataArray['final_deref_stp_thumb'];
                    $apiResponseItem['info']['records'][$key]['final_deref_stp_url'] = $dataArray['final_deref_stp_url'];

                    $ThumbID = false;
                    $PrimaryID = false;
                    // Used foreach loop over $dataItemArray["files"] & check each arrary element tn_file_id = file_id                     
                    foreach ($dataItemArray["files"] as $FileRecord) {
                        if ($FileRecord["file_type"] == 'tn') {
                            $ThumbID = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['tn_file_id'] = $FileRecord["file_id"];
                            continue;
                        }
                        if ($FileRecord["file_type"] == 'pr') {
                            $PrimaryID = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['pr_file_id'] = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                            continue;
                        }
                        if ($FileRecord["file_type"] == 'pr') {
                            $PrimaryID = $FileRecord["file_id"];
                            $apiResponseItem['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                            continue;
                        }
                    }
                    $apiResponse['info']['records'][$count] = $apiResponseItem['info']['records'][$key];
                    if ($recordsItemParent[count($recordsItemParent) - 2]['properties']['aib:visible'] == 'N') {
                        // Get item details with all property of ftree item                        
                        $scrapbookDetails = getItemDetailsWithProp($folderId);
                        if ($scrapbookDetails['prop_details']['scrapbook_type'] == 'public') {
                            $sheare_user = json_decode($scrapbookDetails['prop_details']['share_user']);
                            if (!empty($sheare_user) && !empty($_SESSION['aib']['user_data']['user_login'])) {
                                if (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) {
                                    unset($apiResponse['info']['records'][$count]);
                                }
                            } else {
                                unset($apiResponse['info']['records'][$count]);
                            }
                        }
                    }
                }
                $count++;
            }
        }

        if ($itemData['item_type'] == 'RE') {
            // Used foreach loop over api response to check file type tn & pr for each array element            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $ThumbID = false;
                $PrimaryID = false;
                foreach ($dataArray["final_deref_files"] as $FileRecord) {
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
        // Apply public user filter on api response         
        $apiResponse = applyPublicUserFilter($apiResponse, $filter);
        // Used foreach loop over api response & check conditions        
        foreach ($apiResponse['info']['records'] as $itemKey => $itemDataArray) {
            // Check conditions aib:private == 'Y' & aib:visible == 'N' & unset $apiResponse['info']['records'][$itemKey]              
            if ($itemDataArray['properties']['aib:private'] == 'Y' || $itemDataArray['properties']['aib:visible'] == 'N') {
                unset($apiResponse['info']['records'][$itemKey]);
                continue;
            }
            // Check item type SG            
            if ($itemDataArray['item_type'] == 'SG') {
                $apiResponse['info']['records'][$itemKey]['child_count'] = ($itemDataArray['property_counts'][1]['count'] - $itemDataArray['property_counts'][6]['count']) . ' Rec(s)';
            }
            // Check item type SG
            if ($itemDataArray['item_type'] == 'RE') {
                $apiResponse['info']['records'][$itemKey]['child_count'] = ($itemDataArray['property_counts'][4]['count'] - ($itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][7]['count'])) . ' Item(s)';
            }
        }
        $firstTimeDataArray = [];
        $recordIdsArray = [];
        $indexKey = 0;
        // Used foreach loop over api response to check conditions             
        foreach ($apiResponse['info']['records'] as $recordKey => $recordDataArray) {
            // Check to set link_id & unset $apiResponse['info']['records'][$recordKey]                 
            if (isset($recordDataArray['link_id'])) {
                unset($apiResponse['info']['records'][$recordKey]);
                continue;
            }
            // Check item type SG                
            if ($recordDataArray['item_type'] == 'SG') {
                $firstTimeDataArray['sub_groups'][] = $recordDataArray;
            }
            // Check item type RE 
            if ($recordDataArray['item_type'] == 'RE') {
                $firstTimeDataArray['records'][] = $recordDataArray;
            }
            // Check item title Scrapbooks
            if ($recordDataArray['item_title'] == 'Scrapbooks') {
                // Get archive owner details of ftree archive                    
                $itemOwner = getArchiveOwner($folderId);
                $itemUserId = $itemOwner['user_id'];
                // Request array to get item list of ftree parent item                    
                $scrapbookPostData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => 1,
                    "_op" => "list",
                    "parent" => $recordDataArray['item_id'],
                    "opt_get_files" => 'Y',
                    "opt_deref_links" => 'Y',
                    "opt_get_property" => 'Y',
                    "opt_get_long_prop" => 'Y',
                    "opt_get_prop_count" => 'Y',
                    "opt_get_link_source_properties" => 'Y',
                    "opt_prop_count_set" => json_encode($search_filter),
                    "opt_follow_links" => 'Y'
                );
                // Service request to get item list of ftree parent item
                $apiResponseScrapbook = aibServiceRequest($scrapbookPostData, 'browse');
                // Used foreach loop over api response & check condions                    
                foreach ($apiResponseScrapbook['info']['records'] as $key => $dataArray) {
                    // Check set & not empty link title                         
                    if (isset($dataArray['link_title']) && $dataArray['link_title'] != '') {
                        $apiResponseScrapbook['info']['records'][$key]['item_title'] = $dataArray['link_title'];
                    }
                    if (isset($_SESSION['aib']['user_data']['user_id']) && $_SESSION['aib']['user_data']['user_id'] != $itemUserId && $_SESSION['aib']['user_data']['user_type'] == 'U' && $dataArray['is_link'] == 'N') {
                        $apiResponseScrapbook['info']['records'][$key]['show_copy_link'] = 'yes';
                        $apiResponseScrapbook['info']['records'][$key]['item_user_id'] = $itemUserId;
                    }
                    $apiResponseScrapbook['info']['records'][$key]['child_count'] = ($dataArray['property_counts'][0]['count'] - $dataArray['property_counts'][6]['count']) . ' Item(s)';
                    // Get item details with all item property     
                    $item_property = getItemDetailsWithProp($dataArray['item_id']);
                    if ($item_property['prop_details']['scrapbook_type'] != 'public' and $_SESSION['aib']['user_data']['user_top_folder'] != $_REQUEST['folder_id']) {
                        $sheare_user = json_decode($item_property['prop_details']['share_user']);
                        if (!empty($sheare_user) && !empty($_SESSION['aib']['user_data']['user_login'])) {
                            // Check user login exist in $share_user or not    
                            if (!in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user)) {
                                unset($apiResponseScrapbook['info']['records'][$key]);
                            }
                        } else {
                            unset($apiResponseScrapbook['info']['records'][$key]);
                        }
                    }
                }
                $firstTimeDataArray['scrapbook'] = $apiResponseScrapbook['info']['records'];
            }
            if ($recordDataArray['item_type'] == 'RE') {
                $recordIdsArray[$indexKey] = $recordDataArray['item_id'];
                $indexKey++;
            }
        }
        $_SESSION['pepole_record_ids'] = $recordIdsArray;

        if (count($_SESSION['pepole_record_ids']) > 0) {
            $responseData = array('status' => 'success', 'message' => 'record ids session prepared successfully', 'recordIdarr' => $recordIdsArray);
        }
    } else {
        if (isset($apiResponse['info']['records'])) {
            // Used foreach loop over api response and check conditions   
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                // check visible to public condition & unset $apiResponse['info']['records'][$key]                
                if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check item type cmntset & unset $apiResponse['info']['records'][$key] 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'cmntset') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check link class related_content & unset $apiResponse['info']['records'][$key]
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'related_content') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check link class scrapbook & unset $apiResponse['info']['records'][$key]
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'scrapbook') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check item type AG & unset $apiResponse['info']['records'][$key] 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'AG') {
                    $itemProperty = $dataArray['properties'];
                    $apiResponse['info']['records'][$key]['property_list'] = $itemProperty;
                    if ($itemProperty['status'] == 0) {
                        unset($apiResponse['info']['records'][$key]);
                    }
                }
                // check item title advertisements & unset $apiResponse['info']['records'][$key] 
                if (strtolower($dataArray['item_title']) == 'advertisements') {
                    unset($apiResponse['info']['records'][$key]);
                }
                // check item title shared out of system & unset $apiResponse['info']['records'][$key]
                if (strtolower($dataArray['item_title']) == 'shared out of system') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                // check item type RE 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'RE') {
                    $itemProperty = $dataArray['properties'];
                    $apiResponse['info']['records'][$key]['RE_property'] = $itemProperty;
                    $sheare_user = json_decode($itemProperty['share_user']);
                    // check item type RE & unset $apiResponse['info']['records'][$key] 
                    if ($itemData['item_type'] == 'RE') {
                        if ($itemProperty['aib:visible'] == 'N') {
                            unset($apiResponse['info']['records'][$key]);
                        }
                    } elseif ($itemProperty['aib:visible'] == 'N' || ($itemProperty['aib:visible'] == 'Y' && $itemProperty['aib:private'] == 'Y')) {
                        unset($apiResponse['info']['records'][$key]);
                    }
                }
                // check item type IT 
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'IT') {
                    $index = count($treeDataArray) - 1;
                    $treeData = $treeDataArray[$index];
                    $view_item_id = $folderId;
                    if (!empty($treeData['item_id'])) {
                        $view_item_id = $treeData['item_id'];
                    }
                    // item property due to tree data
                    // $itemProperty=getItemProperty($view_item_id);   
                    $sheare_user = json_decode($itemProperty['share_user']);
                    if ($_SESSION['aib']['user_data']['user_type'] == 'R') { //Pass
                    } else if (($_SESSION['aib']['user_data']['user_type'] == 'A' || $_SESSION['aib']['user_data']['user_type'] == 'U') && $treeDataArray[1]['item_id'] == $_SESSION['aib']['user_data']['user_top_folder']) {
                        
                    } else {
                        if ($apiResponse['properties']['aib:visible'] == 'N') {
                            echo "false";
                            exit;
                        }
                    }
                }
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'SG' && end($treeDataArray)['item_type'] == 'SG') {
                    $sbgroup[$count] = $apiResponse['info']['records'][$key];
                    unset($apiResponse['info']['records'][$key]);
                    $count++;
                }
            }
        }
        if (isset($itemData['item_type']) && $itemData['item_type'] == 'RE') {
            // Get item data of ftree item
            $treeDataArray = getTreeData($folderId);
            // Used foreach loop over api response            
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                $ThumbID = false;
                $PrimaryID = false;
                // Used foreach loop over $dataArray["files"]                 
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
                if ($itemId != '' && $dataArray['item_id'] == $itemId) {
                    $itemArrayKey = $key;
                }
            }
        }
        if (isset($apiResponse['info']['records'])) {
            $subgroupRecordId = array();
            $arrKey = 0;
            // Used foreach loop over api response & check conditions 
            foreach ($apiResponse['info']['records'] as $itemKey => $itemDataArray) {
                //  Check item type AG              
                if ($itemData['item_type'] == 'AG') {
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][3]['count'] - ($itemDataArray['property_counts'][5]['count'] + $itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][8]['count']);
                }
                //  Check item type AR
                if ($itemData['item_type'] == 'AR') {
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][2]['count'] - ($itemDataArray['property_counts'][5]['count'] + $itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][8]['count']);
                }
                //  Check item type CO
                if ($itemData['item_type'] == 'CO') {
                    $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'] - ($itemDataArray['property_counts'][5]['count'] + $itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][8]['count']);
                    if ($itemDataArray['property_counts'][2]['count'] != 0) {
                        $apiResponse['info']['records'][$itemKey]['sg_count'] = $itemDataArray['property_counts'][2]['count'] - ($itemDataArray['property_counts'][5]['count']);
                    }
                }
                //  Check item type SG
                if ($itemData['item_type'] == 'SG') {
                    if ($itemDataArray['item_type'] == 'SG') {
                        $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][1]['count'];
                    } else {
                        $apiResponse['info']['records'][$itemKey]['child_count'] = $itemDataArray['property_counts'][4]['count'] - ($itemDataArray['property_counts'][5]['count'] + $itemDataArray['property_counts'][6]['count'] + $itemDataArray['property_counts'][7]['count']);
                    }
                    if ($itemDataArray['property_counts'][2]['count'] != 0) {
                        $apiResponse['info']['records'][$itemKey]['sg_count'] = $itemDataArray['property_counts'][2]['count'] - ($itemDataArray['property_counts'][5]['count']);
                    }
                    if (count($sbgroup) > 0) {
                        $apiResponse['info']['records'][$itemKey]['sg_count'] = count($sbgroup);
                    }
                }
                if ($itemData['item_type'] == 'AG' || $itemData['item_type'] == 'AR' || $itemData['item_type'] == 'CO') {
                    if ($apiResponse['info']['records'][$itemKey]['is_link'] == 'Y') {
                        unset($apiResponse['info']['records'][$itemKey]);
                    }
                }
                if ($itemData['item_type'] == 'RE') {
                    if ($apiResponse['info']['records'][$itemKey]['item_type'] != 'IT') {
                        unset($apiResponse['info']['records'][$itemKey]);
                    }
                }
                $logintopFolder = 0;
                if (isset($_SESSION['aib']['user_data']['user_top_folder']) and $_SESSION['aib']['user_data']['user_top_folder'] != '') {
                    $logintopFolder = $_SESSION['aib']['user_data']['user_top_folder'];
                }
                $itemTopFolder = -100;
                if (isset($apiResponse['info']['records'][$itemKey]['root_info']['archive_group']['item_id']) and $apiResponse['info']['records'][$itemKey]['root_info']['archive_group']['item_id'] != '') {
                    $itemTopFolder = $apiResponse['info']['records'][$itemKey]['root_info']['archive_group']['item_id'];
                } elseif (isset($apiResponse['info']['records'][$itemKey]['root_info']['archive']['item_id']) and $apiResponse['info']['records'][$itemKey]['root_info']['archive']['item_id'] != '') {
                    $itemTopFolder = $apiResponse['info']['records'][$itemKey]['root_info']['archive']['item_id'];
                }
                $private_records = '';
                if ($apiResponse['info']['records'][$itemKey]['properties']['aib:private'] == 'Y' and ! in_array($_SESSION['aib']['user_data']['user_login'], $sheare_user) == true and ( $share == 0 and $itemId != $apiResponse['info']['records'][$itemKey]['item_id']) and $logintopFolder != $itemTopFolder) {
                    $private_records = 'yes';
                    unset($apiResponse['info']['records'][$itemKey]);
                }
                if ($itemData['item_type'] == 'SG' && $itemDataArray['item_type'] == 'RE') {
                    $subgroupRecordId[$arrKey] = $itemDataArray['item_id'];
                    $arrKey++;
                }
            }
        }
        // Apply filter on api response          
        $apiResponse = applyUserFilter($apiResponse, $filter);
        $_SESSION['data_detail_page'] = $apiResponse;
        if (count($subgroupRecordId) > 0) {
            $_SESSION['subgroup_record_ids'] = $subgroupRecordId;
        }
        if (count($_SESSION['subgroup_record_ids']) > 0) {
            $responseData = array('status' => 'success', 'message' => 'record ids session prepared successfully', 'recordIdarr' => $subgroupRecordId);
        }
    }
    return $responseData;
}

function getFileId($itemId) {
    $sessionKey = $_SESSION['aib']['session_key'];
    if ($itemId != '') {
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get",
            "obj_id" => $itemId,
            "opt_get_files" => 'Y',
            "opt_get_property" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        $fileArray = [];
        foreach ($apiResponse['info']['records'][0]['files'] as $key => $arrData) {
            if ($arrData['file_type'] == 'pr') {
                $fileId = $arrData['file_id'];
            }
        }
        $fileArray['file_id'] = $fileId;
        $fileArray['file_data'] = $apiResponse['info']['records'][0];
    }
    return $fileArray;
}

function getSocietyScrapbookListing($archive_group_id = null) {
    if ($archive_group_id != null) {
        $scrapbookList = [];
        $agChildren = getItemChildWithData($archive_group_id);
        $scrapbookParent = '';
        if (!empty($agChildren)) {
            foreach ($agChildren as $childData) {
                if ($childData['item_title'] == 'Scrapbooks') {
                    $scrapbookParent = $childData['item_id'];
                    break;
                }
            }
            if ($scrapbookParent != '') {
                $scrapbookList = getItemChildWithCounts($scrapbookParent);
                foreach ($scrapbookList as $key => $dataArray) {
                    if ($dataArray['properties']['scrapbook_type'] == 'private') {
                        unset($scrapbookList[$key]);
                        continue;
                    }
                    if (!isset($dataArray['link_owner_properties']) && $dataArray['root_info']['archive_group']['item_id'] != $_SESSION['aib']['user_data']['user_top_folder'] && isset($_SESSION['aib']['user_data']['user_top_folder'])) {
                        $scrapbookList[$key]['show_copy_link'] = 'yes';
                    }
                    $scrapbookList[$key]['child_count'] = $dataArray['property_counts'][0]['count'];
                }
            }
        }
        return $scrapbookList;
    }
}

function getItemChildWithCounts($item_id = null) {
    if ($item_id != null) {
        $sessionKey = $_SESSION['aib']['session_key'];
        // Request to get item's child list    
        $search_filter = array(array('name' => 'aibftype', "value" => "rec"), array('name' => 'aibftype', "value" => "sg"), array('name' => 'is_advertisements', "value" => "Y"), array('name' => 'visible_to_public', "value" => 0), array('name' => 'aib:private', "value" => "Y"));
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $item_id,
            "opt_deref_links" => 'Y',
            "opt_get_property" => 'Y',
            "opt_get_link_source_properties" => 'Y',
            "opt_get_long_prop" => 'Y',
            "opt_get_prop_count" => 'Y',
            "opt_prop_count_set" => json_encode($search_filter),
            "opt_get_root_folder" => 'Y'
        );
        // Service request to get item's child data list       
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}
