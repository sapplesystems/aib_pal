<?php
//
// do_login.php
//
//	Handles login processing.  Referring page must be the login form; any others are directed to the form
// 	again.
//

include("config/aib.php");
include("include/folder_tree.php");
include("include/aib_util.php");

// #########
// MAIN CODE
// #########

	session_start();

	// Get field data

	$FormData = aib_get_form_data();

	// If the referrer isn't the login form generator, redirect

	if (isset($_SERVER["HTTP_REFERER"]) == false)
	{
		header("Location: /login.php");
		exit(0);
	}

	$Source = $_SERVER["HTTP_REFERER"];
	if (strstr($Source,AIB_SERVER_NAME) == false)
	{
		header("Location: /login.php");
		exit(0);
	}

	// If the hidden field isn't present, redirect

	if (isset($FormData["license"]) == false)
	{
		header("Location: /login.php");
		exit(0);
	}

	// Validate login

	$Result = aib_check_login($FormData["user_login"],$FormData["user_pass"]);
	if ($Result[0] != "OK")
	{
		$ReasonCode = bin2hex($Result[1]);
		header("Location: /login_error.php?v=$ReasonCode");
		exit(0);
	}

	// If valid, then set up session and go to main page.  First, clear any
	// outstanding session data, then initialize session and redirect the
	// user to the appropriate page based on the user type

	aib_clear_session();
	aib_init_session($FormData["user_login"]);
	switch($Result[1])
	{
		// Administrators see the admin pages

		case FTREE_USER_TYPE_ADMIN:
		case FTREE_USER_TYPE_ROOT:
		case FTREE_USER_TYPE_SUBADMIN:
			header("Location: /admin_main.php");
			exit(0);

		// Everyone else sees user pages

		default:
			header("Location: /user_main.php");
			exit(0);
	}

	exit(0);

?>
