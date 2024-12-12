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

        print_r($folderId);
        echo '<br/>';
        print_r($storageId);


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
    <div class="top-panel">
        <div class="ut-btn-toolbar ut-btn-split">
            <form id="upload-form" method="post" enctype="multipart/form-data">
                <label class="input-file">
                    <input id="upload-field" type="file" name="files[]"  multiple>
                    <span class="ui-btn ui-btn-success ui-btn-icon-add" href="protoBitrix24://X:/CRM/<?=$folderName?>/">Загрузить файл</span>
                </label>
            </form>

            <a class="ui-btn ui-btn-primary ui-btn-icon-disk" href="protoBitrix24://X:/CRM/<?=$folderName?>/">Открыть на компьютере</a>

        </div>
        <div class="ut-btn-split">
            <?php if($arCompany['ORIGIN_ID']): ?>
            <div id="mawisync" class="ui-btn ui-btn-active ui-btn-icon-download" onclick="downloadFromMawi()">Загрузить из MawiSoft</div>
            <?php endif ?>
            <a class="ui-btn ui-btn-icon-setting" download href="https://crm.refloor-nsk.ru/upload/script/refProtoBitrix24.reg">Настройка ПК</a>

            <a class="ui-btn ui-btn-icon-setting" download href="https://crm.refloor-nsk.ru/upload/script/disk.cmd">Подлючение диска</a>

        </div>
    </div>
    <div id="bx-disk-container">
        <?
//        $APPLICATION->IncludeComponent("bitrix:disk.folder.list","",[
//            'FOLDER_ID' => $folderId,
//            'STORAGE_ID' => $storageId,
//            'IFRAME' => 'Y'
//        ]);
        ?>
        <iframe
            class="app-frame"
            src="https://<?= $_SERVER['HTTP_HOST']; ?>/local/tabCrmFiles_test/files_grid.php?FOLDER_ID=<?=$folderId; ?>&STORAGE_ID=<?=$storageId;?>&PARENT_ID=<?= $parentId ?>"
            style="width: 100%;height: 100%;border-radius: var(--ui-border-radius-md);border: none;"
        ></iframe>
    </div>
<div id="disk-folder-list-toolbar"></div>
<?php endif ?>
<style>
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
<script type="text/javascript">
    document.getElementById("upload-field").addEventListener("change", function () {
        const url = '/local/tabCrmFiles_test/upload.php';
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

    //BX.ready(() => {
    //    BX.ajax.get('<?php //= "/local/apps/tabCrmFiles/files_grid.php?folderId=$folderId&storageId=$storageId";?>//',
    //        r => document.getElementById('bx-disk-container').innerHTML = r
    //    )
    //})
</script>


