<?php
    session_start();
    if (empty($_SESSION['aib']['user_data'])) {
        header('Location: login.php');
        exit;
    }
    $loginUserType = $_SESSION['aib']['user_data']['user_type'];
    if($loginUserType == 'S'){
        header('Location: assistant_index.php');
        exit;
    }
	else{
		header('Location: manage_my_archive.php');
        exit;
	}
    include_once 'config/config.php';
    include_once COMMON_TEMPLATE_PATH.'header.php';
    include_once COMMON_TEMPLATE_PATH.'sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Dashboard</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <?php include_once COMMON_TEMPLATE_PATH.'common_header.php'; 
				if($loginUserType != 'U'){
				?>
            <div class="row">
                <div class="col-md-6">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">My Assistant</h3>
                        </div>
                        <div class="box-body">
                        <div class="tableScroll">
                          <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th width="40%">User Title</th>  
                            <th width="40%">User Login</th>
                            <th width="10%">Waiting</th>
                            <th width="10%">Complete</th>
                           
                        </tr>  
                    </thead>  
                    <tbody id="listdata">   </tbody>  
                </table> 
                </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title"></h3>
                        </div>
                        <div class="box-body">
                          <canvas id="company_analyzer_chart" style="height:250px"></canvas>
                        </div>
                    </div>
                </div>
            </div>
			<?php }?>
        </section>
    </div>
   <?php include_once COMMON_TEMPLATE_PATH.'footer.php'; ?><script type="text/javascript">
    $(document).ready(function () {
        <?php if($loginUserType == 'R'){ ?>
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'assistant_archive_item_list',type:'ar'},
                success: function (response) {
                   $('#archive_listing').html(response);
                   $('.admin-loading-image').hide();
                   get_assistant_list();
                }
            });
        <?php }else{ ?>
            get_assistant_list();
        <?php } ?>
        $('#archive_listing').change(function(){
            get_assistant_list($(this).val());
        });
    });
    var table = $('#myTable').DataTable({"pageLength": 100});
    function get_assistant_list(parent_id = ''){
        
        table.clear().draw();
        if($('#archive_listing').length && parent_id ==''){
            parent_id = $('#archive_listing').val();
        }
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'assistant_list',type:'S',parent_id:parent_id},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    table.row.add([
                        record[i].user_title,
                        record[i].user_login,
		        record[i].Waiting,
			record[i].Complete
                    ]).draw(false);
                    select: true;
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 472)');
            }
        });
    }
    $(document).on('click', '.edit_assistant', function () {
        var user_id = $(this).attr('data-form-user-id');
        if(user_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_user_by_id',user_id:user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('#user_id').val(record.user_id);
                    $('#user_type').val(record.user_type);
                    $('#user_title').val(record.user_title);
                    $('#user_login').val(record.user_login);
                    $.ajax({
                        type: 'POST',
                        url: 'services_admin_api.php',
                        data: {mode:'assistant_archive_item_list',type:'ar'},
                        success: function (response) {
                           $('#archive_name').html(response);
                        }
                    });
                    $('#editAssistantForm').modal('show');
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click', '#update_assistant_btn', function (){
        var assistantFormData = $('#assistantForm').serialize();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_user_profile',formData: assistantFormData },
            success: function (response) {
                var record = JSON.parse(response);
                if(record.status=='success'){
                    get_assistant_list($('#archive_listing').val());
                    $('#editAssistantForm').modal('hide');
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 473)');
            }
        });
    });
    
    $(document).on('click','.delete_assistant',function(){
        if(confirm("Are you sure to delete the assistant? This cannot be undone")){
            $('.admin-loading-image').show();
            var user_profile_id = $(this).attr('data-form-user-id');
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'delete_user_profile',user_profile_id: user_profile_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        get_assistant_list($('#archive_listing').val());
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 474)');
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
</script>