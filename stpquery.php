<?php
//
// Query from STP Archive for records
//

define('DBNAME','aibhistorical');
define('DBUSER','aibuser');
define('DBPASS','ooNgooz8');
define('DBHOST','localhost');
define('STPKEY','siivah0Ahhuovee0OoD5');


	// All data is supplied via POST method

	if (isset($argv[1]) == true)
	{
		$Key = $argv[1];
	}
	else
	{
		if (isset($_POST["k"]) == false)
		{
			$Key = "";
		}
		else
		{
			$Key = $_POST["k"];
		}
	}

	if (isset($_POST["pc"]) == true)
	{
		$PubCode = $_POST["pc"];
	}
	else
	{
		$PubCode = false;
	}

	$OutData = array();
	if ($Key == STPKEY)
	{
		$AIBDBHandle = mysqli_connect(DBHOST,DBUSER,DBPASS,DBNAME);
		if ($AIBDBHandle != false)
		{
			$Query = "SELECT item_id FROM ftree_prop WHERE property_name='aibftype' AND property_value='ag';";
			$Result = mysqli_query($AIBDBHandle,$Query);
			if ($Result != false)
			{
				while(true)
				{
					$Row = mysqli_fetch_assoc($Result);
					if ($Row == false)
					{
						break;
					}

					$ItemID = $Row["item_id"];
					if ($PubCode != false)
					{
						if (isset($ItemMap[$ItemID]) == true)
						{
							continue;
						}
					}

					$TempResult = mysqli_query($AIBDBHandle,"SELECT * FROM ftree WHERE item_id='$ItemID';");
					if ($TempResult == false)
					{
						continue;
					}

					$TempRow = mysqli_fetch_assoc($TempResult);
					mysqli_free_result($TempResult);
					$ItemTitle = rawurldecode($TempRow["item_title"]);
					if (preg_match("/[A-Za-z0-9][\+][A-Za-z0-9]/",$ItemTitle) != false)
					{
						$ItemTitle = urldecode($ItemTitle);
					}

					$OutData[$ItemID] = $ItemTitle;
				}

				mysqli_free_result($Result);
			}
		
			mysqli_close($AIBDBHandle);
		}
	}

	$OutString = json_encode($OutData);
	print($OutString);
?>
