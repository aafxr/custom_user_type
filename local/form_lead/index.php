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


/*
    Салон напольных покрытий    UC_KUQTW0
    Салон отделочных материалов UC_BHXO1M
    Комплектование объектов     UC_L3LRS2
    Розничный покупатель        UC_O2JYI1
    Интернет-магазин            UC_2X9UMM
    Дизайнер                    COMPETITOR
    Архитектор                  UC_2HCJM7
    Другое                      OTHER
 */


    if (!\Bitrix\Main\Loader::includeModule('crm')) {
        http_response_code(500);
        $result['ok'] = false;
        $result['message'] = 'module crm not included';
        include('footer.php');
    }


    include('FormLeadClass.php');


    $contactSource = '';
    if (str_contains($_SERVER['HTTP_ORIGIN'], 'quartzparquet.ru'))
    {
        if(count(array_intersect_key([
            'form_text_1' => true,
            'form_text_3' => true,
            'form_text_4' => true,
            'form_dropdown_SIMPLE_QUESTION_964' => true,
        ],$request))){
            include 'qp_diller.php';
        }
    } elseif (str_contains($_SERVER['HTTP_ORIGIN'], FormLeadClass::SITE_FARGOSPC))
    {
        $contactSource = FormLeadClass::SITE_FARGOSPC;
    }


    include('footer.php');


} finally {
    if(!$isAuthorized) $USER->Logout();
}