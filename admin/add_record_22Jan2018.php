<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login');
    exit;
}
if(isset($_REQUEST['folder_id']) && $_REQUEST['folder_id']!= ''){
    $folder_id = $_REQUEST['folder_id'];
}else{
    $folder_id = 148;
}
$previous = '';
if(isset($_REQUEST['previous']) && $_REQUEST['previous'] != ''){
    $previous = $_REQUEST['previous'];
}
include_once 'config/config.php';
    include_once COMMON_TEMPLATE_PATH.'header.php';
    include_once COMMON_TEMPLATE_PATH.'sidebar.php';
  ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>My Archive</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Add Record</li>
            </ol>
           <h4 class="list_title">Add Record </h4>
        </section>
        <section class="content bgTexture">
        
<div class="content2">
<div class="container-fluid">
<div class="row-fluid">
<div class="col-md-3 col-sm-3">
<input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
			 <div id="record-listing-section" class="tableStyle">
</div>
</div>
<div class="col-md-6 col-sm-6 bgTexture"><h4><strong>You are creating a record in : <span class="whitetext">Sapple 1 sb group</span></strong> <div class="btn btn-primary pull-right  backbtn">Go back to</div></h4>
<p class="errorMsg">Incomplete or missing information. To continue, please provide information for the required fields highlighted below.</p>
<form class="marginBottom30 formStyle">
<div class="row">
<div class="col-md-4 text-right"><strong>Record Name :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Visible to Public? :</strong></div>
<div class="col-md-3">
<span class="custom-dropdown">
    <select>
        <option>- Select -</option>
        <option>Sherlock Holmes</option>
        <option>The Great Gatsby</option>  
        <option>V for Vendetta</option>
        <option>The Wolf of Wallstreet</option>
        <option>Quantum of Solace</option>
    </select>
</span>
</div>
<div class="col-md-4 paddLeftNone"><span class="infotext">If ’Yes’, then sub group can be seen by public users.</span></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Private :</strong></div>
<div class="col-md-3">
<span class="custom-dropdown">
    <select>
        <option>- Select -</option>
        <option>Sherlock Holmes</option>
        <option>The Great Gatsby</option>  
        <option>V for Vendetta</option>
        <option>The Wolf of Wallstreet</option>
        <option>Quantum of Solace</option>
    </select>
</span>
</div>
<div class="col-md-4 paddLeftNone"><span class="infotext">Private records are not published (anyone with a direct link can view this record)</span></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Perform :</strong></div>
<div class="col-md-3">
<span class="custom-dropdown">
    <select>
        <option>- Select -</option>
        <option>Sherlock Holmes</option>
        <option>The Great Gatsby</option>  
        <option>V for Vendetta</option>
        <option>The Wolf of Wallstreet</option>
        <option>Quantum of Solace</option>
    </select>
</span>
</div>
<div class="col-md-4 paddLeftNone"><span class="infotext">If selected , system will submit files for OCR processing</span></div>
</div>

<div id="add_field" >
<div class="row">
<div class="col-md-4 text-right"><strong>Custom Form :</strong></div>
<div class="col-md-3 col-sm-6 col-xs-12">
<span class="custom-dropdown">
    <select id="choose_custom_template">
        <option value="">- Select -</option>
    </select>
</span>
</div>
<div class="col-md-4 col-sm-6 col-xs-12">
<button type="button" class="btn btn-info btnCustom borderRadiusNone" id="custom_template_button">Use This Custom Form</button>
</div>
</div>


<div  id='TextBoxesGroup'>
</div><br/>
</div>

<div id="TextBoxDiv">

</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Name :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Email :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Mobile :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Text Recommended Field :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Discription :</strong></div>
<div class="col-md-7"><textarea class="form-control" rows="3"></textarea></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Date/Time :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Creator :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>URL for this Record :</strong></div>
<div class="col-md-7"><input type="text" class="form-control" placeholder="Text input"></div>
</div>

<div class="row">
<div class="col-md-4 text-right"></div>
<div class="col-md-7"><label class="radio-inline">
  <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1"> Attach all files to this single record
</label></div>
</div>

<div class="row">
<div class="col-md-4 text-right"></div>
<div class="col-md-7"><label class="radio-inline">
  <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1"> Create individual records for each attachment using the file name as the title for each record (all other fields will be duplicated)
</label></div>
</div>

<div class="row">
<div class="col-md-4 text-right"><strong>Upload :</strong></div>
<div class="col-md-7"><div class="form-group">
    <input type="file" id="exampleInputFile">
  </div></div>
</div>

<div class="row">
<div class="col-md-4"></div>
<div class="col-md-7 "><button type="submit" class="btn btn-info btnCustomTwo">Add Record</button> &nbsp;<button type="submit" class="btn btn-danger btnCustomTwo">Clear Form</button></div>
</div>

</form>
<div class="clearfix"></div>
</div>
<div class="col-md-3 col-sm-3">
<div class="fieldSection">
    <h4><strong>Traditional Fields</strong></h4>
    <div  id="data_field">

    </div>
    <h4 class="marginTop20"><strong>Recommended Fields</strong></h4>
    <div id="data_field_recom">
    </div>

</div>
</div>
</div>
</div>
</div>
</div>

</section>

<?php  include_once COMMON_TEMPLATE_PATH.'footer.php'; ?>

  
  <script type="text/javascript">

    $(document).ready(function () {
        $('.sidebar-toggle').trigger('click');
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'fields-list'},
            success: function (response) {
                htmData = '';
                htmDataRecom= '';
                var record = JSON.parse(response);
                for (i = 0; i < record.length; i++) {
                    if(record[i].field_owner_type == 'S') {
                       htmData +='<div class="row"><div class="col-md-5"><strong>'+ record[i].field_title + '</strong></div>'+'<div class="col-md-3"><button type="submit" class="btn btn-info btnrmv btnCustomThree add_custom_fields borderRadiusNone" id="add_field_btn_'+ record[i].field_id +'" data-field-title="'+record[i].field_title+'" data-field-id='+ record[i].field_id +'>Add</button></div>'+'<div class="col-md-4 paddLeftNone"><button type="submit"  id="removebtn_'+ record[i].field_id +'" class="btn btn-danger btnCustomThree enableOnInput remove_custom_field " data-rmv-id='+record[i].field_id +' disabled  >Remove</button></div></div>'
                     }
                     else {
                        htmDataRecom +='<div class="row" data-field-title='+ record[i].field_title +'><div class="col-md-5"><strong>'+ record[i].field_title + '</strong></div>'+'<div class="col-md-3"><button type="submit" class="btn btn-info btnrmv btnCustomThree add_custom_fields borderRadiusNone" data-field-title="'+record[i].field_title+'" data-field-id='+ record[i].field_id +' id="add_field_btn_'+ record[i].field_id +'">Add</button></div>'+'<div class="col-md-4 paddLeftNone"><button type="submit" id="removebtn_'+ record[i].field_id +'" class="btn btn-danger btnCustomThree enableOnInput remove_custom_field"  data-rmv-id='+record[i].field_id +' disabled >Remove</button></div></div>'
                     }
                }
                $('#data_field').append(htmData);
                $('#data_field_recom').append(htmDataRecom);
            },
            error: function () {
                alert('Something went wrong, Please try again');
            }
        });

        $(document).on('click', '.add_custom_fields', function () {
 
           var field_id = $(this).attr('data-field-id'); 
           var field_title_rec = $(this).attr('data-field-title');
           var field_rmv_id =$(this).attr('data-rmv-id'); 

              $("button[data-field-id='"+ field_id+ "']").attr("disabled", "disabled");
              $("button[data-rmv-id='"+ field_id+ "']").removeAttr("disabled");
    
             var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv_'+field_id);

              newTextBoxDiv.after().html('<div class="row" id="textbox_'+ field_id +'"><div class="col-md-4 text-right"><strong>'+field_title_rec+'</strong></div>'+'<div class="col-md-7"><input type="text" name="textbox_'+ field_id +'"  class="form-control" placeholder="Text input"  id="textbox_'+ field_id +'" value="" ></div></div>');
                 newTextBoxDiv.appendTo("#TextBoxesGroup"); 
        });
 
       $(document).on('click', '.remove_custom_field', function () {
           var field_id = $(this).attr('data-rmv-id');
           $("#TextBoxDiv_"+field_id).remove();
           $("button[data-field-id='"+ field_id+ "']").removeAttr("disabled");
           $("button[data-rmv-id='"+ field_id+ "']").attr("disabled", "disabled");
       });
  

         $.ajax({
               url: "services_admin_api.php",
                type: "post",
                data: {mode: 'forms-list'},
                success: function (response){
                        htmformData = '';
                       var record = JSON.parse(response);  
                         for(i=0;i<record.length;i++){ 
                            htmformData +='<option value="'+ record[i].form_id + '">'+ record[i].form_title + '</option>'
                         }
                       $('#choose_custom_template').append(htmformData);
                },
                error: function () {
                    alert('Something went wrong, Please try again');
                }

           });

   
        var folder_id = '<?php echo $folder_id; ?>';
	    var previous_id = '<?php echo $previous; ?>';
        var previousObj = [];
        if(previous_id != ''){
            previousObj =previous_id.split(',');
        }
        $('#current-item-id').val(folder_id);
        $('#previous-item-id').val(JSON.stringify(previousObj));
        getRecordItemDetailsById(folder_id);

   });

    $(document).on('click', '#custom_template_button', function () {
        var selected_form_id = $('#choose_custom_template').val();
        if($('.custom_form_field_matched').length){
            $('.custom_form_field_matched').each(function(){
                var remove_id = $(this).attr('data-rmv-id');
                $('#removebtn_'+remove_id).attr('disabled',true);
                $('#add_field_btn_'+remove_id).attr('disabled',false);
                
            });
        }
        if(selected_form_id != ''){
            $.ajax({
               url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_custom_form_fields', form_id: selected_form_id},
                success: function (response){
                    $('#TextBoxDiv').html(response);
                    $('.custom_form_fields').each(function(){
                        var custom_field_id = $(this).attr('custom_field_id');
                        $('.btnCustomThree').each(function(){
                            var field_id = $(this).attr('data-field-id');
                            if(custom_field_id == field_id){
                                $(this).attr('disabled',true);
                                $('#removebtn_'+field_id).attr('disabled',false);
                                $('#removebtn_'+field_id).addClass('custom_form_field_matched');
                            }
                        });
                    });
                }
           }); 
        }
     });   
   
   $(document).on('click', '.getRecordItemDataByFolderId', function () {
        var current_id = $('#current-item-id').val();
        var previous_id_obj = JSON.parse($('#previous-item-id').val());
        previous_id_obj.push(current_id);
        var item_folder_id = $(this).attr('data-folder-id');
        $('#current-item-id').val(item_folder_id);
        $('#previous-item-id').val(JSON.stringify(previous_id_obj));
        getRecordItemDetailsById(item_folder_id);
    }); 

    function getRecordItemDetailsById(id){
        if (id) {
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'list_tree_items_records', folder_id: id},
                success: function (result) {
                    $('#record-listing-section').html(result);
                },
                error: function () {
                    $('.loading-div').hide();
                    alert('Something went wrong, Please try again');
                }
            });
        }
    }
</script>

