<div class="jquery-popup-overwrite">
    <div class="errorMessage" id="">
        <div class="bgAlert"></div>
        <h3>Error!</h3>
        <p id="error_message"></p>
        <a class="dis dismiss-popup" href="javascript:void(0);">Ok</a>
    </div>
    <div class="successMessage" id="">
        <div class="bgSuccess"></div>
        <h3>Success!</h3>
        <p id="success_message"></p>
        <a class="suc dismiss-popup" href="javascript:void(0);">Ok</a>
    </div>
</div>

<div class="modal fade" id="ocr_perform_folder" role="dialog" > 
    <div class="modal-dialog widthFullModal">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Optical Character Recognition (OCR)</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body" id="movefolderformdiv"> 
			   <form id="ocr_store_form" name="ocr_store_form" method="POST" class="form-horizontal" action="">
                     <input type="hidden" name="object_id" id="object_id" value=""> 
                    <div class="form-group">
                        <label class="col-xs-3 control-label">OCR Value</label>
                        <div class="col-xs-7">
                            <textarea rows="5" class="form-control" name="ocr_value" id="ocr_value"></textarea>
                        </div>
                    </div> 
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="ocr_submit_button" id="ocr_submit_button">Submit</button>
                            <button type="button" class="btn btn-info borderRadiusNone" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
	   
        //Validate login form
        $("#ocr_store_form").validate({
            rules: {
                ocr_value: "required" 
            },
            messages: {
                ocr_value: "OCR Value is required" 
            }
        }); 
 
    });
	
 
function service_term_popup(){
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
		     showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 875)');
                     }
                     $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 876)');
                }
            });
}
 
function PrintElem(elem)
{
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');

    mywindow.document.write('<html><head><title> Terms of Service</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>Terms of Service</h1>');
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
} 
$(document).on('click', '#ocr_submit_button', function () {
		var ocrformData = $("#ocr_store_form").serialize(); 
		if($("#ocr_store_form").valid()){
			 $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'store_ocr_value_field', formData: ocrformData},
                success: function (response) {
                    var result = JSON.parse(response);
					if(result.status =='success'){
						 showPopupMessage('success', result.message);
					}else{
						showPopupMessage('error', result.message + ' (Error Code: 877)');
					} 
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 878)');
                }
            });
			
		} 
    });
$(document).on('click', '#agreeTermButton', function () {
	$("input[name=term_service]").prop("checked", true);
        $('.termsCondition').css('display','none');
});
$(document).on('click', '#notAgreeTermButton', function () {
	$("input[name=term_service]").prop("checked", false);
});
$(document).on('click', '#term_service', function () {
	$("input[name=term_service]").prop("checked", false);
	service_term_popup();
});	 	
</script>