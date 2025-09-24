<?php

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

$countVal = count($treeDataArray);
$archive_id = (isset($treeDataArray[1])) ? $treeDataArray[1]['item_id'] : '';
if ($countVal < 2) {
    echo '<script>$("#filter_by_tag_section").hide();</script>';
}
$show_scrapbook = (strpos($_SERVER['HTTP_REFERER'], 'show_scrapbook') !== false) ? 'show' : 'hide';
$recorHide = 'inline-block';
$subgroupHide = 'inline-block';
if (empty($firstTimeDataArray['records'])) {
    $recorHide = 'none';
}
if (empty($firstTimeDataArray['sub_groups'])) {
    $subgroupHide = 'none';
}
$shareRecordHidden = '';
$showLink = array('U', 'A');
if (isset($_SESSION['aib']['user_data']) && !in_array($_SESSION['aib']['user_data']['user_type'], $showLink)) {
    $shareRecordHidden = 'hidden';
}
//echo'<pre>';
//print_r($apiResponse['info']['records']);
?>
<style>
    #myTableSG_filter{ display:none;}
    .dataTables_length{ display:none;}
    #myTableSG_info{display:none;}
    .ui-tabs-vertical .ui-tabs-nav li{padding: 0px !important;height: 158px;}
    .ui-tabs-vertical .ui-tabs-nav li a{position:absolute;top:50%;left:50%;transform:translateX(-50%) translateY(-50%);}
    .ui-tabs-vertical .ui-tabs-panel{height: 473px;}
</style>
<div>
    <input type="hidden" name="group_item_type" id="group_item_type" value="<?php echo isset(end($treeDataArray)['item_type']) ? end($treeDataArray)['item_type'] : ''; ?>" >

    <ul class="listing">
        <li><a href="index.html"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
        <?php
        $arrayKeys = array_keys($treeDataArray);
        foreach ($treeDataArray as $key => $treeData) {

            $treeData['item_title'] = ($treeData['item_title'] == '_STDUSER') ? 'USER' : $treeData['item_title'];
            if ($treeData['item_type'] == 'CO' || $treeData['item_type'] == 'AR') {
                ?>
                <li data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="/people_profile.html?q=<?php echo encryptQueryString('folder-id=' . $treeData['item_id']); ?>"><?php echo $treeData['item_title'] . ' Home'; ?></a></li>
                <?php
                $treeData['item_title'] = 'Archives';
            }
            if ($treeData['item_title'] != 'Scrapbooks') {
                ?>
                <?php if (end($arrayKeys) == $key) { ?> 
                    <li data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="javascript:void(0);"><?php
                            if ($treeData['item_title'] == 'USER') {
                                echo $treeData['item_title'] . ' LIST';
                            } else {
                                echo $treeData['item_title'];
                            }
                            ?></a></li>
                <?php } else { ?>
                    <li  class="getItemDataByFolderId" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="people.html?q=<?php echo encryptQueryString('folder_id=' . $treeData['item_id']); ?>"><?php
                            if ($treeData['item_title'] == 'USER') {
                                echo $treeData['item_title'] . ' LIST';
                            } else {
                                echo $treeData['item_title'];
                            }
                            ?></a></li>
                    <?php
                }
            }
        }
        ?>
    </ul>
</div>
<div class="clearfix"></div>
<?php if (count($apiResponse['info']['records']) > 0) { ?>
    <?php if ($itemData['item_type'] == 'SG') { ?>
        <div id="tabs" class="tab_archive ui-tabs-vertical">
            <ul class="ui-tabs-nav">
                <li id="record-tab"><a href="#tabs-1">Records (<span id="total_c"></span>)</a></li>
            </ul>
            <div id="tabs-1" class="ui-tabs-panel">
                <input type="hidden" name="people_sub_group" id="people_sub_group" value="<?php echo $itemData['item_type']; ?>" >
                <table id="myTable" class="custum_tbl custom_css " width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th>Forms Id</th>   
                        </tr>  
                    </thead>  
                    <tbody class="society_ast_sub_list widthA people_sub_group_list_record">  
                    <div class="clearfix"></div>
                    <div class="main">
                        <?php
                        $total_c = 0;
                        foreach ($apiResponse['info']['records'] as $itemDataArray) {
                            if ($itemDataArray['scrapbook_item'] == 'Y') {
                                $flg = '&flg=scrapbook';
                                $reference = '&item_refrence_id=' . $itemDataArray['item_parent_refrence_id'];
                                $itemTitle = $itemDataArray['scrapbook_title'];
                            } else {
                                $flg = '&flg=people';
                                $reference = '';
                                $itemTitle = urldecode($itemDataArray['item_title']);
                            }
                            $THUMBURL = THUMB_URL;
                            if ($itemDataArray['item_type'] == 'RE')
                                $THUMBURL = RECORD_THUMB_URL;

                            $showEbayLogo = 'no';
                            $total_day = '';
                            if (isset($itemDataArray['properties']['ebay_url']) && $itemDataArray['properties']['ebay_url'] != '') {
                                $record_date = strtotime($itemDataArray['properties']['ebay_record_date']);
                                $total_day = round((time() - $record_date) / (60 * 60 * 24));
                                if ($total_day <= 7) {
                                    $showEbayLogo = 'yes';
                                }
                            }
                            ?>
                            <tr>
                                <td class="text-center">
                                    <div class="view view-first <?php
                                    if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                        if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                            echo 'active';
                                        }
                                    }
                                    ?>">
                                             <?php
                                             if (isset($itemDataArray['item_parent_id']) && $itemDataArray['item_parent_id'] != '') {
                                                 $itemHref = "item-details.html?q=" . encryptQueryString('folder_id=' . $itemDataArray['item_parent_id'] . '&itemId=' . $itemDataArray['item_id'] . $flg . $reference);
                                                 $target = 'class="custom-link"';

                                                 if ($itemDataArray['final_deref_stp_url'] != '') {
                                                     $itemHref = addhttp($itemDataArray['final_deref_stp_url']);
                                                     $target = 'target="_blank" ';
                                                 }
                                                 ?>
                                                 <?php
                                                 if (isset($itemDataArray['final_deref_stp_thumb']) && $itemDataArray['final_deref_stp_thumb'] != '') {
                                                     $thoumb = addhttp($itemDataArray["final_deref_stp_thumb"]);
                                                     ?>
                                                <img src='<?php echo addhttp($itemDataArray["final_deref_stp_thumb"]) ?>' alt="Stp Image" />
                                                <?php
                                            } else if (isset($itemDataArray['tn_file_id']) && $itemDataArray['tn_file_id'] != '') {
                                                $thoumb = $THUMBURL . '?id=' . $itemDataArray['tn_file_id'];
                                                ?>
                                                <img src="<?php echo $THUMBURL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="Thumb Image"/>
                                                <?php
                                            } else {
                                                $thoumb = $THUMBURL . '?id=' . $itemDataArray['item_id'];
                                                ?> 
                                                <img src="<?php echo $THUMBURL . '?id=' . $itemDataArray['item_id']; ?>" alt="Thumb Image"/>
                                            <?php } ?>
                                            <div class="mask">
                                                <div class="iconsBG top8 share_front_record"  <?php echo $shareRecordHidden; ?> >
                                                    <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>" user-type1="public"  thumb-id-val="<?php echo $thoumb; ?>" type="scrapbook_record_share" ></div>
                                                </div>
                                                <?php if ($_SESSION['aib']['user_data']['user_type'] != 'A') { ?>
                                                    <div class="iconsBG_two" <?php echo $shareRecordHidden; ?>>
                                                        <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="public"><div class="imgAddScrapbook"></div></div>
                                                    </div>
                                                <?php } ?>
                                                <a  <?php echo $target; ?> href="<?php echo $itemHref; ?>">
                                                    <h2>Open</h2> 
                                                    <?php if (isset($itemDataArray['child_count'])) { ?>
                                                        <span><?php echo $itemDataArray['child_count']; ?></span> 
                                                    <?php } ?>
                                                </a>
                                            </div>

                                            <?php
                                        } else {

                                            $itemHref = "item-details.html?q=" . encryptQueryString('folder_id=' . $itemDataArray['item_id'] . $flg . $reference);
                                            $target = 'class="custom-link"';
                                            if ($itemDataArray['final_deref_stp_url'] != '') {
                                                $itemHref = addhttp($itemDataArray['final_deref_stp_url']);
                                                $target = 'target="_blank" ';
                                            }
                                            ?>
                                            <?php
                                            if (isset($itemDataArray['final_deref_stp_thumb']) && $itemDataArray['final_deref_stp_thumb'] != '') {
                                                $thoumb = addhttp($itemDataArray["final_deref_stp_thumb"]);
                                                ?>
                                                <img src='<?php echo addhttp($itemDataArray["final_deref_stp_thumb"]); ?>' alt="Stp Image" />
                                                <?php
                                            } else if (isset($itemDataArray['tn_file_id']) && $itemDataArray['tn_file_id'] != '') {
                                                $thoumb = $THUMBURL . '?id=' . $itemDataArray['tn_file_id'];
                                                ?>
                                                <img src="<?php echo $THUMBURL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="Thumb Image"/>
                                                <?php
                                            } else {
                                                $thoumb = $THUMBURL . '?id=' . $itemDataArray['item_id'];
                                                ?> 
                                                <img src="<?php echo $THUMBURL . '?id=' . $itemDataArray['item_id']; ?>" alt="Thumb Image"/>
                                            <?php } ?>

                                            <div class="mask">
                                                <div class="iconsBG top8 share_front_record"  <?php echo $shareRecordHidden; ?> >
                                                    <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>" user-type1="public" thumb-id-val="<?php echo $thoumb; ?>"></div>
                                                </div>
                                                <?php if ($_SESSION['aib']['user_data']['user_type'] != 'A') { ?>
                                                    <div class="iconsBG_two" <?php echo $shareRecordHidden; ?>>
                                                        <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="public"><div class="imgAddScrapbook"></div></div>
                                                    </div>
                                                <?php } ?>
                                                <a <?php echo $target; ?>   href="<?php echo $itemHref; ?>">
                                                    <h2>Open</h2>
                                                    <?php /* if (isset($itemDataArray['child_count'])) { ?>
                                                      <span><?php echo $itemDataArray['child_count']; ?></span>
                                                      <?php } */ ?>
                                                </a>
                                            </div>

                                        <?php } ?>
                                    </div>
                                    <!--<h6 class="recordHead"><?php // echo $itemTitle ;      ?></h6>-->
                                    <h6 class="recordHead"><?php echo urldecode($itemDataArray['item_title']); ?>
                                        <?php if ($showEbayLogo == 'yes' && $ebayCheckCondition != 'N') { ?><img src="<?php echo IMAGE_PATH . 'ebay-right-now.png' ?>" alt="" > <?php } ?>
                                    </h6>
                                    <?php if (isset($itemDataArray['child_count'])) { ?>
                                        <span><?php echo $itemDataArray['child_count']; ?></span>
                                    <?php }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $total_c++;
                        }
                        ?>
                    </div>
                    </tbody>  
                </table>
            </div>
        </div>
    <?php } else {
        ?>
        <div class="clearfix"></div>
        <?php if ($itemData['item_id'] != PUBLIC_USER_ROOT) { ?>
            <?php if ($currentParentId == PUBLIC_USER_ROOT) { ?>
                <div id="tabs" class="tab_archive ui-tabs-vertical">
                    <ul class="ui-tabs-nav">
                        <li id="record-tab"><a href="#tabs-1">Records (<?php echo $totalRecords; ?>)</a></li> <!-- style="display: <?php //echo $recorHide; ?>"-->
                        <li id="sub-group-tab" style="display:<?php echo $subgroupHide; ?> "><a href="#tabs-2">Sub Groups (<?php echo count(($firstTimeDataArray['sub_groups'])); ?>)</a></li>
                        <li  id="scrapbook-group-tab"><a href="#tabs-3">Scrapbook (<?php echo count(($firstTimeDataArray['scrapbook'])); ?>)</a></li>
                    </ul>
                    <div id="tabs-1" class="ui-tabs-panel">
                        <table id="myTable" class="custum_tbl custom_css" width="100%" cellpadding="0" cellspacing="0">  
                            <thead>  
                                <tr>  
                                    <th>Forms Id</th>   
                                </tr>  
                            </thead>  
                            <tbody class="public_sub_list widthA  people_record_list society_ast_sub_list widthB sub_society_record_more_load">
                            <div class="clearfix"></div>
                            <div class="main">
                                <?php
                                if (isset($firstTimeDataArray['records']) && !empty($firstTimeDataArray['records'])) {
                                    foreach ($firstTimeDataArray['records'] as $itemDataArray) {
                                        $showEbayLogo = 'no';
                                        $total_day = '';
                                        if (isset($itemDataArray['properties']['ebay_url']) && $itemDataArray['properties']['ebay_url'] != '') {
                                            $record_date = strtotime($itemDataArray['properties']['ebay_record_date']);
                                            $total_day = round((time() - $record_date) / (60 * 60 * 24));
                                            if ($total_day <= 7) {
                                                $showEbayLogo = 'yes';
                                            }
                                        }
                                        ?>
                                        <tr style="width: 14%;">
                                            <td class="text-center">
                                                <div class="view view-first <?php
                                                if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                                    if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                                        echo 'active';
                                                    }
                                                }
                                                ?>">
                                                         <?php if (isset($itemDataArray['item_parent_id']) && $itemDataArray['item_parent_id'] != '') { ?>
                                                        <img src="<?php echo THUMB_URL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="Thumb Image"/>
                                                        <a class="custom-link" href="item-details.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_parent_id'] . '&itemId=' . $itemDataArray['item_id'] . '&flg=people'); ?>">
                                                            <div class="mask">
                                                                <h2>Open</h2>
                                                                <?php if (isset($itemDataArray['child_count'])) { ?>
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
                                                            <?php if ($_SESSION['aib']['user_data']['user_type'] != 'A') { ?>
                                                                <div class="iconsBG_two" <?php echo $shareRecordHidden; ?>>
                                                                    <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="public"><div class="imgAddScrapbook"></div></div>
                                                                </div>
                                                            <?php } ?>
                                                            <a class="custom-link" href="item-details.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&flg=people'); ?>">
                                                                <h2>Open</h2> 
                                                                <?php /* if(isset($itemDataArray['child_count'])){ ?>
                                                                  <span><?php echo $itemDataArray['child_count']; ?></span>
                                                                  <?php } */ ?>
                                                            </a>
                                                        </div>


                                                    <?php } ?>
                                                </div>
                                                <h6 class="recordHead"><?php echo urldecode($itemDataArray['item_title']); ?>
                                                    <?php if ($showEbayLogo == 'yes' && $ebayCheckCondition != 'N') { ?><img src="<?php echo IMAGE_PATH . 'ebay-right-now.png' ?>" alt="" > <?php } ?>
                                                </h6>
                                                <?php if (isset($itemDataArray['child_count'])) { ?>
                                                    <span><?php echo $itemDataArray['child_count']; ?></span> 
                                                <?php }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            </tbody>
                        </table>
                        <div class="clearfix"></div>
                        <div class="loadMoreBtn load_more_data load-more-people-record" hidden><button class="btn search-button load_more_people_list_data" >Load More</button></div>
                    </div>
                    <div id="tabs-2" class="ui-tabs-panel">
                        <table id="myTableSG" class="custum_tbl custom_css" width="100%" cellpadding="0" cellspacing="0">  
                            <thead>  
                                <tr>  
                                    <th>Forms Id</th>   
                                </tr>  
                            </thead>  
                            <tbody class="public_sub_list">
                            <div class="clearfix"></div>
                            <div class="main">
                                <?php
                                if (isset($firstTimeDataArray['sub_groups']) && !empty($firstTimeDataArray['sub_groups'])) {
                                    foreach ($firstTimeDataArray['sub_groups'] as $itemDataArray) {
                                        ?>
                                        <tr>
                                            <td class="organizations">
                                                <a href="people.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id']); ?>"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
                                                <div class="clearfix"></div>
                                                <?php if (isset($itemDataArray['child_count'])) { ?>
                                                    <label><?php echo $itemDataArray['child_count']; ?></label>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            </tbody>
                        </table>
                    </div>
                    <div id="tabs-3" class="ui-tabs-panel">
                        <table id="myTableSC" class="custum_tbl custom_css" width="100%" cellpadding="0" cellspacing="0">  
                            <thead>  
                                <tr>  
                                    <th>Forms Id</th>   
                                </tr>  
                            </thead>  
                            <tbody class="public_sub_list widthC">
                            <div class="clearfix"></div>
                            <div class="main">
                                <?php
                                if (isset($firstTimeDataArray['scrapbook']) && !empty($firstTimeDataArray['scrapbook'])) {
                                    foreach ($firstTimeDataArray['scrapbook'] as $itemDataArray) {
                                        ?>
                                        <tr>
                                            <td class="organizations">
                                                <div class="positionRelative">
                                                    <?php if (isset($itemDataArray['show_copy_link']) && $itemDataArray['show_copy_link'] == 'yes') { ?><div class="copyScrapbook copy_scrapbook_to_own_scrapbook" title="Copy scrapbook." data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-parent-id="<?php echo $itemDataArray['item_parent']; ?>" data-item-name="<?php echo $itemDataArray['item_title']; ?>" data-item-user-id="<?php echo $itemDataArray['item_user_id']; ?>"><div class="copyScrapbookImg"></div></div><?php } ?>
                                                    <a href="people.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id']); ?>" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" title="<?php echo $itemDataArray['item_title']; ?>">
                                                        <span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?>
                                                    </a>
                                                </div>
                                                <div class="clearfix"></div>
                                                <?php if (isset($itemDataArray['child_count'])) { ?>
                                                    <label><?php echo $itemDataArray['child_count']; ?></label>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } else { ?>
                <table id="myTable" class="custum_tbl" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th>Forms Id</th>
                        </tr>  
                    </thead>  
                    <tbody class="society_sub_list widthD">  
                        <?php foreach ($apiResponse['info']['records'] as $itemDataArray) { ?>
                            <tr>
                                <td class="organizations">
                                    <a href="people.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id']); ?>"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
                                    <div class="clearfix"></div>
                                    <?php if (isset($itemDataArray['child_count'])) { ?>
                                        <label><?php echo $itemDataArray['child_count']; ?></label>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>  
                </table> 
            <?php } ?>
        <?php } else { ?>
            <table id="myTable" class="custum_tbl" width="100%" cellpadding="0" cellspacing="0">  
                <thead>  
                    <tr>  
                        <th>Forms Id</th>   
                    </tr>  
                </thead>  
                <tbody class="society_list add_people_more_load">  
                    <?php foreach ($apiResponse['info']['records'] as $itemDataArray) { ?>
                        <tr>
                            <td class="ch-grid">
                                <?php $image_name = (isset($itemDataArray['default_property']['archive_group_thumb']) && $itemDataArray['default_property']['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $itemDataArray['default_property']['archive_group_thumb'])) ? ARCHIVE_IMAGE . $itemDataArray['default_property']['archive_group_thumb'] : IMAGE_PATH . 'avatar-1.png'; ?>
                                <a title="<?php echo (isset($itemDataArray['item_title']) && $itemDataArray['item_title'] != '') ? $itemDataArray['item_title'] : 'Username'; ?>" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.html?q=<?php echo encryptQueryString('folder-id=' . $itemDataArray['item_id']); ?>">
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
                    <?php } ?>
                </tbody>  
            </table> 
            <!--        <div class="text-center"><button class="btn btn-primary public-load-more" <?php // echo $moreLoad;        ?> > Load more </button></div>-->
        <?php } ?>
        <?php
    }
} else {
    echo '<div class="text-center">No items found.</div>';
}
?>
<div class="clearfix"></div>
<div class="loadMoreBtn load_more_data load-more-people-list-data" hidden ><button class="btn search-button load_more_people_list_data" >Load More</button></div>
<div class="clearfix"></div>
<!--<div class="publicListLoading text-center" hidden><img class="loaderImgTable" src="public/images/loading.gif" alt="Loading..."></div>-->    
<input type="hidden" class="pagenum" name="start_page" id="start_page" value="<?php echo $stratPage; ?>" />
<input type="hidden" id="apiResCount" name="apiResCount" value="<?php echo (end($treeDataArray)['item_type'] == 'AR') ? $peopleRecordCount : $countArchive; ?>">
<input type="hidden" name="public_List_count_val" id="public_List_count_val" value="<?php echo PUBLIC_COUNT_PER_PAGE; ?>">
<input type="hidden" name="scroll-loading-more-content" id="scroll-loading-more-content" value="no" />
<script>
    var folderId = "<?php echo $folderId; ?>";
    $('#total_c').html('<?php echo $total_c; ?>');
    (function () {
        function init() {
            var speed = 250,
                    easing = mina.easeinout;
            [].slice.call(document.querySelectorAll('#grid > a')).forEach(function (el) {
                var s = Snap(el.querySelector('svg')), path = s.select('path'),
                        pathConfig = {
                            from: path.attr('d'),
                            to: el.getAttribute('data-path-hover')
                        };
                el.addEventListener('mouseenter', function () {
                    path.animate({'path': pathConfig.to}, speed, easing);
                });
                el.addEventListener('mouseleave', function () {
                    path.animate({'path': pathConfig.from}, speed, easing);
                });
            });
        }
        init();
    })();
    //Check for Show/Hide back button.
    $(document).ready(function () {
        $('.dataTables_paginate paging_simple_numbers').css('display', 'none');
        $('.load-more-people-list-data').show();
        $('.load-more-people-record').hide();
        if ($('#group_item_type').val() == 'AR') {
            $('.load-more-people-record').show();
            $('.load-more-people-list-data').hide();
        }
        if ('<?php echo PUBLIC_COUNT_PER_PAGE; ?>' >= parseInt($('#apiResCount').val())) {
            $('.load-more-people-list-data').hide();
            $('.load-more-people-record').hide();
        }
        if ($('.listing li').length > 2) {
            $('.content_archive_search').show();
        } else {
            $('.content_archive_search').hide();
        }
        //Anil changes for showing deefault tabs
        var archive_id = '<?php echo $archive_id; ?>';
        var tree_count = '<?php echo $countVal; ?>';
        setTimeout(function () {
            $('#archive_listing_select').val(archive_id);
        }, 2000);
        var show_scrapbook = '<?php echo $show_scrapbook; ?>';
        setTimeout(function () {
            var current_parent = '<?php echo $currentParentId; ?>';
            if (current_parent == PUBLIC_USER_ROOT) {
                var tab_first = $('#tab-1').text();
                var tab_second = $('#tab-2').text();
                var tab_three = $('#tab-3').text();
                if (tab_first.indexOf("No data available in table") !== -1) {
                    if (tab_second.indexOf("No data available in table") !== -1 && tab_three.indexOf("No data available in table") === -1) {
                        $('#scrapbook-group-tab').trigger('click');
                    }
                    if (tab_second.indexOf("No data available in table") === -1) {
                        $('#sub-group-tab').trigger('click');
                    }
                }
            }
        }, 10);
        if (show_scrapbook == 'show') {
            setTimeout(function () {
                $('#scrapbook-group-tab').trigger('click');
            }, 100);
        }
        //Anil code ends here

        $('.custom-link').each(function () {
            var link = $(this).attr('href');
            var thisObj = $(this);
            var previous_id_obj = JSON.parse($('#previous-item-id').val());
            var url_parts = link.split('?q=');
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'people_encrypted_string', queryString: url_parts[1], previous_id: previous_id_obj, current_item: $('#current-item-id').val()},
                success: function (response) {
                    thisObj.attr('href', 'item-details.html?q=' + response);
                },
                error: function () {

                }
            });
            //var final_link = link + '&previous=' + previous_id_obj + ',' + $('#current-item-id').val();
            //$(this).attr('href', final_link);
        });

        if (JSON.parse($('#previous-item-id').val()) == '') {
            $('#go-back-button').hide();
        } else {
            $('#go-back-button').show();
        }
    });
    //Logic for go back button.
    $('#go-back-button').click(function () {
        var previous_id = JSON.parse($('#previous-item-id').val());
        var current_previous = previous_id.pop();
        $('#current-item-id').val(current_previous);
        $('#previous-item-id').val(JSON.stringify(previous_id));
        getItemDetailsById(current_previous);
    });

    $(document).ready(function () {
        $('ul.tabs li').click(function () {
            var tab_id = $(this).attr('data-tab');

            $('ul.tabs li').removeClass('current');
            $('.tab-content').removeClass('current');

            $(this).addClass('current');
            $("#" + tab_id).addClass('current');
        });
        var users = $('#myTable').DataTable({
            pageLength: 100,
            ordering: false
        });
        $('#customSearchBox').keyup(function () {
            users.search($(this).val()).draw();
        });

        var myTableSG = $('#myTableSG').DataTable({
            pageLength: 100,
            ordering: false
        });
        var myTableSC = $('#myTableSC').DataTable({
            pageLength: 100,
            ordering: false
        });
    });


    $(".getItemDataByFolderId").on("click", function () {
        var folder_id = $(this).data("folder-id");
        getAdvertisement(folder_id);
    });
    setTimeout(function () {
        if (folderId == PUBLIC_USER_ROOT) {
            $("#filterTreePublic").show();
        } else {
            $("#filterTreePublic").hide();
        }
    }, 100);


    $(document).ready(function () {
        $('#tabs').tabs();
    });
    
    $(document).ready(function(){
        var totalRecords = '<?php echo $totalRecords; ?>';
        var sub_group_count = '<?php echo count($firstTimeDataArray['sub_groups']); ?>';
        var scrapbook_count = '<?php echo count($firstTimeDataArray['scrapbook']); ?>';
        if(parseInt(totalRecords) <= 0){
            if(parseInt(sub_group_count) <= 0 && parseInt(scrapbook_count) > 0){
                $('#scrapbook-group-tab a').click();
            }else if(parseInt(sub_group_count) > 0){
                $('#sub-group-tab a').click();
            }
        }
    });
</script>