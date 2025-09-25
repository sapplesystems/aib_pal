<?php 
include("../config/aib_site.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

function log_debug($Msg)
{
	$Handle = fopen("/tmp/default_aib_contact_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function log_error($Msg)
{
	$Handle = fopen("/tmp/default_aib_contact_error.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,$Msg."\n");
		fclose($Handle);
	}
}

function save_request($From,$Comments)
{
	$Handle = fopen("/raid2/aib_requests/contact_requests.txt","a+rw");
	if ($Handle != false)
	{
		fputs($Handle,date("m/d/Y H:i:s")."\n");
		fputs($Handle,"FROM=$From\n");
		fputs($Handle,"MSG=$Comments\n");
		fputs($Handle,"=====\n");
		fclose($Handle);
	}
}

function print_retry($Msg = "You must enter the words you see in the red recaptcha box.  Please go back and try again.")
{
	print "
		<div style='background-color:lemonchiffon; border: 1px solid orange; font-family: arial, helvetica, sans-serif; padding: 20px; font-size: 16px;'>
			<h1 style='color:red;'>Form Error!</h1>$Msg
		</div>
		<div style=' font-family: arial, helvetica, sans-serif; padding: 20px; font-size: 16px;'>
				Thank you for your cooperation! 
		</div>";
}


	$CopyToList = array("paulj@archiveinabox.com","meadway@smalltownpapers.com","cody@smalltownpapers.com");

	$MainTo = "paulj@smalltownpapers.com"; 
	$from = $_POST["Email"];
	$name = $_POST["ContactName"];
	$recaptcha = $_POST["recaptcha_response_field"];

//$from = $_REQUEST['Email'] ; 
//$name = $_REQUEST['ContactName'] ; 
$headers = "From: $from"; 
$subject = "Archive in a Box Inquiry Form";
//$recaptcha = $_REQUEST['recaptcha_response_field'] ; 

$fields = array(); 
$fields{"ContactName"} = "Contact Name"; 
$fields{"GroupName"} = "Group Name"; 
$fields{"Email"} = "Email"; 
$fields{"Phone"} = "Phone"; 
$fields{"Archive"} = "Archive"; 
$fields{"ContactMe"} = "Contact Me"; 
$fields{"Comments"} = "Comments";

$FormData = array();
foreach($fields as $Name => $Desc)
{
	if (isset($_POST[$Name]) == true)
	{
		$FormData[$Name] = $_POST[$Name];
	}
	else
	{
		$FormData[$Name] = "";
	}
}

$body = "We have received the following information:\n\n"; foreach($fields as $a => $b){ $body .= sprintf("%20s: %s\n",$b,$_POST[$a]); } 

$headers2 = "From: info@smalltownpapers.com"; 
$subject2 = "Thank you for contacting us"; 
$autoreply = "Thank you for contacting us. Somebody will get back to you as soon as possible, usually within 48 hours. If you have any more questions, please consult our website at www.archiveinabox.com";

if ($from == '')
{
	print "You have not entered an email, please go back and try again.";
	exit(0);
}

if ($name == '')
{
	print "You have not entered a name, please go back and try again.";
	exit(0);
}

if ($recaptcha == '' || $recaptcha == 'unsure')
{
	print "
		<div style='background-color:lemonchiffon; border: 1px solid orange; font-family: arial, helvetica, sans-serif; padding: 20px; font-size: 16px;'>
			<h1 style='color:red;'>Form Error!</h1>
			You must enter the words you see in the red recaptcha box, please go back and try again.
		</div>
		<div style=' font-family: arial, helvetica, sans-serif; padding: 20px; font-size: 16px;'>
			<h2>What is a ReCaptcha?</h2> This is a tool used to let us know you are a human requesting information, and not a spam bot up to no good.
				<br><br>  
				Thank you for your cooperation! 
		</div>";
	exit(0);
}

	// Reject email if it appears to be SPAM

	$FormEmail = ltrim(rtrim($FormData["Email"]));
	$FormGroupName = ltrim(rtrim($FormData["GroupName"]));
	$FormComments = ltrim(rtrim($FormData["Comments"]));
	$FormPhone = ltrim(rtrim($FormData["Phone"]));
	$FormContactName = ltrim(rtrim($FormData["ContactName"]));

	// Reject if the fields contain any image sources or hrefs

	$CheckList = array($FormEmail,$FormGroupName,$FormComments,$FormPhone,$FormContactName);
	foreach($CheckList as $CheckValue)
	{
		if (preg_match("/[Ss][Rr][Cc][^\=]*[\=][^\=]*/",$CheckValue) != false)
		{
			print_retry("There was an error when verifying your request.  Please go back and try again.");
			exit(0);
		}

		if (preg_match("/[Hh][Rr][Ee][Ff][^\=]*[\=][^\=]*/",$CheckValue) != false)
		{
			print_retry("There was an error when verifying your request.  Please go back and try again.");
			exit(0);
		}
	}

	// Reject certain top-level domains

	if (preg_match("/[\.]ru$/",$FormEmail) != false)
	{
		print_retry("The email address entered (\"$FormEmail\") may be invalid.  Please go back and try again.");
		exit(0);
	}

	if (preg_match("/[\.]cn$/",$FormEmail) != false)
	{
		print_retry("The email address entered (\"$FormEmail\") may be invalid.  Please go back and try again.");
		exit(0);
	}

	// Check for missing '@' in email address line

	if (preg_match("/[\@]/",$FormEmail) == false)
	{
		print_retry("The email address entered (\"$FormEmail\") may be invalid.  Please go back and try again.");
		exit(0);
	}

	// Add timestamp to subject line for AIB/STP staff

	$subject .= " ".date("m/d/Y H:i:s");

	// Save email content to text file in case mail server screws up

	save_request($from,$body);

	// Send email to primary contact at STP/AIB

	$Mailer = new PHPMailer(true);
	try {
		// Server settings

		$Mailer->SMTPDebug = 0;			// Disable debugging
		$Mailer->isSMTP();			// Set mailer to use SMTP
		$Mailer->Host = AIB_MAIL_HOST;		// Specify main and backup SMTP servers
		$Mailer->SMTPAuth = true;			// Enable SMTP authentication
		$Mailer->Username = AIB_MAIL_USER;	// SMTP username
		$Mailer->Password = AIB_MAIL_PASS;	// SMTP password
		$Mailer->SMTPSecure = AIB_MAIL_TRANSPORT;	// Transport type; may be tls or ssl
		$Mailer->Port = AIB_MAIL_PORT;		// TCP port

		$Mailer->setFrom(AIB_MAIL_FROM);
		$Mailer->addAddress($MainTo);
		$Mailer->Subject = $subject;
		$Mailer->Body = $body;
		$Mailer->send();
	}
	catch (Exception $e)
	{
		print("We encountered an error sending your mail.  Please notify info@smalltownpapers.com");
		log_error(var_export($e,true));
		exit(0);
	}

	unset($Mailer);

	// Send copy to sender

	$Mailer = new PHPMailer(true);
	try {
		// Server settings

		$Mailer->SMTPDebug = 0;			// Disable debugging
		$Mailer->isSMTP();			// Set mailer to use SMTP
		$Mailer->Host = AIB_MAIL_HOST;		// Specify main and backup SMTP servers
		$Mailer->SMTPAuth = true;			// Enable SMTP authentication
		$Mailer->Username = AIB_MAIL_USER;	// SMTP username
		$Mailer->Password = AIB_MAIL_PASS;	// SMTP password
		$Mailer->SMTPSecure = AIB_MAIL_TRANSPORT;	// Transport type; may be tls or ssl
		$Mailer->Port = AIB_MAIL_PORT;		// TCP port

		$Mailer->setFrom(AIB_MAIL_FROM);
		$Mailer->addAddress($from);
		$Mailer->Subject = $subject2;
		$Mailer->Body = $autoreply;
		$Mailer->send();
	}
	catch (Exception $e)
	{
		print("We encountered an error sending your mail.  Please notify info@smalltownpapers.com");
		log_error(var_export($e,true));
		exit(0);
	}

	// Send copies for backup to other recipients

	foreach($CopyToList as $CopyTo)
	{
		unset($Mailer);
		$Mailer = new PHPMailer(true);
		try {
			// Server settings

			$Mailer->SMTPDebug = 0;			// Disable debugging
			$Mailer->isSMTP();			// Set mailer to use SMTP
			$Mailer->Host = AIB_MAIL_HOST;		// Specify main and backup SMTP servers
			$Mailer->SMTPAuth = true;			// Enable SMTP authentication
			$Mailer->Username = AIB_MAIL_USER;	// SMTP username
			$Mailer->Password = AIB_MAIL_PASS;	// SMTP password
			$Mailer->SMTPSecure = AIB_MAIL_TRANSPORT;	// Transport type; may be tls or ssl
			$Mailer->Port = AIB_MAIL_PORT;		// TCP port
	
			$Mailer->setFrom(AIB_MAIL_FROM);
			$Mailer->addAddress($CopyTo);
			$Mailer->Subject = $subject;
			$Mailer->Body = $body;
			$Mailer->send();
		}
		catch (Exception $e)
		{
			// No action
		}
	}

	header("Location: http://www.archiveinabox.com/default/thanks.php");
//			$send = mail($to, $subject, $body, $headers); 
//			$send2 = mail($from, $subject2, $autoreply, $headers2); 
		
//			if($send){
//				header( "Location: http://www.archiveinabox.com/default/thanks.php" );
//			} else {
//				print "We encountered an error sending your mail, please notify info@smalltownpapers.com"; 
//			}
?> 
