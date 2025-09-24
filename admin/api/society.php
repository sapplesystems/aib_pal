<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
$folder_id = $_REQUEST['folder-id'];
?>
<style>
    *{margin:0px auto; padding:0px;}
</style>
<div>
<div class="header_img">
    <div class="clientLanding"></div>
    <div class="clientLogo"><img id="client_logo" style="width:200px;" src="" /></div>
</div>
<div class="row-fluid bgMap">
        <div class="col-md-4 col-sm-6 col-xs-12 text-center" id="archive_map_section">
            <img class="img300" src="" />
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 marginTopSociety">
            <h4 class="aboutSociety"><span id="archive_title"></span></h4>
            <div class="contact-society"><button class="btn">Contact</button></div>
            <div class="contentHeading" id="about_archive"></div>
            <div class="entryDate" id="archive_address"></div>
            
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="laptop"><a href="home.php?folder_id=<?php echo $folder_id; ?>"><img src="<?php echo IMAGE_PATH . 'enter-button.png'; ?>" alt="" /></a></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>


<div class="row-fluid">
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="leftModule"><img id="client_content_image" src="" alt="" /></div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="rightModule">
                <div class="accordion_container">
                    <div class="accordion_head"><img src="<?php echo IMAGE_PATH . 'contest-info.png'; ?>" alt="" /> &nbsp;CONTACT INFO<span class="plusminus">-</span></div>
                    <div class="accordion_body" style="display: block;">
                        <p><b>Contact No : </b> <span id="archive_contact_number"></span></p>
                        <p><b>Website : </b><a  id="archive_website_url" target="_blank"><span id="archive_website"></span></a></p>
                    </div>
                    <div class="accordion_head"><img src="<?php echo IMAGE_PATH . 'entry-fees.png'; ?>" alt="" /> &nbsp;DETAIL DESCRIPTION<span class="plusminus">+</span></div>
                    <div class="accordion_body" style="display: none;">
                        <p id="detail_description"></p>
                    </div>
                    <div class="accordion_head"><img src="<?php echo IMAGE_PATH . 'special-instructions.png'; ?>" alt="" /> &nbsp;TIMINGS<span class="plusminus">+</span></div>
                    <div class="accordion_body" style="display: none;">
                        <p><b>Timing : </b><span id="archive_timing"></span></p>
                    </div>
                    <div class="accordion_head"><img src="<?php echo IMAGE_PATH . 'special-instructions.png'; ?>" alt="" /> &nbsp;UPCOMING EVENTS<span class="plusminus">+</span></div>
                    <div class="accordion_body" style="display: none;">
                        <p><span id="archive_upcoming_events"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="clr"></div>
</div>
</div>
<div class="modal fade" id="contactAIB" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Contact Us <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
				<h6>protected by reCAPTCHA</h6>
            </div>
            <div class="modal-body">
                <div class="">
                <p>* = Required Field</p>
                <form class="form-horizontal" name="contact_aib_form" id="contact_aib_form" method="POST" action="">
                    <input type="hidden" name="request_type" id="request_type" value="CT">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_first_name" id="contact_first_name" placeholder="">
                            </div>
                        </div>
						  <div id="recaptcha-form-2" style="display:none;"></div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_last_name" id="contact_last_name" placeholder="">
                            </div>
                        </div>
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
					
                        <div class="text-center"><button class="btn btn-success" id="submit_contact_request" onclick="contact_request_Submit()">SUBMIT REQUEST</button></div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
     
    $(document).ready(function () {
        var folder_id = '<?php echo $folder_id; ?>';
        getItemPropDetails(folder_id);
	getAdvertisement(folder_id);
        $('.accordion_body').show(); 
        $(".plusminus").html("-"); 
        $(".accordion_head").click(function () {
            if ($(this).next(".accordion_body").is(':visible')) {
                $(this).next(".accordion_body").slideUp(300);
                $(this).children(".plusminus").text('+');
            }else {
                $(this).next(".accordion_body").slideDown(300);
                $(this).children(".plusminus").text('-');
            }
        });

        $('#contact_aib_form').validate({
             rules: {
                contact_first_name: {
                    required: true
                },
                contact_last_name: {
                    required: true
                },
                contact_email: {
                    required: true,
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
        
        
    });
    function getItemPropDetails(archive_id){
        if(archive_id != ''){
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_archive_prop_details',archive_id: archive_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    var archive_image_url = '<?php echo ARCHIVE_IMAGE; ?>';
                    $('#archive_title').html(record.prop_details.archive_title);
                    $('#about_archive').html(record.prop_details.archive_about_content);
                    $('#archive_address').html('We are located at: '+record.prop_details.archive_address);
                    $('#detail_description').html(record.prop_details.archive_details_description);
                    $('#archive_contact_number').html(record.prop_details.archive_contact_number);
                    $('#archive_website').html(record.prop_details.archive_website);
                    var archive_url = record.prop_details.archive_website;
                    var pattern = /^((http|https):\/\/)/;
                    if(!pattern.test(archive_url)){
                        archive_url = "http://" + archive_url;
                    }
                    $("#archive_website_url").attr("href", archive_url);
                    $('#archive_timing').html(record.prop_details.archive_timing);
                    $('#client_logo').attr('src',archive_image_url+record.prop_details.archive_logo_image);
                    $('#client_content_image').attr('src',archive_image_url+record.prop_details.archive_details_image);
                    $('.clientLanding').css('background-image', 'url('+archive_image_url+record.prop_details.archive_header_image+')');
                    $('#archive_map_section').html('<iframe src="http://www.google.com/maps?q='+encodeURIComponent(record.prop_details.archive_address)+'&output=embed" style="height:300px;"></iframe>');
                    if(record.prop_details.archive_upcoming_events){
                        $('#archive_upcoming_events').html(record.prop_details.archive_upcoming_events);
                    }else{
                        $('#archive_upcoming_events').html("No upcoming events.");
                    }
                    $('.loading-div').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 672)');
                }
            });
        }
    }
	
	
	
function getAdvertisement(folder_id)
{
    $.ajax({
          url: "services.php",
          type: "post",
          data: {mode: 'get_advertisement',folder_id: folder_id },
          success: function (response) {
              //var record = JSON.parse(response);
          },
          error: function () {
              showPopupMessage('error','Something went wrong, Please try again. (Error Code: 673)');
          }
      });
}
$(document).on('click','.contact-society', function(){
    $('#contactAIB').modal('show');
});
/* $(document).on('click','#submit_contact_request', function(e){
    e.preventDefault();
    if($("#contact_aib_form").valid()){
        if($('#g-recaptcha-response').val() == ''){
            $('.custom-captcha_error').show();
            return false;
        }
        $('.custom-captcha_error').hide();
        $('.loading-div').show();
        var folder_id = '<?php echo $folder_id; ?>';
        var formData = $('#contact_aib_form').serialize();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'submit_request',formData: formData, item_id: folder_id },
            success: function (response) {
                $('.loading-div').hide();
                var result = JSON.parse(response);
                if(result.status == 'success'){
                    $('#contact_aib_form')[0].reset();
                    $('#contactAIB').modal('hide');
                    showPopupMessage('success', result.message);
                }else{
                    showPopupMessage('error', result.message);
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed.');
            }
        });
    }
}); */

function save_aib_contact(){
		grecaptcha.reset(widget_2);  
	   $('.custom-captcha_error').hide();
        $('.loading-div').show();
        var folder_id = '<?php echo $folder_id; ?>';
        var formData = $('#contact_aib_form').serialize();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'submit_request',formData: formData, item_id: folder_id },
            success: function (response) {
                $('.loading-div').hide();
                var result = JSON.parse(response);
                if(result.status == 'success'){
                    $('#contact_aib_form')[0].reset();
                    $('#contactAIB').modal('hide');
                    showPopupMessage('success', result.message);
                }else{
                    showPopupMessage('error', result.message + ' (Error Code: 674)');
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed. (Error Code: 675)');
            }
        });
	
}
 
</script> 