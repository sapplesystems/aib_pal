<?php if ($apiResponseResultSearch['status'] == 'OK') { ?>
<div class="you-were-here">
    <div class="item-location you-were-here-right">
        <?php if(!empty($previousYouWere)){ 
            foreach($previousYouWere as $previousDataArray){
                $prev_file_name="home.html?q=".encryptQueryString('folder_id='.$previousDataArray['item_id']);
                if($previousDataArray['item_type']=='RE'){
                    $prev_file_name="item-details.html?q=".encryptQueryString('folder_id='.$previousDataArray['item_id']);
                }
            ?> 
                <a href="<?php echo $prev_file_name; ?>"><?php echo $previousDataArray['item_title']; ?></a>
            <?php } 
        } ?>(You were here.)
    </div>
</div>
    <ul class="search-result">
    <?php
	$TargetModifier = "";
	if (isset($_POST["search_text"]) == true)
	{
		$SearchText = $_POST["search_text"];
		$SearchText = preg_replace("/[\"]/"," ",$SearchText);
		$SearchText = strtolower($SearchText);
		$SearchWords = preg_split("/[ \t,\/\;]+/",ltrim(rtrim($SearchText)));
		$SearchTags = join(",",$SearchWords);
		$HighlightSpec = "&tags=".$SearchTags;
	}
	else
	{
		$HighlightSpec = "";
	}

    foreach ($apiResponseResultSearch['info']['records'] as $key => $searchDataArray) {
        if($searchDataArray['item_type'] == 'CO' || $searchDataArray['item_type'] == 'SG'){
            $linkFileName = "home.html?q=".encryptQueryString('folder_id=' . $searchDataArray['item_id']);
            $thumbURL = IMAGE_PATH. 'folder.png';
            if($source == 'people'){
                $linkFileName = "people.html?q=".encryptQueryString('folder_id='.$searchDataArray['item_id']);
            }
        }elseif ($searchDataArray['item_type'] == 'RE') {
		if (isset($searchDataArray["is_link"]) == true && $searchDataArray["is_link"] == "Y")
		{
			if (isset($searchDataArray["stp_link_type"]) == true)
			{
				$linkFileName = "http://".$searchDataArray["stp_url"].$HighlightSpec;
				$thumbURL = "http://".$searchDataArray["stp_thumb"];
				$TargetModifier = " target='_blank'";
			}
			else
			{
				$linkFileName = "#";
				$thumbURL = "#";
			}
		}
		else
		{
	            $linkFileName = "item-details.html?q=".encryptQueryString('folder_id='.$searchDataArray['item_id'].'&search_text='.$search_text);
        	    $thumbURL = RECORD_THUMB_URL . '?id=' . $searchDataArray['item_id'];
		}
        }else {
            $linkFileName = "item-details.html?q=".encryptQueryString('folder_id='.$searchDataArray['item_parent'].'&itemId='.$searchDataArray['item_id'].'&search_text='.$search_text);
            $thumbURL = RECORD_THUMB_URL . '?id=' . $searchDataArray['item_id'];
            foreach ($searchDataArray['files'] as $file) {
                if ($file['file_type'] == 'tn'){
                    $thumbURL = THUMB_URL . '?id=' . $file['file_id'];
                }   
            }
        } 
        $style="";
        if(isset($searchDataArray['link_visited']) && $searchDataArray['link_visited'] == 'yes'){
            $style = "style='color: #609;'";
        }
        ?>
            <li>
                <div class="col-md-2 text-center">
                    <a class="organizations search_result_clicked" search_item_id="<?php echo $searchDataArray['item_id']; ?>" href="<?php echo $linkFileName; ?>"<?php echo $TargetModifier; ?> >
                        <img class="searchResultImg"  src="<?php echo $thumbURL; ?>" alt="" />
                    </a>
                </div>
                <div class="col-md-10">
                    <div class="marginTopBottom10">
                        <a class="organizations search_result_clicked" search_item_id="<?php echo $searchDataArray['item_id']; ?>" href="<?php echo $linkFileName; ?>"<?php echo $TargetModifier; ?> ><span  <?php echo $style; ?>><?php echo $searchDataArray['item_title'][0]; ?><?php echo substr($searchDataArray['item_title'], 1); ?></span></a>
                    </div>
                    <div class="marginTopBottom10 item-location">
                        <?php if(!empty($searchDataArray['item_location'])){ ?>
						<ul class="listing search_data_listing">
						<?php
                            foreach($searchDataArray['item_location'] as $dataArray){
                                $file_name="home.html?q=".encryptQueryString('folder_id='.$dataArray['item_id']);
                                if($dataArray['item_type']=='RE'){
                                	$file_name="item-details.html?q=".encryptQueryString('folder_id='.$dataArray['item_id']);
                                }
                            ?> 
                                <li><a href="<?php echo $file_name; ?>"><?php echo $dataArray['item_title']; ?></a></li>
                            <?php } ?>
							</ul>
                        <?php } ?>
                    </div>
                    <?php if(!empty($snipTextDataArray)){ ?>
                    <div class="marginTopBottom10">
						<a class="organizations search_result_clicked" search_item_id="<?php echo $searchDataArray['item_id']; ?>" href="<?php echo $linkFileName; ?>"<?php echo $TargetModifier; ?> >
							<span style="font-weight: normal;color:#000000;"><?php echo $snipTextDataArray[$searchDataArray['item_id']];  ?></span>
						</a>
                    </div>
                    <?php } ?>
                </div>
            </li>
    <?php } ?>             
    </ul> 
<?php
} else {
    echo '<h3 class="text-center">No data found</h3>';
}?>
