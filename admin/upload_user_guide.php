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

function upload_user_guide($user_guide = '')
{
    if (!empty($user_guide)) {
        $PostData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'set_item_prop',
            '_session' => aib_get_session_key(),
            'obj_id' => 1,
            'propname_1' => 'user_guide',
            'propval_1' => $user_guide
        );

        $Result = aib_request($PostData, "browse");
        if ($Result['status'] == 'OK') {
            $responseData = array('status' => 'success', 'message' => 'User guide uploaded successfully.');
        } else {
            $responseData = array('status' => 'fail', 'message' => 'User guide could not be uploaded.');
        }
    } else {
        $responseData = array('status' => 'fail', 'message' => 'No file found.');
    }

    return $responseData;
}

if (isset($_POST['submit'])) {
    $resp = array('status' => 'fail', 'message' => '');
    $file = $_FILES['upload_user_guide'];
    $name1 = $file['name'];
    $type = $file['type'];
    $size = $file['size'];
    $tmppath = $file['tmp_name'];

    if ($name1 != "") {
        if (move_uploaded_file($tmppath, 'tmp/' . $name1)) {
            $resp = upload_user_guide($name1);
        }
    }
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <h4 class="list_title text-center"><span class="pull-left">Upload User Guide</span>
            <span class="headingNameDesign"></span>
        </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="alert alert-dismissible" id="message"></div>
        <?php
        $file_name = get_uploaded_user_guide();
        ?>
        <form name="upload_user_guide_frm" id="upload_user_guide_frm" method="post" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4">
                    <label>Upload User Guide</label>
                    <input class="form-control" type="file" name="upload_user_guide" id="upload_user_guide" accept=".pdf" />
                </div>
                <div class="col-md-2">
                    <input style="margin-top: 30px;" class="btn btn-info" type="submit" name="submit" value="Upload" />
                </div>
                <div class="col-md-6 text-right">
                    <?php if (file_exists(ARCHIVE_IMAGE . $file_name)) { ?>
                        <a class="btn btn-primary" style="margin-top: 30px;" target="_blank" href="<?php echo HOST_ADMIN_IMAGE_PATH . $file_name; ?>">Click to View</a>
                    <?php } ?>
                </div>
            </div>
            <div class="row marginTop20">
                <?php if (file_exists(ARCHIVE_IMAGE . $file_name)) { ?>
                    <div class="col-md-12 text-center">
                        <object style="height: 500px; width: 500px;" data="<?php echo HOST_ADMIN_IMAGE_PATH . $file_name; ?>"></object>
                    </div>
                <?php } ?>
            </div>
        </form>
    </section>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>