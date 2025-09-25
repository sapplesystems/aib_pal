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
	$ArchiveName = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
	$FolderType = ftree_get_property($GLOBALS["aib_db"],$ItemID,"aibftype");

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

		$TargetPage = false;
		if ($SourceFields != false)
		{
			switch($FolderType)
			{
				case "col":
					$TargetPage = "collection_form.php";
					break;


				case "sg":
					$TargetPage = "subgroup_form.php";
					break;


				case "rec":
					$TargetPage = "record_form.php";
					break;

				case false:
					if ($ArchiveName != false)
					{
						$TargetPage = "admin_archiveform.php";
					}
					else
					{
						break;
					}

					break;

				default:
					break;
			}

			// Create link

			if ($TargetPage != false)
			{
				$OutLines[] = "<a href='/$TargetPage?$SourceFields' class='aib-list-action-link'>";

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
	$ArchiveName = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
	$FolderType = ftree_get_property($GLOBALS["aib_db"],$ItemID,"aibftype");

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

	foreach($ExtraData as $Operation => $OpData)
	{
		$URL = $OpData["url"];
		$URL .= "?opcode=".$OpData["opcode"]."&primary=".$Record[$OpData["primary"]];
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
	$URL = "/records.php?opcode=list&parent=".$Record["item_id"];
	$ItemID = $Record["item_id"];
	$ArchiveTitle = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
	if ($ArchiveTitle != false)
	{
		return("<a class='aib-item-link' href='$URL'>$ColValue -- $ArchiveTitle</a>");
	}

	return("<a class='aib-item-link' href='$URL'>$ColValue</a>");
}

// Render item type column
// -----------------------
function aib_render_item_type_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$URL = "/records.php?opcode=list&parent=".$Record["item_id"];
	switch($Record["item_type"])
	{
		case FTREE_OBJECT_TYPE_FOLDER:
			
			// See if there is an entry type from the properties

			$ItemID = $Record["item_id"];
			$EntryType = ftree_get_property($GLOBALS["aib_db"],$ItemID,"aibftype");
			switch($EntryType)
			{
				// Collection

				case "col":
					$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/box32.png'>Collection";
					break;

				// Sub-group

				case "sg":
					$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'>Sub-Group";
					break;

				// Record

				case "rec":
					$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/article32.png'>Record";
					break;

				// All others

				default:
					$ArchiveTitle = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
					if ($ArchiveTitle != false)
					{
						$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'>Archive";
					}
					else
					{
						$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/folder32.png'>Folder";
					}

					break;
			}

			return($HTML);

		case FTREE_OBJECT_TYPE_FILE:
			$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/linedpaper32.png'>File";
			return($HTML);

		case FTREE_OBJECT_TYPE_LINK:
			$HTML = "<img class='aib-list-record-link-icon' src='/images/monoicons/exchange32.png'>Link";
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
	$OutValue = date("m/d/Y H:i:s",$LocalStamp);
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
					$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$LocalID,"archive_name");
					if ($ArchiveNameProp != false)
					{
						$ArchiveTitle = $ArchiveNameProp;
						$ArchiveCodeTitle = $ItemRecord["item_title"];
						$FullTitle .= " -- $ArchiveNameProp";
						$OutValue["archive_id"] = $LocalID;
						$OutValue["archive_title"] = $FullTitle;
						$OutValue["idpath"] = $IDPathList;
						$OutValue["title"] = $ItemRecord["item_title"];
						break;
					}
				}
			}
		}
		else
		{
			$OutValue["title"] = $ItemRecord["item_title"];
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
			$ArchiveCodeTitle = $ItemRecord["item_title"];
			$FullTitle = $ArchiveNameProp." ($ArchiveCodeTitle)";
			$OutValue["archive_id"] = $LocalID;
			$OutValue["archive_title"] = $FullTitle;
			$OutValue["idpath"] = ftree_get_item_id_path($GLOBALS["aib_db"],$ItemID);
			$OutValue["title"] = $ItemRecord["item_title"];
		}
		else
		{
			$OutValue["title"] = $ItemRecord["item_title"];
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
			$PathNames[] = $ItemRecord["item_title"];
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
			return("System");

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

			// Determine what kind of entry the item is.

			$TypeInfo = airrecord_get_item_type($GLOBALS["aib_db"],$OwnerID,true);
			if ($TypeInfo == false)
			{
				return("Unknown Item");
			}

			if ($TypeInfo["type"] == "")
			{
				return("Unknown Item");
			}

			switch($TypeInfo["type"])
			{
				case AIB_ITEM_TYPE_ARCHIVE:
					return($TypeInfo["archive_title"]);

				case AIB_ITEM_TYPE_COLLECTION:
					return("Collection: ".aib_generate_name_path($GLOBALS["aib_db"],$TypeInfo["idpath"]));

				case AIB_ITEM_TYPE_SUBGROUP:
					return("Sub Group: ".aib_generate_name_path($GLOBALS["aib_db"],$TypeInfo["idpath"]));
					
				case AIB_ITEM_TYPE_RECORD:
					return("Record: ".aib_generate_name_path($GLOBALS["aib_db"],$TypeInfo["idpath"]));

				default:
					return("Item: ".aib_generate_name_path($GLOBALS["aib_db"],$TypeInfo["idpath"]));
			}

			return("Unknown Item");

		default:
			break;
	}

	return("System");
}
// #########
// MAIN CODE
// #########

	// Get form

	$FormData = aib_get_form_data();

	// Get opcode.  If not present, error

	$OpCode = element_with_default($FormData,"o",false);
	if ($OpCode == false)
	{
		aib_log_message("ERROR","airrecord.php","Missing opcode");
		send_status("ERROR",array("msg" => "Missing opcode"));
		exit(0);
	}

	$OpCode = hex2bin($OpCode);

	// Get session ID.  Error if not present or bad value.

	$SessionID = element_with_default($FormData,"s",false);
	if ($SessionID === false)
	{
		aib_log_message("ERROR","airrecord.php","Missing session");
		send_status("ERROR",array("msg" => "Missing session"));
		exit(0);
	}

	// Get user record ID.  Error if not present or bad value.

	$UserID = element_with_default($FormData,"i",false);
	if ($UserID === false)
	{
		aib_log_message("ERROR","airrecord.php","Missing user ID");
		send_status("ERROR",array("msg" => "Missing ID"));
		exit(0);
	}

	$UserID = intval(hex2bin($UserID));
	if ($UserID < 0)
	{
		aib_log_message("ERROR","airrecord.php","User ID is invalid (less than zero)");
		send_status("ERROR",array("msg" => "Invalid ID"));
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

		// Generate a list of archives only, where pre-selected archives are in the ID list

		case "lar":

			// Get list of selected items

			$IDListString = aib_get_with_default($FormData,"idlist","");
			$IDList = explode(",",$IDListString);
			$IDMap = array();
			foreach($IDList as $LocalID)
			{
				$IDMap[$LocalID] = 'Y';
			}

			// Get option to show only those archives for a specific user

			$RestrictToUserOption = aib_get_with_default($FormData,"restrict_to_user","N");

			aib_open_db();

			// Get root of archives

			$ArchiveFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");

			// Generate list

			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			$UserGroup = $UserRecord["user_primary_group"];
			if ($RestrictToUserOption == "Y")
			{
				$ChildList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchiveFolderID,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false);
			}
			else
			{
				$ChildList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchiveFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
			}

			if ($ChildList == false)
			{
				$ChildList = array();
			}

			// Output a structured list with classes

			$ULClass = aib_get_with_default($FormData,"ulclass",false);
			$LIClass = aib_get_with_default($FormData,"liclass",false);
			$CollectionClass = aib_get_with_default($FormData,"coclass",false);
			$ArchiveClass = aib_get_with_default($FormData,"arclass",false);
			$SubGroupClass = aib_get_with_default($FormData,"sgclass",false);
			$OutLines = array();
			$ChildIDName = "aib_navlist_childof_".$ArchiveFolderID;
			if ($ULClass != false)
			{
				$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
			}
			else
			{
				$OutLines[] = "<ul id='$ChildIDName'>";
			}

			$ChildCount = 0;
			foreach($ChildList as $Record)
			{
				// Get type of entry (archive, collection, etc)

				$ItemID = $Record["item_id"];
				if (isset($IDMap[$ItemID]) == true)
				{
					$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID' checked onclick=\"set_tree_checkbox(event,this);\">";
				}
				else
				{
					$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID' onclick=\"set_tree_checkbox(event,this);\">";
				}

				$EntryType = ftree_get_property($GLOBALS["aib_db"],$ItemID,"aibftype");
				$Line = false;
				$ArchiveTitle = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
				if ($ArchiveTitle != false)
				{
					if ($ArchiveClass != false)
					{
						$Line = "<li class='$ArchiveClass'> $ArchiveTitle (".$Record["item_title"].")</li>";
					}
					else
					{
						$Line = "<li>$ArchiveTitle (".$Record["item_title"].")</li>";
					}

					$ChildCount++;
				}

				// Output line item

				if ($Line != false)
				{
					$OutLines[] = $Line;
				}

			}

			$OutLines[] = "</ul>";
			aib_close_db();
			if ($ChildCount < 1)
			{
				send_status("OK",array("html" => "", "item_id" => $ParentItem));
			}
			else
			{
				send_status("OK",array("html" => join("\n",$OutLines), "item_id" => $ParentItem));
			}

			exit(0);

		// Generate list with child items

		case "chl":
			$ParentItem = aib_get_with_default($FormData,"pi",false);
			if ($ParentItem === false)
			{
				send_status("ERROR",array("msg" => "Missing pi"));
				exit(0);
			}

			$EditItem = aib_get_with_default($FormData,"ei",false);

			// Get list of child items from database

			aib_open_db();
			$SubSize = ftree_list_child_objects($GLOBALS["aib_db"],$ParentItem,false,false,FTREE_OBJECT_TYPE_FOLDER,true);
			if ($SubSize < 1)
			{
				aib_close_db();
				send_status("OK",array("html" => "", "item_id" => $ParentItem));
				exit(0);
			}

			$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
			$IDPathList = ftree_get_item_id_path($DBHandle,$ParentItem);
			$EntryType = ftree_get_property($GLOBALS["aib_db"],$ParentItem,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			$SubCount = -1;
			switch($EntryType)
			{
				case AIB_ITEM_TYPE_SUBGROUP:
				case AIB_ITEM_TYPE_COLLECTION:
					$SubCount = ftree_list_child_objects_filter_by_aibtype($GLOBALS["aib_db"],$ParentItem,false,false,FTREE_OBJECT_TYPE_FOLDER,true,false,false,array(AIB_ITEM_TYPE_SUBGROUP));
					break;

				case AIB_ITEM_TYPE_ARCHIVE:
					$SubCount = ftree_list_child_objects_filter_by_aibtype($GLOBALS["aib_db"],$ParentItem,false,false,FTREE_OBJECT_TYPE_FOLDER,true,false,false,array(AIB_ITEM_TYPE_COLLECTION));
					break;

				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					$SubCount = ftree_list_child_objects_filter_by_aibtype($GLOBALS["aib_db"],$ParentItem,false,false,FTREE_OBJECT_TYPE_FOLDER,true,false,false,array(AIB_ITEM_TYPE_ARCHIVE));
					break;

				default:
					break;
			}

			if ($SubCount >= 0)
			{
				if ($SubCount == 0)
				{
					aib_close_db();
					send_status("OK",array("html" => "", "item_id" => $ParentItem));
					exit(0);
				}
			}

			// Set up map for path.  If this is a standard user account, then
			// eliminate the path entries above the user's top folder.

			if ($UserRecord != false)
			{
				$UserType = $UserRecord["user_type"];
				if ($UserType == AIB_USER_TYPE_USER || $UserType == AIB_USER_TYPE_PUBLIC)
				{
//					$ParentItem = $UserRecord["user_top_folder"];
					while(true)
					{
						if (count($IDPathList) > 0)
						{
							if ($IDPathList[0] == $UserRecord["user_top_folder"])
							{
								break;
							}

							array_shift($IDPathList);
						}
						else
						{
							break;
						}
					}
				}
			}

			// If the initial parent is the user's top folder, then eliminate the parts of the path
			// that are outside of the user's tree.

			$PathMap = array();
			foreach($IDPathList as $TempID)
			{
				$PathMap[$TempID] = true;
			}

			$UserGroup = $UserRecord["user_primary_group"];
			$ChildList = ftree_list_child_objects($GLOBALS["aib_db"],$ParentItem,false,false,FTREE_OBJECT_TYPE_FOLDER,false,true);
			if ($ChildList == false)
			{
				aib_close_db();
				send_status("OK",array("html" => "", "item_id" => $ParentItem));
				exit(0);
			}

			// Output a structured list with classes

			$ULClass = aib_get_with_default($FormData,"ulclass",false);
			$LIClass = aib_get_with_default($FormData,"liclass",false);
			$CollectionClass = aib_get_with_default($FormData,"coclass",false);
			$ArchiveClass = aib_get_with_default($FormData,"arclass",false);
			$SubGroupClass = aib_get_with_default($FormData,"sgclass",false);
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ParentItem' onclick=\"set_tree_checkbox(event,this);\">";
			$OutLines = array();
			$ChildIDName = "aib_navlist_childof_".$ParentItem;
			if ($ULClass != false)
			{
				$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
			}
			else
			{
				$OutLines[] = "<ul id='$ChildIDName'>";
			}

			$ItemRefMap = array();
			if ($EditItem !== false)
			{
				$ItemReferenceList = aib_get_item_references($EditItem,true);
				foreach($ItemReferenceList as $ItemRefRecord)
				{
					$ItemRefMap[$ItemRefRecord["item_parent"]] = $ItemRefRecord;
				}
			}

			$ChildAddCount = 0;
			foreach($ChildList as $Record)
			{
				// Get type of entry (archive, collection, etc)

				$ItemID = $Record["item_id"];
				if (isset($ItemRefMap[$ItemID]) == true)
				{
					$Checked = " checked ";
				}
				else
				{
					$Checked = "";
				}
				$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID' onclick=\"set_tree_checkbox(event,this);\" $Checked>";
				$EntryType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				if ($EntryType == AIB_ITEM_TYPE_SUBGROUP)
				{
					$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID' onclick=\"set_tree_checkbox(event,this);\" $Checked>";
				}
				else
				{
					$IDBox = "";
					$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID' onclick=\"set_tree_checkbox(event,this);\" $Checked>";
				}

				$Line = false;
				$LocalTitle = aib_urldecode($Record["item_title"]);
				$SpanOn = "<span style='position:relative; left:-0.5em;'>";
				$SpanOff = "</span>";
				switch($EntryType)
				{
					// Collection
	
					case AIB_ITEM_TYPE_COLLECTION:
						$SubCount = ftree_list_child_objects_filter_by_aibtype($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,true,false,false,
							array(AIB_ITEM_TYPE_SUBGROUP));
						$IDString = "id='aib_navlist_entry_".$ItemID."' class='aib-navtree-li'";
						$NoSubIcon = " style='list-style-image: url(\"/images/button-nochild.png\");'";
						$NoSubIDString = "id='aib_navlist_entry_".$ItemID."' class='aib-navtree-li-no-child'";
//						$SubSize = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,true);
						if ($SubCount > 0)
						{
							if ($CollectionClass != false)
							{
								$Line = "<li $IDString onclick=\"fetch_tree_children(event,this,$ItemID);\" class='$CollectionClass'>$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
							else
							{
								$Line = "<li $IDString onclick=\"fetch_tree_children(event,this,$ItemID);\">$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
						}
						else
						{
							if ($CollectionClass != false)
							{
								$Line = "<li $NoSubIDString onclick=\"empty_child_callback(event,this,$ItemID);\" $NoSubIcon>$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
							else
							{
								$Line = "<li $NoSubIDString onclick=\"empty_child_callback(event,this,$ItemID);\" $NoSubIcon>$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
						}

						$ChildAddCount++;
						break;
	
					// Sub-group
	
					case AIB_ITEM_TYPE_SUBGROUP:
						$SubCount = ftree_list_child_objects_filter_by_aibtype($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,true,false,false,
							array(AIB_ITEM_TYPE_SUBGROUP));
						$IDString = "id='aib_navlist_entry_".$ItemID."' class='aib-navtree-li'";
						$NoSubIcon = " style='list-style-image: url(\"/images/button-nochild.png\");'";
						$NoSubIDString = "id='aib_navlist_entry_".$ItemID."' class='aib-navtree-li-no-child'";
//						$SubSize = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,FTREE_OBJECT_TYPE_FOLDER,true);
						if ($SubCount > 0)
						{
							if ($SubGroupClass != false)
							{
								$Line = "<li $IDString onclick=\"fetch_tree_children(event,this,$ItemID);\" class='$SubGroupClass'>$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
							else
							{
								$Line = "<li $IDString onclick=\"fetch_tree_children(event,this,$ItemID);\">$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
						}
						else
						{
							if ($SubGroupClass != false)
							{
								$Line = "<li $NoSubIDString $NoSubIcon onclick=\"empty_child_callback(event,this,$ItemID);\">$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
							else
							{
								$Line = "<li $NoSubIDString $NoSubIcon onclick=\"empty_child_callback(event,this,$ItemID);\">$SpanOn $IDBox $LocalTitle $SpanOff</li>";
							}
						}

						$ChildAddCount++;
						break;
	
					// All others
	
					default:
						break;
				}

				// Output line item

				if ($Line != false)
				{
					$OutLines[] = $Line;
				}

			}

			$OutLines[] = "</ul>";
			aib_close_db();
			if ($ChildAddCount < 1)
			{
				send_status("OK",array("html" => "", "item_id" => $ParentItem));
				exit(0);
			}

			send_status("OK",array("html" => join("\n",$OutLines), "item_id" => $ParentItem));
			exit(0);

		// Generate list of selected tree items

		case "gsl":
			$IDListString = aib_get_with_default($FormData,"idlist","");
			$IDList = explode(",",$IDListString);
			aib_open_db();
			$ChildCount = 0;
			$OutLines = array("<ul class='aib-items-selected-list'>");
			foreach($IDList as $IDValue)
			{
				$Record = ftree_get_item($GLOBALS["aib_db"],$IDValue);
				if ($Record != false)
				{
					$TitlePathList = ftree_get_item_title_path($GLOBALS["aib_db"],$IDValue);
					array_shift($TitlePathList);
					$OutTitlePathList = array();
					foreach($TitlePathList as $TitleRecord)
					{
						$OutTitlePathList[] = aib_urldecode($TitleRecord["item_title"]);

					}

					$LocalTitle = join(" &#8611; ",$OutTitlePathList);
					$ArchiveName = ftree_get_property($GLOBALS["aib_db"],$IDValue,"archive_name");
					$FolderType = ftree_get_property($GLOBALS["aib_db"],$IDValue,"aibftype");
					if ($ArchiveName != false)
					{
						$OutLines[] = "<li class='aib-items-selected-item'>$ArchiveName ($LocalTitle)</li>";
					}

					if ($FolderType != false)
					{
						switch($FolderType)
						{
							case "sg":
								$OutLines[] = "<li class='aib-items-selected-item'>$LocalTitle</li>";
								$ChildCount++;
								break;


							default:
								break;
						}
					}
				}
			}

			$OutLines[] = "</ul>";
			aib_close_db();
			send_status("OK",array("html" => join("\n",$OutLines)));
			exit(0);


		// Bad opcode

		default:
			aib_log_message("ERROR",array("msg" => "airrecord.php; Bad opcode $OpCode"));
			break;
	}

	exit(0);
?>
