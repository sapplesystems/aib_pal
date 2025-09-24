<?php

include("../config/aib.php");
include("../config/aib_site.php");

// Search API
function print_to_logfile_search($Msg)
{
	$Handle = fopen("/tmp/searchapi_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg);
		fclose($Handle);
	}
}


// Perform search request
// ----------------------
function hsearch_perform_search($Options)
{
	$LLMResult = false;
	if (isset($Options["llm_query"]) == true)
	{
		$LLMResult = hsearch_llm_search($Options);
	}

	// Check for required fields

	$RequiredMissing = false;
	$RequiredList = array("api_key","session_key","skip_to","output_count","equation","index_path","index_name","base_url");
	foreach($RequiredList as $Name)
	{
		if (isset($Options[$Name]) == false)
		{
			$RequiredMissing = $Name;
			if ($LLMResult == false)
			{
				return("<request_status><status>ERROR: </status><info>Missing required field $Name</info></request_status>");
			}
		}
	}

	if ($RequiredMissing != false)
	{
		if ($LLMResult == false)
		{
			return("<request_status><status>ERROR: </status><info>Missing required field $RequiredMissing</info></request_status>");
		}
		else
		{
			$OutData = array("status" => "OK", "data" => "<document></document>", "llm_result" => $LLMResult);
		}
	}

	$Attributes = array();
	foreach($Options as $Name => $Value)
	{
		if (preg_match("/^attr_/",$Name) != false)
		{
			$LocalName = preg_replace("/^attr_/","",$Name);
			$LocalName = sprintf("%04d",intval($LocalName));
			$Attributes[$LocalName] = $Value;
		}
	}

	$StartResult = $Options["skip_to"];
	$OutputMaxCount = $Options["output_count"];
	$AttrList = array_keys($Attributes);
	sort($AttrList);

	$SearchPostData = array(
		"_session" => time() - 100,
		"phrase" => $Options["equation"],
		"pagenum" => intval($Options["skip_to"]),
		"perpage" => intval($Options["output_count"]),
		"_indexpath" => $Options["index_path"],
		"_indexcfg" => $Options["index_name"],
		"navi" => "1",
		"clip" => "-1",
	);

	foreach($AttrList as $AttrNum)
	{
		$AttrValue = $Attributes[$AttrNum];
		$LocalNum = intval($AttrNum) - 1;
		$SearchPostData["attr".$LocalNum] = $AttrValue;
	}

	if (isset($Options["sort"]) == true)
	{
		$SearchPostData["order"] = $Options["sort"];
	}

	$CurlObj = curl_init();
	$QueryOptions = array(
		CURLOPT_POST => 1,
		CURLOPT_POST => 0,
		CURLOPT_HEADER => 0,
#		CURLOPT_URL => "https://localhost/cgi-bin/estsearchutil",
# Altered for develop, since the server doesn't have an SSL config
#		CURLOPT_URL => "http://develop.archiveinabox.com/cgi-bin/estsearchutil",
		CURLOPT_URL => AIB_SERVER_PROTOCOL."://".AIB_SERVER_NAME.":".AIB_SERVER_PORT."/cgi-bin/estsearchutil",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_POSTFIELDS => http_build_query($SearchPostData),
	);

	curl_setopt_array($CurlObj,$QueryOptions);
	$Result = curl_exec($CurlObj);
	if ($Result == false)
	{
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj)."\n".$Options["base_url"]."/cgi-bin/estsearchutil","data" => false);
	}
	else
	{
		$OutData = array("status" => "OK", "data" => "<document>".$Result."</document>");
	}

	curl_close($CurlObj);
	if ($LLMResult != false)
	{
		$OutData["llm_result"] = $LLMResult;
	}

	return($OutData);
}

function hsearch_llm_search($FormData)
{
	$QueryText = base64_encode($FormData["llm_query"]);
	$Buffer = shell_exec("python3 query_llm.py \"$QueryText\"");
	return($Buffer);
}

// Convert delimited format to assoc array
// ---------------------------------------
function hsearch_format_to_array($InString)
{
	$List = explode("|",$InString);
	if (count($List) < 1)
	{
		return(array());
	}

	$Out = array();
	foreach($List as $Entry)
	{
		$Segs = explode("=",$Entry);
		if (count($Segs) < 2)
		{
			continue;
		}

		$EntryName = array_shift($Segs);
		$EntryValue = join("=",$Segs);
		$Out[$EntryName] = $EntryValue;
	}

	return($Out);
}

// Convert assoc array to delimited format
// ---------------------------------------
function hsearch_array_to_format($InArray)
{
	$TempList = array();
	foreach($InArray as $Name => $Value)
	{
		$TempList[] = $Name."=".$Value;
	}

	return(join("|",$TempList));
}

// Mark a user-defined field as searchable
// ---------------------------------------
function hsearch_set_field_searchable($DBHandle,$FieldID,$DataType)
{
	$LocalID = trim(preg_replace("/[^0-9]/","",$FieldID));
	if ($LocalID == "")
	{
		return(false);
	}

	$Result = mysqli_query($DBHandle,"SELECT * FROM field_def WHERE field_id='$LocalID';");
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

	$Info = $Row["field_format"];
	$InfoArray = hsearch_format_to_array($Info);
	if ($InfoArray == false)
	{
		return(false);
	}

	if (count(array_keys($InfoArray)) < 1)
	{
		return(false);
	}

	$InfoArray["attr_field"] = "Y";
	$InfoArray["attr_format"] = $DataType;
	$InfoString = hsearch_array_to_format($InfoArray);
	if ($InfoString == "")
	{
		return(false);
	}

	$Result = mysqli_query($DBHandle,"UPDATE field_def SET field_format='$InfoString' WHERE field_id='$LocalID';");
	return(true);
}

// Unmark a user-defined field as searchable
// -----------------------------------------
function hsearch_set_field_not_searchable($DBHandle,$FieldID)
{
	$LocalID = trim(preg_replace("/[^0-9]/","",$FieldID));
	if ($LocalID == "")
	{
		return(false);
	}

	$Result = mysqli_query($DBHandle,"SELECT * FROM field_def WHERE field_id='$LocalID';");
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

	$Info = $Row["field_format"];
	$InfoArray = hsearch_format_to_array($Info);
	if ($InfoArray == false)
	{
		return(false);
	}

	if (count(array_keys($InfoArray)) < 1)
	{
		return(false);
	}

	if (isset($InfoArray["attr_field"]) == true)
	{
		unset($InfoArray["attr_field"]);
	}

	if (isset($InfoArray["attr_format"]) == true)
	{
		unset($InfoArray["attr_format"]);
	}

	$InfoString = hsearch_array_to_format($InfoArray);
	if ($InfoString == "")
	{
		return(false);
	}

	$Result = mysqli_query($DBHandle,"UPDATE field_def SET field_format='$InfoString' WHERE field_id='$LocalID';");
	return(true);
}

// Given a field name, owner type and owner ID, retrieve field ID for use with attributes
// --------------------------------------------------------------------------------------
function hsearch_find_field($DBHandle,$FieldName,$OwnerID)
{

	$LocalID = trim(preg_replace("/[^0-9]/","",$OwnerID));
	$Result = mysqli_query($DBHandle,"SELECT * FROM field_def WHERE field_title='".urlencode($FieldName)."' AND owner_id='$LocalID' LIMIT 1;");
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

	return($Row["field_id"]);
}

?>
