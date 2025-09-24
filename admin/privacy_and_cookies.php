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
        <h4 class="list_title">Add Privacy and Cookies</h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-12">
			 <div class="pull-right" style="margin-bottom:10px">
				<button type="button" onclick="PrintPrivacy();" class="btn btn-primary borderRadiusNone">Print</button> 
			 </div>
			 <div class="clearfix"></div>
                <form class="marginBottom30 formStyle form-group" action="" method="POST" id="privacy_cookies_form" name="privacy_cookies_form">
                       <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
                    <div class="row">
                        <div class="col-md-2 text-right"><strong>Privacy and Cookies :</strong></div>
                        <div class="col-md-10">
							 <textarea class="form-control" rows="20" id="privacy_cookies" name="privacy_cookies"></textarea>
							<!--<input type="text" class="form-control"  id="login_data"  name="login_data" placeholder="Login username">-->
						</div>
                    </div> 
					<?php  if($loginUserType == 'R'){ ?>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7"><button type="button" class="btn btn-info borderRadiusNone" id="addtermCondButton">Submit</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm">Clear Form</button></div>
                    </div>
					<?php } ?>
                </form>
            </div>

        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH.'tinymce/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('.admin-loading-image').show();
    tinymce.init({
        selector: '#privacy_cookies',
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
        data: {mode: 'get_term_and_condition', user_id: 1,form_type : 'PC'},
        success: function (data){
             var result = JSON.parse(data);
             if(result.status == 'success'){ 
                 setTimeout(function(){ 
                   tinymce.get("privacy_cookies").setContent(result.message);  
                   $('.admin-loading-image').hide();
                }, 3000); 
             }else{
                showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 594)');
             }
        },
        error: function () {
            showPopupMessage('error','Something went wrong, Please try again. (Error Code: 595)');
        }
    });
	
	
    $('#addtermCondButton').click(function(){ 
        if($("#privacy_cookies_form").valid()){ 
            //var termForm = $("#privacy_cookies_form").serialize();
            var terms_conditions = tinymce.get("privacy_cookies").getContent();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'term_condition_add_form', terms_conditions: terms_conditions ,form_type : 'PC'},
                success: function (data){
                     var result = JSON.parse(data);
                     if(result.status == 'success'){ 
                        showPopupMessage('success', result.message);
                     }else{
						showPopupMessage('error', result.message + ' (Error Code: 596)');
                     }
                     $('.admin-loading-image').hide();
                      location.reload();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 597)');
                }
            });
			}
    }); 
 
    $("#privacy_cookies_form").validate({
        rules: {
            privacy_cookies:{
                required: true 
            }           
        },
        messages: { 
            privacy_cookies:{
                required: "Please enter Term & Condition"
            }  
        }
    });
});     
function PrintPrivacy(elem)
{	 
	var elem = tinymce.get("privacy_cookies").getContent();
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');

    mywindow.document.write('<html><head><title> Privacy & Cookies</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>Privacy & Cookies</h1>');
    mywindow.document.write(elem);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
} 
</script>