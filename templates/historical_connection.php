<div id="tab5" class="tab-contentH">
    <div class="leftSection">
        <span id="historicalConnectionNo" style="display:none"><?php echo count($relatedItemsData); ?></span>
        <!--<h4 class="">Public Connections <a href="#">View All</a></h4>-->
        <h2  style="color:#FFF;margin:3px;">Historical Connections</h2>
        <?php
        if (!empty($relatedItemsData)) {
            ?>
            <ul class="contentRelated">
                <?php
                $previous_item = [];
                $count = 0;
                foreach ($relatedItemsData as $dataArray) {
					if (isset($dataArray["item_title"]) == true)
					{
						$dataArray["item_title"] = rawurldecode(urldecode($dataArray["item_title"]));
					}
                    $file_name = 'home.html';
                    if ($dataArray['top_parent'] == '_STDUSER') {
                        $file_name = 'people.html';
                    }
                    if($dataArray['item_type'] == 'RE'){
                        $file_name = 'item-details.html';
                    }
                    if ($count == 0) {
                        $show = true;
                        $previous_item[] = $dataArray['item_id'];
                    } else {
                        if (in_array($dataArray['item_id'], $previous_item)) {
                            $show = false;
                        } else {
                            $previous_item[] = $dataArray['item_id'];
                            $show = true;
                        }
                    }
                    ?>
                            <?php if ($show) { ?> <li><a target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder_id='.$dataArray['item_id']); ?>">
                                <?php if ($dataArray['thumbId']) { ?>
                                    <img class="imgRelatedContentGrid" style="width: 29px;" src="<?php echo RECORD_THUMB_URL . '?id=' . $dataArray['thumbId']; ?>" alt="" />
                                <?php }elseif(isset($dataArray['ag_thumb'])){ 
                                    $imageName = ($dataArray['ag_thumb'] != '' && file_exists(ARCHIVE_IMAGE.$dataArray['ag_thumb'])) ? ARCHIVE_IMAGE.$dataArray['ag_thumb'] : IMAGE_PATH . 'no-image1.png';
                                    ?>
                                    <img class="imgRelatedContentGrid" style="width: 29px;" src="<?php echo $imageName; ?>" alt="" />
                               <?php } else { ?>
                                    <img class="imgRelatedContentGrid" style="width: 29px;" src="<?php echo IMAGE_PATH . 'folder.png'; ?>" alt="" />
                        <?php } ?>
                                <span><?php echo $dataArray['item_title']; ?></span></a></li><?php } ?>
                <?php $count ++;
            } ?>
            </ul>
<?php } else { ?>
            <strong>No Historical Connections Found.</strong>
    <?php } ?>
    </div>
</div>
<div id="tab6" class="tab-contentH" style="height:370px !important;">
    <?php
    if (!empty($relatedItemsData)) {
        $file_name = 'home.html';
        if ($userDetails['user_type'] == 'U') {
            $file_name = 'people.html';
        }
        ?>
        <div class="related-scroll">
        <h2  style="color:#FFF;margin:3px;">Historical Connections</h2>
            <ul class="imageDisplay">
                <?php
                $previous_item = [];
                $count = 0;
                foreach ($relatedItemsData as $dataArray) {
                    if($dataArray['item_type'] == 'RE'){
                        $file_name = 'item-details.html';
                    }
                    if ($count == 0) {
                        $show = true;
                        $previous_item[] = $dataArray['item_id'];
                    } else {
                        if (in_array($dataArray['item_id'], $previous_item)) {
                            $show = false;
                        } else {
                            $previous_item[] = $dataArray['item_id'];
                            $show = true;
                        }
                    }
                    ?>
                            <?php if ($show) { ?> 

                        <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" target="_blank" href="<?php echo $file_name; ?>?folder_id=<?php echo $dataArray['item_id']; ?>">
                                <?php
                                if ($dataArray['thumbId']) {
                                    $thumbFile = RECORD_THUMB_URL . '?id=' . $dataArray['thumbId'];
                                } elseif(isset($dataArray['ag_thumb'])){ 
                                    $thumbFile = ($dataArray['ag_thumb'] != '' && file_exists(ARCHIVE_IMAGE.$dataArray['ag_thumb'])) ? ARCHIVE_IMAGE.$dataArray['ag_thumb'] : IMAGE_PATH . 'no-image1.png';
                                } else {
                                    $thumbFile = IMAGE_PATH . 'folder.png';
                                }
                                ?>

                                <div class="ch-item">

                                    <div class="ch-info">
                                        <div class="ch-info-front" style="background-image:url(<?php echo $thumbFile ?>);"></div>
                                        <div class="ch-info-back">
                                            <h3 title="<?php echo $dataArray['item_title']; ?>"><?php echo $dataArray['item_title']; ?></h3>
                                        </div>
                                    </div>
                                </div>

                            </a><h3 title="<?php echo $dataArray['item_title']; ?>"><?php echo $dataArray['item_title']; ?></h3></li><?php } ?>
        <?php $count ++;
    } ?>
            </ul>
        </div>
<?php } else { ?>
        <strong>No Historical Connections Found.</strong>
<?php } ?>
</div>