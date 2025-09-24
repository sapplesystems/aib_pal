<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
define('URLPAGENAME', 'thank-you');
$uri = $_SERVER['REQUEST_URI'];
$str_url = explode("?", $uri);
$val = urldecode($str_url[1]);
$url = explode("&", $val);
$id = explode("=", $url[0]);
$type_value = explode("=", $url[1]);

$unclaimed_society_id = '';
if ($url[2]) {
    $unclaimed_society_arr = explode("=", $url[2]);
    $unclaimed_society_id = $unclaimed_society_arr[1];
}

$flg = 0;
if (isset($url[1]) && $url[1] == 'flg=new_registration') {
    $flg = 1;
}
$current_email = '';
$old_email = '';
$society_hide = 'hidden';
$pass_details = '';
if (isset($url[2]) && !empty($url[2])) {
    $current_email = $url[2];
}
if (isset($url[3]) && !empty($url[3])) {
    $old_email = $url[3];
}
if ($id[1] == 'archive_user_reg') {
    $society_hide = '';
}
?>
<style>
    mark{background-color: #fbd42f !important;}
    .fontValue{	font-size: 14px;
                font-weight: normal;
                padding-top: 8px;
                display: block;
    }
</style>
<div class="clearfix"></div>
<form name="registrationOtherForm" id="registrationOtherForm" method="post" action="">
    <input type="hidden" name="user_login_id" id="user_login_id" value="<?php echo $id[1]; ?>">
    <div class="content bgThankYou" style="height:calc(100vh - 86px);">
        <div class="">
            <div class="container"> 
                <div class="row marginTop20" id="display_message" style="display:block;">
                    <div class="col-md-12">
                        <div class="thankYouText">
                            <div class="change_email" id="change_email" hidden="">
                                <!--<img src="<?php echo IMAGE_PATH . 'thankYou-icon.png'; ?>" alt="Thank You" /><br />-->Your email has been changed successfully.<br />
                                <div class="with_user">Thank you for confirming the email</div> 
                            </div>
                            <div class="add_society" id="add_society" <?php echo $society_hide; ?> >
                                Your account has been created successfully.<br />
                                Please check your email for next steps.
                                <div class="with_user">Thank you for confirming the email</div> 
                            </div>
                            <span id="expirelinkMsj"></span>
                            <div class="without_user" style="display:none">
                                <?php if ($unclaimed_society_id) { ?>
                                    Thank you for your email verification.<br/>
                                    Your password has been created successfully.<br />
                                    Admin will review and update you soon.
                                <?php } else { ?>
                                    Your password has been created successfully.<br />
                                    We will reach out to you with the next steps.
                                <?php } ?>
                            </div>

                            <div class="forget_password" style="display:none">
                                <!--<img src="<?php echo IMAGE_PATH . 'thankYou-icon.png'; ?>" alt="Thank You" /><br />-->Your password has been changed successfully.<br /> 
                            </div>

                            <div id="pswd_div_id" style="display:none"  <?php echo $pass_details; ?>>
                                <span style="font-size:20px !important;padding-left: 63px;margin-bottom: 20px;display: block;">Please choose your password <br/></span>
                                <div class="row">
                                    <div class="col-md-4 text-right"><strong class="fontValue">Password :</strong></div>
                                    <div class="col-md-7"><input type="password" class="form-control"  id="user_password"  name="user_password" placeholder="Password"></div>
                                </div>
                                <div class="row"  style="margin-top:10px;">
                                    <div class="col-md-4 text-right"><strong class="fontValue">Confirm Password :</strong></div>
                                    <div class="col-md-7"><input type="password" class="form-control"  id="user_password_cnfrm"  name="user_password_cnfrm" placeholder="Confirm Password"></div>
                                </div>
                                <div class="col-md-7 col-md-offset-4">
                                    <h5 class="pull-left">
                                        <input type="checkbox" value="Y" name="occasional_update" id="occasional_update" checked />
                                        Yes, please send me occasional updates,training materials, or marketing from ArchiveInBox
                                    </h5>
                                </div>
                                <!--div class="col-md-7 col-md-offset-4">
                                    <h5 class="pull-left">
                                        <input type="checkbox" value="Y" name="term_service" id="term_service" >
                                        I agree to the <a href="javascript:service_term_popup();" style="color:#72afd2;">Terms of service</a>
                                    </h5>
                                </div-->
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
                                                                    <button type="button" class="btn btn-success  borderRadiusNone" id="agreeTermButton">Yes I agree</button>
                                                                    <button type="button" class="btn btn-success  borderRadiusNone closepopup" id="notAgreeTermButton">No I do not agree</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <a class="login_link" href="javascript:UpdateUserPassword();"  style="background: #fbd42f;color: #15345a;padding: 10px 40px;display: inline-block;font-size: 16px;text-decoration: none; margin-top: 10px;border-radius: 5px;">Confirm</a>
                                <div class="col-md-4 text-right"></div>
                            </div> 
                            <div style="margin-top:20px;">
                               <!-- <a class="login_link_page login_link"   href="<?php echo HOST_PATH . 'admin/login.html' ?>"  style="background: #fbd42f;color: #15345a;padding: 10px 40px;display: inline-block;font-size: 16px;text-decoration: none; margin-top: 10px;border-radius: 5px;display:none">Click here to login</a>-->
                            </div>
                            <!--<br />
                            <br />--><span class="without_user" id="forget_msj" style="display:none">Thank You</span></div>

                    </div>
                </div>  
            </div>
            <div class="backPageLink"><a href="index.html"><img src="<?php echo IMAGE_PATH . 'back-icon.png'; ?>" alt="Thank You" /> Back to HomePage</a></div>
        </div>
    </div>
</form> 
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script>
    $(document).ready(function () {
        var user_id = '<?php echo $id[1]; ?>';
        var flg = '<?php echo $flg; ?>';
        var flg_val = '<?php echo $type_value[1]; ?>';
        if (flg_val.trim() == 'change_email' || flg_val.trim() == 'undo_email') {
            var current_email = '<?php echo $current_email; ?>';
            var old_email = ' <?php echo $old_email; ?>';
            if (current_email != '') {
                change_email(user_id, current_email, old_email);
            }
        } else {
            if (user_id != '' && user_id != 'archive_user_reg') {
                $('.navbar-custom-menu').hide();
                $('.backPageLink').hide();
                $('.without_user').hide();
                $('.login_link_page').hide();
                $.ajax({
                    type: 'post',
                    url: 'services.php',
                    data: {mode: 'active_public_user', id: user_id, flg: flg},
                    success: function (response) {
                        var result = JSON.parse(response);
<?php if ($type_value[1] == 'forget') { ?>
                            $('#pswd_div_id').show();
<?php } else { ?>
                            if (result.email_verify != 'yes') {
                                $('#pswd_div_id').show();
                            } else {
                                $('#expirelinkMsj').html('This Link has been expired !')
                            }
<?php } ?>
                    }
                });
            } else {
                $('.with_user').hide();
                $('.login_link').hide();
            }
        }
        $("#registrationOtherForm").validate({
            rules: {
                user_password: {
                    required: true
                },
                user_password_cnfrm: {
                    required: true,
                    equalTo: "#user_password"
                },
                term_service: "required"
            },
            messages: {
                user_password: {
                    required: "Please pnter password"
                },
                user_password_cnfrm: {
                    required: "Please enter confirm password",
                    equalTo: "Confim password does not match"
                },
                required: "Please accept terms of service"
            }
        });

    });

    function UpdateUserPassword() {
        if ($("#registrationOtherForm").valid()) {
            var passwordSetForm = $("#registrationOtherForm").serialize();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'User_confirm_password_Change', formData: passwordSetForm},
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.respnose == 'success') {
<?php if ($type_value[1] == 'forget') { ?>
                            $('.forget_password').show();
                            $('#forget_msj').show();
<?php } else { ?>
                            $('.without_user').show();
                            userWelcomeMessage(<?php echo $id[1]; ?>, '<?php echo $type_value[1]; ?>');
<?php } ?>
                        $('#pswd_div_id').hide();
                        $('.login_link_page').show();
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 319)');
                }
            });
        }
    }
    function change_email(user_id, current_email, old_email) {
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'user_change_email', user_id: user_id, current_email: current_email, old_email: old_email},
            success: function (data) {
                var result = JSON.parse(data);
                $('.loading-div').hide();
                if (result.status == 'success') {
                    $('.termsCondition').css('display', 'block');
                    $('#change_email').show();
                    $('#pswd_div_id').hide();
                }
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 320)');
            }
        });
    }
    function userWelcomeMessage(user_id, type = null){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'user_welcome_message', user_id: user_id, type: type},
            success: function (data) {
                //var result = JSON.parse(data); 
            }
        });

    }

</script>