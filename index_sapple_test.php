<?php
require_once dirname(__FILE__) . '/config/config.php';
if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) {
   // header('Location: home.php');
}

include_once COMMON_TEMPLATE_PATH . 'header.php';

//Fix start for Issue ID 2147 on 28-Apr-2023
$PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => $sessionKey,
        'obj_id' => 1
    );
$artifacts_time=30;
$Result = aibServiceRequest($PostData, "browse");
if ($Result['status'] == 'OK') {
	$artifacts_time= $Result['info']['records']['artifacts_time'];
}


        $sessionKey = $_SESSION['aib']['session_key'];
       $postDataItem = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
		 "opt_get_property" => 'Y',
            "parent" => TRENDING_ARTIFACTS_ID,
         
            
        );
      
echo '<pre>';
		$tradingItems=array();
		$dispLayTrendingItemArray=array();
        $apiResponseTrendingData = aibServiceRequest($postDataItem, 'browse');
		/*foreach($apiResponseTrendingData['info']['records'] as $key=>$trendingData){
			if($key>=10000 and $key<11001){
			$apiRequestData = array(
									'_key' => APIKEY,
									'_user' => 1,
									'_op' => 'get_path',
									'_session' => $sessionKey, 
									'obj_id' => $trendingData['properties']['record_id'],
									'opt_get_property'=>'Y'
								);
			$apiResponse = aibServiceRequest($apiRequestData, 'browse');
			//print_R($apiResponse['info']['records'][1]['item_title']);
			//die;
			//if($trendingData['properties']['view_count_record']>100 ){
			$apiRequestDataItemStatus = array(
									'_key' => APIKEY,
									'_user' => 1,
									'_op' => 'set_item_prop',
									'_session' => $sessionKey,
									'obj_id' => $trendingData['item_id'],
									'propname_1' => 's_item_id',
									'propval_1' => $apiResponse['info']['records'][1]['item_id'],
									'propname_2' => 's_item_title',
									'propval_2' => $apiResponse['info']['records'][1]['item_title'],
									
								);
				
				$apiResponseStatus = aibServiceRequest($apiRequestDataItemStatus, 'browse');
			
			}
			//}	
		}*/
//die;
print_R($apiResponseTrendingData);die;

		/*************** remove duplicate ************************/
		$trandingItemids=array();
if(isset($apiResponseTrendingData['info']['records']) and count($apiResponseTrendingData['info']['records'])){
		/*<!-- Fix start Issue Id 2380 02-May-2024 -->*/
			if(count($apiResponseTrendingData['info']['records']) >16){
				/*<!-- Fix End Issue Id 2380 02-May-2024 -->*/
			foreach($apiResponseTrendingData['info']['records'] as $trendingData){
				$last_viewed_date=$trendingData['properties']['last_viewed_date'];
				if(in_array($trendingData['properties']['record_id'],$trandingItemids) || strtotime($last_viewed_date) < strtotime('-'.$artifacts_time.' days'))	{
					$postDataDeleteItem = array(
						"_key" => APIKEY,
						"_session" => $sessionKey,
						"_user" => 1,
						"_op" => "delete_item",
					 "obj_id" => $trendingData['item_id']


        				);
					 $apiResponseDeleteData = aibServiceRequest($postDataDeleteItem, 'browse');
				}
				else{
					
					$trandingItemids[]=$trendingData['properties']['record_id'];
				}
			}	
			}
}
		/********************************************************/
$tradingItems=array();
		$dispLayTrendingItemArray=array();
        $apiResponseTrendingData = aibServiceRequest($postDataItem, 'browse');

if(isset($apiResponseTrendingData['info']['records']) and count($apiResponseTrendingData['info']['records'])){
			foreach($apiResponseTrendingData['info']['records'] as $key=>$trendingData){
				
				$item['record_id']=$trendingData['properties']['record_id'];
				$item['view_count_record']=$trendingData['properties']['view_count_record'];
				$tradingItems[]=$item;
			}
}
array_multisort(array_column($tradingItems, 'view_count_record'), SORT_DESC, $tradingItems);
$tradingItems = array_slice($tradingItems,0,100);
$tradingItemFinal=array();
shuffle($tradingItems);
		if(isset($tradingItems) and count($tradingItems)){
			foreach($tradingItems as $key=>$trendingData){
				if($key<111){
				
					$tradingItemFinal[]=$trendingData['record_id'];

					}
				
			}
			
			foreach($tradingItemFinal as $trendingId){
				
				$postDataItem = array(
				"_key" => APIKEY,
				"_session" => $sessionKey,
				"_user" => 1,
				"_op" => "list",
				"parent" => $trendingId,
				"opt_get_files" =>'Y',
					
				);
				$apiResponseTrendingThumb = aibServiceRequest($postDataItem, 'browse');
				
				$TrendingItem=array();
				$TrendingFiles=array();
				foreach($apiResponseTrendingThumb['info']['records'] as $trendingItem){
					
					if(count($trendingItem['files'])){
						
						foreach($trendingItem['files'] as $files){
							
							if($files['file_type']=='tn' and !in_array($files['file_id'],$TrendingFiles))
							{
								$TrendingItem['folder_id']=$trendingId;
								$TrendingItem['file_id']=$files['file_id'];
								$TrendingFiles[]=$files['file_id'];
								
								//Fix start for Issue ID 2302 on 11-Oct-2023-->
								$apiRequestData = array(
									'_key' => APIKEY,
									'_user' => 1,
									'_op' => 'get_path',
									'_session' => $sessionKey,
									'obj_id' => $trendingId,
									'opt_get_property'=>'Y'
								);
								$apiResponse = aibServiceRequest($apiRequestData, 'browse');
								//echo '<pre>';
								//print_R($apiResponse['info']['records'][1]);die;
								if(isset($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]['properties']['itemrecord_address_state']) and isset($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]['properties']['itemrecord_address_city']) and trim($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]['properties']['itemrecord_address_state'])!='' and trim($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]['properties']['itemrecord_address_city'])!=''){
									
									$TrendingItem['state']=trim($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]['properties']['itemrecord_address_state']);
									$TrendingItem['city']=trim($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]['properties']['itemrecord_address_city']);
								}
								elseif(isset($apiResponse['info']['records'][1]['properties']['archive_display_state']) and isset($apiResponse['info']['records'][1]['properties']['archive_display_city']) and trim($apiResponse['info']['records'][1]['properties']['archive_display_state'])!='' and trim($apiResponse['info']['records'][1]['properties']['archive_display_city'])!=''){
									
									$TrendingItem['state']=trim($apiResponse['info']['records'][1]['properties']['archive_display_state']);
									$TrendingItem['city']=trim($apiResponse['info']['records'][1]['properties']['archive_display_city']);
								}
								else{
									
									$TrendingItem['state']='NA';
									$TrendingItem['city']='NA';
								}

//Fix End for Issue ID 2302 on 11-Oct-2023-->

					//echo '<pre>';print_R($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]);die;
								
								
							}
							
						}
						
						break;
						
					}
					
				}
				
				if(count($TrendingItem)){
				$dispLayTrendingItemArray[]=$TrendingItem;
					
					}
				
			}
			
			
			
		}
if (isset($_SESSION['aib']['user_data']) && $_SESSION['aib']['user_data']['user_id']==1) {
  echo '<pre>';print_R($dispLayTrendingItemArray);echo '</pre>';//die;
}
//Fix end for Issue ID 2147 on 28-Apr-2023

?>

<script type="text/javascript">
	localStorage.setItem('animate-load-more-home','0');
	localStorage.setItem('animate-load-more-archive','0');
	localStorage.setItem('animate-load-more-collection','0');
	localStorage.setItem('animate-load-more-sub_group','0');
	localStorage.setItem('animate-load-more-record','0');
</script>

<div class="sliderTop">
    <div class="slider-container">
        <div class="slider-control left inactive"></div>
        <div class="slider-control right"></div>
        <ul class="slider-pagi"></ul>
        <div class="slider">
            <div class="slide slide-0 active">
                <div class="slide__bg"></div>
                <div class="slide__content">
                    <svg class="slide__overlay" viewBox="0 0 720 305" preserveAspectRatio="xMaxYMax slice">
                    <path class="slide__overlay-path" d="M0,0 150,0 500,405 0,405" />
                    </svg>
                    <div class="slide__text">
                        <h2 class="slide__text-heading">ArchiveInABox <br class="displayN" />is for everyone.</h2>
                        <p class="slide__text-desc">
                            <ul class="homepage-slider">
                                <li>* Publish your own archive!</li>
                                <li>* You own & control your online assets!</li>
                                <li>* Base platform 100% free!</li>
                            </ul>
                        </p>
                        <!--<a href="why-us.php" class="slide__text-link">READ MORE</a>-->
                    </div>
                </div>
            </div>
            <div class="slide slide-1 ">
                <div class="slide__bg"></div>
                <div class="slide__content">
                    <svg class="slide__overlay" viewBox="0 0 720 305" preserveAspectRatio="xMaxYMax slice">
                    <path class="slide__overlay-path" d="M0,0 150,0 500,405 0,405" />
                    </svg>
                    <div class="slide__text">
                        <h2 class="slide__text-heading">It’s about time.</h2>
                        <p class="slide__text-desc">Make the most of your time with a <br />publishing platform designed to <br />showcase historic collections.</p>
                        <!--<a href="why-us.php" class="slide__text-link">READ MORE</a>-->
                    </div>
                </div>
            </div>
            <div class="slide slide-2">
                <div class="slide__bg"></div>
                <div class="slide__content">
                    <svg class="slide__overlay" viewBox="0 0 720 305" preserveAspectRatio="xMaxYMax slice">
                    <path class="slide__overlay-path" d="M0,0 150,0 500,405 0,405" />
                    </svg>
                    <div class="slide__text">
                        <h2 class="slide__text-heading">The world finds you.</h2>
                        <p class="slide__text-desc">Your place here provides accessibility <br />no matter where you are located.</p>
                        <!--<a href="why-us.php" class="slide__text-link">READ MORE</a>-->
                    </div>
                </div>
            </div>
            <div class="slide slide-3">
                <div class="slide__bg"></div>
                <div class="slide__content">
                    <svg class="slide__overlay" viewBox="0 0 720 305" preserveAspectRatio="xMaxYMax slice">
                    <path class="slide__overlay-path" d="M0,0 150,0 500,405 0,405" />
                    </svg>
                    <div class="slide__text">
                        <h2 class="slide__text-heading">Preservation leads <br class="displayN" />to discovery.</h2>
                        <p class="slide__text-desc">Organize your collections the way <br />you want people to discover them.</p>
                        <!--<a href="why-us.php" class="slide__text-link">READ MORE</a>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<div class="content bg">
    <div class="container-fluid">
        <div class="row marginTop290">
        <div class="col-md-4 col-sm-4 text-center"></div>
            <div class="col-md-2 col-sm-2 text-center">
                <a class="borderStyle" href="home.html?q=<?php echo encryptQueryString('folder_id='.HISTORICAL_SOCITY_ROOT.'&show_text=yes'); ?>" title="Historicals+Museums">
                    <div class="img-2 login-link"></div>
                    <h4 class="text-center login-link">Historicals+Museums</h4>
                </a>
            </div>
            <div class="col-md-2 col-sm-2">
                <a class="borderStyle" href="interstitial-page.html"  title="Newspapers+Periodicals">
                    <div class="img-3 login-link" ></div>
                    <h4 class="text-center login-link">Newspapers+Periodicals</h4>
                </a>
            </div>
           <!-- <div class="col-md-3 col-sm-3 text-center">
                <a href="aib-business.php"  title="Business">
                    <div class="img-1 login-link"></div>
                    <h4 class="text-center login-link">Commercial</h4>
                </a>
            </div>-->
            <div class="col-md-2 col-sm-2 text-center">
                <a class="borderStyle" href="people.html?q=<?php echo encryptQueryString('folder_id='.PUBLIC_USER_ROOT.'&show_text=no') ?>" title="People">
                    <div class="img-4 login-link"></div>
                    <h4 class="text-center login-link">People</h4>
                </a>
            </div>
            <div class="col-md-2 col-sm-2 text-center">
                <a href="javascript:void(0);" id="create_my_own_box" title="Create my own box">
                    <div class="img-6"></div>
                    <h3 class="text-center bgCreateBox">Create my own box</h3>
                </a>
            </div>
        </div>
        <!-- <div class="row marginTop85">
            <div class="col-md-4 col-sm-4"> </div>
           <!-- <?php if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) { ?>
                <a href="admin/index.php" title="Manage your account">
                    <div class="col-md-3 col-sm-3 text-center">
                        <div class="img-7 login-link" ></div>
                        <h3 class="text-center login-link">Manage Your Account</h3>
                    </div>
                </a>
            <?php }else{ ?>
                <div class="col-md-3 col-sm-3 text-center">
                    <div class="img-5 login-link loginPopup" ></div>
                    <h3 class="text-center login-link loginPopup">Login</h3>
                </div>
            <?php } ?>-->
            <!--<div class="col-md-4 col-sm-4 text-center">
                <a href="javascript:void(0);" id="create_my_own_box" title="Create my own box">
                    <div class="img-6"></div>
                    <h3 class="text-center bgCreateBox">Create my own box</h3>
                </a>
            </div>-->
            <!--<div class="col-md-4 col-sm-4"></div>
        </div> -->
    </div>
</div>
<div class="clearfix"></div>
<!--<div class="testimonial">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-md-12">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod<br>tempor incididunt ut labore et dolore magna aliqua.</p>
                <div class="testimonialSlider">
                     <div class="slider-multi">
            <ul>
                <li style="background-image: url(<?php echo IMAGE_PATH; ?>h1.jpg)">
                    <!-- //for testing: <span>slide 1</span> -->
<!--</li>
<li style="background-image: url(<?php echo IMAGE_PATH; ?>h2.jpg)">
    <!-- //for testing: <span>slide 2</span> -->
<!-- </li>
 <li style="background-image: url(<?php echo IMAGE_PATH; ?>h3.jpg)">
     <!-- //for testing: <span>slide 3</span> -->
<!-- </li>
 <li style="background-image: url(<?php echo IMAGE_PATH; ?>h4.jpg)">
     <!-- //for testing: <span>slide 4</span> -->
<!-- </li>
 <li style="background-image: url(<?php echo IMAGE_PATH; ?>h5.jpg)">
     <!-- //for testing: <span>slide 5</span> -->
<!--</li>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>-->
<!--
-->
	<!-- Fix start Issue Id 2380 02-May-2024 -->
<div class="sectionThree marginBottom30 marginTop40" style="margin-top: 75px !important">
    <div class="container">
	<div class="borderTop1"></div>
		<h3 class="text-center">Trending Artifacts</h3>
		<div class="grid">
				<div class="loaderDiv"><img src="<?php echo IMAGE_PATH; ?>/loading.gif" alt="Loader" /></div>

  <!-- Images are loaded here via JavaScript -->
</div>
</div>
</div>


    <div class="container">
        <div class="posArtifacts">
            
          <div class="Trending_div" style="display: none;"><?php foreach($dispLayTrendingItemArray as $key=>$d) {
			 if($key<10){
			 ?><a style="cursor: pointer;" href="item-details.html?q=<?php echo encryptQueryString('folder_id=' . $d['folder_id']); ?>">
			  <!--//Fix start for Issue ID 2302 on 11-Oct-2023-->
			  <img id="<?php echo $d['folder_id'];?>"  src="<?php echo THUMB_URL.'?id='.$d['file_id'];?>" /></a>
			  <!--//Fix End for Issue ID 2302 on 11-Oct-2023-->
			<?php } }?> </div>
        </div>
    </div>
    


<!--
//Fix End for Issue ID 2147 on 28-Apr-2023-->
<script>
$( window ).resize(function() {
    var window_height = $( window ).height();
    $('.slider-container').height(window_height);
});
$(document).ready(function(){
    var window_height = $( window ).height();
    $('.slider-container').height(window_height);
});
new IdealImageSlider.Slider({
    selector: '#slider',
    onInit: function () {
        getSliderText();
    },
    afterChange: function () {
        getSliderText();
    }
}).start();

function getSliderText() {
    $('#slider a').each(function () {
        if ($(this).hasClass('iis-current-slide')) {
            $('#home-page-about-section').html($(this).text());
        }
    });
}
</script>
	<!-- Fix End Issue Id 2380 02-May-2024 -->
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>