<?php
include('../config/aib.php');
include("../include/folder_tree.php");
include("../include/fields.php");
include('../include/aib_util.php');
// Function to call server
// -----------------------
function aib_request($LocalPostData,$FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => AIB_SERVICE_URL."/api/".$FunctionSet.".php",
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
	$Handle = fopen("/tmp/record_form_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,date("Y-m-d H:i:s")." -- ".$Msg."\n");
		fclose($Handle);
	}
}


session_start();

if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
$previous = '';
if(isset($_REQUEST['previous']) && $_REQUEST['previous'] != ''){
    $previous = $_REQUEST['previous'];
}
include_once 'config/config.php';
if(!(isset($_REQUEST['opcode']) and $_REQUEST['opcode']=='save')  ){    


include_once COMMON_TEMPLATE_PATH.'header.php';
    include_once COMMON_TEMPLATE_PATH.'sidebar.php';
  

?>
<div class="content-wrapper">
        <section class="content-header">
            <h1>My Archive</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Add Record</li>
            </ol>
           <h4 class="list_title">Add Record </h4>
        </section>
        <section class="content bgTexture">
        
<div class="content2">

<?php
}

	/*session_start();
	$CheckResult = aib_check_session();
	echo 'hjfj<pre>';print_R($CheckResult);die;
	if ($CheckResult[0] != "OK")
	{
		$ErrorText = bin2hex($CheckResult[1]);
		header("Location: /login_error.php?v=$ErrorText");
		exit(0);
	}

	// Get user info from session data

$CheckResult = aib_check_session();
	$SessionInfo['init']='1516597563';
	$SessionInfo['timeout']='28800';
	$SessionInfo['login']=$_SERVER['user_login'];
	$SessionInfo['recent']='1516597725';
	//$UserInfo = aib_get_user_info($SessionInfo);

	if ($UserInfo[0] != "OK")
	{
		$ErrorText = bin2hex("Cannot retrieve user profile");
		header("Location: /login_error.php?v=$ErrorText");
		exit(0);
	}
*/
	// Get the user type

	//$UserRecord = 1;//$UserInfo[1];
	//echo '<pre>';
	//print_R($_SESSION['aib']['user_data']['user_type']);
	$UserType = $_SESSION['aib']['user_data']['user_type'];//$UserRecord["user_type"];
	$UserID = $_SESSION['aib']['user_data']['user_id'];//$UserRecord["user_id"];
	$UserGroup = $_SESSION['aib']['user_data']['user_top_folder'];//$UserRecord["user_primary_group"];

	// Load user record

	if (aib_open_db() != false)
	{
		$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
		aib_close_db();
	}

	// Get form data and opcode

	$FormData = aib_get_form_data();	
	
	$OpCode = aib_get_with_default($FormData,"opcode",false);
	$SourcePage = aib_get_with_default($FormData,"src",false);
	$SourceKey = aib_get_with_default($FormData,"srckey","");
	$SourceSearchValue = aib_get_with_default($FormData,"searchval","");
	$SourceMode = aib_get_with_default($FormData,"srcmode","");
	$SourcePageNumber = aib_get_with_default($FormData,"srcpn",1);
	$ParentFolderID = aib_get_with_default($FormData,"parent",$UserRecord["user_top_folder"]);
	if($UserType == AIB_USER_TYPE_USER){
		$FormData['opt_show_archive_group_fields']  = 'N';
		$FormData['opt_show_system_fields']         = 'N';
		$FormData['opt_show_recommended_fields']    = 'Y';
		$FormData['opt_show_traditional_fields']    = 'N';
		$FormData['opt_show_archive_fields']        = 'Y';
		$FormData['opt_show_user_fields']           = 'N';
		$FormData['opt_show_symbolic_fields']       = 'N';
	}
	// Get current archive based on user.  If this is the super-user, then no current archive

	$CurrentArchive = false;
	switch($UserType)
	{
		// Root user (superadmin) has no archive

		case AIB_USER_TYPE_ROOT:
			$CurrentArchive = false;
			break;

		// Administrator has archive based on root folder.

		case AIB_USER_TYPE_ADMIN:
			$CurrentArchive = $UserRecord["user_top_folder"];
			break;

		// Standard users can't add a collection, nor can sub-admins.

		case AIB_USER_TYPE_SUBADMIN:
		case AIB_USER_TYPE_USER:
		case AIB_USER_TYPE_PUBLIC:
			$CurrentArchive = $UserRecord["user_top_folder"];
			break;

		default:
			$ErrorText = bin2hex("Unauthorized operation");
			header("Location: login.php");
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
		"page_title" => "SYSTEM ADMINISTRATOR: ADD RECORD",
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
			window.location.href='login.php';
		},$PageTimeout);

		// Prevent the use of the 'back' button to prevent mangling
		// forms. 

		history.pushState(null, null, document.URL);
		//window.addEventListener('popstate', function () {
		    //alert(\"Please use the links on the page instead of the 'Back' button.\");
		   // history.pushState(null, null, document.URL);
		//});

		";




if(!(isset($_REQUEST['opcode']) and $_REQUEST['opcode']=='save')  ){  
?>
<link rel="stylesheet" href="../css/aib.css">
		<script type='text/javascript' src='../jquery-3.2.0.min.js'> </script>
		<script type='text/javascript' src='../js/aib.js'> </script>
<script src="../js/vendor/jquery.ui.widget.js"></script>
<script src="../js/jquery.iframe-transport.js"></script>
<script src="../js/jquery.fileupload.js"></script>
<script>
	var userType="<?php echo $UserType; ?>";
	var forUserType="<?php echo 'X'; ?>";
	//Disable checkboxes from the left panel
	$(document).ajaxStop(function() {
        if(userType==forUserType){
   			$("input[id^=aib_item_checkbox_]").attr("disabled",true);
	    }
    });
	$("document").ready(function(){
		document.getElementById('itemrecord_userecname').disabled = true;
		document.getElementById('itemrecord_useorgname').disabled = true;
		if(userType==forUserType){
   			$("input[id^=aib_item_checkbox_]").attr("disabled",true);
		}
	});
	//alert(userType+"=="+forUserType);
	
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
		//Fix start for Issue ID 2140 on 23-Feb-2023
		else
		{
			$.post('services_admin_api.php',{
				mode: 'add_total_item_count',
				parent_id: '<?php echo $_REQUEST['parent']; ?>',
				item_count: QueuedCount
			},function(response){
				console.log(response)
			});
		}
	}

// Set up uploader.

$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
	disablePreview: true,
	sequentialUploads: true,
	disableExifThumbnail: true,
	previewThumbnail: false,

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
}
	// Get parent folder ID

	$ParentFolderID = aib_get_with_default($FormData,"parent","");

	// Get parent folder type

	aib_open_db();
	$ParentFolderType = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$ParentFolderRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);
	aib_close_db();
	if ($ParentFolderType === false)
	{
		$ParentFolderType = AIB_ITEM_TYPE_ITEM;
	}

	// If in "add" mode, include the checkbox tree in the left column if we're not adding items to an existing record.  The
	// "left_col" data is substituted into the template on display.

	$TreeNavInfo = array();
	if ($OpCode == "add" && $ParentFolderType != AIB_ITEM_TYPE_ITEM && $ParentFolderType != AIB_ITEM_TYPE_RECORD)
	{
		aib_open_db();
		$TreeNavInfo = array("idlist" => array());
		$TreeNavInfo = aib_generate_tree_nav_div($GLOBALS["aib_db"],$UserID,$ParentFolderID,"fetch_tree_children","aib-nav-tree-ul","aib-nav-tree-li","aib-nav-tree-li","aib-nav-tree-li");
		$DisplayData["left_col"] = $TreeNavInfo["html"];
		$DisplayData["left_col"] .= "<br><br><div class='aib-selected-tree-items' id='aib-selected-tree-items'> </div>";
		aib_close_db();
	}

	// Set page title

	if ($UserType != AIB_USER_TYPE_ROOT)
	{
		$DisplayData["page_title"] = $UserRecord["user_login"]."/".$UserRecord["user_title"];
	}
	else
	{
		$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR";
	}
	$DisplayData["page_title"] .= ": ADD";
	

	switch($ParentFolderType)
	{
		case AIB_ITEM_TYPE_ITEM:
		case AIB_ITEM_TYPE_RECORD:
			$DisplayData["page_title"] .= " ITEM(S)";
			break;

		default:
			$DisplayData["page_title"] .= " RECORD";
			break;
	}

?>
<!---------------------------------------------------------------------------------------------------------------------------------->

		
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
		
		if(!(isset($_REQUEST['opcode']) and $_REQUEST['opcode']=='save')  ){  
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


<!------------------------------------------------------------------------------------------------------------------------------------->
<?php

		}
	// Define fields

	$FieldDef = array(
		"itemrecord_title" => array(
			"title" => "Record Name: <span class='aib-required-field-star'>*</span>", "type" => "text", "display_width" => "64",
			"field_name" => "itemrecord_title", "field_id" => "itemrecord_title",
			"desc" => "", "help_function_name" => "itemrecord_title_help_popup"),

		"itemrecord_visible" => array("title" => "Visible To Public?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "itemrecord_visible", "field_id" => "itemrecord_visible",
			"desc" => "If 'Yes', then the record can be seen by public users.", "help_function_name" => "itemrecord_visible_help_popup"),

		"itemrecord_private" => array("title" => "Private?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "itemrecord_private", "field_id" => "itemrecord_private",
			"desc" => "Private records are not published (anyone with a direct link can view this record)", "help_function_name" => "itemrecord_private_help_popup"),

		"file_handling" => array("title" => "Perform OCR?:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "file_handling", "field_id" => "file_handling",
			"desc" => "If selected, system will submit files for OCR processing",
			"help_function_name" => "file_handling",
			"value" => "NONE",
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
			"javascript" => "onclick='set_attach_opt_display(0);'",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_fileattach", "field_id" => "itemrecord_fileattachall",
			"desc" => "", "help_function_name" => "itemrecord_attachall_help_popup"),

		"itemrecord_attachind" => array(
			"title" => "Create individual records for each file (<i>all designated and pre-filled fields will appear in each record</i>)",
			"javascript" => "onclick='set_attach_opt_display(10);'",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_fileattach", "field_id" => "itemrecord_fileattachind",
			"desc" => "", "help_function_name" => "itemrecord_attachind_help_popup"),

		"itemrecord_attachind_userecname" => array(
			"title" => "Use Record Name with iteration numbers",
			"javascript" => "onclick='set_attach_opt_display(11);'",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_ind_which_name", "field_id" => "itemrecord_userecname",
			"desc" => "", "help_function_name" => false),

		"itemrecord_attachind_useorgname" => array(
			"title" => "Use the original file name as the Record Name for each record",
			"javascript" => "onclick='set_attach_opt_display(12);'",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_ind_which_name", "field_id" => "itemrecord_useorgname",
			"desc" => "", "help_function_name" => false),

		"itemrecord_attachall_userecname" => array(
			"title" => "Use Item Title with iteration numbers",
			"javascript" => "onclick='set_attach_opt_display(1);'",
			"left_title" => "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;",
			"type" => "radio", "display_width" => 0,
			"field_name" => "itemrecord_all_which_name", "field_id" => "itemrecord_all_use_title",
			"desc" => "", "help_function_name" => false),

		"itemrecord_attachall_useorgname" => array(
			"title" => "Use original file names for the Item Title",
			"javascript" => "onclick='set_attach_opt_display(2);'",
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

		"itemrecord_url_list" => array(
			"title" => "URL's For This Record:", "type" => "custom", "display_width" => "64",
			"field_name" => "itemrecord_url_list", "field_id" => "itemrecord_url_list",
			"input_table_class" => "aib-url-list-input-table",
			"desc" => ""),
		
		//<!------- SS Fix Start for Issue ID 2268 on 11-Aug-2023 ---->
		
		"itemrecord_address_line" => array(
			"title" => "Address :", "type" => "text", "display_width" => "64",
			"field_name" => "itemrecord_address_line", "field_id" => "itemrecord_address_line",
			
			"desc" => ""),
		
		"itemrecord_address_city" => array(
			"title" => "City:", "type" => "text", "display_width" => "64",
			"field_name" => "itemrecord_address_city", "field_id" => "itemrecord_address_city",
			
			"desc" => ""),
		
		"itemrecord_address_state" => array(
			"title" => "State:", "type" => "dropdown", "display_width" => "64",
			"field_name" => "itemrecord_address_state", "field_id" => "itemrecord_address_state",
			
			"desc" => ""),
		
		"itemrecord_address_pin_code" => array(
			"title" => "Zip Code:", "type" => "text", "display_width" => "64",
			"field_name" => "itemrecord_address_pin_code", "field_id" => "itemrecord_address_pin_code",
			
			"desc" => ""),
//<!------- SS Fix end for Issue ID 2268 on 11-Aug-2023 ---->
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

	// Alter field titles based on user type

	switch($UserType)
	{
		case AIB_USER_TYPE_PUBLIC:
		case AIB_USER_TYPE_USER:
			$FieldDef["file_handling"]["title"] = "Document Type:";
			$FieldDef["file_handling"]["desc"] = "";
			$FieldDef["file_handling"]["option_list"] = array("NONE" => "Photo", "OCR" => "Document");
			break;

		default:
			break;
	}

	// Create text indicating where this entry is being created

	aib_open_db();
	$ParentRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);
	$ParentTitle = aib_urldecode($ParentRecord["item_title"]);
	$ArchiveNameProp = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,"archive_name");
	$IDPathList = ftree_get_item_id_path($GLOBALS["aib_db"],$ParentFolderID);
	$ArchivesFolderID = ftree_get_object_by_path($GLOBALS["aib_db"],FTREE_OBJECT_TYPE_FOLDER.":ARCHIVE GROUP");
	aib_close_db();
	$FormID = "NULL";


	// Field area
if(!(isset($_REQUEST['opcode']) and $_REQUEST['opcode']=='save')  ){  
?>
	<tr>
		<td align='left' valign='top'>
			<?php
}
			$OutBuffer = array();
			$ErrorMessage = false;
			$StatusMessage = false;

			switch($OpCode)
			{
				// Save new record

				case "save":

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
					$PostData["_op"] = "addrecord";
					$PostData["_user"] = "1";
					$PostData["userdef_user_type"] = $UserType;
					$PostData["userdef_user_id"] = $UserID;
					$PostData["userdef_user_group"] = $UserGroup;
					$Result = aib_request($PostData,"recordop");
					//<!------- SS Fix Start for Issue ID 2268 on 11-Aug-2023 ---->
					if(trim($_REQUEST['itemrecord_address_line'])!='' || trim($_REQUEST['itemrecord_address_city'])!='' || trim($_REQUEST['itemrecord_address_state'])!='' || trim($_REQUEST['itemrecord_address_pin_code'])!='')
					{
						$address=trim($_REQUEST['itemrecord_address_line'])." ".trim($_REQUEST['itemrecord_address_city'])." ".trim($_REQUEST['itemrecord_address_state'])." ".trim($_REQUEST['itemrecord_address_pin_code']);
						$address=urlencode($address);
					$CurlObj = curl_init();
						
					$Options = array(
						
						CURLOPT_HEADER => 0,
						CURLOPT_URL =>  "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=". AIB_GOOGLE_MAP_KEY,
						CURLOPT_FRESH_CONNECT => 0,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_FORBID_REUSE => 0,
						CURLOPT_TIMEOUT => 300
					);

					curl_setopt_array($CurlObj,$Options);
					$ResultAdd = curl_exec($CurlObj);
						$addData=json_decode($ResultAdd);
						 $lng=$addData->results[0]->geometry->location->lng;	
						 $lat=$addData->results[0]->geometry->location->lat;	
					
					$SetItemPropParam["_key"] = APIKEY;
					$SetItemPropParam["_session"] = $SessionID;
					$SetItemPropParam["_op"] = "set_item_prop";
					$SetItemPropParam["_user"] = "1";
					$SetItemPropParam["obj_id"] =$Result['info']['records'][0]['msg']; 
					$SetItemPropParam["propname_1"] = 'itemrecord_address_line';
					$SetItemPropParam["propval_1"] = (!empty($_REQUEST['itemrecord_address_line'])) ? ($_REQUEST['itemrecord_address_line']) : '';
					$SetItemPropParam["propname_2"] = 'itemrecord_address_city';
					$SetItemPropParam["propval_2"] = (!empty($_REQUEST['itemrecord_address_city'])) ? ($_REQUEST['itemrecord_address_city']) : '';
					$SetItemPropParam["propname_3"] = 'itemrecord_address_state';
					$SetItemPropParam["propval_3"] = (!empty($_REQUEST['itemrecord_address_state'])) ? ($_REQUEST['itemrecord_address_state']) : '';
					$SetItemPropParam["propname_4"] = 'itemrecord_address_pin_code';
					$SetItemPropParam["propval_4"] = (!empty($_REQUEST['itemrecord_address_pin_code'])) ? ($_REQUEST['itemrecord_address_pin_code']) : '';
					$SetItemPropParam["propname_5"] = 'itemrecord_lat';
					$SetItemPropParam["propval_5"] = $lat;
					$SetItemPropParam["propname_6"] = 'itemrecord_lng';
					$SetItemPropParam["propval_6"] = $lng;
					$SetItemPropResponseAdd = aib_request($SetItemPropParam,"browse");
					
					$postDataPath = array(
						"_key" => APIKEY,
						"_session" => $SessionID,
						"_user" => 1,
						"_op" => "get_path",
						"obj_id" => $Result['info']['records'][0]['msg']
					);
					// Service request to get item tree data        
					$apiResponsePath = aib_request($postDataPath, 'browse');
						
					$society_id_context=0;
					if ($apiResponsePath['status'] == 'OK' and isset($apiResponsePath['info']['records'][1])) {
					   
						
						$society_id_context=$apiResponsePath['info']['records'][1]['item_id'];
					}	
					$SetItemLocParam["_key"] = APIKEY;
					$SetItemLocParam["_session"] = $SessionID;
					$SetItemLocParam["_op"] = "set";
					$SetItemLocParam["_user"] = "1";
					$SetItemLocParam["obj_id"] =$Result['info']['records'][0]['msg']; 
					$SetItemLocParam["lat"] = $lat;
					$SetItemLocParam["lon"] = $lng;
					$SetItemLocParam["alt"] =  '';
					if($society_id_context!=0){
						$SetItemLocParam["context"] = $society_id_context;
					}	
					$SetItemLocResponseAdd = aib_request($SetItemLocParam,"locationsearch");
	 /*echo '<pre>';print_R($SetItemLocParam );
						print_R($SetItemLocResponseAdd );die;*/
					}
					//<!------- SS Fix end for Issue ID 2268 on 11-Aug-2023 ---->
					//Fix start for Issue ID 2140 on 23-Feb-2023
					$GetPathParam["_key"] = APIKEY;
					$GetPathParam["_session"] = $SessionID;
					$GetPathParam["_op"] = "get_path";
					$GetPathParam["_user"] = "1";
					$GetPathParam["obj_id"] = $_REQUEST['parent'];
					$GetPathData = aib_request($GetPathParam,"browse");

					$archive_group_id = $GetPathData['info']['records'][1]['item_id'];
					$GetItemPropParam["_key"] = APIKEY;
					$GetItemPropParam["_session"] = $SessionID;
					$GetItemPropParam["_op"] = "get_item_prop";
					$GetItemPropParam["_user"] = "1";
					$GetItemPropParam["obj_id"] = $archive_group_id;
					$ItemProp = aib_request($GetItemPropParam,"browse");
					$ItemPropRecord = $ItemProp['info']['records'];
					
					$SetItemPropParam["_key"] = APIKEY;
					$SetItemPropParam["_session"] = $SessionID;
					$SetItemPropParam["_op"] = "set_item_prop";
					$SetItemPropParam["_user"] = "1";
					$SetItemPropParam["obj_id"] = $archive_group_id;
					$SetItemPropParam["propname_1"] = 'total_record';
					$SetItemPropParam["propval_1"] = (!empty($ItemPropRecord['total_record'])) ? ($ItemPropRecord['total_record'] + 1) : 1;
					$SetItemPropResponse = aib_request($SetItemPropParam,"browse");
					//Fix end for Issue ID 2140 on 23-Feb-2023
					//exit;
					// Disconnect from DB

					if (aib_close_db() == false)
					{
						$ErrorMessage = "Cannot close database";
					}
					
					header('location:manage_my_archive.php?folder_id='.$_REQUEST['parent']);
								exit;
					break;


				// Add new itemrecord

				case "add":
				case false:
				default:
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

					// Generate field selection display, grabbing any options for field display
					// passed to the form.

					$FieldDisplayOptions = array(
						"field_click_callback" => "field_button_callback",
//						"existing_fields" => $FieldsUsed,
						"archive" => $LocalArchive,
						"archive_group" => $LocalArchiveGroup
						);

					$FieldOptionList = array(
						"opt_show_system_fields","opt_show_recommended_fields",
						"opt_show_traditional_fields","opt_show_archive_group_fields",
						"opt_show_archive_fields","opt_show_user_fields",
						"opt_show_symbolic_fields");
					foreach($FieldOptionList as $FieldOptionName)
					{
						if (isset($FormData[$FieldOptionName]) == true)
						{
							$FieldDisplayOptions[$FieldOptionName] =
								$FormData[$FieldOptionName];
						}
					}

					// FIELD DISPLAY RIGHT SIDE
					// Generate field selection table to right of main form area

					// Get the field ID for the OCR text field so we can hide it from the field list

					$FieldDisplayOptions["opt_hide_field"] = array();
					$OCRFieldDef = ftree_get_field_by_symbolic_name($GLOBALS["aib_db"],AIB_PREDEF_FIELD_OCR_TEXT);
					if ($OCRFieldDef !== false)
					{
						$FieldDisplayOptions["opt_hide_field"][] = $OCRFieldDef["field_id"];
					}


					switch($UserType)
					{
						// Public and regular users have a fixed set of fields shown in the form display area

						case AIB_USER_TYPE_PUBLIC:
						case AIB_USER_TYPE_USER:
							$DisplayData["right_col"] = "";
							break;

						case AIB_USER_TYPE_ROOT:
							$FieldDisplayOptions["opt_show_system_fields"] = "Y";
							$FieldDisplayOptions["opt_show_traditional_fields"] = "Y";
							$FieldDisplayOptions["opt_show_recommended_fields"] = "Y";
							$FieldDisplayOptions["opt_show_archive_fields"] = "Y";
							$FieldDisplayOptions["opt_show_symbolic_fields"] = "Y";
							$FieldDisplayOptions["opt_show_user_fields"] = "Y";
							$RightColContentLines = array();
							$RightColContentLines[] = "<div class='aib-formfield-def-div'>";
							$RightColContentLines[] = aib_generate_field_table($GLOBALS["aib_db"],$UserID,$FieldDisplayOptions);
							$RightColContentLines[] = "<div class='clearitall'></div>";
							$RightColContentLines[] = "</div>";
							$DisplayData["right_col"] = join("\n",$RightColContentLines);
							break;

						case AIB_USER_TYPE_ADMIN:
							$FieldDisplayOptions["opt_show_system_fields"] = "Y";
							$FieldDisplayOptions["opt_show_traditional_fields"] = "Y";
							$FieldDisplayOptions["opt_show_recommended_fields"] = "Y";
							$FieldDisplayOptions["opt_show_archive_fields"] = "Y";
							$FieldDisplayOptions["opt_show_symbolic_fields"] = "Y";
							$FieldDisplayOptions["opt_show_user_fields"] = "Y";
							$RightColContentLines = array();
							$RightColContentLines[] = "<div class='aib-formfield-def-div'>";
							$RightColContentLines[] = aib_generate_field_table($GLOBALS["aib_db"],$UserID,$FieldDisplayOptions);
							$RightColContentLines[] = "<div class='clearitall'></div>";
							$RightColContentLines[] = "</div>";
							$DisplayData["right_col"] = join("\n",$RightColContentLines);
							break;

						case AIB_USER_TYPE_SUBADMIN:
							$FieldDisplayOptions["opt_show_system_fields"] = "Y";
							$FieldDisplayOptions["opt_show_traditional_fields"] = "Y";
							$FieldDisplayOptions["opt_show_recommended_fields"] = "Y";
							$FieldDisplayOptions["opt_show_archive_fields"] = "Y";
							$FieldDisplayOptions["opt_show_symbolic_fields"] = "Y";
							$FieldDisplayOptions["opt_show_user_fields"] = "Y";
							$RightColContentLines = array();
							$RightColContentLines[] = "<div class='aib-formfield-def-div'>";
							$RightColContentLines[] = aib_generate_field_table($GLOBALS["aib_db"],$UserID,$FieldDisplayOptions);
							$RightColContentLines[] = "<div class='clearitall'></div>";
							$RightColContentLines[] = "</div>";
							$DisplayData["right_col"] = join("\n",$RightColContentLines);
							break;

						default:
							$DisplayData["right_col"] = "";
							break;
					}

/*
					if ($UserType != AIB_USER_TYPE_PUBLIC && $UserType != AIB_USER_TYPE_USER)
					{
						if ($ParentFolderType != AIB_ITEM_TYPE_ITEM && $ParentFolderType != AIB_ITEM_TYPE_RECORD)
						{
							$RightColContentLines = array();
							$RightColContentLines[] = "<div class='aib-formfield-def-div'>";
							$RightColContentLines[] = aib_generate_field_table($GLOBALS["aib_db"],$UserID,$FieldDisplayOptions);
							$RightColContentLines[] = "<div class='clearitall'></div>";
							$RightColContentLines[] = "</div>";
		
							$DisplayData["right_col"] = join("\n",$RightColContentLines);
						}
					}
					else
					{
							$DisplayData["right_col"] = "";
					}
*/

					$OutBuffer[] = "<table width='100%' class='parent-folder-info-table'><tr class='parent-folder-info-table-row'><td class='parent-folder-info-title-cell' width='70%'>";
					switch($ParentFolderType)
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

					$OutBuffer[] = "</td><td class='parent-folder-info-link-cell' align='right'><a href=\"manage_my_archive.php?folder_id=".$_REQUEST['parent']."\">Return To List</a></td></tr>";
					$OutBuffer[] = "</table>";
					$OutBuffer[] = "<br><br>";
					$OutBuffer[] = aib_gen_form_header("pageform","add_record.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
//					$OutBuffer[] = "<input type='hidden' name='file_handling' value='NULL'>";
					$OutBuffer[] = "<input type='hidden' id='url_list_string' name='url_list_string' value=''>";
					$OutBuffer[] = "<input type='hidden' id='record_mode' name='record_mode' value='MFSRTITLE'>";
					$OutBuffer[] = "<input type='hidden' name='file_batch' value='".microtime(true)."'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";

					// Force opcode to "save"

					if ($ParentFolderType == AIB_ITEM_TYPE_ITEM || $ParentFolderType == AIB_ITEM_TYPE_RECORD)
					{
						$OutBuffer[] = "<input type='hidden' name='form_id' id='form_id' value='$FormID'>";
						$OutBuffer[] = "<input type='hidden' name='parent_list' id='parent_list' value='$ParentFolderID'>";
						$OutBuffer[] = "<input type='hidden' name='opcode' value='save_item'>";
					}
					else
					{
						$OutBuffer[] = "<input type='hidden' name='parent_list' id='parent_list' value=''>";
						$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
					}

					$OutBuffer[] = "<input type='hidden' name='user' value='$UserID'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
					$FieldDef["itemrecord_attachall"]["checked"] = "CHECKED";
					$FieldDef["itemrecord_attachall"]["value"] = "all";
					$FieldDef["itemrecord_attachind"]["value"] = "ind";
					break;
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
				array("src=records","parent=$ParentFolderID","srckey=$SourceKey","searchval=$SourceSearchValue","srcmode=$SourceMode","srcpn=$SourcePageNumber","archive_code=$ArchiveCode"));

			switch($OpCode)
			{
				case "add":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}
					// fix start for issue id 0002440 on 25-June-2025
					if ($UserType != AIB_USER_TYPE_USER && $UserType != AIB_USER_TYPE_PUBLIC)
					{
						$FieldDef["itemrecord_upload_field"]["fielddata"] = "
						<div class='upload-container'>
							<input id=\"fileupload\" type=\"file\" name=\"files[]\" data-url=\"../server/php/\" multiple >  <button type='button' onclick='trigger_upload()'id='aib_start_upload_button'>Start Uploads</button>
						</div>
						";
					}
					else
					{
						$FieldDef["itemrecord_upload_field"]["fielddata"] = "
						<div class='upload-container'>
							<input id=\"fileupload\" type=\"file\" name=\"files[]\" data-url=\"../server/php/\" multiple >  <button type='button' onclick='trigger_upload()'  id='aib_start_upload_button'>Attach Files</button>
						</div>
						";
					}
					// fix end for issue id 0002440 on 25-June-2025
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
					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 1: About Your Record</td></tr>";
					if ($ParentFolderType == AIB_ITEM_TYPE_ITEM || $ParentFolderType == AIB_ITEM_TYPE_RECORD)
					{
						$FieldDef["itemrecord_title"]["title"] = "Item Title:";
					}

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_alt_title"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["itemrecord_visible"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["itemrecord_private"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["file_handling"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_textarea_field($FieldDef["itemrecord_default_tags"]);
					$OutBuffer[] = aib_draw_input_row_separator();

					// Container for user-defined fields and forms


					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					if ($UserType != AIB_USER_TYPE_PUBLIC && $UserType != AIB_USER_TYPE_USER)
					{
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

						if ($ParentFolderType == AIB_ITEM_TYPE_ITEM || $ParentFolderType == AIB_ITEM_TYPE_RECORD)
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
					}
					else
					{
						$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 2: Additional Information About Your Record (Optional) </td></tr>";

						// Get field definitions for Description, Location, Date, Creator and "Additional Info"

						aib_open_db();
						$FieldDefs = array();
						$SymbolicFieldDefs = ftree_list_symbolic_fields($GLOBALS["aib_db"]);
						foreach($SymbolicFieldDefs as $FieldDefRecord)
						{
							switch($FieldDefRecord["field_symbolic_name"])
							{
								case AIB_PREDEF_FIELD_DESCRIPTION:
								case AIB_PREDEF_FIELD_LOCATION:
								case AIB_PREDEF_FIELD_DATE:
								case AIB_PREDEF_FIELD_CREATOR:
								case AIB_PREDEF_FIELD_INFOTEXT:
									$FieldDefs[] = $FieldDefRecord;
									break;

								default:
									break;
							}
						}
						//echo "<pre>"; print_r($FieldDefs); echo "</pre>";
//						$FieldDefs = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_SYSTEM,-2);
						
						
						$LocalMap = array();
						foreach($FieldDefs as $FieldDefRecord)
						{
							$LocalTitle = urldecode($FieldDefRecord["field_title"]);
							$LocalMap[$LocalTitle] = $FieldDefRecord;
						}
						//echo "<pre>"; print_r($LocalMap); echo "</pre>";
						$TempDefList = array();
						$FieldNameList = array("Description","Location","Date","Creator","Additional Info");
						//echo "<pre>"; print_r($FieldNameList); echo "</pre>";
						foreach($FieldNameList as $LocalName)
						{
							if (isset($LocalMap[$LocalName]) == true)
							{
								$TempDefList[] = array("def" => $LocalMap[$LocalName], "value" => "");
							}
						}
						//echo "<pre>"; print_r($TempDefList); echo "</pre>";
						$OutBuffer[] = aib_draw_user_def_field_area("itemrecord-user-def-fields",$TempDefList);
						aib_close_db();
					}

					$OutBuffer[] = aib_draw_input_row_separator();

					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 3: Define Record Characteristics </td></tr>";

					// Uploaded item attachment and processing options

					if ($ParentFolderType != AIB_ITEM_TYPE_RECORD && $ParentFolderType != AIB_ITEM_TYPE_ITEM)
					{
						$FieldDef["itemrecord_attachind_userecname"]["value"] = "rec";
//						$FieldDef["itemrecord_attachind_userecname"]["checked"] = "CHECKED";
						$FieldDef["itemrecord_attachind_userecname"]["checked"] = false;
						$FieldDef["itemrecord_attachind_useorgname"]["value"] = "org";

						$FieldDef["itemrecord_attachall_userecname"]["value"] = "rec";
						$FieldDef["itemrecord_attachall_userecname"]["checked"] = "CHECKED";
						$FieldDef["itemrecord_attachall_useorgname"]["value"] = "org";
						$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachind"]);
						$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachind_userecname"]);
						$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachind_useorgname"]);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachall"]);
						$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachall_userecname"]);
						$OutBuffer[] = aib_draw_radio_field($FieldDef["itemrecord_attachall_useorgname"]);
						$OutBuffer[] = aib_draw_input_row_separator();
					}

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
				
					
					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 4: Attach Files</td></tr>";

					// Upload field

					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_field"]);
					$OutBuffer[] = aib_draw_input_row_separator();

					// Upload progress display

					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_progress"]);

					// List of files being uploaded

					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_list"]);
					$OutBuffer[] = aib_draw_input_row_separator();

					// URL

//					if ($ParentFolderType != AIB_ITEM_TYPE_RECORD && $ParentFolderType != AIB_ITEM_TYPE_ITEM)
//					{
//						$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_default_url"]);
//						$OutBuffer[] = aib_draw_input_row_separator();
//					}

					// List of record URL's to be added as items

					if ($UserType != AIB_USER_TYPE_USER && $UserType != AIB_USER_TYPE_PUBLIC)
					{
						$FieldDef["itemrecord_url_list"]["fielddata"] = "<div class='aib-url-list-container'>
								<table width='100%'>
									<tr>
										<td width='100%'>
											<select id='url_list' name='itemrecord_default_url_list' class='aib-url-list' size='10'> </select>
										</td>
									</tr>
									<tr>
										<td>
											<button type='button' onclick='url_list_remove_entry()'>Remove Selected URL</button>
										</td>
									</tr>
									<tr>
										<td> &nbsp; </td>
									</tr>
									<tr>
										<td>
											Paste URL: <input id='itemrecord_temp_url' name='itemrecord_temp_url' class='aib-url-input' size='40'>
											&nbsp; &nbsp;
											<button type='button' onclick='url_list_add_entry()'>Add URL To List</button>
										</td>
									</tr>
								</table>
							</div>";
	
						$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_url_list"]);
						$OutBuffer[] = aib_draw_input_row_separator();
					}
					//<!------- SS Fix Start for Issue ID 2268 on 11-Aug-2023 ---->
					
					if($ObjectClass != AIB_ITEM_TYPE_ITEM ){
	$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 5: Location information</td></tr>";
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_address_line"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_address_city"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["itemrecord_address_state"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_address_pin_code"]);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = "";
					$OutBuffer[] = "";
					$OutBuffer[] = "<tr ><td  colspan='99'><div style='    padding-top: 5px;    padding-bottom: 25px;float: left;    padding-left: 132px;width:400px;'>Lat:<input style=\"width:100px;background-color: #ccc;\" type='text' readonly name='itemrecord_lat' id='itemrecord_lat' value=''>&nbsp;&nbsp;&nbsp;&nbsp;Long:<input style=\"width:100px;background-color: #ccc;\" type='text' readonly name='itemrecord_lng' id='itemrecord_lng' value=''></div><div style='float: left;width:200px;border: 1px solid;   padding: 5px;    width: 120px;    background: #15345a;    color: #fff;     margin-left: 15px;    ' id=\"getLocation\" onClick=\"getLocationData()\">Update Lat/Long</div></td></tr>";
					$OutBuffer[] = aib_draw_input_row_separator();
					}
					//<!------- SS Fix end for Issue ID 2268 on 11-Aug-2023 ---->
					switch($OpCode)
					{
						case "edit":
							$OutBuffer[] = aib_draw_form_submit("Save Changes","Undo Changes");
							break;

						default:
						case false:
							switch($UserType)
							{
								case AIB_USER_TYPE_PUBLIC:
								case AIB_USER_TYPE_USER:
									$OutBuffer[] = aib_draw_form_submit("Publish Files","Clear Form");
									break;

								default:
									$OutBuffer[] = aib_draw_form_submit("Add Record","Clear Form");
									break;
							}

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
					/* if($_SESSION['aib']['user_data']['user_type']=='U')
					{    $qString=base64_encode($_SERVER['QUERY_STRING']);
						?>
					    <input type="hidden" value="<?php echo $qString;?>">
						<a class="btn btn-admin borderRadiusNone marginLeft10" href="manage_forms.php?u=<?php echo $qString;?>">Create/Manage Templates</a>
						<a class="btn btn-admin borderRadiusNone marginLeft10" style="margin-top:5px;" href="manage_fields.php?u=<?php echo $qString;?>">Create/Manage Fields</a>
						<?php  } */
					?>
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
		var FileAttachAllState = false;
		var FileAttachAllTitle = true;
		var FileAttachAllOriginal = false;
		var FileAttachIndState = false;
		var FileAttachIndRecord = false;
		var FileAttachIndOriginal = false;
		var ChildCountMap = {};


		var InitCheckedDisplay = false;
		var cname='aib_page_id';
		var cvalue='1123';
		//var exday=60;
		 var d = new Date();
    d.setTime(d.getTime() + (60 * 24 * 60 * 60 * 1000));
    var expires = \"expires=\"+d.toUTCString();
    document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";
		
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

		// URL LIST FUNCTIONS
		// ==================

		// Remove selected entry

		function url_list_remove_entry()
		{
			var ElementObj = document.getElementById('url_list');
			ElementObj.remove(ElementObj.selectedIndex);
		}

		// Add entry from input field for URL text; requires HTTP or HTTPS prefix

		function url_list_add_entry()
		{
			var URLInputObj = document.getElementById('itemrecord_temp_url');
			var TempText = URLInputObj.value;
			var ElementObj = document.getElementById('url_list');
			var ElementOption = document.createElement('option');
			if (TempText.match(/^http:/) || TempText.match(/^https:/))
			{
				ElementOption.text = TempText;
				ElementObj.add(ElementOption);
				URLInputObj.value = '';
				URLInputObj.focus();
			}
			else
			{
				alert(\"You must enter a URL beginning with 'http://' or 'https://'\");
				URLInputObj.value = '';
				URLInputObj.focus();
			}
		}
");

		if ($UserType != AIB_USER_TYPE_USER && $UserType != AIB_USER_TYPE_PUBLIC)
		{
			print("
		function url_list_gather()
		{
			var ElementObj = document.getElementById('url_list');
			var OutText;

			OutText = '';
			for (var Counter = 0; Counter < ElementObj.options.length; Counter++)
			{
				if (Counter > 0)
				{
					OutText = OutText + '\t';
				}

				OutText = OutText + ElementObj.options[Counter].text;
			}

			var InputObj = document.getElementById('url_list_string');
			InputObj.value = OutText;
		}
");
		}
		else
		{
			print("
		function url_list_gather()
		{
		}
");
		}

		print("


		// TREE NAVIGATION FUNCTIONS
		// =========================

		function empty_child_callback(LocalEvent,RefObj,ItemID)
		{
			LocalEvent.preventDefault();
			LocalEvent.stopPropagation();
		}

		function empty_callback(LocalEvent,RefObj,ItemID)
		{
			LocalEvent.preventDefault();
			LocalEvent.stopPropagation();
		}

		// Fetch children for tree

		function fetch_tree_children(LocalEvent,RefObj,ItemID)
		{
			LocalEvent.stopPropagation();
			LocalEvent.preventDefault();

			var QueryParam = {};
			var ChildList;

			if (NavLoadedMap[ItemID] == undefined)
			{
				\$(RefObj).css('list-style-image',\"url('/images/button-open.png')\");
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
				if (ChildCountMap[ItemID] < 1)
				{
					\$(RefObj).css('list-style-image',\"url('/images/button-nochild.png')\");
					return;
				}
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

			ItemID = InData['info']['item_id'];
			ElementID = 'aib_navlist_entry_' + ItemID;
			if (InData['status'] != 'OK')
			{
				alert('ERROR PROCESSING CHILD REQUEST: ' + InData['info']['msg']);
				return;
			}

			if (InData['info']['html'] == '')
			{
				\$('#' + ElementID).css('list-style-image',\"url('/images/button-nochild.png')\");
				\$('#' + ElementID).append(InData['info']['html']);
				ChildCountMap[ItemID] = 0;
				return;
			}

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

		// RECORD OPTION DISPLAY
		// =====================
		function set_attach_opt_display(Mode)
		{
			var LocalFileAttachAll = document.getElementById('itemrecord_fileattachall');
			var LocalFileAttachAllUseTitle = document.getElementById('itemrecord_all_use_title');
			var LocalFileAttachAllUseOriginal = document.getElementById('itemrecord_all_use_original');
			var LocalFileAttachInd = document.getElementById('itemrecord_fileattachind');
			var LocalFileAttachIndRecord = document.getElementById('itemrecord_userecname');
			var LocalFileAttachIndOriginal = document.getElementById('itemrecord_useorgname');

			// Multiple files, single record (AttachAll)

			if (Mode == 0)
			{
				// Preserve state of AttachInd radio buttons

				FileAttachIndRecord = LocalFileAttachIndRecord.checked;
				FileAttachIndOriginal = LocalFileAttachIndOriginal.checked;

				// Clear the buttons and disable

				LocalFileAttachIndRecord.checked = false;
				LocalFileAttachIndOriginal.checked = false;
				LocalFileAttachInd.checked = false;
				LocalFileAttachAllUseTitle.disabled = false;
				LocalFileAttachAllUseOriginal.disabled = false;
				LocalFileAttachIndOriginal.disabled = true;
				LocalFileAttachIndRecord.disabled = true;

				// Set AttachAll radio button states

				if (FileAttachAllTitle == true)
				{
					LocalFileAttachAllUseTitle.checked = true;
					LocalFileAttachAllUseOriginal.checked = false;
					document.getElementById('record_mode').value = 'MFSRTITLE';
				}
				else
				{
					LocalFileAttachAllUseTitle.checked = false;
					LocalFileAttachAllUseOriginal.checked = true;
					document.getElementById('record_mode').value = 'MFSRORIG';
				}

				return;
			}

			if (Mode == 1)
			{
				document.getElementById('record_mode').value = 'MFSRTITLE';
				return;
			}

			if (Mode == 2)
			{
				document.getElementById('record_mode').value = 'MFSRORIG';
				return;
			}

			// Multiple records, single file (AttachInd)

			if (Mode == 10)
			{
				// Preserve state of AttachAll radio buttons

				FileAttachAllTitle = LocalFileAttachAllUseTitle.checked;
				FileAttachAllOriginal = LocalFileAttachAllUseOriginal.checked;

				// Clear the buttons and disable

				LocalFileAttachAll.checked = false;
				LocalFileAttachAllUseTitle.checked = false;
				LocalFileAttachAllUseOriginal.checked = false;
				LocalFileAttachAllUseTitle.disabled = true;
				LocalFileAttachAllUseOriginal.disabled = true;
				LocalFileAttachIndOriginal.disabled = false;
				LocalFileAttachIndRecord.disabled = false;

				// Set AttachInd radio buttons

				if (FileAttachIndRecord == true)
				{
					LocalFileAttachIndRecord.checked = true;
					LocalFileAttachIndOriginal.checked = false;
					document.getElementById('record_mode').value = 'MRSFREC';
				}
				else
				{
					LocalFileAttachIndRecord.checked = false;
					LocalFileAttachIndOriginal.checked = true;
					document.getElementById('record_mode').value = 'MRSFORG';
				}

				return;
			}

			if (Mode == 11)
			{
				document.getElementById('record_mode').value = 'MRSFREC';
				return;
			}

			if (Mode == 12)
			{
				document.getElementById('record_mode').value = 'MRSFORG';
				return;
			}
		}
	");

	// Create a string containing a list of all the subgroups/records where the new data
	// is to be stored.  This is passed to the back end queue for later processing.

	if ($ParentFolderType != AIB_ITEM_TYPE_ITEM && $ParentFolderType != AIB_ITEM_TYPE_RECORD)
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
			 url_list_gather();
			return(true);
			
		}
		");
	}
	else
	{
		print("
		function post_process_form()
		{
			url_list_gather();
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
		//<!------- SS Fix Start for Issue ID 2268 on 11-Aug-2023 ---->
		var parent_id = '<?php echo STATE_PARENT_ID; ?>';
       
        $.ajax({
            url: "<?php echo AIB_SERVICE_FILE_PATH;?>services.php",
            type: "post",
            data: {mode: 'get_state_country', parent_id: parent_id},
            success: function (response) {
                var record = JSON.parse(response);
                var i;
                var state = "";
                state += "<option value='' >---Select---</option>";
                for (i = 0; i < record.length; i++) {
                    var data_value = '';
                    
                    state += "<option value='" + record[i] + "'  " + data_value + " >" + record[i] + "</option>";
                }
                $("#itemrecord_address_state").html(state);
                $('.loading-div').hide();

            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 343)');
                $('.loading-div').hide();
            }
        });
		
		//<!------- SS Fix end for Issue ID 2268 on 11-Aug-2023 ---->
		
		
	});
//<!------- SS Fix Start for Issue ID 2268 on 11-Aug-2023 ---->	
function getLocationData(){
	
	var itemrecord_address_line=$.trim($('#itemrecord_address_line').val());
	var itemrecord_address_city=$.trim($('#itemrecord_address_city').val());
	var itemrecord_address_state=$.trim($('#itemrecord_address_state').val());
	var itemrecord_address_pin_code=$.trim($('#itemrecord_address_pin_code').val());
	
	if(itemrecord_address_line=='' || itemrecord_address_city=='' || itemrecord_address_state=='' || itemrecord_address_pin_code=='' ){
		
		alert('Please fill location information.');
		return false;
	}
	
	var address=itemrecord_address_line+" "+itemrecord_address_city+" zipcode "+itemrecord_address_pin_code+" "+itemrecord_address_state;
	address=encodeURIComponent(address);
	
	 $.ajax({
            url: "https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&key=<?php echo AIB_GOOGLE_MAP_KEY;?>",
            type: "get",
           
            success: function (response) {
              
				$('#itemrecord_lat').attr('value',response.results[0].geometry.location.lat);
				$('#itemrecord_lng').attr('value',response.results[0].geometry.location.lng);
               

            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: #1)');
                
            }
        });
}	
	//<!------- SS Fix end for Issue ID 2268 on 11-Aug-2023 ---->
	// fix start for issue id 0002440 on 25-June-2025
	// fix start for issue id 0002491 on 08-July-2025
	$('#fileupload').on('change', function () {
		const forbiddenTypes = ['video/', 'audio/'];
		const maxSizeInBytes = 1073741824; // 1 GB

		const files = this.files;
		let hasInvalidFile = false;
		let hasLargeFile = false;

		for (const file of files) {
			if (forbiddenTypes.some(type => file.type.startsWith(type))) {
				hasInvalidFile = true;
				break;
			}
			if (file.size > maxSizeInBytes) {
				hasLargeFile = true;
				break;
			}
		}

		if (hasInvalidFile) {
			alert('One or more files are video/audio formats, which are not allowed.');
			this.value = ''; // Clear selection
		} else if (hasLargeFile) {
			alert('One or more files exceed the maximum allowed size of 1 GB.');
			this.value = ''; // Clear selection
		}
	});
	// fix end for issue id 0002491 on 08-July-2025
	// fix end for issue id 0002440 on 25-June-2025
</script>
<?php include_once COMMON_TEMPLATE_PATH.'record_footer.php'; ?>  
</body>
</html>
