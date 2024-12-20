<?php

$result = [];
$result['errors'] = [];

function printResult() {
    global $result;
    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

$folderId = $_GET['folderId'];
$fileName = $_GET['file'];
if(!$folderId) $result['errors'][] = 'folderId not set in query params';
if(!$fileName) $result['errors'][] = 'file not set in query params';

if(count($result['errors']) != 0){
    printResult();
    die();
}

$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/upload/temp/.part/';

if(!is_dir($uploadDir)){
    if(!mkdir($uploadDir, 0777, true)) {
        $result['errors'][] = 'folder not exist';
        printResult();
        die();
    }
}

// Идентификатор загрузки (аплоада). Для генерации идентификатора я обычно использую функцию md5()
$hash = $_SERVER["HTTP_UPLOAD_ID"];



// Информацию о ходе загрузки сохраним в системный лог, это позволить решать проблемы оперативнее
//openlog("html5upload.php", LOG_PID | LOG_PERROR, LOG_LOCAL0);

// Проверим корректность идентификатора
if (preg_match("/^[0123456789abcdef]{32,}$/i",$hash)) {
    $hash .='__'.$fileName;
    $result['hash'] = $hash;

    // пост-обработка
    // abort - сотрем загружаемый файл. Загрузка не удалась.
    if ($_GET["action"]=="abort") {
        if (is_file($uploadDir.$hash.".html5upload")) unlink($uploadDir.$hash.".html5upload");
        $result['ok'] = true;
        printResult();
        return;
    }

    // done - загрузка завершена успешно. Переименуем файл и создадим файл-флаг.
    if ($_SERVER["REQUEST_METHOD"]=="POST" && $_GET["action"]=="done") {
        require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
        /** CUser */
        global $USER;

        // Если файл существует, то удалим его
        if (is_file($uploadDir.$hash.".original")) unlink($uploadDir.$hash.".original");

        $file_ext = strtolower(end(explode('.', $hash)));
        $arFileName = explode(".", end(explode('__', $hash)));
        $fileName = implode(".",array_slice($arFileName, 0, -1));
        $fileName = $fileName." (".$USER->GetID()."-".time().").".$file_ext;

        $uploadDirResult = $_SERVER['DOCUMENT_ROOT'].'/upload/temp/';

        // Переименуем загружаемый файл
        rename($uploadDir.$hash.".html5upload",$uploadDirResult.$fileName);
        // Создадим файл-флаг
        $fw=fopen($uploadDir.$hash.".original_ready","wb");if ($fw) fclose($fw);

        if (!\Bitrix\Main\Loader::includeModule('disk')){
            $result['errors'][] = 'disk module not included';
            printResult();
            die();
        }
        $file = $uploadDirResult.$fileName;
        $arFile = CFile::MakeFileArray($file);

        $folder = \Bitrix\Disk\Folder::getById($folderId);
        $file = $folder->uploadFile($arFile, array(
            'CREATED_BY' => $USER->GetID()
        ));
    }

    // Если HTTP запрос сделан методом POST, то это загрузка порции
    elseif ($_SERVER["REQUEST_METHOD"]=="POST") {

        syslog(LOG_INFO, "Uploading chunk. Hash ".$hash." (".intval($_SERVER["HTTP_PORTION_FROM"])."-".intval($_SERVER["HTTP_PORTION_FROM"]+$_SERVER["HTTP_PORTION_SIZE"]).", size: ".intval($_SERVER["HTTP_PORTION_SIZE"]).")");

        // Имя файла получим из идентификатора загрузки
        $filename=$uploadDir.$hash.".html5upload";

        // Если загружается первая порция, то откроем файл для записи, если не первая, то для дозаписи.
        if (intval($_SERVER["HTTP_PORTION_FROM"])==0)
            $fout=fopen($filename,"wb");
        else
            $fout=fopen($filename,"ab");

        // Если не смогли открыть файл на запись, то выдаем сообщение об ошибке
        if (!$fout) {
            header("HTTP/1.0 500 Internal Server Error");
            $result['errors'][] =  "Can't open file for writing.";
            $result['fileName'] = $filename;
            printResult();
            return;
        }

        // Из stdin читаем данные отправленные методом POST - это и есть содержимое порций
        $fin = fopen("php://input", "rb");
        if ($fin) {
            while (!feof($fin)) {
                // Считаем 1Мб из stdin
                $data=fread($fin, 1024*1024);
                // Сохраним считанные данные в файл
                fwrite($fout,$data);
            }
            fclose($fin);
        }

        fclose($fout);
    }

    // Все нормально, вернем HTTP 200 и тело ответа "ok"
    header("HTTP/1.0 200 OK");
    print "ok\n";
}
else {
    // Если неверный идентификатор загрузку, то вернем HTTP 500 и сообщение об ошибке
    header("HTTP/1.0 500 Internal Server Error");
    $result['errors'][] = "Wrong session hash.";
}


printResult();