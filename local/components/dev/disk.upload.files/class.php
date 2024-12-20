<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();


class CContactPopup extends \CBitrixComponent
{
    function onPrepareComponentParams($arParams)
    {
//        CContactPopup::log('onPrepareComponentParams', $arParams );
        $this->arParams['FOLDER_ID'] = $arParams['FOLDER_ID'];
        return $arParams;
    }

    function executeComponent()
    {
        $this->arResult['FOLDER_ID'] = $this->arParams['FOLDER_ID'];
        $this->arResult['COMPONENT_PATH'] = $this->GetPath();
        $this->includeComponentTemplate();
    }

}