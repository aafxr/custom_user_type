<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");

$isNewContact = empty($arResult['CONTACT']);
$contact = $arResult['CONTACT'] ?? [];

?>

<div class="ui-form contact-edite-form" data-cid="<?= $contact['ID']; ?>">
    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Имя:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactName" type="text" class="ui-ctl-element" data-field="NAME" value="<?=$arResult['CONTACT']['NAME'];?>"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Фамилия:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactLastName" type="text" class="ui-ctl-element" data-field="LAST_NAME" value="<?=$arResult['CONTACT']['LAST_NAME'];?>"/>
            </div>
        </div>
    </div>

    <div>
        <div class="ui-ctl">
            <div class="ui-ctl-label-text">Должность:</div>
            <div class="ui-ctl ui-ctl__combined-input">
                <input id="contactPost" type="text" class="ui-ctl-element" data-field="POST" value="<?=$arResult['CONTACT']['POST'];?>"/>
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
<script>
    BX.ready(() => {
        function handleSaveClick() {
            const contactForm = document.querySelector('.ui-form.contact-edite-form')
            if (contactForm) {
                const fields = {
                    PHONE: [],
                    EMAIL: []
                }
                if (contactForm.hasAttribute('data-cid')) {
                    fields['ID'] = contactForm.getAttribute('data-cid')
                }
                const inputs = ontactForm.querySelectorAll('input')
                for (const input of inputs) {
                    if (input.hasAttribute('data-field')) {
                        const field = input.getAttribute('data-field')
                        if (field === 'PHONE') {
                            fields.PHONE.push(input.value.trim())
                        } else if (field === 'EMAIL') {
                            fields.EMAIL.push(input.value.trim())
                        } else {
                            fields[field] = input.value
                        }
                    }
                }
                fetch(location.origin + '/local/components/dev/company.popup/ajax.php', {
                    method: 'POST',
                    body: JSON.stringify(fields)
                })
                    .then(console.log)
                    .catch(console.error)
            }
        }


        //---------------------------------- buttons events listeners ----------------------------------
        const buttonsContainer = document.querySelector('.ui-form-buttons.contact-form-buttons')
        console.log(buttonsContainer)
        if (buttonsContainer) {
            const saveButton = buttonsContainer.querySelector('.save-button')
            const cancelButton = buttonsContainer.querySelector('.cancel-button')

            if (saveButton) {
                saveButton.addEventListener('click', () => {
                    console.log('click save')
                    handleSaveClick()
                })
            }

            if (cancelButton) {
                cancelButton.addEventListener('click', () => {
                    console.log('click cancel')
                    console.log(<?=json_encode($arResult['CONTACT']);?>)
                    console.log(BX.WindowManager.Get())
                })
            }
        }


    })
</script>