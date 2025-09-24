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
            <div class="col-md-12 tableStyle" id="form-list-section">
                
            </div>
			<div class="col-md-12 tableStyle" id="editUncompleteData">
                
            </div>
			
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
	<?php if (isset($_REQUEST['parent_id'])){?>
	var data_parent_id = <?php echo $_REQUEST['parent_id']; ?>;
	getEditData(data_parent_id);
	<?php }else{?>
	var record_id = <?php echo $_REQUEST['record_id']; ?>;
	var item_id = <?php echo $_REQUEST['item_id']; ?>;
	getCompletedEditData(record_id,item_id)
	<?php }?>
         
    }); 
	$(document).on('click','.assistant_dashboard',function(){ 
		window.location = 'assistant_index.php';
    });
	function getEditData(data_parent_id, item_id = null){
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'edit_assistant_uncomplete_data',parent_id:data_parent_id,item_id:item_id},
            success: function (response) {
                $('#form-list-section').html(response);
                $('#editUncompleteData').show();
                $('.admin-loading-image').hide();
            }
        });
    }
	$(document).on('click','#update_form_data', function(){
        var itemsFormData = $("#edit_items_form").serialize();
        $('.admin-loading-image').show();
        $.ajax({ 
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_assistant_item', formData: itemsFormData},
            success: function (data){
                var result = JSON.parse(data);
                $('.admin-loading-image').hide();
                if(result.status == 'completed'){   
					window.location = 'assistant_index.php';
                }else if(result.status == 'success'){
                    getEditData(result.parent_id, result.item_id );
                }else{ 
					showPopupMessage('error', result.message + ' (Error Code: 1160)');
                    return false;
                }
            },
            error: function () {
                $('.admin-loading-image').hide(); 
				showPopupMessage('error','Something went wrong, Please try again. (Error Code: 110)');
            }
        });
    });
	
	function getCompletedEditData(record_id,item_id){ 
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'edit_assistant_completed_data',record_id:record_id,item_id:item_id},
                success: function (response) {
                    $('#form-list-section').html(response);
                    $('#editUncompleteData').show();
                    $('.admin-loading-image').hide();
                }
            });	
	}
	$(document).on('click','#update_completed_form_data', function(){
        var itemsFormData = $("#edit_items_completed_form").serialize();
        $('.admin-loading-image').show();
        $.ajax({ 
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_assistant_completed_item', formData: itemsFormData},
            success: function(data){
               var result = JSON.parse(data);
               if(result.status == 'success'){
					window.location = 'assistant_index.php'; 
               }
            },
            error: function () {
                $('.admin-loading-image').hide(); 
				showPopupMessage('error','Data not updated, Please try again. (Error Code: 1161)');
            }
        });
    })
</script>