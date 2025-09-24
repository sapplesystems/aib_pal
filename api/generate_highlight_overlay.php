<?php
//
// Generate highlight overlay image
//

include("api_util.php");

// Log debug
// ---------
function local_log_debug($Msg)
{
	$Handle = fopen("/tmp/highlight_debug.txt","a+");
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

// Given a user top folder, make sure the item ID is a child or equal to the top folder.
// -------------------------------------------------------------------------------------
function verify_user_item_access($DBHandle,$ItemID,$TopFolderID,$UserID = false)
{
	if ($UserID !== false)
	{
		if ($UserID == AIB_SUPERUSER)
		{
			return(true);
		}
	}

	// Get ID path to child item

	$IDPath = ftree_get_item_id_path($DBHandle,$ItemID);
	if ($IDPath == false)
	{
		return(false);
	}

	// If the child item is above the top folder, then no access

	$FoundTop = false;
	$FoundID = false;
	foreach($IDPath as $EntryID)
	{
		// Test for top first; if we've found it then we are at or before
		// the child item.  Critical that this test be performed BEFORE
		// testing for child

		if ($EntryID == $TopFolderID)
		{
			$FoundTop = $EntryID;
			break;
		}

		// If we find the child item, we're above the top.  If
		// the child item is the same as the top, we'll exit
		// at the comparison above.

		if ($EntryID == $ItemID)
		{
			$FoundID = $EntryID;
			break;
		}
	}

	if ($FoundTop !== false)
	{
		return(true);
	}

	return(false);
}

// Get file info and path
// ----------------------
function get_item_file_info($DBHandle,$ItemID,$ImageType = AIB_FILE_CLASS_TEXT_LOCATION)
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

	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
	if ($FileInfo == false)
	{
		return(false);
	}

	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	$SourceName = urldecode($SourceName);
	$SourceMIME = $FileInfo["mime"];
	if (file_exists($SourceName) == false)
	{
		return(false);
	}

	$OutData = array(
		"filename" => $SourceName,
		"mime" => $SourceMIME,
		"data" => $Buffer,
		"id" => $ThumbID
		);
	return($OutData);
}


// Get file and path
// -----------------
function get_item_file($DBHandle,$ItemID,$ImageType = AIB_FILE_CLASS_TEXT_LOCATION)
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

	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
	if ($FileInfo == false)
	{
		return(false);
	}

	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	$SourceName = urldecode($SourceName);
	$SourceMIME = $FileInfo["mime"];
	if (file_exists($SourceName) == false)
	{
		return(false);
	}

	$Buffer = file_get_contents($SourceName);
	if ($Buffer == false)
	{
		return(false);
	}

	$OutData = array(
		"mime" => $SourceMIME,
		"data" => $Buffer,
		"id" => $ThumbID
		);
	unset($Buffer);
	return($OutData);
}



// Get image
// ---------

function get_item_image_data($DBHandle,$ItemID,$ImageType = AIB_FILE_CLASS_THUMB)
{
	$FileList = aib_get_files_for_item($GLOBALS["aib_db"],$ItemID,$ImageType);
	$ThumbID = -1;
	foreach($FileList as $FileRecord)
	{
		if ($FileRecord["file_class"] == AIB_FILE_CLASS_THUMB)
		{
			$ThumbID = $FileRecord["record_id"];
			break;
		}
	}

	if ($ThumbID < 0)
	{
		return(false);
	}

	$FileInfo = aib_get_file_info($GLOBALS["aib_db"],$ThumbID);
	if ($FileInfo == false)
	{
		return(false);
	}

	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	$SourceName = urldecode($SourceName);
	$SourceMIME = $FileInfo["mime"];
	if (file_exists($SourceName) == false)
	{
		return(false);
	}

	$Buffer = file_get_contents($SourceName);
	if ($Buffer == false)
	{
		return(false);
	}

	$OutData = array(
		"mime" => $SourceMIME,
		"data" => $Buffer,
		"id" => $ThumbID
		);
	unset($Buffer);
	return($OutData);
}

// Load and parse text location file
// ---------------------------------

function load_location_file($Buffer,$FormatName)
{
        $PageInfo = array("format" => "none");
        $WordInfo = array();
	$AltWordInfo = array();
        $WordMap = array();
        $Mode = "top";
        $CurrentWordX = -1;
        $CurrentWordY = -1;
        $CurrentWordX1 = -1;
        $CurrentWordY1 = -1;
        $CurrentWordText = "";
	$AlternateWordText = "";
	$LineCounter = 0;
	$WordNumber = 0;
	switch($FormatName)
	{
		case "rlc":
                        $Lines = explode("\n",$Buffer);
			$TopLine = false;
			while(true)
			{
				if (count($Lines) < 1)
				{
					break;
				}

                        	$TopLine = array_shift($Lines);
				if (preg_match("/^[0-9]+/",$TopLine) != false)
				{
					break;
				}
			}

			if ($TopLine == false)
			{
			        return(array($PageInfo,$WordMap,$WordInfo));
			}

                        $Fields = preg_split("/[ \t]+/",$TopLine);
                        $PageInfo["format"] = "rlc";
                        $PageInfo["width"] = $Fields[0];
                        $PageInfo["height"] = $Fields[1];
                        $PageInfo["skew_height"] = $Fields[2];
                        $PageInfo["angle"] = $Fields[3];
                        $PageInfo["xshift"] = $Fields[4];
                        $PageInfo["xedge"] = $Fields[5];
                        $PageInfo["yshift"] = $Fields[6];
                        foreach($Lines as $Line)
                        {
				if (ltrim(rtrim($Line)) == "")
				{
					$LineCounter++;
					continue;
				}

                                $Fields = preg_split("/[ \t]+/",$Line);
                                switch(strtoupper($Fields[0]))
                                {
                                        // Alternate character.  Fields are:
                                        // X1, Y1, X2, Y2, Flags, Character, Confidence rating
                                        case "A":
                                                $Mode = "W";
                                                break;

                                        // Word.  Fields are:
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
                                                                        $CurrentWordText = strtolower($CurrentWordText);
									$CurrentWordText = preg_replace("/^[^\-0-9A-Za-z]/","",$CurrentWordText);
									$CurrentWordText = preg_replace("/[^0-9A-Za-z]+$/","",$CurrentWordText);
                                                                        $AlternateWordText = strtolower($AlternateWordText);
									$AlternateWordText = preg_replace("/^[^\-0-9A-Za-z]/","",$AlternateWordText);
									$AlternateWordText = preg_replace("/[^0-9A-Za-z]+$/","",$AlternateWordText);
									$LocalArray = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                                               "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1, "word_number" => $WordNumber);
                                                                        $WordInfo[] = $LocalArray;
                                                                        if (isset($WordMap[$CurrentWordText]) == false)
                                                                        {
                                                                                $WordMap[$CurrentWordText] = array();
                                                                        }

									if (isset($WordMap[$AlternateWordText]) == false)
									{
										$WordMap[$AlternateWordText] = array();
									}

                                                                        $WordMap[$CurrentWordText][] = $LocalArray;
//                                                                        $WordMap[$CurrentWordText][] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
//                                                                                "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1, "word_number" => $WordNumber);
									if ($AlternateWordText != $CurrentWordText)
									{
                                                                        	$WordMap[$AlternateWordText][] = array("text" => $AlternateWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                                                	"X1" => $CurrentWordX1, "Y1" => $CurrentWordY1, "word_number" => $WordNumber);
									}

									$LocalArray["text"] = $AlternateWordText;
									$AltWordInfo[] = $LocalArray;

                                                                        $CurrentWordText = "";
									$AlternateWordText = "";
                                                                        $CurrentWordX = -1;
                                                                        $CurrentWordX1 = -1;
                                                                        $CurrentWordY = -1;
                                                                        $CurrentWordY1 = -1;
									$WordNumber++;
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

				$LineCounter++;
                        }

                        if ($CurrentWordText != "")
                        {
                                $CurrentWordText = strtolower($CurrentWordText);
				$CurrentWordText = preg_replace("/^[^\-0-9A-Za-z]/","",$CurrentWordText);
				$CurrentWordText = preg_replace("/[^0-9A-Za-z]+$/","",$CurrentWordText);
                                $AlternateWordText = strtolower($AlternateWordText);
				$AlternateWordText = preg_replace("/^[^\-0-9A-Za-z]/","",$AlternateWordText);
				$AlternateWordText = preg_replace("/[^0-9A-Za-z]+$/","",$AlternateWordText);
                                $WordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                if (isset($WordMap[$CurrentWordText]) == false)
                                {
                                        $WordMap[$CurrentWordText] = array();
                                }

                                if (isset($WordMap[$AlternateWordText]) == false)
                                {
                                        $WordMap[$AlternateWordText] = array();
                                }

                                $WordMap[$CurrentWordText][] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1, "word_number" => $WordNumber);
				if ($CurrentWordText != $AlternateWordText)
				{
                                	$WordMap[$CurrentWordText][] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                        	"X1" => $CurrentWordX1, "Y1" => $CurrentWordY1, "word_number" => $WordNumber);
				}

				$LocalArray["text"] = $AlternateWordText;
				$AltWordInfo[] = $LocalArray;
                        }

                        break;

		case "eim":
                        $Lines = explode("\n",$Buffer);
                        $TopLine = array_shift($Lines);
                        $Fields = preg_split("/[ \t]+/",$TopLine);
                        $PageInfo["format"] = "eim";
                        $PageInfo["width"] = $Fields[2];
                        $PageInfo["height"] = $Fields[3];
                        $PageInfo["xdpi"] = $Fields[4];
                        $PageInfo["ydpi"] = $Fields[5];
                        preg_match("/[\"][^\"]+[\"]/",$TopLine,$MatchSet);
                        if (isset($MatchSet[0]) == true)
                        {
                                $PageInfo["source_file"] = preg_replace("/[\"]/","",$MatchSet[0]);
                        }
                        else
                        {
                                $PageInfo["source_file"] = "NULL";
                        }

                        if (isset($MatchSet[1]) == true)
                        {
                                $PageInfo["batch_name"] = preg_replace("/[\"]/","",$MatchSet[1]);
                        }
                        else
                        {
                                $PageInfo["batch_name"] = "NULL";
                        }

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
                                                                        $CurrentWordText = strtolower($CurrentWordText);
                                                                        $WordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                                                "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                                                        $AltWordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                                                "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                                                        if (isset($WordMap[$CurrentWordText]) == false)
                                                                        {
                                                                                $WordMap[$CurrentWordText] = array();
                                                                        }

                                                                        $WordMap[$CurrentWordText][] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                                                "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
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
                                $CurrentWordText = strtolower($CurrentWordText);
                                $WordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                $AltWordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                if (isset($WordMap[$CurrentWordText]) == false)
                                {
                                        $WordMap[$CurrentWordText] = array();
                                }

                                $WordMap[$CurrentWordText][] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                        }

                        break;
                
		case "xml":
                        $Lines = explode("\n",$Buffer);
                        $TopLine = array_shift($Lines);
                        $Fields = preg_split("/[ \t]+/",$TopLine);
                        $PageInfo["format"] = "xml";
                        $PageInfo["width"] = $Fields[2];
                        $PageInfo["height"] = $Fields[3];
                        $PageInfo["xdpi"] = $Fields[4];
                        $PageInfo["ydpi"] = $Fields[5];
                        preg_match("/[\"][^\"]+[\"]/",$TopLine,$MatchSet);
                        if (isset($MatchSet[0]) == true)
                        {
                                $PageInfo["source_file"] = preg_replace("/[\"]/","",$MatchSet[0]);
                        }
                        else
                        {
                                $PageInfo["source_file"] = "NULL";
                        }

                        if (isset($MatchSet[1]) == true)
                        {
                                $PageInfo["batch_name"] = preg_replace("/[\"]/","",$MatchSet[1]);
                        }
                        else
                        {
                                $PageInfo["batch_name"] = "NULL";
                        }

                        $Mode = "X";
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
                                                $CurrentWordText = strtolower($FieldSet["text"]);
                                                $WordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                                $AltWordInfo[] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
                                                if (isset($WordMap[$CurrentWordText]) == false)
                                                {
                                                        $WordMap[$CurrentWordText] = array();
                                                }

                                                $WordMap[$CurrentWordText][] = array("text" => $CurrentWordText, "X" => $CurrentWordX, "Y" => $CurrentWordY,
                                                        "X1" => $CurrentWordX1, "Y1" => $CurrentWordY1);
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

                        break;

		default:
			return(false);
	}

        return(array($PageInfo,$WordMap,$WordInfo,$AltWordInfo));
}

// Given a word rectangle and page info, calculate actual rectangle
// ----------------------------------------------------------------
function calculate_word_rectangles($PageInfo,$WordMap,$WordText,$IsPhraseWord,$WordList,$PhraseSet,$AltWordList)
{
        $LocalWord = ltrim(rtrim(strtolower($WordText)));
        if (isset($WordMap[$LocalWord]) == false)
        {
                return(array());
        }

        $Format =$PageInfo["format"];
        switch($Format)
        {
                case "rlc":
                        $Width =$PageInfo["width"];
                        $Height = $PageInfo["height"];
                        $SkewHeight =$PageInfo["skew_height"];
                        $SkewAngle =$PageInfo["angle"];
                        $XSkew =$PageInfo["xshift"];
                        $XEdge = $PageInfo["xedge"];
                        $YSkew =$PageInfo["yshift"];
                        $AngleValue = 0.0;
                        if ($SkewHeight != 0)
                        {
                                $AngleValue = tan($SkewAngle / 1024.0);
                        }

                        foreach($WordMap[$LocalWord] as $WordRecord)
                        {
                                $WordX = $WordRecord["X"];
                                $WordY = $WordRecord["Y"];
                                $WordX1 = $WordRecord["X1"];
                                $WordY1 = $WordRecord["Y1"];
				$PhraseWordFlag = 0;
				if ($IsPhraseWord == 1)
				{
					$PhraseWordFlag = 1;
				}

				if (isset($WordRecord["word_number"]) == true)
				{
					$WordNumber = $WordRecord["word_number"];
				}
				else
				{
					$WordNumber = -2;
				}

				if (isset($WordList[$WordNumber - 1]) == true)
				{
					$LeftWordRecord = $WordList[$WordNumber - 1];
					$AltLeftWordRecord = $AltWordList[$WordNumber - 1];
				}
				else
				{
					$LeftWordRecord = false;
				}

				if (isset($WordList[$WordNumber + 1]) == true)
				{
					$RightWordRecord = $WordList[$WordNumber + 1];
					$AltRightWordRecord = $AltWordList[$WordNumber + 1];
				}
				else
				{
					$RightWordRecord = false;
				}

				$LeftPhraseFlag = 0;
				$RightPhraseFlag = 0;
				$CalcPhraseFlag = 0;

				// If this is a phrase flag, see if we need a full or partial highlight

				if ($PhraseWordFlag == 1)
				{
					if ($LeftWordRecord != false)
					{
						if (isset($PhraseSet[$LeftWordRecord["text"]]) == true || isset($PhraseSet[$AltLeftWordRecord["text"]]) == true)
						{
							$LeftPhraseFlag = 1;
						}
					}
	
					if ($RightWordRecord != false)
					{
						if (isset($PhraseSet[$RightWordRecord["text"]]) == true || isset($PhraseSet[$AltRightWordRecord["text"]]) == true)
						{
							$RightPhraseFlag = 1;
						}
					}
	
					if ($LeftPhraseFlag == 1 || $RightPhraseFlag == 1)
					{
						$CalcPhraseFlag = 2;
					}
					else
					{
						$CalcPhraseFlag = 1;
					}
				}
				else
				{
					$CalcPhraseFlag = 0;
				}


// ----------------------------------------------------------------------------------------------------
// Original placement calculation code for highlighting
//
//                                if ($SkewAngle != 0.0)
//                                {
//                                        $SkewX = 0;
//                                        $SkewY = 0;
//                                        $SkewX1 = 0;
//                                        $SkewY1 = 0;
//                                        if ($SkewAngle > 0)
//                                        {
//                                                $SkewX = intval(($WordX - $XSkew) + ($WordY + $YSkew) * $AngleValue + ($XEdge * 8));
//                                                $SkewY = intval(($WordY + $YSkew) + ($WordX + $XSkew) * $AngleValue);
//                                                $SkewX1 = intval(($WordX1 - $XSkew) + ($WordY1 + $YSkew) * $AngleValue + ($XEdge * 8));
//                                                $SkewY1 = intval(($WordY1 + $YSkew) + ($WordX1 + $XSkew) * $AngleValue);
//                                        }
//                                        else
//                                        {
//                                                $SkewX = intval(($WordX + $XSkew) + ($Height - $YSkew) * abs($AngleValue) - ($XEdge * 8));
//                                                $SkewY = intval(($WordY + $YSkew) + ($WordX - $XSkew) * $AngleValue);
//                                                $SkewX1 = intval(($WordX1 + $XSkew) + ($Height - $YSkew) * abs($AngleValue) - ($XEdge * 8));
//                                                $SkewY = intval(($WordY1 + $YSkew) + ($WordX1 - $XSkew) * $AngleValue);
//                                        }
//
//                                        $WordX = $SkewX;
//                                        $WordY = $SkewY;
//                                        $WordX1 = $SkewX1;
//                                        $WordY1 = $SkewY1;
//
//                                }
// ----------------------------------------------------------------------------------------------------

                                $RectangleList[] = array($WordX,$WordY,$WordX1,$WordY1,$CalcPhraseFlag);
                        }

                        break;

                // EIM and XML are treated the same (no skew or offset data available)

                case "eim":
                case "xml":
                        foreach($WordMap[$LocalWord] as $WordRecord)
                        {
                                $WordX = $WordRecord["X"];
                                $WordY = $WordRecord["Y"];
                                $WordX1 = $WordRecord["X1"];
                                $WordY1 = $WordRecord["Y1"];
				$PhraseWordFlag = 0;
				if ($IsPhraseWord == 1)
				{
					$PhraseWordFlag = 1;
				}

                                $RectangleList[] = array($WordX,$WordY,$WordX1,$WordY1,$PhraseWordFlag);
                        }

                        break;

                default:
                        break;
        }

        return($RectangleList);
}

function generate_overlay_set($LocationData,$Tags,$DiskImageWidth,$DiskImageHeight,$SourceImageWidth,$SourceImageHeight,$DisplayWidth,$DisplayHeight)
{
	// Determine display coordinate rendering ratio

	$OutWidthRatio = $DisplayWidth / $SourceImageWidth;
	$OutHeightRatio = $DisplayHeight / $SourceImageHeight;

	// Process tags, if present

	$OutRectangles = array();
	$SourceImageWidth = 0;
	$SourceImageHeight = 0;
	$HighlightedWordCount = 0;
	$OverlayCount = 0;
	$XCoordListString = "";
	$YCoordListString = "";
	$WidthListString = "";
	$HeightListString = "";
	$RLC_Width = 0;
	$RLC_Height = 0;
	$RLC_SkewHeight = 0;
	$RLC_SkewAngle = 0;
	$RLC_XSkew = 0;
	$RLC_XEdge = 0;
	$RLC_YSkew = 0;
	$PhraseListString = "";
	$RectangleFormat = "none";
	$ColorValueString = "#ffff00";
	$PhraseColorValueString = "#ff9900";
	$BorderColorString = "red";
	$PhraseBorderColorString = "red";
	$OpacityString = "0.3";
	$PhraseOpacityString = "0.3";
	$PhrasesUsedFlagString = "N";
	$PhraseSet = array();
	if ($Tags != "")
	{
		$WordList = array();
		$PhraseFlagList = array();
		$TempWordList = explode("|",$Tags);
		foreach($TempWordList as $TempWord)
		{
			$LocalWord = urldecode($TempWord);
			$LocalWordList = preg_split("/[ \t\n\t\,]+/",$LocalWord);
			if (count($LocalWordList) > 1)
			{
				foreach($LocalWordList as $LocalWord)
				{
					$WordList[] = $LocalWord;
					$PhraseFlagList[] = 1;
					$PhraseSet[$LocalWord] = true;
				}
			}
			else
			{
				$WordList[] = $LocalWord;
				$PhraseFlagList[] = 0;
			}
		}

		$PhrasesUsedFlag = false;
		$HighlightedWordCount = count($WordList);
		$LocationPageInfo = $LocationData[0];
		$WordMap = $LocationData[1];
		$WordInfo = $LocationData[2];
		$AltWordInfo = $LocationData[3];
		if (isset($LocationPageInfo["width"]) == false)
		{
			$LocationPageInfo["width"] = 1800;
		}

		if (isset($LocationPageInfo["height"]) == false)
		{
			$LocationPageInfo["height"] = 3600;
		}

		if (isset($LocationPageInfo["skew_height"]) == false)
		{
			$LocationPageInfo["skew_height"] = 0;
		}

		if (isset($LocationPageInfo["skew_width"]) == false)
		{
			$LocationPageInfo["skew_width"] = 0;
		}

		if (isset($LocationPageInfo["angle"]) == false)
		{
			$LocationPageInfo["angle"] = 0;
		}

		if (isset($LocationPageInfo["xshift"]) == false)
		{
			$LocationPageInfo["xshift"] = 0;
		}

		if (isset($LocationPageInfo["xedge"]) == false)
		{
			$LocationPageInfo["xedge"] = 0;
		}

		if (isset($LocationPageInfo["yshift"]) == false)
		{
			$LocationPageInfo["yshift"] = 0;
		}

//		$SourceImageWidth = $LocationPageInfo["width"];
//		$SourceImageHeight = $LocationPageInfo["height"];
		$XRatio = 1.0;
		$YRatio = 1.0;
		$YCoordList = array();
		$XCoordList = array();
		$WidthList = array();
		$HeightList = array();
		$PhraseList = array();
		$RectangleFormat = $LocationPageInfo["format"];
                $RLC_Width =$LocationPageInfo["width"];
                $RLC_Height = $LocationPageInfo["height"];
                $RLC_SkewHeight =$LocationPageInfo["skew_height"];
                $RLC_SkewAngle =$LocationPageInfo["angle"];
                $RLC_XSkew =$LocationPageInfo["xshift"];
                $RLC_XEdge = $LocationPageInfo["xedge"];
                $RLC_YSkew =$LocationPageInfo["yshift"];
		$WordCounter = 0;
//		$SourceImageXRatio = $DiskImageWidth / $SourceImageWidth;
//		$SourceImageYRatio = $DiskImageHeight / $SourceImageHeight;
//		$WidthRatio = $SourceImageWidth / $DiskImageWidth;
//		$HeightRatio = $SourceImageHeight / $DiskImageHeight;
//		$ScaleHeight = $SourceImageHeight * $SourceImageYRatio * $HeightRatio;
		$ScaleHeight = $SourceImageHeight * $OutWidthRatio;
		$RLC_AngleValue = tan($RLC_SkewAngle / 1024.0);
//		$RLC_XSkew = $RLC_XSkew * $SourceImageXRatio * $WidthRatio;
//		$RLC_XEdge = $RLC_XEdge * $SourceImageXRatio * $WidthRatio;
//		$RLC_YSkew = $RLC_YSkew * $SourceImageYRatio * $HeightRatio;
		$RLC_XSkew = $RLC_XSkew * $OutWidthRatio;
		$RLC_XEdge = $RLC_XEdge * $OutWidthRatio;
		$RLC_YSkew = $RLC_YSkew * $OutHeightRatio;

// DEBUG
		foreach($WordList as $Word)
		{
        		// Get list of rectangles where word occurs on page

		        $RectangleList = calculate_word_rectangles($LocationPageInfo,$WordMap,$Word,$PhraseFlagList[$WordCounter],$WordInfo,$PhraseSet,$AltWordInfo);
			$WordCounter++;

        		// Calculate highlight boxes

	        	foreach($RectangleList as $Rectangle)
	        	{
				$PosX = $Rectangle[0] * $XRatio;
				$PosY = $Rectangle[1] * $YRatio;
				$PosX1 = $Rectangle[2] * $XRatio;
				$PosY1 = $Rectangle[3] * $XRatio;
				$PhraseFlag = $Rectangle[4];
				$SizeX = $PosX1 - $PosX;
				$SizeY = $PosY1 - $PosY;

//				$PosX = $PosX * $SourceImageXRatio * $WidthRatio;
//				$PosY = $PosY * $SourceImageYRatio * $HeightRatio;
//				$PosX1 = $PosX1 * $SourceImageXRatio * $WidthRatio;
//				$PosY1 = $PosY1 * $SourceImageYRatio * $HeightRatio;
				$PosX = $PosX * $OutWidthRatio;
				$PosY = $PosY * $OutHeightRatio;
				$PosX1 = $PosX1 * $OutWidthRatio;
				$PosY1 = $PosY1 * $OutHeightRatio;
				if ($RLC_SkewAngle != 0.0)
				{
					if ($RLC_SkewAngle > 0.0)
					{
						// Positive skew angle

						$PosX = ($PosX - $RLC_XSkew) + ($PosY + $RLC_YSkew) * $RLC_AngleValue + ($RLC_XEdge * 8.0);
						$PosY = ($PosY + $RLC_YSkew) + ($PosX + $RLC_XSkew) * $RLC_AngleValue;
						$PosX1 = ($PosX1 - $RLC_XSkew) + ($PosY + $RLC_YSkew) * $RLC_AngleValue + ($RLC_XEdge * 8.0);
						$PosY1 = ($PosY1 + $RLC_YSkew) + ($PosX1 + $RLC_XSkew) * $RLC_AngleValue;
					}
					else
					{
						// Negative skew angle

						$PosX = ($PosX + $RLC_XSkew) - ($PosY - $RLC_YSkew) * $RLC_AngleValue - ($RLC_XEdge * 8.0);
						$PosY = ($PosY + $RLC_YSkew) + ($PosX - $RLC_XSkew) * $RLC_AngleValue;
						$PosX1 = ($PosX1 + $RLC_XSkew) - ($PosY - $RLC_YSkew) * $RLC_AngleValue - ($RLC_XEdge * 8.0);
						$PosY1 = ($PosY1 + $RLC_YSkew) + ($PosX1 - $RLC_XSkew) * $RLC_AngleValue;
					}
				}

				$SizeX = $PosX1 - $PosX;
				$SizeY = $PosY1 - $PosY;

				// Expand the size slightly so we can put a box around the word

				$SizeX = $SizeX * 1.05;
				$SizeY = $SizeY * 1.05;

				$OutRectangles[] = array(
					"x" => sprintf("%d",round($PosX,0,PHP_ROUND_HALF_DOWN)),
					"y" => sprintf("%d",round($PosY,0,PHP_ROUND_HALF_DOWN)),
					"x1" => sprintf("%d",round($PosX1,0,PHP_ROUND_HALF_UP)),
					"y1" => sprintf("%d",round($PosY1,0,PHP_ROUND_HALF_UP)),
					"w" => sprintf("%d",round($SizeX,0,PHP_ROUND_HALF_UP)),
					"h" => sprintf("%d",round($SizeY,0,PHP_ROUND_HALF_UP)),
					"p" => $PhraseFlag,
					);
        		}
		}

		if ($PhrasesUsedFlag == false)
		{
//			$ColorValueString = "#ffff00";
//			$PhraseColorValueString = "#ff9900";
//			$BorderColorString = "red";
//			$PhraseBorderColorString = "red";
//			$OpacityString = "0.3";
//			$PhraseOpacityString = "0.3";
			$PhrasesUsedFlagString = "N";
		}
		else
		{
//			$ColorValueString = "#0000ff";
//			$PhraseColorValueString = "#ffff00";
//			$BorderColorString = "#ff9900";
//			$PhraseBorderColorString = "red";
//			$OpacityString = "0.1";
//			$PhraseOpacityString = "0.3";
			$PhrasesUsedFlagString = "Y";
		}

	}

	$OutData = array(
		"rect" => $OutRectangles,
		"phrases" => $PhraseList,
//		"color_value_string" => $ColorValueString,
//		"phrase_color_value_string" => $PhraseColorValueString,
//		"border_color_string" => $BorderColorString,
//		"phrase_border_color_string" => $PhraseBorderColorString,
//		"opacity_string" => $OpacityString,
//		"phrase_opacity_string" => $PhraseOpacityString,
		"phrases_used_flag_string" => $PhrasesUsedFlagString,
		);

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

	// Get opcode

	$OpCode = get_assoc_default($FormData,"_op",false);
	if ($OpCode == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOOP"));
		exit(0);
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

	aib_open_db();
	if (aib_api_check_host($GLOBALS["aib_db"],$ServerName) == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "HOSTNOTALLOWED"));
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
	$RequestUserRoot = $RequestUserRecord["user_top_folder"];


	// Generate a new session

	$OutData = array("status" => "OK");
	switch($OpCode)
	{
		// Given an item ID and list of match words, generate a list of rectangles

		case "highlights":

			$ItemID = get_assoc_default($FormData,"obj_id",false);
			$WordListString = get_assoc_default($FormData,"word_list","");
			$DisplayWidth = get_assoc_default($FormData,"display_width","0");
			$DisplayHeight = get_assoc_default($FormData,"display_height","0");
			$FileClass = get_assoc_default($FormData,"file_class",AIB_FILE_CLASS_PRIMARY);

			if ($ItemID === false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGITEMID";
				break;
			}
			
			// Get display size so we can adjust for zoom levels, etc.

			if (intval($DisplayWidth) < 0 || intval($DisplayHeight) < 0)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "BADIMAGESIZE";
				break;
			}

			// Get image file info

			$ImageFileInfo = get_item_file_info($GLOBALS["aib_db"],$ItemID,$FileClass);
			if ($ImageFileInfo == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOIMAGEFILE";
				break;
			}

			// Get image file name

			$SourceName = $ImageFileInfo["filename"];

			// Get MIME type; make sure it's an image

			$MIMEType = strtolower($ImageFileInfo["mime"]);
			$ValidImage = false;
			while(true)
			{
				if (preg_match("/image[\/]/",$MIMEType) != false)
				{
					if (preg_match("/jpeg/",$MIMEType) != false)
					{
						$ValidImage = true;
						break;
					}

					if (preg_match("/jpg/",$MIMEType) != false)
					{
						$ValidImage = true;
						break;
					}

					if (preg_match("/tiff/",$MIMEType) != false)
					{
						$ValidImage = true;
						break;
					}

					if (preg_match("/tif/",$MIMEType) != false)
					{
						$ValidImage = true;
						break;
					}

					if (preg_match("/png/",$MIMEType) != false)
					{
						$ValidImage = true;
						break;
					}

					if (preg_match("/gif/",$MIMEType) != false)
					{
						$ValidImage = true;
						break;
					}
				}

				break;
			}

			if ($ValidImage == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDIMAGEFILE";
				break;
			}

			// Open file and get info

			$ImageBuffer = new Imagick($SourceName);
			if ($ImageBuffer == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "UNRECOGNIZEDIMAGEFORMAT";
				break;
			}

			// Get stored size

			$DiskWidth = $ImageBuffer->getImageWidth();
			$DiskHeight = $ImageBuffer->getImageHeight();
			unset($ImageBuffer);

			// Retrieve the RLC file for the item.  If none, return error.

			$ItemFileData = get_item_file($GLOBALS["aib_db"],$ItemID,AIB_FILE_CLASS_TEXT_LOCATION);
			if ($ItemFileData == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "NOLOCATIONFILE";
				break;
			}

			// Parse locations

			$LocationData = load_location_file($ItemFileData["data"],"rlc");
			if ($LocationData == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "INVALIDLOCATIONDATA";
				break;
			}

			// Generate rectangles.  First, determine if we need to
			// calculate the display width or height (or both).  If the
			// display width and height are zero, use disk size.  Else,
			// calculate the missing value using a ratio determined from
			// the supplied value for width or height.

			if ($DisplayWidth == 0 && $DisplayHeight == 0)
			{
				$DisplayWidth = $DiskWidth;
				$DisplayHeight = $DiskHeight;
			}
			else
			{
				if ($DisplayWidth == 0)
				{
					$LocalRatio = $DisplayHeight / $DiskHeight;
					$DisplayWidth = $DiskWidth * $LocalRatio;
				}
				else
				{
					$LocalRatio = $DisplayWidth / $DiskWidth;
					$DisplayHeight = $DiskHeight * $LocalRatio;
				}
			}

			$OriginalWidth = $LocationData[0]["width"];
			$OriginalHeight = $LocationData[0]["height"];
			$ImageData = generate_overlay_set($LocationData,$WordListString,$DiskWidth,$DiskHeight,$OriginalWidth,$OriginalHeight,$DisplayWidth,$DisplayHeight);
			$OutData["status"] = "OK";
			$OutData["info"] = json_encode($ImageData);
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
