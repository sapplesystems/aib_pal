<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
if (isset($_REQUEST['folder_id']) && $_REQUEST['folder_id'] != '') {
    $folder_id = $_REQUEST['folder_id'];
} else {
    $folder_id = $_SESSION['aib']['user_data']['user_top_folder'];
}
$archive_group_id = '';
if ($_SESSION['aib']['user_data']['user_type'] == 'A') {
    $archive_group_id = $_SESSION['aib']['user_data']['user_top_folder'];
}
$previous = '';
if (isset($_REQUEST['previous']) && $_REQUEST['previous'] != '') {
    $previous = $_REQUEST['previous'];
}
if (isset($_REQUEST['return_data']) && $_REQUEST['return_data'] != '') {
    $_SESSION['aib']['return_data'] = $_REQUEST['return_data'];
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$ebaySaleOptions = [
    'not_applicable' => 'Not Applicable',
    'for_sale'       => 'For Sale',
    'sold'           =>'Sold',
    'not_for_sale'   => 'Not For Sale',
    'want_to_buy'    =>'Want To Buy'
];
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Archive</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Records</span> <span class="headingNameDesign"></span> <?php if (isset($_SESSION['aib']['return_data']) && $_SESSION['aib']['return_data'] != '') { ?><span class="pull-right" id="back_to_advertisement"><a href="manage_advertisements.php?return=1&<?php echo $_SESSION['aib']['return_data']; ?>"><img title="Back to advertisements" src="<?php echo IMAGE_PATH . 'back.png'; ?>" alt="Back to advertisements" /></a></span><?php } ?></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
            <input type="hidden" name="archive_group_id" id="archive_group_id" value="<?php echo $archive_group_id; ?>">
            <div id="data-listing-section" class="col-md-12 tableStyle">

            </div>
        </div>
    </section>
    <!-- Modal -->
</div>
<div class="modal fade" id="archive_modal_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Add Archive Group</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="create_new_items_form" name="create_new_items_form" method="post" class="form-horizontal">
                    <input type="hidden" name="parent_id" id="parent_id" value="">
                    <input type="hidden" name="item_request_type" id="item_request_type" value="">
                    <div id="archive-group" class="archive-group">
                        <!--<div class="form-group">
                            <label class="col-xs-4 control-label">Archive Code</label>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="item_code" id="item_code" />
                            </div>
                        </div> -->
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Archive Title</label>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="item_title" id="item_title" />
                            </div>
                        </div>
                        <div class="form-group" id="archive_advertisements" style="display:none;">
                            <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" name="display_archive_level_advertisements_ar" id="display_archive_level_advertisements">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="collection" class="archive-group">
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Collection Name</label>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="collection_name" id="collection_name" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Visible To Public</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" name="visible_to_public_co" id="visible_to_public">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" name="display_archive_level_advertisements_co" id="display_archive_level_advertisements">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="sub-group" class="archive-group">
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Sub Group Name</label>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="sub_group_name" id="sub_group_name" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Visible To Public</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" name="visible_to_public_sg" id="visible_to_public">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </span>
                            </div>
                        </div>
                        <?php if($_SESSION['aib']['user_data']['user_type'] != 'U'){ ?>
                            <div class="form-group">
                                <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                                <div class="col-xs-7">
                                    <span class="custom-dropdown">
                                        <select class="form-control" name="display_archive_level_advertisements_sg" id="display_archive_level_advertisements">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div id="assistant_assignment" class="archive-group">
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Select Assistant</label>
                            <div class="col-xs-7">
                                <span class="custom-dropdown">
                                    <select class="form-control" name="assistant_listing" id="assistant_listing">

                                    </select>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Sub Group Name</label>
                            <div class="col-xs-7" id="assistant-subgroup-assignment">

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label">Default Sorting By</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" name="default_sorting_by" id="default_sorting_by">
                                    <option value="ID">Created date</option>
                                    <option value="TITLE">Alphabet</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="save_form_data" id="save_form_data">Save</button>
                            <button style="display: none;" type="button" class="btn btn-info borderRadiusNone" name="save_form_data" id="assign_assistant_to_sub_group">Assign</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="archive_edit_modal_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" id="edit_item_popup_content">

        </div>
    </div>
</div>
<div class="modal fade" id="share_record_to_user" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Share Records with user</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="share_user_record" name="share_user_record" method="post" class="form-horizontal">
                    <input type="hidden" name="parent_id" id="parent_id" value="">
                    <input type="hidden" name="sharing_record_id" id="sharing_record_id" value="">

<!--                    <label class="col-xs-4 control-label">Select Username</label>
                    <div id="sub-group" class="archive-group col-md-7">
                        <div id="sare_scrapbook" class="form-control "></div>
                    </div>-->
                    <div class="clearfix"></div>
                    <div class="removeLink">
                     <div class="form-group">
                        <label class="col-xs-4 control-label">Recipient Email Address :</label>
                        <div id="sub-group" class="archive-group col-md-7">
                            <input type="email" name="user_email" id="user_email" class="form-control" placeholder="Enter recipient email address here">
                        </div>    
                    </div>
                    <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Message :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="share_message" id="share_massage" placeholder="Enter your message"></textarea>
                            </div>
                        </div>
                        </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="share_record_with_user" id="share_record_with_user">Share</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="move_modal_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Move Item</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div id="error_message1"></div>
            <div class="modal-body" id="movefolderformdiv">
                <form id="move_form_item" name="move_form_item" method="post" class="form-horizontal">
                    <input type="hidden" name="item_id" id="item_id" value="">
                    <input type="hidden" name="item_type" id="item_type" value="">
                    <div id="archive-group" class="archive-group">
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Select Parent Item </label>
                            <div class="col-xs-7">
                                <select class="form-control" id="item_val"  name="item_val" >
                                    <option value="">- Select -</option> 
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="child_group" class="archive-group" class="add_child" hidden="">
                        <div class="form-group" >
                            <label class="col-xs-4 control-label">Select Child Item </label>
                            <div class="col-xs-7">
                                <select class="form-control child_item_title" id="child_item_id" name="child_item_id">
                                    <option class="child_items" value="">- Select -</option> 
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="move" id="move">Move</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ebay_link_model_id" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Add eBay Sign for this record</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div id="error_message1"></div> 
            <div class="modal-body" id="movefolderformdiv">
                <div id="ebay_remove_link"  style="display: none;"><input type="button" class="btn btn-primary pull-right" value="Remove Link"></div>
                <div class="clearfix"></div>
                <form id="ebay_url_form" name="ebay_url_form" method="post" class="form-horizontal">
                    <input type="hidden" name="ebay_record_id" id="ebay_record_id">
                    <div class="removeLink">
                    <div id="archive-group" class="archive-group">
                        <div class="form-group">
                            <label class="col-xs-4 control-label">URL</label>
                            <div class="col-xs-7">
                                <input type="text" name="ebay_url" id="ebay_url" class="form-control" placeholder="Ebay Url">
                            </div>
                        </div>
                    </div>
                    <div id="archive-group" class="archive-group">
                        <div class="form-group">
                            <label class="col-xs-4 control-label">Is this item for sale ?</label>
                            <div class="col-xs-7">
                                <select name="ebay_sale_options" id="ebay_sale_options" class="form-control">
                                    <?php foreach($ebaySaleOptions as $option=>$value){ ?>
                                        <option value="<?php echo $option; ?>"><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-4 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="ebay_url_share" id="ebay_url_share">Save</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
                            <span id="ebay_expire_text" >Ebay signs expire after 7 days.</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="set_related_record_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Relate this record to another subgroup within your collection</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="col-md-12">
                <div class="modal-body" id="set_related_record_section"></div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="set_public_connection_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Set Public Connections</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body" id="set_public_connection_section">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="move_item_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Move item(s)</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body" id="move_item_section"></div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        var folder_id = '<?php echo $folder_id; ?>';
        var previous_id = '<?php echo $previous; ?>';
        var previousObj = [];
        if (previous_id != '') {
            previousObj = previous_id.split(',');
        }
        $('#current-item-id').val(folder_id);
        $('#previous-item-id').val(JSON.stringify(previousObj));
        getItemDetailsById(folder_id,'');
		setBrowseHome(folder_id);
        $("#create_new_items_form").validate({
            rules: {
                item_code: "required",
                item_title: "required"
            },
            messages: {
                item_code: "Code is required",
                item_title: "Title is required"
            }
        });

<?php if (isset($_REQUEST['folder-id']) and trim($_REQUEST['folder-id']) != '') { ?>
            getItemDetailsById('<?php echo trim($_REQUEST['folder-id']); ?>','');
<?php } ?>
    });
    $(document).on('click', '.getItemDataByFolderId', function () {
        var current_id = $('#current-item-id').val();
        var previous_id_obj = JSON.parse($('#previous-item-id').val());
        previous_id_obj.push(current_id);
        var item_folder_id = $(this).attr('data-folder-id');
        var item_type = $(this).attr('data_item_type');
        if (item_type == 'AG') {
            $('#archive_group_id').val(item_folder_id);
        }
        var record_edit_link = '';
        if (item_type == 'RE') {
            record_edit_link = $('#record_edit_'+item_folder_id).attr('href');
            $('#archive_group_id').val(item_folder_id);
        }
        $('#current-item-id').val(item_folder_id);
        $('#previous-item-id').val(JSON.stringify(previous_id_obj));
        getItemDetailsById(item_folder_id, record_edit_link);
    });
    function getItemDetailsById(id, record_edit_link='') {
        if (id) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'list_tree_items', folder_id: id,record_edit_link:record_edit_link},
                success: function (result) {
                    $('#data-listing-section').html(result);
                    $('.admin-loading-image').hide();
                    <?php if ($_SESSION['aib']['user_data']['user_type'] == 'U' || $_SESSION['aib']['user_data']['user_type'] == 'A'){ ?>
                        var parent_name = $('#heading_listing li:nth-child(2)').attr("data-folder-name");  
                    <?php }else{ ?>
                        var parent_name = $('#heading_listing li:nth-child(3)').attr("data-folder-name"); 
                    <?php } ?>
		    if(typeof parent_name == "undefined"  ){  
							$('.headingNameDesign').html('');
						}
						else{   
							$('.headingNameDesign').html(parent_name);
						}
					 
                },
                error: function () {
                    $('.admin-loading-image').hide();
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 214)');
                }
            });
        }
    }
    $(document).on("click", "#create_archive_group", function () {
        var current_id = $('#current-item-id').val();
        $('#archive_advertisements').hide();
        $('#parent_id').val(current_id);
        $('#item_request_type').val('ag');
        $('#popup_heading').text('Add Archive Group');
        $('#archive_modal_popup').modal('show');
        $('.archive-group').hide();
        $('#archive-group').show();
    });
    $(document).on("click", "#create_archive", function () {
        var current_id = $('#current-item-id').val();
        $('#archive_advertisements').show();
        $('#parent_id').val(current_id);
        $('#item_request_type').val('ar');
        $('#popup_heading').text('Add Archive');
        $('#archive_modal_popup').modal('show');
        $('.archive-group').hide();
        $('#archive-group').show();
        $('#assign_assistant_to_sub_group').hide();
        $('#save_form_data').show();
    });
    $(document).on("click", "#create_collection", function () {
        var current_id = $('#current-item-id').val();
        $('#parent_id').val(current_id);
        $('#item_request_type').val('col');
        $('#popup_heading').text('Add Collection');
        $('#archive_modal_popup').modal('show');
        $('.archive-group').hide();
        $('#collection').show();
        $('#assign_assistant_to_sub_group').hide();
        $('#save_form_data').show();
    });
    $(document).on("click", "#create_sub_group", function () {
        var current_id = $('#current-item-id').val();
        $('#parent_id').val(current_id);
        $('#item_request_type').val('sg');
        $('#popup_heading').text('Add Sub Group');
        $('#archive_modal_popup').modal('show');
        $('.archive-group').hide();
        $('#sub-group').show();
        $('#assign_assistant_to_sub_group').hide();
        $('#save_form_data').show();
    });
    $(document).on("click", "#assignment", function () {
        $('#popup_heading').text('Add Assignment');
        $('#assistant-subgroup-assignment').html('');
        var sub_group_count = 0;
        $('.assign-assistant').each(function () {
            if ($(this).is(':checked')) {
                $('#assistant-subgroup-assignment').append('<div><input class="assigned-to-subgroup" checked="checked" type="checkbox" data-id="' + $(this).attr('data-item-id') + '">&nbsp;&nbsp;' + $(this).attr('data-item-value') + '</div>');
                sub_group_count++;
            }
        });
        if (sub_group_count > 0) {
            getAssistant($('#current-item-id').val());
            $('#archive_modal_popup').modal('show');
            $('.archive-group').hide();
            $('#assistant_assignment').show();
            $('#assign_assistant_to_sub_group').show();
            $('#save_form_data').hide();
        } else {
            showPopupMessage('error', 'Select at least one sub-group');
            return false;
        }
    });
    $(document).on("click", "#add_record", function () {
        window.location.href = "add_record.php?opcode=add&src=records&parent=" + $('#current-item-id').val() + "&srckey=&searchval=&srcmode=&srcpn=&aibnav=" + '{"primary":"' + $('#current-item-id').val() + '","timestamp":1516596566,"src":"admin/manage_my_archive.php","src_title":"Record+Management","src_opcode":"list"}';
    });
	 $(document).on("click", "#import_record", function () {
        window.location.href = "import_record.php?opcode=add&src=records&parent=" + $('#current-item-id').val() + "&srckey=&searchval=&srcmode=&srcpn=&aibnav=" + '{"primary":"' + $('#current-item-id').val() + '","timestamp":1516596566,"src":"admin/manage_my_archive.php","src_title":"Record+Management","src_opcode":"list"}';
    });
    $(document).on("click", "#add_item", function () {
        window.location.href = "add_record.php?opcode=add&src=records&parent=" + $('#current-item-id').val() + "&srckey=&searchval=&srcmode=&srcpn=&aibnav=" + '{"primary":"' + $('#current-item-id').val() + '","timestamp":1516596566,"src":"admin/manage_my_archive.php","src_title":"Record+Management","src_opcode":"list"}';
    });
    $(document).on("click", "#save_form_data", function () {
        if ($("#create_new_items_form").valid()) {
            var itemsFormData = $("#create_new_items_form").serialize();
            var parent_id = $('#parent_id').val();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'create_new_items', formData: itemsFormData},
                success: function (data) {
//                    console.log(data);
                    var result = JSON.parse(data);
                    if (result.status == 'success') {
                        $('#create_new_items_form')[0].reset();
                        $('#archive_modal_popup').modal('hide');
                        getItemDetailsById(parent_id,'');
                    } else {
                        showPopupMessage('error', 'Something went wrong! Please try again. (Error Code: 215)');
                        return false;
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 216)');
                }
            });
        }
    });
    function getAssistant(archive_id) {
        if (archive_id) {
            $('#assistant_listing').html('');
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'get_assistant_list', archive_id: archive_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    for (i = 0; i < record.length; i++) {
                        $('#assistant_listing').append('<option value="' + record[i].user_id + '">' + record[i].user_title + '</option>');
                    }
                    $('.admin-loading-image').hide();
                }
            });
        }
    }
    $(document).on("click", "#assign_assistant_to_sub_group", function () {
        var selected_sub_group = [];
        var selected_assistant = $('#assistant_listing').val();
        $('.assigned-to-subgroup').each(function () {
            if ($(this).is(':checked')) {
                selected_sub_group.push($(this).attr('data-id'));
            }
        });
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode: 'assign_assistant_to_sub_group', sub_group: selected_sub_group, assistant: selected_assistant},
            success: function (response) {
                var record = JSON.parse(response);
                if (record.status == 'success') {
                    $('#archive_modal_popup').modal('hide');
                } else {
                    showPopupMessage('error', record.message + ' (Error Code: 217)');
                }
            }
        });
    });

    $(document).on('click', '.edit_listing_item', function () {
        var parent_id = $('#current-item-id').val();
        var item_id = $(this).attr('data-field-edit-id');
        var item_type = $(this).attr('data_item_type');
        $('.admin-loading-image').show();
        if (item_id != '' && item_type != '') {
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_items', item_type: item_type, item_id: item_id, parent_id: parent_id},
                success: function (data) {
                    $('#edit_item_popup_content').html(data);
                    $('.admin-loading-image').hide();
                    $('#archive_edit_modal_popup').modal('show');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 218)');
                }
            });
        }
    });
    $(document).on('click', '#update_form_data', function () {
        var itemsFormData = $("#edit_items_form").serialize();
        var parent_id = $('#current-item-id').val();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_items_by_id', formData: itemsFormData},
            success: function (data) {
                var result = JSON.parse(data);
                if (result.status == 'success') {
                    $('#archive_edit_modal_popup').modal('hide');
                    getItemDetailsById(parent_id,'');
                } else {
                    showPopupMessage('error', 'Something went wrong! Please try again. (Error Code: 219)');
                    return false;
                }
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 220)');
            }
        });
    });
    $(document).on('click', '.delete_listing_item', function () {
        if (confirm("Are you sure to delete this? This cannot be undone")) {
            var item_id = $(this).attr('data-field-delete-id');
            var parent_id = $('#current-item-id').val();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_item_by_id', item_id: item_id},
                success: function (data) {
                    var result = JSON.parse(data);
                    if (result.status == 'success') {
                        getItemDetailsById(parent_id,'');
                    } else {
                        showPopupMessage('error', 'Something went wrong! Please try again. (Error Code: 221)');
                        return false;
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 222)');
                }
            });
        }
    });

    $(document).on('click', '.change_status_item', function () {
        var archive_id = $(this).attr('data-field-item-id');
        var current_status = $(this).attr('current-staus');
        var parent_id = $('#current-item-id').val();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_archive_group_status', archive_id: archive_id, current_status: current_status},
            success: function (response) {
                getItemDetailsById(parent_id,'');
            },
            error: function () {

            }
        });
    });
    $(document).on('click', '.superadmin_change_status', function () {
        var item_id = $(this).attr('data-field-item-id');
        var current_status = $(this).attr('current-staus');
        var parent_id = $('#current-item-id').val();
        var item_type = $(this).attr('data-item-type');
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_item_current_status', item_id: item_id, current_status: current_status},
            success: function (response) {
                getItemDetailsById(parent_id,'');
            },
            error: function () {

            }
        });
    });
    
      $(document).on('click', '.change_ebay_status', function () {
        var archive_id = $(this).attr('data-field-item-id');
        var current_ebay_status = $(this).attr('current-ebay-staus');
        var parent_id = $('#current-item-id').val();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_archive_group_ebay_status', archive_id: archive_id, current_ebay_status: current_ebay_status},
            success: function (response) {
                getItemDetailsById(parent_id,'');
            },
            error: function () {

            }
        });
    });
    
    $(document).on('click', '.change_publish_status', function(){
        var item_id = $(this).attr('data-field-item-id');
        var current_publish_staus = $(this).attr('current-publish-staus'); 
        var parent_id = $('#current-item-id').val();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_archive_publish_status', item_id: item_id, current_publish_status: current_publish_staus},
            success: function (response) {
                getItemDetailsById(parent_id,'');
            },
            error: function () {

            }
        });
    });

   /*  $(document).on('click', '.perform-ocr-onfolder', function () {
        var folder_id = $(this).attr('data-ocr-folder-id');
        if (folder_id) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'perform_ocr_on_folder', folder_id: folder_id},
                success: function (response) {
                    var result = JSON.parse(response);
                    showPopupMessage('success', result.message);
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
        }
    }); */
	
	$(document).on('click', '.perform-ocr-onfolder', function () {
		$('#ocr_perform_folder').modal('show');
		$('#object_id').val($(this).attr('data-ocr-folder-id'));
		$('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_ocr_field_name', obj_id: $(this).attr('data-ocr-folder-id')},
                success: function (response) {
                    var result = JSON.parse(response);
					if(result.status == 'success'){
						$('#ocr_value').val(result.value);
					}  
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
		
    }); 
	
	$(document).on('click', '.run-ocr-onfolder', function () {
		var folder_id = $(this).attr('data-ocr-folder-id');
		$('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'run_ocr_onfolder', obj_id: folder_id},
                success: function (response) {
                    var result = JSON.parse(response);
					if(result.status == 'success'){
						$.ajax({
							url: "services_admin_api.php",
							type: "post",
							data: {mode: 'run_ocr_onfolder_flag', obj_id: folder_id},
							success: function (response) {
								var result = JSON.parse(response);
								if(result.status == 'success'){
									$('#ocr_span_'+folder_id).html(result.html);
								}  
								$('.admin-loading-image').hide();
							},
							error: function () {
								$('.admin-loading-image').hide();
							}
						});
					}  
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
    });
	
	$(document).on('click', '.reset-ocr-onfolder', function () {
		var folder_id = $(this).attr('data-ocr-folder-id');
		$('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'reset_ocr_onfolder', obj_id: folder_id},
                success: function (response) {
                    var result = JSON.parse(response);
					if(result.status == 'success'){
						$('#ocr_span_'+folder_id).html(result.html);
					}  
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
		
    });

	
    $(document).on("click", ".list_move_item", function () {
        var parent_id = $('#heading_listing li:nth-last-child(2)').attr("data-folder-id");

        var previous_id = $('#previous-item-id').val();
        var pre_id = $.parseJSON(previous_id);
        var cur_foldr_id = $('#current_folder').attr('current-folder-id');
        $('.admin-loading-image').show();
        $('#move_modal_popup').modal('show');
        var modal_data = $(this).attr('data-field-item-id');
        var array_data = modal_data.split("/");
        var itemselect_id = array_data[0];

        $('#item_id').val(array_data[0]);
        $('#item_type').val(array_data[1]);
        if (array_data[1].trim() == 'CO') {
            // var parent_id = pre_id[1];
            var parent_type = 'ar';
        } else if (array_data[1].trim() == 'SG') {
            //var parent_id = pre_id[1];
            var parent_type = 'co';
        } else if (array_data[1].trim() == 'RE') {
            // var parent_id = pre_id[2];
            var parent_type = 'sg';
        } else {
            //var parent_id = pre_id[3];
            var parent_type = 're';
        }

        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode: 'get_parent_item_list', type: parent_type, parent_id: parent_id, current_folder_id: cur_foldr_id},
            success: function (response) {
                if (response == 'Error') {
                    $('#movefolderformdiv').hide();
                    $('#error_message1').html('<div style="color:red;height:92px;padding:25px;"><center>NO PARENT FOUND !</center></div>');
                } else {
                    $('#error_message1').html('');
                    $('#movefolderformdiv').show();
                    $('#item_val').html(response);
                }
                $('.admin-loading-image').hide();
            }
        });
    });
    $(document).on("click", "#move", function () {
        var move = confirm("Are you sure current item to move!");
        if (move == true) {
            $('.admin-loading-image').show();
            var move_item = $("#move_form_item").serialize();
            var perent_id = $('#item_val').val();
            var item_id = $('#item_id').val();
            var child_id = $('#child_item_id').val();
            if (child_id) {
                var item_val = child_id;
            } else {
                var item_val = perent_id;
            }
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'move_item', parent_id: item_val, item_id: item_id},
                success: function (response) {
                    $('.admin-loading-image').hide();
                    var record = JSON.parse(response);
                    showPopupMessage(record.status, record.message);
                    if (record.status == 'success') {
                        var parent_id = $('#current-item-id').val();
                        getItemDetailsById(parent_id,'');
                        $('#move_modal_popup').modal('hide');
                    }
                },
                error: function () {
                    $('.admin-loading-image').hide();
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 223)');
                }
            });
        }
    });
    $(document).on("change", "#item_title", function () {
        $('#child_group').hide();
        var id = $('#item_title').val();
        var type = $('#item_type').val();
        if (type.trim() == 'RE') {
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'get_child_item_list', id: id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.length > 0) {
                        $('#child_group').show();
                        var i;
                        var text = "";
                        for (i = 0; i < record.length; i++) {
                            text += "<option value=" + record[i]['item_id'] + ">" + record[i]['item_title'] + "</option>";
                        }
                        $(".child_item_title").html(text);
                    } else {
                        $('#child_group').hide();
                    }
                }
            });
        }

    });
    $(document).on("click", ".share_with_users", function () {
        $('.admin-loading-image').show();
        var record_id = $(this).attr('data-field-record-id');
        $('#sharing_record_id').val(record_id);
        $('#share_record_to_user').modal('show');
        $('.admin-loading-image').hide();
//        $.ajax({
//            url: "services_admin_api.php",
//            type: "post",
//            data: {mode: 'get_scrpbook_prop', id: record_id},
//            success: function (response) {
//                var record = JSON.parse(response);
//                var shared_email = $.parseJSON(record);
//                if (shared_email) {
//                    selectPublicUser(shared_email);
//                } else {
//                    selectPublicUser('');
//                }
//            },
//            error: function () {
//                showPopupMessage('error', 'Something went wrong, Please try again');
//            }
//        });
 $('.admin-loading-image').hide();
    });

    $(document).on("click", "#share_record_with_user", function () {
        $("#share_user_record").validate({
            rules: {
                user_email: {
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
                user_email: {
                    required:"Please enter email Id",
                    email: "Please enter valid email Id"
                }
            }
        });
        if ($("#share_user_record").valid()) {
            $('.admin-loading-image').show();
            var record_id = $("#sharing_record_id").val();
            var emails = [];
            var email_id = $('.email1').each(function () {
                emails.push($(this).text());
            });
            var user_email = $("#user_email").val();
            var share_massage = $('#share_massage').val();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'set_scrpbook_prop', user_emails: emails, id: record_id, type: 'records', email_id: user_email,share_massage:share_massage},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'success') {
                        showPopupMessage('success', 'Record shared successfully.');
                        $('#share_record_to_user').modal('hide');
                        $('.admin-loading-image').hide();
                         $('#share_user_record')[0].reset();
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 224)');
                }
            });
        }
    });

    function selectPublicUser(email) {
        var data = [];
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'get_public_user_email'},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    data.push(record[i].user_login);
                }
                var ms1 = $('#sare_scrapbook').magicSuggest({data: data, value: email});
                ms1.clear();
                ms1.setData(data);
                ms1.setValue(email);
                $('#share_record_to_user').modal('show');
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 225)');
            }
        });
    }
    $(function () {
        $('#item_title').keypress(function (e) {
            var key = e.which;
            if (key == 13)
            {
                $('#save_form_data').click();
            }
        });
    });
// save ebay link for records.
    $(document).on("click", ".ebay_link", function () {
        $('#ebay_remove_link').css("display", "none")
        $('#ebay_link_model_id').modal('show');
        var record_id = $(this).attr('data-field-ebay-record-id');
        $('#ebay_record_id').val(record_id);
        $("#ebay_remove_link").attr("ebay_record_link_id",record_id);
        check_ebay_link_save(record_id);
    });
    $(document).on("click", "#ebay_url_share", function () {
        $.validator.addMethod(
                "regex",
                function (value, element, regexp) {
                    var check = false;
                    return this.optional(element) || regexp.test(value);
                },
                "Please check your input."
                );
        $("#ebay_url_form").validate({
            rules: {
                ebay_url: {
                    required: true,
                    regex: /^(?:http(?:s)?:\/\/)?(?:[^\.]+\.)?ebay\.com/
                }
            },
            messages: {
                ebay_url: {
                    required: "Please enter the url",
                    regex: "Please enter ebay url only "
                }
            }
        });
        if ($("#ebay_url_form").valid()) {
            $('.admin-loading-image').show();
            var record_id = $('#ebay_record_id').val();
            var url = $('#ebay_url').val();
            var ebay_sale_options = $('#ebay_sale_options').val();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'save_ebay_url', record_id: record_id, url: url, ebay_sale_options: ebay_sale_options},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'success') {
                        showPopupMessage(record.status, record.message);
                        $('#ebay_link_model_id').modal('hide');
                    }else if(record.status == 'error'){
                       showPopupMessage(record.status, record.message); 
                       $('#ebay_link_model_id').modal('hide');
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 226)');
                }
            });
        }
    });
    //Strat Set related records.
    $(document).on('click', '.set_related_records', function () {
        var record_id = $(this).attr('data-field-record-id');
        var archive_group_id = $('#archive_group_id').val();
        if (record_id) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_related_recors_listing', record_id: record_id, archive_group_id: archive_group_id},
                success: function (response) {
                    $('#set_related_record_section').html(response);
                    $('#set_related_record_popup').modal('show');
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
	//Strat Public connections.
    $(document).on('click', '.set_public_connections', function () {
			var record_id = $(this).attr('data-field-record-id');
			var archive_group_id = $('#archive_group_id').val();
			if (record_id) {
				$('.admin-loading-image').show();
				$.ajax({
					url: "services_admin_api.php",
					type: "post",
					data: {mode: 'get_public_connections', record_id: record_id, archive_group_id: archive_group_id},
					success: function (response) {
						$('#set_public_connection_section').html(response);
						$('#set_public_connection_popup').modal('show');
						$('.admin-loading-image').hide();
					},
					error: function () {
						$('.admin-loading-image').hide();
					}
				});
			}
		});
    $(document).on('click', '#set_record_as_related_record', function () {
        var selected_parent = [];
        var record_id = $('#selected_record_id').val();
        var previous_selected_parent = $('#previous_selected_parent').val();
        $('input[id^="available_"]').each(function () {
            if ($(this).is(':checked')) {
                selected_parent.push($(this).val());
            }
        });
        //if (selected_parent.length > 0) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'set_related_recors_listing', record_id: record_id, selected_parent: selected_parent, previous_selected_parent: previous_selected_parent},
                success: function (response) {
                    var result = JSON.parse(response);
                    showPopupMessage(result.status, result.message);
                    $('#set_related_record_popup').modal('hide');
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
        //}
    });
    $(document).on("click","#ebay_remove_link",function(){
      $('.admin-loading-image').show();
      var record_id = $(this).attr('ebay_record_link_id');
      $.ajax({
          url: "services_admin_api.php",
          type: "post",
          data: {mode: 'ebay_remove_link', record_id: record_id},
            success: function (response) {
                var record = JSON.parse(response);
                if (record.status == 'success') {
                        showPopupMessage(record.status, record.message);
                        $('#ebay_link_model_id').modal('hide');
                    }else if(record.status == 'error'){
                       showPopupMessage(record.status, record.message); 
                       $('#ebay_link_model_id').modal('hide');
                    }
                $('.admin-loading-image').hide();
            },
            error: function () {
                $('.admin-loading-image').hide();
            }
      });
       
    });
    function check_ebay_link_save(record_id) {
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'check_ebay_link_save', record_id: record_id},
            success: function (response) {
                var record = JSON.parse(response);
                if (record.ebay_url != '' && record.ebay_url != 'null') {
                    $('#ebay_url').val(record.ebay_url);
                    if(record.ebay_sale_options && record.ebay_sale_options != ''){
                        $('#ebay_sale_options').val(record.ebay_sale_options);
                    }else{
                        $("#ebay_sale_options").val($("#ebay_sale_options option:first").val());
                    }
                }
                if($('#ebay_url').val() !=''){ $('#ebay_remove_link').css("display", "block"); }
                $('.admin-loading-image').hide();
            },
            error: function () {
                $('.admin-loading-image').hide();
            }
        });
    }

    $(document).on('click','.move_item', function(){
        var item_id   = $(this).attr('item-id');
        var item_type = $(this).attr('item-type');
        if(item_id && item_type){
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'change_item_parent', item_id: item_id, item_type: item_type},
                success: function (response) {
                    $('.admin-loading-image').hide();
                    $('#move_item_section').html(response);
                    $('#move_item_popup').modal('show');
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click','.right-section', function(){
        if($(this).is(':checked')){
            $('.right-section').prop('checked', false);
            $(this).prop('checked', 'checked');
        }
    });
    
    $(document).on('click', '#move_selected_item', function(){
        var selected_items  = [];
        var item_count      = 0;
        var selected_parent = '';
        $('.left-section').filter(':checked').each(function(){
            selected_items.push($(this).attr('data-id'));
            item_count ++;
        });
        $('.right-section').filter(':checked').each(function(){
            selected_parent = $(this).attr('data-id');
        });
        if(item_count > 0 && selected_parent !== ''){
            if(confirm('Are you sure to move the selected item(s)')){
                $('.admin-loading-image').show();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'move_item_parent', selected_parent: selected_parent, selected_items: selected_items},
                    success: function (response) {
                        var current_parent = $('#current_folder').attr('current-folder-id');
                        var result = JSON.parse(response);
                        $('.admin-loading-image').hide();
                        $('#move_item_popup').modal('hide');
                        showPopupMessage(result.status, result.message);
                        getItemDetailsById(current_parent);
                    },
                    error: function () {
                        $('.admin-loading-image').hide();
                    }
                });
            }
        }else{
            showPopupMessage('error', "Item and parents both are required. (Error Code: 227)");
        }
    });
    
    $(document).on('click','.load-more-child', function(){
        var item_id = $(this).attr('data-id');
        var item_type = $(this).attr('data-type');
        var selected_type = $(this).attr('selected-data-type');
        var selected_item = $(this).attr('selected-item');
        var curObj = $(this);
        if(item_id && item_type && curObj.hasClass('glyphicon-plus')){
            //$('.admin-loading-image').show();
            curObj.hide();
            curObj.siblings('i').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_more_child', item_id: item_id, item_type: item_type, selected_type:selected_type, selected_item:selected_item},
                success: function (response) {
                    //$('.admin-loading-image').hide();
                    curObj.siblings('i').hide();
                    curObj.show();
                    if(response !== 'no-data'){
                        $(response).insertAfter('#list_'+item_id);
                    }
                    if(curObj.hasClass('glyphicon-minus')){
                        curObj.removeClass('glyphicon-minus').addClass('glyphicon-plus');
                    }else{
                        curObj.removeClass('glyphicon-plus').addClass('glyphicon-minus');
                    }
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
        }else{
            curObj.parents('span').siblings('ul').remove();
            curObj.removeClass('glyphicon-minus').addClass('glyphicon-plus');
        }
    });
</script>
