<?php
if(!defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED !== true) die();


/**
 *
 * - 1 - Салон напольных покрытий    UC_KUQTW0
 * - 2 - Салон отделочных материалов UC_BHXO1M
 * - 3 - Комплектование объектов     UC_L3LRS2
 * - 4 - Розничный покупатель        UC_O2JYI1
 * - 5 - Интернет-магазин            UC_2X9UMM
 * - 6 - Дизайнер                    COMPETITOR
 * - 7 - Архитектор                  UC_2HCJM7
 * - 8 - Другое                      OTHER
 *
 *
 * @param number $option число  1 - 8
 * @return string
 */
function getCompanyType($option): string
{
    switch ($option){
        case 1: return 'UC_KUQTW0';
        case 2: return 'UC_BHXO1M';
        case 3: return 'UC_L3LRS2';
        case 4: return 'UC_O2JYI1';
        case 5: return 'UC_2X9UMM';
        case 6: return 'COMPETITOR';
        case 7: return 'UC_2HCJM7';
        case 8: return 'OTHER';
        default: return '';
    }
}
