<?php
include('../config/aib.php');
include("../include/folder_tree.php");
include("../include/fields.php");
include('../include/aib_util.php');
include('../include/import.php');
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

// Given a CSV file, get the column names
// --------------------------------------
function extract_csv_column_names($CSVFileName)
{
	
	// Get the list of column names
	
	$ColumnNameList = import_field_names_from_csv($CSVFileName);
	if ($ColumnNameList == false)
	{
		return(false);
	}

	if (count($ColumnNameList) < 1)
	{
		return(false);
	}

	return($ColumnNameList);
}



// Detect format of import:  Either "generic" or a known export format from another system
// ---------------------------------------------------------------------------------------
function detect_import_type($FileBatchID)
{
	// Get the first ZIP in the batch

	$FirstZIP = find_first_zip_in_file_batch($FileBatchID);
	if ($FirstZIP == false)
	{
record_form_debug("Can't find zip in batch");
		return(false);
	}

	// Create file names

	$SourceZIPName = AIB_RECORD_FILE_UPLOAD_PATH."/".$FirstZIP;
	$ZIPFileName = AIB_IMPORT_FILE_PATH."/".$FirstZIP;
	$TempCSVFileName = "/tmp/import_csv_temp_".sprintf("%d",posix_getpid()).".csv";

	// Get CSV file to temporary

	if (get_csv_from_zip($SourceZIPName,$TempCSVFileName) == false)
	{
record_form_debug("Can't find csv in batch");
		return(false);
	}

	// Get column names

	$CSVColumns = extract_csv_column_names($TempCSVFileName);

	// Get first few rows (records) from CSV.  If we can't read any, error.

	$CSVRows = get_csv_lines_for_detect($TempCSVFileName);
	system("rm -f \"$TempCSVFileName\" > /dev/null 2> /dev/null");

	if ($CSVRows == false)
	{
record_form_debug("Can't open CSV file");
		return(false);
	}

	if (count($CSVRows) < 1)
	{
record_form_debug("No csv rows");
		return(false);
	}

	// If the CSV columns have the PastPerfect titles, then we can be reasonably certain this is a PP export.  That said, we need to read
	// a few rows of the CSV to make sure....

	$ColMap = array();
	$ColCounter = 0;
	$MatchCount = 0;
	foreach($CSVColumns as $FieldName)
	{
		if (array_search($FieldName,$GLOBALS["aib_import"]["import_cols_pp"]) !== false)
		{
			$ColMap["$ColCounter"] = $FieldName;
			$MatchCount++;
		}

		$ColCounter++;
	}

	if ($MatchCount == count($GLOBALS["aib_import"]["import_cols_pp"]))
	{
		// Columns match.  Read a few lines of the CSV to verify based
		// on data format

		// Check non-blank lines for data format.

		$RowMatch = 0;
		$RowsFetched = count($CSVRows);
		while(count($CSVRows) > 0)
		{
			$Row = array_pop($CSVRows);
			$ColNum = 0;
			$MatchSuccess = 0;
			foreach($Row as $ColValue)
			{
				if (isset($ColMap["$ColNum"]) == false)
				{
					$ColNum++;
					continue;
				}

				$ColName = $ColMap["$ColNum"];
				switch($ColName)
				{
					case "ACCESSNO":
						if (preg_match("/^[0-9]+[\.][0-9]+/",$ColValue) == false && ltrim(rtrim($ColValue)) != "")
						{
record_form_debug("ACCESSNO match fail: $ColValue");
							break;
						}

						$MatchSuccess++;
						break;

					case "OBJECTID":
						if (preg_match("/^[0-9]+[\.][0-9]+[\.][0-9]+/",$ColValue) == false &&
							preg_match("/^[0-9]+[\.][0-9]+/",$ColValue) == false && ltrim(rtrim($ColValue)) != "")
						{
record_form_debug("OBJECTID match fail: $ColValue");
							break;
						}

						$MatchSuccess++;
						break;

					case "IMAGEFILE":
						if (preg_match("/[0-9]+[\.][A-Za-z]+$/",$ColValue) == false && ltrim(rtrim($ColValue)) != "")
						{
record_form_debug("IMAGEFILE match fail: $ColValue");
							break;
						}

						// Check file name extension

						$Segs = explode(".",$ColValue);
						$Extension = array_pop($Segs);
						foreach($GLOBALS["aib_import"]["import_ext_pp"] as $Pattern)
						{
							if (preg_match("/".$Pattern."/",$Extension) != false)
							{
								$MatchSuccess++;
								break;
							}
						}

						break;

					case "IMAGENO":
						if (preg_match("/[0-9]+/",$ColValue) == false && ltrim(rtrim($ColValue)) != "")
						{
record_form_debug("IMAGENO match fail: $ColValue");
							break;
						}

						$MatchSuccess++;
						break;

					default:
						break;
				}

				$ColNum++;
			}

			if ($MatchSuccess >= 4)
			{
				$RowMatch++;
			}
		}

		// If at least 70% of the rows matched the expected pattern, then assume this is PastPerfect.

		if ($RowMatch / $RowsFetched > 0.7)
		{
			system("rm -f \"$TempCSVFileName\" > /dev/null 2> /dev/null");
			return("pp");
		}
record_form_debug("Not enough matching rows for pp, so generic instead");
	}
	else
	{
record_form_debug("Not enough matching columns for pp, so generic instead");
	}

	// Otherwise, assume "generic"

	system("rm -f \"$TempCSVFileName\" > /dev/null 2> /dev/null");
	return("generic");

}


// Debug
// -----
function record_form_debug($Msg)
{
	$Handle = fopen("/tmp/import_map_fields_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,date("Y-m-d H:i:s")." -- ".$Msg."\n");
		fclose($Handle);
	}
}

function map_fields_debug($Msg)
{
	$Handle = fopen("/tmp/import_map_fields_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,date("Y-m-d H:i:s")." -- ".$Msg."\n");
		fclose($Handle);
	}
}


function record_form_debug_display($Msg)
{
	print("<head><title>DEBUG</title></head>\n");
	print("<body><center><h1>$Msg</h1></center></body>\n");
}

function record_form_error_display($Title,$Msg,$URL = false)
{
	$OutTextLines = array();
	$OutTextLines[] = "<tr><td colspan='99' align='center'><font size='5' face='arial,helvetica,universe'><b>$Msg</b></font></td></tr>";
	$OutTextLines[] = "<tr><td colspan='99'> &nbsp; </td></tr>";
	if ($URL !== false)
	{
		$OutTextLines[] = "<tr><td colspan='99' align='center'><font size='4' face='arial,helvetica,universe'><a href='$URL'>Click Here To Continue</a></font></td></tr>";
		$OutTextLines[] = "<tr><td colspan='99'> &nbsp; </td></tr>";
	}

	return(join("\n",$OutTextLines));

}

function find_first_zip_in_file_batch($FileBatchID)
{
	// Get the name of the first ZIP file uploaded (all others are ignored)

	aib_open_db();
	$FileList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatchID);
	if ($FileList == false)
	{
		$FileList = array();
	}

	$FirstZIP = false;

	// Get first ZIP

	foreach($FileList as $FileRecord)
	{
		$FileName = urldecode($FileRecord["file_name"]);
		if (preg_match("/[\.][Zz][Ii][Pp]$/",$FileName) == false)
		{
			continue;
		}

		$FirstZIP = $FileName;
		break;
	}

	if ($FirstZIP == false)
	{
		aib_close_db();
		return(false);
	}

	return($FirstZIP);
}

function get_column_name_list($FileBatchID)
{
	// Get the name of the first ZIP file uploaded (all others are ignored)

	aib_open_db();
	$FileList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatchID);
	if ($FileList == false)
	{
		$FileList = array();
	}

	$FirstZIP = false;

	// Get first ZIP

	foreach($FileList as $FileRecord)
	{
		$FileName = urldecode($FileRecord["file_name"]);
		if (preg_match("/[\.][Zz][Ii][Pp]$/",$FileName) == false)
		{
			continue;
		}

		$FirstZIP = $FileName;
		break;
	}

	if ($FirstZIP == false)
	{
		aib_close_db();
		return(false);
	}

	$TempCSVFileName = "/tmp/import_csv_temp_".sprintf("%d",posix_getpid()).".csv";
	$SourceZIPName = AIB_RECORD_FILE_UPLOAD_PATH."/".$FirstZIP;

	// Get the list of files in the zip

	$ZIPFileList = import_list_zip($SourceZIPName);
	$FirstCSV = false;
	foreach($ZIPFileList as $ArchivedFileName)
	{
		if (preg_match("/[\.][Cc][Ss][Vv]$/",$ArchivedFileName) != false)
		{
			$FirstCSV = $ArchivedFileName;
			break;
		}
	}

	if ($FirstCSV == false)
	{
		aib_close_db();
		return(false);
	}
	
	// Extract the CSV to temporary file

	$ExtractResult = import_list_extract_zip_file($SourceZIPName,$FirstCSV,$TempCSVFileName);
	if ($ExtractResult == false)
	{
		aib_close_db();
		return(false);
	}

	// Get the list of column names

	$ColumnNameList = import_field_names_from_csv($TempCSVFileName);
		aib_close_db();
	system("rm -f \"$TempCSVFileName]\" > /dev/null 2> /dev/null");
	return($ColumnNameList);
}

// Extract CSV from ZIP to temp file
// ---------------------------------
function get_csv_from_zip($SourceZIPName,$TargetCSVFileName)
{

	// Get the list of files in the zip

	$ZIPFileList = import_list_zip($SourceZIPName);
	$FirstCSV = false;
	foreach($ZIPFileList as $ArchivedFileName)
	{
		if (preg_match("/[\.][Cc][Ss][Vv]$/",$ArchivedFileName) != false)
		{
			$FirstCSV = $ArchivedFileName;
			break;
		}
	}

	if ($FirstCSV == false)
	{
		aib_close_db();
		return(false);
	}
	
	// Extract the CSV to temporary file

	$ExtractResult = import_list_extract_zip_file($SourceZIPName,$FirstCSV,$TargetCSVFileName);
	if ($ExtractResult == false)
	{
		aib_close_db();
		return(false);
	}

	return(true);
}


// Get the first few lines of the CSV file
// ---------------------------------------
function get_csv_lines_for_detect($TempCSVFileName)
{
	// Load the first few lines, discarding the first line as the column names

	$Handle = fopen($TempCSVFileName,"r");
	if ($Handle == false)
	{
		return(false);
	}

	$LineList = array();
	$FirstRow = true;
	for ($Counter = 0; $Counter < 10; $Counter++)
	{
		$Row = fgetcsv($Handle);
		if ($Row == false)
		{
			break;
		}

		if ($FirstRow == true)
		{
			$FirstRow = false;
			continue;
		}

		$LineList[] = $Row;
	}

	fclose($Handle);
	return($LineList);
}

// #########
// MAIN CODE
// #########

	$GLOBALS["aib_import"] = array();

	// Create list of expected PastPerfect column names.  If these are all matched,
	// assumption is that it's a PP file.

	$GLOBALS["aib_import"]["import_cols_pp"] = array(
		"ACCESSNO","OBJECTID","IMAGEFILE","IMAGENO");

	// Create list of acceptable PastPerfect image extension patterns.

	$GLOBALS["aib_import"]["import_ext_pp"] = array(
		"[Jj][Pp][Gg]","[Jj][Pp][Ee][Gg]","[Pp][Nn][Gg]","[Tt][Ii][Ff]+","[Gg][Ii][Ff]",
		"[Pp][Dd][Ff]",
		);

session_start();

if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
if(isset($_REQUEST['folder_id']) && $_REQUEST['folder_id']!= ''){
    $folder_id = $_REQUEST['folder_id'];
}else{ 
    $folder_id = 148;
}
$previous = '';
if(isset($_REQUEST['previous']) && $_REQUEST['previous'] != ''){
    $previous = $_REQUEST['previous'];
}
include_once 'config/config.php';


include_once COMMON_TEMPLATE_PATH.'header.php';
    include_once COMMON_TEMPLATE_PATH.'sidebar.php';
  

	if ($_REQUEST["opcode"] == "save")
	{
		$TitleString = "IMPORT PROCESSING SCHEDULED.  You may return to managing your records while the system works.";
	}
	else
	{
		$TitleString = "Import Records -- Select Import Fields";
	}

?>
<div class="content-wrapper">
        <section class="content-header">
            <h1>My Archive</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active"><?php echo $TitleString;?></li>
            </ol>
           <h4 class="list_title"><?php echo $TitleString;?> </h4>
        </section>
        <section class="content bgTexture">
        
<div class="content2">

<?php

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
	$ParentFolderID = aib_get_with_default($FormData,"parent",false);
	$FileBatchID = aib_get_with_default($FormData,"file_batch",false);
	$VisibilityFlag = aib_get_with_default($FormData,"itemrecord_visible","Y");
	$PrivacyFlag = aib_get_with_default($FormData,"itemrecord_private","N");
	$TagString = aib_get_with_default($FormData,"itemrecord_default_tags","");

	// Get the current archive using the parent folder

	aib_open_db();
	$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolderID);
	aib_close_db();
	$ArchiveID = $ArchiveInfo["archive"]["item_id"];
	$ArchiveGroupID = $ArchiveInfo["archive_group"]["item_id"];

	// Set up display data array

	$DisplayData = array(
		"page_title" => "SYSTEM ADMINISTRATOR: SELECT IMPORT FIELDS",
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

	$EmbeddedSession = aib_get_session_key();




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
//		document.getElementById('itemrecord_userecname').disabled = true;
//		document.getElementById('itemrecord_useorgname').disabled = true;
	});
	//alert(userType+"=="+forUserType);
	
	var FileUploadCounter = 0;
	var FileUploadCurrent = -1;
	var UploadDataSet = {};
	var ImportParentFolder = "<?php echo $ParentFolderID;?>";
	var EmbeddedSession = "<?php echo $EmbeddedSession;?>";
	var FileBatch = "<?php echo $FileBatchID;?>";

	// Load the set of fields for a fieldmap

	function load_field_map()
	{
		var SelectedMapName;
		SelectedMapName = $('#import_map_select').val();
		$.ajax({
			type: 'POST',
			url: '/services/import_services.php',
			data: {
				o: 'loadmap',
				s: EmbeddedSession,
				i: '1',
				file_batch: FileBatch,
				parent_folder: ImportParentFolder,
				map_name: SelectedMapName
			},

			success: function(Response) {
				$('#userfields').html(Response);
				}
		});
	}

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

// Set up uploader.  Allow only one ZIP file.

$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
	disablePreview: true,
	sequentialUploads: true,
//	disableExifThumbnail: true,
//	previewThumbnail: false,
//	acceptFileTypes: /(\.|\/)(zip|ZIP)$/1,
//	maxNumberOfFiles: 1,

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
else
{
?>
<link rel="stylesheet" href="../css/aib.css">
		<script type='text/javascript' src='../jquery-3.2.0.min.js'> </script>
		<script type='text/javascript' src='../js/aib.js'> </script>
<script src="../js/vendor/jquery.ui.widget.js"></script>
<script src="../js/jquery.iframe-transport.js"></script>
<script src="../js/jquery.fileupload.js"></script>
</script>
<?php
}

	// Get parent folder ID

	$ParentFolderID = aib_get_with_default($FormData,"parent","");

	// Get setup info from DB

	aib_open_db();

	// Get parent folder type

	$ParentFolderType = ftree_get_property($GLOBALS["aib_db"],$ParentFolderID,AIB_FOLDER_PROPERTY_FOLDER_TYPE);
	$ParentFolderRecord = ftree_get_item($GLOBALS["aib_db"],$ParentFolderID);

	// Get archive and archive group

	$ArchiveInfo = aib_get_archive_and_archive_group($GLOBALS["aib_db"],$ParentFolderID);
	$ParentArchive = $ArchiveInfo["archive"]["item_id"];
	$ParentArchiveGroup = $ArchiveInfo["archive_group"]["item_id"];

	// Get the set of available import mappings for the archive

	$ImportMapList = import_list_mappings($GLOBALS["aib_db"],$ParentArchive);
	aib_close_db();
	if ($ParentFolderType === false)
	{
		$ParentFolderType = AIB_ITEM_TYPE_ITEM;
	}

	// If in "add" mode, include the checkbox tree in the left column if we're not adding items to an existing record.  The
	// "left_col" data is substituted into the template on display.

	// Set page title

	if ($UserType != AIB_USER_TYPE_ROOT)
	{
		$DisplayData["page_title"] = $UserRecord["user_login"]."/".$UserRecord["user_title"];
	}
	else
	{
		$DisplayData["page_title"] = "SYSTEM ADMINISTRATOR";
	}

	if ($OpCode == "save")
	{
		$DisplayData["page_title"] .= ": IMPORT PROCESSING SCHEDULED.  You may return to managing your records while the system works.";
	}
	else
	{
		$DisplayData["page_title"] .= ": SELECT IMPORT FIELDS";
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

		"import_map_select" => array("title" => "Select Field Map:", "type" => "dropdown", "display_width" => "20",
			"field_name" => "import_map_select", "field_id" => "import_map_select",
			"desc" => "To use a previous field map, select from list", "help_function_name" => "import_map_select"),

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

	);

	$MapOptionList = array("NULL" => " -- DON'T USE AN EXISTING MAP --");
	aib_open_db();
	$MapList = import_list_mappings($GLOBALS["aib_db"],$ParentArchiveGroup);
	aib_close_db();
	foreach($MapList as $MappingRecord)
	{
		$MapOptionList[$MappingRecord["title"]] = $MappingRecord["title"];
	}

	$FieldDef["import_map_select"]["option_list"] = $MapOptionList;
	$FieldDef["import_map_select"]["value"] = "NULL";
	$FieldDef["import_map_select"]["script"] = "onchange=\"load_field_map();\"";

	// Define field validations for later code generation

	$ValidationDef = array(
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
//if(!(isset($_REQUEST['opcode']) and $_REQUEST['opcode']=='save')  ){  
?>
	<tr>
		<td align='left' valign='top'>
			<?php
//}
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

					// Get session ID

					$SessionID = aib_get_session_key();

					// Do API import call

					$PostData = $FormData;
					$PostData["_key"] = APIKEY;
					$PostData["_session"] = $SessionID;
					$PostData["_op"] = "import";
					$PostData["_user"] = "1";
					$PostData["userdef_user_type"] = $UserType;
					$PostData["userdef_user_id"] = $UserID;
					$PostData["userdef_user_group"] = $UserGroup;
					$Result = aib_request($PostData,"import");
					$OutBuffer[] = "<table width='100%' class='parent-folder-info-table'><tr class='parent-folder-info-table-row'><td class='parent-folder-info-title-cell'>";
					$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'>Processing Has Been Schedule For Files Imported To:  <b>$ParentTitle</b></span></div>";
					$OutBuffer[] = "</td></tr>";
					$OutBuffer[] = "<tr><td colspan='99'><br><br></td></tr>";
					$OutBuffer[] = "<tr class='parent-folder-info-table-row'>";
					$OutBuffer[] = "<td colspan='99' class='parent-folder-info-link-cell' align='left'><a href=\"manage_my_archive.php?folder_id=".$_REQUEST['parent']."\">Return To List</a></td>";
					$OutBuffer[] = "</tr>";
					$OutBuffer[] = "</table>";
					$OutBuffer[] = "<br><br>";
					$OutBuffer[] = aib_gen_form_header("pageform","import_map_fields.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='file_handling' value='NULL'>";
					$OutBuffer[] = "<input type='hidden' id='url_list_string' name='url_list_string' value=''>";
					$OutBuffer[] = "<input type='hidden' id='record_mode' name='record_mode' value='MFSRTITLE'>";
					$OutBuffer[] = "<input type='hidden' name='file_batch' value='$FileBatchID'>";
					$OutBuffer[] = "<input type='hidden' name='itemrecord_visible' value='$VisibilityFlag'>";
					$OutBuffer[] = "<input type='hidden' name='itemrecord_private' value='$PrivacyFlag'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";

					// Force opcode to "save"

					$OutBuffer[] = "<input type='hidden' name='parent_list' id='parent_list' value=''>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value=''>";
					$OutBuffer[] = "<input type='hidden' name='user' value='$UserID'>";
					$OutBuffer[] = "<table class='aib-input-set'>";
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

					$FileBatchID = aib_get_with_default($FormData,"file_batch",false);
					if ($FileBatchID == false)
					{
						$FileBatchID = microtime(true);
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
							$DisplayData["right_col"] = "";
							break;

						case AIB_USER_TYPE_ADMIN:
							$FieldDisplayOptions["opt_show_system_fields"] = "Y";
							$FieldDisplayOptions["opt_show_traditional_fields"] = "Y";
							$FieldDisplayOptions["opt_show_recommended_fields"] = "Y";
							$FieldDisplayOptions["opt_show_archive_fields"] = "Y";
							$FieldDisplayOptions["opt_show_symbolic_fields"] = "Y";
							$FieldDisplayOptions["opt_show_user_fields"] = "Y";
							$DisplayData["right_col"] = "";
							break;

						case AIB_USER_TYPE_SUBADMIN:
							$FieldDisplayOptions["opt_show_system_fields"] = "Y";
							$FieldDisplayOptions["opt_show_traditional_fields"] = "Y";
							$FieldDisplayOptions["opt_show_recommended_fields"] = "Y";
							$FieldDisplayOptions["opt_show_archive_fields"] = "Y";
							$FieldDisplayOptions["opt_show_symbolic_fields"] = "Y";
							$FieldDisplayOptions["opt_show_user_fields"] = "Y";
							$DisplayData["right_col"] = "";
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
					$OutBuffer[] = "<div class='aib-parent-folder-title'><span class='aib-parent-folder-title-span'>You Are Selecting Fields For Files Imported To:  <b>$ParentTitle</b></span></div>";
					$OutBuffer[] = "</td><td class='parent-folder-info-link-cell' align='right'><a href=\"manage_my_archive.php?folder_id=".$_REQUEST['parent']."\">Abandon Import</a></td></tr>";
					$OutBuffer[] = "</table>";
					$OutBuffer[] = "<br><br>";
					$OutBuffer[] = aib_gen_form_header("pageform","import_map_fields.php",false,"validate_form");
					$OutBuffer[] = "<input type='hidden' name='license' value='".sprintf("%08x",time())."'>";
					$OutBuffer[] = "<input type='hidden' name='file_handling' value='NULL'>";
					$OutBuffer[] = "<input type='hidden' id='url_list_string' name='url_list_string' value=''>";
					$OutBuffer[] = "<input type='hidden' id='record_mode' name='record_mode' value='MFSRTITLE'>";
					$OutBuffer[] = "<input type='hidden' name='file_batch' value='$FileBatchID'>";
					$OutBuffer[] = "<input type='hidden' name='itemrecord_visible' value='$VisibilityFlag'>";
					$OutBuffer[] = "<input type='hidden' name='itemrecord_private' value='$PrivacyFlag'>";
					$OutBuffer[] = "<input type='hidden' name='parent' value='".aib_get_with_default($FormData,"parent","-1")."'>";
					$OutBuffer[] = "<input type='hidden' name='archive_code' value='".aib_get_with_default($FormData,"archive_code","-1")."'>";

					// Force opcode to "save"

					$OutBuffer[] = "<input type='hidden' name='parent_list' id='parent_list' value=''>";
					$OutBuffer[] = "<input type='hidden' name='opcode' value='save'>";
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
				case "map":
				case "edit":
				case false:
					if ($ErrorMessage != false)
					{
						break;
					}

					// Primary (fixed) fields

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 1: About Your Record</td></tr>";

					// Show list of saved field mappings

					$FieldSpec = array(
						"title" => " ",
						"type" => "text",
						"display_width" => 64,
						"field_name" => "field_source_map_title",
						"field_id" => "field_source_map_title",
						"desc" => "",
						"help_function_name" => "",
						"fielddata" => "<b>Load Existing Field Map (OPTIONAL)</b>",
						);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_custom_field($FieldSpec);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_dropdown_field($FieldDef["import_map_select"]);


//					$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_title"]);

					$OutBuffer[] = aib_draw_input_row_separator();

//					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 3: Define Record Characteristics </td></tr>";

					// Uploaded item attachment and processing options
/*
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
*/
					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
//					$OutBuffer[] = "<tr class='aib-form-section-title-row'><td class='aib-form-section-title-cell' colspan='99'>Step 4: Attach Files</td></tr>";

					// Upload field

//					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_field"]);
//					$OutBuffer[] = aib_draw_input_row_separator();

					// Upload progress display

//					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_progress"]);

					// List of files being uploaded

//					$OutBuffer[] = aib_draw_custom_field($FieldDef["itemrecord_upload_list"]);
//					$OutBuffer[] = aib_draw_input_row_separator();

					// URL

//					if ($ParentFolderType != AIB_ITEM_TYPE_RECORD && $ParentFolderType != AIB_ITEM_TYPE_ITEM)
//					{
//						$OutBuffer[] = aib_draw_input_field($FieldDef["itemrecord_default_url"]);
//						$OutBuffer[] = aib_draw_input_row_separator();
//					}

					// List of record URL's to be added as items
/*
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
*/

					// Select source column for title and field name

					$FileBatchID = aib_get_with_default($FormData,"file_batch",false);
					if ($FileBatchID !== false)
					{
						$LocalColNameList = get_column_name_list($FileBatchID);
					}
					else
					{
						$LocalColNameList = array();
					}

					$TitleSourceSpec = array("NULL" => " -- SELECT -- ");

					// See if this is a generic or specific type of import

					$ImportType = detect_import_type($FileBatchID);

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$ImportTypeTitle = "General Import Archive Format";
					switch($ImportType)
					{
						case "pp":
							$ImportTypeTitle = "PastPerfect Archive Format";
							break;

						default:
							break;

					}

					$FieldSpec = array(
						"title" => "Archive Format:",
						"type" => "text",
						"display_width" => 64,
						"field_name" => "name_format",
						"field_id" => "name_format",
						"desc" => "",
						"help_function_name" => "",
						"fielddata" => "<b>$ImportTypeTitle</b>",
						);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_custom_field($FieldSpec);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$FieldSpec = array(
						"title" => " ",
						"type" => "text",
						"display_width" => 64,
						"field_name" => "name_sources",
						"field_id" => "name_sources",
						"desc" => "",
						"help_function_name" => "",
						"fielddata" => "<b>Choose Columns Containing Record Title And Image File Name</b>",
						);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_custom_field($FieldSpec);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
	
					// Determine default title field source

					$ColCounter = 1;
					$SelectedColName = false;
					foreach($LocalColNameList as $LocalColName)
					{
						$TitleSourceSpec["$ColCounter"] = $LocalColName;
						if (preg_match("/[Tt][Ii][Tt][Ll][Ee]/",$LocalColName) != false || preg_match("/[Nn][Aa][Mm][Ee]/",$LocalColName) != false ||
							preg_match("/[Cc][Aa][Pp][Tt][Ii][Oo][Nn]/",$LocalColName) != false)
						{
							if ($SelectedColName === false)
							{
								$SelectedColName = $ColCounter;
							}
						}

						$ColCounter++;
					}

					$FieldSpec = array(
						"title" => "Column With Title:",
						"type" => "dropdown",
						"display_width" => 64,
						"field_name" => "title_source",
						"field_id" => "title_source",
						"desc" => "Database column containing record titles",
						"help_function_name" => "",
						"option_list" => $TitleSourceSpec,
						"value" => "",
						);

					if ($SelectedColName !== false)
					{
						$FieldSpec["value"] = $SelectedColName;
					}

					// Determine default image file field if not a known import type, else set specifically.

					if ($ImportType != "pp")
					{
						$TitleSourceSpec = array("NULL" => "-- SELECT -- ");
						$TagSourceSpec = array("NULL" => " -- SELECT -- ");
						$OutBuffer[] = aib_draw_dropdown_field($FieldSpec);
						$OutBuffer[] = aib_draw_input_row_separator();
						$ColCounter = 1;
						$SelectedColName = false;
						foreach($LocalColNameList as $LocalColName)
						{
							if (preg_match("/[Ff][Ii][Ll][Ee]/",$LocalColName) != false || preg_match("/[Ii][Mm][Aa][Ge]/",$LocalColName) != false ||
								preg_match("/[Ii][Mm][Gg]/",$LocalColName) != false || preg_match("/[Pp][Ii][Cc]/",$LocalColName) != false)
							{
								if ($SelectedColName === false)
								{
									$SelectedColName = $ColCounter;
								}
							}
	
							$TitleSourceSpec["$ColCounter"] = $LocalColName;
							$ColCounter++;
						}
					}
					else
					{
						$TitleSourceSpec = array("NULL" => "-- SELECT -- ");
						$TagSourceSpec = array("NULL" => " -- SELECT -- ");
						$OutBuffer[] = aib_draw_dropdown_field($FieldSpec);
						$OutBuffer[] = aib_draw_input_row_separator();
						$ColCounter = 1;
						$SelectedColName = false;
						foreach($LocalColNameList as $LocalColName)
						{
							if ($LocalColName == "IMAGEFILE")
							{
								if ($SelectedColName === false)
								{
									$SelectedColName = $ColCounter;
								}
							}
	
							$TitleSourceSpec["$ColCounter"] = $LocalColName;
							$ColCounter++;
						}
					}
	
					$FieldSpec = array(
						"title" => "Column With File Name:",
						"type" => "dropdown",
						"display_width" => 64,
						"field_name" => "filename_source",
						"field_id" => "filename_source",
						"desc" => "Database column containing file names",
						"help_function_name" => "",
						"option_list" => $TitleSourceSpec,
						"value" => "",
						);

					if ($SelectedColName !== false)
					{
						$FieldSpec["value"] = $SelectedColName;
					}

					// Determine default tags source field

					$OutBuffer[] = aib_draw_dropdown_field($FieldSpec);
					$OutBuffer[] = aib_draw_input_row_separator();
					$ColCounter = 1;
					$SelectedColName = false;
					$TagSourceSpec = array("NULL" => " -- SELECT -- ");
					foreach($LocalColNameList as $LocalColName)
					{
						if (preg_match("/[Tt][Aa][Gg]/",$LocalColName) != false || preg_match("/[Ww][Oo][Rr][Dd]/",$LocalColName) != false ||
							preg_match("/[Cc][Oo][Mm]+[Ee][Nn][Tt]/",$LocalColName) != false ||
							preg_match("/[Kk][Ee][Yy][Ww][Oo][Rr][Dd]/",$LocalColName) != false)
						{
							if ($SelectedColName === false)
							{
								$SelectedColName = $ColCounter;
							}
						}

						$TagSourceSpec["$ColCounter"] = $LocalColName;
						$ColCounter++;
					}

					$FieldSpec = array(
						"title" => "Column With Tags:",
						"type" => "dropdown",
						"display_width" => 64,
						"field_name" => "tag_source",
						"field_id" => "tag_source",
						"desc" => "OPTIONAL: Database column containing tag values",
						"help_function_name" => "",
						"option_list" => $TitleSourceSpec,
						"value" => "",
						);

					if ($SelectedColName !== false)
					{
						$FieldSpec["value"] = $SelectedColName;
					}

					$OutBuffer[] = aib_draw_dropdown_field($FieldSpec);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();


					// Show list of CSV columns and associated fields

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";

					// Get the file batch

					while(true)
					{

						$FileBatchID = aib_get_with_default($FormData,"file_batch",false);
						if ($FileBatchID == false)
						{
							$OutBuffer[] = record_form_error_display("ERROR: NO FILES UPLOADED","No files uploaded",false);
							break;
						}
	
						// Get the name of the first ZIP file uploaded (all others are ignored)
	
						aib_open_db();
						$FileList = aib_get_upload_batch_queue($GLOBALS["aib_db"],$FileBatchID);
						if ($FileList == false)
						{
							$FileList = array();
						}
	
						$FirstZIP = false;
	
						// Get first ZIP
	
						foreach($FileList as $FileRecord)
						{
							$FileName = urldecode($FileRecord["file_name"]);
							if (preg_match("/[\.][Zz][Ii][Pp]$/",$FileName) == false)
							{
								continue;
							}
	
							$FirstZIP = $FileName;
							break;
						}
	
						if ($FirstZIP == false)
						{
							$OutBuffer[] = record_form_error_display("ERROR: NO ARCHIVE FILE","No ZIP archive uploaded",false);
							break;
						}
	
						$TempCSVFileName = "/tmp/import_csv_temp_".sprintf("%d",posix_getpid()).".csv";
						$SourceZIPName = AIB_RECORD_FILE_UPLOAD_PATH."/".$FirstZIP;
	
						// Get the list of files in the zip

						$ZIPFileList = import_list_zip($SourceZIPName);
						$FileListSize = count($ZIPFileList);
						$FirstCSV = false;
						foreach($ZIPFileList as $ArchivedFileName)
						{
							if (preg_match("/[\.][Cc][Ss][Vv]$/",$ArchivedFileName) != false)
							{
								$FirstCSV = $ArchivedFileName;
								break;
							}
						}
	
						if ($FirstCSV == false)
						{
							$OutBuffer[] = record_form_error_display("ERROR: NO DATABASE FILE","No database (CSV) file available in \"$FirstZIP\" ($FileListSize files in ZIP archive $SourceZIPName) ",false);
							break;
						}
	
						// Extract the CSV to temporary file
	
						$ExtractResult = import_list_extract_zip_file($SourceZIPName,$FirstCSV,$TempCSVFileName);
						if ($ExtractResult == false)
						{
							$OutBuffer[] = record_form_error_display("ERROR: CANNOT EXTRACT DATABASE FILE","Cannot extract database file \"$FirstCSV\" from \"$FirstZIP\" to \"$TempCSVFileName\" ",false);
							break;
						}
	
						// Get the list of column names
	
						$ColumnNameList = import_field_names_from_csv($TempCSVFileName);
						if ($ColumnNameList == false)
						{
							$OutBuffer[] = record_form_error_display("ERROR: CANNOT FIND COLUMN NAMES","Cannot find column names in database file",false);
							break;
						}

						if (count($ColumnNameList) < 1)
						{
							$OutBuffer[] = record_form_error_display("ERROR: NO COLUMN NAMES","There are no column names in database file",false);
							break;
						}

						// Get the list of fields for the archive and archive group

						$ArchiveGroupFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveGroupID,true);
						$ArchiveFieldList = ftree_list_fields($GLOBALS["aib_db"],false,FTREE_OWNER_TYPE_ITEM,$ArchiveID,true);
						$ArchiveTitle = aib_urldecode($ArchiveInfo["archive"]["item_title"]);
						$ArchiveGroupTitle = aib_urldecode($ArchiveInfo["archive_group"]["item_title"]);

						// Show each column on a line, with drop-down for fields available for archive group and/or archive

						$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
						$OutBuffer[] = aib_draw_input_row_separator();
						$FieldSpec = array(
							"title" => "",
							"type" => "text",
							"display_width" => 64,
							"field_name" => "assign_col_title",
							"field_id" => "assign_col_title",
							"desc" => "",
							"help_function_name" => "",
							"fielddata" => "<b>Copy Database Columns To AIB Fields</b>",
							);
						$OutBuffer[] = aib_draw_custom_field($FieldSpec);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_input_row_separator();
						$FieldSpec = array(
							"title" => "<b>Source Column</b> &nbsp;",
							"type" => "text",
							"display_width" => 64,
							"field_name" => "target_field_for_col_".$ColNum,
							"field_id" => "target_field_for_col_".$ColNum,
							"desc" => "",
							"help_function_name" => "",
							"fielddata" => "&nbsp; <b>Destination AIB Field</b>",
							);
						$OutBuffer[] = aib_draw_custom_field($FieldSpec);
						$OutBuffer[] = aib_draw_input_row_separator();
						$OutBuffer[] = aib_draw_input_row_separator();
						$SubBuffer = array();
						$ColNum = 1;
						foreach($ColumnNameList as $ColName)
						{
							if (ltrim(rtrim($ColName)) == "")
							{
								continue;
							}

							$SelectName = "target_field_for_col_".$ColNum;
							$FieldSpecTextArray = array("<select name='$SelectName' id='$SelectName'>");
							$FieldSpecTextArray[] = "<option value='IGNORE' SELECTED>Don't Use This Column</option>";
							$FieldSpecTextArray[] = "<option value='ADD'>Add Column As A New AIB Field</option>";
							if (count($ArchiveGroupFieldList) > 0)
							{
								foreach($ArchiveGroupFieldList as $TempRecord)
								{
									$FieldID = $TempRecord["field_id"];
									$FieldTitle = aib_urldecode($TempRecord["field_title"]);
									$FieldSpecTextArray[] = "<option value='$FieldID'>$ArchiveGroupTitle:  $FieldTitle</option>";
								}
							}

							if (count($ArchiveFieldList) > 0)
							{
								foreach($ArchiveFieldList as $TempRecord)
								{
									$FieldID = $TempRecord["field_id"];
									$FieldTitle = aib_urldecode($TempRecord["field_title"]);
									$FieldSpecTextArray[] = "<option value='$FieldID'>$ArchiveTitle:  $FieldTitle</option>";
								}
							}

							$FieldSpecTextArray[] = "</select>";
							$FieldSpecText = join("\n",$FieldSpecTextArray);
							$FieldSpec = array(
								"title" => "# ".$ColNum." ($ColName): ",
								"type" => "text",
								"display_width" => 64,
								"field_name" => "target_field_for_col_".$ColNum,
								"field_id" => "target_field_for_col_".$ColNum,
								"desc" => "",
								"help_function_name" => "",
								"fielddata" => $FieldSpecText,
								);
							$SubBuffer[] = aib_draw_custom_field($FieldSpec);
							$SubBuffer[] = aib_draw_input_row_separator();
							$ColNum++;
						}


						$TempLocalBuffer = "<div id='userfields'><table class='aib-input-set'>".join("\n",$SubBuffer)."</table></div>";
						$OutBuffer[] = aib_draw_status_message($TempLocalBuffer);

						break;
					}

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = aib_draw_input_row_separator();

					// Show the checkbox and input field for saving the mapping

					$FieldSpec = array(
						"title" => " ",
						"type" => "text",
						"display_width" => 64,
						"field_name" => "save_mapping_title",
						"field_id" => "save_mapping_title",
						"desc" => "",
						"help_function_name" => "",
						"fielddata" => "<b>Save Field Selections As A New Field Mapping (OPTIONAL)</b>",
						);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_custom_field($FieldSpec);
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();
					$FieldSpecText = "<input type='text' name='aib_mapping_name' id='aib_mapping_name' size='64' width='64'>";
					$FieldSpec = array(
						"title" => "Save Field Mapping As:",
						"type" => "text",
						"display_width" => 64,
						"field_name" => "aib_save_mapping",
						"field_id" => "aib_save_mapping",
						"desc" => "Enter a title to save this field mapping for re-use",
						"help_function_name" => "",
						"fielddata" => $FieldSpecText,
						);
					$OutBuffer[] = aib_draw_custom_field($FieldSpec);

					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = aib_draw_input_row_separator();

					// Additional hidden fields

					$TempList = array(
						"<input type='hidden' name='archive_type' value='$ImportType'>",
						);
					$FieldSpecText = join("",$TempList);
					$FieldSpec = array(
						"title" => "",
						"type" => "text",
						"display_width" => 0,
						"field_name" => "hidden_set_two",
						"field_id" => "hidden_set_two",
						"desc" => "",
						"help_function_name" => "",
						"fielddata" => $FieldSpecText,
						);
					$OutBuffer[] = aib_draw_custom_field($FieldSpec);
					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-section-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = aib_draw_input_row_separator();

					// Submit button

					aib_close_db();

					$OutBuffer[] = aib_draw_form_submit("Submit Import For Processing","Reset Form");

					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";
					break;

				case "save":
					if ($ErrorMessage != false)
					{
						break;
					}

					// Primary (fixed) fields

					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = aib_draw_input_row_separator();
					$OutBuffer[] = "<tr class='aib-form-section-row'><td cyylass='aib-form-blank-cell' colspan='99'> </td></tr>";
					$OutBuffer[] = "<tr class='aib-form-section-row'><td class='aib-form-blank-cell' colspan='99'> </td></tr>";

					aib_close_db();
					$OutBuffer[] = "</table>";
					$OutBuffer[] = "</form>";
					break;

				default:
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

	print("
				var CheckedTreeItems = {};
	");

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


		var InitCheckedDisplay = false;
		var cname='aib_page_id';
		var cvalue='1123';
		//var exday=60;
		 var d = new Date();
    d.setTime(d.getTime() + (60 * 24 * 60 * 60 * 1000));
    var expires = \"expires=\"+d.toUTCString();
    document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";

	function post_process_form()
	{
		if (\$('#filename_source').val() == 'NULL')
		{
			alert('You must select a source database column for image file names');
			return(false);
		}

		return(true);
		
	}

	</script>
		");
?>

</div></section></div>
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
<!--<footer class="main-footer">
    <div class="pull-right hidden-xs"></div>
    <div class="text-center"><span class="topMargin20"><strong>Copyright &copy; <?php echo date('Y'); ?> </strong>  All rights reserved. "ArchiveInABox" and box device is a registered trademark of SmallTownPapers, Inc.</span>
				<ul class="socialIcons">
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'fb.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'twitter.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'pinterest.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'linkedIn.png'; ?>"></a></li>
                </ul>
				</div>
				<div class="clearfix"></div>
	</footer>-->
<div class="footer">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-md-2 col-sm-2 centerText"><img src="<?php echo IMAGE_PATH . 'logo.png'; ?>" alt="" /></div>
            <div class="col-md-8 text-center footerText col-sm-10">Copyright © 2017. All rights reserved. "ArchiveInABox" and box device is a registered trademark of SmallTownPapers, Inc. <br> <span> <a href="../terms_condition.php" class="term-of-use foot_ancher" target="_blank">Terms of Use </a> | <a href="../privacy_cookies.php" class="privacy-cookies foot_ancher" target="_blank"> Privacy & cookies </a> | <a href="../dmca.php" class="dmca foot_ancher" target="_blank">Dmca</a> | <a href="../dmca_counter_notice.php" class="privacy-cookies foot_ancher" target="_blank"> Dmca counter notice </a> </span></div>
            <div class="col-md-2 col-sm-12 centerText">
                <ul class="socialIcons">
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'fb.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'twitter.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'pinterest.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'linkedIn.png'; ?>"></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
    <?php foreach($jsArray as $key=>$fileName){ ?>
        <script src="<?php echo JS_PATH.$fileName; ?>"></script>
    <?php } ?>
     
    <script type="text/javascript">
	 $(document).on('click', '.resume-session', function () {
        var user_id = $(this).attr('resume-user-id');
        if (user_id) {
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'resume_user_session', user_id: user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'success') {
                        window.location.href = 'manage_my_archive.php';
                    }
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    </script>
        
</body>
</html>
