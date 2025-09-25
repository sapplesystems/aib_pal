<link rel="stylesheet" href="<?php echo CSS_PATH.'thumbnail-slider.css'; ?>">
<script src="<?php echo JS_PATH .'thumbnail-slider.js'; ?>"></script>
<style>
    #thumbnail-slider-prev {left:0 !important; right:auto;}
    #thumbnail-slider-next {left:auto; right:0 !important;}
</style>
<div class="modal-header form_header">
    <h4 class="list_title text-center" id="popup_heading"><span class="pull-left">Edit Record / Item </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span><button type="button" class="close" data-dismiss="modal">&times;</button></h4>
</div>
<div class="modal-body">
    <form id="edit_items_completed_form" name="edit_items_completed_form" method="post" class="form-horizontal">
        <input type="hidden" name="item_id" id="item_id" value="<?php echo $item_id; ?>">
        <input type="hidden" name="item_type" id="item_type" value="<?php echo $item_type; ?>">
        <input type="hidden" name="sub_group_id" id="sub_group_id" value="<?php echo $treeDataArray[count($treeDataArray) - 2]['item_id']; ?>">
        <div id="edit-item-section">
            <?php if ($item_type == 'RE') { ?>
                <div class="form-group">
                    <label class="col-md-6 control-label">Sub Group: </label>
                    <div class="col-md-6 item-display"><?php echo $treeDataArray[count($treeDataArray) - 2]['item_title']; ?></div>
                </div>
                <div class="form-group">
                    <label class="col-md-6 control-label">Record : </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['item_title']; ?></div>
                </div>
            <?php } if ($item_type == 'IT') { ?>
                <div class="form-group">
                    <label class="col-md-6 control-label">Sub Group : </label>
                    <div class="col-md-6 item-display"><?php echo $treeDataArray[count($treeDataArray) - 2]['item_title']; ?></div>
                </div>
                <div class="form-group">
                    <label class="col-md-6 control-label">Record : </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['parent_details']['item_title']; ?></div>
                </div>
                <div class="form-group">
                    <label class="col-md-6 control-label">Item : </label>
                    <div class="col-md-6 item-display"><?php echo $itemDetails['item_title']; ?></div>
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
                <?php if ($display_image_pr_id != '') { ?>
                    <img class="edit-record-image" src="<?php echo THUMB_URL . '?id=' . $display_image_pr_id; ?>">
                <?php } else { ?>
                    <img class="edit-record-image" src="<?php echo IMAGE_PATH . 'no-image.png'; ?>">
                <?php } ?>
            </label>
            <div class="col-xs-5 overflowSection">
                <label class="col-xs-12 control-label text-left">Title: </label>
                <label class="col-xs-12 control-label topPaddMarginNone"><input type="text" class="form-control" name="record_title" id="record_title" value="<?php echo $itemDetails['item_title']; ?>" /></label>
                <?php
                if (count($itemDetails['fields']) > 0) {
                    foreach ($itemDetails['fields'] as $fieldKey => $fieldDataValue) {
                        ?>
                        <input type="hidden" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][id]" value='<?php echo $fieldDataValue['field_id'] ?>'>
                        <input type="hidden" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_title]" value='<?php echo $fieldDataValue['field_title'] ?>'>
                        <label class="col-xs-12 control-label text-left"><?php echo $fieldDataValue['field_title']; ?>: </label>
                        <label class="col-xs-12 control-label topPaddMarginNone">
						<?php if($fieldDataValue['field_title'] == 'Description' || $fieldDataValue['field_title'] == 'OCR Text'){?>
                            <textarea rows="5" class="form-control" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_value]" id="record_title"><?php echo urldecode($fieldDataValue['field_value']); ?></textarea>
						<?php }else{ ?>
						 <input type="text" class="form-control" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_value]" id="record_title" value="<?php echo $fieldDataValue['field_value']; ?>" />
						<?php } ?>
                        </label>
                        <div class="clearfix"></div>
                    <?php
                    }
                }
                ?>
				<?php if(isset($itemDetails['tags'])){ ?>
                        <label class="col-xs-12 control-label text-left">Tags: </label>
                        <label class="col-xs-12 control-label topPaddMarginNone">
                            <textarea rows="5" class="form-control" name="record_tags" id="record_tags"><?php echo $itemDetails['tags']; ?></textarea>
                        </label>
                <?php } ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-4 control-label"></label>
            <div class="col-xs-7">
                <button type="button" class="btn btn-info borderRadiusNone" name="update_form_data" id="update_completed_form_data">Update</button>
                <button type="button" class="btn btn-danger borderRadiusNone assistant_dashboard" >Back</button>
            </div>
        </div>
    </form>
</div>