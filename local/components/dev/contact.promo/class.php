<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true ) die();

class CContactPromo extends \CBitrixComponent
{
    private $hlBlockId = 20;
    function onPrepareComponentParams($arParams)
    {
        $this->arParams['CONTACT_ID'] = $arParams['CONTACT_ID'] ?? '';
        $this->arParams['CLASS_NAME'] = $arParams['CLASS_NAME'] ?? '';

        $width = intval($arParams['PHOTO_WIDTH']);
        $height = intval($arParams['PHOTO_HEIGHT']);
        $this->arParams['PHOTO_WIDTH'] = $width ? $width : 40;
        $this->arParams['PHOTO_HEIGHT'] = $height ? $height : 40;

        $this->arParams['DEV'] = $arParams['DEV'] ?? false;

        return $arParams;
    }

    function executeComponent()
    {
        $this->arResult['COMPONENT_PATH'] = $this->GetPath();
        $this->arResult['CONTACT_ID'] = $this->arParams['CONTACT_ID'];
        $this->arResult['CLASS_NAME'] = $this->arParams['CLASS_NAME'];
        $this->arResult['ITEMS'] = $this->GetPromoItems();
        $this->arResult['PROMO'] = $this->GetPromoList();

        $this->arResult['PHOTO_WIDTH'] = $this->arParams['PHOTO_WIDTH'];
        $this->arResult['PHOTO_HEIGHT'] = $this->arParams['PHOTO_HEIGHT'];
        $this->arResult['DEV'] = $this->arParams['DEV'];
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
            "order" => array("UF_CREATED_AT" => "DESC"),
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
            $creator = $this->GetUser($arData['UF_CREATED_BY']);
            if($creator){
                $arData['CREATOR_NAME'] = $creator['LAST_NAME'].' '.$creator['NAME'];
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


    function GetUser($creatorID){
        return CUser::GetByID($creatorID)->Fetch();
    }
}