<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$loggedInUserId = $_SESSION['aib']['user_data']['user_id'];

$location_list = array(
    'home_page_template' => 'Home Page Template',
    'my_account' => 'My Account',
);
$location_list_front = array(
    'record_detail' => 'Record Detail',
    'create_people_account' => 'Create People Account',
);
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Help Message</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Help Message</li>
        </ol>
        <h4 class="list_title text-center"> <span class="pull-left">Help Message</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title']; ?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="alert alert-dismissible" id="message"></div>
        <div class="row">
            <div class="col-md-3">
                <select name="location" id="location" class="form-control">
                    <option value="1">Admin Section</option>
                    <option value="2">Browse Section</option>

                </select>
            </div>
            <div class="col-md-3">
                <select name="page_frontend" id="page_frontend" class="form-control" style="display:none">
                    <option value="">Select Location</option>
                    <?php foreach ($location_list_front as $k => $v) { ?>
                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                    <?php } ?>
                </select>
                <select name="page_backend" id="page_backend" class="form-control">
                    <option value="">Select Location</option>
                    <?php foreach ($location_list as $k => $v) { ?>
                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div id="template_form" style="padding-top:10px;"></div>
    </section>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#location').change(function () {
            var location_id = $(this).val();
            $('#page_backend').val("");
            $('#page_frontend').val("");
            if (location_id == 1) {
                $('#page_backend').show();
                $('#page_frontend').hide();
            }
            else {
                $('#page_frontend').show();
                $('#page_backend').hide();
            }
        });
        $('#page_frontend').change(function () {
            var location_id = $(this).val();
            $('#template_form').html('');
            if (location_id) {
                $.ajax({
                    url: "help_message_template/" + location_id + ".php",
                    type: "post",
                    data: {location_id: location_id},
                    success: function (data) {
                        if (data && data != '') {
                            $('#template_form').html(data);
                            getAllHelpMessage(location_id);
                        }
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 458)');
                    }
                });
            }
        });
        $('#page_backend').change(function () {
            var location_id = $(this).val();
            $('#template_form').html('');
            if (location_id) {
                $.ajax({
                    url: "help_message_template/" + location_id + ".php",
                    type: "post",
                    data: {location_id: location_id},
                    success: function (data) {
                        if (data && data != '') {
                            $('#template_form').html(data);
                            getAllHelpMessage(location_id);
                        }
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 459)');
                    }
                });
            }
        });
    });

    function saveHelpMessage(e, element_name, id, location_id) {
        e.preventDefault();
        var element = document.getElementById(element_name);
        if (element_name && location_id) {
            if (element && element.value != '') {
                var data = {
                    element_id: id,
                    language: 'en',
                    text: element.value,
                    name: element_name,
                    location: location_id,
                };
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'save_help_message', data: data},
                    success: function (response) {
                        var record = JSON.parse(response);
                        $('.admin-loading-image').hide();
                        if (record.status == 'OK') {
                            showPopupMessage('success', 'Message added successfully.');
                        }
                        else {
                            showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 460)');
                        }
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 461)');
                        $('.admin-loading-image').hide();
                    }
                });
            } else {
                element.focus();
                return false;
            }
        }
    }

    function getAllHelpMessage(location) {
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'list_all_help_message', location: location},
            success: function (response) {
                var obj = JSON.parse(response);
                $('.admin-loading-image').hide();
                if (obj.status == 'OK') {
                    var record_length = obj.info.records.length;
                    for (var i = 0; i < record_length; i++) {
                        var elm = document.getElementById(obj.info.records[i].element_name);
                        if (elm) {
                            elm.value = obj.info.records[i].element_text;
                        }
                    }
                    //var table = $('#myTable').DataTable({"pageLength": 100,"sDom":'<"H"lfrp>t<"F"ip>'});
                }
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 462)');
                $('.admin-loading-image').hide();
            }
        });
    }
</script>