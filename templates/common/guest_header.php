<?php $loginCssArray = array('bootstrap.min.css','font-awesome.min.css','ionicons.min.css','AdminLTE.min.css','blue.css'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>ArchiveInABox | Log in</title>
        <link rel="shortcut icon" type="image/png" href="favicon.ico"/>
        <!--<link rel="shortcut icon" type="image/png" href="http://eg.com/favicon.png"/>-->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <?php foreach($loginCssArray as $key=>$fileName){ ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH.$fileName; ?>">
        <?php } ?>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    </head>
    <body class="hold-transition login-page">