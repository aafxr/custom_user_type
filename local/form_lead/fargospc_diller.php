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
    '84' => 1,
    '85' => 2,
    '86' => 3,
    '87' => 4,
    '88' => 5,
    '89' => 6,
    '90' => 7,
    '91' => 8,
];

$selectedCompanyTypeNumber = $DROPDOWN_CONVERT_FIELDS[$request['form_dropdown_SIMPLE_QUESTION_283']];
$cityId = getUfCityListValueId($request['form_text_83']) ?? '0';

[$name, $secondName, $lastName] = explode(' ', $request['form_text_74']);

$options = [
    'CUSTOMER' => [
        'NAME' => $name.' '.$secondName,
        'LAST_NAME' => $lastName,
        'FULL_NAME' => $request['form_text_74'],
        'SOURCE_DESCRIPTION' => 'Пришел с '.FormLeadClass::SITE_QUARTZPARQUET,
    ],
    'COMPANY' => [
        'TITLE' =>  $request['form_text_93'],
        'COMPANY_TYPE' => getCompanyType($selectedCompanyTypeNumber),
        'UF_CITY_LIST' => getUfCityListValueId($request['form_text_83']),
        'UF_SOURCE_IB' => '3066',
    ],
    'PHONE' => $request['form_text_75'],
    'MAIL' => $request['form_text_92'],
    'RESPONSIBLE' => getLeadResponsible($cityId, $selectedCompanyTypeNumber),
];

$fl = new FormLeadClass($options);

$fl->createAndBind();

if($fl->hasErrors()){
    http_response_code(500);
    $result['ok'] = false;
    $result['message'] = $fl->getErrorMessage();
}