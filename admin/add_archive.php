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
          <h1>Archive</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Archive</li>
            </ol>
            <h4 class="list_title">Add New Archive </h4>
        </section>
        <section class="content">
          
            <div class="row">
                <div class="col-md-offset-3 col-md-6 col-md-offset-3">
 
                    <form class="marginBottom30 formStyle" name="add_archiveData" id="add_archiveData" method="POST">

						<div class="row">
						<div class="col-md-4 text-right"><strong>Archive Group:</strong></div>
						<div class="col-md-7 col-sm-6 col-xs-12">
						<span class="custom-dropdown">
							<select class="form-control" id="arch_grp_name"  name="arch_grp_name">
								<option value="">- Select -</option> 
								<option value="1">First</option> 
								<option value="2">Second</option> 
							</select>
						</span>
						</div> 
						</div>
						
					 	<div class="row">
						<div class="col-md-4 text-right"><strong>Archive Code :</strong></div>
						<div class="col-md-7"><input type="text" class="form-control"  id="arch_code"  name="arch_code" placeholder="Text input"></div>
						</div>


						<div class="row">
						<div class="col-md-4 text-right"><strong>Archive Group Title :</strong></div>
						<div class="col-md-7"><input type="text" class="form-control"  id="arch_grp_titl"  name="arch_grp_titl" placeholder="Text input"></div>
						</div>

						<div class="row">
						<div class="col-md-4"></div>
						<div class="col-md-7"><button type="button" class="btn btn-info borderRadiusNone" id="addArchiveFormBtn">Add Archive</button> &nbsp;
						<button type="button" class="btn btn-warning borderRadiusNone" id="clearArchiveForm">Clear Form</button></div>
						</div>

                   </form>
                </div>
              
            </div>
        </section>
    </div>
   <?php  include_once COMMON_TEMPLATE_PATH.'footer.php'; ?>
   