<?php
session_start();
if (empty($_SESSION['aib']['user_data'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/config.php';
if (isset($_REQUEST['item_id']) && $_REQUEST['item_id'] != '' && is_numeric($_REQUEST['item_id'])) {
    $item_id = $_REQUEST['item_id'];
    $requestData = array(
        "_id" => APIUSER,
        "_key" => APIKEY,
        "_user" => $_SESSION['aib']['user_data']['user_id'],
    );
    $sessionResult = aib_request($requestData,"session");
    $pdfRequestData["_session"] = $sessionResult["info"];
    $pdfRequestData["_key"] =APIKEY;
    $pdfRequestData["item"] = $item_id;
    $pdfRequestData["_user"] = $_SESSION['aib']['user_data']['user_id'];
    $pdfRequestData["title_type"] = "path";
    $pdfRequestData["sep_page_type"] = "alltext";
    $pdfRequestData["page_title_prefix_template"] = "<html><b>Item:</b></html>";
    $pdfRequestData["page_title_segment_sep_template"] = " => ";
    $pdfRequestData["page_title_segment_template"] = "<html>[[TITLE]]</html>";
    $pdfRequestData["page_title_suffix_template"] = "<html></html>";
    $pdfRequestData["opt_include_record"] = "Y";
    $pdfRequestData["record_summary_header"] = "<br><img src='/home/stparch/virtual_sites/aib_historicals/images/aiblogo.jpg' width='30%' height='0.5'><gotoxy x='0' y='0.75'>";
    $pdfRequestData["item_summary_header"] = "<br><img src='/home/stparch/virtual_sites/aib_historicals/images/aiblogo.jpg' width='30%' height='0.5'><gotoxy x='0' y='0.75'>";
    $resultPdfData = aib_request_raw($pdfRequestData,"make_pdf");
    if($resultPdfData['status'] == 'OK'){
        file_put_contents('tmp/'.$item_id.'.pdf', $resultPdfData['info']);
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$item_id.".pdf");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: binary");
        readfile('tmp/'.$item_id.'.pdf');
        unlink('tmp/'.$item_id.'.pdf');
        exit;
    }
}

function aib_request($LocalPostData, $FunctionSet) {
    $CurlObj = curl_init();
    $Options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => APIURL . $FunctionSet . ".php",
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
    return($OutData);
}

function aib_request_raw($LocalPostData, $FunctionSet) {
    $CurlObj = curl_init();
    $Options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => APIURL . $FunctionSet . ".php",
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
        $OutData = array("status" => "OK", "info" => $Result);
    }

    curl_close($CurlObj);
    return($OutData);
}
