<?php
//
// Notifier functions
//

include("api_util.php");

// Log a debug message
// -------------------
function tag_service_log_debug($Msg)
{
	$Handle = fopen("/tmp/tag_service_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,sprintf("%0.6lf",microtime(true)).": ".$Msg."\n");
		fclose($Handle);
	}
}

// Get value from associative array with default
// ---------------------------------------------
function get_assoc_default($ArrayIn,$Name,$Default)
{
	if (isset($ArrayIn[$Name]) == false)
	{
		return($Default);
	}

	return($ArrayIn[$Name]);
}

// Retrieve user with ID or login
// ------------------------------
function get_user_info($FormData,$UserID = false,$UserLogin = false)
{
	$UserID = get_assoc_default($FormData,"user_id",false);
	$UserLogin = get_assoc_default($FormData,"user_login",false);
	$UserRecord = false;
	$OutData = array("status" => "OK", "info" => "");
	if ($UserID !== false)
	{
		$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
	}
	else
	{
		if ($UserLogin !== false)
		{
			$UserRecord = ftree_get_user_by_login($GLOBALS["aib_db"],$UserLogin);
		}
		else
		{
			$OutData["status"] = "ERROR";
			$OutData["info"] = "MISSINGUSERIDORLOGIN";
			return($OutData);
		}
	}

	if ($UserRecord == false)
	{
		$OutData["status"] = "ERROR";
		$OutData["info"] = "USERIDORLOGINNOTFOUND";
	}
	else
	{
		$OutData["info"] = $UserRecord;
	}

	return($OutData);

}

// #########
// MAIN CODE
// #########


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

	// Get server name.  Must be a valid source as listed in the hosts table.

	$ServerName = get_assoc_default($_SERVER,"REMOTE_HOST",get_assoc_default($_SERVER,"REMOTE_ADDR",false));
	if ($ServerName == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOHOST"));
		exit(0);
	}
	else
	{
		// If the server name is the IP address, attempt to do a reverse lookup using the address.
		// If this fails, simply use the IP address.

		if (preg_match("/^[0-9\.]+$/",$ServerName) != false)
		{
			$HostName = gethostbyaddr($ServerName);
			if ($HostName != false && strtolower($ServerName) != strtolower($HostName))
			{
				$ServerName = $HostName;
			}
		}
	}

	// Get operation to perform

	$OpCode = get_assoc_default($FormData,"_op",false);
	if ($OpCode == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOOP"));
		exit(0);
	}

	// Check server name and opcode; make sure the source is allowed to perform this operation

	aib_open_db();
	if (aib_api_check_host($GLOBALS["aib_db"],$ServerName,$OpCode) == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "HOSTNOTALLOWED"));
		exit(0);
	}

	// Get API key and session, then validate

	$APIKey = get_assoc_default($FormData,"_key",false);
	$APISession = get_assoc_default($FormData,"_session",false);
	if ($APIKey == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGKEY"));
		exit(0);
	}

	if ($APISession == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGSESSION"));
		exit(0);
	}

	$Result = aib_api_validate_session_key($GLOBALS["aib_db"],$APIKey,$APISession,AIB_MAX_API_SESSION);
	if ($Result[0] != "OK")
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => $Result[1]));
		exit(0);
	}

	// Get keyholder

	$KeyHolderID = aib_api_get_key_id($GLOBALS["aib_db"],$APIKey);
	if ($KeyHolderID == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "KEYHOLDERIDNOTFOUND"));
		exit(0);
	}

	// Get user ID of requesting user; required for user account operations

	$RequestUserID = get_assoc_default($FormData,"_user",false);
	if ($RequestUserID === false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGUSER"));
		exit(0);
	}

	// Get the user type and information

	$RequestUserRecord = ftree_get_user($GLOBALS["aib_db"],$RequestUserID);
	if ($RequestUserRecord == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "BADREQUESTUSER"));
		exit(0);
	}

	$RequestUserType = $RequestUserRecord["user_type"];


	// Generate a new session

	$NewSession = aib_api_generate_session_key($GLOBALS["aib_db"],$KeyHolderID);
	$OutData = array("status" => "OK", "session" => $NewSession);

	switch($OpCode)
	{
		// Add notifier definition

		case "notifier_def_add":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$ParentFolderID = get_assoc_default($FormData,"parent_id",false);
			$KeywordString = get_assoc_default($FormData,"keywords",false);
			if ($UserID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			if ($ParentFolderID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENT";
				break;
			}

			if ($KeywordString == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGKEYWORDS";
				break;
			}

			$KeywordList = explode(",",$KeywordString);
			$DefCount = 0;
			foreach($KeywordList as $Keyword)
			{
				$Status = aib_add_notifier($GLOBALS["aib_db"],$UserID,$Keyword,$ParentFolderID);
				if ($Status === true)
				{
					$DefCount++;
				}
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $DefCount;
			break;


		// Delete notifier definition
		case "notifier_def_del":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$ParentFolderID = get_assoc_default($FormData,"parent_id",false);
			$KeywordString = get_assoc_default($FormData,"keywords",false);
			if ($UserID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			if ($ParentFolderID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPARENT";
				break;
			}

			if ($KeywordString == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGKEYWORDS";
				break;
			}

			$KeywordList = explode(",",$KeywordString);
			$DefCount = 0;
			foreach($KeywordList as $Keyword)
			{
				$Status = aib_delete_notifier($GLOBALS["aib_db"],$UserID,$Keyword,$ParentFolderID);
				if ($Status === true)
				{
					$DefCount++;
				}
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $DefCount;
			break;

		// Get list of notifier definitions

		case "notifier_def_list":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$ParentFolderID = get_assoc_default($FormData,"parent_id",false);
			$Keyword = get_assoc_default($FormData,"keyword",false);
			$ResultList = aib_list_notifiers($GLOBALS["aib_db"],$UserID,$Keyword,$ParentFolderID);
			$OutData["status"] = "OK";
			$OutData["info"]["records"] = $ResultList;
			break;

		// Get list of notifier queue entries

		case "notifier_queue_list":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$ResultList = aib_get_notifier_queue_entries($GLOBALS["aib_db"],$UserID);
			$OutData["status"] = "OK";
			$OutData["info"]["records"] = $ResultList;
			break;

		// Clear notifier queue entries

		case "notifier_clear_queue_list":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$BeforeTimeString = get_assoc_default($FormData,"before_time",false);
			if ($BeforeTimeString != false)
			{
				$BeforeTime = strtotime($BeforeTimeString);
			}
			else
			{
				$BeforeTime = false;
			}

			aib_clear_notifier_queue_entries($GLOBALS["aib_db"],$UserID,$BeforeTime);
			$OutData["status"] = "OK";
			$OutData["info"] = "";
			break;

		// Add a notifier queue entry

		case "notifier_add_queue_entry":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$ItemID = get_assoc_default($FormData,"item_id",false);
			$KeywordString = get_assoc_default($FormData,"keywords",false);
			$MatchID = get_assoc_default($FormData,"match_id",false);
			$MatchType = get_assoc_default($FormData,"match_type",AIB_NOTIFIER_MATCH_TYPE_GENERIC);
			$Delimiter = get_assoc_default($FormData,"delimiter",",");
			if ($UserID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGUSERID";
				break;
			}

			if ($ItemID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMID";
				break;
			}

			if ($KeywordString == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGKEYWORDS";
				break;
			}

			if ($MatchID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGMATCHID";
				break;
			}

			$ResultCount = aib_add_notifier_queue_entry($GLOBALS["aib_db"],$UserID,$ItemID,$MatchID,$KeywordString,$Delimiter,$MatchType);


		default:
			$OutData["status"] = "ERROR";
			$OutData["info"] = "BADOP";
			break;
	}

	aib_close_db();
	aib_api_send_response($OutData);
	exit(0);
?>
