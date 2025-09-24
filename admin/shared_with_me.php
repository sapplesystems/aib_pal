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
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Archive</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Shared With Me </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        
        
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
            <input type="hidden" name="archive_group_id" id="archive_group_id" value="<?php echo $archive_group_id; ?>">
            <div id="data-listing-section" class="col-md-12 tableStyle" hidden="">
          </div>
            <div id="scrapbook-data-listing-section" class="col-md-12 tableStyle" >
          </div>
        </div>
    </section>
    <!-- Modal -->
</div>



<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        var folder_id = '';
        $('#data-listing-section').show();
        getItemDetailsById(folder_id);
    });
    function getItemDetailsById(folder_id) {
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'shared_record_list', share_type:'shared_to_user',folder_id: folder_id},
            success: function (result) {
                $('#data-listing-section').html(result);
                $('.admin-loading-image').hide();
            },
            error: function () {
                $('.admin-loading-image').hide();
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 634)');
            }
        });
    }
    $(document).on('click', '.getItemDataByFolderId', function () {
        var folder_id = $(this).attr('data-folder-id');
        getItemDetailsById(folder_id);
    });
    $(document).on('click', '.delete_listing_item', function(){
        var ref_item_id  = $(this).attr('data-field-delete-id');
        var item_id      = $(this).attr('data-item-id');
        var removed_user = $(this).attr('data-removed-user');
        if(ref_item_id){
            if(confirm('Are you sure you want to delete this? This cannot be undone')){
                $('.admin-loading-image').show();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'delete_shared_record', ref_item_id: ref_item_id, delete_type: 'share_with_me',user_to_remove: removed_user, item_id: item_id},
                    success: function (result) {
                        var record = JSON.parse(result);
                        if(record.status == 'success'){
                            getItemDetailsById('');
                            showPopupMessage('success', record.message);
                        }else{
                            showPopupMessage('error', record.message + ' (Error Code: 635)');
                            $('.admin-loading-image').hide();
                        } 
                    },
                    error: function () {
                        $('.admin-loading-image').hide();
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 636)');
                    }
                });
            }
        }
    });
  
     
</script>
