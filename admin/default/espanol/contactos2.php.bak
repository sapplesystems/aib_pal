<?php 
$to = "paulj@smalltownpapers.com" ; 
$from = $_REQUEST['Email'] ; 
$name = $_REQUEST['ContactName'] ; 
$headers = "From: $from"; 
$subject = "En Espanol: Archive in a Box Inquiry "; 
$recaptcha = $_REQUEST['recaptcha_response_field'] ; 

$fields = array(); 
$fields{"ContactName"} = "Contact Name"; 
$fields{"GroupName"} = "Group Name"; 
$fields{"Email"} = "Email"; 
$fields{"Phone"} = "Phone"; 
$fields{"Archive"} = "Archive"; 
$fields{"ContactMe"} = "Contact Me"; 
$fields{"Comments"} = "Comments"; 

$body = "Hemos recibido la siguiente informacion:\n\n"; foreach($fields as $a => $b){ $body .= sprintf("%20s: %s\n",$b,$_REQUEST[$a]); } 

$headers2 = "From: info@smalltownpapers.com"; 
$subject2 = "Gracias por contactar con nosotros"; 
$autoreply = "Gracias por contactar con nosotros. Alguien se pondra en contacto con usted tan pronto como sea posible, generalmente dentro de 48 horas. Si usted tiene alguna pregunta mas, por favor consulte nuestro sitio web en www.archiveinabox.com";

if($from == '') {
	print "Usted no ha entrado en un correo electronico, por favor, vuelve e inténtalo de nuevo";
} else { 
	if($name == '') {
		print "No ha ingresado un nombre, por favor, vuelve e intentalo de nuevo";
	} else { 
		if($recaptcha == '') {
			print "Introduzca las palabras que ve en la zona roja reCAPTCHA. Por favor, vuelve e intentalo de nuevo";
		} else { 
			$send = mail($to, $subject, $body, $headers); 
			$send2 = mail($from, $subject2, $autoreply, $headers2); 
			
			if($send) {
				header( "Location: http://www.archiveinabox.com/espanol/gracias.php" );
			}  else {
				print "Se ha detectado un error al enviar el correo, por favor notifique a info@smalltownpapers.com"; 
			} 
		}
	}
}
?> 
