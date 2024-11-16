<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


$APPLICATION->ShowHeadScripts();
$APPLICATION->ShowHead();


use Bitrix\Main\Loader;

global $APPLICATION;
global $USER;

if (!$USER->IsAuthorized()) {
    http_response_code(401);
    die();
}

if (!Loader::includeModule('crm')) {
    ShowError('Ошибка: Модуль CRM не подключен.');
    die();
}

$contactID = $_POST['contact_id'];
$companyID = $_POST['company_id'];
$APPLICATION->IncludeComponent(
    'dev:contact.popup',
    '',
    [
        'CONTACT_ID' => $contactID,
        'COMPANY_ID' => $companyID,
        'QUIZ_FIELD' => 'UF_CONTACT_QUIZ_AREA',
    ]
);
