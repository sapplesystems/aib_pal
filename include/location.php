<?php
//
// Location processing
//
//

// Log a debugging message
// -----------------------
function location_log_debug($Msg)
{
	$Handle = fopen("/tmp/location_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,date("Y-m-d H:i:s").":$Msg\n");
		fclose($Handle);
	}
}

// Get result set from query
// -------------------------
function loc_query($DBHandle,$Query)
{
	$Result = mysqli_query($DBHandle,$Query,MYSQLI_USE_RESULT);
	if ($Result == false)
	{
		return(array());
	}

	$OutList = mysqli_fetch_all($Result,MYSQLI_ASSOC);
	mysqli_free_result($Result);
	return($OutList);
}

// Do query and return false if empty set
// --------------------------------------
function loc_query_ext($DBHandle,$Query)
{
	$ResultList = loc_query($DBHandle,$Query);
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
function loc_insert_with_id($DBHandle,$Query)
{
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result === false)
	{
		return(false);
	}

	return(mysqli_insert_id($DBHandle));
}

// Get item location data
// ----------------------
function loc_get($DBHandle,$ItemID)
{
	$ResultSet = loc_query($DBHandle,"SELECT * FROM item_loc WHERE item_id='$ItemID' LIMIT 1;");
	if ($ResultSet == false)
	{
		return($ResultSet);
	}
	else
	{
		return($ResultSet[0]);
	}
}

// Set default value for numeric values
// ------------------------------------
function loc_default_num($InValue,$Default = 0.0)
{
	if ($InValue == "")
	{
		return($Default);
	}

	return(floatval($InValue));
}


// Set item location data
// ----------------------
function loc_set($DBHandle,$ItemID,$Context,$Lat,$Lon,$Alt = "0.0")
{
	mysqli_query($DBHandle,"DELETE FROM item_loc WHERE item_id='$ItemID';");
	$LocalAlt = loc_default_num($Alt);
	$LocalLat = loc_default_num($Lat);
	$LocalLon = loc_default_num($Lon);
	if ($Context == "")
	{
		$LocalContext = "DEFAULT";
	}
	else
	{
		$LocalContext = $Context;
	}

	$Result = mysqli_query($DBHandle,"INSERT INTO item_loc (item_id,location_context,lat,lon,alt) VALUES ('$ItemID','$LocalContext','$LocalLat','$LocalLon','$LocalAlt');");
	if ($Result == false)
	{
		return(false);
	}

	return(true);
}


// Clear item location
// -------------------
function loc_clear($DBHandle,$ItemID)
{
	mysqli_query($DBHandle,"DELETE FROM item_loc WHERE item_id='$ItemID';");
	return(true);
}

// Get all items within a square bounded by coordinates
// ----------------------------------------------------
function loc_find($DBHandle,$Context,$Coordinates,$AltLow = false,$AltHigh = false)
{
	$LatLow = $Coordinates["y2"];
	$LatHigh = $Coordinates["y1"];
	$LonLow = $Coordinates["x1"];
	$LonHigh = $Coordinates["x2"];
	$Query = "SELECT * FROM item_loc";
	$AndFlag = false;
	if ($Context != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " location_context='$Context'";
		$AndFlag = true;
	}

	if ($LatLow != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " lat >= $LatLow";
		$AndFlag = true;
	}

	if ($LatHigh != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " lat <= $LatHigh";
		$AndFlag = true;
	}

	if ($LonLow != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " lon >= $LonLow";
		$AndFlag = true;
	}

	if ($LonHigh != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " lon <= $LonHigh";
		$AndFlag = true;
	}

	if ($AltLow != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " alt >= $LatLow";
		$AndFlag = true;
	}

	if ($AltHigh != false)
	{
		if ($AndFlag != false)
		{
			$Query .= " AND";
		}
		else
		{
			$Query .= " WHERE";
		}

		$Query .= " alt <= $AltHigh";
		$AndFlag = true;
	}

	$Query .= ";";

	$ResultSet = loc_query_ext($DBHandle,$Query);
	return(array("query" => $Query, "records" => $ResultSet));
}


function loc_calc_longitude_distance($Latitude)
{
	$Distance = cos($Latitude) * 69.172;
	if ($Distance < 0.0)
	{
		$Distance = $Distance * -1.0;
	}

	return($Distance);
}

function loc_lat_distance($Latitude)
{
	return(111.0);
}
?>
