function getItemDetailsById(id,start_result='',listCount='',group_type='', home='') {
    if (id) {
        if(group_type != ''){
            $('#scroll-loading-more-content').val('yes');
            $('.publicListLoading').show();
        }else{
            if(home == ''){
                $('.loading-div').show(); 
            }
        }
        if(group_type !='')
             mode= 'list_tree_items_with_pagination';
        else
            mode= 'list_tree_items';
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: mode, folder_id: id,start_result:start_result,flg:'list_tree',group_type:group_type},
            success: function (result) {
                if(group_type != ''){
                    if(group_type == 'IT'){
                        $('.add_society_more_load').append(result);
                    }else if(group_type == 'SG'){
                        $('.sub_society_record_more_load').append(result); 
                    }else {
                        $('.sub_society_more_load').append(result); 
                    }
                    var satar_result = $('#satar_result').val();
                    var publicListcount = $('#publicListcount').val();
                    $('#satar_result').val((parseInt(satar_result) + parseInt(listCount)));
                    $('#publicListcount').val(parseInt(publicListcount) +parseInt(listCount));
                    $('#scroll-loading-more-content').val('no');
                    $(".load_more_data button").text("Load More");
                    $(".load_more_data button").prop('disabled', false);
                    $('.load_more_data').attr('disabled', false);
                    if( parseInt( $('#publicListcount').val()) >= parseInt($('#apiResCount').val())){
                        $('.load-more-list-data-val').hide();
                        $('.load-more-record-data').hide();
                    }
                    generateDetailsPageLink();
                }else{
                    $('#dynamic-home-content').html(result);
                    if( parseInt( $('#satar_result').val()) >= parseInt($('#apiResCount').val())){
                        $('.load-more-list-data-val').hide();
                        $('.load-more-record-data').hide();
                    }
                    getCurrentsSetPageNumber(id);
                    if (id == '1'){
                        $('#home_page_register_your_society').show();
                        $('.bannerImage').css('background-image', 'url('+IMAGE_PATH+'systemAdmin-header-img.jpg)'); 
                        getArchiveListing(1);
                    }
                    getUrlVars(id);
                    var parent_title = $('#listliheaddata li:last-child').children('a').text();
                    var complete_path = '';
                    var count = 0;
                    $('#listliheaddata li').each(function(){
                        if($(this).attr('data-title') !== '' && count !== 0 && count !== 2){
                            complete_path = complete_path+'/'+$.trim($(this).attr('data-title'));
                        }
                        count ++;
                    });
                    complete_path = complete_path.slice(1);
                    //$(document).prop('title', "ArchiveInABox  -- "+parent_title);
                    
                    //document.querySelector('meta[name="description"]').setAttribute("content",complete_path);
                    //document.querySelector('meta[name="keywords"]').setAttribute("content",parent_title);
					//$("#tabs-1").animate({ scrollTop: 1000 }, 2000);
					var load_home = localStorage.getItem('animate-load-more-home');
					var load_archive = localStorage.getItem('animate-load-more-archive');
					var load_collection = localStorage.getItem('animate-load-more-collection');
					var load_sub_group = localStorage.getItem('animate-load-more-sub_group');
					var load_record = localStorage.getItem('animate-load-more-record');
					setTimeout(function(){
						if(document.getElementById(load_home)){ console.log('home-'+load_home); document.getElementById(load_home).scrollIntoView(); }
						if(document.getElementById(load_archive)){ console.log('archive-'+load_archive); document.getElementById(load_archive).scrollIntoView(); }
						if(document.getElementById(load_collection)){ console.log('collection-'+load_collection); document.getElementById(load_collection).scrollIntoView(); }
						if(document.getElementById(load_sub_group)){ console.log('sub_group-'+load_sub_group); document.getElementById(load_sub_group).scrollIntoView(); }
						if(document.getElementById(load_record)){ console.log('record-'+load_record); document.getElementById(load_record).scrollIntoView(); }
					},1000);
                }
                getHistoricalConnection(id);
                //listHistoricalConnection(id);
                $('.loading-div').hide();
            },
        error: function () {
            $('.loading-div').hide();
            alert('Something went wrong, Please try again');
        }
        });
    }
}

function generateDetailsPageLink(){
    setTimeout(function(){
        if($('a').hasClass('details-page-url')){
            $('.details-page-url').each(function(){
                var thisObj          = $(this);
                if(thisObj.hasClass('url-append')){
                    thisObj.removeClass('url-append');
                    var id               = thisObj.attr('item-id');
                    var child_count      = thisObj.attr('child-count');
                    var item_type        = thisObj.attr('item-type');
                    var item_parent      = thisObj.attr('item-parent');
                    var current_page_num = $('#myTable').DataTable().page.info();
                    var pageno           = current_page_num.page + 1;
                    var previous_id_obj  = JSON.parse($('#previous-item-id').val());
                    var scrapbook_item   = $('#is_scrapbook_item').val();
                    var extraLink        = '';
                    if(scrapbook_item == 'yes'){
                        extraLink = '&scrapbook_item=' +scrapbook_item+'&flg=scrapbook';
                    }
                    var queryString = "folder_id=" + id + '&previous=' + previous_id_obj + ',' + $('#current-item-id').val() + '&page=' + pageno+extraLink;
                    if(item_type == 'IT'){
                        queryString = "folder_id=" + item_parent+'&itemId='+id+ '&previous=' + previous_id_obj + ',' + $('#current-item-id').val() + '&page=' + pageno+extraLink;
                    }
                    $.ajax({
                        url: "services.php",
                        type: "post",
                        data: {mode: 'get_encrypted_string', queryString: queryString},
                        success: function (response) {
                            thisObj.attr('href', 'item-details.html?q='+response);
                        },
                        error: function () {

                        }
                    });
                }
            });
        }
    },2000);
}

function getHistoricalConnection(item_id){
    $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'get_historical_connection_item', folder_id: item_id},
        success: function (data) {
            if(data){
                $('#historical_connection_data').html(data);
				$('#historical_connection_data_heading').show();
                if($('#historical_connection_count').val() !== '0'){
					
                    $('.historical_connection').show();
                }
            }
        }
    });
}

function listHistoricalConnection(item_id){
    $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'list_historical_connections', folder_id: item_id},
        success: function (data) {
            if(data){
                $('#historical_connections_listing').html(data);
                if($('#list_historical_connection_count').val() !== '0' && item_id !== 1){
                    $('#dynamic-home-content').removeClass('col-md-12 col-sm-12').addClass('col-md-9 col-sm-9');
                    $('.historical_connection_list, #connection_list').show();
                }else{
                    $('#connection_list').hide();
                    $('.historical_connection_list').hide();
                    $('#dynamic-home-content').removeClass('col-md-9 col-sm-9').addClass('col-md-12 col-sm-12');
                }
            }
        }
    });
}

function getCurrentsSetPageNumber(id){
	 //$('.loading-div').show();
		$.ajax({
			url: "services.php",
			type: "post",
			data: {mode: 'get_settable_pagenumber', folder_id: id},
			success: function (data) {  
			 //$('.loading-div').hide();
				if(data){  
					var page = data-1;
					$('#myTable').dataTable().fnPageChange(page);
				}
			},
			error: function () {
				showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 892)');
			}
		});
}

function getPublicItemDetailsById(id,start='',listCount='',group_type='',people_sub_group_type='') {
    if(group_type !='')
          mode= 'public_list_tree_items_with_pagination';
     else
         mode= 'list_tree_public_items';
    if (id) {
       if(group_type != ''){$('.publicListLoading').show(); $('#scroll-loading-more-content').val('yes'); }else{$('.loading-div').show(); }
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: mode, folder_id: id,start:start,group_type:group_type,people_sub_group_type:people_sub_group_type},
            success: function (result) {
                $('.loading-div').hide();
                if(group_type != ''){
                    if(group_type == 'IT'){
                        $('.add_people_more_load').append(result);
                    }else if(people_sub_group_type == 'SG'){
                        $('.people_sub_group_list_record').append(result);
                    }else{
                        $('.people_record_list').append(result);
                    }
                    var satar_result = $('#start_page').val();
                    var publicListcount = $('#public_List_count_val').val();
                    $('#start_page').val((parseInt(satar_result) + parseInt(listCount)));
                    $('#public_List_count_val').val(parseInt(publicListcount) + parseInt(listCount));
                    $('#scroll-loading-more-content').val('no');
                    $(".load_more_data button").text("Load More");
                    $('.load_more_data').attr('disabled', false);
                    $('.load_more_people_list_data').prop('disabled', false);
                    if( parseInt($('#public_List_count_val').val()) >= parseInt($('#apiResCount').val())){
                        $('.load-more-people-list-data').hide();
                        $('.load-more-people-record').hide();
                    }
                }else{
                    $('#dynamic-home-content').html(result);
                    if(id ==PUBLIC_USER_ROOT){
                    $('#home_page_create_my_own_box').show();
					$('#bottom_right_position2').hide();
					$('#bottom_right_position').hide();
                    $('.bannerImage').css('background-image', 'url('+IMAGE_PATH+'systemAdmin-header-img.jpg)'); 
                    getPublicArchiveListing(PUBLIC_USER_ROOT);
                    } 
                }
            },
            error: function () {
                $('.loading-div').hide();
                alert('Something went wrong, Please try again');
            }
        });
    }
}

function getTreeData(folder_id){
    if(folder_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_tree_data', folder_id: folder_id},
            success: function (result) {
                $('#dynamic-tree-content').html(result);
				 $('#dynamic-tree-content-bottom').html(result);
                
            },
            error: function () {
                alert('Something went wrong, Please try again');
            }
        });
    }
}

function getCustomArchiveTree(folder_id){
    if(folder_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_custom_archive_data', folder_id: folder_id},
            success: function (result) {
                $('#custom-archive-tree').html(result);
            },
            error: function () {
                alert('Something went wrong, Please try again');
            }
        });
    }
}

//Fix start for Issue ID 2149 on 14-Feb-2023
function fetchTreeChildren(e, _this, folder_id){
    e.stopPropagation();
    if(folder_id){
        let ul_of = document.getElementById('ul_of_'+folder_id);
        if(ul_of){
            $('#ul_of_'+folder_id).toggleClass('hide');
            if($('#ul_of_'+folder_id).hasClass('hide')){
                $(_this).css('list-style-image',"url('/images/button-closed.png')");
            }else{
                $(_this).css('list-style-image',"url('/images/button-open.png')");
            }
        }else{
            $.ajax({
                url: "services.php",
                type: "post",
                data: {mode: 'fetch_children_tree', folder_id: folder_id},
                success: function (result) {
                    let res = result.trim();
                    if(res){
                        $(_this).append(res);
                        $(_this).css('list-style-image',"url('/images/button-open.png')");
                    }
                },
                error: function () {
                    alert('Something went wrong, Please try again');
                }
            });
        }
    }
}

function fetchTreeChildrenChk(e, _this, folder_id){
    e.stopPropagation();
    $("#archive_listing_select").val('');

    let checkboxes = [];
    $('.FetchTreeChildrenChk').each(function(){
        if($(this).is(':checked')){
            let arid = $(this).val();
            let item_type = $(this).attr('data_type')
            let arids = item_type+'_'+arid
            checkboxes.push(arids);
        }
    });
    $("#archive_listing_select").val(checkboxes.join(','));
}
//Fix end for Issue ID 2149 on 14-Feb-2023

function getPublicTreeData(folder_id){
    if(folder_id){
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_public_tree_data', folder_id: folder_id},
            success: function (result) {
                $('#dynamic-tree-content').html(result);
                $('#dynamic-tree-content-bottom-people').html(result);
            },
            error: function () {
                alert('Something went wrong, Please try again');
            }
        });
    }
}

function getArchiveListing(folder_id){
         //$('.loading-div').show();
	 var state=$("#archiveState").val();         
         var county=$("#archiveCounty").val();
         var city=$("#archiveCity").val();
         var zip=$("#archiveZip").val();
         $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_all_archive',folder_id: folder_id, page: 'search',state: state,county: county,city: city,zip: zip},
            success: function (response) {
                $('#archive_listing_select').html(response);
                //Fix start for Issue ID 2149 on 14-Feb-2023
                // setTimeout(function(){
                //     $("#archive_listing_select").val('ag_'+$("#archive_listing_select_ul li:first").attr('id').split('_')[1]);
                // }, 2000);
                //Fix end for Issue ID 2149 on 14-Feb-2023

                /*if(folder_id == '1'){
                    setTimeout(function(){
                        $("#archive_listing_select").val($("#archive_listing_select option:first").val());
                    }, 2000);
                }*/
                //$('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 893)');
                //$('.loading-div').hide();
            }
        });
    }
    
function getPublicArchiveListing(folder_id){
         $('.loading-div').show();
         $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_all_public_archive',folder_id: folder_id, page: 'search'},
            success: function (response) {
                $('#archive_listing_select').html(response);
                if(folder_id == PUBLIC_USER_ROOT){
                    setTimeout(function(){
                        $("#archive_listing_select").val($("#archive_listing_select option:first").val());
                    }, 2000);
                }
                $('.loading-div').hide();
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 894)');
                $('.loading-div').hide();
            }
        });
    }
    
function getAllSocietyList(container_id){
    $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'get_all_society_admin'},
        success: function (data){
            var result = JSON.parse(data);
            Object.keys(result).forEach(key => {
                $('#'+container_id).append('<option value="'+key+'">'+result[key]+'</option>');
            });
            $('#'+container_id).selectize({
                create : false
            });
        },
        error: function (){
            showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 895)');
        }
    });
}

$(document).on('click', '.connect-to-society', function(){
    var connecting_item_id = $(this).attr('connecting-item-id');
    if(connecting_item_id !== 'undefined' && connecting_item_id !== ''){
        $('#selected_item_id').val(connecting_item_id);
        $('.loading-div').show();
        $('#sub-group-records').html('');
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_society_complete_tree'},
            success: function (data){
                $('#society_tree_data').html(data);
                $('li[id^="aib_navlist_entry_"]').each(function(){
                    var id = $(this).attr('id').split("_").pop(-1);
                    $(this).prepend('<input type="checkbox" data-item-id="'+id+'" value="'+id+'" checkbox-type="custom" class="custom-checkbox-append" />');
                });
                $('#connect_with_other_society').modal('show');
                $('.loading-div').hide();
            },
            error: function (){
                $('.loading-div').hide();
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 896)');
            }
        });
    }else{
        showPopupMessage('error', 'Invalid source item, Please try again!');
    }
});


$(document).on('click', '.connect-to-society-detail', function(){
    var connecting_item_id = $(this).attr('connecting-item-id');
    if(connecting_item_id !== 'undefined' && connecting_item_id !== ''){
        $('#selected_item_id').val(connecting_item_id);
        $('.loading-div').show();
        $('#sub-group-records').html('');
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_society_complete_tree'},
            success: function (data){
                $('#society_tree_data').html(data);
                $('li[id^="aib_navlist_entry_"]').each(function(){
                    var id = $(this).attr('id').split("_").pop(-1);
                    $(this).prepend('<input type="checkbox" data-item-id="'+id+'" value="'+id+'" checkbox-type="custom" class="custom-checkbox-append" />');
                });
                $('#connect_with_other_society_detail').modal('show');
                $('.loading-div').hide();
            },
            error: function (){
                $('.loading-div').hide();
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 897)');
            }
        });
    }else{
        showPopupMessage('error', 'Invalid source item, Please try again!');
    }
});

function getRecordsList(id){
    if($('#aib_item_checkbox_'+id).is(":checked")){
        $('.bgOverlay_loader').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_sub_group_records',item_id: id},
            success: function (data){
                $('.bgOverlay_loader').hide();
                $('.accordion_head').each(function(){
                    if($(this).siblings('div').is(":visible")){
                        $(this).trigger('click');
                    }
                });
                $('#sub-group-records').append(data);
            },
            error: function (){
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 898)');
            }
        });
    }else{
        $('#sg_'+id).remove();
    } 
}

$(document).on('click', '.create-connection', function(){
    var prev_count = parseInt($(this).closest('div.accordion_container').children('h5').children('a').children('label').text());
    if($(this).hasClass('active')){
        $(this).parents('table').siblings('p').children('input').attr('checked', false);
        if(prev_count > 3){
            showPopupMessage('error', 'You can only connect 3 records from a single sub group');
            $('.create-connection.active').each(function(){
                $(this).removeClass('active');
                prev_count --;
            });
        }else{
            $(this).removeClass('active');
            prev_count --;
        }
    }else{
        if(prev_count > 2){
            if($(this).parents('table').siblings('p').children('input').is(':checked')){
                $(this).addClass('active');
                prev_count ++;
            }else{
                showPopupMessage('error', 'You can only connect 3 records from a single sub group');
                return false;
            }
        }else{
            $(this).addClass('active');
            prev_count ++;
        }
    }
    $(this).closest('div.accordion_container').children('h5').children('a').children('label').text(prev_count);
});

$(document).on('click', '.accordion_head', function(){
    if($(this).siblings('div').is(":visible")){
        $(this).siblings('div').hide('slow');
        $(this).children('span').removeClass('glyphicon-triangle-bottom').addClass('glyphicon-triangle-right');
    }else{
        $(this).siblings('div').show('slow');
        $(this).children('span').removeClass('glyphicon-triangle-right').addClass('glyphicon-triangle-bottom');
    }
});

$(document).on('click', '#society_connection_button', function(){
    var checked_count = 0;
    var selected_records = [];
    var connecting_item = $('#selected_item_id').val();
    $('.create-connection.active').each(function(){
        if(!$(this).parents('table').siblings('p').children('input').is(':checked')){
            checked_count ++;
            selected_records.push($(this).attr('data-item-id'));
        }
    });
    if(checked_count === 0){
        $('input[id^="aib_item_checkbox_"]').filter(':checked').each(function(){
            checked_count ++;
            var item_string = $(this).attr('id').split('_');
            var item_id = item_string[item_string.length-1];
            selected_records.push(item_id);
        });
    }
    if(checked_count === 0){
        $('.custom-checkbox-append').filter(':checked').each(function(){
            checked_count ++;
            selected_records.push($(this).val());
        });
    }
    if(checked_count > 0){
        $('.loading-div').show();
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'connect_item_with_other_society_item', connecting_item: connecting_item, selected_items: selected_records},
            success: function (data){
                var result = JSON.parse(data);
                $('.loading-div').hide();
                $('#connect_with_other_society').modal('hide');
                showPopupMessage(result.status,result.message);
            },
            error: function (){
                $('.loading-div').hide();
                showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 899)');
            }
        });
    }else{
        showPopupMessage('error', 'Please select an item to connect with.');
    }
});

$(document).on('click','.connect_with_multiple_records', function(){
    if($(this).children('input').is(':checked')){
        $(this).closest('div.view-first').addClass('active');
    }else{
        $(this).closest('div.view-first').removeClass('active');
    }
    if($('.multi-record-checkbox').filter(':checked').length > 0){
        var selected_records = [];
        $('.multi-record-checkbox').filter(':checked').each(function(){
            selected_records.push($(this).val());
        });
        $('.connect-with-multiple-records').attr('connecting-item-id', selected_records);
        $('.connect-with-multiple-records').show();
    }else{
        $('.connect-with-multiple-records').hide();
    }
    
});

$(document).on('click', '.select-all-records', function(){
    if($(this).is(':checked')){
        $(this).parents('p').siblings('table').children('tbody').children('tr').children('td').children('div.create-connection').each(function(){
            if(!$(this).hasClass('active')){
                $(this).trigger('click');
            }
        });
    }else{
        $(this).parents('p').siblings('table').children('tbody').children('tr').children('td').children('div.create-connection').each(function(){
            if($(this).hasClass('active')){
                $(this).trigger('click');
            }
        });
   }
});

function getUrlVars(id = ''){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++){
        hash = hashes[i].split('=');
        vars[hash[0]] = hash[1];
    }
    if(id !== ''){
        updateUrl(vars, id);
    }
    return vars;
}

function getEncryptedString(queryString, fileName, openInNewTab = 0){
	$('.loading-div').hide();
    $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'get_encrypted_string', queryString: queryString},
        success: function (response) {
            var stringData = response;
            if(openInNewTab === 1){
                window.open(fileName+'?q='+stringData, '_blank');
            }else{
                window.location.href = fileName+'?q='+stringData;
            }
        },
        error: function () {

        }
    });
}

function getEncryptedStringDefineSearch(queryString, defineSearchIds, fileName, openInNewTab = 0){
	$.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'get_encrypted_string', queryString: queryString},
        success: function (response) {
            var stringData = response;

            // Create a form dynamically
            var form = $('<form>', {
                action: fileName+'?q='+stringData,
                method: 'POST',
                target: openInNewTab === 1 ? '_blank' : '_self'
            }).append(
                $('<input>', { type: 'hidden', name: 'define_search_ids', value: defineSearchIds }) // Large data
            );
            $('body').append(form);
            form.submit();
        },
        error: function () {

        }
    });
}

function updateUrl(vars, id){
    $.ajax({
        url: "services.php",
        type: "post",
        data: {mode: 'get_encrypted_url', queryString: vars['q'], item_id: id},
        success: function (response) {
            var browserUrl = window.location.href;
            var url_parts = browserUrl.split("?");
            var completeUrl = url_parts[0]+'?q='+response;
            history.pushState({}, null, completeUrl);
        },
        error: function () {

        }
    });
}
