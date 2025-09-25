<?php

if ($_POST['mode'] == 'get_parent_item_list') {
    $record = $apiResponse['info']['records'];  
    if ($folder_title == 'ar') {
        //echo "<option style='font-weight:bold;' disabled>" . $parent_title . " </option>";
        foreach ($apiResponseItemData['info']['records'] as $key => $archiveData) {
            if ($archiveData['item_title'] != 'Advertisements' && $archiveData['item_id'] != $_POST['current_folder_id']) {
                echo "<option data-type='ar' value='" . $archiveData['item_id'] . "'>" . $archiveData['item_title'] . "</option>";
            }
        }
    }
	else { 
		$flag = 1;
        foreach ($record as $key => $archiveData) { 
			 if ($archiveData['item_title'] != 'Advertisements' && $archiveData['item_id'] != $_POST['current_folder_id']) {   
					echo "<option data-type='ar' value='" . $archiveData['item_id'] . "'>" . urldecode($archiveData['item_title']) . "</option>";
					$flag = 2;
			 }  
		  }
		  
		if($flag ==1){
			echo "Error"; 
		} 
    }
}  