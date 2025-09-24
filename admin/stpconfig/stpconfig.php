<?php
ini_set('display_errors', 'On');
if (session_status() != PHP_SESSION_ACTIVE)
{
	session_start();
}

if (defined('IMAGE_ARCHIVE_PATH') == false)
{
	define('IMAGE_ARCHIVE_PATH','/home/stparch/Archive');
}

if (defined("ROOT_PATH_WITHOUT_SLASH") == false)
{
	define("ROOT_PATH_WITHOUT_SLASH",'/home/stparch/public_html');
}

if (defined("ROOT_PATH") == false)
{
	define("ROOT_PATH", ROOT_PATH_WITHOUT_SLASH . '/');
}

if (defined("HOST_ROOT_PATH") == false)
{
	if (isset($_SERVER['HTTP_HOST']) == true)
	{
		define("HOST_ROOT_PATH","http://".$_SERVER['HTTP_HOST'].'/');
	}
	else
	{
		define("HOST_ROOT_PATH","http://www.stparchive.com/");
	}
}

if (defined("HOST_ROOT_PATH_WITHOUT_SLASH") == false)
{
	if (isset($_SERVER['HTTP_HOST']) == true)
	{
		define("HOST_ROOT_PATH_WITHOUT_SLASH",$_SERVER['HTTP_HOST']);
	}
	else
	{
		define("HOST_ROOT_PATH_WITHOUT_SLASH","http://www.stparchive.com");
	}
}

if (defined("PRIMARY_DOMAIN_NAME") == false)
{
	if (isset($_SERVER["HTTP_HOST"]) == true)
	{
		define("PRIMARY_DOMAIN_NAME",$_SERVER['HTTP_HOST']);
	}
	else
	{
		define("PRIMARY_DOMAIN_NAME","stparchive.com");
	}
}

if (defined("DBUSER") == false)
{
	define("DBUSER",'stparch');                        //Database User
}

if (defined("DBPASS") == false)
{
	define("DBPASS",'archive');                                //Database Password
}

if (defined("DBHOST") == false)
{
	define("DBHOST",'localhost');                //Database Host122.160.68.237
}

if (defined("DATABASE") == false)
{
	define("DATABASE",'stparchive');
}

if (defined("SESSION_PREFIX") == false)
{
	define("SESSION_PREFIX","STPJOB_");
}

if (defined("MAIN_DIR") == false)
{
	define('MAIN_DIR', ROOT_PATH.'Main');
}

if (defined("PROCESS_DIR") == false)
{
	define('PROCESS_DIR', ROOT_PATH.'Archive');
}

if (defined("BACKUP_DIR") == false)
{
	define('BACKUP_DIR',  ROOT_PATH.'BackUp');
}

// all category image width must not 
// exceed 75 pixels
if (defined("MAX_CATEGORY_IMAGE_WIDTH") == false)
{
	define('MAX_CATEGORY_IMAGE_WIDTH', 170);
}

// do we need to limit the product image width?
// setting this value to 'true' is recommended
if (defined("LIMIT_PRODUCT_WIDTH") == false)
{
	define('LIMIT_PRODUCT_WIDTH',     true);
}

// maximum width for all product image
if (defined("MAX_PRODUCT_IMAGE_WIDTH") == false)
{
	define('MAX_PRODUCT_IMAGE_WIDTH', 300);
}

// the width for product thumbnail
if (defined("THUMBNAIL_WIDTH") == false)
{
	define('THUMBNAIL_WIDTH',70);
}

// since all page will require a database access
// and the common library is also used by all
// it's logical to load these library here
//require_once ROOT_PATH.'includes/database.php';
?>
