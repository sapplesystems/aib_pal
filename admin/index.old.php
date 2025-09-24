<?php
include("config/aib.php");
include("include/aib_util.php");
include("include/folder_tree.php");
include("include/fields.php");

	$ParentInfo = aib_get_site_base_folder();
	if ($ParentInfo["parent"] == false)
	{
		if ($ParentInfo["is_management"] == true)
		{
			header("Location: /login.php");
		}
		else
		{
			header("Location: /browse.php");
		}
	}
	else
	{
		header("Location: /browse.php?parent=".$ParentInfo["parent"]);
	}

	exit(0);


?>

