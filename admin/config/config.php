<?php
	include 'message_config.php';
	require_once  '/home/stparch/virtual_sites/www.archiveinabox.com/config/aib.php';
	include 'acl.php';
if (defined("CONFIG_INCLUDE") == false)
{
	define("CONFIG_INCLUDE",true);
	define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT'].'/admin' );
	define('HOST_ROOT_PATH',$_SERVER['HTTP_HOST'].'/admin');
	define('CLASS_PATH', ROOT_PATH.'/');
	define('CSS_PATH','public/css/');
	define('JS_PATH','public/js/');
	define('IMAGE_PATH','public/images/');
	define('TEMPLATE_PATH',ROOT_PATH.'/templates/');
	define('COMMON_TEMPLATE_PATH',ROOT_PATH.'/templates/common/');
	define('APIUSER','test');
	 define('RECORD_THUMB_URL','http://aibpaldev.archiveinabox.com:53080/get_record_thumb.php');
	define('THUMB_URL','http://aibpaldev.archiveinabox.com:53080/get_thumb.php');
	define('IMAGE_TARGET_PATH','tmp/');
	define('ARCHIVE_IMAGE','tmp/');
	define('EMAIL_PATH','../emailer');
	define('HOST_PATH','http://aibpaldev.archiveinabox.com:53080/');
	define('HOST_ROOT_IMAGE_PATH',HOST_PATH.'public/images/');
	define('HOST_ROOT_ICON_PATH',HOST_PATH.'emailer/icon/');
	define('ADMIN_EMAIL','admin@archiveinabox.com');
	define('FONT_PATH','/home/stparch/virtual_sites/www.archiveinabox.com/2018/MONOFONT.ttf');
	define('HOST_ADMIN_IMAGE_PATH',HOST_PATH.'admin/'.ARCHIVE_IMAGE);
	//define('RECAPTCHA_KEY','6LccRlYUAAAAAJHVV-sSpV-0NjwW90zjFlkh9u2W');  //local
	define('TIMESTAMP_1',10); //content form , reprint purchase form 
	define('TIMESTAMP_2',1); //login user second time enter
	define('TIMESTAMP_4',10); //share item with email
	define('TIMESTAMP_5',10); //user resgistration for sign in
	define('TIMESTAMP_6',10);
	define('TESTEMAIL','test@aib.com');//test email id
	define('TIMESTAMP_3',1); //login user second time enter
	define('SERVER_ROOT_PATH','/home/stparch/virtual_sites/www.archiveinabox.com/');
	define('SERVER_IMAGE_PATH','/home/stparch/virtual_sites/www.archiveinabox.com/images/');
	define('HOW_IT_WORKS','https://www.archiveinabox.com/default');
	define('STATE_PARENT_ID',2);
	define('COUNTRY_PARENT_ID',2825);
	define('SECURITY_QUESTION_PARENT_ID',1199445);
	define('EBAY_LINK_LIMIT',3);
	define('ITEM_COUNT_PER_PAGE',3);
	define('PUBLIC_COUNT_PER_PAGE',5);
	define('ALLOW_MASS_IMPORT',true);
	define('BUSINESS_EMAIL','paulj@smalltownpapers.com,cody@smalltownpapers.com');
	define('AIB_IMPORT_FILE_PATH','/raid2/import_files');
	define('DATA_TABLE_PAGE_LENGTH',5000);
	define('SENDGRID_API_KEY','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');
	define('AIB_DOWNLOAD_ENABLE_RECORD_LEVEL', array(539319,4));
	define('AIB_SERVICE_FILE_PATH', 'http://'.$_SERVER['HTTP_HOST'].'/');
        define('AIB_GOOGLE_MAP_KEY','AIzaSyBS-I7WjA-LsMXWhVvPFWKIoQBnCXGp_WY');
	/********* fix start Bug id 2359 23-June-2204 ****************/
	//Changes for Encryption/Decryption
define('ENCRYPT_METHOD', 'AES-256-CBC');
define('SECRET_KEY', 'uaK4jMLm8P158hof');
define('SECRET_IV', 'sapplesystemspvtltdarchive');
define('HASH', 'sha256');
	/********* fix end Bug id 2359 23-June-2204 ****************/
}
