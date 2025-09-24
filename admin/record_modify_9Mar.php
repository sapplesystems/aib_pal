<?php
//
// login.php
//

// FUNCTIONAL INCLUDES

include('../../config/aib.php');
include("../../include/folder_tree.php");
include("../../include/fields.php");
include('../../include/aib_util.php');
define('../../APIKEY',"87fc0d6d9689d84ab48f583175f9522d");
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
// Function to call server
// -----------------------
function aib_request($LocalPostData,$FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "http://develop.archiveinabox.com/api/".$FunctionSet.".php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
	);

	curl_setopt_array($CurlObj,$Options);
	$Result = curl_exec($CurlObj);
	if ($Result == false)
	{
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
	}
	else
	{
		$OutData = json_decode($Result,true);
	}

	curl_close($CurlObj);
	return($OutData);
}

// Get new session key
// -------------------
function aib_get_session_key()
{
	// Generate key

	$PostData = array(
		"_id" => "test",
		"_key" => APIKEY,
		"_user" => 1,
	);

	// Make AIB request

	$Result = aib_request($PostData,"session");

	// Check for request errors

	if ($Result["status"] != "OK")
	{
		return(false);
	}

	$SessionKey = $Result["info"];
	return($SessionKey);
}

// Debug
// -----
function record_form_debug($Msg)
{
	$Handle = fopen("/tmp/record_modify_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,date("Y-m-d H:i:s")." -- ".$Msg."\n");
		fclose($Handle);
	}
}
include_once 'config/config.php';
    include_once COMMON_TEMPLATE_PATH.'header.php';
    include_once COMMON_TEMPLATE_PATH.'sidebar.php';
?>
<div class="content-wrapper">
        <section class="content-header">
            <h1>My Archive</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Edit Record</li>
            </ol>
           <h4 class="list_title">Edit Record </h4>
        </section>
        <section class="content bgTexture">
        
<div class="content2">

<?php
// #########
// MAIN CODE
// #########

	// Check session

	/*session_start();
	$CheckResult = aib_check_session();
	if ($CheckResult[0] != "OK")
	{
		$ErrorText = bin2hex($CheckResult[1]);
		header("Location: /login_error.php?v=$ErrorText");
		exit(0);
	}

	 Get user info from session data

	$SessionInfo = $CheckResult[1];
	$UserInfo = aib_get_user_info($SessionInfo["login"]);
	if ($UserInfo[0] != "OK")
	{
		$ErrorText = bin2hex("Cannot retrieve user profile");
		header("Location: /login_error.php?v=$ErrorText");
		exit(0);
	}

	Get the user type

	$UserRecord = $UserInfo[1]; */
	$UserType = $_SESSION['aib']['user_data']['user_type'];//$UserRecord["user_type"];
	$UserID = $_SESSION['aib']['user_data']['user_id'];//$UserRecord["user_id"];
	$UserGroup = $_SESSION['aib']['user_data']['user_top_folder'];//$UserRecord["user_primary_group"];

	// Get form data and opcode

	$FormData = aib_get_form_data();
	$OpCode = aib_get_with_default($FormData,"opcode",false);
	$SourcePage = aib_get_with_default($FormData,"src",false);
	$SourceKey = aib_get_with_default($FormData,"srckey","");
	$SourceSearchValue = aib_get_with_default($FormData,"searchval","");
	$SourceMode = aib_get_with_default($FormData,"srcmode","");
	$SourcePageNumber = aib_get_with_default($FormData,"srcpn",1);
	$ParentFolderID = aib_get_with_default($FormData,"parent",$UserRecord["user_top_folder"]);

	// Get current archive based on user.  If this is the super-user, then no current archive

	$CurrentArchive = false;
	switch($UserType)
	{
		// Root user (superadmin) has no archive

		case FTREE_USER_TYPE_ROOT:
			$CurrentArchive = false;
			break;

		// Administrator has archive based on root folder.

		case FTREE_USER_TYPE_ADMIN:
			$CurrentArchive = $UserRecord["user_top_folder"];
			break;

		// Standard users can't add a collection, nor can sub-admins.

		case FTREE_USER_TYPE_STANDARD:
		case FTREE_USER_TYPE_SUBADMIN:
		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: /login_error.php?v=$ErrorText");
			exit(0);
	}

	// If there is no archive, get a list of the archives available.

	if ($CurrentArchive == false)
	{
		if (aib_open_db() != false)
		{
			$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVE GROUP");
			$ArchiveList = ftree_list_child_objects($GLOBALS["aib_db"],$ArchivesFolderID,false,false,FTREE_OBJECT_TYPE_FOLDER);
			if ($ArchiveList != false)
			{
				$TotalListPages = (count($ArchiveList) / intval(AIB_DEFAULT_ITEMS_PER_PAGE)) + 1;
			}
			else
			{
				$TotalListPages = 0;
			}

			aib_close_db();
		}
		else
		{
			$ArchiveList = array();
		}
	}

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: Edit RECORD",
		"popup_list" => array(
			"itemrecord_title_help_popup" => array("title" => "Help For: Record Name",
							"heading" => "Help For: Record Name",
							"text" => "Enter the name of the record",
						),
			"itemrecord_visible_help_popup" => array("title" => "Help For: Record Visible",
							"heading" => "Help For: Record Visible",
							"text" => "Select \\'Yes\\' if the sub group is to be visible to the public.",
						),
		),
	);

	$PageTimeout = AIB_SESSION_TIMEOUT;
	$PageTimeout = $PageTimeout * 1000;

	// Add code to header area to prevent the use of the back button, and to auto-navigate
	// to login page on session timeout.

	$DisplayData["head_script"] = "
		setTimeout(function() {
			window.location.href='/login.php';
		},$PageTimeout);

		// Prevent the use of the 'back' button to prevent mangling
		// forms.

		history.pushState(null, null, document.URL);
		//window.addEventListener('popstate', function () {
		   // alert(\"Please use the links on the page instead of the 'Back' button.\");
		    //history.pushState(null, null, document.URL);
		//});

		";
?>
<link rel="stylesheet" href="../../css/aib.css">
<script type='text/javascript' src='../../jquery-3.2.0.min.js'> </script>
<script type='text/javascript' src='../../js/aib.js'> </script>
<script src="../../js/vendor/jquery.ui.widget.js"></script>
<script src="../../js/jquery.iframe-transport.js"></script>
<script src="../../js/jquery.fileupload.js"></script>
<script>

	var FileUploadCounter = 0;
	var FileUploadCurrent = -1;
	var UploadDataSet = {};

	// Enable or disable upload button depending on whether there
	// are items to be uploaded.

	function trigger_upload()
	{
		var Key;
		var LocalData;
		var QueuedCount;
		var CheckBoxName;
		var Index;
		var CheckBoxName;

		$('#aib_start_upload_button').prop('disabled',true);
		QueuedCount = 0;
		for (Key in UploadDataSet)
		{
			LocalData = UploadDataSet[Key];
			if (LocalData.Uploaded == 0)
			{
				Index = LocalData.UploadID;
				CheckBoxName = 'aib_file_upload_check_' + Index.toString();
				if ($('#' + CheckBoxName).prop('checked') == true)
				{
					QueuedCount++;
					LocalData.Uploaded = 1;
					LocalData.submit();
				}
			}
		}

		if (QueuedCount < 1)
		{
			$('#aib_start_upload_button').prop('disabled',false);
		}
	}

// Set up uploader.

$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
	disablePreview: true,
	sequentialUploads: true,

	// Add file to upload queue callback

	add: function(LocalEvent,LocalData) {
		$.each(LocalData.files, function(Index, FileData) {
			var ElementName;
			var HTML;
			var ProgressBarName;
			var StatusCellName;
			var ProgressRowName;
			var CheckBoxName;
			
			// Increment file number so we can generate ID names
			
			FileUploadCounter++;

			// Create new table row for progress display

			ProgressRowName = 'aib_file_upload_progress_row_' + FileUploadCounter.toString();
			ProgressBarName = 'aib_file_upload_progress_bar_' + FileUploadCounter.toString();
			StatusCellName = 'aib_file_upload_progress_status_cell_' + FileUploadCounter.toString();
			CheckBoxName = 'aib_file_upload_check_' + FileUploadCounter.toString();
			HTML = "<tr class='aib-file-upload-progress-row' id='" + ProgressRowName + "'><td class='aib-file-upload-progress-name-cell'>";
			HTML = HTML + FileData.name + '</td>';
			HTML = HTML + "<td class='aib-file-upload-progress-status-cell' id='" + StatusCellName + "' style='width:15%'><input type='checkbox' name='" + CheckBoxName + "' id='" + CheckBoxName + "' checked> Queued</td>";
			HTML = HTML + "<td class='aib-progress-bar-cell'><div class='progress-bar-container'><div class='upload-progress-bar' id='" + ProgressBarName + "'></div></div></td></tr>";
			HTML = HTML + "<tr><td class='aib-file-upload-progress-row-sep' colspan='99'></td></tr>";
			$('#upload_file_progress_list > tbody').append(HTML);

			// Save the current counter as part of the file data

			FileData.UploadID = FileUploadCounter;

			// Disable the submit form button to prevent someone from submitting the form while the uploads are active

			$('.aib-submit-button').prop('disabled',true);
			$('#aib_start_upload_button').prop('disabled',false);	

			// Add data to upload set so we can trigger uploads later on

			LocalData.Uploaded = 0;
			LocalData.UploadID = FileUploadCounter;
			UploadDataSet[FileUploadCounter] = LocalData;
		});

//		LocalData.submit();
	},

	// Update progress bar display for individual file

	progress: function(LocalEvent,LocalData) {
		var Progress = parseInt(LocalData.loaded / LocalData.total * 100, 10);
		var Index;
		var ProgressBarName;
		var StatusCellName;

		// Get the index of the file

		Index = LocalData.files[0].UploadID;
		ProgressBarName = '#aib_file_upload_progress_bar_' + Index.toString();
		StatusCellName = '#aib_file_upload_progress_status_cell_' + Index.toString();

		$(ProgressBarName).css('width',Progress + '%');
		$(ProgressBarName).text(Progress.toString() + '%');
		$(StatusCellName).text('Uploading');

		if (Progress >= 100)
		{
			$(StatusCellName).text('Complete');
		}
	},

	// Upload done

	done: function(LocalEvent,LocalData) {
            $.each(LocalData.result.files, function (Index, FileInfo) {
			var StatusCellName;
			var LocalIndex;

			LocalIndex = LocalData.files[0].UploadID;
			StatusCellName = '#aib_file_upload_progress_status_cell_' + LocalIndex.toString();
			$(StatusCellName).text('Complete');
		});
	},

// NOT USED ================================================================
//	done: function (e, data) {
//            $.each(data.result.files, function (index, file) {
//	    	var LocalText = $('#upload_file_progress_list').html();
//
//		LocalText = LocalText + 'Uploading ' + file.name + '<br>';
//                $('#upload_file_progress_list').html(LocalText);
//            });
//        },
// =========================================================================

	// Global (overall upload) progress bar update

	progressall: function (e, data) {
        	var progress = parseInt(data.loaded / data.total * 100, 10);
        	$('#upload_progress_bar').css('width',progress + '%');
        	$('#upload_progress_bar').text(progress.toString() + '% Complete');

		if (progress >= 100)
		{
			$('.aib-submit-button').prop('disabled',false);
			$('#aib_start_upload_button').prop('disabled',false);
		}
    	}
    });
});
</script>
<?php
	$CurrentObjectID = aib_get_with_default($FormData,"primary","");
	$ItemID = $CurrentObjectID;
	aib_open_db();
	$ObjectRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$CurrentObjectID);
	$ParentFolderID = $ObjectRecord["item_parent"];
	$ObjectClass = ftree_get_property($GLOBALS["aib_db"],$CurrentObjectID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	aib_close_db();
	if ($ObjectClass === false)
	{
		$ObjectClass = AIB_ITEM_TYPE_ITEM;
	}


		// Additional code if this is an item and we're replacing the image.  Don't allow more than one file to be uploaded.
	// If in "add" mode, include the checkbox tree in the left column if we're not adding items to an existing record.  The
	// "left_col" data is substituted into the template on display.

	$TreeNavInfo = array();
	if ($OpCode == "edit" && $ObjectClass == AIB_ITEM_TYPE_RECORD)
	{
		aib_open_db();
		$TreeNavInfo = array("idlist" => array());
		$TreeNavInfo = aib_generate_tree_nav_div($GLOBALS["aib_db"],$UserID,$ParentFolderID,"fetch_tree_children","aib-nav-tree-ul","aib-nav-tree-li","aib-nav-tree-li","aib-nav-tree-li");
		$DisplayData["left_col"] = $TreeNavInfo["html"];
		$DisplayData["left_col"] .= "<br><br><div class='aib-selected-tree-items' id='aib-selected-tree-items'> </div>";
		aib_close_db();
	}

	// Set page title

	if ($UserType != FTREE_USER_TYPE_ROOT)
	{
		$DisplayData["page_title"] = $UserRecord["user_login"]."/".$UserRecord["user_title"];
	}
	else
	{
		$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR";
	}

	switch($OpCode)
	{
		case "edit":
			$DisplayData["page_title"] .= ": EDIT";
			break;

		case "add":
			$DisplayData["page_title"] .= ": ADD";
			break;

		case "save_edit":
			$DisplayData["page_title"] .= ": CHANGES SAVED";
			break;

		case "do_del":
			$DisplayData["page_title"] .= ": DELETED";
			break;
	}

	switch($ObjectClass)
	{
		case AIB_ITEM_TYPE_ITEM:
		case AIB_ITEM_TYPE_RECORD:
			$DisplayData["page_title"] .= " ITEM(S)";
			break;

		default:
			$DisplayData["page_title"] .= " RECORD";
			break;
	}

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
							<td colspan='99' height='10'>&nbsp;  </td>
						</tr>
<?php
	// Set up onload call

	//$OnLoadCall = "onload='init_after_load()'";

//include('template/common_header_admin.php');

	// Define fields

	$FieldDef = array(
		"itemrecord_title" => array(
			"title" => "Record Name: <span class='aib-required-field-star'>*</span>", "type" => "text", "display_width" => "64",
			"field_name" => "itemrecord_title", "field_id" => "itemrecord_title",
			"desc" => "", "help_function_name" => "itemrecord_title_help_popup"),

		"itemrecord_visible" => array("title" => "Visible To Public?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "itemrecord_visible", "field_id" => "itemrecord_visible",
			"desc" => "If 'Yes', then the sub group can be seen by public users.", "help_function_name" => "itemrecord_visible_help_popup"),

		"itemrecord_private" => array("title" => "Private?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "itemrecord_private", "field_id" => "itemrecord_private",
			"desc" => "Private records are not published (anyone with a direct link can view this record)", "help_function_name" => "itemrecord_private_help_popup"),

		"file_handling" => array("title" => "Perform OCR?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "file_handling", "field_id" => "file_handling",
			"desc" => "If selected, system will submit files for OCR processing",
			"help_function_name" => "file_handling",
			"option_list" => array("NONE" => "No", "OCR" => "Yes")),

		"itemrecord_default_url" => array(
			"title" => "Item URL:", "type" => "text", "display_width" => "64",
			"field_name" => "itemrecord_default_url", "field_id" => "itemrecord_default_url",
			"desc" => "", "help_function_name" => "itemrecord_title_help_popup"),

		"itemrecord_upload_field" => array(
			"title" => "Upload:", "type" => "custom", "display_width" => "64",
			"field_name" => "itemrecord_upload", "field_id" => "itemrecord_upload",
			"desc" => "", "help_function_name" => "itemrecord_title_help_popup"),

		"itemrecord_upload_progress" => array(
			"title" => "", "type" => "custom", "display_width" => "64",
			"field_name" => "itemrecord_upload_progress", "field_id" => "itemrecord_upload_progress",
			"desc" => ""),

		"itemrecord_upload_list" => array(
			"title" => "", "type" => "custom", "display_width" => "64",
			"field_name" => "itemrecord_upload_list", "field_id" => "itemrecord_upload_list",
			"desc" => ""),

		"itemrecord_attachall" => array(
			"title" => "Attach multiple files to this single record",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_fileattach", "field_id" => "itemrecord_fileattachall",
			"desc" => "", "help_function_name" => "itemrecord_attachall_help_popup"),

		"itemrecord_attachind" => array(
			"title" => "Create individual records for each file (<i>all designated and pre-filled fields will appear in each record</i>)",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_fileattach", "field_id" => "itemrecord_fileattachind",
			"desc" => "", "help_function_name" => "itemrecord_attachind_help_popup"),

		"itemrecord_attachind_userecname" => array(
			"title" => "Use Record Name with iteration numbers",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_ind_which_name", "field_id" => "itemrecord_userecname",
			"desc" => "", "help_function_name" => false),

		"itemrecord_attachind_useorgname" => array(
			"title" => "Use the original file name as the Record Name for each record",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_ind_which_name", "field_id" => "itemrecord_useorgname",
			"desc" => "", "help_function_name" => false),

		"itemrecord_attachall_userecname" => array(
			"title" => "Use Item Title with iteration numbers",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_all_which_name", "field_id" => "itemrecord_all_use_title",
			"desc" => "", "help_function_name" => false),

		"itemrecord_attachall_useorgname" => array(
			"title" => "Use original file names for the Item Title",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_all_which_name", "field_id" => "itemrecord_all_use_original",
			"desc" => "", "help_function_name" => false),

		"itemrecord_default_desc" => array(
			"title" => "Description:", "type" => "textarea", "display_width" => "40,5",
			"rows" => "5", "cols" => "40",
			"field_name" => "itemrecord_default_desc", "field_id" => "itemrecord_default_desc",
			"desc" => "", "help_function_name" => false),
		"itemrecord_default_tags" => array(
			"title" => "Tags:", "type" => "textarea", "display_width" => "40,5",
			"rows" => "5", "cols" => "40",
			"field_name" => "itemrecord_default_tags", "field_id" => "itemrecord_default_tags",
			"desc" => "Words or phrases separated by commas", "help_function_name" => false),
		"itemrecord_default_creator" => array(
			"title" => "Creator Name/Org:", "type" => "text", "display_width" => "40",
			"field_name" => "itemrecord_default_creator", "field_id" => "itemrecord_default_creator",
			"desc" => "", "help_function_name" => false),
		"itemrecord_default_date" => array(
			"title" => "Date/Time:", "type" => "date", "display_width" => "25",
			"field_name" => "itemrecord_default_date", "field_id" => "itemrecord_default_date",
			"desc" => "", "help_function_name" => false),
		"itemrecord_alt_title" => array(
			"title" => "Item Title: <span class='aib-required-field-star'>*</span>", "type" => "text", "display_width" => "45",
			"field_name" => "itemrecord_subtitle", "field_id" => "itemrecord_subtitle",
			"desc" => "<input type='checkbox' id='use_alt_title' name='use_alt_title' value='Y' onchange=\"use_alt_title_callback();\"> <span class='record-name-checkbox'>Same as Record Name</span>",
			"help_function_name" => false),
		"itemrecord_custom_template" => array(
			"title" => "Custom Template:", "type" => "custom", "display_width" => "64",
			"field_name" => "itemrecord_custom_template", "field_id" => "itemrecord_custom_template",
			"desc" => "", "help_function_name" => "itemrecord_title_help_popup"),


	);

	$FieldDef["itemrecord_visible"]["option_list"] = array("Y" => "Yes", "N" => "No");
	$FieldDef["itemrecord_visible"]["value"] = "Y";
	$FieldDef["itemrecord_private"]["option_list"] = array("N" => "No", "Y" => "Yes");
	$FieldDef["itemrecord_private"]["value"] = "N";

	// Define field validations for later code generation

	$ValidationDef = array(
		"itemrecord_title" => array("type" => "text", "field_id" => "itemrecord_title",
				"conditions" => array(
					"notblank" => array("error_message" => "You must enter a name for the record"),
					),
				),
		"_form" => array("function" => "post_process_form"),
		);

	// Create text indicating where this entry is being created

	aib_open_db();
	$ParentRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);
	$ParentTitle = urldecode($ParentRecord["item_title"]);
	$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"archive_name");
	$IDPathList = ftree_get_item_id_path($GLOBALS["aib_db"],$ParentFolderID);
	$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVE GROUP");
	aib_close_db();
	$FormID = "NULL";


	// Field area

?>
	<tr>
		<td align='left' valign='top'>
			<?php
			$OutBuffer = array();
			$ErrorMessage = false;
			$StatusMessage = false;

			switch($OpCode)
			{
				// Save record or item

				case "save_edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					$SessionID = aib_get_session_key();
					$PostData = $FormData;
					$PostData["_key"] = APIKEY;
					$PostData["_session"] = $SessionID;
					$PostData["_op"] = "updaterecord";
					$PostData["_user"] = "1";
					$PostData["objid"] = $PostData["primary"];
					$Result = aib_request($PostData,"recordop");
					$ObjectRecord = ftree_get_object_by_id($GLOBALS["aib_db"],$PostData["primary"]);
					if ($ObjectRecord != false)
					{
						$ParentFolderID = $ObjectRecord["item_parent"];
					}

					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
	
					break;


				// Save edited item and return to list
/*
				case "save_edit":

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Check for errors and save

					$Primary = aib_get_with_default($FormData,"primary",false);
					if ($Primary == false)
					{
						aib_close_db();
						$ErrorMessage = "Missing primary key";
						break;
					}

					// Get record definition for primary key

					$ObjectInfo = ftree_get_object_by_id($GLOBALS["aib_db"],$Primary);
					if ($ObjectInfo == false)
					{
						aib_close_db();
						$ErrorMessage = "Object doesn't exist";
						break;
					}

					// Get parent object

					$ObjectParent = $ObjectInfo["item_parent"];

					// Check for duplicates.  Do this by getting child object by type and title, and see if something other than
					// the current item comes back.

					$UpdatedTitle = aib_get_with_default($FormData,"itemrecord_title","");
					$TestEntry = ftree_get_child_object($GLOBALS["aib_db"],$ObjectParent,FTREE_OBJECT_TYPE_FOLDER,$UpdatedTitle);
					if ($TestEntry != false)
					{
						if ($TestEntry["item_id"] != $Primary)
						{
							aib_close_db();
							$ErrorMessage = "Sub group title is already used.";
							break;
						}
					}

					// Update

					ftree_set_property($GLOBALS["aib_db"],$Primary,"aibftype","sg",true);
					ftree_rename($GLOBALS["aib_db"],$Primary,urlencode($UpdatedTitle));
					aib_close_db();
					$StatusMessage = "Sub group successfully updated";
					break;
*/

				// Edit mode

				case "edit":
				case false:
				default:

					// Connect to DB

					if (aib_open_db() == false)
					{
						$ErrorMessage = "Cannot open database";
						break;
					}

					// Set field values using primary to get itemrecord

					$ItemDef = ftree_get_item($GLOBALS["aib_db"],$FormData["primary"]);
					if ($ItemDef == false)
					{
						$ErrorMessage = "Cannot retrieve sub group definition";
						aib_close_db();
						break;
					}
	
					$ParentDef = ftree_get_item($GLOBALS["aib_db"],$ItemDef["item_parent"]);
					$FieldDef["itemrecord_title"]["value"] = urldecode(aib_get_with_default($ItemDef,"item_title",""));
					$WorldPermissions = $ItemDef["world_perm"];
					if (preg_match("/[R]/",$WorldPermissions) != false)
					{
						$FieldDef["itemrecord_visible"]["value"] = "Y";
					}
					else
					{
						$FieldDef["itemrecord_visible"]["value"] = "N";
					}

					$FormFields = aib_get_record_fields_used($GLOBALS["aib_db"],$FormData["primary"]);
					$FieldsSaved = ftree_field_get_item_fields($GLOBALS["aib_db"],$FormData["primary"]);
					$FieldsUsed = array();
					if ($FormFields != false)
					{
						foreach($FormFields as $FormFieldInfo)
						{
							$FieldID = $FormFieldInfo["field_record"]["field_id"];
							$FieldsUsed[$FieldID] = array("def" => $FormFieldInfo["field_record"], "value" => "");
						}
					}

					if ($FieldsSaved != false)
					{
						foreach($FieldsSaved as $FieldID => $FieldValue)
						{
							$FieldDef = ftree_get_field($GLOBALS["aib_db"],$FieldID);
							if ($FieldDef != false)
							{
								$FieldsUsed[$FieldID] = array("def" => $FieldDef, "value" => $FieldValue);
							}
						}
					}

					$ParentItemID = aib_get_with_default($ParentDef,"item_id","-1");
					$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentItemID);
					if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
					{
						$LocalArchive = $ArchiveInfo["archive"]["item_id"];
						$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
					}

					$BaseURLFields = join("&",array("src=$SourcePage","srckey=$SourceKey","searchval=$SourceSearchValue","srcmode=$SourceMode",
						"srcpn=$SourcePageNumber","parent=$ParentFolderID","folder_id=$ParentFolderID"));
					$OutBuffer[] = "<table width='100%' class='parent-folder-info-table'><tr class='parent-folder-info-table-row'><td class='parent-folder-info-title-cell' width='70%'>";
					switch($ObjectClass)
					{
						case AIB_ITEM_TYPE_ITEM:
							$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'></span></div>";
							break;

						case AIB_ITEM_TYPE_RECORD:
							$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'>You Are Modifying Record <b>$ParentTitle</b></span></div>";
							break;

						default:
							$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'>You Are Creating A Record In: <b>$ParentTitle</b></span></div>";
							break;
					}

					$OutBuffer[] = "</td><td class='parent-folder-info-link-cell' align='right'><a href=\"/2018/admin/manage_my_archive.php?folder_id=".$ParentFolderID."\">Return To List</a></td></tr>";
					$OutBuffer[] = "</table>";

					$RightColContentLines = array();
					$RightColContentLines[] = "<div class='aib-formfield-def-div'>";
					$RightColContentLines[] = aib_generate_field_table($GLOBALS["aib_db"],$UserID,
						array("field_click_callback" => "field_button_callback", "existing_fields" => $FieldsUsed, "archive" => $LocalArchive,
							"archive_group" => $LocalArchiveGroup));
					$RightColContentLines[] = "<div class='clearitall'></div>";
					$RightColContentLines[] = "</div>";

					$DisplayData["right_col"] = join("\n",$RightColContentLines);

					$OutBuffer[] = aib_gen_form_header("pageform","/2018/admin/record_modify.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='primary' value='".$FormData["primary"]."'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"primary","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='file_batch' value='".microtime(true)."'>";
					$OutBuffer[] = "<input type='hidden' name='user' value='$UserID'>";
					$OutBuffer[] = "<input type='hidden' name='file_handling' value='NULL'>";
					$OutBuffer[] = "<input type='hidden' name='parent_list' id='parent_list' value=''>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save_edit'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					aib_close_db();
					break;

/*
				// Add new itemrecord

				case "add":
					if (isset($FormID) == false)
					{
						$FormID = "NULL";
					}
					else
					{
						if ($FormID == false)
						{
							$FormID = "NULL";
						}
					}

					aib_open_db();
					$BaseURLFields = join("&",array("src=$SourcePage","srckey=$SourceKey","searchval=$SourceSearchValue","srcmode=$SourceMode",
						"srcpn=$SourcePageNumber","parent=$ParentFolderID"));
					$ParentItemID = aib_get_with_default($FormData,"parent","-1");
					$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentItemID);
					if (isset($ArchiveInfo["archive"]) != false && isset($ArchiveInfo["archive_group"]) != false)
					{
						$LocalArchive = $ArchiveInfo["archive"]["item_id"];
						$LocalArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];
					}
					else
					{
						$LocalArchive = "-1";
						$LocalArchiveGroup = "-1";
					}

					$FieldDef["archive_title"]["value"] = "";
					$IndicatorEntryTemplate = "<a class='aib-loc-path-link' href='/records.php?opcode=list&parent=[[ITEMID]]'>[[TITLE]]</a>";
					$IndicatorOptions = array("entry_template" => $IndicatorEntryTemplate, "ul_template" => "<ul class='aib-loc-indicator-list'>");
					$IndicatorOptions["pad_cell_template"] = "<td width='10' style='padding:0;'> </td>";
					$IndicatorOptions["entry_cell_template"] = "<td colspan='99'> &#9495; ";
					$IndicatorOptions["table_template"] = "<table width='100%'>";
					if ($ObjectClass != AIB_ITEM_TYPE_ITEM && $ObjectClass != AIB_ITEM_TYPE_RECORD)
					{
						$RightColContentLines = array();
						$RightColContentLines[] = "<div class='aib-formfield-def-div'>";
						$RightColContentLines[] = aib_generate_field_table($GLOBALS["aib_db"],$UserID,
							array("field_click_callback" => "field_button_callback","archive" => $LocalArchive,"archive_group" => $LocalArchiveGroup));
						$RightColContentLines[] = "<div class='clearitall'></div>";
						$RightColContentLines[] = "</div>";
	
						$DisplayData["right_col"] = join("\n",$RightColContentLines);
					}

					$OutBuffer[] = "<table width='100%' class='parent-folder-info-table'><tr class='parent-folder-info-table-row'><td class='parent-folder-info-title-cell' width='70%'>";
					switch($ObjectClass)
					{
						case AIB_ITEM_TYPE_ITEM:
							$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'></span></div>";
							break;

						case AIB_ITEM_TYPE_RECORD:
							$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'>You Are Adding Items To Record <b>$ParentTitle</b></span></div>";
							break;

						default:
							$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'>You Are Creating A Record In: <b>$ParentTitle</b></span></div>";
							break;
					}

					$OutBuffer[] = "</td><td class='parent-folder-info-link-cell' align='right'><a href=\"/2018/admin/manage_my_archive.php?folder_id=".$_REQUEST['parent']."\">Return To List</a></td></tr>";
					$OutBuffer[] = "</table>";
					$OutBuffer[] = "<br><br>";
					$OutBuffer[] = aib_gen_form_header("pageform","/record_modify.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='file_handling' value='NULL'>";
					$OutBuffer[] = "<input type='hidden' name='file_batch' value='".microtime(true)."'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='parent_list' id='parent_list' value=''>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					$OutBuffer[] = "<input type='hidden' name='user' value='$UserID'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					$FieldDef["itemrecord_attachall"]["checked"] = "CHECKED";
					$FieldDef["itemrecord_attachall"]["value"] = "all";
					$FieldDef["itemrecord_attachind"]["value"] = "ind";
					break;
*/

			}

			// Show status or error message

			if ($ErrorMessage != false || $StatusMessage != false)
			{
				$OutBuffer[] = "<table class='aib-form-result-message-table'>";
				$OutBuffer[] = "<tr class='aib-form-result-message-header-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-header-cell'>";
				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "<tr class='aib-form-result-message-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-cell'>";
				if ($ErrorMessage != false)
				{
					$OutBuffer[] = "<span class='aib-form-result-message-error-span'>";
					$OutBuffer[] = $ErrorMessage;
					$OutBuffer[] = "</span>";
				}
				else
				{
					$OutBuffer[] = "<span class='aib-form-result-message-status-span'>";
					$OutBuffer[] = $StatusMessage;
					$OutBuffer[] = "</span>";
				}

				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "<tr class='aib-form-result-message-footer-row'>";
				$OutBuffer[] = "<td class='aib-form-result-message-footer-cell'>";
				$OutBuffer[] = "</td></tr>";
				$OutBuffer[] = "</table>";
			}

			// Output form or chain depending on operation

			$SourcePage = aib_get_with_default($FormData,"src",false);
			$SourceKey = aib_get_with_default($FormData,"srckey","");
			$SourceSearchValue = aib_get_with_default($FormData,"searchval","");
			$SourceMode = aib_get_with_default($FormData,"srcmode","");
			$SourcePageNumber = aib_get_with_default($FormData,"srcpn",1);
			$ArchiveCode = aib_get_with_default($FormData,"archive_code",-1);
			$ParentFolderID = aib_get_with_default($FormData,"parent",$UserRecord["user_top_folder"]);
			$DefaultFields = array(
				"opcode" => "list",
				"srckey" => $SourceKey,
				"searchval" => $SourceSearchValue,
				"srcmode" => $SourceMode,
				"srcpn" => $SourcePageNumber,
				"parent" => $ParentFolderID,
				"archive_code" => $ArchiveCode,
				);
			$TargetFields = join("&",
				array("src=records","folder_id=$ParentFolderID","parent=$ParentFolderID","srckey=$SourceKey","searchval=$SourceSearchValue","srcmode=$SourceMode","srcpn=$SourcePageNumber","archive_code=$ArchiveCode"));

			switch($OpCode)
			{
				case "save_edit":
					$ParentFolderID = $ObjectRecord["item_parent"];
					$TargetFields = join("&",
						array("src=records","folder_id=$ParentFolderID","parent=$ParentFolderID","srckey=$SourceKey","searchval=$SourceSearchValue","srcmode=$SourceMode","srcpn=$SourcePageNumber","archive_code=$ArchiveCode"));
					$DefaultFields["parent"] = $ParentFolderID;
					if ($ErrorMessage != false)
					{
						break;
					}
					//$TargetSpec = array(array("url" => "/2018/admin/manage_my_archive.php?folder_id=".$ParentFolderID,"title" => "Return To Records Management"));
					//$OutBuffer[] = aib_chain_link_set($TargetSpec);
					$OutBuffer[] = '<script>window.location.href="/2018/admin/manage_my_archive.php?folder_id='.$ParentFolderID.'";</script>';
					break;

/*
				case "save":
					if ($ErrorMessage != false)
					{
						break;
					}

					$TargetSpec = array();
					$TargetSpec[] = array("url" => "/records.php","title" => "Return To Record Management","fields" => $DefaultFields);
					$TempFields = $DefaultFields;
					$TempFields["opcode"] = "add";
					$TargetSpec[] = array( "url" => "/record_modify.php", "title" => "Add Another Record","fields" => $TempFields);
					$OutBuffer[] = aib_chain_link_set($TargetSpec);
					break;
*/
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					$FieldDef["itemrecord_upload_field"]["fielddata"] = "
						<div class='upload-container'>
							<input id=\"fileupload\" type=\"file\" name=\"files[]\" data-url=\"../../server/php/\" multiple>  <button type='button' onclick='trigger_upload()' id='aib_start_upload_button'>Start Uploads</button>
						</div>
					";

					$FieldDef["itemrecord_upload_progress"]["fielddata"] = "
						<div class='upload-container'>
							<div id=\"progress\" class=\"progress\">
								<div class=\"upload-progress-bar\" style='width:0%;' id='upload_progress_bar'></div>
							</div>
						</div>
					";
					
					$FieldDef["itemrecord_upload_list"]["fielddata"] = "
						<div> <table id='upload_file_progress_list' class='aib-upload-status-list-table' cellpadding='0' cellspacing='0'><tbody> </tbody></table> </div>
					";
					
					// Primary (fixed) fields

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					if ($ObjectClass != AIB_ITEM_TYPE_ITEM)
					{
						$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 1: About Your Record</td></tr>";
					}
					else
					{
						$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 1: About Your Item</td></tr>";
					}

					if ($ObjectClass == AIB_ITEM_TYPE_ITEM || $ObjectClass == AIB_ITEM_TYPE_RECORD)
					{
						$FieldDef["itemrecord_title"]["title"] = "Item Title:";
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_title"]);
					if ($ObjectClass != AIB_ITEM_TYPE_ITEM)
					{
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_alt_title"]);
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["itemrecord_visible"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["itemrecord_private"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["file_handling"]);
					$OutBuffer[] = aib_draw_input_row_separator();

					// Default (fixed) optional fields

//					$OutBuffer[] = aib_draw_textarea_field($FieldDef["itemrecord_default_desc"]);
//					$OutBuffer[] = aib_draw_input_row_separator();
//					$OutBuffer[] = aib_draw_textarea_field($FieldDef["itemrecord_default_tags"]);
//					$OutBuffer[] = aib_draw_input_row_separator();
//					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_default_creator"]);
//					$OutBuffer[] = aib_draw_input_row_separator();
//					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_default_date"]);
//					$OutBuffer[] = aib_draw_input_row_separator();

					// Container for user-defined fields and forms


					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 2: Designate Your Fields </td></tr>";

					aib_open_db();
					$LocalBuffer = aib_generate_template_dropdown($GLOBALS["aib_db"],$UserID,
						array("field_name" => "form_id", "field_id" => "form_id", "select_class" => "aib-template-select-class",
							"archive_id" => $LocalArchive, "archive_group_id" => $LocalArchiveGroup,
							"archive_code" => aib_get_with_default($FormData,"archive_code","-1"),
							"option_class" => "aib-template-option-class",
							"title_option_class" => "aib-template-title-option-class",
							"select_callback" => "aib_template_select_callback",
							)
						);
					$FieldDef["itemrecord_custom_template"]["fielddata"] = $LocalBuffer;
					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_custom_template"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					aib_close_db();

					// If the parent is a record or item, draw all of the fields used for the parent's form and/or custom fields.

					if ($ObjectClass == AIB_ITEM_TYPE_ITEM || $ObjectClass == AIB_ITEM_TYPE_RECORD)
					{
						// Get the fields for the parent record

						aib_open_db();
						$CustomFieldInfo = ftree_field_get_item_fields_ext($GLOBALS["aib_db"],$ParentRecord["item_id"]);
						$OutBuffer[] = aib_draw_user_def_field_area("itemrecord-user-def-fields",$CustomFieldInfo);
						aib_close_db();
					}
					else
					{
						$OutBuffer[] = aib_draw_user_def_field_area("itemrecord-user-def-fields");
					}

					$OutBuffer[] = aib_draw_input_row_separator();

					if ($ObjectClass != AIB_ITEM_TYPE_ITEM)
					{
						$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
						$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
						$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
						$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 3: Define Record Characteristics </td></tr>";

						// Uploaded item attachment and processing options

						if ($ObjectClass != AIB_ITEM_TYPE_ITEM)
						{
							$FieldDef["itemrecord_attachind_userecname"]["value"] = "rec";
							$FieldDef["itemrecord_attachind_userecname"]["checked"] = "CHECKED";
							$FieldDef["itemrecord_attachind_useorgname"]["value"] = "org";
							$FieldDef["itemrecord_attachall_userecname"]["value"] = "rec";
							$FieldDef["itemrecord_attachall_userecname"]["checked"] = "CHECKED";
							$FieldDef["itemrecord_attachall_useorgname"]["value"] = "org";
//							$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachind"]);
//							$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachind_userecname"]);
//							$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachind_useorgname"]);
							$OutBuffer[] = aib_draw_input_row_separator();
//							$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachall"]);
							$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachall_userecname"]);
							$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachall_useorgname"]);
							$OutBuffer[] = aib_draw_input_row_separator();
						}
					}

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					if ($ObjectClass != AIB_ITEM_TYPE_ITEM)
					{
						$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 4: Attach &amp; Upload Additional Files</td></tr>";
					}
					else
					{
						$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 3: Optionally Replace File</td></tr>";
					}

					// Upload field

					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_field"]);
					$OutBuffer[] = aib_draw_input_row_separator();

					// Upload progress display

					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_progress"]);

					// List of files being uploaded

					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_list"]);
					$OutBuffer[] = aib_draw_input_row_separator();

					// URL

					if ($ObjectClass != AIB_ITEM_TYPE_RECORD && $ObjectClass != AIB_ITEM_TYPE_ITEM)
					{
						$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_default_url"]);
						$OutBuffer[] = aib_draw_input_row_separator();
					}

					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							$OutBuffer[] = aib_draw_form_submit("Edit Record","Clear Form");
							break;
					}

					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";
					break;
			}

			print(join("\n",$OutBuffer));
			?>
		</td>
	</tr>
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
<?php
	// Include the footer


	// Generate validation functions (code generator)

	print(aib_gen_field_validations("pageform","validate_form",$ValidationDef));

	// Other scripts

	$AddFieldOpcode = bin2hex("addfield");
	$AddFormOpcode = bin2hex("addform");
	$LoadFieldsOpcode = bin2hex("loadfields");
	$RemoveFormOpcode = bin2hex("removeform");
	$RemoveFieldOpcode = bin2hex("delfield");
	$GetChildrenOpcode = bin2hex("chl");
	$GenerateSelectedList = bin2hex("gsl");
	$EncodedUserID = bin2hex($UserID);
	print("
		<script>

		// Global to hold current form

		var CurrentFormID = -1;

		// Global to hold field values

		var UserDefFieldValues = {};

		// Global to hold already-loaded subtrees
	");

	// If there is already nav info, initialize array with data, else empty array.

	if (count(array_keys($TreeNavInfo)) > 0)
	{
		print("
				var CheckedTreeItems = {".$TreeNavInfo["init_item"].":'Y'};
		");
	}
	else
	{
		print("
				var CheckedTreeItems = {};
		");
	}

	if (isset($TreeNavInfo["idlist"]) == true)
	{
		if (count($TreeNavInfo["idlist"]) > 0)
		{
			print("
				var NavLoadedMap = {
			");
			foreach($TreeNavInfo["idlist"] as $ItemID)
			{
				print("
					$ItemID:'Y',
				");
			}

			print("
				};
			");
		}
		else
		{
			print("
			var NavLoadedMap = {};
			");
		}
	}
	else
	{
		print("
		var NavLoadedMap = {};
		");
	}


	print("
		var InitCheckedDisplay = false;
		var FieldsLoaded = 0;
		var TreeObjectID = '$CurrentObjectID';

		// Initialization for page
		
		function init_after_load()
		{
			\$('#aib_start_upload_button').prop('disabled',true);
			aib_load_current_fields();
		}
			
		// Set up event handler for custom template form selection box

		\$('#form_id').change(function() {
			var LocalObj;

			LocalObj = \$('#form_id');

    			\$('#form_id').css('color', \$('#form_id option:selected').css('color'));
			});



		// Template select button clicked; issue command to remove existing for fields, if any.
		// ------------------------------------------------------------------------------------
		function aib_template_select_callback(LocalEvent,RefObj)
		{
			var QueryParam = {};

			LocalEvent.stopPropagation();

			// Load template fields after first discarding any currently loaded template.

			aib_load_template_fields();

		}

		// Load template fields if no template used, else remove existing template (the
		// callback for the remove routine will load the new template).
		// -------------------------------------------------------------------------------
		function aib_load_template_fields()
		{
			var LocalFormID;
			QueryParam = {};

			// If there is already a form in use, we need to delete it first.

			if (CurrentFormID !== 'NULL' && CurrentFormID !== 'BLANK' && CurrentFormID >= 0)
			{
				QueryParam['o'] = '$RemoveFormOpcode';
				QueryParam['i'] = '$EncodedUserID';
				QueryParam['fi'] = CurrentFormID;
				\$('#aib_select_form_button').prop('disabled',true);
				\$('#aib_select_form_button').text('Loading Form');
				aib_ajax_request('/services/airrecord.php',QueryParam,aib_remove_template_fields_callback);
				return;
			}

			// Otherwise, send request to load template as long as a form is selected.

			CurrentFormID = \$('#form_id').val();
			if (CurrentFormID !== 'BLANK' && CurrentFormID !== 'NULL')
			{
				QueryParam['o'] = '$AddFormOpcode';
				QueryParam['i'] = '$EncodedUserID';
				QueryParam['fi'] = CurrentFormID;
				\$('#aib_select_form_button').prop('disabled',true);
				\$('#aib_select_form_button').text('Loading Form');
				aib_ajax_request('/services/airrecord.php',QueryParam,aib_load_template_fields_callback);
				return;
			}

			return;
		}

		// Load existing fields.
		// -------------------------------------------------------------------------------
		function aib_load_current_fields()
		{
			var LocalFormID;
			QueryParam = {};

			if (FieldsLoaded == 0)
			{
				FieldsLoaded = 1;

				// If there is already a form in use, we need to delete it first.

				QueryParam['o'] = '$LoadFieldsOpcode';
				QueryParam['i'] = '$EncodedUserID';
				QueryParam['objid'] = TreeObjectID;
				\$('#aib_select_form_button').prop('disabled',true);
				\$('#aib_select_form_button').text('Loading Form');
				aib_ajax_request('/services/airrecord.php',QueryParam,aib_load_template_fields_callback);
				return;
			}
		}

		// Callback to remove template fields.  Fields are removed, and then the new form, if any, is loaded.
		// --------------------------------------------------------------------------------------------------
		function aib_remove_template_fields_callback(InData)
		{
			var FieldList = [];
			var ListSize;
			var Counter;
			var RowName;
			var FieldID;
			var FormID;
			var QueryParam = {};

			if (InData['status'] != 'OK')
			{
				alert('ERROR PROCESSING FORM REMOVAL REQUEST: ' + InData['info']['msg']);
				\$('#aib_select_form_button').prop('disabled',false);
				\$('#aib_select_form_button').text('Use This Custom Template');
				return;
			}

			// Remove fields

			FieldList = InData['info']['field_list'].split(',');
			ListSize = FieldList.length;
			if (ListSize > 0)
			{
				for (Counter = 0; Counter < ListSize; Counter++)
				{
					// Remove field from screen

					FieldID = FieldList[Counter];
					RowName = 'userfield_' + FieldID + '_field';
					\$('#' + RowName).remove();

					// If field exists in list of fields, set Add button to enabled and
					// disable the Remove button.

					\$('#addfieldbutton_' + FieldID).prop('disabled',false);
					\$('#removefieldbutton_' + FieldID).prop('disabled',true);
				}
			}

			// Send request t load fields for currently selected form, if any

			CurrentFormID = \$('#form_id').val();
			if (CurrentFormID !== 'NULL' && CurrentFormID !== 'BLANK')
			{
				QueryParam['o'] = '$AddFormOpcode';
				QueryParam['i'] = '$EncodedUserID';
				QueryParam['fi'] = CurrentFormID;
				aib_ajax_request('/services/airrecord.php',QueryParam,aib_load_template_fields_callback);
			}

			\$('#aib_select_form_button').prop('disabled',false);
			\$('#aib_select_form_button').text('Use This Custom Template');
			return;
		}

		// Callback to place loaded form fields in document
		// ------------------------------------------------
		function aib_load_template_fields_callback(InData)
		{
			var HTML;
			var RowName;
			var FieldID;
			var ListSize;
			var Counter;
			var FieldList = [];

			if (InData['status'] != 'OK')
			{
				alert('ERROR PROCESSING FORM REQUEST: ' + InData['info']['msg']);
				\$('#aib_select_form_button').prop('disabled',false);
				\$('#aib_select_form_button').text('Use This Custom Template');
				return;
			}

			HTML = InData['info']['html'];
			\$('#itemrecord-user-def-fields tbody').html(HTML);

			// Disable the Add button for all fields in the form if they are
			// in the list.  Enable the Remove button.

			FieldList = InData['info']['field_list'].split(',');
			ListSize = FieldList.length;
			for (Counter = 0; Counter < ListSize; Counter++)
			{
				FieldID = FieldList[Counter];
				\$('#addfieldbutton_' + FieldID).prop('disabled',true);
				\$('#removefieldbutton_' + FieldID).prop('disabled',false);
			}

			\$('#aib_select_form_button').prop('disabled',false);
			\$('#aib_select_form_button').text('Use This Custom Template');
		}


		// USER DEFINED FIELDS
		// ===================

		// Add a user-defined field request.  If the button is clicked in the field
		// list, adds the field to the display.

		function field_button_callback(RefObj,FieldID,OpFlag)
		{
			var QueryParam = {};

			if (OpFlag == 1)
			{
				QueryParam['o'] = '$AddFieldOpcode';
			}
			else
			{
				remove_user_defined_field(FieldID);
				\$('#addfieldbutton_' + FieldID).prop('disabled',false);
				\$('#removefieldbutton_' + FieldID).prop('disabled',true);
				return;
			}

			QueryParam['i'] = '$EncodedUserID';
			QueryParam['fi'] = FieldID;
			aib_ajax_request('/services/airrecord.php',QueryParam,add_user_defined_field_result);
			\$('#addfieldbutton_' + FieldID).prop('disabled',true);
			\$('#removefieldbutton_' + FieldID).prop('disabled',false);
		}

		// Add user-defined field to form

		function add_user_defined_field_result(InData)
		{
			var HTML;
			var RowName;
			var FieldID;

			if (InData['status'] != 'OK')
			{
				alert('ERROR PROCESSING FIELD REQUEST: ' + InData['info']['msg']);
				return;
			}


			// Define row name

			FieldID = InData['info']['field_id'];
			RowName = 'userfield_' + FieldID + '_field';

			// Set up row and title field cell opening

			HTML = \"<tr class='aib-user-def-field-row' id='\" + RowName + \"'><td class='aib-user-def-field-title-cell'>\";
			HTML = HTML + InData['info']['title'] + \"</td>\";
			HTML = HTML + \"<td class='aib-input-title-divider-cell'> </td>\";
			HTML = HTML + \"<td class='aib-user-def-field-input-cell'>\" + InData['info']['input'] + \"</td>\";
			HTML = HTML + \"<td class='aib-user-def-field-desc-cell'>\" + InData['info']['desc'] + \"</td></tr>\";

			// Add row to user-defined fields table

			if (aib_add_row_to_table('itemrecord-user-def-fields',HTML) < 0)
			{
				alert('ERROR: Cannot display field');
			}

//			\$('#itemrecord-user-def-fields > tbody').append(HTML);

			// Set the field default value.  If there is no default value, then use the
			// return data sent to this function.

			FieldID = InData['info']['field_id'];
			if (UserDefFieldValues[FieldID] == undefined)
			{
				UserDefFieldValues[FieldID] = InData['info']['value'];
			}

			\$('#' + FieldID).val(UserDefFieldValues[FieldID]);
		}

		// Error if bad request; add row to table with error text

		function add_user_defined_field_error(ReqObj,ErrorStatus,ErrorText)
		{
			HTML = HTML + \"</td><td class='aib-user-def-field-input-cell'>\";
			HTML = HTML + \"ERROR: \" + ErrorText;
			HTML = HTML + \"</td><td class='aib-user-def-field-desc-cell'> </td></tr>\";
			\$('#itemrecord-user-def-fields > tbody').append(HTML);
		}

		// Remove a user-defined field from form

		function remove_user_defined_field(FieldID)
		{
			var RowName;

			RowName = 'userfield_' + FieldID + '_field';
			\$('#' + RowName).remove();
		}

		function use_alt_title_callback()
		{
			if (\$('#use_alt_title').is(':checked'))
			{
				\$('#itemrecord_subtitle').prop('disabled',true);
			}
			else
			{
				\$('#itemrecord_subtitle').prop('disabled',false);
			}
		}

		// TREE NAVIGATION FUNCTIONS
		// =========================

		// Fetch children for tree

		function fetch_tree_children(LocalEvent,RefObj,ItemID)
		{
			var QueryParam = {};
			var ChildList;

			LocalEvent.stopPropagation();
			if (NavLoadedMap[ItemID] == undefined)
			{
				NavLoadedMap[ItemID] = 'Y';
				QueryParam['o'] = '$GetChildrenOpcode';
				QueryParam['i'] = '$EncodedUserID';
				QueryParam['pi'] = ItemID;
				aib_ajax_request('/services/treenav.php',QueryParam,fetch_tree_children_result);
				return;
			}

			ChildList = \$('#aib_navlist_childof_' + ItemID);
			if (ChildList !== undefined)
			{
				if (ChildList.css('display') != 'none')
				{
					ChildList.css('display','none');
					\$(RefObj).css('list-style-image',\"url('/images/button-closed.png')\");
				}
				else
				{
					ChildList.css('display','block');
					\$(RefObj).css('list-style-image',\"url('/images/button-open.png')\");
				}
			}

		}

		// Set checkbox for tree item, preventing bubble-up

		function set_tree_checkbox(LocalEvent,RefObj)
		{
			var ElementID;

			LocalEvent.stopPropagation();
			ElementID = \$(RefObj).attr('id');
			ElementID = ElementID.replace('aib_item_checkbox_','',ElementID);
			if (\$(RefObj).is(':checked') == true)
			{
				\$(RefObj).prop('checked',true);
				CheckedTreeItems[ElementID] = 'Y';
			}
			else
			{
				\$(RefObj).prop('checked',false);
				CheckedTreeItems[ElementID] = 'N';
			}

			
			show_checked_tree_items();
		}

		// Callback for tree children fetch

		function fetch_tree_children_result(InData)
		{
			var ElementID;
			var ItemID;

			if (InData['status'] != 'OK')
			{
				alert('ERROR PROCESSING CHILD REQUEST: ' + InData['info']['msg']);
				return;
			}

			ItemID = InData['info']['item_id'];
			ElementID = 'aib_navlist_entry_' + ItemID;
			\$('#' + ElementID).append(InData['info']['html']);
			show_checked_tree_items();
		}

		// Show a list of all checked tree items using AJAX to retrieve HTML from
		// a back-end HTML generator.

		function show_checked_tree_items()
		{
			var CheckedItemsList;
			var Size;
			var Counter;
			var IDValue;
			var QueryParam = {};
			var IDList = [];
			var Key;

			// Get a list of all checked items

			for (Key in CheckedTreeItems)
			{
				if (CheckedTreeItems[Key] == 'Y')
				{
					IDList.push(Key);
				}
			}


			// Generate an unsorted list in display area to show items

			QueryParam['idlist'] = IDList.join(',');
			QueryParam['o'] = '$GenerateSelectedList';
			QueryParam['i'] = '$EncodedUserID';
			aib_ajax_request('/services/treenav.php',QueryParam,show_selected_tree_items);
			return;
		}

		function show_selected_tree_items(InData)
		{
			if (InData['status'] != 'OK')
			{
				\$('#aibselectedtreeitems').html(\"ERROR: Can't get list\");
				return;
			}

			\$('#aib-selected-tree-items').html(InData['info']['html']);
		}

		// Copy the selected items array to input form

	");

	// Create a string containing a list of all the subgroups/records where the new data
	// is to be stored.  This is passed to the back end queue for later processing.

	if ($ObjectClass == AIB_ITEM_TYPE_RECORD)
	{
		print("
		function post_process_form()
		{
			var IDList = [];
			var Key;

			// Get a list of all checked items

			for (Key in CheckedTreeItems)
			{
				if (CheckedTreeItems[Key] == 'Y')
				{
					IDList.push(Key);
				}
			}

			\$('#parent_list').val(IDList.join(','));
			return(true);
		}
		");
	}
	else
	{
		print("
		function post_process_form()
		{
			return(true);
		}
		");
	}

	print("

		// If the checked display area hasn't been initialized, do so here

		if (InitCheckedDisplay == false)
		{
			InitCheckedDisplay = true;
			show_checked_tree_items();
		}


		</script>
		");
?>

</div></section></div>
<script type="text/javascript">
	$(document).ready(function(){
		$('.sidebar-toggle').trigger('click');
	});
</script>
<?php //include_once COMMON_TEMPLATE_PATH.'footer.php'; ?>
<?php 
    $jsArray = [
         'bootstrap.min.js',
        'jquery-form-validate.min.js',        
        'jquery.dataTables.min.js',
        'adminlte.min.js',
	'form_validation.js'
    ];
?>
<footer class="main-footer">
    <div class="pull-right hidden-xs"></div>
    <div class="text-center"><span class="topMargin20"><strong>Copyright &copy; <?php echo date('Y'); ?> </strong>  All rights reserved. "ArchiveInABox" and box device is a registered trademark of SmallTownPapers, Inc.</span>
				<ul class="socialIcons">
                    <li><a href="#"><img src="public/images/fb.png"></a></li>
                    <li><a href="#"><img src="public/images/twitter.png"></a></li>
                    <li><a href="#"><img src="public/images/pinterest.png"></a></li>
                    <li><a href="#"><img src="public/images/linkedIn.png"></a></li>
                </ul>
				</div>
				<div class="clearfix"></div>
	</footer>
</div>
    <?php foreach($jsArray as $key=>$fileName){ ?>
        <script src="<?php echo JS_PATH.$fileName; ?>"></script>
    <?php } ?>
     
    <script type="text/javascript">
        $(document).ready(function(){
        $('.sidebar-menu li').each(function(){
            if($(this).hasClass('menu-open')){
                $(this).children('ul').show();
            }
        });
    });
    </script>
        
</body>
</html>
