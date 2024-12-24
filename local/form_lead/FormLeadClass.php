<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();


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
        switch ($this->contactSource){
            case FormLeadClass::SITE_QUARTZPARQUET:
                $this->convertQuartzparquetFields();
                break;
            default:
                $this->errors[] = 'unknown source name';
                return false;
        }
        return true;
    }


    private function convertQuartzparquetFields()
    {
        $this->arContact = [];
        [$name, $secondName, $lastName] = explode(' ', $this->formFields['form_text_1']);
//        $this->arContact['NAME'] = $name.' '.$secondName;
//        $this->arContact['LAST_NAME'] = $lastName;
//        $this->arContact['FULL_NAME'] = $this->formFields['form_text_1'];
//        $this->arContact['ADDRESS'] = $this->formFields['form_text_4'];
        $this->contactPhone = $this->formFields['form_text_2'];
        $this->contactMail = $this->formFields['form_text_3'];
        $this->arContact = [
            'NAME' => ($name.' '.$secondName) ?? '',
            'LAST_NAME' => $lastName ?? '',
            'FULL_NAME'   => $this->formFields['form_text_1'],
            "OPENED" => "Y", // "Доступен для всех" = Да
            'SOURCE_DESCRIPTION' => 'Пришел с '.FormLeadClass::SITE_QUARTZPARQUET,
        ];

        $this->arCompany = [
            'TITLE'   => $this->formFields['form_text_13'],
            'COMPANY_TYPE' => 'CUSTOMER',
            "OPENED" => "Y", // "Доступен для всех" = Да
            'UF_CITY_LIST' => $this->formFields['form_text_4']
        ];

//        $this->arCompany['UF_CITY_LIST'] = $this->formFields['form_text_4'];
//        $this->arCompany['TITLE'] = $this->formFields['form_text_13'];
    }


    public function bind(): bool
    {
        if(!isset($this->arCompany['ID']) || !isset($this->arContact['ID'])){
            $this->errors[] = 'bind fail. CompanyID: '.$this->arCompany['ID'].', ContactID: '.$this->arContact['ID'];
            return false;
        }
        \Bitrix\Crm\Binding\ContactCompanyTable::bindContactIDs(
            $this->arCompany['ID'],
            [$this->arContact['ID']]
        );
        return true;
    }


    public function createAndBind()
    {
        if(!$this->convertFormFields()) return false;
        if(!$this->createContact()) return false;
//        $this->arCompany['CONTACT_ID'] = [$this->arContact['ID']];
        if(!$this->createCompany()) return false;
        if(!$this->bind()) return false;
        return true;
    }




    public function createCompany(): bool
    {
        $this->company = new \CCrmCompany(false);
        $id = $this->company->Add($this->arCompany);
        if($id)
        {
            $this->arCompany['ID'] = $id;
            $this->errors[] = 'companyID: ' . $id;
            return true;
        }
        $this->errors[] = 'failed create company';
        return false;
    }


    public function createContact(): bool
    {
        $this->contact = new \CCrmContact(false);
        $id = $this->contact->Add($this->arContact);
        if($id)
        {
            $this->arContact['ID'] = $id;
            $fm = new \CCrmFieldMulti();
            $value = $this->getMultiFields($id, $this->contactPhone, 'PHONE');
            if(!$fm->Add($value)){
                $this->errors[] = $fm->LAST_ERROR . $this->contactPhone . ' phone fail '. json_encode($value);
                return false;
            }
            $value = $this->getMultiFields($id, $this->contactMail, 'EMAIL');
            if(!$fm->Add($value)){
                $this->errors[] = $fm->LAST_ERROR . $this->contactMail . ' email fail ' . json_encode($value);
                return false;
            }
            $this->errors[] = 'contactID: ' . $id;
            return true;
        }
        $this->errors[] = 'failed create contact '.$id;
        return false;
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


    public function getMultiFields($contactID, $arFields, $typeID, $valueType = 'WORK'): array
    {
        $multiField = [
            'ENTITY_ID'  => \CCrmOwnerType::ContactName,
            'ELEMENT_ID' => $contactID,
            'TYPE_ID'    => $typeID,
            'VALUE_TYPE' => $valueType,
            'VALUE'      => $arFields['VALUE']
        ];
        if(isset($arFields['ID'])) $multiField['ID'] = $arFields['ID'];
        return $multiField;
    }
}