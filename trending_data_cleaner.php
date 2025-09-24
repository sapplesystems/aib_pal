<?php
require_once dirname(__FILE__) . '/config/config.php';
if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) {
   // header('Location: home.php');
}
include_once COMMON_TEMPLATE_PATH . 'header.php';
$artifacts_time=7;

        $sessionKey = $_SESSION['aib']['session_key'];
       $postDataItem = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
		 "opt_get_property" => 'Y',
            "parent" => TRENDING_ARTIFACTS_ID,
         
            
        );
      

		$tradingItems=array();
		$dispLayTrendingItemArray=array();
        $apiResponseTrendingData = aibServiceRequest($postDataItem, 'browse');

$deleteCount=0;
		/*************** remove duplicate and last_viewed_date older then artifacts_time  ************************/
		$trandingItemids=array();
if(isset($apiResponseTrendingData['info']['records']) and count($apiResponseTrendingData['info']['records'])){
		/*<!-- Fix start Issue Id 2380 02-May-2024 -->*/
			if(count($apiResponseTrendingData['info']['records']) >16){
				/*<!-- Fix End Issue Id 2380 02-May-2024 -->*/
			foreach($apiResponseTrendingData['info']['records'] as $trendingData){
				$last_viewed_date=$trendingData['properties']['last_viewed_date'];
				if(in_array($trendingData['properties']['record_id'],$trandingItemids) || strtotime($last_viewed_date) < strtotime('-'.$artifacts_time.' days'))	{
					$postDataDeleteItem = array(
						"_key" => APIKEY,
						"_session" => $sessionKey,
						"_user" => 1,
						"_op" => "delete_item",
					 "obj_id" => $trendingData['item_id']


        				);
					$deleteCount++;
					// $apiResponseDeleteData = aibServiceRequest($postDataDeleteItem, 'browse');
				}
				else{
					
					$trandingItemids[]=$trendingData['properties']['record_id'].$trendingData['properties']['viewed_item_id'];
				}
			}	
			}
}
		
echo $deleteCount;
?>