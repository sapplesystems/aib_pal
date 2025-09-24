<?php
//
// Comment browsing functions
//

include("api_util.php");
include("../include/advert.php");

// Log debug
// ---------
function aib_advert_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_advert_log_debug.txt","a+");
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

		// Create advertisement
		
		case "advert_store":
			$RequiredList = array("item_id","title","url","sort_order","alt_title","inherit","record_ref");
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

			$DisableFlag = "N";
			if (isset($FormData["disable"]) == true)
			{
				if (preg_match("/[Yy]/",$FormData["disable"]) != false)
				{
					$DisableFlag = "Y";
				}
			}

			$InheritFlag = "N";
			if (isset($FormData["inherit"]) == true)
			{
				if (preg_match("/[Yy]/",$FormData["inherit"]) != false)
				{
					$InheritFlag = "Y";
				}
			}

			$Spec = array(
					"item_id" => $FormData["item_id"],
					"ad_title" => $FormData["title"],
					"ad_url" => $FormData["url"],
					"ad_sort_order" => $FormData["sort_order"],
					"ad_alt_title" => $FormData["alt_title"],
					"inherit_flag" => $InheritFlag,
					"disable_flag" => $DisableFlag,
					"record_ref" => $FormData["record_ref"],
				);

			if (isset($FormData["filename"]) == true)
			{
				$Spec["filename"] = $FormData["filename"];
			}

			$Result = advert_store($GLOBALS["aib_db"],$Spec);
			$OutData = $Result;
			break;


		// Associate a file with advertisement

		case "advert_set_file":
			$RequiredList = array("record_id","filename");
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

			$Result = advert_update_file($GLOBALS["aib_db"],$FormData["record_id"],$FormData["filename"]);
			$OutData = $Result;
			break;


		// Update advertisement definition

		case "advert_update":
			$RequiredList = array("record_id");
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

			$RecordID = $FormData["record_id"];
			$InOutList = array(
				"title" => "ad_title", "url" => "ad_url", "sort_order" => "ad_sort_order",
				"alt_title" => "ad_alt_title", "inherit" => "inherit_flag",
				"disable" => "disable_flag"
			);

			$Spec = array("record_id" => $RecordID);
			foreach($InOutList as $InName => $OutName)
			{
				if (isset($FormData[$InName]) == true)
				{
					$Spec[$OutName] = $FormData[$InName];
				}
			}

			$Result = advert_update($GLOBALS["aib_db"],$Spec);
			$OutData = $Result;
			break;

		// Retrieve an advertisement

		case "advert_get":
			$RequiredList = array("record_id");
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

			$RecordID = $FormData["record_id"];
			$Result = advert_get($GLOBALS["aib_db"],$RecordID);
			if ($Result["status"] == "OK")
			{
				$OutData["info"] = array("records" => array());
				$OutData["info"]["records"][] = $Result["record"];
			}
			else
			{
				$OutData = $Result;
			}

			break;


		// Delete advertisement

		case "advert_delete":
			$RequiredList = array("record_id");
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

			$RecordID = $FormData["record_id"];
			$Result = advert_delete($GLOBALS["aib_db"],$RecordID);
			if ($Result["status"] == "OK")
			{
				$OutData["info"] = array("records" => array());
				$OutData["info"]["records"][] = $Result["record"];
			}
			else
			{
				$OutData = $Result;
			}

			break;

		// Get list of advertisements for a given item

		case "advert_list_item_ads":
			$RequiredList = array("item_id");
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

			if (isset($FormData["sort_order"]) == true)
			{
				$SortOrder = $FormData["sort_order"];
			}
			else
			{
				$SortOrder = "sort_name";
			}

			$ItemID = $FormData["item_id"];
			$Result = advert_list_item_ads($GLOBALS["aib_db"],$ItemID,$SortOrder);
			if ($Result["status"] != "OK")
			{
				$OutData = $Result;
				break;
			}

			$OutData["info"] = array();
			$OutData["info"]["records"] = $Result["data"]["records"];
			break;

		// Get all visible advertisements for a given item

		case "advert_visible_ads":
			$RequiredList = array("item_id");
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

			$ItemID = $FormData["item_id"];
			$Result = advert_visible_ads($GLOBALS["aib_db"],$ItemID);
			if ($Result["status"] != "OK")
			{
				$OutData = $Result;
				break;
			}

			$OutData["info"] = array();
			$OutData["info"]["records"] = $Result["data"]["records"];
			break;


		// Get all advertisements associated with item path

		case "advert_path_ads":
			$RequiredList = array("item_id");
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

			$ItemID = $FormData["item_id"];
			$Result = advert_path_ads($GLOBALS["aib_db"],$ItemID);
			if ($Result["status"] != "OK")
			{
				$OutData = $Result;
				break;
			}

			$OutData["info"] = array();
			$OutData["info"]["records"] = $Result["data"]["records"];
			break;

		// Get the file associated with advertisements

		case "advert_get_file":
			$RequiredList = array("record_id");
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

			$RecordID = $FormData["record_id"];
			$Result = advert_get_file($GLOBALS["aib_db"],$RecordID);
			$Result["file_data"] = bin2hex($Result["data"]);
			unset($Result["data"]);
			$OutData = $Result;
			break;

		// Get list of advertisement references

		case "advert_list_item_ads":
			$RequiredList = array("record_id");
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

			$RecordRef = $FormData["record_id"];
			$Result = advert_list_references($GLOBALS["aib_db"],$RecordRef);
			if ($Result["status"] != "OK")
			{
				$OutData = $Result;
				break;
			}

			$OutData["info"] = array();
			$OutData["info"]["records"] = $Result["data"]["records"];
			break;

		case "advert_find_references":
			$RequiredList = array("record_id");
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

			$SourceRef = $FormData["record_id"];
			$SourceAd = advert_get($GLOBALS["aib_db"],$SourceRef);
			if ($SourceAd["status"] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTFOUND";
				break;
			}

			$SourceAd = $SourceAd["record"];
			if (intval($SourceAd["record_ref"]) >= 0)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "REQUESTED AD CANNOT BE A REFERENCE";
				break;
			}

			$OutData = advert_list_references($GLOBALS["aib_db"],$SourceRef);
			break;

		// Set blocking property

		case "advert_set_block":
			$RequiredList = array("action","ad_id","item_id","inherit_flag");
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

			$AdID = $FormData["ad_id"];
			$ItemID = $FormData["item_id"];
			$Action = strtolower($FormData["action"]);
			$InheritFlag = $FormData["inherit_flag"];

			// Make sure the action request type is valid

			$ValidActions = array("block" => true, "unblock" => true, "delete" => true);
			if (isset($ValidActions[$Action]) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALID VALID FOR action";
				break;
			}

			$OutData = advert_set_block($GLOBALS["aib_db"],$ItemID,$AdID,$Action,$InheritFlag);
			break;

		case "advert_list_block":
			$RequiredList = array("item_id");
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

			$ItemID = $FormData["item_id"];
			$OutData = advert_list_blocks($GLOBALS["aib_db"],$ItemID);
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
