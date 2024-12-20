<style>
    .bx-disk-interface-toolbar {
        display: flex;
        align-items: center;
    }

    .disk-breadcrumbs-item-title {
        text-decoration: unset;
    }

    .ui-btn.js-disk-add-button.ui-btn-primary.ui-btn-dropdown {
        margin-left: var(--ui-btn-margin-left);
    }

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

    .preview-img{
        position: relative;
        width: 60px!important;
        height: 60px!important;
        background: transparent!important;;
    }

    .preview-img img{
        position: absolute;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .upload-more{
        margin-left: 10px;
    }
</style>

<div id="bx-disk-container" class="bx-disk-container">
    <div id="disk-folder-list-toolbar"></div>
    <div id="disk-folder-breadcrumbs"></div>
    <?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    /** CMain */
    global $APPLICATION;
    $APPLICATION->ShowHead();

    CJSCore::Init(["fx", "ajax", "viewer", "disk"]);
    \CModule::IncludeModule("crm");
    \Bitrix\Main\UI\Extension::load("ui.buttons");
    \Bitrix\Main\UI\Extension::load("ui.toolbar");

    use Bitrix\Disk\Folder;
    use \Bitrix\Disk\Internals\Grid\FolderListOptions;


    echo $_GET['CRUMBS'].'<br/>';
    $arFolderIds = $_GET['CRUMBS'] ?? [$_GET['FOLDER_ID']];
    $crumbs = [];

    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    foreach ($arFolderIds as $id) {
        $folder = Folder::getById($id);
        if ($folder) {
            $crumbs[] = [
                'ID' => $id,
                'NAME' => $folder->getName(),
                'ENCODED_LINK' => $actual_link
            ];
        }
    }


    ?>
    <?php
    $uri = new \Bitrix\Main\Web\Uri(Bitrix\Main\Context::getCurrent()->getRequest()->getRequestUri());

    $uriToTileM = (clone $uri);
    $uriToTileM->addParams(['viewMode' => FolderListOptions::VIEW_MODE_TILE, 'viewSize' => FolderListOptions::VIEW_TILE_SIZE_M,]);

    $uriToTileXL = (clone $uri);
    $uriToTileXL->addParams(['viewMode' => FolderListOptions::VIEW_MODE_TILE, 'viewSize' => FolderListOptions::VIEW_TILE_SIZE_XL,]);

    $uriToGrid = (clone $uri);
    $uriToGrid->addParams(['viewMode' => FolderListOptions::VIEW_MODE_GRID,]);
    ?>

    <div class="disk-folder-list-toolbar top-panel" id="disk-folder-list-toolbar" style="align-items: center;">
        <?
        $APPLICATION->IncludeComponent(
            'bitrix:disk.breadcrumbs',
            '',
            array(
                'STORAGE_ID' => $_GET['STORAGE_ID'],
                'BREADCRUMBS' => $crumbs,
                'ENABLE_DROPDOWN' => false,
                //!$arResult['IS_TRASH_MODE']
                //'ENABLE_SHORT_MODE' => true,
            )
        );
        ?>

        <div class="disk-folder-list-config">
            <div class="disk-folder-list-view">
                <a href="?<?= $uriToGrid->getQuery() ?>"
                   class="disk-folder-list-view-item disk-folder-list-view-item-lines <?= ($arResult['GRID']['MODE'] === FolderListOptions::VIEW_MODE_GRID ? 'disk-folder-list-view-item-active' : '') ?>"></a>
                <a href="?<?= $uriToTileM->getQuery() ?>"
                   class="disk-folder-list-view-item disk-folder-list-view-item-grid js-disk-change-view <?= ($arResult['GRID']['MODE'] === FolderListOptions::VIEW_MODE_TILE && $arResult['GRID']['VIEW_SIZE'] === FolderListOptions::VIEW_TILE_SIZE_M ? 'disk-folder-list-view-item-active' : '') ?>"
                   data-view-tile-size="<?= FolderListOptions::VIEW_TILE_SIZE_M ?>"></a>
                <a href="?<?= $uriToTileXL->getQuery() ?>"
                   class="disk-folder-list-view-item disk-folder-list-view-item-grid-tile js-disk-change-view <?= ($arResult['GRID']['MODE'] === FolderListOptions::VIEW_MODE_TILE && $arResult['GRID']['VIEW_SIZE'] === FolderListOptions::VIEW_TILE_SIZE_XL ? 'disk-folder-list-view-item-active' : '') ?>"
                   data-view-tile-size="<?= FolderListOptions::VIEW_TILE_SIZE_XL ?>"></a>
            </div>
            <?php
            $menuButton = new \Bitrix\UI\Buttons\Button(["text" => "Добавить",]);
            $menuButton->addClass('ui-btn js-disk-add-button ui-btn-primary ui-btn-dropdown');
            echo $menuButton->render();
            $APPLICATION->IncludeComponent('refloor:disk.upload.files', '', ['FOLDER_ID' => $_GET['FOLDER_ID'], 'CLASS_NAME' => 'upload-more'])
            ?>
        </div>
    </div>
</div>
<?php
$APPLICATION->includeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID' => 'folder_list_' . $_GET['STORAGE_ID'],
        'GRID_ID' => 'folder_list_' . $_GET['STORAGE_ID'],
        'ENABLE_LIVE_SEARCH' => false,
    ]
);
?>


<script>
    BX.Main.filterManager.data['<?='folder_list_' . $_GET['STORAGE_ID']?>'] = BX.Main.Filter
</script>
<!--<div id="disk-folder-list-toolbar"></div>-->

<?php
$APPLICATION->IncludeComponent('refloor:disk.folder.list', "",
    array_merge(
        array_intersect_key(
            $_GET,
            array(
                'STORAGE_ID' => true,
                'FOLDER_ID' => true,
            )),
        [
            'TOP_ACTION_PANEL_RENDER_TO' => '#disk-folder-list-toolbar',
        ])
);


?>

<script>
    BX(() => {
        const node = document.getElementById('folder_list_<?=$_GET['STORAGE_ID']?>_search_container')
        if (node) node.style.display = 'none'
    })
</script>

<script>
    BX.addCustomEvent(window, 'disk.upload.files:allLoadsDone', () => {
        const tbodyTdList = document.querySelectorAll('#folder_list_<?=$_GET['STORAGE_ID']?>_table tbody .main-grid-row-body')
        tbodyTdList.forEach(td => td.remove())
        document.querySelector('.main-grid-more a').click()
    })
</script>

<script>
    function updateFoldersLink(){
        const refreshLink = document.querySelector('.main-grid-more a')
        if (refreshLink) {
            const url = new URL(refreshLink.href)
            document.querySelectorAll('a.bx-disk-folder-title')
                .forEach(e => {
                    const u = new URL(url)
                    u.searchParams.set('FOLDER_ID', e.dataset.objectId)
                    e.href = u.toString()
                })
        }
        const url = new URL(refreshLink.href)
        BX.onCustomEvent(window, 'disk.upload.files:folderChange', [
            '<?=$_GET['STORAGE_ID']?>',
            {folderId: url.searchParams.get('FOLDER_ID')}
        ])
    }
    BX(() => {
        const folderListNode = document.getElementById('folder_list_<?=$_GET['STORAGE_ID']?>')
        updateFoldersLink()
        let observer = new MutationObserver(updateFoldersLink);
        observer.observe(folderListNode, {subtree: true, childList: true})
    })
</script>


<script>
    function updateGridPReview(){
        const tableNode = document.getElementById('folder_list_<?=$_GET['STORAGE_ID']?>_table')
        if(!tableNode) return

        const extList = ['jpg', 'png', 'jpeg']
        const spans = tableNode.querySelectorAll('span.bx-disk-folder-title')
        spans.forEach(s => {
            if(!s.dataset.src) return
            const fileExt = s.dataset.src.split('.').pop()
            if(extList.includes(fileExt)){
                const fileId = s.dataset.objectId
                fetch('https://crm.refloor-nsk.ru/local/tabCrmFiles_test/getFilePreview.php?fileId=' + fileId)
                    .then(r => r.json())
                    .then(data => {
                        if(data.ok){
                            try {
                                const node = s.closest('.bx-disk-object-name').querySelector('.bx-disk-file-icon')
                                node.innerHTML = `<img src="${data.url}" />`
                                node.classList.add('preview-img')
                            }catch (e){console.error(e)}
                        }
                    })
            }
        })
    }
    BX.addCustomEvent('BX.Main.grid:paramsUpdated', updateGridPReview)
    BX(() => {updateGridPReview()})
</script>