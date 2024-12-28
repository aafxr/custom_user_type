<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

?>
<div id="refloor-company-entity" class="refloor-flex-col">
    <div class="refloor-flex-row">
        <div class="refloor-crm-block flex-1">

            <div class="refloor-card-param">
                <div class="refloor-card-label">
                    Название
                </div>
                <div class="refloor-card-field-container">
                    <input class="refloor-field" name="TITLE" type="text" />
                </div>
            </div>


            #компонент карточка компании#
            <pre>
                <? print_r($arParams); ?>
                <? print_r($arResult); ?>
            </pre>
        </div>
        <div class="refloor-flex-col refloor-crm-right-side">
            <div class="refloor-crm-block">
                #ответственный#
            </div>
            <div class="refloor-crm-block">
                #категории#
            </div>
        </div>

    </div>
</div>