<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

define("UF_FIELD_RESULT", "UF_AUTO_280393729397");
define("UF_FIELD_SUCCESS", "UF_AUTO_251545709641");

global $USER;
$userId = $USER->GetID();

$entityBody = file_get_contents('php://input');
$request = json_decode($entityBody, true);

$taskId = (int)$request['taskId'];
$taskNextTypeId = (int)$request['taskNextTypeId'];

$arTask = CTasks::GetList([],['ID'=>$taskId],['*','UF_*'])->fetch();
$isCompleted = ($arTask['STATUS'] == CTasks::STATE_COMPLETED);

$companyId = preg_replace("/[^0-9]/", '',$arTask['UF_CRM_TASK'][0]);
$arCompany = CCrmCompany::GetList([], ["ID" => $companyId])->fetch();

$arUpdateFields = [];
$updateResult = false;
$errors = [];
$lastErrorText = false;

foreach ($request['fields'] as $key => $value) {
    $arUpdateFields[$key] = $value;
}

$log = date("d.m.Y H:i:s").PHP_EOL;
$log .= "Request:<pre>".print_r($request, true)."</pre>";
file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/log/close/taskClose_".$taskId.".log", $log,FILE_APPEND);

$DB->StartTransaction();

if(count($arUpdateFields) > 0) {
    $arUpdateFields['TASK_CONTROL'] = 'N';
    $updateResult = true;

    $obTask = new CTasks;
    $updateResult = $obTask->Update($taskId, $arUpdateFields);

    if(!$updateResult)
    {
        if($e = $APPLICATION->GetException()) {
            $errors[] = $e->GetString();
        }
    }

    $oTaskItem = new CTaskItem($taskId, $userId);
    $oTaskItem->complete();
    //sendCompletedNotify($taskId);

}

if($taskNextTypeId > 1) {

    $nextTaskTypeImportant = $request['nextTask']['important'];
    $nextTaskTypeUrgent = $request['nextTask']['urgent'];

    /**
     * Legacy code
     * */
    $nextTaskPriority = "";
    if($nextTaskTypeImportant) {
        $nextTaskPriority = "важная, не срочная";
    }
    if($nextTaskTypeUrgent) {
        $nextTaskPriority = "срочная, не важная";
    }
    if($nextTaskTypeImportant && $nextTaskTypeUrgent) {
        $nextTaskPriority = "срочная, важная";
    }

    $arNewTask = [
        "TITLE" => "CRM: ".$arCompany['TITLE'],
        "DESCRIPTION" => $request['nextTask']['description'],
        'UF_AUTO_274474131393' => $taskNextTypeId, // Тип следующей задачи
        "DEADLINE" => new \Bitrix\Main\Type\DateTime( $request['nextTask']['deadLine']." 23:59", "Y-m-d H:i"),
        "ALLOW_CHANGE_DEADLINE" => 'N',
        "TASK_CONTROL" => false,
        "DEPENDS_ON" => $taskId,
        "UF_CRM_TASK" => $arTask['UF_CRM_TASK'],
        "RESPONSIBLE_ID" => (int)$request['nextTask']['user'], // Идентификатор исполнителя (ответственного).
        "UF_AUTO_851551329931" => $nextTaskPriority
    ];

    $log = date("d.m.Y H:i:s").PHP_EOL;
    $log .= "Request:<pre>".print_r($arNewTask, true)."</pre>";
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/local/log/close/taskClose_".$taskId.".log", $log,FILE_APPEND);


    if((int)$request["nextTask']['contact"] > 0) {
        $arNewTask['UF_CRM_TASK_CONTACT'] = (int)$request["nextTask']['contact"];
    }

    $rsAdd = CTaskItem::add($arNewTask, $userId);

    if ($ID = $rsAdd->getId()) {
        $nextTaskId = $ID;
        $obTask->Update($taskId, [
            'UF_NEXT_TASK' => $ID
        ]);
    } else {
        $nextTaskId = false;
    }


}

if ($request['taskClosePrevDate']) {
    $taskEntity = new Bitrix\Tasks\Internals\TaskTable();
    $closedDate = new \DateTime(date("Y-m-d"));
    $closedDate->modify('-1 day');
    $bxDateTime = new \Bitrix\Main\Type\DateTime($closedDate->format("d.m.Y"));

    $rs = $taskEntity::update($taskId,
        [
            'STATUS' => CTasks::STATE_COMPLETED,
            'CLOSED_DATE' => $bxDateTime,
        ]
    );
    $result['calc']['closed_date'] = $bxDateTime;
}

TaskActionSync($taskId);

if(count($errors) > 0) {
    $result['ok'] = false;
    $DB->Rollback();
} else {
    $DB->Commit();
}

$result = [
    'ok' => $updateResult,
    'request' => $request,
    'taskId' => $taskId,
    'nextTaskId' => $nextTaskId,
    'companyId' => $companyId,
    'task' => $arTask,
    'updated' => $arUpdateFields,
    'updateResult' => $updateResult,
    'errors' => $errors,
    'error' => $lastErrorText
];

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);