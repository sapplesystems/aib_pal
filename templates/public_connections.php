<?php
$pub_con_count = 0;
if ($themeName == 'custom') {
    if (!empty($publicConnectioData)) {
        $file_name = 'people_profile.html';
        $previous_item = [];
        $count = 0;
        ?>
        <ul class="related_content">
            <?php
            foreach ($publicConnectioData as $dataArray) {
                $image_name = (isset($dataArray['archive_group_thumb']) && $dataArray['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['archive_group_thumb'])) ? ARCHIVE_IMAGE . $dataArray['archive_group_thumb'] : IMAGE_PATH . 'avatar-1.png';
                ?>
                <li>
					<a target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder-id=' . $dataArray['properties']['public_folder']); ?>">
						<img src="<?php echo $image_name; ?>" alt="Image-1" /><span><?php echo $dataArray['user_title']; ?></span>
					</a>
				</li>
        <?php } ?>
        </ul>
    <?php
    } else {
        echo '<strong>No Public Connections Found.</strong>';
    }
    ?>
<?php } elseif ($themeName == 'custom1') { 
    
    if (!empty($publicConnectioData)) {
        $file_name = 'people_profile.html';
        $previous_item = [];
        $count = 0;
        ?>
        <ul class="relContent_tab">
            <?php
            foreach ($publicConnectioData as $dataArray) {
                $image_name = (isset($dataArray['archive_group_thumb']) && $dataArray['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['archive_group_thumb'])) ? ARCHIVE_IMAGE . $dataArray['archive_group_thumb'] : IMAGE_PATH . 'avatar-1.png';
                ?>
                <li>
					<a target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder-id=' . $dataArray['properties']['public_folder']); ?>">
						<img src="<?php echo $image_name; ?>" alt="Image-1" /><span><?php echo $dataArray['user_title']; ?></span>
					</a>
				</li>
        <?php } ?>
        </ul>
    <?php
    } else {
        echo '<strong>No Public Connections Found.</strong>';
    }
} else { ?>
    <div id="tab3" class="tab-contentP">
        <div class="leftSection">
            <!--<h4 class="">Public Connections <a href="#">View All</a></h4>-->
            <span id="publicConnectionNo" style="display:none"><?php echo count($publicConnectioData); ?></span>
                <?php
                if (!empty($publicConnectioData)) {
                    $file_name = 'people_profile.html';
                    ?>
                <ul class="contentRelated">
                    <?php
                    $previous_item = [];
                    $count = 0;
                    foreach ($publicConnectioData as $dataArray) {
                        $pub_con_count++;
                        $image_name = (isset($dataArray['archive_group_thumb']) && $dataArray['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['archive_group_thumb'])) ? ARCHIVE_IMAGE . $dataArray['archive_group_thumb'] : IMAGE_PATH . 'avatar-1.png';
                        ?>
                        <li><a target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder-id=' . $dataArray['properties']['public_folder']); ?>">

                                <img src="<?php echo $image_name; ?>" alt="" />

                                <span><?php echo $dataArray['user_title']; ?></span></a><div class="dotInline" data-count="<?php echo $count; ?>"><span></span><br /><span></span><br /><span></span>
                                <div id="report_user_<?php echo $count; ?>" class="report-user" report-username="<?php echo $dataArray['user_title']; ?>" report_image="<?php echo $image_name; ?>">Report</div>
                            </div>

                        </li>
                    <?php
                    $count ++;
                }
                ?>
                </ul>
        <?php } else { ?>
                <strong>No Public Connections Found.</strong>
        <?php } ?>
        </div>
    </div>
    <div id="tab4" class="tab-contentP">
                <?php
                if (!empty($publicConnectioData)) {
                    $file_name = 'people_profile.html';
                    ?>
            <div class="contentScroll" id="content-7" >
                <ul class="imageDisplay">
                    <?php
                    $previous_item = [];
                    $count = 0;
                    foreach ($publicConnectioData as $dataArray) {
                        $image_name = (isset($dataArray['archive_group_thumb']) && $dataArray['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $dataArray['archive_group_thumb'])) ? ARCHIVE_IMAGE . $dataArray['archive_group_thumb'] : IMAGE_PATH . 'avatar-1.png';
                        ?>


                        <li style="border:none;">
						<a data-folder-id="<?php echo $publicConnectioData['item_id']; ?>" target="_blank" href="<?php echo $file_name; ?>?q=<?php echo encryptQueryString('folder-id=' . $dataArray['properties']['public_folder']); ?>">


                                <div class="ch-item">

                                    <div class="ch-info" style="border-radius: 50%;">
                                        <div class="ch-info-front" style="    border-radius: 50px !important;background-image:url(<?php echo $image_name ?>);"></div>
                                        <div  style="border-radius: 50%;" class="ch-info-back">
                                            <h3><?php echo $dataArray['user_title']; ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <h3><?php echo $dataArray['user_title']; ?></h3>
                            </a><div class="dotInline_grid" data-count-grid="<?php echo $count; ?>"><span></span><br /><span></span><br /><span></span>
                                <div id="report_user_grid_<?php echo $count; ?>" class="report-user_grid" report-username="<?php echo $dataArray['user_title']; ?>" report_image="<?php echo $image_name; ?>">Report</div>
                            </div></li>
                <?php
                $count ++;
            }
            ?>
                </ul>
            </div>

    <?php } else { ?>
            <strong>No Public Connections Found.</strong>
    <?php } ?>
    </div>
<?php } ?>
<script type="text/javascript">
$('#pub_con_count').html('(<?php echo $pub_con_count; ?>)');
</script>