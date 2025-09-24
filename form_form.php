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
	$UserType = $UserRecord["user_type"];
	$UserID = $UserRecord["user_id"];

	// Get form data and opcode

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();
	$OpCode = aib_get_with_default($FormData,"opcode",false);

	// Set owner type based on user profile and any additional fields

	$SourcePage = aib_get_with_default($FormData,"source",false);
	$SourcePageKey = aib_get_with_default($FormData,"source_key",false);
	$OwnerTypeRequest = aib_get_with_default($FormData,"owner_type","archive");

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
	$FieldOwnerID = aib_get_with_default($FormData,"field_owner_id","-1");


	// Set up display data array

	$DisplayData = array(
		"page_title" => $UserName.": MANAGE FORMS",
		"popup_list" => array(
			"archive_code_help_popup" => array("title" => "Help For: Archive Code",
							"heading" => "Help For: Archive Code",
							"text" => "Select the archive to which the administrator will be assigned",
						),
			"admin_login_help_popup" => array("title" => "Help For: Field Login",
							"heading" => "Help For: Field Login",
							"text" => "Enter the login ID of the administrator",
						),
			"admin_name_help_popup" => array("title" => "Help For: Form Name",
							"heading" => "Help For: Form Name",
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
			$DisplayData["page_title"] = "$UserName: EDIT FORMS";
			break;

		case "del":
			$DisplayData["page_title"] = "$UserName: DELETE FORMS";
			break;

		default:
			$DisplayData["page_title"] = "$UserName: ADD FORM";
			break;
	}

	$DisplayData["head_script"] = 
		"

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




	// Define fields

	$FieldDef = array(
		"archive_code" => array(
			"title" => "Archive:", "type" => "dropdown", "display_width" => "10",
			"field_name" => "archive_code", "field_id" => "archive_code",
			"desc" => "", "help_function_name" => "archive_code_help_popup"),

		"form_owner_id" => array(
			"title" => "Archive:", "type" => "dropdown", "display_width" => "10",
			"field_name" => "form_owner_id", "field_id" => "form_owner_id",
			"desc" => "", "help_function_name" => "field_owner_help_popup"),

		"form_title" => array(
			"title" => "Form Name:", "type" => "text", "display_width" => "25",
			"field_name" => "form_title", "field_id" => "form_title",
			"desc" => "", "help_function_name" => "form_title_help_popup"),

		"form_source_fields" => array(
			"title" => "Fields Available:", "type" => "dropdown", "display_width" => "25", "rows" => "10",
			"field_name" => "form_source_fields", "field_id" => "form_source_fields",
			"desc" => "", "help_function_name" => "form_title_help_popup"),

		"form_select_buttons" => array(
			"title" => "", "type" => "custom", "display_width" => "25",
			"field_name" => "form_select_buttons", "field_id" => "form_select_buttons",
			"desc" => "Use the buttons to add or remove fields from the form", "help_function_name" => "form_title_help_popup"),

		"form_dest_fields" => array(
			"title" => "Fields On Form:", "type" => "dropdown", "display_width" => "25", "rows" => "10",
			"field_name" => "form_dest_fields", "field_id" => "form_dest_fields",
			"desc" => "", "help_function_name" => "form_title_help_popup"),

	);


	// Define field validations

	$GlobalFormOwnerID = false;
	$GlobalFormOwnerType = false;
	$ValidationDef = false;
	$FieldsToUseList = array();
	switch($OpCode)
	{
		case "edit":
		case "add":
			$ValidationDef = array(
				"form_title" => array("type" => "text", "field_id" => "form_title",
						"conditions" => array(
							"notblank" => array("error_message" => "You must enter a title for the form"),
							),
						),

				"_form" => array("function" => "generate_form_field_list"),
				);

				break;
	}

	$OutBuffer = array();
	$NavID = aib_get_nav_value("primary");
	$NavTargetInfo = aib_get_nav_target();
	if ($NavTargetInfo != false && preg_match("/save/",$OpCode) == false)
	{
		if ($NavTargetInfo["target"] != "forms.php")
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
				// Save new archive

				case "save":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
					}

					// Get owner; default to current user

					$FormOwner = aib_get_with_default($FormData,"form_owner_id","us:$UserID");
					$FormOwnerID = false;
					$FormOwnerType = "NULL";
					$Segs = explode(":",$FormOwner);
					if (count($Segs) < 2)
					{
						$FormOwnerID = "us:$UserID";
						$FormOwnerType = FTREE_OWNER_TYPE_USER;
					}
					else
					{
						$FormOwnerType = $Segs[0];
						$FormOwnerID = $Segs[1];
						switch($FormOwnerType)
						{
							case AIB_ITEM_TYPE_USER:
								if ($FormOwnerID == AIB_SUPERADMIN)
								{
									$FormOwnerType = FTREE_OWNER_TYPE_SYSTEM;
									break;
								}

								$FormOwnerType = FTREE_OWNER_TYPE_USER;
								break;

							case AIB_ITEM_TYPE_TRADITIONAL:
								$FormOwnerType = FTREE_OWNER_TYPE_SYSTEM;
								break;

							case AIB_ITEM_TYPE_GROUP:
								$FormOwnerType = FTREE_OWNER_TYPE_GROUP;
								break;

							case AIB_ITEM_TYPE_ARCHIVE_GROUP:
							case AIB_ITEM_TYPE_ARCHIVE:
							case AIB_ITEM_TYPE_COLLECTION:
							case AIB_ITEM_TYPE_SUBGROUP:
								$FormOwnerType = FTREE_OWNER_TYPE_ITEM;
								break;

							default:
								break;
						}
					}

					// Check for errors and save
	
					while($ErrorMessage == false)
					{
						// Make sure the owner exists

						switch($FormOwnerType)
						{
							case FTREE_OWNER_TYPE_SYSTEM:
							case FTREE_OWNER_TYPE_TRADITIONAL:
								break;

							case FTREE_OWNER_TYPE_ITEM:
								if (ftree_get_item($GLOBALS["aib_db"],$FormOwnerID) == false)
								{
									$ErrorMessage = "Cannot find form owner";
									break;
								}

								break;

							case FTREE_OWNER_TYPE_USER:
								if (ftree_get_user($GLOBALS["aib_db"],$FormOwnerID) == false)
								{
									$ErrorMessage = "Cannot find form owner user";
									break;
								}

								break;

							default:
								$ErrorMessage = "Invalid owner type: $FormOwnerType";
								break;
						}

						if ($ErrorMessage != false)
						{
							break;
						}

						$LocalTitle = aib_get_with_default($FormData,"form_title",false);
						$LocalFieldList = explode(",",aib_get_with_default($FormData,"form_field_list",""));
						if ($LocalTitle == false)
						{
							$ErrorMessage = "Missing title";
							break;
						}

						$FormID = ftree_field_create_form($GLOBALS["aib_db"],$LocalTitle,$FormOwnerID,$FormOwnerType);
						if ($FormID == false)
						{
							$ErrorMessage = "Cannot create form $LocalTitle";
							break;
						}

						// If there are fields defined, add to form

						if (count($LocalFieldList) > 0)
						{
							foreach($LocalFieldList as $FormFieldID)
							{
								if ($FormFieldID == "" || $FormFieldID == "NULL" || $FormFieldID == "null")
								{
									continue;
								}

								ftree_field_add_field_to_form($GLOBALS["aib_db"],$FormID,$FormFieldID,false,"");
							}
						}

						$StatusMessage = "Form \"<i>$LocalTitle</i>\" created successfully";
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
						break;
					}

					$FormRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormData["primary"]);
					if ($FormRecord == false)
					{
						$ErrorMessage = "Cannot load form";
						break;
					}

					$OriginalFieldList = ftree_field_get_form_fields($GLOBALS["aib_db"],$FormData["primary"]);
					if ($OriginalFieldList == false)
					{
						$OriginalFieldList = array();
					}

					$LocalTitle = aib_get_with_default($FormData,"form_title",false);
					$LocalFieldList = explode(",",aib_get_with_default($FormData,"form_field_list",""));
					$OriginalMap = array();
					$NewMap = array();
					foreach($OriginalFieldList as $TempRecord)
					{
						$OriginalMap[$TempRecord["field_record"]["field_id"]] = true;
					}

					foreach($LocalFieldList as $LocalID)
					{
						$NewMap[$LocalID] = true;
					}

					$UpdateFieldsFlag = false;

					$ChangeInfo = array();
					if ($LocalTitle != $FormRecord["form_title"])
					{
						$ChangeInfo["name"] = $LocalTitle;
					}

					ftree_field_modify_form($GLOBALS["aib_db"],$FormData["primary"],$ChangeInfo);

					// Remove any fields that aren't part of the form any longer

					foreach($OriginalMap as $OriginalID => $LocalFlag)
					{
						if (isset($NewMap[$OriginalID]) == false)
						{
							ftree_field_del_form_field($GLOBALS["aib_db"],$FormData["primary"],$OriginalID);
						}
					}

					// Add new fields, if any

					foreach($NewMap as $NewID)
					{
						if (isset($OriginalMap[$NewID]) == false)
						{
							ftree_field_add_field_to_form($GLOBALS["aib_db"],$FormData["primary"],$NewID,false,"");
						}
					}

					$StatusMessage = "Form successfully modified";

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

					ftree_field_delete_form($GLOBALS["aib_db"],$Primary);
					$StatusMessage = "Form successfully deleted";
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

					$FormRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormData["primary"]);
					if ($FormRecord == false)
					{
						$ErrorMessage = "Cannot retrieve form information";
						aib_close_db();
						break;
					}



					$FieldsToUseList = array();
					$FormOwner = $FormRecord["form_owner"];
					$OwnerType = ftree_get_property($GLOBALS["aib_db"],$FormOwner,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
					$FormOwner = $OwnerType.":".$FormOwner;

					// Get the set of fields used

					$FieldDef["form_dest_fields"]["option_list"] = array();
					$TempList = ftree_field_get_form_fields($GLOBALS["aib_db"],$FormData["primary"]);
					foreach($TempList as $TempRecord)
					{
						$FieldRecord = $TempRecord["field_record"];
						$FieldsToUseList[] = $FieldRecord["field_id"];
						$FieldDef["form_dest_fields"]["option_list"][$FieldRecord["field_id"]] = urldecode($FieldRecord["field_title"]);
					}

					$LocalTitle = aib_get_with_default($FormRecord,"form_title",false);
					$FieldDef["form_title"]["value"] = $LocalTitle;
					$OutBuffer[] = aib_gen_form_header("pageform","/form_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='form_owner_id' id='form_owner_id' value='$FormOwner'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save_edit'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
					$OutBuffer[] = "<input type='hidden' name='form_field_list' id='form_field_list' value=''>";
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
	
					$FormRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormData["primary"]);
					if ($FormRecord == false)
					{
						$ErrorMessage = "Cannot retrieve form information";
						aib_close_db();
						break;
					}

					$FieldDef["form_title"]["value"] = $FormRecord["form_title"];
					$OutBuffer[] = aib_gen_form_header("pageform","/form_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
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
					$FieldDef["field_size"]["value"] = "";
					$OutBuffer[] = aib_gen_form_header("pageform","/form_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					$OutBuffer[] = "<input type='hidden' name='form_field_list' id='form_field_list' value=''>";
					$OutBuffer[] = "<input type='hidden' name='aibnav' value=\"$NavString\">";
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
			if ($NavTargetInfo["target"] != "forms.php")
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
						$TargetSpec[] = 
							array(
							"url" => "/forms.php",
							"title" => "Return To Form List",
							"fields" => array(
								"aibnav" => $NavString,
								),
						);
					}
					else
					{
						$TargetSpec[] = 
							array(
							"url" => "/forms.php",
							"title" => "Return To Form List",
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
							"url" => "/form_form.php",
							"title" => "Add Another Form",
							"fields" => array(
								"opcode" => "add",
								"aibnav" => $NavString,
								),
							);

					$TargetSpec[] = array(
							"url" => "/forms.php",
							"title" => "Return To Form List",
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
					$OutBuffer[] = aib_draw_display_field($FieldDef["form_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_form_submit("Delete Form","link|/forms.php|Go Back To List");
					break;

				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					// Set up input table

					// Based on source and user, show drop down with owner choices.  Forms can be owned by the
					// system, an archive group or an archive.

					$ShowOwnerList = false;
					$OwnerList = array("NULL" => " --- SELECT --- ");
					$LocalOwnerID = $UserID;
					$OutBuffer[] = "<table class='aib-input-set'>";
					$LocalItemID = aib_get_with_default($FormData,"item_id",false);
					aib_open_db();
					if ($OpCode == "add")
					{
						switch($UserType)
						{
							case AIB_USER_TYPE_ROOT:
								$OwnerList = array(
									"NULL" => " --- SELECT --- ",
									"trad:-1" => "Traditional Form", "us:".AIB_SUPERUSER => "Super User"
									);
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
								$OwnerList = array(
									"NULL" => " --- SELECT --- ",
									"us:$UserID" => "User: ".$UserRecord["user_title"]." (".$UserRecord["user_login"].")"
									);
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
								$OwnerList = array(
									"NULL" => " --- SELECT --- ",
									"us:$UserID" => "User: ".$UserRecord["user_title"]." (".$UserRecord["user_login"].")"
									);
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
								$OutBuffer[] = "<input type='hidden' name='form_owner_id' value='us:$UserID'>";
								$OutBuffer[] = "<input type='hidden' name='form_owner_type' value='".FTREE_OWNER_TYPE_USER."'";
								break;
						}

						if (count($OwnerList) > 0)
						{
							$FieldDef["form_owner_id"]["option_list"] = $OwnerList;
							$OutBuffer[] = aib_draw_dropdown_field($FieldDef["form_owner_id"]);
							$OutBuffer[] = aib_draw_input_row_separator();
						}
					}
							
					aib_close_db();

					if ($OpCode == "add")
					{
						// Get list of fields and load into "fields available" list

						aib_open_db();
						if ($UserID == AIB_SUPERUSER)
						{
							$LocalFieldList = ftree_list_fields($GLOBALS["aib_db"],false);
						}
						else
						{
							$LocalFieldList = ftree_list_fields($GLOBALS["aib_db"],$UserID);
						}
	
						aib_close_db();
						$DropList = array();
						foreach($LocalFieldList as $LocalRecord)
						{
							$DropList[$LocalRecord["field_id"]] = $LocalRecord["field_title"];
						}
	
						$FieldDef["form_source_fields"]["option_list"] = $DropList;
						$FieldDef["form_dest_fields"]["option_list"] = array("NULL" => " -- NO FIELDS SELECTED -- ");
					}

					if ($OpCode == "edit")
					{
						// Get list of fields and load into "fields available" list

						aib_open_db();

						$FormRecord = ftree_field_get_form($GLOBALS["aib_db"],$FormData["primary"]);
						if ($FormRecord == false)
						{
							$ErrorMessage = "Cannot retrieve form information";
							aib_close_db();
							break;
						}

						$FormOwner = $FormRecord["form_owner"];
						$OwnerType = ftree_get_property($GLOBALS["aib_db"],$FormOwner,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
						$FormOwner = $OwnerType.":".$FormOwner;
						if ($UserID == AIB_SUPERUSER)
						{
							$LocalFieldList = ftree_list_fields($GLOBALS["aib_db"],false);
						}
						else
						{
							$LocalFieldList = array();
							$LocalMap = array();
							$TempList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,false);
							foreach($TempList as $TempRecord)
							{
								$FieldID = $TempRecord["field_id"];
								if (isset($LocalMap[$FieldID]) == false)
								{
									$LocalMap[$FieldID] = true;
									$LocalFieldList[] = $TempRecord;
								}
							}

							$TempList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_RECOMMENDED,false);
							foreach($TempList as $TempRecord)
							{
								$FieldID = $TempRecord["field_id"];
								if (isset($LocalMap[$FieldID]) == false)
								{
									$LocalMap[$FieldID] = true;
									$LocalFieldList[] = $TempRecord;
								}
							}

							$ArchiveGroupList = aib_get_archive_group_list($GLOBALS["aib_db"],$UserID);
							if ($ArchiveGroupList != false)
							{
								foreach($ArchiveGroupList as $ArchiveGroupRecord)
								{
									$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
									$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$ArchiveGroupID);
									foreach($ArchiveList as $ArchiveRecord)
									{
										$ArchiveID = $ArchiveRecord["item_id"];
										$TempList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveID);
										foreach($TempList as $TempRecord)
										{
											$FieldID = $TempRecord["field_id"];
											if (isset($LocalMap[$FieldID]) == false)
											{
												$LocalMap[$FieldID] = true;
												$LocalFieldList[] = $TempRecord;
											}
										}
									}
								}
							}
						}
	
						$OriginalFieldList = ftree_field_get_form_fields($GLOBALS["aib_db"],$FormData["primary"]);
						if ($OriginalFieldList == false)
						{
							$OriginalFieldList = array();
						}

						$OriginalMap = array();
						$OriginalDropList = array();
						foreach($OriginalFieldList as $FieldRecord)
						{
							$OriginalMap[$FieldRecord["field_record"]["field_id"]] = true;
							$OriginalDropList[$FieldRecord["field_record"]["field_id"]] = 
								urldecode($FieldRecord["field_record"]["field_title"]);
						}

						aib_close_db();
						$DropList = array();
						foreach($LocalFieldList as $LocalRecord)
						{
							if (isset($OriginalMap[$LocalRecord["field_id"]]) == false)
							{
								$DropList[$LocalRecord["field_id"]] = $LocalRecord["field_title"];
							}
						}
	
						if (count($DropList) < 1)
						{
							$FieldDef["form_dest_fields"]["option_list"] = array("NULL" => " -- NO FIELDS SELECTED -- ");
						}
						else
						{
							$FieldDef["form_source_fields"]["option_list"] = $DropList;
						}

					}

					// Set up buttons to add or remove fields

					$FieldDef["form_select_buttons"]["fielddata"] = "
						<button type='button' id='remove_field_button' class='field-move-button' onclick='remove_field(event,this);'> &#8657; Remove Field &#8657;</button> &nbsp; &nbsp; <button type='button' id='add_field_button' class='field-move-button' onclick='add_field(event,this);'>&#8659; Add Field &#8659;</button>
						";

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["form_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_listbox_field($FieldDef["form_source_fields"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_custom_field($FieldDef["form_select_buttons"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_listbox_field($FieldDef["form_dest_fields"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Add Form","Clear Screen Form");
							break;
					}

					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";
					break;
			}

			?>
		</td>
	</tr>

<?php
include('template/common_header.php');

	print("<tr><td colspan='99'>");
	print(join("\n",$OutBuffer));

	// Include the footer

include('template/common_footer.php');

	// Generate validation functions

	print(aib_gen_field_validations("pageform","validate_form",$ValidationDef));

	// Other scripts:
	//
	// 1) Based on the field type selected, alter the field format description area text
	//

	$GetFieldsCode = bin2hex("getfields");
	$EncodedUserID = bin2hex($UserID);
	print("
		<script>

		function add_field(Event,RefObj)
		{
			var SourceFields;
			var DestFields;
			var Selected;
			var SelectedText;

			SourceFields = \$('#form_source_fields');
			DestFields = \$('#form_dest_fields');

			// Get selected field, if any.  If nothing selected, then alert box.

			Event.stopPropagation();

			Selected = SourceFields.val();
			if (Selected == null)
			{
				alert(\"You must select a field from the 'Fields Available' list.\");
				return;
			}

			SelectedText = SourceFields.find('option:selected').text();

			// Remove from source list

			\$(\"#form_source_fields option[value='\" + Selected + \"']\").remove();

			// Add to dest list.  First, remove any existing 'null' entry

			\$(\"#form_dest_fields option[value='NULL']\").remove();
			DestFields.append(\"<option value='\" + Selected + \"'>\" + SelectedText + \"</option>\");
		}

		function remove_field(Event,RefObj)
		{
			var SourceFields;
			var DestFields;
			var Selected;
			var SelectedText;
			var ListSize;

			SourceFields = \$('#form_source_fields');
			DestFields = \$('#form_dest_fields');

			Event.stopPropagation();

			// Get selected option value and text

			// Get selected field, if any.  If nothing selected, then alert box.

			Event.stopPropagation();

			Selected = DestFields.val();
			if (Selected == null)
			{
				alert(\"You must select a field from the 'Fields On Form' list.\");
				return;
			}

			SelectedText = DestFields.find('option:selected').text();
			if (Selected == 'NULL')
			{
				return;
			}

			// Remove from source list

			\$(\"#form_dest_fields option[value='\" + Selected + \"']\").remove();

			// Add to source list.  First, remove any existing 'null' entry

			\$(\"#form_source_fields option[value='NULL']\").remove();
			SourceFields.append(\"<option value='\" + Selected + \"'>\" + SelectedText + \"</option>\");

			// If there aren't any fields in the dest, add a NULL option

			ListSize = DestFields.children('option').length;
			if (ListSize < 1)
			{
				DestFields.append(\"<option value='NULL'> -- NO FIELDS SELECTED -- </option>\");
			}

		}
");

	if ($OpCode == "add")
	{
		print("
		var FieldsToUseList = [];
		");
	}
	else
	{
		if (count($FieldsToUseList) > 0)
		{
			print("\nvar FieldsToUseList = [");
			print(join(",",$FieldsToUseList));
			print("];");
		}
		else
		{
			print("
		var FieldsToUseList = [];
			");
		}
	}

	print("

		// Get all selected options and add to hidden field

		function generate_form_field_list()
		{
			var LocalString;

			// Get the list of all option values in the destination list

			var Options = \$('#form_dest_fields')[0].options;
			FieldsToUseList = \$.map(Options,function(Element) {
				return(Element.value);
				});

			// Create string if there are any elements

			if (FieldsToUseList.length > 0)
			{
				LocalString = FieldsToUseList.join(',');

				// Set hidden field value

				\$('#form_field_list').attr('value',LocalString);
			}
		}

		// Trigger for change of owner ID.  First is function to send request for data,
		// then load and error functions.

		function load_field_list(Info)
		{
			if (Info['status'] != 'OK')
			{
				return;
			}

			FieldsToUseList = [];
			\$('#input-box-form_source_fields').html(Info['info']['html']);
		}

		function error_output(ReqObj,ErrorStatus,ErrorText)
		{
			alert('Request error: ' + ErrorStatus + '; ' + ErrorText);
		}

		function update_field_list()
		{
			var OwnerID;
			var QueryParam = {};

			// Get owner ID

			OwnerID = \$('#form_owner_id').val();

			QueryParam['o'] = '$GetFieldsCode';
			QueryParam['i'] = '$EncodedUserID';
			QueryParam['pn'] = 1;
			QueryParam['checks'] = '';
			QueryParam['checks_title'] = '';
			QueryParam['pic'] = 1;
			QueryParam['lop'] = 'get';
			QueryParam['id'] = 'aibformdef';
			QueryParam['key'] = OwnerID;
			aib_ajax_request('/services/airrecord.php',QueryParam,load_field_list,error_output);
		}

		// Connect change to load fields

		\$('#form_owner_id').change(function(Event) {
			update_field_list();
			});

		// Initial call to load field list

		update_field_list();
		</script>
		");
				
?>

<?php

include('template/common_end_of_page.php');
	exit(0);
?>
