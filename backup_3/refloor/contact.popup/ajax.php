<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
/** CUser */
global $USER;

use \Bitrix\Crm\Binding\ContactCompanyTable;

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
if (isset($request['ID']) && $request['ID'] != '') $contactID = $request['ID'];
if (isset($request['NAME']) && $request['NAME'] != '') $arFields['NAME'] = $request['NAME'];
if (isset($request['LAST_NAME']) && $request['LAST_NAME'] != '') $arFields['LAST_NAME'] = $request['LAST_NAME'];
if (isset($request['POST']) && $request['POST'] != '') $arFields['POST'] = $request['POST'];
if (isset($request['COMPANY_ID']) && $request['COMPANY_ID'] != '') $arFields['COMPANY_ID'] = $request['COMPANY_ID'];
if (isset($request['COMMENTS'])) $arFields['COMMENTS'] = $request['COMMENTS'];

if (isset($request['UF_CRM_HAS_TG_REGISTRATION'])) $arFields['UF_CRM_HAS_TG_REGISTRATION'] = $request['UF_CRM_HAS_TG_REGISTRATION'];
if (isset($request['UF_CRM_60120C8A6BD67'])) $arFields['UF_CRM_60120C8A6BD67'] = $request['UF_CRM_60120C8A6BD67'];
if (isset($request['UF_CRM_SEMINAR'])) $arFields['UF_CRM_SEMINAR'] = $request['UF_CRM_SEMINAR'];
if (isset($request['UF_SEMINAR_QP'])) $arFields['UF_SEMINAR_QP'] = $request['UF_SEMINAR_QP'];
if (isset($request['BIRTHDATE'])) $arFields['BIRTHDATE'] = $request['BIRTHDATE'];




$PHONE = [];
$EMAIL = [];

if (isset($request['PHONE']) && $request['PHONE'] != '') $PHONE = $request['PHONE'];
if (isset($request['EMAIL']) && $request['EMAIL'] != '') $EMAIL = $request['EMAIL'];


function getMultiFields($contactID, $arFields, $typeID, $valueType = 'WORK'){
    $multiField = [
        'ENTITY_ID'  => \CCrmOwnerType::ContactName,
        'ELEMENT_ID' => $contactID,
        'TYPE_ID'    => $typeID,
        'VALUE_TYPE' => $valueType,
        'VALUE'      => $arFields['VALUE']
    ];
    if(isset($arFields['ID'])) $multiField['ID'] = $arFields['ID'];
    return $multiField;
}


$result['fields'] = [];
$oContact = new \CCrmContact(false);
$fm = new \CCrmFieldMulti();
if (!isset($contactID)){
    $contactID = $oContact->add($arFields);
    $company_id = $arFields['COMPANY_ID'];
    if(isset($company_id) && $contactID){
        ContactCompanyTable::bindCompanyIDs($contactID, [$company_id]);
    }
    foreach ($PHONE as $key => $value){
        $value = getMultiFields($contactID, $value, 'PHONE');
        if(!$fm->Add($value)){
            $result['errors'][] = $fm->LAST_ERROR;
        }
    }
    foreach ($EMAIL as $key => $value){
        $value = getMultiFields($contactID, $value, 'EMAIL');
        if(!$fm->Add($value)){
            $result['errors'][] = $fm->LAST_ERROR;
        }
    }
} else{
    $oContact->Update($contactID, $arFields);
    foreach ($PHONE as $key => $value){
        if($value['ID'] != ''){
            $value = getMultiFields($contactID, $value, 'PHONE');
            if(!$fm->Update($value['ID'],$value)){
                $result['errors'][] = $fm->LAST_ERROR;
            }
        } else{
            $value = getMultiFields($contactID, $value, 'PHONE');
            if(!$fm->Add($value)){
                $result['errors'][] = $fm->LAST_ERROR;
            }
        }
    }

    foreach ($EMAIL as $key => $value){
        if($value['ID'] != ''){
            $value = getMultiFields($contactID, $value, 'EMAIL');
            if(!$fm->Update($value['ID'],$value)){
                $result['errors'][] = $fm->LAST_ERROR;
            }
        } else{
            $value = getMultiFields($contactID, $value, 'EMAIL');
            if(!$fm->Add($value)){
                $result['errors'][] = $fm->LAST_ERROR;
            }
        }
    }
}

if ($contactID) $result['bindings'] = ContactCompanyTable::getContactCompanyIDs($contactID);

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
header('Content-type: application/json');
echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);





