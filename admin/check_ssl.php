<?php
if (isset($_SERVER["HTTPS"]) == false)
{
	header("Location: https://www.archiveinabox.com");
	print("");
	exit(0);
}
?>
