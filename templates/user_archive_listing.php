<?php  
    $displayNameArray = array('AG'=>'Folder','AR'=>'Archive','CO'=>'Collection','SG'=>'Sub-Group','RE'=>'Records','IT'=>'File');
    $type = (isset($_SESSION['type']) && $_SESSION['type'] == 'A') ? true : false;
?>
<div class="col-md-12">
    <div class="row">
        <ul class="listing">
            <li><a href="javascript:void(0);"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
            <?php
            foreach ($treeDataArray as $key => $treeData) { ?>
                <li  class="getItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="javascript:void(0);"><?php echo $treeData['item_title']; ?></a></li>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="col-md-12">
    <div class="marginTop20">
        <?php
		$massImport='';
		if(ALLOW_MASS_IMPORT==true)
		{
			$massImport='<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="import_record">Import Records</button>';
		}
		 switch($itemData['item_type']){ 
            case 'AG':
                echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_archive">Create Archive</button>';
                break;
            case 'AR':
                if(!$type){
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_collection">Create Collection</button>'.$massImport;
                }
                break;
            case 'CO':
                if(!$type){
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_sub_group">Create Sub Group</button>';
                    echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="assignment">Assignment</button>';
                }
                break;
            case 'SG':
                if(!$type){
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_sub_group">Create Sub Group</button>';
                    echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>'.$massImport;
                    echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="assignment">Assignment</button>';
                }else{
                    echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>'.$massImport;
                }
                break;
            case 'RE':
                if(!$type){
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="add_item">Add Item</button>';
                }
                break;
            default :
                echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_archive_group">Create Archive Group</button>';
                break;
        } ?>
    </div>
    <div class="tableScroll">
    <table id="manage_records_table_data" class="display table" width="100%" cellspacing="0" cellpadding="0">  
        <thead>  
            <tr>  
                <th width="40%" class="text-center">Title</th>  
                <th width="12%" class="text-center">Type </th>  
                <th width="23%">Created</th>  
                <th width="25%" class="text-center no-sort">Actions</th>  
            </tr>  
        </thead>  
        <tbody>  
            <?php 
            if(count($finalDataArray) > 0){ 
                foreach($finalDataArray as $key=>$dataArray){ ?>
                    <tr>
                        <td>
                            <?php if($dataArray['item_type'] == 'SG'){  ?>
                            <span style="float:left;"><input type="checkbox" class="form-check-input assign-assistant" data-item-id="<?php echo $dataArray['item_id']; ?>" data-item-value="<?php echo urldecode($dataArray['item_title']); ?>"></span>
                            <?php } ?>
                            <span <?php if($dataArray['item_type'] !='IT'){  ?> class="archive-record-listing getItemDataByFolderId " <?php } ?> data_item_type="<?php echo $dataArray['item_type']; ?>" data-folder-id="<?php echo $dataArray['item_id']; ?>"><?php echo urldecode($dataArray['item_title']); ?></span>
                        </td>
                        <td align="center">
                            <?php
                            $count_display = '';
                            if($dataArray['item_type'] == 'AG'){
                                $imageSrc = IMAGE_PATH.'folder.png';
                            }elseif($dataArray['item_type'] == 'AR'){
                                $imageSrc = IMAGE_PATH.'folder.png';
                                $count_display = 'Collections';
                            }elseif($dataArray['item_type'] == 'CO'){
                                $imageSrc = IMAGE_PATH.'box.png';
                                $count_display = 'Sub-Groups';
                            }elseif($dataArray['item_type'] == 'SG'){
                                $imageSrc = IMAGE_PATH.'folder.png';
                                $count_display = 'Rec';
                            }elseif($dataArray['item_type'] == 'RE'){
                                $imageSrc = THUMB_URL.'?id='.$dataArray['first_thumb'];
                                $count_display = 'Items';
                            }elseif($dataArray['item_type'] == 'IT'){
                                $imageSrc = THUMB_URL.'?id='.$dataArray['tn_file_id'];
                            } ?>
                            <img style="width:32px;" src="<?php echo $imageSrc; ?>" alt="Item type" /><br>
                            <?php echo $displayNameArray[$dataArray['item_type']]; ?><br>
                            <?php 
                            echo ($count_display != '') ? $dataArray['child_count'].' '.$count_display : ''; 
                            if(isset($dataArray['sg_count']) && $dataArray['sg_count'] > 0){
                                echo '<br>'.$dataArray['sg_count'].' Sub-Groups';
                            }
                            ?>
                        </td>
                        <td><?php echo date('m/d/Y h:i:s A',$dataArray['item_create_stamp']); ?></td>
                        <td>
                            <?php if($dataArray['item_type'] == 'AG'){
                                $image = 'active.png';
                                if($dataArray['prop_details']['status'] == 0){
                                    $image = 'deactive.png';
                                }
                                ?>
                                <span class="manage-archive-group"><a href="manage_archive_registration.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive Data" src="<?php echo IMAGE_PATH.'view.png'; ?>" alt="" /></a></span>
                                <span class="manage-archive-group"><a href="manage_archive_group_data.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive group" src="<?php echo IMAGE_PATH.'edit_icon.png'; ?>" alt="" /></a></span>
                                <span  data-field-delete-id="<?php echo $dataArray['item_id']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH.'delete_icon.png'; ?>" alt="" /></span>
                                <span  data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-staus="<?php echo $dataArray['prop_details']['status']; ?>"  class="change_status_item"><img title="Change status" src="<?php echo IMAGE_PATH.$image; ?>" alt="" /></span>
                            <?php }else{ ?>
                                <?php if($dataArray['item_type'] == 'RE' ){ ?>
                                    <span data-field-edit-id="<?php echo $dataArray['item_id']; ?>" class="" data_item_type="<?php echo $dataArray['item_type']; ?>">
                                        <a href="record_modify.php?opcode=edit&primary=<?php echo $dataArray['item_id']; ?>&parent_id=<?php echo $folderId; ?>&src=records&srckey=&searchval=&srcmode=list&srcpn=1&aibnav=<?php echo urlencode('primary='.$dataArray['item_id'].'&parent_id='.$folderId.'&src=records&srckey=&searchval=&srcmode=list&srcpn=1') ?>" >
                                            <img title="Edit" src="<?php echo IMAGE_PATH.'edit_icon.png'; ?>" alt="" />
                                        </a>
                                    </span>
                                <?php }elseif($dataArray['item_type'] == 'IT'){ ?>
                                    <span data-field-edit-id="<?php echo $dataArray['item_id']; ?>" class="" data_item_type="<?php echo $dataArray['item_type']; ?>">
                                        <a href="record_modify.php?opcode=edit&primary=<?php echo $dataArray['item_id']; ?>&parent_id=<?php echo $folderId; ?>&record_id=<?php echo $folderId; ?>&item_id=<?php echo $dataArray['item_id']; ?>&src=records&srckey=&searchval=&srcmode=list&srcpn=1&aibnav=<?php echo urlencode('primary='.$dataArray['item_id'].'&parent_id='.$folderId.'&record_id='.$folderId.'&item_id='.$dataArray['item_id'].'&src=records&srckey=&searchval=&srcmode=list&srcpn=1' ) ?>">
                                            <img title="Edit" src="<?php echo IMAGE_PATH.'edit_icon.png'; ?>" alt="" />
                                        </a>
                                    </span>
                                <?php }else{ ?>
                                <span data-field-edit-id="<?php echo $dataArray['item_id']; ?>" class="edit_listing_item" data_item_type="<?php echo $dataArray['item_type']; ?>"><img title="Edit" src="<?php echo IMAGE_PATH.'edit_icon.png'; ?>" alt="" /></span>
                                <?php } ?>
                                <span data-ocr-folder-id="<?php echo $dataArray['item_id']; ?>" class="perform-ocr-onfolder"><img title="OCR" src="<?php echo IMAGE_PATH.'ocr.png'; ?>" alt="" /></span>
                                <span data-field-delete-id="<?php echo $dataArray['item_id']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH.'delete_icon.png'; ?>" alt="" /></span>
                                <?php if($dataArray['item_type'] == 'RE' ){ ?>
                                <span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id=<?php echo $dataArray['item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH.'view.png'; ?>" alt="" /></a></span>
                                <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH.'pdf_icon.gif'; ?>" alt="" /></a></span>
                                <!--<span data-field-delete-id=""><a target="_blank" href="<?php echo $imageSrc; ?>" download ><img title="Image" src="<?php echo IMAGE_PATH.'download.png'; ?>" alt="" /></a></span>-->
                                <?php } ?>
                                 <?php if($dataArray['item_type'] == 'IT'){ ?>
                                <span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id=<?php echo $folderId;?>&itemId=<?php echo $dataArray['item_id']; ?>"><img title="View" src="<?php echo IMAGE_PATH.'view.png'; ?>" alt="" /></a></span>
                                <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH.'pdf_icon.gif'; ?>" alt="" /></a></span>
                                <span data-field-delete-id=""><a target="_blank" href="<?php echo THUMB_URL.'?id='.$dataArray['or_file_id'].'&download=1'; ?>" download><img title="Image" src="<?php echo IMAGE_PATH.'download.png'; ?>" alt="" /></a></span>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
            <?php } } ?>
        </tbody>  
    </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#manage_records_table_data').DataTable({ 
		"pageLength": 100,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }] 
        });
    });
</script>