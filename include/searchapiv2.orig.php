<?php
define('LLM_INBOUND_PATH',"/home/stparch/virtual_sites/www.archiveinabox.com/.llmrequest");
define('LLM_OUTBOUND_PATH',"/home/stparch/virtual_sites/www.archiveinabox.com/.llmresponse");
define('LLM_QUERY_TIMEOUT',600);

// Search API

function log_debug($Msg)
{
	$Handle = fopen("/tmp/searchapi_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

// LLM interface
// -------------
function hsearch_query_llm($Options)
{
	if (isset($Options["chat_request"]) == false)
	{
		return("");
	}

	if (trim($Options["chat_request"]) == "")
	{
		return("");
	}

	// Put request in queue, wait for response.  First, generate a file name using the current POSIX time.
	// Format is SSSSSSSSSSSSSSSS_SSSSSS.req

	$MessageFileName = sprintf("%016.6lf",microtime(true));
	$MessageFileName = preg_replace("/[\.]/","_",$MessageFileName);

	// Temporary file is hidden to prevent the cron job from grabbing it before we've handled things.
	
	$TempMessageFileName = ".".$MessageFileName.".req";
	$MessageFileName = $MessageFileName.".req";
	$OutFile = LLM_INBOUND_PATH."/".$MessageFileName;
	$TempOutFile = LLM_INBOUND_PATH."/".$TempMessageFileName;
	$ReplyFile = LLM_OUTBOUND_PATH."/".$MessageFileName;

	// Save request to temp file

	$LocalArchiveID = $Options["index_name"];
	$LocalArchiveSeg = explode("_",$LocalArchiveID);
	$ArchiveID = array_pop($LocalArchiveSeg);
	file_put_contents($TempOutFile,$ArchiveID."\n".$Options["chat_request"]);

	// Move the temp file to a non-hidden file
	
	system("mv \"$TempOutFile\" \"$OutFile\" 2>/dev/null ; chmod a+rw \"$OutFile\"");

	// Wait for a response, or timeout.
	
	$StartTime = time();
	$TimeoutTime = $StartTime + LLM_QUERY_TIMEOUT;
	while(true)
	{
		if (time() > $TimeoutTime)
		{
			return("");
		}

		// If the file exists, load and return data

		if (file_exists($ReplyFile) == true)
		{
			sleep(0.25);
			$Buffer = file_get_contents($ReplyFile);
			unlink($ReplyFile);
			return($Buffer);
		}

		sleep(0.25);
	}

	return("");
}

function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}

// Convert LLM results to XML
// --------------------------
function hsearch_llm_response_to_xml($InBuffer,$Options)
{
	$LineSet = explode("\n",$InBuffer);
	$Mode = "prefix";
	$TempList = array();
	$SetData = array("doclist" => array());
	$DocData = array();
	foreach($LineSet as $Line)
	{
		$LocalLine = trim($Line);
		if ($LocalLine == "")
		{
			continue;
		}

		if (preg_match("/^[\[][\[]CHAT START[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "chat";
			continue;
		}

		if (preg_match("/^[\[][\[]CHAT END[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "end chat";
			$SetData["chat_result"] = join("\n",$TempList);
			$LocalText = $SetData["chat_result"];
			if (preg_match("/^[^\t]+'content':[ ]+\"/",$LocalText,$MatchSet) != false)
			{
				$LocalText = str_replace($MatchSet[0],"",$LocalText);
			}


			if (preg_match("/\"[\}],[^\t]+$/",$LocalText,$MatchSet) != false)
			{
				$LocalText = str_replace($MatchSet[0],"",$LocalText);
			}

			if (preg_match("/^[^\t]+'content':[ ]+'/",$LocalText,$MatchSet) != false)
			{
				$LocalText = str_replace($MatchSet[0],"",$LocalText);
			}


			if (preg_match("/'[\}],[^\t]+$/",$LocalText,$MatchSet) != false)
			{
				$LocalText = str_replace($MatchSet[0],"",$LocalText);
			}


			$LocalText = str_replace("\\n"," ",$LocalText);
			$LocalText = str_replace("\\'","'",$LocalText);
			$SetData["chat_result"] = $LocalText;
			$TempList = array();
			continue;
		}

		if (preg_match("/^[\[][\[]START LOCAL[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "local";
			continue;
		}

		if (preg_match("/^[\[][\[]END LOCAL[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "endlocal";
			break;
		}

		if (preg_match("/^[\[][\[]START DOC[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "startdoc";
			continue;
		}

		if (preg_match("/^[\[][\[]END DOC[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "enddoc";
			$SetData["doclist"][] = $DocData;
			$DocData = array();
			break;
		}

		if (preg_match("/^SCORE[\=]/",$LocalLine) != false)
		{
			$Mode = "startdoc";
			$LocalScore = preg_replace("/^SCORE[\=]/","",$LocalLine);
			$DocData["score"] = $LocalScore;
			continue;
		}

		if (preg_match("/^[\[][\[]START CONTENT[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "doctext";
			continue;
		}

		if (preg_match("/^[\[][\[]END CONTENT[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "endtext";
			$DocData["text"] = join(" ",$TempList);
			$TempList = array();
			continue;
		}

		if (preg_match("/^[\[][\[]START META[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "startmeta";
			continue;
		}

		if (preg_match("/^[\[][\[]END META[\]][\]]/",$LocalLine) != false)
		{
			$Mode = "endmeta";
			$JSONString = join(" ",$TempList);
			$JSONString = preg_replace("/[\{][\']/","{\"",$JSONString);
			$JSONString = preg_replace("/[\'][\}]/","\"}",$JSONString);
			$JSONString = preg_replace("/[\'][\:]/","\":",$JSONString);
			$JSONString = preg_replace("/[\:] [\']/",": \"",$JSONString);
			$JSONString = preg_replace("/[\'][ ]/","\" ",$JSONString);
			$JSONString = preg_replace("/[\'][\,][ ][\']/","\", \"",$JSONString);
log_debug("Reformatted JSON = $JSONString");
			$DocData["meta"] = json_decode($JSONString,true);
			$TempList = array();
			continue;
		}

		$TempList[] = $LocalLine;
	}

log_debug("Set data is ".var_export($SetData,true));
	$LocalResultsArray = array();
	$IndexName = preg_replace("/[a-zA-Z\_]+/","",$Options["index_name"]);
	if (isset($SetData["chat_result"]))
	{
		if (trim($SetData["chat_result"]) != "")
		{
			$LocalChatText = $SetData["chat_result"];
			$LocalResultsArray[] = "<doc>";
			$LocalResultsArray[] = "<searchid>$IndexName</searchid>";
			$LocalResultsArray[] = "<uri>$IndexName</uri>";
			$LocalResultsArray[] = "<llmchat>Y</llmchat>";
			$LocalResultsArray[] = "<title>Chat Response</title>";
			$LocalResultsArray[] = "<score>9999</score>";
			$LocalResultsArray[] = "<attr>\n</attr>";
			$LocalResultsArray[] = "<snippet>\n<sniptext>";
			$LocalResultsArray[] = $LocalChatText;
			$LocalResultsArray[] = "</sniptext>\n</snippet>\n</doc>";
		}
	}

	foreach($SetData["doclist"] as $Doc)
	{
		$LocalNodeID = intval($Doc["meta"]["nodeid"],16);
		$LocalNode = sprintf("%d",$LocalNodeID);
		$LocalResultsArray[] = "<doc>";
		$LocalResultsArray[] = "<searchid>$LocalNode</searchid>";
		$LocalResultsArray[] = "<uri>$LocalNode</uri>";
		$LocalResultsArray[] = "<llmhit>Y</llmhit>";
		$LocalResultsArray[] = "<title>".$Doc["meta"]["title"]."</title>";
		$LocalScore = floatval($Doc["score"]) * 1000.0;
		$LocalResultsArray[] = "<score>".sprintf("%d",$LocalScore)."</score>";
		$LocalResultsArray[] = "<attr>\n</attr>";
		$LocalResultsArray[] = "<snippet>\n<sniptext>";
		$LocalResultsArray[] = substr($Doc["text"],0,256);
		$LocalResultsArray[] = "</sniptext>\n</snippet>\n</doc>";
	}

	$LocalResultsBuffer = join("\n",$LocalResultsArray);
	return(array($LocalResultsBuffer,$SetData));
}

// Perform search request
// ----------------------
function hsearch_perform_search($Options)
{
	// Check for required fields

log_debug("Performing search using ".var_export($Options,true));
	$RequiredList = array("api_key","session_key","skip_to","output_count","equation","index_path","index_name","base_url");
	foreach($RequiredList as $Name)
	{
		if (isset($Options[$Name]) == false)
		{
			return("<request_status><status>ERROR: </status><info>Missing required field $Name</info></request_status>");
		}
	}

	// Do LLM query first, if there is one

	$StartLLMTime = time();
	$LLMBuffer = hsearch_query_llm($Options);
	$EndLLMTime = time();
	$DeltaLLMTime = $EndLLMTime - $StartLLMTime;
log_debug("Raw from LLM = ".$LLMBuffer);
	$LLMSet = hsearch_llm_response_to_xml($LLMBuffer,$Options);
	$LLMXML = $LLMSet[0];
	$DocSet = $LLMSet[1];
log_debug("Post XML conversion = ".var_export($LLMSet,true));

	$CommandLine = "estcmd search -hs -nl -vx";
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

//	$IndexPath = $Options["index_path"];
//	$CommandLine .= " \"$IndexPath\" ";
//	$CommandLine .= " \"".$Options["equation"]."\"";
//	$Buffer = shell_exec($CommandLine);
//	return(array($CommandLine,$Buffer));

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

	$GetList = array();
	$GetList[] = "phrase=".urlencode($Options["equation"]);
	foreach($AttrList as $AttrNum)
	{
		$AttrValue = $Attributes[$AttrNum];
		$LocalNum = intval($AttrNum) - 1;
		$SearchPostData["attr".$LocalNum] = $AttrValue;
		$GetList[] = "attr".$LocalNum."=".urlencode($AttrValue);
	}

	$GetList[] = "perpage=".$Options["output_count"];
	$GetList[] = "clip=-1";
	$GetList[] = "navi=1";
	$GetList[] = "_session=".(time() - 100);
	$GetList[] = "pagenum=".$Options["skip_to"];

	if (isset($Options["sort"]) == true)
	{
		$SearchPostData["order"] = $Options["sort"];
		$GetList[] = "order=".urlencode($Options["sort"]);
	}

	$GetList[] = "_indexpath=".urlencode($Options["index_path"]);
	$GetList[] = "_indexcfg=".urlencode($Options["index_name"]);

	$GetString = join("&",$GetList);
log_debug("Post data for search is ".var_export($SearchPostData,true));

//	print("<pre>DEBUG: POST DATA = \n".var_export($SearchPostData,true)."\n</pre>");
//	print("<pre>DEBUG: URL STRING = \n".$Options["base_url"]."/cgi-bin/estsearchutil?$GetString\n</pre>");

	if (trim($Options["equation"]) != "")
	{
		$CurlObj = curl_init();
		$QueryOptions = array(
			CURLOPT_POST => 1,
			CURLOPT_POST => 0,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $Options["base_url"]."/cgi-bin/estsearchutil",
//			CURLOPT_URL => $Options["base_url"]."/cgi-bin/estsearchutil?$GetString",
			CURLOPT_FRESH_CONNECT => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 0,
			CURLOPT_TIMEOUT => 300,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => http_build_query($SearchPostData),
		);
	
		curl_setopt_array($CurlObj,$QueryOptions);
		$Result = curl_exec($CurlObj);
	}
	else
	{
		$CurlObj = false;
		$Result = false;
	}

log_debug("estsearchutil result = \n$Result");
	if ($Result == false)
	{
		// If there's an LLM response, add it

		if ($LLMBuffer != false)
		{
			$FinalLines = array();
			$FinalLines[] = "<status><status_value>OK</status_value></status>";
			$FinalLines[] = "<resultset><search_spec><phrase>".$Options["chat_request"]."</phrase>";
			$FinalLines[] = "<attribute></attribute>";
			$FinalLines[] = "</search_spec>";
			$FinalLines[] = "<start>".sprintf("%d",intval($Options["skip_to"]))."</start>";
			$End = intval($Options["skip_to"]) + intval($Options["output_count"]);
			$FinalLines[] = "<end>$End</end>";
			$HitCount = count($DocSet["doclist"]) + 1;
			$FinalLines[] = "<hitcount>$HitCount</hitcount>";
			$FinalLines[] = "<match_count>$HitCount</match_count>";
			$FinalLines[] = "<auxwordcond>false</auxwordcond>";
			$FinalLines[] = "<proctime>$DeltaLLMTime</proctime>";
			$FinalLines[] = "<hints><hintword><value>(null)</value><word>0</word><auxword>(null)</auxword></hintword></hints>";
			$FinalLines[] = "<related_terms></related_terms>";
			$FinalLines[] = "<index_info>";
			$FinalLines[] = "<pagecount>$HitCount</pagecount>";
			$FinalLines[] = "<wordcount>$HitCount</wordcount>";
			$FinalLines[] = "</index_info>";
			$FinalLines[] = $LLMXML;
			$FinalLines[] = "</resultset>";
			$FinalBuffer = "<document>".join("\n",$FinalLines)."</document>";
log_debug("Final buffer =\n$FinalBuffer");
			$OutData = array("status" => "OK", "data" => "<document>".$FinalBuffer."</document>");
		}
		else
		{
			if ($CurlObj != false)
			{
				$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj)."\n".$Options["base_url"]."/cgi-bin/estsearchutil","data" => false, "llm_response" => $LLMXML);
				curl_close($CurlObj);
			}
			else
			{
				$OutData = array("status" => "ERROR", "info" => $Options["base_url"]."/cgi-bin/estsearchutil","data" => false, "llm_response" => $LLMXML);
			}
		}
	}
	else
	{
log_debug("MERGE RESULTS");
		// Convert everything to lines

		$StdSearchLines = explode("\n",$Result);
		$OutLines = array();

		// Go until we get to a "<doc>" tag, then insert the LLM results

		while(true)
		{
			$LocalLine = array_shift($StdSearchLines);
			if ($LocalLine == false)
			{
				break;
			}

			if (preg_match("/^[^\<]+[\<]doc[\>]/",$LocalLine) != false)
			{
				if ($LLMBuffer != false)
				{
					$LLMLines = explode("\n",$LLMXML);
					foreach($LLMLines as $AILine)
					{
						$OutLines[] = $AILine;
					}

					$OutLines[] = $LocalLine;
					break;
				}
			}
			else
			{
				$OutLines[] = $LocalLine;
			}
		}

		// Append the rest of the results

		while(count($StdSearchLines) > 0)
		{
			$OutLines[] = array_shift($StdSearchLines);
		}

		// Create final buffer

		$FinalBuffer = join("\n",$OutLines);
log_debug("MERGE BUFFER = ".$FinalBuffer);
		$OutData = array("status" => "OK", "data" => "<document>".$FinalBuffer."</document>");
		curl_close($CurlObj);
	}

	return($OutData);
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
