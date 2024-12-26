<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();
require_once ($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/custom/refloor_functions.php');

/** CUser */
global $USER;


class FormLeadClass
{
    const SITE_QUARTZPARQUET = 'quartzparquet';
    const SITE_FARGOSPC = 'fargospc';

    public $contactSource = false;
    public $formFields = false;
    public $company = false;
    public $contact = false;
    public $arCompany = false;
    public $arContact = false;
    public $contactPhone = false;
    public $contactMail = false;

    public $errors = [];


    public function __construct($contactSource, $fields)
    {
        $this->contactSource = $contactSource;
        $this->formFields = $fields;
    }


    public function convertFormFields(): bool
    {
        global $USER;
        switch ($this->contactSource){
            case FormLeadClass::SITE_QUARTZPARQUET:
                $this->convertFields(
                    $this->formFields['form_text_1'],
                    $this->formFields['form_text_2'],
                    $this->formFields['form_text_3'],
                    FormLeadClass::SITE_QUARTZPARQUET,
                    $this->formFields['form_text_13'],
                    'CUSTOMER',
                    $this->formFields['form_text_4'],
                    $USER->GetID()
                );
                break;
            case FormLeadClass::SITE_FARGOSPC:
                $this->convertFields(
                    $this->formFields['form_text_74'],
                    $this->formFields['form_text_75'],
                    $this->formFields['form_text_92'],
                    FormLeadClass::SITE_FARGOSPC,
                    $this->formFields['form_text_93'],
                    'CUSTOMER',
                    $this->formFields['form_text_83'],
                    $USER->GetID()
                );
                break;
            default:
                $this->errors[] = 'unknown source name';
                return false;
        }
        return true;
    }

    private function convertFields($cName, $cPhone, $cMail, $cSource, $companyTitle, $companyType, $companyCity, $createdById): void
    {
        [$name, $secondName, $lastName] = explode(' ', $cName);
        $this->contactPhone = $cPhone;
        $this->contactMail = $cMail;
        $this->arContact = [
            'NAME' => ($name.' '.$secondName) ?? '',
            'LAST_NAME' => $lastName ?? '',
            'FULL_NAME'   => $cName,
            "OPENED" => "Y", // "Доступен для всех" = Да
            'SOURCE_DESCRIPTION' => 'Пришел с '.$cSource,
            'CREATED_BY_ID' => $createdById,
            'MODIFY_BY_ID' => $createdById,
            'ASSIGNED_BY_ID' => $createdById,
        ];

        $this->arCompany = [
            'TITLE'   => $companyTitle,
            'COMPANY_TYPE' => $companyType,
            "OPENED" => "Y", // "Доступен для всех" = Да
            'UF_CITY_LIST' => $this->getCityId($companyCity),
            'UF_SOURCE_IB' => 'Пришел с '.$cSource,
            'CREATED_BY_ID' => $createdById,
            'MODIFY_BY_ID' => $createdById,
            'ASSIGNED_BY_ID' => $createdById,
        ];
    }


    private function getCityId($cityName){
        return getUfCityListValueId($cityName);
    }

    private function getSourceId($companyId){
        $arCompany = CCrmCompany::GetList([], ["ID" => $companyId])->fetch();

        $hlbl = 16; // CrmCompanyCategories - категории
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
        $bxSourceClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $IBLOCK_SOURCE_ID = 20;
        $elementId = CIBlockElement::GetList([],["IBLOCK_ID" => $IBLOCK_SOURCE_ID,'XML_ID' => $bxHLSource['UF_XML_ID']])->Fetch()['ID'];
    }


    public function bind(): bool
    {
        if(!isset($this->arCompany['ID']) || !isset($this->arContact['ID'])){
            $this->errors[] = 'bind fail. CompanyID: '.$this->arCompany['ID'].', ContactID: '.$this->arContact['ID'];
            return false;
        }
        \Bitrix\Crm\Binding\ContactCompanyTable::bindContactIDs($this->arCompany['ID'], [$this->arContact['ID']]);
        return true;
    }


    public function createAndBind(): bool
    {
        if(!$this->convertFormFields()) return false;
        if(!$this->createContact()) return false;
        if(!$this->createCompany()) return false;
        if(!$this->bind()) return false;
        return true;
    }




    public function createCompany(): bool
    {
        try {
            $result = \Bitrix\Crm\CompanyTable::add($this->arCompany);
            if($result->isSuccess())
            {
                $this->arCompany['ID'] = $result->getId();
                return true;
            }
            $this->errors[] = implode('\n', $result->getErrorMessages());
            $this->errors[] = 'failed create company';
            return false;
        } catch (Exception $e){
            $this->errors[] = $e->getMessage();
            return false;
        }
    }


    public function createContact(): bool
    {
        try {
            $result = \Bitrix\Crm\ContactTable::add($this->arContact);
            if($result->isSuccess())
            {
                $this->arContact['ID'] = $result->getId();
                $fm = new \CCrmFieldMulti();
                $value = $this->getMultiFields($result->getId(), $this->contactPhone, 'PHONE');
                if(!$fm->Add($value)){
                    $this->errors[] = $fm->LAST_ERROR . $this->contactPhone . ' phone fail '. json_encode($value);
                    return false;
                }
                $value = $this->getMultiFields($result->getId(), $this->contactMail, 'EMAIL');
                if(!$fm->Add($value)){
                    $this->errors[] = $fm->LAST_ERROR . $this->contactMail . ' email fail ' . json_encode($value);
                    return false;
                }
                $this->errors[] = 'contactID: ' . $result->getId();
                return true;
            }
            $this->errors[] = implode('\n', $result->getErrorMessages());
            $this->errors[] = 'failed create contact '.$result->getId();
            return false;
        } catch (Exception $e){
            $this->errors[] = $e->getMessage();
            return false;
        }
    }





    public function hasErrors(): bool
    {
        return boolval(count($this->errors));
    }


    public function getErrorMessage(): string
    {
        if(!$this->hasErrors()) return '';
        return implode('\n', $this->errors);
    }


    public function getMultiFields($contactID, $value, $typeID, $valueType = 'WORK'): array
    {
        $multiField = [
            'ENTITY_ID'  => \CCrmOwnerType::ContactName,
            'ELEMENT_ID' => $contactID,
            'TYPE_ID'    => $typeID,
            'VALUE_TYPE' => $valueType,
            'VALUE'      => $value
        ];
        if(isset($arFields['ID'])) $multiField['ID'] = $arFields['ID'];
        return $multiField;
    }
}