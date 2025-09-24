<?php
include('config/aib.php');
include("include/folder_tree.php");
include('include/aib_util.php');

	if (isset($_SERVER["HTTP_REFERER"]) == false)
	{
		$Referral = "";
	}
	else
	{
		$Referral = $_SERVER["HTTP_REFERER"];
	}

	if (($Referral == "" || $Referral == "-") && isset($argv[1]) == false)
	{
		header("Location: http://www.archiveinabox.com");
		exit(0);
	}

	$FormData = aib_get_form_data();
	if (isset($FormData["id"]) == false)
	{
		if (isset($argv[1]) == false)
		{
			$Buffer = imagecreatefromgif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
			imagejpeg($Buffer);
			exit(0);
		}
		else
		{
			$FormData = array("id" => $argv[1]);
		}
	}

	aib_open_db();
	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FormData["id"]);
	aib_close_db();
	if ($FileInfo != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
		$SourceMIME = urldecode($FileInfo["mime"]);
		$Buffer = false;
		while(true)
		{
			if (strstr($SourceMIME,"image/jpeg") != false)
			{
				$Buffer = imagecreatefromjpeg($SourceName);
				header("Content-type: image/jpeg");
				imagejpeg($Buffer);
				break;
			}

			if (strstr($SourceMIME,"image/png") != false)
			{
				$Buffer = imagecreatefrompng($SourceName);
				header("Content-type: image/jpeg");
				imagejpeg($Buffer);
				break;
			}

			if (strstr($SourceMIME,"image/gif") != false)
			{
				$Buffer = imagecreatefromgif($SourceName);
				header("Content-type: image/jpeg");
				imagejpeg($Buffer);
				break;
			}

			if (strstr($SourceMIME,"image/tif") != false)
			{
				$Image = new Imagick($SourceName);
				if ($Image == false)
				{
					break;
				}

				$Image->setImageFormat("jpeg");
				header("Content-type: image/jpeg");
				$LocalHandle = fopen('php://stdout', 'w+');
				$Image->writeImageFile($LocalHandle);
				fclose($LocalHandle);
				$Buffer = "";
				break;
			}

			break;
		}
	}

	if ($Buffer == false)
	{
		$Buffer = imagecreatefromgif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
		header("Content-type: image/jpeg");
		imagejpeg($Buffer);
	}

	exit(0);

?>
