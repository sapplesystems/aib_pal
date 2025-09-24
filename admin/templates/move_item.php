<div class="row">
    <div class="col-md-5 move_item_left">
        <h5 class="list_title">Select Item(s) to move</h5>
        <ul class="move-list-item">
            <?php foreach($leftItemList as $leftDataArray){ if($leftDataArray['item_type'] == $item_type){ ?>
            
            <li>
                <input type="checkbox" data-id="<?php echo $leftDataArray['item_id']; ?>" class="left-section" <?php if($item_id == $leftDataArray['item_id']){ echo 'checked'; } ?> />
                <span><?php echo $leftDataArray['item_title']; ?></span>
            </li>
            <?php } } ?>
        </ul>
    </div>
    <div class="col-md-7 move_item_right">
        <h5 class="list_title">Select parent where moved</h5>
        <ul class="move-list-item">
            <?php foreach($rightItemList as $rightDataArray){ 
                //if($itemParentId != $rightDataArray['item_id']){?>
                <li>
                    <span id="list_<?php echo $rightDataArray['item_id']; ?>">
                        <?php //echo $accessArray[$item_type] .'== '.$rightDataArray['item_type']; ?>
                        <?php if($accessArray[$item_type] == $rightDataArray['item_type'] || $rightDataArray['item_type'] == 'SG'){ ?>
                        <?php if($rightDataArray['item_type'] == 'SG' || $item_type == 'SG'){ ?>
                            <span selected-item="<?php echo $selected_item; ?>" data-id="<?php echo $rightDataArray['item_id']; ?>" selected-data-type="<?php echo $selected_type; ?>" data-type="<?php echo $rightDataArray['item_type']; ?>" class="glyphicon glyphicon-plus load-more-child" aria-hidden="true"></span>
                            <i class='fa fa-spinner fa-spin hideLoader'></i>
                        <?php }  ?>
                            <?php if($itemParentId != $rightDataArray['item_id'] && $item_id != $rightDataArray['item_id']){ ?>
                                <input type="checkbox" data-id="<?php echo $rightDataArray['item_id']; ?>" class="right-section" id="right_section_<?php echo $rightDataArray['item_id']; ?>" />
                            <?php } ?>
                        <?php }else{ ?>
                            <span selected-item="<?php echo $item_id; ?>" data-id="<?php echo $rightDataArray['item_id']; ?>" selected-data-type="<?php echo $item_type; ?>" data-type="<?php echo $rightDataArray['item_type']; ?>" class="glyphicon glyphicon-plus load-more-child" aria-hidden="true"></span>
                            <i class='fa fa-spinner fa-spin hideLoader'></i>
                        <?php } ?>
                        <span><?php echo $rightDataArray['item_title']; ?></span>
                    </span>
                </li>
            <?php } //} ?>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4 text-center">
        <button type="button" class="btn btn-info borderRadiusNone" name="move_selected_item" id="move_selected_item">Move</button>
        <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
    </div>
    <div class="col-md-4"></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	var $chkboxes = $('.move-list-item .left-section');
		var lastChecked = null;

	$.each($chkboxes, function(){
		if($(this).is(':checked')){
			lastChecked = this;
		}
	});

	$chkboxes.click(function(e) {
		if (!lastChecked) {
			lastChecked = this;
			return;
		}

		if (e.shiftKey) {
			var start = $chkboxes.index(this);
			var end = $chkboxes.index(lastChecked);
			$chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);
		}

		lastChecked = this;
	});
});
</script>

