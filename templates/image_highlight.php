 <?php
  $searchString = (isset($searchString) && $searchString != '')?$searchString:'';
 if($window_height == ''){ 
     /************** Fix start for Image highlight box placemnet issue 16-Sep-2022  Bug Id:  0002005*************//////////////
	 $divheight=$_POST['divheight'];
     /************** Fix End for Image highlight box placemnet issue 16-Sep-2022  Bug Id:  0002005*************//////////////
     
     ?>
        <?php if(!empty($searchHighLightData['rect'])){ ?>
        <div class="imgCenterSlider"><!--width: 338px;height: 480px; position:absolute; left:50%; top:50%; transform:translateX(-50%) translateY(-50%);-->
        <?php }else{ ?>  
            <div class="imgCenterSlider"><!--width: 338px;height: 480px; position:absolute; left:50%; top:50%; transform:translateX(-50%) translateY(-50%);-->
        <?php } ?>
        <?php if($item_type == 'url'){ ?>
            <a href="<?php echo $data_url; ?>" target="_blank">
				<?php if(strpos($data_url, 'youtube')) {?>
				<iframe id="youtubeIframe" width="560" height="615" src="<?php echo $data_url; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
				<img id="mainImage" style="display: none;" src="<?php echo IMAGE_PATH ?>external_url.jpg" alt="Slider Image" />
				<?php }else{ ?>
				<iframe id="youtubeIframe" style="display: none;"  width="560" height="615" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
				<img  id="mainImage" src="<?php echo IMAGE_PATH ?>external_url.jpg" alt="Slider Image" />
					<?php }?>
					</a>
        <?php }else{  ?>
			<iframe id="youtubeIframe" style="display: none;"  width="560" height="615" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>	
            <img id="mainImage" class="show_large_image" src="<?php echo THUMB_URL.'?id='.$pr_file_id.'&record_item_id='.$record_item_id.'&show_text=yes&search_text='.$searchString; ?>" alt="Slider Image" onload="largeHighLightImage()"/>
        <?php }  ?>
        <?php 
        if(!empty($searchHighLightData['rect'])){
            $imageUrl = THUMB_URL.'?id='.$pr_file_id.'&record_item_id='.$record_item_id;
            list($width, $height, $type, $attr) = getimagesize($imageUrl);
            $percentCalc = 100;
            $smallPercentCalc = 100;
            /************** Fix start for Image highlight box placemnet issue 16-Sep-2022  Bug Id:  0002005*************//////////////
            if($height > $divheight){
				$percentCalc = ($divheight*100)/$height;
            }/*
			if($height > 480){
                $percentCalc = (480*100)/$height;
            }*/
			/************** Fix End for Image highlight box placemnet issue 16-Sep-2022  Bug Id:  0002005*************//////////////
           
			
            if($height > 250){
                $smallPercentCalc = (250*100)/$height;
            }
            $count = 0;
            echo '<div id="serachHighLightJS" class="main-display" style="display:none;">';
            foreach($searchHighLightData['rect'] as $dataArray){
				/**********************Fix start for Issue ID 0002031  13-Oct-2022*************************************************************/
               /* echo '<div id="hlbox_'.$count.'" style="position:absolute; left:'.(($dataArray['x']*$percentCalc)/100).'px; top:'.(($dataArray['y']*$percentCalc)/100).'px; width:'.(($dataArray['w']*$percentCalc)/100).'px; height:'.(($dataArray['h']*$percentCalc)/100).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;"></div>';*/
				
				/**********************Fix end for Issue ID 0002031  13-Oct-2022*************************************************************/
                $count ++;
            }
            echo '</div>';
            $countSmall = 0;
            echo '<div class="small-display" style="display:none">';
            foreach($searchHighLightData['rect'] as $dataArray){
                echo '<div id="hlboxs_'.$countSmall.'" style="position:absolute; left:'.(($dataArray['x']*$smallPercentCalc)/100).'px; top:'.(($dataArray['y']*$smallPercentCalc)/100).'px; width:'.(($dataArray['w']*$smallPercentCalc)/100).'px; height:'.(($dataArray['h']*$smallPercentCalc)/100).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;"></div>';
                $countSmall ++;
            }
            echo '</div>';
        }
        echo '</div>';
    }else{ 
        $imageUrl = THUMB_URL.'?id='.$pr_file_id.'&record_item_id='.$record_item_id;
        list($width, $height, $type, $attr) = getimagesize($imageUrl);
        if($window_height > $height ){
          $window_height =  $height ;
        }
        $percentRatio = (($window_height*100)/$height);
        $widthRatio  = (($width*$percentRatio)/100);
        /**********************Fix start for Issue ID 0001996  17-Oct-2022*************************************************************/
        echo '<div class="heighlightBox" >';
        if(!empty($searchHighLightData['rect'])){
            
              echo '<div class="zoomDiv dragscroll" full-image-height="" full-image-width="" style="height: '.$window_height.'px;">';/*heighlightBox*/
        }else{

              echo '<div class="zoomDiv dragscroll" full-image-height="" full-image-width="" style="height: '.$window_height.'px;">';/*heighlightBox*/
			
        }
        /**********************Fix end for Issue ID 0001996  17-Oct-2022*************************************************************/
        if($urlImage !=''){
            echo '<img style="" id="full-image-content" src="'. IMAGE_PATH .'external_url.jpg" alt="" onload="imageLoad()">';
        }else{
            if($pr_file_id !=''){
                echo '<img style="" id="full-image-content" src="'.$imageUrl.'&show_text=yes" alt="" onload="imageLoad()">';
            }else{
                 $imageUrl = IMAGE_PATH.'no-image.png';
                 echo '<img style="" id="full-image-content" src="'.$imageUrl.'" alt="" onload="imageLoad()">';
            }
        }
        echo '<label style="display:none" id="original-image-content" data-src="'.$imageUrl.'"></label>';
        if(!empty($searchHighLightData['rect'])){
            list($width, $height, $type, $attr) = getimagesize($imageUrl);
            $largePercent = 100;
            if($height > $window_height){
                $largePercent = ($window_height*100)/$height;
            }
            $largeCount = 0;
			/**********************Fix start for Issue ID 0002031  13-Oct-2022*************************************************************/
			echo '<div id="fullImgaeSearchHeighLight">';
			
            /*foreach($searchHighLightData['rect'] as $dataArray){
                echo '<div class="full-high-light-image correct_postion full-image-highlight-'.$largeCount.'" id="hlbox_'.$largeCount.'" style="position:absolute; left:'.(($dataArray['x']*$largePercent)/100).'px; top:'.(($dataArray['y']*$largePercent)/100).'px; width:'.(($dataArray['w']*$largePercent)/100).'px; height:'.(($dataArray['h']*$largePercent)/100).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;display:none"></div>';
                echo'<input class="high-light-prop" type="hidden" search_text="'.$searchString.'" left="'.(($dataArray['x']*$largePercent)/100).'"  top="'.(($dataArray['y']*$largePercent)/100).'" width="'.(($dataArray['w']*$largePercent)/100).'" height="'.(($dataArray['h']*$largePercent)/100).'">';
                echo'<input class="high-light-prop-fixed-'.$largeCount.'" type="hidden" search_text="'.$searchString.'" left="'.(($dataArray['x']*$largePercent)/100).'"  top="'.(($dataArray['y']*$largePercent)/100).'" width="'.(($dataArray['w']*$largePercent)/100).'" height="'.(($dataArray['h']*$largePercent)/100).'">';
                $largeCount ++;
            }*/
			/**********************Fix end for Issue ID 0002031  13-Oct-2022*************************************************************/
			echo '</div>';
        }
        echo '</div>';
        /**********************Fix start for Issue ID 0001996  17-Oct-2022*************************************************************/
        if (count($apiResponse['info']['records']) > 1)
		{
            echo "<div class='leftNavDetailON' id='left_detail' name='left_detail'></div>";
			echo "<div class='rightNavDetailON' id='right_detail' name='right_detail' ></div>";
		}
         /**********************Fix start for Issue ID 0002432 25-Feb-2025*************************************************************/
         echo '<div id="" style="z-index: 3; top: 18px; right:-52px; position: absolute;" class="navCanel">
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         </div>';
        /**********************Fix end for Issue ID 0002432 25-Feb-2025*************************************************************/  
        echo '</div>';
        /**********************Fix end for Issue ID 0001996  17-Oct-2022*************************************************************/
    }
    ?>
<script>
// Zoom In Zoom Out start (Bateshwar)
var serach_text = "<?php echo $searchString; ?>";
var url_image = "<?php echo $urlImage; ?>";
$(document).ready(function(){
    var width_ratio = parseInt('<?php echo $widthRatio; ?>');
    $('.zoomBtn').css('display','none');
    $('.popup-original-image').show();
    $('.dragscroll').dragscrollable({});
    $("#full-image-content" ).load(function() {
        if($('#full-image-content').width() > 0 && $('#full-image-content').height() > 0){
                $('.zoomDiv').width($('#full-image-content').width()).height($('#full-image-content').height());
                
        }else{
             $('.zoomDiv').width(width_ratio).height('<?php echo $window_height; ?>');
        }
    }); 
var img = $("#full-image-content");
$("<img>").attr("src", $(img).attr("src")).load(function(){
	 var realWidth = this.width;
    var realHeight = this.height;
    $('.zoomDiv').attr('full-image-height',realHeight);
    $('.zoomDiv').attr('full-image-width',realWidth);
	/**********************Fix start for Issue ID 0002031  13-Oct-2022*************************************************************/
	reMarkHeighlighterFullImage();
	/**********************Fix end for Issue ID 0002031  13-Oct-2022*************************************************************/
	
});

if( url_image !=''){
    $('.loading-div-fullPage').hide();
    $('.popup-original-image').hide();
    $('.popup-original-url-image').children("a").attr("href",url_image);
    $('.popup-original-url-image').css('display','block');
}
$(function() {
    $('#full-image-content').on('load',function() {
        if(url_image == ''){
          $('.zoomBtn').css('display','block');
        }
    });
});
	$(function() {
    $('.show_large_image').on('load',function() {
		/**********************Fix start for Issue ID 0002031  13-Oct-2022*************************************************************/
		reMarkHeighlighter();
		/**********************Fix end for Issue ID 0002031  13-Oct-2022*************************************************************/
        
    });
		
	
});

});
/**********************Fix start for Issue ID 0002031  13-Oct-2022*************************************************************/	
	
	//window.addEventListener("resize", reMarkHeighlighter, false);
//	window.addEventListener("resize", reMarkHeighlighterFullImage, false);
  var id;
$(window).resize(function() {
  
    clearTimeout(id);
    id = setTimeout(reMarkHeighlighterFullImage, 500);
    
});
	  var idf;
$(window).resize(function() {
  
    clearTimeout(idf);
    idf = setTimeout(reMarkHeighlighter, 500);
    
});
    
	
	
	
	let out = document.querySelector(".output");
  
        function reMarkHeighlighter() {
			$('#serachHighLightJS').html('');
          var item_id='<?php echo $item_id;//$record_item_id;?>';
		var imageHeight=$('.show_large_image').height();
		var imageWidth=$('.show_large_image').width();
		var searchString='<?php echo $searchString;?>';
		 $.ajax({
				url: "services.php",
				type: "post",
				data: {mode: 'image_highlight_with_dimenstion', item_id: item_id,imageHeight:imageHeight, searchString:searchString, imageWidth: imageWidth  },
				success: function (data) {
				  	$('#serachHighLightJS').html('');
                    if(data.length){
						obj = JSON.parse(data);
						countDiv=1;
						for (var key of Object.keys(obj)) {
						heightImg=$('.show_large_image').height();

						html= '<div id="hlbox_'+countDiv+'" style="position:absolute; left:'+obj[key]['x']+'px; top:'+obj[key]['y']+'px; width:'+obj[key]['w']+'px; height:'+obj[key]['h']+'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;"></div>';
						countDiv= countDiv+1;	
					   $('#serachHighLightJS').append(html);
						}
                    }
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1055)');
				}});
        }
	
	    function reMarkHeighlighterFullImage(zoom=0){
   
	var item_id='<?php echo $item_id;//$record_item_id;?>';
		
		var searchString='<?php echo $searchString;?>';
		if(zoom==1)
			{
				var imageHeight=$('#full-image-content').css('height');
				imageHeight = imageHeight.replace("px", "");
		var imageWidth=$('#full-image-content').css('width');
				imageWidth = imageWidth.replace("px", "");
			}
		else{		
	var imageHeight=$('#full-image-content').height();
		var imageWidth=$('#full-image-content').width();}
		 $.ajax({
				url: "services.php",
				type: "post",
				data: {mode: 'image_highlight_with_dimenstion', item_id: item_id,imageHeight:imageHeight, searchString:searchString, imageWidth: imageWidth  },
				success: function (data) {
				  	$('#fullImgaeSearchHeighLight').html('');
                      if(data.length){
						obj = JSON.parse(data);
						countDiv=1;
						for (var key of Object.keys(obj)) {

						html= '<div id="hlbox_'+countDiv+'" style="position:absolute; left:'+obj[key]['x']+'px; top:'+obj[key]['y']+'px; width:'+obj[key]['w']+'px; height:'+obj[key]['h']+'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;"></div>';
						countDiv= countDiv+1;	
					   $('#fullImgaeSearchHeighLight').append(html);
						}
                    }
					
				},
				error: function () {
					showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1056)');
				}
			});
}

	/**********************Fix end for Issue ID 0002031  13-Oct-2022*************************************************************/
</script>
