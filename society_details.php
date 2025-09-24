<?php
require_once dirname(__FILE__) . '/config/config.php';
$folder_id=$_REQUEST['folder_id'];
?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="public/css/society_details/plugins2.css"/>
    <!-- Main Styles -->
    <link rel="stylesheet" href="public/css/society_details/theme.css"/>
	<!-- Page Styles -->
	<link rel="stylesheet" href="public/css/society_details/law.css" />
    <!-- Custom Styles -->
    <link rel="stylesheet" href="public/css/society_details/custom.css" />
	<link rel="stylesheet" href="public/css/society_details/modern.css" />
    <!-- End Page Styles -->
	<script src="https://use.fontawesome.com/89bbede699.js"></script>



    <!-- HOME -->
    <section id="home" class="home t-center">
        <!-- Hero Slider -->
        <div class="hero-slider fullheight custom-slider"
            data-slick='{"dots": false, "arrows": true, "fade": true, "speed": 900, "draggable":false, "autoplay":true, "autoplaySpeed": 7000, "slidesToShow": 1, "slidesToScroll": 1, "responsive":[{"breakpoint": 1024,"settings":{"slidesToShow": 1}}]}'>
            <!-- Slide -->
            <div class="slide">
                <!-- Your Image -->
                <div class="slide-img skrollr" data-anchor-target="#home" data-0="transform:translate3d(0, 0px, 0px);" data-900="transform:translate3d(0px, 150px, 0px);">
                    <div class="scale-timer clientLanding bannerImage_society"></div>
                </div>
                <!-- Details -->
                <div class="details">
                    <!-- Centered Container -->
                    <div class="container v-center v-center2">
                        <div class="skrollr" data-0="opacity:1;" data-400="opacity:0;">
                            <h1 class="cinzel law-home-title animated salal-item white" data-animation="fadeInUp" data-animation-delay="0">
                                <span class="salal-effect oswald_font"><span id="archive_title"></span></span>
                            </h1>
                            <div class="xs-mt animated" data-animation="fadeInUp" data-animation-delay="550">
                                <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id); ?>" class="bold font-14 bg-colored xl-btn radius-lg qdr-hover-6 bs-lg-hover slow">ENTER ARCHIVE</a>
							
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Details -->
            </div>
            <!-- End Slide -->
        </div>
        <!-- End Hero Slider -->
    </section>
    <!-- END HOME -->



    <!-- ABOUT -->
    <section class="about">
        <!-- Moved container -->
        <div id="about" class="about-box-container container bg-colored black">
            <!-- Row for cols -->
            <div class="row clearfix">

                <!-- Left Col -->
                <div class="col-md-4 col-12 t-center t-center-mobile">
				<img id="client_logo" style="height:200px;" src="" />
                    <!-- Title -->
                    
                </div>
                <!-- End Left Col -->

                <!-- Right Col -->
                <div class="col-md-8 col-12 t-left t-center-mobile">
                    <h1 class="semibold-title lh-sm oswald_font marginTop20">
                        <span id="archive_title"></span>
                    </h1>
                    <!-- Description -->
                    <p class="xxs-mt lh-md font-18" id="about_archive">
                        
                    </p>
					<h5 class="bold-subtitle gray9 font-18" id="archive_address"></h5>
                </div>
				<div class="col-md-2"></div>
                <!-- End Right Col -->
            </div>
            <!-- End Row for cols -->
        </div>
        <!-- End Moved container -->
    </section>
    <!-- END ABOUT -->
	
	 


    <!-- Articles -->
    <section id="articles" class="pb">
        <!-- Container -->
        <div class="container">

            <!-- Row -->
            <div class="row">

                <!-- Left Column -->
                <div class="col-lg-6 col-md-5 col-12">
                    <!-- Accordion -->
                    <div id="" class="dark textSize">
                        <!-- Card -->
                        <div class="card xxs-mb bg-transparent animated" data-animation="fadeInUp" data-animation-delay="50">
                            <!-- Header -->
                            <div id="accordion-header-1" class="card-header c-pointer bg-transparent bg-gray2-hover bs-light-hover slow click-effect dark-effect padd15" data-toggle="collapse" data-target="#accordion-1" aria-expanded="true" aria-controls="accordion-1">
                                <h5 class="dark uppercase"><span>Contact Info</span> <img class="pull-right" src="public/images/contest-info-new.png" /></h5>
                            </div>
                            <!-- Content -->
                            <div id="accordion-1" class="collapse show" aria-labelledby="accordion-header-1" data-parent="#accordion">
                                <!-- Details -->
                                <div class="card-body padd15">
                                    <p><b>Contact No : </b> <span id="archive_contact_number"></span></p>
									<p><b>Website : </b><a  id="archive_website_url" target="_blank"><span id="archive_website"></span></a></p>
                                </div>
                            </div>
                            <!-- End Content -->
                        </div>
                        <!-- End Card -->
                        <!-- Card -->
                        <div class="card xxs-mb bg-transparent animated" data-animation="fadeInUp" data-animation-delay="300">
                            <!-- Header -->
                            <div id="accordion-header-2" class="card-header c-pointer bg-transparent bg-gray2-hover bs-light-hover slow click-effect dark-effect padd15" data-toggle="collapse" data-target="#accordion-2" aria-expanded="false" aria-controls="accordion-2">
                                <h5 class="dark uppercase"><span>Detail Description</span> <img class="pull-right" src="public/images/entry-fees-new.png" /></h5>
                            </div>
                            <!-- Content -->
                            <div id="accordion-2" class="collapse show" aria-labelledby="accordion-header-2" data-parent="#accordion">
                                <!-- Details -->
                                <div class="card-body padd15">
                                   <p id="detail_description_read_more"></p>
								   <!--<p id="detail_description"></p>-->
                                </div>
                            </div>
                            <!-- End Content -->
                        </div>
                        <!-- End Card -->
                        <!-- Card -->
                        <div class="card xxs-mb bg-transparent animated" data-animation="fadeInUp" data-animation-delay="350">
                            <!-- Header -->
                            <div id="accordion-header-3" class="card-header c-pointer bg-transparent bg-gray2-hover bs-light-hover slow click-effect dark-effect padd15" data-toggle="collapse" data-target="#accordion-3" aria-expanded="false" aria-controls="accordion-3">
                                <h5 class="dark uppercase"><span>Hours</span> <img class="pull-right" src="public/images/special-instructions-new.png" /></h5>
                            </div>
                            <!-- Content -->
                            <div id="accordion-3" class="collapse show" aria-labelledby="accordion-header-3" data-parent="#accordion">
                                <!-- Details -->
                                <div class="card-body padd15">
                                   <p><b>Hours : </b><span id="archive_timing"></span></p>
                                </div>
                            </div>
                            <!-- End Content -->
                        </div>
                        <!-- End Card -->
                        <!-- Card -->
                        <div class="card xxs-mb bg-transparent animated" data-animation="fadeInUp" data-animation-delay="400">
                            <!-- Header -->
                            <div id="accordion-header-4" class="card-header c-pointer bg-transparent bg-gray2-hover bs-light-hover slow click-effect dark-effect padd15" data-toggle="collapse" data-target="#accordion-4" aria-expanded="false" aria-controls="accordion-4">
                                <h5 class="dark uppercase"><span>Upcoming Events</span> <img class="pull-right" src="public/images/special-instructions-new.png" /></h5>
                            </div>
                            <!-- Content -->
                            <div id="accordion-4" class="collapse show" aria-labelledby="accordion-header-4" data-parent="#accordion">
                                <!-- Details -->
                                <div class="card-body padd15">
                                    <p id="archive_upcoming_events_read_more"></p>
                                    <!--<p class="hide" id="archive_upcoming_events"></p>-->
                                </div>
                            </div>
                            <!-- End Content -->
                        </div>
                        <!-- End Card -->
                        <!-- Card -->
                      
                        <!-- End Card -->

                    </div>
                    <!-- End Accordion -->
                </div>
                <!-- End Left Column -->


                <!-- Right Column -->
                <div class="right-boxes col-lg-6 col-md-7 col-12">
                    <!-- Box -->
                   <div id="archive_map_section"></div>
                    <!-- End Box -->
                   
                </div>
                <!-- End Right Column -->
            </div>
            <!-- End Row -->
        </div>
        <!-- End Container -->
    </section>
    <!-- End Articles -->



	<!-- jQuery -->
    <script src="public/js/society_details/jquery.min.js"></script>
    <!-- MAIN SCRIPTS - Classic scripts for all theme -->
    <script src="public/js/society_details/scripts.js"></script>
    <!-- PAGE OPTIONS - You can find special scripts for this version -->
    <!-- END JS FILES -->
	
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
						/* fix start for 2274 10-july */
                        archive_url = "https://" + archive_url;
						/* fix end for 2274 10-july */	
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
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 292)');
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
                        showPopupMessage('error', result.message + ' (Error Code: 293)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed. (Error Code: 294)');
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
                    showPopupMessage('error', 'error', 'Something went wrong, Please try again. (Error Code: 295)');
                }
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 296)');
            }
        });
    }
</script> 

