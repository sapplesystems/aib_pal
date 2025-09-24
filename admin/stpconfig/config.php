<?php
ini_set('display_errors', 'On');
session_start();

//define("ROOT_PATH_WITHOUT_SLASH",'/home/stparch/virtual_sites/CPG');
define("ROOT_PATH_WITHOUT_SLASH",'/home/stparch/public_html');
define("ROOT_PATH", ROOT_PATH_WITHOUT_SLASH . '/');
define("HOST_ROOT_PATH","http://".$_SERVER['HTTP_HOST'].'/');
define("HOST_ROOT_PATH_WITHOUT_SLASH",$_SERVER['HTTP_HOST']);
define("PRIMARY_DOMAIN_NAME",$_SERVER['HTTP_HOST']);

define("DBUSER",'stparch');                        //Database User
define("DBPASS",'archive');                                //Database Password
define("DBHOST",'localhost');                //Database Host122.160.68.237
define("DATABASE",'stparchive');

define("SESSION_PREFIX","STPJOB_");

define('MAIN_DIR', ROOT_PATH.'Main');
define('PROCESS_DIR', ROOT_PATH.'Archive');
define('BACKUP_DIR',  ROOT_PATH.'BackUp');

// all category image width must not 
// exceed 75 pixels
define('MAX_CATEGORY_IMAGE_WIDTH', 170);

// do we need to limit the product image width?
// setting this value to 'true' is recommended
define('LIMIT_PRODUCT_WIDTH',     true);

// maximum width for all product image
define('MAX_PRODUCT_IMAGE_WIDTH', 300);

// the width for product thumbnail
define('THUMBNAIL_WIDTH',70);

// since all page will require a database access
// and the common library is also used by all
// it's logical to load these library here
require_once ROOT_PATH.'includes/database.php';
?>
