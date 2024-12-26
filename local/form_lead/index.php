<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, authorization");
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    die();
}

define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
/** CUser */
global $USER;

$isAuthorized = $USER->IsAuthorized();

try {
    if(!$isAuthorized) $USER->Authorize(1);


    $result = [];


    $requestBody = file_get_contents('php://input');
    try{
        $request = json_decode($requestBody, true);
    }catch (Exception $e){}

    if (!$request) $request = $_POST;

    if(!count($request)){
        http_response_code(400);
        $result['ok'] = false;
        $result['message'] = 'bad request';
        include('footer.php');
    }


//    $result['ok'] = true;
//    $result['get'] = $_GET;
//    $result['post'] = $_POST;
//    $result['REQUEST'] = $_REQUEST;
//    $result['server'] = $_SERVER;
//    $result['body'] = $requestBody;
//    include('footer.php');


    if (!\Bitrix\Main\Loader::includeModule('crm')) {
        http_response_code(500);
        $result['ok'] = false;
        $result['message'] = 'module crm not included';
        include('footer.php');
    }


    include('FormLeadClass.php');


    $contactSource = '';
    if (str_contains($_SERVER['HTTP_ORIGIN'], FormLeadClass::SITE_QUARTZPARQUET))
    {
        $contactSource = FormLeadClass::SITE_QUARTZPARQUET;
    } elseif (str_contains($_SERVER['HTTP_ORIGIN'], FormLeadClass::SITE_FARGOSPC))
    {
        $contactSource = FormLeadClass::SITE_FARGOSPC;
    }


    $formLead = new FormLeadClass($contactSource, $request);
    if ($formLead->createAndBind()) {
        $result['ok'] = true;
    } else {
        $result['ok'] = false;
        $result['message'] = $formLead->getErrorMessage();
    }


    include('footer.php');


} finally {
    if(!$isAuthorized) $USER->Logout();
}