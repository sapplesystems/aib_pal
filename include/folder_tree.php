<?php
//
// Folder tree system
//
//

define('FTREE_OBJECT_TYPE_FOLDER','F');
define('FTREE_OBJECT_TYPE_FILE','I');
define('FTREE_OBJECT_TYPE_LINK','L');
define('FTREE_OBJECT_TYPE_LINK_FOLDER','LF');
define('FTREE_OBJECT_TYPE_LINK_FILE','LI');

define('FTREE_SOURCE_TYPE_INTERNAL','I');
define('FTREE_SOURCE_TYPE_URL','U');
define('FTREE_SOURCE_TYPE_STPARCHIVE','A');
define('FTREE_SOURCE_TYPE_FILE','F');
define('FTREE_SOURCE_TYPE_LINK','L');

define('FTREE_STP_LINK_EDITION','E');
define('FTREE_STP_LINK_PAGE','P');
define('FTREE_STP_LINK_YEAR','Y');

define('FTREE_PERM_READ','R');
define('FTREE_PERM_MODIFY','M');
define('FTREE_PERM_WRITE','W');
define('FTREE_PERM_COPY','C');
define('FTREE_PERM_MOVE','O');
define('FTREE_PERM_DELETE','D');
define('FTREE_PERM_CHMOD','P');
define('FTREE_PERM_CHOWN','N');

define('FTREE_USER_TYPE_STANDARD','U');
define('FTREE_USER_TYPE_ROOT','R');
define('FTREE_USER_TYPE_ADMIN','A');
define('FTREE_USER_TYPE_SUBADMIN','S');

define('FTREE_GROUP_ROOT','1');
define('FTREE_GROUP_ADMIN','2');

define('FTREE_USER_SUPERADMIN','1');

define('FTREE_OWNER_TYPE_USER','U');
define('FTREE_OWNER_TYPE_GROUP','G');
define('FTREE_OWNER_TYPE_SYSTEM','S');
define('FTREE_OWNER_TYPE_ITEM','I');
define('FTREE_OWNER_TYPE_FORM','F');
define('FTREE_OWNER_TYPE_RECOMMENDED','R');

define('FTREE_FIELD_TYPE_TEXT','T');
define('FTREE_FIELD_TYPE_BIGTEXT','B');
define('FTREE_FIELD_TYPE_INTEGER','I');
define('FTREE_FIELD_TYPE_FLOAT','F');
define('FTREE_FIELD_TYPE_DECIMAL','E');
define('FTREE_FIELD_TYPE_DATE','D');
define('FTREE_FIELD_TYPE_TIME','M');
define('FTREE_FIELD_TYPE_DATETIME','DT');
define('FTREE_FIELD_TYPE_TIMESTAMP','TS');
define('FTREE_FIELD_TYPE_DROPDOWN','DD');

// Log a debugging message
// -----------------------
function ftree_log_debug($Msg)
{
	$Handle = fopen("/tmp/ftree_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,date("Y-m-d H:i:s").":$Msg\n");
		fclose($Handle);
	}
}

// Get result set from query
// -------------------------
function ftree_query($DBHandle,$Query)
{
	$Result = mysqli_query($DBHandle,$Query,MYSQLI_USE_RESULT);
	if ($Result == false)
	{
		return(array());
	}

	$OutList = mysqli_fetch_all($Result,MYSQLI_ASSOC);
/*
	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}
*/
	mysqli_free_result($Result);
	return($OutList);
}

// Do query and return false if empty set
// --------------------------------------
function ftree_query_ext($DBHandle,$Query)
{
	$ResultList = ftree_query($DBHandle,$Query);
	if ($ResultList == false)
	{
		return(false);
	}

	if (count($ResultList) < 1)
	{
		return(false);
	}

	return($ResultList);
}

// Return new auto-id from query
// -----------------------------
function ftree_insert_with_id($DBHandle,$Query)
{
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result === false)
	{
		return(false);
	}

	return(mysqli_insert_id($DBHandle));
}

// Get object from path.  Path specification is:
//
//	type:name\ttype:name\ttype:name\t....\ttype:name
//
// Example:
//
//	F:ARCHIVE GROUP<tab>F:Granite Falls Historical Society
//
// -------------------------------------------------
function ftree_get_object_by_path($DBHandle,$InPath,$Delim = "\t")
{
	$InSeg = explode($Delim,$InPath);
	$CurrentParent = -1;
	foreach($InSeg as $Segment)
	{
		$SegInfo = explode(":",$Segment);
		$SegType = array_shift($SegInfo);
		$SegName = join(":",$SegInfo);
		$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_parent=$CurrentParent AND item_type='$SegType' AND item_title='$SegName';");
		if ($LocalList == false)
		{
			return(false);
		}

		$LocalRecord = $LocalList[0];
		$CurrentParent = $LocalRecord["item_id"];
	}

	return($CurrentParent);
}

// Get object from path with a specific parent.  Path specification is:
//
//	type:name\ttype:name\ttype:name\t....\ttype:name
//
// Example:
//
//	F:ARCHIVE GROUP<tab>F:Granite Falls Historical Society
//
// -------------------------------------------------
function ftree_get_object_by_parent_path($DBHandle,$InPath,$StartParent = -1, $Delim = "\t")
{
	$InSeg = explode($Delim,$InPath);
	$CurrentParent = $StartParent;
	foreach($InSeg as $Segment)
	{
		$SegInfo = explode(":",$Segment);
		$SegType = array_shift($SegInfo);
		$SegName = join(":",$SegInfo);
		$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_parent=$CurrentParent AND item_type='$SegType' AND item_title='$SegName';");
		if ($LocalList == false)
		{
			return(false);
		}

		$LocalRecord = $LocalList[0];
		$CurrentParent = $LocalRecord["item_id"];
	}

	return($CurrentParent);
}

// Get object by ID
// ----------------
function ftree_get_object_by_id($DBHandle,$ItemID)
{
	$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_id=$ItemID;");
	if ($LocalList == false)
	{
		return(false);
	}

	return($LocalList[0]);
}

// Get ID of child object
// ----------------------
function ftree_get_child_object($DBHandle,$Parent,$ItemType,$ItemName)
{
	if ($ItemType == false)
	{
		$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_title='$ItemName';");
	}
	else
	{
		$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' AND item_title='$ItemName';");
	}

	if ($LocalList == false)
	{
		return(false);
	}

	return($LocalList[0]);
}

// Given a liste of item ID values, return records for each
// --------------------------------------------------------
function ftree_get_child_object_set($DBHandle,$ItemList)
{
	$OutList = array();
	foreach($ItemList as $ItemID)
	{
		$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_id='$ItemID';");
		if ($LocalList != false)
		{
			$OutList[] = $LocalList[0];
		}
	}

	return($OutList);
}


// Get all child objects of a parent
// ---------------------------------
function ftree_list_child_objects($DBHandle,$Parent,$UserID = false,$GroupID = false,$ItemType = false,$CountOnly = false,$SortTitle = false,$SortID = false)
{
	if ($CountOnly != false)
	{
		if ($ItemType != false)
		{
			$LocalResult = mysqli_query($DBHandle,"SELECT COUNT(*) FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType';");
		}
		else
		{
			$LocalResult = mysqli_query($DBHandle,"SELECT COUNT(*) FROM ftree WHERE item_parent=$Parent;");
		}

		if ($LocalResult == false)
		{
			return(array("ERROR",mysqli_error($DBHandle)));
		}

		$Row = mysqli_fetch_row($LocalResult);
		mysqli_free_result($LocalResult);
		return($Row[0]);
	}

	if ($ItemType != false)
	{
		if ($SortTitle == false)
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_type,item_title;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_id;");
			}
		}
		else
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_title,item_type;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_id;");
			}
		}
	}
	else
	{
		if ($SortTitle == false)
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_type,item_title;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_id;");
			}
		}
		else
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_title,item_type;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_id;");
			}
		}
	}

	if ($ResultSet != false)
	{
		$OutRows = mysqli_fetch_all($ResultSet,MYSQLI_ASSOC);
//		$OutRows = array();
//		while(true)
//		{
//			$Row = mysqli_fetch_assoc($ResultSet);
//			if ($Row == false)
//			{
//				break;
//			}
//
//			$OutRows[] = $Row;
//		}

		mysqli_free_result($ResultSet);
		return($OutRows);
	}

	return(false);
}

// Get all child objects of a parent that have one of the AIB types
// ----------------------------------------------------------------
function ftree_list_child_objects_filter_by_aibtype($DBHandle,$Parent,$UserID = false,$GroupID = false,$ItemType = false,$CountOnly = false,$SortTitle = false,$SortID = false,$AIBTypes = array())
{
	if ($ItemType != false)
	{
		if ($SortTitle == false)
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_type,item_title;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_id;");
			}
		}
		else
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_title,item_type;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType' ORDER BY item_id;");
			}
		}
	}
	else
	{
		if ($SortTitle == false)
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_type,item_title;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_id;");
			}
		}
		else
		{
			if ($SortID == false)
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_title,item_type;");
			}
			else
			{
				$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent ORDER BY item_id;");
			}
		}
	}

	$OutRows = array();
	$TotalCount = 0;
	if ($ResultSet != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($ResultSet);
			if ($Row == false)
			{
				break;
			}
	
			$PropertyValue = ftree_get_property($DBHandle,$Row["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
			if (in_array($PropertyValue,$AIBTypes) == true)
			{
				if ($CountOnly == false)
				{
					$OutRows[] = $Row;
				}
				else
				{
					$TotalCount++;
				}
			}
		}
	}

	mysqli_free_result($ResultSet);
	if ($CountOnly == false)
	{
		return($OutRows);
	}

	return($TotalCount);
}

// Get all child objects of a parent
// ---------------------------------
function ftree_count_child_object_property($DBHandle,$Parent,$UserID,$GroupID,$ItemType = false,$PropertyName)
{
	if ($ItemType != false)
	{
		$ResultSet = mysqli_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType';");
	}
	else
	{
		$ResultSet = mysqli_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$Parent;");
	}

	$PropertyCount = 0;
	if ($ResultSet != false)
	{
		$RowSet = mysqli_fetch_all($ResultSet,MYSQLI_ASSOC);
		mysqli_free_result($ResultSet);
		foreach($RowSet as $Row)
		{
/*
		while(true)
		{
			$Row = mysqli_fetch_assoc($ResultSet);
			if ($Row == false)
			{
				break;
			}
*/

			$ChildID = $Row["item_id"];
			$PropertyValue = ftree_get_property($DBHandle,$ChildID,$PropertyName);
			if ($PropertyValue != false)
			{
				$PropertyCount++;
			}
		}

	}

	return($PropertyCount);
}

// Given an item, find ultimate target of links
// --------------------------------------------
function ftree_deref_link($DBHandle,$ItemID)
{
	$CurrentRecord = false;
	while(true)
	{
		// Get item record

		$CurrentRecord = ftree_get_item($DBHandle,$ItemID);

		// If normal item record (not a link), done.

		if ($CurrentRecord["item_type"] == FTREE_OBJECT_TYPE_FOLDER || $CurrentRecord["item_type"] == FTREE_OBJECT_TYPE_FILE)
		{
			return($CurrentRecord);
		}

		// If link, check target

		if ($CurrentRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
		{
			// If target is a tree item, go to that tree item and look again

			if ($CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_INTERNAL || $CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_FILE)
			{
				$ItemID = $CurrentRecord["item_ref"];
				if ($ItemID < 0)
				{
					return(false);
				}

				continue;
			}

			// If URL or STP Archive, return

			if ($CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_URL || $CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_STPARCHIVE)
			{
				return($CurrentRecord);
			}
		}

		// Link to nowhere

		break;

	}

	return(false);
}

function ftree_log_call_init()
{
	if (isset($GLOBALS["aib_call_summary"]) == true)
	{
		unset($GLOBALS["aib_call_summary"]);
	}
}

function ftree_log_call_start($CallName)
{
	$GLOBALS["aib_call_start"] = array(microtime(true),$CallName);
	if (isset($GLOBALS["aib_call_summary"]) == false)
	{
		$GLOBALS["aib_call_summary"] = array($CallName => 0.0);
	}
	else
	{
		if (isset($GLOBALS["aib_call_summary"][$CallName]) == false)
		{
			$GLOBALS["aib_call_summary"][$CallName] = 0.0;
		}
	}
}

function ftree_log_call_end($DetailFlag = true)
{
	if (isset($GLOBALS["aib_call_start"]) == false)
	{
		return;
	}

	$EndTime = microtime(true);
	$DeltaTime = $EndTime - $GLOBALS["aib_call_start"][0];
	$CallName = $GLOBALS["aib_call_start"][1];
	$GLOBALS["aib_call_summary"][$CallName] += $DeltaTime;
	if ($DetailFlag == true)
	{
		ftree_log_debug($GLOBALS["aib_call_start"][1]." -- ".sprintf("%0.6lf",$DeltaTime));
	}

	unset($GLOBALS["aib_call_start"]);
}

function ftree_log_call_summary()
{
	foreach($GLOBALS["aib_call_summary"] as $CallName => $TotalTime)
	{
		ftree_log_debug("Summary for $CallName: ".sprintf("%0.6lf",$TotalTime));
	}
}

// Batch retrieval of properties for a set of item ID values
// ---------------------------------------------------------
function ftree_batch_get_properties_fetch($DBHandle,&$ItemIDList)
{
	$InitialCount = count($ItemIDList);

	// Create output set of arrays

	$OutSet = array();
	foreach($ItemIDList as $LocalID)
	{
		$OutSet[$LocalID] = array();
	}

	// Create set of array chunks, each <n> values long from the original list

	$ArraySet = array_chunk($ItemIDList,300);

	// Perform queries

	$QueryCounter = 1;
	foreach($ArraySet as $TempIDList)
	{
		// If nothing in list, exit request loop

		if (count($TempIDList) < 1)
		{
			break;
		}

		// Fetch short property values

		$Query = "SELECT * FROM ftree_prop WHERE item_id IN (".join(",",$TempIDList).");";
		$ResultSet = mysqli_query($DBHandle,$Query);
		$RowSet = mysqli_fetch_all($ResultSet,MYSQLI_ASSOC);
		foreach($RowSet as $Row)
		{
			$LocalID = $Row["item_id"];
			$OutSet[$LocalID][$Row["property_name"]] = $Row["property_value"];
		}

		// Fetch long property values

		$Query = "SELECT * FROM ftree_long_prop WHERE item_id IN (".join(",",$TempIDList).");";
		$ResultSet = mysqli_query($DBHandle,$Query);
		$RowSet = mysqli_fetch_all($ResultSet,MYSQLI_ASSOC);
		foreach($RowSet as $Row)
		{
			$LocalID = $Row["item_id"];
			$OutSet[$LocalID][$Row["property_name"]] = $Row["property_value"];
		}

		$QueryCounter++;
	}

	return($OutSet);
}

// Given a set of property names and values, get count of child items with those properties
// ----------------------------------------------------------------------------------------
function ftree_count_child_object_property_set_batch($DBHandle,$Parent,$UserID,$GroupID,$ItemType = false,$PropertySet,$Dereference = false,$LongProp = false)
{
	if ($ItemType != false)
	{
		$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType';");
	}
	else
	{
		$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent;");
	}

	$PropertySetCounts = array();
	foreach($PropertySet as $TempRecord)
	{
		$PropertySetCounts[$TempRecord["name"]."\t".$TempRecord["value"]] = 0;
	}

	$RecordCount = 0;
	if ($ResultSet != false)
	{
		// Get the child records

		$RowSet = mysqli_fetch_all($ResultSet,MYSQLI_ASSOC);
		mysqli_free_result($ResultSet);

		// Create ID list and record map, dereferencing if necessary.

		$IDList = array();
		$RecordMap = array();
		if ($Dereference == false)
		{
			$IDList = array_column($RowSet,"item_id");
			foreach($RowSet as $Row)
			{
				$RecordMap[$Row["item_id"]] = $Row;
			}
		}
		else
		{
			foreach($RowSet as $Row)
			{
				// Don't attempt dereference if a folder or file (performance optimization)

				if ($Row["item_type"] == FTREE_OBJECT_TYPE_FOLDER || $Row["item_type"] == FTREE_OBJECT_TYPE_FILE)
				{
					$IDList[] = $Row["item_id"];
					$RecordMap[$Row["item_id"]] = $Row;
				}
				else
				{
					// Might be a reference.  Deref link if necessary.

					$ChildID = $Row["item_id"];
					$TempRecord = ftree_deref_link($DBHandle,$ChildID);
					if ($TempRecord != false)
					{
						$ChildID = $TempRecord["item_id"];
						$RecordMap[$ChildID] = $TempRecord;
					}
	
					$IDList[] = $ChildID;
				}
			}
		}

		// Release the row set to conserve memory

		unset($RowSet);

		// Fetch properties for all child ID values

		$PropertyBatch = ftree_batch_get_properties_fetch($DBHandle,$IDList);
		unset($IDList);

		// Process all child ID values and properties

		foreach($RecordMap as $ChildID => $Row)
		{
			$PropertyValueMap = $PropertyBatch[$ChildID];
			foreach($PropertySet as $TempRecord)
			{
				$TestName = $TempRecord["name"];
				$TestValue = $TempRecord["value"];

				// If the property appears in the set of properties to track, process here

				if (isset($PropertyValueMap[$TestName]) == true)
				{
					if (urldecode($PropertyValueMap[$TestName]) == $TestValue)
					{
//						$Key = $TempRecord["name"]."\t".$TempRecord["value"];
						$PropertySetCounts[$TempRecord["name"]."\t".$TempRecord["value"]]++;
					}
				}
				else
				{
					// Otherwise, check for special property name or other special cases

					switch($TestName)
					{
						case "@object_type":
							if ($Row["item_type"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;

						case "@object_owner":
							if ($Row["item_user_id"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;

						case "@object_ref":
							if ($Row["item_ref"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;

						case "@notype":
							if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
							{
								if ($Row["item_type"] == FTREE_OBJECT_TYPE_FOLDER || $Row["item_type"] == FTREE_OBJECT_TYPE_FILE)
								{
									if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
									}
									else
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
									}
								}

							}

							break;

						case AIB_FOLDER_PROPERTY_FOLDER_TYPE:

							// "ITEM" type

							if (strtolower($TestValue) == AIB_ITEM_TYPE_ITEM)
							{
								if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
								{
									if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
									}
									else
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
									}
								}
							}

							break;

						default:
/*
							// "ITEM" type

							if ($TestName == AIB_FOLDER_PROPERTY_FOLDER_TYPE && strtolower($TestValue) == AIB_ITEM_TYPE_ITEM)
							{
								if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
								{
									if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
									}
									else
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
									}
								}
							}
*/
							break;
					}
/*
					while(true)
					{
						// Item tree type

						if ($TestName == "@object_type")
						{
							if ($Row["item_type"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;
						}

						// Item owner

						if ($TestName == "@object_owner")
						{
							if ($Row["item_user_id"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;
						}

						// Item reference

						if ($TestName == "@object_ref")
						{
							if ($Row["item_ref"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;
						}

						// "ITEM" type

						if ($TestName == AIB_FOLDER_PROPERTY_FOLDER_TYPE && strtolower($TestValue) == AIB_ITEM_TYPE_ITEM)
						{
							if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
							{
								if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
								{
									$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
								}
								else
								{
									$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
								}
							}

							break;
						}
							

						if ($TestName == "@notype")
						{
							if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
							{
								if ($Row["item_type"] == FTREE_OBJECT_TYPE_FOLDER || $Row["item_type"] ==
									FTREE_OBJECT_TYPE_FILE)
								{
									if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
									}
									else
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
									}
								}

							}

							break;
						}

						break;
					}
*/
				}
			}

			$RecordCount++;
		}

	}

	$OutData = array(
		array("name" => "_child_count", "value" => "", "count" => "$RecordCount")
		);

	foreach($PropertySet as $TempRecord)
	{
		$Key = $TempRecord["name"]."\t".$TempRecord["value"];
		$CountValue = $PropertySetCounts[$Key];
		$OutData[] = array("name" => $TempRecord["name"], "value" => $TempRecord["value"], "count" => $CountValue);
	}

	return($OutData);
}


// Given a set of property names and values, get count of child items with those properties
// ----------------------------------------------------------------------------------------
function ftree_count_child_object_property_set($DBHandle,$Parent,$UserID,$GroupID,$ItemType = false,$PropertySet,$Dereference = false,$LongProp = false)
{
	if ($ItemType != false)
	{
		$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent AND item_type='$ItemType';");
	}
	else
	{
		$ResultSet = mysqli_query($DBHandle,"SELECT * FROM ftree WHERE item_parent=$Parent;");
	}

	$PropertySetCounts = array();
	foreach($PropertySet as $TempRecord)
	{
		$PropertySetCounts[$TempRecord["name"]."\t".$TempRecord["value"]] = 0;
	}

	$RecordCount = 0;
	if ($ResultSet != false)
	{
		$RowSet = mysqli_fetch_all($ResultSet,MYSQLI_ASSOC);
		mysqli_free_result($ResultSet);
		foreach($RowSet as $Row)
		{
			$ChildID = $Row["item_id"];
			if ($Dereference != false)
			{
				$TempRecord = ftree_deref_link($DBHandle,$ChildID);
				if ($TempRecord != false)
				{
					$ChildID = $TempRecord["item_id"];
				}
			}

			$PropertyValueMap = ftree_list_properties($DBHandle,$ChildID,$LongProp);
			foreach($PropertySet as $TempRecord)
			{
				$TestName = $TempRecord["name"];
				$TestValue = $TempRecord["value"];

				// If the property appears in the set of properties to track, process here

				if (isset($PropertyValueMap[$TestName]) == true)
				{
					if (urldecode($PropertyValueMap[$TestName]) == $TestValue)
					{
						$Key = $TempRecord["name"]."\t".$TempRecord["value"];
						$PropertySetCounts[$Key]++;
					}
				}
				else
				{
					// Otherwise, check for special property name or other special cases

					while(true)
					{
						// Item tree type

						if ($TestName == "@object_type")
						{
							if ($Row["item_type"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;
						}

						// Item owner

						if ($TestName == "@object_owner")
						{
							if ($Row["item_user_id"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;
						}

						// Item reference

						if ($TestName == "@object_ref")
						{
							if ($Row["item_ref"] == $TestValue)
							{
								$Key = $TestName."\t".$TestValue;
								$PropertySetCounts[$Key]++;
							}

							break;
						}

						// "ITEM" type

						if ($TestName == AIB_FOLDER_PROPERTY_FOLDER_TYPE && strtolower($TestValue) == AIB_ITEM_TYPE_ITEM)
						{
							if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
							{
								if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
								{
									$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
								}
								else
								{
									$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
								}
							}

							break;
						}
							

						if ($TestName == "@notype")
						{
							if (isset($PropertyValueMap[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == false)
							{
								if ($Row["item_type"] == FTREE_OBJECT_TYPE_FOLDER || $Row["item_type"] ==
									FTREE_OBJECT_TYPE_FILE)
								{
									if (isset($PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]) == false)
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"] = 1;
									}
									else
									{
										$PropertySetCounts[AIB_FOLDER_PROPERTY_FOLDER_TYPE."\tIT"]++;
									}
								}

							}

							break;
						}

						break;
					}
				}
			}

			$RecordCount++;
		}

	}

	$OutData = array(
		array("name" => "_child_count", "value" => "", "count" => "$RecordCount")
		);

	foreach($PropertySet as $TempRecord)
	{
		$Key = $TempRecord["name"]."\t".$TempRecord["value"];
		$CountValue = $PropertySetCounts[$Key];
		$OutData[] = array("name" => $TempRecord["name"], "value" => $TempRecord["value"], "count" => $CountValue);
	}

	return($OutData);
}



// Get associative array item with default
// ---------------------------------------
function ftree_array_item_default($ArrayIn,$Name,$Default)
{
	if (isset($ArrayIn[$Name]) == false)
	{
		return($Default);
	}

	return($ArrayIn[$Name]);
}

// Create object by path
//
//	type:name\ttype:name\ttype:name\t....\ttype:name
//
// Example:
//
//	F:ARCHIVE GROUP<tab>F:Granite Falls Historical Society
//
// ---------------------
function ftree_create_object_by_path($DBHandle,$UserID,$GroupID,$TopFolder = -1,$Path,$Delim = "\t")
{
	// Get path segments.  Start at the top.

	$PathList = explode($Delim,$Path);
	$CurrentParent = $TopFolder;
	foreach($PathList as $PathSeg)
	{
		// Get seg type and title

		$SegInfo = explode(":",$PathSeg);
		$SegType = array_shift($SegInfo);
		$SegName = join(":",$SegInfo);

		// See if a child object with the type and name exists in the current parent

		$TempRecord = ftree_get_child_object($DBHandle,$CurrentParent,$SegType,$SegName);

		// If not present, create

		if ($TempRecord == false)
		{
			$LocalSpec = array(
				"parent" => $CurrentParent,
				"title" => $SegName,
				"user_id" => $UserID,
				"group_id" => $GroupID,
				"item_type" => $SegType,
				"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
				"reference_id" => -1,
				"source_info" => "",
				"allow_dups" => true,
				"user_perm" => "RMWCODPN",
				"group_perm" => "R",
				"world_perm" => "R"
				);

			$NewInfo = ftree_create_object_ext($DBHandle,$LocalSpec);
			if ($NewInfo[0] != "OK")
			{
				return($NewInfo);
			}

			$CurrentParent = $NewInfo[1];
		}
		else
		{
			$CurrentParent = $TempRecord["item_id"];
		}
	}

	return(array("OK",$CurrentParent,""));
}

// Create object using associative array
//
//	parent
//	title
//	user_id
//	group_id
//	item_type
//	source_type
//	source_info
//	reference_id
//	allow_dups
//	user_perm
//	group_perm
//	world_perm
// -------------------------------------
function ftree_create_object_ext($DBHandle,$Info)
{
	$ParentID = ftree_array_item_default($Info,"parent",-1);
	$Title = ftree_array_item_default($Info,"title",date("YmdHis"));
	$UserID = ftree_array_item_default($Info,"user_id",-1);
	$GroupID = ftree_array_item_default($Info,"group_id",-1);
	$ItemType = ftree_array_item_default($Info,"item_type",FTREE_OBJECT_TYPE_FILE);
	$SourceType = ftree_array_item_default($Info,"source_type",FTREE_SOURCE_TYPE_INTERNAL);
	$ReferenceID = ftree_array_item_default($Info,"reference_id",-1);
	$SourceInfo = ftree_array_item_default($Info,"source_info","");
	$AllowDups = ftree_array_item_default($Info,"allow_dups",false);
	$UserPerm = ftree_array_item_default($Info,"user_perm","RMWCODPN");
	$GroupPerm = ftree_array_item_default($Info,"group_perm","R");
	$WorldPerm = ftree_array_item_default($Info,"world_perm","R");
	return(ftree_create_object($DBHandle,$ParentID,$Title,$UserID,$GroupID,$ItemType,$SourceType,$SourceInfo,$ReferenceID,$AllowDups,$UserPerm,$GroupPerm,$WorldPerm));
}

// Create object
// -------------
function ftree_create_object($DBHandle,$Parent,$Title,$UserID,$GroupID,$ItemType,$SourceType = "I",$SourceInfo = "",$ReferenceID = -1,$AllowDups = false,
	$UserPermissions = 'RMWCODPN', $GroupPermissions = 'R', $WorldPermissions = 'R')
{
	// If not allowing for duplicates, make sure there isn't already
	// a child item with the same name and type.

	if ($AllowDups == false)
	{
		$List = ftree_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$Parent AND item_type='$SourceType' AND item_title='$Title';");
		if ($List != false)
		{
			if (count($List) > 0)
			{
				return(array("ERROR","DUPLICATE","SELECT item_id FROM ftree WHERE item_parent=$Parent AND item_type='$SourceType' AND item_title='$Title';"));
			}
		}
	}

	// Create entry

	$CreateStamp = microtime(true);
	$Query = "INSERT INTO ftree (item_parent,item_type,item_title,item_user_id,item_group_id,item_ref,item_source_type,item_source_info,item_create_stamp,user_perm,group_perm,world_perm) VALUES ($Parent,'$ItemType','$Title',$UserID,$GroupID,$ReferenceID,'$SourceType','$SourceInfo',$CreateStamp,'$UserPermissions','$GroupPermissions','$WorldPermissions');";
	$NewID = ftree_insert_with_id($DBHandle,$Query);
	if ($NewID === false)
	{
		return(array("ERROR","CANNOT CREATE: ".mysqli_error($DBHandle)." / ".$Query));
	}

	return(array("OK",$NewID,""));
}

// Create a link to an item elsewhere in the file tree
// ---------------------------------------------------
function ftree_link($DBHandle,$SourceID,$TargetParent,$OwnerID,$TargetName = false)
{
	// Get the source info

	$SourceList = ftree_query($DBHandle,"SELECT * FROM ftree WHERE item_id=$SourceID;");
	if (count($SourceList) < 1)
	{
		return(array("ERROR","SOURCE NOT FOUND"));
	}

	$SourceData = $SourceList[0];
	$SourceTitle = $SourceData["item_title"];

	// If no target name was supplied, use the existing name as the default

	if ($TargetName == false)
	{
		$TargetName = $SourceTitle;
	}

	// Make sure there isn't another item with the same name in the target parent

	$List = ftree_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$TargetParent AND item_title='$TargetName';");
	if (count($List) > 0)
	{
		return(array("ERROR","DUPLICATE NAME"));
	}

	// Create entry

	$ObjectType = FTREE_OBJECT_TYPE_LINK;
	$SourceType = FTREE_SOURCE_TYPE_LINK;
	$Query = "INSERT INTO ftree (item_parent,item_type,item_title,item_owner_id,item_ref,item_source_type,item_source_info) VALUES ".
		"($Parent,'$ObjectType','$TargetName','$OwnerID',$SourceID,'$SourceType','');";
	$NewID = ftree_insert_with_id($DBHandle,$Query);
	if ($NewID === false)
	{
		return(array("ERROR","CANNOT CREATE"));
	}

	return(array("OK",$NewID));
}

// Rename entry
// ------------
function ftree_rename($DBHandle,$Item,$NewTitle,$AllowDupsFlag = false)
{
	// Get the existing entry

	$SourceList = ftree_query($DBHandle,"SELECT item_parent FROM ftree WHERE item_id=$Item;");
	if ($SourceList == false)
	{
		return(array("ERROR","NOT FOUND"));
	}

	$TargetParent = $SourceList[0]["item_parent"];
	if ($AllowDupsFlag == false)
	{
		$List = ftree_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$TargetParent AND item_title='$NewTitle';");
		if (count($List) > 0)
		{
			$TempRecord = $List[0];
			if ($TempRecord["item_id"] != $Item)
			{
				return(array("ERROR","DUPLICATE NAME"));
			}
		}
	}

	mysqli_query($DBHandle,"UPDATE ftree SET item_title='$NewTitle' WHERE item_id=$Item;");
	return(array("OK"));
}

// Modify entry
//
//	item_title
//	item_user_id
//	item_group_id
//	user_perm
//	group_perm
//	world_perm
// ------------
function ftree_modify($DBHandle,$ItemID,$ItemInfo,$AllowDupsFlag = false)
{
	// Get the existing entry

	$SourceList = ftree_query($DBHandle,"SELECT item_parent FROM ftree WHERE item_id=$ItemID;");
	if ($SourceList == false)
	{
		return(array("ERROR","NOT FOUND"));
	}

	$ItemRecord = $SourceList[0];
	if (isset($ItemInfo["item_title"]) != false)
	{
		if ($ItemInfo["item_title"] != urldecode($ItemRecord["item_title"]))
		{
			$ReturnValue = ftree_rename($DBHandle,$ItemID,$ItemInfo["item_title"]);
			if ($ReturnValue[0] == "ERROR")
			{
				return($ReturnValue);
			}
		}
	}

	if (isset($ItemInfo["item_user_id"]) != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree SET item_user_id='".$ItemInfo["item_user_id"]."' WHERE item_id=$ItemID;");
	}

	if (isset($ItemInfo["item_group_id"]) != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree SET item_group_id='".$ItemInfo["item_group_id"]."' WHERE item_id=$ItemID;");
	}

	if (isset($ItemInfo["user_perm"]) != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree SET user_perm='".$ItemInfo["user_perm"]."' WHERE item_id=$ItemID;");
	}

	if (isset($ItemInfo["group_perm"]) != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree SET group_perm='".$ItemInfo["group_perm"]."' WHERE item_id=$ItemID;");
	}

	if (isset($ItemInfo["world_perm"]) != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree SET world_perm='".$ItemInfo["world_perm"]."' WHERE item_id=$ItemID;");
	}

	return(array("OK"));
}


// Move entry from one parent to another
// -------------------------------------
function ftree_move($DBHandle,$SourceItem,$TargetParent,$AllowDups = false,$NewTitle = false)
{
	// Get existing entry

	$SourceList = ftree_query($DBHandle,"SELECT * FROM ftree WHERE item_id=$SourceItem;");
	if ($SourceList == false)
	{
		return(array("ERROR","NOT FOUND"));
	}

	// Set moved item title.  If no title was specified in arguments then use existing title

	$SourceTitle = $SourceList[0]["item_title"];
	if ($NewTitle === false)
	{
		$NewTitle = $SourceTitle;
	}

	// Get target parent

	$TargetList = ftree_query($DBHandle,"SELECT * FROM ftree WHERE item_id=$TargetParent;");
	if ($TargetList == false)
	{
		return(array("ERROR","TARGET NOT FOUND"));
	}

	// Check for duplicates

	if ($AllowDups === false)
	{
		$CheckList = ftree_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$TargetParent and item_title='$NewTitle';");
		if ($CheckList != false)
		{
			return(array("ERROR","DUPLICATE TITLE"));
		}
	}

	// Move entry

	mysqli_query($DBHandle,"UPDATE ftree SET item_parent=$TargetParent WHERE item_id=$SourceItem;");

	// Rename if needed

	if ($NewTitle != $SourceTitle)
	{
		mysqli_query($DBHandle,"UPDATE ftree SET item_title='$NewTitle' WHERE item_id=$SourceItem;");
	}

	return(array("OK",""));
}

// Recursively build a list of objects
// -----------------------------------
function ftree_traverse($DBHandle,$TopItem,&$OutList,$CurrentDepth = 0,$MaxDepth = -1)
{
	// Get top item info if needed

	if (isset($OutList[$TopItem]) == false)
	{
		$TopList = ftree_query($DBHandle,"SELECT * FROM ftree WHERE item_id=$TopItem;");
		if ($TopList == false)
		{
			return(false);
		}

		if (count($TopList) < 1)
		{
			return(false);
		}

		$TopRecord = $TopList[0];
		$OutList[$TopItem] = array("data" => $TopRecord, "children" => array());
	}

	// Get a list of the items under the current top item

	$ChildList = ftree_query($DBHandle,"SELECT item_id FROM ftree WHERE item_parent=$TopItem;");
	if ($ChildList == false)
	{
		return(false);
	}

	if (count($ChildList) < 1)
	{
		return(false);
	}

	// For each item found, add entry to array and get sub-items

	foreach($ChildList as $ChildRecord)
	{
		$ChildID = $ChildRecord["item_id"];
		$OutList[$TopItem]["children"][] = $ChildID;

		// Get children of the current child

		if ($MaxDepth > 0)
		{
			if ($CurrentDepth >= $MaxDepth)
			{
				continue;
			}
		}

		ftree_traverse($DBHandle,$ChildID,$OutList,$CurrentDepth + 1,$MaxDepth);
	}

	return(true);
}

// Copy a set of entries to a target.  Allow automatic renaming of dups; if
// auto-rename is not allowed, then a list of dups is returned (they are
// not copied).  Duplicates are named with a suffix of " [version]".  The
// list of sources is an associative array where the item id is the key,
// and a replacement title is the data (or the value of "false" if the
// existing title is to be used).
//
// Return list indicates if an item was copied, or if there was an error.  The
// key is the source item, the record contains the status and the new item ID
// if the copy was successful.
// -------------------------------------------------------------------------
function ftree_copy($DBHandle,$SourceList,$TargetParent,$AutoRenameDups = true)
{
	$OutDupList = array();

	// Make sure the target exists

	$TempTargetList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_id=$TargetParent;");
	if ($TempTargetList == false)
	{
		$OutDupList[$TargetParent] = array("status" => "ERROR", "info" => "TARGET NOT FOUND");
		return($OutDupList);
	}

	foreach($SourceList as $ItemID => $ReplaceTitle)
	{
		// Get source data

		$TempSourceList = ftree_query_ext($DBHandle,"SELECT * from ftree WHERE item_id=$ItemID;");
		if ($TempSourceList == false)
		{
			$OutDupList[$ItemID] = array("status" => "ERROR", "info" => "SOURCE NOT FOUND");
			continue;
		}

		$SourceRecord = $TempSourceList[0];

		// Determine target title

		if ($ReplaceTitle === false)
		{
			$NewTitle = $SourceRecord["item_title"];
		}
		else
		{
			$NewTitle = $ReplaceTitle;
		}

		// Copy record

		$ItemType = $SourceRecord["item_type"];
		$TestTitle = $NewTitle;
		$Version = 1;
		while(true)
		{
			// Check for duplicate

			$CheckList = ftree_query_ext($DBHandle,"SELECT item_id,item_title FROM ftree WHERE item_parent=$TargetParent and item_type='$ItemType' AND item_title='$NewTitle';");

			// If duplicate, either error or rename

			if ($CheckList != false)
			{
				// If not auto-renaming, error

				if ($AutoRenameDups == false)
				{
					$OutDupList[$ItemID] = array("status" => "ERROR", "info" => "DUPLICATE");
					break;
				}

				// Increment version and try a new title

				$Version++;
				$TestTitle = $NewTitle." [".sprintf("%04d",$Version)."]";
				continue;
			}

			break;
		}

		// Store new entry

		$NewInfo = ftree_create_object($DBHandle,$TargetParent,$NewTitle,$SourceItemInfo["item_user"],$SourceItemInfo["item_group"],$SourceItemInfo["item_type"],$SourceItemInfo["item_info"],
			$SourceItemInfo["item_ref"]);

		// Set output status based on whether there was an error or not

		if ($NewInfo != false)
		{
			if ($NewInfo[0] == "OK")
			{
				// Copy was successful

				$OutDupList[$ItemID] = array("status" => "OK", "info" => $NewInfo[1]);
				$NewID = $NewInfo[1];

				// Copy all properties including security settings

				$PermList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE item_id=$ItemID;");
				if ($PermList != false)
				{
					foreach($PermList as $PermRecord)
					{
						$LocalUserID = $PermRecord["user_id"];
						$LocalGroupID = $PermRecord["group_id"];
						$LocalPermissions = $PermRecord["item_permissions"];
						mysqli_query($DBHandle,"INSERT INTO ftree_acl (item_id,user_id,group_id,item_rights) VALUES ($NewID,$LocalUserID,$LocalGroupID,'$LocalPermissions');");
					}
				}

				$PropList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_prop WHERE item_id=$ItemID;");
				if ($PropList != false)
				{
					foreach($PropList as $PropRecord)
					{
						$LocalPropName = $PropRecord["property_name"];
						$LocalPropValue = $PropRecord["property_value"];
						mysqli_query($DBHandle,"INSERT INTO ftree_prop (item_id,property_name,property_value) VALUES ($NewID,'$LocalPropName','$LocalPropValue');");
					}
				}

				// Copy fields

				$OutData = array();
				$TempList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_data WHERE item_id=$ItemID;");
				if ($TempList != false)
				{
					foreach($TempList as $FieldRecord)
					{
						$OutData[$FieldRecord["field_id"]] = $FieldRecord["field_value"];
					}
				}

				$TempList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_longdata WHERE item_id=$ItemID;");
				if ($TempList != false)
				{
					foreach($TempList as $FieldRecord)
					{
						$OutData[$FieldRecord["field_id"]] = $FieldRecord["field_value"];
					}
				}

				ftree_field_store_item_fields($DBHandle,$NewID,$OutData,false);

			}
			else
			{
				$OutDupList[$ItemID] = array("status" => "ERROR", "info" => $NewInfo[1]);
			}
		}
		else
		{
			$OutDupList[$ItemID] = array("status" => "ERROR", "info" => "CANNOT CREATE NEW ENTRY");
		}
	}

	return($OutDupList);
}

// See if a title and type already occurs in a parent
// --------------------------------------------------
function ftree_check_dup($DBHandle,$Parent,$Title,$Type)
{
	$Query = "SELECT item_id FROM ftree WHERE item_parent=$Parent AND item_title='$Title' AND item_type='$Type';";
	$OutList = ftree_query_ext($DBHandle,$Query);
	return($OutList);
}

// Given a source name and a target parent, determine unique name
// --------------------------------------------------------------
function ftree_generate_target_name($DBHandle,$Parent,$InTitle,$InType)
{
	$TargetName = $InTitle;
	$Counter = 0;
	while(true)
	{
		if (ftree_check_dup($DBHandle,$Parent,$TargetName,$InType) != false)
		{
			$Counter++;
			$TargetName = $InTitle." [".sprintf("%4d",$Counter)."]";
			continue;
		}

		break;
	}

	return($TargetName);
}

// Get info for single item
// ------------------------
function ftree_get_item($DBHandle,$ItemID)
{
	$OutList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_id=$ItemID;");
	if ($OutList == false)
	{
		return(false);
	}

	return($OutList[0]);
}

// Given item ID, return complete title path
// -----------------------------------------
function ftree_get_item_title_path($DBHandle,$ItemID,$UseCache = false)
{
	if ($UseCache !== false)
	{
		if (isset($GLOBALS["id_path_cache"]) == false)
		{
			$GLOBALS["id_path_cache"] = array();
		}
	}

	$OutList = array();
	$LocalItem = $ItemID;
	while(true)
	{
		if ($UseCache !== false)
		{
			if (isset($GLOBALS["id_path_cache"][$LocalItem]) == true)
			{
				$ItemData = $GLOBALS["id_path_cache"][$LocalItem];
			}
			else
			{
				$ItemData = ftree_get_item($DBHandle,$LocalItem);
				if ($ItemData == false)
				{
					break;
				}

				$GLOBALS["id_path_cache"][$LocalItem] = $ItemData;
			}
		}
		else
		{
			$ItemData = ftree_get_item($DBHandle,$LocalItem);
			if ($ItemData == false)
			{
				break;
			}
		}

//		$ItemData = ftree_get_item($DBHandle,$LocalItem);
		if ($ItemData == false)
		{
			break;
		}

		$LocalItem = $ItemData["item_parent"];
		if ($LocalItem < 0)
		{
			break;
		}

		$OutList[] = $ItemData;
	}

	if (count($OutList) == 0)
	{
		return(false);
	}

	$JoinList = array_reverse($OutList);
	return($JoinList);
}

// Given item ID, return complete ID path
// -----------------------------------------
function ftree_get_item_id_path($DBHandle,$ItemID, $UseCache = false)
{
	if ($UseCache !== false)
	{
		if (isset($GLOBALS["id_path_cache"]) == false)
		{
			$GLOBALS["id_path_cache"] = array();
		}
	}

	$OutList = array($ItemID);
	$LocalItem = $ItemID;
	while(true)
	{
		if ($UseCache !== false)
		{
			if (isset($GLOBALS["id_path_cache"][$LocalItem]) == true)
			{
				$ItemData = $GLOBALS["id_path_cache"][$LocalItem];
			}
			else
			{
				$ItemData = ftree_get_item($DBHandle,$LocalItem);
				if ($ItemData == false)
				{
					break;
				}

				$GLOBALS["id_path_cache"][$LocalItem] = $ItemData;
			}
		}
		else
		{
			$ItemData = ftree_get_item($DBHandle,$LocalItem);
			if ($ItemData == false)
			{
				break;
			}
		}

		$LocalItem = $ItemData["item_parent"];
		if ($LocalItem < 0)
		{
			break;
		}

		$OutList[] = $LocalItem;
	}

	if (count($OutList) == 0)
	{
		return(false);
	}

	$JoinList = array_reverse($OutList);
	return($JoinList);
}

// Copy entry, recursive
// ---------------------
function ftree_copy_recursive($DBHandle,$SourceItem,$TargetParent,$TargetName = false,$AutoRename = true)
{

	// Copy the initial object

	$ResultList = ftree_copy($DBHandle,array($SourceItem => false),$TargetParent,$AutoRename);
	if ($ResultList == false)
	{
		return(false);
	}

	$TempList = array_keys($ResultList);
	$Result = $ResultList[$TempList[0]];
	if ($Result["status"] != "OK")
	{
		return(false);
	}

	$NewItemID = $Result["info"];

	// Recursively copy all children of the source object to the destination

	$TargetStack = array($NewItemID);
	$ParentStack = array($SourceItem);
	while(count($ParentStack) > 0)
	{
		// Get current target parent and current source parent

		$CurrentTargetParent = array_pop($TargetStack);
		$CurrentSourceParent = array_pop($ParentStack);

		// Get list of child items under source parent

		$ChildList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_parent=$CurrentSourceParent;");
		if ($ChildList == false)
		{
			continue;
		}

		$CopyList = array();
		foreach($ChildList as $ChildRecord)
		{
			$CopyList[$ChildRecord["item_id"]] = $ChildRecord['item_title'];
		}

		// Copy the items

		$ResultList = ftree_copy($DBHandle,$CopyList,$CurrentTargetParent,false);

		// For each item copied, add to stacks

		foreach($ResultList as $SourceID => $NewRecord)
		{
			if ($NewRecord["status"] != "OK")
			{
				continue;
			}

			array_push($ParentStack,$SourceID);
			array_push($TargetStack,$NewRecord["info"]);
		}
	}

	return(true);
}

// Recursively list all items in a tree entry, and all child items
// ---------------------------------------------------------------
function ftree_list_recursive($DBHandle,$ParentItem,&$OutList,$ValidTypeSet,$ValidTitleSet,$CrawlNonMatching,$MaxDepth = 0,$CurrentDepth = 0)
{

	if ($MaxDepth >= 0)
	{
		if ($CurrentDepth > $MaxDepth)
		{
			return(true);
		}
	}

	$ChildList = ftree_query_ext($DBHandle,"SELECT item_id,item_type,item_title,item_parent FROM ftree FORCE INDEX (childidx) WHERE item_parent=$ParentItem;");
	if ($ChildList == false)
	{
		return(true);
	}

	foreach($ChildList as $ChildRecord)
	{
		$SkipFlag = false;
		if ($ValidTypeSet != false)
		{
			if (isset($ValidTypeSet[$ChildRecord["item_type"]]) == false)
			{
				if ($CrawlNonMatching == "N")
				{
					continue;
				}
				else
				{
					$SkipFlag = true;
				}
			}
		}

		if ($ValidTitleSet != false && $SkipFlag == false)
		{
			$MatchFlag = false;
			foreach($ValidTitleSet as $TitlePattern)
			{
				if (preg_match("/$TitlePattern/",urldecode($ChildRecord["item_title"])) == 1)
				{
					$MatchFlag = true;
					break;
				}
			}

			if ($MatchFlag == false)
			{
				if ($CrawlNonMatching == "N")
				{
					continue;
				}
				else
				{
					$SkipFlag = true;
				}
			}
		}

		$CurrentParent = $ChildRecord["item_id"];
		if ($SkipFlag == false)
		{
			$OutList[] = $ChildRecord["item_id"];
		}

		ftree_list_recursive($DBHandle,$CurrentParent,$OutList,$ValidTypeSet,$ValidTitleSet,$CrawlNonMatching,$MaxDepth,$CurrentDepth + 1);
	}

	unset($ChildList);
	return(true);
}

// Recursively list all items in a tree entry, and all child items, brief output
// ---------------------------------------------------------------
function ftree_list_recursive_brief($DBHandle,$ParentItem,&$OutList,$ValidTypeSet,$CrawlNonMatching,$MaxDepth = 0,$CurrentDepth = 0)
{

	if ($MaxDepth >= 0)
	{
		if ($CurrentDepth > $MaxDepth)
		{
			return(true);
		}
	}

	$ChildList = ftree_query_ext($DBHandle,"SELECT item_id,item_type,item_parent FROM ftree FORCE INDEX (childidx) WHERE item_parent=$ParentItem;");
	if ($ChildList == false)
	{
		return(true);
	}

	foreach($ChildList as $ChildRecord)
	{
		$SkipFlag = false;
		if ($ValidTypeSet != false)
		{
			if (isset($ValidTypeSet[$ChildRecord["item_type"]]) == false)
			{
				if ($CrawlNonMatching == "N")
				{
					continue;
				}
				else
				{
					$SkipFlag = true;
				}
			}
		}

		$CurrentParent = $ChildRecord["item_id"];
		if ($SkipFlag == false)
		{
			$OutList[] = $ChildRecord["item_id"];
		}

		ftree_list_recursive_brief($DBHandle,$CurrentParent,$OutList,$ValidTypeSet,$CrawlNonMatching,$MaxDepth,$CurrentDepth + 1);
	}

	unset($ChildList);
	return(true);
}

// Delete all data for a given item
// --------------------------------
function ftree_delete_single_item($DBHandle,$ItemID,$DeleteStorage = false)
{
	mysqli_query($DBHandle,"DELETE FROM ftree WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM ftree_acl WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE item_parent=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM field_data WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM field_long_data WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM field_data_times WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM ftree_tags WHERE item_id=$ItemID;");
	mysqli_query($DBHandle,"DELETE FROM item_loc WHERE item_id=$ItemID;");
}


// Delete entry, recursive
// -----------------------
function ftree_delete($DBHandle,$ItemID,$DeleteStorage = false)
{
	// Get a list of all items

	$ItemList = array();
	ftree_traverse($DBHandle,$ItemID,$ItemList);

	// Add the initial item to the list

	$ItemList[$ItemID] = true;

	// Delete each item in the list

	foreach($ItemList as $LocalID => $LocalData)
	{
		// Delete database information for the item

		ftree_delete_single_item($DBHandle,$LocalID,$DeleteStorage);

		// Get a list of link references to the item, then delete these as well

		$TempList = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_ref='$LocalID' AND item_type='".FTREE_OBJECT_TYPE_LINK."';");
		if ($TempList != false)
		{
			foreach($TempList as $TempRecord)
			{
				ftree_delete_single_item($DBHandle,$TempRecord["item_id"],$DeleteStorage);
			}
		}
	}
	
	return(true);
}

// Clean up any orphaned data entry items
// --------------------------------------
function ftree_delete_data_entry_orphans($DBHandle)
{
	// Get all marked ID values

	$Result = ftree_query_ext($DBHandle,"SELECT DISTINCT item_id FROM data_entry_queue;");
	if ($Result != false)
	{
		foreach($Result as $Record)
		{
			$ItemID = $Record["item_id"];

			// See if item exists in tree

			$TempResult = ftree_query_ext($DBHandle,"SELECT item_id FROM ftree WHERE item_id=$ItemID LIMIT 1;");

			// If not, delete from data entry queue

			if ($TempResult == false)
			{
				mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE item_id=$ItemID;");
			}
		}
	}

	return(true);
}

// Set property
// ------------
function ftree_set_property($DBHandle,$ItemID,$PropName,$PropValue,$ReplaceFlag = true)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_prop WHERE item_id=$ItemID AND property_name='$PropName';");
	if ($ReplaceFlag == false && $ResultList != false)
	{
		return(false);
	}

	if ($ResultList == false)
	{
		mysqli_query($DBHandle,"INSERT INTO ftree_prop (item_id,property_name,property_value) VALUES ($ItemID,'$PropName','$PropValue');");
	}
	else
	{
		mysqli_query($DBHandle,"UPDATE ftree_prop SET property_value='$PropValue' WHERE item_id=$ItemID AND property_name='$PropName';");
	}

	return(true);
}

// Get property
// ------------
function ftree_get_property($DBHandle,$ItemID,$PropName)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_prop WHERE item_id=$ItemID AND property_name='$PropName';");
	if ($ResultList == false)
	{
		return(false);
	}

	$Record = $ResultList[0];
	return($Record['property_value']);
}

// Get all properties with a given name, optionally filtered by value
// ------------------------------------------------------------------
function ftree_get_all_property_values($DBHandle,$PropName,$PropValue = false)
{
	if ($PropValue === false)
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_prop WHERE property_name='$PropName';");
	}
	else
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_prop WHERE property_name='$PropName' AND property_value='$PropValue';");
	}

	if ($ResultList == false)
	{
		return(false);
	}

	return($ResultList);
}
	
// Delete property
// ---------------
function ftree_delete_property($DBHandle,$ItemID = false,$PropName = false)
{
	if ($ItemID === false)
	{
		return(true);
	}

	if ($PropName === false)
	{
		mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE item_id=$ItemID;");
		return(true);
	}

	mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE item_id=$ItemID AND property_name='$PropName';");
	return(true);
}


// List properties
// ---------------
function ftree_list_properties($DBHandle,$ItemID,$GetLongFlag = false)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT property_name,property_value FROM ftree_prop USE INDEX (propnameidx) WHERE item_id=$ItemID;");
	if ($ResultList === false)
	{
		return(array());
	}

	if (count($ResultList) < 1)
	{
		return(array());
	}

	$OutList = array_column($ResultList,"property_value","property_name");
/*
	$OutList = array();
	if ($ResultList != false)
	{
		foreach($ResultList as $Record)
		{
			$OutList[$Record['property_name']] = $Record['property_value'];
		}
	}
*/

	if ($GetLongFlag !== false)
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT property_name,property_value FROM ftree_long_prop USE INDEX (propnameindex) WHERE item_id='$ItemID';");
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				$OutList[$Record['property_name']] = $Record['property_value'];
			}
		}
	}

	return($OutList);
}

// List properties
// ---------------
function ftree_list_properties_brief($DBHandle,$ItemID,$GetLongFlag = false)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT property_name,property_value FROM ftree_prop WHERE item_id=$ItemID;");
	$OutList = array();
	if ($ResultList != false)
	{
		foreach($ResultList as $Record)
		{
			$OutList[$Record['property_name']] = $Record['property_value'];
		}
	}

	if ($GetLongFlag !== false)
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT property_name,property_value FROM ftree_long_prop WHERE item_id='$ItemID';");
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				$OutList[$Record['property_name']] = $Record['property_value'];
			}
		}
	}

	return($OutList);
}

// Set property
// ------------
function ftree_set_long_property($DBHandle,$ItemID,$PropName,$PropValue,$ReplaceFlag = true)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_long_prop WHERE item_id=$ItemID AND property_name='$PropName';");
	if ($ReplaceFlag == false && $ResultList != false)
	{
		return(false);
	}

	if ($ResultList == false)
	{
		mysqli_query($DBHandle,"INSERT INTO ftree_long_prop (item_id,property_name,property_value) VALUES ($ItemID,'$PropName','$PropValue');");
	}
	else
	{
		mysqli_query($DBHandle,"UPDATE ftree_long_prop SET property_value='$PropValue' WHERE item_id=$ItemID AND property_name='$PropName';");
	}

	return(true);
}

// Get property
// ------------
function ftree_get_long_property($DBHandle,$ItemID,$PropName)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_long_prop WHERE item_id=$ItemID AND property_name='$PropName';");
	if ($ResultList == false)
	{
		return(false);
	}

	$Record = $ResultList[0];
	return($Record['property_value']);
}

// Get all properties with a given name, optionally filtered by value
// ------------------------------------------------------------------
function ftree_get_all_long_property_values($DBHandle,$PropName,$PropValue = false)
{
	if ($PropValue === false)
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_long_prop WHERE property_name='$PropName';");
	}
	else
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_long_prop WHERE property_name='$PropName' AND property_value='$PropValue';");
	}

	if ($ResultList == false)
	{
		return(false);
	}

	return($ResultList);
}
	
// Delete property
// ---------------
function ftree_delete_long_property($DBHandle,$ItemID,$PropName = false)
{
	if ($PropName === false)
	{
		mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE item_id=$ItemID;");
		return(true);
	}

	mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE item_id=$ItemID AND property_name='$PropName';");
	return(true);
}


// List properties
// ---------------
function ftree_list_long_properties($DBHandle,$ItemID)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_long_prop WHERE item_id=$ItemID;");
	$OutList = array();
	if ($ResultList != false)
	{
		foreach($ResultList as $Record)
		{
			$OutList[$Record['property_name']] = $Record['property_value'];
		}
	}

	return($OutList);
}

// Set permission.  Merges permission into existing permissions.
// -------------------------------------------------------------
function ftree_set_permission($DBHandle,$ItemID,$UserID,$GroupID,$PermissionString)
{
	// Must be for user OR group, not both

	if ($UserID >= 0 && $GroupID >= 0)
	{
		return(false);
	}

	// Get any existing permissions and create array

	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE item_id=$ItemID AND user_id=$UserID AND group_id=$GroupID;");
	$PermissionMatrix = array();
	$HasPermissionRecord = false;
	if ($ResultList != false)
	{
		$Record = $ResultList[0];
		$LocalList = str_split($Record["item_rights"]);
		foreach($LocalList as $Value)
		{
			$PermissionMatrix[$Value] = true;
		}

		$HasPermissionRecord = true;
	}

	$LocalList = str_split($PermissionString);
	foreach($LocalList as $Value)
	{
		$PermissionMatrix[$Value] = true;
	}

	$Permissions = join("",array_keys($PermissionMatrix));
	if ($HasPermissionRecord == false)
	{
		mysqli_query($DBHandle,"INSERT INTO ftree_acl (item_id,user_id,group_id,item_rights) VALUES ($ItemID,$UserID,$GroupID,'$Permissions');");
	}
	else
	{
		mysqli_query($DBHandle,"UPDATE ftree_acl SET item_rights='$Permissions' WHERE item_id=$ItemID AND user_id=$UserID AND group_id=$GroupID;");
	}

	return(true);
}

// Unset permission
// ----------------
function ftree_unset_permission($DBHandle,$ItemID,$UserID,$GroupID,$PermissionString)
{
	// Can only use user OR group ID

	if ($UserID >= 0 && $GroupID >= 0)
	{
		return(false);
	}

	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE item_id=$ItemID AND user_id=$UserID and group_id=$GroupID;");
	$PermissionMatrix = array();
	$HasPermissionRecord = false;
	if ($ResultList != false)
	{
		$Record = $ResultList[0];
		$LocalList = str_split($Record["item_rights"]);
		foreach($LocalList as $Value)
		{
			$PermissionMatrix[$Value] = true;
		}

		$HasPermissionRecord = true;
	}

	$LocalList = str_split($PermissionString);
	foreach($LocalList as $Value)
	{
		if (isset($PermissionMatrix[$Value]) == true)
		{
			unset($PermissionMatrix[$Value]);
		}
	}

	if (count(array_keys($PermissionMatrix)) < 1)
	{
		mysqli_query($DBHandle,"DELETE FROM ftree_acl WHERE item_id=$ItemID AND user_id=$UserID AND group_id=$GroupID;");
		return(true);
	}

	$Permissions = join("",array_keys($PermissionMatrix));
	if ($HasPermissionRecord == false)
	{
		mysqli_query($DBHandle,"INSERT INTO ftree_acl (item_id,user_id,group_id,item_rigts) VALUES ($ItemID,$UserID,$GroupID,'$Permissions');");
	}
	else
	{
		mysqli_query($DBHandle,"UPDATE ftree_acl SET item_rights='$Permissions' WHERE item_id=$ItemID AND user_id=$UserID AND group_id=$GroupID;");
	}

	return(true);
}

// Get all permission records for an item
// --------------------------------------
function ftree_get_all_permissions($DBHandle,$ItemID)
{
	$OutData = array("user" => array(), "group" => array());
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE item_id=$ItemID;");
	if ($ResultList == false)
	{
		$OutData[$ItemID] = array();
		return($OutData);
	}

	foreach($ResultList as $Record)
	{
		$LocalID = $Record['item_id'];
		$LocalUser = $Record['user_id'];
		$LocalGroup = $Record['group_id'];
		$LocalList = str_split($Record["item_rights"]);
		$PermissionMatrix = array();
		foreach($LocalList as $Value)
		{
			$PermissionMatrix[$Value] = true;
		}

		if ($LocalUser >= 0)
		{
			if (isset($OutData["user"][$LocalUser]) == false)
			{
				$OutData["user"][$LocalUser] = $PermissionMatrix;
			}
		}
		else
		{
			if (isset($OutData["group"][$LocalGroup]) == false)
			{
				$OutData["group"][$LocalGroup] = $PermissionMatrix;
			}
		}

	}

	return($OutData);
}

// Get explicit permissions for a user/group
// -----------------------------------------
function ftree_get_explicit_permissions($DBHandle,$ItemID,$UserID,$GroupID)
{
	$OutData = array("user" => array(), "group" => array());
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE item_id=$ItemID AND user_id=$UserID AND group_id=$GroupID;");
	if ($ResultList == false)
	{
		return($OutData);
	}

	foreach($ResultList as $Record)
	{
		$LocalID = $Record['item_id'];
		$LocalUser = $Record['user_id'];
		$LocalGroup = $Record['group_id'];
		$LocalList = str_split($Record["item_rights"]);
		$PermissionMatrix = array();
		foreach($LocalList as $Value)
		{
			$PermissionMatrix[$Value] = true;
		}

		if ($LocalUser >= 0)
		{
			if (isset($OutData["user"][$LocalUser]) == false)
			{
				$OutData["user"][$LocalUser] = $PermissionMatrix;
			}
		}
		else
		{
			if (isset($OutData["group"][$LocalGroup]) == false)
			{
				$OutData["group"][$LocalGroup] = $PermissionMatrix;
			}
		}
	}

	return($OutData);
}

// Permission string to matrix
// ---------------------------
function ftree_permissions_to_matrix($InString)
{
	$OutData = array();
	$LocalList = str_split($InString);
	foreach($LocalList as $Code)
	{
		$OutData[$Code] = true;
	}

	return($OutData);
}

// Merge permissions matrices
// --------------------------
function ftree_merge_matrices($MatrixOne,$MatrixTwo)
{
	if ($MatrixOne == false)
	{
		$OutMatrix = array();
	}
	else
	{
		$OutMatrix = $MatrixOne;
	}

	if ($MatrixTwo != false)
	{
		foreach($MatrixTwo as $Code => $Flag)
		{
			$OutMatrix[$Code] = true;
		}
	}

	return($OutMatrix);
}

// Given an item, return all of the specific permissions
// for a single item for all groups in a list, merged as one permissions matrix.
//
// The group matrix is an assoc array, where the key is the 
// group ID.  The output is a permissions matrix (assoc array)
// where the key is the permission granted.  A special "_count"
// key indicates if any records were found.  If there are no
// permissions set, the "_count" value will be one but there
// will be no permissions keys set.  If the "_count" value is
// zero, it means that no records were found for any group in the
// group matrix.
// --------------------------------------------------------------
function ftree_get_merged_group_permissions($DBHandle,$GroupMatrix,$ItemID)
{
	$OutMatrix = array("_count" => 0);
	foreach($GroupMatrix as $GroupID => $Flag)
	{
		// Get ACL entry, if any, for the group and item

		$ACLList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE group_id=$GroupID AND user_id=-1;");
		if ($ACLList != false)
		{
			$ACLRecord = $ACLList[0];
			$OutMatrix['_count']++;
			$OutMatrix = ftree_merge_matrices($OutMatrix,ftree_permissions_to_matrix($ACLRecord["item_rights"]));
		}
	}

	return($OutMatrix);
}

// Given an item, return the permissions based on ACL entries
// ----------------------------------------------------------
function ftree_get_permissions($DBHandle,$UserID,$ItemID,$Inherited = false)
{
	// Get the groups in which the user is a member

	$GroupList = ftree_list_group_membership($DBHandle,$UserID);
	if ($GroupList == false)
	{
		$GroupList = array();
	}

	$GroupMatrix = array();
	foreach($GroupList as $GroupRecord)
	{
		$GroupMatrix[$GroupRecord['group_id']] = true;
	}

	if ($Inherited == false)
	{
		$ItemList = array($ItemID);
	}
	else
	{
		$ItemList = ftree_get_item_id_path($DBHandle,$ItemID);
	}

	$PermissionsMatrix = array();
	foreach($ItemList as $LocalItem)
	{
		// Get item profile

		$ItemRecord = ftree_get_item($DBhandle,$ItemID);
		$OwnerGroup = $ItemRecord["item_group"];
		$OwnerUser = $ItemRecord["item_user"];

		// If there's a specific entry for the user, use that.

		$TempList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE item_id=$ItemID AND user_id=$UserID AND group_id=-1;");
		if ($TempList != false)
		{
			$PermissionsMatrix = ftree_permissions_to_matrix($TempList[0]["item_rights"]);
		}
		else
		{

			// Get the permissions based on group membership.  If any are used, return those.

			$GroupPermMatrix = ftree_get_merged_group_permissions($DBHandle,$GroupMatrix,$ItemID);
			if ($GroupPermMatrix["_count"] > 0)
			{
				unset($GroupPermMatrix["_count"]);
				$PermissionsMatrix = $GroupPermMatrix;
			}
			else
			{
				// Failing all, if the user matches the owner user, and the user is a member
				// of the group that owns the item, full access is granted.

				if ($UserID == $OwnerUser)
				{
					if (isset($GroupMatrix[$OwnerGroup]) == true)
					{
						$PermissionsMatrix = ftree_permissions_to_matrix("RMWCODPN");
					}
				}
			}
		}
	}

	return($PermissionsMatrix);
}

// Encode a password
// -----------------
function ftree_encode_password($InPass)
{
	$LocalPass = md5($InPass);
	for ($Counter = 0; $Counter < 20; $Counter++)
	{
		$LocalPass = md5($LocalPass);
	}

	return($LocalPass);
}

// Create user
// -----------
function ftree_create_user($DBHandle,$UserID,$UserType,$UserLogin,$UserPass,$UserTitle,$UserPrimaryGroup,$UserTopFolder)
{
	// Make sure user doesn't exist

	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_login='$UserLogin';");
	if ($ResultList != false)
	{
		return(array("ERROR","DUPLOGIN"));
	}

	if ($UserID >= 0)
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_id=$UserID;");
		if ($ResultList != false)
		{
			return(array("ERROR","DUPID"));
		}
	}
	else
	{
		while(true)
		{
			$UserID = time();
			$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_id=$UserID;");
			if ($ResultList != false)
			{
				usleep(1000000 + posix_getpid());
				continue;
			}

			break;
		}
	}

	$EncodedPass = ftree_encode_password($UserPass);
	$Result = mysqli_query($DBHandle,"INSERT INTO ftree_user (user_id,user_login,user_type,user_pass,user_title,user_primary_group,user_top_folder) VALUES ($UserID,'$UserLogin','$UserType','$EncodedPass','$UserTitle',$UserPrimaryGroup,$UserTopFolder);");
	if ($Result == false)
	{
		return(array("ERROR",mysqli_error($DBHandle)." --- "."INSERT INTO ftree_user (user_id,user_login,user_type,user_pass,user_title,user_primary_group,user_top_folder) VALUES ($UserID,'$UserLogin','$UserType','$EncodedPass','$UserTitle',$UserPrimaryGroup,$UserTopFolder);"));
	}

	return(array("OK",$UserID));
}

// Update user
// Update info is an assoc array with keys:
//	password
//	name
//	login
// ---------------------------------------------
function ftree_update_user($DBHandle,$UserID,$Info)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_id=$UserID;");
	if ($ResultList == false)
	{
		return(false);
	}

	foreach($Info as $Name => $Value)
	{
		switch($Name)
		{
			case "password":
				$NewPass = ftree_encode_password($Value);
				mysqli_query($DBHandle,"UPDATE ftree_user SET user_pass='$NewPass' WHERE user_id=$UserID;");
				break;

			case "name":
				mysqli_query($DBHandle,"UPDATE ftree_user SET user_title='$Value' WHERE user_id=$UserID;");
				break;

			case "login":

				// Check for dup

				$LocalList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_login='$Value';");
				if ($LocalList != false)
				{
					return(false);
				}

				mysqli_query($DBHandle,"UPDATE ftree_user SET user_login='$Value' WHERE user_id=$UserID;");
				break;

			case "top_folder":
				mysqli_query($DBHandle,"UPDATE ftree_user SET user_top_folder='$Value' WHERE user_id=$UserID;");
				break;

			default:
				break;
		}
	}

	return(true);
}

// Delete user
// -----------
function ftree_delete_user($DBHandle,$UserID)
{
	mysqli_query($DBHandle,"DELETE FROM ftree_user WHERE user_id=$UserID;");
	return(true);
}

// Given user ID, return record
// ----------------------------
function ftree_get_user($DBHandle,$UserID)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_id=$UserID;");
	if ($ResultList == false)
	{
		return(false);
	}

	return($ResultList[0]);
}


// Given login ID, return user ID
// ------------------------------
function ftree_get_user_id_from_login($DBHandle,$Login)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT user_id FROM ftree_user WHERE user_login='$Login';");
	if ($ResultList == false)
	{
		return(false);
	}

	$Record = $ResultList[0];
	return($Record["user_id"]);
}

// List user(s)
// ------------
function ftree_list_users($DBHandle,$UserType = false, $UserTitleFilter = false, $Start = false, $End = false)
{
	if ($Start !== false && $End !== false)
	{
		$StartEnd = " LIMIT $Start,$End";
	}
	else
	{
		$StartEnd = "";
	}

	if ($UserType == false)
	{
		if ($UserTitleFilter == false)
		{
			$ReturnList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user ORDER BY user_title $StartEnd;");
		}
		else
		{
			$ReturnList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_title LIKE '%$UserTitleFilter%' ORDER BY user_title $StartEnd;");
		}
	}
	else
	{
		if ($UserTitleFilter == false)
		{
			$ReturnList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_type='$UserType' ORDER BY user_title $StartEnd;");
		}
		else
		{
			$ReturnList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_type='$UserType' AND user_title LIKE '%$UserTitleFilter%' ORDER BY user_title $StartEnd;");
		}
	}

	return($ReturnList);
}

// List users who have a specific parent folder
// --------------------------------------------
function ftree_list_users_for_parent($DBHandle,$ParentFolder,$UserType = false, $UserTitleFilter = false, $Start = false, $End = false)
{
	if ($UserTitleFilter != false)
	{
		$TitleFilter = " AND user_title LIKE '%$UserTitleFilter%'";
	}
	else
	{
		$TitleFilter = "";
	}

	if ($Start !== false && $End !== false)
	{
		$StartEnd = " LIMIT $Start,$End";
	}
	else
	{
		$StartEnd = "";
	}

	if ($UserType == false)
	{
		$OutList = ftree_query($DBHandle,"SELECT * FROM ftree_user WHERE user_top_folder=$ParentFolder $TitleFilter ORDER BY user_title $StartEnd;");
	}
	else
	{
		$OutList = ftree_query($DBHandle,"SELECT * FROM ftree_user WHERE user_top_folder=$ParentFolder AND user_type='$UserType' $TitleFilter ORDER BY user_title $StartEnd;");
	}

	return($OutList);
}

// Add user as a member of a group
// -------------------------------
function ftree_add_member($DBHandle,$GroupID,$UserID)
{
	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_member WHERE group_id=$GroupID AND user_id=$UserID;");
	if ($ResultList != false)
	{
		return(true);
	}

	mysqli_query($DBHandle,"INSERT INTO ftree_member (group_id,user_id) VALUES ($GroupID, $UserID);");
	return(true);
}

// Remove user from membership
// ---------------------------
function ftree_remove_member($DBHandle,$GroupID,$UserID)
{
	mysqli_query($DBHandle,"DELETE FROM ftree_member WHERE group_id=$GroupID AND user_id=$UserID;");
	return(true);
}

// List all members in a membership group
// --------------------------------------
function ftree_list_members($DBHandle,$GroupID)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_member WHERE group_id=$GroupID;");
	return($TestList);
}

// List all groups in which a user occurs
// --------------------------------------
function ftree_list_group_membership($DBHandle,$UserID)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_member WHERE user_id=$UserID;");
	return($TestList);
}

// Get user profile by login ID
// ----------------------------
function ftree_get_user_by_login($DBHandle,$UserLogin)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_login='$UserLogin';");
	if ($TestList != false)
	{
		return($TestList[0]);
	}

	return(false);
}

// Set user property
// -----------------
function ftree_set_user_prop($DBHandle,$UserID,$PropName,$PropValue)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user_prop WHERE user_id=$UserID AND property_name='$PropName';");
	if ($TestList != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree_user_prop SET property_value='$PropValue' WHERE user_id=$UserID AND property_name='$PropName';");
	}
	else
	{
		mysqli_query($DBHandle,"INSERT INTO ftree_user_prop (user_id,property_name,property_value) VALUES ($UserID,'$PropName','$PropValue');");
	}

	return(true);
}

// Get user property
// -----------------
function ftree_get_user_prop($DBHandle,$UserID,$PropName)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user_prop WHERE user_id=$UserID AND property_name='$PropName';");
	if ($TestList != false)
	{
		$Value = $TestList[0]["property_value"];
		return($Value);
	}

	return(false);
}

// List user properties
// --------------------
function ftree_list_user_prop($DBHandle,$UserID)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user_prop WHERE user_id=$UserID;");
	$OutData = array();
	if ($TestList == false)
	{
		$TestList = array();
	}

	foreach($TestList as $Record)
	{
		$OutData[$Record['property_name']] = $Record['property_value'];
	}

	return($OutData);
}

// Delete user property
// --------------------
function ftree_delete_user_prop($DBHandle,$UserID,$PropName)
{
	mysqli_query($DBHandle,"DELETE FROM ftree_user_prop WHERE user_id=$UserID AND property_name='$PropName';");
	return(true);
}

// Given user ID and password, return true/false on valid
// ------------------------------------------------------
function ftree_check_user($DBHandle,$UserLogin,$Password)
{
	$LocalPass = md5($Password);
	for ($Counter = 0; $Counter < 20; $Counter++)
	{
		$LocalPass = md5($LocalPass);
	}

	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_login='$UserLogin';");
	if ($TestList == false)
	{
		return(false);
	}

	$Record = $TestList[0];
	if ($Record['user_pass'] != $LocalPass)
	{
		return(false);
	}

	return(true);
}

// Get list of user id values for groups for a user
// ------------------------------------------------
function ftree_get_user_group_id_list($DBHandle,$UserID)
{
	// Get the list of groups in which this user is a member

	$GroupList = array();
	$UserGroupRecordList = ftree_list_group_membership($DBHandle,$UserID);
	if ($UserGroupRecordList == false)
	{
		return(array());
	}

	foreach($UserGroupRecordList as $UserRecord)
	{
		$GroupList[] = $UserRecord['group_id'];
	}

	return($GroupList);
}

// Set group property
// -----------------
function ftree_set_group_prop($DBHandle,$GroupID,$PropName,$PropValue)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group_prop WHERE group_id=$GroupID AND property_name='$PropName';");
	if ($TestList != false)
	{
		mysqli_query($DBHandle,"UPDATE ftree_group_prop SET property_value='$PropValue' WHERE group_id=$GroupID AND property_name='$PropName';");
	}
	else
	{
		mysqli_query($DBHandle,"INSERT INTO ftree_group_prop (group_id,property_name,property_value) VALUES ($GroupID,'$PropName','$PropValue');");
	}

	return(true);
}

// Get group property
// -----------------
function ftree_get_group_prop($DBHandle,$GroupID,$PropName)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group_prop WHERE group_id=$GroupID AND property_name='$PropName';");
	if ($TestList != false)
	{
		$Value = $TestList[0]["property_value"];
		return($Value);
	}

	return(false);
}

// Get count for query
// -------------------
function ftree_query_count($DBHandle,$TableName,$WhereClause = false)
{
	if ($WhereClause == false)
	{
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT count(*) FROM $TableName;");
	}
	else
	{
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT count(*) FROM $TableName WHERE $WhereClause;");
	}

	if ($Result == false)
	{
		return(0);
	}

	$Row = mysqli_fetch_row($Result);
	mysqli_free_result($Result);
	return($Row[0]);
}

// List group properties
// --------------------
function ftree_list_group_prop($DBHandle,$GroupID)
{
	$TestList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group_prop WHERE group_id=$GroupID;");
	$OutData = array();
	foreach($TestList as $Record)
	{
		$OutData[$Record['property_name']] = $Record['property_value'];
	}

	return($OutData);
}

// Delete group property
// --------------------
function ftree_delete_group_prop($DBHandle,$GroupID,$PropName)
{
	mysqli_query($DBHandle,"DELETE FROM ftree_group_prop WHERE group_id=$GroupID AND property_name='$PropName';");
	return(true);
}

// Create a group
// --------------
function ftree_create_group($DBHandle,$GroupID,$GroupName,$GroupOwner)
{
	// Check for duplicate name

	$Result = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group WHERE group_title='$GroupName';");
	if ($Result != false)
	{
		return(array("ERROR","DUPLICATE GROUP NAME"));
	}

	// Check for duplicate ID

	$LocalGroupID = -1;
	if ($GroupID >= 0)
	{
		$Result = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group WHERE group_id=$GroupID;");
		if ($Result != false)
		{
			return(array("ERROR","DUPLICATE GROUP ID"));
		}

		$LocalGroupID = $GroupID;
	}
	else
	{
		while(true)
		{
			$LocalGroupID = time();
			$Result = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group WHERE group_id=$LocalGroupID;");
			if ($Result == false)
			{
				break;
			}

			sleep(1);
		}
	}

	// Add group

	$Result = mysqli_query($DBHandle,"INSERT INTO ftree_group (group_id,group_title,group_owner) VALUES ($LocalGroupID,'$GroupName',$GroupOwner);");
	if ($Result == false)
	{
		return(array("ERROR",mysqli_error($DBHandle)));
	}

	return(array("OK",$LocalGroupID));
}

// Get group by name
// -----------------
function ftree_get_group_by_title($DBHandle,$GroupName)
{
	$Result = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group WHERE group_title='$GroupName';");
	return($Result);
}

// Get group by ID
// ---------------
function ftree_get_group_by_id($DBHandle,$GroupID)
{
	$Result = ftree_query_ext($DBHandle,"SELECT * FROM ftree_group WHERE group_id=$GroupID;");
	return($Result);
}

// Delete group
// Group can't be deleted if any users are a member or if it is the default group for a user account
// -------------------------------------------------------------------------------------------------
function ftree_delete_group($DBHandle,$GroupID)
{
	// Check for members using the ID

	$Count = ftree_query_count($DBHandle,"ftree_member","group_id=$GroupID");
	if ($Count > 0)
	{
		return(false);
	}

	// Check for users using the ID

	$Count = ftree_query_count($DBHandle,"ftree_user","user_primary_group=$GroupID");
	if ($Count > 0)
	{
		return(false);
	}

	mysqli_query($DBHandle,"DELETE FROM ftree_group WHERE group_id=$GroupID;");
	return(true);
}

// Get users matching property or property and value
// -------------------------------------------------
function ftree_users_with_prop($DBHandle,$PropertyName,$PropertyValue)
{
	if ($PropertyName == false)
	{
		return(array());
	}

	$Query = "SELECT * from ftree_user_prop WHERE property_name='$PropertyName'";
	if ($PropertyValue != false)
	{
		$Query .= " AND property_value='$PropertyValue'";
	}

	$Query .= ";";
	$Result = ftree_query_ext($DBHandle,$Query);
	if ($Result == false)
	{
		return(array());
	}

	return($Result);
}

// ==================
// DATA ENTRY BATCHES
// ==================

// Given a parent folder, get the list of items marked for data entry
// ------------------------------------------------------------------
function ftree_data_entry_get_marked_in_parent($DBHandle,$ParentID)
{
	$Result = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_parent_id=$ParentID;");
	if ($Result == false)
	{
		return(array());
	}

	return($Result);
}


// Given a parent folder, get the list of items marked for data entry that have not been completed
// -----------------------------------------------------------------------------------------------
function ftree_data_entry_not_complete($DBHandle,$ParentID)
{
	$Result = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_parent_id=$ParentID AND entry_completed <= 0;");
	if ($Result == false)
	{
		return(array());
	}

	return($Result);
}

// Given a parent folder, get the list of items marked for data entry and completed
// --------------------------------------------------------------------------------
function ftree_data_entry_complete($DBHandle,$ParentID)
{
	$Result = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_parent_id=$ParentID AND entry_completed > 0;");
	if ($Result == false)
	{
		return(array());
	}

	return($Result);
}


// Check to see if an item has been marked for data entry and if so, the user.
// ---------------------------------------------------------------------------
function ftree_check_data_entry($DBHandle,$ItemID)
{
	$Result = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_id=$ItemID AND entry_completed <= 0;");
	if ($Result != false)
	{
		$Record = $Result[0];
		return(array("user_id" => $Record["user_id"], "entry_assigned" => $Record["entry_assigned"]));
	}

	return(false);
}


// Add item to data entry list.  Do not add if already in list and not completed.
// ------------------------------------------------------------------------------
function ftree_mark_for_data_entry($DBHandle,$ItemID,$UserID)
{
	// See if entry is already present

	$CheckList = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_id=$ItemID;");
	if ($CheckList != false)
	{
		$CheckRecord = $CheckList[0];
		if ($CheckRecord["entry_completed"] <= 0)
		{
			return(array("status" => "IN USE", "user" => $CheckRecord["user_id"], "entry_assigned" => $CheckRecord["entry_assigned"]));
		}
	}

	// Delete any existing entry.  If the entry already exists, indicate history

	$ExistingEntry = false;
	if ($CheckList != false)
	{
		$CheckRecord = $CheckList[0];
		$LastUser = $CheckRecord["user_id"];
		$LastCompleted = $CheckRecord["entry_completed"];
		$LastAssigned = $CheckRecord["entry_assigned"];
		$ExistingEntry = true;
	}
	else
	{
		$LastUser = false;
		$LastCompleted = false;
		$LastAssigned = false;
	}

	// Get the item parent

	$Result = ftree_query_ext($DBHandle,"SELECT * FROM ftree WHERE item_id=$ItemID;");
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "NOT FOUND"));
	}

	$ParentRecord = $Result[0];
	$ParentID = $ParentRecord["item_id"];

	// Add entry or modify existing

	$AssignStamp = time();
	if ($ExistingEntry == false)
	{
		$Query = "INSERT INTO data_entry_queue (item_id,user_id,entry_assigned,entry_completed,item_parent_id) VALUES (".
			"$ItemID,$UserID,$AssignStamp,-1,$ParentID);";
		$InsertResult = mysqli_query($DBHandle,$Query);
		if ($InsertResult == false)
		{
			return(array("status" => "ERROR", "msg" => "DB ERROR", "error" => mysqli_error($DBHandle)));
		}
	}
	else
	{
		$Query = "UPDATE data_entry_queue SET user_id=$UserID,entry_assigned=$AssignStamp,entry_completed=0,item_parent_id=$ParentID WHERE item_id=$ItemID;";
		mysqli_query($DBHandle,$Query);
	}

	return(array("status" => "OK"));
}

// Add folder content to data entry list.  Returns the number of items marked and number already assigned.
//
// If "Override" is true, then existing entries will be updated with new user.
// If the UserID is -1, then existing entries will be removed.
// -------------------------------------------------------------------------------------------------------
function ftree_mark_folder_for_data_entry($DBHandle,$ParentID,$UserID,$TypePropertyName,$ChildType,$Override = true)
{
	// Get the list of child items

	$ChildList = ftree_list_child_objects($DBHandle,$ParentID,false,false,FTREE_OBJECT_TYPE_FOLDER,false,false);

	// If FALSE return or nothing in list, error.

	if ($ChildList == false)
	{
		return(array("status" => "ERROR", "msg" => "NO ITEMS"));
	}

	$ChildCount = count($ChildList);
	if ($ChildCount < 1)
	{
		return(array("status" => "ERROR", "msg" => "NO ITEMS"));
	}

	// Process each item

	$MarkedCount = 0;
	$InUseCount = 0;
	$TotalCount = 0;
	foreach($ChildList as $ChildRecord)
	{
		// Get ID and type.  If not the correct type, skip.

		$ChildID = $ChildRecord["item_id"];
		$ChildItemType = ftree_get_property($DBHandle,$ChildID,$TypePropertyName);
		if ($ChildItemType != $ChildType)
		{
			continue;
		}

		// Update total processed count

		$TotalCount++;

		// See if the item is in the batch.

		$CheckList = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_id=$ChildID;");

		// If in batch, check for in-process or finished

		if ($CheckList != false)
		{
			$CheckRecord = $CheckList[0];

			// If the entry isn't completed ("entry_completed" is LE zero), then indicate as in use.

			if ($CheckRecord["entry_completed"] <= 0)
			{
				// Increment in-use count

				$InUseCount++;

				// If override is set to FALSE, and the user ID is not -1, then don't override.

				if ($Override == false && $UserID != -1)
				{
					continue;
				}
			}

			// Set the assignment time

			$AssignStamp = time();

			// Increment number of marked items

			$MarkedCount++;

			// If the user ID isn't -1, then update entry with new user ID, new assignment stamp, reset the entry_completed stamp,
			// and reset the parent ID.  Otherwise, delete the entry from the batch.

			if ($UserID != -1)
			{
				mysqli_query($DBHandle,"UPDATE data_entry_queue SET entry_assigned=$AssignStamp,entry_completed=0,user_id=$UserID,item_parent=$ParentID WHERE item_id=$ChildID;");
			}
			else
			{
				mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE item_id=$ChildID;");
			}
		}
		else
		{
			// Item has never been in batch.  Insert entry if the user ID isn't -1.

			$AssignStamp = time();
			if ($UserID != -1)
			{
				$Query = "INSERT INTO data_entry_queue (item_id,user_id,entry_assigned,entry_completed,item_parent_id) VALUES (".
					"$ChildID,$UserID,$AssignStamp,-1,$ParentID);";
				mysqli_query($DBHandle,$Query);
				$MarkedCount++;
			}
		}
	}

	// Return stats

	return(array("status" => "OK", "total" => $TotalCount, "marked" => $MarkedCount, "in_use" => $InUseCount));
}

// Unmark data entry items for a folder
// -------------------------------------------------------------------------------------------------------
function ftree_unmark_data_entry($DBHandle,$UserID,$ItemID)
{

	// Remove entries from table

	while(true)
	{
		if ($UserID !== false && $ItemID !== false)
		{
			mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE user_id=$UserID AND item_parent_id=$ItemID;");
			break;
		}

		if ($UserID === false)
		{
			mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE item_parent_id=$ItemID;");
			break;
		}

		if ($ItemID === false)
		{
			mysqli_query($DBHandle,"DELETE FROM data_entry_queue WHERE user_id=$ItemID;");
			break;
		}

		break;
	}

	return(true);
}

// Mark a data entry item as completed
// -----------------------------------
function ftree_mark_data_entry_complete($DBHandle,$ItemID,$UserID)
{
	$CheckList = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE item_id=$ItemID;");
	if ($CheckList == false)
	{
		return;
	}

	$TimeStamp = time();
	mysqli_query($DBHandle,"UPDATE data_entry_queue set entry_completed=$TimeStamp WHERE item_id=$ItemID;");
	return;
}

// Given a user ID, return a list of all the items waiting or finished for data entry
// ----------------------------------------------------------------------
function ftree_get_data_entry_items($DBHandle,$UserID,$Waiting = true)
{
	if ($Waiting == true)
	{
		$ReturnList = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE user_id=$UserID AND entry_completed <= 0;");
	}
	else
	{
		$ReturnList = ftree_query_ext($DBHandle,"SELECT * FROM data_entry_queue WHERE user_id=$UserID AND entry_completed > 0;");
	}

	if ($ReturnList == false)
	{
		$ReturnList = array();
	}

	return($ReturnList);
}

// Given a user ID, return a list of the parent items with completed or pending assignments for data entry.
// --------------------------------------------------------------------------------------------------------
function ftree_get_data_entry_parents($DBHandle,$UserID,$Waiting = true)
{
	if ($Waiting == true)
	{
		$ReturnList = ftree_query_ext($DBHandle,"SELECT DISTINCT ftree.item_parent inner join ftree on data_entry_queue.item_id=ftree.item_id FROM data_entry_queue WHERE data_entry_queue.user_id=$UserID AND entry_completed <= 0;");
	}
	else
	{
		$ReturnList = ftree_query_ext($DBHandle,"SELECT DISTINCT ftree.item_parent inner join ftree on data_entry_queue.item_id=ftree.item_id FROM data_entry_queue WHERE data_entry_queue.user_id=$UserID AND entry_completed <= 0;");
	}

	if ($ReturnList == false)
	{
		$ReturnList = array();
	}

	return($ReturnList);
}

// Given an item, get the archive and archive group
// ------------------------------------------------
function ftree_get_archive_and_archive_group($DBHandle,$ItemID,$CacheFlag = false)
{
	$OutData = array("archive" => false, "archive_group" => false);
	$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID,$CacheFlag);
	foreach($IDPathList as $LocalID)
	{
		$FolderType = ftree_get_property($DBHandle,$LocalID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		switch($FolderType)
		{
			case AIB_ITEM_TYPE_ARCHIVE_GROUP:
				$OutData["archive_group"] = ftree_get_item($DBHandle,$LocalID);
				break;

			case AIB_ITEM_TYPE_ARCHIVE:
				$OutData["archive"] = ftree_get_item($DBHandle,$LocalID);
				break;

			default:
				break;
		}

		if ($OutData["archive_group"] !== false && $OutData["archive"] !== false)
		{
			break;
		}
	}

	return($OutData);
}

// Given an item, get the home folder of the tree
// ----------------------------------------------
function ftree_get_item_user_home($DBHandle,$ItemID)
{
	$OutData = array("archive" => false, "archive_group" => false);
	$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
	foreach($IDPathList as $LocalID)
	{
		$TempList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_user WHERE user_top_folder='$LocalID';");
		if ($TempList != false)
		{
			return($TempList[0]);
		}
	}

	return(false);
}

// If option value is "Y" or "YES" or "Yes", set true.  Else, false.
// -----------------------------------------------------------------
function ftree_option_truefalse($OptionValue)
{
	if (preg_match("/[Yy]/",$OptionValue) != false)
	{
		return(true);
	}

	return(false);
}

// Given an item ID and filter set, return match (true) or no match (false)
// ------------------------------------------------------------------------
function ftree_match_item_properties($DBHandle,$ItemID,$Boolean,$PropertySpec)
{
	$PropertyList = ftree_list_properties($DBHandle,$ItemID,true);
	if ($Boolean == "AND")
	{
		foreach($PropertySpec as $PropertyRecord)
		{
			$TempName = $PropertyRecord["name"];
			$TempValue = $PropertyRecord["value"];
			if (isset($PropertyList[$TempName]) == false)
			{
				return(false);
			}

			$LocalValue = urldecode($PropertyList[$TempName]);
			if ($LocalValue != $TempValue)
			{
				return(false);
			}
		}
	
		return(true);
	}

	if ($Boolean == "OR")
	{
		foreach($PropertySpec as $PropertyRecord)
		{
			$TempName = $PropertyRecord["name"];
			$TempValue = $PropertyRecord["value"];
			if (isset($PropertyList[$TempName]) == false)
			{
				continue;
			}

			$LocalValue = urldecode($PropertyList[$TempName]);
			if ($LocalValue == $TempValue)
			{
				return(true);
			}
		}
	
		return(false);
	}

	return(false);
}


// Get a list of child items, with optional detail for each item
// -------------------------------------------------------------
function ftree_list_child_objects_ext($DBHandle,$ParentItem,$Options,$ItemTypes,$BlockedProperties,$FieldDataTypeDesc,$FileClassTypeDesc,$RecordSet = false)
{
	$UserID = false;
	$OutData = array("status" => "OK");
	$CountOnlyFlag = false;
	while(true)
	{
		if ($ParentItem === false && $RecordSet == false)
		{
			$OutData["status"] = "ERROR";
			$OutData["info"] = "MISSINGPARENT";
			break;
		}

		$LinkUserID = get_assoc_default($Options,"link_user_id",false);
		$RejectLinkUserID = get_assoc_default($Options,"reject_link_user_id",false);

		// Get options

		$OptionDerefLinks = ftree_option_truefalse(get_assoc_default($Options,"opt_deref_links","N"));
		$GetPropertyFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_property","N"));
		$GetFilesFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_files","N"));
		$GetFieldFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_field","N"));
		$GetThumbFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_thumb","N"));
		$GetPrimaryFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_primary","N"));
		$GetFirstThumbFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_first_thumb","N"));
		$GetLongFlag = ftree_option_truefalse(get_assoc_default($Options,"opt_get_first_thumb","N"));
		$OptGetPropCount = ftree_option_truefalse(get_assoc_default($Options,"opt_get_prop_count","N"));
		$OptionFilterType = strtoupper(get_assoc_default($Options,"opt_filter_type",false));
		$OptionSelectType = strtoupper(get_assoc_default($Options,"opt_select_type",false));
		$OptionSortOrder = strtoupper(get_assoc_default($Options,"opt_sort","TITLE"));
		$SortOrdersAllowed = array("ID" => true, "TITLE" => true,"STPA" => true);
		$OptionLinkOwner = ftree_option_truefalse(get_assoc_default($Options,"opt_link_owner","N"));
		$OptionFilterLinkType = get_assoc_default($Options,"opt_filter_link_type",false);
		$OptNoLinks = strtoupper(get_assoc_default($Options,"opt_no_links","N"));
		$OptShowLinkHome = strtoupper(get_assoc_default($Options,"opt_show_link_home","N"));
		$OptGetLinkUserProfile = strtoupper(get_assoc_default($Options,"opt_get_link_user_profile","N"));
		if ($OptGetLinkUserProfile == "N")
		{
			$OptGetLinkUserProfile = false;
		}
		else
		{
			$OptGetLinkUserProfile = true;
		}

		if ($OptShowLinkHome == "N")
		{
			$OptShowLinkHome = false;
		}
		else
		{
			$OptShowLinkHome = true;
		}


		if ($OptNoLinks == "N")
		{
			$OptNoLinks = false;
		}
		else
		{
			$OptNoLinks = true;
		}

		$OptionLinkProperties = get_assoc_default($Options,"opt_get_link_properties","N");
		if ($OptionLinkProperties == "N")
		{
			$OptionLinkProperties = false;
		}
		else
		{
			$OptionLinkProperties = true;
		}

		$OptMyLinks = strtoupper(get_assoc_default($Options,"opt_my_links","N"));
		if ($OptMyLinks == "N")
		{
			$OptMyLinks = false;
		}
		else
		{
			$OptMyLinks = true;
		}

		if (isset($SortOrdersAllowed[$OptionSortOrder]) == false)
		{
			$OptionSortOrder = "TITLE";
		}

		if ($OptionSortOrder == "TITLE")
		{
			$SortTitleFlag = true;
			$SortIDFlag = false;
		}
		else
		{
			$SortTitleFlag = false;
			$SortIDFlag = true;
		}

		$PropertyCountDef = array();
		if (preg_match("/[Y]/",$OptGetPropCount) != false)
		{
			if (isset($Options["opt_prop_count_set"]) == false)
			{
				$OptGetPropCount = false;
			}
			else
			{
				$OptGetPropCount = true;
				$PropertyCountDef = json_decode($Options["opt_prop_count_set"],true);
			}
		}
		else
		{
			$OptGetPropCount = false;
		}


		$OutData["info"] = array("records" => array());
		if ($RecordSet == false)
		{
			$ResultList = ftree_list_child_objects($DBHandle,$ParentItem,$UserID,false,false,$CountOnlyFlag,$SortTitleFlag,$SortIDFlag);
		}
		else
		{
			$ResultList = $RecordSet;
		}

		if ($CountOnlyFlag == true)
		{
			if ($RecordSet == false)
			{
				if ($ResultList[0] == "ERROR")
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = $ResultList[1];
					break;
				}

				$OutData["status"] = "OK";
				$OutData["info"] = $ResultList[0];
				break;
			}
			else
			{
				$OutData["status"] = "OK";
				$OutData["info"] = count($ResultList);
			}
		}

		$TotalCount = 0;
		if ($ResultList != false)
		{
			// If sorting by STP name, use that instead

			if ($OptionSortOrder == "STPA")
			{
				// Create a map of records, where the key is the sort name

				$LocalMap = array();
				foreach($ResultList as $TempResultRecord)
				{
					// Get sort name; if none present, use item title followed by ID

					$STPSort = ftree_get_property($DBHandle,$ResultRecord["item_id"],"stp:sort_name");
					if ($STPSort == false)
					{
						$STPSort = urldecode($TempResultRecord["item_title"]).",".$TempResultRecord["item_id"];
					}
					else
					{
						// Suffix the sort name with the item ID so we don't have name collisions (just in case)

						$STPSort .= ",".$TempResultRecord["item_id"];
					}

					$LocalMap[$STPSort] = $TempResultRecord;
				}

				// Get key list of map and sort

				$KeyList = array_keys($LocalMap);
				sort($KeyList);

				// Rebuild result list in the sorted key order

				$ResultList = array();
				foreach($KeyList as $TempKey)
				{
					$ResultList[] = $LocalMap[$TempKey];
				}
			}

			foreach($ResultList as $TempResultRecord)
			{
				// Determine if we're dereferencing links

				$ResultRecord = false;
				$IsLink = false;
				$LinkType = false;
				$LinkData = array();
				$LinkUserHome = false;
				if ($OptionDerefLinks == false)
				{
					$ResultRecord = $TempResultRecord;
					if ($TempResultRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
					{
						$IsLink = true;
						$LinkTarget = $TempResultRecord["item_ref"];
						$LinkType = $TempResultRecord["item_source_type"];
						$LinkData["link_type"] = $LinkType;

					}
				}
				else
				{
					// Dereference based on link source if the record is a link

					if ($TempResultRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
					{
						$LinkType = $TempResultRecord["item_source_type"];
						$LinkData["link_type"] = $LinkType;
						switch($TempResultRecord["item_source_type"])
						{
							// Internal (AIB) link.  Fetch the linked item record.

							case FTREE_SOURCE_TYPE_LINK:
							case FTREE_SOURCE_TYPE_INTERNAL:
								$IsLink = true;
								$LinkTarget = $TempResultRecord["item_ref"];
								$ResultRecord = ftree_get_object_by_id($DBHandle,$LinkTarget);
								if ($OptShowLinkHome == true)
								{
									$TempHome = ftree_get_item_user_home($DBHandle,$TempResultRecord["item_id"]);
									if ($TempHome != false)
									{
										$LinkUserHome = $TempHome;
									}
								}

								break;

							// STP Archive link

							case FTREE_SOURCE_TYPE_STPARCHIVE:
								$IsLink = true;
								$LinkTarget = $TempResultRecord["item_ref"];
								$LinkInfo = json_decode(urldecode($TempResultRecord["item_source_info"]),true);
								switch($LinkInfo["type"])
								{
									// Edition
									case FTREE_STP_LINK_EDITION:
										$LinkData["stp_link_type"] = $LinkInfo["type"];
										$LinkData["stp_url"] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
											$LinkInfo["year"]."/".stp_archive_month_name($LinkInfo["mon"])." ".
											$LinkInfo["day"]."/";
										$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
											$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
										break;

									// Page
									case FTREE_STP_LINK_PAGE:
										$LinkData["stp_link_type"] = $LinkInfo["type"];
										$LinkData["stp_url"] = "www.".STP_ARCHIVE_DOMAIN."/aib_page.php?edition=".
											$LinkInfo["ed"]."&page=".$LinkInfo["pg"];
										$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
											$LinkInfo["ed"]."&page=".$LinkInfo["pg"]."&paper=".$LinkInfo["paper"];
										break;

									// Year
									case FTREE_STP_LINK_YEAR:
										$LinkData["stp_link_type"] = $LinkInfo["type"];
										$LinkData["stp_url"] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
											$LinkInfo["year"];
										$LinkData["stp_thumb"] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
											$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
										break;

									default:
										break;
								}

								$ResultRecord = $TempResultRecord;
								break;

							case FTREE_SOURCE_TYPE_URL:
								$IsLink = true;
								$LinkData["link_url"] = $LinkInfo["url"];
								$ResultRecord = $TempResultRecord;
								break;

							default:
								$ResultRecord = $TempResultRecord;
								break;

						}

						if ($ResultRecord == false)
						{
							continue;
						}

					}
					else
					{
						$ResultRecord = $TempResultRecord;
					}
				}

				// Special link processing

				if ($IsLink == true && $OptionDerefLinks == true)
				{
					if ($OptNoLinks == true)
					{
						continue;
					}

					if ($LinkUserID !== false)
					{
						if ($LinkUserID != $TempResultRecord["item_user_id"])
						{
							continue;
						}
					}

					if ($RejectLinkUserID !== false)
					{
						if ($RejectLinkUserID == $TempResultRecord["item_user_id"])
						{
							continue;
						}
					}

					// If this is a link and we want to filter on the type of link ref parent, do so here.  If not the right
					// type, skip.

					if ($OptionFilterLinkType !== false)
					{
						$LinkTarget = $ResultRecord["item_ref"];
						$EntryTypeProperty = ftree_get_property($DBHandle,$LinkTarget,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
						if ($EntryTypeProperty != $OptionFilterLinkType)
						{
							continue;
						}
					}
				}

				// Determine the record type (collection, archive, etc.)

				$FormID = ftree_field_get_item_form($DBHandle,$ResultRecord["item_id"]);
				if ($FormID == false)
				{
					$FormID = "";
				}

				$EntryTypeProperty = ftree_get_property($DBHandle,$ResultRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				if ($EntryTypeProperty == false)
				{
					$EntryTypeProperty = "";
				}

				if ($OptionFilterType != false)
				{
					if ($EntryTypeProperty == $OptFilterType)
					{
						continue;
					}
				}

				if ($OptionSelectType != false)
				{
					if ($EntryPropertyType != $OptSelectType)
					{
						continue;
					}
				}

				if ($CountOnlyFlag == true)
				{
					$TotalCount++;
					continue;
				}

				if (isset($EntryTypes[$EntryTypeProperty]) == true)
				{
					$LocalEntryType = $EntryTypes[$EntryTypeProperty];
				}
				else
				{
					$LocalEntryType = $EntryTypeProperty;
				}

				if ($LocalEntryType === false || ltrim(rtrim($LocalEntryType)) == "")
				{
					$LocalEntryType = "IT";
				}


//				$LocalEntryType = "IT";
//				if (isset($EntryTypes[$EntryTypeProperty]) == true)
//				{
//					$LocalEntryType = $EntryTypes[$EntryTypeProperty];
//				}
//				else
//				{
//					$LocalEntryType = $EntryTypeProperty;
//				}

				$ItemPropertySet = array();
				if ($GetPropertyFlag == true)
				{
					$PropertyList = ftree_list_properties($DBHandle,$ResultRecord["item_id"],$GetLongFlag);
					if ($PropertyList != false)
					{
						foreach($PropertyList as $PropertyName => $PropertyValue)
						{
							$LocalName = urldecode($PropertyName);
							$LocalValue = urldecode($PropertyValue);
							if (isset($BlockedProperties[$LocalName]) == true)
							{
								continue;
							}

							$ItemPropertySet[$LocalName] = $LocalValue;
						}
					}
				}

				$ItemFieldSet = array();
				$ItemTagSet = array();
				if ($GetFieldFlag == true)
				{
					$FieldList = get_item_fields_in_form_order($DBHandle,$ResultRecord["item_id"],$FormID);
					if ($FieldList != false)
					{
						foreach($FieldList as $FieldInfo)
						{
							$LocalDef = $FieldInfo["def"];
							$FieldID = $LocalDef["field_id"];
							$LocalType = "TEXT";
							if (isset($FieldDataTypeDesc[$LocalDef["field_data_type"]]) == true)
							{
								$LocalType = $FieldDataTypeDesc[$LocalDef["field_data_type"]];
							}

							$TempRecord = array(
								"field_id" => $FieldID,
								"field_value" => urldecode($FieldInfo["value"]),
								"field_title" => urldecode($LocalDef["field_title"]),
								"field_size" => $LocalDef["field_size"],
								"field_data_type" => $LocalType,
								"field_format" => urldecode($LocalDef["field_format"]),
								"field_symbolic_name" => $LocalDef["field_symbolic_name"],
								"form_id" => $FormID,
								);
							$ItemFieldSet[] = $TempRecord;
						}
					}
					// Get tags, if any

					$TagSet = aib_get_item_tags($GLOBALS["aib_db"],$ResultRecord["item_id"]);
					if ($TagSet != false)
					{
						$ItemTagSet = $TagSet;
					}
				}

				$ItemFileList = array();
				if ($GetFilesFlag == true)
				{
					$FileList = aib_get_files_for_item($DBHandle,$ResultRecord["item_id"]);
					if ($FileList == false)
					{
						$FileList = array();
					}

					foreach($FileList as $FileRecord)
					{
						$StoredSize = stored_file_size($FileRecord);
						$ItemFileList[] = array(
						"file_id" => $FileRecord["record_id"],
						"file_original_name" => urldecode($FileRecord["file_original_name"]),
						"file_mime_type" => urldecode($FileRecord["file_mime_type"]),
						"file_stored_stamp" => $FileRecord["file_stored_stamp"],
						"file_stored_string" => date("Y.m.d.H.i.s",$FileRecord["file_stored_stamp"]),
						"file_type" => $FileRecord["file_class"],
						"file_size" => $StoredSize,
						);
					}
				}

				$ThumbData = array("id" => -1, "data" => "", "mime" => "");
				$PrimaryData = array("id" => -1, "data" => "", "mime" => "");
				if ($GetThumbFlag == true)
				{
					$LocalThumbData = get_item_image_data($DBHandle,$ResultRecord["item_id"],AIB_FILE_CLASS_THUMB);
					if ($LocalThumbData != false)
					{
						$ThumbData = $LocalThumbData;
					}
				}

				if ($GetPrimaryFlag == true)
				{
					$LocalPrimaryData = get_item_image_data($DBHandle,$ResultRecord["item_id"],AIB_FILE_CLASS_THUMB);
					if ($LocalPrimaryData != false)
					{
						$PrimaryData = $LocalPrimaryData;
					}
				}

				if ($GetFirstThumbFlag == true)
				{
					$FirstThumbID = aib_get_first_record_thumb($DBHandle,$ResultRecord["item_id"]);
				}
				else
				{
					$FirstThumbID = "";
				}

				$OutRecord = array(
					"properties" => $ItemPropertySet,
					"fields" => $ItemFieldSet,
					"files" => $ItemFileList,
					"tags" => $ItemTagSet,
					"item_id" => $ResultRecord["item_id"],
					"item_tree_type" => $ResultRecord["item_type"],
					"item_type" => $LocalEntryType,
					"item_title" => urldecode($ResultRecord["item_title"]),
					"item_ref" => $ResultRecord["item_ref"],
					"item_source_type" => $ResultRecord["item_source_type"],
					"item_source_info" => $ResultRecord["item_source_info"],
					"item_create_stamp" => $ResultRecord["item_create_stamp"],
					"item_create_string" => date("Y.m.d.H.i.s",$ResultRecord["item_create_stamp"]),
					"item_parent" => $ResultRecord["item_parent"],
					"thumb_id" => $ThumbData["id"],
					"thumb_data" => $ThumbData["data"],
					"thumb_mime" => $ThumbData["mime"],
					"primary_id" => $PrimaryData["id"],
					"primary_data" => $PrimaryData["data"],
					"primary_mime" => $PrimaryData["mime"],
					"first_thumb" => $FirstThumbID,
					"form_id" => $FormID,
				);

				if ($IsLink == true)
				{
					$OutRecord["is_link"] = "Y";
					$OutRecord["link_id"] = $TempResultRecord["item_id"];
					if ($OptShowLinkHome == true)
					{
						if ($LinkUserHome != false)
						{
							unset($LinkUserHome["user_pass"]);
							unset($LinkUserHome["user_primary_group"]);
							$OutRecord["link_user_profile"] = $LinkUserHome;
						}
						else
						{
							$OutRecord["link_user_profile"] = array();
						}
					}


					foreach($LinkData as $LinkDataKey => $LinkDataInfo)
					{
						$OutRecord[$LinkDataKey] = $LinkDataInfo;
					}

					if ($OptionLinkOwner == true)
					{
						$OutRecord["link_owner"] = $ResultRecord["item_user_id"];
						$TempUser = ftree_get_user($DBHandle,$ResultRecord["item_user_id"]);
						$OutRecord["link_owner_login"] = $TempUser["user_login"];
						$OutRecord["link_owner_title"] = urldecode($TempUser["user_title"]);
						$OutRecord["link_owner_properties"] = array();
						$UserPropertyList = ftree_list_user_prop($DBHandle,$UserID);
						foreach($UserPropertyList as $LocalName => $LocalValue)
						{
							$TempName = urldecode($LocalName);
							$OutRecord["link_owner_properties"][$TempName] = urldecode($LocalValue);
						}

					}

					$OutRecord["link_ref_properties"] = array();
					if ($OptionLinkProperties == true)
					{
						$LinkProperties = ftree_list_properties($DBHandle,$TempResultRecord["item_ref"],true);
						if ($LinkProperties != false)
						{
							foreach($LinkProperties as $LocalName => $LocalValue)
							{
								$TempName = urldecode($LocalName);
								$OutRecord["link_ref_properties"][$TempName] = urldecode($LocalValue);
							}
						}
					}
				}
				else
				{
					$OutRecord["is_link"] = "N";
					$OutRecord["link_owner"] = "";
					$OutRecord["link_owner_login"] = "";
				}

				$OutRecord["property_counts"] = array();
				if ($OptGetPropCount == true)
				{
					$PropertyCountSet = ftree_count_child_object_property_set_batch($GLOBALS["aib_db"],$ResultRecord["item_id"],false,false,false,$PropertyCountDef);
					$OutRecord["property_counts"] = $PropertyCountSet;
				}

				$OutData["info"]["records"][] = $OutRecord;
			}
		}

		if ($CountOnlyFlag == true)
		{
			$OutData["status"] = "OK";
			$OutData["info"] + $TotalCount;
		}

		break;
	}

	return($OutData);
}

// Get the list of links to a given item in the tree
// -------------------------------------------------
function ftree_get_item_links($DBHandle,$ItemID = false,$UserID = false)
{
	if ($ItemID !== false)
	{
		$Query = "SELECT * FROM ftree WHERE item_ref='$ItemID' AND item_source_type='".FTREE_OBJECT_TYPE_LINK."'";
		if ($UserID !== false)
		{
			$Query .= " AND item_user_id='$UserID'";
		}
	
		$Query .= ";";
		$Results = mysqli_query($DBHandle,$Query);
		if ($Results == false)
		{
			return(array());
		}
	}
	else
	{
		if ($UserID === false)
		{
			return(array());
		}

		$Query = "SELECT * FROM ftree WHERE item_source_type='".FTREE_OBJECT_TYPE_LINK."' AND item_user_id='$UserID';";
		$Results = mysqli_query($DBHandle,$Query);
		if ($Results == false)
		{
			return(array());
		}
	}

	$OutList = mysqli_fetch_all($Results,MYSQLI_ASSOC);
/*
	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Results);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}
*/
	mysqli_free_result($Results);
	return($OutList);
}

// Replace a property value or insert new record, optionally deleting from alternate if long/short
// -----------------------------------------------------------------------------------------------
function ftree_replace_prop($DBHandle,$ItemID,$PropertyName,$PropertyValue,$LongFlag = "N", $DeleteAltFlag = "Y")
{
	if ($LongFlag == "Y")
	{
		$DelQuery = "DELETE FROM ftree_long_prop where item_id='$ItemID' AND property_name='$PropertyName';";
		$Query = "INSERT INTO ftree_long_prop (item_id,property_name,property_value) VALUES ('$ItemID','$PropertyName','$PropertyValue');";
	}
	else
	{
		$DelQuery = "DELETE FROM ftree_prop where item_id='$ItemID' AND property_name='$PropertyName';";
		$Query = "INSERT INTO ftree_prop (item_id,property_name,property_value) VALUES ('$ItemID','$PropertyName','$PropertyValue');";
	}

	mysqli_query($DBHandle,$DelQuery);
	mysqli_query($DBHandle,$Query);
	if ($DeleteAltFlag == "Y")
	{
		if ($LongFlag == "Y")
		{
			$Query = "DELETE FROM ftree_prop WHERE item_id='$ItemID' AND property_name='$PropertyName';";
		}
		else
		{
			$Query = "DELETE FROM ftree_long_prop WHERE item_id='$ItemID' AND property_name='$PropertyName';";
		}

		mysqli_query($DBHandle,$Query);
	}
}

// Replace a property value or insert new record, optionally deleting from alternate if long/short
// -----------------------------------------------------------------------------------------------
function ftree_replace_prop_batch($DBHandle,$ItemIDList,$PropertyName,$PropertyValue,$LongFlag = "N", $DeleteAltFlag = "Y")
{
	// Create two lists containing SQL fragments
	
	$QueryList = array();
	$LocalIDList = array();
	foreach($ItemIDList as $ItemID)
	{
		$QueryList[] = "('$ItemID','$PropertyName','$PropertyValue')";
		$LocalIDList[] = "item_id='$ItemID'";
	}

	// Join fragments

	$ItemIDString = join(" OR ",$LocalIDList);

	// Build deletion and insertion statements
	
	if ($LongFlag == "Y")
	{
		$DelQuery = "DELETE FROM ftree_long_prop WHERE property_name='$PropertyName' AND ($ItemIDString);";
		$Query = "INSERT INTO ftree_long_prop (item_id,property_name,property_value) VALUES ".join(",",$QueryList).";";
	}
	else
	{
		$DelQuery = "DELETE FROM ftree_prop WHERE property_name='$PropertyName' AND ($ItemIDString);";
		$Query = "INSERT INTO ftree_prop (item_id,property_name,property_value) VALUES ".join(",",$QueryList).";";
	}

	// Delete property values, then re-insert with new values
	
	mysqli_query($DBHandle,$DelQuery);
	mysqli_query($DBHandle,$Query);
	if ($DeleteAltFlag == "Y")
	{
		$ItemIDString = join(" OR ",$LocalIDList);
		if ($LongFlag == "Y")
		{
			$Query = "DELETE FROM ftree_prop WHERE property_name='$PropertyName' AND ($ItemIDString);";
		}
		else
		{
			$Query = "DELETE FROM ftree_long_prop WHERE property_name='$PropertyName' AND ($ItemIDString);";
		}

//ftree_log_debug($Query);
		mysqli_query($DBHandle,$Query);
	}
}

// Delete property batch
// ---------------------
function ftree_delete_property_batch($DBHandle,$ItemIDList,$PropName = false,$PropType = "B")
{
	// Create a statement string to use in SQL
	
	$StatementList = array();
	foreach($ItemIDList as $ItemID)
	{
		$StatementList[] = "item_id='$ItemID'";
	}

	$StatementString = join(" OR ",$StatementList);

	// If no property name, delete all properties
	
	if ($PropName === false)
	{
		switch($PropType)
		{
			// Short prop

			case "S":
//ftree_log_debug("DELETE FROM ftree_prop WHERE $StatementString ;");
				mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE $StatementString ;");
				break;

			// Long prop

			case "L":
//ftree_log_debug("DELETE FROM ftree_long_prop WHERE $StatementString ;");
				mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE $StatementString ;");
				break;

			// Both long and short

			case "B":
			default:
//ftree_log_debug("DELETE FROM ftree_prop WHERE $StatementString ;");
//ftree_log_debug("DELETE FROM ftree_long_prop WHERE $StatementString ;");
				mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE $StatementString ;");
				mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE $StatementString ;");
				break;
		}

		return(true);
	}

	switch($PropType)
	{
		case "S":
//ftree_log_debug("DELETE FROM ftree_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			break;

		case "L":
//ftree_log_debug("DELETE FROM ftree_long_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			break;

		case "B":
		default:
//ftree_log_debug("DELETE FROM ftree_prop WHERE property_name='$PropName' AND ($StatementString) ;");
//ftree_log_debug("DELETE FROM ftree_long_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			mysqli_query($DBHandle,"DELETE FROM ftree_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			mysqli_query($DBHandle,"DELETE FROM ftree_long_prop WHERE property_name='$PropName' AND ($StatementString) ;");
			break;
	}

	return(true);
}
?>
