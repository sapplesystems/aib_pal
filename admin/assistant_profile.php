<?php
session_start();
//echo'<pre>';print_r($_SESSION['aib']['user_data']);echo'</pre>';
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?> 
<style>.modal-dialog{width:100%;} #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}</style>
<div class="content-wrapper">
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <form id="userUpadatForm" name="userUpadatForm" method="post" class="form-horizontal">
                <input type="hidden" name="user_id" id="archive_user_id" value="<?php echo $_SESSION['aib']['user_data']['user_id']; ?>">
                <input type="hidden" name="user_old_email" id="user_old_email" value="<?php echo $_SESSION['aib']['user_data']['user_prop']['email']; ?>">
                <div class="">
                    <div class="container manageInfo fullContainer">
                        <div class="row marginTop20" id="display_message" style="display:none">
                            <div class="col-md-12" style="color:green">
                                Your profile has been updated successfully.
                            </div>
                        </div>
                        <div class="row marginTop20 bgNone"><div class="col-md-12"><h3 class="text-center"><span class="pull-left">My Profile </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h3></div></div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Username<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input disabled="" type="text" class="form-control" name="username" id="username" value="<?php echo $_SESSION['aib']['user_data']['user_login']; ?>" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Email Address<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="email" id="email" value="" placeholder="Enter text" >
                            </div> 
                        </div>
                         <div class="clearfix marginTop20"></div> 
                        <div class="checkboxStyle">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="Y"name="occasional_update" id="occasional_update" value="" />
                                    Yes, please send me occasional updates,training materials, or marketing from ArchiveInBox
                                </label>
                            </div>
                            <div class="checkbox" >
                                <label>
                                    <input type="checkbox" value="Y" name="term_service" id="term_service" >
                                    I agree to the <a href="javascript:service_term_popup_on_assistanat()" style="color:#72afd2;">Terms of service</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="row bgNone termsCondition" style="display:none;">
        <div class="col-md-6">
          <div class="">
            <div class="modal-dialog widthFullModal marginTop20">
              <div class="modal-content">
                <div class="modal-header form_header">
                  <h4 class="list_title"><span id="popup_heading">Terms of service </span>
                    <button type="button" class="close canPopUp closepopup" data-dismiss="modal">&times;</button>
                  </h4>
                   <button type="button" onclick="PrintElem('get_term_cond_data');" class="btn btn-primary borderRadiusNone pull-right marginTop10">Print</button>
                </div>
                <div class="modal-body" id="movefolderformdiv">
                  <div class="col-md-12 overflowTerms">
                  <p id="get_term_cond_data"> </p>
                  </div>
                  <div  class="form-horizontal">
                    <div class="form-group">
                      <label class="col-xs-3 control-label"></label>
                      <div class="col-xs-7 marginTop20">
                        <button type="button" class="btn btn-info  borderRadiusNone" id="agreeTermButton">Yes I agree</button>
                        <button type="button" class="btn btn-info  borderRadiusNone closepopup" id="notAgreeTermButton">No I do not agree</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
                      <div class="row marginTop20 bgNone">
                            <div class="col-md-3"></div>          
                            <div class="col-md-4" >
                             <input type="button" class="form-control btn-info btnAss" name="regOtherUpdate" id="regOtherUpdate" value="Update" >
                            <span class="change_pass btn btn-info borderRadiusNone">Change Password</span>
                            </div> 
                        </div>
                        <div class="clearfix marginTop20"></div>
                    </div>
                </div>
            </form>
        </div>

    </section>
</div>
<div class="modal fade" id="change_pass_popup" role="dialog" > 
    <div class="modal-dialog widthFullModal">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Change Password</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body" id="movefolderformdiv">
                <form id="change_password" name="change_password" method="post" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-xs-4 control-label">Old Password</label>
                        <div class="col-xs-7">
                            <input type="password" id="old_pass" name="old_pass" class="form-control" placeholder="Enter Old password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label">New Password</label>
                        <div class="col-xs-7">
                            <input type="password" id="new_pass" name="new_pass" class="form-control" placeholder="Enter New Password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label">Confirm Password</label>
                        <div class="col-xs-7">
                            <input type="password" id="confirm_pass" name="confirm_pass" class="form-control" placeholder="Enter Confirm Password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-4 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info " name="save_pass" id="save_pass">Change</button>
                            <button type="button" class="btn btn-danger " data-dismiss="modal" >Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH.'jquery.inputmask.bundle.js'; ?>"></script>
<script>
    $(document).ready(function () {
        var user_id = '<?php echo $_SESSION['aib']['user_data']['user_id']; ?>'
        if (user_id != '') {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_user_details', user_id: user_id},
                success: function (response) {
                    
                    var record = JSON.parse(response);
                    $('#email').val(record.email);
                    if (record.occasional_update == 'Y') {
                        $('#occasional_update').attr('checked', true);
                    }
                    if (record.term_service == 'Y') {
                        $('#term_service').attr('checked', true);
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 349)');
                }
            });
        }


    });
    $(document).on("click", "#regOtherUpdate", function () {
	  $.validator.addMethod(
                "regex",
                function (value, element, regexp) {
                    var check = false;
                    return this.optional(element) || regexp.test(value);
                },
                "Please check your input."
                );
           $("#userUpadatForm").validate({
            rules: {
                email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                term_service: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Please enter email Id", 
		            email: "Please enter valid email Id "
                },
                term_service: {
                    required: "Please accept terms of service"
                }
            }
        });

        if ($("#userUpadatForm").valid()) {
            $('.admin-loading-image').show();
            var updateUserData = $("#userUpadatForm").serialize();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_assis_details', formData: updateUserData},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('.admin-loading-image').hide();
                    if (record.status == 'success') {
                        showPopupMessage(record.status, record.message);
                    }
                    $('.admin-loading-image').hide();
                    setTimeout(location.reload.bind(location), 4000);
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 350)');
                    $('.loading-div').hide();
                }
            });
        }
    });
    $(document).on("click", ".change_pass", function () {
        $('#change_pass_popup').modal('show');
    });
    $(document).on("click", "#save_pass", function () {
        jQuery.validator.addMethod("notEqual", function (value, element, param) {
            return this.optional(element) || value != param;
        }, "Old password should not be same as new password");
        var oldpass = $('#old_pass').val();
        $("#change_password").validate({
            rules: {
                old_pass: {
                    required: true
                },
                new_pass: {
                    required: true,
                    notEqual: oldpass
                },
                confirm_pass: {
                    required: true,
                    equalTo: "#new_pass"
                }
            },
            messages: {
                old_pass: {
                    required: "Please enter your old password"
                },
                new_pass: {
                    required: "Please enter new password",
                    notEqual: "Old password should not be same as new password"
                },
                confirm_pass: {
                    required: "Please enter confirm password",
                    equalTo: "Password & confirm password should be same"
                }
            }
        });
        if ($("#change_password").valid()) {
            $('.admin-loading-image').show();
            var changePassData = $("#change_password").serialize();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'password_change', formData: changePassData},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('.admin-loading-image').hide();
                    if (record.status == 'success') {
                        showPopupMessage(record.status, record.message);
                        $('.admin-loading-image').hide();
                        $('#change_pass_popup').modal('hide');
                        $('#change_password')[0].reset();
                    } else {
                        $('.admin-loading-image').hide();
                        showPopupMessage(record.status, 'Invalied old password');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 351)');
                    $('.loading-div').hide();
                }
            });
        }
    })

    function service_term_popup_on_assistanat(){
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'get_term_and_condition', user_id: 1},
            success: function (data){
                var result = JSON.parse(data);
                if(result.status == 'success'){ 
                    $('#get_term_cond_data').html(result.message); 
                    $('.termsCondition').css('display','block');
                }else{
                    showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 352)');
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 353)');
            }
        });
    }
</script>