<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
$loginUserType = $_SESSION['aib']['user_data']['user_type'];
$archive_group_id = '';
if($_SESSION['aib']['user_data']['user_type'] == 'A'){
    $archive_group_id = $_SESSION['aib']['user_data']['user_top_folder'];
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Manage Assistant</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Assistant</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Assistant</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <input type="hidden" name="archive_group_id" id="archive_group_id" value="<?php echo $archive_group_id; ?>">
                <?php if($loginUserType == 'R'){ ?>
                    <div class="archive-list custom-dropdown">
                        <select class="form-control" name="archive_listing" id="archive_listing">
                            <option value="">Select an archive</option>
                        </select>
                    </div>
                <?php } ?>
                
                        <div class="tableScroll">
                <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th width="10%" class="text-center">User Id</th> 
                            <th width="20%">User Type</th> 
                            <th width="20%">User Login</th>  
                            <th width="40%">User Title</th>
                            <th width="10%" class="text-center">Actions</th>
                        </tr>  
                    </thead>  
                    <tbody id="listdata">   </tbody>  
                </table> 
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="editAssistantForm" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Update Assistant <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="assistantForm" method="POST" name="assistantForm" class="form-horizontal" action="" >
                    <input type="hidden" name="user_id" id="user_id" value="">
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Archive Group</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_name"  name="archive_name">
                                    <option value="">- Select -</option> 
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">User Login</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" readonly="readonly" name="user_login" id="user_login" value="" />
                        </div>
                    </div>
                    <!--<div class="form-group">
                        <label class="col-xs-3 control-label">User Name</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" name="user_title" id="user_title" value="" />
                        </div>
                    </div>-->
                    <div class="form-group">
                        <label class="col-xs-3 control-label">User Email</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" name="user_email" id="user_email" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">New Password</label>
                        <div class="col-xs-7">
                            <input type="password" class="form-control" name="user_password" id="user_password" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Confirm New Password</label>
                        <div class="col-xs-7">
                            <input type="password" class="form-control" name="confirm_user_password" id="confirm_user_password" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="update_assistant_btn" id="update_assistant_btn">Update</button>
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="clearAssistantForm">Clear Form</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assistant_management_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" id="assistant_management"></div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dataTableDiv").hide();
        <?php if($loginUserType == 'R'){ ?>
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'assistant_archive_item_list',type:'ar'},
                success: function (response) {
                   $('#archive_listing').html(response);
                   $('.admin-loading-image').hide();
                   get_assistant_list();
                }
            });
        <?php }else{ ?>
            get_assistant_list();
        <?php } ?>
        $('#archive_listing').change(function(){
            get_assistant_list($(this).val());
        });
        //Validate login form
        $("#assistantForm").validate({
            rules: {
                user_email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                confirm_user_password: {
                    equalTo : "#user_password"
                }       
            },
            messages: { 
                user_email: {
                    required: "Email Id is required",
                    email: "Please enter valid email Id"
                },
                confirm_user_password: {
                    equalTo: "New password and confirm password should be same." 
                }
            }
        });
    });
     var table = $('#myTable').DataTable({"pageLength": 100});
    function get_assistant_list(parent_id = ''){
        $("#dataTableDiv").hide();  
        table.clear().draw();
        if($('#archive_listing').length && parent_id ==''){
            parent_id = $('#archive_listing').val();
        }
        var image_path = '<?php echo IMAGE_PATH; ?>';
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'assistant_list',type:'S',parent_id:parent_id},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    table.row.add([
                        record[i].user_id,
                        record[i].user_type,
                        record[i].user_login,
                        record[i].user_title, 
						'<span class="edit_assistant" data-title="Edit" data-form-user-id='+ record[i].user_id +'><img title="Edit assistant" src="'+image_path+'edit_icon.png" alt="" /></span><span class="delete_assistant" data-title="Delete" data-form-user-id="'+ record[i].user_id +'"><img title="Delete assistant" src="'+image_path+'delete_icon.png" alt="" /></span><span class="manage_assistant" data-title="Manage assistant" data-form-user-id="'+ record[i].user_id +'"><img title="Manage assistant" src="'+image_path+'manage.png" alt="" /></span><span class="login_as_assistant" data-title="Login as assistant" data-form-user-id="'+ record[i].user_id +'"><img title="Login as assistant" src="'+image_path+'login-as.png" alt="" /></span>'
                    ]).draw(false);
                    select: true;
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 194)');
            }
        });
    }
    $(document).on('click', '.edit_assistant', function () {
        var user_id = $(this).attr('data-form-user-id');
        if(user_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_user_by_id',user_id:user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('#user_id').val(record.user_id);
                    $('#user_type').val(record.user_type);
                    $('#user_title').val(record.user_title);
                    $('#user_login').val(record.user_login);
                    $('#user_email').val(record.property.email);
                    $.ajax({
                        type: 'POST',
                        url: 'services_admin_api.php',
                        data: {mode:'assistant_archive_item_list',type:'ar'},
                        success: function (response) {
                           $('#archive_name').html(response);
                           $('#archive_name').val(record.user_top_folder);
                           $('#editAssistantForm').modal('show');
                           $('.admin-loading-image').hide();
                        }
                    });
                }
            });
        }
    });
    
    $(document).on('click', '#update_assistant_btn', function (){
        if ($("#assistantForm").valid()) {
            var assistantFormData = $('#assistantForm').serialize();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_user_profile',formData: assistantFormData },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        get_assistant_list($('#archive_listing').val());
                        $('#editAssistantForm').modal('hide');
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 195)');
                }
            });
        }
    });
    
    $(document).on('click','.delete_assistant',function(){
        if(confirm("Are you sure to delete the assistant? This cannot be undone")){
            $('.admin-loading-image').show();
            var user_profile_id = $(this).attr('data-form-user-id');
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_user_profile',user_profile_id: user_profile_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        get_assistant_list($('#archive_listing').val());
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 196)');
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click', '.manage_assistant', function(){
        var user_id = $(this).attr('data-form-user-id');
        var archive_group_id = $('#archive_group_id').val();
        if(archive_group_id == ''){
            archive_group_id = $('#archive_listing').val();
        }
        if(user_id && archive_group_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'admin_assistant_assignment',assistant_id: user_id, archive_group_id: archive_group_id},
                success: function (response) {
                   $('#assistant_management').html(response);
                   $('#assistant_management_popup').modal('show');
                   $('.admin-loading-image').hide();
                }
            });
        }
    });
    function refresh_manager_assistantdata(){   
		var user_id = $('#selected_assistant').val();
        var archive_group_id = $('#archive_group_id').val();
        if(archive_group_id == ''){
            archive_group_id = $('#archive_listing').val();
        }
        if(user_id && archive_group_id){ 
			$('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'admin_assistant_assignment',assistant_id: user_id, archive_group_id: archive_group_id},
                success: function (response) {
                   $('#assistant_management').html(response);  
				   $('.admin-loading-image').hide();
                }
            });
        }
	}
    $(document).on('click','#assigned_assistant_to_subgroup', function(){
        var selected_sub_group = [];
        var selected_assistant = $('#selected_assistant').val();
        $('.assigned-to-subgroup').each(function(){
            if($(this).is(':checked')){
                selected_sub_group.push($(this).attr('data-id'));
            }
        });
        if(selected_sub_group.length > 0){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'assign_assistant_to_sub_group',sub_group:selected_sub_group, assistant:selected_assistant },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status == 'success'){
                        /* $('.assigned-to-subgroup').each(function(){
                            var html = '';
                            if($(this).is(':checked')){
                                var subgroup_id = $(this).attr('data-id');
                                var subgroup_name = $(this).attr('data-name');
                                html = '<span style="display:block;" id="assigned_subgroup_'+subgroup_id+'"><input type="checkbox" checked="checked" disabled="disabled" class="form-check-input assigned-assistant" data-item-id="'+subgroup_id+'" data-item-value="'+subgroup_name+'">&nbsp;'+subgroup_name+'</span>';
                                $('#assigned_sub_group').after(html);
                                $('#'+subgroup_id).remove();
                            }
                        }); */
						refresh_manager_assistantdata(); 
                    }else{ 
						showPopupMessage('error', record.message + ' (Error Code: 197)');
                    }
                }
            });
        }else{
            showPopupMessage('error','Please select a sub-group. (Error Code: 198)');
            return false;
        }
    });
    
    $(document).on('click','.available-subgroup', function(){
        var item_id = $(this).val();
        var item_title = $(this).attr('data-name');
        if ($(this).is(':checked')) {
            var html = '';
            html = '<span style="display:block;" id="'+item_id+'"><input type="checkbox" checked="checked" data-id="'+item_id+'" class="assigned-to-subgroup" data-name="'+item_title+'">&nbsp;'+item_title+'</span>';
            $('#selected_from_available_subgroup').after(html);
        }else{
            $('#'+item_id).remove();
        }
    });
	
    $(document).on('click','.available-group', function(){
        var item_id = $(this).val();
		var group_id_name = $(this).attr("id");   
			$('.'+group_id_name+'_'+item_id).each(function() {
				var new_item_id = $(this).val();
				var item_title = $(this).attr('data-name');
				if (!$(this).is(':checked')){ 
					$(this).prop("checked", true);
					var html = '';
					if(item_title != undefined ){
						html = '<span style="display:block;" id="'+new_item_id+'"><input type="checkbox" checked="checked" data-id="'+new_item_id+'" class="assigned-to-subgroup" data-name="'+item_title+'">&nbsp;'+item_title+'</span>';
						$('#selected_from_available_subgroup').after(html);
					}
				}else{
					$(this).prop("checked", false);
					$('#'+new_item_id).remove();
				}
				
			});  
    });
	
	
	
	
    $(document).on('click','.login_as_assistant', function(){
        var user_id = $(this).attr('data-form-user-id');
        if(user_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'login_as_other_user',user_id: user_id},
                success: function (response) {
                   var record = JSON.parse(response);
                   if(record.status == 'success'){
                       window.location.href = 'index.php';
                   }
                   $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click','.assigned-assistant', function(){
    /*     var sub_group_id = $(this).attr('data-item-id');
        var assistant_id  = $('#selected_assistant').val();
        if(sub_group_id && assistant_id){
            if(confirm("Are you sure to unassigned this sub-group")){
                $('.admin-loading-image').show();
                $.ajax({
                    type: 'POST',
                    url: 'services_admin_api.php',
                    data: {mode:'assistant_unassign_subgroup',assistant_id: assistant_id, sub_group_id: sub_group_id},
                    success: function (response) {
                       var record = JSON.parse(response);
                       if(record.status == 'success'){
                            $('#available_'+sub_group_id).attr({disabled:false,checked:false});
                            $('#'+sub_group_id).remove();
                        }
                        $('.admin-loading-image').hide();
                    }
                });
            }
        } */
        
    });
	$(document).on('click','#unassigned-assistant', function(){
		var unassign_id = []; 
		$.each($('.assigned-assistant'), function (index, value){//$('.assigned-assistant').each(function() {
			if ($(this).is(':checked')){  
				  unassign_id.push($(this).attr('data-item-id'));
			}
		}); 
		var assistant_id  = $('#selected_assistant').val();
		if(confirm("Are you sure to unassigned this sub-group")){
			$('.admin-loading-image').show();
			$.ajax({
				type: 'POST',
				url: 'services_admin_api.php',
				data: {mode:'assistant_unassign_subgroup',assistant_id: assistant_id, sub_group_id: unassign_id},
				success: function (response) {
				    var record = JSON.parse(response);
				   if(record.status == 'success'){
						refresh_manager_assistantdata(); 
					}else{  
					$('.admin-loading-image').hide();
					}
				}
			});
		}
		
	});
	$(function(){
		 $('#confirm_user_password').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  {  
			$('#update_assistant_btn').click();
		  }
		});
	});
        
        $(document).on('change', 'input[type=checkbox][id^="aib_item_checkbox_"]', function(){
            var item_id = $(this).attr('id');
            item_id     = item_id.replace('aib_item_checkbox_','',item_id);
            var item_title = $(this).parent().text();
            if ($(this).is(':checked')) {
                var html = '';
                html = '<span style="display:block;" id="'+item_id+'"><input type="checkbox" checked="checked" data-id="'+item_id+'" class="assigned-to-subgroup" data-name="'+item_title+'">&nbsp;'+item_title+'</span>';
                $('#selected_from_available_subgroup').after(html);
            }else{
                $('#'+item_id).remove();
            }
        });
</script>