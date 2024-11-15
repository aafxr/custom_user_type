#### инструкция

local\php_interface\lib\UserType\CustomCompanyContacts.php - класс описывающий пользовательское поле

local\php_interface\event_handler.php - подключение обработчиков класса пользовательского поля

local\contact\contact_edit.php - подключение компонента формы в CDialog

local\components\dev\company.contacts - компонент отображает пользовательское поле в карточке компании


local\components\dev\contact.popup - компонент отображает форму добавления / редактирования контакта
параметры компонента задаются в
\local\contact\contact_edit.php
для работы компонента необходимо указать актуальные параметры в
\local\contact\contact_edit.php
local\components\dev\contact.popup\ajax.php

---

- добавить пользовательское поле (UF_CONTACT_PREFERENCES_AREA) типа строка, множественное для блока CRM_CONTACT

используется в:
\local\php_interface\lib\UserType\CustomCompanyContacts.php
\local\components\dev\contact.popup\ajax.php
\local\contact\contact_edit.php

- добавить пользовательское поле (UF_CONTACT_QUIZ_AREA) типа строка, множественное для блока CRM_CONTACT
  используется в:
  \local\php_interface\lib\UserType\CustomCompanyContacts.php
  \local\components\dev\contact.popup\ajax.php
  \local\contact\contact_edit.php

- указать поле для комментария в CRM_CONTACT (текущее UF_CONTACT_COMMENT)
заменить в 
\local\contact\contact_edit.php