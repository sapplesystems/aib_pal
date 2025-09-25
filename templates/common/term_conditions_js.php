<?php
$terms_and_conditions_up_to_date = $_SESSION['aib']['user_data']['terms_and_conditions_up_to_date'];
$security_question_answers_status = $_SESSION['aib']['user_data']['accept_security_questions'];
if($terms_and_conditions_up_to_date && ($terms_and_conditions_up_to_date == 1 || $terms_and_conditions_up_to_date == '1')){
?>
<script type="text/javascript">
$(document).ready(function(){
	service_term_condition_popup();
	
	$(document).on('click','#agree_term_condition',function(){
		var user_id = $(this).attr('user-id'); 
		var security_question_answers_status = '<?php echo $security_question_answers_status;  ?>';
		$('.admin-loading-image').show();
		$.ajax({
			url:"services.php",
			type:'post',
			data:{mode: 'update_user_term_condition', user_id:user_id},
			success: function(data){
				var result = JSON.parse(data);
				if(result.status == 'success'){
					if(security_question_answers_status ==1){
						$.each(result.message, function (index, questionText) {
							var questionNumber = index + 1;
							var $questionHTML = $(`
									<div class="form-group">
									<label class="col-sm-6">Q${questionNumber}. ${questionText['item_title']}</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" name="answer[]" id="answer_${questionNumber}" placeholder="Enter answer" autocomplete="off">
										<input type="hidden" name="question_id[]" id="question_id_${questionNumber}" value="${questionText['item_id']}">
									</div>
									</div>
								`);
								$('#security_qus').append($questionHTML);
						});
						 
						$('#term_condition_of_services').modal('hide');
						$('#security_ques_ans').modal('show');
					}
					//location.reload();
					 $('.admin-loading-image').hide();
				 }
			},
			eroor:function(){
				showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1153)');
			}
		});
	});
});

function service_term_condition_popup(){
	$('.admin-loading-image').show();
	$.ajax({
		url: "services.php",
		type: "post",
		data: {mode: 'get_term_and_condition', user_id: 1},
		success: function (data){
			var result = JSON.parse(data);
			if(result.status == 'success'){ 
			$('#term_cond_data_value').html(result.message); 
				$('#term_condition_of_services').modal('show');
			}else{
				showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 1154)');
			}
			$('.admin-loading-image').hide();
		},
		error: function () {
			showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1155)');
		}
	});
}
</script>
<?php }else if($security_question_answers_status && ($security_question_answers_status == 1 || $security_question_answers_status == '1')){ ?>
<script type="text/javascript">
	security_question_answer_popup();

	function security_question_answer_popup()
	{
		$.ajax({
			url: "services.php",
			type: "post",
			data: {mode: 'get_questions_list'},
			success: function (data){
				var result = JSON.parse(data);
				if(result.status == 'success'){ 
					 var questionHTML='';
                    var questions_list = result.data;
					$.each(questions_list, function (index, questionText) {
                        var questionNumber = index + 1;
                        var $questionHTML = $(`<div class="form-group">
									<label class="col-sm-6">Q${questionNumber}. ${questionText['item_title']}</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" name="answer[]" id="answer_${questionNumber}" placeholder="Enter answer" autocomplete="off">
										<input type="hidden" name="question_id[]" id="question_id_${questionNumber}" value="${questionText['item_id']}">
									</div>
									</div> `);
                        $('#security_qus').append($questionHTML);
                    });
					$('#term_condition_of_services').modal('hide');
					$('#security_ques_ans').modal('show');
				}else{
					showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: 1154)');
				}
				$('.admin-loading-image').hide();
			},
			error: function () {
				showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1155)');
			}
		});
		
		$('#term_condition_of_services').modal('hide');
		$('#security_ques_ans').modal('show');
	}
</script>

<?php }?>