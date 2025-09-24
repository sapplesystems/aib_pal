<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
if(isset($_REQUEST['folder_id']) && $_REQUEST['folder_id']!= ''){
    $folder_id = $_REQUEST['folder_id'];
}else{
    $folder_id = $_SESSION['aib']['user_data']['user_top_folder'];
	
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Archive</li>
        </ol>
        <h4 class="list_title">Manage Records <?php if(isset($_SESSION['aib']['return_data']) && $_SESSION['aib']['return_data']!= ''){ ?><span class="pull-right" id="back_to_advertisement"><a href="manage_advertisements.php?return=1&<?php echo $_SESSION['aib']['return_data']; ?>"><img title="Back to advertisements" src="<?php echo IMAGE_PATH . 'back.png'; ?>" alt="Back to advertisements" /></a></span><?php } ?></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
            <div id="data-listing-section" class="col-md-12 tableStyle">
                API not available.
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>