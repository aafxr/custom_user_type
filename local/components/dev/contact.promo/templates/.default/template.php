<?php

\Bitrix\Main\UI\Extension::load("ui.buttons");

$promoList = $arResult['PROMO'] ?? [];
$itemsList = [];

foreach ($promoList as $k => $promo) {
    $res = CFile::GetPath(intval($promo['UF_PROMO_PHOTO'])) ?? '';
    $promoList[$k]['UF_PROMO_PHOTO'] = $res;
}

foreach ($arResult['ITEMS'] as $item) {
    $itemsList[$item['UF_PROMO_ID']] = $item;
}
?>

<div id="contact-promo" class="contact-promo">
    <button class="ui-btn ui-btn-light-border promo-dropdown">Выбрать promo</button>
</div>
<script>
    BX.ready(() => {
        const componentPath = '<?=$arResult['COMPONENT_PATH']?>';
        const CONTACT_ID = <?= $arResult['CONTACT_ID']; ?>;
        const promoContainer = document.getElementById('contact-promo')
        const dropDown = promoContainer.querySelector('.promo-dropdown')
        const promoList = <?= json_encode($promoList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const selectedPromo = <?= json_encode($itemsList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const promoToAdd = {}
        const promoToRemove = {}


        function getPopupContent() {
            const listNode = document.createElement('div')
            listNode.classList.add('promo-list')

            for (const p of promoList) {
                const itemNode = document.createElement('div')
                itemNode.classList.add('promo-list-item')
                if (selectedPromo[p.ID]) {
                    itemNode.classList.add('promo-list-item--selected')
                }
                itemNode.innerHTML = `
                        <div class="promo-list-item-photo">
                            <img src="${p.UF_PROMO_PHOTO}" alt="${p.UF_PROMO_VALUE}" />
                        </div>
                        <div class="promo-list-item-name">${p.UF_PROMO_VALUE}</div>
                    `

                itemNode.onclick = function (e) {

                    if (this.classList.contains('promo-list-item--selected')) {
                        delete promoToAdd[p.ID]
                        if (selectedPromo[p.ID]) {
                            promoToRemove[p.ID] = {UF_PROMO_ID: p.ID}
                        }
                    } else {
                        delete promoToRemove[p.ID]
                        if (!selectedPromo[p.ID]) {
                            promoToAdd[p.ID] = {UF_PROMO_ID: p.ID}
                        }
                    }
                    this.classList.toggle('promo-list-item--selected')
                }

                listNode.appendChild(itemNode)
            }
            return listNode
        }


        function handleSavePromo() {
            const data = {
                CONTACT_ID,
                promoToAdd: Array.from(Object.values(promoToAdd)),
                promoToRemove: Array.from(Object.values(promoToRemove))
            }
            return fetch(componentPath + '/ajax.php',{
                method: 'POST',
                body: JSON.stringify(data)
            })
                .then(r => r.json())
                .then(r => {
                    Object.values(promoToAdd).forEach(p => selectedPromo[p.UF_PROMO_ID] = p)
                    Object.values(promoToRemove).forEach(p => delete selectedPromo[p.UF_PROMO_ID])
                    console.log(r)
                    console.log(selectedPromo)
                })
                .catch(console.error)
        }


        const promoPopup = new BX.PopupWindow(
            "promo-popup-window",
            dropDown,
            {
                content: getPopupContent(),
                closeByEsc: true,
                autoHide: true,
                zIndex: 0,
                offsetLeft: 0,
                offsetTop: 0,
                draggable: {restrict: false},
                // height: 400,
                buttons: [
                    new BX.PopupWindowButton({
                        text: "Сохранить",
                        className: "popup-window-button-accept",
                        events: {
                            click: function () {
                                handleSavePromo()
                                    .then(() => this.popupWindow.close())
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text: "Закрыть",
                        className: "webform-button-link-cancel",
                        events: {
                            click: function () {
                                this.popupWindow.close();
                            }
                        }
                    })
                ],
                events: {
                    onPopupShow: function () {
                        // Событие при показе окна
                    },
                    onPopupClose: function () {
                        dropDown.classList.remove('--open')
                    }
                }
            });


        dropDown.addEventListener('click', e => {
            if (dropDown.classList.contains('--open')) {
                promoPopup.close()
                dropDown.classList.remove('--open')
            } else {
                promoPopup.show()
                dropDown.classList.add('--open')
            }
        })
    })
</script>