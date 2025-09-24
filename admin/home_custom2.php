<div class="content2">
    <div class="container-fluid">
        <div class="row-fluid posRelative">
            <style>
                .arrow_slide{
                    position: absolute;
                    z-index: 1;
                    top: 22px;
                    left:-2px;
                }
                #arrow_slide_left{
                    font-size: 22px;
                    margin-right: 0px;
                    cursor: pointer;
                    float: left;
                }
                #arrow_slide_right{
                    font-size: 22px;
                    margin-right: 0px;
                    cursor: pointer;
                    float: left;
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
            <?php include_once TEMPLATE_PATH . 'ads.php'; ?>
        </div>   
    </div>
</div>