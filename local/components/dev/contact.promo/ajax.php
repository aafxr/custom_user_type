<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
/** CUser */
global $USER;

$result = [];

if(!$USER->IsAuthorized()){
    http_response_code(401);
    $result = [
        'ok' => false,
        'message' => 'unauthorized'
    ];
    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    die();
}



$requestBody = file_get_contents('php://input');
try{
    $request = json_decode($requestBody, true);
    if(!$request['promoToAdd']) $request['promoToAdd'] = [];
    if(!$request['promoToRemove']) $request['promoToRemove'] = [];
}catch (Exception $e){
    http_response_code(400);
    $result = [
        'ok' => false,
        'message' => 'bad request'
    ];
    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    die();
}



if (!Bitrix\Main\Loader::IncludeModule('crm')) {
    http_response_code(500);
    $result = [
        'ok' => false,
        'message' => "crm module not included"
    ];
    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    die();
}


/*
 * загрузка существующих запсей о закрепленной за контактом промо-информации
*/
$arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById(20)->fetch();
$obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
$entityDataClass = $obEntity->getDataClass();

$rsData = $entityDataClass::getList(array(
    "select" => array("*"),
    "order" => array("ID" => "ASC"),
    "filter" => array(
        "UF_CONTACT_ID"=>$request['CONTACT_ID'],
        "UF_DELETED_AT" => null,
    )
));

$arContactPromo = [];
while($arData = $rsData->Fetch()){
    $arContactPromo[] = $arData;
}


function isPromoExist($list, $promoId){
    foreach ($list as $p){
        if($p['UF_PROMO_ID'] == $promoId) return true;
    }
    return false;
}


function removePromoFromArray($list, $promoId){
    foreach ($list as $k => $p){
        if($p['UF_PROMO_ID'] == $promoId) {
//            unset($list[$k]);
            return $p;
        }
    }
    return false;
}


foreach ($request['promoToAdd'] as $addPromo){
    if($addPromo['UF_PROMO_ID']){
        $ar = [
            'UF_CONTACT_ID' => $request['CONTACT_ID'],
            'UF_CREATED_AT' => \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime()),
            'UF_CREATED_BY' => $USER->GetID(),
            'UF_PROMO_ID' => $addPromo['UF_PROMO_ID'],
        ];
        $r = $entityDataClass::add($ar);
        if(!$r->isSuccess()){
            $result['message'][] = $r->getErrorMessages();
        }
    }
}


$result['request'] = $request;
$result['res'] = [];

foreach ($request['promoToRemove'] as $removePromo){
    if($removePromo['UF_PROMO_ID']){
        $res = removePromoFromArray($arContactPromo, intval($removePromo['UF_PROMO_ID']));
        if($res){
            $res['UF_DELETED_AT'] = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime());
            $res['UF_DELETED_BY'] = $USER->GetID();
            $r = $entityDataClass::update($res['ID'], $res);
            if(!$r->isSuccess()){
                $result['message'][] = $r->getErrorMessages();
            }
        }
        $result['res'][] = $res;
    }
}


$result['ok'] = true;

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

