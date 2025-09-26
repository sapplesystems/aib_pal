<?php 
    $society_template = $previousYouWere[1]['properties']['custom_template']; 
    $knowledge_base_name = '';
    if(!empty($_SESSION['data_detail_page']['info']['records'][0]['root_info']['archive_group']['item_title']))
    {
        $knowledge_base_name = urldecode($_SESSION['data_detail_page']['info']['records'][0]['root_info']['archive_group']['item_title']);
    }
?>
<?php if ($apiResponseResultSearch['status'] == 'OK' or count($llmChat) or !empty($llmRequestId) or !empty($cachedChatHtml)) { ?>
    <input type="hidden" id="totalResultCount" name="totalResultCount" value="<?php echo $totalResultCount;?>">
    <div class="you-were-here">
        <div class="item-location you-were-here-right">
            <?php if (!empty($previousYouWere)) {
                foreach ($previousYouWere as $previousDataArray) {
                    $prev_file_name = "home.html?q=" . encryptQueryString('folder_id=' . $previousDataArray['item_id']);
                    if ($previousDataArray['item_type'] == 'RE') {
                        $prev_file_name = "item-details.html?q=" . encryptQueryString('folder_id=' . $previousDataArray['item_id']);
                    }
            ?>
                    <a href="<?php echo $prev_file_name; ?>"><?php echo $previousDataArray['item_title']; ?></a>
                    <?php }
            } ?>(You were here.)
        </div>
    </div>
    

    
    
    
    <!-- <div class="ShowHideFilter"><label><input checked type="checkbox" class="show_hide_filter" value="-1">Show/Hide Filter</label></div> -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <!--//Fix start for Issue ID 0002431 on 21-Feb-2025-->
            <!-- <p style="font-size: 16px;font-weight:bold;padding-top:10px;">
                The filter below is only applied to the 100 records that are displayed on this page.<br />
                    To see and filter additional results, use the "Next 100 Records" and "Previous 100 Records" buttons.
            </p> -->
            <!--//Fix end for Issue ID 0002431 on 21-Feb-2025-->
            <!--Fix start for Issue ID 2149 on 14-Feb-2023-->

            <div class="content_archive_search leftModule">
                <h4 class="marginBottom10" style="margin-top:0;">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> 
                    <strong>Perform a New Search</strong>
                </h4>

                <div class="form-group">
                    <select class="form-control archive_listing_select" name="archive_listing_select" id="archive_listing_select" style="display:none;"></select>
                </div>

                <div class="form-group marginBottom10">
                    <input type="text" class="form-control" name="search_text_srp" id="search_text_srp" value="<?php if (isset($search_text) && !empty($search_text)) { echo htmlspecialchars($search_text, ENT_QUOTES); } ?>" placeholder="Enter Keyword Search Here">
                    <span style="color: red;font-size: 12px;">Note: Use quote marks for phrase.</span>
                    <textarea class="form-control marginTop10" name="chat_request_srp" id="chat_request_srp" placeholder="Enter Chat Question Here"><?php if (isset($chat_request) && !empty($chat_request)) { echo htmlspecialchars($chat_request, ENT_QUOTES); } ?></textarea>
                </div>

                <span style="font-size: 14px;position: relative;top: 4px;">Keyword Search and/or Chatbot Search</span>
                <div style="border: 1px solid #15345a;padding: 5px;margin-top: 5px;border-radius: 4px;">
                    <button class="btn search-button search_in_archive" id="search_in_archive_home" data-type="archive">Search All Folders</button>
                    <span style="font-size: 10px; font-weight: 400;display:block;">Search all folders from <span id="current_society_name"><?=$knowledge_base_name;?></span></span>
                </div> 

                <span style="font-size: 14px;position: relative;top: 9px;">Keyword Search Only</span>
                <div style="border: 1px solid #15345a;padding: 5px;margin-top: 10px;border-radius: 4px;">
                    <button class="btn search-button define_search" type="button" id="define_search">Define Search Folders</button>
                    <span style="font-size: 10px; font-weight: 400;display:block;">Choose which folder(s) you want to search</span>
                </div>
            </div>
            <?php if (isset($search_text) && !empty($search_text)) { ?>     
            <div class="AllCheckBox"><label style="padding-bottom: 0; margin-bottom: 0; padding-left: 0; margin-left: 0;"><input checked type="checkbox" class="toggle_all_chk" value="-1">All/None</label><p style="font-size: 14px;font-weight:400; padding-left: 23px; line-height: normal;">Filter up to 100 records displayed on this page only.</p></div>
            <!--Fix start for Issue ID 2149 on 20-Feb-2023-->
                <div id="search_result_chk"></div>
            <?php } ?>
            </div>
            <div class="col-md-9 marginTop13">
                <?php if (!empty($llmRequestId) || !empty($cachedChatHtml)) { ?>
                    <input type="hidden" id="llm_request_id" value="<?php echo htmlspecialchars($llmRequestId); ?>">
                    <div class="searchBGNone">
                        <h3><strong>Chatbot Query: <?=!empty($chat_request)?$chat_request:'';?></strong></h3>
                        <h3 style="font-size: 18px; margin: 18px 0 0 0;"><strong>Chatbot Response:</strong></h3>
                    </div>
                    <div id="llm-stream-container" style="user-select:text;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;">
                        <?php if (!empty($cachedChatHtml)): ?>
                            <!-- Cached chat result -->
                            <div id="llm-stream-content" style="user-select:text;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;">
                                <?php echo $cachedChatHtml; ?>
                            </div>
                        <?php else: ?>
                            <!-- Empty initially, will be filled by streaming -->
                            <div id="llm-stream-content" style="user-select:text;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;"></div>
                        <?php endif; ?>
                        <button id="cancel-llm-query" type="button" class="btn btn-primary btn-sm pull-right" style="line-height: normal;padding: inherit;margin-top:5px;">Cancel Query</button>
                    </div>
                    <!-- Debug area (hidden in production) -->
                    <!-- <pre id="llm-debug-response" style="max-width: 800px; margin: 10px auto; padding: 10px; background: #eee; font-size: 12px; overflow-x: auto; border: 1px dashed #ccc;"></pre> -->
                <?php } ?>

                <!-- Fix start on 20-June-2025 -->
				<?php 
                if($totalResultCount && $totalStatndardResultCount > 0){
				?>
                <div class="searchBGNone"><h3><strong><?php echo $search_text;?></strong></h3></div>
                
                 <div>
                    <!--//Fix start for Issue ID 0002431 on 21-Feb-2025-->
					<div class="col-md-3"  style="text-align: left;   padding-bottom: 10px;"><span style="display: none;cursor: pointer;" class="previous-page backtoLink">Previous 100 Records</span> </div>
					<div class="col-md-6" style="text-align: center;"><span>Total Result Count: <?php echo $totalResultCount;?></span></div>
					<div class="col-md-3" style="text-align: right;   padding-bottom: 10px;"><span style="cursor: pointer;" class="next-page backtoLink">Next 100 Records</span></div>
                    <!--//Fix end for Issue ID 0002431 on 21-Feb-2025-->
                </div>
                <!-- Fix end on 20-June-2025 -->
                <ul class="search-result searchResultScroll">
                    <?php
                    $TargetModifier = "";
                    if (isset($search_text) == true) {
                        $SearchText = $search_text;
                        $SearchText = preg_replace("/[\"]/", " ", $SearchText);
                        $SearchText = strtolower($SearchText);
                        $SearchWords = preg_split("/[ \t,\/\;]+/", ltrim(rtrim($SearchText)));
                        $SearchTags = join(",", $SearchWords);
                        $HighlightSpec = "&tags=" . $SearchTags;
                    } else {
                        $HighlightSpec = "";
                    }

                    //Fix start for Issue ID 2149 on 14-Feb-2023
                //    $FilterData = array();
                   // $LiClasses = array();
                //    $Parent = 0;
                  //  $pathTitle = '';
                    //Fix end for Issue ID 2149 on 14-Feb-2023

                    foreach ($apiResponseResultSearch['info']['records'] as $key => $searchDataArray) {
                        /************ Fix start for 2148 on 9feb2023 *******************************/
                        $linkPrefoxSTP = '';
                        /************ Fix End for 2148 on 9feb2023 *******************************/
                        if ($searchDataArray['item_type'] == 'CO' || $searchDataArray['item_type'] == 'SG') {
                            $linkFileName = "home.html?q=" . encryptQueryString('folder_id=' . $searchDataArray['item_id']);
                            $thumbURL = IMAGE_PATH . 'folder.png';
                            if ($source == 'people') {
                                $linkFileName = "people.html?q=" . encryptQueryString('folder_id=' . $searchDataArray['item_id']);
                            }
                        } elseif ($searchDataArray['item_type'] == 'RE') {
                            if (isset($searchDataArray["is_link"]) == true && $searchDataArray["is_link"] == "Y") {
                                if (isset($searchDataArray["stp_link_type"]) == true) {
									
									/* fix start for 2274 10-july */
                                    $linkFileName = "https://" . $searchDataArray["stp_url"] . $HighlightSpec;
                                    //$thumbURL = "http://" . $searchDataArray["stp_thumb"];
									$thumbURL =  $searchDataArray["stp_thumb"];
									/* fix end for 2274 10-july */
                                    $TargetModifier = " target='_blank'";
                                    /************ Fix start for 2148 on 9feb2023 *******************************/
                                    $linkPrefoxSTP = 'Page ';
                                    /************ Fix End for 2148 on 9feb2023 *******************************/
                                } else {
                                    $linkFileName = "#";
                                    $thumbURL = "#";
                                }
                            } else {
                                $linkFileName = "item-details.html?q=" . encryptQueryString('folder_id=' . $searchDataArray['item_id'] . '&search_text=' . $search_text);
                                $thumbURL = RECORD_THUMB_URL . '?id=' . $searchDataArray['item_id'];
                            }
                        } else {
                            $linkFileName = "item-details.html?q=" . encryptQueryString('folder_id=' . $searchDataArray['item_parent'] . '&itemId=' . $searchDataArray['item_id'] . '&search_text=' . $search_text);
                            $thumbURL = RECORD_THUMB_URL . '?id=' . $searchDataArray['item_id'];
                            foreach ($searchDataArray['files'] as $file) {
                                if ($file['file_type'] == 'tn') {
                                    $thumbURL = THUMB_URL . '?id=' . $file['file_id'];
                                }
                            }
                        }
                        $style = "";
                        if (isset($searchDataArray['link_visited']) && $searchDataArray['link_visited'] == 'yes') {
                            $style = "style='color: #609;'";
                        }
                    ?>
                        <li class="search_li_all search_li_<?php echo $searchDataArray['item_id']; ?>">
                            <div class="col-md-12">
                                <div class="marginTopBottom10 item-location" style="margin-top:0px;">
                                    <?php

                                    foreach ($searchDataArray['item_location'] as $dataArray) {
                                        $LiClasses['search_li_' . $searchDataArray['item_id']][] = 'search_li_' . $dataArray['item_id'];
                                    }
                                    /************ Fix start for 2568 on 26 Sep 2025 *******************************/
                                    // if (!empty($searchDataArray['item_location']) && $searchDataArray['pathTitle'] != $pathTitle) {
                                    if (!empty($searchDataArray['item_location'])) {
                                    /************ Fix end for 2568 on 26 Sep 2025 *******************************/
                                        $breadcrumb_li_class = '';
                                        foreach ($searchDataArray['item_location'] as $dataArray) {
                                            $breadcrumb_li_class .= $dataArray['item_id'];
                                        }
                                    ?>
                                        <ul style="margin-top:0px;" class="listing search_data_listing <?php echo $breadcrumb_li_class; ?>" data-cls="<?php echo $breadcrumb_li_class; ?>">
                                            <?php
                                            foreach ($searchDataArray['item_location'] as $dataArray) {
                                                $file_name = "home.html?q=" . encryptQueryString('folder_id=' . $dataArray['item_id']);
                                                if ($dataArray['item_type'] == 'RE') {
                                                    $file_name = "item-details.html?q=" . encryptQueryString('folder_id=' . $dataArray['item_id']);
                                                }

                                                //$LiClasses['search_li_' . $searchDataArray['item_id']][] = 'search_li_' . $dataArray['item_id'];
                                                if ($dataArray['item_type'] != 'RE' && $dataArray['item_type'] != 'IT') {
                                                    //Fix start for Issue ID 2149 on 14-Feb-2023
                                                    if (!array_search($dataArray['item_id'], array_column($FilterData, 'id'))) {
                                                        $dataF['id'] = $dataArray['item_id'];
                                                        $dataF['parent'] = $Parent;
                                                        $dataF['name'] = addslashes($dataArray['item_title']);
                                                        $dataF['item_type'] = $dataArray['item_type'];
                                                        $FilterData[] = $dataF;
                                                    }
                                                    $Parent = $dataArray['item_id'];
                                                    //Fix end for Issue ID 2149 on 14-Feb-2023
                                            ?>
                                                    <li><a href="<?php echo $file_name; ?>"><?php echo $dataArray['item_title']; ?></a>
                                                    </li>
                                            <?php
                                                }
                                            }
                                            $Parent = 0;
                                            ?>
                                        </ul>
                                    <?php }
                                    $pathTitle = $searchDataArray['pathTitle'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                            <div class="col-md-2">
                                <a class="organizations search_result_clicked" search_item_id="<?php echo $searchDataArray['item_id']; ?>" href="<?php echo $linkFileName; ?>" <?php echo $TargetModifier; ?> target="_blank">
                                    <img class="searchResultImg" src="<?php echo $thumbURL; ?>" alt="" />
                                </a>
                            </div>
                            <div class="col-md-9">
                                <div class="marginTopBottom10">
                                    <!--/************ Fix start for 2148 on 9feb2023 *******************************/	-->
                                    <a class="organizations search_result_clicked" search_item_id="<?php echo $searchDataArray['item_id']; ?>" href="<?php echo $linkFileName; ?>" <?php echo $TargetModifier; ?> target="_blank"><span <?php echo $style; ?>><?php echo $linkPrefoxSTP . $searchDataArray['item_title'][0]; ?><?php echo substr($searchDataArray['item_title'], 1); ?></span></a>
                                    <!--/************ Fix End for 2148 on 9feb2023 *******************************/	-->
                                </div>
                                <?php if (!empty($snipTextDataArray)) { ?>
                                    <div class="marginTopBottom10">
                                        <a class="organizations search_result_clicked" search_item_id="<?php echo $searchDataArray['item_id']; ?>" href="<?php echo $linkFileName; ?>" <?php echo $TargetModifier; ?> target="_blank">
                                            <span style="font-weight: normal;color:#000000;"><?php echo $snipTextDataArray[$searchDataArray['item_id']];  ?></span>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
				<div>
                <!--//Fix start for Issue ID 0002431 on 21-Feb-2025-->
					<div class="col-md-3"  style="text-align: left;   padding-bottom: 10px;"><span style="display: none;cursor: pointer;" class="previous-page backtoLink">Previous 100 Records</span> </div>
					<div class="col-md-6" style="text-align: center;"><span>Total Result Count: <?php echo $totalResultCount;?></span></div>
					<div class="col-md-3" style="text-align: right;   padding-bottom: 10px;"><span style="cursor: pointer;" class="next-page backtoLink">Next 100 Records</span></div>
				<!--//Fix end for Issue ID 0002431 on 21-Feb-2025-->
                </div>
				<?php }?>
            </div>
        </div>
    </div>



    <!--Fix end for Issue ID 2149 on 14-Feb-2023-->
    <!--Fix end for Issue ID 2149 on 20-Feb-2023-->
<?php  //Fix start for Issue ID 2149 on 14-Feb-2023
$ParentKeys = array_search(0, array_column($FilterData, 'parent'));
$ParentKeysCount = count($ParentKeys);
$search_data_list = '<ul class="aib-nav-tree-ul">';
for ($p = 0; $p < $ParentKeysCount; $p++) {
    $item_id = $FilterData[$p]['id'];
    $item_name = $FilterData[$p]['name'];
    $search_data_list .= '<li class="parent_li_data" id="parent_li_' . $item_id . '" data-li_id="' . $item_id . '">';
    $search_data_list .= '<span class="marker_span" id="parent_span_' . $item_id . '" data-span_id="' . $item_id . '"></span>';
    $search_data_list .= '<input checked type="checkbox" class="toggle_chk" id="toggle_chk_' . $item_id . '" value="' . $item_id . '">';
    $search_data_list .= '<span class="marker_text_span" id="parent_span_' . $item_id . '" data-span_id="' . $item_id . '"><strong>' . $item_name . '</strong></span>';
    $search_data_list .= '</li>';
}
$search_data_list .= '</ul>';
//Fix end for Issue ID 2149 on 14-Feb-2023
?>
<!--Fix start for Issue ID 2149 on 14-Feb-2023-->
<script type="text/javascript">
    $(document).ready(function() {

		current_page=$('#current_page').val();
		totalResultCount=parseInt('<?php echo $totalResultCount;?>');
		SEARCH_RESULT_COUNT=parseInt('<?php echo SEARCH_RESULT_COUNT;?>');
		
		$('.next-page').show();
		if(totalResultCount <=parseInt(current_page*SEARCH_RESULT_COUNT))
			{
			$('.next-page').hide();
		}
		if(current_page>1){
			$('.previous-page').show();
		}
        if (!localStorage.getItem('ArchiveClicked')) {
            setTimeout(function() {
                document.getElementsByClassName('marker_span')[0].click();
                localStorage.setItem('ArchiveClicked', 1);
            }, 1000);
        }

        let FilterData = <?php echo json_encode($FilterData); ?>;
        //FilterData = JSON.parse(FilterData);

        let LiClasses = '<?php echo json_encode($LiClasses) ?>';
        LiClasses = JSON.parse(LiClasses);

        if (LiClasses) {
            $.each(LiClasses, function(key, val) {
                let classes = val.join(' ');
                $('.' + key).addClass(classes);
            });
        }

        $('#search_result_chk').html('<?php echo addslashes($search_data_list); ?>');

        $(document).on('click', '.show_hide_filter', function(e) {
            e.stopPropagation();
            if ($(this).is(':checked')) {
                $('.AllCheckBox').css('display', '');
                $('#search_result_chk').css('display', '');
            } else {
                $('.AllCheckBox').css('display', 'none');
                $('#search_result_chk').css('display', 'none');
            }
        });

        $(document).on('click', '.toggle_all_chk', function(e) {
            e.stopPropagation();
            if ($(this).is(':checked')) {
                $('.toggle_chk').prop('checked', true);
                $('.search_li_all').css('display', '');
            } else {
                $('.toggle_chk').prop('checked', false);
                $('.search_li_all').css('display', 'none');
            }
        });

        $(document).on('click', '.toggle_chk', function(e) {
            e.stopPropagation();
            let cval = $(this).val();
			  
            if ($(this).is(':checked')) {
                $('.search_li_' + cval).css('display', '');
            } else {
                $('.search_li_' + cval).css('display', 'none');
            }
			
			if($('#toggle_chk_'+cval).prop('checked')==false){
				$('#ul_of_'+cval).find(':checkbox').each(function(){

					$(this).prop('checked', '');

				});  
			}else{
				$('#ul_of_'+cval).find(':checkbox').each(function(){

					$(this).prop('checked', 'checked');

				});  
			}
        });
		function findTreeChild(li_id)
		{
			let returnFlag=0;
			 $.each(FilterData, function(key, val) {
                    if (val['parent'] == li_id) {
                       returnFlag=1;
						
                    }
                });
			return returnFlag;
			
		}
        //Fix start for Issue ID 2429 on 20-Feb-2025
        $(document).off('click', '.marker_span, .marker_text_span').on('click', '.marker_span, .marker_text_span', function (e) {
        //Fix end for Issue ID 2429 on 20-Feb-2025
            e.stopPropagation();
            let li_id = $(this).data('span_id');
            let elm = document.getElementById('ul_of_' + li_id);
			
            if (!elm) {
				if(findTreeChild(li_id)){
                let search_data_list = '<ul class="aib-nav-tree-ul" style="display: none;" id="ul_of_' + li_id + '">';
                $.each(FilterData, function(key, val) {
                    if (val['parent'] == li_id) {
						
                        search_data_list += '<li class="parent_li_data" id="parent_li_' + val['id'] + '" data-li_id="' + val['id'] + '">';
						if(findTreeChild( val['id'])){
                        search_data_list += '<span class="marker_span" id="parent_span_' + val['id'] + '" data-span_id="' + val['id'] + '"></span>';
						}
                        search_data_list += '<input checked type="checkbox" class="toggle_chk" id="toggle_chk_' + val['id'] + '" value="' + val['id'] + '">';
                        if (val['item_type'] == 'AR') {
                            search_data_list += '<span class="marker_text_span" id="parent_span_' + val['id'] + '" data-span_id="' + val['id'] + '"> <strong>' + val['name'] + '</strong></span>';
                        } else {
                            search_data_list += '<span class="marker_text_span" id="parent_span_' + val['id'] + '" data-span_id="' + val['id'] + '"> ' + val['name'] + '</span>';
                        }
                        search_data_list += '</li>';
                    }
                });
                search_data_list += '</ul>';
                $('#parent_li_' + li_id).append(search_data_list);
				}
            }

            if ($('#ul_of_' + li_id).css('display') == 'none') {
                $('#parent_span_' + li_id).css('background-image', 'url(/images/button-open.png)');
                $('#ul_of_' + li_id).css('display', '');
            } else {
                $('#parent_span_' + li_id).css('background-image', 'url(/images/button-closed.png)');
                $('#ul_of_' + li_id).css('display', 'none');
            }
			
			
        });

        $('.search_data_listing').each(function() {
            let cls = $(this).data('cls');
            let cls_len = $('.' + cls).length;
            if (cls_len && cls_len > 1) {
                let elm = document.getElementsByClassName(cls);
                for (let i = 1; i <= cls_len; i++) {
                    if (elm[i]) {
                        elm[i].style.display = 'none';
                    }
                }
            }
        });

        //Fix start for Issue ID 2149 on 30-March-2023
        $(document).on('click', '.search-result a', function(e) {
            e.preventDefault();
            $('.toggle_chk').each(function() {
				
                if ($(this).is(':checked')) {
                    $(this).attr('checked', 'checked');
                } else {
                    $(this).removeAttr('checked');
                }
            });
            localStorage.setItem('searching', document.getElementById('search_result_render_space').innerHTML);
            window.location.href = $(this).attr('href');
        });

        $(window).bind('beforeunload', function(e) {
            e.preventDefault();
            $('.toggle_chk').each(function() {
                if ($(this).is(':checked')) {
                    $(this).attr('checked', 'checked');
                } else {
                    $(this).removeAttr('checked');
                }
            });
            if (!localStorage.getItem('new_search_clicked')) {
                localStorage.setItem('searching', document.getElementById('search_result_render_space').innerHTML);
            }
        });

        if (localStorage.getItem('searching')) {
            document.getElementById('search_result_render_space').innerHTML = localStorage.getItem('searching');
            localStorage.removeItem('searching');
            localStorage.removeItem('ArchiveClicked');
        }
        localStorage.removeItem('new_search_clicked');
        //Fix end for Issue ID 2149 on 30-March-2023
    });
	
    // new search feature on search result page
    var search_item_id="<?php echo $archive_id;?>";
    var archive_type="<?php echo $archive_type;?>";
    var current_item_id="<?php echo $current_item_id;?>";
    var society_template="<?php echo $society_template;?>";
    var currentSearchXHR = null; // Add this at the top of your script if not already present

    $(document).off('click', '.search_in_archive').on('click', '.search_in_archive', function(){
        // Stop any previous AJAX execution
        // if (currentSearchXHR && currentSearchXHR.readyState !== 4) {
        //     currentSearchXHR.abort();
        // }

        // Show loading spinner when search starts
        $('.loading-div').show();

        //Fix start for Issue ID 2149 on 4-April-2023
        localStorage.removeItem('searching');
        localStorage.removeItem('ArchiveClicked');
        localStorage.setItem('new_search_clicked', 1);
        //Fix end for Issue ID 2149 on 4-April-2023
        let buttonType = $(this).data('type'); // Get the data-type attribute
        var search_folder_id = "";
        var chat_request=$('#chat_request_srp').val();
        if (buttonType === 'folder') {
            search_folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
            chat_request = '';
        }
        if($('#search_text_srp').val()!=''){
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+search_item_id+'&current_item_id='+current_item_id+'&search_folder_id='+search_folder_id+'&search_text='+$('#search_text_srp').val()+'&society_template='+society_template+'&chat_request='+chat_request;
            currentSearchXHR = getEncryptedString(queryString, 'search.html', 1);
        } else{
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+search_item_id+'&current_item_id='+current_item_id+'&search_folder_id='+search_folder_id+'&search_text='+$('#search_text_srp').val()+'&society_template='+society_template+'&chat_request='+chat_request;
            currentSearchXHR = getEncryptedString(queryString, 'search.html', 1);
        }
    });
    
    $(".define_search").on("click", function(){
        $(".aib-tree-nav-div input[type='checkbox']").prop("checked", false);
        // $('#selected-ids').val('');
        $('#define_search_text').val($('#search_text_srp').val());
        $("#define_search_folder_home").show();
    });

    $(".popup_close").click(function(){
        $("#define_search_folder_home").hide();
    });

    $(document).off('click', '#define_search_popup_btn').on('click', '#define_search_popup_btn', function(){
        if($('#define_search_text').val()!='' && $('#selected-ids').val() != ''){
            var define_search_ids = $('#selected-ids').val();
            // console.log(define_search_ids);return false;
            // var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&define_search_ids='+define_search_ids+'&search_text='+$('#define_search_text').val()+'&society_template='+society_template;
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+search_item_id+'&current_item_id='+current_item_id+'&define_search=1&search_text='+$('#define_search_text').val()+'&society_template='+society_template;
            // console.log('within if');
            // console.log(queryString);return false;
            getEncryptedStringDefineSearch(queryString, define_search_ids, 'search.html');       
        }else{
            alert('Please select a folder and enter search content');
        }
    });

    $(document).ready(function() {
        animateCachedLlmText();
    });

    function animateCachedLlmText() {
        const textData = $("#cached-llm-text-data").text();
        const referencesData = $("#cached-references-data").html();
        const container = $("#llm-stream-content");
        
        if (textData) {
            // Start with empty main text div
            container.html('<div class="llm-main-text"></div>');
            const textContainer = container.find('.llm-main-text');
            
            // Character by character animation
            let index = 0;
            function typeChar() {
                if (index < textData.length) {
                    textContainer.text(textData.substring(0, index + 1));
                    index++;
                    setTimeout(typeChar, 15);
                } else {
                    // Animation complete, add references
                    if (referencesData) {
                        container.append('<div class="llm-doc-section">' + referencesData + '</div>');
                    }
                    
                    // Clean up hidden elements
                    $("#cached-llm-text-data, #cached-references-data").remove();
                }
            }
            
            typeChar();
        }
    }
</script>
<!--Fix end for Issue ID 2149 on 14-Feb-2023--> 

<?php
} else {
    echo '<h3 class="text-center">No data found</h3>';
}

