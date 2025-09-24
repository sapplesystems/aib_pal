<?php
include_once '../../config/config.php';
$location_id = $_POST['location_id'];
?>
<form name="manage_home_page_template_help_message_frm" id="manage_home_page_template_help_message_frm" action="" method="post">
    <div class="row"  id="dataTableDiv">
        <div class="col-md-12 tableStyle">
            <div class="tableScroll">
                <table id="myTable" class="display table helpMessage" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th class="text-center">Element</th> 
                            <th class="text-center">Element</th>
                        </tr>  
                    </thead>  
                    <tbody id="listdata">
                        <tr>
                            <td>
                                <label>Historical Society Name</label>
                                <textarea class="form-control editor_style" id="historical_society_name" name="historical_society_name"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'historical_society_name', '1', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Address</label>
                                <textarea class="form-control editor_style" id="address" name="address"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'address', '2', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>About Content</label>
                                <textarea class="form-control editor_style" id="about_content" name="about_content"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'about_content', '3', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Detail Description</label>
                                <textarea class="form-control editor_style" id="detail_description" name="detail_description"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'detail_description', '4', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Request Reprint Text</label>
                                <textarea class="form-control editor_style" id="request_reprint_text" name="request_reprint_text"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'request_reprint_text', '5', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Upcoming Events Text</label>
                                <textarea class="form-control editor_style" id="upcoming_event_text" name="upcoming_event_text"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'upcoming_event_text', '6', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Preferred Time Zone</label>
                                <textarea class="form-control editor_style" id="preferred_time_zone" name="preferred_time_zone"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'preferred_time_zone', '7', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Contact No.</label>
                                <textarea class="form-control editor_style" id="contact_no" name="contact_no"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'contact_no', '8', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Website</label>
                                <textarea class="form-control editor_style" id="website" name="website"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'website', '9', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Hours</label>
                                <textarea class="form-control editor_style" id="hours" name="hours"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'hours', '10', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Watermark Text</label>
                                <textarea class="form-control editor_style" id="watermark_text" name="watermark_text"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'watermark_text', '11', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Display State</label>
                                <textarea class="form-control editor_style" id="display_state" name="display_state"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_state', '12', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Display County</label>
                                <textarea class="form-control editor_style" id="display_county" name="display_county"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_county', '13', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Display City</label>
                                <textarea class="form-control editor_style" id="display_city" name="display_city"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_city', '14', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Display Zip</label>
                                <textarea class="form-control editor_style" id="display_zip" name="display_zip"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_zip', '15', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Templates</label>
                                <textarea class="form-control editor_style" id="templates" name="templates"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'templates', '16', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Logo Image</label>
                                <textarea class="form-control editor_style" id="logo_image" name="logo_image"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'logo_image', '17', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Banner Image</label>
                                <textarea class="form-control editor_style" id="banner_image" name="banner_image"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'banner_image', '18', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Details Image</label>
                                <textarea class="form-control editor_style" id="details_image" name="details_image"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'details_image', '19', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Archive Group Thumb</label>
                                <textarea class="form-control editor_style" id="archive_group_thumb" name="archive_group_thumb"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'archive_group_thumb', '20', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Record Display Page Logo</label>
                                <textarea class="form-control editor_style" id="record_display_page_logo" name="record_display_page_logo"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'record_display_page_logo', '21', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Hist. Connection Logo</label>
                                <textarea class="form-control editor_style" id="hist_connection_logo" name="hist_connection_logo"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'hist_connection_logo', '22', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
						<tr>
                            <td>
                                <label>Tags</label>
                                <textarea class="form-control editor_style" id="tags" name="tags"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'tags', 'ut23', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Show Contact Button</label>
                                <textarea class="form-control editor_style" id="show_contact" name="show_contact"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'show_contact', 'u24', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
						<tr>
                            <td>
                                <label>Show Purchase Reprint Button</label>
                                <textarea class="form-control editor_style" id="show_purchase_reprint" name="show_purchase_reprint"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'show_purchase_reprint', 'u25', '<?php echo $location_id; ?>');">
                            </td>
							<td>
                                <label>Display Name</label>
                                <textarea class="form-control editor_style" id="display_name" name="display_name"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'display_name', 'u26', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
						<tr>
							<td>
                                <label>Promotional Image URL</label>
                                <textarea class="form-control editor_style" id="promo_img_url" name="promo_img_url"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'promo_img_url', 'u27', '<?php echo $location_id; ?>');">
                            </td>
			<td>
				<label>Society Banner Image</label>
				<textarea class="form-control editor_style" id="society_banner_image" name="society_banner_image"></textarea>
				<img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event,'society_banner_image', 'u28', '<?php echo $location_id; ?>');">
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
