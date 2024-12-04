<?php

\Bitrix\Main\UI\Extension::load("ui.buttons");

$promoList = $arResult['PROMO'] ?? [];
$itemsList = [];

foreach ($promoList as $k => $promo){
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
            const promoContainer = document.getElementById('contact-promo')
            const dropDown = promoContainer.querySelector('.promo-dropdown')
            const promoList = <?= json_encode($promoList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const selectedPromo = <?= json_encode($itemsList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

            console.log(promoList)
            console.log(selectedPromo)


            function getPopupContent() {
                const listNode = document.createElement('div')
                listNode.classList.add('promo-list')

                for (const p of promoList){
                    const itemNode = document.createElement('div')
                    itemNode.classList.add('promo-list-item')
                    if(selectedPromo[p.UF_PROMO_ID]) {
                        itemNode.classList.add('promo-list-item--selected')
                    }
                    itemNode.innerHTML = `
                        <div class="promo-list-item-photo">
                            <img src="${p.UF_PROMO_PHOTO}" alt="${p.UF_PROMO_VALUE}" />
                        </div>
                        <div class="promo-list-item-name">${p.UF_PROMO_VALUE}</div>
                    `

                    itemNode.onclick = function(e){
                        if(this.classList.contains('promo-list-item--selected')){
                            delete selectedPromo[p.UF_PROMO_ID]
                            this.classList.remove('promo-list-item--selected')
                        } else{
                            selectedPromo[p.UF_PROMO_ID] = {UF_PROMO_ID: p.UF_PROMO_ID}
                            this.classList.add('promo-list-item--selected')
                        }
                    }

                    listNode.appendChild(itemNode)
                }
                return listNode
            }


            function handleSavePromo(){
                console.log(selectedPromo)
            }


            const promoPopup = new BX.PopupWindow(
                "promo-popup-window",
                dropDown,
                {
                    content: getPopupContent(),
                    closeByEsc : true,
                    autoHide : true,
                    zIndex: 0,
                    offsetLeft: 0,
                    offsetTop: 0,
                    draggable: {restrict: false},
                    width: 800,
                    buttons: [
                        new BX.PopupWindowButton({
                            text: "Сохранить",
                            className: "popup-window-button-accept",
                            events: {
                                click: function () {
                                    handleSavePromo()
                                    this.popupWindow.close();
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
                        onPopupShow: function() {
                            // Событие при показе окна
                        },
                        onPopupClose: function() {
                            dropDown.classList.remove('--open')
                        }
                    }
                });


            dropDown.addEventListener('click', e => {
                if(dropDown.classList.contains('--open')){
                    promoPopup.close()
                    dropDown.classList.remove('--open')
                } else{
                    promoPopup.show()
                    dropDown.classList.add('--open')
                }
            })
        })
    </script>