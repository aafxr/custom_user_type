<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->ShowHead();

$params = [];

if(isset($_GET['folderId'])) $params['FOLDER_ID'] = $_GET['folderId'];
if(isset($_GET['storageId'])) $params['STORAGE_ID'] = $_GET['storageId'];

$APPLICATION->IncludeComponent("bitrix:disk.folder.list","",$params);