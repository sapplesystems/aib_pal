<?php

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

$displayNameArray = array('AG' => 'Archive Group', 'AR' => 'Archive', 'CO' => 'Collection', 'SG' => 'Sub-Group', 'RE' => 'Records');
$countVal = count($treeDataArray);
$archive_id = (isset($treeDataArray[1])) ? $treeDataArray[1]['item_id'] : '';
if ($countVal < 2) {
    echo '<script>$("#filter_by_tag_section").hide();</script>';
}
$subGroup = array();
$subGroupCountShow=0;
foreach ($apiResponse['info']['records'] as $itemDataArray) {
    if ($itemDataArray['item_type'] == 'SG') {
        $subGroup['sub_group'] = $itemDataArray['item_type'];
		$subGroupCountShow++;
    }
}

$subgroup_hide = '';
$hideScrapbook = (isset($societyScrapbookListing) && count($societyScrapbookListing) == 0) ? 'none' : 'inline-block';
if ($subGroupCountShow == 0) {
    $subgroup_hide = 'none';
} else {
    $subgroup_hide = 'inline-block';
}
$shareRecordHidden = '';
$showLink = array('U', 'A');
if (isset($_SESSION['aib']['user_data']) && !in_array($_SESSION['aib']['user_data']['user_type'], $showLink)) {
    $shareRecordHidden = 'hidden';
}
?>

<style>
    #myTableSG_filter{ display:none;}
    .dataTables_length{ display:none;}
    #myTableSG_info{display:none;}
</style>
<?php
$load_more_button = '<div class="loadMoreBtn load_more_data load-more-list-data-val" hidden>
                        <button class="btn search-button load_more_list_data_val" id="load-more-data-button">Load More</button>
                    </div>';
if ($themeName == 'custom1') {
    $beardcrumb_class = 'beardcrumb_archive';
    $arrow_img = '';
    include_once 'home-content-custom2.php';
} else if ($themeName == 'custom') {
    $beardcrumb_class = 'beardcrumb_archive';
    $arrow_img = ' <img src="public/images/arrow_breadcrumb.png" alt="Arrow Breadcrumb">';
    include_once 'home-content-custom1.php';
} else {
    $beardcrumb_class = 'listing';
    $arrow_img = '';
    include_once 'home-content-default.php';
}
?>

<script>
    var folderId = "<?php echo $folderId; ?>";
    (function () {
        function init() {
            var speed = 250,
                    easing = mina.easeinout;
            [].slice.call(document.querySelectorAll('#grid > a')).forEach(function (el) {
                var s = Snap(el.querySelector('svg')), path = s.select('path'),
                        pathConfig = {
                            from: path.attr('d'),
                            to: el.getAttribute('data-path-hover')
                        };
                el.addEventListener('mouseenter', function () {
                    path.animate({'path': pathConfig.to}, speed, easing);
                });
                el.addEventListener('mouseleave', function () {
                    path.animate({'path': pathConfig.from}, speed, easing);
                });
            });
        }
        init();
    })();
    //Check for Show/Hide back button.
    $(document).ready(function () {
        $('#myTableSG_paginate').hide();
        $('.load-more-list-data-val').show();
        $('.load-more-record-data').hide();
        if ($('#group_item_type').val() == 'SG') {
            $('.load-more-record-data').show();
            $('.load-more-list-data-val').hide();
        }
        if ('<?php echo PUBLIC_COUNT_PER_PAGE; ?>' >= parseInt($('#apiResCount').val())) {
            $('.load-more-list-data-val').hide();
            $('.load-more-record-data').hide();
        }
        // Bateshwar added code for show hide archive content
        //$('.content_archive_search').show();
        if ($('.<?php echo $beardcrumb_class; ?> li').length > 2) {
            $('.content_archive_search').show();
        } else {
            $('.content_archive_search').hide();
        }

        //Anil changes for showing deefault tabs
        var archive_id = '<?php echo $archive_id; ?>';
        var tree_count = '<?php echo $countVal; ?>';
        setTimeout(function () {
            $('#archive_listing_select').val(archive_id);
        }, 2000);
        setTimeout(function () {
            var item_type = '<?php echo $itemData['item_type']; ?>';
            if (item_type == 'SG') {
                var tab_first = $('#tab-1').text();
                var tab_second = $('#tab-2').text();
                if (tab_first.indexOf("No data available in table") !== -1 && tab_second.indexOf("No data available in table") === -1) {
                    $('#sub-group-tab').trigger('click');
                    $('#record-tab').hide();
                } else {
                    $('#view-info-text-sub-group').show();
                }
            }
        }, 10);

        //Anil code ends here
        if (JSON.parse($('#previous-item-id').val()) == '') {
            $('#go-back-button').hide();
        } else {
            $('#go-back-button').show();
        }
        /*var clicked_item = '<?php //echo $clickedItem; ?>';
        if ($('#' + clicked_item).length) {
            $('html, body').animate({
                scrollTop: $("#" + clicked_item).offset().top
            }, 1000);
        }*/
    });
    //Logic for go back button.
    $('#go-back-button').click(function () {
        var previous_id = JSON.parse($('#previous-item-id').val());
        var current_previous = previous_id.pop();
        $('#current-item-id').val(current_previous);
        $('#previous-item-id').val(JSON.stringify(previous_id));
        getItemDetailsById(current_previous);
    });

    $(document).ready(function () {
        $('ul.tabs li').click(function () {
            var tab_id = $(this).attr('data-tab');

            $('ul.tabs li').removeClass('current');
            $('.tab-content').removeClass('current');

            $(this).addClass('current');
            $("#" + tab_id).addClass('current');
        });

        var myTable = $('#myTable').dataTable({
            pageLength: '<?php echo DATA_TABLE_PAGE_LENGTH; ?>',
            ordering: false
        });

        $('#customSearchBox').keyup(function () {
            myTable.search($(this).val()).draw();
        });

        var myTableSG = $('#myTableSG').DataTable({
            pageLength: '<?php echo DATA_TABLE_PAGE_LENGTH; ?>',
            ordering: false
        });
        
        $(document).on('click', '.tab-link', function(){
            $('#load-more-data-button').show();
            if($(this).attr('id') == 'historical-tab'){
                $('#load-more-data-button').hide();
            }
        });
    });


    $(".getItemDataByFolderId").on("click", function () {
        var folder_id = $(this).data("folder-id");
        getAdvertisement(folder_id);
    });

    if (folderId == 1) {
        $("#filterTree").show();
    } else {
        $("#filterTree").hide();
    }
    $(document).on('click', '.switch-tab', function () {
        var active_tab = $(this).attr('active-tab-id');
        $('#' + active_tab).trigger('click');
    });
</script>