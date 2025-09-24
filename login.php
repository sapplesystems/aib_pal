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
	// Start sessions

	session_start();

	// Set up display data array

	$DisplayData = array(
		"page_title" => "Login",
		"popup_list" => array(
			"login_help_popup" => array("title" => "Help For: User ID",
							"heading" => "Help For: User ID",
							"text" => "Enter your user ID in this field.",
						),
			"password_help_popup" => array("title" => "Help For: Password",
							"heading" => "Help For: Password",
							"text" => "Enter your password in this field.",
						),
		),
	);

	include("template/top_public_menu.php");

	// Include the page header

include('template/common_header.php');

	// Define fields

	$FieldDef = array(
		"login" => array(
			"title" => "User ID:", "type" => "text", "display_width" => "25", "field_name" => "user_login", "field_id" => "user_login",
			"desc" => "", "help_function_name" => "login_help_popup"),
		"password" => array(
			"title" => "Password:", "type" => "password", "display_width" => "25", "field_name" => "user_pass", "field_id" => "user_pass",
			"desc" => "", "help_function_name" => "password_help_popup"),
	);

	// Define field validations

	$ValidationDef = array(
		"user_login" => array("type" => "text", "field_id" => "user_login",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter a login ID"),
					),
				),
		"user_pass" => array("type" => "password", "field_id" => "user_pass",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter a password"),
					),
				),
		);

	// Field area

?>
	<tr>
		<td align='left' valign='top'>
			<?php print(aib_gen_form_header("login","/do_login.php",false,"validate_login_form")); ?>
			<input type='hidden' name='license' value="<?php print(sprintf("%08x",time())); ?>">
			<table class='aib-input-set'>
				<?php
					print(aib_draw_input_field($FieldDef["login"]));
					print(aib_draw_input_row_separator());
					print(aib_draw_input_field($FieldDef["password"]));
					print(aib_draw_input_row_separator());
					print(aib_draw_form_submit("Login","Clear Form"));
				?>
			</table>
			</form>
		</td>
	</tr>

<?php
	// Include the footer

include('template/common_footer.php');

	// Generate validation functions

	print(aib_gen_field_validations("login","validate_login_form",$ValidationDef));

	// Other scripts
?>

<?php

include('template/common_end_of_page.php');
	exit(0);
?>
