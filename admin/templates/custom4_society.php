<?php
require_once dirname(__FILE__) . '/../config/config.php';
$is_user_logged_in = $_SESSION['aib']['user_data']['user_id'];
$folder_id = $_REQUEST['folder-id'];

?>

	<!--<iframe class="iframeStyle" src="../musical.php" height="100%" width="100%" title="Iframe Example" frameborder="0" scrolling="no" onload="resizeIframe(this)" style="border:0px;"></iframe>-->
	
	
	<link rel="stylesheet" href="public/css/societycss/plugins.css"/>
    <!-- Theme Styles -->
    <link rel="stylesheet" href="public/css/societycss/theme.css"/>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="public/css/societycss/custom.css" />
    <!-- End Page Styles -->
	<script src="https://use.fontawesome.com/89bbede699.js"></script>
	
	
    <!-- HOME SECTION -->
    <section id="home" class="rev_slider_wrapper fullscreen-container" >
        <!-- Start Slider -->
        <div id="home_slider" class="rev_slider fullscreenbanner lightbox_gallery">
            <!-- Slider Container -->
            <ul style="margin:0px;">
                <!-- Slide -->
                <li class="society_banner_image" style="position:relative;height:100vh;background-size: cover;background-position: center center;">
                    <!-- Background Image -->
                    
                   <div class="dotImg"></div>
				   
                    <!-- Layer -->
                    <div class="societyName">
                        <div class="tracking-in-expand"><span id="archive_title"></span></div>
                    </div>
                    <!-- Layer -->
                    <div class="">
                        <!-- Add your video and video's width and height -->
                     
						 <!-- Boxes -->
            <div class="boxes boxes-type-1 qdr-col-1">
                <!-- Item -->
                <div class="boxContainer white slide-top">
                    <!-- Icon -->
                    <a href="society_details.html?q=<?php echo encryptQueryString('folder_id=' .$folder_id); ?>" class="qdr-hover pt-30"><i class="fa fa-eye" aria-hidden="true"></i></a>
                    <!-- Title -->
                    <h2>
                        VIEW
                    </h2>
                </div>
            </div>
            <!-- End Boxes -->
                    </div>
                </li>
                <!-- End Slides -->
               
            </ul>
            <!-- End Container -->
        </div>
        <!-- End Slider -->

        <!-- Home Page Note -->
        <div class="page-note fullwidth c-default clearfix">
            <!-- Left Note -->
            <div class="left-note border-colored white">
                <p class="font-18">
                    <span class="colored uppercase bold" id="archive_title_sub"></span>
                </p>
				<p id="archive_address"></p>
            </div>
        </div>
        <!-- End Home Page Note -->
    </section>
    <!-- END HOME SECTION -->
	
	
	
