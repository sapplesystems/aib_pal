<?php

//
// SmallTownPapers, Inc.
//
// Utility function library.  Assumes that the database configuration settings have already been loaded.
//

// Define page types

define("STP_PAGE_TYPE_FRONT_COVER","FC");
define("STP_PAGE_TYPE_REAR_COVER","RC");
define("STP_PAGE_TYPE_NUMBERED","NP");
define("STP_PAGE_TYPE_STANDARD","S");

// Define page formats

define("STP_YEARBOOK_ARCHIVE","ybook");
define("STP_STANDARD_ARCHIVE","std");

// Log message
// -----------
function stp_db_log($Msg)
{
	$Handle = fopen("/tmp/stp_db_log.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

// Connect to the database
// -----------------------
function stp_db_connect()
{
//	$Handle = mysql_connect(DBHOST,DBUSER,DBPASS);
	$Handle = mysqli_connect(DBHOST,DBUSER,DBPASS);
	if ($Handle == false)
	{
		return(false);
	}

//	if (mysql_select_db(DATABASE) == false)
	if (mysqli_select_db($Handle,DATABASE) == false)
	{
//		mysql_close($Handle);
		mysqli_close($Handle);
		return(false);
	}

	$GLOBALS["stp_db_handle"] = $Handle;
	return($Handle);
}

// Disconnect from the database
// ----------------------------
function stp_db_disconnect($Handle)
{
//	mysql_close($Handle);
	mysqli_close($Handle);
	return;
}

// Return the first matching record
// --------------------------------
function stp_db_first_match($Handle,$Query)
{
	$LocalRecord = false;
//	$LocalResult = mysql_query($Query);
	$LocalResult = mysqli_query($Handle,$Query);
	if ($LocalResult != false)
	{
//		if (mysql_num_rows($LocalResult) > 0)
		if (mysqli_num_rows($LocalResult) > 0)
		{
//			$LocalRecord = mysql_fetch_assoc($LocalResult);
			$LocalRecord = mysqli_fetch_assoc($LocalResult);
		}

//		mysql_free_result($LocalResult);
		mysqli_free_result($LocalResult);
	}

	return($LocalRecord);
}

// Count the number of results for a query
// ---------------------------------------
function stp_db_matches($Handle,$TableName,$WhereClause)
{
	$LocalCount = 0;
//	$LocalResult = mysql_query("SELECT count(*) FROM $TableName WHERE $WhereClause;");
	$LocalResult = mysqli_query($Handle,"SELECT count(*) FROM $TableName WHERE $WhereClause;");
	if ($LocalResult != false)
	{
//		$LocalRecord = mysql_fetch_row($LocalResult);
		$LocalRecord = mysqli_fetch_row($LocalResult);
		if ($LocalRecord != false)
		{
			$LocalCount = $LocalRecord[0];
		}

//		mysql_free_result($LocalResult);
		mysqli_free_result($LocalResult);
	}

	return($LocalCount);
}

// Given a newspaper code, get the newspaper information
// -----------------------------------------------------
function stp_db_get_publication_info($Handle,$NewspaperCode)
{
	$NewspaperCode = strtoupper($NewspaperCode);
	$NewspaperCode = preg_replace("/[^A-Za-z0-9]/","",$NewspaperCode);
	$LocalResult = stp_db_first_match($Handle,"SELECT * FROM publications WHERE publication_code='$NewspaperCode';");
	return($LocalResult);
}

// Given a newspaper code, return all property values
// --------------------------------------------------
function stp_db_get_publication_properties($Handle,$NewspaperCode)
{
	$NewspaperCode = strtoupper($NewspaperCode);
	$ResultSet = array();
//	$LocalResult = mysql_query("SELECT * FROM publication_properties WHERE publication_code='$NewspaperCode';");
	$LocalResult = mysqli_query($Handle,"SELECT * FROM publication_properties WHERE publication_code='$NewspaperCode';");
	if ($LocalResult != false)
	{
//		if (mysql_num_rows($LocalResult) > 0)
		if (mysqli_num_rows($LocalResult) > 0)
		{
			while(true)
			{
//				$LocalRecord = mysql_fetch_assoc($LocalResult);
				$LocalRecord = mysqli_fetch_assoc($LocalResult);
				if ($LocalRecord == false)
				{
					break;
				}

				$ResultSet[$LocalRecord["property_name"]] = $LocalRecord["property_value"];
			}
		}

//		mysql_free_result($LocalResult);
		mysqli_free_result($LocalResult);
	}

	return($ResultSet);
}

// Given a publisher code, get the publisher information
// -----------------------------------------------------
function stp_db_get_publisher_info($Handle,$PubID)
{
	$PubID = preg_replace("/[^A-Za-z0-9]/","",$PubID);
	$LocalResult = stp_db_first_match($Handle,"SELECT * FROM publishers WHERE publisher_code='$PubID';");
	return($LocalResult);
}

// Given a newspaper code, get the list of years available
// -------------------------------------------------------
function stp_db_get_publication_years($Handle,$NewspaperCode,$ReverseSort = false)
{
	$ResultSet = array();
	$NewspaperCode = strtoupper($NewspaperCode);
	if ($ReverseSort == false)
	{
//		$LocalResult = mysql_query("SELECT * FROM edition_years WHERE publication_code='$NewspaperCode' ORDER BY publication_code,edition_year_title;");
		$LocalResult = mysqli_query($Handle,"SELECT * FROM edition_years WHERE publication_code='$NewspaperCode' ORDER BY publication_code,edition_year_title;");
	}
	else
	{
//		$LocalResult = mysql_query("SELECT * FROM edition_years WHERE publication_code='$NewspaperCode' ORDER BY publication_code,edition_year_title DESC;");
		$LocalResult = mysqli_query($Handle,"SELECT * FROM edition_years WHERE publication_code='$NewspaperCode' ORDER BY publication_code,edition_year_title DESC;");
	}

	if ($LocalResult != false)
	{
//		if (mysql_num_rows($LocalResult) > 0)
		if (mysqli_num_rows($LocalResult) > 0)
		{
			while(true)
			{
//				$LocalRecord = mysql_fetch_assoc($LocalResult);
				$LocalRecord = mysqli_fetch_assoc($LocalResult);
				if ($LocalRecord == false)
				{
					break;
				}

				$ResultSet[] = $LocalRecord;
			}
		}

//		mysql_free_result($LocalResult);
		mysqli_free_result($LocalResult);
	}

	return($ResultSet);
}

// Given a newspaper code and a year, get the list of editions
// -----------------------------------------------------------
function stp_db_get_publication_editions($Handle,$NewspaperCode,$Year,$ShowDelayed = true)
{
	$ResultSet = array();
	$TimeStamp = time();
	$NewspaperCode = strtoupper($NewspaperCode);
//	$LocalResult = mysql_query("SELECT * FROM editions WHERE publication_code='$NewspaperCode' AND edition_year=$Year ORDER BY publication_code,edition_sort_name;");
	$LocalResult = mysqli_query($Handle,"SELECT * FROM editions WHERE publication_code='$NewspaperCode' AND edition_year='$Year' ORDER BY publication_code,edition_sort_name;");
	if ($LocalResult != false)
	{
//		if (mysql_num_rows($LocalResult) > 0)
		if (mysqli_num_rows($LocalResult) > 0)
		{
			while(true)
			{
//				$LocalRecord = mysql_fetch_assoc($LocalResult);
				$LocalRecord = mysqli_fetch_assoc($LocalResult);
				if ($LocalRecord == false)
				{
					break;
				}

				if ($ShowDelayed == true)
				{
					$ResultSet[] = $LocalRecord;
				}
				else
				{
					if (intval($LocalRecord["edition_delay_until"]) > 0)
					{
						if (intval($LocalRecord["edition_delay_until"]) <= $TimeStamp)
						{
							$ResultSet[] = $LocalRecord;
						}
					}
				}
			}
		}

//		mysql_free_result($LocalResult);
		mysqli_free_result($LocalResult);
	}

	return($ResultSet);
}

// Get the list of articles available for a range of years
//
// StartYear	EndYear		Result
//	> 0	>= 0		All entries for StartYear
//	< 0	0		All entries for current to StartYear years ago
//	< 0	< 0		All entries from EndYear years ago to StartYear years ago
// --------------------------------------------------------------------------------------
function stp_db_get_publication_articles_years($Handle,$PublicationCode,$StartYear,$EndYear,$Ordering,$OrderDir = "ASC",$ShowDelayed = true)
{
	if ($StartYear > 0 && $EndYear == 0)
	{
		$EndYear = $StartYear;
	}

	if ($StartYear > $EndYear && ($StartYear >= 0 && $EndYear >= 0))
	{
		$TempYear = $EndYear;
		$EndYear = $StartYear;
		$StartYear = $TempYear;
	}

	if ($ShowDelayed == false)
	{
		// Get list of feeds and create feed map

		$FeedList = stp_db_get_feed_list($PublicationCode);
		$FeedMap = array();
		$MaxDelay = 0;
		foreach($FeedList as $FeedRecord)
		{
			$FeedMap[$FeedRecord["record_id"]] = $FeedRecord;
			if (intval($FeedRecord["feed_delay"]) > $MaxDelay)
			{
				$MaxDelay = intval($FeedRecord["feed_delay"]);
			}
		}

		$TimeStamp = time();
	}
	else
	{
		$FeedMap = array();
		$TimeStamp = time();
		$MaxDelay = 0;
	}

	if ($StartYear < 0 && $EndYear != $StartYear && $EndYear < 0)
	{
		$CurrentYear = date("Y");
		$FirstYear = $CurrentYear + $StartYear;
		$LastYear = $CurrentYear + $EndYear;
		$Query = "SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year >= $FirstYear ".
			"AND article_year <= $LastYear AND article_disabled='N'";
	}
	else
	{
		if ($StartYear < 0 && $EndYear == 0)
		{
			$CurrentYear = date("Y");
			$Query = "SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year >= $FirstYear ".
				"AND article_disabled='N'";
		}
		else
		{
			if ($StartYear == 0)
			{
				$Query = "SELECT * FROM articles WHERE article_pub='$PublicationCode' ".
					"AND article_disabled='N'";
			}
			else
			{
				$Query = "SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year >= $StartYear ".
					"AND article_year <= $EndYear AND article_disabled='N'";
			}
		}
	}

	switch(strtolower($Ordering))
	{
		case "date":
			$Query .= " ORDER BY article_pub,article_pubdate";
			break;

		case "alpha":
		default:
			$Query .= " ORDER BY article_pub,article_sort_title";
			break;
	}

	if ($StartYear >= 0)
	{
		switch(strtolower($OrderDir))
		{
			case "asc":
			case "ascending":
				$Query .= " ASC";
				break;
	
			case "desc":
			case "dsc":
			case "descending":
				$Query .= " DESC";
				break;
	
			default:
				break;
		}
	}
	else
	{
		if ($StartYear < 0)
		{
			$Query .= " DESC;";
		}
		else
		{
			$Query .= ";";
		}
	}

//	$Result = mysql_query($Query);
	$Result = mysqli_query($Handle,$Query);
	if ($Result == false)
	{
		return(false);
	}

	$OutList = array();
	while(true)
	{
//		$Row = mysql_fetch_assoc($Result);
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		if ($ShowDelayed == true)
		{
			$OutList[] = $Row;
		}
		else
		{
			if ($Row["article_feed_id"] > 0)
			{
				$FeedID = $Row["article_feed_id"];
				if (isset($FeedMap[$FeedID]) == true)
				{
					$FeedRecord = $FeedMap[$FeedID];
					$ArticleDate = strtotime($Row["article_pubdate"]);
					if ($ArticleDate + $FeedRecord["feed_delay"] <= $TimeStamp)
					{
						$OutList[] = $Row;
					}
				}
			}
			else
			{
				$ArticleDate = strtotime($Row["article_pubdate"]);
				if ($ArticleDate + $MaxDelay <= $TimeStamp)
				{
					$OutList[] = $Row;
				}
			}
		}
	}

//	mysql_free_result($Result);
	mysqli_free_result($Result);
	return($OutList);
}

// Get the list of articles available for a given year and paper
// -------------------------------------------------------------
function stp_db_get_publication_articles($Handle,$PublicationCode,$Year,$FilterDisabled = false, $ShowDelayed = true)
{
	if ($ShowDelayed == false)
	{
		// Get list of feeds and create feed map

		$FeedList = stp_db_get_feed_list($PublicationCode);
		$FeedMap = array();
		$MaxDelay = 0;
		foreach($FeedList as $FeedRecord)
		{
			$FeedMap[$FeedRecord["record_id"]] = $FeedRecord;
			if (intval($FeedRecord["feed_delay"]) > $MaxDelay)
			{
				$MaxDelay = intval($FeedRecord["feed_delay"]);
			}
		}

		$TimeStamp = time();
	}
	else
	{
		$FeedMap = array();
		$TimeStamp = time();
		$MaxDelay = 0;
	}


	$ResultSet = array();
	if (intval($Year) > 0)
	{
		if ($FilterDisabled == false)
		{
//			$Result = mysql_query("SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year=$Year ".
			$Result = mysqli_query($Handle,"SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year='$Year' ".
				"ORDER BY article_pub,article_pubdate;");
		}
		else
		{
//			$Result = mysql_query("SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year=$Year AND ".
			$Result = mysqli_query($Handle,"SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year='$Year' AND ".
				"article_disabled='Y' ".
				"ORDER BY article_pub,article_pubdate;");
		}
	}
	else
	{
		if ($Year < 0)
		{
			$Limit = intval($Year) * -1;
			if ($FilterDisabled == false)
			{
//				$Result = mysql_query("SELECT * FROM articles WHERE article_pub='$PublicationCode' ".
				$Result = mysqli_query($Handle,"SELECT * FROM articles WHERE article_pub='$PublicationCode' ".
					"ORDER BY article_pub,article_pubdate DESC LIMIT $Limit;");
			}
			else
			{
//				$Result = mysql_query("SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_disabled='N' ".
				$Result = mysqli_query($Handle,"SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_disabled='N' ".
					"ORDER BY article_pub,article_pubdate DESC LIMIT $Limit;");
			}
		}
		else
		{
			if ($FilterDisabled == false)
			{
//				$Result = mysql_query("SELECT * FROM articles WHERE article_pub='$PublicationCode' ".
				$Result = mysqli_query($Handle,"SELECT * FROM articles WHERE article_pub='$PublicationCode' ".
					"ORDER BY article_pub,article_pubdate;");
			}
			else
			{
//				$Result = mysql_query("SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_disabled='N'".
				$Result = mysqli_query($Handle,"SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_disabled='N'".
					"ORDER BY article_pub,article_pubdate;");
			}
		}
	}

	if ($Result != false)
	{
//		if (mysql_num_rows($Result) > 0)
		if (mysqli_num_rows($Result) > 0)
		{
			while(true)
			{
//				$Row = mysql_fetch_assoc($Result);
				$Row = mysqli_fetch_assoc($Result);
				if ($Row == false)
				{
					break;
				}

				if ($ShowDelayed == true)
				{
					$ResultSet[] = $Row;
				}
				else
				{
					if ($Row["article_feed_id"] > 0)
					{
						$FeedID = $Row["article_feed_id"];
						if (isset($FeedMap[$FeedID]) == true)
						{
							$FeedRecord = $FeedMap[$FeedID];
							$ArticleDate = strtotime($Row["article_pubdate"]);
							if ($ArticleDate + $FeedRecord["feed_delay"] <= $TimeStamp)
							{
								$ResultSet[] = $Row;
							}
						}
					}
					else
					{
						$ArticleDate = strtotime($Row["article_pubdate"]);
						if ($ArticleDate + $MaxDelay <= $TimeStamp)
						{
							$ResultSet[] = $Row;
						}
					}
				}
			}
		}

//		mysql_free_result($Result);
		mysqli_free_result($Result);
	}

	return($ResultSet);
}

// Get the list of articles for a given date
// -----------------------------------------
function stp_db_get_publication_articles_day($Handle,$PublicationCode,$Year,$Month,$Day,$Disabled='N',$OrderDir = "ASC", $ShowDelayed = true)
{
	if ($ShowDelayed == false)
	{
		// Get list of feeds and create feed map

		$FeedList = stp_db_get_feed_list($PublicationCode);
		$FeedMap = array();
		$MaxDelay = 0;
		foreach($FeedList as $FeedRecord)
		{
			$FeedMap[$FeedRecord["record_id"]] = $FeedRecord;
			if (intval($FeedRecord["feed_delay"]) > $MaxDelay)
			{
				$MaxDelay = intval($FeedRecord["feed_delay"]);
			}
		}

		$TimeStamp = time();
	}
	else
	{
		$FeedMap = array();
		$TimeStamp = time();
		$MaxDelay = 0;
	}

	$ResultSet = array();
	$Query = "SELECT * FROM articles WHERE article_pub='$PublicationCode' AND article_year=$Year AND ".
		"article_month=$Month AND article_day=$Day AND article_disabled='$Disabled'".
		" ORDER BY article_year,article_month,article_day,article_hour,".
		"article_min,article_sec";
	switch(strtolower($OrderDir))
	{
		case "asc":
		case "ascend":
		case "ascending":
			$Query .= " ASC";
			break;

		case "desc":
		case "descending":
			$Query .= " DESC";
			break;

		default:
			break;
	}

	$Query .= ";";
//	$Result = mysql_query($Query);
	$Result = mysqli_query($Handle,$Query);
	if ($Result != false)
	{
//		if (mysql_num_rows($Result) > 0)
		if (mysqli_num_rows($Result) > 0)
		{
			while(true)
			{
//				$Row = mysql_fetch_assoc($Result);
				$Row = mysqli_fetch_assoc($Result);
				if ($Row == false)
				{
					break;
				}

				if ($ShowDelayed == true)
				{
					$ResultSet[] = $Row;
				}
				else
				{
					if ($Row["article_feed_id"] > 0)
					{
						$FeedID = $Row["article_feed_id"];
						if (isset($FeedMap[$FeedID]) == true)
						{
							$FeedRecord = $FeedMap[$FeedID];
							$ArticleDate = strtotime($Row["article_pubdate"]);
							if ($ArticleDate + $FeedRecord["feed_delay"] <= $TimeStamp)
							{
								$ResultSet[] = $Row;
							}
						}
					}
					else
					{
						$ArticleDate = strtotime($Row["article_pubdate"]);
						if ($ArticleDate + $MaxDelay <= $TimeStamp)
						{
							$ResultSet[] = $Row;
						}
					}
				}
			}
		}

//		mysql_free_result($Result);
		mysqli_free_result($Result);
	}

	return($ResultSet);

}

// Given article ID, grab publisher info, article info, file list
// --------------------------------------------------------------
function stp_db_get_article_info($Handle,$ArticleID,$Detail = "all",$Page = false)
{
	$Out = array();
	$PublisherRecord = array();
	$PublicationRecord = array();
	$ArticleID = preg_replace("/[^A-Za-z0-9\.]/","",$ArticleID);
	$ArticleRecord = stp_db_first_match($Handle,"SELECT * FROM articles WHERE article_id='$ArticleID';");
	if ($ArticleRecord == false)
	{
		return(false);
	}

	$FileList = array();

	// If a page was specified, then grab only that page.  Otherwise, grab all.

	if ($Page === false)
	{
//		$Result = mysql_query("SELECT * FROM article_files WHERE article_record_id=".$ArticleRecord["record_id"].
		$Result = mysqli_query($Handle,"SELECT * FROM article_files WHERE article_record_id=".$ArticleRecord["record_id"].
			" ORDER BY article_file_type,article_image_number;");
	}
	else
	{
//		$Result = mysql_query("SELECT * FROM article_files WHERE article_record_id=".$ArticleRecord["record_id"]." AND ".
		$Result = mysqli_query($Handle,"SELECT * FROM article_files WHERE article_record_id=".$ArticleRecord["record_id"]." AND ".
			"article_image_number='$Page' ORDER BY article_file_type,article_image_number;");
	}

	if ($Result == false)
	{
		return(false);
	}

	while(true)
	{
//		$Row = mysql_fetch_assoc($Result);
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$FileList[] = $Row;
	}

//	mysql_free_result($Result);
	mysqli_free_result($Result);

	if ($Detail == "all")
	{
		$PublicationCode = $ArticleRecord["article_pub"];
		$PublicationRecord = stp_db_first_match($Handle,"SELECT * FROM publications WHERE publication_code='$PublicationCode';");
		if ($PublicationRecord == false)
		{
			return(false);
		}
	
		$PublisherCode = $PublicationRecord["publication_pub"];
		$PublisherRecord = stp_db_first_match($Handle,"SELECT * FROM publishers WHERE publisher_code='$PublisherCode';");
		if ($PublisherRecord == false)
		{
			return(false);
		}
	}

	$Out["article"] = $ArticleRecord;
	$Out["publisher"] = $PublisherRecord;
	$Out["publication"] = $PublicationRecord;
	$Out["files"] = $FileList;
	return($Out);
}


// Given a year and date string, get the edition
// ---------------------------------------------
function stp_db_get_edition_from_dates($Handle,$PublicationCode,$Year,$DateString)
{
	$MonthNameMap = array(
		"January" => 1, "February" => 2, "March" => 3, "April" => 4, "May" => 5, "June" => 6,
		"July" => 7, "August" => 8, "September" => 9, "October" => 10, "November" => 11,
		"December" => 12);

	$DateFields = explode(" ",$DateString);
	$MonthName = $DateFields[0];
	$Day = intval($DateFields[1]);
	if (isset($MonthNameMap[$MonthName]) == false)
	{
		return(false);
	}

	$Month = $MonthNameMap[$MonthName];
//	$LocalResult = mysql_query("SELECT * FROM editions WHERE publication_code='$PublicationCode' AND edition_year=$Year AND edition_month=$Month AND edition_day=$Day;");
	$LocalResult = mysqli_query($Handle,"SELECT * FROM editions WHERE publication_code='$PublicationCode' AND edition_year='$Year' AND edition_month='$Month' AND edition_day='$Day';");
	if ($LocalResult == false)
	{
		return(false);
	}

	$OutData = false;
//	if (mysql_num_rows($LocalResult) > 0)
	if (mysqli_num_rows($LocalResult) > 0)
	{ 
//		$OutData = mysql_fetch_assoc($LocalResult);
		$OutData = mysqli_fetch_assoc($LocalResult);
	}

//	mysql_free_result($LocalResult);
	mysqli_free_result($LocalResult);
	return($OutData);
}

// Given a year, month and day, get the edition
// ---------------------------------------------
function stp_db_get_edition_from_yearmonthday($Handle,$PublicationCode,$Year,$Month,$Day)
{
//	$LocalResult = mysql_query("SELECT * FROM editions WHERE publication_code='$PublicationCode' AND edition_year=$Year AND edition_month=$Month AND edition_day=$Day;");
	$LocalResult = mysqli_query($Handle,"SELECT * FROM editions WHERE publication_code='$PublicationCode' AND edition_year='$Year' AND edition_month='$Month' AND edition_day='$Day';");
	if ($LocalResult == false)
	{
		return(false);
	}

	$OutData = false;
//	if (mysql_num_rows($LocalResult) > 0)
	if (mysqli_num_rows($LocalResult) > 0)
	{ 
//		$OutData = mysql_fetch_assoc($LocalResult);
		$OutData = mysqli_fetch_assoc($LocalResult);
	}

//	mysql_free_result($LocalResult);
	mysqli_free_result($LocalResult);
	return($OutData);
}

// Given an edition ID, get the page list
// --------------------------------------
function stp_db_get_edition_pages($Handle,$EditionID)
{
	$ResultSet = array();
//	$LocalResult = mysql_query("SELECT * FROM edition_pages WHERE edition_id=$EditionID ORDER BY edition_id,page_type,page_sort_name;");
	$LocalResult = mysqli_query($Handle,"SELECT * FROM edition_pages WHERE edition_id='$EditionID' ORDER BY edition_id,page_type,page_sort_name;");
	if ($LocalResult != false)
	{
//		if (mysql_num_rows($LocalResult) > 0)
		if (mysqli_num_rows($LocalResult) > 0)
		{
			while(true)
			{
//				$LocalRecord = mysql_fetch_assoc($LocalResult);
				$LocalRecord = mysqli_fetch_assoc($LocalResult);
				if ($LocalRecord == false)
				{
					break;
				}

				$ResultSet[] = $LocalRecord;
			}
		}

//		mysql_free_result($LocalResult);
		mysqli_free_result($LocalResult);
	}

	return($ResultSet);
}

// Display an error message page
// -----------------------------
function stp_error_page($Title,$Msg,$URL = false)
{

	print("<HTML><HEAD><TITLE>$Title</TITLE></HEAD><BODY>\n");
	print("<CENTER><H1>$Msg</H1></CENTER>");
	if ($URL != false)
	{
		print("<CENTER><H3><a href=\"$URL\">Click Here To Continue</a></H3></CENTER>\n");
	}

	print("</BODY></HTML>\n");
}

// Get an associative array value or a default if not present
// ----------------------------------------------------------
function stp_array_default($DataSet,$Name,$DefaultValue)
{
	if (isset($DataSet[$Name]) == false)
	{
		return($DefaultValue);
	}

	return($DataSet[$Name]);
}

// Given contact text, extract a contact email address
// ---------------------------------------------------
function stp_extract_email_from_contact($ContactText)
{
        if (preg_match("/[\<]a[ ]+href[^\>]+[^\<]+[\<][\/]a[\>]/",$ContactText,$TempMatches) != false)
        {
                $EmailURL = $TempMatches[0];
                $ContactText = str_replace($EmailURL,"",$ContactText);

                // Eliminate any escaped quotes from the email URL

                $EmailURL = preg_replace("/[\\\\][\"]/","\"",$EmailURL);
        }
        else
        {
                $EmailURL = "";
        }

	return($EmailURL);
}

// Build a list of page keywords from text
// ---------------------------------------
function stp_build_page_keywords($InText)
{

        // Build page keywords.  Keywords are derived from the page title text, the welcome text and the contact
        // text along with the state name.

        $KeywordList = array("newspaper" => true, "archive" => true, "newspaper archive" => true);
        $SourceArray = array($InText);
        $StopWords = array("to" => true, "po" => true, "box" => true, "the" => true, "at" => true, "and" => true, "or" => true, "with" => true,
                        "href" => true, "mailto" => true, "http" => true, "font" => "true", "br" => true, "p" => true,
                        "voice" => true, "fax" => true, "email" => true, "of" => true, "who" => true, "make" => true, "input" => true,
                        "hidden" => true, "script" => true, "is" => true, "each" => true, "blank" => true, "left" => true, "right" => true,
                        "top" => true, "width" => true, "height" => true, "class" => true, "come" => true, "com" => true, "org" => true, "net" => true);
        foreach($SourceArray as $SourceText)
        {
                $TempWords = preg_split("/[ \,\.]+/",$SourceText);
                foreach($TempWords as $WordText)
                {
                        $WordText = preg_replace("/[\<][A-Za-z]+[\>]/","",$WordText);
                        $WordText = preg_replace("/[\<][A-Za-z]+/","",$WordText);
                        $WordText = preg_replace("/[A-Za-z]+[\>]/","",$WordText);
                        $WordText = preg_replace("/[^A-Za-z0-9 ]/","",$WordText);
                        if (preg_match("/[A-Za-z]/",$WordText) == false)
                        {
                                continue;
                        }

                        if ($WordText == "")
                        {
                                continue;
                        }

                        if (isset($StopWords[strtolower($WordText)]) == true)
                        {
                                continue;
                        }

                        $WordText = strtolower($WordText);
                        $PatternList = array("href","mailto","email","style","font","size","[0-9]","target","blank","www","http","class","map",
                                "paste","absolute","overflow","margin");
                        $MatchFlag = false;
                        foreach($PatternList as $LocalPatternText)
                        {
                                $LocalPattern = "/".$LocalPatternText."/";
                                if (preg_match($LocalPattern,$WordText) == true)
                                {
                                        $MatchFlag = true;
                                        break;
                                }
                        }

                        if ($MatchFlag == true)
                        {
                                continue;
                        }

                        $KeywordList[$WordText] = true;
                }
        }

	return($KeywordList);
}

// Get sponsor settings
// --------------------
function stp_sponsor_settings($DBHandle,$MasterNewspaperCode)
{

	$SponsorSettings = array("newspaper_code" => $MasterNewspaperCode, "random_national" => "N", "random_community" => "N");
//        $SponsorSettingsResult = mysql_query("SELECT * FROM sponsor_settings WHERE newspaper_code='$MasterNewspaperCode';");
        $SponsorSettingsResult = mysqli_query($DBHandle,"SELECT * FROM sponsor_settings WHERE newspaper_code='$MasterNewspaperCode';");
        if ($SponsorSettingsResult != false)
        {
//                $LocalSettings = mysql_fetch_assoc($SponsorSettingsResult);
                $LocalSettings = mysqli_fetch_assoc($SponsorSettingsResult);
                if ($LocalSettings != false)
                {
                        $SponsorSettings["random_national"] = $LocalSettings["random_national"];
                        $SponsorSettings["random_community"] = $LocalSettings["random_community"];
                }
        }

	return($SponsorSettings);
}


// --------------------------
function stp_corporate_sponsor_info($DBHandle,$MasterNewspaperCode)
{
        $CorporateSponsorInfo = array();
        $SponsorSettings = array("newspaper_code" => $MasterNewspaperCode, "random_national" => "N", "random_community" => "N");
//        $SponsorSettingsResult = mysql_query("SELECT * FROM sponsor_settings WHERE newspaper_code='$MasterNewspaperCode';");
        $SponsorSettingsResult = mysqli_query($DBHandle,"SELECT * FROM sponsor_settings WHERE newspaper_code='$MasterNewspaperCode';");
        if ($SponsorSettingsResult != false)
        {
//                $LocalSettings = mysql_fetch_assoc($SponsorSettingsResult);
                $LocalSettings = mysqli_fetch_assoc($SponsorSettingsResult);
                if ($LocalSettings != false)
                {
                        $SponsorSettings["random_national"] = $LocalSettings["random_national"];
                        $SponsorSettings["random_community"] = $LocalSettings["random_community"];
                }
        }

        $CorporateSponsorInfo = array();
//        $TempResult = mysql_query("SELECT * FROM sponsor_data WHERE paper_code='$MasterNewspaperCode' AND sponsor_type='CORP' AND active='Y' ORDER BY sort_order;");
        $TempResult = mysqli_query($DBHandle,"SELECT * FROM sponsor_data WHERE paper_code='$MasterNewspaperCode' AND sponsor_type='CORP' AND active='Y' ORDER BY sort_order;");
        if ($TempResult != false)
        {
                while(true)
                {
//                        $LocalRow = mysql_fetch_assoc($TempResult);
                        $LocalRow = mysqli_fetch_assoc($TempResult);
                        if ($LocalRow == false)
                        {
                                break;
                        }
                        $NewArray = array();
                        foreach($LocalRow as $SourceName => $DestName)
                        {
                                $NewArray[$SourceName] = $LocalRow[$SourceName];
                        }

                        $NewArray["title"] = str_replace("\"","'",$NewArray["title"]);
                        $NewArray["logo_alt_text"] = str_replace("\"","'",$NewArray["logo_alt_text"]);
                        $CorporateSponsorInfo[] = $NewArray;
                }

//                mysql_free_result($TempResult);
                mysqli_free_result($TempResult);
        }

	return($CorporateSponsorInfo);
}

// Get community sponsor info
// --------------------------
function stp_community_sponsor_info($DBHandle,$MasterNewspaperCode)
{

        $CommunitySponsorInfo = array();
//        $TempResult = mysql_query("SELECT * FROM sponsor_data WHERE paper_code='$MasterNewspaperCode' AND sponsor_type='COMM' AND active='Y' ORDER BY sort_order;");
        $TempResult = mysqli_query($DBHandle,"SELECT * FROM sponsor_data WHERE paper_code='$MasterNewspaperCode' AND sponsor_type='COMM' AND active='Y' ORDER BY sort_order;");
        if ($TempResult != false)
        {
                while(true)
                {
//                        $LocalRow = mysql_fetch_assoc($TempResult);
                        $LocalRow = mysqli_fetch_assoc($TempResult);
                        if ($LocalRow == false)
                        {
                                break;
                        }
                        $NewArray = array();
                        foreach($LocalRow as $SourceName => $DestName)
                        {
                                $NewArray[$SourceName] = $LocalRow[$SourceName];
                        }

                        $NewArray["title"] = str_replace("\"","'",$NewArray["title"]);
                        $NewArray["logo_alt_text"] = str_replace("\"","'",$NewArray["logo_alt_text"]);
                        $CommunitySponsorInfo[] = $NewArray;
                }

//                mysql_free_result($TempResult);
                mysqli_free_result($TempResult);
        }

	return($CommunitySponsorInfo);
}

// Get page using page file name
// -----------------------------
function stp_db_get_page_by_name($DBHandle,$PageName)
{
//	$LocalResult = mysql_query("SELECT * FROM edition_pages WHERE pagefile='$PageName';");
	$LocalResult = mysqli_query($DBHandle,"SELECT * FROM edition_pages WHERE pagefile='$PageName';");
	if ($LocalResult == false)
	{
		return(false);
	}

	$OutRecord = false;
//	if (mysql_num_rows($LocalResult) > 0)
	if (mysqli_num_rows($LocalResult) > 0)
	{
//		$OutRecord = mysql_fetch_assoc($LocalResult);
		$OutRecord = mysqli_fetch_assoc($LocalResult);
	}

//	mysql_free_result($LocalResult);
	mysqli_free_result($LocalResult);
	return($OutRecord);
}

// Get edition using edition ID
// ----------------------------
function stp_db_get_edition_from_id($Handle,$EditionID)
{
//	$LocalResult = mysql_query("SELECT * FROM editions WHERE edition_id=$EditionID;");
	$LocalResult = mysqli_query($Handle,"SELECT * FROM editions WHERE edition_id='$EditionID';");
	if ($LocalResult == false)
	{
		return(false);
	}

	$OutData = false;
//	if (mysql_num_rows($LocalResult) > 0)
	if (mysqli_num_rows($LocalResult) > 0)
	{ 
//		$OutData = mysql_fetch_assoc($LocalResult);
		$OutData = mysqli_fetch_assoc($LocalResult);
	}

//	mysql_free_result($LocalResult);
	mysqli_free_result($LocalResult);
	return($OutData);
}

// Given an article ID, page and file type, get file name
// ------------------------------------------------------
function stp_db_get_article_file($Settings,$PublisherID,$ArticleID,$FileName)
{
	$StorageBase = $Settings["storage_base"];
	if ($Page === false)
	{
		return(false);
	}

	$OutFile = "/$StorageBase/$PublisherID/$ArticleID/$FileName";
	return($OutFile);
}

function stp_replace_url_host($InURL,$NewHost,$NewPort = 80)
{
	$InURL = rtrim(ltrim($InURL));
	$URLSeg = preg_split("/[\/]+/",$InURL);
	$OutSeg = array();
	$Seg = array_shift($URLSeg);

	// If the first segment is a protocol identifier, discard it.  Always
	// use "http:" as the initial protocol in the output.

	if (strtolower($Seg) == "http:" || strtolower($Seg) == "https:")
	{
		$OutSeg[] = "http:/";
		$Seg = array_shift($URLSeg);
	}
	else
	{
		$OutSeg[] = "http:/";
	}

	// If the segment doesn't seem to be a server name, then prepend the new name.  Else,
	// discard and use new name.

	if (preg_match("/[\.][Cc][Oo][Mm]$/",$Seg) == false && preg_match("/[\.][Nn][Ee][Tt]$/",$Seg) == false && preg_match("/[\.][Oo][Rr][Gg]$/",$Seg) == false)
	{
		if ($NewPort == 80 || $NewPort == 443)
		{
			$OutSeg[] = $NewHost;
		}
		else
		{
			$OutSeg[] = $NewHost.":".$NewPort;
		}

		$OutSeg[] = $Seg;
	}
	else
	{
		if ($NewPort == 80 || $NewPort == 443)
		{
			$OutSeg[] = $NewHost;
		}
		else
		{
			$OutSeg[] = $NewHost.":".$NewPort;
		}
	}

	foreach($URLSeg as $Seg)
	{
		$OutSeg[] = $Seg;
	}

	$OutURL = join("/",$OutSeg);
	return($OutURL);
}

// Given a host name and publication code, return the name of the search index to use
// ----------------------------------------------------------------------------------
function stp_get_search_index_name($HostName,$PubCode)
{
	$Query = "SELECT * FROM search_config WHERE publication_code='$PubCode' AND host_name='$HostName';";
//	$Result = mysql_query($Query);
	$DBHandle = $GLOBALS["stp_db_handle"];
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(strtolower($PubCode));
	}

	$OutCode = strtolower($PubCode);
	while(true)
	{
//		$Row = mysql_fetch_assoc($Result);
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutCode = $Row["index_name"];
		break;
	}

//	mysql_free_result($Result);
	mysqli_free_result($Result);
	return($OutCode);
}

// Given a publication, get the data feeds for it
// ----------------------------------------------
function stp_db_get_feed_list($PublicationCode)
{
//	$Result = mysql_query("SELECT * FROM data_feeds WHERE feed_publication='$PublicationCode';");
	$DBHandle = $GLOBALS["stp_db_handle"];
//	$Result = mysql_query("SELECT * FROM data_feeds WHERE feed_publication='$PublicationCode';");
	$Result = mysqli_query($DBHandle,"SELECT * FROM data_feeds WHERE feed_publication='$PublicationCode';");
	if ($Result == false)
	{
		return(array());
	}

	$OutList = array();
	while(true)
	{
//		$Row = mysql_fetch_assoc($Result);
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}

//	mysql_free_result($Result);
	mysqli_free_result($Result);
	return($OutList);
}

// Filter a list of articles based on publication delay
// ----------------------------------------------------
function stp_db_filter_articles_for_delay($InitialArticleList,$FeedList)
{
	$ArticleList = array();
	$FeedMap = array();

	// Calculate the max delay for articles.  This will be the largest
	// delay of all the defined feeds.

	$MaxFeedDelay = 0;
	foreach($FeedList as $FeedRecord)
	{
		$FeedMap[$FeedRecord["record_id"]] = $FeedRecord;
		if ($MaxFeedDelay < $FeedRecord["feed_delay"])
		{
			$MaxFeedDelay = $FeedRecord["feed_delay"];
		}
	}

	$MaxFeedDelay = $MaxFeedDelay * 3600;

	// Get today's date

	$TodayTime = time();

	// Process each article

	foreach($InitialArticleList as $Entry)
	{
		// Check to see if the article is to be delayed.  If so, don't put in the list.  The feed ID
		// has to be GT zero...

		if ($Entry["article_feed_id"] > 0)
		{
			// If the feed is defined, check

			if (isset($FeedMap[$Entry["article_feed_id"]]) == true)
			{
				$FeedEntry = $FeedMap[$Entry["article_feed_id"]];

				// If the feed has a delay, check

				if ($Entry["feed_delay"] > 0)
				{
					// Get article time stamp

					$ArticleTime = strtotime($Entry["article_pubdate"]);

					// Comparison time is current time less the delay

					$TestTime = $TodayTime - ($FeedEntry["feed_delay"] * 3600);

					// If the article is older than the comparison time, then publish

					if ($ArticleTime < $TestTime)
					{
						continue;
					}
				}
			}
		}
		else
		{
			// If there's a max delay, check

			if ($MaxFeedDelay > 0)
			{
				// Get article time stamp

				$ArticleTime = strtotime($Entry["article_pubdate"]);

				// Comparison time is current time less the delay

				$TestTime = $TodayTime - $MaxFeedDelay;

				// If the article is older than the comparison time, then publish

				if ($ArticleTime < $TestTime)
				{
					continue;
				}
			}
		}

		$ArticleList[] = $Entry;
	}

	return($ArticleList);
}


// Create feed date and maximum delay values
// -----------------------------------------
function stp_db_set_up_article_delay_check($PublicationCode)
{
		// Get list of feeds and create feed map

	$FeedList = stp_db_get_feed_list($PublicationCode);
	$FeedMap = array();
	$MaxDelay = 0;
	foreach($FeedList as $FeedRecord)
	{
		$FeedMap[$FeedRecord["record_id"]] = $FeedRecord;
		if (intval($FeedRecord["feed_delay"]) > $MaxDelay)
		{
			$MaxDelay = intval($FeedRecord["feed_delay"]);
		}
	}

	$ReturnSet = array("feed_list" => $FeedList, "feed_map" => $FeedMap, "max_delay" => $MaxDelay);
	return($ReturnSet);
}

// Given an article, calculate the publication date and whether the article
// can be active.
// ------------------------------------------------------------------------
function stp_db_calc_article_pub_date($FeedInfo, $ArticleID)
{
	$DBHandle = $GLOBALS["stp_db_handle"];
//	$Result = mysql_query("SELECT * FROM articles WHERE record_id=$ArticleID LIMIT 1;");
	$Result = mysqli_query($DBHandle,"SELECT * FROM articles WHERE record_id='$ArticleID' LIMIT 1;");
	if ($Result == false)
	{
		return(false);
	}

	$TimeStamp = time();
	$OutData = false;
	$MaxDelay = $FeedInfo["max_delay"];
	$TimeStamp = time();
//	if (mysql_num_rows($Result) > 0)
	if (mysqli_num_rows($Result) > 0)
	{
//		$Row = mysql_fetch_assoc($Result);
		$Row = mysqli_fetch_assoc($Result);
		$FeedMap = $FeedInfo["feed_map"];
		$OutData = array();
		if ($Row["article_feed_id"] > 0)
		{
			$FeedID = $Row["article_feed_id"];
			if (isset($FeedMap[$FeedID]) == true)
			{
				$FeedRecord = $FeedMap[$FeedID];
				$ArticleDate = strtotime($Row["article_pubdate"]);
				if ($ArticleDate + $FeedRecord["feed_delay"] <= $TimeStamp)
				{
					$OutData["pub_date"] = strtotime($Row["article_pubdate"]);
				}
				else
				{
					$OutData["pub_date"] = $ArticleDate + $FeedRecord["feed_delay"];
				}
			}
			else
			{
				$ArticleDate = strtotime($Row["article_pubdate"]);
				if ($ArticleDate + $MaxDelay <= $TimeStamp)
				{
					$OutData["pub_date"] = strtotime($Row["article_pubdate"]);
				}
				else
				{
					$OutData["pub_date"] = $ArticleDate + $FeedRecord["feed_delay"];
				}
			}
		}
		else
		{
			$ArticleDate = strtotime($Row["article_pubdate"]);
			if ($ArticleDate + $MaxDelay <= $TimeStamp)
			{
				$OutData["pub_date"] = strtotime($Row["article_pubdate"]);
			}
			else
			{
				$OutData["pub_date"] = $ArticleDate + $FeedRecord["feed_delay"];
			}
		}
	}

//	mysql_free_result($Result);
	mysqli_free_result($Result);
	return($OutData);
}
?>
