<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$user_top_folder = $_SESSION['aib']['user_data']['user_top_folder'];
$archive_id    = '';
$collection_id = '';
$sub_group_id  = '';
if(isset($_REQUEST['return']) && $_REQUEST['return'] == 1){
    unset($_SESSION['aib']['return_data']);
    $archive_id    = $_REQUEST['archive_id'];
    $collection_id = $_REQUEST['collection_id'];
    $sub_group_id  = $_REQUEST['sub_group_id'];
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Advertisements Management</h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Advertisements Management</li>
        </ol>
        <h4 class="list_title">Advertisements</h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle form-group" action="" method="POST" id="advertisements_form" name="advertisements_form">
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Archive :</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_name"  name="archive_name"></select>
                            </span>
                        </div> 
                    </div>
                    <div class="row" id="collection_name_html" style="display:none;">
                        <div class="col-md-4 text-right"><strong>Collection :</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <span class="custom-dropdown">
                                <select class="form-control" id="collection_name"  name="collection_name"></select>
                            </span>
                        </div> 
                    </div>
                    <div class="row" id="subgroup_name_html" style="display:none;">
                        <div class="col-md-4 text-right"><strong>Sub Group :</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <span class="custom-dropdown">
                                <select class="form-control" id="subgroup_name"  name="subgroup_name"></select>
                            </span>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button style="display:none;" type="button" class="btn btn-info borderRadiusNone" id="go_to_record_page">Go To Record Page</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function (){
        var archive_group_id = '<?php echo $user_top_folder; ?>';
        getUserItemListing(archive_group_id,'archive_name');
    });
    $(document).on('change','#archive_name', function(){
        var archive_id = $(this).val();
        $('#collection_name_html').hide();
        $('#subgroup_name_html').hide();
        $('#go_to_record_page').hide();
        if(archive_id != ''){
            getUserItemListing(archive_id, 'collection_name');
        }
    });
    $(document).on('change','#collection_name', function(){
        var collection_id = $(this).val();
        $('#subgroup_name_html').hide();
        $('#go_to_record_page').hide();
        if(collection_id != ''){
            getUserItemListing(collection_id, 'subgroup_name');
        }
    });
    $(document).on('change','#subgroup_name', function(){
        if($(this).val() != ''){
            $('#go_to_record_page').show();
        }else{
            $('#go_to_record_page').hide();
        }
    });
    $(document).on('click', '#go_to_record_page', function(){
        var sub_group_id = $('#subgroup_name').val();
        var collection_id = $('#collection_name').val();
        var archive_id    = $('#archive_name').val();
        var encoded_data  = encodeURIComponent('archive_id='+archive_id+'&collection_id='+collection_id+'&sub_group_id='+sub_group_id);
        if(sub_group_id != ''){
            window.location.href='manage_my_archive.php?folder_id='+sub_group_id+'&adv=1&return_data='+encoded_data;
        }
    });
    var archive_id    = '<?php echo $archive_id; ?>';
    var collection_id = '<?php echo $collection_id; ?>';
    var sub_group_id  = '<?php echo $sub_group_id; ?>';
    function getUserItemListing(item_id,htmlId){
        if(item_id){
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode:'user_archive_listing',item_id: item_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    $('#'+htmlId).html('');
                    $('#'+htmlId).append('<option value="">--Select--</option>');
                    if(record.length > 0){
                        for (i = 0; i < record.length; i++) {
                            $('#'+htmlId).append('<option value="'+record[i].item_id+'">'+record[i].item_title+'</option>');
                        } 
                    }
                    $('#'+htmlId+'_html').show();
                    if(htmlId == 'archive_name' && archive_id != ''){
                        $('#archive_name').val(archive_id).trigger('change');
                        archive_id = '';
                    }
                    if(htmlId == 'collection_name' && collection_id != ''){
                        $('#collection_name').val(collection_id).trigger('change');
                        collection_id = '';
                    }
                    if(htmlId == 'subgroup_name' && sub_group_id != ''){
                        $('#subgroup_name').val(sub_group_id).trigger('change');
                        sub_group_id = '';
                    }
                    $('.admin-loading-image').hide();
                }
            });
        }
    }
</script>