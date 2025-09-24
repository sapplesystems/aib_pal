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
	$SourcePage = aib_get_with_default($FormData,"src",false);
	$SourceKey = aib_get_with_default($FormData,"srckey","");
	$SourceSearchValue = aib_get_with_default($FormData,"searchval","");
	$SourceMode = aib_get_with_default($FormData,"srcmode","");
	$SourcePageNumber = aib_get_with_default($FormData,"srcpn",1);

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: ADD COLLECTION",
		"popup_list" => array(
			"archive_code_help_popup" => array("title" => "Help For: Archive Code",
							"heading" => "Help For: Archive Code",
							"text" => "Select the archive to contain the new collection",
						),
			"collection_title_help_popup" => array("title" => "Help For: Collection Name",
							"heading" => "Help For: Collection Name",
							"text" => "Enter the name of the collection",
						),
			"collection_visible_help_popup" => array("title" => "Help For: Collection Visible",
							"heading" => "Help For: Collection Visible",
							"text" => "Select \\'Yes\\' if the collection is to be visible to the public.",
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
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: EDIT COLLECTION";
			break;

		case "del":
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: DELETE COLLECTION";
			break;

		default:
			$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR: ADD COLLECTION";
			break;
	}

// Include menu data

include("template/top_menu_data.php");

	// If this is a root user, add some items to the base menus

	$UserRecord = $UserInfo[1];
	if ($UserRecord["user_type"] == FTREE_USER_TYPE_ROOT)
	{
include("template/top_menu_admin_data.php");
		$DisplayData["current_menu"] = "Add Archive";
	}



	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"archive_code" => array(
			"title" => "Archive:", "type" => "dropdown", "display_width" => "10",
			"field_name" => "archive_code", "field_id" => "archive_code",
			"desc" => "", "help_function_name" => "archive_code_help_popup"),
		"collection_title" => array(
			"title" => "Collection Name:", "type" => "text", "display_width" => "64",
			"field_name" => "collection_title", "field_id" => "collection_title",
			"desc" => "", "help_function_name" => "collection_title_help_popup"),
		"collection_visible" => array("title" => "Visible To Public?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "collection_visible", "field_id" => "collection_visible",
			"desc" => "If 'Yes', then the collection can be seen by public users.", "help_function_name" => "collection_visible_help_popup"),
	);

	$FieldDef["collection_visible"]["option_list"] = array("Y" => "Yes", "N" => "No");
	$FieldDef["collection_visible"]["value"] = "Y";

	// Define field validations

	$ValidationDef = array(
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

	// Get the archive code from the form.  If not present, assume that we are either editing/saving/deleting/etc. and
	// get the archive code from the collection record (the archive is the parent of the collection).

	$ArchiveCode = aib_get_with_default($FormData,"archive_code",false);
	if ($ArchiveCode == false)
	{
		$Primary = aib_get_with_default($FormData,"primary",false);
		if ($Primary !== false)
		{
			aib_open_db();
			$PrimaryRecord = ftree_get_item($GLOBALS["aib_db"],$Primary);
			aib_close_db();
			if ($PrimaryRecord !== false)
			{
				$ArchiveCode = $PrimaryRecord["item_parent"];
			}
		}
	}

	aib_open_db();
	$TempNavRecord = ftree_get_item($GLOBALS["aib_db"],$ArchiveCode);
	$TempType = ftree_get_property($GLOBALS["aib_db"],$TempNavRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$TempParent = $TempNavRecord["item_id"];
	$RootFolder = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
	aib_close_db();
	$LocalSourceKey = aib_get_with_default($FormData,"srckey","");
	$LocalSearchValue = aib_get_with_default($FormData,"searchval","");
	$LocalSourceMode = aib_get_with_default($FormData,"srcmode","");
	$LocalSourcePageNumber = aib_get_with_default($FormData,"srcpn","");
	$TargetFields = join("&",array("src=collectionform","opcode=list","parent=$TempParent","srckey=$LocalSourceKey","searchval=$LocalSearchValue","srcmode=$LocalSourceMode","srcpn=$LocalSourcePageNumber"));
	$UpLink = "/records.php?$TargetFields";
	switch($TempType)
	{
		case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			$UpTitle = "Return To Organization: <span class='uplink-item-title'>".urldecode($TempNavRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_ARCHIVE:
			$UpTitle = "Return To Archive: <span class='uplink-item-title'>".urldecode($TempNavRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_COLLECTION:
			$UpTitle = "Return To Collection: <span class='uplink-item-title'>".urldecode($TempNavRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_SUBGROUP:
			$UpTitle = "Return To Sub-Group: <span class='uplink-item-title'>".urldecode($TempNavRecord["item_title"])."</span>";
			break;

		case AIB_ITEM_TYPE_RECORD:
			$UpTitle = "Return To Record: <span class='uplink-item-title'>".urldecode($TempNavRecord["item_title"])."</span>";
			break;

		default:
			$UpLink = false;
			break;
	}

	$OutBuffer = array();
	if ($UpLink != false)
	{
		$OutBuffer[] = "<tr><td align='left' valign='top'>";
		$OutBuffer[] = "<div class='browse-uplink-div'><a href='$UpLink' class='browse-uplink-link'>$UpTitle</a></div><br>";
		$OutBuffer[] = "</td></tr>";
	}


	// Field area

?>
	<tr>
		<td align='left' valign='top'>
			<?php
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
	
					// Check for errors and save
					// Check for the archive selected.  If not present, error.

					if ($ArchiveCode === false)
					{
						$ErrorMessage = "Missing archive code";
						break;
					}

					// Check for duplicate in archive folder for collection title

					$CollectionTitle = aib_get_with_default($FormData,"collection_title",false);
					if ($CollectionTitle === false)
					{
						$ErrorMessage = "Missing collection name";
						break;
					}

					$TempDef = ftree_get_child_object($GLOBALS["aib_db"],$ArchiveID,FTREE_OBJECT_TYPE_FOLDER,$CollectionTitle);
					if ($TempDef != false)
					{
						$ErrorMessage = "Collection title already used";
						break;
					}

					// Save new folder (collection).  Use drop-down for "visible" to set world permissions on item.

					$CollectionVisible = aib_get_with_default($FormData,"collection_visible",false);
					if ($CollectionVisible == "Y")
					{
						$WorldPermissions = "R";
					}
					else
					{
						$WorldPermissions = "";
					}

					// Create collection

					$FolderInfo = array("parent" => $ArchiveCode, 
						"title" => $CollectionTitle,
						"user_id" => FTREE_USER_SUPERADMIN,
						"group_id" => FTREE_GROUP_ROOT,
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

					$NewID = $FolderResult[1];

					// Create property which indicates the type of folder

					ftree_set_property($GLOBALS["aib_db"],$NewID,AIB_FOLDER_PROPERTY_FOLDER_TYPE,AIB_ITEM_TYPE_COLLECTION);
					$StatusMessage = "Collection \"<i>$CollectionTitle</i>\" created successfully";

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
					$FieldDef["archive_code"]["value"] = aib_get_with_default($FormData,"archive_code","");
					$FieldDef["archive_title"]["value"] = aib_get_with_default($FormData,"collection_title","");
					$OutBuffer[] = aib_gen_form_header("pageform","/collection_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
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
					if ($Primary === false)
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

					// Check for duplicate collection name in the archive.

					$UpdatedTitle = aib_get_with_default($FormData,"collection_title","");
					$TestEntry = ftree_get_child_object($GLOBALS["aib_db"],$ArchiveCode,FTREE_OBJECT_TYPE_FOLDER,$UpdatedTitle);
					if ($TestEntry != false)
					{
						if ($TestEntry["item_id"] != $Primary)
						{
							aib_close_db();
							$ErrorMessage = "Collection title is already used.";
							break;
						}
					}

					// Update

					ftree_rename($GLOBALS["aib_db"],$Primary,$UpdatedTitle);
					aib_close_db();
					$StatusMessage = "Collection successfully updated";
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

					// Delete tree item

					ftree_delete($GLOBALS["aib_db"],$Primary,true);

					// Delete all properties associated with the tree item

					ftree_delete_property($GLOBALS["aib_db"],$Primary,false);
					$StatusMessage = "Collection successfully deleted";
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

					// Set field values using primary to get collection

					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$FormData["primary"]);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve collection definition";
						aib_close_db();
						break;
					}
	
					$ParentDef = ftree_get_item($GLOBALS["aib_db"],$ItemDef["item_parent"]);
					$FieldDef["archive_code"]["value"] = $ArchiveCode;
					$FieldDef["collection_title"]["value"] = aib_get_with_default($ItemDef,"item_title","");
					$WorldPermissions = $ItemDef["world_perm"];
					if (preg_match("/[R]/",$WorldPermissions) != false)
					{
						$FieldDef["collection_visible"]["value"] = "Y";
					}
					else
					{
						$FieldDef["collection_visible"]["value"] = "N";
					}

					$OutBuffer[] = aib_gen_form_header("pageform","/collection_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
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
					$FieldDef["archive_code"]["value"] = $ArchiveCode;
					$FieldDef["collection_title"]["value"] = aib_get_with_default($ItemDef,"item_title","");
					$OutBuffer[] = aib_gen_form_header("pageform","/collection_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='do_del'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					break;

				// Add new collection

				case "add":
				case false:
				default:
					$FieldDef["archive_code"]["value"] = $ArchiveCode;
					$FieldDef["archive_title"]["value"] = "";
					$OutBuffer[] = aib_gen_form_header("pageform","/collection_form.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
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

			if ($ErrorMessage == false)
			{
				switch($OpCode)
				{
					// Save edited collection record or delete record

					case "save_edit":
					case "do_del":
						$TargetSpec = array();
						$TargetSpec[] = array("url" => "/records.php?opcode=list&parent=$ArchiveCode&pn=$SourcePageNumber","title" => "Return To List",
							"fields" => array());
						$OutBuffer[] = aib_chain_link_set($TargetSpec);
						break;
	
					// Save new collection

					case "save":
						$TargetSpec = array();
						if ($SourcePage == "records")
						{
							$TargetSpec[] = array("url" => "/records.php","title" => "Return To List",
								"fields" => array("opcode" => "list", "parent" => $ArchiveCode, "pn" => $SourcePageNumber));
						}
	
						$TargetSpec[] = array( "url" => "/collection_form.php", "title" => "Add Another Collection",
							"fields" => array( "opcode" => "add", "archive_code" => $ArchiveCode, "pn" => $SourcePageNumber));
						$TargetSpec[] = array("url" => "/records.php?opcode=list&parent=$ArchiveCode&pn=$SourcePageNumber","title" => "Return To List",
							"fields" => array());
						$OutBuffer[] = aib_chain_link_set($TargetSpec);
						break;
	
					// Delete confirm

					case "del":
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_display_field($FieldDef["archive_code"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_display_field($FieldDef["collection_title"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_form_submit("Delete Collection","link|/records.php?opcode=list&parent=$ArchiveCode&pn=$SourcePageNumber|Go Back To List");
						break;
	
					// Add or edit (default)

					case "edit":
	
						// Get the archive record; archive group is the parent of the archive.
	
						aib_open_db();
						$ItemDef = ftree_get_item($GLOBALS["aib_db"],$FormData["primary"]);
						aib_close_db();
						$ArchiveID = $ItemDef["item_parent"];
	
						// Set option list.  If in edit mode, set the default value to the current archive.
	
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_input_field($FieldDef["collection_title"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_dropdown_field($FieldDef["collection_visible"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						switch($OpCode)
						{
							case "edit":
								$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
								break;
	
							default:
							case false:
								$OutBuffer[] = aib_draw_form_submit("Add Collection","Clear Form");
								break;
						}
	
						$OutBuffer[] = "</table>";
						$OutBuffer[] = "</form>";
						break;

					case "add":
					case false:
						// Get a list of archives in the archive group for the drop-down (the default is the supplied archive code).  The archives are children
						// of the archive group, so we get the parent of the current archive to get that list.  If in edit mode, the archive code is supplied
						// by the "primary" form field, otherwise it is already loaded as "archive_code".
	
						if ($ArchiveCode === false)
						{
							$ErrorMessage = "Missing archive code";
							break;
						}
	
						// Get the archive record; archive group is the parent of the archive.
	
						aib_open_db();
						$ItemDef = ftree_get_item($GLOBALS["aib_db"],$ArchiveCode);
						$ArchiveGroup = $ItemDef["item_parent"];
	
						// Get the list of items in the archive group which are archive definitions
	
						$TempList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchiveGroup,false,false,FTREE_OBJECT_TYPE_FOLDER);
						$SelectList = array();
						foreach($TempList as $TempRecord)
						{
							$ArchiveCodeTitle = ftree_get_property($GLOBALS["aib_db"],$TempRecord["item_id"],AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
							if ($ArchiveCodeTitle == false)
							{
								continue;
							}
	
							$SelectList[$TempRecord["item_id"]] = $TempRecord["item_title"]." -- (".$ArchiveCodeTitle.")";
						}
	
						aib_close_db();
	
						// Set option list.  If in edit mode, set the default value to the current archive.
	
						$FieldDef["archive_code"]["option_list"] = $SelectList;
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_dropdown_field($FieldDef["archive_code"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_input_field($FieldDef["collection_title"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_dropdown_field($FieldDef["collection_visible"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						switch($OpCode)
						{
							case "edit":
								$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
								break;
	
							default:
							case false:
								$OutBuffer[] = aib_draw_form_submit("Add Collection","Clear Form");
								break;
						}
	
						$OutBuffer[] = "</table>";
						$OutBuffer[] = "</form>";
						break;
				}
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
