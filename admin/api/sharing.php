<?php
//
// Content sharing functions
//

include("api_util.php");

// Log debug
// ---------
function aib_sharing_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_sharing_log_debug.txt","a+");
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

// #########
// MAIN CODE
// #########

	$EntryTypes = array(
		AIB_ITEM_TYPE_ARCHIVE_GROUP => "AG",
		AIB_ITEM_TYPE_ARCHIVE => "AR",
		AIB_ITEM_TYPE_COLLECTION => "CO",
		AIB_ITEM_TYPE_SUBGROUP => "SG",
		AIB_ITEM_TYPE_RECORD => "RE",
		AIB_ITEM_TYPE_ITEM => "IT",
		AIB_ITEM_TYPE_SYSTEM => "SY",
		);

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
		// Share an item by creating a link to an item.  A link is created in the share
		// target folder, and the ownership of the share is set to the user owning the
		// source item, or the user specified in the call.

		case "share_create":

			// Get target (where the link will be created) and the item that is to be linked into the target

			$ShareTarget = get_assoc_default($FormData,"share_target",false);
			$ShareSource = get_assoc_default($FormData,"share_source",false);

			// Get the user ID of the user who owns the item to be linked.  Default is to
			// get it from the item being linked.

			$ShareFromUser = get_assoc_default($FormData,"share_item_user",false);

			// Get title

			$ShareTitle = get_assoc_default($FormData,"share_title",false);
			if ($ShareTarget == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTARGET";
				break;
			}

			if ($ShareSource == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGSOURCE";
				break;
			}
			
			if ($ShareTitle == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLE";
				break;
			}
			
			// Get item info and target info

			$TargetRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ShareTarget);
			$SourceRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ShareSource);
			if ($TargetRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "TARGETNOTFOUND";
				break;
			}
			

			if ($SourceRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "SOURCENOTFOUND";
				break;
			}

			// If user wasn't specified, then use user associated with source item

			if ($ShareFromUser === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($ShareFromUser > 0)
			{
				$ShareUserProfile = ftree_get_user($GLOBALS["aib_db"],$ShareFromUser);
				if ($ShareUserProfile == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "SHAREUSERNOTFOUND";
					break;
				}
			}

			// Create a tree item that is a link

			$ItemDef = array(
				"title" => $ShareTitle,
				"user_id" => $ShareFromUser,
				"group_id" => -1,
				"reference_id" => $ShareSource,
				"source_type" => FTREE_SOURCE_TYPE_LINK,
				"source_info" => "",
				"user_perm" => "RWCMD",
				"group_perm" => "RC",
				"world_perm" => "R",
				"parent" => $ShareTarget,
				"item_type" => FTREE_OBJECT_TYPE_LINK,
				"allow_dups" => "Y",
				);
			$Result = ftree_create_object_ext($GLOBALS["aib_db"],$ItemDef);
			if ($Result[0] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTCREATELINK";
				break;
			}

			$NewObjectID = $Result[1];

			// If there are properties, add them.  These are in a JSON-encoded array passed in a parameter.

			$SharePropertyString = get_assoc_default($FormData,"share_properties",false);
			if ($SharePropertyString !== false)
			{
				$ShareProperties = json_decode($SharePropertyString,true);
				if ($ShareProperties != false)
				{
					foreach($ShareProperties as $PropSetting)
					{
						ftree_set_property($GLOBALS["aib_db"],$NewObjectID,$PropSetting["name"],$PropSetting["value"],true);
					}
				}
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $NewObjectID;
			break;

		case "share_delete":
			$ShareItem = get_assoc_default($FormData,"obj_id",false);
			if ($ShareItem == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEM";
				break;
			}

			ftree_delete($GLOBALS["aib_db"],$ShareItem,false);
			$OutData["status"] = "OK";
			$OutData["info"] = $ShareItem;
			break;


		case "share_list":
			$Direction = strtolower(get_assoc_default($FormData,"perspective","shared_to_user"));
			$UserID = get_assoc_default($FormData,"user_id",false);
			if ($UserID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($UserID != -1)
			{
				$UserInfo = ftree_get_user($GLOBALS["aib_db"],$UserID);
				if ($UserInfo == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "CANNOTFINDUSER";
					break;
				}
			}
			else
			{
				$UserInfo = array("user_top_folder" => -1);
			}

			$PropertyFilterString = get_assoc_default($FormData,"link_property_filter",false);
			if ($PropertyFilterString != false)
			{
				$PropertyFilterSet = json_decode($PropertyFilterString,true);
			}
			else
			{
				$PropertyFilterSet = false;
			}

			switch($Direction)
			{
				// Get links shared to this user by others, optionally for a specific parent

				case "shared_to_user":

					$ParentID = get_assoc_default($FormData,"parent_id",$UserInfo["user_top_folder"]);
					$FilterFormLinks = get_assoc_default($FormData,"filter_form_links","Y");

					// Traverse folder

					$RawList = array();
					ftree_traverse($GLOBALS["aib_db"],$ParentID,$RawList);

					// Filter out everything but links

					$ResultList = array();
					foreach($RawList as $ItemID => $ItemInfo)
					{
						$LocalRecord = $ItemInfo["data"];
						if ($LocalRecord["item_type"] != FTREE_OBJECT_TYPE_LINK)
						{
							continue;
						}

						if ($FilterFormLinks == "Y")
						{
							$LinkClass = ftree_get_property($GLOBALS["aib_db"],$ItemID,"link_class");
							if ($LinkClass == "recform")
							{
								continue;
							}
						}

						if ($PropertyFilterSet != false)
						{
							if (ftree_match_item_properties($GLOBALS["aib_db"],$ItemID,"AND",$PropertyFilterSet) == false)
							{
								continue;
							}
						}

						$ResultList[] = $LocalRecord;
					}

					// Get detail

					$FormData["opt_link_owner"] = "Y";
					$OutData = $ResultList;

					// Call list_objects to get details for pre-populated list of items (ResultList)

					$OutData = ftree_list_child_objects_ext($GLOBALS["aib_db"],-1,$FormData,false,$BlockedProperties,$FieldDataTypeDesc,$FileClassTypeDesc,$ResultList);
					break;

				// Get links shared by this user to others
				case "shared_from_user":
					$FormData["opt_link_owner"] = "Y";
					$TempList = ftree_get_item_links($GLOBALS["aib_db"],false,$UserID);
					$ResultList = array();
					foreach($TempList as $ItemRecord)
					{
						$ItemID = $ItemRecord["item_id"];
						if ($PropertyFilterSet != false)
						{
							if (ftree_match_item_properties($GLOBALS["aib_db"],$ItemID,"AND",$PropertyFilterSet) == false)
							{
								continue;
							}
						}

						$ResultList[] = $ItemRecord;
					}

					$OutData = ftree_list_child_objects_ext($GLOBALS["aib_db"],-1,$FormData,false,$BlockedProperties,$FieldDataTypeDesc,$FileClassTypeDesc,$ResultList);
					break;

				// Get links to an item, where links are owned by a user
				case "item_share":
					$FormData["opt_link_owner"] = "Y";
					$ReferenceID = get_assoc_default($FormData,"item_id",false);
					if ($UserID == -1)
					{
						$TempList = ftree_get_item_links($GLOBALS["aib_db"],$ReferenceID,false);
					}
					else
					{
						$TempList = ftree_get_item_links($GLOBALS["aib_db"],$ReferenceID,$UserID);
					}

					if ($TempList == false)
					{
						$TempList = array();
					}

					$ResultList = array();
					if ($PropertyFilterSet == false)
					{
						$ResultList = $TempList;
					}
					else
					{
						foreach($TempList as $ItemRecord)
						{
							if (ftree_match_item_properties($GLOBALS["aib_db"],$ItemRecord["item_id"],"AND",$PropertyFilterSet) == false)
							{
								continue;
							}

							$ResultList[] = $ItemRecord;
						}
					}

					$ParentMap = array();

					// Get the list of objects

					if (count($ResultList) < 1)
					{
						$OutData["status"] = "OK";
						$OutData["info"] = array("records" => array());
						break;
					}

					$TempData = ftree_list_child_objects_ext($GLOBALS["aib_db"],-1,$FormData,false,$BlockedProperties,$FieldDataTypeDesc,$FileClassTypeDesc,$ResultList);
					$OutData = $TempData;

					// Grab parent profiles as required

					unset($OutData["info"]["records"]);
					$OutData["info"]["records"] = array();
					foreach($TempData["info"]["records"] as $TempRecord)
					{
						$ParentID = $TempRecord["item_parent"];
						if (isset($ParentMap[$ParentID]) == true)
						{
							$TempRecord["parent_info"] = $ParentMap[$ParentID];
						}
						else
						{
							$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$ParentID);
							$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ParentID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
							$LocalEntryType = "IT";
							if (isset($EntryTypes[$EntryTypeProperty]) == true)
							{
								$LocalEntryType = $EntryTypes[$EntryTypeProperty];
							}

							$ItemRecord["item_type"] = $LocalEntryType;
							$TempRecord["parent_info"] = $ItemRecord;
							$ParentMap[$ParentID] = $ItemRecord;
						}

						$OutData["info"]["records"][] = $TempRecord;
					}

					break;

				default:
					$OutData["status"] = "ERROR";
					$OutData["info"] = "BADPERSPECTIVE";
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
