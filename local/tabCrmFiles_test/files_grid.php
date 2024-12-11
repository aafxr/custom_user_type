<div id="disk-folder-list-toolbar"></div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
/** CMain */
global $APPLICATION;
$APPLICATION->ShowHead();

CJSCore::Init(["fx","ajax","viewer","disk"]);
\CModule::IncludeModule("crm");
\Bitrix\Main\UI\Extension::load("ui.buttons");

$params = [];
$params['FOLDER_ID'] = $_GET['FOLDER_ID'];
$params['STORAGE_ID'] = $_GET['STORAGE_ID'];
//$params['AJAX_MODE'] = 'Y';

$APPLICATION->IncludeComponent("refloor:disk.folder.list","",$_GET);

?>

