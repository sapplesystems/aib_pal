<?php


$file_path_trading_data=AIB_USER_TMP.'displayTrendingItem_cron.txt';
if(file($file_path_trading_data)){
	$data = json_decode(file_get_contents($file_path_trading_data), true);
	/***** Hotpatch start change to update cache file 09-12-2024********/
	$timestamp=strtotime($data['timestamp'])+ (4 * 3600);
	/***** Hotpatch End change to update cache file 09-12-2024********/
	
}

 $tradingItems=$data['tradingItems'];
shuffle($tradingItems);
$dispLayTrendingItemArray=$tradingItems;

if (isset($data['timestamp']) && ($timestamp > time())) {
    
}
else{
	
	$url = HOST_PATH.'/trending_data_generator__cron.php';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // 100ms timeout
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['key' => 'value']));
curl_exec($ch);
curl_close($ch);
}

?>