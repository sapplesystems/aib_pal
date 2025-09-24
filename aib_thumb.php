<?php
include "stpconfig/stpconfig.php";
include "stpinclude/stpfunclib.php";

define("BASE_STORAGE_PATH","/raid2/Archive");

function send_image($FileName)
{
	$Buffer = new Imagick($FileName);
	header('Content-Type: image/' . strtolower($Buffer->getImageFormat()));
	echo $Buffer->getimageblob();
}

function filter_number_param($InValue)
{
	$OutValue = preg_replace("/[^0-9\.\+\-]/","",$InValue);
	return($OutValue);
}

// #########
// MAIN CODE
// #########

	$ServerName = $_SERVER["SERVER_NAME"];
	$ServerPort = $_SERVER["SERVER_PORT"];
	$MonthMap = array("jan" => 1, "january" => 1,
		"feb" => 2, "february" => 2,
		"mar" => 3, "march" => 3,
		"apr" => 4, "april" => 4,
		"may" => 5,
		"jun" => 6, "june" => 6,
		"jul" => 7, "july" => 7,
		"aug" => 8, "august" => 8,
		"sep" => 9, "september" => 9,
		"oct" => 10, "october" => 10,
		"nov" => 11, "november" => 11,
		"dec" => 12, "december" => 12
		);

	// Get POST and GET fields

	$FormData = array();
	foreach($_GET as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	if (isset($FormData["paper"]) == false)
	{
		print("ERROR: Missing paper");
		exit(0);
	}

	$Mode = false;
	while(true)
	{
		if (isset($FormData["edition"]) == true)
		{
			if (isset($FormData["page"]) == true)
			{
				$Mode = "page";
				break;
			}

			if (isset($FormData["year"]) == true)
			{
				$Mode = "year";
				break;
			}

			$Mode = "edition";
			break;
		}

		if (isset($FormData["year"]) == true)
		{
			$Mode = "yearonly";
			break;
		}

		break;
	}

	if ($Mode === false)
	{
		print("ERROR: BAD MODE");
		exit(0);
	}

	// Connect to the database

	$DBHandle = stp_db_connect();
	if ($DBHandle == false)
	{
		print("DATABASE ERROR: CANNOT CONNECT");
		exit(0);
	}

	// Get newspaper code from domain name of server.  The server name should be either
	// "www.TLA.stparchive.com" or "TLA.stparchive.com".

	$NewspaperCode = $FormData["paper"];
	
	// Get the publication info

	$PublicationInfo = stp_db_get_publication_info($DBHandle,$NewspaperCode);
	if ($PublicationInfo == false)
	{
		stp_error_page("DATABASE ERROR","Cannot find publication.  Please try again later.");
		exit(0);
	}

	$PublicationProperties = stp_db_get_publication_properties($DBHandle,$NewspaperCode);

	// Process based on mode

	switch($Mode)
	{
		case "page":
			$EditionID = filter_number_param($FormData["edition"]);
			$EditionRecord = stp_db_get_edition_from_id($DBHandle,$EditionID);
			if ($EditionRecord == false)
			{
				print("ERROR: NO SUCH EDITION");
				break;
			}

			// Get pages in edition, sorted by sort order spec

			$EditionPages = stp_db_get_edition_pages($DBHandle,$EditionID);
			if ($EditionPages == false)
			{
				print("ERROR: NO PAGES IN EDITION");
				break;
			}

			// Get page by name

			$PageNumber = filter_number_param($FormData["page"]);
			$PageRecord = false;
			foreach($EditionPages as $TempRecord)
			{
				if ($PageNumber == $TempRecord["page_name"])
				{
					$PageRecord = $TempRecord;
					break;
				}
			}

			if ($PageRecord == false)
			{
				print("ERROR: PAGE NOT FOUND");
				break;
			}

			$PageName = preg_replace("/[\.][^\n]+$/","",$PageRecord["pagefile"]);
			$FileName = BASE_STORAGE_PATH."/".strtoupper($NewspaperCode)."/".$PageName."_thumb.gif";
			send_image($FileName);
			break;

		case "year":
		case "yearonly":
			$Year = filter_number_param($FormData["year"]);
			if (isset($FormData["edition_id"]) == false)
			{
				$EditionList = stp_db_get_publication_editions($DBHandle,strtoupper($NewspaperCode),$Year,true);
				if ($EditionList == false)
				{
					print("ERROR: NO EDITIONS FOR $NewspaperCode,$Year");
					break;
				}

				$EditionRecord = $EditionList[0];
				$EditionID = $EditionRecord["edition_id"];
			}
			else
			{
				$EditionID = $FormData["edition_id"];
			}

			// Get pages in edition, sorted by sort order spec

			$EditionPages = stp_db_get_edition_pages($DBHandle,$EditionID);
			if ($EditionPages == false)
			{
				print("ERROR: NO PAGES IN EDITION");
				break;
			}

			$PageRecord = $EditionPages[0];
			if ($PageRecord == false)
			{
				print("ERROR: PAGE NOT FOUND");
				break;
			}

			$PageName = preg_replace("/[\.][^\n]+$/","",$PageRecord["pagefile"]);
			$FileName = BASE_STORAGE_PATH."/".strtoupper($NewspaperCode)."/".$PageName."_thumb.gif";
			send_image($FileName);
			break;


		case "edition":
			$EditionID = filter_number_param($FormData["edition"]);
			$EditionRecord = stp_db_get_edition_from_id($DBHandle,$EditionID);
			if ($EditionRecord == false)
			{
				print("ERROR: NO SUCH EDITION");
				break;
			}

			// Get pages in edition, sorted by sort order spec

			$EditionPages = stp_db_get_edition_pages($DBHandle,$EditionID);
			if ($EditionPages == false)
			{
				print("ERROR: NO PAGES IN EDITION");
				break;
			}

			$PageRecord = $EditionPages[0];
			if ($PageRecord == false)
			{
				print("ERROR: PAGE NOT FOUND");
				break;
			}

			$PageName = preg_replace("/[\.][^\n]+$/","",$PageRecord["pagefile"]);
			$FileName = BASE_STORAGE_PATH."/".strtoupper($NewspaperCode)."/".$PageName."_thumb.gif";
			send_image($FileName);
			break;

		default:
			break;
	}

	stp_db_disconnect($DBHandle);
	exit(0);

	// Get the list of pages

?>

