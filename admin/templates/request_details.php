<?php 
    $typeArray = ['RD'=>'Content Removal','CT'=>'Contact Request','RP'=>'Reprint Request','RC'=>'Report Content','TT'=>'Trouble Ticket','ICR'=>'Report Comment','STT'=>'Society Trouble Ticket'];
    $statusArray = ['New','In progress', 'Completed'];
?>
<div class="container-fluid">
<?php if(isset($dataArray['req_item']) && $dataArray['req_item'] != '-1'){ ?>
<div class="row">
    <label class="col-xs-2 control-label">Item Id: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['req_item']; ?></div>
</div>
<?php } ?>
<div class="row">
    <label class="col-xs-2 control-label">Request Type: </label>
    <div class="col-xs-9 top-padding"><?php echo $typeArray[$dataArray['req_type']]; ?></div>
</div>
<div class="row">
    <label class="col-xs-2 control-label">Name: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['req_name']; ?></div>
</div>
<?php if(isset($dataArray['req_phone']) && $dataArray['req_phone'] != ''){ ?>
<div class="row">
    <label class="col-xs-2 control-label">Phone: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['req_phone']; ?></div>
</div>
<?php } ?>
<div class="row">
    <label class="col-xs-2 control-label">Email: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['req_email']; ?></div>
</div>
<div class="row">
    <label class="col-xs-2 control-label">IP Address: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['req_ipaddr']; ?></div>
</div>
    <?php if(isset($dataArray['item_link']) && $dataArray['item_link'] != ''){ ?>
<div class="row">
    <label class="col-xs-2 control-label">Item Link: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['item_link']; ?></div>
</div>
<?php } ?>
<?php if(isset($dataArray['reporting_reason'])){?>
<div class="row">
    <label class="col-xs-2 control-label">Reporting Reason: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['reporting_reason']; ?></div>
</div>
<?php }?>

<?php if(isset($dataArray['organization'])){?>
    <div class="row">
        <label class="col-xs-2 control-label">Organization: </label>
        <div class="col-xs-9 top-padding"><?php echo $dataArray['organization']; ?></div>
    </div>
<?php } ?>

<?php if(isset($dataArray['user_type'])){?>
    <div class="row">
        <label class="col-xs-2 control-label">User Type: </label>
        <div class="col-xs-9 top-padding"><?php echo $dataArray['user_type']; ?></div>
    </div>
<?php } ?>
<?php if(isset($dataArray['type_of_trouble'])){?>
    <div class="row">
        <label class="col-xs-2 control-label">Type Of Trouble: </label>
        <div class="col-xs-9 top-padding"><?php echo $dataArray['type_of_trouble']; ?></div>
    </div>
<?php } ?>
<?php if(isset($dataArray['your_computer'])){?>
    <div class="row">
        <label class="col-xs-2 control-label">Computer Type: </label>
        <div class="col-xs-9 top-padding"><?php echo $dataArray['your_computer']; ?></div>
    </div>
<?php } ?>
<?php if(isset($dataArray['browser_type'])){?>
    <div class="row">
        <label class="col-xs-2 control-label">Browser Type: </label>
        <div class="col-xs-9 top-padding"><?php echo $dataArray['browser_type']; ?></div>
    </div>
<?php } ?>
<?php if(isset($dataArray['internet_connection'])){?>
    <div class="row">
        <label class="col-xs-2 control-label">Internet Connection Type: </label>
        <div class="col-xs-9 top-padding"><?php echo $dataArray['internet_connection']; ?></div>
    </div>
<?php } ?>

<div class="row">
    <label class="col-xs-2 control-label">Comment: </label>
    <div class="col-xs-9 top-padding"><?php echo $dataArray['comment']; ?></div>
</div>

<div class="row">
    <label class="col-xs-2 control-label">Status: </label>
    <div class="col-xs-9 top-padding"><?php if($dataArray['req_status'] =='COMPLETED'){ echo $dataArray['req_status']; } else{ echo "NEW";} ?></div>
</div>
<div class="clearfix"></div>
</div>