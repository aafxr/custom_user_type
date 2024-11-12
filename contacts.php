<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main;
use \Bitrix\Crm;


function transformMultiformFields($multifield){
    return [
        'ID' => $multifield['ID'],
        'TYPE_ID' => $multifield['TYPE_ID'],
        'VALUE' => $multifield['VALUE'],
        'VALUE_TYPE' => $multifield['VALUE_TYPE'],
    ];
}

function loadFieldMulti($contactID, $fieldType){
    $resFieldMulti = \CCrmFieldMulti::GetListEx(
        [],
        [
            'ENTITY_ID' => \CCrmOwnerType::ContactName,
            'ELEMENT_ID' => $contactID,
            'TYPE_ID' => $fieldType
        ]
    );

    $list = [];
    while( $field = $resFieldMulti->fetch() ){
        $list[] = transformMultiformFields($field);
    }
    return $list;
}


if (!Main\Loader::IncludeModule('crm')) {
    echo "crm module not included";
    die();
}


$arOrder = ['ID' => 'ASC'];
$arFilter = ['COMPANY_ID' => '1',];
$arSelect = ['*', 'UF_*'];
$contacts = CCrmContact::getList(
    $arOrder,
    $arFilter,
    [],
    $arSelect
);

$list = [];

while ($contact = $contacts->fetch()) {
    $company['PHONE'] = loadFieldMulti($contact['ID'], CCrmFieldMulti::PHONE );
    $company['EMAIL'] = loadFieldMulti($contact['ID'], CCrmFieldMulti::EMAIL );
    $list[] = $contact;
}
echo json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
