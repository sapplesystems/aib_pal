<?php
$is_user_logged_in = $_SESSION['aib']['user_data']['user_id'];
?>
<div class="minHeight400">
<div class="societyClass society-section" style="display:none;">
    <div class="header_img header_baner">
        <div class="container">
            <div class="row loginDisplayBlock"><div class="col-md-12"><div class="login_box"><img src="public/images/lock_icon.png" alt="Lock Icon" /> &nbsp;Log In</div></div></div>
            <div class="row">
                <div class="col-md-5 col-sm-5 col-xs-12">
                    <div class="societyName_bg">
                        <h1 id='archive_title' class="content-heading">Society Name</h1>
                        <p id='about_archive' class="content-description"></p>
                        <div class="society_logo"><img src="" alt="Society Logo" /></div>
                        <?php /*if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
                            <input type="button" class="btn btn-info claim_this_historical" onclick="openClaimPopup(event);" value="Claim This Historical" />
                        <?php }*/ ?>
                        <div class="societyText_bg  description-background">
                        <!--<img class="yellowImg" src="<?php echo IMAGE_PATH; ?>yellow_bg.png" alt="Yellow Bg" />-->
                            <!--<div id="trapezoid"></div>-->
                            <div class="gradient description-background"></div>
                            <div class="padd15">
                                <h3 class="content-heading">Details Description</h3>
                                <p id='detail_description' class="content-description"></p>
                                <h3 id="marginTop20" class="content-heading">Upcoming Events</h3>
                                <p id="archive_upcoming_events" class="content-description"></p>
                                <h3 class="marginTop20 content-heading">Hours</h3>
                                <p id='archive_timing' class="content-description">No Hours</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content_bg">
        <div class="container">

            <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id . '&society_template=' . $societyTemp); ?>"> 
                <?php if ($is_user_logged_in) { ?>
                    <div class="enterArchive gradient_two" title="Enter My Archive">ENTER MY <br class="breakLine" />ARCHIVE</div>
                <?php } else { ?>
                    <div class="enterArchive gradient_two" title="Enter My Archive">ENTER <br class="breakLine" />ARCHIVE</div>
                <?php } ?>
            </a>
            <?php if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
            <!--register.html?q=<?php //echo encryptQueryString('folder_id=' . $folder_id . '&society_template=' . $societyTemp); ?>-->
                <br/><br/>
                <a href="#"> 
                    <div class="enterArchive_border" style="margin-left:20px;" onclick="openClaimPopup(event);">
                        <div class="enterArchive gradient_two enterArchive_archive" title="Enter My Archive">CLAIM THIS <br class="breakLine breakLineNone" />ARCHIVE</div>
                    </div>
                </a>
            <?php } ?>
            <div class="row">
                <div class="clearfix"></div>
                <div class="col-md-5 col-sm-5 col-xs-12">

                    <div class="contact_us">
                        <h3 class="content-heading"><div class="contact-society posRelative pull-left" style="right:0;"><button class="btn"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span> Contact</button></div> Contact Us</h3>
                        <p class="content-description"><strong> &nbsp;:Address </strong><span class='archive_address'></span></p>
                        <p class="content-description"><strong> &nbsp;:Phone </strong><span id='archive_contact_number'> </span></p>
                    </div>
                </div>
                <div class="col-md-7 col-sm-7 col-xs-12">
                    <div class="map_society marginBottom50">
                        <div style="width: 100%" class="archive_map_section">
                        </div>
                    </div>
                    <div class="pull-right backToArchive custom_back">
                        <a href="home.html?q=<?php echo encryptQueryString('folder_id=1&show_text=yes'); ?>"> 
                            <img src="<?php echo IMAGE_PATH; ?>go-back.png">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>