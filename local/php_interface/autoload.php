<?php

use Bitrix\Main\Loader;

//Автозагрузка наших классов
Loader::registerAutoLoadClasses(null, [
    'CustomCompanyContacts' => APP_CLASS_FOLDER . 'UserType/CustomCompanyContacts.php',
]);