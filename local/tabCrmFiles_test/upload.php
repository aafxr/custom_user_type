<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('disk');

global $USER;
$userId = $USER->GetID();

$result = [
    'ok' => true,
    'request' => $_REQUEST
];

$folderId = (int)$_REQUEST['folderId'];
$folder = \Bitrix\Disk\Folder::getById($folderId);

$result['1'] = '1';
$result['files'] = $_FILES;
print_r($_FILES);
die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result['2'] = '2';
    if (isset($_FILES['files'])) {
        $result['3'] = '3';
        $errors = [];
        $path = $_SERVER['DOCUMENT_ROOT'].'/upload/temp/';
        //$extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $all_files = count($_FILES['files']['tmp_name']);
        $result['4'] = '4';
        for ($i = 0; $i < $all_files; $i++) {
            $file_name = $_FILES['files']['name'][$i];
            $file_tmp = $_FILES['files']['tmp_name'][$i];
            $file_type = $_FILES['files']['type'][$i];
            $file_size = $_FILES['files']['size'][$i];
            $file_ext = strtolower(end(explode('.', $_FILES['files']['name'][$i])));

            $file = $path .$file_name;
            $arFileName = explode(".",$_FILES['files']['name'][$i]);
            $fileName = implode(".",array_slice($arFileName, 0, -1));

            $file_name = $fileName." (".$userId."-".time().").".$file_ext;
            $file = $path .$file_name;

            /*if (!in_array($file_ext, $extensions)) {
                $errors[] = 'Extension not allowed: ' . $file_name . ' ' . $file_type;
            }*/

            if ($file_size > 1024*1024*24) {
                $errors[] = 'File size exceeds limit: ' . $file_name . ' ' . $file_type;
            }

            if (empty($errors)) {
                move_uploaded_file($file_tmp, $file);
                $arFile = CFile::MakeFileArray($file);
                $file = $folder->uploadFile($arFile, array(
                    'CREATED_BY' => 1
                ));
                //unlink($file);
                $result['temp'] = $arFile;
            }
        }

        if ($errors) {
            $result['ok'] = false;
            $result['errors'] = $errors;
        }
    }
}

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);