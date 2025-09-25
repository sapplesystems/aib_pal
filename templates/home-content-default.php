<style>
.ui-tabs-vertical .ui-tabs-nav li{padding: 0px !important;
    height: 158px;
}
.ui-tabs-vertical .ui-tabs-nav li a{
	position:absolute;
	top:50%;
	left:50%;
	transform:translateX(-50%) translateY(-50%);
}

/* .ui-tabs-vertical .ui-tabs-panel{
	height: 473px;
} */
.historical_connection_tab{display: none;}

.ui-tabs-nav{display:none;}


</style>
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
                //Fix start for Issue ID 2002 on 22-Feb-2023
                $society_template = (!empty($_SESSION['society_template'])) ? $_SESSION['society_template'] : '';
                ?>
                <li data-title="<?php echo $treeData['item_title']; ?>" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="society.html?q=<?php echo encryptQueryString('folder-id=' . $treeData['item_id'] . '&society_template=' . $society_template); ?>"><?php echo $treeData['item_title']; ?> Home <?php echo $arrow_img; ?></a></li>
                <?php
                $itemDisTitle = 'Archives';
            }
            if (end($arrayKeys) == $key) {
                ?> 
                <li class="def" data-title="<?php echo $treeData['item_title']; ?>" data-folder-id="<?php echo $treeData['item_id']; ?>" table-page-id = "<?php echo $treeData['item_id']; ?>"><a href="javascript:void(0);"> <?=($itemDisTitle == 'ARCHIVE GROUP')?'Knowledge Base':$itemDisTitle; ?></a></li>
                <?php
            } else {
                if ($itemDisTitle != 'Scrapbooks') {
                    ?>
                    <li data-title="<?php echo $treeData['item_title']; ?>" data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $treeData['item_id'] . '&society_template=' . $society_template); ?>"><?=($itemDisTitle == 'ARCHIVE GROUP')?'Knowledge Base':$itemDisTitle . $arrow_img; ?></a></li>
                    <?php
                }
            }
        }
        ?>
    </ul>
</div>
<div class="clearfix"></div>
<?php 
$connect_links = '';
//Fix start for Issue ID 2002 on 22-Feb-2023
if (!empty($_SESSION['aib']['user_data']) && $itemData['item_type'] != 'IT' && $_SESSION['aib']['user_data']['user_type'] == 'A' && $treeDataArray[1]['item_id'] != $_SESSION['aib']['user_data']['user_top_folder']) { 
    $connect_links .= '<a href="javascript:void(0);" class="connect-to-society single-item" connecting-item-id="'.$itemData['item_id'].'"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Connect to '.$displayNameArray[$itemData['item_type']].'</a>';
    $connect_links .= '<div class="clearfix"></div>';
    $connect_links .= '<a href="javascript:void(0);" class="connect-to-society multiple-item connect-with-multiple-records" connecting-item-id=""><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Connect to record(s)</a>';
    $connect_links .= '<input type="hidden" name="selected_item_id" id="selected_item_id" value="">';
} 
?>
<?php 
/*echo '<pre>';
print_R($apiResponse);echo '</pre>';*/
if (count($apiResponse['info']['records']) > 0) { ?>
    <?php if ($itemData['item_type'] == 'SG') { ?> 
    <div id="tabs" class="tab_archive ui-tabs-vertical">
        <ul class="ui-tabs-nav">
            <li id="record-tab"><a href="#tabs-1">Records (<?php echo $totalRecords; ?>)</a></li>
            <li id="sub-group-tab" style="display:<?php echo $subgroup_hide; ?> "><a href="#tabs-2">Sub Groups (<?php echo $subGroupCountShow; ?>)</a></li>
            <li class="historical_connection_tab" id="historical_connection_tab"><a href="#tabs-3">Historical Connection <span id="historical_count"></span></a></li>
        </ul>
        <div id="tabs-1" class="ui-tabs-panel">
			<?php //echo "home.html?q=".encryptQueryString('folder_id=' . $treeData['item_id'] . '&society_template=' . $society_template);
			$sort_by_val='';
											//   print_r($_SESSION);
			if(isset($_SESSION['sort_by']) and $_SESSION['sort_by']!=''){
				$sort_by_val=$_SESSION['sort_by'];
			}
			?>
		<form id="sortByForm" name="sortByForm" method="post" action="<?php echo "home.html?q=".encryptQueryString('folder_id=' . $treeData['item_id'] . '&society_template=' . $society_template);?>">	
			<select id="sort_by" name="sort_by" onChange="sortyByFormF(this.value)">
				<option value="" <?php if($sort_by_val==''){ echo 'selected';}?> >Sort By</option>
				<option value="a-z" <?php if($sort_by_val=='a-z'){ echo 'selected';}?>>Alphabetical A-Z</option>
				<option value="z-a" <?php if($sort_by_val=='z-a'){ echo 'selected';}?>>Alphabetical Z-A</option>
				<option value="9-1" <?php if($sort_by_val=='9-1'){ echo 'selected';}?>>Highest to Lowest</option>
				<option value="1-9" <?php if($sort_by_val=='1-9'){ echo 'selected';}?>>Lowest to Highest</option>
			</select>
		</form>	
            <?php /*if (count($subGroup)) { ?>
                <a href="javascript:void(0);" id="browse_society_subgroup" class="switch-tab" active-tab-id="sub-group-tab">View Sub Groups - <?php echo count($subGroup); ?></a>
            <?php }*/ ?>
            <div id="sgDivContent">
                <div class="text-center"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px;">Sub Groups in the <span>"<?php echo $currentItemTitle;?>"</span> sub group (Folders)</span></div>
                <div class="folders_div" style="border: 1px solid #ccc;margin-top: 10px;margin-bottom: 30px;display: inline-block;width: 100%;">
                <div id="onlyfourRecords">
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
                    $countData = 1;
                    $countTotalSG = 0;
                    foreach ($apiResponse['info']['records'] as $itemDataArray) {
                        $count_display = '';
                        if ($itemDataArray['item_type'] == 'SG') {
                            if($countData < 5){
                            $count_display = 'Rec(s)';
                            ?>
                            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                                <td class="organizations sub-groups">
                                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber animate-load-more" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
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
                        <?php   } $countData ++; $countTotalSG++;} ?>
                    <?php } ?>
                </div>
                </tbody>  
                </table>
				<?php if($countTotalSG >4){?>	
                <div class="btnShow"><button class="btn btn-primary" onclick="showAllRecords('all');">View <?php echo ($countTotalSG - 4); ?> More</button></div>
				<?php }?>	
                </div>
                <!--------------------------------------------------------------------------------------------------------->
                <div id="allRecords" style="display:none;">
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
                                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber animate-load-more" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
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
                        <?php   } ?>
                    <?php } ?>
                </div>
                </tbody>  
                </table>
                <div class="btnShow2"><button class="btn btn-primary" onclick="showAllRecords('less');">View Less</button></div>
                </div>
            </div>
            </div>
            <div>
            <div class="text-center"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px;">Records in the <span>"<?php echo $currentItemTitle;?>"</span> sub group (Files)</span></div>
            <div class="files_div" style="border: 1px solid #ccc;margin-top: 10px;padding:10px;">
            <ul class="columnView">
                <li onclick="setClass(this.id)" id="3">3 Column</li>
                <li onclick="setClass(this.id)" id="4">4 Column</li>
                <li onclick="setClass(this.id)" id="5">5 Column</li>
            </ul>
            <table id="myTable" class="custum_tbl custom_css" width="100%" cellpadding="0" cellspacing="0">  
                <thead>  
                    <tr>  
                        <th>Forms Id</th>   
                    </tr>  
                </thead>  
                <tbody class="society_ast_sub_list widthB sub_society_record_more_load" id="column">  <!-- widthB=5 column, widthC=4 column, widthD=3 column -->
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
                                                <!--//Fix start for Issue ID 2002 on 22-Feb-2023-->
                                                <!-- Fix start ID 0002427  on 14-Feb-2025-->
                                                <a class="custom-link details-page-url url-append animate-load-more" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" item-id="<?php echo $itemDataArray['item_id']; ?>" child-count="<?php echo $itemDataArray['child_count']; ?>" item-type="<?php echo $itemDataArray['item_type']; ?>" item-parent="<?php echo (!empty($itemDataArray['link_properties']['item_parent'])) ? $itemDataArray['link_properties']['item_parent'] : ''; ?>" href="javascript:pageMoveToItemDetail(<?php echo $itemDataArray['item_id']; ?>,<?php echo $itemDataArray['child_count']; ?>,'<?php echo $itemDataArray['item_type'] ?>', <?php echo $itemDataArray['link_properties']['item_parent'] ?>);">
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
                                                <!-- Fix End ID 0002427  on 14-Feb-2025-->
                                            </div>
                                        </div>
										<!--- Fix start SS Issue Id 2306  14Aug 2023 ---->
                                        <h6 class="recordHead" title="<?php echo urldecode($itemDataArray['item_title']); ?>"><?php echo urldecode($itemDataArray['item_title']); ?></h6>
										<!--- Fix end SS Issue Id 2306  14Aug 2023 ---->
                                        <?php if ($itemDataArray['item_type'] == 'RE') { ?>
                                            <span><?php echo ($count_display != '') ? $itemDataArray['child_count'] . '  ' . $count_display : ''; ?></span> 
                                        <?php } ?>
                                            <?php if ($showEbayLogo == 'yes' && $ebayCheckCondition != 'N') { ?><img src="<?php echo IMAGE_PATH . 'ebay-right-now.png' ?>" alt="" > <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </div>
                </tbody>  
            </table>
            
            <div class="clearfix"></div>
			<script type="text/javascript">
                //Fix start for Issue ID 2002 on 22-Feb-2023
				var load_more_title = '<?php echo (!empty($load_more_title)) ? $load_more_title : ''; ?>';
                //Fix end for Issue ID 2002 on 22-Feb-2023
				$(document).on('click','.animate-load-more',function(e){
					var fid = $(this).data('folder-id');
					localStorage.setItem('animate-load-more-record', fid);
				});
			</script>
            <div class="loadMoreBtn load_more_data load-more-record-data" hidden><button class="btn search-button load_more_list_data_val" >Load More</button></div>
            <?php if ($subgroup_hide != 'none') { ?>
                <div id="view-info-text-sub-group" style="display: none;">To view more records, Please click on Sub-Group tab</div>
            <?php } ?>
            </div>
            </div>
        </div>
       <!-- <div id="tabs-2" class="ui-tabs-panel">
            <?php /*if (count($apiResponse['info']['records']) > 0) { ?>
                <a href="javascript:void(0);" id="browse_society_records" class="switch-tab" active-tab-id="record-tab">View Records - <?php echo $totalRecords; ?></a>
            <?php }*/ ?>
            
        </div>
        <div id="tabs-3" class="ui-tabs-panel"><div id="historical_connection_data"></div>--></div>
    </div>
    <?php } elseif ($itemData['item_type'] == 'AG') { ?>
    <div id="tabs" class="tab_archive ui-tabs-vertical">
        <ul class="ui-tabs-nav">
            <li id="archive-tab"><a href="#tabs-1">Archives (<?php echo $totalArchive; ?>)</a></li>
            <li id="scrapbook-tab" style="display: <?php echo $hideScrapbook; ?>"><a href="#tabs-2">Scrapbooks (<?php echo count($societyScrapbookListing); ?>)<span class="glyphicon glyphicon-menu-down arrowDownTab" aria-hidden="true"></span></a></li>
            <li class="historical_connection_tab" id="historical-tab2"><a href="#tabs-3">Historical Connection <span id="historical_count"></span><span class="glyphicon glyphicon-menu-down arrowDownTab" aria-hidden="true"></span></a></li>
        </ul>
		<div class="text-center" ><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px; margin-top: 15px;">Archive Groups</div>	
        <div id="tabs-1" class="ui-tabs-panel">
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
                                <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber animate-load-more"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
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
			<script type="text/javascript">
				$(document).on('click','.animate-load-more',function(e){
					var fid = $(this).data('folder-id');
					localStorage.setItem('animate-load-more-archive', fid);
				});
			</script>
        </div>
		
		 <?php if(count($societyScrapbookListing) >0 ){ ?>
		<div class="text-center"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px; margin-top: 15px;">Scrapbooks</div>
			<?php }?>
        <div id="tabs-2" class="ui-tabs-panel" <?php if(count($societyScrapbookListing)<1){echo ' style="display: none;"';}?>>
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
                                <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber animate-load-more"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
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
		<div class="text-center" style="display: none;" id="historical_connection_data_heading"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px; margin-top: 15px;">Historical Connections</div>	
        <div id="tabs-3" class="ui-tabs-panel"><div id="historical_connection_data"></div></div>
    </div>
    <?php } else { ?>
        </div>
        </div>
		
        
		<?php 
				  
				   
				  if($itemData['item_type'] == 'AR') {?>
		<div class="clearfix"></div><div class="text-center"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px;">Collections in the <span>"<?php echo $currentItemTitle;?>"</span></div>
		<?php } if($itemData['item_type'] == 'CO') {?>
		<div class="clearfix"></div><div class="text-center"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px;">Subgroups in the <span>"<?php echo $currentItemTitle;?>"</span> </div>
		<?php }?>
		
        <?php
        $tab_content = '';
       
        if ($apiResponse['info']['records'][0]['item_parent'] != '1') {
            $item_type_title = 'Archive(s)';
            if($itemData['item_type'] == 'AR'){
                $item_type_title = 'Collection(s)';
				$load_more_title = 'collection';
            }else if($itemData['item_type'] == 'CO'){
                $item_type_title = 'Sub Group(s)';
				$load_more_title = 'sub_group';
            }
            ?>
        <div id="tabs" class="tab_archive ui-tabs-vertical">
            <ul class="ui-tabs-nav">
                <li id="archive-tab"><a href="#tabs-1"><?php echo $item_type_title; ?> (<?php echo count($apiResponse['info']['records']); ?>)</a></li>
                <li class="historical_connection_tab" id="historical-tab"><a href="#tabs-2">Historical Connection <span id="historical_count"></span></a></li>
            </ul>
            <?php
            $tab_content = 'tab-content';
        }
        ?>
        <div id="tabs-1" class="ui-tabs-panel">
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
                                    <a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $itemDataArray['item_id'] . '&society_template=' . $_SESSION['society_template']); ?>" class="setpagenumber animate-load-more"  data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="javascript:void(0);" title="<?php echo $itemDataArray['item_title']; ?>"><span><?php echo $itemDataArray['item_title'][0]; ?></span><?php echo substr($itemDataArray['item_title'], 1); ?></a>
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
				<script type="text/javascript">
					var load_more_title = '<?php echo $load_more_title; ?>';
					$(document).on('click','.animate-load-more',function(e){
						var fid = $(this).data('folder-id');
						localStorage.setItem('animate-load-more-'+load_more_title, fid);
					});
				</script>
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
							//echo '<pre>';print_r($itemDataArray);die;
							//Fix start for Issue ID 2147 on 28-Apr-2023
							if($itemDataArray['item_type']=='AG'){
								//Fix end for Issue ID 2147 on 28-Apr-2023
                            $customTemp = isset($itemDataArray['properties']['custom_template']) ? $itemDataArray['properties']['custom_template'] : '';
                            $customTempLink = '&society_template=default';
                            if ($customTemp != '') {
                                $customTempLink = '&society_template=' . $customTemp;
                            }
                            ?>
                            <tr id="<?php echo $itemDataArray['item_id']; ?>">
                                <td>
									
                                    <section id="grid" class="grid clearfix">		   
                                        <a class="animate-load-more" data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="society.html?q=<?php echo encryptQueryString('folder-id=' . $itemDataArray['item_id'] . $customTempLink); ?>" data-path-hover="m 180,120.57627 -180,0 L 0,0 180,0 z">
                                            <figure>
                                                <?php
                                                $image_name = (isset($itemDataArray['property_list']['archive_group_thumb']) && $itemDataArray['property_list']['archive_group_thumb'] != '' && file_exists(ARCHIVE_IMAGE . $itemDataArray['property_list']['archive_group_thumb'])) ? ARCHIVE_IMAGE . $itemDataArray['property_list']['archive_group_thumb'] : IMAGE_PATH . 'no-image-blank.png';
                                                ?>
                                                <img src="<?php echo $image_name; ?>" alt="Historical Image" />
                                                <svg viewBox="0 0 180 320" preserveAspectRatio="none"><path d="M 280,260 0,218 0,0 180,0 z"/></svg>
                                                <figcaption>
                                                    <h2><?php echo $itemDataArray['item_title']; ?></h2>
													<p><?php 
													echo $itemDataArray['property_list']['archive_display_city'];
													if($itemDataArray['property_list']['archive_display_city']!='' and $itemDataArray['property_list']['archive_display_state']!=''){
														echo ', ';
													}
													echo $itemDataArray['property_list']['archive_display_state'];?></p>
                                                    <!--<p><?php echo $itemDataArray['item_title']; ?></p>-->
                                                    <button>View</button>
                                                </figcaption>
                                            </figure>
                                        </a>
                                    </section> 
                                </td>
                            </tr>
                        <?php //Fix start for Issue ID 2147 on 28-Apr-2023 
							}
						//Fix end for Issue ID 2147 on 28-Apr-2023
						} ?>
                    </tbody>  
                </table> 
            <?php echo $load_more_button; ?>
			<script type="text/javascript">
				$(document).on('click','.animate-load-more',function(e){
					var fid = $(this).data('folder-id');
					localStorage.setItem('animate-load-more-home', fid);
				});
			</script>
            <?php } ?>
        </div>
        <?php if ($apiResponse['info']['records'][0]['item_parent'] != '1') { ?>
			<div class="text-center" style="display: none;" id="historical_connection_data_heading"><span style="background: var(--first-color);color: var(--second-color);display: inline-block;padding: 5px 10px;font-weight: bold;font-size: 14px; margin-top: 15px;">Historical Connections</div>	
            <div id="tabs-2" class="ui-tabs-panel"><div id="historical_connection_data"></div></div>
        </div>
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

<script type="text/javascript">
$(document).ready(function(){
   // $('#tabs').tabs();
	<?php if($countTotalSG ==0){?>
	$('#sgDivContent').hide();
	<?php }?>
});

var totalRecords = '<?php echo $totalRecords; ?>';
var subGroup = '<?php echo count($subGroup) ?>';
//Fix start for Issue ID 2002 on 22-Feb-2023
var totalArchive = '<?php echo (!empty($totalArchive)) ? $totalArchive : '' ?>';
var societyScrapbookListing = '<?php echo (!empty($societyScrapbookListing)) ? count($societyScrapbookListing) : 0; ?>';
//Fix end for Issue ID 2002 on 22-Feb-2023

var archives = '<?php echo count($apiResponse['info']['records']); ?>';
setTimeout(function(){
    if(totalRecords <= '0' || totalRecords == ''){
        if(subGroup <= '0' || subGroup == ''){
            $('#historical_connection_tab a').click();
        }else{
            $('#sub-group-tab a').click();
        }
    }
    
    if(totalArchive <= '0' || totalArchive == ''){
        if(societyScrapbookListing <= '0' || societyScrapbookListing == ''){
            $('#historical-tab2 a').click();
        }else{
            $('#scrapbook-tab a').click();
        }
    }
    
    if(archives <= '0' || archives == ''){
        $('#historical-tab a').click();
    }
},1000);

$('#connect_links').html('<?php echo $connect_links; ?>');
function setClass(value)
{
    if(value=='3')
    {
        $('#column').removeClass("widthC");
        $('#column').removeClass("widthB");
        $('#column').addClass("widthD");
    }
    else if(value=='4')
    {
        $('#column').removeClass("widthD");
        $('#column').removeClass("widthB");
        $('#column').addClass("widthC");
    }
    else if(value=='5')
    {
        $('#column').removeClass("widthD");
        $('#column').removeClass("widthC");
        $('#column').addClass("widthB");
    }
}

function showAllRecords(type)
{
    if(type == 'all')
    {
        $('#allRecords').css('display','');
        $('#onlyfourRecords').css('display','none');

    }else{
        $('#allRecords').css('display','none');
        $('#onlyfourRecords').css('display','');

    }
    
    
}
function sortyByFormF(value){
	if(value!=''){
	$('#sortByForm').submit();
		}
}	
</script>