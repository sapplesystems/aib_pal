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
            <h1>My Archive</h1>
            <ol class="breadcrumb">
              <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">My Archive</li>
            </ol>
           <h4 class="list_title">Manage Archive  </h4>
        </section>
        <section class="content bgTexture">
          
            <div class="row">
                <div class="col-md-12 tableStyle">
              
                      <table id="myTable" class="display table" width="100%" cellpadding="0" cellspacing="0">  
                      <thead>  
                        <tr>  
                          <th width="10%" class="text-center">ID</th>  
                          <th width="20%">Archive Group </th> 
                          <th width="20%">Archive Group Code </th>  
                          <th width="40%">Archive Group Title</th>  
                          <th width="10%" class="text-center">Actions</th>  
                        </tr>  
                      </thead>  
                      <tbody>  
                        <tr>  
                          <td>001</td>  
                          <td>First</td>
                          <td>087</td>  
                          <td>Test 1</td> 
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                      </tr>  
                        <tr>  
                          <td>002</td> 
                          <td>Second</td> 
                          <td>088</td>  
                          <td>Test 01</td> 
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        <tr>  
                          <td>003</td>
                          <td>First</td>  
                          <td>089</td>  
                          <td>Test 01</td>  
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                         <tr>  
                          <td>004</td>
                          <td>First</td>  
                          <td>090</td>  
                          <td>Test 01</td>  
                         <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td> 
                        </tr>  
                        <tr>  
                          <td>005</td>
                          <td>First</td>  
                          <td>091</td>  
                          <td>Test 01</td>  
                           <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        <tr>  
                          <td>006</td>
                          <td>Second</td>  
                          <td>092</td>  
                          <td>Test 01</td>  
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        
                         <tr>  
                          <td>007</td> 
                          <td>First</td> 
                          <td>093</td>  
                          <td>Test 01</td>  
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        <tr>  
                          <td>008</td>  
                          <td>First</td>
                          <td>094</td>  
                          <td>Test 01</td>  
                           <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        <tr>  
                          <td>009</td> 
                          <td>First</td> 
                          <td>095</td>  
                          <td>Test 01</td>  
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        
                          <tr>  
                          <td>010</td>  
                          <td>First</td>
                          <td>096</td>  
                          <td>Test 01</td>  
                          <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        <tr>  
                          <td>011</td> 
                          <td>First</td> 
                          <td>097</td>  
                          <td>Test 01</td>  
                         <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                        <tr>  
                          <td>012</td> 
                          <td>First</td> 
                          <td>098</td>  
                          <td>Test 01</td>  
                        <td>
                          <span class="" data-title="Edit" data-toggle="modal" data-target="#myModal" ><img src="public/images/edit_icon.png" alt="" /></span><span class="" id="delete" data-title="Delete" data-toggle="#delete" data-target="#delete" ><img src="public/images/delete_icon.png" alt="" /></span>
                          </td>
                        </tr>  
                      </tbody>  
                    </table>  
<!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header form_header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="list_title">Update Archive Record</h4>
        </div>
        <div class="modal-body">
          <!-- The form which is used to populate the item data -->
            <form id="recordsForm" method="post" class="form-horizontal" >
                <div class="form-group">
                    <label class="col-xs-4 control-label">Archive Id</label>
                    <div class="col-xs-7">
                        <input type="text" class="form-control" name="archive_id" disabled="disabled" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-4 control-label">Archive Group</label>
                    <div class="col-xs-7">
                        <input type="text" class="form-control" name="arc_group" />
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-4 control-label">Archive Group Code</label>
                    <div class="col-xs-7">
                        <input type="text" class="form-control" name="arc_group_code" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-4 control-label">Archive Group Title</label>
                    <div class="col-xs-7">
                        <input type="text" class="form-control" name="arc_group_title" />
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-4 control-label"></label>
                    <div class="col-xs-7">
                        <button type="button" class="btn btn-info borderRadiusNone" name="arc_group_btn">Update</button>
                        <button type="button" class="btn btn-danger borderRadiusNone" id="clearArchiveForm">Clear Form</button>
                    </div>
                </div>
            </form>

        </div>
       <!--  <div class="modal-footer">
          <button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal">Close</button>
        </div> -->
      </div>
      
    </div>
  </div>

                </div>
              
            </div>
        </section>
    </div>
   <?php  include_once COMMON_TEMPLATE_PATH.'footer.php'; ?>

<script type="text/javascript">

    $(document).ready(function(){
      $('#myTable').DataTable( );
           
        });
</script>
