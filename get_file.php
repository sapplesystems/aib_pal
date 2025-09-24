<?php
//
// Send arbitrary file to browser.  Requires either the file ID, or a tree node object ID and file class.
//
//
// Common function for buffer output is intentionally avoided to prevent double allocation.  Program needs
// to be updated at some point to stream data rather than loading everything in a buffer.
//

include('config/aib.php');
include("include/folder_tree.php");
include('include/aib_util.php');

function log_debug($Msg)
{
	$Handle = fopen("/tmp/get_image.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

	// Check for referrer.  If none, error.  This is an attempt to prevent direct
	// retrieval of a file from outside of the website.

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
		header("Content-Type: text/ascii");
		$Buffer = "NOT ALLOWED";
		header("Content-Length: ".strlen($Buffer));
		print($Buffer);
		exit(0);
	}

	// Get file and/or item ID

	$FormData = aib_get_form_data();
	if (isset($FormData["obj_id"]) == false && isset($FormData["file_id"]) == false)
	{
		header("Content-Type: text/ascii");
		$Buffer = "ERROR: MISSING ID";
		header("Content-Length: ".strlen($Buffer));
		print($Buffer);
		exit(0);
	}

	// If we've used the object ID as a request, then we need the file class as well.  The first match
	// for the class will be retrieved.

	$FileID = false;
	if (isset($FormData["obj_id"]) == true)
	{
		if (isset($FormData["file_class"]) == false)
		{
			header("Content-Type: text/ascii");
			$Buffer = "ERROR: MISSING FILE CLASS";
			header("Content-Length: ".strlen($Buffer));
			print($Buffer);
			exit(0);
		}

		$FileClass = $FormData["file_class"];
		$ObjID = $FormData["obj_id"];
		aib_open_db();
		$FileList = aib_get_files_for_item($DBHandle,$ObjID);
		aib_close_db();
		foreach($FileList as $FileRecord)
		{
			if ($FileRecord["file_class"] == $FileClass)
			{
				$FileID = $FileRecord["record_id"];
				break;
			}
		}
	}
	else
	{
		$FileID = $FormData["file_id"];
	}

	if ($FileID === false)
	{
		header("Content-Type: text/ascii");
		$Buffer = "ERROR: CANNOT LOCATE FILE";
		header("Content-Length: ".strlen($Buffer));
		print($Buffer);
		exit(0);
	}


	// Get file info

	aib_open_db();
	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FileID);
	aib_close_db();
	if ($FileInfo != false)
	{
		if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
		{
			$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
		}
		else
		{
			$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
		}

		$SourceName = urldecode($SourceName);
		$SourceMIME = urldecode($FileInfo["mime"]);
		$Buffer = file_get_contents($SourceName);
		if ($Buffer == false)
		{
			header("Content-Type: text/ascii");
			$Buffer = "ERROR: CANNOT READ FILE";
		}
		else
		{
			header("Content-Type: ".$SourceMime);
		}

		header("Content-Length: ".strlen($Buffer));
		print($Buffer);
	}
	else
	{
			header("Content-Type: text/ascii");
			$Buffer = "ERROR: CANNOT READ FILE";
			header("Content-Length: ".strlen($Buffer));
			print($Buffer);
	}

	exit(0);

?>
