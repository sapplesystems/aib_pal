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


	$UserRecord = $UserInfo[1];

	// Get form data

	$FormData = aib_get_form_data();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Get nav info, if any

	aib_get_nav_info($FormData);

	// Set up display data array based on the user type

	switch($UserRecord["user_type"])
	{
		case FTREE_USER_TYPE_ROOT:
			$DisplayData = array(
				"page_title" => "SYSTEM ADMINISTRATOR: MY ACCOUNT",
				"popup_list" => array(
					"user_title_help_popup" => array("title" => "Help For: Full Name",
									"heading" => "Help For: Full Name",
									"text" => "Enter your full name or title.",
								),
					"user_password_help_popup" => array("title" => "Help For: New Password",
									"heading" => "Help For: New Password",
									"text" => "To change your password, enter a new password in the 'Password' field, and repeat that password in the 'Password Confirm' field.",
								),
					"user_password_confirm_help_popup" => array("title" => "Help For: New Password Confirm",
									"heading" => "Help For: New Password Confirm",
									"text" => "Enter the same password here as in the 'Password' field to confirm that the new password is correct.",
								),
				),
			);

			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
			$DisplayData = array(
				"page_title" => urldecode($UserRecord["user_title"])." (".urldecode($UserRecord["user_login"])."): MY ACCOUNT",
				"popup_list" => array(
					"user_title_help_popup" => array("title" => "Help For: Full Name",
									"heading" => "Help For: Full Name",
									"text" => "Enter your full name or title.",
								),
					"user_password_help_popup" => array("title" => "Help For: New Password",
									"heading" => "Help For: New Password",
									"text" => "To change your password, enter a new password in the 'Password' field, and repeat that password in the 'Password Confirm' field.",
								),
					"user_password_confirm_help_popup" => array("title" => "Help For: New Password Confirm",
									"heading" => "Help For: New Password Confirm",
									"text" => "Enter the same password here as in the 'Password' field to confirm that the new password is correct.",
								),
				),
			);

			break;

		case FTREE_USER_TYPE_STANDARD:
		default:
			break;
	}

	$DisplayData["head_script"] = "

		// Prevent the use of the 'back' button to prevent mangling
		// forms.

		history.pushState(null, null, document.URL);
		window.addEventListener('popstate', function () {
		    alert(\"Please use the links on the page instead of the 'Back' button.\");
		    history.pushState(null, null, document.URL);
		});


";

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
		$DisplayData["current_menu"] = "My Account";
	}



	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"login_id" => array(
			"title" => "Login:", "type" => "readonly", "display_width" => "25",
			"field_name" => "user_login", "field_id" => "user_login",
			"desc" => ""),
		"login_title" => array(
			"title" => "Full Name:", "type" => "text", "display_width" => "40",
			"field_name" => "user_title", "field_id" => "user_title",
			"desc" => "Full name, title or position",
			"help_function_name" => "user_title_help_popup"),
		"login_password" => array(
			"title" => "New Password:", "type" => "password", "display_width" => "25",
			"field_name" => "user_password", "field_id" => "user_password",
			"desc" => "Enter a new password to change your current password",
			"help_function_name" => "user_password_help_popup"),
		"login_password_confirm" => array(
			"title" => "New Password Confirm:", "type" => "password", "display_width" => "25",
			"field_name" => "user_password_confirm", "field_id" => "user_password_confirm",
			"help_function_name" => "user_password_confirm_help_popup"),
	);

	// Define field validations

	$ValidationDef = array(
		"login_title" => array("type" => "text", "field_id" => "login_title",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter a title"),
					),
				),
		"login_password" => array("type" => "text", "field_id" => "login_password",
			"conditions" => array(
				"notblank" => array("error_message" => "You must enter a password"),
				"matchesotherfield" => array("target" => "login_password_confirm",
							"error_message" => "Passwords do not match"),
					),
				),
		"login_password_confirm" => array("type" => "text", "field_id" => "login_password_confirm",
			"conditions" => array(
				"notblank" => array("error_message" => "You must enter a password"),
				"matchesotherfield" => array("target" => "login_password",
							"error_message" => "Passwords do not match"),
					),
				),
		);

	// Field area.  First, show navigation link back to source if needed

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

	$OutBuffer[] = " <tr> <td align='left' valign='top'>";
			$ErrorMessage = false;
			$StatusMessage = false;

			switch($OpCode)
			{
				// Save edited item and return to list

				case "save_edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
					}

					// Check to see if anything was actually updated.  If not, skip updating.

					$UpdateFlag = false;
					$NewTitle = ltrim(rtrim(aib_get_with_default($FormData,"user_title","")));
					$NewPass = ltrim(rtrim(aib_get_with_default($FormData,"user_password","")));
					$NewPassConfirm = ltrim(rtrim(aib_get_with_default($FormData,"user_password_confirm","")));

					// Get primary key; fail if not present.

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					// Get profile

					$UserRecord = ftree_get_user($GLOBALS["aib_db"],$Primary);
					if ($UserRecord == false)
					{
						aib_close_db();
						$ErrorMessage = "Cannot retrieve user profile";
						break;
					}

					$UpdateArray = array();
					if ($NewTitle != $UserRecord["user_title"])
					{
						$UpdateFlag = true;
						$UpdateArray["name"] = $NewTitle;
					}

					if ($NewPass != "" && $NewPassConfirm != "")
					{
						$UpdateFlag = true;
						$UpdateArray["password"] = $NewPass;
					}

					$Result = true;
					if ($UpdateFlag != false)
					{
						$Result = ftree_update_user($GLOBALS["aib_db"],$Primary,$UpdateArray);
					}

					aib_close_db();
					if ($Result == false)
					{
						$ErrorMessage = "Cannot update user profile";
					}
					else
					{
						$StatusMessage = "Account settings successfully updated";
					}

					break;


				// Edit mode

				case "edit":
				default:
					$FieldDef["login_id"]["value"] = aib_get_with_default($UserRecord,"user_login","");
					$FieldDef["login_title"]["value"] = aib_get_with_default($UserRecord,"user_title","");
					$OutBuffer[] = aib_gen_form_header("pageform","/myaccount.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$UserRecord["user_id"]."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save_edit'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"".aib_get_nav_string()."\">";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

			}

			// Show status or error message

			if ($ErrorMessage != false || $StatusMessage != false)
			{
				$OutBuffer[] = "<table class='aib-form-result-message-table'>";
				$OutBuffer[] = "<tr class='aib-form-result-message-header-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-header-cell'>";
				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "<tr class='aib-form-result-message-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-cell'>";
				if ($ErrorMessage != false)
				{
					$OutBuffer[] = "<span class='aib-form-result-message-error-span'>";
					$OutBuffer[] = $ErrorMessage;
					$OutBuffer[] = "</span>";
				}
				else
				{
					$OutBuffer[] = "<span class='aib-form-result-message-status-span'>";
					$OutBuffer[] = $StatusMessage;
					$OutBuffer[] = "</span>";
				}

				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "<tr class='aib-form-result-message-footer-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-footer-cell'>";
				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "</table>";
			}

			// Output form or chain depending on operation

			$NavString = aib_get_nav_string();
			$TargetSpec = array();
			$NavTargetInfo = aib_get_nav_target();
			if ($NavTargetInfo != false)
			{
				if ($NavTargetInfo["target"] != "admin_main.php")
				{
					$TargetSpec[] = array(
						"url" => "/".$NavTargetInfo["target"]."?aibnav=$NavString",
						"title" => $NavTargetInfo["title"],
						"fields" => array(),
						);
				}
			}

			switch($OpCode)
			{
				case "save_edit":
					if ($ErrorMessage != false)
					{
						break;
					}

					$TargetSpec[] = array(
						"url" => "/admin_main.php",
						"title" => "Return To Main Page",
						"fields" => array(
							"aibnav" => $NavString,
							),
						);

					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["login_id"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["login_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["login_password"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["login_password_confirm"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";
					break;
			}

			print(join("\n",$OutBuffer));
			?>
		</td>
	</tr>

<?php
	// Include the footer

include('template/common_footer.php');

	// Generate validation functions

	print(aib_gen_field_validations("pageform","validate_form",$ValidationDef));

	// Other scripts
?>

<?php

include('template/common_end_of_page.php');
	exit(0);
?>
