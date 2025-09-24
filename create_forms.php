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
            <li class="active">Create Templates</li>
        </ol>
        <h4 class="list_title text-center"> <span class="pull-left">Create Templates</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row"> 
            <div class="col-md-12">


                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="createForms" name="createForms">
                    <div class="row">
                        <div class="col-md-2"><strong>Archive :</strong></div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <span class="custom-dropdown">
                                <select class="form-control" id="archive_name"  name="archive_name">
                                    <option value="0">- Select -</option> 
                                </select>
                            </span>
                        </div> 
                        <div class="col-md-4"></div>
                        <div class="col-md-2"><strong>Template Name :</strong></div>
                        <div class="col-md-3"><input type="text" class="form-control"  id="form_name"  name="form_name" placeholder="Text input"></div>
							<div class="col-md-1 paddLeftNone"></div>
                    </div>


                    <div class="row">
                        <div class="col-md-2"><strong>Template Field :</strong></div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <span class=" ">

                                <div class='aib-input-field-box' id='input-box-form_source_fields'>
                                    <select name='form_source_fields' class='form-control aib-dropdown field-lists' id='form_source_fields'  size='10' multiple="true" >

                                    </select>
                                </div>
                            </span>
                            <br/>

                        </div> 

                        <div class="col-md-4 col-sm-6 col-xs-12 text-center margintop"> 
                            <button type='button' id='add_field_button' class='field-move-button marginBottom10' onclick='add_field(event, this);'>&#x27F8; Add Field &#x27F9;
                            </button><br>
                            <button type='button' id='remove_field_button' class='field-move-button' onclick='remove_field(event, this);'> &#x27F8; Remove Field  &#x27F9;</button>
                        </div>


                        <div class="col-md-2"><strong>Fields On Template :</strong></div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <span class=" ">
                                <div class="aib-input-field-box" id="input-box-form_dest_fields">
                                    <select name="form_dest_fields" class="aib-dropdown form-control" id="form_dest_fields" size="10" multiple="true">
                                        <option value="NULL"> -- NO FIELDS SELECTED -- </option>
                                    </select>
                                </div>  
							 
                        </div> 
						<div class="col-md-1 paddLeftNone">
						<div style="display:block; margin-bottom:3px; margin-top:70px;"><span class="glyphicon" id="btn-up" style="cursor:pointer;">&#xe093;</span></div>
						<div style="display:block;"><span class="glyphicon" id="btn-down" style="cursor:pointer;">&#xe094;</span></div>
						</div>

                    </div>

					

                    <div class="row">
                        <div class="col-md-5"></div>
                        <div class="col-md-3"><button type="button" class="btn btn-info borderRadiusNone" id="addFormButton" >Add Template</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="clearFormbtn">Clear</button></div>
                    </div>

                </form>
            </div>

        </div>
    </section>
</div>
<style>
    ul.shown {
        border: 1px solid #15345a;
        box-shadow:0 1px 1px #777 inset;
        list-style: none;
        line-height: 26px;
        padding-left: 8px;
        min-height: 192px;
    } 


</style>

<script type="text/javascript">
    function add_field(Event, RefObj) {
		$("#form_dest_fields option[value='NULL']").remove();
        $("#form_source_fields :selected").map(function (i, el) {
            $('#form_dest_fields').append("<option selected='selected' data-c='" + $(el).data("c") + "' value='" + $(el).val() + "'>" + $(el).text() + " </option>");
            $("#form_source_fields option[value='" + $(el).val() + "']").remove();
        });

    }

    function remove_field(Event, RefObj)
    {
		var option_num = $('#form_dest_fields option').length;   
        $("#form_dest_fields :selected").map(function (i, el) {

            $("#form_dest_fields option[value='" + $(el).val() + "']").remove();
            var gc = $(el).data("c");
            var newCount = parseInt(gc) - 1;
            //$('#form_source_fields').append("<option value='" + $(el).val()+ "'>" + $(el).text() + " </option>"); 
            $('#form_source_fields option[data-c=' + newCount + ']').after("<option data-c='" + $(el).data("c") + "' value='" + $(el).val() + "'>" + $(el).text() + " </option>");

        });

    }
</script>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script>

    $(document).ready(function () {
        //########### assistant archive listing ##############
        var usertype = '<?php echo $_SESSION['aib']['user_data']['user_type']; ?>';
        $('.admin-loading-image').show();
        if(usertype == 'U'){
			$.ajax({
				url: "services_admin_api.php",
				type: "post",
				data: {mode: 'forms-list'},
				success: function (response) {
					var record = JSON.parse(response);
					if(record.length > 0){
						showPopupMessage('error','You can\'t create more than 1 template');
                        setTimeout(function(){
                            window.location.href='manage_forms.php';
                        }, 2000);
                        return false;
					}
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 115)');
				}
			});
			
            /* $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'check_template_count_public_user'},
                success: function (response) {
                    if(response > 0){
                        showPopupMessage('error','You can\'t create more than 1 template');
                        setTimeout(function(){
                            window.location.href='manage_forms.php';
                        }, 2000);
                        return false;
                    }
                }
            }); */
        }
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode: 'assistant_archive_item_list'},
            success: function (response) {
                $('#archive_name').html(response);
                template_field_data($('#archive_name').val());
            }
        });
	});
        $(document).on('change', '#archive_name', function () {
            template_field_data(this.value);
        });

        function template_field_data(arch_id) {
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'fields-list', type: 'form', arch_id: arch_id},
                success: function (response) {
                    htmData = '';
                    $('.field-lists').html('');
                    var recordArray = JSON.parse(response);
                    var section = "";
                    var globCount = 0;
                    $.each(recordArray, function (key, record) {
                        if (key == 'S') {
                            htmData += '<option disabled data-c="' + globCount + '">=====Traditional Fields=====</option>';
                            globCount++;
                        } else if (key == 'R') {
                            htmData += '<option disabled data-c="' + globCount + '">=====Recommended Fields=====</option>';
                            globCount++;
                        } else {
                            htmData += '<option disabled data-c="' + globCount + '">=====' + key + '=====</option>';
                            globCount++;
                        }
                        for (i = 0; i < record.length; i++) {
                            htmData += '<option data-c="' + globCount + '" value="' + record[i].field_id + '">' + record[i].field_title + '</option>'
                            globCount++;
                        }
                    });
                    $('.field-lists').append(htmData);
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 116)');
                }
            });

        }

        apphtmlli = '';
        $('.shown').html('');
        $(".selectcol").change(function () {
            $('.selectcol option:selected').each(function () {
                apphtmlli = apphtmlli + "<li> <input type='hidden' name='col[]' value=" + jQuery(this).text().replace(/\s/g, '_') + ">" + jQuery(this).text() + "</li>"

            });
            $('.shown').html(apphtmlli);
        });

        $('#addFormButton').click(function () {
            if ($("#createForms").valid()) {
                $('.admin-loading-image').show();
                var FormData = $("#createForms").serialize();
                var selectednumbers = [];
                /*  $('#form_dest_fields :selected').map(function(i, selected) {
                 selectednumbers.push($(selected).val());
                 });*/
                var x = document.getElementById("form_dest_fields");

                var i;
                for (i = 0; i < x.length; i++) {
                    if (x.options[i].value != 'NULL') {
                        selectednumbers.push(x.options[i].value);
                    }
                }

                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'create_forms', formData: FormData, selectvalue: selectednumbers},
                    success: function (data) {

                        var result = JSON.parse(data);
                        if (result.status == 'success') {
                            showPopupMessage(result.status, result.message);
                            setTimeout(function(){
                                window.location.href='manage_forms.php';
                            }, 2000);
                        } else {
                            showPopupMessage('error', result.message + ' (Error Code: 1164)');
                        }
                        $('.admin-loading-image').hide();
                    },
                    error: function () {
                        showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 117)');
                    }
                });
            }
        });
        //Validate login form
        $("#createForms").validate({
            rules: {
                archive_name: {
                    required: true
                },
                form_name: {
                    required: true
                },
                form_dest_fields: {
                    required: true
                }
            },
            messages: {
                archive_name: {
                    required: "Please enter form archive Name"
                },
                form_name: {
                    required: "Please enter form name"
                },
                form_dest_fields: {
                    required: "Please enter field type name"
                }

            }
        });

		
	$('#btn-up').bind('click', function() { 
        $('#form_dest_fields option:selected').each( function() {
            var newPos = $('#form_dest_fields option').index(this) - 1;
            if (newPos > -1) {
                $('#form_dest_fields option').eq(newPos).before("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
                $(this).remove();
            }
        });
    });	
	
	$('#btn-down').bind('click', function() {
        var countOptions = $('#form_dest_fields option').length;
        $('#form_dest_fields option:selected').each( function() {
            var newPos = $('#form_dest_fields option').index(this) + 1;
            if (newPos < countOptions) {
                $('#form_dest_fields option').eq(newPos).after("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
                $(this).remove();
            }
        });
    });
 


</script>
