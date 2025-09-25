<?php if(!empty($childList)){ ?>
    <ul class="move-list-item">
        <?php foreach($childList as $rightDataArray){ 
            //if($itemParentId != $rightDataArray['item_id']){?>
            <li>
                <span id="list_<?php echo $rightDataArray['item_id']; ?>">
                    <?php //echo $accessArray[$selected_type]. '==' .$rightDataArray['item_type']; ?>
                    <?php if($accessArray[$selected_type] == $rightDataArray['item_type'] || $rightDataArray['item_type'] == 'SG'){ ?>
                    <?php if($rightDataArray['item_type'] == 'SG' || $selected_type == 'SG'){ ?>
                        <span selected-item="<?php echo $selected_item; ?>" data-id="<?php echo $rightDataArray['item_id']; ?>" selected-data-type="<?php echo $selected_type; ?>" data-type="<?php echo $rightDataArray['item_type']; ?>" class="glyphicon glyphicon-plus load-more-child" aria-hidden="true"></span>
                        <i class='fa fa-spinner fa-spin hideLoader'></i>
                    <?php } ?>
                        <?php if($itemParentId != $rightDataArray['item_id'] && $rightDataArray['item_id'] != $selected_item){ ?>
                            <input type="checkbox" data-id="<?php echo $rightDataArray['item_id']; ?>" class="right-section" id="right_section_<?php echo $rightDataArray['item_id']; ?>" />
                        <?php } ?>
                        <?php }else{ ?>
                        <span selected-item="<?php echo $selected_item; ?>" data-id="<?php echo $rightDataArray['item_id']; ?>" selected-data-type="<?php echo $selected_type; ?>" data-type="<?php echo $rightDataArray['item_type']; ?>" class="glyphicon glyphicon-plus load-more-child" aria-hidden="true"></span>
                        <i class='fa fa-spinner fa-spin hideLoader'></i>
                    <?php } ?>
                    <span><?php echo $rightDataArray['item_title']; ?></span>
                </span>
            </li>
        <?php } //} ?>
    </ul>
<?php }else{ 
    echo "no-data";
 } 
