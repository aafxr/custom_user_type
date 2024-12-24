<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$result = [];

if (!\Bitrix\Main\Loader::includeModule('crm')){
    http_response_code(500);
    $result['ok'] = false;
    $result['message'] = 'module crm not included';
    include ('footer.php');
}


$entity = new \CCrmCompany;

$result['request'] = $_REQUEST;

include ('footer.php');