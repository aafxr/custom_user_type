<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/local/form_lead/src/HeicToJpg.php');

try {
    if(HeicToJpg::isHeic($_SERVER['DOCUMENT_ROOT'].'/local/form_lead/'."sample1.heic")){
        \HeicToJpg::convert($_SERVER['DOCUMENT_ROOT'].'/local/form_lead/'."sample1.heic")->saveAs($_SERVER['DOCUMENT_ROOT'].'/local/form_lead/'."sample1.jpg");
    } else{
        echo 'not heic';
    }
} catch (Exception $e){
    echo $e->getMessage();
}
