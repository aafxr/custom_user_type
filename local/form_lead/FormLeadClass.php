<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();
require_once ($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/custom/refloor_functions.php');

/** CUser */
global $USER;

class FormLeadClass
{
    const FARGO_DROPDOWN_CONVERT = [
        '84' => 1,
        '85' => 2,
        '86' => 3,
        '87' => 4,
        '88' => 5,
        '89' => 6,
        '90' => 7,
        '91' => 8,
    ];


    const SITE_QUARTZPARQUET = 'quartzparquet';
    const SITE_FARGOSPC = 'fargospc';

    private $options = [];
    private $company = false;
    private $contact = false;
    private $arCompany = [];
    private $arContact = [];
    private $contactPhone = false;
    private $contactMail = false;

    private $errors = [];


    /**
     *
     * 'COMPANY_TYPE' - detCompanyType(1-8)    (getCompanyType.php)
     *
     *
     * 'UF_CITY_LIST' - getUfCityListValueId($cityName)    (/local/php_interface/custom/refloor_functions.php)
     *
     *
     * 'UF_SOURCE_IB' - infoblock  id=20 значение поля UF_XML_ID
     *
     *
     * ```
     *  $options = [
     *      'CUSTOMER' => [
     *          'NAME'  => '',
     *          'LAST_NAME' => '',
     *          'FULL_NAME' => '',
     *          'SOURCE_DESCRIPTION' => '',
     *      ],
     *      'COMPANY' => [
     *          'TITLE' => '',
     *          'COMPANY_TYPE' => '',
     *          'UF_CITY_LIST' => '',
     *          'UF_SOURCE_IB' => '',
     *      ],
     *      'PHONE' => '',
     *      'MAIL' => '',
     *      'RESPONSIBLE' => ''
     *  ];
     * ```
     *
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    private function convertFields(): void
    {
        $this->contactPhone = $this->options['PHONE'];
        $this->contactMail = $this->options['MAIL'];

        $this->arContact['NAME'] = $this->options['CUSTOMER']['NAME'];
        $this->arContact['LAST_NAME'] = $this->options['CUSTOMER']['LAST_NAME'];
        $this->arContact['FULL_NAME'] = $this->options['CUSTOMER']['FULL_NAME'];
        $this->arContact['SOURCE_DESCRIPTION'] = $this->options['CUSTOMER']['SOURCE_DESCRIPTION'];
        $this->arContact['OPENED'] = 'Y';
        $this->arContact['CREATED_BY_ID'] = $this->options['RESPONSIBLE'];
        $this->arContact['MODIFY_BY_ID'] = $this->options['RESPONSIBLE'];
        $this->arContact['ASSIGNED_BY_ID'] = $this->options['RESPONSIBLE'];


        $this->arCompany['TITLE'] = $this->options['COMPANY']['TITLE'];
        $this->arCompany['COMPANY_TYPE'] = $this->options['COMPANY']['COMPANY_TYPE'];
        $this->arCompany['UF_CITY_LIST'] = $this->options['COMPANY']['UF_CITY_LIST'];
        $this->arCompany['UF_SOURCE_IB'] = $this->options['COMPANY']['UF_SOURCE_IB'];
        $this->arCompany['OPENED'] = 'Y';
        $this->arCompany['CREATED_BY_ID'] = $this->options['RESPONSIBLE'];
        $this->arCompany['MODIFY_BY_ID'] = $this->options['RESPONSIBLE'];
        $this->arCompany['ASSIGNED_BY_ID'] = $this->options['RESPONSIBLE'];
    }


    private function getCityId($cityName){
        return getUfCityListValueId($cityName);
    }

    private function getSourceId($companyId){
//        $arCompany = CCrmCompany::GetList([], ["ID" => $companyId])->fetch();
//
//        $hlbl = 16; // CrmCompanyCategories - категории
//        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
//        $bxSourceClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
//
//        $IBLOCK_SOURCE_ID = 20;
//        $elementId = CIBlockElement::GetList([],["IBLOCK_ID" => $IBLOCK_SOURCE_ID,'XML_ID' => $bxHLSource['UF_XML_ID']])->Fetch()['ID'];
    }


    private function bind(): bool
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
        $this->convertFields();
        if(!$this->createContact()) return false;
        if(!$this->createCompany()) return false;
        if(!$this->bind()) return false;
        return true;
    }




    private function createCompany(): bool
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


    private function createContact(): bool
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


    private function getMultiFields($contactID, $value, $typeID, $valueType = 'WORK'): array
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