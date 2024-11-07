<?php


require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main;
use \Bitrix\Crm;

if ( !Main\Loader::IncludeModule('crm') )
{
    echo "crm module not included";
    die();
}

$body = file_get_contents('php://input');


$request = [];
$response = [];
try {
    $request = json_decode($body, true);
    if(!isset($request['field']) || !isset($request['value'])) throw new Exception('bed request');
}catch (Exception $e){
    $response = [
        'ok' => false,
        'message' => 'bed request',
    ];
    http_response_code(400);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

$companies = Crm\CompanyTable::getList([ 'select' => ['*', 'UF_*'] ]);

$field = $request['field'];
$value = $request['value'];

foreach ($companies as $company) {
    if (!isset($company[$field])){
        $c = new CCrmCompany();
        $array = array($field => $value);
        $c->Update($company['ID'], $array);
    }
}

$response = ['ok'=> true, 'request' => $request];
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);



