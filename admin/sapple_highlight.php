<?php
define('APIKEY',"87fc0d6d9689d84ab48f583175f9522dg");
require_once dirname(__FILE__) . '/config/aib.php';

// Function to call server
// -----------------------
function aib_request($LocalPostData,$FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "http://develop.archiveinabox.com:80/api/".$FunctionSet.".php",
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

// #########
// MAIN CODE
// #########

	// Generate key

	$PostData = array(
		"_id" => "test",
		"_key" => APIKEY,
		"_user" => 1,
	);

	// Make AIB request

	$Result = aib_request($PostData,"session");

	// Check for request errors

	if ($Result["status"] != "OK")
	{
		print("ERROR: Cannot get session key; ".$Result["info"]."\n");
		exit(0);
	}

	$SessionKey = $Result["info"];
	$FormData = array(
		"_key" => APIKEY,
		"_session" => $SessionKey,
		"_op" => "highlights",
		"_user" => 1,
		"obj_id" => "10479",
		"word_list" => "little",
		"display_width" => 0,			// Use stored size
		"display_height" => 480,
		"file_class" => "pr"
		);
	$Response = aib_request($FormData,"generate_highlight_overlay");
	/*echo '<pre>';
	print_R($FormData );
	print_R($Response );die;*/
	$info     = json_decode($Response['info'],true);
	//echo "<pre>"; print_r($info); echo "</pre>"; exit;
	/*$img      = imagecreatefromjpeg("test-image.jpg");
	$yellow   = imagecolorallocatealpha($img, 255, 210, 10, 90);
	// foreach($info['rect'] as $imageDataHeighLiter){
		// imagefilledrectangle($img, imageDataHeighLiter['x'], imageDataHeighLiter['y'], imageDataHeighLiter['x1'], imageDataHeighLiter['y1'], $yellow);
	// }
	//imagefilledrectangle($img,  $info['rect'][0]['x'], $info['rect'][0]['y'], $info['rect'][0]['x1'], $info['rect'][0]['y1'], $yellow);
	//imagefilledrectangle($img,  $info['rect'][1]['x'], $info['rect'][1]['y'], $info['rect'][1]['x1'], $info['rect'][1]['y1'], $yellow);
	//header('Content-Type: image/jpeg');
	imagejpeg($img, 'test-image_new.jpg');
	imagedestroy($img);*/
?>
<html>
	<head><title>Image Highlighter</title></head>
	<body style="margin:0px !important">
    <table width="500px">
    <tr><td>brijesh</td>
    <td>
    <div style=" position:relative;width:900px;height:1276px;">
		<img src="http://develop.archiveinabox.com/get_thumb.php?id=7903" alt="" style="height:480px" /> 
        <?php
		foreach($info['rect'] as $imageDataHeighLiter){
		echo '<div id="hlbox_0" style="position:absolute; left:'.($imageDataHeighLiter['x']).'px; top:'.($imageDataHeighLiter['y']).'px; width:'.($imageDataHeighLiter['w']).'px; height:'.($imageDataHeighLiter['h']).'px; background-color:#ffff00; opacity:0.3; outline:0.5px solid red;"></div>';
	 }
		?></div></td></tr></table>
        
	</body>
</html>
