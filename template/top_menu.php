<?php

	print("<div class='aib-dropmenu-container'>\n");
	foreach($DisplayData["menu"] as $TopItemName => $SubItemArray)
	{
		if (count(array_keys($SubItemArray)) < 1)
		{
			if (isset($SubItemArray["link"]) == true)
			{
				if ($DisplayData["current_menu"] == $TopItemName)
				{
					print("<a href=\"#\">$TopItemName</a>\n");
				}
				else
				{
					print("<a href=\"".$SubItemArray["link"]."\">$TopItemName</a>\n");
				}
			}
		}
		else
		{
			print("<div class='aib-dropmenu-dropdown'>\n");
			print("<button class='aib-dropmenu-dropbtn'>$TopItemName</button>\n");
			print("<div class='aib-dropmenu-dropdown-content'>\n");
			foreach($SubItemArray as $ItemTitle => $ItemInfo)
			{
				if ($ItemTitle == "link")
				{
					continue;
				}

				if (isset($DisplayData["current_menu"]) == true)
				{
					if ($DisplayData["current_menu"] == $ItemTitle)
					{
						print("<a href=\"#\" style='color:grey;'>$ItemTitle</a>\n");
					}
					else
					{
						print("<a href=\"".$ItemInfo["link"]."\">$ItemTitle</a>\n");
					}
				}
				else
				{
					print("<a href=\"".$ItemInfo["link"]."\">$ItemTitle</a>\n");
				}
			}

			print("</div>\n");
			print("</div>\n");
		}
	}

	print("</div>\n");

?>
