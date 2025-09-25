<?php
$displayNameArray = array('AG' => 'Folder', 'AR' => 'Archive', 'CO' => 'Collection', 'SG' => 'Sub-Group', 'RE' => 'Records', 'IT' => 'File');
$type = (isset($_SESSION['type']) && $_SESSION['type'] == 'A') ? true : false;
$loggedInType = isset($_SESSION['aib']['user_data']['user_type']) ? $_SESSION['aib']['user_data']['user_type']  : '';
?>
<div class="col-md-12">
    <div class="marginTop20">

    </div>
    <table id="manage_records_table_data" class="display table" width="100%" cellspacing="0" cellpadding="0">  
        <thead>  
            <tr>  
                <th width="30%" class="text-center">Title</th>  
                <th width="10%" class="text-center">Type </th>
                <?php if($shareType != 'shared_to_user'){ ?>
                    <th width="12%" class="text-center">Shared With </th>
                <?php }else{ ?>
                    <th width="12%" class="text-center">Who Shared</th>
                <?php } ?>
                <?php if($loggedInType == 'A'){ ?>   
                    <th>Society</th>
                <?php } ?>
                <th width="23%">Created</th>  
		<th width="23%">Shared date</th>  
                <th width="25%" class="text-center no-sort">Actions</th>  
            </tr>  
        </thead>  
        <tbody>  
            <?php
            if (count($finalDataArray) > 0) {
                foreach ($dataArray as $key => $dataArray) {
                    $refrenceUserName = ($shareType == 'shared_to_user') ? $dataArray['refrence_details']['link_owner_login'] : $dataArray['user_details']['user_login'];
                    if(strtolower($refrenceUserName) != strtolower($_SESSION['aib']['user_data']['user_login'])){
                    ?>
                    <tr>
                        <td>
                            <?php if ($dataArray['item_type'] == 'SG') { ?>
                                <span style="float:left;"><input type="checkbox" class="form-check-input assign-assistant" data-item-id="<?php echo $dataArray['item_id']; ?>" data-item-value="<?php echo urldecode($dataArray['item_title']); ?>"></span>
                            <?php } ?>
                            <span data_item_type="<?php echo $dataArray['item_type']; ?>" data-folder-id="<?php echo $dataArray['item_id']; ?>"><?php if($dataArray['properties']['aib:private'] == 'Y'){ ?> <img style="width:16px;" src="<?php echo IMAGE_PATH.'private-record.png'; ?>" title="Private record" alt="Private Records" /> <?php } ?><?php echo urldecode($dataArray['item_title']); ?></span>
                        </td>
                        <td align="center">
                            <?php
                            $count_display = '';
                            if ($dataArray['item_type'] == 'AG') {
                                $imageSrc = IMAGE_PATH . 'folder.png';
                            } elseif ($dataArray['item_type'] == 'AR') {
                                $imageSrc = IMAGE_PATH . 'folder.png';
                                $count_display = 'Collections';
                            } elseif ($dataArray['item_type'] == 'CO') {
                                $imageSrc = IMAGE_PATH . 'box.png';
                                $count_display = 'Sub-Groups';
                            } elseif ($dataArray['item_type'] == 'SG') {
                                $imageSrc = IMAGE_PATH . 'folder.png';
                                $count_display = 'Rec';
                            } elseif ($dataArray['item_type'] == 'RE' && isset($dataArray['first_thumb'])) {
                                $imageSrc = THUMB_URL . '?id=' . $dataArray['first_thumb'];
                            } elseif ($dataArray['item_type'] == 'RE' && empty($dataArray['first_thumb'])) {
                                $imageSrc = RECORD_THUMB_URL . '?id=' . $dataArray['item_id'];
                                $count_display = 'Items';
                            } elseif ($dataArray['item_type'] == 'IT') {
                                $imageSrc = THUMB_URL . '?id=' . $dataArray['files'][0]['file_id'];
                            }
                            ?>
                            <img style="width:32px;" src="<?php echo $imageSrc; ?>" alt="Item type" /><br>
                            <?php echo $displayNameArray[$dataArray['item_type']]; ?><br>
                            <?php
                            echo ($count_display != '') ? $dataArray['child_count'] . ' ' . $count_display : '';
                            if (isset($dataArray['sg_count']) && $dataArray['sg_count'] > 0) {
                                echo '<br>' . $dataArray['sg_count'] . ' Sub-Groups';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
//                            echo ($shareType == 'shared_to_user') ? $dataArray['refrence_details']['link_owner_login']: $dataArray['user_details_name']; 
                            if($shareType == 'shared_to_user'){
                                if(isset($dataArray['refrence_details']['link_owner_login']) && !empty($dataArray['refrence_details']['link_owner_login']) ){
                                    echo ($dataArray['refrence_details']['link_owner_login'] =='root')? 'anonymous' :$dataArray['refrence_details']['link_owner_login'];
                                }else{
                                    echo 'anonymous';
                                }
                            }else{
                                if(!empty($dataArray['user_details_name']) && $dataArray['user_details_name'] !='root'){
                                    echo ($dataArray['user_details_name'] !='')? $dataArray['refrence_details']['link_owner_email'].'('.$dataArray['user_details_name'].')' :'anonymous';
                                }else{
                                    echo 'anonymous <br>'.$dataArray['refrence_details']['link_owner_email'];
                                } 
                            }
                            ?>
                        </td>
                        <?php if($loggedInType == 'A'){ ?>   
                            <td><?php echo isset($dataArray['user_archive_group'])? $dataArray['user_archive_group'] : 'Public User';  ?></td>
                        <?php } ?>
                        <td><?php echo date('m/d/Y h:i:s A', $dataArray['item_create_stamp']); ?></td>
                        <td><?php echo $dataArray['refrence_details']['link_create_date']; ?></td>
                        <td style="width: 8%;">
                          <!--<span data-field-delete-id="<?php echo $dataArray['item_details']['item_id']; ?>"  class="delete_item_from_scrapbook"><img title="Delete from scrapbook" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span>-->
                        <?php if($folder_id == ''){ ?>
                            <span  data-field-delete-id="<?php echo $dataArray['refrence_item_id']; ?>" data-item-id="<?php echo $dataArray['item_id']; ?>" data-removed-user="<?php echo $dataArray['user_details']['user_login']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span>
                        <?php } ?>    
                        <span data-field-delete-id="">
                            <?php if($dataArray['item_type'] == 'RE'){ ?>
                                <a target="_blank" href="../item-details.php?folder_id=<?php echo $dataArray['item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a>
                            <?php } ?>
                            <?php if($dataArray['item_type'] == 'IT'){ 
                                $item_parent = ($folder_id != '') ? $folder_id : $dataArray['item_record_parent']; ?>
                                <a target="_blank" href="../item-details.php?folder_id=<?php echo $item_parent; ?>&itemId=<?php echo $dataArray['item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a>
                            <?php } ?>
                        </span>  
<!--                            <?php
                            if ($dataArray['item_type'] == 'AG') {
                                $image = 'active.png';
                                if ($dataArray['prop_details']['status'] == 0) {
                                    $image = 'deactive.png';
                                }
                                ?>
                                <span class="manage-archive-group"><a href="manage_archive_registration.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive Data" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                <span class="manage-archive-group"><a href="manage_archive_group_data.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive group" src="<?php echo IMAGE_PATH . 'edit_icon.png'; ?>" alt="" /></a></span>
                                <span  data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-staus="<?php echo $dataArray['prop_details']['status']; ?>"  class="change_status_item"><img title="Change status" src="<?php echo IMAGE_PATH . $image; ?>" alt="" /></span>
                            <?php } else { ?>
                                <?php if ($dataArray['item_type'] == 'RE') { ?>
                                    <span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id=<?php echo $dataArray['item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                    <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH . 'pdf_icon.gif'; ?>" alt="" /></a></span>
                                    <span  data-field-delete-id="<?php echo $dataArray['refrence_item_id']; ?>" data-item-id="<?php echo $dataArray['item_id']; ?>" data-removed-user="<?php echo $dataArray['user_details']['user_login']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span>
                                <?php } ?>
                                <?php if ($dataArray['item_type'] == 'IT') {
										$hide_view = '';
										if($folderId ==''){
											$hide_view = 'hidden';
										}
									?>
                                    <span data-field-delete-id="" ><a target="_blank" href="../item-details.php?folder_id=<?php echo $folderId; ?>&itemId=<?php echo $dataArray['item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                    <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH . 'pdf_icon.gif'; ?>" alt="" /></a></span>
                                    <span data-field-delete-id=""><a target="_blank" href="<?php echo THUMB_URL . '?id=' . $dataArray['or_file_id'] . '&download=1'; ?>" download><img title="Image" src="<?php echo IMAGE_PATH . 'download.png'; ?>" alt="" /></a></span>
                                    <span  data-field-delete-id="<?php echo $dataArray['refrence_item_id']; ?>" data-item-id="<?php echo $dataArray['item_id']; ?>" data-removed-user="<?php echo $dataArray['user_details']['user_login']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span>
                                       <?php } ?>
                            <?php } ?> -->
                        </td>
                    </tr>
                    <?php }
                }
            }   
			if($shareType != 'shared_to_user'){   
			foreach($finalArrayResponse as $record_data){
			$rec_val = $record_data['records']; 
			
			if(isset($rec_val['share_url_link'])){
				$imageSrcNew = THUMB_URL . '?id='.$rec_val['share_thoumb_id'];
				$imageType = 'Item';
			}else{
				$imageSrcNew = RECORD_THUMB_URL . '?id='.$rec_val['share_item_id'];
				$imageType = 'Record';
			}
			  
            ?>
			<tr>
				<td><?php echo $rec_val['share_title'];?></td>
				<td align="center">
					<img style="width:32px;" src="<?php echo $imageSrcNew; ?>" alt="Item type" /><br/>
					<?php echo $imageType;?>
				</td>
				<td>anonymous<br/> (<?php echo $rec_val['share_email'];?>)</td>
				<td><?php echo date('m/d/Y h:i:s A',$rec_val['item_created_date']);?></td>
				<td><?php echo date('m/d/Y h:i:s A',$rec_val['share_created_date']);?></td>
				<td>
				<span  data-field-delete-id="<?php echo $record_data['item_id']; ?>"  class="delete_listing_item_anony"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span>
					 <span> 
						 <?php if(isset($rec_val['share_url_link'])){?>
						 <a target="_blank" href="../item-details.php?folder_id=<?php echo $rec_val['share_url_link']; ?>&itemId=<?php echo $rec_val['share_item_id'];?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a>
						 <?php }else{?>
						 <a target="_blank"  href="../item-details.php?folder_id=<?php echo $rec_val['share_item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a>
						 <?php }?>
					</span>  
				</td>
			</tr> 
			<?php } }?>
        </tbody>  
    </table>
</div>
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
	
	$(document).on('click', '.delete_listing_item_anony', function () {
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
                        getItemDetailsById(parent_id);
                    } else {
                        showPopupMessage('error', 'Something went wrong! Please try again. (Error Code: 1060)');
                        return false;
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 1061)');
                }
            });
        }
    });
	
</script>