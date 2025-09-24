<!DOCTYPE HTML>
<html>
<head>
    <title>Contactos -Noticias, Peri&oacute;dico Archivo Servicio de escaneo de las hemerotecas - ArchiveInABox</title>
    <meta name="keywords" content="peri&oacute;dico, archivo, reserva, almacenamiento, conserva, preservaci&oacute;n, viejo, noticias, paper, im&aacute;genes, microfilm, libro encuadernado, libros, digital, digitally, escanear, escaneado, volúmenes">
    <meta name="Description" content="ArchiveInABox ofrece una soluci&oacute;n llave en mano peri&oacute;dico de archivo para ayudar a preservar los dos peri&oacute;dicos nuevos y viejos digitalmente.">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" href="styles.css">
    <script type="text/javascript">
        function checkForm () {
            if (document.contactForm.ContactName.value.length == 0)
                {alert ("You must enter your contact name");
                  return false;}
            else if (document.contactForm.GroupName.value.length == 0)
                {alert ("Please enter a group name");
                  return false;} 
            else if (document.contactForm.Email.value.length == 0)
                {alert ("Please enter an email address");
                  return false;} 
            else return true;
        }
    </script>
</head>

<body>
    <div class="page-ct">
        <div class="page">
            <?php include ('header.php'); ?> 
            <div class="content-ct">
                <table class="wide">
                    <tr>
                        <td>
                            <table class="wide">
                                <tr>
                                    <td>
                                        <h2>Direcci&oacute;n Física</h2>
                                        SmallTownPapers, Inc.<br>
                                        217 W. Cota Street <br>
                                        Shelton, WA 98584
                                        <br><br>
                                    </td>
                                    <td>
                                        <h2>Teléfono</h2>
                                        (360) 427-6300
                                    </td>
                                </tr>
                            </table>
                            <iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=217+W+Cota+St,+Shelton,+WA+98584&amp;aq=&amp;sll=47.212692,-123.107907&amp;sspn=0.000786,0.001737&amp;vpsrc=6&amp;ie=UTF8&amp;hq=&amp;hnear=217+W+Cota+St,+Shelton,+Washington+98584&amp;t=m&amp;ll=47.212118,-123.101658&amp;spn=6.444174,14.227295&amp;z=7&amp;output=embed"></iframe><br /><small><a href="http://www.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=217+W+Cota+St,+Shelton,+WA+98584&amp;aq=&amp;sll=47.212692,-123.107907&amp;sspn=0.000786,0.001737&amp;vpsrc=6&amp;ie=UTF8&amp;hq=&amp;hnear=217+W+Cota+St,+Shelton,+Washington+98584&amp;t=m&amp;ll=47.212118,-123.101658&amp;spn=6.444174,14.227295&amp;z=7" style="color:#0000FF;text-align:left">Ver mapa m&aacute;s grande</a></small>
                        </td>
                        <td>
                            <div class="section-ct">
                            <h2>Solicite Informaci&oacute;n</h2>
                                <form action="contactos2.php" method="post" name="contactForm" onSubmit="javascript:return checkForm()">
                                    <input name="_recipients" type="hidden" id="_recipients" value="mtncoast@gmail.com"> 
                                    <input name="_subject" type="hidden" id="subject2" value="ArchiveInABox Inquiry"> 
                                    <input name="_from" type="hidden" id="from2" value="ArchiveInABox"> 
                                    <input name="redirect" type="hidden" id="redirect2" value="gracias.php">
                                    <table width="100%" border="0" cellspacing="2" cellpadding="0">
                                        <tr> 
                                            <td>* Nombre Contacto:</td>
                                            <td><input name="ContactName" type="text" id="ContactName" size="30"></td>
                                        </tr>
                                        <tr> 
                                            <td>* Nombre del Peri&oacute;dico o del Grupo : </td>
                                            <td><input name="GroupName" type="text" size="30"></td>
                                        </tr>
                                        <tr> 
                                            <td>* Correo Electr&oacute;nico:</td> 
                                            <td><input name="Email" type="text" id="Email" size="30"></td>
                                        </tr> 
                                        <tr> 
                                            <td>Teléfono (opcional):  </td>
                                            <td><input name="Phone" type="text" id="Phone" size="30"></td>
                                        </tr>
                                        <tr> 
                                            <td>Tipo de Archivo </td>
                                            <td>
                                                <select name="Archive">
                                                    <option value="">Seleccione</option>
                                                    <option value="Bound Volumes">Tomos Encuadernados</option>
                                                    <option value="Loose">Sueltos</option>
                                                    <option value="Microfilm">Microfilmes</option>
                                                    <option value="Other">Otros</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <input type="checkbox" value="ContactMe" checked="checked">
                                                Favor contactarme
                                            </td>
                                        </tr>
                                        </table>
                                        
                                        <table>
                                        <tr> 
                                             <td>Informaci&oacute;n Adicional / Comentarios</td>
                                        </tr>
                                        <tr> 
                                             <td valign="top"><textarea name="Comments" cols="50" rows="6" id="Comments"></textarea></td>
                                        </tr>
                                        
                                        <tr> 
                                             <td>
                                             	<form name="recapctha" method="post" action="verify.php">
													<?php
                                                      require_once('recaptchalib.php');
                                                      $publickey = "6LdGb-0SAAAAAEt_4oaHjKSbKbjC62AZX9T9hSL0"; // you got this from the signup page
                                                      echo recaptcha_get_html($publickey);
                                                    ?>
                                                    <input type="submit" name="Submit" value="Submit" />
                                                  </form>
                                             </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php include ('footer.php'); ?>
        </div>
    </div>
    <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
        var pageTracker = _gat._getTracker("UA-5676111-1");
        pageTracker._trackPageview();
    </script>
</body>
</html>
