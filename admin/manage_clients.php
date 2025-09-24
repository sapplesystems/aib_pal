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
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Clients</li>
        </ol>
        <h4 class="list_title">Manage Clients
        </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th width="10%" class="text-center">ID</th>  
                            <th width="20%">Clients Type </th>  
                            <th width="60%">Clients Title</th>  
                            <th width="10%" class="text-center">Actions</th>   
                        </tr>  
                    </thead>  
                    <tbody>  
                         
                    </tbody>  
                </table>  
                <!-- Modal -->
                <div class="modal fade" id="editClient" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header form_header">
                                <h4 class="list_title">Update Archive Group <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
                            </div>
                            <div class="modal-body">
                                <form id="clinentForm" method="post" class="form-horizontal" >
                                    <input type="hidden" name="client_id" id="client_id" value="">
                                    <div class="form-group">
                                        <label class="col-xs-4 control-label">Archive Group Code :</label>
                                        <div class="col-xs-7">
                                            <input type="text" class="form-control" id="archive_group_code"  name="archive_group_code" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-4 control-label">Archive Group Title :</label>
                                        <div class="col-xs-7">
                                            <input type="text" class="form-control" id="archive_group_title"  name="archive_group_title" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-4 control-label"></label>
                                        <div class="col-xs-7">
                                            <button type="button" class="btn btn-info borderRadiusNone" name="update_client_btn" id="update_client_btn">Update</button>
                                            <button type="button" class="btn btn-danger borderRadiusNone" id="clearClientForm">Clear Form</button>
                                        </div>
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
        var table = $('#myTable').DataTable({"pageLength": 100});
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'archive_group_list', folder_id: 1},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    table.row.add([
                        record[i].item_id,
                        record[i].item_type,
                        record[i].item_title,
                        '<span class="edit_client" data-title="Edit" data-form-edit-id='+ record[i].item_id +' ><img src="'+image_path+'edit_icon.png" alt="" /></span><span class="delete-clients" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="'+image_path+'delete_icon.png" alt="" /></span>'
                    ]).draw(false);
                    select: true
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 511)');
            }
        });
    });
    
    $(document).on('click', '.edit_client', function () {
        var client_id = $(this).attr('data-form-edit-id');
        if(client_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_client_by_id',client_id:client_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('#client_id').val(record.item_id);
                    $('#archive_group_code').val(record.item_type);
                    $('#archive_group_title').val(record.item_title);
                    $('#editClient').modal('show');
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
</script>
