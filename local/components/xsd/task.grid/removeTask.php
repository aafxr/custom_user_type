<?php
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$entityBody = file_get_contents('php://input');
$request = json_decode($entityBody, true);

global $USER;
$userId = $USER->GetID();
$taskId = (int)$request['taskId'];

if (CModule::IncludeModule("tasks"))
{
    //$deleteResult = CTasks::Delete($request['taskId']);
    try {
        //$task = \CTaskItem::getInstance($taskId, 1);
        $oTaskItem = new CTaskItem($taskId, $userId);
        $oTaskItem->delete();
        $deleteResult = true;


    }
    catch (Exception $exception) {
        $deleteResult = false;
        $data = [
            'code' => $exception->getCode(),
            'msg' => $exception->getMessage()
        ];

    }

}

$result=[
    'ok' => $deleteResult,
    'data' => $data,
    'taskId' => $taskId,
    'request' => $request
];

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);