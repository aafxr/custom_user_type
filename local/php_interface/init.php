<?php

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/local/css/custom_contact/custom_contact.css");

//Константы
require dirname(__FILE__) . '/constants.php';

//Автозагрузка классов
require dirname(__FILE__) . '/autoload.php';

//Обработка событий
require dirname(__FILE__) . '/event_handler.php';


/**
 * обёртка для print_r() и var_dump()
 * @param $val - значение
 * @param string $name - заголовок
 * @param bool $mode - использовать var_dump() или print_r()
 * @param bool $die - использовать die() после вывода
 */
function print_p($val, $name = 'Содержимое переменной', $mode = false, $die = false){
    global $USER;
    if($USER->IsAdmin()){
        echo '<pre>'.(!empty($name) ? $name.': ' : ''); if($mode) { var_dump($val); } else { print_r($val); } echo '</pre>';
        if($die) die;
    }
}
