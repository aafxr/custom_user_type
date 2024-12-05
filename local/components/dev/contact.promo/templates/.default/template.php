<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();


\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.forms");

$NODE_ID = 'contact-promo_' . strval(rand(0, PHP_INT_MAX));

$promoList = $arResult['PROMO'] ?? [];
$itemsList = [];

foreach ($promoList as $k => $promo) {
    $res = CFile::GetPath(intval($promo['UF_PROMO_PHOTO'])) ?? '';
    $promoList[$k]['UF_PROMO_PHOTO'] = $res;
}

foreach ($arResult['ITEMS'] as $item) {
    $itemsList[] = $item;
}
?>

<div id="<?=$NODE_ID;?>" class="contact-promo <?= $arResult['CLASS_NAME']; ?>">
    <div class="selected-promo-list">
    </div>
    <button class="ui-btn ui-btn-success promo-dropdown-button">Добавить promo</button>
</div>
<script>
    BX.ready(() => {
        const componentPath = '<?=$arResult['COMPONENT_PATH']?>';
        const CONTACT_ID = <?= $arResult['CONTACT_ID']; ?>;
        const promoContainer = document.getElementById('<?=$NODE_ID;?>')
        const selectedPromoListNode = document.querySelector('.selected-promo-list')
        const dropDown = promoContainer.querySelector('.promo-dropdown-button')
        const promoItems = <?= json_encode($promoList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        let selectedPromo = <?= json_encode($itemsList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        let promoToAdd = {}
        let promoToRemove = {}

        console.log(selectedPromo)


        function updateSelectedPromoListNode(){
            let selectedInner = ''
            for (const p of selectedPromo ){
                const promoItem = promoItems.find(e => e.ID === p.UF_PROMO_ID)
                if(promoItem){
                    selectedInner += `
                    <div class="ui-ctl ui-ctl-before-icon  ui-ctl-ext-after-icon selected-promo-list-item" data-promo-id="${p.ID}">
                        <div class="selected-promo-list-item-content">
                            <div class="selected-promo-list-item-photo">
                                ${promoItem.UF_PROMO_PHOTO ? `<img src="${promoItem.UF_PROMO_PHOTO}" alt="" />` : ''}
                            </div>
                            <div class="selected-promo-list-item-inner">
                                <div class="selected-promo-list-item-name">${promoItem.UF_PROMO_VALUE}</div>
                                <div class="selected-promo-list-item-date">${p.UF_CREATED_AT}</div>
                            </div>
                        </div>
                        <button class="ui-ctl-after ui-ctl-icon-clear selected-promo-list-item-remove-btn"></button>
                    </div>
                    `
                }
            }
            selectedPromoListNode.innerHTML = selectedInner
        }


        function askToRemovePromo(promo){
            const p = promoItems.find(e => e.ID === promo.UF_PROMO_ID)
            if(p){
                return confirm(`Удалить "${p.UF_PROMO_VALUE}"?`)
            }
            return false
        }



        if(selectedPromoListNode){
            updateSelectedPromoListNode()
            selectedPromoListNode.addEventListener('click', e => {
                if(e.target.classList.contains('selected-promo-list-item-remove-btn')) {
                    const selectedPromoItemNode = e.target.closest('.selected-promo-list-item')
                    if(!selectedPromoItemNode) return
                    const promoId = selectedPromoItemNode.dataset.promoId
                    const removePromo = selectedPromo.find(e => e.ID === promoId)
                    if(removePromo && askToRemovePromo(removePromo)){
                        promoToRemove[removePromo.ID] = removePromo
                        handleSavePromo().catch(console.error)
                    }
                }
            })
        }


        function getPopupContent() {
            const listNode = document.createElement('div')
            listNode.classList.add('promo-list')

            for (const p of promoItems) {
                const itemNode = document.createElement('div')
                itemNode.classList.add('promo-list-item')
                if (promoToAdd[p.ID]) {
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
                    } else {
                        promoToAdd[p.ID] = {UF_PROMO_ID: p.ID}
                    }
                    this.classList.toggle('promo-list-item--selected')
                }

                listNode.appendChild(itemNode)
            }
            return listNode
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
                        className: "ui-btn cancel-button cancel-button",
                        events: {
                            click: function () {
                                promoToAdd = {}
                                promoToRemove = {}
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


        /**
         * @returns {Promise}
         */
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
                    // Object.values(promoToAdd).forEach(p => selectedPromo[p.UF_PROMO_ID] = p)
                    // Object.values(promoToRemove).forEach(p => delete selectedPromo[p.UF_PROMO_ID])
                    console.log(r)
                    if(r.ok && r.list) {
                        selectedPromo = r.list
                        promoToAdd = {}
                        promoToRemove = {}
                        updateSelectedPromoListNode()
                        promoPopup.setContent(getPopupContent())
                    }
                    console.log(selectedPromo)
                })
        }


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