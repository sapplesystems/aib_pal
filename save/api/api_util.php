<?php
include("../config/aib.php");
include("../include/folder_tree.php");
include("../include/fields.php");
include("../include/aib_util.php");
include("../include/apikey.php");

// Output result as JSON content
// -----------------------------
function aib_api_send_response($OutInfo)
{
	$OutText = json_encode($OutInfo);
	print($OutText);
}


// ==============
// SESSION TABLES
// ==============

function aib_new_persistent_session()
{
	$SessionCreated = microtime(true);
	$SessionLastActivity = $SessionCreated;
	$Result = mysqli_query($GLOBALS["aib_db"],"INSERT INTO sessions (session_created,session_last_activity) VALUES ($SessionCreated,$SessionLastActivity);");
	if ($Result == false)
	{
		return(false);
	}

	$SessionID = mysqli_insert_id($GLOBALS["aib_db"]);
	return($SessionID);
}

function aib_delete_persistent_session($SessionID)
{
	mysqli_query($GLOBALS["aib_db"],"DELETE FROM sessions WHERE session_id=$SessionID;");
	mysqli_query($GLOBALS["aib_db"],"DELETE FROM session_property WHERE session_id=$SessionID;");
	return(true);
}

function aib_clean_sessions($MaxAge)
{
	$CurrentTime = microtime(true);
	$OldestTime = $CurrentTime - $MaxAge;
	$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM sessions WHERE session_created < $OldestTime;");
	$List = array();
	if ($Result != false)
	{
		if (mysqli_num_rows($Result) > 0)
		{
			while(true)
			{
				$Row = mysqli_fetch_assoc($Result);
				if ($Row == false)
				{
					break;
				}

				$List[] = $Row;
			}
		}

		mysqli_free_result($Result);
	}

	foreach($List as $Row)
	{
		aib_delete_persistent_session($Row["session_id"]);
	}

	return(true);
}

function aib_list_persistent_sessions($MaxAge = false)
{
	if ($MaxAge === false)
	{
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM sessions;");
	}
	else
	{
		$CurrentTime = microtime(true);
		$OldestTime = $CurrentTime - $MaxAge;
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM sessions WHERE session_created >= $OldestTime;");
	}

	if ($Result == false)
	{
		return(false);
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


function aib_get_persistent_session($SessionID,$IncludeProperties = false)
{
	$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM sessions WHERE session_id=$SessionID");
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	$OutArray = array("session" => $Row, "properties" => array());
	if ($IncludeProperties != false)
	{
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM session_property WHERE session_id=$SessionID;");
		if ($Result != false)
		{
			while(true)
			{
				$Row = mysqli_fetch_assoc($Result);
				if ($Row == false)
				{
					break;
				}

				$OutArray["properties"][$Row["property_name"]] = $Row;
			}

			mysqli_free_result($Result);
		}
	}

	return($OutArray);
}

function aib_set_session_property($SessionID,$PropertyName,$PropertyValue)
{
	$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM session_property WHERE session_id=$SessionID AND property_name='$PropertyName';");
	$ReplaceFlag = false;
	if ($Result != false)
	{
		if (mysqli_num_rows($Result) > 0)
		{
			$ReplaceFlag = true;
		}

		mysqli_free_result($Result);
	}

	if ($ReplaceFlag == false)
	{
		mysqli_query($GLOBALS["aib_db"],"INSERT INTO session_property (session_id,property_name,property_value) VALUES ($SessionID,'$PropertyName','$PropertyValue');");
	}
	else
	{
		mysqli_query($GLOBALS["aib_db"],"UPDATE session_property SET property_value='$PropertyValue' WHERE session_id=$SessionID AND property_name='$PropertyName';");
	}

	return(true);
}

function aib_get_session_property($SessionID,$PropertyName)
{
	$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM session_property WHERE session_id=$SessionID AND property_name='$PropertyName';");
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	return($Row["property_value"]);
}

function aib_list_session_properties($SessionID)
{
	$Result = mysqli_query($GLOBALS["aib_db"],"SELECT * FROM session_property WHERE session_id=$SessionID;");
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[$Row["property_name"]] = $Row["property_value"];
	}

	mysqli_free_result($Result);
	return($OutList);
}

function aib_delete_session_property($SessionID,$PropertyName)
{
	$Result = mysqli_query($GLOBALS["aib_db"],"DELETE FROM session_property WHERE session_id=$SessionID AND property_name='$PropertyName';");
	return(true);
}
?>

