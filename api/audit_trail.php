<?php
//
// Audit trail functions
//

include("api_util.php");
include("../include/audit_trail.php");

// Log debug
// ---------
function aib_audit_trail_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_audit_log_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
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

	aib_open_db();
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
	$EntryTypes = array(
		AIB_ITEM_TYPE_ARCHIVE_GROUP => "AG",
		AIB_ITEM_TYPE_ARCHIVE => "AR",
		AIB_ITEM_TYPE_COLLECTION => "CO",
		AIB_ITEM_TYPE_SUBGROUP => "SG",
		AIB_ITEM_TYPE_RECORD => "RE",
		AIB_ITEM_TYPE_ITEM => "IT",
		AIB_ITEM_TYPE_SYSTEM => "SY",
		);

	$BlockedProperties = array(
		);

	$FieldDataTypeDesc = array(
		FTREE_FIELD_TYPE_TEXT => "TEXT",
		FTREE_FIELD_TYPE_BIGTEXT => "BIGTEXT",
		FTREE_FIELD_TYPE_INTEGER => "INT",
		FTREE_FIELD_TYPE_FLOAT => "FLOAT",
		FTREE_FIELD_TYPE_DECIMAL => "DECIMAL",
		FTREE_FIELD_TYPE_DATE => "DATE",
		FTREE_FIELD_TYPE_TIME => "TIME",
		FTREE_FIELD_TYPE_DATETIME => "DATETIME",
		FTREE_FIELD_TYPE_TIMESTAMP => "POSIXTIME",
		FTREE_FIELD_TYPE_DROPDOWN => "SELECT",
		);
	
	$FileClassTypeDesc = array(
		AIB_FILE_CLASS_PRIMARY => "PRI",
		AIB_FILE_CLASS_THUMB => "THUMB",
		AIB_FILE_CLASS_TEXT => "TEXT",
		AIB_FILE_CLASS_TEXT_LOCATION => "TEXTLOC",
		AIB_FILE_CLASS_ORIGINAL => "ORG"
		);

	switch($OpCode)
	{

		// Log an event

		case "log_event":
			$RequiredList = array("event_type");
			$OkFlag = true;
			foreach($RequiredList as $Name)
			{
				if (isset($FormData[$Name]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "MISSING REQUIRED FIELD $Name";
					$OkFlag = false;
					break;
				}
			}

			if ($OkFlag == false)
			{
				break;
			}

			$EventType = $FormData["event_type"];
			$OutData = aib_audit_log_event($GLOBALS["aib_db"],$EventType,$FormData);
			break;

		// Retrieve a specific event
		
		case "get_event":
			$RequiredList = array("event_id");
			$OkFlag = true;
			foreach($RequiredList as $Name)
			{
				if (isset($FormData[$Name]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "MISSING REQUIRED FIELD $Name";
					$OkFlag = false;
					break;
				}
			}

			if ($OkFlag == false)
			{
				break;
			}

			$EventID = $FormData["event_id"];
			$OutData = aib_audit_get_event($GLOBALS["aib_db"],$EventID);
			break;

		// Flush events

		case "flush":
			$OlderThanDate = false;
			$OlderThanDays = false;
			$TypeSelect = false;
			if (isset($FormData["older_than_date"]) == true)
			{
				$OlderThanDate = $FormData["older_than_date"];
			}

			if (isset($FormData["older_than_days"]) == true)
			{
				$OlderThanDays = $FormData["older_than_days"];
			}

			if (isset($FormData["types"]) == true)
			{
				$TypeList = explode(",",$FormData["types"]);
			}

			$OutData = aib_audit_flush_events($GLOBALS["aib_db"],$OlderThanDate,$OlderThanDays,$TypeList);
			break;

		// Report events

		case "report":
			$OptionList = array("typelist","date_low","date_high","user_id","item_id","first_result","last_result","most_recent");
			$OptionData = array();
			foreach($OptionList as $Name)
			{
				if (isset($FormData[$Name]) == true)
				{
					if ($Name == "typelist")
					{
						$OptionData["typelist"] = explode(",",$FormData[$Name]);
					}
					else
					{
						$OptionData[$Name] = $FormData[$Name];
					}
				}
			}

			$OutData = aib_audit_query($GLOBALS["aib_db"],$OptionData);
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
