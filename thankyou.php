<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
define('URLPAGENAME', 'thank-you');
$uri = $_SERVER['REQUEST_URI'];
$str_url = explode("?", $uri);
$val = urldecode($str_url[1]);
$url = explode("&", $val);
$id = explode("=", $url[0]);
$type_value = explode("=", $url[1]);

$unclaimed_society_id = '';
if ($url[2]) {
    $unclaimed_society_arr = explode("=", $url[2]);
    $unclaimed_society_id = $unclaimed_society_arr[1];
}

$flg = 0;
if (isset($url[1]) && $url[1] == 'flg=new_registration') {
    $flg = 1;
}
$current_email = '';
$old_email = '';
$society_hide = 'hidden';
$pass_details = '';
if (isset($url[2]) && !empty($url[2])) {
    $current_email = $url[2];
}
if (isset($url[3]) && !empty($url[3])) {
    $old_email = $url[3];
}
if ($id[1] == 'archive_user_reg') {
    $society_hide = '';
}
?>
<style>
    mark{background-color: #fbd42f !important;}
    .fontValue{	font-size: 14px;
                font-weight: normal;
                padding-top: 8px;
                display: block;
    }
</style>
<div class="clearfix"></div>
<form name="registrationOtherForm" id="registrationOtherForm" method="post" action="">
    <input type="hidden" name="user_login_id" id="user_login_id" value="<?php echo $id[1]; ?>">
    <div class="content bgThankYou" style="height:calc(100vh - 86px);">
        <div class="">
            <div class="container"> 
                <div class="row marginTop20" id="display_message" style="display:block;">
                    <div class="col-md-12">
                        <div class="thankYouText">
                            <div id="pswd_div_id">
                                Your account has been created successfully.<br />
                                <div class="with_user">Thank you for the email verification</div> 
                            </div> 
                        </div>
                    </div>  
                </div>
                <div class="backPageLink"><a href="index.html"><img src="<?php echo IMAGE_PATH . 'back-icon.png'; ?>" alt="Thank You" /> Back to HomePage</a></div>
            </div>
        </div>
</form> 
<div class="clearfix"></div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script>
    $(document).ready(function () {
        var user_id = '<?php echo $id[1]; ?>';
        var flg = '<?php echo $flg; ?>';
        var flg_val = '<?php echo $type_value[1]; ?>';
        var unclaimed_society_id = '<?php echo $unclaimed_society_id; ?>';
        
        if (user_id != '' && unclaimed_society_id!='') {
            $.ajax({
                type: 'post',
                url: 'services.php',
                data: {mode: 'active_public_user', id: user_id, flg: flg, unclaimed_society_id: unclaimed_society_id},
                success: function (response) {
                    console.log(response);
                    var result = JSON.parse(response);
                }
            });
        }
    });

</script>