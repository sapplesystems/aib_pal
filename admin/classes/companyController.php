<?php

namespace classes;

require_once 'config/autoload.php';

use classes\database;
use classes\passwordHash;
use PDO;
use classes\siteUtility;

class companyController {
    
    private $dbConnection;
    private $dbObject;
    private $useTable = 'esign_companies';
    private $refTable = 'esign_company_tokens';
    private $dataTable= 'esign_companies_data';
    public function __construct() {
        $dbConnection = new database();
        $this->dbObject = $dbConnection;
        $this->dbConnection = $dbConnection->connectDB();
    }
    
    public function saveCompany($dataArray = []){
        if(!empty($dataArray)){
            $dataArray  = $this->dbObject->santnizeArray($dataArray);
            $saveData = $this->dbConnection->exec("insert into $this->useTable set name='".$dataArray['company_name']."', email='".$dataArray['company_email']."', phone='".$dataArray['company_phone']."', address='".$dataArray['company_address']."'");
        }
    }
    
    public function getAllCompany(){
        $stmt = $this->dbConnection->prepare("select * from $this->useTable order by id desc");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function changeStatus($id = '', $status= ''){
        if($id !='' && $status !=''){
            if($status == 1)
                $status = 0;
            else
                $status = 1;
            $stmt = $this->dbConnection->prepare("update $this->useTable set is_active = :status where id=:company_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':company_id', $id);
            $stmt->execute();
        }
    }
    
    public function deleteCompany($id=''){
        if($id != ''){
            $stmt = $this->dbConnection->prepare("delete from $this->useTable where id=:company_id");
            $stmt->bindParam(':company_id', $id);
            $stmt->execute();
        }
    }
    
    public function getCompanyById($id= ''){
        if(is_numeric($id)){
            $stmt = $this->dbConnection->prepare("select * from $this->useTable where id=:company_id");
            $stmt->bindParam(':company_id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->dbObject->santnizeArray($result);
        }
    }
    
    public function updateCompany($dataArray = []){
        if(!empty($dataArray)){
            $dataArray  = $this->dbObject->santnizeArray($dataArray);
            $stmt = $this->dbConnection->prepare("update $this->useTable set name='".$dataArray['company_name']."', email='".$dataArray['company_email']."', phone='".$dataArray['company_phone']."', address='".$dataArray['company_address']."' where id=:company_id");
            $stmt->bindParam(':company_id', $dataArray['company_id']);
            $stmt->execute();
        }
    }
    
    public function getCompanyToken($id = ''){
        if($id != ''){
            $stmt = $this->dbConnection->prepare("select c.name,c.email,c.phone,ct.* from $this->refTable as ct inner join $this->useTable as c on c.id=ct.company_id where ct.company_id=:company_id order by ct.id desc");
            $stmt->bindParam(':company_id', $id);
            $stmt->execute();
            return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    public function createCompanyToken($dataArray = []){
        if(!empty($dataArray)){
            if($this->makeAllTokenDeactive($dataArray['company_id'])){
                $this->saveNewToken($dataArray);
            }
        }
    }
    
    private function makeAllTokenDeactive($id = ''){
        if(is_numeric($id)){
            $stmt = $this->dbConnection->prepare("update $this->refTable set is_active=0 where company_id=:company_id");
            $stmt->bindParam(':company_id', $id);
            $stmt->execute();
            return true;
        }
    }
    
    private function saveNewToken($dataArray = []){
        if(!empty($dataArray)){
            $dataArray  = $this->dbObject->santnizeArray($dataArray);
            $authenticationToken = passwordHash::generateRandomToken();
            $date = date('Y-m-d h:i:s');
            $saveData = $this->dbConnection->exec("insert into $this->refTable set company_id='".$dataArray['company_id']."', token='".$authenticationToken."', is_active=1, created='".$date."', modified='".$date."',expired_at='".$dataArray['expiry_date']."',token_purpose='".$dataArray['token_purpose']."'");
            return true;
        }
    }
    
    public function validateByToken($authenticationToken = ''){
        if($authenticationToken != ''){
            $stmt = $this->dbConnection->prepare("SELECT ct.company_id FROM $this->refTable AS ct INNER JOIN $this->useTable c ON c.id=ct.company_id WHERE ct.token=:authToken AND ct.is_active=1 AND c.`is_active`=1 AND ct.expired_at >= CURDATE()");
            $stmt->bindParam(':authToken', $authenticationToken);
            $stmt->execute();
            return $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function setRequestedData($dataArray = [], $company_id = ''){
        if(!empty($dataArray) && $company_id !=''){
            $dataArray  = $this->dbObject->santnizeArray($dataArray);
            $date = date('Y-m-d h:i:s');
            $saveData = $this->dbConnection->exec("insert into $this->dataTable set company_id='".$company_id."', requested_data='".json_encode($dataArray)."', s_url='".$dataArray['SUrl']."', f_url='".$dataArray['FUrl']."', c_url='".$dataArray['CUrl']."',created='".$date."', modified='".$date."', ip_address='".$dataArray['ip_address']."', latitude='".$dataArray['latitude']."', longitude='".$dataArray['longitude']."', city='".$dataArray['city']."', state='".$dataArray['state']."', country='".$dataArray['country']."'");
            $insertedId = $this->dbConnection->lastInsertId();
            $refranceNo = time().sprintf('%03d',$company_id).sprintf('%05d',$insertedId);
            $stmt = $this->dbConnection->prepare("update $this->dataTable set reference_number='".$refranceNo."' where id=:data_id");
            $stmt->bindParam(':data_id', $insertedId);
            $stmt->execute();
            return ['data_id'=>$insertedId,'ref_num'=>$refranceNo];
        }
    }
    
    public function getCompanyRequestedData($dataId = ''){
        if($dataId !=''){
            $stmt = $this->dbConnection->prepare("SELECT * from $this->dataTable where md5(id)=:data_id");
            $stmt->bindParam(':data_id', $dataId);
            $stmt->execute();
            return $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function updateCompanyResponsedData($dataArray = [], $dataId = ''){
        if(!empty($dataArray) && $dataId != ''){
            $status = $dataArray['type'];
            unset($dataArray['type']);
            unset($dataArray['data_id']);
            $date = date('Y-m-d h:i:s');
            $stmt = $this->dbConnection->prepare("update $this->dataTable set responsed_data='".json_encode($dataArray)."',modified='".$date."',response_status='".$status."' where md5(id)=:data_id");
            $stmt->bindParam(':data_id', $dataId);
            $stmt->execute();
            return true;
        }
    }
    
    public function getHeaderData(){
        $totalCompany           = $this->getAllCompany();
        $totalActiveComp        = $this->getAllActiveCompany();
        $totalRequest           = $this->getTotalRequest();
        $totalTodayRequest      = $this->getTodayAllRequest();
        $totalSuccessRequest    = $this->getTotalSuccessRequest();
        $totalcancelRequest     = $this->getTotalCancelRequest();
        $totalFailRequest       = $this->getTotalFailRequest();
        return [
            'total_company'         =>count($totalCompany),
            'total_active_company'  =>count($totalActiveComp),
            'total_request'         =>count($totalRequest),
            'total_today_request'   =>count($totalTodayRequest),
            'total_success_request' =>count($totalSuccessRequest),
            'total_cancel_request'  =>count($totalcancelRequest),
            'total_fail_request'    =>count($totalFailRequest)
        ];
    }
    
    public function getAllActiveCompany(){
        $stmt = $this->dbConnection->prepare("select * from $this->useTable where is_active=1");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalRequest(){
        $stmt = $this->dbConnection->prepare("select * from $this->dataTable");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTodayAllRequest(){
        $stmt = $this->dbConnection->prepare("select * from $this->dataTable where created > CURDATE()");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalSuccessRequest(){
        $stmt = $this->dbConnection->prepare("select * from $this->dataTable where response_status='success'");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalCancelRequest(){
        $stmt = $this->dbConnection->prepare("select * from $this->dataTable where response_status='cancel'");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalFailRequest(){
        $stmt = $this->dbConnection->prepare("select * from $this->dataTable where response_status='fail'");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function checkCompanyEmail($email = '', $companyId = ''){
        $conditions = "where 1=1";
        if($email != ''){
            $conditions .= " and email='".$email."'";
        }
        if($companyId != ''){
            $conditions .= " and id!='".$companyId."'";
        }
        $stmt = $this->dbConnection->prepare("select count(*) as total from $this->useTable $conditions");
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function checkCompanyName($name = '', $companyId = ''){
        $conditions = "where 1=1";
        if($name != ''){
            $conditions .= " and name='".$name."'";
        }
        if($companyId != ''){
            $conditions .= " and id!='".$companyId."'";
        }
        $stmt = $this->dbConnection->prepare("select count(*) as total from $this->useTable $conditions");
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function changeTokenStatus($token_id = '',$token_status = '', $company_id = ''){
        if($token_id !='' && $token_status != ''){
            $this->makeAllTokenDeactive($company_id);
            if($token_status == 1)
                $token_status = 0;
            else
                $token_status = 1;
            $stmt = $this->dbConnection->prepare("update $this->refTable set is_active='".$token_status."' where id=:token_id");
            $stmt->bindParam(':token_id', $token_id);
            $stmt->execute();
            return true;
        }
    }
    
    public function getAllCompanyRequest($conditions){
        $stmt = $this->dbConnection->prepare("SELECT cd.created,cd.ip_address,cd.latitude,cd.longitude,cd.city,cd.state,cd.country,cd.reference_number,cd.response_status,c.name FROM $this->dataTable AS cd INNER JOIN $this->useTable c ON c.`id` = cd.company_id $conditions");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllCompanyList(){
        $stmt = $this->dbConnection->prepare("select id,name from $this->useTable order by name asc");
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
