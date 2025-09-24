<style>
   .historical_list ul.imageDisplay li.resizeLi{width:150px !important; margin:4px 0px !important;}
   .historical_list .height90 {height: 125px !important;}
   .historical_list .ch-info_size {height: 114px !important;width: 134px !important;} 
   .historical_list .ch-info .ch-info-back{display: table;overflow: hidden;}
   .lSSlideWrapper.usingCss{width: 150px;}
   .vertical .lSSlideWrapper.usingCss{width: 150px;margin: 0 auto;}
</style>
<link rel="stylesheet" href="<?php echo CSS_PATH . 'lightslider.css'; ?>" />
<script src="<?php echo JS_PATH . 'lightslider.js'; ?>"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#content-slider_right").lightSlider({
            loop:false,
            keyPress:false,
            item: 3,
            pager: false,
            vertical: true
        });
    });
</script>
<div class="historical_list">
    <input type="hidden" name="list_historical_connection_count" id="list_historical_connection_count" value="<?php echo count($relatedItemsData); ?>" />
    <?php
    if (!empty($relatedItemsData)) {
        $file_name = 'home.html';
        if ($userDetails['user_type'] == 'U') {
            $file_name = 'people.html';
        }
        ?>
        <ul id="content-slider_right" class="content-slider imageDisplay">
            <?php
            $previous_item = [];
            $count = 0;
            foreach ($relatedItemsData as $dataArray) {
                if (isset($dataArray["item_title"]) == true){
                    $dataArray["item_title"] = rawurldecode(urldecode($dataArray["item_title"]));
                }		
                if ($dataArray['item_type'] == 'RE') {
                    $file_name = 'item-details.html';
                }else{
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
                    <li class="resizeLi"><a class="height90" target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder_id='.$dataArray['item_id'].'&society_template='.$dataArray['properties']['custom_template']); ?>">
                            <?php
                            if ($dataArray['thumbId']) {
                                $thumbFile = RECORD_THUMB_URL . '?id=' . $dataArray['thumbId'];
                            } elseif (isset($dataArray['ag_thumb'])) {
                                $thumbFile = ($dataArray['ag_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['ag_thumb'])) ? ARCHIVE_IMAGE . $dataArray['ag_thumb'] : IMAGE_PATH . 'historica_connection.png';
                            } else {
                                $thumbFile = IMAGE_PATH . 'folder.png';
                            }
                            ?>

                            <div class="ch-item padd7">

                                <div class="ch-info ch-info_size">
                                    <div class="ch-info-front" style="background-image:url(<?php echo $thumbFile ?>);"></div>
                                    <div class="ch-info-back">
                                        <h3 title="<?php echo $dataArray['item_society']; ?>"><?php echo $dataArray['item_society']; ?></h3>
                                    </div>
                                </div>
                            </div>

                        </a><h3 class="colorBlack" title="<?php echo $dataArray['item_title']; ?>"><?php echo $dataArray['item_title']; ?></h3></li>
                    <?php } ?>
                    <?php $count ++;
                }
                ?>
        </ul>
    <?php } else { ?>
        <strong>No Historical Connections Found.</strong>
<?php } ?>
</div>
