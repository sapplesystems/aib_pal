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
            <li class="active">My Template</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Templates</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <?php if($_SESSION['aib']['user_data']['user_type'] == 'U'){ ?>
                    <div class="">
                        <a id="add_template_public_user" href="create_forms.php" class="btn btn-admin borderRadiusNone marginLeft10 pull-left">Add Template</a>
                    </div>                    
                <?php } ?>
                
                <?php if(isset($_GET['u'])){ ?>
                    <div class="">
                        <a id="add_template_public_user" href="add_record.php?<?php echo base64_decode($_GET['u']);?>" class="pull-right"><img src="<?php echo IMAGE_PATH . 'go-back-img.png'; ?>" alt="Go Back Image" /></a>
                    </div>                    
                <?php } ?>
                
                
		<div class="tableScroll">
                <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th width="10%" class="text-center">Template Id</th>  
                            <th width="20%">Template Owner Id</th>
                            <th width="20%">Template Owner Type</th>   
                            <th width="40%">Template Title</th>  
                            <th width="10%" class="text-center">Actions</th>  

                        </tr>  
                    </thead>  
                    <tbody id="formlistdata">  

                    </tbody>  
                </table>  
					</div>


                <!-- Modal -->
                <div class="modal fade" id="myModal" role="dialog">
                    <div class="modal-dialog" style="width: 1050px;">
                        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header form_header">
                                <h4 class="list_title">Update Template <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
                            </div>
                            <div class="modal-body">
                                <!-- The form which is used to populate the item data -->
                                <form id="formsForm" method="post" class="form-horizontal" >
                                    <div id="editforms">

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
        /*var usertype = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        if(usertype == 'U'){
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'check_template_count_public_user'},
                success: function (response) {
                    if(response > 0){
                        $('#add_template_public_user').addClass('disabled');
                    }
                }
            });
        }*/
        get_manage_forms();
    });
 var table = $('#myTable').DataTable({"pageLength": 100,"sDom":'<"H"lfrp>t<"F"ip>'});
    function get_manage_forms() {
       
        table.clear().draw();
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
		var usertype = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'forms-list'},
            success: function (response) {
                var record = JSON.parse(response);
				if(usertype == 'U'){
					if(record.length > 0){
						$('#add_template_public_user').addClass('disabled');
					}		
				}
                for (i = 0; i < record.length; i++) {
                    table.row.add([
                        record[i].form_id,
                        record[i].form_owner,
                        record[i].form_owner_type,
                        record[i].form_title, 
						'<span class="custom-edit-click" data-title="Edit" data-toggle="modal" data-target="#myModal" data-form-edit-id=' + record[i].form_id + ' ><img src="'+image_path+'edit_icon.png" alt="" /></span><span  id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" class="custom-delete-forms-click" data-forms-id=' + record[i].form_id + '><img src="'+image_path+'delete_icon.png" alt="" /></span>'
                    ]).draw(false);
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 517)');
            }

        });
    }

    $(document).on('click', '.custom-edit-click', function () {
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'get_forms', edit_forms_id: $(this).attr('data-form-edit-id')},
            success: function (response) {
                $("#editforms").html(response);
                //$('.admin-loading-image').show();
            }
        });
    });

    $(document).on('click', '#forms_btn', function () {
		$('.admin-loading-image').show();
        var forms_name = $('#form_name').val();
        var forms_id = $('#form_id').val();
        var selectednumbers = [];
        var x = document.getElementById("form_dest_fields");
        var i;
        for (i = 0; i < x.length; i++) {
            if (x.options[i].value != 'NULL') {
                selectednumbers.push(x.options[i].value);
            }
        }
        var fields_on_form = $('#fields_on_form').val();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update-manage-form', edit_form_name: forms_name, edit_forms_id: forms_id, edit_field_id: selectednumbers, fields_on_form: fields_on_form},
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status == 'success') {
                    $('#myModal').modal('hide');
                    get_manage_forms();
                } else {
                    showPopupMessage('error', result.message + ' (Error Code: 518)');
                }

            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 519)');
            }
        });
    });


    $(document).on('click', '.custom-delete-forms-click', function () {
        if (confirm('Are you sure to delete? This cannot be undone')) {
            var deleteObj = $(this);
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_forms', forms_id: $(this).attr('data-forms-id')},
                success: function (response) {
                    var result = JSON.parse(response);
                    if (result.status == 'success') {
                        deleteObj.parents('td').parents('tr').remove();
                    } else {
                        showPopupMessage('error', result.message + ' (Error Code: 520)');
                    }
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 521)');
                }

            });
        }
    });
</script>