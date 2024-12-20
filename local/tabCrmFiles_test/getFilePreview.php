<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('disk');


$result = ['ok' => false];
$fileId = $_GET['fileId'];

$promoPhotoSize = [
    "width" => 60,
    "height" => 60
];

if ($fileId) {
    $file = Bitrix\Disk\File::load(array('ID' => $fileId));
    $fileId = $file->getFileId();
    if ($fileId) {
        $res = CFile::ResizeImageGet($fileId, $promoPhotoSize);
        if ($res && $res['src']) {
            $result['ok'] = true;
            $result['url'] = $res['src'];
        }
    }
}


echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);