<div class="clearfix"></div>
<div class="footer">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-md-2 col-sm-2 centerText"><img height="40" src="<?php echo IMAGE_PATH . 'logo.png'; ?>" alt="" /></div>
            <div class="col-md-8 text-center footerText col-sm-10">Copyright © 2017. All rights reserved. "ArchiveInABox" and box device is a registered trademark of SmallTownPapers, Inc. <br> <span>Terms of Use | Privacy & cookies | <a href="javascript:void(0);" class="request-removal">Request Content Removal</a></span></div>
            <div class="col-md-2 col-sm-12 centerText">
                <ul class="socialIcons">
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'fb.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'twitter.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'pinterest.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'linkedIn.png'; ?>"></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="request_removal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Request Content Removal</h4>
            </div>
            <div class="modal-body">
                <p>Not all articles can be screened due to the nature of the subject matter and for legal reasons. Generally, articles about felony charges filed, felony convictions, legal notices and public notices cannot be screened. The article to be screened cannot be about another person -- only the person mentioned in the article can request screening.</p>
                <h5>Newspapers only print Public Information</h5>
                <div class="box_public_info">
                    <h5>First Amendment to the Constitution of the United States</h5>
                    <p>Congress shall make no law respecting an establishment of religion, or prohibiting the free exercise thereof; or abridging the freedom of speech, or of the press; or the right of the people peaceably to assemble, and to petition the Government for a redress of grievances.</p>
                </div>
                    <p>Newspapers produce and deliver news and information which they feel is important -- most often from public information such as arrest reports, traffic infractions, accidents, indictments, lawsuits, jury verdicts, property records, legal notices, and many other sources of information which is freely available to anyone at any time. Newspaper publishers do not have access to any information which is not also available to the public at large. All information published in a newspaper is "public information."</p>
                    <p>The use of your name in an article does not mean you own it. Newspapers deliver news on all sorts of public matters which, in addition to information found in the public registers, may include statements you make to a reporter or official, your photograph, background and other information provided by you or others, rebuttals, opinions, and other information such as court-ordered legal notices, and historical and statistical data.</p>
                    <p>After you submit the form, we will review your request. We do not judge! We must follow the law regarding censorship of the press and only look at your request from that perspective. If we are legally able to screen the article, we will make every effort to do so.</p>
                    <p>We will contact you to let you know if the article can be screened and the amount of the service fee.</p>
                    <p>"The Press of the United States of America" is constitutionally protected from interference, including by the government. If you are an attorney, law enforcement officer, officer of the court, or other investigator, by law you must reveal that to us.</p>
                <div class="removeLink">
                    <form class="form-horizontal" name="content_removal_form" id="content_removal_form" method="POST">
                        <input type="hidden" name="request_type" id="request_type" value="RD">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="email" id="email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Phone Number :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="" maxlength="14">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Paste link to the article(s) you request to have removed :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="article_link" id="article_link" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Comments :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="continue_content_removal">Continue</button>
                <input type="hidden" name="rootFolder" id="rootFolder" value="<?php echo $_REQUEST['folder_id'];?>">
            </div>
        </div>
    </div>
</div>
<?php $jsArray = ['jquery-1.10.2.min.js', 'bootstrap.js', 'jquery-ui.min.js', 'jquery-form-validate.min.js', 'ideal-image-slider.js', 'iis-bullet-nav.js', 'iis-captions.js', 'common.js', 'sappleslider.multi.js', 'snap.svg-min.js', 'tabulous.js', 'jquery.dataTables.min.js', 'index.js','utility.js','jquery.inputmask.bundle.js','tag-it.js','magicsuggest.js']; ?>
<?php foreach ($jsArray as $key => $fileName) { ?>
    <script src="<?php echo JS_PATH . $fileName; ?>"></script>
<?php } ?>
<?php
    //$p_folder_id=0;
    $p_folder_id=isset($_REQUEST['folder_id'])?$_REQUEST['folder_id']:0;
    if($p_folder_id)
    {
?>
    <script type="text/javascript"> 
    $(document).ready(function () {
        var p_folder_id='<?php echo $p_folder_id;?>';
        getAdvertisement(p_folder_id);       
    });
    function getAdvertisement(folder_id){ 
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_advertisement',folder_id: folder_id ,rootId:$("#rootFolder").val()},
            success: function (response) {
                $('#adver').html(response);
            },
            error: function () {
                alert('Something went wrong, Please try again');
            }
          });
    }
</script>
<?php }?>
<script type="text/javascript">
    $(document).ready(function(){
        var phones = [{ "mask": "(###) ###-####"}, { "mask": "(###) ###-##############"}];
        $('#phone_number').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        });
        $("#content_removal_form").validate({
            rules: {
                first_name:{
                    required: true
                },
                last_name:{
                    required: true
                },
                email:{
                    required: true,
                    email: true
                },
                phone_number:{
                    required: true
                    
                },
                article_link:{
                    required: true
                },
                comments:{
                    required: true
                }
            },
            messages: { 
                first_name:{
                    required: "First name is required."
                },
                last_name:{
                    required: "Last name is required."
                },
                email:{
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                },
                phone_number:{
                    required: "Phone No. is required."
                },
                article_link:{
                    required: "Article link is required."
                },
                comments:{
                    required: "Comments is required."
                }
            }
        });
    });
    $(document).on('click','.request-removal', function(){
        $('#request_removal').modal('show');
    });
 
    $(document).on('click','#continue_content_removal', function(e){
        e.preventDefault();
        if($("#content_removal_form").valid()){
            $('.loading-div').show();
            var item_id='<?php echo ($p_folder_id != '' && $p_folder_id != 0) ? $p_folder_id : 1 ;?>';
            var formData = $('#content_removal_form').serialize();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'submit_request',formData: formData, item_id: item_id },
                success: function (response) {
                    var result = JSON.parse(response);
                    $('.loading-div').hide();
                    if(result.status == 'success'){
                        $('#content_removal_form')[0].reset();
                        $('#request_removal').modal('hide');
                        showPopupMessage('success', result.message);
                    }else{
                        showPopupMessage('error', result.message);
                    }
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again');
                }
            });
        }
    });
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23911814-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'UA-23911814-1');
</script>
<script type="text/javascript">
    var slider = $('.slider-multi').sappleMultiSlider();
    $(document).ready(function () {
        $("body").on("contextmenu", "img", function (e) {
            return false;
        });
        $(document).on('click', '.logout-user', function () {
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'logout_user'},
                success: function (result) {
                    $('.loading-div').hide();
                    window.location.href = 'index.php';
                },
                error: function () {
                    $('.loading-div').hide();
                    alert('Something went wrong, Please try again');
                }
            });
        });
    });
</script>
<?php 
include_once COMMON_TEMPLATE_PATH . 'modal.php'; 
include_once COMMON_TEMPLATE_PATH . 'popup.php';
?>
<script type="text/javascript">
	function showPopupMessage(type, message){
        $('.jquery-popup-overwrite').show();
        if(type == 'error'){
            $('#error_message').html(message);
            $('.errorMessage').show();
        }else if(type == 'success'){
            $('#success_message').html(message);
            $('.successMessage').show();
        }else{
            
        }
    }
    $(document).on('click', '.dismiss-popup', function(){
        $('.errorMessage').hide();
        $('.successMessage').hide();
        $('.jquery-popup-overwrite').hide();
    });
</script>