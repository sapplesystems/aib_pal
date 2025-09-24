
<img  src="<?php echo THUMB_URL.'?id='.$pr_file_id.'&show_text=yes&search_text='.$searchString; ?>" alt="Slider Image"  onload="highLighOriginalImage()" />

<?php 
    foreach($searchHighLightData['rect'] as $dataArray){
        echo '<div class="high_light_div" id="hlbox_'.$count.'" style="position:absolute; left:'.($dataArray['x']).'px; top:'.($dataArray['y']).'px; width:'.($dataArray['w']).'px; height:'.($dataArray['h']).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;display:none"></div>';
        $count ++;
    }

?>