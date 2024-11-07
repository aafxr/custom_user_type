<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main;
use \Bitrix\Crm;

if ( !Main\Loader::IncludeModule('crm') )
{
    echo "crm module not included";
    die();
}

$companies = Crm\CompanyTable::getList([ 'select' => ['*', 'UF_*'] ]);

$list = [];

foreach ($companies as $company) {
    if (!isset($company['UF_AUTO_100_31']) || $company['UF_AUTO_100_31'] == false){
        $c = new CCrmCompany();
        $array = array("UF_AUTO_100_31" => '-');
        $c->Update($company['ID'], $array);
    }
    $list[] = $company;
}
    echo json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
