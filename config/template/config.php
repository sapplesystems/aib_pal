<?php
    session_start();
    ini_set('display_errors',0);
	require_once 'aib.php';
    define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT'] );
    define('HOST_ROOT_PATH',$_SERVER['HTTP_HOST']);
    define('CLASS_PATH', ROOT_PATH.'/');
    define('CSS_PATH','public/css/');
    define('JS_PATH','public/js/');
    define('IMAGE_PATH','public/images/');
    define('TEMPLATE_PATH',ROOT_PATH.'/templates/');
    define('COMMON_TEMPLATE_PATH',ROOT_PATH.'/templates/common/');
    define('APIUSER','test');
    define('RECORD_THUMB_URL','http://staging.archiveinabox.com:82/get_record_thumb.php');
    define('THUMB_URL','http://staging.archiveinabox.com:82/get_thumb.php');
	define('ARCHIVE_IMAGE','admin/tmp/');
	define('AIB_SEARCH_URL','http://staging.archiveinabox.com:82/cgi-bin/estsearchutil');
	define('COPY_RIGHT_URL','http://stparchive.com/copyrightnotice.php');
	define('HOST_PATH','http://staging.archiveinabox.com:82/');
    define('ADMIN_EMAIL','admin@archiveinabox.com'); //admin@archiveinabox.com
    define('EMAIL_PATH','emailer');
    define('HOST_ROOT_IMAGE_PATH',HOST_PATH.'public/images/');
	define('HISTORICAL_SOCITY_ROOT',1);
    define('PUBLIC_USER_ROOT',3);
	define('HOST_ROOT_ICON_PATH',HOST_PATH.'emailer/icon/');
	define('TESTEMAIL','test@aib.com');//test email id
	define('FONT_PATH','/home/stparch/virtual_sites/aib_historicals/2018/MONOFONT.ttf');
	define('TIMESTAMP_1',10); //content form , reprint purchase form 
    define('TIMESTAMP_2',1); //login user second time enter
    define('TIMESTAMP_4',10); //share item with email
    define('TIMESTAMP_5',10); //user resgistration for sign in
    define('TIMESTAMP_6',10);
    define('TESTEMAIL','test@aib.com');//test email id
	define('TIMESTAMP_3',1); //login user second time enter
	define('SAPPLE_EMAIL','bateshwar.mishra@sapple.co.in');
	define('BUSINESS_EMAIL','paulj@smalltownpapers.com,cody@smalltownpapers.com,jitender.kumar@sapple.co.in');
	define('HOW_IT_WORKS','http://www.archiveinabox.com/default');
define('STATE_PARENT_ID',2);
define('COUNTRY_PARENT_ID',2437);
define('ITEM_COUNT_PER_PAGE',3);
define('PUBLIC_COUNT_PER_PAGE',5);
define('ALLOW_MASS_IMPORT',true);
define('DATA_TABLE_PAGE_LENGTH',5000);

define('ENCRYPT_METHOD', 'AES-256-CBC');
define('SECRET_KEY', 'uaK4jMLm8P158hof');
define('SECRET_IV', 'sapplesystemspvtltdarchive');
define('HASH','sha256');
include 'message_config.php';

