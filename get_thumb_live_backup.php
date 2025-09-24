<?php
include('config/aib.php');
include("include/folder_tree.php");
include('include/aib_util.php');

function log_debug($Msg)
{
	$Handle = fopen("/tmp/get_thumb.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function output_gif($FileName)
{
	$Buffer = new Imagick($FileName);
	header('Content-Type: image/' . strtolower($Buffer->getImageFormat()));
	echo $Buffer->getimageblob();
	return;
}

function output_jpeg($FileName)
{
	$Buffer = new Imagick($FileName);
	header('Content-Type: image/' . strtolower($Buffer->getImageFormat()));
	echo $Buffer->getimageblob();
	return;
}

function output_image($FileName)
{
	ob_start();
	$ImageBuffer = new Imagick($FileName);
	$Handle = fopen("php://output","w+");
	$ImageBuffer->setImageFormat("jpeg");
	$ImageBuffer->writeImageFile($Handle);
	fclose($Handle);
	$OutBuffer = ob_get_clean();
	header("Content-Length: ".strlen($OutBuffer));
	echo $OutBuffer;
	unset($ImageBuffer);
}



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
			output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
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
	$FileFormat = false;
	if ($FileInfo != false)
	{
		$MIMEType = $FileInfo["mime"];
		while(true)
		{
			if (preg_match("/[Jj][Pp][Ee][Gg]/",$MIMEType) != false || preg_match("/[Jj][Pp][Gg]/",$MIMEType) != false)
			{
				$FileFormat = "jpg";
				break;
			}

			if (preg_match("/[Gg][Ii][Ff]/",$MIMEType) != false)
			{
				$FileFormat = "gif";
				break;
			}

			if (preg_match("/[Tt][Ii][Ff]/",$MIMEType) != false)
			{
				$FileFormat = "tif";
				break;
			}

			break;
		}

		$SourceName = false;
		while(true)
		{
			if (preg_match("/[\.][A-Za-z]+$/",$FileInfo["name"]) == false)
			{
				$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
				break;
			}

			if (preg_match("/[\.]dat/",$FileInfo["name"]) != false)
			{
				$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
				break;
			}

			if (preg_match("/gif$/",$MIMEType) != false)
			{
				$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".gif";
				break;
			}

			if (preg_match("/GIF$/",$MIMEType) != false)
			{
				$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".gif";
				break;
			}

			if (preg_match("/jpeg$/",$MIMEType) != false || preg_match("/jpg$/",$MIMEType) != false)
			{
				$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".jpg";
				break;
			}

			if (preg_match("/JPEG$/",$MIMEType) != false || preg_match("/JPG$/",$MIMEType) != false)
			{
				$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".jpg";
				break;
			}

			break;
		}

		if ($SourceName == false)
		{
			output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
			exit(0);
		}

		$SourceName = urldecode($SourceName);
		if ($FileFormat == "gif")
		{
			output_gif($SourceName);
			exit(0);
		}

		if ($FileFormat == "jpg")
		{
			output_jpeg($SourceName);
			exit(0);
		}

		if ($FileFormat = "tiff")
		{
			output_image($SourceName);
			exit(0);
		}

		output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
		exit(0);
	}

	output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
//	$Buffer = imagecreatefromgif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
//	header("Content-type: image/jpeg");
//	imagejpeg($Buffer);
	exit(0);

?>
