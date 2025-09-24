<?php
require_once dirname(__FILE__) . '/config/config.php';
$headerImage = end(explode('/',$_SESSION['archive_header_image']));
if (isset($_REQUEST['folder_id']) && $_REQUEST['folder_id'] != '') {
    $folder_id = $_REQUEST['folder_id'];
} else {
    $folder_id = PUBLIC_USER_ROOT;
}
$previous = '';
if (isset($_REQUEST['previous']) && $_REQUEST['previous'] != '') {
    $previous = $_REQUEST['previous'];
}
include_once COMMON_TEMPLATE_PATH . 'header_public.php';
?>
    
    <div class="header_img">
   <div class="bannerImage" style="background-size: cover !important; max-width: 100%; background-position:center 45% !important;"></div>
    <?php
    if (isset($_REQUEST['show_text'])) {
	$description =  'Designed to promote sharing, your content can now be spread far and wide with your own ArchiveInABox. It’s easy and free to get started.';
	$title = 'Do you have a collection you want to share?';
    ?>
        <div class="headerImage animate fadeInRightBig two"><h4 style=""><?php echo $title; ?></h4>
            <div><?php echo $description; ?><br><div class="readMoreLink"><a href="why-us-people.html">Learn More</a></div></div>
        </div>
    <?php } ?>
    <?php if (strpos($_SERVER['HTTP_REFERER'], 'public_search.html') !== false ) { ?>
    <div class="pull-right btn search-button" id="backDivURLHome"><a id="backURL" href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt=""> <span>Back To Search</span></a></div>
    <?php } ?>
    </div>

<div class="clearfix"></div>
<div class="content2">
    <div class="container-fluid">
        <div class="row-fluid">
            <div id="dynamic-tree-content" class="col-md-2 col-sm-3 leftModule" ></div>
            <div id="dynamic-home-content" class="col-md-8 col-sm-7 bgTexture default_breadcrumb" style="background:#f7f7f7;"></div>
            <input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
            <div class="clearDiv"></div>
            <?php include_once TEMPLATE_PATH . 'ads_people.php'; ?>
        </div>   
    </div>
</div>
<div class="modal fade bs-example-modal-sm" id="share_record_from_front" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Share Record <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="share_record_front" id="share_record_front" method="POST" action="">
                        <input type="hidden" id="previous_folder_id" name="previous_folder_id" value="" > 
                        <input type="hidden" id="thoumb_id" name="thoumb_id" value="" >
                        <input type="text" id="user-type1" name="user-type1" value="" style="display:none">
                        <input type="text" id="record_share_id" name="record_share_id" value="" style="display:none">
                        <input type="text"  name="timestamp_value" id="timestamp_valueid" value="<?php echo time(); ?>" style="display:none">
                        <div class="clearfix"></div>
                        <?php if(isset($_SESSION['aib']['user_data']) && $_SESSION['aib']['user_data']['user_type'] =='A' ){ ?>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Shared With :</label>
                                <div class="col-sm-7">
                                    <label class="container col-sm-6">Public User
                                        <input type="radio" class="shared-with" checked="checked" name="shared_type" value="public user">
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="container col-sm-6">Society Admin
                                        <input type="radio" class="shared-with" name="shared_type" value="society">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Sender's Name*  :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="sharing_name" id="sharing_name" placeholder="Enter your name here">
                            </div>
                        </div>
                        <div class="form-group public-user">
                            <label for="" class="col-sm-4 control-label">Recipient Email Address* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="share_emailId" id="share_emailId" placeholder="Enter recipient email address here">
                            </div>
                        </div>
                        <div class="form-group society">
                            <label for="" class="col-sm-4 control-label">Society Admin* :</label>
                            <div class="col-sm-7">
                                <select id="selected_society" multiple name="selected_society[]" class="demo-default" autocomplete="off" placeholder="Enter society here"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Message :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="share_message" id="share_massage" placeholder="Enter your message"></textarea>
                            </div>
                        </div>
                    </form>
                    <div class="text-center"><button class="btn btn-success" type="submit" id="share_record_button">Share</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bs-example-modal-sm" id="copy_scrapbook_popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Copy Scrapbook <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" name="copy_scrapbook_form" id="copy_scrapbook_form" method="POST" action="">
                    <input type="hidden" id="scrapbook_id" name="scrapbook_id" value="" > 
                    <input type="hidden" id="scrapbook_parent_id" name="scrapbook_parent_id" value="" >
                    <input type="hidden" id="item_user_id" name="item_user_id" value="">
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Scrapbook Title* :</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="scrapbook_title" id="scrapbook_title" placeholder="Enter scrapbook title" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label"></label>
                        <div class="col-sm-7">
                            <button class="btn btn-success" type="button" id="copy_scrapbook_button">Copy</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; 
if(!empty($_SESSION["record_id"])){unset($_SESSION["record_id"]);}
if (isset($_REQUEST['record_id']) && !empty($_REQUEST['record_id'])) {
    $_SESSION["record_id"] = $_REQUEST['record_id'];
    if(!(isset($_REQUEST['type']))){
    ?>
    <script>
        $(document).ready(function () {
            var folder_id = '<?php echo $_REQUEST['record_id']; ?>';
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_parent_id', folder_id: folder_id},
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result != '') {
                        getPublicItemDetailsById(result);
                        getSocietyHeaderImage(result);
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 590)');
                }
            });
            $('#copy_scrapbook_form').validate({
                rules: {
                    scrapbook_title: {
                        required: true
                    }
                },
                messages: {
                    scrapbook_title: {
                        required: "Please enter scrapbook title"
                    }
                }
            });
        });
    </script>
<?php } } ?>
<script type="text/javascript">
    var PUBLIC_USER_ROOT = "<?php echo PUBLIC_USER_ROOT; ?>";
    $(document).ready(function () {
        getAllSocietyList('selected_society');
        $(document).on('click', '.shared-with', function(){
            var selected_option = $(this).val();
            if(selected_option == 'society'){
                $('.public-user').hide();
                $('.society').show();
                $('#share_emailId').val('');
            }else{
                $("#selected_society")[0].selectize.clear();
                $('.society').hide();
                $('.public-user').show();
            }
        });
	<?php if (isset($_SESSION['archive_header_image']) && $headerImage != '' && isset($_REQUEST['folder_id']) && $_REQUEST['folder_id'] != PUBLIC_USER_ROOT) { ?>
        $('.bannerImage').css('background-image', 'url(<?php echo $_SESSION['archive_header_image']; ?>)'); 
       <?php }else{ ?>
           $('.bannerImage').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
       <?php } ?>
        var folder_id = '<?php echo $folder_id; ?>';
        var previous_id = '<?php echo $previous; ?>';
        if (folder_id == 1)
        {
            $('#client_logo').hide();
        }
        var previousObj = [];
        if (previous_id != '') {
            previousObj = previous_id.split(',');
        }
        $('#current-item-id').val(folder_id);
        $('#previous-item-id').val(JSON.stringify(previousObj));
        getPublicItemDetailsById(folder_id);
        getPublicTreeData(folder_id);
        $(document).on('click', '.getItemDataByFolderId', function () {
            var current_id = $('#current-item-id').val();
            var previous_id_obj = JSON.parse($('#previous-item-id').val());
            previous_id_obj.push(current_id);
            var item_folder_id = $(this).attr('data-folder-id');
            $('#current-item-id').val(item_folder_id);
            $('#previous-item-id').val(JSON.stringify(previous_id_obj));
            getPublicItemDetailsById(item_folder_id);
            if (item_folder_id == PUBLIC_USER_ROOT)
            {
                $('#client_logo').hide();
                $('#home_page_create_my_own_box').show();
            }

        });
        $(document).on('click','.load_more_data', function() {
            var apiListCount = $('#apiResCount').val();
            var publicListCount = $('#public_List_count_val').val();
            if(parseInt(apiListCount) >= parseInt(publicListCount)){
                    var people_sub_group = $('#people_sub_group').val(); 
                    var people_sub_group_type = '';
                    if(typeof(people_sub_group) != "undefined" && people_sub_group !== null) {
                      people_sub_group_type = people_sub_group;
                    }
                    var listCount = '<?php echo PUBLIC_COUNT_PER_PAGE; ?>';
                    var start = $('#start_page').val();
                    var group_item_type = $('#group_item_type').val();
                    var folder_id = $('#current-item-id').val();
                    var scroll_loading_more_content = $('#scroll-loading-more-content').val();
                    if(start !='' && start!='NaN' && scroll_loading_more_content =='no'){
                        $(".load_more_data button").text("Loading...");
                        $('.load_more_data').attr('disabled', true);
                        $('.load_more_people_list_data').prop('disabled', true);
                        getPublicItemDetailsById(folder_id,parseInt(start),listCount,group_item_type,people_sub_group_type); 
                    }
            }else{
                 $(".load_more_data").hide();
            }
       });
    });
$(document).on('click', '.share_front_record', function () {
        $('.loading-div').show();
        var record_id = $(this).children("div").attr("data-record-id-value");
        var user_type = $(this).children("div").attr("user-type1");
        var thoumb_id = $(this).children("div").attr("thumb-id-val"); 
        var type =  $(this).children("div").attr("type");
        var previous_id = $( ".listing li:last-child").attr('data-folder-id');
        if(type == 'scrapbook_record_share'){
            $('#previous_folder_id').val(previous_id);
        }
        
        $('#record_share_id').val(record_id);
        $('#user_type1').val(user_type);
        $('#thoumb_id').val(thoumb_id);
        $('#share_record_from_front').modal('show');
         $('.loading-div').hide();
         });
    $("#share_record_front").validate({
        rules: {
            sharing_name: {
                required: true
            },
            share_emailId: {
                required: {
                    depends:function(){
                        $(this).val($.trim($(this).val()));
                        return true;
                    }
                },
                email: true
            }
        },
        messages: {
            sharing_name: {
                required: "Please enter your name"
            },
            share_emailId: {
                required: "Email-ID is required.",
                email: "Please enter correct email id"
            }
        }
    });
    $(document).on('click', '#share_record_button', function () {
        if ($("#share_record_front").valid() && $('#share_record_front').val() == '') {
            $('.loading-div').show();
            var checked_option = $("input[name='shared_type']:checked"). val();
            var mode = 'share_item';
            var selected_society = '';
            if(checked_option == 'society'){
                mode = 'share_item_with_society_admin';
                selected_society = $('#selected_society').val();
            }
            var record_id = $('#record_share_id').val();
            var email = $('#share_emailId').val();
            var share_massage = $('#share_massage').val();
            var sharing_name = $('#sharing_name').val();
            var user_type = $('#user_type1').val();
            var timestamp_value = $('#timestamp_valueid').val();
            var thoumb_id = $('#thoumb_id').val(); 
            var previous_id = $('#previous_folder_id').val();
            if(previous_id !=''){
            var folder_id  = previous_id+'&type=scrapbook_record'; 
			var new_fol_id = previous_id;
            }else{
            var folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
			var new_fol_id = folder_id;
            }
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: mode, email: email, folder_id: folder_id, item_id: record_id, thoumb_id: thoumb_id, timestamp_value: timestamp_value,share_type:'record',user_type:user_type,share_massage:share_massage,sharing_name:sharing_name,new_fol_id:new_fol_id,selected_society: selected_society},
                success: function (data) {
                    var record = JSON.parse(data);
                    $('.loading-div').hide();
                    showPopupMessage(record.status, record.message);
                    if (record.status == 'success') {
                        setTimeout(1000);
                          $('#share_record_front')[0].reset();
                    }
                     $('.loading-div').hide();
                    $('#share_record_from_front').modal('hide');
                    $('#share_emailId').val('');
                    
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 591)');
                }
            });
        }

    });
    function getSocietyHeaderImage(folder_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_society_details',folder_id: folder_id },
            success: function (response) {
                var record = JSON.parse(response);
                var archive_image_url = '<?php echo ARCHIVE_IMAGE; ?>';
                if(record.banner){
                    $('.bannerImage').css('background-image', 'url('+archive_image_url+record.banner+')');
                }
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 592)');
            }
        });
    }
    
    $(document).on('click', '.copy_scrapbook_to_own_scrapbook', function(){
        var item_id      = $(this).attr('data-item-id');
        var item_parent  = $(this).attr('data-parent-id');
        var item_title   = $(this).attr('data-item-name');
        var item_user_id = $(this).attr('data-item-user-id');
        $('#scrapbook_id').val(item_id);
        $('#scrapbook_parent_id').val(item_parent);
        $('#scrapbook_title').val(item_title);
        $('#item_user_id').val(item_user_id);
        $('#copy_scrapbook_popup').modal('show');
    });
    
    $(document).on('click', '#copy_scrapbook_button', function(){
        var scrapbook_id        = $('#scrapbook_id').val();
        var scrapbook_parent_id = $('#scrapbook_parent_id').val();
        var scrapbook_title     = $('#scrapbook_title').val();
        var item_user_id        = $('#item_user_id').val();
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'copy_scrapbook',scrapbook_id: scrapbook_id, scrapbook_parent_id: scrapbook_parent_id, scrapbook_title: scrapbook_title, item_user_id: item_user_id },
            success: function (response) {
                var record = JSON.parse(response);
                showPopupMessage(record.status,record.message);
                $('#copy_scrapbook_popup').modal('hide');
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 593)');
            }
        });
    });

</script>