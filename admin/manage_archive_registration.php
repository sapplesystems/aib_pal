<?php
session_start();
ini_set('display_errors',0);
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$archive_id = $_REQUEST['archive_id'];
$entityOrgArray=array(
                      "501 Non-profit"=>"501 Non-profit",
                      "Corporation"=>"Corporation",
                      "LLC"=>"LLC",
                      "coOp"=>"coOp",
                      "Sole Properietor"=>"Sole Properietor",
                      "Other"=>"Other",
                    );
$orgStructureArray=array(
                      "boardOfDirectors"=>array("title"=>"Board of Directors",'field'=>false),
                      "CEO"=>array("title"=>"CEO",'field'=>true),
                      "executiveDirector"=>array("title"=>"Executive Director",'field'=>true), 
                      "precident"=>array("title"=>"president",'field'=>true),
                      "otherExecutive"=>array("title"=>"Other Executive",'field'=>true),
                      "committees"=>array("title"=>"Committees",'field'=>false)
                    );
$user_id='';
if(isset($_SESSION['aib']['user_data'])){
   $user_id = $_SESSION['aib']['user_data']['user_id']; 
   $user_email ='';
   if(!empty($_SESSION['aib']['user_data']['user_prop'])){
    $user_email =  $_SESSION['aib']['user_data']['user_prop']['email'];  
   }
}

?>
<style>
	#crop_uploading_image .modal-body{overflow:auto; padding: 15px;}
	.marginTop15{margin-top:15px;}
</style>
<div class="content-wrapper">
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
        <h4 class="list_title text-center"><span class="pull-left">Edit Owner Account</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> <a href="manage_my_archive.php" class="btn btn-danger btn-sm pull-right" style="margin-top:-5px;">Back</a></h4>
		
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <form id="registrationOtherForm" name="registrationOtherForm" method="post" class="form-horizontal">
                <input type="hidden" name="archive_id" value="<?php echo $archive_id; ?>">
                <input type="hidden" name="archive_user_id" id="archive_user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="user_old_email" id="user_old_email" value="<?php echo $user_email; ?>">
                 <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
            <div class="">
                <div class="container-fluid manageInfo">
                    
                <div class="row marginTop20" id="display_message" style="display:none">
                <div class="col-md-12" style="color:green">
                        Your profile has been updated successfully.
                </div>
                </div>
                    <div class="row-fluid marginTop20 bgNone"><h3>Historical Society Information</h3></div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>Historical Society Name<span>*</span>:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="society_name" id="society_name1" value="" placeholder="Enter text">
                        </div> 
                    </div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>State<span>*</span>:</label>
                        </div>
                        <div class="col-md-3" >
                            <select class="form-control society_state_list" name="society_state" id="society_state"></select>
<!--                            <input type="text" class="form-control" name="society_state" id="society_state" value="" placeholder="Enter text">-->
                        </div> 
                    </div>

                    <div class="row-fluid marginTop20 bgNone"><h3>Contact Information</h3></div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>First Name<span>*</span>:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="firstName" id="firstName" value="" placeholder="Enter text">
                        </div> 
                    </div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>Last Name<span>*</span>:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="lastName" id="lastName" value="" placeholder="Enter text">
                        </div> 
                    </div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>Title<span>*</span>:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" class="form-control" name="title" id="title" value="" placeholder="Enter text">
                        </div> 
                    </div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>Username:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" class="form-control"  id="register_username1" value="" placeholder="Enter text" disabled="true">
                        </div> 
                    </div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>Owner Email Address:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" class="form-control"  name="register_emailId" id="register_emailId" value="" placeholder="Enter text" >
                        </div> 
                        <?php if($_SESSION['aib']['user_data']['user_type'] != 'R'){ ?>
                        <div class="col-md-3" ><a href="#" onclick="resendValidationLinkAgain(event);" class="btn btn-primary">Resend Validation Link</a></div>
                        <?php } ?>
                    </div>
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3" >
                            <label>Phone Number<span>*</span>:</label>
                        </div>
                        <div class="col-md-3" >
                            <input type="text" maxlength="14" class="form-control" name="phoneNumber" id="phoneNumber" value="" placeholder="Enter text">
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
                    
                    
    
    <!--<div class="row-fluid marginTop20 bgNone"><h3>Other Information</h3></div>
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
    </div>-->
    
    <div class="row-fluid marginTop20 bgNone"><h3>Location Information</h3></div>
    
    <div class="row hedingSub bgNone text-center">
        <div class="col-md-6" ><h3 class="bgNone">Physical Address</h3> </div> 
        <div class="col-md-6" ><h3 class="bgNone marginBottom10">Mailing Address</h3> <input type="checkbox" name="sameAsPhysicalAddress" id="sameAsPhysicalAddress" > <?php echo $osValue['title']?> Same As physical Address</div>
    </div>
    
    <div class="row marginTop20 padd5">
        <div class="col-md-3" >
            <label>Address Line 1<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <input type="text" class="physical form-control" name="physicalAddressLine1" id="physicalAddressLine1" value="" placeholder="Enter text">
        </div> 
        
        <div class="col-md-3" >
            <label>Address Line 1<span>*</span>:</label>
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
            <label>City<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <input type="text" class="physical form-control" name="physicalCity" id="physicalCity" value="" placeholder="Enter text">
        </div> 
        
        <div class="col-md-3" >
            <label>City<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <input type="text" class="mailing form-control" name="mailingCity" id="mailingCity" value="" placeholder="Enter text">
        </div> 
    </div>
    <div class="row marginTop20 padd5">
        <div class="col-md-3" >
            <label>State<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <select class="physical form-control physical_state" name="physicalState" id="physicalState"> </select>
            <!--<input type="text" class="physical form-control" name="physicalState" id="physicalState" value="" placeholder="Enter text">-->
        </div> 
        
        <div class="col-md-3" >
            <label>State<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <select class="mailing form-control mailing_state" name="mailingState" id="mailingState"> </select>
        </div> 
    </div>
    
    <div class="row marginTop20 padd5">
        <div class="col-md-3" >
            <label>Zip<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <input type="text" class="physical form-control" name="physicalZip" id="physicalZip" value="" placeholder="Enter text">
        </div> 
        
        <div class="col-md-3" >
            <label>Zip<span>*</span>:</label>
        </div>
        <div class="col-md-3" >
            <input type="text" class="mailing form-control" name="mailingZip" id="mailingZip" value="" placeholder="Enter text">
        </div> 
    </div>
    
   <!-- <div class="row-fluid marginTop20 bgNone"><h3>Tax Information</h3></div>
    <div class="row marginTop20 padd5">
        <div class="col-md-3" >
            <label>Federal Tax ID Number:</label>
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
            <select name="entityOrganization" class="form-control" id="entityOrganization" onChange="if(this.value=='Other'){$('#eoOther').show();}else{$('#eoOther').hide();}">
                <?php foreach($entityOrgArray as $eoaKey=>$eoaValue){?>
                <option value="<?php echo $eoaKey;?>"> <?php echo $eoaValue;?></option>
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
    </div>-->
    
    <div class="row-fluid marginTop20 bgNone"><h3>Terms of Services</h3></div>
    <!--<div class="row marginTop20 paddLeft15 padd5"><strong>Select all that apply</strong></div>
    <?php foreach($orgStructureArray as $osKey=>$osValue){?>
    <div class="row marginTop20 padd5">
        <div class="col-md-12" >
            <input type="checkbox" class="orgStructure" name="<?php echo $osKey?>" id="<?php echo $osKey?>" value="true" > <?php echo $osValue['title']?>
        </div>            
    </div>
        <?php if($osValue['field']==true){ ?>
            <div class="row padd5" style="display:none" id="<?php echo "check_".$osKey;?>" >
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="<?php echo $osKey."_firstName"?>" id="<?php echo $osKey."_firstName"?>" value="" placeholder="Enter First Name">
                </div>       
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="<?php echo $osKey."_lastName"?>" id="<?php echo $osKey."_lastName"?>" value="" placeholder="Enter Last Name">
                </div>         
                <div class="col-md-3" >
                    <input type="text" class="form-control" name="<?php echo $osKey."_email"?>" id="<?php echo $osKey."_email"?>" value="" placeholder="Enter Email Address">
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
            </div>-->
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
    
    <div class="row marginTop20 bgNone">
    <div class="col-md-4"></div>          
        <div class="col-md-2" >
            <input type="button" class="form-control btn-success" name="regOtherUpdate" id="regOtherUpdate" value="Update" >
        </div> 
		<div class="col-md-2" >
			<a href="manage_my_archive.php" class="btn btn-danger marginTop15">Back</a>
		</div>
	 </div> 
    <div class="clearfix marginTop20"></div>
                </div>
            </div>
            </form>
        </div>
        
    </section>
</div>


<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH . 'jquery.inputmask.bundle.js'; ?>"></script>

<script type="text/javascript">
    var archive_id = '<?php echo $archive_id; ?>';
    var user_id = '<?php echo $user_id; ?>'
    
    function resendValidationLinkAgain(e){
        e.preventDefault();
        $('.admin-loading-image').show();
            $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'change_email_validation_link', 
                user_id: '<?php echo $_SESSION['aib']['user_data']['user_id'] ?>'
            },
            success: function (data){
                 var result = JSON.parse(data);
                 showPopupMessage(result.status, result.message);
                 $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 484)');
                $('.admin-loading-image').hide();
            }
        });
    }
    
    $(document).ready(function(){
        get_state();
        var phones = [{ "mask": "(###) ###-####"}, { "mask": "(###) ###-##############"}];
        $('#phoneNumber').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        });
       $(".orgStructure").on("click",function(){
           var osId=$(this).attr("id");
           if($("#"+osId).is(":checked")==true){
               $("#check_"+osId).show();
           }else{  
               $("#check_"+osId).hide();
           }
       });
       
       $("#sameAsPhysicalAddress").on("change",function(){
            if($(this).is(":checked")==true){
                $("#mailingAddressLine1").val($("#physicalAddressLine1").val());
                $("#mailingAddressLine2").val($("#physicalAddressLine2").val());
                $("#mailingCity").val($("#physicalCity").val());
                $("#mailingState").val($("#physicalState").val());
                $("#mailingZip").val($("#physicalZip").val());
            }else{
                $(".mailing").each(function(){
                    $(this).val("");
                });
            }
       });
       
       
   $("#registrationOtherForm").validate({
     rules: {  
     society_name: "required",
     firstName: "required",
     lastName: "required",
     title: "required",
     society_state :{
                    required:true,
                    remote: {
                        url:"services_admin_api.php",
                        type:"POST",
                        data:{ 
                                mode:function(){ return "check_duplicate_item";},
                                society_name:function(){ return $("#society_name1").val();}
                        }
                    }
        },
     register_username: {
         required: {
             depends:function(){
                 $(this).val($.trim($(this).val()));
                 return true;
             }
         },
         email: true
     },
     phoneNumber: {
              required:true,
              minlength:14,
              maxlength:14  
                                   
         },
     faxNumber: {
             //required:true,
             minlength:10,
             maxlength:14,
             number: true                        
         }, 
         physicalAddressLine1: "required",
         physicalCity: "required",
         physicalState: "required",
         physicalZip: "required",
         mailingAddressLine1: "required",
         mailingCity: "required",
         mailingState: "required",
         mailingZip: "required",
         term_service:"required"
            /*,
         federalTaxIDNumber:"required",
         sateTaxIDNumber:"required",
         entityOrganization:"required"*/                   
     },
     messages: {
             society_name: "Please enter society name",
             firstName: "First Name is required",
             lastName: "Last Name is required",
             title: "Title is required", 
             society_state :{
                 required:"Society state is required",
                 remote: jQuery.validator.format("Historical Society Name is already taken for the state {0}.")
             }, 
             register_username: {
                 required: "Email is required",
                 email: "Please enter valid email"
             },
             phoneNumber: {
                 required: "Phone number is required",
                 number: "Please enter valid Phone Number"                    
             },
             faxNumber: {
                 //required: "Fax number is required",
                 number: "Please enter valid Fax Number"                    
             },          
         physicalAddressLine1: "Physical Address line1 is required",
         physicalCity: "Physical city is required",
         physicalState: "Physical state is required",
         physicalZip: "Physical zip is required", 
         mailingAddressLine1: "Mailing Address line1 is required",
         mailingCity: "Mailing city is required",
         mailingState: "Mailing state is required",
         mailingZip: "Mailing zip is required",
         term_service: "Please accept terms of service"
         /*federalTaxIDNumber: "Federal Tax ID Number is required",
         sateTaxIDNumber: "Sate Tax ID Number is required",
         entityOrganization: "Entity Organization is required"*/
     }
  });
  
  function getItemPropDetails(archive_id,user_id) {
        if (archive_id != '') {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_archive_prop_details', archive_id: archive_id,user_id:user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.item_title) {
                        $('#society_name1').val(record.item_title);
                    }
                    if(record.user_properties.email){
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
                    
                    if (record.prop_details.physicalState !='') {
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
                    if (record.prop_details.mailingState !='') {
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
                    if (record.prop_details.CEO=="true" || record.prop_details.CEO_firstName !='') {
                        $('#check_CEO').show();
                        $('#CEO').prop("checked","true");
                        
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
                    
                    if (record.prop_details.executiveDirector=="true") {
                        $('#check_executiveDirector').show();
                        $('#executiveDirector').prop("checked","true");
                        
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
                    if (record.prop_details.precident=="true") {
                        $('#check_precident').show();
                        $('#precident').prop("checked","true");
                        
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
                    if (record.prop_details.otherExecutive=="true") {
                        $('#check_otherExecutive').show();
                        $('#otherExecutive').prop("checked","true");
                        
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
                    if (record.prop_details.committees=="true") {                        
                        $('#committees').prop("checked","true");
                    }
                    if (record.prop_details.boardOfDirectors=="true") {                        
                        $('#boardOfDirectors').prop("checked","true");
                    }
                    if (record.prop_details.sameAsPhysicalAddress=="true") {                        
                        //$('#sameAsPhysicalAddress').prop("checked","true");
                    }
                    if (record.prop_details.archive_user_id) {                        
                        $('#archive_user_id').val(record.prop_details.archive_user_id);
                    }
                    if (record.item_id) {                        
                        $('#item_id').val(record.item_id);
                    }
                    if (record.prop_details.society_state !='') {
                        setTimeout(function(){
                            $('#society_state').val(record.prop_details.society_state);
                        },1000);
                    }
                    if (record.prop_details.archive_user_id) {
                            $.ajax({
                              url: "services_admin_api.php",
                              type: "post",
                              data: {mode: 'get_user_by_id', user_id: record.prop_details.archive_user_id},
                              success: function (response) {
                                   var response = JSON.parse(response);
                                    $('#register_username1').val(response.user_login);
                                     if(response.user_login){
                                         $('#register_username1').val(response.user_login);                                                                                  
                                     }
                                     if(response.user_title){                                         
                                         var nameArray=response.user_title.split(" ");
                                         if(nameArray[0]){
                                                $('#title').val(nameArray[0]);
                                          }
                                          if(nameArray[1]){
                                                $('#firstName').val(nameArray[1]);
                                          }
                                          if(nameArray[2]){
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
                        //$('#term_service').attr('checked', true);
                    } 
                    
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 485)');
                }
            });
        }
    }
    
    //Call Initialy to load data
    getItemPropDetails(archive_id,user_id);
    
    
  function updateRegistrationDetails(){ 
            $('.admin-loading-image').show();
            var updateRegistrationData=$("#registrationOtherForm").serialize();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_archive_registration_details', formData: updateRegistrationData},
                success: function (response) {
                    var record = JSON.parse(response);
                     if (record.status == 'success') {
                         $('.alertMessage').css('display','block'); 
                         $('#success_update_message').html(record.message);
                        if(record.change_email_message !=''){
                         $('#change_email_message').html(record.change_email_message);   
                        }
//                        window.location.href = "manage_archive_registration.php?archive_id="+archive_id;
                         $('.admin-loading-image').hide();
                          $("html, body").animate({ scrollTop: 0 }, "slow");
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 486)');
                }
            });
  }
  
  $("#regOtherUpdate").on("click", function(){
      if ($("#registrationOtherForm").valid()) {
          updateRegistrationDetails();
      }
  });
     
});
function service_term_popup(){
	 $('#term_of_services').modal('show');
	 $('.admin-loading-image').show();
	 	$.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_term_and_condition', user_id: 1},
                success: function (data){
                     var result = JSON.parse(data);
                     if(result.status == 'success'){ 
						$('#get_term_cond_data').html(result.message); 
						$('.termsCondition').css('display','block');
                     }else{
						showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 487)');
                     }
                     $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 488)');
                }
            });
}
$(document).on('click','.closeAlertSection',function(){
  $('.alertMessage').css('display','none'); 
  window.location.href = "manage_archive_registration.php?archive_id="+archive_id;
})
function get_state(){
         $('.loading-div').show();
         var parent_id = '<?php echo STATE_PARENT_ID; ?>';
         $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_state_country', parent_id:parent_id},
                success: function (response) { 
		    var record = JSON.parse(response);
                    var i;
                    var society_state = "";
                    var physical_state = "";
                    var mailing_state = "";
                    society_state += "<option value='' >---Select---</option>";
                    physical_state += "<option value='' >---Select---</option>";
                    mailing_state += "<option value='' >---Select---</option>";
                    for (i = 0; i < record.length; i++) {
                        society_state += "<option value='" + record[i] + "' >" + record[i] + "</option>";
                        physical_state += "<option value='" + record[i] + "' >" + record[i] + "</option>";
                        mailing_state += "<option value='" + record[i] + "'   >" + record[i] + "</option>";     
                     }
                        $(".society_state_list").html(society_state);
                        $(".physical_state").html(physical_state);
                        $(".mailing_state").html(mailing_state);   
                      
                    $('.loading-div').hide();
                   
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 489)');
                    $('.loading-div').hide();
                }
         });
    }
</script>