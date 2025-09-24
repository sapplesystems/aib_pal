<?php
$userType = $_SESSION['aib']['user_data']['user_type'];
 if ($_POST['mode'] == 'get_child') {

//    print_array($apiResponse,true);
//    
//  foreach( $data as $key=>$subgroup){
//            if($subgroup['item_title'] !='Advertisements' ){
//           $html = "<option value='' style='font-weight:bold;' >---Select---</option>";
//           $html.= "<option data-type='ar' value='".$subgroup['item_id']."'>&nbsp;&nbsp;&nbsp;". $subgroup['item_title']."</option>";
//           }
//        } 
//        echo $html;
} else {
    foreach ($apiResponse['info']['records'] as $key => $archiveGroup) {
        if ($requestType == 'ar') {
            echo "<option data-type='ag' value='" . $archiveGroup['item_id'] . "'>" . $archiveGroup['item_title'] . "</option>";
        } else {
            echo "<option data-type='ag' value='" . $archiveGroup['item_id'] . "'>" . $archiveGroup['item_title'] . "</option>";
        }
        if (!empty($archiveGroup['archive']) && $requestType == 'ar') {
            foreach ($archiveGroup['archive'] as $archiveKey => $archiveData) {
                if($userType !='U' && $archiveData['item_tree_type'] == 'F'){
                    echo "<option data-type='ar' value='" . $archiveData['item_id'] . "'>&nbsp;&nbsp;&nbsp;" . $archiveData['item_title'] . "</option>";
                }
            }
        }
    }
}