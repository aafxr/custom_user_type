<?php
use Bitrix\Main\UserTable;
use Bitrix\Im\Chat;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Main\Mail\Event;
const BLOCK_PERIOD_FROM_CREATE = 1;
const BLOCK_PERIOD_FROM_LAST_ACTIVITY = 2;

/**
 * @param $userId
 * @param $deptId
 * @return bool
 * Состооить ли пользователь в отделе/подотделах
 */
function userInDept($userId, $deptId){

    $UserDepartments = CIntranetUtils::GetUserDepartments($userId);

    if($UserDepartments) {

        if (in_array($deptId, $UserDepartments)) {
            return true;
        } else {
            $sub = CIntranetUtils::getSubDepartments($deptId);
            foreach ($sub as $ss) {
                if (userInDept($userId, $ss)) {
                    return true;
                }
            }
        }
    }
    return false;

}

/**
 * @param $userId
 * @return false|mixed
 * Получаем ID чата уведомлений пользователя
 */
function getNotificationChat($userId) {
    $chatDB = ChatTable::getList([
        'filter' => ['AUTHOR_ID' => $userId, 'TYPE'=>'S' ],
        'limit' => 1,
        'order' => ['ID' => 'DESC'],
    ]);
    if ($chat = $chatDB->fetch()){
        $chatId = $chat['ID'];
        return $chatId;
    }
    return false;
}
function sendNewIncomingTaskNotify($taskId) {

    $taskId = (int)$taskId;
    $taskEntity = new Bitrix\Tasks\Internals\TaskTable();
    $taskObj = $taskEntity::getById($taskId)->fetch();
    $arTask = CTasks::GetList([],['ID'=>$taskId],['*','UF_*'])->fetch();

    $responsibleId = $taskObj['RESPONSIBLE_ID'];
    $createdBy = $taskObj['CREATED_BY'];
    $closedBy =  $taskObj['CLOSED_BY'];

    if($createdBy != $responsibleId) {

        $rsUserTaskFrom = CUser::GetByID($createdBy);
        $arUserTaskFrom = $rsUserTaskFrom->Fetch();
        $rsUserTaskTo = CUser::GetByID($responsibleId);
        $arUserTaskTo = $rsUserTaskTo->Fetch();

        $userTaskToChat = getNotificationChat($responsibleId);

        /**
         *
         */
        $pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");
        $taskPath = CComponentEngine::MakePathFromTemplate(
            $pathTemplateTaskEntryView,
            [
                'user_id' => $createdBy,
                'task_id' => $taskId
            ]
        );

        $arCompanyId = explode("_", $arTask['UF_CRM_TASK'][0]);
        if ($arCompanyId[0] != "CO") {
            $companyId = false;
            $companyText = "Прочие дела!";
        } else {
            $companyId = $arCompanyId[1];
            $arCompany = CCrmCompany::GetList([], ["ID" => $companyId],['ID','TITLE','ORIGIN_ID','UF_*'])->fetch();
            $companyText = $arCompany['TITLE'];

        }
        $rsClosedUser = CUser::GetByID($closedBy );
        $arClosedUser = $rsClosedUser->Fetch();

        $desc = mb_strimwidth($arTask['DESCRIPTION'], 0, 160, "...");
        $message = 'Новое поручение [URL=https://crm.refloor-nsk.ru'.$taskPath.'][#'.$taskId.'] '.$taskObj['TITLE'].' ['.$companyText.'] '.$desc.'[/URL]';

        $mailMessage = "Новое поручение от сотрудника ". $arUserTaskFrom ['NAME']." ".$arUserTaskFrom ['LAST_NAME']."<br />";
        $mailMessage .= "<a href='https://crm.refloor-nsk.ru".$taskPath."'>[#".$taskId."]".$taskObj['TITLE']."</a><br />";
        if($companyId) {
            $mailMessage .= "по клиенту <a href='https://crm.refloor-nsk.ru/crm/company/details/".$companyId."/'>".$companyText."</a><br />";
        }
        $mailMessage .= "<br />Срок: ".$arTask['DEADLINE']."<br />";
        $mailMessage .= "<br />Описание: ".$arTask['DESCRIPTION']."<br />";

        \Bitrix\Im\Model\MessageTable::add([
            'CHAT_ID' => $userTaskToChat,
            'AUTHOR_ID' => $createdBy,
            'MESSAGE' => $message,
            'NOTIFY_TYPE' => 2,
            'NOTIFY_MODULE' => 'tasks',
            'NOTIFY_READ' => 'N'
        ]);

        Event::send(array(
            "EVENT_NAME" => "REFLOOR_CUSTOM",
            "LID" => "s1",
            "C_FIELDS" => array(
                "EMAIL_TO" => $arUserTaskTo['EMAIL'],
                "THEME" => 'Новое поручение',
                "TEXT" => $mailMessage
            ),
        ));

    }
}
function sendCompletedNotify($taskId) {

    $taskId = (int)$taskId;
    $taskEntity = new Bitrix\Tasks\Internals\TaskTable();
    $taskObj = $taskEntity::getById($taskId)->fetch();
    $arTask = CTasks::GetList([],['ID'=>$taskId],['*','UF_*'])->fetch();

    $responsibleId = $taskObj['RESPONSIBLE_ID'];
    $createdBy = $taskObj['CREATED_BY'];
    $closedBy =  $taskObj['CLOSED_BY'];

    if($createdBy != $responsibleId) {

        $rsUserTaskFrom = CUser::GetByID($createdBy);
        $arUserTaskFrom = $rsUserTaskFrom->Fetch();
        $rsUserTaskTo = CUser::GetByID($responsibleId);
        $rsUserTaskTo = $rsUserTaskTo->Fetch();

        $userTaskFromChat = getNotificationChat($createdBy);

        /**
         *
         */
        $pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");
        $taskPath = CComponentEngine::MakePathFromTemplate(
            $pathTemplateTaskEntryView,
            [
                'user_id' => $createdBy,
                'task_id' => $taskId
            ]
        );

        $arCompanyId = explode("_", $arTask['UF_CRM_TASK'][0]);
        if ($arCompanyId[0] != "CO") {
            $companyId = false;
            $companyText = "Прочие дела!";
        } else {
            $companyId = $arCompanyId[1];
            $arCompany = CCrmCompany::GetList([], ["ID" => $companyId],['ID','TITLE','ORIGIN_ID','UF_*'])->fetch();
            $companyText = $arCompany['TITLE'];

        }
        $rsClosedUser = CUser::GetByID($closedBy );
        $arClosedUser = $rsClosedUser->Fetch();

        $desc = mb_strimwidth($arTask['DESCRIPTION'], 0, 24, "...");
        $short_res = mb_strimwidth($arTask['UF_AUTO_280393729397'], 0, 100, "...");
        $message = 'Поручение [URL=https://crm.refloor-nsk.ru'.$taskPath.'][#'.$taskId.'] '.$taskObj['TITLE'].' ['.$companyText.'] '.$desc.'[/URL]';
        $message .= 'завершено,'.PHP_EOL.'Результат: '.$short_res;

        $mailMessage = "Сотрудник ". $arClosedUser['NAME']." ".$arClosedUser['LAST_NAME']." выполнил поручение<br /><a href='https://crm.refloor-nsk.ru".$taskPath."'>[#".$taskId."]".$taskObj['TITLE']."</a><br />";

        if($companyId) {
            $mailMessage .= "по клиенту <a href='https://crm.refloor-nsk.ru/crm/company/details/".$companyId."/'>".$companyText."</a><br />";
        }
        $mailMessage .= "<br />Описание: ".$arTask['DESCRIPTION']."<br />";
        $mailMessage .= "Результат: ".$arTask['UF_AUTO_280393729397']."<br />";

        /*echo "from=".$createdBy."<br />";
        echo "chat=".$userTaskFromChat."<br />";
        echo 'closed='.$closedBy."<br />";
        echo "#=".$message."<br />";*/
        \Bitrix\Im\Model\MessageTable::add([
            'CHAT_ID' => $userTaskFromChat,
            'AUTHOR_ID' => $closedBy,
            'MESSAGE' => $message,
            'NOTIFY_TYPE' => 2,
            'NOTIFY_MODULE' => 'tasks',
            'NOTIFY_READ' => 'N'
        ]);

        Event::send(array(
            "EVENT_NAME" => "REFLOOR_CUSTOM",
            "LID" => "s1",
            "C_FIELDS" => array(
                "EMAIL_TO" => $arUserTaskFrom['EMAIL'],
                "THEME" => 'Выполнено поручение',
                "TEXT" => $mailMessage
            ),
        ));

    }
}

function getSubordinateList($idArrayOnly = false) {

    CModule::IncludeModule("timeman");
    global $USER;
    $userId = $USER->GetID();

    if ($USER->IsAdmin()) {
        $userFilter = ['ACTIVE' => 'Y', '!UF_DEPARTMENT' => false];
    } else {

        // подразделения пользователя
        $arDepartmens = CIntranetUtils::GetUserDepartments($userId);
        $arAvailableDepartments = [];
        // подчиненные подразделения пользователя
        foreach ($arDepartmens as $departmentId) {
            $res = CIBlockSection::GetByID($departmentId);
            if ($departmentInfo = $res->GetNext()) {

                $departmentName = $departmentInfo['NAME'];
                $departmentManager = CIntranetUtils::GetDepartmentManagerID($departmentId);

                $subordinatesAvailable = ($departmentManager == $userId);

                /**
                 * @issue: Штефан Михаил: Саша, привет. Нужно отделу обслуживания:
                 * Таргаевой, Харламовой и Симоненко открыть доступ к просмотру задач всех сотрудников отдела.
                 * @date 14.10.2024
                 * @comment Симоненко - итак руководитель, в условие не добавляем
                 */
                if($departmentId == 18) {
                    if(in_array($userId,[248,93])) {
                        $subordinatesAvailable = true;
                    }
                }

                // Доступны ли подчиненные
                if ($subordinatesAvailable) {
                    $arAvailableDepartments[] = $departmentId;

                    $obImport = new Bitrix\Timeman\Monitor\Utils\Department;
                    $arSubDepartmentList = $obImport->getSubordinateDepartments($departmentId);
                    $arAvailableDepartments = array_merge($arAvailableDepartments, $arSubDepartmentList);

                    $userFilter = ['ACTIVE' => 'Y', 'UF_DEPARTMENT' => $arAvailableDepartments];
                } else {
                    // Иначе только себя
                    $userFilter = ['ACTIVE' => 'Y', 'ID' => $userId];
                }
            }
        }
    }

    $select = ['ID', 'NAME', 'LAST_NAME','UF_DEPARTMENT'];
    $res = UserTable::getList(['order' => ['LAST_NAME'=>'ASC'], 'select' => $select, 'filter' => $userFilter]);
    $arSubUsers = $res->fetchAll();

    if($idArrayOnly) {
        $result = [];
        foreach ($arSubUsers as $subUser) {
            $result[] = $subUser['ID'];
        }
    } else {
        $result = $arSubUsers;
    }

    return $result;
}


/**
 * Получаем дату последней успешной активности
 */
function CompanyLastActivityDateTimeHandler($companyId) {

    $arCompany = CCrmCompany::GetList([],["ID" => $companyId],['DATE_CREATE','CLOSED_DATE','UF_EXT_DATE_CREATE'])->fetch();

    $dateBase = ($arCompany['UF_EXT_DATE_CREATE']) ? $arCompany['UF_EXT_DATE_CREATE'] : $arCompany['DATE_CREATE'];

    $arUpdateFields = [
        'UF_LAST_ACTIVITY' => new \Bitrix\Main\Type\DateTime($dateBase),
        'UF_BLOCK_TYPE' => BLOCK_PERIOD_FROM_CREATE
    ];

    $arTask = CTasks::GetList(
        [   'CLOSED_DATE'=>'DESC'],
        [
            'STATUS'=>CTasks::STATE_COMPLETED,
            'UF_CRM_TASK' => 'CO_'.$companyId
        ])->fetch();

    if($arTask) {
        $result['task'] = $arTask;
        $arUpdateFields['UF_LAST_ACTIVITY'] = new \Bitrix\Main\Type\DateTime($arTask['CLOSED_DATE']);
        $arUpdateFields['UF_BLOCK_TYPE'] = BLOCK_PERIOD_FROM_LAST_ACTIVITY;
    }

    if(count($arUpdateFields) > 0) {
        $crmCompany = new CCrmCompany;
        $crmCompany->Update($companyId, $arUpdateFields);
    }

}

/**
 * Сихнронизация поля город список с HL
 * @param $cityName
 * @return void
 */
function getUfCityListValueId($cityName) {

    $result = false;

    $userFieldId = 1708;

    $obEnum = new CUserFieldEnum();
    $rsEnum = $obEnum->GetList(
        [], // select
        [
            "USER_FIELD_ID" => $userFieldId,
            "VALUE" => $cityName
        ]
    );

    $enum = array();
    if($arEnum = $rsEnum->Fetch())
    {
        $result = $arEnum["ID"];
    } else {

        $res = \Bitrix\Sale\Location\LocationTable::getList(array(
            'filter' => array('TYPE_CODE' => 'CITY','NAME_RU'=>$cityName),
            'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
        ));
        if($item = $res->fetch())
        {

            $obEnum = new CUserFieldEnum;
            $obEnum->SetEnumValues($userFieldId, [
                "n0" => [
                    "XML_ID" => $item['ID'],
                    "VALUE" => $item['NAME_RU']
                ],
            ]);
            $rsEnum = $obEnum->GetList(
                [], // select
                [
                    "USER_FIELD_ID" => $userFieldId,
                    "VALUE" => $cityName
                ]
            );
            if($arEnum = $rsEnum->Fetch()) {
                $result = $arEnum["ID"];
            }
        } else {
            $xmlId = \Cutil::translit((string)$cityName,"ru",["replace_space"=>"-","replace_other"=>"-"]);
            $xmlId = "mawi-".$xmlId;

            $obEnum = new CUserFieldEnum;
            $obEnum->SetEnumValues($userFieldId, [
                "n0" => [
                    "XML_ID" => $xmlId,
                    "VALUE" => $cityName
                ],
            ]);
            $rsEnum = $obEnum->GetList(
                [], // select
                [
                    "USER_FIELD_ID" => $userFieldId,
                    "VALUE" => $cityName
                ]
            );
            if($arEnum = $rsEnum->Fetch()) {
                $result = $arEnum["ID"];
            }
        }


    }
    return $result;

    //echo "cityName=".$cityName." result=".$result."<br />";
}
/**
 * Сихнронизация произвольного пользовательнского поля список с текстом
 * @param $cityName
 * @return void
 */
function getUFListValueId($fieldValue,$userFieldId) {

    $result = false;

    //$userFieldId = 1708;

    $obEnum = new CUserFieldEnum();
    $rsEnum = $obEnum->GetList(
        [], // select
        [
            "USER_FIELD_ID" => $userFieldId,
            "VALUE" => $fieldValue
        ]
    );

    $enum = array();
    if($arEnum = $rsEnum->Fetch())
    {
        $result = $arEnum["ID"];
    } else {

            $xmlId = \Cutil::translit((string)$fieldValue,"ru",["replace_space"=>"-","replace_other"=>"-"]);
            $xmlId = "xml-".$xmlId;

            $obEnum = new CUserFieldEnum;
            $obEnum->SetEnumValues($userFieldId, [
                "n0" => [
                    "XML_ID" => $xmlId,
                    "VALUE" => $fieldValue
                ],
            ]);
            $rsEnum = $obEnum->GetList(
                [], // select
                [
                    "USER_FIELD_ID" => $userFieldId,
                    "VALUE" => $fieldValue
                ]
            );
            if($arEnum = $rsEnum->Fetch()) {
                $result = $arEnum["ID"];
            }



    }
    return $result;

    //echo "cityName=".$cityName." result=".$result."<br />";
}
