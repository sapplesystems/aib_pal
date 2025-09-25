<?php
include("class.phpmailer.php");
set_time_limit (3600) ;
//sendSMTPMail("","","","","aaa","anil.kumar@sapple.co.in","sssss","ssssssssssssss" , '');
function sendSMTPMail($host,$port,$userName,$password,$from,$to,$subject,$body , $attachment = '')
{
	
	//$from="sales@ida-reader.com";
	$mail             = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	$mail->Mailer = "smtp";

	if(trim($host) != "" && trim($port) != "" && trim($userName) != "" && trim($password) != "")
	{
		$mail->Host       = $host;      // sets GMAIL as the SMTP server
		$mail->Port       = $port;                   // set the SMTP port for the GMAIL server
		$mail->Username   = $userName;  // GMAIL username
		$mail->Password   = $password;            // GMAIL password
	}
	else
	{
		$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
		$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
		$mail->Username   = "catchall@alllinksinone.com";  // GMAIL username
		$mail->Password   = "sapple123";            // GMAIL password
		
		/*$mail->Host       = "168.144.48.25";      // sets GMAIL as the SMTP server
		$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
		$mail->Username   = "sales@ida-reader.com";  // GMAIL username
		$mail->Password   = "planmanida@123";            // GMAIL password*/
	}

	if (isset($GLOBALS["sendmail_from_title"]) == false)
	{
		$SendTitle = "STP Archive";
	}
	else
	{
		$SendTitle = $GLOBALS["sendmail_from_title"];
	}

	$mail->AddReplyTo("admin@stparchive.com",$SendTitle);
	
	$mail->SetFrom("admin@stparchive.com",$SendTitle);

	$mail->Subject    = $subject;
	
	//$mail->Body       = "Hi,<br>This is the HTML BODY<br>";                      //HTML Body
	$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	$mail->WordWrap   = 50; // set word wrap
	$mail->MsgHTML($body);
	$mail->AddAddress($to, "");
	$mail->IsHTML(true); // send as HTML
	if(!$mail->Send())
	return false;
	else
	return true;
}
?>
