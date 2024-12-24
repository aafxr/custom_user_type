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


    public function convertFormFields()
    {
        switch ($this->contactSource){
            case FormLeadClass::SITE_QUARTZPARQUET:
                $this->convertQuartzparquetFields();
                break;
            default:
                $this->errors[] = 'unknown source name';
        }
    }


    private function convertQuartzparquetFields()
    {
        $this->arContact = [];
        [$name, $secondName, $lastName] = explode('', $this->formFields['form_text_1']);
        $this->arContact['NAME'] = $name.' '.$secondName;
        $this->arContact['LAST_NAME'] = $lastName;
        $this->arContact['FULL_NAME'] = $this->formFields['form_text_1'];
        $this->contactPhone = $this->formFields['form_text_2'];
        $this->contactMail = $this->formFields['form_text_3'];



    }

    public function createCompany(): bool
    {
        $this->company = new \CCrmCompany;
        $id = $this->company->Add($this->arCompany);
        if($id)
        {
            $this->arCompany['ID'] = $id;
            return true;
        }
        $this->errors[] = 'failed create company';
        return false;
    }


    public function createContact(): bool
    {
        $this->contact = new \CCrmContact;
        $id = $this->contact->Add($this->arContact);
        if($id)
        {
            $this->arCompany['ID'] = $id;
            return true;
        }
        $this->errors[] = 'failed create contact';
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
}