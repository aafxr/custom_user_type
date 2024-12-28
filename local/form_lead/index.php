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
    if (!$isAuthorized) $USER->Authorize(1);


    $result = ['ok' => true];


    $requestBody = file_get_contents('php://input');
    try {
        $request = json_decode($requestBody, true);
    } catch (Exception $e) {
    }

    if (!$request) $request = $_POST;

    if (!count($request)) {
        http_response_code(400);
        $result['ok'] = false;
        $result['message'] = 'bad request';
        include('footer.php');
    }


    if (!\Bitrix\Main\Loader::includeModule('crm')) {
        http_response_code(500);
        $result['ok'] = false;
        $result['message'] = 'module crm not included';
        include('footer.php');
    }


    if (str_contains($_SERVER['HTTP_ORIGIN'], 'quartzparquet.ru')) {
        if (
            count(array_intersect_key([
                'form_text_1' => true,
                'form_text_3' => true,
                'form_text_4' => true,
                'form_text_13' => true,
                'form_dropdown_SIMPLE_QUESTION_964' => true,
            ], $request))
        ) {
            include 'qp_diller.php';
        } elseif (
            count(array_intersect_key([
                'form_text_15' => true,
                'form_text_16' => true,
                'form_text_17' => true,
                'form_text_18' => true,
                'form_text_20' => true,
                'form_dropdown_SIMPLE_QUESTION_412' => true,
            ], $request))
        ){
            include 'qp_designer.php';
        }else{
            http_response_code(500);
            $result['ok'] = false;
            $result['message'] = 'no much form';
        }
    } elseif (str_contains($_SERVER['HTTP_ORIGIN'], 'fargospc.ru')) {
        if (
            count(array_intersect_key([
                'form_text_74' => true,
                'form_text_75' => true,
                'form_text_92' => true,
                'form_text_83' => true,
                'form_text_93' => true,
                'form_dropdown_SIMPLE_QUESTION_283' => true,
            ], $request))
        ) {
            include 'fargospc_diller.php';
        } elseif (
            count(array_intersect_key([
                'form_text_95' => true,
                'form_text_96' => true,
                'form_text_97' => true,
                'form_text_98' => true,
                'form_text_107' => true,
                'form_dropdown_SIMPLE_QUESTION_283' => true,
            ], $request))
        ){
            include 'fargospc_designer.php';
        }else{
            http_response_code(500);
            $result['ok'] = false;
            $result['message'] = 'no much form';
        }
    } else{
        http_response_code(500);
        $result['ok'] = false;
        $result['message'] = 'no handle form';
    }


    include('footer.php');


} finally {
    if (!$isAuthorized) $USER->Logout();
}