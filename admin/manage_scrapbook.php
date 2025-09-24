<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Scrapbook</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Scrapbook</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">My Scrapbook</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <table id="scrapbook_listing" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>
                            <th width="30%">Scrapbook Title </th>
                            <th width="40%">Shared With </th>
                            <th width="30%" class="text-center">Actions</th>  
                        </tr>  
                    </thead>  
                    <tbody id="scrapbook_data_section"></tbody>  
                </table>
            </div>
        </div>
    </section>
    <div class="modal fade" id="share_scrap" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header form_header">
                    <h4 class="list_title"><span id="popup_heading">Share Scrapbook</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
                </div>
                <div class="modal-body">
                    <form id="share_scrapbook" name="share_scrapbook" method="post" class="form-horizontal">
                        <input type="hidden" name="parent_id" id="parent_id" value="">
                        <input type="hidden" name="scrapbook_id" id="scrapbook_id" value="">
          <div class="clearfix"></div>
                    <div class="formMailId">
                        <label class="col-xs-4 control-label">Email-Id :</label>
                        <div id="sub-group" class="archive-group col-md-7">
                            <input type="email" name="user_email" id="user_email" class="form-control" placeholder="Enter Email-Id">
                        </div>    
                    </div>
                    <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Message :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="share_message" id="share_massage" placeholder="Enter your message"></textarea>
                            </div>
                        </div>
                    <div class="clearfix"></div>

                        <div class="form-group" style="padding-top: 50px !important;">
                            <label class="col-xs-4 control-label"></label>
                            <div class="col-xs-7">
                                <button type="button" class="btn btn-info borderRadiusNone" name="save_form_data" id="save_form_data">Save</button>
                                <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="manage_edit_scrapbook" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Edit Scrapbook</span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="edit_scrapbook_form" name="edit_scrapbook_form" method="post" class="form-horizontal">
                    <input type="hidden" name="edit_scrapbook_id" id="edit_scrapbook_id" value="">
					 <div class="row">
                        <div class="col-md-4 text-right"><strong>Scrapbook Title :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="scrapbook_title"  name="scrapbook_title" placeholder="Enter scrapbook title"></div>
                    </div>
					<div class="clearfix">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Scrapbook Type :</strong></div>
                        <div class="col-md-7">
                            <select name="scrapbook_type" id="scrapbook_type" class="form-control">
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                    </div>
					<div class="clearfix">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-info borderRadiusNone" id="edit_scrapbook_button" name="edit_scrapbook_button">Submit</button>
                            <button type="button" class="btn btn-warning  borderRadiusNone" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#dataTableDiv").hide();
        getScrapbookListing();

        $("#share_scrapbook").validate({
            rules: {
                user_email:{
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                share_message: {
                    required : true
                }
            },
            messages: {
                user_email:{
                    required: "User email is required",
                    email:"Please enter valid email Id"
                },
                share_message: {
                    required: "Message is required."
                }
            }
        });



    });
	var table = $('#scrapbook_listing').DataTable({"pageLength": 100});
    function getScrapbookListing() {
        
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
        table.clear().draw();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'list_user_scrapbook'},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    var scrapbook_id = record[i].item_id;
                    var manage_image = 'manage.png';
                    var manage_title = 'Manage entries';
                    var share_scrapbook = '<span class="share_scrapbooks"  data-title="" data-scrapbook-id=' + record[i].item_id + ' ><img src="' + image_path + 'share.png" title="share scrapbook" alt=""  /></span>';
                    if(record[i].item_ref && record[i].item_ref > 1){
                        scrapbook_id = record[i].item_ref+'&scrapbook_ref='+record[i].item_id;
                        share_scrapbook = '';
                        manage_image = 'view.png';
                        manage_title = 'View entries';
                    }
                    table.row.add([
                        '<a href="manage_scrapbook_entries.php?scrapbook_id=' + scrapbook_id + '">'+record[i].item_title+'</a>',
                        record[i].shared_with,
                        '<span class="edit_scrapbook" data-title="Edit" data-scrapbook-id=' + record[i].item_id + ' ><img src="' + image_path + 'edit_icon.png" alt="" title="Edit scrapbook" /></span><span data-title="Delete" class="delete_scrapbook" data-scrapbook-id=' + record[i].item_id + ' ><img src="' + image_path + 'delete_icon.png" alt="" title="Delete scrapbook" /></span><span data-title="Manage entries" class="manage_scrapbook_entries" data-scrapbook-id=' + scrapbook_id + ' ><a href="manage_scrapbook_entries.php?scrapbook_id=' + scrapbook_id + '"><img src="' + image_path + manage_image+'" title="'+manage_title+'" alt="" /></a></span>'+share_scrapbook
                    ]).draw(false);
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 566)');
            }
        });
    }
    
    $(document).on('click', '.edit_scrapbook', function(){
        var scrapbook_id = $(this).attr('data-scrapbook-id');
        if(scrapbook_id){
			$('.admin-loading-image').show();
			$.ajax({
                                url: "services_admin_api.php",
                                type: "post",
                                data: {mode: 'user_scrapbookid_record',scrapbook_id:scrapbook_id},
                                success: function (response) { 
                                                    var record = JSON.parse(response); 
                                                    $('#scrapbook_title').val(record.scrap_name);   
                                                    $('#scrapbook_type').val(record.scrap_type);
                                                    $('#edit_scrapbook_id').val(scrapbook_id);  
                                                    $('.admin-loading-image').hide();
                                                    $('#manage_edit_scrapbook').modal('show');
                                },
                                error: function () {
                                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 567)');
                                }
                        });
            
        }
    });
	
	$(document).on('click', '#edit_scrapbook_button', function(){
            if ($("#edit_scrapbook_form").valid()) {
                $('.admin-loading-image').show();
                var formData = $("#edit_scrapbook_form").serialize();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'update_new_scrapbook', formData: formData },
                    success: function (data) { 
					     var record = JSON.parse(data);
                         $('.admin-loading-image').hide();
                         showPopupMessage(record.status,record.message);
                        if(record.status == 'success'){
                            getScrapbookListing();
			 $('#manage_edit_scrapbook').modal('hide');
                        }  
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again. (Error Code: 568)');
                    }
                });
            }
    });
	
    $(document).on('click', '.delete_scrapbook', function () {
        var scrapbook_id = $(this).attr('data-scrapbook-id');
        if (confirm("Are you sure to delete the scrapbook? This cannot be undone")) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_user_scrapbook', scrapbook_id: scrapbook_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('.admin-loading-image').hide();
                    showPopupMessage(record.status, record.message);
                    if (record.status == 'success') {
                        setTimeout(function () {
                            getScrapbookListing();
                        }, 1000);
                    }
                },
                error: function () {
                    $('.admin-loading-image').hide();
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 569)');
                }
            });
        }
    });

    $(document).on("click", ".share_scrapbooks", function () {
        $('.admin-loading-image').show();
        var scrapbook_id = $(this).attr('data-scrapbook-id');
        $('#scrapbook_id').val(scrapbook_id);
        $('#share_scrap').modal('show');
        $('.admin-loading-image').hide();
        
//        
//        $.ajax({
//            url: "services_admin_api.php",
//            type: "post",
//            data: {mode: 'get_scrpbook_prop', id: scrapbook_id},
//            success: function (response) {
//                var record = JSON.parse(response);
//                var shared_email = $.parseJSON(record);
//                if (shared_email) {
//                    selectPublicUser(shared_email);
//                }else{
//                    selectPublicUser('');
//                }
//            },
//            error: function () {
//                showPopupMessage('error', 'Something went wrong, Please try again');
//            }
//        });
    });

    $(document).on("click", "#save_form_data", function () {
        if ($("#share_scrapbook").valid()) {
            $('.admin-loading-image').show();
            var scrapbook_id = $("#scrapbook_id").val();
            var email = $('#user_email').val();
            var share_massage = $('#share_massage').val();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {
                    mode: 'set_scrpbook_prop',
                    email_id: email,
                    id: scrapbook_id,
                    type: 'scrapbook',
                    share_massage: share_massage
                },
                success: function (response) {
                    var record = JSON.parse(response);
                    showPopupMessage(record.status, record.message);
                    if (record.status == 'success') {
                        setTimeout(function () {
                            window.location.href = 'manage_scrapbook.php';
                        }, 1500);
                        $('#share_scrap').modal('hide');
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 570)');
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
				
                var ms1 = $('#sare_scrapbook').magicSuggest({
                        data: data,
                        value: email
                    });
                  ms1.clear();
				  ms1.setData(data);
				  ms1.setValue(email);
                $('#share_scrap').modal('show');
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 571)');
            }
        });
    }
</script>