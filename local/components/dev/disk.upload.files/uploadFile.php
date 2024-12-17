<?php
$uploaddir="./uploaddir";

// Идентификатор загрузки (аплоада). Для генерации идентификатора я обычно использую функцию md5()
$hash=$_SERVER["HTTP_UPLOAD_ID"];

// Информацию о ходе загрузки сохраним в системный лог, это позволить решать проблемы оперативнее
openlog("html5upload.php", LOG_PID | LOG_PERROR, LOG_LOCAL0);

// Проверим корректность идентификатора
if (preg_match("/^[0123456789abcdef]{32}$/i",$hash)) {


    // Если HTTP запрос сделан методом GET, то это не загрузка порции, а пост-обработка
    if ($_SERVER["REQUEST_METHOD"]=="GET") {

        // abort - сотрем загружаемый файл. Загрузка не удалась.
        if ($_GET["action"]=="abort") {
            if (is_file($uploaddir."/".$hash.".html5upload")) unlink($uploaddir."/".$hash.".html5upload");
            print "ok abort";
            return;
        }

        // done - загрузка завершена успешно. Переименуем файл и создадим файл-флаг.
        if ($_GET["action"]=="done") {
            syslog(LOG_INFO, "Finished for hash ".$hash);

            // Если файл существует, то удалим его
            if (is_file($uploaddir."/".$hash.".original")) unlink($uploaddir."/".$hash.".original");

            // Переименуем загружаемый файл
            rename($uploaddir."/".$hash.".html5upload",$uploaddir."/".$hash.".original");

            // Создадим файл-флаг
            $fw=fopen($uploaddir."/".$hash.".original_ready","wb");if ($fw) fclose($fw);
        }
    }

    // Если HTTP запрос сделан методом POST, то это загрузка порции
    elseif ($_SERVER["REQUEST_METHOD"]=="POST") {

        syslog(LOG_INFO, "Uploading chunk. Hash ".$hash." (".intval($_SERVER["HTTP_PORTION_FROM"])."-".intval($_SERVER["HTTP_PORTION_FROM"]+$_SERVER["HTTP_PORTION_SIZE"]).", size: ".intval($_SERVER["HTTP_PORTION_SIZE"]).")");

        // Имя файла получим из идентификатора загрузки
        $filename=$uploaddir."/".$hash.".html5upload";

        // Если загружается первая порция, то откроем файл для записи, если не первая, то для дозаписи.
        if (intval($_SERVER["HTTP_PORTION_FROM"])==0)
            $fout=fopen($filename,"wb");
        else
            $fout=fopen($filename,"ab");

        // Если не смогли открыть файл на запись, то выдаем сообщение об ошибке
        if (!$fout) {
            syslog(LOG_INFO, "Can't open file for writing: ".$filename);
            header("HTTP/1.0 500 Internal Server Error");
            print "Can't open file for writing.";
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
    syslog(LOG_INFO, "Uploading chunk. Wrong hash ".$hash);
    header("HTTP/1.0 500 Internal Server Error");
    print "Wrong session hash.";
}

// Закроем syslog лог
closelog();