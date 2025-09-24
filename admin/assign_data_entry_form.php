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
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();
	$OpCode = aib_get_with_default($FormData,"opcode",false);
	$ParentFolder = aib_get_with_default($FormData,"parent","");
	$SourceKey = aib_get_with_default($FormData,"srckey","");
	$SearchValue = aib_get_with_default($FormData,"searchval","");
	$SourceMode = aib_get_with_default($FormData,"srcmode","");
	$SourcePageNumber = aib_get_with_default($FormData,"srcpn","");

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: ASSIGN DATA ENTRY",
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
	$UserRecord = $UserInfo[1];
	$UserID = $UserRecord["user_id"];
	$UserType = $UserRecord["user_type"];
	if ($UserID == AIB_SUPERUSER)
	{
		$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: ASSIGN DATA ENTRY";
	}
	else
	{
		$DisplayData["page_title"] = $UserRecord["user_title"]." (".$UserRecord["user_login"]."): ASSIGN DATA ENTRY";
	}

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	if ($UserType == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
		$DisplayData["current_menu"] = "Add Assistant";
	}


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
	$ResultMessageList = array();
	$OutBuffer = array();
	$NavID = aib_get_nav_value("primary");
	$NavTargetInfo = aib_get_nav_target();
	if ($NavTargetInfo != false && preg_match("/save/",$OpCode) == false)
	{
		if ($NavTargetInfo["target"] != "fields.php")
		{
			$URL = "/".$NavTargetInfo["target"]."?aibnav=".aib_get_nav_string();
			$UpTitle = "Return To ".$NavTargetInfo["title"];
			$OutBuffer[] = "<tr><td align='left' valign='top'>";
			$OutBuffer[] = "<div class='browse-uplink-div'><a href='$URL' class='browse-uplink-link'>$UpTitle</a></div><br>";
			$OutBuffer[] = "</td></tr>";
		}
	}


	// Field area

	$OutBuffer[] = "<tr> <td align='left' valign='top'>";
			$ErrorMessage = false;
			$StatusMessage = false;

			switch($OpCode)
			{
				// Assign data entry to an assistant

				case "assign":

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Get current archive

					$ParentFolder = aib_get_with_default($FormData,"parent","-1");
					if ($ParentFolder == -1)
					{
						$ErrorMessage = "Missing archive and/or parent folder ID";
						break;
					}

					$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
					if ($ArchiveInfo["archive_group"] == false)
					{
						$ErrorMessage = "Bad archive";
						break;
					}

					$ArchiveID = $ArchiveInfo["archive"]["item_id"];

					// Get the list of assistants for the current archive

					$AssistantUserList = aib_db_query("SELECT * FROM ftree_user WHERE user_type='".FTREE_USER_TYPE_SUBADMIN."' AND user_top_folder=$ArchiveID;");
					if ($AssistantUserList == false)
					{
						$AssistantUserList = array();
					}

					// Start form

					$OutBuffer[] = aib_gen_form_header("pageform","/assign_data_entry_form.php",false,"validate_form");

					// Hidden fields with any passed state information supplied to this form

					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='assign_save'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='$ParentFolder'>";
					$OutBuffer[] = "<input type='hidden' name='srckey' value='$SourceKey'>";
					$OutBuffer[] = "<input type='hidden' name='searchval' value='$SearchValue'>";
					$OutBuffer[] = "<input type='hidden' name='srcmode' value='$SourceMode'>";
					$OutBuffer[] = "<input type='hidden' name='srcpn' value='$SourcePageNumber'>";

					// Field area

					$OutBuffer[] = "<table class='aib-input-set'>";

					// Drop-down with assistants

					$FieldDef = array("title" => "Select Assistant:","type" => "dropdown",
						"display_width" => "32", "field_name" => "assistant_id",
						"field_id" => "assistant_id", "desc" => "");
					$TempSet = array("-1" => " *** UNMARK CHECKED ITEMS *** ");

					foreach($AssistantUserList as $AssistantRecord)
					{
						$TempSet[$AssistantRecord["user_id"]] = $AssistantRecord["user_title"];
					}

					$FieldDef["option_list"] = $TempSet;
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef);
					$CustomFieldDef = array("title" => "", "type" => "",
						"display_width" => "", "field_name" => "custom1", "field_id" => "custom1",
						"desc" => "",
						);
					$CustomFieldDef["fielddata"] = "<br><span style='width:100%; display:inline-block; vertical-align:middle; font-size:1.0em; font-weight:bold;'>Sub-Groups:</span>".
						"<br><span style='width:100%; height:2px; background:#000000; display:inline-block; vertical-align:middle;'> &nbsp; </span>";
					$OutBuffer[] = aib_draw_custom_field($CustomFieldDef);

					// Show each selected subgroup as a checkbox item that is checked

					$IDList = explode(",",aib_get_with_default($FormData,"idlist",""));
					$FieldDef = array("title" => "", "type" => "checkbox", "display_width" => "25",
						"field_name" => "", "field_id" => "", "desc" => "");
					foreach($IDList as $ItemID)
					{
						$ItemRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$ItemID);
						if ($ItemRecord == false)
						{
							continue;
						}

						$FieldDef["side_title"] = $ItemRecord["item_title"];
						$FieldDef["field_name"] = "checkeditem_".$ItemID;
						$FieldDef["field_id"] = $FieldDef["field_name"];
						$FieldDef["checked"] = "CHECKED";
						$OutBuffer[] = aib_draw_checkbox_field($FieldDef);
					}
						
					$CustomFieldDef["fielddata"] = "<span style='width:100%; height:2px; background:#000000; display:inline-block; vertical-align:middle;'> &nbsp; </span>";
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();

					// Submit button

					$OutBuffer[] = aib_draw_form_submit("Apply","Undo");

					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";

					// Set up right-side navigation panel

					$IndicatorEntryTemplate = "<a class='aib-loc-path-link' href='/browse.php?&parent=[[ITEMID]]'>[[TITLE]]</a>";
					$IndicatorOptions = array("entry_template" => $IndicatorEntryTemplate, "ul_template" => "<ul class='aib-loc-indicator-list'>");
					$IndicatorOptions["pad_cell_template"] = "<td width='5'></td>";
					$IndicatorOptions["entry_cell_template"] = "<td colspan='99'>";
					$IndicatorOptions["symbol_cell_template"] = "<td style='width:0.5em; padding:0;'><span style='font-size:1.5em; color:#a0a0a0;'>&#9492;</span></td>";
					$IndicatorOptions["table_template"] = "<table width='100%' cellpadding='0' cellspacing='0'>";
					$IndicatorOptions["archive_groups_title"] = "Organizations";

					// Get ID path for current parent

					$IDPathList = ftree_get_item_id_path($GLOBALS["aib_db"],$ParentFolder);

					// Generate nav display on right

					$RightColContentLines = array();
					$RightColContentLines[] = "<div class='aib-loc-indicator-title-div'>";
					$RightColContentLines[] = "<span class='aib-loc-indicator-title-span'>Your Current Location</span>";
					$RightColContentLines[] = "</div>";
					$RightColContentLines[] = "<div class='aib-loc-indicator-div'>";
					$RightColContentLines[] = aib_generate_loc_indicator_table($GLOBALS["aib_db"],$IndicatorOptions,$IDPathList);
					$RightColContentLines[] = "<div class='clearitall'></div>";
					$RightColContentLines[] = "</div>";
					$DisplayData["right_col"] = join("\n",$RightColContentLines);
					aib_close_db();
					break;

				case "assign_save":
					
					// Update database

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Get current archive

					$ParentFolder = aib_get_with_default($FormData,"parent","-1");
					if ($ParentFolder == -1)
					{
						$ErrorMessage = "Missing archive and/or parent folder ID";
						break;
					}

					$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolder);
					if ($ArchiveInfo["archive_group"] == false)
					{
						$ErrorMessage = "Bad archive";
						break;
					}

					$ArchiveID = $ArchiveInfo["archive"]["item_id"];

					// Get the assistant

					$AssistantID = aib_get_with_default($FormData,"assistant_id",false);
					if ($AssistantID === false)
					{
						$ErrorMessage = "Missing assistant ID";
						$AssistantName = "Missing assistant ID";
						break;
					}

					if ($AssistantID != -1)
					{
						$AssistantRecord = ftree_get_user($GLOBALS["aib_db"],$AssistantID);
						$AssistantName = urldecode($AssistantRecord["user_title"]);
					}
					else
					{
						$AssistantName = " *** UNMARK CHECKED ITEMS *** ";
					}

					// Get all of the checked sub-groups

					$CheckedList = array();
					foreach($FormData as $FormFieldName => $FormFieldValue)
					{
						if (preg_match("/^checkeditem[\_]/",$FormFieldName) == false)
						{
							continue;
						}

						$LocalID = preg_replace("/^checkeditem[\_]/","",$FormFieldName);
						$CheckedList[] = $LocalID;
					}

					// Update the database table with all of the items in each selected subgroup

					foreach($CheckedList as $SubGroupID)
					{
						$SubGroupRecord = ftree_get_item($GLOBALS["aib_db"],$SubGroupID);
						$SubGroupRecord["item_title"] = urldecode($SubGroupRecord["item_title"]);
						if ($SubGroupRecord == false)
						{
							$Msg = "Cannot retrieve profile for subgroup $SubGroupID";
							$ResultMessageList[] = $Msg;
							continue;
						}

						$AssignResult = ftree_mark_folder_for_data_entry($GLOBALS["aib_db"],$SubGroupID,$AssistantID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_RECORD,false);
						switch($AssignResult["status"])
						{
							case "OK":
								$TotalRecords = $AssignResult["total"];
								$MarkedRecords = $AssignResult["marked"];
								$UsedRecord = $AssignResult["in_use"];
								$Msg = "Processed ".$SubGroupRecord["item_title"]."; $TotalRecords records of which $MarkedRecords were marked ($UsedRecord in use)";
								$ResultMessageList[] = $Msg;
								break;

							case "ERROR":
								if ($AssignResult["msg"] == "NO ITEMS")
								{
									$Msg = "Processed ".$SubGroupRecord["item_title"]."; no records available to be marked";
									$ResultMessageList[] = $Msg;
									break;
								}

								$Msg = "Processed ".$SubGroupRecord["item_title"]."; ERROR";
								$ResultMessageList[] = $Msg;
								break;

							default:
								$Msg = "Processed ".$SubGroupRecord["item_title"]."; ERROR";
								$ResultMessageList[] = $Msg;
								break;
						}
					}

					// Close database

					aib_close_db();

					// Chain to the appropriate location

					if ($AssistantID != -1)
					{
						$StatusMessage = "Subgroup(s) Successfully Assigned To \"$AssistantName\"";
					}
					else
					{
						$StatusMessage = "Subgroup(s) Successfully Un-Marked";
					}

					break;

				default:
					$ErrorMessage = "Invalid mode";
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
					if (count($ResultMessageList) > 0)
					{
						$OutBuffer[] = "<br>";
						foreach($ResultMessageList as $Msg)
						{
							$OutBuffer[] = "<br>$Msg";
						}
					}

					$OutBuffer[] = "</span>";
				}

				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "<tr class='aib-form-result-message-footer-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-footer-cell'>";
				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "</table>";
			}

			// Output form or chain depending on operation

			switch($OpCode)
			{
				case "assign_save":
					if ($ErrorMessage != false)
					{
						break;
					}

					$TargetSpec = array(
						array(
						"url" => "/records.php?parent=$ParentFolder",
						"title" => "Return To Records Management",
						"fields" => array(),
						),
					);

					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				default:
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
