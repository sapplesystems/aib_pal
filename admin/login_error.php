<?php
//
// login.php
//

// FUNCTIONAL INCLUDES

include('config/aib.php');
include('include/aib_util.php');

// #########
// MAIN CODE
// #########

	session_start();

	// Set up display data array

	$DisplayData = array(
		"page_title" => "LOGIN ERROR!",
		"popup_list" => array(),
	);

	// Include the page header

include('template/common_header.php');

	// Field area

	$FormData = aib_get_form_data();
?>
	<tr>
		<td align='center' valign='top'>
			<span style='align:center; width:100%;'>
<?php
			print("<b>");
			if (isset($FormData["v"]) != false)
			{
				print(hex2bin($FormData["v"]));
				print("<br><br>");
			}
			else
			{
				print("INVALID LOGIN");
			}

			print("</b><br><br>");
?>
			<a href='/login.php'>Click Here</a> To Continue </span>
		</td>
	</tr>

<?php
	// Include the footer

include('template/common_footer.php');

	exit(0);
