<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();


\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.forms");

$NODE_ID = 'contact-promo_' . strval(rand(0, PHP_INT_MAX));

$promoList = $arResult['PROMO'] ?? [];
$itemsList = [];

$promoPhotoSize = [
    "width" => $arResult['PHOTO_WIDTH'],
    "height" => $arResult['PHOTO_HEIGHT']
];

//if($arResult['DEV']){
//    echo "<pre>";
//    print_r($promoPhotoSize);
//    echo "</pre>";
//}

foreach ($promoList as $k => $promo) {
    $res = CFile::ResizeImageGet(intval($promo['UF_PROMO_PHOTO']), $promoPhotoSize) ?? '';
    $promoList[$k]['UF_PROMO_PHOTO'] = $res['src'];
//    if($arResult['DEV']){
//        echo "<pre>";
//        print_r($res);
//        echo "</pre>";
//    }
}

foreach ($arResult['ITEMS'] as $item) {
    $itemsList[] = $item;
}
?>

<div id="<?=$NODE_ID;?>" class="contact-promo <?= $arResult['CLASS_NAME']; ?>">
    <div class="contact-promo-preview-list"></div>
</div>

<script>
    BX.ready(() => {
        const componentPath = '<?=$arResult['COMPONENT_PATH']?>';
        const CONTACT_ID = <?= $arResult['CONTACT_ID']; ?>;
        const promoContainer = document.getElementById('<?=$NODE_ID;?>')
        const previewsContainer = promoContainer.querySelector('.contact-promo-preview-list')
        const promoItems = <?= json_encode($promoList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const selectedPromo = <?= json_encode($itemsList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function updatePreviews(){
            if(!previewsContainer) return
            let selectedInner = ''
            for (const p of selectedPromo ){
                const promoItem = promoItems.find(e => e.ID === p.UF_PROMO_ID)
                if(promoItem){
                    const title = `${promoItem.UF_PROMO_VALUE}\nДобавил: ${p.CREATOR_NAME}\nДата: ${p.UF_CREATED_AT}`
                    selectedInner += `
                        <div class="contact-promo-preview-list-item" title="${title}" data-promo-id="${p.ID}">
                            <img src="${promoItem.UF_PROMO_PHOTO}" alt="" />
                        </div>
                    `
                }
            }
            previewsContainer.innerHTML = selectedInner
        }

        updatePreviews()
    })
</script>
