
<?php 
if (count($apiResponse['info']['records']) > 0 ) {
    if(isset($_POST['flg']) && $_POST['flg'] == 'list_tree'){ 
        foreach ($apiResponse['info']['records'] as $itemDataArray) {
             if($_POST['group_type'] =='IT' ){
        ?>
            <tr id="<?php echo $itemDataArray['item_id']; ?>"> 
                <td>
                     <section id="grid" class="grid clearfix">		   
                         <a class="animate-load-more" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="society.html?q=<?php echo encryptQueryString('folder-id='.$itemDataArray['item_id']); ?>" data-path-hover="m 180,120.57627 -180,0 L 0,0 180,0 z">
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
             <script type="text/javascript">
             $(document).ready(function(){
                 
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
             });
             </script>
        <?php }elseif ($_POST['group_type'] =='SG') {
            $showEbayLogo = ''; 
            if(isset($itemDataArray['properties']['ebay_url']) && !empty($itemDataArray['properties']['ebay_url'])){
                $record_date = strtotime($itemDataArray['properties']['ebay_record_date']);
                $now = time();
                $datediff = $now - $record_date;
                $total_day = round($datediff / (60 * 60 * 24));
                if($total_day <= 7){ 
                    $showEbayLogo = 'Yes';
                }
            }  
            $count_display = 'Item(s)';
            ?>
              <tr id="<?php echo $itemDataArray['item_id']; ?>">
                <?php if (isset($itemDataArray['stp_url']) && $itemDataArray['stp_url'] != '' && $itemDataArray['link_type'] == 'A') { ?>
                    <td>
                        <div class="view view-first <?php
                            if(isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) {
                                if ($_SESSION["record_id"] == $itemDataArray['item_id']) {
                                 echo 'active';
                                }
                            }  ?>">
                                    <img src='<?php echo addhttp($itemDataArray["stp_thumb"]) ?>' alt="Stp Image" />
                                    <div class="mask">
                                    <div class="iconsBG top8" id="share_front_record" <?php echo $shareRecordHidden; ?> >
                                    <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>"  user-type1="society" thumb-id-val="<?php echo addhttp($itemDataArray["stp_thumb"]); ?>"></div>
                                    </div>
                                    <div class="iconsBG_two"  <?php echo $shareRecordHidden; ?>>
                                    <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="society"><div class="imgAddScrapbook"></div></div>
                                    </div>
                                    <a class="custom-link" href="<?php echo addhttp($itemDataArray['stp_url']); ?>" target="_blank">
                                        <h2>Open</h2>
                                    </a> 
                                    </div>
                                    </div>
                                    <h6 class="recordHead">
                                        <?php
                                        $dateValue = urldecode($itemDataArray['item_title']);
                                        $dateValueArray = explode(',',$dateValue);
                                        echo $dateValueArray[0].'<br>'.$dateValueArray[1]; 
                                        if($showEbayLogo == 'Yes' ){ ?>
                                        <img src="<?php echo IMAGE_PATH.'ebay-right-now.png' ?>" alt="" >
                                        <?php } ?>
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
                                    ?>">
                                            <img src='<?php echo RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id']; ?>' alt="Thumb Image"/>
                                            <div class="mask">
                                                <div class="iconsBG top8" id="share_front_record" <?php echo $shareRecordHidden; ?>>
                                                    <div  class="imgShareScrapbook share" title="Share Link" data-record-id-value="<?php echo $itemDataArray['item_id']; ?>"  user-type1="society" thumb-id-val="<?php echo RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id']; ?>"></div>
                                                </div>
                                                <div class="iconsBG_two"  <?php echo $shareRecordHidden; ?>>
                                                <div class="record_add_to_scrapbook scrapbookAdd recordScrapbook" title="Add to scrapbook" record_id="<?php echo $itemDataArray['item_id']; ?>" user-type2="society"><div class="imgAddScrapbook"></div></div>
                                                </div>
                                                <a class="custom-link details-page-url url-append animate-load-more" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" item-id="<?php echo $itemDataArray['item_id']; ?>" child-count="<?php echo $itemDataArray['child_count']; ?>" item-type="<?php echo $itemDataArray['item_type']; ?>" item-parent="<?php echo $itemDataArray['_debug']['item_parent']; ?>" href="javascript:pageMoveToItemDetail(<?php echo $itemDataArray['item_id']; ?>,<?php echo $itemDataArray['child_count']; ?>);">
                                                  <h2 style="display:<?php if (isset($_SESSION["record_id"]) && !empty($_SESSION["record_id"])) { if ($_SESSION["record_id"] == $itemDataArray['item_id']) { echo 'none';}else{echo'block';}} ?>">Open</h2>
                                                    <span><?php echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : ''; ?></span>    
                                                </a>
                                            </div>

                                        </div>
                                        <h6 class="recordHead">
                                            <?php echo urldecode($itemDataArray['item_title']); ?>
                                             <?php if($showEbayLogo == 'Yes'){ ?>
                                            <img src="<?php echo IMAGE_PATH.'ebay-right-now.png' ?>" alt="" >
                                            <?php } ?></h6>
                                    </td>
                    <?php } ?>
                            </tr>
            
        <?php }else{ ?>
            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                <td class="organizations">
                    <a class="getItemDataByFolderId setpagenumber animate-load-more"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
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
     <?php }} }else{ 
        $totalPageOfItem = (isset($_POST['action']) && $_POST['action']=='Back')?$totalPageOfItem:  ++ $totalPageOfItem;  
 ?>
<div class="sapple-slider-thumb" id="thumb-list-<?php echo $totalPageOfItem; ?>" thumb-parent-count="<?php echo $totalPageOfItem; ?>">
<?php
    $item_count = $start;
    $indexCount = $totalItemPage;
    foreach ($apiResponse['info']['records'] as $itemDataArray) {
        if ($itemDataArray['item_id'] == $itemId) {
            $selectedItem = $item_count;
        }
		$enc_str = 'folder_id='.$_REQUEST['folder_id'].'&itemId='.$itemDataArray['item_id'];
		$enc_url = 'item-details.html?q='.encryptQueryString($enc_str);
        ?>
        <div class="sapple-slider-item-thumb" id="sapple-slider-thumb-item-<?php echo $item_count; ?>">

            <?php if (isset($itemDataArray['tn_file_id']) && $itemDataArray['tn_file_id'] != '') { ?>
                <img data-enc-url="<?php echo $enc_url; ?>" data-url="item-details.html?folder_id=<?php echo $_REQUEST['folder_id']; ?>&itemId=<?php echo $itemDataArray['item_id']; ?>" data-item-type="internal" data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-pr-file-id="<?php echo $itemDataArray['pr_file_id']; ?>" data-tn-file-id="<?php echo $itemDataArray['tn_file_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo THUMB_URL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="slider-image" />
            <?php } else { ?>
                <?php if($itemDataArray['is_link'] == 'Y' && $itemDataArray['link_type'] == 'U'){ ?> 
                <img data-url="<?php echo addhttp(urldecode($itemDataArray['item_source_info'])); ?>" data-item-type="url" data-item-id="<?php echo $itemDataArray['item_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo IMAGE_PATH . 'external_url_thumb.jpg'; ?>"/>
                <?php }else{ ?>
                <img data-enc-url="<?php echo $enc_url; ?>" data-url="item-details.html?folder_id=<?php echo $_REQUEST['folder_id']; ?>&itemId=<?php echo $itemDataArray['item_id']; ?>" data-item-type="internal" data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-pr-file-id="<?php echo $itemDataArray['pr_file_id']; ?>" data-tn-file-id="<?php echo $itemDataArray['tn_file_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo IMAGE_PATH . 'no-image.png'; ?>"/>
            <?php } } ?>
               <div class="itemDetailImageNumb"><?php echo $item_count + 1  ; ?></div>
        </div>
            <?php
             $item_count ++;
    } ?>
    </div>
<?php }
} 
function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}
?>              
