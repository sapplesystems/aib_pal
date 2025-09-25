<?php
$is_user_logged_in = $_SESSION['aib']['user_data']['user_id'];
?>
<style>
    *{margin:0px auto; padding:0px;}
    #client_content_image_url{cursor: default;}
</style>
<div class="minHeightfull">
<div class="society-section" style="display:none;">
    <div>
        <div class="header_img bgBlue_header">
            <div class="clientLanding bannerImage_society"></div><!-- bannerImage clientLanding-->
            <div class="clientLogo col-md-2"><img id="client_logo" style="width:200px;" src="" /></div> <!--clientLogo_society-->
            <!--div id="top_right_position"></div-->
            <?php /*if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
                <input type="button" class="btn btn-info claim_this_historical" onclick="openClaimPopup(event);" value="Claim This Historical" />
            <?php }*/ ?>
        </div>

        <!-- Fix start on 25-June-2025 -->
             <div class="row-fluid bgMap description-background">
            <div class="col-md-4 col-sm-6 col-xs-12 text-center" id="archive_map_section">
                <img class="img300" src="" />
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 marginTopSociety">
                <h4 class="aboutSociety content-heading"><span id="archive_title"></span></h4>
                <div class="contact-society"><button class="btn">Contact</button></div>
                <div class="contentHeading content-description" id="about_archive"></div>
                <div class="entryDate content-description" id="archive_address"></div>

            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="laptop">
                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id); ?>">
                        <?php if ($is_user_logged_in) { ?>
                            <img style="height:60px;" src="<?php echo IMAGE_PATH . 'enter-button.png'; ?>" alt="" /> 
                            <!--span class="field-tip">
                                <img src="public/images/info.png" alt="">
                                <span class="tip-content">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</span>
                            </span-->
                        <?php } else { ?>
                            <img style="height:60px;" src="<?php echo IMAGE_PATH . 'enter-archive.png'; ?>" alt="" /> 
                            <!--span class="field-tip">
                                <img src="public/images/info.png" alt="">
                                <span class="tip-content">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</span>
                            </span-->
                        <?php } ?>
                    </a>
                    <?php if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
                        <br/><br/>
                        <!--register.html?q=<?php //echo encryptQueryString('folder_id=' . $folder_id);    ?>-->
                        <a href="#">
                            <img onclick="openClaimPopup(event);" style="height:60px;" src="<?php echo IMAGE_PATH . 'claim-this-archive.png'; ?>" alt="" />
                        </a>
                    <?php } ?>
                </div>
            </div>
                <!-- Fix end on 25-June-2025 -->
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="pull-right backToArchive"><a href="home.html?q=<?php echo encryptQueryString('folder_id=1&show_text=yes'); ?>" class="backtoBtn"><img src="<?php echo IMAGE_PATH . 'back-icon.png'; ?>"> Back to Archive List</a></div>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>
    <div class="row-fluid">
        <div class="col-md-5 col-sm-5 col-xs-12">
            <div class="leftModule">
                <a id="client_content_image_url" href="#">
                    <img id="client_content_image" src="" alt="" />
                </a>
            </div>
        </div>
        <div class="col-md-7 col-sm-7 col-xs-12">
            <div class="rightModule">
                <!--<div class="accordion_container">
                    <div class="accordion_head content-heading"><img src="<?php echo IMAGE_PATH . 'contest-info.png'; ?>" alt="" /> &nbsp;CONTACT INFO<span class="plusminus">-</span></div>
                    <div class="accordion_body" style="display: block;">
                        <p class="content-description"><b>Contact No : </b> <span id="archive_contact_number"></span></p>
                        <p class="content-description"><b>Website : </b><a  id="archive_website_url" target="_blank"><span id="archive_website"></span></a></p>
                    </div>
                    <div class="accordion_head content-heading"><img src="<?php echo IMAGE_PATH . 'entry-fees.png'; ?>" alt="" /> &nbsp;DETAIL DESCRIPTION<span class="plusminus">+</span></div>
                    <div class="accordion_body" style="display: none;">
                        <p class="content-description" id="detail_description"></p>
                    </div>
                    <div class="accordion_head content-heading"><img src="<?php echo IMAGE_PATH . 'special-instructions.png'; ?>" alt="" /> &nbsp;HOURS<span class="plusminus">+</span></div>
                    <div class="accordion_body" style="display: none;">
                        <p class="content-description"><b>Hours : </b><span id="archive_timing"></span></p>
                    </div>
                    <div class="accordion_head content-heading"><img src="<?php echo IMAGE_PATH . 'special-instructions.png'; ?>" alt="" /> &nbsp;UPCOMING EVENTS<span class="plusminus">+</span></div>
                    <div class="accordion_body" style="display: none;">
                        <p class="content-description"><span id="archive_upcoming_events"></span></p>
                    </div>
                </div>-->

                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title">
                                <a class="content-heading" role="button" data-toggle="" data-parent="#accordion" href="#" aria-expanded="true" aria-controls="collapseOne">
                                    <img src="<?php echo IMAGE_PATH . 'contest-info.png'; ?>" alt="" /> &nbsp;CONTACT INFO <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                            <div class="panel-body">
                                <p class="content-description"><b>Contact No : </b> <span id="archive_contact_number"></span></p>
                                <p class="content-description"><b>Website : </b><a  id="archive_website_url" target="_blank"><span id="archive_website"></span></a></p>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingTwo">
                            <h4 class="panel-title">
                                <a class="content-heading" class="collapsed" role="button" data-toggle="" data-parent="#accordion" href="#" aria-expanded="false" aria-controls="collapseTwo">
                                    <img src="<?php echo IMAGE_PATH . 'entry-fees.png'; ?>" alt="" /> &nbsp;DETAIL DESCRIPTION <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
                            <div class="panel-body">
                                <p class="content-description" id="detail_description_read_more"></p>
                                <p class="content-description hide" id="detail_description"></p>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingThree">
                            <h4 class="panel-title">
                                <a class="content-heading" class="collapsed" role="button" data-toggle="" data-parent="#accordion" href="#" aria-expanded="false" aria-controls="collapseThree">
                                    <img src="<?php echo IMAGE_PATH . 'special-instructions.png'; ?>" alt="" /> &nbsp;HOURS <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseThree" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingThree">
                            <div class="panel-body">
                                <p class="content-description"><b>Hours : </b><span id="archive_timing"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingFour">
                            <h4 class="panel-title">
                                <a class="content-heading" class="collapsed" role="button" data-toggle="" data-parent="#accordion" href="#" aria-expanded="false" aria-controls="collapseFour">
                                    <img src="<?php echo IMAGE_PATH . 'special-instructions.png'; ?>" alt="" /> &nbsp;UPCOMING EVENTS <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseFour" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingFour">
                            <div class="panel-body">
                                <p class="content-description">
                                    <span id="archive_upcoming_events_read_more"></span>
                                    <span class="hide" id="archive_upcoming_events"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
</div>
<div class="clr"></div>

<!--</div>
</div>-->

<script type="text/javascript">
    function readMore(elm, t, e) {
        e.preventDefault();
        if($('#'+elm).css('-webkit-line-clamp') == '2'){
            $('#'+elm+'_read_more').addClass('hide');
            $('#'+elm).removeClass('hide');
            $('#'+elm).addClass('read_more_toggle');
        }else{
            $('#'+elm+'_read_more').removeClass('hide');
            $('#'+elm).addClass('hide');
            $('#'+elm).removeClass('read_more_toggle');
        }
    }
</script>