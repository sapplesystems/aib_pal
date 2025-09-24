<?php 
namespace classes;

use PDO;

class database{
    
    private $host ;
    private $db_name ;
    private $db_user;
    private $db_password;
    private $db_port;
    private $connection;
    
    /*
     *Constructor of the class 
     *Author: Sapple Systems
    */
    public function connectDB($host = '', $dbName = '', $dbPort = '', $dbUser = '', $dbPassword = ''){
        $this->host         = ($host != '')         ?$host      : DB_HOST;
        $this->db_name      = ($dbName != '')       ?$dbName    : DB_NAME;
        $this->db_port      = ($dbPort != '')       ?$dbPort    : DB_PORT;
        $this->db_user      = ($dbUser != '')       ?$dbUser    : DB_USER;
        $this->db_password  = ($dbPassword != '')   ?$dbPassword: DB_PASSWORD;
        try {
            $conn = new PDO("mysql:host=$this->host;dbname=$this->db_name;port=$this->db_port", $this->db_user, $this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection = $conn;
        }catch(PDOException $e){
            echo "Connection failed: " . $e->getMessage();
        }
        return $this->connection;
    }
    
    public function santnizeArray($dataArray = []){
        if(!empty($dataArray)){
            $dataArray = array_map('trim',$dataArray);
            return $dataArray;
        }
    }
    
}