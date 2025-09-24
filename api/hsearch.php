<?php
//
// Search engine wrapper
//

include("api_util.php");
include("../include/searchapiv2.php");

// Log debug
// ---------
function aib_search_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_search_log_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,"$Msg\n");
		fclose($Handle);
	}

}

// Get value from associative array with default
// ---------------------------------------------
function get_assoc_default($ArrayIn,$Name,$Default)
{
	if (isset($ArrayIn[$Name]) == false)
	{
		return($Default);
	}

	return($ArrayIn[$Name]);
}

// Clean up text to remove any higher ASCII characters.
// ----------------------------------------------------
function browse_cleanup_text($InText)
{
	$OutText = preg_replace("/[\x80-\xff]+/"," ",$InText);
	return($OutText);
}

// Retrieve user with ID or login
// ------------------------------
function get_user_info($FormData,$UserID = false,$UserLogin = false)
{
	$UserID = get_assoc_default($FormData,"user_id",false);
	$UserLogin = get_assoc_default($FormData,"user_login",false);
	$UserRecord = false;
	$OutData = array("status" => "OK", "info" => "");
	if ($UserID !== false)
	{
		$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
	}
	else
	{
		if ($UserLogin !== false)
		{
			$UserRecord = ftree_get_user_by_login($GLOBALS["aib_db"],$UserLogin);
		}
		else
		{
			$OutData["status"] = "ERROR";
			$OutData["info"] = "MISSINGUSERIDORLOGIN";
			return($OutData);
		}
	}

	if ($UserRecord == false)
	{
		$OutData["status"] = "ERROR";
		$OutData["info"] = "USERIDORLOGINNOTFOUND";
	}
	else
	{
		$OutData["info"] = $UserRecord;
	}

	return($OutData);

}

// Given a user top folder, make sure the item ID is a child or equal to the top folder.
// -------------------------------------------------------------------------------------
function verify_user_item_access($DBHandle,$ItemID,$TopFolderID,$UserID = false)
{
	if ($UserID !== false)
	{
		if ($UserID == AIB_SUPERUSER)
		{
			return(true);
		}
	}

	// Get ID path to child item

	$IDPath = ftree_get_item_id_path($DBHandle,$ItemID);
	if ($IDPath == false)
	{
		return(false);
	}

	// If the child item is above the top folder, then no access

	$FoundTop = false;
	$FoundID = false;
	foreach($IDPath as $EntryID)
	{
		// Test for top first; if we've found it then we are at or before
		// the child item.  Critical that this test be performed BEFORE
		// testing for child

		if ($EntryID == $TopFolderID)
		{
			$FoundTop = $EntryID;
			break;
		}

		// If we find the child item, we're above the top.  If
		// the child item is the same as the top, we'll exit
		// at the comparison above.

		if ($EntryID == $ItemID)
		{
			$FoundID = $EntryID;
			break;
		}
	}

	if ($FoundTop !== false)
	{
		return(true);
	}

	return(false);
}

// Given month number, return STP Archive month name
// -------------------------------------------------
function stp_archive_month_name($Month)
{
	$MonthList = array(
		"January","February","March","April","May",
		"June","July","August","September","October",
		"November","December");
	if (isset($MonthList[$Month - 1]) == false)
	{
		return("NA");
	}

	return($MonthList[$Month - 1]);
}

// Multiple-round URL decode
// -------------------------
function browse_urldecode($InString)
{
	$OutString = urldecode($InString);
	for ($Counter = 0; $Counter < 3; $Counter++)
	{
		if (preg_match("/[A-Za-z][\+][A-Za-z]/",$OutString) == false)
		{
			if (preg_match("/[\%][0-9A-Fa-f][0-9A-Fa-f]/",$OutString) == false)
			{
				return($OutString);
			}
		}

		$OutString = urldecode($OutString);
	}

	for ($Counter = 0; $Counter < 3; $Counter++)
	{
		if (preg_match("/[A-Za-z][\+][A-Za-z]/",$OutString) == false)
		{
			if (preg_match("/[\%][0-9A-Fa-f][0-9A-Fa-f]/",$OutString) == false)
			{
				return($OutString);
			}
		}

		$OutString = rawurldecode($OutString);
	}

	return($OutString);
}


// Output image
// ------------
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

function stored_file_size($FileRecord, $PhysicalFlag = false)
{
	if (isset($FileRecord["file_size"]) == true)
	{
		$LocalSize = $FileRecord["file_size"];
		if ($LocalSize > 0)
		{
			return($LocalSize);
		}
	}

	if ($PhysicalFlag == false)
	{
		return(-1);
	}

	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FileRecord["record_id"]);
	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	$SourceName = urldecode($SourceName);
	if (file_exists($SourceName) == true)
	{
		return(filesize($SourceName));
	}

	return(-1);
}


// Get image
// ---------

function get_item_image_data($DBHandle,$ItemID,$ImageType = AIB_FILE_CLASS_THUMB)
{
	$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID,$ImageType);
	$ThumbID = -1;
	foreach($FileList as $FileRecord)
	{
		if ($FileRecord["file_class"] == AIB_FILE_CLASS_THUMB)
		{
			$ThumbID = $FileRecord["record_id"];
			break;
		}
	}

	if ($ThumbID < 0)
	{
		return(false);
	}

	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
	if ($FileInfo == false)
	{
		return(false);
	}

	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	$SourceName = urldecode($SourceName);
	$SourceMIME = $FileInfo["mime"];
	if (file_exists($SourceName) == false)
	{
		return(false);
	}

	$Buffer = file_get_contents($SourceName);
	if ($Buffer == false)
	{
		return(false);
	}

	$OutData = array(
		"mime" => $SourceMIME,
		"data" => base64_encode($Buffer),
		"id" => $ThumbID
		);
	unset($Buffer);
	return($OutData);
}

// Given a form ID and item ID, return the fields for the item in form order, with fields not on the form
// listed after form fields.
// ------------------------------------------------------------------------------------------------------
function get_item_fields_in_form_order($DBHandle,$ItemID,$FormID)
{
	if ($FormID == "" || $FormID === false)
	{
		$FieldList = ftree_field_get_item_fields_ext($DBHandle,$ItemID);
		return($FieldList);
	}

	$LocalMap = array();
	$FieldList = ftree_field_get_item_fields_ext($DBHandle,$ItemID);
	foreach($FieldList as $FieldID => $FieldInfo)
	{
		$LocalMap[$FieldID] = $FieldInfo;
	}

	$FormFields = ftree_field_get_form_fields($DBHandle,$FormID);
	if ($FormFields == false)
	{
		return($FieldList);
	}

	$TempList = array();
	foreach($FormFields as $FormRecord)
	{
		$FieldID = $FormRecord["field_record"]["field_id"];
		if (isset($LocalMap[$FieldID]) == true)
		{
			$TempList[] = $LocalMap[$FieldID];
			unset($LocalMap[$FieldID]);
		}
		else
		{
			$TempList[] = array("value" => "", "def" => $FormRecord["field_record"]);
		}
	}

	foreach($FieldList as $FieldID => $FieldInfo)
	{
		if (isset($LocalMap[$FieldID]) == true)
		{
			$TempList[] = $LocalMap[$FieldID];
		}
	}



	return($TempList);
}

function log_call_start($CallName)
{
	$GLOBALS["aib_call_start"] = array(microtime(true),$CallName);
	if (isset($GLOBALS["aib_call_summary"]) == false)
	{
		$GLOBALS["aib_call_summary"] = array($CallName => 0.0);
	}
	else
	{
		if (isset($GLOBALS["aib_call_summary"][$CallName]) == false)
		{
			$GLOBALS["aib_call_summary"][$CallName] = 0.0;
		}
	}
}

function log_call_end()
{
	if (isset($GLOBALS["aib_call_start"]) == false)
	{
		return;
	}

	$EndTime = microtime(true);
	$DeltaTime = $EndTime - $GLOBALS["aib_call_start"][0];
	$CallName = $GLOBALS["aib_call_start"][1];
	$GLOBALS["aib_call_summary"][$CallName] += $DeltaTime;
	aib_browse_log_debug($GLOBALS["aib_call_start"][1]." -- ".sprintf("%0.6lf",$DeltaTime));
	unset($GLOBALS["aib_call_start"]);
}

function log_call_summary()
{
	foreach($GLOBALS["aib_call_summary"] as $CallName => $TotalTime)
	{
		aib_browse_log_debug("Summary for $CallName: ".sprintf("%0.6lf",$TotalTime));
	}
}


// #########
// MAIN CODE
// #########


	// Collect form data

	$FormData = array();
	foreach($_GET as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	// Get opcode

	$OpCode = get_assoc_default($FormData,"_op",false);
	if ($OpCode == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOOP"));
		exit(0);
	}

	// Get server name.  Must be a valid source as listed in the hosts table.

	$ServerName = get_assoc_default($_SERVER,"REMOTE_HOST",get_assoc_default($_SERVER,"REMOTE_ADDR",false));
	if ($ServerName == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOHOST"));
		exit(0);
	}
	else
	{
		// If the server name is the IP address, attempt to do a reverse lookup using the address.
		// If this fails, simply use the IP address.

		if (preg_match("/^[0-9\.]+$/",$ServerName) != false)
		{
			$HostName = gethostbyaddr($ServerName);
			if ($HostName != false && strtolower($ServerName) != strtolower($HostName))
			{
				$ServerName = $HostName;
			}
		}
	}

	aib_open_db();
	if (aib_api_check_host($GLOBALS["aib_db"],$ServerName) == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "HOSTNOTALLOWED"));
		exit(0);
	}

	// Check server name and opcode; make sure the source is allowed to perform this operation

	if (aib_api_check_host($GLOBALS["aib_db"],$ServerName,$OpCode) == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "HOSTNOTALLOWED"));
		exit(0);
	}

	// Get API key and session, then validate

	$APIKey = get_assoc_default($FormData,"_key",false);
	$APISession = get_assoc_default($FormData,"_session",false);
	if ($APIKey == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGKEY"));
		exit(0);
	}

	if ($APISession == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGSESSION"));
		exit(0);
	}

	$Result = aib_api_validate_session_key($GLOBALS["aib_db"],$APIKey,$APISession,AIB_MAX_API_SESSION);
	if ($Result[0] != "OK")
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => $Result[1]));
		exit(0);
	}

	// Get keyholder

	$KeyHolderID = aib_api_get_key_id($GLOBALS["aib_db"],$APIKey);
	if ($KeyHolderID == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "KEYHOLDERIDNOTFOUND"));
		exit(0);
	}

	// Get user ID of requesting user; required for user account operations

	$RequestUserID = get_assoc_default($FormData,"_user",false);
	if ($RequestUserID === false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGUSER"));
		exit(0);
	}

	// Get the user type and information

	$RequestUserRecord = ftree_get_user($GLOBALS["aib_db"],$RequestUserID);
	if ($RequestUserRecord == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "BADREQUESTUSER"));
		exit(0);
	}

	$RequestUserType = $RequestUserRecord["user_type"];
	$RequestUserRoot = $RequestUserRecord["user_top_folder"];


	// Generate a new session

	$OutData = array("status" => "OK");
	switch($OpCode)
	{
		case "search":
			$OutData["info"] = hsearch_perform_search($GLOBALS["aib_db"],$FormData);
			$OutData["status"] = "OK";
aib_search_log_debug(date("Y-m-d H:i:s")." ".__LINE__." ".var_export($OutData,true));
			break;

		case "get_llm_stream":
			if (isset($FormData["llm_request_id"]) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGLLMREQUESTID";
				break;
			}

			$TempData = hsearch_get_llm_output($GLOBALS["aib_db"],$FormData["llm_request_id"]);
			if ($TempData == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOSUCHREQUEST";
				break;
			}

			$OutData["status"] = strtoupper($TempData["status"]);
			$OutData["info"] = $TempData["output_text"];
			$OutData["query_text"] = $TempData["query_text"];
aib_search_log_debug(date("Y-m-d H:i:s")." ".__LINE__." get_llm_stream -- ".var_export($OutData,true));
			break;

		case "set_search":
			$FieldID = get_assoc_default($FormData,"field_id",false);
			$DataType = get_assoc_default($FormData,"data_type",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			if ($DataType == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGDATATYPE";
				break;
			}

			$OutData["info"] = hsearch_set_field_searchable($GLOBALS["aib_db"],$FieldID,$DataType);
			$OutData["status"] = "OK";
			break;

		case "clear_search":
			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			$OutData["info"] = hsearch_set_field_not_searchable($GLOBALS["aib_db"],$FieldID);
			$OutData["status"] = "OK";
			break;

		default:
			$OutData["status"] = "ERROR";
			$OutData["info"] = "BADOP";
			break;
	}

	aib_close_db();
	aib_api_send_response($OutData);
	exit(0);
?>
