<?php
if(!defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED !== true) die();






function getLeadResponsible($cityId, $selectNum){
    return '1';
    switch ($selectNum){
        case 1:
        case 2:
            return '241'; // филоненко анастасия
        case 3:
            return '104'; // кравченко валерия
        case 4:
            /*
             5266 - москва
             5976 - мос обл

            '268' // бондаренко валентин
            '257' // неупокоев евгений
            '30' // бреусова светлана
            */
            if ($cityId == '5266') return '268';
            elseif ($cityId == '5976') return '257';
            return '30';
        case 5:
            return '241'; // филоненко анастасия
        case 6:
        case 7:
        case 8:
            return '104'; // кравченко валерия
        default:
            return '1';
    }
}



$responsibleForDesigners = [];

function getResponsibleForNewDesigner(){
    $hldata = Bitrix\Highloadblock\HighloadBlockTable::getById(22)->fetch();
    $hlentity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hldata);
    $hlclass = $hlentity->getDataClass();


    $currentTime = DateTime::createFromPhp(new \DateTime);


    $res = $hlclass::getList([
        'filter' => [
            '<UF_START' => $currentTime,
            '>=UF_END' => $currentTime,
        ]
    ]);

    $arSchedule = $res->fetch();
    if($arSchedule) return $arSchedule['UF_RESPONSIBLE_ID'];

    /* создаем запись в расписании */
    $res = $hlclass::getList([
        'order' => ['UF_END' => 'DESC'],
        'filter' => [
            '<UF_END' => $currentTime,
        ]
    ]);

    $arSchedule = $res->fetch();

}


