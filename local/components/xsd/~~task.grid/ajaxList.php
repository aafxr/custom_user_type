<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require ("functions.php");



$entityBody = file_get_contents('php://input');
$request = json_decode($entityBody, true);

/**
 * @todo props - убрать
 * остальное вынести в functions
 */
// props
$hlElID = 11; //
$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlElID)->fetch();
$propClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

$hlElID = 12; //
$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlElID)->fetch();
$propValuesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();


$hlid = 2; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlid)->fetch();
$taskTypesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

$taskTypesRes = $taskTypesClass::getList([]);
$taskTypesList = [];
while ($taskTypeData = $taskTypesRes->Fetch()) {
    $taskTypesList[$taskTypeData['ID']] = $taskTypeData;
}


$status = "COMPANY_TYPE";
$listCompanyTypes = \CCrmStatus::GetStatusList( $status );

$columns = [
    ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'DATE', 'name' => 'Дата', 'sort' => 'DATE', 'default' => true],
    ['id' => 'ACTION', 'name' => 'Дествие', 'sort' => 'AMOUNT', 'default' => true],
    ['id' => 'COMPANY', 'name' => 'Название фирмы', 'sort' => 'PAYER_INN', 'default' => true],
    ['id' => 'DESCRIPTION', 'name' => 'Описание', 'sort' => 'PAYER_NAME', 'default' => true],
    ['id' => 'RESULT', 'name' => 'Отчет', 'sort' => 'IS_SPEND', 'default' => true],
];

$userId = $request['userId'];

$currentDateTime = new \DateTime(date("Y-m-d"));
$currentDateTimeNextDay = clone $currentDateTime;
$currentDateTimeNextDay->setTime(0,0);
$currentDateTimeNextDay->modify("+1 day");


$filterDate = $request['date'];
$filterDateTime = new \DateTime($filterDate);

$filterDateTimeNextDay = clone $filterDateTime;
$filterDateTimeNextDay->modify("+1 day");

// Первый день месяца
$periodStart = clone $currentDateTime;
$periodStart->modify('-1 month');
$periodStart->modify('first day of this month');

// Последний день месяца
$periodEnd = clone $periodStart;
$periodEnd->modify('+2 month');
$periodEnd->modify('last day of this month');

/**
 *  условия
 *  1. дата отбора < текущей = показываем только закрытые задачи, за заданный период
 *  2. дата отбора = текущей = показываем задачи на сегодня, и все не закрытые задачи за прошлые даты
 *  3. дата отбора > текущей = показываем все задачи на заданный период
 */


$periodType = 2;
if($filterDateTime < $currentDateTime) { $periodType = 1; }
if($filterDateTime > $currentDateTime) { $periodType = 3; }

/**
* Собираем задачи
* 1. собираем все завершенные, они будут по дате закрытия
* 2. собираем все запланированые
* 3. собираем все просрочки = отображаются на текущую дату
    * ---
 *
 */

$arListFilterCompleted = [
    [
        "LOGIC" => "AND",
        ">=CLOSED_DATE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTime),
        "<CLOSED_DATE" =>  \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTimeNextDay),
    ],
    "REAL_STATUS" => CTasks::STATE_COMPLETED,
    "RESPONSIBLE_ID" => $userId,
    /* "!UF_CRM_TASK" => false */ // Убираем так как нам надо будет фиксировать и обычные задачи
];
$resTaskList = CTasks::GetList(
    [
        "DEADLINE" => "DESC",
        "ID" => "DESC"
    ],
    $arListFilterCompleted,
    [
        "*","UF_*"
    ]
);
$row = 0;
$list = buildGridList($resTaskList,[],$taskTypesList);

if($periodType > 1 ) {
    $arListFilterPlanned = [
        [
            "LOGIC" => "AND",
            ">=DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTime),
            "<DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTimeNextDay),
        ],
        "!REAL_STATUS" => CTasks::STATE_COMPLETED,
        "RESPONSIBLE_ID" => $userId,
        /* "!UF_CRM_TASK" => false */ // Убираем так как нам надо будет фиксировать и обычные задачи
    ];
    $resTaskList = CTasks::GetList(
        [
            "UF_AUTO_851551329931" => 'DESC',
            "DEADLINE" => "DESC",
            "ID" => "DESC"
        ],
        $arListFilterPlanned,
        [
            "*", "UF_*"
        ]
    );
    $list = buildGridList($resTaskList, $list, $taskTypesList);

}


//echo $periodType;


if($periodType == 2) {
    $arListFilter = [
        [
            "LOGIC" => "AND",
            "<DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($currentDateTime),

        ],
        "!REAL_STATUS" => CTasks::STATE_COMPLETED,
        "RESPONSIBLE_ID" => $userId,
        "!UF_CRM_TASK" => false
    ];

    $resTaskList = CTasks::GetList(
        [
            "DEADLINE" => "DESC",
            "ID" => "DESC"
        ],
        $arListFilter,
        [
            "*","UF_*"
        ]
    );
    $list = buildGridList($resTaskList,$list,$taskTypesList);
}

ob_start(); ?>

<?php $APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => "REFLOOR_TASK_GRID",
        'COLUMNS' => $columns,
        'ROWS' => $list,
        'CURRENT_PAGE' => 1,
        'DEFAULT_PAGE_SIZE' => 20,

    ]
); ?>

<?php
$html = ob_get_contents();
ob_end_clean();
echo $html;
/*
$result = [
    'ok' => true,
    'html' => $html
];

echo json_encode($result);*/