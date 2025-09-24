<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
$archive_id = $_REQUEST['user_id'];
?>
<style>.modal-dialog{width:100% !important;} #crop_uploading_image .modal-body{overflow:auto; padding: 15px;}</style>
<div class="content-wrapper">
    <section class="content-header"> 
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Archive</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Manage Home Page Template</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span></h4>
    </section>
    <section class="content">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="marginBottom30 formStyle" class="form-group" action="" method="POST" id="editArchiveGroupDetails" name="editArchiveGroupDetails" enctype="multipart/form-data">
                    <input type="hidden" name="archive_id" id="archive_id" value="<?php echo $archive_id; ?>">
                    <input type="hidden" name="type" id="type" value="">
                   
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display Name :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_item_title"  name="archive_item_title" placeholder="Archive title" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Description :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_desc" id="archive_desc" class="form-control" placeholder="Description"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Tags :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <textarea rows="5" name="archive_tags" id="archive_tags" class="form-control" placeholder="Tags"></textarea>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display State :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <select class="form-control display_state_list"  id="archive_display_state"  name="archive_display_state"></select>
                            <!--<input type="text" class="form-control"  id="archive_display_state"  name="archive_display_state" placeholder="Text input" value="">-->
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display County :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_display_county"  name="archive_display_county" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display City :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_display_city"  name="archive_display_city" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Display Zip :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_display_zip"  name="archive_display_zip" placeholder="Text input" value="">
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Watermark Text :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input type="text" class="form-control"  id="archive_watermark_text"  name="archive_watermark_text" placeholder="Text input" value="" maxlength="30">
                        </div> 
                    </div>
					<div class="row">
                        <div class="col-md-4 text-right"><strong>Show Contact Button :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                        <label class="radio-inline"><input type="radio" name="archive_show_contact_btn" id="button_check" value="Y" >Enable</label>
						<label class="radio-inline"><input type="radio" name="archive_show_contact_btn" class="button_check" value="N" checked>Disable</label>
                        </div> 
                    </div>
					<div class="row">
                        <div class="col-md-4 text-right"><strong>Show Purchase Reprint Button :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                        <label class="radio-inline"><input type="radio" name="archive_show_purchase_reprint_btn" id="button_check_reprint" value="Y" >Enable</label>
						<label class="radio-inline"><input type="radio" name="archive_show_purchase_reprint_btn"  value="N" checked>Disable</label>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Banner Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_header_image" id="archive_header_image">
                            <span class="help-text">(Must be at least 1600 X 400 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_header_image_post" src="" alt="your image" />
                            <input type="hidden" name="archive_header_x" id="archive_header_x" />
                            <input type="hidden" name="archive_header_y" id="archive_header_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Profile Image :</strong></div>
                        <div class="col-md-8 col-sm-6 col-xs-12">
                            <input class="archive-details-file-upload" type="file" name="archive_group_thumb" id="archive_group_thumb">
                            <span class="help-text">(Must be at least 400 X 400 (Width X Height) )</span>
                            <div class="clearfix"></div>
                            <img class="archive-details-logo-section" id="archive_group_thumb_post" src="" alt="your image"/>
                            <input type="hidden" name="archive_group_thumb_x" id="archive_group_thumb_x" />
                            <input type="hidden" name="archive_group_thumb_y" id="archive_group_thumb_y" />
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-info borderRadiusNone" id="editArchiveDetails">Update</button> &nbsp;
                            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm">Clear Form</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="crop_uploading_image" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header form_header">
                <button class="btn btn-info borderRadiusNone" id="close_crop_popup">Crop & Save</button>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="image_display_section">
                <img class="crop-popup-image-section" id="archive_logo_image_pre" src="" alt="your image" style="display:none;" />
                <img class="crop-popup-image-section" id="archive_details_image_pre" src="" alt="your image" style="display:none;" />
                <img class="crop-popup-image-section" id="archive_banner_image_pre" src="" alt="your image" style="display:none;" />
            </div>
        </div>
    </div>
</div>

<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>
<script src="<?php echo JS_PATH.'jquery.inputmask.bundle.js'; ?>"></script>
<script src="<?php echo JS_PATH.'tinymce/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
var jcrop_api_header;
var countheader=0;
function readURLheader(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {	
            $('#image_display_section').html('<img id="archive_logo_image_pre" src="'+e.target.result+'">');
            $('#crop_uploading_image').modal('show');
            $('#archive_logo_image_pre').Jcrop({
                onSelect: updateCoordsheader,
                minSize : [1300,3900],
                maxSize : [1300,3900],
                aspectRatio :3/1,
                setSelect:   [ 100, 100, 50, 50 ],
            },function(){
                jcrop_api_header = this;
            });
        };
        reader.readAsDataURL(input.files[0]);	 
    }
}
function updateCoordsheader(c){
    $('#archive_header_x').val(c.x);
    $('#archive_header_y').val(c.y);
};   

var jcrop_api_gruop_thumb;
var countgroupthumb=0;
function readURLarchiveGroupThumb(input){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#image_display_section').html('<img id="archive_logo_image_pre" src="'+e.target.result+'">');
            $('#crop_uploading_image').modal('show');
            $('#archive_logo_image_pre').Jcrop({
                aspectRatio: 1,
                onSelect: updateCoordsGroupThumb,
                minSize : [400,400],
                maxSize : [400,400],
                setSelect:   [ 100, 100, 50, 50 ]
            },function(){
                jcrop_api_gruop_thumb = this;
            });
        };
        reader.readAsDataURL(input.files[0]);	 
    }
}
function updateCoordsGroupThumb(c){
    $('#archive_group_thumb_x').val(c.x);
    $('#archive_group_thumb_y').val(c.y);
}    
   
$(document).ready(function(){
    get_state();
    $("#archive_header_image").change(function(){
        var _URL = window.URL || window.webkitURL;
        var file = $(this)[0].files[0];
        var img = new Image();
        var imgwidth = 0;
        var imgheight = 0;
        var currentObj = this;
	img.src = _URL.createObjectURL(file);
        img.onload = function() {
            imgwidth = this.width;
            imgheight = this.height;
            if(imgwidth <= 1600 && imgheight <= 400){ 
				showPopupMessage('error','Image dimension must be at least 1600X400(Width X Height)');
                return false;
            }else{
                $('#type').val('banner');
                if(countheader >0){
                    jcrop_api_header.destroy();
                }
                countheader=countheader+1;
                readURLheader(currentObj);
            }
        };
    });
    
    $("#archive_group_thumb").change(function(){
        var _URL = window.URL || window.webkitURL;
        var file = $(this)[0].files[0];
        var img = new Image();
        var imgwidth = 0;
        var imgheight = 0;
        var currentObj = this;
	img.src = _URL.createObjectURL(file);
        img.onload = function() {
            imgwidth = this.width;
            imgheight = this.height;
            if(imgwidth <= 400 && imgheight <= 400){ 
		showPopupMessage('error','Image dimension must be at least 400X400(Width X Height)');
                return false;
            }else{
                $('#type').val('archive_group_thumb');
                if(countgroupthumb >0){
                    jcrop_api_gruop_thumb.destroy();
                }
                countgroupthumb=countgroupthumb+1;
                readURLarchiveGroupThumb(currentObj);
            }
        };
    });
    
    $(".modal").on("hidden.bs.modal", function(){
        $(this).removeData();
    });	
    var archive_id = '<?php echo $archive_id; ?>';
    getItemPropDetails(archive_id);
    //Validate login form
    $("#editArchiveGroupDetails").validate({
            rules: {
                archive_item_title:{
                    required: true
                },
               archive_desc: {
                    required: true 
                }
            },
            messages: { 
                archive_item_title:{
                    required: "Display name is required"
                },
               archive_desc: {
                    required: "Description is required" 
                }
            }
        });
        
        $(window).resize(function(){
            var height = $(this).height() - 130;
            $('#crop_uploading_image .modal-body').css('height', height+'px');
        });
        $(window).resize();
    });
    $(document).on('click','#editArchiveDetails',function(e){
        e.preventDefault();
        if($("#editArchiveGroupDetails").valid()){
            $('.admin-loading-image').show();
            var archiveGroupetails = $("#editArchiveGroupDetails").serialize();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'update_archive_group_details', formData: archiveGroupetails},
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.status=='success'){
                        $('.admin-loading-image').hide();
                        window.location.href="manage_my_archive.php";
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 240)');
                }
            });
        }
    });
    
    function getItemPropDetails(archive_id){
        if(archive_id != ''){
            $('.admin-loading-image').show();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_archive_prop_details',archive_id: archive_id },
                success: function (response) {
                    var record = JSON.parse(response);
                    if(record.item_title){
                        $('#archive_item_title').val(record.item_title);
                    }
                    if(record.prop_details.archive_desc){
                        $('#archive_desc').val(record.prop_details.archive_desc);
                    }
                    if(record.prop_details.archive_tags){
                        $('#archive_tags').val(record.prop_details.archive_tags);
                    }
                    if(record.prop_details.archive_display_state){
                        $('#archive_display_state').val(record.prop_details.archive_display_state);
                    }
                    if(record.prop_details.archive_display_county){
                        $('#archive_display_county').val(record.prop_details.archive_display_county);
                    }
                    if(record.prop_details.archive_display_city){
                        $('#archive_display_city').val(record.prop_details.archive_display_city);
                    }
                    if(record.prop_details.archive_display_zip){
                        $('#archive_display_zip').val(record.prop_details.archive_display_zip);
                    }
                    if(record.prop_details.archive_watermark_text){ 
                        $('#archive_watermark_text').val($.trim(record.prop_details.archive_watermark_text));
                    }
                    if(record.prop_details.archive_show_contact_btn =='Y'){  
                        $("#button_check").attr("checked", true); 
                    }
                    if(record.prop_details.archive_show_purchase_reprint_btn =='Y'){  
                        $("#button_check_reprint").attr("checked", true); 
                    }
                    if(record.prop_details.archive_header_image){
                        $('#archive_header_image_post').attr('src','tmp/'+record.prop_details.archive_header_image);
                        $('#archive_header_image_post').show();
                    }
                    if(record.prop_details.archive_group_thumb){
                        $('#archive_group_thumb_post').attr('src','tmp/'+record.prop_details.archive_group_thumb);
                        $('#archive_group_thumb_post').show();
                    }
                    $('.admin-loading-image').hide();
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 241)');
                }
            });
        }
    }
    $(document).on('click', '#close_crop_popup', function(e){
        e.preventDefault();
        $('.admin-loading-image').show();
        $.ajax({
            url: "services_admin_api.php?mode=upload_croped_image",
            type: "post",
            data: new FormData($("#editArchiveGroupDetails")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function (response) {
                var record = JSON.parse(response);
                if(record.status == 'success'){
                    if(record.type=='banner'){
                        $('#archive_header_image_post').attr('src',record.image);
                        $('#archive_header_image_post').show();
                    }
                    if(record.type=='archive_group_thumb'){
                        $('#archive_group_thumb_post').attr('src',record.image);
                        $('#archive_group_thumb_post').show();
                    }
                }
                $('.admin-loading-image').hide();
                $('#crop_uploading_image').modal('hide');
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 242)');
            }
        });
    });
    function get_state(){
         $('.loading-div').show();
         var parent_id = '<?php echo STATE_PARENT_ID; ?>';
         $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'get_state_country', parent_id:parent_id},
                success: function (response) { 
		    var record = JSON.parse(response);
                    var i;
                    var display_state = "";
                   
                    display_state += "<option value='' >---Select---</option>";
                    for (i = 0; i < record.length; i++) {
                       display_state +="<option value='" + record[i] + "'  >" + record[i] + "</option>";
                    }
                        $(".display_state_list").html(display_state);
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 243)');
                    $('.loading-div').hide();
                }
         });
    }
</script>