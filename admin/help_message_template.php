<?php
include_once '../../config/config.php';
$location_id = $_POST['location_id'];
?>
<form name="record_detail_help_message_frm" id="record_detail_help_message_frm" action="" method="post">
    <div class="row"  id="dataTableDiv">
        <div class="col-md-12 tableStyle">
            <div class="tableScroll">
                <table id="myTable" class="display table helpMessage" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th class="text-center">Element</th> 
                        </tr>  
                    </thead>  
                    <tbody id="listdata">
                        <tr>
                            <td>
                                <label>Related Content</label>
                                <textarea class="form-control editor_style" id="related_content" name="related_content"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'related_content', 'rd1', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Historical Connection</label>
                                <textarea class="form-control editor_style" id="historical_connection" name="historical_connection"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'historical_connection', 'rd2', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Public Connection</label>
                                <textarea class="form-control editor_style" id="public_connection" name="public_connection"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'public_connection', 'rd3', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                    </tbody>  
                </table> 
            </div>
        </div>
    </div>
</form>
<?php
exit;
?>