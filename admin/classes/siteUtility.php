<?php

namespace classes;

class siteUtility {
    
    static function jsonEncode($dataArray = []){
        if(!empty($dataArray)){
            return json_encode($dataArray);
        }
    }
    
    static function jsonDecode($string = ''){
        if($string !=''){
            return json_decode($string);
        }
    }
    
    static function sessionWrite($key ='' ,$array = []){
        if(!empty($key)){
            $_SESSION[$key] = $array;
        }
    }
    
    static function logOut($key = ''){
        if($key !='')
            unset($_SESSION[$key]);
        else 
            unset($_SESSION);
        session_destroy();
        return true;
    }
    
    public static function cleanDataArray($dataArray = []){
        if(!empty($dataArray)){
            $dataArray = array_map('trim',$dataArray);
            return $dataArray;
        }
    }
}
