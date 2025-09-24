<!DOCTYPE HTML>
<html>
<head>
    <title>Gracias! - Noticias, Periódico Archivo Servicio de escaneo de las hemerotecas - ArchiveInABox</title>
    <meta name="keywords" content="periódico, archivo, reserva, almacenamiento, conserva, preservación, viejo, noticias, paper, imágenes, microfilm, libro encuadernado, libros, digital, digitally, escanear, escaneado, volúmenes">
    <meta name="Description" content="ArchiveInABox ofrece una solución llave en mano periódico de archivo para ayudar a preservar los dos periódicos nuevos y viejos digitalmente.">
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
                            <h1>Gracias por contactar con SmallTownPapers</h1>
                            <h2>Hemos recibido su consulta. </h2> 
                            <br><br>
                            Nos pondremos en contacto contigo en breve. Si desea hablar con un representante ahora, por favor llame 360-427-6300. 
                            <br><br>
                            <a href="index.php">Llegó a la página de inicio</a>
                        </td>
                        <td>
                        <img src="images/archive_collage.jpg" width="449" height="332" alt="Archive Collage"></td>
                    </tr>
                </table>
                <img src="images/process.gif" width="815" height="143" alt="Process">
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
