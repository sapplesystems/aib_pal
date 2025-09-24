<?php
//
// login.php
//

// FUNCTIONAL INCLUDES

include('config/aib.php');
include("include/folder_tree.php");
include("include/fields.php");
include('include/aib_util.php');

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
	$FormData = aib_get_form_data();

	// Get navigation info

	aib_get_nav_info($FormData);
	aib_update_nav_info("src","records.php");
	aib_update_nav_info("src_title","Record Management");
	aib_update_nav_info("src_opcode",aib_get_with_default($FormData,"opcode","list"));
	$NavString = aib_get_nav_string();

	// Based on the type of user, set the opening folder if there is not a current opening folder defined

	$UserType = $UserRecord["user_type"];
	$ParentFolderID = aib_get_with_default($FormData,"parent",-1);

	// If no parent, see if there is one in the nav info

	if ($ParentFolderID < 0)
	{
		$NavID = aib_get_nav_value("primary",false);
		if ($NavID !== false)
		{
			$ParentFolderID = $NavID;
		}
	}

	// Update navigation settings to reflect current primary value

	aib_update_nav_info("primary",$ParentFolderID);
	$NavString = aib_get_nav_string();

	// Set page title

	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			if ($ParentFolderID < 0)
			{
				aib_open_db();
				$ParentFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
				aib_close_db();
			}

			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: RECORDS";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_login"]." / ".$UserRecord["user_title"].": RECORDS";
			if ($ParentFolderID < 0)
			{
				$ParentFolderID = $UserRecord["user_top_folder"];
			}

			break;
	}

	// Get operation to perform

	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array default values and indicate the current menu option

	$DisplayData["popup_list"] = array();
//	$DisplayData["current_menu"] = "Upload / Manage Records";
	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: MANAGE RECORDS";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_title"].": MANAGE RECORDS";
			break;
	}

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
	}

	$PageTimeout = AIB_SESSION_TIMEOUT;
	$PageTimeout = $PageTimeout * 1000;
	$DisplayData["head_script"] = "
		setTimeout(function() {
			window.location.href='/login.php';
		},$PageTimeout);

		// Prevent the use of the 'back' button to prevent mangling
		// forms.

		history.pushState(null, null, document.URL);
		window.addEventListener('popstate', function () {
		    alert(\"Please use the links on the page instead of the 'Back' button.\");
		    history.pushState(null, null, document.URL);
		});


		";


	// Set up initialization JavaScript based on the opcode

	$TotalListPages = 0;
	$PreloadBuffer = "";
	switch($OpCode)
	{
		// If viewing a list, set up code which generates initialization signal to load list with first page of data.
		// General operation is:
		//
		//	1) Generate preload code.  Page count is preset to whatever value reflects the initial page count without searches.
		//	2) Generate list division container
		//	3) Generate list script code for base of page; this handles paging buttons and search requests
		//	4) AJAX call made to service, which calculates the updated page count and generates the HTML for the
		//	   division based on current operation (search, list) and the selected page number.
		//

		case "list":
		case false:
		default:
			if (aib_open_db() != false)
			{
				$ArchiveList = ftree_list_child_objects($GLOBALS["aib_db"],$ParentFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER);
				if ($ArchiveList != false)
				{
					$TotalListPages = intval((count($ArchiveList) / intval(AIB_DEFAULT_TREE_ITEMS_PER_PAGE)) + 1);
				}
				else
				{
					$TotalListPages = 0;
				}

				// Generate pre-load functions for HTML page header

				$PreloadBuffer = aib_generate_list_preload("aibrecords",$TotalListPages);
				aib_close_db();

				// Add preload functions to header script area

				if (isset($DisplayData["head_script"]) == false)
				{
					$DisplayData["head_script"] = $PreloadBuffer;
				}
				else
				{
					$DisplayData["head_script"] .= $PreloadBuffer;
				}

				break;
			}

			break;
	}

	// Set up footer script information

	$FooterScriptLines = array();

	$OutBuffer = array();
	$StatusMessage = false;
	$ErrorMessage = false;

	// Set up return to parent link

	aib_open_db();
	$ParentFolderRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);
	$TempParent = $ParentFolderRecord["item_parent"];
	$TempRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderRecord["item_parent"]);
	$TempType = ftree_get_property($GLOBALS["aib_db"],$ParentFolderRecord["item_parent"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$RootFolder = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
	aib_close_db();
	$TargetFields = join("&",array("parent=$TempParent",aib_get_nav_string()));
	$UpLink = "/records.php?$TargetFields";
	switch($TempType)
	{
		case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			$UpTitle = "Go Back Up To Organization: <span class='uplink-item-title'>".urldecode($TempRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_ARCHIVE:
			$UpTitle = "Go Back Up To Archive: <span class='uplink-item-title'>".urldecode($TempRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_COLLECTION:
			$UpTitle = "Go Back Up To Collection: <span class='uplink-item-title'>".urldecode($TempRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_SUBGROUP:
			$UpTitle = "Go Back Up To Sub-Group: <span class='uplink-item-title'>".urldecode($TempRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_RECORD:
			$UpTitle = "Go Back Up To Record: <span class='uplink-item-title'>".urldecode($TempRecord["item_title"])."</span>";
			break;

		default:
			$UpLink = false;
			break;
	}

	if ($UpLink != false)
	{
		$OutBuffer[] = "<div class='browse-uplink-div'><a href='$UpLink' class='browse-uplink-link'>$UpTitle</a></div>";
	}



	// Process based on opcode

	switch($OpCode)
	{
		case "list":
		case false:
		default:

			// Generate list table.  First, create div to hold table data.

			$OutBuffer[] = "<div name='aibrecords' id='aibrecords-data-div' class='aib-generic-list-div'>";

			// Set parent folder field as a hidden field usable by the list processing code

			$OutBuffer[] = "<input type='hidden' name='aibrecords-key' value='$ParentFolderID'>";
			$OutBuffer[] = "<input type='hidden' name='aibrecords-key' id='aibrecords-key' value='$ParentFolderID'>";

			// Generate frame of the list without data.  This also sets up bindings for button events, etc..
			// The content will be generated by an AJAX request which generates the lines using the
			// aib_generate_generic_list_inner_html() function.

			$ListParam = array();
			$ListParam["columns"] = array(
				"item_title" => "Title",
				"item_source_type" => "Type",
				"item_create_stamp" => "Created",
				".op" => "",
				);
			$ListParam["col_width"] = array(
				"item_title" => "60%",
				"item_source_type" => "10%",
				"item_create_stamp" => "20%",
				".op" => "10%",
				);
			$ListParam["searchable"] = array(
				"item_title" => "Login",
				"item_source_type" => "Name",
				"item_create_stamp" => "Created",
				);
			$ListParam["pagecount"] = 1;
			$ListParam["pagenum"] = 1;
			$ListParam["pagesize"] = AIB_DEFAULT_TREE_ITEMS_PER_PAGE;
			$ListParam["extra_title_rows"] = array();
			aib_open_db();

			// Set up return path and "go up" link

			$UserType = $UserRecord["user_type"];
			$UserGroup = $UserRecord["user_primary_group"];

			// Get ID path for parent

			$ParentRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);
			$ParentTitle = urldecode($ParentRecord["item_title"]);
			$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"archive_name");
			$IDPathList = ftree_get_item_id_path($GLOBALS["aib_db"],$ParentFolderID);

			// Trim everything before the default root item for the user

			$UserRoot = $UserRecord["user_top_folder"];
			if ($UserType != FTREE_USER_TYPE_ROOT)
			{
				while(true)
				{
					if (count($IDPathList) == 0)
					{
						break;
					}

					if ($IDPathList[0] == $UserRoot)
					{
						break;
					}

					array_shift($IDPathList);
				}
			}

			// Now that we have the list of ID values, create a URL string with the
			// ID values as segments.

			$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
			$ParentPathList = array();
			$ArchiveTitle = false;
			$ArchiveCodeTitle = false;
			$ArchiveCode = "";
			foreach($IDPathList as $LocalID)
			{
				$LocalRecord = ftree_get_item($GLOBALS["aib_db"],$LocalID);
				if ($LocalRecord == false)
				{
					continue;
				}

				$FullTitle = urldecode($LocalRecord["item_title"]);
				$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$LocalID,"archive_name");
				if ($ArchiveNameProp != false)
				{
					$ArchiveCode = $LocalID;
					$ArchiveTitle = $ArchiveNameProp;
					$ArchiveCodeTitle = urldecode($LocalRecord["item_title"]);
					$FullTitle .= " -- $ArchiveNameProp";
				}

				$Temp = "<a class='aib-folder-path-link' href='/records.php?opcode=list&parent=".$LocalRecord["item_id"]."'>$FullTitle</a>";
				$ParentPathList[] = $Temp;
			}

			$IndicatorEntryTemplate = "<a class='aib-loc-path-link' href='/records.php?opcode=list&parent=[[ITEMID]]'>[[TITLE]]</a>";
			$IndicatorOptions = array("entry_template" => $IndicatorEntryTemplate, "ul_template" => "<ul class='aib-loc-indicator-list'>");
			$IndicatorOptions["pad_cell_template"] = "<td width='5'></td>";
			$IndicatorOptions["entry_cell_template"] = "<td colspan='99'>";
			$IndicatorOptions["symbol_cell_template"] = "<td style='width:0.5em; padding:0;'><span style='font-size:1.5em; color:#a0a0a0;'>&#9492;</span></td>";
			$IndicatorOptions["table_template"] = "<table width='100%' cellpadding='0' cellspacing='0'>";
			$RightColContentLines = array();
			$RightColContentLines[] = "<div class='aib-loc-indicator-div'>";
			$RightColContentLines[] = aib_generate_loc_indicator_table($GLOBALS["aib_db"],$IndicatorOptions,$IDPathList);
			$RightColContentLines[] = "<div class='clearitall'></div>";
			$RightColContentLines[] = "</div>";
			$DisplayData["right_col"] = join("\n",$RightColContentLines);

			// Create a "go up level" link

			$LastID = false;
			while(true)
			{
				if (count($IDPathList) < 1)
				{
					$LastID = false;
					break;
				}

				$LastID = array_pop($IDPathList);
				if ($LastID == $ParentFolderID)
				{
					continue;
				}

				break;
			}

			if ($LastID !== false)
			{
				$LocalRecord = ftree_get_item($GLOBALS["aib_db"],$LastID);
				$FullTitle = urldecode($LocalRecord["item_title"]);
				$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$LastID,"archive_name");
				if ($ArchiveNameProp != false)
				{
					$FullTitle .= " -- $ArchiveNameProp";
				}
			}

			$ListParam["extra_title_rows"][] = "<tr class='aib-records-list-extra-title-sep-row'><td class='aib-records-list-extra-title-sep-cell' colspan='99'> </td></tr>";

			// Show type of list based on current folder type

			$TitleCell = "<td class='aib-folder-type-cell'>";
			$MenuCell = "<td class='aib-folder-menu-cell'>";
			$ArchiveTitle = urldecode($ParentRecord["item_title"]);
			$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"archive_name");
			$FolderType = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"aibftype");
			$LocalTitle = urldecode($ParentRecord["item_title"]);
			if (preg_match("/[%][0-9A-Fa-f]+/",$LocalTitle) != false)
			{
				$LocalTitle = urldecode($LocalTitle);
			}

			if (preg_match("/[^+][+]+[^+]/",$LocalTitle) != false)
			{
				$LocalTitle = urldecode($LocalTitle);
			}

			$LocalSourceKey = aib_get_with_default($FormData,"srckey","");
			$LocalSearchValue = aib_get_with_default($FormData,"searchval","");
			$LocalSourceMode = aib_get_with_default($FormData,"srcmode","");
			$LocalSourcePageNumber = aib_get_with_default($FormData,"srcpn","");
			$TargetFields = join("&",array("src=records","parent=$ParentFolderID","srckey=$LocalSourceKey","searchval=$LocalSearchValue","srcmode=$LocalSourceMode","srcpn=$LocalSourcePageNumber","aibnav=$NavString"));
			switch($FolderType)
			{
				// Collection
				case AIB_ITEM_TYPE_COLLECTION:
					$TitleCell .= "<span class='aib-folder-type-span'>Sub Groups In </span><span class='aib-folder-type-title-span'>$LocalTitle</span>";
					$MenuCell .= "<a href='/subgroup_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Create Sub Group</a>";
					$MenuCell .= " &nbsp; ";
					$MenuCell .= "<a href='javascript:;' class='aib-folder-menu-link' onclick=\"assign_data_entry('$ParentFolderID','$LocalSourceKey','$LocalSearchValue','$LocalSourceMode','$LocalSourcePageNumber');\">Assignments</a>";
					break;

				// Sub-group
				case AIB_ITEM_TYPE_SUBGROUP:
					$TitleCell .= "<span class='aib-folder-type-span'>Records And Sub-Groups In </span><span class='aib-folder-type-title-span'>$LocalTitle</span>";
					$MenuCell .= "<a href='/subgroup_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Create Sub Group</a>";
					$MenuCell .= " &nbsp; ";
					$MenuCell .= "<a href='/record_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Add Record</a>";
					$MenuCell .= " &nbsp; ";
					$MenuCell .= "<a href='javascript:;' class='aib-folder-menu-link' onclick=\"assign_data_entry('$ParentFolderID','$LocalSourceKey','$LocalSearchValue','$LocalSourceMode','$LocalSourcePageNumber');\">Assignments</a>";
					break;

				// Record
				case AIB_ITEM_TYPE_RECORD:
					$TitleCell .= "<span class='aib-folder-type-span'>Items In </span><span class='aib-folder-type-title-span'>$LocalTitle</span>";
					$MenuCell .= "<a href='/record_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Add Item</a>";
					break;

				// Archive group
				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					$FullTitle = "Archives In Archvive Group $ArchiveTitle";
					$TitleCell .= "<span class='aib-folder-type-span'>Archives In </span><span class='aib-folder-type-title-span'>$ArchiveTitle</span>";
					$MenuCell .= "<a href='/admin_archiveform.php?opcode=add&archive_group_code=$ParentFolderID&$TargetFields' class='aib-folder-menu-link'>Create Archive</a>";
					break;

				// Archive
				case AIB_ITEM_TYPE_ARCHIVE:
					$FullTitle = "Collections In $ArchiveCodeTitle -- $ArchiveTitle";
					$TitleCell .= "<span class='aib-folder-type-span'>Collections In </span><span class='aib-folder-type-title-span'>$ArchiveTitle</span>";
					$MenuCell .= "<a href='/collection_form.php?opcode=add&archive_code=$ParentFolderID&$TargetFields' class='aib-folder-menu-link'>Create Collection</a>";
					break;

				default:
					$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"archive_name");
					if ($ArchiveNameProp != false)
					{
						$FullTitle = "Collections In $ArchiveCodeTitle -- $ArchiveTitle";
						$TitleCell .= "<span class='aib-folder-type-span'>Collections In </span><span class='aib-folder-type-title-span'>$ArchiveTitle</span>";
						$MenuCell .= "<a href='/collection_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Create Collection</a>";
					}

					if ($ArchivesFolderID == $ParentFolderID)
					{
						$TitleCell .= "<span class='aib-folder-type-span'>Master Archive Group List</span>";
						$MenuCell .= "<a href='/archivegroup_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Create Archive Group</a>";
					}

					break;
			}

			$MenuCell .= "</td>";
			$TitleCell .= "</td>";
			$TitleRow = "<tr class='aib-folder-type-row'>$TitleCell $MenuCell</tr>";
			$ListParam["extra_title_rows"][] = $TitleRow;

			// Add a checkbox for each row

			if ($UserType == FTREE_USER_TYPE_ADMIN || $UserType == FTREE_USER_TYPE_ROOT)
			{
				$ListParam["checks"] = "item_id";
				$ListParam["checks_title"] = "";
			}

			// Generate list frame

			$OutBuffer[] = aib_generate_generic_list_frame_html("aibrecords",$ListParam);
			aib_close_db();

			// Generate script and any html required to make table interactive.

			$Spec = array(
				"opcode" => "lr",
				"rows" => AIB_DEFAULT_TREE_ITEMS_PER_PAGE,
				"total_pages" => $TotalListPages,
				"url" => "/services/airrecord.php",
				"key" => "",
				"lop" => "list",
				"aibnav" => $NavString,
				"extra_param" => array(				# Extra parameters for AJAX call
					"refsrc" => "records"
					),
				);

			// If the current user is an admin or superuser, add checkboxes

			if ($UserType == FTREE_USER_TYPE_ADMIN || $UserType == FTREE_USER_TYPE_ROOT)
			{
				$Spec["checks"] = "item_id";
				$Spec["checks_title"] = "<i>Select</i>";
			}
			else
			{
				$Spec["checks"] = "";
				$Spec["checks_title"] = "";
			}

			// Set up trigger so that when a new selection is made for an archive, the list is redisplayed

			if ($UserType == FTREE_USER_TYPE_ROOT)
			{
				$Spec["extra_init_code"] = "
					\$('#aibrecords-key').change(function(Event) {
						listquery_aibrecords();
					});
				";
			}

			$Spec["extra_params"] = array();
			$ListData = aib_generate_scroll_table_handler(AIB_SUPERUSER,"aibrecords",$Spec);
			$FooterScriptLines[] = $ListData["script"];
			$DisplayData["content"] = join("\n",$OutBuffer);
			break;
	}

	// Handlers for forms, etc.

	$FooterScriptLines[] = "

	<script>

	var NavString = \"$NavString\";
	function assign_data_entry(ParentFolderID,SourceKey,SearchValue,SourceMode,PageNumber)
	{
		var ListOfCheckBoxes;
		var Index;
		var ListSize;
		var ElementObject;
		var ListOfIDValues;
		var ListOfIDString;
		var URL;

		// Get all checked items

		ListOfCheckBoxes = \$('[id^=record_checkbox]');
		ListSize = ListOfCheckBoxes.length;
		ListOfIDValues = [];
		for (Index = 0; Index < ListSize; Index++)
		{
			ElementObject = ListOfCheckBoxes[Index];
			if (\$(ElementObject).is(':checked'))
			{
				ListOfIDValues.push(\$(ElementObject).val());
			}
		}

		// Create string

		ListOfIDString = ListOfIDValues.join(',');

		// Create URL

		URL = '/assign_data_entry_form.php?opcode=assign&parent=' + ParentFolderID +
			'&srckey=' + SourceKey + '&searchval=' + SearchValue + '&srcmode=' + SourceMode +
			'&srcpn=' + PageNumber + '&idlist=' + ListOfIDString + '&aibnav=' + NavString;
		window.open(URL,'_self');

	}
	</script>

	";
	// Include the footer

	$DisplayData["footer_lines"] = join("\n",$FooterScriptLines);

	// Grab the list template and output page

	include("template/common_list.php");
	exit(0);
?>
