
<?php $APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['LIST'],
        'CURRENT_PAGE' => 1,
        'DEFAULT_PAGE_SIZE' => 20,
        'SHOW_ROW_CHECKBOXES' => false,
        'ALLOW_SORT' => false
    ]
);
?>