<?php
require_once dirname(__FILE__) . '/config/config.php';
//include_once COMMON_TEMPLATE_PATH . 'header.php';
if($_SESSION['society_template']=='custom2'){
	include_once COMMON_TEMPLATE_PATH . 'header2.php';
}else{
	include_once COMMON_TEMPLATE_PATH . 'header.php';
}
?>
<div class="clearfix"></div>
<div class="content2 marginBottom30 overflowHidden posInherit minHeightAuto marginTop100">
    <div class="container">
    <div class="row">
    <div class="col-md-10">
      <div id="dmca" class="">
       </div>
       </div>
        <div class="col-md-2">
        <div id="print_content"><button type="button" onclick="print_content('dmca')" class="btn btn-success borderRadiusNone">Print</button></div>
    </div>
</div>
</div>
</div>



<?php 
if($_SESSION['society_template']=='custom2'){
	include_once COMMON_TEMPLATE_PATH . 'footer2.php';
}else{
	include_once COMMON_TEMPLATE_PATH . 'footer.php';
}
//include_once COMMON_TEMPLATE_PATH . 'footer.php'; 
?>
<script>
$(document).ready(function(){
    get_terms_uses();
})

function get_terms_uses(){
     $('.loading-div').show();
   $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'get_term_and_condition', user_id: 1,type:'DMCA'},
        success: function (data){
             var result = JSON.parse(data);
             if(result.status == 'success'){ 
             $('#dmca').html(result.message); 
             }else{
             showPopupMessage('error', 'error','Something went wrong, Please try again. (Error Code: 171)');
             }
              $('.loading-div').hide();
        },
        error: function () {
            showPopupMessage('error','Something went wrong, Please try again. (Error Code: 172)');
        }
});
}
$(window).resize(function(){
   var height = $(this).height() - ($(".bgTopStripe").height() + $(".footer").height() + 170);
   $('.minHeightAuto').css('min-height', height+'px');
})

$(window).resize(); //on page load
</script>