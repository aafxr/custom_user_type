<?php
if(!defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED !== true) die();

function getLeadResponsible($cityId, $selectNum){
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