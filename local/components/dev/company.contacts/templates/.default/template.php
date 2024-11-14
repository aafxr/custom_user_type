<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();
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
                                <span class="crm-entity-widget-client-box-name" ><?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?></span>
                            <?php else: ?>
                                <a class="crm-entity-widget-client-box-name" href="/crm/contact/details/<?= $contact["ID"]; ?>"><?= $contact['NAME'] . ' ' . $contact['LAST_NAME']; ?></a>
                            <?php endif;?>
                        </div>
                    </div>
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
                    <div class="crm-entity-widget-client-box-position"><?= $contact['POST']; ?></div>
                    <div class="crm-entity-widget-client-contact">
                        <?php if (isset($contact['PHONE']) && is_array($contact['PHONE'])): ?>
                            <?php foreach ($contact['PHONE'] as $k => $phone): ?>
                                <div class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"><?= $phone['VALUE'] ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (isset($contact['EMAIL']) && is_array($contact['EMAIL'])): ?>
                            <?php foreach ($contact['EMAIL'] as $k => $email): ?>
                                <div class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"><?= $email['VALUE'] ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($contact['EMAIL']) && is_array($contact['EMAIL'])): ?>
                        <?php foreach ($contact['EMAIL'] as $k => $email): ?>
                            <div class="crm-entity-widget-client-contact-item crm-entity-widget-client-contact-phone"><?= $email['VALUE'] ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="crm-entity-widget-client-box-quiz">
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
    </div>
</div>
    <script>
        BX.ready(() => {
            let node = document.querySelector('[data-cid="<?=$arResult['USER_FIELD_NAME'];?>"]')
            console.log(node)
            if (node) {
                let titleNode = node.querySelector(".ui-entity-editor-block-title")
                titleNode.style.display = "none"
<?php if($editeMode):?>
                node.addEventListener('click', (e) => {
                    console.log(2)
                    const el = e.target.closest('.contact-block')
                    if(el && el.hasAttribute('data-contact-id')){
                        const contact_id = el.getAttribute('data-contact-id')
                        const title = "Контакт " + el.getAttribute('data-contact-name')
                        const contact_url = window.location.origin + '/local/contact/contact_edite.php?contact_id=' + contact_id + '&IFRAME=Y'
                        const dialog = new BX.CDialog({title, contact_url});
                        dialog.Show()
                    }
                    return false
                })
<?php endif; ?>
            }
        })
    </script>

