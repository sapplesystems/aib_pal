<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$loggedInUserId = $_SESSION['aib']['user_data']['user_id'];
$user_top_folder = $_SESSION['aib']['user_data']['user_top_folder'];
?>
<style type="text/css">
    #advertisement_listing_table th{text-align: center;}
    #advertisement_listing_table td{text-align: center !important;}
    .adtable th{text-align: center;}
    .adv_action{cursor: pointer;}
    #ad_title_name{
        font-weight: bold;
        text-transform: uppercase;
        background-color: #ededed;
        color: #15345a;
        border-top: 1px solid #cccccc;
        padding: 10px;
        margin: 0;
        border-radius: 0;
        font-size: 16px;
        width: 100%;
    }
    .crop_save_msg{padding: 0px 0px 0px 50px;position: relative;top: 7px;}
	.jcrop-keymgr{display:none !important;}
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Manage Advertisement</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Advertisement</li>
        </ol>
        <h4 class="list_title text-center"> <span class="pull-left">Manage Advertisement</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <!--div class="row">
            <div class="col-md-12">
                <div class="add_new_ad">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Display Order :</label>
                        </div>
                        <div class="col-md-10">
                            <label>
                                <input type="radio" name="sorting_order" class="sorting_order" id="sorting_order0" value="2" /> Random
                                &nbsp;&nbsp;
                                <input type="radio" name="sorting_order" class="sorting_order" id="sorting_order1" value="1" /> Sorted
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div-->
        <div class="row">
            <div class="col-md-12 text-center marginBottom10">
                <input type="button" class="btn btn-info" value="Add New Advertisement" onclick="addNewAd();" />
            </div>
        </div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <div class="tableScroll">
                    <form id="advertisement_frm" name="advertisement_frm" method="post" class="form-horizontal" enctype="multipart/form-data">
                        <div class="hide add_new_ad" id="add_new_ad_table">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Advertisement Title :</label>
                                    <input class="form-control" type="text" name="ad_title" id="ad_title" value="" />
                                </div>
                                <div class="col-md-3">
                                    <label>Advertisement Alt Title :</label>
                                    <input class="form-control" type="text" name="ad_alt_title" id="ad_alt_title" value="" />
                                </div>
                                <div class="col-md-3">
                                    <label>Advertisement Link Url :</label>
                                    <input class="form-control" type="text" name="ad_url" id="ad_url" value="" />
                                    <span class="help-text">(Must be a valid url. Example: http://www.google.com/ )</span>
                                </div>
                                <div class="col-md-3">
                                    <label>Advertisement Banner :</label>
                                    <input class="form-control" type="file" name="original_file" id="original_file" />
                                    <span class="help-text">(Must be at least 300 x 250 (Width x Height) )</span>
                                    <div class="clearfix"></div>
                                    <input type="hidden" name="advertisement_x" id="advertisement_x" />
                                    <input type="hidden" name="advertisement_y" id="advertisement_y" />
                                    <input type="hidden" name="advertisement_w" id="advertisement_w" />
                                    <input type="hidden" name="advertisement_h" id="advertisement_h" />
                                </div>
                            </div>


                            <div class="row">
                                <!--div class="col-md-12">
                                    <label>Advertisement Link Url :</label>
                                    <input class="form-control" type="text" name="ad_url" id="ad_url" value="" />
                                    <span class="help-text">(Must be a valid url. Example: http://www.google.com/ )</span>
                                </div>
                                <div class="col-md-4">
                                    <label>Advertisement Position :</label>
                                    <input class="form-control" type="text" name="ad_sort_order" id="ad_sort_order" value="1" />
                                </div>
                                <div class="col-md-4">

                                    <label>Apply to child :</label>
                                    <input type="checkbox" name="inherit_flag" id="inherit_flag" value="1" />
                                </div-->
                            </div>

                            <div class="row text-center">
                                <div class="col-md-12">
                                    <input type="hidden" name="ad_item_id" id="ad_item_id" value="<?php echo $user_top_folder; ?>">
                                    <input type="hidden" name="ad_update_id" id="ad_update_id" value="">
                                    <input type="hidden" name="disable_flag" id="disable_flag" value="Y">
                                    <input class="btn btn-info" type="submit" name="advertisement_frm_sub" id="advertisement_frm_sub" value="Submit" />
                                    <input class="btn btn-default" type="button" value="Cancel" onclick="closeAdForm();" />
                                </div>
                            </div>
                        </div>

                        <div class="tableStyle">
                            <table class="table" cellspacing="0" cellpadding="0" border="0" width="100%" id="advertisement_listing_table">
                                <thead>
                                    <tr>
                                        <th>Ad Title</th>
                                        <th>Ad Alt Title</th>
                                        <th>File</th>
                                        <th>Url</th>
                                        <!--th>Apply to child</th>
                                        <th>Position</th-->
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="advertisement_tbody"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="editUserPopup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Update Administrator <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="administrator_form" name="administrator_form" method="POST" class="form-horizontal" action="">
                    <input type="hidden" name="user_id" id="user_id" value="">
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Archive Group</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_name"  name="archive_name" disabled="disabled">
                                    <option value="">- Select -</option> 
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">User Login</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" readonly="readonly" name="user_login" id="user_login" value="" />
                        </div>
                    </div>
                    <?php if ($_SESSION['aib']['user_data']['user_type'] == 'A') { ?>
                        <div class="form-group">
                            <label class="col-xs-3 control-label">User Type</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" id="user_access_type"  name="user_access_type">
                                        <option value="primary">Primary</option>
                                        <option value="secondary">Secondary</option> 
                                    </select>
                                </span>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">User Email</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" name="user_email" id="user_email" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">New Password</label>
                        <div class="col-xs-7">
                            <input type="password" class="form-control" name="user_password" id="user_password" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Confirm New Password</label>
                        <div class="col-xs-7">
                            <input type="password" class="form-control" name="confirm_user_password" id="confirm_user_password" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="update_administrator_form" id="update_administrator_form">Update</button>
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="clearAssistantForm">Clear Form</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bs-example-modal-lg" data-backdrop="static" id="crop_uploading_image" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header form_header">
                <button class="btn btn-info borderRadiusNone" id="close_crop_popup">Crop & Save</button>
                <button class="btn btn-info borderRadiusNone cancel_crop_popup">Cancel</button>
                <button type="button" class="close cancel_crop_popup" data-dismiss="modal">&times;</button>
                <span class="crop_save_msg">Use the cropping tool below to adjust the height of your ad.</span>
            </div>
            <div class="modal-body" id="advertisement_image_display_section" style="overflow: auto;"></div>
        </div>
    </div>
</div>
<div class="modal fade bs-example-modal-lg" id="advertisement_view_popup" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Advertisement Detail</span> 
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center marginBottom10">
                        <div class="tableStyle">
                            <label id="ad_title_name"></label>
                            <table class="table adtable" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Path</th>
                                        <th>Apply to Child</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="advertisement_view_detail"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    getAdvertisement(<?php echo $user_top_folder; ?>);
    $(document).ready(function () {
        $("#original_file").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth < 300 || imgheight < 250) {
                    showPopupMessage('error', 'Image width must be at least 300 x 250(Width x Height)');
                    return false;
                } else {
                    readURLheader(currentObj);
                }
            };
        });

        $(document).on('click', '#close_crop_popup', function (e) {
            e.preventDefault();
            $('.admin-loading-image').show();
            $('#crop_uploading_image').modal('hide');
            $('.admin-loading-image').hide();
        });
        
        $(document).on('click','.cancel_crop_popup',function(e){
            e.preventDefault();
            $('.admin-loading-image').show();
            $('#crop_uploading_image').modal('hide');
            $('.admin-loading-image').hide();
            $('#original_file').val('');
        });

        $(document).on('click', '#advertisement_frm_sub', function (e) {
            e.preventDefault();
            var ad_title = $('#ad_title').val();
            var ad_url = $('#ad_url').val();
            if (ad_title == '') {
                $('#ad_title').focus();
                return false;
            }
            if (ad_url != '' && validURL(ad_url) == false) {
                $('#ad_url').focus();
                return false;
            }
            var ad_item_id = $('#ad_item_id').val();
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php?mode=upload_advertisement",
                type: "post",
                data: new FormData($("#advertisement_frm")[0]),
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'OK' && record.record_id != '') {
                        showPopupMessage('success', 'Advertisement added successfully');
                        resetAdvertisementForm();
                        getAdvertisement(ad_item_id);
                    }else{
                        showPopupMessage('error', record.msg + ' (Error Code: 333)');
                    }
                    $('.admin-loading-image').hide();
                    $('#crop_uploading_image').modal('hide');

                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 334)');
                    $('.admin-loading-image').hide();
                    resetAdvertisementForm();
                }
            });
        });

        $(document).on('change', '.sorting_order', function () {
            var item_id = $('#ad_item_id').val();
            var sorting_order = $(this).val();//document.getElementById('sorting_order').checked;
            if (item_id && item_id != '') {
                $('.admin-loading-image').show();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'set_ad_sorting_order', item_id: item_id, sorting_order: sorting_order},
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if (obj.status == 'OK') {
                            showPopupMessage('success', 'Sorting order status changed successfully.');
                        }
                        $('.admin-loading-image').hide();
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 335)');
                        $('.admin-loading-image').hide();
                    }
                });
            }
        });
    });

    var jcrop_api_header;
    var countheader = 0;
    function readURLheader(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#advertisement_image_display_section').html('<img id="advertisement_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#advertisement_image_pre').Jcrop({
                    onSelect: updateCoordsheader,
                    minSize: [300, 250],
                    maxSize: [300, 250],
                    setSelect: [100, 100, 0, 0],
                }, function () {
                    jcrop_api_header = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCoordsheader(c) {
        $('#advertisement_x').val(c.x);
        $('#advertisement_y').val(c.y);
        $('#advertisement_w').val(c.w);
        $('#advertisement_h').val(c.h);
    }

    function validURL(str) {
        var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
        if (!regex.test(str)) {
            $('#ad_url').val('http://'+str);
            return true;
        } else {
            return true;
        }
    }

    function advertisement(item_id) {
        if (item_id) {
            $('#ad_item_id').val(item_id);
            $('#advertisement_popup').modal('show');
            getAdvertisement(item_id);
        } else {
            showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: #1)');
        }
    }

    function addNewAd() {
        resetAdvertisementForm();
        $('#add_new_ad_table').removeClass('hide');
    }

    function closeAdForm() {
        $('#add_new_ad_table').addClass('hide');
        resetAdvertisementForm();
    }

    function getAdvertisement(item_id) {
        closeAdForm();
        $('#advertisement_tbody').html('');
        $('#advertisement_listing_table').addClass('hide');
        if (item_id) {
            $('.admin-loading-image').show();
            /*$.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_ad_sorting_order', item_id: item_id},
                success: function (response) {
                    var obj = JSON.parse(response);
                    if (obj.status == 'OK') {
                        var ad_sorting_order = obj.ad_sorting_order;
                        $('#sorting_order0').prop('checked', false);
                        $('#sorting_order1').prop('checked', false);
                        if (ad_sorting_order.trim() == '1') {
                            $('#sorting_order1').prop('checked', true);
                            //$('#sorting_order').val('1');
                        } else {
                            $('#sorting_order0').prop('checked', true);
                            //$('#sorting_order').val('0');
                        }
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                    $('.admin-loading-image').hide();
                    resetAdvertisementForm();
                }
            });*/
            /*************
             * 
             * 
             **************/
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_advertisement_list', item_id: item_id},
                success: function (response) {
                    var obj = JSON.parse(response);
                    if (obj.status == 'OK' && obj.record_id != '') {
                        var record = obj.info.records;
                        var record_length = record.length;
                        var html = '';
                        for (var x = 0; x < record_length; x++) {
                            var rec_ref = record[x].record_ref;
                            if(rec_ref > 0){
                                continue;
                            }
                            var org_img = '';
                            if (record[x].original_file != '') {
                                org_img = '<img style="width: 50px;height: 50px;" src="../get_ad_thumb.php?original_file=' + record[x].original_file + '" />';
                            }
                            var action = '';
                            action += ' <span class="adv_action"><img title="View Ad" src="public/images/view.png" alt="Delete Ad" onclick="viewAd(\'' + record[x].record_id + '\');"></span>';
                            action += ' <span class="adv_action"><img title="Edit Ad" src="public/images/edit_icon.png" alt="Edit Ad" onclick="editAd(\'' + record[x].record_id + '\');"></span>';
                            action += ' <span class="adv_action"><img title="Delete Ad" src="public/images/delete_icon.png" alt="Delete Ad" onclick="deleteAd(\'' + record[x].record_id + '\');"></span>';
                            html += '<tr id="tr_' + record[x].record_id + '">';
                            html += '<td>' + record[x].ad_title + '</td>';
                            html += '<td>' + record[x].ad_alt_title + '</td>';
                            html += '<td>' + org_img + '</td>';
                            html += '<td>' + record[x].ad_url + '</td>';
                            html += '<td>' + action + '</td>';
                            html += '</tr>';
                        }
                        if(html != ''){
                            $('#advertisement_tbody').html(html);
                            $('#advertisement_listing_table').removeClass('hide');
                        }
                        resetAdvertisementForm();
                    }
                    $('.admin-loading-image').hide();
                    $('#crop_uploading_image').modal('hide');

                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 336)');
                    $('.admin-loading-image').hide();
                    resetAdvertisementForm();
                }
            });
        }
    }

    function editAd(record_id) {
        if (record_id) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_advertisement_detail', record_id: record_id},
                success: function (response) {
                    var obj = JSON.parse(response);
                    if (obj.record_id) {
                        addNewAd();
                        $('#ad_update_id').val(obj.record_id);
                        $('#ad_item_id').val(obj.item_id);
                        $('#ad_title').val(obj.ad_title);
                        $('#ad_alt_title').val(obj.ad_alt_title);
                        $('#ad_url').val(obj.ad_url);
                        /*$('#inherit_flag').attr('checked', false);
                        if (obj.inherit_flag == 'Y') {
                            $('#inherit_flag').attr('checked', true);
                        }
                        $('#ad_sort_order').val(obj.ad_sort_order);*/
                        //$('#ad_item_id').val(obj.original_file);
                    }
                    $('.admin-loading-image').hide();

                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 337)');
                    $('.admin-loading-image').hide();
                    resetAdvertisementForm();
                }
            });
        }
    }

    function deleteAd(record_id) {
        if (confirm('Are you sure to delete this advertisement?')) {
            if (record_id) {
                $('.admin-loading-image').show();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'delete_advertisement', record_id: record_id},
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if (obj.status == 'OK') {
                            $('#tr_' + record_id).remove();
                        }
                        $('.admin-loading-image').hide();

                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 338)');
                        $('.admin-loading-image').hide();
                        resetAdvertisementForm();
                    }
                });
            }
        }
    }
    
    function viewAd(record_id){
        if(record_id){
            $('#ad_title_name').html('');
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_advertisement_references', record_id: record_id},
                success: function (response) {
                    var obj = JSON.parse(response);
                    if (obj.status == 'OK') {
                        var record = obj.data.records;
                        var record_length = record.length;
                        var html = '';
                        $('#ad_title_name').html(obj.data.ad_title_name);
                        for (var x = 0; x < record_length; x++) {
                            var source_def = record[x].source_def;
                            var item_path = record[x].item_path;
                            var inherit_flag = record[x].inherit_flag;
                            html += '<tr id="tr_' + record[x].record_id + '">';
                            html += '<td>' + item_path + '</td>';
                            html += '<td>' + inherit_flag + '</td>';
                            html += '<td><span class="adv_action"><img title="Delete Ad" src="public/images/delete_icon.png" alt="Delete Ad" onclick="deleteAd(\''+record[x].record_id+'\');"></span></td>';
                            html += '</tr>';
                        }
                        if(html != ''){
                            $('#advertisement_view_detail').html(html);
                        }else{
                            $('#advertisement_view_detail').html('<tr><td colspan="3">No record found</td></tr>');
                        }
                    }
                    $('.admin-loading-image').hide();

                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 339)');
                    $('.admin-loading-image').hide();
                }
            });
            $('#advertisement_view_popup').modal('show');
        }else{
            showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: #1)');
        }
        $('.admin-loading-image').hide();
    }

    function resetAdvertisementForm() {
        document.getElementById('advertisement_frm').reset();
        $('#ad_update_id').val('');
    }
</script>