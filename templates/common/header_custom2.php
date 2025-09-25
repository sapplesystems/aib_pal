<?php
$cssArray = ['bootstrap.css', 'class.css', 'custom2.css', 'item-custom-detail.css', 'society_class.css', 'aib-responsive.css', 'ideal-image-slider.css', 'default.css', 'common.css', 'style_common.css', 'style1.css', 'jquery.dataTables.min.css', 'sappleslider.multi.css', 'tabulous.css', 'component.css', 'jquery.tagit.css', 'tagit.ui-zendesk.css', 'magicsuggest.css', 'jquery.mCustomScrollbar.css', 'selectize.bootstrap.css'];
$loadedFileName = end(explode("/", $_SERVER['SCRIPT_NAME']));
setcookie('aib_page_id', 1, time() + (86400 * 30), "/");
if (!function_exists('aibServiceRequest')) {

    function aibServiceRequest($postData, $fileName, $mail = null) {
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
$title = '';
$description = '';
$ocrText = '';
//if($loadedFileName == 'item-details.php'){
$itemId = (isset($_REQUEST['itemId']) && $_REQUEST['itemId'] != '') ? $_REQUEST['itemId'] : $_REQUEST['folder_id'];
if (isset($_REQUEST['folder-id'])) {
    $itemId = $_REQUEST['folder-id'];
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


if ($loadedFileName == 'item-details.php') {
    if (isset($_REQUEST['itemId']) && $_REQUEST['itemId'] != '') {
        $itemId = $_REQUEST['itemId'];
    } else {
        $postDataItem = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => $itemId,
            "opt_deref_links" => 'Y',
        );
        $apiResponse = aibServiceRequest($postDataItem, 'browse');
        if ($apiResponse['status'] == 'OK' && !empty($apiResponse['info']['records'])) {
            foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                if ($dataArray['item_type'] != 'IT') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['properties']['aib:visible']) && $dataArray['properties']['aib:visible'] == 'N') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['item_type']) && $dataArray['item_type'] == 'cmntset') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'related_content') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'historical_connection') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'scrapbook') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if (strtolower($dataArray['item_title']) == 'advertisements') {
                    unset($apiResponse['info']['records'][$key]);
                }
                if (strtolower($dataArray['item_title']) == 'shared out of system') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
                if ($dataArray['item_title'] == 'Scrapbooks') {
                    unset($apiResponse['info']['records'][$key]);
                    continue;
                }
            }
            $apiResponse['info']['records'] = array_values($apiResponse['info']['records']);
            $itemId = $apiResponse['info']['records'][0]['item_id'];
        }
    }
}
if ($itemId && $sessionKey) {
    $postDataItem = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "get",
        "opt_get_field" => 'Y',
        "obj_id" => $itemId
    );
    $itemDetails = aibServiceRequest($postDataItem, 'browse');
    if ($itemDetails['status'] == 'OK') {
        $title = (isset($itemDetails['info']['records'][0]) && $itemDetails['info']['records'][0]['item_title'] != '') ? trim(preg_replace('/[\t\n\r\s]+/', ' ', $itemDetails['info']['records'][0]['item_title'])) : "";
        $descriptionFields = '';
        if (!empty($itemDetails['info']['records'][0]['fields'])) {
            foreach ($itemDetails['info']['records'][0]['fields'] as $fieldValue) {
                $descriptionFields .= $fieldValue['field_title'] . ': ' . $fieldValue['field_value'] . ', ';
            }
            $descriptionFields = trim($descriptionFields, ', ');
        }
    }
    $postDataOcr = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_op" => "get_named_fields",
        "_user" => 1,
        "obj_id" => $itemId
    );
    $ocrDetails = aibServiceRequest($postDataOcr, 'fields');
    if ($ocrDetails['status'] == 'OK') {
        if (!empty($ocrDetails['info']['records'])) {
            foreach ($ocrDetails['info']['records'] as $dataArray) {
                $ocrText .= ',' . $dataArray['value'];
            }
            $ocrText = trim($ocrText, ',');
        }
    }
    if ($descriptionFields != '') {
        $ocrText = $ocrText . ', ' . $descriptionFields;
    }
    /*     * ******************Get path****************** */
    $tags = '';
    $postDataOcr = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_user" => 1,
        "_op" => "tags_get",
        "obj_id" => $itemId
    );
    $ocrDetails = aibServiceRequest($postDataOcr, 'tags');
    if ($ocrDetails['status'] == 'OK') {
        if (!empty($ocrDetails['info']['records'])) {
            $tags = implode($ocrDetails['info']['records'], ',');
        }
    }
    $postDataOcr = array(
        "_key" => APIKEY,
        "_session" => $sessionKey,
        "_op" => "get_path",
        "_user" => 1,
        "obj_id" => $itemId
    );
    $description = [];
    $ocrDetails = aibServiceRequest($postDataOcr, 'browse');
    if ($ocrDetails['status'] == 'OK') {
        if (!empty($ocrDetails['info']['records'])) {
            array_shift($ocrDetails['info']['records']);
            foreach ($ocrDetails['info']['records'] as $dataArray) {
                $description[] = trim(preg_replace('/[\t\n\r\s]+/', ' ', $dataArray['item_title']));
            }
            $descriptionMeta = implode($description, '/');
            $keywords = implode($description, ',');
        }
    }
    if ($keywords != '') {
        $ocrText = $ocrText . ', ' . $keywords;
    }
    if ($tags != '') {
        $ocrText = $ocrText . ', Tags: ' . $tags;
    }
    if ($title != '') {
        $ocrText = $ocrText . ', ' . $title;
    }
}
//}
$ocrText = trim($ocrText, ',');
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="shortcut icon" type="image/png" href="favicon.ico"/>
        <meta name="keywords" content="<?php echo ($title != '') ? $title : ''; ?>" />
        <meta name="description" content="<?php echo ($descriptionMeta != '') ? $descriptionMeta : ''; ?>" />
        <title>ArchiveInABox <?php echo ($title != '') ? ' -- ' . $title : ''; ?></title>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
        <meta property="og:title" content="<?php echo ($title != '') ? $title : ''; ?>" />
        <meta property="og:url" content="<?php echo AIB_SERVICE_URL . $_SERVER['REQUEST_URI']; ?>" />
        <meta property="og:description" content="<?php echo ($descriptionMeta != '') ? $descriptionMeta : urldecode($title); ?>	" />
        <meta property="og:type" content="article" />
        <!--<meta property="og:image" content="http://www.stparchive.com/fb_thumb.php?url=ABD01292014P01.php" />-->
        <?php foreach ($cssArray as $key => $fileName) { ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH . $fileName; ?>" />
        <?php } ?>	
    </head>
    <body>
        <div class="loading-div">
            <img class="loading-img" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." />
        </div>
        <div class="loading-div-fullPage">
            <img class="loading-img" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." />
        </div>
        <div class="container-fluid bgGradient_head">
            <div class="row">
                <div class="col-md-3 col-sm-3">
					<div class="aib_logo_header">
						<span class="society_name_header">Granite Falls</span>
					</div>
				</div>
                <div class="col-md-9 col-sm-9 Logocenter">
                    <?php if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) { ?>
                        <ul class="logInBtn">
                            <li>
                                <a href="javascript:void(0);">
                                    <img class="imgUser" alt="User Image" src="<?php echo IMAGE_PATH . 'avatar-2.png'; ?>" />&nbsp; 
                                    <?php echo $_SESSION['aib']['user_data']['user_login']; ?>&nbsp;
									<span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                                </a>
                                <ul class="headerDropdown">
                                    <span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span>
                                    <li><a class="" href="admin/manage_my_archive.html"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Manage Your Account</a></li>
                                    <li><a class="logout-user" href="javascript:void(0);"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    <?php } else { ?>
                        <a class="loginStyle loginPopup" href="javascript:void(0);">
                            <span class="glyphicon glyphicon-user" aria-hidden="true"></span> Login
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>