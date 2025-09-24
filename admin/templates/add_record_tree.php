<div class="leftModule">

    <h4 class="marginBottom10"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> <strong>Your Current Location</strong></h4>
	
    <div class="treeStructure">
        <ul class="treeModule css-treeview">
            <?php $count = 0; foreach($treeDataArray as $key=>$treeData){
                $extraPadding = 0;
                if($count == 2){
                    $extraPadding = 15;
                }
                if($count > 2){
                    $extraPadding = ($count-1)*15;
                }
                $padding_left = (($count*15)+$extraPadding); ?>
                <ul style="padding-left: <?php echo $padding_left; ?>px">
                    <?php if($count != 0){ ?><div class="pull-left"><img src="<?php echo IMAGE_PATH . 'arrow-down.png'; ?>" /></div><?php } ?>
                    <?php if((count($treeDataArray)-1) == $key) { ?>
                    <li class="treeActive"><a href="javascript:void(0);"><?php echo $treeData['item_title']; ?></a></li>
                    <?php }else{ ?>
                        <li class="getRecordItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="javascript:void(0);"><?php echo $treeData['item_title']; ?></a></li>
                    <?php } ?>
                </ul>
            <?php $count++; } ?>
        </ul>	
    </div>
</div>
