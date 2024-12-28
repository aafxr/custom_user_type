<?php

/**
 * Toolbar filter
 **/
use \Bitrix\UI\Toolbar\Facade\Toolbar;
use \Bitrix\UI\Buttons\Icon;

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\Loader::includeModule('highloadblock');

Cmodule::includeModule("highloadblock");


$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $arResult['grid_id'],
        'COLUMNS' => $arResult['columns'],
        'ROWS' => $arResult['rows'],
        'CURRENT_PAGE' => 1,
        'DEFAULT_PAGE_SIZE' => 20,
        'SHOW_TOTAL_COUNTER' => true,
        'ALLOW_PIN_HEADER' => true, // Разрешает закреплять панель с заголовками вверху таблицы при вертикальной прокрутке
        'SHOW_ROW_CHECKBOXES' => false, // Отображать чек-боксы для строк
        'ALLOW_SORT' => false, // азрешить пользователям изменять сортировку данных по тем столбцам, которые разрешены для изменения сортировки. Персональная настройка.
    ]
);

?>
<script type="text/javascript">

    localStorage.setItem('crmHistoryIframeId', window.frameElement.id);

    function deleteTask(taskId) {
        console.log("<?=$componentPath;?>");
        console.log("delete action task "+taskId);
        const url = "<?=$componentPath;?>/removeTask.php";
        console.log(url);
        const requestData = {
            'taskId' : taskId
        };

        fetch(url, {
            method: 'POST',
            body: JSON.stringify(requestData),
        }).then((response) => response.json())
            .then((data) => {
                if (data.ok) {
                    console.log("delete success");
                    console.dir(data);
                    //alert("Загрузка временно недоступна");
                    location.reload();
                } else {
                    alert("Ошибка удаления задачи");
                    console.dir(data);
                }
            });
    }
</script>
