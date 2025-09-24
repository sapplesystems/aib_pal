<?php /* ?><script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="http://bank.sappleserve.com/esign/public/js/geoPosition.js"></script>
<script src="http://bank.sappleserve.com/esign/public/js/geoPositionSimulator.js"></script>
<script type="text/javascript">
		if(geoPosition.init()){
			geoPosition.getCurrentPosition(success_callback,error_callback,{enableHighAccuracy:true});
		}
		else{
			document.getElementById('result').innerHTML = '<span class="error">Functionality not available</span>';
		}

		function success_callback(p)
		{
			var latitude = parseFloat( p.coords.latitude );
			var longitude = parseFloat( p.coords.longitude );
			$.session.set('latitude', latitude);
			$.session.set('longitude', longitude); 
		}
		
		function error_callback(p)
		{
					
		}		
</script>
<?php */ ?>
<?php
require_once dirname(__FILE__). '/config/bootstrap.php';
use classes\siteUtility;
$headersData = apache_request_headers();
$requestedData = (array)json_decode(file_get_contents('php://input'));
$ip_address = $_SERVER['REMOTE_ADDR'];
$jsondata = file_get_contents("http://timezoneapi.io/api/ip/?" . $ip_address);
$data = json_decode($jsondata, true);
$requestedData = siteUtility::cleanDataArray($requestedData);
$requestedData['ip_address']   = isset($data['data']['ip']) ? $data['data']['ip'] : '';
$requestedData['city'] 	       = isset($data['data']['city']) ? $data['data']['city'] : '';
$requestedData['state']        = isset($data['data']['state']) ? $data['data']['state'] : '';
$requestedData['country']      = isset($data['data']['country']) ? $data['data']['country'] : '';
list($latitude,$longitude)     = explode(",", $data['data']['location']);
$requestedData['latitude']     = isset($latitude) ? $latitude : '';
$requestedData['longitude']    = isset($longitude) ? $longitude : '';
if(array_key_exists('AuthToken', $headersData)){
    $requestedToken = $headersData['AuthToken'];
    $requestedData['AuthToken'] = $requestedToken;
    $companyObj  = new classes\companyController();
    $companyData = $companyObj->validateByToken($requestedToken);
    if(!empty($companyData) && is_numeric($companyData['company_id'])){ 
        $savedRequestedData = $companyObj->setRequestedData($requestedData,$companyData['company_id']);
        if(!empty($savedRequestedData)){
        ?>
            <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script> 
            <form action="http://14.142.204.12:98/eMsecure/SignerGateway/Index" id="frmdata" method="post">
                <input id="File" name="File" type="hidden" value="<?php echo isset($requestedData['File']) ? $requestedData['File'] : ''; ?>" />
                <input id="Name" name="Name" type="hidden" value="<?php echo isset($requestedData['name']) ? $requestedData['name'] : ''; ?>" />
                <input id="SelectPage" name="SelectPage" type="hidden" value="<?php echo isset($requestedData['SelectPage']) ? $requestedData['SelectPage'] : 'FIRST'; ?>" />
                <input id="SignatureType" name="SignatureType" type="hidden" value="<?php echo isset($requestedData['SignatureType']) ? $requestedData['SignatureType'] : '1'; ?>" />
                <input id="SignaturePosition" name="SignaturePosition" type="hidden" value="<?php echo isset($requestedData['SignaturePosition']) ? $requestedData['SignaturePosition'] : 'Bottom-Center'; ?>" />
                <input id="AuthToken" name="AuthToken" type="hidden" value="<?php echo AUTH_TOKEN; ?>" />
                <input id="PageNumber" name="PageNumber" type="hidden" value="<?php echo isset($requestedData['PageNumber']) ? $requestedData['PageNumber'] : ''; ?>" />
                <input id="Data" name="Data" type="hidden" value="<?php echo isset($requestedData['Data']) ? $requestedData['Data'] : ''; ?>" />
                <input id="FileType" name="FileType" type="hidden" value="<?php echo isset($requestedData['FileType']) ? $requestedData['FileType'] : 'PDF'; ?>" />
                <input id="PreviewRequired" name="PreviewRequired" type="hidden" value="<?php echo isset($requestedData['PreviewRequired']) ? $requestedData['PreviewRequired'] : 'false'; ?>" />
                <input id="CustomizeCoordinates" name="CustomizeCoordinates" type="hidden" value="<?php echo isset($requestedData['CustomizeCoordinates']) ? $requestedData['CustomizeCoordinates'] : ''; ?>" />
                <input id="PagelevelCoordinates" name="PagelevelCoordinates" type="hidden" value="<?php echo isset($requestedData['PagelevelCoordinates']) ? $requestedData['PagelevelCoordinates'] : ''; ?>" />
                <input id="SUrl" name="SUrl" type="hidden" value="http://bank.sappleserve.com/esign/response_api?type=success&data_id=<?php echo md5($savedRequestedData['data_id']); ?>" /> 
                <input id="FUrl" name="FUrl" type="hidden" value="http://bank.sappleserve.com/esign/response_api?type=fail&data_id=<?php echo md5($savedRequestedData['data_id']); ?>" /> 
                <input id="CUrl" name="CUrl" type="hidden" value="http://bank.sappleserve.com/esign/response_api?type=cancel&data_id=<?php echo md5($savedRequestedData['data_id']); ?>" /> 
                <input id="AadhaarNumber" name="AadhaarNumber" type="hidden" value="<?php echo isset($requestedData['AadhaarNumber']) ? $requestedData['AadhaarNumber'] : ''; ?>" /> 
                <input id="ReferenceNumber" name="ReferenceNumber" type="hidden" value="<?php echo $savedRequestedData['ref_num']; ?>" />
                <center style="position:absolute;top:10%;left:18%">
                    <div>
                        <img src="/Content/Img/ajax-loader.gif" />
                        <div>
                            <h4>
                                You will be redirected to emSigner signature gateway please wait...
                            </h4>
                        </div>
                    </div>
                    <input type="submit" value="sign" />
                </center>
            </form> 
   <?php }else{
       echo "Invalid authentication token.";
   }
    }
}else{
    echo "Authentication token is required.";
}
