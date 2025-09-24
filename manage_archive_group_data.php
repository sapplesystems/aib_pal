<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$archive_id = $_REQUEST['archive_id'];
$browseUrl  = 'society.php?folder-id='.$archive_id;
?>
<style>.modal-dialog{width:100% !important;} #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}
</style>
<div class="content-wrapper">
    <section class="content-header"> 
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Archive</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Home Page Template </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span></h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-2 col-md-8 col-md-offset-2">
                <form class="marginBottom30 formStyle form-group" autocomplete="off" action="" method="POST" id="editArchiveGroupDetails" name="editArchiveGroupDetails" enctype="multipart/form-data">
                    <input type="hidden" name="archive_id" id="archive_id" value="<?php echo $archive_id; ?>">
                    <input type="hidden" name="type" id="type" value="">
                    <input type="hidden" name="color_code" id="color_code" value="" />
                    <input type="hidden" name="color_code_selected_for" id="color_code_selected_for" value="" />
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Historical Society Name:</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_name"  name="archive_name" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Address :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_address" id="archive_address" class="form-control" placeholder="Address"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>About Content :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_about_content" id="archive_about_content" class="form-control" placeholder="About content"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Detail Description :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_details_description" id="archive_details_description" class="form-control" placeholder="Detail Description"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Request Reprint Text :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_request_reprint_text" id="archive_request_reprint_text" class="form-control" placeholder="Detail Description"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Upcoming Events Text :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_upcoming_events" id="archive_upcoming_events" class="form-control" placeholder="Upcoming Events Text"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Preferred Time Zone:</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <select  name="preferred_time_zone" id="preferred_time_zone" class="form-control">
                                <option value="">---Select---</option>
                                <option value="UTC -11:00">UTC-11: Samoa Standard Time</option>
                                <option value="UTC -10:00">UTC-10: Hawaii-Aleutian Standard Time (HST)</option>
                                <option value="UTC -09:00">UTC-9: Alaska Standard Time (AKST)</option>
                                <option value="UTC -08:00">UTC-8: Pacific Standard Time (PST)</option>
                                <option value="UTC -07:00">UTC-7: Mountain Standard Time (MST)</option>
                                <option value="UTC -06:00">UTC-6: Central Standard Time (CST)</option>
                                <option value="UTC -05:00">UTC-5: Eastern Standard Time (EST)</option>
                                <option value="UTC -04:00">UTC-4: Atlantic Standard Time (AST)</option>
                                <option value="UTC +10:00">UTC+10: Chamorro Standard Time</option>
                                <option value="UTC +12:00">UTC+12: Wake Island Time Zone</option>
                            </select>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Contact No. :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" maxlength="14" class="form-control"  id="archive_contact_number"  name="archive_contact_number" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Website :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_website"  name="archive_website" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Hours :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_timing"  name="archive_timing" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Watermark Text :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_watermark_text"  name="archive_watermark_text" placeholder="Text input" value="" maxlength="30">
                        </div> 
                    </div>

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display State :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <select  class="form-control display_state_list"  id="archive_display_state"  name="archive_display_state"></select>
                            <!--<input type="text" class="form-control"  id="archive_display_state"  name="archive_display_state" placeholder="Text input" value="">-->
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display County :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_display_county"  name="archive_display_county" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display City :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_display_city"  name="archive_display_city" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display Zip :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_display_zip"  name="archive_display_zip" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row" style="display: none;">
                        <div class="col-md-2 text-right"></div>
                        <div class="col-md-10 col-sm-8 col-xs-12">
                            <ul class="nav nav-tabs">
                                <li class="active"><a  href="#tab_content_section" data-toggle="tab">Customize Color & Font</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_content_section">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Heading Font Color</label>
                                            <input class="colorpicker" type="text" data-type="heading_font_color" id="heading_font_color" name="heading_font_color" value="" autocomplete="false">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Heading Font</label>
                                            <div id="heading_font" class="fontSelect" data-type="heading_font">
                                                <div class="arrow-down"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Heading Font Size</label>
                                            <select class="form-control change-font-size" name="heading_font_size" id="heading_font_size" data-type="heading_font_size">
                                                <option value="">Select Font Size</option>
                                                <?php
                                                for ($i = 10; $i <= 50; $i++) {
                                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-md-4">
                                            <label>Content Font Color</label>
                                            <input class="colorpicker" type="text" data-type="content_font_color" id="content_font_color" name="content_font_color" value="" autocomplete="false">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Content Font</label>
                                            <div id="content_font" class="fontSelect" data-type="content_font">
                                                <div class="arrow-down"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Content Font Size</label>
                                            <select class="form-control change-font-size" name="content_font_size" id="content_font_size" data-type="content_font_size">
                                                <option value="">Select Font Size</option>
                                                <?php
                                                for ($i = 10; $i <= 50; $i++) {
                                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-md-4">
                                            <label>Button Font Color</label>
                                            <input class="colorpicker" type="text" data-type="button_font_color" id="button_font_color" name="button_font_color" value="" autocomplete="false">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Button Font</label>
                                            <div id="button_font" class="fontSelect" data-type="button_font">
                                                <div class="arrow-down"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Button Font Size</label>
                                            <select class="form-control change-font-size" name="button_font_size" id="button_font_size" data-type="button_font_size">
                                                <option value="">Select Font Size</option>
                                                <?php
                                                for ($i = 10; $i <= 50; $i++) {
                                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-md-4">
                                            <label>Button Background</label>
                                            <input class="colorpicker" data-type="button_background" type="text" id="button_background" name="button_background" value="" autocomplete="false">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Desc. Background</label>
                                            <input class="colorpicker" type="text" data-type="description_background" id="description_background" name="description_background" value="" autocomplete="false">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-group">
                        <div class="col-md-4 text-right"><strong>Templates :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <ul class="templatesCustom">
                                <li>
                                    <h4>Default</h4>
                                    <div><img src="<?php echo IMAGE_PATH ?>default.png" alt="" ></div>
                                    <div><input type="radio" name="custom_template" id="default" value="default" checked=""> <span>Default</span></div>
                                </li>
                                <li>
                                    <h4>Custom1</h4>
                                    <div><img src="<?php echo IMAGE_PATH ?>custom-1.png" alt="" ></div>
                                    <div><input type="radio" name="custom_template" id="custom1" value="custom1"> <span>Custom1</span></div>
                                </li>
								<li>
                                    <h4>Custom2</h4>
                                    <div><img src="<?php echo IMAGE_PATH ?>custom-2.png" alt="" ></div>
                                    <div><input type="radio" name="custom_template" id="custom2" value="custom2"> <span>Custom2</span></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <button class="preview-template">Preview</button>
                        </div> 
                    </div>
                    
                    <!-- *Details Page Design section* -->
                    <div class="row-group" style="display: none;">
                        <div class="col-md-4 text-right"><strong>Details Page Design :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <!--default-->
                            <label for="default_design">Default</label>
                            <input type="radio" value ="default" name="details_page_design" group="dpdesign" checked class="details_page_design" id="default_design">
                            <!--custom-->
                            <label for="custom_design">Custom1</label>
                            <input type="radio" value ="custom" name="details_page_design" group="dpdesign" class="details_page_design" id="custom_design">
                            <!--custome_1-->
                            <label for="custom_design1">Custom2</label>
                            <input type="radio" value ="custom1" name="details_page_design" group="dpdesign" class="details_page_design" id="custom_design1">
                        </div>
                    </div>
                    <!-- **Details Page Design section** -->
                    
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Logo Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_logo_image" id="archive_logo_image">
                            <span class="help-text">(Must be at least 200 X 200 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_logo_image_post_display" src="" alt="your image" />
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_logo_image"><img title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_logo_x" id="archive_logo_x" />
                            <input type="hidden" name="archive_logo_y" id="archive_logo_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Banner Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_header_image" id="archive_header_image">
                            <span class="help-text custom_temp_crop">(Must be at least 1600 X 400 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_header_image_post" src="" alt="your image" />
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_header_image"><img title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_header_x" id="archive_header_x" />
                            <input type="hidden" name="archive_header_y" id="archive_header_y" />
                            <input type="hidden" name="baner_crop_height" id="baner_crop_height">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Details Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_details_image" id="archive_details_image">
                            <span class="help-text">(Must be at least 645 X 430 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_details_image_post" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_details_image"><img title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_details_x" id="archive_details_x" />
                            <input type="hidden" name="archive_details_y" id="archive_details_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Archive Group Thumb :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_group_thumb" id="archive_group_thumb">
                            <span class="help-text ">(Must be at least 400 X 400 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_group_thumb_post" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_group_thumb"><img title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_group_thumb_x" id="archive_group_thumb_x" />
                            <input type="hidden" name="archive_group_thumb_y" id="archive_group_thumb_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Details Page Logo :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_group_details_thumb" id="archive_group_details_thumb">
                            <span class="help-text ">(Must be at least 400 X 400 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_group_details_thumb_img" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_group_details_thumb"><img title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_group_details_thumb_x" id="archive_group_details_thumb_x" />
                            <input type="hidden" name="archive_group_details_thumb_y" id="archive_group_details_thumb_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Hist. Connection Logo :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="historical_connection_logo" id="historical_connection_logo">
                            <span class="help-text ">(Must be at least 200 X 200 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="historical_connection_logo_img" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="historical_connection_logo"><img title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="historical_connection_logo_x" id="historical_connection_logo_x" />
                            <input type="hidden" name="historical_connection_logo_y" id="historical_connection_logo_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-info borderRadiusNone" id="editArchiveDetails">Update</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="clearArchiveDetailsForm">Clear Form</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="crop_uploading_image" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <button class="btn btn-info borderRadiusNone" id="close_crop_popup">Crop & Save</button>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="image_display_section">
                <img class="crop-popup-image-section" id="archive_logo_image_pre" src="" alt="your image" style="display:none;" />
                <img class="crop-popup-image-section" id="archive_details_image_pre" src="" alt="your image" style="display:none;" />
                <img class="crop-popup-image-section" id="archive_banner_image_pre" src="" alt="your image" style="display:none;" />
            </div>
        </div>
    </div>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH . 'jquery.inputmask.bundle.js'; ?>"></script>
<script src="<?php echo JS_PATH . 'tinymce/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
    var jcrop_api_logo;
    var countLogo = 0;
    function readURLLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    aspectRatio: 1,
                    onSelect: updateCoordsLogo,
                    minSize: [200, 200],
                    maxSize: [200, 200],
                    setSelect: [100, 100, 50, 50]
                }, function () {
                    jcrop_api_logo = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCoordsLogo(c) {
        $('#archive_logo_x').val(c.x);
        $('#archive_logo_y').val(c.y);
    }
    ;

    var jcrop_api_header;
    var countheader = 0;
    function readURLheader(input,height) {
        if (input.files && input.files[0]) {
            console.log(height);
            var crop_height = 1300;
            if(height == 800){
                crop_height = 2000;
            }
            
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    onSelect: updateCoordsheader,
                    minSize: [crop_height, 3900],
                    maxSize: [crop_height, 3900],
                    aspectRatio: 3 / 1,
                    setSelect: [100, 100, 50, 50],
                }, function () {
                    jcrop_api_header = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
            $('#baner_crop_height').val(height);
        }
    }
    function updateCoordsheader(c) {
        $('#archive_header_x').val(c.x);
        $('#archive_header_y').val(c.y);
    }

    var jcrop_api_details;
    var countdetails = 0;
    function readURLdetails(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    aspectRatio: 1,
                    onSelect: updateCoordsdetails,
                    minSize: [500, 1100],
                    maxSize: [500, 1100],
                    setSelect: [100, 100, 50, 50]
                }, function () {
                    jcrop_api_details = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCoordsdetails(c) {
        $('#archive_details_x').val(c.x);
        $('#archive_details_y').val(c.y);
    };


    var jcrop_api_gruop_thumb;
    var countgroupthumb = 0;
    function readURLarchiveGroupThumb(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    aspectRatio: 1,
                    onSelect: updateCoordsGroupThumb,
                    minSize: [400, 400],
                    maxSize: [400, 400],
                    setSelect: [100, 100, 50, 50]
                }, function () {
                    jcrop_api_gruop_thumb = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function updateCoordsGroupThumb(c) {
        $('#archive_group_thumb_x').val(c.x);
        $('#archive_group_thumb_y').val(c.y);
    }
    
    //Seprate details page logo
    var jcrop_api_gruop_details_thumb;
    var countgroupthumbDetails = 0;
    function readURLarchiveGroupDetailsThumb(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    aspectRatio: 1,
                    onSelect: updateCoordsGroupDetailsThumb,
                    minSize: [400, 400],
                    maxSize: [400, 400],
                    setSelect: [100, 100, 50, 50]
                }, function () {
                    jcrop_api_gruop_details_thumb = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function updateCoordsGroupDetailsThumb(c) {
        $('#archive_group_details_thumb_x').val(c.x);
        $('#archive_group_details_thumb_y').val(c.y);
    }
    //Seprate details page logo end
    
    
    var jcrop_api_hist_connection_logo;
    var countHistConnLogo = 0;
    function readURLHistConnectionLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    aspectRatio: 1,
                    onSelect: updateCoordsHIstConnectionLogo,
                    minSize: [200, 200],
                    maxSize: [200, 200],
                    setSelect: [100, 100, 50, 50]
                }, function () {
                    jcrop_api_hist_connection_logo = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function updateCoordsHIstConnectionLogo(c) {
        $('#historical_connection_logo_x').val(c.x);
        $('#historical_connection_logo_y').val(c.y);
    }
    
    
    
    

    $(document).ready(function () {
        get_state();
        $('#archive_display_zip').keypress(function (e) {
            var key = e.which;
            if (key == 13)
            {
                $('#editArchiveDetails').click();
            }
        });

        tinymce.init({
            selector: '#archive_request_reprint_text',
            height: 200,
            branding: false,
            theme: 'modern',
            plugins: 'image link media template codesample table charmap hr pagebreak nonbreaking anchor textcolor wordcount imagetools contextmenu colorpicker',
            toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | fontsizeselect',
            image_advtab: true
        });
        var phones = [{"mask": "(###) ###-####"}, {"mask": "(###) ###-##############"}];
        $('#archive_contact_number').inputmask({
            mask: phones,
            greedy: false,
            definitions: {'#': {validator: "[0-9]", cardinality: 1}}
        });

        $("#archive_logo_image").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 200 && imgheight <= 200) {
                    showPopupMessage('error', 'Image dimension must be at least 200X200(Width X Height)');
                    return false;
                } else {
                    $('#type').val('logo');
                    if (countLogo > 0) {
                        jcrop_api_logo.destroy();
                    }
                    countLogo = countLogo + 1;
                    readURLLogo(currentObj);
                }
            };
        });
					
		$(document).on('click','#custom1',function(){
			$('#custom_design').prop('checked', true);
		});
		$(document).on('click','#custom2',function(){
			$('#custom_design1').prop('checked', true);
		});
		$(document).on('click','#default',function(){
			$('#default_design').prop('checked', true);
		});

        $("#archive_header_image").change(function () {
            var custom_temp = $('input[name=custom_template]:checked').val();
            var crop_height = 400 ; 
            if(custom_temp !='default'){
                crop_height = 800;
            }
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 1600 && imgheight <= crop_height) {
                    showPopupMessage('error', 'Image dimension must be at least 1600X'+crop_height+'(Width X Height)');
                    return false;
                } else {
                    $('#type').val('banner');
                    if (countheader > 0) {
                        jcrop_api_header.destroy();
                    }
                    countheader = countheader + 1;
                    readURLheader(currentObj,crop_height);
                }
            };
        });
        $("#archive_details_image").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 645 && imgheight <= 430) {
                    showPopupMessage('error', 'Image dimension must be at least 645X430(Width X Height)');
                    return false;
                } else {
                    $('#type').val('content');
                    if (countdetails > 0) {
                        jcrop_api_details.destroy();
                    }
                    countdetails = countdetails + 1;
                    readURLdetails(currentObj);
                }
            };
        });

        $("#archive_group_thumb").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 400 && imgheight <= 400) {
                    showPopupMessage('error', 'Image dimension must be at least 400X400(Width X Height)');
                    return false;
                } else {
                    $('#type').val('archive_group_thumb');
                    if (countgroupthumb > 0) {
                        jcrop_api_gruop_thumb.destroy();
                    }
                    countgroupthumb = countgroupthumb + 1;
                    readURLarchiveGroupThumb(currentObj);
                }
            };
        });
        
        //Seprate details page logo start
        $("#archive_group_details_thumb").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 400 && imgheight <= 400) {
                    showPopupMessage('error', 'Image dimension must be at least 400X400(Width X Height)');
                    return false;
                } else {
                    $('#type').val('archive_group_details_thumb');
                    if (countgroupthumbDetails > 0) {
                        jcrop_api_gruop_details_thumb.destroy();
                    }
                    countgroupthumbDetails = countgroupthumbDetails + 1;
                    readURLarchiveGroupDetailsThumb(currentObj);
                }
            };
        });
        //End Seprate details page logo
        
        $("#historical_connection_logo").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 200 && imgheight <= 200) {
                    showPopupMessage('error', 'Image dimension must be at least 200X200(Width X Height)');
                    return false;
                } else {
                    $('#type').val('historical_connection_logo');
                    if (countHistConnLogo > 0) {
                        jcrop_api_hist_connection_logo.destroy();
                    }
                    countHistConnLogo = countHistConnLogo + 1;
                    readURLHistConnectionLogo(currentObj);
                }
            };
        });

        $(".modal").on("hidden.bs.modal", function () {
            $(this).removeData();
        });
        var archive_id = '<?php echo $archive_id; ?>';
        getItemPropDetails(archive_id);
        //Validate login form
        $("#editArchiveGroupDetails-no").validate({
            rules: {
                archive_title: {
                    required: true
                },
                archive_address: {
                    required: true
                },
                archive_about_content: {
                    required: true
                },
                archive_details_description: {
                    required: true
                },
                archive_contact_number: {
                    required: true
                },
                archive_zip: {
                    required: true,
                    number: true
                },
                archive_state: {
                    required: true
                },
                archive_city: {
                    required: true
                },
                archive_website: {
                    required: true
                },
                archive_timing: {
                    required: true
                },
                archive_watermark_text: {
                    required: true
                }
            },
            messages: {
                archive_title: {
                    required: "Title is required"
                },
                archive_address: {
                    required: "Address is required"
                },
                archive_about_content: {
                    required: "About content is required"
                },
                archive_details_description: {
                    required: "Detail description is required"
                },
                archive_contact_number: {
                    required: "Contact number is required"
                },
                archive_zip: {
                    required: "Zip code is required",
                    number: "Enter valid zip code only."
                },
                archive_state: {
                    required: "Archive state is required."
                },
                archive_city: {
                    required: "Archive city is required."
                },
                archive_website: {
                    required: "Website is required"
                },
                archive_timing: {
                    required: "Timing is required"
                },
                archive_watermark_text: {
                    required: "Watermark text is required"
                }
            }
        });


        $(window).resize(function () {
            var height = $(this).height() - 130;
            $('#crop_uploading_image .modal-body').css('height', height + 'px');
        });
        $(window).resize();
        
        //Changes start for color and font option
        $('.colorpicker').each(function(){
            var background_applied_for = $(this).attr('data-type');
            $(this).colpick({
                layout: 'hex',
                submit: 0,
                colorScheme: 'dark',
                onChange: function (hsb, hex, rgb, el, bySetColor) {
                    $(el).css('border-color', '#' + hex);
                    if (!bySetColor) {
                        $(el).val(hex);
                        $('#color_code').val(hex);
                        $('#color_code_selected_for').val(background_applied_for);
                    }
                }
            });
        });
        $('.fontSelect').each(function(){
            var font_applied_for =  $(this).attr('data-type');
            $(this).fontSelector({
                'hide_fallbacks': true,
                'initial': 'Select Font,Arial',
                'selected': function (style) {
                    if (style != 'Select Font,Arial'){
                        update_font_face(font_applied_for, style);
                    }
                },
                'fonts': [
                    'Select Font,Select Font',
                    'Arial,Arial,Helvetica,sans-serif',
                    'Arial Black,Arial Black,Gadget,sans-serif',
                    'Comic Sans MS,Comic Sans MS,cursive',
                    'Courier New,Courier New,Courier,monospace',
                    'Georgia,Georgia,serif',
                    'Impact,Charcoal,sans-serif',
                    'Lucida Console,Monaco,monospace',
                    'Lucida Sans Unicode,Lucida Grande,sans-serif',
                    'Palatino Linotype,Book Antiqua,Palatino,serif',
                    'Tahoma,Geneva,sans-serif',
                    'Times New Roman,Times,serif',
                    'Trebuchet MS,Helvetica,sans-serif',
                    'Verdana,Geneva,sans-serif',
                    'Gill Sans,Geneva,sans-serif'
                ]
            });
        });
        //Changes end for color and font option
    });
    
    $(document).on('click', '#editArchiveDetails', function (e) {
        e.preventDefault();
        //if($("#editArchiveGroupDetails").valid()){
        $('.admin-loading-image').show();
        var archiveGroupetails = $("#editArchiveGroupDetails").serialize();
        var reprint_request_data = tinymce.get("archive_request_reprint_text").getContent();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_archive_group_details', formData: archiveGroupetails, reprint_request_data: reprint_request_data},
            success: function (response) {
                var record = JSON.parse(response);
                if (record.status == 'success') {
                    $('.admin-loading-image').hide();
                    window.location.href = "manage_my_archive.php";
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 183)');
            }
        });
        //}
    });

    function getItemPropDetails(archive_id) {
        if (archive_id != '') {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_archive_prop_details', archive_id: archive_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.item_title) {
                        $('#archive_name').val(record.item_title);
                    }
                    /* if(record.prop_details.archive_title){
                     $('#archive_title').val(record.prop_details.archive_title);
                     } */
                    if (record.prop_details.archive_address) {
                        $('#archive_address').val(record.prop_details.archive_address);
                    }
                    if (record.prop_details.archive_about_content) {
                        $('#archive_about_content').val(record.prop_details.archive_about_content);
                    }
                    if (record.prop_details.archive_upcoming_events) {
                        $('#archive_upcoming_events').val(record.prop_details.archive_upcoming_events);
                    }
                    if (record.prop_details.archive_contact_number) {
                        $('#archive_contact_number').val(record.prop_details.archive_contact_number);
                    }
                    if (record.prop_details.preferred_time_zone) {
                        // $('#document.getElementById("mySelect").value ').val(record.prop_details.archive_contact_number); 
                        document.getElementById("preferred_time_zone").value = record.prop_details.preferred_time_zone;
                    }
                    /*if(record.prop_details.archive_state){
                     $('#archive_state').val(record.prop_details.archive_state);
                     }
                     if(record.prop_details.archive_city){
                     $('#archive_city').val(record.prop_details.archive_city);
                     }
                     if(record.prop_details.archive_zip){
                     $('#archive_zip').val(record.prop_details.archive_zip);
                     } */
                    if (record.prop_details.archive_website) {
                        $('#archive_website').val(record.prop_details.archive_website);
                    }
                    if (record.prop_details.archive_timing) {
                        $('#archive_timing').val(record.prop_details.archive_timing);
                    }
                    if (record.prop_details.archive_watermark_text) {
                        $('#archive_watermark_text').val($.trim(record.prop_details.archive_watermark_text));
                    }
                    if (record.prop_details.archive_details_description) {
                        $('#archive_details_description').val(record.prop_details.archive_details_description);
                    }

                    if (record.prop_details.archive_display_state) {
                        $('#archive_display_state').val(record.prop_details.archive_display_state);
                    }
                    if (record.prop_details.archive_display_county) {
                        $('#archive_display_county').val(record.prop_details.archive_display_county);
                    }
                    if (record.prop_details.archive_display_city) {
                        $('#archive_display_city').val(record.prop_details.archive_display_city);
                    }
                    if (record.prop_details.archive_display_zip) {
                        $('#archive_display_zip').val(record.prop_details.archive_display_zip);
                    }

                    setTimeout(function () {
                        tinymce.get("archive_request_reprint_text").setContent(record.prop_details.archive_request_reprint_text);
                    }, 1000);

                    if (typeof record.prop_details.custom_template !== 'undefined' && record.prop_details.custom_template == 'custom1') {
                        $('#custom1,#custom_design').attr('checked', true);
                        $('.custom_temp_crop').text('(Must be at least 1600 X 800 (Width X Height)) ');
                        $('.browse_home').attr("href", "<?php echo '../'.$browseUrl.'&society_template=' ?>"+record.prop_details.custom_template);
                    } else if (typeof record.prop_details.custom_template !== 'undefined' && record.prop_details.custom_template == 'custom2') {
                        $('#custom2,#custom_design1').attr('checked', true);
                        $('.custom_temp_crop').text('(Must be at least 1600 X 800 (Width X Height)) ');
                        $('.browse_home').attr("href", "<?php echo '../'.$browseUrl.'&society_template=' ?>"+record.prop_details.custom_template);
                    } else {
                        $('#default,#default_design').attr('checked', true);
                        $('.browse_home').attr("href", "<?php echo '../'.$browseUrl.'&society_template=' ?>"+record.prop_details.custom_template);
                    }

                    /*details_page_design_checkbox*/
                        if(typeof record.prop_details.custom_template !== 'undefined'){
							if(record.prop_details.details_page_design=='custom1'){
								$('#custom_design1').attr('checked', true);
							}else if(record.prop_details.details_page_design=='custom'){
								$('#custom_design').attr('checked', true);
							} else{
								$('#default_design').attr('checked', true);
							}
                            //$("input[type='radio'][value='"+record.prop_details.details_page_design+"']").attr('checked', true);
                        }
						/*else{
                            $("input[type='radio'][value='default']").attr('checked', true);
                        }*/
                        // if(record.prop_details.details_page_design == "custom"){
                        //     $('#custom_design').attr('checked',true);
                        // }else{
                        //     $('#default_design').attr('checked',true);
                        // }
                    /**details_page_design_checkbox**/

                    if (record.prop_details.archive_logo_image && record.prop_details.archive_logo_image !== 'undefined') {
                        $('#archive_logo_image_post_display').attr('src', 'tmp/' + record.prop_details.archive_logo_image);
                        $('#archive_logo_image_post_display').show();
                    }else{
                        $('#archive_logo_image_post_display').siblings('a.remove-society-image').hide();
                    }
                    if (record.prop_details.archive_header_image && record.prop_details.archive_header_image !== 'undefined') {
                        $('#archive_header_image_post').attr('src', 'tmp/' + record.prop_details.archive_header_image);
                        $('#archive_header_image_post').show();
                    }else{
                        $('#archive_header_image_post').siblings('a.remove-society-image').hide();
                    }
                    if (record.prop_details.archive_details_image && record.prop_details.archive_details_image !== 'undefined') {
                        $('#archive_details_image_post').attr('src', 'tmp/' + record.prop_details.archive_details_image);
                        $('#archive_details_image_post').show();
                    }else{
                        $('#archive_details_image_post').siblings('a.remove-society-image').hide();
                    }
                    if (record.prop_details.archive_group_thumb && record.prop_details.archive_group_thumb !== 'undefined') {
                        $('#archive_group_thumb_post').attr('src', 'tmp/' + record.prop_details.archive_group_thumb);
                        $('#archive_group_thumb_post').show();
                    }else{
                        $('#archive_group_thumb_post').siblings('a.remove-society-image').hide();
                    }
                    if (record.prop_details.archive_group_details_thumb && record.prop_details.archive_group_details_thumb !== 'undefined') {
                        $('#archive_group_details_thumb_img').attr('src', 'tmp/' + record.prop_details.archive_group_details_thumb);
                        $('#archive_group_details_thumb_img').show();
                    }else{
                        $('#archive_group_details_thumb_img').siblings('a.remove-society-image').hide();
                    }
                    if (record.prop_details.historical_connection_logo && record.prop_details.historical_connection_logo !== 'undefined') {
                        $('#historical_connection_logo_img').attr('src', 'tmp/' + record.prop_details.historical_connection_logo);
                        $('#historical_connection_logo_img').show();
                    }else{
                        $('#historical_connection_logo_img').siblings('a.remove-society-image').hide();
                    }
                    if(record.prop_details.description_background != ''){
                        $('#description_background').css('border-color', '#'+record.prop_details.description_background);
                    }
                    
                    if(record.prop_details.heading_font_color != ''){
                        $('#heading_font_color').css('border-color', '#'+record.prop_details.heading_font_color);
                    }
                    if(record.prop_details.content_font_color != ''){
                        $('#content_font_color').css('border-color', '#'+record.prop_details.content_font_color);
                    }
                    if(record.prop_details.button_font_color != ''){
                        $('#button_font_color').css('border-color', '#'+record.prop_details.button_font_color);
                    }
                    
                    if(record.prop_details.button_background != ''){
                        $('#button_background').css('border-color', '#'+record.prop_details.button_background);
                    }
                    if(record.prop_details.heading_font_size != ''){
                        $('#heading_font_size').val(record.prop_details.heading_font_size);
                    }
                    if(record.prop_details.content_font_size != ''){
                        $('#content_font_size').val(record.prop_details.content_font_size);
                    }
                    if(record.prop_details.button_font_size != ''){
                        $('#button_font_size').val(record.prop_details.button_font_size);
                    }
                    if(record.prop_details.heading_font != ''){
                        selectDefaultFont('heading_font', record.prop_details.heading_font);
                    }
                    if(record.prop_details.content_font != ''){
                        selectDefaultFont('content_font', record.prop_details.content_font);
                    }
                    if(record.prop_details.button_font != ''){
                        selectDefaultFont('button_font', record.prop_details.button_font);
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 184)');
                }
            });
        }
    }
    $(document).on('click', '#close_crop_popup', function (e) {
        e.preventDefault();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php?mode=upload_croped_image",
            type: "post",
            data: new FormData($("#editArchiveGroupDetails")[0]),
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                var record = JSON.parse(response);
                if (record.status == 'success') {
                    if (record.type == 'logo') {
                        $('#archive_logo_image_post_display').attr('src', record.image);
                        $('#archive_logo_image_post_display').show();
                        $('#archive_logo_image_post_display').siblings('a.remove-society-image').show();
                    }
                    if (record.type == 'banner') {
                        $('#archive_header_image_post').attr('src', record.image);
                        $('#archive_header_image_post').show();
                        $('#archive_header_image_post').siblings('a.remove-society-image').show();
                    }
                    if (record.type == 'content') {
                        $('#archive_details_image_post').attr('src', record.image);
                        $('#archive_details_image_post').show();
                        $('#archive_details_image_post').siblings('a.remove-society-image').show();
                    }
                    if (record.type == 'archive_group_thumb') {
                        $('#archive_group_thumb_post').attr('src', record.image);
                        $('#archive_group_thumb_post').show();
                        $('#archive_group_thumb_post').siblings('a.remove-society-image').show();
                    }
                    if (record.type == 'archive_group_details_thumb') {
                        $('#archive_group_details_thumb_img').attr('src', record.image);
                        $('#archive_group_details_thumb_img').show();
                        $('#archive_group_details_thumb_img').siblings('a.remove-society-image').show();
                    }
                    if (record.type == 'historical_connection_logo') {
                        $('#historical_connection_logo_img').attr('src', record.image);
                        $('#historical_connection_logo_img').show();
                        $('#historical_connection_logo_img').siblings('a.remove-society-image').show();
                    }
                }
                $('.admin-loading-image').hide();
                $('#crop_uploading_image').modal('hide');
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 185)');
            }
        });
    });
    
    $(document).on('click','.preview-template',function(){
        var preview_url = '<?php echo '../'.$browseUrl.'&society_template=' ?>'+$('input[name=custom_template]:checked').val(); 
        window.open(preview_url, '_blank');
        return false;
    });
    
    function get_state() {
        $('.loading-div').show();
        var parent_id = '<?php echo STATE_PARENT_ID; ?>';
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'get_state_country', parent_id: parent_id},
            success: function (response) {
                var record = JSON.parse(response);
                var i;
                var display_state = "";

                display_state += "<option value='' >---Select---</option>";
                for (i = 0; i < record.length; i++) {
                    display_state += "<option value='" + record[i] + "'  >" + record[i] + "</option>";
                }
                $(".display_state_list").html(display_state);
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 186)');
                $('.loading-div').hide();
            }
        });
    }
    $(document).on('change','input[type=radio][name=custom_template]',function(){
           if($(this).val() =='default'){
              $('.custom_temp_crop').text('(Must be at least 1600 X 400 (Width X Height)) '); 
           }else{
                $('.custom_temp_crop').text('(Must be at least 1600 X 800 (Width X Height)) '); 
           }
    }); 
    
    function update_font_face(field_name, font){
        setArchiveProperty(field_name, font);
    }
    
    $(document).on('mouseup','.colpick', function(){
        var color_code  = $('#color_code').val();
        var color_field = $('#color_code_selected_for').val();
        if(color_code != '' && color_field != ''){
            setArchiveProperty(color_field, color_code);
        }
    });
    
    $(document).on('change', '.change-font-size', function(){
        var font_size  = $(this).val();
        var font_field = $(this).attr('data-type');
        if(font_size != '' && font_field != ''){
            setArchiveProperty(font_field, font_size);
        }
    });
    
    function setArchiveProperty(field_name, field_value){
        if(field_name != '' && field_value != ''){
            var archive_id = $('#archive_id').val();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_archive_font_color', field_name: field_name, field_value: field_value, archive_id: archive_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'success') {
                        $('.admin-loading-image').hide();
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 187)');
                }
            });
        }
    }
    
    function selectDefaultFont(containerId, selectedFont = ''){
        if(selectedFont != ''){
            var font= selectedFont.substr(0, selectedFont.indexOf(','));
            font = font.replace(/'/g, '');
            font = font.replace(/"/g, '');
            $('#'+containerId).children('span').text(font);
            $('#'+containerId).css('font-family',selectedFont);
        }
    }
    
    $(document).on('click', '.remove-society-image', function(){
        var property_name = $(this).attr('property-name');
        var item_id       = $('#archive_id').val();
        if(confirm('Are you sure to delete this? Once removed can\'t be undone.')){
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'remove_society_images', property_name: property_name, item_id: item_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status == 'success'){
                        showPopupMessage(record.status, record.message);
                        window.location.reload();
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 188)');
                }
            });
        }
    });
</script>