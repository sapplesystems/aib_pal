 <?php
  $searchString = (isset($searchString) && $searchString != '')?$searchString:'';
 if($window_height == ''){ 
     
     ?>
        <?php if(!empty($searchHighLightData['rect'])){ ?>
        <div class="imgCenterSlider"><!--width: 338px;height: 480px; position:absolute; left:50%; top:50%; transform:translateX(-50%) translateY(-50%);-->
        <?php }else{ ?>  
            <div class="imgCenterSlider"><!--width: 338px;height: 480px; position:absolute; left:50%; top:50%; transform:translateX(-50%) translateY(-50%);-->
        <?php } ?>
        <?php if($item_type == 'url'){ ?>
            <a href="<?php echo $data_url; ?>" target="_blank"><img src="<?php echo IMAGE_PATH ?>external_url.jpg" alt="Slider Image" /></a>
        <?php }else{  ?>
            <img class="show_large_image" src="<?php echo THUMB_URL.'?id='.$pr_file_id.'&record_item_id='.$record_item_id.'&show_text=yes&search_text='.$searchString; ?>" alt="Slider Image" onload="largeHighLightImage()"/>
        <?php }  ?>
        <?php 
        if(!empty($searchHighLightData['rect'])){
            $imageUrl = THUMB_URL.'?id='.$pr_file_id.'&record_item_id='.$record_item_id;
            list($width, $height, $type, $attr) = getimagesize($imageUrl);
            $percentCalc = 100;
            $smallPercentCalc = 100;
            if($height > 480){
                $percentCalc = (480*100)/$height;
            }
            if($height > 250){
                $smallPercentCalc = (250*100)/$height;
            }
            $count = 0;
            echo '<div class="main-display" style="display:none;">';
            foreach($searchHighLightData['rect'] as $dataArray){
                echo '<div id="hlbox_'.$count.'" style="position:absolute; left:'.(($dataArray['x']*$percentCalc)/100).'px; top:'.(($dataArray['y']*$percentCalc)/100).'px; width:'.(($dataArray['w']*$percentCalc)/100).'px; height:'.(($dataArray['h']*$percentCalc)/100).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;"></div>';
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
        if(!empty($searchHighLightData['rect'])){
              echo '<div class="heighlightBox zoomDiv dragscroll" full-image-height="" full-image-width="" style="height: '.$window_height.'px;">';
        }else{
             echo '<div class="ImageBox zoomDiv dragscroll" full-image-height="" full-image-width="" style="height: '.$window_height.'px; " >';
        }
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
            foreach($searchHighLightData['rect'] as $dataArray){
                echo '<div class="full-high-light-image correct_postion full-image-highlight-'.$largeCount.'" id="hlbox_'.$largeCount.'" style="position:absolute; left:'.(($dataArray['x']*$largePercent)/100).'px; top:'.(($dataArray['y']*$largePercent)/100).'px; width:'.(($dataArray['w']*$largePercent)/100).'px; height:'.(($dataArray['h']*$largePercent)/100).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;display:none"></div>';
                echo'<input class="high-light-prop" type="hidden" search_text="'.$searchString.'" left="'.(($dataArray['x']*$largePercent)/100).'"  top="'.(($dataArray['y']*$largePercent)/100).'" width="'.(($dataArray['w']*$largePercent)/100).'" height="'.(($dataArray['h']*$largePercent)/100).'">';
                echo'<input class="high-light-prop-fixed-'.$largeCount.'" type="hidden" search_text="'.$searchString.'" left="'.(($dataArray['x']*$largePercent)/100).'"  top="'.(($dataArray['y']*$largePercent)/100).'" width="'.(($dataArray['w']*$largePercent)/100).'" height="'.(($dataArray['h']*$largePercent)/100).'">';
                $largeCount ++;
            }
        }
        echo '</div>';
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

});
</script>