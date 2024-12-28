<?
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->ShowHead();
global $USER;
use Bitrix\Disk\Ui;
CJSCore::Init(["fx","ajax","viewer","disk"]);
\CModule::IncludeModule("crm");
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.filter");

$userId = $USER->GetID();
$idRootFolderCRM = 77386;
$idPlacementEntity = false;


try {
    $placement = $_REQUEST['PLACEMENT'] ?? $_GET['PLACEMENT'];
    $placementOptions = $_REQUEST['PLACEMENT_OPTIONS'] ?? $_GET['PLACEMENT_OPTIONS'];
    if($placement == "CRM_COMPANY_DETAIL_TAB") {
        $arPlacementOptions = json_decode($placementOptions, true);
        $idPlacementEntity = $arPlacementOptions['ID'];
        $arCompany = CCrmCompany::GetByID($idPlacementEntity);
    }
} catch (Exception $exception) {
    // Exception
}
//echo "idPlacementEntity=".$idPlacementEntity."<br />";

$driver = \Bitrix\Disk\Driver::getInstance();
$storage = $driver->getStorageByCommonId('shared_files_s1');

if($idPlacementEntity && $storage):

    $company = CCrmCompany::GetByID($idPlacementEntity);
    $storageId = $storage->getId();

    $folder = $storage->getRootObject();
    $folderCrm = $storage->getChild(
        array(
            '=NAME' => 'CRM',
            'TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER
        )
    );
    if($folderCrm) {
        $folderEntityNameId = '[C'.$idPlacementEntity.']';
        $bxCompanyCleanName = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/u", '', $company['TITLE']);
        $folderEntityName = mb_strimwidth(trim($bxCompanyCleanName),0,30)." ".$folderEntityNameId;
        //$folderEntityName = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", '', $folderEntityName);

        $folderEntity = $folderCrm->getChild(
            [
                'NAME' => "%".$folderEntityNameId,
                'TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER
            ]
        );
        if($folderEntity) {

            $folderEntity->rename($folderEntityName);
            // Если найден , обновляем имя
        } else {
            // Если не найден, создаем папку для хранения
            echo 'no exist: '.$folderEntityName.'<br />';
            $folderEntity = $folderCrm->addSubFolder([
                'NAME' => $folderEntityName,
                'CREATED_BY' => 1
            ]);
        }

        $folderId = $folderEntity->getId();
        $folderName = $folderEntity->getName();
        $urlManager = \Bitrix\Disk\Driver::getInstance()->getUrlManager();
		$folderURL = urlencode($urlManager->getPathFolderList($folderEntity));
        $parentId = $folderEntity->getParentId();


    } else {
        //echo "CRM folder not found<br />";
    }
    ?>


    <?php
    /*
    $APPLICATION->includeComponent(
        'bitrix:disk.folder.toolbar',
        '',
        [
            "FOLDER_ID"=>$idRootFolderCRM,
            "STORAGE_ID" => $storageId
        ]
    );*/
    ?>
    <iframe
        class="app-frame"
        src="https://<?= $_SERVER['HTTP_HOST']; ?>/local/apps/tabCrmFiles/files_grid.php?FOLDER_ID=<?=$folderId; ?>&STORAGE_ID=<?=$storageId;?>&PARENT_ID=<?= $parentId ?>"
        style="width: 100%; height: 100%; border-radius: var(--ui-border-radius-md);border: none;"
    ></iframe>
<?php endif ?>

<style>
   .content-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .top-panel {
        flex: 0 0 auto;
    }

    .content-panel {
        position relative;
        flex: 1 1 auto;
        overflow-y: auto;
    }

    .ut-btn-toolbar {
        display: flex;
        flex-direction: row;
        gap: 0.5rem;
    }
    .top-panel {
       display: flex;
       justify-content: space-between;
    }


    .input-file {
        position: relative;
        display: inline-block;
    }
    .input-file span {
        position: relative;
        display: inline-block;
        cursor: pointer;
        outline: none;
        text-decoration: none;

        text-align: center;

        box-sizing: border-box;
        border: none;
        margin: 0;
        transition: background-color 0.2s;
    }
    .input-file input[type=file] {
        position: absolute;
        z-index: -1;
        opacity: 0;
        display: block;
        width: 0;
        height: 0;
    }

    /* Focus */
    .input-file input[type=file]:focus + span {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* Hover/active */
    .input-file:hover span {
        -background-color: #59be6e;
    }
    .input-file:active span {
        -background-color: #2E703A;
    }

    /* Disabled */
    .input-file input[type=file]:disabled + span {
        background-color: #eee;
    }
</style>
<?php
/*
?>
<script type="text/javascript">
    document.getElementById("upload-field").addEventListener("change", function () {
        const url = '/local/apps/tabCrmFiles/upload.php';
        const files = document.querySelector('[type=file]').files;
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            let file = files[i]
            formData.append('files[]', file)
        }
        formData.append('folderId',<?=$folderId;?>);

        fetch(url+"?folderId=<?=$folderId;?>", {
            method: 'POST',
            body: formData,
        }).then((response) => response.json())
        .then((data) => {
            if (data.ok) {
                console.dir(data);
                //alert("Загрузка временно недоступна");
                location.reload();
            } else {
                alert("fail!");
            }
        });
    })
    function downloadFromMawi() {
        console.log("Start Download!");
        document.getElementById("mawisync").classList.remove("ui-btn-icon-download");
        document.getElementById("mawisync").classList.add("ui-btn-wait");
        fetch("/import/ajaxGetFilesItem.php?id=<?=$arCompany['ORIGIN_ID'];?>").then((response) => response.json())
        .then((data) => {
            if (data.ok) {
                console.dir(data);
                //alert("Загрузка временно недоступна");
                location.reload();
            } else {
                alert("fail!");
            }
        });
        return false;
    }
</script>
*/
?>
