<?php
$saveTitle = '';
if ($apiResponseRef['info']['records'][0]['item_ref'] == $item_id) {
    if ($apiResponseRef['info']['records'][0]['item_title'] != '') {
        $saveTitle = "<strong  class='yelloBckTitle'>My Scrapbook Title: </strong> &nbsp;" . $apiResponseRef['info']['records'][0]['item_title'];
    } else {
        $saveTitle = "<strong  class='yelloBckTitle'>My Scrapbook Title: </strong> &nbsp;" . $apiResponse['info']['records'][0]['item_title'];
    }
}
$item_title = '';
end($apiResponse['info']['records']);
$key = key($apiResponse['info']['records']);
if ($apiResponse['info']['records'][$key]['item_title'] != '') {
    $item_title = $apiResponse['info']['records'][$key]['item_title'];
}
?>
<div class="myTitle"><span class="desccription_item_details"><?php echo $saveTitle; ?></span></div>
<span class="tableSection">
    <!--h4> 
        <span class="desccription_item_details paddLeftNone">
            <strong><?php //echo ($apiResponse['info']['records'][0]['item_type'] == 'RE') ? 'Record Title :' : 'Item Title :'; ?></strong> 
            <label><?php //echo urldecode($apiResponse['info']['records'][0]['item_title']); ?></label>
        </span>
    </h4-->

    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td width="20%">
                <span style="position: relative;z-index: 1;">
                    <?php echo ($apiResponse['info']['records'][0]['item_type'] == 'RE') ? 'Record Title :' : 'Item Title :'; ?>
                </span>
            </td>
            <td>
                <p><span><?php echo urldecode($apiResponse['info']['records'][0]['item_title']); ?></span></p>
            </td>
        </tr>
        <?php
        if (count($apiResponse['info']['records'][0]['fields']) > 0 || !empty($apiTagResponse['info']['records'])) {
            foreach ($apiResponse['info']['records'][0]['fields'] as $key => $recordsArray) {
                if ($recordsArray['field_value'] != '' && $recordsArray['field_title'] != 'OCR Text') {
                    ?>
                    <tr>
                        <td width="20%"><span style="position: relative;z-index: 1;"><?php echo $recordsArray['field_title']; ?> :</span></td>
                        <td>
                            <p><span><?php echo urldecode($recordsArray['field_value']); ?></span></p>
                        </td>
                    </tr>
                    <?php
                }
            } if (!empty($apiTagResponse['info']['records'])) {
                ?> 
                <tr>
                    <td width="20%"><span>Tags :</span></td>
                    <td>
                        <p><span><?php echo implode(',', $apiTagResponse['info']['records']); ?></span></p>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr><td colspan="2">No fields found.</td></tr>
        <?php } ?>
    </table>
</span>