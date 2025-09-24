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
			$ParentItem = $UserRecord["user_top_folder"];
			break;
	}

	$PrimaryKey = $ParentItem;
	$FormData = aib_get_form_data();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array default values

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: MANAGE COLLECTIONS",
		"popup_list" => array(),
		"current_menu" => "Manage Collections",
	);

	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: MANAGE COLLECTIONS";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_title"].": MANAGE COLLECTIONS";
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

				$PreloadBuffer = aib_generate_list_preload("aibcollections",$TotalListPages);

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
				$OutBuffer[] = "<div name='aibselectarchive-div' id='aibselectarchive-div' class='aib-selectarchive-div'>";
				$OutBuffer[] = "<br><br>";
				$OutBuffer[] = "Current Archive: <select name='aibcollections-key' id='aibcollections-key' class='aib-selectarchive-dropdown'>";
				if (aib_open_db() != false)
				{
					$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
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
					$OutBuffer[] = "<option value='".$ArchiveRecord["id"]."'>".$ArchiveRecord["title"]."</option>";
				}

				$OutBuffer[] = "</select>";
				$OutBuffer[] = "</div>";

			}
			else
			{
				$OutBuffer[] = "<input type='hidden' name='aibcollections-key' value='$ParentItem'>";
			}

			// Generate list table.  First, create div to hold table data.

			$OutBuffer[] = "<div name='aibcollections' id='aibcollections-data-div' class='aib-generic-list-div'>";

			// Generate frame of the list without data.  This also sets up bindings for button events, etc..
			// The content will be generated by an AJAX request which generates the lines using the
			// aib_generate_generic_list_inner_html() function.

			$ListParam = array();
			$ListParam["columns"] = array(
				"item_title" => "Collection Name",
				".op" => "",
				);
			$ListParam["searchable"] = array(
				"item_title" => "Collection Name",
				);
			$ListParam["pagecount"] = 1;
			$ListParam["pagenum"] = 1;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$OutBuffer[] = aib_generate_generic_list_frame_html("aibcollections",$ListParam);


			// End of table data area

			$OutBuffer[] = "</div>";

			// Generate script and any html required to make table interactive.

			$Spec = array(
				"opcode" => "lca",
				"rows" => AIB_DEFAULT_ITEMS_PER_PAGE,
				"total_pages" => $TotalListPages,
				"url" => "/services/air.php",
				"key" => $ParentItem,
				);

			// Set up trigger so that when a new selection is made for an archive, the list is redisplayed

			if ($UserType == FTREE_USER_TYPE_ROOT)
			{
				$Spec["extra_init_code"] = "
					\$('#aibcollections-key').change(function(Event) {
						listquery_aibcollections();
					});
				";
			}

			$ListData = aib_generate_scroll_table_handler(AIB_SUPERUSER,"aibcollections",$Spec);
			$FooterScriptLines[] = $ListData["script"];
			$OutBuffer[] = "</div>";
			break;
	}

	$DisplayData["content"] = join("\n",$OutBuffer);
	$DisplayData["footer_lines"] = join("\n",$FooterScriptLines);
include('template/common_list.php');
	exit(0);
?>
