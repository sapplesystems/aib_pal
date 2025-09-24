<?php

//
// Generate a PDF from one or more image inputs
//
// FUNCTIONAL INCLUDES

include("api_util.php");
require("html2pdf.php");

// Log an error message
// --------------------
function log_make_pdf_debug($Msg)
{
	$Handle = fopen("/tmp/make_pdf_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
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



function load_rlc($FileName,$ImageWidth = 1800,$ImageHeight = 2771,$PageWidth = 8.5, $PageHeight = 14.0)
{
	$OutData = array();
	$XPixelsPerInch = $ImageWidth / $PageWidth;
	$YPixelsPerInch = $ImageHeight / $PageHeight;
	$Buffer = file_get_contents($FileName);
	$Lines = explode("\n",$Buffer);
	$PageSpecLine = array_shift($Lines);

	while(true)
	{
		// Generic, word-oriented RLC

		if (preg_match("/[\,]/",$PageSpecLine) == true)
		{
			$TempField = preg_split("/[\,]/",$PageSpecLine);
			if (count($TempField) >= 7)
			{
				$PageSpec = preg_split("/[ \,]/",$PageSpecLine);
				$OriginalWidth = $PageSpec[0];
				$OriginalHeight = $PageSpec[1];
				$XRatio = $ImageWidth / $OriginalWidth;
				$YRatio = $ImageHeight / $OriginalHeight;
				foreach($Lines as $Line)
				{
					if (ltrim(rtrim($Line)) == "")
					{
						continue;
					}

					$LineFields = explode(",",$Line);
					$Y2 = array_pop($LineFields);
					$X2 = array_pop($LineFields);
					$Y1 = array_pop($LineFields);
					$X1 = array_pop($LineFields);
					$Word = join(",",$LineFields);
					$Y2 = $Y2 * $YRatio / $YPixelsPerInch;
					$X2 = $X2 * $XRatio / $XPixelsPerInch;
					$Y1 = $Y1 * $YRatio / $YPixelsPerInch;
					$X1 = $X1 * $XRatio / $XPixelsPerInch;
					$OutData[] = array("word" => $Word, "x" => $X1, "y" => $Y1, "x1" => $X2, "y1" => $Y2);
				}

				return($OutData);
			}
		}

		// Character-oriented rlc

		if (preg_match("/[ \t]+/",$PageSpecLine) == true)
		{
			$TempField = preg_split("/[ \t]/",$PageSpecLine);
			if (count($TempField) >= 7)
			{
				$Mode = "top";
				$PageSpec = preg_split("/[ \t]+/",$PageSpecLine);
				$OriginalWidth = $PageSpec[0];
				$OriginalHeight = $PageSpec[1];
				$XRatio = $ImageWidth / $OriginalWidth;
				$YRatio = $ImageHeight / $OriginalHeight;
				$SkewHeight = $PageSpec[2];
				$Angle = $PageSpec[3];
				$XShift = $PageSpec[4];
				$XEdge = $PageSpec[5];
				$YShift = $PageSpec[6];
				$CurrentWordText = "";
				$AlternateWordText = "";
				$CurrentWordX = -1;
				$CurrentWordX1 = -1;
				$CurrentWordY = -1;
				$CurrentWordY1 = -1;
				foreach($Lines as $Line)
				{
					if (ltrim(rtrim($Line)) == "")
					{
						continue;
					}

					$Fields = preg_split("/[ \t]+/",$Line);
					switch(strtoupper($Fields[0]))
					{
						// Alternate character:
						// X1, Y1, X2, Y2, Flags

						case "A":
							$Mode = "W";
							break;

						// Word:
						// X1, Y1, X2, Y2, Flags [alternate character]

						case "W":
							if ($Mode == "top")
							{
								$Mode = "W";
							}

							switch($Mode)
							{
								case "L":
									break;

								case "W":
									if ($CurrentWordX < 0)
									{
										$CurrentWordX = $Fields[1];
										$CurrentWordY = $Fields[2];
										$CurrentWordX1 = $Fields[3];
										$CurrentWordY1 = $Fields[4];
									}

									if ($CurrentWordText != "")
									{
										$X1 = $CurrentWordX;
										$X2 = $CurrentWordX1;
										$Y1 = $CurrentWordY;
										$Y2 = $CurrentWordY1;
										$Y2 = $Y2 * $YRatio / $YPixelsPerInch;
										$X2 = $X2 * $XRatio / $XPixelsPerInch;
										$Y1 = $Y1 * $YRatio / $YPixelsPerInch;
										$X1 = $X1 * $XRatio / $XPixelsPerInch;
										$OutData[] = array("word" => $CurrentWordText, "x" => $X1,
											"y" => $Y1, "x1" => $X2, "y1" => $Y2);
                		                                                $CurrentWordText = "";
										$AlternateWordText = "";
                                                		                $CurrentWordX = -1;
                                                                	        $CurrentWordX1 = -1;
		                                                                $CurrentWordY = -1;
                		                                                $CurrentWordY1 = -1;
                                                		        }

                                                               		$CurrentWordX = $Fields[1];
	                                                                $CurrentWordY = $Fields[2];
               		                                                $CurrentWordX1 = $Fields[3];
                               		                                $CurrentWordY1 = $Fields[4];
                                               		                break;

	                                                        default:
               		                                                break;
                               		                }

                                               		$Mode = "W";
	                                                break;

               		                        // Character.  Fields are:
                               		        // X1, Y1, X2, Y2, Flags, Character, Confidence rating
	                                        case "C":
               		                                $Mode = "W";
							if ($Fields[6] < "!" || $Fields[6] > "~")
							{
								$Fields[6] = "";
							}

                               		                $CurrentWordText .= $Fields[6];
							if (count($Fields) > 12)
							{
								if ($Fields[12] < "!" || $Fields[12] > "~")
								{
									$Fields[12] = "";
								}

								$AlternateWordText .= $Fields[6].$Fields[12];
							}
							else
							{
								$AlternateWordText .= $Fields[6];
							}

							break;

	                                        // Line.  Fields are:
               		                        // X1, Y1, X2, Y2
                               		        case "L":
                                               		break;

	                                        default:
               		                                break;
                               		}
               		        }

	                        if ($CurrentWordText != "")
               		        {
					$X1 = $CurrentWordX;
					$X2 = $CurrentWordX1;
					$Y1 = $CurrentWordY;
					$Y2 = $CurrentWordY1;
					$Y2 = $Y2 * $YRatio / $YPixelsPerInch;
					$X2 = $X2 * $XRatio / $XPixelsPerInch;
					$Y1 = $Y1 * $YRatio / $YPixelsPerInch;
					$X1 = $X1 * $XRatio / $XPixelsPerInch;
					$OutData[] = array("word" => $CurrentWordText, "x" => $X1,
						"y" => $Y1, "x1" => $X2, "y1" => $Y2);
	                        }
               		}

		}

		break;

	}

	return($OutData);
}

function get_command_line_options($Argv)
{
	$OutData = array();
	$Counter = 1;
	while(isset($Argv[$Counter]) == true)
	{
		if (preg_match("/[\=]/",$Argv[$Counter]) == true)
		{
			$Segs = explode("=",$Argv[$Counter]);
			$FieldName = array_shift($Segs);
			$FieldValue = join("=",$Segs);
			$OutData[$FieldName] = $FieldValue;
		}
		else
		{
			$OutData[$Argv[$Counter]] = "Y";
		}

		$Counter++;
	}

	return($OutData);
}

function load_eim($FileName,$ImageWidth = 1800,$ImageHeight = 2771,$PageWidth = 8.5, $PageHeight = 14.0)
{
	$OutData = array();
	$XPixelsPerInch = $ImageWidth / $PageWidth;
	$YPixelsPerInch = $ImageHeight / $PageHeight;
	$Buffer = file_get_contents($FileName);
	$Lines = explode("\n",$Buffer);
	$PageSpecLine = array_shift($Lines);
        $Lines = explode("\n",$Buffer);
        $TopLine = array_shift($Lines);
        $Fields = preg_split("/[ \t]+/",$TopLine);
	$OriginalWidth = $Fields[2];
	$OriginalHeight = $Fields[3];
	$OriginalXDPI = $Fields[4];
	$OriginalYDPI = $Fields[5];
	$Mode = false;
	$CurrentWordText = "";
	$CurrentWordX = -1;
	$CurrentWordY = -1;
	$CurrentWordX1 = -1;
	$CurrentWordY1 = -1;
	$XRatio = $ImageWidth / $OriginalWidth;
	$YRatio = $ImageHeight / $OriginalHeight;
        foreach($Lines as $Line)
        {
		$Fields = preg_split("/[ \t]+/",$Line);
                switch(strtoupper($Fields[0]))
                {
			// Alternate character.  Fields are:
                        // Character
                        case "A":
                        	$Mode = "W";
                                break;

                        // Region.  Fields are:
                        // X,Y,X1,Y1,?
                        case "R":
                                break;

                        // Word.  Fields are:
                        // X1, Y1, X2, Y2,?
                        case "W":
	                        switch($Mode)
                                {
                                	case "L":
                                        	break;

                                        case "W":
                                                if ($CurrentWordText != "")
                                                {
							$X1 = $CurrentWordX;
							$X2 = $CurrentWordX1;
							$Y1 = $CurrentWordY;
							$Y2 = $CurrentWordY1;
							$Y2 = $Y2 * $YRatio / $YPixelsPerInch;
							$X2 = $X2 * $XRatio / $XPixelsPerInch;
							$Y1 = $Y1 * $YRatio / $YPixelsPerInch;
							$X1 = $X1 * $XRatio / $XPixelsPerInch;
							$OutData[] = array("word" => $CurrentWordText, "x" => $X1, "y" => $Y1,
								"x1" => $X2, "y1" => $Y2);
                                                        $CurrentWordText = "";
                                                        $CurrentWordX = -1;
                                                        $CurrentWordX1 = -1;
                                                        $CurrentWordY = -1;
                                                        $CurrentWordY1 = -1;
                                                }

                                                $CurrentWordX = $Fields[1];
                                                $CurrentWordY = $Fields[2];
                                                $CurrentWordX1 = $Fields[3];
                                                $CurrentWordY1 = $Fields[4];
                                                break;

                                       default:
                                                break;
                                }

                                $Mode = "W";
                               	break;

                        // Character.  Fields are:
                        // X1, Y1, X2, Y2, Flags, Character, Confidence rating
                        case "C":
                                $Mode = "W";
                                $CurrentWordText .= $Fields[6];
                                break;

                        // Line.  Fields are:
                        // X1, Y1, X2, Y2,?
                        case "L":
                                break;

                        default:
                                break;
                }
        }

        if ($CurrentWordText != "")
        {
		$X1 = $CurrentWordX;
		$X2 = $CurrentWordX1;
		$Y1 = $CurrentWordY;
		$Y2 = $CurrentWordY1;
		$Y2 = $Y2 * $YRatio / $YPixelsPerInch;
		$X2 = $X2 * $XRatio / $XPixelsPerInch;
		$Y1 = $Y1 * $YRatio / $YPixelsPerInch;
		$X1 = $X1 * $XRatio / $XPixelsPerInch;
		$OutData[] = array("word" => $CurrentWordText, "x" => $X1, "y" => $Y1,
			"x1" => $X2, "y1" => $Y2);
        }

	return($OutData);
}

function load_xml($FileName,$ImageWidth = 1800,$ImageHeight = 2771,$PageWidth = 8.5, $PageHeight = 14.0)
{
                // XML file

	$XPixelsPerInch = $ImageWidth / $PageWidth;
	$YPixelsPerInch = $ImageHeight / $PageHeight;
	$OutData = array();
        $TestName = $BaseName.".xml";
        $Buffer = file_get_contents($TestName);
        $Lines = explode("\n",$Buffer);
        $TopLine = array_shift($Lines);
        $Fields = preg_split("/[ \t]+/",$TopLine);
	$OriginalWidth = $Fields[2];
	$OriginalHeight = $Fields[3];
	$OriginalXDPI = $Fields[4];
	$OriginalYDPI = $Fields[5];
        $Mode = "X";
	$XRatio = $ImageWidth / $OriginalWidth;
	$YRatio = $ImageHeight / $OriginalHeight;
        foreach($Lines as $Line)
        {
                while(true)
                {
                        // Page specification

                        if (preg_match("/[\<]page/",$Line) != false)
                        {
                                $FieldSet = parse_xml_fields($Line);
                                $PageInfo["width"] = $FieldSet["x"];
                                $PageInfo["height"] = $FieldSet["y"];
                                $PageInfo["xdpi"] = $FieldSet["xdpi"];
                                $PageInfo["ydpi"] = $FieldSet["ydpi"];
                                $PageInfo["source_file"] = $FieldSet["image"];
                                $PageInfo["batch_name"] = $FieldSet["batch"];
                                $Mode = "P";
                                break;
                        }

                        // Word specification

                        if (preg_match("/[\<]word/",$Line) != false)
                        {
                                $Mode = "W";
                                $FieldSet = parse_xml_fields($Line);
                                $CurrentWordX = $FieldSet["x1"];
                                $CurrentWordY = $FieldSet["y1"];
                                $CurrentWordX1 = $FieldSet["x2"];
                                $CurrentWordY1 = $FieldSet["y2"];
                                break;
                        }

                        // Word text

                        if (preg_match("/[\<]text/",$Line) != false)
                        {
                                $Mode = "R";
                                $FieldSet = parse_xml_fields($Line);
                                $CurrentWordText = $FieldSet["text"];
				$X1 = $CurrentWordX;
				$X2 = $CurrentWordX1;
				$Y1 = $CurrentWordY;
				$Y2 = $CurrentWordY1;
				$Y2 = $Y2 * $YRatio / $YPixelsPerInch;
				$X2 = $X2 * $XRatio / $XPixelsPerInch;
				$Y1 = $Y1 * $YRatio / $YPixelsPerInch;
				$X1 = $X1 * $XRatio / $XPixelsPerInch;
				$OutData[] = array("word" => $CurrentWordText, "x" => $X1, "y" => $Y1,
					"x1" => $X2, "y1" => $Y2);
                                $CurrentWordText = "";
                                $CurrentWordX = -1;
                                $CurrentWordX1 = -1;
                                $CurrentWordY = -1;
                                $CurrentWordY1 = -1;
                                break;
                        }

                        break;
                }
        }

	return($OutData);
}

function debug_message($Msg)
{
	$Handle = fopen("php://stderr","a+");
	fputs($Handle,$Msg."\n");
	fclose($Handle);
}

function log_debug_message($Msg)
{
	$Handle = fopen("/tmp/make_pdf.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function get_item_image_data($DBHandle,$ItemID,$ImageType = AIB_FILE_CLASS_THUMB)
{
	$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID,$ImageType);
	$ThumbID = -1;
	foreach($FileList as $FileRecord)
	{
		if ($FileRecord["file_class"] == $ImageType)
		{
			$ThumbID = $FileRecord["record_id"];
			break;
		}
	}

	if ($ThumbID < 0)
	{
		return(false);
	}

	return($ThumbID);
}

// Get record; if reference record is a link, get the original record
// ------------------------------------------------------------------
function get_real_record($DBHandle,$InRecord)
{
	if ($InRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
	{
		$SourceType = $InRecord["item_source_type"];
		switch($SourceType)
		{
			case FTREE_SOURCE_TYPE_INTERNAL:
				return(ftree_get_item($DBHandle,$InRecord["item_ref"]));

			default:
				return(false);
		}
	}
	else
	{
		return($InRecord);
	}

	return($InRecord);

}

function get_form()
{
	$FormData = array();
	foreach($_GET as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	return($FormData);
}

// Return "true" if the text buffer represents HTML; if the text starts with "<html>" and
// ends with "</html>", then HTML.
// --------------------------------------------------------------------------------------
function text_is_html($InText)
{
	if (preg_match("/^[\<][Hh][Tt][Mm][Ll][\>]/",$InText) == false)
	{
		return(false);
	}

	if (preg_match("/[\<][\/][Hh][Tt][Mm][Ll][\>]$/",$InText) == false)
	{
		return(false);
	}

	return(true);
}

// Returns HTML from buffer where text is enclosed in <html></html>.
// -----------------------------------------------------------------
function extract_html($InText)
{
	$TempText = preg_replace("/^[\<][Hh][Tt][Mm][Ll][\>]/","",$InText);
	$OutText = preg_replace("/[\<][\/][Hh][Tt][Mm][Ll][\>]$/","",$TempText);
	return($OutText);
}

// Output HTML-based error message
// -------------------------------
function send_html_error($Title,$Message,$ReturnLink)
{
	print("<head><title>$Title</title></head>");
	print("<body>");
	print("<center><h1>$Title</h1></center></title><br>");
	print("<p>$Message</p>");
	if ($ReturnLink != false)
	{
		print("<br><br><center>Click <a href=\"$ReturnLink\">HERE</a> To Continue</center>");
	}

	print("</body></html>");
}

// Output a title path
// -------------------
function generate_title_path($ItemID,$TitlePrefixTemplate,$TitleSuffixTemplate,$TitleSegmentTemplate,$TitleSegmentSepTemplate)
{
	$UseHTML = false;
	if (text_is_html($TitleSegmentTemplate) == true || text_is_html($TitlePrefixTemplate) == true ||
		text_is_html($TitleSuffixTemplate) == true || text_is_html($TitleSegmentSepTemplate) == true)
	{
		$UseHTML = true;
		$LocalSegmentTemplate = extract_html($TitleSegmentTemplate);
		$LocalPrefixTemplate = extract_html($TitlePrefixTemplate);
		$LocalSuffixTemplate = extract_html($TitleSuffixTemplate);
		$LocalSegmentSepTemplate = extract_html($TitleSegmentSepTemplate);
	}
	else
	{
		$LocalSegmentTemplate = $TitleSegmentTemplate;
		$LocalPrefixTemplate = $TitlePrefixTemplate;
		$LocalSuffixTemplate = $TitleSuffixTemplate;
		$LocalSegmentSepTemplate = $TitleSegmentSepTemplate;
	}

	$PageTitleList = ftree_get_item_title_path($GLOBALS["aib_db"],$ItemID);
	$PageTitle = $LocalPrefixTemplate;
	$TitleList = array();
	foreach($PageTitleList as $PageTitleSegment)
	{
		$TitleList[] = preg_replace("/[\[][\[]TITLE[\]][\]]/",urldecode($PageTitleSegment["item_title"]),$LocalSegmentTemplate);
	}

	$PageTitle .= join($LocalSegmentSepTemplate,$TitleList);
	$PageTitle .= $LocalSuffixTemplate;
	return(array($UseHTML,$PageTitle));
}

// #########
// MAIN CODE
// #########


	$FormData = get_form();
	$ReturnLink = get_assoc_default($FormData,"return_link",false);

	aib_open_db();
	// Get API key and session, then validate

	$APIKey = get_assoc_default($FormData,"_key",false);
	$APISession = get_assoc_default($FormData,"_session",false);
	if ($APIKey == false)
	{
		aib_close_db();
		send_html_error("Error Processing Request","Missing key",$ReturnLink);
		exit(0);
	}

	if ($APISession == false)
	{
		aib_close_db();
		send_html_error("Error Processing Request","Missing session",$ReturnLink);
		exit(0);
	}

	$Result = aib_api_validate_session_key($GLOBALS["aib_db"],$APIKey,$APISession,AIB_MAX_API_SESSION);
	if ($Result[0] != "OK")
	{
		aib_close_db();
		send_html_error("Error Processing Request","Cannot validate API key",$ReturnLink);
		exit(0);
	}

	// Get keyholder

	$KeyHolderID = aib_api_get_key_id($GLOBALS["aib_db"],$APIKey);
	if ($KeyHolderID == false)
	{
		aib_close_db();
		send_html_error("Error Processing Request","API keyholder not found",$ReturnLink);
		exit(0);
	}

	// Get user ID of requesting user; required for user account operations

	$RequestUserID = get_assoc_default($FormData,"_user",false);
	if ($RequestUserID === false)
	{
		aib_close_db();
		send_html_error("Error Processing Request","Missing user",$ReturnLink);
		exit(0);
	}

	// Get the user type and information

	$RequestUserRecord = ftree_get_user($GLOBALS["aib_db"],$RequestUserID);
	if ($RequestUserRecord == false)
	{
		aib_close_db();
		send_html_error("Error Processing Request","Bad request user",$ReturnLink);
		exit(0);
	}

	$RequestUserType = $RequestUserRecord["user_type"];
	$RequestUserRoot = $RequestUserRecord["user_top_folder"];


	// Generate a new session

	$NewSession = aib_api_generate_session_key($GLOBALS["aib_db"],$KeyHolderID);
	$OutData = array("status" => "OK", "session" => $NewSession);


	// Get image type

	$ImageType = get_assoc_default($FormData,"image_type",AIB_FILE_CLASS_PRIMARY);

	// Get title info.  Title type may be "none", "item", "record_item_number", "default", or "path".
	// If the title type is "path", then the prefix, segment and suffix templates are used.  If the
	// title segment template isn't empty, it will be used.

	$DocTitle = get_assoc_default($FormData,"doc_title","PDF Generated By ArchiveInABox");
	$DocTitle .= " ".date("m/d/Y H:i:s");
	$TitleType = get_assoc_default($FormData,"title_type","none");
	$DefaultPageTitle = get_assoc_default($FormData,"default_page_title","");
	$TitleSegmentSepTemplate = get_assoc_default($FormData,"page_title_segment_sep_template"," ");
	$TitleSegmentTemplate = get_assoc_default($FormData,"page_title_segment_template"," [[TITLE]] ");
	$TitlePrefixTemplate = get_assoc_default($FormData,"page_title_prefix_template","");
	$TitleSuffixTemplate = get_assoc_default($FormData,"page_title_suffix_template","");
	$OptIncludeRecord = get_assoc_default($FormData,"opt_include_record","N");
	$ItemSummaryHeader = get_assoc_default($FormData,"item_summary_header","");
	$RecordSummaryHeader = get_assoc_default($FormData,"record_summary_header","");

	// Get separator page style

	$SepPageStyle = get_assoc_default($FormData,"sep_page_type","none");
	$SepPageDefaultText = get_assoc_default($FormData,"sep_page_default_text","");

	// Get cover page info

	$CoverPageText = get_assoc_default($FormData,"cover_page_text",false);

	// Get page size

	$PageSize = get_assoc_default($FormData,"page_size","Letter");
	switch($PageSize)
	{
		case "Letter":
			$PageWidth = 8.5;
			$PageHeight = 11.0;
			break;

		case "Legal":
			$PageWidth = 8.5;
			$PageHeight = 14.0;
			break;

		default:
			$PageWidth = 8.5;
			$PageHeight = 11.0;
			break;

	}

	// Get item list

	$ItemListString = get_assoc_default($FormData,"item","");
	$FileList = array();
	$ItemMap = array();
	$ItemList = explode(",",$ItemListString);
	$RecordTags = false;
	$RecordFields = false;
	$RecordTitle = false;
	$RecordPathTitle = false;

	// Process each item

	foreach($ItemList as $ItemID)
	{
		// Get type

		$ItemType = ftree_get_property($GLOBALS["aib_db"],$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($ItemType == false)
		{
			$ItemType = AIB_ITEM_TYPE_ITEM;
		}

		$ItemRecord = ftree_get_item($GLOBALS["aib_db"],$ItemID);
		$ItemMap[$ItemID] = $ItemRecord;

		// If record, get child items

		switch($ItemType)
		{
			// Record

			case AIB_ITEM_TYPE_RECORD:

				// Get list of child items in record, sorted by title.
				$ChildList = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,false,false,true,false);
				if ($ChildList == false)
				{
					$ChildList = array();
				}
	
				// For each entry, get file ID
	
				$ItemNumber = 1;
				foreach($ChildList as $ChildRecord)
				{
					$LocalRecord = get_real_record($GLOBALS["aib_db"],$ChildRecord);
					if ($LocalRecord == false)
					{
						continue;
					}

					$LocalRecord["_item_number"] = $ItemNumber;
					$ItemNumber++;
					$ItemMap[$LocalRecord["item_id"]] = $LocalRecord;
					$FileID = get_item_image_data($GLOBALS["aib_db"],$LocalRecord["item_id"],$ImageType);
					if ($FileID == false)
					{
						continue;
					}
	
					$FileList[] = $FileID;
				}

				// Get tags, if any

				$TagList = aib_get_item_tags($GLOBALS["aib_db"],$ItemID);
				if ($TagList != false)
				{
					$RecordTags = join(",",$TagList);
				}

				// Get record field data

				$RecordFields = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ItemID);
				break;

			// Subgroup

			case AIB_ITEM_TYPE_SUBGROUP:

				// Get list of child items that are not subgroups

				$ChildList = ftree_list_child_objects($GLOBALS["aib_db"],$ItemID,false,false,false,false,true,false);
				if ($ChildList == false)
				{
					$ChildList = array();
				}

				// Process subgroup entries

				$LocalChildList = array();
				foreach($ChildList as $ChildRecord)
				{
					$LocalRecord = get_real_record($GLOBALS["aib_db"],$ChildRecord);
					$ChildType = ftree_get_property($GLOBALS["aib_db"],$LocalRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);

					// If child entry isn't a record, skip

					if ($ChildType != AIB_ITEM_TYPE_RECORD)
					{
						continue;
					}

					// Get list of items in record

					$SubList = ftree_list_child_objects($GLOBALS["aib_db"],$LocalRecord["item_id"],false,false,false,false,true,false);
					if ($SubList == false)
					{
						$SubList = array();
					}

					// Copy to overall list

					$ItemNumber = 1;
					foreach($SubList as $SubRecord)
					{
						$LocalRecord = get_real_record($GLOBALS["aib_db"],$SubRecord);
						$LocalRecord["_item_number"] = $ItemNumber;
						$ItemNumber++;
						$LocalChildList[] = $LocalRecord;
					}
				}

				// For each entry, get file ID
	
				foreach($LocalChildList as $ChildRecord)
				{
					$LocalRecord = get_real_record($GLOBALS["aib_db"],$ChildRecord);
					$ItemMap[$ChildRecord["item_id"]] = $LocalRecord;
					$FileID = get_item_image_data($GLOBALS["aib_db"],$LocalRecord["item_id"],$ImageType);
					if ($FileID == false)
					{
						continue;
					}
	
					$FileList[] = $FileID;
				}

				break;

			// Individual item

			default:
			// Else, get single item

				$FileID = get_item_image_data($GLOBALS["aib_db"],$ItemID,$ImageType);
				if ($FileID != false)
				{
					$FileList[] = $FileID;
				}
				
				$LocalRecord = get_real_record($GLOBALS["aib_db"],$ItemRecord);
				$LocalRecord["_item_number"] = 1;
				$ItemMap[$ItemID] = $LocalRecord;
				break;
		}

	}

	// Get file info for each file

	$FileInfoMap = array();
	foreach($FileList as $FileID)
	{
		$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$FileID);
		$FileInfoMap[$FileID] = $FileInfo;
	}

	// Create PDF object and set parameters

	$PageTitle = "";
	$PDF = new PDF("P","in",$PageSize,$PageTitle,"");
	$DateString = date("M d, Y H:i:s T",time());
	$PDF->SetCreator("Generated By ArchiveInABox, $DateString");
	$PDF->SetTitle($DocTitle);
	$PDF->SetMargins(0.0,0.0,0.0);

	// If there is a cover page, use it

	if ($CoverPageText != "")
	{
		$PDF->AddPage("P",$PageSize);
		if (text_is_html($CoverPageText) == false)
		{
			$PDF->SetFontSize(12);
			$PDF->SetMargins(0.0,0.0,0.0);
			$PDF->SetTextColor(0,0,0);
			$PDF->Write(5,$CoverPageText);
		}
		else
		{
			$PDF->SetFont("Arial","",12);
			$PDF->AddPage("P",$PageSize);
			$PDF->WriteHTML(extract_html($CoverPageText),true);
		}
	}


	// Process each file as a page, where all OCR text and field data is included

	$RecordOutputMap = array();
	$TempFileName = "/tmp/".posix_getpid().".dat";
	system("rm -f $TempFileName");
	$FileNumber = 1;
	foreach($FileList as $FileID)
	{
		$UseHTML = false;
		$FileInfo = $FileInfoMap[$FileID];
		if ($FileInfo == false)
		{
			continue;
		}

		// Get item record

		$FileItemID = $FileInfo["record"]["file_item_id"];
		if (isset($ItemMap[$FileItemID]) == false)
		{
			continue;
		}

		$ItemRecord = $ItemMap[$FileItemID];
		$ItemTitle = urldecode($ItemRecord["item_title"]);
		$ItemType = ftree_get_property($GLOBALS["aib_db"],$ItemRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);

		// Set font

		$PDF->SetFont('Times');

		$OCRTextBuffer = false;
		switch($ItemType)
		{
			case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			case AIB_ITEM_TYPE_ARCHIVE:
			case AIB_ITEM_TYPE_COLLECTION:
			case AIB_ITEM_TYPE_SUBGROUP:
				break;

			default:
				if (isset($RecordOutputMap[$ItemRecord["item_parent"]]) == true)
				{
					break;
				}

				if ($OptIncludeRecord != "Y" && $OptIncludeRecord != "y")
				{
					break;
				}

				$RecordOutputMap[$ItemRecord["item_parent"]] = true;
				$ParentType = ftree_get_property($GLOBALS["aib_db"],$ItemRecord["item_parent"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				if ($ParentType == AIB_ITEM_TYPE_RECORD)
				{
					$TextBuffer = "<html>$RecordSummaryHeader<font size='20' color='#0080'>Record Information</font><font size='10'><br><br>";
					$TitleInfo = generate_title_path($ItemRecord["item_parent"],$TitlePrefixTemplate,$TitleSuffixTemplate,
						$TitleSegmentTemplate,$TitleSegmentSepTemplate);
					$TextBuffer .= $TitleInfo[1]."<br><br>";

					// Output tags, if any

					$TagList = aib_get_item_tags($GLOBALS["aib_db"],$ItemRecord["item_parent"]);
					if ($TagList != false)
					{
						$TextBuffer .= "Tags:<br>".join(",",$TagList)."<br><br>";
					}

					$FieldInfo = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ItemRecord["item_parent"]);
					if ($FieldInfo != false)
					{
						foreach($FieldInfo as $FieldRecord)
						{
							$FieldTitle = urldecode($FieldRecord["def"]["field_title"]);
							$FieldValue = urldecode($FieldRecord["value"]);
							$FieldSymbolicName = ltrim(rtrim($FieldRecord["def"]["field_symbolic_name"]));
							if ($FieldSymbolicName == AIB_PREDEF_FIELD_OCRTEXT)
							{
								$OCRTextBuffer = $FieldValue;
								continue;
							}

							if ($FieldValue == "")
							{
								$FieldValue = "<font color='#808080'>No data</font>";
							}

							$TextBuffer .= "<u>$FieldTitle:</U><br>$FieldValue<br><br>";

						}
					}

					$TextBuffer .= "</font></html>";
					$UseHTML = true;
					$PDF->AddPage("P",$PageSize);
					$PDF->SetMargins(0.0,0.0);
					$PDF->SetTextColor(0,0,0);
					$PDF->SetXY(0,0);
					$PDF->SetFontSize(10);
					if ($UseHTML == true)
					{
						$PDF->WriteHTML(extract_html($TextBuffer),true);
					}
					else
					{
						$PDF->MultiCell(0,0.5,$TextBuffer,0);
					}
				}

				break;
		}



		// Print separator page

		$UseHTML = false;
		$TextBuffer = "";
		switch($SepPageStyle)
		{
			case "none":
				$TextBuffer = false;
				break;

			case "default":
				if (text_is_html($SepPageDefaultText) == true)
				{
					$TextBuffer = extract_html($SepPageDefaultText);
					$UseHTML = true;
				}
				else
				{
					$TextBuffer = $SepPageDefaultText;
				}

				break;

			case "fields_only":
				switch($ItemType)
				{
					case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					case AIB_ITEM_TYPE_ARCHIVE:
					case AIB_ITEM_TYPE_COLLECTION:
					case AIB_ITEM_TYPE_SUBGROUP:
						break;

					default:
						$TextBuffer = "<html>$ItemSummaryHeader<font size='14' color='#0080'>Item Information</font><font size='10'><br><br>";
						$TitleInfo = generate_title_path($ItemRecord["item_id"],$TitlePrefixTemplate,$TitleSuffixTemplate,
							$TitleSegmentTemplate,$TitleSegmentSepTemplate);
						$TextBuffer .= $TitleInfo[1]."<br><br>";

						// Output tags, if any

						$TagList = aib_get_item_tags($GLOBALS["aib_db"],$FileItemID);
						if ($TagList != false)
						{
							$TextBuffer .= "Tags:<br>".join(",",$TagList)."<br><br>";
						}

						$FieldInfo = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$FileItemID);
						if ($FieldInfo != false)
						{
							foreach($FieldInfo as $FieldRecord)
							{
								$FieldTitle = urldecode($FieldRecord["def"]["field_title"]);
								$FieldSymbolicName = ltrim(rtrim($FieldRecord["def"]["field_symbolic_name"]));
								if ($FieldSymbolicName == AIB_PREDEF_FIELD_OCRTEXT)
								{
									$OCRTextBuffer = $FieldValue;
									continue;
								}

								$FieldValue = urldecode($FieldRecord["value"]);
								if ($FieldValue == "")
								{
									$FieldValue = "<font color='#808080'>No data</font>";
								}

								$TextBuffer .= "$FieldTitle:<br>$FieldValue<br><br>";
							}
						}

						$TextBuffer .= "</font></html>";
						$UseHTML = true;
						break;
				}

				break;

			case "ocr_only":
				switch($ItemType)
				{
					case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					case AIB_ITEM_TYPE_ARCHIVE:
					case AIB_ITEM_TYPE_COLLECTION:
					case AIB_ITEM_TYPE_SUBGROUP:
						break;

					default:
						$TextBuffer = "<html>$ItemSummaryHeader<font size='14' color='#0080'>Item Information</font><font size='10'><br><br>";
						$TitleInfo = generate_title_path($ItemRecord["item_id"],$TitlePrefixTemplate,$TitleSuffixTemplate,
							$TitleSegmentTemplate,$TitleSegmentSepTemplate);
						$TextBuffer .= $TitleInfo[1]."<br><br>";
						$FieldInfo = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$FileItemID);
						if ($FieldInfo != false)
						{
							foreach($FieldInfo as $FieldRecord)
							{
								$FieldTitle = urldecode($FieldRecord["def"]["field_title"]);
								$FieldSymbolicName = ltrim(rtrim($FieldRecord["def"]["field_symbolic_name"]));
								if ($FieldSymbolicName != AIB_PREDEF_FIELD_OCRTEXT)
								{
									continue;
								}

								$OCRTextBuffer = $FieldValue;
//								$FieldValue = urldecode($FieldRecord["value"]);
//								if ($FieldValue == "")
//								{
//									$FieldValue = "<font color='#808080'>No data</font>";
//								}
//
//								$TextBuffer .= "$FieldTitle:<br>$FieldValue<br><br>";
								break;
							}
						}

						$TextBuffer .= "</font></html>";
						$UseHTML = true;
						break;
				}

				break;

			case "alltext":
				switch($ItemType)
				{
					case AIB_ITEM_TYPE_ARCHIVE_GROUP:
					case AIB_ITEM_TYPE_ARCHIVE:
					case AIB_ITEM_TYPE_COLLECTION:
					case AIB_ITEM_TYPE_SUBGROUP:
						break;

					default:
						$TextBuffer = "<html>$ItemSummaryHeader<font size='14' color='#0080'>Item Information</font><font size='10'><br><br>";
						$TitleInfo = generate_title_path($ItemRecord["item_id"],$TitlePrefixTemplate,$TitleSuffixTemplate,
							$TitleSegmentTemplate,$TitleSegmentSepTemplate);
						$TextBuffer .= $TitleInfo[1]."<br><br>";

						// Output tags, if any

						$TagList = aib_get_item_tags($GLOBALS["aib_db"],$FileItemID);
						if ($TagList != false)
						{
							$TextBuffer .= "Tags:<br>".join(",",$TagList)."<br><br>";
						}

						$FieldInfo = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$FileItemID);
						if ($FieldInfo != false)
						{
							foreach($FieldInfo as $FieldRecord)
							{
								$FieldTitle = urldecode($FieldRecord["def"]["field_title"]);
								$FieldValue = urldecode($FieldRecord["value"]);
								$FieldSymbolicName = ltrim(rtrim($FieldRecord["def"]["field_symbolic_name"]));
								if ($FieldSymbolicName == AIB_PREDEF_FIELD_OCRTEXT)
								{
									$OCRTextBuffer = $FieldValue;
									continue;
								}

								if ($FieldValue == "")
								{
									$FieldValue = "<font color='#808080'>No data</font>";
								}

								$TextBuffer .= "<u>$FieldTitle:</U><br>$FieldValue<br><br>";

							}
						}

						$TextBuffer .= "</font></html>";
						$UseHTML = true;
						break;
				}

				break;

			default:
				break;
		}

		if ($TextBuffer !== false)
		{
			$TextBuffer = ltrim(rtrim($TextBuffer));
			if ($TextBuffer == "" || $TextBuffer == "<html></html>")
			{
				$TextBuffer = "<html>No image information available for <b>".urldecode($ItemRecord["item_title"])."</b></html>";
				$UseHTML = true;
			}

			$PDF->AddPage("P",$PageSize);
			$PDF->SetMargins(0.0,0.0);
			$PDF->SetTextColor(0,0,0);
			$PDF->SetXY(0,0);
			$PDF->SetFontSize(10);
			if ($UseHTML == true)
			{
				$PDF->WriteHTML(extract_html($TextBuffer),true);
			}
			else
			{
				$PDF->MultiCell(0,0.5,$TextBuffer,0);
			}

		}

		// Add page to PDF for image

		$PDF->AddPage("P",$PageSize);

		// Set page title

		$UseHTML = false;
		switch($TitleType)
		{
			case "none":
				$PageTitle = "";
				break;

			case "item":
				if ($TitleSegmentTemplate == "")
				{
					$PageTitle = $ItemTitle;
				}
				else
				{
					if (text_is_html($TitleSegmentTemplate) == true)
					{
						$UseHTML = true;
						$LocalTemplate = extract_html($TitleSegmentTemplate);
						$PageTitle = preg_replace("/[\[][\[]TITLE[\]][\]]/",$ItemTitle,$LocalTemplate);
					}
					else
					{
						$PageTitle = preg_replace("/[\[][\[]TITLE[\]][\]]/",$ItemTitle,$TitleSegmentTemplate);
					}
				}

				break;

			case "record_item_number":
				$LocalTitle = "Item # ".get_assoc_default($ItemRecord,"_item_number","$FileNumber");
				if ($TitleSegmentTemplate == "")
				{
					$PageTitle = $LocalTitle;
				}
				else
				{
					if (text_is_html($TitleSegmentTemplate) == true)
					{
						$UseHTML = true;
						$LocalTemplate = extract_html($TitleSegmentTemplate);
						$PageTitle = preg_replace("/[\[][\[]TITLE[\]][\]]/",$LocalTitle,$LocalTemplate);
					}
					else
					{
						$PageTitle = preg_replace("/[\[][\[]TITLE[\]][\]]/",$LocalTitle,$TitleSegmentTemplate);
					}
				}

				break;

			// Use the path as the title.  A prefix template for the path is used, followed by each segment within a template,
			// and then followed with a suffix template.

			case "path":
				$TitleInfo = generate_title_path($ItemRecord["item_id"],$TitlePrefixTemplate,$TitleSuffixTemplate,
					$TitleSegmentTemplate,$TitleSegmentSepTemplate);
				$UseHTML = $TitleInfo[0];
				$PageTitle = $TitleInfo[1];
/*
				if (text_is_html($TitleSegmentTemplate) == true || text_is_html($TitlePrefixTemplate) == true ||
					text_is_html($TitleSuffixTemplate) == true || text_is_html($TitleSegmentSepTemplate) == true)
				{
					$UseHTML = true;
					$LocalSegmentTemplate = extract_html($TitleSegmentTemplate);
					$LocalPrefixTemplate = extract_html($TitlePrefixTemplate);
					$LocalSuffixTemplate = extract_html($TitleSuffixTemplate);
					$LocalSegmentSepTemplate = extract_html($TitleSegmentSepTemplate);
				}
				else
				{
					$LocalSegmentTemplate = $TitleSegmentTemplate;
					$LocalPrefixTemplate = $TitlePrefixTemplate;
					$LocalSuffixTemplate = $TitleSuffixTemplate;
					$LocalSegmentSepTemplate = $TitleSegmentSepTemplate;
				}

				$PageTitleList = ftree_get_item_title_path($GLOBALS["aib_db"],$ItemRecord["item_id"]);
				$PageTitle = $LocalPrefixTemplate;
				$TitleList = array();
				foreach($PageTitleList as $PageTitleSegment)
				{
					$TitleList[] = preg_replace("/[\[][\[]TITLE[\]][\]]/",urldecode($PageTitleSegment["item_title"]),$LocalSegmentTemplate);
				}

				$PageTitle .= join($LocalSegmentSepTemplate,$TitleList);
				$PageTitle .= $LocalSuffixTemplate;
*/
				break;

			default:
				$PageTitle = $DefaultPageTitle;
				if (text_is_html($TitleSegmentTemplate) == true)
				{
					$UseHTML = true;
					$LocalTemplate = extract_html($TitleSegmentTemplate);
					$PageTitle = preg_replace("/[\[][\[]TITLE[\]][\]]/",$PageTitle,$LocalTemplate);
				}

				break;

		}

		$FileNumber++;
		if ($TitleType != "none")
		{
			if ($UseHTML == false)
			{
				$PDF->SetFontSize(10);
				$PDF->SetTextColor(0,0,0);
				$PDF->MultiCell(0.0, 0.5, $PageTitle, 0, 0);
			}
			else
			{
				$PDF->SetFontSize(10);
				$PDF->SetTextColor(0,0,0);
				$PDF->WriteHTML($PageTitle,true);
			}
		}

		$UseHTML = false;

		// Place text from fields and OCR text.  Field names are modified such that
		// punctuation and whitespace are converted to underscores.

		$TextBuffer = "";
		$FieldInfo = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$FileItemID);
		if ($FieldInfo != false)
		{
			foreach($FieldInfo as $FieldRecord)
			{
				$FieldTitle = urldecode($FieldRecord["def"]["field_title"]);
				$FieldSymbolicName = ltrim(rtrim($FieldRecord["def"]["field_symbolic_name"]));
				if ($FieldSymbolicName == AIB_PREDEF_FIELD_OCRTEXT)
				{
					continue;
				}

				$FieldValue = urldecode($FieldRecord["value"]);
				if ($FieldSymbolicName != "")
				{
					$TextBuffer .= "$FieldTitle ($FieldSymbolicName):\n$FieldValue\n";
				}
				else
				{
					$TextBuffer .= "$FieldTitle:\n$FieldValue\n";
				}

			}
		}

		if ($OCRTextBuffer != false)
		{
			$OCRTextBuffer = rawurldecode($OCRTextBuffer);
			$OCRTextBuffer = urldecode($OCRTextBuffer);
			$OCRTextBuffer = ltrim(rtrim($OCRTextBuffer));
			$OCRTextBuffer = preg_replace("/[ \t\n]+/"," ",$OCRTextBuffer);
			$TextBuffer .= $OCRTextBuffer;
		}

		if ($TextBuffer != "")
		{
			$PDF->SetFontSize(2);
			$PDF->SetMargins(0.0,0.0,0.0);
			$PDF->SetTextColor(255,255,255);
			$PDF->MultiCell(0,0.25,$TextBuffer);
		}

		// Determine if the file can be added as an image

		if (preg_match("/image[\/]/",$FileInfo["mime"]) != false)
		{
			$LocalSubname = microtime(true);
			$LocalSubname = preg_replace("/[\.]/","_",$LocalSubname);
			$TempFileName = "/tmp/".posix_getpid()."_".$LocalSubname;
			$MIMEType = urldecode($FileInfo["mime"]);
			while(true)
			{
				if (preg_match("/[Jj][Pp][Gg]/",$MIMEType) != false || preg_match("/[Jj][Pp][Ee][Gg]/",$MIMEType) != false)
				{
					$TempFileName .= ".jpg";
					break;
				}

				if (preg_match("/[Tt][Ii][Ff]/",$MIMEType) != false)
				{
					$TempFileName .= ".tif";
					break;
				}

				if (preg_match("/[Gg][Ii][Ff]/",$MIMEType) != false)
				{
					$TempFileName .= ".gif";
					break;
				}

				$TempFileName .= ".dat";
				break;
			}

			system("rm -f $TempFileName");
			$FileInfo["name"] = urldecode($FileInfo["name"]);
			aib_fetch_file($GLOBALS["aib_db"],$FileInfo,"file://".$TempFileName);
			if (file_exists($TempFileName) == true)
			{
				// Create ImageMagic image from temporary file

				$ImageObject = new Imagick($TempFileName);

				// Get image width and height

				$XSize = $ImageObject->getImageWidth();
				$YSize = $ImageObject->getImageHeight();

				// Get rid of the image object now that we've gotten the statistics

				unset($ImageObject);

				// Place image on PDF page

				if ($TitleType != "none")
				{
//					$XDensity = ($XSize / $PageWidth) * -1.1;
//					$YDensity = ($YSize / $PageHeight) * -1.1;

					// Calculate aspect ratio and adjust image.  IMPORTANT:  Use absolute value of XDensity.

//					$MaxHeight = ($PageHeight * 0.8) * abs($XDensity);
//					$YDensity = $XDensity;
//					while($YSize / abs($YDensity) >= $MaxHeight)
//					{
//						// IMPORTANT: Value is negative, so decreasing this will INCREASE the absolute value.
//
//						$YDensity--;
//
//						// Note that the XDensity is decreased also to maintain aspect ratio
//
//						$XDensity--;
//					}
//
//					$PDF->Image($TempFileName,0.5,0.5,$XDensity,$YDensity);
					$XDensity = ($XSize / $PageWidth) * 1.2;
					$YDensity = ($YSize / $PageHeight) * 1.2;
					if ($YDensity > $XDensity)
					{
						$XDensity = $YDensity;
					}
					else
					{
						if ($XDensity > $YDensity)
						{
							$YDensity = $XDensity;
						}
					}

					$PDF->Image($TempFileName,0.5,0.5,$XDensity * -1.0,$YDensity * -1.0);

				}
				else
				{
//					$XDensity = ($XSize / $PageWidth) * -1.01;
//					$YDensity = ($YSize / $PageHeight) * -1.01;

					// Calculate aspect ratio and adjust image.  IMPORTANT: Use absolute value of XDensity.

//					$MaxHeight = ($PageHeight * 0.8) * abs($XDensity);
//					$YDensity = $XDensity;
//					while($YSize / abs($YDensity) >= $MaxHeight)
//					{
//						// IMPORTANT: Value is negative, so decreasing this will INCREASE the absolute value
//						// and therefore decrease the vertical size of the image
//
//						$YDensity--;
//
//						// Note that the XDensity is decreased also to maintain aspect ratio
//
//						$XDensity--;
//					}

//					$PDF->Image($TempFileName,0.0,0.0,$XDensity,$YDensity);
//					if ($XSize > $YSize)
//					{
//						$PDF->Image($TempFileName,0.0,0.0,$XDensity,0);
//					}
//					else
//					{
//						$PDF->Image($TempFileName,0.0,0.0,0,$YDensity);
//					}
					$XDensity = ($XSize / $PageWidth) * 1.2;
					$YDensity = ($YSize / $PageHeight) * 1.2;
					if ($YDensity > $XDensity)
					{
						$XDensity = $YDensity;
					}
					else
					{
						if ($XDensity > $YDensity)
						{
							$YDensity = $XDensity;
						}
					}

					$PDF->Image($TempFileName,0.0,0.0,$XDensity * -1.0,$YDensity * -1.0);

				}

			}

			// Remove temporary image file

			system("rm -f $TempFileName");
		}
	}

	// Generate PDF and send to STDOUT

	$OutBuffer = $PDF->Output("S");
//	header("Content-type: application-x/pdf");
//	header("Content-length: ".strlen($OutBuffer));
	print("");
	print($OutBuffer);
	exit(0);
?>
