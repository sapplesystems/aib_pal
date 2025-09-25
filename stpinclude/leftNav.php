<?php
$YearQuery="SELECT *FROM year_master";
$result=dbQuery($YearQuery);

?>
<table width="99%" border="0" bgcolor="#482400">
<?PHP
  while($row=dbFetchAssoc($result))
  {
  ?>
  <tr>
  <td bgcolor="#996600"><a href="<?php echo HOST_ROOT_PATH."Year".$row['year']."/";?>"><?php echo $row['year'];?></a></td>	
  </tr>
  <?php 
	}
	?>
  <tr>
    <td width="1px;"></td>
  </tr>
</table>
