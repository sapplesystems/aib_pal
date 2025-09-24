<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';

// Function to call server
// -----------------------
function aib_request($LocalPostData, $FunctionSet)
{
    $CurlObj = curl_init();
    $Options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => AIB_SERVICE_URL . "/api/" . $FunctionSet . ".php",
        CURLOPT_FRESH_CONNECT => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 0,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
    );

    curl_setopt_array($CurlObj, $Options);
    $Result = curl_exec($CurlObj);
    if ($Result == false) {
        $OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
    } else {
        $OutData = json_decode($Result, true);
    }

    curl_close($CurlObj);
    return ($OutData);
}

function aib_get_session_key()
{
    if (!isset($_SESSION['aib']['session_key'])) {
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );
        $apiResponse = aib_request($postData, 'session');
        if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
            $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
        }
    } else {
        $sessionKey = $_SESSION['aib']['session_key'];
    }
    return ($sessionKey);
}

function get_uploaded_user_guide()
{
    $PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => aib_get_session_key(),
        'obj_id' => 1
    );

    $Result = aib_request($PostData, "browse");
    if ($Result['status'] == 'OK') {
        return $Result['info']['records']['user_guide'];
    }
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <h4 class="list_title text-center"><span class="pull-left">Download User Guide</span>
            <span class="headingNameDesign"></span>
        </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="alert alert-dismissible" id="message"></div>
        <div class="row">
            <?php
            $file_name = get_uploaded_user_guide();
            if (file_exists(ARCHIVE_IMAGE . $file_name)) { ?>
                <div class="col-md-2"></div>
                <div class="col-md-8 text-center">
                    <object data="<?php echo HOST_ADMIN_IMAGE_PATH . $file_name ?>" width="600" height="600"></object>
                </div>
                <div class="col-md-2">
                    <input class="btn btn-primary" type="button" value="Download" onclick=downloadUserGuide(); />
                    <br /><br />
                    <a class="btn btn-primary" target="_blank" href="<?php echo HOST_ADMIN_IMAGE_PATH . $file_name; ?>">Click to View</a>
                </div>
            <?php } ?>
        </div>
    </section>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script type="text/javascript">
    function downloadUserGuide() {
        let downloadUrl = '<?php echo HOST_ADMIN_IMAGE_PATH . $file_name; ?>';
        let link = document.createElement('a');
        link.href = downloadUrl;
        link.download = '<?php echo $file_name; ?>';
        link.dispatchEvent(new MouseEvent('click'));
    }
</script>