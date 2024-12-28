<?php

class TaskReportPopup extends \CBitrixComponent
{
    //const GRID_ID = 'REFLOOR_TASK_START_GRID';
    //const PAGE_SIZE = 15;

    function getCompanyType($userId) {
        $projectDep = \CIntranetUtils::GetDeparmentsTree(32, true);

        $resUsers = \Bitrix\Intranet\Util::getDepartmentEmployees([
            'DEPARTMENTS' => $projectDep,
            'RECURSIVE'   => 'Y',
        ]);
        $projectUsers = [];
        while($arUser = $resUsers->fetch() ) {
            $projectUsers[] = $arUser['ID'];
        }

        $projectDIY = \CIntranetUtils::GetDeparmentsTree(33, true);
        $resUsersDIY = \Bitrix\Intranet\Util::getDepartmentEmployees([
            'DEPARTMENTS' => $projectDIY,
            'RECURSIVE'   => 'Y',
        ]);
        $DIYUsers = [];
        while($arUserDIY = $resUsersDIY->fetch() ) {
            $DIYUsers[] = $arUserDIY['ID'];
        }
        $DIYUsers = [65,220,228];
        if(in_array($userId, $projectUsers)) {
            $companyType = 'COMPETITOR';
        } else {
            if(in_array($userId, $DIYUsers)) {
                $companyType = '1';
            } else {
                $companyType = 'SUPPLIER';
            }
        }

        return $companyType;
    }

    public function executeComponent()
    {
        // $grid_id = self::GRID_ID ;
        // $grid_options = new Grid\Options($grid_id);
        global $USER;
        $userId = $USER->GetID();
        $taskId = (int)$this->arParams['TASK_ID'];
        $arTask = CTasks::GetList([],['ID'=>$taskId],['*','UF_*'])->fetch();

        $crmEntityId = false;
        if($arTask["UF_CRM_TASK"][0]) {
            $crmEntityCode = $arTask["UF_CRM_TASK"][0];
            $arCrmEntityCode = explode("_", $crmEntityCode);
            $crmEntityType = $arCrmEntityCode[0];
            $crmEntityId = $arCrmEntityCode[1];
        }

        $isCompleted = ($arTask['STATUS'] == CTasks::STATE_COMPLETED);

        $this->arResult['COMPONENT_PATH'] = $this->GetPath();
        $this->arResult['TASK'] = $arTask;
        $this->arResult['COMPLETED'] = $isCompleted = ($arTask['STATUS'] == CTasks::STATE_COMPLETED) ? 'Y' : 'N';

        if($isCompleted != "Y") {
            /**
             * поулчаем список типов задач
             */
            $taskTypesList = [];
            $companyTypeId = $this->getCompanyType($userId);
            Cmodule::includeModule("highloadblock");
            $hlbl = 2; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
            $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
            $taskTypesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

            /*$taskTypesRes = $taskTypesClass::getList(
                [
                    'order'=>['UF_CODE'=>'ASC','UF_NAME'=>'ASC'],
                    'filter'=>['UF_COMPANY_TYPE'=>$this->getCompanyType($userId)]
                ]); */

            $taskTypesRes = $taskTypesClass::getList(
                [
                    'order'=>['UF_COMPANY_TYPE'=>'ASC','UF_CODE'=>'ASC','UF_NAME'=>'ASC'],
                    'filter'=>[
                        [
                            'LOGIC' => 'OR',
                            /*[
                                'ID' => [310]
                            ],*/
                            [
                                'UF_COMPANY_TYPE'=>$companyTypeId
                            ],
                            [
                                'ID' => [310,288,289]
                            ]
                        ]

                    ]
                ]);


            $taskTypesList = [];
            while ($taskTypeData = $taskTypesRes->Fetch()) {
                $taskTypesList[$taskTypeData['ID']] = $taskTypeData;
            }





            $this->arResult['TASK_TYPES'] = $taskTypesList;

            /**
             * поулчаем список пользователей
             */
            $arUsers = [];
            $arParams["FIELDS"] = Array("ID", "ACTIVE", "NAME", "LAST_NAME", "SECOND_NAME");
            $arParams["SELECT"] = Array("UF_DEPARTMENT");
            $filter = Array("ACTIVE"=>"Y", "!UF_DEPARTMENT"=>false);
            $rsUsers = CUser::GetList(($by="LAST_NAME"), ($order="asc"), $filter, $arParams);
            while($arUser = $rsUsers->GetNext())
            {
                $arUsers[] = $arUser;
            }
            $this->arResult['USERS'] = $arUsers;

            /**
             * Получаем список контактов компании
             */
            $arContacts = [];
            $dbRes = CCrmContact::GetContactByCompanyId($crmEntityId);
            while ($arContact = $dbRes->Fetch()) {
                $arContacts[] = $arContact;
            }
            $this->arResult['CONTACTS'] = $arContacts;

        }

        $this->includeComponentTemplate();
    }
}