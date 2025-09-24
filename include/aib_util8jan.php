<?php
//
// aib_util.php
//
//	Utility functions for AIB
//

function aibutil_log_debug($Msg)
{
	$Handle = fopen("/tmp/aibutil_debug.txt","a+");
	if ($Handle !== false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function aib_print_stderr($Msg)
{
	$Handle = fopen("php://stderr","a+");
	fputs($Handle,$Msg."\n");
	fclose($Handle);
}

// Multiple-round URL decode
// -------------------------
function aib_urldecode($InString)
{
	$OutString = urldecode($InString);
	for ($Counter = 0; $Counter < 3; $Counter++)
	{
		if (preg_match("/[A-Za-z][\+][A-Za-z]/",$OutString) == false)
		{
			if (preg_match("/[\%][0-9A-Fa-f][0-9A-Fa-f]/",$OutString) == false)
			{
				return($OutString);
			}
		}

		$OutString = urldecode($OutString);
	}

	for ($Counter = 0; $Counter < 3; $Counter++)
	{
		if (preg_match("/[A-Za-z][\+][A-Za-z]/",$OutString) == false)
		{
			if (preg_match("/[\%][0-9A-Fa-f][0-9A-Fa-f]/",$OutString) == false)
			{
				return($OutString);
			}
		}

		$OutString = rawurldecode($OutString);
	}

	return($OutString);
}

function aib_check_session()
{
	session_start();
	if (isset($_SESSION["aib_historical"]) == false)
	{
		return(array("ERROR","SESSION HAS EXPIRED"));
	}

	$SessionString = urldecode($_SESSION["aib_historical"]);
	$SessionData = json_decode($SessionString,true);
	if (isset($GLOBALS["aib_session_timeout"]) == false)
	{
		$Timeout = (60 * 60 * 8);
	}
	else
	{
		$Timeout = $GLOBALS["aib_session_timeout"];
	}

	if (isset($SessionData["init"]) == false)
	{
		return(array("ERROR","NO INIT"));
	}

	if (isset($SessionData["recent"]) == false)
	{
		$StartTime = $SessionData["init"];
	}
	else
	{
		$StartTime = $SessionData["recent"];
	}

	$CurrentTime = time();
	$DeltaTime = $CurrentTime - $StartTime;
	if ($DeltaTime > $Timeout)
	{
		return(array("ERROR","SESSION HAS EXPIRED"));
	}

	$SessionData["recent"] = $CurrentTime;
	$SessionString = urlencode(json_encode($SessionData));
	$_SESSION["aib_historical"] = $SessionString;
	if (setcookie("aib_page_id",md5(md5(date("YmdHis"))),time() + 86000,"/",AIB_DOMAIN,false,false) == false)
	{
		return(array("ERROR","CANNOT SET PAGE ID COOKIE; PLEASE ENABLE COOKIES FOR THIS WEB SITE"));
	}

	return(array("OK",$SessionData));
}


function aib_clear_session()
{
	if (isset($_SESSION["aib_historical"]) == true)
	{
		unset($_SESSION["aib_historical"]);
	}

	if (isset($_SESSION["aib_page_id"]) == true)
	{
		unset($_SESSION["aib_page_id"]);
	}

	return(true);
}

// Initialize session

function aib_init_session($LoginID)
{
	if (isset($GLOBALS["aib_session_timeout"]) == false)
	{
		$Timeout = (60 * 60 * 8);
	}
	else
	{
		$Timeout = $GLOBALS["aib_session_timeout"];
	}

	$SessionData = array(
		"init" => time(),
		"timeout" => $Timeout,
		"login" => $LoginID,
		"recent" => time()
	);

	$SessionString = urlencode(json_encode($SessionData));
	$_SESSION["aib_historical"] = $SessionString;
	$_SESSION["aib_page_id"] = md5(md5(date("YmdHis")));
	return(true);
}

// Get form data

function aib_get_form_data()
{
	$FormData = array();
	foreach($_GET as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	return($FormData);
}

// Get true/false option from array
// --------------------------------
function aib_truefalse_option($InArray,$Name,$Default)
{
	if (isset($InArray[$Name]) == false)
	{
		return($Default);
	}

	$Value = $InArray[$Name];
	if (preg_match("/[Yy]/",$Value) != false)
	{
		return(true);
	}

	return(false);
}

// Get user data
// -------------
function aib_get_user_info($LoginID)
{
	$DBHandle = mysqli_connect(AIB_DB_HOST,AIB_DB_USER,AIB_DB_PASS);
	if ($DBHandle == false)
	{
		return(array("ERROR","Cannot connect to database"));
	}

	mysqli_select_db($DBHandle,AIB_DB_NAME);
	$Result = mysqli_query($DBHandle,"SELECT * FROM ftree_user WHERE user_login='$LoginID';");
	if ($Result == false)
	{
		mysqli_close($DBHandle);
		return(array("ERROR","USERDBQUERYFAIL"));
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		mysqli_close($DBHandle);
		return(array("ERROR","BADREQUESTUSER"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	return(array("OK",$Row));
}

// Check a login based on user ID, password and type

function aib_check_login($LoginID,$Password)
{
	$DBHandle = mysqli_connect(AIB_DB_HOST,AIB_DB_USER,AIB_DB_PASS);
	if ($DBHandle == false)
	{
		return(array("ERROR","Cannot connect to database"));
	}

	mysqli_select_db($DBHandle,AIB_DB_NAME);
	$Result = mysqli_query($DBHandle,"SELECT * FROM ftree_user WHERE user_login='$LoginID';");
	if ($Result == false)
	{
		mysqli_close($DBHandle);
		return(array("ERROR","Database query failed"));
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		mysqli_close($DBHandle);
		return(array("ERROR","Invalid login or password"));
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	$EncodedPassword = aib_encode_password($Password);
	if ($Row["user_pass"] != $EncodedPassword)
	{
		return(array("ERROR","Invalid login or password"));
	}

	$OutArray = array();
	$OutArray[] = "OK";
	$OutArray[] = $Row["user_type"];
	$OutArray[] = $Row["user_title"];
	return($OutArray);
}

// Connect to database
// -------------------
function aib_open_db()
{
	$GLOBALS["aib_db"] = mysqli_connect(AIB_DB_HOST,AIB_DB_USER,AIB_DB_PASS);
	if ($GLOBALS["aib_db"] == false)
	{
		unset($GLOBALS["aib_db"]);
		return(false);
	}

	mysqli_select_db($GLOBALS["aib_db"],AIB_DB_NAME);
	return(true);
}

// Disconnect from database
// ------------------------
function aib_close_db()
{
	if (isset($GLOBALS["aib_db"]) != false)
	{
		mysqli_close($GLOBALS["aib_db"]);
		unset($GLOBALS["aib_db"]);
	}

	return(true);
}

// Escape string for mySQL
// -----------------------
function aib_mysql_escape_string($InString)
{
	return(mysqli_real_escape_string($InString));
}

// Log a message
// -------------
function aib_log_message($Type,$ModuleName,$Msg)
{
	$Handle = false;
	switch($Type)
	{
		case "SECURITY":
			$Handle = fopen(AIB_SECURITY_LOG,"a+");
			break;

		case "ERROR":
			$Handle = fopen(AIB_ERROR_LOG,"a+");

		default:
			break;
	}

	if ($Handle != false)
	{
		$DateString = date("Y,m,d,H,i,s").sprintf("%0.6f",microtime(true));
		fputs($Handle,$DateString."\t".$ModuleName."\t".$Msg."\n");
		fclose($Handle);
	}

}

// Do a database query, returning false on error or empty set.
// -----------------------------------------------------------
function aib_db_query($Query)
{
	if (isset($GLOBALS["aib_db"]) == false)
	{
		return(false);
	}

	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}

	mysqli_free_result($Result);
	return($OutList);
}

// Count number of records
// -----------------------
function aib_db_count($TableName,$Query)
{
	if ($Query == false)
	{
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT count(*) FROM $TableName;");
	}
	else
	{
		$Result = mysqli_query($GLOBALS["aib_db"],"SELECT count(*) FROM $TableName WHERE $Query;");
	}

	if ($Result == false)
	{
		return(0);
	}

	$Row = mysqli_fetch_row($Result);
	mysqli_free_result($Result);
	return($Row[0]);
}

// Encode a password
// -----------------
function aib_encode_password($InPass)
{
	$LocalPass = md5($InPass);
	for ($Counter = 0; $Counter < 20; $Counter++)
	{
		$LocalPass = md5($LocalPass);
	}

	return($LocalPass);
}



// Get a value from assoc array with default
// -----------------------------------------
function aib_get_with_default($ArrayIn,$Name,$Default)
{
	if (isset($ArrayIn[$Name]) == false)
	{
		return($Default);
	}

	return($ArrayIn[$Name]);
}

// Generate a popup window
// -----------------------
function aib_generate_popup($Name,$Width,$Height,$Title,$Heading,$Text)
{
	$OutLines = array();
	$WindowName = $Name."Window";
	$OutLines[] = "function $Name"."() {";
	$OutLines[] = "LocalWindow = window.open('','linkpopup','height=$Height,width=$Width,titlebar=0');";
	$OutLines[] = "var Tmp = LocalWindow.document;";
	$OutLines[] = "Tmp.write('<html><head><title>$Title</title></head><body>');";
	$OutLines[] = "Tmp.write('<h2>$Heading</h2>');";
	$OutLines[] = "Tmp.write('<p><font face=\"arial,helvetica,universe\">');";
	$TempLines = explode("\n",$Text);
	foreach($TempLines as $TempLine)
	{
		$OutLines[] = "Tmp.write('$TempLine');";
	}

	$OutLines[] = "Tmp.write('</font></p>');";
	$OutLines[] = "Tmp.write('<p><a href=\"javascript:self.close()\">CLOSE THIS WINDOW</a></p>');";
	$OutLines[] = "Tmp.write('</body></html>');";
	$OutLines[] = "Tmp.close();";
	$OutLines[] = "}";
	$OutLines[] = "";
	return(join("\n",$OutLines));
}

// Draw an error message.
// ----------------------
function aib_draw_error_message($Msg)
{
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-form-error-message' colspan='99'>
										<span class='aib-form-error-message-span'>$Msg</span>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	return($TemplateText);
}

// Draw a status message.
// ----------------------
function aib_draw_status_message($Msg)
{
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-form-status-message' colspan='99'>
										<span class='aib-form-status-message-span'>$Msg</span>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	return($TemplateText);
}

// Draw an input field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// display_width
// data_size
// help_function_name
// input_under		If "Y", data field goes under title.
//
// Classes:
//
//	class_field_area_row
//	class_field_area_cell
//	class_aib_input_box
//	class_aib_help_link
//	class_aib_input_field_row
//	class_aib_input_field_title_cell
//	class_aib_input_title_box
//	class_aib_input_title_divider_cell
//	class_aib_input_field_cell
//	class_aib_input_field_box
//	class_aib_text_input
//	class_aib_input_explain_divide_cell
//	class_aib_input_explain_cell
//	class_aib_input_explain_box
// -------------------------------------------
function aib_draw_input_field($FieldDef)
{
	$Class_field_area_row = aib_get_with_default($FieldDef,"class_field_area_row","field-area-row");
	$Class_field_area_cell = aib_get_with_default($FieldDef,"class_field_area_cell","field-area-cell");
	$Class_aib_input_box = aib_get_with_default($FieldDef,"class_aib_input_box","aib-input-box");
	$Class_aib_input_table = aib_get_with_default($FieldDef,"class_aib_input_table","aib-input-table");
	$Class_aib_input_field_row = aib_get_with_default($FieldDef,"class_aib_input_field_row","aib-input-field-row");
	$Class_aib_input_field_title_cell = aib_get_with_default($FieldDef,"class_aib_input_field_title_cell","aib-input-field-title-cell");
	$Class_aib_input_field_cell = aib_get_with_default($FieldDef,"class_aib_input_field_cell","aib-input-field-cell");
	$Class_aib_input_title_box = aib_get_with_default($FieldDef,"class_aib_input_title_box","aib-input-title-box");
	$Class_aib_input_field_box = aib_get_with_default($FieldDef,"class_aib_input_field_box","aib-input-field-box");
	$Class_aib_input_title_divider_cell = aib_get_with_default($FieldDef,"class_aib_input_title_divider_cell","aib-input-title-divider-cell");
	$Class_aib_input_explain_divide_cell = aib_get_with_default($FieldDef,"class_aib_input_explain_divide_cell","aib-input-explain-divide-cell");
	$Class_aib_input_explain_cell = aib_get_with_default($FieldDef,"class_aib_input_explain_cell","aib-input-explain-cell");
	$Class_aib_input_explain_box = aib_get_with_default($FieldDef,"class_aib_input_explain_box","aib-input-explain-box");
	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	if (aib_get_with_default($FieldDef,"input_under","N") == "N")
	{
		$TemplateText = "
				<tr class='$Class_field_area_row'>
					<td class='$Class_field_area_cell'>
						<div class='$Class_aib_input_box' id='input-box-login-id'>
							<table class='$Class_aib_input_table'>
								<tr class='$Class_aib_input_field_row'>
									<td class='$Class_aib_input_field_title_cell'>
										<div class='$Class_aib_input_title_box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='$Class_aib_input_title_divider_cell'> </td>
									<td class='$Class_aib_input_field_cell'>
										<div class='$Class_aib_input_field_box' id='input-box-[[FIELDNAME]]'>
											<input name='[[FIELDNAME]]' class='aib-text-input' id='[[FIELDID]]' [[DISPLAYSIZE]] [[DATASIZE]] type='[[FIELDTYPE]]' value=\"[[FIELDVALUE]]\">
										</div>
									</td>
									<td width='5' class='$Class_aib_input_explain_divide_cell'> </td>
									<td class='$Class_aib_input_explain_cell'>
										<div class='$Class_aib_input_explain_box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
		";
	}
	else
	{
		$TemplateText = "
				<tr class='$Class_field_area_row'>
					<td class='$Class_field_area_cell'>
						<div class='$Class_aib_input_box' id='input-box-login-id'>
							<table class='$Class_aib_input_table'>
								<tr class='$Class_aib_input_field_row'>
									<td class='$Class_aib_input_field_title_cell'>
										<div class='$Class_aib_input_title_box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='$Class_aib_input_explain_divide_cell'> </td>
									<td class='$Class_aib_input_explain_cell'>
										<div class='$Class_aib_input_explain_box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
								<tr class='$Class_aib_input_field_row'>
									<td class='$Class_aib_input_field_cell' colspan='3'>
										<div class='$Class_aib_input_field_box' id='input-box-[[FIELDNAME]]'>
											<input name='[[FIELDNAME]]' class='aib-text-input' id='[[FIELDID]]' [[DISPLAYSIZE]] [[DATASIZE]] type='[[FIELDTYPE]]' value=\"[[FIELDVALUE]]\">
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
		";
	}


	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[DISPLAYSIZE]]" => "display_width",
		"[[DATASIZE]]" => "data_size",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[DISPLAYSIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"size='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[DATASIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"maxlength='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a checkbox field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// value
// display_width
// data_size
// help_function_name
// checked
// side_title	Goes immediately next to check box instead of title column
// javascript -- extra JavaScript code
// -------------------------------------------
function aib_draw_checkbox_field($FieldDef)
{
	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-title-cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											<input type='checkbox' name='[[FIELDNAME]]' class='aib-checkbox-input' id='[[FIELDID]]' value=\"[[FIELDVALUE]]\" [[CHECKED]] [[JAVASCRIPT]]> <span id='[[FIELDNAME]-sidetitle'>[[SIDETITLE]]</span>
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		"[[CHECKED]]" => "checked",
		"[[SIDETITLE]]" => "side_title",
		"[[JAVASCRIPT]]" => "javascript",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a radiobutton field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// value
// display_width
// data_size
// help_function_name
// checked
// left_title			Left-side title, if present
// javascript -- extra javascript for field
// title_style -- Extra title style
// -------------------------------------------
function aib_draw_radio_field($FieldDef)
{
	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-title-cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											[[LEFTTITLE]] <input type='radio' name='[[FIELDNAME]]' class='aib-checkbox-input' id='[[FIELDID]]' value=\"[[FIELDVALUE]]\" [[CHECKED]] [[JAVASCRIPT]]> <span id='[[FIELDNAME]]-righttitle' style='[[TITLESTYLE]]'>[[TITLE]]</span>
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		"[[CHECKED]]" => "checked",
		"[[LEFTTITLE]]" => "left_title",
		"[[JAVASCRIPT]]" => "javascript",
		"[[TITLESTYLE]]" => "title_style",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a field surround and add text.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// display_width
// data_size
// help_function_name
// fielddata			HTML to display in field area
// -------------------------------------------
function aib_draw_custom_field($FieldDef)
{
	$AIBInputTableClass = "aib-input-table";
	if (isset($FieldDef["input_table_class"]) == true)
	{
		$AIBInputTableClass = $FieldDef["input_table_class"];
	}

	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='$AIBInputTableClass'>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-title-cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											[[FIELDDATA]]
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[FIELDATA]]" => "fielddata",
		"[[DISPLAYSIZE]]" => "display_width",
		"[[DATASIZE]]" => "data_size",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		"[[FIELDDATA]]" => "fielddata",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[DISPLAYSIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"size='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[DATASIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"maxlength='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[FIELDDATA]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;

			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a dropdown field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// display_width
// data_size
// help_function_name
// value		Current value (default selection);
// option_list		List of option values where
//			key is value, data is title.
// script		Any script code
// -------------------------------------------
function aib_draw_dropdown_field($FieldDef)
{
	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-title-cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											<select name='[[FIELDNAME]]' class='aib-dropdown' id='[[FIELDID]]' [[SCRIPT]] >
		";

	$TempLines = array();
	if (isset($FieldDef["value"]) != false)
	{
		$DefaultValue = $FieldDef["value"];
	}
	else
	{
		$DefaultValue = false;
	}

	foreach($FieldDef["option_list"] as $OptionValue => $OptionTitle)
	{
		if ($DefaultValue === false)
		{
			$TempLines[] = "<option value=\"$OptionValue\">$OptionTitle</option>";
		}
		else
		{
			if ($OptionValue == $DefaultValue)
			{
				$TempLines[] = "<option value=\"$OptionValue\" SELECTED >$OptionTitle</option>";
			}
			else
			{
				$TempLines[] = "<option value=\"$OptionValue\">$OptionTitle</option>";
			}
		}
	}

	$TemplateText .= join("\n",$TempLines);
	$TemplateText .= "								</select>
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[DISPLAYSIZE]]" => "display_width",
		"[[DATASIZE]]" => "data_size",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		"[[SCRIPT]]" => "script",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[DISPLAYSIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"size='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[DATASIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"maxlength='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a list box field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// rows
// display_width
// data_size
// help_function_name
// value		Current value (default selection);
// option_list		List of option values where
//			key is value, data is title.
// script		Any script code
// -------------------------------------------
function aib_draw_listbox_field($FieldDef)
{
	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-title-cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											<select name='[[FIELDNAME]]' class='aib-dropdown' id='[[FIELDID]]' [[SCRIPT]] [[ROWS]] >
		";

	$TempLines = array();
	if (isset($FieldDef["value"]) != false)
	{
		$DefaultValue = $FieldDef["value"];
	}
	else
	{
		$DefaultValue = false;
	}

	if (isset($FieldDef["option_list"]) == true)
	{
		foreach($FieldDef["option_list"] as $OptionValue => $OptionTitle)
		{
			if ($DefaultValue === false)
			{
				$TempLines[] = "<option value=\"$OptionValue\">$OptionTitle</option>";
			}
			else
			{
				if ($OptionValue == $DefaultValue)
				{
					$TempLines[] = "<option value=\"$OptionValue\" SELECTED >$OptionTitle</option>";
				}
				else
				{
					$TempLines[] = "<option value=\"$OptionValue\">$OptionTitle</option>";
				}
			}
		}
	}

	$TemplateText .= join("\n",$TempLines);
	$TemplateText .= "								</select>
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[DISPLAYSIZE]]" => "display_width",
		"[[DATASIZE]]" => "data_size",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		"[[SCRIPT]]" => "script",
		"[[ROWS]]" => "rows",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[DISPLAYSIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"size='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[ROWS]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"size='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[DATASIZE]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"maxlength='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a container for user-defined fields
// ----------------------------------------
function aib_draw_user_def_field_area($TableName,$DefaultFields = false)
{
	$OutRows = array();
	$OutRows[] = "
				<tr class='user-area-row'>
					<td class='user-area-cell'>
						<table class='aib-user-field-area-table' id='$TableName' name='$TableName'>
							<tbody>
		";
	if ($DefaultFields != NULL)
	{
		foreach($DefaultFields as $DefaultFieldRecord)
		{
			$FieldDef = $DefaultFieldRecord["def"];
			$FieldValue = urldecode($DefaultFieldRecord["value"]);
			if (preg_match("/[A-Za-z][\+][A-Za-z]/",$FieldValue) != false)
			{
				$FieldValue = urldecode($FieldValue);
			}

			$RowName = "userfield_".$FieldDef["field_id"]."_field";
			$HTMLFieldID = "userfield_".$FieldDef["field_id"]."_field";
			$OutRows[] = "<tr class='aib-user-def-field-row' id='$RowName'><td class='aib-user-def-field-title-cell'>";
			$OutRows[] = urldecode($FieldDef["field_title"])."</td>";
			$OutRows[] = "<td class='aib-user-def-field-input-cell'>";
			switch($FieldDef["field_data_type"])
			{
				case FTREE_FIELD_TYPE_TEXT:
				case FTREE_FIELD_TYPE_INTEGER:
				case FTREE_FIELD_TYPE_DATE:
				case FTREE_FIELD_TYPE_TIME:
					$Size = $FieldDef["field_size"];
					if (intval($Size) <= 0)
					{
						$Size = 32;
					}

					$OutRows[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value='$FieldValue' size='$Size' class='aib-record-text-input-field'>";
					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'> </td></tr>";
					break;

				case FTREE_FIELD_TYPE_DATETIME:
					$Size = $FieldRecord["field_size"];
					if (intval($Size) <= 0)
					{
						$Size = 32;
					}

					$OutRows[] = "<input type='datetime' name='$HTMLFieldID' id='$HTMLFieldID' value='$FieldValue' size='$Size' class='aib-record-datetime-input-field'>";
					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'> </td></tr>";
					break;

				case FTREE_FIELD_TYPE_TIMESTAMP:
					$LocalValue = time();
					$OutRows[] = "<input type='hidden' name='$HTMLFieldID' id='$HTMLFieldID' value='$FieldValue' class='aib-record-timestamp-input-field'>";
					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
					break;

				case FTREE_FIELD_TYPE_BIGTEXT:
					$Segs = preg_split("/[ \,\/\:\;]+/",$FieldDef["field_size"]);
					$Rows = 5;
					$Cols = 40;
//					if (count($Segs) == 1)
//					{
//						$Cols = $Segs[0];
//					}
//					else
//					{
//						if (count($Segs) > 1)
//						{
//							$Rows = $Segs[0];
//							$Cols = $Segs[1];
//						}
//					}

					$OutRows[] = "<textarea name='$HTMLFieldID' id='$HTMLFieldID' rows='$Rows' cols='$Cols' class='aib-record-textarea-input-field'>$FieldValue</textarea>";
					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
					break;

				case FTREE_FIELD_TYPE_FLOAT:
				case FTREE_FIELD_TYPE_DECIMAL:
					$Segs = preg_split("/[ \,\/\:\;]+/",$FieldDef["field_size"]);
					$Size = 10;
					if (count($Segs) >= 1)
					{
						$Size = $Segs[0];
					}

					$OutRows[] = "<input type='text' name='$HTMLFieldID' id='$HTMLFieldID' value='$FieldValue' size='$Size' class='aib-record-floatdecimal-input-field'>";
					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
					break;

				case FTREE_FIELD_TYPE_DROPDOWN:
					$RawList = explode("\n",urldecode($FieldDef["field_format"]));
					$OptionList = array();
					foreach($RawList as $OptionLine)
					{
						$Segs = explode("=",$OptionLine);
						if (count($Segs) < 1)
						{
							continue;
						}

						if (count($Segs) < 2)
						{
							$OptionList[$Segs[0]] = $Segs[0];
						}
						else
						{
							$OptionList[$Segs[0]] = $Segs[1];
						}
					}

					$Rows[] = "<select name='$HTMLFieldID' id='$HTMLFieldID' class='aib-record-dropdown-input-field'>";
					foreach($Rows as $Value => $Desc)
					{
						if ($Value == $FieldValue)
						{
							$OutRows[] = "<option value=\"$Value\" SELECTED>$Desc</option>";
						}
						else
						{
							$OutRows[] = "<option value=\"$Value\">$Desc</option>";
						}
					}

					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
					break;

				default:
					$OutRows[] = "<input type='text' name='$HTMLFieldID' value='$FieldValue' size='".$FieldRecord["field_size"]."'>";
					$OutRows[] = "</td>";
					$OutRows[] = "<td class='aib-user-def-field-desc-cell'></td></tr>";
					break;
			}
		}
	}

	$OutRows[] = "
							</tbody>
						</table>
					</td>
				</tr>
		";

	return(join("\n",$OutRows));
}

// Draw a textarea field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// rows
// cols
// help_function_name
// value		Current value (default selection);
// option_list		List of option values where
//			key is value, data is title.
// input_under		If "Y", data field goes under title.
// -------------------------------------------
function aib_draw_textarea_field($FieldDef)
{
	$Class_field_area_row = aib_get_with_default($FieldDef,"class_field_area_row","field-area-row");
	$Class_field_area_cell = aib_get_with_default($FieldDef,"class_field_area_cell","field-area-cell");
	$Class_aib_input_box = aib_get_with_default($FieldDef,"class_aib_input_box","aib-input-box");
	$Class_aib_input_table = aib_get_with_default($FieldDef,"class_aib_input_table","aib-input-table");
	$Class_aib_input_field_row = aib_get_with_default($FieldDef,"class_aib_input_field_row","aib-input-field-row");
	$Class_aib_input_field_title_cell = aib_get_with_default($FieldDef,"class_aib_input_field_title_cell","aib-input-field-title-cell");
	$Class_aib_input_field_cell = aib_get_with_default($FieldDef,"class_aib_input_field_cell","aib-input-field-cell");
	$Class_aib_input_title_box = aib_get_with_default($FieldDef,"class_aib_input_title_box","aib-input-title-box");
	$Class_aib_input_field_box = aib_get_with_default($FieldDef,"class_aib_input_field_box","aib-input-field-box");
	$Class_aib_input_title_divider_cell = aib_get_with_default($FieldDef,"class_aib_input_title_divider_cell","aib-input-title-divider-cell");
	$Class_aib_input_explain_divide_cell = aib_get_with_default($FieldDef,"class_aib_input_explain_divide_cell","aib-input-explain-divide-cell");
	$Class_aib_input_explain_cell = aib_get_with_default($FieldDef,"class_aib_input_explain_cell","aib-input-explain-cell");
	$Class_aib_input_explain_box = aib_get_with_default($FieldDef,"class_aib_input_explain_box","aib-input-explain-box");

	$HelpTemplateText = "
										<a class='aib-help-link' href='javascript:[[HELPNAME]]();' tabindex='-1'>?</a>\n";
	if (aib_get_with_default($FieldDef,"input_under","N") == "N")
	{
		$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='$Class_aib_input_field_title_cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											<textarea name='[[FIELDNAME]]' class='aib-textarea' id='[[FIELDID]]' [[ROWS]] [[COLS]]>[[FIELDVALUE]]</textarea>
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
		";
	}
	else
	{
		$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='$Class_aib_input_field_title_cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[HELPLINK]]
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-cell' colspan='3'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											<textarea name='[[FIELDNAME]]' class='aib-textarea' id='[[FIELDID]]' [[ROWS]] [[COLS]]>[[FIELDVALUE]]</textarea>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
		";
	}


	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[HELPNAME]]" => "help_function_name",
		"[[TITLE]]" => "title",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		"[[ROWS]]" => "rows",
		"[[COLS]]" => "cols",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			case "[[ROWS]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"rows='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[COLS]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,"cols='".$FieldDef[$FieldDefString]."'",$TemplateText);
				}

				break;

			case "[[HELPNAME]]":
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
				}
				else
				{
					if ($FieldDef[$FieldDefString] == "")
					{
						$TemplateText = str_replace("[[HELPLINK]]","",$TemplateText);
						break;
					}

					$TempText = str_replace($TemplateString,$FieldDef[$FieldDefString],$HelpTemplateText);
					$TemplateText = str_replace("[[HELPLINK]]",$TempText,$TemplateText);
				}

				break;

			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

// Draw a display-only field.  Input array keys are:
// type (text or password)
// field_id
// field_name
// title
// desc
// display_width
// -------------------------------------------
function aib_draw_display_field($FieldDef)
{
	$TemplateText = "
				<tr class='field-area-row'>
					<td class='field-area-cell'>
						<div class='aib-input-box' id='input-box-login-id'>
							<table class='aib-input-table'>
								<tr class='aib-input-field-row'>
									<td class='aib-input-field-title-cell'>
										<div class='aib-input-title-box' id='title-box-[[FIELDNAME]]'>
										[[TITLE]]
										</div>
									</td>
									<td width='5' class='aib-input-title-divider-cell'> </td>
									<td class='aib-input-field-cell'>
										<div class='aib-input-field-box' id='input-box-[[FIELDNAME]]'>
											<input name='[[FIELDNAME]]' id='[[FIELDID]]' type='hidden' value=\"[[FIELDVALUE]]\">
											<span class='aib-display-field'>[[FIELDVALUE]]</span>
										</div>
									</td>
									<td width='5' class='aib-input-explain-divide-cell'> </td>
									<td class='aib-input-explain-cell'>
										<div class='aib-input-explain-box' id='explain-box-[[FIELDNAME]]'>[[DESCRIPTION]]
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
	";

	$TranslationTable = array(
		"[[FIELDNAME]]" => "field_name",
		"[[FIELDID]]" => "field_id",
		"[[DESCRIPTION]]" => "desc",
		"[[TITLE]]" => "title",
		"[[FIELDTYPE]]" => "type",
		"[[FIELDVALUE]]" => "value",
		);
	foreach($TranslationTable as $TemplateString => $FieldDefString)
	{
		switch($TemplateString)
		{
			default:
				if (isset($FieldDef[$FieldDefString]) == false)
				{
					$TemplateText = str_replace($TemplateString,"",$TemplateText);
				}
				else
				{
					$TemplateText = str_replace($TemplateString,$FieldDef[$FieldDefString],$TemplateText);
				}

				break;
		}
	}

	return($TemplateText);
}

function aib_draw_input_row_separator()
{
	return("
				<tr class='field-area-row'>
					<td class='field-area-sep' height='5'> </td>
				</tr>
		");

}

function aib_draw_form_submit($SubmitText,$ResetText,$Class_aib_submit_button = "aib-submit-button", $Class_aib_reset_button = "aib-reset-button")
{
	$TemplateText = "
				<tr class='button-area-row'>
					<td class='button-area-cell'>
						<div class='aib-button-box' id='button-box-pass-id'>
							<table class='aib-button-table'>
								<tr class='aib-button-field-row'>
									<td class='aib-button-field-title-cell'>
										<div class='aib-button-title-box' id='title-box-form'>
										</div>
									</td>
									<td width='5' class='aib-button-title-divider-cell'> </td>
									<td class='aib-button-field-cell'>
										<div class='aib-button-field-box' id='input-box-form'>
											<input type='submit' class='$Class_aib_submit_button' value='[[SUBMITTEXT]]' id='aib_submit_button'> ";
	if ($ResetText != "")
	{
		if (substr($ResetText,0,5) == "link|")
		{
			$ResetSeg = explode("|",$ResetText);
			$LocalURL = $ResetSeg[1];
			$LocalTitle = $ResetSeg[2];
			$TemplateText .= " &nbsp; &nbsp; <a href=\"$LocalURL\">$LocalTitle</a> ";
		}
		else
		{
			$TemplateText .= " &nbsp; &nbsp; <input type='reset' class='$Class_aib_reset_button' value='[[RESETTEXT]]' id='aib_reset_button'> ";
		}
	}

	$TemplateText .= "
										</div>
									</td>
									<td width='5' class='aib-button-explain-divide-cell'> </td>
									<td class='aib-button-explain-cell'>
										<div class='aib-button-explain-box' id='explain-box-form'>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td class='field-area-button-sep' height='10'> </td>
				</tr>

	";

	$TemplateText = str_replace("[[SUBMITTEXT]]",$SubmitText,$TemplateText);
	$TemplateText = str_replace("[[RESETTEXT]]",$ResetText,$TemplateText);
	return($TemplateText);
}

// Show chaining link form.  Target list is a list of records, where
// each record contains "url" and a sub-array of field values, where
// the key is the field name and the value is the field value.  Each
// target also contains a "title":
//
//	url => Target URL
//	title => Title of URL link
//	fields => array
//		fieldname => fieldvalue
//
// -----------------------------------------------------------------
function aib_chain_link_set($TargetList)
{
	$OutList = array();
	$OutList[] = "<table class='aib-chain-link_table'>";
	foreach($TargetList as $TargetRecord)
	{
		$OutList[] = "<tr class='aib-chain-link-row'>";
		$URL = aib_get_with_default($TargetRecord,"url","#");
		$Title  = aib_get_with_default($TargetRecord,"title","Click Here");
		$Fields = aib_get_with_default($TargetRecord,"fields",array());
		$CellClass = aib_get_with_default($TargetRecord,"cell_class","aib-chain-link-col");
		$LinkClass = aib_get_with_default($TargetRecord,"cell_class","aib-chain-link-link");
		$OutList[] = "<td class='$CellClass'>";
		$OutURL = $URL;
		$Flag = false;
		foreach($Fields as $FieldName => $FieldValue)
		{
			if ($Flag == false)
			{
				$OutURL .= "?";
				$Flag = true;
			}
			else
			{
				$OutURL .= "&";
			}

			$OutURL .= $FieldName."=".$FieldValue;
		}

		$OutList[] = "<a class='$LinkClass' href=\"$OutURL\">$Title</a>";
		$OutList[] = "</td></tr>";
		$OutList[] = "<tr class='aib-chain-link-sep-row'>";
		$OutList[] = "<td colspan='99' class='aib-chain-link-sep-col'> </td>";
		$OutList[] = "</tr>";
	}

	$OutList[] = "</table>";
	return(join("\n",$OutList));
}

// Generate form header
// --------------------
function aib_gen_form_header($FormName,$Action,$Class = false, $ValidateFunction = false)
{
	$Buffer = "<form name=\"$FormName\" id=\"$FormName\" method='POST' enctype='multipart/form-data'";
	if ($Class != false)
	{
		$Buffer .= " class=\"$Class\"";
	}
	else
	{
		$Buffer .= " class=\"aib-generic-form\"";
	}

	$Buffer .= " action=\"$Action\"";
	if ($ValidateFunction != false)
	{
		$Buffer .= " onsubmit=\"return $ValidateFunction"."()\"";
	}

	$Buffer .= ">";
	return($Buffer);
}

// Generate validations for fields
// Info is an assoc array:
//
//	"field_name" => array(
//		"type" => "field type",		-- text,select,textarea,password,
//		"id" => "field id",
//		"conditions" => array(
//
//			"condition" => array("name" => "value"), "condition" => array("name" => "value")
//		)
//	)
//
//	Value names:
//		error_message	Message to show on error
//		value		Check value
//		low		Low range value
//		high		High range value
//		pattern		Pattern for regexp
//		modifier	Regexp pattern modifier
//		target		Target field for compare
//		target_type	text,select,textarea,password
//
//
// Extra validation code can be added by including a
// field named "_form" in the form:
//
//	"_form" => array("function" => "function_name")
//
// This will cause a function called "function_name" to be
// invoked.  If it returns false, then the validation
// function will return false.
//	
// -------------------------------
function aib_gen_field_validations($FormName,$ValidateName,$Info)
{
	if ($Info == false)
	{
		return("");
	}

	$TemplatePrefix = "
<script>

function set_field_surround_color(FieldName,ColorName) {
	var TotalName;

	TotalName = 'input-box-' + FieldName;
	\$('#' + TotalName).css('border','solid ' + ColorName + ' 2px');
}

function set_field_focus(FieldID) {
	var FieldObj;

	FieldObj = document.getElementById(FieldID);
	FieldObj.focus();
}

function do_form_error_message(Message,FieldID)
{
	alert(Message);
	set_field_focus(FieldID);
}

function [[VALIDATENAME]]() {
	var FieldValue;
	var SelectNumber;
	var SelectDefault;
	var TargetValue;
	var LowValue;
	var HighValue;
	var Pattern;
	var ExtraValidate;

	";

	$TemplateSuffix = "
}
</script>
	";

	// Function header

	$TemplatePrefix = str_replace("[[VALIDATENAME]]",$ValidateName,$TemplatePrefix);
	$OutLines = array($TemplatePrefix);


	// Clear surrounds

	foreach($Info as $FieldName => $FieldInfo)
	{
		$OutLines[] = "
			set_field_surround_color('$FieldName','white');
			";
	}


	// Process each field

	foreach($Info as $FieldName => $FieldInfo)
	{
		if ($FieldName == "_form")
		{
			if (isset($FieldInfo["function"]) == true)
			{
				$OutLines[] = "
	ExtraValidate = ".$FieldInfo["function"]."();";
				$OutLines[] = "
	if (ExtraValidate == false)
	{
		return(false);
	}";
					
			}

			continue;
		}

		if (isset($FieldInfo["field_id"]) == false)
		{
			continue;
		}

		$FieldID = $FieldInfo["field_id"];
		if (isset($FieldInfo["type"]) == false)
		{
			$Type = "text";
		}
		else
		{
			$Type = $FieldInfo["type"];
		}

		switch($Type)
		{
			case "text":
			default:
				$OutLines[] = "FieldValue = document.getElementById('$FieldName').value;";
				break;
		}

		foreach($FieldInfo["conditions"] as $ConditionType => $ConditionValues)
		{
			if (isset($ConditionValues["error_message"]) == false)
			{
				$ErrorMessage = "Invalid input for $FieldName";
			}
			else
			{
				$ErrorMessage = $ConditionValues["error_message"];
			}

			switch($ConditionType)
			{
				case "notempty":
				case "notblank":
					$OutLines[] = "
						if (FieldValue == '')
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}
						";
					break;

				case "matchesotherfield":
				case "matchestargetfield":
				case "matchesother":
				case "matchestarget":
				case "notmatchesotherfield":
				case "notmatchestargetfield":
				case "notmatchesother":
				case "notmatchestarget":
					if (isset($ConditionValues["target"]) == false && isset($ConditionValues["target_name"]) == false)
					{
						break;
					}

					if (isset($ConditionValues["target"]) != false)
					{
						$TargetName = $ConditionValues["target"];
					}
					else
					{
						$TargetName = $ConditionValues["target_name"];
					}

					if (isset($Info[$TargetName]) == false)
					{
						break;
					}

					$TargetType = $Info[$TargetName]["type"];
					switch($TargetType)
					{
						case "text":
							$OutLines[] = "TargetValue = document.getElementById('$TargetName').value;";
							break;

						default:
							break;
					}

					switch($ConditionType)
					{
						case "matchesotherfield":
							$OutLines[] = "
								if (TargetValue != FieldValue)
								{
									set_field_surround_color('$FieldName','red');
									do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
									return(false);
								}";

							break;

						case "notmatchesotherfield":
							$OutLines[] = "
								if (TargetValue == FieldValue)
								{
									set_field_surround_color('$FieldName','red');
									do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
									return(false);
								}";

							break;

						default:
							break;
					}

					break;

				case "string_inrange":
					if (isset($ConditionValues["low"]) == false || isset($ConditionValues["high"]) == false)
					{
						break;
					}

					$Low = $ConditionValues["low"];
					$High = $ConditionValues["high"];
					$OutLines[] = "
						if (FieldValue < \"$Low\" || FieldValue > \"$High\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;

				case "num_inrange":
					if (isset($ConditionValues["low"]) == false || isset($ConditionValues["high"]) == false)
					{
						break;
					}

					$Low = $ConditionValues["low"];
					$High = $ConditionValues["high"];
					$OutLines[] = "
						if (FieldValue < parseFloat(\"$Low\") || FieldValue > parseFloat(\"$High\"))
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;

				case "string_outrange":
					if (isset($ConditionValues["low"]) == false || isset($ConditionValues["high"]) == false)
					{
						break;
					}

					$Low = $ConditionValues["low"];
					$High = $ConditionValues["high"];
					$OutLines[] = "
						if (FieldValue >= \"$Low\" && FieldValue <= \"$High\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;

				case "num_outrange":
					if (isset($ConditionValues["low"]) == false || isset($ConditionValues["high"]) == false)
					{
						break;
					}

					$Low = $ConditionValues["low"];
					$High = $ConditionValues["high"];
					$OutLines[] = "
						if (FieldValue  >= parseFloat(\"$Low\") && FieldValue <= parseFloat(\"$High\"))
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;

				case "eq":
					if (isset($ConditionValues["value"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["value"];
					$OutLines[] = "
						if (FieldValue != \"$Value\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;

				case "le":
					if (isset($ConditionValues["value"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["value"];
					$OutLines[] = "
						if (FieldValue > \"$Value\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;


				case "ge":
					if (isset($ConditionValues["value"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["value"];
					$OutLines[] = "
						if (FieldValue < \"$Value\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;


				case "ne":
					if (isset($ConditionValues["value"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["value"];
					$OutLines[] = "
						if (FieldValue == \"$Value\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;


				case "lt":
					if (isset($ConditionValues["value"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["value"];
					$OutLines[] = "
						if (FieldValue >= \"$Value\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;


				case "gt":
					if (isset($ConditionValues["value"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["value"];
					$OutLines[] = "
						if (FieldValue <= \"$Value\")
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";
					break;

				case "regexp":
					if (isset($ConditionValues["pattern"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["pattern"];
					if (isset($ConditionValues["modifier"]) == true)
					{
						$Modifier = $ConditionValues["modifier"];
					}
					else
					{
						$Modifier = "";
					}

					$OutLines[] = "
						Pattern = /".$Value."/".$Modifier.";
						if (Pattern.test(FieldValue) != true)
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";

					break;

				case "notregexp":
					if (isset($ConditionValues["pattern"]) == false)
					{
						break;
					}

					$Value = $ConditionValues["pattern"];
					if (isset($ConditionValues["modifier"]) == true)
					{
						$Modifier = $ConditionValues["modifier"];
					}
					else
					{
						$Modifier = "";
					}

					$OutLines[] = "
						Pattern = /".$Value."/".$Modifier.";
						if (Pattern.test(FieldValue) == true)
						{
							set_field_surround_color('$FieldName','red');
							do_form_error_message(\"$ErrorMessage\",\"$FieldID\");
							return(false);
						}";

					break;

				default:
					break;
			}
		}
	}


	$OutLines[] = $TemplateSuffix;
	return(join("\n",$OutLines));
}

// Generate script code to set up table pre-load
// ---------------------------------------------
function aib_generate_list_preload($DisplayID,$TotalPages)
{
	// Causes query function for list to be loaded for the first page of results once the
	// document is ready and the window is completely loaded.  Causes the AJAX request for
	// the first page to be triggered.

	$StatusVarName = $DisplayID."_Status";
	$QueryFuncName = "listquery_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageNumberVar = "ListPageNumber_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageCountVar = "ListPageCount_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$CurrentFuncVar = "CurrentOperation_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$ListSearchValueField = $DisplayID."-lsv";
	$QueryFuncName = "listquery_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$DisplayFuncName = "listshow_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$ErrorFuncName = "listerror_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$SearchFuncName = "listsearch_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$SearchDropName = "listsearchdrop_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$InitListFuncName = "listinit_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageNumberVar = "ListPageNumber_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageCountVar = "ListPageCount_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$FirstPageButton = $DisplayID."-firstpage";
	$NextPageButton = $DisplayID."-nextpage";
	$LastPageButton = $DisplayID."-lastpage";
	$PrevPageButton = $DisplayID."-prevpage";
	$GotoPageButton = $DisplayID."-gotopage";
	$SearchButton = $DisplayID."-search";
	$ClearSearchButton = $DisplayID."-clearsearch";
	$TopListSearchValueField = $DisplayID."-toplsv";
	$TopFirstPageButton = $DisplayID."-topfirstpage";
	$TopNextPageButton = $DisplayID."-topnextpage";
	$TopLastPageButton = $DisplayID."-toplastpage";
	$TopPrevPageButton = $DisplayID."-topprevpage";
	$TopGotoPageButton = $DisplayID."-topgotopage";
	$TopSearchButton = $DisplayID."-topsearch";
	$TopClearSearchButton = $DisplayID."-topclearsearch";
	$Buffer = "

	$StatusVarName = 'preload';

	\$(window).on('load',function() {
		if ($StatusVarName == 'preload')
		{
			$PageCountVar = $TotalPages;
			$PageNumberVar = 1;
			$StatusVarName = 'ok';
			$InitListFuncName();
			$QueryFuncName();
		}

	});

	";
	return($Buffer);
}
		

	

// Generate a scrolling table.
//
// UserID is the user ID (not the login ID, but the record ID)
// DisplayID is the ID and/or name of the div to contain the table.
// Spec is the query specification.  These items are:
//
//	rows		Number of rows to show at one time
//	table_name	Name of the table to be used for the display
//	column_list	List of columns to be retrieved as comma-separated names
//	order_by	The ORDER BY specification for the query
//	where		WHERE clause
//	column_code	Column-specific code.  The key is the column name
//			and the value is a string which contains replacement
//			strings formatted as ||column_name||.
//
//	checks		If set to "Y", then add a checkbox for each record shown
//	checks_title	Checkbox column title (optional)
// 
//
// The select statement must include the phrase "[[LIMIT]]", which is where a LIMIT clause will be placed.
// The output is an array where the "html" container is the HTML to be output, and the "script" container
// contains the handler scripts.
//
// The column list is a list of arrays, where each array contains the "column" item (column name) and
// a "title" item (column title).  There is also a "width" item, and a "wordwrap" item (true or false).
//
// The table contains a series of columns as given in the order, with a "search" field, "next page",
// "prev page", "first page" and "last page" button.
// -------------------------------------------------------------------------------------------------------
function aib_generate_scroll_table_handler($UserID,$DisplayID,$Spec)
{
	$FirstPageButton = $DisplayID."-firstpage";
	$NextPageButton = $DisplayID."-nextpage";
	$LastPageButton = $DisplayID."-lastpage";
	$PrevPageButton = $DisplayID."-prevpage";
	$GotoPageButton = $DisplayID."-gotopage";
	$SearchButton = $DisplayID."-search";
	$ClearSearchButton = $DisplayID."-clearsearch";
	$PageNumberInput = $DisplayID."-pagenum";
	$KeyValueInput = $DisplayID."-key";
	$ListSearchValueField = $DisplayID."-lsv";
	$TableContentArea = $DisplayID."-content";
	$QueryFuncName = "listquery_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$DisplayFuncName = "listshow_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$ErrorFuncName = "listerror_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$SearchFuncName = "listsearch_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$SearchDropName = "listsearchdrop-".preg_replace("/[\.\-]+/","_",$DisplayID);
	$InitListFuncName = "listinit_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageNumberVar = "ListPageNumber_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageCountVar = "ListPageCount_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$CurrentFuncVar = "CurrentOperation_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PageNumberSpan = $DisplayID."-page-number-span";
	$PageCountSpan = $DisplayID."-page-count-span";

	$TopSearchFuncName = "listsearchtop_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$TopListSearchValueField = $DisplayID."-toplsv";
	$TopFirstPageButton = $DisplayID."-topfirstpage";
	$TopNextPageButton = $DisplayID."-topnextpage";
	$TopLastPageButton = $DisplayID."-toplastpage";
	$TopPrevPageButton = $DisplayID."-topprevpage";
	$TopGotoPageButton = $DisplayID."-topgotopage";
	$TopSearchButton = $DisplayID."-topsearch";
	$TopClearSearchButton = $DisplayID."-topclearsearch";
	$TopPageNumberInput = $DisplayID."-toppagenum";
	$TopSearchDropName = "listsearchdroptop-".preg_replace("/[\.\-]+/","_",$DisplayID);

	if (isset($Spec["extra_init_code"]) == true)
	{
		$ExtraInitCode = $Spec["extra_init_code"];
	}
	else
	{
		$ExtraInitCode = "";
	}

	$ItemCheckBoxes = "";
	$ItemCheckBoxesTitle = "";
	if (isset($Spec["checks"]) == true)
	{
		$ItemCheckBoxes = $Spec["checks"];
	}

	if (isset($Spec["checks_title"]) == true)
	{
		$ItemCheckBoxesTitle = $Spec["checks_title"];
	}

	// Button handlers

	$FirstButtonClickFunc = "firstpagefunc_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$LastButtonClickFunc = "lastpagefunc_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$NextButtonClickFunc = "nextpagefunc_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$PrevButtonClickFunc = "prevpagefunc_".preg_replace("/[\.\-]+/","_",$DisplayID);
	$GotoButtonClickFunc = "gotopagefunc_".preg_replace("/[\.\-]+/","_",$DisplayID);

	$TotalPages = $Spec["total_pages"];
	$OpCode = bin2hex($Spec["opcode"]);
	$Rows = $Spec["rows"];
	$EncodedUserID = bin2hex($UserID);
	if (isset($Spec["url"]) != false)
	{
		$ServiceURL = $Spec["url"];
	}
	else
	{
		$ServiceURL = "/services/air.php";
	}

	// Generate extra parameters for call to AJAX function

	$ExtraParamString = "";
	if (isset($Spec["extra_param"]) != false)
	{
		$ExtraParam = $Spec["extra_param"];
		foreach($ExtraParam as $ExtraName => $ExtraValue)
		{
			$ExtraParamString .= "QueryParam['$ExtraName'] = '$ExtraValue';\n";
		}
	}

	$NavString = aib_get_nav_string();
	if ($NavString == false)
	{
		$NavString = "";
	}

	$ScriptLines = array();
	$ScriptLines[] = "
<script>
	var $PageCountVar = 1;
	var $PageNumberVar = 1;
	var $CurrentFuncVar = 'list';
	var NavString = \"$NavString\";

	// Display the data from the list

	function $DisplayFuncName(InData) {

		// If the status isn't OK, then show error message

		if (InData['status'] != 'OK')
		{
			\$('#$TableContentArea').html(\"ERROR: \" + InData['info']['msg']);
		}

		// Get total page count and update variable

		$PageCountVar = InData['info']['pagecount'];

		// Update the table body with new HTML

		\$('#$TableContentArea').html(InData['info']['html']);
		\$('#$PageNumberSpan').html('Current Page:' + ".$PageNumberVar.".toString());
		\$('#$PageCountSpan').html(".$PageCountVar.".toString());
	}

	// Display error

	function $ErrorFuncName(ReqObj,ErrorStatus,ErrorText) {
		\$('#$TableContentArea').html(\"ERROR: \" + ErrorText);
	}

	// Do search function

	function $SearchFuncName() {
		var QueryParam = {};
		var SelectedVal;

		// Get column name from search list

		
		SelectedVal = \$('#$SearchDropName').val();
		if (\$('#$ListSearchValueField').val() == '')
		{
			alert('You must enter something to search for');
			return;
		}

		// Do query

		QueryParam['o'] = '$OpCode'
		QueryParam['i'] = '$EncodedUserID';
		QueryParam['pn'] = $PageNumberVar;
		QueryParam['checks'] = '$ItemCheckBoxes';
		QueryParam['checks_title'] = '$ItemCheckBoxesTitle';
		QueryParam['pic'] = '$Rows';
		QueryParam['lop'] = 'search';
		QueryParam['lsc'] = SelectedVal;
		QueryParam['lsv'] = \$('#$ListSearchValueField').val();
		QueryParam['id'] = '$DisplayID';
		QueryParam['key'] = \$('#$KeyValueInput').val();
		QueryParam['aibnav'] = NavString;
		$ExtraParamString
		aib_ajax_request('$ServiceURL',QueryParam,$DisplayFuncName,$ErrorFuncName);
	}

	function $TopSearchFuncName() {
		var QueryParam = {};
		var SelectedVal;

		// Get column name from search list

		
		SelectedVal = \$('#$TopSearchDropName').val();
		if (\$('#$TopListSearchValueField').val() == '')
		{
			alert('You must enter something to search for');
			return;
		}

		// Do query

		QueryParam['o'] = '$OpCode'
		QueryParam['i'] = '$EncodedUserID';
		QueryParam['pn'] = $PageNumberVar;
		QueryParam['checks'] = '$ItemCheckBoxes';
		QueryParam['checks_title'] = '$ItemCheckBoxesTitle';
		QueryParam['pic'] = '$Rows';
		QueryParam['lop'] = 'search';
		QueryParam['lsc'] = SelectedVal;
		QueryParam['lsv'] = \$('#$TopListSearchValueField').val();
		QueryParam['id'] = '$DisplayID';
		QueryParam['key'] = \$('#$KeyValueInput').val();
		QueryParam['aibnav'] = NavString;
		$ExtraParamString
		aib_ajax_request('$ServiceURL',QueryParam,$DisplayFuncName,$ErrorFuncName);
	}

	// Do query for table

	function $QueryFuncName() {
		var QueryParam = {};

		// If currently searching, then forward call to search function instead of doing a list

		if ($CurrentFuncVar == 'search')
		{
			$SearchFuncName();
			return;
		}

		if ($CurrentFuncVar == 'topsearch')
		{
			$TopSearchFuncName();
		}


		QueryParam['o'] = '$OpCode';
		QueryParam['i'] = '$EncodedUserID';
		QueryParam['pn'] = $PageNumberVar;
		QueryParam['checks'] = '$ItemCheckBoxes';
		QueryParam['checks_title'] = '$ItemCheckBoxesTitle';
		QueryParam['pic'] = '$Rows';
		QueryParam['lop'] = 'list';
		QueryParam['id'] = '$DisplayID';
		QueryParam['key'] = \$('#$KeyValueInput').val();
		QueryParam['aibnav'] = NavString;
		$ExtraParamString
		aib_ajax_request('$ServiceURL',QueryParam,$DisplayFuncName,$ErrorFuncName);
	}

	// Set up callbacks for paging buttons

	function $InitListFuncName()
	{
		\$('#$FirstPageButton').click(function() {
			$PageNumberVar = 1;
			$QueryFuncName();
		});

		\$('#$LastPageButton').click(function() {
			$PageNumberVar = $PageCountVar;
			$QueryFuncName();
		});

		\$('#$GotoPageButton').click(function() {
			$PageNumberVar = parseInt(\$('#$PageNumberInput').val());
			$QueryFuncName();
		});

		\$('#$NextPageButton').click(function() {
			if ($PageNumberVar < $PageCountVar)
			{
				$PageNumberVar++;
				$QueryFuncName();
			}
		});

		\$('#$PrevPageButton').click(function() {
			if ($PageNumberVar > 1)
			{
				$PageNumberVar--;
				$QueryFuncName();
			}
		});

		\$('#$SearchButton').click(function() {
			$CurrentFuncVar = 'search';
			$SearchFuncName();
		});

		\$('#$ClearSearchButton').click(function() {
			$CurrentFuncVar = 'list';
			$PageNumberVar = 1;
			\$('#$ListSearchValueField').val('');
			$QueryFuncName();
		});

		$ExtraInitCode;
	}

	</script>
	";

	return(array("html" => "", "script" => join("\n",$ScriptLines)));
}

// Generate list frame HTML.  This contains everything except the <tbody> content.
// -------------------------------------------------------------------------------
function aib_generate_generic_list_frame_html($ListID,$ListParams)
{
	// Output a table where the first row is headings

	$TableContentArea = $ListID."-content";
	$ColumnList = $ListParams["columns"];
	$PageNumber = $ListParams["pagenum"];
	$PageCount = $ListParams["pagecount"];
	$CheckBoxes = aib_get_with_default($ListParams,"checks","");
	$CheckBoxesTitle = aib_get_with_default($ListParams,"checks_title","");
	if (isset($ListParams["key"]) == true)
	{
		$KeyValue = $ListParams["key"];
	}
	else
	{
		$KeyValue = "";
	}

	$ColCount = count(array_keys($ColumnList));
	$OutLines = array();
	$TableName = $ListID."-table";
	$NavString = aib_get_nav_string();
	if ($NavString == false)
	{
		$NavString = "";
	}

	// Output extra title rows

	if (isset($ListParams["extra_title_rows"]) == true)
	{
		$OutLines[] = "<table class='aib-generic-list-head-table'>";
		if (count($ListParams["extra_title_rows"]) > 0)
		{
			foreach($ListParams["extra_title_rows"] as $Row)
			{
				$OutLines[] = $Row;
			}

			$OutLines[] = "<tr class='aib-generic-list-extra-title-sep-row'><td class='aib-generic-list-extra-title-sep-cell' colspan='99'> </td></tr>";
		}

		$OutLines[] = "</table>";
	}

	// Top paging and search form

	$OutLines[] = "<table class='aib-generic-list-table'>";
	$OutLines[] = " <tr class='aib-list-footer-row'>";
	$OutLines[] = "  <td class='aib-list-footer-cell-spacer' colspan='99'> </td>";
	$OutLines[] = " </tr>";
	$OutLines[] = " <tr class='aib-list-footer-row'>";
	$OutLines[] = "  <td class='aib-list-footer-cell' colspan='99'>";
	$OutLines[] = "   <table class='aib-list-footer-table'>";
	$OutLines[] = "    <tr class='aib-list-paging-row'>";
	$OutLines[] = "     <td class='aib-list-paging-cell'>";
	$OutLines[] = "      <span id='$ListID-page-number-span'>Current Page: $PageNumber</span> &nbsp; &nbsp; &nbsp;";
	$OutLines[] = "      <button id='$ListID"."-topfirstpage' class='aib-list-first-page-button'>&#60;&#60; First</button>";
	$OutLines[] = "      &nbsp; <button id='$ListID"."-topprevpage' class='aib-list-prev-page-button'>&#60; Prev</button>";
	$OutLines[] = "      &nbsp; <button id='$ListID"."-topnextpage' class='aib-list-next-page-button'>&#62; Next</button>";
	$OutLines[] = "      &nbsp; <button id='$ListID"."-toplastpage' class='aib-list-last-page-button'>&#62;&#62; Last</button>";
	$OutLines[] = "      &nbsp; &nbsp; Go To Page:";
	$OutLines[] = "      <input type='text' name='$ListID"."-toppagenum' id='$ListID"."-toppagenum' class='aib-list-pagenum-field' value='$PageNumber' size='7'>";
	$OutLines[] = "       of <span id='$ListID-page-count-span'>$PageCount</span> pages";
	$OutLines[] = "       &nbsp; <button id='$ListID"."-topgotopage' class='aib-list-goto-page-button'>Go</button>";
	$OutLines[] = "      <input type='hidden' name='$ListID"."-topkey' id='$ListID"."-topkey' value=\"$KeyValue\">";
	$OutLines[] = "     </td>";
	$OutLines[] = "    </tr>";

	// If there's a search spec available, display search field

	if (isset($ListParams["searchable"]) == true)
	{
		// Set up footer with search

		$OutLines[] = "     <tr class='aib-list-footer-row'>";
		$OutLines[] = "      <td class='aib-list-footer-cell-spacer' colspan='99'> </td>";
		$OutLines[] = "     </tr>";
		$OutLines[] = "     <tr class='aib-list-search-row'>";
		$OutLines[] = "      <td class='aib-list-search-cell'>";
		$OutLines[] = "       Search For <input type='text' name='$ListID"."-toplsv' id='$ListID"."-toplsv'> In ";
		$OutLines[] = "       <select name='listsearchdrotopp-$ListID' id='listsearchdrotopp-$ListID'>";
		$OutLines[] = "         <option value='ALL'>All Columns</option>";
		foreach($ListParams["searchable"] as $SearchColName => $SearchColTitle)
		{
			$OutLines[] = "         <option value='$SearchColName'>$SearchColTitle</option>";
		}

		$OutLines[] = "       </select>";
		$OutLines[] = "       &nbsp; &nbsp; <button id='$ListID"."-topsearch' class='aib-list-search-button'>Search</button>";
		$OutLines[] = "       &nbsp; &nbsp; <button id='$ListID"."-topclearsearch' class='aib-list-clearsearch-button'>Clear Search</button>";
		$OutLines[] = "      </td>";
		$OutLines[] = "     </tr>";
	}

	$OutLines[] = "    </table>";
	$OutLines[] = "   </table>";

	// List table

	$OutLines[] = "    <table class='aib-generic-list-table'>";
	$OutLines[] = "     <thead class='aib-generic-list-table-head'>";
	$OutLines[] = "      <tr class='aib-generic-list-table-head-row'>";
	if ($CheckBoxes != "")
	{
		$OutLines[] = "      <th class='aib-generic-list-table-check-title-col'>$CheckBoxesTitle</th>";
	}
	else
	{
		$OutLines[] = "      <th class='aib-generic-list-table-check-title-col'> </th>";
	}

	foreach($ColumnList as $ColName => $ColTitle)
	{
		$ColWidth = "";
		if (isset($ListParams["col_width"]) == true)
		{
			if (isset($ListParams["col_width"][$ColName]) == true)
			{
				$ColWidth = $ListParams["col_width"][$ColName];
			}
		}

		if ($ColWidth != "")
		{
			$TempWidth = "width='$ColWidth'";
		}
		else
		{
			$TempWidth = "";
		}

		if ($ColName != ".op")
		{
			$OutLines[] = "      <th class='aib-generic-list-table-title-col' $TempWidth>$ColTitle</th>";
		}
		else
		{
			$OutLines[] = "      <th class='aib-generic-list-table-op-title-col' $TempWidth> </th>";
		}
	}

	$OutLines[] = "     </tr>";
	$OutLines[] = "    </thead>";


	// Create footer.  This contains paging buttons, and if there are searchable fields, the search box and controls.

	$OutLines[] = "    <tfoot>";
	$OutLines[] = "     <tr class='aib-list-footer-row'>";
	$OutLines[] = "      <td class='aib-list-footer-cell-spacer' colspan='99'> </td>";
	$OutLines[] = "     </tr>";
	$OutLines[] = "     <tr class='aib-list-footer-row'>";
	$OutLines[] = "      <td class='aib-list-footer-cell' colspan='99'>";
	$OutLines[] = "       <table class='aib-list-footer-table'>";
	$OutLines[] = "        <tr class='aib-list-paging-row'>";
	$OutLines[] = "         <td class='aib-list-paging-cell'>";
	$OutLines[] = "          <span id='$ListID-page-number-span'>Current Page: $PageNumber</span> &nbsp; &nbsp; &nbsp;";
	$OutLines[] = "          <button id='$ListID"."-firstpage' class='aib-list-first-page-button'>&#60;&#60; First</button>";
	$OutLines[] = "          &nbsp; <button id='$ListID"."-prevpage' class='aib-list-prev-page-button'>&#60; Prev</button>";
	$OutLines[] = "          &nbsp; <button id='$ListID"."-nextpage' class='aib-list-next-page-button'>&#62; Next</button>";
	$OutLines[] = "          &nbsp; <button id='$ListID"."-lastpage' class='aib-list-last-page-button'>&#62;&#62; Last</button>";
	$OutLines[] = "          &nbsp; &nbsp; Go To Page:";
	$OutLines[] = "          <input type='text' name='$ListID"."-pagenum' id='$ListID"."-pagenum' class='aib-list-pagenum-field' value='$PageNumber' size='7'>";
	$OutLines[] = "          of <span id='$ListID-page-count-span'>$PageCount</span> pages";
	$OutLines[] = "          &nbsp; <button id='$ListID"."-gotopage' class='aib-list-goto-page-button'>Go</button>";
	$OutLines[] = "          <input type='hidden' name='$ListID"."-key' id='$ListID"."-key' value=\"$KeyValue\">";
	$OutLines[] = "         </td>";
	$OutLines[] = "        </tr>";

	// If there's a search spec available, display search field

	if (isset($ListParams["searchable"]) == true)
	{
		// Set up footer with search

		$OutLines[] = "        <tr class='aib-list-footer-row'>";
		$OutLines[] = "         <td class='aib-list-footer-cell-spacer' colspan='99'> </td>";
		$OutLines[] = "        </tr>";
		$OutLines[] = "        <tr class='aib-list-search-row'>";
		$OutLines[] = "         <td class='aib-list-search-cell'>";
		$OutLines[] = "         Search For <input type='text' name='$ListID"."-lsv' id='$ListID"."-lsv'> In ";
		$OutLines[] = "         <select name='listsearchdrop-$ListID' id='listsearchdrop-$ListID'>";
		$OutLines[] = "          <option value='ALL'>All Columns</option>";
		foreach($ListParams["searchable"] as $SearchColName => $SearchColTitle)
		{
			$OutLines[] = "          <option value='$SearchColName'>$SearchColTitle</option>";
		}

		$OutLines[] = "         </select>";
		$OutLines[] = "         &nbsp; &nbsp; <button id='$ListID"."-search' class='aib-list-search-button'>Search</button>";
		$OutLines[] = "         &nbsp; &nbsp; <button id='$ListID"."-clearsearch' class='aib-list-clearsearch-button'>Clear Search</button>";
		$OutLines[] = "        </td>";
		$OutLines[] = "       </tr>";
	}

	$OutLines[] = "         </table>";
	$OutLines[] = "        </td>";
	$OutLines[] = "       </tr>";
	$OutLines[] = "      </tfoot>";

	// Table body area

	$OutLines[] = "      <tbody class='aib-generic-list-body' id='$TableContentArea'> </tbody>";
	$OutLines[] = "     </table>";
	return(join("\n",$OutLines));
}

// Generate inner list HTML
// ------------------------
function aib_generate_generic_list_inner_html($FormData,$ListID,$ListParams,$RecordList)
{
	// Output a table where the first row is headings

	$ColumnList = $ListParams["columns"];
	$Callbacks = $ListParams["callbacks"];
	$PageNumber = $ListParams["pagenum"];
	$PageCount = $ListParams["pagecount"];
	$RecordsPerPage = $ListParams["pagesize"];
	$ColCount = count(array_keys($ColumnList));
	$OutLines = array();
	$RecordCount = count($RecordList);
	$RowCounter = 0;
	if (isset($ListParams['empty_list_message']) == true)
	{
		$EmptyListMessage = $ListParams["empty_list_message"];
	}
	else
	{
		$EmptyListMessage = "";
	}
	
	$CheckBoxes = aib_get_with_default($ListParams,"checks","");
	$CheckBoxesTitle = aib_get_with_default($ListParams,"checks_title","");
	$NavString = aib_get_nav_string();
	if ($NavString == false)
	{
		$NavString = "";
	}

	// Output records

	if (isset($ListParams["extra_title_rows"]) == true)
	{
		if (count($ListParams["extra_title_rows"]) > 0)
		{
			foreach($ListParams["extra_title_rows"] as $Row)
			{
				$OutLines[] = $Row;
			}

			$OutLines[] = "<tr class='aib-generic-list-extra-title-sep-row'><td class='aib-generic-list-extra-title-sep-cell' colspan='99'> </td></tr>";
		}
	}

	$RecordCounter = 0;
	foreach($RecordList as $Record)
	{
		$RecordCounter++;

		// Output columns for record

		$OutLines[] = "<tr class='aib-generic-list-data-row'>";

		// If checkboxes enabled, display for each row

		if ($CheckBoxes != "")
		{
			$OutLines[] = "<td class='aib-generic-list-check-cell'>";
			$OutLines[] = "<input type='checkbox' id='record_checkbox_$RecordCounter' value='".$Record[$CheckBoxes]."'>";
			$OutLines[] = "</td>";
		}
		else
		{
			$OutLines[] = "<td class='aib-generic-list-check-cell'> </td>";
		}

		foreach($ColumnList as $ColName => $ColTitle)
		{
			// Get value unless a "virtual" column, which is indicated with a
			// period prefix

			if (substr($ColName,0,1) != ".")
			{
				$ColValue = $Record[$ColName];
			}
			else
			{
				$ColValue = "";
			}

			$OutLines[] = "<td class='aib-generic-list-data-col'>";

			// If callback, call it with value and record

			if (isset($Callbacks[$ColName]) == true)
			{
				$Callback = $Callbacks[$ColName][0];
				if (isset($Callbacks[$ColName][1]) == true)
				{
					$ExtraData = $Callbacks[$ColName][1];
				}
				else
				{
					$ExtraData = false;
				}

				$OutLines[] = "<div class='aib-generic-list-data-container'>".call_user_func_array($Callback,array($ColName,$ColValue,$Record,$ExtraData))."</div>";
			}
			else
			{
				$OutLines[] = "<div class='aib-generic-list-data-container'>$ColValue</div>";
			}

			$OutLines[] = "</td>";
		}

		$OutLines[] = "</tr>";
		$RowCounter++;
	}

	if (count($RecordList) < 1)
	{
		$OutLines[] = $EmptyListMessage;
		$RowCounter++;
	}

	$ColCount = count($ColumnList);
	while($RowCounter < $RecordsPerPage)
	{
		$OutLines[] = "<tr class='aib-generic-list-data-row'>";
		for($ColIndex = 0; $ColIndex < $ColCount; $ColIndex++)
		{
			$OutLines[] = "<td class='aib-generic-list-data-col'>";
			$OutLines[] = "<div class='aib-generic-list-data-container'> &nbsp; </div>";
			$OutLines[] = "</td>";
		}

		$OutLines[] = "</tr>";
		$RowCounter++;
	}

	return(join("\n",$OutLines));
}

// Generate inner list HTML
// ------------------------
function aib_generate_records_list_inner_html($FormData,$ListID,$ListParams,$RecordList)
{
	// Output a table where the first row is headings

	$ColumnList = $ListParams["columns"];
	$Callbacks = $ListParams["callbacks"];
	$PageNumber = $ListParams["pagenum"];
	$PageCount = $ListParams["pagecount"];
	$RecordsPerPage = $ListParams["pagesize"];
	$ColCount = count(array_keys($ColumnList));
	$OutLines = array();
	$RecordCount = count($RecordList);
	$RowCounter = 0;
	if (isset($ListParams['empty_list_message']) == true)
	{
		$EmptyListMessage = $ListParams["empty_list_message"];
	}
	else
	{
		$EmptyListMessage = "";
	}
	
	$CheckBoxes = aib_get_with_default($ListParams,"checks","");
	$CheckBoxesTitle = aib_get_with_default($ListParams,"checks_title","");
	$NavString = aib_get_nav_string();
	if ($NavString == false)
	{
		$NavString = "";
	}

	// Output records

	if (isset($ListParams["extra_title_rows"]) == true)
	{
		if (count($ListParams["extra_title_rows"]) > 0)
		{
			foreach($ListParams["extra_title_rows"] as $Row)
			{
				$OutLines[] = $Row;
			}

			$OutLines[] = "<tr class='aib-generic-list-extra-title-sep-row'><td class='aib-generic-list-extra-title-sep-cell' colspan='99'> </td></tr>";
		}
	}

	$RecordCounter = 0;
	foreach($RecordList as $Record)
	{
		$RecordCounter++;

		// Output columns for record

		$OutLines[] = "<tr class='aib-generic-list-data-row'>";

		// If checkboxes enabled, display for each row

		$FolderType = ftree_get_property($GLOBALS["aib_db"],$Record["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($CheckBoxes != "")
		{
			if ($FolderType == AIB_ITEM_TYPE_SUBGROUP)
			{
				$OutLines[] = "<td class='aib-generic-list-check-cell'>";
				$OutLines[] = "<input type='checkbox' id='record_checkbox_$RecordCounter' value='".$Record[$CheckBoxes]."'>";
				$OutLines[] = "</td>";
			}
			else
			{
				$OutLines[] = "<td class='aib-generic-list-check-cell'> </td>";
			}
		}
		else
		{
			$OutLines[] = "<td class='aib-generic-list-check-cell'> </td>";
		}

		foreach($ColumnList as $ColName => $ColTitle)
		{
			// Get value unless a "virtual" column, which is indicated with a
			// period prefix

			if (substr($ColName,0,1) != ".")
			{
				$ColValue = $Record[$ColName];
			}
			else
			{
				$ColValue = "";
			}

			$OutLines[] = "<td class='aib-generic-list-data-col'>";

			// If callback, call it with value and record

			if (isset($Callbacks[$ColName]) == true)
			{
				$Callback = $Callbacks[$ColName][0];
				if (isset($Callbacks[$ColName][1]) == true)
				{
					$ExtraData = $Callbacks[$ColName][1];
				}
				else
				{
					$ExtraData = false;
				}

				$OutLines[] = "<div class='aib-generic-list-data-container'>".call_user_func_array($Callback,array($ColName,$ColValue,$Record,$ExtraData))."</div>";
			}
			else
			{
				$OutLines[] = "<div class='aib-generic-list-data-container'>$ColValue</div>";
			}

			$OutLines[] = "</td>";
		}

		$OutLines[] = "</tr>";
		$RowCounter++;
	}

	if (count($RecordList) < 1)
	{
		$OutLines[] = $EmptyListMessage;
		$RowCounter++;
	}

	$ColCount = count($ColumnList);
	while($RowCounter < $RecordsPerPage)
	{
		$OutLines[] = "<tr class='aib-generic-list-data-row'>";
		for($ColIndex = 0; $ColIndex < $ColCount; $ColIndex++)
		{
			$OutLines[] = "<td class='aib-generic-list-data-col'>";
			$OutLines[] = "<div class='aib-generic-list-data-container'> &nbsp; </div>";
			$OutLines[] = "</td>";
		}

		$OutLines[] = "</tr>";
		$RowCounter++;
	}

	return(join("\n",$OutLines));
}

// Generate a location indicator table
//
// Config
//
//	archive_class
//	collection_class
//	subgroup_class
//	record_class
//	item_class
//	entry_template
//	ul_template
//	li_template
//	archive_groups_title
// -----------------------------------
function aib_generate_loc_indicator_list($DBHandle,$Config,$IDPathList)
{
	$ULTemplate = aib_get_with_default($Config,"ul_template","<ul>");
	$LITemplate = aib_get_with_default($Config,"li_template","<li>");
	$EntryTemplate = aib_get_with_default($Config,"entry_template",false);
	$ArchivesFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
	$ParentPathList = array();
	$ArchiveTitle = false;
	$ArchiveCodeTitle = false;
	$ArchiveCode = "";
	$OutLines = array();
	$DepthCounter = 0;
	$OutLines[] = $ULTemplate;
	foreach($IDPathList as $LocalID)
	{
		$LocalRecord = ftree_get_item($DBHandle,$LocalID);
		if ($LocalRecord == false)
		{
			continue;
		}

		$OutLines[] = $LITemplate;
		$FullTitle = aib_urldecode($LocalRecord["item_title"]);
		$ArchiveNameProp = ftree_get_property($DBHandle,$LocalID,"archive_name");
		if ($LocalRecord["item_title"] == "ARCHIVE GROUP")
		{
			$FullTitle = aib_get_with_default($Config,"archive_groups_title",$FullTitle);
		}

		if ($ArchiveNameProp != false)
		{
			$ArchiveCode = $LocalID;
			$ArchiveTitle = $ArchiveNameProp;
			$ArchiveCodeTitle = aib_urldecode($LocalRecord["item_title"]);
			$FullTitle .= " -- $ArchiveNameProp";
		}

		if ($EntryTemplate == false)
		{
			$OutLines[] = $FullTitle;
		}
		else
		{
			$TempLink = $EntryTemplate;
			$TempLink = str_replace("[[ITEMID]]",$LocalID,$TempLink);
			$TempLink = str_replace("[[TITLE]]",$FullTitle,$TempLink);
			$OutLines[] = $TempLink;
		}

		$OutLines[] = $ULTemplate;
		$DepthCounter++;
	}

	for ($LocalCounter = 0; $LocalCounter < $DepthCounter; $LocalCounter++)
	{
		$OutLines[] = "</ul>";
	}

	$OutLines[] = "</ul>";
	return(join("\n",$OutLines));
}

// Generate a scrolling div with a series of records and checkboxes next to each record
//
// Spec
//	check_field			Name of field containing value for checkbox or "_counter"
//	check_title			Title of checkbox column
//	check_prefix			Checkbox name and ID prefix
//	field_list			List of field names to be shown
//	title_list			ColumnTitles
// 	div_class			Name of div class
//	table_class			Name of table class
//	list_title_cell_class		Name of list title cell class
//	list_check_title_cell_class	Name of checkbox column title cell class
//	list_data_row_class		Name of list data row class
//	list_check_cell_class		Name of list checkbox cell class
//	list_data_cell_class		Name of list data cell class
//	width				Width override
//	height				Height override
//	button_title			Title of submit button
//	button_action			onClick action for submit button
//	button_row_class
//	button_cell_class
//	button_class
//	button_id
//	div_style			Extra DIV style code
// ------------------------------------------------------------------------------------
function aib_generate_scrolling_record_div($DBHandle,$Spec,$RecordList)
{
	$CheckField = aib_get_with_default($Spec,"check_field",false);
	$CheckTitle = aib_get_with_default($Spec,"check_title","");
	$CheckPrefix = aib_get_with_default($Spec,"check_prefix","");
	$WidthOverride = aib_get_with_default($Spec,"width",false);
	$HeightOverride = aib_get_with_default($Spec,"height",false);
	$FieldList = aib_get_with_default($Spec,"field_list",array());
	$TitleList = aib_get_with_default($Spec,"title_list",array());
	$DivClass = aib_get_with_default($Spec,"div_class","generic-scroll-div");
	$DivStyle = aib_get_with_default($Spec,"div_style","");
	$TableClass = aib_get_with_default($Spec,"div_class","generic-scroll-div-table");
	$ListTitleCellClass = aib_get_with_default($Spec,"list_title_cell_class","generic-scroll-div-title-cell");
	$ListCheckTitleCellClass = aib_get_with_default($Spec,"list_check_title_cell_class","generic-scroll-div-check-title-cell");
	$ListDataRowClass = aib_get_with_default($Spec,"list_data_row_class","generic-scroll-div-data-row");
	$ListCheckCellClass = aib_get_with_default($Spec,"list_check_cell_class","generic-scroll-div-check-cell");
	$ListDataCellClass = aib_get_with_default($Spec,"list_data_cell_class","generic-scroll-div-data-cell");
	$ButtonRowClass = aib_get_with_default($Spec,"button_row_class","generic-scroll-div-button-row");
	$ButtonCellClass = aib_get_with_default($Spec,"button_row_class","generic-scroll-div-button-cell");
	$ButtonClass = aib_get_with_default($Spec,"button_row_class","generic-scroll-div-button");
	$ButtonID = aib_get_with_default($Spec,"button_id","generic_scroll_div_button");
	$ButtonTitle = aib_get_with_default($Spec,"button_title","Select");
	$ButtonAction = aib_get_with_default($Spec,"button_action","");
	$OutLines = array();

	// Div

	$DivSpec = "<div class='$DivClass'";
	if ($WidthOverride !== false)
	{
		$DivSpec .= " width='$WidthOverride'";
	}

	if ($HeightOverride !== false)
	{
		$DivSpec .= " height='$HeightOverride'";
	}

	if ($DivStyle != "")
	{
		$DivSpec .= " style=\"".$DivStyle."\"";
	}

	$DivSpec .= ">";
	$OutLines = array($DivSpec);

	// Table in div

	$OutLines[] = "<table class='$TableClass'>";

	// Header for table

	$OutLines[] = "<thead><tr>";
	$OutLines[] = "<th class='$ListCheckTitleCellClass'>$CheckTitle</th>";
	foreach($TitleList as $Title)
	{
		$OutLines[] = "<th class='$ListTitleCellClass'>$Title</th>";
	}

	$OutLines[] = "</tr></thead><tbody>";

	// Data rows

	$RecordCounter = 0;
	foreach($RecordList as $Record)
	{
		$RecordCounter++;
		$OutLines[] = "<tr class='$ListDataRowClass'>";
		if ($CheckField != "_counter" && $CheckField !== false)
		{
			$OutLines[] = "<td class='$ListCheckCellClass'><input type='checkbox' name='".$CheckPrefix."_".$RecordCounter."' id='".$CheckPrefix."_".$RecordCounter."'".
				" value='".$Record[$CheckField]."'></td>";
		}
		else
		{
			$OutLines[] = "<td class='$ListCheckCellClass'><input type='checkbox' name='".$CheckPrefix."_".$RecordCounter."' id='".$CheckPrefix."_".$RecordCounter."'".
				" value='$RecordCounter'></td>";
		}

		foreach($FieldList as $FieldName)
		{
			if (isset($Record[$FieldName]) == true)
			{
				$OutLines[] = "<td class='$ListDataCellClass'>".$Record[$FieldName]."</td>";
			}
			else
			{
				$OutLines[] = "<td class='$ListDataCellClass'>INVALID</td>";
			}
		}

		$OutLines[] = "</tr>";
	}

	$OutLines[] = "<tr class='$ButtonRowClass'>";
	$OutLines[] = "<td class='$ButtonCellClass' colspan='99'>";
	$OutLines[] = "<button type='button' class='$ButtonClass' id='$ButtonID' onclick=\"$ButtonAction\">$ButtonTitle</button>";
	$OutLines[] = "</td></tr>";
	$OutLines[] = "</tbody>";
	$OutLines[] = "</table>";
	$OutLines[] = "</div>";
	return(join("\n",$OutLines));
}




// Given an item, return type.  Return is an array:
//
//	type = type
//	name = name (if applicable)
// --------------------------------------------------------------
function aib_get_item_class($DBHandle,$ItemID)
{
	// Most common will be a folder

	$FolderType = ftree_get_property($DBHandle,$ItemID,"aibftype");
	if ($FolderType != false)
	{
		return(array("type" => $FolderType, "name" => false));
	}

	$ArchiveName = ftree_get_property($DBHandle,$ItemID,"archive_name");
	if ($ArchiveName != false)
	{
		return(array("type" => AIB_ITEM_TYPE_ARCHIVE, "name" => $ArchiveName));
	}

	$ArchiveGroupCode = ftree_get_property($DBHandle,$ItemID,"archive_group_code");
	if ($ArchiveGroupCode != false)
	{
		return(array("type" => AIB_ITEM_TYPE_ARCHIVE_GROUP, "name" => $ArchiveGroupCode));
	}

	return(array("type" => false, "name" => false));
}


// Get list of entries for browse page
// -----------------------------------
function aib_get_browse_items($DBHandle,$Parent,$UserID,$Start,$Count,$ItemType)
{
	// Get groups associated with user

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$UserGroup = $UserRecord['user_primary_group'];
	$OutArray = array();
	$Counter = 0;
	if ($Count < 0)
	{
		$Limit = -1;
	}
	else
	{
		$Limit = $Start + $Count;
	}

	switch($ItemType)
	{
		case AIB_ITEM_TYPE_ARCHIVE:
			$ChildList = ftree_list_child_objects($DBHandle,$Parent,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false,true);
			foreach($ChildList as $ChildRecord)
			{
				$ChildType = aib_get_item_class($DBHandle,$ChildRecord["item_id"]);
				if ($ChildType["type"] != AIB_ITEM_TYPE_ARCHIVE)
				{
					continue;
				}

				if ($Counter >= $Start)
				{
					if ($Counter < $Limit || $Limit < 0)
					{
						$ChildRecord["_archive_name"] = $ChildType["name"];
						$OutArray[] = $ChildRecord;
					}
				}
			}

			break;

		case AIB_ITEM_TYPE_COLLECTION:
			$ChildList = ftree_list_child_objects($DBHandle,$Parent,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false,true);
			foreach($ChildList as $ChildRecord)
			{
				$ChildType = aib_get_item_class($DBHandle,$ChildRecord["item_id"]);
				if ($ChildType["type"] != AIB_ITEM_TYPE_COLLECTION)
				{
					continue;
				}

				if ($Counter >= $Start)
				{
					if ($Counter < $Limit || $Limit < 0)
					{
						$OutArray[] = $ChildRecord;
					}
				}
			}

			break;

		case AIB_ITEM_TYPE_SUBGROUP:
			$ChildList = ftree_list_child_objects($DBHandle,$Parent,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false,true);
			foreach($ChildList as $ChildRecord)
			{
				$ChildType = aib_get_item_class($DBHandle,$ChildRecord["item_id"]);
				if ($ChildType["type"] != AIB_ITEM_TYPE_SUBGROUP)
				{
					continue;
				}

				if ($Counter >= $Start)
				{
					if ($Counter < $Limit || $Limit < 0)
					{
						$OutArray[] = $ChildRecord;
					}
				}
			}

			break;

		case AIB_ITEM_TYPE_RECORD:
			$ChildList = ftree_list_child_objects($DBHandle,$Parent,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false,true);
			foreach($ChildList as $ChildRecord)
			{
				$ChildType = aib_get_item_class($DBHandle,$ChildRecord["item_id"]);
				if ($ChildType["type"] != AIB_ITEM_TYPE_RECORD)
				{
					continue;
				}

				if ($Counter >= $Start)
				{
					if ($Counter < $Limit || $Limit < 0)
					{
						$OutArray[] = $ChildRecord;
					}
				}
			}

			break;

		case AIB_ITEM_TYPE_ITEM:
			$ChildList = ftree_list_child_objects($DBHandle,$Parent,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false,true);
			foreach($ChildList as $ChildRecord)
			{
				$ChildType = aib_get_item_class($DBHandle,$ChildRecord["item_id"]);
				if ($ChildType["type"] != AIB_ITEM_TYPE_ITEM)
				{
					continue;
				}

				if ($Counter >= $Start)
				{
					if ($Counter < $Limit || $Limit < 0)
					{
						$OutArray[] = $ChildRecord;
					}
				}
			}

			break;

		default:
			break;
	}

	return($OutArray);
}

// Generate child entry list for tree nav
// --------------------------------------
function aib_generate_tree_nav_child($DBHandle,$AvoidSet,$UserRecord,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false,$ItemRefMap = false)
{
	// Create output line array

	$OutLines = array();

	// Get child list.  If no list, just return empty string.

	$ChildList = ftree_list_child_objects($DBHandle,$InitialParent,false,false,FTREE_OBJECT_TYPE_FOLDER,false,true,false);
	if ($ChildList == false)
	{
		return("");
	}

	$PathMap = $AvoidSet;

	// Process each entry

	foreach($ChildList as $ItemRecord)
	{
		$TempID = $ItemRecord["item_id"];
		$IDName = "aib_navlist_entry_".$TempID;
		$ChildIDName = "aib_navlist_childof_".$TempID;

		// See if the child item is not visible

		$LocalVisible = ftree_get_property($DBHandle,$TempID,AIB_FOLDER_PROPERTY_VISIBLE);
		if ($LocalVisible == "N")
		{
			continue;
		}

		$FolderType = ftree_get_property($DBHandle,$TempID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);

		// Create input for line.  If the item exists in this parent, then checked.

		if (isset($ItemRefMap[$TempID]) == true)
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\" checked>";
		}
		else
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\">";
		}

		$FolderType = ftree_get_property($DBHandle,$TempID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		switch($FolderType)
		{
			case AIB_ITEM_TYPE_COLLECTION:
				if (isset($AvoidSet[$TempID]) == false)
				{
					if ($CollectionClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$CollectionClass'>".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$tempid);\">".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
				}
				else
				{
					if ($CollectionClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$CollectionClass'>".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$tempid);\">".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
				}

				break;

			case AIB_ITEM_TYPE_SUBGROUP:
				if (isset($AvoidSet[$TempID]) == false)
				{
					if ($SubGroupClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$SubGroupClass'>$IDBox ".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">$IDBox ".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
				}
				else
				{
					if ($SubGroupClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$SubGroupClass'>$IDBox ".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">$IDBox ".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";

				}

				break;

			case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			case AIB_ITEM_TYPE_ARCHIVE:
				if (isset($AvoidSet[$TempID]) == false)
				{
					if ($ArchiveClass != false)
					{
						$OutLines[] = "<li id='$IDName' class='$ArchiveClass' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(this,$TempID);\">".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
				}
				else
				{
					if ($ArchiveClass != false)
					{
						$OutLines[] = "<li id='$IDName' class='$ArchiveClass' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
				}

				break;

			default:
				break;
		}
	}

	return(join("\n",$OutLines));
}

// Generate child entry list for tree nav, edit mode
// --------------------------------------
function aib_generate_tree_nav_child_edit($DBHandle,$AvoidSet,$UserRecord,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false,$ItemRefMap = false,$ExpandMap = false)
{
	// Create output line array

	$OutLines = array();

	// Get child list.  If no list, just return empty string.

	$ChildList = ftree_list_child_objects($DBHandle,$InitialParent,false,false,FTREE_OBJECT_TYPE_FOLDER,false,true,false);
	if ($ChildList == false)
	{
		return("");
	}

	$PathMap = $AvoidSet;

	// Process each entry

	foreach($ChildList as $ItemRecord)
	{
		$TempID = $ItemRecord["item_id"];
		$IDName = "aib_navlist_entry_".$TempID;
		$ChildIDName = "aib_navlist_childof_".$TempID;

		// See if the child item is not visible

		$LocalVisible = ftree_get_property($DBHandle,$TempID,AIB_FOLDER_PROPERTY_VISIBLE);
		if ($LocalVisible == "N")
		{
			continue;
		}

		// Create input for line.  If the item exists in this parent, then checked.

		if (isset($ItemRefMap[$TempID]) == true)
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\" checked>";
		}
		else
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\">";
		}

		$FolderType = ftree_get_property($DBHandle,$TempID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		switch($FolderType)
		{
			case AIB_ITEM_TYPE_COLLECTION:
				if (isset($ExpandMap[$TempID]) == true)
				{
					if ($CollectionClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$CollectionClass'>".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$tempid);\">".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
					break;
				}

				if (isset($AvoidSet[$TempID]) == false)
				{
					if ($CollectionClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$CollectionClass'>".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$tempid);\">".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
				}
				else
				{
					if ($CollectionClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$CollectionClass'>".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$tempid);\">".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
				}

				break;

			case AIB_ITEM_TYPE_SUBGROUP:
				if (isset($ExpandMap[$TempID]) == true)
				{
					if ($SubGroupClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$SubGroupClass'>$IDBox ".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">$IDBox ".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
					break;
				}

				if (isset($AvoidSet[$TempID]) == false)
				{
					if ($SubGroupClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$SubGroupClass'>$IDBox ".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">$IDBox ".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
				}
				else
				{
					if ($SubGroupClass != false)
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$SubGroupClass'>$IDBox ".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">$IDBox ".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";

				}

				break;

			case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			case AIB_ITEM_TYPE_ARCHIVE:
				if (isset($ExpandMap[$TempID]) == true)
				{
					if ($ArchiveClass != false)
					{
						$OutLines[] = "<li id='$IDName' class='$ArchiveClass' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
					break;
				}

				if (isset($AvoidSet[$TempID]) == false)
				{
					if ($ArchiveClass != false)
					{
						$OutLines[] = "<li id='$IDName' class='$ArchiveClass' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(this,$TempID);\">".aib_urldecode($ItemRecord["item_title"])."</li>";
					}
				}
				else
				{
					if ($ArchiveClass != false)
					{
						$OutLines[] = "<li id='$IDName' class='$ArchiveClass' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
					}
					else
					{
						$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
					}

					if ($ULClass != false)
					{
						$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
					}
					else
					{
						$OutLines[] = "<ul id='$ChildIDName'>";
					}

					$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
					$OutLines[] = "</ul>";
					$OutLines[] = "</li>";
				}

				break;

			default:
				break;
		}
	}

	return(join("\n",$OutLines));
}

// Get list of archive groups
// --------------------------
function aib_get_archive_group_list($DBHandle,$UserID)
{
	$InitialParent = false;
	if ($UserID !== false && $UserID != AIB_SUPERUSER)
	{
		// Get the top folder for the user

		$UserInfo = ftree_get_user($DBHandle,$UserID);
		if ($UserInfo == false)
		{
			return(false);
		}

		// Determine ID path for top folder

		$TopFolder = $UserInfo["user_top_folder"];
		if ($TopFolder < 0 || $TopFolder === false)
		{
			$InitialParent = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
		}
		else
		{
			$IDPathList = ftree_get_item_id_path($DBHandle,$TopFolder);
			if ($IDPathList == false)
			{
				return(false);
			}

			foreach($IDPathList as $ItemID)
			{
				$ItemType = ftree_get_property($DBHandle,$ItemID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				if ($ItemType == false)
				{
					continue;
				}

				if ($ItemType == AIB_ITEM_TYPE_ARCHIVE_GROUP)
				{
					$TempRecord = ftree_get_item($DBHandle,$ItemID);
					$ArchiveGroupCode = ftree_get_property($DBHandle,$ItemID,AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
					$TempRecord["_archive_group_code"] = $ArchiveGroupCode;
					$OutList[] = $TempRecord;
					return($OutList);
				}
			}

			return(false);
		}
	}
	else
	{
		$InitialParent = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
		if ($InitialParent === false)
		{
			return(false);
		}
	}

	$ChildList = ftree_list_child_objects($DBHandle,$InitialParent,false,false,FTREE_OBJECT_TYPE_FOLDER,false,true);
	if ($ChildList == false)
	{
		return(false);
	}

	$OutList = array();
	foreach($ChildList as $ChildRecord)
	{
		$ChildCode = aib_get_item_class($DBHandle,$ChildRecord['item_id']);
		if ($ChildCode["type"] == AIB_ITEM_TYPE_ARCHIVE_GROUP)
		{
			$LocalRecord = $ChildRecord;
			$LocalRecord["_archive_group_code"] = ftree_get_property($DBHandle,$ChildRecord["item_id"],AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
			$OutList[] = $LocalRecord;
		}
	}

	return($OutList);
}

// Generate a tree nav block
// -------------------------
function aib_generate_tree_nav_div($DBHandle,$UserID,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false, $ItemID = false)
{
	$OutLines = array();
	$OutLines[] = "<div class='aib-tree-nav-div'>";

	// Get item references

	$ItemRefMap = array();
	if ($ItemID !== false)
	{
		$ItemReferenceList = aib_get_item_references($ItemID,true);

		foreach($ItemReferenceList as $ItemRefRecord)
		{
			$ItemRefMap[$ItemRefRecord["item_parent"]] = $ItemRefRecord;
		}

		$TempRecord = ftree_get_item($DBHandle,$ItemID);
		$ItemRefMap[$TempRecord["item_parent"]] = ftree_get_item($DBHandle,$TempRecord["item_parent"]);
	}
	else
	{
		if ($InitialParent !== false)
		{
			$ItemRefMap[$InitialParent] = ftree_get_item($DBHandle,$InitialParent);
		}
	}

	// Get root folder for user if no initial parent or item ID

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$OriginalInitialParent = $InitialParent;
	$IDPathList = array();
	while(true)
	{
		if ($ItemID === false)
		{
			if ($InitialParent === false)
			{
				if ($UserID == AIB_SUPERUSER)
				{
					$InitialParent = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
				}
				else
				{
					$InitialParent = $UserRecord["user_top_folder"];
				}

				break;
			}

			break;
		}

		break;
	}

	// Get path to this item to root

	if ($InitialParent !== false)
	{
		$IDPathList = ftree_get_item_id_path($DBHandle,$InitialParent);
	}
	else
	{
		if ($ItemID !== false)
		{
			$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
		}
		else
		{
			$IDPathList = array();
		}
	}

	// If the initial parent is the user's top folder, then eliminate the parts of the path
	// that are outside of the user's tree.

	if ($UserRecord != false)
	{
		if ($InitialParent == $UserRecord["user_top_folder"])
		{
			while(true)
			{
				if (count($IDPathList) > 0)
				{
					if ($IDPathList[0] == $InitialParent)
					{
						break;
					}

					array_shift($IDPathList);
				}
				else
				{
					break;
				}
			}
		}
		else
		{
			$UserType = $UserRecord["user_type"];
			if ($UserType == AIB_USER_TYPE_USER || $UserType == AIB_USER_TYPE_PUBLIC)
			{
				$LocalInitialParent = $UserRecord["user_top_folder"];
				while(true)
				{
					if (count($IDPathList) > 0)
					{
						if ($IDPathList[0] == $LocalInitialParent)
						{
							break;
						}

						array_shift($IDPathList);
					}
					else
					{
						break;
					}
				}
			}
		}
	}

	// Set up map for path

	$PathMap = array();
	foreach($IDPathList as $TempID)
	{
		$PathMap[$TempID] = true;
	}

	$PathMap[$InitialParent] = true;

	// Output list tree

	if ($ULClass != false)
	{
		$OutLines[] = "<ul class='$ULClass'>";
	}
	else
	{
		$OutLines[] = "<ul>";
	}

	// Set tail item

	$CloseCount = 0;
	if (count($IDPathList) > 0)
	{
		$LastItem = $IDPathList[count($IDPathList) - 1];
	}
	else
	{
		$LastItem = false;
	}

	$TopFolderType = ftree_get_property($GLOBALS["aib_db"],$IDPathList[0],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$TopFolderRecord = ftree_get_item($GLOBALS["aib_db"],$IDPathList[0]);
	$TopFolderTitle = aib_urldecode($TopFolderRecord["item_title"]);

	// Get the archive and archive group


	if ($ItemID === false)
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$InitialParent);
	}
	else
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$ItemID);
	}

	$ArchiveID = $ArchiveInfo["archive"]["item_id"];
	$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];
	if ($ArchiveGroupID !== false && $ArchiveGroupID != "")
	{
		$TempID = $ArchiveGroupID;
	}
	else
	{
		$TempID = $IDPathList[0];
	}

	while(true)
	{
		// Build field, ID name, child ID name

		if (isset($ItemRefMap[$TempID]) == true)
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\" checked>";
		}
		else
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' checked  onclick=\"set_tree_checkbox(event,this);\">";
		}

		$IDName = "aib_navlist_entry_".$TempID;
		$ChildIDName = "aib_navlist_childof_".$TempID;
		$ItemRecord = ftree_get_item($DBHandle,$TempID);

		// Decode title

		$ItemRecord["item_title"] = aib_urldecode($ItemRecord["item_title"]);
		$CloseCount++;

		// Output archive group title

		if ($ArchiveClass != false)
		{
			$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$ArchiveClass'>".aib_urldecode($ItemRecord["item_title"]);
		}
		else
		{
			$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
		}

		if ($ULClass != false)
		{
			$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
		}
		else
		{
			$OutLines[] = "<ul id='$ChildIDName'>";
		}

		$OutLines[] = aib_generate_tree_nav_child($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap);
		$OutLines[] = "</ul>";
		break;
	}

	$OutLines[] = "</ul>";
	$OutLines[] = "</div>";
	array_pop($IDPathList);
	$OutIDList = array_keys($ItemRefMap);
	foreach(array_keys($PathMap) as $TempVal)
	{
		if (isset($ItemRefMap[$TempVal]) == false)
		{
			$OutIDList[] = $TempVal;
		}
	}

	return(array("idlist" => $OutIDList, "init_item" => $InitialParent, "html" => join("\n",$OutLines)));
}

// Generate a tree nav block for edit
// ----------------------------------
function aib_generate_tree_nav_div_edit($DBHandle,$UserID,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false, $ItemID = false)
{
	$OutLines = array();
	$OutLines[] = "<div class='aib-tree-nav-div'>";

	// Get item references

	$ItemRefMap = array();
	if ($ItemID !== false)
	{
		$ItemReferenceList = aib_get_item_references($ItemID,true);

		foreach($ItemReferenceList as $ItemRefRecord)
		{
			$ItemRefMap[$ItemRefRecord["item_parent"]] = $ItemRefRecord;
		}

		$TempRecord = ftree_get_item($DBHandle,$ItemID);
		$ItemRefMap[$TempRecord["item_parent"]] = ftree_get_item($DBHandle,$TempRecord["item_parent"]);
	}
	else
	{
		if ($InitialParent !== false)
		{
			$ItemRefMap[$InitialParent] = ftree_get_item($DBHandle,$InitialParent);
		}
	}

	// Get root folder for user if no initial parent or item ID

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$OriginalInitialParent = $InitialParent;
	$IDPathList = array();
	while(true)
	{
		if ($ItemID === false)
		{
			if ($InitialParent === false)
			{
				if ($UserID == AIB_SUPERUSER)
				{
					$InitialParent = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
				}
				else
				{
					$InitialParent = $UserRecord["user_top_folder"];
				}

				break;
			}

			break;
		}

		break;
	}

	// Get path to this item to root

	if ($InitialParent !== false)
	{
		$IDPathList = ftree_get_item_id_path($DBHandle,$InitialParent);
	}
	else
	{
		if ($ItemID !== false)
		{
			$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
		}
		else
		{
			$IDPathList = array();
		}
	}

	// If the initial parent is the user's top folder, then eliminate the parts of the path
	// that are outside of the user's tree.

	if ($UserRecord != false)
	{
		if ($InitialParent == $UserRecord["user_top_folder"])
		{
			while(true)
			{
				if (count($IDPathList) > 0)
				{
					if ($IDPathList[0] == $InitialParent)
					{
						break;
					}

					array_shift($IDPathList);
				}
				else
				{
					break;
				}
			}
		}
		else
		{
			$UserType = $UserRecord["user_type"];
			if ($UserType == AIB_USER_TYPE_USER || $UserType == AIB_USER_TYPE_PUBLIC)
			{
				$LocalInitialParent = $UserRecord["user_top_folder"];
				while(true)
				{
					if (count($IDPathList) > 0)
					{
						if ($IDPathList[0] == $LocalInitialParent)
						{
							break;
						}

						array_shift($IDPathList);
					}
					else
					{
						break;
					}
				}
			}
		}
	}

	// Set up map for path

	$PathMap = array();
	foreach($IDPathList as $TempID)
	{
		$PathMap[$TempID] = true;
	}

	$PathMap[$InitialParent] = true;

	// Output list tree

	if ($ULClass != false)
	{
		$OutLines[] = "<ul class='$ULClass'>";
	}
	else
	{
		$OutLines[] = "<ul>";
	}

	// Set tail item

	$CloseCount = 0;
	if (count($IDPathList) > 0)
	{
		$LastItem = $IDPathList[count($IDPathList) - 1];
	}
	else
	{
		$LastItem = false;
	}

	$TopFolderType = ftree_get_property($GLOBALS["aib_db"],$IDPathList[0],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$TopFolderRecord = ftree_get_item($GLOBALS["aib_db"],$IDPathList[0]);
	$TopFolderTitle = aib_urldecode($TopFolderRecord["item_title"]);

	// Get the archive and archive group


	if ($ItemID === false)
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$InitialParent);
	}
	else
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$ItemID);
	}

	$ArchiveID = $ArchiveInfo["archive"]["item_id"];
	$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];
	if ($ArchiveGroupID !== false && $ArchiveGroupID != "")
	{
		$TempID = $ArchiveGroupID;
	}
	else
	{
		$TempID = $IDPathList[0];
	}

	while(true)
	{
		// Build field, ID name, child ID name

		if (isset($ItemRefMap[$TempID]) == true)
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\" checked>";
		}
		else
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' checked  onclick=\"set_tree_checkbox(event,this);\">";
		}

		$IDName = "aib_navlist_entry_".$TempID;
		$ChildIDName = "aib_navlist_childof_".$TempID;
		$ItemRecord = ftree_get_item($DBHandle,$TempID);

		// Decode title

		$ItemRecord["item_title"] = aib_urldecode($ItemRecord["item_title"]);
		$CloseCount++;

		// Output archive group title

		if ($ArchiveClass != false)
		{
			$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$ArchiveClass'>".aib_urldecode($ItemRecord["item_title"]);
		}
		else
		{
			$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
		}

		if ($ULClass != false)
		{
			$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
		}
		else
		{
			$OutLines[] = "<ul id='$ChildIDName'>";
		}


		$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap);
		$OutLines[] = "</ul>";
		break;
	}

	$OutLines[] = "</ul>";
	$OutLines[] = "</div>";
	array_pop($IDPathList);
	$OutIDList = array_keys($ItemRefMap);
	foreach(array_keys($PathMap) as $TempVal)
	{
		if (isset($ItemRefMap[$TempVal]) == false)
		{
			$OutIDList[] = $TempVal;
		}
	}

	$TempInitList = array();
	foreach($ItemRefMap as $IDValue => $RefInfo)
	{
		// Get ref record.  If a sub-group, then part of parents

		$RefRecord = ftree_get_item($DBHandle,$IDValue);
		$RefFolderType = ftree_get_property($DBHandle,$IDValue,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($RefFolderType == AIB_ITEM_TYPE_SUBGROUP)
		{
			$TempInitList[] = $IDValue;
		}
	}

	return(array("idlist" => $OutIDList, "init_item" => $TempInitList, "html" => join("\n",$OutLines)));
}

// Generate a tree nav block for edit mode.
// ----------------------------------------
function aib_generate_tree_nav_div_edit_old($DBHandle,$UserID,$InitialParent,$ClickFunction,$ULClass = false,$LIClass = false,$ArchiveClass = false,$CollectionClass = false,$SubGroupClass = false, $ItemID = false)
{
	$OutLines = array();
	$OutLines[] = "<div class='aib-tree-nav-div'>";

	// Get item references

	$ItemRefMap = array();
	$ExpandMap = array();
	if ($ItemID !== false)
	{
		$ItemReferenceList = aib_get_item_references($ItemID,true);
		foreach($ItemReferenceList as $ItemRefRecord)
		{
			$ItemRefMap[$ItemRefRecord["item_parent"]] = $ItemRefRecord;
			$TempList = ftree_get_item_id_path($DBHandle,$ItemRefRecord["item_parent"]);
			foreach($TempList as $PathID)
			{
				$ExpandMap[$PathID] = true;
			}
		}

		$TempRecord = ftree_get_item($DBHandle,$ItemID);
		$ItemRefMap[$TempRecord["item_parent"]] = ftree_get_item($DBHandle,$TempRecord["item_parent"]);
	}
	else
	{
		if ($InitialParent !== false)
		{
			$ItemRefMap[$InitialParent] = ftree_get_item($DBHandle,$InitialParent);
		}
	}


	// Get root folder for user if no initial parent or item ID

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$OriginalInitialParent = $InitialParent;
	$IDPathList = array();
	while(true)
	{
		if ($ItemID === false)
		{
			if ($InitialParent === false)
			{
				if ($UserID == AIB_SUPERUSER)
				{
					$InitialParent = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
				}
				else
				{
					$InitialParent = $UserRecord["user_top_folder"];
				}

				break;
			}

			break;
		}

		break;
	}

	// Get path to this item to root

	if ($InitialParent !== false)
	{
		$IDPathList = ftree_get_item_id_path($DBHandle,$InitialParent);
	}
	else
	{
		if ($ItemID !== false)
		{
			$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
		}
		else
		{
			$IDPathList = array();
		}
	}

	// Set up map for path

	$PathMap = array();
	foreach($IDPathList as $TempID)
	{
		$PathMap[$TempID] = true;
	}

	// Output list tree

	if ($ULClass != false)
	{
		$OutLines[] = "<ul class='$ULClass'>";
	}
	else
	{
		$OutLines[] = "<ul>";
	}

	// Set tail item

	$CloseCount = 0;
	if (count($IDPathList) > 0)
	{
		$LastItem = $IDPathList[count($IDPathList) - 1];
	}
	else
	{
		$LastItem = false;
	}

	// Get the archive and archive group


	if ($ItemID === false)
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$InitialParent);
	}
	else
	{
		$ArchiveInfo = aib_get_archive_and_archive_group($DBHandle,$ItemID);
	}

	$ArchiveID = $ArchiveInfo["archive"]["item_id"];
	$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];
	$TempID = $ArchiveGroupID;

	// Build field, ID name, child ID name

	if (isset($ItemRefMap[$TempID]) == true)
	{
		$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' onclick=\"set_tree_checkbox(event,this);\" checked>";
	}
	else
	{
		$IDBox = "<input type='checkbox' id='aib_item_checkbox_$TempID' checked  onclick=\"set_tree_checkbox(event,this);\">";
	}

	$IDName = "aib_navlist_entry_".$TempID;
	$ChildIDName = "aib_navlist_childof_".$TempID;
	$ItemRecord = ftree_get_item($DBHandle,$TempID);

	// Decode title

	$ItemRecord["item_title"] = aib_urldecode($ItemRecord["item_title"]);

	$CloseCount++;

	// Output archive group title

	if ($ArchiveClass != false)
	{
		$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\" class='$ArchiveClass'>".aib_urldecode($ItemRecord["item_title"]);
	}
	else
	{
		$OutLines[] = "<li id='$IDName' onclick=\"$ClickFunction(event,this,$TempID);\">".aib_urldecode($ItemRecord["item_title"]);
	}

	if ($ULClass != false)
	{
		$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
	}
	else
	{
		$OutLines[] = "<ul id='$ChildIDName'>";
	}

	$OutLines[] = aib_generate_tree_nav_child_edit($DBHandle,$PathMap,$UserRecord,$TempID,$ClickFunction,$ULClass,$LIClass,$ArchiveClass,$CollectionClass,$SubGroupClass,$ItemRefMap,$ExpandMap);
	$OutLines[] = "</ul>";
	$OutLines[] = "</ul>";
	$OutLines[] = "</div>";
	array_pop($IDPathList);
	$TempMap = array();
	foreach($ItemRefMap as $ItemID => $TempRecord)
	{
		$TempMap[$ItemID] = "Y";
	}

	if ($InitialParent !== false)
	{
		$TempMap[$InitialParent] = "Y";
	}

	$InitItemBufferLines = array();
	foreach($TempMap as $ItemID => $Status)
	{
		$InitItemBufferLines[] = " $ItemID:'$Status'";
	}

	$InitialParentBuffer = join(",",$InitItemBufferLines);
	$OutIDList = array_keys($ItemRefMap);
	foreach(array_keys($PathMap) as $TempVal)
	{
		if (isset($ItemRefMap[$TempVal]) == false)
		{
			$OutIDList[] = $TempVal;
		}
	}

	return(array("idlist" => $OutIDList, "init_item" => $InitialParentBuffer, "html" => join("\n",$OutLines)));
}




// Generate a location indicator table
//
// Config
//
//	archive_class
//	collection_class
//	subgroup_class
//	record_class
//	item_class
//	entry_template
//	table_template
//	row_template
//	pad_cell_template
//	entry_cell_template
//	symbol_cell_template
//	archive_groups_title
// -----------------------------------
function aib_generate_loc_indicator_table($DBHandle,$Config,$IDPathList)
{
	$EntryTemplate = aib_get_with_default($Config,"entry_template","[[TITLE]]");
	$TableTemplate = aib_get_with_default($Config,"table_template","<table>");
	$RowTemplate = aib_get_with_default($Config,"row_template","<tr>");
	$PadCellTemplate = aib_get_with_default($Config,"pad_cell_template","<td> </td>");
	$EntryCellTemplate = aib_get_with_default($Config,"entry_cell_template","<td colspan='99'>");
	$SymbolCellTemplate = aib_get_with_default($Config,"symbol_cell_template","<td></td>");
	$ArchivesFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
	$ParentPathList = array();
	$ArchiveTitle = false;
	$ArchiveCodeTitle = false;
	$ArchiveCode = "";
	$OutLines = array();
	$DepthCounter = 0;
	$OutLines[] = $TableTemplate;
	foreach($IDPathList as $LocalID)
	{
		$LocalRecord = ftree_get_item($DBHandle,$LocalID);
		if ($LocalRecord == false)
		{
			continue;
		}

		$OutLines[] = $RowTemplate;
		for ($DepthIndex = 0; $DepthIndex <= $DepthCounter; $DepthIndex++)
		{
			$OutLines[] = $PadCellTemplate;
		}

		if ($LocalID != $IDPathList[0])
		{
			$OutLines[] = $SymbolCellTemplate;
		}

		$FullTitle = aib_urldecode($LocalRecord["item_title"]);
		if ($LocalRecord["item_title"] == "ARCHIVE GROUP")
		{
			$FullTitle = aib_get_with_default($Config,"archive_groups_title",$FullTitle);
		}

		$ArchiveNameProp = ftree_get_property($DBHandle,$LocalID,"archive_name");
		if ($ArchiveNameProp != false)
		{
			$ArchiveCode = $LocalID;
			$ArchiveTitle = $ArchiveNameProp;
			$ArchiveCodeTitle = $LocalRecord["item_title"];
			$FullTitle .= " -- $ArchiveNameProp";
		}

		$OutLines[] = $EntryCellTemplate;
		$TempLink = $EntryTemplate;
		$TempLink = str_replace("[[ITEMID]]",$LocalID,$TempLink);
		$TempLink = str_replace("[[TITLE]]",$FullTitle,$TempLink);
		$OutLines[] = $TempLink;
		$OutLines[] = "</td>";
		$OutLines[] = "</tr>";
		$DepthCounter++;
	}

	$OutLines[] = "</table>";
	return(join(" ",$OutLines));
}

// Generate a dropdown and button for form template selection
// ----------------------------------------------------------
function aib_generate_template_dropdown($DBHandle,$UserID,$IndicatorOptions)
{
	$FieldName = $IndicatorOptions["field_name"];
	$FieldID = $IndicatorOptions["field_id"];
	$SelectClass = $IndicatorOptions["select_class"];
	$OptionClass = $IndicatorOptions["option_class"];
	$TitleOptionClass = $IndicatorOptions["title_option_class"];
	$SelectCallback = $IndicatorOptions["select_callback"];
	$ArchiveID = $IndicatorOptions["archive_id"];
	$ArchiveGroupID = $IndicatorOptions["archive_group_id"];
	$GroupList = ftree_get_user_group_id_list($DBHandle,$UserID);
	if (isset($IndicatorOptions["script"]) == true)
	{
		$ScriptData = $IndicatorOptions["script"];
	}
	else
	{
		$ScriptData = "";
	}

	if (isset($IndicatorOptions["default"]) == true)
	{
		$DefaultID = $IndicatorOptions["default"];
	}
	else
	{
		$DefaultID = "";
	}

	// Get the list of forms for the system.  If this is the root user, get all forms.

	$SystemForms = array();
	if ($UserID != AIB_SUPERUSER)
	{
		$Result = mysqli_query($DBHandle,"SELECT * FROM form_def WHERE form_owner_type='".FTREE_OWNER_TYPE_SYSTEM."' ORDER BY form_title;");
	}
	else
	{
		$Result = mysqli_query($DBHandle,"SELECT * FROM form_def ORDER BY form_title;");
	}

	if ($Result != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			$SystemForms[] = $Row;
		}

		mysqli_free_result($Result);
	}

	// Get the list of archive group forms

	$ArchiveGroupForms = array();
	$Result = mysqli_query($DBHandle,"SELECT * FROM form_def WHERE form_owner=$ArchiveGroupID ORDER BY form_title;");
	if ($Result != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			$ArchiveGroupForms[] = $Row;
		}

		mysqli_free_result($Result);
	}


	// Get the list of forms for the archive

	$ArchiveForms = array();
	$Result = mysqli_query($DBHandle,"SELECT * FROM form_def WHERE form_owner=$ArchiveID ORDER BY form_title;");
	if ($Result != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			$ArchiveForms[] = $Row;
		}

		mysqli_free_result($Result);
	}

	// Get the list of forms for the group(s)

	$GroupMap = array();
	$GroupForms = array();
	foreach($GroupList as $GroupID)
	{
		$Result = mysqli_query($DBHandle,"SELECT * FROM form_def WHERE form_owner_type='".FTREE_OWNER_TYPE_GROUP."' AND form_owner=$GroupID ORDER BY form_title;");
		if ($Result != false)
		{
			while(true)
			{
				$Row = mysqli_fetch_assoc($Result);
				if ($Row == false)
				{
					break;
				}
	
				$LocalID = $Row["group_id"];
				if (isset($GroupMap[$LocalID]) == false)
				{
					$GroupForms[] = $Row;
					$GroupMap[$LocalID] = true;
				}
			}
	
			mysqli_free_Result($Result);
		}
	}

	// Get the list of forms for the user

	$UserForms = array();
	$Result = mysqli_query($DBHandle,"SELECT * FROM form_def WHERE form_owner_type='".FTREE_OWNER_TYPE_USER."' AND form_owner=$UserID ORDER BY form_title;");
	if ($Result != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			$UserForms[] = $Row;
		}

		mysqli_free_Result($Result);
	}

	// Generate list of options

	$OptionList = array("NULL" => " -- SELECT A CUSTOM TEMPLATE -- ");
	$OptionList["BLANK"] = "NO TEMPLATE";
	if (count(array_keys($SystemForms)) > 0)
	{
		foreach($SystemForms as $FormRecord)
		{
			$OptionList[$FormRecord["form_id"]] = urldecode($FormRecord["form_title"]);
		}
	}

	if (count(array_keys($ArchiveGroupForms)) > 0)
	{
		foreach($ArchiveGroupForms as $FormRecord)
		{
			$OptionList[$FormRecord["form_id"]] = urldecode($FormRecord["form_title"]);
		}
	}

	if (count(array_keys($ArchiveForms)) > 0)
	{
		foreach($ArchiveForms as $FormRecord)
		{
			$OptionList[$FormRecord["form_id"]] = urldecode($FormRecord["form_title"]);
		}
	}

	$OutLines = array("<select name='$FieldName' id='$FieldID' class='$SelectClass' $ScriptData>");
	foreach ($OptionList as $Value => $Name)
	{
		if ($Value == $DefaultID)
		{
			$Selected = " SELECTED ";
		}
		else
		{
			$Selected = "";
		}

		if ($Value == "NULL")
		{
			$OutLines[] = "<option class='$TitleOptionClass' value=\"$Value\" $Selected>$Name</option>";
		}
		else
		{
			$OutLines[] = "<option class='$OptionClass' value=\"$Value\" $Selected>$Name</option>";
		}
	}

	$OutLines[] = "</select>";
	$OutLines[] = " &nbsp; &nbsp; <button type='button' id='aib_select_form_button' onclick='$SelectCallback(event,this);'>Use This Custom Template</select>";
	return(join("\n",$OutLines));
}

// Get a list of all fields available to the user
// ----------------------------------------------
function aib_get_available_fields($DBHandle,$UserID,$RestrictToArchiveGroup = false, $RestrictToArchive = false)
{
	$LocalFieldList = array();
	$LocalMap = array();
	$ArchiveGroupList = aib_get_archive_group_list($DBHandle,$UserID);
	if ($ArchiveGroupList != false)
	{
		foreach($ArchiveGroupList as $ArchiveGroupRecord)
		{
			$ArchiveGroupID = $ArchiveGroupRecord["item_id"];
			if ($RestrictToArchiveGroup !== false)
			{
				if ($ArchiveGroupID != $RestrictToArchiveGroup)
				{
					continue;
				}
			}

			$ArchiveList = aib_get_archive_group_archive_list($DBHandle,$ArchiveGroupID);
			foreach($ArchiveList as $ArchiveRecord)
			{
				$ArchiveID = $ArchiveRecord["item_id"];
				if ($RestrictToArchive !== false)
				{
					if ($RestrictToArchive != $ArchiveID)
					{
						continue;
					}
				}

				$TempList = ftree_list_fields($DBHandle,false,FTREE_OWNER_TYPE_ITEM,$ArchiveID);
				foreach($TempList as $TempRecord)
				{
					$FieldID = $TempRecord["field_id"];
					if (isset($LocalMap[$FieldID]) == false)
					{
						$LocalMap[$FieldID] = true;
						$LocalFieldList[] = $TempRecord;
					}
				}
			}
		}
	}

	return($LocalFieldList);

}

// Generate a table that lists the various field and form definitions available
// ----------------------------------------------------------------------------
function aib_generate_field_table($DBHandle,$UserID,$IndicatorOptions = array(),$HideSystemList = false)
{
	if ($HideSystemList == false)
	{
		$HideSystemList = $GLOBALS["aib_hide_predef_field_list"];
	}

	$HideFields = array();
	$SystemFields = array();
	$RecommendedFields = array();
	$ArchiveFields = array();
	if (isset($IndicatorOptions["opt_hide_field"]) == true)
	{
		foreach($IndicatorOptions["opt_hide_field"] as $FieldID)
		{
			$HideFields[$FieldID] = true;
		}
	}

	if (isset($IndicatorOptions["existing_fields"]) == true)
	{
		$ExistingFields = $IndicatorOptions["existing_fields"];
	}
	else
	{
		$ExistingFields = array();
	}

	if (isset($IndicatorOptions["archive_group"]) != false)
	{
		$ArchiveGroupID = $IndicatorOptions["archive_group"];
		$ArchiveGroupRecord = ftree_get_item($DBHandle,$ArchiveGroupID);
	}
	else
	{
		$ArchiveGroupID = false;
		$ArchiveGroupRecord = false;
	}

	if (isset($IndicatorOptions["archive"]) != false)
	{
		$ArchiveID = $IndicatorOptions["archive"];
		$ArchiveRecord = ftree_get_item($DBHandle,$ArchiveID);
	}
	else
	{
		$ArchiveID = false;
		$ArchiveRecord = false;
	}

	$FieldClickHandler = false;
	if ($IndicatorOptions != false)
	{
		$FieldClickHandler = aib_get_with_default($IndicatorOptions,"field_click_callback",false);
	}

	$ShowSystemFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_system_fields",true);
	$ShowRecommendedFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_recommended_fields",true);
	$ShowTraditionalFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_traditional_fields",true);
	$ShowArchiveGroupFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_archive_group_fields",true);
	$ShowArchiveFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_archive_fields",true);
	$ShowUserFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_user_fields",true);
	$ShowSymbolicFieldsFlag = aib_truefalse_option($IndicatorOptions,"opt_show_symbolic_fields",false);

	// Get all system-level (traditional) fields.  These must have an owner type of system, and may have a symbolic name.

	$SystemFields = array();
	if ($ShowSystemFieldsFlag == true || $ShowTraditionalFieldsFlag == true || $ShowSymbolicFieldsFlag == true)
	{
		$ResultList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,false,true);
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				if ($Record["_disabled"] == "Y")
				{
					continue;
				}

				if (isset($HideFields[$Record["field_id"]]) == true)
				{
					continue;
				}

				if (isset($HideSystemList[$Record["field_symbolic_name"]]) == true)
				{
					continue;
				}

				if (isset($ExistingFields[$Record["field_id"]]) == true)
				{
					continue;
				}

				$SymbolicName = ltrim(rtrim($Record["field_symbolic_name"]));
				if ($SymbolicName != "" && $SymbolicName != "NULL")
				{
					if ($ShowSymbolicFieldsFlag == false)
					{
						continue;
					}
				}

				$SystemFields[] = $Record;
			}
		
			unset($ResultList);
		}
	}

	// Get all fields defined as "recommended".  These have an owner type of "recommended".

	$RecommendedFields = array();
	if ($ShowRecommendedFieldsFlag == true)
	{
		$ResultList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_RECOMMENDED,false,true);
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				if (isset($HideFields[$Record["field_id"]]) == true)
				{
					continue;
				}

				if (isset($ExistingFields[$Record["field_id"]]) == true)
				{
					continue;
				}
	
				$RecommendedFields[] = $Record;
			}
		
			unset($ResultList);
		}
	}

	// Get all fields owned by the user

	$UserFields = array();
	if ($ShowUserFieldsFlag == true)
	{
		$ResultList = ftree_list_fields($GLOBALS["aib_db"],$UserID,false,false,true);
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				if (isset($HideFields[$Record["field_id"]]) == true)
				{
					continue;
				}

				if (isset($ExistingFields[$Record["field_id"]]) == true)
				{
					continue;
				}
	
				$Record["field_title"] = urldecode($Record["field_title"]);
				$UserFields[] = $Record;
			}
		
			unset($ResultList);
		}
	}

	// Get all fields owned by the archive group

	$ArchiveGroupFields = array();
	if ($ShowArchiveGroupFieldsFlag == true)
	{
		$ResultList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveGroupID,true);
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				if (isset($HideFields[$Record["field_id"]]) == true)
				{
					continue;
				}

				if (isset($ExistingFields[$Record["field_id"]]) == true)
				{
					continue;
				}
	
				$Record["field_title"] = urldecode($Record["field_title"]);
				$ArchiveGroupFields[] = $Record;
			}
		
			unset($ResultList);
		}
	}

	// Get all fields owned by the archive

	$ArchiveFields = array();
	if ($ShowArchiveFieldsFlag == true)
	{
		$ResultList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveID,true);
		if ($ResultList != false)
		{
			foreach($ResultList as $Record)
			{
				if (isset($HideFields[$Record["field_id"]]) == true)
				{
					continue;
				}

				if (isset($ExistingFields[$Record["field_id"]]) == true)
				{
					continue;
				}
	
				$Record["field_title"] = urldecode($Record["field_title"]);
				$ArchiveFields[] = $Record;
			}
		
			unset($ResultList);
		}
	}

	$OutLines[] = "<table class='aib-field-list-table'>";
	if (count($SystemFields) > 0)
	{
		$OutLines[] = "<tr><td colspan='99' class='aib-field-list-title-cell'><b>Traditional Fields</b></td></tr>";
		foreach($SystemFields as $FieldRecord)
		{
			$AddButtonID = "addfieldbutton_".$FieldRecord["field_id"];
			$RemoveButtonID = "removefieldbutton_".$FieldRecord["field_id"];
			$FieldID = $FieldRecord["field_id"];
			if ($FieldClickHandler == false)
			{
				$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' disabled >Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' disabled >Remove</button></td></tr>";
			}
			else
			{
				$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' onclick='$FieldClickHandler(this,$FieldID,1);'>Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' onclick='$FieldClickHandler(this,$FieldID,0);' disabled >Remove</button></td></tr>";
			}
		}

		$OutLines[] = "<tr><td colspan='99' class='aib-field-list-separator-cell'> </td></tr>";
	}

	if (count($RecommendedFields) > 0)
	{
		$OutLines[] = "<tr><td colspan='99' class='aib-field-list-title-cell'><b>Recommended Fields</b></td></tr>";
		foreach($RecommendedFields as $FieldRecord)
		{
			$AddButtonID = "addfieldbutton_".$FieldRecord["field_id"];
			$RemoveButtonID = "removefieldbutton_".$FieldRecord["field_id"];
			$FieldID = $FieldRecord["field_id"];
			if ($FieldClickHandler == false)
			{
				$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' disabled >Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' disabled >Remove</button></td></tr>";
			}
			else
			{
				$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' onclick='$FieldClickHandler(this,$FieldID,1);'>Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' onclick='$FieldClickHandler(this,$FieldID,0);' disabled >Remove</button></td></tr>";
			}
		}

		$OutLines[] = "<tr><td colspan='99' class='aib-field-list-separator-cell'> </td></tr>";
	}

	if (count($ArchiveGroupFields) > 0)
	{
		$OutLines[] = "<tr><td colspan='99' class='aib-field-list-title-cell'><b>Custom Fields For Archive Group ".urldecode($ArchiveGroupRecord["item_title"])."</b></td></tr>";
	}

	foreach($ArchiveGroupFields as $FieldRecord)
	{
		$AddButtonID = "addfieldbutton_".$FieldRecord["field_id"];
		$RemoveButtonID = "removefieldbutton_".$FieldRecord["field_id"];
		$FieldID = $FieldRecord["field_id"];
		if ($FieldClickHandler == false)
		{
			$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' disabled >Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' disabled >Remove</button></td></tr>";
		}
		else
		{
			$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' onclick='$FieldClickHandler(this,$FieldID,1);'>Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' onclick='$FieldClickHandler(this,$FieldID,0);' disabled >Remove</button></td></tr>";
		}
	}

	if (count($ArchiveFields) > 0)
	{
		$OutLines[] = "<tr><td colspan='99' class='aib-field-list-title-cell'><b>Custom Fields For Archive ".urldecode($ArchiveRecord["item_title"])."</b></td></tr>";
	}

	foreach($ArchiveFields as $FieldRecord)
	{
		$AddButtonID = "addfieldbutton_".$FieldRecord["field_id"];
		$RemoveButtonID = "removefieldbutton_".$FieldRecord["field_id"];
		$FieldID = $FieldRecord["field_id"];
		if ($FieldClickHandler == false)
		{
			$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' disabled >Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' disabled >Remove</button></td></tr>";
		}
		else
		{
			$OutLines[] = "<tr><td class='aib-field-list-field-title-cell'>".$FieldRecord["field_title"]."</td><td class='aib-field-list-field-use-cell'><button class='aib-add-field-button' id='$AddButtonID' onclick='$FieldClickHandler(this,$FieldID,1);'>Add</button></td><td class='aib-field-list-field-remove-cell'><button class='aib-remove-field-button' id='$RemoveButtonID' onclick='$FieldClickHandler(this,$FieldID,0);' disabled >Remove</button></td></tr>";
		}
	}

	$OutLines[] = "</table>";
	return(join("\n",$OutLines));
}

// Given a record, retrieve the fields used and the form used (if any)
// -------------------------------------------------------------------
function aib_get_record_fields_used($DBHandle,$ItemID)
{
	$FieldsUsed = array();
	$TempList = ftree_query_ext($DBHandle,"SELECT * FROM form_item WHERE item_id=$ItemID;");
	if ($TempList != false)
	{
		if (count($TempList) > 0)
		{
			$FormID = $TempList[0]["form_id"];
			$LocalFieldList = ftree_field_get_form_fields($DBHandle,$FormID);
			return($LocalFieldList);
		}
	}

	return(false);
}

// Given an archive group ID, return all of the archives as a list
// ---------------------------------------------------------------
function aib_get_archive_group_archive_list($DBHandle,$ParentFolder)
{
	$ChildList = ftree_list_child_objects($DBHandle,$ParentFolder,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
	if ($ChildList == false)
	{
		return(array());
	}

	return($ChildList);
}

// Given a user ID, return the archive top folder and information.
// ---------------------------------------------------------------
function aib_get_user_archive($DBHandle,$UserID)
{
	// If root, then there's no archive; return a list of all archives in all archive groups

	$OutList = array();
	if ($UserID == AIB_SUPERUSER)
	{
		$OutMap = array();

		// Get the archive group root

		$ArchivesFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
		$ArchiveGroupList = ftree_list_child_objects($DBHandle,$ArchivesFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
		if ($ArchiveGroupList == false)
		{
			return($OutList);
		}

		// For each archive group, get the list of archives

		foreach($ArchiveGroupList as $GroupRecord)
		{
			$ArchiveGroupID = $GroupRecord["item_id"];
			$LocalList = ftree_list_child_objects($DBHandle,$ArchiveGroupID,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
			if ($LocalList != false)
			{
				foreach($LocalList as $LocalRecord)
				{
					$FolderType = ftree_get_property($DBHandle,$LocalRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
					if ($FolderType == AIB_ITEM_TYPE_ARCHIVE)
					{
						if (isset($OutMap[$LocalRecord["item_id"]]) == false)
						{
							$LocalRecord["_archive_group_title"] = $GroupRecord["item_title"];
							$LocalRecord["_archive_group_id"] = $GroupRecord["item_id"];
							$OutMap[$LocalRecord["item_id"]] = $LocalRecord;
						}
					}
				}
			}
		}

		foreach($OutMap as $ItemID => $ItemRecord)
		{
			$OutList[] = $ItemRecord;
		}

		return($OutList);
	}

	// Get the home folder of the user

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	if ($UserRecord == false)
	{
		return($OutList);
	}

	$HomeFolderID = $UserRecord["user_top_folder"];
	$HomeFolderType = ftree_get_property($DBHandle,$HomeFolderID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	if ($HomeFolderType == AIB_ITEM_TYPE_ARCHIVE)
	{
		$OutRecord = ftree_get_item($DBHandle,$HomeFolderID);
		$ParentRecord = ftree_get_item($DBHandle,$OutRecord["item_parent"]);
		$OutRecord["_archive_group_title"] = $ParentRecord["item_title"];
		$OutRecord["_archive_group_id"] = $ParentRecord["item_id"];
		$OutList[] = $OutRecord;
		return($OutList);
	}

	if ($HomeFolderType == AIB_ITEM_TYPE_ARCHIVE_GROUP)
	{
		$GroupRecord = ftree_get_item($DBHandle,$HomeFolderID);
		$LocalList = ftree_list_child_objects($DBHandle,$GroupRecord["item_id"],false,false,FTREE_OBJECT_TYPE_FOLDER,false,true);
		if ($LocalList != false)
		{
			if ($LocalList[0] != "ERROR")
			{
				foreach($LocalList as $LocalRecord)
				{
					$FolderType = ftree_get_property($DBHandle,$LocalRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
					if ($FolderType == AIB_ITEM_TYPE_ARCHIVE)
					{
						$LocalRecord["_archive_group_title"] = $GroupRecord["item_title"];
						$LocalRecord["_archive_group_id"] = $GroupRecord["item_id"];
						$OutList[] = $LocalRecord;
					}
				}
			}
		}

		return($OutList);
	}

	// Can't quickly get archive, so get ID path and find archive in that

	$IDPathList = ftree_get_item_id_path($DBHandle,$HomeFolderID);
	foreach($IDPathList as $LocalID)
	{
		$FolderType = ftree_get_property($DBHandle,$LocalID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($FolderType == AIB_ITEM_TYPE_ARCHIVE)
		{
			$TempRecord = ftree_get_item($DBHandle,$LocalID);
			$GroupRecord = ftree_get_item($DBHandle,$TempRecord["item_parent"]);
			$TempRecord["_archive_group_title"] = $GroupRecord["item_title"];
			$TempRecord["_archive_group_id"] = $GroupRecord["item_id"];
			$OutList[] = $TempRecord;
			return($OutList);
		}
	}

	// No archive was found, so empty return list

	return($OutList);
}

// Generate a list of archive groups
// ---------------------------------
function aib_generate_archive_group_list($DBHandle,$UserID)
{
	if ($UserID != AIB_SUPERUSER)
	{
		return(array());
	}

	$ArchiveGroupRoot = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.AIB_ARCHIVE_GROUP_ROOT);
	if ($ArchiveGroupRoot === false)
	{
		return(array());
	}

	$ChildList = ftree_list_child_objects($DBHandle,$ArchiveGroupRoot,$UserID,false,FTREE_OBJECT_TYPE_FOLDER,false);
	$OutList = array();
	foreach($ChildList as $ChildRecord)
	{
		$ChildID = $ChildRecord["item_id"];
		$FolderType = ftree_get_property($DBHandle,$ChildID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($FolderType != AIB_ITEM_TYPE_ARCHIVE_GROUP)
		{
			continue;
		}

		$OutList[] = $ChildRecord;
	}

	return($OutList);
}

// Get a list of archives with pre-selected archives checked in an unstructured list
// ---------------------------------------------------------------------------------
function aib_generate_archive_list($DBHandle,$IDList,$UserID,$RestrictToUserOption = false,$ULClass,$LIClass)
{

	$IDMap = array();
	foreach($IDList as $LocalID)
	{
		$IDMap[$LocalID] = 'Y';
	}

	// Get root of archives

	$ArchiveFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");

	// Generate list

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$UserGroup = $UserRecord["user_primary_group"];
	if ($RestrictToUserOption == true)
	{
		$ChildList = ftree_list_child_objects($DBHandle,$ArchiveFolderID,$UserID,$UserGroup,FTREE_OBJECT_TYPE_FOLDER,false);
	}
	else
	{
		$ChildList = ftree_list_child_objects($DBHandle,$ArchiveFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
	}

	if ($ChildList == false)
	{
		$ChildList = array();
	}

	// Output a structured list with classes

	$OutLines = array();
	$ChildIDName = "aib_navlist_childof_".$ArchiveFolderID;
	if ($ULClass != false)
	{
		$OutLines[] = "<ul id='$ChildIDName' class='$ULClass'>";
	}
	else
	{
		$OutLines[] = "<ul id='$ChildIDName'>";
	}

	foreach($ChildList as $Record)
	{
		// Get type of entry (archive, collection, etc)

		$ItemID = $Record["item_id"];
		if (isset($IDMap[$ItemID]) == true)
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID' checked >";
		}
		else
		{
			$IDBox = "<input type='checkbox' id='aib_item_checkbox_$ItemID'>";
		}

		$EntryType = ftree_get_property($DBHandle,$ItemID,"aibftype");
		$Line = false;
		$ArchiveTitle = ftree_get_property($DBHandle,$ItemID,"archive_name");
		if ($ArchiveTitle != false)
		{
			if ($LIClass != false)
			{
				$Line = "<li class='$LIClass'>$IDBox $ArchiveTitle (".$Record["item_title"].")</li>";
			}
			else
			{
				$Line = "<li>$IDBox $ArchiveTitle (".$Record["item_title"].")</li>";
			}
		}

		// Output line item

		if ($Line != false)
		{
			$OutLines[] = $Line;
		}

	}

	$OutLines[] = "</ul>";
	return(join("\n",$OutLines));
	exit(0);
}

// Get the list of archives to which a user has access
// ---------------------------------------------------
function aib_get_list_of_accessible_archives($DBHandle,$UserID)
{
	$ArchiveFolderID = ftree_get_object_by_path($DBHandle,FTREE_OBJECT_TYPE_FOLDER.":ARCHIVES");
	$ChildList = ftree_list_child_objects($DBHandle,$ArchiveFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER,false);
	$ChildMap = array();
	foreach($ChildList as $Record)
	{
		$ChildMap[$Record["item_id"]] = $Record;
	}

	$OutList = array();
	if ($UserID == AIB_SUPERUSER)
	{
		return($ChildList);
	}
	else
	{
		$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_acl WHERE user_id=$UserID;");
		if ($ResultList == false)
		{
			$ResultList = array();
		}

		return($ResultList);
	}

	return(array());
}

// Given an item, get the archive and archive group
// ------------------------------------------------
function aib_get_archive_and_archive_group($DBHandle,$ItemID)
{
	$OutData = array("archive" => false, "archive_group" => false);
	$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
	foreach($IDPathList as $LocalID)
	{
		$FolderType = ftree_get_property($DBHandle,$LocalID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		switch($FolderType)
		{
			case AIB_ITEM_TYPE_ARCHIVE_GROUP:
				$OutData["archive_group"] = ftree_get_item($DBHandle,$LocalID);
				break;

			case AIB_ITEM_TYPE_ARCHIVE:
				$OutData["archive"] = ftree_get_item($DBHandle,$LocalID);
				break;

			default:
				break;
		}

		if ($OutData["archive_group"] !== false && $OutData["archive"] !== false)
		{
			break;
		}
	}

	return($OutData);
}

// Given a user ID, return a list of records for each archive and archive group
// ----------------------------------------------------------------------------
function aib_get_available_archives($DBHandle,$UserID)
{
	$OutList = array();

	// Get the user info

	$UserRecord = ftree_get_user($DBHandle,$UserID);
	$UserType = $UserRecord["user_type"];
	$ArchiveGroupList = aib_get_archive_group_list($DBHandle,$UserID);
	foreach($ArchiveGroupList as $ArchiveGroupRecord)
	{
		$OutList[] = $ArchiveGroupRecord;

		// Get the list of archives for the archive group

		$ChildList = ftree_list_child_objects($DBHandle,$ArchiveGroupRecord["item_id"],$UserID,false,FTREE_OBJECT_TYPE_FOLDER,false,true);
		if ($ChildList != false)
		{
			foreach($ChildList as $ChildRecord)
			{
				$FolderType = ftree_get_property($DBHandle,$ChildRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				if ($FolderType == AIB_ITEM_TYPE_ARCHIVE)
				{
					$OutList[] = $ChildRecord;
				}
			}
		}
	}

	return($OutList);
}

// Given an archive group, get all archives in the group.
// ------------------------------------------------------
function aib_get_archives_in_archive_group($DBHandle,$GroupID)
{
	$OutList = array();
	$TempList = ftree_list_child_objects($DBHandle,$GroupID,false,false,FTREE_OBJECT_TYPE_FOLDER,false,true);
	foreach($TempList as $ChildRecord)
	{
		$FolderType = ftree_get_property($DBHandle,$ChildRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
		if ($FolderType == AIB_ITEM_TYPE_ARCHIVE)
		{
			$OutList[] = $ChildRecord;
		}
	}

	return($OutList);
}

// Retrieve a list of all files associated with a batch
// ----------------------------------------------------
function aib_get_upload_batch_queue($DBHandle,$BatchID)
{
	$OutList = array();
	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE file_batch='$BatchID' ORDER BY record_id;");
	if ($Result != false)
	{
		while(true)
		{
			$Row = mysqli_fetch_assoc($Result);
			if ($Row == false)
			{
				break;
			}

			$OutList[] = $Row;
		}

		mysqli_free_result($Result);
	}

	return($OutList);
}

// Retrieve a file upload record
// -----------------------------
function aib_get_upload_record($DBHandle,$RecordID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE record_id=$RecordID;");
	if ($Result == false)
	{
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	return($Row);
}

// Get storage area
// ----------------
function aib_get_storage_area($DBHandle,$StorageID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM ftree_storage WHERE record_id=$StorageID;");
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$Record = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	return($Record);
}
	

// Delete all records associated with a batch from the table
// -------------------------------------------------------
function aib_delete_upload_batch_queue($DBHandle,$BatchID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE file_batch='$BatchID';");
	return;
}

// Delete all files associated with a batch from the upload area
// -------------------------------------------------------------
function aib_delete_upload_batch_files($DBHandle,$BatchID)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE file_batch='$BatchID';");
	if ($Result == false)
	{
		return(false);
	}

	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$FullName = AIB_RECORD_FILE_UPLOAD_PATH.$Row["file_name"];
		system("rm -f \"$FullName\" 2> /dev/null > /dev/null");
	}

	mysqli_free_result($Result);
	return(true);
}

// Given a file ID for a file to be stored, return a hashed path
// -------------------------------------------------------------
function aib_hashed_storage_path($FileID)
{
	$Temp = sprintf("%018.6lf",$FileID);
	$Temp = preg_replace("/[^0-9]/","",$Temp);
	$OutPath = substr($Temp,0,4)."/".substr($Temp,4,4)."/".substr($Temp,8,4)."/".substr($Temp,12,4);
	return($OutPath);
}

// Delete an uploaded file
// -----------------------
function aib_delete_uploaded_file($DBHandle,$FileQueueID,$ItemID,$TargetStorageArea = false,$VerboseFlag = false)
{
	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE record_id=$FileQueueID;");
	if ($Result == false)
	{
		return(array("ERROR","Database error looking for file; ".mysqli_error($DBHandle)));
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(array("ERROR","Cannot retrieve file from uploads"));
	}

	$BatchRecord = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	$SourceFileName = AIB_RECORD_FILE_UPLOAD_PATH."/".urldecode($BatchRecord["file_name"]);
	$Command = "rm -f \"$SourceFileName\" 2> /dev/null > /dev/null";
	system($Command);
	return(array("OK",$SourceFileName));
}

// Get pages in a PDF file
// -----------------------
function aib_get_pdf_page_count($SourceFile)
{
	$PageCount = 0;
	$Buffer = shell_exec("pdfinfo \"$SourceFile\"");
	$BufferLines = explode("\n",$Buffer);
	foreach($BufferLines as $Line)
	{
		if (preg_match("/^Pages[\:]/",$Line) != false)
		{
			$PageCount = preg_replace("/[^0-9]/","",$Line);
			break;
		}
	}

	return($PageCount);
}

// Generate a storage file name
// ----------------------------
function aib_generate_storage_file_name($DBHandle,$TargetStorageArea = false)
{
	if ($TargetStorageArea != false)
	{
		$StorageAreaRecord = aib_get_storage_area($DBHandle,$TargetStorageArea);
		$StoragePath = $StorageAreaRecord["file_storage_base"];
		$StorageAreaID = $StorageAreaRecord["record_id"];
	}
	else
	{
		$StorageAreaRecord = false;
		$StoragePath = AIB_DEFAULT_STORAGE_PATH;
		$StorageAreaID = -1;
	}

	// Get time stamp

	$StorageStamp = sprintf("%018.6lf",microtime(true));

	// Generate hashed storage path

	$HashedStoragePath = aib_hashed_storage_path($StorageStamp);

	// Create stored file name

	$StoredFileName = preg_replace("/[\.]/","_",$StorageStamp);

	// Create storage path if needed

	$FullPath = $StoragePath."/".$HashedStoragePath;
	if (file_exists($FullPath) == false)
	{
		mkdir($FullPath,0777,true);
	}

	// Create output file name and get source file name

	$NewFileName = $FullPath."/".$StoredFileName.".dat";
	$InfoName = $FullPath."/".$StoredFileName.".inf";
	return(array("stored_name" => $StoredFileName.".dat", "base_name" => $StoredFileName, "full_path" => $NewFileName,
		"info_name" => $InfoName,
		"storage_path" => $FullPath,
		"hashed_path" => $HashedStoragePath,
		"stored_path" => $HashedStoragePath."/".$StoredFileName.".dat"));
}

// Create an info file
// -------------------
function aib_store_info_file($FileInfo,$OriginalName,$MIMEType)
{
	$InfoFileName = $FileInfo["info_name"];
	$StatInfo = stat($FileInfo["full_path"]);
	$Handle = fopen($InfoFileName,"w");
	fputs($Handle,"mime-type=".$MIMEType."\n");
	fputs($Handle,"original_name=$OriginalName\n");
	fputs($Handle,"size=".$StatInfo["size"]."\n");
	fputs($Handle,"atime=".$StatInfo["atime"]."\n");
	fputs($Handle,"mtime=".$StatInfo["mtime"]."\n");
	fputs($Handle,"ctime=".$StatInfo["ctime"]."\n");
	fclose($Handle);
	return(true);
}

// Extract a value from a bbox word line spec area
// -----------------------------------------------
function aib_extract_bbox_wordspec($Line,$SpecName)
{
	switch($SpecName)
	{
		case "xmin":
			preg_match("/[\<]word[^\>]+/",$Line,$MatchSet);
			$WordSpecText = $MatchSet[0];
			preg_match("/xMin[=][\"][^\"]+/",$WordSpecText,$MatchSet);
			$Value = preg_replace("/[^0-9\.]/","",$MatchSet[0]);
			$Value = sprintf("%d",intval($Value));
			return($Value);

		case "ymin":
			preg_match("/[\<]word[^\>]+/",$Line,$MatchSet);
			$WordSpecText = $MatchSet[0];
			preg_match("/yMin[=][\"][^\"]+/",$WordSpecText,$MatchSet);
			$Value = preg_replace("/[^0-9\.]/","",$MatchSet[0]);
			$Value = sprintf("%d",intval($Value));
			return($Value);

		case "xmax":
			preg_match("/[\<]word[^\>]+/",$Line,$MatchSet);
			$WordSpecText = $MatchSet[0];
			preg_match("/xMax[=][\"][^\"]+/",$WordSpecText,$MatchSet);
			$Value = preg_replace("/[^0-9\.]/","",$MatchSet[0]);
			$Value = sprintf("%d",intval($Value));
			return($Value);

		case "ymax":
			preg_match("/[\<]word[^\>]+/",$Line,$MatchSet);
			$WordSpecText = $MatchSet[0];
			preg_match("/yMax[=][\"][^\"]+/",$WordSpecText,$MatchSet);
			$Value = preg_replace("/[^0-9\.]/","",$MatchSet[0]);
			$Value = sprintf("%d",intval($Value));
			return($Value);

		case "word":
			preg_match("/[\>][^\<]+/",$Line,$MatchSet);
			$WordSpecText = $MatchSet[0];
			$WordSpecText = preg_replace("/^[\>]/","",$WordSpecText);
			$WordSpecText = preg_replace("/[\<]$/","",$WordSpecText);
			return($WordSpecText);

		default:
			return(false);
	}

	return(false);
}


// Generate location data from bounding-box input
// ----------------------------------------------
function aib_generate_rlc_from_bbox($InputFileName)
{
	$FileBuffer = file_get_contents($InputFileName);
	$LineList = explode("\n",$FileBuffer);
	$Width = 0;
	$Height = 0;
	$OutLines = array();
	foreach($LineList as $Line)
	{
		if (preg_match("/[\<]page width[=]/",$Line) != false)
		{
			preg_match("/width[=][\"][0-9\.]+[\"]/",$Line,$MatchSet);
			$Width = preg_replace("/[^0-9\.]/","",$MatchSet[0]);
			preg_match("/height[=][\"][0-9\.]+[\"]/",$Line,$MatchSet);
			$Height = preg_replace("/[^0-9\.]/","",$MatchSet[0]);
			$Width = intval(sprintf("%0.2f",floatval($Width) / 72) * 300);
			$Height = intval(sprintf("%0.2f",floatval($Height) / 72) * 300);
			$OutLines[] = "$Width $Height 0 0 0 0 0";
			continue;
		}

		if (preg_match("/[\<]word/",$Line) != false)
		{
			$XMin = intval(sprintf("%0.2f",floatval(aib_extract_bbox_wordspec($Line,"xmin")) / 72) * 300);
			$YMin = intval(sprintf("%0.2f",floatval(aib_extract_bbox_wordspec($Line,"ymin")) / 72) * 300);
			$XMax = intval(sprintf("%0.2f",floatval(aib_extract_bbox_wordspec($Line,"xmax")) / 72) * 300);
			$YMax = intval(sprintf("%0.2f",floatval(aib_extract_bbox_wordspec($Line,"ymax")) / 72) * 300);
			$WordText = aib_extract_bbox_wordspec($Line,"word");
			$OutLines[] = "W $XMin $YMin $XMax $YMax 1";
			$Length = strlen($WordText);
			for ($Index = 0; $Index < $Length; $Index++)
			{
				$OutLines[] = "C $XMin $YMin $XMax $YMax 1 ".$WordText[$Index]." 99 0 0 0 0";
			}

			continue;
		}
	}

	$OutBuffer = join("\n",$OutLines);
	return($OutBuffer);
}

// Store file record for an item
//
// RecordInfo is an associative array:
//
//	batch_id		Batch ID
//	original_name		Original File name
//	storage_name		File name as stored
//	mime_type		MIME type
//	processing_info		Processing info
// -----------------------------------------------------------------------------------
function aib_add_file_to_item($DBHandle,$ItemID,$StorageAreaID,$FileClass,$RecordInfo)
{
	$OutData = array();
	$StoredStamp = microtime(true);
	if (isset($RecordInfo["storage_size"]) == true)
	{
		$StorageSize = $RecordInfo["storage_size"];
	}
	else
	{
		$StorageSize = 0;
	}

	$QueryValues = array(
		"'".$RecordInfo["batch_id"]."'",
		"'".urlencode($RecordInfo["original_name"])."'",
		"'".urlencode($RecordInfo["storage_name"])."'",
		"'".urlencode($RecordInfo["mime_type"])."'",
		"'".urlencode($RecordInfo["processing_info"])."'",
		$StoredStamp,
		$ItemID,
		$StorageAreaID,
		"'".$FileClass."'",
		$StorageSize);
	$QueryValueString = join(",",$QueryValues);
	$Query = "INSERT INTO ftree_files (file_batch,file_original_name,file_stored_name,file_mime_type,file_process_status,file_stored_stamp,".
		"file_item_id,file_storage_area,file_class,file_size) VALUES ($QueryValueString);";
	$InsertResult = mysqli_query($DBHandle,$Query);
	if ($InsertResult == false)
	{
		$ErrorMsg = mysqli_error($DBHandle);
		$OutData["id"] = false;
		$OutData["status"] = $ErrorMsg;
		return($OutData);
	}

	$OutData["id"] = $InsertResult;
	$OutData["stamp"] = $StoredStamp;
	return($OutData);
}

// Generate page image from PDF
// ----------------------------
function aib_generate_pdf_page_image($SourcePDF,$PageNumber,$MaxWidth, $MaxHeight, $TargetFileNameBase)
{
	$PPMFile = $TargetFileNameBase.".ppm";
	$JPEGFile = $TargetFileNameBase.".jpg";
	system("pdftoppm -f $PageNumber -l $PageNumber -rx 300 -ry 300 -cropbox \"$SourcePDF\" \"$PPMFile\"");

	// If width and/or height is set, scale image.  Else just convert to JPEG.

	if ($MaxWidth > 0 || $MaxHeight > 0)
	{
		$Command = "pnmscale \"$PPMFile\"";
		if ($MaxWidth > 0)
		{
			$Command .= " -xsize=$MaxWidth";
		}

		if ($MaxHeight > 0)
		{
			$Command .= " -ysize=$MaxHeight";
		}

		$Command .= " | ppmtojpeg > \"$JPEGFile\"";
		system($Command);
		system("rm -f \"$PPMFile\" 2> /dev/null > /dev/null");
	}
	else
	{
		system("cat \"$PPMFile\" | ppmtojpeg > \"$JPEGFile\"");
		system("rm -f \"$PPMFile\" 2> /dev/null > /dev/null");
	}

	return(true);
}


// Store PDF file; accounts for multiple pages
// -------------------------------------------
function aib_store_uploaded_pdf($DBHandle,$BatchRecord,$SourceFile,$FileQueueID,$ItemID,$TargetStorageArea = false,$VerboseFlag = false,$GenerateThumbFlag = false,$UserID = -1, $GroupID = -1,$OptionData = false)
{
	$UseAltTitle = false;

	if ($OptionData != false)
	{
		if (isset($OptionData[AIB_BATCH_USE_ALT_TITLE]) == true)
		{
			$UseAltTitle = $OptionData[AIB_BATCH_USE_ALT_TITLE];
		}
	}

	$NewItemErrors = array();

	// Get source file name and extension

	$FileInfo = array();
	$SourceSeg = explode(".",$SourceFile);
	if (count($SourceSeg) < 2)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Invalid original file name", "info" => $FileInfo, "file" => $BatchRecord);
		return($NewItemErrors);
	}

	$SourceFileExt = array_pop($SourceSeg);

	// Get the item record and the parent item record.  Get field data from parent.

	$ItemRecord = ftree_get_item($DBHandle,$ItemID);
	$ItemTags = aib_get_item_tags($DBHandle,$ItemID);
	if ($ItemTags == false)
	{
		$ItemTags = array();
	}

	$ItemTagString = join(",",$ItemTags);
	$ParentItemID = $ItemRecord["item_parent"];
	$ParentRecord = ftree_get_item($DBHandle,$ParentItemID);
	$ParentItemTitle = urldecode($ParentRecord["item_title"]);
	$ParentFields = ftree_field_get_item_fields_ext($DBHandle,$ParentItemID);
	if ($ParentFields == false)
	{
		$ParentFields = array();
	}

	// If a storage area was defined, get it.  Otherwise use the default path.

	if ($TargetStorageArea != false)
	{
		$StorageAreaRecord = aib_get_storage_area($DBHandle,$TargetStorageArea);
		$StoragePath = $StorageAreaRecord["file_storage_base"];
		$StorageAreaID = $StorageAreaRecord["record_id"];
	}
	else
	{
		$StorageAreaRecord = false;
		$StoragePath = AIB_DEFAULT_STORAGE_PATH;
		$StorageAreaID = -1;
	}

	// Get the number of pages in the PDF

	$PageCount = aib_get_pdf_page_count($SourceFile);
	if ($PageCount == 0)
	{
		return(false);
	}

	$SourceFileSize = filesize($SourceFile);

	// Create temporary file names

	$LocalStamp = sprintf("%0.6lf",microtime(true));
	$LocalStamp = preg_replace("/[^0-9]/","",$LocalStamp);
	$ProcessFileName = "/tmp/".$LocalStamp;

	// Extract each page and render to file which is then added as an item

	$BatchID = $BatchRecord["file_batch"];
	$SourceFieldData = false;
	$StorableFieldData = array();

	// Get field data to be replicated for the pages.  If the ItemID is not false, use the
	// data from that.  Otherwise, use whatever was generated for the parent record.

	if ($ItemID !== false)
	{
		$FieldDataList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ItemID);
	}
	else
	{
		$FieldDataList = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ParentItemID);
	}

	if ($FieldDataList == false)
	{
		$FieldDataList = array();
	}

	foreach($FieldDataList as $FieldID => $FieldInfo)
	{
		$StorableFieldData[$FieldID] = $FieldInfo["value"];
	}

	// Store the original file, associated with parent record.  First, get the original base name.

	$SourceSeg = explode("/",$SourceFile);
	$OriginalName = array_pop($SourceSeg);

	// Generate file name

	$OriginalNameInfo = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);

	// Copy file

	$OutputOriginalName = $OriginalNameInfo["full_path"];
	$CommandLine = "cp -f \"$SourceFile\" \"$OutputOriginalName\" > /dev/null 2> /dev/null";
	system($CommandLine);

	// If the file isn't there, error

	if (file_exists($OutputOriginalName) == false)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store original PDF file", "info" => $FileInfo, "file" => $BatchRecord);
		return($NewItemErrors);
	}

	// Associate original file

	$FileInfo = array("batch_id" => $BatchID, "original_name" => $OriginalName, "storage_name" => $OriginalNameInfo["stored_path"],
		"mime_type" => "application/x-pdf", "processing_info" => "src_bch=".$BatchRecord["file_batch"],"storage_size" => $SourceFileSize);
	$FileResult = aib_add_file_to_item($DBHandle,$ParentItemID,$StorageAreaID,AIB_FILE_CLASS_ORIGINAL,$FileInfo);
	if ($FileResult["id"] === false)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot associate PDF file with item", "info" => $FileInfo, "file" => $BatchRecord);
		return($NewItemErrors);
	}

	aib_store_info_file($OriginalNameInfo,$OriginalName,"application/x-pdf");

	// Store image, thumbnail, text and location data for each page.  If the first
	// page item has already been created, associate image with that.  Otherwise,
	// create new item for each page.

	$OCRFieldDef = ftree_get_field_by_symbolic_name($DBHandle,AIB_PREDEF_FIELD_OCRTEXT);
	$CurrentID = $ItemID;
	for ($PageNum = 1; $PageNum <= $PageCount; $PageNum ++)
	{
		$PageTitle = urlencode(urldecode($ItemRecord["item_title"])." ".sprintf(" Page %5d",$PageNum));

		// Generate file names

		$BaseImageFileName = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
		$BaseThumbFileName = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
		$BaseTextFileName = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
		$BaseLocationFileName = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);

		// Get the full path file names for command lines

		$ImageFileName = $BaseImageFileName["full_path"];
		$ThumbFileName = $BaseThumbFileName["full_path"];
		$TextFileName = $BaseTextFileName["full_path"];
		$LocationFileName = $BaseLocationFileName["full_path"];

		// Set temporary file names

		while(true)
		{
			if ($PageCount < 10)
			{
				$OutputProcessFileName = $ProcessFileName."-".$PageNum.".ppm";
				$OutputTextProcessFileName = $ProcessFileName."-".$PageNum.".txt";
				break;
			}

			if ($PageCount < 100)
			{
				$OutputProcessFileName = $ProcessFileName."-".sprintf("%02d",$PageNum).".ppm";
				$OutputTextProcessFileName = $ProcessFileName."-".sprintf("%02d",$PageNum).".txt";
				break;
			}

			if ($PageCount < 1000)
			{
				$OutputProcessFileName = $ProcessFileName."-".sprintf("%03d",$PageNum).".ppm";
				$OutputTextProcessFileName = $ProcessFileName."-".sprintf("%03d",$PageNum).".txt";
				break;
			}

			if ($PageCount < 10000)
			{
				$OutputProcessFileName = $ProcessFileName."-".sprintf("%04d",$PageNum).".ppm";
				$OutputTextProcessFileName = $ProcessFileName."-".sprintf("%04d",$PageNum).".txt";
				break;
			}

			$OutputProcessFileName = $ProcessFileName."-".$PageNum.".ppm";
			$OutputTextProcessFileName = $ProcessFileName."-".$PageNum.".txt";
			break;
		}

		$OutputLocationProcessFileName = $ProcessFileName.".box";

		// Extract page image from PDF and put in temp file

		if ($VerboseFlag != false)
		{
			print("STATUS: Extracting page $PageNum to $OutputProcessFileName for ".urldecode($PageTitle)."\n");
		}

		system("pdftoppm -f $PageNum -l $PageNum -rx 300 -ry 300 -cropbox \"$SourceFile\" \"$ProcessFileName\"");

		// Extract page text from PDF; first the raw text and then the location data for the text; put in temp files

		if ($VerboseFlag != false)
		{
			print("STATUS: Extracting page text from $SourceFile to $OutputTextProcessFileName\n");
		}

		system("pdftotext -f $PageNum -l $PageNum -raw \"$SourceFile\" \"$OutputTextProcessFileName\" 2> /dev/null");
		system("pdftotext -f $PageNum -l $PageNum -bbox \"$SourceFile\" \"$OutputLocationProcessFileName\" 2> /dev/null");

		if (file_exists($OutputTextProcessFileName) == false)
		{
			if ($VerboseFlag != false)
			{
				print("WARNING: Could not extract page text for page $PageNum in $SourceFile\n");
			}
		}
		else
		{
			if ($VerboseFlag != false)
			{
				print("STATUS: Extracted ".filesize($OutputTextProcessFileName)." characters for page $PageNum\n");
			}
		}

		// Convert location data to format required by system

		if ($VerboseFlag != false)
		{
			print("STATUS: Page text generated for page $PageNum\n");
			print("STATUS: Generating text location file from $SourceFile to $OutputLocationProcessFileName\n");
		}

		$RLCData = aib_generate_rlc_from_bbox($OutputLocationProcessFileName);

		// Get text

		if (file_exists($OutputTextProcessFileName) == true)
		{
			$PageTextBuffer = file_get_contents($OutputTextProcessFileName);
		}
		else
		{
			$PageTextBuffer = false;
		}

		// Create JPEG image for viewing, setting the scale

		system("pnmscale \"$OutputProcessFileName\" -xsize=".AIB_MAX_IMAGE_WIDTH." | ppmtojpeg > \"$ImageFileName\"");

		// Scale to the thumbnail size, and convert to JPEG if there's a thumbnail

		if ($GenerateThumbFlag !== false)
		{
			system("pnmscale $OutputProcessFileName -xsize=".AIB_DEFAULT_THUMBNAIL_WIDTH." |  ppmtojpeg > \"$ThumbFileName\"");
		}

		// Clean up text files

		system("rm -f \"$OutputTextProcessFileName\" > /dev/null 2> /dev/null");
		system("rm -f \"$OutputLocationProcessFileName\" > /dev/null 2> /dev/null");

		// Clean up image file

		system("rm -f \"$OutputProcessFileName\" > /dev/null 2> /dev/null");

		// If the page images can't be generated, ignore page.

		if (file_exists($ImageFileName) == false)
		{
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot generate image file from $OutputProcessFileName to $ImageFileName\n");
			}

			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot create image file(s)", "info" => "", "file" => $BatchRecord);
			continue;
		}

		$ImageFileSize = filesize($ImageFileName);
		if ($GenerateThumbFlag !== false && file_exists($ThumbFileName) == false)
		{
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot generate thumbnail file from $OutputProcessFileName to $ThumbFileName\n");
			}

			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot create thumbnail file(s)", "info" => "", "file" => $BatchRecord);
			continue;
		}

		if ($GenerateThumbFlag !== false)
		{
			$ThumbFileSize = filesize($ThumbFileName);
		}
		else
		{
			$ThumbFileSize = 0;
		}

		// Output text and text location data

		$LocationFileSize = 0;
		if ($RLCData != false)
		{
			file_put_contents($LocationFileName,$RLCData);
			if (file_exists($LocationFileName) == false)
			{
				$RLCData = false;
			}
			else
			{
				$LocationFileSize = filesize($LocationFileName);
			}
		}

		$TextFileSize = 0;
		if ($PageTextBuffer != false)
		{
			file_put_contents($TextFileName,$PageTextBuffer);
			if (file_exists($TextFileName) == false)
			{
				$PageTextBuffer = false;
			}
			else
			{
				$TextFileSize = filesize($TextFileName);
			}

			if ($RLCData == false && $PageTextBuffer !== false)
			{
				$RLCData = "";
				file_put_contents($LocationFileName,$RLCData);
			}

		}

		// If the current ID is set, use it.  Else, create a new item

		if ($CurrentID !== false)
		{
			$NewItemID = $CurrentID;
			$CurrentID = false;
			ftree_rename($GLOBALS["aib_db"],$NewItemID,$PageTitle);
		}
		else
		{
			$ItemInfo = array("parent" => $ParentItemID,
				"title" => $PageTitle,
				"user_id" => $UserID,
				"group_id" => $GroupID,
				"item_type" => FTREE_OBJECT_TYPE_FILE,
				"source_type" => FTREE_SOURCE_TYPE_INTERNAL,
				"source_info" => "",
				"reference_id" => -1,
				"allow_dups" => true,
				"user_perm" => "RMWCODPN",
				"group_perm" => "RMW",
				"world_perm" => "R",
				);
			if ($UseAltTitle != false)
			{
				$ItemInfo["title"] = urldecode($UseAltTitle);
			}

			$ItemResult = ftree_create_object_ext($GLOBALS["aib_db"],$ItemInfo);
			if ($ItemResult[0] != "OK")
			{
				$NewItemErrors[] = array("status" => "ERROR", "error" => $ItemResult[1], "info" => $ItemInfo, "file" => $BatchRecord);
				continue;
			}
			else
			{
				$NewItemID = $ItemResult[1];
			}

		}

		// If there are tags, add them

		if (ltrim(rtrim($ItemTagString)) != "")
		{
			aib_add_item_tags($DBHandle,$NewItemID,$ItemTagString);
		}

		// If the OCR text is too small, render full-size page to a temp JPG file and copy to OCR area

		if ($TextFileSize < AIB_PDF_MIN_PAGE_TEXT_SIZE)
		{
			if ($VerboseFlag != false)
			{
				print("WARNING: Extracted text is too small; submitting page image for page $PageNum to OCR processing\n");
			}

			$BaseJPEGName = "ocr_image_".$NewItemID.".jpg";
			$JPEGFile = AIB_OCR_FILE_QUEUE_PATH."/$BaseJPEGName";
			system("pdftoppm -f $PageNum -l $PageNum -rx 300 -ry 300 -cropbox \"$SourceFile\" | ppmtojpeg --quality=100 > \"$JPEGFile\"");
			$PageInfoArray = array("tree_item=$NewItemID","profile=$OCRProfile","languages=eng","source=$BaseJPEGName");
			$PageInfoString = join("\t",$PageInfoArray);
			aib_store_file_batch_entry($GLOBALS["aib_db"],AIB_BATCH_RECORD_TYPE_OCR_REQUEST,$PageInfoString,$NewItemID);
		}

		// Save OCR text

//		if ($OCRFieldDef != false && $PageTextBuffer != false)
//		{
//			$OCRValueSet = array();
//			$OCRValueSet[$OCRFieldDef["field_id"]] = ltrim(rtrim($PageTextBuffer));
//			ftree_field_store_item_fields($DBHandle,$NewItemID,$OCRValueSet,false);
//		}

		// Store field data for the item

		ftree_field_store_item_fields($DBHandle,$NewItemID,$StorableFieldData);

		// Associate image with item

		if ($VerboseFlag != false)
		{
			print("STATUS: Attaching files for page $PageNum to $NewItemID ($ParentItemTitle)\n");
			print("STATUS: Storing $OutputProcessFileName to image $ImageFileName\n");
		}

		$FileInfo = array("batch_id" => $BatchID, "original_name" => $BaseImageFileName["base_name"].".jpg", "storage_name" => $BaseImageFileName["stored_path"],
			"mime_type" => "image/jpeg", "processing_info" => "src_bch=".$BatchRecord["file_batch"], "storage_size" => $ImageFileSize);
		$FileResult = aib_add_file_to_item($DBHandle,$NewItemID,$StorageAreaID,AIB_FILE_CLASS_PRIMARY,$FileInfo);
		if ($FileResult["id"] === false)
		{
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot create entry for primary file ".$BaseImageFileName["base_name"]."\n");
			}

			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store primary file", "info" => $FileInfo, "file" => $BatchRecord);
			continue;
		}

		if ($VerboseFlag != false)
		{
			print("STATUS: Storing $OutputProcessFileName to thumbnail $ThumbFileName\n");
		}

		aib_store_info_file($BaseImageFileName,$OriginalName,"image/jpeg");

		// Associate thumbnail with item

		$FileInfo = array("batch_id" => $BatchID, "original_name" => $BaseThumbFileName["base_name"].".jpg", "storage_name" => $BaseThumbFileName["stored_path"],
			"mime_type" => "image/jpeg", "processing_info" => "src_bch=".$BatchRecord["file_batch"],"storage_size" => $ThumbFileSize);
		$FileResult = aib_add_file_to_item($DBHandle,$NewItemID,$StorageAreaID,AIB_FILE_CLASS_THUMB,$FileInfo);
		if ($FileResult["id"] === false)
		{
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot create entry for thumbnail file ".$BaseThumbFileName["base_name"]."\n");
			}

			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store thumbnail file", "info" => $FileInfo, "file" => $BatchRecord);
			continue;
		}

		aib_store_info_file($BaseThumbFileName,$OriginalName,"image/jpeg");

		// Associate page text with item, if page text was found.  Place text in ocr text field as well

		if ($PageTextBuffer != false)
		{
			$FileInfo = array("batch_id" => $BatchID, "original_name" => $BaseTextFileName["base_name"].".txt", "storage_name" => $BaseTextFileName["stored_path"],
				"mime_type" => "text/ascii", "processing_info" => "src_bch=".$BatchRecord["file_batch"],"storage_size" => $TextFileSize);
			$FileResult = aib_add_file_to_item($DBHandle,$NewItemID,$StorageAreaID,AIB_FILE_CLASS_TEXT,$FileInfo);
			if ($FileResult["id"] === false)
			{
				$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store text file", "info" => $FileInfo, "file" => $BatchRecord);
				continue;
			}

			aib_store_info_file($BaseTextFileName,$OriginalName,"text/ascii");
			if ($VerboseFlag != false)
			{
				print("STATUS: Saved text for page\n");
			}

			// Save field data to OCR text field

			if ($OCRFieldDef !== false)
			{
				ftree_field_store_item_field($DBHandle,$NewItemID,$OCRFieldDef,urlencode($PageTextBuffer));
				if ($VerboseFlag != false)
				{
					print("STATUS: Saved text for page to OCR field\n");
				}

			}

		}
		else
		{
			// Submit PDF image for OCR if no text found.  Generate full-size image and place in OCR image storage area.  The name of the image
			// is the item id as a jpeg, so "ocr_image_<itemid>.jpg".

			$TargetImageFileNameBase = AIB_OCR_FILE_QUEUE_PATH."/ocr_image_".$NewItemID;
			aib_generate_pdf_page_image($SourceFile,$PageNum,-1,-1,$TargetImageFileBaseName);
			$LocalList = array("tree_item=$NewItemID","profile=NULL","source=ocr_image_".$NewItemID.".jpg","languages=eng");
			$LocalBatchInfo = join("\t",$LocalList);
			aib_store_file_batch_entry($DBHandle,AIB_BATCH_RECORD_TYPE_OCR_REQUEST,$LocalBatchInfo,0);
		}

		// Associate location data with item, if page text was found and RLC data could be created

		if ($RLCData != false)
		{
			$FileInfo = array("batch_id" => $BatchID, "original_name" => $BaseTextFileName["base_name"].".rlc", "storage_name" => $BaseLocationFileName["stored_path"],
				"mime_type" => "text/ascii", "processing_info" => "src_bch=".$BatchRecord["file_batch"],"storage_size" => $LocationFileSize);
			$FileResult = aib_add_file_to_item($DBHandle,$NewItemID,$StorageAreaID,AIB_FILE_CLASS_TEXT_LOCATION,$FileInfo);
			if ($FileResult["id"] === false)
			{
				$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store thumbnail file", "info" => $FileInfo, "file" => $BatchRecord);
				continue;
			}

			aib_store_info_file($BaseLocationFileName,$OriginalName,"text/ascii");
		}

		// Remove temporary files

		system("rm -f $ProcessFileName");
		system("rm -f $OutputProcessFileName");
		system("rm -f $OutputProcessFileName");
		$NewItemErrors[] = array("status" => "OK", "file" => $BatchRecord);
	}

	return($NewItemErrors);
}

// Get name of an uploaded file
// ----------------------------
function aib_get_uploaded_file_name($DBHandle,$FileQueueID)
{
	// Get the parent item record

	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE record_id=$FileQueueID;");
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$BatchRecord = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	$SourceFileName = AIB_RECORD_FILE_UPLOAD_PATH."/".urldecode($BatchRecord["file_name"]);
	return($SourceFileName);
}


// Store an uploaded file
// ----------------------
function aib_store_uploaded_file($DBHandle,$FileQueueID,$ItemID,$TargetStorageArea = false,$VerboseFlag = false,$GenerateThumbFlag = false,$UserID = -1, $GroupID = -1, $DeleteFlag = true)
{
	// Get the parent item record

	$NewItemErrors = array();
	$Result = mysqli_query($DBHandle,"SELECT * FROM file_uploads WHERE record_id=$FileQueueID;");
	if ($Result == false)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot get batch record", "info" => array("item_id" => $ItemID, "queue_id" => $FileQueueID), "file" => false);
		return($NewItemErrors);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot get batch record", "info" => array("item_id" => $ItemID, "queue_id" => $FileQueueID), "file" => false);
		return($NewItemErrors);
	}

	$BatchRecord = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	$FileID = $BatchRecord["record_id"];
	$BatchID = $BatchRecord["file_batch"];
	
	$SourceFileName = AIB_RECORD_FILE_UPLOAD_PATH."/".urldecode($BatchRecord["file_name"]);
	if (file_exists($SourceFileName) == true)
	{
		$SourceFileSize = filesize($SourceFileName);
	}
	else
	{
		$SourceFileSize = -1;
	}

	$SourceSegs = explode("/",$SourceFileName);
	$OriginalName = array_pop($SourceSegs);

	// Get file MIME info

	$Buffer = shell_exec("file -F '|' -i \"$SourceFileName\"");
	if ($Buffer == false)
	{
		if ($VerboseFlag != false)
		{
			print("ERROR: Cannot get MIME info\n");
		}

		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot get MIME info", "info" => array("item_id" => $ItemID, "queue_id" => $FileQueueID), "file" => $BatchRecord);
		return($NewItemErrors);
	}

	$Buffer = ltrim(rtrim($Buffer));
	$Segs = preg_split("/[\|]/",$Buffer);
	$MIMEInfo = $Segs[1];

	// Process based on MIME type

	// PDF; call a special storage handler

	if (preg_match("/[\.][Pp][Dd][Ff]$/",$SourceFileName) != false || preg_match("/[Pp][Dd][Ff]/",$MIMEInfo) != false)
	{
		return(aib_store_uploaded_pdf($DBHandle,$BatchRecord,$SourceFileName,$FileQueueID,$ItemID,$TargetStorageArea,$VerboseFlag,$GenerateThumbFlag,$UserID,$GroupID));
	}

	// For all others, process locally.  First, get storage area.
	// If a storage area was defined, get it.  Otherwise use the default path.

	if ($TargetStorageArea != false)
	{
		$StorageAreaRecord = aib_get_storage_area($DBHandle,$TargetStorageArea);
		$StoragePath = $StorageAreaRecord["file_storage_base"];
		$StorageAreaID = $StorageAreaRecord["record_id"];
	}
	else
	{
		$StorageAreaRecord = false;
		$StoragePath = AIB_DEFAULT_STORAGE_PATH;
		$StorageAreaID = -1;
	}

	// Create output image file, thumb and information files.  Even if the source is not an image,
	// we'll need to create it.

	$StatInfo = stat($SourceFileName);
	$UploadedFileNameInfo = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
	$ImageFileNameInfo = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
	$ThumbFileNameInfo = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
	$ImageFileName = $ImageFileNameInfo["full_path"];
	$ThumbFileName = $ThumbFileNameInfo["full_path"];
	$UploadedFileName = $UploadedFileNameInfo["full_path"];
	$LocalStamp = sprintf("%0.6lf",microtime(true));
	$LocalStamp = preg_replace("/[^0-9]/","",$LocalStamp);
	$ProcessFileName = "/tmp/".$LocalStamp;
	$OutputProcessFileName = $ProcessFileName.".ppm";
	$TempJpegFileName = $ProcessFileName.".jpg";

	$SourceNameSegs = explode("/",$SourceFileName);
	$OriginalSourceFileName = array_pop($SourceNameSegs);

	// Image files

	if (preg_match("/[Ii][Mm][Aa][Gg][Ee]/",$MIMEInfo) != false)
	{
		// Load the file into an image buffer

		try
		{
			$ImageInfoBuffer = new Imagick($SourceFileName);
		}
		catch(Exception $Except)
		{
			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot load image into Imagick", "info" => $Except, "file" => $BatchRecord);
			if ($VerboseFlag != false)
			{
				print("ERROR: Can't load image into Imagick: $Except\n");
			}

			return($NewItemErrors);
		}

		// If the image is too big, resize it

		if ($ImageInfoBuffer->getImageWidth() > AIB_MAX_IMAGE_WIDTH)
		{
			$ImageInfoBuffer->scaleImage(AIB_MAX_IMAGE_WIDTH,0);
		}


		// If we're storing the originally uploaded file, do that here.

		if (AIB_STORE_ORIGINAL_UPLOAD_FILE == "Y")
		{
			// Write original file to storage; associate with item

			if ($VerboseFlag != false)
			{
				print("STATUS: Copying original file from $SourceFileName TO $UploadedFileName\n");
			}
	
			system("cp \"$SourceFileName\" \"$UploadedFileName\" 2> /dev/null > /dev/null");
			if (file_exists($UploadedFileName) == false)
			{
				unset($ImageInfoBuffer);
				system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
				$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store original file", "info" => $UploadedFileName, "file" => $BatchRecord);
				if ($VerboseFlag != false)
				{
					print("ERROR: Cannot store original file to $UploadedFileName\n");
				}
	
				return($NewItemErrors);
			}
	
			$UploadedSize = filesize($UploadedFileName);
			$FileInfo = array("batch_id" => $BatchID, "original_name" => $OriginalSourceFileName, "storage_name" => $UploadedFileNameInfo["stored_path"],
				"mime_type" => $MIMEInfo, "processing_info" => "src_bch=".$BatchRecord["file_batch"], "original_size" => $SourceFileSize, "storage_size" => $UploadedSize);
			$FileResult = aib_add_file_to_item($DBHandle,$ItemID,$StorageAreaID,AIB_FILE_CLASS_ORIGINAL,$FileInfo);
			if ($FileResult["id"] === false)
			{
				unset($ImageInfoBuffer);
				$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store original file reference", "info" => $FileResult["status"], "file" => $BatchRecord);
				if ($VerboseFlag != false)
				{
					print("ERROR: Cannot store original file reference in database\n");
				}
	
				system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
				return($NewItemErrors);
			}

			aib_store_info_file($UploadedFileNameInfo,$OriginalSourceFileName,$MIMEInfo);

			// Write image file to storage; associate with item

			if ($VerboseFlag != false)
			{
				print("STATUS: Writing image file to storage ($ImageFileName)\n");
			}
		}

		$ImageInfoBuffer->setCompressionQuality(75);
		$ImageInfoBuffer->setImageFormat("jpeg");
		$ImageInfoBuffer->writeImage($ImageFileName);
		if (file_exists($ImageFileName) == false)
		{
			unset($ImageInfoBuffer);
			system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store image file reference", "info" => $ImageFileName, "file" => $BatchRecord);
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot store image file to $ImageFileName\n");
			}

			return($NewItemErrors);
		}

		$ImageFileSize = filesize($ImageFileName);
		$FileInfo = array("batch_id" => $BatchID, "original_name" => $OriginalSourceFileName, "storage_name" => $ImageFileNameInfo["stored_path"],
			"mime_type" => "image/jpeg", "processing_info" => "src_bch=".$BatchRecord["file_batch"], "storage_size" => $ImageFileSize);
		$FileResult = aib_add_file_to_item($DBHandle,$ItemID,$StorageAreaID,AIB_FILE_CLASS_PRIMARY,$FileInfo);
		if ($FileResult["id"] === false)
		{
			unset($ImageInfoBuffer);
			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store primary image file reference", "info" => $FileResult["status"], "file" => $BatchRecord);
			system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot store primary file reference in database\n");
			}

			return($NewItemErrors);
		}

		aib_store_info_file($ImageFileNameInfo,$OriginalSourceFileName,"image/jpeg");

		// Write thumbnail to storage; associate with item

		if ($VerboseFlag != false)
		{
			print("STATUS: Writing thumbnail file to storage ($ThumbFileName)\n");
		}

		$ImageInfoBuffer->scaleImage(AIB_DEFAULT_THUMBNAIL_WIDTH,0);
		$ImageInfoBuffer->setCompressionQuality(90);
		$ImageInfoBuffer->writeImage($ThumbFileName);
		if (file_exists($ThumbFileName) == false)
		{
			system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
			unset($ImageInfoBuffer);
			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store thumbnail file", "info" => $ThumbFileName, "file" => $BatchRecord);
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot store thumbnail $ThumbFileName\n");
			}

			return($NewItemErrors);
		}

		$ThumbFileSize = filesize($ThumbFileName);
		$OriginalThumbName = $ThumbFileNameInfo["base_name"].".jpg";
		$FileInfo = array("batch_id" => $BatchID, "original_name" => $OriginalThumbName, "storage_name" => $ThumbFileNameInfo["stored_path"],
			"mime_type" => "image/jpeg", "processing_info" => "src_bch=".$BatchRecord["file_batch"], "storage_size" => $ThumbFileSize);
		$FileResult = aib_add_file_to_item($DBHandle,$ItemID,$StorageAreaID,AIB_FILE_CLASS_THUMB,$FileInfo);
		if ($FileResult["id"] === false)
		{
			$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot store thumbnail image file reference", "info" => $FileResult["status"], "file" => $BatchRecord);
			if ($VerboseFlag != false)
			{
				print("ERROR: Cannot store thumbnail reference in database\n");
			}

			system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
			unset($ImageInfoBuffer);
			return($NewItemErrors);
		}

		aib_store_info_file($ThumbFileNameInfo,$OriginalSourceFileName,"image/jpeg");
		unset($ImageInfoBuffer);
		if ($DeleteFlag == true)
		{
			system("rm -f \"$SourceFileName\" 2> /dev/null > /dev/null");
		}

		if ($VerboseFlag != false)
		{
			print("STATUS: Stored file $SourceFileName\n");
		}

		$NewItemErrors[] = array("status" => "OK", "msg" => "Stored file", "info" => array("src" => $SourceFileName), "file" => $BatchRecord);
		return($NewItemErrors);
	}

	// If text file, create an image with the first 24 lines of text

	// If a sound file, create a spectogram of the first non-silent second as the image

	// If a video file, extract first non-blank frame as an image

	// Unrecognized file

	$NewItemErrors[] = array("status" => "ERROR", "error" => "Unrecognized file type", "info" => $SourceFileName, "file" => $BatchRecord);
	return($NewItemErrors);
}

// Store a file associated with an item
// ------------------------------------
function aib_store_file($DBHandle,$SourceFileName,$FileClass,$ItemID,$TargetStorageArea = false,$UserID = -1, $GroupID = -1,$DeleteOriginalFlag = true)
{
	// Get the item definition

	$ItemRecord = ftree_get_item($DBHandle,$ItemID);
	if ($ItemRecord == false)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot get item information", "info" => array("item_id" => $ItemID), "file" => false);
		return($NewItemErrors);
	}

	// Make sure the source file exists

	if (file_exists($SourceFileName) == false)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot find file", "info" => array("item_id" => $ItemID, "source" => $SourceFileName), "file" => false);
		return($NewItemErrors);
	}

	// Get file size and original name

	$SourceFileSize = filesize($SourceFileName);
	$SourceSegs = explode("/",$SourceFileName);
	$OriginalName = array_pop($SourceSegs);

	// Get file MIME info

	$Buffer = shell_exec("file -F '|' -i \"$SourceFileName\"");
	if ($Buffer == false)
	{
		if ($VerboseFlag != false)
		{
			print("ERROR: Cannot get MIME info\n");
		}

		$NewItemErrors[] = array("status" => "ERROR", "error" => "Cannot get MIME info", "info" => array("item_id" => $ItemID, "source" => $SourceFileName), "file" => false);
		return($NewItemErrors);
	}

	$Buffer = ltrim(rtrim($Buffer));
	$Segs = preg_split("/[\|]/",$Buffer);
	$MIMEInfo = $Segs[1];

	// For all others, process locally.  First, get storage area.
	// If a storage area was defined, get it.  Otherwise use the default path.

	if ($TargetStorageArea != false)
	{
		$StorageAreaRecord = aib_get_storage_area($DBHandle,$TargetStorageArea);
		$StoragePath = $StorageAreaRecord["file_storage_base"];
		$StorageAreaID = $StorageAreaRecord["record_id"];
	}
	else
	{
		$StorageAreaRecord = false;
		$StoragePath = AIB_DEFAULT_STORAGE_PATH;
		$StorageAreaID = -1;
	}

	// Get source file name info

	$StatInfo = stat($SourceFileName);

	// Generate a storage file name

	$StorageFileNameInfo = aib_generate_storage_file_name($DBHandle,$TargetStorageArea);
	$ImageFileName = $StorageFileNameInfo["full_path"];
	$LocalStamp = sprintf("%0.6lf",microtime(true));
	$LocalStamp = preg_replace("/[^0-9]/","",$LocalStamp);
	$SourceNameSegs = explode("/",$SourceFileName);

	// Copy the file

	$OriginalSourceFileName = array_pop($SourceNameSegs);
	$CommandLine = "cp -f \"$SourceFileName\" \"$ImageFileName\" > /dev/null 2> /dev/null";
	system($CommandLine);

	// If the file isn't there, error

	if (file_exists($ImageFileName) == false)
	{
		$NewItemErrors[] = array("status" => "ERROR", "error" => "STOREFAIL", "error_info" => "Target file was not created", "info" => array(
			"item_id" => $ItemID, "source" => $SourceFileName, "dest" => $ImageFileName));
		return($NewItemErrors);
	}

	// Delete temporary file

	if ($DeleteOriginalFlag == true)
	{
		$CommandLine = "rm -f \"$SourceFileName\"";
		system($CommandLine);
	}

	// Create info file

	aib_store_info_file($StorageFileNameInfo,$OriginalSourceFileName,$MIMEInfo);

	// Associate the file with the item

	$FileInfo = array("batch_id" => "NULL", "original_name" => $OriginalSourceFileName, "storage_name" => $StorageFileNameInfo["stored_path"],
		"mime_type" => $MIMEInfo, "processing_info" => "", "original_size" => $SourceFileSize, "storage_size" => $SourceFileSize);
	$FileResult = aib_add_file_to_item($DBHandle,$ItemID,$StorageAreaID,$FileClass,$FileInfo);
	$NewItemErrors[] = array("status" => "OK", "error" => "", "info" => array("item_id" => $ItemID, "source" => $SourceFileName, "dest" => $ImageFileName));
	return($NewItemErrors);
}

// Get all files associated with an item
// -------------------------------------
function aib_get_files_for_item($DBHandle,$ItemID,$FileClass = false)
{
	if ($FileClass == false)
	{
		$Query = "SELECT * FROM ftree_files WHERE file_item_id=$ItemID;";
	}
	else
	{
		$Query = "SELECT * FROM ftree_files WHERE file_item_id=$ItemID AND file_class='$FileClass';";
	}

	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(false);
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}

	mysqli_free_result($Result);
	return($OutList);
}

// Get path to stored file
// -----------------------
function aib_get_file_info($DBHandle,$FileID)
{
	$Query = "SELECT * FROM ftree_files WHERE record_id=$FileID;";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	if ($Row == false)
	{
		return(false);
	}

	$StorageArea = $Row["file_storage_area"];
	$Name = $Row["file_stored_name"];
	$MIMESegs = explode(";",urldecode($Row["file_mime_type"]));
	$MIMEType = $MIMESegs[0];

	// Get storage area def if needed

	if ($StorageArea >= 0)
	{
		$Result = mysqli_query($DBHandle,"SELECT * FROM ftree_storage WHERE record_id=$StorageArea;");
		if ($Result == false)
		{
			return(false);
		}

		$Row = mysqli_fetch_assoc($Result);
		mysqli_free_result($Result);
		if ($Row == false)
		{
			return(false);
		}

		$StoragePath = $Row["file_storage_base"];
	}
	else
	{
		$StoragePath = AIB_DEFAULT_STORAGE_PATH;
	}

	return(array("storage_area" => $StorageArea, "name" => $Name, "mime" => $MIMEType, "path" => $StoragePath, "record" => $Row));
}

// Given a file info array, fetch file
// -----------------------------------
function aib_fetch_file($DBHandle,$FileInfo,$OutputType = "buf://")
{
	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	if (file_exists($SourceName) == false)
	{
		return(false);
	}

	if ($OutputType == "buf://")
	{
		return(file_get_contents($SourceName));
	}

	if (substr($OutputType,0,7) == "file://")
	{
		$OutName = substr($OutputType,7);
		system("cp -f \"$SourceName\" \"$OutName\" 2> /dev/null > /dev/null");
	}

	return(true);
}




// Remove a file associated with an item
// -------------------------------------
function aib_remove_file_from_item($DBHandle,$ItemID,$FileID)
{
	$Result = mysqli_query($DBHandle,"DELETE FROM ftree_files WHERE file_item_id='$ItemID' AND record_id='$FileID';");
	return(true);
}

// Remove uploaded file entry
// --------------------------
function aib_remove_upload_entry($DBHandle,$RecordID)
{
	$Result = mysqli_query($DBHandle,"DELETE FROM file_uploads WHERE record_id=$RecordID;");
	if ($Result == false)
	{
		return(array("ERROR",mysqli_error($DBHandle)));
	}
	else
	{
		return(array("OK"));
	}
}

// Store batch entry for file
// --------------------------
function aib_store_file_batch_entry($DBHandle,$OpCode,$BatchInfo,$Identifier)
{
	$Query = "INSERT INTO batch_process_queue (record_type,batch_info,identifier) VALUES ('$OpCode','$BatchInfo','$Identifier');";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("ERROR",mysqli_error($DBHandle)));
	}

	$ResultID = mysqli_insert_id($DBHandle);
	return(array("OK",$ResultID));
}

// Remove process queue entry
// --------------------------
function aib_remove_process_queue_entry($DBHandle,$RecordID)
{
	$Result = mysqli_query($DBHandle,"DELETE FROM batch_process_queue WHERE record_id=$RecordID;");
	if ($Result == false)
	{
		return(array("ERROR",mysqli_error($DBHandle)));
	}
	else
	{
		return(array("OK"));
	}
}

// Get process queue entries
// -------------------------
function aib_get_process_queue_entries($DBHandle,$OpCode = false, $Identifier = false)
{
	$Query = "SELECT * FROM batch_process_queue";
	if ($OpCode !== false || $Identifier !== false)
	{
		$Query .= " WHERE";
	}

	if ($OpCode !== false)
	{
		if ($Identifier !== false)
		{
			$Query .= " record_type='$OpCode' AND identifier='$Identifier'";
		}
		else
		{
			$Query .= " record_type='$OpCode'";
		}
	}
	else
	{
		if ($Identifier !== false)
		{
			$Query .= " identifier='$Identifier'";
		}
	}

	$Query .= " ORDER BY record_id;";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array("ERROR",mysqli_error($DBHandle)));
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}

	mysqli_free_result($Result);
	return(array("OK",$OutList));
}

function aib_delete_process_queue_entry($DBHandle,$RecordID)
{
	if ($RecordID === false)
	{
		return;
	}

	if ($RecordID < 0)
	{
		return;
	}

	mysqli_query($DBHandle,"DELETE FROM batch_process_queue WHERE record_id=$RecordID;");
}

// Get the first available thumbnail for a record.  Returns the ID of the thumbnail image file ID
// ----------------------------------------------------------------------------------------------
function aib_get_first_record_thumb($DBHandle,$RecordID)
{
	// Get the list of children, sorted by title

	$ThumbID = -1;

	// Get list, sorted by ID rather than title

	$ChildItemList = ftree_list_child_objects($DBHandle,$RecordID,false,false,false,false,false,true);
	if ($ChildItemList == false)
	{
		return($ThumbID);
	}

	// Check each for a thumbnail

	foreach($ChildItemList as $ChildRecord)
	{
		$ChildID = $ChildRecord["item_id"];
		$FileList = aib_get_files_for_item($DBHandle,$ChildID);
		foreach($FileList as $FileRecord)
		{
			if ($FileRecord["file_class"] == AIB_FILE_CLASS_THUMB)
			{
				$ThumbID = $FileRecord["record_id"];
				break;
			}
		}

		if ($ThumbID > 0)
		{
			break;
		}
	}

	return($ThumbID);
}

// Get top (parent) folder
// -----------------------
function aib_get_site_base_folder()
{
	$ServerName = $_SERVER["SERVER_NAME"];
	$Segs = explode(".",$ServerName);
	$SiteName = strtolower($Segs[0]);
	$DomainName = strtolower($Segs[1]);
	if ($DomainName != "archiveinabox")
	{
		return(array("parent" => false, "is_management" => false));
	}

	if ($SiteName == "manage")
	{
		return(array("parent" => false, "is_management" => true));
	}

	aib_open_db();
	$ArchiveGroups = ftree_get_all_property_values($GLOBALS["aib_db"],AIB_FOLDER_PROPERTY_ARCHIVE_GROUP_CODE);
	$Archives = ftree_get_all_property_values($GLOBALS["aib_db"],AIB_FOLDER_PROPERTY_ARCHIVE_CODE);
	aib_close_db();
	$GroupMap = array();
	$ArchiveMap = array();
	foreach($ArchiveGroups as $PropertyRecord)
	{
		$Key = strtolower(urldecode($PropertyRecord["property_value"]));
		$Value = $PropertyRecord["item_id"];
		if (isset($GroupMap[$Key]) == false)
		{
			$GroupMap[$Key] = $Value;
		}
	}

	foreach($Archives as $PropertyRecord)
	{
		$Key = strtolower(urldecode($PropertyRecord["property_value"]));
		$Value = $PropertyRecord["item_id"];
		if (isset($ArchiveMap[$Key]) == false)
		{
			$ArchiveMap[$Key] = $Value;
		}
	}

	$ParentFolder = false;
	if (isset($GroupMap[$SiteName]) == true)
	{
		return(array("parent" => $GroupMap[$SiteName],"is_management" => false));
	}

	if (isset($ArchiveMap[$SiteName]) == true)
	{
		return(array("parent" => $ArchiveMap[$SiteName],"is_management" => false));
	}

	return(array("parent" => false, "is_management" => false));
}

// Get nav info
// ------------
function aib_get_nav_info($LocalFormData)
{
	// Get navigation info

	if (isset($LocalFormData["aibnav"]) == true)
	{
		$NavInfo = json_decode(urldecode($LocalFormData["aibnav"]),true);
	}
	else
	{
		$NavInfo = array("primary" => "-1");
	}

	$NavInfo["timestamp"] = time();
	$GLOBALS["nav_source_info"] = urlencode(json_encode($NavInfo));
	return;
}

// Update nav info
// ---------------
function aib_update_nav_info($Name,$Value)
{
	$NavInfo = json_decode(urldecode($GLOBALS["nav_source_info"]),true);
	if ($Value == false)
	{
		unset($NavInfo[$Name]);
	}
	else
	{
		$NavInfo[$Name] = $Value;
	}

	$GLOBALS["nav_source_info"] = urlencode(json_encode($NavInfo));
	return;
}

// Get nav parameter
// -----------------
function aib_get_nav_value($Name,$Default = false)
{
	$NavInfo = json_decode(urldecode($GLOBALS["nav_source_info"]),true);
	if (isset($NavInfo[$Name]) == false)
	{
		return($Default);
	}

	return($NavInfo[$Name]);
}

// Get nav string
// --------------
function aib_get_nav_string()
{
	return($GLOBALS["nav_source_info"]);
}

// Given nav contents, determine page to return to
// -----------------------------------------------
function aib_get_nav_target()
{
	$NavInfo = json_decode(urldecode($GLOBALS["nav_source_info"]),true);
	if (isset($NavInfo["src"]) == false)
	{
		return(false);
	}

	$LocalTitle = "Previous Page";
	if (isset($NavInfo["src_title"]) == true)
	{
		$LocalTitle = $NavInfo["src_title"];
	}

	$LocalOpCode = "";
	if (isset($NavInfo["src_opcode"]) == true)
	{
		$LocalOpCode = $NavInfo["src_opcode"];
	}

	return(array("target" => $NavInfo["src"], "title" => $NavInfo["src_title"], "opcode" => $LocalOpCode));
}

// Given a user ID, see if the user is owned by another user and what the default rights are
// -----------------------------------------------------------------------------------------
function aib_get_user_owner_and_rights($DBHandle,$UserID)
{
	$Owner = ftree_get_user_prop($DBHandle,$UserID,AIB_USER_PROPERTY_OWNER);
	$Rights = ftree_get_user_prop($DBHandle,$UserID,AIB_USER_PROPERTY_DEFAULT_RIGHTS);
	return(array("owner" => $Owner, "rights" => $Rights));
}

// Set user owner
// --------------
function aib_set_user_owner($DBHandle,$UserID,$OwnerID)
{
	if ($OwnerID === false)
	{
		ftree_delete_user_prop($DBHandle,$UserID,AIB_USER_PROPERTY_OWNER);
	}
	else
	{
		ftree_set_user_prop($DBHandle,$UserID,AIB_USER_PROPERTY_OWNER,"$OwnerID");
	}
}

// Set user default rights
// -----------------------
function aib_set_user_default_rights($DBHandle,$UserID,$Rights)
{
	if ($Rights === false)
	{
		ftree_delete_user_prop($DBHandle,$UserID,AIB_USER_PROPERTY_DEFAULT_RIGHTS);
	}
	else
	{
		ftree_set_user_prop($DBHandle,$UserID,AIB_USER_PROPERTY_DEFAULT_RIGHTS,$Rights);
	}
}

// Remove a file from an item, then delete file
// --------------------------------------------
function aib_remove_item_file($DBHandle,$FileID)
{
	$FileInfo = aib_get_file_info($DBHandle,$FileID);
	if ($FileInfo == false)
	{
		return(array("ERROR","NOTFOUND"));
	}

	if (preg_match("/[\.]dat$/",$FileInfo["name"]) != false)
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"];
	}
	else
	{
		$SourceName = $FileInfo["path"]."/".$FileInfo["name"].".dat";
	}

	$SourceName = urldecode($SourceName);
	if (file_exists($SourceName) == false)
	{
		return(array("ERROR","CANTREADFILE"));
	}

	$Query = "DELETE FROM ftree_files WHERE file_id='$FileID';";
	mysqli_query($DBHandle,$Query);
	system("rm -f \"$SourceName\" 2> /dev/null");
	return(true);
}

// Get a list of all subadmins.  Output is an associative array where
// the key is the user ID, and the value is an associative array:
//
//	"def"		User record
//	"archives"	List of archives used, where each entry is an archive tree record
// ---------------------------------------------------------------------------------------
function aib_util_get_subadmin_list($UserID)
{
	// Get the profile for this user

	$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);

	// Get the user's root folder

	$UserRoot = $UserRecord["user_top_folder"];

	// Get the folder type

	$SubadminList = array();
	$UserRootType = ftree_get_property($GLOBALS["aib_db"],$UserRoot,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	switch($UserRootType)
	{
		// If group, get the list of archives and then get the list of users for each archive

		case AIB_ITEM_TYPE_ARCHIVE_GROUP:
			$ArchiveList = aib_get_archive_group_archive_list($GLOBALS["aib_db"],$UserRoot);
			if ($ArchiveList == false)
			{
				$ArchiveList = array();
			}

			$UserMap = array();
			foreach($ArchiveList as $ArchiveInfo)
			{
				$ArchiveID = $ArchiveInfo["item_id"];
				$UserList = ftree_list_users_for_parent($GLOBALS["aib_db"],$ArchiveID,FTREE_USER_TYPE_SUBADMIN);
				if ($UserList != false)
				{
					foreach($UserList as $UserRecord)
					{
						if (isset($UserMap[$UserRecord["user_id"]]) == false)
						{
							$UserMap[$UserRecord["user_id"]] = array("def" => $UserRecord, "archives" => array($ArchiveInfo));
						}
						else
						{
							$UserMap[$UserRecord["user_id"]]["archives"][] = $ArchiveInfo;
						}
					}
				}
			}

			return($UserMap);

		case AIB_ITEM_TYPE_ARCHIVE:
			$UserMap = array();
//			$UserList = ftree_list_users_for_parent($GLOBALS["aib_db"],$UserRoot,FTREE_USER_TYPE_SUBADMIN);
			$UserList = ftree_list_users_for_parent($GLOBALS["aib_db"],$ArchiveID,FTREE_USER_TYPE_SUBADMIN);
			if ($UserList != false)
			{
				foreach($UserList as $UserRecord)
				{
					if (isset($UserMap[$UserRecord["user_id"]]) == false)
					{
						$UserMap[$UserRecord["user_id"]] = array("def" => $UserRecord, "archives" => array($ArchiveInfo));
					}
					else
					{
						$UserMap[$UserRecord["user_id"]]["archives"][] = $ArchiveInfo;
					}
				}
			}

			return($UserMap);

		default:
			break;
	}

	return(array());
}


// For a subadmin, get the list of waiting and completed records
// -------------------------------------------------------------
function aib_util_get_subadmin_status($UserID)
{
	$WaitingDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,true);
	$CompletedDataEntry = ftree_get_data_entry_items($GLOBALS["aib_db"],$UserID,false);
	$OutData = array("waiting" => $WaitingDataEntry, "completed" => $CompletedDataEntry);
	return($OutData);
}


// Generate a list of subadmins and assignment statuses for admin
// --------------------------------------------------------------
function aib_util_collect_admin_subadmin_status($UserID)
{
	$OutData = array();
	$SubAdminList = aib_util_get_subadmin_list($UserID);
	foreach($SubAdminList as $SubAdminID => $DefRecord)
	{
		$SubAdminRecord = $DefRecord["def"];
		$SubAdminArchives = $DefRecord["archives"];
		foreach($SubAdminArchives as $ArchiveRecord)
		{
			$SubAdminStatusInfo = aib_util_get_subadmin_status($SubAdminID);
			$WaitingCount = count($SubAdminStatusInfo["waiting"]);
			$CompletedCount = count($SubAdminStatusInfo["completed"]);
			$OutData[$SubAdminID] = array("waiting" => $WaitingCount, "completed" => $CompletedCount);
		}
	}

	return($OutData);
}

// Store customer request
// ----------------------
function aib_store_cust_request($ReqType, $Name, $Phone, $Email, $IPAddr, $Info, $OwnerItem = -1, $OwnerUserID = -1, $ReqStatus = "NEW")
{
	$LocalName = urlencode($Name);
	$LocalPhone = urlencode($Phone);
	$LocalEmail = urlencode($Email);
	$LocalIP = urlencode($IPAddr);
	$LocalInfo = urlencode($Info);
	$LocalTime = time();
	$Query = "INSERT INTO requests (req_type,name,phone,email,ip_addr,req_time,info,owner_item,owner_user,req_status) VALUES ('$ReqType','$LocalName','$LocalPhone',".
		"'$LocalEmail','$LocalIP',$LocalTime,'$LocalInfo','$OwnerItem','$OwnerUserID','$ReqStatus');";
	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	if ($Result == false)
	{
		return("ERROR");
	}
	
	$ResultID = mysqli_insert_id($GLOBALS["aib_db"]);
	return($ResultID);
}

// Update customer request
// ----------------------
function aib_update_cust_request($ReqID,$ModInfo)
{
	if ($ReqID == false || $ReqID == "")
	{
		return(false);
	}

	$FieldList = array("req_type" => false, "req_name" => true, "req_phone" => true, "req_email" => true,"req_ipaddr" => true,"req_info" => true,
		"req_item" => false,"req_user" => false, "req_status" => false);
	$MatchNames = array(
		"req_type" => "req_type",
		"req_name" => "name",
		"req_phone" => "phone",
		"req_email" => "email",
		"req_ipaddr" => "ip_addr",
		"req_info" => "info",
		"req_item" => "owner_item",
		"req_user" => "owner_user",
		"req_status" => "req_status");

	// Construct update query

	$Query = "UPDATE requests SET ";
	$CommaFlag = false;
	foreach($FieldList as $FieldName => $EncodeFlag)
	{
		if (isset($ModInfo[$FieldName]) == true)
		{
			if ($EncodeFlag == true)
			{
				$LocalString = urlencode($ModInfo[$FieldName]);
			}
			else
			{
				$LocalString = $ModInfo[$FieldName];
			}

			if ($CommaFlag == true)
			{
				$Query .= ",";
			}

			$ColName = $MatchNames[$FieldName];
			$Query .= " $ColName='$LocalString'";
			$CommaFlag = true;
		}
	}

	$Query .= " WHERE record_id='$ReqID';";
	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	if ($Result == false)
	{
		return(false);
	}
	
	return("OK");
}

// List customer requests
// ----------------------
function aib_list_cust_request($ReqType,$Name = false, $Phone = false, $Email = false, $IPAddr = false, $OwnerItem = false, $OwnerUserID = false, $StartTime = false, $EndTime = false, $ResultSort = false, $StartResult = false, $ResultCount = false, $ReqStatus = false)
{
	$Query = "SELECT * FROM requests WHERE req_type='$ReqType'";
	$AndFlag = true;
	if ($Name != false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " name='".urlencode($Name)."'";
		$AndFlag = true;
	}

	if ($Phone != false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " phone='".urlencode($Phone)."'";
		$AndFlag = true;
	}

	if ($Email != false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " email='".urlencode($Email)."'";
		$AndFlag = true;
	}

	if ($IPAddr != false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " ip_addr='".urlencode($IPAddr)."'";
		$AndFlag = true;
	}

	if ($OwnerItem !== false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " owner_item='$OwnerItem'";
		$AndFlag = true;
	}

	if ($OwnerUserID !== false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " owner_user='$OwnerUserID'";
		$AndFlag = true;
	}

	if ($ReqStatus !== false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		$Query .= " req_status='$ReqStatus'";
		$AndFlag = true;
	}

	if ($StartTime !== false || $EndTime !== false)
	{
		if ($AndFlag == true)
		{
			$Query .= " AND ";
		}

		if ($StartTime !== false && $EndTime !== false)
		{
			$Query .= " req_time >= '$StartTime' AND req_time <= '$EndTime'";
		}
		else
		{
			if ($StartTime !== false)
			{
				$Query .= " req_time >= '$StartTime'";
			}
			else
			{
				$Query .= " req_time <= '$EndTime'";
			}
		}
	}

	switch($ResultSort)
	{
		case "name":
			$Query .= " ORDER BY name";
			break;

		case "phone":
			$Query .= " ORDER BY phone";
			break;

		case "email":
			$Query .= " ORDER BY email";
			break;

		case "ip_addr":
			$Query .= " ORDER BY ip_addr";
			break;

		case "item":
			$Query .= " ORDER BY owner_item";
			break;

		case "user":
			$Query .= " ORDER BY owner_user";
			break;

		case "time":
			$Query .= " ORDER BY req_time";
			break;

		default:
			$Query .= " ORDER BY record_id";
			break;
	}

	if ($StartResult !== false)
	{
		if ($ResultCount !== false)
		{
			if ($StartResult >= 0)
			{
				$Query .= " LIMIT $StartResult,$ResultCount";
			}
		}
		else
		{
			$Query .= " LIMIT $StartResult";
		}
	}

	$Query .= ";";
	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	if ($Result == false)
	{
		return(array());
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		$OutList[] = $Row;
	}

	mysqli_free_result($Result);
	return($OutList);
}

// Get customer request
// --------------------
function aib_get_cust_request($RecordID)
{
	$Query = "SELECT * FROM requests WHERE record_id='$RecordID'";
	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	if ($Result == false)
	{
		return(false);
	}

	if (mysqli_num_rows($Result) < 1)
	{
		mysqli_free_result($Result);
		return(false);
	}

	$Row = mysqli_fetch_assoc($Result);
	mysqli_free_result($Result);
	return($Row);
}

// Delete customer request
// --------------------
function aib_del_cust_request($RecordID)
{
	if ($RecordID == false || $RecordID == "")
	{
		return(false);
	}

	$Query = "DELETE FROM requests WHERE record_id='$RecordID'";
	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	return(true);
}

// Get the list of folders where an item is referenced
// ---------------------------------------------------
function aib_get_item_references($ItemID,$RestrictReferences = false)
{
	$Query = "SELECT * FROM ftree WHERE item_ref='$ItemID' AND item_source_type='L';";
	$Result = mysqli_query($GLOBALS["aib_db"],$Query);
	if ($Result == false)
	{
		return(array());
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		if ($RestrictReferences == true)
		{
			$LinkClass = ftree_get_property($GLOBALS["aib_db"],$Row["item_id"],"link_class");
			if ($LinkClass != false)
			{
				if ($LinkClass != "recform")
				{
					continue;
				}
			}
			else
			{
				continue;
			}
		}


		$OutList[] = $Row;
	}

	mysqli_free_result($Result);
	return($OutList);
}

// Check to see if a word is a stopword
// ------------------------------------
function is_a_stopword($InWord)
{
	$StopWords = array(
		"a" => true, "about" => true, "above" => true, "after" => true, "again" => true,
		"against" => true, "all" => true, "am" => true, "an" => true, "and" => true,
		"any" => true, "are" => true, "aren't" => true, "as" => true, "at" => true,
		"be" => true, "because" => true, "been" => true, "before" => true, "being" => true,
		"below" => true, "between" => true, "both" => true, "but" => true, "by" => true,
		"can't" => true, "cannot" => true, "could" => true, "couldn't" => true, "did" => true,
		"didn't" => true, "do" => true, "does" => true, "doesn't" => true, "doing" => true,
		"don't" => true, "down" => true, "during" => true, "each" => true, "few" => true,
		"for" => true, "from" => true, "further" => true, "had" => true, "hadn't" => true,
		"has" => true, "hasn't" => true, "have" => true, "haven't" => true, "having" => true,
		"he" => true, "he'd" => true, "he'll" => true, "he's" => true, "her" => true,
		"here" => true, "here's" => true, "hers" => true, "herself" => true, "him" => true,
		"himself" => true, "his" => true, "how" => true, "how's" => true, "i" => true,
		"i'd" => true, "i'll" => true, "i'm" => true, "i've" => true, "if" => true,
		"in" => true, "into" => true, "is" => true, "isn't" => true, "it" => true,
		"it's" => true, "its" => true, "itself" => true, "let's" => true, "me" => true,
		"more" => true, "most" => true, "mustn't" => true, "my" => true, "myself" => true,
		"no" => true, "nor" => true, "not" => true, "of" => true, "off" => true,
		"on" => true, "once" => true, "only" => true, "or" => true, "other" => true,
		"ought" => true, "our" => true, "ours" => true, "ourselves" => true, "out" => true,
		"over" => true, "own" => true, "same" => true, "shan't" => true, "she" => true,
		"she'd" => true, "she'll" => true, "she's" => true, "should" => true, "shouldn't" => true,
		"so" => true, "some" => true, "such" => true, "than" => true, "that" => true,
		"that's" => true, "the" => true, "their" => true, "theirs" => true, "them" => true,
		"themselves" => true, "then" => true, "there" => true, "there's" => true, "these" => true,
		"they" => true, "they'd" => true, "they'll" => true, "they're" => true,
		"they've" => true, "this" => true, "those" => true, "through" => true, "to" => true,
		"too" => true, "under" => true, "until" => true, "up" => true, "very" => true,
		"was" => true, "wasn't" => true, "we" => true, "we'd" => true, "we'll" => true,
		"we're" => true, "we've" => true, "were" => true, "weren't" => true, "what" => true,
		"what's" => true, "when" => true, "when's" => true, "where" => true, "where's" => true,
		"which" => true, "while" => true, "who" => true, "who's" => true, "whom" => true,
		"why" => true, "why's" => true, "with" => true, "won't" => true, "would" => true,
		"wouldn't" => true, "you" => true, "you'd" => true, "you'll" => true, "you're" => true,
		"you've" => true, "your" => true, "yours" => true, "yourself" => true, "yourselves" => true,
	);

	if (isset($StopWords[strtolower($InWord)]) == true)
	{
		return(true);
	}

	return(false);
}

// Add tags for item.  Parses text.
// --------------------------------
function aib_add_item_tags($DBHandle,$ItemID,$InText,$Delimiter = ",")
{
	$TagMap = array();
	$Tags = explode($Delimiter,$InText);
	$Query = "INSERT INTO ftree_tags (item_id,tag_value) VALUES ";
	$SetList = array();
	foreach($Tags as $TempTag)
	{
		$LocalTempTag = ltrim(rtrim($TempTag));
		if (is_a_stopword($LocalTempTag) == true)
		{
			continue;
		}

		if ($LocalTempTag == "")
		{
			continue;
		}

		$TagValue = urlencode(strtoupper($LocalTempTag));
		if (isset($TagMap[$TagValue]) == true)
		{
			continue;
		}

		$SetList[] = "('$ItemID','$TagValue')";
		$TagMap[$TagValue] = true;
	}

	$Query .= join(",",$SetList);
	$Query .= ";";
	mysqli_query($DBHandle,$Query);
	return(true);
}

// Add notifier
// ------------
function aib_add_notifier($DBHandle,$UserID,$Keyword,$ItemParentID)
{
	// Clean up keyword

	$LocalKeyword = urlencode(ltrim(rtrim(strtolower($Keyword))));
	if ($LocalKeyword == "")
	{
		return("NOKEY");
	}

	$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM notifiers WHERE user_id='$UserID' AND item_parent_id='$ItemParentID' AND keyw0rd='$LocalKeyword';");
	if ($ResultList !== false)
	{
		return("DUP");
	}

	$LocalType = ftree_get_property($GLOBALS["aib_db"],$ItemParentID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	if ($LocalType == false)
	{
		$LocalType = "";
	}

	$Query = "INSERT INTO notifiers (user_id,item_parent_id,keyword,type) VALUES ('$UserID','$ItemParentID','$LocalKeyword','$LocalType');";
	mysqli_query($DBHandle,$Query);
	return("OK");
}

// Remove notifier
// ---------------
function aib_delete_notifier($DBHandle,$UserID,$Keyword,$ItemParentID)
{
	$Query = "DELETE FROM notifiers WHERE";
	$AndFlag = false;
	while(true)
	{
		if ($UserID !== false)
		{
			$Query .= " user_id=$UserID";
			$AndFlag = true;
		}

		if ($ItemParentID !== false)
		{
			if ($AndFlag == true)
			{
				$Query .= " AND ";
			}

			$Query .= " item_parent_id='$ItemParentID'";
			$AndFlag = true;
		}

		if ($Keyword !== false)
		{
			if ($AndFlag == true)
			{
				$Query .= " AND ";
			}

			$LocalKeyword = urlencode(ltrim(rtrim(strtolower($Keyword))));
			if ($LocalKeyword == "")
			{
				return(false);
			}

			$Query .= " keyword='$LocalKeyword'";
			$AndFlag = true;
		}

		break;
	}

	$Query .= ";";
	mysqli_query($DBHandle,$Query);
	return(true);
}


// Get list of notifier definitions
// --------------------------------
function aib_list_notifiers($DBHandle,$UserID = false,$Keyword = false,$ItemParentID = false)
{
	$Query = "SELECT * FROM notifiers";
	if ($UserID !== false || $Keyword !== false || $ItemParentID !== false)
	{
		$Query .= " WHERE ";
		$AndFlag = false;
		while(true)
		{
			if ($UserID !== false)
			{
				$Query .= " user_id=$UserID";
				$AndFlag = true;
			}

			if ($ItemParentID !== false)
			{
				if ($AndFlag == true)
				{
					$Query .= " AND ";
				}

				$Query .= " item_parent_id='$ItemParentID'";
				$AndFlag = true;
			}

			if ($Keyword !== false)
			{
				if ($AndFlag == true)
				{
					$Query .= " AND ";
				}

				$LocalKeyword = urlencode(ltrim(rtrim(strtolower($Keyword))));
				if ($LocalKeyword == "")
				{
					return(array());
				}

				$Query .= " keyword='$LocalKeyword'";
				$AndFlag = true;
			}

			break;
		}
	}

	$Query .= " ORDER BY user_id,item_parent_id;";
	$Result = mysqli_query($DBHandle,$Query);
	if ($Result == false)
	{
		return(array());
	}

	$OutList = array();
	while(true)
	{
		$Row = mysqli_fetch_assoc($Result);
		if ($Row == false)
		{
			break;
		}

		if ($Row["item_parent_id"] >= 0)
		{
			$ItemRecord = ftree_get_item($DBHandle,$Row['item_parent_id']);
			if ($ItemRecord != false)
			{
				$Row["item_title"] = $ItemRecord["item_title"];
				$ItemParentType = ftree_get_property($GLOBALS["aib_db"],$Row["item_parent_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				$Row["item_entry_type"] = $ItemParentType;
				$Row["item_type"] = $ItemRecord["item_type"];
				$Row["item_source_type"] = $ItemRecord["item_source_type"];
				$Row["item_ref"] = $ItemRecord["item_ref"];
			}
			else
			{
				$Row["item_title"] = "";
				$Row["item_entry_type"] = "";
				$Row["item_type"] = "";
				$Row["item_source_type"] = "";
				$Row["item_ref"] = "";
			}
		}
		else
		{
			$Row["item_title"] = "ALL";
			$Row["item_entry_type"] = "";
			$Row["item_type"] = "";
			$Row["item_source_type"] = "";
			$Row["item_ref"] = "";
		}

		$OutList[] = $Row;
	}

	mysqli_free_result($Result);
	return($OutList);
}

// Update notifier queue based on notifier definitions
// ---------------------------------------------------
function aib_update_notifier_queue($DBHandle,$ItemID,$KeywordString,$Delimiter = ",")
{
	// Get parent of item

	$ItemRecord = ftree_get_item($DBHandle,$ItemID);
	if ($ItemRecord == false)
	{
		return(false);
	}

	// Get path to item

	$IDPathList = ftree_get_item_id_path($DBHandle,$ItemID);
	if ($IDPathList == false)
	{
		$IDPathList = array();
	}

	$IDPathList[] = -1;

	// Get unique keywords

	$KeywordList = explode($Delimiter,strtolower($KeywordString));
	$KeywordMap = array();
	foreach($KeywordList as $Keyword)
	{
		$EncodedKeyword = urlencode(rtrim(ltrim($Keyword)));
		if ($EncodedKeyword == "")
		{
			continue;
		}

		$KeywordMap[$EncodedKeyword] = true;
	}

	// Process each keyword

	$AddCount = 0;
	$LocalTimeStamp = time();
	$EncodedList = array_keys($KeywordMap);
	$LocalMatchType = AIB_NOTIFIER_MATCH_TYPE_RECORD;
	foreach($EncodedList as $EncodedKeyword)
	{
		// Add notifiers for each keyword only once based on the first match.

		foreach($IDPathList as $MatchID)
		{
			// Get list of notifiers for the ID and keyword

			$ResultList = ftree_query_ext($DBHandle,"SELECT * FROM notifiers WHERE item_parent_id='$MatchID' AND keyword='$EncodedKeyword';");
			if ($ResultList != false)
			{

				// Add a queue entry for each match

				foreach($ResultList as $ResultRecord)
				{
					$UserID = $ResultRecord["user_id"];
					$Query = "INSERT INTO notifier_queue (user_id,add_timestamp,item_id,keyword,match_id,match_type) VALUES ('$UserID','$LocalTimeStamp','$ItemID','$EncodedKeyword','$MatchID','$LocalMatchType');";
					mysqli_query($DBHandle,$Query);
					$AddCount++;
				}
			}
		}
	}

	return($AddCount);
}

// Get notifier queue entries.  Return array is grouped by user ID.
// ----------------------------------------------------------------
function aib_get_notifier_queue_entries($DBHandle,$UserID = false)
{
	if ($UserID === false)
	{
		$ResultList = ftree_query($DBHandle,"SELECT * FROM notifier_queue ORDER BY user_id,add_timestamp;");
	}
	else
	{
		$ResultList = ftree_query($DBHandle,"SELECT * FROM notifier_queue WHERE user_id='$UserID' ORDER BY user_id,add_timestamp;");
	}

	if ($ResultList == false)
	{
		return(array());
	}

	$OutSet = array();
	foreach($ResultList as $ResultRecord)
	{
		$LocalUser = $ResultRecord["user_id"];
		if (isset($OutSet[$LocalUser]) == false)
		{
			$OutSet[$LocalUser] = array();
		}

		$TempRecord = $ResultRecord;
		$TempRecord["keyword"] = urldecode($TempRecord["keyword"]);
		$TempRecord["timestamp_string"] = date("Y-m-d H:i:s",$TempRecord["add_timestamp"]);
		if ($TempRecord["item_id"] >= 0)
		{
			$ItemRecord = ftree_get_item($DBHandle,$TempRecord['item_id']);
			if ($ItemRecord != false)
			{
				$TempRecord["item_title"] = $ItemRecord["item_title"];
				$ItemParentType = ftree_get_property($GLOBALS["aib_db"],$TempRecord["item_id"],AIB_FOLDER_PROPERTY_FOLDER_TYPE);
				$TempRecord["item_entry_type"] = $ItemParentType;
				$TempRecord["item_type"] = $ItemRecord["item_type"];
				$TempRecord["item_source_type"] = $ItemRecord["item_source_type"];
				$TempRecord["item_ref"] = $ItemRecord["item_ref"];
			}
			else
			{
				$TempRecord["item_title"] = "N/A";
				$TempRecord["item_entry_type"] = "";
				$TempRecord["item_type"] = "";
				$TempRecord["item_source_type"] = "";
				$TempRecord["item_ref"] = "";
			}
		}
		else
		{
			$TempRecord["item_title"] = "N/A";
			$TempRecord["item_entry_type"] = "";
			$TempRecord["item_type"] = "";
			$TempRecord["item_source_type"] = "";
			$TempRecord["item_ref"] = "";
		}

		$OutSet[$LocalUser][] = $TempRecord;
	}

	return($OutSet);
}


// Clear notifier queue
// --------------------
function aib_clear_notifier_queue_entries($DBHandle,$UserID = false,$BeforeTime = false)
{
	$Query = "DELETE FROM notifier_queue";
	if ($UserID !== false || $SinceTime !== false)
	{
		$Query .= " WHERE ";
		$AndFlag = false;
		while(true)
		{
			if ($UserID !== false)
			{
				if ($AndFlag == true)
				{
					$Query .= " AND ";
				}

				$Query .= " user_id='$UserID'";
				$AndFlag = true;
			}

			if ($SinceTime !== false)
			{
				if ($AndFlag == true)
				{
					$Query .= " AND ";
				}

				$Query .= " add_timestamp < '$BeforeTime'";
				$AndFlag = true;
			}

			break;
		}
	}

	$Query .= ";";
	mysqli_query($DBHandle,$Query);
	return(true);
}

// Add entry to notifier queue
// ---------------------------
function aib_add_notifier_queue_entry($DBHandle,$UserID,$ItemID,$MatchID,$KeywordString,$Delimiter = ",",$MatchType = AIB_NOTIFIER_MATCH_TYPE_GENERIC)
{
	// Get unique keywords

	$KeywordList = explode($Delimiter,strtolower($KeywordString));
	$KeywordMap = array();
	foreach($KeywordList as $Keyword)
	{
		$EncodedKeyword = urlencode(rtrim(ltrim($Keyword)));
		if ($EncodedKeyword == "")
		{
			continue;
		}

		$KeywordMap[$EncodedKeyword] = true;
	}

	// Process each keyword

	$AddCount = 0;
	$LocalTimeStamp = time();
	$EncodedList = array_keys($KeywordMap);
	foreach($EncodedList as $EncodedKeyword)
	{
		$Query = "INSERT INTO notifier_queue (user_id,add_timestamp,item_id,keyword,match_id,match_type) VALUES ('$UserID','$LocalTimeStamp','$ItemID','$EncodedKeyword','$MatchID','$MatchType');";
		mysqli_query($DBHandle,$Query);
		$AddCount++;
	}

	return($AddCount);
}


// Remove item tags.  If a specific set of tags is not given, delete all tags
// -----------------
function aib_del_item_tags($DBHandle,$ItemID,$TagList = false)
{
	if ($TagList == false)
	{
		mysqli_query($DBHandle,"DELETE FROM ftree_tags WHERE item_id='$ItemID';");
		return(true);
	}

	foreach($TagList as $TagValue)
	{
		$TempTag = urlencode(strtoupper($TagValue));
		mysqli_query($DBHandle,"DELETE FROM ftree_tags WHERE item_id='$ItemID' AND tag_value='$TempTag';");
	}

	return(true);
}

// Retrieve the tags stored for an item as a list
// ----------------------------------------------
function aib_get_item_tags($DBHandle,$ItemID)
{
	$TempList = ftree_query_ext($DBHandle,"SELECT * FROM ftree_tags WHERE item_id=$ItemID;");
	if ($TempList == false)
	{
		return(array());
	}

	$OutData = array();
	foreach($TempList as $TempRecord)
	{
		$OutData[] = urldecode($TempRecord["tag_value"]);
	}

	return($OutData);
}

// Find items matching a tag
// -------------------------
function aib_find_tag_items($DBHandle,$Tag,$Method = "EXACT", $Start = false,$Count = false)
{
	switch($Method)
	{
		case "WILD":
			$Query = "SELECT * FROM ftree_tags WHERE tag_value LIKE '%".urlencode(strtoupper($Tag))."%'";
			break;

		case "SUFFIX":
			$Query = "SELECT * FROM ftree_tags WHERE tag_value LIKE '%".urlencode(strtoupper($Tag))."'";
			break;

		case "PREFIX":
			$Query = "SELECT * FROM ftree_tags WHERE tag_value LIKE '".urlencode(strtoupper($Tag))."%'";
			break;

		case "EXACT":
		default:
			$Query = "SELECT * FROM ftree_tags WHERE tag_value='".urlencode(strtoupper($Tag))."'";
			break;
	}

	$Query .= " ORDER BY item_id";
	if ($Start !== false)
	{
		$Query .= " LIMIT $Start";
		if ($Count !== false)
		{
			$Query .= ",$Count";
		}
	}

	$Query .= ";";
	$Result = ftree_query_ext($DBHandle,$Query);
	return($Result);
}

// Find items matching tag set with booleans
// 
// Each tag spec is:
//
//	boolean => "AND|OR|"
//	method => "EXACT,WILD,SUFFIX,PREFIX",
//	value => tag text
// -----------------------------------------
function aib_find_tag_items_boolean($DBHandle,$TagSpec,$Method = "EXACT",$Start = false, $Count = false)
{
	$FoundHash = array();
	$Clause = 0;
	foreach($TagSpec as $TagEntry)
	{
		if (isset($TagEntry["method"]) == true)
		{
			$Method = $TagEntry["method"];
		}
		else
		{
			$Method = "EXACT";
		}

		if (isset($TagEntry["boolean"]) == true)
		{
			$Boolean = strtoupper($TagEntry["boolean"]);
		}
		else
		{
			$Boolean = "AND";
		}

		// If first clause, always "OR"

		if ($Clause == 0)
		{
			$Boolean = "OR";
		}

		if (isset($TagEntry["value"]) == false)
		{
			continue;
		}

		$Tag = $TagEntry["value"];
		switch($Method)
		{
			case "WILD":
				$Query = "SELECT item_id FROM ftree_tags WHERE tag_value LIKE '%".urlencode(strtoupper($Tag))."%'";
				break;

			case "SUFFIX":
				$Query = "SELECT item_id FROM ftree_tags WHERE tag_value LIKE '%".urlencode(strtoupper($Tag))."'";
				break;

			case "PREFIX":
				$Query = "SELECT item_id FROM ftree_tags WHERE tag_value LIKE '".urlencode(strtoupper($Tag))."%'";
				break;

			case "EXACT":
			default:
				$Query = "SELECT item_id FROM ftree_tags WHERE tag_value='".urlencode(strtoupper($Tag))."'";
				break;
		}

		$Query .= " ORDER BY item_id";

		// A pseudo-limiting system which may return fewer than the expected number of results.  Works by limiting the
		// initial hit list size to avoid later overflows.

		if ($Clause == 0)
		{
			if ($Start !== false)
			{
				$Query .= " LIMIT $Start";
				if ($Count !== false)
				{
					$Query .= ",$Count";
				}
			}

		}

		$Query .= ";";

		// Get clause results

		$Result = ftree_query_ext($DBHandle,$Query);

		// Merge into overall results based on boolean

		if ($Result != false)
		{
			switch($Boolean)
			{
				// Add found item to results or increment hit count if already present

				case "OR":
					foreach($Result as $Record)
					{
						$ItemID = $Record["item_id"];
						if (isset($FoundHash[$ItemID]) == false)
						{
							$FoundHash[$ItemID] = 1;
						}
						else
						{
							$FoundHash[$ItemID]++;
						}
					}

					break;

				// Remove found item from results if present

				case "NOT":
					foreach($Result as $Record)
					{
						$ItemID = $Record["item_id"];
						if (isset($FoundHash[$ItemID]) == true)
						{
							unset($FoundHash[$ItemID]);
						}
					}

					break;

				// Create new result list.  Add entries found in both existing result set
				// and clause result set to new result set.  New result set becomes the overall result set.

				case "AND":
				default:
					$TempHash = array();
					foreach($Result as $Record)
					{
						$ItemID = $Record["item_id"];
						if (isset($FoundHash[$ItemID]) == false)
						{
							continue;
						}
						else
						{
							$TempHash[$ItemID] = $FoundHash[$ItemID] + 1;
						}
					}

					unset($FoundHash);
					$FoundHash = $TempHash;
					unset($TempHash);
					break;
			}
		}

		$Clause++;
	}

	return($FoundHash);
}

// Given an item, find ultimate target of links
// --------------------------------------------
function aib_deref_link($DBHandle,$ItemID)
{
	$CurrentRecord = false;
	while(true)
	{
		// Get item record

		$CurrentRecord = ftree_get_item($DBHandle,$ItemID);

		// If normal item record (not a link), done.

		if ($CurrentRecord["item_type"] == FTREE_OBJECT_TYPE_FOLDER || $CurrentRecord["item_type"] == FTREE_OBJECT_TYPE_FILE)
		{
			return($CurrentRecord);
		}

		// If link, check target

		if ($CurrentRecord["item_type"] == FTREE_OBJECT_TYPE_LINK)
		{
			// If target is a tree item, go to that tree item and look again

			if ($CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_INTERNAL || $CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_FILE)
			{
				$ItemID = $CurrentRecord["item_ref"];
				if ($ItemID < 0)
				{
					return(false);
				}

				continue;
			}

			// If URL or STP Archive, return

			if ($CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_URL || $CurrentRecord["item_source_type"] == FTREE_SOURCE_TYPE_STPARCHIVE)
			{
				return($CurrentRecord);
			}
		}

		// Link to nowhere

		break;

	}

	return(false);
}


// Given an item that is a link, generate target values
// ----------------------------------------------------
function aib_util_generate_link_target_info($DBHandle,$Prefix,$Suffix,$TempResultRecord,$OptShowLinkHome = false)
{
	$OutData = array();
	switch($TempResultRecord["item_source_type"])
	{
		// Internal (AIB) link.  Fetch the linked item record.

		case FTREE_SOURCE_TYPE_LINK:
		case FTREE_SOURCE_TYPE_INTERNAL:
			$IsLink = true;
			$LinkTarget = $TempResultRecord["item_ref"];
			$ResultRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$LinkTarget);
			if ($OptShowLinkHome == true)
			{
				$TempHome = ftree_get_item_user_home($GLOBALS["aib_db"],$TempResultRecord["item_id"]);
				if ($TempHome != false)
				{
					$LinkUserHome = $TempHome;
				}
			}

			break;

		// STP Archive link

		case FTREE_SOURCE_TYPE_STPARCHIVE:
			$IsLink = true;
			$LinkTarget = $TempResultRecord["item_ref"];
			$LinkInfo = json_decode(urldecode($TempResultRecord["item_source_info"]),true);
			switch($LinkInfo["type"])
			{
				// Edition
				case FTREE_STP_LINK_EDITION:
					$OutData[$Prefix."stp_link_type".$Suffix] = $LinkInfo["type"];
					$OutData[$Prefix."stp_url".$Suffix] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
						$LinkInfo["year"]."/".stp_archive_month_name($LinkInfo["mon"])." ".
						$LinkInfo["day"]."/";
					$OutData[$Prefix."stp_thumb".$Suffix] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
						$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
					break;

				// Page
				case FTREE_STP_LINK_PAGE:
					$OutData[$Prefix."stp_link_type".$Suffix] = $LinkInfo["type"];
					$OutData[$Prefix."stp_url".$Suffix] = "www.".STP_ARCHIVE_DOMAIN."/aib_page.php?edition=".
						$LinkInfo["ed"]."&page=".$LinkInfo["pg"];
					$OutData[$Prefix."stp_thumb".$Suffix] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
						$LinkInfo["ed"]."&page=".$LinkInfo["pg"]."&paper=".$LinkInfo["paper"];
					break;

				// Year
				case FTREE_STP_LINK_YEAR:
					$OutData[$Prefix."stp_link_type".$Suffix] = $LinkInfo["type"];
					$OutData[$Prefix."stp_url".$Suffix] = $LinkInfo["paper"].".".STP_ARCHIVE_DOMAIN."/".
						$LinkInfo["year"];
					$OutData[$Prefix."stp_thumb".$Suffix] = "www.".STP_ARCHIVE_DOMAIN."/aib_thumb.php?edition=".
						$LinkInfo["ed"]."&paper=".$LinkInfo["paper"];
					break;

				default:
					break;
			}

			break;

		case FTREE_SOURCE_TYPE_URL:
			$IsLink = true;
			$LinkInfo = json_decode(urldecode($TempResultRecord["item_source_info"]),true);
			$OutData[$Prefix."link_url".$Suffix] = $LinkInfo["url"];
			break;

		default:
			break;

	}

	return($OutData);
}

	

?>
