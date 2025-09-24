<?php 
session_start();
if (!empty($_SESSION['aib']['user_data'])) {
    header('Location: index.php');
    exit;
} 
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH.'guest_header.php';
// function aibServiceRequest($postData, $fileName, $mail = null) {
//     $curlObj = curl_init();
//     // Create data for C-url
//     $options = array(
//         CURLOPT_POST => 1,
//         CURLOPT_HEADER => 0,
//         CURLOPT_URL => AIB_SERVICE_URL . '/api/' . $fileName . ".php",
//         CURLOPT_FRESH_CONNECT => 0,
//         CURLOPT_RETURNTRANSFER => 1,
//         CURLOPT_FORBID_REUSE => 0,
//         CURLOPT_TIMEOUT => 300,
//         CURLOPT_POSTFIELDS => http_build_query($postData)
//     );
//     // Set options for c-url
//     curl_setopt_array($curlObj, $options);
//     // Execute c-url request
//     $result = curl_exec($curlObj);
//     if ($result == false) {
//         $outData = array("status" => "ERROR", "info" => curl_error($curlObj));
//     } else {
//         $outData = json_decode($result, true);
//     }
//     curl_close($curlObj);
//     if (isset($outData['info']) && $outData['info'] == 'EXPIRED') {
//         generateSession();
//     }
//     return($outData);
// }
// if (!isset($_SESSION['aib']['session_key']))
// {
//     $postData = array(
//         "_id" => APIUSER,
//         "_key" => APIKEY
//     );

//     $apiResponse = aibServiceRequest($postData, 'session');
//     if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '')
//     {
//         $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
//     }
// }
// else
// {
//     $sessionKey = $_SESSION['aib']['session_key'];
// }
// $responseData = array('status' => 'error', 'message' => 'Some things went wrong! Please try again.');
//          $postData = array(
//             "_key" => APIKEY,
//             "_session" => $sessionKey,
//             "_user" => 1,
//             "_op" => "list",
//             "parent" => SECURITY_QUESTION_PARENT_ID
//         );
// $apiResponse = aibServiceRequest($postData, 'browse');
// // echo "<pre>";print_r($apiResponse);die;
// $questions = $apiResponse['info']['records'];
$questions = [];
?>

<div class="login-box">
  <div class="login-logo"> <img src="<?php echo IMAGE_PATH . 'logo.png'; ?>" alt="" /> </div>
  <div class="login-box-body">
    <p class="login-box-msg">Admin Login</p>
    <label class="" id="response_message"></label>
    <div id="login_form_section">
    <form action="login_api.php" method="post" name="login_form" id="login_form" autocomplete="off">
      <div class="form-group has-feedback"> <span class="form-control-feedback"><img style="margin-top:-4px;" src="<?php echo IMAGE_PATH . 'mail-icon.png'; ?>" alt="" /></span>
        <input type="text" class="form-control" placeholder="Enter username" name="username" id="username" autocomplete="off">
      </div>
      <input type="text" id="field_check" name="field_check" value="" style="display:none">
      <input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
      <div class="form-group has-feedback"> <span class="form-control-feedback"><img style="margin-top:-4px;" src="<?php echo IMAGE_PATH . 'password-icon.png'; ?>" alt="" /></span>
        <input type="password" class="form-control" placeholder="Enter password" autocomplete="off" name="password" id="password">
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="checkbox icheck"> </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 btnYellow"> <div class="row">
						
                        
          <div class="col-xs-6"><a class="btn btn-primary backHome pull-right" href="<?php echo HOST_PATH; ?>" >Go to site home</a></div> 
          <div class="col-xs-6">
            <button type="button" class="btn btn-primary btn-block btn-flat" name="admin_login" id="admin_login" value="Sign In"><img class="login-loading" style="height: 18px; display:none;" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /> Sign In</button>
          </div>
        </div>
      </div>
      <div class="col-md-12 text-right">
        <div class="forgot topMargin20"> <a href="javascript:void(0);" id="forget_password">Forgot your password ?</a> </div>
      </div>
      </div>
    </form>
  </div>
  <div id="forget_form_section" style="display:none;">
    <form action="" method="post" name="userforgetpswdForm" id="userforgetpswdForm" autocomplete="off">
      <label>Forget Your Password ?</label>
      <div class="form-group has-feedback"> <span class="form-control-feedback"><img style="margin-top:-4px;" src="<?php echo IMAGE_PATH . 'mail-icon.png'; ?>" alt="" /></span>
        <input type="text" class="form-control" placeholder="Enter user name  to reset your password." name="forget_user_id" id="forget_user_id" autocomplete="off">
      </div>
      <?php if(!empty($questions) && count($questions) > 0){
        $i=1;
        foreach($questions as $key => $question) { 
      ?>
      <label><?=$question['item_title'];?></label>
      <div class="form-group has-feedback">
        <input type="hidden" name="question_id[]" id="question_id_<?=$i;?>" value="<?=$question['item_id'];?>"/>
        <input type="text" class="form-control" placeholder="Enter your answer." name="answer[]" id="answer_<?=$i;?>" autocomplete="off">
      </div>
      <?php $i++;} } ?>
      <div class="row">
        <div class="col-xs-6">
          <div class="checkbox icheck"> </div>
        </div>
        <input type="text"  id="field_forgetpsw_form" name="field_forgetpsw_form" value="" style="display:none" >
        <input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
        <div class="col-xs-12 btnYellow">
          <div class="col-xs-5">
            <!--<label class="pull-right login-loading" style="display:none;"><img style="height: 30px;" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></label>-->
            <button type="button" class="btn btn-primary btn-block btn-flat" name="user_forget_button" id="user_forget_button"><img class="login-loading" style="height: 18px; display:none;" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /> Submit</button>
          </div>
          <div class="col-xs-5">
            <button type="button" class="btn btn-primary btn-block btn-flat"  id="back_login">Back</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> 
<script type="text/javascript">
    $(document).ready(function(){
	$('#forget_form_section').hide();
	 
       $('#admin_login').click(function(){
        $("#login_form").submit();
       if($("#login_form").valid() && $('#field_check').val() ==''  ){
           $('.login-loading').show();
			var loginFormData = $("#login_form").serialize();
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'login_user', formData: loginFormData},
				success: function (data){
					 var result = JSON.parse(data);
          //  console.log('result--',result);
					 if(result.status == 'success'){
            // console.log(result.session_data);
            // console.log(result.redirect_url);return false;
						// window.location.href = result.redirect_url;
            setTimeout(() => {
                window.location = result.redirect_url;
            }, 300); // slight delay lets server write session
					 }else{
						var message = result.message.split(':');
						$('#response_message').addClass(result.status);
						$('#response_message').html(message);
						$('#response_message').show();
						$('.login-loading').hide();
					 }
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 477)');
				}
			}); 
        }  
	});
        //Validate login form
        $("#login_form").validate({
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
		
    });
	
    
	
	$(function(){
		 $('#password').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  { 
			$('#admin_login').click();
		  }
		});
	});
$(document).on('click', '#forget_password', function () {
	$('#login_form_section').hide();
	$('#forget_form_section').show();  
});
$(document).on('click', '#back_login', function () {
	$('#login_form_section').show();
	$('#forget_form_section').hide();
});
$(document).on('click', '#user_forget_button', function(){
	
	if ($("#userforgetpswdForm").valid() &&  $('#field_forgetpsw_form').val() =='') {
		$('.login-loading').show();
		 var forgetpswdFormData = $("#userforgetpswdForm").serialize();  
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'forget_password_email', formData: forgetpswdFormData},
				success: function (data) {
				$('.login-loading').hide();
					var result = JSON.parse(data);
					if (result.status == 'success') {
            window.location.href = result.message;
					}
					else{ 
						alert(result.message);
					 }
				},
				error: function () {
					alert('Something went wrong, Please try again.');
				}
			}); 
	}
});


</script>
<?php include_once COMMON_TEMPLATE_PATH.'guest_footer.php'; ?>
