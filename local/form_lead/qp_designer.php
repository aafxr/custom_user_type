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
$DROPDOWN_CONVERT_FIELDS = [
    '21' => 9,
    '22' => 6,
    '23' => 7,
    '24' => 10,
    '25' => 8,
];

$selectedCompanyTypeNumber = $DROPDOWN_CONVERT_FIELDS[$request['form_dropdown_SIMPLE_QUESTION_412']];
$cityId = getUfCityListValueId($request['form_text_18']) ?? '0';

[$name, $secondName, $lastName] = explode(' ', $request['form_text_15']);

$options = [
    'CUSTOMER' => [
        'NAME' => $name.' '.$secondName,
        'LAST_NAME' => $lastName,
        'FULL_NAME' => $request['form_text_15'],
        'SOURCE_DESCRIPTION' => 'Пришел с '.FormLeadClass::SITE_QUARTZPARQUET,
    ],
    'COMPANY' => [
        'TITLE' => $request['form_text_20'],
        'COMPANY_TYPE' => getCompanyType($selectedCompanyTypeNumber),
        'UF_CITY_LIST' => getUfCityListValueId($request['form_text_18']),
        'UF_SOURCE_IB' => '3065',
    ],
    'PHONE' => $request['form_text_16'],
    'MAIL' => $request['form_text_17'],
    'RESPONSIBLE' => getLeadResponsible($cityId, $selectedCompanyTypeNumber),
];

$fl = new FormLeadClass($options);

$fl->createAndBind();

if($fl->hasErrors()){
    http_response_code(500);
    $result['ok'] = false;
    $result['message'] = $fl->getErrorMessage();
}