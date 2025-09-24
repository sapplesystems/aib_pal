<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
$loginUserType = $_SESSION['aib']['user_data']['user_type'];
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Assistant</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Assistant Dashboard</li>
        </ol>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-12 tableStyle" id="assistant_index_listing_section">
                
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'assistant_index_data'},
            success: function (response) {
                $('#assistant_index_listing_section').html(response);
                $('.admin-loading-image').hide();
            }
        });
    });
	
	function markComplete(e, sg_id){
		e.preventDefault();
		if(confirm("Are you sure to mark this folder complete?")){
			$('.admin-loading-image').show();
			$.ajax({
				type: 'POST',
				url: 'services_admin_api.php',
				data: {mode:'mark_complete_record', folder_id: sg_id},
				success: function (response) {
					$('.admin-loading-image').hide();
					var obj = JSON.parse(response);
					if(obj.status == 'success'){
						showPopupMessage('success','Mark completed successfully.');
						setTimeout(function(){
							window.location.href = window.location.href;
						},1000);
					}else{
						showPopupMessage('error','Something went wrong, Please try again. (Error Code: 345)');
					}
				},
				error: function () {
					$('.admin-loading-image').hide(); 
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 346)');
				}
			});
		}
	}
	
	function markInComplete(e, assistant_id, sg_id){
		e.preventDefault();
		if(confirm("Are you sure to mark this folder incomplete?")){
			$('.admin-loading-image').show();
			$.ajax({
				type: 'POST',
				url: 'services_admin_api.php',
				data: {mode:'mark_incomplete_record', assistant_id:assistant_id, sub_group_id: sg_id},
				success: function (response) {
					$('.admin-loading-image').hide();
					var obj = JSON.parse(response);
					if(obj.status == 'success'){
						showPopupMessage('success','Mark incompleted successfully.');
						setTimeout(function(){
							window.location.href = window.location.href;
						},1000);
					}else{
						showPopupMessage('error','Something went wrong, Please try again. (Error Code: 347)');
					}
				},
				error: function () {
					$('.admin-loading-image').hide(); 
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 348)');
				}
			});
		}
	}
</script>