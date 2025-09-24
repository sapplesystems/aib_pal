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
			$ErrorText = bin2hex("Unauthorized");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	$PrimaryKey = $ParentItem;
	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	aib_update_nav_info("src","admins.php");
	aib_update_nav_info("src_title","Manage Client Administrators");
	$NavString = aib_get_nav_string();
	$NavTargetInfo = aib_get_nav_target();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array default values

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: MANAGE ARCHIVE GROUP ADMINISTRATORS",
		"popup_list" => array(),
		"current_menu" => "Super Admin: Manage Administrators",
	);

	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: MANAGE ARCHIVE GROUP ADMINISTRATORS";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_title"].": MANAGE ARCHIVE GROUP ADMINISTRATORS";
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

				$PreloadBuffer = aib_generate_list_preload("aibadmins",$TotalListPages);

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
				$OutBuffer[] = "Archive Group: <select name='aibadmins-key' id='aibadmins-key' class='aib-selectarchive-dropdown'>";
				if (aib_open_db() != false)
				{
					$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
					$ArchiveList = array(array('id' => "NULL", "title" => " -- SELECT -- "));
					$TempList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchivesFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER);
					foreach($TempList as $TempRecord)
					{
						$Title = ftree_get_property($GLOBALS["aib_db"],$TempRecord["item_id"],"archive_name");
						if ($Title == false)
						{
							$Title = "(No title available)";
						}

						$ArchiveList[] = array("id" => $TempRecord["item_id"], "title" => $TempRecord["item_title"]." -- $Title");
					}

					aib_close_db();
				}
				else
				{
					$ArchiveList = array(array("id" => "NULL", "title" => "No archives available"));
				}

				foreach($ArchiveList as $ArchiveRecord)
				{
					if ($ArchiveCode != false && $ArchiveRecord["id"] == $ArchiveCode)
					{
						$OutBuffer[] = "<option value='".$ArchiveRecord["id"]."' SELECTED >".$ArchiveRecord["title"]."</option>";
					}
					else
					{
						$OutBuffer[] = "<option value='".$ArchiveRecord["id"]."'>".$ArchiveRecord["title"]."</option>";
					}
				}

				$OutBuffer[] = "</select>";
				$OutBuffer[] = "</div>";

				// Show "add admin" link

				$LocalSourceKey = aib_get_with_default($FormData,"srckey","");
				$LocalSearchValue = aib_get_with_default($FormData,"searchval","");
				$LocalSourceMode = aib_get_with_default($FormData,"srcmode","");
				$LocalSourcePageNumber = aib_get_with_default($FormData,"srcpn","");
				$TargetFields = join("&",array("src=fields","srckey=$LocalSourceKey","searchval=$LocalSearchValue","srcmode=$LocalSourceMode","srcpn=$LocalSourcePageNumber"));
				$TitleRow = "<tr class='aib-folder-type-row'>";
				$TitleCell = "<td class='aib-folder-type-cell'>";
				$MenuCell = "<td class='aib-folder-menu-cell'>";
				$MenuCell .= "<a href='/admin_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Add Administrator</a>";
				$TitleCell .= "</td>";
				$TitleRow .= $TitleCell;
				$MenuCell .= "</td>";
				$TitleRow .= $MenuCell;
				$TitleRow .= "</td></tr>";


			// Generate list table.  First, create div to hold table data.

			$OutBuffer[] = "<div name='aibadmins' id='aibadmins-data-div' class='aib-generic-list-div'>";

			// Generate frame of the list without data.  This also sets up bindings for button events, etc..
			// The content will be generated by an AJAX request which generates the lines using the
			// aib_generate_generic_list_inner_html() function.

			$ListParam = array();
			$ListParam["columns"] = array(
				"user_login" => "Login",
				"user_title" => "Name",
				"user_top_folder" => "Archive Group",
				".op" => "",
				);
			$ListParam["searchable"] = array(
				"user_login" => "Login",
				"user_title" => "Name",
				".user_top_folder" => "Archive Group",
				);
			$ListParam["pagecount"] = 1;
			$ListParam["pagenum"] = 1;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$ListParam["extra_title_rows"][] = $TitleRow;
			$OutBuffer[] = aib_generate_generic_list_frame_html("aibadmins",$ListParam);


			// End of table data area

			$OutBuffer[] = "</div>";

			// Generate script and any html required to make table interactive.

			$Spec = array(
				"opcode" => "laa",
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
					\$('#aibadmins-key').change(function(Event) {
						listquery_aibadmins();
					});
				";
			}

			$ListData = aib_generate_scroll_table_handler(AIB_SUPERUSER,"aibadmins",$Spec);
			$FooterScriptLines[] = $ListData["script"];
			$OutBuffer[] = "</div>";
			break;
	}

	$DisplayData["content"] = join("\n",$OutBuffer);
	$DisplayData["footer_lines"] = join("\n",$FooterScriptLines);

include('template/common_list.php');
	exit(0);
?>
