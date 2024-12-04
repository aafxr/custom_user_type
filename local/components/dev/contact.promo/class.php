<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

class CContactPromo extends \CBitrixComponent
{
    private $hlBlockId = 20;
    function onPrepareComponentParams($arParams)
    {
        $this->arParams['CONTACT_ID'] = $arParams['CONTACT_ID'] ?? '';
        return $arParams;
    }

    function executeComponent()
    {
        $this->arResult['COMPONENT_PATH'] = $this->GetPath();
        $this->arResult['CONTACT_ID'] = $this->arParams['CONTACT_ID'];
        $this->arResult['ITEMS'] = $this->GetPromoItems();
        $this->arResult['PROMO'] = $this->GetPromoList();
        $this->includeComponentTemplate();
    }


    /**
     * список созданных промодля контакта
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    function GetPromoItems()
    {
        if (!isset($this->arParams['CONTACT_ID'])) return [];
        if (!CModule::IncludeModule('highloadblock')) return [];
        $arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById($this->hlBlockId)->fetch();
        $obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
        $entityDataClass = $obEntity->getDataClass();

        $rsData = $entityDataClass::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => array(
                "UF_CONTACT_ID"=>$this->arParams['CONTACT_ID'],
                "UF_DELETED_AT" => null,
            )
        ));

        $items = [];
        while($arData = $rsData->Fetch()){
            $arData['UF_CREATED_AT'] = FormatDateFromDB($arData['UF_CREATED_AT']);
            if($arData['UF_DELETED_AT']){
                $arData['UF_DELETED_AT'] = FormatDateFromDB($arData['UF_DELETED_AT']);
            }
            $items[] = $arData;
        }
        return $items;
    }


    /**
     * список всез промо
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    function GetPromoList()
    {
        $arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById(21)->fetch();
        $obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
        $entityDataClass = $obEntity->getDataClass();

        $rsData = $entityDataClass::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC")
        ));

        $items = [];
        while($arData = $rsData->Fetch()){
            $items[] = $arData;
        }
        return $items;
    }
}