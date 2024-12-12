<div id="disk-folder-list-toolbar"></div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
/** CMain */
global $APPLICATION;
$APPLICATION->ShowHead();

CJSCore::Init(["fx","ajax","viewer","disk"]);
\CModule::IncludeModule("crm");
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.filter");
\Bitrix\Main\UI\Extension::load("ui.grid");

$params = [];
$params['FOLDER_ID'] = $_GET['FOLDER_ID'];
$params['STORAGE_ID'] = $_GET['STORAGE_ID'];

$storage = \Bitrix\Disk\Driver::getInstance()->getStorageByCommonId('shared_files_s1');
$folder = \Bitrix\Disk\Folder::loadById($_GET['FOLDER_ID']);


$urlManager = \Bitrix\Disk\Driver::getInstance()->getUrlManager();
$folderURL = $urlManager->getPathFolderList($folder);

print_r($folderURL)

?>

<script>
    console.log(BX.Main.filterManager.data)
</script>
<?php
//$APPLICATION->IncludeComponent("refloor:disk.common", ".default", [
//        "SEF_MODE" => "Y",
//        "SEF_FOLDER" => $folderURL,
//        "FOLDER_ID" => $folder->getId(),
//        "STORAGE_ID" => Bitrix\Intranet\Integration\Wizards\Portal\Ids::getDiskStorageId('SHARED_STORAGE_ID'),
//    ]
//);

$APPLICATION->IncludeComponent("refloor:disk.folder.list","",
    array_merge(array_intersect_key($params,array(
//        'STORAGE' => true,
//        'PATH_TO_FOLDER_LIST' => true,
//        'PATH_TO_FILE_HISTORY' => true,
//        'PATH_TO_FILE_VIEW' => true,
//        'PATH_TO_DISK_BIZPROC_WORKFLOW_ADMIN' => true,
//        'PATH_TO_DISK_START_BIZPROC' => true,
//        'PATH_TO_DISK_TASK_LIST' => true,
//        'PATH_TO_DISK_TASK' => true,
//        'PATH_TO_DISK_VOLUME' => true,
        'STORAGE_ID' => true,
        'FOLDER_ID' => true,
    )), array(
        'FOLDER' => $folder,
        'IFRAME' => 'Y',
//        'RELATIVE_PATH' => '/',
//        'RELATIVE_ITEMS' => [],
    ))
);

?>
