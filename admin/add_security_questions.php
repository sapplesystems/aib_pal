<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>My Archive</h1>
        <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Add Questions</li>
        </ol>
        <h4 class="list_title text-center"><span class="pull-left">Add Questions</span> <span class="headingNameDesign"><?php echo $_SESSION['aib']['user_data']['item_title'];?></span> </h4>
    </section>
    <section class="content bgTexture">
        <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
        <div class="row"> 
            <div class="col-md-12">
                <form class="" action="" method="POST" id="addQuestionsform" name="addQuestionsform">
                <div id="questionFields">
                    <div class="row marginTop20 padd5">
                        <div class="col-md-3 text-right" >
                            <label>Question :</label>
                            <p style="font-size: 12px;">(You can add upto 2 questions.)</p>
                        </div>
                        <div class="col-md-5" >
                            <input type="text" class="form-control question-input" name="question_1" placeholder="Enter question">
                        </div> 
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info" id="addformsQuestionButton" name="addformsQuestionButton">Add Question</button> &nbsp;
                            <button type="button" class="btn btn-warning clearAdminForm" id="clearformsForm02" name="clearformsForm02">Clear Question</button></div>
                    </div>
                     <!--<div class="row marginTop20 padd5">
                        <div class="col-md-4 text-right" >
                            <label>Question.2 :</label>
                        </div>
                        <div class="col-md-7" >
                            <input type="text" class="form-control question-input" name="question_2" placeholder="Enter question">
                        </div> 
                    </div>-->
                </div>
                     <!--<div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-success borderRadiusNone" id="addMoreQuestions">+ Add More</button>
                        </div>
                    </div>-->

                </form>
            </div>
              <div class="col-md-12 tableStyle marginTop40">
                  <table class="table">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Question</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="questions-list">
                        <!-- Content will be loaded here -->
                    </tbody>
                </table>

              </div>
        </div>
    </section>
</div>


<style>
    ul.shown {
        border: 1px solid #d4d4d4;
        list-style: none;
        line-height: 26px;
        padding-left: 8px;
        min-height: 192px;
    }
    .d-none {display: none;}
    #questions-list td{
        text-align:left;
        background-color:#ffffff;
    }
</style>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script type="text/javascript">
$(document).ready(function () {
  // ✅ Initialize validate
  var validator = $("#addQuestionsform").validate({
    rules: {
      "question_1": {
        required: true
      }
    },
    messages: {
      "question_1": {
        required: "Please enter a question"
      }
    },
    errorElement: "div",
    errorClass: "text-danger"
  });

  // ✅ Form submit check
  $('#addformsQuestionButton').click(function () {
        if ($("#addQuestionsform").valid()) {
            $('.admin-loading-image').show();
                var FormData = $("#addQuestionsform").serialize();
                $.ajax({
                    url: "services_admin_api.php",
                    type: "post",
                    data: {mode: 'add_security_questions', formData: FormData},
                    success: function (data) {
                        var result = JSON.parse(data);
                        $('.admin-loading-image').hide();
                        if (result.status == 'success') { 
                            showPopupMessage(result.status, result.message);
                            loadSecurityQuestions(); // Reload updated list
                            // setTimeout(function(){
                            //      window.location.href='add_security_questions.php';
                            // }, 2000);
                        } else { 
                            showPopupMessage('error', result.message + ' (Error Code: #0001)');
                        }
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again. (Error Code: #0001)');
                    }
                });
        } 
  });

  // ✅ Add more dynamic fields with validation
  $('#addMoreQuestions').click(function () {
    var newInput = $(`
      <div class="row marginTop20 padd5">
        <div class="col-md-4 text-right">
          <label>Question :</label>
        </div>
        <div class="col-md-7">
          <input type="text" class="form-control question-input" name="question[]" placeholder="Enter question">
        </div>
      </div>`);

    $('#questionFields').append(newInput);

    // ✅ Add validation to new input
    newInput.find('input').each(function () {
      $(this).rules("add", {
        required: true,
        messages: {
          required: "Please enter a question"
        }
      });
    });
  });

  function loadSecurityQuestions() {
    $.ajax({
        url: "services_admin_api.php",
        type: "post",
        data: {mode: 'get_security_questions'},
        success: function (data) {
            $('#questions-list').html(data);
        },
        error: function () {
            alert('Failed to load questions');
        }
    });
  }

  loadSecurityQuestions(); 

});

$(document).on('click', '.btn-edit', function () {
    var row = $(this).closest('tr');
    row.find('.view-title').addClass('d-none');
    row.find('.edit-title, .btn-save, .btn-cancel').removeClass('d-none');
    $(this).addClass('d-none');
});

$(document).on('click', '.btn-cancel', function () {
    var row = $(this).closest('tr');
    row.find('.edit-title, .btn-save, .btn-cancel').addClass('d-none');
    row.find('.btn-edit').removeClass('d-none');
    row.find('.view-title').removeClass('d-none');
});

$(document).on('click', '.btn-save', function () {
    var row = $(this).closest('tr');
    var itemId = row.data('id');
    var updatedTitle = row.find('.edit-title').val();
    console.log(itemId);
    console.log(updatedTitle);

    $('.admin-loading-image').show(); // Show loader

    $.ajax({
        url: 'services_admin_api.php',
        method: 'POST',
        data: {
            mode: 'update_security_question',
            item_id: itemId,
            item_title: updatedTitle
        },
        success: function (data) {
            $('.admin-loading-image').hide(); // Hide loader

            var result = JSON.parse(data);
            if (result.status === 'success') {
                showPopupMessage(result.status, result.message);

                // Update UI
                row.find('.view-title').text(updatedTitle);
                row.find('.edit-title, .btn-save, .btn-cancel').addClass('d-none');
                row.find('.view-title').removeClass('d-none');
                row.find('.btn-edit').removeClass('d-none');
            } else {
                showPopupMessage('error', result.message + ' (Error Code: #0001)');
            }
        },
        error: function () {
            $('.admin-loading-image').hide(); // Hide loader
            showPopupMessage('error', 'Something went wrong, Please try again. (Error Code: #0001)');
        }
    });
});

</script>