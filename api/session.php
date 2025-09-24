<?php
//
// Session management for API calls.  Programs must first validate
// with an ID and API key.  The session is then returned.
//

include("api_util.php");

	// Collect form data

	$FormData = array();
	foreach($_GET as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	if (isset($FormData["_op"]) == true)
	{
		if ($FormData["_op"] == "gensession")
		{
			if (isset($FormData["_key"]) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "MISSINGKEY");
				aib_send_response($OutData);
				exit(0);
			}

			$Key = $FormData["_key"];
			aib_open_db();
			$SessionValue = aib_api_generate_session_key($GLOBALS["aib_db"],$Key);
			aib_close_db();
			$OutData = array("status" => "OK", "info" => $SessionValue);
			aib_send_response($OutData);
			exit(0);
		}
	}

	// Get the ID and API key

	if (isset($FormData["_id"]) == false)
	{
		$OutData = array("status" => "ERROR", "info" => "MISSINGID");
		aib_api_send_response($OutData);
		exit(0);
	}

	if (isset($FormData["_key"]) == false)
	{
		$OutData = array("status" => "ERROR", "info" => "MISSINGKEY");
		aib_api_send_response($OutData);
		exit(0);
	}

	$ID = $FormData["_id"];
	$Key = $FormData["_key"];
	aib_open_db();
	$Flag = aib_api_validate_key($GLOBALS["aib_db"],$Key,$ID);
	if ($Flag == false)
	{
		aib_close_db();
		$OutData = array("status" => "ERROR", "info" => "BADIDORKEY");
		aib_api_send_response($OutData);
		exit(0);
	}

	$SessionValue = aib_api_generate_session_key($GLOBALS["aib_db"],$Key);
	aib_close_db();
	$OutData = array("status" => "OK", "info" => $SessionValue);
	aib_api_send_response($OutData);
	exit(0);


