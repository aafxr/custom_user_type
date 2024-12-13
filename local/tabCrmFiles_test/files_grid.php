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


$arFolderIds = $_GET['CRUMBS'] ?? [];
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
//$APPLICATION->IncludeComponent(
//    'bitrix:disk.folder.toolbar',
//    '',
//    array_intersect_key(
//        $_GET,
//        array(
//            'STORAGE_ID' => true,
//            'FOLDER_ID' => true,
//        ))
//);

//$APPLICATION->IncludeComponent(
//    'bitrix:disk.breadcrumbs',
//    "disk-folder-breadcrumbs",
//    [
//        'BREADCRUMBS' => $crumbs,
//        'BREADCRUMBS_ID' => 'disk-folder-breadcrumbs',
//        'STORAGE_ID' => $_GET['STORAGE_ID']
//
//    ]
//);
//$uriString = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestUri();
//$uri = new \Bitrix\Main\Web\Uri($uriString);


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
<div id="disk-folder-list-toolbar" class="disk-folder-list-toolbar"></div>


<?php
$APPLICATION->IncludeComponent('bitrix:disk.folder.list', "",
    array_intersect_key(
        $_GET,
        array(
            'STORAGE_ID' => true,
            'FOLDER_ID' => true,
        ))
);

//<script>
//     BX(() => {
/*        const node = document.getElementById('folder_list_<?=$_GET['STORAGE_ID']?>_search_container')*/
//         if (node) node.style.display = 'none'
//     })
//</script>

?>


<script>
    BX(() => {
        const refreshLink = document.querySelectorAll('.main-grid-more a')
        if (refreshLink) {
            const url = new URL(refreshLink.href)
            document.querySelectorAll('a.bx-disk-folder-title')
                .forEach(e => {
                    const u = new URL(url)
                    u.searchParams.set('FOLDER_ID', e.dataset.objectId)
                    e.href = u.toString()
                })
        }
    })
</script>
