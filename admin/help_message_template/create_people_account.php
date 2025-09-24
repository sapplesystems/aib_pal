<?php
include_once '../../config/config.php';
$location_id = $_POST['location_id'];
?>
<form name="create_people_account_help_message_frm" id="create_people_account_help_message_frm" action="" method="post">
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
                                <label>Username</label>
                                <textarea class="form-control editor_style" id="people_username" name="people_username"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'people_username', 'pr1', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <!--tr>
                            <td>
                                <label>Email ID</label>
                                <textarea class="form-control editor_style" id="people_email" name="people_email"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'people_email', 'pr2', '<?php //echo $location_id; ?>');">
                            </td>
                        </tr-->
                    </tbody>  
                </table> 
            </div>
        </div>
    </div>
</form>
<?php
exit;
?>