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
        <h4 class="list_title">Add DMCA Counter Notice</h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-12">
			 <div class="pull-right" style="margin-bottom:10px">
				<button type="button" onclick="PrintTerm();" class="btn btn-primary borderRadiusNone">Print</button> 
			 </div>
			 <div class="clearfix"></div>
                <form class="marginBottom30 formStyle form-group" action="" method="POST" id="dmca_ciunter_notice_form" name="dmca_ciunter_notice_form">
                       <input type="hidden" name="timestamp" id="timestamp" value="<?php echo time(); ?>">
                    <div class="row">
                        <div class="col-md-2 text-right"><strong>DMCA Counter Notice:</strong></div>
                        <div class="col-md-10">
							 <textarea class="form-control" rows="20" id="dmca_ciunter_notice" name="dmca_ciunter_notice"></textarea>
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
        selector: '#dmca_ciunter_notice',
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
    data: {mode: 'get_term_and_condition', user_id: 1,form_type : 'DMCA_COUNTER'},
    success: function (data){
         var result = JSON.parse(data);
         if(result.status == 'success'){ 
             setTimeout(function(){ 
                tinymce.get("dmca_ciunter_notice").setContent(result.message); 
                $('.admin-loading-image').hide();
            }, 3000); 
         }else{
            showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 450)');
         }
    },
    error: function () {
        showPopupMessage('error','Something went wrong, Please try again. (Error Code: 451)');
    }
    });
	
	
    $('#addtermCondButton').click(function(){ 
        if($("#dmca_ciunter_notice_form").valid()){ 
            //var termForm = $("#dmca_ciunter_notice_form").serialize();
            var terms_conditions = tinymce.get("dmca_ciunter_notice").getContent();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'term_condition_add_form', terms_conditions: terms_conditions ,form_type : 'DMCA_COUNTER'},
                success: function (data){
                     var result = JSON.parse(data);
                     if(result.status == 'success'){ 
                        showPopupMessage('success', result.message);
                     }else{
						showPopupMessage('error', result.message + ' (Error Code: 452)');
                     }
                     $('.admin-loading-image').hide();
                      location.reload();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 453)');
                }
            });
			}
    }); 
 
    $("#dmca_ciunter_notice_form").validate({
        rules: {
            dmca_ciunter_notice:{
                required: true 
            }           
        },
        messages: { 
            dmca_ciunter_notice:{
                required: "Please enter Term & Condition"
            }  
        }
    });
});     
function PrintTerm(elem)
{	
	var elem = tinymce.get("dmca_ciunter_notice").getContent();
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');

    mywindow.document.write('<html><head><title> DMCA Counter Notice</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>DMCA Counter Notice</h1>');
    mywindow.document.write(elem);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
} 
</script>