<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Scrapbook</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Add Scrapbook</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Add Scrapbook </span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="add_scrapbook_form" name="add_scrapbook_form">
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Scrapbook Title :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="scrapbook_title"  name="scrapbook_title" placeholder="Enter scrapbook title"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Scrapbook Type :</strong></div>
                        <div class="col-md-7">
                            <select name="scrapbook_type" id="scrapbook_type" class="form-control">
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-info borderRadiusNone" id="add_scrapbook_button" name="add_scrapbook_button">Add Scrapbook</button>
                            <button type="button" class="btn btn-warning  borderRadiusNone" id="clearformsForm02" name="clearformsForm02">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#add_scrapbook_form").validate({
            rules: {
                scrapbook_title: {
                    required: true
                }
            },
            messages: {
                scrapbook_title: {
                    required: "Please enter scrapbook title"
                }
            }
        });
        $(document).on('click', '#add_scrapbook_button', function(){
            if ($("#add_scrapbook_form").valid()) {
                $('.admin-loading-image').show();
                var formData = $("#add_scrapbook_form").serialize();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'add_new_scrapbook', formData: formData },
                    success: function (data) {
                        var record = JSON.parse(data);
                        $('.admin-loading-image').hide();
                        showPopupMessage(record.status,record.message);
                        if(record.status == 'success'){
                            setTimeout(function(){
                                window.location.href='manage_scrapbook.php';
                            },1000);
                        }
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again. (Error Code: 344)');
                    }
                });
            }
        });
    });
</script>