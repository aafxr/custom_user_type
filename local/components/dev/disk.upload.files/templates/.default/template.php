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
    function newFileUploadTemplate(fu, onDone = () => {}){
        const node = document.createElement('tr')
        node.id = fu.uploadId
        node.innerHTML = `
            <td class="bx-disk-popup-upload-file-progress-container-td">
                <div class="bx-disk-popup-upload-file-progress-container">
                    <div class="bx-disk-popup-upload-file-progress-line-end" style="width: ${fu.getProgress()}%"></div>
                    <div class="bx-disk-popup-upload-file-progress-filename">${fu.file.name}</div>
                </div>
            </td>
            <td class="bx-disk-popup-upload-file-progress-container-lasttd">
                <span class="bx-disk-popup-upload-file-progress-btn-end" style="opacity: 0; pointer-events: none" id="file${fu.uploadId}Done"></span>
            </td>
        `



        fu.onChange = function(){
            node.querySelector('.bx-disk-popup-upload-file-progress-line-end').style.width = `${fu.getProgress()}%`
        }.bind(fu)

        fu.onSuccess = function() {
            console.log(fu)
            const n = node.querySelector('.bx-disk-popup-upload-file-progress-btn-end')
            n.style.opacity = 1
            n.style.pointerEvents = 'all'
            onDone()
        }.bind(fu)

        fu.onReject = () => {

        }

        return {
            node, fileUploader: fu
        }

    }

    BX(() => {
        const chunkSize = 1024*1024 * 10;
        let folderId = <?=$arResult['FOLDER_ID'] ?>;
        const namespace = BX.namespace('BX.Disk.upload<?=$btnId?>')
        let fileUploads = []
        let filesLoaded = 0
        const btnNode = document.getElementById('<?=$btnId?>')
        const inputFilesNode = btnNode.querySelector('input')


        BX.addCustomEvent('disk.upload.files:folderChange', (controllerId, uploader) => {
            if(uploader){
                folderId = uploader.folderId
                fileUploads = []
            }
        })


        const dialogContent = `
            <div id="popup-window-content-bx-dfu-upload-FolderList" class="popup-window-content" dropzone="copy f:*/*">
                <div class="bx-disk-popup-container" style="display: block;">
                    <div class="bx-disk-popup-content tac bx-disk-upload-file">
                        <div class="bx-disk-popup-upload-title">Загружено файлов <span id="FolderListNumber">0</span> из <span id="FolderListCount">0</span></div>
                        <div class="bx-disk-upload-file-section">
                            <div class="bx-disk-upload-file-content">
                                <table class="bx-disk-upload-file-list" id="FolderListPlaceHolder">

                                </table>
                            </div>
                            <div class="bx-disk-upload-file-buttons">
                                <button
                                    class="ui-btn ui-btn-success ui-btn-icon-add"
                                    onclick="BX.Disk.upload<?=$btnId?>.addFiles(event)"
                                    >Загрузить еще</button>
                                <button
                                    class="ui-btn"
                                    onclick="BX.Disk.upload<?=$btnId?>.closeDialog(event)"
                                    >Закрыть</button>
                            </div
                        </div>
                    </div>
                </div>
            </div>
            `
        const dialog = new BX.PopupWindow('call_feedback', window.body, {
            title: 'Загрузка нового документа',
            autoHide : true,
            lightShadow : true,
            closeIcon : true,
            closeByEsc : true,
            overlay: {}
        });
        dialog.setContent(dialogContent);
        const tableNode = dialog.getContentContainer().querySelector('.bx-disk-upload-file-list')


        namespace.addFiles = function(e){
            inputFilesNode.click()
        }


        namespace.closeDialog = function(e){
            dialog.close()
        }


        function updateDialogTitle(){
            const dialogTitleNode = dialog.getContentContainer().querySelector('.bx-disk-popup-upload-title')
            dialogTitleNode.querySelector('#FolderListNumber').innerText = fileUploads.length
            dialogTitleNode.querySelector('#FolderListCount').innerText = filesLoaded
            if(fileUploads.every(f => f.isCompleted())){
                BX.onCustomEvent(window, 'disk.upload.files:allLoadsDone', [])
            }
        }


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
                    const fu = new BX.Disk.FileUploadClass({
                        file,
                        URL: '<?= $arResult['COMPONENT_PATH'] ?>' + '/uploadFile.php',
                        folderId,
                        chunk: chunkSize,
                    })
                    const {node} = newFileUploadTemplate(fu, () => {
                        filesLoaded += 1
                        updateDialogTitle()
                    })
                    tableNode.appendChild(node)
                    fileUploads.push(fu)
                }
                dialog.show()
                updateDialogTitle()
                fileUploads.forEach(f => f.send())
            })
        }
    })
</script>
