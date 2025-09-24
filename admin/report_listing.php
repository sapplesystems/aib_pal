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
        <h1>User Reporting List</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">User Report Listing</li>
        </ol>
        <h4 class="list_title">Manage Reported Public Connections</h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                  <?php if($loginUserType == 'R'){ ?>
                 <div class="archive-list custom-dropdown left35">
                        <select class="form-control" name="select_dropdown" id="select_dropdown">
                            <option value="Archive">Archive</option>
                            <option value="People">People</option>
                            <option value="All" selected="" >All</option>
                        </select>
                    </div>
                <?php } ?>
                <div class="tableScroll">
                    <table id="user_reporting" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                        <thead>  
                            <tr>
                                <th width="15%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Reported User</th> 
                                <th width="15%">Reporting User</th>
                                <th width="40%" class="text-center">Reporting Url</th>
                                <th width="15%" class="text-center">Action</th>
                                
                            </tr>  
                        </thead>  
                        <tbody id="user_notifier_listing_section"></tbody>  
                    </table> 
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="report_details" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">User Report <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="assistantForm" method="POST" name="assistantForm" class="form-horizontal reportListStyle" action="" >
                  <div class="form-group">
                        <label class="col-xs-3 control-label">Reported User</label>
                        <div class="col-xs-8">
                            <span id="reported_user"></span>
                           
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Reporting User</label>
                        <div class="col-xs-8">
                            <span id="reporting_user"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Reporting Url</label>
                        <div class="col-xs-8">
                            <span id="reporting_url"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Reporting Reason</label>
                        <div class="col-xs-8">
                            <span id="reporting_reason">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<br />
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span>
                        </div>
                    </div>
                 
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#dataTableDiv").hide();
        getUserReportingListing();
    });
     $(document).on('change','#select_dropdown',function(){
        getUserReportingListing();
    });
	 var user_reporting = $('#user_reporting').DataTable({"pageLength": 100,"sDom":'<"H"lfrp>t<"F"ip>'});
    function getUserReportingListing(){
        $("#dataTableDiv").hide();
        var image_path ='<?php echo IMAGE_PATH; ?>'
        var search_type = $('#select_dropdown').val();
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'get_user_report_listing',search_type:search_type},
            success: function (response) {
               
                user_reporting.clear().draw();
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                        var select_option = '<span style="display:none">0</span><select data-form-request-id='+ record[i][0] +' style="padding:0px; width:110px;" class="form-control change_request_status" name="change_request_status"><option value="NEW">New</option><option value="COMPLETED">Completed</option></select>';
                        var delete_link = '<span class="delete_report_items unlink_share_conection"  data-value="'+record[i][0]+'" title="Delete Report Item"><button>Unlink</button></span>';
                        if(record[i][1] == 'COMPLETED'){
                            select_option = '<span style="display:none">1</span><select disabled data-form-request-id='+ record[i][0] +' style="padding:0px; width:110px;" class="form-control change_request_status" name="change_request_status"><option value="NEW">New</option><option value="COMPLETED" selected>Completed</option></select>';
                            delete_link = '';
                        }
                        user_reporting.row.add([
                        select_option,
                        record[i].user_reported,
                        record[i].user_reporting, 
						"<a target='_blank' style='color:#3c8dbc;' href="+record[i].report_url+">"+record[i].report_url+"</a>",
                        '<span class="show_report_details"  data-value="'+record[i][0]+'" title="View Full Details"><img src="'+image_path+'view.png" alt="" /></span>'+delete_link+'<a href="'+record[i].report_url+'" target="_blank"><span class="view_report_item_details"  data-value="'+record[i][0]+'" title="View Item Details"><img src="'+image_path+'view.png" alt="" /></span></a>'
               ]).draw(false);
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 625)');
            } 
        });
       }
	$(document).on('click', '.show_report_details', function(){
            $('.admin-loading-image').show();
            var data_val = $(this).attr('data-value');
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'get_user_report',req_id:data_val},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('#reported_user').html( record.user_reported);
                    $('#reporting_user').html(record.user_reporting);
                    $('#reporting_url').html(record.report_url);
                    $('#reporting_reason').html(record.report_reason);
                    $('#report_details').modal('show');
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 626)');
                } 
            });
	});
	
        $(document).on('click', '.delete_report_items', function(){
            var report_id = $(this).attr('data-value');
            if(confirm('Are you sure to delete ? This cannot be undone')){
                $('.admin-loading-image').show();
                $.ajax({
                    type: 'POST',
                    url: 'services_admin_api.php',
                    data: {mode:'delete_report_data',request_id: report_id},
                    success: function (response) {
                        var record = JSON.parse(response);
                        if(record.status == 'success'){
                            getUserReportingListing();
                        }
                        $('.admin-loading-image').hide();
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again. (Error Code: 627)');
                    } 
                });
            }
        });
        $(document).on('change', '.change_request_status', function(){
            var request_id = $(this).attr('data-form-request-id');
            var status     = $(this).val();
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'change_user_report_status',request_id:request_id, status: status},
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status == 'success'){
                        getUserReportingListing();
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 628)');
                } 
            });
        });
</script>