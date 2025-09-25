<table width='100%' cellpadding='0' cellspacing='0' valign='top' align='center' class='aib-common-titlebar'>
	<tr class='aib-common-titlebar-row'>
		<td align='left' valign='top' style='width:274px;' class='aib-common-titlebar-logo-cell'>
			<img src='/images/aiblogo.png'>
		</td>
		<td align='left' style='width:25px;'> </td>
		<td align='left' valign='top' class='aib-common-titlebar-title-cell'>
<?php
		if (isset($DisplayData["page_title"]) == true)
		{
			print("<span class='aib-common-titlebar-title-span'>");
			print($DisplayData["page_title"]);
			print("</span>");
		}
?>
		</td>
		<td align='right' valign='top' class='aib-common-titlebar-group-cell'>
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

