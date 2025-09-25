<?php
//
// Create and manage records
//

include("api_util.php");

// Log debug
// ---------
function aib_record_log_debug($Msg)
{
	$Handle = fopen("/tmp/recordop_debug.txt","a+");
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

function modify_links($FormData)
{

	$OutData = array("status" => "OK");
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				return($OutData);
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				return($OutData);
			}

			$RequestedParentList = get_assoc_default($FormData,"parent_list",false);
			if ($RequestedParentList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTLIST";
				return($OutData);
			}

			// Get the item parent.  Error if the submitted item is actually a link.

			$ItemRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
			if ($ItemRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADID";
				return($OutData);
			}

			// If the item isn't an internal or file reference, error.  Do not allow links to links.

			if ($ItemRecord["item_source_type"] != FTREE_SOURCE_TYPE_INTERNAL && $ItemRecord["item_source_type"] != FTREE_SOURCE_TYPE_FILE)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTORIGINAL";
				return($OutData);
			}

			$UserID = get_assoc_default($FormData,"user_id",$ItemRecord["item_user_id"]);
			$UserGroup = get_assoc_default($FormData,"user_group",$ItemRecord["item_group_id"]);

			// Item must be a record

			$ItemType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			if ($ItemType != AIB_ITEM_TYPE_RECORD)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTRECORD";
				return($OutData);
			}

			// Get the base parent for the item

			$ItemParent = $ItemRecord["item_parent"];

			// Get the list of target parent values

			$TempParentList = explode(",",$RequestedParentList);

			// Create list of subgroups where item is to be placed

			$ParentList = array($ItemParent);
			$ParentMap[$ItemParent] = true;
			foreach($TempParentList as $TempParent)
			{
				if ($TempParent == $ItemParent)
				{
					continue;
				}

				$ParentList[] = $TempParent;
				$ParentMap[$TempParent] = true;
			}

			// Get the list of current link locations.  Compare these against the
			// submitted list.  If a current link doesn't appear in the list, it
			// means that it has been removed; save this in a delete list.  Otherwise,
			// note it as being part of the existing set of accepted locations.

			$LinkLocationList = aib_get_item_references($ItemID);
			$DeleteMap = array();
			$ExistingMap = array();
			foreach($LinkLocationList as $LinkRecord)
			{
				// Get parent

				$TempParent = $LinkRecord["item_parent"];

				// If parent isn't in the valid parent list (as submitted), mark for removal.  Else,
				// keep.

				if (isset($ParentMap[$TempParent]) == false)
				{
					$DeleteMap[$TempParent] = $LinkRecord["item_id"];
				}
				else
				{
					$ExistingMap[$TempParent] = $LinkRecord["item_id"];
				}
			}

			// Remove references where required, using the delete map

			foreach($DeleteMap as $TempParent => $TempID)
			{
				ftree_delete($GLOBALS["aib_db"],$TempID,true);
			}

			// Add references where needed

			$CreateResult = array();
			$WorldPermissions = "R";
			$TempRecordTitle = $ItemRecord["item_title"];
			foreach($ParentMap as $ParentID => $ParentCondition)
			{
				// Don't add where already existing

				if (isset($ExistingMap[$ParentID]) == true)
				{
					continue;
				}

				// Don't add to actual parent

				if ($ParentID == $ItemParent)
				{
					continue;
				}

				// Create new entry

				$FolderInfo = array("parent" => $ParentID, 
					"title" => urlencode($TempRecordTitle),
					"user_id" => $UserID,
					"group_id" => $UserGroup,
					"item_type" => FTREE_OBJECT_TYPE_LINK, 
					"source_type" => FTREE_SOURCE_TYPE_LINK,
					"source_info" => "", 
					"reference_id" => $ItemID,
					"allow_dups" => false,
					"user_perm" => "RMWCODPN", 
					"group_perm" => "RMW",
					"world_perm" => $WorldPermissions,
					);

				$FolderResult = ftree_create_object_ext($GLOBALS["aib_db"],$FolderInfo);

				// Save create attempt result.  If all was ok, the "status" field will
				// contain "OK".

				$CreateResult[] = array("parent" => $ParentID, "status" => $FolderResult[0]);
			}

			// Set "records" to the results of the inserts

			$OutData["info"]["records"] = $CreateResult;
	return($OutData);
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
		case "addrecord":
			// Get alternate title flag

			$UseAltTitleFlag = aib_get_with_default($FormData,"use_alt_title","Y");
			if ($UseAltTitleFlag != "Y")
			{
				$AltTitle = false;
			}

			// Get alternate title

			$AltTitle = aib_get_with_default($FormData,"itemrecord_subtitle",false);
			if (ltrim(rtrim($AltTitle)) == "")
			{
				$AltTitle = false;
			}

			// Get all titles method and independent titles method

			$AllTitleMethod = aib_get_with_default($FormData,"itemrecord_all_which_name","rec");
			$IndTitleMethod = aib_get_with_default($FormData,"itemrecord_ind_which_name","rec");
			$PrivacySetting = aib_get_with_default($FormData,"itemrecord_private","N");
			$VisibleSetting = aib_get_with_default($FormData,"itemrecord_visible","Y");
			$URLListString = aib_get_with_default($FormData,"url_list_string","");
			$URLList = explode("\t",$URLListString);
			$FileBatchID = aib_get_with_default($FormData,"file_batch",false);
			if ($FileBatchID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Missing file batch ID";
				break;
			}

			$ParentFolderID = aib_get_with_default($FormData,"parent",false);
			if ($ParentFolderID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Missing parent item code";
				break;
			}

			// Check for duplicate in archive folder for itemrecord title

			$RecordTitle = aib_get_with_default($FormData,"itemrecord_title",false);
			if ($RecordTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Missing record name";
				break;
			}

			// Get tag values

			$TagString = aib_get_with_default($FormData,"itemrecord_default_tags","");
			$TempDef = ftree_get_child_object($GLOBALS["aib_db"],$ParentFolderID,FTREE_OBJECT_TYPE_FOLDER,urlencode($RecordTitle));
			if ($TempDef != false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Title already used";
				break;
			}

			$FormID = aib_get_with_default($FormData,"form_id","NULL");

			// Use drop-down for "visible" to set world permissions on item.

			$RecordVisible = aib_get_with_default($FormData,"itemrecord_visible","Y");
			if ($RecordVisible == "Y")
			{
				$WorldPermissions = "R";
			}
			else
			{
				$WorldPermissions = "";
			}

			$FileBatch = aib_get_with_default($FormData,"file_batch","-1");
			$AttachFlagValue = aib_get_with_default($FormData,"itemrecord_fileattach","all");

			// Get OCR flag

			$OCRFlag = aib_get_with_default($FormData,"file_handling","N");

			// Get all user-defined and default fields.  The key in the array is the field ID, data is the value

			$DefaultFields = array();
			$UserFields = array();
			if (isset($FormData["itemrecord_default_url"]) == true)
			{
				$URLField = $FormData["itemrecord_default_url"];
			}
			else
			{
				$URLField = "";
			}

			foreach($FormData as $FieldName => $FieldValue)
			{
				if (preg_match("/^userfield[\_][0-9]+/",$FieldName) != false)
				{
					$LocalFieldID = preg_replace("/[^0-9]/","",$FieldName);
					$UserFields[$LocalFieldID] = $FieldValue;
				}

				if (preg_match("/^itemrecord[\_]default[\_]/",$FieldName) != false)
				{
					if ($FieldName != "itemrecord_default_url")
					{
						$LocalFieldID = preg_replace("/^itemrecord[\_]default[\_]/","",$FieldName);
						$DefaultFields[$LocalFieldID] = $FieldValue;
					}
				}
			}

			// Get the list of sub-folders to which this entry will be added

			$ParentFolderString = aib_get_with_default($FormData,"parent_list","");
			$TempParentFolderList = explode(",",$ParentFolderString);

			// Create parent folder list such that the primary parent is the first entry

			$ParentFolderList = array($ParentFolderID);
			foreach($TempParentFolderList as $TempParentID)
			{
				if ($ParentFolderID != $TempParentID)
				{
					$ParentFolderList[] = $TempParentID;
				}
			}

			// Create record(s) and individual items.  If there are no files uploaded, then assume "all" attached to one record by default.

			$ProcessResults = array();
			$MasterID = false;
			$BatchList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatch);
			$UserType = aib_get_with_default($FormData,"userdef_user_type","");
			$UserID = aib_get_with_default($FormData,"userdef_user_id","1");
			$UserGroup = aib_get_with_default($FormData,"userdef_user_group","");

			foreach($URLList as $URLEntry)
			{
				// Trim leading/trailing whitespace

				$LocalURLEntry = rtrim(ltrim($URLEntry));
				if ($LocalURLEntry == "")
				{
					continue;
				}

				// If there's no HTTP/HTTPS prefix, ignore

				if (preg_match("/^[Hh][Tt][Tt][Pp]/",$LocalURLEntry) == false)
				{
					continue;
				}

				// URL is ok...add to list

				$BatchList[] = array("_url" => $LocalURLEntry);
			}

			if ($AttachFlagValue == "all" || count($BatchList) < 1)
			{
				// Create record

				$Counter = 0;
				foreach($ParentFolderList as $SelectedFolderID)
				{
					if ($SelectedFolderID == $ParentFolderID)
					{
						$FolderInfo = array("parent" => $SelectedFolderID, 
							"title" => urlencode($RecordTitle),
							"user_id" => $UserID,
							"group_id" => $UserGroup,
							"item_type" => FTREE_OBJECT_TYPE_FOLDER, 
							"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
							"source_info" => "", 
							"reference_id" => -1, 
							"allow_dups" => false,
							"user_perm" => "RMWCODPN", 
							"group_perm" => "RMW",
							"world_perm" => $WorldPermissions
							);
					}
					else
					{
						$FolderInfo = array("parent" => $SelectedFolderID, 
							"title" => urlencode($RecordTitle),
							"user_id" => $UserID,
							"group_id" => $UserGroup,
							"item_type" => FTREE_OBJECT_TYPE_LINK, 
							"source_type" => FTREE_SOURCE_TYPE_LINK,
							"source_info" => "", 
							"reference_id" => $MasterID,
							"allow_dups" => false,
							"user_perm" => "RMWCODPN", 
							"group_perm" => "RMW",
							"world_perm" => $WorldPermissions
							);
					}

					$FolderResult = ftree_create_object_ext($GLOBALS["aib_db"],$FolderInfo);
					if ($FolderResult[0] != "OK")
					{
						$OutData["status"] = "ERROR";
						if ($SelectedFolderID == $ParentFolderID)
						{
							$OutData["info"] = "CANNOTCREATEPRIMARY";
							break;
						}
						else
						{
							$ProcessResults[] = array("status" => "ERROR", "type" => "CREATELINK", "msg" => $FolderResult[1]);
							continue;
						}
					}
					else
					{
						if ($SelectedFolderID == $ParentFolderID)
						{
							$ProcessResults[] = array("status" => "OK", "type" => "CREATEPRIMARY", "msg" => $FolderResult[1]);
						}
						else
						{
							$ProcessResults[] = array("status" => "OK", "type" => "CREATELINK", "msg" => $FolderResult[1]);
						}
					}

					$NewRecordID = $FolderResult[1];
					if ($SelectedFolderID == $ParentFolderID)
					{
						$MasterID = $NewRecordID;
					}

					// Set form used

					$FormID = aib_get_with_default($FormData,"form_id","NULL");
					if ($FormID != "NULL" && $FormID != "BLANK")
					{
						ftree_field_set_item_form($GLOBALS["aib_db"],$NewRecordID,$FormID);
					}

					// Create property which indicates the type of folder

					ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_RECORD,true);

					// Create property for the file batch

					ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_FILE_BATCH,$FileBatch,true);

					// Store user-defined field information

					ftree_field_store_item_fields($GLOBALS["aib_db"],$NewRecordID,$UserFields);
					ftree_field_store_item_fields($GLOBALS["aib_db"],$NewRecordID,$DefaultFields,true);

					// Store property for URL

					ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_RECORD_ITEM_PROPERTY_URL,$URLField);

					// Store visible and private property values

					ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_VISIBLE,$VisibleSetting,true);
					ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_PRIVATE,$PrivacySetting,true);

					// Store tags

					aib_add_item_tags($GLOBALS["aib_db"],$NewRecordID,$TagString,",");

					// Update tag notifiers

					if ($PrivacySetting != "Y")
					{
						aib_update_notifier_queue($GLOBALS["aib_db"],$NewRecordID,$TagString,",");
					}

					// Generate indexing document

					$ExportRecord = array("item_id" => $NewRecordID);
					ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));

					// For each file, create a child item for the new record, and insert each into the
					// file processing batch table.  Only do this for the original, not linked records.

					$NewItemErrors = array();
					$FileProcessErrors = array();
					$NewItemList = array();
					$ItemIteratorNumber = 1;
					$UserType = aib_get_with_default($FormData,"userdef_user_type","");
					$UserID = aib_get_with_default($FormData,"userdef_user_id","1");
					$UserGroup = aib_get_with_default($FormData,"userdef_user_group","");
					if ($SelectedFolderID == $ParentFolderID)
					{
						foreach($BatchList as $BatchRecord)
						{
							// Create a new item for the file and save all field data

							if (isset($BatchRecord["_url"]) == false)
							{
								$ItemInfo = array("parent" => $MasterID,
									"title" => urlencode($RecordTitle),
									"user_id" => $UserID,
									"group_id" => $UserGroup,
									"item_type" => FTREE_OBJECT_TYPE_FILE,
									"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
									"source_info" => "",
									"reference_id" => -1,
									"allow_dups" => true,
									"user_perm" => "RMWCODPN",
									"group_perm" => "RMW",
									"world_perm" => $WorldPermissions,
									);
							}
							else
							{
								$ItemInfo = array("parent" => $MasterID,
									"title" => urlencode($BatchRecord["_url"]),
									"user_id" => $UserID,
									"group_id" => $UserGroup,
									"item_type" => FTREE_OBJECT_TYPE_LINK,
									"source_type" => FTREE_SOURCE_TYPE_URL,
									"source_info" => urlencode($BatchRecord["_url"]),
									"reference_id" => -1,
									"allow_dups" => true,
									"user_perm" => "RMWCODPN",
									"group_perm" => "RMW",
									"world_perm" => $WorldPermissions,
									);
							}


							if ($AltTitle != false)
							{
								$ItemInfo["title"] = urlencode($AltTitle);
							}

							if ($AllTitleMethod == "rec")
							{
								if ($AltTitle != false)
								{
									$ItemInfo["title"] = urlencode($AltTitle.sprintf(" %9d",$ItemIteratorNumber));
								}
								else
								{
									$ItemInfo["title"] = urlencode($RecordTitle).sprintf(" %9d",$ItemIteratorNumber);
								}
							}
							else
							{
								$ItemInfo["title"] = $BatchRecord["file_name"];
							}

							$ItemResult = ftree_create_object_ext($GLOBALS["aib_db"],$ItemInfo);
							if ($ItemResult[0] != "OK")
							{
								$ProcessResults[] = array("status" => "ERROR", "type" => "CREATEITEM", "msg" => $ItemResult[1]);
								continue;
							}
							else
							{
								$ProcessResults[] = array("status" => "OK", "type" => "CREATEITEM", "msg" => $ItemResult[1]);
							}

							$ItemIteratorNumber++;
							$FormID = aib_get_with_default($FormData,"form_id","NULL");
							if ($FormID != "NULL" && $FormID != "BLANK")
							{
								ftree_field_set_item_form($GLOBALS["aib_db"],$ItemResult[1],$FormID);
							}

							// Store property for URL

							if (isset($BatchRecord["_url"]) == false)
							{
								ftree_set_property($GLOBALS["aib_db"],$ItemResult[1],AIB_RECORD_ITEM_PROPERTY_URL,$URLField);
							}

							// Store user-defined field information

							ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemResult[1],$UserFields);
							ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemResult[1],$DefaultFields,true);

							// Store tags

							aib_add_item_tags($GLOBALS["aib_db"],$ItemResult[1],$TagString,",");

							// Update tag notifiers

							if ($PrivacySetting != "Y")
							{
								aib_update_notifier_queue($GLOBALS["aib_db"],$ItemResult[1],$TagString,",");
							}

							// Add file to batch processing list with operation codes

							$BatchInfoList = array(AIB_BATCH_STORAGE_REQUEST."=".$ItemResult[1]);
							if ($OCRFlag == "Y")
							{
								$BatchInfoList[] = AIB_BATCH_OCR_REQUEST."=".$ItemResult[1];
							}

							if ($AltTitle != false)
							{
								$BatchInfoList[] = AIB_BATCH_USE_ALT_TITLE."=".urlencode($AltTitle);
							}

							if (isset($BatchRecord["_url"]) == false)
							{
								$BatchInfo = join("\t",$BatchInfoList);
								$BatchResult = aib_store_file_batch_entry($GLOBALS["aib_db"],AIB_BATCH_RECORD_TYPE_UPLOAD,$BatchInfo,$BatchRecord["record_id"]);
								if ($BatchResult[0] != "OK")
								{
									$ProcessResults[] = array(
										"status" => "ERROR", 
										"type" => "STOREFILEBATCHENTRY", 
										"msg" => $BatchResult[1], 
										"record_id" => $BatchRecord["record_id"],
										"file_name" => $BatchRecord["file_name"]
										);
								}
								else
								{
									$ProcessResults[] = array(
										"status" => "OK", 
										"type" => "STOREFILEBATCHENTRY", 
										"msg" => $BatchResult[1], 
										"record_id" => $BatchRecord["record_id"],
										"file_name" => $BatchRecord["file_name"]
										);
								}
							}
							else
							{
								$ProcessResults[] = array(
									"status" => "OK", 
									"type" => "URLSTORE", 
									"msg" => "OK",
									"record_id" => "",
									"file_name" => "",
								);
							}
	
							// Generate indexing document

							$ExportRecord = array("item_id" => $BatchResult[1]);
							ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));

						}
					}
				}
			}
			else
			{
				// Create a record for each uploaded file, and attach file to a single item in each record

				foreach($ParentFolderList as $SelectedFolderID)
				{
					$BatchCounter = 0;
					$ItemIteratorNumber = 1;
					foreach($BatchList as $BatchRecord)
					{
						if ($IndTitleMethod == "rec")
						{
							$TempRecordTitle = $RecordTitle.sprintf(" %9d",$ItemIteratorNumber);
						}
						else
						{
							$TempRecordTitle = $BatchRecord["file_name"];
						}

						$BatchCounter++;

						// Create itemrecord

						if ($SelectedFolderID == $ParentFolderID)
						{
							$FolderInfo = array("parent" => $SelectedFolderID, 
								"title" => urlencode($TempRecordTitle),
								"user_id" => $UserID,
								"group_id" => $UserGroup,
								"item_type" => FTREE_OBJECT_TYPE_FOLDER, 
								"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
								"source_info" => "", 
								"reference_id" => -1, 
								"allow_dups" => false,
								"user_perm" => "RMWCODPN", 
								"group_perm" => "RMW",
								"world_perm" => $WorldPermissions
								);
						}
						else
						{
							$FolderInfo = array("parent" => $SelectedFolderID, 
								"title" => urlencode($TempRecordTitle),
								"user_id" => $UserID,
								"group_id" => $UserGroup,
								"item_type" => FTREE_OBJECT_TYPE_LINK, 
								"source_type" => FTREE_SOURCE_TYPE_LINK,
								"source_info" => "", 
								"reference_id" => $MasterID,
								"allow_dups" => false,
								"user_perm" => "RMWCODPN", 
								"group_perm" => "RMW",
								"world_perm" => $WorldPermissions
							);
						}

						$FolderResult = ftree_create_object_ext($GLOBALS["aib_db"],$FolderInfo);
						if ($FolderResult[0] != "OK")
						{
							$OutData["status"] = "ERROR";
							if ($SelectedFolderID == $ParentFolderID)
							{
								$OutData["info"] = "CANNOTCREATEPRIMARY";
								break;
							}
							else
							{
								$ProcessResults[] = array("status" => "ERROR", "type" => "CREATELINK", "msg" => $FolderResult[1]);
								continue;
							}
						}
						else
						{
							if ($SelectedFolderID == $ParentFolderID)
							{
								$ProcessResults[] = array("status" => "OK", "type" => "CREATEPRIMARY", "msg" => $FolderResult[1]);
							}
							else
							{
								$ProcessResults[] = array("status" => "OK", "type" => "CREATELINK", "msg" => $FolderResult[1]);
							}
						}

						$NewRecordID = $FolderResult[1];
						if ($SelectedFolderID == $ParentFolderID)
						{
							$MasterID = $NewRecordID;
						}

						$FormID = aib_get_with_default($FormData,"form_id","NULL");
						if ($FormID != "NULL" && $FormID != "BLANK")
						{
							ftree_field_set_item_form($GLOBALS["aib_db"],$NewRecordID,$FormID);
						}


						// Create property which indicates the type of folder

						ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_RECORD,true);

						// Create property for the file batch

						ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_FILE_BATCH,$FileBatch,true);


						// Store user-defined field information

						ftree_field_store_item_fields($GLOBALS["aib_db"],$NewRecordID,$UserFields);
						ftree_field_store_item_fields($GLOBALS["aib_db"],$NewRecordID,$DefaultFields,true);

						// Store property for URL

						ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_RECORD_ITEM_PROPERTY_URL,$URLField);

						// Set private and visible properties

						ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_VISIBLE,$VisibleSetting,true);
						ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_PRIVATE,$PrivacySetting,true);

						// Store tags

						aib_add_item_tags($GLOBALS["aib_db"],$NewRecordID,$TagString,",");

						// Update tag notifiers

						if ($PrivacySetting != "Y")
						{
							aib_update_notifier_queue($GLOBALS["aib_db"],$NewRecordID,$TagString,",");
						}

						$ExportRecord = array("item_id" => $NewRecordID);
						ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));

						// Create a child item for the new record, and insert each into the
						// file processing batch table.

						if ($SelectedFolderID == $ParentFolderID)
						{
							$NewItemErrors = array();
							$FileProcessErrors = array();
							$NewItemList = array();

							// Create a new item for the file and save all field data

							if ($IndTitleMethod == "rec")
							{
								if ($AltTitle != false)
								{
									$TempItemTitle = $AltTitle.sprintf(" %9d",$ItemIteratorNumber);
								}
								else
								{
									$TempItemTitle = $RecordTitle.sprintf(" %9d",$ItemIteratorNumber);
								}
							}
							else
							{
								if (isset($BatchRecord["_url"]) == false)
								{
									$TempItemTitle = $BatchRecord["file_name"];
								}
								else
								{
									$TempItemTitle = $BatchRecord["_url"];
								}
							}

							if (isset($BatchRecord["_url"]) == false)
							{
								$ItemInfo = array("parent" => $MasterID,
									"title" => urlencode($TempItemTitle),
									"user_id" => $UserID,
									"group_id" => $UserGroup,
									"item_type" => FTREE_OBJECT_TYPE_FILE,
									"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
									"source_info" => "",
									"reference_id" => -1,
									"allow_dups" => true,
									"user_perm" => "RMWCODPN",
									"group_perm" => "RMW",
									"world_perm" => $WorldPermissions,
									);
							}
							else
							{
								$ItemInfo = array("parent" => $MasterID,
									"title" => urlencode($TempItemTitle),
									"user_id" => $UserID,
									"group_id" => $UserGroup,
									"item_type" => FTREE_OBJECT_TYPE_LINK,
									"source_type" => FTREE_SOURCE_TYPE_URL,
									"source_info" => $BatchRecord["_url"],
									"reference_id" => -1,
									"allow_dups" => true,
									"user_perm" => "RMWCODPN",
									"group_perm" => "RMW",
									"world_perm" => $WorldPermissions,
									);
							}

							$ItemResult = ftree_create_object_ext($GLOBALS["aib_db"],$ItemInfo);
							if ($ItemResult[0] != "OK")
							{
								$ProcessResults[] = array("status" => "ERROR", "type" => "CREATEITEM", "msg" => $ItemResult[1]);
								continue;
							}
							else
							{
								$ProcessResults[] = array("status" => "OK", "type" => "CREATEITEM", "msg" => $ItemResult[1]);
							}

							$ItemIteratorNumber++;
							$FormID = aib_get_with_default($FormData,"form_id","NULL");
							if ($FormID != "NULL" && $FormID != "BLANK")
							{
								ftree_field_set_item_form($GLOBALS["aib_db"],$ItemResult[1],$FormID);
							}

							// Store property for URL

							if (isset($BatchRecord["_url"]) == false)
							{
								ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_RECORD_ITEM_PROPERTY_URL,$URLField);
							}

							// Store user-defined field information

							ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemResult[1],$UserFields);
							ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemResult[1],$DefaultFields,true);

							// Store tags

							aib_add_item_tags($GLOBALS["aib_db"],$ItemResult[1],$TagString,",");

							// Update tag notifiers

							if ($PrivacySetting != "Y")
							{
								aib_update_notifier_queue($GLOBALS["aib_db"],$ItemResult[1],$TagString,",");
							}

							// Generate indexing document

							$ExportRecord = array("item_id" => $ItemResult[1]);
							ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));


							// Add file to batch processing list with operation codes

							if (isset($BatchRecord["_url"]) == false)
							{
								$BatchInfoList = array(AIB_BATCH_STORAGE_REQUEST."=".$ItemResult[1]);
								if ($OCRFlag == "Y")
								{
									$BatchInfoList[] = AIB_BATCH_OCR_REQUEST."=".$ItemResult[1];
								}
	
								if ($AltTitle != false)
								{
									$BatchInfoList[] = AIB_BATCH_USE_ALT_TITLE."=".urlencode($AltTitle);
								}
	
								$BatchInfo = join("\t",$BatchInfoList);
								$BatchResult = aib_store_file_batch_entry($GLOBALS["aib_db"],AIB_BATCH_RECORD_TYPE_UPLOAD,$BatchInfo,$BatchRecord["record_id"]);
								if ($BatchResult[0] != "OK")
								{
									$ProcessResults[] = array(
										"status" => "ERROR", 
										"type" => "STOREFILEBATCHENTRY", 
										"msg" => $BatchResult[1], 
										"record_id" => $BatchRecord["record_id"],
										"file_name" => $BatchRecord["file_name"]
										);
								}
								else
								{
									$ProcessResults[] = array(
										"status" => "OK", 
										"type" => "STOREFILEBATCHENTRY", 
										"msg" => $BatchResult[1], 
										"record_id" => $BatchRecord["record_id"],
										"file_name" => $BatchRecord["file_name"]
									);
								}
							}
							else
							{
								$ProcessResults[] = array(
									"status" => "OK", 
									"type" => "STOREURL", 
									"msg" => "OK",
									"record_id" => "",
									"file_name" => "",
								);
							}
						}
					}
				}
			}

			if ($OutData["status"] != "ERROR")
			{
				$OutData["info"]["records"] = $ProcessResults;
			}

			break;

		// Update record

		case "updaterecord":

			// Get ID of object being updated

			$ObjectID = aib_get_with_default($FormData,"primary",false);
			if ($ObjectID == false || $ObjectID == "")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Missing item ID";
				break;
			}

			// Get object record

			$ObjectRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ObjectID);
			if ($ObjectRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Cannot retrieve tree item";
				break;
			}

			// Get object class (item or record)

			$ObjectClass = ftree_get_property($GLOBALS["aib_db"],$ObjectID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			if ($ObjectClass == false)
			{
				$ObjectClass = AIB_ITEM_TYPE_ITEM;
			}

			$ParentFolderID = $ObjectRecord["item_parent"];

			$TagString = aib_get_with_default($FormData,"itemrecord_default_tags","");

			// Get alternate title flag

			$UseAltTitleFlag = aib_get_with_default($FormData,"use_alt_title","Y");
			if ($UseAltTitleFlag != "Y")
			{
				$AltTitle = false;
			}

			// Get alternate title

			$AltTitle = aib_get_with_default($FormData,"itemrecord_subtitle",false);
			if (ltrim(rtrim($AltTitle)) == "")
			{
				$AltTitle = false;
			}

			// Get privacy and visibility settings

			$PrivacySetting = aib_get_with_default($FormData,"itemrecord_private","N");
			$VisibleSetting = aib_get_with_default($FormData,"itemrecord_visible","Y");

			// Get all titles method and independent titles method

			$AllTitleMethod = aib_get_with_default($FormData,"itemrecord_all_which_name","rec");
			$IndTitleMethod = aib_get_with_default($FormData,"itemrecord_ind_which_name","rec");
			$FileBatchID = aib_get_with_default($FormData,"file_batch",false);

			// Check for duplicate in archive folder for itemrecord title

			$RecordTitle = aib_get_with_default($FormData,"itemrecord_title",false);
			if ($RecordTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Missing record title";
				break;
			}

			$TempDef = ftree_get_child_object($GLOBALS["aib_db"],$ParentFolderID,FTREE_OBJECT_TYPE_FOLDER,urlencode($RecordTitle));
			if ($TempDef != false)
			{
				if ($TempDef["item_id"] != $ObjectID)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "Title already used";
					break;
				}
			}

			// Get form ID, if any

			$FormID = aib_get_with_default($FormData,"form_id","NULL");

			// Use drop-down for "visible" to set world permissions on item.

			$RecordVisible = aib_get_with_default($FormData,"itemrecord_visible","Y");
			if ($RecordVisible == "Y")
			{
				$WorldPermissions = "R";
			}
			else
			{
				$WorldPermissions = "";
			}

			// Get file batch, if any.

			$FileBatch = aib_get_with_default($FormData,"file_batch","-1");
//			$AttachFlagValue = aib_get_with_default($FormData,"itemrecord_fileattach","all");
			$AttachFlagValue = "all";

			// Get OCR flag

			$OCRFlag = aib_get_with_default($FormData,"file_handling","N");

			// Get all user-defined and default fields.  The key in the array is the field ID, data is the value

			$DefaultFields = array();
			$UserFields = array();
			if (isset($FormData["itemrecord_default_url"]) == true)
			{
				$URLField = $FormData["itemrecord_default_url"];
			}
			else
			{
				$URLField = "";
			}

			foreach($FormData as $FieldName => $FieldValue)
			{
				if (preg_match("/^userfield[\_][0-9]+/",$FieldName) != false)
				{
					$LocalFieldID = preg_replace("/[^0-9]/","",$FieldName);
					$UserFields[$LocalFieldID] = $FieldValue;
				}

				if (preg_match("/^itemrecord[\_]default[\_]/",$FieldName) != false)
				{
					if ($FieldName != "itemrecord_default_url")
					{
						$LocalFieldID = preg_replace("/^itemrecord[\_]default[\_]/","",$FieldName);
						$DefaultFields[$LocalFieldID] = $FieldValue;
					}
				}
			}

			// Get the list of sub-folders to which this entry will be added

			$ParentFolderString = aib_get_with_default($FormData,"parent_list","");
			$TempParentFolderList = explode(",",$ParentFolderString);

			// Create parent folder list such that the primary parent is the first entry

			$ParentFolderList = array($ParentFolderID);
			foreach($TempParentFolderList as $TempParentID)
			{
				if ($ParentFolderID != $TempParentID)
				{
					$ParentFolderList[] = $TempParentID;
				}
			}

			// Create record(s) and individual items.  If there are no files uploaded, then assume "all" attached to one record by default.

			$ProcessResults = array();
			$MasterID = $ObjectID;

			// Get all user-defined and default fields.  The key in the array is the field ID, data is the value

			$DefaultFields = array();
			$UserFields = array();
			if (isset($FormData["itemrecord_default_url"]) == true)
			{
				$URLField = $FormData["itemrecord_default_url"];
			}
			else
			{
				$URLField = "";
			}

			foreach($FormData as $FieldName => $FieldValue)
			{
				if (preg_match("/^userfield[\_][0-9]+/",$FieldName) != false)
				{
					$LocalFieldID = preg_replace("/[^0-9]/","",$FieldName);
					$UserFields[$LocalFieldID] = $FieldValue;
				}

				if (preg_match("/^itemrecord[\_]default[\_]/",$FieldName) != false)
				{
					if ($FieldName != "itemrecord_default_url")
					{
						$LocalFieldID = preg_replace("/^itemrecord[\_]default[\_]/","",$FieldName);
						$DefaultFields[$LocalFieldID] = $FieldValue;
					}
				}
			}

			// Update title

			ftree_rename($GLOBALS["aib_db"],$ObjectID,urlencode($RecordTitle),false);

			// Set private and visible properties

			ftree_set_property($GLOBALS["aib_db"],$ObjectID,AIB_FOLDER_PROPERTY_VISIBLE,$VisibleSetting,true);
			ftree_set_property($GLOBALS["aib_db"],$ObjectID,AIB_FOLDER_PROPERTY_PRIVATE,$PrivacySetting,true);

			// Update record and/or item list

			if ($ObjectClass == AIB_ITEM_TYPE_RECORD)
			{
				// Delete fields for record or item

				ftree_field_delete_item_field($GLOBALS["aib_db"],$ObjectID,false);

				// Store user-defined field information

				ftree_field_store_item_fields($GLOBALS["aib_db"],$ObjectID,$UserFields);
				ftree_field_store_item_fields($GLOBALS["aib_db"],$ObjectID,$DefaultFields,true);

				// Store property for URL

				ftree_set_property($GLOBALS["aib_db"],$ObjectID,AIB_RECORD_ITEM_PROPERTY_URL,$URLField);

				// Store tags

				aib_del_item_tags($GLOBALS["aib_db"],$ObjectID);
				aib_add_item_tags($GLOBALS["aib_db"],$ObjectID,$TagString,",");

				// Update tag notifiers

				if ($PrivacySetting != "Y")
				{
					aib_update_notifier_queue($GLOBALS["aib_db"],$ObjectID,$TagString,",");
				}

				// Generate indexing document

				$ExportRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ObjectID);
				ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));

				// Update links

				$TempFormData = $FormData;
				$TempFormData["obj_id"] = $FormData["objid"];
				modify_links($TempFormData);

				// Add any new items

				$BatchList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatch);
				foreach($BatchList as $BatchRecord)
				{
					// Create a new item for the file and save all field data

					$ItemInfo = array("parent" => $MasterID,
						"title" => urlencode($RecordTitle),
						"user_id" => $UserID,
						"group_id" => $UserGroup,
						"item_type" => FTREE_OBJECT_TYPE_FILE,
						"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
						"source_info" => "",
						"reference_id" => -1,
						"allow_dups" => true,
						"user_perm" => "RMWCODPN",
						"group_perm" => "RMW",
						"world_perm" => $WorldPermissions,
						);

					if ($AltTitle != false)
					{
						$ItemInfo["title"] = urlencode($AltTitle);
					}

					if ($AllTitleMethod == "rec")
					{
						if ($AltTitle != false)
						{
							$ItemInfo["title"] = urlencode($AltTitle.sprintf(" %9d",$ItemIteratorNumber));
						}
						else
						{
							$ItemInfo["title"] = urlencode($RecordTitle).sprintf(" %9d",$ItemIteratorNumber);
						}
					}
					else
					{
						$ItemInfo["title"] = $BatchRecord["file_name"];
					}

					$ItemResult = ftree_create_object_ext($GLOBALS["aib_db"],$ItemInfo);
					if ($ItemResult[0] != "OK")
					{
						$ProcessResults[] = array("status" => "ERROR", "type" => "CREATEITEM", "msg" => $ItemResult[1]);
						continue;
					}
					else
					{
						$ProcessResults[] = array("status" => "OK", "type" => "CREATEITEM", "msg" => $ItemResult[1]);
					}

					$ItemIteratorNumber++;
					if ($FormID != "NULL" && $FormID != "BLANK")
					{
						ftree_field_set_item_form($GLOBALS["aib_db"],$ItemResult[1],$FormID);
					}

					// Store property for URL

					ftree_set_property($GLOBALS["aib_db"],$ItemResult[1],AIB_RECORD_ITEM_PROPERTY_URL,$URLField);

					// Store user-defined field information

					ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemResult[1],$UserFields);
					ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemResult[1],$DefaultFields,true);

					// Store tags

					aib_add_item_tags($GLOBALS["aib_db"],$ItemResult[1],$TagString,",");

					// Update tag notifiers

					if ($PrivacySetting != "Y")
					{
						aib_update_notifier_queue($GLOBALS["aib_db"],$ItemResult[1],$TagString,",");
					}

					// Add file to batch processing list with operation codes

					$BatchInfoList = array(AIB_BATCH_STORAGE_REQUEST."=".$ItemResult[1]);
					if ($OCRFlag == "Y")
					{
						$BatchInfoList[] = AIB_BATCH_OCR_REQUEST."=".$ItemResult[1];
					}

					if ($AltTitle != false)
					{
						$BatchInfoList[] = AIB_BATCH_USE_ALT_TITLE."=".urlencode($AltTitle);
					}

					$BatchInfo = join("\t",$BatchInfoList);
					$BatchResult = aib_store_file_batch_entry($GLOBALS["aib_db"],AIB_BATCH_RECORD_TYPE_UPLOAD,$BatchInfo,$BatchRecord["record_id"]);
					if ($BatchResult[0] != "OK")
					{
						$ProcessResults[] = array(
							"status" => "ERROR", 
							"type" => "STOREFILEBATCHENTRY", 
							"msg" => $BatchResult[1], 
							"record_id" => $BatchRecord["record_id"],
							"file_name" => $BatchRecord["file_name"]
							);
					}
					else
					{
						$ProcessResults[] = array(
							"status" => "OK", 
							"type" => "STOREFILEBATCHENTRY", 
							"msg" => $BatchResult[1], 
							"record_id" => $BatchRecord["record_id"],
							"file_name" => $BatchRecord["file_name"]
							);
					}

					// Generate indexing document

					$ExportRecord = array("item_id" => $BatchResult[1]);
					ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));
				}
			}
			else
			{
				// Processing a single item in a record.  First, delete existing fields.

				ftree_field_delete_item_field($GLOBALS["aib_db"],$ObjectID,false);

				// Store property for URL

				ftree_set_property($GLOBALS["aib_db"],$ObjectID,AIB_RECORD_ITEM_PROPERTY_URL,$URLField);

				// Store user-defined field information

				ftree_field_store_item_fields($GLOBALS["aib_db"],$ObjectID,$UserFields);
				ftree_field_store_item_fields($GLOBALS["aib_db"],$ObjectID,$DefaultFields,true);

				// Store tags

				aib_del_item_tags($GLOBALS["aib_db"],$ObjectID);
				aib_add_item_tags($GLOBALS["aib_db"],$ObjectID,$TagString,",");

				// Update tag notifiers

				if ($PrivacySetting != "Y")
				{
					aib_update_notifier_queue($GLOBALS["aib_db"],$ObjectID,$TagString,",");
				}

				// Replace image file if an image was selected

				$BatchList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatch);
				if (count($BatchList) > 0)
				{
					// Get the list of existing files

					$ImageList = aib_get_files_for_item($GLOBALS["aib_db"],$ObjectID,false);

					// Delete the images

					if ($ImageList != false)
					{
						foreach($ImageList as $ImageRecord)
						{
							aib_remove_file_from_item($GLOBALS["aib_db"],$ObjectID,$ImageRecord["record_id"]);
						}
					}

					$BatchRecord = $BatchList[0];

					// Add file to batch processing list with operation codes

					$BatchInfoList = array(AIB_BATCH_STORAGE_REQUEST."=".$ObjectID);
					if ($OCRFlag == "Y")
					{
						$BatchInfoList[] = AIB_BATCH_OCR_REQUEST."=".$ObjectID;
					}

					if ($AltTitle != false)
					{
						$BatchInfoList[] = AIB_BATCH_USE_ALT_TITLE."=".urlencode($AltTitle);
					}

					$BatchInfo = join("\t",$BatchInfoList);
					$BatchResult = aib_store_file_batch_entry($GLOBALS["aib_db"],AIB_BATCH_RECORD_TYPE_UPLOAD,$BatchInfo,$BatchRecord["record_id"]);
					if ($BatchResult[0] != "OK")
					{
						$ProcessResults[] = array(
							"status" => "ERROR", 
							"type" => "STOREFILEBATCHENTRY", 
							"msg" => $BatchResult[1], 
							"record_id" => $BatchRecord["record_id"],
							"file_name" => $BatchRecord["file_name"]
							);
					}
					else
					{
						$ProcessResults[] = array(
							"status" => "OK", 
							"type" => "STOREFILEBATCHENTRY", 
							"msg" => $BatchResult[1], 
							"record_id" => $BatchRecord["record_id"],
							"file_name" => $BatchRecord["file_name"]
							);
					}
				}

				$ExportRecord = array("item_id" => $ObjectID);
				ftree_field_export_to_search($GLOBALS["aib_db"],AIB_BASE_INDEX_DOC_PATH,"/browse.php?parent=[[ITEMID]]",array($ExportRecord));

			}

			if ($OutData["status"] != "ERROR")
			{
				$OutData["info"]["records"] = $ProcessResults;
			}

			break;

		

		// Modify record location(s)

		case "modrecord":
			$OutData = modify_links($FormData);
			break;

		// Submit OCR request for one or more items
		case "markocr":
			$ItemListString = aib_get_with_default($FormData,"item_id_list","");
			$OptRecursive = aib_get_with_default($FormData,"opt_recursive",false);
			if ($OptRecursive != false)
			{
				if (preg_match("/[Yy]/",$OptRecursive) != false)
				{
					$OptRecursive = true;
				}
				else
				{
					$OptRecursive = false;
				}
			}

			$ItemList = array();
			if ($OptRecursive == true)
			{
				$TempList = array();
				$LocalList = explode(",",$ItemListString);
				foreach($LocalList as $ItemID)
				{
					ftree_traverse($GLOBALS["aib_db"],$ItemID,$TempList);
				}

				foreach($LocalList as $ItemID)
				{
					if (isset($TempList[$ItemID]) == false)
					{
						$TempList[$ItemID] = true;
					}
				}

				foreach($TempList as $ID => $Info)
				{
					$ItemList[] = $ID;
				}

			}
			else
			{
				$ItemList = explode(",",$ItemListString);
			}

			foreach($ItemList as $ItemID)
			{
				$InfoArray = array("image_profile" => "-1", "source" => "user", "timestamp" => microtime(true));
				$ItemType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				switch($ItemType)
				{
					// Do not process these types

					case AIB_ITEM_TYPE_ARCHIVE:
					case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					case AIB_ITEM_TYPE_COLLECTION:
					case AIB_ITEM_TYPE_SUBGROUP:
						break;

					default:
						$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$ItemID);
						switch($ItemRecord["item_type"])
						{
							case FTREE_OBJECT_TYPE_FOLDER:
							case FTREE_OBJECT_TYPE_LINK:
							case FTREE_OBJECT_TYPE_LINK_FOLDER:
								break;

							// Process if real record

							default:
								aib_store_file_batch_entry($GLOBALS["aib_db"],AIB_BATCH_RECORD_TYPE_OCR_REQUEST,"$ItemID",urlencode(json_encode($InfoArray)));
								break;
						}

						break;
				}
			}

			$OutData["status"] = "OK";
			break;

		case "set_private":
			$ObjectID = aib_get_with_default($FormData,"obj_id",false);
			$Privacy = aib_get_with_default($FormData,"privacy_setting",false);
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			if ($Privacy == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPRIVACY";
				break;
			}

			if (preg_match("/[Yy]/",$Privacy) != false)
			{
				ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_PRIVATE,"Y",true);
			}
			else
			{
				ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_PRIVATE,"N",true);
			}

			break;


		case "set_visible":
			$ObjectID = aib_get_with_default($FormData,"obj_id",false);
			$Visible = aib_get_with_default($FormData,"visible_setting",false);
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			if ($Visible == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGVISIBLE";
				break;
			}

			if (preg_match("/[Yy]/",$Visible) != false)
			{
				ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_VISIBLE,"Y",true);
			}
			else
			{
				ftree_set_property($GLOBALS["aib_db"],$NewRecordID,AIB_FOLDER_PROPERTY_VISIBLE,"N",true);
			}

			break;

		case "get_privacy":
			$ObjectID = aib_get_with_default($FormData,"obj_id",false);
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$Output = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_PRIVATE);
			if ($Output == false)
			{
				$Output = "N";
			}

			$OutData["info"] = $Output;
			break;

		case "get_visible":
			$ObjectID = aib_get_with_default($FormData,"obj_id",false);
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$Output = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_VISIBLE);
			if ($Output == false)
			{
				$Output = "Y";
			}

			$OutData["info"] = $Output;
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
