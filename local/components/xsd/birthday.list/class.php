<?php

class BirthdayListComponent extends \CBitrixComponent
{
    const GRID_ID = 'REFLOOR_BIRTHDAY_LIST';
    const PAGE_SIZE = 15;

    private $listCity = [];

    function getColumns() {
        $columns = [
            /*['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],*/
            ['id' => 'DATE', 'name' => 'Дата', 'sort' => 'DATE', 'default' => true],
            ['id' => 'CONTACT', 'name' => 'Контакт', 'sort' => 'AMOUNT', 'default' => true],
            ['id' => 'CITY', 'name' => 'Город', 'sort' => 'AMOUNT', 'default' => true],

            ['id' => 'COMPANY', 'name' => 'Компания', 'sort' => 'PAYER_INN', 'default' => true],
            ['id' => 'ASSIGNED_BY', 'name' => 'Ответственный', 'sort' => 'AMOUNT', 'default' => true],

        ];
        return $columns;
    }

    public function executeComponent()
    {

        $userFieldId = 1708;

        $obEnum = new CUserFieldEnum();
        $rsEnum = $obEnum->GetList(
            [
                "VALUE" => 'ASC'
            ],
            [
                "USER_FIELD_ID" => $userFieldId,
            ]
        );

        $enum = array();
        while($arEnum = $rsEnum->Fetch()) {
            $arCityList[$arEnum["ID"]] = $arEnum["VALUE"];
        }

        global $USER;
        $grid_id = self::GRID_ID;

        $this->arResult['COLUMNS'] = $this->getColumns();
        $this->arResult['GRID_ID'] = $grid_id;
        $dateCode   = $this->arParams['DATE'];
        
        $list = [];

        if(!$dateCode) {
            $dateCode = date("dm");
        }
        \Bitrix\Main\Loader::includeModule('crm');

        $contactResult = CCrmContact::GetListEx(
            [
                'SOURCE_ID' => 'DESC'
            ],
            [
                'UF_BIRTH_DM' => $dateCode,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            false,
            [
                'ID',
                'FULL_NAME',
                'COMPANY_ID',
                'COMPANY_TITLE',
                'POST',
                'BIRTHDATE'
            ]
        );


        $count = 0;
        while( $contact = $contactResult->fetch() )
        {
            $count++;
            /**
             * [ 'ID' => ..., 'TITLE' => ... ]
             * @var array
             */
            /*echo '<pre>';
            print_r($contact);
            echo '</pre>';*/



            $date =  new \DateTime($contact['BIRTHDATE']);

            $companyId = $contact['COMPANY_ID'];
            $companyText = "";
            $cityText = "";
            if ($companyId) {

                $entityTypeId = \CCrmOwnerType::Company;
                $entityUrl = \CCrmOwnerType::GetEntityShowPath($entityTypeId, $companyId);

                $arCompany = CCrmCompany::GetList([], ["ID" => $companyId], ['ASSIGNED_BY_ID','COMPANY_TYPE','TITLE',  'UF_*'])->Fetch();
                $companyText = '<a href="' . $entityUrl . '"><b>' . $arCompany['TITLE'] . '</b></a>';
                $cityText = $arCityList[$arCompany['UF_CITY_LIST']];

                $company = CCrmCompany::GetByID($companyId);
            }

            $contactText =$contact['FULL_NAME'];
            $dbResult = CCrmFieldMulti::GetList([
                'ID' => 'asc',
            ], [
                'ENTITY_ID' => 'CONTACT',
                /*'TYPE_ID' => 'PHONE',*/
                'ELEMENT_ID' => $contact['ID'],
            ]);
            while ($fields = $dbResult->Fetch()) {
                if($fields['TYPE_ID'] == 'PHONE') {
                    $phone = preg_replace("/[^,.0-9]/", '', $fields['VALUE']);;
                    $contactText .= "<div><a href='tel:".$phone."'>".$fields['VALUE']."</a></div>";
                }
                if($fields['TYPE_ID'] == 'EMAIL') {
                    $contactText .= "<div><a href='mailto:".$fields['VALUE']."'>".$fields['VALUE']."</a></div>";
                }
            }

            $listItem = [
                'id' => 'contact_' . $contact['ID'],
                'data' => [
                    /*'ID' => $contact['ID'],*/
                    'DATE' => $date->format("d.m.Y"),
                    'CONTACT' => $contactText,
                    'COMPANY' => $companyText,
                    'CITY' => $cityText,
                    'ASSIGNED_BY' =>  $company['ASSIGNED_BY_NAME']." ".$company['ASSIGNED_BY_LAST_NAME']
                ],
                'actions' => [

                ],
            ];
            if($company['ASSIGNED_BY_ID'] == $USER->GetID()) {
                array_unshift($list, $listItem);
            } else {
                $list[] = $listItem;
            }

        }
        //echo "#".$count."#".$dateCode."<br />";


        $this->arResult['LIST'] = $list;

        $this->includeComponentTemplate();
    }


}