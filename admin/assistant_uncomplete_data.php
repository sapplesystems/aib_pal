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
	var form_init_data;
	var form_now_data;
	
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
	
	function checkGetEditData(data_parent_id, item_id = null, is_skipped = null){
		form_now_data = $('#edit_items_form').serialize();
		if(form_init_data == form_now_data){
			getEditData(data_parent_id, item_id, is_skipped);
		}else{
			if(confirm("you haven't submitted the page. Changes done if any will be lost")){
				getEditData(data_parent_id, item_id, is_skipped);
			}
		}
	}
	
	function getEditData(data_parent_id, item_id = null, is_skipped = null){
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'edit_assistant_uncomplete_data',parent_id:data_parent_id,item_id:item_id,is_skipped:is_skipped},
            success: function (response) {
                $('#form-list-section').html(response);
                $('#editUncompleteData').show();
                $('.admin-loading-image').hide();
				form_init_data = $('#edit_items_form').serialize();
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
				console.log(result);
                $('.admin-loading-image').hide();
                if(result.status == 'completed'){   
					window.location = 'assistant_index.php';
                }else if(result.status == 'success'){
                    getEditData(result.parent_id, result.item_id, result.is_skipped);
                }else{ 
					showPopupMessage('error', result.message + ' (Error Code: 354)');
                    return false;
                }
            },
            error: function () {
                $('.admin-loading-image').hide(); 
				showPopupMessage('error','Something went wrong, Please try again. (Error Code: 355)');
            }
        });
    });
	
	function getCompletedEditData(record_id,item_id, is_skipped = null){ 
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'edit_assistant_completed_data',record_id:record_id,item_id:item_id,is_skipped:is_skipped},
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
				showPopupMessage('error','Data not updated, Please try again. (Error Code: 356)');
            }
        });
    })
</script>