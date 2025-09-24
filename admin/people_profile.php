<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header_public.php';
$folder_id = $_REQUEST['folder-id'];
?>
<style>
    *{margin:0px auto; padding:0px;}
</style>
<div class="minHeightfull">
    <div class="header_img">
        <div class="clientLanding" style="background-size: cover !important; background-position:center 45%;"></div>
    </div>
    <div class="row-fluid bgMap">
        <div class="col-md-4 col-sm-6 col-xs-12 text-center" id="archive_map_section">
            <img class="img300" id='public_user_image' src="" />
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 marginTopSociety">
            <h4 class="aboutSociety"><span id="archive_title"></span></h4>
            <div class="contact-society"><button class="btn" id="contactUsIdBtn" style="display:none">Contact</button></div>
            <div class="contentHeading" id="about_archive"></div>
            <div class="entryDate" id="archive_address"></div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12"><div class="laptop"><a href="people.html?q=<?php echo encryptQueryString('folder_id='.$folder_id); ?>"><img src="<?php echo IMAGE_PATH . 'enter-button.png'; ?>" alt="" /></a></div>
        </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="pull-right backToArchive"><a href="people.html?q=<?php echo encryptQueryString('folder_id=3&show_text=no'); ?>" class="backtoBtn"><img src="<?php echo IMAGE_PATH . 'back-icon.png'; ?>"> Back to People List</a></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="clr"></div>
</div>
</div>
<div class="modal fade" id="contactAIB" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Contact Us <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
				 
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
						<input type="text" id="field_contact_us" name="field_contact_us" value="" style="display:none"> 
						<input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
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
                            <label for="inputEmail3" class="col-sm-4 control-label">Your Message* :</label>
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
            } else {
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
    });
    function getItemPropDetails(archive_id) {
        if (archive_id != '') {
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_archive_prop_details', archive_id: archive_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    var archive_image_url = '<?php echo ARCHIVE_IMAGE; ?>';
                    $('#archive_title').html(record.item_title);
                    $('#about_archive').html(record.prop_details.archive_desc); 
					if(record.prop_details.archive_show_contact_btn == 'Y'){
						$('#contactUsIdBtn').show();
					} 
                    if(record.prop_details.archive_header_image){
                        $('.clientLanding').css('background-image', 'url(' + archive_image_url + record.prop_details.archive_header_image + ')');
                    }else{
                        $('.clientLanding').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
                    }
                    if(record.prop_details.archive_group_thumb){
                        $('#public_user_image').attr('src',archive_image_url + record.prop_details.archive_group_thumb);
                    }
                    $('.loading-div').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 586)');
                }
            });
        }
    }
    function getAdvertisement(folder_id)
    {
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_advertisement', folder_id: folder_id},
            success: function (response) {
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 587)');
            }
        });
    }
    $(document).on('click', '.contact-society', function () {
        $('#contactAIB').modal('show');
    });
    $(document).on('click','#submit_contact_request', function(e){
    e.preventDefault();
    if($("#contact_aib_form").valid()  && $('#field_contact_us').val() ==''){ 
        $('.custom-captcha_error').hide();
        $('.loading-div').show();
        var folder_id = '<?php echo $folder_id; ?>';
        var formData = $('#contact_aib_form').serialize();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'submit_request',formData: formData, item_id: folder_id ,type:'U' },
            success: function (response) {
                $('.loading-div').hide();
                var result = JSON.parse(response);
                if(result.status == 'success'){
                    $('#contact_aib_form')[0].reset();
                    $('#contactAIB').modal('hide');
                    showPopupMessage('success', result.message);
                }else{
                    showPopupMessage('error', result.message + ' (Error Code: 588)');
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed. (Error Code: 589)');
            }
        });
    }
});
</script>