<?php
include("class.phpmailer.php");
set_time_limit (3600) ;
//sendSMTPMail("","","","","aaa","anil.kumar@sapple.co.in","sssss","ssssssssssssss" , '');
function sendSMTPMail($host,$port,$userName,$password,$from,$to,$subject,$body , $attachment = '')
{
	
	$mail = new PHPMailer();
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
		$mail->Username   = "archiveinabox@smalltownpapers.com";  // GMAIL username
		$mail->Password   = "keep me posted";            // GMAIL password
		
	}

	if (isset($GLOBALS["sendmail_from_title"]) == false)
	{
		$SendTitle = "Archive In A Box";
	}
	else
	{
		$SendTitle = $GLOBALS["sendmail_from_title"];
	}

	$mail->AddReplyTo("archiveinabox@smalltownpapers.com",$SendTitle);
	
	$mail->SetFrom("archiveinabox@smalltownpapers.com",$SendTitle);

	$mail->Subject    = $subject;
	
	$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	$mail->WordWrap   = 50; // set word wrap
	$mail->MsgHTML($body);
	$mail->AddAddress($to, "");
	$mail->IsHTML(true); // send as HTML
	if(!$mail->Send())
	{
		return(array("status" => "ERROR", "msg" => $mail->ErrorInfo));
	}
	else
	{
		return(array("status" => "OK"));
	}
}
?>
