<?php
foreach ($formdata as $formlist) {
    ?>
	<div class="createForms">
    <div class="row">
        <div class="col-md-2"><strong>Template Name :</strong></div>
        <div class="col-md-3"> <input type="text" class="form-control" name="form_name" id="form_name" value="<?php echo $formlist['form_name']; ?>" /></div>
        <input type="hidden" class="form-control" name="form_id" id="form_id" value="<?php echo $formlist['form_id']; ?>" />      
    </div><br/>
    <div class="row">
        <div class="col-md-2"><strong>Template Field :</strong></div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <span class=" ">

                <div class='aib-input-field-box' id='input-box-form_source_fields'>
                    <select name='form_source_fields' class='form-control aib-dropdown field-lists'  id='form_source_fields'  size='10' multiple="true" ></select>
                </div>
            </span>
            <br/>
        </div> 
        <div class="col-md-4 col-sm-6 col-xs-12 text-center margintop"> 
            <button type='button' id='add_field_button' class='field-move-button marginBottom10' onclick='add_field(event, this);'>&#x27F8; Add Field &#x27F9;
            </button>
            <button type='button' id='remove_field_button' class='field-move-button' onclick='remove_field(event, this);'> &#x27F8; Remove Field  &#x27F9;</button> 
        </div>
        <div class="col-md-2"><strong>Fields On Template :</strong></div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="aib-input-field-box" id="input-box-form_dest_fields">
                <select name="form_dest_fields" class="aib-dropdown form-control"  id="form_dest_fields" size="10" multiple="true">
                    <option value="NULL"> -- NO FIELDS SELECTED -- </option>
                    <?php
                    $fieldsOnForm = '';
                    foreach ($fielddata as $val) {
                        $fieldsOnForm .= $val['form_field_id'] . ',';
                        ?>
                        <option  selected= "selected" id="form_field" value="<?php echo $val['form_field_id']; ?>"><?php echo urldecode($val['field_title']); ?></option>
                    <?php } ?>     
                </select>
                <input type="hidden" name="fields_on_form" id="fields_on_form" value="<?php echo $fieldsOnForm; ?>" >
            </div>  
			 </div>
						<div class="col-md-1 paddLeftNone">
				<div style="display:block; margin-bottom:3px; margin-top:70px;"><span class="glyphicon" id="btn-up" style="cursor:pointer;">&#xe093;</span></div>
						<div style="display:block;"><span class="glyphicon" id="btn-down" style="cursor:pointer;">&#xe094;</span></div>
        </div> 
    </div>
<?php } ?>
<div class="form-group">
    <label class="col-xs-4 control-label"></label>
    <div class="col-xs-7">
        <button type="button" class="btn btn-info borderRadiusNone" name="forms_btn" id="forms_btn">Update Template</button>
        <button type="button" class="btn btn-danger borderRadiusNone" id="clearformsForm">Clear</button>
    </div>
</div>
</div>


<script type="text/javascript">
    function add_field(Event, RefObj) {
		$("#form_dest_fields option[value='NULL']").remove();
        $("#form_source_fields :selected").map(function (i, el) {
            $('#form_dest_fields').append("<option selected='selected' data-c='"+$(el).data("c")+"' value='" + $(el).val() + "'>" + $(el).text() + " </option>");
            $("#form_source_fields option[value='" + $(el).val() + "']").remove();
        });
    }

    function remove_field(Event, RefObj) {
        $("#form_dest_fields :selected").map(function (i, el) {
            $("#form_dest_fields option[value='" + $(el).val() + "']").remove();
            var gc=$(el).data("c");
            var newCount=parseInt(gc)-1;
            
            //$('#form_source_fields').append("<option value='" + $(el).val() + "'>" + $(el).text() + " </option>");
            if(typeof gc =='undefined'){
                $('#form_source_fields').append("<option value='" + $(el).val() + "'>" + $(el).text() + " </option>");
            }else{
                $('#form_source_fields option[data-c='+newCount+']').after("<option data-c='"+$(el).data("c")+"' value='" + $(el).val()+ "'>" + $(el).text() + " </option>");
            }
        });
    }
</script>
<script type="text/javascript">
    $(document).ready(function () {
		var archive_id = '<?php echo $form_owner_id; ?>';
        $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {mode: 'assistant_archive_item_list'},
            success: function (response) {
                $('#archive_name').html(response);
				template_field_data(archive_id );
            }
        });
		function template_field_data(archive_id){
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'fields-list', type: 'edit',arch_id : archive_id},
            success: function (response) {
                htmData = '';
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
                    }
                });
                $('.field-lists').append(htmData);
                $('.admin-loading-image').hide();
            },
            error: function () {
                showPopupMessage('error', record.message);
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
	
		
    });
</script>
