<?php
//
// login.php
//

// FUNCTIONAL INCLUDES

include('config/aib.php');
include('include/folder_tree.php');
include('include/aib_util.php');

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

	// Get opcode, if any

	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array

	$DropDownMenuData = array(
		"Owner" => array(
			"My Account" => array("link" => "/myaccount.php", "help" => false),
			"Manage Collections" => array("link" => "#", "help" => false),
			"Logout" => array("link" => "/login.php", "help" => false),
			),
		"Assistants" => array(
			"Add New Assistant" => array("link" => "#", "help" => false),
			"Manage Assistants" => array("link" => "#", "help" => false),
			),
		"My Archive" => array(
			"Upload / Manage Editions" => array("link" => "#", "help" => false),
			"Content Removal Requests" => array("link" => "#", "help" => false),
			"Contact Requests" => array("link" => "#", "help" => false),
		),
		"Revenue" => array(
			"Display Ads" => array("link" => "#", "help" => false),
			"Reprint Requests" => array("link" => "#", "help" => false),
		),
	);

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: MANAGE ADMINISTRATORS",
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
		"menu" => $DropDownMenuData,
		"current_menu" => false,
	);

	$PageTimeout = AIB_SESSION_TIMEOUT;
	$PageTimeout = $PageTimeout * 1000;
	$DisplayData["head_script"] = "
		setTimeout(function() {
			window.location.href='/login.php';
		},$PageTimeout);
		";

	// If this is a root user, add some items to the menus

	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
		$DisplayData["menu"]["Assistants"]["Add Administrator"] = array("link" => "#", "help" => false);
		$DisplayData["menu"]["Assistants"]["Manage Administrators"] = array("link" => "#", "help" => false);
		$DisplayData["menu"]["Assistants"]["Add Group"] = array("link" => "#", "help" => false);
		$DisplayData["menu"]["Assistants"]["Manage Groups"] = array("link" => "#", "help" => false);
		$DisplayData["menu"]["My Archive"]["Add Archive"] = array("link" => "/admin_addarchive.php", "help" => false);
		$DisplayData["menu"]["My Archive"]["Manage Archives"] = array("link" => "#", "help" => false);
		$DisplayData["current_menu"] = "Super Admin: Manage Administrators";
	}

	// Set the current menu item based on the opcode

	switch($OpCode)
	{
		case "myaccount":
			$DisplayData["current_menu"] = "My Account";
			break;

		default:
			break;
	}

	// Include the page header, which also contains the drop-down menu

	include('template/common_header.php');

?>
	<tr>
			<!-- CONTENT GOES HERE -->
	</tr>

<?php
	// Include the footer

include('template/common_footer.php');
include('template/common_end_of_page.php');

	exit(0);
