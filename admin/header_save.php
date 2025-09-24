if ($_SERVER["REMOTE_ADDR"] != "216.235.107.33")
{
	header("Location: /default");
	print("");
	exit(0);
}
