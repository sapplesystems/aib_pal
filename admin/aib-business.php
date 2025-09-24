<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
$uri = $_SERVER['REQUEST_URI'];
$str_url = explode("?", $uri);
$val = urldecode($str_url[1]);
echo $val;
$url = explode("&", $val);
$id = explode("=", $url[0]);
$type_value = explode("=", $url[1]);
$flg = 0;
if (isset($url[1])) {
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
if ($id[1] == 'archive_user_reg' ) {
    $society_hide = '';
}

?>

<div class="clearfix"></div>
<form name="registrationOtherForm" id="registrationOtherForm" method="post" action="">
    <input type="hidden" name="user_login_id" id="user_login_id" value="<?php echo $id[1]; ?>">
    <div class="content bgBusiness" style="min-height: 700px;">
        <div class="bgBusiness_overlay">
            <div class="container"> 
                <div class="row marginTop20" id="display_message" style="display:block;">
                    <div class="col-md-12">
                        <div class="businessText">
                            <h3 class="colorYellow"><span class="glyphicon glyphicon-briefcase top3" aria-hidden="true"></span> ArchiveInABox for Business</h3>
                            <p class="marginTop20">Businesses can use ArchiveInABox to publish public documents such as publications, reports, research, press releases, product sales & support sheets, manuals, warranties, parts catalogues, photographs, historic marketing such as print advertising, and link directly to audio & video channel content. </p>
                            <ul class="business_listing">
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;Publish all your public archives in one place</li>
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;Customize your pages using premade templates</li>
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;Low-cost and easy to manage</li>
                            </ul>
                            <p class="marginTop30">The scalable graph architecture and modular design offers many advantages:</p>
                            <ul class="business_listing">
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;Highly flexible organization and structure</li>
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;Quickly retrieve connected information</li>
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;Integrated OCR in 40+ languages</li>
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;High speed search engine</li>
                            <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;API available (request documentation)</li>
                            </ul>
                            <p class="marginTop30">To begin using <span class="colorYellow">ArchiveInABox for Business</span>, please <a class="contactBusiness contactPage" href="javascript:formShowBusiness();">Contact us</a></p>
                            </div>

                    </div>
                </div>  
            </div>
           <div class="backStylePeople"><a class="backtoLink pull-right marginTop20" href="index.html"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Home Page</a></div>
        </div>
    </div>
</form>
<div class="clearfix"></div>
<div class="modal fade" id="contactBusinessAIB" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Contact Us <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
				 
            </div>
            <div class="modal-body">
                <div class="">
                <p>* = Required Field</p>
                <form class="form-horizontal" name="contact_aib_business_form" id="contact_aib_business_form" method="POST" action="">
                    <input type="hidden" name="request_type"   value="CT">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_first_name" id="contact_first_name" placeholder="">
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_last_name" id="contact_last_name" placeholder="">
                            </div>
                        </div>
						<input type="text" id="field_contact_bus_us" name="field_contact_bus_us" value="" style="display:none"> 
						<input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email Address* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_email" id="contact_email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Subject* :</label>
                            <div class="col-sm-7">
                                 <input type="text" class="form-control" name="contact_subject" id="contact_subject" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Your Message :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="contact_comments" name="contact_comments" rows="4"></textarea>
                            </div>
                        </div> 
                    </form>
					
                        <div class="text-center"><button class="btn btn-success" id="submit_contact_request">SUBMIT REQUEST</button></div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script> 
jQuery.validator.addMethod("validEmail", function (value, element) {
    return this.optional(element) || /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i.test(value);
	}, "Please Enter Valid EmailId");
	

 $('#contact_aib_business_form').validate({
             rules: {
                contact_first_name: {
                    required: true
                },
                contact_last_name: {
                    required: true
                },
                contact_email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                contact_subject: {
                    required: true
                },
                contact_comments: {
                    required: true
                }
             },
             messages: {
                 contact_first_name: {
                    required: "First name is required."
                },
                contact_last_name: {
                    required: "Second name is required."
                },
                contact_email: {
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                },
                contact_subject: {
                    required: "Subject is required"
                },
                contact_comments: {
                    required: "Comments is required."
                }
             }
         });
		 
$(document).on('click','#submit_contact_request', function(e){
    e.preventDefault();
    if($("#contact_aib_business_form").valid()  && $('#field_contact_bus_us').val() ==''){ 
        $('.custom-captcha_error').hide();
        $('.loading-div').show(); 
        var formData = $('#contact_aib_business_form').serialize();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'submit_request',formData: formData },
            success: function (response) {
                $('.loading-div').hide();
                var result = JSON.parse(response);
                if(result.status == 'success'){
                    $('#contact_aib_business_form')[0].reset();
                    $('#contactBusinessAIB').modal('hide');
                    showPopupMessage('success', result.message);
                }else{
                    showPopupMessage('error', result.message + ' (Error Code: 1173)');
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed. (Error Code: 1174)');
            }
        });
    }
});  

function formShowBusiness(){
	$('#contactBusinessAIB').modal('show');
}
</script>
