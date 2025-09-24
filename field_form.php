<?php
//
// field_form.php
//
// Manage fields
//
// Field ownership notes
//
// 1) The "owner" of a field is determined by the user account
//	a) Fields created by the root user belong to the system unless specifically assigned to an archive folder
//	b) Fields created by an archive user belong to the archive unless specifically created for an entry/item or a user
//
// 2) The "owner type" is:
//	a) If the owner is the system, then FTREE_OWNER_TYPE_SYSTEM
//	b) If the owner is an archive, then FTREE_OWNER_TYPE_ITEM
//	c) If the owner is a user, then FTREE_OWNER_TYPE_USER
//
// 3) The type of ownership is passed as a field.  If not present, the ownership is
//    determined solely by the user profile and the source page.  If the source page
//    is not present, the ownership is based on the user type and the archive.

// FUNCTIONAL INCLUDES

include('config/aib.php');
include("include/folder_tree.php");
include("include/fields.php");
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
	$UserID = $UserRecord["user_id"];
	$UserType = $UserRecord["user_type"];

	// Get form data and opcode

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set owner type based on user profile and any additional fields

	$SourcePage = aib_get_with_default($FormData,"source",false);
	$SourcePageKey = aib_get_with_default($FormData,"source_key",false);
	$OwnerTypeRequest = aib_get_with_default($FormData,"owner_type","archive");

	// Get current archive based on user.  If this is the super-user, then no current archive.

	aib_open_db();
	$InitArchiveList = aib_get_user_archive($GLOBALS["aib_db"],$UserRecord["user_id"]);
	aib_close_db();
	if (count($InitArchiveList) < 1)
	{
		$CurrentArchive = -1;
	}
	else
	{
		if (count($InitArchiveList) > 1)
		{
			$CurrentArchive = -1;
		}
		else
		{
			$CurrentArchive = $InitArchiveList[0]["item_id"];
		}
	}

	$ArchiveList = array("NULL" => " -- SELECT -- ");
	foreach($InitArchiveList as $InitRecord)
	{
		$ArchiveList[$InitRecord["item_id"]] = $InitRecord["_archive_group_title"].": ".$InitRecord["item_title"];
	}

	// Set user name and make sure this user can add or modify fields

	switch($UserType)
	{
		// Root user (superadmin) has no archive

		case FTREE_USER_TYPE_ROOT:
			$UserName = "SYSTEM ADMINISTRATOR";
			break;

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_SUBADMIN:
			$UserName = $UserRecord["user_title"];
			break;

		// Standard users can't add a field

		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	$FieldOwnerType = aib_get_with_default($FormData,"field_owner_type",FTREE_OWNER_TYPE_SYSTEM);
	$FieldOwnerID = aib_get_with_default($FormData,"field_owner_id",false);


	// Set up display data array

	$DisplayData = array(
		"page_title" => $UserName.": MANAGE FIELDS",
		"popup_list" => array(
			"archive_code_help_popup" => array("title" => "Help For: Archive Code",
							"heading" => "Help For: Archive Code",
							"text" => "Select the archive to which the administrator will be assigned",
						),
			"admin_login_help_popup" => array("title" => "Help For: Field Login",
							"heading" => "Help For: Field Login",
							"text" => "Enter the login ID of the administrator",
						),
			"admin_name_help_popup" => array("title" => "Help For: Field Name",
							"heading" => "Help For: Field Name",
							"text" => "Enter the full name or title of the administrator being created",
						),
			"admin_pass_help_popup" => array("title" => "Help For: Field Password",
							"heading" => "Help For: Field Password",
							"text" => "Enter the password for the administrator",
						),
			"admin_pass_confirm_help_popup" => array("title" => "Help For: Field Confirm Password",
							"heading" => "Help For: Field Confirm Password",
							"text" => "Enter the new administrator password again to confirm",
						),
		),
	);

	switch($OpCode)
	{
		case "edit":
			$DisplayData["page_title"] = "$UserName: EDIT FIELDS";
			break;

		case "del":
			$DisplayData["page_title"] = "$UserName: DELETE FIELD";
			break;

		default:
			$DisplayData["page_title"] = "$UserName: ADD FIELD";
			break;
	}

	$DisplayData["head_script"] = 
		"
			\$(document).ready(function(){
				init_field_data_type_change_handler();
			});

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

	$UserRecord = $UserInfo[1];
	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
		$DisplayData["current_menu"] = "Add Field";
	}



	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"archive_code" => array(
			"title" => "Archive:", "type" => "dropdown", "display_width" => "10",
			"field_name" => "archive_code", "field_id" => "archive_code",
			"desc" => "If field is custom for archive, use the selected archive", "help_function_name" => "archive_code_help_popup"),

		"field_owner_id" => array(
			"title" => "Field Owner:", "type" => "dropdown", "display_width" => "10",
			"field_name" => "field_owner_id", "field_id" => "field_owner_id",
			"desc" => "", "help_function_name" => "field_owner_help_popup"),

		"field_class" => array(
			"title" => "Field Class", "type" => "dropdown", "display_width" => "25",
			"field_name" => "field_class", "field_id" => "field_class",
			"desc" => "", "help_function_name" => "field_class_help_popup",
			"option_list" => array(
				FTREE_OWNER_TYPE_SYSTEM => "Traditional (All Archives)",
				FTREE_OWNER_TYPE_RECOMMENDED => "Recommended (All Archives)",
				FTREE_OWNER_TYPE_ITEM => "Custom For Selected Archive",
				),
			),

		"field_title" => array(
			"title" => "Field Name:", "type" => "text", "display_width" => "25",
			"field_name" => "field_title", "field_id" => "field_title",
			"desc" => "", "help_function_name" => "field_title_help_popup"),

		"field_data_type" => array(
			"title" => "Field Type:", "type" => "dropdown", "display_width" => "25",
			"field_name" => "field_data_type", "field_id" => "field_data_type",
			"desc" => "", "help_function_name" => "field_data_type_help_popup",
			"option_list" => array(
				FTREE_FIELD_TYPE_TEXT => "Short Text, Up To 255 Characters",
				FTREE_FIELD_TYPE_BIGTEXT => "Long Text, Up To 64,000 Characters",
				FTREE_FIELD_TYPE_FLOAT => "Number",
				FTREE_FIELD_TYPE_INTEGER => "Whole Number",
				FTREE_FIELD_TYPE_DECIMAL => "Number With Fixed Decimals",
				FTREE_FIELD_TYPE_DATE => "Date",
				FTREE_FIELD_TYPE_TIME => "Time",
				FTREE_FIELD_TYPE_DATETIME => "Combined Date And Time",
				FTREE_FIELD_TYPE_TIMESTAMP => "System Timestamp (No Editing)",
				FTREE_FIELD_TYPE_DROPDOWN => "Option List",
				),
			),

		"field_format" => array(
			"title" => "Field Format Detail:", "type" => "textarea", "rows" => "5", "cols" => "40",
			"field_name" => "field_format", "field_id" => "field_format",
			"desc" => "No special formatting options required", "help_function_name" => "field_format_help_popup",
			),

		"field_size" => array(
			"title" => "Field Display Width:", "type" => "text", "display_width" => "10",
			"field_name" => "field_size", "field_id" => "field_size",
			"desc" => "Edit to change the size of the field on the screen", "help_function_name" => "field_size_help_popup"),

	);

	$FieldDef["archive_code"]["option_list"] = $ArchiveList;
	$FieldDef["archive_code"]["value"] = $CurrentArchive;

	// Define field validations

	$GlobalFieldOwnerID = false;
	$GlobalFieldOwnerType = false;
	$ValidationDef = false;
	switch($OpCode)
	{
		case "edit":
		case "add":
			$ValidationDef = array(
				"archive_code" => array("type" => "text", "field_id" => "archive_code",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter an archive code"),
							),
						),
				"field_title" => array("type" => "text", "field_id" => "field_title",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a title for the field"),
							),
						),
				"field_data_type" => array("type" => "text", "field_id" => "field_data_type",
						"conditions" => array(
							"notblank" => array("error_message" => "You must select a data type"),
							),
						),
				"field_size" => array("type" => "text", "field_id" => "field_size",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a maximum width"),
							),
						),
				);

				break;
	}

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

	$OutBuffer[] = " <tr> <td align='left' valign='top'>";

			$ErrorMessage = false;
			$StatusMessage = false;

			switch($OpCode)
			{
				// Save new archive

				case "save":

					$LocalArchiveCode = aib_get_with_default($FormData,"archive_code",-1);
					$LocalTitle = aib_get_with_default($FormData,"field_title",false);
					$LocalDataType = aib_get_with_default($FormData,"field_data_type",false);
					$LocalFormat = aib_get_with_default($FormData,"field_format","");
					$LocalSize = aib_get_with_default($FormData,"field_size",false);
					$LocalOwnerID = aib_get_with_default($FormData,"field_owner_id",false);
					if ($LocalTitle == false)
					{
						$ErrorMessage = "Missing title";
						break;
					}

					if ($LocalDataType == false)
					{
						$ErrorMessage = "Missing data type";
						break;
					}

					if ($LocalSize === false)
					{
						$ErrorMessage = "Missing size";
						break;
					}

					if ($LocalOwnerID === false)
					{
						$ErrorMessage = "Missing owner";
						break;
					}

					$LocalOwnerType = false;

					// Determine the owner and owner type for the field based on the owner ID field input

					while(true)
					{
						if (preg_match("/^us[\:]/",$LocalOwnerID) != false)
						{
							$LocalOwnerType = FTREE_OWNER_TYPE_USER;
							$LocalOwnerID = substr($LocalOwnerID,3);
							break;
						}

						if (preg_match("/^it[\:]/",$LocalOwnerID) != false)
						{
							$LocalOwnerType = FTREE_OWNER_TYPE_ITEM;
							$LocalOwnerID = substr($LocalOwnerID,3);
							break;
						}

						if (preg_match("/^ar[\:]/",$LocalOwnerID) != false)
						{
							$LocalOwnerType = FTREE_OWNER_TYPE_ITEM;
							$LocalOwnerID = substr($LocalOwnerID,3);
							break;
						}

						if (preg_match("/^ag[\:]/",$LocalOwnerID) != false)
						{
							$LocalOwnerType = FTREE_OWNER_TYPE_ITEM;
							$LocalOwnerID = substr($LocalOwnerID,3);
							break;
						}

						if (preg_match("/^trad[\:]/",$LocalOwnerID) != false)
						{
							$LocalOwnerType = FTREE_OWNER_TYPE_SYSTEM;
							$LocalOwnerID = -1;
							break;
						}

						if (preg_match("/^rec[\:]/",$LocalOwnerID) != false)
						{
							$LocalOwnerType = FTREE_OWNER_TYPE_RECOMMENDED;
							$LocalOwnerID = -1;
							break;
						}

						$LocalOwnerType = FTREE_OWNER_TYPE_USER;
						$LocalOwnerID = $UserID;
						break;
					}

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Create field

					$Result = ftree_field_create_field($GLOBALS["aib_db"],$LocalTitle,$LocalDataType,$LocalFormat,$LocalSize,$LocalOwnerType,$LocalOwnerID);
					if ($Result == false)
					{
						$ErrorMessage = "Cannot create field";
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

					// Get archive ID and/or owner

					$ArchiveID = aib_get_with_default($FormData,"archive_code",-1);
					if ($ArchiveID == "NULL")
					{
						$ArchiveID = -1;
					}
	
					// Check for errors and save
	
					while($ErrorMessage == false)
					{
						$LocalTitle = aib_get_with_default($FormData,"field_title",false);
						$LocalDataType = aib_get_with_default($FormData,"field_data_type",false);
						$LocalFormat = aib_get_with_default($FormData,"field_format",false);
						$LocalSize = aib_get_with_default($FormData,"field_size",false);
						$FieldID = aib_get_with_default($FormData,"primary",false);
						if ($FieldID === false)
						{
							$ErrorMessage = "Missing primary";
							break;
						}

						$UpdateInfo = array();
						if ($LocalTitle != false)
						{
							$UpdateInfo["title"] = urlencode($LocalTitle);
						}

						if ($LocalSize !== false)
						{
							$UpdateInfo["size"] = $LocalSize;
						}

						if ($LocalFormat != false)
						{
							$UpdateInfo["format"] = $LocalFormat;
						}

						$Result = ftree_field_modify_field($GLOBALS["aib_db"],$FieldID,$UpdateInfo);
						$StatusMessage = "Field \"<i>$LocalTitle</i>\" updated successfully";
						break;
					}

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
//					$OutBuffer[] = aib_gen_form_header("pageform","/field_form.php",false,"validate_form");
//					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
//					if ($UserType != FTREE_USER_TYPE_ROOT)
//					{
//						$OutBuffer[] = "<input type='hidden' name='archive_code' value='$CurrentArchive'>";
//					}
//
//					$OutBuffer[] = "<input type='hidden' name='field_owner_type' value='$FieldOwnerType'>";
//					$OutBuffer[] = "<input type='hidden' name='field_owner_id' value='$FieldOwnerID'>";
//					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
//					$OutBuffer[] = "<table class='aib-input-set'>";
//					break;

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

					ftree_field_delete_field($GLOBALS["aib_db"],$Primary);
					$StatusMessage = "Field successfully deleted";
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

					$FieldRecord = ftree_field_get_field($GLOBALS["aib_db"],$FormData["primary"]);
					if ($FieldRecord == false)
					{
						$ErrorMessage = "Cannot retrieve field information";
						aib_close_db();
						break;
					}

					$LocalTitle = aib_get_with_default($FieldRecord,"field_title",false);
					$LocalDataType = aib_get_with_default($FieldRecord,"field_data_type",false);
					$LocalFormat = aib_get_with_default($FieldRecord,"field_format",false);
					$LocalSize = aib_get_with_default($FieldRecord,"field_size",false);
					$FieldID = aib_get_with_default($FieldRecord,"primary",false);
					$LocalOwnerID = aib_get_with_default($FieldRecord,"field_owner_id",-1);
					$LocalOwnerType = aib_get_with_default($FieldRecord,"field_owner_type",FTREE_OWNER_TYPE_SYSTEM);

					if ($LocalTitle == false)
					{
						$LocalTitle = "";
					}

					$FieldDef["field_title"]["value"] = urldecode($LocalTitle);
					$FieldDef["field_data_type"]["value"] = $LocalDataType;
					$FieldDef["field_format"]["value"] = $LocalFormat;
					$FieldDef["field_size"]["value"] = $LocalSize;
					$OutBuffer[] = aib_gen_form_header("pageform","/field_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='field_owner_id' value='$LocalOwnerID'>";
					$OutBuffer[] = "<input type='hidden' name='field_owner_type' value='$LocalOwnerType'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value='$NavString'>";
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
	
					$FieldRecord = ftree_field_get_field($GLOBALS["aib_db"],$FormData["primary"]);
					if ($FieldRecord == false)
					{
						$ErrorMessage = "Cannot retrieve field information";
						aib_close_db();
						break;
					}

					$FieldDef["field_title"]["value"] = urldecode($FieldRecord["field_title"]);
					$FieldDef["field_data_type"]["value"] = $FieldRecord["field_data_type"];
					$FieldDef["field_format"]["value"] = $FieldRecord["field_format"];
					$FieldDef["field_size"]["value"] = $FieldRecord["field_size"];
					$OutBuffer[] = aib_gen_form_header("pageform","/field_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value='$NavString'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='do_del'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

				// Add new item

				case "add":
				case false:
				default:
					$FieldDef["field_title"]["value"] = "";
					$FieldDef["field_data_type"]["value"] = "";
					$FieldDef["field_format"]["value"] = "";
					$FieldDef["field_size"]["value"] = "64";
					$OutBuffer[] = aib_gen_form_header("pageform","/field_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value='$NavString'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
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
				if ($NavTargetInfo["target"] != "fields.php")
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
				case "do_del":
					if ($ErrorMessage != false)
					{
						break;
					}

					if (isset($FormData["archive_code"]) == true)
					{
						$CurrentArchive = $FormData["archive_code"];
						$TargetSpec[] = array(
							"url" => "/fields.php?archive_code=$CurrentArchive",
							"title" => "Return To Field List",
							"fields" => array(
								"aibnav" => $NavString,
								),
							);
					}
					else
					{
						$TargetSpec[] = array(
							"url" => "/fields.php",
							"title" => "Return To Field List",
							"fields" => array(
								"aibnav" => $NavString,
								),
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
							"url" => "/field_form.php",
							"title" => "Add Another Field",
							"fields" => array(
								"opcode" => "add",
								"aibnav" => $NavString,
								),
							);
					$TargetSpec[] = array(
							"url" => "/fields.php",
							"title" => "Return To Field List",
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
					$OutBuffer[] = aib_draw_display_field(urldecode($FieldDef["field_title"]));
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_form_submit("Delete Field","link|/fields.php|Go Back To List");
					break;

				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}


					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Determine field owner.  It's either an archive, an item, or the user.  If the owner_id isn't specified, then
					// the owner is the current user if the current user isn't an administrator or sub-administrator.  If the current
					// user is a sub-administrator, the choices are the archive for the sub-administrator, the current item (if the 
					// item_id field is present), or the user.  If the current user is an administrator, the choices are the user,
					// the current item (if the item_id field is present), the archive group, or one of the archives.  If the user
					// is the super-admin, then all archive groups and archives are available.

					// First, determine if the owner_id and item_id fields are set

					$LocalOwnerID = aib_get_with_default($FormData,"owner_id",false);
					$LocalItemID = aib_get_with_default($FormData,"item_id",false);
					$LocalOwnerType = false;

					// Get archive group list and archive list if the owner_id or item_id fields are blank, based on the user type.

					$ArchiveGroupList = array();
					$ArchiveList = array();
					$OwnerList = array();
					switch($UserType)
					{
						case AIB_USER_TYPE_ROOT:
							$OwnerList = array("trad:-1" => "Traditional Field", "rec:-1" => "Recommended Field");
							if ($LocalOwnerID !== false && $LocalOwnerID != $UserID)
							{
								$LocalOwnerRecord = ftree_get_user($GLOBALS["aib_db"],$LocalOwnerID);
								if ($LocalOwnerRecord != false)
								{
									$OwnerList["us:$LocalOwnerID"] = "User: ".$LocalOwnerRecord["user_title"]. "(".$LocalOwnerRecord["user_login"].")";
								}
							}

							if ($LocalItemID !== false)
							{
								$LocalItemRecord = ftree_get($GLOBALS["aib_db"],$LocalItemID);
								if ($LocalItemRecord != false)
								{
									$OwnerList["it:$LocalItemID"] = "Record: ".$LocalItemRecord["item_title"];
								}
							}

							$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
							if ($ArchiveGroupList != false)
							{
								foreach($ArchiveGroupList as $ArchiveGroupRecord)
								{
									$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
									$ArchiveGroupTitle = $ArchiveGroupRecord["item_title"];
									$ArchiveGroupCode = $ArchiveGroupRecord["_archive_group_code"];
									$OwnerList["ag:".$ArchiveGroupID] = $ArchiveGroupTitle." ($ArchiveGroupCode)";
									$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$ArchiveGroupID);
									foreach($ArchiveList as $ArchiveRecord)
									{
										$ArchiveID = $ArchiveRecord["item_id"];
										$ArchiveTitle = $ArchiveRecord["item_title"];
										$OwnerList["ar:$ArchiveID"] = "&#9492; $ArchiveTitle";
									}
								}
							}

							break;

						case AIB_USER_TYPE_ADMIN:
							$OwnerList = array();
							if ($LocalOwnerID !== false)
							{
								$LocalOwnerRecord = ftree_get_user($GLOBALS["aib_db"],$LocalOwnerID);
								if ($LocalOwnerRecord != false)
								{
									$OwnerList["us:$LocalOwnerID"] = "User: ".$LocalOwnerRecord["user_title"]. "(".$LocalOwnerRecord["user_login"].")";
								}
							}

							if ($LocalItemID !== false)
							{
								$LocalItemRecord = ftree_get($GLOBALS["aib_db"],$LocalItemID);
								if ($LocalItemRecord != false)
								{
									$OwnerList["it:$LocalItemID"] = "Record: ".$LocalItemRecord["item_title"];
								}
							}

							$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
							if ($ArchiveGroupList != false)
							{
								foreach($ArchiveGroupList as $ArchiveGroupRecord)
								{
									$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
									$ArchiveGroupTitle = $ArchiveGroupRecord["item_title"];
									$ArchiveGroupCode = $ArchiveGroupRecord["_archive_group_code"];
									$OwnerList["ag:".$ArchiveGroupID] = $ArchiveGroupTitle." ($ArchiveGroupCode)";
									$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$ArchiveGroupID);
									foreach($ArchiveList as $ArchiveRecord)
									{
										$ArchiveID = $ArchiveRecord["item_id"];
										$ArchiveTitle = $ArchiveRecord["item_title"];
										$OwnerList["ar:$ArchiveID"] = "&#9492; $ArchiveTitle";
									}
								}
							}

							break;

						case AIB_USER_TYPE_SUBADMIN:
							$OwnerList = array();
							if ($LocalOwnerID !== false)
							{
								$LocalOwnerRecord = ftree_get_user($GLOBALS["aib_db"],$LocalOwnerID);
								if ($LocalOwnerRecord != false)
								{
									$OwnerList["us:$LocalOwnerID"] = "User: ".$LocalOwnerRecord["user_title"]. "(".$LocalOwnerRecord["user_login"].")";
								}
							}

							if ($LocalItemID !== false)
							{
								$LocalItemRecord = ftree_get($GLOBALS["aib_db"],$LocalItemID);
								if ($LocalItemRecord != false)
								{
									$OwnerList["it:$LocalItemID"] = "Record: ".$LocalItemRecord["item_title"];
								}
							}

							$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
							if ($ArchiveGroupList != false)
							{
								foreach($ArchiveGroupList as $ArchiveGroupRecord)
								{
									$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
									$ArchiveGroupTitle = $ArchiveGroupRecord["item_title"];
									$ArchiveGroupCode = $ArchiveGroupRecord["_archive_group_code"];
									$OwnerList["ag:".$ArchiveGroupID] = $ArchiveGroupTitle." ($ArchiveGroupCode)";
									$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$ArchiveGroupID);
									foreach($ArchiveList as $ArchiveRecord)
									{
										$ArchiveID = $ArchiveRecord["item_id"];
										$ArchiveTitle = $ArchiveRecord["item_title"];
										$OwnerList["ar:$ArchiveID"] = "&#9492; $ArchiveTitle";
									}
								}
							}

							break;

						default:
							if ($LocalOwnerID !== false)
							{
								$OutBuffer[] = "<input type='hidden' name='field_owner_id' value='$LocalOwnerID'>";
								$OutBuffer[] = "<input type='hidden' name='field_owner_type' value='".FTREE_OWNER_TYPE_USER."'";
								break;
							}

							if ($LocalItemID !== false)
							{
								$OutBuffer[] = "<input type='hidden' name='field_owner_id' value='$LocalItemID'>";
								$OutBuffer[] = "<input type='hidden' name='field_owner_type' value='".FTREE_OWNER_TYPE_ITEM."'";
								break;
							}

							$OutBuffer[] = "<input type='hidden' name='field_owner_id' value='$UserID'>";
							$OutBuffer[] = "<input type='hidden' name='aibnav' value='$NavString'>";
							$OutBuffer[] = "<input type='hidden' name='field_owner_type' value='".FTREE_OWNER_TYPE_USER."'";
							break;
					}


					// Set up input field list

					$OutBuffer[] = "<table class='aib-input-set'>";
					if (count($OwnerList) > 0 && $OpCode == "add")
					{
						$FieldDef["field_owner_id"]["option_list"] = $OwnerList;
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_dropdown_field($FieldDef["field_owner_id"]);
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["field_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["field_data_type"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_textarea_field($FieldDef["field_format"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["field_size"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Add Field","Clear Form");
							break;
					}

					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";
					aib_close_db();
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

	// Other scripts:
	//
	// 1) Based on the field type selected, alter the field format description area text
	//

	print("
		<script>
			function init_field_data_type_change_handler()
			{
				\$('#field_data_type').change(function() {
					var CurrentValue = \$(this).val();
				
					if (CurrentValue == '".FTREE_FIELD_TYPE_TEXT."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_BIGTEXT."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_FLOAT."')
					{
						\$('#explain-box-field_format').html('You may enter the total digits followed by a comma and the number of decimals.  Example: 10,4');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_INTEGER."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_DECIMAL."')
					{
						\$('#explain-box-field_format').html('You may enter the total digits followed by a comma and the number of decimals.  Example: 10,4');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_DATE."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_TIME."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_TIMESTAMP."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_DATETIME."')
					{
						\$('#explain-box-field_format').html('No special formatting options required');
						return;
					}

					if (CurrentValue == '".FTREE_FIELD_TYPE_DROPDOWN."')
					{
						\$('#explain-box-field_format').html('Enter option values as<br> <span style=\"font-family:Courier;\">value=description</span>, one per line.<br><br>  <u>Example</u>:<br><span style=\"font-family:Courier;\">photo=Photograph<br>box=Box<br>tintype=Tin Type Photo</span>');
						return;
					}

					\$('#explain-box-field_format').html('');
					return;
				});
			}

		</script>
		");
				
?>

<?php

include('template/common_end_of_page.php');
	exit(0);
?>
