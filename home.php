<?php
require_once dirname(__FILE__) . '/config/config.php';
if (isset($_REQUEST['folder_id']) && $_REQUEST['folder_id'] != '') {
    $folder_id = $_REQUEST['folder_id'];
} else {
    $folder_id = HISTORICAL_SOCITY_ROOT; //1
}
include_once 'get_template_name.php';
include_once 'get_unclaimed_society_flag.php';
$previous = '';
if (isset($_REQUEST['previous']) && $_REQUEST['previous'] != '') {
    $previous = $_REQUEST['previous'];
}

include_once 'get_template_name.php';

$template_breadcrumb_class = '';

if ($societyTemp == 'custom2') {
    $template_breadcrumb_class = 'custom2_breadcrumb';
    include_once COMMON_TEMPLATE_PATH . 'header2.php';
} else if ($societyTemp == 'custom1') {
    $template_breadcrumb_class = 'custom1_breadcrumb';
    include_once COMMON_TEMPLATE_PATH . 'details-header.php';
} else {
    $template_breadcrumb_class = 'default_breadcrumb';
    include_once COMMON_TEMPLATE_PATH . 'header.php';
}

function getTreeData($folderId = '') {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_path",
            "obj_id" => $folderId,
            "opt_get_property" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

if (!isset($_SESSION['aib']['session_key'])) {
    $postData = array(
        "_id" => APIUSER,
        "_key" => APIKEY
    );
    $apiResponse = aibServiceRequest($postData, 'session');
    if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
        $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
    }
} else {
    $sessionKey = $_SESSION['aib']['session_key'];
}
$treeDataArray = getTreeData($folder_id);
$item_tree_path=array();
if(count($treeDataArray)){
	for($tCount=2;$tCount<=count($treeDataArray)-1;$tCount++){
	
		$item_tree_path[]=$treeDataArray[$tCount]['item_id'];
	}
}
$treeDataArrayItemType = array_column($treeDataArray, 'item_type');
$treeDataArrayItemTypeAgKey = array_search('AG', $treeDataArrayItemType);
$parentFolderId = $treeDataArray[$treeDataArrayItemTypeAgKey]['item_id'];

if (isset($treeDataArray[1])) {
    $_SESSION['archive_logo_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_logo_image'];
    $_SESSION['archive_header_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_header_image'];
    $_SESSION['archive_details_image'] = ARCHIVE_IMAGE . $treeDataArray[1]['properties']['archive_details_image'];
}
$headerImage = end(explode('/', $_SESSION['archive_header_image']));
$logoImage = end(explode('/', $_SESSION['archive_logo_image']));
$banner_image_full = '';
/* if(count($treeDataArray) == 1 && $treeDataArray[0]['item_type'] == 'IT'){
    $banner_image_full = 'bannerImage_full';
  } */
if ($_REQUEST['folder_id'] == '1') {
    $banner_image_full = 'bannerImage_full';
}
?>

<?php
    if (!isset($_SESSION['aib']['session_key']))
    {
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );

        $apiResponse = aibServiceRequest($postData, 'session');
        if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '')
        {
            $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
        }
    }
    else
    {
        $sessionKey = $_SESSION['aib']['session_key'];
    }
    $PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => $sessionKey,
        'obj_id' => 1
    );
    $artifacts_status=0; $home_logo = ''; $home_banner = ''; $home_page_title = ''; $home_page_description = '';
    $Result = aibServiceRequest($PostData, "browse");
    if ($Result['status'] == 'OK') {
        $artifacts_status= $Result['info']['records']['artifacts_status'];
        $home_logo = !empty($Result['info']['records']['default_home_logo'])?'/admin/tmp/'.$Result['info']['records']['default_home_logo']:'/public/images/society_logo.png';
        $home_banner = !empty($Result['info']['records']['default_home_header_image'])?'/admin/tmp/'.$Result['info']['records']['default_home_header_image']:'/public/images/new_layout_bg.jpg';
        $home_page_title = isset($Result['info']['records']['home_page_title']) ? $Result['info']['records']['home_page_title'] : ""; 
        $home_page_description = isset($Result['info']['records']['home_page_description']) ? $Result['info']['records']['home_page_description'] : ""; 
    }
    
?>

<!--<div class="header_img bgBlue_header">
    <div class="bannerImage bannerImage_society <?php echo $banner_image_full; ?>"></div>
    <?php if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
        <input type="button" class="btn btn-info claim_this_historical" onclick="openClaimPopup(event);" value="Claim This Historical" />
    <?php } ?>
    <?php
	/***** Fix start Issue Id 2029-- 04-oct-2022 ****************/
    if (isset($_REQUEST['show_text'])) {
        $description = ($_REQUEST['show_text'] == 'yes') ? '<p class="welcomeText">We help historical societies, town museums, genealogical organizations, and stakeholders advance their mission by digitizing newspaper and printed collections for preservation and online access.</p>' : 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s';
        $title = ($_REQUEST['show_text'] == 'yes') ? 'Does your collection include newspapers or other large and bound archives?' : 'Lorem Ipsum';
        ?>
        <div class="headerImage animate fadeInRightBig two"><h4 style=""><?php echo $title; ?></h4>
            <div class="descText"><?php echo $description; ?><ul><li><div class="readMoreLink"><a target="_blank" href="https://www.archiveinabox.com/default/">Investigate our Scanning Services</a></div></li><li><div class="readMoreLink"><a target="_blank" href="/interstitial-page2.html">Discover Hosting Options</a></div></li><li><div class="readMoreLink"><a href="why-us.html">Learn More</a></div></li></ul></div></div>
    <?php } /***** Fix end Issue Id 2029-- 04-oct-2022 ****************/?>
    <?php if (strpos($_SERVER['HTTP_REFERER'], 'search.php') !== false) { ?>
        <div class="pull-right btn search-button" id="backDivURLHome"><a id="backURL" href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt=""> <span>Back To Search</span></a></div>
    <?php } ?>
    <div class="clientLogo col-md-2">
        <img id="client_logo" style="width:200px;" src="" />
        <div id="connect_links"></div>
    </div>    
    <!-<div id="top_right_position"></div-->

<!--</div>-->

<div class="header_img bgBlue_header">
            <div class="clientLanding bannerImage_society" style="background-image:url(<?=$home_banner;?>);"></div><!-- bannerImage clientLanding-->
            <div class="clientLogo col-md-2"><img id="" style="width:200px;" src="<?=$home_logo;?>" /></div> <!--clientLogo_society-->
            <!--div id="top_right_position"></div-->
            <?php /*if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
                <input type="button" class="btn btn-info claim_this_historical" onclick="openClaimPopup(event);" value="Claim This Historical" />
            <?php }*/ ?>
        </div>


<div class="clearfix"></div>

<?php
if ($societyTemp == 'custom2') {
    include_once 'home_custom2.php';
} else if ($societyTemp == 'custom1') {
    include_once 'home_custom1.php';
} else {
    include_once 'home_default.php';
}
?>

<div class="modal fade bs-example-modal-sm" id="share_record_from_front" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Share Record <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="share_record_front" id="share_record_front" method="POST" action="">
                        <input type="hidden" id="thoumb_id" name="thoumb_id" value="" >
                        <input type="hidden" id="new_fol_id" name="new_fol_id" value="" >
                        <input type="text" id="user-type1" name="user-type1" value="" style="display:none">
                        <input type="text" id="record_share_id" name="record_share_id" value="" style="display:none">
                        <input type="text"  name="timestamp_value" id="timestamp_valueid" value="<?php echo time(); ?>" style="display:none">
                        <div class="clearfix"></div>
                        <?php if (isset($_SESSION['aib']['user_data']) && $_SESSION['aib']['user_data']['user_type'] == 'A') { ?>
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
                            <label for="" class="col-sm-4 control-label">Sender's Name* :</label>
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

<div class="modal fade bs-example-modal-sm" id="connect_with_other_society" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Connect with Society <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4" id="society_tree_data"></div>
                    <div class="col-md-8">
                        <div class="bgOverlay_loader"><img class="loading-img_items" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." /></div>
                        <div id="sub-group-records"></div>
                    </div>
                </div>
                <div class="text-center connect-footer">
                    <button class="btn btn-success" type="submit" id="society_connection_button">Submit</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
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
<div class="modal fade" id="claimed_message_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Claim This Archive</h4>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <p id="claimed_message_text"></p>
                    <div class="text-center">
                        <?php
                        $claimed_society_registration_url = "#";
                        if ($is_unclaimed_society && $is_unclaimed_society == '1') {
                            $claimed_society_registration_url = 'register.html?q=' . encryptQueryString('folder_id=' . $folder_id . '&society_template=' . $societyTemp);
                        }
                        ?>
                        <a class="btn btn-success" href="<?php echo $claimed_society_registration_url; ?>">Continue with Claim</a>
                        <a class="btn btn-danger" href="#" data-dismiss="modal" aria-label="Close">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<?php
if ($societyTemp == 'custom2') {
    include_once COMMON_TEMPLATE_PATH . 'details-footer.php'; //include_once COMMON_TEMPLATE_PATH . 'footer2.php';
} else if ($societyTemp == 'custom1') {
    include_once COMMON_TEMPLATE_PATH . 'details-footer.php';
} else {
    include_once COMMON_TEMPLATE_PATH . 'footer_new.php';
}

$p_folder_id = 0;
$p_folder_id = $_REQUEST['folder_id'];
if (isset($_REQUEST['record_id']) && !empty($_REQUEST['record_id'])) {
    $_SESSION["record_id"] = $_REQUEST['record_id'];
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
                        getSocietyHeaderImage(result);
                        getItemDetailsById(result);
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 173)');
                }
            });

        });
    </script>
	<?php
} else {
    unset($_SESSION["record_id"]);
}
?>

//<?php if ($parentFolderId == 77292) { ?>
//	<script type="text/javascript">
//		$(document).ready(function () {
//			$('body').addClass('heritage_society');
//		});
//	</script>
//<?php } ?>

<script type="text/javascript">
    var HISTORICAL_SOCITY_ROOT = "<?php echo HISTORICAL_SOCITY_ROOT; ?>";
    $(document).ready(function () {
        $('#arrow_slide_left').click(function () {
            left_right_arrow_slide('in');
        });
        $('#arrow_slide_right').click(function () {
            left_right_arrow_slide('out');
        });

        getAllSocietyList('selected_society');
        $(document).on('click', '.shared-with', function () {
            var selected_option = $(this).val();
            if (selected_option == 'society') {
                $('.public-user').hide();
                $('.society').show();
                $('#share_emailId').val('');
            } else {
                $("#selected_society")[0].selectize.clear();
                $('.society').hide();
                $('.public-user').show();
            }
        });

        $('#myTableSG_paginate').hide();
        var folder_id = '<?php echo $folder_id; ?>';
        var previous_id = '<?php echo $previous; ?>';

        if (folder_id == HISTORICAL_SOCITY_ROOT)
        {
            $('#client_logo').hide();
        }
        var previousObj = [];
        if (previous_id != '') {
            previousObj = previous_id.split(',');
        }
        $('#current-item-id').val(folder_id);
        $('#previous-item-id').val(JSON.stringify(previousObj));
        getItemDetailsById(folder_id, '', '', '', 'home');
        getTreeData(folder_id);
        $(document).on('click', '.getItemDataByFolderId', function () {
            var current_id = $('#current-item-id').val();
            var previous_id_obj = JSON.parse($('#previous-item-id').val());
            previous_id_obj.push(current_id);
            var item_folder_id = $(this).attr('data-folder-id');
            $('#current-item-id').val(item_folder_id);
            $('#previous-item-id').val(JSON.stringify(previous_id_obj));
            getItemDetailsById(item_folder_id);
            if (item_folder_id == HISTORICAL_SOCITY_ROOT)
            {
                $('#client_logo').hide();
                $('#home_page_register_your_society').show();
            }

        });
<?php if (isset($_SESSION['archive_header_image']) and $headerImage != '' and isset($_REQUEST['folder_id']) and $_REQUEST['folder_id'] != HISTORICAL_SOCITY_ROOT) { ?>
            $('.bannerImage').css('background-image', 'url(<?php echo $_SESSION['archive_header_image']; ?>)');
<?php } else { ?>
            //$('.bannerImage').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
            $('.bannerImage').css('background-image', 'url(public/images/default-header-img.jpg)');
<?php } ?>
<?php if (isset($_SESSION['archive_logo_image']) and $logoImage != '' and isset($_REQUEST['folder_id']) and $_REQUEST['folder_id'] != HISTORICAL_SOCITY_ROOT) { ?>
            $('#client_logo').attr('src', '<?php echo $_SESSION['archive_logo_image']; ?>');
<?php } ?>
        $(document).on('click', '.load_more_data', function () {
            var apiListCount = $('#apiResCount').val();
            var publicListCount = $('#publicListcount').val();
            if (parseInt(apiListCount) > parseInt(publicListCount)) {
                var start = $('#satar_result').val();
                var listCount = '<?php echo PUBLIC_COUNT_PER_PAGE; ?>';
                var group_item_type = $('#group_item_type').val();
                var folder_id = $('#current-item-id').val();
                var scroll_loading_more_content = $('#scroll-loading-more-content').val();
                if (start != '' && start != 'NaN' && scroll_loading_more_content == 'no') {
                    $(".load_more_data button").text("Loading...");
                    $('.load_more_data').attr('disabled', true);
                    $('.load_more_list_data_val').prop('disabled', true);
                    getItemDetailsById(folder_id, start, listCount, group_item_type);
                }
            } else {
                $(".load_more_data").hide();
            }
        });
        generateDetailsPageLink();
    });
    $(document).on('keyup', '#search_text', function (e) {
        var key = e.which;
        if (key == 13)
        {
            $('#search_in_archive_home').click();
        }
    });
    $(document).on('click', '.setpagenumber', function () {
        var folder_id = $('#listliheaddata li:last-child').attr("table-page-id");
        var current_page_num = $('#myTable').DataTable().page.info();
        var pageno = current_page_num.page + 1;
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'set_pagenumber_data', folder_id: folder_id, current_page_num: pageno},
            success: function (data) {
            },
            error: function () {
                //showPopupMessage('error', 'Something went wrong, Please try again');
            }
        });
    });

    function pageMoveToItemDetail(id, child_count, item_type, item_parent) {
        var current_page_num = $('#myTable').DataTable().page.info();
        var pageno = current_page_num.page + 1;
        var previous_id_obj = JSON.parse($('#previous-item-id').val());
        var scrapbook_item = $('#is_scrapbook_item').val();
        var extraLink = '';
        if (scrapbook_item == 'yes') {
            extraLink = '&scrapbook_item=' + scrapbook_item + '&flg=scrapbook';
        }
        var queryString = "folder_id=" + id + '&previous=' + previous_id_obj + ',' + $('#current-item-id').val() + '&society_template=<?php echo $societyTemp; ?>&page=' + pageno + extraLink;
        if (item_type == 'IT') {
            queryString = "folder_id=" + item_parent + '&itemId=' + id + '&previous=' + previous_id_obj + ',' + $('#current-item-id').val() + '&society_template=<?php echo $societyTemp; ?>&page=' + pageno + extraLink;
        }
        getEncryptedString(queryString, 'item-details.html');
    }

    $(document).on('click', '#share_front_record', function () {
        $('#share_record_from_front').modal('show');
        $('.loading-div').show();
        var record_id = $(this).children("div").attr("data-record-id-value");
        var user_type = $(this).children("div").attr("user-type1");
        var thoumb_id = $(this).children("div").attr("thumb-id-val");
        var new_fol_id = $('#listliheaddata li:last-child').attr("data-folder-id");
        $('#record_share_id').val(record_id);
        $('#user_type1').val(user_type);
        $('#thoumb_id').val(thoumb_id);
        $('#new_fol_id').val(new_fol_id);
        $('.loading-div').hide();
    });
    function get_public_username(email) {
        var data = [];
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_public_user_email'},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    data.push(record[i].user_login);
                }
                var ms1 = $('#sare_item_username').magicSuggest({data: data, value: email});
                ms1.clear();
                ms1.setData(data);
                ms1.setValue(email);
                $('#share_record_from_front').modal('show');
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 174)');
            }
        });
    }
    $("#share_record_front").validate({
        ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
        rules: {
            share_emailId: {
                required: {
                    depends: function () {
                        $(this).val($.trim($(this).val()));
                        return true;
                    }
                },
                email: true
            },
            sharing_name: {
                required: true
            },
            selected_society: {
                required: true
            }
        },
        messages: {
            share_emailId: {
                required: "Email-ID is required.",
                email: "Please enter correct email id"
            },
            sharing_name: {
                required: "Please enter your name"
            },
            selected_society: {
                required: "Please enter society name"
            }
        }
    });
    $(document).on('click', '#share_record_button', function () {
        if ($("#share_record_front").valid() && $('#share_record_front').val() == '') {
            $('.loading-div').show();
            var checked_option = $("input[name='shared_type']:checked").val();
            var mode = 'share_item';
            var selected_society = '';
            if (checked_option == 'society') {
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
            var new_fol_id = $('#new_fol_id').val();
            var folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: mode, email: email, folder_id: folder_id, item_id: record_id, thoumb_id: thoumb_id, timestamp_value: timestamp_value, share_type: 'record', user_type: user_type, share_massage: share_massage, sharing_name: sharing_name, new_fol_id: new_fol_id, selected_society: selected_society},
                success: function (data) {
                    var record = JSON.parse(data);
                    $('.loading-div').hide();
                    showPopupMessage(record.status, record.message);
                    if (record.status == 'success') {
                        setTimeout(1000);
                    }
                    $('#share_record_from_front').modal('hide');
                    $('#share_emailId').val('');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 175)');
                }
            });
        }

    });
    $(document).on('click', '#share_item_button', function () {
        if ($("#share_items_front").valid() && $('#share_items_front').val() == '') {
            $('.loading-div').show();
            var item_id = $('#sharing_item_id').val();
            var email = $('#share_emailId').val();
            var share_massage = $('#share_massage').val();
            var user_type = $('#user_type1').val();
            var timestamp_value = $('#timestamp_valueid').val();
            var folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
            var new_fol_id = $('#new_fol_id').val();
            var username = [];
            var email_id = $('.email1').each(function () {
                username.push($(this).text());
            });
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'share_item', username: username, email: email, folder_id: folder_id, item_id: item_id, thoumb_id: item_id, timestamp_value: timestamp_value, share_type: 'item', user_type: user_type, share_massage: share_massage, new_fol_id: new_fol_id},
                success: function (data) {
                    var record = JSON.parse(data);
                    $('.loading-div').hide();
                    showPopupMessage(record.status, record.message);
                    if (record.status == 'success') {
                        setTimeout(1000);
                    }
                    $('#share_record_from_front').modal('hide');
                    $('#share_emailId').val('');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 176)');
                }
            });
        }

    });
    function getSocietyHeaderImage(folder_id) {
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_society_details', folder_id: folder_id},
            success: function (response) {
                var record = JSON.parse(response);
                var archive_image_url = '<?php echo ARCHIVE_IMAGE; ?>';
                if (record.logo) {
                    $('#client_logo').attr('src', archive_image_url + record.logo);
                }
                if (record.banner) {
                    $('.bannerImage').css('background-image', 'url(' + archive_image_url + record.banner + ')');
                } else {
                    //$('.bannerImage').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
                    $('.bannerImage').css('background-image', 'url(public/images/default-header-img.jpg)');
                }
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 177)');
            }
        });
    }
    $(document).on('click', '.copy_scrapbook_to_own_scrapbook', function () {
        var item_id = $(this).attr('data-item-id');
        var item_parent = $(this).attr('data-parent-id');
        var item_title = $(this).attr('data-item-name');
        var item_user_id = $(this).attr('data-item-user-id');
        $('#scrapbook_id').val(item_id);
        $('#scrapbook_parent_id').val(item_parent);
        $('#scrapbook_title').val(item_title);
        $('#item_user_id').val(item_user_id);
        $('#copy_scrapbook_popup').modal('show');
    });

    $(document).on('click', '#copy_scrapbook_button', function () {
        var scrapbook_id = $('#scrapbook_id').val();
        var scrapbook_parent_id = $('#scrapbook_parent_id').val();
        var scrapbook_title = $('#scrapbook_title').val();
        var item_user_id = $('#item_user_id').val();
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'copy_scrapbook', scrapbook_id: scrapbook_id, scrapbook_parent_id: scrapbook_parent_id, scrapbook_title: scrapbook_title, item_user_id: item_user_id},
            success: function (response) {
                var record = JSON.parse(response);
                showPopupMessage(record.status, record.message);
                $('#copy_scrapbook_popup').modal('hide');
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 178)');
            }
        });
    });

    function openClaimPopup(e) {
        e.preventDefault();
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_claimed_popup_message', user_id: 1, type: 'CM'},
            success: function (data) {
                console.log(data);
                var result = JSON.parse(data);
                if (result.status == 'success') {
                    $('#claimed_message_text').html(result.message);
                    $('#claimed_message_modal').modal('show');
                } else {
                    showPopupMessage('error', 'error', 'Something went wrong, Please try again. (Error Code: 179)');
                }
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 180)');
            }
        });
    }

    function left_right_arrow_slide(s) {
        if (s == 'in') {
            $('#dynamic-tree-content').addClass('hide');
        }else{
            $('#dynamic-tree-content').removeClass('hide');
        }
        if ($('#dynamic-tree-content').hasClass('hide') == true) {
            $('.default_breadcrumb,.custom1_breadcrumb,.custom2_breadcrumb').removeClass('col-md-8 col-sm-8');
            $('.default_breadcrumb,.custom1_breadcrumb,.custom2_breadcrumb').addClass('col-md-10 col-sm-10');
            $('#arrow_slide_left').addClass('hide');
            $('#arrow_slide_right').removeClass('hide');
        } else {
            $('.default_breadcrumb,.custom1_breadcrumb,.custom2_breadcrumb').removeClass('col-md-10 col-sm-10');
            $('.default_breadcrumb,.custom1_breadcrumb,.custom2_breadcrumb').addClass('col-md-8 col-sm-8');
            $('#arrow_slide_left').removeClass('hide');
            $('#arrow_slide_right').addClass('hide');
        }
    }
</script>
