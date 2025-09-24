<?php
namespace classes;

require_once 'config/autoload.php';

use classes\database;
use classes\passwordHash;
use PDO;
use classes\siteUtility;

class loginController {
    private $dbConnection;
    private $dbObject;
    private $useTable = 'esign_admins';
    public function __construct() {
        $dbConnection = new database();
        $this->dbObject = $dbConnection;
        $this->dbConnection = $dbConnection->connectDB();
    }
    
    public function loginUser($dataArray = []){
        $response = ['msg'=>'Something went wrong, Please try again','status'=>0];
        if(!empty($dataArray)){
            $dataArray         = $this->dbObject->santnizeArray($dataArray);
            $userEmail         = $dataArray['user_email'];
            $userPassword      = $dataArray['user_password'];
            $stmt              = $this->dbConnection->prepare("select * from $this->useTable where email=:email");
            $stmt->bindParam(':email', $userEmail);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($result)){
                $dbPassword = $result[0]['password'];
                if(passwordHash::matchPassword($userPassword,$dbPassword)){
                    siteUtility::sessionWrite('user', $result[0]);
                    $response = ['msg'=>'Validated successfully.','status'=>1];
                }else{
                    $response['msg'] = 'Invalid password.';
                }
            }else{
                $response['msg'] = 'Entered emailId not exists.';
            }
        }
        return siteUtility::jsonEncode($response);
    }
    
    public function logOutUser(){
        return siteUtility::logOut('user');
    }
}
