<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
$reportingRessionCmntArray = ['Pornographic','Hate speech','Extremism','Violence','Racist','Bullying','Abuse','Child Abuse',' ','Copyright Infringement','Other'];
$pr_file_id = $_REQUEST['pr_file_id'];

?>
<div class="original_image">
    <?php if(!isset($_REQUEST['searchString'])){ ?>
    <img class="centerImg" src="<?php echo THUMB_URL.'?id='.$pr_file_id.'&show_text=yes&search_text='.$searchString; ?>" alt="Slider Image" />
    <?php } ?>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){ 
        $('html, body').css('overflow-x', 'inherit'); 
        $('.pull-right').hide();
        $('.footer').hide();
        var item_id = "<?php echo isset($_REQUEST['item_id'])?$_REQUEST['item_id']:''; ?>"; 
        var pr_file_id = "<?php echo (isset($_REQUEST['pr_file_id']))?$_REQUEST['pr_file_id']:''; ?>";
        var searchString = "<?php echo (isset($_REQUEST['searchString']))?$_REQUEST['searchString']:''; ?>";
        if(item_id !='' && pr_file_id !='' && searchString !=''){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'image_with_original_data', item_id: item_id,pr_file_id:pr_file_id, searchString:searchString},
                success: function (data) {
                   $('.original_image').html(data);
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 666)');
                }
            });
            
        }
       
    });
    function highLighOriginalImage(){
        $('.high_light_div').css('display','block');
    }
</script>
</body>
</html>