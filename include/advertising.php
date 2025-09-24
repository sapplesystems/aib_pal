<?php

// =============================
// ADVERTISING DISPLAY FUNCTIONS
// =============================

// Get list of available advertisements.
// -------------------------------------
function aib_get_ad_list($DBHandle,$Type,$ItemID,$AdCategory)
{
	$OutList = array();
	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisments WHERE ad_type='$Type' AND ad_category='$AdCategory' AND item_id=$ItemID ORDER BY ad_sort_order;");
	if ($Result != false)
	{
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
	}

	return($OutList);
}

// Given a list of advertisement entries, either jumble into a random
// order, or simply return the same list.
// ------------------------------------------------------------------
function aib_order_ad_list($DBHandle,$OrderType,$AdvertisementList)
{
	$OutList = array();
	if ($OrderType == "random")
	{
		while(true)
		{
			$CurrentCount = count($AdvertisementList);
			if ($CurrentCount < 1)
			{
				break;
			}

			if ($CurrentCount < 2)
			{
				$OutList[] = array_pop($AdvertisementList);
			}
			else
			{
				$Index = mt_rand(0,$CurrentCount - 1);
				$OutList[] = $AdvertisementList[$Index];
				unset($AdvertisementList[$Index]);
			}
		}
	}
	else
	{
		$OutList = $AdvertisementList;
	}

	return($OutList);
}


// Generate the HTML for an advertisement block.
//
//	BlockTemplate: 	The template for the HTML surrounding the set of ads. The 
//			substitution variables are:
//
//	[[ITEMID]]
//	[[ITEMTITLE]]
//	[[ITEMPARENTID]]
//	[[ITEMPARENTTITLE]]
//	[[ARCHIVE]]
//	[[ARCHIVEGROUP]]
//	[[ARCHIVEGROUPTITLE]]
//	[[ARCHIVETITLE]]
//	[[DATETIME]]
//	[[TIMESTAMP]]
//	[[USERLOGIN]]
//	[[USERTITLE]]
//	[[USERID]]
//	[[HOSTNAME]]
//	[[ADSET]]
//
//	AdTemplate:	The template for the individual advertisements.  The substitution
//			variables are:
//
//	[[ITEMID]]
//	[[ITEMTITLE]]
//	[[ITEMPARENTID]]
//	[[ITEMPARENTTITLE]]
//	[[ARCHIVE]]
//	[[ARCHIVEGROUP]]
//	[[ARCHIVEGROUPTITLE]]
//	[[ARCHIVETITLE]]
//	[[ADIMAGESRC]]
//	[[ADURL]]
//	[[ADTITLE]]
//	[[ADMOUSEOVER]]
//	[[HOSTNAME]]
//	[[USERLOGIN]]
//	[[USERTITLE]]
//	[[USERID]]
//	[[DATETIME]]
//	[[TIMESTAMP]]
//
//
//	The output is the BlockTemplate with the set of ads embedded, each one using
//	the AdTemplate.
//

function aib_generate_ad_block($DBHandle,$ItemID,$UserID,$AdvertisementList,$BlockTemplate,$AdTemplate)
{
	// Get the item record

	$ItemRecord = ftree_get_item($DBHandle,$ItemParent);

	// Get the item parent record

	$ItemParentRecord =ftree_get_item($DBHandle,$ItemRecord["item_parent"]);

	// Get archive and archive group

	$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$ItemID);
	$Archive = $ArchiveInfo["archive"];
	$ArchiveGroup = $ArchiveInfo["archive_group"];
	if ($Archive == false)
	{
		$Archive = array("item_id" => "-1", "item_title" => "N/A");
	}

	if ($ArchiveGroup == false)
	{
		$ArchiveGroup = array("item_id" => "-1", "item_title" => "N/A");
	}

	// Get user info

	$UserRecord = ftree_get_user($DBHandle,$UserID);

	// Set up substitutions

	$LocalTime = time();
	$BlockVars = array(
		"ITEMID" => $ItemID,
		"ITEMTITLE" => urldecode($ItemRecord["item_title"]),
		"ITEMPARENTID" => $ItemRecord["item_parent"],
		"ITEMPARENTTITLE" => urldecode($ItemParentRecord["item_title"]),
		"ARCHIVE" => $Archive["item_id"],
		"ARCHIVEGROUP" => $ArchiveGroup["item_id"],
		"ARCHIVETITLE" => urldecode($Archive["item_title"]),
		"ARCHIVEGROUPTITLE" => urldecode($ArchiveGroup["item_title"]),
		"TIMESTAMP" => $LocalTime,
		"DATETIME" => date("m/d/Y H:i:s",$LocalTime),
		"USERLOGIN" => $UserRecord["user_login"],
		"USERTITLE" => $UserRecord["user_title"],
		"USERID" => $UserID,
		"HOSTNAME" => $_SERVER["HOST_NAME"],
		);
	$AdVars = array(
		"ITEMID" => $ItemID,
		"ITEMTITLE" => urldecode($ItemRecord["item_title"]),
		"ITEMPARENTID" => $ItemRecord["item_parent"],
		"ITEMPARENTTITLE" => urldecode($ItemParentRecord["item_title"]),
		"ARCHIVE" => $Archive["item_id"],
		"ARCHIVEGROUP" => $ArchiveGroup["item_id"],
		"ARCHIVETITLE" => urldecode($Archive["item_title"]),
		"ARCHIVEGROUPTITLE" => urldecode($ArchiveGroup["item_title"]),
		"TIMESTAMP" => $LocalTime,
		"DATETIME" => date("m/d/Y H:i:s",$LocalTime),
		"USERLOGIN" => $UserRecord["user_login"],
		"USERTITLE" => $UserRecord["user_title"],
		"USERID" => $UserID,
		"HOSTNAME" => $_SERVER["HOST_NAME"],
		);

	// Construct each advertisement

	$TemplateData = array();
	foreach($AdvertisementList as $AdInfo)
	{
		$LocalAdTemplate = $AdTemplate;
		$AdVars["ADIMAGESRC"] = $AdInfo["image_name"];
		$AdVars["ADURL"] = $AdInfo["target_url"];
		$AdVars["ADTITLE"] = urldecode($AdInfo["ad_title"]);
		$AdVars["ADMOUSEOVER"] = urldecode($AdInfo["ad_alt_title"]);
		foreach($AdVars as $VarName => $VarValue)
		{
			$LocalAdTemplate = str_replace("[[".$VarName."]]",$VarValue,$LocalAdTemplate);
		}

		$TemplateData[] = $LocalAdTemplate;
	}

	// Construct output buffer

	foreach($BlockVars as $VarName => $VarValue)
	{
		$BlockTemplate = str_replace("[[".$VarName."]]",$VarValue,$BlockTemplate);
	}

	$BlockTemplate = str_replace("[[ADSET]]",join("",$TemplateData),$BlockTemplate);
	return($BlockTemplate);
}

// Store an advertisement
//
// $Info is an associative array:
//
//	item_id		The item ID of the advertisement owner
//	ad_title	Advertisement title
//	ad_alt_title	Alternate (mouseover) title
//	image_name	Name of the local image for the ad
//	target_url	Target url (opened when ad is clicked on)
//	ad_sort_order	Integer sort order
//
// ----------------------
function aib_store_ad($DBHandle,$Info)
{
	$Query = "INSERT INTO advertisements (item_id,ad_title,ad_alt_title,image_Name,target_url,ad_sort_order) VALUES (";
	$ValueList = array(
		aib_get_with_default($Info,"item_id","-1"),
		"'".urlencode(aib_get_with_default($Info,"ad_title",""))."'",
		"'".urlencode(aib_get_with_default($Info,"ad_alt_title",""))."'",
		"'".urlencode(aib_get_with_default($Info,"image_name",""))."'",
		"'".urlencode(aib_get_with_default($Info,"target_url",""))."'",
		aib_get_with_default($Info,"sort_order","1"),
		);
	$Query .= join(",",$ValueList).");";
	mysqli_query($DBHandle,$Query);
	$NewID = mysqli_insert_id($DBHandle);
	return($NewID);
}

// Get an advertisement
// --------------------
function aib_get_ad($DBHandle,$AdID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM advertisements WHERE ad_id=$AdID;");
	if ($Result != false)
	{
		$Row = mysqli_fetch_assoc($Result);
		mysqli_free_result($Result);
		return($Row);
	}

	return(false);
}

// Update an advertisement
//
// $Info is an associative array:
//
//	item_id		The item ID of the advertisement owner
//	ad_title	Advertisement title
//	ad_alt_title	Alternate (mouseover) title
//	image_name	Name of the local image for the ad
//	target_url	Target url (opened when ad is clicked on)
//	ad_sort_order	Integer sort order
//
// ----------------------
// -----------------------
function aib_update_ad($DBHandle,$AdID,$Info)
{
	$FieldList = array("item_id","ad_title","ad_alt_title","image_name","target_url","sort_order");
	$Query = "UPDATE advertisements SET ";
	$SetList = array();
	foreach($FieldList as $FieldName)
	{
		if (isset($Info[$FieldName]) == true)
		{
			$SetList[] = $FieldName."='".urlencode($Info[$FieldName])."'";
		}
	}

	$Query .= join(",",$SetList)." WHERE ad_id=$AdID;";
	mysqli_query($DBHandle,$Query);
	return(true);
}

// List advertisements
// -------------------
function aib_list_ads($DBHandle,$ItemID,$CategoryFilter = false)
{
	$Query = "SELECT * FROM advertisements WHERE item_id=$ItemID";
	if ($CategoryFilter != false)
	{
		$Query .= " AND ad_category='".urlencode($CategoryFilter)."'";
	}

	$Query .= " ORDER BY ad_sort_order;";
	$Result = mysqli_query($DBHandle,$Query);
	$OutList = array();
	if ($Result != false)
	{
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
	}

	return($OutList);
}

// List advertisement categories
// -----------------------------
function aib_list_ad_cats($DBHandle,$ItemID)
{
	$Query = "SELECT DISTINCT(ad_category) AS ad_category FROM advertisements WHERE item_id=$ItemID ORDER BY ad_category;";
	$Result = mysqli_query($DBHandle,$Query);
	$OutList = array();
	if ($Result != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			$OutList[] = $Row["ad_category"];
		}

		mysqli_free_result($Result);
	}

	return($OutList);
}

?>
