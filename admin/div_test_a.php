// Generate a tree nav block
// -------------------------
function aib_generate_tree_nav_div_edit_test($DBHandle,$UserID,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false, $ItemID = false)
{
	$OutLines = array();
	$OutLines[] = "<div class='aib-tree-nav-div'>";

	// Get item references

	$ItemRefMap = array();
	if ($ItemID !== false)
	{
		$ItemReferenceList = aib_get_item_references($ItemID);

		foreach($ItemReferenceList as $ItemRefRecord)
		{
			$ItemRefMap[$ItemRefRecord["item_parent"]] = $ItemRefRecord;
		}

		$TempRecord = ftree_get_item($DBHandle,$ItemID);
		$ItemRefMap[$TempRecord["item_parent"]] = ftree_get_item($DBHandle,$TempRecord["item_parent"]);
	}
	else
	{
		if ($InitialParent !== false)
		{
			$ItemRefMap[$InitialParent] = ftree_get_item($DBHandle,$InitialParent);
		}
	}

	// Get root folder for user if no initial parent or item ID

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$OriginalInitialParent = $InitialParent;
	$IDPathList = array();
	while(true)
	{
		if ($ItemID === false)
		{
			if ($InitialParent === false)
			{
				if ($UserID == AIB_SUPERUSER)
				{
					$InitialParent = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
				}
				else
				{
					$InitialParent = $UserRecord["user_top_folder"];
				}

				break;
			}

			break;
		}

		break;
	}

	// Get path to this item to root

	if ($InitialParent !== false)
	{
		$IDPathList = ftree_get_item_id_path($DBHandle,$InitialParent);
	}
	else
	{
		if ($ItemID !== false)
		{
			$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
		}
		else
		{
			$IDPathList = array();
		}
	}

	// If the initial parent is the user's top folder, then eliminate the parts of the path
	// that are outside of the user's tree.

	if ($UserRecord != false)
	{
		if ($InitialParent == $UserRecord["user_top_folder"])
		{
			while(true)
			{
				if (count($IDPathList) > 0)
				{
					if ($IDPathList[0] == $InitialParent)
					{
						break;
					}

					array_shift($IDPathList);
				}
				else
				{
					break;
				}
			}
		}
		else
		{
			$UserType = $UserRecord["user_type"];
			if ($UserType == AIB_USER_TYPE_USER || $UserType == AIB_USER_TYPE_PUBLIC)
			{
				$InitialParent = $UserRecord["user_top_folder"];
				while(true)
				{
					if (count($IDPathList) > 0)
					{
						if ($IDPathList[0] == $InitialParent)
						{
							break;
						}

						array_shift($IDPathList);
					}
					else
					{
						break;
					}
				}
			}
		}
	}

	// Set up map for path

	$PathMap = array();
	foreach($IDPathList as $TempID)
	{
		$PathMap[$TempID] = true;
	}

	$PathMap[$InitialParent] = true;

	// Output list tree

	if ($ULClass != false)
	{
		$OutLines[] = "<ul class='$ULClass'>";
	}
	else
	{
		$OutLines[] = "<ul>";
	}

	// Set tail item

	$CloseCount = 0;
	if (count($IDPathList) > 0)
	{
		$LastItem = $IDPathList[count($IDPathList) - 1];
	}
	else
	{
		$LastItem = false;
	}

	$TopFolderType = ftree_get_property($GLOBALS["aib_db"],$IDPathList[0],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$TopFolderRecord = ftree_get_item($GLOBALS["aib_db"],$IDPathList[0]);
	$TopFolderTitle = urldecode($TopFolderRecord["item_title"]);

	// Get the archive and archive group


	if ($ItemID === false)
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$InitialParent);
	}
	else
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$ItemID);
	}

	$ArchiveID = $ArchiveInfo["archive"]["item_id"];
	$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];
	if ($ArchiveGroupID !== false && $ArchiveGroupID != "")
	{
		$TempID = $ArchiveGroupID;
	}
	else
	{
		$TempID = $IDPathList[0];
	}

	while(true)
	{
		// Build field, ID name, child ID name

		if (isset($ItemRefMap[$TempID]) == true)
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\" checked>";
		}
		else
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' checked  onclick=\"set_tree_checkbox(event,this);\">";
		}

		$IDName = "aib_navlist_entry_".$TempID;
		$ChildIDName = "aib_navlist_childof_".$TempID;
		$ItemRecord = ftree_get_item($DBHandle,$TempID);

		// Decode title

		$ItemRecord["item_title"] = urldecode($ItemRecord["item_title"]);
		$CloseCount++;

		// Output archive group title

		if ($ArchiveClass != false)
		{
			$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$ArchiveClass'>".$ItemRecord["item_title"];
		}
		else
		{
			$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">".$ItemRecord["item_title"];
		}

		if ($ULClass != false)
		{
			$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
		}
		else
		{
			$OutLines[] = "<ul id='$ChildIDName'>";
		}

		$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap);
		$OutLines[] = "</ul>";
		break;
	}

	$OutLines[] = "</ul>";
	$OutLines[] = "</div>";
	array_pop($IDPathList);
	$OutIDList = array_keys($ItemRefMap);
	foreach(array_keys($PathMap) as $TempVal)
	{
		if (isset($ItemRefMap[$TempVal]) == false)
		{
			$OutIDList[] = $TempVal;
		}
	}

	return(array("idlist" => $OutIDList, "init_item" => $InitialParent, "html" => join("\n",$OutLines)));
}


