#### инструкция

local\php_interface\lib\UserType\CustomCompanyContacts.php - класс описывающий пользовательское поле

local\php_interface\event_handler.php - подключение обработчиков класса пользовательского поля

local\contact\contact_edit.php - подключение компонента формы в CDialog

local\components\dev\company.contacts - компонент отображает пользовательское поле в карточке компании


local\components\dev\contact.popup - компонент отображает форму добавления / редактирования контакта
параметры компонента задаются в<br/>
\local\contact\contact_edit.php<br/>
для работы компонента необходимо указать актуальные параметры в<br/>
\local\contact\contact_edit.php<br/>
local\components\dev\contact.popup\ajax.php<br/>

---

- добавить пользовательское поле (UF_CONTACT_PREFERENCES_AREA) типа строка, множественное для блока CRM_CONTACT
используется в:<br/>
\local\php_interface\lib\UserType\CustomCompanyContacts.php<br/>
\local\components\dev\contact.popup\ajax.php<br/>
\local\contact\contact_edit.php<br/>

- добавить пользовательское поле (UF_CONTACT_QUIZ_AREA) типа строка, множественное для блока CRM_CONTACT
  используется в:<br/>
  \local\php_interface\lib\UserType\CustomCompanyContacts.php<br/>
  \local\components\dev\contact.popup\ajax.php<br/>
  \local\contact\contact_edit.php<br/>

- указать поле для комментария в CRM_CONTACT (текущее UF_CONTACT_COMMENT)
заменить в <br/>
\local\contact\contact_edit.php