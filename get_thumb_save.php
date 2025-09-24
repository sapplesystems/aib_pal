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

function output_gif($FileName, $show_text = false, $water_mark_text = '')
{
	if($show_text && trim($water_mark_text) != ''){
		createWaterMark($FileName, $water_mark_text);
	}else{
		$Buffer = new Imagick($FileName);
		header('Content-Type: image/' . strtolower($Buffer->getImageFormat()));
		echo $Buffer->getimageblob();
	}
	return;
}

function output_jpeg($FileName, $show_text = false, $water_mark_text = '')
{
	if($show_text && trim($water_mark_text) != ''){
		createWaterMark($FileName, $water_mark_text);
	}else{
		$Buffer = new Imagick($FileName);
		header('Content-Type: image/' . strtolower($Buffer->getImageFormat()));
		echo $Buffer->getimageblob();
	}
	return;
}

function output_image($FileName, $show_text = false, $water_mark_text = '')
{
	if($show_text && trim($water_mark_text) != ''){
		createWaterMark($FileName, $water_mark_text,$text = 'tiff');
	}else{
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
}	
//Sapple Code starts
function createWaterMark($sourceImage = null, $water_mark_text, $text = false){
	if($sourceImage && $water_mark_text){
		$Buffer = new Imagick($sourceImage);
		$imageData=($Buffer->getimageblob());
		$path=AIB_ADMIN_TMP;
		$fileName='tmpfile'.date('hmsi').rand(1,1000).'.'.strtolower($Buffer->getImageFormat());
		$myfile = fopen($path.$fileName, "w");
		fwrite($myfile, $imageData);
		fclose($myfile);
		$image = new Imagick($path.$fileName);
                
                $watermarkLogo = new Imagick();
                $watermarkLogo->readImage(AIB_WATER_MARK_IMG);
                
		$watermark = new Imagick();
		$text = $water_mark_text;
		$draw = new ImagickDraw();
		$watermark->newImage(400, 120, new ImagickPixel('none'));
		$draw->setFont(AIB_WATER_MARK_FONT);
		$draw->setFillColor('grey');
		$draw->setFillOpacity(.6);
		$draw->setGravity(Imagick::GRAVITY_NORTHWEST);
		//$watermark->annotateImage($draw, 10, 10, 0, $text);
		$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);
		$draw->setFontSize(25);
		$watermark->annotateImage($draw, 50, 80, 350, $text);
                //$watermark->annotateImage($draw, 5, 10, 0, $text);
		//$watermark->annotateImage($draw, 10, 10, 0, $text);
		for ($w = 0; $w < $image->getImageWidth(); $w += 400){
			for ($h = 0; $h < $image->getImageHeight(); $h += 120) {
					$image->compositeImage($watermarkLogo, Imagick::COMPOSITE_OVER, $w+150, $h-20);
					$image->compositeImage($watermark, Imagick::COMPOSITE_OVER, ($w-40), ($h-90));
			}
		}
		if($text){
			$image->setImageFormat('jpeg');
			header('Content-type: image/jpeg');
		}else{
			$image->setImageFormat(strtolower($Buffer->getImageFormat()));
			header('Content-type: image/'.strtolower($Buffer->getImageFormat()));
		}
		unlink($path.$fileName);
		echo $image;
	}
}
//Sapple Code end 
function get_item_image_data($DBHandle,$ItemID,$ImageType = AIB_FILE_CLASS_THUMB)
{
	$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID,$ImageType);
	$ThumbID = -1;
	foreach($FileList as $FileRecord)
	{
		if ($FileRecord["file_class"] == $ImageType)
		{
			$ThumbID = $FileRecord["record_id"];
			break;
		}
	}

	if ($ThumbID < 0)
	{
		return(false);
	}

	return($ThumbID);
}

// #########
// MAIN CODE
// #########

	$FormData = aib_get_form_data();
//	if (isset($_SERVER["HTTP_REFERER"]) == false)
//	{
//		$Referral = "";
//	}
//	else
//	{
//		$Referral = $_SERVER["HTTP_REFERER"];
//	}

	if(isset($FormData["download"]) && $FormData["download"] != 1){
		if (($Referral == "" || $Referral == "-") && isset($argv[1]) == false)
		{
			header("Location: http://www.archiveinabox.com");
			exit(0);
		}
	}
	
	if (isset($FormData["id"]) == false && $FormData["item_id"] == false)
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

	if (isset($FormData["id"]) == true)
	{
		$ObjID = $FormData["id"];
		$ObjType = "file";
	}
	else
	{
		$ObjID = $FormData["item_id"];
		$ObjType = "item";
	}


	aib_open_db();
	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FormData["id"]);
	//Sapple Code starts
	$show_text = (isset($FormData['show_text']) && $FormData['show_text'] == 'yes') ? true : false;
	$itemArchiveDetails = ftree_get_archive_and_archive_group($GLOBALS["aib_db"],$FileInfo['record']['file_item_id']);
	$archive_group_id   = isset($itemArchiveDetails['archive_group']['item_id']) ? $itemArchiveDetails['archive_group']['item_id'] : '';
	if($archive_group_id != ''){
		$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
		$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
	}else{
		$archiveDetails = ftree_get_item_title_path($GLOBALS["aib_db"],$FileInfo['record']['file_item_id']);
		$item_id    = $archiveDetails[0]['item_id'];
		$userWaterMark = ftree_get_long_property($GLOBALS["aib_db"],$item_id,'archive_watermark_text');
		$water_mark_text = (isset($userWaterMark) && trim($userWaterMark) != '')? $userWaterMark : '';
	}
	if ($ObjType == "file")
	{
		$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FormData["id"]);
	}
	else
	{

		$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ObjID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($EntryTypeProperty == AIB_ITEM_TYPE_RECORD)
		{
			$ThumbID = aib_get_first_record_thumb($GLOBALS["aib_db"],$ObjID);
		}
		else
		{
			$ThumbID = get_item_image_data($GLOBALS["aib_db"],$ObjID,AIB_FILE_CLASS_THUMB);
		}
		
		if ($ThumbID != false)
		{
			$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
		}
		else
		{
			$FileInfo = false;
		}
	}

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
			output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif",$show_text, $water_mark_text);
			exit(0);
		}

		$SourceName = urldecode($SourceName);
		if ($FileFormat == "gif")
		{
			output_gif($SourceName, $show_text, $water_mark_text);
			exit(0);
		}

		if ($FileFormat == "jpg")
		{
			output_jpeg($SourceName, $show_text, $water_mark_text);
			exit(0);
		}

		if ($FileFormat = "tiff")
		{
			output_image($SourceName, $show_text, $water_mark_text);
			exit(0);
		}

		output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif", $show_text, $water_mark_text);
		exit(0);
	}

	output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif", $show_text, $water_mark_text);
//	$Buffer = imagecreatefromgif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
//	header("Content-type: image/jpeg");
//	imagejpeg($Buffer);
	exit(0);

?>
