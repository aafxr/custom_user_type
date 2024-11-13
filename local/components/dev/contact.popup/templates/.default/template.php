<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");

$isNewContact = empty($arResult['CONTACT']);
$contact = $arResult['CONTACT'] ?? [];

?>

<div class="ui-form contact-edite-form" data-cid="<?php $contact['ID'] ?>">
    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Имя:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactName" type="text" class="ui-ctl-element" data-field="NAME"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Фамилия:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactLastName" type="text" class="ui-ctl-element" data-field="LAST_NAME"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Должность:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactPost" type="text" class="ui-ctl-element" data-field="POST"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Телефон:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactComment" type="text" class="ui-ctl-element" data-field="PHONE"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">E-mail:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactComment" type="text" class="ui-ctl-element" data-field="EMAIL"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Комментарий:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactComment" type="text" class="ui-ctl-element" data-field="COMMENT"/>
            </div>
        </div>
    </div>

    <div class="ui-form-buttons contact-form-buttons">
        <button class="ui-btn ui-btn-success save-button"><span class="ui-btn-text">Сохранить</span></button>
        <button class="ui-btn cancel-button"><span class="ui-btn-text">Отменить</span></button>
    </div>
</div>