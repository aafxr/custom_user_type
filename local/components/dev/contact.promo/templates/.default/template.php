<?php

\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.select");

$promoList = $arResult['PROMO'] ?? [];
$itemsList = [];

foreach ($arResult['ITEMS'] as $item){
    $itemsList[$item['UF_PROMO_ID']] = $item;
}

$itemsList[1] = [];
$itemsList[4] = [];

?>

    <div id="contact-promo" class="contact-promo">
        <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown promo-dropdown">
            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <div class="ui-ctl-element"> Выбранная опция </div>
            <div class="dropdown-options">
                <? foreach ($promoList as $promo){
                    $checked = boolval( $itemsList[$promo['UF_PROMO_ID']] );
                ?>
                    <div
                        class="ui-ctl-element ui-ctl-after-icon dropdown-options-item <?= $checked ? 'ui-ctl-icon-clear checked' : ''; ?>"
                        title="<?= $promo['UF_PROMO_VALUE']; ?>"
                        data-item-id="<?= $promo['UF_PROMO_ID']?>"
                        data-item-value="<?= $promo['UF_PROMO_VALUE']?>"
                    > <?= $promo['UF_PROMO_VALUE']?> </div>
                <? } ?>

            </div>
        </div>

</div>

