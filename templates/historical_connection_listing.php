<?php
$displayNameArray = array('AG' => 'Folder', 'AR' => 'Archive', 'CO' => 'Collection', 'SG' => 'Sub-Group', 'RE' => 'Records', 'IT' => 'File');
$type = (isset($_SESSION['type']) && $_SESSION['type'] == 'A') ? true : false;
$loggedInType = isset($_SESSION['aib']['user_data']['user_type']) ? $_SESSION['aib']['user_data']['user_type'] : '';
?>
<div class="col-md-12">
	<div class="row">
		<div class="text-center">
			<label><b>Display Connection Type:</b></label>
			<select id="admin_historical_connections">
				<option value="item_share">All</option>
				<option value="shared_from_user" <?php echo isset($_REQUEST['perspective'])&&$_REQUEST['perspective']=='shared_from_user'?'selected':'' ?>>Your HIstorical Connections to Others</option>
				<option value="shared_to_user" <?php echo isset($_REQUEST['perspective'])&&$_REQUEST['perspective']=='shared_to_user'?'selected':'' ?>>Other's Historical Connections to You</option>
			</select>
		</div>
	</div>
</div>
<div class="col-md-12">
    <div class="marginTop20">

    </div>
    <table id="manage_records_table_data" class="display table" width="100%" cellspacing="0" cellpadding="0">  
        <thead>  
            <tr>  
                <th width="31%" class="text-center">Original Content</th>
                <th width="31%" class="text-center">Connection Location</th>
                <th class="text-center">Connection Type</th>
                <th class="text-center">Connection Owner</th>
                <th class="text-center">Created</th>
                <th width="10%" class="text-center">Actions</th>
            </tr>  
        </thead>  
        <tbody>
			<?php
			foreach ($dataArray as $key=>$value) {
			?>
			<tr>
				<td><?php echo $value['original_content'] ?></td>
				<td><?php echo $value['connection_location'] ?></td>
				<td><?php echo $value['connection_type'] ?></td>
				<td><?php echo $value['connection_owner'] ?></td>
				<td><?php echo $value['created'] ?></td>
				<td><?php echo $value['actions'] ?></td>
			</tr>
			<?php
			}
			?>
        </tbody>  
    </table>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('#manage_records_table_data').DataTable({
			"pageLength": 100,
			columnDefs: [{
					targets: 'no-sort',
					orderable: false
				}]
		});
	});

</script>