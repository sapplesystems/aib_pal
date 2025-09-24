<?php
if (defined("AIB_ADMIN_MESSAGE_INCLUDE") == false)
{
	define('AIB_ADMIN_MESSAGE_INCLUDE',true);
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
}
/********* fix start Bug id 2359 23-June-2204 ****************/
function encryptQueryString($string = null){
    $key = hash(HASH, SECRET_KEY);
    $iv = substr(hash(HASH, SECRET_IV), 0, 16);
    if($string != null){
        $encrypted_string=openssl_encrypt($string, ENCRYPT_METHOD, $key, 0, $iv);
        return base64_encode($encrypted_string);
    }
}
/********* fix end Bug id 2359 23-June-2204 ****************/