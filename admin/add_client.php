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
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Clients</li>
        </ol>
        <h4 class="list_title">Add New Client</h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="addClientForm" name="addClientForm">
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Archive Group Code :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="archive_group_code"  name="archive_group_code" placeholder="Text input"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Archive Group Title :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control" id="archive_group_title"  name="archive_group_title" placeholder="Text input"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-info borderRadiusNone" id="addClientButton">Add Client</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone" id="clearClientForm">Clear Form</button></div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#addClientButton').click(function(){
            if($("#addClientForm").valid()){
                
            }
        });
        $("#addClientForm").validate({
            rules: {
                archive_group_code:{
                    required: true
                },
                archive_group_title:{
                    required: true
                }
            },
            messages: {
                archive_group_code:{
                    required: "Archive group code is required"
                },
                archive_group_title:{
                    required: "Archive group title is required"
                }
            }
        });
    });
</script>