<?php
//
// User functions
//

include("api_util.php");

// Log a debug message
// -------------------
function req_service_log_debug($Msg)
{
	$Handle = fopen("/tmp/user_service_debug.txt","a+");
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
		// Store user request
		
		case "req_store":
			// Check for required fields

			$ReqType = get_assoc_default($FormData,"req_type",false);
			$ReqName = get_assoc_default($FormData,"req_name","");
			$ReqPhone = get_assoc_default($FormData,"req_phone","");
			$ReqEmail = get_assoc_default($FormData,"req_email","");
			$ReqIPAddr = get_assoc_default($FormData,"req_ipaddr","");
			$ReqInfo = get_assoc_default($FormData,"req_info","");
			$ReqOwnerItem = get_assoc_default($FormData,"req_item",-1);
			$ReqOwnerUserID = get_assoc_default($FormData,"req_user",-1);
			$ReqStatus = get_assoc_default($FormData,"req_status","NEW");
			if ($ReqType == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTYPE";
				break;
			}

			$Result = aib_store_cust_request($ReqType,$ReqName,$ReqPhone,$ReqEmail,$ReqIPAddr,$ReqInfo,$ReqOwnerItem,$ReqOwnerUserID,$ReqStatus);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTSTORE";
				break;
			}

			$OutData["info"] = $Result;
			break;

		// Modify user request
		
		case "req_mod":
			$ReqID = get_assoc_default($FormData,"req_id",false);
			if ($ReqID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$Result = aib_get_cust_request($ReqID);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADID";
				break;
			}

			$FieldList = array("req_type","req_name","req_phone","req_email","req_ipaddr","req_info","req_item","req_user","req_status");
			$ModInfo = array();
			foreach($FieldList as $FieldName)
			{
				if (isset($FormData[$FieldName]) == true)
				{
					$ModInfo[$FieldName] = $FormData[$FieldName];
				}
			}

			$Result = aib_update_cust_request($ReqID,$ModInfo);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTUPDATE";
				break;
			}

			$OutData["info"] = "";
			break;

		// Get
		case "req_get":
			$ReqID = get_assoc_default($FormData,"req_id",false);
			if ($ReqID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			$Result = aib_get_cust_request($ReqID);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADID";
				break;
			}

			$OutData["records"] = array();
			$OutData["records"][] = array(
				"req_id" => $Result["record_id"],
				"req_type" => $Result["req_type"],
				"req_name" => urldecode($Result["name"]),
				"req_phone" => urldecode($Result["phone"]),
				"req_email" => urldecode($Result["email"]),
				"req_ipaddr" => urldecode($Result["ip_addr"]),
				"req_time" => $Result["req_time"],
				"req_info" => urldecode($Result["info"]),
				"req_user" => $Result["owner_user"],
				"req_item" => $Result["owner_item"],
				"req_status" => $Result["req_status"]);
			break;

		// Delete
		case "req_del":
			$ReqID = get_assoc_default($FormData,"req_id",false);
			if ($ReqID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGID";
				break;
			}

			aib_del_cust_request($ReqID);
			break;

		// List
		case "req_list":
			$ReqType = get_assoc_default($FormData,"req_type",false);
			$ReqName = get_assoc_default($FormData,"req_name",false);
			$ReqPhone = get_assoc_default($FormData,"req_phone",false);
			$ReqEmail = get_assoc_default($FormData,"req_email",false);
			$ReqIPAddr = get_assoc_default($FormData,"req_ipaddr",false);
			$ReqOwnerItem = get_assoc_default($FormData,"req_item",false);
			$ReqOwnerUserID = get_assoc_default($FormData,"req_user",false);
			$ReqStartTime = get_assoc_default($FormData,"start_time",false);
			$ReqEndTime = get_assoc_default($FormData,"end_time",false);
			$ReqSort = get_assoc_default($FormData,"sort_order",false);
			$ReqStart = get_assoc_default($FormData,"first",false);
			$ReqCount = get_assoc_default($FormData,"rows",false);
			$ReqStatus = get_assoc_default($FormData,"status",false);
			$ResultSet = aib_list_cust_request($ReqType,$ReqName,$ReqPhone,$ReqEmail,$ReqIPAddr,$ReqOwnerItem,$ReqOwnerUserID,$ReqStartTime,
				$ReqEndTime,$ReqSort,$ReqStart,$ReqCount,$ReqStatus);
			if ($ResultSet == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADREQUEST";
				break;
			}

			$OutList = array();
			foreach($ResultSet as $Record)
			{
				$OutList[] = array(
					"req_id" => $Record["record_id"],
					"req_type" => $Record["req_type"],
					"req_name" => urldecode($Record["name"]),
					"req_phone" => urldecode($Record["phone"]),
					"req_email" => urldecode($Record["email"]),
					"req_ipaddr" => urldecode($Record["ip_addr"]),
					"req_time" => $Record["req_time"],
					"req_info" => urldecode($Record["info"]),
					"req_user" => $Record["owner_user"],
					"req_item" => $Record["owner_item"],
					"req_status" => $Record["req_status"]);
			}

			$OutData["records"] = $OutList;
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
