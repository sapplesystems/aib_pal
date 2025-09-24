<!DOCTYPE html>
<html>
	<head>
<?php
		if (isset($DisplayData["page_title"]) != false)
		{
			print("<title>Archive In A Box -- ".strtoupper($DisplayData["page_title"])."</title>\n");
		}
		else
		{
			print("<title>Archive In A Box -- </title>\n");
		}
?>
		<link rel="stylesheet" href="css/aib.css">
		<script type='text/javascript' src='/jquery-3.2.0.min.js'> </script>
		<script type='text/javascript' src='/js/aib.js'> </script>
<?php
		// If there is a header script area, output here

		if (isset($DisplayData["head_script"]) != false)
		{
			print("<script>\n");
			print($DisplayData["head_script"]);
			print("</script>\n");
		}

		// If there is other header HTML, output here

		if (isset($DisplayData["header_html"]) != false)
		{
			print($DisplayData["header_html"]);
		}


		// If there are popups, output here

		if (isset($DisplayData["popup_list"]) != false)
		{
			print("<script>\n");
			foreach($DisplayData["popup_list"] as $FunctionName => $DisplayInfo)
			{
				$PopupTitle = $DisplayInfo["title"];
				$PopupHeading = $DisplayInfo["heading"];
				$PopupText = $DisplayInfo["text"];
				print(aib_generate_popup($FunctionName,500,800,$PopupTitle,$PopupHeading,$PopupText));
			}

			print("</script>\n");
		}
?>

	</head>
<?php
	if (isset($OnLoadCall) == true)
	{
		print("<body style='margin:0;' $OnLoadCall>");
	}
	else
	{
		print("<body style='margin:0;'>");
	}
?>
<?php
		if (isset($DisplayData["body_top_html"]) == true)
		{
			print($DisplayData["body_top_html"]);
		}
?>
		<table width='100%' cellpadding='0' cellspacing='0' valign='top' align='center' class='aib-common-titlebar'>
			<tr class='aib-common-titlebar-row'>
				<td align='left' valign='top' width='25%' class='aib-common-titlebar-logo-cell'>
					<img src='/images/aiblogo.png'>
				</td>
				<td align='left' style='width:25px;'>
				</td>
				<td align='center' valign='top' class='aib-common-titlebar-title-cell' width='50%'>
<?php
				if (isset($DisplayData["page_title"]) == true)
				{
					print("<span class='aib-common-titlebar-title-span' id='titlebar_span'>");
					print($DisplayData["page_title"]);
					print("</span>");
				}
?>
				</td>
				<td align='right' valign='top' class='aib-common-titlebar-group-cell' width='25%'>
<?php
				print("<span class='aib-common-titlebar-group-span'>");
				if (isset($DisplayData["primary_group_title"]) == true)
				{
					print($DisplayData["primary_group_title"]);
				}
				else
				{
					print("Archive In A Box");
				}

				print("</span>");
?>
				</td>
			</tr>
		</table>
		<div class='aib-content-div'>
		<table align='center' valign='top' cellpadding='0' cellspacing='0' class='aib-page-table'>
			<tr>
<?php
				if (isset($DisplayData["menu"]) != false)
				{
					print("<td align='center' colspan='99'>\n");
					print("<div class='aib-menu-bar-container'>");
					include("template/top_menu.php");
					print("</div>");
					print("</td>");
				}
				else
				{
					print("<td align='center' colspan='99'>\n");
					print("<td align='center'>\n");
					print("<div class='aib-menu-bar-container-blank'>");
					print("</div>");
					print("</td>");
				}
?>
			</tr>
			<tr>
				<td class='aib-left-col'>
					<div class='aib-left-content'>
<?php
				if (isset($DisplayData["left_col"]) == true)
				{
					print($DisplayData["left_col"]);
				}
?>
					</div>
				</td>
				<td class='aib-col-sep'> </td>
				<td class='aib-center-col' valign='top'>
					<table class='aib-center-table' align='left' valign='top'>
						<tr>
							<td colspan='99' height='10'> &nbsp; </td>
						</tr>

