<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$loggedInUserId = $_SESSION['aib']['user_data']['user_id'];

$timeZone = array(
    "UTC -11:00" => "UTC-11: Samoa Standard Time",
    "UTC -10:00" => "UTC-10: Hawaii-Aleutian Standard Time (HST)",
    "UTC -09:00" => "UTC-9: Alaska Standard Time (AKST)",
    "UTC -08:00" => "UTC-8: Pacific Standard Time (PST)",
    "UTC -07:00" => "UTC-7: Mountain Standard Time (MST)",
    "UTC -06:00" => "UTC-6: Central Standard Time (CST",
    "UTC -05:00" => "UTC-5: Eastern Standard Time (EST)",
    "UTC -04:00" => "UTC-4: Atlantic Standard Time (AST)",
    "UTC +10:00" => "UTC+10: Chamorro Standard Time",
    "UTC +1@:00" => "UTC+12: Wake Island Time Zone"
);
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


//error_reporting(E_ALL & ~E_NOTICE);
?>
<style>
    mark{background-color: #fbd42f !important;}
</style>

<div class="header_img">
    <div class="bannerImage"></div>
</div>
<div class="clearfix"></div>

<div class="content-wrapper">
    <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
        <div class="container spanStyle">
            <div class="row marginTop20 bgNone"><h3><span class="width78">Historical Society Information</span> <span class="pull-right" id="reg_edit"><a class="btn btn-success marginTop4" href="javascript:void(0);">Edit</a></span></h3></h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Historical Society Name<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="society_name" id="society_name1" value="<?php
                    if (!empty($data)) {
                        echo $data['society_name'];
                    }
                    ?>" readonly />
                </div> 
            </div>

            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>State<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="society_state" id="society_state" value="<?php
                    if (!empty($data)) {
                        echo $data['society_state'];
                    }
                    ?>"  readonly />
                </div> 
            </div>

            <div class="row bgNone"><h3>Contact Information</h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>First Name<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="firstName" id="firstName" value="<?php
                    if (!empty($data)) {
                        echo $data['firstName'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Last Name<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="lastName" id="lastName" value="<?php
                    if (!empty($data)) {
                        echo $data['lastName'];
                    }
                    ?>" readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Title<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="title" id="title" value="<?php
                    if (!empty($data)) {
                        echo $data['title'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Username<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="register_username" id="register_username1" value="<?php
                    if (!empty($data)) {
                        echo $data['register_username'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Email<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" class="form-control" name="register_emailId" id="register_emailId" value="<?php
                    if (!empty($data)) {
                        echo $data['register_emailId'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Phone Number<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="text" maxlength="14" class="form-control" name="phoneNumber" id="phoneNumber" value="<?php
                    if (!empty($data)) {
                        echo $data['phoneNumber'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Preferred Time Zone<span>*</span>:</label>
                </div>
                <div class="col-md-5" >
                    <input type="hidden" maxlength="14" class="form-control" name="preferred_time_zone" id="preferred_time_zone" value="<?php
                    if (!empty($data)) {
                        echo $data['preferred_time_zone'];
                    }
                    ?>" readonly />
                    <span class="spanStyle">
                        <?php
                        if (!empty($data)) {
                            echo $timeZone[$data['preferred_time_zone']];
                        }
                        ?>
                    </span>
                </div> 
            </div>

            <div class="clearfix marginTop20"></div>
            <div class="row">
                <div class="col-md-12" id="search_result_render_space" style="display:none;">Loading....</div>
            </div>
        </div>
    </div>
    <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
        <div class="container"> 
            <div class="row marginTop20 bgNone"><h3>Location Information </h3></h3></div>
            <div class="row bgNone hedingSub">
                <div class="col-md-6 text-center"><h3 class="bgNone">Physical Address</h3> </div> 
                <div class="col-md-6 text-center"><h3 class="bgNone marginBottom10">Mailing Address</h3></div>

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
                    ?>" readonly />
                </div> 

                <div class="col-md-3" >
                    <label>Address Line 1<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingAddressLine1" id="mailingAddressLine1" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingAddressLine1'];
                    }
                    ?>"  readonly />
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
                    ?>"  readonly />
                </div> 

                <div class="col-md-3" >
                    <label>Address Line 2:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingAddressLine2" id="mailingAddressLine2" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingAddressLine2'];
                    }
                    ?>"  readonly />
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
                    ?>"  readonly />
                </div> 

                <div class="col-md-3" >
                    <label>City<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingCity" id="mailingCity" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingCity'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>State<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="physical form-control" name="physicalState" id="physicalState" value="<?php
                    if (!empty($data)) {
                        echo $data['physicalState'];
                    }
                    ?>"  readonly />
                </div> 

                <div class="col-md-3" >
                    <label>State<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingState" id="mailingState" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingState'];
                    }
                    ?>"  readonly />
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
                    ?>"  readonly />
                </div> 

                <div class="col-md-3" >
                    <label>Zip<span>*</span>:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="mailing form-control" name="mailingZip" id="mailingZip" value="<?php
                    if (!empty($data)) {
                        echo $data['mailingZip'];
                    }
                    ?>" readonly />
                </div> 
            </div>

            <div class="row marginTop20 bgNone"><h3>Tax Information</h3></div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Federal Tax ID Number:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="federalTaxIDNumber" id="federalTaxIDNumber" value="<?php
                    if (!empty($data)) {
                        echo $data['federalTaxIDNumber'];
                    }
                    ?>"  readonly />
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
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 padd5">
                <div class="col-md-3" >
                    <label>Entity Organization:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="entityOrganization" id="entityOrganization" value="<?php
                    if (!empty($data)) {
                        echo $data['entityOrganization'];
                    }
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20" style="display:none" id="eoOther">
                <div class="col-md-3" >
                    <label>Other:</label>
                </div>
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="entityOrganization" id="entityOrganizationOther" value="<?php
                    if (!empty($data)) {
                        echo $data['entityOrganization'];
                    }
                    ?>"  readonly />
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
                    ?>"  readonly />
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
                    ?>" readonly />
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
                    ?>"  readonly />
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
                    ?>"  readonly />
                </div> 
            </div>
            <div class="row marginTop20 bgNone"><h3>Organization Structure</h3></div>
            <div class="row marginTop20 padd5 paddLeft15"><strong>Select all that apply</strong></div>
            <?php foreach ($orgStructureArray as $osKey => $osValue) { ?>
                <div class="row marginTop20 padd5">
                    <div class="col-md-12" >
                        <input type="checkbox" class="orgStructure" name="<?php echo $osKey ?>" id="<?php echo $osKey ?>" value="<?php echo $data[$osKey] ?>" disabled="" <?php
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
                            <input type="text" class="form-control" name="<?php echo $osKey . "_firstName" ?>" id="<?php echo $osKey . "_firstName" ?>" value="<?php echo $data[$osKey . "_firstName"] ?>"  readonly />
                        </div>       
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="<?php echo $osKey . "_lastName" ?>" id="<?php echo $osKey . "_lastName " ?>" value="<?php echo $data[$osKey . "_lastName"] ?>" readonly />
                        </div>         
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="<?php echo $osKey . "_email" ?>" id="<?php echo $osKey . "_email" ?>" value="<?php echo $data[$osKey . "_email"] ?>" readonly />
                        </div>         
                    </div>
                <?php } ?>
            <?php } ?>
            <div class="row marginTop20 bgNone"> 
                <div class="col-md-9">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" value="Y"name="occasional_update" id="occasional_update" value="" />
                            Yes, please send me occasional updates,training materials, or marketing from ArchiveInBox
                        </label>
                    </div>
                </div> 
            </div>
            <div class="row marginTop20 bgNone"> 
                <div class="col-md-9">
                    <div class="checkbox" >
                        <label>
                            <input type="checkbox" value="Y" name="term_service" id="term_service" >
                            I agree to the <a href="javascript:service_term_popup();" style="color:#72afd2;"><strong>Terms of service</strong></a>
                        </label>
                    </div>
                </div> 
            </div>
            <div class="row bgNone termsCondition" style="display:none;">
                <div class="col-md-6">
                    <div class="" id="term_of_services">
                        <div class="modal-dialog widthFullModal marginTop20">
                            <div class="modal-content">
                                <div class="modal-header form_header">
                                    <h4 class="list_title"><span id="popup_heading">Terms of service </span>
                                        <button type="button" class="close canPopUp closepopup" data-dismiss="modal">&times;</button>
                                    </h4>
                                    <button type="button" onclick="PrintElem('get_term_cond_data');" class="btn btn-primary borderRadiusNone pull-right marginTop10">Print</button>
                                </div>
                                <div class="modal-body" id="movefolderformdiv">
                                    <div class="col-md-12 overflowTerms">
                                        <p id="get_term_cond_data"> </p>
                                    </div>
                                    <div  class="form-horizontal">
                                        <div class="form-group">
                                            <label class="col-xs-3 control-label"></label>
                                            <div class="col-xs-7 marginTop20">
                                                <button type="button" class="btn btn-success  borderRadiusNone" id="agreeTermButton">Yes I agree</button>
                                                <button type="button" class="btn btn-success  borderRadiusNone closepopup" id="notAgreeTermButton">No I do not agree</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix marginTop20"></div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    var archive_id = '<?php echo $_REQUEST['folder_id']; ?>';
    var user_id = '1';
    function getItemPropDetails(archive_id, user_id) {
        if (archive_id != '') {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_archive_prop_details', archive_id: archive_id, user_id: user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.item_title) {
                        $('#society_name1').val(record.item_title);
                    }
                    if (record.user_properties.email) {
                        $('#register_emailId').val(record.user_properties.email);
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
                        $('#society_state').val(record.prop_details.society_state);
                    }
                    if (record.prop_details.archive_user_id) {
                        $.ajax({
                            url: "services_admin_api.php",
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
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 665)');
                }
            });
        }
    }

    //Call Initialy to load data
    if (archive_id) {
        getItemPropDetails(archive_id, user_id);
    }
</script>