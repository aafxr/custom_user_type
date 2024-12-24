<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
/** CUser */
global $USER;

//$USER->Authorize(1);

$result = ['request' => $_POST];

if (!\Bitrix\Main\Loader::includeModule('crm')) {
    http_response_code(500);
    $result['ok'] = false;
    $result['message'] = 'module crm not included';
    include('footer.php');
}


include('FormLeadClass.php');

$formLead = new FormLeadClass(FormLeadClass::SITE_QUARTZPARQUET, $_POST);
if ($formLead->createAndBind()) {
    $result['ok'] = true;
} else {
    $result['ok'] = false;
    $result['message'] = $formLead->getErrorMessage();
}

include('footer.php');