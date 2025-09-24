<?php

require_once dirname(__FILE__) . '/config/config.php';
if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) {
   // header('Location: home.php');
}
include_once COMMON_TEMPLATE_PATH . 'header.php';


include_once 'trending_data_generator.php';


// Step 1: Pick maximum unique folder_id items
foreach ($dispLayTrendingItemArray as $item) {
    if (!in_array($item['soc_id'], $usedFolderIds)) {
        $uniqueResult[] = $item;
        $usedFolderIds[] = $item['soc_id'];
    }
    if (count($uniqueResult) === 10) break;
}
if(count($uniqueResult) < 10){
	
	foreach ($dispLayTrendingItemArray as $item) {
		if (!in_array($item['viewed_item_id'], $uniqueResult)) {
			$uniqueResult[] = $item;

		}
		if (count($uniqueResult) === 10) break;
	}
}
$dispLayTrendingItemArray=$uniqueResult;

//echo '<pre>';print_R($uniqueResult);echo '</pre>';
?>

<script type="text/javascript">
	localStorage.setItem('animate-load-more-home','0');
	localStorage.setItem('animate-load-more-archive','0');
	localStorage.setItem('animate-load-more-collection','0');
	localStorage.setItem('animate-load-more-sub_group','0');
	localStorage.setItem('animate-load-more-record','0');
</script>

<style>
    ul.header-menu li.display_only_home{
    display: inline-block;
}
</style>
<?php
    if (!isset($_SESSION['aib']['session_key']))
    {
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );

        $apiResponse = aibServiceRequest($postData, 'session');
        if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '')
        {
            $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
        }
    }
    else
    {
        $sessionKey = $_SESSION['aib']['session_key'];
    }
    $PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => $sessionKey,
        'obj_id' => 1
    );
    $artifacts_status=0; $home_logo = ''; $home_banner = ''; $home_page_title = ''; $home_page_description = '';
    $Result = aibServiceRequest($PostData, "browse");
    if ($Result['status'] == 'OK') {
        $artifacts_status= $Result['info']['records']['artifacts_status'];
        $home_logo = !empty($Result['info']['records']['default_home_logo'])?'/admin/tmp/'.$Result['info']['records']['default_home_logo']:'/public/images/society_logo.png';
        $home_banner = !empty($Result['info']['records']['default_home_header_image'])?'/admin/tmp/'.$Result['info']['records']['default_home_header_image']:'/public/images/new_layout_bg.jpg';
        $home_page_title = isset($Result['info']['records']['home_page_title']) ? $Result['info']['records']['home_page_title'] : ""; 
        $home_page_description = isset($Result['info']['records']['home_page_description']) ? $Result['info']['records']['home_page_description'] : ""; 
    }
    
    
?>
<div class="header_img bgBlue_header">
            <div class="clientLanding bannerImage_society" style="background-image:url(<?=$home_banner;?>);"></div><!-- bannerImage clientLanding-->
            <div class="clientLogo col-md-2"><img id="" style="width:200px;" src="<?=$home_logo;?>" /></div> <!--clientLogo_society-->
            <!--div id="top_right_position"></div-->
            <?php /*if ($is_unclaimed_society && $is_unclaimed_society == '1') { ?>
                <input type="button" class="btn btn-info claim_this_historical" onclick="openClaimPopup(event);" value="Claim This Historical" />
            <?php }*/ ?>
        </div>
<div class="clearfix"></div>

<!-- Fix start on 04-July-2025 -->
        <div class="row-fluid bgMap description-background new_layout">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-2 col-sm-6 col-xs-12 text-center" id=""> </div>
            <div class="col-md-10 col-sm-6 col-xs-12 marginTopSociety">
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-11">
                        <div class="media">
                        <div class="media-left">
                            <img src="<?php echo IMAGE_PATH . 'aib-pal-logo.jpg'; ?>" alt="AIB-PAL Logo" />
                        </div>
                        <div class="media-body">
                            <div class="paddTop45">
                        <h4 class="aboutSociety content-heading"><span id=""><?=$home_page_title;?></span></h4>
                        <h4 class="aboutSociety"><?=$home_page_description;?></h4>
						<div class="laptop">
                    <a href="home.html?q=<?php echo encryptQueryString('folder_id='.HISTORICAL_SOCITY_ROOT.'&show_text=yes'); ?>">
                        <span>View Knowledge Base</span> 
                    </a>
                </div>
                    </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
            <!-- Fix end on 25-June-2025 -->
            <div class="clearfix"></div>
        </div>
            <!-- Fix end on 04-July-2025 -->

<!-- Fix start Issue Id 0002463 26-June-2025 -->
<div>
<?php
    if (!isset($_SESSION['aib']['session_key']))
    {
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );

        $apiResponse = aibServiceRequest($postData, 'session');
        if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '')
        {
            $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
        }
    }
    else
    {
        $sessionKey = $_SESSION['aib']['session_key'];
    }
    $PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => $sessionKey,
        'obj_id' => 1
    );
    $artifacts_status=0;
    $Result = aibServiceRequest($PostData, "browse");
    if ($Result['status'] == 'OK') {
        $artifacts_status= $Result['info']['records']['artifacts_status'];
    }
    if($artifacts_status == 1){
?>
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
        <div class="posArtifacts marginBottom10">
            
          <div class="Trending_div" style="display: none;"><?php foreach($dispLayTrendingItemArray as $key=>$d) {
			 if($key<10){
			 ?><a style="cursor: pointer;" href="item-details.html?q=<?php echo encryptQueryString('folder_id=' . $d['folder_id']); ?>">
			  <!--//Fix start for Issue ID 2302 on 11-Oct-2023-->
			  <img id="<?php echo $d['folder_id'];?>"  src="<?php echo THUMB_URL.'?id='.$d['file_id'];?>" /></a>
			  <!--//Fix End for Issue ID 2302 on 11-Oct-2023-->
			<?php } }?> </div>
			<div style="width: 100%;display: flex;    justify-content: center; align-items: center;padding: 10px"><div style="width: 200px;" class="text-overlay"><a href="trending_artifacts.php">More Trending Artifacts</a></div></div>
        </div>
    </div>
<?php } ?>    
</div>
<!-- Fix end Issue Id 0002463 26-June-2025 -->            

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