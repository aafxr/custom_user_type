<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");

$defaultPreferences = [
  'Зарегистрирован в чат-боте:нет',
  'Любит ПВХ плитку?:нет',
  'Прослушал семинар?:нет',
  'Прослушал семинар Кварцпаркет:нет',
];

$isNewContact = empty($arResult['CONTACT']);
$contact = $arResult['CONTACT'] ?? [];
$preferences = $contact[$arResult['PREFERENCES_FIELD']] ?? $defaultPreferences;
$quiz =  $contact[$arResult['QUIZ_FIELD']] ?? [];
$phones = $contact['PHONE'] ?? [];
$emails = $contact['EMAIL'] ?? [];

if(empty($preferences)) $preferences = $defaultPreferences;

?>
<style>
    .ui-form{
        height: 100%;
    }

    .ui-form-container{
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px 20px;
    }

    .ui-form-col{
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .ui-form-col-comment{
        grid-column: 1 / -1
    }

    .ui-form .ui-ctl,
    .ui-form .ui-btn{
        margin: 0!important;
    }

    .ui-form-buttons{
        padding-top: 20px;
        margin-top: auto;
        display: flex;
        gap: 8px;
    }
</style>
<div id="contactEditForm" class="ui-form contact-edite-form" data-cid="<?= $arResult['CONTACT_ID']; ?>">
    <div class="ui-form-container">
        <div class="ui-form-col">
            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Имя:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactName" type="text" class="ui-ctl-element form-input-field" data-field="NAME" value="<?=$arResult['CONTACT']['NAME'];?>"/>
                </div>
            </div>

            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Фамилия:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactLastName" type="text" class="ui-ctl-element form-input-field" data-field="LAST_NAME" value="<?=$arResult['CONTACT']['LAST_NAME'];?>"/>
                </div>
            </div>

            <div class="ui-ctl">
                <div class="ui-ctl-label-text">Должность:</div>
                <div class="ui-ctl ui-ctl__combined-input">
                    <input id="contactPost" type="text" class="ui-ctl-element form-input-field" data-field="POST" value="<?=$arResult['CONTACT']['POST'];?>"/>
                </div>
            </div>
        </div>



        <div class="ui-form-col">
            <div>
                <?php foreach ($preferences as $k => $p){
                    $r = explode(':',$p);
                    $value = $r[0];
                    $checked = $r[1] == 'да';
                    ?>
                    <label class="ui-ctl ui-ctl-checkbox form-checkbox-label">
                        <input
                                type="checkbox"
                                class="ui-ctl-element form-input-checkbox"
                                data-field="<?=$arResult['PREFERENCES_FIELD'];?>"
                                data-value="<?=$value;?>"
                            <?= $checked ? 'checked' : '';?>
                        />
                        <div class="ui-ctl-label-text"><?=explode(':',$p)[0];?></div>
                    </label>
                <?php };?>
            </div>
        </div>



        <div class="ui-form-col">
            <div class="ui-ctl-label-text">Телефон:</div>
            <?php if(empty($phones)) : ?>
                <div class="ui-ctl">
                    <div class="ui-ctl ui-ctl__combined-input">
                        <input
                                type="text"
                                class="ui-ctl-element form-input-phone"
                                data-field="PHONE"
                                data-id=""
                                value=""
                        />
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach($phones as $k => $p) : ?>
                <div class="ui-ctl">
                    <div class="ui-ctl ui-ctl__combined-input">
                        <input
                                type="text"
                                class="ui-ctl-element form-input-phone"
                                data-field="PHONE"
                                data-id="<?=$p['ID'];?>"
                                value="<?= $p['VALUE']; ?>"
                        />
                    </div>
                </div>
            <?php endforeach; ?>
            <input type="button" class="ui-btn ui-btn-success form-phone-button"  name="extraPhone" value="Добавить телефон" title="Добавить телефон">
        </div>




        <div class="ui-form-col">
            <div class="ui-ctl-label-text">E-mail:</div>
            <?php if(empty($emails)) : ?>
                <div class="ui-ctl">
                    <div class="ui-ctl ui-ctl__combined-input">
                        <input
                                type="text"
                                class="ui-ctl-element form-input-email"
                                data-field="EMAIL"
                                data-id=""
                                value=""
                        />
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach($emails as $k => $e) : ?>
                <div class="ui-ctl">
                    <div class="ui-ctl ui-ctl__combined-input">
                        <input
                                type="text"
                                class="ui-ctl-element form-input-email"
                                data-field="EMAIL"
                                data-id="<?=$e['ID'];?>"
                                value="<?= $e['VALUE']; ?>"
                        />
                    </div>
                </div>
            <?php endforeach; ?>
            <input type="button" class="ui-btn ui-btn-success form-email-button"  name="extraPhone" value="Добавить e-mail" title="Добавить e-mail">
        </div>


        <div class="ui-form-col ui-form-col-comment">
            <div class="ui-ctl-label-text">Комментарий:</div>
            <div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
                <textarea
                    class="ui-ctl-element form-comment"
                    data-field="<?=$arResult['COMMENT_FIELD'];?>"
                    value="<?=$contact[$arResult['COMMENT_FIELD']] ?? '';?>"
                ><?=$contact[$arResult['COMMENT_FIELD']] ?? '';?></textarea>
            </div>
        </div>


    </div>
    <div class="ui-form-buttons">
        <button class="ui-btn ui-btn-success ui-btn-icon-done save-button">Сохранить</button>
        <button class="ui-btn cancel-button cancel-button">Отменить</button>
    </div>

</div>
<script>
    BX.ready(() => {
        const getExtraPhoneTemplate = () => {
            const div = document.createElement('div')
            div.classList.add('ui-ctl')
            div.innerHTML = `
            <div class="ui-ctl ui-ctl__combined-input">
                <input
                    type="text"
                    class="ui-ctl-element form-input-phone"
                    data-field="PHONE"
                    data-id=""
                    value=""
                />
            </div>
        `
            return div
        }

        const getExtraEmailTemplate = () => {
            const div = document.createElement('div')
            div.classList.add('ui-ctl')
            div.innerHTML = `
            <div class="ui-ctl ui-ctl__combined-input">
                <input
                    type="text"
                    class="ui-ctl-element form-input-email"
                    data-field="EMAIL"
                    data-id=""
                    value=""
                />
            </div>
        `
            return div
        }


        const company_id = <?= $arResult['COMPANY_ID'] ?? 0; ?>;
        const contact_id = <?= $arResult['CONTACT_ID'] ?? 0; ?>;
        console.log(<?=json_encode($arResult)?>)
        BX.WindowManager.Get()?.SetTitle?.('<?=$isNewContact ? 'Добавить контакт' : 'Изменить контакт: ' .$arResult['CONTACT']['NAME'].' '.$arResult['CONTACT']['LAST_NAME']?>')
        const confirmChangesURL = '<?=$arResult['COMPONENT_PATH']?>';
        const contactForm = document.querySelector('.ui-form.contact-edite-form')


        const phoneButton = contactForm.querySelector('.form-phone-button')
        if(phoneButton) phoneButton.addEventListener('click', (e) => {
            e.preventDefault()
            phoneButton.parentElement.insertBefore(getExtraPhoneTemplate(), phoneButton)
        })


        const emailButton = contactForm.querySelector('.form-email-button')
        if(emailButton) emailButton.addEventListener('click', (e) => {
            e.preventDefault()
            emailButton.parentElement.insertBefore(getExtraEmailTemplate(), emailButton)
        })


        function handleSaveClick() {
            if (contactForm) {
                const fields = { PHONE: [], EMAIL: [] }
                if (company_id) fields['COMPANY_ID'] = company_id

                if (contact_id) fields['ID'] = contact_id


                let hasError = false
                const handleBadCondition = (el) =>{
                    if(el instanceof Element){
                        el.parentElement.classList.add('ui-ctl-warning')
                    }
                    hasError = true
                }

                let inputs = contactForm.querySelectorAll('.form-input-field')
                for (const input of inputs) {
                    if (input.hasAttribute('data-field')) {
                        const field = input.getAttribute('data-field')
                        fields[field] = input.value
                    }
                }


                inputs = contactForm.querySelectorAll('.form-input-phone')
                for (const input of inputs) {
                    if (input.hasAttribute('data-field')) {
                        const p = {
                            ID: input.getAttribute('data-id'),
                            VALUE: input.value.trim().replaceAll(/\D/g, '')
                        }
                        if(!p.VALUE.length) continue
                        if(p.VALUE.length < 10){
                            handleBadCondition(input)
                            continue
                        }
                        fields['PHONE'].push(p)
                    }
                }


                inputs = contactForm.querySelectorAll('.form-input-email')
                for (const input of inputs) {
                    if (input.hasAttribute('data-field')) {
                        const em = {
                            ID: input.getAttribute('data-id'),
                            VALUE: input.value.trim()
                        }
                        if(!em.VALUE.length) continue
                        if(!/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/g.test(em.VALUE)){
                            console.log(em)
                            handleBadCondition(input)
                            continue
                        }
                        fields['EMAIL'].push(em)
                    }
                }


                inputs = contactForm.querySelectorAll('.form-input-checkbox')
                for (const input of inputs) {
                    if (input.hasAttribute('data-field')) {
                        const field = input.getAttribute('data-field')
                        if(!fields[field]) fields[field] = []
                        fields[field].push(`${input.getAttribute('data-value')}:${input.checked ? 'да' : 'нет'}`)
                    }
                }



                const comment = contactForm.querySelector('.form-comment')
                if(comment && comment.hasAttribute('data-field')) fields[comment.getAttribute('data-field')] = comment.value.trim()
                if(hasError) throw new Error('некоторые поля заполнены не правельно')
                return fetch(confirmChangesURL + '/ajax.php', {
                    method: 'POST',
                    body: JSON.stringify(fields)
                })
            }
        }


        //---------------------------------- buttons events listeners ----------------------------------
        const buttonsContainer = document.querySelector('.ui-form-buttons')
        if (buttonsContainer) {
            const saveButton = buttonsContainer.querySelector('.save-button')
            const cancelButton = buttonsContainer.querySelector('.cancel-button')

            if (saveButton) {
                saveButton.addEventListener('click', () => {
                    console.log('click save')
                    saveButton.classList.add('ui-btn-wait')
                    new Promise((r) => r(handleSaveClick()))
                        .then(() => BX.WindowManager.Get().Close())
                        .then(() => document.querySelector('#refloor-refresh')?.click())
                        .catch(e => {
                            console.error(e)
                            saveButton.classList.remove('ui-btn-wait')
                        })
                })
            }

            if (cancelButton) {
                cancelButton.addEventListener('click', () => {
                    console.log('click cancel')
                    BX.WindowManager.Get().Close()
                })
            }
        }
    })
</script>