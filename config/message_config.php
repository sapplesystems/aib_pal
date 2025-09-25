<?php
/***************************Start Code change for Bug ID 1995/ Dated: 12-Sep-2022*****************************************/
if (defined("AIB_MESSAGE_CONFIG") == false)
{
	define('AIB_MESSAGE_CONFIG',true);
	/***************************End Code change for Bug ID 1995/ Dated: 12-Sep-2022*****************************************/
$message = [
    'MISSINGKEY' => 'No API key available',
    'MISSINGSESSION' => 'Invalid session key used',
    'NOHOST' => 'No host is selected',
    'NOOP' => 'Operation code missing',
    'HOSTNOTALLOWED' => 'Host not allowed',
    'SESSIONKEYQUERYERROR' => 'Error requesting session key information',
    'MISSINGUSERLOGIN' => 'User login is missing',
    'MISSINGUSERPASS'  => 'User password is missing',
    'MISSINGUSERTITLE' => 'User title is missing',
    'CANNOTCREATE: DUPLOGIN' => 'User login already in used',
    'BADLOGINORPASS' => 'Invalid login or password'
];
define('MESSAGE', json_encode($message));

function encryptQueryString($string = null){
    $key = hash(HASH, SECRET_KEY);
    $iv = substr(hash(HASH, SECRET_IV), 0, 16);
    if($string != null){
        $encrypted_string=openssl_encrypt($string, ENCRYPT_METHOD, $key, 0, $iv);
        return base64_encode($encrypted_string);
    }
}

function decryptQueryString($string = null){
    if($string != null){
        $key = hash(HASH, SECRET_KEY);
        $iv = substr(hash(HASH, SECRET_IV), 0, 16);
        $decrypted_string = openssl_decrypt(base64_decode($string), ENCRYPT_METHOD, $key, 0, $iv);
        $queryStringArray = explode('&',$decrypted_string);
        $parametersArray = array();
        if(!empty($queryStringArray)){
            foreach($queryStringArray as $dataArray){
                $textArray = explode('=',$dataArray);
                if(isset($textArray[0])){
                    $parametersArray[$textArray[0]] = $textArray[1];
                }
            }
        }
        return $parametersArray;
    }
}
}
if(isset($_REQUEST['q'])){
    $key = hash(HASH, SECRET_KEY);
    $iv = substr(hash(HASH, SECRET_IV), 0, 16);
    $decrypted_string = openssl_decrypt(base64_decode($_REQUEST['q']), ENCRYPT_METHOD, $key, 0, $iv);
    $queryStringArray = explode('&',$decrypted_string);
    if(!empty($queryStringArray)){
        foreach($queryStringArray as $dataArray){
            $textArray = explode('=',$dataArray);
            if(isset($textArray[0])){
                $_REQUEST[$textArray[0]] = $textArray[1];
            }
        }
    }
}

function is_base64_encoded($str) {
    if (empty($str) || strlen($str) % 4 !== 0) {
        return false;
    }
    if (!preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $str)) {
        return false;
    }

    // Decode and re-encode to verify
    $decoded = base64_decode($str, true);
    if ($decoded === false) {
        return false;
    }

    return base64_encode($decoded) === $str;
}