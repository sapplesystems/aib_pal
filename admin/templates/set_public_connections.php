<div style="height: 350px;">


<form id="move_form_item" name="move_form_item" method="post" class="form-horizontal">
    <input type="hidden" name="selected_record_id" id="selected_record_id" value="<?php echo $record_id; ?>">
    <input type="hidden" name="previous_selected_parent" id="previous_selected_parent" value='<?php echo $previous_related_parent; ?>'>
<?php foreach($societyDataArray as $societyData){
	$checked='';
	if(in_array($societyData['info']['item_id'],$assignedSubGroups))
		$checked=' checked="checked"';
	?>
     <input <?php echo $checked; ?> type="checkbox" id="available_<?php echo $societyData['info']['item_id']; ?>" class="form-check-input available-subgroup" value="<?php echo $societyData['info']['item_id']; ?>" data-name="<?php echo $societyData['info']['item_title']; ?>">
                    <a style="padding-left: 5px;"><?php echo $societyData['info']['item_title']; ?></a><br />
   <?php 
} ?>
 <div class="form-group">
        <label class="col-xs-4 control-label"></label>
        <div class="col-xs-7">
            <button type="button" class="btn btn-info borderRadiusNone" name="set_record_as_related_record" id="set_record_as_related_record">Submit</button>
        </div>
    </div>
</form>

