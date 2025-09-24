<?php
        if (!empty($uncompleteResponseData)) {
            foreach ($uncompleteResponseData as $uncompleteData) {
                ?>
                  <div><b>Sub-Group: </b>  <?php echo $uncompleteData['sub_group']; ?></div>
              
            <?php }
        } 
 ?>
    
<?php  $flag = 0;
	 foreach($mydataarray as $key=>$itemlist){  ?>
	                 <div class="mydivs">
	                 <div class="tab loopshow_<?php echo $flag;?>" id="itemdiv">
                      <div><b>Record: </b> <span><?php echo $itemlist[0]['item_title']; ?></span></div>
                      <hr>                       
                             
                      <div class="form-group">
                           <label class="col-xs-3">Title :</label>
                             <div class="col-xs-8">
                              <input type="text" class="form-control" id="get_title_<?php echo $flag; ?>" name="get_item_title" value="<?php echo $itemlist[0]['item_title']; ?>" />
                             </div>
                      </div>
                      <div class="form-group">
                        <input type="hidden" name="item_id" id="item_id_<?php echo $flag; ?>" value="<?php echo $itemlist[0]['item_id']; ?>">
                      </div>
                     <div class="form-group">
                            <label class="col-xs-4 control-label"></label>
                            <div class="col-xs-7">
                                <button type="button" id="edit_item" get-id ="<?php echo $flag; ?>" class="btn btn-info borderRadiusNone" name="fields_btn">Save Record</button>
                                <button type="button" class="btn btn-danger borderRadiusNone" id="clearFieldsForm">Undo Changes</button>
                               
                            </div>
                        </div>

                     </div>            
</div>
	    	
	  <?php $flag++; }  ?>
 <button type="button" class="prevdiv btn btn-danger borderRadiusNone" id="clearFieldsForm">Prev</button>
 <button type="button" class="nextdiv btn btn-danger borderRadiusNone"  id="next_click_btn" style="float:right;" id="clearFieldsForm">Next</button>
   
    <script>
    $(document).ready(function () {
    var divs = $('.mydivs>div');
    divs.hide().first().show();
     $(document).on('click','.nextdiv',function(e){
        divs.eq(now).hide();
        now = (now + 1 < divs.length) ? now + 1 : 9;
        divs.eq(now).show(); // show next
        var lastChild = divs[divs.length - 1];
    });
     $(document).on('click','.prevdiv',function(e){
        divs.eq(now).hide();
        now = (now > 0) ? now - 1 : divs.length - 1;
        divs.eq(now).show(); 
    }); 
   
});
    </script>