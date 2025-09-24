<?php

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}
?>
<div class="accordion_container" id="sg_<?php echo $itemDetails['item_id'] ?>">
    <h5 class="accordion_head"><?php echo $itemDetails['item_title'] ?> <a class="sub-group-item-count"><label class="total-count">0</label>Item(s) selected</a> <span class="glyphicon glyphicon-triangle-bottom pull-right" aria-hidden="true"></span></h5>
    <div class="accordion_body">
        <?php if(count($dataRecordsArray['info']['records']) > 0){  ?><!--<p class="cursorPointer">Select All <input type="checkbox" class="select-all-records" data-sg-id="<?php echo $item_id; ?>" /></p> !--> <?php } ?>
        <table id="myTable" class="custum_tbl custom_css sub-groups-records" width="100%" cellpadding="0" cellspacing="0">
            <thead>  
                <tr>  
                    <th>Forms Id</th>   
                </tr>  
            </thead> 
            <tbody class="society_ast_sub_list widthB">
                    <?php if(count($dataRecordsArray['info']['records']) > 0){ foreach ($dataRecordsArray['info']['records'] as $itemDataArray) { ?>
                    <tr>
                        <?php if (isset($itemDataArray['stp_url']) && $itemDataArray['stp_url'] != '') { ?>
                            <td>
                                <div class="view view-first create-connection" data-item-id="<?php echo $itemDataArray['item_id']; ?>">
                                    <img src='<?php echo addhttp($itemDataArray["stp_thumb"]) ?>' alt="Stp Image" />
                                    <div class="mask">
                                        <a class="custom-link" href="javascript:void(0)"></a> 
                                    </div>
                                </div>
                                <h6 class="recordHead">
                                    <?php echo urldecode($itemDataArray['item_title']); ?>
                                </h6>
                            </td>
                            <?php } else { ?>
                            <td class="text-center">
                                <div class="view view-first create-connection" data-item-id="<?php echo $itemDataArray['item_id']; ?>">
                                    <img src='<?php echo RECORD_THUMB_URL . '?id=' . $itemDataArray['item_id']; ?>' alt="Thumb Image"/>
                                    <div class="mask">
                                        <a class="custom-link" href="javascript:void(0)"></a>
                                    </div>
                                </div>
                                <h6 class="recordHead">
                                    <?php echo urldecode($itemDataArray['item_title']); ?>
                                </h6>
                            </td>
                    <?php } ?>
                    </tr>
                    <?php } } else{ ?>
                    <tr style="width:100% !important;"><td class="text-center">No records found.</td></tr>
                    <?php } ?>
            </tbody>
        </table>
    </div>
</div>