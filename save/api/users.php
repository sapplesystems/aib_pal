<?php
//
// User functions
//

include("api_util.php");

// Log a debug message
// -------------------
function user_service_log_debug($Msg)
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
		// Sessions
		// ========

		// Log in
		case "login":

			// Get login ID and password

			$UserLogin = get_assoc_default($FormData,"user_login",false);
			$UserPass = get_assoc_default($FormData,"user_pass",false);
			if ($UserLogin == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGLOGINID";
				break;
			}

			if ($UserPass == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPASS";
				break;
			}


			// Check login

			$Result = aib_check_login($FormData["user_login"],$FormData["user_pass"]);
			if ($Result[0] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADLOGINORPASS";
				break;
			}

			$OutData["info"] = array("status" => "OK", "info" => "");
			break;

		// Log out
		case "logout":
			$OutData["info"] = array("status" => "OK", "info" => "");
			break;

		// Profile maintenance
		// ===================

		// Get user profile
		case "get_profile":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);

			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				break;
			}

			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED","session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$OutData["info"] = array(
				"user_id" => $UserRecord["user_id"],
				"user_login" => $UserRecord["user_login"],
				"user_title" => $UserRecord["user_title"],
				"user_primary_group" => $UserRecord["user_primary_group"],
				"user_top_folder" => $UserRecord["user_top_folder"],
				"user_type" => $UserRecord["user_type"],
				"user_owner" => $UserOwnerInfo["owner"],
				"user_default_rights" => $UserOwnerInfo["rights"]
				);

			break;

		// Update user profile
		case "update_profile":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);

			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				$OutData["session"] = $NewSession;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED", "session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$UserID = $UserRecord["user_id"];
			$NewLogin = get_assoc_default($FormData,"new_user_login",false);
			$NewTitle = get_assoc_default($FormData,"new_user_title",false);
			$NewPassword = get_assoc_default($FormData,"new_user_password",false);
			$NewUserOwner = get_assoc_default($FormData,"new_user_owner",false);
			$NewUserRights = get_assoc_default($FormData,"new_user_rights",false);
			$NewTopFolder = get_assoc_default($FormData,"new_top_folder",false);
			$NewInfo = array();
			if ($NewLogin != false)
			{
				$NewInfo["login"] = $NewLogin;
			}

			if ($NewTitle != false)
			{
				$NewInfo["name"] = $NewTitle;
			}

			if ($NewPassword != false)
			{
				$NewInfo["password"] = $NewPassword;
			}

			if ($NewUserOwner != false)
			{
				$NewInfo["owner"] = $NewUserOwner;
			}

			if ($NewUserRights != false)
			{
				$NewInfo["rights"] = $NewUserRights;
			}

			if ($NewTopFolder != false)
			{
				$NewInfo["top_folder"] = $NewTopFolder;
			}

			$Result = ftree_update_user($GLOBALS["aib_db"],$UserID,$NewInfo);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTUPDATEUSER";
			}
			else
			{
				$OutData["status"] = "OK";
				$OutData["info"] = $UserID;
			}

			break;

		// Delete user profile
		case "delete_profile":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);

			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				$OutData["session"] = $NewSession;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED");
				break;
			}

			$UserRecord = $UserInfo["info"];
			ftree_delete_user($GLOBALS["aib_db"],$UserRecord["user_id"]);
			$OutData["status"] = "OK";
			$OutData["info"] = $UserRecord["user_id"];
			break;

		// List profiles for users, optionally filtering by type or top folder
		case "list_profiles":
			// The requesting user has to be root or an admin

			if ($RequestUserType != AIB_USER_TYPE_ROOT && $RequestUserType != AIB_USER_TYPE_ADMIN)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED", "session" => $NewSession);
				break;
			}

			$UserTypeFilter = get_assoc_default($FormData,"user_type",false);
			$UserParentFilter = get_assoc_default($FormData,"user_top_folder",false);
			$UserTitleFilter = get_assoc_default($FormData,"user_title",false);
			$StartResult = get_assoc_default($FormData,"_start",false);
			$EndResult = get_assoc_default($FormData,"_end",false);
			$IncludeProperties = strtoupper(get_assoc_default($FormData,"_prop","N"));
			if ($UserParentFilter == false)
			{
				$ResultList = ftree_list_users($GLOBALS["aib_db"],$UserTypeFilter,$UserTitleFilter,$StartResult,$EndResult);
			}
			else
			{
				$ResultList = ftree_list_users_for_parent($GLOBALS["aib_db"],$UserParentFilter,$UserTypeFilter,$UserTitleFilter,$StartResult,$EndResult);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array("count" => count($ResultList));
			$OutData["info"]["records"] = array();
			foreach($ResultList as $Record)
			{
				if ($IncludeProperties == "Y")
				{
					$Properties = ftree_list_user_prop($GLOBALS["aib_db"],$Record["user_id"]);
					if ($Properties == false)
					{
						$Properties = array();
					}
				}
				else
				{
					$Properties = array();
				}

				$OutData["info"]["records"][] = array(
					"user_id" => $Record["user_id"],
					"user_login" => $Record["user_login"],
					"user_type" => $Record["user_type"],
					"user_title" => urldecode($Record["user_title"]),
					"user_top_folder" => $Record["user_top_folder"],
					"user_primary_group" => $Record["user_primary_group"],
					"_properties" => $Properties,
					);
			}

			break;

		// Create a profile

		case "create_profile":
			// If missing required fields, error

			$RequiredList = array("user_login" => "MISSINGUSERLOGIN","user_pass" => "MISSINGUSERPASS","user_title" => "MISSINGUSERTITLE");
			foreach($RequiredList as $RequiredName => $ErrorMsg)
			{
				if (isset($FormData[$RequiredName]) == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = $ErrorMsg;
					break;
				}
			}

			$UserLogin = get_assoc_default($FormData,"user_login",false);
			$UserType = get_assoc_default($FormData,"user_type",AIB_USER_TYPE_USER);
			$UserPass = get_assoc_default($FormData,"user_pass",false);
			$UserTitle = get_assoc_default($FormData,"user_title",false);
			$UserPrimaryGroup = get_assoc_default($FormData,"user_primary_group","-1");
			$UserTopFolder = get_assoc_default($FormData,"user_top_folder","-1");
			$OptionCreate = strtoupper(get_assoc_default($FormData,"opt_create_home","N"));
			$OptionHomeType = get_assoc_default($FormData,"opt_home_type",AIB_ITEM_TYPE_ARCHIVE);
			if ($OptionCreate == "Y")
			{
				$OptCreateFlag = true;
			}
			else
			{
				$OptCreateFlag = false;
			}

			if ($OptCreateFlag == false && $UserTopFolder <= 0)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADTOPFOLDER";
				break;
			}

			// Restrict account type creation based on the requesting user

			$UserTypeViolation = false;
			switch($RequestUserType)
			{
				// Root (super user) can create anything except another root

				case AIB_USER_TYPE_ROOT:
					if ($UserType == AIB_USER_TYPE_ROOT)
					{
						$UserTypeViolation = true;
					}

					break;

				// Admin can create sub-admin, account user, or public user

				case AIB_USER_TYPE_ADMIN:
					switch($UserType)
					{
						case AIB_USER_TYPE_SUBADMIN:
						case AIB_USER_TYPE_USER:
						case AIB_USER_TYPE_PUBLIC:
							break;

						default:
							$UserTypeViolation = true;
							break;
					}

					break;

				// Sub-admin can create account user or public user

				case AIB_USER_TYPE_SUBADMIN:
					switch($UserType)
					{
						case AIB_USER_TYPE_USER:
						case AIB_USER_TYPE_PUBLIC:
							break;

						default:
							$UserTypeViolation = true;
							break;
					}

					break;

				// All other user types are not allowed to create a user

				default:
					$UserTypeViolation = true;
					break;
			}

			// If the requesting user isn't allowed to create the account type, error

			if ($UserTypeViolation == true)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOTALLOWED";
				break;
			}

			// If the home create option is used, create home folder based on user type

			if ($OptCreateFlag == true)
			{
				switch($UserType)
				{
					case AIB_USER_TYPE_USER:

						// Make sure user isn't already defined

						if (ftree_get_user_id_from_login($GLOBALS["aib_db"],$UserLogin) != false)
						{
							$OutData["status"] = "ERROR";
							$OutData["status"] = "DUPLICATELOGIN";
							break;
						}
						
						// Create user under "standard users" where the login is the user's folder name

						$PathSpec = join("\t",array(
							"F:".AIB_PREDEF_FOLDER_NAME_STANDARD_USERS_ROOT,
							"F:".$UserTitle,
							));
						$NewInfo = ftree_create_object_by_path($GLOBALS["aib_db"],-1,-1,-1,$PathSpec,"\t");
						if ($NewInfo[0] != "OK")
						{
							$OutData["status"] = "ERROR";
							$OutData["info"] = $NewInfo[1];
							break;
						}

						$UserTopFolder = $NewInfo[1];
						ftree_set_property($GLOBALS["aib_db"],$UserTopFolder,AIB_FOLDER_PROPERTY_FOLDER_TYPE,$OptionHomeType,true);
						break;

					default:
						break;
				}

			}

			if ($OutData["status"] == "ERROR")
			{
				break;
			}

			// Attempt to create profile

			$Result = ftree_create_user($GLOBALS["aib_db"],-1,$UserType,$UserLogin,$UserPass,$UserTitle,$UserPrimaryGroup,$UserTopFolder);
			if ($Result[0] != "OK")
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTCREATE: ".$Result[1];
				break;
			}

			if ($OptCreateFlag == true)
			{
				ftree_modify($GLOBALS["aib_db"],$UserTopFolder,array("item_user_id" => $Result[1]),false);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = $Result[1];
			break;


		// User Profile Properties
		// =======================

		// Set property
		case "set_profile_prop":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);
			
			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				$OutData["session"] = $NewSession;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED","session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$UserID = $UserRecord["user_id"];
			$PropertyName = get_assoc_default($FormData,"property_name",false);
			$PropertyValue = get_assoc_default($FormData,"property_value",false);
			if ($PropertyName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNAME";
				break;
			}

			if ($PropertyValue == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGVALUE";
				break;
			}

			$PropertyName = urlencode($PropertyName);
			$PropertyValue = urlencode($PropertyValue);
			ftree_set_user_prop($GLOBALS["aib_db"],$UserID,$PropertyName,$PropertyValue);
			$OutData["status"] = "OK";
			$OutData["info"] = urldecode($PropertyName);
			break;

		// Get property
		case "get_profile_prop":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);
			
			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				$OutData["session"] = $NewSession;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED","session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$UserID = $UserRecord["user_id"];
			$PropertyName = get_assoc_default($FormData,"property_name",false);
			if ($PropertyName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNAME";
				break;
			}

			$PropertyName = urlencode($PropertyName);
			$Result = ftree_get_user_prop($GLOBALS["aib_db"],$UserID,$PropertyName);
			if ($Result == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "PROPNOTFOUND";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array(
				"property_value" => urldecode($Result),
				"property_name" => urldecode($PropertyName)
				);

			break;

		// Delete property
		case "del_profile_prop":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);
			
			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				$OutData["session"] = $NewSession;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED","session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$UserID = $UserRecord["user_id"];
			$PropertyName = get_assoc_default($FormData,"property_name",false);
			if ($PropertyName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGNAME";
				break;
			}

			$PropertyName = urlencode($PropertyName);
			$Result = ftree_delete_user_prop($GLOBALS["aib_db"],$UserID,$PropertyName);
			$OutData["status"] = "OK";
			$OutData["info"] = urldecode($PropertyName);
			break;

		// Get a list of properties for a profile
		case "list_profile_prop":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);
			
			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED","session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$UserID = $UserRecord["user_id"];
			$ResultList = ftree_list_user_prop($GLOBALS["aib_db"],$UserID);
			if ($ResultList == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "CANNOTGETPROPLIST";
				break;
			}

			$OutData["status"] = "OK";
			$OutData["info"] = array("count" => count($ResultList), "records" => array());
			foreach($ResultList as $PropertyName => $PropertyValue)
			{
				$OutData["info"]["records"][] = array(
					"property_name" => urldecode($PropertyName),
					"property_value" => urldecode($PropertyValue)
					);
			}

			break;

		// Set profile properties in a batch (multiple in one call)
		case "set_profile_prop_batch":
			$UserID = get_assoc_default($FormData,"user_id",false);
			$UserLogin = get_assoc_default($FormData,"user_login",false);
			
			// Get user info

			$UserInfo = get_user_info($FormData,$UserID,$UserLogin);
			if ($UserInfo["status"] == "ERROR")
			{
				$OutData = $UserInfo;
				$OutData["session"] = $NewSession;
				break;
			}


			$UserOwnerInfo = aib_get_user_owner_and_rights($GLOBALS["aib_db"],$UserID);
			if ($UserOwnerInfo["owner"] == false)
			{
				$UserOwnerInfo["owner"] = "NULL";
			}

			if ($UserOwnerInfo["rights"] == false)
			{
				$UserOwnerInfo["rights"] = "";
			}

			// Make sure the requesting user has rights to see this profile

			if (check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$RequestUserType) == false)
			{
				$OutData = array("status" => "ERROR", "info" => "NOTALLOWED","session" => $NewSession);
				break;
			}

			$UserRecord = $UserInfo["info"];
			$UserID = $UserRecord["user_id"];
			$PropertyListString = get_assoc_default($FormData,"property_list",false);
			if ($PropertyListString == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPROPERTYLIST";
				break;
			}

			$PropertyList = json_decode($PropertyListString,true);
			if (count($PropertyList) < 1)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "EMPTYLIST";
				break;
			}

			$OutData["status"] = "OK";
			foreach($PropertyList as $Entry)
			{
				$PropertyName = get_assoc_default($Entry,"name",false);
				$PropertyValue = get_assoc_default($Entry,"value",false);
				if ($PropertyName == false || $PropertyValue == false)
				{
					$OutData["status"] = "ERROR";
					$OutData["info"] = "BADENTRYFORMAT";
					break;
				}
			}

			if ($OutData["status"] != "OK")
			{
				break;
			}

			foreach($PropertyList as $Entry)
			{
				$PropertyName = urlencode(get_assoc_default($Entry,"name",false));
				$PropertyValue = urlencode(get_assoc_default($Entry,"value",false));
				ftree_set_user_prop($GLOBALS["aib_db"],$UserID,$PropertyName,$PropertyValue);
			}

			$OutData["status"] = "OK";
			$OutData["info"] = count($PropertyList);
			break;

		// Get users with a property matching a value, or users that have a property

		case "user_matching_prop":
			$PropertyName = get_assoc_default($FormData,"property_name",false);
			if ($PropertyName == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGPROPERTYNAME";
			}

			$PropertyValue = get_assoc_default($FormData,"property_value",false);
			if ($PropertyValue !== false)
			{
				$PropertyValue = urlencode($PropertyValue);
			}

			$Result = ftree_users_with_prop($GLOBALS["aib_db"],urlencode($PropertyName),$PropertyValue);
			$OutList = array();
			foreach($Result as $PropRecord)
			{
				$UserID = $PropRecord["user_id"];
				$Profile = ftree_get_user($GLOBALS["aib_db"],$UserID);
				if ($Profile == false)
				{
					continue;
				}

				$Profile["_properties"] = array();
				$PropertyList = ftree_list_user_prop($GLOBALS["aib_db"],$UserID);
				foreach($PropertyList as $LocalName => $LocalValue)
				{
					$Profile["_properties"][$TempName] = urldecode($LocalValue);
				}

				$OutList[] = $Profile;
			}

			$OutData["info"] = array("records" => $OutList);
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
