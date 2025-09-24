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
<div id="filter_by_tag_section" class="content_archive_search"   <?php echo $archiveTagSearchHide; ?> >
    <h4 class="marginBottom10"><span class="glyphicon glyphicon-tag" aria-hidden="true"></span> <strong>Search by Tags Only</strong></h4>
    <div class="form-group">
        <ul id="search_by_tags"></ul>
    </div>
</div>
<div class="content_archive_search" <?php echo $archiveTagSearchHide; ?> >
<h4 class="marginBottom10"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> <strong>Select Archive group</strong></h4>
<div class="form-group">
    <!--<label for="exampleInputEmail1">Select Archive group:</label>-->
    <select class="form-control" name="archive_listing_select" id="archive_listing_select"></select>
</div>
<div class="form-group">
    <input type="text" class="form-control" name="search_text" id="search_text" value="" placeholder="Search by Content">
</div>
<button class="btn search-button" id="search_in_archive_home">Search</button>
</div>
<br/>
<!--div id="bottom_left_position"></div-->
<!--<h4 class="marginBottom10"><span class="glyphicon glyphicon-tag" aria-hidden="true"></span> <strong>Search within Content</strong></h4>
<span>Select Archive group: </span>
<select class="form-control" name="archive_listing_select" id="archive_listing_select"></select>
<div style="margin:5px;"></div>
<input type="text" class="form-control" name="search_text" id="search_text" value="" placeholder="Enter text">
<div style="margin:5px;"></div>
<button class="btn search-button" id="search_in_archive_home">Search</button>-->
<script type="text/javascript">
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
    });
    $(document).on('click', '#search_in_archive_home', function(){
        var archive_type= $('#archive_listing_select option:selected').attr('data_type');
        if($('#search_text').val()!=''){
            var queryString = 'archive_type='+archive_type+'&search_mode=archive&return=home&archive_id='+$('#archive_listing_select').val()+'&current_item_id='+$('#current-item-id').val()+'&search_text='+$('#search_text').val()+'&society_template='+society_template;
            getEncryptedString(queryString, 'search.html');
	}else{
            alert('Please enter search content');
        }
    });
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
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 808)');
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
    
    function listItems() { 
        if($("#rootFolder").val()>0){
            var state=$("#archiveState").val();
            var county=$("#archiveCounty").val();
            var city=$("#archiveCity").val();
            var zip=$("#archiveZip").val();
            
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'list_tree_items', folder_id: HISTORICAL_SOCIETY_ROOT,state: state,county: county,city: city,zip: zip},
                success: function (result) {
                    //$('.loading-div').hide();
                    $('#dynamic-home-content').html(result);
                },
                error: function () {
                    //$('.loading-div').hide();
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 809)');
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
</script>