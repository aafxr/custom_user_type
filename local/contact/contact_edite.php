<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

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
$APPLICATION->IncludeComponent(
    'dev:contact.popup',
    '',
    [
        'CONTACT_ID' => $contactID,
    ]
);
