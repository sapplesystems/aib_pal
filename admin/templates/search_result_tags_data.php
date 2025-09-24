<?php if(!empty($finalDataArray)){ ?>
    <div class="you-were-here">
        <div class="item-location you-were-here-right">
            <?php if(!empty($previousYouWere)){ 
                foreach($previousYouWere as $previousDataArray){
                    $prev_file_name="home.html?q=".encryptQueryString('folder_id='.$previousDataArray['item_id']);
                    if($previousDataArray['item_type']=='RE'){
                        $prev_file_name="item-details.html?q=".encryptQueryString('folder_id='.$previousDataArray['item_id']);
                    }
                ?> 
                    <a href="<?php echo $prev_file_name; ?>"><?php echo $previousDataArray['item_title']; ?></a>
                <?php } 
            } ?>(You were here.)
        </div>
    </div>
    <ul class="search-result">
        <?php foreach($finalDataArray as $dataArray){ 
            if($dataArray['item_type'] == 'RE'){
                $display_image = RECORD_THUMB_URL.'?id='.$dataArray['item_id'];
                $link = "item-details.html?q=".encryptQueryString('folder_id='.$dataArray['item_id']);
            }
            if($dataArray['item_type'] == 'IT'){
                $display_image = THUMB_URL.'?id='.$dataArray['tn_file_id'];
                $link = "item-details.html?q=".encryptQueryString('folder_id='.$dataArray['item_parent'].'&itemId='.$dataArray['item_id']);
            }
            $style="";
            if(isset($dataArray['link_visited']) && $dataArray['link_visited'] == 'yes'){
                $style = "style='color: #609;'";
            }
            ?>
            <li>
                <div class="col-md-2">
                    <a style="display: block;" class="organizations search_result_clicked" search_item_id="<?php echo $dataArray['item_id']; ?>" href="<?php echo $link; ?>" target="_blank">
                        <img class="searchResultImg" src="<?php echo $display_image ?>" title="<?php echo $dataArray['item_title']; ?>" />
                    </a>
                </div>
                <div class="col-md-10">
                    <div class="marginTopBottom10">
                        <a style="display: block;" class="organizations search_result_clicked" search_item_id="<?php echo $dataArray['item_id']; ?>" href="<?php echo $link; ?>" target="_blank">
                            <span <?php echo $style; ?>><?php echo $dataArray['item_title'][0]; ?><?php echo substr($dataArray['item_title'], 1); ?></span>
                        </a>
                    </div>
                    <div class="marginTopBottom10 item-location">
                        <?php if(!empty($dataArray['item_location'])){ 
                            foreach($dataArray['item_location'] as $dataArray){
                                $file_name="home.html?q=".encryptQueryString('folder_id='.$dataArray['item_id']);
                                if($dataArray['item_type']=='RE'){
                                    $file_name="item-details.html?q=".encryptQueryString('folder_id='.$dataArray['item_id']);
                                }
                                ?> 
                                <a href="<?php echo $file_name; ?>"><?php echo $dataArray['item_title']; ?></a>
                            <?php } 
                        } ?>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
<?php }else{ ?>
<h3 class="text-center">No matching records found.</h3>
<?php } ?>

