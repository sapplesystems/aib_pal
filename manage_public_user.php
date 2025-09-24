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
        <h1>Manage Administrator</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Public User</li>
        </ol>
        <h4 class="list_title">Manage Public User </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
            <div class="tableScroll">
                <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th width="10%" class="text-center">User Id</th> 
                            <th width="20%">Archive Group</th> 
                            <th width="10%">User Type</th> 
                            <th width="20%">User Title</th>  
                            <th width="30%">User Login</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>  
                    </thead>  
                    <tbody id="listdata">   </tbody>  
                </table> 
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="editUserPopup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Update Public User <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="administrator_form" name="administrator_form" method="POST" class="form-horizontal" action="">
                    <input type="hidden" name="user_id" id="user_id" value="">
                    <div class="form-group">
                        <label class="col-xs-3 control-label">User Login</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" readonly="readonly" name="user_login" id="user_login" value="" />
                        </div>
                    </div>
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
                            <button type="button" class="btn btn-info borderRadiusNone" name="update_administrator_form" id="update_administrator_form">Update</button>
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="clearAssistantForm">Clear Form</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dataTableDiv").hide();
        get_administrator_data();
        //Validate login form
        $("#administrator_form").validate({
            rules: {
                user_email:{
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
                user_email:{
                    required: "User email is required",
                    email:"Please enter valid email Id"
                },
                confirm_user_password: {
                    equalTo: "New password and confirm password should be same." 
                }
            }
        });
    });
    $(document).on('click', '.edit_administrator', function () {
        $('.admin-loading-image').show();
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
                    $('#user_email').val(record.property.email);
                    $('#user_login').val(record.user_login);
                    $('#editUserPopup').modal('show');
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click', '#update_administrator_form', function (){
        if ($("#administrator_form").valid()) {
            var administratorFormData = $('#administrator_form').serialize();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_user_profile',formData: administratorFormData },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        get_administrator_data();
                        $('#editUserPopup').modal('hide');
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 229)');
                }
            });
        }
    });
	 var table = $('#myTable').DataTable({"pageLength": 100});
    function get_administrator_data(){
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
       
        table.clear().draw();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'assistant_list',type:'U'},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {   
                    var status_image = 'deactive.png';
                    var status = 'd';
                    var status_image_ebay = 'ebay-disable.png';
                    var status_ebay = 'Y';
                    
                    if(record[i].status == 'a'){
                        status_image = 'active.png';
                        status       = 'a';
                    } 
					if(record[i].ebay_status != 'N'){  
						status_image_ebay = 'ebay-enable.png';
						status_ebay       = 'N';
					} 
                    table.row.add([
                        record[i].user_id,                        
                        record[i].item_title,
                        record[i].user_type,
                        record[i].user_title,
                        record[i].user_login, 
			'<span class="edit_administrator" data-title="Edit" data-form-user-id='+ record[i].user_id +'><img src="'+image_path+'edit_icon.png" alt="" /></span><span class="delete_administrator" data-form-user-id="'+ record[i].user_id +'"><img src="'+image_path+'delete_icon.png" alt="" /></span><span class="login_as_administrator" data-title="Login as administrator" data-form-user-id="'+ record[i].user_id +'"><img title="Login as administrator" src="'+image_path+'login-as.png" alt="" /></span><span user-status="'+status+'" class="change_user_status" data-title="Change user status" data-form-user-id="'+ record[i].user_id +'"><img title="Change status" src="'+image_path+status_image+'" alt="" /></span><span user-status="'+status_ebay+'" class="change_user_ebay_status" data-title="Change user ebay status" data-form-folder-id="'+ record[i].user_top_folder +'"><img title="Change Ebay status" src="'+image_path+status_image_ebay+'" alt="" /></span>'
                        ]).draw(false);
                    select: true
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 230)');
            }
        });
    }
    
    $(document).on('click','.delete_administrator',function(){
        if(confirm("Are you sure to delete the public user ? This cannot be undone")){
            //$('.admin-loading-image').show();
            var user_profile_id = $(this).attr('data-form-user-id');
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_user_profile',user_profile_id: user_profile_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        get_administrator_data();
                    }
                    //$('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 231)');
                    //$('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click','.login_as_administrator', function(){
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
                       window.location.href = 'manage_my_archive.php';
                   }
                   $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click', '.change_user_status', function(){
        var user_id = $(this).attr('data-form-user-id');
        var current_status = $(this).attr('user-status');
        if(user_id){
            if(confirm('Are you sure to change the status of this user?')){
                $('.admin-loading-image').show();
                $.ajax({
                    type: 'POST',
                    url: 'services_admin_api.php',
                    data: {mode:'change_public_user_status',user_id: user_id, current_status: current_status},
                    success: function (response) {
                        var record = JSON.parse(response);
                        if(record.status == 'success'){
                          get_administrator_data();
                        }else{
                            $('.admin-loading-image').hide();
                        }
                    }
                });
            }
        }
    });
    
    $(document).on('click', '.change_user_ebay_status', function(){
        var folder_id = $(this).attr('data-form-folder-id');
        var current_status = $(this).attr('user-status');
        if(user_id){
            if(confirm('Are you sure to change the ebay status of this user?')){
                $('.admin-loading-image').show();
                $.ajax({
                    type: 'POST',
                    url: 'services_admin_api.php',
                    data: {mode:'update_archive_group_ebay_status',archive_id: folder_id, current_ebay_status: current_status},
                    success: function (response) { 
						get_administrator_data(); 
                    }
                });
            }
        }
    });
    
    $(function(){
             $('#confirm_user_password').keypress(function (e) {
             var key = e.which;
             if(key == 13)  
              {  
                    $('#update_administrator_form').click();
              }
            });
    });
</script>