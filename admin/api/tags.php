<?php
//
// Tag functions
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

// Make sure the requesting user is allowed to get the account information.  The requesting user
// must be a super admin, an admin, or the same user.
// ---------------------------------------------------------------------------------------------
function check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$UserType)
{
	// If this is a high-level function (not specific to a user), the requesting user
	// must be the root or an admin.

	if ($UserID === false)
	{
		if ($UserType != AIB_USER_TYPE_ROOT && $UserType != AIB_USER_TYPE_ADMIN)
		{
			return(false);
		}

		return(true);
	}

	if ($UserID != $RequestUserID)
	{
		if ($UserType != AIB_USER_TYPE_ROOT && $UserType != AIB_USER_TYPE_ADMIN)
		{
			return(false);
		}

		// If the user is an admin, make sure either the top folder is the same
		// as the admin user, or that the admin user actually owns the user in question.

		if ($UserType == AIB_USER_TYPE_ADMIN)
		{
			$AdminUserProfile = ftree_get_user($GLOBALS["aib_db"],$RequestUserID);
			if ($AdminUserProfile == false)
			{
				return(false);
			}

			if ($UserOwnerInfo["owner"] == "NULL")
			{
				$UserInfo = ftree_get_user($GLOBALS["aib_db"],$UserID);
				$UserTopFolder = $UserInfo["user_top_folder"];
				$RequestTopFolder = $AdminUserProfile["user_top_folder"];

				// If the admin top folder and the requesting user top folder are the same, then
				// the requesting user is ok.

				if ($UserTopFolder == $RequestTopFolder)
				{
					return(true);
				}

				// Otherwise, check to see if the user's top folder is within the tree for the
				// administrator's top folder.

				$IDPath = ftree_get_item_id_path($GLOBALS["aib_db"],$RequestTopFolder);
				if ($IDPath == false)
				{
					return(false);
				}

				$CheckFlag = false;
				foreach($IDPath as $LocalID)
				{
					// Found the admin's top folder; set flag.  This will always occur BEFORE
					// the user's top folder is found if the admin is higher in the tree.

					if ($LocalID == $RequestTopFolder)
					{
						$CheckFlag = true;
						continue;
					}

					// Found user's top folder

					if ($LocalID == $UserTopFolder)
					{
						// If the check flag is false, it means this user
						// is outside of the top folder for the admin, regardless of
						// whether the admin is in a different tree, or if the admin
						// top folder is below that of the user.

						if ($CheckFlag == false)
						{
							return(false);
						}

						break;
					}
				}

				return(true);
			}
		}
	}

	// Defaults to true; maybe this should default to false?

	return(true);
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
		// Search tags for matching items, with option for search type, start and end results

		case "tags_find":
			$SearchType = strtoupper(get_assoc_default($FormData,"search_type","EXACT"));
			$StartResult = get_assoc_default($FormData,"start_result",false);
			$ResultCount = get_assoc_default($FormData,"result_count",false);
			$TagToFind = get_assoc_default($FormData,"search_tag",false);
			if ($TagToFind == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGSEARCH";
				break;
			}

			$ResultList = aib_find_tag_items($GLOBALS["aib_db"],$TagToFind,$SearchType,$StartResult,$ResultCount);
			$OutData["info"]["records"] = array();
			if ($ResultList == false)
			{
				break;
			}

			$OutData["info"]["records"] = $ResultList;
			break;

		// Search tags for matching items, with option for search type, start and end results, with booleans.
		// The search set is a series of records, each of which contains:
		//
		// boolean: "AND", "OR" or "NOT"
		// method: "EXACT","WILD","SUFFIX" or "PREFIX"
		// value: Tag text
		//
		// Example:
		//
		//	[
		//		{boolean:"OR", method:"EXACT",value:"QUICK"},
		//		{boolean:"AND", method:"EXACT",value:"BROWN"},
		//	]

		case "tags_find_boolean":
			$StartResult = get_assoc_default($FormData,"start_result",false);
			$ResultCount = get_assoc_default($FormData,"result_count",false);
			$TagSet = get_assoc_default($FormData,"tag_spec",false);
			if ($TagSet == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGSEARCH";
				break;
			}

			$TagSpec = json_decode($TagSet,true);
			$ResultList = aib_find_tag_items_boolean($GLOBALS["aib_db"],$TagSpec,$StartResult,$ResultCount);
			$OutData["info"]["records"] = array();
			if ($ResultList == false)
			{
				break;
			}

			$OutData["info"]["records"] = $ResultList;
			break;


		// Add tags to an item

		case "tags_add":
			$ObjectID = get_assoc_default($FormData,"obj_id",false);
			$TagString = get_assoc_default($FormData,"tags",false);
			$OptReplace = get_assoc_default($FormData,"opt_replace","N");
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			if ($TagString == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTAGS";
				break;
			}

			if (preg_match("/[Yy]/",$OptReplace) != false)
			{
				$OptReplace = true;
			}
			else
			{
				$OptReplace = false;
			}

			if ($OptReplace == true)
			{
				aib_del_item_tags($GLOBALS["aib_db"],$ObjectID);
			}

			aib_add_item_tags($GLOBALS["aib_db"],$ObjectID,$TagString,",");
			break;


		// Remove tags from an item

		case "tags_del":
			$ObjectID = get_assoc_default($FormData,"obj_id",false);
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOID";
				break;
			}
			
			$TagString = get_assoc_default($FormData,"tags",false);
			if ($TagString != false)
			{
				$TagList = explode(",",strtoupper($TagString));
				if (count($TagList) < 1)
				{
					$TagList = false;
				}
			}
			else
			{
				$TagList = false;
			}

			aib_del_item_tags($GLOBALS["aib_db"],$ObjectID,$TagList);
			break;

		case "tags_get":
			$ObjectID = get_assoc_default($FormData,"obj_id",false);
			if ($ObjectID == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}
			
			$OutData["info"]["records"] = array();
			$Results = aib_get_item_tags($GLOBALS["aib_db"],$ObjectID);
			if ($Results != false)
			{
				$OutData["info"]["records"] = $Results;
			}

			break;

		default:
			$OutData["status"] = "ERROR";
			$OutData["info"] = "BADOP";
			break;
	}

	aib_close_db();
	aib_api_send_response($OutData);
	exit(0);
?>
