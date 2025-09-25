<?php
$archiveTagSearchHide = '';
if(count($treeDataArray)==1 && $treeDataArray[0]['item_id'] == HISTORICAL_SOCITY_ROOT){ $archiveTagSearchHide ='hidden'; }
if(count($treeDataArray)==1){ $display="display:block"; }else{$display="display:none";}
$society_template = $treeDataArray[1]['properties']['custom_template'];
?>
<div id="filterTree" style="<?php echo $display;?>">
	<h4 class="marginBottom10"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <strong>Locate by</strong></h4>
	<span class="custom-dropdown marginBottom10">
    <select class="form-control" id="archiveState">
        <option value="">- States -</option>        
    </select>
	</span>
	<!--<h4 class="marginBottom10"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <strong>Locate by County</strong></h4>-->
	<span class="custom-dropdown marginBottom10">
    <select class="form-control" id="archiveCounty">
        <option value="">- County -</option>
        
    </select>
	</span>
	<!--<h4 class="marginBottom10"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <strong>Locate by City</strong></h4>-->
	<span class="custom-dropdown marginBottom10">
    <select class="form-control" id="archiveCity">
        <option value="">- City -</option>
        
    </select>
	</span>
	<!--<h4 class="marginBottom10"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <strong>Locate by Zip</strong></h4>-->
	<span class="custom-dropdown marginBottom10">
    <select class="form-control" id="archiveZip">
        <option value="">- Zip -</option>
        
    </select>
	</span>
</div>

<!-- CODE ADDED TO SEARCH PEOPLE BY THEIR NAME - START HERE :- JANUARY 23, 2020 :- BY MANSIJ -->
<div id="filterTree" style="<?php echo $display;?>">
	<h4 class="marginBottom10"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <strong>Search Archive by Tags</strong></h4>
	<span class="marginBottom10">
		<input type="text" class="form-control" id="archive_search_by_tags" value="" />
	</span>
</div>
<!---->


<div class="content_archive_search" <?php echo $archiveTagSearchHide; ?> >
<h4 class="marginBottom10"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> <strong>Search</strong></h4>
<div class="form-group">
    <!-- <label for="exampleInputEmail1">Select Archive group:</label> -->
    
    <!-- Fix start for Issue ID 2394 on 01-Aug-2024 -->
    <select class="form-control archive_listing_select" name="archive_listing_select" id="archive_listing_select" style="display:none;"></select>
	<!-- Fix end for Issue ID 2394 on 01-Aug-2024 -->

    <!------- Fix start for Issue ID 2149 on 14-Feb-2023 ---->
    <!-- <ul class="aib-nav-tree-ul" id="archive_listing_select_ul"></ul>
    <input type="hidden" id="archive_listing_select" name="archive_listing_select" value="" /> -->
    <!------- Fix end for Issue ID 2149 on 14-Feb-2023 ---->
</div>
<div class="form-group marginBottom10">
    <input type="text" class="form-control" name="search_text" id="search_text" value="" placeholder="Enter Keyword Search Here">
    <span style="color: red;font-size: 12px;">Note: Use quote marks for phrase.</span>
	<textarea class="form-control marginTop10" name="chat_request" id="chat_request" placeholder="Enter Chat Question Here"></textarea>
</div>
<!------- Fix start for Issue ID 2430 on 06-March-2025 ---->
<span style="font-size: 14px;position: relative;top: 4px;">Keyword Search and/or Chatbot Search</span>
<div style="border: 1px solid #15345a;padding: 5px;margin-top: 5px;border-radius: 4px;">
<button class="btn search-button search_in_archive" id="search_in_archive_home" data-type="archive">Search All Folders</button>
<span style="font-size: 10px; font-weight: 400;display:block;">Search all folders from <span id="current_society_name"></span></span>
</div> 
<span style="font-size: 14px;position: relative;top: 9px;">Keyword Search Only</span>
<div style="border: 1px solid #15345a;padding: 5px;margin-top: 10px;border-radius: 4px;">
<button class="btn search-button search_in_archive" id="search_in_folder_home" data-type="folder">Search this Folder</button>
<span style="font-size: 10px; font-weight: 400;display:block;">Search only the current folder you are viewing</span>
<button class="btn search-button define_search" type="button" id="define_search" style="margin-top: 10px;">Define Search Folders</button>
<span style="font-size: 10px; font-weight: 400;display:block;">Choose which folder(s) you want to search</span>
</div>
<!------- Fix end for Issue ID 2430 on 06-March-2025 ---->
<!-- <div id="show-complete-tree"></div> -->
<?php //include_once 'show_archive_tree.php'; ?>
</div>
<br/>
<style>
	.tagit-new{
		width: 220px;
	}
	.arrow {
  border: solid black;
  border-width: 0 3px 3px 0;
  display: inline-block;
  padding: 3px;
cursor: pointer;		
}


.up {
  transform: rotate(-135deg);
  -webkit-transform: rotate(-135deg);
}

.down {
  transform: rotate(45deg);
  -webkit-transform: rotate(45deg);
}
</style>
<div id="filter_by_tag_section" class="content_archive_search"   <?php echo $archiveTagSearchHide; ?> >
    <h4 class="marginBottom10"><span class="glyphicon glyphicon-tag" aria-hidden="true"></span> <strong>Search by Tags Only "Optional"</strong><p style="    margin-top: -10px;    margin-bottom: 10"><i id="updownarraow" style="float: right;" onClick="showHideTagSearch()" class="arrow up"></i></p></h4>
    <div class="form-group" id="tagSearchDiv" style="display: none;">
        <ul id="search_by_tags" style="width: 250px;"></ul>
    </div>
</div>
<!--div id="bottom_left_position"></div-->
<!--<h4 class="marginBottom10"><span class="glyphicon glyphicon-tag" aria-hidden="true"></span> <strong>Search within Content</strong></h4>
<span>Select Archive group: </span>
<select class="form-control" name="archive_listing_select" id="archive_listing_select"></select>
<div style="margin:5px;"></div>
<input type="text" class="form-control" name="search_text" id="search_text" value="" placeholder="Enter text">
<div style="margin:5px;"></div>
<button class="btn search-button" id="search_in_archive_home">Search</button>-->
<script type="text/javascript">
	function showHideTagSearch(){
		
		$('#tagSearchDiv').toggle(100);
		$('#updownarraow').toggleClass('down up');
	}
	var society_template="<?php echo $society_template;?>";
    $(document).ready(function(){
      if($("#listliheaddata li:nth-child(3)").length){
       var folder_id = $("#listliheaddata li:nth-child(3)").attr('data-folder-id');
     }else{
       var folder_id = '<?php echo $folderId; ?>';
     }
        if(folder_id != HISTORICAL_SOCIETY_ROOT){
            getArchiveListing(folder_id);
        }
        getStateListing();
        $('#search_by_tags').tagit({allowSpaces: true, singleFieldDelimiter: ",", placeholderText: 'Separate tags with comma(,)'});
        $('#search_by_tags input[type="text"]').addClass('form-control');
    });
    // Fix start for Issue ID 2430 on 06-March-2025
	$(document).off('click', '.search_in_archive').on('click', '.search_in_archive', function(){
		$('.loading-div').show();
        //Fix start for Issue ID 2149 on 4-April-2023
        localStorage.removeItem('searching');
        localStorage.removeItem('ArchiveClicked');
        localStorage.setItem('new_search_clicked', 1);
        //Fix end for Issue ID 2149 on 4-April-2023
        var archive_type= $('#archive_listing_select option:selected').attr('data_type');
        let buttonType = $(this).data('type'); // Get the data-type attribute
        // console.log("Button type: " + buttonType);
        var search_folder_id = "";
        // Fix start for Issue ID 2502 on 15-July-2025
        var chat_request=$('#chat_request').val();
        if (buttonType === 'folder') {
            search_folder_id = '<?php echo $_REQUEST['folder_id']; ?>';
            chat_request = '';
        }
		// Fix end for Issue ID 2502 on 15-July-2025
        // console.log("search_folder_id: " + search_folder_id);
    if($('#search_text').val()!=''){
		// if(1){
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&search_folder_id='+search_folder_id+'&search_text='+$('#search_text').val()+'&society_template='+society_template+'&chat_request='+chat_request;
            // console.log('within if');
            // console.log(queryString);return false;
            getEncryptedString(queryString, 'search.html', 1);
      //Fix start for Issue ID 2398 on 08-Aug-2024       
	}
		else if($('#dynamic-tree-content-bottom  .content_archive_search  .form-group  #search_text').val()!=''){
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&search_folder_id='+search_folder_id+'&search_text='+$('#dynamic-tree-content-bottom  .content_archive_search  .form-group  #search_text').val()+'&society_template='+society_template+'&chat_request='+$('#dynamic-tree-content-bottom  .content_archive_search  .form-group  #chat_request').val();
            // console.log('within else if');
            // console.log(queryString);
            getEncryptedString(queryString, 'search.html', 1);
   //Fix start for Issue ID 2398 on 08-Aug-2024          
	}
		else{
            //alert('Please enter search content');
			 var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&search_folder_id='+search_folder_id+'&search_text='+$('#search_text').val()+'&society_template='+society_template+'&chat_request='+chat_request;
            // console.log('within if');
            // console.log(queryString);return false;
            getEncryptedString(queryString, 'search.html', 1);
        }
		
	});

    $(".define_search").on("click", function(){
        $(".aib-tree-nav-div input[type='checkbox']").prop("checked", false);
        // $('#selected-ids').val('');
        $('#define_search_text').val($('#search_text').val());
        $("#define_search_folder_home").show();
    });

    $(".popup_close").click(function(){
        $("#define_search_folder_home").hide();
    });

    $(document).off('click', '#define_search_popup_btn').on('click', '#define_search_popup_btn', function(){
        if($('#define_search_text').val()!='' && $('#selected-ids').val() != ''){
            var archive_type= $('#archive_listing_select option:selected').attr('data_type');
            var define_search_ids = $('#selected-ids').val();
            // console.log(define_search_ids);return false;
            // var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&define_search_ids='+define_search_ids+'&search_text='+$('#define_search_text').val()+'&society_template='+society_template;
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&define_search=1&search_text='+$('#define_search_text').val()+'&society_template='+society_template;
            // console.log('within if');
            // console.log(queryString);return false;
            getEncryptedStringDefineSearch(queryString, define_search_ids, 'search.html');       
        }else{
            alert('Please select a folder and enter search content');
        }
    });
    // Fix end for Issue ID 2430 on 06-March-2025
    function getStateListing(){
         $('.loading-div').show();
         var state=$("#archiveState").val();         
         var county=$("#archiveCounty").val();
         var city=$("#archiveCity").val();
         var zip=$("#archiveZip").val();
         
         $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_archive_search_items',State:state,County:county,City:city,Zip:zip},
            success: function (response) {
                   var records=JSON.parse(response);
                   var stateHtml="<option value=''>- States -</option>";
                   $.each(records.State,function(key,value){
                       stateHtml+="<option value='"+key+"'>"+value+"</option>";
                   });
                   $('#archiveState').html(stateHtml);
                   
                   var cityHtml="<option value=''>- City -</option>";
                   $.each(records.City,function(key,value){
                       cityHtml+="<option value='"+key+"'>"+value+"</option>";
                   });
                   $('#archiveCity').html(cityHtml);
                   
                   var zipHtml="<option value=''>- Zip -</option>";
                   $.each(records.Zip,function(key,value){
                       zipHtml+="<option value='"+key+"'>"+value+"</option>";
                   });                   
                   $('#archiveZip').html(zipHtml);
                   
                   var countyHtml="<option value=''>- County -</option>";
                   $.each(records.County,function(key,value){
                       countyHtml+="<option value='"+key+"'>"+value+"</option>";
                   });                   
                   $('#archiveCounty').html(countyHtml);
                
                  
                    $.each(records.selected,function(key,value){                         
                            $('#archive'+key).val(value);
                    });
                  
                
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1062)');
                $('.loading-div').hide();
            }
        });
    }
    
    $("#archiveState, #archiveCounty, #archiveCity, #archiveZip").on("change",function(){
           $('.loading-div').show();
           listItems();
           getStateListing();
		    getArchiveListing(HISTORICAL_SOCIETY_ROOT);
    });
    
	/*CODE ADDED TO SEARCH PEOPLE BY THEIR NAME - START HERE :- JANUARY 23, 2020 :- BY MANSIJ*/
	var typingTimer;                //timer identifier
	var doneTypingInterval = 1000;  //time in ms (5 seconds)
	var myInput = document.getElementById('archive_search_by_tags');

	myInput.addEventListener('keyup', () => {
		clearTimeout(typingTimer);
		//if (myInput.value) {
			typingTimer = setTimeout(doneTyping, doneTypingInterval);
		//}
	});

	function doneTyping () {
		$('.loading-div').show();
		listItems();
		getStateListing();
		getArchiveListing(HISTORICAL_SOCIETY_ROOT);

	}
	/*END HERE*/
    
    function listItems() { 
        if($("#rootFolder").val()>0){
            var state=$("#archiveState").val();
            var county=$("#archiveCounty").val();
            var city=$("#archiveCity").val();
            var zip=$("#archiveZip").val();
	    var archive_search_by_tags=$("#archive_search_by_tags").val();
            
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'list_tree_items', folder_id: HISTORICAL_SOCIETY_ROOT,state: state,county: county,city: city,zip: zip,tags: archive_search_by_tags},
                success: function (result) {
                    //$('.loading-div').hide();
                    $('#dynamic-home-content').html(result);
                },
                error: function () {
                    //$('.loading-div').hide();
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1063)');
                }
            });  
        }
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
            var queryString = 'search_mode=tags&return=home&searched_tags='+searched_tags.substr(1)+'&current_item_id='+$('#current-item-id').val()+'&society_template='+society_template;
            getEncryptedString(queryString,'search.html');
        }
    });

    //Fix start for Issue ID 2394 on 01-Aug-2024
    $(document).ready(function() {
        setTimeout(function() {
            // Get options after delay
            var firstSelectOptions = $('.archive_listing_select').first().html();
            $('.archive_listing_select').eq(1).html(firstSelectOptions);

            // Now get the first option's value
            var firstOptionValue = $(".archive_listing_select option:first").text().trim();
            var formattedValue = firstOptionValue
            .toLowerCase()
            .replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
            $("#current_society_name").text(formattedValue);
        }, 2000); // 2-second delay
    });

    //Fix end for Issue ID 2394 on 01-Aug-2024

    // get tree
    // $(document).ready(function () {
    //     function getWholeTree()
    //     {
    //         var parent_id = "<?=$parent_folder_id_new;?>";
    //         $.ajax({
    //             url: "services.php",
    //             type: "post",
    //             data: {mode: 'get_current_society_complete_tree', parent_folder_id: parent_id},
    //             success: function (result) {
    //                 // console.log(result);
    //                 $('#show-complete-tree').html(result);
    //                 // $('#dynamic-tree-content-bottom').html(result);
                    
    //             },
    //             error: function () {
    //                 alert('Something went wrong, Please try again');
    //             }
    //         });
    //     }
        
    //     getWholeTree();
    // });
</script>
