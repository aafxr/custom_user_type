<?php


use \Bitrix\Main\EventManager;
use \Bitrix\Main\UserField\TypeBase;
use \Bitrix\Main\UI\Extension;


//$eventManager = EventManager::getInstance();
//$eventManager->addEventHandlerCompatible('main', 'OnUserTypeBuildList', array('CustomCompanyContacts', 'GetUserTypeDescription'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockAdd', array('CustomCompanyContacts', 'OnAfterIBlockAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockElementAdd', array('CustomCompanyContacts', 'OnAfterIBlockElementAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockElementDelete', array('CustomCompanyContacts', 'OnAfterIBlockElementDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockElementSetPropertyValues', array('CustomCompanyContacts', 'OnAfterIBlockElementSetPropertyValues'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockElementSetPropertyValuesEx', array('CustomCompanyContacts', 'OnAfterIBlockElementSetPropertyValuesEx'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockElementUpdate', array('CustomCompanyContacts', 'OnAfterIBlockElementUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockPropertyAdd', array('CustomCompanyContacts', 'OnAfterIBlockPropertyAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockPropertyDelete', array('CustomCompanyContacts', 'OnAfterIBlockPropertyDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockPropertyUpdate', array('CustomCompanyContacts', 'OnAfterIBlockPropertyUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockSectionAdd', array('CustomCompanyContacts', 'OnAfterIBlockSectionAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockSectionUpdate', array('CustomCompanyContacts', 'OnAfterIBlockSectionUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnAfterIBlockUpdate', array('CustomCompanyContacts', 'OnAfterIBlockUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockAdd', array('CustomCompanyContacts', 'OnBeforeIBlockAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockDelete', array('CustomCompanyContacts', 'OnBeforeIBlockDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockElementAdd', array('CustomCompanyContacts', 'OnBeforeIBlockElementAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockElementDelete', array('CustomCompanyContacts', 'OnBeforeIBlockElementDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockElementUpdate', array('CustomCompanyContacts', 'OnBeforeIBlockElementUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockPropertyAdd', array('CustomCompanyContacts', 'OnBeforeIBlockPropertyAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockPropertyDelete', array('CustomCompanyContacts', 'OnBeforeIBlockPropertyDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockPropertyUpdate', array('CustomCompanyContacts', 'OnBeforeIBlockPropertyUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockSectionAdd', array('CustomCompanyContacts', 'OnBeforeIBlockSectionAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockSectionDelete', array('CustomCompanyContacts', 'OnBeforeIBlockSectionDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockSectionUpdate', array('CustomCompanyContacts', 'OnBeforeIBlockSectionUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnBeforeIBlockUpdate', array('CustomCompanyContacts', 'OnBeforeIBlockUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockDelete', array('CustomCompanyContacts', 'OnIBlockDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockElementAdd', array('CustomCompanyContacts', 'OnIBlockElementAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockElementDelete', array('CustomCompanyContacts', 'OnIBlockElementDelete'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockElementSetPropertyValues', array('CustomCompanyContacts', 'OnIBlockElementSetPropertyValues'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockElementSetPropertyValuesEx', array('CustomCompanyContacts', 'OnIBlockElementSetPropertyValuesEx'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockElementUpdate', array('CustomCompanyContacts', 'OnIBlockElementUpdate'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnIBlockPropertyBuildList', array('CustomCompanyContacts', 'OnIBlockPropertyBuildList'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnStartIBlockElementAdd', array('CustomCompanyContacts', 'OnStartIBlockElementAdd'));
EventManager::getInstance()->addEventHandlerCompatible('main', 'OnStartIBlockElementUpdate', array('CustomCompanyContacts', 'OnStartIBlockElementUpdate'));

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
            'EDIT_CALLBACK' => [__CLASS__, 'GetPublicView'],
            'VIEW_CALLBACK' => [__CLASS__, 'GetPublicView'],
        );
    }


    static function GetDBColumnType($arUserField = []): string
    {
        CustomCompanyContacts::log('GetDBColumnType',$arUserField);

        return "varchar(10)";
    }


    function PrepareSettings($arUserField) {
        CustomCompanyContacts::log('PrepareSettings',$arUserField);

        return $arUserField;
    }

    static function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm){
        CustomCompanyContacts::log('GetSettingsHTML',$arUserField, $arHtmlControl, $bVarsFromForm);

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
        CustomCompanyContacts::log('GetPublicView',$arUserField, $arAdditionalParameters);

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


            $res .= '<div class="crm-entity-widget-client-block" data-contact-id="'.$contact['ID'].'">
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

        $templateUrl = '/local/media/templates/' . __CLASS__;
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
                </div>
                <script>
                    BX.ready(() => {
                        let node = document.querySelector("[data-cid=\"'.$arUserField['FIELD_NAME'].'\"]")
                        if(node){
                            let titleNode = node.querySelector(".ui-entity-editor-block-title")
                            titleNode.style.display = "none"
                        } 
                        dialog = new BX.CDialog({
                            "title": "test title",
                            "content_url": "'.$templateUrl.'/content.php?IFRAME=Y",
                        });
                        dialog.Show()
                    })
                </script>
                ';
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array()): string
    {
        CustomCompanyContacts::log('GetPublicEdit',$arUserField, $arAdditionalParameters);
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
        CustomCompanyContacts::log('OnBeforeSave',$arUserField, $value);
        return $value ?? $arUserField['VALUE'] ?? $arUserField['USER_TYPE']['DEFAULT_VALUE'] ?? 'N';
    }


    static function log($title = '', ...$params){
        $log = "<div class=\"section\"><h4 class=\"title\">$title</h4>";
        foreach ($params as $k => $param){
            $log .= '<pre class="code">'.json_encode($param, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</pre>';
        }
        $log .= "</div>\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/dev/test.log', $log, FILE_APPEND);
    }

    static function OnAfterIBlockAdd($arFields){
        CustomCompanyContacts::log('OnAfterIBlockAdd', $arFields);
    }
    static function OnAfterIBlockElementAdd($arFields){
        CustomCompanyContacts::log('OnAfterIBlockElementAdd', $arFields);
    }
    static function OnAfterIBlockElementDelete($arFields){
        CustomCompanyContacts::log('OnAfterIBlockElementDelete', $arFields);
    }
    static function OnAfterIBlockElementSetPropertyValues($arFields){
        CustomCompanyContacts::log('OnAfterIBlockElementSetPropertyValues', $arFields);
    }
    static function OnAfterIBlockElementSetPropertyValuesEx($arFields){
        CustomCompanyContacts::log('OnAfterIBlockElementSetPropertyValuesEx', $arFields);
    }
    static function OnAfterIBlockElementUpdate($arFields){
        CustomCompanyContacts::log('OnAfterIBlockElementUpdate', $arFields);
    }
    static function OnAfterIBlockPropertyAdd($arFields){
        CustomCompanyContacts::log('OnAfterIBlockPropertyAdd', $arFields);
    }
    static function OnAfterIBlockPropertyDelete($arFields){
        CustomCompanyContacts::log('OnAfterIBlockPropertyDelete', $arFields);
    }
    static function OnAfterIBlockPropertyUpdate($arFields){
        CustomCompanyContacts::log('OnAfterIBlockPropertyUpdate', $arFields);
    }
    static function OnAfterIBlockSectionAdd($arFields){
        CustomCompanyContacts::log('OnAfterIBlockSectionAdd', $arFields);
    }
    static function OnAfterIBlockSectionUpdate($arFields){
        CustomCompanyContacts::log('OnAfterIBlockSectionUpdate', $arFields);
    }
    static function OnAfterIBlockUpdate($arFields){
        CustomCompanyContacts::log('OnAfterIBlockUpdate', $arFields);
    }
    static function OnBeforeIBlockAdd($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockAdd', $arFields);
    }
    static function OnBeforeIBlockDelete($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockDelete', $arFields);
    }
    static function OnBeforeIBlockElementAdd($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockElementAdd', $arFields);
    }
    static function OnBeforeIBlockElementDelete($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockElementDelete', $arFields);
    }
    static function OnBeforeIBlockElementUpdate($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockElementUpdate', $arFields);
    }
    static function OnBeforeIBlockPropertyAdd($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockPropertyAdd', $arFields);
    }
    static function OnBeforeIBlockPropertyDelete($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockPropertyDelete', $arFields);
    }
    static function OnBeforeIBlockPropertyUpdate($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockPropertyUpdate', $arFields);
    }
    static function OnBeforeIBlockSectionAdd($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockSectionAdd', $arFields);
    }
    static function OnBeforeIBlockSectionDelete($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockSectionDelete', $arFields);
    }
    static function OnBeforeIBlockSectionUpdate($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockSectionUpdate', $arFields);
    }
    static function OnBeforeIBlockUpdate($arFields){
        CustomCompanyContacts::log('OnBeforeIBlockUpdate', $arFields);
    }
    static function OnIBlockDelete($arFields){
        CustomCompanyContacts::log('OnIBlockDelete', $arFields);
    }
    static function OnIBlockElementAdd($arFields){
        CustomCompanyContacts::log('OnIBlockElementAdd', $arFields);
    }
    static function OnIBlockElementDelete($arFields){
        CustomCompanyContacts::log('OnIBlockElementDelete', $arFields);
    }
    static function OnIBlockElementSetPropertyValues($arFields){
        CustomCompanyContacts::log('OnIBlockElementSetPropertyValues', $arFields);
    }
    static function OnIBlockElementSetPropertyValuesEx($arFields){
        CustomCompanyContacts::log('OnIBlockElementSetPropertyValuesEx', $arFields);
    }
    static function OnIBlockElementUpdate($arFields){
        CustomCompanyContacts::log('OnIBlockElementUpdate', $arFields);
    }
    static function OnIBlockPropertyBuildList($arFields){
        CustomCompanyContacts::log('OnIBlockPropertyBuildList', $arFields);
    }
    static function OnStartIBlockElementAdd($arFields){
        CustomCompanyContacts::log('OnStartIBlockElementAdd', $arFields);
    }
    static function OnStartIBlockElementUpdate($arFields){
        CustomCompanyContacts::log('OnStartIBlockElementUpdate', $arFields);
    }

//OnAfterIBlockAdd
//OnAfterIBlockElementAdd
//OnAfterIBlockElementDelete
//OnAfterIBlockElementSetPropertyValues
//OnAfterIBlockElementSetPropertyValuesEx
//OnAfterIBlockElementUpdate
//OnAfterIBlockPropertyAdd
//OnAfterIBlockPropertyDelete
//OnAfterIBlockPropertyUpdate
//OnAfterIBlockSectionAdd
//OnAfterIBlockSectionUpdate
//OnAfterIBlockUpdate
//OnBeforeIBlockAdd
//OnBeforeIBlockDelete
//OnBeforeIBlockElementAdd
//OnBeforeIBlockElementDelete
//OnBeforeIBlockElementUpdate
//OnBeforeIBlockPropertyAdd
//OnBeforeIBlockPropertyDelete
//OnBeforeIBlockPropertyUpdate
//OnBeforeIBlockSectionAdd
//OnBeforeIBlockSectionDelete
//OnBeforeIBlockSectionUpdate
//OnBeforeIBlockUpdate
//OnIBlockDelete
//OnIBlockElementAdd
//OnIBlockElementDelete
//OnIBlockElementSetPropertyValues
//OnIBlockElementSetPropertyValuesEx
//OnIBlockElementUpdate
//OnIBlockPropertyBuildList
//OnStartIBlockElementAdd
//OnStartIBlockElementUpdate
}