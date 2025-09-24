<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';

if (isset($_POST) && $_POST['society_name'] != "") {
    unset($_SESSION['data']);

    $_POST['register_user_password'] = "test";
    $_SESSION['data'] = $_POST;
    echo "<script>window.location.href='register-step2.html'</script>";
    exit;
}
?>
<style>
mark {
	background-color: #fbd42f !important;
}
</style>
<div class="header_img">
  <div class="bannerImage"></div>
</div>
<div class="clearfix"></div>
<form class="form-horizontal" name="userRegistrationForm" id="userRegistrationForm" action="" method="POST" autocomplete="off">
  <input type="hidden" name="user_type" value="A">
  <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
    <div class="container" id="register_data">
      <div class="row marginTop20 bgNone">
        <h3>SignUp to your account</h3>
      </div>
      <div class="row marginTop20 padd5">
        <div class="col-md-3" >
          <label>User Name <span>*</span>:</label>
        </div>
        <div class="col-md-3" >
          <input type="text" class="form-control" name="register_username" id="register_username" value="" placeholder="Enter username">
        </div>
      </div>
      <input type="text"  id="field_register_form" name="field_register_form" value="" style="display:none">
      <input type="text"  name="timestamp_value" value="<?php echo time(); ?>" style="display:none">
      <div class="row marginTop20 padd5">
        <div class="col-md-3" >
          <label>Email ID <span>*</span>:</label>
        </div>
        <div class="col-md-3" >
          <input type="text" class="form-control" name="register_email" id="register_email" value="" placeholder="Enter email Id">
        </div>
      </div>
      <div class="row marginTop20 bgNone">
        <div class="col-md-9">
          <div class="checkbox">
            <label>
              <input type="checkbox" value="Y" name="occasional_update" id="occasional_update" checked />
              Yes, please send me occasional updates,training materials, or marketing from ArchiveInBox </label>
          </div>
        </div>
      </div>
      <div class="row marginTop20 bgNone">
        <div class="col-md-9">
          <div class="checkbox" >
            <label>
              <input type="checkbox" value="Y" name="term_service" id="term_service" >
              I agree to the <a href="javascript:service_term_popup();" style="color:#72afd2;"> Terms of service</a> </label>
          </div>
        </div>
      </div>
      <div class="row bgNone termsCondition" style="display:none;">
        <div class="col-md-6">
          <div class="" id="term_of_services">
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
       
      <div class="clearfix marginTop20"></div>
      <div class="row">
        <div class="col-md-12" id="search_result_render_space" style="display:none;">Loading....</div>
      </div>
    </div>
    <div class="row bgNone marginBottom40">
    <div class="col-md-5"></div>
        <div class="col-md-2">
          <input type="button" class="form-control btn-success marginTop20" name="register" id="user_register_button" value="Register" >
        </div>
        <div class="col-md-5"></div>
      </div>
    </div>
    <div class="container marginTop20" id="success_msg" style="display: none;">
      <div  class="alert alert-success" role="alert"> <strong>Congratulations! </strong> Your ArchiveInABox has been created. Check your email for instructions on validating your account. </div>
      <div>
        <center>
          <a href="admin/login.html" class="form-control btn-success" name="register" id="user_register_button" style="width: auto !important;display: inline-block;"> Log In To Your Box</a>
        </center>
      </div>
    </div>
  </div>
</form>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
		jQuery.validator.addMethod("validEmail", function (value, element) {
		return this.optional(element) || /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i.test(value);
		}, "Please Enter Valid EmailId");

         $.validator.addMethod("regex",function (value, element, regexp) {
                    var check = false;
                    return this.optional(element) || regexp.test(value);
                }, "Please check your input."
                );
        //Validate registration form
        $("#userRegistrationForm").validate({
            rules: {
                register_username: {
                    required: true,
                    regex: /^[a-zA-Z0-9_]{1,40}$/
                },
                register_email: {
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
                register_username: {
                    required: "Username is required.",
                    regex: "Only alphanumeric and _ allowed"
                },
                register_email: {
                    required: "Email is required",
                    email: "Please enter valid email"
                },
                term_service: {
                    required: "Please accept terms of service"
                }
            }
        });
	
		$('#register_email').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  { 
			$('#user_register_button').click();
		  }
		}); 

    });
    $(document).on('click', '#user_register_button', function () {
        if ($("#userRegistrationForm").valid() && $('#field_register_form').val() == '') {
            $('.loading-div').show();
            var registerFormData = $("#userRegistrationForm").serialize();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'register_normal_user', formData: registerFormData},
                success: function (result) {
                    var result = JSON.parse(result);
					$('.loading-div').hide();
                    if (result.status == 'success') {
                        $("#popupDiv").hide();
                        $('#userRegistrationForm')[0].reset();
                        $('#register_data').hide();
                        $('#success_msg').show();
                        showPopupMessage('success', result.message);
                      /*   setTimeout(function () {
                            window.location.reload(1);
                        }, 5000); */
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 623)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 624)');
                }
            });
        }
    });
 	
</script>