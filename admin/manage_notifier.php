<?php
header('location: index.php');die; 
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
        <h1>Manage Notifier</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Manage Notifier</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Notifier</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row" id="dataTableDiv">
            <div class="col-md-12 tableStyle">
                <div class="">
                    <a id="add_new_notifier" href="javascript:void(0);" class="btn btn-admin borderRadiusNone marginLeft10 pull-left">Add Notifier</a>
                </div>
                <div class="tableScroll">
                    <table id="notifier_container" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                        <thead>  
                            <tr>  
                                <th width="30%" class="text-center">Word or Phrase</th> 
                                <th width="40%">Parent</th>
                                <th width="30%" class="text-center">Actions</th>
                            </tr>  
                        </thead>  
                        <tbody id="user_notifier_listing_section"></tbody>  
                    </table> 
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="add_new_notifier_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Add Notifier <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <div id="alert_msg" class="alert_addnotifier"><span> Currently you can add only 10 notifier</span></div>
                <form id="add_notifier_form" method="POST" name="add_notifier_form" class="form-horizontal" action="" >
                  <input type="hidden" name="total_notifire" id="total_notifire">
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Archive Group</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_group"  name="archive_group">
                                    <option value="">--All Archive Groups--</option> 
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Enter Word or Phrase</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" name="new_tags" id="new_tags" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="add_notifier_btn" id="add_notifier_btn">Add</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="edit_new_notifier_popup" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title">Edit Notifier <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="edit_notifier_form" method="POST" name="edit_notifier_form" class="form-horizontal" action="" >
                  <div class="form-group">
                        <label class="col-xs-3 control-label">Archive Group</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_group"  name="archive_group">
                                    <option value="">--All Archive Groups--</option> 
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Enter Word or Phrase</label>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" name="new_tags" id="new_tags" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="edit_notifier_btn" id="edit_notifier_btn">Edit</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal">Cancel</button>
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
        getNotifierListing();
        $("#add_notifier_form").validate({
            rules: {
                new_tags: {
                    required: true
                }
            },
            messages: {
                new_tags: {
                    required: "Please enter word and/or phrase"
                }
            }
        });
    });
    
    $(document).on('click','#add_new_notifier', function(){
        getArchiveListing()
        $('#add_new_notifier_popup').modal('show');
         var noOfNotifire = $('#total_notifire').val();
         if(noOfNotifire > 9 ){
             $('#alert_msg').show();
             $('#add_notifier_form').hide(); 
         }else{
             $('#alert_msg').hide();
             $('#add_notifier_form').show();
         }
    });
    
    $(document).on('click', '#add_notifier_btn', function(){
            if ($("#add_notifier_form").valid()){
            $('.admin-loading-image').show();
            var notifierFormData = $('#add_notifier_form').serialize();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'add_new_notifier', formData: notifierFormData },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status == 'success'){
                        $('#add_new_notifier_popup').modal('hide');
                        $("#add_notifier_form")[0].reset();
                        getNotifierListing();
                    }
                    showPopupMessage(record.status,record.message + ' (Error Code: 558)');
                     $('.admin-loading-image').hide();
                }
            });
        }   
    });
     var notifier_container = $('#notifier_container').DataTable({"pageLength": 100});
    function getNotifierListing(){
        $('.admin-loading-image').show();
        var image_path = '<?php echo IMAGE_PATH; ?>';
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'get_user_notifier_listing'},
            success: function (response) {
               
                notifier_container.clear().draw();
                var record = JSON.parse(response);
                $('#total_notifire').val(record.length);
                for (i = 0; i < record.length; i++) {
                        notifier_container.row.add([
                        record[i].keyword_title,
                        record[i].item_title,
			'<span title="Delete" class="delete_notifier" data-keyword='+record[i].keyword+' data-parent-id='+record[i].item_parent_id+' data-notifier-user-id=' +  record[i].user_id + ' ><img src="'+image_path+'delete_icon.png" alt="" /></span>'
                       // '<span class="notifier_edit" data-title="Edit" data-notifier-user-id=' + record[i].user_id + ' ><img src="'+image_path+'edit_icon.png" alt="" /></span><span title="Delete" class="delete_notifier" data-notifier-user-id=' +  record[i].user_id + ' ><img src="'+image_path+'delete_icon.png" alt="" /></span>'
                    ]).draw(false);
                }
                $("#dataTableDiv").show();
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 559)');
            } 
        });
    }
    
    function getArchiveListing(){
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'society_listing_for_public_user' },
            success: function (response) {
                $('#archive_group').html(response);
                $("#archive_group").prepend("<option value='' selected='selected'>--All Archive Groups--</option>");
                $('.admin-loading-image').hide();
            }
        });
    }
	 
    $(document).on('click', '.notifier_edit', function(){  
		$('#edit_new_notifier_popup').modal('show');
		var notifier_id = $(this).attr('data-notifier-user-id');
		 $.ajax({
			type: 'POST',
			url: 'services_admin_api.php',
			data: {mode:'get_notitify_edit_id' ,notifier_id:notifier_id},
			success: function (response) { 
			//alert("fddf");
			}
		});
	});
	
	$(document).on('click', '.delete_notifier', function(){  
		var user_id = $(this).attr('data-notifier-user-id');
		var keyword = $(this).attr('data-keyword');
		var parent_id = $(this).attr('data-parent-id');
		 $.ajax({
			type: 'POST',
			url: 'services_admin_api.php',
			data: {mode:'delete_user_notitify' ,user_id:user_id,keyword:keyword,parent_id:parent_id},
			success: function (response) { 
				var record = JSON.parse(response);
				if(record.status == 'success'){
					showPopupMessage('success',record.message);	
					getNotifierListing();
				}
			}
		});
	});
	$(function(){
		 $('#new_tags').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  {  
			$('#add_notifier_btn').click();
			return false;
		  }
		});
	});
</script>