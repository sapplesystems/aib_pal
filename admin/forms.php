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
	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	aib_update_nav_info("src","forms.php");
	aib_update_nav_info("src_title","Form Management");
	$NavString = aib_get_nav_string();
	$NavTargetInfo = aib_get_nav_target();

	// Get folder and screen from which the user navigated

	$UserType = $UserRecord["user_type"];
	$UserID = $UserRecord["user_id"];
	$ParentItem = aib_get_with_default($FormData,"parent",false);
	if ($ParentItem === false)
	{
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
	}

	$PrimaryKey = aib_get_with_default($FormData,"primary",false);
	$AIBKey = aib_get_with_default($FormData,"aibforms-key",false);
	$OpCode = aib_get_with_default($FormData,"opcode","add");
	$FormOwnerType = aib_get_with_default($FormData,"form_owner_type",FTREE_OWNER_TYPE_SYSTEM);
	$FormOwnerID = aib_get_with_default($FormData,"form_owner","-1");

	// Set up display data array default values

	$DisplayData = array(
		"popup_list" => array(),
		"current_menu" => "Manage Forms",
	);

	$DisplayData["head_script"] = "

		// Prevent the use of the 'back' button to prevent mangling
		// forms.

		history.pushState(null, null, document.URL);
		window.addEventListener('popstate', function () {
		    alert(\"Please use the links on the page instead of the 'Back' button.\");
		    history.pushState(null, null, document.URL);
		});


";

	// Set up prerequisites based on type of user

	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: MANAGE FIELDS";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_STANDARD:
		default:
			$DisplayData["page_title"] = $UserRecord["user_title"].": MANAGE FIELDS";
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
				$FieldList = ftree_list_fields($GLOBALS["aib_db"],$UserID);
				if ($FieldList != false)
				{
					$TotalListPages = intval((count($FieldList) / intval(AIB_DEFAULT_ITEMS_PER_PAGE)) + 1);
				}
				else
				{
					$TotalListPages = 0;
				}

				aib_close_db();

				// Generate pre-load functions for HTML page header

				$PreloadBuffer = aib_generate_list_preload("aibforms",$TotalListPages);

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
			// Generate list table.  First, create div to hold table data.

			$OutBuffer[] = "<div name='aibforms' id='aibforms-data-div' class='aib-generic-list-div'>";

			// Set parent folder field as a hidden field usable by the list processing code

			$OutBuffer[] = "<input type='hidden' name='aibforms-key' id='aibforms-key' value='$AIBKey'>";
			$OutBuffer[] = "<input type='hidden' name='primary' id='primary' value='$PrimaryKey'>";

			// Generate frame of the list without data.  This also sets up bindings for button events, etc..
			// The content will be generated by an AJAX request which generates the lines using the
			// aib_generate_generic_list_inner_html() function.

			$ListParam = array();
			$ListParam["columns"] = array(
				"form_title" => "Name",
				".ownedby" => "Owned By",
				".op" => "",
				);
			$ListParam["col_width"] = array(
				"form_title" => "50%",
				".ownedby" => "30%",
				".op" => "10%",
				);
			$ListParam["searchable"] = array(
				"form_title" => "Name",
				);
			$ListParam["pagecount"] = 1;
			$ListParam["pagenum"] = 1;
			$ListParam["pagesize"] = AIB_DEFAULT_TREE_ITEMS_PER_PAGE;
			$ListParam["extra_title_rows"] = array();
			aib_open_db();

			// Set up return path and "go up" link

			$UserType = $UserRecord["user_type"];
			$UserGroup = $UserRecord["user_primary_group"];

			$ListParam["extra_title_rows"][] = "<tr class='aib-records-list-extra-title-sep-row'><td class='aib-records-list-extra-title-sep-cell' colspan='99'> </td></tr>";

			// Show "add field" link

			$LocalSourceKey = aib_get_with_default($FormData,"srckey","");
			$LocalSearchValue = aib_get_with_default($FormData,"searchval","");
			$LocalSourceMode = aib_get_with_default($FormData,"srcmode","");
			$LocalSourcePageNumber = aib_get_with_default($FormData,"srcpn","");
			$TargetFields = join("&",array("src=fields","srckey=$LocalSourceKey","searchval=$LocalSearchValue","srcmode=$LocalSourceMode","srcpn=$LocalSourcePageNumber"));
			$TitleRow = "<tr class='aib-folder-type-row'>";
			$TitleCell = "<td class='aib-folder-type-cell'>";
			$MenuCell = "<td class='aib-folder-menu-cell'>";
			$MenuCell .= "<a href='/form_form.php?opcode=add&$TargetFields' class='aib-folder-menu-link'>Create Form</a>";
			$TitleCell .= "</td>";
			$TitleRow .= $TitleCell;
			$MenuCell .= "</td>";
			$TitleRow .= $MenuCell;
			$TitleRow .= "</td></tr>";
			$ListParam["extra_title_rows"][] = $TitleRow;

			// Generate list frame

			$OutBuffer[] = aib_generate_generic_list_frame_html("aibforms",$ListParam);
			aib_close_db();


			// End of table data area

			$OutBuffer[] = "</div>";

			// Generate script and any html required to make table interactive.

			$Spec = array(
				"opcode" => "lod",
				"rows" => AIB_DEFAULT_TREE_ITEMS_PER_PAGE,
				"total_pages" => $TotalListPages,
				"url" => "/services/airrecord.php",
				"key" => "",
				"lop" => "list",
				"aibnav" => $NavString,
				);

			// Set up trigger so that when a new selection is made for an archive, the list is redisplayed

			if ($UserType == FTREE_USER_TYPE_ROOT)
			{
				$Spec["extra_init_code"] = "
					\$('#aibforms-key').change(function(Event) {
						listquery_aibforms();
					});
				";
			}

			$ListData = aib_generate_scroll_table_handler($UserID,"aibforms",$Spec);
			$FooterScriptLines[] = $ListData["script"];
			$OutBuffer[] = "</div>";
			break;
	}

	$DisplayData["content"] = join("\n",$OutBuffer);

	// Add any footer script

	$DisplayData["footer_lines"] = join("\n",$FooterScriptLines);

include('template/common_list.php');
	exit(0);
?>
