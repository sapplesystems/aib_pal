<?php
require_once dirname(__FILE__) . '/config/config.php';

$societyTemp = isset($_REQUEST['society_template']) ? $_REQUEST['society_template'] : '';
if($societyTemp=='custom2'){
	include_once COMMON_TEMPLATE_PATH . 'header2.php';
}else if($societyTemp=='custom1'){
	include_once COMMON_TEMPLATE_PATH . 'details-header.php';
}else{
	include_once COMMON_TEMPLATE_PATH . 'header.php';
}
$archive_id     = (isset($_REQUEST['archive_id']) && $_REQUEST['archive_id'] != '') ? $_REQUEST['archive_id']: '';
$current_item_id     = (isset($_REQUEST['current_item_id']) && $_REQUEST['current_item_id'] != '') ? $_REQUEST['current_item_id']: '';
$search_text    = base64_encode((isset($_REQUEST['search_text']) && $_REQUEST['search_text'] != '') ? $_REQUEST['search_text']: '');
// new llm chat request
$chat_request    = base64_encode((isset($_REQUEST['chat_request']) && $_REQUEST['chat_request'] != '') ? $_REQUEST['chat_request']: '');
// echo $search_text;
// echo "<br>";
// echo base64_decode($search_text);die;
$chat_text    = (isset($_REQUEST['chat_text']) && $_REQUEST['chat_text'] != '') ? $_REQUEST['chat_text']: '';
$string_first_char = (strpos($search_text, "'")!== false) ? 'single' : 'normal';
$search_mode    = (isset($_REQUEST['search_mode']) && $_REQUEST['search_mode']!= '') ? $_REQUEST['search_mode'] : '';
$searched_tags  = (isset($_REQUEST['searched_tags']) && $_REQUEST['searched_tags']!= '') ? $_REQUEST['searched_tags'] : '';
$return_page    = (isset($_REQUEST['return']) && $_REQUEST['return']!= '') ? $_REQUEST['return'].'.html?q='.encryptQueryString('folder_id='.$_REQUEST['current_item_id'].'&society_template='.$societyTemp): '';
$define_search_folders = '';
if(isset($_REQUEST['define_search_ids']) && $_REQUEST['define_search_ids']!= '')
{
    $define_search_folders =  $_REQUEST['define_search_ids'];
    $_SESSION['define_search_ids'] =  $_REQUEST['define_search_ids'];
}elseif(isset($_SESSION['define_search_ids']) && $_SESSION['define_search_ids']!= '')
{
    if(isset($_REQUEST['define_search']) && $_REQUEST['define_search'] == 1)
    {
        $define_search_folders =  $_SESSION['define_search_ids'];
    }
}

function getTreeData($folderId = '') {
    if ($folderId != '') {
        $sessionKey = $_SESSION['aib']['session_key'];
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "get_path",
            "obj_id" => $folderId,
            "opt_get_property" => 'Y'
        );
        $apiResponse = aibServiceRequest($postData, 'browse');
        if ($apiResponse['status'] == 'OK') {
            return $apiResponse['info']['records'];
        }
    }
}

$treeDataArray = getTreeData($current_item_id);

$item_tree_path=array();
if(count($treeDataArray)){
	for($tCount=2;$tCount<=count($treeDataArray)-1;$tCount++){
	
		$item_tree_path[]=$treeDataArray[$tCount]['item_id'];
	}
}
$treeDataArrayItemType = array_column($treeDataArray, 'item_type');
$treeDataArrayItemTypeAgKey = array_search('AG', $treeDataArrayItemType);
$parentFolderId = $treeDataArray[$treeDataArrayItemTypeAgKey]['item_id'];
?>
<style>
    mark{background-color: #fbd42f !important;}
    /* Loader animation */
    .spinner {
        width: 16px;
        height: 16px;
        border: 3px solid #ccc;
        border-top-color: #4cafef;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        display: inline-block;
        vertical-align: middle;
        margin-right: 6px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .llm-loader {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        color: #666;
        font-size: 13px;
    }

    .llm-waiting {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #999;
        font-size: 13px;
    }

    /* Main LLM text */
    .llm-main-text {
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 0px;
        padding: 10px;
        background: #fff;
        /* border-radius: 6px; */
        border: 1px solid #ddd;
        color: #000;
    }

    /* Document matches */
    .llm-doc-section {
        border-top: 1px solid #ccc;
        padding-top: 10px;
        margin-top: 15px;
    }

    .llm-doc-section h4{
        margin: 20px 0px;
        font-weight: bold;
        color: #000;
        font-weight:bold;
    }

    .llm-doc {
        /*border: 1px solid #ddd;*/
        background: #fff;
        padding: 15px 0px;
        /*margin-bottom: 8px;*/
        /* border-radius: 6px; */
        color:#000;
    }

    .llm-doc:nth-child(even){
        background: #eee;
    }
    .llm-doc-score {
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
        display:none;
    }
    .llm-doc-content {
        font-size: 16px;
    }

    .llm-doc-content a{
        color: #000;
    }
    
    .llm-doc-content a:hover{
        text-decoration: underline;
        color: #000;
    }

    #llm-stream-container {
        width: 100%;
        /* max-width: 800px; */
        max-height: 500px;
        min-height: 100px;
        margin: 10px auto 40px;
        padding: 5px;
        border: 1px solid #ccc;
        /* border-radius: 6px; */
        /*background: #f9f9f9;*/
        overflow-y: auto;
        text-align: left;
        box-sizing: border-box;
    }

    #llm-stream-content {
        width: 100%;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Inline blinking dots loader */
    .inline-loader {
        display: inline-block;
        vertical-align: middle;
        margin-left: 4px;
    }

    .inline-loader span {
        display: inline-block;
        width: 6px;
        height: 6px;
        margin: 0 1px;
        background: #555;
        border-radius: 50%;
        animation: blink 1.4s infinite both;
    }

    .inline-loader span:nth-child(2) {
        animation-delay: 0.2s;
    }
    .inline-loader span:nth-child(3) {
        animation-delay: 0.4s;
    }

    .llm-doc .listing li a:after{
        border:0px !important;
    }

    .llm-doc .col-md-1{width: auto;}

    .llm-doc .marginTopBottom10{
            margin: 0 10px 10px 0;
    }

    .search_li_all .col-md-1{width:auto;}

    @keyframes blink {
        0%, 80%, 100% { opacity: 0; }
        40% { opacity: 1; }
    }

</style>
<div class="header_img bgBlue_header">
    <div class="bannerImage bannerImage_society "></div>
    <div class="clientLogo"><img id="client_logo" style="width:200px;" src="" /></div>
</div>
<div class="clearfix"></div>
<div class="content2" style="min-height: 400px;">
    <div class="container">
	<div class="row marginTop20">
        <!-- Fix start on 20-June-2025 -->
		<div style="display:none;"><textarea name="chat_request" id="chat_request"></textarea></div>
        <!-- Fix end on 20-June-2025 -->
            <?php if($search_mode == 'archive'){ ?>
<!--            <div class="col-md-3" >
                <label>Select Archive:</label><select class="form-control" name="archive_listing_select" id="archive_listing_select"></select>
            </div>-->
            <div class="col-md-5 col-sm-12 col-xs-12 searchSec">
                <div style="display:none;">
                <?php if($string_first_char == 'single'){ ?>
                    <input type="text" class="form-control searchInputResult" name="search_text" id="search_text" value="<?php echo htmlspecialchars(base64_decode($search_text), ENT_QUOTES); ?>" placeholder="Enter text">
                <?php }else{ ?>
                    <input type="text" class="form-control searchInputResult" name="search_text" id="search_text" value='<?php echo htmlspecialchars(base64_decode($search_text), ENT_QUOTES); ?>' placeholder="Enter text">
                <?php } ?>
                    <button class="btn" id="search_in_archive">Search</button>

                    <!--//Fix start for Issue ID 2149 on 4-April-2023-->
                    <button class="btn btn-primary" id="reset_search_in_archive">Define Search Folders</button>
                    <!--//Fix end for Issue ID 2149 on 4-April-2023-->
                    </div>
            </div>
            <div class="clearfix clearSec"></div>
            <!-- Fix start on 20-June-2025 -->
            <div class="col-md-3 text-center" style="display:none;"><span class="searchResultHeading">Search Results</span></div>
            <div class="col-md-7 text-right"><a class="backtoLink" href="<?php echo $return_page; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a></div>
            <!-- Fix end on 20-June-2025 -->
            <?php }else{ ?>
                <div class="col-md-5" >
                    <ul id="search_by_tags">
                        <?php 
                            $tagsArray = explode(',',$searched_tags);
                            if(!empty($tagsArray)){
                                foreach($tagsArray as $tag){
                                    echo '<li>'.$tag.'</li>';
                                }
                            }
                        ?>
                    </ul>
                </div>
                <div class="col-md-3 text-center"><span class="searchResultHeading">Search Results</span></div>
                <div class="col-md-4 text-right"><a class="backtoLink" href="<?php echo $return_page; ?>"><img src="<?php echo IMAGE_PATH . 'back-to-search.png'; ?>" alt="Go Back Image" /> Back to Archive</a></div>
            <?php } ?>
        </div>
        <div class="clearfix"></div>
        <div class="row">
        <div class="col-md-12">
            <div id="search_result_render_space" style="display:none;">Loading....</div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<input type="hidden" id="current_page" name="current_page" value="1">
<?php
if($societyTemp=='custom2'){
	include_once COMMON_TEMPLATE_PATH . 'footer2.php';
}else if($societyTemp=='custom1'){
	include_once COMMON_TEMPLATE_PATH . 'details-footer.php';
}else{
	include_once COMMON_TEMPLATE_PATH . 'footer_new.php';
}

?>

<?php //include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script src="<?php echo JS_PATH.'jquery.mark.min.js'; ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
	
/* Fix start Issue id: 0002352  05jan-2024 */	
      window.addEventListener('beforeunload', function (event) {
  event.stopImmediatePropagation();
});
	window.onbeforeunload=null;
/* Fix End Issue id: 0002352  05jan-2024 */
    var search_mode = '<?php echo $search_mode; ?>';
    if(search_mode == 'archive'){
//            getArchiveListing();

    // Code add by Bateshwar to resolve issue 1814 
    var archive_id = "<?php echo $archive_id; ?>";
    <?php if($string_first_char == 'single'){ ?>
        var search_text = "<?php echo $search_text; ?>";
    <?php }else{ ?>
        var search_text = '<?php echo $search_text; ?>';
    <?php } ?>
    
    if(archive_id != '' && search_text!= ''){
         getDataToDisplay(1,archive_id,search_text);
}else if(archive_id != ''){
         getDataToDisplay(1,archive_id,search_text);
}
		


        }else{
            $('#search_by_tags').tagit({ fieldName: "skills", allowSpaces: true, singleFieldDelimiter: ",", placeholderText: 'Separate tags with comma(,)'});
            $('#search_by_tags input[type="text"]').addClass('form-control');
            //$('#search_by_tags').tagit({label: '<?php echo $searched_tags; ?>', value: "<?php echo $searched_tags; ?>"});
            //$(".tagit-new input.ui-widget-content.ui-autocomplete-input").val("<?php echo $searched_tags; ?>").blur();
            getDataByTagsFilter('<?php echo $searched_tags; ?>');
        }
        
        $(document).on('keyup',".tagit-new input.ui-widget-content.ui-autocomplete-input",function(event){
            var pressed_key = event.which;
            var searched_tags = '';
            $("#search_by_tags li").each(function(){
                if($(this).children('span').hasClass('tagit-label')){
                    searched_tags = searched_tags+','+($(this).children('span').text());
                }
            });
            if(pressed_key == 13){ 
                searched_tags = searched_tags.substr(1)
               getDataByTagsFilter(searched_tags);
            }
        });
      <?php if (isset($_SESSION['archive_header_image']) and $_SESSION['archive_header_image'] != '' and $_SESSION['archive_header_image']!='admin/tmp/') { ?>
            $('.bannerImage').css('background-image', 'url(<?php echo $_SESSION['archive_header_image']; ?>)');
           
     <?php }else{ ?>
           $('.bannerImage').css('background-image', 'url(public/images/systemAdmin-header-img.jpg)');
       <?php } if (isset($_SESSION['archive_logo_image']) and $_SESSION['archive_logo_image'] != '' and $_SESSION['archive_logo_image']!='admin/tmp/') { ?>
          
            $('#client_logo').attr('src', '<?php echo $_SESSION['archive_logo_image']; ?>');
     <?php } ?>
    });
//    function getArchiveListing(){
//        var archive_id = "<?php echo $archive_id; ?>";
//        var search_text = "<?php echo $search_text; ?>";
//         $('.loading-div').show();
//         $.ajax({
//            url: "services.php",
//            type: "post",
//            data: {mode: 'get_all_archive'},
//            success: function (response) {
//                $('#archive_listing_select').html(response);
//                if(archive_id != ''){
//                    $('#archive_listing_select').val(archive_id);
//                }
//                if(archive_id != '' && search_text!= ''){
//                    //$('#search_in_archive').trigger('click');
//                    getDataToDisplay(1);
//                }else{
//                    $('.loading-div').hide();
//                }
//            },
//            error: function () {
//                showPopupMessage('error','Something went wrong, Please try again');
//                $('.loading-div').hide();
//            }
//        });
//    }

    //Fix start for Issue ID 2149 on 4-April-2023
    $(document).on('click','#reset_search_in_archive',function(){
        // $('#search_in_archive').click();
        $('#define_search_text').val($('#search_text').val());
        $("#define_search_folder_home").show();
    });
    $(".popup_close").click(function(){
        $("#define_search_folder_home").hide();
    });
    //Fix end for Issue ID 2149 on 4-April-2023
    
//     $(document).on('click', '#search_in_archive', function(){
//         //Fix start for Issue ID 2149 on 4-April-2023
//         localStorage.removeItem('searching');
//         localStorage.removeItem('ArchiveClicked');
//         localStorage.setItem('new_search_clicked', 1);
//         //Fix end for Issue ID 2149 on 4-April-2023
//         var search_text = $('#search_text').val();
// //        var archive_id  = $('#archive_listing_select').val();
//         var archive_id  = '<?php //echo $archive_id; ?>';
//         var search_mode = '<?php //echo $search_mode; ?>';
//         var return_to   = '<?php //echo $_REQUEST['return']; ?>';
//         var current_item_id = '<?php //echo $_REQUEST['current_item_id'] ?>';
//         var archive_type ='<?php //echo $_REQUEST['archive_type']; ?>';
//         window.location.href = 'search.html?archive_type='+archive_type+'&search_mode='+search_mode+'&return='+return_to+'&archive_id='+archive_id+'&current_item_id='+current_item_id+'&search_text='+search_text;
       
//         //getDataToDisplay(1);
//     });

    function getDataByTagsFilter(tag){
        $('.loading-div').show();
        var current_item_id = '<?php echo $_REQUEST['current_item_id']; ?>';
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'search_data_by_tags',search_tags: tag, current_item_id: current_item_id},
            success: function (response) {
                $('#search_result_render_space').html(response);
                var you_were_here = $('.you-were-here').html();
                $('.you-were-here').remove();
                $(you_were_here).insertBefore("#search_result_render_space");
                $('#search_result_render_space').show();
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 286)');
                $('.loading-div').hide();
            }
        });
    }
    
    function getDataToDisplay(current_page,archive_id='',search_text='',paginate=0){
        // var search_text = $('#search_text').val();
        // var archive_id  = $('#archive_listing_select').val();
        // var archive_type= $('#archive_listing_select option:selected').attr('data_type');
		if(archive_id==''){
			archive_id='<?php echo $_REQUEST['archive_id']; ?>';
		}
		if(search_text==''){
		  <?php if($string_first_char == 'single'){ ?>
				var search_text = "<?php echo $search_text; ?>";
			<?php }else{ ?>
				var search_text = '<?php echo $search_text; ?>';
			<?php } ?>
		}	
		if(current_page != ''){
			
				$('#current_page').attr('value',current_page);
		}
		
        var archive_type ='<?php echo $_REQUEST['archive_type']; ?>';
        var record_per_page = $('#record_per_page').val();
        var current_item_id = '<?php echo $_REQUEST['current_item_id']; ?>';
        var search_folder_id = '<?php echo $_REQUEST['search_folder_id']; ?>';
        var define_search_ids = '<?php echo $define_search_folders; ?>';
		var chat_request = '<?php echo $chat_request; ?>';
		<?php if (!empty($chat_request)): ?>
            var chat_request_title = <?php echo json_encode(base64_decode($chat_request)); ?>;
            document.title = chat_request_title;
        <?php endif; ?>
      //  if(search_text != ''){
		if(1){
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'search_data',search_text: search_text, archive_id: archive_id, archive_type: archive_type,current_page: current_page,record_per_page: record_per_page,source:'historical', current_item_id: current_item_id, search_folder_id: search_folder_id, define_search_ids: define_search_ids,chat_request:chat_request},
                success: function (response) {
					if(response!='<h3 class="text-center">No data found</h3>'){
                    $('#search_result_render_space').html(response);
                    var llmRequestId = $('#llm_request_id').val();
                    var chatContentHtml = $('#llm-stream-content').html();
                    // Only call streaming if no cached chat content
                    if (llmRequestId && chatContentHtml === '') {
                        triggerLlmStreamRequest(llmRequestId, chat_request);
                    }

                    var you_were_here = $('.you-were-here').html();
                    $('.you-were-here').remove();
						if(paginate==0){
							 $(you_were_here).insertBefore("#search_result_render_space");
						}
                   
                    $('#search_result_render_space').show();
                    searchTextOrg=$('#search_text').val().replace(/^"(.*)"$/, '$1');
                    searchTextOrg = searchTextOrg.replace(/['",]+/g, '');
                    
                    searchTextOrg = $('#search_text').val().replace(/[",]+/g, '');
                    
                   <?php if((strpos(base64_decode($search_text), '"')!== false)) {?> 
                                $("#search_result_render_space").mark(searchTextOrg,{"separateWordSearch": false});
                    <?php }else{ ?>
                                    $("#search_result_render_space").mark(searchTextOrg);
                    <?php }?>
                
                    //	 $("#search_result_render_space").mark(searchTextOrg);
                    $('.loading-div').hide();
                    var $container = $("html,body");
                    var $scrollTo = $('.AllCheckBox');

						$container.animate({scrollTop: $scrollTo.offset().top - $container.offset().top + $container.scrollTop(), scrollLeft: 0},300); 
					}else{
						$('#current_page').val( parseInt(parseInt($('#current_page').val())-1));
						 $('.loading-div').hide();
						alert('No more result.');
					}	
                   
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 287)');
                    $('.loading-div').hide();
                }
            });
        }
    }

    /**
     * Function: triggerLlmStreamRequest
     * Purpose:  Polls backend for LLM stream response, displays content incrementally
     *           with typing animation and inline loader
     */
    function triggerLlmStreamRequest(llmRequestId, chat_request) {
        // ====== Cached DOM elements ======
        const container = $("#llm-stream-content"); // where we display the text
        const scrollArea = $("#llm-stream-container"); // scroll container for auto-scroll
        const loaderHtml = '<span class="inline-loader"><span></span><span></span><span></span></span>'; // blinking dots loader

        // ====== Internal tracking ======
        let lastCompleteText = "";        // text that has already been rendered to user
        let currentDisplayText = "";   // NEW: Track what's currently displayed
        let typingInProgress = false;  // prevents overlapping character animations
        let llmPollingInterval = null; // setInterval reference to stop later
        let isCancelled = false;
        let pendingText = "";          // NEW: Store pending text to type

        // ===============================================
        // Renders the partial text with inline loader
        // Called when status = WORKING
        // ===============================================
        function renderPartial(text, isWorking) {
            container.html(
                '<div class="llm-main-text">' +
                text.replace(/\n/g, '<br>') +
                (isWorking ? loaderHtml : '') +
                '</div>'
            );

            // Auto-scroll so latest text remains visible
            scrollArea.scrollTop(scrollArea[0].scrollHeight);
        }

        // ===============================================
        // Renders the fully completed text + references
        // Called when status = DONE
        // ===============================================
        function renderFinal(text, referencesHtml) {
            let html = '<div class="llm-main-text">' +
                text.replace(/\n/g, '<br>') +
                '</div>';

            // Append references if provided
            if (referencesHtml) {
                html += '<div class="llm-doc-section"><h4>Chatbot Response References</h4>' + referencesHtml + '</div>';
            }

            container.html(html);
            scrollArea.scrollTop(scrollArea[0].scrollHeight);
        }

        // ===============================================
        // Typing animation - appends new text chunk character by character
        // Calls renderPartial() on each frame to refresh the display
        // ===============================================
        // IMPROVED: Better typing animation with proper state management
        function typeText(newText, isWorking, callback) {
            if (typingInProgress) {
                // Store pending text instead of ignoring
                pendingText = newText;
                return;
            }
            
            typingInProgress = true;
            let i = 0;
            const startText = currentDisplayText; // Start from current display state

            function addChar() {
                currentDisplayText = startText + newText.substring(0, i + 1);
                renderPartial(currentDisplayText, isWorking);
                
                i++;
                if (i < newText.length) {
                    setTimeout(addChar, 15); // Slightly faster for better UX
                } else {
                    typingInProgress = false;
                    lastCompleteText = currentDisplayText; // Update complete text only when done
                    
                    // Process any pending text
                    if (pendingText && pendingText !== newText) {
                        const nextText = pendingText;
                        pendingText = "";
                        typeText(nextText, isWorking, callback);
                    } else if (callback) {
                        callback();
                    }
                }
            }

            addChar();
        }

        // ====== Initial State: Only loader visible until text arrives ======
        container.html(loaderHtml);

        $('#cancel-llm-query').off('click').on('click', function () {
            if (isCancelled) return;
            isCancelled = true;
            clearInterval(llmPollingInterval);
            $('#cancel-llm-query').hide();
            container.html('<div class="alert alert-danger" style="padding:8px 10px;font-size:16px;">Query cancelled by user.</div>');
        });
        // ===============================================
        // Polling function
        // Called every few seconds to get latest partial output from backend
        // ===============================================
        function pollLlm() {
            if (isCancelled || typingInProgress) return; // Skip polling during typing
            var archive_id= "<?php echo $_REQUEST['archive_id']; ?>";
            $.ajax({
                url: "services.php",
                type: "post",
                dataType: "json",
                data: {
                    mode: "get_llm_stream_poll",
                    llm_request_id: llmRequestId,
                    archive_id: archive_id,
                    chat_request: chat_request
                },
                success: function (streamResponse) {
                    if (isCancelled) return;
                    // Extract the raw info text from API (either in api_response.info or info)
                    let infoRaw = "";
                    if (streamResponse.api_response && streamResponse.api_response.info) {
                        infoRaw = streamResponse.api_response.info;
                    } else if (streamResponse.info) {
                        infoRaw = streamResponse.info;
                    }

                    if (streamResponse.status === "WAITING") {
                        // Keep showing loader until we get first non-empty text
                        container.html(loaderHtml);
                    }

                    else if (streamResponse.status === "WORKING") {																   
                        const match = infoRaw.match(/<llm_text>([\s\S]*?)(?:<\/llm_text>|$)/);
                        const currentPartial = match ? match[1].trim() : "";

                        // IMPROVED: Better text comparison logic
                        if (currentPartial && currentPartial !== lastCompleteText) {
                            if (currentPartial.startsWith(lastCompleteText)) {
                                // Only type the new part
                                const newSegment = currentPartial.slice(lastCompleteText.length);
                                if (newSegment.trim()) {
                                    typeText(newSegment, true);
                                }
                            } else {
                                // Complete reset - type whole text
                                lastCompleteText = "";
                                currentDisplayText = "";
                                typeText(currentPartial, true);
                            }
                        }
                    }
                    
                    else if (streamResponse.status === "DONE") {
                        // Stop polling immediately
                        clearInterval(llmPollingInterval);

                        // Safety: decode HTML entities first in case they're escaped
                        let decodedInfo = $("<textarea/>").html(
                            streamResponse.api_response && streamResponse.api_response.info
                                ? streamResponse.api_response.info
                                : (streamResponse.info || "")
                        ).text();

                        // Extract final <llm_text>
                        const textMatch = decodedInfo.match(/<llm_text>([\s\S]*?)<\/llm_text>/i);
                        let finalText = textMatch ? textMatch[1].trim() : "";

                        // If fail to parse, fallback to whatever was last shown
                        if (!finalText && lastCompleteText) {
                            finalText = lastCompleteText;
                        }

                        // Get references HTML (only once)
                        let docsHtml = streamResponse.finalHtmlNew || "";

                        // FORCE character-by-character animation for all text
                        if (finalText) {
                            // Reset display state to ensure full animation
                            if (lastCompleteText === "") {
                                // No previous text shown, animate entire text
                                currentDisplayText = "";
                                typeText(finalText, false, function () {
                                    renderFinal(finalText, docsHtml);
                                    lastCompleteText = finalText;
                                    currentDisplayText = finalText;
                                });
                            } else {
                                // Some text already shown, animate only new part
                                const newTextToType = finalText.slice(lastCompleteText.length);
                                if (newTextToType.trim()) {
                                    typeText(newTextToType, false, function () {
                                        renderFinal(finalText, docsHtml);
                                        lastCompleteText = finalText;
                                        currentDisplayText = finalText;
                                    });
                                } else {
                                    // No new text, just show final
                                    renderFinal(finalText, docsHtml);
                                    lastCompleteText = finalText;
                                    currentDisplayText = finalText;
                                }
                            }
                        } else {
                            // No text to show
                            renderFinal("", docsHtml);
                        }
                    }

                    else if (streamResponse.api_response && streamResponse.api_response.status === "ERROR") {
                        const errorMessage = streamResponse.api_response.info || "An error occurred.";
                        container.html(`<span style="color:red;">${errorMessage}</span>`);
                        clearInterval(llmPollingInterval);
                    }
                },
                error: function () {
                    if (isCancelled) return;
                    container.html("<span style='color:red;'>AJAX request failed. Retrying...</span>");
                    clearInterval(llmPollingInterval);

                    // Restart polling after a short delay (e.g., 3 seconds)
                    setTimeout(function () {
                        if (!isCancelled) {
                            llmPollingInterval = setInterval(pollLlm, 2500);
                            pollLlm(); // trigger immediately
                        }
                    }, 3000);
                }
            });
        }

        // ===============================================
        // Start polling every 2.5 seconds
        // ===============================================
        llmPollingInterval = setInterval(pollLlm, 2500);
        pollLlm(); // Immediate first call
    }

    $(document).on('click','.pagination-link',function(){
        var page_no = $(this).attr('data-count');
        getDataToDisplay(page_no);
    });
    $(document).on('click','.next-page',function(){
        var next_page = parseInt(parseInt($('#current_page').val())+1);
		
        getDataToDisplay(next_page,'','',1);
    });
    $(document).on('click','.previous-page',function(){
        var prev_page = parseInt(parseInt($('#current_page').val())-1);
		
        getDataToDisplay(prev_page,'','',1);
    });
    $(document).on('keyup', '#search_text', function (e) {
        var key = e.which;
        if(key == 13){    
            $('#search_in_archive').click();
        }
    });
    
    $(document).on('click', '.search_result_clicked', function(){
        var item_id = $(this).attr('search_item_id');
        if(item_id){
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'item_visited',item_id: item_id},
                success: function (response) {},
                error: function () {}
            });
        }
        // Manually open link in new tab
        window.open(href, '_blank');
        // Prevent duplicate opening if browser already does it
        e.preventDefault();
    });

    $(document).off('click', '#define_search_popup_btn').on('click', '#define_search_popup_btn', function(){
        if($('#define_search_text').val()!='' && $('#selected-ids').val() != ''){
            $('.loading-div').show();
            var society_template = "<?php echo $societyTemp; ?>";
            var archive_type= "<?php echo $_REQUEST['archive_type']; ?>";
            var archive_id= "<?php echo $_REQUEST['archive_id']; ?>";
            var current_item_id= "<?php echo $_REQUEST['current_item_id']; ?>";
            var define_search_ids = $('#selected-ids').val();
            // console.log(define_search_ids);
            // var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+archive_id+'&current_item_id='+current_item_id+'&define_search_ids='+define_search_ids+'&search_text='+$('#define_search_text').val()+'&society_template='+society_template;
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+archive_id+'&current_item_id='+current_item_id+'&define_search=1&search_text='+$('#define_search_text').val()+'&society_template='+society_template;
            // console.log('within if');
            // console.log(queryString);return false;
            // getEncryptedString(queryString, 'search.html');
            getEncryptedStringDefineSearch(queryString, define_search_ids, 'search.html');       
        }else{
            alert('Please select a folder and enter search content');
        }
    });

</script>
