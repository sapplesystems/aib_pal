<?php
//
// import_services.php (Ajax Info Request)
//
// Ajax handler
//

include("../config/aib.php");
include("../include/folder_tree.php");
include("../include/fields.php");
include("../include/aib_util.php");
include("../include/import.php");

function log_import_services_message($Msg)
{
	$Handle = fopen("/tmp/import_services_debug.txt","a+");
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

// Generate field mapping output
// -----------------------------
function generate_field_mapping($FolderID,$FileBatchID,$MapName)
{
	$SubBuffer = array();
	$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$FolderID);
	$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
	$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];
	$ArchiveID = $ArchiveInfo["archive"]["item_id"];
	$FileList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatchID);
	if ($FileList == false)
	{
		$FileList = array();
	}

	$MappingIndex = array();
	if ($MapName != "NULL")
	{
		$MappingInfo = import_get_mapping($GLOBALS["aib_db"],$LocalArchiveGroup,"I",$MapName);
		if ($MappingInfo == false)
		{
			return("<p style='color:#ff0000; text-align:center;'>NO FIELD MAPPING NAMED \"$MapName\" AVAILABLE</p>");
		}

		$KeyList = array_keys($MappingInfo);
		foreach($KeyList as $MapColumnID)
		{
			if (preg_match("/[\_]+title/",$MapColumnID) != false)
			{
				continue;
			}

			$TargetField = $MappingInfo["$MapColumnID"];
			$KeyValue = $MapColumnID.":".$TargetField;
			$MappingIndex[$KeyValue] = true;
		}
	}
	else
	{
		$MappingInfo = false;
	}

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

	if ($FirstZIP == false)
	{
		return("<p style='color:#ff0000; text-align:center;'>ERROR: NO ARCHIVE FILE</p>");
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
		return("<p style='color:#ff0000; text-align:center;'>ERROR: NO DATABASE FILE</p>");
	}

	// Extract the CSV to temporary file

	$ExtractResult = import_list_extract_zip_file($SourceZIPName,$FirstCSV,$TempCSVFileName);
	if ($ExtractResult == false)
	{
		return("<p style='color:#ff0000; text-align:center;'>ERROR: CANNOT EXTRACT DATABASE FILE</p>");
	}

	// Get the list of column names

	$ColumnNameList = import_field_names_from_csv($TempCSVFileName);
	if ($ColumnNameList == false)
	{
		return("<p style='color:#ff0000; text-align:center;'>ERROR: CANNOT FIND COLUMN NAMES</p>");
	}

	if (count($ColumnNameList) < 1)
	{
		return("<p style='color:#ff0000;'>ERROR: NO COLUMN NAMES</p>");
	}

	// Get the list of fields for the archive and archive group

	$ArchiveGroupFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveGroupID,true);
	$ArchiveFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveID,true);
	$ArchiveTitle = aib_urldecode($ArchiveInfo["archive"]["item_title"]);
	$ArchiveGroupTitle = aib_urldecode($ArchiveInfo["archive_group"]["item_title"]);

	// Show each column on a line, with drop-down for fields available for archive group and/or archive

	$SubBuffer = array();
	$ColNum = 1;
	foreach($ColumnNameList as $ColName)
	{
		if (ltrim(rtrim($ColName)) == "")
		{
			continue;
		}

		if (isset($MappingIndex["$ColName"]) == true)
		{
			$TargetFieldList = $MappingIndex["$ColName"];
		}
		else
		{
			$TargetFieldList = false;
		}

		$SelectName = "target_field_for_col_".$ColNum;
		$FieldSpecTextArray = array("<select name='$SelectName' id='$SelectName'>");
		$FieldSpecTextArray[] = "<option value='IGNORE' SELECTED>Ignore</option>";
		$FieldSpecTextArray[] = "<option value='ADD'>Add As New AIB Field</option>";
		if (count($ArchiveGroupFieldList) > 0)
		{
			foreach($ArchiveGroupFieldList as $TempRecord)
			{
				$FieldID = $TempRecord["field_id"];
				$FieldTitle = aib_urldecode($TempRecord["field_title"]);
				if ($MappingInfo == false)
				{
					$FieldSpecTextArray[] = "<option value='$FieldID'>$ArchiveGroupTitle:  $FieldTitle</option>";
				}
				else
				{
					$KeyValue = $ColNum.":".$FieldID;
					if (isset($MappingIndex[$KeyValue]) == true)
					{
						$FieldSpecTextArray[] = "<option value='$FieldID' SELECTED>$ArchiveGroupTitle:  $FieldTitle</option>";
					}
					else
					{
						$FieldSpecTextArray[] = "<option value='$FieldID'>$ArchiveGroupTitle:  $FieldTitle</option>";
					}
				}
			}
		}

		if (count($ArchiveFieldList) > 0)
		{
			foreach($ArchiveFieldList as $TempRecord)
			{
				$FieldID = $TempRecord["field_id"];
				$FieldTitle = aib_urldecode($TempRecord["field_title"]);
				if ($MappingInfo == false)
				{
					$FieldSpecTextArray[] = "<option value='$FieldID'>$ArchiveTitle:  $FieldTitle</option>";
				}
				else
				{
					$KeyValue = $ColNum.":".$FieldID;
					if (isset($MappingIndex[$KeyValue]) == true)
					{
						$FieldSpecTextArray[] = "<option value='$FieldID' SELECTED>$ArchiveTitle:  $FieldTitle</option>";
					}
					else
					{
						$FieldSpecTextArray[] = "<option value='$FieldID'>$ArchiveTitle:  $FieldTitle</option>";
					}
				}

			}
		}

		$FieldSpecTextArray[] = "</select>";
		$FieldSpecText = join("\n",$FieldSpecTextArray);
		$FieldSpec = array(
			"title" => "# ".$ColNum."; $ColName: ",
			"type" => "text",
			"display_width" => 64,
			"field_name" => "target_field_for_col_".$ColNum,
			"field_id" => "target_field_for_col_".$ColNum,
			"desc" => "",
			"help_function_name" => "",
			"fielddata" => $FieldSpecText,
			);
		$SubBuffer[] = aib_draw_custom_field($FieldSpec);
		$SubBuffer[] = aib_draw_input_row_separator();
		$ColNum++;
	}


	$TempLocalBuffer = "<table class='aib-input-set'>".join("\n",$SubBuffer)."</table>";
	return($TempLocalBuffer);
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


		// Load a field map as a series of fields

		case "loadmap":
			aib_open_db();
			$MapName = aib_get_with_default($FormData,"map_name","NO MAP");
			$ParentFolder = aib_get_with_default($FormData,"parent_folder","-1");
			$FileBatchID = aib_get_with_default($FormData,"file_batch","-1");
			print(generate_field_mapping($ParentFolder,$FileBatchID,$MapName));
			aib_close_db();
			exit(0);

		// Bad opcode

		default:
			print("<p>BAD OPCODE</p>");
			break;
	}

	exit(0);
?>
