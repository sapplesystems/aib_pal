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
        <h4 class="list_title">Add Claimed Popup Message </h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-12">
                <div class="pull-right" style="margin-bottom:10px">
                    <button type="button" onclick="PrintTerm();" class="btn btn-primary borderRadiusNone">Print</button> 
                </div>
                <div class="clearfix"></div>
                <form class="marginBottom30 formStyle form-group" action="" method="POST" id="claimed_message_frm" name="claimed_message_frm">
                    <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
                    <div class="row">
                        <div class="col-md-2 text-right"><strong>Claimed Popup Message :</strong></div>
                        <div class="col-md-10">
                            <textarea class="form-control" rows="20" id="claimed_message" name="claimed_message"></textarea>
                           <!--<input type="text" class="form-control"  id="login_data"  name="login_data" placeholder="Login username">-->
                        </div>
                    </div> 
                    <?php if ($loginUserType == 'R') { ?>
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-7">
                                <button type="button" class="btn btn-info borderRadiusNone" id="claimed_message_frm_sub">Submit</button> &nbsp;
                                <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm">Clear Form</button>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </div>

        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH . 'tinymce/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
                        $(document).ready(function () {
                            $('.admin-loading-image').show();
                            tinymce.init({
                                selector: '#claimed_message',
                                height: 300,
                                branding: false,
                                theme: 'modern',
                                plugins: 'image link media template codesample table charmap hr pagebreak nonbreaking anchor textcolor wordcount imagetools contextmenu colorpicker',
                                toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | fontsizeselect',
                                image_advtab: true
                            });
                            $.ajax({
                                url: "services_admin_api.php",
                                type: "post",
                                data: {mode: 'get_claimed_popup_message', user_id: 1, type: 'CM'},
                                success: function (data) {
                                    var result = JSON.parse(data);
                                    if (result.status == 'success') {
                                        setTimeout(function () {
                                            tinymce.get("claimed_message").setContent(result.message);
                                            $('.admin-loading-image').hide();
                                        }, 3000);
                                    } else {
                                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 360)');
										$('.admin-loading-image').hide();
                                    }
                                },
                                error: function () {
                                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 361)');
									$('.admin-loading-image').hide();
                                }
                            });


                            $('#claimed_message_frm_sub').click(function () {
                                if ($("#claimed_message_frm").valid()) {
                                    //var termForm = $("#claimed_message_frm").serialize();
                                    var claimed_message = tinymce.get("claimed_message").getContent();
                                    $('.admin-loading-image').show();
                                    $.ajax({
                                        url: "services_admin_api.php",
                                        type: "post",
                                        data: {mode: 'set_claimed_popup_message', claimed_message: claimed_message},
                                        success: function (data) {
                                            var result = JSON.parse(data);
                                            if (result.status == 'success') {
                                                showPopupMessage('success', result.message);
                                            } else {
                                                showPopupMessage('error', result.message + ' (Error Code: 362)');
                                            }
                                            $('.admin-loading-image').hide();
                                            location.reload();
                                        },
                                        error: function () {
                                            showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 363)');
                                        }
                                    });
                                }
                            });

                            $("#claimed_message_frm").validate({
                                rules: {
                                    claimed_message: {
                                        required: true
                                    }
                                },
                                messages: {
                                    claimed_message: {
                                        required: "Please enter claimed message"
                                    }
                                }
                            });
                        });
                        function PrintTerm(elem)
                        {
                            var elem = tinymce.get("claimed_message").getContent();
                            var mywindow = window.open('', 'PRINT', 'height=400,width=600');

                            mywindow.document.write('<html><head><title> Claimed Popup Message</title>');
                            mywindow.document.write('</head><body >');
                            mywindow.document.write('<h1>Claimed Popup Message</h1>');
                            mywindow.document.write(elem);
                            mywindow.document.write('</body></html>');

                            mywindow.document.close(); // necessary for IE >= 10
                            mywindow.focus(); // necessary for IE >= 10*/

                            mywindow.print();
                            mywindow.close();

                            return true;
                        }
</script>