<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();


class CContactPopup extends \CBitrixComponent
{
    function onPrepareComponentParams($arParams)
    {
//        CContactPopup::log('onPrepareComponentParams', $arParams );
        $this->arParams['FOLDER_ID'] = $arParams['FOLDER_ID'];
        $this->arParams['CLASS_NAME'] = $arParams['CLASS_NAME'];
        return $arParams;
    }

    function executeComponent()
    {
        $this->arResult['FOLDER_ID'] = $this->arParams['FOLDER_ID'];
        $this->arResult['CLASS_NAME'] = $this->arParams['CLASS_NAME'];
        $this->arResult['COMPONENT_PATH'] = $this->GetPath();
        $this->includeComponentTemplate();
    }

}