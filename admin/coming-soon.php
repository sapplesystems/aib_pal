<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
?>
<div class="clearfix"></div>
<form name="registrationOtherForm" id="registrationOtherForm" method="post" action="">
    <input type="hidden" name="user_login_id" id="user_login_id" value="<?php echo $id[1]; ?>">
    <div class="content bgComingSoon" style="min-height: 700px;">
        <div class="">
            <div class="" id="display_message" style="display:block;">
                <div class="commingSoonImg">
                    <!--<div class="commingSoonText"><a href="#">LogIn</a></div>-->
                </div>  
            </div>
            <div class="backStylePeople"><a class="backtoLink pull-right marginTop20" href="index.html"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Home Page</a></div>
        </div>
    </div>
</form>
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
