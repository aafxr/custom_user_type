<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$result = ['STATUS' => 'OK', 'REQUEST'=>$_REQUEST];


$obTask = new CTasks;
$success = $obTask->Update($_REQUEST['taskId'], ['DEADLINE'=>$_REQUEST['newDate']]);

echo json_encode($result);

?>