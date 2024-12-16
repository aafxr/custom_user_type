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

    use Bitrix\Disk\Folder;
    use \Bitrix\Disk\Internals\Grid\FolderListOptions;


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
    <style>
        .bx-disk-interface-toolbar {
            display: flex;
            align-items: center;
        }

        .disk-breadcrumbs-item-title {
            text-decoration: unset;
        }
    </style>
    <div class="disk-folder-list-toolbar" id="disk-folder-list-toolbar" style="align-items: center;">
        <?
        $APPLICATION->IncludeComponent(
            'bitrix:disk.breadcrumbs',
            '',
            array(
                'STORAGE_ID' => $_GET['STORAGE_ID'],
//            'BREADCRUMBS_ROOT' => $arResult['BREADCRUMBS_ROOT'],
                'BREADCRUMBS' => $crumbs,
                'ENABLE_DROPDOWN' => false,//!$arResult['IS_TRASH_MODE']
                //'ENABLE_SHORT_MODE' => true,
            )
        );
        ?>

        <?
        //        $APPLICATION->IncludeComponent(
        //            'bitrix:disk.folder.toolbar',
        //            '',
        //            array_intersect_key(
        //                $_GET,
        //                array(
        //                    'STORAGE_ID' => true,
        //                    'FOLDER_ID' => true,
        //                )))
        //        <script>
        //            BX(() => {
        //                const node = document.querySelector('.bx-disk-context-button')
        //                console.log(node)
        //                if(node){
        //                    node.style.display = 'none'
        //                }
        //            })
        //        </script>
        ?>

        <div class="disk-folder-list-config">
            <!--        --><? // if (!empty($arResult['ENABLED_TRASHCAN_TTL'])): ?>
            <!--            <div class="disk-folder-list-trashcan-info">-->
            <!--                <span class="disk-folder-list-trashcan-info-text">-->
            <?php //= Loc::getMessage('DISK_FOLDER_LIST_TRASHCAN_TTL_NOTICE', ['#TTL_DAY#' => $arResult['TRASHCAN_TTL']]) ?><!--</span>-->
            <!--            </div>-->
            <!--        --><? // endif; ?>
            <!--        <div class="disk-folder-list-sorting">-->
            <!--            <span class="disk-folder-list-sorting-text" data-role="disk-folder-list-sorting">-->
            <?php //= $sortLabel ?><!--</span>-->
            <!--        </div>-->
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
        'ENABLE_LIVE_SEARCH' => true,
    ]
);
?>


<script>
    BX.Main.filterManager.data['<?='folder_list_' . $_GET['STORAGE_ID']?>'] = BX.Main.Filter
</script>
<div id="disk-folder-list-toolbar"></div>


<?php
$APPLICATION->IncludeComponent('bitrix:disk.folder.list', "",
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
    BX(() => {
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

        const resize = (w = window) => {
            const parent = w.parent
            const frame = parent.document.querySelector('iframe')
            console.log(frame)
            if (frame) {
                frame.style.height = w.document.body.scrollHeight + 17 + 'px'
            }
            if (w !== parent) resize(parent)
        }
        resize();
        window.addEventListener('resize', resize)
    })
</script>
