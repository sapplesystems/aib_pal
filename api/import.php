<?php
//
// Create and manage records
//

include("api_util.php");

// Log debug
// ---------
function local_log_debug($Msg)
{
	$Handle = fopen("/tmp/import_api_debug.txt","a+");
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

function modify_links($FormData,$ItemID = false)
{

	$OutData = array("status" => "OK");
	if ($ItemID == false)
	{
		$ItemID = get_assoc_default($FormData,"obj_id",false);
	}

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
			$LinkClass = ftree_get_property($GLOBALS["aib_db"],$LinkRecord["item_id"],"link_class");
			if ($LinkClass != false)
			{
				if ($LinkClass == "recform")
				{
					$DeleteMap[$TempParent] = $LinkRecord["item_id"];
				}
			}
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

	$PrivacySetting = aib_get_with_default($FormData,"itemrecord_private","N");
	$VisibleSetting = aib_get_with_default($FormData,"itemrecord_visible","Y");

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
		if ($FolderResult[0] == "OK")
		{
			$TempLocalRecordID = $FolderResult[1];
			ftree_set_property($GLOBALS["aib_db"],$TempLocalRecordID,"link_class","recform",true);
			ftree_set_property($GLOBALS["aib_db"],$TempLocalRecordID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_RECORD,true);
			ftree_set_property($GLOBALS["aib_db"],$TempLocalRecordID,AIB_FOLDER_PROPERTY_VISIBLE,$VisibleSetting,true);
			ftree_set_property($GLOBALS["aib_db"],$TempLocalRecordID,AIB_FOLDER_PROPERTY_PRIVATE,$PrivacySetting,true);
		}

		// Save create attempt result.  If all was ok, the "status" field will
		// contain "OK".

		$CreateResult[] = array("parent" => $ParentID, "status" => $FolderResult[0]);
	}

	// Set "records" to the results of the inserts

	$OutData["info"]["records"] = $CreateResult;
	return($OutData);
}

// Fetch stored image so we can OCR it
// -----------------------------------
function fetch_stored_image_for_ocr($ObjectID)
{
	// Fetch the primary image to the OCR storage area

	$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ObjectID,AIB_FILE_CLASS_PRIMARY);
	if ($FileList == false)
	{
		return(false);
	}

	$Entry = $FileList[0];
	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$Entry["record_id"]);
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

	// Get the source name

	$SourceName = urldecode($SourceName);

	// Get the MIME type so we can name the output file appropriately

	$MimeType = strtolower($FileInfo["mime"]);
	$BaseName = false;
	while(true)
	{
		if (preg_match("/image\/jpeg/",$MimeType) == true)
		{
			$BaseName = $ObjectID.".jpg";
			break;
		}

		if (preg_match("/image\/gif/",$MimeType) == true)
		{
			$BaseName = $ObjectID.".gif";
			break;
		}

		if (preg_match("/image\/tif/",$MimeType) == true)
		{
			$BaseName = $ObjectID.".tif";
			break;
		}

		break;
	}

	if ($BaseName !== false)
	{
		$TargetName = AIB_OCR_FILE_QUEUE_PATH."/".$BaseName;
		system("cp -f \"$SourceName\" \"$TargetName\" > /dev/null 2> /dev/null");
		if (file_exists($TargetName) == false)
		{
			return(false);
		}

		return($BaseName);
	}

	return(false);
}

// Get first zip file name
// -----------------------
function get_zip_name($FileBatchID)
{
	// Get the name of the first ZIP file uploaded (all others are ignored)
	
	$FileList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatchID);
	if ($FileList == false)
	{
		$FileList = array();
	}

local_log_debug("File list is ".var_export($FileList,true));
	$FirstZIP = false;

	// Get first ZIP

	foreach($FileList as $FileRecord)
	{
		$FileName = urldecode($FileRecord["file_name"]);
		if (preg_match("/[\.][Zz][Ii][Pp]$/",$FileName) == false)
		{
			continue;
		}

		$FirstZIP = $FileName;
		break;
	}

	return($FirstZIP);

}


// Get the names of columns for CSV file in zip
// --------------------------------------------
function get_zip_csv_column_names($FileBatchID)
{

	$FirstZip = get_zip_name($FileBatchID);
	if ($FirstZIP == false)
	{
		return(array("status" => "ERROR", "data" => "No archive file"));
	}

	$TempCSVFileName = "/tmp/import_csv_temp_".sprintf("%d",posix_getpid()).".csv";
	$SourceZIPName = $FullName = AIB_RECORD_FILE_UPLOAD_PATH."/".$FirstZIP;

	// Get the list of files in the zip

	$ZIPFileList = import_list_zip($SourceZIPName);
	$FirstCSV = false;
	foreach($ZIPFileList as $ArchivedFileName)
	{
		if (preg_match("/[\.][Cc][Ss][Vv]$/",$ArchivedFileName) != false)
		{
			$FirstCSV = $ArchivedFileName;
			break;
		}
	}

	if ($FirstCSV == false)
	{
		return(array("status" => "ERROR", "data" => "No database file"));
	}

	// Extract the CSV to temporary file

	$ExtractResult = import_list_extract_zip_file($SourceZIPName,$FirstCSV,$TempCSVFileName);
	if ($ExtractResult == false)
	{
		return(array("status" => "ERROR", "data" => "Cannot extract database file"));
	}

	// Get the list of column names

	$ColumnNameList = import_field_names_from_csv($TempCSVFileName);
	if ($ColumnNameList == false)
	{
		return(array("status" => "ERROR", "data" => "Cannot find column names in database file"));
	}

	if (count($ColumnNameList) < 1)
	{
		return(array("status" => "ERROR", "data" => "There are no column names in database file"));
	}

	return(array("status" => "OK", "data" => $ColumnNameList));
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
		case "import":

			// Get the parent folder

			$ParentFolder = aib_get_with_default($FormData,"parent",false);

			// Get mapping

			$MappingName = aib_get_with_default($FormData,"aib_mapping_name","NULL");
			if (ltrim(rtrim($MappingName)) == "")
			{
				$MappingName = "NULL";
			}

			// Get the column fields

			$TitleColumn = aib_get_with_default($FormData,"title_source",false);
			$FileNameColumn = aib_get_with_default($FormData,"filename_source",false);
			$TagColumn = aib_get_with_default($FormData,"tag_source",false);
			$ArchiveType = aib_get_with_default($FormData,"archive_type",false);
			$ColumnFields = array();
			$NewFields = array();
			foreach($FormData as $FieldName => $FieldValue)
			{
				if (preg_match("/^target[\_]field[\_]for[\_]col[\_]/",$FieldName) != false)
				{
					$ColumnID = preg_replace("/^target[\_]field[\_]for[\_]col[\_]/","",$FieldName);
					$TargetField = $FieldValue;
					if ($TargetField == "IGNORE" || $TargetField == "NULL")
					{
						continue;
					}

					if ($TargetField == "ADD")
					{
						$NewFields["$ColumnID"] = true;
						$ColumnFields["$ColumnID"] = "ADD";
						continue;
					}

					$ColumnFields["$ColumnID"] = $TargetField;
				}
			}

			// Get the file batch

			$FileBatch = aib_get_with_default($FormData,"file_batch",false);
			$FileBatchID = $FileBatch;

			// Error if missing parent or file batch, or if there is no file name column specified, or if no title
			// column is specified

			if ($FileBatch === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFILEBATCHID";
				break;
			}

			if ($ParentFolder === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTID";
				break;
			}

			if ($FileNameColumn === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFILENAMECOL";
				break;
			}

			if ($TitleColumn === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLECOL";
				break;
			}

			if ($TagColumn === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLECOL";
				break;
			}

			// Get the ZIP file name

			$TempZipName = get_zip_name($FileBatchID);
			if ($TempZipName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOZIPNAME";
				break;
			}

			$SourceZIPName = AIB_RECORD_FILE_UPLOAD_PATH."/".$TempZipName;


			// Move the zip file to the import processing path, using a time stamp as the base
			// for file name.
			//
			// NOTE: May want to alter this, as it may require some time to move a large ZIP archive
			// if going across file systems.

			$MyPID = posix_getpid();
			$LocalTime = time();
			$BaseTargetZip = sprintf("%d%s%d%s",$LocalTime,"_",$MyPID,".zip");
			$BaseTargetSpec = sprintf("%d%s%d%s",$LocalTime,"_",$MyPID,".inf");
			$BaseTargetFlag = sprintf("%d%s%d%s",$LocalTime,"_",$MyPID,".rdy");
			$TargetZipName = AIB_IMPORT_FILE_PATH."/".$BaseTargetZip;
			$TargetSpecName = AIB_IMPORT_FILE_PATH."/".$BaseTargetSpec;
			$TargetFlagName = AIB_IMPORT_FILE_PATH."/".$BaseTargetFlag;
			system("mv \"$SourceZIPName\" \"$TargetZipName\" 2> /dev/null > /dev/null");

			// Save import spec to info file with same base name as copied zip.  Spec is saved
			// as a JSON array.

			$SpecHandle = fopen($TargetSpecName,"w");
			if ($SpecHandle == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTCREATEUPLOADSPEC";
				break;
			}

			$VisibleFlag = aib_get_with_default($FormData,"itemrecord_visible","Y");
			$PrivateFlag = aib_get_with_default($FormData,"itemrecord_private","N");
			$OCRFlag = aib_get_with_default($FormData,"itemrecord_ocr","N");

			$SaveInfo = array(
				"zipfile" => $TargetZipName,				// ZIP archive file name
				"specfile" => $TargetSpecName,				// Spec file name
				"file_batch" => $FileBatch,				// File batch ID
				"parent" => $ParentFolder,				// Parent item ID
				"column_fields" => $ColumnFields,			// Column field list
				"filename_col" => $FileNameColumn,			// Column containing file name
				"title_col" => $TitleColumn,				// Column containing title
				"tag_col" => $TagColumn,				// Column containing tags
				"visible" => $VisibleFlag,				// Visible?
				"private" => $PrivateFlag,				// Private?
				"save_mapping" => $MappingName,				// If not "NULL", save mapping using this title,
				"archive_type" => $ArchiveType,				// Archive type:  generic or one of the known commercial formats
				"ocr" => $OCRFlag,
				);

			// Encode spec info and save to spec file

			$Buffer = json_encode($SaveInfo);
			fputs($SpecHandle,$Buffer);
			fclose($SpecHandle);

			// Create "ready" flag file with same base name as copied zip.  Import batch
			// processor will catch the available ZIP and spec on the next pass.

			system("touch \"$TargetFlagName\" 2> /dev/null > /dev/null");
			system("chmod a+rwx \"$TargetFlagName\" 2> /dev/null > /dev/null");
			system("chmod a+rwx \"$TargetSpecName\" 2> /dev/null > /dev/null");
			system("chmod a+rwx \"$TargetZipName\" 2> /dev/null > /dev/null");

			// Delete file batch

			aib_delete_upload_batch_files($GLOBALS["aib_db"],$FileBatch);

			$OutData["status"] = "OK";
			$OutData["info"] = $LocalTime.",".$MyPID;
			break;

		// Get the list of available field mappings

		case "list_mappings":
			$ParentFolder = aib_get_with_default($FormData,"parent",false);
			if ($ParentFolder === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTID";
				break;
			}

			$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
			if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
			{
				$LocalArchive = $ArchiveInfo["archive"]["item_id"];
				$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDARCHIVE";
				break;
			}


			$MappingList = import_list_mappings($GLOBALS["aib_db"],$LocalArchiveGroup,"I");
			if ($MappingList === false)
			{
				$OutData["info"] = array();
			}
			else
			{
				$OutData["info"] = $MappingList;
				$OutData["status"] = "OK";
			}

			break;


		// Get a field mapping definition

		case "get_mapping":
			$ParentFolder = aib_get_with_default($FormData,"parent",false);
			$MappingTitle = aib_get_with_default($FormData,"map_title",false);
			$OutFormat = aib_get_with_default($FormData,"format","normal");
			if ($ParentFolder === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTID";
				break;
			}

			if ($MappingTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGMAPTITLE";
				break;
			}

			$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
			if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
			{
				$LocalArchive = $ArchiveInfo["archive"]["item_id"];
				$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "Cannot determine archive and/or archive group";
				break;
			}


			$MappingInfo = import_get_mapping($GLOBALS["aib_db"],$LocalArchiveGroup,"I",$MappingTitle);
			if ($MappingInfo === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTFOUND";
			}
			else
			{
				$OutData["status"] = "OK";
				switch($OutFormat)
				{
					case "normal":
						$OutData["info"] = $MappingInfo;
						break;

					case "field":
						$OutData["info"] = array();
						foreach($MappingInfo as $ColNumber => $TargetField)
						{
							if ($ColNumber != "__title")
							{
								$OutData["info"][] = array("target_field_for_column_".$ColNumber => $TargetField);
							}
						}

						break;

					default:
						$OutData["status"] = "ERROR";
						$OutData["info"] = "BADOUTFORMAT";
						break;
				}

			}

			break;


		// Save a mapping definition

		case "save_mapping":
			$ParentFolder = aib_get_with_default($FormData,"parent",false);
			$MappingTitle = aib_get_with_default($FormData,"map_title",false);
			$MapInfoString = aib_get_with_default($FormData,"map_info",false);
			if ($ParentFolder === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTID";
				break;
			}

			if ($MappingTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGMAPTITLE";
				break;
			}

			if ($MapInfoString === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGMAPDATA";
				break;
			}

			$MapInfo = json_decode($MapInfoString,true);
			$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
			if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
			{
				$LocalArchive = $ArchiveInfo["archive"]["item_id"];
				$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADARCHIVEGROUP";
				break;
			}


			import_set_mapping($GLOBALS["aib_db"],$LocalArchiveGroup,"I",$MappingTitle,$MapInfo);
			$OutData["info"] = $MappingTitle;
			$OutData["status"] = "OK";
			break;

		// Delete a mapping definition

		case "delete_mapping":
			$ParentFolder = aib_get_with_default($FormData,"parent",false);
			$MappingTitle = aib_get_with_default($FormData,"map_title",false);
			if ($ParentFolder === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTID";
				break;
			}

			if ($MappingTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGMAPTITLE";
				break;
			}

			$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
			if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
			{
				$LocalArchive = $ArchiveInfo["archive"]["item_id"];
				$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADARCHIVEGROUP";
				break;
			}


			import_delete_mapping($GLOBALS["aib_db"],$LocalArchiveGroup,"I",$MappingTitle);
			$OutData["info"] = $MappingTitle;
			$OutData["status"] = "OK";
			break;


		// Rename a mapping definition

		case "rename_mapping":
			$ParentFolder = aib_get_with_default($FormData,"parent",false);
			$MappingTitle = aib_get_with_default($FormData,"map_title",false);
			$NewMappingTitle = aib_get_with_default($FormData,"new_map_title",false);
			if ($ParentFolder === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENTID";
				break;
			}

			if ($MappingTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGMAPTITLE";
				break;
			}

			if ($NewMappingTitle === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNEWTITLE";
				break;
			}

			$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
			if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
			{
				$LocalArchive = $ArchiveInfo["archive"]["item_id"];
				$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADARCHIVEGROUP";
				break;
			}


			if (import_rename_mapping($GLOBALS["aib_db"],$LocalArchiveGroup,"I",$MappingTitle,$NewMappingTitle) == true)
			{
				$OutData["status"] = "OK";
			}
			else
			{
				$OutData["status"] = "ERROR";
			}

			$OutData["info"] = $NewMappingTitle;
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
