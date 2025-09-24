<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$userId = '';
if(isset($_SESSION['aib']['user_data']['user_id']) && !empty($_SESSION['aib']['user_data']['user_id'])){
   $userId = $_SESSION['aib']['user_data']['user_id'];
}
?> 
<div class="content-wrapper">
    <!--<section class="content-header"> 
        <h1>Profile</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Profiles</li>
        </ol>
        <!--<h4 class="list_title text-center"><span class="pull-left">Society Name</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>-->
    <!--</section>-->
    <section class="content bgTexture bgCreateSociety">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="container-fluid">
		<div class="row">
			
		</div>
                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="profileForm" name="profileForm">
                    
					<div class="createSocietyForm">
					<h3>Create Society</h3>
					<div class="form-group">
						<label for="exampleInputEmail1">Society Name</label>
						<input type="text" class="form-control" id="" placeholder="">
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">City</label>
						<input type="text" class="form-control" id="" placeholder="">
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">State</label>
						<select class="form-control">
						  <option>1</option>
						  <option>2</option>
						  <option>3</option>
						  <option>4</option>
						  <option>5</option>
						</select>
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">Logo</label>
						<input type="file" class="form-control">
					</div>
					<div class="row">
                        <div class="col-md-12 text-center"><button type="button" class="BtnCreateSocietyY" id="" name="Create Society">Create</button> &nbsp;
                            <button type="button" class="btnCreateSocietyN clearAdminForm">Clear Form</button></div>
                    </div>
			</div>
                  
                    
                     
                    
                    <div class="row bgNone termsCondition" style="display:none">
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
                    
                    
                </form>
            </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script>

    $(document).ready(function () {
        var user_id = '<?php echo $userId; ?>';
        getPropData(user_id);
        $('#UpdateProfileButton').click(function () {
            if ($("#profileForm").valid()) {
                 $('.admin-loading-image').show();
                var profileFormData = $("#profileForm").serialize();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'update_profile', formData: profileFormData},
                    success: function (data) {
                        var result = JSON.parse(data);
                        if (result.status == 'success') {
                            showPopupMessage(result.status, result.message);
                        }else { 
                            showPopupMessage('error', result.message + ' (Error Code: 371)');
                        }
                        $('.admin-loading-image').hide();
                    },
                    error: function () { 
			            showPopupMessage('error','Something went wrong, Please try again. (Error Code: 372)');
                    }
                });
            }
        });
        //Validate login form
        $("#profileForm").validate({
            rules: {
                profile_name: {
                    required: true
                },
                profile_paswd: {
                    required: true
                },
                profile_cnfrm_paswd: {
                    equalTo: "#profile_paswd"
                },
                 term_service: {
                    required: true
                }
            },
            messages: {
                profile_name: {
                    required: "Please enter Name"
                },
                 profile_paswd: {
                    required: "Please enter password"
                },
                profile_cnfrm_paswd: {
                    equalTo: "Your Password does not Match"
                },
                term_service: {
                    required: "Please accept terms of service"
                }

            }
        });
        function getPropData(user_id){
             $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_archive_prop_data',user_id:user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.occasional_update == 'Y') {
                        $('#occasional_update').attr('checked', true);
                    }
                    if (record.term_service == 'Y') {
                        $('#term_service').attr('checked', true);
                    } 
                    
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 373)');
                }
            });
            
        }
    });
</script>