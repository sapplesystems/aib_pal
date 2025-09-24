<?php
    require_once dirname(__FILE__). '/config/bootstrap.php';
    $companyObj  = new classes\companyController();
    $dataId = $_REQUEST['data_id'];
    $companyRequestData = $companyObj->getCompanyRequestedData($dataId);
    $updateResponseData = $companyObj->updateCompanyResponsedData($_REQUEST,$dataId);
    unset($_REQUEST['data_id']);
    $url = '';
    switch($_REQUEST['type']) {
        case 'success':
            $url = $companyRequestData['s_url'];
            break;
        case 'cancel':
             $url = $companyRequestData['c_url'];
            break;
        case 'fail':
             $url = $companyRequestData['f_url'];
            break;
    }
?>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script>
<form method="POST" name="responseForm" id="responseForm" action="<?php echo $url ?>">
    <input type="hidden" name="type" value='<?php echo $_REQUEST['type']; ?>' />
    <input type="hidden" name="Base64FileData" value='<?php echo $_REQUEST['Base64FileData']; ?>' />
    <input type="hidden" name="FileType" value='<?php echo $_REQUEST['FileType']; ?>' />
    <input type="hidden" name="ReferenceNumber" value='<?php echo $_REQUEST['ReferenceNumber']; ?>' />
</form>
<script type = "text/javascript">
    $('#responseForm').submit();
</script>