<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Share Link</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Public Connections With Me</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Public Connections With Me</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-12 tableStyle">
                <div class="tableScroll">
                    <table id="sharelink_container" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                        <thead>  
                            <tr>  
                                <!--<th width="30%" class="text-center">Word or Phrase</th>--> 
                                <th width="40%">Share Links</th>
                                <th width="30%">Shared Date</th>
                                <th width="30%" class="text-center">Actions</th>
                            </tr>  
                        </thead>  
                        <tbody id="user_notifier_listing_section"></tbody>  
                    </table> 
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        getShareLinkListing();
    });
    var sharelink_container = $('#sharelink_container').DataTable({"pageLength": 100});
    function getShareLinkListing(){
        var image_path ='<?php echo IMAGE_PATH; ?>';
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'get_user_share_with_me_listing'},
            success: function (response) {
                sharelink_container.clear().draw();
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                        sharelink_container.row.add([
                        record[i].itemTitle,
                        record[i].create_date_time,
			'<span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id='+record[i].item_parent+'"><img src="'+image_path+'view.png" alt="" /></a></span><span title="Unlink" class="unlink_share_conection"  data-link-id=' +record[i].item_id + ' ><button>Unlink</button></span>'
                  ]).draw(false);
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 326)');
            } 
        });
    }
    
  
	$(document).on('click', '.unlink_share_conection', function(){  
		var obj_id = $(this).attr('data-link-id');
//                alert(obj_id)
		 $.ajax({
			type: 'POST',
			url: 'services_admin_api.php',
			data: {mode:'unlink_share_link' ,obj_id:obj_id},
			success: function (response) { 
				var record = JSON.parse(response);
				if(record.status == 'success'){
					showPopupMessage('success',record.message);	
					getShareLinkListing();
				}
			}
		});
	});
	
</script>