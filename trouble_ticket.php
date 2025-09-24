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
    <section class="content-header"> 
        <h1>Create Trouble Ticket</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Create Trouble Ticket</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Create Trouble Ticket</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="create_trouble_ticket_form" name="create_trouble_ticket_form">
                    <div class="row">
                        <div class="col-md-5 "><strong>Name :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="name"  name="name" value="<?php echo $_SESSION['aib']['user_data']['user_title']; ?>" readonly="readonly"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 "><strong>Email :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="trouble_email"  name="trouble_email" value="<?php echo urldecode($_SESSION['aib']['user_data']['properties']['email']); ?>" readonly="readonly"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 "><strong>Your Computer :</strong></div>
                        <div class="col-md-7">
                            <select name="your_computer" id="your_computer" class="form-control">
                                <option value=""> Select your computer </option>
                                <option value="PC">PC</option>
                                <option value="Macintosh">Macintosh</option>
                                <option value="iPad">iPad</option>
                                <option value="Android Tablet">Android Tablet</option>
                                <option value="iPhone">iPhone</option>
                                <option value="Android Phone">Android Phone</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 "><strong>Browser Type :</strong></div>
                        <div class="col-md-7">
                            <select name="browser_type" id="browser_type" class="form-control">
                                <option value="">Select you browser</option>
                                <option value="Chrome">Chrome</option>
                                <option value="Firefox">Firefox</option>
                                <option value="Internet Explorer">Internet Explorer</option>
                                <option value="Safari">Safari</option>
                                <option value="Edge">Edge</option>
                                <option value="Opera">Opera</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 "><strong>Describe Your Issue :</strong></div>
                        <div class="col-md-7">
                            <textarea name="your_message" id="your_message" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-info borderRadiusNone" id="submit_trouble_ticket" name="submit_trouble_ticket">Submit Ticket</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#create_trouble_ticket_form').validate({
            rules: {
                name: {
                    required: true
                },
                trouble_email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },  
                    email: true
                },
                your_computer: {
                    required: true
                },
                browser_type: {
                    required: true
                },
                your_message: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Name is required."
                },
                trouble_email: {
                    required: "Please enter email Id",
                    email: "Please enter valid email Id"
                },
                your_computer: {
                    required: "Please select your computer type."
                },
                browser_type: {
                    required: "Please select your browser type."
                },
                your_message: {
                    required: "Please describe your issue."
                }
            }
        });
    });
    $(document).on('click', '#submit_trouble_ticket', function(){
        if($("#create_trouble_ticket_form").valid()){
            var formData = $('#create_trouble_ticket_form').serialize();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'submit_society_trouble_request',formData: formData},
                success: function (response) {
                    var result = JSON.parse(response);
                    if(result.status == 'success'){
                        $('.admin-loading-image').hide();
                        $('#create_trouble_ticket_form')[0].reset();
                        showPopupMessage('success', result.message);
                    }else{
                        showPopupMessage('error', result.message + ' (Error Code: 321)');
                    }
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 322)');
                }
            });
        }
    });
</script>