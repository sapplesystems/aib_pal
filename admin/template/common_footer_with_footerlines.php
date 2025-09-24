							</td>
						</tr>
					</table>
				</td>
				<td class='aib-col-sep'>
				</td>
				<td class='aib-right-col'>
					<div class='aib-right-content'>
				<?php
					if (isset($DisplayData["right_col"]) == true)
					{
						print($DisplayData["right_col"]);
					}
				?>
					</div>
				</td>
			</tr>
		</table>
		</div>
		<table width='100%' align='left' valign='top' cellpadding='0' cellspacing = '0' class='aib-footer-table'>
			<tr><td height='20'> &nbsp; </td></tr>
			<tr>
				<td valign='bottom' align='center' class='aib-footer-copyright-cell'>
				 ArchiveInABox&reg; 2.0 &nbsp; &nbsp; Copyright &copy; <?php print(date('Y')) ?> SmallTownPapers&reg;, Inc. All Rights Reserved. <a href="/coming_soon.html" target='_blank'><b><font color='#a0000'>Open Your Own Box</font></b></a>
				</td>
			</tr>
		</table>
<?php
		if (isset($DisplayData["footer_lines"]) == true)
		{
			print($DisplayData["footer_lines"]);
		}
?>
