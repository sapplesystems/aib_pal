<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
$reportingRessionCmntArray = ['Pornographic','Hate speech','Extremism','Violence','Racist','Bullying','Abuse','Child Abuse',' ','Copyright Infringement','Other'];
$pr_file_id = $_REQUEST['pr_file_id'];
/*********** SS Fix Start Issue Id 2313 16-Aug-2023 **************/
$folder_id = $_REQUEST['folder_id'];
/*********** SS End Start Issue Id 2313 16-Aug-2023 **************/
$downloadImagehide = $_REQUEST['downloadImagehide'];
?>
<div class="original_image">
    <?php if(!isset($_REQUEST['searchString'])){ ?>
	<!--/*********** SS Fix Start Issue Id 2313 16-Aug-2023 **************/-->
    <img class="centerImg" src="<?php echo THUMB_URL.'?id='.$pr_file_id.'&show_text=yes&search_text='.$searchString.'&folder_id='.$folder_id; ?>" alt="Slider Image" />
	<!--/*********** SS Fix Start Issue Id 2313 16-Aug-2023 **************/-->
    <?php } ?>
	<!------- Fix start for Issue ID 2141 on 17-Jan2023 ---->
                    <a href="javascript:rotateImageFullImage()" class="rotateBTNFull2">Rotate Image</a>
									<!------- Fix End for Issue ID 2141 on 17-Jan2023 ---->
	<!------- Fix start for Issue ID 0002150  27-Jan-2023 ---->
	<div class="download-image download-print" style="display: block;right: 70px;">
		<button class="btn downloadBtn <?php echo $downloadImagehide; ?>">Download </button><img height="20" src="public/images/reprintIcon.png" alt="" class="reprintIcon"></div>
	<!------- Fix End for Issue ID 0002150  27-Jan-2023 ---->
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="https://use.fontawesome.com/e9084ed560.js"></script>
<script type="text/javascript">
	/*<!------- Fix start for Issue ID 2141 on 17-Jan2023 ---->*/
	var rotateFullIamgeAngle=90;
	function rotateImageFullImage()
	{
		$('.centerImg').css({'transform': 'rotate('+rotateFullIamgeAngle+'deg)'});
		rotateFullIamgeAngle=rotateFullIamgeAngle+90;
		
	}
	/*<!------- Fix End for Issue ID 2141 on 17-Jan2023 ---->*/
    $(document).ready(function(){ 
        $('html, body').css('overflow-x', 'inherit'); 
        $('.pull-right').hide();
        $('.footer').hide();
        $('.loading-div').hide();
		var height =$('.centerImg').height();
        var item_id = "<?php echo isset($_REQUEST['item_id'])?$_REQUEST['item_id']:''; ?>"; 
        var pr_file_id = "<?php echo (isset($_REQUEST['pr_file_id']))?$_REQUEST['pr_file_id']:''; ?>";
        var searchString = "<?php echo (isset($_REQUEST['searchString']))?$_REQUEST['searchString']:''; ?>";
        if(item_id !='' && pr_file_id !='' && searchString !=''){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'image_with_original_data', item_id: item_id,pr_file_id:pr_file_id, searchString:searchString,height:height},
                success: function (data) {
                   $('.original_image').html(data);
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 328)');
                }
            });
			
        }
		 /**********************Fix start for Issue ID 0002150  27-Jan-2023**************************************************/	
		var downloadImagehide='<?php echo $_REQUEST['downloadImagehide'];?>';
		$.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'item_get_download_enable', item_id: item_id},
                success: function (data) {
                 if(data==1)
					 {
						 $('.download-image').hide();
					 }
				else if(downloadImagehide=='hidden'){
				
					$('.download-image').hide();}	
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 329)');
                }
            });
      /**********************Fix End for Issue ID 0002150  27-Jan-2023*******************************************************/	      
       
    });
	
	 /**********************Fix start for Issue ID 0002150  27-Jan-2023***********************************************************/	
          $(document).on('click','.downloadBtn',function(){//view_original_image.html
            
            var pr_file_id = "<?php echo (isset($_REQUEST['pr_file_id']))?$_REQUEST['pr_file_id']:''; ?>"
			  source="<?php echo $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];  //echo THUMB_URL;?>/get_thumb.php?id="+pr_file_id+"&show_text=yes&folder_id=<?php echo $_REQUEST['folder_id'];?>&downloadImage=1";
			  const fileName = source.split('/').pop();
	var el = document.createElement("a");
	el.setAttribute("href", source);
	el.setAttribute("download", pr_file_id);
	document.body.appendChild(el);
 	el.click();
	el.remove();
          
        });
		/**********************Fix End for Issue ID 0002150  27-Jan-2023*******************************************************/	
    function highLighOriginalImage(){
        $('.high_light_div').css('display','block');
    }
	 /**********************Fix start for Issue ID 0002308  12-Sep-2023***********************************************************/	
	$('.centerImg').on('dragstart', function(event) { event.preventDefault(); });
	 /**********************Fix start for Issue ID 0002308  12-Sep-2023***********************************************************/	
	
</script>
</body>
</html>