<?php
session_start();

include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';

?>
<div class="content-wrapper">
    <form name="registrationForm" id="registrationForm" method="post" action="" enctype="multipart/form-data">
		<input type="hidden" name="user_type" value="A">
		<div class="content2 contactInfo" style="min-height: 400px; padding:0 15px;">
			<div class="container">
				<div class="row marginTop20 bgNone"><h3>Create Society</h3></div>
				<div class="row marginTop20 padd5">
					<div class="col-md-3" >
						<label>Society Name<span>*</span>:</label>
					</div>
					<div class="col-md-3" >
						<input type="text" class="form-control" name="society_name" id="society_name1" value="<?php if(!empty($data)){echo $data['society_name'];} ?>" placeholder="Enter text">
					</div> 
				</div>

				<div class="row marginTop20 padd5">
					<div class="col-md-3" >
						<label>State<span>*</span>:</label>
					</div>
					<div class="col-md-3" >
						<!--<input type="text" class="form-control" name="society_state" id="society_state" value="<?php if(!empty($data)){echo $data['society_state'];} ?>" placeholder="Enter text">-->
						<select class="form-control" name="society_state" id="society_state"> </select>
					</div> 
				</div>
				
				<div class="row marginTop20 padd5">
					<div class="col-md-3" >
						<label>Logo Image<span>*</span>:</label>
					</div>
					<div class="col-md-3" >
						<input class="archive-details-file-upload" type="file" name="archive_logo_image" id="archive_logo_image">
						<span class="help-text">(Must be at least 200 X 200 (Width X Height) )</span>
						<div class="clearfix"></div>
						<img class="archive-details-logo-section" id="archive_logo_image_post_display" src="" alt="your image" />
						<!--a href="javascript:void(0);" class="remove-society-image" property-name="archive_logo_image"><img title="Remove" src="<?php //echo IMAGE_PATH . 'delete_icon.png'; ?>" alt="" /></a-->
						<input type="hidden" name="archive_logo_x" id="archive_logo_x" />
						<input type="hidden" name="archive_logo_y" id="archive_logo_y" />
					</div> 
				</div>
				 
				<div class="row marginTop20 bgNone"> 
				<div class="col-md-3"></div>          
					<div class="col-md-2">
						<input type="hidden" name="unclaimed_society" value="1" />
						<input type="hidden" name="type" value="logo" />
						<input type="hidden" name="archive_id" id="archive_id" value="" />
						<input type="button" class="form-control btn-success" name="regSubmit" id="regSubmit" value="Submit" >
					</div> 
				 </div>
				
				<div class="clearfix marginTop20"></div>
				<div class="row">
					<div class="col-md-12" id="search_result_render_space" style="display:none;">Loading....</div>
				</div>
			</div>
		</div>
	</form>
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
<script type="text/javascript">
	var jcrop_api_logo;
    var countLogo = 0;
    function readURLLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_display_section').html('<img id="archive_logo_image_pre" src="' + e.target.result + '">');
                $('#crop_uploading_image').modal('show');
                $('#archive_logo_image_pre').Jcrop({
                    aspectRatio: 1,
                    onSelect: updateCoordsLogo,
                    minSize: [200, 200],
                    maxSize: [200, 200],
                    setSelect: [100, 100, 50, 50]
                }, function () {
                    jcrop_api_logo = this;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
	
	function updateCoordsLogo(c) {
        $('#archive_logo_x').val(c.x);
        $('#archive_logo_y').val(c.y);
    }

    $(document).ready(function(){
		$("#archive_logo_image").change(function () {
            var _URL = window.URL || window.webkitURL;
            var file = $(this)[0].files[0];
            var img = new Image();
            var imgwidth = 0;
            var imgheight = 0;
            var currentObj = this;
            img.src = _URL.createObjectURL(file);
            img.onload = function () {
                imgwidth = this.width;
                imgheight = this.height;
                if (imgwidth <= 200 && imgheight <= 200) {
                    showPopupMessage('error', 'Image dimension must be at least 200X200(Width X Height)');
                    return false;
                } else {
                    $('#type').val('logo');
                    if (countLogo > 0) {
                        jcrop_api_logo.destroy();
                    }
                    countLogo = countLogo + 1;
                    readURLLogo(currentObj);
                }
            };
        });
		
        get_state();
        var phones = [{ "mask": "(###) ###-####"}, { "mask": "(###) ###-##############"}];
        $('#phoneNumber').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        });
        $("#registrationForm").validate({
            rules: {                
                society_name: "required",
                society_state :{
                    required:true,
                    remote: {
                        url:"../services.php",
                        type:"POST",
                        data:{ 
                            mode:function(){ return "check_duplicate_item";},
                            society_name:function(){ return $("#society_name1").val();}
                        }
                    }
                },
            },
            messages: {                
                society_name: "Please enter society name",
                society_state :{
                 required:"Society state is required",
                 remote: jQuery.validator.format("Historical Society Name is already taken for the state {0}.")
                }
            }
        });
        
        $("#regSubmit").on("click",function(e){
			var arc_id = document.getElementById('archive_id').value;
			if($("#registrationForm").valid()==true){
				if(arc_id && arc_id!=''){
						updateRegistrationDetails();
				}else{
					var dataVal = $("#registrationForm").serialize();
					console.log(dataVal);
					return false;
					$('.loading-div').show();
					$.ajax({
						url: "../services.php",
						type: "post",
						data: {mode: 'register_new_user', formData: dataVal},
						success: function (response) { 
						console.log(response);
						var record = JSON.parse(response);
								$('.loading-div').hide();
								document.body.scrollTop = 0;
								if(record.status == 'success'){ 
									//window.location.href = 'manage_my_archive.php';// 'thank-you.html?flg=archive_user_reg';
								}
								else{
									alert(record.message);
								}
							},
							error: function () {
								alert('Something went wrong, Please try again');
								$('.loading-div').hide();
							}
					 }); 
				}
			} else{
			   $('.error:visible:first').focus();
				scrollTop: $('.error:visible:first').offset().top;
			}
        });
		
		$(document).on('click', '#close_crop_popup', function (e) {
			e.preventDefault();
			var dataVal = $("#registrationForm").serialize();
			$('.admin-loading-image').show();
			$.ajax({
				url: "../services.php",
				type: "post",
				data: {mode: 'register_new_user', formData: dataVal},
				success: function (response) { 
				var record = JSON.parse(response);
						$('.loading-div').hide();
						document.body.scrollTop = 0;
						if(record.status == 'success'){ 
							document.getElementById('archive_id').value = record.archive_id;
							$.ajax({
								url: "services_admin_api.php?mode=upload_croped_image",
								type: "post",
								data: new FormData($("#registrationForm")[0]),
								contentType: false,
								cache: false,
								processData: false,
								success: function (response) {
									var record = JSON.parse(response);
									if (record.status == 'success') {
										if (record.type == 'logo') {
											$('#archive_logo_image_post_display').attr('src', record.image);
											$('#archive_logo_image_post_display').show();
											//$('#archive_logo_image_post_display').siblings('a.remove-society-image').show();
										}
									}
									$('.admin-loading-image').hide();
									$('#crop_uploading_image').modal('hide');
								},
								error: function () {
									showPopupMessage('error', 'Something went wrong, Please try again');
								}
							});
						}
						else{
							alert(record.message);
						}
					},
					error: function () {
						alert('Something went wrong, Please try again');
						$('.loading-div').hide();
					}
			 });
		});
		
		$(document).on('click', '.remove-society-image', function(){
			var property_name = $('#society_name1').val();
			var item_id       = $('#archive_id').val();
			if(confirm('Are you sure to delete this? Once removed can\'t be undone.')){
				$('.admin-loading-image').show();
				$.ajax({
					url: "services_admin_api.php",
					type: "post",
					data: {mode: 'remove_society_images', property_name: property_name, item_id: item_id},
					success: function (response) {
						var record = JSON.parse(response);
						if(record.status == 'success'){
							showPopupMessage(record.status, record.message);
						}
					},
					error: function () {
						showPopupMessage('error', 'Something went wrong, Please try again');
					}
				});
			}
		});
    });
	
    function get_state(){
         $('.loading-div').show();
         var parent_id = '<?php echo STATE_PARENT_ID; ?>';
         var society_state = '<?php echo $data['society_state']; ?>';
         $.ajax({
                url: "../services.php",
                type: "post",
                data: {mode: 'get_state_country', parent_id:parent_id},
                success: function (response) { 
				var record = JSON.parse(response);
                    var i;
                    var state = "";
                    state += "<option value='' >---Select---</option>";
                    for (i = 0; i < record.length; i++) {
                        var data_value = '';
                       if(society_state == record[i]){ data_value = 'selected' ;}
                            state += "<option value='" + record[i] + "'  "+ data_value +" >" + record[i] + "</option>";
                        }
                        $("#society_state").html(state);
                    $('.loading-div').hide();
                   
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again');
                    $('.loading-div').hide();
                }
         }); 
    }
	
	function updateRegistrationDetails(){ 
		$('.admin-loading-image').show();
		var updateRegistrationData=$("#registrationForm").serialize();
		$.ajax({
			url: "services_admin_api.php",
			type: "post",
			data: {mode: 'update_archive_registration_details', formData: updateRegistrationData},
			success: function (response) {
				var record = JSON.parse(response);
				if (record.status == 'success') {
					 window.location.href = 'manage_my_archive.php';
				}
				$('.admin-loading-image').hide();
			},
			error: function () {
				showPopupMessage('error','Something went wrong, Please try again');
			}
		});
	}
</script>