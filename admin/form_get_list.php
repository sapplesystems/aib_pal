<?php
if (count($mydataarr) > 0) {
    foreach ($mydataarr as $key => $formlist) {
        ?>
        <div class="row" id="TextBoxDiv_<?php echo $formlist['form_field_id']; ?>">
            <div class="col-md-4 text-right">
                <strong><?php echo $formlist['field_title']; ?></strong>
            </div>

            <div class="col-md-7">
                <input type="text" class="form-control custom_form_fields" name="textbox_<?php echo $formlist['form_field_id']; ?>" custom_field_id="<?php echo $formlist['form_field_id']; ?>" id="textbox_<?php echo $formlist['form_field_id']; ?>
                       "  placeholder="Text input">
            </div>
        </div>
    <?php }
} else { ?>
    <div class="row" id="No_field_found"> <div class="col-md-4 text-right"></div><div class="col-md-7"><strong>No fields found. </strong></div></div>
<?php } ?>