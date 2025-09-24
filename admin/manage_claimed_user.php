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
?>
<style type="text/css">
.claimed_action{cursor:pointer; height:24px; margin:0 3px;}
.claimed_action_disabled{opacity: 0.3;cursor: not-allowed;}
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Manage Administrator</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Administrator</li>
        </ol>
        <h4 class="list_title text-center"> <span class="pull-left">Historical Society Claims</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="alert alert-dismissible" id="message"></div>
        <div class="row"  id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <div class="tableScroll">
                    <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                        <thead>  
                            <tr>  
                                <th width="15%" class="text-center">Society Name</th> 
                                <th width="15%" class="text-center">Name</th> 
                                <th width="20%" class="text-center">Email</th>  
                                <th width="15%" class="text-center">Phone</th>
                                <th width="10%" class="text-center">Email Verified</th>
                                <th width="10%" class="text-center">Date Claimed</th>
                                <th width="15%" class="text-center">Actions</th>
                            </tr>  
                        </thead>  
                        <tbody id="listdata">   </tbody>  
                    </table> 
                </div>
            </div>
        </div>
    </section>
</div>


<!--USER INFORMATION MODAL-->
<div class="modal fade bs-example-modal-lg" id="user_information" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body managedClaim">
                <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
                    <div class="container-fluid spanStyle">
                        <div class="col-md-12 text-center"><h3 class="bgNone marginBottom10">Historical Society Information</h3></div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Historical Society Name<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="society_name" readonly />
                            </div> 
                        </div>

                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>State<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="society_state" readonly />
                            </div> 
                        </div>

                        <div class="col-md-12 text-center"><h3 class="bgNone marginBottom10">Contact Information</h3></div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>First Name<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="firstName" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Last Name<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="lastName" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Title<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="title" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Username<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="register_username" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Email<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="register_emailId" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Phone Number<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" maxlength="14" class="form-control" id="phoneNumber" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Preferred Time Zone<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="preferred_time_zone" readonly />
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
                    <div class="container-fluid">  
                        <div class="col-md-12 text-center"><h3 class="bgNone marginBottom10">Location Information</h3></div>
                        <div class="row-fluid bgNone hedingSub">
                            <div class="col-md-12 text-center"><h3 class="bgNone">Physical Address</h3> </div> 
                        </div>

                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Address Line 1<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="physical form-control" id="physicalAddressLine1" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Address Line 2:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="physical form-control" id="physicalAddressLine2" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>City<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="physical form-control" id="physicalCity" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>State<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="physical form-control" id="physicalState" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Zip<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="physical form-control" id="physicalZip" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid bgNone hedingSub">
                            <div class="col-md-12 text-center"><h3 class="bgNone">Mailing Address</h3> </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Address Line 1<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="mailing form-control" id="mailingAddressLine1" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Address Line 2:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="mailing form-control" id="mailingAddressLine2" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>City<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="mailing form-control" id="mailingCity" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>State<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="mailing form-control" id="mailingState" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Zip<span>*</span>:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="mailing form-control" id="mailingZip" readonly />
                            </div> 
                        </div>

                        <div class="col-md-12 text-center"><h3 class="bgNone marginBottom10">Tax Information</h3></div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Federal Tax ID Number:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="federalTaxIDNumber" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>State Tax ID Number:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="sateTaxIDNumber" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Entity Organization:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="entityOrganization" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20" style="display:none" id="eoOther">
                            <div class="col-md-6" >
                                <label>Other:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="entityOrganizationOther" readonly />
                            </div> 
                        </div>

                        <div class="col-md-12 text-center"><h3 class="bgNone marginBottom10">Other Information</h3></div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Redactions Email Address:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="redactionsEmailAddress" readonly />
                            </div> 
                        </div>

                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Reprint Email Address:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="reprintEmailAddress" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Contact Email Address:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="contactEmailAddress" readonly />
                            </div> 
                        </div>
                        <div class="row-fluid marginTop20 padd5">
                            <div class="col-md-6" >
                                <label>Website URL:</label>
                            </div>
                            <div class="col-md-6" >
                                <input type="text" class="form-control" id="websiteURL" readonly />
                            </div> 
                        </div>
                        <div class="col-md-12 text-center"><h3 class="bgNone marginBottom10">Organization Structure</h3></div>
                        <?php foreach ($orgStructureArray as $osKey => $osValue) { ?>
                           <div class="row marginTop20 padd5">
                                <div class="col-md-12" >
                                    <input type="checkbox" class="orgStructure" id="<?php echo $osKey ?>" value="<?php echo $data[$osKey] ?>" disabled> <?php echo $osValue['title'] ?>
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
                                        <input type="text" class="form-control" id="<?php echo $osKey . "_firstName" ?>"  readonly />
                                    </div>       
                                    <div class="col-md-3" >
                                        <input type="text" class="form-control" id="<?php echo $osKey . "_lastName " ?>" readonly />
                                    </div>         
                                    <div class="col-md-3" >
                                        <input type="text" class="form-control" id="<?php echo $osKey . "_email" ?>" readonly />
                                    </div>         
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="clearfix marginTop20"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--USER INFORMATION MODAL END HERE-->

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dataTableDiv").hide();
        get_administrator_data();

        //Validate login form
        $("#administrator_form").validate({
            rules: {
                user_email: {
                    required: {
                        depends: function () {
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                confirm_user_password: {
                    equalTo: "#user_password"
                }
            },
            messages: {
                user_email: {
                    required: "Email Id is required",
                    email: "Please enter valid email Id"
                },
                confirm_user_password: {
                    equalTo: "New password and confirm password should be same."
                }
            }
        });
    });


    var table = $('#myTable').DataTable({"pageLength": 100,"sDom":'<"H"lfrp>t<"F"ip>'});
    function get_administrator_data() {
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';

        table.clear().draw();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'assistant_list', type: 'A', type2: 'claimed'},
            success: function (response) {
                var record = JSON.parse(response);
                var is_verified;
                var phone_number;
                var email_id;
                var action_buttons;
                var date_claimed;
                for (i = 0; i < record.length; i++) {
                    if (record[i].item_title == '' || !record[i].item_title) {
                        //continue;
                    }
                    action_buttons = '';
                    approve_display = ' style="display:none;" ';
                    reject_display = ' style="display:none;" ';
                    if (record[i]._properties.claimed_user_approved == 'N') {
                        reject_display = '';
                    } else if (record[i]._properties.claimed_user_approved == 'Y') {
                        approve_display = '';
                    }

                    if (record[i]._properties.email_verify && record[i]._properties.email_verify == 'yes') {
                        is_verified = 'Verified';
                    } else {
                        is_verified = 'Not Verified';
                    }
                    phone_number = '';
                    if (record[i]._properties.phone_no) {
                        phone_number = record[i]._properties.phone_no;
                        phone_number = decodeURIComponent(phone_number);
                        phone_number = phone_number.replace("+", " ");
                    }
                    email_id = '';
                    if (record[i]._properties.email) {
                        email_id = record[i]._properties.email;
                        email_id = decodeURIComponent(email_id);
                    }

                    action_buttons += '<span id="action_' + record[i].user_id + '">';
                    if (record[i]._properties.admin_action) {
                        if (record[i]._properties.claimed_user_approved && record[i]._properties.claimed_user_approved == 'Y') {
                            action_buttons += '<img class="claimed_action claimed_action_disabled" title="Approved" src="public/images/active.png" alt="Approved">';
							action_buttons += '<img class="claimed_action" title="Reject" src="public/images/deactive.png" alt="Reject" onclick="setUserReject(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ', this);">';
							action_buttons += '<img class="claimed_action" title="View" src="public/images/view.png" alt="View" onclick="viewUserInfo(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ');">';
							action_buttons += '<img class="claimed_action claimed_action_disabled" title="Delete" src="public/images/delete_icon.png" alt="Delete">';
                        } else if (record[i]._properties.claimed_user_approved && record[i]._properties.claimed_user_approved == 'N') {
                            action_buttons += '<img class="claimed_action" title="Approve" src="public/images/active.png" alt="Approve" onclick="setUserApprove(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ', this);">';
							action_buttons += '<img class="claimed_action claimed_action_disabled" title="Rejected" src="public/images/deactive.png" alt="Rejected">';
							action_buttons += '<img class="claimed_action" title="View" src="public/images/view.png" alt="View" onclick="viewUserInfo(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ');">';
							action_buttons += '<img class="claimed_action" title="Delete" src="public/images/delete_icon.png" alt="Delete" onclick="deleteUser(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ', this);">';
                        }
                    } else {
                        action_buttons += '<img class="claimed_action" title="Approve" src="public/images/active.png" alt="Approve" onclick="setUserApprove(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ', this);">';
                        action_buttons += '<img class="claimed_action" title="Reject" src="public/images/deactive.png" alt="Reject" onclick="setUserReject(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ', this);">';
                        action_buttons += '<img class="claimed_action" title="View" src="public/images/view.png" alt="View" onclick="viewUserInfo(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ');">';
						action_buttons += '<img class="claimed_action" title="Delete" src="public/images/delete_icon.png" alt="Delete" onclick="deleteUser(' + record[i].user_id + ',\'' + record[i].user_login + '\',' + record[i].item_id + ', this);">';
                    }
                    action_buttons += '</span>';
                    
                    if(record[i]._properties.timestamp){
                        var ds = new Date(record[i]._properties.timestamp * 1000);
                        date_claimed = (ds.getMonth()+1) + '-' + ds.getDate() + '-' + ds.getFullYear();
                    }

                    table.row.add([
                        record[i].item_title,
                        record[i].user_title,
                        email_id,
                        phone_number,
                        is_verified,
                        date_claimed,
                        action_buttons
                    ]).draw(false);
                    select: true
                }
                $('.admin-loading-image').hide();
                $("#dataTableDiv").show();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 494)');
            }
        });
    }
	
	function deleteUser(user_id, username, item_id, t){
		var html_action_buttons = '';
        if(confirm('Are you sure to delete this claimed request?')){
            $.ajax({
                type: 'post',
                url: '../services.php',
                data: {mode: 'delete_claimed_user_status', id: user_id, username: username, item_id: item_id, flg: 'Del'},
                success: function (response) {
					console.log(response);
                    var result = JSON.parse(response);
					if(result.status == 'OK'){
						$('.admin-loading-image').show();
						$('#message').removeClass('alert-warning');
						$('#message').removeClass('alert-danger');
						$('#message').addClass('alert-success');
						$('#message').html('<button type="button" class="close" data-dismiss="alert">&times;</button>User deleted successfully.');
						t.parentElement.parentElement.parentElement.remove();
						$('.admin-loading-image').hide();
					}else{
						$('.admin-loading-image').show();
						$('#message').removeClass('alert-success');
						$('#message').removeClass('alert-danger');
						$('#message').addClass('alert-warning');
						$('#message').html('<button type="button" class="close" data-dismiss="alert">&times;</button>User could not be deleted.');
						$('.admin-loading-image').hide();
					}
                }
            });
        }
	}

    function setUserApprove(user_id, username, item_id, t) {
		var html_action_buttons = '';
        if(confirm('Are you sure to approve this claimed request?')){
            $.ajax({
                type: 'post',
                url: '../services.php',
                data: {mode: 'claimed_user_status', id: user_id, username: username, item_id: item_id, flg: 'Y'},
                success: function (response) {
                    var result = JSON.parse(response);
					if(result.status == 'success'){
						var society_name_new = result.society_name_new;
						var span_id = $(t).parent().attr('id');
						var e = document.getElementById(span_id);
						e.parentElement.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.innerHTML = society_name_new;
						$('.admin-loading-image').show();
						$('#message').removeClass('alert-warning');
						$('#message').removeClass('alert-danger');
						$('#message').addClass('alert-success');
						$('#message').html('<button type="button" class="close" data-dismiss="alert">&times;</button>User approved successfully.');
						$('.admin-loading-image').hide();
						html_action_buttons += '<img class="claimed_action claimed_action_disabled" title="Approved" src="public/images/active.png" alt="Approved">';
						html_action_buttons += '<img class="claimed_action" title="Reject" src="public/images/deactive.png" alt="Reject" onclick="setUserReject(' + user_id + ',\'' + username + '\',' + item_id + ', this);">';
						html_action_buttons += '<img class="claimed_action" title="View" src="public/images/view.png" alt="View" onclick="viewUserInfo(' + user_id + ',\'' + username + '\',' + item_id + ');">';
						html_action_buttons += '<img class="claimed_action" title="Delete" src="public/images/delete_icon.png" alt="Delete">';
						$('#action_' + user_id).html(html_action_buttons);
					}else{
						$('.admin-loading-image').show();
						$('#message').removeClass('alert-success');
						$('#message').removeClass('alert-danger');
						$('#message').addClass('alert-warning');
						$('#message').html('<button type="button" class="close" data-dismiss="alert">&times;</button>'+result.message);
						$('.admin-loading-image').hide();
					}
                }
            });
        }
    }

    function setUserReject(user_id, username, item_id, t) {
		var html_action_buttons = '';
        if(confirm('Are you sure to reject this claimed request?')){
            $.ajax({
                type: 'post',
                url: '../services.php',
                data: {mode: 'claimed_user_status', id: user_id, username: username, item_id: item_id, flg: 'N'},
                success: function (response) {
                    var result = JSON.parse(response);
					var society_name_old = result.society_name_old;
					var span_id = $(t).parent().attr('id');
					var e = document.getElementById(span_id);
					e.parentElement.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.innerHTML = society_name_old;
                    $('.admin-loading-image').show();
					$('#message').removeClass('alert-success');
					$('#message').removeClass('alert-warning');
                    $('#message').addClass('alert-danger');
                    $('#message').html('<button type="button" class="close" data-dismiss="alert">&times;</button>User rejected successfully.');
                    $('.admin-loading-image').hide();
					html_action_buttons += '<img class="claimed_action" title="Approve" src="public/images/active.png" alt="Approve" onclick="setUserApprove(' + user_id + ',\'' + username + '\',' + item_id + ', this);">';
					html_action_buttons += '<img class="claimed_action claimed_action_disabled" title="Rejected" src="public/images/deactive.png" alt="Rejected">';
					html_action_buttons += '<img class="claimed_action" title="View" src="public/images/view.png" alt="View" onclick="viewUserInfo(' + user_id + ',\'' + username + '\',' + item_id + ');">';
					html_action_buttons += '<img class="claimed_action" title="Delete" src="public/images/delete_icon.png" alt="Delete" onclick="deleteUser(' + user_id + ',\'' + username + '\',' + item_id + ', this);">';
                    $('#action_' + user_id).html(html_action_buttons);
                }
            });
        }
    }

    function viewUserInfo(user_id, username, item_id) {
        var archive_id = item_id;
        var user_id = '1';
        if (archive_id) {
            $('.admin-loading-image').show();
            $.ajax({
                type: 'post',
                url: 'services_admin_api.php',
                data: {mode: 'get_temp_claim_data', id: item_id, username: username},
                success: function (response) {
                    var result = JSON.parse(response);
                    var result_length = result.length;
                    if (result_length) {
                        for (var i = 0; i < result_length; i++) {
                            $('#' + result[i].name).val(result[i].value);
                            if ($('#' + result[i].name).is(':checkbox') && result[i].value == 'true') {
                                $('#' + result[i].name).attr('checked', result[i].value)
                            }
                        }
                        $('.admin-loading-image').hide();
                        $('#user_information').modal('show');
                    }
                }
            });
            $('.admin-loading-image').hide();
            //getItemPropDetails(archive_id, user_id);
        }
    }

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
                    $('#user_information').modal('show');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 495)');
                }
            });
        }
    }
</script>