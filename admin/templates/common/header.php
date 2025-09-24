<?php
$cssArray = ['bootstrap.min.css', 'font-awesome.min.css', 'ionicons.min.css', 'jquery-jvectormap.css', 'AdminLTE.min.css', '_all-skins.min.css', 'bootstrap-datepicker.min.css', 'class.css', 'jquery.dataTables.min.css', 'jquery.Jcrop.css', 'style.min-jstree.css', 'aib.css','magicsuggest.css','colorpicker.css','fontselector.css'];
if($_SESSION['aib']['user_data']['user_type']=='U'){
    $browseUrl='people_profile.php?folder-id='.$_SESSION['aib']['user_data']['user_top_folder'];
}elseif($_SESSION['aib']['user_data']['user_type']=='A'){
    $browseUrl='society.php?folder-id='.$_SESSION['aib']['user_data']['user_top_folder'];
}else{
    $browseUrl='home.php?folder_id=1';
    $browseUrl='home.html?q=R2o0cm8xand2SnZwcXdqTU8zQXQ2NVF4RWJjdlRnLzBZS0Jrckc1NDNXST0=';
}
if($_SESSION['aib']['user_data']['user_type'] == 'S'){
	$logoUrl = 'index.php';	
}else{
	$logoUrl = 'manage_my_archive.php';
}
// fix start for issue id 0002472 on 25-June-2025
$userType = $_SESSION['aib']['user_data']['user_type'] ?? null;
$labelMap = [
    'R' => 'Owner Management',
    'A' => 'Client Management',
    'S' => 'Client Management',
    'U' => 'People Account Management',
    'X' => 'People Account Management',
];
$displayLabel = $labelMap[$userType] ?? 'Unknown Role';
// fix end for issue id 0002472 on 25-June-2025
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>AIB PAL | Dashboard</title>
        <link rel="shortcut icon" type="image/png" href="favicon.ico"/>
        <!--<link rel="shortcut icon" type="image/png" href="http://eg.com/favicon.png"/>-->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <?php foreach ($cssArray as $key => $fileName) { ?>
            <link rel="stylesheet" href="<?php echo CSS_PATH . $fileName; ?>">
        <?php } ?>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <style type="text/css">.jcrop-keymgr{display: none;}</style>
    </head>
   
    <?php
	if (isset($OnLoadCall) == true)
	{
		print("<body style='margin:0;' $OnLoadCall  class=\"hold-transition skin-blue sidebar-mini\">");
	}
	else
	{
		print("<body style='margin:0;'  class=\"hold-transition skin-blue sidebar-mini\">");
	}
?>
        <div class="wrapper">
            <header class="main-header">
                <a href="<?php echo $logoUrl;?>" class="logo">
                    <span class="logo-mini"><b>AIB</b></span>
                    <span class="logo-lg"><img src="<?php echo IMAGE_PATH . 'logo.png'; ?>" alt="" /></span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                    </a>
                    <!--<span class="imgClient">
                    <?php if (isset($_SESSION['archive_logo_image']) and $_SESSION['archive_logo_image'] != '') { ?>
                             <img height="50" src="<?php echo $_SESSION['archive_logo_image']; ?>" class="" alt="Historical Society">
                        </span>
                    <?php } else { ?>
                        <img height="50" src="<?php echo IMAGE_PATH . 'historical-society.png'; ?>" class="" alt="Historical Society"></span>
                    <?php } ?> -->
                    <!-- fix start for issue id 0002472 on 25-June-2025 -->
                    <h2 id="accountInfo"><?=$displayLabel;?></h2>
                    <!-- fix end for issue id 0002472 on 25-June-2025 -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="<?php echo IMAGE_PATH . 'avatar.png'; ?>" class="user-image" alt="User Image">
                                    <span class="hidden-xs"><?php echo $_SESSION['aib']['user_data']['user_login']; ?></span>
                                </a>
                                <ul class="dropdown-menu menuDropdown">
                                    <!-- User image -->
                                    <!--<li>
                                        <a href="./update_profile.php" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>Profile</a>
                                    </li> -->
									<?php if (isset($_SESSION['aib']['previous_user_data']) && !empty($_SESSION['aib']['previous_user_data'])) { ?>
                                        <li>
                                            <a href="javascript:void(0);" class="btn btn-default btn-flat resume-session" resume-user-id="<?php echo $_SESSION['aib']['previous_user_data']['user_id']; ?>"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>Resume session</a>
                                        </li>
                                    <?php } ?>
									<li>
                                        <a class="browse_home" target="_blank" href="../<?php echo $browseUrl;?>" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>Browse</a>
                                    </li>
                                    <li>
                                        <a href="admin.php?mode=logout" class="btn btn-default btn-flat"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>Sign out</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                </nav>
            </header>    