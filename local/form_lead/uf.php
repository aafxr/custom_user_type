<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');


$result = [];
$rsUserFields = \Bitrix\Main\UserFieldTable::getList([
    'filter' => ['FIELD_NAME' => 'UF_SOURCE_IB'],
]);
while ($arUserField = $rsUserFields->fetch()) {
    $result = \CUserFieldEnum::getList([], [
        'USER_FIELD_ID' => $arUserField['ID'],
    ]);
}



//$rsUserFields = \Bitrix\Main\UserFieldTable::getList(array(
//    'order' => array('ENTITY_ID'=>'ASC','SORT'=>'ASC'),
//));
//
//$result = [];
//while($arUserField=$rsUserFields->fetch())
//{
//    $result [$arUserField['FIELD_NAME']] = $arUserField;
//}

include 'footer.php';