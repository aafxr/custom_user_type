<?php


use \Bitrix\Main\EventManager;
use \Bitrix\Main\UserField\TypeBase;
use \Bitrix\Main\UI\Extension;


//$eventManager = EventManager::getInstance();
//$eventManager->addEventHandlerCompatible('main', 'OnUserTypeBuildList', array('CustomCompanyContacts', 'GetUserTypeDescription'));


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
            'PREFERENCES_FIELD' => 'UF_CONTACT_PREFERENCES_AREA',
            'QUIZ_FIELD' => 'UF_CONTACT_QUIZ_AREA',
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
            'DEFAULT_VALUE' => "-",
            'EDIT_CALLBACK' => [__CLASS__, 'GetPublicEdit'],
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
        $res = '';
        $companyID = $arUserField['ENTITY_VALUE_ID'];
        $list = CustomCompanyContacts::GetContactsList($companyID);
        foreach ($list as $k => $contact) {
            $preferencesField = $arUserField['USER_TYPE']['PREFERENCES_FIELD'];
            if(isset($contact[$preferencesField]) && $contact[$preferencesField] != false){
                $preferences = '';
                foreach ($contact[$preferencesField] as $k => $pref){
                    $pref = explode(':',$pref);

                    $preferences .= '<span>'.$pref[0] . (end($pref) == 'да' ? "<span class=\"yes\">да</span>": "<span class=\"no\">нет</span>").',</span>';
                }
            }

            $quizField = $arUserField['USER_TYPE']['QUIZ_FIELD'];
            if(isset($contact[$quizField]) && $contact[$quizField] != false){
                $quiz = '';
                foreach ($contact[$quizField] as $k => $q){
                    $quiz .= '<span>'.$q.',</span>';
                }
            }

            if(isset($contact['PHONE']) && is_array($contact['PHONE'])){
                $phones = '';
                foreach ($contact['PHONE'] as $k => $phone){
                    $phones .= '<div  class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone" >'.$phone['VALUE'].'</div>';
                }
            }

            if(isset($contact['EMAIL']) && is_array($contact['EMAIL'])){
                $emails = '';
                foreach ($contact['EMAIL'] as $k => $email){
                    $emails .= '<div  class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-email" >'.$email['VALUE'].'</div>';
                }
            }


            $res .= '<div class="crm-entity-widget-client-block">
                        <div class="crm-entity-widget-client-box crm-entity-widget-participants-block" >
                            <div class="crm-entity-widget-client-box-name-container" >
                                <div class="crm-entity-widget-client-box-name-row">
                                    <a class="crm-entity-widget-client-box-name" href="/crm/contact/details/'.$contact['ID'].'/" >'.$contact['NAME'] . ' ' . $contact['LAST_NAME'].'</a>
                                    <div class="crm-entity-widget-client-actions-container">
                                        <a class="crm-entity-widget-client-action-call crm-entity-widget-client-action-available" ></a >
                                        <a class="crm-entity-widget-client-action-mail crm-entity-widget-client-action-available"></a>
                                        <a class="crm-entity-widget-client-action-im"></a>
                                    </div>
                                </div>
                            </div>
                            '.(isset($preferences) ? '<div class="crm-entity-widget-client-box-preferences">'.$preferences.'</div>' : '').
                            '<div class="crm-entity-widget-client-box-position">'.$contact['POST'].'</div>
                            <div class="crm-entity-widget-client-contact">
                                '.($phones ?? '').'
                                '.($emails ?? '').'
                            </div>
                            '.(isset($quiz) ? '<div class="crm-entity-widget-client-box-quiz">'.$quiz.'</div>' : '').'
                            <div class="crm-entity-widget-client-address"></div>
                        </div>
                    </div>' ;
        }

        return '<div class="crm-entity-widget-content-block-inner crm-entity-widget-inner">
                    <div class="crm-entity-widget-content-block-inner-container">
                        <div class="crm-entity-widget-content-block-title">
                            <span class="crm-entity-widget-content-subtitle-text">
                                <span>Контакты, связанные с компанией</span>
                                <span class="crm-entity-card-widget-title-edit-icon"></span>
                            </span>
                        </div>
                        '.$res.'
                    </div>
                </div>';
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array()): string
    {
        $name = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = $arUserField['VALUE'] ?? $arUserField['USER_TYPE']['DEFAULT_VALUE'] ?? 'NP';

        $res = '';
        $companyID = $arUserField['ENTITY_VALUE_ID'];
        $list = CustomCompanyContacts::GetContactsList($companyID);
        foreach ($list as $k => $contact) {
            $res .= '<div class="crm-entity-widget-client-box-name-container" >
                        <div class="crm-entity-widget-client-box-name-row">
                            <a class="crm-entity-widget-client-box-name" href="/crm/contact/details/'.$contact['ID'].'/" >'.$contact['NAME'] . ' ' . $contact['LAST_NAME'].'</a>
                        </div>
                    </div>';
        }
        return '<input type="hidden" name="' . $name . '" value="'.$value.'"/>' .
                '<div class="crm-entity-widget-content-block-inner crm-entity-widget-inner">
                    <div class="crm-entity-widget-content-block-inner-container">
                        <div class="crm-entity-widget-content-block-title">
                            <span class="crm-entity-widget-content-subtitle-text">
                                <span>Контакты, связанные с компанией</span>
                                <span class="crm-entity-card-widget-title-edit-icon"></span>
                            </span>
                        </div>
                        '.$res.'
                    </div>
                </div>';
    }


    static function GetContactsList($companyID)
    {
        if (!isset($companyID)) return [];
        $arOrder = ['ID' => 'ASC'];
        $arFilter = ['COMPANY_ID' => $companyID,];
        $arSelect = ['*', 'UF_*'];
        $list = [];
        $contacts = CCrmContact::GetList($arOrder, $arFilter, [], $arSelect);
        while ($contact = $contacts->fetch()) {
            $contact['PHONE'] = CustomCompanyContacts::loadFieldMulti($contact['ID'], CCrmFieldMulti::PHONE );
            $contact['EMAIL'] = CustomCompanyContacts::loadFieldMulti($contact['ID'], CCrmFieldMulti::EMAIL );
            $list[] = $contact;
        }
        return $list;
    }

    static function loadFieldMulti($contactID, $fieldType){
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
            $list[] = CustomCompanyContacts::transformMultiformFields($field);
        }
        return $list;
    }


    static function transformMultiformFields($multifield){
        return [
            'ID' => $multifield['ID'],
            'TYPE_ID' => $multifield['TYPE_ID'],
            'VALUE' => $multifield['VALUE'],
            'VALUE_TYPE' => $multifield['VALUE_TYPE'],
        ];
    }


    static function OnBeforeSave($arUserField, $value){
        return $value ?? $arUserField['VALUE'] ?? $arUserField['USER_TYPE']['DEFAULT_VALUE'] ?? 'N';
    }
}