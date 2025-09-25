<?php
$selectedItem = 0;

$reference_id=(isset($_REQUEST['reference_id']) && $_REQUEST['reference_id'] != '') ? $_REQUEST['reference_id'] : '';
if($_REQUEST['flg']==1){$page = 'people.php';}else{$page = 'home.php';}
if($_REQUEST['flg']!=2){$scrapbook_hide = 'hidden=""';}
if(isset($itemData['properties']['ebay_url']) && !empty($itemData['properties']['ebay_url'])){
    $image_name = $itemData['properties']['ebay_sale_options'].'.png';
    $ebay_url = addhttp($itemData['properties']['ebay_url']);
    $ebay_field_hide = "";
    $record_date = strtotime($itemData['properties']['ebay_record_date']);
    $now = time(); // or your date as well
    $datediff = $now - $record_date;
    $total_day = round($datediff / (60 * 60 * 24));
    $ebay_link_hide = '';
    if($total_day > 7){
        $ebay_link_hide = 'hidden'; 
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
$printImagehide ='';
if(isset($_SESSION['aib']['user_data'])&& !empty($_SESSION['aib']['user_data'])){
 if($_SESSION['aib']['user_data']['user_type']=='U'){$printImagehide = 'hidden';}   
}
?>


<div class="">
    <div class="col-md-12 project-bg-img home-bg-img-animate-new" style="opacity:1;" id="home-bg-img-inner">
        <div class="topHeader">
            <div id="desktop-slider-section" style="display:none">
                <div class="sapple-slider">
                    <div class="sapple-slider-banner">
                        <div class="banner-slider">
                            <div class="sapple-slider-item" id="sapple-slider-item-0" item-count="0"></div> 
                            <input type="hidden" name="maximum_image_count" id="maximum_image_count" value="<?php echo count($apiResponse['info']['records']); ?>">
                          <?php  if($itemData['properties']['_url']){ ?><a href="<?php echo $itemData['properties']['_url']; ?>" target="_blank" > <button class="btn btnExternal">External Url</button></a> <?php } ?>
                          
                          
                          
                          <div class="contentdiv">
<!--<span id="btnright" style="position:absolute; top:50%; right:0; z-index:1;">Lorem Ipsum</span>-->
<div class="slidediv">
<div class="tabs">
<span id="" class="relatedImage btnright"><img class="" src="public/images/relatedContent.png" alt=""></span>
  <ul id="tabs-nav">
    <li><a href="#tab1"><img src="public/images/list-icon.png" alt=""></a></li>
    <li><a href="#tab2"><img src="public/images/grid-view.png" alt=""></a></li>
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
<!--<span id="btnright" style="position:absolute; top:50%; right:0; z-index:1;">Lorem Ipsum</span>-->
<div class="slidedivP">
<div class="tabs">
<span id="" class="relatedImage btnrightP"><img class="" src="public/images/publicConnection.png" alt=""></span>
  <ul id="tabs-navP">
    <li><a href="#tab3"><img src="public/images/list-icon.png" alt=""></a></li>
    <li><a href="#tab4"><img src="public/images/grid-view.png" alt=""></a></li>
  </ul> <!-- END tabs-nav -->
  <div id="tabs-content">
    <div id="tab3" class="tab-contentP">
               <ul class="imageDisplay">
               <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                   <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                 <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                   <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                    <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                 <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                    <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                 <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                   <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                    <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                 <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                   <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                <h3>Paul Jeffko</h3>
                                    </a></li>
                                    <li><a data-folder-id="<?php echo $itemDataArray['item_id']; ?>" href="people_profile.php?folder-id=<?php echo $itemDataArray['item_id']; ?>">
                                    <div class="ch-item">

                                    <div class="ch-info">
                                            <div class="ch-info-front" style="background-image:url(public/images/Paul-Jeffko.jpg);"></div>
                                            <div class="ch-info-back">
                                                   <h3>Paul Jeffko</h3>
                                    </div>
                                </div>
                                </div>
                                <h3>Paul Jeffko</h3>
                                    </a></li>
               
               </ul>
               <div class="clearfix"></div>
    </div>
    <div id="tab4" class="tab-contentP">
              <ul class="contentRelated">
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              <li><a href="#"><img class="imgConnection" src="public/images/avatar-1.png" alt=""><span>Granite Falls Mining</span></a></li>
              </ul>
               <div class="clearfix"></div>
               </div>
  </div> <!-- END tabs-content -->
</div> <!-- END tabs -->
</div>
<div>

</div>
</div>
                          
                          
                          
                        </div>
                        <div style="text-align:center;"><img  id="view-more-picture" src="<?php echo IMAGE_PATH . 'view-more-pictures.png'; ?>"></div>
                        <div class="thumb-section">
                        <div  class="left450"  <?php echo $ebay_field_hide; echo $ebay_link_hide; ?>><a href="<?php echo $ebay_url; ?>" class="ebay_class" target="_blank"><img src="<?php echo IMAGE_PATH . $image_name; ?>" alt="ebay-logo" class="" /></a></div>
                            <div id="record_item_title"></div>
                            <div id="hide-slider-thumb" style="text-align:center; display:none; margin-top: -14px;"><img style="cursor:pointer;" id="hide-more-picture" src="<?php echo IMAGE_PATH . 'hide-slider.png'; ?>"></div>
                            <div class="thumb-listing" style="display:none;" >
                                <div class="sapple-slider-thumb" id="thumb-list-1" thumb-parent-count="1">
                                    <?php
                                    if (count($apiResponse['info']['records']) > 0) {
                                        $item_count = 0;
                                        $indexCount = 1;

                                        foreach ($apiResponse['info']['records'] as $itemDataArray) {
                                            if ($itemDataArray['item_id'] == $itemId) {
                                                $selectedItem = $item_count;
                                            }
                                            ?>
                                            <div class="sapple-slider-item-thumb" id="sapple-slider-thumb-item-<?php echo $item_count; ?>">
                                                
                                                <?php if (isset($itemDataArray['tn_file_id']) && $itemDataArray['tn_file_id'] != '') { ?>
                                                    <img data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-pr-file-id="<?php echo $itemDataArray['pr_file_id']; ?>" data-tn-file-id="<?php echo $itemDataArray['tn_file_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo THUMB_URL . '?id=' . $itemDataArray['tn_file_id']; ?>" alt="slider-image" />
                                                <?php } else { ?>
                                                    <img data-item-id="<?php echo $itemDataArray['item_id']; ?>" data-pr-file-id="<?php echo $itemDataArray['pr_file_id']; ?>" data-tn-file-id="<?php echo $itemDataArray['tn_file_id']; ?>" class="sapple-thumb-list" data-count="<?php echo $item_count; ?>" id="slider-thumb-image-<?php echo $item_count; ?>" src="<?php echo IMAGE_PATH . 'no-image.png'; ?>"/>
                                                <?php } ?>
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
                                <div class="purchase-reprint" <?php echo $printImagehide; ?> ><button class="btn reprintBtn">Purchase Reprint</button><img height="20" src="<?php echo IMAGE_PATH . 'reprintIcon.png'; ?>" alt="" class="reprintIcon" /></div>
                                <div class="left-arrow-section"><i class="left-arrow"></i></div>
                                <div class="right-arrow-section"><i class="right-arrow"></i></div>
                                <div class="pagination-counter">
                                    <span class="counter"><sup class="current">1</sup><img src="<?php echo IMAGE_PATH . 'line.png'; ?>"><sub class="total">1</sub></span>
                                </div>
                                <div class="maximise"><img height="30" src="<?php echo IMAGE_PATH . 'maximise.png'; ?>" alt=""></div>
                                <div id="share_front_items" class="share"><button data-item-id-value="<?php echo $itemDataArray['item_id']; ?>" data-target=".bs-example-modal-sm" data-toggle="modal" class="btn reprintBtn">Share</button></div>
                                <div id="back_to_scrapbook" <?php echo $scrapbook_hide; ?>><button class="btn reprintBtn" style=" background-color: #fbd42f ;color: #06335a;">Back to scrapbook <img height="20" src="<?php echo IMAGE_PATH . 'reprintIcon.png'; ?>" alt="" class="reprintIcon" /></button></div>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div>
        <ul class="listing">
            <li><a href="javascript:void(0);"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
            <?php $arrayKeys=array_keys($treeDataArray);
            foreach ($treeDataArray as $key => $treeData) {
                ?>
                <?php if (end($arrayKeys) == $key) { ?> 
                    <li><a href="javascript:void(0);"><?php echo $treeData['item_title']; ?></a></li>
                <?php } else { ?>
                    <li data-folder-id="<?php echo $treeData['item_id']; ?>"><a href="<?php echo $page;?>?folder_id=<?php echo $treeData['item_id']; ?>"><?php echo $treeData['item_title']; ?></a></li>
                <?php }
            }
            ?>
        </ul>
    </div>
    <div class="bgYellow">

        <div class="rightBox">

            <div>
                <div id="tabs_container">
                    <div id="tabs-1">
                    </div>

                    <div id="tabs-2">
                        <h4 class='headingComment'>Comments</h4>                    
                        <span id="com-msg" style="display:none">Your comment has been posted.</span>
                        <span id="loadingComment" style="display:none; padding-bottom:10px;"><img style="width:20px" src="public/images/loading.gif" alt="Loading..."> We are fetching previous comments...</span>
                        <div class="borderTopCmnt"></div>
                        <img class="userPic postUser" src="public/images/avatar-1.png" alt="" />
                       <textarea placeholder="Write a reply..." class="widthTextarea" id="txtUserComments"></textarea>
                        <div class="viewAll" id="post-your-comments">Post your comment</div>     
                    </div>

                </div><!--End tabs container-->

            </div><!--End tabs-->

			<!-- Second Tab -->
            <div class="clearfix"></div>
        </div>
        <div class="treeDesign leftModule adSection">
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
                            <img class="fullWidthImg" id="full-image-content" src="" alt="">
                        </div>
                    </div>
                    <?php if (count($apiResponse['info']['records']) > 1) { ?>
                        <div class="rightNavON"></div>
                    <?php } ?>
                    <div class="fullWidthESC">Click "ESC" Exit Full Screen Mode</div>  
                    <div class="popup-purchase-reprint" <?php echo $printImagehide; ?>><button class="btn">Purchase Reprint</button></div>
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
                <h4 class="modal-title" id="">Contact Us <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <p>* = Required Field</p>
                    <form class="form-horizontal" name="contact_aib_form" id="contact_aib_form" method="POST" action="">
                        <input type="hidden" name="request_type" id="request_type" value="CT">
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
                <h4 class="modal-title" id="">Add item to scrapbook <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
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
                <h4 class="modal-title" id="">Reprint Form <span style="text-align:right;float: right;padding-right: 31px;">protected by reCAPTCHA</span> </h4>
				 
            </div>
            <div class="modal-body">
                <div class="box_public_info">
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
                        <input type="hidden" name="request_type" id="request_type" value="RP">
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
                <h4 class="modal-title" id="">Share Item <span style="text-align:right;float: right;padding-right: 31px;">protected by reCAPTCHA</span>  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
				 
            </div>
            <div class="modal-body">
                <div class="">
                    <form class="form-horizontal" name="share_items_front" id="share_items_front" method="POST" action="">
                     <div class="form-group" <?php echo$username_hide; ?>> 
                      <label class="col-xs-4 control-label">Select Username</label>
                       <div id="sub-group" class="archive-group col-md-7">
                        <div id="sare_item_username" class="form-control "></div>
                      </div>
                      </div> 
					   <input type="text" id="field_share_item" name="field_share_item" value="" style="display:none">
					   <input type="text"  name="timestamp_value" id="timestamp_valueid" value="<?php echo time();?>" style="display:none">
                         <div class="clearfix"></div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email-Id* :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="share_emailId" id="share_emailId" placeholder="Enter Email-Id here">
                            </div>
                        </div>
                    </form>
                    <div class="text-center"><button class="btn btn-success" type="submit" id="share_item_button">Share</button></div>
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
?>

<script type="text/javascript">
    var parent_folder_id = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-item-id');//'<?php //echo $p_folder_id; ?>';
    var loginStatus = '<?php echo $loggedIn; ?>';
     
    $(document).ready(function () {
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
                    required: true,
                    email: true
                },
                cus_page_link: {
                    required: true
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
                    required: "Page link is required"
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
                    required: true,
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
                    email:true,
					required: true,
                }
            },
            messages: {
                share_emailId: {
                    email:"Please enter correct email id",
					required: "Email-ID is required."
                }
            }
        });
		
		// SCRAPBOOK
		$("#add_scrapbook_item_form").validate({
            rules: {
                user_scrap_text: { 
					required: true,
                }
            },
            messages: {
                user_scrap_text: { 
					required: "Enter scrapbook here."
                }
            }
        });
		
		
		
		
		
        var p_folder_id = '<?php echo $p_folder_id; ?>';
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
        $('.pagination-counter .total').text(total_item_count);
        var pr_file_id = $('#slider-thumb-image-0').attr('data-pr-file-id');
        var item_id = $('#slider-thumb-image-0').attr('data-item-id');
        getPrimaryImageData(pr_file_id);
        getItemCustomFields(item_id,ref_id);
        //DESKTOP SLIDER START
        $('.sapple-thumb-list').click(function () {
            if (!$(this).parent().hasClass('slider-active-thumb')) {
                var pr_file_id = $(this).attr('data-pr-file-id');
                var item_id = $(this).attr('data-item-id');
                var data_count = parseInt($(this).attr('data-count'));
                $('.current').html(parseInt(data_count + 1));
                getItemCustomFields(item_id,ref_id);
                getPrimaryImageData(pr_file_id);
                var clickedThumb = $(this).attr('data-count');
                $('.sapple-slider-item-thumb').removeClass('slider-active-thumb');
                $('#sapple-slider-thumb-item-' + clickedThumb).addClass('slider-active-thumb');
            }
        });
        //THUMB SECTION START
        $('.right-arrow-section').click(function () {
            //var current_value = $('.current').text();
            var current_value = $(".sapple-slider-thumb:visible").attr('thumb-parent-count');
            if (current_value == total_thumb_page)
                current_value = 1;
            else
                current_value = parseInt(parseInt(current_value) + 1);
            $('.sapple-slider-thumb').css({'display': 'none'});
            $('.sapple-slider-thumb').removeClass('animated fadeIn');
            $('#thumb-list-' + current_value).css({'display': 'inline-block'});
            $('#thumb-list-' + current_value).addClass('animated fadeIn');
            //$('.current').text(current_value);
        });
        $('.left-arrow-section').click(function () {
            //var current_value = $('.current').text();
            var current_value = $(".sapple-slider-thumb:visible").attr('thumb-parent-count');
            if (current_value == 1)
                current_value = total_thumb_page;
            else
                current_value = parseInt(parseInt(current_value) - 1);
            $('.sapple-slider-thumb').css({'display': 'none'});
            $('.sapple-slider-thumb').removeClass('animated fadeIn');
            $('#thumb-list-' + current_value).css({'display': 'inline-block'});
            $('#thumb-list-' + current_value).addClass('animated fadeIn');
            //$('.current').text(current_value);
        });
        //THUMB SECTION END
        //BANNER SECTION START
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
                    var imageObj = $(this).children('img').attr('src');
                    //alert(imageObj); return false;
                    $('.fullWidthESC').show();
                    $('#full-image-content').attr('src', imageObj);
                    $('#full-width-image-popup').modal('show');
                    setTimeout(function () {
                        $('.fullWidthESC').hide('slow');
                    }, 2000);
                }
            });
        });
        //Maximise Section End here
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
    });

    $(window).load(function () {
        $("#home-bg-img-title").hide();
        setTimeout(function () {
            $('#home-bg-img-inner').css("opacity", "1");
            $('#home-bg-img-inner').addClass("animated fadeIn");
        }, 800);


    });
    function getPrimaryImageData(pr_file_id) {
        var thumb_url = '<?php echo THUMB_URL; ?>';
        $('.loading-div').show();
        var img_data = '<img class="show_large_image" src="' + thumb_url + '?id=' + pr_file_id + '&show_text=yes" alt="Slider Image" />';
        $('#sapple-slider-item-0').html(img_data);
        $('#sapple-slider-item-0').show();
        $('.loading-div').hide();
    }
    $(document).on('click', '.leftNavON', function () {
        var thumb_url = '<?php echo THUMB_URL; ?>';
        var current_active_thumb_count = $('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-count');
        var maximum_image_count = parseInt(parseInt($('#maximum_image_count').val()) - 1);
        var prevoius_active_count = '';
        if (current_active_thumb_count == 0) {
            prevoius_active_count = maximum_image_count;
        } else {
            prevoius_active_count = parseInt(current_active_thumb_count - 1);
        }
        var next_pr_file_id = $('#slider-thumb-image-' + prevoius_active_count).attr('data-pr-file-id');
        $('#full-image-content').attr('src', thumb_url + '?id=' + next_pr_file_id + '&show_text=yes');
        $('#slider-thumb-image-' + prevoius_active_count).trigger('click');
        $('.sapple-slider-thumb').hide();
        $('#slider-thumb-image-' + prevoius_active_count).parents('.sapple-slider-thumb').css('display', 'inline-block');
        var slider_count = $('#slider-thumb-image-' + prevoius_active_count).parents('.sapple-slider-thumb').attr('thumb-parent-count');
        $('.current').text(slider_count);
        $('.loading-div').show();
        hideLoader();
    });
    $(document).on('click', '.rightNavON', function () {
        var thumb_url = '<?php echo THUMB_URL; ?>';
        var current_active_thumb_count = parseInt($('.thumb-listing').find('.slider-active-thumb').children('img').attr('data-count'));
        var maximum_image_count = parseInt(parseInt($('#maximum_image_count').val()) - 1);
        var next_active_count = '';
        if (current_active_thumb_count == maximum_image_count) {
            next_active_count = 0;
        } else {
            next_active_count = parseInt(current_active_thumb_count + 1);
        }
        var next_pr_file_id = $('#slider-thumb-image-' + next_active_count).attr('data-pr-file-id');
        $('#full-image-content').attr('src', thumb_url + '?id=' + next_pr_file_id + '&show_text=yes');
        $('#slider-thumb-image-' + next_active_count).trigger('click');
        $('.sapple-slider-thumb').hide();
        $('#slider-thumb-image-' + next_active_count).parents('.sapple-slider-thumb').css('display', 'inline-block');
        var slider_count = $('#slider-thumb-image-' + next_active_count).parents('.sapple-slider-thumb').attr('thumb-parent-count');
        $('.current').text(slider_count);
        $('.loading-div').show();
        hideLoader();
    });
    function hideLoader() {
        var $elems = $('#full-image-content');
        var elemsCount = $elems.length;
        var loadedCount = 0;
        $elems.on('load', function () {
            loadedCount++;
            if (loadedCount == elemsCount) {
                $('.loading-div').hide();
            }
        });
    }
    $(document).on('click', '.purchase-reprint', function () {
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
                    $('#record_item_title').html('<strong>Item Title: </strong>' + record.item_title);
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
      window.location.assign('people.php?folder_id='+'<?php echo $_REQUEST['previousId']; ?>');
    })
    $(document).on('click','#item_add_to_scrapbook',function(){
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
    
    //Comment Section 
    /*function listCommentThreadsPost(parent_folder_id,action) {
        
        if (parent_folder_id) { 
            //$('#tabs-2').html("Fetching Comments,Please wait!");
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'list_comment_threads', parent_folder_id: parent_folder_id,action:action},
                success: function (response) {
                    $("#tabs-2 div[id^=comment-]:last").append(response);
                    //$('#tabs-2').prepend(response);
                    //$("#tabs ul li:nth-child(1) a").trigger('click');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }*/
    
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
    /*function listCommentInThread(object_id) {
        if (parent_folder_id) {            
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'list_comments_in_thread', object_id: object_id},
                success: function (response) {
                    //$('#tabs-2').html(response);
                    $("#tabs ul li:nth-child(1) a").trigger('click');
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again');
                }
            });
        }
    }*/
    //Comment Section 
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
           html+='<p> ' + obj.comment_text + '</p>';
           html+='<span>';
           html+='<a href="javascript:void(0);" class="replyBtn-' + obj.item_id + '">Reply</a> '; 
           html+='<a href="javascript:void(0);" class="deleteBtn-' + obj.item_id + '"> Delete</a>';
           html+='</span>';
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
         listCommentThreads(parent_folder_id,'onload');
         
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
                               $('#tabs-2').prepend('<h4 class="headingComment">Comments</h4>');
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
        $('#sharing_record_id').val(record_id);
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'share_get_records_prop', id: record_id},
            success: function (response) {
                var record = JSON.parse(response);
                var shared_email = $.parseJSON(record);
                if (shared_email) {
                    get_public_username(shared_email);
                } else {
                    get_public_username('');
                }
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again');
            }
        });
    });
    
      
        
        
    });
    
    function getRelatedItems(record_id){
        if(record_id){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'get_related_items', record_id: record_id},
                success: function (data) {
                    $('#related_content_section').html(data);
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
	
	$(function() {
	var flag=0;
$(".btnright").click(function() {
	$('#tab2').show();
$('.slidediv').animate({"right": "0px"});
$('.btnrightP').hide();
 $('.sapple-slider-item img').animate({"width": "250px","left": "30%"});
  if(flag==1){
	   $('.sapple-slider-item img').animate({"width": "480px","left": "50%"});
	   $('.slidediv').animate({"right": "-620px"}); 
	   $('.btnrightP').delay(800).show(0); 
	  flag=0;
  }else{
	  flag=1;
  }
});

});


$(function() {
	var flag=0;
$(".btnrightP").click(function() {
$('.slidedivP').animate({"right": "0px"});
$('.btnright').hide();
 $('.sapple-slider-item img').animate({"width": "250px","left": "30%"});
  if(flag==1){
	   $('.sapple-slider-item img').animate({"width": "480px","left": "50%"});
	   $('.slidedivP').animate({"right": "-620px"});
	   $('.btnright').delay(800).show(0);
	  flag=0;
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
</script> 