<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();
/** CMain */
global $APPLICATION;

use Bitrix\Disk\Ui;
CJSCore::Init(["fx","ajax","viewer","disk"]);
\CModule::IncludeModule("crm");
\Bitrix\Main\UI\Extension::load("ui.buttons");

Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/disk/css/disk.css')

?>
<style>
    .bx-disk-popup-upload-file-progress-line-end{
        transition: all .2s;
    }

    .bx-disk-popup-upload-file-progress-filename{
        display: flex;
        align-items: center;
    }

</style>
<label class="input-file">
    <input id="upload-field" type="file" name="files[]" multiple="">
    <span class="ui-btn ui-btn-success ui-btn-icon-add">Загрузить файл</span>
</label>
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
                        </div>
                    </div>
                </div>
            </div>
            `
    }

    BX(() => {
        const fileUploadList = []

       const dialog = new BX.CDialog({
           title: 'Загрузка нового документа',
           content: getDialogTemplate({fileUploads: fileUploadList}),
           width: 580,
           buttons: [
               {
                   title: 'Заккрыть',
                   name: 'Заккрыть',
                   action: function () {
                       BX.WindowManager.Get().Close();
                   },
                   onclick: "BX.WindowManager.Get().Close()"
               }
           ]
       })

        window.dialog  = dialog

        const inputFilesNode = document.getElementById('upload-field')
        const fileUploads = []
        if(inputFilesNode){
            inputFilesNode.addEventListener('change', (e) => {
                for (let i = 0; i < e.target.files.length; i++){
                    const file = e.target.files.item(i)
                    const fu = new BX.Disk.FileUploadClass({
                        file,
                        URL: '<?= $arResult['COMPONENT_PATH'] ?>' + '/uploadFile.php',
                        folderId: <?=$arResult['FOLDER_ID'] ?>,
                        onSuccess: (f) => dialog.SetContent(getDialogTemplate({fileUploads})),
                        onChange: (f) => dialog.SetContent(getDialogTemplate({fileUploads})),
                        reject: (f) => dialog.SetContent(getDialogTemplate({fileUploads})),
                    })
                    fileUploads.push(fu)
                }
                dialog.SetContent(getDialogTemplate({fileUploads}))
                dialog.Show()
                fileUploads.forEach(f => f.send())
            })
        }

    })
</script>
