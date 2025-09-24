<?php
//
// Field management
//

include("api_util.php");

// Log debug
// ---------
function aib_field_log_debug($Msg)
{
	$Handle = fopen("/tmp/aib_field_log_debug.txt","a+");
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

		case "field_create":

			// Get field information

			$FieldTitle = get_assoc_default($FormData,"field_title",false);
			$FieldDataType = get_assoc_default($FormData,"field_data_type",FTREE_FIELD_TYPE_TEXT);
			$FieldFormat = get_assoc_default($FormData,"field_format","");
			$FieldSize = get_assoc_default($FormData,"field_size","");
			$FieldOwnerType = get_assoc_default($FormData,"field_owner_type",FTREE_OWNER_TYPE_SYSTEM);
			$FieldOwnerID = get_assoc_default($FormData,"field_owner_id",FTREE_USER_SUPERADMIN);
			$FieldSymbolicName = get_assoc_default($FormData,"field_symbolic_name","NULL");
			$FieldClass = get_assoc_default($FormData,"field_class",false);

			if ($FieldTitle == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLE";
				break;
			}

			switch($FieldClass)
			{
				case "user":
				case "admin":
				case "subadmin":
				case "public":
					$FieldOwnerType = FTREE_OWNER_TYPE_USER;
					break;

				case "system":
					$FieldOwnerType = FTREE_OWNER_TYPE_SYSTEM;
					$FieldOwnerID = -1;
					$FieldSymbolicName = "NULL";
					break;

				case "archive":
				case "archive_group":
				case "record":
				case "subgroup":
				case "collection":
				case "item":
					$FieldOwnerType = FTREE_OWNER_TYPE_ITEM;
					break;

				case "form":
					$FieldOwnerType = FTREE_OWNER_TYPE_FORM;
					break;

				case "recommended":
					$FieldOwnerType = FTREE_OWNER_TYPE_RECOMMENDED;
					$FieldOwnerID = -1;
					break;

				case "traditional":
					$FieldOwnerType = FTREE_OWNER_TYPE_SYSTEM;
					$FieldOwnerID = -2;
					break;

				case "predef":
					$FieldOwnerType = FTREE_OWNER_TYPE_SYSTEM;
					$FieldOwnerID = -1;
					if ($FieldSymbolicName == "NULL")
					{
						$FieldSymbolicName = preg_replace("/[^A-Za-z0-9\_]/","_",$FieldTitle);
					}

					break;

				default:
					break;
			}

			$NewID = ftree_field_create_field($GLOBALS["aib_db"],$FieldTitle,$FieldDataType,$FieldFormat,$FieldSize,
				$FieldOwnerType,$FieldOwnerID,$FieldSymbolicName);
			if ($NewID !== false)
			{
				$OutData["status"] = "OK";
				$OutData["info"] = $NewID;
				break;
			}

			$OutData["status"] = "ERROR";
			$OutData["info"] = "CANNOTCREATEFIELD";
			break;


		case "field_modify":

			// Get field information

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			$ModifyList = array(
				"field_title" => "title",
				"field_data_type" => "data_type",
				"field_format" => "field_format",
				"field_size" => "field_size",
				"field_owner_type" => "owner_type",
				"field_owner_id" => "owner_id"
			);

			$ModifyData = array();
			foreach($ModifyList as $FormName => $UpdateName)
			{
				if (isset($FormData[$FormName]) == true)
				{
					$ModifyData[$UpdateName] = $FormData[$FormName];
				}
			}

			if (ftree_field_modify_field($GLOBALS["aib_db"],$FieldID,$ModifyData) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTUPDATE";
			}
			else
			{
				$OutData["status"] = "OK";
			}

			break;


		case "field_del":

			// Get field information

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			// Get field definition.  If a system field that is reserved, prevent deletion.

			$Record = ftree_field_get_field($GLOBALS["aib_db"],$FieldID);
			$SymbolicName = $Record["field_symbolic_name"];
			if ($SymbolicName != "" && $SymbolicName != "NULL")
			{
				switch($SymbolicName)
				{
					case AIB_PREDEF_FIELD_OCRTEXT:
					case AIB_PREDEF_FIELD_COUNTRY:
					case AIB_PREDEF_FIELD_STATE:
					case AIB_PREDEF_FIELD_COUNTY:
					case AIB_PREDEF_FIELD_CITY:
					case AIB_PREDEF_FIELD_POSTAL:
					case AIB_PREDEF_FIELD_DESCRIPTION:
					case AIB_PREDEF_FIELD_COMMENT_TEXT:
					case AIB_PREDEF_FIELD_COMMENT:
					case AIB_PREDEF_FIELD_OCR_TEXT:
					case AIB_PREDEF_FIELD_ALTITUDE:
					case AIB_PREDEF_FIELD_LATITUDE:
					case AIB_PREDEF_FIELD_LONGITUDE:
					case AIB_PREDEF_FIELD_CREATOR:
					case AIB_PREDEF_FIELD_DATE:
					case AIB_PREDEF_FIELD_PROVENANCE:
					case AIB_PREDEF_FIELD_LOCATION:
					case AIB_PREDEF_FIELD_INFOTEXT:
					case AIB_PREDEF_FIELD_URL:
						$OutData["status"] = "ERROR";
						$OutData["info"] = "CANNOTDELETESYSTEMFIELD";
						break;

					default:
						break;
				}
			}

			if ($OutData["status"] == "ERROR")
			{
				break;
			}

			ftree_field_delete_field($GLOBALS["aib_db"],$FieldID);
			$OutData["status"] = "OK";
			break;


		case "field_get":

			// Get field information

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			$Record = ftree_field_get_field($GLOBALS["aib_db"],$FieldID);
			if ($Record == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADFIELD";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array("records" => array());
			$OutData["info"]["records"][] = array(
				"field_id" => $FieldID,
				"field_title" => $Record["field_title"],
				"field_data_type" => $Record["field_data_type"],
				"field_format" => $Record["field_format"],
				"field_size" => $Record["field_size"],
				"field_owner_type" => $Record["field_owner_type"],
				"field_owner_id" => $Record["field_owner_id"],
				"field_symbolic_name" => $Record["field_symbolic_name"],
				);
			break;


		case "field_list":

			// List all fields defined, or narrow to a specific owner and owner type

			$UserID = get_assoc_default($FormData,"field_user",false);
			$OwnerType = get_assoc_default($FormData,"field_owner_type",false);
			$OwnerID = get_assoc_default($FormData,"field_owner_id",false);
			$FilterDisabled = get_assoc_default($FormData,"filter_disabled","N");
			if (preg_match("/^[Yy]/",$FilterDisabled) != false)
			{
				$FilterDisabled = true;
			}
			else
			{
				$FilterDisabled = false;
			}

			$RecordList = ftree_list_fields($GLOBALS["aib_db"],$UserID,$OwnerType,$OwnerID,$FilterDisabled);
			if ($RecordList == false)
			{
				$RecordList = array();
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($RecordList as $LocalRecord)
			{
				$OutData["info"]["records"][] = $LocalRecord;
			}

			break;

		case "field_enable":
			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			ftree_field_disable($GLOBALS["aib_db"],$FieldID,false);
			break;
				
		case "field_disable":
			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			ftree_field_disable($GLOBALS["aib_db"],$FieldID,true);
			break;
				
		case "form_create":

			// Get field information

			$FormName = get_assoc_default($FormData,"form_name",false);
			$FormOwnerID = get_assoc_default($FormData,"form_owner_id",FTREE_USER_SUPERADMIN);
			$FormOwnerType = get_assoc_default($FormData,"form_owner_type",FTREE_OWNER_TYPE_SYSTEM);

			if ($FormName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMNAME";
				break;
			}

			$NewID = ftree_field_create_form($GLOBALS["aib_db"],$FormName,$FormOwnerID,$FormOwnerType);
			if ($NewID !== false)
			{
				$OutData["status"] = "OK";
				$OutData["info"] = $NewID;
				break;
			}

			$OutData["status"] = "ERROR";
			$OutData["info"] = "CANNOTCREATEFORM";
			break;


		case "form_modify":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$ModifyList = array(
				"form_name" => "name",
				"form_owner_type" => "owner_type",
				"form_owner_id" => "owner_id"
			);

			$ModifyData = array();
			foreach($ModifyList as $FormName => $UpdateName)
			{
				if (isset($FormData[$FormName]) == true)
				{
					$ModifyData[$UpdateName] = $FormData[$FormName];
				}
			}

			if (ftree_field_modify_form($GLOBALS["aib_db"],$FormID,$ModifyData) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTUPDATE";
			}
			else
			{
				$OutData["status"] = "OK";
				$OutData["info"] = "";
			}

			break;


		case "form_get":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$FormRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormID);
			if ($FormRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETFORM";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			$OutData["info"]["records"][] = array(
				"form_id" => $FormID,
				"form_name" => $FormRecord["form_title"],
				"form_owner_id" => $FormRecord["form_owner"],
				"form_owner_type" => $FormRecord["form_owner_type"],
			);

			break;

		case "form_list":

			// Get form information

			$FormOwnerID = get_assoc_default($FormData,"form_owner_id",false);
			$FormOwnerType = get_assoc_default($FormData,"form_owner_type",false);
			$RecordList = ftree_list_forms($GLOBALS["aib_db"],$FormOwnerID,$FormOwnerType);
			if ($RecordList == false)
			{
				$RecordList = array();
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array("records" => array());
			foreach($RecordList as $LocalRecord)
			{
				$OutData["info"]["records"][] = $LocalRecord;
			}

			break;


		case "form_add_field":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			$SortOrder = get_assoc_default($FormData,"field_sort_order",false);
			$AltTitle = get_assoc_default($FormData,"field_alt_title",false);
			$Result = ftree_field_add_field_to_form($GLOBALS["aib_db"],
				$FormID,$FieldID,$SortOrder,$AltTitle);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTADDFIELDTOFORM";
			}
			else
			{
				$OutData["status"] = "OK";
			}

			break;

		case "form_modify_field":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			$SortOrder = get_assoc_default($FormData,"field_sort_order",false);
			$AltTitle = get_assoc_default($FormData,"field_alt_title",false);
			$Settings = array();
			if ($SortOrder != false)
			{
				$Settings["sort_order"] = $SortOrder;
			}

			if ($AltTitle != false)
			{
				$Settings["alt_title"] = $AltTitle;
			}

			$Result = ftree_field_alter_form_field($GLOBALS["aib_db"],$FormID,$FieldID,$Settings);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTMODIFYFIELD";
			}
			else
			{
				$OutData["status"] = "OK";
			}

			break;

		case "form_reorder_fields":
			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			ftree_form_field_resequence($GLOBALS["aib_db"],$FormID);
			$OutData["status"] = "OK";
			break;


		case "form_list_fields":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$Results = ftree_field_get_form_fields($GLOBALS["aib_db"],$FormID);
			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array();
			foreach($Results as $ResultRecord)
			{
				$OutData["info"]["records"][] = array(
					"form_id" => $FormID,
					"form_field_id" => $ResultRecord["form_record"]["field_id"],
					"form_field_sort_order" => $ResultRecord["form_record"]["field_sort_order"],
					"form_field_alt_title" => $ResultRecord["form_record"]["field_alt_title"],
					"field_title" => $ResultRecord["field_record"]["field_title"],
					"field_data_type" => $ResultRecord["field_record"]["field_data_type"],
					"field_format" => $ResultRecord["field_record"]["field_format"],
					"field_size" => $ResultRecord["field_record"]["field_size"],
					"field_owner_type" => $ResultRecord["field_record"]["field_owner_type"],
					"field_owner_id" => $ResultRecord["field_record"]["field_owner_id"],
					"field_symbolic_name" => $ResultRecord["field_record"]["field_symbolic_name"],
				);
			}

			break;


		case "form_del_field":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			ftree_field_del_form_field($GLOBALS["aib_db"],$FormID,$FieldID);
			$OutData["status"] = "OK";
			break;


		case "form_del":

			// Get form information

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			ftree_field_delete_form($GLOBALS["aib_db"],$FormID);
			$OutData["status"] = "OK";
			break;


		case "set_allow_edit_field":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$AllowList = get_assoc_default($FormData,"field_list",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($AllowList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDLIST";
				break;
			}

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "USERNOTFOUND";
				break;
			}

			$AllowString = urlencode($AllowList);
			ftree_set_user_prop($GLOBALS["aib_db"],$UserID,AIB_USER_PROPERTY_ALLOWED_FIELDS,$AllowString);
			$OutData["status"] = "OK";
			$OutData["info"] = $UserID;
			break;

		case "set_not_allow_edit_field":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$AllowList = get_assoc_default($FormData,"field_list",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($AllowList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDLIST";
				break;
			}

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "USERNOTFOUND";
				break;
			}

			$AllowString = urlencode($AllowList);
			ftree_set_user_prop($GLOBALS["aib_db"],$UserID,AIB_USER_PROPERTY_NOTALLOWED_FIELDS,$AllowString);
			$OutData["status"] = "OK";
			$OutData["info"] = $UserID;
			break;

		case "get_allowed_edit":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$AllowList = get_assoc_default($FormData,"field_list",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($AllowList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDLIST";
				break;
			}

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "USERNOTFOUND";
				break;
			}

			$Property = ftree_get_user_prop($GLOBALS["aib_db"],$UserID,AIB_USER_PROPERTY_ALLOWED_FIELDS);
			if ($Property == false)
			{
				$Property = "";
			}
			else
			{
				$Property = urldecode($Property);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $Property;
			break;


		case "get_not_allowed_edit":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$AllowList = get_assoc_default($FormData,"field_list",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($AllowList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDLIST";
				break;
			}

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "USERNOTFOUND";
				break;
			}

			$Property = ftree_get_user_prop($GLOBALS["aib_db"],$UserID,AIB_USER_PROPERTY_NOTALLOWED_FIELDS);
			if ($Property == false)
			{
				$Property = "";
			}
			else
			{
				$Property = urldecode($Property);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $Property;
			break;

		case "clear_allowed":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$AllowList = get_assoc_default($FormData,"field_list",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($AllowList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDLIST";
				break;
			}

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "USERNOTFOUND";
				break;
			}

			ftree_delete_user_prop($GLOBALS["aib_db"],$UserID,AIB_USER_PROPERTY_ALLOWED_FIELDS);
			$OutData["status"] = "OK";
			$OutData["info"] = $UserID;
			break;


		case "clear_not_allowed":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$AllowList = get_assoc_default($FormData,"field_list",false);
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			if ($AllowList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDLIST";
				break;
			}

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "USERNOTFOUND";
				break;
			}

			ftree_delete_user_prop($GLOBALS["aib_db"],$UserID,AIB_USER_PROPERTY_NOTALLOWED_FIELDS);
			$OutData["status"] = "OK";
			$OutData["info"] = $UserID;
			break;

		case "get_named_fields":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$FieldListString = get_assoc_default($FormData,"field_list",false);
			$FieldList = array();
			if ($FieldListString !== false)
			{
				$FieldList = json_decode($FieldListString,true);
			}

			// Get item.  If not found, error.

			$ItemInfo = ftree_get_item($GLOBALS["aib_db"],$ItemID);
			if ($ItemInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "ITEMNOTFOUND";
				break;
			}

			$OutList = array();

			// If no item, return field definitions for fields with symbolic names

			$TempFieldDefs = ftree_list_symbolic_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,-1);
			$FieldNameMap = array();
			$FieldIDMap = array();
			foreach($TempFieldDefs as $FieldDef)
			{
				$FieldNameMap[$FieldDef["field_symbolic_name"]] = $FieldDef;
				$FieldIDMap[$FieldDef["field_id"]] = $FieldDef;
			}

			if ($ItemID === false)
			{
				// If no fields specified, return all

				if ($FieldListString == false)
				{
					foreach($TempFieldDefs as $FieldDef)
					{
						$OutList[] = array("def" => $FieldDef, "value" => "");
					}
				}
				else
				{
					foreach($FieldList as $FieldName)
					{
						if (isset($FieldNameMap[$FieldName]) == true)
						{
							$OutList[] = array("def" => $FieldDef, "value" => "");
						}
					}
				}
			}
			else
			{
				// If no fields specified, return all for item.  Else, those specified.

				$FieldData = ftree_field_get_item_fields($GLOBALS["aib_db"],$ItemID);
				if ($FieldListString == false)
				{
					foreach($FieldData as $LocalFieldID => $FieldValue)
					{
						if (isset($FieldIDMap[$LocalFieldID]) == true)
						{
							$OutList[] = array("def" => $FieldIDMap[$LocalFieldID], "value" => $FieldValue);
						}
					}
				}
				else
				{
					foreach($FieldList as $FieldName)
					{
						if (isset($FieldNameMap[$FieldName]) == true)
						{
							$LocalID = $FieldNameMap[$FieldName]["field_id"];
							if (isset($FieldData[$LocalID]) == true)
							{
								$OutList[] = array("def" => $FieldNameMap[$FieldName], "value" => $FieldData[$LocalID]);
							}
						}
					}
				}
			}

			$OutData["info"]["records"] = $OutList;
			break;

		// Store fields.  Field list input is JSON:
		//
		//	[
		//		{
		//			"name" : "symbolic name",
		//			"value" : "value",
		//		}
		//		....
		//	]
		case "store_named_fields":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMID";
				break;
			}

			// Get item.  If not found, error.

			$ItemInfo = ftree_get_item($GLOBALS["aib_db"],$ItemID);
			if ($ItemInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "ITEMNOTFOUND";
				break;
			}


			$FieldListString = get_assoc_default($FormData,"field_list",false);
			$FieldList = array();
			if ($FieldListString !== false)
			{
				$TempFieldDefs = ftree_list_symbolic_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,-1);
				$FieldNameMap = array();
				foreach($TempFieldDefs as $FieldDef)
				{
					$FieldNameMap[$FieldDef["field_symbolic_name"]] = $FieldDef;
				}

				$TempValues;
				$FieldList = json_decode($FieldListString,true);
				foreach($FieldList as $FieldRecord)
				{
					$FieldValue = $FieldRecord["value"];
					$FieldName = $FieldRecord["name"];
					if (isset($FieldNameMap[$FieldName]) == true)
					{
						$LocalDef = $FieldNameMap[$FieldName];
						$TempValues[$LocalDef["field_id"]] = $FieldValue;
					}
				}

				ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemID,$TempValues,false);
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDDATA";
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
