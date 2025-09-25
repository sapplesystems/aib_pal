<?php
$is_user_logged_in = $_SESSION['aib']['user_data']['user_id'];
?>
<style>
    *{margin:0px auto; padding:0px;}
   .bgImageMain{background-image:url(../public/images/Golden_Country_Road.jpg);width: 100%;height: 100vh;background-position: center center;background-size: cover;position: relative;}
.bgImageMain .society_heading{color: #ffffff;
    font-size: 54px;
    text-align: center;
    text-shadow: 0 1px 1px #333;
    padding: 10px;
    position: relative;
    z-index: 2;
    border-top: 1px dotted #fff;
    border-bottom: 1px dotted #fff;
    display: inline-block;
    margin-left: 50px;
    margin-top: 60px;
	font-family: 'Oswald', sans-serif;
	font-weight: 500;
	}
.society_stamp_box{display: flex;
    justify-content: center;
    position: absolute;
    bottom: 20%;
    left: 30px;
    z-index: 2;}
.society_stamp_box > div{margin:0 20px;}
.society_stamp_box div span{display:block; text-align:center;color:#fff;font-size:22px;font-weight:bold;text-shadow: 0 1px 1px #333;margin-top:10px;}
.society_address{text-align: center;
    height: 230px;
    width: 230px;
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
    position: absolute;
    right: 15px;
    bottom: 15px;
    padding: 20px;z-index: 2;}
.society_address p.addressHead{font-size: 22px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #ffffff;
    text-shadow: 0 1px 1px #333;
    border-bottom: 1px dotted rgba(255,255,255,0.4);
    padding-bottom: 10px;padding-top:25px;}
.society_address p.addressInfo{font-size: 16px;
    font-weight: normal;
    color: #ffffff;
    text-shadow: 0 1px 1px #333;
    line-height: 22px;}
	
	
	
	.dottedoverlay {
	background: url(../public/images/gridtile.png);
    background-repeat: repeat;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
}

.scale-up-center {
	-webkit-animation: scale-up-center 0.9s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
	        animation: scale-up-center 0.9s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
}
@-webkit-keyframes scale-up-center {
  0% {
    -webkit-transform: scale(0.5);
            transform: scale(0.5);
			opacity:0;
  }
  100% {
    -webkit-transform: scale(1);
            transform: scale(1);
			opacity:1;
  }
}
@keyframes scale-up-center {
  0% {
    -webkit-transform: scale(0.5);
            transform: scale(0.5);
			opacity:0;
  }
  100% {
    -webkit-transform: scale(1);
            transform: scale(1);
			opacity:1;
  }
}

#visitSociety h3{font-size:32px;}

    .modal-dialog-centered{
		transform: translateY(-50%) !important;
		top: 50%;	
	}

</style>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<div class="bgImageMain">
<div class="dottedoverlay"></div>
<img src="images/2021-04-15.jpg" style="padding-left: 52px;" /><h1 class="society_heading">Rural Postage Museum</h1>
<div class="society_stamp_box">
<div class="scale-up-center">
<a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id); ?>">
                        <?php if ($is_user_logged_in) { ?>
                            <img style="height:150px;" src="<?php echo IMAGE_PATH . 'usa_circa_1990_comanche.jpg'; ?>" alt="" /> 
							<span>Browse</span>
                        <?php } else { ?>
                            <img style="height:150px;" src="<?php echo IMAGE_PATH . 'usa_circa_1990_comanche.jpg'; ?>" alt="" /> 
							<span>Browse</span>
                        <?php } ?>
</a></div>
<div class="scale-up-center">
<a href="home.html?q=<?php echo encryptQueryString('folder_id=' . $folder_id); ?>">
                        <?php if ($is_user_logged_in) { ?>
                            <img style="height:150px;" src="<?php echo IMAGE_PATH . 'usa_circa_1953_washington_territory.jpg'; ?>" alt="" /> 
							<span>Search</span>
                        <?php } else { ?>
                            <img style="height:150px;" src="<?php echo IMAGE_PATH . 'usa_circa_1953_washington_territory.jpg'; ?>" alt="" /> 
							<span>Search</span>
                        <?php } ?>
</a>
</div>
<!--<div class="scale-up-center"><img style="height:150px;" src="../public/images/usa_circa_1940_pony_express_rider.jpg" /><span>Visit</span></div>-->
<div class="scale-up-center">
<a href="javascript:void(0);" data-toggle="modal" data-target="#visitSociety">
                        <?php if ($is_user_logged_in) { ?>
                            <img style="height:150px;" src="<?php echo IMAGE_PATH . 'usa_circa_1940_pony_express_rider.jpg'; ?>" alt="" /> 
							<span>Visit</span>
                        <?php } else { ?>
                            <img style="height:150px;" src="<?php echo IMAGE_PATH . 'usa_circa_1940_pony_express_rider.jpg'; ?>" alt="" /> 
							<span>Visit</span>
                        <?php } ?>
</a>
</div>
</div>
<div class="society_address">
	<p class="addressHead">Rural Postage Museum</p>
<p class="addressInfo">3456 Route 66<br>
Elma, Kansas 99445<br>
456-999-2344</p>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init();
</script>
<div class="clr"></div>

<!-- Modal -->
<div class="modal fade" id="visitSociety" tabindex="-1" role="dialog" aria-labelledby="visitSocietyLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
	<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="visitSocietyLabel"><strong>Rural Postage Museum</strong></h4>
      </div>
      <div class="modal-body text-center">
      <img src="images/2021-04-15.jpg" width="100px" />
        <h3>We're open Saturdays 8am to 5pm and by appointment.</h3>
		<p class="marginTop20">3456 Route 66, Elma, Kansas 99445, 456-999-2344</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!--</div>
</div>-->

<script type="text/javascript">
    function readMore(elm, t, e) {
        e.preventDefault();
        if($('#'+elm).css('-webkit-line-clamp') == '2'){
            $('#'+elm+'_read_more').addClass('hide');
            $('#'+elm).removeClass('hide');
            $('#'+elm).addClass('read_more_toggle');
        }else{
            $('#'+elm+'_read_more').removeClass('hide');
            $('#'+elm).addClass('hide');
            $('#'+elm).removeClass('read_more_toggle');
        }
    }
</script>