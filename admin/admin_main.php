<?php
//
// login.php
//

// ===================
// FUNCTIONAL INCLUDES
// ===================

include('config/aib.php');
include('include/folder_tree.php');
include('include/aib_util.php');

// =========
// FUNCTIONS
// =========

// Generate a list of waiting records for a subadmin
// -------------------------------------------------
function generate_subadmin_waiting_records($InSpec)
{
	$UserID = $InSpec["user_id"];
	$UserType = $InSpec["user_type"];
	aib_open_db();
	$WaitingMap = array();
	$CompletedMap = array();
	$WaitingDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,true);

	// Get a list of all subgroups where user has completed all data entry

	$CompletedDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,false);
	if ($WaitingDataEntry != false)
	{
		foreach($WaitingDataEntry as $Record)
		{
			$ItemID = $Record["item_id"];
			$ItemParent = $Record["item_parent_id"];
			if (isset($WaitingMap[$ItemParent]) == false)
			{
				$WaitingMap[$ItemParent] = 1;
			}
			else
			{
				$WaitingMap[$ItemParent]++;
			}

		}
	}

	if ($CompletedDataEntry != false)
	{
		foreach($CompletedDataEntry as $Record)
		{
			$ItemID = $Record["item_id"];
			$ItemParent = $Record["item_parent_id"];
			if (isset($CompletedMap[$ItemParent]) == false)
			{
				$CompletedMap[$ItemParent] = 1;
			}
			else
			{
				$CompletedMap[$ItemParent]++;
			}
		}
	}

	// Show a list of waiting items.  Each entry is a link which starts data entry.

	$OutLines[] = "<div class='assistant-item-list-container'>";
	$OutLines[] = "<span class='assistant-item-list-container-title'>Records To Be Processed</span><br><br>";
	$OutLines[] = "<table class='assistant-waiting-table' id='assistant_waiting_table'>";
	$OutLines[] = "<thead class='assistant-waiting-table-head'>";
	$OutLines[] = "<tr class='assistant-waiting-table-head-row'>";
	$OutLines[] = "<th class='assistant-waiting-table-head-cell'>Archive</th>";
	$OutLines[] = "<th class='assistant-waiting-table-head-cell'>Sub-Group</th>";
	$OutLines[] = "<th class='assistant-waiting-table-head-cell'>Records To Do</th>";
	$OutLines[] = "</tr>";
	$OutLines[] = "</thead>";
	$OutLines[] = "<body class='assistant-waiting-table-body'>";
	foreach($WaitingMap as $ItemParent => $ItemCount)
	{
		$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$ItemParent);
		$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ItemParent);
		$OutLines[] = "<tr class='assistant-waiting-table-row'>";
		$OutLines[] = "<td class='assistant-waiting-table-archive-cell'>";
		$OutLines[] = urldecode($ArchiveInfo["archive"]["item_title"]);
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-waiting-table-subgroup-cell'>";
		$Title = "<a class='assistant-waiting-table-subgroup-link' href='/edit_record_form.php?opcode=start_sub&src=main&primary=$ItemParent'>";
		$Title .= urldecode($ItemRecord["item_title"])."</a>";
		$OutLines[] = $Title;
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-waiting-table-count-cell'>";
		$OutLines[] = number_format($ItemCount);
		$OutLines[] = "</td>";
		$OutLines[] = "</tr>";
	}

	if (count(array_keys($WaitingMap)) < 1)
	{
		$OutLines[] = "<tr class='assistant-waiting-table-row'>";
		$OutLines[] = "<td class='assistant-waiting-table-archive-cell'>";
		$OutLines[] = " --- ";
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-waiting-table-subgroup-cell'>";
		$OutLines[] = " --- ";
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-waiting-table-count-cell'>";
		$OutLines[] = " --- ";
		$OutLines[] = "</td>";
		$OutLines[] = "</tr>";
	}

	$OutLines[] = "</table>";
	$OutLines[] = "</div>";

	$OutLines[] = "<br>";

	// Show a list of completed items.  Each entry is a link which starts data entry.

	$OutLines[] = "<div class='assistant-item-list-container'>";
	$OutLines[] = "<span class='assistant-item-list-container-title'>Completed Records</span><br><br>";
	$OutLines[] = "<table class='assistant-completed-table' id='assistant_completed_table'>";
	$OutLines[] = "<thead class='assistant-completed-table-head'>";
	$OutLines[] = "<tr class='assistant-completed-table-head-row'>";
	$OutLines[] = "<th class='assistant-completed-table-head-cell'>Archive</th>";
	$OutLines[] = "<th class='assistant-completed-table-head-cell'>Sub-Group</th>";
	$OutLines[] = "<th class='assistant-completed-table-head-cell'>Records Completed</th>";
	$OutLines[] = "</tr>";
	$OutLines[] = "</thead>";
	$OutLines[] = "<body class='assistant-completed-table-body'>";
	foreach($CompletedMap as $ItemParent => $ItemCount)
	{
		$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$ItemParent);
		$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ItemParent);
		$OutLines[] = "<tr class='assistant-completed-table-row'>";
		$OutLines[] = "<td class='assistant-completed-table-archive-cell'>";
		$OutLines[] = urldecode($ArchiveInfo["archive"]["item_title"]);
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-completed-table-subgroup-cell'>";
		$OutLines[] = urldecode($ItemRecord["item_title"]);
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-completed-table-count-cell'>";
		$OutLines[] = number_format($ItemCount);
		$OutLines[] = "</td>";
		$OutLines[] = "</tr>";
	}

	if (count(array_keys($CompletedMap)) < 1)
	{
		$OutLines[] = "<tr class='assistant-completed-table-row'>";
		$OutLines[] = "<td class='assistant-completed-table-archive-cell'>";
		$OutLines[] = " --- ";
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-completed-table-subgroup-cell'>";
		$OutLines[] = " --- ";
		$OutLines[] = "</td>";
		$OutLines[] = "<td class='assistant-completed-table-count-cell'>";
		$OutLines[] = " --- ";
		$OutLines[] = "</td>";
		$OutLines[] = "</tr>";
	}


	$OutLines[] = "</table>";
	$OutLines[] = "</div>";
	return(join("\n",$OutLines));
}

// Get a list of all subadmins.  Output is an associative array where
// the key is the user ID, and the value is an associative array:
//
//	"def"		User record
//	"archives"	List of archives used, where each entry is an archive tree record
// ---------------------------------------------------------------------------------------
function get_subadmin_list($UserID)
{
	// Get the profile for this user

	$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);

	// Get the user's root folder

	$UserRoot = $UserRecord["user_top_folder"];

	// Get the folder type

	$SubadminList = array();
	$UserRootType = ftree_get_property($GLOBALS["aib_db"],$UserRoot,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	switch($UserRootType)
	{
		// If group, get the list of archives and then get the list of users for each archive

		case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$UserRoot);
			if ($ArchiveList == false)
			{
				$ArchiveList = array();
			}

			$UserMap = array();
			foreach($ArchiveList as $ArchiveInfo)
			{
				$ArchiveID = $ArchiveInfo["item_id"];
				$UserList = ftree_list_users_for_parent($GLOBALS["aib_db"],$ArchiveID,FTREE_USER_TYPE_SUBADMIN);
				if ($UserList != false)
				{
					foreach($UserList as $UserRecord)
					{
						if (isset($UserMap[$UserRecord["user_id"]]) == false)
						{
							$UserMap[$UserRecord["user_id"]] = array("def" => $UserRecord, "archives" => array($ArchiveInfo));
						}
						else
						{
							$UserMap[$UserRecord["user_id"]]["archives"][] = $ArchiveInfo;
						}
					}
				}
			}

			return($UserMap);

		case AIB_ITEM_TYPE_ARCHIVE:
			$UserMap = array();
			$UserList = ftree_list_users_for_parent($GLOBALS["aib_db"],$UserRoot,FTREE_USER_TYPE_SUBADMIN);
			$UserList = ftree_list_users_for_parent($GLOBALS["aib_db"],$ArchiveID,FTREE_USER_TYPE_SUBADMIN);
			if ($UserList != false)
			{
				foreach($UserList as $UserRecord)
				{
					if (isset($UserMap[$UserRecord["user_id"]]) == false)
					{
						$UserMap[$UserRecord["user_id"]] = array("def" => $UserRecord, "archives" => array($ArchiveInfo));
					}
					else
					{
						$UserMap[$UserRecord["user_id"]]["archives"][] = $ArchiveInfo;
					}
				}
			}

			return($UserMap);

		default:
			break;
	}

	return(array());
}


// For a subadmin, get the list of waiting and completed records
// -------------------------------------------------------------
function get_subadmin_status($UserID)
{
	$WaitingDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,true);
	$CompletedDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,false);
	$OutData = array("waiting" => $WaitingDataEntry, "completed" => $CompletedDataEntry);
	return($OutData);
}


// Generate a list of subadmins and assignment statuses for admin
// --------------------------------------------------------------
function generate_admin_subadmin_status($InSpec)
{
	$UserID = $InSpec["user_id"];
	$UserType = $InSpec["user_type"];
	$UserTopFolder = $InSpec["user_top_folder"];
	$OutLines = array();
	$OutLines[] = "<div class='assistant-status-list-container'>";
	$OutLines[] = "<span class='assistant-status-list-container-title'>Assignments Status</span><br><br>";
	$OutLines[] = "<table class='assistant-status-table' id='assistant_status'>";
	$OutLines[] = "<thead class='assistant-status-table-head'>";
	$OutLines[] = "<tr class='assistant-status-table-head-row'>";
	$OutLines[] = "<th class='assistant-status-table-head-cell'>Assistant</th>";
	$OutLines[] = "<th class='assistant-status-table-head-cell'>Archive Name</th>";
	$OutLines[] = "<th class='assistant-status-table-head-numeric-cell'>Waiting</th>";
	$OutLines[] = "<th class='assistant-status-table-head-numeric-cell'>Completed</th>";
	$OutLines[] = "</tr>";
	$OutLines[] = "</thead>";
	$OutLines[] = "<body class='assistant-status-table-body'>";
	$SubAdminList = get_subadmin_list($UserID);
	foreach($SubAdminList as $SubAdminID => $DefRecord)
	{
		$SubAdminRecord = $DefRecord["def"];
		$SubAdminArchives = $DefRecord["archives"];
		foreach($SubAdminArchives as $ArchiveRecord)
		{
			$SubAdminStatusInfo = get_subadmin_status($SubAdminID);
			$WaitingCount = count($SubAdminStatusInfo["waiting"]);
			if ($WaitingCount < 1)
			{
				$WaitingCount = " --- ";
			}

			$CompletedCount = count($SubAdminStatusInfo["completed"]);
			if ($CompletedCount < 1)
			{
				$CompletedCount = " --- ";
			}

			$AssistantName = urldecode($SubAdminRecord["user_title"]);
			$ArchiveName = urldecode($ArchiveRecord["item_title"]);
			$OutLines[] = "<tr class='assistant-status-table-row'>";
			$OutLines[] = "<td class='assistant-status-table-assistant-cell'>$AssistantName</td>";
			$OutLines[] = "<td class='assistant-status-table-archive-cell'>$ArchiveName</td>";
			$OutLines[] = "<td class='assistant-status-table-waiting-cell'>$WaitingCount</td>";
			$OutLines[] = "<td class='assistant-status-table-completed-cell'>$CompletedCount</td>";
			$OutLines[] = "</tr>";
		}
	}

	$OutLines[] = "</table>";
	$OutLines[] = "</div>";
	return(join("\n",$OutLines));
}

// #########
// MAIN CODE
// #########
	// Check session

	session_start();
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
	$UserType = $UserRecord["user_type"];
	$UserID = $UserRecord["user_id"];
	switch($UserRecord["user_type"])
	{
		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
		case FTREE_USER_TYPE_ROOT:
			break;

		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	// Get form data

	$FormData = aib_get_form_data();

	// Get nav info and set page source/title

	aib_get_nav_info($FormData);
	aib_update_nav_info("src","admin_main.php");
	aib_update_nav_info("src_title","Main Page");

	// Get opcode, if any

	$OpCode = aib_get_with_default($FormData,"opcode",false);

	$DisplayData = array(
		"page_title" => "OWNER DASHBOARD",
		"popup_list" => array(
			"login_help_popup" => array("title" => "Help For: Login",
							"heading" => "Help For: Login",
							"text" => "Enter your login ID in this field.",
						),
			"password_help_popup" => array("title" => "Help For: Password",
							"heading" => "Help For: Password",
							"text" => "Enter your password in this field.",
						),
		),
		"current_menu" => false,
	);

	$PageTimeout = AIB_SESSION_TIMEOUT;
	$PageTimeout = $PageTimeout * 1000;
	$DisplayData["head_script"] = "
		setTimeout(function() {
		alert(\"Please use the links on the page instead of the 'Back' button.\");
		window.location.href='/login.php';
		},$PageTimeout);
		";

	// Include menu data

	switch($UserRecord["user_type"])
	{
		case FTREE_USER_TYPE_ROOT:
			include("template/top_menu_data.php");
			include("template/top_menu_admin_data.php");
			$DisplayData["page_title"] = "SUPER USER DASHBOARD";
			break;

		case FTREE_USER_TYPE_ADMIN:
			include("template/top_menu_data.php");
			$DisplayData["page_title"] = "ADMINISTRATOR DASHBOARD";
			break;

		case FTREE_USER_TYPE_SUBADMIN:
			include("template/top_menu_subadmin_data.php");
			$DisplayData["page_title"] = "ASSISTANT DASHBOARD";
			break;

		default:
			$DisplayData["page_title"] = "UNKNOWN DASHBOARD";
			break;
	}


	// Set the current menu item based on the opcode

	$OutLines = array();
	switch($OpCode)
	{
		case "myaccount":
			$DisplayData["current_menu"] = "My Account";
			break;

		default:
			// Show data entry waiting if sub-admin

			if ($UserType == FTREE_USER_TYPE_SUBADMIN)
			{

				$GenerateSpec = array("user_id" => $UserID, "user_type" => $UserType, "opcode" => $OpCode);
				aib_open_db();
				$LocalBuffer = generate_subadmin_waiting_records($GenerateSpec);
				aib_close_db();
				$OutLines[] = $LocalBuffer;
				break;
			}

			if ($UserType == FTREE_USER_TYPE_ADMIN)
			{
				$GenerateSpec = array("user_id" => $UserID, "user_type" => $UserType, "opcode" => $OpCode);
				aib_open_db();
				$LocalBuffer = generate_admin_subadmin_status($GenerateSpec);
				aib_close_db();
				$OutLines[] = $LocalBuffer;
				break;
			}

			break;
	}

	// Include the page header, which also contains the drop-down menu

	include('template/common_header.php');

?>
	<tr>
		<td colspan='99'>
	<?php print(join("\n",$OutLines)); ?>
		</td>
	</tr>

<?php
	// Include the footer

include('template/common_footer.php');
include('template/common_end_of_page.php');

	exit(0);
