<?php
$pageUsedOptions = [
    'BUS' => "Business -- Promote your business by reprinting portion of articles",
    'BUSR' => "Business -- Research content to be used as a component",
    'MED' => "Media -- Television, radio, print news, public relations",
    'MEDG' => "Media -- Professional Genealogy Services",
    'MEDA' => "Media -- Any activity where you receive payment for use of content",
    'PER' => "Personal -- Single scrapbook, family history document",
    'PERE' => "Personal -- Educational or hobby use",
    'OTH' => "Other -- Describe in detail in the 'comments' section below"
];
$publicLinkHide ='';
$title ='';
if(count($treeDataArray)<= 3){
    $publicLinkHide = 'hidden';
    $title = 'User Record';
}
$selectedItem = 0;
$reference_id=(isset($_REQUEST['reference_id']) && $_REQUEST['reference_id'] != '') ? $_REQUEST['reference_id'] : '';
$printImagehide ='';
if(isset($_SESSION['aib']['user_data'])&& !empty($_SESSION['aib']['user_data'])){
 if($_SESSION['aib']['user_data']['user_type']=='U'){$printImagehide = 'hidden';}   
}

if($_REQUEST['flg']==1){$page = 'people.php'; $printImagehide = 'hidden'; $publicLinkHide='';}else{$page = 'home.php';}
if($_REQUEST['flg']!=2){$scrapbook_hide = 'hidden=""'; $reprintPurchaseHide = 'hidden';}
$ebay_field_hide = "";
if(isset($itemData['properties']['ebay_url']) && !empty($itemData['properties']['ebay_url'])){
    $image_name = $itemData['properties']['ebay_sale_options'].'.png';
    $ebay_url = addhttp($itemData['properties']['ebay_url']);
    $record_date = strtotime($itemData['properties']['ebay_record_date']);
    $now = time();
    $datediff = $now - $record_date;
    $total_day = round($datediff / (60 * 60 * 24));
    if($total_day > 7){
        $ebay_field_hide = 'hidden'; 
    }
}else{
    $image_name = 'not_applicable.png';
    $ebay_url = "#";
    $ebay_field_hide = "hidden";
}
function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}
$addToScrapbook='';
if(in_array($_REQUEST['folder_id'],$_SESSION["scrapbook_ref"])){$addToScrapbook = 'hidden';}
$login_username_rep ='';
if(isset($_SESSION['aib']['user_data']['user_login']) && !empty($_SESSION['aib']['user_data']['user_login'])){
    $login_username_rep = $_SESSION['aib']['user_data']['user_login'];
}
$archive_logo   = '';
$logo_back_home = '';
$display_name   = '';
$archive_id     = '';
if($treeDataArray[0]['item_title'] == '_STDUSER'){
    $logo_back_home = "people_profile.php?folder-id=".$treeDataArray[1]['item_id'];
    $archive_logo = $treeDataArray[1]['properties']['archive_group_thumb'];
    $display_name = $treeDataArray[1]['item_title'];
    $archive_id   = $treeDataArray[1]['item_id'];
}else{
    $logo_back_home = "society.php?folder-id=".$treeDataArray[1]['item_id'];
    $archive_logo = $treeDataArray[1]['properties']['archive_group_details_thumb'];
    $display_name = $treeDataArray[1]['item_title'];
    $archive_id   = $treeDataArray[1]['item_id'];
}
/*
if(isset($treeDataArray[1]['properties']['archive_logo_image']) && !empty($treeDataArray[1]['properties']['archive_logo_image'])){
  $archive_logo = $treeDataArray[1]['properties']['archive_logo_image'];  
} */
$showLink          = array('U','A');
$shareItemHidden ='';
if(isset($_SESSION['aib']['user_data']) && !in_array($_SESSION['aib']['user_data']['user_type'],$showLink)){
  $shareItemHidden = 'hidden';  
  $addToScrapbook = 'hidden';
} 
if($userPropertyArray['archive_show_purchase_reprint_btn'] == 'Y' && isset($userPropertyArray['archive_show_purchase_reprint_btn'])){
	$publicLinkHide = '';
	$printImagehide = '';
}
$searchString = (isset($_REQUEST['searchString']) && $_REQUEST['searchString'] != '') ? $_REQUEST['searchString'] : '';
?>
<div class="">
    <div class="col-md-12 project-bg-img home-bg-img-animate-new" style="opacity:1;" id="home-bg-img-inner">
        <div class="topHeader">
            <div id="desktop-slider-section" >
                <div class="sapple-slider">
                    <div class="sapple-slider-banner">
                        <div class="banner-slider">
                            <div class="clearfix"></div>
                            <div class="historical_connection_top">
                                <div class="historical_head custom_historical_head">Historical Connections</div>
                                <div id="historical_connection_data_top"></div>
                            </div> 
                            <div class="clearfix"></div>
                            <?php if($preKeyId !=''){ ?>
                          <a href="javascript:pageMoveToItemDetail(<?php echo $preKeyId; ?>)">  <button class="btn preRecord"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Previous Record</button></a>
                            <?php } ?>
                            <div class="sapple-slider-item <?php if(count($apiResponse['info']['records']) <= 0 && $private_records == 'yes'){ echo 'no-item-found'; }elseif(count($apiResponse['info']['records']) <= 0 && $private_records == ''){ echo "no-item"; } ?>" id="sapple-slider-item-0" item-count="0"></div>
                           <input type="hidden" name="maximum_image_count" id="maximum_image_count" value="<?php echo count($apiResponse['info']['records']); ?>">
			    <?php if(isset($archive_logo) && $archive_logo != ''){ ?>
                            <div class="textRelated"><a href="<?php echo $logo_back_home; ?>"><img id="client_logo" style="width:150px;" src="<?php echo ARCHIVE_IMAGE.$archive_logo ; ?>" /><div class="imghoverText"><div><?php echo $display_name; ?></div></div></a><span id="contact_archive_owner" data-owner_id="<?php echo $archive_id; ?>"><?php echo $display_name; ?></span></div>
                            <?php } ?>
                            <?php if($nextKeyId !=''){ ?>
                            <a href="javascript:pageMoveToItemDetail(<?php echo $nextKeyId; ?>)"><button class="btn nextRecord"> Next Record <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span></button></a>
                            <?php } ?>
                             <div class="contentdiv">
<div class="slidediv">

<div class="tabs">
<span id="" class="relatedImage btnright"><!--<img class="leftArrowImg" id="" src="<?php echo IMAGE_PATH . 'leftArrow.png'; ?>"> <?php if(isset($_SESSION['archive_item_title'])){ echo $_SESSION['archive_item_title'].'&nbsp;';}?>Related Content--><img class="leftArrowImg" src="public/images/relatedContent.png" alt="">
	<div class="digitConnection relatedContentNumbr"></div>
</span>
  <ul id="tabs-nav">
    <li><a href="#tab1"><div class="listIcon"></div></a></li>
    <li><a href="#tab2"><div class="gridIcon"></div></a></li>
  </ul> <!-- END tabs-nav -->
  <div id="tabs-content">
  
  <div class="leftSection" id="related_content_section"></div>
   
  </div> <!-- END tabs-content -->
</div> <!-- END tabs -->
</div>
<div>

</div>
</div>

<div class="contentdivP">
<div class="slidedivP">
<div class="tabs">
<span id="" class="relatedImage btnrightP"><!--<img class="leftArrowImg" id="" src="<?php echo IMAGE_PATH . 'leftArrow.png'; ?>"> <?php if(isset($_SESSION['archive_item_title'])){ echo $_SESSION['archive_item_title'].'&nbsp;';}?>Public Connection-->
<img class="leftArrowImg" id="" src="<?php echo  'public/images/publicConnection1.png'; ?>">
<div class="digitConnection publicConNumbr" ></div>
</span>
  <ul id="tabs-navP">
    <li><a href="#tab3"><div class="listIcon"></div></a></li>
    <li><a href="#tab4"><div class="gridIcon"></div></a></li>
  </ul> <!-- END tabs-nav -->
  <div id="tabs-content">
  <div class="leftSection" id="public_connection_content_section"></div>
   
  </div> <!-- END tabs-content -->
</div> <!-- END tabs -->
</div>
<div>

</div>
</div> 
   </div>
                        <div style="text-align:center;"><img  id="view-more-picture" src="<?php echo IMAGE_PATH . 'view-more-pictures.png'; ?>"></div>
                        <?php if (count($apiResponse['info']['records']) > 0) { ?>
                        <div class="thumb-section">
                        <div  class="left450"  <?php echo $ebay_field_hide; ?>><a style="display:none" href="<?php echo $ebay_url; ?>" class="ebay_class" target="_blank"><img src="<?php echo IMAGE_PATH . $image_name; ?>" alt="ebay-logo" class="" /></a></div>
                            <div id="record_item_title"></div>
                            <!--<div id="hide-slider-thumb" style="text-align:center; display:none; margin-top: -14px;"><img style="cursor:pointer;" id="hide-more-picture" src="<?php echo IMAGE_PATH . 'hide-slider.png'; ?>"></div>-->
                            <div class="thumb-listing" style="display:none;" >
                                <input type="hidden" name="start" id="start" value="<?php echo $start; ?>">
                                <input type="hidden" name="total_item_page" id="total_item_page" value="<?php echo $totalPageOfItem ; ?>">
                                <div class="sapple-slider-thumb" id="thumb-list-<?php echo $totalPageOfItem; ?>" thumb-parent-count="<?php echo $totalPageOfItem ; ?>">
                                    <?php
                                    if (count($apiResponse['info']['records']) > 0) {
//                                        $item_count = 0;
//                                        $indexCount = 1;
                                        $item_count = $start;
                                        $indexCount = $totalPageOfItem;

                                        foreach ($apiResponse['info']['records'] as $itemDataArray) {
                                            if ($itemDataArray['item_id'] == $itemId) {
                                                $selectedItem = $item_count;
                                            }
                                            ?>
                                            <div class="sapple-slider-item-thumb" id="sapple-slider-thumb-item-<?php echo $item_count; ?>">
                                                
                                                <?php if (isset($itemDataArray['tn_file_id']) && $itemDataArray['tn_file_id'] != '') { ?>
                                                <img data-url="item-details.php?folder_id=<?php echo $_REQUEST['folder_id']; ?>&itemId=<?php echo $itemDataArray['item_id']; ?>" data-item-type="internal" data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-pr-file-id="<?php echo $itemDataArray['pr_file_id']; ?>" data-tn-file-id="<?php echo $itemDataArray['tn_file_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo THUMB_URL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="slider-image" />
                                                <?php } else { ?>
                                                    <?php if($itemDataArray['is_link'] == 'Y' && $itemDataArray['link_type'] == 'U'){ ?> 
                                                    <img data-url="<?php echo addhttp(urldecode($itemDataArray['item_source_info'])); ?>" data-item-type="url" data-item-id="<?php echo $itemDataArray['item_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo IMAGE_PATH . 'external_url_thumb.jpg'; ?>"/>
                                                    <?php }else{ ?>
                                                    <img data-url="item-details.php?folder_id=<?php echo $_REQUEST['folder_id']; ?>&itemId=<?php echo $itemDataArray['item_id']; ?>" data-item-type="internal" data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-pr-file-id="<?php echo $itemDataArray['pr_file_id']; ?>" data-tn-file-id="<?php echo $itemDataArray['tn_file_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo IMAGE_PATH . 'no-image.png'; ?>"/>
                                                <?php } } ?>
						<div class="itemDetailImageNumb"><?php echo $item_count + 1; ?></div>
                                            </div>
                                            <?php
                                            $item_count++;
                                            if ($item_count % 3 == 0 && count($apiResponse['info']['records']) > ($indexCount * 3)) {
                                                $indexCount++;
                                                ?>
                                            </div><div class="sapple-slider-thumb" id="thumb-list-<?php echo $indexCount; ?>" thumb-parent-count="<?php echo $indexCount; ?>">
                                                <?php
                                            }
                                        }
                                    }
                                    ?>              
                                </div>
                                <div id="add_to_scrapbook" class="left350" <?php echo $addToScrapbook; ?>><button class="btn reprintBtn">Add to scrapbook</button><img height="20" src="<?php echo IMAGE_PATH . 'reprintIcon.png'; ?>" alt="" class="reprintIcon" /></div>
                                <div class="purchase-reprint" <?php echo $printImagehide.$publicLinkHide; ?> ><button class="btn reprintBtn">Purchase Reprint</button><img height="20" src="<?php echo IMAGE_PATH . 'reprintIcon.png'; ?>" alt="" class="reprintIcon" /></div>
				<?php if($totalItems > 3){ ?>			 
                                <div class="left-arrow-section"><i class="left-arrow"></i></div>
                                <div class="right-arrow-section"><i class="right-arrow"></i></div>
                                <?php } ?>
                                <div class="pagination-counter">
                                    <span class="counter"><sup class="current">1</sup><img src="<?php echo IMAGE_PATH . 'line.png'; ?>"><sub class="total">1</sub></span>
                                </div>
                                <!--<div class="maximise"><img height="30" src="<?php echo IMAGE_PATH . 'maximise.png'; ?>" alt=""></div>-->
                                <div id="share_front_items" class="share" <?php echo $shareItemHidden; ?> ><button data-item-id-value="<?php echo $itemDataArray['item_id']; ?>" data-target=".bs-example-modal-sm" data-toggle="modal" class="btn reprintBtn">Share Link</button></div>
                                <?php
				    if(isset($_SESSION['aib']['user_data']['user_type'])&& $_SESSION['aib']['user_data']['user_type'] == 'U'){
									  
					 ?>
                                          <div id="linkPubConBtn" class="linkBtn"><button class="btn">Connect</button></div>
										 <?php  
									}
								?>
                            </div>
                             <div class="topPosition" id="back_to_scrapbook" <?php echo $scrapbook_hide; ?>><button class="btn btn-success">Back to scrapbook <img height="20" src="<?php echo IMAGE_PATH . 'reprintIcon.png'; ?>" alt="" class="reprintIcon" /></button></div>
                             <div class="pull-right" id="backDivURL"><a class="backtoLink" id="backURL" href=""><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt=""> <span>Back To Search</span></a></div>
                        </div>
                        <?php } ?>
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div>
        <ul class="listing">
            <li><a href="index.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
            <?php $arrayKeys=array_keys($treeDataArray);$count=0;
            $redirect_file_name = 'home.php';
            if($treeDataArray[0]['item_title'] == '_STDUSER'){
                $redirect_file_name = 'people.php';
            }
            foreach ($treeDataArray as $key => $treeData) {
                $treeData['item_title'] = ($treeData['item_title'] == '_STDUSER') ? 'USER LIST' : $treeData['item_title'];
		$itemDisTitle=$treeData['item_title'];
		if($treeData['item_type']=='AG' and $count==0){ ?>
                    <li><a href="society.php?folder-id=<?php echo $treeData['item_id']?>"><?php echo $treeData['item_title'] ?></a></li>
                    <?php	
                    $itemDisTitle='Archives';$count++;
		}else if(($treeData['item_type']=='CO' || $treeData['item_type']=='AR') and  $count==0){ ?>
                    <li><a href="/people_profile.php?folder-id=<?php echo $treeData['item_id']?>"><?php echo $treeData['item_title'].' Home';?></a></li>
		<?php	
                    $itemDisTitle='Archives';$count++;
		}
                ?>
                <?php if (end($arrayKeys) == $key) {
                    $record_name = '';
                    if($treeDataArray['item_type'] == 'RE'){
                        $record_name = urldecode($itemDisTitle);
                    }
                    ?> 
                <li><a href="javascript:void(0);"><?php echo urldecode($itemDisTitle) ; ?></a></li>
                <?php } else { ?>
                    <li data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="<?php echo $redirect_file_name;?>?folder_id=<?php echo $treeData['item_id']; ?>"><?php echo $itemDisTitle; ?></a></li>
                <?php }
            }
            ?>
        </ul>
        <?php
    if($_SESSION['aib']['user_data']['user_type'] == 'A' && $treeDataArray[1]['item_id']!= $_SESSION['aib']['user_data']['user_top_folder']){ ?>
        <a href="javascript:void(0);" class="btn connect-to-society-detail single-item marginRight15" connecting-item-id="<?php echo $_REQUEST['folder_id']; ?>"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Connect to this record</a>
    <?php }?>
    </div>
    <div class="bgYellow">
        <div class="rightBox hConnection">
            <div>
                <div id="tabs_container" class="minHeight200">
                    <div id="tabs-1">
                    </div>

                    <div id="tabs-2">
                        <h4 class='headingComment'>Comments <button class="btn btn-success pull-right" id="load_comments_details_page">View Comments</button></h4>                    
                        <span id="com-msg" style="display:none">Your comment has been posted.</span>
                        <span id="loadingComment" style="display:none; padding-bottom:10px;"><img style="width:20px" src="public/images/loading.gif" alt="Loading..."> We are fetching previous comments...</span>
                        <div class="borderTopCmnt" style="display: none;"></div>
                        <img class="userPic postUser" src="public/images/avatar-1.png" alt="" />
                       <textarea placeholder="Write a reply..." class="widthTextarea" id="txtUserComments"></textarea>
                        <div class="viewAll" id="post-your-comments">Post your comment</div>     
                    </div>
                </div><!--End tabs container-->
            </div><!--End tabs-->
            <!-- Second Tab -->
        </div>
        <div class="treeDesign leftModule adSection">
            <div class="clearfix"></div>
            <div class="historical_connection hConnection-footer">
                <div class="historical_head custom_historical_head">Historical Connections</div>
                <div id="historical_connection_data"></div>
            </div> 
            <div class="clearfix"></div>
            <?php include 'ads.php'; ?>
        </div>
        <div class="clearfix"></div>
    </div>     

</div>
<div class="modal fade" id="full-width-image-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-body padd_f location_top">
                <div class="fullWidthDiv">
                    <div id="" style="z-index: 3; top: 5.7%; right:5.5%; position: absolute;" class="navCanel">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <?php if (count($apiResponse['info']['records']) > 1) { ?>
                        <div class="leftNavON"></div>
                    <?php } ?>
                    <div style="width:100%; text-align:center; display:inline-table; height:100%;">
                        <div style="display:table-cell; vertical-align:middle;">
                            <div>
                                <div id="large_image_container" style="text-align: -webkit-auto"></div>
                            <p class="imgDetailsDesc">
                                <strong>Record Name :</strong> <span class="record_name"> <?php
                                end($treeDataArray);
                                $key = key($treeDataArray);
                                if($treeDataArray[$key]['item_type'] =='RE'){
                                   echo $treeDataArray[$key]['item_title'] ;
                                } ?> 
                                </span><br />
                                <strong>Item Name :</strong> <span class="item_title">  </span><br />
                                <strong>Page No :</strong> <span class="page_number"></span>
                            </p>
                            </div>
                        </div>
                    </div>
                    <?php if (count($apiResponse['info']['records']) > 1) { ?>
                        <div class="rightNavON"></div>
                    <?php } ?>
                    <?php if($preKeyId !=''){ ?>
                        <a href="javascript:pageMoveToItemDetail(<?php echo $preKeyId; ?>)">  <button class="btn preRecord"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Previous Record</button></a>
                    <?php } ?>    
                    <div class="fullWidthESC">Click "ESC" Exit Full Screen Mode</div>
                    <div class="zoomBtn">
                        <button id="zoom_in">+</button>
                        <img class="zoom_magnifire" src="<?php echo IMAGE_PATH; ?>zoom_in.png" alt=""/>
                        <button id="zoom_out">-</button>
                    </div>
                    <div class="popup-original-url-image"><a target="_blank"><button class="btn">Open Url</button></a></div>
                     <div class="popup-original-image" ><button class="btn">View Original Image</button></div>
                    <div class="popup-purchase-reprint" <?php echo $printImagehide.$publicLinkHide ; ?>><button class="btn">Purchase Reprint</button></div>
               
                <?php if($nextKeyId !=''){ ?>
                     <a href="javascript:pageMoveToItemDetail(<?php echo $nextKeyId; ?>)"><button class="btn nextRecord"> Next Record <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span></button></a>
                <?php } ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="modal fade" id="contactAIBReprint" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Contact Us <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <p>* = Required Field</p>
                    <form class="form-horizontal" name="contact_aib_form" id="contact_aib_form" method="POST" action="">
                        <input type="hidden" name="request_type" id="request_type_CT" value="CT">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_first_name" id="contact_first_name" placeholder="">
                            </div>
                        </div>
			<input type="text" id="field_reprint_contactus" name="field_reprint_contactus" value="" style="display:none">  
			<input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_last_name" id="contact_last_name" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email Address* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_email" id="contact_email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Subject* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_subject" id="contact_subject" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Your Message :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="contact_comments" name="contact_comments" rows="4"></textarea>
                            </div>
                        </div> 
                        <div class="text-center"><button class="btn btn-success" id="submit_contact_request">SUBMIT REQUEST</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="modal fade" id="add_current_item_to_scrapbook" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Add item to scrapbook <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="add_scrapbook_item_form" id="add_scrapbook_item_form" method="POST" action="">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Scrapbook Entry Title* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="scrapbook_entry_title" id="scrapbook_entry_title" placeholder="Enter title here">
                            </div>
                        </div>
			<input type="text" id="field_scrapbook_form" name="field_scrapbook_form" value="" style="display:none">  
			<input type="text"  name="timestamp_value" id="scrap_timestamp" value="<?php echo time();?>" style="display:none">
                        <div class="form-group" id="scrapbookselectId">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Select a scrapbook* :</label>
                            <div class="col-sm-7">
                                <select class="form-control" name="user_scrapbook" id="user_scrapbook"></select>
                            </div>
                        </div>
						<div class="form-group" id="scrapbooktextId" style="display:none">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Enter a scrapbook* :</label>
                            <div class="col-sm-7">
                                 <input type="text" class="form-control" name="user_scrap_text" id="user_scrap_text" placeholder="Enter scrapbook here">
                            </div>
                        </div>
                    </form>
                    <div class="text-center"><button class="btn btn-success" id="item_add_to_scrapbook">ADD TO SCRAPBOOK</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reprintForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Reprint Form <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span>  </h4>
				 
            </div>
            <div class="modal-body">
                <div class="box_public_info check_box_val" hidden="">
                    <p id="archive_group_reprint_text"></p>
                </div>
                <div class="box_public_info" style="padding: 15px;">
                    <p>Your purchase includes a Restricted Use License. Read our <a href="<?php echo COPY_RIGHT_URL; ?>" target="_blank"><font color="red">Copyright and Permitted Use Policy.</font></a><br>
                        For larger orders and multiple editions, please <a id="contact_reprint_request" href="javascript:void(0);"><font color="red">contact us.</font></a>
                    </p>
                </div>
                <div class="">
                    <p>* = Required Field</p>
                    <form class="form-horizontal" name="purchase-reprint-form" id="purchase-reprint-form" method="POST" action="">
                        <input type="hidden" name="request_type" id="request_type_RP" value="RP">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="cus_first_name" id="cus_first_name" placeholder="">
                            </div>
                        </div>
					   <input type="text" id="field_reprint_form" name="field_reprint_form" value="" style="display:none">  
					   <input type="text"  name="timestamp_value" value="<?php echo time();?>" style="display:none">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="cus_last_name" id="cus_last_name" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email Address* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="cus_email" id="cus_email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Paste links here of the page(s) you wish to order* :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="cus_page_link" name="cus_page_link" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">How will these pages be used?* :</label>
                            <div class="col-sm-7">
                                <select class="form-control" name="cus_page_used" id="cus_page_used">
                                    <option value="">Please select one</option>
                                    <?php foreach ($pageUsedOptions as $key => $pageOption) { ?>
                                        <option value="<?php echo $key ?>"><?php echo $pageOption; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Comments :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="cus_comments" name="cus_comments" rows="4"></textarea>
                            </div>
                        </div> 
                    </form>
		<div class="text-center"><button id="submit-reprint-button" class="btn btn-success">SUBMIT REQUEST</button></div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="modal fade bs-example-modal-sm" id="share_item_from_front" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Share Item <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span>  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>		 
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="share_items_front" id="share_items_front" method="POST" action="">	  
			<input type="hidden" id="new_fol_id" name="new_fol_id" value="" >
                        <input type="hidden" name="sharing_item_id" id="sharing_item_id">                
		        <input type="text" id="field_share_item" name="field_share_item" value="" style="display:none">
		        <input type="text"  name="timestamp_value" id="timestamp_valueid" value="<?php echo time();?>" style="display:none">
                        <div class="clearfix"></div>
                        <?php if(isset($_SESSION['aib']['user_data']) && $_SESSION['aib']['user_data']['user_type'] =='A' ){ ?>
                            <div class="form-group">
                                <label for="" class="col-sm-4 control-label">Shared With :</label>
                                <div class="col-sm-7">
                                    <label class="container col-sm-6">Public User
                                        <input type="radio" class="shared-with" checked="checked" name="shared_type" value="public user">
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="container col-sm-6">Society Admin
                                        <input type="radio" class="shared-with" name="shared_type" value="society">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                            </div> 
                        <?php } ?>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Sender's Name* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="sharing_name" id="sharing_name" placeholder="Enter your name here">
                            </div>
                        </div>
                        <div class="form-group public-user">
                            <label for="" class="col-sm-4 control-label">Recipient Email Address* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="share_emailId" id="share_emailId" placeholder="Enter recipient email address here">
                            </div>
                        </div>
                        <div class="form-group society">
                            <label for="" class="col-sm-4 control-label">Society Admin* :</label>
                            <div class="col-sm-7">
                                <select id="selected_society" multiple name="selected_society[]" class="demo-default" autocomplete="off" placeholder="Enter society here"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Message :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="share_message" id="share_massage" placeholder="Enter your message"></textarea>
                            </div>
                        </div>
                    </form>
                    <div class="text-center"><button class="btn btn-success" type="submit" id="share_item_button">Share</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="report_user_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Report <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
	 </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="report_form_data_val" id="report_form_data_val" method="POST" action="">
                        <input type="hidden" name="user_image" id="user_image">
                        <input type="hidden" name="default_url_image_id" id="default_url_image_id">
                        <input type="hidden" name="url_image_id" id="url_image_id">
                        <div class="form-group" <?php echo $username_hide; ?>> 
                      <label class="col-xs-5 control-label">Item Url:</label>
                       <div id="" class="archive-group col-md-7">
                           <input type="text" class="form-control" name="report_item_url" id="report_item_url" value="" placeholder="" disabled="">
                      </div>
                      </div> 
                         <div class="clearfix"></div>
                        <div class="form-group">
                            <label for="" class="col-sm-5 control-label">Username:</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="report_username" id="report_username" value="" placeholder="" disabled="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-5 control-label">Reason for reporting:</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" rows="3" id="report_reason" name="report_reason"></textarea>
                            </div>
                        </div>
                    </form>
                    <div class="text-center"><button class="btn btn-success" type="submit" id="report_submit">Submit</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="connect_with_other_society_detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="">Connect with Society <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4" id="society_tree_data"></div>
                    <div class="col-md-8">
                        <div class="bgOverlay_loader"><img class="loading-img_items" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." /></div>
                        <div id="sub-group-records"></div>
                    </div>
                </div>
                <div class="text-center connect-footer">
                <input type="hidden" id="selected_item_id" name="selected_item_id" value="<?php echo $itemDataArray['item_id']; ?>"  />
                    <button class="btn btn-success" type="submit" id="society_connection_button">Submit</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$p_folder_id = 0;
$p_folder_id = $_REQUEST['folder_id'];
$loggedIn=false;
if(!empty($_SESSION['aib']['user_data']['user_id'])){
  $loggedIn=true;
}
$defaultItemId = (isset($_REQUEST['itemId']) && $_REQUEST['itemId'] != '') ? $_REQUEST['itemId'] : '';
$showScrapbookTitle = '';
if(isset($_SESSION['aib']['user_data']['user_type']) && $_SESSION['aib']['user_data']['user_type']== 'U'){
    $showScrapbookTitle = 'yes';
}
?>

<script type="text/javascript">
    $('#start').val('<?php echo $start; ?>');
    $('#total_item_page').val('<?php echo $totalPageOfItem; ?>');
    var searchString = '<?php echo $searchString; ?>';
    var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');//'<?php //echo $p_folder_id; ?>';
    var loginStatus = '<?php echo $loggedIn; ?>';
    var showScrapbookTitle = '<?php echo $showScrapbookTitle; ?>';
    var default_url_id = '<?php echo $_REQUEST['folder_id']; ?>';
    $('#default_url_image_id').val(default_url_id);
    $(document).ready(function () {
        getAllSocietyList('selected_society');
		setTimeout(function(){ jQuery('.btnrightH ').click(); }, 4000);
		//setTimeout(function(){ jQuery('.btnrightH ').click(); }, 8000);
        $(document).on('click', '.shared-with', function(){
            var selected_option = $(this).val();
            if(selected_option == 'society'){
                $('.public-user').hide();
                $('.society').show();
                $('#share_emailId').val('');
            }else{
                $("#selected_society")[0].selectize.clear();
                $('.society').hide();
                $('.public-user').show();
            }
        });
        getScrapbookRecordDetails('');
        var ref_id = '<?php echo $reference_id; ?>';
        $('#purchase-reprint-form').validate({
            rules: {
                cus_first_name: {
                    required: true
                },
                cus_last_name: {
                    required: true
                },
                cus_email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                cus_page_link: {
                    required: true,
		    LinkCorrect : /^(?:http(?:s)?:\/\/)?(?:[^\.]+\.)?archiveinabox\.com/
                },
                cus_page_used: {
                    required: true
                }
            },
            messages: {
                cus_first_name: {
                    required: "First name is required."
                },
                cus_last_name: {
                    required: "Last name is required."
                },
                cus_email: {
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                },
                cus_page_link: {
                    required: "Page link is required",
					LinkCorrect: "Please enter  archiveinabox.com url only "
                },
                cus_page_used: {
                    required: "Please select an option."
                }
            }
        });

        //validate contact form

        $('#contact_aib_form').validate({
            rules: {
                contact_first_name: {
                    required: true
                },
                contact_last_name: {
                    required: true
                },
                contact_email: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                contact_subject: {
                    required: true
                },
                contact_comments: {
                    required: true
                }
            },
            messages: {
                contact_first_name: {
                    required: "First name is required."
                },
                contact_last_name: {
                    required: "Second name is required."
                },
                contact_email: {
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                },
                contact_subject: {
                    required: "Subject is required"
                },
                contact_comments: {
                    required: "Comments is required."
                }
            }
        });
        //End contact form
		
	$("#share_items_front").validate({
            rules: {
                share_emailId: {
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                sharing_name: {
                    required: true
                }
            },
            messages: {
                share_emailId: {
                    required: "Email-ID is required.",
                    email:"Please enter correct email id"
                },
                sharing_name: {
                    required: "Please enter your name"
                }
            }
        });
		
        // SCRAPBOOK
        $("#add_scrapbook_item_form").validate({
            rules: {
                scrapbook_entry_title: {
                    required: true
                },
                user_scrap_text: { 
                    required: true,
                }
            },
            messages: {
                scrapbook_entry_title: {
                    required: "Scrapbook entry title is required"
                },
                user_scrap_text: { 
                    required: "Enter scrapbook here."
                }
            }
        });
		
        var p_folder_id = '<?php echo $p_folder_id; ?>';
        var default_item_id = '<?php echo $defaultItemId; ?>';
        getAdvertisement(p_folder_id);
        getItemArchiveProperty(p_folder_id);
        $('#tabs').tabulous({
            effect: 'scale'
        });
        $('#desktop-slider-section').show();
        $(".sapple-slider-thumb:first").css({'display': 'inline-block'});
        $('#sapple-slider-thumb-item-0').addClass('slider-active-thumb');
        var total_thumb_page = $('.sapple-slider-thumb').length;
        var total_item_count = $('.sapple-slider-item-thumb').length;
//        $('.pagination-counter .total').text(total_item_count);


        var total_items = '<?php echo $totalItems; ?>'; 
        $('.pagination-counter .total').text(total_items);
        $('#maximum_image_count').val(total_items);
        var pr_file_id = $('#slider-thumb-image-0').attr('data-pr-file-id');
        var item_type  = $('#slider-thumb-image-0').attr('data-item-type');
        var item_id = $('#slider-thumb-image-0').attr('data-item-id');
        var data_url= $('#slider-thumb-image-0').attr('data-url');
        item_id = (item_id !== '' && typeof(item_id) !== 'undefined') ? item_id : default_item_id;
        getPrimaryImageData(pr_file_id, item_type, data_url, searchString, item_id);
        getItemCustomFields(item_id,ref_id);
        if(showScrapbookTitle == 'yes'){
            getSavedScrapbookTitle(item_id);
        }
        //DESKTOP SLIDER START
        $(document).on('click','.sapple-thumb-list',function () {
            if (!$(this).parent().hasClass('slider-active-thumb')) {
                var pr_file_id = $(this).attr('data-pr-file-id');
                var tn_file_id = $(this).attr('data-tn-file-id');
                var item_type  = $(this).attr('data-item-type');
                var data_url   = $(this).attr('data-url');
                $('#url_image_id').val(tn_file_id);
                var item_id = $(this).attr('data-item-id');
                var browserUrl = window.location.href
                var res = browserUrl.split(".com/").pop(-1);
                var dataSet = getUrlVars();
                var folderId = '<?php echo $p_folder_id; ?>';
                if($('#left-right-icon-clicked').val() != 'yes'){
                    if(dataSet['itemId'] == 'undefined' || dataSet['itemId'] != item_id){
                         for(var index in dataSet) {
                            if(index != 'folder_id' && index != 'itemId'){
                                data_url +='&'+index+'='+dataSet[index];
                            }
                        }
                        window.location.href=data_url; 
                    }else{
                        var data_count = parseInt($(this).attr('data-count'));
                        $('.current').html(parseInt(data_count + 1));
                        getItemCustomFields(item_id,ref_id);
                        getPrimaryImageData(pr_file_id, item_type, data_url, searchString, item_id);
                        if(showScrapbookTitle == 'yes'){
                            getSavedScrapbookTitle(item_id);
                        }
                        //getItemOcrData(item_id);
                        var clickedThumb = $(this).attr('data-count');
                        $('.sapple-slider-item-thumb').removeClass('slider-active-thumb');
                        $('#sapple-slider-thumb-item-' + clickedThumb).addClass('slider-active-thumb');
                    }
                }else{
                    $('#left-right-icon-clicked').val('no');
                    var data_count = parseInt($(this).attr('data-count'));
                    $('.current').html(parseInt(data_count + 1));
                    getItemCustomFields(item_id,ref_id);
                    getPrimaryImageData(pr_file_id, item_type, data_url, searchString, item_id);
                    if(showScrapbookTitle == 'yes'){
                        getSavedScrapbookTitle(item_id);
                    }
                    //getItemOcrData(item_id);
                    var clickedThumb = $(this).attr('data-count');
                    $('.sapple-slider-item-thumb').removeClass('slider-active-thumb');
                    $('#sapple-slider-thumb-item-' + clickedThumb).addClass('slider-active-thumb');
                }
            }
            var selected_page = $('.slider-active-thumb').children('.itemDetailImageNumb').text()+ ' of '+ $('.total').text();
            $('.page_number').text(selected_page);
        });

        $('.right-slider-section-').click(function () {
            var current_selected = $(".banner-slider div:visible").attr('item-count');
            var current_next = parseInt(parseInt(current_selected) + 1);
            $('.sapple-slider-item').css({'display': 'none'});
            $('.sapple-slider-item').removeClass('animated fadeIn');
            if ($('#sapple-slider-item-' + current_next).length) {
                $('#sapple-slider-item-' + current_next).css({'display': 'inline-block'});
                $('#sapple-slider-item-' + current_next).addClass('animated fadeIn');
                $('.sapple-slider-thumb').css({'display': 'none'});
                $('.sapple-slider-thumb').removeClass('animated fadeIn');
                $('#sapple-slider-thumb-item-' + current_next).parent('div').css({'display': 'inline-block'});
                $('.current').text(parseInt($('#sapple-slider-thumb-item-' + current_next).parent('div').attr('thumb-parent-count')));
                $('#sapple-slider-thumb-item-' + current_next).parent('div').addClass('animated fadeIn');
            } else {
                $('#sapple-slider-item-0').css({'display': 'inline-block'});
                $('#sapple-slider-item-0').addClass('animated fadeIn');
                $('.sapple-slider-thumb').css({'display': 'none'});
                $('.sapple-slider-thumb').removeClass('animated fadeIn');
                $('#sapple-slider-thumb-item-0').parent('div').css({'display': 'inline-block'});
                $('#sapple-slider-thumb-item-0').parent('div').addClass('animated fadeIn');
                $('.current').text(parseInt($('#sapple-slider-thumb-item-0').parent('div').attr('thumb-parent-count')));
            }
            $('.sapple-slider-item-thumb').removeClass('slider-active-thumb');
            $('#sapple-slider-thumb-item-' + current_next).addClass('slider-active-thumb');
        });
        $('.left-slider-section-').click(function () {
            var current_selected = $(".banner-slider div:visible").attr('item-count');
            var current_prev = parseInt(parseInt(current_selected) - 1);
            $('.sapple-slider-item').css({'display': 'none'});
            $('.sapple-slider-item').removeClass('animated fadeIn');
            if ($('#sapple-slider-item-' + current_prev).length) {
                $('#sapple-slider-item-' + current_prev).css({'display': 'inline-block'});
                $('#sapple-slider-item-' + current_prev).addClass('animated fadeIn');
                $('.sapple-slider-thumb').css({'display': 'none'});
                $('.sapple-slider-thumb').removeClass('animated fadeIn');
                $('#sapple-slider-thumb-item-' + current_prev).parent('div').css({'display': 'inline-block'});
                $('#sapple-slider-thumb-item-' + current_prev).parent('div').addClass('animated fadeIn');
                $('.current').text(parseInt($('#sapple-slider-thumb-item-' + current_prev).parent('div').attr('thumb-parent-count')));
            } else {
                $('.sapple-slider-item:last').css({'display': 'inline-block'});
                $('.sapple-slider-item:last').addClass('animated fadeIn');
                var last_item_index = parseInt($('.sapple-slider-item:last').attr('item-count'));
                $('.sapple-slider-thumb').css({'display': 'none'});
                $('.sapple-slider-thumb').removeClass('animated fadeIn');
                $('#sapple-slider-thumb-item-' + last_item_index).parent('div').css({'display': 'inline-block'});
                $('#sapple-slider-thumb-item-' + last_item_index).parent('div').addClass('animated fadeIn');
                $('.current').text(parseInt($('#sapple-slider-thumb-item-' + last_item_index).parent('div').attr('thumb-parent-count')));
            }
            $('.sapple-slider-item-thumb').removeClass('slider-active-thumb');
            $('#sapple-slider-thumb-item-' + current_prev).addClass('slider-active-thumb');
        });
        //BANNER SECTION END
        $(document).on('click', '.maximise, .show_large_image', function () {
            $(".sapple-slider-item").each(function () {
                if ($(this).css("display") == "block") {
                    var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
                    var pr_file_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-pr-file-id');
                    var height = $(window).height();
                        $.ajax({
                            url: "services.php",
                            type: "post",
                            data: {mode: 'image_data_with_highlight', item_id: item_id,pr_file_id:pr_file_id, searchString:searchString, height: height  },
                            success: function (data) {
                               $('#large_image_container').html(data);
                              // $(data).insertAfter("#full-image-content" );
                               //$('#full-image-content').attr('src', $('#original-image-content').attr('data-src'));
                            },
                            error: function () {
                                showPopupMessage('error','Something went wrong, Please try again');
                            }
                        });
                    //var imageObj = $(this).children('div').children('img').attr('src');
                    $('.fullWidthESC').show();
                    //$('#full-image-content').attr('src', imageObj);
                    $('#full-width-image-popup').modal('show');
                    setTimeout(function () {
                        $('.fullWidthESC').hide('slow');
                    }, 2000);
                }
            });
        });
        //Maximise Section End here
        
        // Original Image view
        $(document).on('click','.popup-original-image',function(){
            var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
            var pr_file_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-pr-file-id');
            var url = "<?php echo HOST_PATH ;?>"+'view_original_image.php?item_id='+item_id+'&pr_file_id='+pr_file_id;
            if(searchString !=''){
                url = url+'&searchString='+searchString;
            }
            window.open(url, '_blank');
        });
        
        
        $('#view-more-picture').click(function () {
            $('.thumb-listing').show();
            $('#view-more-picture').hide();
            $('#hide-slider-thumb').show();
            $('#record_item_title').show();
        });
        $('#hide-more-picture').click(function () {
            $('.thumb-listing').hide();
            $('#view-more-picture').show();
            $('#hide-slider-thumb').hide();
            $('#record_item_title').hide();
        });
        //DESKTOP SLIDER END
        $('#view-more-picture').trigger('click');

        $('#slider-thumb-image-<?php echo $selectedItem; ?>').click();
        getRelatedItems(p_folder_id);
        getPublicConnections(p_folder_id);
        $('#scrapbook_entry_title').keypress(function (e) {
            var key = e.which;
            if(key == 13){ 
                $('#item_add_to_scrapbook').click();
            }
        }); 
		
	var selected_page = $('.slider-active-thumb').children('.itemDetailImageNumb').text() +' of '+ $('.total').text();
        $('.page_number').text(selected_page);
        
        //getItemOcrData($('.slider-active-thumb').find('img').attr('data-item-id'));
    });

    $(window).load(function () {
        $("#home-bg-img-title").hide();
        setTimeout(function () {
            $('#home-bg-img-inner').css("opacity", "1");
            $('#home-bg-img-inner').addClass("animated fadeIn");
        }, 800);


    });
    function getPrimaryImageData(pr_file_id, item_type, data_url, searchString= '', item_id = '') {
        var thumb_url = '<?php echo THUMB_URL; ?>';
        var image_url = '<?php echo IMAGE_PATH; ?>';
        $('.loading-div').show();
        //Start New code for image highlight
        if(pr_file_id !=''){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'image_data_with_highlight', pr_file_id: pr_file_id,item_type: item_type, data_url:data_url, searchString:searchString, item_id:item_id },
                success: function (response) {
                    $('#sapple-slider-item-0').html(response);
                    $('#sapple-slider-item-0').show();
                    $('.loading-div').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed.');
                }
        }); 
        }else{
            var noImage ='<div class="imgCenterSlider"> <img class="show_large_image" src="'+image_url+'no-image.png" alt="Slider Image" onload="largeHighLightImage()"></div>';
            $('#sapple-slider-item-0').html(noImage);
            $('#sapple-slider-item-0').show();
            $('.loading-div').hide();
        }
        //End New code for image highlight
    }
   
    $(document).on('click', '.purchase-reprint', function () {
        var reprint_text = $('#archive_group_reprint_text').text();
        if(reprint_text != ''){$('.check_box_val').show();}
        var thumb_url = '<?php echo THUMB_URL; ?>';
        var pr_file_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-pr-file-id');
        $('#full-image-purchase-reprint').attr('src', thumb_url + '?id=' + pr_file_id);
        $('#reprintForm').modal('show');
        setTimeout(function () {
            $('.fullWidthESC').hide('slow');
        }, 2000);
    });
    $(document).on('click', '.popup-purchase-reprint', function () {
        $('.loading-div').show();
        $('#full-width-image-popup').modal('hide');
        setTimeout(function () {
            $('.purchase-reprint').trigger('click');
            $('.loading-div').hide();
        }, 1000);
    });

    function getItemCustomFields(item_id,ref_id) {
      
        if (item_id) {
	    $('.loading-div').show();
            getItemDetails(item_id);
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_item_complete_details', item_id: item_id,ref_id:ref_id},
                success: function (response) {
                    $('#tabs-1').html(response);
		    $('.loading-div').hide();
                    //$("#tabs ul li:nth-child(1) a").trigger('click');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }

    function getItemDetails(item_id) {
        if (item_id) {
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_item_details', item_id: item_id},
                success: function (response) {
                    var record = JSON.parse(response);
                   // $('#record_item_title').html('<strong>Item Title: </strong>' + record.itemTitle);
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }
 

    function getItemArchiveProperty(record_id) {
        if (record_id) {
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_item_archive_group_details', item_id: record_id},
                success: function (response) {
                    var result = JSON.parse(response);
                    $('#archive_group_reprint_text').html(result.archive_request_reprint_text);
                    if(result.ebay_status =='N'){
                        $('.ebay_class').hide();
                    }else{
                        $('.ebay_class').show();
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }
	
	
	
    $(document).on('click', '#contact_reprint_request', function () {
        $('#reprintForm').modal('hide');
        $('#contactAIBReprint').modal('show');
    });
    $(document).on('click', '#submit_contact_request', function (e) {
        e.preventDefault();
        if ($("#contact_aib_form").valid()  &&  $('#field_reprint_contactus').val() =='') { 
            $('.custom-captcha_error').hide();
            $('.loading-div').show();
            var folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
            var formData = $('#contact_aib_form').serialize();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'submit_request', formData: formData, item_id: folder_id},
                success: function (response) {
                    $('.loading-div').hide();
                    var result = JSON.parse(response);
                    if (result.status == 'success') {
                        $('#contact_aib_form')[0].reset();
                        $('#contactAIBReprint').modal('hide');
                        showPopupMessage('success', result.message);
                    } else {
                        showPopupMessage('error', result.message);
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed.');
                }
            });
        }
    });
    $(document).on('click', '#add_to_scrapbook', function(){
        $('.loading-div').show();
        //var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_user_scrapbook_listing'},
            success: function (response) {
                $('.loading-div').hide();
                $('#user_scrapbook').html('');
                var result = JSON.parse(response);
                if (result.status == 'success') {
					$('#add_current_item_to_scrapbook').modal('show'); 
					if(result.data.length > 0){
						for (i = 0; i < result.data.length; i++) { 
					        $('#user_scrapbook').append('<option value="'+result.data[i].item_id+'">'+result.data[i].item_title+'</option');
						}
						$('#scrapbooktextId').hide();
						$('#scrapbookselectId').show();
					}else{
						$('#scrapbooktextId').show();
						$('#scrapbookselectId').hide();
					} 
                }else if(result.status == 'login') {
                    $('#response_message').html(result.message);
                    $('#response_message').show();
                    $('.loginPopup').trigger('click');
                }else{
                    showPopupMessage('error', result.message);
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed.');
            }
        });
    });
    $(document).on('click','#back_to_scrapbook',function(){
        var scrpbookBackUrl = '<?php echo $_REQUEST['scrapbook_item'] ?>';
        if(scrpbookBackUrl == 'yes'){
            window.location.assign('home.php?folder_id='+'<?php echo $_REQUEST['previousId']; ?>');
        }else{
            window.location.assign('people.php?folder_id='+'<?php echo $_REQUEST['previousId']; ?>');
        }
    });
    $(document).on('click','#item_add_to_scrapbook',function(){
        if($('#add_scrapbook_item_form').valid()){
            $('.loading-div').show();
            var item_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
            var item_parent = '<?php echo $p_folder_id; ?>';
            var entry_title = $('#scrapbook_entry_title').val();
            var scrapbook_id= $('#user_scrapbook').val(); 
            var scrap_name = $('#user_scrap_text').val();
            var timestamp_value = $('#scrap_timestamp').val();
            $.ajax({
                url: "services.php",
                type: "post",
                data: { mode: 'add_item_to_scrapbook',item_id: item_id, entry_title: entry_title, scrapbook_id: scrapbook_id, item_parent: item_parent,scrap_name: scrap_name , timestamp_value:timestamp_value },
                success: function (response) {
                    var result = JSON.parse(response);
                    $('.loading-div').hide();
                    if (result.status == 'success') {
                        $('#add_scrapbook_item_form')[0].reset();
                        $('#add_current_item_to_scrapbook').modal('hide');
                        showPopupMessage('success', result.message);
                    }else if(result.status == 'login') {
                        showPopupMessage('error', result.message);
                        setTimeout(function(){
                            $('.loginPopup').trigger('click');
                        },1000);
                    }else{
                        showPopupMessage('error', result.message);
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Previous request not completed.');
                }
            });
        }
    });
	 
    //Comment Section 
    function listCommentThreads(parent_folder_id,action) {
        
        if (parent_folder_id) { 
            //$('#tabs-2').html("Fetching Comments,Please wait!");
            $(".borderTopCmnt").show();
            $("#loadingComment").show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'list_comment_threads', parent_folder_id: parent_folder_id,action:action},
                success: function (response) {
                    $('#tabs-2 div.innerComment').remove();
                    $('#tabs-2 div[id^=comment-]').remove();
                    $("#loadingComment").hide();
                    $('#tabs-2').prepend(response);
                    
                    $('.headingComment').remove();
                    $('#tabs-2').prepend('<h4 class="headingComment">Comments</h4>');
                    //$("#tabs ul li:nth-child(1) a").trigger('click');
                    setTimeout(function(){ $(".viewAll").removeClass("hidescale");},500);
                    if($('#tabs-2 div[id^=comment-]').length<=0){
                      $(".borderTopCmnt").hide();
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }
    
    function deleteCommentFromThread(obj_id){
        if (obj_id) {            
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'delete_comment_from_thread', obj_id: obj_id},
                success: function (response) {
                    response=jQuery.parseJSON(response);
                    $('#tabs-2 div[id^=comment-'+response.item_id+']').next("div.innerComment").remove();
                    $('#tabs-2 div[id^=comment-'+response.item_id+']').remove();
                    if($('#tabs-2 div[id^=comment-]').length<=0){
                      $(".borderTopCmnt").hide();
                    }
                    //$("#tabs ul li:nth-child(1) a").trigger('click');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }
    function addCommentToThread(obj) {
        
        var parent_thread=obj.itemId;
        var comment_text=obj.reply;
        
        if (parent_thread) {            
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'add_comment_to_thread', parent_thread: parent_thread,comment_text: comment_text},
                success: function (response) {
                    
                    var response= jQuery.parseJSON(response);
                    
                    var isFirstInnerComment=false;
                    //check is parent has inner comments,or current comment is the first comment
                    var ptObject=$("#comment-"+response.parent_thread);
                    if(ptObject.next("div.innerComment").length<=0){ 
                        isFirstInnerComment=true;
                    }
                    
                     var html = appendInnerCommentHTML(response,isFirstInnerComment);                    
                     if(isFirstInnerComment===false){
                         ptObject.next("div.innerComment").append(html);
                     }else{
                         ptObject.after(html);
                     }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }
    function appendInnerCommentHTML(obj,isFirstInnerComment){
        
           var html='<div id="comment-' + obj.item_id + '" >';
           html+='<span class="nameUser">';
           html+='<!--<span class="glyphicon glyphicon-user" aria-hidden="true"></span>-->\n\
            <img class="userPic" src="public/images/avatar-1.png" alt="" />&nbsp;' + obj.userName + '</span>';
           html+=' <span class="dateCmnt">- ' + obj.time + '</span>';
           html+='<p id="comment_list_'+obj.item_id+'"> ' + obj.comment_text + '</p>';
           html+='<span>';
           html+='<a href="javascript:void(0);" class="replyBtn-' + obj.item_id + '">Reply</a> '; 
           html+='<a href="javascript:void(0);" class="deleteBtn-' + obj.item_id + '"> Delete</a>';
           html+='<a href="javascript:void(0);" class="edit-comment" data-comment_id="'+obj.item_id+'"> Edit</a>';
           html+='</span>';
           html+='<div id="edit_comment_'+obj.item_id+'" style="display:none;">';
           html+='<textarea class="widthTextarea submitEditComments" data-itemId="'+obj.item_id+ '">'+obj.comment_text+'</textarea>';
           html+='</div>';
           html+=' <div class="replyCmnt replyBox-' + obj.item_id + '" style="display:none;">';
           html+='<textarea placeholder="Write a reply..."  class="widthTextarea replyOnUserComment" data-itemId="' + obj.item_id + '"></textarea>';
           html+='</div>';
           html+=' <span class="devideCmnt"></span>';
           html+='</div>';
           if(isFirstInnerComment==true){
               
               innerCommentHtml='<div class="innerComment lastComment" style="padding-left:50px">';
               innerCommentHtml+=html;
               innerCommentHtml+='</div>';
               return innerCommentHtml;
           }else{
               return html;
           }
               
           
    }
    $("document").ready(function(){ 
        //Onload
         var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
         //alert(parent_folder_id);
         //listCommentThreads(parent_folder_id,'onload');
         
         //********Change Comment on Items ******//
         $("img[id^=slider-thumb-image-]").on("click",function(){
             var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
             $('#tabs-2 div.innerComment').remove();
             $('#tabs-2 div[id^=comment-]').remove();
             listCommentThreads(parent_folder_id,'onload');
         });
          //********Change Comment on Items ******//
         
         setTimeout(function(){$(".viewAll").removeClass("hidescale");},500);
        $("#txtUserComments").keyup(function(e){
            var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
            if (e.keyCode == 13 && jQuery.trim(this.value)!="") {                   
                var comment=this.value;
                this.value="";
                $.ajax({
                    url: "services.php",
                    type: "post",
                    data: {mode: 'create_comment_thread', parent_folder_id: parent_folder_id,comment: comment},
                    success: function (response) { 
                        //listCommentThreadsPost(parent_folder_id,"hit");
                         var response= jQuery.parseJSON(response);
                         var html = appendInnerCommentHTML(response,false); 
                         if($("#tabs-2 div[id^=comment-]:last").closest(".innerComment").length>0){
                             //$("#tabs-2 div.lastComment:last").after(html);
                             $("#tabs-2 >.innerComment:last").after(html);
                         }else{
                             if($("#tabs-2 div[id^=comment-]").length>0){
                               $("#tabs-2 div[id^=comment-]:last").after(html);
                             }else{
                               $("#tabs-2").prepend(html);
                               $('.headingComment').remove();
                               $('#tabs-2').prepend('<h4 class="headingComment">Comments <button class="btn btn-success pull-right" id="load_comments_details_page">View Comments</button></h4>');
                             }
                         }
                         
                         $(".borderTopCmnt").show();
                         $("#com-msg").hide();
                         $("#com-msg").hide();
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again');
                    }
                });
            }
        });
        
        $("#post-your-comments").on("click",function(){
            if (jQuery.trim($("#txtUserComments").val())!="") {                   
                var comment=$("#txtUserComments").val();
                $("#com-msg").show();
                var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
                $("#txtUserComments").val("");
                $.ajax({
                    url: "services.php",
                    type: "post",
                    data: {mode: 'create_comment_thread', parent_folder_id: parent_folder_id,comment: comment},
                    success: function (response) { 
                        //listCommentThreadsPost(parent_folder_id,"hit");
                         var response= jQuery.parseJSON(response);
                         var html = appendInnerCommentHTML(response,false);                    
                         if($("#tabs-2 div[id^=comment-]:last").closest(".innerComment").length>0){
                             //$("#tabs-2 div.lastComment:last").after(html);
                             $("#tabs-2 >.innerComment:last").after(html);
                         }else{
                             if($("#tabs-2 div[id^=comment-]").length>0){
                               $("#tabs-2 div[id^=comment-]:last").after(html);
                             }else{
                               $("#tabs-2").prepend(html);
                               $('.headingComment').remove();
                               $('#tabs-2').prepend('<h4 class="headingComment">Comments</h4>');
                             }
                         }
                         $(".borderTopCmnt").show();
                         $("#com-msg").hide();
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again');
                    }
                });
            }
        });
        
        if(loginStatus==false){
            $("#txtUserComments").attr("placeholder","Login to comment...");
            $("#txtUserComments").on("focus",function(){ $(".loginPopup").trigger("click")});
        }
        
        
        $(document).on("click","a[class^=replyBtn-]", function () { //alert($("div.replyBox-" + itemId).is(':hidden'));
            var itemId = $(this).attr("class").split("-").pop();
            if ($("div.replyBox-" + itemId).is(':hidden') == false) {
                $("div.replyBox-" + itemId).hide();
            } else {
                $("div.replyBox-" + itemId).show();
            }
        });

 
    //****** Post Reply on items *************
    $(document).on("keyup",".replyOnUserComment", function (e) {
        var obj = {};
        if (e.keyCode == 13 && jQuery.trim(this.value) != "") {
            obj.itemId = $(this).data("itemid");
            obj.reply = this.value;
            addCommentToThread(obj);
            this.value = "";
            $("div.replyBox-" + obj.itemId).hide();
        }
    });
    
    $(document).on("click","a[class^=deleteBtn-]", function (e) {        
        var itemId = $(this).attr("class").split("-").pop();
           deleteCommentFromThread(itemId);
    });
    
    $(document).on('click', '#share_front_items', function () {
        $('#share_item_from_front').modal('show');
        $('.loading-div').show();
        var record_id = '<?php echo $itemData['item_id'] ?>';
	var new_fol_id = $('.listing li:nth-last-child(2)').attr("data-folder-id");  
	$('#new_fol_id').val(new_fol_id); 
        $('#sharing_item_id').val(record_id);
	$('#share_item_from_front').modal('show');
        $('.loading-div').hide();
    }); 
    var p_folder_id = '<?php echo $p_folder_id; ?>';
    setTimeout(function(){ 
        getHistoricalConnection(p_folder_id);
        getHistoricalConnectionTop(p_folder_id);
    }, 4000);
});

$(document).on('click', '#load_comments_details_page', function(){
    var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');
    $('.borderTopCmnt').show();
    listCommentThreads(parent_folder_id,'onload');
});
    
    function getRelatedItems(record_id){
        if(record_id){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_related_items', record_id: record_id},
                success: function (data) {
                    $('#related_content_section').html(data);
					if($('#relatedContentNo').html() > 0){
						$('.relatedContentNumbr').html($('#relatedContentNo').html());
					}
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again');
                }
            });
        }
    } 
    
    function getHistoricalConnection(item_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_historical_connection_item_details', folder_id: item_id},
            success: function (data) {
                if(data){
                    $('#historical_connection_data').html(data);
                    if($('#historical_connection_count').val() !== '0'){
                        $('.historical_connection').show();
                        $('.show-historical-connection').show();
                    }
                }
            }
        });
    }
    
    function getHistoricalConnectionTop(item_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_historical_connection_item_details_top', folder_id: item_id},
            success: function (data) {
                if(data){
                    $('#historical_connection_data_top').html(data);
                    if($('#historical_connection_count').val() !== '0'){
                        $('.historical_connection_top').show();
                        $('.show-historical-connection').show();
                    }
                }
            }
        });
    }
    
	function getPublicConnections(record_id){
        if(record_id){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_public_connections', record_id: record_id},
                success: function (data) {
                    $('#public_connection_content_section').html(data);
					if($('#publicConnectionNo').html() > 0){
						$('.publicConNumbr').html($('#publicConnectionNo').html());
					}
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again');
                }
            });
        }
    }
    function get_public_username(email){
         var data = [];
         $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_public_user_email'},
            success: function (response) {
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    data.push(record[i].user_login);
                }
                var ms1 = $('#sare_item_username').magicSuggest({data: data, value: email});
                ms1.clear();
                ms1.setData(data);
                ms1.setValue(email);
                $('#share_item_from_front').modal('show');
                  $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again');
            }
        });
    }
	
	
	 $( function() {
    $( "#tabs" ).tabs();
  } );
	
	
$(function() {
    var flag=0;
    $(".btnright").click(function() {
        $('#tab1').show();
        $('#tab2').hide();
        $('.slidediv').animate({"right": "0px"});
        $('.btnrightP').hide();
        $('.btnrightH').hide();
        $('.imgCenterSlider').animate({"left": "30%"});
        $('.imgCenterSlider img').animate({"max-height": "250px"});
        $('.small-display').show();
        $('.main-display').hide();
        if(flag==1){
            $('.imgCenterSlider').animate({"left": "50%"});
            $('.imgCenterSlider img').animate({"max-height": "480px"});
            $('.slidediv').animate({"right": "-500px"}); 
            $('.btnrightP').delay(800).show(0); 
            $('.btnrightH').delay(800).show(0);
            $('.small-display').hide();
            $('.main-display').show();
            flag=0;
        }else{
              flag=1;
        }
    });
});


$(function() {
    var flag=0;
    $(".btnrightP").click(function() {
        $('#tab3').show();
        $('#tab4').hide();
        $('.slidedivP').animate({"right": "0px"});
        $('.btnright').hide();
        $('.btnrightH').hide();
        $('.imgCenterSlider').animate({"left": "30%"});
        $('.imgCenterSlider img').animate({"max-height": "250px"});
        $('.small-display').show();
        $('.main-display').hide();
        if(flag==1){
            $('.imgCenterSlider').animate({"left": "50%"});
            $('.imgCenterSlider img').animate({"max-height": "480px"});
            $('.slidedivP').animate({"right": "-500px"});
            $('.btnright').delay(800).show(0);
            $('.btnrightH').delay(800).show(0);
            flag=0;
            $('.small-display').hide();
            $('.main-display').show();
        }else{
            flag=1;
        }
    });
});
  // Show the first tab and hide the rest
$('#tabs-nav li:first-child').addClass('active');
$('.tab-content').hide();
$('.tab-content:first').show();

// Click function
$('#tabs-nav li').click(function(){
  $('#tabs-nav li').removeClass('active');
  $(this).addClass('active');
  $('.tab-content').hide();
  
  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});

  // Show the first tab and hide the rest
$('#tabs-navP li:first-child').addClass('active');
$('.tab-contentP').hide();
$('.tab-contentP:first').show();

// Click function
$('#tabs-navP li').click(function(){
	
  $('#tabs-navP li').removeClass('active');
  $(this).addClass('active');
  $('.tab-contentP').hide();
  
  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});
$('#linkPubConBtn').click(function(){
 	//var user_top_folder='<?php echo $_SESSION['aib']['user_data']['user_top_folder'];?>';
 	//var user_id='<?php echo $_SESSION['aib']['user_data']['user_id'];?>';
 	var item_id='<?php echo $_REQUEST['folder_id'];?>';
	var p_folder_id = '<?php echo $p_folder_id; ?>';
	$.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'link_user_public_connection',item_id:item_id},
            success: function (response) {
				
				obj = jQuery.parseJSON(response);
				showPopupMessage(obj.status, obj.message);
				getPublicConnections(p_folder_id);
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed.');
            }
        });
        
  
});
 function getScrapbookRecordDetails(record_id){
          $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'check_scrapbook_record',record_d:record_id},
            success: function (response) {
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed.');
            }
        });
        
    }
	
	
	$(document).on("click" ,".dotInline",function(){
            var count = $(this).attr('data-count');
            $('.report-user').hide();
            $("#report_user_"+count).toggle();
        });
		
        $(document).on("click" ,".dotInline_grid",function(){
            var count = $(this).attr('data-count-grid');
            $('.report-user_grid').hide();
            $("#report_user_grid_"+count).toggle();
        });
		
		
	$(document).on("click",".report-user_grid",function(){
              $('.loading-div').show();
                var rep_username = '<?php echo $login_username_rep; ?>';
                if(rep_username =='' || rep_username =='null'){
                      $('.loading-div').hide();
                      $('.loginPopup').trigger('click');
                }else{
                    var username = $(this).attr('report-username');
                    $('#report_username').val(username);
                    var item_url = window.location.href;
                    $('#report_item_url').val(item_url);
                    $('.loading-div').hide();
                    var image = $(this).attr('report_image');
                    $('#user_image').val(image);
                    $('#report_user_modal').modal('show'); 
                }
		});
		
	
	$(document).on("click" ,".report-user",function(){
                $('.loading-div').show();
                var rep_username = '<?php echo $login_username_rep; ?>';
                if(rep_username =='' || rep_username =='null'){
                      $('.loading-div').hide();
                      $('.loginPopup').trigger('click');
                }else{
                    var username = $(this).attr('report-username');
                    $('#report_username').val(username);
                    var item_url = window.location.href;
                    $('#report_item_url').val(item_url);
                    $('.loading-div').hide();
                    var image = $(this).attr('report_image');
                    $('#user_image').val(image);
                    $('#report_user_modal').modal('show'); 
                }
		});
        $(document).on("click","#report_submit",function(){
                       var item_url = $('#report_item_url').val();
                       var username = $('#report_username').val();
                       var userImage = $('#user_image').val();
                       var notifierFormData = $('#report_form_data_val').serialize();
                        $('.loading-div').show();
                       $.ajax({
                        url: "services.php",
                        type: "post",
                        data: {mode: 'report_to_user', formData: notifierFormData,item_url:item_url,username:username,userImage:userImage},
                        success: function (response) {
                                var record = JSON.parse(response);
                                $('.loading-div').hide();
                                showPopupMessage(record.status, record.message);
                                if (record.status == 'success') {
                                    setTimeout(1000);
                                 $('#report_form_data_val')[0].reset();   
                                }
                                $('#report_user_modal').modal('hide');

                        },
                        error: function () {
                            showPopupMessage('error', 'Something went wrong, Please try again');
                        }
                });
        });        
$(document).on('click','#contact_archive_owner', function(){
    var archive_id = $(this).attr('data-owner_id');
    var itemIdHtml = '<input type="hidden" name="item_id" id="item_id" value="'+archive_id+'">';
    if(!$('#item_id').length){
        $(itemIdHtml).insertAfter('#front_contact_us_request_type');
    }
    $('.front-contact-us').trigger("click");
});

$(document).on('click', '.edit-comment', function(){
    var comment_id = $(this).attr('data-comment_id');
    $('#edit_comment_'+comment_id).show();
});

$(document).on("keyup",".submitEditComments", function (e) {
        var obj = {};
        if (e.keyCode == 13 && $.trim(this.value) != "") {
            var comment_id = $(this).attr('data-itemid');
            var comments   = $.trim(this.value);
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'update_comments_by_id', comment_text: comments, comment_id: comment_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status == 'success'){
                        $('#edit_comment_'+comment_id).hide();
                        $('#comment_list_'+comment_id).text(comments);
                    }else{
                        showPopupMessage(response.status, response.message);
                    }
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    });
    
$(document).on('click', '.update-item-fields', function(){
    var field_id = $(this).attr('data-field-id');
    $(this).hide();
    if($(this).attr('data-type') == 'fields'){
        $('#update_item_fields_'+field_id).show();
    }else if($(this).attr('data-type') == 'tags'){
        $('#update_item_fields_tags').show();
    }else{
        $('#update_original_item_title').show();
    }
});

$(document).on('click', '.cancel_field_value', function(){
    var field_id = $(this).attr('data-field-id');
    if($(this).attr('data-type') == 'fields'){
        $('#update_item_fields_'+field_id).hide();
        $('#item_fields_'+field_id).show();
    }else if($(this).attr('data-type') == 'tags'){
        $('#update_item_fields_tags').hide();
        $('#item_fields_tags').show();
    }else{
        $('#update_original_item_title').hide();
        $('#original_item_title').show();
    }
});

$(document).on('click', '.update_field_value', function(){
    var data_type = $(this).attr('data-type');
    var item_id       = '';
    var field_id      = '';
    var updated_value = '';
    if(data_type == 'fields'){
        field_id = $(this).attr('data-field-id');
        item_id  = $('#edit_field_'+field_id).attr('data-item_id');
        updated_value = $('#edit_field_'+field_id).val();
        $('#fields_loader_'+field_id).show();
    }else if(data_type == 'tags'){
        item_id  = $('#edit_field_tags').attr('data-item_id');
        updated_value = $('#edit_field_tags').val();
        $('#tag_loader').show();
    }else{
        item_id  = $('#edit_item_title').attr('data-item_id');
        updated_value = $('#edit_item_title').val();
        $('#item_loader').show();
    }
    $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'update_item_fields_tags_data', data_type: data_type, item_id: item_id, field_id: field_id, updated_value: updated_value },
        success: function (response) {
            var record = JSON.parse(response);
            if(record.status == 'success'){
                if(data_type == 'fields'){
                    $('#update_item_fields_'+field_id).hide();
                    $('#item_fields_'+field_id+' span').text(updated_value);
                    $('#item_fields_'+field_id).show();
                    $('#fields_loader_'+field_id).hide();
                }else if(data_type == 'tags'){
                    $('#update_item_fields_tags').hide();
                    $('#item_fields_tags span').text(updated_value);
                    $('#item_fields_tags').show();
                    $('#tag_loader').hide();
                }else{
                    $('#update_original_item_title').hide();
                    $('#original_item_title label').text(updated_value);
                    $('#original_item_title').show();
                    $('#item_loader').hide();
                }
            }else{
                showPopupMessage(response.status, response.message);
            }
        },
        error: function () {
            showPopupMessage('error', 'Something went wrong, Please try again');
        }
    });
});

function getSavedScrapbookTitle(item_id){
    if(item_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_saved_scrapbook_title',item_id: item_id},
            success: function (response) {
                if(response != ''){
                    $('#record_item_title').html('Saved in Scrapbook as "' + response+'"');
                }else{
                    $('#record_item_title').html('');
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed.');
            }
        });
    }
}
function largeHighLightImage(){
    $('.main-display').css('display','block');
    $('.zoomBtn').css('display','block');
}

 $(document).on('click','#zoom_in',function(){
    var image_width = $('#full-image-content').width();
    var image_height = $('#full-image-content').height();
    if($('.high-light-prop').attr('search_text') !=''){
       $('#full-image-content').css('max-width','none');
       $('#full-image-content').css('max-height','none');
   }
    var percrntage_image_width = (parseFloat($('.zoomDiv').width())*10)/100;
    image_width = parseFloat(image_width) + parseFloat(percrntage_image_width);
    var percrntage_image_height = (parseFloat( $('.zoomDiv').height(),10)*10)/100;
    image_height = parseFloat(image_height) + parseFloat(percrntage_image_height);
    if(parseInt($('.zoomDiv').attr('full-image-height')) >= image_height && parseInt($('.zoomDiv').attr('full-image-width')) >= image_width ){
        $('#full-image-content').height(image_height);
        $('#full-image-content').width(image_width);
        var i =0;
        $('.high-light-prop').each(function(){
            var left_pos = $(this).attr('left');
            left_pos = parseFloat(left_pos) + parseFloat($('.high-light-prop-fixed-'+i).attr('left')*10)/100 ;
            var top_pos = $(this).attr('top');
            top_pos = parseFloat(top_pos) + parseFloat(($('.high-light-prop-fixed-'+i).attr('top')*10)/100);
            var hl_width = $(this).attr('width');
            hl_width = parseFloat(hl_width) + parseFloat(($('.high-light-prop-fixed-'+i).attr('width')*10)/100);
            var hl_height = $(this).attr('height');
            hl_height = parseFloat(hl_height) + parseFloat(($('.high-light-prop-fixed-'+i).attr('height')*10)/100);

            $(this).attr("left",left_pos);
            $(this).attr("top",top_pos);
            $(this).attr("width",hl_width);
            $(this).attr("height",hl_height);
            $('.full-image-highlight-'+i).css({top:top_pos , left:left_pos,position:'absolute'});
            $('.full-image-highlight-'+i).width(hl_width).height(hl_height); 

        i++;
    })  
        
    }
});
$(document).on('click','#zoom_out',function(){
    var image_width = $('#full-image-content').width();
    var percrntage_image_width = (parseFloat( $('.zoomDiv').width())*10)/100;
    image_width = parseFloat(image_width) - parseFloat(percrntage_image_width);
    var image_height = $('#full-image-content').height();
    var percrntage_image_height = (parseFloat($('.zoomDiv').height())*10)/100;
    image_height = parseFloat(image_height) - parseFloat(percrntage_image_height);
    if($('.zoomDiv').height() <= image_height && $('.zoomDiv').width() <= image_width ){
        $('#full-image-content').height(image_height);
        $('#full-image-content').width(image_width);
        var i =0;
        $('.high-light-prop').each(function(){
            var left_pos = $(this).attr('left');
            left_pos = parseFloat(left_pos) - parseFloat($('.high-light-prop-fixed-'+i).attr('left')*10)/100 ;
            var top_pos = $(this).attr('top');
            top_pos = parseFloat(top_pos) - parseFloat(($('.high-light-prop-fixed-'+i).attr('top')*10)/100);
            var hl_width = $(this).attr('width');
            hl_width = parseFloat(hl_width) - parseFloat(($('.high-light-prop-fixed-'+i).attr('width')*10)/100);
            var hl_height = $(this).attr('height');
            hl_height = parseFloat(hl_height) - parseFloat(($('.high-light-prop-fixed-'+i).attr('height')*10)/100);

            $(this).attr("left",left_pos);
            $(this).attr("top",top_pos);
            $(this).attr("width",hl_width);
            $(this).attr("height",hl_height);
            $('.full-image-highlight-'+i).css({top:top_pos , left:left_pos,position:'absolute'});
            $('.full-image-highlight-'+i).width(hl_width).height(hl_height); 
        i++;
    })  
    }
});
function getItemOcrData(item_id){
    if(item_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_ocr_text_value',item_id: item_id},
            success: function (response) {
                var result = JSON.parse(response);
                var ocr_data = result.data;
                if(result.status == 'success'){
                    if(ocr_data !=''){
                        $('.add_ocr_data').html('');
                        $('.ocr_data_div_value').css('display','block');
                        var i;
                        for(i=0; i<ocr_data.length; i++){
                           $('.add_ocr_data').append(ocr_data[i].value); 
                        }
                    }else{
                        $('.add_ocr_data').html('');
                        $('.ocr_data_div_value').css('display','none');
                    } 
                }
            },
            error: function () {
                showPopupMessage('error', 'Previous request not completed.');
            }
        });
    }
}
function getUrlVars(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++){
        hash = hashes[i].split('=');
        vars[hash[0]] = hash[1];
    }
    return vars;
}
</script> 