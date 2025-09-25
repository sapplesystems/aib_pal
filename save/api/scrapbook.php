<?php
//
// Scrapbook browsing functions
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
		// Create a scrapbook for a user

		case "scrpbk_new":

			// Get fields

			$Title = get_assoc_default($FormData,"title",false);
			if ($Title == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLE";
				break;
			}

			$UserID = get_assoc_default($FormData,"user_id",-1);
			if ($UserID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			$GroupID = get_assoc_default($FormData,"group_id",-1);

			// Get user profile and determine home folder

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADUSER";
				break;
			}

			$HomeFolder = $UserProfile["user_top_folder"];

			// First, see if scrapbook exists.  If so, error.

			$PathSpec = join("\t",array(
				"F:".AIB_PREDEF_FOLDER_NAME_SCRAPBOOK_SET,
				"F:".urlencode($Title),
				));
			$ScrapbookParent = ftree_get_object_by_parent_path($GLOBALS["aib_db"],$PathSpec,$HomeFolder,"\t");
			if ($ScrapbookParent !== false && $ScrapbookParent["item_user_id"] == $UserID)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "DUPLICATE";
				break;
			}

			// Create scrapbook by path

			$PathDef = join("\t",array(
				"F:".AIB_PREDEF_FOLDER_NAME_SCRAPBOOK_SET,
				"F:".urlencode($Title)
				));
			$ScrapbookInfo = ftree_create_object_by_path($GLOBALS["aib_db"],$UserID,$GroupID,$HomeFolder,$PathDef,"\t");
			if ($ScrapbookInfo[0] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = join(";",$ScrapbookInfo);
				break;
			}

			ftree_set_property($GLOBALS["aib_db"],$ScrapbookInfo[1],AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_SCRAPBOOK,true);

			// Return new scrapbook folder ID

			$OutData["info"] = $ScrapbookInfo[1];
			break;
			

		// Update scrapbook properties (title only)

		case "scrpbk_upd":

			// Get fields

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$NewTitle = get_assoc_default($FormData,"new_title",false);
			if ($ItemID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			if ($NewTitle == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNEWTITLE";
				break;
			}

			$Result = ftree_rename($GLOBALS["aib_db"],$ItemID,urlencode($NewTitle),false);
			$OutData["status"] = "OK";
			$OutData["info"] = "";
			break;

		// Remove scrapbook

		case "scrpbk_del":

			// Get fields

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			if ($ItemID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			ftree_delete($GLOBALS["aib_db"],$Scrapbook["item_id"],true);
			$OutData["status"] = "OK";
			$OutData["info"] = "";
			break;


		// List scrapbooks for user

		case "scrpbk_lst":
			$UserID = get_assoc_default($FormData,"user_id",-1);
			if ($UserID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSER";
				break;
			}

			// Get user profile and determine home folder

			$UserProfile = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserProfile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADUSER";
				break;
			}

			$HomeFolder = $UserProfile["user_top_folder"];
			$OptionSortOrder = strtoupper(get_assoc_default($FormData,"opt_sort","TITLE"));
			$OptionGetProperties = strtoupper(get_assoc_default($FormData,"opt_get_property","N"));
			$OptionGetFiles = strtoupper(get_assoc_default($FormData,"opt_get_files","N"));
			$OptionGetFields = strtoupper(get_assoc_default($FormData,"opt_get_field","N"));
			$OptionGetThumb = strtoupper(get_assoc_default($FormData,"opt_get_thumb","N"));
			$OptionGetPrimary = strtoupper(get_assoc_default($FormData,"opt_get_primary","N"));
			$OptionGetFirstThumb = strtoupper(get_assoc_default($FormData,"opt_get_first_thumb","N"));
			$OptionFilterType = strtoupper(get_assoc_default($FormData,"opt_filter_type",false));
			$OptionSelectType = strtoupper(get_assoc_default($FormData,"opt_select_type",false));
			$OptionDerefLinks = strtoupper(get_assoc_default($FormData,"opt_deref_links","N"));
			if ($OptionDerefLinks == "N")
			{
				$OptionDerefLinks = false;
			}
			else
			{
				$OptionDerefLinks = true;
			}

			if (preg_match("/[Y]/",$OptionGetProperties) != false)
			{
				$GetPropertyFlag = true;
			}
			else
			{
				$GetPropertyFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetFiles) != false)
			{
				$GetFilesFlag = true;
			}
			else
			{
				$GetFilesFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetFields) != false)
			{
				$GetFieldFlag = true;
			}
			else
			{
				$GetFieldFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetThumb) != false)
			{
				$GetThumbFlag = true;
			}
			else
			{
				$GetThumbFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetPrimary) != false)
			{
				$GetPrimaryFlag = true;
			}
			else
			{
				$GetPrimaryFlag = false;
			}

			$SortOrdersAllowed = array("ID" => true, "TITLE" => true,"STPA" => true);
			if (isset($SortOrdersAllowed[$OptionSortOrder]) == false)
			{
				$OptionSortOrder = "TITLE";
			}

			if ($OptionSortOrder == "TITLE")
			{
				$SortTitleFlag = true;
				$SortIDFlag = false;
			}
			else
			{
				$SortTitleFlag = false;
				$SortIDFlag = true;
			}

			if (preg_match("/[Y]/",$OptionGetFirstThumb) != false)
			{
				$GetFirstThumbFlag = true;
			}
			else
			{
				$GetFirstThumbFlag = false;
			}

			$OutData["info"] = array("records" => array());
			$ResultList = ftree_list_child_objects($GLOBALS["aib_db"],$HomeFolder,$UserID,false,FTREE_OBJECT_TYPE_FOLDER,false,$SortTitleFlag,$SortIDFlag);
			if ($CountOnlyFlag == true)
			{
				if ($ResultList[0] == "ERROR")
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = $ResultList[1];
					break;
				}

				$OutData["status"] = "OK";
				$OutData["info"] = $ResultList[0];
				break;
			}

			$TotalCount = 0;
			if ($ResultList != false)
			{
				// If sorting by STP name, use that instead

				if ($OptionSortOrder == "STPA")
				{
					// Create a map of records, where the key is the sort name

					$LocalMap = array();
					foreach($ResultList as $TempResultRecord)
					{
						// Get sort name; if none present, use item title followed by ID

						$STPSort = ftree_get_property($GLOBALS["aib_db"],$ResultRecord["item_id"],"stp:sort_name");
						if ($STPSort == false)
						{
							$STPSort = urldecode($TempResultRecord["item_title"]).",".$TempResultRecord["item_id"];
						}
						else
						{
							// Suffix the sort name with the item ID so we don't have name collisions (just in case)

							$STPSort .= ",".$TempResultRecord["item_id"];
						}

						$LocalMap[$STPSort] = $TempResultRecord;
					}

					// Get key list of map and sort

					$KeyList = array_keys($LocalMap);
					sort($KeyList);

					// Rebuild result list in the sorted key order

					$ResultList = array();
					foreach($KeyList as $TempKey)
					{
						$ResultList[] = $LocalMap[$TempKey];
					}
				}

				foreach($ResultList as $TempResultRecord)
				{
					// Determine if we're dereferencing links

					$IsLink = false;
					$LinkType = false;
					$LinkData = array();
					if ($OptionDerefLinks == false)
					{
						$ResultRecord = $TempResultRecord;
					}
					else
					{
						// Dereference based on link source if the record is a link

						if ($TempResultRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
						{
							$LinkType = $TempResultRecord["item_source_type"];
							$LinkData["link_type"] = $LinkType;
							switch($TempResultRecord["item_source_type"])
							{
								// Internal (AIB) link.  Fetch the linked item record.

								case FTREE_SOURCE_TYPE_LINK:
									$IsLink = true;
									$LinkTarget = $TempResultRecord["item_ref"];
									$ResultRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$LinkTarget);
									break;

								// STP Archive link

								case FTREE_SOURCE_TYPE_STPARCHIVE:
									$IsLink = true;
									$LinkTarget = $TempResultRecord["item_ref"];
									$LinkInfo = json_decode(urldecode($TempResultRecord["item_source_info"]),true);
									switch($LinkInfo["type"])
									{
										// Edition
										case FTREE_STP_LINK_EDITION:
											$LinkData["stp_link_type"] = $LinkInfo["type"];
											$LinkData["stp_url"] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
												$LinkInfo["year"]."/".stp_archive_month_name($LinkInfo["mon"])." ".
												$LinkInfo["day"]."/";
											$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
												$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
											break;

										// Page
										case FTREE_STP_LINK_PAGE:
											$LinkData["stp_link_type"] = $LinkInfo["type"];
											$LinkData["stp_url"] = "www.".STP_ARCHIVE_DOMAIN."/aib_page.php?edition=".
												$LinkInfo["ed"]."&page=".$LinkInfo["pg"];
											$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
												$LinkInfo["ed"]."&page=".$LinkInfo["pg"]."&paper=".$LinkInfo["paper"];
											break;

										// Year
										case FTREE_STP_LINK_YEAR:
											$LinkData["stp_link_type"] = $LinkInfo["type"];
											$LinkData["stp_url"] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
												$LinkInfo["year"];
											$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
												$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
											break;

										default:
											break;
									}

									$ResultRecord = $TempResultRecord;
									break;

								case FTREE_SOURCE_TYPE_URL:
									$IsLink = true;
									$LinkData["link_url"] = $LinkInfo["url"];
									$ResultRecord = $TempResultRecord;
									break;

								default:
									$ResultRecord = $TempResultRecord;
									break;

							}

							if ($ResultRecord == false)
							{
								continue;
							}

						}
						else
						{
							$ResultRecord = $TempResultRecord;
						}
					}

					// Determine the record type (collection, archive, etc.)

					$FormID = ftree_field_get_item_form($GLOBALS["aib_db"],$ResultRecord["item_id"]);
					if ($FormID == false)
					{
						$FormID = "";
					}

					$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
					if ($OptionFilterType != false)
					{
						if ($EntryTypeProperty == $OptFilterType)
						{
							continue;
						}
					}

					if ($OptionSelectType != false)
					{
						if ($EntryPropertyType != $OptSelectType)
						{
							continue;
						}
					}

					if ($CountOnlyFlag == true)
					{
						$TotalCount++;
						continue;
					}

					$LocalEntryType = "IT";
					if (isset($EntryTypes[$EntryTypeProperty]) == true)
					{
						$LocalEntryType = $EntryTypes[$EntryTypeProperty];
					}
					else
					{
						$LocalEntryType = $EntryTypeProperty;
					}

					$ItemPropertySet = array();
					if ($GetPropertyFlag == true)
					{
						$PropertyList = ftree_list_properties($GLOBALS["aib_db"],$ResultRecord["item_id"]);
						if ($PropertyList != false)
						{
							foreach($PropertyList as $PropertyName => $PropertyValue)
							{
								$LocalName = urldecode($PropertyName);
								$LocalValue = urldecode($PropertyValue);
								if (isset($BlockedProperties[$LocalName]) == true)
								{
									continue;
								}

								$ItemPropertySet[$LocalName] = $LocalValue;
							}
						}
					}

					$ItemFieldSet = array();
					if ($GetFieldFlag == true)
					{
						$FieldList = get_item_fields_in_form_order($GLOBALS["aib_db"],$ResultRecord["item_id"],$FormID);
//						$FieldList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ResultRecord["item_id"]);
						if ($FieldList != false)
						{
							foreach($FieldList as $FieldInfo)
							{
								$LocalDef = $FieldInfo["def"];
								$FieldID = $LocalDef["field_id"];
								$LocalType = "TEXT";
								if (isset($FieldDataTypeDesc[$LocalDef["field_data_type"]]) == true)
								{
									$LocalType = $FieldDataTypeDesc[$LocalDef["field_data_type"]];
								}

								$TempRecord = array(
									"field_id" => $FieldID,
									"field_value" => urldecode($FieldInfo["value"]),
									"field_title" => urldecode($LocalDef["field_title"]),
									"field_size" => $LocalDef["field_size"],
									"field_data_type" => $LocalType,
									"field_format" => urldecode($LocalDef["field_format"]),
									"form_id" => $FormID,
									);
								$ItemFieldSet[] = $TempRecord;
							}
						}
					}

					$ItemFileList = array();
					if ($GetFilesFlag == true)
					{
						$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ResultRecord["item_id"]);
						if ($FileList == false)
						{
							$FileList = array();
						}

						foreach($FileList as $FileRecord)
						{
							$StoredSize = stored_file_size($FileRecord);
							$ItemFileList[] = array(
							"file_id" => $FileRecord["record_id"],
							"file_original_name" => urldecode($FileRecord["file_original_name"]),
							"file_mime_type" => urldecode($FileRecord["file_mime_type"]),
							"file_stored_stamp" => $FileRecord["file_stored_stamp"],
							"file_stored_string" => date("Y.m.d.H.i.s",$FileRecord["file_stored_stamp"]),
							"file_type" => $FileRecord["file_class"],
							"file_size" => $StoredSize,
							);
						}
					}

					$ThumbData = array("id" => -1, "data" => "", "mime" => "");
					$PrimaryData = array("id" => -1, "data" => "", "mime" => "");
					if ($GetThumbFlag == true)
					{
						$LocalThumbData = get_item_image_data($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FILE_CLASS_THUMB);
						if ($LocalThumbData != false)
						{
							$ThumbData = $LocalThumbData;
						}
					}

					if ($GetPrimaryFlag == true)
					{
						$LocalPrimaryData = get_item_image_data($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FILE_CLASS_THUMB);
						if ($LocalPrimaryData != false)
						{
							$PrimaryData = $LocalPrimaryData;
						}
					}

					if ($GetFirstThumbFlag == true)
					{
						$FirstThumbID = aib_get_first_record_thumb($GLOBALS["aib_db"],$ResultRecord["item_id"]);
					}
					else
					{
						$FirstThumbID = "";
					}

					$OutRecord = array(
						"properties" => $ItemPropertySet,
						"fields" => $ItemFieldSet,
						"files" => $ItemFileList,
						"item_id" => $ResultRecord["item_id"],
						"item_tree_type" => $ResultRecord["item_type"],
						"item_type" => $LocalEntryType,
						"item_title" => urldecode($ResultRecord["item_title"]),
						"item_ref" => $ResultRecord["item_ref"],
						"item_source_type" => $ResultRecord["item_source_type"],
						"item_source_info" => $ResultRecord["item_source_info"],
						"item_create_stamp" => $ResultRecord["item_create_stamp"],
						"item_create_string" => date("Y.m.d.H.i.s",$ResultRecord["item_create_stamp"]),
						"thumb_id" => $ThumbData["id"],
						"thumb_data" => $ThumbData["data"],
						"thumb_mime" => $ThumbData["mime"],
						"primary_id" => $PrimaryData["id"],
						"primary_data" => $PrimaryData["data"],
						"primary_mime" => $PrimaryData["mime"],
						"first_thumb" => $FirstThumbID,
						"form_id" => $FormID,
					);

					if ($IsLink == true)
					{
						$OutRecord["is_link"] = "Y";
						$OutRecord["link_id"] = $TempResultRecord["item_id"];
						foreach($LinkData as $LinkDataKey => $LinkDataInfo)
						{
							$OutRecord[$LinkDataKey] = $LinkDataInfo;
						}
					}
					else
					{
						$OutRecord["is_link"] = "N";
					}

					$OutData["info"]["records"][] = $OutRecord;
				}
			}

			if ($CountOnlyFlag == true)
			{
				$OutData["status"] = "OK";
				$OutData["info"] + $TotalCount;
			}

			break;


		// Add entry to scrapbook

		case "scrpbk_addent":

			// Get scrapbook item ID

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			if ($ItemID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			// Get scrapbook entry information.  This is the title to give a link, and the target
			// of the link.

			$Title = get_assoc_default($FormData,"title",false);
			$Target = get_assoc_default($FormData,"target",false);
			if ($Title === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLE";
				break;
			}

			if ($Target === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTARGET";
				break;
			}

			// Create the entry

			$ItemInfo = array(
				"parent" => $ItemID,
				"title" => urlencode($Title),
				"user_id" => -1,
				"group_id" => -1,
				"item_type" => FTREE_OBJECT_TYPE_LINK,
				"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
				"source_info" => "",
				"reference_id" => $Target,
				"allow_dups" => true,
				"user_perm" => "RMWCODPN",
				"group_perm" => "RMW",
				"world_perm" => "R",
				);

			$NewObject = ftree_create_object_ext($GLOBALS["aib_db"],$ItemInfo);
			if ($NewObject[0] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $NewObject[1];
				break;
			}

			ftree_set_property($GLOBALS["aib_db"],$NewObject[1],AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_SCRAPBOOK_ENTRY,true);
			$OutData["status"] = "OK";
			$OutData["info"] = $NewObject[1];
			break;


		// Remove entry

		case "scrpbk_delent":

			// Get fields

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			if ($ItemID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			ftree_delete($GLOBALS["aib_db"],$Scrapbook["item_id"],true);
			$OutData["status"] = "OK";
			$OutData["info"] = "";
			break;


		// List scrapbook entries

		case "scrpbk_lstent":

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			if ($ItemID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$OptionSortOrder = strtoupper(get_assoc_default($FormData,"opt_sort","TITLE"));
			$OptionGetProperties = strtoupper(get_assoc_default($FormData,"opt_get_property","N"));
			$OptionGetFiles = strtoupper(get_assoc_default($FormData,"opt_get_files","N"));
			$OptionGetFields = strtoupper(get_assoc_default($FormData,"opt_get_field","N"));
			$OptionGetThumb = strtoupper(get_assoc_default($FormData,"opt_get_thumb","N"));
			$OptionGetPrimary = strtoupper(get_assoc_default($FormData,"opt_get_primary","N"));
			$OptionGetFirstThumb = strtoupper(get_assoc_default($FormData,"opt_get_first_thumb","N"));
			$OptionFilterType = strtoupper(get_assoc_default($FormData,"opt_filter_type",false));
			$OptionSelectType = strtoupper(get_assoc_default($FormData,"opt_select_type",false));
			$OptionDerefLinks = strtoupper(get_assoc_default($FormData,"opt_deref_links","N"));
			if ($OptionDerefLinks == "N")
			{
				$OptionDerefLinks = false;
			}
			else
			{
				$OptionDerefLinks = true;
			}

			if (preg_match("/[Y]/",$OptionGetProperties) != false)
			{
				$GetPropertyFlag = true;
			}
			else
			{
				$GetPropertyFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetFiles) != false)
			{
				$GetFilesFlag = true;
			}
			else
			{
				$GetFilesFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetFields) != false)
			{
				$GetFieldFlag = true;
			}
			else
			{
				$GetFieldFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetThumb) != false)
			{
				$GetThumbFlag = true;
			}
			else
			{
				$GetThumbFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetPrimary) != false)
			{
				$GetPrimaryFlag = true;
			}
			else
			{
				$GetPrimaryFlag = false;
			}

			$SortOrdersAllowed = array("ID" => true, "TITLE" => true,"STPA" => true);
			if (isset($SortOrdersAllowed[$OptionSortOrder]) == false)
			{
				$OptionSortOrder = "TITLE";
			}

			if ($OptionSortOrder == "TITLE")
			{
				$SortTitleFlag = true;
				$SortIDFlag = false;
			}
			else
			{
				$SortTitleFlag = false;
				$SortIDFlag = true;
			}

			if (preg_match("/[Y]/",$OptionGetFirstThumb) != false)
			{
				$GetFirstThumbFlag = true;
			}
			else
			{
				$GetFirstThumbFlag = false;
			}

			$OutData["info"] = array("records" => array());
			$ResultList = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,$UserID,false,FTREE_OBJECT_TYPE_LINK,false,$SortTitleFlag,$SortIDFlag);
			if ($CountOnlyFlag == true)
			{
				if ($ResultList[0] == "ERROR")
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = $ResultList[1];
					break;
				}

				$OutData["status"] = "OK";
				$OutData["info"] = $ResultList[0];
				break;
			}

			$TotalCount = 0;
			if ($ResultList != false)
			{
				// If sorting by STP name, use that instead

				if ($OptionSortOrder == "STPA")
				{
					// Create a map of records, where the key is the sort name

					$LocalMap = array();
					foreach($ResultList as $TempResultRecord)
					{
						// Get sort name; if none present, use item title followed by ID

						$STPSort = ftree_get_property($GLOBALS["aib_db"],$ResultRecord["item_id"],"stp:sort_name");
						if ($STPSort == false)
						{
							$STPSort = urldecode($TempResultRecord["item_title"]).",".$TempResultRecord["item_id"];
						}
						else
						{
							// Suffix the sort name with the item ID so we don't have name collisions (just in case)

							$STPSort .= ",".$TempResultRecord["item_id"];
						}

						$LocalMap[$STPSort] = $TempResultRecord;
					}

					// Get key list of map and sort

					$KeyList = array_keys($LocalMap);
					sort($KeyList);

					// Rebuild result list in the sorted key order

					$ResultList = array();
					foreach($KeyList as $TempKey)
					{
						$ResultList[] = $LocalMap[$TempKey];
					}
				}

				foreach($ResultList as $TempResultRecord)
				{
					// Determine if we're dereferencing links

					$IsLink = false;
					$LinkType = false;
					$LinkData = array();
					if ($OptionDerefLinks == false)
					{
						$ResultRecord = $TempResultRecord;
					}
					else
					{
						// Dereference based on link source if the record is a link

						if ($TempResultRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
						{
							$LinkType = $TempResultRecord["item_source_type"];
							$LinkData["link_type"] = $LinkType;
							switch($TempResultRecord["item_source_type"])
							{
								// Internal (AIB) link.  Fetch the linked item record.

								case FTREE_SOURCE_TYPE_LINK:
									$IsLink = true;
									$LinkTarget = $TempResultRecord["item_ref"];
									$ResultRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$LinkTarget);
									break;

								// STP Archive link

								case FTREE_SOURCE_TYPE_STPARCHIVE:
									$IsLink = true;
									$LinkTarget = $TempResultRecord["item_ref"];
									$LinkInfo = json_decode(urldecode($TempResultRecord["item_source_info"]),true);
									switch($LinkInfo["type"])
									{
										// Edition
										case FTREE_STP_LINK_EDITION:
											$LinkData["stp_link_type"] = $LinkInfo["type"];
											$LinkData["stp_url"] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
												$LinkInfo["year"]."/".stp_archive_month_name($LinkInfo["mon"])." ".
												$LinkInfo["day"]."/";
											$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
												$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
											break;

										// Page
										case FTREE_STP_LINK_PAGE:
											$LinkData["stp_link_type"] = $LinkInfo["type"];
											$LinkData["stp_url"] = "www.".STP_ARCHIVE_DOMAIN."/aib_page.php?edition=".
												$LinkInfo["ed"]."&page=".$LinkInfo["pg"];
											$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
												$LinkInfo["ed"]."&page=".$LinkInfo["pg"]."&paper=".$LinkInfo["paper"];
											break;

										// Year
										case FTREE_STP_LINK_YEAR:
											$LinkData["stp_link_type"] = $LinkInfo["type"];
											$LinkData["stp_url"] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
												$LinkInfo["year"];
											$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
												$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
											break;

										default:
											break;
									}

									$ResultRecord = $TempResultRecord;
									break;

								case FTREE_SOURCE_TYPE_URL:
									$IsLink = true;
									$LinkData["link_url"] = $LinkInfo["url"];
									$ResultRecord = $TempResultRecord;
									break;

								default:
									$ResultRecord = $TempResultRecord;
									break;

							}

							if ($ResultRecord == false)
							{
								continue;
							}

						}
						else
						{
							$ResultRecord = $TempResultRecord;
						}
					}

					// Determine the record type (collection, archive, etc.)

					$FormID = ftree_field_get_item_form($GLOBALS["aib_db"],$ResultRecord["item_id"]);
					if ($FormID == false)
					{
						$FormID = "";
					}

					$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
					if ($OptionFilterType != false)
					{
						if ($EntryTypeProperty == $OptFilterType)
						{
							continue;
						}
					}

					if ($OptionSelectType != false)
					{
						if ($EntryPropertyType != $OptSelectType)
						{
							continue;
						}
					}

					if ($CountOnlyFlag == true)
					{
						$TotalCount++;
						continue;
					}

					$LocalEntryType = "IT";
					if (isset($EntryTypes[$EntryTypeProperty]) == true)
					{
						$LocalEntryType = $EntryTypes[$EntryTypeProperty];
					}
					else
					{
						$LocalEntryType = $EntryTypeProperty;
					}

					$ItemPropertySet = array();
					if ($GetPropertyFlag == true)
					{
						$PropertyList = ftree_list_properties($GLOBALS["aib_db"],$ResultRecord["item_id"]);
						if ($PropertyList != false)
						{
							foreach($PropertyList as $PropertyName => $PropertyValue)
							{
								$LocalName = urldecode($PropertyName);
								$LocalValue = urldecode($PropertyValue);
								if (isset($BlockedProperties[$LocalName]) == true)
								{
									continue;
								}

								$ItemPropertySet[$LocalName] = $LocalValue;
							}
						}
					}

					$ItemFieldSet = array();
					if ($GetFieldFlag == true)
					{
						$FieldList = get_item_fields_in_form_order($GLOBALS["aib_db"],$ResultRecord["item_id"],$FormID);
//						$FieldList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ResultRecord["item_id"]);
						if ($FieldList != false)
						{
							foreach($FieldList as $FieldInfo)
							{
								$LocalDef = $FieldInfo["def"];
								$FieldID = $LocalDef["field_id"];
								$LocalType = "TEXT";
								if (isset($FieldDataTypeDesc[$LocalDef["field_data_type"]]) == true)
								{
									$LocalType = $FieldDataTypeDesc[$LocalDef["field_data_type"]];
								}

								$TempRecord = array(
									"field_id" => $FieldID,
									"field_value" => urldecode($FieldInfo["value"]),
									"field_title" => urldecode($LocalDef["field_title"]),
									"field_size" => $LocalDef["field_size"],
									"field_data_type" => $LocalType,
									"field_format" => urldecode($LocalDef["field_format"]),
									"form_id" => $FormID,
									);
								$ItemFieldSet[] = $TempRecord;
							}
						}
					}

					$ItemFileList = array();
					if ($GetFilesFlag == true)
					{
						$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ResultRecord["item_id"]);
						if ($FileList == false)
						{
							$FileList = array();
						}

						foreach($FileList as $FileRecord)
						{
							$StoredSize = stored_file_size($FileRecord);
							$ItemFileList[] = array(
							"file_id" => $FileRecord["record_id"],
							"file_original_name" => urldecode($FileRecord["file_original_name"]),
							"file_mime_type" => urldecode($FileRecord["file_mime_type"]),
							"file_stored_stamp" => $FileRecord["file_stored_stamp"],
							"file_stored_string" => date("Y.m.d.H.i.s",$FileRecord["file_stored_stamp"]),
							"file_type" => $FileRecord["file_class"],
							"file_size" => $StoredSize,
							);
						}
					}

					$ThumbData = array("id" => -1, "data" => "", "mime" => "");
					$PrimaryData = array("id" => -1, "data" => "", "mime" => "");
					if ($GetThumbFlag == true)
					{
						$LocalThumbData = get_item_image_data($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FILE_CLASS_THUMB);
						if ($LocalThumbData != false)
						{
							$ThumbData = $LocalThumbData;
						}
					}

					if ($GetPrimaryFlag == true)
					{
						$LocalPrimaryData = get_item_image_data($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FILE_CLASS_THUMB);
						if ($LocalPrimaryData != false)
						{
							$PrimaryData = $LocalPrimaryData;
						}
					}

					if ($GetFirstThumbFlag == true)
					{
						$FirstThumbID = aib_get_first_record_thumb($GLOBALS["aib_db"],$ResultRecord["item_id"]);
					}
					else
					{
						$FirstThumbID = "";
					}

					$OutRecord = array(
						"properties" => $ItemPropertySet,
						"fields" => $ItemFieldSet,
						"files" => $ItemFileList,
						"item_id" => $ResultRecord["item_id"],
						"item_tree_type" => $ResultRecord["item_type"],
						"item_type" => $LocalEntryType,
						"item_title" => urldecode($ResultRecord["item_title"]),
						"item_ref" => $ResultRecord["item_ref"],
						"item_source_type" => $ResultRecord["item_source_type"],
						"item_source_info" => $ResultRecord["item_source_info"],
						"item_create_stamp" => $ResultRecord["item_create_stamp"],
						"item_create_string" => date("Y.m.d.H.i.s",$ResultRecord["item_create_stamp"]),
						"thumb_id" => $ThumbData["id"],
						"thumb_data" => $ThumbData["data"],
						"thumb_mime" => $ThumbData["mime"],
						"primary_id" => $PrimaryData["id"],
						"primary_data" => $PrimaryData["data"],
						"primary_mime" => $PrimaryData["mime"],
						"first_thumb" => $FirstThumbID,
						"form_id" => $FormID,
					);

					if ($IsLink == true)
					{
						$OutRecord["is_link"] = "Y";
						$OutRecord["link_id"] = $TempResultRecord["item_id"];
						foreach($LinkData as $LinkDataKey => $LinkDataInfo)
						{
							$OutRecord[$LinkDataKey] = $LinkDataInfo;
						}
					}
					else
					{
						$OutRecord["is_link"] = "N";
					}

					$OutData["info"]["records"][] = $OutRecord;
				}
			}

			if ($CountOnlyFlag == true)
			{
				$OutData["status"] = "OK";
				$OutData["info"] + $TotalCount;
			}

			break;

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
