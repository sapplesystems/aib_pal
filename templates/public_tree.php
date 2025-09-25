<?php 
$archiveTagSearchHide = '';
if(count($treeDataArray)==1 && $treeDataArray[0]['item_id'] == PUBLIC_USER_ROOT){ $archiveTagSearchHide ='hidden'; }
if(count($treeDataArray)==1){ $display="display:block"; $tagDisplay = "display:none";}else{$display="display:none"; $tagDisplay = "display:block";}
$society_template = $treeDataArray[1]['properties']['custom_template'];
?> 
<div id="filterTreePublic" style="<?php echo $display;?>">
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
<div id="filterTreePublic" style="<?php echo $display;?>">
    <h4 class="marginBottom10"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <strong>Search by Name</strong></h4>
    <span class="marginBottom10">
        <input type="text" class="form-control" id="name_search" value="" placeholder="Name" />
    </span>
</div>
<!--END HERE-->

<div id="filter_by_tag_section" style="<?php echo $tagDisplay;?>">
    <h4 class="marginBottom10"><span class="glyphicon glyphicon-tag" aria-hidden="true"></span> <strong>Search by Tags Only</strong></h4>
    <div class="form-group">
        <ul id="search_by_tags"></ul>
    </div>
</div>
<div class="content_archive_search"  <?php echo $archiveTagSearchHide; ?> >
<h4 class="marginBottom10"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> <strong>Select Archive</strong></h4>
<div class="form-group">
    <!--<label for="exampleInputEmail1">Select Archive:</label>-->
    <select class="form-control" name="archive_listing_select" id="archive_listing_select"></select>
</div>
<div class="form-group">
    <input type="text" class="form-control" name="search_text" id="search_text" value="" placeholder="Search by Content">
</div>
<button class="btn search-button" id="search_in_archive_home">Search</button>
</div>
<script type="text/javascript">
    var PUBLIC_USER_ROOT="<?php echo PUBLIC_USER_ROOT;?>";
    var society_template="<?php echo $society_template;?>";
    $(document).ready(function () {
        var folder_id = '<?php echo $folderId; ?>';
        if(folder_id != PUBLIC_USER_ROOT){
            getPublicArchiveListing(folder_id);
        }
        getStateListing();
        $('#search_by_tags').tagit({allowSpaces: true, singleFieldDelimiter: ",", placeholderText: 'Separate tags with comma(,)'});
        $('#search_by_tags input[type="text"]').addClass('form-control');
    });
    
    $(function(){
        $('#search_text').keypress(function (e) {
        var key = e.which;
        if(key == 13)   
            {  $('#search_in_archive_home').click();}
        });
    });
    $(document).on('click', '#search_in_archive_home', function(){
        var folder_id = '<?php echo $folderId; ?>';
        var return_to = 'archive';
        if(folder_id == PUBLIC_USER_ROOT){
            return_to = 'root';
        }
        var queryString = 'search_mode=archive&return=people&return_to='+return_to+'&archive_id='+$('#archive_listing_select').val()+'&search_text='+$('#search_text').val()+'&society_template='+society_template;
        getEncryptedString(queryString, 'public_search.html');
        //window.location.href = 'public_search.html?search_mode=archive&return=people&return_to='+return_to+'&archive_id='+$('#archive_listing_select').val()+'&search_text='+$('#search_text').val();
    });
    function getStateListing() {
        $('.loading-div').show();
        var state = $("#archiveState").val();
        var county = $("#archiveCounty").val();
        var city = $("#archiveCity").val();
        var zip = $("#archiveZip").val();

        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_public_archive_search_items', State: state, County: county, City: city, Zip: zip},
            success: function (response) {
                var records = JSON.parse(response);
                var stateHtml = "<option value=''>- States -</option>";
                $.each(records.State, function (key, value) {
                    stateHtml += "<option value='" + key + "'>" + value + "</option>";
                });
                $('#archiveState').html(stateHtml);

                var cityHtml = "<option value=''>- City -</option>";
                $.each(records.City, function (key, value) {
                    cityHtml += "<option value='" + key + "'>" + value + "</option>";
                });
                $('#archiveCity').html(cityHtml);

                var zipHtml = "<option value=''>- Zip -</option>";
                $.each(records.Zip, function (key, value) {
                    zipHtml += "<option value='" + key + "'>" + value + "</option>";
                });
                $('#archiveZip').html(zipHtml);

                var countyHtml = "<option value=''>- County -</option>";
                $.each(records.County, function (key, value) {
                    countyHtml += "<option value='" + key + "'>" + value + "</option>";
                });
                $('#archiveCounty').html(countyHtml);
                $.each(records.selected, function (key, value) {
                    $('#archive' + key).val(value);
                });
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 1057)');
                $('.loading-div').hide();
            }
        });
    }

    $("#archiveState, #archiveCounty, #archiveCity, #archiveZip").on("change", function () {
        $('.loading-div').show();
        listItems();
        getStateListing();
    });
	

	/*CODE ADDED TO SEARCH PEOPLE BY THEIR NAME - START HERE :- JANUARY 23, 2020 :- BY MANSIJ*/
	var typingTimer;                //timer identifier
	var doneTypingInterval = 1000;  //time in ms (5 seconds)
	var myInput = document.getElementById('name_search');

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
	}
	/*END HERE*/

    function listItems() {
        if ($("#rootFolder").val() > 0) {
            var state = $("#archiveState").val();
            var county = $("#archiveCounty").val();
            var city = $("#archiveCity").val();
            var zip = $("#archiveZip").val();
			var name_search = $("#name_search").val();
            $('.loading-div').show();
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'list_tree_public_items', folder_id: PUBLIC_USER_ROOT, state: state, county: county, city: city, zip: zip, name: name_search},
                success: function (result) {
                    $('#dynamic-home-content').html(result);
                },
                error: function () {
                    showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 1058)');
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
            var queryString = 'search_mode=tags&return=people&searched_tags='+searched_tags.substr(1)+'&current_item_id='+$('#archive_listing_select').val()+'&society_template='+society_template;
            getEncryptedString(queryString,'search.html');
            //window.location.href = 'search.html?search_mode=tags&return=people&searched_tags='+searched_tags.substr(1)+'&current_item_id='+$('#archive_listing_select').val();
        }
    });
</script>
