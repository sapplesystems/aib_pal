<form id="move_form_item" name="move_form_item" method="post" class="form-horizontal">
    <input type="hidden" name="selected_record_id" id="selected_record_id" value="<?php echo $record_id; ?>">
    <input type="hidden" name="previous_selected_parent" id="previous_selected_parent" value='<?php echo $previous_related_parent; ?>'>
    <div class="col-md-12 text-center"><h4 class="marginBottom10">Record you are setting related content for</h4></div>
    <div class="clearfix"></div>
    <div class="col-md-12 borderBottom1 text-center"><img style="max-width: 100%; height: 120px;" src="<?php echo RECORD_THUMB_URL.'?id='.$record_id; ?>"><span class="recordTitle"><strong>Record Title:</strong> <?php echo $recordDetails['item_title']; ?></span></div>
    <div class="clearfix"></div>
    <div class="col-md-12"><h4 class="marginBottom10 list_title">Select Sub Groups</h4></div>
    <div class="clearfix"></div>
    <div class="form-group set_related_item">
        <?php
			$valueFound = 0;  
			if($login_user_type == 'U'){ 
				?>
            <ul class="tree-view">
                <?php foreach($allSubgroupsArray as $key=>$dataArray){ 
				  	$valueFound = 1;	
                    $checked = "";
                    if(in_array($dataArray['item_id'],$assignedSubGroups)){
                        $checked = "checked='checked'";
                    }
                    ?>
                <li data-id="<?php echo $dataArray['item_id']; ?>" data-type="<?php echo $dataArray['item_type']; ?>"  style="list-style: none;">
                    <input <?php echo $checked; ?> type="checkbox" id="available_<?php echo $dataArray['item_id']; ?>" class="form-check-input available-subgroup" value="<?php echo $dataArray['item_id']; ?>" data-name="<?php echo $dataArray['item_title']; ?>">
                    <a style="padding-left: 5px;"><?php echo $dataArray['item_title']; ?></a>
                </li>
                <?php } ?>
            </ul>
        <?php }else{  ?>
            <ul class="tree-view">
                    <li data-id="<?php echo $allSubgroupsArray['parent']['item_id']; ?>" data-type="<?php echo $allSubgroupsArray['parent']['item_type']; ?>" style="list-style: none;">
			<a style="padding-left: 5px;"><?php echo $allSubgroupsArray['parent']['item_title']; ?></a>
                        <ul>
                            <?php if (!empty($allSubgroupsArray['parent']['archive'])) { 
                                foreach ($allSubgroupsArray['parent']['archive'] as $archiveKey => $archiveDataArray) { 
									$valueFound = 1;	
                                    if($archiveDataArray['item_title'] != 'Advertisements'){ ?>
                                        <li data-id="<?php echo $archiveDataArray['item_id']; ?>" style="list-style: none;">
                                            <a style="padding-left: 5px;"><?php echo $archiveDataArray['item_title']; ?></a>
                                            <?php if(!empty($archiveDataArray['collection'])){ ?>
                                                <ul>
                                                <?php foreach($archiveDataArray['collection'] as $collectionKey=>$collectionDataArray){
                                                    if($collectionDataArray['item_title'] != 'Advertisements'){ ?>
                                                        <li data-id="<?php echo $collectionDataArray['item_id']; ?>" style="list-style: none;">
                                                            <a style="padding-left: 5px;"><?php echo $collectionDataArray['item_title']; ?></a>
                                                            <?php if (!empty($collectionDataArray['sub_groups'])) { ?>
                                                            <ul>
                                                                <?php foreach ($collectionDataArray['sub_groups'] as $subgroup_key => $subgroupDataArray) {
                                                                        if($subgroupDataArray['item_title'] != 'Advertisements'){ ?>
                                                                            <li class="tree-sub-group" data-id="<?php echo $subgroupDataArray['item_id']; ?>">
                                                                            <?php if (in_array($subgroupDataArray['item_id'], $assignedSubGroups)) { ?>
                                                                                <input type="checkbox" id="available_<?php echo $subgroupDataArray['item_id']; ?>" class="form-check-input available-subgroup" checked="checked" value="<?php echo $subgroupDataArray['item_id']; ?>" data-name="<?php echo $subgroupDataArray['item_title']; ?>">
                                                                            <?php } else { ?>   
                                                                                <input type="checkbox" id="available_<?php echo $subgroupDataArray['item_id']; ?>" class="form-check-input available-subgroup" value="<?php echo $subgroupDataArray['item_id']; ?>" data-name="<?php echo $subgroupDataArray['item_title']; ?>">
                                                                            <?php } ?>
                                                                            <a><?php echo $subgroupDataArray['item_title']; ?></a></li>
                                                                    <?php } 
                                                                } ?>
                                                            </ul>
                                                            <?php } ?>
                                                        </li>
                                                    <?php }
                                                    } ?>
                                                </ul>
                                        <?php } ?>
                                    </li>
                               <?php }
				}
                            } ?>
                        </ul>
                    </li>
                </ul>
        <?php } 
			if($valueFound ==0){ ?>
		<div>
		<center style="color:red">NO Sub Group Available !</center>
		</div> 
		<?php } ?>
    </div>
	<?php if($valueFound ==1){?>
    <div class="form-group">
        <label class="col-xs-4 control-label"></label>
        <div class="col-xs-7">
            <button type="button" class="btn btn-info borderRadiusNone" name="set_record_as_related_record" id="set_record_as_related_record">Submit</button>
        </div>
    </div>
	<?php }?>
</form>