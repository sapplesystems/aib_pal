<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
?>

    <div class="content bgInterstitial" style="min-height: calc(100% - 86px);">
        <div class="bgBusiness_overlay">
            <div class="container"> 
                <div class="row marginTop50" id="display_message" style="display:block;">
                    <div class="col-md-5 col-sm-5">
                        <div class="businessText">
                            <h3 class="colorYellow">Looking for Newspaper Archives?</h3>
                            <p class="marginTop20">SmallTownPapers offers free access to millions of pages of newspaper archives from small towns going back to the 1800s! </p>
                            <ul class="business_listing">
                            <li><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> &nbsp;Exclusive rural newspaper archives</li>
                            <li><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> &nbsp;Over 300 titles</li>
                            <li><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> &nbsp;Search & browse</li>
                            </ul>
                            <a href="http://www.stparchive.com/" class="btn btn-info marginTop30 pull-right bgColor" target="_blank">SmallTownPapers Archive</a>
                            </div>

                    </div>
                    <div class="col-md-2 col-sm-2"><div class="interstitialLine"></div></div>
                    <div class="col-md-5 col-sm-5">
                        <div class="businessText marginNone">
                            <h3 class="colorYellow marginNone">Scanning & Web Hosting for Publishers</h3>
                            <p class="marginTop20 marginTopNone">With <span class="colorYellow">ArchiveInABox for Publishers</span> you can organize and host your publication archives all in one place.</p>
                            <ul class="business_listing">
                            <li><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> &nbsp;Weekly PDF edition</li>
                            <li><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> &nbsp;Web articles</li>
                            <li><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> &nbsp;Scanned archives</li>
                            </ul>
                            <a href="interstitial-page2.html" class="btn btn-info marginTop30 pull-right bgColor">More for Publishers</a>
                            </div>

                    </div>
                </div>  
            </div>
           <a class="backtoLink backStylePeople" href="index.html"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Home Page</a>
        </div>
    </div>
<div class="clearfix"></div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>


