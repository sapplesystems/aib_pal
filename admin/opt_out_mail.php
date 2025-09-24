<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$loginUserType = $_SESSION['aib']['user_data']['user_type'];
?> 
<div class="content-wrapper">
    <section class="content-header"> 
        <h4 class="list_title">Manage Mail Opt-Out</h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-12">
                <table class="table" cellspacing="0" cellpadding="0" width="100%" border="0">
                    <tr>
                        <td width="30%">
                            <input type="checkbox" class="mail_opt_out" id="Registred_Society" />
                            <span>Registered Society</span>
                        </td>
                        <td width="70%">
                            <input type="checkbox" class="mail_opt_out" id="Society_Approved" />
                            <span>Society Approved</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="Email_Changed" />
                            <span>Email Changed</span>
                        </td>
                        <!--td>
                            <input type="checkbox" class="mail_opt_out" id="Forgot_Password" />
                            <span>Forgot Password</span>
                        </td-->
                        <td colspan="2">
                            <input type="checkbox" class="mail_opt_out" id="Change_Password" />
                            <span>Change Password</span>
                        </td>
                    </tr>
                    <tr>
                        <!--td>
                            <input type="checkbox" class="mail_opt_out" id="Public_User_Registration" />
                            <span>Public User Registration</span>
                        </td>
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="User_Approved" />
                            <span>User Approved</span>
                        </td-->
                        <!--td>
                            <input type="checkbox" class="mail_opt_out" id="Update_Profile" />
                            <span>Update Profile</span>
                        </td-->
                        
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="User_Welcome" />
                            <span>User Welcome</span>
                        </td>
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="Contact_Us" />
                            <span>Contact Us</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="Report_Content" />
                            <span>Report Content</span>
                        </td>
                        <td colspan="2">
                            <input type="checkbox" class="mail_opt_out" id="Content_Removal_Request" />
                            <span>Content Removal Request</span>
                        </td>
                        <!--td>
                            <input type="checkbox" class="mail_opt_out" id="Item_Report_To_User" />
                            <span>Item Report to User</span>
                        </td-->
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="Trouble_Ticket" />
                            <span>Trouble Ticket</span>
                        </td>
                        <td>
                            <input type="checkbox" class="mail_opt_out" id="Reprint_Request" />
                            <span>Reprint Request</span>
                        </td>
                        <!--td>
                            <input type="checkbox" class="mail_opt_out" id="Comment_Report" />
                            <span>Comment Report</span>
                        </td-->
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="checkbox" class="mail_opt_out" id="Share_Content" />
                            <span>Share Content</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'get_mail_opt_out'},
            success: function (data) {
                var result = JSON.parse(data);
                if (result.status == 'OK') {
                    var record = result.info.records;
                    if(record.Registred_Society == '1'){ $('#Registred_Society').prop('checked', true); }
                    if(record.Society_Approved == '1'){ $('#Society_Approved').prop('checked', true); }
                    if(record.User_Welcome == '1'){ $('#User_Welcome').prop('checked', true); }
                    if(record.Email_Changed == '1'){ $('#Email_Changed').prop('checked', true); }
                    /*if(record.Forgot_Password == '1'){ $('#Forgot_Password').prop('checked', true); }*/
                    if(record.Change_Password == '1'){ $('#Change_Password').prop('checked', true); }
                    /*if(record.Public_User_Registration == '1'){ $('#Public_User_Registration').prop('checked', true); }
                    if(record.User_Approved == '1'){ $('#User_Approved').prop('checked', true); }*/
                    /*if(record.Update_Profile == '1'){ $('#Update_Profile').prop('checked', true); }*/
                    if(record.Share_Content == '1'){ $('#Share_Content').prop('checked', true); }
                    if(record.Contact_Us == '1'){ $('#Contact_Us').prop('checked', true); }
                    if(record.Report_Content == '1'){ $('#Report_Content').prop('checked', true); }
                    if(record.Content_Removal_Request == '1'){ $('#Content_Removal_Request').prop('checked', true); }
                    /*if(record.Item_Report_To_User == '1'){ $('#Item_Report_To_User').prop('checked', true); }*/
                    if(record.Trouble_Ticket == '1'){ $('#Trouble_Ticket').prop('checked', true); }
                    if(record.Reprint_Request == '1'){ $('#Reprint_Request').prop('checked', true); }
                    /*if(record.Comment_Report == '1'){ $('#Comment_Report').prop('checked', true); }*/
                } else {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 582)');
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 583)');
                $('.admin-loading-image').hide();
            }
        });

        $('.mail_opt_out').click(function () {
            $('.admin-loading-image').show();
            var checkbox_name = $(this).attr('id');
            var is_checked = '0';
            if($(this).is(':checked')){
                is_checked = '1';
            }
            $('.admin-loading-image').hide();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'set_mail_opt_out', checkbox_name: checkbox_name, is_checked: is_checked},
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.status == 'OK') {
                        showPopupMessage('success', 'Status changed successfully.');
                    } else {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 584)');
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 585)');
                    $('.admin-loading-image').hide();
                }
            });
        });
    });
</script>