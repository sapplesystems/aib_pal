<?php
include_once '../../config/config.php';
$location_id = $_POST['location_id'];
?>
<form name="manage_home_page_template_help_message_frm" id="manage_home_page_template_help_message_frm" action="" method="post">
    <div class="row"  id="dataTableDiv">
        <div class="col-md-12 tableStyle">
            <div class="tableScroll">
                <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th class="text-center">Element Name</th> 
                            <th class="text-center">Help Message</th>
                            <th class="text-center">Actions</th>
                        </tr>  
                    </thead>  
                    <tbody id="listdata">
                        <tr>
                            <td>Historical Society Name</td>
                            <td><textarea class="form-control editor_style" id="historical_society_name" name="historical_society_name"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'historical_society_name', '1', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td><textarea class="form-control editor_style" id="address" name="address"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'address', '2', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>About Content</td>
                            <td><textarea class="form-control editor_style" id="about_content" name="about_content"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'about_content', '3', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Detail Description</td>
                            <td><textarea class="form-control editor_style" id="detail_description" name="detail_description"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'detail_description', '4', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Request Reprint Text</td>
                            <td><textarea class="form-control editor_style" id="request_reprint_text" name="request_reprint_text"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'request_reprint_text', '5', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Upcoming Events Text</td>
                            <td><textarea class="form-control editor_style" id="upcoming_event_text" name="upcoming_event_text"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'upcoming_event_text', '6', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Preferred Time Zone</td>
                            <td><textarea class="form-control editor_style" id="preferred_time_zone" name="preferred_time_zone"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'preferred_time_zone', '7', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Contact No.</td>
                            <td><textarea class="form-control editor_style" id="contact_no" name="contact_no"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'contact_no', '8', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Website</td>
                            <td><textarea class="form-control editor_style" id="website" name="website"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'website', '9', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Hours</td>
                            <td><textarea class="form-control editor_style" id="hours" name="hours"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'hours', '10', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Watermark Text</td>
                            <td><textarea class="form-control editor_style" id="watermark_text" name="watermark_text"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'watermark_text', '11', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Display State</td>
                            <td><textarea class="form-control editor_style" id="display_state" name="display_state"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_state', '12', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Display County</td>
                            <td><textarea class="form-control editor_style" id="display_county" name="display_county"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_county', '13', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Display City</td>
                            <td><textarea class="form-control editor_style" id="display_city" name="display_city"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_city', '14', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Display Zip</td>
                            <td><textarea class="form-control editor_style" id="display_zip" name="display_zip"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_zip', '15', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Templates</td>
                            <td><textarea class="form-control editor_style" id="templates" name="templates"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'templates', '16', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Logo Image</td>
                            <td><textarea class="form-control editor_style" id="logo_image" name="logo_image"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'logo_image', '17', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Banner Image</td>
                            <td><textarea class="form-control editor_style" id="banner_image" name="banner_image"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'banner_image', '18', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Details Image</td>
                            <td><textarea class="form-control editor_style" id="details_image" name="details_image"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'details_image', '19', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Archive Group Thumb</td>
                            <td><textarea class="form-control editor_style" id="archive_group_thumb" name="archive_group_thumb"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'archive_group_thumb', '20', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Record Display Page Logo</td>
                            <td><textarea class="form-control editor_style" id="record_display_page_logo" name="record_display_page_logo"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'record_display_page_logo', '21', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>Hist. Connection Logo</td>
                            <td><textarea class="form-control editor_style" id="hist_connection_logo" name="hist_connection_logo"></textarea></td>
                            <td>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'hist_connection_logo', '22', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                    </tbody>  
                </table> 
            </div>
        </div>
    </div>
</form>
<?php
exit;
?>