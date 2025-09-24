 <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script> 
<form action="http://14.142.204.12:98/eMsecure/SignerGateway/Index" id="frmdata" method="post">
<input id="File" name="File"
 type="hidden" value="<?php echo base64_encode(file_get_contents('http://sappleserve.com/esign/sample.pdf'));?>" />
 <input id="Name" name="Name" type="hidden" value="Brijesh Gupta" />
 <input id="SelectPage" name="SelectPage" type="hidden" value="FIRST" />
 <input id="SignatureType" name="SignatureType" type="hidden" value="1" />
 <input id="SignaturePosition" name="SignaturePosition" type="hidden" value="Bottom-Center" />
 <input id="AuthToken" name="AuthToken" type="hidden" value="F9jzk/ocW3oAc2eihyVSttAMcz9idMpzIlUWcalN9gPpX6epxINH+TVlo0sVdzXD" />
 <input id="PageNumber" name="PageNumber" type="hidden" value="" />
 <input id="Data" name="Data" type="hidden" value="" />
 <input id="FileType" name="FileType" type="hidden" value="PDF" />
 <input id="PreviewRequired" name="PreviewRequired" type="hidden" value="false" />
 <input id="CustomizeCoordinates" name="CustomizeCoordinates" type="hidden" value="" />
 <input id="PagelevelCoordinates" name="PagelevelCoordinates" type="hidden" value="" />
 <input id="SUrl" name="SUrl" type="hidden" value="http://sappleserve.com/esign/SUrl.php" /> 
  <input id="FUrl" name="FUrl" type="hidden" value="http://sappleserve.com/esign/FUrl.php" /> 
   <input id="CUrl" name="CUrl" type="hidden" value="http://sappleserve.com/esign/CUrl.php" /> 
   <input id="AadhaarNumber" name="AadhaarNumber" type="hidden" value="" /> 
 <center style="position:absolute;top:35%;left:33%">
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