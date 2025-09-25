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


//Fix start for Issue ID 2149 on 14-Feb-2023
// foreach ($apiResponse['info']['records'] as $key => $archiveGroup) {
//     echo '<li id="li_' . $archiveGroup['item_id'] . '" onclick="fetchTreeChildren(event, this, ' . $archiveGroup['item_id'] . ');"><input checked type="checkbox" data_type="ag" value="'.$archiveGroup['item_id'].'" class="FetchTreeChildrenChk" onclick="fetchTreeChildrenChk(event, this, '.$archiveGroup['item_id'].');" /> ' . strtoupper($archiveGroup['item_title']) . '</li>';
//     if (!empty($archiveGroup['archive']) && $requestType == 'ar') {
//         echo '<ul class="aib-nav-tree-ul" id="ul_of_'.$archiveGroup['item_id'].'">';
//         foreach ($archiveGroup['archive'] as $archiveKey => $archiveData) {
//             if($archiveData['item_tree_type'] == 'F'){
//                 echo '<li id="li_' . $archiveData['item_id'] . '" onclick="fetchTreeChildren(event, this, ' . $archiveData['item_id'] . ');"><input type="checkbox" data_type="ar" value="'.$archiveData['item_id'].'" class="FetchTreeChildrenChk" onclick="fetchTreeChildrenChk(event, this, '.$archiveData['item_id'].');" /> ' . strtoupper($archiveData['item_title']) . '</li>';
//             }
//         }
//         echo '</ul>';
//     }
// }
//Fix end for Issue ID 2149 on 14-Feb-2023