<?php
if (!defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED !== true) die();
require_once ($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/custom/refloor_functions.php');

include 'FormLeadClass.php';
include 'getCompanyType.php';
include 'getLeadResponsible.php';

/** Array */
global $request;
/** Array */
global $result;


/** нормализация выподающего списка к 1 - 8 */
$QP_DROPDOWN_CONVERT_FIELDS = [
    '5' => 1,
    '6' => 2,
    '7' => 3,
    '8' => 4,
    '9' => 5,
    '10' => 6,
    '11' => 7,
    '12' => 8,
];

$selectedCompanyTypeNumber = $QP_DROPDOWN_CONVERT_FIELDS[$request['form_dropdown_SIMPLE_QUESTION_964']];
$cityId = getUfCityListValueId($request['form_text_4']) ?? '0';

[$name, $secondName, $lastName] = explode(' ', $request['form_text_1']);

$options = [
    'CUSTOMER' => [
        'NAME' => $name.' '.$secondName,
        'LAST_NAME' => $lastName,
        'FULL_NAME' => $request['form_text_1'],
        'SOURCE_DESCRIPTION' => 'Пришел с '.FormLeadClass::SITE_QUARTZPARQUET,
        ],
    'COMPANY' => [
        'TITLE' => '',
        'COMPANY_TYPE' => getCompanyType($selectedCompanyTypeNumber),
        'UF_CITY_LIST' => getUfCityListValueId($request['form_text_4']),
        'UF_SOURCE_IB' => '3064',
    ],
    'PHONE' => $request['form_text_2'],
    'MAIL' => $request['form_text_3'],
    'RESPONSIBLE' => getLeadResponsible($cityId, $selectedCompanyTypeNumber),
];

$fl = new FormLeadClass($options);

$fl->createAndBind();

if($fl->hasErrors()){
    http_response_code(500);
    $result['ok'] = false;
    $result['message'] = $fl->getErrorMessage();
}