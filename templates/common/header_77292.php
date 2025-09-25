<?php
$cssArray = ['bootstrap.css', 'class_77292.css','society_class.css', 'aib-responsive.css', 'ideal-image-slider.css', 'default.css', 'common.css', 'style_common.css', 'style1.css','jquery.dataTables.min.css','sappleslider.multi.css','tabulous.css','component.css','jquery.tagit.css','tagit.ui-zendesk.css','magicsuggest.css','jquery.mCustomScrollbar.css','selectize.bootstrap.css'];
$loadedFileName = end(explode("/", $_SERVER['SCRIPT_NAME']));
setcookie('aib_page_id', 1, time() + (86400 * 30), "/");
if(!function_exists('aibServiceRequest')){
    function aibServiceRequest($postData, $fileName, $mail = null) {
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
$title       = '';
$description = '';
$ocrText     = '';
//if($loadedFileName == 'item-details.php'){
    $itemId = (isset($_REQUEST['itemId']) && $_REQUEST['itemId'] != '') ? $_REQUEST['itemId'] : $_REQUEST['folder_id'];
    if(isset($_REQUEST['folder-id'])){
        $itemId = $_REQUEST['folder-id'];
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
    
    
    if($loadedFileName == 'item-details.php'){
        if(isset($_REQUEST['itemId']) && $_REQUEST['itemId'] != ''){
            $itemId = $_REQUEST['itemId'];
        }else{
            $postDataItem = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "list",
                "parent" => $itemId,
                "opt_deref_links" =>'Y',
            );     
            $apiResponse = aibServiceRequest($postDataItem, 'browse');
            if($apiResponse['status'] == 'OK' && !empty($apiResponse['info']['records'])){
                foreach ($apiResponse['info']['records'] as $key => $dataArray) {
                    if($dataArray['item_type'] != 'IT'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(isset($dataArray['properties']['aib:visible']) && $dataArray['properties']['aib:visible'] == 'N'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }               
                    if(isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == '0'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    } 
                    if(isset($dataArray['item_type']) && $dataArray['item_type'] == 'cmntset'){
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
                    if(isset($dataArray['link_properties']['link_class']) && $dataArray['link_properties']['link_class'] == 'scrapbook'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if(strtolower($dataArray['item_title']) == 'advertisements'){
                        unset($apiResponse['info']['records'][$key]);
                    }
                    if(strtolower($dataArray['item_title']) == 'shared out of system'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                    if($dataArray['item_title'] == 'Scrapbooks'){
                        unset($apiResponse['info']['records'][$key]);
                        continue;
                    }
                }
                $apiResponse['info']['records'] = array_values($apiResponse['info']['records']);
                $itemId = $apiResponse['info']['records'][0]['item_id'];
            }
        }
    }
    if($itemId && $sessionKey){
        $postDataItem = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get",
            "opt_get_field" => 'Y',
            "obj_id" => $itemId
        );     
        $itemDetails = aibServiceRequest($postDataItem, 'browse');
        if($itemDetails['status'] == 'OK'){
            $title = (isset($itemDetails['info']['records'][0]) && $itemDetails['info']['records'][0]['item_title'] != '') ? trim(preg_replace('/[\t\n\r\s]+/', ' ', $itemDetails['info']['records'][0]['item_title'])) : "";
            $descriptionFields = '';
            if(!empty($itemDetails['info']['records'][0]['fields'])){
                foreach($itemDetails['info']['records'][0]['fields'] as $fieldValue){
                    $descriptionFields .= $fieldValue['field_title'].': '.$fieldValue['field_value'].', ';
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
        if($ocrDetails['status'] == 'OK'){
            if(!empty($ocrDetails['info']['records'])){
                foreach($ocrDetails['info']['records'] as $dataArray){
                    $ocrText .= ','.$dataArray['value'];
                }
                $ocrText = trim($ocrText, ',');
            }
        }
        if($descriptionFields != ''){
            $ocrText = $ocrText.', '.$descriptionFields;
        }
	/********************Get path*******************/
	$tags='';
	$postDataOcr = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "tags_get",
            "obj_id" => $itemId
        );
        $ocrDetails = aibServiceRequest($postDataOcr, 'tags');
        if($ocrDetails['status'] == 'OK'){
            if(!empty($ocrDetails['info']['records'])){
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
	$description =[];
        $get_path = $ocrDetails = aibServiceRequest($postDataOcr, 'browse');
        if($ocrDetails['status'] == 'OK'){
            if(!empty($ocrDetails['info']['records'])){
                array_shift($ocrDetails['info']['records']);
                foreach($ocrDetails['info']['records'] as $dataArray){
                    $description[]= trim(preg_replace('/[\t\n\r\s]+/', ' ', $dataArray['item_title']));
                }
                $descriptionMeta = implode($description, '/');
                $keywords = implode($description, ',');
            }
        }
        if($keywords != ''){
            $ocrText = $ocrText.', '.$keywords;
        }
        if($tags != ''){
            $ocrText = $ocrText.', Tags: '.$tags;
        }
        if($title != ''){
            $ocrText = $ocrText.', '.$title;
        }
    }
//}
$ocrText = trim($ocrText, ',');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="shortcut icon" type="image/png" href="favicon.ico"/>
        <meta name="keywords" content="<?php echo ($title != '')? $title: ''; ?>" />
        <meta name="description" content="<?php echo ($descriptionMeta != '')? $descriptionMeta: ''; ?>" />
        <title>ArchiveInABox <?php echo ($title != '')? ' -- '.$title: ''; ?></title>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
        <meta property="og:title" content="<?php echo ($title != '')?$title: ''; ?>" />
        <meta property="og:url" content="<?php echo AIB_SERVICE_URL.$_SERVER['REQUEST_URI'];?>" />
        <meta property="og:description" content="<?php echo ($descriptionMeta != '') ? $descriptionMeta: urldecode($title); ?>	" />
        <meta property="og:type" content="article" />
        <!--<meta property="og:image" content="http://www.stparchive.com/fb_thumb.php?url=ABD01292014P01.php" />-->
        <?php foreach ($cssArray as $key => $fileName) { ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH . $fileName; ?>" />
        <?php } ?>
        <style> #slider{height:460px !important;}</style>	
    </head>
    <body>
        <div class="loading-div">
            <img class="loading-img" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." />
        </div>
        <div class="loading-div-fullPage">
            <img class="loading-img" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." />
        </div>
        <div class="bgTopStripe">
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="col-md-2 col-sm-2 col-xs-12 centerText header_logo">
                        <?php if(strpos($_SERVER['REQUEST_URI'],"society") !== false){ ?>
                            <!--a href="index.html"><img style="margin-top:-30px; margin-left: -15px;" height="100" src="<?php //echo IMAGE_PATH . 'img-6.png'; ?>" alt="" /></a-->
                        <?php }else if(strpos($_SERVER['REQUEST_URI'],"public_search") == true){ ?>
                            <a href="index.html"><img height="40" src="<?php echo IMAGE_PATH . 'logo-aib.png'; ?>" alt="" /></a>
                        <?php }else if(strpos($_SERVER['REQUEST_URI'],"search") == true){ ?>
                            <!--<a href="index.html"><img height="40" src="<?php echo IMAGE_PATH . 'logo-aib.png'; ?>" alt="" /></a>-->
                        <?php }else if((count($get_path['info']['records']) == 1 && $get_path['info']['records'][0]['item_type'] == 'IT') || !$itemId){ ?>
                            <a href="index.html"><img height="40" src="<?php echo IMAGE_PATH . 'logo-aib.png'; ?>" alt="" /></a>
                        <?php }else if(strpos($_SERVER['REQUEST_URI'],"item-details") !== false){ ?>
                            <a href="index.html"><img style="margin-top:-20px;" height="80" src="<?php echo IMAGE_PATH . 'img-6.png'; ?>" alt="" /></a>
                        <?php } ?>
                    </div>
                    <div class="col-md-8 col-sm-2 col-xs-12 minCenterDiv"></div>
                    <div class="col-md-2 col-sm-2 col-xs-12 text-right textAlignCenter topMargin10 min300">
                        <ul class="header-menu pull-right">
                            <?php if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) { ?>
                                <!--<li><a href="#"><span class="glyphicon glyphicon-knight" aria-hidden="true"></span> OWNER</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> ASSISTANTS</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> MY ARCHIVE</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-signal" aria-hidden="true"></span> REVENUE</a></li> -->
                                <!--<li><a href="search.php"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> SEARCH</a></li> -->
                                <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="<?php echo IMAGE_PATH . 'avatar.png'; ?>" class="user-image" alt="User Image">
                                    <span class=""><?php echo $_SESSION['aib']['user_data']['user_login']; ?></span>
                                </a>
                                <ul class="dropdown-menu menuDropdown"> 
                                    <li>
                                        <a href="admin/manage_my_archive.html" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>Manage Your Account</a>
                                    </li>
                                    <li>
                                        <a class="logout-user" href="javascript:void(0);" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>Log out</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <li style="display:none;"><a href="javascript:void(0);" class="loginPopup"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> LOGIN</a></li>
                                <!--<li class="logout-user"><a href="javascript:void(0);"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> LOGOUT</a></li>-->
                            <?php }else{ ?>
                                <!--<li><a href="search.php"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> SEARCH</a></li> -->
                                
                                <li><a href="javascript:void(0);" class="loginPopup"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> LOGIN</a></li>
                                
                            <?php } ?>
                        </ul>
                        <!--<div class="pull-right responsive-menu">
                            <div class="imgMenu"><img src="<?php echo IMAGE_PATH . 'responsive-menu.png'; ?>">
                                <ul class="btn-header">
                                    <div class="arrow-up"></div>
                                    <?php if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) { ?>
                                        <li><a href="#"><span class="glyphicon glyphicon-knight" aria-hidden="true"></span> OWNER</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> ASSISTANTS</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> MY ARCHIVE</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-signal" aria-hidden="true"></span> REVENUE</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> SUPER ADMIN</a></li>
                                        <li class="logout-user"><a href="javascript:void(0);"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> LOGOUT</a></li>
                                    <?php }else{ ?>
                                        <li><a href="javascript:void(0);"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> LOGIN</a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>