<?php

class TaskGridComponent extends \CBitrixComponent
{
    const GRID_ID = 'REFLOOR_TASK_START_GRID';
    const PAGE_SIZE = 15;

    function buildGridList($resTaskList, $list = []) {

        $status = "COMPANY_TYPE";
        $listCompanyTypes = \CCrmStatus::GetStatusList( $status );

        $taskTypesList = $this->getTaskTypes();
        global $USER;
        $userId = $USER->GetID();

        $pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");
        $pathTaskEntryEdit = COption::GetOptionString("tasks", "paths_task_user_edit");

        while ($arTask = $resTaskList->GetNext()) {
            //echo ". ";

            $taskPath = CComponentEngine::MakePathFromTemplate(
                $pathTemplateTaskEntryView,
                [
                    'user_id' => $userId,
                    'task_id' => $arTask['ID']
                ]
            );
            $taskEditPath = CComponentEngine::MakePathFromTemplate(
                $pathTaskEntryEdit,
                [
                    'user_id' => $userId,
                    'task_id' => $arTask['ID']
                ]
            );

            $taskCompleted = ($arTask['REAL_STATUS'] == CTasks::STATE_COMPLETED);

            $deadlineDateTime = new \DateTime($arTask['DEADLINE']);
            $currentDateTime = new \DateTime();
            $taskDateTimeCaption = "<br />";

            if ($taskCompleted) {

                /**
                 * Если задача завершена - дата задачи ставим по дате закрытия задачи
                 */
                $closedDateTime = new \DateTime($arTask['CLOSED_DATE']);

                $taskDateTime = clone $closedDateTime;
                $taskDateTimeCaption .= "<span>Завершена</span>";

                if ($closedDateTime > $taskDateTime) {
                    // Завершенная задача тоже может быть просрочена
                    $taskDateTimeCaption .= "<span class='overdue'>Просрочена</span>";
                }

            } else {

                //echo "W ";
                $taskDateTime = $deadlineDateTime;

                if ($deadlineDateTime < $currentDateTime) {
                    $taskDateTimeCaption .= "<span class='overdue'>Просрочена</span>";
                }
            }


            $arCompanyId = explode("_", $arTask['UF_CRM_TASK'][0]);
            if ($arCompanyId[0] != "CO") {
                $companyId = false;
                $companyText = "Прочие дела!";
            } else {
                $companyId = $arCompanyId[1];
            }

            $priority = $arTask['UF_AUTO_851551329931'];
            $priorityIcon = "";
            switch ($priority) {
                case 'срочная, важная';
                    $priorityIcon = '<img src="/local/images/important_urgent.svg" />';
                    break;
                case 'важная, не срочная';
                    $priorityIcon = '<img src="/local/images/important.svg" />';
                    break;
                case 'срочная, не важная';
                    $priorityIcon = '<img src="/local/images/urgent.svg" />';
                    break;
            }

            $actionId = $arTask['UF_AUTO_274474131393'];
            $actionText = $actionId ? '<a href="' . $taskPath . '" class="eventType">' . $priorityIcon . ' ' . $taskTypesList[$actionId]['UF_CODE'] . ' ' . $taskTypesList[$actionId]['UF_NAME'] . '</a>' : '<span class="eventType">Прочие дела</span>';

            $companyId = $arCompanyId[1];
            $companyText = "";
            if ($companyId) {

                $entityTypeId = \CCrmOwnerType::Company;
                $entityUrl = \CCrmOwnerType::GetEntityShowPath($entityTypeId, $companyId);

                $arCompany = CCrmCompany::GetList([], ["ID" => $companyId], ['TITLE', 'COMPANY_TYPE', 'UF_*'])->Fetch();
                $companyText = '<a href="' . $entityUrl . '"><b>' . $arCompany['TITLE'] . '</b></a>';

                $companyTags = [];
                if ($arCompany['UF_CRM_1712158211014']) {
                    $companyTags[] = $arCompany['UF_CRM_1712158211014'];
                }
                if ($arCompany['COMPANY_TYPE']) {
                    $companyTags[] = $listCompanyTypes[$arCompany['COMPANY_TYPE']];
                }

                $companyText .= '<div class="grtxt">' . implode(" / ", $companyTags) . '</div>'; // city field
            }

            // UF_AUTO_280393729397 - result
            $objDateTime = new \Bitrix\Main\Type\DateTime($arTask['CLOSED_DATE'], "d.m.Y H:i:s");
            if ($arTask['UF_AUTO_280393729397']) {
                //$taskResult = '<div class="reportsuccess"><a href="'.$taskPath.'" class="datetime">' . $objDateTime->format("d.m H:i") . "</a> " . TxtToHTML($arTask['UF_AUTO_280393729397']) . '</div>';
                    $classSuccess = $arTask['UF_AUTO_251545709641'] ? 'reportsuccess' : '';
                    $taskResult = '<div class="action-task-report '.$classSuccess.'" data-id="' . $arTask['ID'] . '"><span class="datetime">' . $objDateTime->format("d.m H:i") . "</span> " . TxtToHTML($arTask['UF_AUTO_280393729397']) . '</div>';

            } else {
                // $taskPath

                //$taskResult = '<a class="report" href="'.$taskPath.'" >Написать отчет #</a>';
                $taskResult = '<div class="action-task-report report" data-id="' . $arTask['ID'] . '" >Написать отчет</div>';
            }


            $taskDescription = ($arTask['DESCRIPTION'] != "" ? $arTask['DESCRIPTION'] : $arTask['TITLE']);
            $taskDescription = "<a class='text' href='" . $taskPath . "'>" . TxtToHTML($taskDescription) . "</a>";
            if ($arTask['CREATED_BY'] != $arTask['RESPONSIBLE_ID']) {
                $arCreator = \Bitrix\Main\UserTable::getById($arTask['CREATED_BY'])->fetch();

                $taskDescription = "<b>Поручение от сотрудника " . $arCreator['NAME'] . " " . $arCreator['LAST_NAME'] . ":&nbsp;</b>" . $taskDescription;
            }
            $list[] = [
                'id' => 'task_' . $arTask['ID'],
                'data' => [
                    'ID' => "<a href='" . $taskPath . "'>" . $arTask['ID'] . "</a>",
                    'DATE' => "<a class='text' href='" . $taskPath . "'>" . $taskDateTime->format("d.m.Y") . $taskDateTimeCaption . "</a>",
                    '~DATE' => $taskDateTime,
                    'ACTION' => $actionText,
                    '~ACTION' => $actionId,
                    'DESCRIPTION' => $taskDescription,
                    '~DESCRIPTION' => $arTask['DESCRIPTION'],
                    'COMPANY' => $companyText,
                    'RESULT' => $taskResult
                ],
                'actions' => [
                    [
                        'text' => 'Редактировать',
                        'onclick' => 'BX.SidePanel.Instance.open("'.$taskEditPath.'")'
                    ],
                    [
                        'text' => 'Удалить',
                        'onclick' => 'document.location.href="/accountant/reports/1/delete/"'
                    ]
                ],
            ];

        }
        return $list;
    }

    function getTaskTypes() {
        // props
        $hlElID = 11; //
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlElID)->fetch();
        $propClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $hlElID = 12; //
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlElID)->fetch();
        $propValuesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();


        $hlid = 2; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlid)->fetch();
        $taskTypesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $taskTypesRes = $taskTypesClass::getList([]);
        $taskTypesList = [];
        while ($taskTypeData = $taskTypesRes->Fetch()) {
            $taskTypesList[$taskTypeData['ID']] = $taskTypeData;
        }
        return $taskTypesList;
    }

    function getColumns() {
        $columns = [
            ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
            ['id' => 'DATE', 'name' => 'Дата', 'sort' => 'DATE', 'default' => true],
            ['id' => 'ACTION', 'name' => 'Дествие', 'sort' => 'AMOUNT', 'default' => true],
            ['id' => 'COMPANY', 'name' => 'Название фирмы', 'sort' => 'PAYER_INN', 'default' => true],
            ['id' => 'DESCRIPTION', 'name' => 'Описание', 'sort' => 'PAYER_NAME', 'default' => true],
            ['id' => 'RESULT', 'name' => 'Отчет', 'sort' => 'IS_SPEND', 'default' => true],
        ];
        return $columns;
    }

    function getDayList() {
        $currentDateTime = new \DateTime(date("Y-m-d"));
        $currentDateTimeNextDay = clone $currentDateTime;
        $currentDateTimeNextDay->setTime(0,0);
        $currentDateTimeNextDay->modify("+1 day");

        $filterDateTime = new \DateTime($this->date);
        $filterDateTimeNextDay = clone $filterDateTime;
        $filterDateTimeNextDay->modify("+1 day");

        // Первый день месяца
        $periodStart = clone $currentDateTime;
        $periodStart->modify('-1 month');
        $periodStart->modify('first day of this month');

        // Последний день месяца
        $periodEnd = clone $periodStart;
        $periodEnd->modify('+2 month');
        $periodEnd->modify('last day of this month');

        $periodType = 2;
        if($filterDateTime < $currentDateTime) { $periodType = 1; }
        if($filterDateTime > $currentDateTime) { $periodType = 3; }

        /* echo 'filterDT='.$this->date."<br />";
        echo 'periodType='.$periodType; */

        $arListFilterCompleted = [
            [
                "LOGIC" => "AND",
                ">=CLOSED_DATE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTime),
                "<CLOSED_DATE" =>  \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTimeNextDay),
            ],
            "REAL_STATUS" => CTasks::STATE_COMPLETED,
            "RESPONSIBLE_ID" => $this->userId,
        ];
        $resTaskList = CTasks::GetList(
            [
                "DEADLINE" => "DESC",
                "ID" => "DESC"
            ],
            $arListFilterCompleted,
            [
                "*","UF_*"
            ]
        );
        $row = 0;
        $list = $this->buildGridList($resTaskList,[]);

        if($periodType > 1 ) {
            $arListFilterPlanned = [
                [
                    "LOGIC" => "AND",
                    ">=DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTime),
                    "<DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTimeNextDay),
                ],
                "!REAL_STATUS" => CTasks::STATE_COMPLETED,
                "RESPONSIBLE_ID" => $this->userId,
                /* "!UF_CRM_TASK" => false */ // Убираем так как нам надо будет фиксировать и обычные задачи
            ];
            $resTaskList = CTasks::GetList(
                [
                    "UF_AUTO_851551329931" => 'DESC',
                    "DEADLINE" => "DESC",
                    "ID" => "DESC"
                ],
                $arListFilterPlanned,
                [
                    "*", "UF_*"
                ]
            );
            $list = $this->buildGridList($resTaskList, $list);

        }

        if($periodType == 2) {
            $arListFilter = [
                [
                    "LOGIC" => "AND",
                    "<DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($currentDateTime),

                ],
                "!REAL_STATUS" => CTasks::STATE_COMPLETED,
                "RESPONSIBLE_ID" => $this->userId,
                "!UF_CRM_TASK" => false
            ];

            $resTaskList = CTasks::GetList(
                [
                    "DEADLINE" => "DESC",
                    "ID" => "DESC"
                ],
                $arListFilter,
                [
                    "*","UF_*"
                ]
            );
            $list = $this->buildGridList($resTaskList,$list);
        }


        return $list;

    }

    public function executeComponent()
    {
        global $USER;
        $this->userId = $this->arParams['USER_ID'] ? $this->arParams['USER_ID'] : $USER->GetID();
        $this->date   = $this->arParams['DATE'] ? $this->arParams['DATE'] : date("d.m.Y");

        $grid_id = self::GRID_ID;
        $grid_options = new CGridOptions($grid_id);
        $this->arResult['GRID_ID'] = $grid_id;
        $this->arResult['OPTIONS'] = $grid_options;


        $this->arResult['COLUMNS'] = $this->getColumns();
        $this->arResult['COMPONENT_PATH'] = $this->GetPath();

        if(true) {
            $this->arResult['TASK_LIST'] = $this->getDayList();
        }


        $this->arResult['DATE_TIME'] = date("H:i:s");
        $this->includeComponentTemplate();
    }


}