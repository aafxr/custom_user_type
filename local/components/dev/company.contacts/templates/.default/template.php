<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();
\Bitrix\Main\UI\Extension::load("ui.buttons");

$editeMode = boolval($arResult['EDITE_MODE'])
?>
<div class="crm-entity-widget-content-block-inner crm-entity-widget-inner">
    <div class="crm-entity-widget-content-block-inner-container">
        <div class="crm-entity-widget-content-block-title">
            <span class="crm-entity-widget-content-subtitle-text">
                <span>Контакты, связанные с компанией</span>
                <span class="crm-entity-card-widget-title-edit-icon"></span>
            </span>
        </div>
        <?php foreach ($arResult['CONTACTS'] as $k => $contact): ?>
            <div
                class="crm-entity-widget-client-block contact-block"
                data-contact-id="<?= $contact['ID']; ?>"
                data-contact-name="<?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?>"
            >
                <div class="crm-entity-widget-client-box crm-entity-widget-participants-block">
                    <div class="crm-entity-widget-client-box-name-container">
                        <div class="crm-entity-widget-client-box-name-row">
                            <?php if($editeMode): ?>
                                <span class="crm-entity-widget-client-box-name edit-contact" ><?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?></span>
                            <?php else: ?>
                                <a class="crm-entity-widget-client-box-name" href="/crm/contact/details/<?= $contact["ID"]; ?>/"><?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?></a>
                            <?php endif;?>
                        </div>
                    </div>
                    <div class="crm-entity-widget-client-box-position"><?= $contact['POST']; ?></div>
                    <div class="crm-entity-widget-client-box-preferences">
                        <?php
                        if (isset($contact[$arResult['PREFERENCES_FIELD']]) && is_array($contact[$arResult['PREFERENCES_FIELD']])) {
                            $preferences = '';
                            foreach ($contact[$arResult['PREFERENCES_FIELD']] as $k => $pref) {
                                $pref = explode(':',$pref);
                                $isYes = $pref[1] == 'да';
                                ?>
                                <span><?= $pref[0]; ?><span class="<?= $isYes ? 'yes' : 'no'; ?>"><?= $isYes ? 'да' : 'нет'; ?></span></span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="crm-entity-widget-client-contact">
                        <?php if (isset($contact['PHONE']) && is_array($contact['PHONE'])): ?>
                            <?php foreach ($contact['PHONE'] as $k => $phone): ?>
                                <a
                                    class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"
                                    href="callto://<?=$phone['VALUE'];?>"
                                ><?= $phone['VALUE'] ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (isset($contact['EMAIL']) && is_array($contact['EMAIL'])): ?>
                            <?php foreach ($contact['EMAIL'] as $k => $email): ?>
                                <a
                                    class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"
                                    href="mailto:<?=$email['VALUE'];?>"
                                ><?= $email['VALUE'] ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="crm-entity-widget-client-box-quiz crm-entity-widget-client-box-quiz-fields">
                        <?php if (isset($contact[$arResult['QUIZ_FIELD']]) && $contact[$arResult['QUIZ_FIELD']] != false) {
                            $quiz = '';
                            foreach ($contact[$arResult['QUIZ_FIELD']] as $k => $q) {
                                ?>
                                <span><?= $q; ?> </span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="crm-entity-widget-client-address"></div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if($editeMode): ?>
            <button class="ui-btn ui-btn-xs ui-btn-success add-contact-button">Добавить контакт</button>
        <?php endif; ?>
    </div>
</div>
    <script>
        BX.ready(() => {
            console.log(<?=json_encode($arResult)?>)
            const company_id = <?= $arResult['COMPANY_ID'] ?? 0; ?>;
            let node = document.querySelector('[data-cid="<?=$arResult['USER_FIELD_NAME'];?>"]')
            if (node) {
                let titleNode = node.querySelector(".ui-entity-editor-block-title")
                titleNode.style.display = "none"


            <?php if($editeMode):?>
                node.addEventListener('click', (e) => {
                    const el = e.target.closest('.contact-block')
                    if(el && el.hasAttribute('data-contact-id')){
                        const contact_id = el.getAttribute('data-contact-id')
                        const title = "Контакт " + el.getAttribute('data-contact-name')
                        const content_url = '/local/contact/contact_edit.php?IFRAME=Y'
                        const content_post = `contact_id=${contact_id}&company_id=${company_id}`
                        const dialog = new BX.CDialog({title, content_url, content_post});
                        dialog.Show()
                    }
                    return false
                })

                const newContactButton = node.querySelector('.add-contact-button')
                if(newContactButton){
                    newContactButton.addEventListener('click', (e) => {
                        const title = "Добавить контакт"
                        const content_url = '/local/contact/contact_edit.php?IFRAME=Y'
                        const content_post = `company_id=${company_id}`
                        const dialog = new BX.CDialog({title, content_url, content_post});
                        dialog.Show()
                    })
                }
            <?php endif; ?>
            }
        })
    </script>

<?php
/*
<?php if (isset($contact['EMAIL']) && is_array($contact['EMAIL'])): ?>
    <?php foreach ($contact['EMAIL'] as $k => $email): ?>
        <div class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"><?= $email['VALUE'] ?></div>
    <?php endforeach; ?>
<?php endif; ?>
*/

