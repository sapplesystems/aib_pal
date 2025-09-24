<?php
//require_once dirname(__FILE__) . '/config/config.php';
if(isset($_REQUEST['folder_id']) && $_REQUEST['folder_id']!= ''){
    $folder_id = $_REQUEST['folder_id'];
    $previousId= isset($_REQUEST['previous'])? $_REQUEST['previous']:'';
    $flag = 0;
    if(isset($_REQUEST['flg']) && $_REQUEST['flg']=='people'){$flag = 1;}
    if (isset($_REQUEST['flg']) && $_REQUEST['flg']=='scrapbook') {$flag=2;}
    $previousValue = explode(',', $_REQUEST['previous']);
}else{
    $folder_id = HISTORICAL_SOCITY_ROOT;//1
}
include_once COMMON_TEMPLATE_PATH . 'header.php';
$reportingRessionCmntArray = ['Pornographic','Hate speech','Extremism','Violence','Racist','Bullying','Abuse','Child Abuse',' ','Copyright Infringement','Other'];
$scrapbookItem = (isset($_REQUEST['scrapbook_item']) && $_REQUEST['scrapbook_item'] == 'yes') ? 'yes' : '';
if(empty( $_SESSION['aib']['user_data'])){$username_hide ="hidden";}

if(isset($_REQUEST['share']) || (isset($_REQUEST['search_text']) && $_REQUEST['search_text']!='')){
    $flag= 3;
}
?>

<div class="content2" style="background:none;">
    <input type="hidden" name="details-previous-id" id="details-previous-id" value="">
    <input type="hidden" name="left-right-icon-clicked" id="left-right-icon-clicked" value="no" />
    
    <div id='details-description-section'></div>
</div>
<div class="modal fade" id="comment_report_popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Report Comment<span style="text-align:right;float: right;padding-right: 31px;color:green">.</span></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" name="comment_report_form" id="comment_report_form" method="POST">
                    <input type="hidden" name="comment_id" id="comment_id" value="">
                    <input type="hidden" name="item_url" id="item_url" value="">
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">First Name : <span>*</span></label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="rp_first_name" id="rp_first_name" value="<?php echo $_SESSION['aib']['user_data']['user_title']; ?>" placeholder="Enter your first name">
                        </div>
                    </div> 
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Last Name : <span>*</span></label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="rp_last_name" id="rp_last_name" placeholder="Enter your first name">
                        </div>
                    </div> 
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Email : <span>*</span></label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="rp_email" id="rp_email" value="<?php echo $_SESSION['aib']['user_data']['properties']['email']; ?>" placeholder="Enter your email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Phone : <span>*</span> </label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="rp_phone" id="rp_phone" placeholder="Enter your Phone" maxlength="14">
                        </div>
                    </div> 
                    <div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Reason for reporting this content: <span>*</span></label>
                            <div class="col-sm-7">
                                <select class="form-control" name="reporting_reason_comment" id="reporting_reason_comment">
                                    <option value="0">--Select--</option>
                                    <?php foreach($reportingRessionCmntArray as $reportValue){?>
                                    <option value="<?php echo $reportValue;?>"><?php echo $reportValue;?></option>
                                    <?php }?>
				 </select>
                            </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label">Your Message : </label>
                        <div class="col-sm-7">
                            <textarea class="form-control"  name="rp_message" id="rp_message" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label"></label>
                        <div class="col-sm-7">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-info" id="comment_report_form_submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; 
$backURLSearch=''; 
$search_text = (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] != '') ? $_REQUEST['search_text']: '';
if (strpos($_SERVER['HTTP_REFERER'], 'search.html') !== false) {
    $backURLSearch=$_SERVER['HTTP_REFERER'];
}
?>
<script type="text/javascript">
    var backURLSearch= '<?php echo $backURLSearch; ?>';
    var searchString = '<?php echo $search_text; ?>';
    var searchQueryString = '';
    if(searchString != ''){
        searchQueryString = '&search_text='+searchString;
    }
    $(document).ready(function(){
        var previous = '<?php echo $previousId; ?>';
        var previous_obj = previous.split(',');
        $('#details-previous-id').val(JSON.stringify(previous_obj));
        var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
	var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
        var folder_id = '<?php echo $folder_id; ?>';
        var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
        var share = '<?php echo isset($_REQUEST['share'])?$_REQUEST['share']:"0"; ?>';
        var flg = '<?php echo $flag; ?>';
		flg = parseInt(flg);
        var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
        var previousId = '<?php echo $previousValue[1]; ?>';
        var scrapbook_item = '<?php echo $scrapbookItem; ?>';
        if(scrapbook_item == 'yes' || previousId == ''){
            previousId = '<?php echo $previousValue[0]; ?>';
        }
	var detailPagecontent='Y';
        if(flg == 3 || flg == 2){ 
            getRecordIds(folder_id); 
        }else{
            getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber,pagefolder,'','','','',searchString,'',scrapbook_item);  
        }
		  
        //getAdvertisement(folder_id);
    });
    
 
 var phones = [{ "mask": "(###) ###-####"}, { "mask": "(###) ###-##############"}];
        $('#rp_phone').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        }); 
$('#comment_report_form').validate({
        rules: {
            rp_first_name:{
                required: true
            }, 
            rp_last_name:{
                required: true
            }, 
            rp_email:{
                required: {
                    depends:function(){
                        $(this).val($.trim($(this).val()));
                        return true;
                    }
                },
                validEmailid: true
            },
            rp_phone:{
                required: true
            },
            reporting_reason_comment:{
                required: true
            }
        },
        messages: {
            rp_first_name:{
                required: "First Name is required"
            }, 
            rp_last_name:{
                required: "Last Name is required"
            }, 
            rp_email:{
                required: "Email Id is required",
                validEmailid: "Please enter valid email Id"
            },
            rp_phone:{
                required: "Phone No. is required."
            },
            reporting_reason_comment:{
                required: "Reporting Reason  is required."
            },
        }
});

$(document).on('click', '.report-comment', function () {  
    var comment_id = $(this).attr('data-comment-id');  
    var url = window.location.href;
    $('#item_url').val(url);
    $('#comment_id').val(comment_id);
    $('#comment_report_popup').modal('show');
});

$(document).on('click', '#comment_report_form_submit', function(){
    if ($("#comment_report_form").valid()) { 
        $('.loading-div').show();  
        var formData = $('#comment_report_form').serialize();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'submit_comment_report', formData: formData},
            success: function (response) {
                $('.loading-div').hide();
                var result = JSON.parse(response);
                if (result.status == 'success') {
                    $('#comment_report_form')[0].reset();
                    $('#comment_report_popup').modal('hide');
                    showPopupMessage('success', result.message);
                } else {
                    showPopupMessage('error', result.message + ' (Error Code: 432)');
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed. (Error Code: 433)');
            }
        });
    }
});


//$('#details-back-button').click(function(){
$(document).on('click', '#details-back-button', function () {
    var previous_id = JSON.parse($('#details-previous-id').val());
    var current_previous = previous_id.pop();
    window.location.href="home.html?folder_id="+current_previous+"&previous="+previous_id.toString();
});
function ItemPageNumberSet(page,pagefolder){  
		var folder_id = pagefolder.split(",");  
		  $.ajax({
			url: "services.php",
			type: "post",
			data: {mode: 'set_pagenumber_data', folder_id: folder_id[folder_id.length-1], current_page_num:page},
			success: function (data) { 
			},
			error: function () {
				showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 434)');
			}
		});   
	}
    
$(document).on('click', '#submit-reprint-button', function(){
	if ($("#purchase-reprint-form").valid() &&  $('#field_reprint_form').val() =='') {
		$('.custom-captcha_error').hide();
		  $('.loading-div').show();
            var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
            var formData = $('#purchase-reprint-form').serialize();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'submit_request', formData: formData, item_id: item_id},
                success: function (response) {
                    $('.loading-div').hide();
                    var result = JSON.parse(response);
                    if (result.status == 'success') {
                        $('#purchase-reprint-form')[0].reset();
                        $('#reprintForm').modal('hide');
                        showPopupMessage('success', result.message);
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 435)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed. (Error Code: 436)');
                }
            });
	}
});
 
$(document).on('click', '#share_item_button', function(){
        if($("#share_items_front").valid() &&  $('#field_share_item').val() ==''){
            $('.loading-div').show();
            var checked_option = $("input[name='shared_type']:checked"). val();
            var mode = 'share_item';
            var selected_society = '';
            if(checked_option == 'society'){
                mode = 'share_item_with_society_admin';
                selected_society = $('#selected_society').val();
            }
            var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
            var thoumb_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-tn-file-id');
            var email = $('#share_emailId').val();
            var sharing_name = $('#sharing_name').val();
            var timestamp_value = $('#timestamp_valueid').val();
            var folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
            var share_massage = $('#share_massage').val();
            var share_url_link = '<?php echo $_SERVER['QUERY_STRING']; ?>';
            var new_fol_id = $('#new_fol_id').val();
            var search_text = '<?php  echo (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] !='')?$_REQUEST['search_text']:''; ?>';
            var username = [];
//            var email_id = $('.email1').each(function () {
//            username.push($(this).text());
//            });
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: mode,username:'',email:email,folder_id:folder_id,item_id:item_id,thoumb_id:thoumb_id,timestamp_value:timestamp_value,share_massage:share_massage, sharing_name:sharing_name,new_fol_id:new_fol_id,share_url_link:share_url_link,search_text:search_text, selected_society: selected_society},
                success: function (data) {
                   var record = JSON.parse(data);
                   $('.loading-div').hide();
                        showPopupMessage(record.status,record.message);
                        if(record.status == 'success'){
                                setTimeout(1000);
                                $('#share_items_front')[0].reset();
                        }
                        $('#share_item_from_front').modal('hide');  
                      
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 437)');
                }
	}); 
}
	 
});
$(document).on('click','.left-arrow-section',function () {
    var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';        
    //var current_value = $(".sapple-slider-thumb:visible").attr('thumb-parent-count');
    var total_items =  $('.pagination-counter .total').text(); 
    var total_thumb_page = $('.sapple-slider-thumb').length;
    var current_value = $(".sapple-slider-thumb:visible").attr('thumb-parent-count');
	current_value = parseInt(current_value);
    if (current_value == 1){
        current_value = total_thumb_page;
    }else{
        var row_val = parseInt($('#total_item_page').val()) -1;         
        if(itemId != '' &&  $( "#thumb-list-"+row_val).length == 0 ){
            var previous = '<?php echo $previousId; ?>';
            var previous_obj = previous.split(',');
            $('#details-previous-id').val(JSON.stringify(previous_obj));
            var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
            var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
            var start =  $('#start').val();
            var folder_id = '<?php echo $folder_id; ?>';
            var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
            var share = '<?php echo isset($_REQUEST['share'])?$_REQUEST['share']:"0"; ?>';
            var flg = '<?php echo $flag; ?>';
            var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
            var previousId = '<?php echo $previousValue[1]; ?>';
            var scrapbook_item = '<?php echo $scrapbookItem; ?>';
            if(scrapbook_item == 'yes' || previousId == ''){
                previousId = '<?php echo $previousValue[0]; ?>';
            }
            var detailPagecontent='Y';
            var action = 'Back';
            var total_item_page = parseInt($('#total_item_page').val(),10) - parseInt(1, 10);
            getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber,pagefolder,start,total_item_page,action,'','','',scrapbook_item);
        
        }else{
            current_value = parseInt(parseInt(current_value) - 1);
            $('.sapple-slider-thumb').css({'display': 'none'});
            $('.sapple-slider-thumb').removeClass('animated fadeIn');
            $('#thumb-list-' + current_value).css({'display': 'inline-block'});
            $('#thumb-list-' + current_value).addClass('animated fadeIn');
            if(parseInt($('#start').val()) > 0){ var start_val = parseInt($('#start').val()) - 6 ; $('#start').val(start_val); }
            if(parseInt($('#total_item_page').val()) > 0){ var total_row = parseInt($('#total_item_page').val()) - 1 ; $('#total_item_page').val(total_row); }
        }
    }   
});

$(document).on('click','.right-arrow-section',function () {
    var total_items =  $('.pagination-counter .total').text(); 
    var total_item_page = $('#total_item_page').val();
//    var total_thumb_page = $('.sapple-slider-thumb').length;
    var current_value = $(".sapple-slider-thumb:visible").attr('thumb-parent-count');
    var row_val = parseInt($('#total_item_page').val()) + 1; 
    if(current_value == total_item_page && $( "#thumb-list-"+row_val).length == 0 ){
        var previous = '<?php echo $previousId; ?>';
        var previous_obj = previous.split(',');
        $('#details-previous-id').val(JSON.stringify(previous_obj));
        var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
        var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
        var start = $('#start').val();
        var folder_id = '<?php echo $folder_id; ?>';
        var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
        var share = '<?php echo isset($_REQUEST['share'])?$_REQUEST['share']:"0"; ?>';
        var flg = '<?php echo $flag; ?>';
        var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
        var previousId = '<?php echo $previousValue[1]; ?>';
        var scrapbook_item = '<?php echo $scrapbookItem; ?>';
        if(scrapbook_item == 'yes' || previousId == ''){
            previousId = '<?php echo $previousValue[0]; ?>';
        }
        var detailPagecontent='Y';
        var action = 'Next';
        if(total_items > total_item_page*6 ){ 
        getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber,pagefolder,start,total_item_page,action,'','','',scrapbook_item);
        }
    }else{
        current_value = parseInt(parseInt(current_value) + 1);
        $('.sapple-slider-thumb').css({'display': 'none'});
        $('.sapple-slider-thumb').removeClass('animated fadeIn');
        $('#thumb-list-' + current_value).css({'display': 'inline-block'});
        $('#thumb-list-' + current_value).addClass('animated fadeIn');
            var start_val = parseInt($('#start').val()) + 6 ; $('#start').val(start_val); 
            if(parseInt($('#total_item_page').val()) > 0){ var total_row = parseInt($('#total_item_page').val()) + 1 ; $('#total_item_page').val(total_row); }
    }
});

 $(document).on('click', '.rightNavON', function () {
//        var thumb_url = '<?php echo THUMB_URL; ?>';
        $('#left-right-icon-clicked').val('yes');
        var current_active_thumb_count = parseInt($('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-count'));
        var maximum_image_count = parseInt(parseInt($('#maximum_image_count').val()) - 1);
        var next_active_count = '';
        if (current_active_thumb_count != maximum_image_count ) {
            $('.loading-div-fullPage').show();
            $('.rightNavON').hide();
            $('.leftNavON',).hide();
            $('.imgDetailsDesc').css('display','none');
            if($("#sapple-slider-thumb-item-"+parseInt(current_active_thumb_count + 1)).length == 0){
                var previous = '<?php echo $previousId; ?>';
                var previous_obj = previous.split(',');
                $('#details-previous-id').val(JSON.stringify(previous_obj));
                var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
                var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
                var start = $('#start').val();
                var folder_id = '<?php echo $folder_id; ?>';
                var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
                var share = '<?php echo isset($_REQUEST['share'])?$_REQUEST['share']:"0"; ?>';
                var flg = '<?php echo $flag; ?>';
                var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
                var previousId = '<?php echo $previousValue[1]; ?>';
                var scrapbook_item = '<?php echo $scrapbookItem; ?>';
                if(scrapbook_item == 'yes' || previousId == ''){
                    previousId = '<?php echo $previousValue[0]; ?>';
                }
                var detailPagecontent='Y';
                var action = 'Next';
                var sertchText = "<?php echo isset($_REQUEST['search_text'])?$_REQUEST['search_text']:''; ?>";
                
                if(parseInt($('#maximum_image_count').val()) > parseInt(current_active_thumb_count + 1)){ 
                getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber,pagefolder,start,$('#total_item_page').val(),action,'full-image',sertchText,'',scrapbook_item);
                } 
            }else{
                var parentCount =  $('.thumb-listing').find('.slider-active-thumb').parent('div').attr('thumb-parent-count'); 
                var currentParentId = $('.thumb-listing').find("#sapple-slider-thumb-item-"+parseInt(current_active_thumb_count + 1)).parent('div').attr('thumb-parent-count'); 
                if(parentCount < currentParentId ){
                    $('#total_item_page').val(currentParentId);
                    var strat_set_val = (parseInt(currentParentId) - 1)*6 ;
                    $('#start').val(strat_set_val);
                }
            }
            next_active_count = parseInt(current_active_thumb_count + 1);
            setTimeout(function(){
            var next_pr_file_id = $('#slider-thumb-image-' + next_active_count).attr('data-pr-file-id');
            var height = $(window).height();
            var item_id = $('#slider-thumb-image-' + next_active_count).attr('data-item-id');
            fullImageWithHighLight(next_pr_file_id,item_id,height,searchString);
            $('#slider-thumb-image-' + next_active_count).trigger('click');
            $('.sapple-slider-thumb').hide();
            $('#slider-thumb-image-' + next_active_count).parents('.sapple-slider-thumb').css('display', 'inline-block');
            $('.pagination-counter .current').text(parseInt(next_active_count)+1);
           }, 1000); 
        }
    });
 $(document).on('click', '.leftNavON', function () {
        var current_active_thumb_count = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-count');
        $('#left-right-icon-clicked').val('yes');
        var prevoius_active_count = '';
        if (current_active_thumb_count != 0) {
            $('.loading-div-fullPage').show();
            $('.rightNavON').hide();
            $('.leftNavON',).hide();
            $('.imgDetailsDesc').css('display','none');
            if($("#sapple-slider-thumb-item-"+parseInt(current_active_thumb_count - 1)).length == 0){
                var row_val = parseInt($('#total_item_page').val()) -1;         
                if(itemId != '' &&  $( "#thumb-list-"+row_val).length == 0 ){
                var previous = '<?php echo $previousId; ?>';
                var previous_obj = previous.split(',');
                $('#details-previous-id').val(JSON.stringify(previous_obj));
                var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
                var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
                var start =  $('#start').val();
                var folder_id = '<?php echo $folder_id; ?>';
                var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
                var share = '<?php echo isset($_REQUEST['share'])?$_REQUEST['share']:"0"; ?>';
                var flg = '<?php echo $flag; ?>';
                var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
                var previousId = '<?php echo $previousValue[1]; ?>';
                var scrapbook_item = '<?php echo $scrapbookItem; ?>';
                if(scrapbook_item == 'yes' || previousId == ''){
                    previousId = '<?php echo $previousValue[0]; ?>';
                }
                var detailPagecontent='Y';
                var action = 'Back';
                var sertchText = "<?php echo isset($_REQUEST['search_text'])?$_REQUEST['search_text']:''; ?>";
                var total_item_page = parseInt($('#total_item_page').val(),10) - parseInt(1, 10);
                getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber,pagefolder,start,total_item_page,action,'full-image',sertchText,'',scrapbook_item);
                }
            }else{
            var parentCount =  $('.thumb-listing').find('.slider-active-thumb').parent('div').attr('thumb-parent-count'); 
            var currentParentId = $('.thumb-listing').find("#sapple-slider-thumb-item-"+parseInt(current_active_thumb_count - 1)).parent('div').attr('thumb-parent-count'); 
            if(parentCount > currentParentId ){
                $('#total_item_page').val(parentCount);
                var strat_set_val = (parseInt(parentCount) - 1)*6 ;
                $('#start').val(strat_set_val);
                if(parseInt($('#start').val()) > 0){ var start_val = parseInt($('#start').val()) - 6 ; $('#start').val(start_val); }
                if(parseInt($('#total_item_page').val()) > 0){ var total_row = parseInt($('#total_item_page').val()) - 1 ; $('#total_item_page').val(total_row); }
            }
            }
            prevoius_active_count = parseInt(current_active_thumb_count - 1);
            setTimeout(function(){
                var next_pr_file_id = $('#slider-thumb-image-' + prevoius_active_count).attr('data-pr-file-id');
                var height = $(window).height();
                var item_id = $('#slider-thumb-image-' + prevoius_active_count).attr('data-item-id');
                fullImageWithHighLight(next_pr_file_id,item_id,height,searchString);
                $('#slider-thumb-image-' + prevoius_active_count).trigger('click');
                $('.sapple-slider-thumb').hide();
                $('#slider-thumb-image-' + prevoius_active_count).parents('.sapple-slider-thumb').css('display', 'inline-block');
                $('.pagination-counter .current').text(parseInt(prevoius_active_count)+1);
                }, 1000);
            }
    });

 function getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber='',pagefolder='',start='',total_item_page='',action='',imgFlg='',searchString = '',recordIdsArr = '',scrapbook_item = ''){
    if(recordIdsArr !='' && typeof(recordIdsArr) != 'undefined'){
            recordIdsArr = JSON.stringify(recordIdsArr);
    }else{
        recordIdsArr = '';
    }
        if(start!=''){
             mode= 'list_tree_items_detail_pagination';
        }else{
             mode= 'list_tree_items';
        }
        if(imgFlg != 'full-image') {
            $('.loading-div').show();
        } 
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: mode, folder_id: folder_id,itemId: itemId,share: share,flg:flg,previousId:previousId,reference_id:ref,detailPagecontent:detailPagecontent,start:start,total_item_page:total_item_page,action:action, searchString:searchString,recordIdsArr:recordIdsArr,scrapbook_item:scrapbook_item},
            success: function (result) {
		ItemPageNumberSet(pageNumber,pagefolder);
                if(start !=''){
                    if(action !='' && action =='Next'){
                    var add_div_id = $('#total_item_page').val();
                    $(result).insertAfter( "#thumb-list-"+add_div_id);
                    $('.sapple-slider-thumb').css({'display': 'none'});
                    $('.sapple-slider-thumb').removeClass('animated fadeIn');
                    add_div_id = parseInt(add_div_id, 10) + parseInt(1, 10);
                    $('#thumb-list-' + add_div_id).css({'display': 'inline-block'});
                    $('#thumb-list-' + add_div_id).addClass('animated fadeIn');
                    var start_val = parseInt($('#start').val()) + 6 ; $('#start').val(start_val); 
                    if(parseInt($('#total_item_page').val()) > 0){ var total_row = parseInt($('#total_item_page').val()) + 1 ; $('#total_item_page').val(total_row); }
                   }else{
                        var add_div_id = parseInt($('#total_item_page').val());
                        $(result).insertBefore( "#thumb-list-"+add_div_id);
                        $('#thumb-list-' + add_div_id).css({'display': 'none'});
                        $('#thumb-list-' + add_div_id).removeClass('animated fadeIn');
                        add_div_id = add_div_id - parseInt(1,10);
                        $('#thumb-list-' + add_div_id).css({'display': 'inline-block'});
                        $('#thumb-list-' + add_div_id).addClass('animated fadeIn');
                        if(parseInt($('#start').val()) > 0){ var start_val = parseInt($('#start').val()) - 6 ; $('#start').val(start_val); }
                        if(parseInt($('#total_item_page').val()) > 0){ var total_row = parseInt($('#total_item_page').val()) - 1 ; $('#total_item_page').val(total_row); }
                    }
                   
                }else{
                    if(jQuery.trim(result)=="false"){
                       var html='<div class="minHeight700"><div class="disabledLink"><img src="public/images/disabled-img.jpg"></div></div><div id="backDivURL"><a id="backURL" href="">Back to Search</a></div>';
                        $('#details-description-section').html(html);
                            if(backURLSearch=='' || backURLSearch==0){
                                $('#backDivURL').hide();
                            }else{
                                $('#backURL').attr('href',backURLSearch);
                            }
                    }else{
                        $('#details-description-section').html(result);
                        if(backURLSearch=='' || backURLSearch==0){
                            $('#backDivURL').hide();
                        }else{
                            $('#backURL').attr('href',backURLSearch);
                        }
                    }
                }
              if(imgFlg != 'full-image') {
                $('.loading-div').hide();
               } 
               if(itemId === ''){
                    $('.record_home').trigger('click');
                    $('#sapple-slider-thumb-item-0').addClass('slider-active-thumb');
                }
            },
            error: function () {
                $('.loading-div').hide();
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 438)');
            }
        });
}

 function pageMoveToItemDetail(id) {
        var page = $('#total_item_page').val(); 
        var current_page = 1;
        if(page != ''){
            current_page = page;
        }
        var previous_id_obj = '<?php echo $previousId; ?>';
        var queryString = 'folder_id='+id+'&previous=' + previous_id_obj + '&page='+current_page;
        getEncryptedString(queryString, 'item-details.html');
    }
 function getRecordIds(id){
     $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'record_id_session',folder_id:id},
            success: function (data) {
               var record = JSON.parse(data);
               $('.loading-div').hide();
                    if(record.status == 'success'){
                        var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
                        var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
                        var folder_id = '<?php echo $folder_id; ?>';
                        var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
                        var share = '<?php echo isset($_REQUEST['share'])?$_REQUEST['share']:"0"; ?>';
                        var flg = '<?php echo $flag; ?>';
                        var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
                        var previousId = '<?php echo $previousValue[1]; ?>';
                        var scrapbook_item = '<?php echo $scrapbookItem; ?>';
                        if(scrapbook_item == 'yes' || previousId == ''){
                            previousId = '<?php echo $previousValue[0]; ?>';
                        }
                        var detailPagecontent='Y';
                        getRecordTreedata(folder_id,itemId,share,flg,previousId,ref,detailPagecontent,pageNumber,pagefolder,'','','','',searchString,record.recordIdarr,scrapbook_item);
                    }
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 439)');
            }
	}); 
 }  
 function imageLoad(){
    $('.full-high-light-image').css('display','block'); 
    var fullPath = document.getElementById("full-image-content").src;
    var filename = fullPath.replace(/^.*[\\\/]/, '');
    if(filename !='image_loading.gif'){
        $('.imgDetailsDesc').css('display','block');
        if(parseInt($('.slider-active-thumb').children('.itemDetailImageNumb').text()) >= parseInt($('.total').text())){
            $('.rightNavON').hide();
        }else{
            $('.rightNavON').show();
        }
        if(parseInt($('.slider-active-thumb').children('.itemDetailImageNumb').text()) <= parseInt('1')){
            $('.leftNavON').hide();
        }else{
            $('.leftNavON').show();
        }
        //$('.leftNavON').show();
        //$('.rightNavON').show();
        $('.loading-div-fullPage').hide();
    }
}
function fullImageWithHighLight(pr_file_id,item_id,height,searchString){
//    if(pr_file_id !=''){
        $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'image_data_with_highlight', item_id: item_id,pr_file_id:pr_file_id, searchString:searchString, height: height},
        success: function (data) {
           $('#large_image_container').html(data);
          // $(data).insertAfter("#full-image-content" );
           //$('#full-image-content').attr('src', $('#original-image-content').attr('data-src'));
        },
        error: function () {
            showPopupMessage('error','Something went wrong, Please try again. (Error Code: 440)');
        }
   });
//    }
        
    
}
</script>
</body>
</html>