<div>
    <input type="hidden" name="is_scrapbook_item" id="is_scrapbook_item" value="<?php echo $scrapbookItem; ?>" />
    <input type="hidden" name="group_item_type" id="group_item_type" value="<?php echo isset(end($treeDataArray)['item_type']) ? end($treeDataArray)['item_type'] : ''; ?>" >
    <ul class="<?php echo $beardcrumb_class; ?>" id="listliheaddata">
        <li><a href="index.html"><span class="glyphicon glyphicon-home" aria-hidden="true"></span><?php echo $arrow_img; ?></a></li>
        <?php
        $arrayKeys = array_keys($treeDataArray);
        foreach ($treeDataArray as $key => $treeData) {
            ?>
            <?php
            $itemDisTitle = $treeData['item_title'];
            if ($treeData['item_type'] == 'AG') {
                ?>
                <li data-title="<?php echo $treeData['item_title']; ?>" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="society.html?q=<?php echo encryptQueryString('folder-id=' . $treeData['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>"><?php echo $treeData['item_title']; ?> Home <?php echo $arrow_img; ?></a></li>
                <?php
                $itemDisTitle = 'Archives';
            }
            if (end($arrayKeys) == $key) {
                ?> 
                <li data-title="<?php echo $treeData['item_title']; ?>" data-folder-id="<?php echo $treeData['item_id']; ?>" table-page-id = "<?php echo $treeData['item_id']; ?>"><a href="javascript:void(0);"> <?php echo $itemDisTitle; ?></a></li>
                <?php
            } else {
                if ($itemDisTitle != 'Scrapbooks') {
                    ?>
                    <li data-title="<?php echo $treeData['item_title']; ?>" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $treeData['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>"><?php echo $itemDisTitle . $arrow_img; ?></a></li>
                    <?php
                }
            }
        }
        ?>
    </ul>
</div>
<div class="clearfix"></div>
<?php if ($itemData['item_type'] != 'IT' && $_SESSION['aib']['user_data']['user_type'] == 'A' && $treeDataArray[1]['item_id'] != $_SESSION['aib']['user_data']['user_top_folder']) { ?>
    <a href="javascript:void(0);" class="connect-to-society single-item" connecting-item-id="<?php echo $itemData['item_id'] ?>"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Connect to <?php echo $displayNameArray[$itemData['item_type']]; ?></a>
    <a href="javascript:void(0);" class="connect-to-society multiple-item connect-with-multiple-records" connecting-item-id=""><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Connect to record(s)</a>
    <input type="hidden" name="selected_item_id" id="selected_item_id" value="">
<?php } ?>
<?php if (count($apiResponse['info']['records']) > 0) { ?>
    <?php if ($itemData['item_type'] == 'SG') { ?>
        <ul class="tabs widthAuto">
            <li class="tab-link current" data-tab="tab-1" id="record-tab">Records</li>
            <li class="tab-link" data-tab="tab-2" id="sub-group-tab" style="display:<?php echo $subgroup_hide; ?> ">Sub Groups</li>
            <li class="tab-link historical_connection_tab" data-tab="tab-3" id="sub-group-tab">Historical Connection</li>
        </ul>
        <div id="tab-1" class="tab-content current box-shadow">
            <?php /*if (count($subGroup)) { ?>
                <a href="javascript:void(0);" id="browse_society_subgroup" class="switch-tab" active-tab-id="sub-group-tab">View Sub Groups - <?php echo count($subGroup); ?></a>
            <?php }*/ ?>
            <table id="myTable" class="custum_tbl custom_css" width="100%" cellpadding="0" cellspacing="0">  
                <thead>  
                    <tr>  
                        <th>Forms Id</th>   
                    </tr>  
                </thead>  
                <tbody class="society_ast_sub_list widthB sub_society_record_more_load">  
                <div class="clearfix"></div>
                <div class="main">
                    <?php
                    foreach ($apiResponse['info']['records'] as $itemDataArray) {
                        $count_display = '';
                        if ($itemDataArray['item_type'] != 'SG') {
                            $showEbayLogo = 'no';
                            $total_day = '';
                            if (isset($itemDataArray['properties']['ebay_url']) && $itemDataArray['properties']['ebay_url'] != '') {
                                $record_date = strtotime($itemDataArray['properties']['ebay_record_date']);
                                $total_day = round((time() - $record_date) / (60 * 60 * 24));
                                if ($total_day <= 7) {
                                    $showEbayLogo = 'yes';
                                }
                            }
                            $count_display = 'Item(s)';
                            ?>
                            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                                <?php if (isset($itemDataArray['stp_url']) && $itemDataArray['stp_url'] != '' && $itemDataArray['link_type'] == 'A') { ?>
                                    <td>
                                        <div class="view view-first <?php
                                        if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                            if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                                echo 'active';
                                            }
                                        }
                                        ?>">
                                            <img src='<?php echo addhttp($itemDataArray["stp_thumb"]) ?>' alt="Stp Image" />
                                            <div class="mask">
                                                <div class="iconsBG top8" id="share_front_record" <?php echo $shareRecordHidden; ?> >
                                                    <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>"  user-type1="society" thumb-id-val="<?php echo addhttp($itemDataArray["stp_thumb"]); ?>"></div>
                                                </div>
                                                <?php if ($_SESSION['aib']['user_data']['user_type'] == 'A' && $treeDataArray[1]['item_id'] != $_SESSION['aib']['user_data']['user_top_folder']) { ?>
                                                    <div class="iconsBG top45 connect_with_multiple_records" data-record-id="<?php echo $itemDataArray['item_id']; ?>" <?php echo $shareRecordHidden; ?> >
                                                        <input type="checkbox" class="multi-record-checkbox" value="<?php echo $itemDataArray['item_id']; ?>">
                                                    </div>
                                                <?php } ?>
                                                <div class="iconsBG_two"  <?php echo $shareRecordHidden; ?>>
                                                    <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="society"><div class="imgAddScrapbook"></div></div>
                                                </div>
                                                <?php if (isset($itemDataArray['related_count']) && $itemDataArray['related_count'] > 0) { ?>
                                                    <div class="iconsBG_two bottom10">
                                                        <div class="" title="Related Item" record_id="<?php echo $itemDataArray['item_id']; ?>" ><div class="imgRelatedItems"></div></div>
                                                    </div>
                                                <?php } ?>
                                                <a class="custom-link" href="<?php echo addhttp($itemDataArray['stp_url']); ?>" target="_blank">
                                                    <h2>Open</h2>
                                                </a> 
                                            </div>
                                        </div>
                                        <h6 class="recordHead">
                                            <?php
                                            $dateValue = urldecode($itemDataArray['item_title']);
                                            $dateValueArray = explode(',', $dateValue);
                                            echo $dateValueArray[0] . '<br>' . $dateValueArray[1];
                                            ?>
                                            <?php if ($showEbayLogo == 'yes' && $ebayCheckCondition != 'N') { ?><img src="<?php echo IMAGE_PATH . 'ebay-right-now.png' ?>" alt="" > <?php } ?>
                                        </h6>
                                    </td>
                                <?php } else { ?>
                                    <td class="text-center">
                                        <div class="view view-first <?php
                                        if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                            if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                                echo 'active';
                                            }
                                        }
                                        $thumbUrl = RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id'];
                                        if ($itemDataArray['item_type'] == 'IT') {
                                            $thumbUrl = THUMB_URL . '?id=' . $itemDataArray['tn_file_id'];
                                        }
                                        ?>">
                                            <img src='<?php echo $thumbUrl; ?>' alt="Thumb Image"/>
                                            <div class="mask">
                                                <?php if ($itemDataArray['item_type'] == 'RE') { ?>
                                                    <div class="iconsBG top8" id="share_front_record" <?php echo $shareRecordHidden; ?>>
                                                        <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>"  user-type1="society" thumb-id-val="<?php echo RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id']; ?>"></div>
                                                    </div>
                                                    <?php if ($_SESSION['aib']['user_data']['user_type'] == 'A' && $treeDataArray[1]['item_id'] != $_SESSION['aib']['user_data']['user_top_folder']) { ?>
                                                        <div class="iconsBG top45 connect_with_multiple_records" data-record-id="<?php echo $itemDataArray['item_id']; ?>" <?php echo $shareRecordHidden; ?> >
                                                            <input type="checkbox" class="multi-record-checkbox" value="<?php echo $itemDataArray['item_id']; ?>">
                                                        </div>
                                                    <?php } ?>
                                                    <div class="iconsBG_two"  <?php echo $shareRecordHidden; ?>>
                                                        <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="society"><div class="imgAddScrapbook"></div></div>
                                                    </div>
                                                <?php } ?>
                                                <?php if (isset($itemDataArray['related_count']) && $itemDataArray['related_count'] > 0) { ?>
                                                    <div class="iconsBG_two bottom10">
                                                        <div class="" title="Related Item" record_id="<?php echo $itemDataArray['item_id']; ?>" ><div class="imgRelatedItems"></div></div>
                                                    </div>
                                                <?php } ?>
                                                <!-- Fix start for Issue ID 0002427 on 14-Feb-2025 -->
                                                <a class="custom-link details-page-url url-append" item-id="<?php echo $itemDataArray['item_id']; ?>" child-count="<?php echo $itemDataArray['child_count']; ?>" item-type="<?php echo $itemDataArray['item_type']; ?>" item-parent="<?php echo $itemDataArray['link_properties']['item_parent']; ?>" href="javascript:pageMoveToItemDetail(<?php echo $itemDataArray['item_id']; ?>,<?php echo $itemDataArray['child_count']; ?>,'<?php echo $itemDataArray['item_type'] ?>', <?php echo $itemDataArray['link_properties']['item_parent'] ?>);">
                                                <!-- Fix end for Issue ID 0002427 on 14-Feb-2025 -->
                                                    <h2 style="display:<?php
                                                    if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                                        if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                                            echo 'none';
                                                        } else {
                                                            echo'block';
                                                        }
                                                    }
                                                    ?>">Open</h2>
                                                        <?php /* if($itemDataArray['item_type'] == 'RE'){ ?>
                                                          <span><?php echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : ''; ?></span>
                                                          <?php } */ ?>
                                                </a>
                                            </div>
                                        </div>
                                        <h6 class="recordHead"><?php echo urldecode($itemDataArray['item_title']); ?> <?php if ($showEbayLogo == 'yes' && $ebayCheckCondition != 'N') { ?><img src="<?php echo IMAGE_PATH . 'ebay-right-now.png' ?>" alt="" > <?php } ?></h6>
                                        <?php if ($itemDataArray['item_type'] == 'RE') { ?>
                                            <span><?php echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : ''; ?></span> 
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </div>
                </tbody>  
            </table>
            <div class="clearfix"></div>
            <div class="loadMoreBtn load_more_data load-more-record-data" hidden><button class="btn search-button load_more_list_data_val" >Load More</button></div>
            <?php if ($subgroup_hide != 'none') { ?>
                <div id="view-info-text-sub-group" style="display: none;">To view more records, Please click on Sub-Group tab</div>
            <?php } ?>
        </div>
        <div id="tab-2" class="tab-content box-shadow">
            <?php /*if (count($apiResponse['info']['records']) > 0) { ?>
                <a href="javascript:void(0);" id="browse_society_records" class="switch-tab" active-tab-id="record-tab">View Records - <?php echo $totalRecords; ?></a>
            <?php }*/ ?>
            <table id="myTableSG" class="custum_tbl custom_css" width="100%" cellpadding="0" cellspacing="0">  
                <thead>  
                    <tr>  
                        <th>Forms Id</th>   
                    </tr>  
                </thead>  
                <tbody class="society_ast_sub_list">  
                <div class="clearfix"></div>
                <div class="main">
                    <?php
                    foreach ($apiResponse['info']['records'] as $itemDataArray) {
                        $count_display = '';
                        if ($itemDataArray['item_type'] == 'SG') {
                            $count_display = 'Rec(s)';
                            ?>
                            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                                <td class="organizations sub-groups">
                                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
                                    <label>
                                        <?php
                                        echo ($count_display != '') ? $itemDataArray['child_count'] . '   ' . $count_display : '';
                                        if (isset($itemDataArray['sg_count']) && $itemDataArray['sg_count'] > 0) {
                                            echo '<br> &nbsp &nbsp ' . $itemDataArray['sg_count'] . '  Sub-Group(s)';
                                        }
                                        ?>
                                    </label>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </div>
                </tbody>  
            </table>
        </div>
        <div id="tab-3" class="tab-content box-shadow"><div id="historical_connection_data"></div></div>
    <?php } elseif ($itemData['item_type'] == 'AG') { ?>
        <ul class="tabs widthAuto">
            <li class="tab-link current" data-tab="tab-1" id="archive-tab">Archives </li>
            <li class="tab-link" data-tab="tab-2" id="scrapbook-tab" style="display: <?php echo $hideScrapbook; ?>">Scrapbooks <span class="glyphicon glyphicon-menu-down arrowDownTab" aria-hidden="true"></span></li>
            <li class="tab-link historical_connection_tab" data-tab="tab-3" id="historical-tab">Historical Connection <span class="glyphicon glyphicon-menu-down arrowDownTab" aria-hidden="true"></span></li>
        </ul>
        <div id="tab-1" class="tab-content current box-shadow">
            <?php /*if (isset($societyScrapbookListing) && count($societyScrapbookListing) > 0) { ?>
                <a href="javascript:void(0);" id="browse_society_scrapbook" class="switch-tab" active-tab-id="scrapbook-tab">View Scrapbooks - <?php echo count($societyScrapbookListing); ?></a>
            <?php }*/ ?>
            <table id="myTable" class="custum_tbl" width="100%" cellpadding="0" cellspacing="0">  
                <thead>  
                    <tr>  
                        <th>Forms Id</th>
                    </tr>  
                </thead>  
                <tbody class="society_sub_list  sub_society_more_load">  
                    <?php foreach ($apiResponse['info']['records'] as $itemDataArray) { ?>
                        <tr id="<?php echo $itemDataArray['item_id']; ?>">
                            <td class="organizations">
                                <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
                                <?php
                                $count_display = '';
                                if ($itemDataArray['item_type'] == 'AR') {
                                    $count_display = 'Collection(s)';
                                } elseif ($itemDataArray['item_type'] == 'CO') {
                                    $count_display = 'Sub-Group(s)';
                                } elseif ($itemDataArray['item_type'] == 'SG') {
                                    $count_display = 'Rec(s)';
                                } elseif ($itemDataArray['item_type'] == 'RE') {
                                    $count_display = 'Item(s)';
                                }
                                ?> 
                                <div class="clearfix"></div>
                                <label style="height:10px;">
                                    <?php
                                    echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : '';
                                    if (isset($itemDataArray['sg_count']) && $itemDataArray['sg_count'] > 0) {
                                        echo '<br> &nbsp &nbsp ' . $itemDataArray['sg_count'] . '  Sub-Groups';
                                    }
                                    ?>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>  
            </table>
            <?php echo $load_more_button; ?>
        </div>
        <div id="tab-2" class="tab-content box-shadow">
            <?php /*if (isset($apiResponse['info']['records']) && count($apiResponse['info']['records']) > 0) { ?>
                <a href="javascript:void(0);" id="browse_society_archive" class="switch-tab" active-tab-id="archive-tab">View Archive - <?php echo $totalArchive; ?></a>
            <?php }*/ ?>
            <table id="myTable" class="custum_tbl" width="100%" cellpadding="0" cellspacing="0">  
                <thead>  
                    <tr>  
                        <th>Forms Id</th>
                    </tr>  
                </thead>  
                <tbody class="society_sub_list  sub_society_more_load_scrapbook paddingtr10">
                    <?php foreach ($societyScrapbookListing as $itemDataArray) { ?>
                        <tr id="<?php echo $itemDataArray['item_id']; ?>">
                            <td class="organizations hoverScrapbook">
                                <?php if (isset($itemDataArray['show_copy_link']) && $itemDataArray['show_copy_link'] == 'yes') { ?><div class="copyScrapbook copy_scrapbook_to_own_scrapbook showScrapbook" title="Copy scrapbook." data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-parent-id="<?php echo $itemDataArray['item_parent']; ?>" data-item-name="<?php echo $itemDataArray['item_title']; ?>" data-item-user-id="<?php echo $itemDataArray['_debug']['item_user_id']; ?>"><div class="copyScrapbookImg"></div></div> <?php } ?>
                                <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
                                <?php
                                $count_display = '';
                                if ($itemDataArray['item_type'] == 'AR') {
                                    $count_display = 'Collection(s)';
                                } elseif ($itemDataArray['item_type'] == 'CO') {
                                    $count_display = 'Sub-Group(s)';
                                } elseif ($itemDataArray['item_type'] == 'SG') {
                                    $count_display = 'Rec(s)';
                                } elseif ($itemDataArray['item_type'] == 'RE') {
                                    $count_display = 'Item(s)';
                                }
                                ?> 
                                <div class="clearfix"></div>
                                <label style="height:10px;">
                                    <?php
                                    echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : '';
                                    if (isset($itemDataArray['sg_count']) && $itemDataArray['sg_count'] > 0) {
                                        echo '<br> &nbsp &nbsp ' . $itemDataArray['sg_count'] . '  Sub-Groups';
                                    }
                                    ?>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div id="tab-3" class="tab-content box-shadow"><div id="historical_connection_data"></div></div>
    <?php } else { ?>
        </div>
        </div>
        <div class="clearfix"></div>
        <?php
        $tab_content = '';
        //echo '<pre>';print_r($apiResponse['info']['records']);echo '</pre>';
        if ($apiResponse['info']['records'][0]['item_parent'] != '1') {
            ?>
            <ul class="tabs widthAuto">
                <li class="tab-link current" data-tab="tab-1" id="archive-tab">Archives </li>
                <li class="tab-link historical_connection_tab" data-tab="tab-2" id="historical-tab">Historical Connection </li>
            </ul>
            <?php
            $tab_content = 'tab-content';
        }
        ?>
        <div id="tab-1" class="current box-shadow <?php echo $tab_content; ?>">
            <?php if ($itemData['item_id'] != 1) { ?>
                <table id="myTable" class="custum_tbl" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th>Forms Id</th>
                        </tr>  
                    </thead>  
                    <tbody class="society_sub_list  sub_society_more_load">  
                        <?php foreach ($apiResponse['info']['records'] as $itemDataArray) { ?>
                            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                                <td class="organizations">
                                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
                                    <?php
                                    $count_display = '';
                                    if ($itemDataArray['item_type'] == 'AR') {
                                        $count_display = 'Collection(s)';
                                    } elseif ($itemDataArray['item_type'] == 'CO') {
                                        $count_display = 'Sub-Group(s)';
                                    } elseif ($itemDataArray['item_type'] == 'SG') {
                                        $count_display = 'Rec(s)';
                                    } elseif ($itemDataArray['item_type'] == 'RE') {
                                        $count_display = 'Item(s)';
                                    }
                                    ?> 
                                    <div class="clearfix"></div>
                                    <label>
                                        <?php
                                        echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : '';
                                        if (isset($itemDataArray['sg_count']) && $itemDataArray['sg_count'] > 0) {
                                            echo '<br> &nbsp &nbsp ' . $itemDataArray['sg_count'] . '  Sub-Groups';
                                        }
                                        ?>
                                    </label>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>  
                </table> 
            <?php echo $load_more_button; ?>
            <?php } else { ?>
                <table id="myTable" class="custum_tbl societyListing" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th>Forms Id</th>   
                        </tr>  
                    </thead>  
                    <tbody class="society_list societyListing add_society_more_load">  
                        <?php
                        foreach ($apiResponse['info']['records'] as $itemDataArray) {
                            $customTemp = isset($itemDataArray['properties']['custom_template']) ? $itemDataArray['properties']['custom_template'] : '';
                            $customTempLink = '&society_template=default';
                            if ($customTemp != '') {
                                $customTempLink = '&society_template=' . $customTemp;
                            }
                            ?>
                            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                                <td>
                                    <section id="grid" class="grid clearfix">		   
                                        <a class="" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="society.html?q=<?php echo encryptQueryString('folder-id=' . $itemDataArray['item_id'] . $customTempLink); ?>" data-path-hover="m 180,120.57627 -180,0 L 0,0 180,0 z">
                                            <figure>
                                                <?php
                                                $image_name = (isset($itemDataArray['property_list']['archive_group_thumb']) && $itemDataArray['property_list']['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $itemDataArray['property_list']['archive_group_thumb'])) ? ARCHIVE_IMAGE . $itemDataArray['property_list']['archive_group_thumb'] : IMAGE_PATH . 'no-image-blank.png';
                                                ?>
                                                <img src="<?php echo $image_name; ?>" alt="Historical Image" />
                                                <svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 280,260 0,218 0,0 180,0 z"/></svg>
                                                <figcaption>
                                                    <h2><?php echo $itemDataArray['item_title']; ?></h2>
                                                    <!--<p><?php echo $itemDataArray['item_title']; ?></p>-->
                                                    <button>View</button>
                                                </figcaption>
                                            </figure>
                                        </a>
                                    </section> 
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>  
                </table> 
            <?php echo $load_more_button; ?>
            <?php } ?>
        </div>
        <?php if ($apiResponse['info']['records'][0]['item_parent'] != '1') { ?>
            <div id="tab-2" class="tab-content box-shadow"><div id="historical_connection_data"></div></div>
        <?php } ?>
        <?php
    }
} else {
    echo '<div class="text-center">No items found.</div>';
}
?>    
<div class="clearfix"></div>
<!--div class="loadMoreBtn load_more_data load-more-list-data-val" hidden><button class="btn search-button load_more_list_data_val" id="load-more-data-button">Load More</button></div-->
<div class="clearfix"></div>  
<!--div class="historical_connection">
    <div class="historical_head">Historical Connections</div>
    <div id="historical_connection_data"></div>
</div--> 
<div class="clearfix"></div>  
<!--<div id="more_data_loader" class="publicListLoading text-center" hidden><img class="loaderImgTable" src="public/images/loading.gif" alt="Loading..."></div>-->     
<input type="hidden" class="pagenum" name="satar_result" id="satar_result" value="<?php echo $start_result; ?>" />
<input type="hidden" id="apiResCount" name="apiResCount" value="<?php echo $countArchive; ?>">
<input type="hidden" name="publicListcount" id="publicListcount" value="<?php echo PUBLIC_COUNT_PER_PAGE; ?>">
<input type="hidden" name="scroll-loading-more-content" id="scroll-loading-more-content" value="no" />