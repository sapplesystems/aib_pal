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
        <h1>Society Trouble Ticket Requests</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Society Trouble Ticket Requests</li>
        </ol>
        <h4 class="list_title">Manage Society Trouble Ticket Requests </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                 <div class="tableScroll">
                <table id="content_request_table" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>
                            <th width="10%" class="text-center">Status</th>
                            <th width="15%" class="text-center">Name</th> 
                            <th width="15%" class="text-center">Email</th>
                            <th width="15%" class="text-center">Created</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>  
                    </thead>  
                    <tbody id="listdata"></tbody>  
                </table> 
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="request_details_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Report Details <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body" id="request_details_body_section"></div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#dataTableDiv").hide();
        getContentRemovalData('STT');
    });
    
    $(document).on('click','.view_request_data', function(){
        var request_id = $(this).attr('data-form-request-id');
        if(request_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_data_by_request_id',request_id:request_id},
                success: function (response) {
                    $('#request_details_body_section').html(response);
                    $('#request_details_popup').modal('show');
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    var table = $('#content_request_table').DataTable({"pageLength": 100});
    function getContentRemovalData(type){
        $("#dataTableDiv").hide();
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
        table.clear().draw();
        var archive_group_id = '';
        if($('#archive_listing').length){
            archive_group_id = $('#archive_listing').val();
        }
        var select_type = $('#select_dropdown').val();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'get_report_list_data',request_data_type:type, archive_group_id: archive_group_id,select_type:select_type },
            success: function (response) {
                var record = JSON.parse(response);
                if(record==null){
                    $('.admin-loading-image').hide();
                    return false;
                }
                for (i = 0; i < record.length; i++) {
                    if(record[i].req_status	 == 'COMPLETED'){  
                        var select_option = '<span style="display:none">1</span><select data-form-request-id='+ record[i].req_id +' style="padding:0px; width:110px;" class="form-control change_request_status" name="change_request_status"><option value="New">New</option> <option value="COMPLETED" selected>Completed</option></select>';
			var delButton = '';
                    }else{  
                        var select_option = '<span style="display:none">0</span><select data-form-request-id='+ record[i].req_id +' style="padding:0px; width:110px;" class="form-control change_request_status" name="change_request_status"><option value="New">New</option> <option value="COMPLETED">Completed</option></select>';
                        var delButton = '<span class="delete_request_data" data-form-request-id="'+ record[i].item_link +'" request-id='+ record[i].req_id +'><img src="'+image_path+'delete_icon.png" alt="" /></span>';
                    }		
                    table.row.add([
                        select_option,
                        record[i].req_name,
                        record[i].req_email,
                        record[i].created,
			delButton+'<span class="view_request_data" data-title="View" data-form-request-id='+ record[i].req_id +'><img src="'+image_path+'view.png" alt="" /></span>'
                    ]).draw(false);
                    select: true;
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            }
        });
    }
     
    
    $(document).on('click','.delete_request_data', function(){
        if(confirm('Are you sure to delete ? This cannot be undone')){
            var request_id =  $(this).attr('request-id');
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
		url: 'services_admin_api.php',
		data: {mode:'change_request_status',request_id: request_id, status: 'COMPLETED'},
		success: function (response) {
                    getContentRemovalData('STT');
		}
            });
        }
    });
   $(document).on('change','#select_dropdown',function(){
        var select_dropdown = $('#select_dropdown').val();
        if($(".archive_list_data").is(":visible")){
          $('.archive_list_data').css("visibility", "hidden");   
       }
        if(select_dropdown == 'Archive'){
            $('.archive_list_data').css("visibility", "visible");
        }
        getContentRemovalData('STT');
    });
    
    $(document).on('change','.change_request_status', function(){
        var request_id = $(this).attr('data-form-request-id');
        var status     = $(this).val();
        $('.admin-loading-image').show();
		changeStatusReport(request_id,status);
    });
	$(document).on('keyup', '#contact_comments', function (e) {
		var key = e.which;
		if(key == 13)   
		{   
			$('#save_request_form_data').click();
		}
	});
	
	function changeStatusReport(request_id,status){
		$.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'change_report_status',request_id: request_id, status: status},
            success: function (response) {
                var record = JSON.parse(response);
                if(record.status == 'success'){
                    getContentRemovalData('STT');
                }
            }
        });
	}
</script>