<?php 
    if($item_type == 'AG')
        $headerTitle = 'Edit Archive Group';
    elseif($item_type == 'AR')
        $headerTitle = 'Edit Archive';
    elseif($item_type == 'CO')
        $headerTitle = 'Edit Collection';
    elseif($item_type == 'SG')
        $headerTitle = 'Edit Sub Group';
    else 
        $headerTitle = 'Edit Item';
?>
<div class="modal-header form_header">
    <h4 class="list_title text-center" id="popup_heading"><span class="pull-left"><?php echo $headerTitle; ?></span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
</div>
<?php  
if ($item_type == 'RE') {
    $countRecordPos = 1;
    $flag = 0;
    foreach ($_SESSION['aib']['uncomplete_data'][$parent_id] as $k => $v) {
        if ($k != $item_id and $flag == 0) {
            $countRecordPos++;
        } else {
            $flag = 1;
        }
    }
    $_SESSION['aib']['countRecordPos'] = $countRecordPos;
    $_SESSION['aib']['recordParent'] = $item_id;
    $_SESSION['aib']['countRecordChildPos'] = 0;
    $_SESSION['aib']['countItemtotal'] = count($_SESSION['aib']['uncomplete_data'][$parent_id][$item_id]['item_records']);
    $_SESSION['aib']['countRecordtotal'] = count($_SESSION['aib']['uncomplete_data'][$parent_id]);
    $recordDis = $_SESSION['aib']['countRecordPos'] . ' of ' . $_SESSION['aib']['countRecordtotal'];
} else if ($item_type == 'IT') {
    $_SESSION['aib']['countRecordChildPos'] = $_SESSION['aib']['countRecordChildPos'] + 1;
    $recordDis = $_SESSION['aib']['countRecordPos'] . ' of ' . $_SESSION['aib']['countRecordtotal'];
    $itemDis = $_SESSION['aib']['countRecordChildPos'] . ' of ' . $_SESSION['aib']['countItemtotal'];
}
?>
<div class="modal-body">
    <form id="edit_items_form" name="edit_items_form" method="post" class="form-horizontal">
        <input type="hidden" name="item_id" id="item_id" value="<?php echo $item_id; ?>">
        <input type="hidden" name="item_type" id="item_type" value="<?php echo $item_type; ?>">
        <?php if($item_type == 'AG'){ ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Code</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_code" id="item_code" value="<?php if(isset($itemDetails['prop_details']['code'])){ echo $itemDetails['prop_details']['code']; } ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Title</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_title" id="item_title" value="<?php echo $itemDetails['item_title']; ?>" />
                </div>
            </div>
        <?php } ?>
        <?php if($item_type == 'AR'){ ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Code</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_code" id="item_code" value="<?php if(isset($itemDetails['prop_details']['code'])){ echo $itemDetails['prop_details']['code']; } ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Title</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_title" id="item_title" value="<?php echo $itemDetails['item_title']; ?>" />
                </div>
            </div>
            <div class="form-group" id="archive_advertisements">
                <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="display_archive_level_advertisements" id="display_archive_level_advertisements">
                            <option <?php if(isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements']== 0){ echo "selected"; } ?> value="0">No</option>
                            <option <?php if(isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements']== 1){ echo "selected"; } ?> value="1">Yes</option>
                        </select>
                    </span>
                </div>
            </div>
        <?php } ?>
        <?php if($item_type == 'CO'){ ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Collection Name</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="collection_name" id="collection_name" value="<?php echo $itemDetails['item_title']; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Visible To Public</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="visible_to_public" id="visible_to_public">
                            <option <?php if(isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public']== 1){ echo "selected"; } ?> value="1">Yes</option>
                            <option <?php if(isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public']== 0){ echo "selected"; } ?> value="0">No</option>
                        </select>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="display_archive_level_advertisements" id="display_archive_level_advertisements">
                            <option <?php if(isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements']== 0){ echo "selected"; } ?> value="0">No</option>
                            <option <?php if(isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements']== 1){ echo "selected"; } ?> value="1">Yes</option>
                        </select>
                    </span>
                </div>
            </div>
        <?php } ?>
        <?php if($item_type == 'SG'){ ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Sub Group Name</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="sub_group_name" id="sub_group_name" value="<?php echo $itemDetails['item_title']; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Visible To Public</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="visible_to_public" id="visible_to_public">
                            <option <?php if(isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public']== 1){ echo "selected"; } ?> value="1">Yes</option>
                            <option <?php if(isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public']== 0){ echo "selected"; } ?> value="0">No</option>
                        </select>
                    </span>
                </div>
            </div>
            <?php if($_SESSION['aib']['user_data']['user_type'] != 'U'){ ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="display_archive_level_advertisements" id="display_archive_level_advertisements">
                            <option <?php if(isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements']== 0){ echo "selected"; } ?> value="0">No</option>
                            <option <?php if(isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements']== 1){ echo "selected"; } ?> value="1">Yes</option>
                        </select>
                    </span>
                </div>
            </div>
            <?php } ?>
        <?php } ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">Default Sorting By</label>
            <div class="col-xs-7">
                <span class="custom-dropdown">
                    <select class="form-control" name="default_sorting_by" id="default_sorting_by">
                        <option <?php if(isset($itemDetails['prop_details']['sort_by']) && $itemDetails['prop_details']['sort_by']== 'ID'){ echo "selected"; } ?> value="ID">Created date</option>
                        <option <?php if(isset($itemDetails['prop_details']['sort_by']) && $itemDetails['prop_details']['sort_by']== 'TITLE'){ echo "selected"; } ?> value="TITLE">Alphabet</option>
                    </select>
                </span>
            </div>
        </div>
        <?php if($item_type == 'RE' || $item_type == 'IT' ){ ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH.'thumbnail-slider.css'; ?>">
            <script src="<?php echo JS_PATH .'thumbnail-slider.js'; ?>"></script>
            <style>
                #thumbnail-slider-prev {left:0 !important; right:auto;}
                #thumbnail-slider-next {left:auto; right:0 !important;}
            </style>
            <input type="hidden" name="parent_id" id="parent_id" value="<?php echo ($parent_id!='') ? $parent_id : ''; ?>">
            <div id="edit-item-section">
            <?php if($item_type == 'RE'){ ?>
                <div class="form-group">
                    <label class="col-md-6 control-label">Sub Group: </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['parent_details']['item_title']?></div>
                </div>
                <div class="form-group">
                    <label class="col-md-6 control-label">Record <?php echo'('.$recordDis.')'?>: </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['item_title'] ; ?></div>
                </div>
            <?php } if($item_type == 'IT'){ ?>
                <div class="form-group">
                    <label class="col-md-6 control-label">Sub Group: </label>
                    <div class="col-md-6 item-display"><?php echo $treeDataArray[count($treeDataArray)-2]['item_title']; ?></div>
                </div>
                <div class="form-group">
                    <label class="col-md-6 control-label">Record <?php echo'('.$recordDis.')'?>: </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['parent_details']['item_title'];  ?></div>
                </div>
                <div class="form-group">
                    <label class="col-md-6 control-label">Item In Record <?php echo'('.$itemDis.')'?>: </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['item_title'] ; ?></div>
                </div>
            <?php } ?>
            </div>
            <div class="form-group">
                <div id="thumbnail-slider">
                    <div class="inner">
                        <ul>
                            <?php
                            if(isset($itemDetails['files_records']) && count($itemDetails['files_records']) > 0){
                            foreach($itemDetails['files_records'] as $imageList){ ?>
                                <li><a class="thumb" href="<?php echo THUMB_URL . '?id=' . $imageList['tn_file_id']; ?>"></a></li>
                            <?php } } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-6 control-label">
                    <?php if($display_image_pr_id!=''){ ?>
                    <img class="edit-record-image" src="<?php echo THUMB_URL . '?id=' . $display_image_pr_id; ?>">
                    <?php }else{ ?>
                    <img class="edit-record-image" src="<?php echo IMAGE_PATH . 'no-image.png'; ?>">
                    <?php } ?>
                </label>
                <div class="col-xs-5">
                    <label class="col-xs-12 control-label text-left">Title: </label>
                    <label class="col-xs-12 control-label topPaddMarginNone"><input type="text" class="form-control" name="record_title" id="record_title" value="<?php echo $itemDetails['item_title']; ?>" /></label>
                    <?php if(count($itemDetails['fields']) > 0){ 
                        foreach($itemDetails['fields'] as $fieldKey=>$fieldDataValue){
                        ?>
                            <input type="hidden" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][id]" value='<?php echo $fieldDataValue['field_id'] ?>'>
                            <input type="hidden" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_title]" value='<?php echo $fieldDataValue['field_title'] ?>'>
                            <label class="col-xs-12 control-label text-left"><?php echo $fieldDataValue['field_title']; ?>: </label>
                            <label class="col-xs-12 control-label topPaddMarginNone">
							<?php if($fieldDataValue['field_title'] == 'Description' || $fieldDataValue['field_title'] == 'OCR Text'){?>
                                <textarea rows="5" class="form-control" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_value]" id="record_title"><?php echo urldecode($fieldDataValue['field_value']); ?></textarea>
							<?php } else{ ?>
                                <input type="text" class="form-control" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_value]" id="record_title" value="<?php echo $fieldDataValue['field_value']; ?>" />
							<?php }?>
                            </label>
                            <div class="clearfix"></div>
                        <?php } 
                        } ?>
                    <?php if(isset($itemDetails['tags'])){ ?>
                        <label class="col-xs-12 control-label text-left">Tags: </label>
                        <label class="col-xs-12 control-label topPaddMarginNone">
                            <textarea rows="5" class="form-control" name="record_tags" id="record_tags"><?php echo $itemDetails['tags']; ?></textarea>
                        </label>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <?php ?>
        <div class="form-group">
            <label class="col-xs-4 control-label"></label>
            <div class="col-xs-7">
                <button type="button" class="btn btn-info borderRadiusNone" name="update_form_data" id="update_form_data">Update</button>
                <button type="button" class="btn btn-danger borderRadiusNone assistant_dashboard" data-dismiss="modal">Back</button>
            </div>
        </div>
    </form>
</div>