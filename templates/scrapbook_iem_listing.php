<table id="manage_records_table_data" class="display table" width="100%" cellspacing="0" cellpadding="0">  
    <thead>  
        <tr>  
            <th width="28%" class="text-center">Title</th>  
            <th width="25%" class="text-center">Save Title</th>  
            <th width="10%" class="text-center">Type </th>  
            <th width="17%">Created</th>  
            <th width="20%" class="text-center no-sort">Actions</th>  
        </tr>  
    </thead>  
    <tbody>  
        <?php
        if (count($finalDataArray) > 0) {
            foreach ($finalDataArray as $key => $dataArray) {
                ?>
                <tr>
                    <td>
                        <span <?php if ($dataArray['item_type'] != 'IT') { ?> class="" <?php } ?> data_item_type="<?php echo $dataArray['item_type']; ?>" data-folder-id="<?php echo $dataArray['item_id']; ?>"><?php echo urldecode($dataArray['item_title']); ?></span>
                    </td>
                    <td>
            <span <?php if ($dataArray['item_type'] != 'IT') { ?> class="" <?php } ?> data_item_type="<?php if ($dataArray['item_details']['item_title'] != '') {
            echo $dataArray['item_details']['item_type'];
        } else {
            echo $dataArray['item_type'];
        } ?>" data-folder-id="<?php if ($dataArray['item_details']['item_title'] != '') {
                    echo $dataArray['item_details']['item_id'];
                } else {
                    echo $dataArray['item_id'];
                } ?>"> <?php if(!empty( $dataArray['scrapbook_title'])){ echo $dataArray['scrapbook_title'] ; }else{ echo urldecode($dataArray['item_title']) ; }  ?></span>
                    </td>
                    <td align="center">
        <?php
        if ($dataArray['item_type'] == 'IT') {
            $imageSrc = THUMB_URL . '?id=' . $dataArray['tn_file_id'];
        } elseif ($dataArray['item_type'] == 'RE') {
            $imageSrc = RECORD_THUMB_URL . '?id=' . $dataArray['item_id'];
        }
        if(!empty($dataArray['final_deref_stp_thumb'])){ $imageSrc = "http://". $dataArray['final_deref_stp_thumb'];}
        $redirect_page = ($dataArray['top_parents'] == '_STDUSER') ? 'people.php' : 'home.php';
        //if(isset($dataArray['item_details']['prop_details']['rediect_page']) && !empty($dataArray['item_details']['prop_details']['rediect_page']) && $dataArray['item_details']['prop_details']['rediect_page'] == 'public'){$redirect_page = 'people.php';}
        ?>
                        <img style="width:32px;" src="<?php echo $imageSrc; ?>" alt="Item type" />
                    </td>
                    <td><?php echo date('m/d/Y h:i:s A', $dataArray['item_create_stamp']); ?></td>
                    <td>
                        <?php if($scrapbook_ref == ''){ ?><span data-field-delete-id="<?php echo $dataArray['link_id']; ?>"  class="delete_item_from_scrapbook"><img title="Delete from scrapbook" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span> <?php } ?>
                        <span data-field-delete-id=""><a target="_blank" href="../<?php if($dataArray['item_type'] == 'RE'){echo $redirect_page.'?folder_id='.$dataArray['item_parents'].'&record_id='.$dataArray['item_id']; }else{ echo'item-details.php?folder_id='.$dataArray['item_parents']; ?>&itemId=<?php echo $dataArray['item_id']; } ?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                    </td>
                </tr>
    <?php
    }
}
?>
    </tbody>  
</table>
<script type="text/javascript">
    $(document).ready(function () {
        $('#manage_records_table_data').DataTable({
			"pageLength": 100,
            columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }]
        });
    });
   $(document).on('click', '.getItemDataByFolderId', function () {
        var item_folder_id = $(this).attr('data-folder-id');
        getItemDetailsById(item_folder_id);
    });
    function getItemDetailsById(id) {
        if (id) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'list_tree_items', folder_id: id,data_type:'share'},
                success: function (result) {
                    $('#data-listing-section').html(result);
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    $('.admin-loading-image').hide();
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 1059)');
                }
            });
        }
    }
 
    
</script>
