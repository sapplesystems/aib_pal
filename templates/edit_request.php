<?php 
    $pageUsedOptions = [
        'BUS' => "Business -- Promote your business by reprinting portion of articles",
        'BUSR'=> "Business -- Research content to be used as a component",
        'MED' => "Media -- Television, radio, print news, public relations",
        'MEDG'=> "Media -- Professional Genealogy Services",
        'MEDA'=> "Media -- Any activity where you receive payment for use of content",
        'PER' => "Personal -- Single scrapbook, family history document",
        'PERE'=> "Personal -- Educational or hobby use",
        'OTH' => "Other -- Describe in detail in the 'comments' section below"
    ];
?>
<form id="edit_request_form" name="edit_request_form" method="post" class="form-horizontal">
    <input type="hidden" name="request_id" id="request_id" value="<?php echo $dataArray['req_id']; ?>">
    <input type="hidden" name="request_type" id="request_type" value="<?php echo $dataArray['req_type']; ?>">
    <?php if(isset($dataArray['req_name'])){ ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">Name: </label>
            <div class="col-xs-7">
                <input type="text" class="form-control" name="request_name" id="request_name" value="<?php echo $dataArray['req_name']; ?>" />
            </div>
        </div>
    <?php } ?>
    <?php if(isset($dataArray['req_email'])){ ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">Email: </label>
            <div class="col-xs-7">
                <input type="text" class="form-control" name="request_email" id="request_email" value="<?php echo $dataArray['req_email']; ?>" />
            </div>
        </div>
    <?php } ?>
    <?php if(isset($dataArray['req_phone'])){ ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">phone: </label>
            <div class="col-xs-7">
                <input type="text" class="form-control" name="request_phone" id="request_phone" value="<?php echo $dataArray['req_phone']; ?>" />
            </div>
        </div>
    <?php } ?>
    <?php if(isset($dataArray['item_link']) && $dataArray['req_type'] != 'CT'){ ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">Item Link: </label>
            <div class="col-xs-7">
                <input type="text" class="form-control" name="request_link" id="request_phone" value="<?php echo $dataArray['item_link']; ?>" />
            </div>
        </div>
    <?php }else{ ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">Subject: </label>
            <div class="col-xs-7">
                <input type="text" class="form-control" name="request_subject" id="request_subject" value="<?php echo $dataArray['item_link']; ?>" />
            </div>
        </div>
    <?php } ?>
    <?php if(isset($dataArray['page_used'])){ ?>
    <div class="form-group">
        <label class="col-xs-4 control-label">How will these pages be used?: </label>
        <div class="col-xs-7">
            <select class="form-control" name="request_page_used" id="request_page_used">
                <option value="">Please select one</option>
                <?php foreach($pageUsedOptions as $key=>$pageOption){ ?>
                    <option <?php if($dataArray['page_used'] == $key){ echo "selected"; } ?> value="<?php echo $key ?>"><?php echo $pageOption; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <?php } ?>
    <?php if(isset($dataArray['comment'])){ ?>
        <div class="form-group">
            <label class="col-xs-4 control-label">Comment: </label>
            <div class="col-xs-7">
                <textarea class="form-control" id="contact_comments" name="request_comments" rows="4"><?php echo $dataArray['comment']; ?></textarea>
            </div>
        </div>
    <?php } ?>
    <div class="form-group">
        <label class="col-xs-4 control-label"></label>
        <div class="col-xs-7">
            <button type="button" class="btn btn-info borderRadiusNone" name="save_request_form_data" id="save_request_form_data">Save</button>
            <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal" >Cancel</button>
        </div>
    </div>
</form>