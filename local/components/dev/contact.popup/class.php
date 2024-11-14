<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

class CContactPopup extends \CBitrixComponent
{
    function onPrepareComponentParams($arParams)
    {
        $this->arParams['CONTACT_ID'] = $arParams['CONTACT_ID'];
        $this->arParams['QUIZ_FIELD'] = $arParams['QUIZ_FIELD'] ?? '';
        $this->arParams['PREFERENCES_FIELD'] = $arParams['PREFERENCES_FIELD'] ?? '';
        return $arParams;
    }

    function executeComponent()
    {
        $this->arResult['CONTACT_ID'] = $this->arParams['CONTACT_ID'];
        $this->arResult['CONTACT'] = $this->GetContact($this->arParams['CONTACT_ID']);
        $this->arResult['COMPONENT_PATH'] = $this->GetPath();
        $this->arResult['QUIZ_FIELD'] = $this->arParams['QUIZ_FIELD'];
        $this->arResult['PREFERENCES_FIELD'] = $this->arParams['PREFERENCES_FIELD'];
        $this->includeComponentTemplate();
    }

    function GetContact($contactID)
    {
        if (!isset($contactID)) return [];
        $arOrder = ['ID' => 'ASC'];
        $arFilter = ['ID' => $contactID,];
        $arSelect = ['*', 'UF_*'];
        $contacts = \CCrmContact::GetList($arOrder, $arFilter, [], $arSelect);
        $contact = $contacts->fetch();
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