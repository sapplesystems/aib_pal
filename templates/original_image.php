	<!--	/************** Fix start for Image highlight box placemnet issue 20-Sep-2022 Bug Id:  0002005*************////////////// -->
<img style="float:left;"  src="<?php echo THUMB_URL.'?id='.$pr_file_id.'&show_text=yes&search_text='.$searchString; ?>" alt="Slider Image"  onload="highLighOriginalImage()" />
	<!--	/************** Fix end for Image highlight box placemnet issue 20-Sep-2022 Bug Id:  0002005 *************////////////// -->

<?php 
    foreach($searchHighLightData['rect'] as $dataArray){
        echo '<div class="high_light_div" id="hlbox_'.$count.'" style="position:absolute; left:'.($dataArray['x']).'px; top:'.($dataArray['y']).'px; width:'.($dataArray['w']).'px; height:'.($dataArray['h']).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;display:none"></div>';
        $count ++;
    }

?>