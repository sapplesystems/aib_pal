<section class="content-header"><h5 class="list_title text-center"><span class="pull-left">Records To Be Processed </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h5></section>
<table id="assistant_data_uncomplete" class="display table" width="100%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th>Archive</th>
            <th>Sub Group</th>
            <th>Records To Do</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($uncompleteResponseData)) {
            foreach ($uncompleteResponseData as $unCompleteKey=>$uncompleteData) {
                if($uncompleteData['sub_group'] != ''){
                ?>
                <tr>
                    <td align="left"><?php echo $uncompleteData['archive']; ?></td>
                    <td align="left"><span class="edit_uncomplete_list" data-parent-id="<?php echo $unCompleteKey; ?>"><?php echo $uncompleteData['sub_group']; ?></span></td>
                    <td align="left"><?php echo count($uncompleteData['list']); ?></td>
                </tr>
            <?php } }
        } else {
            ?>
<?php } ?>
    </tbody>    
</table>
<section class="content-header"><h5 class="list_title">Completed Records</h5></section>
<table id="assistant_data_complete" class="display table" width="100%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th>Archive</th>
            <th>Sub Group</th>
            <th>Records Completed</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($completeResponseData)) {
            foreach ($completeResponseData as $completeKey=>$completeData) {
                if($completeData['sub_group'] != ''){
                ?>
                <tr>
                    <td align="left"><?php echo $completeData['archive']; ?></td>
                    <td align="left"><span class="edit_assistant_complete_list" data-parent-id="<?php echo $completeKey; ?>"><?php echo $completeData['sub_group']; ?></span></td>
                    <td align="left"><?php echo count($completeData['list']); ?></td>
                </tr>
            <?php } }
        } else {
            ?>
<?php } ?>
    </tbody>
</table>
<div class="modal fade" id="editUncompleteData" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" id="form-list-section">
            
        </div>
    </div>
</div>
<div class="modal fade" id="edit_complete_assistant_items" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" id="form_edit_complete_assistant">
            
        </div>
    </div>
</div>
<div class="modal fade" id="editCompletedAssistantData" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" id="assistant_completed_section">
            <div class="modal-header form_header">
                <h4 class="list_title" id="popup_heading">Edit Completed Record <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
            </div>
            <div class="modal-body">
                <form id="edit_completed_data" name="edit_completed_data" method="post" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-xs-4 control-label">Select Record</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" name="completed_record_listing" id="completed_record_listing">
                                    <option value="">--Please Select--</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label">Select Item</label>
                        <div class="col-xs-7">
                            <span class="custom-dropdown">
                                <select class="form-control" name="completed_item_listing" id="completed_item_listing">
                                    <option value="">--Please Select--</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-4 control-label"></label>
                        <div class="col-xs-7">
                            <button type="button" class="btn btn-info borderRadiusNone" name="edit_records_item" id="edit_records_item">Edit</button>
                            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#assistant_data_uncomplete').DataTable({"pageLength": 100});
        $('#assistant_data_complete').DataTable({"pageLength": 100});
    });
    $(document).on('click','.edit_uncomplete_list',function(){
        var data_parent_id = $(this).attr('data-parent-id');
       // getEditData(data_parent_id);
		window.location = 'assistant_uncomplete_data.php?parent_id=' + data_parent_id;
    });
    
    $(document).on('click', '.edit_assistant_complete_list', function(){
        var data_parent_id = $(this).attr('data-parent-id');
        getRecordItemListing(data_parent_id, 'completed_record_listing','record');
    });
    
    $(document).on('change','#completed_record_listing', function(){
        var parent_id = $(this).val();
        if(parent_id != ''){
            getRecordItemListing(parent_id, 'completed_item_listing','item');
        }
    });
    
/*     $(document).on('click','#edit_records_item', function(){
        var record_id = $('#completed_record_listing').val();
        var item_id   = $('#completed_item_listing').val();
        if(record_id != ''){
            $("#completed_item_listing").html('<option value="">--Please Select--</option>');
            $('#editCompletedAssistantData').modal('hide');
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'edit_assistant_completed_data',record_id:record_id,item_id:item_id},
                success: function (response) {
                    $('#form_edit_complete_assistant').html(response);
                    $('#edit_complete_assistant_items').modal('show');
                    $('.admin-loading-image').hide();
                }
            });
        }else{ 
			showPopupMessage('error','Please select a record');
            return false;
        }
    }); */
	
	$(document).on('click','#edit_records_item', function(){  
		var record_id = $('#completed_record_listing').val();
        var item_id   = $('#completed_item_listing').val();
		window.location = 'assistant_uncomplete_data.php?record_id='+record_id+'&&item_id='+item_id;
		
	});
    
    function getRecordItemListing(parent_id, container_id, type){
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'get_assistant_completed_record_list',parent_id:parent_id, type: type},
            success: function (response) {
                var record = JSON.parse(response);
                var option_list = '<option value="">--Please Select--</option>';
                for (i = 0; i < record.length; i++) {
                    option_list += '<option value="'+record[i].item_id+'">'+record[i].item_title+'</option>';
                }
                $('#'+container_id).html(option_list);
                $('#editCompletedAssistantData').modal('show');
                $('.admin-loading-image').hide();
            }
        });
    }
    
  /*   function getEditData(data_parent_id, item_id = null){
        $('.admin-loading-image').show();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'edit_assistant_uncomplete_data',parent_id:data_parent_id,item_id:item_id},
            success: function (response) {
                $('#form-list-section').html(response);
                $('#editUncompleteData').modal('show');
                $('.admin-loading-image').hide();
            }
        });
    } */
    $(document).on('click','.back_uncomplete_list_data', function(){
        var data_count = $(this).attr('data-count');
        var previous_data_count = parseInt(parseInt(data_count)-1);
        if($('#uncomplete_list_'+previous_data_count).length){
            $('.uncomplete_list_data').hide();
            $('#uncomplete_list_'+previous_data_count).show();
        }else{
            
        }
    });
    $(document).on('click','.mark_as_completed', function(){
        var parent_id = $('#parent_id').val();
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode:'mark_item_as_complete',parent_id:parent_id },
            success: function (response){
                
            }
        });
    });
 /*    $(document).on('click','#update_form_data', function(){
        var itemsFormData = $("#edit_items_form").serialize();
        $('.admin-loading-image').show();
        $.ajax({ 
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_assistant_item', formData: itemsFormData},
            success: function (data){
                var result = JSON.parse(data);
                $('.admin-loading-image').hide();
                if(result.status == 'completed'){
                    window.location.reload()
                }else if(result.status == 'success'){
                    getEditData(result.parent_id, result.item_id );
                }else{ 
					showPopupMessage('error', result.message);
                    return false;
                }
            },
            error: function () {
                $('.admin-loading-image').hide(); 
				showPopupMessage('error','Something went wrong, Please try again');
            }
        });
    }); */
    
  /*   $(document).on('click','#update_completed_form_data', function(){
        var itemsFormData = $("#edit_items_completed_form").serialize();
        $('.admin-loading-image').show();
        $.ajax({ 
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'update_assistant_completed_item', formData: itemsFormData},
            success: function(data){
               var result = JSON.parse(data);
               if(result.status == 'success'){
                   $('#edit_complete_assistant_items').modal('hide');
                   var sub_group_id = $('#sub_group_id').val();
                   getRecordItemListing(sub_group_id, 'completed_record_listing','record');
               }
            },
            error: function () {
                $('.admin-loading-image').hide(); 
				showPopupMessage('error','Data not updated, Please try again.');
            }
        });
    }) */
</script>