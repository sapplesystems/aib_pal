<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
    include_once COMMON_TEMPLATE_PATH.'header.php';
    include_once COMMON_TEMPLATE_PATH.'sidebar.php';
    ?> 
    <div class="content-wrapper">
        <section class="content-header"> 
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Display Ads</li>
            </ol>
        </section>
        <section class="content">
          
            <div class="row">
                <div class="col-md-offset-3 col-md-6 col-md-offset-3">

                   <h3>Display Ads</h3>
				   
                   
                </div>
              
            </div>
        </section>
    </div>
   <?php  include_once COMMON_TEMPLATE_PATH.'footer.php'; ?>
   