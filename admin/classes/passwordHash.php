<?php

namespace classes;

class passwordHash {
    private static $options = ['cost' => 12];
    static function encryptPassword($password = null){
        if($password != null){
            return password_hash($password, PASSWORD_BCRYPT, self::$options);
        }
    }
    
    static function matchPassword($userPassword = '', $dbPassword =''){
        if($userPassword !='' && $dbPassword!=''){
            if(password_verify($userPassword, $dbPassword) == false){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    
    static function generateRandomToken(){
        return $token = bin2hex(openssl_random_pseudo_bytes(24));
    }
}
