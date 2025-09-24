<?php
	
	
	
require_once 'config/config.php';
 

################ function to call api #################
function aibServiceRequest($LocalPostData,$FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => AIB_SERVICE_URL."/api/".$FunctionSet.".php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
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
	return($OutData);
}
######################## END  #####################################

	############ Generate session key ##############
	$PostData = array(
		"_id" => "test",
		"_key" => APIKEY,
		"_user" => 1,
	); 
	$Result = aibServiceRequest($PostData,"session"); 
	if ($Result["status"] != "OK")
	{
		print("ERROR: Cannot get session key; ".$Result["info"]."\n");
		exit(0);
	}
	$sessionKey = $Result["info"];
	//	print("Session key = $sessionKey\n");
	
	##################################################

	?>

<html>
	<head> 
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	</head>
   <style>
  .container{
	  width:300;
	  height:150;
	  border:1px solid;
	  background-color:#ccffff;
	  
  }
  </style>

<body>
	<form name="import" method="post" enctype="multipart/form-data">
	<div class="container">
		<label>Import Excel file(CSV only.)</label>
		<div class="control-group">
			<div class="controls">
				<input type="file" name="file" id="file" class="input-large">
			</div>
		</div>
	 <br>
	<div class="control-group">
		<div class="controls">
		<button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Upload</button>
		</div>
	</div>
	</form>
<?php 
if(isset($_POST["Import"])){
	$file = $_FILES['file']['tmp_name'];
	$handle = fopen($file, "r");
	$row = 1;  
	while(($filesop = fgetcsv($handle, 1000, ",")) !== false){
		if($row == 3){ $row++; continue; }
		$num = count($filesop);
		if($filesop[0] != ''){ 
			$apiRequestData = array(
				"_key" => APIKEY,
				"_session" => $sessionKey,
				"_op" => 'create_item',
				"_user" => 1,
				"parent" =>  COUNTRY_PARENT_ID,   // STATE_PARENT_ID
				"item_title" => urlencode($filesop[1]) 
			);
			$apiResponse = aibServiceRequest($apiRequestData, 'browse');  
		   if ($apiResponse['status'] == 'OK') { 
				$archive_id = $apiResponse['info'];
				$aditionalProp['_key'] = APIKEY;
				$aditionalProp['_user'] = 1;
				$aditionalProp['_op'] = 'set_item_prop';
				$aditionalProp['_session'] = $sessionKey;
				$aditionalProp['obj_id'] = $archive_id;
				$aditionalProp['propname_1'] = 'short_name';
				$aditionalProp['propval_1'] = urlencode($filesop[0]);
			   $apiProResponse = aibServiceRequest($aditionalProp, 'browse');   
			}
		} 
	} 
}
?>
</div>
</body>
</html>
