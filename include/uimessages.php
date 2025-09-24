<?php

//
// uimessages.php
//
// Programmable messages for AIB elements
//


// Store message
// -------------
function uimsg_store_message($DBHandle,$Spec)
{
	$ReqList = array("element_id","language_code","element_text","element_name","element_location");
	$ElementID = rawurlencode($Spec["element_id"]);
	$Query = "SELECT record_id FROM ui_messages WHERE element_id='$ElementID';";
	$Result = mysqli_query($DBHandle,$Query);
	$StorageQuery = false;
	if ($Result != false)
	{
		$Row = mysqli_fetch_assoc($Result);
		mysqli_free_result($Result);
		if ($Row != false)
		{
			$RecordID = $Row["record_id"];

			// URL encode everything

			$UpdateElements = array();
			$AvailableSpec = array("language_code","element_text","element_name","element_location");
			foreach($AvailableSpec as $LocalName)
			{
				if (isset($Spec[$LocalName]) == true)
				{
					$UpdateElements[] = $LocalName."='".rawurlencode($Spec[$LocalName])."'";
				}
			}

			$ElementString = join(",",$UpdateElements);

			$StorageQuery = "UPDATE ui_messages SET $ElementString WHERE record_id='$RecordID';";
		}
	}

	if ($StorageQuery == false)
	{
		foreach($ReqList as $Name)
		{
			if (isset($Spec[$Name]) == false)
			{
				return(array("status" => "ERROR", "msg" => "MISSING $Name"));
			}
		}

		$LanguageCode = rawurlencode($Spec["language_code"]);
		$ElementText = rawurlencode($Spec["element_text"]);
		$ElementName = rawurlencode($Spec["element_name"]);
		$ElementLocation = rawurlencode($Spec["element_location"]);
		$StorageQuery = "INSERT INTO ui_messages (element_id,language_code,element_text,element_name,element_location) VALUES ('";
		$StorageQuery .= join("','",array($ElementID,$LanguageCode,$ElementText,$ElementName,$ElementLocation))."');";
	}

	$ErrorCode = mysqli_query($DBHandle,$StorageQuery);
	if ($ErrorCode == false)
	{
		return(array("status" => "ERROR", "msg" => "CANNOTSTORE: ".mysqli_error($DBHandle)));
	}

	return(array("status" => "OK", "msg" => "OK"));
}

// Retrieve message by element ID
// ------------------------------
function uimsg_get_by_element_id($DBHandle,$ElementID)
{
	$LocalElementID = rawurlencode($ElementID);
	$Query = "SELECT * FROM ui_messages WHERE element_id='$LocalElementID';";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "QUERYFAILED"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$OutData = array("status" => "OK", "msg" => "", "data" => array());
	$OutList = array("element_id","language_code","element_text","element_name","element_location");
	foreach($OutList as $OutName)
	{
		$OutData["data"][$OutName] = rawurldecode($Row[$OutName]);
	}

	return($OutData);
}


// Retrieve message by element name and location
// ---------------------------------------------
function uimsg_get_by_name_and_location($DBHandle,$ElementName,$ElementLocation)
{
	$LocalElementName = rawurlencode($ElementName);
	$LocalElementLocation = rawurlencode($ElementLocation);
	$Query = "SELECT * FROM ui_messages WHERE element_name='$LocalElementName' AND element_location='$LocalElementLocation';";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "QUERYFAILED"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$OutData = array("status" => "OK", "msg" => "", "data" => array());
	$OutList = array("element_id","language_code","element_text","element_name","element_location");
	foreach($OutList as $OutName)
	{
		$OutData["data"][$OutName] = rawurldecode($Row[$OutName]);
	}

	return($OutData);
}

// Get all elements for a location
// -------------------------------
function uimsg_get_location_elements($DBHandle,$ElementLocation)
{
	$LocalElementLocation = rawurlencode($ElementLocation);
	$Query = "SELECT * FROM ui_messages WHERE element_location='$LocalElementLocation';";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "QUERYFAILED"));
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$OutData = array("status" => "OK", "msg" => "", "data" => array());
	$OutList = array("element_id","language_code","element_text","element_name","element_location");
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$TempRecord = array();
		foreach($OutList as $OutName)
		{
			$TempRecord[$OutName] = rawurldecode($Row[$OutName]);
		}

		$OutData["data"][] = $TempRecord;
	}

	mysqli_free_result($Result);
	return($OutData);
}

// Delete element message by element ID
// ------------------------------------
function uimsg_delete_element($DBHandle,$ElementID)
{
	$LocalElementID = rawurlencode($ElementID);
	$Query = "SELECT * FROM ui_messages WHERE element_id='$LocalElementID';";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "QUERYFAILED"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	mysqli_query($DBHandle,"DELETE FROM ui_messages WHERE record_id='".$Row["record_id"]."';");
	return(array("status" => "OK", "msg" => ""));
}


?>
