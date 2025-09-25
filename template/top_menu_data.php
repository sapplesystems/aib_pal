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
		"Owner" => array(
			"Home" => array("link" => "/admin_main.php", "help" => false),
			"My Account" => array("link" => "/myaccount.php$MenuSourceInfo", "help" => false),
//			"Manage Collections" => array("link" => "collections.php", "help" => false),
//			"Add Collection" => array("link" => "/collection_form.php", "help" => false),
			"Logout" => array("link" => "/login.php", "help" => false),
			),
		"Assistants" => array(
			"Add New Assistant" => array("link" => "/assistant_form.php$MenuSourceInfo", "help" => false),
			"Manage Assistants" => array("link" => "/assistants.php$MenuSourceInfo", "help" => false),
			),
		"My Archive" => array(
			"Upload / Manage Records" => array("link" => "/records.php", "help" => false),
			"Content Removal Requests" => array("link" => "#", "help" => false),
			"Contact Requests" => array("link" => "#", "help" => false),
			"Manage Fields" => array("link" => "/fields.php$MenuSourceInfo", "help" => false),
			"Manage Forms" => array("link" => "/forms.php$MenuSourceInfo", "help" => false),
		),
		"Revenue" => array(
			"Display Ads" => array("link" => "#", "help" => false),
			"Reprint Requests" => array("link" => "#", "help" => false),
		),
	);

	$DisplayData["menu"]= $DropDownMenuData;


?>
