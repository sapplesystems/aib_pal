<?php
foreach($apiResponse['info']['records'] as $key=>$archiveGroup){
    if($requestType=='ar'){
        echo "<option data_type='ag'  value='".$archiveGroup['item_id']."'>".strtoupper($archiveGroup['item_title'])."</option>";
    }else{
        echo "<option data_type='ag' value='".$archiveGroup['item_id']."'>".strtoupper($archiveGroup['item_title'])."</option>";
    }
    if(!empty($archiveGroup['archive']) && $requestType=='ar'){
        foreach($archiveGroup['archive'] as $archiveKey=>$archiveData){
            if($archiveData['item_tree_type'] == 'F')
                echo "<option data_type='ar' value='".$archiveData['item_id']."'>&nbsp;&nbsp;&nbsp;".strtoupper($archiveData['item_title'])."</option>";
        }
    }
}
