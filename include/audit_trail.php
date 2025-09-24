<?php
//
// audit_trail.php
//
// Audit trail recording
//

include("../config/aib.php");


// Add audit trail event
// ---------------------
function aib_audit_log_event($DBHandle,$EventType,$InfoArray)
{
	// Set up preset values based on event type, and create JSON encoded array

	$ReturnValue = array("status" => "OK", "msg" => "");
	$UserID = -1;
	$ItemID = -1;
	$ItemInfo = "";
	$OperationType = "";
	$OperationInfo = "";
	$RecordTime = microtime(true);
	switch($EventType)
	{
		case AIB_AUDIT_EVENT_LOGIN:
		case AIB_AUDIT_EVENT_LOGIN_FAIL:
			$RequiredList = array("user_login","ipaddr");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$TempData = array("user_login" => $InfoArray["user_login"], "ipaddr" => $InfoArray["ipaddr"]);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_LOGIN_SUCCEED:
		case AIB_AUDIT_EVENT_LOGOUT:
			$RequiredList = array("user_login","ipaddr","user_id");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$TempData = array("user_login" => $InfoArray["user_login"], "ipaddr" => $InfoArray["ipaddr"], "user_id" => $UserID);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_SESSION_TIMEOUT:
			$RequiredList = array("user_login","user_id");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$TempData = array("user_login" => $InfoArray["user_login"], "user_id" => $UserID);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_MODIFY_ACCOUNT:
			$RequiredList = array("mod_user_id","user_id","mod_type");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$TempData = array("mod_user_id" => $InfoArray["mod_user_id"], "user_id" => $UserID, "mod_type" => $InfoArray["mod_type"]);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_CREATE_RECORD:
			$RequiredList = array("user_id","parent_item");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["parent_item"];
			$TempData = array("user_id" => $UserID, "parent_item" => $InfoArray["parent_item"]);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_MODIFY_RECORD:
			$RequiredList = array("item_id","user_id","mod_type");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["item_id"];
			$TempData = array("item_id" => $ItemID, "user_id" => $UserID, "mod_type" => $InfoArray["mod_type"]);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_ADD_RECORD_ITEM:
			$RequiredList = array("user_id","record_item_id","file_upload");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["record_item_id"];
			$TempData = array("record_item_id" => $ItemID, "user_id" => $UserID, "file_upload" => $InfoArray["file_upload"]);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_MODIFY_RECORD_ITEM:
			$RequiredList = array("user_id","item_id","record_item_id","mod_type");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["item_id"];
			$TempData = array("record_item_id" => $InfoArray["record_item_id"], "item_id" => $ItemID,
			       "user_id" => $UserID, "mod_type" => $InfoArray["mod_type"]);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_CREATE_TREE_ENTRY:
			$RequiredList = array("user_id","parent_item","new_item_title","item_id");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["item_id"];
			$TempData = array("parent_item" => $InfoArray["parent_item"], "item_id" => $ItemID,
			       "user_id" => $UserID, "new_item_title" => urlencode($InfoArray["new_item_title"]));
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_MODIFY_TREE_ENTRY:
			$RequiredList = array("user_id","item_id");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["item_id"];
			$TempData = array("item_id" => $ItemID, "user_id" => $UserID);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_UPLOAD_FILE:
			$RequiredList = array("user_id","parent_item");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["parent_item"];
			$TempData = array("parent_item" => $ItemID, "user_id" => $UserID);
			if (isset($InfoArray["item_id"]) == true)
			{
				$TempData["item_id"] = $InfoArray["item_id"];
			}
			else
			{
				$TempData["item_id"] = "";
			}

			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_UPLOAD_IMPORT:
		case AIB_AUDIT_EVENT_UPLOAD_IMPORT_COMPLETE:
		case AIB_AUDIT_EVENT_SUBMIT_IMPORT:
			$RequiredList = array("user_id","parent_item");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["parent_item"];
			$TempData = array("parent_item" => $ItemID, "user_id" => $UserID);
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_PROCESS_IMPORT:
		case AIB_AUDIT_EVENT_COMPLETE_IMPORT:
			$RequiredList = array("user_id","parent_item","batch_file_name");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["parent_item"];
			$TempData = array("parent_item" => $ItemID, "user_id" => $UserID, "batch_file_name" => urlencode($InfoArray["batch_file_name"]));
			$OperationInfo = json_encode($TempData);
			break;

		case AIB_AUDIT_EVENT_VIEW_RECORD:
		case AIB_AUDIT_EVENT_VIEW_ITEM:
			$RequiredList = array("user_id","item_id","ipaddr");
			foreach($RequiredList as $Name)
			{
				if (isset($InfoArray[$Name]) == false)
				{
					$ReturnValue["status"] = "ERROR";
					$ReturnValue["msg"] = "Missing required value: $Name";
					return($ReturnValue);
				}
			}

			$UserID = $InfoArray["user_id"];
			$ItemID = $InfoArray["item_id"];
			$TempData = array("item_id" => $ItemID, "user_id" => $UserID, "ipaddr" => $InfoArray["ipaddr"]);
			$OperationInfo = json_encode($TempData);
			break;


		default:
			$ReturnValue["status"] = "ERROR";
			$ReturnValue["msg"] = "Invalid event type: $EventType";
			return($ReturnValue);
	}

	$Query = "INSERT INTO audit_trail(record_time,operation_type,operation_info,user_id,item_id,item_info) VALUES ('$RecordTime','$EventType','$OperationInfo',".
		"'$UserID','$ItemID','$ItemInfo');";
	mysqli_query($DBHandle,$Query);
	return($ReturnValue);
}

// Query for specific event
// ------------------------
function aib_audit_get_event($DBHandle,$EventRecordID)
{
	$ReturnValue = array("status" => "OK", "msg" => "");
	$Query = "SELECT * FROM audit_trail WHERE record_id='$EventRecordID';";
	$Result = mysli_query($DBHandle,$Query);
	if ($Result == false)
	{
		$ReturnValue["status"] = "ERROR";
		$ReturnValue["msg"] = "QUERY FAILED: ".mysqli_error($DBHandle);
		return($ReturnValue);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		$ReturnValue["status"] = "ERROR";
		$ReturnValue["msg"] = "NOT FOUND";
		return($ReturnValue);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
}


// Flush events older than a date or N days.  Optionally flush specific types listed in an array.
// ----------------------------------------------------------------------------------------------
function aib_audit_flush_events($DBHandle,$OlderThanDate = false, $OlderThanDays = false, $TypeSelect = false)
{
	$ReturnValue = array("status" => "OK", "msg" => "");
	$Query = "DELETE FROM audit_trail WHERE ";
	$AndFlag = false;
	if ($OlderThanDate !== false && $OlderThanDays == false)
	{
		$TimeValue = strtotime($OlderThanDate);
		$Query .= "record_time < '$TimeValue' "; 
		$AndFlag = true;
	}

	if ($OlderThanDays !== false)
	{
		$DayValue = $OlderThanDays * 24 * 60 * 60;
		$TimeValue = microtime(true);
		$TimeValue = $TimeValue - $DayValue;
		if ($AndFlag != false)
		{
			$Query .= " AND ";
		}

		$Query .= " record_time < '$TimeValue' ";
		$AndFlag = true;
	}

	if ($TypeSelect != false)
	{
		$Limit = count($TypeSelect);
		if ($Limit > 0)
		{
			if ($AndFlag != false)
			{
				$Query .= " AND (";
			}
		}

		$Counter = 0;
		foreach($TypeSelect as $Type)
		{
			$Query .= "operation_type = '$Type'";
			$Counter++;
			if ($Counter < $Limit - 1)
			{
				$Query .= " OR ";
			}
		}

		$Query .= ")";
	}

	if ($AndFlag == false)
	{
		$ReturnValue = array("status" => "ERROR", "msg" => "NO FILTER SPECIFIED");
		return($ReturnValue);
	}


	$Query .= " ;";
	mysqli_query($DBHandle,$Query);
	return($ReturnValue);
}


// Query for events.  Options are for type(s), date range, user, item_id, start and end of result set.  These are
// in the Options associative array as:
//
// typelist=<array of types>
// date_low=<date/time string>
// date_high=<date/time string>
// user_id=<user ID>
// item_id=<item ID>
// first_result=<row number>
// last_result=<row number>
// most_recent=<num rows>
//
// If "most_recent" is used, the records will be returned in reverse order, with the oldest being at the start
// of the list of records returned. 
// ---------------------------------------------------------------------------------------------------------------
function aib_audit_query($DBHandle,$Options)
{
	$ReturnValue = array("status" => "OK", "msg" => "");
	$AndFlag = false;
	$Query = "SELECT * FROM audit_trail";
	$OptFlag = false;
	$OptList = array("typelist","date_low","date_high","user_id","item_id","first_result","last_result","most_recent");
	foreach($OptList as $Name)
	{
		if (isset($Options[$Name]) == true)
		{
			$OptFlag = true;
			break;
		}
	}

	if ($OptFlag == false)
	{
		$Query .= ";";
	}
	else
	{
		$Query .= " WHERE ";
		if (isset($Options["typelist"]) == true)
		{
			$Query .= "(";
			$TypeSelect = $Options["typelist"];
			$Limit = count($TypeSelect);
			$Counter = 0;
			foreach($TypeSelect as $Type)
			{
				$Query .= "operation_type = '$Type'";
				$Counter++;
				if ($Counter < $Limit - 1)
				{
					$Query .= " OR ";
				}
			}

			$Query .= ") ";
		}

		$AndFlag = true;
		if (isset($Options["date_low"]) == true)
		{
			if (isset($Options["date_high"]) == true)
			{
				$LowTime = strtotime($Options["date_low"]);
				$HighTime = strtotime($Options["date_high"]);
				if ($AndFlag == true)
				{
					$Query .= " AND ";
				}

				$Query .= "(record_time >= '$LowTime' AND record_time <= '$HighTime')";
				$AndFlag = true;
			}
			else
			{
				if ($AndFlag == true)
				{
					$Query .= " AND ";
				}

				$LowTime = strtotime($Options["date_low"]);
				$Query .= "record_time >= '$LowTime' ";
				$AndFlag = true;
			}
		}

		if (isset($Options["user_id"]) == true)
		{
			if ($AndFlag == true)
			{
				$Query .= " AND ";
			}

			$Query .= " user_id='".$Options["user_id"]."'";
			$AndFlag = true;
		}

		if (isset($Options["item_id"]) == true)
		{
			if ($AndFlag == true)
			{
				$Query .= " AND ";
			}

			$Query .= " item_id='".$Options["user_id"]."'";
			$AndFlag = true;
		}

		if (isset($Options["first_result"]) == true)
		{
			if (isset($Options["last_result"]) == true)
			{
				$FirstResult = intval($Options["first_result"]);
				$LastResult = intval($Options["last_result"]);
				$RowCount = $LastResult - $FirstResult;
				$Query .= " limit $FirstResult,$RowCount";
			}
			else
			{
				$FirstResult = intval($Options["first_result"]);
				$LastResult = "18446744073709551615";
				$Query .= " limit $FirstResult,$LastResult";
			}
		}
		else
		{
			if (isset($Options["last_result"]) == true)
			{
				$LastResult = intval($Options["last_result"]);
				$Query .= " limit $LastResult";
			}
		}

		if (isset($Options["most_recent"]) == true && isset($Options["last_result"]) == false && isset($Options["first_result"]) == false)
		{
			$FinalRows = intval($Options["most_recent"]);
			$Query .= " ORDER BY record_time DESC LIMIT $FinalRows";
		}

		$Query .= ";";
	}

	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		$ReturnValue["status"] = "ERROR";
		$ReturnValue["msg"] = "QUERY FAILED: ".mysqli_error($DBHandle);
		return($ReturnValue);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		$ReturnValue["status"] = "ERROR";
		$ReturnValue["msg"] = "NOT FOUND";
		return($ReturnValue);
	}

	$ReturnValue["records"] = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$ReturnValue["records"][] = $Row;
	}

	mysqli_free_result($Result);
	return($ReturnValue);
}

?>
