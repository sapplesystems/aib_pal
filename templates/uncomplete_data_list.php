<form name="uncompleteListForm" id="uncompleteListForm" method="POST" action="" class="formStyle form-group">
    <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>">
    <?php if(!empty($uncompleteListData)){
    foreach($uncompleteListData as $key=>$uncompleteDataArray){ ?>
        <div id="uncomplete_list_<?php echo $key; ?>" class="uncomplete_list_data">
            <input type="hidden" name="item_id_<?php echo $key; ?>" id="item_id_<?php echo $key; ?>" value="<?php echo $uncompleteDataArray['item_id'] ?>">
            <input type="hidden" name="item_modified_<?php echo $key; ?>" id="item_modified_<?php echo $key; ?>" value="<?php echo isset($uncompleteDataArray['edited'])? $uncompleteDataArray['edited'] : '0'; ?>">
            
            <div class="row">
                <div class="col-md-4 text-right"><strong>Item Title :</strong></div>
                <div class="col-md-7">
                    <input type="text" class="form-control"  id="item_title_<?php echo $key; ?>" name="item_title_<?php echo $key; ?>"  placeholder="Text input" value="<?php echo $uncompleteDataArray['item_title'] ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-7">
                    <button type="button" class="btn btn-info borderRadiusNone edit_uncomplete_list_data" data-count="<?php echo $key; ?>" id="edit_uncomplete_<?php echo $key; ?>">Update</button> &nbsp;
                    <button type="button" class="btn btn-info borderRadiusNone mark_as_completed" style="display:none;">Mark As Completed</button>
                    <button type="button" class="btn btn-danger borderRadiusNone back_uncomplete_list_data" data-count="<?php echo $key; ?>" id="">Back</button>
                </div>
            </div>
        </div>
    <?php } } ?>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('.uncomplete_list_data').hide();
        $('#uncomplete_list_0').show();
    });
</script>