<style type="text/css">
.display_count,.display_count{width:30px;text-align: center;}
</style>
<?php
if ($item_type == 'AG')
    $headerTitle = 'Edit Archive Group';
elseif ($item_type == 'AR')
    $headerTitle = 'Edit Archive';
elseif ($item_type == 'CO')
    $headerTitle = 'Edit Collection';
elseif ($item_type == 'SG')
    $headerTitle = 'Edit Sub Group';
else
    $headerTitle = 'Edit Item';
?>
<div class="modal-header form_header">
    <h4 class="list_title text-center" id="popup_heading"><span class="pull-left"><?php echo $headerTitle; ?></span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span></h4>
</div>
<?php
$current_records = current($_SESSION['aib']['uncomplete_data']);
$_SESSION['aib']['current_records'] = $current_records;
$current_records_keys = array_keys($current_records);

$record_drop_down = '<span class="custom-dropdown"><select id="record_dis_dd" class="form-control">';
$rdd = 0;
for($i = 0; $i < count($current_records_keys); $i++){
	$rdd++;
	$record_get_param = array(
		'_key' => APIKEY,
		'_user' => 1,
		'_op' => 'get',
		'_session' => $sessionKey,
		'obj_id' => $current_records_keys[$i]
	);
	$record_get_result = aibServiceRequest($record_get_param, 'browse');
	if($record_get_result['status'] == 'OK'){
		$rdd_selected = '';
		if($parent_id == $record_get_result['info']['records'][0]['item_id']){
			$rdd_selected = 'selected';
		}
		$record_drop_down .= '<option value="'.$rdd.'" '.$rdd_selected.'>';
		$record_drop_down .= $record_get_result['info']['records'][0]['item_title'];
		$record_prop_param = array(
			'_key' => APIKEY,
			'_user' => 1,
			'_op' => 'get_item_prop',
			'_session' => $sessionKey,
			'obj_id' => $current_records_keys[$i]
		);
		$record_prop_result = aibServiceRequest($record_prop_param, 'browse');
		if($record_prop_result['status'] == 'OK'){
			if(!empty($record_prop_result['info']['records']['last_updated_on'])){
				$record_drop_down .= ' - Modified At: '.date('m-d-Y H:i',strtotime($record_prop_result['info']['records']['last_updated_on']));
			}
		}
		$record_drop_down .= '</option>';
	}
}
$record_drop_down .= '</select></span>';

$_SESSION['aib']['current_records_keys'] = $current_records_keys;
$current_item_keys = array_column($current_records[$parent_id]['item_records'], 'item_id');
$record_number = array_search($parent_id,$current_records_keys) + 1;
$item_number = array_search($item_id,$current_item_keys) + 1;
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
    $recordDis = '&nbsp;<input class="display_count" type="text" value="'.$_SESSION['aib']['countRecordPos'].'" />' . ' of ' . $_SESSION['aib']['countRecordtotal'];
    $update_message = 'Update in the record';
} else if ($item_type == 'IT') {
    $_SESSION['aib']['countRecordChildPos'] = $_SESSION['aib']['countRecordChildPos'] + 1;
    $recordDis = '&nbsp;<input class="display_count" id="record_dis" type="text" value="'.$record_number.'" />' . ' of ' . $_SESSION['aib']['countRecordtotal'];
    //$recordDis = '<input class="display_count" id="record_dis" type="text" value="'.$_SESSION['aib']['countRecordPos'].'" />' . ' of ' . $_SESSION['aib']['countRecordtotal'];
    //$itemDis = '<input class="display_count" id="item_dis" type="text" value="'.$_SESSION['aib']['countRecordChildPos'].'" />' . ' of ' . $_SESSION['aib']['countItemtotal'];
    $itemDis = '&nbsp;<input class="display_count" id="item_dis" type="text" value="'.$item_number.'" />' . ' of ' . count($current_records[$parent_id]['item_records']);
    $update_message = 'Update in the item';
}

$completed = [];
foreach ($_SESSION['aib']['uncomplete_data'] as $key => $value) {
    foreach ($value as $k => $v) {
        foreach ($v['item_records'] as $x => $y) {
            if ($y['completed'] == 'yes') {
                $completed[] = $y['item_id'];
            }
        }
    }
}
?>
<style>
    #thumbnail-slider-inner ul li.li_updated:before{
        content: "";
        position: absolute;
        top: 3px;
        right: 3px;
        z-index: 1;
        background-image:url('public/images/green_check.png');
        height: 15px;
        width: 15px;
    }
</style>
<div class="modal-body">
    <form id="edit_items_form" name="edit_items_form" method="post" class="form-horizontal">
        <input type="hidden" name="item_id" id="item_id" value="<?php echo $item_id; ?>">
        <input type="hidden" name="item_type" id="item_type" value="<?php echo $item_type; ?>">
        <input type="hidden" name="is_skipped" id="is_skipped" value="<?php echo $is_skipped; ?>">
        <?php if ($item_type == 'AG') { ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Code</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_code" id="item_code" value="<?php
                    if (isset($itemDetails['prop_details']['code'])) {
                        echo $itemDetails['prop_details']['code'];
                    }
                    ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Title</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_title" id="item_title" value="<?php echo $itemDetails['item_title']; ?>" />
                </div>
            </div>
        <?php } ?>
        <?php if ($item_type == 'AR') { ?>
            <div class="form-group">
                <label class="col-xs-4 control-label">Archive Code</label>
                <div class="col-xs-7">
                    <input type="text" class="form-control" name="item_code" id="item_code" value="<?php
                    if (isset($itemDetails['prop_details']['code'])) {
                        echo $itemDetails['prop_details']['code'];
                    }
                    ?>" />
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
                            <option <?php
                            if (isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements'] == 0) {
                                echo "selected";
                            }
                            ?> value="0">No</option>
                            <option <?php
                            if (isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements'] == 1) {
                                echo "selected";
                            }
                            ?> value="1">Yes</option>
                        </select>
                    </span>
                </div>
            </div>
        <?php } ?>
        <?php if ($item_type == 'CO') { ?>
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
                            <option <?php
                            if (isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public'] == 1) {
                                echo "selected";
                            }
                            ?> value="1">Yes</option>
                            <option <?php
                            if (isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public'] == 0) {
                                echo "selected";
                            }
                            ?> value="0">No</option>
                        </select>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="display_archive_level_advertisements" id="display_archive_level_advertisements">
                            <option <?php
                            if (isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements'] == 0) {
                                echo "selected";
                            }
                            ?> value="0">No</option>
                            <option <?php
                            if (isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements'] == 1) {
                                echo "selected";
                            }
                            ?> value="1">Yes</option>
                        </select>
                    </span>
                </div>
            </div>
        <?php } ?>
        <?php if ($item_type == 'SG') { ?>
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
                            <option <?php
                            if (isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public'] == 1) {
                                echo "selected";
                            }
                            ?> value="1">Yes</option>
                            <option <?php
                            if (isset($itemDetails['prop_details']['visible_to_public']) && $itemDetails['prop_details']['visible_to_public'] == 0) {
                                echo "selected";
                            }
                            ?> value="0">No</option>
                        </select>
                    </span>
                </div>
            </div>
		<!----------- Issue Id 2108 Fix start date 12-Dec-2022--------------->
		<div class="form-group">
                <label class="col-xs-4 control-label">Allow Downloads</label>
                <div class="col-xs-7">
                    <span class="custom-dropdown">
                        <select class="form-control" name="download_to_public_item" id="download_to_public_item">
							 <option <?php
                            if (isset($itemDetails['prop_details']['download_to_public_item']) && $itemDetails['prop_details']['download_to_public_item'] == 0) {
                                echo "selected";
                            }
                            ?> value="0">No</option>
                            <option <?php
                            if (isset($itemDetails['prop_details']['download_to_public_item']) && $itemDetails['prop_details']['download_to_public_item'] == 1) {
                                echo "selected";
                            }
                            ?> value="1">Yes</option>
                           
                        </select>
                    </span>
                </div>
            </div>
		<!----------- Issue Id 2108 Fix End date 12-Dec-2022--------------->
            <?php if ($_SESSION['aib']['user_data']['user_type'] != 'U') { ?>
                <div class="form-group">
                    <label class="col-xs-4 control-label">Display Archive Level Advertisements</label>
                    <div class="col-xs-7">
                        <span class="custom-dropdown">
                            <select class="form-control" name="display_archive_level_advertisements" id="display_archive_level_advertisements">
                                <option <?php
                                if (isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements'] == 0) {
                                    echo "selected";
                                }
                                ?> value="0">No</option>
                                <option <?php
                                if (isset($itemDetails['prop_details']['display_archive_level_advertisements']) && $itemDetails['prop_details']['display_archive_level_advertisements'] == 1) {
                                    echo "selected";
                                }
                                ?> value="1">Yes</option>
                            </select>
                        </span>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
        <div class="form-group" style="margin-bottom:0;">
            <label class="col-xs-4 control-label" style="margin-right:0;">Default Sorting By:</label>
            <div class="col-xs-4">
                <span class="custom-dropdown">
                    <select class="form-control" name="default_sorting_by" id="default_sorting_by">
                        <option <?php
                        if (isset($itemDetails['prop_details']['sort_by']) && $itemDetails['prop_details']['sort_by'] == 'ID') {
                            echo "selected";
                        }
                        ?> value="ID">Created date</option>
                        <option <?php
                        if (isset($itemDetails['prop_details']['sort_by']) && $itemDetails['prop_details']['sort_by'] == 'TITLE') {
                            echo "selected";
                        }
                        ?> value="TITLE">Alphabetically</option>
						<!----------- Issue Id 2379 Fix Start date 8-May-2024--------------->
						<option <?php
                        if (isset($itemDetails['prop_details']['sort_by']) && $itemDetails['prop_details']['sort_by'] == 'ASCII') {
                            echo "selected";
                        }
                        ?> value="ASCII">ASCII Value for Titile</option>
						<!----------- Issue Id 2379 Fix End date 8-May-2024--------------->
                    </select>
                </span>
            </div>
        </div>
        <?php if ($item_type == 'RE' || $item_type == 'IT') { ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH . 'thumbnail-slider.css'; ?>">
            <script src="<?php echo JS_PATH . 'thumbnail-slider.js'; ?>"></script>
            <style>
                #thumbnail-slider-prev {left:0 !important; right:auto;}
                #thumbnail-slider-next {left:auto; right:0 !important;}
            </style>
            <input type="hidden" name="parent_id" id="parent_id" value="<?php echo ($parent_id != '') ? $parent_id : ''; ?>">
            <div id="edit-item-section">
                <?php if ($item_type == 'RE') { ?>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Sub Group: </label>
                        <div class="col-md-6 item-display"><?php echo $itemDetails['parent_details']['item_title'] ?></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Record <?php echo'(' . $recordDis . ')' ?>: </label>
                        <div class="col-md-4 item-display"><?php echo $record_drop_down;//$itemDetails['item_title']; ?></div>
                    </div>
                    <div class="form-group">
                        <?php
                        $files_records_count = count($itemDetails['files_records']);
                        $item_out_of = ($files_records_count > 0) ? 1 : 0;
                        ?>
                        <label class="col-md-4 control-label">Item In Record <?php echo '(&nbsp;<input class="display_count" type="text" value="'.$item_out_of.'" /> of ' . $files_records_count . ')'; ?>: </label>
                        <div class="col-md-6 item-display"><?php echo $itemDetails['item_title']; ?></div>
                    </div>
                <?php } if ($item_type == 'IT') { ?>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Sub Group: </label>
                        <div class="col-md-6 item-display"><?php echo $treeDataArray[count($treeDataArray) - 2]['item_title']; ?></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Record <?php echo'(' . $recordDis . ')' ?>: </label>
                        <div class="col-md-4 item-display"><?php echo $record_drop_down;//$itemDetails['parent_details']['item_title']; ?></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Item In Record <?php echo'(' . $itemDis . ')' ?>: </label>
                        <div class="col-md-6 item-display"><?php echo $itemDetails['item_title']; ?></div>
                    </div>
                <?php } ?>
                <div class="form-group">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-info borderRadiusNone" name="update_form_data" id="update_form_data">Submit</button>
                        <button type="button" class="btn btn-danger borderRadiusNone assistant_dashboard" data-dismiss="modal">Back</button>
                    </div>
                </div>
            </div>
            <!--div class="form-group">
                <div class="col-xs-6">
                    <label><?php //echo $update_message; ?></label>
                </div>
            </div-->
            <div class="form-group">
                <div class="col-xs-6">
                    <div id="thumbnail-slider">
                        <div class="inner" id="thumbnail-slider-inner">
							<?php
							$transform = '';
							if(!empty($item_number) && $item_number > 3){
								$tx = (-100 * $item_number);
								$transform = 'style = "transform: translateX('.$tx.'px)"';
							}
							?>
                            <ul <?php echo $transform; ?>>
                                <?php
                                if (isset($itemDetails['files_records']) && count($itemDetails['files_records']) > 0) {
                                    foreach ($itemDetails['files_records'] as $imageList) {
                                        $modified = '';
                                        if (in_array($imageList['item_id'], $completed)) {
                                            $modified = 'li_updated';
                                        }
                                        ?>
                                        <li id="slider_<?php echo $imageList['item_id']; ?>" class="tslider <?php echo $modified; ?>" 
                                            onclick="checkGetEditData('<?php echo $imageList['item_parent']; ?>', '<?php echo $imageList['item_id']; ?>', '1');">
                                            <a class="thumb" href="<?php echo THUMB_URL . '?id=' . $imageList['tn_file_id']; ?>"></a>
                                        </li>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <li><a class="thumb" href="<?php echo IMAGE_PATH . 'no-image.png'; ?>"></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div style="margin-top:20px;">
                        <?php if ($display_image_pr_id != '') { ?>
                            <img class="edit-record-image" src="<?php echo THUMB_URL . '?id=' . $display_image_pr_id; ?>">
                        <?php } else { ?>
                            <img class="edit-record-image" src="<?php echo IMAGE_PATH . 'no-image.png'; ?>">
                        <?php } ?>
                    </div>
                </div>

                <div class="col-xs-5">
					<?php if(!empty($item_property['last_updated_on'])){ ?>
						<label class="col-xs-12 control-label text-left">Last Updated On: <?php echo date('m-d-Y H:i', strtotime($item_property['last_updated_on'])); ?></label>
					<?php } ?>
                    <label class="col-xs-12 control-label text-left">Title: </label>
                    <label class="col-xs-12 control-label topPaddMarginNone">
                        <textarea rows="5" class="form-control" name="record_title" id="record_title"><?php echo $itemDetails['item_title']; ?></textarea>
                    </label>
                    <?php
                    if (count($itemDetails['fields']) > 0) {
                        foreach ($itemDetails['fields'] as $fieldKey => $fieldDataValue) {
                            ?>
                            <input type="hidden" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][id]" value='<?php echo $fieldDataValue['field_id'] ?>'>
                            <input type="hidden" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_title]" value='<?php echo $fieldDataValue['field_title'] ?>'>
                            <label class="col-xs-12 control-label text-left"><?php echo $fieldDataValue['field_title']; ?>: </label>
                            <label class="col-xs-12 control-label topPaddMarginNone">
                                <?php //if($fieldDataValue['field_title'] == 'Description' || $fieldDataValue['field_title'] == 'OCR Text'){   ?>
                                <textarea rows="5" class="form-control" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_value]" id="record_title"><?php echo urldecode($fieldDataValue['field_value']); ?></textarea>
                                <?php /* } else{ ?>
                                  <input type="text" class="form-control" name="form_fields[field_<?php echo $fieldDataValue['field_id'] ?>][field_value]" id="record_title" value="<?php echo $fieldDataValue['field_value']; ?>" />
                                  <?php } */ ?>
                            </label>
                            <div class="clearfix"></div>
                            <?php
                        }
                    }
                    ?>
                    <?php if (isset($itemDetails['tags'])) { ?>
                        <label class="col-xs-12 control-label text-left">Tags: </label>
                        <label class="col-xs-12 control-label topPaddMarginNone">
                            <textarea rows="5" class="form-control" name="record_tags" id="record_tags"><?php echo $itemDetails['tags']; ?></textarea>
                        </label>
                    <?php } ?>
					<!------- SS Fix Start for Issue ID 2333 on 17-Jan-2024 ---->
					<?php
					$itemrecord_lat='';
					if(trim($itemDetails['parent_details']['properties']['itemrecord_lat'])!=''){
						$itemrecord_lat=$itemDetails['parent_details']['properties']['itemrecord_lat'];
					}
					$itemrecord_lng='';
					if(trim($itemDetails['parent_details']['properties']['itemrecord_lng'])!=''){
						$itemrecord_lng=$itemDetails['parent_details']['properties']['itemrecord_lng'];
					}
					$itemrecord_address_city='';
					if(trim($itemDetails['parent_details']['properties']['itemrecord_address_city'])!=''){
						$itemrecord_address_city=$itemDetails['parent_details']['properties']['itemrecord_address_city'];
					}
					$itemrecord_address_line='';
					if(trim($itemDetails['parent_details']['properties']['itemrecord_address_line'])!=''){
						$itemrecord_address_line=$itemDetails['parent_details']['properties']['itemrecord_address_line'];
					}										 
					$itemrecord_address_pin_code='';
					if(trim($itemDetails['parent_details']['properties']['itemrecord_address_pin_code'])!=''){
						$itemrecord_address_pin_code=$itemDetails['parent_details']['properties']['itemrecord_address_pin_code'];
					}
					$itemrecord_address_state='';
					if(trim($itemDetails['parent_details']['properties']['itemrecord_address_state'])!=''){
						$itemrecord_address_state=$itemDetails['parent_details']['properties']['itemrecord_address_state'];
					}										 
															 
					?>
					
					<div class="col-xs-12" id="locationDiv" style="display: none;">
					<label>Location Information:</label>
						<table width="100%" cellpadding="2" cellspacing="2" border="0">
						<tr>
							<td style="width: 100px;"><strong>Address</strong>:</td>
							
							<td><input style="width: 300px;height: 40px;" type="text" name="itemrecord_address_line" id="itemrecord_address_line"  value="<?php echo $itemrecord_address_line;?>"></td>
						</tr>
						<tr>
							<td><strong>City</strong>:</td>
							
							<td><input style="width: 300px;height: 30px;" type="text" name="itemrecord_address_city" id="itemrecord_address_city"  value="<?php echo $itemrecord_address_city;?>"></td>
						</tr>
						<tr>
							<td><strong>State</strong>:</td>
							
							<td>
							<select style="width: 300px;height: 30px;" name="itemrecord_address_state" class="aib-dropdown" id="itemrecord_address_state">
								<option value="">---Select---</option>
								</select>
							</td>
						</tr>
						<tr>
							<td><strong>Zip Code</strong>:</td>
							
							<td><input style="width: 300px;height: 30px;" type="text" name="itemrecord_address_pin_code" id="itemrecord_address_pin_code"  value="<?php echo $itemrecord_address_pin_code;?>"></td>
						</tr>
						<tr>
							<td colspan="2">
								<div style="  padding-top: 5px;    padding-bottom: 25px;float: left;width:400px;">Lat:<input style="width:100px;background-color: #ccc;" type="text" readonly="" name="itemrecord_lat" id="itemrecord_lat" value="<?php echo $itemrecord_lat;?>">&nbsp;&nbsp;&nbsp;&nbsp;Long:<input style="width:100px;background-color: #ccc;" type="text" readonly="" name="itemrecord_lng" id="itemrecord_lng" value="<?php echo $itemrecord_lng;?>"></div>
								<div style="float: right;width:200px;border: 1px solid;   padding: 5px;    width: 150px;    background: #15345a;    color: #fff;    margin-left: 15px;     " id="getLocation" onclick="getLocationData()">Update Lat/Long</div></td>
							</tr>	
						</table>
					</div>
                    <div class="col-xs-12 text-center">
                        <button type="button" class="btn btn-info borderRadiusNone" name="update_form_data" id="update_form_data">Submit</button>
                        <button type="button" class="btn btn-danger borderRadiusNone assistant_dashboard" data-dismiss="modal">Back</button>
                    </div>
                </div>
				<!------- SS Fix End for Issue ID 2333 on 17-Jan-2024 ---->
            </div>
        <?php }else{ ?>
        <?php ?>
        <div class="form-group">
            <label class="col-xs-4 control-label"></label>
            <div class="col-xs-7">
                <button type="button" class="btn btn-info borderRadiusNone" name="update_form_data" id="update_form_data">Submit</button>
                <button type="button" class="btn btn-danger borderRadiusNone assistant_dashboard" data-dismiss="modal">Back</button>
            </div>
        </div>
		<?php } ?>
    </form>
</div>
<?php if(!empty($countRecordPos)){ ?>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#update_form_data').click();
		});
	</script>
<?php } ?>
<script type="text/javascript">
var current_records = '<?php echo addslashes(json_encode($current_records)); ?>';
var current_records_keys = '<?php echo addslashes(json_encode($current_records_keys)); ?>';
var current_records_obj = JSON.parse(current_records);
var current_records_keys_obj = JSON.parse(current_records_keys);

$(document).ready(function(){	
	setTimeout(function(){
		$('.tslider').removeClass('active');
		$('#slider_<?php echo $item_id; ?>').addClass('active');
		var ic = $('#item_dis').val();
		if(ic > 3){
			var tx = (-100 * ic);
			//$('#thumbnail-slider-inner ul').css('transform','translateX('+tx+'px)');
			$('#thumbnail-slider-prev').removeClass('disabled');
		}
	},500);
	
	$('#record_dis').blur(function(){
		var record_dis = $('#record_dis').val();
		var item_dis = $('#item_dis').val();
		if(!record_dis || record_dis == ''){
			record_dis = 1;
		}
		if(!item_dis || item_dis == ''){
			item_dis = 1;
		}
		item_dis = 1;
		record_dis = record_dis - 1;
		item_dis = item_dis - 1;
		var record_id = current_records_keys_obj[record_dis];
		var item_id = current_records_obj[record_id]['item_records'][item_dis]['item_id'];
		if(record_id && item_id && record_id != '' && item_id != ''){
			checkGetEditData(record_id, item_id, '1');
			setTimeout(function(){
				$('#record_dis').val(record_dis + 1);
				$('#is_skipped').val(1);
			},1000);
		}
	});
	$('#record_dis_dd').change(function(){
		var record_dis = $('#record_dis_dd').val();
		var item_dis = $('#item_dis').val();
		if(!record_dis || record_dis == ''){
			record_dis = 1;
		}
		if(!item_dis || item_dis == ''){
			item_dis = 1;
		}
		item_dis = 1;
		record_dis = record_dis - 1;
		item_dis = item_dis - 1;
		var record_id = current_records_keys_obj[record_dis];
		var item_id = current_records_obj[record_id]['item_records'][item_dis]['item_id'];
		if(record_id && item_id && record_id != '' && item_id != ''){
			checkGetEditData(record_id, item_id, '1');
			setTimeout(function(){
				$('#record_dis').val(record_dis + 1);
				$('#is_skipped').val(1);
			},1000);
		}
	});
	$('#item_dis').blur(function(){
		var record_dis = $('#record_dis').val();
		var item_dis = $('#item_dis').val();
		if(!record_dis || record_dis == ''){
			record_dis = 1;
		}
		if(!item_dis || item_dis == ''){
			item_dis = 1;
		}
		record_dis = record_dis - 1;
		item_dis = item_dis - 1;
		var record_id = current_records_keys_obj[record_dis];
		var item_id = current_records_obj[record_id]['item_records'][item_dis]['item_id'];
		if(record_id && item_id && record_id != '' && item_id != ''){
			checkGetEditData(record_id, item_id, '1');
			setTimeout(function(){
				$('#item_dis').val(item_dis + 1);
				$('#is_skipped').val(1);
				var ic = $('#item_dis').val();
				if(ic > 3){
					var tx = (-100 * ic);
					//$('#thumbnail-slider-inner ul').css('transform','translateX('+tx+'px)');
					$('#thumbnail-slider-prev').removeClass('disabled');
				}
			},1000);
		}
	});
});
</script>
<!------- SS Fix Start for Issue ID 2333 on 17-Jan-2024 ---->
<script type="text/javascript">
	$(document).ready(function(){
		
		
		var parent_id = '<?php echo STATE_PARENT_ID; ?>';
        var itemrecord_address_state='<?php echo $itemrecord_address_state;?>';
        $.ajax({
            url: "<?php echo AIB_SERVICE_FILE_PATH;?>services.php",
            type: "post",
            data: {mode: 'get_state_country', parent_id: parent_id},
            success: function (response) {
                var record = JSON.parse(response);
                var i;
                var state = "";
                state += "<option value='' >---Select---</option>";
                for (i = 0; i < record.length; i++) {
                    var data_value = '';
                    if(itemrecord_address_state==record[i]){
						data_value='selected';
					}
                    state += "<option value='" + record[i] + "'  " + data_value + " >" + record[i] + "</option>";
                }
                $("#itemrecord_address_state").html(state);
                $('.loading-div').hide();
				
				 $('#locationDiv').hide();
				if($('#item_dis').val()==1){
					 $('#locationDiv').show();
				}
				

            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 797)');
                $('.loading-div').hide();
            }
        });
		
		
		
		
	});

function getLocationData(){
	
	var itemrecord_address_line=$.trim($('#itemrecord_address_line').val());
	var itemrecord_address_city=$.trim($('#itemrecord_address_city').val());
	var itemrecord_address_state=$.trim($('#itemrecord_address_state').val());
	var itemrecord_address_pin_code=$.trim($('#itemrecord_address_pin_code').val());
	
	if(itemrecord_address_line=='' || itemrecord_address_city=='' || itemrecord_address_state=='' || itemrecord_address_pin_code=='' ){
		
		alert('Please fill location information.');
		return false;
	}
	
	var address=itemrecord_address_line+" "+itemrecord_address_city+" zipcode "+itemrecord_address_pin_code+" "+itemrecord_address_state;
	address=encodeURIComponent(address);
	
	 $.ajax({
            url: "https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&key=<?php echo AIB_GOOGLE_MAP_KEY;?>",
            type: "get",
           
            success: function (response) {
              
				$('#itemrecord_lat').attr('value',response.results[0].geometry.location.lat);
				$('#itemrecord_lng').attr('value',response.results[0].geometry.location.lng);
               

            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again');
                
            }
        });
}	
	
</script>
<!------- SS Fix End for Issue ID 2333 on 17-Jan-2024 ---->