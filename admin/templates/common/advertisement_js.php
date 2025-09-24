<script type="text/javascript">
    var url = window.location.href;
    var home_page = url.indexOf("home");
    var item_detail_page = url.indexOf("item-details");

    var is_google_ad_top_left = 0;
    var is_google_ad_top_right = 0;
    var is_google_ad_bottom_left = 0;
    var is_google_ad_bottom_right = 0;
    var ad_display_type = '';
    var ad_display_flip_time = '';
    var custom_ad_count = 0;
    $(document).ready(function () {
        var p_folder_id = '<?php echo $p_folder_id; ?>';
        //_getAdvertisement(p_folder_id);
        /*setTimeout(function () {
         if ((ad_display_type && ad_display_type == '2' && custom_ad_count > 3)) {
         getAdvertisement(p_folder_id);
         } else {
         _getAdvertisement(p_folder_id);
         }
         
         if (ad_display_type && ad_display_type == '1' && ad_display_flip_time > 0) {
         setInterval(function(){
         _getAdvertisement(p_folder_id);
         }, ad_display_flip_time * 1000);
         }
         }, 3000);*/
        setTimeout(function () {
            getGoogleAd(p_folder_id);
        }, 500);
    });
    function _getAdvertisement(folder_id) {
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: '_get_advertisement', folder_id: folder_id, rootId: $("#rootFolder").val(), position_for: 'top_left', home_page: home_page, item_detail_page: item_detail_page},
            success: function (response) {
                var obj = JSON.parse(response);
                if (obj.status != 'OK') {
                    return false;
                }
                var record = obj.info.records;
                var div_arr = [];
                $('.ad_div').each(function () {
                    var ad_div_id = $(this).attr('id');
                    div_arr.push(ad_div_id);
                });

                for (var x = 0; x < div_arr.length; x++) {
                    var ad_html = '';
                    var ad_alt = record[x].ad_alt_title;
                    var rec_ref = record[x].record_ref;
                    var ad_url = record[x].ad_url;
                    var org_img = '';
                    if (record[x].original_file != '') {
                        org_img = '<img src="../get_ad_thumb.php?original_file=' + record[x].original_file + '" alt="' + ad_alt + '" />';
                    }
                    if (rec_ref > 0 && record[x].source_def.original_file != '') {
                        org_img = '<img src="../get_ad_thumb.php?original_file=' + record[x].source_def.original_file + '" alt="' + ad_alt + '" />';
                    }
                    if (ad_url) {
                        ad_html = '<a target="_blank" href="' + ad_url + '">' + org_img + '</a>';
                    } else {
                        ad_html = org_img;
                    }
                    $('#' + div_arr[x]).html(ad_html);
                    if (div_arr[x] == 'top_left_position') {
                        $('#top_left_position_item_detail').html(ad_html);
                    }
                }
            },
            error: function () {
            }
        });
    }

    /*function getAdvertisement(folder_id) {
     if (document.getElementById('top_left_position') && is_google_ad_top_left == 0) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement', folder_id: folder_id, rootId: $("#rootFolder").val(), position_for: 'top_left'},
     success: function (response) {
     $('#top_left_position,#top_left_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     if (document.getElementById('top_right_position') && is_google_ad_top_right == 0) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement', folder_id: folder_id, rootId: $("#rootFolder").val(), position_for: 'top_right'},
     success: function (response) {
     $('#top_right_position,#top_right_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     if (document.getElementById('bottom_left_position') && is_google_ad_bottom_left == 0) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement', folder_id: folder_id, rootId: $("#rootFolder").val(), position_for: 'bottom_left'},
     success: function (response) {
     $('#bottom_left_position,#bottom_left_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     if (document.getElementById('bottom_right_position') && is_google_ad_bottom_right == 0) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement', folder_id: folder_id, rootId: $("#rootFolder").val(), position_for: 'bottom_right'},
     success: function (response) {
     $('#bottom_right_position,#bottom_right_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     setTimeout(function () {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement', folder_id: folder_id, rootId: $("#rootFolder").val(), position_for: 'bottom_right2'},
     success: function (response) {
     $('#bottom_right_position2').html(response);
     },
     error: function () {
     }
     });
     }, 1000);
     }
     }
     
     function getAdvertisementFlip1(ad_record, folder_id, flag) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement_flip', folder_id: folder_id, ad_record: ad_record, flag: flag, position_for: 'top_left'},
     success: function (response) {
     $('#top_left_position,#top_left_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     function getAdvertisementFlip2(ad_record, folder_id, flag) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement_flip', folder_id: folder_id, ad_record: ad_record, flag: flag, position_for: 'top_right'},
     success: function (response) {
     $('#top_right_position,#top_right_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     function getAdvertisementFlip3(ad_record, folder_id, flag) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement_flip', folder_id: folder_id, ad_record: ad_record, flag: flag, position_for: 'bottom_left'},
     success: function (response) {
     $('#bottom_left_position#bottom_left_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     function getAdvertisementFlip4(ad_record, folder_id, flag) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement_flip', folder_id: folder_id, ad_record: ad_record, flag: flag, position_for: 'bottom_right'},
     success: function (response) {
     $('#bottom_right_position,#bottom_right_position_item_detail').html(response);
     },
     error: function () {
     }
     });
     }
     
     function getAdvertisementFlip5(ad_record, folder_id, flag) {
     $.ajax({
     url: "services.php",
     type: "post",
     data: {mode: 'get_advertisement_flip', folder_id: folder_id, ad_record: ad_record, flag: flag, position_for: 'bottom_right2'},
     success: function (response) {
     $('#bottom_right_position2').html(response);
     },
     error: function () {
     }
     });
     }*/

    function getGoogleAd(folder_id) {
        is_google_ad_top_left = 0;
        is_google_ad_top_right = 0;
        is_google_ad_bottom_left = 0;
        is_google_ad_bottom_right = 0;
        $.ajax({
            url: "services.php",
            type: "post",
            data: {mode: 'get_google_ad_script', item_id: folder_id},
            success: function (response) {
                var obj = JSON.parse(response);
                if (obj.status == 'OK') {
                    var google_ad_script = obj.google_ad_script;
                    var default_ad_script = obj.default_ad_script;
					 var switch_off_all_adds = obj.switch_off_all_adds;
                    var top_left_position = obj.top_left_position;
                    var top_right_position = obj.top_right_position;
                    var bottom_left_position = obj.bottom_left_position;
                    var bottom_right_position = obj.bottom_right_position;
                    custom_ad_count = obj.custom_ad_count;
                    ad_display_type = obj.ad_display_type;
                    ad_display_flip_time = obj.ad_display_flip_time;
					
					if(switch_off_all_adds==1)
					{}else{
                    if ((custom_ad_count < 3 && folder_id > 1) && (google_ad_script != '') && (top_left_position || top_right_position || bottom_left_position || bottom_right_position)) {
                        console.log('google ad');
                        $('#google_ad_script').val(google_ad_script);
                        if (top_left_position != null && top_left_position != '') {
                            is_google_ad_top_left = 1;
                            $('#top_left_position').html(google_ad_script);
                            $('#top_left_position_item_detail').html(google_ad_script);
                        }
                        if (top_right_position != null && top_right_position != '') {
                            is_google_ad_top_right = 1;
                            $('#top_right_position').html(google_ad_script);
                            $('#top_right_position_item_detail').html(google_ad_script);
                        }
                        if (bottom_left_position != null && bottom_left_position != '') {
                            is_google_ad_bottom_left = 1;
                            $('#bottom_left_position').html(google_ad_script);
                            $('#bottom_left_position_item_detail').html(google_ad_script);
                        }
                        if (bottom_right_position != null && bottom_right_position != '') {
                            is_google_ad_bottom_right = 1;
                            $('#bottom_right_position').html(google_ad_script);
                            $('#bottom_right_position_item_detail').html(google_ad_script);
                            $('#bottom_right_position2').html(google_ad_script);
                        }
                    } else if (custom_ad_count < 3 && folder_id > 1) {
                        console.log('default ad');
                        is_google_ad_top_left = 1;
                        is_google_ad_top_right = 1;
                        is_google_ad_bottom_left = 1;
                        is_google_ad_bottom_right = 1;
                        $('#top_left_position').html(default_ad_script);
                        $('#top_left_position_item_detail').html(default_ad_script);
                        $('#top_right_position').html(default_ad_script);
                        $('#top_right_position_item_detail').html(default_ad_script);
                        $('#bottom_left_position').html(default_ad_script);
                        $('#bottom_left_position_item_detail').html(default_ad_script);
                        $('#bottom_right_position').html(default_ad_script);
                        $('#bottom_right_position_item_detail').html(default_ad_script);
                        $('#bottom_right_position2').html(default_ad_script);
                    }

                    /*if ((ad_display_type && ad_display_type == '2' && custom_ad_count > 3)) {
                     getAdvertisement(folder_id);
                     } else {
                     _getAdvertisement(folder_id);
                     }*/ // THIS IF BLOCK COMMENTED AS THE AD SCROLL FEATURE HAS BEEN STOPPED FOR NOW.
                    if (custom_ad_count >= 3 && folder_id > 1) {
                        _getAdvertisement(folder_id);
                    }

                    //THIS FUNCTION WILL GET THE UNIQUE ADD EVERY TIME AND ALSO WILL WORK FOR FLIP AS AN INTERVAL HAS BEEN INITIALLED BELOW IN CONDITION

                    if (ad_display_type && ad_display_type == '1' && ad_display_flip_time > 0 && custom_ad_count >= 3 && folder_id > 1) {
                        setInterval(function () {
                            _getAdvertisement(folder_id);
                        }, ad_display_flip_time * 1000);
                    }
					
					}
                }
            },
            error: function () {
            }
        });
    }
</script>