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
	$NavString = aib_get_nav_string();
	$NavTargetInfo = aib_get_nav_target();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set up display data array

	$DisplayData = array(
		"popup_list" => array(
			"archive_group_code_help_popup" => array("title" => "Help For: Archive Group Code",
							"heading" => "Help For: Archive Group Code",
							"text" => "Enter a code used to identify the archive group",
						),
			"archive_group_title_help_popup" => array("title" => "Help For: Archive Group Title",
							"heading" => "Help For: Archive Group Title",
							"text" => "Enter the title that will be shown to users.",
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
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: EDIT CLIENT";
			break;

		case "del":
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: DELETE CLIENT";
			break;

		case "add":
		default:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: ADD CLIENT";
			break;
	}

// Include menu data

include("template/top_menu_data.php");

	// Add admin header for menu and set current menu option

include("template/top_menu_admin_data.php");
	$DisplayData["current_menu"] = "Add Archive Group";


	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"archive_group_code" => array(
			"title" => "Archive Group Code:", "type" => "text", "display_width" => "16",
			"field_name" => "archive_group_code", "field_id" => "archive_group_code",
			"desc" => "", "help_function_name" => "archive_group_code_help_popup"),
		"archive_group_title" => array(
			"title" => "Archive Group Title:", "type" => "text", "display_width" => "64",
			"field_name" => "archive_group_title", "field_id" => "archive_group_title",
			"desc" => "", "help_function_name" => "archive_group_title_help_popup"),
	);

	// Define field validations

	$ValidationDef = array(
		"archive_group_code" => array("type" => "text", "field_id" => "archive_code",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter an archive group code"),
					),
				),
		"archive_group_title" => array("type" => "text", "field_id" => "archive_title",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter an archive group title"),
					),
				),
		);

	// Field area

	$OutBuffer = array();
	$NavID = aib_get_nav_value("primary");
	$NavTargetInfo = aib_get_nav_target();
	if ($NavTargetInfo != false && preg_match("/save/",$OpCode) == false)
	{
		if ($NavTargetInfo["target"] != "archivegroups.php")
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
						// Check for "ARCHIVE GROUP" folder; create if necessary

						$ArchiveGroupFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVE GROUP");
						if ($ArchiveGroupFolderID === false)
						{
							$FolderResult = ftree_create_object($GLOBALS["aib_db"],-1,"ARCHIVE GROUP",
								FTREE_USER_SUPERADMIN,FTREE_GROUP_ROOT,FTREE_OBJECT_TYPE_FOLDER);
							if ($FolderResult[0] != "OK")
							{
								$ErrorMessage = "Cannot find or create top folder; ".$FolderResult[1];
								break;
							}

							$ArchiveGroupFolderID = $FolderResult[1];
						}

						// Check for duplicate title and/or code

						$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code","NULL");
						if ($ArchiveGroupCode == "NULL")
						{
							$ErrorMessage = "Invalid archive group code";
							break;
						}

						$ArchiveGroupTitle = aib_get_with_default($FormData,"archive_group_title",false);
						if ($ArchiveGroupTitle === false)
						{
							$ErrorMessage = "Invalid archive group title";
							break;
						}

						$TempDef = ftree_get_child_object($GLOBALS["aib_db"],$ArchiveGroupFolderID,FTREE_OBJECT_TYPE_FOLDER,$ArchiveGroupTitle);
						if ($TempDef != false)
						{
							$ErrorMessage = "Archive group title already used";
							break;
						}

						$TempDef = ftree_get_all_property_values($GLOBALS["aib_db"],AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
						if ($TempDef != false)
						{
							foreach($TempDef as $TempRecord)
							{
								if ($TempRecord["property_value"] == $ArchiveGroupCode)
								{
									$ErrorMessage = "Archive group code already used";
									break;
								}
							}

							if ($ErrorMessage != false)
							{
								break;
							}
						}
	
						// Save new folder
	
						$FolderResult = ftree_create_object($GLOBALS["aib_db"],$ArchiveGroupFolderID,$ArchiveGroupTitle,
							FTREE_USER_SUPERADMIN,FTREE_GROUP_ROOT,FTREE_OBJECT_TYPE_FOLDER);
						if ($FolderResult[0] != "OK")
						{
							$ErrorMessage = $FolderResult[1];
							break;
						}
	
						$FolderID = $FolderResult[1];

						// Save archive group code property

						ftree_set_property($GLOBALS["aib_db"],$FolderID,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE,$ArchiveGroupCode);
						ftree_set_property($GLOBALS["aib_db"],$FolderID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_ARCHIVE_GROUP,$ArchiveGroupCode);
						$FormData["archive_group_title"] = "";
						$FormData["archive_group_code"] = "";
						$StatusMessage = "Archive Group \"<i>$ArchiveGroupTitle ($ArchiveGroupCode)</i>\" created successfully";
						break;
					}

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
					$FieldDef["archive_group_code"]["value"] = aib_get_with_default($FormData,"archive_group_code","");
					$FieldDef["archive_group_title"]["value"] = aib_get_with_default($FormData,"archive_group_title","");
//					$OutBuffer[] = aib_gen_form_header("pageform","/archivegroup_form.php",false,"validate_form");
//					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
//					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
//					$OutBuffer[] = "<table class='aib-input-set'>";
					break;


				// Save edited item and return to list

				case "save_edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
					}

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary === false)
					{
						$ErrorMessage = "Missing primary";
						break;
					}

					while($ErrorMessage == false)
					{

						// Check for duplicate title and/or code

						$ArchiveGroupCode = aib_get_with_default($FormData,"archive_group_code","NULL");
						if ($ArchiveGroupCode == "NULL")
						{
							$ErrorMessage = "Invalid archive group code";
							break;
						}

						$ArchiveGroupTitle = aib_get_with_default($FormData,"archive_group_title",false);
						if ($ArchiveGroupTitle === false)
						{
							$ErrorMessage = "Invalid archive group title";
							break;
						}

						$CurrentItemRecord = ftree_get_item($GLOBALS["aib_db"],$Primary);
						$CurrentCode = false;
						$TempDef = ftree_get_child_object($GLOBALS["aib_db"],$ArchiveGroupFolderID,FTREE_OBJECT_TYPE_FOLDER,$ArchiveGroupTitle);
						if ($TempDef != false)
						{
							if ($TempDef["item_id"] != $Primary)
							{
								$ErrorMessage = "Archive group title already used";
								break;
							}
						}

						$TempDef = ftree_get_all_property_values($GLOBALS["aib_db"],AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
						if ($TempDef != false)
						{
							foreach($TempDef as $TempRecord)
							{
								if ($TempRecord["item_id"] == $Primary)
								{
									$CurrentCode = $TempRecord["property_value"];
								}

								if ($TempRecord["property_value"] == $ArchiveGroupCode && $TempRecord["item_id"] != $Primary)
								{
									$ErrorMessage = "Archive group code already used";
									break;
								}
							}

							if ($ErrorMessage != false)
							{
								break;
							}
						}

						// If the title has changed, rename item.

						if ($CurrentRecord["item_title"] != $ArchiveGroupTitle)
						{
							ftree_rename($GLOBALS["aib_db"],$Primary,$ArchiveGroupTitle);
						}

						// If the code has changed, update property.

						if ($CurrentCode !== false && $ArchiveGroupCode != $CurrentCode)
						{
							ftree_set_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE,$ArchiveGroupCode,true);
						}

						$StatusMessage = "Archive group successfully updated";
						break;
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
					ftree_delete_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
					$StatusMessage = "Archive group successfully deleted";
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
	
					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					// Set field values.  First, get item definition.  Then get property.
	
					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$Primary);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve item";
						aib_close_db();
						break;
					}
	
					$CodeProperty = ftree_get_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
					if ($CodeProperty == false)
					{
						$CodeProperty = "";
					}
	
					$FieldDef["archive_group_code"]["value"] = $CodeProperty;
					$FieldDef["archive_group_title"]["value"] = aib_get_with_default($ItemDef,"item_title","");
					$OutBuffer[] = aib_gen_form_header("pageform","/archivegroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
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
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					// Set field values.  First, get item definition.  Then get property.
	
					$CodeProperty = ftree_get_property($GLOBALS["aib_db"],$Primary,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
					if ($CodeProperty == false)
					{
						$CodeProperty = "";
					}
	
					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$Primary);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve item";
						aib_close_db();
						break;
					}
	
					$FieldDef["archive_group_code"]["value"] = aib_get_with_default($ItemDef,"item_title","");
					$FieldDef["archive_group_title"]["value"] = $CodeProperty;
					$OutBuffer[] = aib_gen_form_header("pageform","/archivegroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='do_del'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

				// Add new archive

				case "add":
				case false:
				default:
					$FieldDef["archive_group_code"]["value"] = "";
					$FieldDef["archive_group_title"]["value"] = "";
					$OutBuffer[] = aib_gen_form_header("pageform","/archivegroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
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
			if ($NavTargetInfo["target"] != "archivegroups.php")
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

					$TargetSpec[] = 
						array(
						"url" => "/archivegroups.php",
						"title" => "Return To Archive Group List",
						"fields" => array(
							"aibnav" => $NavString,
							),
						);

					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "save":
					if ($ErrorMessage != false)
					{
						break;
					}

					$TargetSpec[] =
						array(
							"url" => "/archivegroup_form.php",
							"title" => "Add Another Archive Group",
							"fields" => array(
							"opcode" => "add",
							"aibnav" => $NavString,
							),
						);

					$TargetSpec[] = 
						array(
							"url" => "/archivegroups.php",
							"title" => "Return To Archive Group List",
							"fields" => array(
							"opcode" => "list",
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
					$OutBuffer[] = aib_draw_display_field($FieldDef["archive_group_code"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["archive_group_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_form_submit("Delete Archive Group","link|/archivegroup_form.php|Go Back To List");
					break;

				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["archive_group_code"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["archive_group_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Add Archive Group","Clear Form");
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
