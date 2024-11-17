<?php
use Bitrix\Tasks\Control\Exception\TaskAddException;
use Bitrix\Tasks\Control\Exception\TaskNotFoundException;
use Bitrix\Tasks\Control\Exception\TaskUpdateException;
//AddEventHandler("tasks","OnTaskUpdate","RefloorOnAfterTaskUpdateSync");
AddEventHandler("tasks","OnTaskUpdate","RefloorOnAfterTaskReCalcValues");
AddEventHandler("tasks","OnTaskAdd","RefloorOnAfterTaskAdd");
AddEventHandler("tasks","OnBeforeTaskDelete","RefloorOnBeforeTaskDelete");
//AddEventHandler("tasks","OnBeforeTaskUpdate","RefloorOnBeforeTaskUpdate");


/**
 * Проверка перед удалением
 */
function RefloorOnBeforeTaskDelete($taskId, $arTask) {

    global $USER;

    $bxCurrentUserId = $USER->GetID();
    $userIsAdmin = $USER->IsAdmin();
    $taskCreatorId = $arTask['CREATED_BY'];

    if((!$userIsAdmin) && ($taskCreatorId != $bxCurrentUserId)) {
        $taskCreatorId = $arTask['CREATED_BY'];
        throw new \Bitrix\Tasks\ActionFailedException("Задачу может удалить только создатель или администратор");
        return false;
    }
}


/**
 *
 */
function TaskActionSync($taskId) {

    //echo "Sync:".$taskId."<br />";

    //echo "taskId=".$taskId."<br />";
    global $USER;
    $bxCurrentUserId = $USER->GetID();
    $obActivities = CCrmActivity ::GetList(
        [],
        [
            'OWNER_TYPE_ID' =>  CCrmOwnerType::ResolveID('COMPANY'),
            /*'OWNER_ID' => $companyId,*/
            /*'COMPLETED' => 'N',*/
            'CHECK_PERMISSIONS' => 'N',
            'PROVIDER_TYPE_ID' => 'TASKS_TASK',
            'ASSOCIATED_ENTITY_ID' => $taskId
        ],
        false,
        false,
        [

        ]
    );
    while ($arActivity = $obActivities->Fetch()) {

        $taskId = $arActivity['SETTINGS']['TASK_ID'];
        $task = new \Bitrix\Tasks\Item\Task($taskId, $bxCurrentUserId);
        /*echo '<pre>';
        print_r($task->getData());
        echo '</pre>';*/
        //echo $task["CREATED_DATE"];
        //echo $task->Title."<br />";
        $report = $task['UF_AUTO_280393729397'];

        $activityCompleted = ($task->Status == CTasks::STATE_COMPLETED);

        /*echo "completed:".($activityCompleted?"Y":"N")."<br />";
        echo "report:".$report."<br />";
        echo '<pre>';
        print_r($arActivity);
        echo '</pre>';*/

        // [ACTIVITY_STATUS] => FINISHED
        // [ACTIVITY_STATUS] => VIEWED

        $settings = $arActivity['SETTINGS'];
        $settings["ACTIVITY_STATUS"] = $activityCompleted ? "FINISHED" : "VIEWED";

        CCrmActivity::update($arActivity['ID'],
            [
                "AUTHOR_ID " => $task["CREATED_BY"],
                "START_TIME" => $task["CREATED_DATE"],
                "LAST_UPDATED" => $task["CREATED_DATE"],
                "CREATED" => $task["CREATED_DATE"],
                "COMPLETED" => $activityCompleted,
                "SETTINGS" => $settings
            ]
        );

        $note = \Bitrix\Crm\Timeline\Entity\NoteTable::query()
            ->addSelect('*')
            ->where('ITEM_ID', $arActivity['ID'])
            ->where('ITEM_TYPE', 2)
            ->fetchObject();
        if ($note) {
            $note->delete();
        }

        if($task->Status == CTasks::STATE_COMPLETED) {
            $note = \Bitrix\Crm\Timeline\Entity\NoteTable::createObject();
            $note->set('ITEM_ID', $arActivity['ID']);
            $note->set('ITEM_TYPE', 2);
            $note->set('CREATED_BY_ID', $task["RESPONSIBLE_ID"]);
            $note->set('CREATED_TIME', $task["CLOSED_DATE"]);
            $note->set('UPDATED_BY_ID', $task["RESPONSIBLE_ID"]);
            $note->set('UPDATED_TIME', $task["CLOSED_DATE"]);
            $note->set('TEXT', $report);
            $saveResult = $note->save();
            if ($saveResult->isSuccess())
            {
                //$this->sendPullEvent($ownerTypeId, $ownerId, $itemType, $itemId);
            } else {
                //echo "err:".$saveResult->getErrors();
            }
        }
    }

}

/**
 * @param $taskId
 * @param $arParams
 * @return void
 * Функция должна синхронизировать статус дела и статус задачи
 */
function RefloorOnAfterTaskUpdateSync($taskId, $arParams) {

}

function RefloorOnAfterTaskAdd($taskId, $arParams) {
    $log = date("d.m.Y H:i:s");
    $log = "OnTaskAdd:".$taskId.PHP_EOL;
    $log .= "<pre>".print_r($arParams,true)."</pre>";
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/dev/test.log",$log,FILE_APPEND);
    sendNewIncomingTaskNotify($taskId);
}



/**
 * Обновляем компанию привязанную к задаче (дату последней успешной активности)
 * @param $taskId
 * @param $arParams
 * @return void
 */
function RefloorOnAfterTaskReCalcValues($taskId, $arParams) {
    global $USER;
    $bxCurrentUserId = $USER->GetID();
    $task = new \Bitrix\Tasks\Item\Task($taskId, $bxCurrentUserId);

    $log = date("d.m.Y H:i:s");
    /*$log = $taskId.PHP_EOL;
    $log .= "<pre>".print_r($arParams,true)."</pre>";
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/dev/test.log",$log,FILE_APPEND); */


    $arTask = CTasks::GetList([], ['ID'=> $taskId], ['ID','STATUS','UF_CRM_TASK'])->fetch();
    if(($arTask['STATUS'] == CTasks::STATE_COMPLETED) && $arTask['UF_CRM_TASK']) {
        $arCrmEntity = explode("_",$arTask['UF_CRM_TASK'][0]);
        if($arCrmEntity[0] == "CO") {
            $crmEntityId = $arCrmEntity[1];
            CompanyLastActivityDateTimeHandler($crmEntityId);
        }
    }
    if(($arParams['STATUS'] == CTasks::STATE_COMPLETED) &&
        ($arParams['STATUS'] != $arParams['META:PREV_FIELDS']['STATUS'])) {
        /*$log = date("d.m.Y H:i:s");
        $log .= $taskId.">CLOSED>".PHP_EOL;
        file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/dev/test.log",$log,FILE_APPEND);*/
        sendCompletedNotify($taskId);
        /*
        $log = $taskId.">CLOSED AFT SEND>".PHP_EOL;
        file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/dev/test.log",$log,FILE_APPEND);*/

    }


    // $arFields['STATUS'], когда он равен CTasks::STATE_COMPLETED и не равен $arFields['META:PREV_FIELDS']['STATUS'],
    /*$log = date("d.m.Y H:i:s");
    $log .= "<pre>".print_r($arParams,true)."</pre>";
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/lotest/test.log",$log,FILE_APPEND);
    if(($arParams['STATUS'] == CTasks::STATE_COMPLETED) &&
       ($arParams['STATUS'] != $arParams['META:PREV_FIELDS']['STATUS'])) {
        sendCompletedNotify($taskId);
    }*/

}