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

	// Check session.  If none present, create one.

	aib_init_session("_public");
	$ErrorMessage = false;
	$CheckResult = aib_check_session();
	$UserID = -1;
	$UserRecord = array("user_id" => -1, "user_title" => "Guest", "user_login" => "_public", "user_type" => AIB_USER_TYPE_PUBLIC);
	$UserType = AIB_USER_TYPE_PUBLIC;
	if ($CheckResult[0] == "OK")
	{
		// If there is a user, get the profile.  Otherwise, "__public" is the profile.

		$SessionInfo = $CheckResult[1];
		if (isset($SessionInfo['login']) != false)
		{
			$UserLogin = $SessionInfo["login"];
			if ($UserLogin == "_public")
			{
				$UserID = -1;
				$UserType = AIB_USER_TYPE_PUBLIC;
			}
			else
			{
				$UserInfo = aib_get_user_info($SessionInfo["login"]);
				if ($UserInfo[0] == "OK")
				{
					$UserID = $UserInfo[1]["user_id"];
					$UserType = $UserInfo[1]["user_type"];
					$UserLogin = $UserInfo[1]["user_login"];
				}
			}
		}
	}

	// Get form data

	$FormData = aib_get_form_data();

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
			window.location.href='/login.php';
		},$PageTimeout);
		";

	// Include menu data

	switch($UserRecord["user_type"])
	{
		case FTREE_USER_TYPE_ROOT:
			include("template/top_menu_data.php");
			include("template/top_menu_admin_data.php");
			$DisplayData["page_title"] = "SUPER USER: SEARCH";
			break;

		case FTREE_USER_TYPE_ADMIN:
			include("template/top_menu_data.php");
			$DisplayData["page_title"] = "ADMINISTRATOR: SEARCH";
			break;

		case FTREE_USER_TYPE_SUBADMIN:
			include("template/top_menu_subadmin_data.php");
			$DisplayData["page_title"] = urldecode($UserRecord["user_title"])." (".$UserRecord["user_login"]."): SEARCH";
			break;

		default:
			$DisplayData["page_title"] = "SEARCH";
			break;
	}

	$OutLines = array();

	$OutLines[] = "<div class='search-select-archive-message'>";
	$OutLines[] = "<h1>Please Select An Archive</h1>";
	$OutLines[] = "</div>";

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
