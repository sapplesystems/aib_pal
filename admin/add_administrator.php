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
        <h1>Client Administrator</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Client Administrator</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Add New Client Administrator </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="addAdministratorForm" name="addAdministratorForm">
                     <input type="hidden" name="user_exist" id="user_exist" value="false">
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Archive Group :</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_name"  name="archive_name">
                                    <option value="">- Select -</option> 
                                </select>
                            </span>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>User Type :</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <span class="custom-dropdown">
                                <select class="form-control" id="user_type"  name="user_type">
                                     <option value="primary" selected>Primary</option>
                                     <option value="secondary">Secondary</option>
                                </select>
                            </span>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>User Login :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control login"  id="login_data"  name="login_data" placeholder="Login username">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>User Password :</strong></div>
                        <div class="col-md-7"><input type="password" class="form-control login"  id="user_password"  name="user_password" placeholder="Login Password">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>User Email :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="user_email"  name="user_email" placeholder="Email Id"></div>
                    </div>
                    <!--<div class="row">
                        <div class="col-md-4 text-right"><strong>User Name :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control" id="asst_name"  name="asst_name" placeholder="Display name"></div>
                    </div>-->
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-info borderRadiusNone" id="addAdministratorButton">Add Administrator</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm">Clear Form</button></div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#login_data').bind('input', function(){
            var data = $('#login_data').val();
            if(data == '' || data == null){$('label[for=user_login]').remove();}
        });
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode: 'assistant_archive_item_list', type: 'ag'},
            success: function (response) {
                $('#archive_name').html(response);
                $('.admin-loading-image').hide();
            }
        });
        $('#addAdministratorButton').click(function () {
            var user_exist = $('#user_exist').val();
            if ($("#addAdministratorForm").valid() && user_exist=='false') {
                $('.admin-loading-image').show();
                var assistantFormData = $("#addAdministratorForm").serialize();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'add_administrator', formData: assistantFormData},
                    success: function (data) {
                        var result = JSON.parse(data);
                        if (result.status == 'success') {
                            $("#addAdministratorForm")[0].reset();
                            window.location.href = 'manage_clients_administrator.php';
                        } else {
                            showPopupMessage('error', result.message + ' (Error Code: 330)');
                        }
                        $('.admin-loading-image').hide();
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 331)');
                        $('.admin-loading-image').hide();
                    }
                });
            }
        });
        $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                var check = false;
                return this.optional(element) || regexp.test(value);
            },
            "Please check your input."
        );
        //Validate login form
        $("#addAdministratorForm").validate({
            rules: {
                archive_name: {
                    required: true
                },
                login_data:{
                    required: true,
                    regex : /^[a-zA-Z0-9_]{1,40}$/
                },
                user_password:{
                    required: true,
                    regex: /^.{6,}$/
                },
                user_email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                }
            },
            messages: {
                archive_name: {
                    required: "Please select an archive group"
                },
                login_data:{
                    required: "Please enter login username",
                    regex : "Only alphanumeric and _ allowed"
                },
                user_password:{
                    required: "Please enter login password",
                    regex : "Password should be minimum 6 character"
                },
                user_email: {
                    required: "Please enter email Id",
                    email: "Please enter valid email Id"
                }
            }
        });
		
		
		$('#user_email').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  { 
			$('#addAdministratorButton').click();
		  }
		}); 
		
		
    });
    
    $(document).on('focusout onchange', '#login_data', function(){
        var selected_archive = $("#archive_name option:selected").text();
        var matches = selected_archive.match(/\b(\w)/g);              // ['J','S','O','N']
        var suffix = matches.join(''); 
        var user_login = $('#login_data').val();
        if(user_login =='' || user_login == null){
            $('label[for=user_login]').remove();  
        }
//        if(user_login != ''){
//            if(user_login.indexOf('_admin_'+suffix) == -1){
//                $('#login_data').val(user_login+'_admin_'+suffix);
//            }
//        }
         geck_user_exist();
    });
  function geck_user_exist(){
      $('.admin-loading-image').show();
      var user_login = $('#login_data').val();
      $('#user_exist').val('false');
      $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'check_user_exist', username:user_login},
            success: function (data) {
                $('label[for=user_login]').remove();
                var result = JSON.parse(data);
                if (result.status == 'success') {
                   $('#user_exist').val('true');
                   var html = ' <label  class="login_error" for="user_login">'+result.message+'</label>';
                   $(html).insertAfter( "#login_data");
                } 
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 332)');
                $('.admin-loading-image').hide();
            }
        });
  }   
</script>