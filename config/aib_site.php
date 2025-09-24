<?php
if (defined("AIB_SITE_INCLUDE") == false)
{
	define("AIB_SITE_INCLUDE",true);
define('AIB_STP_THUMBNAIL_HOST','aibpaldev.archiveinabox.com:53080');
define('AIB_DB_HOST','localhost');
define('AIB_DB_USER','aibuser');
define('AIB_DB_PASS','ooNgooz8');
define('AIB_DB_NAME','aibhistorical');
define('AIB_SERVER_NAME','aibpaldev.archiveinabox.com');
define('AIB_SERVER_PORT','53080');
define('AIB_SERVER_PROTOCOL','http');
define('AIB_ALLOW_COPY_PASTE','Y');
define('AIB_DOMAIN','.archiveinabox.com');
define('AIB_MAX_API_SESSION','3600');
define('HIDE_MAX_API_SESSION','3600');
define('AIB_SERVICE_URL','http://127.0.0.1:53080');
define('APIKEY',"93167f8656d6cd7c211e7efdfe6e5d4d");
define('AIB_APP_NAME','test');
define('AIB_IS_DEVELOP_SITE','N');
define('AIB_STORE_ORIGINAL_UPLOAD_FILE','N');
define('AIB_OCR_DATA_PATH','/home/stparch/ocr/aib_ocr_text_uploads');

define('AIB_RECORD_FILE_UPLOAD_PATH','/home/stparch/virtual_sites/www.archiveinabox.com/server/php/files');
define('AIB_DEFAULT_STORAGE_PATH','/home/stparch/virtual_sites/www.archiveinabox.com/file_storage');
define('AIB_OCR_FILE_QUEUE_PATH','/home/stparch/ocr/aib_ocr_files');
define('AIB_BASE_SITE_PATH','/home/stparch/virtual_sites/www.archiveinabox.com');
define('AIB_CGI_PATH','/home/stparch/virtual_sites/www.archiveinabox.com/cgi-bin');
define('AIB_NOTIFIER_EMAIL_TEMPLATE','/home/stparch/virtual_sites/www.archiveinabox.com/templates/notifier_email_template.html');
define('SENDGRID_API_KEY','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');

// Databases for indexes (where the item id is the index name)

define('AIB_BASE_INDEX_PATH','/home/stparch/virtual_sites/aib_indexes');

// Record definition document storage for indexes, where the item id is the last path
// segment for the documents.

define('AIB_BASE_INDEX_DOC_PATH','/home/stparch/virtual_sites/aib_indexdata');

define("AIB_WATER_MARK_FONT","/home/stparch/virtual_sites/www.archiveinabox.com/MONOFONT.ttf");
define("AIB_WATER_MARK_IMG","/home/stparch/virtual_sites/www.archiveinabox.com/public/images/logo-watermark.png");
define("AIB_ADMIN_TMP","/home/stparch/virtual_sites/www.archiveinabox.com/admin/tmp/");



/*define('AIB_MAIL_HOST','locahost');		// SMTP host name
define('AIB_MAIL_USER','username');		// User name for SMTP
define('AIB_MAIL_PASS','password');		// Password for SMTP
define('AIB_MAIL_PORT','587');			// Port on SMTP host
define('AIB_MAIL_TRANSPORT','tls');		// May be tls or ssl*/

/*
define('AIB_MAIL_HOST','smtp.sendgrid.net');		// SMTP host name
define('AIB_MAIL_USER','betterbnc_mail');		// User name for SMTP
define('AIB_MAIL_PASS','mQfpJL749?');		// Password for SMTP
define('AIB_MAIL_PORT','587');			// Port on SMTP host
define('AIB_MAIL_TRANSPORT','tls');		// May be tls or ssl
*/

define('AIB_MAIL_HOST','email-smtp.us-west-1.amazonaws.com');		// SMTP host name
define('AIB_MAIL_USER','AKIASZ5LEYPOYMWBPMV2');				// User name for SMTP
define('AIB_MAIL_PASS','BCKzTwuxW6NulemUuh5SCD6UpxAuJzMU2AE1b2ziRkV0');		// Password for SMTP
define('AIB_MAIL_PORT','587');			// Port on SMTP host
define('AIB_MAIL_TRANSPORT','tls');		// May be tls or ssl

define('AIB_MAIL_FROM','admin@archiveinabox.com');
define('AIB_MAIL_REPLY_TO','admin@archiveinabox.com');
define('ALLOW_MASS_IMPORT',true);
define('AIB_IMPORT_FILE_PATH','/home/stparch/aib_import/import_files');
define('AIB_AD_STORAGE_PATH','/home/stparch/aib_ad_storage');

define("AIB_USER_TMP","/home/stparch/virtual_sites/www.archiveinabox.com/tmp/"); 

define("LLM_UPDATE_QUEUE_PATH","/home/stparch/aib_utilities/llm_update_queue");

define("ENABLE_LLM_CHAT_CACHE","Y");


}
