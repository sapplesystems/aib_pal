<?php
die;
define('APIKEY', "93167f8656d6cd7c211e7efdfe6e5d4d");
ini_set('max_execution_time',1000);
// Function to call server
// -----------------------
function aibServiceRequest($LocalPostData, $FunctionSet)
{
	$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "https://www.archiveinabox.com/api/" . $FunctionSet . ".php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
	);

	curl_setopt_array($CurlObj, $Options);
	$Result = curl_exec($CurlObj);
	if ($Result == false) {
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
	} else {
		$OutData = json_decode($Result, true);
	}

	curl_close($CurlObj);
	return ($OutData);
}

$PostData = array(
	"_id" => "test",
	"_key" => APIKEY,
	"_user" => 1,
);

// Make AIB request

$Result = aibServiceRequest($PostData, "session");

// Check for request errors

if ($Result["status"] != "OK") {
	print("ERROR: Cannot get session key; " . $Result["info"] . "\n");
	exit(0);
}

$sessionKey = $Result["info"];


function aib_request($sessionKey, $item_id)
{
	$postData = array(
		"_key" => APIKEY,
		"_session" => $sessionKey,
		"_user" => 1,
		"_op" => "list",
		"parent" => $item_id,
	);
	// Service request to get item tree data        
	$apiResponse = aibServiceRequest($postData, 'browse');
	//echo '<pre>';
	//print_r($postData);
	//print_r($apiResponse);

	static $ar_count = 0;
	static $co_count = 0;
	static $sg_count = 0;
	static $re_count = 0;
	static $it_count = 0;
	static $record_update_count = 0;
	static $sg_count_arr = array();
	static $re_count_arr = array();
	static $it_count_arr = array();
	if (!empty($apiResponse) && $apiResponse['status'] == 'OK') {
		foreach ($apiResponse['info']['records'] as $k1 => $v1) {
			$item_id = $v1['item_id'];
			if ($v1['item_type'] == 'AR') {
				$ar_count++;
			} else if ($v1['item_type'] == 'CO') {
				$co_count++;
			} else if ($v1['item_type'] == 'SG') {
				$sg_count++;
				$sg_count_arr[]= $item_id ;
				$postData = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $item_id,
                    'propname_1' => 'download_to_public_item',
                    'propval_1' => 1,
                );
				//print_r($postData );
                // Service request for set item property
                $apiResponse = aibServiceRequest($postData, 'browse');
				$record_update_count++;
				//print_r($apiResponse );
			} else if ($v1['item_type'] == 'RE') {
				$re_count++;
				$re_count_arr[]= $item_id ;
				$postData = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $item_id,
                    'propname_1' => 'enable_disable_download',
                    'propval_1' => 2,
                );
                // Service request for set item property
                $apiResponse = aibServiceRequest($postData, 'browse');
				$record_update_count++;
				//print_r($apiResponse );
			} else if ($v1['item_type'] == 'IT') {
				$it_count++;
				$it_count_arr[]= $item_id ;
				$postData = array(
                    '_key' => APIKEY,
                    '_user' => 1,
                    '_op' => 'set_item_prop',
                    '_session' => $sessionKey,
                    'obj_id' => $item_id,
                    'propname_1' => 'enable_disable_download',
                    'propval_1' => 2,
                );
                // Service request for set item property
                $apiResponse = aibServiceRequest($postData, 'browse');
				$record_update_count++;
			}
			//echo '<br>'.$item_id;	
			aib_request($sessionKey, $item_id);
		}
	}

	return [
		'ar_count' => $ar_count,
		'co_count' => $co_count,
		'sg_count' => $sg_count,
		're_count' => $re_count,
		'it_count' => $it_count,
		'record_update_count' => $record_update_count,
		////'sg_count_arr' => $sg_count_arr,
		//'re_count_arr' => $re_count_arr,
		//'it_count_arr' => $it_count_arr
	];
}


 //$result = aib_request($sessionKey, 539319);596134 
//$result = aib_request($sessionKey, 576950);  this can be run in single script[co_count] => 3     [sg_count] => 111     [re_count] => 761     [it_count] => 172    [record_update_count] => 1044

/**********  Historical Collections  596134 sub groups
653313   Done       [sg_count] => 1      [re_count] => 41     [it_count] => 4461
636710   Done    [sg_count] => 15     [re_count] => 1970     [it_count] => 3085
634836   Done   [sg_count] => 41     [re_count] => 222     [it_count] => 1842
653648	 Done   [sg_count] => 1     [re_count] => 4     [it_count] => 84     [record_update_count] => 89
656387
694479	 Done [sg_count] => 1     [re_count] => 2     [it_count] => 840     [record_update_count] => 843
781552   Done  [sg_count] => 165     [re_count] => 2068    [it_count] => 2955    [record_update_count] => 5188
695532	 Done   [sg_count] => 1     [re_count] => 6     [it_count] => 94     [record_update_count] => 101
695545	 Done     [sg_count] => 73     [re_count] => 1636     [it_count] => 1642     [record_update_count] => 3351
695404	 Done   [sg_count] => 2     [re_count] => 4     [it_count] => 412     [record_update_count] => 418
695463	 Done  [sg_count] => 1     [re_count] => 2     [it_count] => 244     [record_update_count] => 247
596135   Done   [sg_count] => 2003     [re_count] => 16586     [it_count] => 20214     [record_update_count] => 38803
695519	 Done  [sg_count] => 1     [re_count] => 2     [it_count] => 106     [record_update_count] => 109
642898	 Done    [sg_count] => 29     [re_count] => 268     [it_count] => 3219     [record_update_count] => 3516
698998	 Done [sg_count] => 1     [re_count] => 8     [it_count] => 154     [record_update_count] => 163
699030	 Done    [sg_count] => 604    [re_count] => 8760    [it_count] => 9077    [record_update_count] => 18441
747331   Done     [sg_count] => 49     [re_count] => 53     [it_count] => 1768     [record_update_count] => 1870
719676   Done    [sg_count] => 1    [re_count] => 4    [it_count] => 71    [record_update_count] => 
743177 Done  [sg_count] => 19     [re_count] => 19     [it_count] => 79     [record_update_count] => 117
719696 DOne  [sg_count] => 284     [re_count] => 5551     [it_count] => 11025     [record_update_count] => 16860
635335  Done      [sg_count] => 1    [re_count] => 17    [it_count] => 602    [record_update_count] => 620
721571  Done      [sg_count] => 1     [re_count] => 17     [it_count] => 602     [record_update_count] => 620
721626  Done  [sg_count] => 4    [re_count] => 293    [it_count] => 4295    [record_update_count] => 4592



656458
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 11
    [re_count] => 21
    [it_count] => 109
    [record_update_count] => 141
)

656498
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 25
    [re_count] => 80
    [it_count] => 255
    [record_update_count] => 360
)

656520
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 37
    [re_count] => 118
    [it_count] => 359
    [record_update_count] => 514
)

656556
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 64
    [re_count] => 390
    [it_count] => 706
    [record_update_count] => 1160
)

656581
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 80
    [re_count] => 717
    [it_count] => 1042
    [record_update_count] => 1839
)

656607
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 104
    [re_count] => 895
    [it_count] => 1389
    [record_update_count] => 2388
)

656633
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 129
    [re_count] => 1118
    [it_count] => 1652
    [record_update_count] => 2899
)

656655
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 154
    [re_count] => 1406
    [it_count] => 2039
    [record_update_count] => 3599
)

656667
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 197
    [re_count] => 1989
    [it_count] => 2674
    [record_update_count] => 4860
)

656685
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 223
    [re_count] => 2212
    [it_count] => 2897
    [record_update_count] => 5332
)

656698
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 252
    [re_count] => 2515
    [it_count] => 3286
    [record_update_count] => 6053
)

656711
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 276
    [re_count] => 2601
    [it_count] => 3676
    [record_update_count] => 6553
)

656724
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 302
    [re_count] => 2745
    [it_count] => 3997
    [record_update_count] => 7044
)

656735
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 346
    [re_count] => 2962
    [it_count] => 4527
    [record_update_count] => 7835
)

656745
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 371
    [re_count] => 3236
    [it_count] => 4801
    [record_update_count] => 8408
)

656760
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 417
    [re_count] => 3753
    [it_count] => 5465
    [record_update_count] => 9635
)

656775
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 435
    [re_count] => 3959
    [it_count] => 5752
    [record_update_count] => 10146
)

656788
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 509
    [re_count] => 4662
    [it_count] => 6578
    [record_update_count] => 11749
)

656801
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 569
    [re_count] => 5285
    [it_count] => 7382
    [record_update_count] => 13236
)

656814
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 647
    [re_count] => 6101
    [it_count] => 8346
    [record_update_count] => 15094
)

766375
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 654
    [re_count] => 6173
    [it_count] => 8418
    [record_update_count] => 15245
)

656827
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 3
    [re_count] => 14
    [it_count] => 25
    [record_update_count] => 42
)

656836
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 4
    [re_count] => 26
    [it_count] => 37
    [record_update_count] => 67
)

656849
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 18
    [re_count] => 139
    [it_count] => 165
    [record_update_count] => 322
)

656858
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 20
    [re_count] => 145
    [it_count] => 178
    [record_update_count] => 343
)

656877
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 39
    [re_count] => 294
    [it_count] => 352
    [record_update_count] => 685
)

656894
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 40
    [re_count] => 302
    [it_count] => 360
    [record_update_count] => 702
)

656905
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 49
    [re_count] => 346
    [it_count] => 428
    [record_update_count] => 823
)

656917
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 64
    [re_count] => 451
    [it_count] => 559
    [record_update_count] => 1074
)

656929
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 67
    [re_count] => 457
    [it_count] => 574
    [record_update_count] => 1098
)

656943
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 70
    [re_count] => 475
    [it_count] => 635
    [record_update_count] => 1180
)

656955
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 81
    [re_count] => 508
    [it_count] => 712
    [record_update_count] => 1301
)

656974
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 93
    [re_count] => 586
    [it_count] => 828
    [record_update_count] => 1507
)

656983
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 100
    [re_count] => 629
    [it_count] => 920
    [record_update_count] => 1649
)

657007
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 103
    [re_count] => 633
    [it_count] => 933
    [record_update_count] => 1669
)

657018
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 114
    [re_count] => 754
    [it_count] => 1067
    [record_update_count] => 1935
)

657027
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 159
    [re_count] => 1236
    [it_count] => 1624
    [record_update_count] => 3019
)

657035
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 186
    [re_count] => 1593
    [it_count] => 2034
    [record_update_count] => 3813
)

657047
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 260
    [re_count] => 2966
    [it_count] => 3510
    [record_update_count] => 6736
)

657069
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 284
    [re_count] => 3198
    [it_count] => 3786
    [record_update_count] => 7268
)

771188
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 285
    [re_count] => 3199
    [it_count] => 3787
    [record_update_count] => 7271
)

657077
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 340
    [re_count] => 3888
    [it_count] => 4633
    [record_update_count] => 8861
)

657089
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 5
    [re_count] => 38
    [it_count] => 80
    [record_update_count] => 123
)

657104
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 14
    [re_count] => 90
    [it_count] => 211
    [record_update_count] => 315
)

657116
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 28
    [re_count] => 253
    [it_count] => 388
    [record_update_count] => 669
)

773108
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 30
    [re_count] => 261
    [it_count] => 396
    [record_update_count] => 687
)

773127
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 31
    [re_count] => 262
    [it_count] => 397
    [record_update_count] => 690
)

657128
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 48
    [re_count] => 389
    [it_count] => 579
    [record_update_count] => 1016
)

657145
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 51
    [re_count] => 394
    [it_count] => 592
    [record_update_count] => 1037
)

773413
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 52
    [re_count] => 396
    [it_count] => 594
    [record_update_count] => 1042
)

657175
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 86
    [re_count] => 700
    [it_count] => 934
    [record_update_count] => 1720
)

657185
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 95
    [re_count] => 756
    [it_count] => 1015
    [record_update_count] => 1866
)

774109
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 96
    [re_count] => 756
    [it_count] => 1015
    [record_update_count] => 1867
)

774110
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 100
    [re_count] => 846
    [it_count] => 1105
    [record_update_count] => 2051
)

657195
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 108
    [re_count] => 891
    [it_count] => 1150
    [record_update_count] => 2149
)

657205
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 186
    [re_count] => 1676
    [it_count] => 2108
    [record_update_count] => 3970
)

657220
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 187
    [re_count] => 1677
    [it_count] => 2116
    [record_update_count] => 3980
)

657232
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 188
    [re_count] => 1678
    [it_count] => 2117
    [record_update_count] => 3983
)

657243
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 256
    [re_count] => 2559
    [it_count] => 3324
    [record_update_count] => 6139
)

657253
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 261
    [re_count] => 2569
    [it_count] => 3363
    [record_update_count] => 6193
)

657262
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 263
    [re_count] => 2580
    [it_count] => 3384
    [record_update_count] => 6227
)

657270
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 310
    [re_count] => 2944
    [it_count] => 3913
    [record_update_count] => 7167
)

657284
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 362
    [re_count] => 3575
    [it_count] => 5314
    [record_update_count] => 9251
)

657292
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 35
    [re_count] => 300
    [it_count] => 313
    [record_update_count] => 648
)

657304
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 39
    [re_count] => 352
    [it_count] => 365
    [record_update_count] => 756
)

657315
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 65
    [re_count] => 561
    [it_count] => 638
    [record_update_count] => 1264
)

657328
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 67
    [re_count] => 564
    [it_count] => 641
    [record_update_count] => 1272
)

657340
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 81
    [re_count] => 659
    [it_count] => 785
    [record_update_count] => 1525
)

657357
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 99
    [re_count] => 788
    [it_count] => 1009
    [record_update_count] => 1896
)

657373
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 138
    [re_count] => 984
    [it_count] => 1378
    [record_update_count] => 2500
)

657392
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 139
    [re_count] => 985
    [it_count] => 1398
    [record_update_count] => 2522
)

657404
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 197
    [re_count] => 1700
    [it_count] => 2390
    [record_update_count] => 4287
)

657431
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 200
    [re_count] => 1703
    [it_count] => 2414
    [record_update_count] => 4317
)

657443
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 297
    [re_count] => 3079
    [it_count] => 3901
    [record_update_count] => 7277
)

657453
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 301
    [re_count] => 3083
    [it_count] => 3919
    [record_update_count] => 7303
)

657462
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 338
    [re_count] => 3501
    [it_count] => 4396
    [record_update_count] => 8235
)

657474
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 349
    [re_count] => 3640
    [it_count] => 4581
    [record_update_count] => 8570
)

657482
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 350
    [re_count] => 3643
    [it_count] => 4584
    [record_update_count] => 8577
)

657493
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 358
    [re_count] => 3741
    [it_count] => 4692
    [record_update_count] => 8791
)

657500
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 359
    [re_count] => 3742
    [it_count] => 4693
    [record_update_count] => 8794
)

657516
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 360
    [re_count] => 3750
    [it_count] => 4719
    [record_update_count] => 8829
)

657530
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 361
    [re_count] => 3755
    [it_count] => 4724
    [record_update_count] => 8840
)

657539
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 363
    [re_count] => 3783
    [it_count] => 4763
    [record_update_count] => 8909
)

657548
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 391
    [re_count] => 3913
    [it_count] => 4912
    [record_update_count] => 9216
)

657561
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 2
    [re_count] => 13
    [it_count] => 26
    [record_update_count] => 41
)

683185
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 37
    [re_count] => 237
    [it_count] => 310
    [record_update_count] => 584
)

657584
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 41
    [re_count] => 259
    [it_count] => 372
    [record_update_count] => 672
)

657597
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 121
    [re_count] => 1019
    [it_count] => 1268
    [record_update_count] => 2408
)

657607
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 149
    [re_count] => 1370
    [it_count] => 1681
    [record_update_count] => 3200
)

657614
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 186
    [re_count] => 1980
    [it_count] => 2419
    [record_update_count] => 4585
)

657624
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 188
    [re_count] => 1985
    [it_count] => 2427
    [record_update_count] => 4600
)

657634
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 191
    [re_count] => 1992
    [it_count] => 2444
    [record_update_count] => 4627
)

657648
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 194
    [re_count] => 2005
    [it_count] => 2457
    [record_update_count] => 4656
)

657657
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 199
    [re_count] => 2026
    [it_count] => 2489
    [record_update_count] => 4714
)

657673
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 202
    [re_count] => 2038
    [it_count] => 2512
    [record_update_count] => 4752
)

657685
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 264
    [re_count] => 2647
    [it_count] => 3358
    [record_update_count] => 6269
)

657697
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 266
    [re_count] => 2656
    [it_count] => 3367
    [record_update_count] => 6289
)

657713
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 269
    [re_count] => 2683
    [it_count] => 3404
    [record_update_count] => 6356
)

657721
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 272
    [re_count] => 2694
    [it_count] => 3420
    [record_update_count] => 6386
)

657739
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 273
    [re_count] => 2695
    [it_count] => 3428
    [record_update_count] => 6396
)

657752
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 275
    [re_count] => 2697
    [it_count] => 3440
    [record_update_count] => 6412
)

657760
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 277
    [re_count] => 2713
    [it_count] => 3456
    [record_update_count] => 6446
)

657767
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 288
    [re_count] => 2799
    [it_count] => 3586
    [record_update_count] => 6673
)

657778
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 289
    [re_count] => 2807
    [it_count] => 3601
    [record_update_count] => 6697
)

657793
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 290
    [re_count] => 2808
    [it_count] => 3602
    [record_update_count] => 6700
)

657820
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 3
    [re_count] => 26
    [it_count] => 26
    [record_update_count] => 55
)

657831
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 6
    [re_count] => 37
    [it_count] => 47
    [record_update_count] => 90
)

657843
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 7
    [re_count] => 40
    [it_count] => 50
    [record_update_count] => 97
)

657871
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 16
    [re_count] => 95
    [it_count] => 132
    [record_update_count] => 243
)

657880
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 20
    [re_count] => 116
    [it_count] => 153
    [record_update_count] => 289
)

657893
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 52
    [re_count] => 419
    [it_count] => 508
    [record_update_count] => 979
)

657903
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 59
    [re_count] => 456
    [it_count] => 553
    [record_update_count] => 1068
)

657913
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 60
    [re_count] => 486
    [it_count] => 583
    [record_update_count] => 1129
)

657927
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 104
    [re_count] => 906
    [it_count] => 1098
    [record_update_count] => 2108
)

657976
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 111
    [re_count] => 971
    [it_count] => 1248
    [record_update_count] => 2330
)

657984
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 117
    [re_count] => 1015
    [it_count] => 1366
    [record_update_count] => 2498
)

657997
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 118
    [re_count] => 1016
    [it_count] => 1374
    [record_update_count] => 2508
)

658005
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 123
    [re_count] => 1086
    [it_count] => 1447
    [record_update_count] => 2656
)

658016
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 148
    [re_count] => 1351
    [it_count] => 1799
    [record_update_count] => 3298
)

658027
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 173
    [re_count] => 1577
    [it_count] => 2073
    [record_update_count] => 3823
)

658037
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 193
    [re_count] => 1759
    [it_count] => 2335
    [record_update_count] => 4287
)

658050
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 194
    [re_count] => 1771
    [it_count] => 2347
    [record_update_count] => 4312
)

658067
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 322
    [re_count] => 2816
    [it_count] => 3392
    [record_update_count] => 6530
)

672046
Array
(
    [ar_count] => 0
    [co_count] => 0
    [sg_count] => 323
    [re_count] => 2817
    [it_count] => 3393
    [record_update_count] => 6533
)
**********/

$arrSub=array('657820','657831','657843','657871','657880','657893','657903','657913','657927','657976','657984','657997','658005','658016','658027','658037','658050','658067','672046');
$count=0;
foreach($arrSub as $sub){
	if($count>20){
		
		//die;
	}
	echo '<br>'. $sub;
	$result = aib_request($sessionKey,  $sub);
 echo '<pre>';
 print_r($result);
	$count++;
	
}die;



