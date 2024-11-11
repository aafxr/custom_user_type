<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

class CCompanyContacts extends CBitrixComponent{
    function OnPrepareComponentParams($arParams){
        $this->arParams = $arParams;
        $this->arResult['CUSTOM'] = $arResult['CUSTOM'] ?? '13';
        return $arParams;
    }

    function executeComponent(){
//         $this->arResult;
        $this->includeComponentTemplate();
    }
}