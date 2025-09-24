<?php
require_once dirname(__FILE__) . '/config/config.php';
if(isset($_REQUEST['folder_id']) && $_REQUEST['folder_id']!= ''){
    $folder_id = $_REQUEST['folder_id'];
    $previousId= $_REQUEST['previous'];
    $flag = 0;
    if(isset($_REQUEST['flg']) && $_REQUEST['flg']=='people'){$flag = 1;}
    if (isset($_REQUEST['flg']) && $_REQUEST['flg']=='scrapbook') {$flag=2;}
    $previousValue = explode(',', $_REQUEST['previous']);
}else{
    $folder_id = HISTORICAL_SOCITY_ROOT;//1
}
include_once COMMON_TEMPLATE_PATH . 'header.php';
$pageUsedOptions = [
    'BUS' => "Business -- Promote your business by reprinting portion of articles",
    'BUSR' => "Business -- Research content to be used as a component",
    'MED' => "Media -- Television, radio, print news, public relations",
    'MEDG' => "Media -- Professional Genealogy Services",
    'MEDA' => "Media -- Any activity where you receive payment for use of content",
    'PER' => "Personal -- Single scrapbook, family history document",
    'PERE' => "Personal -- Educational or hobby use",
    'OTH' => "Other -- Describe in detail in the 'comments' section below"
];

if(empty( $_SESSION['aib']['user_data'])){$username_hide ="hidden";}
?>
<div class="content2" style="background:none;">
    <input type="hidden" name="details-previous-id" id="details-previous-id" value="">
    
    <div id='details-description-section'></div>
   
	<!--<div class="col-md-12">
    <div class="container-fluid" style="background:#e9e9e9;">
        <div class="row alsoLike">
            <div class="col-md-12">
                <h4><span class="circleHeading"></span>You may also like</h4>
                <ul class="alsoLikeImages">
                    <li><img src="<?php echo IMAGE_PATH . '1.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                    <li><img src="<?php echo IMAGE_PATH . '2.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                    <li><img src="<?php echo IMAGE_PATH . '3.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                    <li><img src="<?php echo IMAGE_PATH . '4.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                </ul>
            </div>
        </div>
    </div>
	</div>
	<div class="col-md-12">
    <div class="container-fluid" style="background:#444444;">
        <div class="row recentlyViewed">
            <div class="col-md-12">
                <h4><span class="circleHeading"></span>You may also like</h4>
                <ul class="recentlyViewedImages">
                    <li><img src="<?php echo IMAGE_PATH . '1.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                    <li><img src="<?php echo IMAGE_PATH . '2.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                    <li><img src="<?php echo IMAGE_PATH . '3.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                    <li><img src="<?php echo IMAGE_PATH . '4.jpg'; ?>" /> <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p><div class="ImgView"><div><a href="#"><img src="<?php echo IMAGE_PATH .'full-view.png'; ?>" /></a> <a href="#"><img src="<?php echo IMAGE_PATH .'link.png'; ?>" /></a></div></div></li>
                </ul>
            </div>
        </div>
    </div>
	</div>-->
</div>

<div class="modal fade" id="reprintForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Reprint Form</h4>
				<h6>protected by reCAPTCHA</h6>
            </div>
            <div class="modal-body">
                <div class="box_public_info">
                    <p id="archive_group_reprint_text"></p>
                </div>
                <div class="box_public_info" style="padding: 15px;">
                    <p>Your purchase includes a Restricted Use License. Read our <a href="<?php echo COPY_RIGHT_URL; ?>" target="_blank"><font color="red">Copyright and Permitted Use Policy.</font></a><br>
                        For larger orders and multiple editions, please <a id="contact_reprint_request" href="javascript:void(0);"><font color="red">contact us.</font></a>
                    </p>
                </div>
                <div class="">
                    <p>* = Required Field</p>
                    <form class="form-horizontal" name="purchase-reprint-form" id="purchase-reprint-form" method="POST" action="">
                        <input type="hidden" name="request_type" id="request_type" value="RP">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="cus_first_name" id="cus_first_name" placeholder="">
                            </div>
                        </div>
						 <div id="recaptcha-form-3" style="display:none;"></div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="cus_last_name" id="cus_last_name" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email Address* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="cus_email" id="cus_email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Paste links here of the page(s) you wish to order* :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="cus_page_link" name="cus_page_link" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">How will these pages be used?* :</label>
                            <div class="col-sm-7">
                                <select class="form-control" name="cus_page_used" id="cus_page_used">
                                    <option value="">Please select one</option>
                                    <?php foreach ($pageUsedOptions as $key => $pageOption) { ?>
                                        <option value="<?php echo $key ?>"><?php echo $pageOption; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Comments :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="cus_comments" name="cus_comments" rows="4"></textarea>
                            </div>
                        </div> 
                    </form>
					 <div class="text-center"><button id="submit-reprint-button" class="btn btn-success" onclick="reprint_button_form();">SUBMIT REQUEST</button></div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="modal fade bs-example-modal-sm" id="share_item_from_front" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Share Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
				<h6>protected by reCAPTCHA</h6>
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="share_items_front" id="share_items_front" method="POST" action="">
                     <div class="form-group" <?php echo$username_hide; ?>> 
                      <label class="col-xs-4 control-label">Select Username</label>
                       <div id="sub-group" class="archive-group col-md-7">
                        <div id="sare_item_username" class="form-control "></div>
                      </div>
                      </div>
					   <div id="recaptcha-form-4" style="display:none;"></div>
                         <div class="clearfix"></div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email-Id* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="share_emailId" id="share_emailId" placeholder="Enter Email-Id here">
                            </div>
                        </div>
                    </form>
                    <div class="text-center"><button class="btn btn-success" type="submit" id="share_item_button">Share</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        var previous = '<?php echo $previousId; ?>';
        var previous_obj = previous.split(',');
        $('#details-previous-id').val(JSON.stringify(previous_obj));
		
        var pageNumber = '<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:"";?>';
		var pagefolder = '<?php echo isset($_REQUEST['previous'])?$_REQUEST['previous']:"";?>';
		
        var folder_id = '<?php echo $folder_id; ?>';
		var itemId = '<?php echo isset($_REQUEST['itemId'])?$_REQUEST['itemId']:""; ?>';
        var flg = '<?php echo $flag; ?>';
        var ref = '<?php echo $_REQUEST['item_refrence_id']; ?>';
        var previousId = '<?php echo $previousValue[1]; ?>';
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'list_tree_items', folder_id: folder_id,itemId: itemId,flg:flg,previousId:previousId,reference_id:ref},
            success: function (result) {
                $('.loading-div').hide();
				ItemPageNumberSet(pageNumber,pagefolder);
                if(jQuery.trim(result)=="false"){
                    var html='<div class="minHeight700"><div class="disabledLink"><img src="public/images/disabled-img.jpg"></div></div>';
                    $('#details-description-section').html(html);
                }else{
                    $('#details-description-section').html(result);
                }
            },
            error: function () {
                $('.loading-div').hide();
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 667)');
            }
        });
		
		  $("#share_items_front").validate({
            rules: {
                share_emailId: {
                    email:true,
					required: true,
                }
            },
            messages: {
                share_emailId: {
                    email:"Please enter correct email id4",
					required: "Email-ID is required."
                }
            }
        });
        //getAdvertisement(folder_id);
    });
    //$('#details-back-button').click(function(){
    $(document).on('click', '#details-back-button', function () {
        var previous_id = JSON.parse($('#details-previous-id').val());
        var current_previous = previous_id.pop();
        window.location.href="home.php?folder_id="+current_previous+"&previous="+previous_id.toString();
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
				showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 668)');
			}
		});   
	}
   
function save_purchase_reprint(form){ 
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
                        showPopupMessage('error', result.message + ' (Error Code: 669)');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed. (Error Code: 670)');
                }
            }); 
	
}

function share_email_item_save(){
	 
	grecaptcha.reset(widget_4); 
	$('.loading-div').show();
		  var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
		  var thoumb_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-tn-file-id');
		  var email = $('#share_emailId').val();
		  var folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
		  var username = [];
		  var email_id = $('.email1').each(function () {
		  username.push($(this).text());
		  });
		  $.ajax({
			url: "services.php",
			type: "post",
			data: {mode: 'share_item',username:username,email:email,folder_id:folder_id,item_id:item_id,thoumb_id:thoumb_id},
			success: function (data) {
			   var record = JSON.parse(data);
			   $('.loading-div').hide();
				showPopupMessage(record.status,record.message);
				if(record.status == 'success'){
					setTimeout(1000);
				}
				$('#share_item_from_front').modal('hide');  
				$('#share_emailId').val('');
			},
			error: function () {
				showPopupMessage('error','Something went wrong, Please try again. (Error Code: 671)');
			}
		});  
}
</script>
</body>
</html>