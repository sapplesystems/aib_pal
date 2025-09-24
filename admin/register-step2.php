<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';

$entityOrgArray = array(
    "501 Non-profit" => "501 Non-profit",
    "Corporation" => "Corporation",
    "LLC" => "LLC",
    "coOp" => "coOp",
    "Sole Properietor" => "Sole Properietor",
    "Other" => "Other",
);
$orgStructureArray = array(
    "boardOfDirectors" => array("title" => "Board of Directors", 'field' => false),
    "CEO" => array("title" => "CEO", 'field' => true),
    "executiveDirector" => array("title" => "Executive Director", 'field' => true),
    "precident" => array("title" => "president", 'field' => true),
    "otherExecutive" => array("title" => "Other Executive", 'field' => true),
    "committees" => array("title" => "Committees", 'field' => false)
);
//$data=$_SESSION['data1'];
$folder_id = ($_REQUEST['folder_id']) ? $_REQUEST['folder_id'] : '';
if (isset($_POST) && !empty($_POST)) {
    unset($_SESSION['data2']);
    $_SESSION['data2'] = $_POST;
    $encrypted_url = '';
    if ($folder_id) {
        $encrypted_url = '?q=' . encryptQueryString('folder_id=' . $folder_id);
    }
    echo "<script>window.location.href='register-step3.html" . $encrypted_url . "';</script>";
    exit;
}
$data = '';
if (isset($_SESSION['data2']) && !empty($_SESSION['data2'])) {
    $data = $_SESSION['data2'];
}
?>
<style>
    mark{background-color: #fbd42f !important;}
</style>

<div class="header_img">
    <div class="bannerImageRegSociety"></div>
</div>
<div class="clearfix"></div>
<form name="registrationOtherForm" id="registrationOtherForm" method="post" action="">
    <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
        <div class="container"> 
            <div class="row marginTop20" id="display_message" style="display:none">
                <div class="col-md-3" style="color:green">
                    Your profile has been created successfully.Please wait for administrator response.
                </div>
            </div>


            <div class="row marginTop20 bgNone"><h3>Location Information</h3></div>


            <div class="row bgNone hedingSub">
                <div class="col-md-6 text-center"><h3 class="bgNone">Physical Address</h3> </div> 
                <div class="col-md-6 text-center"><h3 class="bgNone marginBottom10">Mailing Address</h3> <input type="checkbox" name="sameAsPhysicalAddress" id="sameAsPhysicalAddress" value="<?php
                    if (!empty($data)) {
                        if ($data['sameAsPhysicalAddress'] == 'true') {
                            //echo 'checked';
                        };
                    } else {
                        //echo'true';
                    }
                    ?>"> <?php echo $osValue['title'] ?> Same As physical Address</div>

            </div>

            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Address Line 1<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="physical form-control" name="physicalAddressLine1" id="physicalAddressLine1" value="<?php
                    if (!empty($data)) {
                        echo $data['physicalAddressLine1'];
                    }
                    ?>" placeholder="Enter text">
                </div> 

                <div class="col-md-3" >
                    <label>Address Line 1<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingAddressLine1" id="mailingAddressLine1" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingAddressLine1'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>


            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Address Line 2:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="physical form-control" name="physicalAddressLine2" id="physicalAddressLine2" value="<?php
                    if (!empty($data)) {
                        echo $data['physicalAddressLine2'];
                    }
                    ?>" placeholder="Enter text">
                </div> 

                <div class="col-md-3" >
                    <label>Address Line 2:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingAddressLine2" id="mailingAddressLine2" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingAddressLine2'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>

            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>City<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="physical form-control" name="physicalCity" id="physicalCity" value="<?php
                    if (!empty($data)) {
                        echo $data['physicalCity'];
                    }
                    ?>" placeholder="Enter text">
                </div> 

                <div class="col-md-3" >
                    <label>City<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingCity" id="mailingCity" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingCity'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>State<span>*</span>:</label>
                </div>
                <div class="col-md-3" >

                    <select  class="physical form-control physical_state" name="physicalState" id="physicalState"></select>
    <!--                <input type="text" class="physical form-control" name="physicalState" id="physicalState" value="<?php
                    if (!empty($data)) {
                        echo $data['physicalState'];
                    }
                    ?>" placeholder="Enter text">-->
                </div> 

                <div class="col-md-3" >
                    <label>State<span>*</span>:</label>
                </div>
                <div class="col-md-3" >

                    <select  class="mailing form-control mailing_state" name="mailingState" id="mailingState"   value="<?php
                    if (!empty($data)) {
                        echo $data['mailingState'];
                    }
                    ?>"> </select>
                    <!--<input type="text" class="mailing form-control" name="mailingState" id="mailingState" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingState'];
                    }
                    ?>" placeholder="Enter text">-->
                </div> 
            </div>

            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Zip<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="physical form-control" name="physicalZip" id="physicalZip" value="<?php
                    if (!empty($data)) {
                        echo $data['physicalZip'];
                    }
                    ?>" placeholder="Enter text">
                </div> 

                <div class="col-md-3" >
                    <label>Zip<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingZip" id="mailingZip" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingZip'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>

            <div class="row marginTop20 bgNone"><h3>Tax Information</h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Federal TIN<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="federalTaxIDNumber" id="federalTaxIDNumber" value="<?php
                    if (!empty($data)) {
                        echo $data['federalTaxIDNumber'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>State Tax ID Number:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="sateTaxIDNumber" id="sateTaxIDNumber" value="<?php
                    if (!empty($data)) {
                        echo $data['sateTaxIDNumber'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Entity Organization:</label>
                </div>
                <div class="col-md-3" >
                    <select name="entityOrganization" class="form-control" id="entityOrganization" onChange="if (this.value == 'Other') {
                                $('#eoOther').show();
                            } else {
                                $('#eoOther').hide();
                            }">
                                <?php foreach ($entityOrgArray as $eoaKey => $eoaValue) { ?>
                            <option value="<?php echo $eoaKey; ?>"> <?php echo $eoaValue; ?></option>
                        <?php } ?>
                    </select>
                </div> 
            </div>
            <div class="row marginTop20" style="display:none" id="eoOther">
                <div class="col-md-3" >
                    <label>Other:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="entityOrganizationOther" id="entityOrganizationOther" value="<?php
                    if (!empty($data)) {
                        echo $data['entityOrganizationOther'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>

            <div class="row marginTop20 bgNone"><h3>Other Information</h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Redactions Email Address:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="redactionsEmailAddress" id="redactionsEmailAddress" value="<?php
                    if (!empty($data)) {
                        echo $data['redactionsEmailAddress'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>

            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Reprint Email Address:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="reprintEmailAddress" id="reprintEmailAddress" value="<?php
                    if (!empty($data)) {
                        echo $data['reprintEmailAddress'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Contact Email Address:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="contactEmailAddress" id="contactEmailAddress" value="<?php
                    if (!empty($data)) {
                        echo $data['contactEmailAddress'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Website URL:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="websiteURL" id="websiteURL" value="<?php
                    if (!empty($data)) {
                        echo $data['websiteURL'];
                    }
                    ?>" placeholder="Enter text">
                </div> 
            </div>


            <div class="row marginTop20 bgNone"><h3>Organization Structure</h3></div>
            <div class="row marginTop20 padd5 paddLeft15"><strong>Select all that apply</strong></div>
            <?php foreach ($orgStructureArray as $osKey => $osValue) { ?>
                <div class="row marginTop20 padd5">
                    <div class="col-md-12" >
                        <input type="checkbox" class="orgStructure" name="<?php echo $osKey ?>" id="<?php echo $osKey ?>" value="<?php
                        if (!empty($data)) {
                            echo $data[$osKey];
                        } else {
                            echo'true';
                        }
                        ?>"  <?php
                               if (isset($data[$osKey])) {
                                   echo 'checked';
                               }
                               ?>> <?php echo $osValue['title'] ?>
                    </div>            
                </div>
                <?php if ($osValue['field'] == true) { ?>
                    <div class="row padd5" style="display: <?php
                    if (isset($data[$osKey])) {
                        echo 'block';
                    } else {
                        echo 'none';
                    }
                    ?>;" id="<?php echo "check_" . $osKey; ?>" >
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="<?php echo $osKey . "_firstName" ?>" id="<?php echo $osKey . "_firstName" ?>" value="<?php
                            if (!empty($data)) {
                                echo $data[$osKey . "_firstName"];
                            }
                            ?>" placeholder="Enter First Name">
                        </div>       
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="<?php echo $osKey . "_lastName" ?>" id="<?php echo $osKey . "_lastName" ?>" value="<?php
                            if (!empty($data)) {
                                echo $data[$osKey . "_lastName"];
                            }
                            ?>" placeholder="Enter Last Name">
                        </div>         
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="<?php echo $osKey . "_email" ?>" id="<?php echo $osKey . "_email" ?>" value="<?php
                            if (!empty($data)) {
                                echo $data[$osKey . "_email"];
                            }
                            ?>" placeholder="Enter Email Address">
                        </div>         
                    </div>
                <?php } ?>
            <?php } ?>
            <div class="row marginTop20 bgNone">  
                <div class="col-md-5"></div>         
                <div class="col-md-2" >
                    <input type="hidden" name="archive_id" id="archive_id" value="<?php echo $folder_id; ?>" />
                    <input type="button" class="form-control btn-success" name="regOtherSubmit" id="regOtherSubmit" value="Next" >
                </div> 
            </div>
            <div class="clearfix marginTop20"></div>
        </div>
    </div>
</form>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH . 'jquery.mark.min.js'; ?>"></script>
<script type="text/javascript">
                        var archive_id = '<?php echo $folder_id; ?>';
                        var user_id = '<?php echo $user_id; ?>';
                        $(document).ready(function () {
                            get_state();
                            $(".orgStructure").on("click", function () {
                                var osId = $(this).attr("id");
                                if ($("#" + osId).is(":checked") == true) {
                                    $("#check_" + osId).show();
                                } else {
                                    $("#check_" + osId).hide();
                                }
                            });

                            $("#sameAsPhysicalAddress").on("change", function () {
                                if ($(this).is(":checked") == true) {
                                    $("#mailingAddressLine1").val($("#physicalAddressLine1").val());
                                    $("#mailingAddressLine2").val($("#physicalAddressLine2").val());
                                    $("#mailingCity").val($("#physicalCity").val());
                                    $("#mailingState").val($("#physicalState").val());
                                    $("#mailingZip").val($("#physicalZip").val());
                                } else {
                                    $(".mailing").each(function () {
                                        $(this).val("");
                                    });
                                }
                            });


                            $("#registrationOtherForm").validate({
                                rules: {
                                    physicalAddressLine1: "required",
                                    physicalCity: "required",
                                    physicalState: "required",
                                    physicalZip: "required",
                                    mailingAddressLine1: "required",
                                    mailingCity: "required",
                                    mailingState: "required",
                                    mailingZip: "required",
                                    federalTaxIDNumber: "required"
                                },
                                messages: {
                                    physicalAddressLine1: "Physical Address line1 is required",
                                    physicalCity: "Physical city is required",
                                    physicalState: "Physical state is required",
                                    physicalZip: "Physical zip is required",
                                    mailingAddressLine1: "Mailing Address line1 is required",
                                    mailingCity: "Mailing city is required",
                                    mailingState: "Mailing state is required",
                                    mailingZip: "Mailing zip is required",
                                    federalTaxIDNumber: "Fedral TIN is required"
                                }
                            });

                            $("#regOtherSubmit").on("click", function () {
                                if ($("#registrationOtherForm").valid() == true) {
                                    $("#registrationOtherForm").submit();
                                } else {
                                    $('.error:visible:first').focus();
                                    scrollTop: $('.error:visible:first').offset().top;
                                }
                            });

                        });
                        function get_state() {
                            $('.loading-div').show();
                            var parent_id = '<?php echo STATE_PARENT_ID; ?>';
                            var physical_society_state = '<?php echo $data['physicalState']; ?>';
                            var mailing_society_state = '<?php echo $data['mailingState']; ?>';
                            $.ajax({
                                url: "services.php",
                                type: "post",
                                data: {mode: 'get_state_country', parent_id: parent_id},
                                success: function (response) {
                                    var record = JSON.parse(response);
                                    var i;
                                    var physical_state = "";
                                    var mailing_state = "";
                                    physical_state += "<option value='' >---Select---</option>";
                                    mailing_state += "<option value='' >---Select---</option>";
                                    for (i = 0; i < record.length; i++) {
                                        var physical_state_data = '';
                                        var mailing_state_data = '';
                                        if (physical_society_state == record[i]) {
                                            physical_state_data = 'selected';
                                        }
                                        physical_state += "<option value='" + record[i] + "'  " + physical_state_data + " >" + record[i] + "</option>";
                                        if (mailing_society_state == record[i]) {
                                            mailing_state_data = 'selected';
                                        }
                                        mailing_state += "<option value='" + record[i] + "'  " + mailing_state_data + " >" + record[i] + "</option>";

                                    }
                                    $(".physical_state").html(physical_state);
                                    $(".mailing_state").html(mailing_state);

                                    $('.loading-div').hide();

                                },
                                error: function () {
                                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 616)');
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
                        setTimeout(function(){
                            $('#physicalState').val(record.prop_details.physicalState);
                        },1000);
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
                        setTimeout(function(){
                            $('#mailingState').val(record.prop_details.mailingState);
                        },1000);
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
                        //$('#sameAsPhysicalAddress').prop("checked", "true");
                    }
                    if (record.prop_details.archive_user_id) {
                        $('#archive_user_id').val(record.prop_details.archive_user_id);
                    }
                    if (record.item_id) {
                        $('#item_id').val(record.item_id);
                    }
                    if (record.prop_details.society_state != '') {
                        $('#society_state').val(record.prop_details.society_state);
                    }
                    if (record.prop_details.archive_user_id) {
                        $.ajax({
                            url: "admin/services_admin_api.php",
                            type: "post",
                            data: {mode: 'get_user_by_id', user_id: record.prop_details.archive_user_id},
                            success: function (response) {
                                var response = JSON.parse(response);
                                $('#register_username1').val(response.user_login);
                                if (response.user_login) {
                                    $('#register_username1').val(response.user_login);
                                }
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
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 617)');
                }
            });
        }
    }

    //Call Initialy to load data
<?php if (empty($_SESSION['data2'])) { ?>
    if (archive_id) {
        getItemPropDetails(archive_id, user_id);
    }
<?php } ?>
</script>