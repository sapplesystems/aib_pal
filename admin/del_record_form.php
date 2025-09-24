<?php
//
// del_record_form.php
//

// FUNCTIONAL INCLUDES

include('config/aib.php');
include("include/folder_tree.php");
include("include/fields.php");
include('include/aib_util.php');

define('AIB_RECORD_COLUMN_COUNT','4');
define('AIB_RECORD_COLLECTION_ICON','/images/collection.png');
define('AIB_RECORD_LINK_ICON','/images/link.png');
define('AIB_SCROLLABLE_IMAGE_WIDTH','150');
define('AIB_SCROLL_LEFT_BUTTON_IMAGE','/images/monoicons/circleleft32.png');
define('AIB_SCROLL_RIGHT_BUTTON_IMAGE','/images/monoicons/circleright32.png');

function show_error_page($Title,$Message)
{
	print("<head><title>ERROR: $Title</title></head>");
	print("<body><h1>$Title</h1><br>");
	print("<div>$Message</div>");
	print("</body>");
	exit(0);
}

// #########
// MAIN CODE
// #########

	// Check session

	$CheckResult = aib_check_session();
	if ($CheckResult[0] != "OK")
	{
		$ErrorText = bin2hex($CheckResult[1]);
		header("Location: /login_error.php?v=$ErrorText");
		exit(0);
	}

	// Get user info from session data

	$SessionInfo = $CheckResult[1];
	$UserInfo = aib_get_user_info($SessionInfo["login"]);
	if ($UserInfo[0] != "OK")
	{
		$ErrorText = bin2hex("Cannot retrieve user profile");
		header("Location: /login_error.php?v=$ErrorText");
		exit(0);
	}

	$UserRecord = $UserInfo[1];
	$UserType = $UserRecord["user_type"];
	$UserID = $UserRecord["user_id"];

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();
	$NavTargetInfo = aib_get_nav_target();
	$Primary = aib_get_with_default($FormData,"primary",false);
	if ($Primary == false)
	{
		show_error_page("MISSING PARAMETER","Missing parameter required for operation");
		exit(0);
	}

	// Get opcode

	$OpCode = aib_get_with_default($FormData,"opcode","");

	// Get the current parent folder.  If there isn't one, start at the archive groups level.

	if (aib_open_db() == false)
	{
		show_error_page("SYSTEM ERROR","Cannot connect to database");
	}

	// Set up display data array and set page title based on the user type

	$DisplayData = array("popup_list" => array());
	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: DELETE RECORD";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_title"].": DELETE RECORD";
			break;
	}

	// Include menu data

	switch($UserRecord["user_type"])
	{
		case FTREE_USER_TYPE_ROOT:
			include("template/top_menu_data.php");
			include("template/top_menu_admin_data.php");
			break;

		case FTREE_USER_TYPE_ADMIN:
			include("template/top_menu_data.php");
			break;

		case FTREE_USER_TYPE_SUBADMIN:
			include("template/top_menu_subadmin_data.php");
			break;

		default:
			break;
	}

	// Set up initialization JavaScript based on the opcode

	$DisplayData["head_script"] = "

		// Prevent the use of the 'back' button to prevent mangling
		// forms.

		history.pushState(null, null, document.URL);
		window.addEventListener('popstate', function () {
		    alert(\"Please use the links on the page instead of the 'Back' button.\");
		    history.pushState(null, null, document.URL);
		});


";

	// Include the page header

include('template/common_header_admin.php');

	// Set up footer script information

	$FooterScriptLines = array();
	
	// Set up output buffer.  Start with a table row.

	$OutBuffer = array("<tr><td colspan='99'>");

	// Based on the user and the current folder, show items in folder

	$OutBuffer[] = "<div class='browse-content-div'>";
	$FirstFieldID = false;
	$ChildItemList = array();
	$TargetSpec = array();
	$ErrorMessage = false;
	$StatusMessage = false;
	$LowBannerScrollValue = 0;
	$HighBannerScrollValue = 0;
	$CurrentRecordItem = -1;
	$ErrorMessage = false;
	$StatusMessage = false;

	// Load data based on opcode

	$CurrentSubgroupID = -1;		// Subgroup
	$CurrentRecordID = -1;			// Record
	$CurrentRecordItemID = -1;		// Record item
	$FirstChild = false;			// First child item in record
	$HighlightInfoIcon = false;
	$CurrentItemType = false;
	$ChildItemList = array();
	$FirstChildItem = -1;
	$SubgroupCompleteFlag = false;
	$SavedRecordID = false;
	$SavedItemID = false;
	$EditItemID = false;

	$NextRecordFlag = false;		// If true, there are records available
	$NextItemFlag = false;			// If true, there are items available

	// Form data fields:
	//
	//	item_id		Record to be deleted
	//	

	$ProcessingType = "record";

	// Process based on opcode

	switch($OpCode)
	{
		// Show delete form

		// Do deletion

		// Start processing subgroup

		case "start_sub":

			// Get subgroup

			$CurrentSubgroupID = aib_get_with_default($FormData,"primary","-1");

			// Get list of waiting items and filter out the list of items for the subgroup.

			$WaitingDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,true);
			if ($WaitingDataEntry == false)
			{
				$WaitingDataEntry = array();
			}

			$WorkSet = array();
			foreach($WaitingDataEntry as $WaitingEntry)
			{
				if ($WaitingEntry["item_parent_id"] == $CurrentSubgroupID)
				{
					$WorkSet[] = $WaitingEntry;
				}
			}

			// If there aren't any, then no current record

			if (count($WorkSet) < 1)
			{
				break;
			}

			// Get the first record ID

			$CurrentRecordID = $WorkSet[0]["item_id"];
			$NextRecordFlag = true;

			// Get the lst of child items and get first item (if available)

			$ChildItemList = ftree_list_child_objects($GLOBALS["aib_db"],$CurrentRecordID,false,false,FTREE_OBJECT_TYPE_FILE,false,false,true);
			if (count($ChildItemList) > 0)
			{
				$FirstChildItem = $ChildItemList[0]["item_id"];
			}

			break;

		// Next record

		case "_next":
			$CurrentSubgroupID = aib_get_with_default($FormData,"primary","-1");
			$CurrentRecordID = aib_get_with_default($FormData,"record_id","-1");
			$CurrentRecordItemID = aib_get_with_default($FormData,"item_id","-1");
			$ChildItemList = ftree_list_child_objects($GLOBALS["aib_db"],$CurrentRecordID,false,false,FTREE_OBJECT_TYPE_FILE,false,false,true);
			if ($ChildItemList != false)
			{
				if (count($ChildItemList) > 0)
				{
					$FirstChildItem = $ChildItemList[0]["item_id"];
				}
			}

			$CurrentRecordItemID = -1;
			$NextRecordFlag = true;
			$NextItemFlag = false;
			break;

		// Next item

		case "_next_item":

			// Get the subgroup, record, item

			$CurrentSubgroupID = aib_get_with_default($FormData,"primary","-1");
			$CurrentRecordID = aib_get_with_default($FormData,"record_id","-1");
			$CurrentRecordItemID = aib_get_with_default($FormData,"item_id","-1");
			$EditItemID = $CurrentRecordItemID;
			$ChildItemList = ftree_list_child_objects($GLOBALS["aib_db"],$CurrentRecordID,false,false,FTREE_OBJECT_TYPE_FILE,false,false,true);
			if ($ChildItemList != false)
			{
				if (count($ChildItemList) > 0)
				{
					$FirstChildItem = $ChildItemList[0]["item_id"];
				}
			}

			$NextRecordFlag = true;
			$NextItemFlag = true;
			break;

		// Delete

		case "del":

			// If the source was "records", this is a delete request.  Determine the type of entry, and then
			// set the FormData values accordingly.

			$SourceType = aib_get_with_default($FormData,"src","");
			if ($SourceType == "records")
			{
				$TempID = aib_get_with_default($FormData,"primary","-1");
				
				// Get the item record and determine type

				$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$TempID);
				$ItemType = ftree_get_property($GLOBALS["aib_db"],$TempID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				if ($ItemType == false)
				{
					$ItemType = AIB_ITEM_TYPE_ITEM;
				}

				switch($ItemType)
				{
					case AIB_ITEM_TYPE_RECORD:
						$ProcessType = "record";
						break;

					case AIB_ITEM_TYPE_ITEM:
						$ProcessType = "item";
						break;

					default:
						$ProcessType = "record";
						break;
				}
			}

			// Get current values and child list

			switch($ProcessType)
			{
				case "item":
					$CurrentRecordItemID = aib_get_with_default($FormData,"primary","-1");
					$TempRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentRecordItemID);
					$CurrentRecordID = $TempRecord["item_parent"];
					$TempRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentRecordID);
					$CurrentSubgroupID = $TempRecord["item_parent"];
					break;

				case "record":
				default:
					$CurrentRecordID = aib_get_with_default($FormData,"primary","-1");
					$TempRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentRecordID);
					$CurrentSubgroupID = $TempRecord["item_parent"];
					$CurrentRecordItemID = -1;
					break;
			}


			$ChildItemList = ftree_list_child_objects($GLOBALS["aib_db"],$CurrentRecordID,false,false,FTREE_OBJECT_TYPE_FILE,false,false,true);
			$NextRecordFlag = true;
			$FirstChildItem = false;
			if ($ChildItemList != false)
			{
				if (count($ChildItemList) > 0)
				{
					$FirstChildItem = $ChildItemList[0]["item_id"];
					$NextItemFlag = true;
				}
			}

			break;

		// Delete record


		case "do_del":
			$CurrentSubgroupID = aib_get_with_default($FormData,"primary","-1");
			$CurrentRecordID = aib_get_with_default($FormData,"record_id","-1");
			$CurrentRecordItemID = aib_get_with_default($FormData,"item_id","-1");

			// Delete the record and all associated storage

			ftree_delete($GLOBALS["aib_db"],$CurrentRecordID,true);
			break;

		default:
			$ErrorMessage = "Invalid opcode";
			$CurrentRecordID = -1;
			$CurrentRecordItem = -1;
			$CurrentSubgroupID = aib_get_with_default($FormData,"primary","-1");
			break;
	}

	// If transitioning to the next record, show targets

	$OutBuffer = array();
	$SrcMode = aib_get_with_default($FormData,"srcmode","");
	$SourceKey = aib_get_with_default($FormData,"srckey","");
	$SearchVal = aib_get_with_default($FormData,"searchval","");
	$SrcPn = aib_get_with_default($FormData,"srcpn","");
	$SourceType = aib_get_with_default($FormData,"src","");
	$RecordFields = "primary=$CurrentSubgroupID&record_id=$CurrentRecordID&item_id=$CurrentRecordItemID&src=$SourceType";
	$TargetSpec = array();
	$OutBuffer = array();

	$NavID = aib_get_nav_value("primary");
	$NavTargetInfo = aib_get_nav_target();
	if ($NavTargetInfo != false && preg_match("/save/",$OpCode) == false)
	{
		$URL = "/".$NavTargetInfo["target"]."?aibnav=".aib_get_nav_string();
		$UpTitle = "Return To ".$NavTargetInfo["title"];
		$OutBuffer[] = "<tr><td align='left' valign='top'>";
		$OutBuffer[] = "<div class='browse-uplink-div'><a href='$URL' class='browse-uplink-link'>$UpTitle</a></div><br>";
		$OutBuffer[] = "</td></tr>";
	}

	// Set up center column row and cell

	$OutBuffer[] = "<tr>";
	$OutBuffer[] = "<td>";

	// Output based on opcode and user type

	switch($OpCode)
	{
		// Record was deleted.

		case "do_del":
			if ($UserType == FTREE_USER_TYPE_ROOT || $UserType == FTREE_USER_TYPE_ADMIN)
			{
				$Target = "/admin_main.php?parent=$PassedParent&srcmode=$SrcMode&srckey=$SourceKey&searchval=$SearchVal&srcpn=$SrcPn";
				$TargetSpec[] = array("url" => $Target, "title" => "Home Page", "fields" => array());
				$Target = "/records.php?parent=$PassedParent&srcmode=$SrcMode&srckey=$SourceKey&searchval=$SearchVal&srcpn=$SrcPn";
				$TargetSpec[] = array("url" => $Target, "title" => "Back To Managing Records", "fields" => array());
			}

			$OutBuffer[] = "<table width='100%' cellpadding='0' cellspacing='0'>";
			$OutBuffer[] = "<tr>";
			$OutBuffer[] = "<td align='right' width='30%' class='saved-changes-title'>Deleted record&nbsp;</td>";
			$OutBuffer[] = "</tr>";
			if ($SourceType == "records")
			{
				$OutBuffer[] = "<tr>";
				$OutBuffer[] = "<td align='right' width='30%'> </td>";
				$OutBuffer[] = "<td class='edit-record-record-title'>";
				$Target = "/records.php?parent=$PassedParent&srcmode=$SrcMode&srckey=$SourceKey&searchval=$SearchVal&srcpn=$SrcPn";
				$OutBuffer[] = "<a href=\"$Target\">Click Here To Return To Record Management</a>";
				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "<tr>";
				$OutBuffer[] = "<td align='right' width='30%'> </td>";
				$OutBuffer[] = "<td class='edit-record-record-title'>";
				$Target = "/admin_main.php?parent=$PassedParent&srcmode=$SrcMode&srckey=$SourceKey&searchval=$SearchVal&srcpn=$SrcPn";
				$OutBuffer[] = "<a href=\"$Target\">Click Here To Return To Home Page</a>";
				$OutBuffer[] = "</td></tr>";
			}

			$OutBuffer[] = "</table>";
			break;

		default:
			if ($NextRecordFlag == false)
			{
				if ($NextItemFlag == false)
				{
					if ($UserType != FTREE_USER_TYPE_ROOT && $UserType != FTREE_USER_TYPE_ADMIN)
					{
						$Target = "/admin_main.php?parent=$PassedParent&srcmode=$SrcMode&srckey=$SourceKey&searchval=$SearchVal&srcpn=$SrcPn";
						$TargetSpec[] = array("url" => $Target, "title" => "Home Page", "fields" => array());
					}
				}
			}

			break;
	}

	// If there are target specs, display.  Otherwise, show form.

//	if (count($TargetSpec) > 0)
//	{
//		$OutBuffer[] = aib_chain_link_set($TargetSpec);
//	}
//	else
	if (count($TargetSpec) < 1)
	{
		$TargetOpCode = false;
		switch($OpCode)
		{
			case "_next":
			case "edit":
			case "save_record":
			case "start_sub":
				$TargetOpCode = "do_del";

			case "_next_item":
			case "save_item":
				$TargetOpCode = "do_del";
				break;

			case "del":
				$TargetOpCode = "do_del";
				break;

			default:
				$TargetOpCode = false;
				break;
		}

		if ($TargetOpCode != false)
		{
			// Get subgroup, record and item entries

			$SubgroupRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentSubgroupID);
			$RecordRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentRecordID);
			$EditID = false;
			if ($CurrentRecordItemID > 0)
			{
				$RecordItemRecord = ftree_get_item($GLOBALS["aib_db"],$CurrentRecordItemID);
				$EditRecord = $RecordItemRecord;
				$EditID = $CurrentRecordItemID;
			}
			else
			{
				$RecordItemRecord = false;
				$EditRecord = $RecordRecord;
				$EditID = $CurrentRecordID;
			}

			// Get parent folder type

			$RootFolder = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
			$OutBuffer[] = "<table class='browse-title-table'>";
			$OutBuffer[] = "<tr class='browse-title-row'>";
			$OutBuffer[] = "<td class='edit-record-title-cell'>";
			$OutBuffer[] = "<table width='100%' cellpadding='0' cellspacing='0'>";
			$OutBuffer[] = "<tr>";
			$OutBuffer[] = "<td align='right' width='20%'>Sub-Group: &nbsp; </td>";
			$OutBuffer[] = "<td class='edit-record-record-title'>".urldecode($SubgroupRecord["item_title"])."</td>";
			$OutBuffer[] = "</tr>";
			$OutBuffer[] = "<tr>";
			$OutBuffer[] = "<td align='right' width='20%'>Record: &nbsp; </td>";
			if ($CurrentRecordItemID <= 0)
			{
				$OutBuffer[] = "<td class='edit-record-record-title'>".urldecode($RecordRecord["item_title"])."</td>";
				$OutBuffer[] = "</tr>";
			}
			else
			{
				$OutBuffer[] = "<td class='edit-record-record-title'>".urldecode($RecordRecord["item_title"])."</td>";
				$OutBuffer[] = "</tr>";
				$OutBuffer[] = "<tr>";
				$OutBuffer[] = "<td align='right'>Item In Record: &nbsp;</td>";
				$OutBuffer[] = "<td class='edit-record-item-title'>".urldecode($RecordItemRecord["item_title"])."</td>";
				$OutBuffer[] = "<tr>";
			}

			$OutBuffer[] = "</table>";

			$OutBuffer[] = "</td>";
			$OutBuffer[] = "</tr>";
			$OutBuffer[] = "</table>";
			$OutBuffer[] = "<br>";

			// Create a table where there are two columns:  Left is for scrolling
			// photo banner, right is for field data

			$OutBuffer[] = "<table class='browse-item-table'>";

			// Banner area

			$OutBuffer[] = "<tr class='record-item-banner-row'>";
			$BannerWidth = (count($ChildItemList) + 1) * AIB_SCROLLABLE_IMAGE_WIDTH;
			$LowBannerScrollValue = $BannerWidth * -1;
			$OutBuffer[] = "<td class='record-item-banner-cell' colspan='99'>";
			$OutBuffer[] = "<table width='100%' class='browse-item-banner-table'>";
			$OutBuffer[] = "<tr class='browse-item-banner-table-row'>";
			$OutBuffer[] = "<td class='browse-item-banner-table-left-page-cell'>";
			$OutBuffer[] = "<button type='button' class='browse-item-banner-table-left-page-button' onclick=\"move_banner(this,'left');\" id='left_page_button'><img id='left_page_button_image' src='".AIB_SCROLL_LEFT_BUTTON_IMAGE."'></button>";
			$OutBuffer[] = "</td>";
			$OutBuffer[] = "<td class='browse-item-banner-table-banner-cell'>";
			$OutBuffer[] = "<div class='browse-item-banner-container'>";
			$OutBuffer[] = "<div class='browse-item-banner' style='width:$BannerWidth"."px;' id='browse_item_banner'>";

			// Show a "folder" icon as representing the entire record

			$OutBuffer[] = "<img class='browse-item-info-image' src=\"/images/smallvinyl.png\" id='record_info_icon'>";

			// If the current item is a record, show the items in the record.  Otherwise, show only the item itself.  When showing other items,
			// do not load item fields, just image.

			$FirstChild = false;
			$DisplayChildItemList = array();
			foreach($ChildItemList as $ChildRecord)
			{
				$ChildID = $ChildRecord["item_id"];
				$ChildTitle = $ChildRecord["item_title"];

				// Show thumbnail, if any available.  Otherwise a title indicating that the file can be opened

				$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ChildID);
				$ThumbURL = "/get_thumb.php?id=-1";
				$TempChildRecord = false;
				foreach($FileList as $FileRecord)
				{
					$TempMIME = urldecode($FileRecord["file_mime_type"]);
					if ($FileRecord["file_class"] == AIB_FILE_CLASS_THUMB && preg_match("/image/",$TempMIME) != false)
					{
						$TempChildRecord = $FileRecord;
						$ThumbID = $FileRecord["record_id"];
						$ThumbURL = "/get_thumb.php?id=$ThumbID";
						break;
					}
				}

				if ($TempChildRecord != false)
				{
					$DisplayChildItemList[] = $ChildRecord;
					$RefName = "child_item_".$ChildID;
					$OutBuffer[] = "<img class='browse-item-thumb-image' id='$RefName' src=\"$ThumbURL\">";
					if ($FirstChild === false)
					{
						$FirstChild = $ChildID;
					}
				}
			}

			if ($CurrentRecordItemID !== false)
			{
				if ($EditItemID != false)
				{
					$FirstChild = $EditItemID;
				}
			}

			if ($CurrentRecordItemID <= 0)
			{
				$HighlightInfoIcon = true;
			}

			$OutBuffer[] = "</div></div>";
			$OutBuffer[] = "</td>";
			$OutBuffer[] = "<td class='browse-item-banner-table-right-page-cell'>";
			$OutBuffer[] = "<button type='button' class='browse-item-banner-table-right-page-button' onclick=\"move_banner(this,'right');\" id='right_page_button'><img id='right_page_button_image' src='".AIB_SCROLL_RIGHT_BUTTON_IMAGE."'</button>";
			$OutBuffer[] = "</td>";
			$OutBuffer[] = "</tr></table>";
			$OutBuffer[] = "<td>";

			// End of banner row

			$OutBuffer[] = "</tr>";

			// Photo and field area

			$OutBuffer[] = "<tr class='record-item-content-row'>";
 			$ImageCount = count($DisplayChildItemList) + 1;
			$HighBannerScrollValue = (AIB_SCROLLABLE_IMAGE_WIDTH / 4) * -1;
			if ($HighBannerScrollValue < 0)
			{
				$HighBannerScrollValue = 0;
			}

			$BannerWidth = ($ImageCount + 1) * AIB_SCROLLABLE_IMAGE_WIDTH;
			if ($ImageCount > 1)
			{
				$LowBannerScrollValue = ($BannerWidth - AIB_SCROLLABLE_IMAGE_WIDTH) * -1;
			}
			else
			{
				$LowBannerScrollValue = $BannerWidth * -1;
			}

			$OutBuffer[] = "<td class='record-item-photo-cell' id='item_photo_cell'>";
			$OutBuffer[] = "</td>";
			$OutBuffer[] = "<td class='record-item-separator-cell'> </td>";

			// Field column.  Contains all data entry fields.

			$OutBuffer[] = "<td class='record-item-field-cell' id='item_field_cell'>";

			// Create table with values

			$PassedParent = "";
			$OutBuffer[] = "<form method='POST' action='/edit_record_form.php'>";
			$OutBuffer[] = "<input type='hidden' name='opcode' value='$TargetOpCode'>";
			$OutBuffer[] = "<input type='hidden' name='parent' value='$PassedParent'>";
			$OutBuffer[] = "<input type='hidden' name='primary' value='$CurrentSubgroupID'>";
			$OutBuffer[] = "<input type='hidden' name='record_id' value='$CurrentRecordID'>";
			$OutBuffer[] = "<input type='hidden' name='item_id' value='$CurrentRecordItemID'>";
			$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
			$OutBuffer[] = "<input type='hidden' name='src' value='".aib_get_with_default($FormData,"src","")."'>";
			$OutBuffer[] = "<input type='hidden' name='srckey' value='".aib_get_with_default($FormData,"srckey","")."'>";
			$OutBuffer[] = "<input type='hidden' name='searchval' value='".aib_get_with_default($FormData,"searchval","")."'>";
			$OutBuffer[] = "<input type='hidden' name='srcmode' value='".aib_get_with_default($FormData,"srcmode","")."'>";
			$OutBuffer[] = "<input type='hidden' name='srcpn' value='".aib_get_with_default($FormData,"srcpn","")."'>";
			$OutBuffer[] = "<table class='browse-item-field-table'>";
			$OutBuffer[] = "<tr class='browse-item-field-sep-row'>";
			$OutBuffer[] = "<td class='browse-item-field-sep-row-cell' colspan='99'> </td>";
			$OutBuffer[] = "</tr>";

			// Get form ID

			$FormID = ftree_field_get_item_form($GLOBALS["aib_db"],$EditID);

			// Get field data

			$FieldDataList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$EditID);
			if ($FieldDataList == false)
			{
				$FieldDataList = array();
			}

			// Show title field

			$FirstFieldID = "recordfield_title";
			$TempDef = array(
				"title" => "Title:",
				"field_name" => "recordfield_title",
				"field_id" => "recordfield_title",
				"value" => urldecode($EditRecord["item_title"]),
				);
			$TempDef["input_under"] = "Y";
			$TempDef["class_aib_input_field_title_cell"] = "aib-data-entry-field-title-cell";
			$OutBuffer[] = aib_draw_display_field($TempDef);

			// For each field, show input field.

			foreach($FieldDataList as $FieldID => $FieldInfo)
			{
				$FieldValue = urldecode($FieldInfo["value"]);

				// Get field definition

				$FieldDef = $FieldInfo["def"];

				// Create temporary display definition based on field definition

				$TempDef = array(
					"title" => urldecode($FieldDef["field_title"]),
					"field_name" => "formfield_".$FieldDef["field_id"],
					"field_id" => "formfield_".$FieldDef["field_id"],
					"value" => $FieldValue
					);

				switch($FieldDef["field_data_type"])
				{
					case FTREE_FIELD_TYPE_TEXT:
						$TempDef["type"] = "text";
						$TempDef["display_width"] = $FieldDef["field_size"];
						$TempDef["input_under"] = "Y";
						$TempDef["class_aib_input_field_title_cell"] = "aib-data-entry-field-title-cell";
						$OutBuffer[] = aib_draw_display_field($TempDef);
						break;

					case FTREE_FIELD_TYPE_BIGTEXT:
						$TempDef["type"] = "textarea";
						$TempDef["input_under"] = "Y";
						$TempDef["class_aib_input_field_title_cell"] = "aib-data-entry-field-title-cell";
						$LocalSeg = preg_split("/[\, ]+/",$FieldDef["field_size"]);
						if (count($LocalSeg) < 2)
						{
							$TempDef["display_width"] = preg_replace("/[^0-9]/","",$FieldDef["field_size"]);
							if (intval($TempDef["display_width"]) > 64)
							{
								$TempDef["display_width"] = "64";
							}

							$TempDef["rows"] = "10";
							$TempDef["cols"] = $TempDef["display_width"];
						}
						else
						{
							$TempDef["display_width"] = $LocalSeg[1];
							$TempDef["rows"] = $LocalSeg[0];
							$TempDef["cols"] = $LocalSeg[1];
						}

						$OutBuffer[] = aib_draw_display_field($TempDef);
						break;

					case FTREE_FIELD_TYPE_INTEGER:
					case FTREE_FIELD_TYPE_FLOAT:
					case FTREE_FIELD_TYPE_DECIMAL:
					case FTREE_FIELD_TYPE_DATE:
					case FTREE_FIELD_TYPE_TIME:
					case FTREE_FIELD_TYPE_DATETIME:
					case FTREE_FIELD_TYPE_TIMESTAMP:
					case FTREE_FIELD_TYPE_DROPDOWN:
					default:
						$TempDef["type"] = "text";
						$TempDef["display_width"] = $FieldDef["field_display_size"];
						$TempDef["input_under"] = "Y";
						$TempDef["class_aib_input_field_title_cell"] = "aib-data-entry-field-title-cell";
						$OutBuffer[] = aib_draw_display_field($TempDef);
						break;
				}
			}

			$OutBuffer[] = aib_draw_input_row_separator();
			switch($TargetOpCode)
			{
				case "save_record":
				case "del":
				case "do_del":
					$OutBuffer[] = aib_draw_form_submit("Delete Record","Undo Changes","aib-data-entry-submit-button","aib-data-entry-reset-button");
					break;

			}


			$OutBuffer[] = "</table>";

			// End of field area

			$OutBuffer[] = "</td>";

			// End of image/field row

			$OutBuffer[] = "</tr>";

			// End of table

			$OutBuffer[] = "</table>";

		}
		else
		{
			$Target = "/admin_main.php?parent=$PassedParent&srcmode=$SrcMode&srckey=$SourceKey&searchval=$SearchVal&srcpn=$SrcPn&aibnav=$NavString";
			$TargetSpec[] = array("url" => $Target, "title" => "Home Page", "fields" => array());
			$OutBuffer[] = aib_chain_link_set($TargetSpec);
		}
	}

	// End of browse content div

	$OutBuffer[] = "</div>";

	// End of center content row

	$OutBuffer[] = "</td></tr>";

	// Output center (record list) div

	print(join("\n",$OutBuffer));

	$QueryImageCode = bin2hex("itemimage");
	$QueryFieldCode = bin2hex("itemfield");
	$EncodedUserID = bin2hex(sprintf("%d",$UserID));

	// Include the footer

include('template/common_footer_admin.php');

	// Modal image display code

	$BottomBufferLines = array();
	$BottomBufferLines[] = "
	
	<!-- modal box -->

	<div id='myModal' class='modal'>
		<span class='close' id='close_modal'>&times;</span>
		<img class='modal-content' id='img01'>
		<div id='caption'></div>
	</div>
	";

	print(join("\n",$BottomBufferLines));

	if ($HighlightInfoIcon === false)
	{
		$FooterScriptLines[] = "
			var HighlightInfoIcon = false;
			";
	}
	else
	{
		$FooterScriptLines[] = "
			var HighlightInfoIcon = true;
			";
	}


	// Add any footer script

	$FooterScriptLines[] = "
		var LowBannerScrollValue = $LowBannerScrollValue;
		var HighBannerScrollValue = $HighBannerScrollValue;
		var CurrentBannerScrollValue = $HighBannerScrollValue;
		var InitialItemLoaded = -1;

		// Initialize banner

		\$('#browse_item_banner').css('margin-left',CurrentBannerScrollValue.toString() + 'px');

		function move_banner(RefObj,Direction)
		{
			if (Direction == 'left')
			{
				// Scroll images to the left

				\$('#right_page_button').prop('disabled',false);
				\$('#right_page_button_image').css('-webkit-filter','');
				\$('#right_page_button_image').css('filter','');
				CurrentBannerScrollValue = CurrentBannerScrollValue + 100;
				if (CurrentBannerScrollValue >= HighBannerScrollValue)
				{
					CurrentBannerScrollValue = HighBannerScrollValue;
					\$('#left_page_button').prop('disabled',true);
					\$('#left_page_button').prop('disabled',true);
					\$('#left_page_button_image').css('-webkit-filter','blur(4px)');
					\$('#left_page_button_image').css('filter','blur(4px)');
				}
			}
			else
			{
				// Scroll images to the right

				\$('#left_page_button').prop('disabled',false);
				\$('#left_page_button_image').css('-webkit-filter','');
				\$('#left_page_button_image').css('filter','');
				CurrentBannerScrollValue = CurrentBannerScrollValue - 100;
				if (CurrentBannerScrollValue < LowBannerScrollValue)
				{
					CurrentBannerScrollValue = LowBannerScrollValue;
					\$('#right_page_button').prop('disabled',true);
					\$('#right_page_button_image').css('-webkit-filter','blur(4px)');
					\$('#right_page_button_image').css('filter','blur(4px)');
				}
			}

			\$('#browse_item_banner').css('margin-left',CurrentBannerScrollValue.toString() + 'px');
		}

		function load_item_data(RefName,ChildID)
		{
			var QueryParam = {};
			
			QueryParam['o'] = '$QueryImageCode';
			QueryParam['i'] = '$EncodedUserID';
			QueryParam['pn'] = 1;
			QueryParam['checks'] = '';
			QueryParam['checks_title'] = '';
			QueryParam['pic'] = 1;
			QueryParam['lop'] = 'get';
			QueryParam['id'] = 'aibitem';
			QueryParam['key'] = ChildID;
			QueryParam['edit_mode'] = 'Y';
			aib_ajax_request('/services/browseair.php',QueryParam,show_record,error_record);

//			QueryParam['o'] = '$QueryFieldCode';
//			aib_ajax_request('/services/browseair.php',QueryParam,show_record,error_record);
			if (HighlightInfoIcon == false)
			{
				\$('#' + RefName).css('border','3px solid lightgreen');
			}
			else
			{
				\$('#record_info_icon').css('border','3px solid lightgreen');
			}

		}

	function show_record(InData)
	{
		var InfoType;

		if (InData['status'] != 'OK')
		{
			alert('Error processing fetch request: ' + InData['info']['msg']);
			return;
		}

		InfoType = InData['info']['type'];
		if (InfoType == 'image')
		{
			\$('#item_photo_cell').html(InData['info']['html']);
		}

		if (InfoType == 'field')
		{
			\$('#item_field_cell').html(InData['info']['html']);
		}
	}

	function error_record(ReqObj,ErrorStatus,ErrorText)
	{
		alert('Request error: ' + ErrorStatus + '; ' + ErrorText);
	}

	// Set up handler for photo area to allow zoom.  When image is clicked,
	// the image URL is transferred to the image element in the modal, and
	// the modal display mode is set to 'block'.

//	var PhotoAreaElement = document.getElementById('item_photo_cell');
//	PhotoAreaElement.onclick = function() {
//	
//		var ImageURL;
//		var ImageElement;
//		var TitleElement;
//		var CaptionElement;
//		var TitleText;
//		var ModalImage;
//		var ModalBox;
//
//		// Get sources
//
//		ImageElement = document.getElementById('browse_image');
////		TitleElement = document.getElementById('item_title_cell');
//		CaptionElement = document.getElementById('caption');
//		ModalImage = document.getElementById('img01');
//		ModalBox = document.getElementById('myModal');
//		ImageURL = ImageElement.src;
//		TitleText = TitleElement.innerHTML;
//
//		// Copy image url to modal
//
//		ModalImage.src = ImageURL;
//
//		// Copy text to caption
//
////		CaptionElement.innerHTML = TitleText;
//
//		// Show modal
//
//		ModalBox.style.display = 'block';
//	}
//	

	// Set up click handler for close button.  When user clicks on the span, the modal closes.

	var CloseButtonSpan = document.getElementById('close_modal');
	CloseButtonSpan.onclick = function() {
		var ModalBox;

		ModalBox = document.getElementById('myModal');
		ModalBox.style.display = 'none';
	}


	";

	if ($FirstChild !== false)
	{
		if ($FirstChild >= 0)
		{
			$RefName = "child_item_".$FirstChild;
			$FooterScriptLines[] = "// Load first image";
			$FooterScriptLines[] = "load_item_data('$RefName',$FirstChild);";
		}
		else
		{
			$FooterScriptLines[] = "// Load first image";
			$FooterScriptLines[] = "load_item_data('$RefName',$FirstChild);";
		}
	}

//	if ($FirstFieldID !== false && $EditItemID === false)
	if ($FirstFieldID !== false)
	{
		$FooterScriptLines[] = "
		// Set focus to first field

		document.getElementById(\"$FirstFieldID\").focus();

		// If the reset button loses focus, go to the first field

		var ResetButtonElement = document.getElementById('aib_reset_button');
		ResetButtonElement.onblur = function() {
			document.getElementById(\"$FirstFieldID\").focus();
		}
		
		";
	}

	if ($EditItemID !== false)
	{
		$FooterScriptLines[] = "

		if (InitialItemLoaded < 1)
		{
			InitialItemLoaded = 1;
			load_item_data('child_item_$EditItemID','$EditItemID');
		}

		";
	}

	print("<script>".join("\n",$FooterScriptLines)."</script>");
?>

<?php

	// Output end of page

include('template/common_end_of_page_admin.php');

	// Close database

	aib_close_db();
	exit(0);
?>
