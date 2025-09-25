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
<!--<div class="modal fade" id="term_of_services" role="dialog" > 
    <div class="modal-dialog widthFullModal">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Terms of service </span> <button type="button" class="close canPopUp" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body" id="movefolderformdiv">
				<div style="float:right"><button type="button" onclick="PrintElem('get_term_cond_data');" class="btn btn-primary borderRadiusNone">Print</button></div>
				<p id="get_term_cond_data"> </p>
				<div  class="form-horizontal"> 
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7 marginTop20">
                            <button type="button" class="btn btn-success  borderRadiusNone" id="agreeTermButton">Yes I agree</button>
							<button type="button" class="btn btn-success  borderRadiusNone" id="notAgreeTermButton">No I do not agree</button>
                             
                        </div>
                    </div>
                </div>
				
            </div>
        </div>
    </div>
</div>-->

<div class="modal fade" id="add_record_to_scrapbook" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <button type="button" class="close model_close" data-dismiss="modal" aria-label="Close" hidden=""><span aria-hidden="true">&times;</span></button>
        <div class="modal-content">
            <div class="modal-header scrapbook_head">
                <h4 class="modal-title" id="">Add record to scrapbook <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="add_scrapbook_record_form" id="add_scrapbook_record_form" method="POST" action="">
                        <input type="hidden" id="recordId" name="recordId">
                         <input type="hidden" id="user-type2" name="user-type2" value="">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Scrapbook Entry Title* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="scrapbook_entry_title" id="scrapbook_entry_title" placeholder="Enter title here">
                            </div>
                        </div>
                        <div class="form-group" id="scrapbookselectId">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Select a scrapbook* :</label>
                            <div class="col-sm-7">
                                <select class="form-control" name="user_scrapbook" id="user_scrapbook"></select>
                            </div>
                        </div>
                        <div class="form-group" id="scrapbooktextId" style="display:none">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Enter a scrapbook* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="user_scrap_text" id="user_scrap_text" placeholder="Enter scrapbook here">
                            </div>
                        </div>
                    </form>
                    <div class="text-center" id="add_to_scrapbook_button"><button class="btn btn-success" id="add_record_scrapbook">ADD TO SCRAPBOOK</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="alert_record_to_scrapbook">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Warning..</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <img src="<?php echo HOST_ROOT_IMAGE_PATH; ?>exist.png"> <span>This record is already added in your scrapbook</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $("#add_scrapbook_record_form").validate({
        rules: {
            scrapbook_entry_title: {
                required: true
            },
            user_scrap_text: { 
                required: true,
            }
        },
        messages: {
            scrapbook_entry_title: {
                required: "Scrapbook entry title is required"
            },
            user_scrap_text: { 
                required: "Enter scrapbook here."
            }
        }
    });
});	
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
  $(document).on('click', '.record_add_to_scrapbook', function () {
        var record_id = $(this).attr('record_id');
        var user_type = $(this).attr('user-type2');
        checkScrapbookRecord(record_id);
        $('#recordId').val(record_id);
        $('#user-type2').val(user_type);
        $('.loading-div').show();
    });
    function checkScrapbookRecord(record_id) {
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'check_scrapbook_record', record_d: record_id},
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status == 'exist') {
                    $('.loading-div').hide();
                    $('#alert_record_to_scrapbook').modal('show');
                } else {
                    showModelScrapbook();
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed. (Error Code: 1167)');
            }
        });

    }
    function showModelScrapbook() {
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_user_scrapbook_listing'},
            success: function (response) {
                $('.loading-div').hide();
                $('#user_scrapbook').html('');
                var result = JSON.parse(response);
                if (result.status == 'success') {
                    $('#add_record_to_scrapbook').modal('show');
                    if (result.data.length > 0) {
                        for (i = 0; i < result.data.length; i++) {
                            $('#user_scrapbook').append('<option value="' + result.data[i].item_id + '">' + result.data[i].item_title + '</option');
                        }
                        $('#scrapbooktextId').hide();
                        $('#scrapbookselectId').show();
                    } else {
                        $('#scrapbooktextId').show();
                        $('#scrapbookselectId').hide();
                    }
                    $('.loading-div').hide();
                } else if (result.status == 'login') {
                    $('.loading-div').hide();
                    $('#response_message').html(result.message);
                    $('#response_message').show();
                    $('.loginPopup').trigger('click');
                } else {
                    showPopupMessage('error', result.message + ' (Error Code: 1168)');
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed. (Error Code: 1169)');
            }
        });
    }
    
    $(document).on('click', '#add_record_scrapbook', function () {
        if($('#add_scrapbook_record_form').valid()){
            $('.loading-div').show();
            var record_id = $('#recordId').val();
            var record_parent = '<?php echo $p_folder_id; ?>';
            var entry_title = $('#scrapbook_entry_title').val();
            var scrapbook_id = $('#user_scrapbook').val();
            var scrap_name = $('#user_scrap_text').val();
            var usert_type = $('#user_type2').val();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'add_record_to_scrapbook', record_id: record_id, entry_title: entry_title, scrapbook_id: scrapbook_id, record_parent: record_parent, scrap_name: scrap_name,usert_type:usert_type},
                success: function (response) {
                    var result = JSON.parse(response);
                    $('.loading-div').hide();
                    if (result.status == 'success') {
                        $('#add_scrapbook_record_form')[0].reset();
                        $('#add_record_to_scrapbook').modal('hide');
                        showPopupMessage('success', result.message);
                    } else if (result.status == 'login') {
                        showPopupMessage('error', result.message + ' (Error Code: 1170)');
                        setTimeout(function () {
                            $('.loginPopup').trigger('click');
                        }, 1000);
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 1171)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed. (Error Code: 1172)');
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