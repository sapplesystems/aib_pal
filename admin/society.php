<?php
require_once dirname(__FILE__) . '/config/config.php';
$folder_id = $_REQUEST['folder-id'];

include_once 'get_template_name.php';
include_once 'get_unclaimed_society_flag.php';

/*if ($societyTemp == 'custom2') {
    include_once COMMON_TEMPLATE_PATH . 'header2.php';
} else if ($societyTemp == 'custom1') {
    include_once COMMON_TEMPLATE_PATH . 'details-header.php';
} else {
    include_once COMMON_TEMPLATE_PATH . 'header.php';
}

switch ($societyTemp) {
    case "custom1":
        include_once TEMPLATE_PATH . 'custom2_society.php'; //include_once TEMPLATE_PATH . 'custom_society.php';
        break;
    case "custom2":
        include_once TEMPLATE_PATH . 'custom2_society.php';
        break;
    default:
        include_once TEMPLATE_PATH . 'default_society.php';
        break;
}*/

include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once TEMPLATE_PATH . 'default_society.php';
?>
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
                        <input type="text"  name="timestamp_value" value="<?php echo time(); ?>" style="display:none">
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

<div class="modal fade" id="claimed_message_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Claim This Archive</h4>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <p id="claimed_message_text"></p>
                    <div class="text-center">
                        <?php
                        $claimed_society_registration_url = "#";
                        if ($is_unclaimed_society && $is_unclaimed_society == '1') {
                            $claimed_society_registration_url = 'register.html?q=' . encryptQueryString('folder_id=' . $folder_id . '&society_template=' . $societyTemp);
                        }
                        ?>
                        <a class="btn btn-success" href="<?php echo $claimed_society_registration_url; ?>">Continue with Claim</a>
                        <a class="btn btn-danger" href="#" data-dismiss="modal" aria-label="Close">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<?php
/*if ($societyTemp == 'custom2') {
    include_once COMMON_TEMPLATE_PATH . 'details-footer.php';//include_once COMMON_TEMPLATE_PATH . 'footer2.php';
} else if ($societyTemp == 'custom1') {
    include_once COMMON_TEMPLATE_PATH . 'details-footer.php';
} else {
    include_once COMMON_TEMPLATE_PATH . 'footer.php';
}*/

include_once COMMON_TEMPLATE_PATH . 'footer.php';
?>
<script type="text/javascript">
    var template = '<?php echo isset($_REQUEST['society_template']) ? $_REQUEST['society_template'] : ''; ?>';
    $(document).ready(function () {
        var folder_id = '<?php echo $folder_id; ?>';
        if (template != '') {
            if (template == 'custom1') {
                $('.header_logo img').hide();
            }
        }
        getItemPropDetails(folder_id);
        //getAdvertisement(folder_id);
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
                        depends: function () {
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
        var max_char = 130;
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
                    $('#about_archive').html(record.prop_details.archive_about_content);
                    $('#archive_address').html('We are located at: ' + record.prop_details.archive_address);
                    $('.archive_address').html(record.prop_details.archive_address);
                    var archive_details_description = record.prop_details.archive_details_description;
					
					if(typeof(archive_details_description) != "undefined" && archive_details_description!='')
					{
						$('#detail_description_read_more').html(archive_details_description.substring(0, max_char)+"...<a class='read_more_btn' href='javascript:void(0);' onclick='readMore(\"detail_description\", this, event);'>Read More</a>");
						
						var read_less_hide = '';
						if(archive_details_description.length <= max_char){
							$('#detail_description_read_more').addClass('hide');
							$('#detail_description').removeClass('hide');
							read_less_hide = 'hide';
						}
						$('#detail_description').html(archive_details_description+" <a class='read_more_btn "+read_less_hide+"' href='javascript:void(0);' onclick='readMore(\"detail_description\", this, event);'>Read Less</a>");
						read_less_hide = '';
					}
                    $('#archive_contact_number').html(record.prop_details.archive_contact_number);
                    $('#archive_website').html(record.prop_details.archive_website);
                    var archive_url = record.prop_details.archive_website;
                    var pattern = /^((http|https):\/\/)/;
                    if (!pattern.test(archive_url)) {
                        archive_url = "http://" + archive_url;
                    }
                    $("#archive_website_url").attr("href", archive_url);
                    $('#archive_timing').html(record.prop_details.archive_timing);
                    if (record.prop_details.archive_logo_image && record.prop_details.archive_logo_image !== 'undefined') {
                        $('.society_logo img').attr('src', archive_image_url + record.prop_details.archive_logo_image);
                        $('#client_logo').attr('src', archive_image_url + record.prop_details.archive_logo_image);
                    }
                    if (record.prop_details.archive_details_image && record.prop_details.archive_details_image !== 'undefined') {
                        $('#client_content_image').attr('src', archive_image_url + record.prop_details.archive_details_image);
                    }
                    if (record.prop_details.archive_details_image_url && record.prop_details.archive_details_image_url !== '') {
                        $('#client_content_image_url').attr('href', record.prop_details.archive_details_image_url);
                        $('#client_content_image_url').attr('target','_blank');
                        $('#client_content_image_url').css('cursor','pointer');
                    }
                    if (record.prop_details.archive_header_image && record.prop_details.archive_header_image !== 'undefined') {
                        $('.header_baner').css('background-image', 'url(' + archive_image_url + record.prop_details.archive_header_image + ')');
                        $('.clientLanding').css('background-image', 'url(' + archive_image_url + record.prop_details.archive_header_image + ')');
                    } else {
                        //$('.header_baner').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
                        //$('.clientLanding').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
                        $('.clientLanding').css('background-image', 'url(public/images/default-header-img.jpg)');
                    }
                    $('.archive_map_section').html('<iframe  width="100%" height="300"  src="https://www.google.com/maps?q=' + encodeURIComponent(record.prop_details.archive_address) + '&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>');
                    $('#archive_map_section').html('<iframe src="https://www.google.com/maps?q=' + encodeURIComponent(record.prop_details.archive_address) + '&output=embed" style="height:300px;"></iframe>');
                    if (record.prop_details.archive_upcoming_events) {
                        var archive_upcoming_events = record.prop_details.archive_upcoming_events;
						
						if(typeof(archive_upcoming_events) != "undefined" && archive_upcoming_events!=''){
                        $('#archive_upcoming_events_read_more').html(archive_upcoming_events.substring(0, max_char)+"...<a class='read_more_btn' href='javascript:void(0);' onclick='readMore(\"archive_upcoming_events\", this, event);'>Read More</a>");
							
							if(archive_upcoming_events.length <= max_char){
								$('#archive_upcoming_events_read_more').addClass('hide');
								$('#archive_upcoming_events').removeClass('hide');
								read_less_hide = 'hide';
							}
							$('#archive_upcoming_events').html(archive_upcoming_events+" <a class='read_more_btn "+read_less_hide+"' href='javascript:void(0);' onclick='readMore(\"archive_upcoming_events\", this, event);'>Read Less</a>");
						}
                    } else {
                        $('#archive_upcoming_events_read_more').html("No upcoming events.");
                        $('#archive_upcoming_events').html("No upcoming events.");
                    }
                    if (record.prop_details.heading_font) {
                        //$('.content-heading').css('font-family', record.prop_details.heading_font);
                    }
                    if (record.prop_details.heading_font_color) {
                        //$('.content-heading').css('color', '#' + record.prop_details.heading_font_color);
                    }
                    if (record.prop_details.heading_font_size) {
                        //$('.content-heading').css('font-size', record.prop_details.heading_font_size + 'px');
                    }

                    if (record.prop_details.content_font) {
                        //$('.content-description').css('font-family', record.prop_details.content_font);
                    }
                    if (record.prop_details.content_font_color) {
                        //$('.content-description').css('color', '#' + record.prop_details.content_font_color);
                    }
                    if (record.prop_details.content_font_size) {
                        //$('.content-description').css('font-size', record.prop_details.content_font_size + 'px');
                    }
                    if (record.prop_details.description_background && $('.description-background').length) {
                        //$('.description-background').css('background-color', '#' + record.prop_details.description_background);
                    }
                    if ($('.enterArchive').length) {
                        if (record.prop_details.button_background) {
                            $('.enterArchive').css('background-color', '#' + record.prop_details.button_background);
                        }
                        if (record.prop_details.button_font) {
                            $('.enterArchive').css('font-family', record.prop_details.button_font);
                        }
                        if (record.prop_details.button_font_size) {
                            $('.enterArchive').css('font-size', record.prop_details.button_font_size + 'px');
                        }
                        if (record.prop_details.button_font_color) {
                            $('.enterArchive').css('color', '#' + record.prop_details.button_font_color);
                        }
                    }
                    $('.loading-div').hide();
                    $('.society-section').show();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 645)');
                }
            });
        }
    }

    $(document).on('click', '.contact-society', function () {
        $('#contactAIB').modal('show');
    });
    $(document).on('click', '#submit_contact_request', function (e) {
        e.preventDefault();
        if ($("#contact_aib_form").valid() && $('#field_contact_us').val() == '') {
            $('.custom-captcha_error').hide();
            $('.loading-div').show();
            var folder_id = '<?php echo $folder_id; ?>';
            var formData = $('#contact_aib_form').serialize();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'submit_request', formData: formData, item_id: folder_id},
                success: function (response) {
                    $('.loading-div').hide();
                    var result = JSON.parse(response);
                    if (result.status == 'success') {
                        $('#contact_aib_form')[0].reset();
                        $('#contactAIB').modal('hide');
                        showPopupMessage('success', result.message);
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 646)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed. (Error Code: 647)');
                }
            });
        }
    });

    function openClaimPopup(e) {
        e.preventDefault();
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_claimed_popup_message', user_id: 1, type: 'CM'},
            success: function (data) {
                console.log(data);
                var result = JSON.parse(data);
                if (result.status == 'success') {
                    $('#claimed_message_text').html(result.message);
                    $('#claimed_message_modal').modal('show');
                } else {
                    showPopupMessage('error', 'error', 'Something went wrong, Please try again. (Error Code: 648)');
                }
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 649)');
            }
        });
    }
</script> 