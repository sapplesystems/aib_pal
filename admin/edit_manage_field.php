<div class="form-group" hidden="">
        <label class="col-xs-4 control-label">Field Id</label>
        <div class="col-xs-7">
            <input type="text" class="form-control" id="fldid" name="field_id" value="<?php echo $recordget[0]['field_id']; ?>" disabled="disabled" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-4 control-label">Field Owner</label>
        <div class="col-xs-7">
            <input type="text" class="form-control" id="fldowner" name="field_owner" value="<?php echo $itemTitle['item_title']; ?>" disabled="disabled"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-4 control-label">Field Title</label>
        <div class="col-xs-7">
            <input type="text" class="form-control" id="fldtitle" name="field_title" value="<?php echo urldecode($recordget[0]['field_title']); ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-4 control-label">Field Type</label>
        <div class="col-xs-7">
            <!--<input type="text" class="form-control" id="fldtype" name="field_datatype" value="<?php // echo $val['field_data_type']; ?>" />-->
       
         <span class=" ">
            <select class="form-control" id="fldtype"  name="field_datatype" >
                <option value="">- Select -</option> 
                <?php 
                $type = array('T'=>'Short Text, Up To 255 Characters','B'=>'Long Text, Up To 64,000 Characters','F'=>'Number',
                    'I'=>'Whole Number','E'=>'Number With Fixed Decimals','D'=>'Date','M'=>'Time',
                    'DT'=>'Combined Date And Time','TS'=>'System Timestamp (No Editing)','DD'=>'Option List');
                    foreach($type as $key=>$value){
                    if($recordget[0]['field_data_type']== $key){$fieldType = 'selected';}else{$fieldType='';}
                    echo' <option value="'.$key.'" '.$fieldType.'>'.$value.'</option>';
                    }
                ?>        
            </select>
        </span>
        </div>
    </div>
    <!--<div class="form-group">
        <label class="col-xs-4 control-label">Field Format</label>
        <div class="col-xs-7">
            <input type="text" class="form-control" id="fldformat" name="field_format" value="<?php echo urldecode($recordget[0]['field_format']); ?>" />
        </div>
    </div> -->
    <div class="form-group">
        <label class="col-xs-4 control-label">Field Size</label>
        <div class="col-xs-7">
            <input type="text" class="form-control"  id="fldsize" name="field_size" value="<?php echo $recordget[0]['field_size']; ?>"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-4 control-label"></label>
        <div class="col-xs-7">
            <button type="button" id="btn-click" class="btn btn-info borderRadiusNone" name="fields_btn">Update</button>
            <button type="button" class="btn btn-danger borderRadiusNone clearAdminForm" id="clearFieldsForm">Clear Form</button>
        </div>
    </div>