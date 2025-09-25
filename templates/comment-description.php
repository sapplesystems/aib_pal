<?php
//echo "<pre>";print_r($commentList);
$comments_count = 0;
function printCommentRecursive($commentsThreads){
        //$commentsThreads = $comments['commentsThreads'];
        foreach ($commentsThreads as $commentsThread) {
            $title = explode("-", $commentsThread['item_title']);
            $userName = $title[0];
            $userId = isset($title[2])?$title[2]:"";
            $time = $commentsThread['item_create_stamp'];


            $html = '<div id="comment-' . $commentsThread['item_id'] . '" >
                        <span class="nameUser">
                            <!--<span class="glyphicon glyphicon-user" aria-hidden="true"></span> -->
							<img class="userPic" src="public/images/avatar-1.png" alt="" />
                                 ' . $userName . '
                         </span>
                         <span class="dateCmnt">- ' . date('d F Y H:i a', $time) . '</span>
                            <p class="comment_list" id="comment_list_'.$commentsThread['item_id'].'">
                            ' . $commentsThread['fields'][0]['field_value'] . '
                            </p>';
                 if(!empty($_SESSION['aib']['user_data']['user_id'])){
                      $html .= '<span class="comment_action">';
                      $html .= '<a href="javascript:void(0);" class="replyBtn-' . $commentsThread['item_id'] . '">Reply</a>';
                      if($_SESSION['aib']['user_data']['user_id']==$userId){
                        $html .= '<a href="javascript:void(0);" class="deleteBtn-' . $commentsThread['item_id'] . '">Delete</a>';
                        $html .= '<a href="javascript:void(0);" class="edit-comment" data-comment_id="'.$commentsThread['item_id'].'">Edit</a>';
                      }
                      $html .= '</span>';
                 }
						if($_SESSION['aib']['user_data']['user_id'] != $userId){
                        $html .= '<span class="comment_action"><a href="javascript:void(0);" class="report-comment" data-comment-id="'.$commentsThread['item_id'].'">Report</a></span>';
						}
                        $html .='<div id="edit_comment_'.$commentsThread['item_id'].'" style="display:none;">
                            <textarea class="widthTextarea submitEditComments" data-itemId="' . $commentsThread['item_id'] . '">'.$commentsThread['fields'][0]['field_value'].'</textarea>
                        </div>';
                        $html .='<div class="replyCmnt replyBox-' . $commentsThread['item_id'] . '" style="display:none;">
                             
							<textarea placeholder="Write a reply..." class="widthTextarea replyOnUserComment" data-itemId="' . $commentsThread['item_id'] . '"></textarea>
                        </div>

                        <span class="devideCmnt"></span>
            </div> ';
            echo $html;
            if(!empty($commentsThread['commentsThreads'])){
                echo '<div class="innerComment lastComment" style="padding-left:50px">';
                    printCommentRecursive($commentsThread['commentsThreads']);
                echo '</div>';
            }
    }//comment thread
    
}

  foreach ($commentList as $comments) {
    if (isset($comments['commentsThreads']) && count($comments['commentsThreads']) <= 0) {
        continue;
    } else {
        $comments_count++;
        if(empty($_REQUEST['get_count'])){
            printCommentRecursive($comments['commentsThreads']);
        }
        
    }//else if end
 }//comment thread loop end
 echo '<script type="text/javascript">$("#comments_count").html("('.$comments_count.')");</script>';

?>




