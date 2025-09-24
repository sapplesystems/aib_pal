<style>
.tree-sub-group{list-style: none;}
.innerBorder{border:1px solid #cccccc;}
</style>
<input type="hidden" name="selected_assistant" id="selected_assistant" value="<?php echo $assistant_id; ?>">
<div class="modal-header form_header">
    <h4 class="list_title">Managing Assignment for Assistant: <?php echo $assistantDetails['user_title']; ?> <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
</div>
<div class="modal-body">
<div class="innerBorder">
<div class="row">
    <div class="col-xs-4" style="padding-right:0px;">
        <div style="height: 435px; overflow: auto; border-right: 1px solid #cccccc;"><!-- box_public_info -->
            <h5 class="text-center">Available Sub Groups</h5>
            <?php
            include SERVER_ROOT_PATH . '/generate_tree.php';
            generateTree($_SESSION['aib']['user_data']['user_id'], $assistantAssignedArchive, 'assistant_tree');
            ?>
        </div>
    </div>
	<div class="col-xs-4" style="padding:0px;">
                <div style="height: 350px; overflow: auto;padding: 0 10px;border-bottom:1px solid #cccccc; border-right:1px solid #cccccc;"><!-- box_public_info -->
                    <h5 id="selected_from_available" class="text-center">Selected From Available Sub Groups</h5>
                    <div id="selected_from_available_subgroup"><div class="clearfix"></div></div>
                </div>
                <div class="text-center"> 
                    <button type="button" class="btn btn-info borderRadiusNone" name="assigned_assistant_to_subgroup" id="assigned_assistant_to_subgroup">Assign Selected<br>Sub Groups</button>	
                </div>
            </div>
			<div class="col-xs-4" style="padding-left:0px;">
                <div style="height: 350px; overflow: auto;padding: 0 10px;border-bottom:1px solid #cccccc;"><!-- box_public_info -->
                    <h5 id="assigned_sub_group" class="text-center">Assigned Sub Groups</h5>
                    <?php
                    if (!empty($assignedSubGroups)) {
                        foreach ($assignedSubGroups as $key => $dataArray) {
                            ?>
                            <?php if ($key != '') { ?>
                                <span style="display: block;" id="<?php echo $key; ?>"><input type="checkbox" checked="checked" class="form-check-input assigned-assistant" data-item-id="<?php echo $key; ?>" data-item-value="<?php echo $dataArray; ?>">&nbsp;<?php echo $dataArray; ?></span>
                                <?php
                            }
                        }
                    }
                    ?>
                </div> 
                <div class="text-center"> 
                    <!--<button type="button" class="btn btn-danger borderRadiusNone" data-dismiss="modal">Cancel</button>-->
                    <button type="button" class="btn btn-danger borderRadiusNone" id="unassigned-assistant">Unassign Selected<br>Sub Groups</button>
                </div>
            </div>
			</div>
    <div class="clearfix"></div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.tree-view li').each(function () {
            if ($(this).children('ul').length > 0) {
                $(this).addClass('parent');
            }
        });

        $('.tree-view li.parent > a').click(function ( ) {
            $(this).parent().toggleClass('active');
            $(this).parent().children('ul').slideToggle('fast');
        });
    });

</script>