<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;//Для работы с языковыми переменными

Loc::loadMessages(dirname(__FILE__) . "/_class.php");//Если нужная языковая переменная загрузится, то попытка загрузить её из языкового файла component.php производиться не будет, из class.php попытка в любом случаи производиться не будет. Если Вам всё же требуется загрузить языковые переменные из языкового файла с именем class.php требуется явно указать необходимость такой загрузки, даже если для размещения кода компонента Вы используете файл class.php(то-есть подход d7)

if (!isset($arParams["TEMPLATE_FOR_DATE"]) or (($arParams["TEMPLATE_FOR_DATE"]."") == "")) {$arParams["TEMPLATE_FOR_DATE"] = "Y-m-d";}
$arResult["DATE"] = date($arParams["TEMPLATE_FOR_DATE"],time());
$this->IncludeComponentTemplate();
