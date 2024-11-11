<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
echo '<div class="box">' . Loc::getMessage("NIKOLAEVEVGE_DATE_CURRENT_TEMPLATE_LABEL_TEXT") . ": " . $arResult["DATE"] . '</div>';
