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



$arFields = [];
if (isset($request['NAME']) && $request['NAME'] != '') $arFields['NAME'] = $request['NAME'];
if (isset($request['LAST_NAME']) && $request['LAST_NAME'] != '') $arFields['LAST_NAME'] = $request['LAST_NAME'];
if (isset($request['POST']) && $request['POST'] != '') $arFields['POST'] = $request['POST'];
if (isset($request['UF_CONTACT_PREFERENCES_AREA']) && is_array($request['UF_CONTACT_PREFERENCES_AREA'])) $arFields['UF_CONTACT_PREFERENCES_AREA'] = $request['UF_CONTACT_PREFERENCES_AREA'];
if (isset($request['ID']) && $request['ID'] != '') {
    $contactID = $request['ID'];
}

$PHONE = [];
$EMAIL = [];

if (isset($request['PHONE']) && $request['PHONE'] != '') $PHONE['PHONE'] = $request['PHONE'];
if (isset($request['EMAIL']) && $request['EMAIL'] != '') $EMAIL['EMAIL'] = $request['EMAIL'];


$oContact = new \CCrmContact(false);
if (isset($contactID)){
    $oContact->add($arFields);
} else{
    $oContact->Update($contactID, $arFields);
}
if($oContact->LAST_ERROR != ""){
    $result = [
        'ok' => false,
        'message' => $oContact->LAST_ERROR
    ];
} else{
    $result = [
        'ok' => true,
        'result' => $result
    ];
}

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);





