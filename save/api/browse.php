<?php
//
// Content browsing functions
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
		// Given a parent folder, get a list of content.  If the parent is -1,
		// then retrieve the list of archive groups.
		// -------------------------------------------------------------------

		case "list":

			// Get parent

			$ParentID = get_assoc_default($FormData,"parent",false);
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

			// Sort order may be:
			//
			//	ID
			//	TITLE
			//	STPA
			//

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
			$GetLongFlag = strtoupper(get_assoc_default($FormData,"opt_get_long_prop","N"));
			$OptGetPropCount = strtoupper(get_assoc_default($FormData,"opt_get_prop_count","N"));
			$OptNoLinks = strtoupper(get_assoc_default($FormData,"opt_no_links","N"));
			$OptShowLinkHome = strtoupper(get_assoc_default($FormData,"opt_show_link_home","N"));
			if ($OptShowLinkHome == "N")
			{
				$OptShowLinkHome = false;
			}
			else
			{
				$OptShowLinkHome = true;
			}

			if ($OptNoLinks == "N")
			{
				$OptNoLinks = false;
			}
			else
			{
				$OptNoLinks = true;
			}

			if ($GetLongFlag == "N")
			{
				$GetLongFlag = false;
			}
			else
			{
				$GetLongFlag = true;
			}

			if ($OptionDerefLinks == "N")
			{
				$OptionDerefLinks = false;
			}
			else
			{
				$OptionDerefLinks = true;
			}

			$OptionLinkProperties = get_assoc_default($FormData,"opt_get_link_properties","N");
			if ($OptionLinkProperties == "Y")
			{
				$OptionLinkProperties = true;
			}
			else
			{
				$OptionLinkProperties = false;
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

			$PropertyCountDef = array();
			if (preg_match("/[Y]/",$OptGetPropCount) != false)
			{
				if (isset($FormData["opt_prop_count_set"]) == false)
				{
					$OptGetPropCount = false;
				}
				else
				{
					$OptGetPropCount = true;
					$PropertyCountDef = json_decode($FormData["opt_prop_count_set"],true);
				}
			}
			else
			{
				$OptGetPropCount = false;
			}

			$LinkUserID = get_assoc_default($FormData,"link_user_id",false);
			$RejectLinkUserID = get_assoc_default($FormData,"reject_link_user_id",false);

			$OutData["info"] = array("records" => array());
			$ResultList = ftree_list_child_objects($GLOBALS["aib_db"],$ParentID,false,false,false,false,$SortTitleFlag,$SortIDFlag);
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
					$LinkUserHome = false;
					if ($OptionDerefLinks == false)
					{
						$ResultRecord = $TempResultRecord;
						if ($TempResultRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
						{
							$IsLink = true;
							$LinkTarget = $TempResultRecord["item_ref"];
							$LinkType = $TempResultRecord["item_source_type"];
							$LinkData["link_type"] = $LinkType;
						}
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
									if ($OptShowLinkHome == true)
									{
										$TempHome = ftree_get_item_user_home($GLOBALS["aib_db"],$TempResultRecord["item_id"]);
										if ($TempHome != false)
										{
											$LinkUserHome = $TempHome;
										}
									}

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

					// Special handling for links

					if ($IsLink == true)
					{
						if ($OptNoLinks == true)
						{
							continue;
						}

						if ($LinkUserID !== false)
						{
							if ($LinkUserID != $TempResultRecord["item_user_id"])
							{
								continue;
							}
						}

						if ($RejectLinkUserID !== false)
						{
							if ($RejectLinkUserID == $TempResultRecord["item_user_id"])
							{
								continue;
							}
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

					$ItemPropertySet = array();
					if ($GetPropertyFlag == true)
					{
						$PropertyList = ftree_list_properties($GLOBALS["aib_db"],$ResultRecord["item_id"],$GetLongFlag);
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
						"item_type" => $LocalEntryType,
						"item_tree_type" => $ResultRecord["item_type"],
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
						$OutRecord["link_type"] = $LinkType;
						$OutRecord["link_id"] = $TempResultRecord["item_id"];
						if ($OptShowLinkHome == true)
						{
							if ($LinkUserHome != false)
							{
								unset($LinkUserHome["user_pass"]);
								unset($LinkUserHome["user_primary_group"]);
								$OutRecord["link_user_profile"] = $LinkUserHome;
							}
							else
							{
								$OutRecord["link_user_profile"] = array();
							}
						}

						foreach($LinkData as $LinkDataKey => $LinkDataInfo)
						{
							$OutRecord[$LinkDataKey] = $LinkDataInfo;
						}

						if ($OptionLinkOwner == true)
						{
							$OutRecord["link_owner"] = $ResultRecord["item_user_id"];
							$TempUser = ftree_get_user($GLOBALS["aib_db"],$ResultRecord["item_user_id"]);
							$OutRecord["link_owner_login"] = $TempUser["user_login"];
							$OutRecord["link_owner_title"] = urldecode($TempUser["user_title"]);
							$OutRecord["link_owner_properties"] = array();
							$UserPropertyList = ftree_list_user_prop($GLOBALS["aib_db"],$UserID);
							foreach($UserPropertyList as $LocalName => $LocalValue)
							{
								$TempName = urldecode($LocalName);
								$OutRecord["link_owner_properties"][$TempName] = urldecode($LocalValue);
							}

						}

						$OutRecord["link_ref_properties"] = array();
						if ($OptionLinkProperties == true)
						{
							$LinkProperties = ftree_list_properties($GLOBALS["aib_db"],$TempResultRecord["item_ref"],true);
							if ($LinkProperties != false)
							{
								foreach($LinkProperties as $LocalName => $LocalValue)
								{
									$TempName = urldecode($LocalName);
									$OutRecord["link_ref_properties"][$TempName] = urldecode($LocalValue);
								}
							}
						}
					}
					else
					{
						$OutRecord["is_link"] = "N";
					}

					// Count properties if needed

					$OutRecord["property_counts"] = array();
					if ($OptGetPropCount == true)
					{
						$PropertyCountSet = ftree_count_child_object_property_set($GLOBALS["aib_db"],$TempResultRecord["item_id"],false,false,false,$PropertyCountDef);
						$OutRecord["property_counts"] = $PropertyCountSet;
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

		// Retrieve a specific item in the folder tree

		case "get":

			// Get ID

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the item

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Sort order may be:
			//
			//	ID
			//	TITLE
			//

			$OptionGetProperties = strtoupper(get_assoc_default($FormData,"opt_get_property","N"));
			if (preg_match("/[Y]/",$OptionGetProperties) != false)
			{
				$GetPropertyFlag = true;
			}
			else
			{
				$GetPropertyFlag = false;
			}

			$OptionGetFields = strtoupper(get_assoc_default($FormData,"opt_get_field","N"));
			if (preg_match("/[Y]/",$OptionGetFields) != false)
			{
				$GetFieldFlag = true;
			}
			else
			{
				$GetFieldFlag = false;
			}

			$OptionGetFiles = strtoupper(get_assoc_default($FormData,"opt_get_files","N"));
			if (preg_match("/[Y]/",$OptionGetFiles) != false)
			{
				$GetFileFlag = true;
			}
			else
			{
				$GetFileFlag = false;
			}

			$OutData["info"] = array("records" => array());
			$ResultRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
			if ($ResultRecord != false);
			{
				$FormID = ftree_field_get_item_form($GLOBALS["aib_db"],$ItemID);
				if ($FormID == false)
				{
					$FormID = "";
				}

				// Determine the record type (collection, archive, etc.)

				$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ResultRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				$LocalEntryType = "IT";
				if (isset($EntryTypes[$EntryTypeProperty]) == true)
				{
					$LocalEntryType = $EntryTypes[$EntryTypeProperty];
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
//					$FieldList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ResultRecord["item_id"]);
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
				if ($GetFileFlag == true)
				{
					$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID);
					$OutData["info"] = array("records" => array());
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

				$OutData["info"]["records"][] = array(
					"properties" => $ItemPropertySet,
					"fields" => $ItemFieldSet,
					"files" => $ItemFileList,
					"item_id" => $ResultRecord["item_id"],
					"item_type" => $LocalEntryType,
					"item_title" => urldecode($ResultRecord["item_title"]),
					"item_ref" => $ResultRecord["item_ref"],
					"item_source_type" => $ResultRecord["item_source_type"],
					"item_source_info" => $ResultRecord["item_source_info"],
					"item_create_stamp" => $ResultRecord["item_create_stamp"],
					"item_create_string" => date("Y.m.d.H.i.s",$ResultRecord["item_create_stamp"]),
					"form_id" => $FormID,
					);
			}

			break;

		// Retrieve list of files associated with an item

		case "listfiles":

			// Get ID

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the item

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Get the list of files

			$ResultList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID);
			$OutData["info"] = array("records" => array());
			if ($ResultList == false)
			{
				break;
			}

			foreach($ResultList as $FileRecord)
			{
				$StoredSize = stored_file_size($FileRecord);
				$OutData["info"]["records"][] = array(
					"file_id" => $FileRecord["record_id"],
					"file_original_name" => urldecode($FileRecord["file_original_name"]),
					"file_mime_type" => urldecode($FileRecord["file_mime_type"]),
					"file_stored_stamp" => $FileRecord["file_stored_stamp"],
					"file_stored_string" => date("Y.m.d.H.i.s",$FileRecord["file_stored_stamp"]),
					"file_type" => $FileRecord["file_class"],
					"file_size" => $StoredSize,
					);
			}

			break;

		// Get file ID for first thumb for record

		case "firstthumb":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			$SendImageOpt = strtoupper(get_assoc_default($FormData,"opt_send_image","N"));
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the item

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$ItemRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
			if ($ItemRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Make sure the item is a record.  If not, error.

			$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			if ($EntryTypeProperty != AIB_ITEM_TYPE_RECORD)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTARECORD";
				break;
			}

			$ThumbID = aib_get_first_record_thumb($GLOBALS["aib_db"],$ItemID);
			if ($ThumbID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTHUMB";
				break;
			}

			if ($SendImageOpt != "Y")
			{
				$OutData["status"] = "OK";
				$OutData["info"] = $ThumbID;
				break;
			}

			$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
			if ($FileInfo != false)
			{
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
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$Buffer = file_get_contents($SourceName);
				if ($Buffer == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$OutData["mime"] = $SourceMIME;
				$OutData["info"] = base64_encode($Buffer);
				break;
			}

			$OutData["status"] = "ERROR";
			$OutData["info"] = "NOFILE";
			break;


		// Get file ID for item thumb

		case "getthumb":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the item

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$ItemRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
			if ($ItemRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID,AIB_FILE_CLASS_THUMB);
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
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOFILE";
				break;
			}

			$SendImageOpt = strtoupper(get_assoc_default($FormData,"opt_send_image","N"));
			if ($SendImageOpt == "Y")
			{
				$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
				if ($FileInfo == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "NOFILE";
					break;
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
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$Buffer = file_get_contents($SourceName);
				if ($Buffer == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$OutData["mime"] = $SourceMIME;
				$OutData["info"] = base64_encode($Buffer);
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $ThumbID;
			break;


		// Get file ID for primary item image

		case "getprimary":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the item

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$ItemRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
			if ($ItemRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID,AIB_FILE_CLASS_PRIMARY);
			$ThumbID = -1;
			foreach($FileList as $FileRecord)
			{
				if ($FileRecord["file_class"] == AIB_FILE_CLASS_PRIMARY)
				{
					$ThumbID = $FileRecord["record_id"];
					break;
				}
			}

			if ($ThumbID < 0)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOFILE";
				break;
			}

			$SendImageOpt = strtoupper(get_assoc_default($FormData,"opt_send_image","N"));
			if ($SendImageOpt == "Y")
			{
				$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
				if ($FileInfo == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "NOFILE";
					break;
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
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$Buffer = file_get_contents($SourceName);
				if ($Buffer == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$OutData["mime"] = $SourceMIME;
				$OutData["info"] = base64_encode($Buffer);
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $ThumbID;
			break;


		// Get file ID for item original image

		case "getoriginal":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGIDORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the item

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$ItemRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
			if ($ItemRecord == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID);
			$ThumbID = -1;
			foreach($FileList as $FileRecord)
			{
				if ($FileRecord["file_class"] == AIB_FILE_CLASS_ORIGINAL)
				{
					$ThumbID = $FileRecord["record_id"];
					break;
				}
			}

			if ($ThumbID < 0)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOFILE";
				break;
			}

			$SendImageOpt = strtoupper(get_assoc_default($FormData,"opt_send_image","N"));
			if ($SendImageOpt == "Y")
			{
				$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
				if ($FileInfo == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "NOFILE";
					break;
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
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$Buffer = file_get_contents($SourceName);
				if ($Buffer == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "FILENOTAVAILABLE";
					break;
				}

				$OutData["mime"] = $SourceMIME;
				$OutData["info"] = base64_encode($Buffer);
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $ThumbID;
			break;


		// Get file information

		case "fileinfo":
			$ItemID = get_assoc_default($FormData,"file_id",false);
			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ItemID);
			if ($FileInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTFOUND";
				break;
			}

			$FileRecord = $FileInfo["record"];
			$StoredSize = stored_file_size($FileRecord);
			$OutData["info"]["record"] = array(
				"file_id" => $ItemID,
				"file_original_name" => urldecode($FileRecord["file_original_name"]),
				"file_mime_type" => urldecode($FileRecord["file_mime_type"]),
				"file_stored_stamp" => $FileRecord["file_stored_stamp"],
				"file_stored_string" => date("Y.m.d.H.i.s",$FileRecord["file_stored_stamp"]),
				"file_type" => $FileRecord["file_class"],
				"file_size" => $StoredSize,
				);
			$OutData["status"] = "OK";
			break;


		// Get file content

		case "getfile":
			$ItemID = get_assoc_default($FormData,"file_id",false);
			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ItemID);
			if ($FileInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTFOUND";
				break;
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
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTAVAILABLE";
				break;
			}

			$Buffer = file_get_contents($SourceName);
			if ($Buffer == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTAVAILABLE";
				break;
			}

			$OutData["mime"] = $SourceMIME;
			$OutData["info"] = base64_encode($Buffer);
			break;

		// Copy file content to a specified destination file on the server

		case "copyfile":
			$ItemID = get_assoc_default($FormData,"file_id",false);
			$DestFile = get_assoc_default($FormData,"dest",false);
			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			if ($DestFile == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGDEST";
				break;
			}

			$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ItemID);
			if ($FileInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTFOUND";
				break;
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
			if (file_exists($SourceName) == true)
			{
				system("cp -f \"$SourceName\" \"$DestFile\" 2> /dev/null");
				if (file_exists($DestFile) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "CANNOTCOPY";
				}
			}
			else
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTREADFILE";
			}

			break;

		case "attach_item_file":

			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Get path or data, MIME, type

			$FilePath = get_assoc_default($FormData,"file_path",false);
			$FileData = get_assoc_default($FormData,"file_data",false);
			$FileMIME = get_assoc_default($FormData,"file_mime","application/octet-stream");
			$FileType = get_assoc_default($FormData,"file_type",AIB_FILE_CLASS_PRIMARY);

			if ($FilePath == false && $FileData == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOFILEDATAORPATH";
				break;
			}

			// If file data was passed, create a temporary file using the timestamp and item ID in /tmp

			$TempFileName = false;
			if ($FileData != false)
			{
				$LocalStamp = microtime(true);
				$LocalStamp = preg_replace("/[^0-9A-Za-z]/","_",$LocalStamp);
				$TempFileName = "/tmp/$LocalStamp"."_".$ItemID.".dat";
				$Buffer = base64_decode($FileData);
				file_put_contents($TempFileName,$Buffer);
				unset($FileData);
				unset($Buffer);
			}
			else
			{
				$TempFileName = $FilePath;
			}


		case "delete_item_file":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Get file ID

			$FileID = get_assoc_default($FormData,"file_id",false);
			if ($FileID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOFILEID";
				break;
			}

			// Remove file

			aib_remove_item_file($GLOBALS["aib_db"],$FileID);
			$OutData["status"] = "OK";
			$OutData["info"] = "ERROR";
			break;

		// Get the list of fields associated with an item

		case "item_fields":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["status"]["records"] = array();
//			$FieldList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ItemID);
			$FieldList = get_item_fields_in_form_order($GLOBALS["aib_db"],$ResultRecord["item_id"],$FormID);
			if ($FieldList != false)
			{
				foreach($FieldList as $FieldID => $FieldInfo)
				{
					$LocalDef = $FieldInfo["def"];
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

				$OutData["status"]["records"] = $ItemFieldSet;
			}

			break;

		case "store_item_fields":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Get fields

			$StorageData = array();
			foreach(array_keys($FormData) as $FormFieldName)
			{
				if (preg_match("/^field[\_]id/",$FormFieldName) != false)
				{
					$FieldNumber = preg_replace("/^field[\_]id/","",$FormFieldName);
					$TempID = $FormData[$FormFieldName];
					$TempName = "field_value".$FieldNumber;
					if (isset($FormData[$TempName]) == false)
					{
						continue;
					}

					$StorageData[$TempID] = $FormData[$TempName];
				}
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Store field

			$Result = ftree_field_store_item_fields($GLOBALS["aib_db"],$ItemID,$StorageData,false);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTSTOREFIELDS";
				break;
			}

			$OutData["status"] = "OK";
			break;


		case "del_item_field":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$FieldID = get_assoc_default($FormData,"field_id",false);
			if ($FieldID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFIELDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			ftree_field_delete_item_field($GLOBALS["aib_db"],$ItemID,$StorageData,false);
			$OutData["status"] = "OK";
			break;


		case "create_item":
			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"path",false);
			$AllowDupsFlag = strtoupper(get_assoc_default($FormData,"opt_allow_dup","N"));
			if (preg_match("/[Y]/",$AllowDupsFlag) != false)
			{
				$AllowDupsFlag = true;
			}
			else
			{
				$AllowDupsFlag = false;
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

			$NewItemTitle = get_assoc_default($FormData,"item_title",false);
			if ($NewItemTitle == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTITLE";
				break;
			}

			$AIBType = get_assoc_default($FormData,"item_class",AIB_ITEM_TYPE_SUBGROUP);
			$SourceFieldList = array(
				"item_title" => "title",
				"item_owner_id" => "user_id",
				"item_owner_group" => "group_id",
				"item_source" => "source_type",
				"item_reference_id" => "reference_id",
				"item_source_info" => "source_info",
				"item_user_perm" => "user_perm",
				"item_group_perm" => "group_perm",
				"item_world_perm" => "world_perm",
				"parent" => "parent",
				);

			$OutData["status"] = "OK";
			$ParentFolderType = ftree_get_property($GLOBALS["aib_db"],$ParentID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			$LocalType = FTREE_OBJECT_TYPE_FOLDER;
			switch($ParentFolderType)
			{
				case AIB_ITEM_TYPE_SYSTEM:
					break;

				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_ARCHIVE:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_ARCHIVE:
				case AIB_ITEM_TYPE_COLLECTION:
				case AIB_ITEM_TYPE_SUBGROUP:
				case AIB_ITEM_TYPE_RECORD:
					break;

/*
				case AIB_ITEM_TYPE_ARCHIVE:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_COLLECTION:
							break;

						case AIB_ITEM_TYPE_SUBGROUP:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_COLLECTION:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_SUBGROUP:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_SUBGROUP:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_SUBGROUP:
						case AIB_ITEM_TYPE_RECORD:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_RECORD:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_ITEM:
						$LocalType = FTREE_OBJECT_TYPE_FILE;
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				default:
					// Private type

					if (substr($AIBType,0,1) == "#")
					{
						break;
					}

					// Get the "archives" folder; if that is the parent and we're trying to create a group, allow it.

					$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVE GROUP");
					if ($ParentID == $ArchivesFolderID)
					{
						if ($AIBType == AIB_ITEM_TYPE_ARCHIVE_GROUP)
						{
							break;
						}
					}

					$OutData["status"] = "ERROR";
*/
					break;
			}

			if ($OutData["status"] == "ERROR")
			{
				$OutData["info"] = "CANNOTCREATETYPEINPARENT";
				break;
			}

			$ItemInfo = array();
			foreach($SourceFieldList as $FormName => $InfoName)
			{
				if (isset($FormData[$FormName]) == true)
				{
					$ItemInfo[$InfoName] = $FormData[$FormName];
				}
			}

			$ItemInfo["item_type"] = $LocalType;
			$Result = ftree_create_object_ext($GLOBALS["aib_db"],$ItemInfo);
			if ($Result[0] == "ERROR")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result[1];
				break;
			}

			$NewID = $Result[1];

			// Set property for folder type

			ftree_set_property($GLOBALS["aib_db"],$NewID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,$AIBType);
			$OutData["status"] = "OK";
			$OutData["info"] = $NewID;
			break;

		case "modify_item":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$AllowDupFlag = get_assoc_default($FormData,"opt_allow_dup",false);
			if ($AllowDupFlag != false)
			{
				if (preg_match("/^[Yy]/",$AllowDupFlag) != false)
				{
					$AllowDupFlag = true;
				}
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$NewData = array();
			$ModifyList = array("item_title","item_user_id","item_group_id","user_perm","group_perm","world_perm");
			foreach($ModifyList as $ModifyName)
			{
				if (isset($FormData[$ModifyName]) == true)
				{
					$NewData[$ModifyName] = $FormData[$ModifyName];
				}
			}

			$Result = ftree_modify($GLOBALS["aib_db"],$ItemID,$NewData,$AllowDupFlag);
			if ($Result[0] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result[1];
			}
			else
			{
				$OutData["status"] = "OK";
				$OutData["info"] = "$ItemID";
			}

			break;


		case "delete_item":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			ftree_delete($GLOBALS["aib_db"],$ItemID,false);
			$OutData["status"] = "OK";
			break;

		case "move_item":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"parent_path",false);
			$AllowDupsFlag = strtoupper(get_assoc_default($FormData,"opt_allow_dup","N"));
			if (preg_match("/[Y]/",$AllowDupsFlag) != false)
			{
				$AllowDupsFlag = true;
			}
			else
			{
				$AllowDupsFlag = false;
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
				$OutData["info"] = "NOACCESSTOTARGET";
				break;
			}

/*
			$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
			$AIBType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			$ParentFolderType = ftree_get_property($GLOBALS["aib_db"],$ParentID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			switch($ParentFolderType)
			{
				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_ARCHIVE:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_ARCHIVE:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_COLLECTION:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_COLLECTION:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_SUBGROUP:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_SUBGROUP:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_SUBGROUP:
						case AIB_ITEM_TYPE_RECORD:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_RECORD:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_ITEM:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				default:
					// If the parent is the root of archive groups and the new item is an archive group, allow.

					$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
					if ($ParentID == $ArchivesFolderID)
					{
						if ($AIBType == AIB_ITEM_TYPE_ARCHIVE_GROUP)
						{
							break;
						}
					}

					// If the requesting user is the super-user, then allow anything

					if ($RequestUserType == AIB_USER_TYPE_ROOT)
					{
						break;
					}

					$OutData["status"] = "ERROR";
					break;
			}

			if ($OutData["status"] == "ERROR")
			{
				$OutData["info"] = "CANNOTCREATETYPEINPARENT";
				break;
			}

*/
			$NewItemTitle = get_assoc_default($FormData,"item_title",false);
			$Result = ftree_move($GLOBALS["aib_db"],$ItemID,$ParentID,$AllowDupsFlag,$NewItemTitle);
			if ($Result[0] == "ERROR")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result[1];
			}
			else
			{
				$OutData["status"] = "OK";
			}

			break;

		case "copy_item":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$ParentID = get_assoc_default($FormData,"parent",false);
			$ParentPath = get_assoc_default($FormData,"parent_path",false);
			$AllowDupsFlag = strtoupper(get_assoc_default($FormData,"opt_allow_dup","N"));
			if (preg_match("/[Y]/",$AllowDupsFlag) != false)
			{
				$AllowDupsFlag = true;
			}
			else
			{
				$AllowDupsFlag = false;
			}

			$AutoRenameFlag = strtoupper(get_assoc_default($FormData,"opt_auto_rename","N"));
			if (preg_match("/[Y]/",$AllowDupsFlag) != false)
			{
				$AutoRenameFlag = true;
			}
			else
			{
				$AutoRenameFlag = false;
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
				$OutData["info"] = "NOACCESSTOTARGET";
				break;
			}

			$ParentFolderType = ftree_get_property($GLOBALS["aib_db"],$ParentID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			$AIBType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			switch($ParentFolderType)
			{
				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_ARCHIVE:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_ARCHIVE:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_COLLECTION:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_COLLECTION:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_SUBGROUP:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_SUBGROUP:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_SUBGROUP:
						case AIB_ITEM_TYPE_RECORD:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				case AIB_ITEM_TYPE_RECORD:
					switch($AIBType)
					{
						case AIB_ITEM_TYPE_ITEM:
							break;

						default:
							$OutData["status"] = "ERROR";
							break;
					}

					break;

				default:
					$OutData["status"] = "ERROR";
					break;
			}

			if ($OutData["status"] == "ERROR")
			{
				$OutData["info"] = "CANNOTCREATETYPEINPARENT";
				break;
			}

			$Result = ftree_copy_recursive($GLOBALS["aib_db"],$ItemID,$ParentID,$AllowDupsFlag,$AutoRenameFlag);
			if ($Result[0] == "ERROR")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Result[1];
			}
			else
			{
				$OutData["status"] = "OK";
			}

			break;

		case "set_item_form":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			ftree_field_set_item_form($GLOBALS["aib_db"],$ItemID,$FormID);
			$OutData["status"] = "OK";
			break;


		case "get_item_form":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$Result = ftree_field_get_item_form($GLOBALS["aib_db"],$ItemID);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOFORM";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $Result;
			break;


		case "form_usage":
			$FormID = get_assoc_default($FormData,"form_id",false);
			if ($FormID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFORMID";
				break;
			}

			$Result = ftree_field_form_usage($GLOBALS["aib_db"],$FormID);
			$OutData["status"] = "OK";
			if ($Result != false)
			{
				$Result = array();
			}

			$OutData["info"]["records"] = array();
			foreach($Result as $Record)
			{
				$OutData["info"]["records"][] = array(
					"form_id" => $Record["form_id"],
					"item_id" => $Record["item_id"],
					);
			}

			break;

		case "get_archive_and_group":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$ArchiveInfo = ftree_get_archive_and_archive_group($GLOBALS["aib_db"],$ItemID);
			if ($ArchiveInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETINFO";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"]["records"] = array(
				"archive" => $ArchiveInfo["archive"],
				"archive_group" => $ArchiveInfo["archive_group"]
				);
			break;


		case "get_path":

			// Get parent

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$CountOnlyFlag = strtoupper(get_assoc_default($FormData,"opt_count_only","N"));
			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			// Verify that we can get access to the folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Sort order may be:
			//
			//	ID
			//	TITLE
			//

			$OptionSortOrder = strtoupper(get_assoc_default($FormData,"opt_sort","TITLE"));
			$OptionGetProperties = strtoupper(get_assoc_default($FormData,"opt_get_property","N"));
			$OptionGetFiles = strtoupper(get_assoc_default($FormData,"opt_get_files","N"));
			$OptionGetFields = strtoupper(get_assoc_default($FormData,"opt_get_field","N"));
			$OptionGetThumb = strtoupper(get_assoc_default($FormData,"opt_get_thumb","N"));
			$OptionGetPrimary = strtoupper(get_assoc_default($FormData,"opt_get_primary","N"));
			$OptionGetParentItems = strtoupper(get_assoc_default($FormData,"opt_get_parent_items","N"));
			if (preg_match("/[Y]/",$OptionGetProperties) != false)
			{
				$GetPropertyFlag = true;
			}
			else
			{
				$GetPropertyFlag = false;
			}

			if (preg_match("/[Y]/",$OptionGetParentItems) != false)
			{
				$GetParentItemsFlag = true;
			}
			else
			{
				$GetParentItemsFlag = false;
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

			$SortOrdersAllowed = array("ID" => true, "TITLE" => true);
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

			$OutData["info"] = array("records" => array());
			$ResultList = ftree_get_item_id_path($GLOBALS["aib_db"],$ItemID);
			if ($ResultList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADITEM";
				break;
			}

			foreach($ResultList as $LocalItemID)
			{
				$ItemInfo = ftree_get_item($GLOBALS["aib_db"],$LocalItemID);

				// Determine the record type (collection, archive, etc.)

				$EntryTypeProperty = ftree_get_property($GLOBALS["aib_db"],$ItemInfo["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				$LocalEntryType = "IT";
				if (isset($EntryTypes[$EntryTypeProperty]) == true)
				{
					$LocalEntryType = $EntryTypes[$EntryTypeProperty];
				}

				$ItemParentList = array();
				if ($GetParentItemsFlag == true)
				{
					$TempChildList = ftree_list_child_objects($GLOBALS["aib_db"],$LocalItemID,false,false,false,false,false,false);
					if ($TempChildList != false)
					{
						foreach($TempChildList as $TempChildRecord)
						{
							$ItemChildRecord = array("item" => array(), "files" => array(), "properties" => array(), "fields" => array());
							$TempPropertySet = array();
							if ($GetPropertyFlag == true)
							{
								$PropertyList = ftree_list_properties($GLOBALS["aib_db"],$TempChildRecord["item_id"]);
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
			
										$TempPropertySet[$LocalName] = $LocalValue;
									}
								}
							}

							$TempFieldSet = array();
							if ($GetFieldFlag == true)
							{
								$FieldList = get_item_fields_in_form_order($GLOBALS["aib_db"],$ResultRecord["item_id"],$FormID);
//								$FieldList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$TempChildRecord["item_id"]);
								if ($FieldList != false)
								{
									foreach($FieldList as $FieldID => $FieldInfo)
									{
										$LocalDef = $FieldInfo["def"];
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
										$TempFieldSet[] = $TempRecord;
									}
								}
							}

							$TempFileSet = array();
							if ($GetFilesFlag == true)
							{
								$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$TempChildRecord["item_id"]);
								if ($FileList == false)
								{
									$FileList = array();
								}

								foreach($FileList as $FileRecord)
								{
									$StoredSize = stored_file_size($FileRecord);
									$TempFileSet[] = array(
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

							$TempChildRecord["item_title"] = urldecode($TempChildRecord["item_title"]);
							$TempChildRecord["item_source_info"] = urldecode($TempChildRecord["item_source_info"]);
							$ItemChildRecord = array("item" => $TempChildRecord, "files" => $TempFileSet, "properties" => $TempPropertySet,
								"fields" => $TempFieldSet);
							$ItemParentList[] = $ItemChildRecord;
						}
					}
								
				}

				$ItemPropertySet = array();
				if ($GetPropertyFlag == true)
				{
					$PropertyList = ftree_list_properties($GLOBALS["aib_db"],$ItemInfo["item_id"]);
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
//					$FieldList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ItemInfo["item_id"]);
					if ($FieldList != false)
					{
						foreach($FieldList as $FieldID => $FieldInfo)
						{
							$LocalDef = $FieldInfo["def"];
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
					$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemInfo["item_id"]);
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
					$LocalThumbData = get_item_image_data($GLOBALS["aib_db"],$ItemInfo["item_id"],AIB_FILE_CLASS_THUMB);
					if ($LocalThumbData != false)
					{
						$ThumbData = $LocalThumbData;
					}
				}

				if ($GetPrimaryFlag == true)
				{
					$LocalPrimaryData = get_item_image_data($GLOBALS["aib_db"],$ItemInfo["item_id"],AIB_FILE_CLASS_THUMB);
					if ($LocalPrimaryData != false)
					{
						$PrimaryData = $LocalPrimaryData;
					}
				}

				$OutData["info"]["records"][] = array(
					"properties" => $ItemPropertySet,
					"fields" => $ItemFieldSet,
					"files" => $ItemFileList,
					"item_id" => $ItemInfo["item_id"],
					"item_type" => $LocalEntryType,
					"item_title" => urldecode($ItemInfo["item_title"]),
					"item_ref" => $ItemInfo["item_ref"],
					"item_source_type" => $ItemInfo["item_source_type"],
					"item_source_info" => $ItemInfo["item_source_info"],
					"item_create_stamp" => $ItemInfo["item_create_stamp"],
					"item_create_string" => date("Y.m.d.H.i.s",$ItemInfo["item_create_stamp"]),
					"thumb_id" => $ThumbData["id"],
					"thumb_data" => $ThumbData["data"],
					"thumb_mime" => $ThumbData["mime"],
					"primary_id" => $PrimaryData["id"],
					"primary_data" => $PrimaryData["data"],
					"primary_mime" => $PrimaryData["mime"],
					"child_items" => $ItemParentList,
					);
			}

			break;

		// Set item properties

		case "set_item_prop":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			$LongProperty = get_assoc_default($FormData,"opt_long","N");
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Get properties

			$PropertySet = array();
			$Prefix = "propname_";
			foreach($FormData as $FieldName => $FieldValue)
			{
				if (substr($FieldName,0,9) == "propname_")
				{
					$ValueName = "propval_".substr($FieldName,9);
					if (isset($FormData[$ValueName]) == false)
					{
						continue;
					}

					$PropertyName = $FieldValue;
					$PropertyValue = $FormData[$ValueName];
					$PropertySet[$PropertyName] = $PropertyValue;
				}
			}

			// Store properties

			foreach($PropertySet as $Name => $Value)
			{
				if ($LongProperty == "N")
				{
					ftree_set_property($GLOBALS["aib_db"],$ItemID,$Name,$Value,true);
				}
				else
				{
					ftree_set_long_property($GLOBALS["aib_db"],$ItemID,$Name,$Value,true);
				}

			}

			$OutData["status"] = "OK";
			$OutData["info"] = "";
			break;


		// Get item properties

		case "get_item_prop":

			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			$LongProperty = get_assoc_default($FormData,"opt_long","N");
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$ResultList = ftree_list_properties($GLOBALS["aib_db"],$ItemID);
			$LongResultList = ftree_list_long_properties($GLOBALS["aib_db"],$ItemID);
			if ($ResultList == false)
			{
				$ResultList = array();
			}

			if ($LongResultList == false)
			{
				$LongResultList = array();
			}

			$OutData["info"] = array("records" => array());
			foreach($ResultList as $PropName => $PropValue)
			{
				$OutData["info"]["records"][$PropName] = $PropValue;
			}

			foreach($LongResultList as $PropName => $PropValue)
			{
				$OutData["info"]["records"][$PropName] = $PropValue;
			}

			$OutData["status"] = "OK";
			break;


		// Remove item properties

		case "del_item_prop":

			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			$LongProperty = get_assoc_default($FormData,"opt_long","N");
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Get properties

			$PropertySet = array();
			$Prefix = "propname_";
			foreach($FormData as $FieldName => $FieldValue)
			{
				if (substr($FieldName,0,9) == "propname_")
				{
					$PropertySet[] = $FieldValue;
				}
			}

			// Delete properties

			foreach($PropertySet as $Name)
			{
				ftree_delete_property($GLOBALS["aib_db"],$ItemID,$Name);
				ftree_delete_long_property($GLOBALS["aib_db"],$ItemID,$Name);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = "";
			break;

		// Get list of items with a specific property value

		case "items_with_prop_value":
			$PropertyName = get_assoc_default($FormData,"prop_name",false);
			$PropertyValue = get_assoc_default($FormData,"prop_value",false);
			$LongProperty = get_assoc_default($FormData,"opt_long","N");
			if ($PropertyName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNAME";
				break;
			}

			if ($PropertyValue == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGVALUE";
				break;
			}

			$ResultList = ftree_get_all_property_values($GLOBALS["aib_db"],$PropertyName,$PropertyValue);
			$LongResultList = ftree_get_all_long_property_values($GLOBALS["aib_db"],$PropertyName,$PropertyValue);
			if ($ResultList == false)
			{
				$ResultList = array();
			}

			if ($LongResultList == false)
			{
				$LongResultList = array();
			}

			$OutData["info"] = array("records" => array());
			foreach($ResultList as $Record)
			{
				$OutData["info"]["records"][] = $Record["item_id"];
			}

			foreach($LongResultList as $Record)
			{
				$OutData["info"]["records"][] = $Record["item_id"];
			}

			$OutData["status"] = "OK";
			break;



		// Get list of items with a given property

		case "items_with_prop":
			$PropertyName = get_assoc_default($FormData,"prop_name",false);
			$LongProperty = get_assoc_default($FormData,"opt_long","N");
			if ($PropertyName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNAME";
				break;
			}

			$ResultList = ftree_get_all_property_values($GLOBALS["aib_db"],$PropertyName,false);
			$LongResultList = ftree_get_all_long_property_values($GLOBALS["aib_db"],$PropertyName,false);
			if ($ResultList == false)
			{
				$ResultList = array();
			}

			if ($LongResultList == false)
			{
				$LongResultList = array();
			}

			$OutData["info"] = array("records" => array());
			foreach($ResultList as $Record)
			{
				$OutData["info"]["records"][] = $Record["item_id"];
			}

			foreach($LongResultList as $Record)
			{
				$OutData["info"]["records"][] = $Record["item_id"];
			}

			$OutData["status"] = "OK";
			break;


		// Store file for item

		case "store_item_file":
			// Get item

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			// Verify that we can get access to the parent folder

			if (verify_user_item_access($GLOBALS["aib_db"],$ItemID,$RequestUserRoot,$RequestUserID) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOACCESS";
				break;
			}

			// Get the name of the file

			$SourceName = get_assoc_default($FormData,"source_file_name",false);
			$FileClass = get_assoc_default($FormData,"file_class","pr");
			if ($SourceName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFILENAME";
				break;
			}

			if (file_exists($SourceName) == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTEXIST";
				break;
			}

			$TempHandle = fopen($SourceName,"r");
			if ($TempHandle == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "FILENOTREADABLE";
				break;
			}

			fclose($TempHandle);

			$Result = aib_store_file($GLOBALS["aib_db"],$SourceName,$FileClass,$ItemID);
			system("rm -f \"$SourceName\" 2> /dev/null > /dev/null");
			$OutData["status"] = "OK";
			$OutData["info"] = array("records" => $Result);
			break;

		case "list_item_ref":
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$OutData["info"]["records"] = array();
			$Result = aib_get_item_references($ItemID);
			foreach($Result as $Record)
			{
				$OutData["records"][] = array(
					"item_id" => $Record["item_id"],
					"item_parent" => $Record["item_parent"],
					"item_type" => $Record["item_type"],
					"item_title" => urldecode($Record["item_title"]),
					"item_user_id" => $Record["item_user_id"],
					"item_group_id" => $Record["item_group_id"],
					"item_ref" => $Record["item_ref"],
					"item_source_type" => $Record["item_source_type"],
					"item_source_info" => $Record["item_source_info"],
					"item_create_stamp" => $Record["item_create_stamp"],
					"user_perm" => $Record["user_perm"],
					"group_perm" => $Record["group_perm"],
					"world_perm" => $Record["world_perm"],
					);
			}

			$OutData["status"] = "OK";
			break;

		case "item_links":
			$OutData["info"] = array();
			$OutData["status"] = "OK";
			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$ItemPath = get_assoc_default($FormData,"path",false);
			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMORPATH";
				break;
			}

			if ($ItemID == false)
			{
				$ItemID = ftree_get_object_by_path($GLOBALS["aib_db"],$ItemPath);
			}

			if ($ItemID == false && $ItemPath == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDID";
				break;
			}

			$UserID = get_assoc_default($FormData,"user_id",false);
			$TempList = ftree_get_item_links($GLOBALS["aib_db"],$ItemID,$UserID);
			if ($TempList == false)
			{
				$TempList = array();
			}

			$OutData = ftree_list_child_objects_ext($GLOBALS["aib_db"],false,$FormData,false,$BlockedProperties,$FieldDataTypeDesc,$FileClassTypeDesc,$TempList);
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
