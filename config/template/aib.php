<?php
//
// aib.php
//
// AIB config
//

include("/home/stparch/virtual_sites/aib_historicals/config/aib_site.php");

define('AIB_SESSION_TIMEOUT','3600');
define('AIB_SUPERUSER','1');
define('AIB_ANONYMOUS_USER',"-2");

define('AIB_USER_TYPE_ROOT','R');
define('AIB_USER_TYPE_ADMIN','A');
define('AIB_USER_TYPE_SUBADMIN','S');
define('AIB_USER_TYPE_USER','U');
define('AIB_USER_TYPE_PUBLIC','X');

define('AIB_DEFAULT_ITEMS_PER_PAGE','10');
define('AIB_DEFAULT_TREE_ITEMS_PER_PAGE','100');
define('AIB_DEFAULT_BROWSE_ITEMS_PER_PAGE','20');
define('AIB_ERROR_LOG','/tmp/aib_error.log');
define('AIB_SECURITY_LOG','/tmp/aib_security.log');

define('AIB_FOLDER_PROPERTY_ARCHIVE_NAME','archive_name');
define('AIB_FOLDER_PROPERTY_ARCHIVE_CODE','archive_code');
define('AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE','archive_group_code');
define('AIB_FOLDER_PROPERTY_FOLDER_TYPE','aibftype');
define('AIB_FOLDER_PROPERTY_RECORDFILEMODE','rfmode');
define('AIB_FOLDER_PROPERTY_FOLDER_ICON','folder_icon');
define('AIB_FOLDER_PROPERTY_FILE_BATCH','filebatch');

define('AIB_FOLDER_PROPERTY_STP_SORT_NAME','stp:sort_name');
define('AIB_FOLDER_PROPERTY_STP_PUB_CODE','stpapubcode');
define('AIB_FOLDER_PROPERTY_PRIVATE','aib:private');
define('AIB_FOLDER_PROPERTY_VISIBLE','aib:visible');

define('AIB_RECORD_ITEM_PROPERTY_URL','_url');

define('AIB_USER_PROPERTY_ALLOWED_FIELDS','defldyes');
define('AIB_USER_PROPERTY_NOTALLOWED_FIELDS','defldno');
define('AIB_USER_PROPERTY_ACCOUNT_CLASS','acctclass');
define('AIB_USER_PROPERTY_OWNER','owner');
define('AIB_USER_PROPERTY_DEFAULT_RIGHTS','defrights');

define('AIB_ITEM_PROPERTY_LINK_URL','linkurl');

define('AIB_ITEM_TYPE_ARCHIVE_GROUP','ag');
define('AIB_ITEM_TYPE_ARCHIVE','ar');
define('AIB_ITEM_TYPE_COLLECTION','col');
define('AIB_ITEM_TYPE_SUBGROUP','sg');
define('AIB_ITEM_TYPE_RECORD','rec');
define('AIB_ITEM_TYPE_ITEM','it');
define('AIB_ITEM_TYPE_USER','us');
define('AIB_ITEM_TYPE_TRADITIONAL','trad');
define('AIB_ITEM_TYPE_RECOMMENDED','rec');
define('AIB_ITEM_TYPE_SYSTEM','sys');
define('AIB_ITEM_TYPE_SCRAPBOOK_ENTRY','scrpbkent');
define('AIB_ITEM_TYPE_SCRAPBOOK','scrpbk');
define('AIB_ITEM_TYPE_SCRAPBOOK_SET','scrpbkset');
define('AIB_ITEM_TYPE_COMMENT','cmnt');
define('AIB_ITEM_TYPE_COMMENT_THREAD','cmntthrd');
define('AIB_ITEM_TYPE_COMMENT_SET','cmntset');

define('AIB_PREDEF_FOLDER_NAME_SCRAPBOOK_SET','Scrapbooks');
define('AIB_PREDEF_FOLDER_NAME_COMMENT_SET','Comments');
define('AIB_PREDEF_FOLDER_NAME_SHARE_SET','Shares');
define('AIB_PREDEF_FOLDER_NAME_ARCHIVE_GROUP_ROOT','ARCHIVE GROUP');
define('AIB_PREDEF_FOLDER_NAME_CONSTANTS_ROOT','_CONSTANTS');
define('AIB_PREDEF_FOLDER_NAME_STANDARD_USERS_ROOT','_STDUSER');
define('AIB_PREDEF_FOLDER_NAME_SYSTEM','_SYS');
define('AIB_PREDEF_FOLDER_NAME_USER_CLASSES','USERCLASSES');
define('AIB_PREDEF_FOLDER_NAME_COUNTRY_DEF','LOC.COUNTRYDEF');
define('AIB_PREDEF_FOLDER_NAME_COUNTY_DEF','LOC.COUNTYDEF');
define('AIB_PREDEF_FOLDER_NAME_STATE_DEF','LOC.STATEDEF');
define('AIB_PREDEF_FOLDER_NAME_CITY_DEF','LOC.CITYDEF');
define('AIB_PREDEF_FOLDER_NAME_POSTAL_DEF','LOC.POSTALDEF');

define('AIB_ARCHIVE_GROUP_ROOT',':ARCHIVE GROUP');

define('AIB_BATCH_RECORD_TYPE_UPLOAD','RU');
define('AIB_BATCH_RECORD_TYPE_OCR_REQUEST','ORQ');

define("AIB_BATCH_STORAGE_REQUEST","STO");
define("AIB_BATCH_OCR_REQUEST","OCR");
define("AIB_BATCH_USE_ALT_TITLE","UAT");

define("AIB_VALID_OCR_SOURCE_LIST","jpg,jpeg,pdf,tiff,tif,png,gif");
define("AIB_PDF_MIN_PAGE_TEXT_SIZE","256");

define("AIB_FILE_CLASS_PRIMARY","pr");
define("AIB_FILE_CLASS_THUMB","tn");
define("AIB_FILE_CLASS_TEXT","tx");
define("AIB_FILE_CLASS_TEXT_LOCATION","tl");
define("AIB_FILE_CLASS_ORIGINAL","or");

define("AIB_DEFAULT_THUMBNAIL_WIDTH","256");
define("AIB_MAX_IMAGE_WIDTH","1800");

define("AIB_SESSION_PROPERTY_PAGE","pg");
define("AIB_SESSION_PROPERTY_ACTION","ac");
define("AIB_SESSION_PROPERTY_ACTION_INFO","acinf");

define("ARCHIVE_GROUP_PARENT_NAME","ARCHIVE GROUP");

define("CUST_REQUEST_REDACT","RD");
define("CUST_REQUEST_CONTACT","CT");
define("CUST_REQUEST_REPRINT","RP");

define("STP_ARCHIVE_DOMAIN","stparchive.com");
define("STP_ARCHIVE_PUBLICATION_CODE","stpapubcode");

define("AIB_PREDEF_FIELD_OCRTEXT","ocrtxt");
define("AIB_PREDEF_FIELD_COUNTRY","country");
define("AIB_PREDEF_FIELD_STATE","state");
define("AIB_PREDEF_FIELD_COUNTY","county");
define("AIB_PREDEF_FIELD_CITY","city");
define("AIB_PREDEF_FIELD_POSTAL","postalcode");
define("AIB_PREDEF_FIELD_DESCRIPTION","desc");
define("AIB_PREDEF_FIELD_COMMENT_TEXT","cmttxt");
define("AIB_PREDEF_FIELD_COMMENT","cmttxt");
define("AIB_PREDEF_FIELD_OCR_TEXT","ocrtxt");
define("AIB_PREDEF_FIELD_ALTITUDE","alt");
define("AIB_PREDEF_FIELD_LATITUDE","lat");
define("AIB_PREDEF_FIELD_LONGITUDE","lon");
define("AIB_PREDEF_FIELD_CREATOR","creator");
define("AIB_PREDEF_FIELD_DATE","date");
define("AIB_PREDEF_FIELD_PROVENANCE","provenance");
define("AIB_PREDEF_FIELD_LOCATION","geoloc");
define("AIB_PREDEF_FIELD_INFOTEXT","infotxt");
define("AIB_PREDEF_FIELD_URL","url");

define("AIB_NOTIFIER_MATCH_TYPE_GENERIC","G");
define("AIB_NOTIFIER_MATCH_TYPE_RECORD","R");

define("AIB_NOTIFIER_SUBJECT","New Items Of Interest To You From ArchiveInABox");

$GLOBALS["aib_hide_predef_field_list"] = array(
			AIB_PREDEF_FIELD_OCRTEXT => true,
			AIB_PREDEF_FIELD_COUNTRY => true,
			AIB_PREDEF_FIELD_STATE => true,
			AIB_PREDEF_FIELD_COUNTY => true,
			AIB_PREDEF_FIELD_CITY => true,
			AIB_PREDEF_FIELD_POSTAL => true,
			AIB_PREDEF_FIELD_COMMENT_TEXT => true,
			AIB_PREDEF_FIELD_COMMENT => true,
			AIB_PREDEF_FIELD_LATITUDE => true,
			AIB_PREDEF_FIELD_LONGITUDE => true,
			AIB_PREDEF_FIELD_ALTITUDE => true,
			AIB_PREDEF_FIELD_PROVENANCE => true,
			AIB_PREDEF_FIELD_URL => true,
			);

?>
