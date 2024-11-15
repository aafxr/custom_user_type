<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

class CCompanyContacts extends \CBitrixComponent{
    function OnPrepareComponentParams($arParams){
        $this->arParams['COMPANY_ID'] = $arParams['COMPANY_ID'] ?? '';
        $this->arParams['QUIZ_FIELD'] = $arParams['QUIZ_FIELD'] ?? '';
        $this->arParams['PREFERENCES_FIELD'] = $arParams['PREFERENCES_FIELD'] ?? '';
        $this->arParams['USER_FIELD_NAME'] = $arParams['USER_FIELD_NAME'] ?? '';
        $this->arParams['EDITE_MODE'] = $arParams['EDITE_MODE'];
        $this->arParams['CACHE_TIME'] = 0;
        return $arParams;
    }

    function executeComponent(){
        $this->arResult['COMPANY_ID'] = $this->arParams['COMPANY_ID'];
        $this->arResult['CONTACTS'] = $this->GetContactsList($this->arParams['COMPANY_ID']);
        $this->arResult['QUIZ_FIELD'] = $this->arParams['QUIZ_FIELD'];
        $this->arResult['PREFERENCES_FIELD'] = $this->arParams['PREFERENCES_FIELD'];
        $this->arResult['USER_FIELD_NAME'] = $this->arParams['USER_FIELD_NAME'];
        $this->arResult['EDITE_MODE'] = $this->arParams['EDITE_MODE'];

        $this->includeComponentTemplate();
    }

    function GetContactsList($companyID)
    {
        if (!isset($companyID)) return [];
        $arOrder = ['ID' => 'ASC'];
        $arFilter = ['COMPANY_ID' => $companyID,];
        $arSelect = [];
        $list = [];
        $contacts = \CCrmContact::GetList($arOrder, $arFilter, $arSelect );
        while ($contact = $contacts->fetch()) {
            $contact['PHONE'] = $this->loadFieldMulti($contact['ID'], \CCrmFieldMulti::PHONE );
            $contact['EMAIL'] = $this->loadFieldMulti($contact['ID'], \CCrmFieldMulti::EMAIL );
            $list[] = $contact;
        }
        return $list;
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