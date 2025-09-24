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
            <div class="col-md-12"><h3>What is ArchiveInABox? <a class="backtoLink pull-right marginTop10" href="people.html?q=<?php echo encryptQueryString('folder_id='.PUBLIC_USER_ROOT.'&show_text=no'); ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a></h3><p>ArchiveInABox for People is a new way to explore the history and artifacts of everything, even remote rural locations. Historical societies and museums can now publish their collections online so anyone in the world can view their materials.<br /><br />And you can too! All in the same place.<br /><br />ArchiveInABox for People is a powerful new way to create your own online archive and access thousands of others.<br /><br />With your free account, you can browse and search all the content, create your own archive, save to scrapbooks, and share with friends & family.</p>
                <div class="text-center">
                    <a href="<?php if(isset($_SESSION['aib']['user_data']['user_id']) && !empty(isset($_SESSION['aib']['user_data']['user_id']))){
                        echo 'javascript:checkedUserLoggedIn()';}else{ echo 'registration_user.html'; } ?>" title='Register'>
                        <input type="button" class='btn btn-success btn-register-society marginTop10' id="register_public_user" value='Create My Own Box'> 
                    </a>
                </div>
            </div>
        </div>
        <div class="row marginTop30">
            <div class="leftPanel posRelative">
                <div class="whyUsImg oneP"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Discover, save, and share </h2>
                            <!--<p><strong>ArchiveInABox for Historicals</strong> is designed to make the most of your time. Tools help streamline all processes. Templates automate repetitive tasks. Easy management of your brand, messaging, and advertising. Place house ads anywhere and push your brand throughout your archive.</p>-->
                            <!--<div class="readMore">READ MORE</div>  -->
                        </div>
                    </div>
                </div> 
            </div>   
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg twoP"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Online access to curated collections</h2>
                            <!--<p>Instead of spending thousands of dollars a year building and maintaining an underdeveloped online publishing presence, use the power of a large scale publishing platform built to handle your entire archive.</p>-->
                            <!--<div class="readMore">READ MORE</div>  -->
                        </div> 
                    </div>
                </div>  
            </div> 
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg threeP"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Collectors</h2>
                            <!--<p>Unlike other online publishing software, <strong>ArchiveInABox for Historicals</strong> is free for historical societies and historical stakeholders such as museums, libraries, and non-commercial archives -- with no preset content limit. It is web-based and can be used from any internet connection. </p>-->
                            <!--<div class="readMore">READ MORE</div>  -->
                        </div>
                    </div>
                </div> 
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg fourP"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Hobbyists</h2>
                            <!--<p>Create fields that suit your indexing goals. Create index field templates to standardize repetitive forms for different data sets. Upload individual or large groups of files or import from your museum management software. Upload content once, publish in multiple locations. Page level OCR on demand. Build specialty scrapbooks, push related content, and connect to others. </p>-->
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div> 
                    </div> 
                </div> 
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg fiveP"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Artists</h2>
                            <!--<p>Set up your archive the way you want. Pattern after a library, traditional archive, card catalog, or any way you can imagine. Create Archives, Collections, Subgroups. Records can be individual or multiple pages. Upload JPG, PNG or PDF (single and multi-page). Link records to other assets such as your YouTube channel, Vimeo, Issu and others.</p>-->
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div> 
                    </div>  
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg sixP"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Family albums</h2>
                            <!--<p>Now your indexers and helpers can work from home at any time. Set up assistant accounts, create individual indexing assignments, and monitor progress. Assistants can edit indexes including system-generated OCR.</p>-->
                            <!--<div class="readMore">READ MORE</div>  -->   
                        </div>   
                    </div>  
                </div>   
            </div>
        </div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg sevenP"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Old scrapbooks</h2>
                            <!--<p>Designed to promote sharing, your content can now be spread far and wide. Monitor how users connect, share and comment on your content. Connect your archive with people who can discover and share your history. </p>-->
                            <!--<div class="readMore">READ MORE</div>  -->   
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="panelRight posRelative">
                <div class="whyUsImg eightP"></div>
                <div class="panelLeft">
                    <div class="imgDescription2">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Easy to get started</h2>
                            <!--<p>Build your donor base by offering reprints and research services. Sell and manage your own local supporter advertising to develop sponsorships and grants.</p>-->
                            <!--<div class="readMore">READ MORE</div>  --> 
                        </div>  
                    </div> 
                </div>  
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="leftPanel posRelative">
                <div class="whyUsImg nineP"></div>
                <div class="rightPanel">
                    <div class="imgDescription">
                        <div class="textContent">
                            <!--<h6>A LITTLE ABOUT US</h6>-->
                            <h2>Free to use</h2>
                            <!--<p>Real time watermarking allows you to watermark any of your images automatically, and when users save your content to a scrapbook, the watermark is retained. The platform does not allow downloading or printing of images. You exclusively own and control all of your content; no partnership is created. Add or delete content at any time (except for real time backups; see terms of service).</p>-->
                            <!--<div class="readMore">READ MORE</div>  -->  
                        </div>  
                    </div>  
                </div>   
            </div> 
        </div>
        
        
        
        <div class="row createBoxPeople">
        <div class="col-md-4"></div>
        <div class="col-md-4 col-sm-4 text-center">
                <a href="registration_user.html" id="create_my_own_box_user" title="Create my own box">
                    <div class="img-6"></div>
                    <h3 class="text-center">Create my own box</h3>
                </a>
            </div>
            <div class="col-md-4"></div>
            </div>
          
        
    </div>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
