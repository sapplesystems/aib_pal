<?php
//
// API key handler
//


// Generate a new API key.
// -----------------------
function aib_api_generate_key($DBHandle,$KeyHolderID)
{
	$DateString = date("YmdHis").sprintf("%0.16lf",microtime(true));
	$DateString = md5($DateString);
	$LocalID = urlencode($KeyHolderID);
	mysqli_query($DBHandle,"DELETE FROM apikeys WHERE keyholder_id='$LocalID'");
	$Query = "INSERT INTO apikeys (keyholder_id,api_key) VALUES ('$KeyHolderID','$DateString');";
	mysqli_query($DBHandle,$Query);
	return($DateString);
}

// Validate a key
// --------------
function aib_api_validate_key($DBHandle,$Key,$KeyHolderID)
{
	$LocalID = urlencode($KeyHolderID);
	$LocalKey = preg_replace("/[^A-Za-z0-9]/","",$Key);
	$Query = "SELECT * FROM apikeys WHERE keyholder_id='$LocalID' AND api_key='$LocalKey';";
	$Result = mysqli_query($DBHandle,$Query);
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

	return(true);
}

// Given a key holder ID, return the API key
// -----------------------------------------
function aib_api_get_key($DBHandle,$KeyHolderID)
{
	$LocalID = urlencode($KeyHolderID);
	$Query = "SELECT * FROM apikeys WHERE keyholder_id='$LocalID';";
	$Result = mysqli_query($DBHandle,$Query);
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

	return($Row["api_key"]);
}

// Delete an API key
// -----------------
function aib_api_delete_key($DBHandle,$Key,$KeyHolderID)
{
	$LocalID = urlencode($KeyHolderID);
	$LocalKey = preg_replace("/[^A-Za-z0-9]/","",$Key);
	$Query = "DELETE FROM apikeys WHERE keyholder_id='$LocalID' AND api_key='$LocalKey';";
	$Result = mysqli_query($DBHandle,$Query);
	return(true);
}

// ============
// SESSION KEYS
// ============

// Session key is generated as a product of the current time and the
// application key
// -----------------------------------------------------------------
function aib_api_generate_session_key($DBHandle,$Key)
{
	$LocalKey = preg_replace("/[^A-Za-z0-9]/","",$Key);

	// Get current time

	$LocalTime = microtime(true);
	$TimeString = sprintf("%016.6lf",$LocalTime);

	// Hash the time string using an MD5.

	$SessionKey = md5($TimeString);

	// Save the session in the session database

	mysqli_query($DBHandle,"DELETE FROM api_sessions WHERE api_key='$LocalKey';");
	mysqli_query($DBHandle,"INSERT INTO api_sessions (api_key,session_key,session_time) VALUES ('$LocalKey','$SessionKey',$LocalTime);");
	return($SessionKey);
}

// Validate the session key.  Return value is a two-element array where
// the first element is either "OK" or "ERROR".  If "OK", the second element
// is the time the session was started.  If "ERROR", the return values are either
// "NO SESSION" or "EXPIRED".
// ------------------------------------------------------------------------------
function aib_api_validate_session_key($DBHandle,$Key,$SessionKey,$MaxTime = false)
{
	if ($MaxTime == false)
	{
		$MaxTime = AIB_MAX_API_SESSION;
	}

	$Result = mysqli_query($DBHandle,"SELECT * FROM api_sessions WHERE api_key='$Key';");
	if ($Result == false)
	{
		return(array("ERROR","SESSIONKEYQUERYERROR"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(array("ERROR","NOMATCHINGAPIKEY"));
	}

	$LocalTime = time();
	$SessionTime = $Row["session_time"];
	if ($LocalTime - $SessionTime >= $MaxTime)
	{
		return(array("ERROR","EXPIRED"));
	}

	return(array("OK",$SessionTime));
}

// Given a key, return the client ID
// ---------------------------------
function aib_api_get_key_id($DBHandle,$Key)
{
	$Query = "SELECT * FROM apikeys WHERE api_key='$Key';";
	$Result = mysqli_query($DBHandle,$Query);
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

	return(urldecode($Row["keyholder_id"]));
}

// =====
// HOSTS
// =====

// Check to see if a host is allowed to have access to the API
// -----------------------------------------------------------
function aib_api_check_host($DBHandle,$Name,$FunctionCode = false)
{
	// Check for specific host.

	$EncodedName = urlencode($Name);
	$Query = "SELECT * FROM api_hosts WHERE hostname='$EncodedName';";
	$Result = mysqli_query($DBHandle,$Query);
	$Row = false;
	if ($Result != false)
	{
		if (mysqli_num_rows($Result) > 0)
		{
			$Row = mysqli_fetch_assoc($Result);
		}

		mysqli_free_result($Result);
	}

	if ($Row == false)
	{
		$Query = "SELECT * FROM api_hosts WHERE hostname='$EncodedName';";
		$Result = mysqli_query($DBHandle,$Query);
		if ($Result == false)
		{
			return(false);
		}

		$Row = mysqli_fetch_assoc($Result);
		mysqli_free_result($Result);
	}

	// If the specific host wasn't found, see if all hosts in a domain are allowed (wildcard)

	if ($Row == false)
	{
		// Get the TLD and domain

		$Segs = explode(".",$Name);
		$TLD = array_pop($Segs);
		$Domain = array_pop($Segs);
		$CheckName = urlencode("*".$Domain.".".$TLD);
		$Query = "SELECT * FROM api_hosts WHERE hostname='$CheckName';";
		$Result = mysqli_query($DBHandle,$Query);
		if ($Result == false)
		{
			return(false);
		}

		$Row = mysqli_fetch_assoc($Result);
		mysqli_free_result($Result);
	}

	// See if "all hosts" is a valid entry

	if ($Row == false)
	{
		$Query = "SELECT * FROM api_hosts WHERE hostname='".urlencode("*")."';";
		$Result = mysqli_query($DBHandle,$Query);
		$Row = false;
		if ($Result != false)
		{
			$Row = mysqli_fetch_assoc($Result);
			mysqli_free_result($Result);
		}
	}

	// No matches...return false

	if ($Row == false)
	{
		return(false);
	}

	// Get info

	$Info = $Row["info"];

	// If there's a function code, check to see if it is permitted for this host

	if ($FunctionCode != false)
	{
		$Info = explode(",",$Info);
		foreach($Info as $Pair)
		{
			$Segs = explode("=",$Pair);
			if ($Segs[0] == 'ALL')
			{
				if ($Segs[1] =='Y')
				{
					return(true);
				}

				return(false);
			}

			if ($Segs[0] == $FunctionCode)
			{
				if ($Segs[1] == 'Y')
				{
					return(true);
				}

				return(false);
			}
		}

		return(false);
	}

	return(true);
}

// Set host operations
// -------------------
function aib_api_set_host($DBHandle,$HostName,$AllowedOps)
{
	// Convert allowed operations array to a string

	$AllowedList = array();
	foreach($AllowedOps as $OpCode => $AllowFlag)
	{
		$LocalFlag = strtoupper(substr($AllowFlag,0,1));
		if ($LocalFlag != "Y")
		{
			$LocalFlag = "N";
		}

		$AllowedList[] = $OpCode."=".$LocalFlag;
	}

	$AllowedString = join(",",$AllowedList);

	// Encode the host name

	$EncodedHost = urlencode($HostName);

	// See if the host is already there; if so, we update.  Otherwise, we add.

	$UpdateFlag = false;
	$Result = mysqli_query($DBHandle,"SELECT * FROM api_hosts WHERE hostname='$EncodedHost';");
	if ($Result != false)
	{
		if (mysqli_num_rows($Result) > 0)
		{
			$UpdateFlag = true;
		}

		mysqli_free_result($Result);
	}

	if ($UpdateFlag == true)
	{
		$Query = "UPDATE api_hosts SET info='$AllowedString';";
	}
	else
	{
		$Query = "INSERT INTO api_hosts (hostname,info) VALUES ('$EncodedHost','$AllowedString');";
	}

	mysqli_query($DBHandle,$Query);
	return(true);
}

// Delete host profile
// ----------------
function aib_api_del_host($DBHandle,$HostName)
{
	$EncodedHost = urlencode($HostName);
	mysqli_query($DBHandle,"DELETE FROM api_hosts WHERE hostname='$EncodedHost';");
	return(true);
}

// Get host profile
// ----------------
function aib_api_get_host($DBHandle,$HostName)
{
	// Encode the host name

	$EncodedHost = urlencode($HostName);

	// Get the record from the DB

	$Result = mysqli_query($DBHandle,"SELECT * FROM api_hosts WHERE hostname='$EncodedHost';");
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	// Create output structure

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	$OutInfo = array("hostname" => urldecode($Row["hostname"]));
	$InString = $Row["info"];
	$InList = explode(",",$InString);
	$OpArray = array();
	foreach($InList as $Setting)
	{
		$Segs = explode("=",$Setting);
		$OpArray[$Segs[0]] = $Segs[1];
	}

	$OutInfo["permissions"] = $OpArray;
	return($OutInfo);
}

?>
