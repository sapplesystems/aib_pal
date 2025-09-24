<?php
require_once dirname(__FILE__) . '/config/config.php';
if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) {
   // header('Location: home.php');
}
include_once COMMON_TEMPLATE_PATH . 'header.php';

include_once 'trending_data_generator.php';



?>

<script type="text/javascript">
	localStorage.setItem('animate-load-more-home','0');
	localStorage.setItem('animate-load-more-archive','0');
	localStorage.setItem('animate-load-more-collection','0');
	localStorage.setItem('animate-load-more-sub_group','0');
	localStorage.setItem('animate-load-more-record','0');
</script>

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
			 if($key<100){
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