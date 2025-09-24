<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
$folder_id = $_REQUEST['folder_id'];
?>
<style>
    mark {
        background-color: #fbd42f !important;
    }
</style>
<div class="header_img">
    <div class="bannerImage"></div>
</div>
<div class="clearfix"></div>
<form class="form-horizontal" name="claim_society_frm" id="claim_society_frm" action="" method="POST" autocomplete="off">
    <input type="hidden" name="user_type" value="A">
    <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
        <div class="container" id="register_data">
            <div class="row marginTop20 bgNone">
                <h3>Claim Society</h3>
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label for="exampleInputEmail1">Historical Society Name<span>*</span></label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" id="society_name" name="society_name" placeholder="">
                </div>
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label for="exampleInputEmail1">Username<span>*</span></label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" id="register_username" name="register_username" placeholder="">
                </div>
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label for="exampleInputEmail1">First Name<span>*</span></label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" id="claim_first_name" name="firstName" placeholder="">
                </div>
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label for="exampleInputEmail1">Last Name<span>*</span></label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" id="claim_last_name" name="lastName" placeholder="">
                </div>
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label for="exampleInputEmail1">Email Id<span>*</span></label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" id="register_emailId" name="register_emailId" placeholder="">
                </div>
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label for="exampleInputEmail1">Phone No.<span>*</span></label>
                </div>
                <div class="col-md-3" >
                    <input type="text" maxlength="14" class="form-control" id="claim_phone_no" name="claim_phone_no" placeholder="">
                </div>
            </div>
            <div class="row bgNone marginBottom40">
                <div class="col-md-5"></div>
                <div class="col-md-2">
                    <input type="hidden" name="name" id="user_title" value="">
                    <input type="hidden" name="user_type" value="A">
                    <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
                    <input type="hidden" name="society_id" id="society_id" value="<?php echo $folder_id; ?>">
                    <input type="hidden" name="request_type" id="request_type" value="CLAIM">
                    <input type="hidden" name="register_user_password" id="register_user_password" value="test">
                    <input type="button" class="form-control btn-success marginTop20" name="claim_society_frm_sub" id="claim_society_frm_sub" value="Register" >
                </div>
                <div class="col-md-5"></div>
            </div>
        </div>
    </div>
</form>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    var archive_id = '<?php echo $folder_id; ?>';
    var user_id = '1';

    $(document).ready(function () {
        var phones = [{"mask": "(###) ###-####"}, {"mask": "(###) ###-##############"}];
        $('#claim_phone_no').inputmask({
            mask: phones,
            greedy: false,
            definitions: {'#': {validator: "[0-9]", cardinality: 1}}
        });

        $("#claim_society_frm").validate({
            rules: {
                society_name: "required",
                register_username: "required",
                firstName: "required",
                lastName: "required",
                register_emailId: "required",
                claim_phone_no: "required",
            },
            messages: {
                society_name: "Please enter society name",
                register_username: "Please enter user name",
                firstName: "Please enter your first name",
                lastName: "Please enter your last name",
                register_emailId: "Please enter your email id",
                claim_phone_no: "Please enter your phone number",
            }
        });

        $("#claim_society_frm_sub").on("click", function (e) {
            e.preventDefault();
            if ($("#claim_society_frm").valid()) {
                var folder_id = '<?php echo $folder_id; ?>';
                $('.admin-loading-image').removeClass('hide');
                $('.admin-loading-image').addClass('show');
                var formData = $('#claim_society_frm').serialize();
                $.ajax({
                    url: "services.php",
                    type: "post",
                    data: {mode: 'register_new_user', formData: formData},
                    success: function (response) {
                        var record = JSON.parse(response);
                        $('.loading-div').hide();
                        document.body.scrollTop = 0;
                        if (record.status == 'success') {
                            $('.admin-loading-image').removeClass('show');
                            $('.admin-loading-image').addClass('hide');
                            document.getElementById('claim_society_frm').reset();
                            window.location.href = 'thank-you.html?flg=archive_user_reg';
                            return false;
                            /*$.ajax({
                             url: "services.php",
                             type: "post",
                             data: {mode: 'submit_request', formData: formData, item_id: folder_id},
                             success: function (response) {
                             var result = JSON.parse(response);
                             if (result.status == 'success') {
                             $('.admin-loading-image').removeClass('show');
                             $('.admin-loading-image').addClass('hide');
                             document.getElementById('claim_society_frm').reset();
                             window.location.href = 'thank-you.html?flg=archive_user_reg';
                             } else {
                             showPopupMessage('error', result.message);
                             }
                             },
                             error: function () {
                             showPopupMessage('error', 'Previous request not completed.');
                             }
                             });*/
                        }
                        else {
                            showPopupMessage('error', record.message + ' (Error Code: 357)');
                        }
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 358)');
                        $('.loading-div').hide();
                    }
                });
                /*$.ajax({
                 url: "services.php",
                 type: "post",
                 data: {mode: 'submit_request', formData: formData, item_id: folder_id},
                 success: function (response) {
                 var result = JSON.parse(response);
                 if (result.status == 'success') {
                 $('.admin-loading-image').removeClass('show');
                 $('.admin-loading-image').addClass('hide');
                 document.getElementById('claim_society_frm').reset();
                 } else {
                 showPopupMessage('error', result.message);
                 }
                 },
                 error: function () {
                 showPopupMessage('error', 'Previous request not completed.');
                 }
                 });*/
            }
        });
    });

    function getItemPropDetails(archive_id, user_id) {
        if (archive_id != '') {
            $('.admin-loading-image').show();
            $.ajax({
                url: "admin/services_admin_api.php",
                type: "post",
                data: {mode: 'get_archive_prop_details', archive_id: archive_id, user_id: user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.item_title) {
                        $('#society_name').val(record.item_title);
                        $('#society_id').val(record.item_id);
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 359)');
                }
            });
        }
    }

    //Call Initialy to load data
    if (archive_id) {
        getItemPropDetails(archive_id, user_id);
    }
</script>