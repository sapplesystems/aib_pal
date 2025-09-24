<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
$folder_id = '';
if(isset($_SESSION['aib']['user_data']['user_top_folder']) && !empty($_SESSION['aib']['user_data']['user_top_folder'])){
$folder_id = $_SESSION['aib']['user_data']['user_top_folder'];
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
            <li class="active">Shared With Me</li>
        </ol>
       <h4 class="list_title text-center"><span class="pull-left">Shared With Me </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <table id="scrapbook_listing" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>
                            <th width="30%">Scrapbook Title </th>
                            <th width="20%">Shared By </th>
                            <th width="20%">Shared Date </th>
                            <th width="30%" class="text-center">Actions</th>  
                        </tr>  
                     </thead>  
                    <tbody id="scrapbook_data_section"></tbody>  
                </table>
            </div>
        </div>
    </section>
    <!-- Modal -->
</div>



<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dataTableDiv").hide();
        var folder_id = <?php echo $folder_id; ?>;
        getItemDetailsById(folder_id);
    });
	 var table = $('#scrapbook_listing').DataTable({"pageLength": 100});
    function getItemDetailsById(folder_id) {
          $('#data-listing-section').show();
        
         var image_path = '<?php echo IMAGE_PATH; ?>';
         var HOST_PATH = '<?php echo HOST_PATH; ?>';
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'share_record_scrapbook_list', share_type:'shared_to_user',folder_id: folder_id},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    table.row.add([
                        '<a href="javascript:void(0)">'+record[i].item_title+'</a>',
                        record[i].link_owner_title,
                        record[i].link_create_date,
                        '<span data-field-delete-id=""><a target="_blank" href="'+HOST_PATH+'people.php?folder_id='+record[i].item_id+'"><img title="View" src="' + image_path + 'view.png" alt=""></a></span><span data-title="Delete" class="delete_scrapbook" item_id=' + record[i].item_id + ' ref_id=' + record[i].link_id + '><img src="' + image_path + 'delete_icon.png" alt="" title="Delete scrapbook" />'
                    ]).draw(false);
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                $('.admin-loading-image').hide();
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 283)');
            }
        });
    }
    $(document).on('click', '.delete_scrapbook', function(){
        var folder_id = <?php echo $folder_id; ?>;
        var item_id      = $(this).attr('item_id');
        var ref_id = $(this).attr('ref_id');
        if(ref_id){
              if(confirm('Are you sure you want to delete this? This cannot be undone')){
                $('.admin-loading-image').show();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'delete_shared_scrapbook',
                        delete_type: 'share_with_me',item_id: item_id,ref_id:ref_id},
                    success: function (result) {
                        var record = JSON.parse(result);
                        if(record.status == 'success'){
                            showPopupMessage('success', record.message);
                            $('.admin-loading-image').hide();
                            location.reload();
                        }else{
                            showPopupMessage('error', record.message + ' (Error Code: 284)');
                            $('.admin-loading-image').hide();
                        } 
                    },
                    error: function () {
                        $('.admin-loading-image').hide();
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 285)');
                    }
                });
            } 
          }
    });
  
     
</script>
