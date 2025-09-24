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
            <li class="active">Create Fields</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Create Fields</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row"> 
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle form-group" action="" method="POST" id="addforms" name="addforms">
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Field Owner:</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">

                            <select class="form-control" id="field_owner_name" name="field_owner_name">
                                <option value="">- Select -</option> 
                                <!--   <option value='".$usertype."'>Traditional Field</option> -->
                                <!-- <option value="R">Recommended Field</option> -->
                            </select>

                        </div> 
                    </div>

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Field Name :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="field_name"  name="field_name" placeholder="Text input"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 text-right"><strong> Field Type:</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <span class=" ">
                                <select class="form-control" id="field_type_name"  name="field_type_name" >
                                    <option value="">- Select -</option> 
                                    <option value="T">Short Text, Up To 255 Characters</option>
                                    <option value="B">Long Text, Up To 64,000 Characters</option>
                                    <option value="F">Number</option>
                                    <option value="I">Whole Number</option>
                                    <option value="E">Number With Fixed Decimals</option>
                                    <option value="D">Date</option>
                                    <option value="M">Time</option>
                                    <option value="DT">Combined Date And Time</option>
                                    <option value="TS">System Timestamp (No Editing)</option>
                                    <option value="DD">Option List</option>                 
                                </select>
                            </span>
                        </div> 
                    </div>

<!--                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Field Format Detail:</strong></div>
                        <div class="col-md-7 col-sm-6 col-xs-12">
                            <textarea class="form-control" rows="3" class="field_format_detail" id="field_format_detail" name="field_format_detail" placeholder="Text input" value=""></textarea><p class="ipt_text">No special formatting options required</p>
                        </div> 
                    </div>-->

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Field Display Width :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control"  id="field_display_width_name"  name="field_display_width_name" placeholder="Text input"><p class="ipt_text">Edit to change the size of the field on the screen</p></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7"><button type="button" class="btn btn-info borderRadiusNone" id="addformsButton" name="addformsButton">Add Field</button> &nbsp;
                            <button type="button" class="btn btn-warning  borderRadiusNone clearAdminForm" id="clearformsForm02" name="clearformsForm02">Clear Field</button></div>
                    </div>

                </form>
            </div>

        </div>
    </section>
</div>


<style>
    ul.shown {
        border: 1px solid #d4d4d4;
        list-style: none;
        line-height: 26px;
        padding-left: 8px;
        min-height: 192px;
    } 
</style>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function () {
        var usertype = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        //var page_load = false;
        $('.admin-loading-image').show();
        if(usertype == 'U'){
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'fields-list',type:'manage'},
				success: function (response) {
					var record = JSON.parse(response);
					if(record.length > 4){
						showPopupMessage('error','You can\'t create more than 5 fields');
                        setTimeout(function(){
                            window.location.href='manage_fields.php';
                        }, 2000);
                        return false;
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 364)');
				}
			});
			/*
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'check_field_count_public_user'},
                success: function (response) {
                    if(response > 4){
                        showPopupMessage('error','You can\'t create more than 5 fields');
                        setTimeout(function(){
                            window.location.href='manage_fields.php';
                        }, 2000);
                        return false;
                    }
                }
            }); */
        }
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode: 'assistant_archive_item_list', type: 'ar'},
            success: function (response) {
                $('#field_owner_name').html(response);
                if (usertype == 'R'){
                    $('#field_owner_name').prepend('<option value="R">Recommended Field</option>');
                    $('#field_owner_name').prepend('<option value="S">Traditional Field</option>');
                }
                $('.admin-loading-image').hide();
            }
        });
        $('#addformsButton').click(function () {
            if ($("#addforms").valid()) {
                $('.admin-loading-image').show();
                var FormData = $("#addforms").serialize();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'create_fields', formData: FormData},
                    success: function (data) {
                        var result = JSON.parse(data);
                        $('.admin-loading-image').hide();
                        if (result.status == 'success') { 
                            showPopupMessage(result.status, result.message);
                            setTimeout(function(){
                                window.location.href='manage_fields.php';
                            }, 2000);
                        } else { 
                            showPopupMessage('error', result.message + ' (Error Code: 365)');
                        }
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again. (Error Code: 366)');
                    }
                });
            }
        });
        //Validate login form
        $("#addforms").validate({
            rules: {
                field_owner_name: {
                    required: true
                },
                field_name: {
                    required: true
                },
                field_type_name: {
                    required: true
                },
//                field_format_detail: {
//                    required: true
//                },
                field_display_width_name: {
                    required: true
                }
            },
            messages: {
                field_owner_name: {
                    required: "Please enter field owner Name"
                },
                field_name: {
                    required: "Please enter field name"
                },
                field_type_name: {
                    required: "Please enter field type name"
                },
//                field_format_detail: {
//                    required: "Please enter field format detail"
//                },
                field_display_width_name: {
                    required: "Please enter field display field name"
                }
            }
        });
    });
	
	$(function(){
		 $('#field_display_width_name').keypress(function (e) {
		 var key = e.which;
		 if(key == 13)   
		  {  
			$('#addformsButton').click();
		  }
		});
	});
</script>