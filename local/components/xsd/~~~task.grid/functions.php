<?php

function buildGridList($resTaskList, $list = [],$taskTypesList) {
    global $USER;
    $userId = $USER->GetID();


    $pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");

    $pathTaskEntryEdit = COption::GetOptionString("tasks", "paths_task_user_edit");


    while ($arTask = $resTaskList->GetNext()) {

        /*echo '<pre>';
        print_r($arTask);
        echo '</pre>';*/
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

        //$deadline = preg_replace('~(.+)\s? .*~', '$1', $arTask['DEADLINE']);

        $deadlineDateTime = new \DateTime($arTask['DEADLINE']);
        $currentDateTime = new \DateTime();
        $taskDateTimeCaption = "<br />";

        if($taskCompleted) {
            /**
             * Если задача завершена - дата задачи ставим по дате закрытия задачи
             */
            $closedDateTime = new \DateTime($arTask['CLOSED_DATE']);

            $taskDateTime = clone $closedDateTime;
            $taskDateTimeCaption .= "<span>Завершена</span>";

            if($closedDateTime > $taskDateTime) {
                // Завершенная задача тоже может быть просрочена
                $taskDateTimeCaption .= "<span class='overdue'>Просрочена</span>";
            }

        } else {

            $taskDateTime = $deadlineDateTime;

            if($deadlineDateTime < $currentDateTime) {
                $taskDateTimeCaption .= "<span class='overdue'>Просрочена</span>";
            }
        }



        $arCompanyId = explode("_",$arTask['UF_CRM_TASK'][0]);
        if($arCompanyId[0] != "CO") {

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
        $actionText = $actionId ? '<a href="'.$taskPath.'" class="eventType">'.$priorityIcon.' '.$taskTypesList[$actionId]['UF_CODE'].' '.$taskTypesList[$actionId]['UF_NAME'].'</a>' : '<span class="eventType">Прочие дела</span>';

        $companyId = $arCompanyId[1];
        $companyText = "";
        if($companyId) {

            $entityTypeId = \CCrmOwnerType::Company;
            $entityUrl = \CCrmOwnerType::GetEntityShowPath( $entityTypeId, $companyId );

            $arCompany = CCrmCompany::GetList([], ["ID" => $companyId],['TITLE','COMPANY_TYPE','UF_*'])->Fetch();
            $companyText =  '<a href="'.$entityUrl.'"><b>'.$arCompany['TITLE'].'</b></a>';

            $companyTags = [];
            if($arCompany['UF_CRM_1712158211014']) { $companyTags[] = $arCompany['UF_CRM_1712158211014'];}
            if($arCompany['COMPANY_TYPE']) { $companyTags[] = $listCompanyTypes[$arCompany['COMPANY_TYPE']];}

            $companyText .= '<div class="grtxt">'.implode(" / ",$companyTags).'</div>'; // city field

            //$companyText .= '<pre>'.print_r($arCompany,true).'</pre>';

            /* props
            $companyCategories = $arCompany['UF_COMPANY_CATEGORIES'];
            $companyProperties = $arCompany['UF_PROPERTY_VALUES'];



            $companyProps = [];
            $resPropValues =$propValuesClass::getList(['filter' => ['UF_COMPANY'=> $companyId]]);
            while ($arPropValue = $resPropValues->Fetch()) {
                $companyProps[] = $arPropValue["UF_TITLE"];
            }

            $companyText .= '<div class="companyprops">'.implode("/",$companyProps).'</div>';
            */

        }
        // STAGE_ID = стадия // STATUS_COMPLETE // REAL_STATUS
        // UF_CRM_TASK_CONTACT
        // UF_NEXT_TASK
        // UF_TASK_REPORT



        // UF_AUTO_280393729397 - result
        $objDateTime = new \Bitrix\Main\Type\DateTime( $arTask['CLOSED_DATE'], "d.m.Y H:i:s");
        if($arTask['UF_AUTO_280393729397']) {
            //$taskResult = '<div class="reportsuccess"><a href="'.$taskPath.'" class="datetime">' . $objDateTime->format("d.m H:i") . "</a> " . TxtToHTML($arTask['UF_AUTO_280393729397']) . '</div>';
            $taskResult = '<div class="action-task-report" data-id="'.$arTask['ID'].'"><span class="datetime">' . $objDateTime->format("d.m H:i") . "</span> " . TxtToHTML($arTask['UF_AUTO_280393729397']) . '</div>';
        } else {
            // $taskPath

            //$taskResult = '<a class="report" href="'.$taskPath.'" >Написать отчет #</a>';
            $taskResult = '<div class="action-task-report" data-id="'.$arTask['ID'].'" >Написать отчет #</div>';
        }
        /*$taskPriority = "";
        if($arTask['UF_AUTO_851551329931']) {
            $arTaskPriority = explode(",",$arTask['UF_AUTO_851551329931']);
            foreach ($arTaskPriority as $priority) {
                $taskPriority .= "<div class='priority'>".$priority."</div>";
            }
        } */



        //$taskPriority = "<div class='priority-list'>".$taskPriority."</div>";;

        $taskDescription = ($arTask['DESCRIPTION']!=""?$arTask['DESCRIPTION']:$arTask['TITLE']);
        $taskDescription = "<a class='text' href='".$taskPath."'>".TxtToHTML($taskDescription)."</a>";
        if($arTask['CREATED_BY'] != $arTask['RESPONSIBLE_ID']) {
            $arCreator = \Bitrix\Main\UserTable::getById($arTask['CREATED_BY'])->fetch();

            $taskDescription = "<b>Поручение от сотрудника ".$arCreator['NAME']." ".$arCreator['LAST_NAME'].":&nbsp;</b>".$taskDescription;
        }
        $list[] = [
            'id'   => 'task_'.$arTask['ID'],
            'data' => [
                'ID' => "<a href='".$taskPath."'>".$arTask['ID']."</a>",
                'DATE'        => "<a class='text' href='".$taskPath."'>".$taskDateTime->format("d.m.Y").$taskDateTimeCaption."</a>",
                '~DATE'       => $taskDateTime,
                'ACTION'      =>  $actionText,
                '~ACTION'     =>  $actionId,
                'DESCRIPTION' =>  $taskDescription,
                '~DESCRIPTION' => $arTask['DESCRIPTION'],
                'COMPANY' =>  $companyText,
                'RESULT' => $taskResult
            ],
            'actions' => [
                [
                    'text'    => 'Редактировать',
                    'onclick' => 'document.location.href="'.$taskEditPath.'"'
                ],
                [
                    'text'    => 'Удалить',
                    'onclick' => 'document.location.href="/accountant/reports/1/delete/"'
                ]
            ],
        ];
    }

    return $list;
}