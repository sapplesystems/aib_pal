<style>
    .slider_historical .ch-info .ch-info-back{display: table;overflow: hidden;transform: none; display:none;}
    .slider_historical ul.imageDisplay li.resizeLi{width:150px !important; margin:5px !important;}
    .slider_historical .height90 {height: 90px !important;}
    .slider_historical .ch-info_size {height: 84px !important;width: 108px !important; transform:none !important;}
    .slider_historical .ch-info_size:hover .ch-info-back{display:inline-table;}
    .slider_historical li.resizeLi{width:124px !important;}
    .widthFull100{width:100% !important;}
</style>
<!--link rel="stylesheet" href="<?php //echo CSS_PATH . 'lightslider.css';  ?>" />
<script src="<?php //echo JS_PATH . 'lightslider.js';  ?>"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#content-slider").lightSlider({
            loop: false,
            keyPress: false,
            item: 4,
            pager: false
        });
    });
</script-->
<div id="tab6" class="tab-contentH item">
    <input type="hidden" name="historical_connection_count" id="historical_connection_count" value="<?php echo count($relatedItemsData); ?>" />
    <?php
    $count = 0;
    if (!empty($relatedItemsData)) {
        $file_name = 'home.php';
        if ($userDetails['user_type'] == 'U') {
            $file_name = 'people.php';
        }
        ?>
        <ul id="content-slider" class="content-slider imageDisplay slider_historical widthFull100">
            <?php
            $previous_item = [];
            $count = 0;
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
                    <li class="resizeLi"><a class="height90" target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder_id=' . $dataArray['item_id'] . '&society_template=' . $dataArray['properties']['custom_template']); ?>">
                            <?php
                            if ($dataArray['thumbId']) {
                                $thumbFile = RECORD_THUMB_URL . '?id=' . $dataArray['thumbId'];
                            } elseif (isset($dataArray['ag_thumb'])) {
                                $thumbFile = ($dataArray['ag_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['ag_thumb'])) ? ARCHIVE_IMAGE . $dataArray['ag_thumb'] : IMAGE_PATH . 'historica_connection.png';
                            } else {
                                $thumbFile = IMAGE_PATH . 'folder.png';
                            }
                            $item_title = str_replace("+", " ", $dataArray['item_title']);
                            ?>

                            <div class="ch-item padd7">

                                <div class="ch-info ch-info_size">
                                    <div class="ch-info-front" style="background-image:url(<?php echo $thumbFile ?>);"></div>
                                    <div class="ch-info-back">
                                        <h3 title="<?php echo $dataArray['item_society']; ?>"><?php echo $dataArray['item_society']; ?></h3>
                                    </div>
                                </div>
                            </div>

                        </a><h3 class="colorBlack" title="<?php echo $item_title; ?>"><?php echo $item_title; ?></h3></li>
                    <?php } ?>
                    <?php
                    $count ++;
                }
                ?>
        </ul>
    <?php } /* else { ?>
      <strong>No Historical Connections Found.</strong>
      <?php } */ ?>
</div>
<script type="text/javascript">
    if (parseInt($('#historical_connection_count').val()) > 0) {
        $('.historical_connection_tab').css('display','block');
        $('#historical_count').html('(<?php echo $count; ?>)');
    }
</script>