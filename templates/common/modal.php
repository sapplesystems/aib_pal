
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
                                <!-- Fix start on 01-July-2025 -->
								  <div class='marginTop20'>
                                     <a href="javascript:void(0);" id="forget_password_new">Forgot your password ?</a>
                                    <!--p style="font-size:14px;font-weight:bold;">Forgot your password? Please contact your administrator.</p-->
                                </div>
                                <!-- Fix start on 01-July-2025 -->
                            </div>
                        </div>
						<!--/*** Fix start for issue Id 2392 22-07-2024 fixed by SS **********/-->
						 <div class="form-row recaptcha-none" style=" padding-top: 55px;padding-left: 40px;">
							 <div class="g-recaptcha"
							 data-sitekey="<?php echo RC_SITE_KEY; ?>"
							 data-badge="inline" data-size="invisible"
							 data-callback="setResponse"></div>
							 <input type="hidden" class="captcha-response" name="captcha-response" />
						 </div>
						<!--/*** Fix End for issue Id 2392 22-07-2024 fixed by SS **********/-->
                    </form>
                    <div class="clearfix"></div>
                    <div class="row marginTop40 create-none">
                    <div class="col-md-6 col-sm-6 text-center" style="padding-left: 55px;">
                <a href="javascript:void(0);" id="create_my_own_box" title="Create my own box" class="opacityBox">
                    <div class="img-6 heightBox120" style="height: 100px !important;background-size: contain;"></div>
                    <h3 class="text-center bgCreateBox" style="font-size: 16px;padding: 10px;margin-bottom: 40px;">Create my own box</h3>
                </a>
            </div>
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
						 <!--/*** Fix start for issue Id 2392 22-07-2024 fixed by SS **********/-->
						 <div class="form-row" style=" padding-top: 55px;">
							 <div class="g-recaptcha"
							 data-sitekey="<?php echo RC_SITE_KEY; ?>"
							 data-badge="inline" data-size="invisible"
							 data-callback="setResponse"></div>
							 <input type="hidden" class="captcha-response" name="captcha-response" />
						 </div>
						<!--/*** Fix End for issue Id 2392 22-07-2024 fixed by SS **********/-->	
                    </form>
                </div>
                <div id="forget_form_section_new">
					 <form class="form-horizontal" name="userforgetpswdFormNew" id="userforgetpswdFormNew" action="" method="POST" autocomplete="off">
                        <div class='login_title'>
                            <span>Forgot Your Password ?</span>
                        </div>
                        <div class='login_fields'>
                            <div class='login_fields__user'>
                                <div class='icon'>
                                    <img src='<?php echo IMAGE_PATH.'user_icon.png'; ?>'>
                                </div>
                                <div class="marginBottom10">
                                    <input type="text" class="" name="forget_user_id" id="forget_user_id" placeholder="Enter user name  to reset your password. " autocomplete="off">
                                </div>
                                <div id="question_list"></div>
                            </div>  
							<input type="text"  id="field_forgetpsw_form" name="field_forgetpsw_form" value="" style="display:none" > 
							<input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
                            <div class='login_fields__submit'>
                                <input type='button' name='register' id='user_forget_button_new' value='Submit'>
                                <input type='button' class='back_login' value='Back'>
                                 
                            </div>
                        </div>
						 <!--/*** Fix start for issue Id 2392 22-07-2024 fixed by SS **********/-->
						 <div class="form-row" style=" padding-top: 55px;">
							 <div class="g-recaptcha"
							 data-sitekey="<?php echo RC_SITE_KEY; ?>"
							 data-badge="inline" data-size="invisible"
							 data-callback="setResponse"></div>
							 <input type="hidden" class="captcha-response" name="captcha-response" />
						 </div>
						<!--/*** Fix End for issue Id 2392 22-07-2024 fixed by SS **********/-->	
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

                <!--------------------------create popup for security questions & answers---------------------------------------------------------------------------->
                <div class="security_ques_ans" style="display:none">
					 <form class="form-horizontal" name="createQuestionAnswerForm" id="createQuestionAnswerForm" action="" method="POST" autocomplete="off">
                        <div class='login_title'>
                            <span>Create Security Questions Answers </span>
                        </div>
                        <div class='login_fields'>
                            <div class='login_fields__q' id="question_div_id"> 
                               
                            </div>   
							
                            <div class='login_fields__submit'> 
                                <input type='button' name='register' id='security_qa' value='Submit'>
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
			/*fix start for issue ID 2406 12-Nov-2024 ***********/
			grecaptcha.execute();
			/*fix end  for issue ID 2406 12-Nov-2024 ***********/
            $("#popupDiv").show();
            $('#login_form_section').show();
            $('#forget_form_section').hide();
            $('#forget_form_section_new').hide();
            $('.security_ques_ans').hide();
        });
        $(".cancelPopUp, .cancel").click(function () {
            $("#popupDiv").hide();
            $('#question_div_id').html('');
        });
        $('#login_form_section').show(); 
        $('#user-register-button').hide(); 
		$('#forget_form_section').hide();
        $('#forget_form_section_new').hide();
        $('.security_ques_ans').hide();
        
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

        $("#userforgetpswdFormNew").validate({
            rules: {
               forget_user_id : {
                    required: true 
                },
                answer_1: {
                    required: true,
                    validAnswer: true
                },
                answer_2: {
                    required: true,
                    validAnswer: true
                }
            },
            messages: {
            forget_user_id: {
                    required: "Please enter Username" 
                },
            answer_1: {
                    required: "Answer is required",
                    validAnswer: "Only alphabets, numbers, hyphens, periods, apostrophes, commas are allowed."
                },
            answer_2: {
                    required: "Answer is required",
                    validAnswer: "Only alphabets, numbers, hyphens, periods, apostrophes, commas are allowed."
                }
            }
        });

        $("#createQuestionAnswerForm").validate({
                rules: {
                    'answer[]': {
                        required: true,
                        validAnswer: true
                    },
                    
                },
                messages: {
                    'answer[]': {
                        required: "Answer is required",
                        validAnswer: "Only alphabets, numbers, hyphens, periods, apostrophes, commas are allowed."
                    },
                   
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
     $('.security_ques_ans').hide();
});
$(document).on('click', '#forget_password_new', function () {

    //get question list
    $.ajax({
		url: "services.php",
		type: "post",
		data: {mode: 'get_questions_list'},
		    success: function (data) {
                $('.loading-div').hide();
                var result = JSON.parse(data);
                if (result.status == 'success') {
                     var questionHTML='';
                     var questions_list = result.data;
                    $.each(questions_list, function (index, questionText) {
                        var questionNumber = index + 1;
                        var $questionHTML = $(`<div class="marginBottom10">
                                    <label>Q${questionNumber}. ${questionText['item_title']}</label>
                                    <input type="text" class="form-control" name="answer[]" id="answer_${questionNumber}" placeholder="Enter answer" autocomplete="off">
                                    <input type="hidden" name="question_id[]" id="question_id_${questionNumber}" value="${questionText['item_id']}"/>
                                </div> `);

                                   
                        $('#question_list').append($questionHTML);
                       });
                        $('#login_form_section').hide();
	                    $('#forget_form_section_new').show();  
                        $('.security_ques_ans').hide();
                }
                else{
                    showPopupMessage('error',result.message );
                }
			},
			error: function () {
				showPopupMessage('error','Something went wrong, Please try again.');
			}
	}); 
	
});
$(document).on('click', '.back_login', function () {
	$('#login_form_section').show();
	$('#forget_form_section').hide();
	$('.create_account_user').hide();
}); 

$(document).on('click', '#create_account', function () {
	$('.create_account_user').show();
	$('#login_form_section').hide();
    $('.security_ques_ans').hide();
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
						showPopupMessage('error',result.message + ' (Error Code: 1138)');
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1139)');
				}
			}); 
	}
});

$(document).on('click', '#user-login-button', function(){
    //Fix start for Issue ID 2002 on 22-Feb-2023
    // var page_name = '<?php echo (!empty(URLPAGENAME))?URLPAGENAME:'' ; ?>'
	  /************* Fix Start Issue Id 2143  18-jan-2023 ***************/
     var page_name = '<?php echo (!empty(URLPAGENAME))?URLPAGENAME:'' ; ?>'
     //Fix end for Issue ID 2002 on 22-Feb-2023
	 /************* Fix end Issue Id 2143  18-jan-2023 ***************/
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
                    var scurity_ques_ans = '';
                    var questions_list = '';
					if (result.status == 'success') {
                        scurity_ques_ans = result.data.properties.security_question_answers;
                        questions_list = result.data.question_list;
                        console.log('heell--',questions_list);
                    
                                        if(page_name == 'thank-you'){
                                            if(scurity_ques_ans == '' || scurity_ques_ans == undefined)
                                               {
                                                var questionHTML='';
                                               $.each(questions_list, function (index, questionText) {
                                                    var questionNumber = index + 1;
                                                    var $questionHTML = $(`
                                                            <div class="marginBottom10">
                                                            <label>Q${questionNumber}. ${questionText['item_title']}</label>
                                                            <input type="hidden" name="question_id[]" id="question_id_${questionNumber}" value="${questionText['item_id']}"/>
                                                            <input type="text" class="form-control" name="answer[]" id="answer_${questionNumber}" placeholder="Enter answer" autocomplete="off">
                                                        </div>
                                                        `);
                                                        $('#question_div_id').append($questionHTML);
                                                });
                                                    $("#popupDiv").show(); 
                                                    $('#login_form_section').hide();
                                                    $('.security_ques_ans').show();

                                               }else{
                                                   window.location.href = 'index.html';
                                               }
                                            }else{
                                                if(scurity_ques_ans == '' || scurity_ques_ans == undefined)
                                                {
                                                    var questionHTML='';
                                                    $.each(questions_list, function (index, questionText) {
                                                            var questionNumber = index + 1;
                                                            var $questionHTML = $(`
                                                                    <div class="marginBottom10">
                                                                    <label>Q${questionNumber}. ${questionText['item_title']}</label>
                                                                    <input type="hidden" name="question_id[]" id="question_id_${questionNumber}" value="${questionText['item_id']}"/>
                                                                    <input type="text" class="form-control" name="answer[]" id="answer_${questionNumber}" placeholder="Enter answer" autocomplete="off">
                                                                </div>
                                                                `);
                                                                $('#question_div_id').append($questionHTML);
                                                        });
                                                    $("#popupDiv").show(); 
                                                    $('#login_form_section').hide();
                                                    $('.security_ques_ans').show();

                                                }else{
                                                   
                                                    location.reload();
                                                }
                                                //location.reload();  
                                            }
					} else {
						$('#response_message').addClass(result.status);
						$('#response_message').html(result.message);
						$('#response_message').show();
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1140)');
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

//add security question answers 
$(document).on('click', '#security_qa', function(){
	
	if ($("#createQuestionAnswerForm").valid()) {
		$('.loading-div').show();
		 var forgetpswdFormData = $("#createQuestionAnswerForm").serialize();  
			$.ajax({
				url: "services.php",
				type: "post",
				data: {mode: 'security_questions_answers', formData: forgetpswdFormData},
				success: function (data) {
				$('.loading-div').hide();
					var result = JSON.parse(data);
					if (result.status == 'success') {
						//showPopupMessage('success',result.message); 
                        location.reload();
					}
					else{
						showPopupMessage('error',result.message + ' (Error Code: 1141)');
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1142)');
				}
			}); 
	}
});

//new forget password with new question answer
$(document).on('click', '#user_forget_button_new', function(){
	
	if ($("#userforgetpswdFormNew").valid() &&  $('#field_forgetpsw_form').val() =='') {
		$('.loading-div').show();
		 var forgetpswdFormData = $("#userforgetpswdFormNew").serialize();  
			$.ajax({
				url: "services.php",
				type: "post",
				data: {mode: 'forget_password_new', formData: forgetpswdFormData},
				success: function (data) {
				$('.loading-div').hide();
					var result = JSON.parse(data);
					if (result.status == 'success') {
                        window.location.href = result.message;
						//showPopupMessage('success',result.message); 
					}
					else{
						showPopupMessage('error',result.message + ' (Error Code: 1143)');
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1144)');
				}
			}); 
	}
});

// Allow only a-z, 0-9, hyphen, period, apostrophe, comma, and spaces
jQuery.validator.addMethod("validAnswer", function(value, element) {
    return this.optional(element) || /^[a-z0-9\-.,'\s]+$/i.test(value);
}, "Only alphabets, numbers, hyphens, periods, apostrophes, commas are allowed.");

</script>