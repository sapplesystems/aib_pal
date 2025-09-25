<?php
$is_user_logged_in = $_SESSION['aib']['user_data']['user_id'];
?>
<div class="minHeight400">
<div class="societyClass society-section" style="display:none;">
    <div class="header_img_society header_baner">
        <div class="society_logo society_logo_society">
            <img src="" alt="Society Logo" />
        </div>
        <?php /*if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
            <input type="button" class="btn btn-info claim_this_historical" onclick="openClaimPopup(event);" value="Claim This Historical" />
        <?php }*/ ?>
    </div>
    <div class="content_bg_society">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-sm-12 col-xs-12 marginTop35">
                    <h1 id='archive_title' class="content-heading content-heading_society">Society Name</h1>
                    <span class="contactLink contact-society">Contact</span><!--contact-society-->
                    <div class="clearfix"></div>
                    <p id='about_archive' class="content-description"></p>
                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id . '&society_template=' . $societyTemp); ?>"> 
                        <div class="enterArchive_border">
                            <?php if ($is_user_logged_in) { ?>
                                <div class="enterArchive gradient_two enterArchive_archive" title="Enter My Archive">ENTER MY <br class="breakLine breakLineNone" />ARCHIVE</div>
                            <?php } else { ?>
                                <div class="enterArchive gradient_two enterArchive_archive" title="Enter My Archive">ENTER <br class="breakLine breakLineNone" />ARCHIVE</div>
                            <?php } ?>
                        </div>
                    </a>
                    <?php if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
                    <!--register.html?q=<?php //echo encryptQueryString('folder_id=' . $folder_id . '&society_template=' . $societyTemp); ?>-->
                    <a href="#"> 
                        <div class="enterArchive_border" style="margin-left:20px;" onclick="openClaimPopup(event);">
                            <div class="enterArchive gradient_two enterArchive_archive" title="Enter My Archive">CLAIM THIS <br class="breakLine breakLineNone" />ARCHIVE</div>
                        </div>
                    </a>
                    <?php } ?>
                    <div class="backArchive_border">
                        <div class="backArchive_archive">
                            <a href="home.html?q=<?php echo encryptQueryString('folder_id=1&show_text=yes'); ?>"> 
                                Back to Archive List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-sm-12 col-xs-12 topMargin30">
                    <div class="map_society map_society_society">
                        <div class="archive_map_section archive_map_section_archive">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="infobg_society">
        <div class="container">
            <div class="row">
                <div class="col-md-5 col-sm-12 center_Img">
                    <img src="<?php echo IMAGE_PATH; ?>img_society_archive.jpg">
                </div>
                <div class="col-md-7 col-sm-12"> 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="infoBox_archive_border">
                                <div class="infoBox_archive"><h4>Contact Info</h4>
                                    <p class="marginBottomNone"><strong>Contact No:</strong></p>
                                    <p id="archive_contact_number"></p>
                                    <p class="marginBottomNone"><strong>Website:</strong></p>
                                    <p id="archive_website"></p>
                                    <p class="marginBottomNone"><strong>Hours:</strong></p>
                                    <p id='archive_timing' class="content-description">No Hours</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="infoBox_archive_border">
                                <div class="infoBox_archive"><h4>Upcoming Events</h4>
                                    <p id="archive_upcoming_events" class="content-description"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row marginTop25">
                        <div class="col-md-12">
                            <div class="infoBox_archive_border">
                                <div class="infoBox_archive height100"><h4>Detail Description</h4>
                                    <p id='detail_description' class="content-description"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>