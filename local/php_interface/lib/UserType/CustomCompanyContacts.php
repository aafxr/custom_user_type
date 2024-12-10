<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();


use \Bitrix\Main\EventManager;
use \Bitrix\Main\UserField\TypeBase;
use \Bitrix\Main\UI\Extension;


class CustomCompanyContacts extends TypeBase
{

    const USER_TYPE_ID = 'custom_company_contacts';

    static function GetUserTypeDescription(): array
    {
        return array(
            "PROPERTY_TYPE"		=>"S",
            'USER_TYPE_ID' => CustomCompanyContacts::USER_TYPE_ID,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => "контакты компании",
            'QUIZ_FIELD' => 'UF_CONTACT_QUIZ_AREA',
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
            'DEFAULT_VALUE' => "-",
            'EDIT_CALLBACK' => [__CLASS__, 'GetPublicView'],
            'VIEW_CALLBACK' => [__CLASS__, 'GetPublicView'],
        );
    }


    static function GetDBColumnType($arUserField = []): string
    {
        return "varchar(10)";
    }


    function PrepareSettings($arUserField) {
        return $arUserField;
    }

    static function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm){

        $name = 'DEFAULT_VALUE';
        $value = '-';
        if($arUserField && isset($arUserField['SETTINGS']) && isset($arUserField['SETTINGS']['DEFAULT_VALUE'])){
            $value = $arUserField['USER_TYPE']['DEFAULT_VALUE'];
        }
        $result = '
      <tr style="vertical-align: middle;">
         <td>Значение по умолчанию:</td>
         <td>
            '.'<input type="text" name="' . $name . '" value="'.$value.'"/>'.'
         </td>
      </tr>
      ';
        return $result;
    }




    public static function GetPublicView($arUserField, $arAdditionalParameters = array()): string
    {
        global $APPLICATION;
        $companyID = $arUserField['ENTITY_VALUE_ID'];
        ob_start();
        $APPLICATION->IncludeComponent(
            'refloor:company.contacts',
            '',
            [
                /*'AJAX_MODE' => 'Y',*/
                'COMPANY_ID' => $companyID,
                'QUIZ_FIELD' => $arUserField['USER_TYPE']['QUIZ_FIELD'],
                'USER_FIELD_NAME' => $arUserField['FIELD_NAME'],
                'EDITE_MODE' => $arAdditionalParameters['CONTEXT'] == 'UI_EDITOR' && $arAdditionalParameters['mode'] =="main.edit",
            ]
        );
        $res = ob_get_contents();
        ob_clean();
        return $res;
    }


    static function OnBeforeSave($arUserField, $value){
//        CustomCompanyContacts::log('OnBeforeSave',$arUserField, $value);
        return $value ?? $arUserField['VALUE'] ?? $arUserField['USER_TYPE']['DEFAULT_VALUE'] ?? 'N';
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