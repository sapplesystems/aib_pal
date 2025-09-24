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
                <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
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
                            <div class="col-md-3" >
                                <span class="change_pass btn btn-info">Change Password</span>
                            </div>
                        </div>

                        <div class="row marginTop20 bgNone"><div class="col-md-12"><h3>Ownership Information</h3></div></div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>First Name<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="firstName" id="firstName" placeholder="Enter text">
                            </div> 
                            <div class="col-md-3" >
                                <label>Last Name<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="lastName" id="lastName" placeholder="Enter text">
                            </div> 
                        </div>

                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Phone Number<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" maxlength="14" class="form-control" name="phoneNumber" id="phoneNumber" value="" placeholder="Enter text">
                            </div> 
                            <div class="col-md-3" >
                                <label>Preferred Time Zone<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <!--<input type="text" name="preferred_time_zone" id="preferred_time_zone" value="<?php // date_default_timezone_set("Asia/Calcutta"); echo date('d-m-Y H:i:s'); ?>">-->
                                <select  name="preferred_time_zone" id="preferred_time_zone" class="form-control">
                                    <option value="">---Select---</option>
                                    <option value="UTC -11:00">UTC-11: Samoa Standard Time</option>
                                    <option value="UTC -10:00">UTC-10: Hawaii-Aleutian Standard Time (HST)</option>
                                    <option value="UTC -09:00">UTC-9: Alaska Standard Time (AKST)</option>
                                    <option value="UTC -08:00">UTC-8: Pacific Standard Time (PST)</option>
                                    <option value="UTC -07:00">UTC-7: Mountain Standard Time (MST)</option>
                                    <option value="UTC -06:00">UTC-6: Central Standard Time (CST)</option>
                                    <option value="UTC -05:00">UTC-5: Eastern Standard Time (EST)</option>
                                    <option value="UTC -04:00">UTC-4: Atlantic Standard Time (AST)</option>
                                    <option value="UTC +10:00">UTC+10: Chamorro Standard Time</option>
                                    <option value="UTC +12:00">UTC+12: Wake Island Time Zone</option>
                                </select>
                            </div> 
                        </div>

                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Mailing Address:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="physical form-control" name="mallingAddress" id="mallingAddress" value="" placeholder="Enter text">
                            </div>
                        </div>
                         <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Country<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <select class="mailing form-control user_country_list" name="country" id="country" ></select>
                                <!--<input type="text" class="mailing form-control" name="country" id="country" value="" placeholder="Enter text">-->
                            </div> 
                        </div> 
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>State<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <select class="mailing form-control user_state_list" name="mailingState" id="mailingState"></select>
                                <!--<input type="text" class="mailing form-control" name="mailingState" id="mailingState" value="" placeholder="Enter text">-->
                            </div> 
                        </div>  
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>City<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="mailing form-control" name="mailingCity" id="mailingCity" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Postal Code<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="mailing form-control" name="mailingZip" id="mailingZip" value="" placeholder="Enter text">
                            </div> 
                        </div>
                       
                        <div class="clearfix marginTop20"></div> 
                        <div class="checkboxStyle">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="Y" name="do_not_search" id="do_not_search" />
				    I do not want users to be able to search me by my username.
				</label>
			    </div>
                            <div class="checkbox">
				<label>
                                    <input type="checkbox" value="Y" name="occasional_update" id="occasional_update" />
                                    Yes, please send me occasional updates,training materials, or marketing from ArchiveInBox
                                </label>
                            </div>
                            <div class="checkbox" >
                                <label>
                                    <input type="checkbox" value="Y" name="term_service" id="term_service" >
                                    I agree to the <a href="javascript:service_term_popup();" style="color:#72afd2;">Terms of service</a>
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
                        <div class="clearfix marginTop20"></div>
                        <div class="row marginTop20 bgNone">
                            <div class="col-md-5"></div>          
                            <div class="col-md-2" >
                                <input type="button" class="form-control btn-success" name="regOtherUpdate" id="regOtherUpdate" value="Update" >
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
                            <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancel</button>
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
        get_state();
        get_country();
		var phones_num = [{ "mask": "(###) ###-####"}, { "mask": "(###) ###-##############"}];
		$('#phoneNumber').inputmask({ 
			mask: phones_num, 
			greedy: false, 
			definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
		});
	
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
                    $('#firstName').val(record.firstName);
                    $('#lastName').val(record.lastName);
                    $('#phoneNumber').val(record.phoneNumber);
                    $('#mallingAddress').val(record.mallingAddress);
                    if (record.preferred_time_zone != '') {
                        $("#preferred_time_zone option[value='" + record.preferred_time_zone + "']").attr('selected', 'selected');
                        $("#preferred_time_zone").val(record.preferred_time_zone);
                    }
                    $('#mailingCity').val(record.mailingCity);
                    $('#mailingState').val(record.mailingState);
                    $('#mailingZip').val(record.mailingZip);
                    $('#country').val(record.country);
					if (record.do_not_search == 'Y') {
			$('#do_not_search').attr('checked', true);
		    }
                    if (record.occasional_update == 'Y') {
                        $('#occasional_update').attr('checked', true);
                    }
                    if (record.term_service == 'Y') {
                        $('#term_service').attr('checked', true);
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 601)');
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
                firstName: {
                    required: true
                },
                lastName: {
                    required: true
                },
                email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                phoneNumber: {
                    required: true,
                    minlength: 10,
                    maxlength: 14
                }, 
                preferred_time_zone: {
                    required: true
                },
                mailingCity: {
                    required: true,
                },
                mailingState: {
                    required: true
                },
                mailingZip: {
                    required: true,
                    number: true
                },
                country: {
                    required: true
                },
                term_service: {
                    required: true
                }
            },
            messages: {
                firstName: {
                    required: "Please enter first name"
                },
                lastName: {
                    required: "Please enter last name"
                },
                email: {
                    required: "Please enter email Id", 
                    email: "Please enter valid email Id "
                },
                phoneNumber: {
                    required: "Phone number is required",
                    number: "Please enter valid Phone Number"
                }, 
                preferred_time_zone: {
                    required: "Please select time zone"
                },
                mailingCity: {
                    required: "Please enter mailling city"
                },
                mailingState: {
                    required: "Please enter mailling state"
                },
                mailingZip: {
                    required: "Please enter postal code",
                    number: "Only numbered is allowed"
                },
                country: {
                    required: "Please enter country name"
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
                data: {mode: 'update_user_details', formData: updateUserData},
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
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 602)');
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
                        showPopupMessage(record.status, 'Invalid old password. (Error Code: 603)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 604)');
                    $('.loading-div').hide();
                }
            });
        }
    })
    function get_state(){
         $('.loading-div').show();
         var parent_id = '<?php echo STATE_PARENT_ID; ?>';
         $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_state_country', parent_id:parent_id},
                success: function (response) { 
		    var record = JSON.parse(response);
                    var i;
                    var user_state = "";
                   
                    user_state += "<option value='' >---Select---</option>";
                    for (i = 0; i < record.length; i++) {
                       user_state +="<option value='" + record[i] + "'  >" + record[i] + "</option>";
                    }
                        $(".user_state_list").html(user_state);
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 605)');
                    $('.loading-div').hide();
                }
         });
    }
    function get_country(){
         var parent_id = '<?php echo COUNTRY_PARENT_ID; ?>';
         $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_state_country', parent_id:parent_id},
                success: function (response) { 
		    var record = JSON.parse(response);
                    var i;
                    var user_country= "";
                    user_country += "<option value='' >---Select---</option>";
                    for (i = 0; i < record.length; i++) {
                            user_country += "<option value='" + record[i] + "' >" + record[i] + "</option>";
                     }
                     $(".user_country_list").html(user_country);
                    $('.loading-div').hide();
                   
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 606)');
                    $('.loading-div').hide();
                }
         });
    }
</script>
