<div class="content2">
    <div class="container-fluid">
        <div class="row posRelative">
            <style>
                .arrow_slide{
                    position: absolute;
                    z-index: 1;
                    top: 22px;
                    left:-2px;
					display:none;
                }
                #arrow_slide_left{
                    font-size: 22px;
                    margin-right: 0px;
                    cursor: pointer;
                    float: left;
					display:none;
                }
                #arrow_slide_right{
                    font-size: 22px;
                    margin-right: 0px;
                    cursor: pointer;
                    float: left;
					display:none;
                }
				
                .bottom_right_position_home_default#bottom_right_position2{
                    position:initial;
                    padding: 0;
                    margin-top: 20px;
                }
            </style>
            <div class="arrow_slide" id="arrow_slide_left">
                <span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span>
            </div>
            <div class="arrow_slide hide" id="arrow_slide_right">
                <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
            </div>
            <div id="dynamic-tree-content" class="col-md-2 col-sm-2 leftModule"></div>
            <?php //include_once TEMPLATE_PATH . 'home-content.php';  ?>
            <div class="col-md-8 col-sm-8 bgTexture <?php echo $template_breadcrumb_class; ?>" style="background:#f7f7f7;">
                <div id="dynamic-home-content" class="col-md-12 col-sm-12"></div>
                <!--div class="col-md-3 col-sm-3" id="connection_list">
                     <div class="historical_connection_list">
                        <div class="historical_head">Historical Connections</div>
                        <div id="historical_connections_listing"></div>
                    </div>
                </div-->
            </div>
            <input type="hidden" name="current-item-id" id="current-item-id" value="">
            <input type="hidden" name="previous-item-id" id="previous-item-id" value="">
            <div class="clearDiv"></div>
            <?php //include_once TEMPLATE_PATH . 'ads.php'; ?>
            <?php 
                $class = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'list_tree_items') ? 'widthFull': '';
            ?>
            <div class="col-md-2 col-sm-2 custom_ad_width <?php echo $class; ?>">
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
        </div>   
    </div>
</div>