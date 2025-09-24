// Generate a tree nav block for edit mode.
// ----------------------------------------
function aib_generate_tree_nav_div_edit($DBHandle,$UserID,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false, $ItemID = false)
{
	$OutLines = array();
	$OutLines[] = "<div class='aib-tree-nav-div'>";

	// Get item references

	$ItemRefMap = array();
	$ExpandMap = array();
	if ($ItemID !== false)
	{
		$ItemReferenceList = aib_get_item_references($ItemID);
		foreach($ItemReferenceList as $ItemRefRecord)
		{
			$ItemRefMap[$ItemRefRecord["item_parent"]] = $ItemRefRecord;
			$TempList = ftree_get_item_id_path($DBHandle,$ItemRefRecord["item_parent"]);
			foreach($TempList as $PathID)
			{
				$ExpandMap[$PathID] = true;
			}
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

	// Set up map for path

	$PathMap = array();
	foreach($IDPathList as $TempID)
	{
		$PathMap[$TempID] = true;
	}

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
	$TempID = $ArchiveGroupID;

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

	$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
	$OutLines[] = "</ul>";
	$OutLines[] = "</ul>";
	$OutLines[] = "</div>";
	array_pop($IDPathList);
	$TempMap = array();
	foreach($ItemRefMap as $ItemID => $TempRecord)
	{
		$TempMap[$ItemID] = "Y";
	}

	if ($InitialParent !== false)
	{
		$TempMap[$InitialParent] = "Y";
	}

	$InitItemBufferLines = array();
	foreach($TempMap as $ItemID => $Status)
	{
		$InitItemBufferLines[] = " $ItemID:'$Status'";
	}

	$InitialParentBuffer = join(",",$InitItemBufferLines);
	$OutIDList = array_keys($ItemRefMap);
	foreach(array_keys($PathMap) as $TempVal)
	{
		if (isset($ItemRefMap[$TempVal]) == false)
		{
			$OutIDList[] = $TempVal;
		}
	}

	return(array("idlist" => $OutIDList, "init_item" => $InitialParentBuffer, "html" => join("\n",$OutLines)));
}




