<?php
session_start();
ini_set('display_errors', 0);
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
//$archive_id = $_REQUEST['archive_id'];
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
$user_id = '';
if (isset($_SESSION['aib']['user_data'])) {
    $user_id = $_SESSION['aib']['user_data']['user_id'];
    $user_email = '';
    if (!empty($_SESSION['aib']['user_data']['user_prop'])) {
        $user_email = $_SESSION['aib']['user_data']['user_prop']['email'];
    }
}
?>
<style>
    #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}
    .modal-dialog{width:100% !important;} #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}
</style>
<div class="content-wrapper">
    <form name="register_society_frm" id="register_society_frm" action="" method="post" enctype="multipart/form-data">
        <!--OWNER ACCOUNT-->
        <section class="content-header alertMessage" style="display:none"> 
            <div class="alert alert-message" role="alert">
                <button type="button" class="close cancelAlert closeAlertSection" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong id="success_update_message"> </strong>
                <div id="change_email_message"></div>
            </div>
        </section>
        <section class="content-header"> 
            <h1>My Archive</h1>
            <ol class="breadcrumb">
                <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">My Archive</li>
            </ol>
            <h4 class="list_title text-center"><span class="pull-left">Create Client Account</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span> </h4>
        </section>

        <section class="content">
            <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
            <div class="row">
                <input type="hidden" name="archive_id" id="archive_id" value="">
                <input type="hidden" name="archive_user_id" id="archive_user_id" value="">
                <input type="hidden" name="user_old_email" id="user_old_email" value="">
                <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
                <div class="">
                    <div class="container-fluid manageInfo">

                        <div class="row marginTop20" id="display_message" style="display:none">
                            <div class="col-md-12" style="color:green">
                                Your profile has been created successfully.
                            </div>
                        </div>
                        <!-- fix start for issue id 0002466 on 25-June-2025 -->
                        <div class="row-fluid marginTop20 bgNone"><h3>Client Information</h3></div>
                        <!-- fix end for issue id 0002466 on 25-June-2025 -->
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <!-- fix start for issue id 0002466 on 25-June-2025 -->
                                <label>Client Name<span>*</span>:</label>
                                <!-- fix end for issue id 0002466 on 25-June-2025 -->
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="society_name" id="society_name1" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>State:</label>
                            </div>
                            <div class="col-md-3" >
                                <select class="form-control society_state_list" name="society_state" id="society_state"></select>
                            </div> 
                        </div>

                        <div class="row-fluid marginTop20 bgNone"><h3>Contact Information</h3></div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>First Name:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="firstName" id="firstName" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Last Name:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="lastName" id="lastName" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Title:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="title" id="title" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Username<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control" name="register_username"  id="register_username1" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <!-- fix start for issue id 0002466 on 25-June-2025 -->
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Password<span>*</span>:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="password" class="form-control" name="register_password"  id="register_password" value="" placeholder="Enter Password">
                            </div>
                        </div>
                        <!-- fix end for issue id 0002466 on 25-June-2025 -->
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Email-Id:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="form-control"  name="register_emailId" id="register_emailId" value="<?php echo ADMIN_EMAIL; ?>" placeholder="Enter text" >
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Phone Number:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" maxlength="14" class="form-control" name="phoneNumber" id="phoneNumber" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <!-- fix start for issue id 0002466 on 25-June-2025 -->
                        <span style="display:none;">
                            <div class="row-fluid marginTop20 bgNone"><h3>Other Information</h3></div>
                            <div class="row marginTop20 padd5">
                                <div class="col-md-3" >
                                    <label>Redactions Email Address:</label>
                                </div>
                                <div class="col-md-3" >
                                    <input type="text" class="form-control" name="redactionsEmailAddress" id="redactionsEmailAddress" value="" placeholder="Enter text">
                                </div> 
                            </div>

                            <div class="row marginTop20 padd5">
                                <div class="col-md-3" >
                                    <label>Reprint Email Address:</label>
                                </div>
                                <div class="col-md-3" >
                                    <input type="text" class="form-control" name="reprintEmailAddress" id="reprintEmailAddress" value="" placeholder="Enter text">
                                </div> 
                            </div>
                            <div class="row marginTop20 padd5">
                                <div class="col-md-3" >
                                    <label>Contact Email Address:</label>
                                </div>
                                <div class="col-md-3" >
                                    <input type="text" class="form-control" name="contactEmailAddress" id="contactEmailAddress" value="" placeholder="Enter text">
                                </div> 
                            </div>
                            <div class="row marginTop20 padd5">
                                <div class="col-md-3" >
                                    <label>Website URL:</label>
                                </div>
                                <div class="col-md-3" >
                                    <input type="text" class="form-control" name="websiteURL" id="websiteURL" value="" placeholder="Enter text">
                                </div> 
                            </div>
                        </span>
                        <!-- fix end for issue id 0002466 on 25-June-2025 -->
                        <div class="row-fluid marginTop20 bgNone"><h3>Location Information</h3></div>

                        <div class="row hedingSub bgNone text-center">
                            <div class="col-md-6" ><h3 class="bgNone">Physical Address</h3> </div> 
                            <div class="col-md-6" ><h3 class="bgNone marginBottom10">Mailing Address</h3> <input type="checkbox" name="sameAsPhysicalAddress" id="sameAsPhysicalAddress"  value="true"> <?php echo $osValue['title'] ?> Same As physical Address</div>
                        </div>

                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Address Line 1:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="physical form-control" name="physicalAddressLine1" id="physicalAddressLine1" value="" placeholder="Enter text">
                            </div> 

                            <div class="col-md-3" >
                                <label>Address Line 1:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="mailing form-control" name="mailingAddressLine1" id="mailingAddressLine1" value="" placeholder="Enter text">
                            </div> 
                        </div>


                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Address Line 2:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="physical form-control" name="physicalAddressLine2" id="physicalAddressLine2" value="" placeholder="Enter text">
                            </div> 

                            <div class="col-md-3" >
                                <label>Address Line 2:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="mailing form-control" name="mailingAddressLine2" id="mailingAddressLine2" value="" placeholder="Enter text">
                            </div> 
                        </div>

                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>City:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="physical form-control" name="physicalCity" id="physicalCity" value="" placeholder="Enter text">
                            </div> 

                            <div class="col-md-3" >
                                <label>City:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="mailing form-control" name="mailingCity" id="mailingCity" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>State:</label>
                            </div>
                            <div class="col-md-3" >
                                <select class="physical form-control physical_state" name="physicalState" id="physicalState"> </select>
                            </div> 

                            <div class="col-md-3" >
                                <label>State:</label>
                            </div>
                            <div class="col-md-3" >
                                <select class="mailing form-control mailing_state" name="mailingState" id="mailingState"> </select>
                            </div> 
                        </div>

                        <div class="row marginTop20 padd5">
                            <div class="col-md-3" >
                                <label>Zip:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="physical form-control" name="physicalZip" id="physicalZip" value="" placeholder="Enter text">
                            </div> 

                            <div class="col-md-3" >
                                <label>Zip:</label>
                            </div>
                            <div class="col-md-3" >
                                <input type="text" class="mailing form-control" name="mailingZip" id="mailingZip" value="" placeholder="Enter text">
                            </div> 
                        </div>
                        <!-- fix start for issue id 0002466 on 25-June-2025 -->
                        <span style="display:none;">
                            <div class="row-fluid marginTop20 bgNone"><h3>Tax Information</h3></div>
                            <div class="row marginTop20 padd5">
                                <div class="col-md-3" >
                                    <label>Federal TIN:</label>
                                </div>
                                <div class="col-md-3" >
                                    <input type="text" class="form-control" name="federalTaxIDNumber" id="federalTaxIDNumber" value="" placeholder="Enter text">
                                </div> 
                            </div>
                            <div class="row marginTop20 padd5">
                                <div class="col-md-3" >
                                    <label>State Tax ID Number:</label>
                                </div>
                                <div class="col-md-3" >
                                    <input type="text" class="form-control" name="sateTaxIDNumber" id="sateTaxIDNumber" value="" placeholder="Enter text">
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
                                    <input type="text" class="form-control" name="entityOrganizationOther" id="entityOrganizationOther" value="" placeholder="Enter text">
                                </div> 
                            </div>

                            <div class="row-fluid marginTop20 bgNone"><h3>Organization Structure</h3></div>
                            <div class="row marginTop20 paddLeft15 padd5"><strong>Select all that apply</strong></div>
                            <?php foreach ($orgStructureArray as $osKey => $osValue) { ?>
                                <div class="row marginTop20 padd5">
                                    <div class="col-md-12" >
                                        <input type="checkbox" class="orgStructure" name="<?php echo $osKey ?>" id="<?php echo $osKey ?>" value="true" > <?php echo $osValue['title'] ?>
                                    </div>            
                                </div>
                                <?php if ($osValue['field'] == true) { ?>
                                    <div class="row padd5" style="display:none" id="<?php echo "check_" . $osKey; ?>" >
                                        <div class="col-md-3" >
                                            <input type="text" class="form-control" name="<?php echo $osKey . "_firstName" ?>" id="<?php echo $osKey . "_firstName" ?>" value="" placeholder="Enter First Name">
                                        </div>       
                                        <div class="col-md-3" >
                                            <input type="text" class="form-control" name="<?php echo $osKey . "_lastName" ?>" id="<?php echo $osKey . "_lastName" ?>" value="" placeholder="Enter Last Name">
                                        </div>         
                                        <div class="col-md-3" >
                                            <input type="text" class="form-control" name="<?php echo $osKey . "_email" ?>" id="<?php echo $osKey . "_email" ?>" value="" placeholder="Enter Email Address">
                                        </div>         
                                    </div>
                                <?php } ?>
                            <?php } ?>

                            <div class="row marginTop20 bgNone"> 
                                <div class="col-md-9">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="Y" name="occasional_update" id="occasional_update" />
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
                                            I agree to the <a href="javascript:service_term_popup();" style="color:#72afd2;" >Terms of service</a>
                                        </label>
                                    </div>
                                </div> 
                            </div>
                         <!-- fix end for issue id 0002466 on 25-June-2025 -->
                        </span>       
                        <div class="row bgNone termsCondition" style="display:none;">
                            <div class="col-md-6">
                                <div class="">
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
                                                            <button type="button" class="btn btn-info  borderRadiusNone" id="agreeTermButton">Yes I agree</button>
                                                            <button type="button" class="btn btn-info  borderRadiusNone closepopup" id="notAgreeTermButton">No I do not agree</button>
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
                </div>
            </div>
        </section>

        <!--MANAGE HOME PAGE TEMPLATE-->
        <section class="content-header"> 
            <h1>My Archive</h1>
            <ol class="breadcrumb">
                <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">My Archive</li>
            </ol>
            <h4 class="list_title text-center"><span class="pull-left">Manage Home Page Template </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span></h4>
        </section>
        <section class="content manageHomeUI">
            <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
            <div class="row">
                <div class="col-md-offset-2 col-md-8 col-md-offset-2">
                    <input type="hidden" name="type" id="type" value="">
                    <input type="hidden" name="color_code" id="color_code" value="" />
                    <input type="hidden" name="color_code_selected_for" id="color_code_selected_for" value="" />
                    <div class="row" style="display: none;">
                        <div class="col-md-4 text-right"><strong>Historical Society Name:</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control" id="archive_name"  name="archive_name" placeholder="Text input" value="">
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
                    <div class="row-group" style="display:none;">
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
                            <img class="archive-details-logo-section marginTop5" id="archive_logo_image_post_display" src="" alt="your image" />
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_logo_image"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_logo_x" id="archive_logo_x" />
                            <input type="hidden" name="archive_logo_y" id="archive_logo_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Banner Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_header_image" id="archive_header_image">
                            <span class="help-text custom_temp_crop">(Must be at least 1600 X 340 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section marginTop5" id="archive_header_image_post" src="" alt="your image" />
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_header_image"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="archive_header_x" id="archive_header_x" />
                            <input type="hidden" name="archive_header_y" id="archive_header_y" />
                            <input type="hidden" name="baner_crop_height" id="baner_crop_height">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Promotional Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_details_image" id="archive_details_image">
                            <span class="help-text">(Must be at least 600 X 400 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section marginTop5" id="archive_details_image_post" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_details_image"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
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
                            <img class="archive-details-logo-section marginTop5" id="archive_group_thumb_post" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_group_thumb"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
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
                            <img class="archive-details-logo-section marginTop5" id="archive_group_details_thumb_img" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="archive_group_details_thumb"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
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
                            <img class="archive-details-logo-section marginTop5" id="historical_connection_logo_img" src="" alt="your image"/>
                            <a href="javascript:void(0);" class="remove-society-image" property-name="historical_connection_logo"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                            <input type="hidden" name="historical_connection_logo_x" id="historical_connection_logo_x" />
                            <input type="hidden" name="historical_connection_logo_y" id="historical_connection_logo_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-info borderRadiusNone" id="register_society_frm_sub">Submit</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="register_society_frm_clear">Clear Form</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
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

<!--OWNER ACCOUNT SCRIPT START HERE-->
<script type="text/javascript">
                                                    var archive_id;
                                                    var location_href = 0;
                                                    $(document).ready(function () {
                                                        $('#society_name1').blur(function () {
                                                            var society_name = $(this).val();
                                                            $('#archive_name').val(society_name);
                                                        });
                                                        $("#register_society_frm").validate({
                                                            rules: {
                                                                society_name: "required",
                                                                register_username: "required",
                                                                // fix start for issue id 0002466 on 25-June-2025
                                                                register_password: "required",
                                                                // fix end for issue id 0002466 on 25-June-2025
                                                                society_state: {
                                                                    //required: true,
                                                                    remote: {
                                                                        url: "../services.php",
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
                                                            },
                                                            messages: {
                                                                society_name: "Please enter society name",
                                                                register_username: "Please enter username",
                                                                // fix start for issue id 0002466 on 25-June-2025
                                                                register_password: "Please enter password",
                                                                // fix end for issue id 0002466 on 25-June-2025
                                                                society_state: {
                                                                    //required: "Society state is required",
                                                                    remote: jQuery.validator.format("Historical Society Name is already taken for the state {0}.")
                                                                }
                                                            }
                                                        });
                                                        $("#register_society_frm_sub").on("click", function () {
                                                            location_href = 1;
                                                            if ($("#register_society_frm").valid()) {
                                                                var arc_id = $('#archive_id').val();
                                                                if (arc_id && arc_id != '') {
                                                                    updateOwnerAccountData();
                                                                } else {
                                                                    insertOwnerAccountData();
                                                                }
                                                            }
                                                        });

                                                        var phones = [{"mask": "(###) ###-####"}, {"mask": "(###) ###-##############"}];
                                                        $('#phoneNumber').inputmask({
                                                            mask: phones,
                                                            greedy: false,
                                                            definitions: {'#': {validator: "[0-9]", cardinality: 1}}
                                                        });
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
                                                    });
                                                    $(document).on('click', '.closeAlertSection', function () {
                                                        $('.alertMessage').css('display', 'none');
                                                    });

                                                    var jcrop_api_logo;
                                                    var countLogo = 0;
                                                    function readURLLogo(input) {
                                                        if (input.files && input.files[0]) {
                                                            var reader = new FileReader();
                                                            reader.onload = function (e) {
                                                                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                                                                $('#crop_uploading_image').modal('show');
                                                                $('#archive_logo_image_pre').Jcrop({
                                                                    //aspectRatio: 1,
                                                                    onSelect: updateCoordsLogo,
                                                                    minSize: [200, 200],
                                                                    maxSize: [200, 200],
                                                                    setSelect: [100, 100, 0, 0]
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

                                                    var jcrop_api_header;
                                                    var countheader = 0;
                                                    function readURLheader(input, height) {
                                                        if (input.files && input.files[0]) {
                                                            var crop_height = 1300;
                                                            if (height == 800) {
                                                                crop_height = 2000;
                                                            }

                                                            var reader = new FileReader();
                                                            reader.onload = function (e) {
                                                                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                                                                $('#crop_uploading_image').modal('show');
                                                                $('#archive_logo_image_pre').Jcrop({
                                                                    onSelect: updateCoordsheader,
                                                                    minSize: [1600, height],//minSize: [crop_height, 3900],
                                                                    maxSize: [1600, height],//maxSize: [crop_height, 3900],
                                                                    //aspectRatio: 3 / 1,
                                                                    setSelect: [100, 100, 0, 0],
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
                                                                    //aspectRatio: 1,
                                                                    onSelect: updateCoordsdetails,
                                                                    minSize: [600, 400],
                                                                    maxSize: [600, 400],
                                                                    setSelect: [100, 100, 0, 0]
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
                                                    }


                                                    var jcrop_api_gruop_thumb;
                                                    var countgroupthumb = 0;
                                                    function readURLarchiveGroupThumb(input) {
                                                        if (input.files && input.files[0]) {
                                                            var reader = new FileReader();
                                                            reader.onload = function (e) {
                                                                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                                                                $('#crop_uploading_image').modal('show');
                                                                $('#archive_logo_image_pre').Jcrop({
                                                                    //aspectRatio: 1,
                                                                    onSelect: updateCoordsGroupThumb,
                                                                    minSize: [400, 400],
                                                                    maxSize: [400, 400],
                                                                    setSelect: [100, 100, 0, 0]
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
                                                                    //aspectRatio: 1,
                                                                    onSelect: updateCoordsGroupDetailsThumb,
                                                                    minSize: [400, 400],
                                                                    maxSize: [400, 400],
                                                                    setSelect: [100, 100, 0, 0]
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
                                                                    //aspectRatio: 1,
                                                                    onSelect: updateCoordsHIstConnectionLogo,
                                                                    minSize: [200, 200],
                                                                    maxSize: [200, 200],
                                                                    setSelect: [100, 100, 0, 0]
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
                                                                if (imgwidth < 200 || imgheight < 200) {
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

                                                        $("#archive_header_image").change(function () {
                                                            var custom_temp = $('input[name=custom_template]:checked').val();
                                                            var crop_height = 340;
                                                            if (custom_temp != 'default') {
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
                                                                if (imgwidth < 1600 || imgheight < crop_height) {
                                                                    showPopupMessage('error', 'Image dimension must be at least 1600X' + crop_height + '(Width X Height)');
                                                                    return false;
                                                                } else {
                                                                    $('#type').val('banner');
                                                                    if (countheader > 0) {
                                                                        jcrop_api_header.destroy();
                                                                    }
                                                                    countheader = countheader + 1;
                                                                    readURLheader(currentObj, crop_height);
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
                                                                if (imgwidth < 600 || imgheight < 400) {
                                                                    showPopupMessage('error', 'Image dimension must be at least 600X400(Width X Height)');
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
                                                                if (imgwidth < 400 || imgheight < 400) {
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
                                                                if (imgwidth < 400 || imgheight < 400) {
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
                                                                if (imgwidth < 200 || imgheight < 200) {
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

                                                        $(window).resize(function () {
                                                            var height = $(this).height() - 130;
                                                            $('#crop_uploading_image .modal-body').css('height', height + 'px');
                                                        });
                                                        $(window).resize();
                                                        //Changes start for color and font option
                                                        $('.colorpicker').each(function () {
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
                                                        $('.fontSelect').each(function () {
                                                            var font_applied_for = $(this).attr('data-type');
                                                            $(this).fontSelector({
                                                                'hide_fallbacks': true,
                                                                'initial': 'Select Font,Arial',
                                                                'selected': function (style) {
                                                                    if (style != 'Select Font,Arial') {
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
                                                    });

                                                    $(document).on('click', '#close_crop_popup', function (e) {
                                                        e.preventDefault();
                                                        location_href = 0;
                                                        if ($("#register_society_frm").valid()) {
                                                            $('.admin-loading-image').show();
                                                            var arc_id = $('#archive_id').val();
                                                            if (!arc_id || arc_id == '') {
                                                                insertOwnerAccountData();
                                                            }
                                                            setTimeout(function () {
                                                                $.ajax({
                                                                    url: "services_admin_api.php?mode=upload_croped_image",
                                                                    type: "post",
                                                                    data: new FormData($("#register_society_frm")[0]),
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
                                                                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 609)');
                                                                    }
                                                                });
                                                            }, 3000);
                                                        } else {
                                                            $('.admin-loading-image').hide();
                                                            $('#crop_uploading_image').modal('hide');
                                                            alert('Enter society name');
                                                            $('#archive_logo_image,#archive_header_image,#archive_details_image,#archive_group_thumb,#archive_group_details_thumb,#historical_connection_logo').val('');
                                                        }
                                                    });

                                                    $(document).on('change', 'input[type=radio][name=custom_template]', function () {
                                                        if ($(this).val() == 'default') {
                                                            $('.custom_temp_crop').text('(Must be at least 1600 X 340 (Width X Height)) ');
                                                        } else {
                                                            $('.custom_temp_crop').text('(Must be at least 1600 X 800 (Width X Height)) ');
                                                        }
                                                    });
                                                    function update_font_face(field_name, font) {
                                                        setArchiveProperty(field_name, font);
                                                    }

                                                    $(document).on('mouseup', '.colpick', function () {
                                                        var color_code = $('#color_code').val();
                                                        var color_field = $('#color_code_selected_for').val();
                                                        if (color_code != '' && color_field != '') {
                                                            setArchiveProperty(color_field, color_code);
                                                        }
                                                    });
                                                    $(document).on('change', '.change-font-size', function () {
                                                        var font_size = $(this).val();
                                                        var font_field = $(this).attr('data-type');
                                                        if (font_size != '' && font_field != '') {
                                                            setArchiveProperty(font_field, font_size);
                                                        }
                                                    });
                                                    function setArchiveProperty(field_name, field_value) {
                                                        if (field_name != '' && field_value != '') {
                                                            var archive_id = $('#archive_id').val();
                                                            $('.admin-loading-image').show();
                                                            $.ajax({
                                                                url: "services_admin_api.php",
                                                                type: "post",
                                                                data: {mode: 'update_archive_font_color', field_name: field_name, field_value: field_value, archive_id: archive_id},
                                                                success: function (response) {
                                                                    var record = JSON.parse(response);
                                                                    if (record.status == 'success') {
                                                                        $('.admin-loading-image').hide();
                                                                    }
                                                                },
                                                                error: function () {
                                                                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 610)');
                                                                }
                                                            });
                                                        }
                                                    }

                                                    function selectDefaultFont(containerId, selectedFont = ''){
                                                        if (selectedFont != '') {
                                                            var font = selectedFont.substr(0, selectedFont.indexOf(','));
                                                            font = font.replace(/'/g, '');
                                                            font = font.replace(/"/g, '');
                                                            $('#' + containerId).children('span').text(font);
                                                            $('#' + containerId).css('font-family', selectedFont);
                                                        }
                                                    }

                                                    $(document).on('click', '.remove-society-image', function () {
                                                        var property_name = $(this).attr('property-name');
                                                        var item_id = $('#archive_id').val();
                                                        if (confirm('Are you sure to delete this? Once removed can\'t be undone.')) {
                                                            $('.admin-loading-image').show();
                                                            $.ajax({
                                                                url: "services_admin_api.php",
                                                                type: "post",
                                                                data: {mode: 'remove_society_images', property_name: property_name, item_id: item_id},
                                                                success: function (response) {
                                                                    var record = JSON.parse(response);
                                                                    if (record.status == 'success') {
                                                                        $(this).hide();
                                                                        if (property_name == 'archive_logo_image') {
                                                                            $('#archive_logo_image').val('');
                                                                            $('#archive_logo_image_post_display').attr('src', '');
                                                                            $('#archive_logo_image_post_display').hide();
                                                                        }
                                                                        if (property_name == 'archive_header_image') {
                                                                            $('#archive_header_image').val('');
                                                                            $('#archive_header_image_post').attr('src', '');
                                                                            $('#archive_header_image_post').hide();
                                                                        }
                                                                        if (property_name == 'archive_details_image') {
                                                                            $('#archive_details_image').val('');
                                                                            $('#archive_details_image_post').attr('src', '');
                                                                            $('#archive_details_image_post').hide
                                                                        }
                                                                        if (property_name == 'archive_group_thumb') {
                                                                            $('#archive_group_thumb').val('');
                                                                            $('#archive_group_thumb_post').attr('src', '');
                                                                            $('#archive_group_thumb_post').hide()
                                                                        }
                                                                        if (property_name == 'archive_group_details_thumb') {
                                                                            $('#archive_group_details_thumb').val('');
                                                                            $('#archive_group_details_thumb_img').attr('src', '');
                                                                            $('#archive_group_details_thumb_img').hide();
                                                                        }
                                                                        if (property_name == 'historical_connection_logo') {
                                                                            $('#historical_connection_logo').val('');
                                                                            $('#historical_connection_logo_img').attr('src', '');
                                                                            $('#historical_connection_logo_img').hide();
                                                                        }
                                                                        $('.admin-loading-image').hide();
                                                                    }
                                                                },
                                                                error: function () {
                                                                    $('.admin-loading-image').hide();
                                                                }
                                                            });
                                                        }
                                                    });

                                                    function service_term_popup() {
                                                        $('#term_of_services').modal('show');
                                                        $('.admin-loading-image').show();
                                                        $.ajax({
                                                            url: "services_admin_api.php",
                                                            type: "post",
                                                            data: {mode: 'get_term_and_condition', user_id: 1},
                                                            success: function (data) {
                                                                var result = JSON.parse(data);
                                                                if (result.status == 'success') {
                                                                    $('#get_term_cond_data').html(result.message);
                                                                    $('.termsCondition').css('display', 'block');
                                                                } else {
                                                                    showPopupMessage('error', 'error', 'Something went wrong, Please try again. (Error Code: 611)');
                                                                }
                                                                $('.admin-loading-image').hide();
                                                            },
                                                            error: function () {
                                                                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 612)');
                                                            }
                                                        });
                                                    }

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
                                                                var society_state = "";
                                                                var physical_state = "";
                                                                var mailing_state = "";
                                                                var display_state = "";
                                                                society_state += "<option value='' >---Select---</option>";
                                                                physical_state += "<option value='' >---Select---</option>";
                                                                mailing_state += "<option value='' >---Select---</option>";
                                                                display_state += "<option value='' >---Select---</option>";
                                                                for (i = 0; i < record.length; i++) {
                                                                    society_state += "<option value='" + record[i] + "' >" + record[i] + "</option>";
                                                                    physical_state += "<option value='" + record[i] + "' >" + record[i] + "</option>";
                                                                    mailing_state += "<option value='" + record[i] + "'   >" + record[i] + "</option>";
                                                                    display_state += "<option value='" + record[i] + "'  >" + record[i] + "</option>";
                                                                }
                                                                $(".society_state_list").html(society_state);
                                                                $(".physical_state").html(physical_state);
                                                                $(".mailing_state").html(mailing_state);
                                                                $(".display_state_list").html(display_state);

                                                                $('.loading-div').hide();

                                                            },
                                                            error: function () {
                                                                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 613)');
                                                                $('.loading-div').hide();
                                                            }
                                                        });
                                                    }

                                                    function getOwnerAccountParams() {
                                                        var arc_id = $('#archive_id').val();
                                                        var owner_account_data = {
                                                            archive_id: arc_id,
                                                            society_name: $('#society_name1').val(),
                                                            society_state: $('#society_state').val(),
                                                            firstName: $('#firstName').val(),
                                                            lastName: $('#lastName').val(),
                                                            title: $('#title').val(),
                                                            name: $('#title').val() + ' ' + $('#firstName').val() + ' ' + $('#lastName').val(),
                                                            register_username: $('#register_username1').val(),
                                                            register_emailId: $('#register_emailId').val(),
                                                            phoneNumber: $('#phoneNumber').val(),
                                                            redactionsEmailAddress: $('#redactionsEmailAddress').val(),
                                                            reprintEmailAddress: $('#reprintEmailAddress').val(),
                                                            contactEmailAddress: $('#contactEmailAddress').val(),
                                                            websiteURL: $('#websiteURL').val(),
                                                            physicalAddressLine1: $('#physicalAddressLine1').val(),
                                                            physicalAddressLine2: $('#physicalAddressLine2').val(),
                                                            physicalCity: $('#physicalCity').val(),
                                                            physicalState: $('#physicalState').val(),
                                                            physicalZip: $('#physicalZip').val(),
                                                            sameAsPhysicalAddress: $('#sameAsPhysicalAddress').val(),
                                                            mailingAddressLine1: $('#mailingAddressLine1').val(),
                                                            mailingAddressLine2: $('#mailingAddressLine2').val(),
                                                            mailingCity: $('#mailingCity').val(),
                                                            mailingState: $('#mailingState').val(),
                                                            mailingZip: $('#mailingZip').val(),
                                                            federalTaxIDNumber: $('#federalTaxIDNumber').val(),
                                                            sateTaxIDNumber: $('#sateTaxIDNumber').val(),
                                                            entityOrganization: $('#entityOrganization').val(),
                                                            boardOfDirectors: $('#boardOfDirectors').is(':checked'),
                                                            CEO: $('#CEO').is(':checked'),
                                                            CEO_firstName: $('#CEO_firstName').val(),
                                                            CEO_lastName: $('#CEO_lastName').val(),
                                                            CEO_email: $('#CEO_email').val(),
                                                            executiveDirector: $('#executiveDirector').is(':checked'),
                                                            executiveDirector_firstName: $('#executiveDirector_firstName').val(),
                                                            executiveDirector_lastName: $('#executiveDirector_lastName').val(),
                                                            executiveDirector_email: $('#executiveDirector_email').val(),
                                                            precident: $('#precident').is(':checked'),
                                                            precident_firstName: $('#precident_firstName').val(),
                                                            precident_lastName: $('#precident_lastName').val(),
                                                            precident_email: $('#precident_email').val(),
                                                            otherExecutive: $('#otherExecutive').is(':checked'),
                                                            otherExecutive_firstName: $('#otherExecutive_firstName').val(),
                                                            otherExecutive_lastName: $('#otherExecutive_lastName').val(),
                                                            otherExecutive_email: $('#otherExecutive_email').val(),
                                                            committees: $('#committees').is(':checked'),
                                                            occasional_update: $('#occasional_update').is(':checked'),
                                                            term_service: $('#term_service').is(':checked'),
                                                            unclaimed_society: '1',
                                                            user_type: 'A',
                                                            // fix start for issue id 0002466 on 25-June-2025
                                                            register_user_password: $('#register_password').val(),
                                                            // register_user_password: 'test',
                                                            // fix end for issue id 0002466 on 25-June-2025
                                                            timestamp: $('#timestamp').val()
                                                        }
                                                        return owner_account_data;
                                                    }
//MANAGE HOME PAGE TEMPLATE FIELDS PARAMS
                                                    function getHomePageParams() {
                                                        var arc_id = $('#archive_id').val();
                                                        var custom_template = '';
                                                        var details_page_design = '';
                                                        if ($('#custom1').is(":checked")) {
                                                            custom_template = 'custom1';
                                                            details_page_design = 'custom';
                                                        } else if ($('#custom2').is(":checked")) {
                                                            custom_template = 'custom2';
                                                            details_page_design = 'custom1';
                                                        } else {
                                                            custom_template = 'default';
                                                            details_page_design = 'default';
                                                        }
                                                        var manage_home_page_template_data = {
                                                            archive_id: arc_id,
                                                            archive_name: $('#archive_name').val(),
                                                            archive_address: $('#archive_address').val(),
                                                            archive_about_content: $('#archive_about_content').val(),
                                                            archive_details_description: $('#archive_details_description').val(),
                                                            archive_request_reprint_text: $('#archive_request_reprint_text').val(),
                                                            archive_upcoming_events: $('#archive_upcoming_events').val(),
                                                            preferred_time_zone: $('#preferred_time_zone').val(),
                                                            archive_contact_number: $('#archive_contact_number').val(),
                                                            archive_website: $('#archive_website').val(),
                                                            archive_timing: $('#archive_timing').val(),
                                                            archive_watermark_text: $('#archive_watermark_text').val(),
                                                            archive_display_state: $('#archive_display_state').val(),
                                                            archive_display_county: $('#archive_display_county').val(),
                                                            archive_display_city: $('#archive_display_city').val(),
                                                            archive_display_zip: $('#archive_display_zip').val(),
                                                            custom_template: custom_template,
                                                            details_page_design: details_page_design,
                                                            unclaimed_society: '1',
                                                            user_type: 'A',
                                                            timestamp: $('#timestamp').val()
                                                        };
                                                        return manage_home_page_template_data;
                                                    }

                                                    function insertOwnerAccountData() {
                                                        var data = getOwnerAccountParams();
                                                        $.ajax({
                                                            url: "../services.php",
                                                            type: "post",
                                                            data: {mode: 'register_new_user', formData: $.param(data)},
                                                            success: function (response) {
                                                                var record = JSON.parse(response);
                                                                $('.loading-div').hide();
                                                                document.body.scrollTop = 0;
                                                                if (record.status == 'success') {
                                                                    document.getElementById('archive_id').value = record.archive_id;
                                                                    archive_id = record.archive_id;
                                                                    updateManageHomePageSocietyData();
                                                                    return true;
                                                                }
                                                                else {
                                                                    return false;
                                                                }
                                                            },
                                                            error: function () {
                                                                return false;
                                                            }
                                                        });
                                                    }

                                                    function updateOwnerAccountData() {
                                                        var data = getOwnerAccountParams();
                                                        var updateRegistrationData = $.param(data);
                                                        $.ajax({
                                                            url: "services_admin_api.php",
                                                            type: "post",
                                                            data: {mode: 'update_archive_registration_details', formData: updateRegistrationData},
                                                            success: function (response) {
                                                                var record = JSON.parse(response);
                                                                if (record.status == 'success') {
                                                                    updateManageHomePageSocietyData();
                                                                    $('.alertMessage').css('display', 'block');
                                                                    $('#success_update_message').html(record.message);
                                                                    if (record.change_email_message != '') {
                                                                        $('#change_email_message').html(record.change_email_message);
                                                                    }
                                                                    $('.admin-loading-image').hide();
                                                                    $("html, body").animate({scrollTop: 0}, "slow");
                                                                }
                                                                $('.admin-loading-image').hide();
                                                            },
                                                            error: function () {
                                                                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 614)');
                                                            }
                                                        });
                                                    }

                                                    function updateManageHomePageSocietyData() {
                                                        var data = getHomePageParams();
                                                        var archiveGroupetails = $.param(data);
                                                        var reprint_request_data = tinymce.get("archive_request_reprint_text").getContent();
                                                        $.ajax({
                                                            url: "services_admin_api.php",
                                                            type: "post",
                                                            data: {mode: 'update_archive_group_details', formData: archiveGroupetails, reprint_request_data: reprint_request_data},
                                                            success: function (response) {
                                                                var record = JSON.parse(response);
                                                                if (record.status == 'success') {
                                                                    //$('.admin-loading-image').hide();
                                                                    if (location_href == 1) {
                                                                        window.location.href = 'manage_my_archive.php';
                                                                    }
                                                                }
                                                                $('.admin-loading-image').hide();
                                                            },
                                                            error: function () {
                                                                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 615)');
                                                            }
                                                        });
                                                    }

                                                    get_state();
</script>