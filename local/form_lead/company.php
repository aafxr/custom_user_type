<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$result = [];


$companyId = $_GET['companyId'];


if(!isset($companyId)){
    $result['message'] = 'need companyId';
    include 'footer.php';
}

if (!\Bitrix\Main\Loader::includeModule('crm')) {
    http_response_code(500);
    $result['ok'] = false;
    $result['message'] = 'module crm not included';
    include 'footer.php';
}

$oCompany = CCrmCompany::GetList(
    ['DATE_CREATE' => 'DESC'],
    ['ID' => $companyId],
    []
);


$arCompany = $oCompany->Fetch();


$result['company'] = $arCompany;



include "footer.php";
