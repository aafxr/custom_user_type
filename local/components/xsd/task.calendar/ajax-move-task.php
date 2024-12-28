<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$result = ['STATUS' => 'OK', 'REQUEST'=>$_REQUEST];


$obTask = new CTasks;
$success = $obTask->Update($_REQUEST['taskId'], ['DEADLINE'=>$_REQUEST['newDate']." 19:00"]);

$rsTask = CTasks::GetByID($_REQUEST['taskId']);
if ($arTask = $rsTask->GetNext()) {
    $result['task'] = $arTask;
}

echo json_encode($result);

?>