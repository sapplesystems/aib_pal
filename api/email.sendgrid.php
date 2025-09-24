<?php
//
// Email functions
//

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

//require 'Exception.php';
//require 'PHPMailer.php';
//require 'SMTP.php';
include("api_util.php");
include("../config/aib_site.php");

// SENDGRID

include("./lib/eventwebhook/EventWebhook.php");
include("./lib/eventwebhook/EventWebhookHeader.php");
include("./lib/mail/BypassListManagement.php");
include("./lib/mail/Category.php");
include("./lib/mail/MimeType.php");
include("./lib/mail/TemplateId.php");
include("./lib/mail/BatchId.php");
include("./lib/mail/Section.php");
include("./lib/mail/EmailAddress.php");
include("./lib/mail/From.php");
include("./lib/mail/Attachment.php");
include("./lib/mail/ReplyTo.php");
include("./lib/mail/CustomArg.php");
include("./lib/mail/MailSettings.php");
include("./lib/mail/Asm.php");
include("./lib/mail/Content.php");
include("./lib/mail/HtmlContent.php");
include("./lib/mail/Cc.php");
include("./lib/mail/To.php");
include("./lib/mail/Header.php");
include("./lib/mail/BccSettings.php");
include("./lib/mail/IpPoolName.php");
include("./lib/mail/TypeException.php");
include("./lib/mail/PlainTextContent.php");
include("./lib/mail/SandBoxMode.php");
include("./lib/mail/Substitution.php");
include("./lib/mail/SubscriptionTracking.php");
include("./lib/mail/Personalization.php");
include("./lib/mail/OpenTracking.php");
include("./lib/mail/GroupsToDisplay.php");
include("./lib/mail/TrackingSettings.php");
include("./lib/mail/SendAt.php");
include("./lib/mail/ClickTracking.php");
include("./lib/mail/SpamCheck.php");
include("./lib/mail/Ganalytics.php");
include("./lib/mail/Subject.php");
include("./lib/mail/GroupId.php");
include("./lib/mail/Mail.php");
include("./lib/mail/Bcc.php");
include("./lib/mail/Footer.php");
include("./vendor/sendgrid/php-http-client/lib/Response.php");
include("./vendor/sendgrid/php-http-client/lib/Exception/InvalidRequest.php");
include("./vendor/sendgrid/php-http-client/lib/Client.php");
include("./lib/BaseSendGridClientInterface.php");
include("./lib/SendGrid.php");
include("./lib/contacts/RecipientForm.php");
include("./lib/contacts/Recipient.php");
include("./lib/helper/Assert.php");
include("./lib/TwilioEmail.php");
include("./lib/stats/Stats.php");

use SendGrid\Mail\Mail;


// Log a debug message
// -------------------
function email_service_log_debug($Msg)
{
	$Handle = fopen("/tmp/email_service_debug.txt","a+");
	if ($Handle != false)
	{
		fputs($Handle,sprintf("%0.6lf",microtime(true)).": ".$Msg."\n");
		fclose($Handle);
	}
}

// Get value from associative array with default
// ---------------------------------------------
function get_assoc_default($ArrayIn,$Name,$Default)
{
	if (isset($ArrayIn[$Name]) == false)
	{
		return($Default);
	}

	return($ArrayIn[$Name]);
}

// Retrieve user with ID or login
// ------------------------------
function get_user_info($FormData,$UserID = false,$UserLogin = false)
{
	$UserID = get_assoc_default($FormData,"user_id",false);
	$UserLogin = get_assoc_default($FormData,"user_login",false);
	$UserRecord = false;
	$OutData = array("status" => "OK", "info" => "");
	if ($UserID !== false)
	{
		$UserRecord = ftree_get_user($GLOBALS["aib_db"],$UserID);
	}
	else
	{
		if ($UserLogin !== false)
		{
			$UserRecord = ftree_get_user_by_login($GLOBALS["aib_db"],$UserLogin);
		}
		else
		{
			$OutData["status"] = "ERROR";
			$OutData["info"] = "MISSINGUSERIDORLOGIN";
			return($OutData);
		}
	}

	if ($UserRecord == false)
	{
		$OutData["status"] = "ERROR";
		$OutData["info"] = "USERIDORLOGINNOTFOUND";
	}
	else
	{
		$OutData["info"] = $UserRecord;
	}

	return($OutData);

}

// Make sure the requesting user is allowed to get the account information.  The requesting user
// must be a super admin, an admin, or the same user.
// ---------------------------------------------------------------------------------------------
function check_user_profile_access($RequestUserID,$UserID,$UserOwnerInfo,$UserType)
{
	// If this is a high-level function (not specific to a user), the requesting user
	// must be the root or an admin.

	if ($UserID === false)
	{
		if ($UserType != AIB_USER_TYPE_ROOT && $UserType != AIB_USER_TYPE_ADMIN)
		{
			return(false);
		}

		return(true);
	}

	if ($UserID != $RequestUserID)
	{
		if ($UserType != AIB_USER_TYPE_ROOT && $UserType != AIB_USER_TYPE_ADMIN)
		{
			return(false);
		}

		// If the user is an admin, make sure either the top folder is the same
		// as the admin user, or that the admin user actually owns the user in question.

		if ($UserType == AIB_USER_TYPE_ADMIN)
		{
			$AdminUserProfile = ftree_get_user($GLOBALS["aib_db"],$RequestUserID);
			if ($AdminUserProfile == false)
			{
				return(false);
			}

			if ($UserOwnerInfo["owner"] == "NULL")
			{
				$UserInfo = ftree_get_user($GLOBALS["aib_db"],$UserID);
				$UserTopFolder = $UserInfo["user_top_folder"];
				$RequestTopFolder = $AdminUserProfile["user_top_folder"];

				// If the admin top folder and the requesting user top folder are the same, then
				// the requesting user is ok.

				if ($UserTopFolder == $RequestTopFolder)
				{
					return(true);
				}

				// Otherwise, check to see if the user's top folder is within the tree for the
				// administrator's top folder.

				$IDPath = ftree_get_item_id_path($GLOBALS["aib_db"],$RequestTopFolder);
				if ($IDPath == false)
				{
					return(false);
				}

				$CheckFlag = false;
				foreach($IDPath as $LocalID)
				{
					// Found the admin's top folder; set flag.  This will always occur BEFORE
					// the user's top folder is found if the admin is higher in the tree.

					if ($LocalID == $RequestTopFolder)
					{
						$CheckFlag = true;
						continue;
					}

					// Found user's top folder

					if ($LocalID == $UserTopFolder)
					{
						// If the check flag is false, it means this user
						// is outside of the top folder for the admin, regardless of
						// whether the admin is in a different tree, or if the admin
						// top folder is below that of the user.

						if ($CheckFlag == false)
						{
							return(false);
						}

						break;
					}
				}

				return(true);
			}
		}
	}

	// Defaults to true; maybe this should default to false?

	return(true);
}

// #########
// MAIN CODE
// #########


	// Collect form data

	$FormData = array();
	foreach($_GET as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	foreach($_POST as $Name => $Value)
	{
		$FormData[$Name] = $Value;
	}

	// Get server name.  Must be a valid source as listed in the hosts table.

	$ServerName = get_assoc_default($_SERVER,"REMOTE_HOST",get_assoc_default($_SERVER,"REMOTE_ADDR",false));
	if ($ServerName == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOHOST"));
		exit(0);
	}
	else
	{
		// If the server name is the IP address, attempt to do a reverse lookup using the address.
		// If this fails, simply use the IP address.

		if (preg_match("/^[0-9\.]+$/",$ServerName) != false)
		{
			$HostName = gethostbyaddr($ServerName);
			if ($HostName != false && strtolower($ServerName) != strtolower($HostName))
			{
				$ServerName = $HostName;
			}
		}
	}

	// Get operation to perform

	$OpCode = get_assoc_default($FormData,"_op",false);
	if ($OpCode == false)
	{
		aib_api_send_response(array("status" => "ERROR", "info" => "NOOP"));
		exit(0);
	}

	// Check server name and opcode; make sure the source is allowed to perform this operation

	aib_open_db();
	if (aib_api_check_host($GLOBALS["aib_db"],$ServerName,$OpCode) == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "HOSTNOTALLOWED"));
		exit(0);
	}

	// Get API key and session, then validate

	$APIKey = get_assoc_default($FormData,"_key",false);
	$APISession = get_assoc_default($FormData,"_session",false);
	if ($APIKey == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGKEY"));
		exit(0);
	}

	if ($APISession == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGSESSION"));
		exit(0);
	}

	$Result = aib_api_validate_session_key($GLOBALS["aib_db"],$APIKey,$APISession,AIB_MAX_API_SESSION);
	if ($Result[0] != "OK")
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => $Result[1]));
		exit(0);
	}

	// Get keyholder

	$KeyHolderID = aib_api_get_key_id($GLOBALS["aib_db"],$APIKey);
	if ($KeyHolderID == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "KEYHOLDERIDNOTFOUND"));
		exit(0);
	}

	// Get user ID of requesting user; required for user account operations

	$RequestUserID = get_assoc_default($FormData,"_user",false);
	if ($RequestUserID === false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "MISSINGUSER"));
		exit(0);
	}

	// Get the user type and information

	$RequestUserRecord = ftree_get_user($GLOBALS["aib_db"],$RequestUserID);
	if ($RequestUserRecord == false)
	{
		aib_close_db();
		aib_api_send_response(array("status" => "ERROR", "info" => "BADREQUESTUSER"));
		exit(0);
	}

	$RequestUserType = $RequestUserRecord["user_type"];


	// Generate a new session

	$NewSession = aib_api_generate_session_key($GLOBALS["aib_db"],$KeyHolderID);
	$OutData = array("status" => "OK", "session" => $NewSession);

	switch($OpCode)
	{
		// Send email

		case "send":

			$To = get_assoc_default($FormData,"to",false);
			$From = get_assoc_default($FormData,"from",false);
			$Reply = get_assoc_default($FormData,"reply",false);
			$Subject = get_assoc_default($FormData,"subject",false);
			$Subject = preg_replace("/\W/"," ",$Subject);
			$Body = get_assoc_default($FormData,"body",false);
			$IsHTML = get_assoc_default($FormData,"is_html","N");
			$CC = get_assoc_default($FormData,"cc",false);
			$BCC = get_assoc_default($FormData,"bcc",false);
			if (preg_match("/[Yy]/",$IsHTML) != false)
			{
				$IsHTML = true;
			}
			else
			{
				$IsHTML = false;
			}

			if ($To == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGTO";
				break;
			}

			if ($From == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGFROM";
				break;
			}

			if ($Reply == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGREPLY";
				break;
			}

			if ($Subject == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGSUBJECT";
				break;
			}

			if ($Body == false)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = "MISSINGBODY";
				break;
			}

			$TempList = explode(",",$To);
			$ToList = array();
			foreach($TempList as $Entry)
			{
				$Seg = explode("/",$Entry);
				$Address = array_shift($Seg);
				if (count($Seg) > 0)
				{
					$Name = join("/",$Seg);
				}
				else
				{
					$Name = false;
				}

				$ToList[$Address] = $Name;
			}

			$Seg = explode("/",$From);
			$FromAddress = array_shift($Seg);
			if (count($Seg) > 0)
			{
				$FromName = join("/",$Seg);
			}
			else
			{
				$FromName = false;
			}

			$Seg = explode("/",$Reply);
			$ReplyAddress = array_shift($Seg);
			if (count($Seg) > 0)
			{
				$ReplyName = join("/",$Seg);
			}
			else
			{
				$ReplyName = false;
			}

			// CC

			$CCList = array();
			if ($CC != false)
			{
				$TempList = explode(",",$CC);
				foreach($TempList as $Entry)
				{
					$Seg = explode("/",$Entry);
					$Address = array_shift($Seg);
					if (count($Seg) > 0)
					{
						$Name = join("/",$Seg);
					}
					else
					{
						$Name = false;
					}
	
					$CCList[$Address] = $Name;
				}
			}

			// BCC

			$BCCList = array();
			if ($BCC != false)
			{
				$TempList = explode(",",$BCC);
				foreach($TempList as $Entry)
				{
					$Seg = explode("/",$Entry);
					$Address = array_shift($Seg);
					if (count($Seg) > 0)
					{
						$Name = join("/",$Seg);
					}
					else
					{
						$Name = false;
					}
	
					$BCCList[$Address] = $Name;
				}
			}


			// Create mailer object.  Passing 'true' to constructor enables exceptions

			$Mailer = new Mail();

    			// Sender

			if ($FromName != false)
			{
				$Mailer->setFrom($FromAddress,$FromName);
			}
			else
			{
				$Mailer->setFrom($FromAddress);
			}

			// Recipients

			foreach($ToList as $ToAddress => $ToName)
			{
				if ($ToName != false)
				{
					$Mailer->addTo($ToAddress,$ToName);
				}
				else
				{
					$Mailer->addTo($ToAddress);
				}
			}

			// Reply-to

			if ($ReplyName != false)
			{
				$Mailer->setReplyTo($ReplyAddress,$ReplyName);
			}
			else
			{
				$Mailer->setReplyTo($ReplyAddress);
			}

			// CC

			foreach($CCList as $ToAddress => $ToName)
			{
				if ($ToName != false)
				{
					$Mailer->addTo($ToAddress,$ToName);
				}
				else
				{
					$Mailer->addTo($ToAddress);
				}
			}

			// BCC

			foreach($BCCList as $ToAddress => $ToName)
			{
				if ($ToName != false)
				{
					$Mailer->addBcc($ToAddress,$ToName);
				}
				else
				{
					$Mailer->addBcc($ToAddress);
				}
			}

			// Content

			$Mailer->setGlobalSubject($Subject);
			if ($IsHTML == true)
			{
				$Mailer->addContent("text/html",$Body);
			}
			else
			{
				$Mailer->addContent("text/ascii",$Body);
			}

			$SendGrid = new \SendGrid(SENDGRID_API_KEY);
			$OutData["info"] = array("status" => "OK", "info" => "");
			try {
				$Response = $SendGrid->send($Mailer);
			}
			catch (Exception $EValue)
			{
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Mailer->ErrorInfo;
			}
/* OLD CODE
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

    				// Sender

				if ($FromName != false)
				{
					$Mailer->setFrom($FromAddress,$FromName);
				}
				else
				{
					$Mailer->setFrom($FromAddress);
				}

				// Recipients

				foreach($ToList as $ToAddress => $ToName)
				{
					if ($ToName != false)
					{
						$Mailer->addTo($ToAddress,$ToName);
					}
					else
					{
						$Mailer->addTo($ToAddress);
					}
				}

				// Reply-to

				if ($ReplyName != false)
				{
					$Mailer->setReplyTo($ReplyAddress,$ReplyName);
				}
				else
				{
					$Mailer->setReplyTo($ReplyAddress);
				}

				// CC

				foreach($CCList as $ToAddress => $ToName)
				{
					if ($ToName != false)
					{
						$Mailer->addTo($ToAddress,$ToName);
					}
					else
					{
						$Mailer->addTo($ToAddress);
					}
				}

				// BCC

				foreach($BCCList as $ToAddress => $ToName)
				{
					if ($ToName != false)
					{
						$Mailer->addBcc($ToAddress,$ToName);
					}
					else
					{
						$Mailer->addBcc($ToAddress);
					}
				}

//				$Mailer->setFrom('from@example.com', 'Mailer');
//				$Mailer->addAddress('joe@example.net', 'Joe User');     // Add a recipient
//				$Mailer->addAddress('ellen@example.com');               // Name is optional
//				$Mailer->addReplyTo('info@example.com', 'Information');
//				$Mailer->addCC('cc@example.com');
//				$Mailer->addBCC('bcc@example.com');

				// Content

				$Mailer->setSubject = $Subject;
				if ($IsHTML == true)
				{
					$Mailer->addContent("text/html",$Body);
				}
				else
				{
					$Mailer->addContent("text/ascii",$Body);
				}

				$Mailer->send();
			}
			catch (Exception $e)
			{
				email_service_log_debug("Error sending email: ".$Mailer->ErrorInfo);
				$OutData["status"] = "ERROR";
				$OutData["info"] = $Mailer->ErrorInfo;
			}

			$OutData["info"] = array("status" => "OK", "info" => "");
*/
			break;

		default:
			$OutData["status"] = "ERROR";
			$OutData["info"] = "BADOP";
			break;
	}

	aib_close_db();
	aib_api_send_response($OutData);
	exit(0);
?>
