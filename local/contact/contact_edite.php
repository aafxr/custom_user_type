<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;
global $USER;

if (!$USER->IsAuthorized()) {
    http_response_code(401);
    die();
}

$contactID = $_GET['contact_id'];

$APPLICATION->IncludeComponent(
    'dev:contact.popup',
    '',
    [
        'CONTACT_ID' => $contactID,
    ]
);
