<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();
/** CMain */
global $APPLICATION;
/** CBitrixComponent */
global $component;

use Bitrix\Disk\Ui;
CJSCore::Init(["fx","ajax","viewer","disk"]);
\CModule::IncludeModule("crm");
\Bitrix\Main\UI\Extension::load("ui.buttons");

Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/disk/css/disk.css');


$btnId = rand(1000000, 10000000);

?>
<div id="<?=$btnId?>" class="input-file <?=$arResult['CLASS_NAME']?>">
    <input id="upload-field" type="file" name="files[]" multiple="">
    <span class="ui-btn ui-btn-success ui-btn-icon-add">Загрузить файл</span>
</div>
<script>
    function getDialogTemplate(parameters = {}){
        const {fileUploads} = parameters
        let content = []
        let complited = []

        if(fileUploads && Array.isArray(fileUploads)){
            for (const fu of fileUploads){
                if(fu.isCompleted() ) complited.push(fu)
                const inner = `
                        <tr id="${fu.uploadId}">
                            <td class="bx-disk-popup-upload-file-progress-container-td">
                                <div class="bx-disk-popup-upload-file-progress-container">
                                    <div class="bx-disk-popup-upload-file-progress-line-end" style="width: ${fu.getProgress()}%"></div>
                                    <div class="bx-disk-popup-upload-file-progress-filename">${fu.file.name}</div>
                                </div>
                            </td>
                            <td class="bx-disk-popup-upload-file-progress-container-lasttd">
                                ${fu.isCompleted() ? '<span class="bx-disk-popup-upload-file-progress-btn-end" id="file${fu.uploadId}Done"></span>' : ''}
                            </td>
                        </tr>
                    `
                content.push(inner)
            }
        }

        return `
            <div id="popup-window-content-bx-dfu-upload-FolderList" class="popup-window-content" dropzone="copy f:*/*">
                <div class="bx-disk-popup-container" style="display: block;">
                    <div class="bx-disk-popup-content tac bx-disk-upload-file">
                        <div class="bx-disk-popup-upload-title">Загружено файлов <span id="FolderListNumber">${complited.length}</span> из <span id="FolderListCount">${content.length}</span></div>
                        <div class="bx-disk-upload-file-section">
                            <table class="bx-disk-upload-file-list" id="FolderListPlaceHolder">
                                ${content.join('')}
                            </table>
                            <div class="bx-disk-upload-file-buttons">
                                <button
                                    class="ui-btn ui-btn-success ui-btn-icon-add"
                                    onclick="BX.Disk.upload<?=$btnId?>.addFiles(event)"
                                    >Загрузить еще</button>
                            </div
                        </div>
                    </div>
                </div>
            </div>
            `
    }

    BX(() => {
        let folderId = <?=$arResult['FOLDER_ID'] ?>;
        const namespace = BX.namespace('BX.Disk.upload<?=$btnId?>')
        let fileUploads = []
        const btnNode = document.getElementById('<?=$btnId?>')
        const inputFilesNode = btnNode.querySelector('input')


        BX.addCustomEvent('disk.upload.files:folderChange', (controllerId, uploader) => {
            if(uploader){
                folderId = uploader.folderId
                fileUploads = []
            }
        })

        namespace.addFiles = function(e){
            inputFilesNode.click()
        }

        console.log(namespace)

        const dialog = new BX.PopupWindow('call_feedback', window.body, {
            title: 'Загрузка нового документа',
            autoHide : true,
            lightShadow : true,
            closeIcon : true,
            closeByEsc : true,
            overlay: {}
        });
        dialog.setContent(getDialogTemplate({fileUploads}));

        btnNode.addEventListener('click', (e) => {
            if(fileUploads.length){
                dialog.show()
                return
            }
            inputFilesNode.click()
        })

        inputFilesNode.click()
        window.dialog  = dialog

        if(inputFilesNode){
            inputFilesNode.addEventListener('input', (e) => {
                for (let i = 0; i < e.target.files.length; i++){
                    const file = e.target.files.item(i)
                    console.log({
                        file,
                        URL: '<?= $arResult['COMPONENT_PATH'] ?>' + '/uploadFile.php',
                        folderId,
                        onSuccess: (f) => {
                            dialog.setContent(getDialogTemplate({fileUploads}))
                            if(fileUploads.every(f => f.isCompleted())){
                                BX.onCustomEvent(window, 'disk.upload.files:allLoadsDone', [this.id, this]);
                            }
                        },
                        onChange: (f) => dialog.setContent(getDialogTemplate({fileUploads})),
                        reject: (f) => dialog.setContent(getDialogTemplate({fileUploads})),
                    })
                    const fu = new BX.Disk.FileUploadClass({
                        file,
                        URL: '<?= $arResult['COMPONENT_PATH'] ?>' + '/uploadFile.php',
                        folderId,
                        onSuccess: (f) => {
                            dialog.setContent(getDialogTemplate({fileUploads}))
                            if(fileUploads.every(f => f.isCompleted())){
                                BX.onCustomEvent(window, 'disk.upload.files:allLoadsDone', [this.id, this]);
                            }
                        },
                        onChange: (f) => dialog.setContent(getDialogTemplate({fileUploads})),
                        reject: (f) => dialog.setContent(getDialogTemplate({fileUploads})),
                    })
                    fileUploads.push(fu)
                }
                dialog.setContent(getDialogTemplate({fileUploads}))
                dialog.show()
                fileUploads.forEach(f => f.send())
            })
        }
    })
</script>
