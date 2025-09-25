<div id="tab6" class="tab-contentH item customSlider ">
    <input type="hidden" name="historical_connection_count" id="historical_connection_count" value="<?php echo count($relatedItemsData); ?>" />
    <?php
    $his_con_count = 0;
    if (!empty($relatedItemsData)) {
        $file_name = 'home.html';
        if ($userDetails['user_type'] == 'U') {
            $file_name = 'people.html';
        }
        ?>
        <ul class="default_historical_connection_ul">
            <?php
            $previous_item = [];
            $count = 0;
            $his_con_count = 0;
            foreach ($relatedItemsData as $dataArray) {
                if (isset($dataArray["item_title"]) == true) {
                    $dataArray["item_title"] = rawurldecode(urldecode($dataArray["item_title"]));
                }
                if ($dataArray['item_type'] == 'RE') {
                    $file_name = 'item-details.html';
                } else {
                    $file_name = 'home.html';
                    if ($userDetails['user_type'] == 'U') {
                        $file_name = 'people.html';
                    }
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
                    <li class="default_historical_connection_li">
                        <a target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder_id=' . $dataArray['item_id'] . '&society_template=' . $dataArray['properties']['custom_template']); ?>">
                            <?php
                            if ($dataArray['thumbId']) {
                                $thumbFile = RECORD_THUMB_URL . '?id=' . $dataArray['thumbId'];
                            } elseif (isset($dataArray['ag_thumb'])) {
                                $thumbFile = ($dataArray['ag_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['ag_thumb'])) ? ARCHIVE_IMAGE . $dataArray['ag_thumb'] : IMAGE_PATH . 'historica_connection.png';
                            } else {
                                $thumbFile = IMAGE_PATH . 'folder.png';
                            }
                            ?>
                            <img src="<?php echo $thumbFile ?>" />
                            <h3 class="hoverText" title="<?php echo $dataArray['item_society']; ?>"><?php echo $dataArray['item_society']; ?></h3>
                        </a>
                        <h3 class="colorBlack" title="<?php echo str_replace("+"," ",$dataArray['item_title']); ?>"><?php echo str_replace("+"," ",$dataArray['item_title']); ?></h3>
                    </li>
                <?php } ?>
                <?php
                $count ++;
                $his_con_count++;
            }
            ?>
        </ul>
    <?php } else { ?>
        <strong>No Historical Connections Found.</strong>
<?php } ?>
</div>
<script type="text/javascript">
$('#his_con_count').html('(<?php echo $his_con_count; ?>)');
</script>