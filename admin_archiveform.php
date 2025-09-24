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

	// Make sure this is an admin

	$UserRecord = $UserInfo[1];
	$UserID = $UserRecord['user_id'];
	$UserType = $UserRecord["user_type"];
	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
		case FTREE_USER_TYPE_ADMIN:
			break;

		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();
	$NavTargetInfo = aib_get_nav_target();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array

	$DisplayData = array(
		"popup_list" => array(
			"archive_code_help_popup" => array("title" => "Help For: Archive Code",
							"heading" => "Help For: Archive Code",
							"text" => "Enter a code used to identify the archive.",
						),
			"archive_title_help_popup" => array("title" => "Help For: Archive Title",
							"heading" => "Help For: Archive Title",
							"text" => "Enter the title that will be shown to users.",
						),
		),
	);

	$PageTimeout = AIB_SESSION_TIMEOUT;
	$PageTimeout = $PageTimeout * 1000;
	$DisplayData["head_script"] = "
		setTimeout(function() {
			window.location.href='/login.php';
		},$PageTimeout);

		// Prevent the use of the 'back' button to prevent mangling
		// forms.

		history.pushState(null, null, document.URL);
		window.addEventListener('popstate', function () {
		    alert(\"Please use the links on the page instead of the 'Back' button.\");
		    history.pushState(null, null, document.URL);
		});


		";


	$OperationTitle = "ADD";
	switch($OpCode)
	{
		case "edit":
			$OperationTitle = "EDIT";
			break;

		case "del":
			$OperationTitle = "DELETE";
			break;

		default:
			$OperationTitle = "ADD";
			break;
	}

	// Include menu data and set title

	$AccountTitle = "USER";
include("template/top_menu_data.php");
	switch($UserType)
	{
		case FTREE_USER_TYPE_ROOT:
			$AccountTitle = "SYSTEM ADMINISTRATOR";
include("template/top_menu_admin_data.php");
			break;

		case FTREE_USER_TYPE_ADMIN:
			$AccountTitle = $UserRecord["user_title"];
			break;

		default:
			break;
	}

	$DisplayData["page_title"] = $AccountTitle.": $OperationTitle ARCHIVE";
	$DisplayData["current_menu"] = "Add Archive";



	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"archive_group_code" => array(
			"title" => "Archive Group", "type" => "dropdown", "display_width" => "45",
			"field_name" => "archive_group_code", "field_id" => "archive_group_code",
			"desc" => "", "help_function_name" => "archive_group_code_help_popup"),
		"archive_code" => array(
			"title" => "Archive Code:", "type" => "text", "display_width" => "10",
			"field_name" => "archive_code", "field_id" => "archive_code",
			"desc" => "", "help_function_name" => "archive_code_help_popup"),
		"archive_title" => array(
			"title" => "Archive Title:", "type" => "text", "display_width" => "64",
			"field_name" => "archive_title", "field_id" => "archive_title",
			"desc" => "", "help_function_name" => "archive_title_help_popup"),
	);

	// Define field validations

	$ValidationDef = array(
		"archive_group_code" => array("type" => "text", "field_id" => "archive_code",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter an archive code"),
					),
				),
		"archive_code" => array("type" => "text", "field_id" => "archive_code",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter an archive code"),
					),
				),
		"archive_title" => array("type" => "text", "field_id" => "archive_title",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter an archive title"),
					),
				),
		);

	// Field area

	$OutBuffer = array();
	$NavID = aib_get_nav_value("primary");
	$NavTargetInfo = aib_get_nav_target();
	if ($NavTargetInfo != false && preg_match("/save/",$OpCode) == false)
	{
		if ($NavTargetInfo["target"] != "admin_archives.php")
		{
			$URL = "/".$NavTargetInfo["target"]."?aibnav=".aib_get_nav_string();
			$UpTitle = "Return To ".$NavTargetInfo["title"];
			$OutBuffer[] = "<tr><td align='left' valign='top'>";
			$OutBuffer[] = "<div class='browse-uplink-div'><a href='$URL' class='browse-uplink-link'>$UpTitle</a></div><br>";
			$OutBuffer[] = "</td></tr>";
		}
	}

	$OutBuffer[] = "<tr> <td align='left' valign='top'>";

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
						// Check for missing data

						$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code",false);
						$ArchiveCode = aib_get_with_default($FormData,"archive_code",false);
						$ArchiveTitle = aib_get_with_default($FormData,"archive_title",false);
						if ($ArchiveGroupCode === false || intval($ArchiveGroupCode) <= 0)
						{
							$ErrorMessage = "Missing archive group";
							break;
						}

						if ($ArchiveCode == false || ltrim(rtrim($ArchiveCode)) == "")
						{
							$ErrorMessage = "Missing archive code";
							break;
						}

						if ($ArchiveTitle == false || ltrim(rtrim($ArchiveTitle)) == "")
						{
							$ErrorMessage = "Missing archive title";
							break;
						}

						// See if the archive code is already used

						$ArchiveCodeList = ftree_get_all_property_values($GLOBALS["aib_db"],AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
						if ($ArchiveCodeList != false)
						{
							foreach($ArchiveCodeList as $ArchiveCodeRecord)
							{
								if ($ArchiveCodeRecord["property_value"] == $ArchiveCode)
								{
									$ErrorMessage = "Duplicate archive code";
									break;
								}
							}
						
							if ($ErrorMessage != false)
							{
								break;
							}
						}

						// See if the archive title is already used for the archive group

						$ItemInfo = ftree_get_child_object($GLOBALS["aib_db"],$ArchiveGroupCode,FTREE_OBJECT_TYPE_FOLDER,$ArchiveTitle);
						if ($ItemInfo != false)
						{
							$ErrorMessage = "Duplicate archive title";
							break;
						}
	
						// Save new archive as a folder and set the folder type
	
						$FolderResult = ftree_create_object($GLOBALS["aib_db"],$ArchiveGroupCode,$ArchiveTitle,
							FTREE_USER_SUPERADMIN,FTREE_GROUP_ROOT,FTREE_OBJECT_TYPE_FOLDER);
						if ($FolderResult[0] != "OK")
						{
							$ErrorMessage = $FolderResult[1];
							break;
						}
	
						$FolderID = $FolderResult[1];
	
						// Save archive code property with archive title

						ftree_set_property($GLOBALS["aib_db"],$FolderID,AIB_FOLDER_PROPERTY_ARCHIVE_CODE,$ArchiveCode);
						ftree_set_property($GLOBALS["aib_db"],$FolderID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_ARCHIVE);
						$FormData["archive_title"] = "";
						$FormData["archive_code"] = "";
						$StatusMessage = "Archive \"<i>$ArchiveTitle ($ArchiveCode)</i>\" created successfully";
						break;
					}

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
					break;


				// Save edited item and return to list

				case "save_edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
					}

					// Check for errors and save

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					// Check for errors and save
	
					while($ErrorMessage == false)
					{
						// Check for missing data

						$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code",false);
						$ArchiveCode = aib_get_with_default($FormData,"archive_code",false);
						$ArchiveTitle = aib_get_with_default($FormData,"archive_title",false);
						if ($ArchiveGroupCode === false || intval($ArchiveGroupCode) <= 0)
						{
							$ErrorMessage = "Missing archive group";
							break;
						}

						if ($ArchiveCode == false || ltrim(rtrim($ArchiveCode)) == "")
						{
							$ErrorMessage = "Missing archive code";
							break;
						}

						if ($ArchiveTitle == false || ltrim(rtrim($ArchiveTitle)) == "")
						{
							$ErrorMessage = "Missing archive title";
							break;
						}

						// See if the archive code is already used

						$ArchiveCodeList = ftree_get_all_property_values($GLOBALS["aib_db"],AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
						if ($ArchiveCodeList != false)
						{
							foreach($ArchiveCodeList as $ArchiveCodeRecord)
							{
								if ($ArchiveCodeRecord["property_value"] == $ArchiveCode && $ArchiveCodeRecord["item_id"] != $Primary)
								{
									$ErrorMessage = "Duplicate archive code";
									break;
								}
							}
						
							if ($ErrorMessage != false)
							{
								break;
							}
						}
						else
						{
							$ErrorMessage = "Bad archive code list";
							break;
						}

						// See if the archive title is already used for the archive group

						$ItemInfo = ftree_get_child_object($GLOBALS["aib_db"],$ArchiveGroupCode,FTREE_OBJECT_TYPE_FOLDER,$ArchiveTitle);
						if ($ItemInfo != false)
						{
							if ($ItemInfo["item_id"] != $Primary)
							{
								$ErrorMessage = "Duplicate archive title";
								break;
							}
						}

						// Get the current definition (item)

						$CurrentItemRecord = ftree_get_item($GLOBALS["aib_db"],$Primary);
						$CurrentArchiveTitle = $CurrentItemRecord["item_title"];
						$CurrentArchiveCode = ftree_get_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);

						// Update only changed values

						if ($ArchiveTitle != $CurrentArchiveTitle)
						{
							ftree_rename($GLOBALS["aib_db"],$Primary,$ArchiveTitle);
						}

						if ($ArchiveCode != $CurrentArchiveCode)
						{
							ftree_set_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_CODE,$ArchiveCode);
						}

						// If the archive group has changed, move the folder

						$CurrentParent = $CurrentItemRecord["item_parent"];
						if ($CurrentParent != $ArchiveGroupCode)
						{
							ftree_move_item($GLOBALS["aib_db"],$Primary,$ArchiveGroupCode,true,false);
						}

						$StatusMessage = "Archive \"<i>$ArchiveTitle ($ArchiveCode)</i>\" updated successfully";
						break;
					}

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
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

					ftree_delete($GLOBALS["aib_db"],$Primary,true);
					ftree_delete_property($GLOBALS["aib_db"],$Primary,false);
					$StatusMessage = "Archive successfully deleted";
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
	
					// Set field values.  First, get item definition.  Then get property.
	
					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary === false)
					{
						$ErrorMessage = "Missing primary";
						break;
					}

					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$Primary);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve item";
						aib_close_db();
						break;
					}
	
					$TempList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
					$FieldDef["archive_group_code"]["option_list"] = array("" => " -- SELECT -- ");
					$DefaultArchiveGroupCode = "";
					foreach($TempList as $TempRecord)
					{
						$DefaultArchiveGroupCode = $TempRecord["item_id"];
						$LocalKey = $TempRecord["item_id"];
						$LocalTitle = $TempRecord["item_title"]." (".$TempRecord["_archive_group_code"].")";
						$FieldDef["archive_group_code"]["option_list"][$LocalKey] = $LocalTitle;
					}

					$ArchiveTitle = $ItemDef["item_title"];

					// Parent is the current archive group

					$ParentOfArchive = $ItemDef["item_parent"];
					$FieldDef["archive_group_code"]["value"] = $ParentOfArchive;
					$ArchiveCode = ftree_get_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
					$FieldDef["archive_code"]["value"] = $ArchiveCode;
					$FieldDef["archive_title"]["value"] = $ArchiveTitle;
					$OutBuffer[] = aib_gen_form_header("pageform","/admin_archiveform.php",false,"validate_form");
					if (count($FieldDef["archive_group_code"]["option_list"]) < 3)
					{
						$OutBuffer[] = "<input type='hidden' name='archive_group_code' value='$DefaultArchiveGroupCode'>";
					}

					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='$Primary'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
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

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary === false)
					{
						$ErrorMessage = "Missing primary";
						break;
					}
	
					// Set field values.  First, get item definition.  Then get property.
	
					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$FormData["primary"]);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve item";
						aib_close_db();
						break;
					}
	
					$ArchiveTitle = $ItemDef["item_title"];
					$ArchiveCode = ftree_get_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
					$FieldDef["archive_code"]["value"] = $ArchiveCode;
					$FieldDef["archive_title"]["value"] = $ArchiveTitle;
					$OutBuffer[] = aib_gen_form_header("pageform","/admin_archiveform.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='$Primary'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='do_del'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

				// Add new archive

				case "add":
				case false:
				default:
					aib_open_db();
					$FieldDef["archive_group_code"]["value"] = aib_get_with_default($FormData,"archive_group_code","");
					$TempList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
					$DefaultArchiveGroupCode = "";
					$FieldDef["archive_group_code"]["option_list"] = array("" => " -- SELECT -- ");
					foreach($TempList as $TempRecord)
					{
						$DefaultArchiveGroupCode = $TempRecord["item_id"];
						$LocalKey = $TempRecord["item_id"];
						$LocalTitle = $TempRecord["item_title"]." (".$TempRecord["_archive_group_code"].")";
						$FieldDef["archive_group_code"]["option_list"][$LocalKey] = $LocalTitle;
					}

					$FieldDef["archive_code"]["value"] = "";
					$FieldDef["archive_title"]["value"] = "";
					$OutBuffer[] = aib_gen_form_header("pageform","/admin_archiveform.php",false,"validate_form");
					if (count($FieldDef["archive_group_code"]["option_list"]) < 3)
					{
						$OutBuffer[] = "<input type='hidden' name='archive_group_code' value='$DefaultArchiveGroupCode'>";
					}

					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					aib_close_db();
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
			if ($NavTargetInfo["target"] != "admin_archives.php")
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

					$Fields = array();
					$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code",false);
					if ($ArchiveGroupCode != false)
					{
						$Fields["parent"] = $ArchiveGroupCode;
						$Fields["opcode"] = "list";
					}

					$Fields["aibnav"] = $NavString;
					$TargetSpec[] = array(
						"url" => "/admin_archives.php",
						"title" => "Return To Managing Archives",
						"fields" => $Fields,
						);

					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "save":
					if ($ErrorMessage != false)
					{
						break;
					}

					$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code",false);
					$TargetSpec[] =
						array(
							"url" => "/admin_archiveform.php",
							"title" => "Add Another Archive",
							"fields" => array(
								"opcode" => "add",
								"archive_group_code" => $ArchiveGroupCode,
								"aibnav" => $NavString,
								),
							);

					$TargetSpec[] =
						array(
							"url" => "/admin_archives.php",
							"title" => "Return To Managing Archives",
							"fields" => array(
								"opcode" => "list",
								"parent" => $ArchiveGroupCode,
								"aibnav" => $NavString,
								),
							);

					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "del":
					if ($ErrorMessage != false)
					{
						break;
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["archive_code"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["archive_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code",false);
					if ($ArchiveGroupCode === false)
					{
						$OutBuffer[] = aib_draw_form_submit("Delete Archive","link|/admin_archives.php|Go Back To List");
					}
					else
					{
						$OutBuffer[] = aib_draw_form_submit("Delete Archive","link|/admin_archives.php?primary=$ArchiveGroupCode|Go Back To List");
					}

					break;

				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					if (count($FieldDef["archive_group_code"]["option_list"]) > 2)
					{
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_dropdown_field($FieldDef["archive_group_code"]);
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["archive_code"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["archive_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Add Archive","Clear Form");
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
