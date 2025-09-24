<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$loggedInUserId = $_SESSION['aib']['user_data']['user_id'];
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Manage Administrator</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Administrator</li>
        </ol>
        <!-- Fix start on 21-Mar-2025 -->
       <!-- <h4 class="list_title text-center"> <span class="pull-left top-padding">Manage Client Administrator</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> 
        <button class="btnDownload" onClick="get_administrator_data_export()"><i class="fa fa-download"></i>&nbsp; Download Report</button>
        <div class="clearfix"></div>    
         </h4>-->
        <!-- Fix end on 21-Mar-2025 -->
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row"  id="dataTableDiv">
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
                <h4 class="list_title">Update Administrator <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="administrator_form" name="administrator_form" method="POST" class="form-horizontal" action="">
                    <input type="hidden" name="user_id" id="user_id" value="">
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Archive Group</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_name"  name="archive_name" disabled="disabled">
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
                    <?php if($_SESSION['aib']['user_data']['user_type'] == 'A'){ ?>
                        <div class="form-group">
                            <label class="col-xs-3 control-label">User Type</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" id="user_access_type"  name="user_access_type">
                                        <option value="primary">Primary</option>
                                        <option value="secondary">Secondary</option> 
                                    </select>
                                </span>
                            </div>
                        </div>
                    <?php } ?>
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

<div class="modal fade" id="tnc_accept_list_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Terms and Conditions Version List <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
				<table class="table">
					<thead>  
						<tr>  
							<th>Version</th>
							<th>DateTime</th>
						</tr>  
					</thead>  
					<tbody id="tnc_accept_list_popup_data"></tbody>
				</table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="term_condition_version" role="dialog"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Terms of service </span> 
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				</h4>
            </div>
                <div class="modal-body" id="movefolderformdivData"> 
				<div class="clearfix"></div>
                <div class="footerOverflow overflowTerms">
					<p id="term_cond_value"> </p>
				</div>
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
                    $('#user_title').val(record.user_title);
                    $('#user_login').val(record.user_login);
                    $('#user_email').val(record.property.email);
                    $('#user_access_type').val(record.properties.type);
                    $.ajax({
                        type: 'POST',
                        url: 'services_admin_api.php',
                        data: {mode:'assistant_archive_item_list',type:'ag'},
                        success: function (response) {
                           $('#archive_name').html(response);
                           $('#archive_name').val(record.user_top_folder);
                           $('#editUserPopup').modal('show');
                           $('.admin-loading-image').hide();
                        }
                    });
                }
            });
        }
    });
	
	$(document).on('click','.tnc_accept_list',function(){
		$('.admin-loading-image').show();
        var user_id = $(this).attr('data-form-user-id');
		if(user_id){
			$.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_tnc_accept_list',user_id:user_id},
                success: function (response) {
                    var record = JSON.parse(response);
					if(record.status == 'success'){
						$('#tnc_accept_list_popup_data').html(record.html);
						$('#tnc_accept_list_popup').modal('show');
					}
                }
            });
			$('.admin-loading-image').hide();
		}
	});
	
	$(document).on('click','.tnc_version',function(){
		var tnc_id = $(this).attr('id');
		$('#term_cond_value').html('');
		if(tnc_id){
			$('.admin-loading-image').show();
			$.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_tnc_version',tnc_id:tnc_id},
                success: function (response) {
					console.log(response);
                    var record = JSON.parse(response);
					if(record.status == 'success'){
						$('#term_cond_value').html(record.message);
						$('#term_condition_version').modal('show');
					}
					$('.admin-loading-image').hide();
                }
            });
		}
	});
    
    $(document).on('click', '#update_administrator_form', function (){
        if ($("#administrator_form").valid()) {
            var loggedInUserId = '<?php echo $loggedInUserId; ?>';
            var editedUserId   = $('#user_id').val();
            var administratorFormData = $('#administrator_form').serialize();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_user_profile',formData: administratorFormData },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        if(loggedInUserId == editedUserId){
                            window.location.reload();
                        }
                        get_administrator_data();
                        $('#editUserPopup').modal('hide');
                    }
                    //$('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 496)');
                }
            });
        }
    });
	var table = $('#myTable').DataTable({"pageLength": 100,"sDom":'<"H"lfrp>t<"F"ip>'});
	
	function get_administrator_data_export(){
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
        
		
		
		
      
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'assistant_list_generate_report',type:'A', type2: 'unclaimed'},
            success: function (response) {
                                  /*  var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
table.row.add([
                        record[i].user_id,                        
                        record[i].item_title,
                        record[i].user_pro_type,
                        record[i].user_title,
                        record[i].user_login,
						'<span class="edit_administrator" data-title="Edit" data-form-user-id='+ record[i].user_id +'><img src="'+image_path+'edit_icon.png" alt="" /></span><span class="delete_administrator" data-form-user-id="'+ record[i].user_id +'"><img src="'+image_path+'delete_icon.png" alt="" /></span><span class="login_as_administrator" data-title="Login as administrator" data-form-user-id="'+ record[i].user_id +'"><img title="Login as administrator" src="'+image_path+'login-as.png" alt="" /></span><span class="tnc_accept_list" data-title="Edit" data-form-user-id='+ record[i].user_id +'><img src="'+image_path+'terms_condition.png" alt="Term and Condition Accept List" /></span>'
                    ]).draw(false);
                    select: true
                }
                $('.admin-loading-image').hide();
		$("#dataTableDiv").show();*/
				 $('.admin-loading-image').hide();
				downloadFile( '<?php echo HOST_PATH;?>tmp/user_list_report.csv');
				
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 497)');
            }
        });
    }
	function downloadFile(url) {
  const a = document.createElement('a');
  a.href = url;
  a.download = 'data.csv'; // optional: leave blank to use the original filename
		
  document.body.appendChild(a);
  a.click();
  a.remove();
}
    function get_administrator_data(){
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
        
        table.clear().draw();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'assistant_list',type:'A', type2: 'unclaimed'},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    table.row.add([
                        record[i].user_id,                        
                        record[i].item_title,
                        record[i].user_pro_type,
                        record[i].user_title,
                        record[i].user_login,
						'<span class="edit_administrator" data-title="Edit" data-form-user-id='+ record[i].user_id +'><img src="'+image_path+'edit_icon.png" alt="" /></span><span class="delete_administrator" data-form-user-id="'+ record[i].user_id +'"><img src="'+image_path+'delete_icon.png" alt="" /></span><span class="login_as_administrator" data-title="Login as administrator" data-form-user-id="'+ record[i].user_id +'"><img title="Login as administrator" src="'+image_path+'login-as.png" alt="" /></span><span class="tnc_accept_list" data-title="Edit" data-form-user-id='+ record[i].user_id +'><img src="'+image_path+'terms_condition.png" alt="Term and Condition Accept List" /></span>'
                    ]).draw(false);
                    select: true
                }
                $('.admin-loading-image').hide();
		$("#dataTableDiv").show();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 498)');
            }
        });
    }
    
    $(document).on('click','.delete_administrator',function(){
        if(confirm("Are you sure to delete the assistant? This cannot be undone")){
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
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 499)');
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
    $(function(){
        $('#confirm_user_password').keypress(function (e) {
            var key = e.which;
            if(key == 13){  
                $('#update_administrator_form').click();
            }
        });
    });
</script>