<?php

//
// advert.php
//
// Advertising associated with tree items
//


// Log message
// -----------
function advert_log_debug($Msg)
{
	$Handle = fopen("/tmp/advert_log_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

// Create a hashed path for storing ad files.  Path name is base path plus
// hashed hexadecimal ID of advertisement record.
// ----------------------------------------------
function advert_hash_path($AdID)
{
	$IDString = sprintf("%'.010u",intval($AdID));
	$ChunkList = str_split($IDString,2);
	$Path = AIB_AD_STORAGE_PATH."/".join("/",$ChunkList);
	return($Path);
}

// Generate a storage file name given an ID
// ----------------------------------------
function advert_get_stored_file($AdID)
{
	$HashedPath = advert_hash_path($AdID);
	$StorageName = $HashedPath."/".sprintf("%'.010u",$AdID).".dat";
	return($StorageName);
}

// Get MIME type of file
// ---------------------
function advert_file_mimetype($Filename = "")
{
    $FileName = escapeshellcmd($Filename);
    $Command = "file -b --mime-type -m /usr/share/misc/magic {$FileName}";
    $MimeType = shell_exec($Command);
    return(trim($MimeType));
}

// Store ad definition
// -------------------
function advert_store($DBHandle,$Spec)
{
	$ReqList = array("item_id","ad_title","ad_url","ad_sort_order","ad_alt_title","inherit_flag","record_ref");
	foreach($ReqList as $Name)
	{
		if (isset($Spec[$Name]) == false)
		{
			return(array("status" => "ERROR", "msg" => "MISSING $Name"));
		}
	}

	// Check to see if filename is present

	if (isset($Spec["filename"]) == true && intval($Spec["record_ref"]) <= 0)
	{
		if (is_writeable($Spec["filename"]) == false)
		{
			return(array("status" => "ERROR", "msg" => "FILE NOT EXIST OR NOT USABLE"));
		}
	}

	if (intval($Spec["record_ref"]) > 0 && $Spec["filename"] != "")
	{
		return(array("status" => "ERROR", "msg" => "REFERENCE RECORDS CANNOT HAVE ATTACHED FILES"));
	}

	// Store record

	$ItemID = rawurlencode($Spec["item_id"]);
	$AdTitle = rawurlencode($Spec["ad_title"]);
	$AdURL = rawurlencode($Spec["ad_url"]);
	$AdSortOrder = rawurlencode($Spec["ad_sort_order"]);
	$AdAltTitle = rawurlencode($Spec["ad_alt_title"]);
	$RecordRef = rawurlencode($Spec["record_ref"]);
	$OriginalFile = $Spec["filename"];
	$OriginalSeg = explode("/",$OriginalFile);
	$OriginalFile = array_pop($OriginalSeg);
	$OriginalFile = rawurlencode($OriginalFile);
	$InheritFlag = preg_replace("/[^YN]/","",strtoupper(substr($Spec["inherit_flag"],0,1)));
	if (isset($Spec["disable_flag"]) == true)
	{
		$DisableFlag = preg_replace("/[^YN]/","",strtoupper(substr($Spec["disable_flag"],0,1)));
	}
	else
	{
		$DisableFlag = "N";
	}

	if ($InheritFlag == "")
	{
		$InheritFlag = "N";
	}

	if ($DisableFlag == "")
	{
		$DisableFlag = "N";
	}

	if (intval($Spec["record_ref"]) > 0)
	{
		$Result = mysqli_query($DBHandle,"SELECT record_id FROM advertisements where record_id='".$Spec["record_ref"]."';");
		if ($Result == false)
		{
		}

		if (mysqli_num_rows($Result) < 1)
		{
			mysqli_free_result($Result);
			return(array("status" => "ERROR", "msg" => "REF RECORD NOT FOUND"));
		}

		mysqli_free_result($Result);
	}

	$Query = "INSERT INTO advertisements (item_id,ad_title,ad_url,ad_sort_order,ad_alt_title,inherit_flag,original_file,disable_flag,record_ref) VALUES ('";
	$FieldValues = join("','",array($ItemID,$AdTitle,$AdURL,$AdSortOrder,$AdAltTitle,$InheritFlag,$OriginalFile,$DisableFlag,$RecordRef))."');";
	$Query .= $FieldValues;
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "CANNOT INSERT: ".mysqli_error($DBHandle)));
	}

	$RecordID = mysqli_insert_id($DBHandle);
	if (isset($Spec["filename"]) == false)
	{
		return(array("status" => "OK", "record_id" => $RecordID));
	}

	// Generate hashed file name and store file if needed
	
	if (intval($Spec["record_ref"]) <= 0)
	{
		$HashedPath = advert_hash_path($RecordID);
		if (file_exists($HashedPath) == false)
		{
			mkdir($HashedPath,0777,true);
			if (file_exists($HashedPath) == false)
			{
				mysqli_query($DBHandle,"DELETE FROM advertisements WHERE record_id='$RecordID';");
				return(array("status" => "ERROR", "msg" => "CANNOT CREATE STORAGE PATH"));
			}
		}
	
		$StorageName = $HashedPath."/".sprintf("%'.010u",$RecordID).".dat";
	
		// Copy the file
	
		@copy($Spec["filename"],$StorageName);
		if (file_exists($StorageName) == false)
		{
			mysqli_query($DBHandle,"DELETE FROM advertisements WHERE record_id='$RecordID';");
			return(array("status" => "ERROR", "msg" => "CANNOT STORE FILE"));
		}
	}

	$OutData = array("status" => "OK", "msg" => "", "record_id" => $RecordID);
	return($OutData);
}

// Update file associated with advertisement
// -----------------------------------------
function advert_update_file($DBHandle,$RecordID,$OriginalFile)
{
	// Check to see if filename is present

	if (is_writeable($OriginalFile) == false)
	{
		return(array("status" => "ERROR", "msg" => "FILE NOT EXIST OR NOT USABLE"));
	}

	// Get current definition

	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE record_id='$RecordID';");
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	if (intval($Row["record_ref"]) > 0)
	{
		return(array("status" => "ERROR", "msg" => "REFERENCE RECORD ONLY"));
	}

	$HashedPath = advert_hash_path($RecordID);
	if (file_exists($HashedPath) == false)
	{
		mkdir($HashedPath,0777,true);
		if (file_exists($HashedPath) == false)
		{
			return(array("status" => "ERROR", "msg" => "CANNOT CREATE STORAGE PATH"));
		}
	}

	$StorageName = $HashedPath."/".sprintf("%'.010u",$RecordID).".dat";

	// Copy the file

	@copy($OriginalFile,$StorageName);
	if (file_exists($StorageName) == false)
	{
		return(array("status" => "ERROR", "msg" => "CANNOT STORE FILE; $OriginalFile -->  $StorageName"));
	}

	$OriginalSeg = explode("/",$OriginalFile);
	$OriginalFile = array_pop($OriginalSeg);
	$OriginalFile = rawurlencode($OriginalFile);
	$Query = "UPDATE advertisements set original_file='$OriginalFile' WHERE record_id='$RecordID';";
	mysqli_query($DBHandle,$Query);
	$OutData = array("status" => "OK", "msg" => "", "record_id" => $RecordID);
	return($OutData);
}


// Update definition (will NOT update file)
// ----------------------------------------
function advert_update($DBHandle,$Spec)
{
	$ReqList = array("record_id");
	foreach($ReqList as $Name)
	{
		if (isset($Spec[$Name]) == false)
		{
			return(array("status" => "ERROR", "msg" => "MISSING $Name"));
		}
	}

	// Get current definition

	$RecordID = $Spec["record_id"];
	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE record_id='$RecordID';");
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}


	$ReqList = array("ad_title","ad_url","ad_sort_order","ad_alt_title","inherit_flag","disable_flag","record_ref");
	$UpdateList = array();
	foreach($ReqList as $Name)
	{
		if (isset($Spec[$Name]) == true)
		{
			$Value = "";
			switch($Name)
			{
				case "inherit_flag":
					$Value = preg_replace("/[^YN]/","",strtoupper($Spec["inherit_flag"]));
					break;

				case "disabled_flag":
					$Value = preg_replace("/[^YN]/","",strtoupper($Spec["disable_flag"]));
					break;

				default:
					$Value = rawurlencode($Spec[$Name]);
					break;
			}

			$UpdateList[] = $Name."='".$Value."'";
		}
	}

	if (count($UpdateList) < 1)
	{
		return(array("status" => "ERROR", "msg" => "NOTHING TO UPDATE"));
	}


	// Update record

	$Query = "UPDATE advertisements SET ".join(",",$UpdateList)." WHERE record_id='$RecordID';";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "CANNOT UPDATE DATBASE: ".mysqli_error($DBHandle)));
	}

	return(array("status" => "OK", "msg" => ""));
}

// Get reference record
// --------------------
function advert_get_ref_record($DBHandle,$RefID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE record_id='$RefID';");
	if ($Result == false)
	{
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(false);
	}

	$OutRecord = array();
	foreach($Row as $Name => $Value)
	{
		if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
		{
			$OutRecord[$Name] = $Value;
		}
		else
		{
			$OutRecord[$Name] = rawurldecode($Value);
		}
	}

	return($OutRecord);
}

// Get ad definition
// -----------------
function advert_get($DBHandle,$RecordID)
{
	// Get current definition

	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE record_id='$RecordID';");
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$OutRecord = array();
	foreach($Row as $Name => $Value)
	{
		if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
		{
			$OutRecord[$Name] = $Value;
		}
		else
		{
			$OutRecord[$Name] = rawurldecode($Value);
		}
	}

	if (intval($Row["record_ref"]) > 0)
	{
		$RefRecord = advert_get_ref_record($DBHandle,$Row["record_ref"]);
		if ($RefRecord == false)
		{
			return(array("status" => "ERROR", "msg" => "INVALID AD RECORD REFERENCE"));
		}

		$OutRecord["source_def"] = array();
		foreach($RefRecord as $Name => $Value)
		{
			if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
			{
				$OutRecord["source_def"][$Name] = $Value;
			}
			else
			{
				$OutRecord["source_def"][$Name] = rawurldecode($Value);
			}
		}
	}



	$OutData = array("status" => "OK", "msg" => "", "record" => $OutRecord);
	return($OutData);
}

// Delete ad definition
// ---------------------
function advert_delete($DBHandle,$RecordID)
{
	// Get current definition

	if ($RecordID == "")
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE record_id='$RecordID';");
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	mysqli_free_result($Result);
	mysqli_query($DBHandle,"DELETE FROM advertisements WHERE record_id='$RecordID';");
	mysqli_query($DBHandle,"DELETE FROM advertisements WHERE record_ref='$RecordID';");
	$OutData = array("status" => "OK", "msg" => "");
	return($OutData);
}

// Get ad file, return as buffer with MIME type
// --------------------------------------------
function advert_get_file($DBHandle,$RecordID)
{
	// Get current definition

	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE record_id='$RecordID';");
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("status" => "ERROR", "msg" => "NOTFOUND"));
	}

	if (intval($Row["record_ref"]) > 0)
	{
		return(array("status" => "ERROR", "msg" => "REFERENCE RECORD ONLY"));
	}

	// Get MIME type of stored file

	$StoredName = advert_get_stored_file($RecordID);
	if (file_exists($StoredName) == false)
	{
		return(array("status" => "ERROR", "msg" => "NOFILE; $StoredName"));
	}

	$MIMEType = advert_file_mimetype($StoredName);
	$OriginalName = rawurldecode($Row["original_file"]);
	$OutData = array("status" => "OK", "msg" => "", "mime" => $MIMEType, "original_name" => $OriginalName, "data" => file_get_contents($StoredName));
	return($OutData);
}

// List ads defined for tree item
// ------------------------------
function advert_list_item_ads($DBHandle,$ItemID,$SortMethod = "sort_name")
{
	$SortName = "ad_sort_order";
	switch($SortMethod)
	{
		case "title":
			$SortName = "ad_title";
			break;

		case "url":
			$SortName = "ad_url";
			break;

		case "alt_title":
			$SortName = "ad_alt_title";
			break;

		case "id":
			$SortName = "record_id";
			break;

		default:
			break;

	}

	$Query = "SELECT * FROM advertisements WHERE item_id='$ItemID' ORDER BY $SortName;";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "QUERYFAILED: ".mysqli_error($DBHandle)));
	}

	$OutData = array("status" => "OK", "msg" => "", "data" => array("records" => array()));
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$NewRecord = array(
			"record_id" => $Row["record_id"],
			"item_id" => $Row["item_id"],
			"ad_title" => rawurldecode($Row["ad_title"]),
			"ad_url" => rawurldecode($Row["ad_url"]),
			"ad_sort_order" => rawurldecode($Row["ad_sort_order"]),
			"ad_alt_title" => rawurldecode($Row["ad_alt_title"]),
			"inherit_flag" => $Row["inherit_flag"],
			"disable_flag" => $Row["disable_flag"],
			"original_file" => rawurldecode($Row["original_file"]),
			"record_ref" => rawurldecode($Row["record_ref"]),
		);

		if (intval($Row["record_ref"]) > 0)
		{
			$RefRecord = advert_get_ref_record($DBHandle,$Row["record_ref"]);
			if ($RefRecord == false)
			{
				continue;
			}

			$NewRecord["source_def"] = array();
			foreach($RefRecord as $Name => $Value)
			{
				if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
				{
					$NewRecord["source_def"][$Name] = $Value;
				}
				else
				{
					$NewRecord["source_def"][$Name] = rawurldecode($Value);
				}
			}
		}

		$OutData["data"]["records"][] = $NewRecord;
	}

	mysqli_free_result($Result);
	return($OutData);
}

// Block or unblock an ad at a given item.  Blocking can
// be inherited until another block/unblock record is seen.
//
// [
// 	ad_record_id: {
// 		"b":"Y/N",		If "Y", then ad "ad_record_id" is blocked
// 		"i":"Y/N"		If "Y", then block is inheritable.
// 		},
// 		...
// ]
//
// Opcode may be:
//
// 	block
// 	unblock
// 	delete (delete spec for this ad)
//
// InheritFlag may be:
// 	Y
// 	N
// --------------------------------------------------
function advert_set_block($DBHandle,$ItemID,$AdID,$OpCode,$InheritFlag)
{
	$InhibitPropValue = ftree_get_long_property($DBHandle,$ItemID,"_ads_inhibit");
	if ($InhibitPropValue == false)
	{
		$BlockSpec = array();
	}
	else
	{
		$BlockSpec = json_decode($InhibitPropValue,true);
	}

	// Make sure the ads referenced actually exist.  If not, delete the ones that don't and save
	// the spec string again.

	$TempSpec = $BlockSpec;
	$ChangeFlag = false;
	foreach($BlockSpec as $LocalID)
	{
		$Temp = advert_get($DBHandle,$LocalID);
		if ($Temp["status"] != "OK")
		{
			unset($TempSpec[$LocalID]);
			$ChangeFlag = true;
		}

		if (intval($Temp["record"]["record_ref"]) > 0)
		{
			$TempID = $Temp["record_ref"];
			$RefTemp = advert_get($DBHandle,$TempID);
			if ($RefTemp["status"] != "OK")
			{
				unset($TempSpec[$LocalID]);
				$ChangeFlag = true;
			}
		}
	}

	if ($ChangeFlag == true)
	{
		$SpecString = json_encode($BlockSpec);
		ftree_set_long_property($DBHandle,$ItemID,"_ads_inhibit",$SpecString);
	}

	// See if the ad ID given is a reference.  If so, store the actual (referred) ID.

	$AdvertInfo = advert_get($DBHandle,$AdID);
	$LocalAdID = $AdID;
	if (intval($AdvertInfo["record"]["record_ref"]) > 0)
	{
		$LocalAdID = $AdvertInfo["record"]["record_ref"];
	}

	switch(strtolower($OpCode))
	{
		case "block":
			$BlockSpec[$LocalAdID] = array("b" => "Y", "i" => strtoupper(substr($InheritFlag,0,1)),"o" => $AdID);
			break;

		case "unblock":
			$BlockSpec[$LocalAdID] = array("b" => "N", "i" => strtoupper(substr($InheritFlag,0,1)),"o" => $AdID);
			break;

		case "delete":
			if (isset($BlockSpec[$LocalAdID]) == true)
			{
				unset($BlockSpec[$LocalAdID]);
			}

			break;

		default:
			return(array("status" => "ERROR", "msg" => "INVALID OPCODE"));
	}

	$SpecString = json_encode($BlockSpec);
	ftree_set_long_property($DBHandle,$ItemID,"_ads_inhibit",$SpecString);
	return(array("status" => "OK", "msg" => ""));

}

// List ad blocks for a given item
// -------------------------------
function advert_list_blocks($DBHandle,$ItemID)
{
	$InhibitPropValue = ftree_get_long_property($DBHandle,$ItemID,"_ads_inhibit");
	if ($InhibitPropValue == false)
	{
		$BlockSpec = array();
	}
	else
	{
		$BlockSpec = json_decode($InhibitPropValue,true);
	}

	$OutData = array("status" => "OK", "msg" => "", "info" => array());
	foreach($BlockSpec as $AdID => $SpecRecord)
	{
		$AdvertisementInfo = advert_get($DBHandle,$AdID);
		if ($AdvertisementInfo["status"] != "OK")
		{
			continue;
		}

		$OutData["info"][] = array("ad_id" => $AdID, "block" => $SpecRecord["b"], "inherit" => $SpecRecord["i"], "def" => $AdvertisementInfo["record"]);
	}

	return($OutData);
}


// List ads visible for tree item based on item path.  An ad is visible in a child item only if the "inherit" flag
// is set to "Y" on the ad in the parent(s).  Output is in order from root to leaf.
// ---------------------------------------------------------------------------------------------------------------
function advert_visible_ads($DBHandle,$ItemID)
{
	$OutData = array("status" => "OK", "msg" => "", "data" => array("records" => array()));

	// Get the path for the item
	
	$ItemPathList = ftree_get_item_id_path($DBHandle,$ItemID);

	// Create blocking spec array
	
	$BlockSpec = array();
	$AdSpec = array();

	// Process path from top to bottom
	
	foreach($ItemPathList as $PathID)
	{
		// Get the ads for the current item

		$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE item_id='$PathID';");
		if ($Result == false)
		{
			continue;
		}

		if (mysqli_num_rows($Result) < 1)
		{
			mysqli_free_result($Result);
			continue;
		}

		// Get inhibit specs, if any

		$InhibitPropValue = ftree_get_long_property($DBHandle,$PathID,"_ads_inhibit");
		if ($InhibitPropValue != false)
		{
			$InhibitPropSpec = json_decode($InhibitPropValue,true);
		}
		else
		{
			$InhibitPropSpec = array();
		}


		// For each ad, check for inherit and disable flags.  If the ad can be inherited,
		// and it is not disabled, it can be shown.

		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			// Save ad definition

			$CurrentAdID = false;
			if (intval($Row["record_ref"]) > 0)
			{
				$RefRecord = advert_get_ref_record($DBHandle,$Row["record_ref"]);
				if ($RefRecord != false)
				{
					$CurrentAdID = $RefRecord["record_id"];
					if (isset($AdSpec[$RefRecord["record_id"]]) == false)
					{
						$AdSpec[$RefRecord["record_id"]] = $RefRecord;
					}
				}
			}
			else
			{
				$CurrentAdID = $Row["record_id"];
				if (isset($AdSpec[$Row["record_id"]]) == false)
				{
					$AdSpec[$Row["record_id"]] = $Row;
				}
			}

			// See if this ad is blocked or allowed

			if (isset($InhibitPropSpec[$CurrentAdID]) == true)
			{
				$BlockSpec[$CurrentAdID] = array("spec_id" => $PathID, "spec" => $InhibitPropSpec[$CurrentAdID]);
			}

			// If ad is current blocked, next.  Note that if an ad has been re-allowed
			// at a lower level, the block spec will contain "Y" for the given ad.

			if (isset($BlockSpec[$CurrentAdID]) == true)
			{
				$LocalSpec = $BlockSpec[$CurrentAdID];
				if ($LocalSpec["spec_id"] != $PathID)
				{
					if ($LocalSpec["spec"]["i"] == "Y")
					{
						if ($LocalSpec["spec"]["b"] == "Y")
						{
							continue;
						}
					}
				}
				else
				{
					if ($LocalSpec["spec"]["b"] == "Y")
					{
						continue;
					}
				}

			}


			if (($Row["inherit_flag"] == "Y" && $Row["disable_flag"] == "N") || ($Row["item_id"] == $ItemID && $Row["disable_flag"] == "N"))
			{
				$NewRecord = array(
					"record_id" => $Row["record_id"],
					"item_id" => $Row["item_id"],
					"ad_title" => rawurldecode($Row["ad_title"]),
					"ad_url" => rawurldecode($Row["ad_url"]),
					"ad_sort_order" => rawurldecode($Row["ad_sort_order"]),
					"ad_alt_title" => rawurldecode($Row["ad_alt_title"]),
					"inherit_flag" => $Row["inherit_flag"],
					"disable_flag" => $Row["disable_flag"],
					"original_file" => rawurldecode($Row["original_file"]),
					"record_ref" => rawurldecode($Row["record_ref"]),
				);

				if (intval($Row["record_ref"]) > 0)
				{
					$RefRecord = advert_get_ref_record($DBHandle,$Row["record_ref"]);
					if ($RefRecord == false)
					{
						continue;
					}

					$NewRecord["source_def"] = array();
					foreach($RefRecord as $Name => $Value)
					{
						if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
						{
							$NewRecord["source_def"][$Name] = $Value;
						}
						else
						{
							$NewRecord["source_def"][$Name] = rawurldecode($Value);
						}
					}
				}

				$OutData["data"]["records"][] = $NewRecord;
			}
		}

		mysqli_free_result($Result);
	}

	return($OutData);
}


// List ads for tree item path, regardless of visibility.
// ------------------------------------------------------
function advert_path_ads($DBHandle,$ItemID)
{
	$OutData = array("status" => "OK", "msg" => "", "data" => array("records" => array()));

	// Get the path for the item
	
	$ItemPathList = ftree_get_item_id_path($DBHandle,$ItemID);
	foreach($ItemPathList as $PathID)
	{
		// Get the ads for the current item

		$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE item_id='$PathID';");
		if ($Result == false)
		{
			continue;
		}

		if (mysqli_num_rows($Result) < 1)
		{
			mysqli_free_result($Result);
			continue;
		}

		// For each ad, check for inherit and disable flags.  If the ad can be inherited,
		// and it is not disabled, it can be shown.

		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			if ($Row["disable_flag"] == "Y")
			{
				continue;
			}

			$NewRecord = array(
				"record_id" => $Row["record_id"],
				"item_id" => $Row["item_id"],
				"ad_title" => rawurldecode($Row["ad_title"]),
				"ad_url" => rawurldecode($Row["ad_url"]),
				"ad_sort_order" => rawurldecode($Row["ad_sort_order"]),
				"ad_alt_title" => rawurldecode($Row["ad_alt_title"]),
				"inherit_flag" => $Row["inherit_flag"],
				"disable_flag" => $Row["disable_flag"],
				"original_file" => rawurldecode($Row["original_file"]),
				"record_ref" => rawurldecode($Row["record_ref"]),
			);

			if (intval($Row["record_ref"]) > 0)
			{
				$RefRecord = advert_get_ref_record($DBHandle,$Row["record_ref"]);
				if ($RefRecord == false)
				{
					continue;
				}

				$NewRecord["source_def"] = array();
				foreach($RefRecord as $Name => $Value)
				{
					if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
					{
						$NewRecord["source_def"][$Name] = $Value;
					}
					else
					{
						$NewRecord["source_def"][$Name] = rawurldecode($Value);
					}
				}
			}

			$OutData["data"]["records"][] = $NewRecord;
		}

		mysqli_free_result($Result);
	}

	return($OutData);
}

// Given an advertisement record, find all references to that advertisement
// ------------------------------------------------------------------------
// List ads defined for tree item
// ------------------------------
function advert_list_references($DBHandle,$RecordID)
{
	$Query = "SELECT * FROM advertisements WHERE record_ref='$RecordID';";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("status" => "ERROR", "msg" => "QUERYFAILED"));
	}

	$OutData = array("status" => "OK", "msg" => "", "data" => array("records" => array()));
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$NewRecord = array(
			"record_id" => $Row["record_id"],
			"item_id" => $Row["item_id"],
			"ad_title" => rawurldecode($Row["ad_title"]),
			"ad_url" => rawurldecode($Row["ad_url"]),
			"ad_sort_order" => rawurldecode($Row["ad_sort_order"]),
			"ad_alt_title" => rawurldecode($Row["ad_alt_title"]),
			"inherit_flag" => $Row["inherit_flag"],
			"disable_flag" => $Row["disable_flag"],
			"original_file" => rawurldecode($Row["original_file"]),
			"record_ref" => rawurldecode($Row["record_ref"]),
		);

		if (intval($Row["record_ref"]) > 0)
		{
			$RefRecord = advert_get_ref_record($DBHandle,$Row["record_ref"]);
			if ($RefRecord == false)
			{
				continue;
			}

			$NewRecord["source_def"] = array();
			foreach($RefRecord as $Name => $Value)
			{
				if ($Name == "record_id" || $Name == "item_id" || $Name == "disable_flag" || $Name == "inherit_flag")
				{
					$NewRecord["source_def"][$Name] = $Value;
				}
				else
				{
					$NewRecord["source_def"][$Name] = rawurldecode($Value);
				}
			}
		}

		$OutData["data"]["records"][] = $NewRecord;
	}

	mysqli_free_result($Result);
	return($OutData);
}
?>
