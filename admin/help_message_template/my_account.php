<?php
include_once '../../config/config.php';
$location_id = $_POST['location_id'];
?>
<form name="manage_home_page_template_help_message_frm" id="manage_home_page_template_help_message_frm" action="" method="post">
    <div class="row"  id="dataTableDiv">
        <div class="col-md-12 tableStyle">
            <div class="tableScroll">
                <table id="myTable" class="display table helpMessage" width="100%" cellpadding="0" cellspacing="0">  
                    <thead>  
                        <tr>  
                            <th class="text-center">Element</th> 
                            <th class="text-center">Element</th>
                        </tr>  
                    </thead>  
                    <tbody id="listdata">
                        <tr>
                            <td>
                                <label>Login</label>
                                <textarea class="form-control editor_style" id="login" name="login"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'login', 'my1', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Name</label>
                                <textarea class="form-control editor_style" id="name" name="name"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'name', 'my2', '<?php echo $location_id; ?>');">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>New Password</label>
                                <textarea class="form-control editor_style" id="new_password" name="new_password"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'new_password', 'my3', '<?php echo $location_id; ?>');">
                            </td>
                            <td>
                                <label>Confirm New Password</label>
                                <textarea class="form-control editor_style" id="confirm_new_pass" name="confirm_new_pass"></textarea>
                                <img title="Save" src="public/images/active.png" alt="Save" onclick="saveHelpMessage(event, 'confirm_new_pass', 'my4', '<?php echo $location_id; ?>');">
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