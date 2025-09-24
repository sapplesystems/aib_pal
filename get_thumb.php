<?php
include('config/aib.php');
include("include/folder_tree.php");
include('include/aib_util.php');

function log_debug($Msg)
{
	$Handle = fopen("/tmp/get_thumb.txt","a+");
	if ($Handle != false)
	{
		$DateString = date("Y-m-d H:i:s")."/".sprintf("%0.6lf",microtime(true));
		fputs($Handle,$DateString."|".$Msg."\n");
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
// MDM 01-17-2019
// Removed; redundant operation.  Note that "$Buffer" is no longer used, which affects code after the drawing functions below.
//		$Buffer = new Imagick($sourceImage);
//		$imageData=($Buffer->getimageblob());
//		$path=AIB_ADMIN_TMP;
//		$fileName='tmpfile'.date('hmsi').rand(1,1000).'.'.strtolower($Buffer->getImageFormat());
//		$myfile = fopen($path.$fileName, "w");
//		fwrite($myfile, $imageData);
//		fclose($myfile);
//		$image = new Imagick($path.$fileName);

		// Open image to be watermarked and create image object

		$image = new Imagick($sourceImage);
 
		// Create watermark logo image object
               
                $watermarkLogo = new Imagick();
                $watermarkLogo->readImage(AIB_WATER_MARK_IMG);
                
		// Create watermark image object

		$watermark = new Imagick();
		$watermark->newImage(400, 120, new ImagickPixel('none'));

// MDM 01-17-2019
// Removed; assignment to local variable is unncessary (PHP makes a copy of the value passed to the function)
//		$text = $water_mark_text;

		// Create image object for text, set font and fill color/opacity

		$draw = new ImagickDraw();
		$draw->setFont(AIB_WATER_MARK_FONT);
		$draw->setFillColor('grey');
		$draw->setFillOpacity(.6);

// MDM 01-17-2019
// Removed; do we need setGravity called twice?
//		$draw->setGravity(Imagick::GRAVITY_NORTHWEST);
		//$watermark->annotateImage($draw, 10, 10, 0, $text);

		// Set origin quadrant for text

		$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);
		$draw->setFontSize(25);

		// Draw text

		$watermark->annotateImage($draw, 50, 80, 350, $water_mark_text);

                //$watermark->annotateImage($draw, 5, 10, 0, $text);
		//$watermark->annotateImage($draw, 10, 10, 0, $text);
		for ($w = 0; $w < $image->getImageWidth(); $w += 400){
			for ($h = 0; $h < $image->getImageHeight(); $h += 120) {
					$image->compositeImage($watermarkLogo, Imagick::COMPOSITE_OVER, $w+150, $h-20);
					$image->compositeImage($watermark, Imagick::COMPOSITE_OVER, ($w-40), ($h-90));
			}
		}

// MDM 01-17-2019
// Changes below to remove references to "$Buffer" and replace with "$image".  "$Buffer" is redundant, and may not
// reflect the image format change if not JPEG.
//
// Unique file name is temp_ plus microtime to the microsecond, plus the appropriate image file name suffix.  The file
// is not used locally but is passed to the browser to force a reload of the image on a cached page.

		// Create unique file name base; used to force the browser to download the image instead of
		// defaulting to cache (use the Content-disposition header with 'inline').

		$UniqueFileName = "temp_".preg_replace("/[\.]/","_",microtime(true));
		if($water_mark_text){
			$image->setImageFormat('jpeg');
			$UniqueFileName .= ".jpg";
			header('Content-type: image/jpeg');
			header("Content-disposition: inline; filename=\"$UniqueFileName\"");
		}else{
			$image->setImageFormat(strtolower($image->getImageFormat()));
			header('Content-type: image/'.strtolower($image->getImageFormat()));
			$UniqueFileName .= ".".$image->getImageFormat();
			header("Content-disposition: inline; filename=\"$UniqueFileName\"");
		}
// MDM 01-17-2019
// We no longer create a temp file, so this can be removed
//		unlink($path.$fileName);

		// Send image to browser

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

//	log_debug("START");
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
//	log_debug("OPENED DATABASE");
	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FormData["id"]);
//	log_debug("GOT FILE INFO");
	//Sapple Code starts
	$show_text = (isset($FormData['show_text']) && $FormData['show_text'] == 'yes') ? true : false;
	$itemArchiveDetails = ftree_get_archive_and_archive_group($GLOBALS["aib_db"],$FileInfo['record']['file_item_id']);
//	log_debug("GOT ARCHIVE AND ARCHIVE GROUP");
	$archive_group_id   = isset($itemArchiveDetails['archive_group']['item_id']) ? $itemArchiveDetails['archive_group']['item_id'] : '';
        //$archive_group_id   = isset($FileInfo['record']['file_item_id']) ? $FileInfo['record']['file_item_id'] : '';
        if(isset($_REQUEST['record_item_id'])){
            $archive_group_id = $_REQUEST['record_item_id'];
        }/*********** SS Fix Start Issue Id 2313 16-Aug-2023 **************/
 		elseif(isset($_REQUEST['folder_id'])){
            $archive_group_id = $_REQUEST['folder_id'];
        }/*********** SS End Start Issue Id 2313 16-Aug-2023 **************/
	if($archive_group_id != ''){
		/************* SS FIX START 0002309   21-Sep-2023 ************************/
		if(isset($_REQUEST['downloadImage']) and $_REQUEST['downloadImage']==1){
			
			$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_download_watermark_text');
			$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
			
			if($water_mark_text==''){
				$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
				$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
			}
		}
		else{
			$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
			$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
		}
		/************* SS END START 0002309   21-Sep-2023 ************************/
		
		/***fetch the water mark of parent folder if not found on record**/
		if(trim($water_mark_text)=='')
		{
			$archiveDetails = ftree_get_item_title_path($GLOBALS["aib_db"],$archive_group_id);
			
			$itemParent=$archiveDetails[count($archiveDetails)-1]['item_parent'];
			/************* SS FIX START 0002309   21-Sep-2023 ************************/
			if(isset($_REQUEST['downloadImage']) and $_REQUEST['downloadImage']==1){

				$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_download_watermark_text');
				$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';

				if($water_mark_text==''){
					$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
					$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
				}
			}
			else{
				$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
				$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
			}
			/************* SS END START 0002309   21-Sep-2023 ************************/
			
			/***fetch the water mark of root folder if not found on record and subfolder**/
			if(trim($water_mark_text)=='')
			{
				$archiveDetails = ftree_get_item_title_path($GLOBALS["aib_db"],$archive_group_id);
				$itemParent=$archiveDetails[0]['item_id'];
				/************* SS FIX START 0002309   21-Sep-2023 ************************/
				if(isset($_REQUEST['downloadImage']) and $_REQUEST['downloadImage']==1){

					$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_download_watermark_text');
					$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';

					if($water_mark_text==''){
						$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
						$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
					}
				}
				else{
					$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
					$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
				}
				/************* SS END START 0002309   21-Sep-2023 ************************/
				}
			}
		
		
//		log_debug("GOT PROPERTIES FOR ARCHIVE GROUP");
	}else{
		$archiveDetails = ftree_get_item_title_path($GLOBALS["aib_db"],$FileInfo['record']['file_item_id']);
//		log_debug("GOT ITEM TITLE PATH");
		$item_id    = $archiveDetails[0]['item_id'];
		/************* SS FIX START 0002309   21-Sep-2023 ************************/
		if(isset($_REQUEST['downloadImage']) and $_REQUEST['downloadImage']==1){
			
			$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_download_watermark_text');
			$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
			
			if($water_mark_text==''){
				$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
				$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
			}
		}
		else{
			$archivePropertiesList = ftree_get_long_property($GLOBALS["aib_db"],$archive_group_id,'archive_watermark_text');
			$water_mark_text = ($archivePropertiesList != '') ? $archivePropertiesList :'';
		}
		/************* SS END START 0002309   21-Sep-2023 ************************/
		
	}
	if ($ObjType == "file")
	{
		$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FormData["id"]);
//		log_debug("GOT FILE INFO");
	}
	else
	{

		$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ObjID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($EntryTypeProperty == AIB_ITEM_TYPE_RECORD)
		{
			$ThumbID = aib_get_first_record_thumb($GLOBALS["aib_db"],$ObjID);
//			log_debug("GOT FIRST RECORD THUMB");
		}
		else
		{
			$ThumbID = get_item_image_data($GLOBALS["aib_db"],$ObjID,AIB_FILE_CLASS_THUMB);
//			log_debug("GOT THUMB DATA");
		}
		
		if ($ThumbID != false)
		{
			$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
//			log_debug("GOT THUMB FILE INFO");
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
                if(!empty($water_mark_text)){
                    $water_mark_text = str_replace("+"," ",$water_mark_text);
                }
		if ($SourceName == false)
		{
//			log_debug("SENDING BLANK GIF");
			output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif",$show_text, $water_mark_text);
			exit(0);
		}

		$SourceName = urldecode($SourceName);
		if ($FileFormat == "gif")
		{
//			log_debug("SENDING GIF");
			output_gif($SourceName, $show_text, $water_mark_text);
			exit(0);
		}

		if ($FileFormat == "jpg")
		{
//			log_debug("SENDING JPEG");
			output_jpeg($SourceName, $show_text, $water_mark_text);
			exit(0);
		}

		if ($FileFormat = "tiff")
		{
//			log_debug("SENDING TIFF");
			output_image($SourceName, $show_text, $water_mark_text);
			exit(0);
		}

//		log_debug("SENDING BLANK AT END");
		output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif", $show_text, $water_mark_text);
		exit(0);
	}

//	log_debug("SENDING BLANK AT END (B)");
	output_gif(AIB_BASE_SITE_PATH."/images/blankthumb.gif", $show_text, $water_mark_text);
//	$Buffer = imagecreatefromgif(AIB_BASE_SITE_PATH."/images/blankthumb.gif");
//	header("Content-type: image/jpeg");
//	imagejpeg($Buffer);
	exit(0);

?>
