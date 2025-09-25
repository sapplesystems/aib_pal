<?php
$cssArray = ['bootstrap.css', 'class.css','society_class.css', 'aib-responsive.css', 'ideal-image-slider.css', 'default.css', 'common.css', 'style_common.css', 'style1.css','jquery.dataTables.min.css','sappleslider.multi.css','tabulous.css','component.css','jquery.tagit.css','tagit.ui-zendesk.css','magicsuggest.css','jquery.mCustomScrollbar.css','selectize.bootstrap.css'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="shortcut icon" type="image/png" href="favicon.ico"/>
        <!--<link rel="shortcut icon" type="image/png" href="http://eg.com/favicon.png"/>-->
        <title>ArchiveInABox</title>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
        <?php foreach ($cssArray as $key => $fileName) { ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH . $fileName; ?>" />
        <?php } ?>
        <style> #slider{height:460px !important;}</style>
		
    </head>
    <body>
        <div class="loading-div">
            <img class="loading-img" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." />
        </div>
        <div class="loading-div-fullPage">
            <img class="loading-img" src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading..." />
        </div>
        <div class="bgTopStripe">
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="col-md-3 col-sm-6 col-xs-12 centerText header_logo">
                        <a href="index.php"><img height="40" src="<?php echo IMAGE_PATH . 'logo-aib.png'; ?>" alt="" /></a>
                    </div>
                    <div class="col-md-9 col-sm-6 col-xs-12 text-right textAlignCenter topMargin10">
                        <ul class="header-menu pull-right">
                            <?php if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) { ?>
                                <!--<li><a href="#"><span class="glyphicon glyphicon-knight" aria-hidden="true"></span> OWNER</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> ASSISTANTS</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> MY ARCHIVE</a></li>
                                <li><a href="#"><span class="glyphicon glyphicon-signal" aria-hidden="true"></span> REVENUE</a></li> -->
                                <!--<li><a href="search.php"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> SEARCH</a></li> -->
                                <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="<?php echo IMAGE_PATH . 'avatar.png'; ?>" class="user-image" alt="User Image">
                                    <span class=""><?php echo $_SESSION['aib']['user_data']['user_login']; ?></span>
                                </a>
                                <ul class="dropdown-menu menuDropdown"> 
                                    <li>
                                        <a href="admin/manage_my_archive.php" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>Manage Your Account</a>
                                    </li>
                                    <li>
                                        <a class="logout-user" href="javascript:void(0);" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>Log out</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <li style="display:none;"><a href="javascript:void(0);" class="loginPopup"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> LOGIN</a></li>
                                <!--<li class="logout-user"><a href="javascript:void(0);"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> LOGOUT</a></li>-->
                            <?php }else{ ?>
                                <!--<li><a href="search.php"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> SEARCH</a></li> -->
                                
                                <li><a href="javascript:void(0);" class="loginPopup"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> LOGIN</a></li>
                                
                            <?php } ?>
                        </ul>
                        <!--<div class="pull-right responsive-menu">
                            <div class="imgMenu"><img src="<?php echo IMAGE_PATH . 'responsive-menu.png'; ?>">
                                <ul class="btn-header">
                                    <div class="arrow-up"></div>
                                    <?php if (isset($_SESSION['aib']['user_data']) && !empty($_SESSION['aib']['user_data'])) { ?>
                                        <li><a href="#"><span class="glyphicon glyphicon-knight" aria-hidden="true"></span> OWNER</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> ASSISTANTS</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> MY ARCHIVE</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-signal" aria-hidden="true"></span> REVENUE</a></li>
                                        <li><a href="#"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> SUPER ADMIN</a></li>
                                        <li class="logout-user"><a href="javascript:void(0);"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> LOGOUT</a></li>
                                    <?php }else{ ?>
                                        <li><a href="javascript:void(0);"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> LOGIN</a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>