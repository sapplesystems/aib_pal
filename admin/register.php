<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';

$folder_id = ($_REQUEST['folder_id']) ? $_REQUEST['folder_id'] : '';

if (isset($_POST) && $_POST['society_name'] != "") {
    unset($_SESSION['data1']);
    $_SESSION['data1'] = $_POST;
    $encrypted_url = '';
    if ($folder_id) {
        $encrypted_url = '?q=' . encryptQueryString('folder_id=' . $folder_id);
    }
    echo "<script>window.location.href='register-step2.html" . $encrypted_url . "';</script>";
    exit;
}
$data = '';
if (isset($_SESSION['data1']) && !empty($_SESSION['data1'])) {
    $data = $_SESSION['data1'];
}

$user_id = 1; //$_SESSION['aib']['user_data']['user_id'];
?>
<style>
    mark{background-color: #fbd42f !important;}
</style>
<div class="header_img">
    <div class="bannerImageRegSociety"></div>
</div>
<div class="clearfix"></div>
<form name="registrationForm" id="registrationForm" method="post" action="">
    <input type="hidden" name="user_type" value="A">
    <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
        <div class="container">
            <div class="row marginTop20 bgNone"><h3>Historical Society Information</h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Historical Society Name<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="society_name" id="society_name1" value="<?php
                    if (!empty($data)) {
                        echo $data['society_name'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>

            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>State<span>*</span>:
					
					</label>
                </div>
                <div class="col-md-3" >
                    <select class="form-control" name="society_state" id="society_state"> </select>
                </div> 
            </div>

            <div class="row bgNone"><h3>Contact Information</h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>First Name<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="firstName" id="firstName" value="<?php
                    if (!empty($data)) {
                        echo $data['firstName'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Last Name<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="lastName" id="lastName" value="<?php
                    if (!empty($data)) {
                        echo $data['lastName'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Title<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="title" id="title" value="<?php
                    if (!empty($data)) {
                        echo $data['title'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Username<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="register_username" id="register_username1" value="<?php
                    if (!empty($data)) {
                        echo $data['register_username'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Email<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="register_emailId" id="register_emailId" value="<?php
                    if (!empty($data)) {
                        echo $data['register_emailId'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Phone Number<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" maxlength="14" class="form-control" name="phoneNumber" id="phoneNumber" value="<?php
                    if (!empty($data)) {
                        echo $data['phoneNumber'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Preferred Time Zone<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <select  name="preferred_time_zone" id="preferred_time_zone" class="form-control">
                        <option value="">---Select---</option>
                        <option value="UTC -11:00"  <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -11:00') {
                            echo 'selected';
                        }
                        ?>  > UTC-11: Samoa Standard Time</option>
                        <option value="UTC -10:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -10:00') {
                            echo 'selected';
                        }
                        ?> >UTC-10: Hawaii-Aleutian Standard Time (HST)</option>
                        <option value="UTC -09:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -09:00') {
                            echo 'selected';
                        }
                        ?> >UTC-9: Alaska Standard Time (AKST)</option>
                        <option value="UTC -08:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -08:00') {
                            echo 'selected';
                        }
                        ?> >UTC-8: Pacific Standard Time (PST)</option>
                        <option value="UTC -07:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -07:00') {
                            echo 'selected';
                        }
                        ?> >UTC-7: Mountain Standard Time (MST)</option>
                        <option value="UTC -06:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -06:00') {
                            echo 'selected';
                        }
                        ?> >UTC-6: Central Standard Time (CST)</option>
                        <option value="UTC -05:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -05:00') {
                            echo 'selected';
                        }
                        ?> >UTC-5: Eastern Standard Time (EST)</option>
                        <option value="UTC -04:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC -04:00') {
                            echo 'selected';
                        }
                        ?> >UTC-4: Atlantic Standard Time (AST)</option>
                        <option value="UTC +10:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC +10:00') {
                            echo 'selected';
                        }
                        ?> >UTC+10: Chamorro Standard Time</option>
                        <option value="UTC +12:00" <?php
                        if (!empty($data['preferred_time_zone']) && $data['preferred_time_zone'] == 'UTC +12:00') {
                            echo 'selected';
                        }
                        ?> >UTC+12: Wake Island Time Zone</option>
                    </select>
                </div> 
            </div>
            <!--<div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Fax Number:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="faxNumber" id="faxNumber" value="" placeholder="Enter text">
                </div> 
            </div> -->

            <div class="row marginTop20 bgNone"> 
                <div class="col-md-3"></div>          
                <div class="col-md-2">
                    <input type="hidden" name="archive_id" id="archive_id" value="<?php echo $folder_id; ?>" />
                    <input type="button" class="form-control btn-success" name="regSubmit" id="regSubmit" value="Next" >
                </div> 
            </div>

            <div class="clearfix marginTop20"></div>
            <div class="row">
                <div class="col-md-12" id="search_result_render_space" style="display:none;">Loading....</div>
            </div>
        </div>
    </div>
</form>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    var archive_id = '<?php echo $folder_id; ?>';
    var user_id = '<?php echo $user_id; ?>';
    $(document).ready(function () {
        get_state();
        var phones = [{"mask": "(###) ###-####"}, {"mask": "(###) ###-##############"}];
        $('#phoneNumber').inputmask({
            mask: phones,
            greedy: false,
            definitions: {'#': {validator: "[0-9]", cardinality: 1}}
        });
        $("#registrationForm").validate({
            rules: {
                society_name: "required",
                firstName: "required",
                lastName: "required",
                title: "required",
                society_state: {
                    required: true,
                    remote: {
                        url: "services.php",
                        type: "POST",
                        data: {
                            mode: function () {
                                return "check_duplicate_item";
                            },
                            society_name: function () {
                                return $("#society_name1").val();
                            }, 
                            archive_id: function () {
                                return $("#archive_id").val();
                            }
                        }
                    }
                },
                register_username: {
                    required: true,
                    remote: {
                        url: "services.php",
                        type: "POST",
                        data: {
                            mode: function () {
                                return "check_user_exist";
                            },
                            register_username1: function () {
                                return $("#register_username1").val();
                            }
                        }
                    }
                },
                register_emailId: {
                    required: {
                        depends: function () {
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                phoneNumber: {
                    required: true,
                    minlength: 14,
                    maxlength: 14
                },
                faxNumber: {
                    minlength: 10,
                    maxlength: 12,
                    number: true
                },
                preferred_time_zone: {
                    required: true
                }
            },
            messages: {
                society_name: "Please enter society name",
                firstName: "First Name is required",
                lastName: "Last Name is required",
                title: "Title is required",
                society_state: {
                    required: "Society state is required",
                    remote: jQuery.validator.format("Historical Society Name is already taken for the state {0}.")
                },
                register_username: {
                    required: "Username is required",
                    remote: jQuery.validator.format("Username already exist.")
                },
                register_emailId: {
                    required: "Email is required",
                    email: "Please enter valid email"
                },
                phoneNumber: {
                    required: "Phone number is required",
                    number: "Please enter valid Phone Number"
                },
                faxNumber: {
                    number: "Please enter valid Fax Number"
                },
                preferred_time_zone: "Please select preferred time zone"
            }
        });

        $("#regSubmit").on("click", function (e) {
            if ($("#registrationForm").valid() == true) {
                $("#registrationForm").submit();
            } else {
                $('.error:visible:first').focus();
                scrollTop: $('.error:visible:first').offset().top;
            }
        });
    });
    function get_state() {
        $('.loading-div').show();
        var parent_id = '<?php echo STATE_PARENT_ID; ?>';
        var society_state = '<?php echo $data['society_state']; ?>';
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_state_country', parent_id: parent_id},
            success: function (response) {
                var record = JSON.parse(response);
                var i;
                var state = "";
                state += "<option value='' >---Select---</option>";
                for (i = 0; i < record.length; i++) {
                    var data_value = '';
                    if (society_state == record[i]) {
                        data_value = 'selected';
                    }
                    state += "<option value='" + record[i] + "'  " + data_value + " >" + record[i] + "</option>";
                }
                $("#society_state").html(state);
                $('.loading-div').hide();

            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 621)');
                $('.loading-div').hide();
            }
        });
    }

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
                        $('#society_name1').val(record.item_title);
                    }
                    if (record.prop_details.contactEmailAddress) {
                        //$('#register_emailId').val(record.user_properties.email);
                        $('#register_emailId').val(record.prop_details.contactEmailAddress);
                    }
                    if (record.prop_details.phoneNumber) {
                        $('#phoneNumber').val(record.prop_details.phoneNumber);
                    }
//                    if (record.prop_details.faxNumber) {
//                        $('#faxNumber').val(record.prop_details.faxNumber);
//                    }
                    if (record.prop_details.redactionsEmailAddress) {
                        $('#redactionsEmailAddress').val(record.prop_details.redactionsEmailAddress);
                    }
                    if (record.prop_details.reprintEmailAddress) {
                        $('#reprintEmailAddress').val(record.prop_details.reprintEmailAddress);
                    }
                    if (record.prop_details.contactEmailAddress) {
                        $('#contactEmailAddress').val(record.prop_details.contactEmailAddress);
                    }
                    if (record.prop_details.websiteURL) {
                        $('#websiteURL').val(record.prop_details.websiteURL);
                    }
                    if (record.prop_details.physicalAddressLine1) {
                        $('#physicalAddressLine1').val(record.prop_details.physicalAddressLine1);
                    }
                    if (record.prop_details.physicalAddressLine2) {
                        $('#physicalAddressLine2').val(record.prop_details.physicalAddressLine2);
                    }
                    if (record.prop_details.physicalCity) {
                        $('#physicalCity').val(record.prop_details.physicalCity);
                    }

                    if (record.prop_details.physicalState != '') {
                        $('#physicalState').val(record.prop_details.physicalState);
                    }
                    if (record.prop_details.physicalZip) {
                        $('#physicalZip').val(record.prop_details.physicalZip);
                    }

                    if (record.prop_details.mailingAddressLine1) {
                        $('#mailingAddressLine1').val(record.prop_details.mailingAddressLine1);
                    }
                    if (record.prop_details.mailingAddressLine2) {
                        $('#mailingAddressLine2').val(record.prop_details.mailingAddressLine2);
                    }
                    if (record.prop_details.mailingCity) {
                        $('#mailingCity').val(record.prop_details.mailingCity);
                    }
                    if (record.prop_details.mailingState != '') {
                        $('#mailingState').val(record.prop_details.mailingState);
                    }
                    if (record.prop_details.mailingZip) {
                        $('#mailingZip').val(record.prop_details.mailingZip);
                    }

                    if (record.prop_details.sateTaxIDNumber) {
                        $('#sateTaxIDNumber').val(record.prop_details.sateTaxIDNumber);
                    }
                    if (record.prop_details.federalTaxIDNumber) {
                        $('#federalTaxIDNumber').val(record.prop_details.federalTaxIDNumber);
                    }
                    if (record.prop_details.entityOrganization) {
                        $('#entityOrganization').val(record.prop_details.entityOrganization);
                    }
                    if (record.prop_details.entityOrganizationOther) {
                        $('#eoOther').show();
                        $('#entityOrganizationOther').val(record.prop_details.entityOrganizationOther);
                    }
                    if (record.prop_details.CEO == "true" || record.prop_details.CEO_firstName != '') {
                        $('#check_CEO').show();
                        $('#CEO').prop("checked", "true");

                        if (record.prop_details.CEO_firstName) {
                            $('#CEO_firstName').val(record.prop_details.CEO_firstName);
                        }
                        if (record.prop_details.CEO_lastName) {
                            $('#CEO_lastName').val(record.prop_details.CEO_lastName);
                        }
                        if (record.prop_details.CEO_email) {
                            $('#CEO_email').val(record.prop_details.CEO_email);
                        }
                    }

                    if (record.prop_details.executiveDirector == "true") {
                        $('#check_executiveDirector').show();
                        $('#executiveDirector').prop("checked", "true");

                        if (record.prop_details.executiveDirector_firstName) {
                            $('#executiveDirector_firstName').val(record.prop_details.executiveDirector_firstName);
                        }
                        if (record.prop_details.executiveDirector_lastName) {
                            $('#executiveDirector_lastName').val(record.prop_details.executiveDirector_lastName);
                        }
                        if (record.prop_details.executiveDirector_email) {
                            $('#executiveDirector_email').val(record.prop_details.executiveDirector_email);
                        }
                    }
                    if (record.prop_details.precident == "true") {
                        $('#check_precident').show();
                        $('#precident').prop("checked", "true");

                        if (record.prop_details.precident_firstName) {
                            $('#precident_firstName').val(record.prop_details.precident_firstName);
                        }
                        if (record.prop_details.precident_lastName) {
                            $('#precident_lastName').val(record.prop_details.precident_lastName);
                        }
                        if (record.prop_details.precident_email) {
                            $('#precident_email').val(record.prop_details.precident_email);
                        }
                    }
                    if (record.prop_details.otherExecutive == "true") {
                        $('#check_otherExecutive').show();
                        $('#otherExecutive').prop("checked", "true");

                        if (record.prop_details.otherExecutive_firstName) {
                            $('#otherExecutive_firstName').val(record.prop_details.otherExecutive_firstName);
                        }
                        if (record.prop_details.otherExecutive_lastName) {
                            $('#otherExecutive_lastName').val(record.prop_details.otherExecutive_lastName);
                        }
                        if (record.prop_details.otherExecutive_email) {
                            $('#otherExecutive_email').val(record.prop_details.otherExecutive_email);
                        }
                    }
                    if (record.prop_details.committees == "true") {
                        $('#committees').prop("checked", "true");
                    }
                    if (record.prop_details.boardOfDirectors == "true") {
                        $('#boardOfDirectors').prop("checked", "true");
                    }
                    if (record.prop_details.sameAsPhysicalAddress == "true") {
                        $('#sameAsPhysicalAddress').prop("checked", "true");
                    }
                    if (record.prop_details.archive_user_id) {
                        $('#archive_user_id').val(record.prop_details.archive_user_id);
                    }
                    if (record.item_id) {
                        $('#item_id').val(record.item_id);
                    }
                    if (record.prop_details.society_state != '') {
                        setTimeout(function () {
                            $('#society_state').val(record.prop_details.society_state);
                        }, 1000);
                    }
                    if (record.prop_details.preferred_time_zone != '') {
                        $('#preferred_time_zone').val(record.prop_details.preferred_time_zone);
                    }
                    if (record.prop_details.archive_user_id) {
                        $.ajax({
                            url: "admin/services_admin_api.php",
                            type: "post",
                            data: {mode: 'get_user_by_id', user_id: record.prop_details.archive_user_id},
                            success: function (response) {
                                var response = JSON.parse(response);
                                /*$('#register_username1').val(response.user_login);
                                if (response.user_login) {
                                    $('#register_username1').val(response.user_login);
                                }*/
                                if (response.user_title) {
                                    var nameArray = response.user_title.split(" ");
                                    if (nameArray[0]) {
                                        $('#title').val(nameArray[0]);
                                    }
                                    if (nameArray[1]) {
                                        $('#firstName').val(nameArray[1]);
                                    }
                                    if (nameArray[2]) {
                                        $('#lastName').val(nameArray[2]);
                                    }
                                }
                            }
                        });
                    }
                    if (record.user_properties.occasional_update == 'Y') {
                        $('#occasional_update').attr('checked', true);
                    }
                    if (record.user_properties.term_service == 'Y') {
                        $('#term_service').attr('checked', true);
                    }

                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 622)');
                }
            });
        }
    }

    //Call Initialy to load data
<?php if (empty($_SESSION['data1'])) { ?>
        if (archive_id) {
            getItemPropDetails(archive_id, user_id);
        }
<?php } ?>
</script>