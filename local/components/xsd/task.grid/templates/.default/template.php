<?php
CJSCore::Init(array("popup"));
\Bitrix\Main\UI\Extension::load("ui.icons.b24");
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");

/**
 * Toolbar filter
 **/
use \Bitrix\UI\Toolbar\Facade\Toolbar;
use \Bitrix\UI\Buttons\Icon;
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.tooltip");

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\Loader::includeModule('highloadblock');

Cmodule::includeModule("highloadblock");

function buildGridList($resTaskList, $list = [],$taskTypesList) {
    global $USER;
    $userId = $USER->GetID();


    $pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");

    $pathTaskEntryEdit = COption::GetOptionString("tasks", "paths_task_user_edit");


    while ($arTask = $resTaskList->GetNext()) {

        /*echo '<pre>';
        print_r($arTask);
        echo '</pre>';*/
        $taskPath = CComponentEngine::MakePathFromTemplate(
            $pathTemplateTaskEntryView,
            [
                'user_id' => $userId,
                'task_id' => $arTask['ID']
            ]
        );
        $taskEditPath = CComponentEngine::MakePathFromTemplate(
            $pathTaskEntryEdit,
            [
                'user_id' => $userId,
                'task_id' => $arTask['ID']
            ]
        );

        $taskCompleted = ($arTask['REAL_STATUS'] == CTasks::STATE_COMPLETED);

        //$deadline = preg_replace('~(.+)\s? .*~', '$1', $arTask['DEADLINE']);

        $deadlineDateTime = new \DateTime($arTask['DEADLINE']);
        $currentDateTime = new \DateTime();
        $taskDateTimeCaption = "<br />";

        if($taskCompleted) {
            /**
             * Если задача завершена - дата задачи ставим по дате закрытия задачи
             */
            $closedDateTime = new \DateTime($arTask['CLOSED_DATE']);

            $taskDateTime = clone $closedDateTime;
            $taskDateTimeCaption .= "<span>Завершена</span>";

            if($closedDateTime > $taskDateTime) {
                // Завершенная задача тоже может быть просрочена
                $taskDateTimeCaption .= "<span class='overdue'>Просрочена</span>";
            }

        } else {

            $taskDateTime = $deadlineDateTime;

            if($deadlineDateTime < $currentDateTime) {
                $taskDateTimeCaption .= "<span class='overdue'>Просрочена</span>";
            }
        }



        $arCompanyId = explode("_",$arTask['UF_CRM_TASK'][0]);
        if($arCompanyId[0] != "CO") {

            $companyId = false;
            $companyText = "Прочие дела!";
        } else {
            $companyId = $arCompanyId[1];
        }

        $priority = $arTask['UF_AUTO_851551329931'];
        $priorityIcon = "";
        switch ($priority) {
            case 'срочная, важная';
                $priorityIcon = '<img src="/local/images/important_urgent.svg" />';
                break;
            case 'важная, не срочная';
                $priorityIcon = '<img src="/local/images/important.svg" />';
                break;
            case 'срочная, не важная';
                $priorityIcon = '<img src="/local/images/urgent.svg" />';
                break;
        }

        $actionId = $arTask['UF_AUTO_274474131393'];
        $actionText = $actionId ? '<a href="'.$taskPath.'" class="eventType">'.$priorityIcon.' '.$taskTypesList[$actionId]['UF_CODE'].' '.$taskTypesList[$actionId]['UF_NAME'].'</a>' : '<span class="eventType">Прочие дела</span>';

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



        // UF_AUTO_280393729397 - result
        $objDateTime = new \Bitrix\Main\Type\DateTime( $arTask['CLOSED_DATE'], "d.m.Y H:i:s");
        if($arTask['UF_AUTO_280393729397']) {
            //$taskResult = '<div class="reportsuccess"><a href="'.$taskPath.'" class="datetime">' . $objDateTime->format("d.m H:i") . "</a> " . TxtToHTML($arTask['UF_AUTO_280393729397']) . '</div>';
            $taskResult = '<div class="action-task-report" data-id="'.$arTask['ID'].'"><span class="datetime">' . $objDateTime->format("d.m H:i") . "</span> " . TxtToHTML($arTask['UF_AUTO_280393729397']) . '</div>';
        } else {
            // $taskPath

            //$taskResult = '<a class="report" href="'.$taskPath.'" >Написать отчет #</a>';
            $taskResult = '<div class="action-task-report" data-id="'.$arTask['ID'].'" >Написать отчет #</div>';
        }
        /*$taskPriority = "";
        if($arTask['UF_AUTO_851551329931']) {
            $arTaskPriority = explode(",",$arTask['UF_AUTO_851551329931']);
            foreach ($arTaskPriority as $priority) {
                $taskPriority .= "<div class='priority'>".$priority."</div>";
            }
        } */



        //$taskPriority = "<div class='priority-list'>".$taskPriority."</div>";;

        $taskDescription = ($arTask['DESCRIPTION']!=""?$arTask['DESCRIPTION']:$arTask['TITLE']);
        $taskDescription = "<a class='text' href='".$taskPath."'>".TxtToHTML($taskDescription)."</a>";
        if($arTask['CREATED_BY'] != $arTask['RESPONSIBLE_ID']) {
            $arCreator = \Bitrix\Main\UserTable::getById($arTask['CREATED_BY'])->fetch();

            $taskDescription = "<b>Поручение от сотрудника ".$arCreator['NAME']." ".$arCreator['LAST_NAME'].":&nbsp;</b>".$taskDescription;
        }
        $list[] = [
            'id'   => 'unique_row_id_'.$arTask['ID'],
            'data' => [
                'ID' => "<a href='".$taskPath."'>".$arTask['ID']."</a>",
                'DATE'        => "<a class='text' href='".$taskPath."'>".$taskDateTime->format("d.m.Y").$taskDateTimeCaption."</a>",
                '~DATE'       => $taskDateTime,
                'ACTION'      =>  $actionText,
                '~ACTION'     =>  $actionId,
                'DESCRIPTION' =>  $taskDescription,
                '~DESCRIPTION' => $arTask['DESCRIPTION'],
                'COMPANY' =>  $companyText,
                'RESULT' => $taskResult
            ],
            'actions' => [
                [
                    'text'    => 'Редактировать',
                    'onclick' => 'document.location.href="'.$taskEditPath.'"'
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

$userId = $_REQUEST['userId'] ? $_REQUEST['userId'] : $USER->GetID();
$userList = getSubordinateList($idArrayOnly = true);
if(!in_array($userId,$userList)) {
    $userId = $USER->GetID();
}


/**
 * @todo Нужно проверить что есть доступ
 */


$pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_edit");
$taskNewPath = CComponentEngine::MakePathFromTemplate(
    $pathTemplateTaskEntryView ,
    [
        'user_id' => $userId,
        'task_id' => 0
    ]
);
//echo $taskNewPath;


?>
<div class="refloor-start-container">
<a id="refloor-refresh" class="refloor-btn-refresh ui-btn ui-btn-icon-business" href="<?=$_SERVER["REQUEST_URI"]?>"></a>

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

/**
 *   TASK:GRID
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
    ['id' => 'DATE', 'name' => 'Дата', 'sort' => 'DATE', 'default' => true],
    ['id' => 'ACTION', 'name' => 'Дествие', 'sort' => 'AMOUNT', 'default' => true],
    ['id' => 'COMPANY', 'name' => 'Название фирмы', 'sort' => 'PAYER_INN', 'default' => true],
    ['id' => 'DESCRIPTION', 'name' => 'Описание', 'sort' => 'PAYER_NAME', 'default' => true],
    ['id' => 'RESULT', 'name' => 'Отчет', 'sort' => 'IS_SPEND', 'default' => true],
];

//$userId = $USER->GetID();

$currentDateTime = new \DateTime(date("Y-m-d"));
$currentDateTimeNextDay = clone $currentDateTime;
$currentDateTimeNextDay->setTime(0,0);
$currentDateTimeNextDay->modify("+1 day");


$filterDate = $_REQUEST['date'] ? $_REQUEST['date'] : date("d.m.Y");
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
$periodEnd->setTime(23,59,00);


/**
 * Кнопки тулбара
 */

$taskNewPath .= "?DEADLINE=".$filterDate."%2023%3A59&UF_CRM_TASK=CO_19422";

Toolbar::addButton([
    "click" => new \Bitrix\UI\Buttons\JsCode(
        "BX.SidePanel.Instance.open('/crm/company/details/0/')" //произвольный код, который будет выполнен при клике на кнопку
    ),
    "text" => "Добавить компанию",
    "icon" => Icon::ADD,
    'classList' => [
        "ui-btn ui-btn-success -action-task-popup"
    ],
]);
Toolbar::addButton([
    "click" => new \Bitrix\UI\Buttons\JsCode(
        "refloorNewTask()" //произвольный код, который будет выполнен при клике на кнопку
    ),
    "text" => "Добавить прочие дела",
    "icon" => Icon::ADD,
    'classList' => [
        "ui-btn ui-btn-success -action-task-popup"
    ],
]);

/*Toolbar::addButton([
    "link" =>  $taskNewPath,
    "text" => "Добавить прочие дела",
    "icon" => Icon::ADD,
    'classList' => [
        "ui-btn ui-btn-success action-task-popup"
    ],
]);*/
Toolbar::addButton([
    "link" =>  "/crm/overdue/",
    "text" => "Просрочки",
    "icon" => Icon::INFO,

]);
Toolbar::addButton([
    "link" => "/crm/import/",
    "text" => "Загрузить компанию из MawiSoft",
]);


/**
 *  условия
 *  1. дата отбора < текущей = показываем только закрытые задачи, за заданный период
 *  2. дата отбора = текущей = показываем задачи на сегодня, и все не закрытые задачи за прошлые даты
 *  3. дата отбора > текущей = показываем все задачи на заданный период
 */


$periodType = 2;
if($filterDateTime < $currentDateTime) { $periodType = 1; }
if($filterDateTime > $currentDateTime) { $periodType = 3; }

// Считаем закрытые для календаря
$arFilterTasksClosed = [
    [
        "LOGIC" => "AND",
        ">=CLOSED_DATE" => \Bitrix\Main\Type\DateTime::createFromPhp($periodStart/*$periodStart*/),
        "<CLOSED_DATE" =>  \Bitrix\Main\Type\DateTime::createFromPhp($currentDateTimeNextDay),
    ],
    "REAL_STATUS" => CTasks::STATE_COMPLETED,
    "RESPONSIBLE_ID" => $userId,
    /*"!UF_CRM_TASK" => false*/
];
$resTaskClosed = CTasks::GetList(
    [
        "DEADLINE" => "DESC",
        "ID" => "DESC"
    ],
    $arFilterTasksClosed,
    [
        "*","UF_*"
    ]
);

$calendarClosedCount = [];
while ($arTask = $resTaskClosed->GetNext()) {
    $deadline = preg_replace('~(.+)\s? .*~', '$1', $arTask['CLOSED_DATE']);
    $calendarClosedCount[$deadline]++;
}

// Считаем не закрытые для календаря
$arFilterTasksUnClosed = [
    [
        "LOGIC" => "AND",
        ">=DEADLINE" => \Bitrix\Main\Type\DateTime::createFromPhp($currentDateTime/*$periodStart*/),
        "<=DEADLINE" =>  \Bitrix\Main\Type\DateTime::createFromPhp($periodEnd),
    ],
    "!REAL_STATUS" => CTasks::STATE_COMPLETED,
    "RESPONSIBLE_ID" => $userId,
    /*"!UF_CRM_TASK" => false*/
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



<form method="get" action="">

    <input type="hidden" name="date" value="<?=$_REQUEST['date'];?>" />
    <div class="flex-control-line">
        <div class="ui-btn ui-btn-light"><?=$arResult['FILTER_DATE'];?></div>
        <?php
        $userList = getSubordinateList();
        if(sizeof($userList) > 1):
        ?>
            <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
                <div class="ui-ctl-after ui-ctl-icon-angle"></div>

                <select name="userId" class="ui-ctl-element">
                <?
                    foreach ($userList as $userItem):

                        ?>
                        <option
                            value="<?=$userItem['ID'];?>"
                            <? if($userItem['ID'] == $userId): ?>selected<? endif ?>
                        >
                            <?=$userItem['NAME']." ".$userItem['LAST_NAME'];?>
                        </option>
                        <?
                    endforeach;

                ?>f
                </select>
            </div>
            <input type="submit" class="ui-btn ui-btn-primary" value="Показать задачи сотрудника" />
            <a href="report" class="ui-btn ui-btn-primary">Отчет по сотрудникам</a>
        <?php endif; ?>
        <div class="ui-btn ui-btn-success birthday-list-" onclick="BX.SidePanel.Instance.open('/crm/birthdayList/index.php?date=<?=$filterDateTime->format("dm");?>',{width: 900, title:'Дни рождения',allowChangeHistory:false,cacheable:false})">
            Дни рождения <?=$arResult['BIRTHDAY_COUNT'];?>
        </div>
    </div>

</form>

    <div class="header-container">
<div class="calendar">
    <div id="calendar"></div>
    <?php echo RefloorCalendar::getInterval($periodStart->format('n.Y'), $periodEnd->format('n.Y'),[],$calendarUnclosedCount, $calendarClosedCount); ?>
</div>

        <div class="notify-list">

<?php
global $USER;
$curUserId = $USER->GetID();
if($userId == $curUserId) {
$pathTemplateTaskEntryView = COption::GetOptionString("tasks", "paths_task_user_entry");

$res = CTasks::GetList(['DEADLINE'=>'ASC'],["RESPONSIBLE_ID"=>$userId,"!CREATED_BY"=>$userId,'STATUS'=>-2]);
$count=0;
while ($arTask = $res->GetNext()) {
    $taskPath = CComponentEngine::MakePathFromTemplate(
            $pathTemplateTaskEntryView,
            [
                'user_id' => $userId,
                'task_id' => $arTask['ID']
            ]
        );

        ?>

    <?php if($count < 3): ?>
        <div class="ui-alert ui-alert-warning" style="cursor: pointer" onclick="BX.SidePanel.Instance.open('<?=$taskPath?>')">
            <span class="ui-alert-message"><strong>Новое поручение на <?=$arTask['DEADLINE']?> от сотрудника <span style="cursor:pointer" bx-tooltip-user-id="<?=$arTask['CREATED_BY']?>"><?=$arTask['CREATED_BY_NAME']?> <?=$arTask['CREATED_BY_LAST_NAME']?></span>:</strong>  <?=$arTask['TITLE']?> </span>
        </div>
        <?php endif ?>

        <?php
        $count++;
    }
    ?>
    <?php if($count > 3): ?>
        <p style="margin-left: 10px;">и еще <?=($count-3)?> непросмотренных поручений</p>
    <?php endif ?>

<?php } ?>
        </div>
    </div>
    <?php $curFilter = $_REQUEST['filter']; if(!$curFilter) { $curFilter='false'; }?>
    <form method="GET">
        <input type="hidden" name="userId" value="<?=$_REQUEST['userId'];?>" />
        <input type="hidden" name="filter" value="<?=$_REQUEST['filter'];?>" />
        <div class="grid-filter">
        <a class="ui-btn ui-btn-sm <?=($curFilter=='false')?'ui-btn-success':''?>" href="<?= $APPLICATION->GetCurPageParam("filter=false",['filter']);?>" >Все</a>
        <a class="ui-btn ui-btn-sm <?=($curFilter=='done')?'ui-btn-success':''?>"
           href="<?= $APPLICATION->GetCurPageParam("filter=done",['filter']);?>">Выполненные</a>
        <a class="ui-btn ui-btn-sm <?=($curFilter=='undone')?'ui-btn-success':''?>"
           href="<?= $APPLICATION->GetCurPageParam("filter=undone",['filter']);?>" >Открытые</a>
        <a class="ui-btn ui-btn-sm <?=($curFilter=='incoming')?'ui-btn-success':''?>"
           href="<?= $APPLICATION->GetCurPageParam("filter=incoming",['filter']);?>"
        >Поручения</a>
        <div class="check-container">
            <input type="checkbox" name="filterImportant" value="Y" <?php if($_REQUEST['filterImportant']):?>checked<?php endif ?>>Важные
        </div>
        <div class="check-container">
            <input type="checkbox" name="filterUrgent" value="Y" <?php if($_REQUEST['filterUrgent']):?>checked<?php endif ?>>Срочные
        </div>
            <div class="check-container">
                <select list="city-list" name="filterCity"  class="ui-ctl-element" type="text" placeholder="Город" />
                    <option value="">Все города</option>
                    <?php
                    $userFieldId = 1708;

                    $obEnum = new CUserFieldEnum();
                    $rsEnum = $obEnum->GetList(
                        [
                            "VALUE" => 'ASC'
                        ],
                        [
                            "USER_FIELD_ID" => $userFieldId,
                            "ID" => $arResult['CITY_LIST']
                        ]
                    );

                    $enum = array();
                    while($arEnum = $rsEnum->Fetch())
                    { ?>
                        <option <?php if($arEnum["ID"]==$_REQUEST['filterCity']): ?>selected<?php endif?>
                                value="<?=$arEnum["ID"];?>"><?=$arEnum["VALUE"];?></option>
                    <? } ?>
                </select>
            </div>
            <div class="check-container">

                <select list="task-types-list" name="filterTaskType"  class="ui-ctl-element" type="text" placeholder="Действие" />
                <option value="">Все действия</option>
                <option value="false" <?php if("false"==$_REQUEST['filterTaskType']): ?>selected<?php endif?>>Прочие дела</option>
                <?php

                foreach ($arResult['TASK_TYPES_LIST'] as $taskTypeId)
                {
                    $actionTitle = $arResult['TASK_TYPES_LIST_ALL'][$taskTypeId]['UF_CODE'].".";
                    $actionTitle .= " ".$arResult['TASK_TYPES_LIST_ALL'][$taskTypeId]['UF_NAME'];
                    ?>
                    <option <?php if($taskTypeId==$_REQUEST['filterTaskType']): ?>selected<?php endif?>
                            value="<?=$taskTypeId;?>"><?=$actionTitle;?></option>
                <? } ?>
                </select>
            </div>
            <div class="check-container">
                <input  class="ui-btn ui-btn-sm" type="submit" value="Отфильтровать"/>
            </div>
        </div>
    </form>
</div>

    <?php $APPLICATION->IncludeComponent(
        'bitrix:main.ui.grid',
        '',
        [
            'GRID_ID' => $arResult['GRID_ID'],
            'COLUMNS' => $arResult['COLUMNS'],
            'ROWS' => $arResult['TASK_LIST'],
            'CURRENT_PAGE' => 1,
            'DEFAULT_PAGE_SIZE' => 20,
            'SHOW_ROW_CHECKBOXES' => true,
            'ALLOW_SORT' => false
        ]
    );
    ?>
</div>
<script type="text/javascript">
    window.BXDEBUG = true;
    BX.ready(() => {
        document.body.addEventListener('click', e => {
            if (e.target.classList.contains('birthday-list')) {
                const taskId = e.target.getAttribute('data-id');
                console.log("pre click!");
                dialog = new BX.CDialog({
                    title: 'Дни рождения',
                    content_url: '/local/apps/birthdayList/index.php?IFRAME=Y',
                    content_post: 'data='+taskId
                }).Show();

                /*BX.SidePanel.Instance.open("/local/task/report/form.php?IFRAME=Y&SIDE=Y&taskId="+taskId);
                BX.addCustomEvent("SidePanel.Slider:onCloseComplete", function(event) {
                    console.log(event.getSlider()); //получить объект слайдера
                    document.getElementById("reloadPage").click();
                });*/
            }
        })
    });
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

