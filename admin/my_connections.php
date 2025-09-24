<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
	header('Location: login.php');
	exit;
}
if (isset($_REQUEST['folder_id']) && $_REQUEST['folder_id'] != '') {
	$folder_id = $_REQUEST['folder_id'];
} else {
	$folder_id = $_SESSION['aib']['user_data']['user_top_folder'];
}
$archive_group_id = '';
if ($_SESSION['aib']['user_data']['user_type'] == 'A') {
	$archive_group_id = $_SESSION['aib']['user_data']['user_top_folder'];
}
$previous = '';
if (isset($_REQUEST['previous']) && $_REQUEST['previous'] != '') {
	$previous = $_REQUEST['previous'];
}
if (isset($_REQUEST['return_data']) && $_REQUEST['return_data'] != '') {
	$_SESSION['aib']['return_data'] = $_REQUEST['return_data'];
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Connections</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Connections</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">My Connections </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
            <input type="hidden" name="archive_group_id" id="archive_group_id" value="<?php echo $archive_group_id; ?>">
            <div id="data-listing-section" class="col-md-12 tableStyle">

            </div>
        </div>
    </section>
    <!-- Modal -->
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
	$(document).ready(function () {
		var folder_id = '<?php echo $folder_id; ?>';
		var previous_id = '<?php echo $previous; ?>';
		var previousObj = [];
		if (previous_id != '') {
			previousObj = previous_id.split(',');
		}
		$('#current-item-id').val(folder_id);
		$('#previous-item-id').val(JSON.stringify(previousObj));
		getItemDetailsById(folder_id, '');
		$("#create_new_items_form").validate({
			rules: {
				item_code: "required",
				item_title: "required"
			},
			messages: {
				item_code: "Code is required",
				item_title: "Title is required"
			}
		});

<?php if (isset($_REQUEST['folder-id']) and trim($_REQUEST['folder-id']) != '') { ?>
			getItemDetailsById('<?php echo trim($_REQUEST['folder-id']); ?>', '');
<?php } ?>
	});
	function getItemDetailsById(id, record_edit_link = '') {
		if (id) {
			$('.admin-loading-image').show();
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'admin_historical_connections', folder_id: id, record_edit_link: record_edit_link},
				success: function (result) {
					$('#data-listing-section').html(result);
					$('.admin-loading-image').hide();
<?php if ($_SESSION['aib']['user_data']['user_type'] == 'U' || $_SESSION['aib']['user_data']['user_type'] == 'A') { ?>
						var parent_name = $('#heading_listing li:nth-child(2)').attr("data-folder-name");
<?php } else { ?>
						var parent_name = $('#heading_listing li:nth-child(3)').attr("data-folder-name");
<?php } ?>
					if (typeof parent_name == "undefined") {
						$('.headingNameDesign').html('');
					} else {
						$('.headingNameDesign').html(parent_name);
					}

				},
				error: function () {
					$('.admin-loading-image').hide();
					showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 576)');
				}
			});
	}
	}
	$(document).on('click', '.delete_listing_item', function () {
		if (confirm("Are you sure to delete this? This cannot be undone")) {
			var item_id = $(this).attr('data-field-delete-id');
			var parent_id = $('#current-item-id').val();
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'delete_historical_connection', item_id: item_id},
				success: function (data) {
					var result = JSON.parse(data);
					if (result.status == 'success') {
						getItemDetailsById(parent_id);
					} else {
						showPopupMessage('error', 'Something went wrong! Please try again. (Error Code: 577)');
						return false;
					}
				},
				error: function () {
					showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 578)');
				}
			});
		}
	});

	$(document).on('click', '.block_historical_connection', function () {
                var status = $(this).attr('block_connection');
                var message= "Are you sure to block this?";
                if(status === 'false'){
                    message= "Are you sure to unblock this?";
                }
		if (confirm(message)) {
			var item_id = $(this).attr('data-field-block-id');
			var block_connection = $(this).attr('block_connection');
			var parent_id = $('#current-item-id').val();
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'block_historical_connection', item_id: item_id, block_connection: block_connection},
				success: function (data) {
					var result = JSON.parse(data);
					if (result.status == 'success') {
                                            getItemDetailsById(parent_id);
					} else {
                                            showPopupMessage('error', 'Something went wrong! Please try again. (Error Code: 579)');
                                            return false;
					}
				},
				error: function () {
					showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 580)');
				}
			});
		}
	});
	$(document).on("change", "#admin_historical_connections", function () {
//		alert($('#admin_historical_connections').val());
//	$('#admin_historical_connections').change(function () {
		$('.admin-loading-image').show();
		$.ajax({
			url: "services_admin_api.php",
			type: "post",
			data: {mode: 'admin_historical_connections', perspective: $('#admin_historical_connections').val()},
			success: function (result) {
				$('#data-listing-section').html(result);
				$('.admin-loading-image').hide();
<?php if ($_SESSION['aib']['user_data']['user_type'] == 'U' || $_SESSION['aib']['user_data']['user_type'] == 'A') { ?>
					var parent_name = $('#heading_listing li:nth-child(2)').attr("data-folder-name");
<?php } else { ?>
					var parent_name = $('#heading_listing li:nth-child(3)').attr("data-folder-name");
<?php } ?>
				if (typeof parent_name == "undefined") {
					$('.headingNameDesign').html('');
				} else {
					$('.headingNameDesign').html(parent_name);
				}

			},
			error: function () {
				$('.admin-loading-image').hide();
				showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 581)');
			}
		});

//alert($('#admin_historical_connections').val());
	});
</script>
