<?php
//
// Comment browsing functions
//

include("api_util.php");
include("../include/uimessages.php");

// Log debug
// ---------
function aib_uielement_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_uielement_log_debug.txt","a+");
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

		// Store text for UI element

		case "store_ui_element":
			$OkFlag = true;
			if (isset($FormData["element_id"]) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSING element_id";
				$OkFlag = false;
				break;
			}

			if ($OkFlag == false)
			{
				break;
			}

			$ReqList = array("element_id" => "element_id",
				"language" => "language_code",
				"text" => "element_text",
				"name" => "element_name",
				"location" => "element_location",
			);

			$Spec = array();
			foreach($ReqList as $InName => $OutName)
			{
				if (isset($FormData[$InName]) == true)
				{
					$Spec[$OutName] = $FormData[$InName];
				}
			}

			$Result = uimsg_store_message($GLOBALS["aib_db"],$Spec);
			if ($Result["status"] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result["msg"];
			}

			break;


		// Get text for UI element by ID
	
		case "get_ui_element":
			$OkFlag = true;
			$ReqList = array("element_id");
			foreach($ReqList as $FieldName)
			{
				if (isset($FormData[$FieldName]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "MISSING $FieldName";
					$OkFlag = false;
					break;
				}
			}

			if ($OkFlag == false)
			{
				break;
			}

			$Result = uimsg_get_by_element_id($GLOBALS["aib_db"],$FormData["element_id"]);
			if ($Result["status"] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result["msg"];
				break;
			}

			$OutData["info"] = array("records" => array());
			$OutData["info"]["records"][] = $Result["data"];
			break;


		// Get text for UI element by name and location
		
		case "get_ui_element_nameloc":
			$OkFlag = true;
			$ReqList = array("name","location");
			foreach($ReqList as $FieldName)
			{
				if (isset($FormData[$FieldName]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "MISSING $FieldName";
					$OkFlag = false;
					break;
				}
			}

			if ($OkFlag == false)
			{
				break;
			}

			$Result = uimsg_get_by_name_and_location($GLOBALS["aib_db"],$FormData["name"],$FormData["location"]);
			if ($Result["status"] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result["msg"];
				break;
			}

			$OutData["info"] = array("records" => array());
			$OutData["info"]["records"][] = $Result["data"];
			break;



		// List elements in location

		case "list_by_location":
			$OkFlag = true;
			$ReqList = array("location");
			foreach($ReqList as $FieldName)
			{
				if (isset($FormData[$FieldName]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "MISSING $FieldName";
					$OkFlag = false;
					break;
				}
			}

			if ($OkFlag == false)
			{
				break;
			}

			$Result = uimsg_get_location_elements($GLOBALS["aib_db"],$FormData["location"]);
			if ($Result["status"] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result["msg"];
				break;
			}

			$OutData["info"] = array("records" => array());
			foreach($Result["data"] as $Record)
			{
				$OutData["info"]["records"][] = $Record;
			}

			break;


		// Delete text for UI element

		case "del_ui_element":
			$OkFlag = true;
			$ReqList = array("element_id");
			foreach($ReqList as $FieldName)
			{
				if (isset($FormData[$FieldName]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "MISSING $FieldName";
					$OkFlag = false;
					break;
				}
			}

			if ($OkFlag == false)
			{
				break;
			}

			$Result = uimsg_delete_element($GLOBALS["aib_db"],$FormData["element_id"]);
			if ($Result["status"] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result["msg"];
				break;
			}

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
