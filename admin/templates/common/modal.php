<div id="popupDiv" style="position:absolute; width:100%; height:100%; left:0; top:0; z-index:999; background:rgba(0,0,0,0.7); overflow:hidden;">
    <div class="popUpBox" id="">
    <!--<div style="background:rgba(255,255,255,0.6); position:absolute; top:0; right:0; bottom:0; left:0; width:100%; height:100%;"></div>-->
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
							<input type="text"  id="field_login_form" name="field_login_form" value="" style="display:none" > 
							<input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
						 <div id="recaptcha-form-6" style="display:none;"></div> 
                            <div class='login_fields__password'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'lock_icon.png'; ?>'>
                                </div>
                                <input type="password" name="password" class="" id="password" placeholder="Enter password" autocomplete="off">
                            </div>
                            <div class='login_fields__submit'>
                                <input type='button' name='login' id='user-login-button' value='Log In'>
                                <!--<input type='button' id='user-register-button' value='Register'>
                                <input type='button' id='create_account' value='Create Account'>-->
                                <input type='button' id='closePop' value='Cancel'>  
								  <div class='forgot'>
                                    <a href="javascript:void(0);" id="forget_password">Forgot your password ?</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="clearfix"></div>
                    <div class="row marginTop65">
                    <div class="col-md-3 col-sm-3"></div>
                    <div class="col-md-6 col-sm-6 text-center">
                <a href="javascript:void(0);" id="create_my_own_box" title="Create my own box" class="opacityBox">
                    <div class="img-6 heightBox120"></div>
                    <h3 class="text-center bgCreateBox">Create my own box</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-3"></div>
            		</div>
                    
                </div>
				
				<div id="forget_form_section">
					 <form class="form-horizontal" name="userforgetpswdForm" id="userforgetpswdForm" action="" method="POST" autocomplete="off">
                        <div class='login_title'>
                            <span>Forgot Your Password ?</span>
                        </div>
                        <div class='login_fields'>
                            <div class='login_fields__user'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'user_icon.png'; ?>'>
                                </div>
                                <input type="text" class="" name="forget_user_id" id="forget_user_id" placeholder="Enter user name  to reset your password. " autocomplete="off">
                            </div>  
							<input type="text"  id="field_forgetpsw_form" name="field_forgetpsw_form" value="" style="display:none" > 
							<input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
                            <div class='login_fields__submit'>
                                <input type='button' name='register' id='user_forget_button' value='Submit'>
                                <input type='button' class='back_login' value='Back'>
                                 
                            </div>
                        </div>
                    </form>
                </div>
				
				<div class="create_account_user" style="display:none">
					 <form class="form-horizontal" name="createMyOwnBoxForm" id="createMyOwnBoxForm" action="" method="POST" autocomplete="off">
                        <div class='login_title'>
                            <span>Create My Own Box</span>
                        </div>
                        <div class='login_fields'>
                            <div class='login_fields__user'> 
								<select name="select_who_you_are"   class="form-control select_who_you_are" style="height:auto">
									<option value="0">Please select who you are ?</option> 
									<option value="historical">Historicals+Museums</option>
									<option value="newspapers">Newspapers+Periodicals</option>
									<option value="commercial">Commercial</option>
									<option value="people">People</option>
								</select>
                            </div>   
							
                            <div class='login_fields__submit'> 
                                <input type='button' class='back_login' value='Back'>
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
	 jQuery.validator.addMethod("validEmail", function (value, element) {
    return this.optional(element) || /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i.test(value);
	}, "Please Enter Valid EmailId");
	
        $(document).on("click", ".loginPopup", function () {
            $("#popupDiv").show();
            $('#login_form_section').show();
            $('#forget_form_section').hide();
        });
        $(".cancelPopUp, .cancel").click(function () {
            $("#popupDiv").hide();
        });
        $('#login_form_section').show(); 
        $('#user-register-button').hide(); 
		$('#forget_form_section').hide();
        
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
        
		$("#userforgetpswdForm").validate({
            rules: {
               forget_user_id : {
                    required: true 
                }
            },
            messages: {
            forget_user_id: {
                    required: "Please enter Username" 
                }
            }
        });
		
		$('#password').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  { 
			$('#user-login-button').click();
		  }
		}); 
 
    });

$(document).on('click', '#closePop', function () {
	$('.cancelPopUp').click(); 
});
$(document).on('click', '#forget_password', function () {
	$('#login_form_section').hide();
	$('#forget_form_section').show();  
});
$(document).on('click', '.back_login', function () {
	$('#login_form_section').show();
	$('#forget_form_section').hide();
	$('.create_account_user').hide();
}); 

$(document).on('click', '#create_account', function () {
	$('.create_account_user').show();
	$('#login_form_section').hide();
});
	
$(document).on('click', '#user_forget_button', function(){
	
	if ($("#userforgetpswdForm").valid() &&  $('#field_forgetpsw_form').val() =='') {
		$('.loading-div').show();
		 var forgetpswdFormData = $("#userforgetpswdForm").serialize();  
			$.ajax({
				url: "services.php",
				type: "post",
				data: {mode: 'forget_password_email', formData: forgetpswdFormData},
				success: function (data) {
				$('.loading-div').hide();
					var result = JSON.parse(data);
					if (result.status == 'success') {
						showPopupMessage('success',result.message); 
					}
					else{
						showPopupMessage('error',result.message + ' (Error Code: 872)');
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 873)');
				}
			}); 
	}
});

$(document).on('click', '#user-login-button', function(){
     var page_name = '<?php echo (URLPAGENAME)?URLPAGENAME:'' ; ?>'
	if ($("#userLoginForm").valid() &&  $('#field_login_form').val() =='') {
            $('.loading-div').show();
		 var loginFormData = $("#userLoginForm").serialize();
			$.ajax({
				url: "services.php",
				type: "post",
				data: {mode: 'login_user', formData: loginFormData},
				success: function (data) {
                                    $('.loading-div').hide();
					var result = JSON.parse(data);
					if (result.status == 'success') {
                                            if(page_name == 'thank-you'){
                                               window.location.href = 'index.html'; 
                                            }else{
                                              location.reload();  
                                            }
					} else {
						$('#response_message').addClass(result.status);
						$('#response_message').html(result.message);
						$('#response_message').show();
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 874)');
				}
			});
	}
});

$(document).on('change', '.select_who_you_are', function(){
    var selected_option = $(this).val();
    if(selected_option != ''){ 
        if(selected_option == 'historical'){
            window.location.href = 'register.html';
        }
		else if(selected_option == 'newspapers'){
                     window.location.href = 'interstitial-page.html';
        }
		else if(selected_option == 'commercial'){
                        window.location.href = 'aib-business.html';
        }
		else{ 
		   window.location.href = 'registration_user.html';
        }
    }
});
</script>