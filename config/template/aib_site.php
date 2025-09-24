<?php
define('AIB_DB_HOST','localhost');
define('AIB_DB_USER','aibuser');
define('AIB_DB_PASS','ooNgooz8');
define('AIB_DB_NAME','aibhistorical');
define('AIB_SERVER_NAME','staging.archiveinabox.com:82');
define('AIB_SERVER_PORT','80');
define('AIB_SERVER_PROTOCOL','http');
define('AIB_ALLOW_COPY_PASTE','Y');
define('AIB_DOMAIN','.archiveinabox.com');
define('AIB_MAX_API_SESSION','3600');
define('AIB_SERVICE_URL','http://staging.archiveinabox.com:82');
define('APIKEY',"87fc0d6d9689d84ab48f583175f9522dx");
define('AIB_APP_NAME','test');
define('AIB_IS_DEVELOP_SITE','Y');
define('AIB_STORE_ORIGINAL_UPLOAD_FILE','N');
define('AIB_OCR_DATA_PATH','/home/stparch/ocr/aib_ocr_text_uploads');

define('AIB_RECORD_FILE_UPLOAD_PATH','/home/stparch/virtual_sites/aib_historicals/server/php/files');
define('AIB_DEFAULT_STORAGE_PATH','/home/stparch/virtual_sites/aib_historicals/file_storage');
define('AIB_OCR_FILE_QUEUE_PATH','/home/stparch/ocr/aib_ocr_files');
define('AIB_BASE_SITE_PATH','/home/stparch/virtual_sites/aib_historicals');
define('AIB_CGI_PATH','/home/stparch/virtual_sites/aib_historicals/cgi-bin');
define('AIB_NOTIFIER_EMAIL_TEMPLATE','/home/stparch/virtual_sites/aib_historicals/templates/notifier_email_template.html');

// Databases for indexes (where the item id is the index name)

define('AIB_BASE_INDEX_PATH','/home/stparch/virtual_sites/aib_indexes');

// Record definition document storage for indexes, where the item id is the last path
// segment for the documents.

define('AIB_BASE_INDEX_DOC_PATH','/home/stparch/virtual_sites/aib_indexdata');

define("AIB_WATER_MARK_FONT","/home/stparch/virtual_sites/aib_historicals/MONOFONT.ttf");
define("AIB_WATER_MARK_IMG","/home/stparch/virtual_sites/aib_historicals/public/images/logo-watermark.png");
define("AIB_ADMIN_TMP","/home/stparch/virtual_sites/aib_historicals/admin/tmp/");



/*define('AIB_MAIL_HOST','locahost');		// SMTP host name
define('AIB_MAIL_USER','username');		// User name for SMTP
define('AIB_MAIL_PASS','password');		// Password for SMTP
define('AIB_MAIL_PORT','587');			// Port on SMTP host
define('AIB_MAIL_TRANSPORT','tls');		// May be tls or ssl*/

define('AIB_MAIL_HOST','smtp.sendgrid.net');		// SMTP host name
define('AIB_MAIL_USER','betterbnc_mail');		// User name for SMTP
define('AIB_MAIL_PASS','z8amazon8$');		// Password for SMTP
define('AIB_MAIL_PORT','587');			// Port on SMTP host
define('AIB_MAIL_TRANSPORT','tls');		// May be tls or ssl

define('AIB_MAIL_FROM','admin@archiveinabox.com');
define('AIB_MAIL_REPLY_TO','admin@archiveinabox.com');

define('ALLOW_MASS_IMPORT',true);
define('AIB_IMPORT_FILE_PATH','/raid2/import_files');
