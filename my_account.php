<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?> 
<div class="content-wrapper">

    <section class="content-header"> 
        <h1>Owner</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Owner</li>
        </ol>
        <h4 class="list_title">My Account</h4>
    </section>
    <section class="content">

        <div class="row">
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">

                <form class="marginBottom30 formStyle"  name="my_accountForm" id="my_accountForm" method="POST">

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Login :</strong></div>
                        <div class="col-md-7">Root</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>Full Name :</strong></div>
                        <div class="col-md-7"><input type="text" class="form-control" id="full_name"  name="full_name" placeholder="Text input">
                        <p class="ipt_text">Full name, title or position </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>New Password :</strong></div>
                        <div class="col-md-7"><input type="password" class="form-control"  id="new_pswd"  name="new_pswd" placeholder="Password">
                         <p class="ipt_text">Enter a new password to change your current password  </p>
                         </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 text-right"><strong>New Confirm Password  :</strong></div>
                        <div class="col-md-7"><input type="password" class="form-control" id="new_cnfrm_paswd"  name="new_cnfrm_paswd" placeholder="Confirm Password"></div>
                    </div>

                    <div class="row">
					<div class="col-md-4"></div>
                        <div class="col-md-7"><button type="button" class="btn btn-info borderRadiusNone" id="myAccountFormButton">Save Changes</button> &nbsp;
                            <button type="button" class="btn btn-warning borderRadiusNone" id="undomyForm">Undo Changes</button></div>
                    </div>


                </form>
            </div>

        </div>
    </section>
</div>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php';  