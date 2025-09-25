<?php
//
// air.php (Ajax Info Request)
//
// Ajax handler
//

include("../config/aib.php");
include("../include/folder_tree.php");
include("../include/accounts.php");
include("../include/aib_util.php");

function log_air_message($Msg)
{
	$Handle = fopen("/tmp/air_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function send_status($Status,$Info)
{
	$OutData = array("status" => $Status, "info" => $Info);
	print(json_encode($OutData));
}

function element_with_default($InArray,$Name,$Default)
{
	if (isset($InArray[$Name]) == false)
	{
		return($Default);
	}

	return($InArray[$Name]);
}

// Convert user type title to type code
// ------------------------------------
function aib_user_type_code_from_title($Code)
{
	switch(strtolower($Code))
	{
		case "root":
			return(AIB_USER_TYPE_ROOT);

		case "admin":
			return(AIB_USER_TYPE_ADMIN);

		case "user":
			return(AIB_USER_TYPE_USER);

		case "sub-admin":
		case "sub":
		case "subadmin":
			return(AIB_USER_TYPE_SUBADMIN);
			break;

		default:
			return("");
	}
}


// RENDER FUNCTIONS
// ================
// Render user type
// ----------------
function aib_render_user_type_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	switch($ColValue)
	{
		case AIB_USER_TYPE_ROOT:
			return("ROOT");

		case AIB_USER_TYPE_ADMIN:
			return("ADMIN");

		case AIB_USER_TYPE_USER:
			return("USER");

		case AIB_USER_TYPE_SUBADMIN:
			return("SUB-ADMIN");

		default:
			return("N/A");
	}
}

// Render top folder
// -----------------
function aib_render_user_top_folder($ColName,$ColValue,$Record,$ExtraData = false)
{
	$LocalResults = ftree_get_item($GLOBALS["aib_db"],$ColValue);
	if ($LocalResults == false)
	{
		return($ColValue);
	}

	return($LocalResults["item_title"]);
}

// Render user group
// -----------------
function aib_render_user_group_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$LocalResults = aib_db_query("SELECT * FROM ftree_group WHERE group_id=$ColValue;");
	if ($LocalResults == false)
	{
		return("N/A");
	}

	return($LocalResults[0]["group_title"]);
}

// Render archive group code column
// --------------------------------
function aib_render_archive_group_code_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$ItemID = $Record["item_id"];
	$Title = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
	if ($Title != false)
	{
		return($Title);
	}

	return("N/A");
}

// Render archive code column
// --------------------------
function aib_render_archive_code_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$ItemID = $Record["item_id"];
	$Code = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
	if ($Code != false)
	{
		return($Code);
	}

	return("N/A");
}

// Render archive title column
// ---------------------------
function aib_render_archive_title_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$ItemID = $Record["item_id"];
	$Title = ftree_get_property($GLOBALS["aib_db"],$ItemID,"archive_name");
	if ($Title != false)
	{
		return($Title);
	}

	return("N/A");
}

// Render archive owner column
// ---------------------------
function aib_render_archive_owner_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OwnerID = $Record["item_user_id"];
	$UserInfo = ftree_get_user($GLOBALS["aib_db"],$OwnerID);
	if ($UserInfo != false)
	{
		return($UserInfo["user_title"]);
	}

	return($OwnerID);
}

// Render actions column
// ---------------------
function aib_render_actions_col($ColName,$ColValue,$Record,$ExtraData = false)
{
	$OutLines = array();
	if ($ExtraData == false)
	{
		return("");
	}

	$NavString = aib_get_nav_string();
	if ($ExtraData !== false)
	{
		foreach($ExtraData as $Operation => $OpData)
		{
			$URL = $OpData["url"];
			$URL .= "?opcode=".$OpData["opcode"]."&primary=".$Record[$OpData["primary"]];
			aib_update_nav_info("primary",$Record[$OpData["primary"]]);
			$URL .= "&aibnav=$NavString";
			if (isset($OpData["image"]) == true)
			{
				$Link = "<a href=\"$URL\" class='aib-list-action-link'><img class='aib-list-action-link-icon' src=\"".$OpData["image"]."\" title=\"".$OpData["title"]."\"></a>";
			}
			else
			{
				$Link = "<a href=\"$URL\" class='aib-list-action-link'>".$OpData["title"]."</a>";
			}
	
			$OutLines[] = $Link;
		}
	}

	return(join("&nbsp;",$OutLines));
}

// #########
// MAIN CODE
// #########

	// Get form

	$FormData = aib_get_form_data();
	aib_get_nav_info($FormData);
	$NavString = aib_get_nav_string();

	// Get opcode.  If not present, error

	$OpCode = element_with_default($FormData,"o",false);
	if ($OpCode == false)
	{
		aib_log_message("ERROR","air.php","Missing opcode");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	$OpCode = hex2bin($OpCode);

	// Get session ID.  Error if not present or bad value.

	$SessionID = element_with_default($FormData,"s",false);
	if ($SessionID === false)
	{
		aib_log_message("ERROR","air.php","Missing session");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	// Get user record ID.  Error if not present or bad value.

	$UserID = element_with_default($FormData,"i",false);
	if ($UserID === false)
	{
		aib_log_message("ERROR","air.php","Missing user ID");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	$UserID = intval(hex2bin($UserID));
	if ($UserID < 0)
	{
		aib_log_message("ERROR","air.php","User ID is invalid (less than zero)");
		send_status("ERROR",array("msg" => "Invalid"));
		exit(0);
	}

	switch($OpCode)
	{
		// Test response
		case "ts":
			send_status("OK",array("msg" => "Test response","value" => date("H:i:s")));
			exit(0);

		// Get a list of collections in an archive
		case "lca":
			// Get key value, which is the archive item ID

			$HTML = "";
			$KeyValue = ltrim(rtrim(aib_get_with_default($FormData,"key",false)));
			$EmptyListMessage = "";
			$ResultList = array();
			if ($KeyValue == false)
			{
				$EmptyListMessage = "<tr class='aib-list-error-message-row'><td colspan='99' class='aib-list-error-message-cell'><span class='aib-list-error-message-span'>No archive selected</span></td></tr>";
				aib_log_message("ERROR","air.php","Missing key value when getting list of collections in archive");
			}

			if ($KeyValue == "NULL")
			{
				$EmptyListMessage = "<tr class='aib-list-error-message-row'><td colspan='99' class='aib-list-error-message-cell'><span class='aib-list-error-message-span'>No archive selected</span></td></tr>";
			}

			if ($EmptyListMessage == "")
			{
				// Get list ID

				$ListID = aib_get_with_default($FormData,"id","");

				// Get the page number and number of items per page
	
				$PageNumber = aib_get_with_default($FormData,"pn","1");
				$PageItemCount = aib_get_with_default($FormData,"pic","10");
				$StartItem = ($PageNumber - 1) * $PageItemCount;
				if ($StartItem < 0)
				{
					$StartItem = 0;
				}
	
				// Get operation.  This may be one of "list" or "search"
	
				$ListOp = aib_get_with_default($FormData,"lop","list");
				aib_open_db();

				// Get user profile

				$UserInfo = ftree_get_user($GLOBALS["aib_db"],$UserID);
				$UpdatedItemCount = 0;
				$ResultList = array();
				switch($ListOp)
				{
					case "list":
					default:
						$ResultList = ftree_list_child_objects($GLOBALS["aib_db"],$KeyValue,$UserInfo["user_id"],$UserInfo["user_primary_group"],FTREE_OBJECT_TYPE_FOLDER);
						$UpdatedItemCount = aib_db_count("ftree","item_parent=$KeyValue;");
						$ResultList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$KeyValue ORDER BY item_title LIMIT $StartItem,$PageItemCount;");
						break;
				}
	
				if ($ResultList == false)
				{
					$ResultList = array();
					$EmptyListMessage = "<tr class='aib-list-error-message-row'><td colspan='99' class='aib-list-error-message-cell'><span class='aib-list-error-message-span'>There are no collections in this archive</span></td></tr>";
				}
			}

			$ListParam = array();
			$ListParam["columns"] = array(
					"item_title" => "Collection Name",
					".op" => "",
					);

			$ListParam["callbacks"] = array(

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_actions_col",
					array("edit" => array("title" => "Edit", "url" => "/collection_form.php", "primary" => "item_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
						"del" => array("title" => "Delete", "url" => "/collection_form.php", "primary" => "item_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
						),
					),
				);
			$ListParam["searchable"] = array(
				"item_title" => "Collection Name",
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);
			break;

		// Get a list of all archives
		case "lar":
			// Make sure this is the superuser

			if ($UserID != intval(AIB_SUPERUSER))
			{
				aib_log_message("ERROR","air.php","Attempt by non-super user to get administrative user list");
				send_status("ERROR",array("msg" => "Invalid lar"));
				exit(0);
			}

			aib_open_db();
			$UserInfo = ftree_get_user($GLOBALS["aib_db"],$UserID);

			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the page number and number of items per page

			$PageNumber = aib_get_with_default($FormData,"pn","1");
			$PageItemCount = aib_get_with_default($FormData,"pic","10");
			$StartItem = ($PageNumber - 1) * $PageItemCount;
			if ($StartItem < 0)
			{
				$StartItem = 0;
			}

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			$ArchiveGroup = aib_get_with_default($FormData,"key",false);
			if ($ArchiveGroup === false || $ArchiveGroup == "NULL")
			{
				aib_close_db();
				send_status("OK",array("html" => "<p>You must select an archive group</p>", "pagecount" => "0"));
				exit(0);
			}

			$UpdatedItemCount = 0;
			$ResultList = array();
			switch($ListOp)
			{
				// Search
				case "search":
					$SearchValue = aib_get_with_default($FormData,"lsv",false);
					$SearchCol = aib_get_with_default($FormData,"lsc","ALL");
					if ($SearchValue != false)
					{
						switch($SearchCol)
						{
							case ".archive_title":
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$SubList = aib_db_query("SELECT * FROM ftree_prop WHERE property_name='archive_name AND (property_value LIKE \"%$SearchValue%\" OR property_value LIKE \"%$SearchValueOne%\" OR property_value LIKE \"%$SearchValueTwo%\") ORDER BY property_value;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								$ResultList = array();
								$Counter = 0;
								$UpdatedItemCount = 0;
								foreach($SubList as $SubRecord)
								{
									$ItemID = $SubRecord["item_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_id=$ItemID;");
									if ($TempList != false)
									{
										if ($TempList[0]["item_parent"] == $ArchivesFolderID)
										{
											$UpdatedItemCount++;
											if ($Counter < $StartItem)
											{
												$Counter++;
												continue;
											}

											if ($Counter > $StartItem + $PageItemCount)
											{
												continue;
											}

											$ResultList[] = $TempList[0];
											$Counter++;
										}
									}

								}

								break;

							case "item_title":
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND (item_title LIKE \"%$SearchValue%\" OR item_title LIKE \"%$SearchValueOne%\" OR item_title LIKE \"%$SearchValueTwo%\") ORDER BY item_title;");
								$UpdatedItemCount = count($TempList);
								$Counter = 0;
								$ResultList = array();
								foreach($TempList as $TempRecord)
								{
									if ($Counter < $StartItem)
									{
										$Counter++;
										continue;
									}

									if ($Counter > $StartItem + $PageItemCount)
									{
										continue;
									}

									$ResultList[] = $TempRecord;
								}

								break;

							case ".archive_owner":
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$SubList = aib_db_query("SELECT * FROM ftree_user WHERE user_name LIKE \"%$SearchValue%\" OR user_name LIKE \"%$SearchValueOne%\" OR user_name LIKE \"%$SearchValueTwo%\" ORDER BY user_name;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								$ResultList = array();
								foreach($SubList as $SubRecord)
								{
									$UserID = $SubRecord["user_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND item_owner=$UserID ORDER BY item_title;");
									if ($TempList != false)
									{
										$UpdatedItemCount++;
										if ($Counter < $StartItem)
										{
											$Counter++;
											continue;
										}

										if ($Counter > $StartItem + $PageItemCount)
										{
											continue;
										}

										$ResultList[] = $TempList[0];
										$Counter++;
									}
								}

								break;

							case "ALL":
							default:
								// Do all of the searches, placing the results in associative array where the key is the item ID.  Once all
								// occurrences are found, output a list sorted by title.

								$ItemMap = array();
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$SearchValueThree = strtoupper($SearchValue);
								$SubList = aib_db_query("SELECT * FROM ftree_prop WHERE property_name='archive_name' AND (property_value LIKE \"%$SearchValue%\" OR property_value LIKE \"%$SearchValueOne%\" OR property_value LIKE \"%$SearchValueTwo%\" OR property_value LIKE \"%$SearchValueThree%\") ORDER BY property_value;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								foreach($SubList as $SubRecord)
								{
									$ItemID = $SubRecord["item_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_id=$ItemID;");
									if ($TempList != false)
									{
										$TempRecord = $TempList[0];
										if ($TempRecord["item_parent"] == $ArchivesFolderID)
										{
											if (isset($ItemMap[$TempRecord["item_id"]]) == false)
											{
												$ItemMap[$TempRecord["item_id"]] = $TempRecord;
											}
										}
									}
								}

								$SubList = aib_db_query("SELECT * FROM ftree_user WHERE user_name LIKE \"%$SearchValue%\" OR user_name LIKE \"%$SearchValueOne%\" OR user_name LIKE \"%$SearchValueTwo%\" OR user_name LIKE \"%$SearchValueThree%\" ORDER BY user_name;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								foreach($SubList as $SubRecord)
								{
									$UserID = $SubRecord["user_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND item_owner=$UserID ORDER BY item_title;");
									if ($TempList != false)
									{
										if (isset($ItemMap[$TempList[0]["item_id"]]) == false)
										{
											$ItemMap[$TempList[0]["item_id"]] = $$TempList[0];
										}
									}
								}

								$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND (item_title LIKE \"%$SearchValue%\" OR item_title LIKE \"%$SearchValueOne%\" OR item_title LIKE \"%$SearchValueTwo%\" OR item_title LIKE \"%$SearchValueThree%\") ORDER BY item_title;");
								if ($TempList != false)
								{
									foreach($TempList as $TempRecord)
									{
										if (isset($ItemMap[$TempRecord["item_id"]]) == false)
										{
											$ItemMap[$TempRecord["item_id"]] = $TempRecord;
										}
									}
								}

								// Create a list sorted by the item title followed by a delim and the item ID

								$SortList = array();
								foreach($ItemMap as $ItemID => $ItemRecord)
								{
									$SortList[] = $ItemRecord["item_title"]."\t".$ItemID;
								}

								sort($SortList);

								// Generate result list with items in sorted order

								$Counter = 0;
								$ResultList = array();
								$UpdatedItemCount = count($SortList);
								foreach($SortList as $SortKey)
								{
									if ($Counter < $StartItem)
									{
										$Counter++;
										continue;
									}

									if ($Counter >= $StartItem + $PageItemCount)
									{
										break;
									}

									$Segs = explode("\t",$SortKey);
									$ResultList[] = $ItemMap[$Segs[1]];
									$Counter++;
								}

								break;
						}
					}

					break;

				case "list":
				default:
					$UpdatedItemCount = aib_db_count("ftree","item_parent=$ArchiveGroup;");
					$ResultList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchiveGroup ORDER BY item_title LIMIT $StartItem,$PageItemCount;");
					break;
			}

			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam = array();
			$ListParam["columns"] = array(
					"item_title" => "Archive Name",
					".archive_code" => "Archive Code",
					".op" => "",
					);

			$ListParam["callbacks"] = array(
				".archive_code" => array("aib_render_archive_code_col",false),

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_actions_col",
					array("edit" => array("title" => "Edit", "url" => "/admin_archiveform.php", "primary" => "item_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
						"del" => array("title" => "Delete", "url" => "/admin_archiveform.php", "primary" => "item_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
						),
					),
				);
			$ListParam["searchable"] = array(
				"item_title" => "Archive Name",
				".archive_code" => "Archive Code",
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);

		// Get a list of all archive groups

		case "larg":
			// Make sure this is the superuser

			if ($UserID != intval(AIB_SUPERUSER))
			{
				aib_log_message("ERROR","air.php","Attempt by non-super user to get administrative list");
				send_status("ERROR",array("msg" => "Invalid larh"));
				exit(0);
			}

			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the page number and number of items per page

			$PageNumber = aib_get_with_default($FormData,"pn","1");
			$PageItemCount = aib_get_with_default($FormData,"pic","10");
			$StartItem = ($PageNumber - 1) * $PageItemCount;
			if ($StartItem < 0)
			{
				$StartItem = 0;
			}

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			aib_open_db();
			$UpdatedItemCount = 0;
			$ResultList = array();
			switch($ListOp)
			{
				// Search
				case "search":
					$SearchValue = aib_get_with_default($FormData,"lsv",false);
					$SearchCol = aib_get_with_default($FormData,"lsc","ALL");
					if ($SearchValue != false)
					{
						switch($SearchCol)
						{
							case ".archive_group_code":
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$SubList = aib_db_query("SELECT * FROM ftree_prop WHERE property_name='archive_name AND (property_value LIKE \"%$SearchValue%\" OR property_value LIKE \"%$SearchValueOne%\" OR property_value LIKE \"%$SearchValueTwo%\") ORDER BY property_value;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								$ResultList = array();
								$Counter = 0;
								$UpdatedItemCount = 0;
								foreach($SubList as $SubRecord)
								{
									$ItemID = $SubRecord["item_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_id=$ItemID;");
									if ($TempList != false)
									{
										if ($TempList[0]["item_parent"] == $ArchivesFolderID)
										{
											$UpdatedItemCount++;
											if ($Counter < $StartItem)
											{
												$Counter++;
												continue;
											}

											if ($Counter > $StartItem + $PageItemCount)
											{
												continue;
											}

											$ResultList[] = $TempList[0];
											$Counter++;
										}
									}

								}

								break;

							case "item_title":
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND (item_title LIKE \"%$SearchValue%\" OR item_title LIKE \"%$SearchValueOne%\" OR item_title LIKE \"%$SearchValueTwo%\") ORDER BY item_title;");
								$UpdatedItemCount = count($TempList);
								$Counter = 0;
								$ResultList = array();
								foreach($TempList as $TempRecord)
								{
									if ($Counter < $StartItem)
									{
										$Counter++;
										continue;
									}

									if ($Counter > $StartItem + $PageItemCount)
									{
										continue;
									}

									$ResultList[] = $TempRecord;
								}

								break;


							case "ALL":
							default:
								// Do all of the searches, placing the results in associative array where the key is the item ID.  Once all
								// occurrences are found, output a list sorted by title.

								$ItemMap = array();
								$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
								$SearchValueOne = strtolower($SearchValue);
								$SearchValueTwo = ucfirst($SearchValueOne);
								$SearchValueThree = strtoupper($SearchValue);
								$SubList = aib_db_query("SELECT * FROM ftree_prop WHERE property_name='archive_name' AND (property_value LIKE \"%$SearchValue%\" OR property_value LIKE \"%$SearchValueOne%\" OR property_value LIKE \"%$SearchValueTwo%\" OR property_value LIKE \"%$SearchValueThree%\") ORDER BY property_value;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								foreach($SubList as $SubRecord)
								{
									$ItemID = $SubRecord["item_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_id=$ItemID;");
									if ($TempList != false)
									{
										$TempRecord = $TempList[0];
										if ($TempRecord["item_parent"] == $ArchivesFolderID)
										{
											if (isset($ItemMap[$TempRecord["item_id"]]) == false)
											{
												$ItemMap[$TempRecord["item_id"]] = $TempRecord;
											}
										}
									}
								}

								$SubList = aib_db_query("SELECT * FROM ftree_user WHERE user_name LIKE \"%$SearchValue%\" OR user_name LIKE \"%$SearchValueOne%\" OR user_name LIKE \"%$SearchValueTwo%\" OR user_name LIKE \"%$SearchValueThree%\" ORDER BY user_name;");
								if ($SubList == false)
								{
									$SubList = array();
								}

								foreach($SubList as $SubRecord)
								{
									$UserID = $SubRecord["user_id"];
									$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND item_owner=$UserID ORDER BY item_title;");
									if ($TempList != false)
									{
										if (isset($ItemMap[$TempList[0]["item_id"]]) == false)
										{
											$ItemMap[$TempList[0]["item_id"]] = $$TempList[0];
										}
									}
								}

								$TempList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID AND (item_title LIKE \"%$SearchValue%\" OR item_title LIKE \"%$SearchValueOne%\" OR item_title LIKE \"%$SearchValueTwo%\" OR item_title LIKE \"%$SearchValueThree%\") ORDER BY item_title;");
								if ($TempList != false)
								{
									foreach($TempList as $TempRecord)
									{
										if (isset($ItemMap[$TempRecord["item_id"]]) == false)
										{
											$ItemMap[$TempRecord["item_id"]] = $TempRecord;
										}
									}
								}

								// Create a list sorted by the item title followed by a delim and the item ID

								$SortList = array();
								foreach($ItemMap as $ItemID => $ItemRecord)
								{
									$SortList[] = $ItemRecord["item_title"]."\t".$ItemID;
								}

								sort($SortList);

								// Generate result list with items in sorted order

								$Counter = 0;
								$ResultList = array();
								$UpdatedItemCount = count($SortList);
								foreach($SortList as $SortKey)
								{
									if ($Counter < $StartItem)
									{
										$Counter++;
										continue;
									}

									if ($Counter >= $StartItem + $PageItemCount)
									{
										break;
									}

									$Segs = explode("\t",$SortKey);
									$ResultList[] = $ItemMap[$Segs[1]];
									$Counter++;
								}

								break;
						}
					}

					break;

				case "list":
				default:
					$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVE GROUP");
					$UpdatedItemCount = aib_db_count("ftree","item_parent=$ArchivesFolderID;");
					$ResultList = aib_db_query("SELECT * FROM ftree WHERE item_parent=$ArchivesFolderID ORDER BY item_title LIMIT $StartItem,$PageItemCount;");
					break;
			}

			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam = array();
			$ListParam["columns"] = array(
					"item_title" => "Client Title",
					".archive_group_code" => "Client Code",
					".op" => "",
					);

			$ListParam["callbacks"] = array(
				".archive_group_code" => array("aib_render_archive_group_code_col",false),

				// Operations column extra data parameters are:
				//	title		Title of the operation
				//	url		URL for operation
				//	primary		Primary key for record to be passed in the "primary" field to the URL
				//	opcode		Opcode to be passed in the "opcode" field to the URL

				".op" => array("aib_render_actions_col",
					array("edit" => array("title" => "Edit", "url" => "/archivegroup_form.php", "primary" => "item_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
						"del" => array("title" => "Delete", "url" => "/archivegroup_form.php", "primary" => "item_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
						),
					),
				);
			$ListParam["searchable"] = array(
				"item_title" => "Client Title",
				".archive_group_code" => "Client Code",
				);
			$ListParam["pagecount"] = intval($UpdatedItemCount / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => intval($UpdatedItemCount / $ListParam["pagesize"]) + 1));
			exit(0);

		
		// Get a list of all administrative users

		case "lau":

			// Make sure this is the superuser

			if ($UserID != intval(AIB_SUPERUSER))
			{
				aib_log_message("ERROR","air.php","Attempt by non-super user to get administrative user list");
				send_status("ERROR",array("msg" => "Invalid lau"));
				exit(0);
			}

			// Get list ID

			$ListID = aib_get_with_default($FormData,"id","");

			// Get the page number and number of items per page

			$PageNumber = aib_get_with_default($FormData,"pn","1");
			$PageItemCount = aib_get_with_default($FormData,"pic","10");
			$StartItem = $PageNumber * $PageItemCount;

			// Get operation.  This may be one of "list" or "search"

			$ListOp = aib_get_with_default($FormData,"lop","list");
			aib_open_db();
			$ResultList = false;
			switch($ListOp)
			{
				// Search
				case "search":
					$SearchValue = aib_get_with_default($FormData,"lsv",false);
					$SearchCol = aib_get_with_default($FormData,"lsc","ALL");
					if ($SearchValue != false)
					{
						switch($SearchCol)
						{
							case "user_type":
								$UserTypeCode = aib_user_type_code_from_title($SearchValue);
								$ResultList = aib_db_query("SELECT * FROM ftree_user WHERE user_title='$UserTypeCode' ORDER BY user_title LIMIT $StartItem,$PageItemCount;");
								break;

							case "user_title":
								$ResultList = aib_db_query("SELECT * FROM ftree_user WHERE user_title LIKE '%$SearchValue%' ORDER BY user_title LIMIT $StartItem,$PageItemCount;");
								break;

							case "ALL":
							default:
								$ResultList = aib_db_query("SELECT * FROM ftree_user WHERE user_type LIKE '%$SearchValue%' OR user_title LIKE '%$SearchValue%' ORDER BY user_title LIMIT $StartItem,$PageItemCount;");
								break;
						}
					}

					break;

				case "list":
				default:
					$ResultList = aib_db_query("SELECT * FROM ftree_user WHERE user_type='".AIB_USER_TYPE_ADMIN."' ORDER BY user_title LIMIT $StartItem,$PageItemCount;");
					break;
			}


			if ($ResultList == false)
			{
				$ResultList = array();
			}

			$ListParam = array();
			$ListParam["columns"] = array(
					"user_id" => "User ID",
					"user_type" => "User Type",
					"user_title" => "User Name",
					"user_group" => "Primary Group",
					);
			$ListParam["callbacks"] = array(
				"user_type" => array("aib_render_user_type_col",false),
				"user_primary_group" => array("aib_render_user_group_col",false)
					);
			$ListParam["searchable"] = array(
				"user_type" => "User Type",
				"user_title" => "User Title"
				);
			$ListParam["pagecount"] = (count($ResultList) / $PageItemCount) + 1;
			if ($PageNumber >= $ListParam["pagecount"])
			{
				$PageNumber = $ListParam["pagecount"];
			}

			$ListParam["pagenum"] = $PageNumber;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => count($ResultList)));
			exit(0);

		// Get a list of all administrators for an archive

		case "laa":

			// Make sure this is the superuser

			if ($UserID != intval(AIB_SUPERUSER))
			{
				aib_log_message("ERROR","air.php","Attempt by non-super user to get administrative user list");
				send_status("ERROR",array("msg" => "Invalid lau"));
				exit(0);
			}

			// Get key, which is the archive ID

			$PageNumber = 1;
			$EmptyListMessgae = "";
			$KeyValue = ltrim(rtrim(aib_get_with_default($FormData,"key",false)));
			if ($KeyValue == false)
			{
				$EmptyListMessage = "<tr class='aib-list-error-message-row'><td colspan='99' class='aib-list-error-message-cell'><span class='aib-list-error-message-span'>No archive selected</span></td></tr>";
				aib_log_message("ERROR","air.php","Missing key value when getting list of users for archive");
			}

			if ($EmptyListMessage == "")
			{

				// Get list ID
	
				$ListID = aib_get_with_default($FormData,"id","");
	
				// Get the page number and number of items per page
	
				$PageNumber = aib_get_with_default($FormData,"pn","1");
				$PageItemCount = aib_get_with_default($FormData,"pic","10");
				$StartItem = ($PageNumber - 1) * $PageItemCount;
	
				$ResultList = array();
				$ListParam = array();
				$ListParam["columns"] = array(
						"user_login" => "Login",
						"user_title" => "User Name",
						"user_primary_group" => "Primary Group",
						".op" => "",
						);
				$ListParam["callbacks"] = array(
					"user_type" => array("aib_render_user_type_col",false),
					"user_primary_group" => array("aib_render_user_group_col",false),
					".op" => array("aib_render_actions_col",
						array("edit" => array("title" => "Edit", "url" => "/admin_form.php", "primary" => "user_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
							"del" => array("title" => "Delete", "url" => "/admin_form.php", "primary" => "user_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
							),
						),
					);
				$ListParam["searchable"] = array(
					"user_login" => "User Type",
					"user_title" => "User Title"
					);
				$ListParam["pagecount"] = (count($ResultList) / $PageItemCount) + 1;
				if ($PageNumber >= $ListParam["pagecount"])
				{
					$PageNumber = $ListParam["pagecount"];
				}
	
				$ListOp = aib_get_with_default($FormData,"lop","list");
				aib_open_db();
				switch($ListOp)
				{
					case "list":
					default:
						if ($KeyValue == "NULL" || $KeyValue == false)
						{
							$TempList = ftree_list_users($GLOBALS["aib_db"],AIB_USER_TYPE_ADMIN);
							$ResultList = array();
							foreach($TempList as $TempRecord)
							{
								if ($TempRecord["user_id"] == AIB_SUPERUSER)
								{
									continue;
								}

								$ResultList[] = $TempRecord;
							}
						}
						else
						{
							$ResultList = ftree_list_users_for_parent($GLOBALS["aib_db"],$KeyValue,AIB_USER_TYPE_ADMIN);
						}
	
						if (count($ResultList) < 1 || $ResultList == false)
						{
							$ResultList = array();
							$EmptyListMessage = "<tr class='aib-list-error-message-row'><td colspan='99' class='aib-list-error-message-cell'><span class='aib-list-error-message-span'>There are no administrators for this archive</span></td></tr>";
						}

						break;
				}
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => count($ResultList)));
			exit(0);
			break;

		// Get a list of all sub-administrators for an archive

		case "las":

			// Make sure this is the superuser

			if ($UserID != intval(AIB_SUPERUSER))
			{
				aib_log_message("ERROR","air.php","Attempt by non-super user to get administrative user list");
				send_status("ERROR",array("msg" => "Invalid lau"));
				exit(0);
			}

			// Get key, which is the archive ID

			$PageNumber = 1;
			$EmptyListMessage = "";
			$KeyValue = ltrim(rtrim(aib_get_with_default($FormData,"key","NULL")));
			$KeySegs = explode(":",$KeyValue);
			if (count($KeySegs) > 1)
			{
				$KeyValueType = $KeySegs[0];
			}
			else
			{
				$KeyValueType = false;
			}

			$KeyValue = preg_replace("/[^0-9]/","",$KeyValue);
			if ($EmptyListMessage == "")
			{

				// Get list ID
	
				$ListID = aib_get_with_default($FormData,"id","");
	
				// Get the page number and number of items per page
	
				$PageNumber = aib_get_with_default($FormData,"pn","1");
				$PageItemCount = aib_get_with_default($FormData,"pic","10");
				$StartItem = ($PageNumber - 1) * $PageItemCount;
	
				$ResultList = array();
				$ListParam = array();
				$ListParam["columns"] = array(
						"user_login" => "Login",
						"user_title" => "User Name",
						"user_top_folder" => "Archive",
						".op" => "",
						);
				$ListParam["callbacks"] = array(
					"user_type" => array("aib_render_user_type_col",false),
					"user_top_folder" => array("aib_render_user_top_folder",false),
					".op" => array("aib_render_actions_col",
						array("edit" => array("title" => "Edit", "url" => "/assistant_form.php", "primary" => "user_id", "opcode" => "edit", "image" => "/images/monoicons/pencil32.png"),
							"del" => array("title" => "Delete", "url" => "/assistant_form.php", "primary" => "user_id", "opcode" => "del", "image" => "/images/monoicons/recycle32.png"),
							),
						),
					);
				$ListParam["searchable"] = array(
					"user_login" => "User Type",
					"user_title" => "User Title"
					);
				$ListParam["pagecount"] = (count($ResultList) / $PageItemCount) + 1;
				if ($PageNumber >= $ListParam["pagecount"])
				{
					$PageNumber = $ListParam["pagecount"];
				}
	
				$ListOp = aib_get_with_default($FormData,"lop","list");
				aib_open_db();
				switch($ListOp)
				{
					case "list":
					default:
						// Get list of assistants based on the type of folder

						$ResultList = array();
						switch($KeyValueType)
						{
							// All assistants in all archives in the archive group

							case AIB_ITEM_TYPE_ARCHIVE_GROUP:
								$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$KeyValue);
								if ($ArchiveList == false)
								{
									$ArchiveList = array();
								}

								foreach($ArchiveList as $ArchiveInfo)
								{
									$ArchiveID = $ArchiveInfo["item_id"];
									$TempResultList = aib_db_query("SELECT * FROM ftree_user WHERE user_type='".FTREE_USER_TYPE_SUBADMIN."' AND user_top_folder=$ArchiveID;");
									if ($TempResultList != false)
									{
										foreach($TempResultList as $TempResultRecord)
										{
											$ResultList[] = $TempResultRecord;
										}
									}
								}

								break;

							// All assistants in an archive

							case AIB_ITEM_TYPE_ARCHIVE:
								$TempResultList = aib_db_query("SELECT * FROM ftree_user WHERE user_type='".FTREE_USER_TYPE_SUBADMIN."' AND user_top_folder=$KeyValue;");
								if ($TempResultList != false)
								{
									$ResultList = $TempResultList;
								}

								break;

							// All assistants in the system

							default:
								$ResultList = ftree_list_users($GLOBALS["aib_db"],AIB_USER_TYPE_SUBADMIN);
								break;
						}

						break;
				}
			}

			if (count($ResultList) < 1 || $ResultList == false)
			{
				$ResultList = array();
				$EmptyListMessage = "<tr class='aib-list-error-message-row'><td colspan='99' class='aib-list-error-message-cell'><span class='aib-list-error-message-span'>There are no assistants for this archive</span></td></tr>";
			}

			$ListParam["pagenum"] = $PageNumber;
			$ListParam["pagesize"] = AIB_DEFAULT_ITEMS_PER_PAGE;
			$ListParam["empty_list_message"] = $EmptyListMessage;
			$HTML = aib_generate_generic_list_inner_html($FormData,$ListID,$ListParam,$ResultList);
			aib_close_db();
			send_status("OK",array("html" => $HTML, "pagecount" => count($ResultList)));
			exit(0);
			break;

		// Bad opcode

		default:
			aib_log_message("ERROR","air.php","Bad opcode $OpCode");
			break;
	}

	exit(0);
?>
