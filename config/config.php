<?php
    session_set_cookie_params(10800);
    ini_set('session.gc_maxlifetime', 10800);
    session_start();
    ini_set('display_errors',0);
	require_once 'aib.php';
/***************************Start Code change for Bug ID 1995/ Dated: 12-Sep-2022*****************************************/
if (defined("AIB_MAIN_CONFIG") == false)
{
	define('AIB_MAIN_CONFIG',true);
	/***************************End Code change for Bug ID 1995/ Dated: 12-Sep-2022*****************************************/
    define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT'] );
    define('HOST_ROOT_PATH',$_SERVER['HTTP_HOST']);
    define('CLASS_PATH', ROOT_PATH.'/');
    define('CSS_PATH','public/css/');
    define('JS_PATH','public/js/');
    define('IMAGE_PATH','public/images/');
    define('TEMPLATE_PATH',ROOT_PATH.'/templates/');
    define('COMMON_TEMPLATE_PATH',ROOT_PATH.'/templates/common/');
    define('APIUSER','test');
    define('RECORD_THUMB_URL','http://aibpaldev.archiveinabox.com:53080/get_record_thumb.php');
    define('THUMB_URL','http://aibpaldev.archiveinabox.com:53080/get_thumb.php');
	define('ARCHIVE_IMAGE','admin/tmp/');
	define('AIB_SEARCH_URL','http://aibpaldev.archiveinabox.com:53080/cgi-bin/estsearchutil');
	define('COPY_RIGHT_URL','https://stparchive.com/copyrightnotice.php');
	define('HOST_PATH','http://aibpaldev.archiveinabox.com:53080/');
    define('ADMIN_EMAIL','admin@archiveinabox.com'); //admin@archiveinabox.com
    define('EMAIL_PATH','emailer');
    define('HOST_ROOT_IMAGE_PATH',HOST_PATH.'public/images/');
	define('HISTORICAL_SOCITY_ROOT',1);
    define('PUBLIC_USER_ROOT',3);
	define('HOST_ROOT_ICON_PATH',HOST_PATH.'emailer/icon/');
	define('TESTEMAIL','test@aib.com');//test email id
	define('FONT_PATH','/home/stparch/virtual_sites/aibpaldev.archiveinabox.com/2018/MONOFONT.ttf');
	define('TIMESTAMP_1',10); //content form , reprint purchase form 
    define('TIMESTAMP_2',1); //login user second time enter
    define('TIMESTAMP_4',10); //share item with email
    define('TIMESTAMP_5',10); //user resgistration for sign in
    define('TIMESTAMP_6',10);
    define('TESTEMAIL','test@aib.com');//test email id
	define('TIMESTAMP_3',1); //login user second time enter
	define('SAPPLE_EMAIL','bateshwar.mishra@sapple.co.in');
	define('BUSINESS_EMAIL','paulj@smalltownpapers.com,cody@smalltownpapers.com,aib_validations@smalltownpapers.com');
	define('HOW_IT_WORKS','http://aibpaldev.archivenabox.com:53080/default');
	define('STATE_PARENT_ID',2);
	define('COUNTRY_PARENT_ID',2825);
    define('SECURITY_QUESTION_PARENT_ID',1199445);
	define('ITEM_COUNT_PER_PAGE',3);
	define('ITEM_COUNT_PER_PAGE_DEFAULT',6);
	define('ITEM_COUNT_PER_PAGE_CUSTOM2',5);
	define('ITEM_COUNT_PER_PAGE',3);
	define('PUBLIC_COUNT_PER_PAGE',20);
	define('DATA_TABLE_PAGE_LENGTH',5000);
define('TRENDING_ARTIFACTS_ID', 795095);	
define('GOOGLE_AD_SCRIPT','<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- AIB Square 300x250 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:250px"
     data-ad-client="ca-pub-6093015826500083"
     data-ad-slot="8236013247"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>');

define('ENCRYPT_METHOD', 'AES-256-CBC');
define('SECRET_KEY', 'uaK4jMLm8P158hof');
define('SECRET_IV', 'sapplesystemspvtltdarchive');
define('HASH','sha256');

define('SEARCH_RESULT_COUNT', '100');
	
	define('SEARCH_INDEX_PATH', '/home/stparch/virtual_sites/aib_indexes');
define('SEARCH_BASE_URL', 'http://aibpaldev.archiveinabox.com:53080');
define('SEARCH_BASE_CURL_URL', 'http://aibpaldev.archiveinabox.com:53080/api/hsearch.php');  


//Fix start for Issue ID 2420 on 04-Feb-2025
define('CSS_VERSION', 2);
define('JS_VERSION', 3);
define('IMG_VERSION', 1);
//Fix end for Issue ID 2420 on 04-Feb-2025  
define('LLM_SEARCH_WORD_LIMIT', 50);
}

include 'message_config.php';
