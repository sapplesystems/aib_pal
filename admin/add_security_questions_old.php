<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
include_once COMMON_TEMPLATE_PATH . 'sidebar.php';
function aib_request($LocalPostData, $FunctionSet)
{
    $CurlObj = curl_init();
    $Options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => AIB_SERVICE_URL . "/api/" . $FunctionSet . ".php",
        CURLOPT_FRESH_CONNECT => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 0,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_POSTFIELDS => http_build_query($LocalPostData)
    );

    curl_setopt_array($CurlObj, $Options);
    $Result = curl_exec($CurlObj);
    if ($Result == false) {
        $OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
    } else {
        $OutData = json_decode($Result, true);
    }

    curl_close($CurlObj);
    return ($OutData);
}

function aib_get_session_key()
{
    if (!isset($_SESSION['aib']['session_key'])) {
        $postData = array(
            "_id" => APIUSER,
            "_key" => APIKEY
        );
        $apiResponse = aib_request($postData, 'session');
        if ($apiResponse['status'] == 'OK' && $apiResponse['info'] != '') {
            $sessionKey = $_SESSION['aib']['session_key'] = $apiResponse['info'];
        }
    } else {
        $sessionKey = $_SESSION['aib']['session_key'];
    }
    return ($sessionKey);
}
$postData = array(
    "_key" => APIKEY,
    "_session" => aib_get_session_key(),
    "_user" => 1,
    "_op" => "list",
    "parent" => SECURITY_QUESTION_PARENT_ID
);
$apiResponse = aib_request($postData, 'browse');
// echo "<pre>";print_r($apiResponse);
$security_questions = !empty($apiResponse['info']['records']) ? $apiResponse['info']['records'] : "";
// echo "<pre>";print_r($security_questions);die;
?>
<style>
  .d-none { display: none; }
</style>
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
            <div class="col-md-offset-3 col-md-6 col-md-offset-3">
                <form class="" action="" method="POST" id="addQuestionsform" name="addQuestionsform">
                <div id="questionFields">
                    <div class="row marginTop20 padd5">
                        <div class="col-md-4 text-right" >
                            <label>Question :</label>
                        </div>
                        <div class="col-md-7" >
                            <input type="text" class="form-control question-input" name="question" placeholder="Enter question">
                        </div> 
                    </div>
                </div>
                     <!--<div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-success borderRadiusNone" id="addMoreQuestions">+ Add More</button>
                        </div>
                    </div>-->
                    <br />

                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-info borderRadiusNone" id="addformsQuestionButton" name="addformsQuestionButton">Add Question</button> &nbsp;
                            <button type="button" class="btn btn-warning  borderRadiusNone clearAdminForm" id="clearformsForm02" name="clearformsForm02">Clear Question</button></div>
                    </div>

                </form>
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
</style>
<?php include_once COMMON_TEMPLATE_PATH . 'footer.php'; ?>

<script type="text/javascript">
$(document).ready(function () {
  // ✅ Initialize validate
  // var validator = $("#addQuestionsform").validate({
  //   rules: {
  //     "question": {
  //       required: true
  //     }
  //   },
  //   messages: {
  //     "question": {
  //       required: "Please enter a question"
  //     }
  //   },
  //   errorElement: "div",
  //   errorClass: "text-danger"
  // });

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
                            // setTimeout(function(){
                            //      window.location.href='add_security_questions.php';
                            // }, 2000);
                        } else { 
                            showPopupMessage('error', result.message + ' (Error Code: 365)');
                        }
                    },
                    error: function () {
                        showPopupMessage('error','Something went wrong, Please try again. (Error Code: 366)');
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
});


    $('.btn-edit').on('click', function () {
      alert('edit started');
        // const row = $(this).closest('tr');
        // row.find('.view-title').hide();
        // row.find('.edit-title').removeClass('d-none');
        // row.find('.btn-edit').hide();
        // row.find('.btn-save, .btn-cancel').removeClass('d-none');
    });

    $('.btn-cancel').on('click', function () {
        const row = $(this).closest('tr');
        row.find('.edit-title').addClass('d-none');
        row.find('.view-title').show();
        row.find('.btn-save, .btn-cancel').addClass('d-none');
        row.find('.btn-edit').show();
    });

    $('.btn-save').on('click', function () {
        const row = $(this).closest('tr');
        const itemId = row.data('id');
        const newTitle = row.find('.edit-title').val();

        // Send via AJAX
        $.post('<?= base_url('items/update-title') ?>', {
            item_id: itemId,
            item_title: newTitle
        }, function (response) {
            if (response.success) {
                row.find('.view-title').text(newTitle).show();
                row.find('.edit-title').addClass('d-none');
                row.find('.btn-save, .btn-cancel').addClass('d-none');
                row.find('.btn-edit').show();
            } else {
                alert('Update failed');
            }
        }, 'json');
    });
	
</script>