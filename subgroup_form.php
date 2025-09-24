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

	$UserRecord = $UserInfo[1];
	$UserType = $UserRecord["user_type"];
	$UserID = $UserRecord["user_id"];
	$UserGroup = $UserRecord["user_primary_group"];

	// Get form data and opcode

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();
	$OpCode = aib_get_with_default($FormData,"opcode",false);
	$SourcePage = aib_get_with_default($FormData,"src",false);
	$SourceKey = aib_get_with_default($FormData,"srckey","");
	$SourceSearchValue = aib_get_with_default($FormData,"searchval","");
	$SourceMode = aib_get_with_default($FormData,"srcmode","");
	$SourcePageNumber = aib_get_with_default($FormData,"srcpn",1);
	$ParentFolderID = aib_get_with_default($FormData,"parent",aib_get_with_default($FormData,"primary",$UserRecord["user_top_folder"]));

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: ADD COLLECTION",
		"popup_list" => array(
			"subgroup_title_help_popup" => array("title" => "Help For: Sub Group Name",
							"heading" => "Help For: Sub Group Name",
							"text" => "Enter the name of the sub group",
						),
			"subgroup_visible_help_popup" => array("title" => "Help For: Sub Group Visible",
							"heading" => "Help For: Sub Group Visible",
							"text" => "Select \\'Yes\\' if the sub group is to be visible to the public.",
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

	// Set page title

	if ($UserType != FTREE_USER_TYPE_ROOT)
	{
		$DisplayData["page_title"] = $UserRecord["user_login"]."/".$UserRecord["user_title"];
	}
	else
	{
		$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR";
	}

	switch($OpCode)
	{
		case "edit":
			$DisplayData["page_title"] .= ": EDIT SUBGROUP";
			break;

		case "add":
			$DisplayData["page_title"] .= ": ADD SUBGROUP";
			break;

		case "del":
			$DisplayData["page_title"] .= ": DELETE SUBGROUP";
			break;

		case "save_edit":
			$DisplayData["page_title"] .= ": SUBGROUP CHANGES SAVED";
			break;

		case "do_del":
			$DisplayData["page_title"] .= ": SUBGROUP DELETED";
			break;
	}



// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
	}



	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"subgroup_title" => array(
			"title" => "Sub Group Name:", "type" => "text", "display_width" => "64",
			"field_name" => "subgroup_title", "field_id" => "subgroup_title",
			"desc" => "", "help_function_name" => "subgroup_title_help_popup"),
		"subgroup_visible" => array("title" => "Visible To Public?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "subgroup_visible", "field_id" => "subgroup_visible",
			"desc" => "If 'Yes', then the sub group can be seen by public users.", "help_function_name" => "subgroup_visible_help_popup"),
	);

	$FieldDef["subgroup_visible"]["option_list"] = array("Y" => "Yes", "N" => "No");
	$FieldDef["subgroup_visible"]["value"] = "Y";

	// Define field validations

	$ValidationDef = array(
		"archive_title" => array("type" => "text", "field_id" => "archive_title",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter an archive title"),
					),
				),
		);

	// Create text indicating where this entry is being created

	aib_open_db();
	$ParentRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);
	$ParentTitle = $ParentRecord["item_title"];
	$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"archive_name");
	$IDPathList = ftree_get_item_id_path($GLOBALS["aib_db"],$ParentFolderID);
	$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
	aib_close_db();
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



	// Field area

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
						break;
					}
	
					$ParentFolderID = aib_get_with_default($FormData,"parent",false);
					if ($ParentFolderID == false)
					{
						$ErrorMessage = "Missing parent code";
						break;
					}

					// Check for duplicate in archive folder for subgroup title

					$SubGroupTitle = aib_get_with_default($FormData,"subgroup_title",false);
					if ($SubGroupTitle === false)
					{
						$ErrorMessage = "Missing sub group name";
						break;
					}

					$TempDef = ftree_get_child_object($GLOBALS["aib_db"],$ParentFolderID,FTREE_OBJECT_TYPE_FOLDER,$SubGroupTitle);
					if ($TempDef != false)
					{
						$ErrorMessage = "Sub group title already used";
						break;
					}

					// Save new folder (subgroup).  Use drop-down for "visible" to set world permissions on item.

					$SubGroupVisible = aib_get_with_default($FormData,"subgroup_visible",false);
					if ($SubGroupVisible == "Y")
					{
						$WorldPermissions = "R";
					}
					else
					{
						$WorldPermissions = "";
					}

					// Create subgroup

					$FolderInfo = array("parent" => $ParentFolderID, 
						"title" => $SubGroupTitle,
						"user_id" => $UserID,
						"group_id" => $UserGroup,
						"item_type" => FTREE_OBJECT_TYPE_FOLDER, 
						"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
						"source_info" => "", 
						"reference_id" => -1, 
						"allow_dups" => false,
						"user_perm" => "RMWCODPN", 
						"group_perm" => "RMW", 
						$WorldPermissions);
					$FolderResult = ftree_create_object_ext($GLOBALS["aib_db"],$FolderInfo);
					if ($FolderResult[0] != "OK")
					{
						$ErrorMessage = $FolderResult[1];
						break;
					}

					// Create property which indicates the type of folder

					$NewID = $FolderResult[1];
					ftree_set_property($GLOBALS["aib_db"],$NewID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_SUBGROUP,true);
					$StatusMessage = "Sub group \"<i>$SubGroupTitle</i>\" created successfully";

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
						break;
					}
	
					$FieldDef["archive_title"]["value"] = "";
					$OutBuffer[] = "<div class='aib-path-display-div'>$ParentLinkPath</div>";
					$OutBuffer[] = aib_gen_form_header("pageform","/subgroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
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

					// Get record definition for primary key

					$ObjectInfo = ftree_get_object_by_id($GLOBALS["aib_db"],$Primary);
					if ($ObjectInfo == false)
					{
						aib_close_db();
						$ErrorMessage = "Object doesn't exist";
						break;
					}

					// Get parent object

					$ObjectParent = $ObjectInfo["item_parent"];

					// Check for duplicates.  Do this by getting child object by type and title, and see if something other than
					// the current item comes back.

					$UpdatedTitle = aib_get_with_default($FormData,"subgroup_title","");
					$TestEntry = ftree_get_child_object($GLOBALS["aib_db"],$ObjectParent,FTREE_OBJECT_TYPE_FOLDER,$UpdatedTitle);
					if ($TestEntry != false)
					{
						if ($TestEntry["item_id"] != $Primary)
						{
							aib_close_db();
							$ErrorMessage = "Sub group title is already used.";
							break;
						}
					}

					// Update

					ftree_set_property($GLOBALS["aib_db"],$Primary,"aibftype","sg",true);
					ftree_rename($GLOBALS["aib_db"],$Primary,$UpdatedTitle);
					aib_close_db();
					$StatusMessage = "Sub group successfully updated";
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
					$StatusMessage = "Sub group successfully deleted";
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

					// Set field values using primary to get subgroup

					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$FormData["primary"]);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve sub group definition";
						aib_close_db();
						break;
					}
	
					$ParentDef = ftree_get_item($GLOBALS["aib_db"],$ItemDef["item_parent"]);
					$FieldDef["subgroup_title"]["value"] = aib_get_with_default($ItemDef,"item_title","");
					$WorldPermissions = $ItemDef["world_perm"];
					if (preg_match("/[R]/",$WorldPermissions) != false)
					{
						$FieldDef["subgroup_visible"]["value"] = "Y";
					}
					else
					{
						$FieldDef["subgroup_visible"]["value"] = "N";
					}

					// Generate nav display on right

					$IndicatorEntryTemplate = "[[TITLE]]";
					$IndicatorOptions = array("entry_template" => $IndicatorEntryTemplate, "ul_template" => "<ul class='aib-loc-indicator-list'>");
					$IndicatorOptions["pad_cell_template"] = "<td width='5'></td>";
					$IndicatorOptions["entry_cell_template"] = "<td colspan='99'>";
					$IndicatorOptions["symbol_cell_template"] = "<td style='width:0.5em; padding:0;'><span style='font-size:1.5em; color:#a0a0a0;'>&#9492;</span></td>";
					$IndicatorOptions["table_template"] = "<table width='100%' cellpadding='0' cellspacing='0'>";
					$IndicatorOptions["archive_groups_title"] = "Organizations";

					$RightColContentLines = array();
					$RightColContentLines[] = "<div class='aib-loc-indicator-title-div'>";
					$RightColContentLines[] = "<span class='aib-loc-indicator-title-span'>Your Current Location</span>";
					$RightColContentLines[] = "</div>";
					$RightColContentLines[] = "<div class='aib-loc-indicator-div'>";
					$RightColContentLines[] = aib_generate_loc_indicator_table($GLOBALS["aib_db"],$IndicatorOptions,$IDPathList);
					$RightColContentLines[] = "<div class='clearitall'></div>";
					$RightColContentLines[] = "</div>";
					$DisplayData["right_col"] = join("\n",$RightColContentLines);

					$OutBuffer[] = aib_gen_form_header("pageform","/subgroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";
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
	
					// Set field values.  First, get item definition.  Then get property.
	
					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$FormData["primary"]);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve item";
						aib_close_db();
						break;
					}
	
					$ParentDef = ftree_get_item($GLOBALS["aib_db"],$ItemDef["item_parent"]);
					$FieldDef["subgroup_title"]["value"] = aib_get_with_default($ItemDef,"item_title","");
					$OutBuffer[] = aib_gen_form_header("pageform","/subgroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='do_del'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

				// Add new subgroup

				case "add":
				case false:
				default:
					$FieldDef["archive_title"]["value"] = "";
					aib_open_db();
					$IndicatorEntryTemplate = "<a class='aib-loc-path-link' href='/records.php?opcode=list&parent=[[ITEMID]]'>[[TITLE]]</a>";
					$IndicatorOptions = array("entry_template" => $IndicatorEntryTemplate, "ul_template" => "<ul class='aib-loc-indicator-list'>");
					$IndicatorOptions["pad_cell_template"] = "<td width='10' style='padding:0;'> </td>";
					$IndicatorOptions["entry_cell_template"] = "<td colspan='99'> &#9495; ";
					$IndicatorOptions["table_template"] = "<table width='100%'>";
					$RightColContentLines = array();
					$RightColContentLines[] = "<div class='aib-loc-indicator-div'>";
					$RightColContentLines[] = aib_generate_loc_indicator_table($GLOBALS["aib_db"],$IndicatorOptions,$IDPathList);
					$RightColContentLines[] = "<div class='clearitall'></div>";
					$RightColContentLines[] = "</div>";
					$DisplayData["right_col"] = join("\n",$RightColContentLines);
					aib_close_db();

					$OutBuffer[] = aib_gen_form_header("pageform","/subgroup_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";
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

			$SourcePage = aib_get_with_default($FormData,"src",false);
			$SourceKey = aib_get_with_default($FormData,"srckey","");
			$SourceSearchValue = aib_get_with_default($FormData,"searchval","");
			$SourceMode = aib_get_with_default($FormData,"srcmode","");
			$SourcePageNumber = aib_get_with_default($FormData,"srcpn",1);
			$ArchiveCode = aib_get_with_default($FormData,"archive_code",-1);
			$ParentFolderID = aib_get_with_default($FormData,"parent",$UserRecord["user_top_folder"]);
			$DefaultFields = array(
				"opcode" => "list",
				"srckey" => $SourceKey,
				"searchval" => $SourceSearchValue,
				"srcmode" => $SourceMode,
				"srcpn" => $SourcePageNumber,
				"parent" => $ParentFolderID,
				"archive_code" => $ArchiveCode,
				);
			$TargetFields = join("&",
				array("src=records","parent=$ParentFolderID","srckey=$SourceKey","searchval=$SourceSearchValue","srcmode=$SourceMode","srcpn=$SourcePageNumber","archive_code=$ArchiveCode","aibnav=$NavString"));

			switch($OpCode)
			{
				case "save_edit":
					if ($ErrorMessage != false)
					{
						break;
					}

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

					// Get record definition for primary key

					$ObjectInfo = ftree_get_object_by_id($GLOBALS["aib_db"],$Primary);
					if ($ObjectInfo == false)
					{
						aib_close_db();
						$ErrorMessage = "Object doesn't exist";
						break;
					}

					// Get parent object

					$ObjectParent = $ObjectInfo["item_parent"];
					$DefaultFields["parent"] = $ObjectParent;
					$DefaultFields["aibnav"] = $NavString;
					$TargetSpec = array(array("url" => "/records.php","title" => "Return To Records Management","fields" => $DefaultFields));
					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "do_del":
					if ($ErrorMessage != false)
					{
						break;
					}

					$DefaultFields["aibnav"] = $NavString;
					$TargetSpec = array(array("url" => "/records.php","title" => "Return To Records Management","fields" => $DefaultFields));
					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "save":
					if ($ErrorMessage != false)
					{
						break;
					}

					$TargetSpec = array();
					$DefaultFields["aibnav"] = $NavString;
					$TargetSpec[] = array("url" => "/records.php","title" => "Return To Record Management","fields" => $DefaultFields);
					$TempFields = $DefaultFields;
					$TempFields["opcode"] = "add";
					$TargetSpec[] = array( "url" => "/subgroup_form.php", "title" => "Add Another Sub Group","fields" => $TempFields);
					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;

				case "del":
					if ($ErrorMessage != false)
					{
						break;
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_display_field($FieldDef["subgroup_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_form_submit("Delete Sub Group","link|/records.php?opcode=list&$TargetFields|Go Back To List");
					break;

				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["subgroup_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["subgroup_visible"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Add Sub Group","Clear Form");
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
