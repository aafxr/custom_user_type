<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
//\Bitrix\Main\UI\Extension::load("ui.forms");
//\Bitrix\Main\UI\Extension::load("ui.alerts");
//\Bitrix\Main\UI\Extension::load("ui.buttons");

$APPLICATION->ShowHead(false);
$APPLICATION->includeComponent(
    'dev:company.contacts',
    '',
    [ "CUSTOM" => '42' ],
    null
);
require($_SERVER["DOCUMENT_ROOT"]. "/bitrix/modules/main/include/epilog_after.php");

?>


<!--    <div class="ui-form">-->
<!--        <div>-->
<!--            <div class="ui-ctl">-->
<!--                <div class="ui-ctl-label-text">Имя:</div>-->
<!--                <div class="ui-ctl ui-ctl__combined-input">-->
<!--                    <input id="contactName" type="text" class="ui-ctl-element"/>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--        <div>-->
<!--            <div class="ui-ctl">-->
<!--                <div class="ui-ctl-label-text">Фамилия:</div>-->
<!--                <div class="ui-ctl ui-ctl__combined-input">-->
<!--                    <input id="contactName" type="text" class="ui-ctl-element"/>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--        <div>-->
<!--            <div class="ui-ctl">-->
<!--                <div class="ui-ctl-label-text">Должность:</div>-->
<!--                <div class="ui-ctl ui-ctl__combined-input">-->
<!--                    <input id="contactName" type="text" class="ui-ctl-element"/>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--        <div>-->
<!--            <div class="ui-ctl">-->
<!--                <div class="ui-ctl-label-text">Комментарий:</div>-->
<!--                <div class="ui-ctl ui-ctl__combined-input">-->
<!--                    <input id="contactName" type="text" class="ui-ctl-element"/>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="ui-ctl ui-ctl-row">-->
<!--            <button class="ui-btn ui-btn-success" ><span class="ui-btn-text">Сохранить</span></button>-->
<!--            <button class="ui-btn " ><span class="ui-btn-text">Сохранить</span></button>-->
<!--        </div>-->
<!--    </div>-->
