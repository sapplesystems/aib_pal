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

	// Make sure this is an admin

	$UserRecord = $UserInfo[1];
	switch($UserRecord["user_type"])
	{
		case FTREE_USER_TYPE_ROOT:
			break;

		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	aib_update_nav_info("src","admin_archives.php");
	aib_update_nav_info("src_title","Archive Management");
	$NavString = aib_get_nav_string();
	$NavTargetInfo = aib_get_nav_target();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: MANAGE ARCHIVES",
		"popup_list" => array(),
	);

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
		$DisplayData["current_menu"] = "Super Admin: Manage Archives";
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
				$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
				$ArchiveList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchivesFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER);
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

				$PreloadBuffer = aib_generate_list_preload("aibarchives",$TotalListPages);

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

	// Include the page header

	$PageTimeout = AIB_SESSION_TIMEOUT;
	$PageTimeout = $PageTimeout * 1000;
	if (isset($DisplayData["head_script"]) == false)
	{
		$DisplayData["head_script"] = "
			setTimeout(function() {
				window.location.href='/login.php';
			},$PageTimeout);
			";
	}
	else
	{
		$DisplayData["head_script"] .= "
			setTimeout(function() {
				window.location.href='/login.php';
			},$PageTimeout);
			";
	}




include('template/common_header.php');

	// Set up footer script information

	$FooterScriptLines = array();

?>
	<tr>
		<td align='left' valign='top'>
			<?php
			$OutBuffer = array();
			$StatusMessage = false;
			$ErrorMessage = false;

			// Process based on opcode

			switch($OpCode)
			{
				case "list":
				case false:
				default:

					$ArchiveCode = aib_get_with_default($FormData,"primary",false);

					// Generate list table.  First, create div to hold table data.

					$OutBuffer[] = "<div name='aibselectarchivegroup-div' id='aibselectarchivegroup-div' class='aib-selectarchive-div'>";
					$OutBuffer[] = "<br><br>";
					$OutBuffer[] = "Select Archive Group: <select name='aibarchives-key' id='aibarchives-key' class='aib-selectarchive-dropdown'>";
					if (aib_open_db() != false)
					{
						$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
						$ArchiveList = array(array('id' => "NULL", "title" => " -- SELECT -- "));
						$TempList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchivesFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER);
						foreach($TempList as $TempRecord)
						{
							$Code = ftree_get_property($GLOBALS["aib_db"],$TempRecord["item_id"],AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
							if ($Code == false)
							{
								continue;
							}

							$ArchiveList[] = array("id" => $TempRecord["item_id"], "title" => $TempRecord["item_title"]." -- $Code");
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
					$OutBuffer[] = "<div name='aibarchives' id='aibarchives-data-div' style='aib-generic-list-div'>";

					// Generate frame of the list without data.  This also sets up bindings for button events, etc..
					// The content will be generated by an AJAX request which generates the lines using the
					// aib_generate_generic_list_inner_html() function.

					$ListParam = array();
					$ListParam["columns"] = array(
						"item_title" => "Archive Code",
						".archive_title" => "Archive Name",
						".op" => "",
						);
					$ListParam["searchable"] = array(
						"item_title" => "Archive Code",
						".archive_title" => "Archive Title",
						);
					$ListParam["pagecount"] = 1;
					$ListParam["pagenum"] = 1;
					$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
					$OutBuffer[] = aib_generate_generic_list_frame_html("aibarchives",$ListParam);


					// End of table data area

					$OutBuffer[] = "</div>";

					// Generate script and any html required to make table interactive.

					$Spec = array(
						"opcode" => "lar",
						"rows" => AIB_DEFAULT_ITEMS_PER_PAGE,
						"total_pages" => $TotalListPages,
						"url" => "/services/air.php",
						"aibnav" => $NavString,
						);

					// Set up trigger which will cause the list to reload when a different
					// archive group is selected

					$Spec["extra_init_code"] = "
						\$('#aibarchives-key').change(function(Event) {
							listquery_aibarchives();
						});
					";

					$ListData = aib_generate_scroll_table_handler(AIB_SUPERUSER,"aibarchives",$Spec);
					$FooterScriptLines[] = $ListData["script"];
					break;
			}

			print(join("\n",$OutBuffer));
			?>
		</td>
	</tr>

<?php
	// Include the footer

include('template/common_footer.php');

	// Add any footer script

	print(join("\n",$FooterScriptLines));
?>

<?php

include('template/common_end_of_page.php');
	exit(0);
?>
