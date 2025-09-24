<?php
//
// login.php
//

// FUNCTIONAL INCLUDES

include('config/aib.php');
include("include/folder_tree.php");
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

	// Based on the type of user, set the main archive

	$UserType = $UserRecord["user_type"];
	$UserID = $UserRecord["user_id"];
	$ParentItem = -1;
	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$ParentItem = -1;
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$ParentItem = $UserRecord["user_top_folder"];
			break;
	}

	$PrimaryKey = $ParentItem;
	$FormData = aib_get_form_data();

	// Get nav info, if any

	aib_get_nav_info($FormData);
	aib_update_nav_info("src","assistants.php");
	aib_update_nav_info("src_title","Assistant Management");
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array default values

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: MANAGE ARCHIVE ASSISTANTS",
		"popup_list" => array(),
		"current_menu" => "Manage Administrators",
	);

	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: MANAGE ARCHIVE ASSISTANTS";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_title"].": MANAGE ARCHIVE ASSISTANTS";
			break;
	}

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
	}


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
				$ArchiveList = ftree_list_child_objects($GLOBALS["aib_db"],$ParentItem,false,false,FTREE_OBJECT_TYPE_FOLDER);
				if ($ArchiveList != false)
				{
					$TotalListPages = intval((count($ArchiveList) / intval(AIB_DEFAULT_ITEMS_PER_PAGE)) + 1);
				}
				else
				{
					$TotalListPages = 0;
				}

				aib_close_db();

				// Generate pre-load functions for HTML page header

				$PreloadBuffer = aib_generate_list_preload("aibassistants",$TotalListPages);

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

	// Process based on opcode

	switch($OpCode)
	{
		case "list":
		case false:
		default:

			// If the user is a super-user, then add a drop-down box to select the archive.  Otherwise, expect from parent folder of user.

			if ($UserType == FTREE_USER_TYPE_ROOT)
			{
				if (isset($FormData["archive_code"]) == true)
				{
					$ArchiveCode = $FormData["archive_code"];
				}
				else
				{
					$ArchiveCode = false;
				}

				$OutBuffer[] = "<div name='aibselectarchive-div' id='aibselectarchive-div' class='aib-selectarchive-div'>";
				$OutBuffer[] = "<br><br>";
				$OutBuffer[] = "Current Archive: <select name='aibassistants-key' id='aibassistants-key' class='aib-selectarchive-dropdown'>";
				aib_open_db();
				$OwnerList = array("NULL" => " -- SELECT --");
				$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
				if ($ArchiveGroupList != false)
				{
					foreach($ArchiveGroupList as $ArchiveGroupRecord)
					{
						$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
						$ArchiveGroupTitle = $ArchiveGroupRecord["item_title"];
						$ArchiveGroupCode = $ArchiveGroupRecord["_archive_group_code"];
						$OwnerList["ag:".$ArchiveGroupID] = $ArchiveGroupTitle;
						$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$ArchiveGroupID);
						foreach($ArchiveList as $ArchiveRecord)
						{
							$ArchiveID = $ArchiveRecord["item_id"];
							$ArchiveTitle = $ArchiveRecord["item_title"];
							$OwnerList["ar:".$ArchiveID] = "&#9492; $ArchiveTitle";
						}
					}
				}

				aib_close_db();
				$ArchiveList = $OwnerList;
				foreach($ArchiveList as $LocalArchiveID => $LocalArchiveTitle)
				{
					$LocalID = preg_replace("/[^0-9]/","",$LocalArchiveID);
					if ($LocalID == $ArchiveCode)
					{
						$OutBuffer[] = "<option value=\"$LocalArchiveID\" SELECTED >$LocalArchiveTitle</option>";
					}
					else
					{
						$OutBuffer[] = "<option value=\"$LocalArchiveID\">$LocalArchiveTitle</option>";
					}
				}

				$OutBuffer[] = "</select>";
				$OutBuffer[] = "</div>";

			}
			else
			{
				$OutBuffer[] = "<input type='hidden' name='aibassistants-key' id='aibassistants-key' value='ag:$ParentItem'>";
			}

			// Generate list table.  First, create div to hold table data.

			$OutBuffer[] = "<div name='aibassistants' id='aibassistants-data-div' class='aib-generic-list-div'>";

			// Generate frame of the list without data.  This also sets up bindings for button events, etc..
			// The content will be generated by an AJAX request which generates the lines using the
			// aib_generate_generic_list_inner_html() function.

			$ListParam = array();
			$ListParam["columns"] = array(
				"user_login" => "Login",
				"user_title" => "Name",
				"user_top_folder" => "Archive",
				".op" => "",
				);
			$ListParam["searchable"] = array(
				"user_login" => "Login",
				"user_title" => "Name",
				".user_top_folder" => "Top Folder",
				);
			$ListParam["pagecount"] = 1;
			$ListParam["pagenum"] = 1;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$OutBuffer[] = aib_generate_generic_list_frame_html("aibassistants",$ListParam);


			// End of table data area

			$OutBuffer[] = "</div>";

			// Generate script and any html required to make table interactive.

			$NavString = aib_get_nav_string();
			$Spec = array(
				"opcode" => "las",
				"rows" => AIB_DEFAULT_ITEMS_PER_PAGE,
				"total_pages" => $TotalListPages,
				"url" => "/services/air.php",
				"key" => "",
				"lop" => "list",
				"aibnav" => $NavString,
				);

			// Set up trigger so that when a new selection is made for an archive, the list is redisplayed

			if ($UserType == FTREE_USER_TYPE_ROOT)
			{
				$Spec["extra_init_code"] = "
					\$('#aibassistants-key').change(function(Event) {
						listquery_aibassistants();
					});
				";
			}

			$ListData = aib_generate_scroll_table_handler(AIB_SUPERUSER,"aibassistants",$Spec);
			$FooterScriptLines[] = $ListData["script"];
			break;
	}

	$DisplayData["content"] = join("\n",$OutBuffer);

	// Add any footer script

	$DisplayData["footer_lines"] = join("\n",$FooterScriptLines);
?>

<?php

include('template/common_list.php');
	exit(0);
?>
