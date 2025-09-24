<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
$loginUserType = $_SESSION['aib']['user_data']['user_type'];
$archive_group_id = '';
if($_SESSION['aib']['user_data']['user_type'] == 'A'){
    $archive_group_id = $_SESSION['aib']['user_data']['user_top_folder'];
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Fields</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Fields</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <?php if($_SESSION['aib']['user_data']['user_type'] == 'U'){ ?>
                    <div class="">
                        <a id="add_field_public_user" href="create_fields.php" class="btn btn-admin borderRadiusNone marginLeft10 pull-left">Add Field</a>
                    </div>
                <?php } ?>
                
                <?php if(isset($_GET['u'])){ ?>
                    <div class="">
                        <a id="add_template_public_user" href="add_record.php?<?php echo base64_decode($_GET['u']);?>" class="pull-right"><img src="<?php echo IMAGE_PATH . 'go-back-img.png'; ?>" alt="Go Back Image" /></a>
                    </div>                    
                <?php } ?>
                <?php if($loginUserType == 'R'){ ?>
                    <div class="archive-list custom-dropdown">
                        <select class="form-control" name="archive_listing" id="archive_listing">
                            <option value="">Select an archive</option>
                        </select>
                    </div>
                <?php } ?>
		<div class="tableScroll">
                <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th width="10%" class="text-center">Fields Id</th>
                            <th width="20%">Field Owner </th> 
                            <th width="15%">Fields Title </th>  
                            <!--<th width="10%">Fields Type</th> -->
                            <!--<th width="20%">Fields Format</th>--> 
                            <th width="15%">Field Display Width</th>  
                            <th width="10%" class="text-center">Actions</th>  
                        </tr>  
                    </thead>  
                    <tbody id="fieldlistdata">  

                    </tbody>  
                </table> 
                </div>

                <!-- Modal -->
                <div class="modal fade" id="myModal" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header form_header">
                                <h4 class="list_title">Update Fields <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
                            </div>
                            <div class="modal-body">
                                <!-- The form which is used to populate the item data -->
                                <form id="fieldsForm" method="post" class="form-horizontal" >
                                    <div id = "editfield">
                                    </div>
                                </form>

                            </div>

                        </div>

                    </div>
                </div>


            </div>

        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#dataTableDiv").hide();
        <?php if($loginUserType == 'R'){ ?>
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'assistant_archive_item_list',type:'ag'},
                success: function (response) {
                   $('#archive_listing').html(response);
                   $('#archive_listing').prepend('<option value="S">Traditional</option><option value="R">Recommended</option>');
                   $("#archive_listing").val($("#archive_listing option:first").val());
                   get_manage_field();
                }
            });
        <?php }else{ ?>
        get_manage_field();
        <?php } ?>
        
        $('#archive_listing').change(function(){
            get_manage_field($(this).val());
        });
    });
var table = $('#myTable').DataTable({"pageLength": 100,"sDom":'<"H"lfrp>t<"F"ip>'});
    function get_manage_field(archive_id='') {
        
        $('.admin-loading-image').show();
        if($('#archive_listing').length && archive_id ==''){
            archive_id = $('#archive_listing').val();
        }
        var image_path = '<?php echo IMAGE_PATH; ?>';
        var usertype = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        table.clear().draw();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'fields-list',type:'manage',arch_id: archive_id},
            success: function (response) {
                var record = JSON.parse(response);
                if(usertype == 'U'){
                    if(record.length > 4){
                        $('#add_field_public_user').addClass('disabled');
                    }		
                }
                for (i = 0; i < record.length; i++) {
                    var status_link = '<span class="change-field-status" current-status="' + record[i]._disabled + '" data-id="' + record[i].field_id + '"><img src="'+image_path+'active.png" alt="" /></span>';
                    if(record[i]._disabled == 'Y'){
                        status_link = '<span class="change-field-status" current-status="' + record[i]._disabled + '" data-id="' + record[i].field_id + '"><img src="'+image_path+'deactive.png" alt="" /></span>';
                    }
                    table.row.add([
                        record[i].field_id,
                        record[i].display_owner_title,
                        record[i].field_title,
                        record[i].field_size, 
			'<span class="custom-edit-click" data-title="Edit" data-toggle="modal"  data-target="#myModal" data-field-edit-id=' + record[i].field_id + ' ><img src="'+image_path+'edit_icon.png" alt="" /></span><span data-title="Delete" data-toggle="#delete" data-target="#delete" class="custom-delete-click" data-field-id=' + record[i].field_id + ' ><img src="'+image_path+'delete_icon.png" alt="" /></span>'+status_link
                    ]).draw(false);
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 512)');
            }
        });
    }

    $(document).on('click', '.custom-delete-click', function () {
        if (confirm('Are you sure to delete? This cannot be undone')) {
            var deleteObj = $(this);
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_fields', field_id: $(this).attr('data-field-id')},
                success: function (responseData) {
                    var result = JSON.parse(responseData);
                    if (result.status == 'success') {
                        deleteObj.parents('td').parents('tr').remove();
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 513)');
                    }
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 514)');
                }
            });
        }
    });

    $(document).on('click', '.custom-edit-click', function () {
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'get_fields', edit_field_id: $(this).attr('data-field-edit-id')},
            success: function (response) {
                $("#editfield").html(response);
                $('.admin-loading-image').hide();
            }
        });
    });

    $(document).on('click', '#btn-click', function () {
        var field_id = $('#fldid').val();
        var fieldowner = $('#fldowner').val();
        var fieldtitle = $('#fldtitle').val();
        var fielddatatype = $('#fldtype').val();
        var fieldformat = $('#fldformat').val();
        var fieldsize = $('#fldsize').val();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update-manage-field', update_field_id: field_id, updatefieldowner: fieldowner, updatefieldtitle: fieldtitle, updatefieldtype: fielddatatype, updatefieldformat: fieldformat, updatefieldsize: fieldsize},
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status == 'success') {
                    $('#myModal').modal('hide');
                    get_manage_field();
                } else {
                    showPopupMessage('error', result.message + ' (Error Code: 515)');
                }
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 516)');
            }
        });
    });
	  
    $(document).on('keyup', '#fldsize', function (e) {
            var key = e.which;
            if(key == 13)   
            {  
                    $('#btn-click').click();
            }
    });
    
    $(document).on('click', '.change-field-status', function(){
        var field_id = $(this).attr('data-id');
        var current_status = $(this).attr('current-status');
        var status = (current_status == 'N') ? 'disable' : 'enable';
        if(confirm("Are you sure to "+status+" this field")){
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'change_field_status', field_id: field_id, field_status: current_status },
                success: function (response) {
                    var result = JSON.parse(response);
                    showPopupMessage(result.status,result.message);
                    if(result.status = 'success'){
                        get_manage_field();
                    }
                }
            });
        }
    });
</script>