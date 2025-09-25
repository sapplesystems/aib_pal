<div id="popupDiv" style="position:absolute; width:100%; height:100%; left:0; top:0; z-index:999; background:rgba(0,0,0,0.7); overflow:hidden;">
    <div class="popUpBox" id="">
        <div class="leftPanelPopup"><div class="PopUpImage"></div></div>
        <div class="rightPanelPopup">
            <img class="cancelPopUp" src="<?php echo IMAGE_PATH.'popup-cancel.png'; ?>" />
            <div class="heightScroll">
            <div class='login'>
                <div class="" id="response_message"></div>
                <div id="login_form_section">
                    <form class="" name="userLoginForm" id="userLoginForm" action="" method="POST" autocomplete="off">
                        <div class='login_title'>
                            <span>Login to your account</span>
                        </div>
                        <div class='login_fields'>
                            <div class='login_fields__user'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'user_icon.png'; ?>'>
                                </div>
                                <input type="text" class="" name="username" id="username" placeholder="Enter username" autocomplete="off">
                            </div>
                            <div class='login_fields__password'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'lock_icon.png'; ?>'>
                                </div>
                                <input type="password" name="password" class="" id="password" placeholder="Enter password" autocomplete="off">
                            </div>
                            <div class='login_fields__submit'>
                                <input type='button' name='login' id='user-login-button' value='Log In'>
                                <input type='button' id='user-register-button' value='Register'>
                                <input type='button' id='cancel' class='cancel' value='Cancel'>   
                                <div class='forgot'>
                                    <a href="javascript:void(0);" id="register_new_user">Not an account? Register here</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="register_form_section">
                    <form class="form-horizontal" name="userRegistrationForm" id="userRegistrationForm" action="" method="POST" autocomplete="off">
                        <div class='login_title'>
                            <span>SignUp to your account</span>
                        </div>
                        <div class='login_fields'>
                            <div class='login_fields__user'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'user_icon.png'; ?>'>
                                </div>
                                <input type="text" class="" name="register_username" id="register_username" placeholder="Enter username" autocomplete="off">
                            </div>
                            <div class='login_fields__user'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'user_icon.png'; ?>'>
                                </div>
                                <input type="text" class="" name="register_email" id="register_email" placeholder="Enter your email Id" autocomplete="off">
                            </div>
                            <div class='login_fields__password'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'lock_icon.png'; ?>'>
                                </div>
                                <input type="password" name="register_user_password" class="" id="register_user_password" placeholder="Enter password" autocomplete="off">
                            </div>
                            <div class='login_fields__password'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'lock_icon.png'; ?>'>
                                </div>
                                <input type="password" name="register_user_confirm_password" class="" id="register_user_confirm_password" placeholder="Enter confirm password" autocomplete="off">
                            </div>
                            <div class='login_fields__submit'>
                                <input type='button' name='register' id='user_register_button' value='Register'>
                                <input type='button' id='cancel_register' class='cancel' value='Cancel'>
                                <div class='forgot'>
                                    <a href="javascript:void(0);" id="login_user_link">Already have an account? Login Here</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        $(document).on("click", ".loginPopup", function () {
            $("#popupDiv").show();
        });
        $(".cancelPopUp, .cancel").click(function () {
            $("#popupDiv").hide();
        });
        $('#login_form_section').show();
        $('#register_form_section').hide();
        $('#login_user_link').hide();
        $('#user-register-button').hide();

        $('#user-login-button').click(function () {
            if ($("#userLoginForm").valid()) {
                var loginFormData = $("#userLoginForm").serialize();
                $.ajax({
                    url: "services.php",
                    type: "post",
                    data: {mode: 'login_user', formData: loginFormData},
                    success: function (data) {
                        var result = JSON.parse(data);
                        if (result.status == 'success') {
                            location.reload();
                        } else {
                            $('#response_message').addClass(result.status);
                            $('#response_message').html(result.message);
                            $('#response_message').show();
                        }
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again.');
                    }
                });
            }
        });
        $(document).on('click', '#user_register_button', function(){
            if ($("#userRegistrationForm").valid()) {
                var registerFormData = $("#userRegistrationForm").serialize();
                $.ajax({
                    url: "services.php",
                    type: "post",
                    data: {mode: 'register_normal_user', formData: registerFormData},
                    success: function (result) {
                        var result = JSON.parse(result);
                        if (result.status == 'success') {
                            $("#popupDiv").hide();
                            showPopupMessage('success',result.message);
                        }else{
                            showPopupMessage('error',result.message);
                        }
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again.');
                    }
                });
            }
        });
        
        //Validate login form
        $("#userLoginForm").validate({
            rules: {
                username: "required",
                password: "required"
            },
            messages: {
                username: "Username is required",
                password: "Password is required"
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
        //Validate registration form
        $("#userRegistrationForm").validate({
            rules: {
                register_username: {
                    required: true,
                    regex : /^[a-zA-Z0-9_]{1,40}$/
                },
                register_email: {
                    required: true,
                    email: true
                },
                register_user_password: "required",
                register_user_confirm_password: {
                    required: true,
                    equalTo: "#register_user_password"
                }
            },
            messages: {
                register_username:{ 
                    required: "Username is required.",
                    regex : "Only alphanumeric and _ allowed"
                },
                register_email: {
                    required: "Email is required",
                    email: "Please enter valid email"
                },
                register_user_password: "Password is required",
                register_user_confirm_password: {
                    required: "Confirm password is required",
                    equalTo: "Password and confirm password does't match"
                }
            }
        });
    });
    $(document).on('click', '#register_new_user', function () {
        $('#login_form_section').hide();
        $('#register_form_section').show();
        $('#user-login-button').hide();
        $('#user-register-button').show();
        $('#login_user_link').show();
        $('#register_new_user').hide();;
    });
    $(document).on('click', '#login_user_link', function () {
        $('#login_form_section').show();
        $('#register_form_section').hide();
        $('#login_user_link').hide();
        $('#register_new_user').show();
        $('#user-register-button').hide();
        $('#user-login-button').show();
    });
</script>