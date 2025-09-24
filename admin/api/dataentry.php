<?php
//
// Data entry functions
//

include("api_util.php");

// Log debug
// ---------
function aib_browse_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_browse_log_debug.txt","a+");
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

	$NewSession = aib_api_generate_session_key($GLOBALS["aib_db"],$KeyHolderID);
	$OutData = array("status" => "OK", "session" => $NewSession);
	$EntryTypes = array(
		AIB_ITEM_TYPE_ARCHIVE_GROUP => "AG",
		AIB_ITEM_TYPE_ARCHIVE => "AR",
		AIB_ITEM_TYPE_COLLECTION => "CO",
		AIB_ITEM_TYPE_SUBGROUP => "SG",
		AIB_ITEM_TYPE_RECORD => "RE",
		AIB_ITEM_TYPE_ITEM => "IT"
		);

	$BlockedProperties = array(
		AIB_FOLDER_PROPERTY_ARCHIVE_NAME => true,
		AIB_FOLDER_PROPERTY_FOLDER_TYPE => true,
		AIB_FOLDER_PROPERTY_FOLDER_ICON => true,
		AIB_FOLDER_PROPERTY_FILE_BATCH => true,
		AIB_ITEM_PROPERTY_LINK_URL => true
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
		case "data_entry_admin_stat":
			if ($RequestUserType != AIB_USER_TYPE_ADMIN)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTADMIN";
				break;
			}

			$DataEntryStats = aib_util_collect_admin_subadmin_stats($RequestUserID);
			$OutData["status"] = "OK";
			$OutData["info"] = array("records" => array());
			foreach($DataEntryStats as $SubID => $SubInfo)
			{
				$OutData["info"]["records"][$SubID] = $SubInfo;
			}

			break;

		case "data_entry_in_folder":
			// Get parent

			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDPARENT";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ParentID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$Result = ftree_data_entry_get_marked_in_parent($GLOBALS["aib_db"],$ParentID);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETITEMS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Result as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"item_id" => $ResultRecord["item_id"],
					"user_id" => $ResultRecord["user_id"],
					"entry_assigned" => $ResultRecord["entry_assigned"],
					"entry_completed" => $ResultRecord["entry_completed"],
					"item_parent_id" => $ResultRecord["item_parent_id"]
					);
			}

			break;


		case "data_entry_folder_incomplete":
			// Get parent

			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDPARENT";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ParentID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$Result = ftree_data_entry_not_complete($GLOBALS["aib_db"],$ParentID);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETITEMS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Result as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"item_id" => $ResultRecord["item_id"],
					"user_id" => $ResultRecord["user_id"],
					"entry_assigned" => $ResultRecord["entry_assigned"],
					"entry_completed" => $ResultRecord["entry_completed"],
					"item_parent_id" => $ResultRecord["item_parent_id"]
					);
			}

			break;

		case "data_entry_folder_complete":
			// Get parent

			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDPARENT";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ParentID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$Result = ftree_data_entry_complete($GLOBALS["aib_db"],$ParentID);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETITEMS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Result as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"item_id" => $ResultRecord["item_id"],
					"user_id" => $ResultRecord["user_id"],
					"entry_assigned" => $ResultRecord["entry_assigned"],
					"entry_completed" => $ResultRecord["entry_completed"],
					"item_parent_id" => $ResultRecord["item_parent_id"]
					);
			}

			break;

		case "data_entry_is_marked":
			// Get parent

			$ParentID = get_assoc_default($FormData,"obj_id",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ParentID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$Result = ftree_check_data_entry($GLOBALS["aib_db"],$ParentID);
			if ($Result == false)
			{
				$OutData["status"] = "OK";
				$OutData["info"]["records"] = array(
					"user_id" => -1,
					"entry_assigned" => -1
					);
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array(
				"user_id" => $Result["user_id"],
				"entry_assigned" => $Result["entry_assigned"],
				);

			break;

		case "data_entry_mark_item_todo":
			// Get parent

			$ParentID = get_assoc_default($FormData,"obj_id",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$UserID = get_assoc_default($FormData,"user_id",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			$AssignList = array();
			$ParentIDList = explode(",",$ParentID);
			foreach($ParentIDList as $LocalParentID)
			{
				if (verify_user_item_access($GLOBALS["aib_db"],$LocalParentID,$RequestUserRoot,$RequestUserID) == false)
				{
					$AssignList[] = array("item_id" => $LocalParentID, "status" => "ERROR", "msg" => "NOACCESS");
					continue;
				}

				$Result = ftree_mark_folder_for_data_entry($GLOBALS["aib_db"],$LocalParentID,$UserID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_RECORD);
				if ($Result == false)
				{
					$AssignList[] = array("item_id" => $LocalParentID, "status" => "ERROR", "msg" => "CANNOTMARK");
					continue;
				}
	
				if ($Result["status"] == "ERROR")
				{
					$AssignList[] = array("item_id" => $LocalParentID, "status" => "ERROR", "msg" => "INVALIDID");
					continue;
				}

				$AssignList[] = array("item_id" => $LocalParentID, "status" => "OK", "msg" => "OK", "total" => $TotalCount,
					"marked" => $MarkedCount, "in_use" => $InUseCount);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array("records" => $AssignList);
			break;

		case "data_entry_mark_folder_todo":
			// Get parent

			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDPARENT";
				break;
			}

			$UserID = get_assoc_default($FormData,"user_id",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			$ChildType = get_assoc_default($FormData,"opt_item_type",AIB_ITEM_TYPE_RECORD);
			$ReassignFlag = get_assoc_default($FormData,"opt_reassign","N");
			if (preg_match("/[Yy]/",$ReassignFlag) != false)
			{
				$ReassignFlag = true;
			}
			else
			{
				$ReassignFlag = false;
			}

			$ParentIDList = explode(",",$ParentID);
			$AssignList = array();
			foreach($ParentIDList as $LocalParentID)
			{
				if (verify_user_item_access($GLOBALS["aib_db"],$LocalParentID,$RequestUserRoot,$RequestUserID) == false)
				{
					$AssignList[] = array("item_id" => $LocalParentID, "status" => "ERROR", "msg" => "NOACCESS");
					continue;
				}

				$Result = ftree_mark_folder_for_data_entry($GLOBALS["aib_db"],$LocalParentID,$UserID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,$ChildType,$ReassignFlag);
				if ($Result == false)
				{
					$AssignList[] = array("item_id" => $LocalParentID, "status" => "ERROR", "msg" => "CANNOTMARK");
					continue;
				}
	
				if ($Result["status"] == "ERROR")
				{
					$AssignList[] = array("item_id" => $LocalParentID, "status" => "ERROR", "msg" => "INVALIDID");
					continue;
				}

				$AssignList[] = array("item_id" => $LocalParentID, "status" => "OK", "msg" => "OK", "total" => $TotalCount,
					"marked" => $MarkedCount, "in_use" => $InUseCount);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array("records" => $AssignList);
			break;


		case "data_entry_mark_complete":
			// Get parent

			$ParentID = get_assoc_default($FormData,"obj_id",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			$CountOnlyFlag = strtoupper(get_assoc_default($FormData,"opt_count_only","N"));
			if (preg_match("/[Y]/",$CountOnlyFlag) != false)
			{
				$CountOnlyFlag = true;
			}
			else
			{
				$CountOnlyFlag = false;
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ParentID == false)
			{
				$ParentID = ftree_get_object_by_path($GLOBALS["aib_db"],$ParentPath);
			}

			if ($ParentID == false && $ParentPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ParentID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			ftree_mark_data_entry_complete($GLOBALS["aib_db"],$ParentID,-1);
			$OutData["status"] = "OK";
			break;

		case "data_entry_unmark_folders":

			// Get folder list and user ID

			$ItemListString = get_assoc_default($FormData,"item_id_list","");
			$UserID = get_assoc_default($FormData,"user_id",false);
			$ItemList = explode(",",$ItemListString);
			foreach($ItemList as $ItemID)
			{
				ftree_unmark_data_entry($GLOBALS["aib_db"],$UserID,$ItemID);
			}

			// Delete orphans

			ftree_delete_data_entry_orphans($GLOBALS["aib_db"]);

			$OutData["status"] = "OK";
			break;

		case "data_entry_waiting":
			// Get user

			$UserID = get_assoc_default($FormData,"user_id",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			$Result = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,true);
			if ($Result === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETITEMS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Result as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"item_id" => $ResultRecord["item_id"],
					"user_id" => $ResultRecord["user_id"],
					"entry_assigned" => $ResultRecord["entry_assigned"],
					"entry_completed" => $ResultRecord["entry_completed"],
					"item_parent_id" => $ResultRecord["item_parent_id"]
					);
			}

			break;

		case "data_entry_complete":
			// Get user

			$UserID = get_assoc_default($FormData,"user_id",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			$Result = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,false);
			if ($Result === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETITEMS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Result as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"item_id" => $ResultRecord["item_id"],
					"user_id" => $ResultRecord["user_id"],
					"entry_assigned" => $ResultRecord["entry_assigned"],
					"entry_completed" => $ResultRecord["entry_completed"],
					"item_parent_id" => $ResultRecord["item_parent_id"]
					);
			}

			break;

		case "data_entry_marked_subs":
			// Get user

			$UserID = get_assoc_default($FormData,"user_id",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			$CompletedOption = get_assoc_default($FormData,"opt_completed","N");
			if ($CompletedOption == "N")
			{
				$CompletedOption = true;
			}
			else
			{
				$CompletedOption = false;
			}

			$Result = ftree_get_data_entry_parents($GLOBALS["aib_db"],$UserID,$CompletedOption);
			if ($Result === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETITEMS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Result as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"parent_id" => $ResultRecord["item_parent"]
					);
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
