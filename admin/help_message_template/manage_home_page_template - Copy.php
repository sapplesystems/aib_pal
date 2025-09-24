<?php
include_once '../../config/config.php';
$location_id = $_POST['location_id'];
?>
<form name="manage_home_page_template_help_message_frm" id="manage_home_page_template_help_message_frm" action="" method="post">
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <tr>
                    <td>Historical Society Name :</td>
                    <td><textarea class="form-control editor_style" id="historical_society_name" name="historical_society_name"></textarea></td>
                </tr>
                <tr>
                    <td>Address :</td>
                    <td><textarea class="form-control editor_style" id="address" name="address"></textarea></td>
                </tr>
                <tr>
                    <td>About Content :</td>
                    <td><textarea class="form-control editor_style" id="about_content" name="about_content"></textarea></td>
                </tr>
                <tr>
                    <td>Detail Description :</td>
                    <td><textarea class="form-control editor_style" id="detail_description" name="detail_description"></textarea></td>
                </tr>
                <tr>
                    <td>Request Reprint Text :</td>
                    <td><textarea class="form-control editor_style" id="request_reprint_text" name="request_reprint_text"></textarea></td>
                </tr>
                <tr>
                    <td>Upcoming Events Text :</td>
                    <td><textarea class="form-control editor_style" id="upcoming_event_text" name="upcoming_event_text"></textarea></td>
                </tr>
                <tr>
                    <td>Preferred Time Zone :</td>
                    <td><textarea class="form-control editor_style" id="preferred_time_zone" name="preferred_time_zone"></textarea></td>
                </tr>
                <tr>
                    <td>Contact No. :</td>
                    <td><textarea class="form-control editor_style" id="contact_no" name="contact_no"></textarea></td>
                </tr>
                <tr>
                    <td>Website :</td>
                    <td><textarea class="form-control editor_style" id="website" name="website"></textarea></td>
                </tr>
                <tr>
                    <td>Hours :</td>
                    <td><textarea class="form-control editor_style" id="hours" name="hours"></textarea></td>
                </tr>
                <tr>
                    <td>Watermark Text :</td>
                    <td><textarea class="form-control editor_style" id="watermark_text" name="watermark_text"></textarea></td>
                </tr>
                <tr>
                    <td>Display State :</td>
                    <td><textarea class="form-control editor_style" id="display_state" name="display_state"></textarea></td>
                </tr>
                <tr>
                    <td>Display County :</td>
                    <td><textarea class="form-control editor_style" id="display_county" name="display_county"></textarea></td>
                </tr>
                <tr>
                    <td>Display City :</td>
                    <td><textarea class="form-control editor_style" id="display_city" name="display_city"></textarea></td>
                </tr>
                <tr>
                    <td>Display Zip :</td>
                    <td><textarea class="form-control editor_style" id="display_zip" name="display_zip"></textarea></td>
                </tr>
                <tr>
                    <td>Templates :</td>
                    <td><textarea class="form-control editor_style" id="templates" name="templates"></textarea></td>
                </tr>
                <tr>
                    <td>Logo Image :</td>
                    <td><textarea class="form-control editor_style" id="logo_image" name="logo_image"></textarea></td>
                </tr>
                <tr>
                    <td>Banner Image :</td>
                    <td><textarea class="form-control editor_style" id="banner_image" name="banner_image"></textarea></td>
                </tr>
                <tr>
                    <td>Details Image :</td>
                    <td><textarea class="form-control editor_style" id="details_image" name="details_image"></textarea></td>
                </tr>
                <tr>
                    <td>Archive Group Thumb :</td>
                    <td><textarea class="form-control editor_style" id="archive_group_thumb" name="archive_group_thumb"></textarea></td>
                </tr>
                <tr>
                    <td>Record Display Page Logo :</td>
                    <td><textarea class="form-control editor_style" id="record_display_page_logo" name="record_display_page_logo"></textarea></td>
                </tr>
                <tr>
                    <td>Hist. Connection Logo :</td>
                    <td><textarea class="form-control editor_style" id="hist_connection_logo" name="hist_connection_logo"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" class="btn btn-success" value="Submit" name="manage_home_page_template_help_message_frm_sub" id="manage_home_page_template_help_message_frm_sub" />
                        <input type="reset" class="btn btn-danger" value="Clear" />
                        <input type="hidden" name="location_id" value="<?php echo $location_id; ?>" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>

<script src="<?php echo JS_PATH . 'tinymce/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
    /*$(document).ready(function () {
        tinymce.init({
            selector: '.editor_style',
            height: 5,
            branding: false,
            theme: 'modern',
            plugins: 'image link media template codesample table charmap hr pagebreak nonbreaking anchor textcolor wordcount imagetools contextmenu colorpicker',
            toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | fontsizeselect',
            image_advtab: true
        });
    });*/
</script>
<?php
exit;
?>