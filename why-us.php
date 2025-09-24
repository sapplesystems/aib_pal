<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
?>
<!--<div class="header_img" style="position:relative;">
    <div class="whyUs"></div>
    <div id="register_your_society"><a href="register.php" title='Register'><input type="button" class='btn pull-right btn-success btn-register-society' value='Register your society'> </a></div>
</div>-->
<div class="clearfix"></div>
<div class="content2 marginBottom30 overflowHidden posInherit marginTop100">
    <div class="container">
        <div class="row mainHeadWhyUs">
        <div class="col-md-12">
        <h3>Connect with people who discover and share your history.</h3><p>Join our established group of <strong>Historical Societies and Museums</strong> who share content by publishing their collections online. All published content is available to anyone, anywhere in the world. Connect your archive with people who can discover and share your history.</p>
        <a href="<?php if(isset($_SESSION['aib']['user_data']['user_id']) && !empty(isset($_SESSION['aib']['user_data']['user_id']))){
             echo 'javascript:checkedUserLoggedIn()';}else{ echo 'register.html'; } ?>" title='Register'><input type="button" class='btn btn-success btn-register-society marginTop10' value='Register your society'> </a>
        <a class="backtoLink pull-right marginTop20" href="home.html?q=<?php echo encryptQueryString('folder_id=1&show_text=yes'); ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a>
        </div>
        </div>
        <div class="row marginTop30">
            <div class="leftPanel posRelative">
                <div class="whyUsImg three"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Easy to use. Build your brand. </h2>
                            <p><strong>ArchiveInABox for Historicals</strong> is designed to make the most of your time. Tools help streamline all processes. Templates automate repetitive tasks. Easy management of your brand, messaging, and advertising. Place house ads anywhere and push your brand throughout your archive.</p>
                            <!--<div class="readMore">READ MORE</div>  -->
                        </div>
                    </div>
                </div> 
            </div>   
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg twelve"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Leverage your resources.</h2>
                            <p>Instead of spending thousands of dollars a year building and maintaining an underdeveloped online publishing presence, use the power of a large scale publishing platform built to handle your entire archive.</p>
                            <!--<div class="readMore">READ MORE</div>  -->
                        </div> 
                    </div>
                </div>  
            </div> 
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg fourteen"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>No license required. 100% web based.</h2>
                            <p>Unlike other online publishing software, <strong>ArchiveInABox for Historicals</strong> is free for historical societies and historical stakeholders such as museums, libraries, and non-commercial archives -- with no preset content limit. It is web-based and can be used from any internet connection. </p>
                            <!--<div class="readMore">READ MORE</div>  -->
                        </div>
                    </div>
                </div> 
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg thirteen"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Production tools included.</h2>
                            <p>Create fields that suit your indexing goals. Create index field templates to standardize repetitive forms for different data sets. Upload individual or large groups of files or import from your museum management software. Upload content once, publish in multiple locations. Page level OCR on demand. Build specialty scrapbooks, push related content, and connect to others. </p>
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div> 
                    </div> 
                </div> 
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg ten"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Flexible Database Layout.</h2>
                            <p>Set up your archive the way you want. Pattern after a library, traditional archive, card catalog, or any way you can imagine. Create Archives, Collections, Subgroups. Records can be individual or multiple pages. Upload JPG, PNG or PDF (single and multi-page). Link records to other assets such as your YouTube channel, Vimeo, Issu and others.</p>
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div> 
                    </div>  
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg six"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Assistants work from anywhere.</h2>
                            <p>Now your indexers and helpers can work from home at any time. Set up assistant accounts, create individual indexing assignments, and monitor progress. Assistants can edit indexes including system-generated OCR.</p>
                            <!--<div class="readMore">READ MORE</div>  -->   
                        </div>   
                    </div>  
                </div>   
            </div>
        </div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg eleven"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Users socialize your collections.</h2>
                            <p>Designed to promote sharing, your content can now be spread far and wide. Monitor how users connect, share and comment on your content. Connect your archive with people who can discover and share your history. </p>
                            <!--<div class="readMore">READ MORE</div>  -->   
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg seven"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Revenue potential</h2>
                            <p>Build your donor base by offering reprints and research services. Sell and manage your own local supporter advertising to develop sponsorships and grants.</p>
                            <!--<div class="readMore">READ MORE</div>  --> 
                        </div>  
                    </div> 
                </div>  
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg thirteenP"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Ownership and downloading.</h2>
                            <p>Real time watermarking allows you to watermark any of your images automatically, and when users save your content to a scrapbook, the watermark is retained. The platform does not allow downloading or printing of images. You exclusively own and control all of your content; no partnership is created. Add or delete content at any time (except for real time backups; see terms of service).</p>
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div>  
                    </div>  
                </div>   
            </div> 
        </div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg fourteenP"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Our business model.</h2>
                            <p>We sell scanning services, and specialize in oversize, fragile, and large projects such as digitizing newspapers from bound volumes, periodicals, maps, photo albums, scrapbooks, loose photos and negatives, and microfilm. Our service is all inclusive of shipping, scanning, and processing. Materials are scanned and returned intact and everything is delivered on portable hard drives. Purchase of scanning services is not required to use the platform. <a style="text-decoration:underline;" href="<?php echo HOW_IT_WORKS; ?>" target="_blank">Learn about our production process.</a></p>
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div>  
                    </div>  
                </div>  
            </div> 
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="aibLogoBig"><a href="http://www.smalltownpapers.com/"><img src="public/images/smalltownpapers-logo.jpg" alt="SmallTownPapers-logo Logo" /></a></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Our History.</h2>
                            <p>ArchiveInABox, part of SmallTownPapers, Inc., has been in business since 2000. We’ve digitized thousands of bound-volume newspaper archives and produced millions of scanned online-searchable newspaper pages -- all available to the public for free. We have scanned and published the newspaper collections of numerous historical societies. We are committed to helping historical stakeholders by providing a robust, technically advanced online hosting system at no cost.</p>
                            <!--<div class="readMore">READ MORE</div>  --> 
                        </div>  
                    </div>  
                </div> 
            </div> 
        </div>
        <div class="row socializeHistory">
        <div class="col-md-12">
        <h3>Socialize your local history!</h3><p>Public users create their own profiles and share your collections with people all over the world. <a id="register_your_society" style="text-decoration:underline;" href="<?php if(isset($_SESSION['aib']['user_data']['user_id']) && !empty(isset($_SESSION['aib']['user_data']['user_id']))){
             echo 'javascript:checkedUserLoggedIn()';}else{ echo 'register.html'; } ?>">Get started today.</a></p>
        
        </div>
        </div>
        
        
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
