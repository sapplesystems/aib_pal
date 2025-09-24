<?php

//
// Tag management
//
// Tables:
//
//	ftree_tags
//		record_id
//		item_id
//		tag_value
//		tag_relevance (double)
//

// Store or update tags for an item
//
// Tags is an assoc array where the key
// is the tag value, data is relevance.
// If the relevance is set to zero, the
// tag value is deleted.
// ------------------------------------
function ftree_tags_store($DBHandle,$ItemID,$Tags)
{
	foreach($Tags as $TagName => $TagRelevance)
	{
		if ($TagRelevance == 0.0)
		{
			mysqli_query($DBHandle,"DELETE FROM ftree_tags WHERE item_id=$ItemID AND tag_value='$TagName';");
			continue;
		}

		$Result = mysqli_query($DBHandle,"SELECT item_id FROM ftree_tags WHERE item_id=$ItemID AND tag_value='$TagName';");
		if ($Result != false)
		{
			if (mysqli_num_rows($Result) > 0)
			{
				mysqli_query($DBHandle,"UPDATE ftree_tags SET tag_relevance=$TagRelevance WHERE item_id=$ItemID AND tag_value='$TagName';");
			}
			else
			{
				mysqli_query($DBHandle,"INSERT INTO ftree_tags (item_id,tag_value,tag_relevance) VALUES ($ItemID,'$TagName',$TagRelevance);");
			}
		}
		else
		{
			mysqli_query($DBHandle,"INSERT INTO ftree_tags (item_id,tag_value,tag_relevance) VALUES ($ItemID,'$TagName',$TagRelevance);");
		}
	}

	return(true);
}

// Get tags for an item
// --------------------
function ftree_tags_get_item_tags($DBHandle,$ItemID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM ftree_tags WHERE item_id=$ItemID;");
	if ($Result == false)
	{
		return(array());
	}

	$OutData = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutData[$Row["tag_value"]] = $Row["tag_relevance"];
	}

	mysqli_free_result($Result);
	return($OutData);
}

// Find all items with a tag, optionally returning a subset.  Items are always
// sorted by relevance.  Return is a list of records in the order retrieved.
// -----------------------------------------------------------------------------
function ftree_tags_find_items($DBHandle,$TagName,$Start = -1,$Count = -1)
{
	$Query = "SELECT * FROM ftree_tags WHERE tag_value='$TagName' ORDER BY tag_relevance DESC";
	if ($Start >= 0)
	{
		if ($Count > 0)
		{
			$Query .= " LIMIT $Start,$Count";
		}
		else
		{
			$Query .= " LIMIT $Start";
		}
	}
	
	$Query .= ";";
	$Result = mysqli_query($DBHandle,$Query);
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

// Delete tags for an item
// -----------------------
function ftree_tags_delete_item_tags($DBHandle,$ItemID)
{
	mysqli_query($DBHandle,"DELETE FROM ftree_tags WHERE item_id=$ItemID;");
	return(true);
}

// Delete a tag value for all items
// --------------------------------
function ftree_tags_delete_tag_global($DBHandle,$TagName)
{
	mysqli_query($DBHandle,"DELETE FROM ftree_tags WHERE tag_value='$TagName';");
	return(true);
}


?>
