<?php

session_start();
require_once 'config/config.php';
ini_set('display_errors', 0);
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
                    $_SESSION['aib']['session_key'] = $apiResponse['session'];
                    $_SESSION['aib']['user_data'] = $apiResponse['info'];
                    $_SESSION['aib']['user_data']['user_prop'] = $apiUserProp;
                }
                $responseData = array('status' => 'success', 'message' => 'Login successfully');
            } else {
                $responseData = array('status' => 'error', 'message' => 'temporarly your archive group is deactivated.');
            }
        } else {
            $responseData = array('status' => 'error', 'message' => $apiResponse['info']);
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
        include 'item_listing.php';
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
    case 'assistant_list':
        $topFolderId = (isset($_POST['parent_id']) && $_POST['parent_id'] != '') ? $_POST['parent_id'] : $_SESSION['aib']['user_data']['user_top_folder'];
        $userId = $_SESSION['aib']['user_data']['user_id'];

        switch ($_POST['type']) {
            case 'S':
                $itemDetails = getItemData($topFolderId);
                $subAdminData = array();
                if ($itemDetails['item_type'] == 'AG') {
                    $postData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $userId,
                        "_op" => "list",
                        "parent" => $topFolderId
                    );
                    $apiResponse = aibServiceRequest($postData, 'browse');
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
                }

                $subAdminDataNew = array();
                foreach ($subAdminData as $data) {
                    $uncompleteRequestData = array(
                        '_key' => APIKEY,
                        '_user' => $userId,
                        '_op' => 'data_entry_waiting',
                        '_session' => $sessionKey,
                        'user_id' => $data['user_id']
                    );

                    $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');

                    if (trim($uncompleteResponse['status']) == 'OK') {
                        $data['Waiting'] = count($uncompleteResponse['info']['records']);
                    } else {
                        $data['Waiting'] = 0;
                    }



                    $completeRequestData = array(
                        '_key' => APIKEY,
                        '_user' => $userId,
                        '_op' => 'data_entry_complete',
                        '_session' => $sessionKey,
                        'user_id' => $data['user_id']
                    );
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
            case 'A':
                $apiGetData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'list_profiles',
                    "_user" => $userId,
                    "user_type" => 'A',
                    "opt_include_sub" => 'Y'
                );
                $apiResponse = aibServiceRequest($apiGetData, 'users');
                $responseData = $apiResponse['info']['records'];
                //Get the Archive Group data
                $userListWithArchiveGroup = Array();
                foreach ($responseData as $key => $value) {
                    $archiveData = getItemData($value['user_top_folder']);
                    if (!empty($archiveData)) {
                        $value['item_title'] = $archiveData['item_title'];
                        $property_user = getUsersAllProperty($value['user_id']);
                        $value['user_pro_type'] = $property_user['type'];
                        $userListWithArchiveGroup[] = $value;
                    }
                }
                print json_encode($userListWithArchiveGroup);
                break;

            case 'U':
                $apiGetData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'list_profiles',
                    "_user" => $userId,
                    "user_type" => 'U',
                    "opt_include_sub" => 'Y'
                );
                $apiResponse = aibServiceRequest($apiGetData, 'users');
                $responseData = $apiResponse['info']['records'];
                //Get the Archive Group data
                $userListWithArchiveGroup = Array();
                foreach ($responseData as $key => $value) {
                    $user_status = getUserProfileProperties($value['user_id'],'status');
                    $value['status'] = isset($user_status['status']) ? $user_status['status'] : '';
                    $archiveData = getItemData($value['user_top_folder']);
                    if (!empty($archiveData)) {
                        $value['item_title'] = $archiveData['item_title'];
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
        $type = $_POST['type'];
        $finalresponse = [];
        $archive_group_id = $_POST['arch_id'];
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
                if ($dataArray['field_owner_type'] == 'S' || $dataArray['field_owner_type'] == 'R') {
                    continue;
                }
                if (!in_array($dataArray['field_owner_id'], $childata)) {
                    unset($responseData[$responseKey]);
                }
            }
            if (isset($_REQUEST['type']) and $_REQUEST['type'] == 'edit'and isset($_SESSION['filedArray'])) {
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
                    }
                }
            }
            foreach ($responseData as $newresponseData) {
                $finalresponse[] = $newresponseData;
            }
            print json_encode($finalresponse);
        }
        break;
    case 'update_profile':
        parse_str($_POST['formData'], $postData);
        $passwordDataArray = array();
        if (isset($postData['profile_paswd']) && $postData['profile_paswd'] != '') {
            $passwordDataArray = array("new_user_password" => $postData['profile_paswd']);
        }
        /* $requestPostData = array(
          "_key" =>APIKEY,
          "_session" =>$sessionKey,
          "_op" => 'update_profile',
          '_user'=> $_SESSION['aib']['user_data']['user_id'],
          'user_id'=> $_SESSION['aib']['user_data']['user_id'],
          "new_user_login" =>$postData['login_data'],
          "new_user_title" =>$postData['profile_name']
          ); */
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
        } else {
            $responseData = array('status' => 'error', 'message' => 'Profile not updated.');
        }
        print json_encode($responseData);
        break;
    /* Create Fields Section Start */
    case 'create_fields':
        parse_str($_POST['formData'], $postData);
        $field_owner_type = 'I';
        $field_owner_id = $postData['field_owner_name'];
        if (trim($postData['field_owner_name']) == 'R' || trim($postData['field_owner_name']) == 'S') {
            $field_owner_type = $postData['field_owner_name'];
            $field_owner_id = '-1';
        }
        $requestPostData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => 'field_create',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            "field_title" => $postData['field_name'],
            "field_format" => $postData['field_format_detail'],
            "field_size" => $postData['field_display_width_name'],
            "field_owner_type" => $field_owner_type,
            "field_owner_id" => $field_owner_id,
            "field_data_type" => $postData['field_type_name']
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
            'field_format' => $_POST['updatefieldformat'],
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
            if (count($NewAdded)) {
                $requestFormFieldData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_op" => 'form_list_fields',
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    'form_id' => $_POST['edit_forms_id']
                );
                $responseFormFieldData = aibServiceRequest($requestFormFieldData, 'fields');
                $sortListData = end($responseFormFieldData['info']['records']);
                $sortOrder = $sortListData['form_field_sort_order'];
                foreach ($NewAdded as $fieldNew) {
                    if ($fieldNew != '') {
                        $sortOrder ++;
                        $requestPostData = array(
                            "_key" => APIKEY,
                            "_session" => $sessionKey,
                            "_op" => 'form_add_field',
                            '_user' => $_SESSION['aib']['user_data']['user_id'],
                            "form_id" => $_POST['edit_forms_id'],
                            "field_id" => $fieldNew,
                            "field_sort_order" => $sortOrder
                        );
                        $apiResponse = aibServiceRequest($requestPostData, 'fields');
                    }
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
            $responseData = array('status' => 'success', 'message' => 'Form updated successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Form not updated.');
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
            $responseData = array('status' => 'success', 'message' => 'Assistant deleted successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Assistant deleted not successfully.');
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
            $responseData = array('status' => 'success', 'message' => 'Fields deleted successfully.');
        } else {
            $responseData = array('status' => 'error', 'message' => 'Fields deleted not successfully.');
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
        $itemData = getItemData($folderId);
        if ($itemData['item_type'] == 'AR') {
            $archiveDetails = getItemDetailsWithProp($itemData['item_id']);
            if (isset($archiveDetails['prop_details']['type']) && $archiveDetails['prop_details']['type'] == 'A') {
                $_SESSION['type'] = "A";
            } else {
                unset($_SESSION['type']);
            }
        }
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $folderId,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_deref_links" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        //echo "<pre>";print_r($apiResponse);exit;
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
                        continue;
                    }
                    if ($FileRecord["file_type"] == 'or') {
                        $PrimaryID = $FileRecord["file_id"];
                        $apiResponse['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                }
            }
        }
        foreach ($apiResponse['info']['records'] as $itemKey => $itemDataArray) {
            $itemCount = getItemChildCount($itemDataArray['item_id'], $itemData['item_type']);
            if ($itemCount) {
                $apiResponse['info']['records'][$itemKey]['child_count'] = $itemCount['child_count'];
                if ($itemCount['sg_count'] != 0) {
                    $apiResponse['info']['records'][$itemKey]['sg_count'] = $itemCount['sg_count'];
                }
            }
            
            if($apiResponse['info']['records'][$itemKey]['item_title']=='aib-shared'){
                unset($apiResponse['info']['records'][$itemKey]);
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
        switch ($postData['item_request_type']) {
            case 'ar':
                $item_title = $postData['item_title'];
                $aditionalProp['propname_1'] = 'code';
                $aditionalProp['propval_1'] = $postData['item_code'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements_ar'];
                break;
            case 'col':
                $item_title = $postData['collection_name'];
                $aditionalProp['propname_1'] = 'visible_to_public';
                $aditionalProp['propval_1'] = $postData['visible_to_public_co'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements_co'];
                break;
            case 'sg':
                $item_title = $postData['sub_group_name'];
                $aditionalProp['propname_1'] = 'visible_to_public';
                $aditionalProp['propval_1'] = $postData['visible_to_public_sg'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements_sg'];
                break;
            default :
                $item_title = $postData['item_title'];
                $aditionalProp['propname_1'] = 'code';
                $aditionalProp['propval_1'] = $postData['item_code'];
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
                
                //Create Shared Folder For the User
                createSharedSubGroup($user_id,$postData['parent_id']);
                
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
            $requestPostData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_op" => 'update_profile',
                '_user' => $_SESSION['aib']['user_data']['user_id'],
                'user_id' => $postData['user_id'],
                "new_user_title" => $postData['user_title']
            );
            $mergedDataArray = array_merge($requestPostData, $passwordDataArray);
            $apiResponse = aibServiceRequest($mergedDataArray, 'users');
            $responseData = array('status' => 'error', 'message' => 'Profile not updated.');
            if ($apiResponse['status'] == 'OK') {
                $requestUpdatePropertyData = array(
                    "_key" => APIKEY,
                    "_session" => $sessionKey,
                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                    "_op" => "set_profile_prop",
                    "user_id" => $postData['user_id'],
                    "property_name" => 'email',
                    "property_value"=> $postData['user_email']
                );
                $result = aibServiceRequest($requestUpdatePropertyData,"users");
                $responseData = array('status' => 'success', 'message' => 'Profile updated successfully.');
            } else {
                $responseData = array('status' => 'error', 'message' => $message[$responseStatus['info']]);
            }
        }
        print json_encode($responseData);
        break;
    case 'get_assistant_list':
        $archive_id = $_POST['archive_id'];
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
        $postData['archive_request_reprint_text'] = $_POST['reprint_request_data'];
        if (isset($postData['archive_id']) && $postData['archive_id'] != '') {
            $archive_id = $postData['archive_id'];
            if (isset($postData['archive_item_title']) && $postData['archive_item_title'] != '') {
                $arcive_title = $postData['archive_item_title'];
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
        $emails = $_POST['user_emails'];
        $email = json_encode($emails);
        $apiRequestDataItem = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'set_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $id,
            'opt_long' => 'Y',
            'propname_1' => 'share_user',
            'propval_1' => $email
        );
        $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        //echo  "<pre>";print_r($apiResponse);
        if ($apiResponse['status'] == 'OK') {
            $data_id = getTreeData($_POST['id']);
            $archive_details = getItemDetailsWithProp($data_id[1]['item_id']); 
            $to = implode(",",$emails);
            $email_data = [];
            $email_data['to'] = $to;
            $email_data['from'] =  ADMIN_EMAIL;  
            $email_data['reply'] = $to;
            $email_template = file_get_contents(EMAIL_PATH."/share.html");
            $name = $_SESSION['aib']['user_data']['user_title'];
            $date =  date("F j, Y");
            $email_template = str_replace('#name#',$name, $email_template);
            $email_template = str_replace('#date#',$date, $email_template);
            $header_image = HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_header_image'];
            $header_image = '<img style="width:100%;" src="'.$header_image.'" alt="Image" />';
            $thoumb = '<img height="100" style="position: absolute;left: 50%;top: 50%;transform: translateX(-50%) translateY(-50%);height: 80px;" src="'.HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_logo_image'].'" alt="AIB Logo" />';
            if(!empty($archive_details['prop_details']['archive_logo_image'])){
            $email_data['subject'] = 'ArchiveInABox: Record shared with you !!!';      
            $email_template = str_replace('#archive_logo#',$thoumb, $email_template);  
            }else{
              $email_data['subject'] = 'ArchiveInABox: Scrapbook shared with you !!!';
             $email_template = str_replace('#archive_logo#','', $email_template);    
            }
            $email_template = str_replace('#header_images#',$header_image, $email_template);
            $url ='<a style="word-break:break-all;" href="'.HOST_PATH.'item-details.php?folder_id='.$_POST['id'].'" target="_blank">'.HOST_PATH.'item-details.php?folder_id='.$_POST['id'].'</a>';
            $img1 = '<img style="width:100%;" src="'. THUMB_URL . '?item_id=' . $_POST['id'].'" alt="Image" />';
            $img2= '<img style="width:100%;" src="'.HOST_ROOT_IMAGE_PATH.'systemAdmin-header-img.jpg" alt="Image" />';
            $header_image = '<img style="width:100%;" src="'.HOST_ROOT_IMAGE_PATH.'mail-template_header.jpg" alt="Image" />';
            $email_template = str_replace('#vulval#',$url, $email_template);
            if(!empty($_POST['type']) && $_POST['type']=='scrapbook'){
            $email_template = str_replace('#item_images#',$img2, $email_template);   
            }else{
            $email_template = str_replace('#item_images#',$img1, $email_template);
            }
            $email = sendMail($email_data,$email_template);
            if($email){
              $responseData = array('status' => 'success', 'message' => 'Scrapbook share successfully');  
            }
            foreach($emails as $uEmail){
                linkTheSharedRecords($_SESSION['aib']['user_data']['user_id'],$uEmail,$id);
            }
        }
        print json_encode($responseData);
        break;
    case 'get_archive_prop_details':
        $archive_id = $_POST['archive_id'];
        $itemDetails = getItemDetailsWithProp($archive_id);
        print json_encode($itemDetails);
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
        switch ($postData['item_type']) {
            case 'AR':
                $item_title = $postData['item_title'];
                $aditionalProp['propname_1'] = 'code';
                $aditionalProp['propval_1'] = $postData['item_code'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements'];
                $item_class = 'ar';
                break;
            case 'CO':
                $item_title = $postData['collection_name'];
                $aditionalProp['propname_1'] = 'visible_to_public';
                $aditionalProp['propval_1'] = $postData['visible_to_public'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements'];
                $item_class = 'col';
                break;
            case 'SG':
                $item_title = $postData['sub_group_name'];
                $aditionalProp['propname_1'] = 'visible_to_public';
                $aditionalProp['propval_1'] = $postData['visible_to_public'];
                $aditionalProp['propname_2'] = 'display_archive_level_advertisements';
                $aditionalProp['propval_2'] = $postData['display_archive_level_advertisements'];
                $item_class = 'sg';
                break;
            case 'RE':
            case 'IT':
                $item_title = $postData['record_title'];
                break;

            default :
                $item_title = $postData['item_title'];
                $aditionalProp['propname_1'] = 'code';
                $aditionalProp['propval_1'] = $postData['item_code'];
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
        $responseData = array('status' => 'error', 'message' => 'Item not deleted.');
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
                $uploadedImage = cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, 'banner');
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
        }
        $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
        print json_encode($response);
        break;
    case 'get_list_data':
        $archive_id = (isset($_POST['archive_group_id']) && $_POST['archive_group_id'] != '' ) ? $_POST['archive_group_id'] : $_SESSION['aib']['user_data']['user_top_folder'];
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
                    $itemArchiveDetails = getTreeData($item_id, true);
                    $item_archive_group_id = isset($itemArchiveDetails[1]) ? $itemArchiveDetails[1]['item_id'] : $item_id;
                    if ($item_archive_group_id != $archive_id) {
                        unset($listDataArray[$key]);
                        continue;
                    } else {
                        $infoData = (array) json_decode($dataArray['req_info']);
                        $finalDataArray[$count] = array_merge($dataArray, $infoData);
                        $finalDataArray[$count]['created'] = date('Y-m-d h:i:s A', $dataArray['req_time']);
                        if ($request_data_type == 'RP') {
                            $itemDetails = getItemData($item_id);
                            if (!empty($itemDetails['files'])) {
                                foreach ($itemDetails['files'] as $key => $fileDataArray) {
                                    if ($fileDataArray['file_type'] == 'or') {
                                        $file_id = $fileDataArray['file_id'];
                                        $finalDataArray[$count]['image_link'] = THUMB_URL . '?id=' . $file_id . '&download=1';
                                    }
                                }
                            } else {
                                $finalDataArray[$count]['image_link'] = 'not found';
                            }
                        }
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
            $responseData = array('status' => 'error', 'message' => 'Somethings went wrong.');
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
            $apiResponse = aibServiceRequest($apiRequestDataItem, 'browse');
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
            $infoData['status'] = $status;
            $requestPostData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "req_mod",
                "req_id" => $request_id,
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
            if ($archiveDetails['item_type'] == 'AG') {
                $allSubgroupsArray = getAGAllSubgroup($assistantAssignedArchive);
            } else {
                $allSubgroupsArray = getAllSubgroup($assistantAssignedArchive);
            }
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
                $alternetUserData = $apiResponse['info'];
                $loggedInUserData = $_SESSION['aib']['user_data'];
                $_SESSION['aib']['user_data'] = $alternetUserData;
                $_SESSION['aib']['user_data']['user_prop'] = $apiUserProp;
                $_SESSION['aib']['previous_user_data'] = $loggedInUserData;
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
            $responseData = array('status' => 'error', 'message' => 'Archive details not updated');
            if ($apiResponse['status'] == 'OK') {
                $responseData = array('status' => 'success', 'message' => 'Archive details updated successfully');
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
            $userScrapbookParent = $apiResponseData['info']['records'][0]['item_id'];
            if ($userScrapbookParent) {
                $userScrapbookList = getItemChildWithData($userScrapbookParent);
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
                "_user" => $_SESSION['aib']['user_data']['user_id'],
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
        if ($scrapbook_id) {
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "scrpbk_lstent",
                "obj_id" => $scrapbook_id
            );
            $apiResponseData = aibServiceRequest($postRequestData, 'scrapbook');
            if ($apiResponseData['status'] == 'OK') {
                $responseDataArray = $apiResponseData['info']['records'];
                $finalDataArray = [];
                $count = 0;
                foreach ($responseDataArray as $dataArray) {
                    $postData = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => 1,
                        "_op" => "get",
                        "obj_id" => $dataArray['item_ref'],
                        "opt_get_field" => 'Y',
                        "opt_get_files" => 'Y'
                    );
                    $item_property = getItemDetailsWithProp($dataArray['item_id']);
                    $apiResponse = aibServiceRequest($postData, 'browse');
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
                            if ($FileRecord["file_type"] == 'or') {
                                $PrimaryID = $FileRecord["file_id"];
                                $apiResponse['info']['records'][$key]['or_file_id'] = $FileRecord["file_id"];
                                continue;
                            }
                        }
                    }
                    $finalDataArray[$count] = $apiResponse['info']['records'][0];
                    $finalDataArray[$count]['item_details'] = $item_property;
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
        $folderId = isset($_POST['folder_id'])?$_POST['folder_id']:"";
        $shareType = isset($_POST['share_type'])?$_POST['share_type']:"Shared-With-Other";
        $recordType = isset($_POST['record_type'])?$_POST['record_type']:"RE";
        
        $itemData = getItemData($folderId,1);
        if ($itemData['item_type'] == 'AR') {
            $archiveDetails = getItemDetailsWithProp($itemData['item_id']);
            if (isset($archiveDetails['prop_details']['type']) && $archiveDetails['prop_details']['type'] == 'A') {
                $_SESSION['type'] = "A";
            } else {
                unset($_SESSION['type']);
            }
        }
        if($shareType=='Shared-With-Me'){
            $sUserID=1;
        }else{
            $sUserID=$_SESSION['aib']['user_data']['user_id'];
        }
        
        $userRootItems=getItemList($folderId,$sUserID);
        
       if($recordType=='RE'){
            $userSharedItems=array();
            $sharedItemsRecords=array();
            if(isset($userRootItems)){

                foreach ($userRootItems as $key => $dataArray) {
                    //echo $dataArray['item_title'];
                    if(isset($dataArray['item_title']) && $dataArray['item_title']=='aib-shared'){
                        $userSharedItems=getItemList($dataArray['item_id'],$_SESSION['aib']['user_data']['user_id']);

                        foreach($userSharedItems as $childKey=>$childValue){
                            if(isset($childValue['item_title']) && $childValue['item_title']==$shareType){
                                    $sharedItemsRecords=getItemList($childValue['item_id'],$_SESSION['aib']['user_data']['user_id']);

                                    break;
                            }
                        }
                        break;
                    }
                }
            }
            $finalDataArray=array();
            foreach($sharedItemsRecords as $refRecordsArray){
                //$finalDataArray[]=getItemList($refRecordsArray['item_ref'],$_SESSION['aib']['user_data']['user_id']);
                        $postData = array(
                                    "_key" => APIKEY,
                                    "_session" => $sessionKey,
                                    "_user" =>1,
                                    "_op" => "get",
                                    "obj_id" => $refRecordsArray['item_ref'],
                                    "opt_get_field" => 'Y',
                                    "opt_get_files" => 'Y'
                                );
                        $apiResponse = aibServiceRequest($postData, 'browse');
                        if ($apiResponse['status'] == 'OK') {
                             $finalDataArray[]= $apiResponse['info']['records'][0];
                        }
                
                        //echo $refRecordsArray['item_ref'];
                        //$finalDataArray[]=getItemData($refRecordsArray['item_ref']);
            }
        }else{
            $finalDataArray=$userRootItems;
        }
          
        
        if ($itemData['item_id'] == 1) {
            foreach ($finalDataArray as $key => $dataArray) {
                $apiRequestData = array(
                    '_key' => APIKEY,
                    '_user' => $_SESSION['aib']['user_data']['user_id'],
                    '_op' => 'get_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $dataArray['item_id']
                );
                $apiResponseProps = aibServiceRequest($apiRequestData, 'browse');
                $finalDataArray[$key]['prop_details'] = $apiResponseProps['info']['records'];
            }
        }
        
        if ($itemData['item_type'] == 'RE') {
            $treeDataArray = getTreeData($folderId,false,1);
            foreach ($finalDataArray as $key => $dataArray) {
                $ThumbID = false;
                $PrimaryID = false;
                foreach ($dataArray["files"] as $FileRecord) {
                    if ($FileRecord["file_type"] == 'tn') {
                        $ThumbID = $FileRecord["file_id"];
                        $finalDataArray[$key]['tn_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                    if ($FileRecord["file_type"] == 'pr') {
                        $PrimaryID = $FileRecord["file_id"];
                        $finalDataArray[$key]['pr_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                    if ($FileRecord["file_type"] == 'or') {
                        $PrimaryID = $FileRecord["file_id"];
                        $finalDataArray[$key]['or_file_id'] = $FileRecord["file_id"];
                        continue;
                    }
                }
            }
        }
        foreach ($finalDataArray as $itemKey => $itemDataArray) {
            $itemCount = getItemChildCount($itemDataArray['item_id'], $itemData['item_type'],1);
            if ($itemCount) {
                $finalDataArray[$itemKey]['child_count'] = $itemCount['child_count'];
                if ($itemCount['sg_count'] != 0) {
                    $finalDataArray[$itemKey]['sg_count'] = $itemCount['sg_count'];
                }
            }
        }
        
        if (isset($finalDataArray)) {
            include_once TEMPLATE_PATH . 'shared_archive_listing.php';
        }
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

}

function getItemChildWithData($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    } else {
        return false;
    }
}

function getUserTotalTypeCount($user_id = null, $property_name = null) {
    if ($user_id && $property_name) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "get_profile_prop",
            "user_id" => $user_id,
            "property_name" => $property_name
        );
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['property_value'];
        } else {
            return '0';
        }
    }
}

function updateUserProperty($user_id = null, $property_name = null, $created_id = null) {
    if ($user_id && $property_name) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $previousCount = getUserTotalTypeCount($user_id, $property_name);
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
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "set_profile_prop_batch",
            "user_id" => $user_id,
            "property_list" => json_encode($userProperty)
        );
        $apiResponse = aibServiceRequest($postRequestData, "users");
        return true;
    }
}

function getUsersAllProperty($user_id = null) {
    if ($user_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postRequestData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => isset($_SESSION['aib']['user_data']['user_id']) ? $_SESSION['aib']['user_data']['user_id'] : 1,
            "_op" => "list_profile_prop",
            "user_id" => $user_id
        );
        $apiResponse = aibServiceRequest($postRequestData, "users");
        if ($apiResponse['status'] == 'OK') {
            $propertyList = [];
            foreach ($apiResponse['info']['records'] as $propertyData) {
                $propertyList[$propertyData['property_name']] = $propertyData['property_value'];
            }
            return $propertyList;
        }
    }
}

function getAllSubgroup($archive_id) {
    if ($archive_id) {
        $archiveDetails = getItemData($archive_id);
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $archive_id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            $responseDataArray = [];
            $responseDataArray['parent'] = $archiveDetails;
            $collectionList = $apiResponse['info']['records'];
            if (!empty($collectionList)) {
                $collection_count = 0;
                foreach ($collectionList as $collectionKey => $collectionData) {
                    $responseDataArray['parent']['collection'][$collection_count] = $collectionData;
                    $postDataCollection = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $_SESSION['aib']['user_data']['user_id'],
                        "_op" => "list",
                        "parent" => $collectionData['item_id']
                    );
                    $apiResponseCollection = aibServiceRequest($postDataCollection, 'browse');
                    if ($apiResponseCollection['status'] == 'OK') {
                        $subGroupList = $apiResponseCollection['info']['records'];
                        $sub_group_count = 0;
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

function getAGAllSubgroup($archiveGroupId = null) {
    if ($archiveGroupId) {
        $archiveDetails = getItemData($archiveGroupId);
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $archiveGroupId
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            $responseDataArray = [];
            $responseDataArray['parent'] = $archiveDetails;
            $archiveList = $apiResponse['info']['records'];
            if (!empty($archiveList)) {
                $archive_count = 0;
                foreach ($archiveList as $key => $archiveData) {
                    $responseDataArray['parent']['archive'][$archive_count] = $archiveData;
                    $postDataArchive = array(
                        "_key" => APIKEY,
                        "_session" => $sessionKey,
                        "_user" => $_SESSION['aib']['user_data']['user_id'],
                        "_op" => "list",
                        "parent" => $archiveData['item_id']
                    );
                    $apiResponseArchive = aibServiceRequest($postDataArchive, 'browse');
                    if ($apiResponseArchive['status'] == 'OK') {
                        $collectionList = $apiResponseArchive['info']['records'];
                        if (!empty($collectionList)) {
                            $collection_count = 0;
                            foreach ($collectionList as $collectionKey => $collectionData) {
                                $responseDataArray['parent']['archive'][$archive_count]['collection'][$collection_count] = $collectionData;
                                $postDataCollection = array(
                                    "_key" => APIKEY,
                                    "_session" => $sessionKey,
                                    "_user" => $_SESSION['aib']['user_data']['user_id'],
                                    "_op" => "list",
                                    "parent" => $collectionData['item_id']
                                );
                                $apiResponseCollection = aibServiceRequest($postDataCollection, 'browse');
                                if ($apiResponseCollection['status'] == 'OK') {
                                    $subGroupList = $apiResponseCollection['info']['records'];
                                    $sub_group_count = 0;
                                    foreach ($subGroupList as $sub_group_key => $subgroupData) {
                                        $responseDataArray['parent']['archive'][$archive_count]['collection'][$collection_count]['sub_groups'][$sub_group_count] = $subgroupData;
                                        $sub_group_count++;
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

function getSubgroupsOfSubGroup($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postDataArray = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
        $apiResponseArray = aibServiceRequest($postDataArray, 'browse');
        if ($apiResponseArray['status'] == 'OK') {
            $listDataArray = $apiResponseArray['info']['records'];
            $subgroupDataArray = array();
            if (!empty($listDataArray)) {
                $count = 0;
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

function getAssignedSubGroups($assistant_id) {
    $sessionKey = $_SESSION['aib']['session_key'];
    $assignedSubGroups = [];
    $uncompleteRequestData = array(
        '_key' => APIKEY,
        '_user' => $_SESSION['aib']['user_data']['user_id'],
        '_op' => 'data_entry_waiting',
        '_session' => $sessionKey,
        'user_id' => $assistant_id
    );
    $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
    if ($uncompleteResponse['status'] == 'OK') {
        $unCompleteDataArray = $uncompleteResponse['info']['records'];
        if (!empty($unCompleteDataArray)) {
            foreach ($unCompleteDataArray as $uncompleteKey => $uncompleteData) {
                $itemDetails = getItemData($uncompleteData['item_parent_id']);
                $assignedSubGroups[$itemDetails['item_id']] = $itemDetails['item_title'];
            }
        }
    }

    $completeRequestData = array(
        '_key' => APIKEY,
        '_user' => $_SESSION['aib']['user_data']['user_id'],
        '_op' => 'data_entry_complete',
        '_session' => $sessionKey,
        'user_id' => $assistant_id
    );
    $completeResponse = aibServiceRequest($completeRequestData, 'dataentry');
    if ($completeResponse['status'] == 'OK') {
        $completeDataArray = $completeResponse['info']['records'];
        if (!empty($completeDataArray)) {
            foreach ($completeDataArray as $completeKey => $completeData) {
                $itemDetails = getItemData($completeData['item_parent_id']);
                $assignedSubGroups[$itemDetails['item_id']] = $itemDetails['item_title'];
            }
        }
    }
    return $assignedSubGroups;
}

function cropAndUploadImage($imageObj, $x_coordinate, $y_coordinate, $type) {
    $dimensionArray = array('logo' => array('width' => 200, 'height' => 200), 'banner' => array('width' => 1600, 'height' => 400), 'content' => array('width' => 645, 'height' => 430), 'archive_group_thumb' => array('width' => 400, 'height' => 400));
    $inFile = $imageObj['tmp_name'];
    $fileName = time() . '_' . $imageObj['name'];
    $fileNameWithPath = IMAGE_TARGET_PATH . $fileName;
    $image = new Imagick($inFile);
    $image->cropImage($dimensionArray[$type]['width'], $dimensionArray[$type]['height'], $x_coordinate, $y_coordinate);
    $image->writeImage($fileNameWithPath);
    return $fileName;
}

function getUncompleteDataForParent($parent_id = null) {
    if ($parent_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $user_id = $_SESSION['aib']['user_data']['user_id'];
        $uncompleteRequestData = array(
            '_key' => APIKEY,
            '_user' => $user_id,
            '_op' => 'data_entry_waiting',
            '_session' => $sessionKey,
            'user_id' => $user_id
        );
        $uncompleteResponse = aibServiceRequest($uncompleteRequestData, 'dataentry');
        unset($_SESSION['aib']['uncomplete_data']);
        foreach ($uncompleteResponse['info']['records'] as $key => $uncompleteData) {
            if ($parent_id == $uncompleteData['item_parent_id']) {
                $recordItems = getAllItemRecords($uncompleteData['item_id']);
                $uncompleteData['item_records'] = $recordItems;
                $_SESSION['aib']['uncomplete_data'][$uncompleteData['item_parent_id']][$uncompleteData['item_id']] = $uncompleteData;
            }
        }
    }
}

function markItemAsCompleted($postData) {
    if (array_key_exists($postData['parent_id'], $_SESSION['aib']['uncomplete_data'])) {
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
        foreach ($_SESSION['aib']['uncomplete_data'] as $parentKey => $parentDataList) {
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
 * @method: getNextUncompleteItemToEdit()
 * @return: (boolean)true, false
 */

function getNextUncompleteItemToEdit() {
    $unCompleteListData = $_SESSION['aib']['uncomplete_data'];
    foreach ($unCompleteListData as $key => $unCompleteData) {
        foreach ($unCompleteData as $unCompKey => $dataArray) {
            if (array_key_exists('completed', $dataArray)) {
                if (array_key_exists('item_records', $dataArray) && !empty($dataArray['item_records'])) {
                    $itemCount = 0;
                    foreach ($dataArray['item_records'] as $itemKey => $data) {
                        if (array_key_exists('completed', $data)) {
                            $itemCount++;
                            continue;
                        } else {
                            return array('status' => 'success', 'item_id' => $data['item_id'], 'parent_id' => $dataArray['item_id']);
                        }
                    }
                    if (count($dataArray['item_records']) == $itemCount) {
                        markRecordAsComplete($unCompKey);
                    }
                } else {
                    markRecordAsComplete($unCompKey);
                }
            } else {
                return array('status' => 'success', 'item_id' => $dataArray['item_id'], 'parent_id' => $key);
            }
        }
    }
    return array('status' => 'completed', 'item_id' => '', 'parent_id' => '');
}

function markRecordAsComplete($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $requestPostData = array(
            '_key' => APIKEY,
            '_session' => $sessionKey,
            '_op' => 'data_entry_mark_complete',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'obj_id' => $item_id
        );
        $apiResponse = aibServiceRequest($requestPostData, 'dataentry');
        return true;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getAllItemRecords()
 * $params1: $item_id(int)
 * @return: (array)$apiResponse
 */

function getAllItemRecords($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

/*
 * @Author: Sapple Systems
 * @method: updateItemFieldsData()
 * @params1: $formFieldData(array)
 * $params2: $item_id(int)
 * @return: (array)$apiResponse
 */

function updateItemFieldsData($formFieldData = array(), $item_id = null) {
    if ($item_id && !empty($formFieldData)) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $requestApiData = array(
            '_key' => APIKEY,
            '_session' => $sessionKey,
            '_op' => 'store_item_fields',
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            'obj_id' => $item_id
        );
        $count = 1;
        foreach ($formFieldData as $fieldKey => $fieldData) {
            $requestApiData['field_id_' . $count] = $fieldData['id'];
            $requestApiData['field_value_' . $count] = $fieldData['field_value'];
            $count++;
        }
        $apiResponse = aibServiceRequest($requestApiData, 'browse');
        return true;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getUncompleteItemDetails()
 * @params1: $item_id(int)
 * @return: (array)$apiResponse
 */

function getUncompleteItemDetails($item_id = null, $parent_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $itemDetails = getItemDetailsWithProp($item_id);
        $parentDetails = getItemData($parent_id);
        $itemDetails['parent_details'] = $parentDetails;
        if ($itemDetails['item_type'] == 'RE' || $itemDetails['item_type'] == 'IT') {
            if ($itemDetails['item_type'] == 'IT') {
                $item_id = $parent_id;
            }
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
        return $itemDetails;
    }
}

/*
 * @Author: Sapple Systems
 * @method: getItemDetailsWithProp()
 * @params1: $item_id(int)
 * @return: (array)$apiResponse
 */

function getItemDetailsWithProp($item_id = null) {
    if ($item_id) {
        $sessionKey = $_SESSION['aib']['session_key'];
        $apiRequestData = array(
            '_key' => APIKEY,
            '_user' => (isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1,
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

/*
 * @Author: Sapple Systems
 * @method: createNewUser()
 * @params1: $userType('S','A')
 * @params2: $dataArray(array)
 * @return: (array)$apiResponse
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
        $defaultPassword = 'AIB';
        $email_data['to'] = $dataArray['user_email'];
        $email_data['from'] = ADMIN_EMAIL;
        $email_data['reply'] = $dataArray['user_email'];
        $email_data['subject'] = 'User Registration';
        if($userType == 'S'){
        $title = 'assistant';  
        $archive_id =  getTreeData($dataArray['archive_name']);
        $archive_details = getItemDetailsWithProp($archive_id[1]['item_id']); 
       }else{
            $title = 'administrator'; 
            $archive_details = getItemDetailsWithProp($dataArray['archive_name']);
       }
        $header_image = HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_header_image'];
        $header_image = '<img style="width:100%;" src="'.$header_image.'" alt="Image" />';
        $email_template = str_replace('#group_name#',$archive_details['item_title'], $email_template); 
        $email_template = str_replace('#username#',$dataArray['login_data'], $email_template);
        $email_template = str_replace('#header_images#',$header_image, $email_template);
        $email_template = str_replace('#title#',$title, $email_template);
        $thoumb = '<img height="100" style="position: absolute;left: 50%;top: 50%;transform: translateX(-50%) translateY(-50%);height: 80px;" src="'.HOST_ADMIN_IMAGE_PATH.$archive_details['prop_details']['archive_logo_image'].'" alt="AIB Logo" />';
        $email_template = str_replace('#thumb#',$thoumb, $email_template);
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $user,
            "_op" => "create_profile",
            "user_login" => $dataArray['login_data'],
            "user_pass" => $defaultPassword,
            "user_type" => $userType,
            "user_title" => $dataArray['asst_name'],
            "user_top_folder" => $dataArray['archive_name'],
            "user_primary_group" => -1
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        if($apiResponse['status'] == 'OK'){
            $userProperty[0]['name'] = 'email';
            $userProperty[0]['value'] = $dataArray['user_email'];
            if ($userType == 'A') {
                $userProperty[1]['name'] = 'type';
                $userProperty[1]['value'] = $dataArray['user_type'];
            }
            $postRequestData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $_SESSION['aib']['user_data']['user_id'],
                "_op" => "set_profile_prop_batch",
                "user_id" => $apiResponse['info'],
                "property_list" =>json_encode($userProperty)
            );
            $result = aibServiceRequest($postRequestData, "users");
            $url = 'id='.$result['info'].'&email='.$postDataArray['register_email'];
            $link = '<a href="'.HOST_PATH.'thank-you.php?'.urlencode($url).'" target="_blank" style="background:#fbd42f; color:#15345a; padding:10px; display:inline-block; font-size:12px; font-weight:bold; text-decoration:none; margin-bottom:40px;">Click to confirm Email</a>';
            $email_template = str_replace('#confirm_email#',$link,$email_template); 
            $email = sendMail($email_data,$email_template);
            if($result['status'] =='OK'){
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
 * @method: getTreeData()
 * @params: $folderId(int)
 * @return: (array)$apiResponse
 */

function getTreeData($folderId = '', $assistant = false,$uid="") {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        if(empty($uid)){
           $uid= (isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1;
        }
        
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $uid,
            "_op" => "get_path",
            "obj_id" => $folderId
        );
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

function getRecursiveItemRecord($sessionKey, $folderId) {
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => $_SESSION['aib']['user_data']['user_id'],
        "_op" => "list",
        "parent" => $folderId,
        "opt_sort" => 'TITLE'
    );
    $Record = array();
    $apiResponse = aibServiceRequest($postData, 'browse');
    foreach ($apiResponse['info']['records'] as $key => $dataArray) {
        $postData1 = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $dataArray['item_id'],
            "opt_sort" => 'TITLE'
        );
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

function getItemData($folderId = '',$uid="") {
    if ($folderId) {
        $sessionKey = $_SESSION['aib']['session_key'];
        if(empty($uid)){
            $uid=(isset($_SESSION['aib']['user_data']['user_id'])) ? $_SESSION['aib']['user_data']['user_id'] : 1;
        }
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $uid,
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

function getItemChild($item_id = null) {
    if ($item_id) {
        $itemId = [];
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "list",
            "parent" => $item_id
        );
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

function getItemChildCount($item_id = null, $item_type = null,$UID="") {
    if ($item_type != RE) {
        if ($item_id) {
            $sessionKey = $_SESSION['aib']['session_key'];
            
            if(empty($UID)){
                $UID=$_SESSION['aib']['user_data']['user_id'];
            }
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => $UID,
                "_op" => "list",
                "parent" => $item_id
            );
            $apiResponse = aibServiceRequest($postData, 'browse');
            $sgCount = 0;
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

function print_array($dataArray = [], $exit = false) {
    echo "<pre>";
    print_r($dataArray);
    echo "</pre>";
    if ($exit)
        exit;
}

function checkArchiveStatus($userTopFolder = null, $userType = null) {
    if ($userTopFolder && $userType) {
        if ($userType == 'R') {
            return true;
        } else {
            $archiveDetails = getItemDetailsWithProp($userTopFolder);
            if ($archiveDetails['item_type'] == 'AG') {
                return ($archiveDetails['prop_details']['status'] == 1) ? true : false;
            } else {
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

function aibServiceRequest($postData, $fileName, $mail = null) {
    $curlObj = curl_init();
    $api_url = ($mail == null) ? APIURL: MAIL_APIURL;
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $api_url . $fileName . ".php",
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
    if (isset($outData['info']) && $outData['info'] == 'EXPIRED' && $mail == null) {
        unset($_SESSION);
        session_destroy();
        header('Location: login.php');
        exit;
    } else {
        return($outData);
    }
}


function getItemList($folderId,$uID){
    
    if(empty($folderId) || empty($uID)) return false;
    $sessionKey = $_SESSION['aib']['session_key'];
    $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $uID,
            "_op" => "list",
            "parent" => $folderId,
            "opt_get_files" => 'Y',
            "opt_get_first_thumb" => 'Y',
            "opt_deref_links" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        return $apiResponse['info']['records'];
}

function isShareFolderExist($folderId,$userID){
    
        $userRootItems=getItemList($folderId,$userID);
        if(isset($userRootItems)){
            foreach ($userRootItems as $key => $dataArray) {
                if(isset($dataArray['item_title']) && $dataArray['item_title']=='aib-shared'){
                    return true;
                }
            }
        }
       return false; 
}

//Check shared subgroup
function createSharedSubGroup($uid,$pid,$item_title='aib-shared',$isFirst='Y'){  
    
    if(isShareFolderExist($pid,$uid)){
        $responseData = array('status' => 'error', 'message' => 'Intenal item, already in use.');
        return $responseData;
    }
    
    $sessionKey = $_SESSION['aib']['session_key'];
    $postData= array(
                '_key' => APIKEY,
                '_user' => $uid,
                '_op' => 'create_item',
                '_session' => $sessionKey,
                'parent' => $pid,
                'item_title' => $item_title,
                'item_owner_id' => $uid,
                'opt_allow_dup' => 'N',
                'item_type'=>'sg'
            );
    $apiResponse = aibServiceRequest($postData, 'browse');
    //echo "<pre>";print_r($apiResponse); exit;
    $responseData = array('status' => 'error', 'message' => 'Item not created, Please try again');
        if ($apiResponse['status'] == 'OK') { 
                $archive_id = $apiResponse['info'];
                
                $aditionalProp['_key'] = APIKEY;
                $aditionalProp['_user'] = $uid;
                $aditionalProp['_op'] = 'set_item_prop';
                $aditionalProp['_session'] = $sessionKey;
                $aditionalProp['obj_id'] = $archive_id;
                $aditionalProp['propname_1'] = 'visible_to_public';
                $aditionalProp['propval_1'] = 0;
                $apiResponse = aibServiceRequest($aditionalProp, 'browse');
                
                
                
                $responseData= array('status' => 'success', 'message' => 'Item created successfully.');
                if($isFirst=='Y'){ //Create Two More Item in subgroup With fixed Name
                    $responseData=createSharedSubGroup($uid,$archive_id,'Shared-With-Me','N');
                    $responseData=createSharedSubGroup($uid,$archive_id,'Shared-With-Other','N');
                }
                
        }
        return $responseData;
}
/*
 * Link the shared record with other user.
 */
function linkTheSharedRecords($currentUID,$email,$recordId){
    $sessionKey = $_SESSION['aib']['session_key'];
    
    $recepientUsersDetails=getUserProfile('',$email);
    
    $currentUserFolderId=$_SESSION['aib']['user_data']['user_top_folder'];
    $currentUsercontainer=getSharedFolder($currentUserFolderId,$currentUID);
    $recepientUserscontainer=getSharedFolder($recepientUsersDetails['user_top_folder'],$recepientUsersDetails['user_id']);
    
    if(empty($currentUsercontainer)){
        //First Create share folder and then share
        createSharedSubGroup($currentUID,$currentUserFolderId,'aib-shared','Y');
        $currentUsercontainer=getSharedFolder($currentUserFolderId,$currentUID);
    }
    
    if(empty($recepientUserscontainer)){
        //First Create share folder and then share
        createSharedSubGroup($recepientUsersDetails['user_id'],$recepientUsersDetails['user_top_folder'],'aib-shared','Y');
        $recepientUserscontainer=getSharedFolder($recepientUsersDetails['user_top_folder'],$recepientUsersDetails['user_id']);
    }
    
        
    $recordDetails=getItemData($recordId);
    
    $postData= array(
                    '_key' => APIKEY,
                    //'_user' => $currentUID,
                    '_op' => 'create_item',
                    '_session' => $sessionKey,
                    //'parent' => $pid,
                    'item_title' => $recordDetails['item_title'],
                    'opt_allow_dup' => 'Y',
                    //'item_type'=>'rec',
                    'item_class' => 'rec',
                    'item_source'=>'L',
                    'item_reference_id'=>$recordId
                );
        foreach($currentUsercontainer as $containerList){         
                   if($containerList['item_title']=='Shared-With-Other'){
                       $postData["_user"]=$currentUID;
                       $postData["parent"]=$containerList['item_id'];
                       $postData["item_owner_id"]=$currentUID;

                        $apiResponse = aibServiceRequest($postData, 'browse');
                        $responseData = array('status' => 'error', 'message' => 'Item not created, Please try again');
                        if ($apiResponse['status'] == 'OK') { 
                              $archive_id = $apiResponse['info'];
                              $responseData[] = array('status' => 'success', 'message' => 'Item created successfully.');
                        }
                        //break;
                }
        }
        
        foreach($recepientUserscontainer as $containerList){         
                   if($containerList['item_title']=='Shared-With-Me'){
                       $postData["_user"]=$recepientUsersDetails['user_id'];
                       $postData["parent"]=$containerList['item_id'];
                       $postData["item_owner_id"]=$recepientUsersDetails['user_id'];
                        $apiResponse = aibServiceRequest($postData, 'browse');
                        $responseData = array('status' => 'error', 'message' => 'Item not created, Please try again');
                        if ($apiResponse['status'] == 'OK') { 
                              $archive_id = $apiResponse['info'];
                              $responseData[] = array('status' => 'success', 'message' => 'Item created successfully.');
                        }
                        //break;
                }
        }
        //echo "<pre>";print_r($responseData);
    
        return $responseData;
}

function getUserProfile($userId='',$loginId=''){
    
    //if(empty($userId) || empty($loginId)) return false;
    
    $sessionKey = $_SESSION['aib']['session_key'];
    
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_profile"            
        );
        
        if(!empty($userId)){
            $postData["user_id"]=$userId;
        }
        if(!empty($loginId)){
            $postData["user_login"]=$loginId;
        }
       
        
        $apiResponse = aibServiceRequest($postData, 'users');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info'];
        }
}

function getSharedFolder($folderId,$userID){ 
    $userRootItems=getItemList($folderId,$userID);
        $userSharedItems=array();
        if(isset($userRootItems)){
            foreach ($userRootItems as $key => $dataArray) {
                if(isset($dataArray['item_title']) && $dataArray['item_title']=='aib-shared'){
                    $userSharedItems=getItemList($dataArray['item_id'],$userID);
                    break;
                }
            }
        }
       return $userSharedItems; 
}



function sendMail($email_content,$template){
    $postData = array(
        "_id" => APIUSER,
        "_key" => MAIL_APIKEY
    );
    $apiResponse = aibServiceRequest($postData, 'session','send');
    $sessionKey = $_SESSION['aib']['session_key'];
    if(!empty($template) && !empty($email_content)){
        $year = date("Y");
        $logo = '<img src="'.HOST_ROOT_IMAGE_PATH.'logo.png" alt="AIB Logo" />';
        $header_image = '<img style="width:100%;" src="'.HOST_ROOT_IMAGE_PATH.'mail-template_header.jpg" alt="Image" />';
        $template = str_replace('#logo#', $logo, $template);
        $template = str_replace('#header_images#', $header_image, $template);
        $template = str_replace('#year#', $year, $template);
        $postData = array(
            "_key" => MAIL_APIKEY,
            "_session" => $apiResponse['info'],
            "_op" => "send", 
            "_user" => 1,
            "to" => $email_content['to'],
            "from" => $email_content['from'],
            "reply" => $email_content['reply'],
            "subject" => $email_content['subject'],
            "body" => $template,
            "is_html" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData,'email','send');
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );
        $apiResponse = aibServiceRequest($postData, 'session');
        return true;
    }
}

function getUserProfileProperties($user_id = null, $property_name){
    if($user_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "get_profile_prop", //list_profile_prop
            "user_id" => $user_id,
            "property_name" =>$property_name
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        $userPropertyList = [];
        if($apiResponse['status'] == 'OK'){
            $userPropertyList[$apiResponse['info']['property_name']] = $apiResponse['info']['property_value'];
        }
        return $userPropertyList;
    }
}

function getUserProfileById($user_id = null){
    if($user_id){
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => $_SESSION['aib']['user_data']['user_id'],
            "_op" => "get_profile",
            "user_id" =>$user_id
        );
        $apiResponse = aibServiceRequest($postData, 'users');
        if($apiResponse['status'] == 'OK'){
            return $apiResponse['info'];
        }
    }
}

function setUserProfileStatus($user_id, $status){
    $userDetails = getUserProfileById($user_id);
    $user_archive_id = $userDetails['user_top_folder'];
    $sessionKey = $_SESSION['aib']['session_key'];
    $postData = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => $_SESSION['aib']['user_data']['user_id'],
        "_op" => "set_profile_prop",
        "user_id" =>$user_id,
        "property_name" => 'status',
        "property_value"=> $status
    );
    $apiResponse = aibServiceRequest($postData, 'users');
    if($apiResponse['status'] == 'OK'){
        return setUserArchiveStatus($user_archive_id, $status);
    }
}

function setUserArchiveStatus($archive_id = null, $status){
    if($archive_id){
        $status = ($status == 'd') ? 0 : 1;
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            '_key' => APIKEY,
            '_user' => $_SESSION['aib']['user_data']['user_id'],
            '_op' => 'set_item_prop',
            '_session' => $sessionKey,
            'obj_id' => $archive_id,
            'propname_1' => 'status',
            'propval_1' => $status,
        );
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
