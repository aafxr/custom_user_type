<?php
if (!defineD('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CContactPopup extends \CBitrixComponent
{
    function OnPrepareComponentParams($arParams)
    {
        $this->arParams['CONTACT_ID'] = $arParams['CONTACT_ID'];
    }

    function executeComponent()
    {
        $this->arResult['CONTACT'] = $this->GetContact($this->arParams['CONTACT_ID']);
        $this->includeComponentTemplate();
    }

    function GetContact($contactID)
    {
        if (!isset($contactID)) return [];
        $arOrder = ['ID' => 'ASC'];
        $arFilter = ['ID' => $contactID,];
        $arSelect = ['*', 'UF_*'];
        $contact = \CCrmContact::GetList($arOrder, $arFilter, [], $arSelect)->fetch();
        if ($contact ) {
            $contact['PHONE'] = $this->loadFieldMulti($contact['ID'], \CCrmFieldMulti::PHONE );
            $contact['EMAIL'] = $this->loadFieldMulti($contact['ID'], \CCrmFieldMulti::EMAIL );
        }
        return $contact;
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
            $list[] = $this->transformMultiformFields($field);
        }
        return $list;
    }


    function transformMultiformFields($multifield){
        return [
            'ID' => $multifield['ID'],
            'TYPE_ID' => $multifield['TYPE_ID'],
            'VALUE' => $multifield['VALUE'],
            'VALUE_TYPE' => $multifield['VALUE_TYPE'],
        ];
    }

}