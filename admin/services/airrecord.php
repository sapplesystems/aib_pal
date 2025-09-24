<?php
//
// airrecord.php (Ajax Info Request)
//
// Ajax handler
//

include("../config/aib.php");
include("../include/folder_tree.php");
include("../include/fields.php");
include("../include/aib_util.php");

function log_air_message($Msg)
{
	$Handle = fopen("/tmp/air_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function send_status($Status,$Info)
{
	$OutData = array("status" => $Status, "info" => $Info);
	print(json_encode($OutData));
}

function element_with_default($InArray,$Name,$Default)
{
	if (isset($InArray[$Name]) == false)
	{
		return($Default);
	}

	return($InArray[$Name]);
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


// Convert user type title to type code
// ------------------------------------
function aib_user_type_code_from_title($Code)
{
	switch(strtolower($Code))
	{
		case "root":
			return(AIB_USER_TYPE_ROOT);

		case "admin":
			return(AIB_USER_TYPE_ADMIN);

		case "user":
			return(AIB_USER_TYPE_USER);

		case "sub-admin":
		case "sub":
		case "subadmin":
			return(AIB_USER_TYPE_SUBADMIN);
			break;

		default:
			return("");
	}
}


// RENDER FUNCTIONS
// ================
// Render user type
// ----------------
function aib_render_user_type_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	switch($ColValue)
	{
		case AIB_USER_TYPE_ROOT:
			return("ROOT");

		case AIB_USER_TYPE_ADMIN:
			return("ADMIN");

		case AIB_USER_TYPE_USER:
			return("USER");

		case AIB_USER_TYPE_SUBADMIN:
			return("SUB-ADMIN");

		default:
			return("N/A");
	}
}

// Render user group
// -----------------
function aib_render_user_group_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$LocalResults = aib_db_query("SELECT * FROM ftree_group WHERE group_id=$ColValue;");
	if ($LocalResults == false)
	{
		return("N/A");
	}

	return($LocalResults[0]["group_title"]);
}

// Render archive title column
// ---------------------------
function aib_render_archive_title_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$ItemID = $Record["item_id"];
	$Title = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
	if ($Title != false)
	{
		return($Title);
	}

	return("N/A");
}

// Render archive owner column
// ---------------------------
function aib_render_archive_owner_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OwnerID = $Record["item_user_id"];
	$UserInfo = ftree_get_user($GLOBALS["aib_db"],$OwnerID);
	if ($UserInfo != false)
	{
		return($UserInfo["user_title"]);
	}

	return($OwnerID);
}

// Render field data type column
// -----------------------------
function aib_render_field_data_type_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	switch($ColValue)
	{
		case FTREE_FIELD_TYPE_TEXT:
			return("Text");

		case FTREE_FIELD_TYPE_BIGTEXT:
			return("Description");

		case FTREE_FIELD_TYPE_INTEGER:
			return("Integer");

		case FTREE_FIELD_TYPE_FLOAT:
			return("Number");

		case FTREE_FIELD_TYPE_DECIMAL:
			return("Decimal");

		case FTREE_FIELD_TYPE_DATE:
			return("Date");

		case FTREE_FIELD_TYPE_TIME:
			return("Time");

		case FTREE_FIELD_TYPE_DATETIME:
			return("Date And Time");

		case FTREE_FIELD_TYPE_TIMESTAMP:
			return("Time Stamp");

		case FTREE_FIELD_TYPE_DROPDOWN:
			return("Option List");

		default:
			return("Field");
	}

	return("Field");
}

// Render actions for records
// --------------------------
function aib_render_record_actions_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$NavString = aib_get_nav_string();
	$OutLines = array();
	if ($ExtraData == false)
	{
		return("");
	}

	// Determine the entry type

	$ItemID = $Record["item_id"];
	$ParentItemID = $Record["item_parent"];
	$CurrentPage = aib_get_with_default($ExtraData,"_page","1");
	$SearchValue = aib_get_with_default($ExtraData,"_searchval","");
	$PageKey = aib_get_with_default($ExtraData,"_key","");
	$PageMode = aib_get_with_default($ExtraData,"_pagemode","");
	$ArchiveCode = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
	$FolderType = ftree_get_property($GLOBALS["aib_db"],$ItemID,"aibftype");
	if ($FolderType == false)
	{
		$FolderType = AIB_ITEM_TYPE_ITEM;
	}

	if (isset($ExtraData["extra_param"]) != false)
	{
		$ExtraParam = $ExtraData["extra_param"];
	}
	else
	{
		$ExtraParam = array();
	}

	// Process each action type

	foreach($ExtraData as $Operation => $OpData)
	{
		// Don't process common parameters

		if (substr($Operation,0,1) == "_")
		{
			continue;
		}

		// Get image URL, if any

		if (isset($OpData["image"]) == true)
		{
			$Image = $OpData["image"];
		}
		else
		{
			$Image = false;
		}

		// Set up fields and mode title based on operation

		$SourceFields = false;
		$ModeName = false;
		$TargetPage = false;
		$TargetWindow = false;
		switch($Operation)
		{
			case "edit":
				if ($FolderType != AIB_ITEM_TYPE_ITEM)
				{
					$SourceFields = join("&",array("opcode=edit","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage","aibnav=$NavString"));
				}
				else
				{
					// If an item, determine the subgroup and record.  Parent of item is the record, parent of the record is the subgroup.

					$CurrentRecordID = $Record["item_parent"];
					$CurrentRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentRecordID);
					$CurrentSubgroupID = $CurrentRecord["item_parent"];
//					$SourceFields = join("&",array("opcode=_next_item","primary=$CurrentSubgroupID","record_id=$CurrentRecordID","item_id=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage","aibnav=$NavString"));
					$SourceFields = join("&",array("opcode=edit","primary=$ItemID","record_id=$CurrentRecordID","item_id=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage","aibnav=$NavString"));

				}

				$ModeName = "Edit";
				break;

			case "del":
				$SourceFields = join("&",array("opcode=del","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage","aibnav=$NavString"));
				$ModeName = "Delete";
				$TargetPage = "del_record_form.php";
				break;

			case "add":
				$SourceFields = join("&",array("opcode=add","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage","aibnav=$NavString"));
				$ModeName = "Add";
				break;

			case "ocr":
				$SourceFields = join("&",array("opcode=ocr","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage","aibnav=$NavString"));
				$ModeName = "OCR";
				break;

			case "view":
				if ($FolderType != AIB_ITEM_TYPE_ITEM && $FolderType != AIB_ITEM_TYPE_RECORD)
				{
					break;
				}

				$SourceFields = "parent=$ItemID";
				$TargetPage = "browse.php";
				$TargetWindow = "_blank";
				break;


			default:
				break;
		}

		// Add referral page

		if (isset($ExtraData["refsrc"]) == true)
		{
			$SourceFields .= "&refsrc=".$ExtraData["refsrc"];
		}

		// Get target page based on entry type and source referring page

		if ($SourceFields != false)
		{
			if ($TargetPage == false)
			{
				switch($FolderType)
				{
					case "col":
						$TargetPage = "collection_form.php";
						break;
	
	
					case "sg":
						$TargetPage = "subgroup_form.php";
						break;


					case "ar":
						$TargetPage = "admin_archiveform.php";
						break;
	
					case AIB_ITEM_TYPE_RECORD:
					case AIB_ITEM_TYPE_ITEM:
						$TargetPage = "record_modify.php";
						break;
	
					case false:
						break;
	
					default:
						break;
				}
			}

			// Create link

			if ($TargetPage != false)
			{
				if ($TargetWindow == false)
				{
					$OutLines[] = "<a href='/$TargetPage?$SourceFields' class='aib-list-action-link'>";
				}
				else
				{
					$OutLines[] = "<a href='/$TargetPage?$SourceFields' class='aib-list-action-link' target='$TargetWindow'>";
				}


				// If there's an image, use it.  Else just the mode title.

				if ($Image != false)
				{
					$OutLines[] = "<img src='$Image' class='aib-list-action-link-icon' title='$ModeName'>";
				}
				else
				{
					$OutLines[] = $ModeName;
				}

				$OutLines[] = "</a>";
			}

		}
	}

	return(join("",$OutLines));
}

// Render actions for records
// --------------------------
function aib_render_generic_actions_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OutLines = array();
	if ($ExtraData == false)
	{
		return("");
	}

	// Determine the entry type

	$CurrentPage = aib_get_with_default($ExtraData,"_page","1");
	$SearchValue = aib_get_with_default($ExtraData,"_searchval","");
	$PageKey = aib_get_with_default($ExtraData,"_key","");
	$PageMode = aib_get_with_default($ExtraData,"_pagemode","");
	$NavString = aib_get_nav_string();

	// Process each action type

	foreach($ExtraData as $Operation => $OpData)
	{
		// Don't process common parameters

		if (substr($Operation,0,1) == "_")
		{
			continue;
		}

		// Get image URL, if any

		if (isset($OpData["image"]) == true)
		{
			$Image = $OpData["image"];
		}
		else
		{
			$Image = false;
		}

		$PrimaryName = $OpData["primary"];
		$ItemID = $Record[$PrimaryName];

		// Set up fields and mode title based on operation

		$SourceFields = false;
		$ModeName = false;
		switch($Operation)
		{
			case "edit":
				$SourceFields = join("&",array("opcode=edit","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage"));
				$ModeName = "Edit";
				break;

			case "del":
				$SourceFields = join("&",array("opcode=del","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage"));
				$ModeName = "Delete";
				break;

			case "add":
				$SourceFields = join("&",array("opcode=add","primary=$ItemID","src=records","srckey=$PageKey","searchval=$SearchValue","srcmode=$PageMode","srcpn=$CurrentPage"));
				$ModeName = "Add";
				break;

			default:
				break;
		}

		// Get target page based on entry type

		if ($NavString != false)
		{
			$SourceFields .= "&aibnav=$NavString";
		}

		$TargetPage = $OpData["url"];

		// Create link

		$OutLines[] = "<a href='$TargetPage?$SourceFields' class='aib-list-action-link'>";

		// If there's an image, use it.  Else just the mode title.

		if ($Image != false)
		{
			$OutLines[] = "<img src='$Image' class='aib-list-action-link-icon' title='$ModeName'>";
		}
		else
		{
			$OutLines[] = $ModeName;
		}

		$OutLines[] = "</a>";
	}

	return(join("",$OutLines));
}

// Render actions column
// ---------------------
function aib_render_actions_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OutLines = array();
	if ($ExtraData == false)
	{
		return("");
	}

	$NavString = aib_get_nav_string();
	foreach($ExtraData as $Operation => $OpData)
	{
		$URL = $OpData["url"];
		$URL .= "?opcode=".$OpData["opcode"]."&primary=".$Record[$OpData["primary"]];
		if (isset($OpData["extra_fields"]) == true)
		{
			foreach($OpData["extra_fields"] as $ExtraName => $ExtraValue)
			{
				$URL .= "&"."$ExtraName=$ExtraValue";
			}
		}

		if ($NavString != false)
		{
			$URL .= "&aibnav=$NavString";
		}

		if (isset($OpData["image"]) == true)
		{
			$Link = "<a href=\"$URL\" class='aib-list-action-link'><img class='aib-list-action-link-icon' src=\"".$OpData["image"]."\" title=\"".$OpData["title"]."\"></a>";
		}
		else
		{
			$Link = "<a href=\"$URL\" class='aib-list-action-link'>".$OpData["title"]."</a>";
		}

		$OutLines[] = $Link;
	}

	return(join("&nbsp;",$OutLines));
}

// Render title column
// -------------------
function aib_render_item_title_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$DecodedColValue = urldecode($ColValue);
	$ItemID = $Record["item_id"];
	$EntryType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	switch($EntryType)
	{
		case false:
			$URL = "/browse.php?&parent=".$Record["item_id"];
			return("<span class='aib-item-no-link'>$DecodedColValue</span>");

		default:
			$ObjectType = $Record["item_type"];
			switch($ObjectType)
			{
				case FTREE_OBJECT_TYPE_LINK:
					$URL = "";
					switch($Record["item_source_type"])
					{
						case FTREE_SOURCE_TYPE_STPARCHIVE:
							$LinkInfo = json_decode(urldecode($Record["item_source_info"]),true);
							if (isset($LinkInfo["type"]) == false)
							{
								$URL = "/records.php?opcode=list&parent=".$Record["item_id"];
								return("<a class='aib-item-link' href='$URL'>$DecodedColValue</a>");
							}

							switch($LinkInfo["type"])
							{
								// Edition
								case FTREE_STP_LINK_EDITION:
									$URL = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
										$LinkInfo["year"]."/".stp_archive_month_name($LinkInfo["mon"])." ".
										$LinkInfo["day"]."/";
									$ThumbURL = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
										$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
									break;

								// Page
								case FTREE_STP_LINK_PAGE:
									$URL = "www.".STP_ARCHIVE_DOMAIN."/aib_page.php?edition=".
										$LinkInfo["ed"]."&page=".$LinkInfo["pg"];
									$ThumbURL = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
										$LinkInfo["ed"]."&page=".$LinkInfo["pg"]."&paper=".$LinkInfo["paper"];
									break;

								// Year
								case FTREE_STP_LINK_YEAR:
									$URL = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
										$LinkInfo["year"];
									$ThumbURL = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
										$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
									break;

								default:
									break;
							}

							$URL = "http://".$URL;
							return("<a class='aib-item-link' target='_blank' href='$URL'>$DecodedColValue</a>");
							break;

						default:
							break;
					}

					break;

				case FTREE_OBJECT_TYPE_FOLDER:
				case FTREE_OBJECT_TYPE_FILE:
				default:
					break;
			}

			break;
	}

	$URL = "/records.php?opcode=list&parent=".$Record["item_id"];
	return("<a class='aib-item-link' href='$URL'>$DecodedColValue</a>");
}

// Render item type column
// -----------------------
function aib_render_item_type_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$URL = "/records.php?opcode=list&parent=".$Record["item_id"];
	$LocalItemID = $Record["item_id"];
	switch($Record["item_type"])
	{
		case FTREE_OBJECT_TYPE_LINK:
			if ($Record["item_source_type"] == FTREE_SOURCE_TYPE_STPARCHIVE)
			{
				$LinkInfo = json_decode(urldecode($Record["item_source_info"]),true);
				$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
				$ThumbURL = "http://www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".  $LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
				$HTML .= "<img class='aib-list-record-link-icon' src='$ThumbURL'><br>STP Archive";
				$HTML .= "</td></tr></table>";
				return($HTML);
			}

			$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
			$ThumbURL = "/images/monoicons/exchange32.png";
			$HTML .= "<img class='aib-list-record-link-icon' src='$ThumbURL'><br>Link";
			$HTML .= "</td></tr></table>";
			return($HTML);
			break;


		case FTREE_OBJECT_TYPE_FOLDER:
			
			// See if there is an entry type from the properties

			$ItemID = $Record["item_id"];
			$EntryType = ftree_get_property($GLOBALS["aib_db"],$ItemID,"aibftype");
			switch($EntryType)
			{
				// Archive

				case "ar":
					$HTML = "<table cellpadding='0' cellspacing='0'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
					$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Archive";
					$ItemCount = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,true);
					if ($ItemCount !== false)
					{
						$HTML .= sprintf("<br>%d Collections",$ItemCount);
					}

					$HTML .= "</td></tr></table>";
					break;

				// Collection

				case "col":
					$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
					$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/box32.png'><br>Collection";
					$ItemCount = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,true);
					if ($ItemCount !== false)
					{
						$HTML .= sprintf("<br>%d Sub-Groups",$ItemCount);
					}

					$HTML .= "</td></tr></table>";
					break;

				// Sub-group

				case "sg":
					$HTML = "<table cellpadding='0' cellspacing='0'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
					$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Sub-Group";
					$SubList = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
					$SubCount = 0;
					$RecCount = 0;
					foreach($SubList as $SubRecord)
					{
						$FolderType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
						if ($FolderType != false)
						{
							if ($FolderType == AIB_ITEM_TYPE_SUBGROUP)
							{
								$SubCount++;
								continue;
							}

							if ($FolderType == AIB_ITEM_TYPE_RECORD)
							{
								$RecCount++;
								continue;
							}
						}
					}

					if ($SubList !== false)
					{
						$HTML .= sprintf("<br>%d Sub<br>%d Rec",$SubCount,$RecCount);
					}

					$HTML .= "</td></tr></table>";
					break;

				// Record

				case "rec":
					$ThumbID = -1;
//					$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/article32.png'>Record";
					$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
					$ThumbID = aib_get_first_record_thumb($GLOBALS["aib_db"],$ItemID);
					if ($ThumbID > 0)
					{
						$HTML .= "<img class='aib-list-record-link-icon' src='/get_thumb.php?id=$ThumbID'><br>Record";
					}
					else
					{
						$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/article32.png'><br>Record";
					}

					$ItemCount = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FILE,true);
					if ($ItemCount !== false)
					{
						$HTML .= sprintf("<br>%d Items",$ItemCount);
					}

					$HTML .= "</td></tr></table>";
					break;

				// All others

				default:
					switch($Record["item_source_type"])
					{
						case FTREE_SOURCE_TYPE_STPARCHIVE:
							$LinkInfo = json_decode(urldecode($Record["item_source_info"]),true);
							$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
							$URL = "http://www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
									$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
							$HTML .= "<img class='aib-list-record-link-icon' src='$URL'><br>STP Archive";
							break;

						default:
							$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
							$FolderType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
							switch($FolderType)
							{
								case AIB_ITEM_TYPE_ARCHIVE_GROUP:
									$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Archive Group";
									break;

								case AIB_ITEM_TYPE_ARCHIVE:
									$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Archive";
									break;

								case AIB_ITEM_TYPE_COLLECTION:
									$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Collection";
									break;

								case AIB_ITEM_TYPE_SUBGROUP:
									$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Sub-Group";
									break;

								case AIB_ITEM_TYPE_RECORD:
									$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Record";
									break;

								default:
									$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'><br>Item";
									break;
							}

							$HTML .= "</td></tr></table>";
							break;
					}

					break;
			}

			return($HTML);

		case FTREE_OBJECT_TYPE_FILE:
//			$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/linedpaper32.png'>File";
			$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$LocalItemID);
			$ThumbID = -1;
			$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
			foreach($FileList as $FileRecord)
			{
				if ($FileRecord["file_class"] == AIB_FILE_CLASS_THUMB)
				{
					$ThumbID = $FileRecord["record_id"];
					break;
				}
			}

			$HTML .= "<img class='aib-list-record-link-icon' src='/get_thumb.php?id=$ThumbID'><br>File";
			$HTML .= "</td></tr></table>";
			return($HTML);

		case FTREE_OBJECT_TYPE_LINK:
			$HTML = "<table cellpadding='0' cellspacing='0' class='aib-icon-col-table'><tr class='aib-icon-col-table-row'><td align='center' colspan='99' class='aib-icon-col-table-cell'>";
			$HTML .= "<img class='aib-list-record-link-icon' src='/images/monoicons/exchange32.png'><br>Link";
			$HTML .= "</td></tr></table>";
			return($HTML);

		default:
			return($ColValue);
	}

	return($ColValue);
}

// Render time stamp for item creation
// -----------------------------------
function aib_render_item_create_stamp_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$LocalStamp = intval($ColValue);
	$OutValue = date("m/d/Y h:i:s A",$LocalStamp);
	return($OutValue);
}

// Given an item ID, return the type
// ---------------------------------
function airrecord_get_item_type($DBHandle,$ItemID,$IncludeArchiveFlag = false)
{
	$ItemRecord = ftree_get_item($DBHandle,$ItemID);
	if ($ItemRecord == false)
	{
		return(false);
	}

	// See if there is a type property.  If so, that determines the type.

	$TypeProperty = ftree_get_property($DBHandle,$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	if ($TypeProperty != false)
	{
		$OutValue = array("type" => $TypeProperty);
		if ($IncludeArchiveFlag != false)
		{
			$ArchiveFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
			if ($ArchiveFolderID !== false)
			{
				$IDPathList = ftree_get_item_id_path($GLOBALS["aib_db"],$ItemID);
				foreach($IDPathList as $LocalID)
				{
					$ArchiveCodeProp = ftree_get_property($GLOBALS["aib_db"],$LocalID,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
					if ($ArchiveCodeProp != false)
					{
						$ArchiveTitle = urldecode($ItemRecord["item_title"]);
						$ArchiveCodeTitle = urldecode($ArchiveCodeProp);
						$FullTitle .= " -- $ArchiveCodeTitle";
						$OutValue["archive_id"] = $LocalID;
						$OutValue["archive_title"] = $FullTitle;
						$OutValue["idpath"] = $IDPathList;
						$OutValue["title"] = urldecode($ItemRecord["item_title"]);
						break;
					}
				}
			}
		}
		else
		{
			$OutValue["title"] = urldecode($ItemRecord["item_title"]);
		}

		return($OutValue);
	}

	// If there is an archive name, it's an archive

	$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
	if ($ArchiveNameProp != false)
	{
		$OutValue = array("type" => AIB_ITEM_TYPE_ARCHIVE);
		if ($IncludeArchiveFlag != false)
		{
			$ArchiveTitle = $ArchiveNameProp;
			$ArchiveCodeTitle = urldecode($ItemRecord["item_title"]);
			$FullTitle = $ArchiveNameProp." ($ArchiveCodeTitle)";
			$OutValue["archive_id"] = $LocalID;
			$OutValue["archive_title"] = $FullTitle;
			$OutValue["idpath"] = ftree_get_item_id_path($GLOBALS["aib_db"],$ItemID);
			$OutValue["title"] = urldecode($ItemRecord["item_title"]);
		}
		else
		{
			$OutValue["title"] = urldecode($ItemRecord["item_title"]);
		}
	
		return($OutValue);
	}

	$OutValue = array("type" => "", "archive_id" => "", "archive_title" => "", "idpath" => false);
	return($OutValue);
}

// Given an ID path, generate a name path for column display
// ---------------------------------------------------------
function aib_generate_name_path($DBHandle,$IDPathList)
{
	$PathNames = array();
	foreach($IDPathList as $IDValue)
	{
		$ItemRecord = ftree_get_item($DBHandle,$ItemID);
		if ($ItemRecord == false)
		{
			$PathNames[] = "*";
		}
		else
		{
			$PathNames[] = urldecode($ItemRecord["item_title"]);
		}
	}

	$PathText = join("=>",$PathNames);
	if (strlen($PathText) > 32)
	{
		$PathText = "...".substr($PathText,-30);
	}

	return($PathText);
}



// Render ownership column for field list
// --------------------------------------
function aib_render_field_owner_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OwnerType = $Record["field_owner_type"];
	$OwnerID = $Record["field_owner_id"];
	switch($OwnerType)
	{
		case FTREE_OWNER_TYPE_SYSTEM:
			return("System: Traditional");

		case FTREE_OWNER_TYPE_RECOMMENDED:
			return("System: Recommended");

		case FTREE_OWNER_TYPE_GROUP:
			$GroupRecord = ftree_get_group_by_id($GLOBALS["aib_db"],$OwnerID);
			if ($GroupRecord == false)
			{
				return("Unknown Group");
			}

			return("Group: ".$GroupRecord["group_title"]);

		case FTREE_OWNER_TYPE_USER:
			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$OwnerID);
			if ($UserRecord == false)
			{
				return("Unknown User");
			}

			return("User: ".$UserRecord["user_title"]);

		case FTREE_OWNER_TYPE_FORM:
			$FormRecord = ftree_field_get_form($GLOBALS["aib_db"],$OwnerID);
			if ($FormRecord == false)
			{
				return("Unknown Form");
			}

			return("Form: ".$UserRecord["form_title"]);

		case FTREE_OWNER_TYPE_ITEM:
			$FieldOwnerID = $Record["field_owner_id"];
			if (isset($Record["_owner_info"]) == true)
			{
				$OwnerInfo = $Record["_owner_info"];
				if ($OwnerInfo["_archive_id"] == $FieldOwnerID)
				{
					return($OwnerInfo["_archive_title"]);
				}

				if ($OwnerInfo["_archive_group_id"] == $FieldOwnerID)
				{
					return($OwnerInfo["_archive_group_title"]);
				}
			}

			return("Unknown Item");

		default:
			break;
	}

	return("System");
}

// Render ownership column for form list
// --------------------------------------
function aib_render_form_owner_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OwnerType = $Record["form_owner_type"];
	$OwnerID = $Record["form_owner"];
	switch($OwnerType)
	{
		case FTREE_OWNER_TYPE_SYSTEM:
			return("SYSTEM: Traditional");

		case FTREE_OWNER_TYPE_RECOMMENDED:
			return("SYSTEM: Recommended");

		case FTREE_OWNER_TYPE_GROUP:
			$GroupRecord = ftree_get_group_by_id($GLOBALS["aib_db"],$OwnerID);
			if ($GroupRecord == false)
			{
				return("Unknown Group");
			}

			return("GROUP: ".$GroupRecord["group_title"]);

		case FTREE_OWNER_TYPE_USER:
			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$OwnerID);
			if ($UserRecord == false)
			{
				return("Unknown User");
			}

			return("USER: ".$UserRecord["user_title"]);

		case FTREE_OWNER_TYPE_ITEM:
			$FieldOwnerID = $Record["form_owner"];
			$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$FieldOwnerID);
			if ($ItemRecord == false)
			{
				return("N/A");
			}

			$ItemType = ftree_get_property($GLOBALS["aib_db"],$FieldOwnerID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			switch($ItemType)
			{
				case AIB_ITEM_TYPE_ARCHIVE:
					$ArchiveCode = ftree_get_property($GLOBALS["aib_db"],$FieldOwnerID,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
					return("ARCHIVE: ".urldecode($ItemRecord["item_title"])." ($ArchiveCode)");

				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					$ArchiveCode = ftree_get_property($GLOBALS["aib_db"],$FieldOwnerID,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
					return("ARCHIVE GROUP: ".urldecode($ItemRecord["item_title"])." ($ArchiveCode)");

				case AIB_ITEM_TYPE_COLLECTION:
					return("COLLECTION: ".urldecode($ItemRecord["item_title"]));

				case AIB_ITEM_TYPE_SUBGROUP:
					return("SUB-GROUP: ".urldecode($ItemRecord["item_title"]));

				case AIB_ITEM_TYPE_RECORD:
					return("RECORD: ".urldecode($ItemRecord["item_title"]));

				default:
					return("N/A");
			}

			return("N/A");

		default:
			break;
	}

	return("System");
}

// Get the owner archive and archive group information based on the owner ID and type
// ----------------------------------------------------------------------------------
function get_field_owner_info($DBHandle,$OwnerType,$OwnerID,&$OwnerCache)
{
	$Key = $OwnerType.$OwnerID;
	$OwnerRecord = array();
	$OwnerRecord["_owner_type"] = "";
	$OwnerRecord["_user_title"] = "";
	$OwnerRecord["_user_id"] = -1;
	$OwnerRecord["_archive_title"] = false;
	$OwnerRecord["_archive_id"] = -1;
	$OwnerRecord["_archive_group_title"] = false;
	$OwnerRecord["_archive_group_id"] = -1;
	if (isset($OwnerCache[$Key]) == false)
	{
		switch($OwnerType)
		{
			case FTREE_OWNER_TYPE_ITEM:
				$OwnerRecord = ftree_get_item($DBHandle,$OwnerID);
				if ($OwnerRecord == false)
				{
					break;
				}

				$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$OwnerID);
				if ($ArchiveInfo["archive"] != false)
				{
					$OwnerRecord["_archive_title"] = urldecode($ArchiveInfo["archive"]["item_title"]);
					$OwnerRecord["_archive_id"] = $ArchiveInfo["archive"]["item_id"];
				}
				else
				{
					$OwnerRecord["_archive_title"] = false;
					$OwnerRecord["_archive_id"] = false;
				}

				if ($ArchiveInfo["archive_group"] != false)
				{
					$OwnerRecord["_archive_group_title"] = urldecode($ArchiveInfo["archive_group"]["item_title"]);
					$OwnerRecord["_archive_group_id"] = $ArchiveInfo["archive_group"]["item_id"];
				}
				else
				{
					$OwnerRecord["_archive_group_title"] = false;
					$OwnerRecord["_archive_group_id"] = false;
				}

				$OwnerCache[$Key] = $OwnerRecord;
				break;

			case FTREE_OWNER_TYPE_USER:
				$OwnerRecord["_archive_title"] = false;
				$OwnerRecord["_archive_id"] = -1;
				$OwnerRecord["_archive_group_title"] = false;
				$OwnerRecord["_archive_group_id"] = -1;
				$UserRecord = ftree_get_user($DBHandle,$OwnerID);
				if ($UserRecord != false)
				{
					$OwnerRecord["_user_title"] = $UserRecord["user_title"];
					$OwnerRecord["_user_id"] = $UserRecord["user_id"];
				}
				else
				{
					$OwnerRecord["_user_title"] = "";
					$OwnerRecord["_user_id"] = -1;
				}

				$OwnerCache[$Key] = $OwnerRecord;
				break;

			case FTREE_OWNER_TYPE_SYSTEM:
			case FTREE_OWNER_TYPE_RECOMMENDED:
				$OwnerCache[$Key] = $OwnerRecord;
				break;

			default:
				$OwnerRecord["_archive_title"] = false;
				$OwnerRecord["_archive_id"] = -1;
				$OwnerRecord["_archive_group_title"] = false;
				$OwnerRecord["_archive_group_id"] = -1;
				$OwnerRecord["_user_title"] = "";
				$OwnerRecord["_user_id"] = -1;
				$OwnerCache[$Key] = $OwnerRecord;
				break;
		}

		$OwnerRecord = $OwnerCache[$Key];
	}
	else
	{
		$OwnerRecord = $OwnerCache[$Key];
	}

	return($OwnerRecord);
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

// Filter out fields that should not appear in section 2 (Designate Your Fields)
// -----------------------------------------------------------------------------
function skip_system_field($InField)
{
	$SymbolicName = $InField["field_symbolic_name"];
	if (isset($GLOBALS["aib_hide_predef_field_list"][$SymbolicName]) == false)
	{
		return(false);
	}

	return(true);
}


// #########
// MAIN CODE
// #########

	// Get form

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();

	// Get opcode.  If not present, error

	$OpCode = element_with_default($FormData,"o",false);
	if ($OpCode == false)
	{
		aib_log_message("ERROR","airrecord.php","Missing opcode");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	$OpCode = hex2bin($OpCode);

	// Get session ID.  Error if not present or bad value.

	$SessionID = element_with_default($FormData,"s",false);
	if ($SessionID === false)
	{
		aib_log_message("ERROR","airrecord.php","Missing session");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	// Get user record ID.  Error if not present or bad value.

	$UserID = element_with_default($FormData,"i",false);
	if ($UserID === false)
	{
		aib_log_message("ERROR","airrecord.php","Missing user ID");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	$UserID = intval(hex2bin($UserID));
	if ($UserID < 0)
	{
		aib_log_message("ERROR","airrecord.php","User ID is invalid (less than zero)");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	$SearchValue = "";
	$ParentPathList = false;
	$ParentPathLink = false;
	$ParentFolderLink = false;
	$ListParam = array();
	$ListParam["extra_title_rows"] = array();
	$PageKey = aib_get_with_default($FormData,"key","");
	switch($OpCode)
	{
		// Test response
		case "ts":
			send_status("OK",array("msg" => "Test response","value" => date("H:i:s")));
			exit(0);

		// Get all fields for an object

		case "loadfields":
			$ItemID = aib_get_with_default($FormData,"objid",false);
			if ($ItemID == false)
			{
				send_status("ERROR",array("msg" => "Missing item ID"));
				exit(0);
			}

			aib_open_db();
			$FormID = ftree_field_get_item_form($GLOBALS["aib_db"],$ItemID);
			if ($FormID == false)
			{
				$FormID = "";
			}


			// Get all fields for object

//			$ItemFields = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ItemID);
			$ItemFields = get_item_fields_in_form_order($GLOBALS["aib_db"],$ItemID,$FormID);
			if ($ItemFields == false)
			{
				$ItemFields = array();
			}

			// Get all of the fields defined for the form

			aib_close_db();
			$OutLines = array();
			$OutInfo = array();
			$FieldList = array();
			foreach($ItemFields as $ItemFieldRecord)
			{
				$FieldRecord = $ItemFieldRecord["def"];
				if (skip_system_field($FieldRecord) == true)
				{
					continue;
				}

				$FieldValue = urldecode($ItemFieldRecord["value"]);
				$FieldID = $FieldRecord["field_id"];
				$FieldList[] = $FieldID;
				$RowName = "userfield_".$FieldID."_field";
				$OutLines[] = "<tr class='aib-user-def-field-row' id='$RowName'><td class='aib-user-def-field-title-cell'>".urldecode($FieldRecord["field_title"])."</td>";
				$OutLines[] = "<td class='aib-input-title-divider-cell'> </td><td class='aib-user-def-field-input-cell'>";

				// Create HTML based on the field type

				$HTMLFieldID = "userfield_".$FieldID."_field";
				switch($FieldRecord["field_data_type"])
				{
					case FTREE_FIELD_TYPE_TEXT:
					case FTREE_FIELD_TYPE_INTEGER:
					case FTREE_FIELD_TYPE_DATE:
					case FTREE_FIELD_TYPE_TIME:
						$Size = $FieldRecord["field_size"];
						if (intval($Size) <= 0)
						{
							$Size = 32;
						}
	
						$OutLines[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value=\"$FieldValue\" size='$Size' class='aib-record-text-input-field'>";
						break;
	
					case FTREE_FIELD_TYPE_DATETIME:
						$OutLines[] = "<input type='datetime' name='$HTMLFieldID' id='$HTMLFieldID' value=\"$FieldValue\" size='$Size' class='aib-record-datetime-input-field'>";
						break;

					case FTREE_FIELD_TYPE_TIMESTAMP:
						$OutLines[] = "<input type='hidden' name='$HTMLFieldID' id='$HTMLFieldID' value='$FieldValue' class='aib-record-timestamp-input-field'>";
						break;
	
					case FTREE_FIELD_TYPE_BIGTEXT:
						$Segs = preg_split("/[ \,\/\:\;]+/",$FieldRecord["field_size"]);
						$Rows = 5;
						$Cols = 40;
						$OutLines[] = "<textarea name='$HTMLFieldID' id='$HTMLFieldID' rows='$Rows' cols='$Cols' class='aib-record-textarea-input-field'>$FieldValue</textarea>";
						break;
	
					case FTREE_FIELD_TYPE_FLOAT:
					case FTREE_FIELD_TYPE_DECIMAL:
						$Segs = preg_split("/[ \,\/\:\;]+/",$FieldRecord["field_size"]);
						$Size = 10;
						if (count($Segs) >= 1)
						{
							$Size = $Segs[0];
						}
	
						$OutLines[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value=\"$FieldValue\" size='$Size' class='aib-record-floatdecimal-input-field'>";
						break;
	
					case FTREE_FIELD_TYPE_DROPDOWN:
						$RawList = explode("\n",urldecode($FieldRecord["field_format"]));
						$OptionList = array();
						foreach($RawList as $OptionLine)
						{
							$Segs = explode("=",$OptionLine);
							if (count($Segs) < 1)
							{
								continue;
							}
	
							if (count($Segs) < 2)
							{
								$OptionList[$Segs[0]] = $Segs[0];
							}
							else
							{
								$OptionList[$Segs[0]] = $Segs[1];
							}
						}
	
						$OutLines[] = "<select name='$HTMLFieldID' id='$HTMLFieldID'>";
						foreach($OptionList as $Value => $Desc)
						{
							if ($Value == $FieldValue)
							{
								$OutLines[] = "<option value=\"$Value\" SELECTED >$Desc</option>";
							}
							else
							{
								$OutLines[] = "<option value=\"$Value\">$Desc</option>";
							}
						}
	
						break;
	
					default:
						$OutLines[] = "<input type='text' name='$HTMLFieldID' value=\"$FieldValue\" size='".$FieldRecord["field_size"]."' class='aib-record-dropdown-input-field'>";
						break;
				}

				$OutLines[] = "</td>";
				$OutLines[] = "<td class='aib-input-explain-divide-cell'> </td>";
				$OutLines[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
				$OutLines[] = "<tr class='aib-user-def-field-sep-row'><td colspan='99' class='aib-user-def-field-sep-col'> </td></tr>";
			}

			$OutInfo['html'] = join("\n",$OutLines);
			$OutInfo['field_list'] = join(",",$FieldList);
			send_status("OK",$OutInfo);
			exit(0);
			
		// Add field
		case "addfield":
			$FieldID = aib_get_with_default($FormData,"fi",false);
			if ($FieldID == false)
			{
				send_status("ERROR",array("msg" => "Missing field ID"));
				exit(0);
			}

			aib_open_db();
			$FieldRecord = ftree_field_get_field($GLOBALS["aib_db"],$FieldID);
			aib_close_db();
			if ($FieldRecord == false)
			{
				send_status("ERROR",array("msg" => "Field definition not found"));
				exit(0);
			}

			// Create HTML based on the field type

			$OutLines = array();
			$OutInfo = array();
			$HTMLFieldID = "userfield_".$FieldID."_field";
			switch($FieldRecord["field_data_type"])
			{
				case FTREE_FIELD_TYPE_TEXT:
				case FTREE_FIELD_TYPE_INTEGER:
				case FTREE_FIELD_TYPE_DATE:
				case FTREE_FIELD_TYPE_TIME:
					$Size = $FieldRecord["field_size"];
					if (intval($Size) <= 0)
					{
						$Size = 32;
					}

					$OutLines[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value='' size='$Size' class='aib-record-text-input-field'>";
					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";
					break;

				case FTREE_FIELD_TYPE_DATETIME:
					$Size = $FieldRecord["field_size"];
					if (intval($Size) <= 0)
					{
						$Size = 32;
					}

					$OutLines[] = "<input type='datetime' name='$HTMLFieldID' id='$HTMLFieldID' value='' size='$Size' class='aib-record-datetime-input-field'>";
					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";

				case FTREE_FIELD_TYPE_TIMESTAMP:
					$LocalValue = time();
					$OutLines[] = "<input type='hidden' name='$HTMLFieldID' id='$HTMLFieldID' value='$LocalValue' class='aib-record-timestamp-input-field'>";
					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";
					break;

				case FTREE_FIELD_TYPE_BIGTEXT:
					$Segs = preg_split("/[ \,\/\:\;]+/",$FieldRecord["field_size"]);
					$Rows = 5;
					$Cols = 40;
//					if (count($Segs) == 1)
//					{
//						$Cols = $Segs[0];
//					}
//					else
//					{
//						if (count($Segs) > 1)
//						{
//							$Rows = $Segs[0];
//							$Cols = $Segs[1];
//						}
//					}

					$OutLines[] = "<textarea name='$HTMLFieldID' id='$HTMLFieldID' rows='$Rows' cols='$Cols' class='aib-record-textarea-input-field'></textarea>";
					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";
					break;

				case FTREE_FIELD_TYPE_FLOAT:
				case FTREE_FIELD_TYPE_DECIMAL:
					$Segs = preg_split("/[ \,\/\:\;]+/",$FieldRecord["field_size"]);
					$Size = 10;
					if (count($Segs) >= 1)
					{
						$Size = $Segs[0];
					}

					$OutLines[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value='' size='$Size' class='aib-record-floatdecimal-input-field'>";
					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";
					break;

				case FTREE_FIELD_TYPE_DROPDOWN:
					$RawList = explode("\n",urldecode($FieldRecord["field_format"]));
					$OptionList = array();
					foreach($RawList as $OptionLine)
					{
						$Segs = explode("=",$OptionLine);
						if (count($Segs) < 1)
						{
							continue;
						}

						if (count($Segs) < 2)
						{
							$OptionList[$Segs[0]] = $Segs[0];
						}
						else
						{
							$OptionList[$Segs[0]] = $Segs[1];
						}
					}

					$OutLines[] = "<select name='$HTMLFieldID' id='$HTMLFieldID' class='aib-record-dropdown-input-field'>";
					foreach($OptionList as $Value => $Desc)
					{
						$OutLines[] = "<option value=\"$Value\">$Desc</option>";
					}

					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";
					break;

				default:
					$OutLines[] = "<input type='text' name='$HTMLFieldID' value='' size='".$FieldRecord["field_size"]."'>";
					$OutInfo["input"] = join("\n",$OutLines);
					$OutInfo["title"] = urldecode($FieldRecord["field_title"]);
					$OutInfo["desc"] = "";
					$OutInfo["field_id"] = $FieldID;
					$OutInfo["value"] = "";
					break;
			}

			send_status("OK",$OutInfo);
			exit(0);

		// Add a form.  Essentially the "add field" operation but for all fields on a form
		case "addform":
			$FormID = aib_get_with_default($FormData,"fi",false);
			if ($FormID == false)
			{
				send_status("ERROR",array("msg" => "Missing form ID"));
				exit(0);
			}

			aib_open_db();
			$FieldRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormID);
			if ($FieldRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Form definition not found"));
				exit(0);
			}

			// Get all of the fields defined for the form

			$FormFieldRecords = ftree_field_get_form_fields($GLOBALS["aib_db"],$FormID);
			aib_close_db();
			$OutLines = array();
			$OutInfo = array();
			$FieldList = array();
			foreach($FormFieldRecords as $FormFieldRecord)
			{
				$FieldRecord = $FormFieldRecord["field_record"];
				$FieldID = $FieldRecord["field_id"];
				$FieldList[] = $FieldID;
				$RowName = "userfield_".$FieldID."_field";
				$OutLines[] = "<tr class='aib-user-def-field-row' id='$RowName'><td class='aib-user-def-field-title-cell'>".urldecode($FieldRecord["field_title"])."</td>";
				$OutLines[] = "<td class='aib-input-title-divider-cell'> </td><td class='aib-user-def-field-input-cell'>";

				// Create HTML based on the field type

				$HTMLFieldID = "userfield_".$FieldID."_field";
				switch($FieldRecord["field_data_type"])
				{
					case FTREE_FIELD_TYPE_TEXT:
					case FTREE_FIELD_TYPE_INTEGER:
					case FTREE_FIELD_TYPE_DATE:
					case FTREE_FIELD_TYPE_TIME:
						$Size = $FieldRecord["field_size"];
						if (intval($Size) <= 0)
						{
							$Size = 32;
						}
	
						$OutLines[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value='' size='$Size' class='aib-record-text-input-field'>";
						break;
	
					case FTREE_FIELD_TYPE_DATETIME:
						$OutLines[] = "<input type='datetime' name='$HTMLFieldID' id='$HTMLFieldID' value='' size='$Size' class='aib-record-datetime-input-field'>";
						break;

					case FTREE_FIELD_TYPE_TIMESTAMP:
						$LocalValue = time();
						$OutLines[] = "<input type='hidden' name='$HTMLFieldID' id='$HTMLFieldID' value='$LocalValue' class='aib-record-timestamp-input-field'>";
						break;
	
					case FTREE_FIELD_TYPE_BIGTEXT:
						$Segs = preg_split("/[ \,\/\:\;]+/",$FieldRecord["field_size"]);
						$Rows = 5;
						$Cols = 40;
//						if (count($Segs) == 1)
//						{
//							$Rows = $Segs[0];
//						}
//						else
//						{
//							if (count($Segs) > 1)
//							{
//								$Rows = $Segs[0];
//								$Cols = $Segs[1];
//							}
//						}
	
						$OutLines[] = "<textarea name='$HTMLFieldID' id='$HTMLFieldID' rows='$Rows' cols='$Cols' class='aib-record-textarea-input-field'></textarea>";
						break;
	
					case FTREE_FIELD_TYPE_FLOAT:
					case FTREE_FIELD_TYPE_DECIMAL:
						$Segs = preg_split("/[ \,\/\:\;]+/",$FieldRecord["field_size"]);
						$Size = 10;
						if (count($Segs) >= 1)
						{
							$Size = $Segs[0];
						}
	
						$OutLines[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value='' size='$Size' class='aib-record-floatdecimal-input-field'>";
						break;
	
					case FTREE_FIELD_TYPE_DROPDOWN:
						$RawList = explode("\n",urldecode($FieldRecord["field_format"]));
						$OptionList = array();
						foreach($RawList as $OptionLine)
						{
							$Segs = explode("=",$OptionLine);
							if (count($Segs) < 1)
							{
								continue;
							}
	
							if (count($Segs) < 2)
							{
								$OptionList[$Segs[0]] = $Segs[0];
							}
							else
							{
								$OptionList[$Segs[0]] = $Segs[1];
							}
						}
	
						$OutLines[] = "<select name='$HTMLFieldID' id='$HTMLFieldID'>";
						foreach($OptionList as $Value => $Desc)
						{
							$OutLines[] = "<option value=\"$Value\">$Desc</option>";
						}
	
						break;
	
					default:
						$OutLines[] = "<input type='text' name='$HTMLFieldID' value='' size='".$FieldRecord["field_size"]."' class='aib-record-dropdown-input-field'>";
						break;
				}

				$OutLines[] = "</td>";
				$OutLines[] = "<td class='aib-input-explain-divide-cell'> </td>";
				$OutLines[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
				$OutLines[] = "<tr class='aib-user-def-field-sep-row'><td colspan='99' class='aib-user-def-field-sep-col'> </td></tr>";
			}

			$OutInfo['html'] = join("\n",$OutLines);
			$OutInfo['field_list'] = join(",",$FieldList);
			send_status("OK",$OutInfo);
			exit(0);
			
		// Remove form

		case "removeform":
			$FormID = aib_get_with_default($FormData,"fi",false);
			if ($FormID == false)
			{
				send_status("ERROR",array("msg" => "Missing form ID"));
				exit(0);
			}

			aib_open_db();
			$FieldRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormID);
			if ($FieldRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Form definition not found"));
				exit(0);
			}

			// Get all of the fields defined for the form

			$FormFieldRecords = ftree_field_get_form_fields($GLOBALS["aib_db"],$FormID);
			aib_close_db();
			$FieldList = array();
			foreach($FormFieldRecords as $Record)
			{
				$FieldList[] = $Record["field_record"]["field_id"];
			}

			$FieldListString = join(",",$FieldList);
			send_status("OK",array("html" => "", "field_list" => $FieldListString));
			exit(0);

		// Get a list of field definitions
		case "lfd":

			$HTML = "";

			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the page number and number of items per page

			$ParentFolderID = aib_get_with_default($FormData,"key",false);
			$PageNumber = aib_get_with_default($FormData,"pn","1");
			$PageItemCount = aib_get_with_default($FormData,"pic","10");
			$StartItem = ($PageNumber - 1) * $PageItemCount;
			if ($StartItem < 0)
			{
				$StartItem = 0;
			}

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			aib_open_db();

			// Get the user ID.  This determines what fields will be shown.

			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Cannot retrieve user profile"));
				exit(0);
			}

			// Get the available archives for the user

			$UserGroup = $UserRecord["user_primary_group"];
			$UserType = $UserRecord["user_type"];
			$UpdatedItemCount = 0;
			$EmptyListMessage = "";
			$ResultList = array();
			if ($ListOp == "list")
			{
				switch($UserType)
				{
					case AIB_USER_TYPE_ROOT:
						$LocalList = ftree_list_fields($GLOBALS["aib_db"],false);
						if ($LocalList == false)
						{
							$ResultList = array();
						}
						else
						{
							$ResultList = $LocalList;
						}

						break;

					case AIB_USER_TYPE_ADMIN:
					case AIB_USER_TYPE_SUBADMIN:
					case AIB_USER_TYPE_USER:
					default:
						$ResultList = array();
						$ArchiveGroupMap = array();
						$ArchiveMap = array();
						$FieldMap = array();
						$UserFieldSet = array();
						$FieldSet = array();

						// Get all available archives

						$AvailableArchives = aib_get_available_archives($GLOBALS["aib_db"],$UserID);

						// Get the list of fields owned by each archive and archive group

						foreach($AvailableArchives as $ArchiveRecord)
						{
							$ArchiveGroupID = $ArchiveRecord["item_parent"];
							$ArchiveID = $ArchiveRecord["item_id"];
							if (isset($ArchiveGroupMap[$ArchiveGroupID]) == false)
							{
								$ArchiveGroupMap[$ArchiveGroupID] = ftree_get_item($GLOBALS["aib_db"],$ArchiveGroupID);
								$FieldSet[$ArchiveGroupID] = array();
							}

							if (isset($ArchiveMap[$ArchiveID]) == false)
							{
								$ArchiveMap[$ArchiveID] = ftree_get_item($GLOBALS["aib_db"],$ArchiveID);
								$FieldSet[$ArchiveGroupID][$ArchiveID] = array();
							}

							// Get fields assigned to archive group

							$LocalList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OBJECT_TYPE_ITEM,$ArchiveRecord["item_parent"]);
							foreach($LocalList as $TempRecord)
							{
								$FieldID = $TempRecord["field_id"];
								$FieldMap[$FieldID] = $TempRecord;
								if (isset($FieldSet[$ArchiveGroupID][-1]) == false)
								{
									$FieldSet[$ArchiveGroupID][-1] = array();
								}

								$FieldSet[$ArchiveGroupID][-1][$FieldID] = $TempRecord;
							}

							// Get fields assigned to archive

							$LocalList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OBJECT_TYPE_ITEM,$ArchiveRecord["item_id"]);
							foreach($LocalList as $TempRecord)
							{
								$FieldID = $TempRecord["field_id"];
								$FieldMap[$TempRecord["field_id"]] = $TempRecord;
								$FieldSet[$ArchiveGroupID][$ArchiveID][$FieldID] = $TempRecord;
							}
						}

						// Get the list of fields that are owned by the user

						$LocalList = ftree_list_fields($GLOBALS["aib_db"],$UserID);
						foreach($LocalList as $TempRecord)
						{
							$FieldMap[$TempRecord["field_id"]] = $TempRecord;
							$UserFieldSet[$TempRecord["field_id"]] = $TempRecord;
						}

						// Create output list such that archive fields appear under their respective archive groups, and
						// user-owned fields appear at the end.

						foreach($FieldSet as $ArchiveGroupID => $ArchiveGroupSet)
						{
							foreach($ArchiveGroupSet as $ArchiveID => $ArchiveFieldSet)
							{
								foreach($ArchiveFieldSet as $FieldID => $FieldRecord)
								{
									if (isset($UserFieldSet[$FieldID]) == false)
									{
										$NewRecord = $FieldRecord;
										while(true)
										{
											if ($FieldRecord["field_owner_id"] == $ArchiveGroupID)
											{
												$NewRecord["_item_type"] = AIB_ITEM_TYPE_ARCHIVE_GROUP;
												break;
											}

											if ($FieldRecord["field_owner_id"] == $ArchiveID)
											{
												$NewRecord["_item_type"] = AIB_ITEM_TYPE_ARCHIVE;
												break;
											}

											$NewRecord["_item_type"] = AIB_ITEM_TYPE_ITEM;
											break;
										}

										$NewRecord["_archive_group_title"] = urldecode($ArchiveGroupMap[$ArchiveGroupID]["item_title"]);
										$NewRecord["_archive_title"] = urldecode($ArchiveMap[$ArchiveID]["item_title"]);
										$ResultList[] = $NewRecord;
									}
								}
							}
						}

						foreach($UserFieldSet as $FieldID => $FieldRecord)
						{
							$ResultList[] = $FieldRecord;
						}

						break;

				}
			}

			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam["columns"] = array(
					"field_title" => "Name",
					"field_data_type" => "Type",
					"field_size" => "Created",
					".ownedby" => "",
					".op" => "",
					);

			$ListParam["callbacks"] = array(
				"field_data_type" => array("aib_render_field_data_type_col",false),

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_generic_actions_col",
					array(	"_page" => $PageNumber,
						"_searchval" => $SearchValue,
						"_pagemode" => $ListOp,
						"_pagekey" => aib_get_with_default($FormData,"key",""),
						"edit" => array("title" => "Edit", "url" => "/field_form.php", "primary" => "field_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
						"del" => array("title" => "Delete", "url" => "/field_form.php", "primary" => "field_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
						),
					),

				// Owned by column

				".ownedby" => array("aib_render_field_owner_col"),
				);
			$ListParam["searchable"] = array(
				"field_title" => "Name",
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_TREE_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);

		// Get a list of field definitions, filtering if needed using "key", where the key indicates the owner

		case "glfd":

			$HTML = "";

			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the field list filter.  This may be either a formatted filter
			// where the type of field is followed by an ID, or an unformatted
			// filter which is actually just a user ID.

			$FieldTypeFilter = aib_get_with_default($FormData,"key","NULL");
			if ($FieldTypeFilter == "NULL" || $FieldTypeFilter == "")
			{
				if ($UserID == AIB_SUPERUSER)
				{
					$FieldListFilterType = "";
					$FieldListFilterID = -1;
				}
				else
				{
					$FieldListFilterType = "";
					$FieldListFilterID = false;
				}
			}
			else
			{
				$Segs = explode(":",$FieldTypeFilter);
				if (count($Segs) < 1)
				{
					$FieldListFilterType = "";
					$FieldListFilterID = $FieldTypeFilter;
				}
				else
				{
					$FieldListFilterType = $Segs[0];
					$FieldListFilterID = $Segs[1];
				}
			}

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			aib_open_db();

			// Get the user ID.  This determines what fields will be shown.

			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Cannot retrieve user profile"));
				exit(0);
			}

			// Get the available archives for the user

			$UserGroup = $UserRecord["user_primary_group"];
			$UserType = $UserRecord["user_type"];
			$UpdatedItemCount = 0;
			$EmptyListMessage = "";
			$ResultList = array();
			$ItemCache = array();
			if ($ListOp == "list")
			{
				// Get page number, start item

				$PageNumber = aib_get_with_default($FormData,"pn","1");
				$PageItemCount = aib_get_with_default($FormData,"pic","10");
				$StartItem = ($PageNumber - 1) * $PageItemCount;
				if ($StartItem < 0)
				{
					$StartItem = 0;
				}

				$LastItem = $StartItem + $PageItemCount;

				// Get the list of available fields based on the user ID, the filter ID and the filter type

				$UserFieldList = array();
				$ItemFieldList = array();
				switch($FieldListFilterType)
				{
					// No filter

					case "NULL":
					case "":

						// If super-user, get all fields

						if ($UserID == AIB_SUPERUSER)
						{
							$UserFieldList = ftree_list_fields($GLOBALS["aib_db"],false,false,false);
							$ItemFieldList = array();
						}
						else
						{
							// Otherwise, list all that are available to the user based on their ID or
							// the archive group and archives.

							$UserFieldList = ftree_list_fields($GLOBALS["aib_db"],$UserID,FTREE_OWNER_TYPE_USER,false);

							// Get the list of available archives

							$ArchiveList = aib_get_available_archives($GLOBALS["aib_db"],$UserID);

							// For each archive and archive group, get the fields and save to lists.  Use an array
							// to prevent duplicates from showing up.

							$TempMap = array();
							foreach($ArchiveList as $ArchiveRecord)
							{
								$TempList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveRecord["item_id"]);
								if ($TempList != false)
								{
									foreach($TempList as $TempRecord)
									{
										if(isset($TempMap[$TempRecord["field_id"]]) == false)
										{
											$TempMap[$TempRecord["field_id"]] = $TempRecord;
											$ItemFieldList[] = $TempRecord;
										}
									}
								}
							}
						}

						break;

					case AIB_ITEM_TYPE_USER:
						$UserFieldList = ftree_list_fields($GLOBALS["aib_db"],$FieldListFilterID,false,false);
						$ItemFieldList = array();
						break;

					case AIB_ITEM_TYPE_ARCHIVE:
						$ItemFieldList = ftree_list_fields($GLOBALS["aib_db"],false,false,$FieldListFilterID);
						break;

					case AIB_ITEM_TYPE_ARCHIVE_GROUP:
						$TempMap = array();
						$TempList = ftree_list_fields($GLOBALS["aib_db"],false,false,$FieldListFilterID);
						if ($TempList != false)
						{
							foreach($TempList as $TempRecord)
							{
								if(isset($TempMap[$TempRecord["field_id"]]) == false)
								{
									$TempMap[$TempRecord["field_id"]] = $TempRecord;
									$ItemFieldList[] = $TempRecord;
								}
							}
						}

						$ArchiveList = aib_get_available_archives($GLOBALS["aib_db"],$UserID);

						// For each archive and archive group, get the fields and save to lists

						foreach($ArchiveList as $ArchiveRecord)
						{
							if ($ArchiveRecord["item_parent"] != $FieldListFilterID)
							{
								continue;
							}

							$TempList = ftree_list_fields($GLOBALS["aib_db"],false,false,$ArchiveRecord["item_id"]);
							if ($TempList != false)
							{
								foreach($TempList as $TempRecord)
								{
									if(isset($TempMap[$TempRecord["field_id"]]) == false)
									{
										$TempMap[$TempRecord["field_id"]] = $TempRecord;
										$ItemFieldList[] = $TempRecord;
									}
								}
							}
						}

						break;

					default:
						$UserFieldList = array();
						$ItemFieldList = ftree_list_fields($GLOBALS["aib_db"],false,false,$FieldListFilterID);
						break;
				}

				// Get the list of traditional and system fields, if required

				switch($FieldListFilterType)
				{
					case "NULL":
					case "":
						$SystemFieldList = array();
						$TraditionalFieldList = array();
						break;

					case AIB_ITEM_TYPE_SYSTEM:
						$SystemFieldList = array();
						$TraditionalFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,false);
						break;

					case AIB_ITEM_TYPE_RECOMMENDED:
						$SystemFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_RECOMMENDED,false);
						$TraditionalFieldList = array();
						break;

					default:
						$SystemFieldList = array();
						$TraditionalFieldList = array();
						break;
				}

				$ItemCounter = -1;

				// System fields

				foreach($SystemFieldList as $LocalRecord)
				{
					$ItemCounter++;
					if ($ItemCounter < $StartItem || $ItemCounter >= $LastItem)
					{
						continue;
					}

					$NewRecord = $LocalRecord;
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
					$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
					$NewRecord["_owner_info"] = $OwnerInfo;
					$ResultList[] = $NewRecord;
				}

				// Traditional fields

				foreach($TraditionalFieldList as $LocalRecord)
				{
					$ItemCounter++;
					if ($ItemCounter < $StartItem || $ItemCounter >= $LastItem)
					{
						continue;
					}

					$NewRecord = $LocalRecord;
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
					$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
					$NewRecord["_owner_info"] = $OwnerInfo;
					$ResultList[] = $NewRecord;
				}

				// User fields

				foreach($UserFieldList as $LocalRecord)
				{
					$ItemCounter++;
					if ($ItemCounter < $StartItem || $ItemCounter >= $LastItem)
					{
						continue;
					}

					$NewRecord = $LocalRecord;
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
					$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
					$NewRecord["_owner_info"] = $OwnerInfo;
					$ResultList[] = $NewRecord;
				}

				// Item fields

				foreach($ItemFieldList as $LocalRecord)
				{
					$ItemCounter++;
					if ($ItemCounter < $StartItem || $ItemCounter >= $LastItem)
					{
						continue;
					}

					$NewRecord = $LocalRecord;
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
					$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
					$NewRecord["_owner_info"] = $OwnerInfo;
					$ResultList[] = $NewRecord;
				}
			}

			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam["columns"] = array(
					"field_title" => "Name",
					"field_data_type" => "Type",
					".ownedby" => "",
					".op" => "",
					);

			$ListParam["callbacks"] = array(
				"field_data_type" => array("aib_render_field_data_type_col",false),

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_generic_actions_col",
					array(	"_page" => $PageNumber,
						"_searchval" => $SearchValue,
						"_pagemode" => $ListOp,
						"_pagekey" => aib_get_with_default($FormData,"key",""),
						"edit" => array("title" => "Edit", "url" => "/field_form.php", "primary" => "field_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
						"del" => array("title" => "Delete", "url" => "/field_form.php", "primary" => "field_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
						),
					),

				// Owned by column

				".ownedby" => array("aib_render_field_owner_col"),
				);
			$ListParam["searchable"] = array(
				"field_title" => "Name",
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_TREE_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);

		// Get a list of form definitions
		case "lod":

			$HTML = "";

			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the page number and number of items per page

			$ParentFolderID = aib_get_with_default($FormData,"key",false);
			$PageNumber = aib_get_with_default($FormData,"pn","1");
			$PageItemCount = aib_get_with_default($FormData,"pic","10");
			$StartItem = ($PageNumber - 1) * $PageItemCount;
			if ($StartItem < 0)
			{
				$StartItem = 0;
			}

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			aib_open_db();
			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Cannot retrieve user profile"));
				exit(0);
			}

			$UserGroup = $UserRecord["user_primary_group"];
			$UserType = $UserRecord["user_type"];
			$UpdatedItemCount = 0;
			$EmptyListMessage = "";
			$ResultList = array();
			switch($ListOp)
			{
				case "list":
				default:

					// Get the list of archive groups and archives for the user based on the type

					if ($UserID != AIB_SUPERUSER)
					{
						$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
						foreach($ArchiveGroupList as $ArchiveGroupRecord)
						{
							$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
							$TempList = ftree_list_forms($GLOBALS["aib_db"],$ArchiveGroupID);
							foreach($TempList as $TempRecord)
							{
								$ResultList[] = $TempRecord;
							}

							$ArchiveList = aib_get_archives_in_archive_group($GLOBALS["aib_db"],$ArchiveGroupID);
							foreach($ArchiveList as $ArchiveRecord)
							{
								$ArchiveID = $ArchiveRecord["item_id"];
								$TempList = ftree_list_forms($GLOBALS["aib_db"],$ArchiveID);
								foreach($TempList as $TempRecord)
								{
									$ResultList[] = $TempRecord;
								}
							}
						}
					}
					else
					{
						$ResultList = ftree_list_forms($GLOBALS["aib_db"],false,false);
					}

					break;
			}

			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam["columns"] = array(
					"form_title" => "Name",
					".ownedby" => "",
					".op" => "",
					);

			$ListParam["callbacks"] = array(

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_generic_actions_col",
					array(	"_page" => $PageNumber,
						"_searchval" => $SearchValue,
						"_pagemode" => $ListOp,
						"_pagekey" => aib_get_with_default($FormData,"key",""),
						"edit" => array("title" => "Edit", "url" => "/form_form.php", "primary" => "form_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
						"del" => array("title" => "Delete", "url" => "/form_form.php", "primary" => "form_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
						),
					),

				// Owned by column

				".ownedby" => array("aib_render_form_owner_col"),
				);
			$ListParam["searchable"] = array(
				"form_title" => "Name",
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_TREE_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);

		// Get a list of collections for an archive.  This is for drop-downs only.
		case "lc":
			// Get user profile.  If not an administrator, error.

			aib_open_db();
			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Cannot retrieve user profile"));
				exit(0);
			}

			switch($UserRecord["user_type"])
			{
				case FTREE_USER_TYPE_STANDARD:
					aib_close_db();
					send_status("ERROR",array("msg" => "Unauthorized operation"));
					exit(0);

				default:
					break;
			}

			// Get parent folder, which should be the archive parent

			$ParentFolderID = aib_get_with_default($FormData,"key",false);
			if ($ParentFolderID == false || $ParentFolderID == "" || $ParentFolderID == "NULL")
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Invalid archive or collection"));
				exit(0);
			}

			// Get default value

			$DefaultCollectionID = aib_get_with_default($FormData,"def",false);

			// Get field name

			$SelectName = aib_get_with_default($FormData,"sel","aibcollection");

			// Get the list of child folders; these will be the collections in the archive.

			$UserGroup = $UserRecord["user_primary_group"];
			$UpdatedItemCount = ftree_list_child_objects($GLOBALS["aib_db"],$ParentFolderID,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,true);
			$ResultList = ftree_list_child_objects($GLOBALS["aib_db"],$ParentFolderID,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false);
			aib_close_db();

			// Create select list

			$OutBuffer = array();
			$OutBuffer[] = "<select class='aib-collection-dropdown' name='$SelectName' id='$SelectName'>";
			foreach($ResultList as $ResultRecord)
			{
				if ($DefaultCollectionID !== false)
				{
					if ($ResultRecord["item_id"] == $DefaultCollectionID)
					{
						$OutBuffer[] = "<option value=\"".$ResultRecord["item_id"]."\" SELECTED >".urldecode($ResultRecord["item_title"])."</option>";
					}
					else
					{
						$OutBuffer[] = "<option value=\"".$ResultRecord["item_id"]."\">".urldecode($ResultRecord["item_title"])."</option>";
					}
				}
				else
				{
					$OutBuffer[] = "<option value=\"".$ResultRecord["item_id"]."\">".urldecode($ResultRecord["item_title"])."</option>";
				}
			}

			$HTML = join("\n",$OutBuffer);
			send_status("OK",array("html" => $HTML));
			exit(0);

		// Get a list of records in archive/collection
		case "lr":
			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the page number and number of items per page

			$PageNumber = aib_get_with_default($FormData,"pn","1");
			$PageItemCount = aib_get_with_default($FormData,"pic","10");
			$StartItem = ($PageNumber - 1) * $PageItemCount;
			if ($StartItem < 0)
			{
				$StartItem = 0;
			}

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			aib_open_db();
			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			if ($UserRecord == false)
			{
				aib_close_db();
				send_status("ERROR",array("msg" => "Cannot retrieve user profile"));
				exit(0);
			}

			$UserGroup = $UserRecord["user_primary_group"];
			$CheckBoxes = aib_get_with_default($FormData,"checks","");
			$CheckBoxesTitle = aib_get_with_default($FormData,"checks_title","");
			$ExtraParamList = array();
			foreach($FormData as $FormFieldName => $FormFieldValue)
			{
				if (preg_match("/^[\_][\_]extra/",$FormFieldName) != false)
				{
					$LocalFieldName = preg_replace("/^[\_][\_]extra/","",$FormFieldName);
					$ExtraParamList[$LocalFieldName] = $FormFieldValue;
				}
			}

			$UpdatedItemCount = 0;
			$EmptyListMessage = "";
			$ResultList = array();
			switch($ListOp)
			{
				// Search
				case "search":
					$ParentFolderID = aib_get_with_default($FormData,"key",false);
					$SearchValue = aib_get_with_default($FormData,"lsv",false);
					$SearchCol = aib_get_with_default($FormData,"lsc","ALL");
					if ($SearchValue != false)
					{
						switch($SearchCol)
						{
							case "item_title":
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ParentFolderID AND (item_title LIKE \"%$SearchValue%\" OR item_title LIKE \"%$SearchValueOne%\" OR item_title LIKE \"%$SearchValueTwo%\") ORDER BY item_title;");
								$UpdatedItemCount = count($TempList);
								$Counter = 0;
								$ResultList = array();
								foreach($TempList as $TempRecord)
								{
									if ($Counter < $StartItem)
									{
										$Counter++;
										continue;
									}

									if ($Counter > $StartItem + $PageItemCount)
									{
										continue;
									}

									$ResultList[] = $TempRecord;
								}

								break;

							case "ALL":
							default:
								// Do all of the searches, placing the results in associative array where the key is the item ID.  Once all
								// occurrences are found, output a list sorted by title.

								$ItemMap = array();
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$SearchValueThree = strtoupper($SearchValue);
								$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ParentFolderID AND (item_title LIKE \"%$SearchValue%\" OR item_title LIKE \"%$SearchValueOne%\" OR item_title LIKE \"%$SearchValueTwo%\" OR item_title LIKE \"%$SearchValueThree%\") ORDER BY item_title;");
								if ($TempList != false)
								{
									foreach($TempList as $TempRecord)
									{
										if (isset($ItemMap[$TempRecord["item_id"]]) == false)
										{
											$ItemMap[$TempRecord["item_id"]] = $TempRecord;
										}
									}
								}

								// Create a list sorted by the item title followed by a delim and the item ID

								$SortList = array();
								foreach($ItemMap as $ItemID => $ItemRecord)
								{
									$SortList[] = urldecode($ItemRecord["item_title"])."\t".$ItemID;
								}

								sort($SortList);

								// Generate result list with items in sorted order

								$Counter = 0;
								$ResultList = array();
								$UpdatedItemCount = count($SortList);
								foreach($SortList as $SortKey)
								{
									if ($Counter < $StartItem)
									{
										$Counter++;
										continue;
									}

									if ($Counter >= $StartItem + $PageItemCount)
									{
										break;
									}

									$Segs = explode("\t",$SortKey);
									$ResultList[] = $ItemMap[$Segs[1]];
									$Counter++;
								}

								break;
						}
					}

					break;

				case "list":
				default:
					$SearchValue = aib_get_with_default($FormData,"lsv",false);
					$ParentFolderID = aib_get_with_default($FormData,"key",false);
					$ResultList = array();
					if ($ParentFolderID == false)
					{
						$EmptyListMessage = "Missing home folder";
						$UpdatedItemCount = 0;
					}
					else
					{
						$UpdatedItemCount = ftree_list_child_objects($GLOBALS["aib_db"],$ParentFolderID,$UserID,$UserGroup,false,true,false);
						$LocalResultList = ftree_list_child_objects($GLOBALS["aib_db"],$ParentFolderID,$UserID,$UserGroup,false,false,true);
						foreach($LocalResultList as $ResultRecord)
						{
							$ResultRecord["item_title"] = urldecode($ResultRecord["item_title"]);
							$ResultList[] = $ResultRecord;
						}
					}

					break;
			}

			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam["columns"] = array(
					"item_title" => "Title",
					"item_type" => "Type",
					"item_create_stamp" => "Created",
					".op" => "",
					);

			$ExtraEditParamList = $ExtraParamList;
			$ExtraEditParamList["src"] = "records";
			$ListParam["callbacks"] = array(
				"item_title" => array("aib_render_item_title_col",false),
				"item_type" => array("aib_render_item_type_col",false),
				"item_create_stamp" => array("aib_render_item_create_stamp_col",false),

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_record_actions_col",
					array(	"_page" => $PageNumber,
						"_searchval" => $SearchValue,
						"_pagemode" => $ListOp,
						"_pagekey" => aib_get_with_default($FormData,"key",""),
						"edit" => array("title" => "Edit", "image" => "/images/monoicons/pencil32.png",
							"url" => "/record_modify.php", "primary" => "item_id", "opcode" => "edit",
							"extra_param" => $ExtraEditParamList),
						"ocr" => array("title" => "OCR","image" => "/images/monoicons/gear32.png",
							"url" => "/record_form.php", "primary" => "item_id", "opcode" => "ocr",
							"extra_param" => $ExtraParamList),
						"del" => array("title" => "Delete", "image" => "/images/monoicons/recycle32.png",
							"url" => "/del_record_form.php", "primary" => "item_id", "opcode" => "del",
							"extra_param" => $ExtraParamList),
						"view" => array("title" => "View", "image" => "/images/monoicons/camera32.png",
							"url" => "/browse.php", "primary" => "item_id", "opcode" => false,
							"extra_param" => false),
						),
					),
				);
			$ListParam["searchable"] = array(
				"item_title" => "Title",
				"item_type" => "Type",
				"item_create_stamp" => "Created"
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_TREE_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$ListParam["checks"] = $CheckBoxes;
			$ListParam["checks_title"] = $CheckBoxesTitle;
			$HTML = aib_generate_records_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);

		// Get the fields for a given owner and return as a select list

		case "getfields":
			$HTML = "";

			// Get owner ID

			$FieldTypeFilter = aib_get_with_default($FormData,"key","");

			// Get the field list filter.  This may be either a formatted filter
			// where the type of field is followed by an ID, or an unformatted
			// filter which is actually just a user ID.

			aib_open_db();
			if ($FieldTypeFilter == "" || $FieldTypeFilter == "NULL")
			{
				$FieldListFilterID = "-1";
				$FieldListFilterType = "";
			}
			else
			{
				$Segs = explode(":",$FieldTypeFilter);
				if (count($Segs) < 2)
				{
					$FieldListFilterID = $FieldTypeFilter;
					$FieldListFilterType = ftree_get_property($GLOBALS["aib_db"],$FieldListFilterID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				}
				else
				{
					$FieldListFilterType = $Segs[0];
					$FieldListFilterID = $Segs[1];
				}
			}


			$ArchiveGroupFieldList = array();
			$ArchiveFieldList = array();
			$UserFieldList = array();
			$ItemFieldList = array();
			$SystemFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,false);
			$RecommendedFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_RECOMMENDED,false);
			switch($FieldListFilterType)
			{
				case AIB_ITEM_TYPE_USER:
					$UserFieldList = ftree_list_fields($GLOBALS["aib_db"],$FieldListFilterID,false,false);
					$ItemFieldList = array();
					break;

				case AIB_ITEM_TYPE_ARCHIVE:
					$ArchiveFieldList = ftree_list_fields($GLOBALS["aib_db"],false,false,$FieldListFilterID);
					break;

				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					$TempMap = array();
					$TempList = ftree_list_fields($GLOBALS["aib_db"],false,false,$FieldListFilterID);
					if ($TempList != false)
					{
						foreach($TempList as $TempRecord)
						{
							if(isset($TempMap[$TempRecord["field_id"]]) == false)
							{
								$TempMap[$TempRecord["field_id"]] = $TempRecord;
								$ArchiveGroupFieldList[] = $TempRecord;
							}
						}
					}

					$ArchiveList = aib_get_archives_in_archive_group($GLOBALS["aib_db"],$FieldListFilterID);

					// For each archive and archive group, get the fields and save to lists

					foreach($ArchiveList as $ArchiveRecord)
					{
						if ($ArchiveRecord["item_parent"] != $FieldListFilterID)
						{
							continue;
						}

						$TempList = ftree_list_fields($GLOBALS["aib_db"],false,false,$ArchiveRecord["item_id"]);
						if ($TempList != false)
						{
							foreach($TempList as $TempRecord)
							{
								if(isset($TempMap[$TempRecord["field_id"]]) == false)
								{
									$TempMap[$TempRecord["field_id"]] = $TempRecord;
									$ArchiveFieldList[] = $TempRecord;
								}
							}
						}
					}

					break;

				default:
					$UserFieldList = array();
					$ItemFieldList = ftree_list_fields($GLOBALS["aib_db"],false,false,$FieldListFilterID);
					break;
			}

			$ItemCounter = -1;

			// System fields

			$OwnerCache = array();
			foreach($SystemFieldList as $LocalRecord)
			{
				$NewRecord = $LocalRecord;
				$OwnerKey = $LocalRecord["field_owner_type"].$LocalRecord["field_owner_id"];
				if (isset($OwnerCache[$OwnerKey]) == false)
				{
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$OwnerCache[$OwnerKey] = $OwnerInfo;
				}
				else
				{
					$OwnerInfo = $OwnerCache[$OwnerKey];
				}

				$NewRecord["field_title"] = urlencode("Traditional: ").$NewRecord["field_title"];
				$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
				$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
				$NewRecord["_owner_info"] = $OwnerInfo;
				$ResultList[] = $NewRecord;
			}

			// Recommended fields

			foreach($RecommendedFieldList as $LocalRecord)
			{
				$NewRecord = $LocalRecord;
				$OwnerKey = $LocalRecord["field_owner_type"].$LocalRecord["field_owner_id"];
				if (isset($OwnerCache[$OwnerKey]) == false)
				{
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$OwnerCache[$OwnerKey] = $OwnerInfo;
				}
				else
				{
					$OwnerInfo = $OwnerCache[$OwnerKey];
				}

				$NewRecord["field_title"] = urlencode("Recommended: ").$NewRecord["field_title"];
				$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
				$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
				$NewRecord["_owner_info"] = $OwnerInfo;
				$ResultList[] = $NewRecord;
			}

			// Archive group fields

			foreach($ArchiveGroupFieldList as $LocalRecord)
			{
				$NewRecord = $LocalRecord;
				$OwnerKey = $LocalRecord["field_owner_type"].$LocalRecord["field_owner_id"];
				if (isset($OwnerCache[$OwnerKey]) == false)
				{
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$OwnerCache[$OwnerKey] = $OwnerInfo;
				}
				else
				{
					$OwnerInfo = $OwnerCache[$OwnerKey];
				}

				$NewRecord["field_title"] = $OwnerInfo["_archive_group_title"].": ".$NewRecord["field_title"];
				$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
				$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
				$NewRecord["_owner_info"] = $OwnerInfo;
				$ResultList[] = $NewRecord;
			}

			// Archive fields

			foreach($ArchiveFieldList as $LocalRecord)
			{
				$NewRecord = $LocalRecord;
				$OwnerKey = $LocalRecord["field_owner_type"].$LocalRecord["field_owner_id"];
				if (isset($OwnerCache[$OwnerKey]) == false)
				{
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$OwnerCache[$OwnerKey] = $OwnerInfo;
				}
				else
				{
					$OwnerInfo = $OwnerCache[$OwnerKey];
				}

				$NewRecord["field_title"] = $OwnerInfo["_archive_title"].": ".$NewRecord["field_title"];
				$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
				$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
				$NewRecord["_owner_info"] = $OwnerInfo;
				$ResultList[] = $NewRecord;
			}

			// User fields

			foreach($UserFieldList as $LocalRecord)
			{
				$NewRecord = $LocalRecord;
				$OwnerKey = $LocalRecord["field_owner_type"].$LocalRecord["field_owner_id"];
				if (isset($OwnerCache[$OwnerKey]) == false)
				{
					$OwnerInfo = get_field_owner_info($GLOBALS["aib_db"],$LocalRecord["field_owner_type"],$LocalRecord["field_owner_id"],$ItemCache);
					$OwnerCache[$OwnerKey] = $OwnerInfo;
				}
				else
				{
					$OwnerInfo = $OwnerCache[$OwnerKey];
				}

				$NewRecord["field_title"] = urlencode("Your Custom Field: ").$NewRecord["field_title"];
				$NewRecord["_archive_title"] = $OwnerInfo["_archive_title"];
				$NewRecord["_archive_group_title"] = $OwnerInfo["_archive_group_title"];
				$NewRecord["_owner_info"] = $OwnerInfo;
				$ResultList[] = $NewRecord;
			}

			// Item fields

			$OutLines = array(
				"<select name='form_source_fields' id='form_source_fields' class='aib-dropdown' size='10'>"
				);

			foreach($ResultList as $FieldRecord)
			{
				$OutLines[] = "<option value='".$FieldRecord["field_id"]."'>".urldecode($FieldRecord["field_title"])."</option>";
			}

			$OutLines[] = "</select>";
			send_status("OK",array("html" => join("\n",$OutLines)));
			break;

		// Bad opcode

		default:
			aib_log_message("ERROR","airrecord.php","Bad opcode $OpCode");
			send_status("ERROR",array("msg" => "Bad opcode"));
			break;
	}

	exit(0);
?>
