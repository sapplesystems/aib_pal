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

	// Get the user type

	$UserType = $UserInfo[1]["user_type"];

	// Get form data and opcode

	$FormData = aib_get_form_data();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Get navigation info

	aib_get_nav_info($FormData);

	// Get current archive based on user.  If this is the super-user, then no current archive

	$CurrentArchive = false;
	switch($UserType)
	{
		// Root user (superadmin) has no archive

		case FTREE_USER_TYPE_ROOT:
			$CurrentArchive = false;
			break;

		case FTREE_USER_TYPE_ADMIN:
			$CurrentArchive = aib_get_with_default($FormData,"archive_code",false);
			break;

		// Standard users, sub-admins can't add an admin

		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	// If there is no archive, get a list of the archives available.

	if ($CurrentArchive == false)
	{
		if (aib_open_db() != false)
		{
			$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
			$ArchiveList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchivesFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER);
			if ($ArchiveList != false)
			{
				$TotalListPages = (count($ArchiveList) / intval(AIB_DEFAULT_ITEMS_PER_PAGE)) + 1;
			}
			else
			{
				$TotalListPages = 0;
			}

			aib_close_db();
		}
		else
		{
			$ArchiveList = array();
		}
	}

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: ASSISTANTS",
		"popup_list" => array(
			"archive_code_help_popup" => array("title" => "Help For: Archive Code",
							"heading" => "Help For: Archive Code",
							"text" => "Select the archive to which the administrator will be assigned",
						),
			"admin_login_help_popup" => array("title" => "Help For: Assistant Login",
							"heading" => "Help For: Assistant Login",
							"text" => "Enter the login ID of the administrator",
						),
			"admin_name_help_popup" => array("title" => "Help For: Assistant Name",
							"heading" => "Help For: Assistant Name",
							"text" => "Enter the full name or title of the administrator being created",
						),
			"admin_pass_help_popup" => array("title" => "Help For: Assistant Password",
							"heading" => "Help For: Assistant Password",
							"text" => "Enter the password for the administrator",
						),
			"admin_pass_confirm_help_popup" => array("title" => "Help For: Assistant Confirm Password",
							"heading" => "Help For: Assistant Confirm Password",
							"text" => "Enter the new administrator password again to confirm",
						),
		),
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

	switch($OpCode)
	{
		case "edit":
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: EDIT ASSISTANT";
			break;

		case "del":
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: DELETE ASSISTANT";
			break;

		default:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: ADD ASSISTANT";
			break;
	}

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	$UserRecord = $UserInfo[1];
	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
		$DisplayData["current_menu"] = "Add Assistant";
	}


	$UserID = $UserRecord["user_id"];
	$UserType = $UserRecord["user_type"];
	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"archive_code" => array(
			"title" => "Archive:", "type" => "dropdown", "display_width" => "10",
			"field_name" => "archive_code", "field_id" => "archive_code",
			"desc" => "", "help_function_name" => "archive_code_help_popup"),
		"admin_login" => array(
			"title" => "Login:", "type" => "text", "display_width" => "25",
			"field_name" => "admin_login", "field_id" => "admin_login",
			"desc" => "", "help_function_name" => "admin_login_help_popup"),
		"admin_name" => array(
			"title" => "Name:", "type" => "text", "display_width" => "64",
			"field_name" => "admin_name", "field_id" => "admin_name",
			"desc" => "", "help_function_name" => "admin_name_help_popup"),
		"admin_pass" => array(
			"title" => "Password:", "type" => "password", "display_width" => "32",
			"field_name" => "admin_pass", "field_id" => "admin_pass",
			"desc" => "", "help_function_name" => "admin_pass_help_popup"),
		"admin_pass_confirm" => array(
			"title" => "Confirm Password:", "type" => "password", "display_width" => "32",
			"field_name" => "admin_pass_confirm", "field_id" => "admin_pass_confirm",
			"desc" => "", "help_function_name" => "admin_pass_confirm_help_popup"),
	);

	// Define field validations

	$CurrentEditRecord = array();
	$ValidationDef = false;
	switch($OpCode)
	{
		case "edit":
			$ValidationDef = array(
				"archive_code" => array("type" => "text", "field_id" => "archive_code",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter an archive code"),
							),
						),
				"admin_login" => array("type" => "text", "field_id" => "admin_login",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a login for the administrator"),
							),
						),
				"admin_name" => array("type" => "text", "field_id" => "admin_name",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a name or title for the administrator"),
							),
						),
				"admin_pass" => array("type" => "text", "field_id" => "admin_pass",
						"conditions" => array(
							"matchesotherfield" => array("target" => "admin_pass_confirm", "error_message" => "Passwords do not match"),
							),
						),
				"admin_pass_confirm" => array("type" => "text", "field_id" => "admin_pass_confirm",
						"conditions" => array(
							"matchesotherfield" => array("target" => "admin_pass", "error_message" => "Passwords do not match"),
							),
						),
				);

				break;

		case "add":
		default:
			$ValidationDef = array(
				"archive_code" => array("type" => "text", "field_id" => "archive_code",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter an archive code"),
							),
						),
				"admin_login" => array("type" => "text", "field_id" => "admin_login",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a login for the administrator"),
							),
						),
				"admin_name" => array("type" => "text", "field_id" => "admin_name",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a name or title for the administrator"),
							),
						),
				"admin_pass" => array("type" => "text", "field_id" => "admin_pass",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a password for the administrator"),
							"matchesotherfield" => array("target" => "admin_pass_confirm", "error_message" => "Passwords do not match"),
							),
						),
				"admin_pass_confirm" => array("type" => "text", "field_id" => "admin_pass_confirm",
						"conditions" => array(
							"notblank" => array("error_message" => "You must confirm the administrator password"),
							"matchesotherfield" => array("target" => "admin_pass", "error_message" => "Passwords do not match"),
							),
						),
				);

				break;
	}

	// Show navigation links for the previous operation

	$OutBuffer = array();
	$NavID = aib_get_nav_value("primary");
	$NavTargetInfo = aib_get_nav_target();
	if ($NavTargetInfo != false && preg_match("/save/",$OpCode) == false)
	{
		if ($NavTargetInfo["target"] != "assistants.php")
		{
			$URL = "/".$NavTargetInfo["target"]."?aibnav=".aib_get_nav_string();
			$UpTitle = "Return To ".$NavTargetInfo["title"];
			$OutBuffer[] = "<tr><td align='left' valign='top'>";
			$OutBuffer[] = "<div class='browse-uplink-div'><a href='$URL' class='browse-uplink-link'>$UpTitle</a></div><br>";
			$OutBuffer[] = "</td></tr>";
		}
	}

	// Field area

	$OutBuffer[] = "<tr><td align='left' valign='top'>";
			$ErrorMessage = false;
			$StatusMessage = false;

			switch($OpCode)
			{
				// Save new archive

				case "save":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
					}
	
					// Check for errors and save
	
					while($ErrorMessage == false)
					{
						// Check for the archive selected.  If not present, error.

						$ArchiveID = aib_get_with_default($FormData,"archive_code",false);
						if ($ArchiveID === false)
						{
							$ErrorMessage = "Missing archive code";
							break;
						}

						$ArchiveID = preg_replace("/[^0-9]/","",$ArchiveID);

						// Check for a duplicate login

						$LoginID = aib_get_with_default($FormData,"admin_login",false);
						$Name = aib_get_with_default($FormData,"admin_name",false);
						$Password = aib_get_with_default($FormData,"admin_pass",false);
						$PasswordConfirm = aib_get_with_default($FormData,"admin_pass_confirm",false);
						if ($Password != $PasswordConfirm)
						{
							$ErrorMessage = "Passwords do not match";
							break;
						}

						$TempRecord = ftree_get_user_id_from_login($GLOBALS["aib_db"],$LoginID);
						if ($TempRecord != false)
						{
							$ErrorMessage = "Login ID already in use";
							break;
						}

						// Get group title

						$ArchiveRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ArchiveID);
						if ($ArchiveRecord == false)
						{
							$ErrorMessage = "Cannot find archive";
							break;
						}

						// Get the default group for the archive.  If not present, create.

						$DefaultGroupName = $ArchiveRecord["item_title"]." Group";
						$GroupInfo = ftree_get_group_by_title($GLOBALS["aib_db"],$DefaultGroupName);
						if ($GroupInfo == false)
						{
							ftree_create_group($GLOBALS["aib_db"],-1,$DefaultGroupName,FTREE_GROUP_ADMIN);
							$GroupInfo = ftree_get_group_by_title($GLOBALS["aib_db"],$DefaultGroupName);
						}

						$GroupID = $GroupInfo[0]["group_id"];

						// Create user account with selected archive as the home folder

						$Result = ftree_create_user($GLOBALS["aib_db"],-1,FTREE_USER_TYPE_SUBADMIN,$LoginID,$Password,$Name,$GroupID,$ArchiveID);
						if ($Result[0] == "ERROR")
						{
							$ErrorMessage = "Cannot create user: ".$Result[1];
							break;
						}
	
						$StatusMessage = "Assistant \"<i>$Name</i>\" created successfully";
						break;
					}

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
					$FieldDef["archive_code"]["value"] = $ArchiveID;
					$FieldDef["archive_title"]["value"] = "";
					break;


				// Save edited item and return to list

				case "save_edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Check for errors and save

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					// Get user definition using primary

					$AdminRecord = ftree_get_user($GLOBALS["aib_db"],$Primary);
					if ($AdminRecord == false)
					{
						aib_close_db();
						$ErrorMessage = "User cannot be found";
						break;
					}

					// Update anything that has changed

					$Info = array();
					$NewID = aib_get_with_default($FormData,"admin_login",false);
					$NewPass = aib_get_with_default($FormData,"admin_pass",false);
					$NewPassConfirm = aib_get_with_default($FormData,"admin_pass_confirm",false);
					$NewName = aib_get_with_default($FormData,"admin_name");
					if ($NewID != $AdminRecord["user_login"])
					{
						$Info["login"] = $NewID;
					}

					if ($NewPass != false)
					{
						if ($NewPass != $NewPassConfirm)
						{
							aib_close_db();
							$ErrorMessage = "Password doesn't match";
							break;
						}

						if (ftree_encode_password($NewPass) != $AdminRecord["user_pass"])
						{
							$Info["password"] = $NewPass;
						}
					}

					if ($NewName != $AdminRecord["user_title"])
					{
						$Info["name"] = $NewName;
					}

					ftree_update_user($GLOBALS["aib_db"],$Primary,$Info);

					// All done

					aib_close_db();
					$StatusMessage = "Assistant account successfully updated";
					break;


				// Perform delete and return to list

				case "do_del":
					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					ftree_delete_user($GLOBALS["aib_db"],$Primary);
					$StatusMessage = "Assistant successfully deleted";
					aib_close_db();
					break;


				// Edit mode

				case "edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Set field values using primary to get administrative account

					$AdminRecord = ftree_get_user($GLOBALS["aib_db"],$FormData["primary"]);
					if ($AdminRecord == false)
					{
						$ErrorMessage = "Cannot retrieve administrator account information";
						aib_close_db();
						break;
					}

					$CurrentEditRecord = $AdminRecord;
					$ArchiveID = $AdminRecord["user_top_folder"];
					$FieldDef["archive_code"]["value"] = $ArchiveID;
					$FieldDef["admin_login"]["value"] = $AdminRecord["user_login"];
					$FieldDef["admin_name"]["value"] = $AdminRecord["user_title"];
					$OutBuffer[] = aib_gen_form_header("pageform","/assistant_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"".aib_get_nav_string()."\">";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save_edit'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					aib_close_db();
					break;

				// Delete mode confirm

				case "del":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}
	
					// Set field values using primary to get administrative account

					$AdminRecord = ftree_get_user($GLOBALS["aib_db"],$FormData["primary"]);
					if ($AdminRecord == false)
					{
						$ErrorMessage = "Cannot retrieve administrator account information";
						aib_close_db();
						break;
					}

					$ArchiveID = $AdminRecord["user_top_folder"];
					$ArchiveFolder = ftree_get_item($GLOBALS["aib_db"],$ArchiveID);
					$FieldDef["archive_code"]["value"] = $ArchiveFolder["item_title"];
					$FieldDef["admin_login"]["value"] = $AdminRecord["user_login"];
					$FieldDef["admin_name"]["value"] = $AdminRecord["user_title"];
					$OutBuffer[] = aib_gen_form_header("pageform","/assistant_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"".aib_get_nav_string()."\">";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='do_del'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

				// Add new administrator

				case "add":
				case false:
				default:
					$FieldDef["admin_login"]["value"] = "";
					$FieldDef["admin_name"]["value"] = "";
					$FieldDef["admin_pass"]["value"] = "";
					$FieldDef["admin_pass_confirm"]["value"] = "";
					$OutBuffer[] = aib_gen_form_header("pageform","/assistant_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
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

			$TargetSpec = array();
			if ($NavTargetInfo["target"] != "assistants.php")
			{
				$TargetSpec[] = array(
					"url" => "/".$NavTargetInfo["target"]."?aibnav=".aib_get_nav_string(),
					"title" => $NavTargetInfo["title"],
					"fields" => array()
					);
			}

			switch($OpCode)
			{
				case "save_edit":
				case "do_del":
					if ($ErrorMessage != false)
					{
						break;
					}

					if (isset($FormData["archive_code"]) == true)
					{
						$ArchiveCode = $FormData["archive_code"];
						$TargetSpec[] = array(
							"url" => "/assistants.php?archive_code=$ArchiveCode",
							"title" => "Return To Assistant List",
							"fields" => array(),
							);
					}
					else
					{
						$TargetSpec[] = array(
							"url" => "/assistants.php",
							"title" => "Return To Assistant List",
							"fields" => array(),
							);
					}


					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "save":
					if ($ErrorMessage != false)
					{
						break;
					}

					$TargetSpec[] = array(
							"url" => "/assistant_form.php",
							"title" => "Add Another Assistant",
							"fields" => array(
								"opcode" => "add",
								),
							);
					$TargetSpec[] = array(
							"url" => "/assistants.php",
							"title" => "Return To Assistant List",
							"fields" => array(
								"opcode" => "list",
								),
							);

					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "del":
					if ($ErrorMessage != false)
					{
						break;
					}

					if ($UserType == FTREE_USER_TYPE_ROOT)
					{
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_display_field($FieldDef["archive_code"]);
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["admin_login"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["admin_name"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_form_submit("Delete Assistant","link|/assistants.php|Go Back To List");
					break;

				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					aib_open_db();
					$OwnerList = array("NULL" => " -- SELECT --");
					$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
					$ArchiveID = -1;
					if ($OpCode == "edit")
					{
						$ArchiveID = $CurrentEditRecord["user_top_folder"];
					}

					if ($ArchiveGroupList != false)
					{
						foreach($ArchiveGroupList as $ArchiveGroupRecord)
						{
							$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
							$ArchiveGroupTitle = $ArchiveGroupRecord["item_title"];
							$ArchiveGroupCode = $ArchiveGroupRecord["_archive_group_code"];
							$OwnerList["ag:".$ArchiveGroupID] = $ArchiveGroupTitle;
							if ($ArchiveID == $ArchiveGroupID)
							{
								$ArchiveID = "ag:".$ArchiveID;
							}

							$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$ArchiveGroupID);
							foreach($ArchiveList as $ArchiveRecord)
							{
								$LocalArchiveID = $ArchiveRecord["item_id"];
								if ($ArchiveID == $LocalArchiveID)
								{
									$ArchiveID = "ar:".$ArchiveID;
								}

								$ArchiveTitle = $ArchiveRecord["item_title"];
								$OwnerList["ar:".$LocalArchiveID] = "&#9492; $ArchiveTitle";
							}
						}
					}

					aib_close_db();

					$FieldDef["archive_code"]["value"] = $ArchiveID;
					$OutBuffer[] = aib_draw_input_row_separator();
					$FieldDef["archive_code"]["option_list"] = $OwnerList;
					if (isset($FormData["archive_code"]) == true)
					{
						$FieldDef["archive_code"]["value"] = $FormData["archive_code"];
					}

					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["archive_code"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["admin_login"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["admin_name"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["admin_pass"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["admin_pass_confirm"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Add Assistant","Clear Form");
							break;
					}

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
