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
//  Fix start Issue Id 0002463 26-June-2025
//  Fix start Issue Id 0002476 07-July-2025
if (isset($_POST['submit'])) {
    $artifacts_status = isset($_POST['artifacts_status']) ? 1 : 0;
	 $PostData = array(
            '_key' => APIKEY,
            '_user' => 1,
            '_op' => 'set_item_prop',
            '_session' => aib_get_session_key(),
            'obj_id' => 1,
            'propname_1' => 'artifacts_time',
            'propval_1' => $_POST['artifacts_time'],
            'propname_2' => 'artifacts_status',
            'propval_2' => $artifacts_status,
            'propname_3' => 'home_page_title',
            'propval_3' => $_POST['home_page_title'],
            'propname_4' => 'home_page_description',
            'propval_4' => $_POST['home_page_description']
        );

        $Result = aib_request($PostData, "browse");
}
//  Fix end Issue Id 0002476 07-July-2025
//  Fix end Issue Id 0002463 26-June-2025
$PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => aib_get_session_key(),
        'obj_id' => 1
    );
$artifacts_time=0;
//  Fix start Issue Id 0002476 07-July-2025
$home_logo_img = '';
$home_banner_img = '';
$home_page_title = '';
$home_page_description = '';
//  Fix end Issue Id 0002476 07-July-2025
    $Result = aib_request($PostData, "browse");
    // echo "<pre>";print_r($Result);
    if ($Result['status'] == 'OK') {
        $artifacts_time= $Result['info']['records']['artifacts_time'];
        //  Fix start Issue Id 0002476 07-July-2025
        $home_logo_img = isset($Result['info']['records']['default_home_logo']) ? 'tmp/'.$Result['info']['records']['default_home_logo'] : ""; 
        $home_banner_img = isset($Result['info']['records']['default_home_header_image']) ? 'tmp/'.$Result['info']['records']['default_home_header_image'] : ""; 
        $home_page_title = isset($Result['info']['records']['home_page_title']) ? $Result['info']['records']['home_page_title'] : ""; 
        $home_page_description = isset($Result['info']['records']['home_page_description']) ? $Result['info']['records']['home_page_description'] : ""; 
        //  Fix end Issue Id 0002476 07-July-2025
    }
    //  Fix start Issue Id 0002463 26-June-2025
    $artifacts_status = isset($Result['info']['records']['artifacts_status']) ? $Result['info']['records']['artifacts_status'] : 0;
    //  Fix end Issue Id 0002463 26-June-2025
?>
<!--  Fix start Issue Id 0002476 07-July-2025 -->
<style>
    #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}
    .modal-dialog{width:100% !important;} #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}
    .paddTop35{padding-top: 35px;}
    .my-20{margin-top:20px;margin-bottom: 20px;}
</style>
<!--  Fix end Issue Id 0002476 07-July-2025 -->
<div class="content-wrapper">
    <section class="content-header">
        <!-- Fix start Issue Id 0002476 07-July-2025 -->
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <!-- Fix end Issue Id 0002476 07-July-2025 -->
        <h4 class="list_title text-center"><span class="pull-left">Homepage Settings</span>
            <span class="headingNameDesign"></span>
        </h4>
        <!-- Fix start Issue Id 0002476 07-July-2025 -->
        <form name="register_society_frm" id="register_society_frm" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" id="type" value="">
            <div class="row mt-3">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                    <label><strong>Logo Image :</strong></label>
                    <input class="archive-details-file-upload" type="file" name="home_logo_image" id="home_logo_image">
                    <span class="help-text">(Must be at least 200 X 200 (Width X Height) )</span>
                    <div class="clearfix"></div>
                    <img class="archive-details-logo-section marginTop5" id="home_logo_image_post_display" src="<?=$home_logo_img;?>" alt="your image" <?=!empty($home_logo_img)?'style ="display:inline;"':'';?> />
                    <a href="javascript:void(0);" class="remove-default-image" property-name="default_home_logo" id="home_logo" style="display: <?=!empty($home_logo_img)?'inline':'none';?>"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                    <input type="hidden" name="home_logo_x" id="home_logo_x" />
                    <input type="hidden" name="home_logo_y" id="home_logo_y" />
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                    <label><strong>Banner Image :</strong></label>
                    <input class="archive-details-file-upload" type="file" name="home_header_image" id="home_header_image">
                    <span class="help-text custom_temp_crop">(Must be at least 1600 X 340 (Width X Height) )</span>
                    <div class="clearfix"></div>
                    <img class="archive-details-logo-section marginTop5" id="home_header_image_post" src="<?=$home_banner_img;?>" alt="your image" <?=!empty($home_banner_img)?'style ="display:inline;"':'';?> />
                    <a href="javascript:void(0);" class="remove-default-image" property-name="default_home_header_image" id="home_banner" style="display: <?=!empty($home_banner_img)?'inline':'none';?>"><img class="marginTop5" title="Remove" src="<?php echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a>
                    <input type="hidden" name="home_header_x" id="home_header_x" />
                    <input type="hidden" name="home_header_y" id="home_header_y" />
                    <input type="hidden" name="baner_crop_height" id="baner_crop_height">
                    </div>
                </div>
            </div>
        </form>
		<form name="global_config" id="global_config" method="post" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label><strong>Home Page Title :</strong></label>
                        <input type="text" class="form-control" id="home_page_title"  name="home_page_title" placeholder="Home Page Title" value="<?=$home_page_title;?>">
                    </div> 
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label><strong>Description :</strong></label>
                        <textarea rows="1" name="home_page_description" id="home_page_description" class="form-control" placeholder="Description"><?=$home_page_description;?></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label>Trending Artifacts time line in days</label>
                        <input class="form-control" type="number" name="artifacts_time" id="artifacts_time" value="<?php echo $artifacts_time;?>"  />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group paddTop35">
                        <input class="form-check-input" type="checkbox" name="artifacts_status" id="artifacts_status"
                            value="1" <?php echo ($artifacts_status == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="artifacts_status">Show Trending Artifacts</label>
                    </div>
                </div>
            </div>
            <!-- Fix end Issue Id 0002463 26-June-2025 -->
            
			<div class="row my-20">
			    <div class="col-md-12 text-center">
                    <input class="btn btn-info" type="submit" name="submit" value="Save Configuration" />
                </div>
            </div>
            
        </form>
    </section>
    
    <div class="clearfix"></div>
</div>

<div class="modal fade" id="crop_uploading_image" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <button class="btn btn-info borderRadiusNone" id="close_crop_popup">Crop & Save</button>
                <button type="button" class="close" id="model_close_crop_popup" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="image_display_section">
                <img class="crop-popup-image-section" id="home_logo_image_pre" src="" alt="your image" style="display:none;" />
                <img class="crop-popup-image-section" id="archive_details_image_pre" src="" alt="your image" style="display:none;" />
                <img class="crop-popup-image-section" id="archive_banner_image_pre" src="" alt="your image" style="display:none;" />
            </div>
        </div>
    </div>
</div>
<!-- Fix end Issue Id 0002476 07-July-2025 -->
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<!-- Fix start Issue Id 0002476 07-July-2025 -->
<script type="text/javascript">
    var jcrop_api_logo;
    var countLogo = 0;
    function readURLLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="home_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#home_logo_image_pre').Jcrop({
                    //aspectRatio: 1,
                    onSelect: updateCoordsLogo,
                    minSize: [200, 200],
                    maxSize: [200, 200],
                    setSelect: [100, 100, 0, 0]
                }, function () {
                    jcrop_api_logo = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCoordsLogo(c) {
        $('#home_logo_x').val(c.x);
        $('#home_logo_y').val(c.y);
    }

    var jcrop_api_header;
    var countheader = 0;
    function readURLheader(input, height) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="home_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#home_logo_image_pre').Jcrop({
                    onSelect: updateCoordsheader,
                    minSize: [1600, height],
                    maxSize: [1600, height],
                    //aspectRatio: 3 / 1,
                    setSelect: [100, 100, 0, 0],
                }, function () {
                    jcrop_api_header = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
            $('#baner_crop_height').val(height);
        }
    }
    function updateCoordsheader(c) {
        $('#home_header_x').val(c.x);
        $('#home_header_y').val(c.y);
    }

    //Seprate details page logo end


    $("#home_logo_image").change(function () {
        var _URL = window.URL || window.webkitURL;
        var file = $(this)[0].files[0];
        var img = new Image();
        var imgwidth = 0;
        var imgheight = 0;
        var currentObj = this;
        img.src = _URL.createObjectURL(file);
        img.onload = function () {
            imgwidth = this.width;
            imgheight = this.height;
            if (imgwidth < 200 || imgheight < 200) {
                showPopupMessage('error', 'Image dimension must be at least 200X200(Width X Height)');
                return false;
            } else {
                $('#type').val('logo');
                if (countLogo > 0) {
                    jcrop_api_logo.destroy();
                }
                countLogo = countLogo + 1;
                readURLLogo(currentObj);
            }
        };
    });

    $("#home_header_image").change(function () {
        var crop_height = 340;
        var _URL = window.URL || window.webkitURL;
        var file = $(this)[0].files[0];
        var img = new Image();
        var imgwidth = 0;
        var imgheight = 0;
        var currentObj = this;
        img.src = _URL.createObjectURL(file);
        img.onload = function () {
            imgwidth = this.width;
            imgheight = this.height;
            if (imgwidth < 1600 || imgheight < crop_height) {
                showPopupMessage('error', 'Image dimension must be at least 1600X' + crop_height + '(Width X Height)');
                return false;
            } else {
                $('#type').val('banner');
                if (countheader > 0) {
                    jcrop_api_header.destroy();
                }
                countheader = countheader + 1;
                readURLheader(currentObj, crop_height);
            }
        };
    });

    $(document).on('click', '#model_close_crop_popup', function (e) {
	
		//alert($('#type').val());
		if($('#type').val()=='banner'){	
			$('#home_header_image').val('');
		}else if($('#type').val()=='logo'){
			$('#home_logo_image').val('');
		}
	});

    $(document).on('click', '#close_crop_popup', function (e) {
        e.preventDefault();
        location_href = 0;
        if ($("#register_society_frm").valid()) {
            $('.admin-loading-image').show();
            setTimeout(function () {
                $.ajax({
                    url: "services_admin_api.php?mode=upload_default_home_image",
                    type: "post",
                    data: new FormData($("#register_society_frm")[0]),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (response) {
                        var record = JSON.parse(response);
                        if (record.status == 'success') {
                            if (record.type == 'logo') {
                                $('#home_logo_image_post_display').attr('src', record.image);
                                $('#home_logo_image_post_display').show();
                                $('#home_logo_image_post_display').siblings('a.remove-default-image').show();
                            }
                            if (record.type == 'banner') {
                                $('#home_header_image_post').attr('src', record.image);
                                $('#home_header_image_post').show();
                                $('#home_header_image_post').siblings('a.remove-default-image').show();
                            }
                        }
                        $('.admin-loading-image').hide();
                        $('#crop_uploading_image').modal('hide');
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again');
                    }
                });
            }, 3000);
        } else {
            $('.admin-loading-image').hide();
            $('#crop_uploading_image').modal('hide');
            $('#home_logo_image,#archive_header_image,#archive_details_image,#archive_group_thumb,#archive_group_details_thumb,#historical_connection_logo').val('');
        }
    });

    $(document).on('click', '.remove-default-image', function () {
        var property_name = $(this).attr('property-name');
        if (confirm('Are you sure to delete this? Once removed can\'t be undone.')) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'remove_default_home_images', property_name: property_name},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'success') {
                        $(this).hide();
                        if (property_name == 'default_home_logo') {
                            $('#home_logo_image').val('');
                            $('#home_logo_image_post_display').attr('src', '');
                            $('#home_logo_image_post_display').hide();
                            $('#home_logo').hide();
                        }
                        if (property_name == 'default_home_header_image') {
                            $('#archive_header_image').val('');
                            $('#home_header_image_post').attr('src', '');
                            $('#home_header_image_post').hide();
                            $('#home_banner').hide();
                        }

                        $('.admin-loading-image').hide();
                    }
                },
                error: function () {
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
</script>
<!-- Fix end Issue Id 0002476 07-July-2025 -->