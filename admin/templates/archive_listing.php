<?php
$displayNameArray = array('AG' => 'Folder', 'AR' => 'Archive', 'CO' => 'Collection', 'SG' => 'Sub-Group', 'RE' => 'Records', 'IT' => 'File');
$type = (isset($_SESSION['type']) && $_SESSION['type'] == 'A') ? true : false;
$currentfolder_id = end($treeDataArray);

if(in_array($treeDataArray[1]['item_id'],AIB_DOWNLOAD_ENABLE_RECORD_LEVEL) or in_array($treeDataArray[0]['item_id'],AIB_DOWNLOAD_ENABLE_RECORD_LEVEL)){
	echo '<input type="hidden" id="AIB_DOWNLOAD_ENABLE_RECORD_LEVEL" value="1" />';
	
}else{
	echo '<input type="hidden" id="AIB_DOWNLOAD_ENABLE_RECORD_LEVEL" value="0" />'; 
}
?>
<div class="col-md-12">
    <div class="row" id="heading_listing_row">
        <ul class="listing" id="heading_listing">
            <li data-folder-id ='0' ><a href="javascript:void(0);"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
            <?php foreach ($treeDataArray as $key => $treeData) { ?>
                <?php if ($treeData['item_title'] != '_STDUSER') { ?>
                    <?php if ($_SESSION['aib']['user_data']['user_type'] == 'U' && ($treeData['item_type'] == 'AR' || $treeData['item_type'] == 'CO')) { ?>
                        <li  class="getItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>" data-folder-name="<?php echo $treeData['item_title']; ?>"><a href="javascript:void(0);">My Home</a></li>
                    <?php } else { ?>
                        <li  class="getItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>" data-folder-name="<?php echo $treeData['item_title']; ?>"><a href="javascript:void(0);"><?=(trim($treeData['item_title']) == 'ARCHIVE GROUP')?'Knowledge Base':$treeData['item_title']; ?></a></li>
                        <?php
                    }
                }
            }
            ?>
        </ul>
    </div>
</div>
<div class="col-md-12">
    <div class="marginTop20">
        <?php
        $massImport = '';
        if (ALLOW_MASS_IMPORT == true) {
            $massImport = '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="import_record">Import Records</button>';
        }
        if ($_SESSION['aib']['user_data']['user_type'] == 'U') {
            switch ($itemData['item_type']) {
                case 'SG': echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>' . $massImport;
                    break;
                case 'RE':
                    echo '<a href="' . $_SESSION['record_edit_link'] . '"><button type="button" class="btn btn-admin borderRadiusNone">Add Item</button></a>';
                    break;
                default: echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_sub_group">Create Sub Group</button>';
                    echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>' . $massImport;
                    break;
            }
        } else {
            $moveItem = '';
            $showPublish = '';
            switch ($itemData['item_type']) {
                case 'AG':
                    $showPublish = 'yes';
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_archive">Create Archive</button> ';
                    if ($_SESSION['aib']['user_data']['user_type'] == 'A') {
                        echo ' <button type="button" class="btn btn-admin borderRadiusNone" onclick="advertisement('.$currentfolder_id['item_id'].');">Set Universal Ad</button>';
                    }
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
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>' . $massImport;
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="assignment">Assignment</button>';
                    } else {
                        echo '<button type="button" class="btn btn-admin borderRadiusNone marginLeft10" id="add_record">Add Record</button>' . $massImport;
                    }
                    break;
                case 'RE':
                    if (!$type) {
                        echo '<a href="' . $_SESSION['record_edit_link'] . '"><button type="button" class="btn btn-admin borderRadiusNone">Add Item</button></a>';
                    }
                    break;
                default :
                    echo '<button type="button" class="btn btn-admin borderRadiusNone" id="create_archive_group">Create Knowledge Base</button>';

                    break;
            }
        }
        ?>
    </div>
    <div class="tableScroll">
        <?php //echo '<pre>';print_r($finalDataArray);echo '</pre>'; ?>
        <table id="manage_records_table_data" class="display table" width="100%" cellspacing="0" cellpadding="0">  
            <thead>  
                <tr>  
                    <th width="40%" class="text-center">Title</th>  
                    <th width="10%" class="text-center">Type </th>  
                    <th width="17%" class="text-center">Created</th>  
                    <th width="18%" class="text-center">Date Claimed</th>
                    <th width="15%" class="text-center no-sort">Actions</th>
                </tr>  
            </thead>  
            <tbody>  
                <?php
                if (count($finalDataArray) > 0) {
                    foreach ($finalDataArray as $key => $dataArray) {
                        // Skip rows with item_type = 'SG'
                        if (isset($dataArray['item_type']) && $dataArray['item_type'] === 'SG') {
                            continue;
                        }
                        $unclaimed_society = 'NA';
                        if (isset($dataArray['properties']['society_for_claim'])) {
                            if ($dataArray['properties']['society_for_claim'] == '1') {
                                $unclaimed_society = 'Not Claimed';
                            } else {
                                if($dataArray['properties']['approved_date']){
                                    $unclaimed_society = date('m/d/Y', strtotime($dataArray['properties']['approved_date']));
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <?php
                                if (isset($dataArray['properties']['ebay_url']) && $dataArray['properties']['ebay_url'] != '' && $ebayCheckCondition != 'N') {
                                    echo '<span style="display:none;">1</span>';
                                }
                                ?>
                                <?php if ($dataArray['item_type'] == 'SG') { ?>
                                    <span style="float:left;"><input type="checkbox" class="form-check-input assign-assistant" data-item-id="<?php echo $dataArray['item_id']; ?>" data-item-value="<?php echo urldecode($dataArray['item_title']); ?>"></span>
                                <?php } ?>
                                <span <?php if ($dataArray['item_type'] != 'IT') { ?> class="archive-record-listing getItemDataByFolderId " <?php } ?> data_item_type="<?php echo $dataArray['item_type']; ?>" data-folder-id="<?php echo $dataArray['item_id']; ?>">
                                    <?php if (($dataArray['item_type'] == 'RE' || $dataArray['item_type'] == 'IT') && $dataArray['properties']['aib:private'] == 'Y') { ?> <img style="width:16px;" src="<?php echo IMAGE_PATH . 'private-record.png'; ?>" title="Private record" alt="Private Records" /><?php } ?>
                                    <?php echo urldecode($dataArray['item_title']); ?>
                                </span>
                                <?php
                                if (isset($dataArray['properties']['ebay_url']) && $dataArray['properties']['ebay_url'] != '' && $ebayCheckCondition != 'N') {
                                    echo '<br><img src="' . IMAGE_PATH . 'ebay.png" alt="" />';
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
                                    if ($dataArray['is_link'] == 'Y' && $dataArray['link_type'] == 'U') {
                                        $imageSrc = IMAGE_PATH . 'external_url_thumb.jpg';
                                    }
                                }
                                ?>
                                <img class="typeImg" src="<?php echo $imageSrc; ?>" alt="Item type" /><br>
                                <?php
                                if ($dataArray['is_link'] == 'Y' && $dataArray['link_type'] == 'U') {
                                    echo 'Link';
                                } else {
                                    echo $displayNameArray[$dataArray['item_type']];
                                }
                                ?><br>
                                <?php
                                echo ($count_display != '') ? $dataArray['child_count'] . ' ' . $count_display : '';
                                if (isset($dataArray['sg_count']) && $dataArray['sg_count'] > 0) {
                                    echo '<br>' . $dataArray['sg_count'] . ' Sub-Group(s)';
                                }
                                ?>
                            </td>
                            <td align="center"><?php echo date('m/d/Y h:i:s A', $dataArray['item_create_stamp']); ?></td>
                            <td align="center"><?php echo $unclaimed_society; ?></td>
                            <td>
                                <?php if($dataArray['item_type'] != 'IT'){ 
								 $downLoadWM=0;
									  if(in_array($treeDataArray[1]['item_id'],AIB_DOWNLOAD_ENABLE_RECORD_LEVEL) or in_array($treeDataArray[0]['item_id'],AIB_DOWNLOAD_ENABLE_RECORD_LEVEL)){
										  $downLoadWM=1;
									  }
								?>
                                    <span class="Watermark" onclick="setWatermark('<?php echo $dataArray['item_id']; ?>','<?php echo $downLoadWM;?>');">
                                        <img title="Watermark" src="<?php echo IMAGE_PATH . 'watermark-icon.png'; ?>" alt="Watermark" />
                                    </span>
                                    <?php if ($_SESSION['aib']['user_data']['user_type'] == 'A') { ?>
                                        <span class="Advertisement" onclick="advertisement('<?php echo $dataArray['item_id']; ?>');">
                                            <img title="Advertisement" src="<?php echo IMAGE_PATH . 'ad-icon.png'; ?>" alt="Advertisement" />
                                        </span>
                                    <?php } ?>
                                <?php } ?>
                                <?php if ($dataArray['item_type'] == 'AG' && $_SESSION['aib']['user_data']['user_type'] == 'R') { ?>
                                    <span class="Advertisement" onclick="googleAd('<?php echo $dataArray['item_id']; ?>');">
                                        <img title="Google Advertisement" src="<?php echo IMAGE_PATH . 'google_ad.png'; ?>" alt="Google Advertisement" />
                                    </span>
                                <?php } ?>
                                <?php if ($dataArray['item_type'] == 'SG' || $dataArray['item_type'] == 'RE' || $dataArray['item_type'] == 'IT') { ?>
                                    <span id="ocr_span_<?php echo $dataArray['item_id']; ?>">
                                        <?php if ($_SESSION['aib']['user_data']['user_type'] == 'R') { ?>
                                            <span data-ocr-folder-id="<?php echo $dataArray['item_id']; ?>" class="reset-ocr-onfolder"><img title="Reset OCR" src="<?php echo IMAGE_PATH . 'Reset_OCR.png'; ?>" alt="" /></span>
                                            <?php
                                        }
                                        if ($dataArray['properties']['ocr_flag'] == '1') {
                                            ?>
                                            <span data-ocr-folder-id="<?php echo $dataArray['item_id']; ?>"><img title="OCRED" src="<?php echo IMAGE_PATH . 'OCRed.png'; ?>" alt="" /></span>
                                        <?php } else { ?>
                                            <span data-ocr-folder-id="<?php echo $dataArray['item_id']; ?>" class="run-ocr-onfolder"><img title="Run OCR" src="<?php echo IMAGE_PATH . 'Run_OCR.png'; ?>" alt="" /></span>
                                        <?php } ?>
                                    </span>
                                <?php } ?>
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
                                    $deleteConfirmItemType =' Archive Group';
                                    ?>
                                    <span class="manage-archive-group"><a target="_blank" href="../society.html?folder-id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive Data" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                    <span class="manage-archive-group"><a href="manage_archive_group_data.php?archive_id=<?php echo $dataArray['item_id']; ?>"><img title="Manage archive group" src="<?php echo IMAGE_PATH . 'edit_icon.png'; ?>" alt="" /></a></span>
                                    <span  data-field-delete-id="<?php echo $dataArray['item_id']; ?>" data-field-delete-msg="<?php echo urldecode($dataArray['item_title']); ?>" data-field-delete-msg-type='<?php echo $deleteConfirmItemType; ?>' class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span> 
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
                                    <?php } ?>
                                    <?php if ($dataArray['item_type'] == 'IT' && $_SESSION['aib']['user_data']['user_type'] != 'U') { ?>
                                        <span data-ocr-folder-id="<?php echo $dataArray['item_id']; ?>" class="perform-ocr-onfolder"><img title="OCR" src="<?php echo IMAGE_PATH . 'ocr.png'; ?>" alt="" /></span><?php } ?>
                                    <?php //if ($dataArray['item_type'] == 'IT' || $dataArray['item_type'] == 'RE' || $dataArray['item_type'] == 'SG') { ?>
                                    
                                    <?php $deleteConfirmMsgSub=urldecode($dataArray['item_title']); 
									$deleteConfirmItemType='';
									 if ($dataArray['item_type'] == 'AG') {
                                   			$deleteConfirmItemType =' Archive Group';
										} elseif ($dataArray['item_type'] == 'AR') {
											$deleteConfirmItemType =' Archive Group';
										} elseif ($dataArray['item_type'] == 'CO') {
											$deleteConfirmItemType =' Collection';
										} elseif ($dataArray['item_type'] == 'SG') {
											$deleteConfirmItemType =' Sub Group';
										} elseif ($dataArray['item_type'] == 'RE') {
											$deleteConfirmItemType =' Record';
										} elseif ($dataArray['item_type'] == 'IT') {
											$deleteConfirmItemType =' Item';
										}
									
									?>
                                        <span  data-field-delete-id="<?php echo $dataArray['item_id']; ?>" data-field-delete-msg="<?php echo $deleteConfirmMsgSub; ?>" data-field-delete-msg-type='<?php echo $deleteConfirmItemType; ?>' class="delete_listing_item"><img title="Delete" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></span> 
                                    <?php //} ?>
                                    <?php if ($dataArray['item_type'] == 'CO') { ?>
                                       <!--<span class="list_move_item" data-field-item-id="<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                    <?php } elseif ($dataArray['item_type'] == 'SG') { ?>
                                       <!--<span class="list_move_item" data-field-item-id="<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                    <?php } ?>
                                    <?php if ($dataArray['item_type'] == 'RE') { ?>
                                        <span data-field-delete-id=""><a target="_blank" href="../item-details.php?folder_id=<?php echo $dataArray['item_id']; ?>&share=1"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                        <?php if ($_SESSION['aib']['user_data']['user_type'] != 'U') { ?>
                                            <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH . 'pdf_icon.gif'; ?>" alt="" /></a></span>
                                        <?php } ?>
                <!-- <span class="list_move_item" data-field-item-id="<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                        <?php if ($_SESSION['aib']['user_data']['user_type'] == 'U') { ?>
                                            <span data-field-record-id="<?php echo $dataArray['item_id']; ?>" class="share_with_users"><img title="Share with user" src="<?php echo IMAGE_PATH . 'share.png'; ?>" alt="" /></span>
                                        <?php } if ($ebayCheckCondition != 'N') { ?>
                                            <span data-field-ebay-record-id="<?php echo $dataArray['item_id']; ?>" class="ebay_link"><img title="Ebay Url" src="<?php echo IMAGE_PATH . 'ebay.png'; ?>" alt="" /></span> 
                                        <?php } ?>
                                        <span data-field-record-id="<?php echo $dataArray['item_id']; ?>" class="set_related_records"><img title="Set related records" src="<?php echo IMAGE_PATH . 'related-item.png'; ?>" alt="" /></span>
                                        <?php if ($_SESSION['aib']['user_data']['user_type'] == 'U') { ?>
                                                                 <!--<span data-field-record-id="<?php // echo $dataArray['item_id'];      ?>" class="set_public_connections"><img title="Set public connections records" src="<?php echo IMAGE_PATH . 'public-connection.png'; ?>" alt="" /></span>-->
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($dataArray['item_type'] == 'IT') { 
								/********* fix start Bug id 2359 23-June-2204 ****************/
										$enc_str_n = 'folder_id='.$folderId.'&itemId='.$dataArray['item_id']."&share=1";
										$enc_url_n = encryptQueryString($enc_str_n);
										$url="../item-details.html?q=".$enc_url_n;
										//$url="../item-details.html?q=".$enc_str_n;
										/********* fix end Bug id 2359 23-June-2204 ****************/
								?>
                                        <span data-field-delete-id=""><a target="_blank" href="<?php echo $url;?>"><img title="View" src="<?php echo IMAGE_PATH . 'view.png'; ?>" alt="" /></a></span>
                                        <?php if ($_SESSION['aib']['user_data']['user_type'] != 'U') { ?>
                                            <span data-field-item-id="<?php echo $dataArray['item_id']; ?>"><a href="download-pdf.php?item_id=<?php echo $dataArray['item_id']; ?>" title="Download PDF" target="_blank"><img title="pdf" src="<?php echo IMAGE_PATH . 'pdf_icon.gif'; ?>" alt="" /></a></span>
                                            <span data-field-delete-id=""><a target="_blank" href="<?php echo THUMB_URL . '?id=' . $dataArray['or_file_id'] . '&download=1'; ?>" download><img title="Image" src="<?php echo IMAGE_PATH . 'download.png'; ?>" alt="" /></a></span>
                                        <?php } ?>
                    <!--<span class="list_move_item" data-field-item-id=<?php echo $dataArray['item_id'] . '/' . $dataArray['item_type']; ?>"><img title="Move Item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>-->
                                    <?php } ?>
                                    <?php
                                }
                                if ($_SESSION['aib']['user_data']['user_type'] == 'R' && $dataArray['item_type'] != 'AG' && $dataArray['item_type'] != 'RE' && $dataArray['item_type'] != 'IT') {
                                    $image = 'active.png';
                                    $current_status = 1;
                                    if (isset($dataArray['properties']['visible_to_public']) && $dataArray['properties']['visible_to_public'] == 0) {
                                        $image = 'deactive.png';
                                        $current_status = 0;
                                    }
                                    ?>
                                    <span data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-staus="<?php echo $current_status; ?>"  class="superadmin_change_status"><img title="Change status" src="<?php echo IMAGE_PATH . $image; ?>" alt="" /></span>
                                <?php } ?>
                                <?php if ($moveItem == 'yes') { ?>
                                    <span class="move_item" item-id="<?php echo $dataArray['item_id']; ?>" item-type="<?php echo $dataArray['item_type']; ?>"><img title="Move item " src="<?php echo IMAGE_PATH . 'move.png'; ?>" alt="" /></span>
                                <?php } ?>
                                <?php
                                if ($_SESSION['aib']['user_data']['user_type'] == 'R' && $showPublish == 'yes') {
                                    $publishStatus = 'N';
                                    $gray_scale = '';
                                    $publishText = 'Hide';
                                    if (isset($dataArray['properties']['publish_status']) && $dataArray['properties']['publish_status'] == 'N') {
                                        $publishStatus = 'Y';
                                        $gray_scale = 'gray_scale';
                                        $publishText = 'Show';
                                    }
                                    ?>
                                    <span  data-field-item-id="<?php echo $dataArray['item_id']; ?>" current-publish-staus="<?php echo $publishStatus; ?>"  class="change_publish_status <?php echo $gray_scale; ?>"><img title="<?php echo $publishText; ?>" src="<?php echo IMAGE_PATH . 'publish.png'; ?>" alt="" /></span>
                                <?php } ?>
								<?php if ($_SESSION['aib']['user_data']['user_type'] == 'R') { ?>
									<span class="update_owner_account">
										<a href="manage_archive_registration.php?archive_id=<?php echo $dataArray['item_id']; ?>">
											<img title="update_owner_account" src="<?php echo IMAGE_PATH . 'update_owner_account.png'; ?>" alt="Update Owner Account" />
										</a>
									</span>
								<?php } ?>
								
								<!--  Change Start 12-Sep-2022 Bug ID 2000 -->
                               <!-- 

 Start Fixed Issue ID 2000 12-Sep-2022  
Change  if ($_SESSION['aib']['user_data']['user_type'] == 'R')  to $dataArray['item_type'] == 'AG' because these icons should be display on Archive Groups(12-09-22)-->
                                    <?php if ($dataArray['item_type'] == 'AG' && $_SESSION['aib']['user_data']['user_type'] == 'R') { ?>
							<!-- End Fixed Issue ID 2000 12-Sep-2022  -->	
                                    <!-- Fixed Start Issue ID 2465 02-Jul-2025  -->
                                    <!-- <span class="Advertisement" onclick="resendVerificationLink('<?php echo $dataArray['properties']['archive_user_id']; ?>');">
                                        <img title="Resend Validation Link" src="<?php echo IMAGE_PATH . 'resend-link.png'; ?>" alt="Resend Validation Link" />
                                    </span> -->
                                    <!-- Fixed end Issue ID 2465 02-Jul-2025  -->
                                    <span class="Advertisement" onclick="fullValidateAccount('<?php echo $dataArray['item_id']; ?>');">
                                    <!-- Start Fixed Issue ID 2000 12-Sep-2022  -->
                                        <img title="Validate Account" src="<?php echo IMAGE_PATH . 'validate-user.png'; ?>" alt="Validate Account" />
									 <!-- End Fixed Issue ID 2000 12-Sep-2022  -->	
                                    </span>
                                <?php } ?>
								<!-- Start Fixed Issue ID 2138 02-Jan-2023  -->
								<?php $disableStyle='';$enableStyle=''; 
						
						if(!isset($dataArray['properties']['enable_disable_download']) ){$enable_disable_download=1;$enableStyle="display:none";}
						elseif(isset($dataArray['properties']['enable_disable_download']) and $dataArray['properties']['enable_disable_download']==1){$enable_disable_download=2;$disableStyle="display:none";}
								else{$enable_disable_download=1;$enableStyle="display:none";}?>
								<?php  if((in_array($treeDataArray[1]['item_id'],AIB_DOWNLOAD_ENABLE_RECORD_LEVEL) or in_array($treeDataArray[0]['item_id'],AIB_DOWNLOAD_ENABLE_RECORD_LEVEL)) && ($dataArray['item_type'] == 'RE' || $dataArray['item_type'] == 'IT')){
									?>
									 <span id="disableButtonSpan<?php echo $dataArray['item_id']; ?>"  style="<?php echo $disableStyle; ?>"  class="Advertisement" onclick="enableDisbaleDownload('<?php echo $dataArray['item_id']; ?>',1);">
									 <img title="Disable Download for Public User" src="<?php echo IMAGE_PATH . 'download_active.png'; ?>" alt="Disable Download for Public User" />	
									</span>	 
									 <span id="enableButtonSpan<?php echo $dataArray['item_id']; ?>"   style="<?php echo $enableStyle; ?>" class="Advertisement" onclick="enableDisbaleDownload('<?php echo $dataArray['item_id']; ?>',2);">
									<img title="Enable Download for Public User" src="<?php echo IMAGE_PATH . 'download_inactive.png'; ?>" alt="Enable Download for Public User" />	 
                                   </span>
                                        
									
                                    
								<?php } ?>
								<!-- Start Fixed Issue ID 2378 05-Mar-2024  -->	
								
								
								 <?php //echo '<pre>	';print_R($dataArray);die;
						if ($dataArray['item_type'] == 'RE' and isset($dataArray['properties']['itemrecord_lat']) and $dataArray['properties']['itemrecord_lat']!='') { ?>
						
                                    <!--<span class="Advertisement" id="remove_location<?php echo $dataArray['item_id']; ?>" onclick="remove_location('<?php echo $dataArray['item_id']; ?>');">-->
                                  <span class="" id="remove_location<?php echo $dataArray['item_id']; ?>" >
                                        <img title="Remove location information" src="<?php echo IMAGE_PATH . 'remove_location.png'; ?>" alt="Remove location information" />
									
                                    </span>
                                <?php }  ?>
								<!-- End Fixed Issue ID 2378 05-Mar-2024  -->	
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
<span id="current_folder" current-folder-id="<?php echo $currentfolder_id['item_id']; ?>"> </span>



<script type="text/javascript">
    $(document).ready(function () {
		$("html, body").animate({ scrollTop: 0 }, 600);
        var user_type = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        if (user_type == 'U') {
            $('.list_move_item').hide();
        }
        $('#manage_records_table_data').DataTable({
            "pageLength": 100,
            "sDom": '<"H"lfrp>t<"F"ip>',
            columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }]
        });
    });



</script>