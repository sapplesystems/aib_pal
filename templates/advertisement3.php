<style>
    marquee img{margin-bottom:10px;display: block;/*border: 1px solid #cccccc;*/ text-align:center; /*padding:10px;*/}
    marquee{height: 250px;}
    .advertisement_head{background: #fbd42f;color: #15345a;padding: 5px 20px;display: inline-block;margin-top: 0px;margin-bottom: 10px; float:left;}
    #advertisement3.ad_border{width: 300px;display: inline-block;overflow: hidden;height:250px;}
    #advertisement2.ad_border img{width:300px;height:250px;} 
</style>
<?php
if ($response['status'] == 'OK') {
    $ad_record = $response['info']['records'];
    //echo '<pre>';print_r($apiResponse['info']['records']);echo '</pre>';
    $ad_display_type = ($apiResponse['info']['records']['ad_display_type']) ? $apiResponse['info']['records']['ad_display_type'] : '2';
    $ad_display_flip_time = ($apiResponse['info']['records']['ad_display_flip_time']) ? $apiResponse['info']['records']['ad_display_flip_time'] : '-1';
    $ad_record_count = count($ad_record);
    if ($ad_record_count) {
        ?>
        <div class="ad_border" id="advertisement3">
            <?php if ($ad_display_type == '2') { ?>
                <marquee behavior="scroll" direction="up" onmouseover="this.stop();" onmouseout="this.start();">
                    <?php
                    for ($a = 0; $a < $ad_record_count; $a++) {
                        $ad_url = '';
                        if (!empty($ad_record[$a]['ad_url'])) {
                            $ad_url = $ad_record[$a]['ad_url'];
                        }
                        $record_ref = $ad_record[$a]['record_ref'];
                        $original_file = '';
                        if ($ad_record[$a]['original_file'] != '') {
                            $original_file = $ad_record[$a]['original_file'];
                        }
                        if ($record_ref > 0) {
                            $original_file = $ad_record[$a]['source_def']['original_file'];
                        }
                        if (!empty($ad_url)) {
                            ?>
                            <a target="_blank" href="<?php echo $ad_url; ?>">
                                <?php if ($original_file) { ?>
                                    <img src="../get_ad_thumb.php?original_file=<?php echo $original_file; ?>" alt="<?php echo $ad_record[$a]['ad_alt_title']; ?>" />
                                <?php } ?>
                            </a>
                            <?php
                        } else {
                            ?>
                            <img src="../get_ad_thumb.php?original_file=<?php echo $original_file; ?>" alt="<?php echo $ad_record[$a]['ad_alt_title']; ?>" />
                            <?php
                        }
                    }
                    ?>
                </marquee>
            <?php } else { ?>
                <?php
                $ad_url = '';
                $a = ($flag) ? $flag : 0;
                if (!$ad_record[$a]['record_id']) {
                    $a = 0;
                }
                if (!empty($ad_record[$a]['ad_url'])) {
                    $ad_url = $ad_record[$a]['ad_url'];
                }
                $record_ref = $ad_record[$a]['record_ref'];
                $original_file = '';
                if ($ad_record[$a]['original_file'] != '') {
                    $original_file = $ad_record[$a]['original_file'];
                }
                if ($record_ref > 0) {
                    $original_file = $ad_record[$a]['source_def']['original_file'];
                }
                if (!empty($ad_url)) {
                    ?>
                    <a target="_blank" href="<?php echo $ad_url; ?>">
                        <?php if ($original_file) { ?>
                            <img src="../get_ad_thumb.php?original_file=<?php echo $original_file; ?>" alt="<?php echo $ad_record[$a]['ad_alt_title']; ?>" />
                        <?php } ?>
                    </a>
                    <?php
                } else {
                    ?>
                    <img src="../get_ad_thumb.php?original_file=<?php echo $original_file; ?>" alt="<?php echo $ad_record[$a]['ad_alt_title']; ?>" />
                    <?php
                }
                ?>
                <?php /*if ($ad_display_flip_time > 0) { ?>
                    <script type="text/javascript">
                        setTimeout(function () {
                            getAdvertisementFlip3(<?php echo json_encode($response); ?>, '<?php echo $_REQUEST['folder_id']; ?>',<?php echo $a++; ?>);
                        },<?php echo $ad_display_flip_time * 1000; ?>);
                    </script>
                <?php }*/ ?>
            <?php } ?>

        </div>
        <?php
    }
}
?>