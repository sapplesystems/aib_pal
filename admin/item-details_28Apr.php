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
                showPopupMessage('error','Something went wrong, Please try again');
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
				showPopupMessage('error', 'Something went wrong, Please try again');
			}
		});   
	}
   
</script>
</body>
</html>