<?php
//
// Do a location-based search and update the location search data
//
//
//

include('api_util.php');
include('../include/location.php');

function log_debug($Msg)
{
	$Handle = fopen("/tmp/loc_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function check_param($ParamList,$FormData)
{
	foreach($ParamList as $Name)
	{
		if (isset($FormData[$Name]) == false)
		{
			return(array(false,$Name));
		}
	}

	return(array(true));
}

function test_property_value($PropertyValue,$Operand,$Value1,$Value2 = false)
{
	switch(strtoupper($Operand))
	{
		case "EQ":
			if ($PropertyValue == $Value1)
			{
				return(true);
			}

			break;

		case "NE":
			if ($PropertyValue != $Value1)
			{
				return(true);
			}

			break;

		case "LT":
			if ($PropertyValue < $Value1)
			{
				return(true);
			}

			break;

		case "GT":
			if ($PropertyValue > $Value1)
			{
				return(true);
			}

			break;

		case "LE":
			if ($PropertyValue <= $Value1)
			{
				return(true);
			}

			break;

		case "GE":
			if ($PropertyValue >= $Value1)
			{
				return(true);
			}

			break;

		case "IR":
			if ($Value2 == false)
			{
				$Value2 = $Value1;
			}

			if ($PropertyValue >= $Value1 && $PropertyValue <= $Value2)
			{
				return(true);
			}

			break;

		case "ER":
			if ($PropertyValue < $Value1 || $PropertyValue > $Value2)
			{
				return(true);
			}

			break;

		case "RE":
			if (preg_match("/".$Value1."/",$PropertyValue) != false)
			{
				return(true);
			}

			break;

		default:
			break;

	}

	return(false);
}

function get_assoc_default($InArray,$Key,$Default = false)
{
	if (isset($InArray[$Key]) == true)
	{
		return($InArray[$Key]);
	}

	return($Default);
}

function get_form_data()
{
	$OutData = array();
	foreach($_GET as $Name => $Value)
	{
		$OutData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$OutData[$Name] = $Value;
	}

	return($OutData);
}

function get_option_value($FormData,$Name)
{
	if (isset($FormData[$Name]) == false)
	{
		return(false);
	}

	if (preg_match("/[Yy1]/",$FormData[$Name]) != false)
	{
		return(true);
	}

	return(false);
}


// #########
// MAIN CODE
// #########


	aib_open_db();

	// Get API key and session, then validate

	$FormData = get_form_data();
	$APIKey = get_assoc_default($FormData,"_key",false);
	$APISession = get_assoc_default($FormData,"_session",false);
	if ($APIKey == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "msg" => "MISSINGKEY"));
		exit(0);
	}

	if ($APISession == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "msg" => "MISSINGSESSION"));
		exit(0);
	}

	$Result = aib_api_validate_session_key($GLOBALS["aib_db"],$APIKey,$APISession,HIDE_MAX_API_SESSION);
	if ($Result[0] != "OK")
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "msg" => $Result[1]));
		exit(0);
	}

	// Get keyholder

	$KeyHolderID = aib_api_get_key_id($GLOBALS["aib_db"],$APIKey);
	if ($KeyHolderID == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "msg" => "KEYHOLDERIDNOTFOUND"));
		exit(0);
	}

	// Get opcode

	$OpCode = get_assoc_default($FormData,"_op",false);
	if ($OpCode == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "msg" => "MISSING OP"));
		exit(0);
	}

	// Process opcode

	switch(strtolower($OpCode))
	{
		case "set":
			$ParamCheck = check_param(array("obj_id","lat","lon","alt"),$FormData);
			if ($ParamCheck[0] == false)
			{
				$Missing = $ParamCheck[1];
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "MISSING PARAM $Missing"));
				exit(0);
			}

			$ObjID = $FormData["obj_id"];
			$Lat = $FormData["lat"];
			$Lon = $FormData["lon"];
			$Alt = $FormData["alt"];
			if (isset($FormData["context"]) == true)
			{
				$Context = $FormData["context"];
			}
			else
			{
				$Context = "DEFAULT";
			}

			// Make sure the object exists

			$ItemInfo = ftree_get_item($GLOBALS["aib_db"],$ObjID);
			if ($ItemInfo == false)
			{
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "ITEM NOT FOUND $ObjID"));
				exit(0);
			}

			// Set properties for all values.  These are formatted as "location.lat", "location.lon", "location.alt".

			ftree_set_property($GLOBALS["aib_db"],$ObjID,"location.lat",$Lat);
			ftree_set_property($GLOBALS["aib_db"],$ObjID,"location.lon",$Lon);
			ftree_set_property($GLOBALS["aib_db"],$ObjID,"location.alt",$Alt);

			// Save values in location table

			if (loc_set($GLOBALS["aib_db"],$ObjID,$Context,$Lat,$Lon,$Alt) == false)
			{
				$ResponseArray = array("status" => "ERROR", "msg" => "CANNOT INSERT INTO ITEM_LOC");
			}
			else
			{
				$ResponseArray = array("status" => "OK", "msg" => "$ObjID");
			}

			break;

		case "get":
			$ParamCheck = check_param(array("obj_id"),$FormData);
			if ($ParamCheck[0] == false)
			{
				$Missing = $ParamCheck[1];
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "MISSING PARAM $Missing"));
				exit(0);
			}

			$ObjID = $FormData["obj_id"];

			// Make sure the object exists

			$ItemInfo = ftree_get_item($GLOBALS["aib_db"],$ObjID);
			if ($ItemInfo == false)
			{
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "ITEM NOT FOUND $ObjID"));
				exit(0);
			}

			$Results = loc_get($GLOBALS["aib_db"],$ObjID);
			if ($Results == false)
			{
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "ITEM HAS NO LOCATION $ObjID"));
				exit(0);
			}

			// Get properties and send out any locations available.

			$Lat = $Results["lat"];
			$Lon = $Results["lon"];
			$Alt = $Results["alt"];
			$Context = $Results["location_context"];
			$ResponseArray = array("status" => "OK", "msg" => "$ObjID", "lat" => $Lat, "lon" => $Lon, "alt" => $Alt, "context" => $Context);
			break;

		case "clear":
			$ParamCheck = check_param(array("obj_id"),$FormData);
			if ($ParamCheck[0] == false)
			{
				$Missing = $ParamCheck[1];
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "MISSING PARAM $Missing"));
				exit(0);
			}

			$ObjID = $FormData["obj_id"];

			// Make sure the object exists

			$ItemInfo = ftree_get_item($GLOBALS["aib_db"],$ObjID);
			if ($ItemInfo == false)
			{
				loc_clear($GLOBALS["aib_db"],$ObjID);
				aib_close_db();
				aib_api_send_response(array("status" => "WARNING", "msg" => "ITEM NOT FOUND BUT HAVE CLEARED LOC $ObjID"));
				exit(0);
			}

			loc_clear($GLOBALS["aib_db"],$ObjID);
			ftree_delete_property($GLOBALS["aib_db"],$ObjID,"location.lat");
			ftree_delete_property($GLOBALS["aib_db"],$ObjID,"location.lon");
			ftree_delete_property($GLOBALS["aib_db"],$ObjID,"location.alt");
			$ResponseArray = array("status" => "OK", "msg" => "$ObjID");
			break;

		case "find":

			// Get minimum parameters

			$ParamCheck = check_param(array("center_x","center_y","radius"),$FormData);
			if ($ParamCheck[0] == false)
			{
				$Missing = $ParamCheck[1];
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "MISSING PARAM $Missing"));
				exit(0);
			}

			$GetItemDataFlag = get_option_value($FormData,"get_item_data");
			$GetItemPropFlag = get_option_value($FormData,"get_properties");
			$GetItemFilesFlag = get_option_value($FormData,"get_files");
			$CenterX = $FormData["center_x"];
			$CenterY = $FormData["center_y"];
			$Radius = $FormData["radius"];
			$CenterX = sprintf("%0.6lf",floatval($CenterX));
			$CenterY = sprintf("%0.6lf",floatval($CenterY));
			$Radius = sprintf("%0.6lf",floatval($Radius));
			if (isset($FormData["context"]) == true)
			{
				$Context = $FormData["context"];
			}
			else
			{
				$Context = "DEFAULT";
			}

			// If there are extra filters, get those too.

			if (isset($FormData["tag_match_filter"]) == true)
			{
				$TagString = $FormData["tag_match_filter"];
				if (ltrim(rtrim($TagString)) != "")
				{
					$TagMatchList = explode(",",$TagString);
				}
				else
				{
					$TagMatchList = false;
				}
			}
			else
			{
				$TagMatchList = false;
			}

			if (isset($FormData["tag_reject_filter"]) == true)
			{
				$TagString = $FormData["tag_reject_filter"];
				if (ltrim(rtrim($TagString)) != "")
				{
					$TagRejectList = explode(",",$TagString);
				}
				else
				{
					$TagRejectList = false;
				}
			}
			else
			{
				$TagRejectList = false;
			}

			$PropertyFilters = array();
			$PropertyRejectFilters = array();
			foreach($FormData as $Name => $Value)
			{
				if (preg_match("/^pmatch[\_]/",$Name) == true)
				{
					// Separate the property name from the prefix

					$Segs = explode("_",$Name);
					$PropertyName = $Segs[1];

					// Parse the comparison value

					$Segs = explode(",",$Value);
					if (count($Segs) < 2)
					{
						continue;
					}

					// Get type of comparison

					$CompareCode = array_shift($Segs);

					// Get comparison values

					if (count($Segs) > 1)
					{
						$LowValue = array_shift($Segs);
						$HighValue = array_shift($Segs);
						$PropertyFilters[$PropertyName] = array("op" => strtoupper($CompareCode), "low_value" => $LowValue, "high_value" => $HighValue);
					}
					else
					{
						$CompareValue = join(",",$Segs);
						$PropertyFilters[$PropertyName] = array("op" => strtoupper($CompareCode), "low_value" => $CompareValue, "high_value" => false);
					}
				}

				if (preg_match("/^preject[\_]/",$Name) == true)
				{
					$Segs = explode("_",$Name);
					$PropertyName = $Segs[1];
					$Segs = explode(",",$Value);
					if (count($Segs) < 2)
					{
						continue;
					}

					$CompareCode = array_shift($Segs);
					if (count($Segs) > 1)
					{
						$LowValue = array_shift($Segs);
						$HighValue = array_shift($Segs);
						$PropertyRejectFilters[$Name] = array("op" => strtoupper($CompareCode), "low_value" => $LowValue, "high_value" => $HighValue);
					}
					else
					{
						$CompareValue = join(",",$Segs);
						$PropertyRejectFilters[$Name] = array("op" => strtoupper($CompareCode), "low_value" => $Compare);
					}
				}
			}

			// Calculate miles per degree of latitude, then calculate the number of degrees of latitude for the radius

			$MilesPerLatitudeDegree = loc_lat_distance($CenterY);
			$LatitudeRadiusDegrees = $Radius / $MilesPerLatitudeDegree;
			$TopLatitude = $CenterY + $LatitudeRadiusDegrees;
			$BottomLatitude = $CenterY - $LatitudeRadiusDegrees;

			// Calculate the miles per degree of longitude at the top of the square, then for the bottom

			$TopMilesPerLon = loc_calc_longitude_distance($TopLatitude);
			$BottomMilesPerLon = loc_calc_longitude_distance($BottomLatitude);

			// Calculate degrees of distance for the radius at the top and bottom

			$TopDegrees = $Radius / $TopMilesPerLon;
			$BottomDegrees = $Radius / $BottomMilesPerLon;

			// Calculate final coordinates for the bounding box

			$TopCoord = $TopLatitude;
			$BotCoord = $BottomLatitude;
			$LeftTopCoord = $CenterX - $TopDegrees;
			$RightTopCoord = $CenterX + $TopDegrees;
			$LeftBotCoord = $CenterX - $BottomDegrees;
			$RightBotCoord = $CenterX + $BottomDegrees;

			// Since we have to perform a search on a square rather than a rhombus (due to SQL restrictions), determine the largest
			// square.

			if ($TopMilesPerLon > $BottomMilesPerLon)
			{
				$TopCoord = $TopLatitude;
				$BotCoord = $BottomLatitude;
				$LeftTopCoord = $CenterX - $TopDegrees;
				$RightTopCoord = $CenterX + $TopDegrees;
				$LeftBotCoord = $CenterX - $TopDegrees;
				$RightBotCoord = $CenterX + $TopDegrees;
			}
			else
			{
				$TopCoord = $TopLatitude;
				$BotCoord = $BottomLatitude;
				$LeftTopCoord = $CenterX - $BottomDegrees;
				$RightTopCoord = $CenterX + $BottomDegrees;
				$LeftBotCoord = $CenterX - $BottomDegrees;
				$RightBotCoord = $CenterX + $BottomDegrees;
			}


			// Perform search

			$Coordinates = array("x1" => $LeftTopCoord, "y1" => $TopCoord, "x2" => $RightBotCoord, "y2" => $BotCoord);

			$ResultSet = loc_find($GLOBALS["aib_db"],$Context,$Coordinates,false,false);
			if ($ResultSet == false)
			{
				aib_close_db();
				aib_api_send_response(array("status" => "ERROR", "msg" => "NOT FOUND"));
				exit(0);
			}
			else
			{
				$SearchQuery = $ResultSet["query"];
				$ResultSet = $ResultSet["records"];
			}

			// Convert response to a map and unload the original result set list

			$ResponseArray = array();
			$MapArray = array();
			foreach($ResultSet as $ResultRecord)
			{
				$MapArray[$ResultRecord["item_id"]] = $ResultRecord;
			}

			unset($ResultSet);

			// Process tag matches if needed.  Get the list of tags to be matched and those
			// to be rejected (items with matching tags are rejected). Create a map of the
			// tags so we can do fast lookups.

			$TagMatchMap = array();
			if ($TagMatchList != false)
			{
				foreach($TagMatchList as $TagValue)
				{
					$TagMatchMap[strtoupper($TagValue)] = true;
				}

				unset($TagMatchList);
			}

			$TagMatchSetSize = count(array_keys($TagMatchMap));
			$TagRejectMap = array();
			if ($TagRejectList != false)
			{
				foreach($TagRejectList as $TagValue)
				{
					$TagRejectMap[strtoupper($TagValue)] = true;
				}

				unset($TagRejectList);
			}

			$TagRejectSetSize = count(array_keys($TagRejectMap));

			// If there are any tag filters, do them here

			if ($TagMatchSetSize > 0 || $TagRejectSetSize > 0)
			{
				$OutMapArray = array();

				// Process each item found

				foreach($MapArray as $ItemID => $ItemInfo)
				{
					// Create a map of the item tags

					$LocalMap = array();
					$TagSet = aib_get_item_tags($GLOBALS["aib_db"],$ItemID);

					// If there are no tags, check to see if there is a match set.  If there is, then the
					// item can't be used, as it has no tags (and therefore none that match the filter set).
					// By definition, the item will meet any tag reject requirements.

					if ($TagSet == false)
					{
						if ($TagMatchSetSize > 0)
						{
							continue;
						}
					}

					// Create an associative array for fast lookups on tag comparisons

					foreach($TagSet as $LocalTagValue)
					{
						$LocalMap[$LocalTagValue] = true;
					}

					unset($TagSet);

					// If we need to include only matching items, do here.  Compare the match list
					// against the list of item tags.  If there are any matches, include the item.
					// Otherwise, next item.

					if ($TagMatchSetSize > 0)
					{
						$CompareArray = array_intersect_key($TagMatchMap,$LocalMap);
						if (count(array_keys($CompareArray)) > 0)
						{
							$OutMapArray[$ItemID] = $ItemInfo;
						}
						else
						{
							continue;
						}
					}

					// If we need to reject items, do that here.  Compare the reject list against
					// the list of item tags.  If there are any matches, reject the item.  If not,
					// add the item to the output if not already present.

					if ($TagRejectSetSize > 0)
					{
						$CompareArray = array_intersect_key($TagRejectMap,$LocalMap);
						if (count(array_keys($CompareArray)) > 0)
						{
							if (isset($OutMapArray[$ItemID]) == true)
							{
								unset($OutMapArray[$ItemID]);
							}
						}
						else
						{
							if (isset($OutMapArray[$ItemID]) == false)
							{
								$OutMapArray[$ItemID] = $ItemInfo;
							}
						}
					}
				}

				unset($MapArray);
				$MapArray = $OutMapArray;
			}

			// Process property reject and match filters if needed

			$PropMatchCount = count($PropertyFilters);
			$PropNoMatchCount = count($PropertyRejectFilters);
			$PropCache = array();
			if ($PropMatchCount > 0)
			{
				// Create output array and process each source item

				$OutMapArray = array();
				foreach($MapArray as $ItemID => $ItemInfo)
				{
					// Load properties into cache

					if (isset($PropCache[$ItemID]) == false)
					{
						$PropCache[$ItemID] = ftree_list_properties($GLOBALS["aib_db"],$ItemID,true);
						if ($PropCache[$ItemID] == false)
						{
							$PropCache[$ItemID] = array();
						}
					}

					// Property compare loop

					$MatchFlag = false;
					foreach($PropCache[$ItemID] as $PropertyName => $PropertyValue)
					{
						// If in the filters, test

						if (isset($PropertyFilters[$PropertyName]) == true)
						{
							// Get test spec

							$Spec = $PropertyFilters[$PropertyName];

							// If the property doesn't match, don't include this item.  Try the next property.

							if (test_property_value($PropertyValue,$Spec["op"],$Spec["low_value"],$Spec["high_value"]) == false)
							{
								continue;
							}

							// Property matches.  Add item to output and exit compare loop.

							$MatchFlag = true;
							break;
						}
					}

					if ($MatchFlag == true)
					{
						$OutMapArray[$ItemID] = $ItemInfo;
					}
				}

				// Remove old item set, reassign

				unset($MapArray);
				$MapArray = $OutMapArray;
			}

			// Process matches that aren't allowed

			if ($PropNoMatchCount > 0)
			{
				$OutMapArray = array();
				foreach($MapArray as $ItemID => $ItemInfo)
				{
					// Load properties into cache

					if (isset($PropCache[$ItemID]) == false)
					{
						$PropCache[$ItemID] = ftree_list_properties($GLOBALS["aib_db"],$ItemID,true);
						if ($PropCache[$ItemID] == false)
						{
							$PropCache[$ItemID] = array();
						}
					}

					// Property compare loop

					$RejectFlag = false;
					foreach($PropCache[$ItemID] as $PropertyName => $PropertyValue)
					{
						// If in the filters, test

						if (isset($PropertyRejectFilters[$PropertyName]) == true)
						{
							// Get test spec

							$Spec = $PropertyRejectFilters[$PropertyName];

							// If the property matches, then reject item

							if (test_property_value($PropertyValue,$Spec["op"],$Spec["low_value"],$Spec["high_value"]) == true)
							{
								$RejectFlag = true;
								break;
							}
						}
					}

					if ($RejectFlag == false)
					{
						$OutMapArray[$ItemID] = $ItemInfo;
					}
				}

				unset($MapArray);
				$MapArray = $OutMapArray;
			}

			$ItemSet = array();
			if ($GetItemDataFlag == true)
			{
				$Query = "SELECT * FROM ftree WHERE ";
				$IDList = array_keys($MapArray);
				$ORFlag = false;
				foreach($IDList as $LocalID)
				{
					if ($ORFlag == true)
					{
						$Query .= " OR ";
					}

					$Query .= " item_id='$LocalID'";
					$ORFlag = true;
				}

				$Query .= ";";
				$ResultSet = loc_query($GLOBALS["aib_db"],$Query);
				if ($ResultSet != false)
				{
					foreach($ResultSet as $ResultRecord)
					{
						$ItemSet[$ResultRecord["item_id"]] = $ResultRecord;
					}

					unset($ResultSet);
				}
			}

			$OutList = array();
			foreach($MapArray as $ItemID => $ItemEntry)
			{
				if ($GetItemDataFlag == false)
				{
					$OutList[] = $ItemEntry;
				}
				else
				{
					if (isset($ItemSet[$ItemID]) != false)
					{
						$ItemRecord = $ItemSet[$ItemID];
						$ItemRecord["location"] = $ItemEntry;
						if ($GetItemPropFlag == true)
						{
							$PropertySet = ftree_list_properties($GLOBALS["aib_db"],$ItemID,true);
							if ($PropertySet != false)
							{
								$ItemRecord["properties"] = $PropertySet;
							}
							else
							{
								$ItemRecord["properties"] = array();
							}
						}

						if ($GetItemFilesFlag == true)
						{
							$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID);
							if ($FileList != false)
							{
								$ItemRecord["files"] = $FileList;
							}
							else
							{
								$ItemRecord["files"] = array();
							}
						}

						$OutList[] = $ItemRecord;
					}
				}
			}

			unset($MapArray);
			$ResponseArray = array("status" => "OK", "msg" => sprintf("%d",count($OutList)),"records" => $OutList);
			break;

		default:
			aib_close_db();
			aib_api_send_response(array("status" => "ERROR", "msg" => "BAD OPCODE"));
			exit(0);
	}


	aib_close_db();
	aib_api_send_response($ResponseArray);
	exit(0);

?>
