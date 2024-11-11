<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;//Для работы с языковыми переменными

class CMyComponentName extends CBitrixComponent
{
    public function onIncludeComponentLang()
    {
        Loc::loadMessages(dirname(__FILE__) . "/_class.php");
    }

    public function onPrepareComponentParams($arParams)
    {
        if (!isset($arParams["TEMPLATE_FOR_DATE"]) or (($arParams["TEMPLATE_FOR_DATE"]."") == "")) {$arParams["TEMPLATE_FOR_DATE"] = "Y-m-d";}
        return $arParams;
    }

    public function executeComponent()
    {
//если бы проверка установки параметра шаблона выводы даты проверялась в этом методе то можно расскомментировать строку ниже
//        if (!isset($this->arParams["TEMPLATE_FOR_DATE"]) or (($this->arParams["TEMPLATE_FOR_DATE"]."") == "")) {$this->arParams["TEMPLATE_FOR_DATE"] = "Y-m-d";}

//Установка значения даты в массиве arResult, который является свойством текущего объекта в соответствии с заданным шаблоном даты в параметрах компонента
        $this->arResult["DATE"] = date($this->arParams["TEMPLATE_FOR_DATE"],time());

        $this->IncludeComponentTemplate();
    }
}
