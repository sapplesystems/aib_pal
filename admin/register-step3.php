<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';

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
$data='';
if(!empty($_SESSION['data1']) && !empty($_SESSION['data2'])){
  $data = array_merge($_SESSION['data1'],$_SESSION['data2']);  
}else{
    echo "<script>window.location.href='coming-soon.php'</script>";
    exit;
}
error_reporting(E_ALL & ~E_NOTICE);
?>
<style>
    mark{background-color: #fbd42f !important;}
</style>

<div class="header_img">
    <div class="bannerImage"></div>
</div>
<div class="clearfix"></div>

<form name="finalRegistrationForm" id="finalRegistrationForm" method="post" action="">
    <input type="hidden" name="user_type" value="A">
    <input type="hidden" name="register_user_password" value="test">
<div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
    <div class="container">
        <div class="row marginTop20 bgNone"><h3>Historical Society Information <span class="pull-right" id="reg_edit"><a class="btn btn-success marginTop4" href="javascript:void(0);">Edit</a></span></h3></h3></div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Historical Society Name<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="society_name" id="society_name1" value="<?php if(!empty($data)){echo $data['society_name'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>

        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>State<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="society_state" id="society_state" value="<?php if(!empty($data)){echo $data['society_state'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        <div class="row bgNone"><h3>Contact Information</h3></div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>First Name<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="firstName" id="firstName" value="<?php if(!empty($data)){echo $data['firstName'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Last Name<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="lastName" id="lastName" value="<?php if(!empty($data)){echo $data['lastName'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
         <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Title<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="title" id="title" value="<?php if(!empty($data)){echo $data['title'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
         <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Username<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="register_username" id="register_username1" value="<?php if(!empty($data)){echo $data['register_username'];} ?>" placeholder="Enter text" readonly />
            </div> 
         </div>
         <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Email<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="register_emailId" id="register_emailId" value="<?php if(!empty($data)){echo $data['register_emailId'];} ?>" placeholder="Enter text" readonly />
            </div> 
         </div>
         <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Phone Number<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" maxlength="14" class="form-control" name="phoneNumber" id="phoneNumber" value="<?php if(!empty($data)){echo $data['phoneNumber'];} ?>" placeholder="Enter text" readonly />
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
        <div class="row marginTop20" id="display_message" style="display:none">
            <div class="col-md-3" style="color:green">
                Your profile has been created successfully.Please wait for administrator response.
            </div>
        </div>
        
        
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
                <input type="text" class="physical form-control" name="physicalAddressLine1" id="physicalAddressLine1" value="<?php if(!empty($data)){echo $data['physicalAddressLine1'];} ?>" placeholder="Enter text" readonly />
            </div> 
            
            <div class="col-md-3" >
                <label>Address Line 1<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="mailing form-control" name="mailingAddressLine1" id="mailingAddressLine1" value="<?php if(!empty($data)){echo $data['mailingAddressLine1'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Address Line 2:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="physical form-control" name="physicalAddressLine2" id="physicalAddressLine2" value="<?php if(!empty($data)){echo $data['physicalAddressLine2'];} ?>" placeholder="Enter text" readonly />
            </div> 
            
            <div class="col-md-3" >
                <label>Address Line 2:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="mailing form-control" name="mailingAddressLine2" id="mailingAddressLine2" value="<?php if(!empty($data)){echo $data['mailingAddressLine2'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>City<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="physical form-control" name="physicalCity" id="physicalCity" value="<?php if(!empty($data)){echo $data['physicalCity'];} ?>" placeholder="Enter text" readonly />
            </div> 
            
            <div class="col-md-3" >
                <label>City<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="mailing form-control" name="mailingCity" id="mailingCity" value="<?php if(!empty($data)){echo $data['mailingCity'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>State<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="physical form-control" name="physicalState" id="physicalState" value="<?php if(!empty($data)){echo $data['physicalState'];} ?>" placeholder="Enter text" readonly />
            </div> 
            
            <div class="col-md-3" >
                <label>State<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="mailing form-control" name="mailingState" id="mailingState" value="<?php if(!empty($data)){echo $data['mailingState'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Zip<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="physical form-control" name="physicalZip" id="physicalZip" value="<?php if(!empty($data)){echo $data['physicalZip'];} ?>" placeholder="Enter text" readonly />
            </div> 
            
            <div class="col-md-3" >
                <label>Zip<span>*</span>:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="mailing form-control" name="mailingZip" id="mailingZip" value="<?php if(!empty($data)){echo $data['mailingZip'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        <div class="row marginTop20 bgNone"><h3>Tax Information</h3></div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Federal Tax ID Number:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="federalTaxIDNumber" id="federalTaxIDNumber" value="<?php if(!empty($data)){echo $data['federalTaxIDNumber'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>State Tax ID Number:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="sateTaxIDNumber" id="sateTaxIDNumber" value="<?php if(!empty($data)){echo $data['sateTaxIDNumber'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Entity Organization:</label>
            </div>
            <div class="col-md-3" >
                 <input type="text" class="form-control" name="entityOrganization" id="entityOrganization" value="<?php if(!empty($data)){echo $data['entityOrganization'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20" style="display:none" id="eoOther">
            <div class="col-md-3" >
                <label>Other:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="entityOrganization" id="entityOrganizationOther" value="<?php if(!empty($data)){echo $data['entityOrganization'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        <div class="row marginTop20 bgNone"><h3>Other Information</h3></div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Redactions Email Address:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="redactionsEmailAddress" id="redactionsEmailAddress" value="<?php if(!empty($data)){echo $data['redactionsEmailAddress'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Reprint Email Address:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="reprintEmailAddress" id="reprintEmailAddress" value="<?php if(!empty($data)){echo $data['reprintEmailAddress'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Contact Email Address:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="contactEmailAddress" id="contactEmailAddress" value="<?php if(!empty($data)){echo $data['contactEmailAddress'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 padd5">
            <div class="col-md-3" >
                <label>Website URL:</label>
            </div>
            <div class="col-md-3" >
                <input type="text" class="form-control" name="websiteURL" id="websiteURL" value="<?php if(!empty($data)){echo $data['websiteURL'];} ?>" placeholder="Enter text" readonly />
            </div> 
        </div>
        <div class="row marginTop20 bgNone"><h3>Organization Structure</h3></div>
        <div class="row marginTop20 padd5 paddLeft15"><strong>Select all that apply</strong></div>
        <?php foreach($orgStructureArray as $osKey=>$osValue){?>
        <div class="row marginTop20 padd5">
            <div class="col-md-12" >
                <input type="checkbox" class="orgStructure" name="<?php echo $osKey?>" id="<?php echo $osKey?>" value="<?php echo $data[$osKey]?>" disabled="" <?php if($data[$osKey] == 'true'){echo 'checked';} ?>> <?php echo $osValue['title']?>
            </div>            
        </div>
            <?php if($osValue['field']==true){ ?>
                <div class="row padd5" style="display: <?php if($data[$osKey] == 'true'){echo 'block';}else{echo 'none';} ?>;" id="<?php echo "check_".$osKey;?>" >
                    <div class="col-md-3" >
                      <input type="text" class="form-control" name="<?php echo $osKey."_firstName"?>" id="<?php echo $osKey."_firstName"?>" value="<?php echo $data[$osKey."_firstName"]?>" placeholder="Enter First Name" readonly />
                    </div>       
                    <div class="col-md-3" >
                      <input type="text" class="form-control" name="<?php echo $osKey."_lastName" ?>" id="<?php echo $osKey."_lastName "?>" value="<?php echo $data[$osKey."_lastName"]?>" placeholder="Enter Last Name" readonly />
                    </div>         
                    <div class="col-md-3" >
                      <input type="text" class="form-control" name="<?php echo $osKey."_email"?>" id="<?php echo $osKey."_email"?>" value="<?php echo $data[$osKey."_email"]?>" placeholder="Enter Email Address" readonly />
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
                            I agree to the <a href="javascript:service_term_popup();">Terms of service</a>
                        </label>
                    </div>
                </div> 
            </div>
        <div class="row marginTop20 bgNone">  
        <div class="col-md-5"></div>         
            <div class="col-md-2" >
                <input type="button" class="form-control btn-success" name="finalRegSubmit" id="finalRegSubmit" value="Submit" >
            </div> 
         </div>
        <div class="clearfix marginTop20"></div>
    </div>
</div>
</form>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH.'jquery.mark.min.js'; ?>"></script>
<script type="text/javascript">
      
       $("#finalRegSubmit").on("click",function(){
            $("#finalRegistrationForm").validate({
           rules: {                
               term_service: "required"
           },
           messages: {                
               required: "Please accept terms of service"
           }
        });
         if($("#finalRegistrationForm").valid()==true){
           var dataVal = $("#finalRegistrationForm").serialize();
           $('.loading-div').show();
           $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'register_new_user', formData: dataVal},
                success: function (response) { 
                    $('.loading-div').hide();
                    $("#display_message").show();
                    document.body.scrollTop = 0;
                    window.location.href = 'thank-you.php?flg=archive_user_reg';
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 618)');
                    $('.loading-div').hide();
                }
         }); 
         }
       });
   $(document).on('click','#reg_edit',function(){
       $('.loading-div').show(); 
      window.location.href='register.php'; 
   });
   
function service_term_popup(){
	 $('#term_of_services').modal('show');
	 $('.loading-div').show();
	 	$.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_term_and_condition', user_id: 1},
                success: function (data){
                     var result = JSON.parse(data);
                     if(result.status == 'success'){ 
						$('#get_term_cond_data').html(result.message); 
                     }else{
						showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 619)');
                     }
                     $('.loading-div').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 620)');
                }
            });
}
   
</script>