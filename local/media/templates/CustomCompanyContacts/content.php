<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");
?>

    <div class="ui-form">
        <div>
            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Имя:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactName" type="text" class="ui-ctl-element"/>
                </div>
            </div>
        </div>

        <div>
            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Фамилия:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactName" type="text" class="ui-ctl-element"/>
                </div>
            </div>
        </div>

        <div>
            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Должность:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactName" type="text" class="ui-ctl-element"/>
                </div>
            </div>
        </div>

<!--        <div>-->
<!--            <div class="ui-ctl ui-ctl__combined-input">-->
<!--                <div class="ui-ctl-label-text">Контакты:</div>-->
<!--                <div class="ui-ctl ui-ctl__combined-input">-->
<!--                    <select class="ui-ctl-multiple-select" name="contacts" >-->
<!--                        <option value="PHONE">тел.</option>-->
<!--                        <option value="EMAIL">mail</option>-->
<!--                    </select>-->
<!--                    <input id="contactName" type="text" class="ui-ctl-element"/>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <div>
            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Комментарий:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactName" type="text" class="ui-ctl-element"/>
                </div>
            </div>
        </div>
        <div class="ui-ctl ui-ctl__combined-input">
            <button class="ui-btn ui-btn-success" >Сохранить</button>
            <button class="ui-btn " >Сохранить</button>
        </div>
    </div>

<?php
//<div class="ui-entity-editor-section-content ui-entity-editor-section-content-padding-right">
//
//    <div class="ui-entity-editor-content-block crm-entity-widget-content-block-field-multifield" data-cid="PHONE">
//        <div data-field-tag="PHONE" class="ui-entity-editor-block-before-action"></div>
//        <div class="ui-entity-editor-block-draggable-btn-container">
//            <div class="ui-entity-editor-draggable-btn"></div>
//        </div>
//        <div class="ui-entity-editor-block-title ui-entity-widget-content-block-title-edit">
//            <label class="ui-entity-editor-block-title-text">Имя</label>
//        </div>
//        <div class="crm-entity-widget-content-block-inner">
//            <div class="crm-entity-widget-content-block-field-container crm-entity-widget-content-block-field-container-double">
//                <input name="PHONE[23][VALUE]" type="hidden" value="8 4012 531249">
//                <input name="PHONE[23][VALUE_COUNTRY_CODE]" type="hidden" value="VN">
//                <div class="crm-entity-widget-content-input-wrapper">
//                    <span
//                            class="crm-entity-widget-content-country-flag crm-entity-phone-number-input-flag-24"
//                            style="cursor: pointer; display: inline-block; border: 1px solid rgba(82, 92, 105, 0.2); background-image: url("/bitrix/js/crm/entity-selector/src/images/vn.png");"
//>
//                    <span class="crm-entity-widget-content-country-flag-tick"></span>
//                    </span>
//                    <input class="crm-entity-widget-content-input crm-entity-widget-content-input-phone" type="text" value="8 4012 531249">
//                </div>
//            </div>
//        </div>
//    </div>
//
//    <div class="ui-entity-editor-content-block crm-entity-widget-content-block-field-multifield" data-cid="PHONE">
//        <div data-field-tag="PHONE" class="ui-entity-editor-block-before-action"></div>
//        <div class="ui-entity-editor-block-draggable-btn-container">
//            <div class="ui-entity-editor-draggable-btn"></div>
//        </div>
//        <div class="ui-entity-editor-block-title ui-entity-widget-content-block-title-edit">
//            <label class="ui-entity-editor-block-title-text">Фамилия</label>
//        </div>
//        <div class="crm-entity-widget-content-block-inner">
//            <div class="crm-entity-widget-content-block-field-container crm-entity-widget-content-block-field-container-double">
//                <input name="PHONE[23][VALUE]" type="hidden" value="8 4012 531249">
//                <input name="PHONE[23][VALUE_COUNTRY_CODE]" type="hidden" value="VN">
//                <div class="crm-entity-widget-content-input-wrapper">
//                    <span
//                            class="crm-entity-widget-content-country-flag crm-entity-phone-number-input-flag-24"
//                            style="cursor: pointer; display: inline-block; border: 1px solid rgba(82, 92, 105, 0.2); background-image: url("/bitrix/js/crm/entity-selector/src/images/vn.png");"
//>
//                    <span class="crm-entity-widget-content-country-flag-tick"></span>
//                    </span>
//                    <input class="crm-entity-widget-content-input crm-entity-widget-content-input-phone" type="text" value="8 4012 531249">
//                </div>
//            </div>
//        </div>
//    </div>
//
//    <div class="ui-entity-editor-content-block crm-entity-widget-content-block-field-multifield" data-cid="PHONE">
//        <div data-field-tag="PHONE" class="ui-entity-editor-block-before-action"></div>
//        <div class="ui-entity-editor-block-draggable-btn-container">
//            <div class="ui-entity-editor-draggable-btn"></div>
//        </div>
//        <div class="ui-entity-editor-block-title ui-entity-widget-content-block-title-edit">
//            <label class="ui-entity-editor-block-title-text">Телефон</label>
//        </div>
//        <div class="crm-entity-widget-content-block-inner">
//            <div class="crm-entity-widget-content-block-field-container crm-entity-widget-content-block-field-container-double">
//                <input name="PHONE[23][VALUE]" type="hidden" value="8 4012 531249">
//                <input name="PHONE[23][VALUE_COUNTRY_CODE]" type="hidden" value="VN">
//                <div class="crm-entity-widget-content-input-wrapper">
//                    <span
//                            class="crm-entity-widget-content-country-flag crm-entity-phone-number-input-flag-24"
//                            style="cursor: pointer; display: inline-block; border: 1px solid rgba(82, 92, 105, 0.2); background-image: url("/bitrix/js/crm/entity-selector/src/images/vn.png");"
//>
//                    <span class="crm-entity-widget-content-country-flag-tick"></span>
//                    </span>
//                    <input class="crm-entity-widget-content-input crm-entity-widget-content-input-phone" type="text"
//                           value="8 4012 531249">
//                </div>
//                <input name="PHONE[23][VALUE_TYPE]" type="hidden" value="WORK">
//                <div class="crm-entity-widget-content-block-select">
//                    <div class="crm-entity-widget-content-select">Рабочий</div>
//                </div>
//                <div class="crm-entity-widget-content-remove-block"></div>
//            </div>
//        </div>
//    </div>
//</div>
?>