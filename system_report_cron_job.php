<?php
require_once dirname(__FILE__) . '/config/config.php'; 
$out=exec('sh /home/devteam/sapplescripts/getsysteminfo.sh');

$fp='systeminfo-'.date('m-d-Y').'.txt';
echo "/home/devteam/sapplescripts/logs/".$fp;


$handle = fopen("/home/devteam/sapplescripts/logs/".$fp, "r");
$report='';
if ($handle) {
    while (($line = fgets($handle)) !== false) {
       $report=$report.$line.'<br>';
    }

    fclose($handle);
}
$postData = array(
        "_id" => APIUSER,
        "_key" => APIKEY
    );
    $apiResponse = aibServiceRequest($postData, 'session');
    if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
        $sessionKey = $apiResponse['info'];
    }

       
        $template ='Hi Team,<br><br> Please review the below system reprot.<br><br><br><br>'.$report;
        // Request array to send email       
//$to[]='surya@sapple.co.in/sury';
$to[]='pphukan@sapple.co.in/parag';
$to[]='cody@smalltownpapers.com/cody';
$to[]='abhinav.anand@sapple.co.in/abhinav';
$to[]='meadway@smalltownpapers.com/mike';
$to=implode(',',$to);
        $postData = array(
            "_key" => APIKEY,
            "_session" => $sessionKey,
            "_op" => "send",
            "_user" => 1,
            "to" => $to,
            "from" => 'admin@archiveinabox.com',
            "reply" => 'admin@archiveinabox.com',
            "subject" => 'AIB-System Report-'.date('m-d-Y'),
            "body" => $template,
            "is_html" => 'Y'
        );
        // Service request to send email 
        $apiResponse = aibServiceRequest($postData, 'email', 'send');
print_R($apiResponse );
        if($apiResponse['status'] == 'OK'){
			echo 'sent report';
		}else{
			echo 'sent report failed';
		}
    


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
?>
