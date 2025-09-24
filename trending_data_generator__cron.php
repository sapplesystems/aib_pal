<?php
require_once dirname(__FILE__) . '/config/config.php';
$file_path_trading_data=AIB_USER_TMP.'displayTrendingItem_cron.txt';

function aibServiceRequest($postData, $fileName, $mail = null) {
    // Create a new curl resource
    $curlObj = curl_init();
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => AIB_SERVICE_URL . '/api/' . $fileName . ".php",
        CURLOPT_FRESH_CONNECT => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 0,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_POSTFIELDS => http_build_query($postData)
    );
    // Set multiple options for a curl transfer
    curl_setopt_array($curlObj, $options);
    $result = curl_exec($curlObj);
    if ($result == false) {
        $outData = array("status" => "ERROR", "info" => curl_error($curlObj));
    } else {
        $outData = json_decode($result, true);
    }
    // close curl resource
    curl_close($curlObj);
    if (isset($outData['info']) && $outData['info'] == 'EXPIRED' && $mail == null) {
        unset($_SESSION);
        session_destroy();
        header('Location: home.php');
        exit;
    } else {
        return ($outData);
    }
}

$postData = array(
        "_id" => APIUSER,
        "_key" => APIKEY
    );

$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL =>AIB_SERVICE_URL."/api/session.php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($postData)
	);

	curl_setopt_array($CurlObj,$Options);
	 $Result = curl_exec($CurlObj);
	if ($Result == false)
	{
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
	}
	else
	{
		$OutData = json_decode($Result,true);
	}

	curl_close($CurlObj);

//print_R($OutData);die;
 echo $sessionKey = $OutData['info'];

//Fix start for Issue ID 2147 on 28-Apr-2023
$PostData = array(
        '_key' => APIKEY,
        '_user' => 1,
        '_op' => 'get_item_prop',
        '_session' => $sessionKey,
        'obj_id' => 1
    );
$artifacts_time=7;
$Result = aibServiceRequest($PostData, "browse");
if ($Result['status'] == 'OK') {
	$artifacts_time= $Result['info']['records']['artifacts_time'];
}
$artifacts_time=7;

       
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

		// get all knowledge base list
		$postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_user" => 1,
            "_op" => "list",
            "parent" => 1,
            "opt_get_property" => 'Y'
        );
        $apiResponseKBList = aibServiceRequest($postData, 'browse');
// echo '<pre>';print_R( $apiResponseTrendingData);die;
		$itemStatusMap = [];
		if (!empty($apiResponseKBList['info']['records'])) {
			foreach ($apiResponseKBList['info']['records'] as $record) {
				// If 'status' key is missing, default to 0
				$status = $record['properties']['status'] ?? 0;
				$itemId = $record['item_id'] ?? null;
				if ($itemId !== null) {
					$itemStatusMap[$itemId] = $status;
				}
			}
		}
// echo '<pre>';print_R( $itemStatusMap);die;
		/*************** remove duplicate and last_viewed_date older then artifacts_time  ************************/
		$trandingItemids=array();
if(isset($apiResponseTrendingData['info']['records']) and count($apiResponseTrendingData['info']['records'])){
		/*<!-- Fix start Issue Id 2380 02-May-2024 -->*/
			if(count($apiResponseTrendingData['info']['records']) >16){
				/*<!-- Fix End Issue Id 2380 02-May-2024 -->*/
			foreach($apiResponseTrendingData['info']['records'] as $trendingData){
				$last_viewed_date=$trendingData['properties']['last_viewed_date'];
				if(in_array($trendingData['properties']['record_id'].$trendingData['properties']['s_item_id'],$trandingItemids) || strtotime($last_viewed_date) < strtotime('-'.$artifacts_time.' days'))	{
					$postDataDeleteItem = array(
						"_key" => APIKEY,
						"_session" => $sessionKey,
						"_user" => 1,
						"_op" => "delete_item",
					 "obj_id" => $trendingData['item_id']


        				);
					//echo '==Del'.$trendingData['item_id'];
					 $apiResponseDeleteData = aibServiceRequest($postDataDeleteItem, 'browse');
				}
				else{
					
					$trandingItemids[]=$trendingData['properties']['record_id'].$trendingData['properties']['s_item_id'];
				}
			}	
			}
}
	//die;	/********************************************************/
	$tradingItems=array();
	$tradingItemsMoreThan100=array();
		$dispLayTrendingItemArray=array();
        $apiResponseTrendingData = aibServiceRequest($postDataItem, 'browse');

	if(isset($apiResponseTrendingData['info']['records']) and count($apiResponseTrendingData['info']['records'])){
		foreach($apiResponseTrendingData['info']['records'] as $key=>$trendingData){
			$sItemId = $trendingData['properties']['s_item_id'] ?? null;
			// Only keep trending data if s_item_id matches an item_id with status=1
			if ($sItemId !== null && isset($itemStatusMap[$sItemId]) && $itemStatusMap[$sItemId] == 1) {
					$item=array();
					$item['record_id']=$trendingData['properties']['record_id'];
					$item['viewed_item_id']=$trendingData['properties']['viewed_item_id'];
					$item['view_count_record']=$trendingData['properties']['view_count_record'];
					$item['last_viewed_date']=$trendingData['properties']['last_viewed_date'];
					$item['s_item_title']=$trendingData['properties']['s_item_title'];
					$item['s_item_id']=$trendingData['properties']['s_item_id'];
					if($trendingData['properties']['view_count_record']>99){
						$tradingItemsMoreThan100[]=$item;
					}
					$tradingItems[]=$item;
				}
			}
	}
//Fix start for Issue ID 0002422 on 14-Feb-2025
	if(count($tradingItemsMoreThan100)>199){
		array_multisort(array_column($tradingItemsMoreThan100, 'last_viewed_date'), SORT_DESC, $tradingItemsMoreThan100);	
		$tradingItems = array_slice($tradingItemsMoreThan100,0,2000);


	}	
	else{	
		array_multisort(array_column($tradingItems, 'view_count_record'), SORT_DESC, $tradingItems);
		$tradingItems = array_slice($tradingItems,0,2000);

	}

shuffle($tradingItems);
$tradingItemFinal=array();
$tradingItemFinalSSCount=array();
		if(isset($tradingItems) and count($tradingItems)){
			foreach($tradingItems as $key=>$trendingData){
				if(count($tradingItemFinal)<2001){
					$count=1;
					if(isset($tradingItemFinalSSCount[$trendingData['s_item_id']])){
						$count=$tradingItemFinalSSCount[$trendingData['s_item_id']]+1;
						
						
					}
					
					$tradingItemFinalSSCount[$trendingData['s_item_id']]=$count;
					//if($count<30){
						$recInfo=array();
							$recInfo['record_id']=$trendingData['record_id'];
							$recInfo['viewed_item_id']=$trendingData['viewed_item_id'];
					$recInfo['view_count_record']=$trendingData['view_count_record'];
							$tradingItemFinal[]=$recInfo;
							
						
						
					//	}
					
					

					}
				
			}
			//echo '<pre>';print_R( $tradingItemFinal);die;
			foreach($tradingItemFinal as $trendingId){
				
				$postDataItem = array(
				"_key" => APIKEY,
				"_session" => $sessionKey,
				"_user" => 1,
				"_op" => "list",
				"parent" => $trendingId['record_id'],
				"opt_get_files" =>'Y',
					
				);
				$apiResponseTrendingThumb = aibServiceRequest($postDataItem, 'browse');
				//echo $trendingId.'<pre>';print_R($apiResponseTrendingThumb);	echo '</pre>';
				$TrendingItem=array();
				$TrendingFiles=array();
				foreach($apiResponseTrendingThumb['info']['records'] as $trendingItem){
					
					if($trendingId['viewed_item_id']==''){
						$trendingId['viewed_item_id']=$trendingId['record_id']+1;
					}
					//echo  '<br>'. $trendingId['record_id'].'---'.$trendingId['viewed_item_id'];
					if(count($trendingItem['files']) and $trendingItem['item_id']==$trendingId['viewed_item_id']){
						
						foreach($trendingItem['files'] as $files){
							
							if($files['file_type']=='tn' and !in_array($files['file_id'],$TrendingFiles))
							{
								$TrendingItem['folder_id']=$trendingId['record_id'];
								$TrendingItem['viewed_item_id']=$trendingId['viewed_item_id'];
								$TrendingItem['view_count_record']=$trendingId['view_count_record'];
								$TrendingItem['file_id']=$files['file_id'];
								$TrendingFiles[]=$files['file_id'];
								
								//Fix start for Issue ID 2302 on 11-Oct-2023-->
								$apiRequestData = array(
									'_key' => APIKEY,
									'_user' => 1,
									'_op' => 'get_path',
									'_session' => $sessionKey,
									//Fix start for Issue ID 2409 on 26-Dec-2024-->
									'obj_id' => $trendingId['record_id'],
									//Fix End for Issue ID 2409 on 26-Dec-2024-->
									'opt_get_property'=>'Y'
								);
								$apiResponsePath = aibServiceRequest($apiRequestData, 'browse');
								//echo '<pre>===';
								//print_R($apiResponsePath['info']['records'][1]);die;
								$TrendingItem['soc_id']=$apiResponsePath['info']['records'][1]['item_id'];
								$TrendingItem['soc_title']=$apiResponsePath['info']['records'][1]['item_title'];
								if(isset($apiResponsePath['info']['records'][count($apiResponsePath['info']['records'])-1]['properties']['itemrecord_address_state']) and isset($apiResponsePath['info']['records'][count($apiResponsePath['info']['records'])-1]['properties']['itemrecord_address_city']) and trim($apiResponsePath['info']['records'][count($apiResponsePath['info']['records'])-1]['properties']['itemrecord_address_state'])!='' and trim($apiResponsePath['info']['records'][count($apiResponsePath['info']['records'])-1]['properties']['itemrecord_address_city'])!=''){
									
									$TrendingItem['state']=trim($apiResponsePath['info']['records'][count($apiResponsePath['info']['records'])-1]['properties']['itemrecord_address_state']);
									$TrendingItem['city']=trim($apiResponsePath['info']['records'][count($apiResponsePath['info']['records'])-1]['properties']['itemrecord_address_city']);
								}
								elseif(isset($apiResponsePath['info']['records'][1]['properties']['archive_display_state']) and isset($apiResponsePath['info']['records'][1]['properties']['archive_display_city']) and trim($apiResponsePath['info']['records'][1]['properties']['archive_display_state'])!='' and trim($apiResponsePath['info']['records'][1]['properties']['archive_display_city'])!=''){
									
									$TrendingItem['state']=trim($apiResponsePath['info']['records'][1]['properties']['archive_display_state']);
									$TrendingItem['city']=trim($apiResponsePath['info']['records'][1]['properties']['archive_display_city']);
								}
								else{
									
									$TrendingItem['state']='NA';
									$TrendingItem['city']='NA';
								}

//Fix End for Issue ID 2302 on 11-Oct-2023-->

					//echo '<pre>';print_R($apiResponse['info']['records'][count($apiResponse['info']['records'])-1]);die;
								
								
							}
							
						}
						
						break;
						
					}
					
				}
				
				if(count($TrendingItem)){
				$dispLayTrendingItemArray[]=$TrendingItem;
					
					}
				
			}
			
			
			
		}
//echo '<pre>==';print_R($dispLayTrendingItemArray);

//Fix end for Issue ID 0002422 on 14-Feb-2025
	$storedData['timestamp']=date("Y-m-d H:i:s");
	$storedData['tradingItems']=$dispLayTrendingItemArray;
	file_put_contents($file_path_trading_data,json_encode($storedData, JSON_PRETTY_PRINT));	
//Fix end for Issue ID 2147 on 28-Apr-2023



?>