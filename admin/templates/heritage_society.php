<?php
require_once dirname(__FILE__) . '/../config/config.php';
$is_user_logged_in = $_SESSION['aib']['user_data']['user_id'];
$folder_id = $_REQUEST['folder-id'];

?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

<style>
body{overflow:hidden !important;}
</style>
	
	<div class="bg_img"></div>
	<div class="navBar">
	<div class="container">
	<div class="row">
	<div class="col-md-12">
			<ul>
				<li><a href="#">Home</a></li>
				<li class="hov"><a href="#">About <i class="fa fa-caret-down" aria-hidden="true"></i></a>
					<ul class="main">
						<li><a href="http://staging.pacificgroveheritage.org/our-society/">Our Society</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/board-of-directors/">Board of Directors</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/heritage-house-awards/">Heritage House Awards</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/historic-home-plaques/">Historic Home Plaques</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/tours/">Tours</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/timeline/">Timeline</a></li>
					</ul>
				</li>
				<li class="hov"><a href="#">Get Involved <i class="fa fa-caret-down" aria-hidden="true"></i></a>
					<ul class="main">
						<li><a href="http://staging.pacificgroveheritage.org/attend-a-lecture/">Attend a Lecture</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/become-a-member/">Become A Member</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/donate/">Donate</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/volunteer/">Volunteer</a></li>
					</ul>
				</li>
				<li class="hov"><a href="#">Archives <i class="fa fa-caret-down" aria-hidden="true"></i></a>
					<ul class="main">
						<li><a href="https://www.archiveinabox.com/home.html?q=VmhHYkcvY3hLckN1azhuSEdPQjhiYU5XRGtzQ3RjRlhiMTZOUnNOVmEvVT0=">Search Archives (documents, images, newsletters)</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/newsletters/">Newsletters</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/historic-photos/">Historic Photos</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/question-of-the-month/">Question of the Month</a></li>
						<li><a href="http://staging.pacificgroveheritage.org/sanborn-maps/">Sanborn Maps</a></li>
					</ul>
				</li>
				<li><a href="#">Events</a></li>
				<li class="hov"><a href="#">How Do I… <i class="fa fa-caret-down" aria-hidden="true"></i></a>
					<ul class="main">
						<li><a href="http://staging.pacificgroveheritage.org/contact-us/">Contact Us</a></li>
					</ul>
				</li>
				<li><a href="#" class="member_btn">BECOME A MEMBER</a></li>
			</ul>
	</div>
	</div>
	</div>
	</div>
	
	<div class="logoSociety">
		<a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id); ?>"><img src="public/images/PGHSLOGO.png" /></a>
	</div>
	
	<div class="footer_bar">
		<div class="container">
		<div class="row">
		<div class="col-md-8">
		<p class="marginBottomNone mt-8">© 2021 – The Heritage Society of Pacific Grove | Web Design by <a class="linkColor" href="https://mckindercreative.com">McKinder Creative</a></p>
	</div>
	<div class="col-md-4 text-right">
	<span class="fb_icon"><i class="fa fa-facebook" aria-hidden="true"></i></span>
	</div>
	</div>
	</div>
	</div>
	
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
                    $('.archive_map_section').html('<iframe  width="100%" height="450"  src="https://www.google.com/maps?q=' + encodeURIComponent(record.prop_details.archive_address) + '&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>');
                    $('#archive_map_section').html('<iframe src="https://www.google.com/maps?q=' + encodeURIComponent(record.prop_details.archive_address) + '&output=embed" style="height:450px;"></iframe>');
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
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 798)');
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
                data: {mode: 'submit_request', formData: formData, item_id: '<?php echo $folder_id; ?>'},
                success: function (response) {
                    $('.loading-div').hide();
                    var result = JSON.parse(response);
                    if (result.status == 'success') {
                        $('#contact_aib_form')[0].reset();
                        $('#contactAIB').modal('hide');
                        showPopupMessage('success', result.message);
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 799)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed. (Error Code: 800)');
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
                    showPopupMessage('error', 'error', 'Something went wrong, Please try again. (Error Code: 801)');
                }
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 802)');
            }
        });
    }
</script> 	
	
	
	
