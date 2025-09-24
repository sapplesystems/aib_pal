<!DOCTYPE HTML>
<html>
<head>
    <title>Thank You! - Archive and Bound Volume Scanning - ArchiveInABox</title>
    <meta name="Description" content="ArchiveInABox features a turn-key newspaper archive solution to help preserve both new and old newspapers digitally.  Store, storage, preservation, news, paper, imaging, microfilm, bound, books, digital, scan, scanning, copy.">
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
                            <h1>Thank you for contacting SmallTownPapers</h1>
                            <h2>Your inquiry has been received. </h2> 
                            <br><br>
                            We will contact you soon. If you wish to speak with a representative now, please call 360-427-6300. 
                            <br><br>
                            <a href="index.php">Back to Home Page</a>
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
