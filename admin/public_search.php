<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
$archive_id = (isset($_REQUEST['archive_id']) && $_REQUEST['archive_id'] != '') ? $_REQUEST['archive_id']: '';
$search_text= (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] != '') ? $_REQUEST['search_text']: '';
$string_first_char = ($search_text[0] == "'") ? 'single' : 'normal';
$return_page   = (isset($_REQUEST['return']) && $_REQUEST['return']!= '') ? $_REQUEST['return'].'.html?q='.encryptQueryString('folder_id='.$_REQUEST['archive_id'].'&show_text=yes') : '';
if($_REQUEST['return_to'] == 'root'){
    $return_page   = $_REQUEST['return'].'.html?q='.encryptQueryString('folder_id='.PUBLIC_USER_ROOT);
}
?>
<style>
    mark{background-color: #fbd42f !important;}
</style>
<div class="header_img bgBlue_header">
    <div class="bannerImage " style="background-size: 100% 100% !important;max-width: 100%;"></div>
</div>
<div class="clearfix"></div>
<div class="content2" style="min-height: 400px;">
    <div class="container">
        <div class="row marginTop20">
            <div class="col-md-5 col-sm-12 col-xs-12" >
                <?php if($string_first_char == 'single'){ ?>
                    <input type="text" class="form-control searchInputResult" name="search_text" id="search_text" value="<?php echo $search_text; ?>" placeholder="Enter text">
                <?php }else{ ?>
                    <input type="text" class="form-control searchInputResult" name="search_text" id="search_text" value='<?php echo $search_text; ?>' placeholder="Enter text">
                <?php } ?>
                <button class="btn" id="search_in_archive">Search</button>
            </div>
            <div class="col-md-3 text-center"><span class="searchResultHeading">Search Results</span></div>
            <div class="col-md-4 text-right"><a class="backtoLink" href="<?php echo $return_page; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a></div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
        <div class="col-md-12">
            <div id="search_result_render_space" style="display:none;">Loading....</div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH.'jquery.mark.min.js'; ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
//        getArchiveListing();
      	<?php if (isset($_SESSION['archive_header_image']) and $_SESSION['archive_header_image']!='admin/tmp/') { ?>
        $('.bannerImage').css('background-image', 'url(<?php echo $_SESSION['archive_header_image']; ?>)'); 
       <?php }else{ ?>
           $('.bannerImage').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
       <?php } ?> 
    // This code add by Bateshwar to resolve issues 1814
    var archive_id = "<?php echo $archive_id; ?>";
    <?php if($string_first_char == 'single'){ ?>
        var search_text = "<?php echo $search_text; ?>";
    <?php }else{ ?>
        var search_text = '<?php echo $search_text; ?>';
    <?php } ?>
    if(archive_id != '' && search_text!= ''){
        getDataToDisplay(1,archive_id,search_text);
    }

 });
//    function getArchiveListing(){
//        var archive_id = "<?php echo $archive_id; ?>";
//        var search_text = "<?php echo $search_text; ?>";
//         $('.loading-div').show();
//         $.ajax({
//            url: "services.php",
//            type: "post",
//            data: {mode: 'get_all_public_archive'},
//            success: function (response) {
//                
//                $('#archive_listing_select').html(response);
//                if(archive_id != ''){
//                    $('#archive_listing_select').val(archive_id);
//                }
//                if(archive_id != '' && search_text!= ''){
//                    getDataToDisplay(1);
//                    //$('#search_in_archive').trigger('click');
//                }else{
//                    $('.loading-div').hide();
//                }
//            },
//            error: function () {
//                showPopupMessage('error','Something went wrong, Please try again');
//                $('.loading-div').hide();
//            }
//        });
//    }
    
    $(document).on('click', '#search_in_archive', function(){
        var search_text = $('#search_text').val();
//        var archive_id  = $('#archive_listing_select').val();
        var archive_id  = '<?php echo $archive_id; ?>';
        var search_mode = '<?php echo $_REQUEST['search_mode']; ?>';
        var return_to   = '<?php echo $_REQUEST['return']; ?>';
        var queryString = 'search_mode='+search_mode+'&return='+return_to+'&archive_id='+archive_id+'&search_text='+search_text;
        getEncryptedString(queryString,'public_search.html');
        //var url = 'public_search.html?search_mode='+search_mode+'&return='+return_to+'&archive_id='+archive_id+'&search_text='+search_text;
        //window.location.href = url;
        //getDataToDisplay(1);
    });
    
    function getDataToDisplay(current_page,archive_id='',search_text=''){
//        var search_text = $('#search_text').val();
//        var archive_id  = $('#archive_listing_select').val();

        // code add by Bateshwar to resolved issue 1814
        
//        var archive_type = $('#archive_listing_select option:selected').attr('data_type');
        var record_per_page = $('#record_per_page').val();
        if(search_text != ''){
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'search_data',search_text: search_text, archive_id: archive_id, archive_type: 'ar',current_page: current_page,record_per_page: record_per_page,source:'people'},
                success: function (response) {
                    $('#search_result_render_space').html(response);
                    $('#search_result_render_space').show();
                    $("#search_result_render_space").mark($('#search_text').val());
                    $('.loading-div').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 600)');
                    $('.loading-div').hide();
                }
            });
        }
    }
    $(document).on('click','.pagination-link',function(){
        var page_no = $(this).attr('data-count');
        getDataToDisplay(page_no);
    });
    $(document).on('click','.next-page',function(){
        var next_page = parseInt(parseInt($('#current_page').val())+1);
        getDataToDisplay(next_page);
    });
    $(document).on('click','.previous-page',function(){
        var prev_page = parseInt(parseInt($('#current_page').val())-1);
        getDataToDisplay(prev_page);
    });
</script>