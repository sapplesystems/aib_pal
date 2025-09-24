<?php 
foreach ($apiResponse['info']['records'] as $itemDataArray) {
    if($_POST['group_type'] =='IT'){
    ?>
                        <tr>
                            <td class="ch-grid">
                <?php $image_name = (isset($itemDataArray['default_property']['archive_group_thumb']) && $itemDataArray['default_property']['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $itemDataArray['default_property']['archive_group_thumb'])) ? ARCHIVE_IMAGE . $itemDataArray['default_property']['archive_group_thumb'] : IMAGE_PATH . 'avatar-1.png'; ?>
                                <a title="<?php echo (isset($itemDataArray['item_title']) && $itemDataArray['item_title'] != '') ? $itemDataArray['item_title'] : 'Username'; ?>" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.html?q=<?php echo encryptQueryString('folder-id='.$itemDataArray['item_id']); ?>">
                                    <div class="ch-item">

                                        <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(<?php echo $image_name; ?>);"></div>
                                            <div class="ch-info-back">
                                                <h3>OPEN<br />MY<br />BOX</h3>	
                                            </div>
                                        </div>
                                    </div> 
                                    <h3><strong><?php echo (isset($itemDataArray['item_title']) && $itemDataArray['item_title'] != '') ? $itemDataArray['item_title'] : 'Username'; ?></strong></h3>
                                </a>
									
                            </td>
                        </tr>
        <?php }else{ ?>
    
       <tr style="width: 14%;">
                                        <td>
                                            <div class="view view-first <?php
                                            if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                                if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                                    echo 'active';
                                                }
                                            }
                                            ?>">
                        <?php if (isset($itemDataArray['item_parent_id']) && $itemDataArray['item_parent_id'] != '') { ?>
                                                    <img src="<?php echo THUMB_URL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="Thumb Image"/>
                                                    <a class="custom-link" href="item-details.html?q=<?php echo encryptQueryString('folder_id='.$itemDataArray['item_parent_id'] . '&itemId=' . $itemDataArray['item_id'] .'&flg=people'); ?>">
                                                        <div class="mask">
                                                            <h2>Open</h2>
                                                            <?php if(isset($itemDataArray['child_count'])){ ?>
                                                            <span><?php echo $itemDataArray['child_count']; ?></span> 
                                                            <?php } ?>
                                                        </div>
                                                    </a>
                        <?php } else { ?>
                                                    <img src='<?php echo RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id']; ?>' alt="Thumb Image"/>

                                                    <div class="mask">
                                                        <div class="iconsBG top8 share_front_record"   <?php echo $shareRecordHidden; ?> >
                                                            <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>" user-type1="public" thumb-id-val="<?php echo RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id']; ?>" type="people_record_share"></div>
                                                        </div>
                                                        <div class="iconsBG_two" <?php echo $shareRecordHidden; ?>>
                                                        <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="public"><div class="imgAddScrapbook"></div></div>
                                                        </div>
                                                        <a class="custom-link" href="item-details.html?q=<?php echo encryptQueryString('folder_id='.$itemDataArray['item_id'] .'&flg=people'); ?>">
                                                            <h2>Open</h2> 
                                                            <?php if(isset($itemDataArray['child_count'])){ ?>
                                                            <span><?php echo $itemDataArray['child_count']; ?></span> 
                                                            <?php } ?>
                                                        </a>
                                                    </div>
													

                        <?php } ?>
                                            </div>
                                            <h6 class="recordHead"><?php echo urldecode($itemDataArray['item_title']); ?>
							<?php if($showEbayLogo == 'yes' && $ebayCheckCondition != 'N'){ ?><img src="<?php echo IMAGE_PATH.'ebay-right-now.png' ?>" alt="" > <?php } ?>
								</h6>
                                        </td>
                                    </tr>
    
    
    <?php  }  } ?>