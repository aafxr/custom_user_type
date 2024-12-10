<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

class CContactPopup extends \CBitrixComponent
{
    function onPrepareComponentParams($arParams)
    {
//        CContactPopup::log('onPrepareComponentParams', $arParams );
        $this->arParams['CONTACT_ID'] = $arParams['CONTACT_ID'];
        $this->arParams['COMPANY_ID'] = $arParams['COMPANY_ID'];
        $this->arParams['COMMENT_FIELD'] = $arParams['COMMENT_FIELD'] ?? '';
        $this->arParams['QUIZ_FIELD'] = $arParams['QUIZ_FIELD'] ?? '';
        $this->arParams['PREFERENCES_FIELD'] = $arParams['PREFERENCES_FIELD'] ?? '';
        return $arParams;
    }

    function executeComponent()
    {
//        CContactPopup::log('executeComponent', $this->arParams, $this->arResult);
        $this->arResult['COMPANY_ID'] = $this->arParams['COMPANY_ID'];
        $this->arResult['CONTACT_ID'] = $this->arParams['CONTACT_ID'];
        $this->arResult['COMMENT_FIELD'] = $this->arParams['COMMENT_FIELD'];
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
        $arSelect = [];
        $contacts = \CCrmContact::GetList($arOrder, $arFilter, $arSelect);
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

//    static function log($title = '', ...$params){
//        $log = "<div class=\"section\"><h4 class=\"title\">$title</h4>";
//        foreach ($params as $k => $param){
//            $log .= '<pre class="code">'.json_encode($param, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</pre>';
//        }
//        $log .= "</div>\n";
//        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.log', $log, FILE_APPEND);
//    }
}