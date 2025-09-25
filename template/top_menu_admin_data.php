<?php
	if (isset($GLOBALS["nav_source_info"]) == false)
	{
		$MenuSourceInfo = "";
	}
	else
	{
		$MenuSourceInfo = "?aibnav=".$GLOBALS["nav_source_info"];
	}

	$DisplayData["menu"]["Super Admin"] = array();
	$DisplayData["menu"]["Super Admin"]["Add Client"] = array("link" => "/archivegroup_form.php$MenuSourceInfo", "help" => false);
	$DisplayData["menu"]["Super Admin"]["Manage Client"] = array("link" => "/archivegroups.php$MenuSourceInfo", "help" => false);
	$DisplayData["menu"]["Super Admin"]["Add Client Administrator"] = array("link" => "/admin_form.php$MenuSourceInfo", "help" => false);
	$DisplayData["menu"]["Super Admin"]["Manage Client Administrators"] = array("link" => "/admins.php$MenuSourceInfo", "help" => false);
	$DisplayData["menu"]["Super Admin"]["Add Archive"] = array("link" => "/admin_archiveform.php$MenuSourceInfo", "help" => false);
	$DisplayData["menu"]["Super Admin"]["Manage Archives"] = array("link" => "/admin_archives.php$MenuSourceInfo", "help" => false);
//	$DisplayData["menu"]["Super Admin"]["Add User Group"] = array("link" => "/admin_groupform.php", "help" => false);
//	$DisplayData["menu"]["Super Admin"]["Manage User Groups"] = array("link" => "/admin_managegroup.php", "help" => false);
	$DisplayData["menu"]["Super Admin"]["Browse Archives Like User"] = array("link" => "browse.php?mode=arc", "help" => false);

?>
