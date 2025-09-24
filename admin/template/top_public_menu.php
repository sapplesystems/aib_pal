<?php
	// Set up display data array

	if (isset($GLOBALS["nav_source_info"]) == false)
	{
		$MenuSourceInfo = "";
	}
	else
	{
		$MenuSourceInfo = "?aibnav=".$GLOBALS["nav_source_info"];
	}

	$DropDownMenuData = array(
		"Menu" => array(
			"Browse Archives" => array("link" => "/browse.php", "help" => false),
			"Search Archives" => array("link" => "/search.php$MenuSourceInfo", "help" => false),
			"Login" => array("link" => "/login.php", "help" => false),
			),
	);

	$DisplayData["menu"]= $DropDownMenuData;


?>
