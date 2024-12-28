<?php

/**
 * Toolbar filter
 **/
use \Bitrix\UI\Toolbar\Facade\Toolbar;

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\Loader::includeModule('highloadblock');

Cmodule::includeModule("highloadblock");
function buildGridList($resTaskList, $list = [],$taskTypesList) {
    global $USER;
    $userId = $USER->GetID();
    $pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");

    while ($arTask = $resTaskList->GetNext()) {
        //echo "...";
        //if($row > 30) continue;
        $row++;
        $deadline = preg_replace('~(.+)\s? .*~', '$1', $arTask['DEADLINE']);
        $deadlineObj = new \DateTime($arTask['DEADLINE']);
        $currentDateTime = new \DateTime();
        if($deadlineObj < $currentDateTime) {
            $deadline .= "<br /><span class='overdue'>Просрочено</span>";
        }

        $arCompanyId = explode("_",$arTask['UF_CRM_TASK'][0]);
        if($arCompanyId[0] != "CO") continue;

        $actionId = $arTask['UF_AUTO_274474131393'];
        $actionText = $actionId ? '<span class="eventType">'.$taskTypesList[$actionId]['UF_CODE'].' '.$taskTypesList[$actionId]['UF_NAME'].'</span>' : '<span class="eventType">прочее</span>';

        $companyId = $arCompanyId[1];
        $companyText = "";
        if($companyId) {

            $entityTypeId = \CCrmOwnerType::Company;
            $entityUrl = \CCrmOwnerType::GetEntityShowPath( $entityTypeId, $companyId );

            $arCompany = CCrmCompany::GetList([], ["ID" => $companyId],['TITLE','COMPANY_TYPE','UF_*'])->Fetch();
            $companyText =  '<a href="'.$entityUrl.'"><b>'.$arCompany['TITLE'].'</b></a>';

            $companyTags = [];
            if($arCompany['UF_CRM_1712158211014']) { $companyTags[] = $arCompany['UF_CRM_1712158211014'];}
            if($arCompany['COMPANY_TYPE']) { $companyTags[] = $listCompanyTypes[$arCompany['COMPANY_TYPE']];}

            $companyText .= '<div class="grtxt">'.implode(" / ",$companyTags).'</div>'; // city field

            //$companyText .= '<pre>'.print_r($arCompany,true).'</pre>';

            /* props
            $companyCategories = $arCompany['UF_COMPANY_CATEGORIES'];
            $companyProperties = $arCompany['UF_PROPERTY_VALUES'];



            $companyProps = [];
            $resPropValues =$propValuesClass::getList(['filter' => ['UF_COMPANY'=> $companyId]]);
            while ($arPropValue = $resPropValues->Fetch()) {
                $companyProps[] = $arPropValue["UF_TITLE"];
            }

            $companyText .= '<div class="companyprops">'.implode("/",$companyProps).'</div>';
            */

        }
        // STAGE_ID = стадия // STATUS_COMPLETE // REAL_STATUS
        // UF_CRM_TASK_CONTACT
        // UF_NEXT_TASK
        // UF_TASK_REPORT

        $taskPath = CComponentEngine::MakePathFromTemplate(
            $pathTemplateTaskEntryView ,
            [
                'user_id' => $userId,
                'task_id' => $arTask['ID']
            ]
        );

        // UF_AUTO_280393729397 - result
        $objDateTime = new \Bitrix\Main\Type\DateTime( $arTask['CLOSED_DATE'], "d.m.Y H:i:s");
        if($arTask['UF_AUTO_280393729397']) {
            $taskResult = '<div class="reportsuccess"><span class="datetime">' . $objDateTime->format("d.m H:i") . "</span> " . $arTask['UF_AUTO_280393729397'] . '</div>';
        } else {
            $taskResult = '<a class="report" href="'.$taskPath.'">Написать отчет</a>';
        }

        $list[] = [
            'id'   => 'unique_row_id_'.$arTask['ID'],
            'data' => [
                'ID' => "<a href='".$taskPath."'>".$arTask['ID']."</a>",
                'DATE'        => $deadline,
                '~DATE'       => $deadline,
                'ACTION'      =>  $actionText,
                '~ACTION'     =>  $actionId,
                'DESCRIPTION' =>  $arTask['DESCRIPTION'],
                '~DESCRIPTION' => $arTask['DESCRIPTION'],
                'COMPANY' =>  $companyText,
                'RESULT' => $taskResult
            ],
            'actions' => [
                [
                    'text'    => 'Редактировать',
                    'onclick' => 'document.location.href="/accountant/reports/1/edit/"'
                ],
                [
                    'text'    => 'Удалить',
                    'onclick' => 'document.location.href="/accountant/reports/1/delete/"'
                ]
            ],
        ];
    }

    return $list;
}
$res = \Bitrix\Main\Config\Option::getForModule("tasks");

global $USER;
$userId = $USER->GetID();
$pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_edit");
$taskNewPath = CComponentEngine::MakePathFromTemplate(
    $pathTemplateTaskEntryView ,
    [
        'user_id' => $userId,
        'task_id' => 0
    ]
);
//echo $taskNewPath;

Toolbar::addButton([
    "link" =>  $taskNewPath,
    "text" => "Добавить прочие дела"
]);
?>
<style>
    .eventType {
        background-color: #FFF6D5;
        padding: 1px 4px;
        font-size: 12px;
        border-radius: 3px;
        margin-left: 2px;
        border: solid 1px #f5e9bf;
    }
    .grtxt {
        color: #55707e;
        font-size: 12px;
    }
    a.report {
        font-weight: bold;
        color: #D8454A;
        text-decoration: #D8454A underline dotted;
    }
    .reportsuccess {
        color: #107000;
        font-weight: bold;
        margin-top: 3px;

    }
    .reportsuccess .datetime {
        border-bottom: #107000 1px dotted;
    }

    .companyprops {
        font-size: 10px;
        margin-top: 6px;
    }
</style>
<?php
$APPLICATION->IncludeComponent(
    'bitrix:crm.control_panel',
    '',
    array(
        'ID' => 'START',
        'ACTIVE_ITEM_ID' => 'START',
        'PATH_TO_COMPANY_LIST' => isset($arParams['PATH_TO_COMPANY_LIST']) ? $arParams['PATH_TO_COMPANY_LIST'] : '',
        'PATH_TO_COMPANY_EDIT' => isset($arParams['PATH_TO_COMPANY_EDIT']) ? $arParams['PATH_TO_COMPANY_EDIT'] : '',
        'PATH_TO_CONTACT_LIST' => isset($arParams['PATH_TO_CONTACT_LIST']) ? $arParams['PATH_TO_CONTACT_LIST'] : '',
        'PATH_TO_CONTACT_EDIT' => isset($arParams['PATH_TO_CONTACT_EDIT']) ? $arParams['PATH_TO_CONTACT_EDIT'] : '',
        'PATH_TO_DEAL_LIST' => isset($arParams['PATH_TO_DEAL_LIST']) ? $arParams['PATH_TO_DEAL_LIST'] : '',
        'PATH_TO_DEAL_EDIT' => isset($arParams['PATH_TO_DEAL_EDIT']) ? $arParams['PATH_TO_DEAL_EDIT'] : '',
        'PATH_TO_LEAD_LIST' => isset($arParams['PATH_TO_LEAD_LIST']) ? $arParams['PATH_TO_LEAD_LIST'] : '',
        'PATH_TO_LEAD_EDIT' => isset($arParams['PATH_TO_LEAD_EDIT']) ? $arParams['PATH_TO_LEAD_EDIT'] : '',
        'PATH_TO_QUOTE_LIST' => isset($arResult['PATH_TO_QUOTE_LIST']) ? $arResult['PATH_TO_QUOTE_LIST'] : '',
        'PATH_TO_QUOTE_EDIT' => isset($arResult['PATH_TO_QUOTE_EDIT']) ? $arResult['PATH_TO_QUOTE_EDIT'] : '',
        'PATH_TO_INVOICE_LIST' => isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '',
        'PATH_TO_INVOICE_EDIT' => isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '',
        'PATH_TO_REPORT_LIST' => isset($arParams['PATH_TO_REPORT_LIST']) ? $arParams['PATH_TO_REPORT_LIST'] : '',
        'PATH_TO_DEAL_FUNNEL' => isset($arParams['PATH_TO_DEAL_FUNNEL']) ? $arParams['PATH_TO_DEAL_FUNNEL'] : '',
        'PATH_TO_EVENT_LIST' => isset($arParams['PATH_TO_EVENT_LIST']) ? $arParams['PATH_TO_EVENT_LIST'] : '',
        'PATH_TO_PRODUCT_LIST' => isset($arParams['PATH_TO_PRODUCT_LIST']) ? $arParams['PATH_TO_PRODUCT_LIST'] : ''
    ),
    $component
);
?>


<?php





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

/*
\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
    'FILTER_ID' => $arResult['GRID_ID'],
    'GRID_ID' => $arResult['GRID_ID'],
    'FILTER' => $arResult['GRID_FILTER'],
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_LABEL' => true
]);*/


$status = "COMPANY_TYPE";
$listCompanyTypes = \CCrmStatus::GetStatusList( $status );

$columns = [
    ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'DATE', 'name' => 'Крайний срок', 'sort' => 'DATE', 'default' => true],
    ['id' => 'ACTION', 'name' => 'Дествие', 'sort' => 'AMOUNT', 'default' => true],
    ['id' => 'COMPANY', 'name' => 'Название фирмы', 'sort' => 'PAYER_INN', 'default' => true],
    ['id' => 'DESCRIPTION', 'name' => 'Описание', 'sort' => 'PAYER_NAME', 'default' => true],
    ['id' => 'RESULT', 'name' => 'Отчет', 'sort' => 'IS_SPEND', 'default' => true],
];

$userId = $USER->GetID();

$currentDateTime = new \DateTime(date("Y-m-d"));

$filterDate = $_REQUEST['date'] ? $_REQUEST['date'] : date("d.m.Y");
$filterDateTime = new \DateTime($filterDate);



// Первый день месяца
$periodStart = clone $currentDateTime;
$periodStart->modify('first day of this month');

// Последний день месяца
$periodEnd = clone $filterDateTime;
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
/*
echo "periodType = ".$periodType."<br />";
echo '<pre>';

print_r($filterDateTime);
print_r($periodStart);
print_r($periodEnd);
print_r($currentDateTime);
echo '</pre>';
*/
// Считаем не закрытые для календаря
$arFilterTasksUnClosed = [

    [
        "LOGIC" => "AND",
        ">=DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($periodStart),
        "<=DEADLINE" =>  \Bitrix\Main\Type\DateTime::createFromPhp($periodEnd),
    ],
    "!REAL_STATUS" => CTasks::STATE_COMPLETED,
    "RESPONSIBLE_ID" => $userId,
    "!UF_CRM_TASK" => false
];
$resTaskUnClosed = CTasks::GetList(
    [
        "DEADLINE" => "DESC",
        "ID" => "DESC"
    ],
    $arFilterTasksUnClosed,
    [
        "*","UF_*"
    ]
);

$calendarUnclosedCount = [];
while ($arTask = $resTaskUnClosed->GetNext()) {
    $deadline = preg_replace('~(.+)\s? .*~', '$1', $arTask['DEADLINE']);
    $calendarUnclosedCount [$deadline]++;
}
?>

<div class="calendar">
    <div id="calendar"></div>
    <?php echo RefloorCalendar::getInterval(date('n.Y'), date('n.Y', strtotime('+2 month')),[],$calendarUnclosedCount); ?>
</div>




<?php

$arListFilter = [
    [
        "LOGIC" => "AND",
        ">=DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTime),
        "<DEADLINE" =>  \Bitrix\Main\Type\DateTime::createFromPhp($filterDateTime->modify("+1 day")),
    ],
    /*"!REAL_STATUS" => CTasks::STATE_COMPLETED*/
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

$row = 0;




$list = buildGridList($resTaskList,[],$taskTypesList);

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

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => "REFLOOR_TASK_GRID",
        'COLUMNS' => $columns,
        'ROWS' => $list,
        'CURRENT_PAGE' => 1,
        'DEFAULT_PAGE_SIZE' => 20
    ]
);

?>

