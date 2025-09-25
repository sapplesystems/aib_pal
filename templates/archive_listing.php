<?php
$displayNameArray = array('AG' => 'Folder', 'AR' => 'Archive', 'CO' => 'Collection', 'SG' => 'Sub-Group', 'RE' => 'Records', 'IT' => 'File');
$type = (isset($_SESSION['type']) && $_SESSION['type'] == 'A') ? true : false; 
$currentfolder_id = end($treeDataArray); 
?>
<div class="col-md-12">
    <div class="row">
        <ul class="listing" id="heading_listing">
            <li data-folder-id ='0' ><a href="javascript:void(0);"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
            <?php foreach ($treeDataArray as $key => $treeData) { ?>
                <?php if($treeData['item_title'] != '_STDUSER'){ ?>
                <?php if ($_SESSION['aib']['user_data']['user_type'] == 'U' && ($treeData['item_type'] == 'AR' || $treeData['item_type'] == 'CO')){ ?>
                    <li  class="getItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>" data-folder-name="<?php echo $treeData['item_title'];?>"><a href="javascript:void(0);">My Home</a></li>
                <?php }else{ ?>
                <li  class="getItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>" data-folder-name="<?php echo $treeData['item_title'];?>"><a href="javascript:void(0);"><?php echo $treeData['item_title']; ?></a></li>
            <?php } } } ?>
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
        if ($_SESSION['aib']['user_data']['user_type'] == 'U') {
            switch ($itemData['item_type']) {
                case 'SG': echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>'.$massImport;
                    break;
                 case 'RE':
                        echo '<a href="'.$_SESSION['record_edit_link'].'"><button type="button" class="btn btn-admin borderRadiusNone">Add Item</button></a>';
                        break;
                default: echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_sub_group">Create Sub Group</button>';
                    echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>'.$massImport;
                    break;
            }
        } else {
            $moveItem    = '';
            $showPublish = '';
            switch ($itemData['item_type']) {
                case 'AG':
                    $showPublish = 'yes';
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_archive">Create Archive</button>';
                    break;
                case 'AR':
                    $moveItem = 'yes';
                    $showPublish = 'yes';
                    if (!$type) {
                        echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_collection">Create Collection</button>';
                    }
                    break;
                case 'CO':
                    $moveItem = 'yes';
                    $showPublish = 'yes';
                    if (!$type) {
                        echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_sub_group">Create Sub Group</button>';
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="assignment">Assignment</button>';
                    }
                    break;
                case 'SG':
                    $showPublish = 'yes';
                    $moveItem = 'yes';
                    if (!$type) {
                        echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_sub_group">Create Sub Group</button>';
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>'.$massImport;
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="assignment">Assignment</button>';
                    } else {
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>'.$massImport;
                    }
                    break;
                case 'RE':
                    if (!$type) {
                        echo '<a href="'.$_SESSION['record_edit_link'].'"><button type="button" class="btn btn-admin borderRadiusNone">Add Item</button></a>';
                    }
                    break;
                default :
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_archive_group">Create Archive Group</button>';

                    break;
            }
        }
        ?>
    </div>
    <div class="tableScroll">
    <table id="manage_records_table_data" class="display table" width="100%" cellspacing="0" cellpadding="0">  
        <thead>  
            <tr>  
                <th width="40%" class="text-center">Title</th>  
                <th width="12%" class="text-center">Type </th>  
                <th width="23%" class="text-center">Created</th>  
                <th width="25%" class="text-center no-sort">Actions</th>  
            </tr>  
        </thead>  
        <tbody>  
            <?php
            if (count($finalDataArray) > 0) {   
                foreach ($finalDataArray as $key => $dataArray) { 
                    ?>
                    <tr>
                        <td>
                            <?php
                            if(isset($dataArray['properties']['ebay_url']) && $dataArray['properties']['ebay_url'] != '' && $ebayCheckCondition != 'N' ){
                                echo '<span style="display:none;">1</span>';
                            }
                            ?>
                            <?php if ($dataArray['item_type'] == 'SG') { ?>
                                <span style="float:left;"><input type="checkbox" class="form-check-input assign-assistant" data-item-id="<?php echo $dataArray['item_id']; ?>" data-item-value="<?php echo urldecode($dataArray['item_title']); ?>"></span>
                            <?php } ?>
                            <span <?php if ($dataArray['item_type'] != 'IT') { ?> class="archive-record-listing getItemDataByFolderId " <?php } ?> data_item_type="<?php echo $dataArray['item_type']; ?>" data-folder-id="<?php echo $dataArray['item_id']; ?>">
                                <?php if(($dataArray['item_type'] == 'RE' || $dataArray['item_type'] == 'IT') && $dataArray['properties']['aib:private'] == 'Y'){?> <img style="width:16px;" src="<?php echo IMAGE_PATH.'private-record.png'; ?>" title="Private record" alt="Private Records" /><?php } ?>
                                <?php echo urldecode($dataArray['item_title']); ?>
                            </span>
                            <?php
                            if(isset($dataArray['properties']['ebay_url']) && $dataArray['properties']['ebay_url'] != '' && $ebayCheckCondition != 'N' ){
                                echo '<br><img src="'.IMAGE_PATH.'ebay.png" alt="" />';
                            }
                            ?>
                        </td>
                        <td align="center">
                            <?php
                            $count_display = '';
                            if ($dataArray['item_type'] == 'AG') {
                                $imageSrc = IMAGE_PATH . 'folder.png';
                            } elseif ($dataArray['item_type'] == 'AR') {
                                $imageSrc = IMAGE_PATH . 'folder.png';
                                $count_display = 'Collection(s)';
                            } elseif ($dataArray['item_type'] == 'CO') {
                                $imageSrc = IMAGE_PATH . 'box.png';
                                $count_display = 'Sub-Group(s)';
                            } elseif ($dataArray['item_type'] == 'SG') {
                                $imageSrc = IMAGE_PATH . 'folder.png';
                                $count_display = 'Rec(s)';
                            } elseif ($dataArray['item_type'] == 'RE') {
                                $imageSrc = THUMB_URL . '?id=' . $dataArray['first_thumb'];
                                $count_display = 'Item(s)';
                            } elseif ($dataArray['item_type'] == 'IT') {
                                $imageSrc = THUMB_URL . '?id=' . $dataArray['tn_file_id'];
                                if($dataArray['is_link'] == 'Y' && $dataArray['link_type'] == 'U'){
                                    $imageSrc = IMAGE_PATH . 'external_url_thumb.jpg';
                                }
                            }
                            ?>
                            <img style="width:32px;" src="<?php echo $imageSrc; ?>" alt="Item type" /><br>
                            <?php if($dataArray['is_link'] == 'Y' && $dataArray['link_type'] == 'U'){ echo 'Link'; } else{ echo $displayNameArray[$dataArray['item_type']]; } ?><br>
                            <?php
                            echo ($count_display != '') ? $dataArray['child_count'] . ' ' . $count_display : '';
                            if (isset($dataArray['sg_count']) && $dataArray['sg_count'] > 0) {
                                echo '<br>' . $dataArray['sg_count'] . ' Sub-Group(s)';
                            }
                            ?>
                        </td>
                        <td><?php echo date('m/d/Y h:i:s A', $dataArray['item_create_stamp']); ?></td>
                        <td>
                            <?php
                            if ($dataArray['item_type'] == 'AG') {
                                $image = 'active.png';
                                $imageEbay = 'ebay-enable.png';
                                $ebayValue = 'N';
                                if ($dataArray['prop_details']['status'] == 0) {
                                    $image = 'deactive.png';
                                }
                                if (isset($dataArray['prop_details']['ebay_status']) && $dataArray['prop_details']['ebay_status'] == 'N') {
                                     $imageEbay = 'ebay-disable.png';
                                     $ebayValue = 'Y';
                                }
                                ?>
                                <span class="manage-archive-group"><a href="manage_archive_registration.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive Data" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                <span class="manage-archive-group"><a href="manage_archive_group_data.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive group" src="<?php echo IMAGE_PATH . 'edit_icon.png'; ?>" alt="" /></a></span>
                                <!--<span  data-field-delete-id="<?php echo $dataArray['item_id']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span>-->
                                <span  data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-staus="<?php echo $dataArray['prop_details']['status']; ?>"  class="change_status_item"><img title="Change status" src="<?php echo IMAGE_PATH . $image; ?>" alt="" /></span>
                                <span  data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-ebay-staus="<?php echo $ebayValue; ?>"  class="change_ebay_status"><img title="Change Ebay status" src="<?php echo IMAGE_PATH . $imageEbay; ?>" alt="" /></span>
                                <?php } else { ?>
                                <?php if ($dataArray['item_type'] == 'RE') { ?>
                                    <span data-field-edit-id="<?php echo $dataArray['item_id']; ?>" class="" data_item_type="<?php echo $dataArray['item_type']; ?>">
                                        <a id="record_edit_<?php echo $dataArray['item_id']; ?>" href="record_modify.php?opcode=edit&primary=<?php echo $dataArray['item_id']; ?>&parent_id=<?php echo $folderId; ?>&src=records&srckey=&searchval=&srcmode=list&srcpn=1&aibnav=<?php echo urlencode('primary=' . $dataArray['item_id'] . '&parent_id=' . $folderId . '&src=records&srckey=&searchval=&srcmode=list&srcpn=1') ?>" >
                                            <img title="Edit" src="<?php echo IMAGE_PATH . 'edit_icon.png'; ?>" alt="" />
                                        </a>
                                    </span>
                                <?php } elseif ($dataArray['item_type'] == 'IT') { ?>
                                    <span data-field-edit-id="<?php echo $dataArray['item_id']; ?>" class="" data_item_type="<?php echo $dataArray['item_type']; ?>">
                                        <a href="record_modify.php?opcode=edit&primary=<?php echo $dataArray['item_id']; ?>&parent_id=<?php echo $folderId; ?>&record_id=<?php echo $folderId; ?>&item_id=<?php echo $dataArray['item_id']; ?>&src=records&srckey=&searchval=&srcmode=list&srcpn=1&aibnav=<?php echo urlencode('primary=' . $dataArray['item_id'] . '&parent_id=' . $folderId . '&record_id=' . $folderId . '&item_id=' . $dataArray['item_id'] . '&src=records&srckey=&searchval=&srcmode=list&srcpn=1') ?>">
                                            <img title="Edit" src="<?php echo IMAGE_PATH . 'edit_icon.png'; ?>" alt="" />
                                        </a>
                                    </span>
                                <?php } else { ?>
                                    <span data-field-edit-id="<?php echo $dataArray['item_id']; ?>" class="edit_listing_item" data_item_type="<?php echo $dataArray['item_type']; ?>"><img title="Edit" src="<?php echo IMAGE_PATH . 'edit_icon.png'; ?>" alt="" /></span>
                                <?php }  ?>
                                <?php  if($dataArray['item_type'] == 'IT' && $_SESSION['aib']['user_data']['user_type'] !='U'){ ?>
                                <span data-ocr-folder-id="<?php echo $dataArray['item_id']; ?>" class="perform-ocr-onfolder"><img title="OCR" src="<?php echo IMAGE_PATH . 'ocr.png'; ?>" alt="" /></span><?php } ?>
                                <?php    if ($dataArray['item_type'] == 'IT' || $dataArray['item_type'] == 'RE' || $dataArray['item_type'] == 'SG') { ?>
                                     <span data-field-delete-id="<?php echo $dataArray['item_id']; ?>"  class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span> 
                                <?php }?>
                                <?php if ($dataArray['item_type'] == 'CO') { ?>
                                    <!--<span class="list_move_item" data-field-item-id="<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                <?php } elseif ($dataArray['item_type'] == 'SG') { ?>
                                    <!--<span class="list_move_item" data-field-item-id="<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                <?php } ?>
                                <?php if ($dataArray['item_type'] == 'RE') { ?>
                                    <span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id=<?php echo $dataArray['item_id']; ?>&share=1"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                    <?php  if ($_SESSION['aib']['user_data']['user_type'] != 'U') { ?>
                                        <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH . 'pdf_icon.gif'; ?>" alt="" /></a></span>
                                    <?php } ?>
                                    <!-- <span class="list_move_item" data-field-item-id="<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                    <?php if($_SESSION['aib']['user_data']['user_type'] == 'U') { ?>
                                     <span data-field-record-id="<?php echo $dataArray['item_id']; ?>" class="share_with_users"><img title="Share with user" src="<?php echo IMAGE_PATH . 'share.png'; ?>" alt="" /></span>
                                    <?php }  if ($ebayCheckCondition != 'N') { ?>
                                    <span data-field-ebay-record-id="<?php echo $dataArray['item_id']; ?>" class="ebay_link"><img title="Ebay Url" src="<?php echo IMAGE_PATH . 'ebay.png'; ?>" alt="" /></span> 
                                    <?php }?>
                                    <span data-field-record-id="<?php echo $dataArray['item_id']; ?>" class="set_related_records"><img title="Set related records" src="<?php echo IMAGE_PATH . 'related-item.png'; ?>" alt="" /></span>
                                    <?php  if ($_SESSION['aib']['user_data']['user_type'] == 'U') {?>
                                     <!--<span data-field-record-id="<?php // echo $dataArray['item_id']; ?>" class="set_public_connections"><img title="Set public connections records" src="<?php echo IMAGE_PATH . 'public-connection.png'; ?>" alt="" /></span>-->
                                     <?php } ?>
                               <?php } ?>
                                <?php if ($dataArray['item_type'] == 'IT') { ?>
                                    <span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id=<?php echo $folderId; ?>&itemId=<?php echo $dataArray['item_id']; ?>&share=1"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                    <?php  if ($_SESSION['aib']['user_data']['user_type'] != 'U') { ?>
                                    <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH . 'pdf_icon.gif'; ?>" alt="" /></a></span>
                                    <span data-field-delete-id=""><a target="_blank" href="<?php echo THUMB_URL . '?id=' . $dataArray['or_file_id'] . '&download=1'; ?>" download><img title="Image" src="<?php echo IMAGE_PATH . 'download.png'; ?>" alt="" /></a></span>
                                     <?php } ?>
                                    <!--<span class="list_move_item" data-field-item-id=<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                <?php } ?>
                            <?php } 
                            if($_SESSION['aib']['user_data']['user_type'] == 'R' && $dataArray['item_type'] != 'AG' && $dataArray['item_type'] != 'RE' && $dataArray['item_type'] != 'IT'){ 
                                $image = 'active.png';
                                $current_status = 1;
                                if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == 0) {
                                    $image = 'deactive.png';
                                    $current_status = 0;
                                } ?>
                                  <span data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-staus="<?php echo $current_status; ?>"  class="superadmin_change_status"><img title="Change status" src="<?php echo IMAGE_PATH . $image; ?>" alt="" /></span>
                        <?php } ?>
                            <?php if($moveItem == 'yes'){ ?>
                                  <span class="move_item" item-id="<?php echo $dataArray['item_id']; ?>" item-type="<?php echo $dataArray['item_type']; ?>"><img title="Move item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>
                            <?php } ?>
                            <?php if($_SESSION['aib']['user_data']['user_type'] == 'R' && $showPublish == 'yes'){ 
                                $publishStatus = 'N';
                                $gray_scale    = '';
                                $publishText   = 'Hide';
                                if (isset($dataArray['properties']['publish_status']) && $dataArray['properties']['publish_status'] == 'N') {
                                    $publishStatus = 'Y';
                                    $gray_scale    = 'gray_scale';
                                    $publishText   = 'Show';
                                }
                                ?>
                                <span  data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-publish-staus="<?php echo $publishStatus; ?>"  class="change_publish_status <?php echo $gray_scale; ?>"><img title="<?php echo $publishText; ?>" src="<?php echo IMAGE_PATH .'publish.png'; ?>" alt="" /></span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php
		}
            }
            ?>
        </tbody>  
    </table>
    </div>
</div>
<span id="current_folder" current-folder-id="<?php echo $currentfolder_id['item_id'];?>"> </span>



<script type="text/javascript">
    $(document).ready(function () {
        var user_type = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        if(user_type == 'U'){
            $('.list_move_item').hide();
        }
        $('#manage_records_table_data').DataTable({
			"pageLength": 100,
            columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }]
        });
    });



</script>