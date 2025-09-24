<?php
require_once dirname(__FILE__) . '/config/config.php';

$societyTemp = isset($_REQUEST['society_template']) ? $_REQUEST['society_template'] : '';
if($societyTemp=='custom2'){
	include_once COMMON_TEMPLATE_PATH . 'header2.php';
}else if($societyTemp=='custom1'){
	include_once COMMON_TEMPLATE_PATH . 'details-header.php';
}else{
	include_once COMMON_TEMPLATE_PATH . 'header.php';
}

$archive_id     = (isset($_REQUEST['archive_id']) && $_REQUEST['archive_id'] != '') ? $_REQUEST['archive_id']: '';
$search_text    = (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] != '') ? $_REQUEST['search_text']: '';
$string_first_char = ($search_text[0] == "'") ? 'single' : 'normal';
$search_mode    = (isset($_REQUEST['search_mode']) && $_REQUEST['search_mode']!= '') ? $_REQUEST['search_mode'] : '';
$searched_tags  = (isset($_REQUEST['searched_tags']) && $_REQUEST['searched_tags']!= '') ? $_REQUEST['searched_tags'] : '';
$return_page    = (isset($_REQUEST['return']) && $_REQUEST['return']!= '') ? $_REQUEST['return'].'.html?q='.encryptQueryString('folder_id='.$_REQUEST['current_item_id'].'&society_template='.$societyTemp): '';
?>
<style>
    mark{background-color: #fbd42f !important;}
</style>
<div class="header_img bgBlue_header">
    <div class="bannerImage bannerImage_society "></div>
    <div class="clientLogo"><img id="client_logo" style="width:200px;" src="" /></div>
</div>
<div class="clearfix"></div>
<div class="content2" style="min-height: 400px;">
    <div class="container">
        <div class="row marginTop20">
            
            <?php if($search_mode == 'archive'){ ?>
<!--            <div class="col-md-3" >
                <label>Select Archive:</label><select class="form-control" name="archive_listing_select" id="archive_listing_select"></select>
            </div>-->
            <div class="col-md-5 col-sm-12 col-xs-12">
                <?php if($string_first_char == 'single'){ ?>
                    <input type="text" class="form-control searchInputResult" name="search_text" id="search_text" value="<?php echo $search_text; ?>" placeholder="Enter text">
                <?php }else{ ?>
                    <input type="text" class="form-control searchInputResult" name="search_text" id="search_text" value='<?php echo $search_text; ?>' placeholder="Enter text">
                <?php } ?>
                    <button class="btn" id="search_in_archive">Search</button>
            </div>
            <div class="col-md-3 text-center"><span class="searchResultHeading">Search Results</span></div>
            <div class="col-md-4 text-right"><a class="backtoLink" href="<?php echo $return_page; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a></div>
            <?php }else{ ?>
                <div class="col-md-5" >
                    <ul id="search_by_tags">
                        <?php 
                            $tagsArray = explode(',',$searched_tags);
                            if(!empty($tagsArray)){
                                foreach($tagsArray as $tag){
                                    echo '<li>'.$tag.'</li>';
                                }
                            }
                        ?>
                    </ul>
                </div>
                <div class="col-md-3 text-center"><span class="searchResultHeading">Search Results</span></div>
                <div class="col-md-4 text-right"><a class="backtoLink" href="<?php echo $return_page; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a></div>
            <?php } ?>
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

<?php
if($societyTemp=='custom2'){
	include_once COMMON_TEMPLATE_PATH . 'footer2.php';
}else if($societyTemp=='custom1'){
	include_once COMMON_TEMPLATE_PATH . 'details-footer.php';
}else{
	include_once COMMON_TEMPLATE_PATH . 'footer.php';
}
?>

<?php //include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script src="<?php echo JS_PATH.'jquery.mark.min.js'; ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
        
    var search_mode = '<?php echo $search_mode; ?>';
    if(search_mode == 'archive'){
//            getArchiveListing();

    // Code add by Bateshwar to resolve issue 1814 
    var archive_id = "<?php echo $archive_id; ?>";
    <?php if($string_first_char == 'single'){ ?>
        var search_text = "<?php echo $search_text; ?>";
    <?php }else{ ?>
        var search_text = '<?php echo $search_text; ?>';
    <?php } ?>
    
    if(archive_id != '' && search_text!= ''){
         getDataToDisplay(1,archive_id,search_text);
}


        }else{
            $('#search_by_tags').tagit({ fieldName: "skills", allowSpaces: true, singleFieldDelimiter: ",", placeholderText: 'Separate tags with comma(,)'});
            //$('#search_by_tags').tagit({label: '<?php echo $searched_tags; ?>', value: "<?php echo $searched_tags; ?>"});
            //$(".tagit-new input.ui-widget-content.ui-autocomplete-input").val("<?php echo $searched_tags; ?>").blur();
            getDataByTagsFilter('<?php echo $searched_tags; ?>');
        }
        
        $(document).on('keyup',".tagit-new input.ui-widget-content.ui-autocomplete-input",function(event){
            var pressed_key = event.which;
            var searched_tags = '';
            $("#search_by_tags li").each(function(){
                if($(this).children('span').hasClass('tagit-label')){
                    searched_tags = searched_tags+','+($(this).children('span').text());
                }
            });
            if(pressed_key == 13){ 
                searched_tags = searched_tags.substr(1)
               getDataByTagsFilter(searched_tags);
            }
        });
      <?php if (isset($_SESSION['archive_header_image']) and $_SESSION['archive_header_image'] != '' and $_SESSION['archive_header_image']!='admin/tmp/') { ?>
            $('.bannerImage').css('background-image', 'url(<?php echo $_SESSION['archive_header_image']; ?>)');
           
     <?php }else{ ?>
           $('.bannerImage').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
       <?php } if (isset($_SESSION['archive_logo_image']) and $_SESSION['archive_logo_image'] != '' and $_SESSION['archive_logo_image']!='admin/tmp/') { ?>
          
            $('#client_logo').attr('src', '<?php echo $_SESSION['archive_logo_image']; ?>');
     <?php } ?>
    });
//    function getArchiveListing(){
//        var archive_id = "<?php echo $archive_id; ?>";
//        var search_text = "<?php echo $search_text; ?>";
//         $('.loading-div').show();
//         $.ajax({
//            url: "services.php",
//            type: "post",
//            data: {mode: 'get_all_archive'},
//            success: function (response) {
//                $('#archive_listing_select').html(response);
//                if(archive_id != ''){
//                    $('#archive_listing_select').val(archive_id);
//                }
//                if(archive_id != '' && search_text!= ''){
//                    //$('#search_in_archive').trigger('click');
//                    getDataToDisplay(1);
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
        var search_mode = '<?php echo $search_mode; ?>';
        var return_to   = '<?php echo $_REQUEST['return']; ?>';
        var current_item_id = '<?php echo $_REQUEST['current_item_id'] ?>';
        var archive_type ='<?php echo $_REQUEST['archive_type']; ?>';
        window.location.href = 'search.html?archive_type='+archive_type+'&search_mode='+search_mode+'&return='+return_to+'&archive_id='+archive_id+'&current_item_id='+current_item_id+'&search_text='+search_text;
       
        //getDataToDisplay(1);
    });
    
    function getDataByTagsFilter(tag){
        $('.loading-div').show();
        var current_item_id = '<?php echo $_REQUEST['current_item_id']; ?>';
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'search_data_by_tags',search_tags: tag, current_item_id: current_item_id},
            success: function (response) {
                $('#search_result_render_space').html(response);
                var you_were_here = $('.you-were-here').html();
                $('.you-were-here').remove();
                $(you_were_here).insertBefore("#search_result_render_space");
                $('#search_result_render_space').show();
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 632)');
                $('.loading-div').hide();
            }
        });
    }
    
    function getDataToDisplay(current_page,archive_id='',search_text=''){
//        var search_text = $('#search_text').val();
//        var archive_id  = $('#archive_listing_select').val();
//        var archive_type= $('#archive_listing_select option:selected').attr('data_type');
        var archive_type ='<?php echo $_REQUEST['archive_type']; ?>';
        var record_per_page = $('#record_per_page').val();
        var current_item_id = '<?php echo $_REQUEST['current_item_id']; ?>';
        if(search_text != ''){
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'search_data',search_text: search_text, archive_id: archive_id, archive_type: archive_type,current_page: current_page,record_per_page: record_per_page,source:'historical', current_item_id: current_item_id},
                success: function (response) {
                    $('#search_result_render_space').html(response);
                    var you_were_here = $('.you-were-here').html();
                    $('.you-were-here').remove();
                    $(you_were_here).insertBefore("#search_result_render_space");
                    $('#search_result_render_space').show();
						searchTextOrg=$('#search_text').val().replace(/^"(.*)"$/, '$1');
						searchTextOrg = searchTextOrg.replace(/['",]+/g, '')
                    $("#search_result_render_space").mark(searchTextOrg,{"separateWordSearch": false});
					 $("#search_result_render_space").mark(searchTextOrg);
                    $('.loading-div').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 633)');
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
    $(document).on('keyup', '#search_text', function (e) {
        var key = e.which;
        if(key == 13){    
            $('#search_in_archive').click();
        }
    });
    
    $(document).on('click', '.search_result_clicked', function(){
        var item_id = $(this).attr('search_item_id');
        if(item_id){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'item_visited',item_id: item_id},
                success: function (response) {},
                error: function () {}
            });
        }
    });
</script>