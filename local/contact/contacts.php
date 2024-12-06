<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


use \Bitrix\Main\Loader;

use \Bitrix\Main;
use \Bitrix\Crm;

global $APPLICATION;
global $USER;


CModule::IncludeModule("crm");


function GetContactsList($companyID)
{
    if (!isset($companyID)) return [];
    $arOrder = ['ID' => 'ASC'];
    $arFilter = ['COMPANY_ID' => $companyID,];
    $arSelect = [];
    $list = [];
    $contacts = \CCrmContact::GetList($arOrder, $arFilter, $arSelect);
    while ($contact = $contacts->fetch()) {
        $contact['PHONE'] = loadFieldMulti($contact['ID'], \CCrmFieldMulti::PHONE);
        $contact['EMAIL'] = loadFieldMulti($contact['ID'], \CCrmFieldMulti::EMAIL);
        $list[] = $contact;
    }
    return $list;
}

function loadFieldMulti($contactID, $fieldType)
{
    $resFieldMulti = \CCrmFieldMulti::GetListEx(
        [],
        [
            'ENTITY_ID' => \CCrmOwnerType::ContactName,
            'ELEMENT_ID' => $contactID,
            'TYPE_ID' => $fieldType
        ]
    );

    $list = [];
    while ($field = $resFieldMulti->fetch()) {
        $list[] = transformMultiformFields($field);
    }
    return $list;
}


function transformMultiformFields($multifield)
{
    return [
        'ID' => $multifield['ID'],
        'TYPE_ID' => $multifield['TYPE_ID'],
        'VALUE' => $multifield['VALUE'],
        'VALUE_TYPE' => $multifield['VALUE_TYPE'],
    ];
}


$contacts = GetContactsList($_GET['company_id']);

$arResult['CONTACTS'] = $contacts;


function getPrefferences($contact)
{
    $preferences = [
        'В чат-боте' => [
            'NAME' => 'UF_CRM_HAS_TG_REGISTRATION',
            'VALUE' => false,
        ],
        'Любит ПВХ плитку?' => [
            'NAME' => 'UF_CRM_60120C8A6BD67',
            'VALUE' => false,
        ],
        'Прослушал семинар?' => [
            'NAME' => 'UF_CRM_SEMINAR',
            'VALUE' => false,
        ],
        'Прослушал семинар QP' => [
            'NAME' => 'UF_SEMINAR_QP',
            'VALUE' => false,
        ],
    ];

    foreach ($preferences as $name => $defaultValue) {
        if (isset($contact[$defaultValue['NAME']])) {
            $preferences[$name]['VALUE'] = $contact[$defaultValue['NAME']];
        }
    }
    return $preferences;
}

?>


<div class="refloor-contacts">
    <div class="crm-entity-widget-content-block-inner crm-entity-widget-inner">
        <div class="crm-entity-widget-content-block-inner-container">
            <div class="crm-entity-widget-content-block-title">
                <span class="crm-entity-widget-content-subtitle-text">
                    <span>Контактные лица</span>
                    <span class="crm-entity-card-widget-title-edit-icon"></span>
                </span>
            </div>
            <?php foreach ($arResult['CONTACTS'] as $k => $contact): ?>
                <div
                        class="crm-entity-widget-client-block contact-block refloor-contact-block"
                        data-contact-id="<?= $contact['ID']; ?>"
                        data-contact-name="<?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?>"
                >

                    <div class="crm-entity-widget-client-box crm-entity-widget-participants-block">
                        <div class="crm-entity-widget-client-box-name-container">
                            <div class="crm-entity-widget-client-box-name-row">
                                <a class="crm-entity-widget-client-box-name edit-contact" ><?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?></a>
                                <a href="/crm/contact/details/<?= $contact["ID"]; ?>/" class="form-client-card-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16" height="16" viewBox="0 0 24 24">
                                        <path d="M 5 3 C 3.9069372 3 3 3.9069372 3 5 L 3 19 C 3 20.093063 3.9069372 21 5 21 L 19 21 C 20.093063 21 21 20.093063 21 19 L 21 12 L 19 12 L 19 19 L 5 19 L 5 5 L 12 5 L 12 3 L 5 3 z M 14 3 L 14 5 L 17.585938 5 L 8.2929688 14.292969 L 9.7070312 15.707031 L 19 6.4140625 L 19 10 L 21 10 L 21 3 L 14 3 z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="crm-entity-widget-client-box-position">
                            <? if($contact['POST'] != ""): ?>
                                <?= $contact['POST']; ?>&nbsp;
                            <? endif ?>
                            <? if($contact['BIRTHDATE'] != ""): ?>
                                <? $birthday = explode(" ",$contact['BIRTHDATE']); ?>

                                день рождения: <?=$birthday[0]?>
                            <? endif ?>
                        </div>
                        <div class="crm-entity-widget-client-box-preferences refloor-contact-properties">
                            <?php
                            $preferences = getPrefferences($contact);
                            foreach ($preferences as $k => $p){
                                $name = $k;
                                $ufFieldName = $p['NAME'];
                                $checked = boolval($p['VALUE']);
                                ?>
                                <span class="refloor-contact-property"><?= $name; ?><span class="<?= $checked ? 'yes' : 'no'; ?>"><?= $checked ? 'да' : 'нет'; ?></span></span>
                            <?php } ?>
                        </div>
                        <?
                            if(isset($contact['ID'])){
                                $APPLICATION->IncludeComponent(
                                    'refloor:contact.promo',
                                    'previews',
                                    [
                                        'CONTACT_ID' => $contact['ID'] ?? '1',
                                    ]
                                );
                            }
                        ?>
                        <div class="crm-entity-widget-client-contact">
                            <?php if (isset($contact['PHONE']) && is_array($contact['PHONE'])): ?>
                                <?php foreach ($contact['PHONE'] as $k => $phone): ?>
                                    <a
                                            class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"
                                            href="tel://<?=preg_replace('/[^+\d]/', '', $phone['VALUE']);?>"
                                            onclick="event.stopPropagation()"
                                    ><?= $phone['VALUE'] ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (isset($contact['EMAIL']) && is_array($contact['EMAIL'])): ?>
                                <?php foreach ($contact['EMAIL'] as $k => $email): ?>
                                    <a
                                            class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"
                                            href="mailto:<?=$email['VALUE'];?>"
                                            onclick="event.stopPropagation()"
                                    ><?= $email['VALUE'] ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <? /* <div class="crm-entity-widget-client-box-quiz crm-entity-widget-client-box-quiz-fields">
                            <?php if (isset($contact[$arResult['QUIZ_FIELD']]) && $contact[$arResult['QUIZ_FIELD']] != false) {
                                $quiz = '';
                                foreach ($contact[$arResult['QUIZ_FIELD']] as $k => $q) {
                                    ?>
                                    <span><?= $q; ?> </span>
                                    <?php
                                }
                            }
                            ?>
                        </div> */ ?>
                        <div class="refloor-comments">
                            <?=$contact['COMMENTS'];?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php /* if($editeMode): ?>
                <button class="ui-btn ui-btn-xs ui-btn-success add-contact-button">Добавить контакт</button>
            <?php endif; */ ?>
            <?php if($arResult['COMPANY_ID'] == 0): ?>
                <p>Для добавления контактов сохраните карточку компании</p>
            <?php else: ?>
                <a class="add-contact-button">Добавить контакт</a>
            <?php endif ?>
        </div>
    </div>
    <script>
        BX.ready(() => {
            console.log("Init contacts")
            const company_id = <?= $arResult['COMPANY_ID'] ?? 0; ?>;
            let node = document.querySelector('[data-cid="<?=$arResult['USER_FIELD_NAME'];?>"]')
            console.log(node)
            if (node) {
                let titleNode = node.querySelector(".ui-entity-editor-block-title")
                titleNode.style.display = "none"


                node.addEventListener('click', (e) => {
                    e.preventDefault()
                    e.stopImmediatePropagation()
                    if(e.target.closest('.form-client-card-link')) return
                    const el = e.target.closest('.contact-block')
                    if(el && el.hasAttribute('data-contact-id')){
                        const contact_id = el.getAttribute('data-contact-id')
                        const title = "Контакт " + el.getAttribute('data-contact-name')
                        const content_url = '/local/contact/contact_edit.php?IFRAME=Y'
                        const content_post = `contact_id=${contact_id}&company_id=${company_id}`
                        const dialog = new BX.CDialog({title, content_url, content_post, width: 740, height: 740});
                        dialog.Show()
                    }
                    return false
                })


                const newContactButton = node.querySelector('.add-contact-button')
                if(newContactButton){
                    newContactButton.addEventListener('click', (e) => {
                        e.preventDefault()
                        const title = "Добавить контакт"
                        const content_url = '/local/contact/contact_edit.php?IFRAME=Y'
                        const content_post = `company_id=${company_id}`
                        const dialog = new BX.CDialog({title, content_url, content_post, width: 740, height: 740});
                        dialog.Show()
                    })
                }
            }
        })
    </script>
</div>