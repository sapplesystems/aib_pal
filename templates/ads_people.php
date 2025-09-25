<style>
.bottom_right_position_home_default#bottom_right_position2{
                    position:initial;
                    padding: 0;
                    margin-top: 20px;
                }
</style>
<?php 
    $class = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'list_tree_items') ? 'widthFull': '';
?>
<div class="col-md-2 <?php echo $class; ?>">
    <div style="display:none;" id="home_page_register_your_society">
        <a href="why-us.html" title='Register' class='btn btn-success btn-register-society'>Register your society</a>
    </div>
    <div style="display:none;" class="marginTop20 adCreateBox" id="home_page_create_my_own_box"> 
        <a  href="javascript:void(0);" id="create_my_own_box" title="Create my own box">
            <div class="img-6"></div>
            <h3 class="text-center">Create my own box</h3>
        </a> 
    </div>
  <!--div class="ad text-right marginBottom10" id="adver"></div-->
  <div id="bottom_right_position2" class="ad_div bottom_right_position_home_default"></div>
                <div class="clearfix"></div>
                <div id="bottom_right_position" class="ad_div"></div>
</div>
