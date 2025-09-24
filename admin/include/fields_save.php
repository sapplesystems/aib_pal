<?php
//
//

// Log debug
function ftree_field_log_debug($Msg)
{
	$Handle = fopen("/tmp/ftree_field_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

// Get result set from query
// -------------------------
function ftree_field_query($DBHandle,$Query)
{
	$Result = mysqli_query($DBHandle,$Query,MYSQLI_USE_RESULT);
	if ($Result == false)
	{
		return(array());
	}

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

	mysqli_free_result($Result);
	return($OutList);
}

// Do query and return false if empty set
// --------------------------------------
function ftree_field_query_ext($DBHandle,$Query)
{
	$ResultList = ftree_field_query($DBHandle,$Query);
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


// Create field definition
// -----------------------
function ftree_field_create_field($DBHandle,$Title,$DataType,$Format,$Size,$OwnerType = FTREE_OWNER_TYPE_SYSTEM,$OwnerID = FTREE_USER_SUPERADMIN, $SymbolicName = "NULL")
{
	$LocalFormat = urlencode($Format);
	$LocalTitle = urlencode($Title);
	$Status = mysqli_query($DBHandle,"INSERT INTO field_def (field_title,field_data_type,field_format,field_size,field_owner_type,field_owner_id,field_symbolic_name) VALUES ('$LocalTitle','$DataType','$LocalFormat','$Size','$OwnerType',$OwnerID,'$SymbolicName');");
	if ($Status == false)
	{
		return(false);
	}

	$NewID = mysqli_insert_id($DBHandle);
	return($NewID);
}

// Modify field definition
// Settings is an assoc array with the following keys:
//
//	title
//	data_type
//	format
//	size
//	owner_type
//	owner_size
//
// Note that if a field was defined as being under 256
// bytes and the size is modified, the system will
// move all of the field data to the large field storage
// area.
// -----------------------
function ftree_field_modify_field($DBHandle,$FieldID,$Settings)
{
	// Make sure the field exists

	$ResultList = ftree_field_query_ext($DBHandle,"SELECT field_id FROM field_def WHERE field_id=$FieldID;");
	if ($ResultList == false)
	{
		return(false);
	}

	// Modify

	foreach($Settings as $Name => $Value)
	{
		switch($Name)
		{
			case "title":
				mysqli_query($DBHandle,"UPDATE field_def SET field_title='$Value' WHERE field_id=$FieldID;");
				break;

			case "data_type":
				mysqli_query($DBHandle,"UPDATE field_def SET field_data_type='$Value' WHERE field_id=$FieldID;");
				break;

			case "format":
				mysqli_query($DBHandle,"UPDATE field_def SET field_format='$Value' WHERE field_id=$FieldID;");
				break;

			case "size":
				mysqli_query($DBHandle,"UPDATE field_def SET field_size='$Value' WHERE field_id=$FieldID;");
				break;

			case "owner_type":
				mysqli_query($DBHandle,"UPDATE field_def SET field_owner_type='$Value' WHERE field_id=$FieldID;");
				break;

			case "owner_id":
				mysqli_query($DBHandle,"UPDATE field_def SET field_owner_id=$Value WHERE field_id=$FieldID;");
				break;

			default:
				break;
		}
	}

	return(true);
}

// Retrieve a field
// ----------------
function ftree_field_get_field($DBHandle,$FieldID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM field_def WHERE field_id=$FieldID;");
	if ($Result == false)
	{
		return(false);
	}

	if (count($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	return($Row);
}

// Create form definition
// ----------------------
function ftree_field_create_form($DBHandle,$Name,$OwnerID,$OwnerType)
{
	mysqli_query($DBHandle,"INSERT INTO form_def (form_title,form_owner,form_owner_type) VALUES ('$Name','$OwnerID','$OwnerType');");
	$NewID = mysqli_insert_id($DBHandle);
	return($NewID);
}

// Retrieve form
// -------------
function ftree_field_get_form($DBHandle,$FormID)
{
	$FormList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_def WHERE form_id=$FormID LIMIT 1;");
	if ($FormList == false)
	{
		return(false);
	}

	return($FormList[0]);
}

// Modify form definition
//
// Settings is an assoc array where the keys may be
//
//	name
//	owner
// ------------------------------------------------
function ftree_field_modify_form($DBHandle,$FormID,$Settings)
{
	// Make sure the form exists

	$CheckList = ftree_field_query_ext($DBHandle,"SELECT form_id FROM form_def WHERE form_id=$FormID;");
	if ($CheckList == false)
	{
		return(false);
	}

	// Modify

	foreach($Settings as $Name => $Value)
	{
		switch($Name)
		{
			case "name":
				mysqli_query($DBHandle,"UPDATE form_def SET form_title='$Value' WHERE form_id=$FormID;");
				break;

			case "owner":
				mysqli_query($DBHandle,"UPDATE form_def SET form_owner='$Value' WHERE form_id=$FormID;");
				break;

			case "owner_type":
				mysqli_query($DBHandle,"UPDATE form_def SET form_owner_type='$Value' WHERE form_id=$FormID;");
				break;

			default:
				break;
		}
	}

	return(true);
}

// Delete form definition
// ----------------------
function ftree_field_delete_form($DBHandle,$FormID)
{
	mysqli_query($DBHandle,"DELETE FROM form_field WHERE form_id=$FormID;");
	mysqli_query($DBHandle,"DELETE FROM form_def WHERE form_id=$FormID;");
	return(true);
}

// Add field to form
// -----------------
function ftree_field_add_field_to_form($DBHandle,$FormID,$FieldID,$SortOrder = false,$AltTitle = "")
{
	// Make sure the form exists

	$CheckList = ftree_field_query_ext($DBHandle,"SELECT form_id FROM form_def WHERE form_id=$FormID;");
	if ($CheckList == false)
	{
		return(false);
	}

	// Make sure the field isn't already part of the form

	$CheckList = ftree_field_query_ext($DBHandle,"SELECT record_id FROM form_field WHERE form_id=$FormID AND field_id=$FieldID;");
	if ($CheckList != false)
	{
		return(false);
	}

	// If the sort order is specified, just insert

	if ($SortOrder !== false)
	{
		mysqli_query($DBHandle,"INSERT INTO form_field (form_id,field_id,field_sort_order,field_alt_title) VALUES ($FormID,$FieldID,$SortOrder,'$AltTitle');");
		$NewID = mysqli_insert_id($DBHandle);
		return($NewID);
	}

	// Sort order not specified.  Get the existing fields and set new sort value to the highest value plus 10.

	$FieldList = ftree_field_query_ext($DBHandle,"SELECT sort_order FROM form_field WHERE form_id=$FormID;");
	if ($FieldList == false)
	{
		$SortOrder = 10;
	}
	else
	{
		$SortOrder = -99999999;
		foreach($FieldList as $Record)
		{
			if (intval($Record["sort_order"]) > $SortOrder)
			{
				$SortOrder = intval($Record["sort_order"]);
			}
		}

		$SortOrder += 10;
	}

	// Save field

	mysqli_query($DBHandle,"INSERT INTO form_field (form_id,field_id,field_sort_order,field_alt_title) VALUES ($FormID,$FieldID,$SortOrder,'$AltTitle');");
	$NewID = mysqli_insert_id($DBHandle);
	return($NewID);
}

// Remove field from form
// ----------------------
function ftree_field_del_form_field($DBHandle,$FormID,$FieldID)
{
	mysqli_query($DBHandle,"DELETE FROM form_field WHERE form_id=$FormID AND field_id=$FieldID;");
	return(true);
}

// Get all fields for a form
// -------------------------
function ftree_field_get_form_fields($DBHandle,$FormID)
{
	$TempList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_field WHERE form_id=$FormID ORDER BY field_sort_order;");
	if ($TempList == false)
	{
		return(array());
	}

	$OutList = array();
	foreach($TempList as $TempRecord)
	{
		// Get field definition

		$FieldInfo = ftree_field_get_field($DBHandle,$TempRecord["field_id"]);
		if ($FieldInfo != false)
		{
			$OutList[] = array("form_record" => $TempRecord, "field_record" => $FieldInfo);
		}
	}

	return($OutList);
}

// Modify field in form (display order, etc.)
//
// Settings is an assoc array with the following keys:
//
//	sort_order
//	alt_title
// ---------------------------------------------------
function ftree_field_alter_form_field($DBHandle,$FormID,$FieldID,$Settings)
{
	// Make sure field is associated with form

	$TempList = ftree_field_query_ext($DBHandle,"SELECT record_id FROM form_field WHERE form_id=$FormID AND field_id=$FieldID;");
	if ($TempList == false)
	{
		return(false);
	}

	$Record = $TempList[0];
	$RecordID = $Record["record_id"];

	foreach($Settings as $Name => $Value)
	{
		switch($Name)
		{
			case "sort_order":
				mysqli_query($DBHandle,"UPDATE form_field SET field_sort_order=$Value WHERE record_id=$RecordID;");
				break;

			case "alt_title":
				mysqli_query($DBHandle,"UPDATE form_field SET field_alt_title='$Value' WHERE record_id=$RecordID;");
				break;

			default:
				break;
		}
	}

	return(true);
}

// Resequence fields in a form
// ---------------------------
function ftree_form_field_resequence($DBHandle,$FormID)
{
	// Get the list of fields

	$TempList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_field WHERE form_id=$FormID ORDER BY field_sort_order;");
	if ($TempList == false)
	{
		return(true);
	}

	// Resequence

	$Sequence = 10;
	foreach($TempList as $TempRecord)
	{
		$Query = "UPDATE form_field SET field_sort_order='$Sequence' WHERE form_id=$FormID AND field_id=$FieldID;";
		mysqli_query($DBHandle,$Query);
		$Sequence += 10;
	}

	return(true);
}


// Create default field definitions
// --------------------------------
function ftree_field_create_default_fields($DBHandle)
{
	$DefaultDef = array(
		array("field_title" => "URL", "field_symbolic_name" => "url", "field_data_type" => "T",
			"field_format" => "", "field_size" => "64",
			"field_owner_type" => "S", "field_owner_id" => "-1"),
		array("field_title" => "Description", "field_symbolic_name" => "desc", "field_data_type" => "B",
			"field_format" => "", "field_Size" => "40",
			"field_owner_type" => "S", "field_owner_id" => "-1"),
		array("field_title" => "Tags", "field_symbolic_name" => "tags", "field_data_type" => "B",
			"field_format" => "", "field_Size" => "40",
			"field_owner_type" => "S", "field_owner_id" => "-1"),
		array("field_title" => "Creator", "field_symbolic_name" => "creator", "field_data_type" => "T",
			"field_format" => "", "field_Size" => "64",
			"field_owner_type" => "S", "field_owner_id" => "-1"),
		array("field_title" => "Date/Time", "field_symbolic_name" => "date", "field_data_type" => "T",
			"field_format" => "", "field_Size" => "64",
			"field_owner_type" => "S", "field_owner_id" => "-1"),
		);

	// See if one of the defaults is defined

	$FieldDefList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_def WHERE field_symbolic_name='url' AND field_owner_type='S' AND field_owner_id=-1;");
	if ($FieldDefList != false)
	{
		return;
	}

	// If not, create default fields

	foreach($DefaultDef as $DefaultDefRecord)
	{
		ftree_field_create_field($DBHandle,$DefaultDefRecord["field_title"],$DefaultDefRecord["field_data_type"],
			$DefaultDefRecord["field_format"],$DefaultDefRecord["field_size"],$DefaultDefRecord["field_owner_type"],
			$DefaultDefRecord["field_owner_id"],$DefaultDefRecord["field_symbolic_name"]);
	}

	return;
}

// Store field(s) for ftree item
//
// Info is an assoc array where the key
// is the field ID and the value is the stored value
// -------------------------------------------------
function ftree_field_store_item_fields($DBHandle,$ItemID,$Info,$SystemFields=false)
{
	// Get the field definitions

	ftree_field_create_default_fields($DBHandle);
	$FieldDef = array();
	foreach($Info as $FieldID => $Value)
	{
		if ($SystemFields == false)
		{
			$FieldDefList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_def WHERE field_id=$FieldID;");
			if ($FieldDefList == false)
			{
				return(false);
			}

			$FieldDef[$FieldID] = $FieldDefList[0];
		}
		else
		{
			$FieldDefList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_def WHERE field_symbolic_name='$FieldID' AND field_owner_type='S' AND field_owner_id=-1;");
			if ($FieldDefList == false)
			{
				return(false);
			}

			$FieldDef[$FieldID] = $FieldDefList[0];
		}
	}

	// Store short fields in short value table, long fields in long value table

	foreach($Info as $FieldID => $Value)
	{
		$LocalDef = $FieldDef[$FieldID];
		$LocalFieldID = $LocalDef["field_id"];
		$LocalValue = urlencode($Value);
		if ($LocalDef["field_data_type"] == FTREE_FIELD_TYPE_BIGTEXT)
		{
			mysqli_query($DBHandle,"INSERT INTO field_longdata (item_id,field_id,field_value) VALUES ($ItemID,$LocalFieldID,'$LocalValue');");
		}
		else
		{
			mysqli_query($DBHandle,"INSERT INTO field_data (item_id,field_id,field_value) VALUES ($ItemID,$LocalFieldID,'$LocalValue');");
		}
	}

	$TempList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_data_times WHERE item_id=$ItemID;");
	if ($TempList == false)
	{
		$UpdateTime = time();
		mysqli_query($DBHandle,"INSERT INTO field_data_times (item_id,last_field_update,last_search_gen) VALUES ($ItemID,$UpdateTime,-1);");
	}
	else
	{
		$UpdateTime = time();
		mysqli_query($DBHandle,"UPDATE field_data_times SET last_field_update=$UpdateTime WHERE item_id=$ItemID;");
	}

	return(true);
}

// Retrieve fields for ftree item
// ------------------------------
function ftree_field_get_item_fields($DBHandle,$ItemID)
{
	$OutData = array();
	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_data WHERE item_id=$ItemID;");
	if ($RecordList != false)
	{
		foreach($RecordList as $FieldRecord)
		{
			$OutData[$FieldRecord["field_id"]] = urldecode($FieldRecord["field_value"]);
		}
	}

	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_longdata WHERE item_id=$ItemID;");
	if ($RecordList != false)
	{
		foreach($RecordList as $FieldRecord)
		{
			$OutData[$FieldRecord["field_id"]] = urldecode($FieldRecord["field_value"]);
		}
	}

	return($OutData);
}

// Retrieve fields for ftree item, including definitions
// -----------------------------------------------------
function ftree_field_get_item_fields_ext($DBHandle,$ItemID)
{
	$OutData = array();
	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_data WHERE item_id=$ItemID;");
	if ($RecordList != false)
	{
		foreach($RecordList as $FieldRecord)
		{
			$FieldDef = ftree_field_get_field($DBHandle,$FieldRecord["field_id"]);
			$OutData[$FieldRecord["field_id"]] = array("value" => $FieldRecord["field_value"], "def" => $FieldDef);
		}
	}

	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_longdata WHERE item_id=$ItemID;");
	if ($RecordList != false)
	{
		foreach($RecordList as $FieldRecord)
		{
			$FieldDef = ftree_field_get_field($DBHandle,$FieldRecord["field_id"]);
			$OutData[$FieldRecord["field_id"]] = array("value" => $FieldRecord["field_value"], "def" => $FieldDef);
		}
	}

	return($OutData);
}

// Update field(s) for ftree item
// ------------------------------
function ftree_field_update_item_fields($DBHandle,$ItemID,$Info)
{
	// Get the field definitions

	$FieldDef = array();
	foreach($Info as $FieldID => $Value)
	{
		$FieldDefList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_def WHERE field_id=$FieldID;");
		if ($FieldDefList == false)
		{
			return(false);
		}

		$FieldDef[$FieldID] = $FieldDefList[0];
	}

	// Store short fields in short value table, long fields in long value table.

	foreach($Info as $FieldID => $RawValue)
	{
		// Encode saved values to avoid injection attacks

		$Value = urlencode($RawValue);

		// Get field definition.  If the field is longer than 255 bytes or is of type BIGTEXT, save in long data.

		$LocalDef = $FieldDef[$FieldID];
		if ($LocalDef["field_size"] > 255 || $LocalDef["field_data_type"] == FTREE_FIELD_TYPE_BIGTEXT)
		{
			// If the field is stored in the short data, remove it.

			mysqli_query($DBHandle,"DELETE FROM field_data WHERE item_id=$ItemID AND field_id=$FieldID;");

			// If the field already exists, update.  Else, store.

			$LocalList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_longdata WHERE item_id=$ItemID AND field_id=$FieldID;");
			if ($LocalList == false)
			{
				mysqli_query($DBHandle,"INSERT INTO field_longdata (item_id,field_id,field_value) VALUES ($ItemID,$FieldID,'$Value');");
			}
			else
			{
				mysqli_query($DBHandle,"UPDATE field_longdata SET field_value='$Value' WHERE item_id=$ItemID AND field_id=$FieldID;");
			}
		}
		else
		{
			// If the field is stored in the long data, remove it

			mysqli_query($DBHandle,"DELETE FROM field_longdata WHERE item_id=$ItemID AND field_id=$FieldID;");

			// If the field already exists, update.  Else, store.

			$LocalList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_data WHERE item_id=$ItemID AND field_id=$FieldID;");
			if ($LocalList == false)
			{
				mysqli_query($DBHandle,"INSERT INTO field_data (item_id,field_id,field_value) VALUES ($ItemID,$FieldID,'$Value');");
			}
			else
			{
				mysqli_query($DBHandle,"UPDATE field_data SET field_value='$Value' WHERE item_id=$ItemID AND field_id=$FieldID;");
			}

		}
	}

	return(true);
}

function ftree_field_delete_item_field($DBHandle,$ItemID,$FieldID)
{
	if ($FieldID !== false)
	{
		mysqli_query($DBHandle,"DELETE FROM field_data WHERE field_id=$FieldID AND item_id=$ItemID;");
		mysqli_query($DBHandle,"DELETE FROM field_longdata WHERE field_id=$FieldID AND item_id=$ItemID;");
	}
	else
	{
		mysqli_query($DBHandle,"DELETE FROM field_data WHERE item_id=$ItemID;");
		mysqli_query($DBHandle,"DELETE FROM field_longdata WHERE item_id=$ItemID;");
	}

	return(true);
}

// Delete field definition, deleting all stored data
// -------------------------------------------------
function ftree_field_delete_field($DBHandle,$FieldID)
{
	mysqli_query($DBHandle,"DELETE FROM field_def WHERE field_id=$FieldID;");
	mysqli_query($DBHandle,"DELETE FROM form_def WHERE field_id=$FieldID;");
	mysqli_query($DBHandle,"DELETE FROM field_data WHERE field_id=$FieldID;");
	mysqli_query($DBHandle,"DELETE FROM field_longdata WHERE field_id=$FieldID;");
	return(true);
}

// Set form property or properties
//
// PropertySet is an assoc array where key is property name,
// data is property value.
// -------------------------------
function ftree_field_set_form_property($DBHandle,$FormID,$PropertySet)
{
	$ExistingProperties = array();
	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_property WHERE form_id=$FormID;");
	if ($RecordList != false)
	{
		foreach($RecordList as $Record)
		{
			$ExistingProperties[$Record["property_name"]] = $Record["property_value"];
		}
	}

	foreach($PropertySet as $PropertyName => $PropertyValue)
	{
		if (isset($ExistingProperties[$PropertyName]) == false)
		{
			mysqli_query($DBHandle,"INSERT INTO form_property (form_id,property_name,property_value) VALUES ($FormID,'$PropertyName','$PropertyValue');");
		}
		else
		{
			mysqli_query($DBHandle,"UPDATE form_property SET property_value='$PropertyValue' WHERE form_id=$FormID;");
		}
	}

	return(true);
}

// Get form properties
// -------------------
function ftree_field_get_form_properties($DBHandle,$FormID)
{
	$ExistingProperties = array();
	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_property WHERE form_id=$FormID;");
	if ($RecordList != false)
	{
		foreach($RecordList as $Record)
		{
			$ExistingProperties[$Record["property_name"]] = $Record["property_value"];
		}
	}

	return($ExistingProperties);
}

// Delete form properties
// ----------------------
function ftree_field_delete_form_properties($DBHandle,$FormID,$NameList)
{
	foreach($NameList as $Name)
	{
		mysqli_query($DBHandle,"DELETE FROM form_property WHERE form_id=$FormID AND property_name='$Name';");
	}

	return(true);
}

// Given a string, perform substitutions based on the format [[NAME]].  If the VarName argument
// is false, then any bracketed name will be replaced with VarValue.
// ---------------------------------------------------------------------------------------------
function ftree_field_do_subst($InString,$VarName,$VarValue)
{
	if ($VarName != false)
	{
		$OutString = str_replace("[[".$VarName."]]",$VarValue,$InString);
	}
	else
	{
		$OutString = preg_replace("/[\[][\[][^\]]+[\]][\]]/",$VarValue,$InString);
	}

	return($OutString);
}

// Do hardwired substitutions
// --------------------------
function ftree_field_do_hard_subs($InString,$ItemID,$Counter,$TimeSeconds,$TimeMicroseconds,$ItemProperties)
{
	$OutString = ftree_field_do_subst($InString,"ITEMID",$ItemID);
	$OutString = ftree_field_do_subst($OutString,"COUNTER",$Counter);
	$OutString = ftree_field_do_subst($OutString,"DATE",date("Ymd",$TimeSeconds));
	$OutString = ftree_field_do_subst($OutString,"TIME",date("His",$TimeSeconds));
	$OutString = ftree_field_do_subst($OutString,"DATETIME",date("YmdHis",$TimeSeconds));
	$OutString = ftree_field_do_subst($OutString,"TIMESTAMP",sprintf("%09d",$TimeSeconds));
	$OutString = ftree_field_do_subst($OutString,"MICROTIMESTAMP",sprintf("%09d",$TimeSeconds).".".sprintf("%06d",$TimeMicroseconds));
	$OutString = ftree_field_do_subst($OutString,"MICROTIMESTAMPUNDER",sprintf("%09d",$TimeSeconds)."_".sprintf("%06d",$TimeMicroseconds));
	$OutString = ftree_field_do_subst($OutString,"PID",sprintf("%09d",posix_getpid()));
	return($OutString);
}

// Given a microtime value, return seconds and microseconds
// --------------------------------------------------------
function ftree_field_split_microtime($InTime)
{
	$Segs = explode(".",sprintf("%0.6f",$InTime));
	return($Segs);
}

// Get a list of all items that require export to search
// -----------------------------------------------------
function ftree_field_find_export_update($DBHandle)
{
	$ResultList = ftree_field_query_ext($DBHandle,"SELECT item_id FROM field_data_times WHERE last_field_update > last_search_gen;");
	if ($ResultList == false)
	{
		return(array());
	}

	return($ResultList);
}

function ftree_field_store_item_field($DBHandle,$ItemID,$FieldDef,$FieldData)
{
	$LocalFieldID = $FieldDef["field_id"];
	if ($FieldDef["field_data_type"] == FTREE_FIELD_TYPE_BIGTEXT)
	{
		mysqli_query($DBHandle,"INSERT INTO field_longdata (item_id,field_id,field_value) VALUES ($ItemID,$LocalFieldID,'$FieldData');");
	}
	else
	{
		mysqli_query($DBHandle,"INSERT INTO field_data (item_id,field_id,field_value) VALUES ($ItemID,$LocalFieldID,'$FieldData');");
	}

	return(true);
}

function ftree_field_disable($DBHandle,$FieldID,$Disabled = true)
{
	$ResultList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_def WHERE field_id='$FieldID';");
	if ($ResultList == false)
	{
		return(false);
	}

	$FieldDef = $ResultList[0];
	if ($Disabled == true)
	{
		if (preg_match("/^DISABLED[\;]/",$FieldDef["field_format"]) == false)
		{
			$FieldDef["field_format"] = "DISABLED;".$FieldDef["field_format"];
			mysqli_query($DBHandle,"UPDATE field_def SET field_format='".$FieldDef["field_format"]."' WHERE field_id='$FieldID';");
			return(true);
		}

		return(true);
	}

	if (preg_match("/^DISABLED[\;]/",$FieldDef["field_format"]) != false)
	{
		$FieldDef["field_format"] = preg_replace("/^DISABLED[\;]/","",$FieldDef["field_format"]);
		mysqli_query($DBHandle,"UPDATE field_def SET field_format='".$FieldDef["field_format"]."' WHERE field_id='$FieldID';");
		return(true);
	}

	return(true);
}

function ftree_field_is_disabled($FieldDef)
{
	if (preg_match("/^DISABLED[\;]/",$FieldDef["field_format"]) != false)
	{
		return(true);
	}

	return(false);
}


// Parse words in a string, correctly handling dates, numeric values.  Apostrophed words are truncated.
// ----------------------------------------------------------------------------------------------------
function ftree_field_parse_words($InString)
{
	// Get rid of punctuation where possible.  We retain commas in case the string contains numbers with
	// thousands separators.

	$TempString = preg_replace("/[^0-9A-Za-z\.\+\-\, ]/"," ",$InString);

	// Split the string using spaces

	$RawList = preg_split("/[ ]+/",$InString);
	$TempList = array();

	// First pass.  Split apart compound words, reformat numbers, etc.

	foreach($RawList as $RawWord)
	{
		$RawWord = ltrim(rtrim($RawWord));

		// Remove apostrophe endings

		$RawWord = preg_replace("/[\'][A-Za-z]+/","",$RawWord);

		// Get rid of trailing periods.

		$RawWord = preg_replace("/[\.]$/","",$RawWord);

		// Split apart words separated by a period.  First, check
		// to make sure this isn't a number.

		if (preg_match("/[A-Za-z]+/",$RawWord) != false)
		{
			$SubSeg = explode(".",$RawWord);
			if (count($SubSeg) > 0)
			{
				foreach($SubSeg as $SubWord)
				{
					if (ltrim(rtrim($SubWord)) != "")
					{
						$TempList[] = $SubWord;
					}
				}

				continue;
			}
		}

		// Handle numbers:

		if (preg_match("/^[0-9\,\.\+\-]+$/",$RawWord) != false)
		{
			$RawWord = preg_replace("/[\,]/","",$RawWord);
			$TempList[] = $RawWord;
			continue;
		}

		$TempList[] = $RawWord;
	}

	return($TempList);
}

// Save field data for item to search spec (.est) file.  The path and URI can contain substitution values; these are
// formatted as:
//
//	[[NAME]]
//
// where the name may be one of:
//
//	ITEMID
//	COUNTER
//		Counter for the item being processed.  Will not be unique.
//
//	ITEM_PROPERTY_<name>
//	DATE
//	TIME
//	DATETIME
//	TIMESTAMP
//	MICROTIMESTAMP
//		Format is secondes.microseconds
//
//	MICROTIMESTAMPUNDER
//		Format is seconds_microseconds
//
//	PID
//		Process ID
//
// ItemList contains complete item records
//
// Return value is an associative array where the key is the item ID, and the data
// is a status string.  "OK" indicates success, while errors are formatted as
// "FAIL,<reason string>".
// -------------------------------------------------------------------------------------------------------------------
function ftree_field_export_to_search($DBHandle,$BasePath,$BaseURI,$ItemList,$ArchiveInfo = false, $TypePropertyName = false, $AllowedTypes = false, $AllowNoType = false,$SkipLinks = true, $SearchAllowName = "searchindex")
{
	$ExportResults = array();
	$FieldDefCache = array();
	$FormDefCache = array();
	$FieldDataCache = array();
	$ItemFormCache = array();

	// Process each item

	$Counter = 0;
	foreach($ItemList as $ItemRecord)
	{
		// If this is a link, skip if required

		$ItemID = $ItemRecord["item_id"];
		if ($ItemRecord["item_ref"] > 0 && $SkipLinks == true)
		{
			$ExportResults[$ItemID] = "WARNING, LINK SKIPPED";
			continue;
		}

		// Get archive and archive group for item

		if ($ArchiveInfo == false)
		{
			$ArchiveInfo = ftree_get_archive_and_archive_group($DBHandle,$ItemID);
		}

		$ArchiveID = $ArchiveInfo["archive"]["item_id"];
		$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];

		// Retrieve field data

		$ItemFields = ftree_field_get_item_fields_ext($DBHandle,$ItemID);
		if ($ItemFields == false)
		{
			$ItemFields = array();
		}

		$LocalFormID = false;

		// If the item uses a form, grab form definition

		$ItemFormList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_item WHERE item_id=$ItemID;");
		if ($ItemFormList != false)
		{
			if (count($ItemFormList) > 0)
			{
				$LocalFormID = $ItemFormList[0]["form_id"];
				$FormList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_def WHERE form_id=$LocalFormID;");
				if ($FormList != false)
				{
					$FormDefCache[$LocalFormID] = $FormList[0];
				}
			}
		}


		// Load field cache and form cache

		$FieldDataCache = array();
		foreach($ItemFields as $FieldID => $FieldInfo)
		{
			$FieldDefCache[$FieldID] = $FieldInfo["def"];
			$FieldDataCache[$FieldID] = $FieldInfo["value"];
			$FormList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_def WHERE field_id=$FieldID;");
			if ($FormList != false)
			{
				foreach($FormList as $FormRecord)
				{
					$FormID = $FormRecord["form_id"];
					if (isset($FormDefCache[$FormID]) == false)
					{
						$FormDefCache[$FormID] = $FormRecord;
					}
				}
			}
		}

		// Get item properties (key is property name, value is property value)

		$ItemProperties = ftree_list_properties($DBHandle,$ItemID,true);
		if (isset($ItemProperties[$SearchAllowName]) == true)
		{
			if ($ItemProperties[$SearchAllowName] != "Y")
			{
				$ExportResults[$ItemID] = "WARNING, SEARCH DISALLOWED FOR ENTRY";
				continue;
			}
		}

		// If the item is marked as "private", don't export to search

		$PrivacySetting = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_PRIVATE);
		if ($PrivacySetting == "Y")
		{
			$ExportResults[$ItemID] = "WARNING, SEARCH DISALLOWED FOR PRIVATE ENTRY";
			continue;
		}

		// If there is a set of allowed property values and a property name field set, check item

		if ($TypePropertyName !== false && $AllowedTypes !== false)
		{
			$AllowedItem = false;
			$LocalType = "";
			foreach($ItemProperties as $LocalName => $LocalValue)
			{
				if (urldecode($LocalName) == $TypePropertyName)
				{
					$LocalType = urldecode($LocalValue);
					if (isset($AllowedTypes[urldecode($LocalValue)]) == true)
					{
						$AllowedItem = true;
						break;
					}
				}
			}

			if ($AllowedItem == false)
			{
				if ($AllowNoType == false)
				{
					$ExportResults[$ItemID] = "WARNING: TYPE OF '$LocalType' NOT PERMITTED";
					continue;
				}
			}
		}

		// Do substitutions for path

		$TimeSegs = ftree_field_split_microtime(microtime(true));
		$PathValue = ftree_field_do_hard_subs($BasePath,$ItemID,$Counter,$TimeSegs[0],$TimeSegs[1],$ItemProperties);

		// Add hashed segments to path, where the hashed segments are the zero-prefixed, hex numeric
		// value of the item ID split into 2-character segments.

		$SegmentList = str_split(sprintf("%08x",$ItemID),2);
		if ($ArchiveGroupID !== false)
		{
			$PathValue = $PathValue."/".sprintf("%08x",$ArchiveGroupID);
		}

		if ($ArchiveID !== false)
		{
			$PathValue = $PathValue."/".sprintf("%08x",$ArchiveID);
		}

		$PathValue = $PathValue."/".join("/",$SegmentList);

		// Do substitutions for URI

		$URIValue = $ItemID;
//		$URIValue = ftree_field_do_hard_subs($BaseURI,$ItemID,$Counter,$TimeSegs[0],$TimeSegs[1],$ItemProperties);

		// Create path if needed

		if (file_exists($PathValue) == false)
		{
			@mkdir($PathValue,0777,true);
			if (file_exists($PathValue) == false)
			{
				$ExportResults[$ItemID] = "FAIL,PATH CREATE";
				continue;
			}
		}

		// Create file name

		$PathValue .= "/".sprintf("%08x",$ItemID).".est";

		// Output data to file in Hyperestraier format (.est)

		$Handle = fopen($PathValue,"w");
		if ($Handle == false)
		{
			$ExportResults[$ItemID] = "FAIL,EST CREATE";
			continue;
		}

		// Get title path, removing first segment

		$TitlePathList = ftree_get_item_title_path($DBHandle,$ItemID);
		if ($TitlePathList === false)
		{
			$ExportResults[$ItemID] = "FAIL,TITLE PATH";
			continue;
		}

		$TitleSegs = array();
		foreach($TitlePathList as $TitlePathRecord)
		{
			$TitleSegs[] = urldecode($TitlePathRecord["item_title"]);
		}

		array_shift($TitleSegs);
		$TitlePath = join("/",$TitleSegs);
		$ItemTitle = urldecode($ItemRecord["item_title"]);
		$TitlePath = preg_replace("/[\r\n\t]+/"," ",$TitlePath);
		$ItemTitle = preg_replace("/[\r\n\t]+/"," ",$ItemTitle);
		
		// Set title prefix based on entry type

		if (isset($ItemProperties[AIB_FOLDER_PROPERTY_FOLDER_TYPE]) == true)
		{
			$FolderType = $ItemProperties[AIB_FOLDER_PROPERTY_FOLDER_TYPE];
			switch($FolderType)
			{
				case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					$TitlePath = "Organization: ".$ItemTitle;
					break;

				case AIB_ITEM_TYPE_ARCHIVE:
					$TitlePath = "Archive: ".$ItemTitle;
					break;

				case AIB_ITEM_TYPE_COLLECTION:
					$TitlePath = "Collection: ".$ItemTitle;
					break;

				case AIB_ITEM_TYPE_SUBGROUP:
					$TitlePath = "Sub-Group: ".$ItemTitle;
					break;

				case AIB_ITEM_TYPE_RECORD:
					$TitlePath = "Record: ".$ItemTitle;
					break;

				case AIB_ITEM_TYPE_ITEM:
				default:
					$TitlePath = "File: ".$ItemTitle;
					break;
			}
		}
		else
		{
			$TitlePath = "File: $ItemTitle";
		}

		// Create mandatory search fields

		fputs($Handle,"@uri=$URIValue\n");
		fputs($Handle,"@title=$TitlePath\n");
		fputs($Handle,"@cdate=".date("Y-m-d H:i:s",$ItemRecord["item_create_stamp"])."\n");

		// Add archive title and archive group title

		fputs($Handle,"@archive_group_title=".urldecode($ArchiveInfo["archive_group"]["item_title"])."\n");
		fputs($Handle,"@archive_title=".urldecode($ArchiveInfo["archive"]["item_title"])."\n");
		fputs($Handle,"@item_id=$ItemID\n");

		// Add tags

		$TagRecordList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_tags WHERE item_id=$ItemID;");
		if ($TagRecordList != false)
		{
			$TagTextList = array();
			foreach($TagRecordList as $TagRecord)
			{
				$TagTextList[] = urldecode($TagRecord["tag_value"]);
			}

			fputs($Handle,"@tags=".join(",",$TagTextList)."\n");
		}
		
		// For each field, add a search field

		$OutFields = array();
		$OutStructuredFields = array();
		foreach($FieldDefCache as $FieldID => $FieldDef)
		{
			$FieldTitle = urldecode($FieldDef["field_title"]);

			// If there is an alt title, use it instead

			$AltTitleList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_alt_titles WHERE item_id=$ItemID AND field_id=$FieldID;");
			if ($AltTitleList != false)
			{
				$FieldTitle = urldecode($AltTitleList[0]["field_alt_title"]);
			}
			else
			{

				// If the item uses a form, get the field definitions used by the form and see if
				// there's an alternate field title.

				if ($LocalFormID !== false)
				{
					$FormFieldList = ftree_field_query_ext($DBHandle,"SELECT * FROM form_field WHERE form_id=$LocalFormID AND field_id=$FieldID;");
					if ($FormFieldList != false)
					{
						$TempFieldDef = $FormFieldList[0];
						if (ltrim(rtrim(urldecode($TempFieldDef["field_alt_title"]))) != "")
						{
							$FieldTitle = ltrim(rtrim(urldecode($TempFieldDef["field_alt_title"])));
						}
					}
				}
			}

			if (isset($FieldDataCache[$FieldID]) == true)
			{
				$FieldValue = urldecode($FieldDataCache[$FieldID]);
			}
			else
			{
				$FieldValue = "";
			}

			// Output field value; replace any newlines or carriage returns with spaces to avoid screwing up the .est file format.

			$OutFields[] = $FieldTitle."=".preg_replace("/[\n\r]+/"," ",$FieldValue);
			$OutStructuredFields[] = "@".$FieldTitle."=".$FieldValue;

			// Add field which contains words for the field

//			$FieldTitle .= "_words";
//			$LocalWordList = ftree_field_parse_words($InString);
//			fputs($Handle,$FieldTitle."=".join(",",$LocalWordList)."\n");
		}

		// For tags, save in a tags field separated by commas

//		$TagSet = ftree_tags_get_item_tags($DBHandle,$ItemID);
//		if ($TagSet != false)
//		{
//			$TagString = join(",",$TagSet);
//			fputs($Handle,"tags=$TagString\n");
//			$OutFields[] = "Tags: ".$TagString;
//		}

		// Output structured fields

		foreach($OutStructuredFields as $StructuredField)
		{
			fputs($Handle,$StructuredField."\n");
		}

		// Output separator before text

		fputs($Handle,"\n");

		fputs($Handle,$TitlePath."\n");
//		fputs($Handle,"Archive Group: ".urldecode($ArchiveInfo["archive_group"]["item_title"])."\n");
//		fputs($Handle,"Archive: ".urldecode($ArchiveInfo["archive"]["item_title"])."\n");

		// Add tags

		$TagRecordList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_tags WHERE item_id=$ItemID;");
		if ($TagRecordList != false)
		{
			$TagTextList = array();
			foreach($TagRecordList as $TagRecord)
			{
				$TagTextList[] = urldecode($TagRecord["tag_value"]);
			}

			fputs($Handle,join(",",$TagTextList)."\n");
		}
		
		// For each field, store text

		foreach($OutFields as $OutLine)
		{
			fputs($Handle,$OutLine."\n");
		}

		// Close .est file

		fclose($Handle);

		// Update "last generated" date for search data

		$LocalTime = time();
		$GenList = ftree_field_query_ext($DBHandle,"SELECT item_id FROM field_data_times WHERE item_id=$ItemID;");
		if ($GenList == false)
		{
			mysqli_query($DBHandle,"INSERT INTO field_data_times (item_id,last_field_update,last_search_gen) VALUES ($ItemID,-1,$LocalTime);");
		}
		else
		{
			mysqli_query($DBHandle,"UPDATE field_data_times SET last_search_gen=$LocalTime WHERE item_id=$ItemID;");
		}

		$ExportResults[$ItemID] = "OK";
	}

	return($ExportResults);
}

// Export an archive to search documents.  Each collection and subgroup is used to generate
// a search document which takes users to that point in the folder tree.
// ----------------------------------------------------------------------------------------
function ftree_export_archive_headings($DBHandle,$TopFolder)
{
	// Recursively process child entries

	$ChildList = ftree_list_child_objects($DBHandle,$TopFolder,false,false,FTREE_OBJECT_TYPE_FOLDER,false,false);
	if ($ChildList == false)
	{
		$ChildList = array();
	}

	foreach($ChildList as $ChildRecord)
	{
		$ChildID = $ChildRecord["item_id"];
		$ChildItemType = ftree_get_property($GLOBALS["aib_db"],$ChildID,$TypePropertyName);
		if ($ChildItemType != $ChildType)
		{
			continue;
		}

		switch($ChildItemType)
		{
			case AIB_ITEM_TYPE_ARCHIVE:
			case AIB_ITEM_TYPE_SUBGROUP:
			case AIB_ITEM_TYPE_COLLECTION:
				ftree_export_archive_headings($DBHandle,$ChildID);
				break;

			default:
				break;
		}

		// Generate index file for the current item

		$ArchiveInfo = ftree_get_archive_and_archive_group($DBHandle,$ChildID);
		if ($ArchiveInfo["archive_group"] == false || $ArchiveInfo["archive"] == false)
		{
			return;
		}

		$ArchiveID = $ArchiveInfo["archive"]["item_id"];
		$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];

		// Do substitutions for path

		$TimeSegs = ftree_field_split_microtime(microtime(true));
		$PathValue = ftree_field_do_hard_subs($BasePath,$ChildID,$Counter,$TimeSegs[0],$TimeSegs[1],array());

		// Add hashed segments to path, where the hashed segments are the zero-prefixed, hex numeric
		// value of the item ID split into 2-character segments.

		$SegmentList = str_split(sprintf("%08x",$ItemID),2);
		if ($ArchiveGroupID !== false)
		{
			$PathValue = $PathValue."/".sprintf("%08x",$ArchiveGroupID);
		}

		if ($ArchiveID !== false)
		{
			$PathValue = $PathValue."/".sprintf("%08x",$ArchiveID);
		}

		$PathValue = $PathValue."/".join("/",$SegmentList);

		// Do substitutions for URI

		$URIValue = ftree_field_do_hard_subs($BaseURI,$ChildID,$Counter,$TimeSegs[0],$TimeSegs[1],array());

		// Create path if needed

		if (file_exists($PathValue) == false)
		{
			@mkdir($PathValue,0777,true);
			if (file_exists($PathValue) == false)
			{
				continue;
			}
		}

		// Create file name

		$PathValue .= "/".sprintf("%08x",$ItemID).".est";

		// Output data to file in Hyperestraier format (.est)

		$Handle = fopen($PathValue,"w");
		if ($Handle == false)
		{
			$ExportResults[$ItemID] = "FAIL,EST CREATE,$PathValue";
			continue;
		}

		// Get title path, removing first segment

		$TitlePathList = ftree_get_item_title_path($DBHandle,$ItemID);
		$TitleSegs = array();
		foreach($TitlePathList as $TitlePathRecord)
		{
			$TitleSegs[] = urldecode($TitlePathRecord["item_title"]);
		}

		array_shift($TitleSegs);
		$TitlePath = join("/",$TitleSegs);

		// Create mandatory search fields

		fputs($Handle,"@uri=$URIValue\n");
		fputs($Handle,"@title=$TitlePath\n");
		fputs($Handle,"@cdate=".date("Y-m-d H:i:s",$ItemRecord["item_create_stamp"])."\n");

		// Add archive title and archive group title

		fputs($Handle,"@archive_group_title=".urldecode($ArchiveInfo["archive_group"]["item_title"])."\n");
		fputs($Handle,"@archive_title=".urldecode($ArchiveInfo["archive"]["item_title"])."\n");
		fputs($Handle,"@item_id=$ChildID\n");
		
		// Output separator before text

		fputs($Handle,"\n");

		fputs($Handle,"Archive Group: ".urldecode($ArchiveInfo["archive_group"]["item_title"])."\n");
		fputs($Handle,"Archive: ".urldecode($ArchiveInfo["archive"]["item_title"])."\n");

		// Close .est file

		fclose($Handle);

	}

	return;
}

// Get a field by symbolic name
// ----------------------------
function ftree_get_field_by_symbolic_name($DBHandle,$FieldName)
{
	$Query = "SELECT * FROM field_def WHERE field_symbolic_name='$FieldName';";
	$ResultList = ftree_field_query_ext($DBHandle,$Query);
	if ($ResultList == false)
	{
		return(false);
	}

	return($ResultList[0]);
}

// Get field data for a single field
// ---------------------------------
function ftree_get_field_data($DBHandle,$FieldID,$ItemID)
{
	$OutData = array();
	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_data WHERE item_id=$ItemID AND field_id='$FieldID';");
	if ($RecordList != false)
	{
		foreach($RecordList as $FieldRecord)
		{
			$OutData[$FieldRecord["field_id"]] = urldecode($FieldRecord["field_value"]);
		}
	}

	$RecordList = ftree_field_query_ext($DBHandle,"SELECT * FROM field_longdata WHERE item_id=$ItemID AND field_id='$FieldID';");
	if ($RecordList != false)
	{
		foreach($RecordList as $FieldRecord)
		{
			$OutData[$FieldRecord["field_id"]] = urldecode($FieldRecord["field_value"]);
		}
	}

	if (isset($OutData[$FieldID]) == true)
	{
		return($OutData[$FieldID]);
	}

	return(false);
}

// Get a list of fields with symbolic names, optionally filtered by owner type, owner or user ID
// ---------------------------------------------------------------------------------------------
function ftree_list_symbolic_fields($DBHandle,$UserID = false,$OwnerType = false,$Owner = false)
{
	$Query = "SELECT * FROM field_def WHERE field_symbolic_name != '' AND field_symbolic_name != 'NULL'";

	if ($OwnerType !== false)
	{
		// Owner type is specified.  If the user and owner ID are blank, then
		// filter based on owner type only.

		if ($UserID === false && $Owner === false)
		{
			$Query .= " AND field_owner_type='$OwnerType'";
		}
		else
		{
			// There is a user ID and/or an owner ID.  If both present, then restrict to the supplied
			// owner type, but allow either user or owner ID.

			if ($UserID === false)
			{
				$Query .= " AND field_owner_type='$OwnerType' AND field_owner_id=$Owner";
			}
			else
			{
				$Query .= " AND field_owner_type='$OwnerType' AND (field_owner_id=$UserID OR field_owner_id=$Owner)";
			}
		}
	}
	else
	{
		// No owner type was specified.  If there is no user ID but there is an owner ID, then allow the owner ID
		// with a type of item.  If there is a user ID and no owner ID, then allow user ID and type of user.  If
		// both are present, then use a compound clause allowing an item owner and the owner ID, OR a user owner
		// and the user ID.

		if ($UserID === false)
		{
			if ($Owner !== false)
			{
				$Query .= " AND field_owner_type='".FTREE_OWNER_TYPE_ITEM."' AND field_owner_id=$Owner";
			}
		}
		else
		{
			if ($Owner !== false)
			{
				$Query .= " AND (field_owner_type='".FTREE_OWNER_TYPE_ITEM."' AND field_owner_id=$Owner) OR ".
					"(field_owner_type='".FTREE_OWNER_TYPE_USER."' AND field_owner_id=$UserID)";
			}
		}
	}

	if ($UserID === false && $OwnerType === false && $Owner === false)
	{
		$Query .= " ORDER BY field_owner_type DESC,field_title;";
	}
	else
	{
		$Query .= " ORDER BY field_title;";
	}

	$ResultList = ftree_field_query_ext($DBHandle,$Query);
	$OutList = array();
	if ($ResultList != false)
	{
		foreach($ResultList as $Row)
		{
			$NewRow = $Row;
			$NewRow["field_title"] = urldecode($Row["field_title"]);
			$NewRow["field_format"] = urldecode($Row["field_format"]);
			$OutList[] = $NewRow;
		}
	}

	return($OutList);
}


	

// List fields (no filtering by form)
// ----------------------------------
function ftree_list_fields($DBHandle,$UserID = false,$OwnerType = false,$Owner = false,$FilterDisabledFlag = false)
{
	$Query = "SELECT * FROM field_def";
	if ($UserID !== false || $OwnerType !== false || $Owner !== false)
	{
		$Query .= " WHERE ";
	}

	if ($OwnerType !== false)
	{
		// Owner type is specified.  If the user and owner ID are blank, then
		// filter based on owner type only.

		if ($UserID === false && $Owner === false)
		{
			$Query .= " field_owner_type='$OwnerType'";
		}
		else
		{
			// There is a user ID and/or an owner ID.  If both present, then restrict to the supplied
			// owner type, but allow either user or owner ID.

			if ($UserID === false)
			{
				$Query .= " field_owner_type='$OwnerType' AND field_owner_id=$Owner";
			}
			else
			{
				$Query .= " field_owner_type='$OwnerType' AND (field_owner_id=$UserID OR field_owner_id=$Owner)";
			}
		}
	}
	else
	{
		// No owner type was specified.  If there is no user ID but there is an owner ID, then allow the owner ID
		// with a type of item.  If there is a user ID and no owner ID, then allow user ID and type of user.  If
		// both are present, then use a compound clause allowing an item owner and the owner ID, OR a user owner
		// and the user ID.

		if ($UserID === false)
		{
			if ($Owner !== false)
			{
				$Query .= " field_owner_type='".FTREE_OWNER_TYPE_ITEM."' AND field_owner_id=$Owner";
			}
		}
		else
		{
			if ($Owner !== false)
			{
				$Query .= " (field_owner_type='".FTREE_OWNER_TYPE_ITEM."' AND field_owner_id=$Owner) OR ".
					"(field_owner_type='".FTREE_OWNER_TYPE_USER."' AND field_owner_id=$UserID)";
			}
		}
	}

	if ($UserID === false && $OwnerType === false && $Owner === false)
	{
		$Query .= " ORDER BY field_owner_type DESC,field_title;";
	}
	else
	{
		$Query .= " ORDER BY field_title;";
	}

	$ResultList = ftree_field_query_ext($DBHandle,$Query);
	$OutList = array();
	if ($ResultList != false)
	{
		foreach($ResultList as $Row)
		{
			$NewRow = $Row;
			$NewRow["field_title"] = urldecode($Row["field_title"]);
			$NewRow["field_format"] = urldecode($Row["field_format"]);
			if (ftree_field_is_disabled($NewRow) == true)
			{
				if ($FilterDisabledFlag == true)
				{
					continue;
				}

				$NewRow["_disabled"] = "Y";
			}
			else
			{
				$NewRow["_disabled"] = "N";
			}
				
			$OutList[] = $NewRow;
		}
	}

	return($OutList);
}

// List fields (no filtering by form)
// ----------------------------------
function ftree_list_forms($DBHandle,$UserID,$OwnerType = false)
{
	if ($UserID !== false)
	{
		if ($OwnerType !== false)
		{
			$Query = "SELECT * FROM form_def WHERE form_owner=$UserID AND form_owner_type='$OwnerType' ORDER BY form_title;";
		}
		else
		{
			$Query = "SELECT * FROM form_def WHERE form_owner=$UserID ORDER BY form_title;";
		}
	}
	else
	{
		if ($OwnerType !== false)
		{
			$Query = "SELECT * FROM form_def WHERE form_owner_type='$OwnerType' ORDER BY form_title;";
		}
		else
		{
			$Query = "SELECT * FROM form_def ORDER BY form_title;";
		}
	}

	$ResultList = ftree_field_query_ext($DBHandle,$Query);
	$OutList = array();
	if ($ResultList != false)
	{
		foreach($ResultList as $Row)
		{
			$NewRow = $Row;
			$NewRow["form_title"] = urldecode($Row["form_title"]);
			$OutList[] = $NewRow;
		}
	}

	return($OutList);
}

// Set form used for record
// ------------------------
function ftree_field_set_item_form($DBHandle,$ItemID,$FormID)
{
	$Query = "DELETE FROM form_item WHERE item_id=$ItemID;";
	mysqli_query($DBHandle,$Query);
	$Query = "INSERT INTO form_item (item_id,form_id) VALUES ($ItemID,$FormID);";
	mysqli_query($DBHandle,$Query);
}

// Get form used for record
// ------------------------
function ftree_field_get_item_form($DBHandle,$ItemID)
{
	$List = ftree_field_query_ext($DBHandle,"SELECT * FROM form_item WHERE item_id=$ItemID;");
	if ($List == false)
	{
		return(false);
	}

	return($List[0]["form_id"]);
}

// Get list of items using a form
// ------------------------------
function ftree_field_form_usage($DBHandle,$FormID)
{
	$List = ftree_field_query_ext($DBHandle,"SELECT * FROM form_item WHERE form_id=$FormID;");
	return($List);
}

?>
