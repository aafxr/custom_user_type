<?php

class RefloorHistoryGridComponent extends \CBitrixComponent
{
    const GRID_ID = 'REFLOOR_TASK_HISTORY_GRID';
    const PAGE_SIZE = 15;

    private $UF_TASK_GOAL_REACHED = 'UF_AUTO_251545709641';
    private $UF_TASK_REPORT = 'UF_AUTO_280393729397';

    private $userId = false;

    private $rows;
    private $typeList;

    private $templatePathTaskView;
    private $templatePathTaskEdit;

    private $users;

    private function getTasksClosed() {
        $arFilterTasksClosed = [
            "REAL_STATUS" => CTasks::STATE_COMPLETED,
            "UF_CRM_TASK" => "CO_".$this->arParams['COMPANY_ID']
        ];

        $resTaskClosed = CTasks::GetList(
            [
                "CLOSED_DATE" => "DESC",
                "ID" => "DESC"
            ],
            $arFilterTasksClosed,
            [
                "*","UF_*"
            ]
        );
        return $resTaskClosed;
    }
    private function getTasksUnClosed() {

        $arFilterTasksUnClosed = [
            "!REAL_STATUS" => CTasks::STATE_COMPLETED,
            "UF_CRM_TASK" => "CO_".$this->arParams['COMPANY_ID']
        ];
        $resTaskUnClosed = CTasks::GetList(
            [
                "DEADLINE" => "DESC",
                "ID" => "DESC"
            ],
            $arFilterTasksUnClosed,
            [
                "*","UF_*"
            ]
        );
        return $resTaskUnClosed;
    }


    public function onPrepareComponentParams($arParams)
    {
        // время кеширования
        if (!isset($arParams['USER_ID'])) {
            global $USER;
            $arParams['USER_ID'] = $USER->GetID();
        } else {
            $arParams['USER_ID'] = intval($arParams['USER_ID']);
        }
        // возвращаем в метод новый массив $arParams
        //$arParams['COMPANY_ID'] = $arParams['COMPANY_ID'];

        $hlid = 2; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlid)->fetch();
        $taskTypesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

        $taskTypesRes = $taskTypesClass::getList([]);
        $taskTypesList = [];
        while ($taskTypeData = $taskTypesRes->Fetch()) {
            $taskTypesList[$taskTypeData['ID']] = $taskTypeData;
        }
        $this->typeList = $taskTypesList;

        return $arParams;
    }

    private function getColumns() {
        $columns = [
            /*['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],*/
            ['id' => 'DATE', 'name' => 'Дата', 'sort' => 'DATE', 'default' => true],
            ['id' => 'ACTION', 'name' => 'Действие', 'sort' => 'ACTION', 'default' => true],
            /*['id' => 'USER', 'name' => 'Сотрудник', 'sort' => 'USER', 'default' => true],*/
            ['id' => 'DESCRIPTION', 'name' => 'Желаемый результат', 'sort' => 'DESCRIPTION', 'default' => true],
            ['id' => 'RESULT', 'name' => 'Отчет', 'sort' => 'RESULT', 'default' => true],
        ];
        return $columns;
    }

    private function getUserPic($id)
    {
        if (!$this->users[$id]) {
            $arUser = \Bitrix\Main\UserTable::getById($id)->fetch();
            $this->users[$id] = $arUser;
        }

        $userAvatar = '<div class="tasks-grid-username-pic" style="background-color: #'.substr(md5($id),0,6).'33" ></div>';
        if($this->users[$id]['PERSONAL_PHOTO']) {
            $photo = CFile::ResizeImageGet($this->users[$id]['PERSONAL_PHOTO'], array('width' => 24, 'height' => 24), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            $userAvatar = "<div class='tasks-grid-username-pic' style='background-image: url(".$photo['src'].")' ></div>";
        }

        //$userAvatar = '<div class="tasks-grid-username-pic" {$inlineStyles} ></div>';

        $result = '<span class="tasks-grid-username" >'.$userAvatar.$this->users[$id]['NAME']." ".$this->users[$id]['LAST_NAME'].'</span>';
        return $result;
    }

    private function addTaskToGrid($res) {

        global $USER;
        $currentUserId = $USER->GetID();

        while ($arTask = $res->GetNext()) {
            $taskCompleted = ($arTask['REAL_STATUS'] == CTasks::STATE_COMPLETED);
            $taskViewPath = CComponentEngine::MakePathFromTemplate(
                $this->templatePathTaskView,
                [
                    'user_id' => $currentUserId,
                    'task_id' => $arTask['ID']
                ]
            );
            $taskEditPath = CComponentEngine::MakePathFromTemplate(
                $this->templatePathTaskEdit,
                [
                    'user_id' => $currentUserId,
                    'task_id' => $arTask['ID']
                ]
            );

            $overdue = false;

            if($arTask["REAL_STATUS"] == CTasks::STATE_COMPLETED) {
                $dateTime = new \Bitrix\Main\Type\DateTime($arTask["CLOSED_DATE"]);
                $deadLineDateTime = new \Bitrix\Main\Type\DateTime($arTask["DEADLINE"]);
                $dateTimeText = $dateTime->format("d.m.Y");

                $phpDateTime =  new DateTime($dateTime->format("d.m.Y"));
                $phpDeadLineDateTime = new DateTime($deadLineDateTime->format("d.m.Y"));

                if( $phpDateTime > $phpDeadLineDateTime) {
                    $overdueInterval = $phpDeadLineDateTime->diff($phpDateTime);
                    $overdue = $overdueInterval->days;
                }

            } else {
                $dateTime = new \Bitrix\Main\Type\DateTime($arTask["DEADLINE"]);
                $dateTimeText = $dateTime->format("d.m.Y");

                $phpDateTime =  new DateTime($dateTime->format("d.m.Y"));
                $phpCurrentDateTime = new DateTime();

                if($phpCurrentDateTime > $phpDateTime) {
                    $overdueInterval = $phpCurrentDateTime->diff($phpDateTime);
                    $overdue = $overdueInterval->days;
                } else {
                    //
                }
            }
            if($overdue) {
                $dateTimeText .= "<div class='task-overdue'>Просрочено дней: ".$overdue."</div>";
            }

            //
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
            $actionText = '<div onclick="BX.SidePanel.Instance.open(\''.$taskViewPath.'\')" class="task-event-type">'.$priorityIcon.$this->typeList[$actionId]['UF_NAME']."</div>";


            /*$userId = $arTask['RESPONSIBLE_ID'];
            $userText = $userId;*/



            /**
             * Описание
             */
            $taskDescription = TxtToHTML($arTask['DESCRIPTION']!=""?$arTask['DESCRIPTION']:$arTask['TITLE']);
            $taskDescription = $this->getUserPic($arTask['CREATED_BY']).'<div class="task-description" onclick="BX.SidePanel.Instance.open(\''.$taskViewPath.'\')">'.$taskDescription.'</div>';
            $taskDescription = '<div class="task-col-block">'.$taskDescription.'</div>';
            /**
             * Результат
             */
            $objDateTime = new \Bitrix\Main\Type\DateTime($arTask['CLOSED_DATE'], "d.m.Y H:i:s");

            if($arTask['REAL_STATUS'] == 5) {
                $classSuccess = $arTask['UF_AUTO_251545709641'] ? 'reportsuccess' : '';
                $taskResult = '<div class="action-task-report '.$classSuccess.'" data-id="' . $arTask['ID'] . '"><span class="datetime action-task-report" data-id="' . $arTask['ID'] . '">' . $objDateTime->format("d.m H:i") . "</span> " . TxtToHTML($arTask['UF_AUTO_280393729397']?$arTask['UF_AUTO_280393729397']:'Нет отчета о результатах') . '</div>';

            } else {
                // $taskPath
                //$taskResult = '<a class="report" href="'.$taskPath.'" >Написать отчет #</a>';
                $taskResult = '<div class="action-task-report report" data-id="' . $arTask['ID'] . '" >Написать отчет</div>';
            }

            if($arTask['RESPONSIBLE_ID'] != $arTask['CREATED_BY']) {
                $taskResult = $this->getUserPic($arTask['RESPONSIBLE_ID']).$taskResult;
            }

            $actions = [];
            $actions[] = [
                'text'    => 'Редактировать',
                'onclick' => 'BX.SidePanel.Instance.open("'.$taskEditPath.'")'
            ];
            if(!$taskCompleted  && $arTask['CREATED_BY'] == $currentUserId) {
                // Если задача не закрыта - можно удалить
                $actions[] = [
                    'text' => 'Удалить',
                    'onclick' => 'deleteTask('.$arTask['ID'].');'
                ];
            }



            $row = [
                'id'   => 'unique_row_id_'.$arTask['ID'],
                'data' => [
                    /*'ID'            => "<a href='".$taskPath."'>".$arTask['ID']."</a>",*/
                    'DATE'          => $dateTimeText,
                    '~DATE'         => $dateTime,
                    'ACTION'        =>  $actionText,
                    '~ACTION'       =>  $actionId,
                    /*'USER'          =>  $userText,
                    '~USER'         =>  $userId,*/
                    'DESCRIPTION'   =>  $taskDescription,
                    '~DESCRIPTION'  => $arTask['DESCRIPTION'],
                    'RESULT'        => $taskResult
                ],
                'actions' => $actions,
            ];


            $this->rows[] = $row;
        }
    }

    public function executeComponent()
    {

        $grid_id = self::GRID_ID ;
        // $grid_options = new Grid\Options($grid_id);

        $this->templatePathTaskView = COption::GetOptionString("tasks", "paths_task_user_entry");
        $this->templatePathTaskEdit = COption::GetOptionString("tasks", "paths_task_user_edit");


        $resTaskClosed = $this->getTasksClosed();
        $resTaskUnClosed = $this->getTasksUnClosed();

        $this->rows = [];
        $this->addTaskToGrid($resTaskUnClosed);
        $this->addTaskToGrid($resTaskClosed);




        $this->arResult = [
            'grid_id' => $grid_id,
            'columns' => $this->getColumns(),
            'rows' => $this->rows
        ];

        $this->includeComponentTemplate();
    }
}