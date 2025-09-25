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
			"My Account" => array("link" => "/myaccount.php$MenuSourceInfo", "help" => false),
			"Logout" => array("link" => "/login.php", "help" => false),
			),
	);

	$DisplayData["menu"]= $DropDownMenuData;


?>
