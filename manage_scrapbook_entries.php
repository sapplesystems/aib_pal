<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
$scrapbook_id ='';
$scrapbook_ref = '';
if(isset($_REQUEST['scrapbook_ref']) && $_REQUEST['scrapbook_ref'] !='' && is_numeric($_REQUEST['scrapbook_ref'])){
    $scrapbook_ref = $_REQUEST['scrapbook_ref'];
}
if(isset($_REQUEST['scrapbook_id']) && $_REQUEST['scrapbook_id'] !='' && is_numeric($_REQUEST['scrapbook_id'])){
    $scrapbook_id = $_REQUEST['scrapbook_id'];
}else{
    header('Location: manage_scrapbook.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Scrapbook Entries</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Scrapbook</li>
        </ol>
        <h4 class="list_title">Manage Scrapbook Entries <a class="backtoLink pull-right" href="manage_scrapbook.php"><img src="<?php echo IMAGE_PATH ?>back-to-search.png" alt="Back To Scrapbook"> Back To Scrapbook</a></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div id="data-listing-section" class="col-md-12 tableStyle">
                
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        var scrapbook_id = '<?php echo $scrapbook_id; ?>';
        getAllScrapbookEntries(scrapbook_id);
    });
    
    $(document).on('click', '.delete_item_from_scrapbook', function(){
        var scrapbook_item_id = $(this).attr('data-field-delete-id');
        var scrapbook_id = '<?php echo $scrapbook_id; ?>';
        if(confirm("Are you sure to delete the entry from scrapbook? This cannot be undone")){
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_entry_from_scrapbook', scrapbook_item_id: scrapbook_item_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    $('.admin-loading-image').hide();
                    showPopupMessage(record.status,record.message);
                    if(record.status == 'success'){
                        setTimeout(function(){
                            getAllScrapbookEntries(scrapbook_id);
                        },1000);
                    }
                },
                error: function () {
                    $('.admin-loading-image').hide();
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 232)');
                }
            });
        }
    });
    
    function getAllScrapbookEntries(scrapbook_id){
        if(scrapbook_id){
            var scrapbook_ref = '<?php echo $scrapbook_ref; ?>';
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'list_scrapbook_entries', scrapbook_id: scrapbook_id, scrapbook_ref: scrapbook_ref},
                success: function (response) {
                    $('#data-listing-section').html(response);
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 233)');
                }
            });
        }
    }
</script>