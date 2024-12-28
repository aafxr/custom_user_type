<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$APPLICATION->SetAdditionalCSS("/bitrix/js/ui/buttons/src/css/ui.buttons.css");
?>

<?
$APPLICATION->SetTitle("Календарь");

$weekDays = array( 1 => 'Понедельник' , 'Вторник' , 'Среда' , 'Четверг' , 'Пятница' , 'Суббота' , 'Воскресенье' );
$monthNames = array(1=>'Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Августь','Сентябрь','Октябрь','Ноябрь','Декабрь');
$periodType= $_REQUEST['type'] != '' ? $_REQUEST['type'] : 'month';
$period=$_REQUEST['date'] != '' ? $_REQUEST['date'] : date('Y-m-d');
$periodDateTime = new \DateTime($period);
$periodNext = new \DateTime($period);
$periodPrev = new \DateTime($period);
$periodMonthStart = false;
?>
    <div id="calendar-control">
        <div class="period-type-selector">
            <a class="item <?=($periodType=='day' ? 'active' : '');?>" href="?type=day&date=<?=$period?>">День</a>
            <a class="item <?=($periodType=='week' ? 'active' : '');?>" href="?type=week&date=<?=$period?>">Неделя</a>
            <a class="item <?=($periodType=='month' ? 'active' : '');?>" href="?type=month&date=<?=$period?>">Месяц</a>
            <a class="item"  href="?type=<?=$periodType;?>">Текущий</a>
        </div>

        <? if($periodType == 'day'):
            $periodNext->modify('+1 day');
            $periodPrev->modify('-1 day');
            $periodStart = clone( $periodDateTime);
            $periodEnd = clone( $periodDateTime);
            $periodEnd->modify('+1 day');
            ?>
            <div class="period-control">
                <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodPrev->format("Y-m-d");?>"><</a>
                <div class="value"><?=$period?></div>
                <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodNext->format("Y-m-d");?>">></a>
            </div>
        <? endif ?>
        <? if($periodType == 'week'):
            $periodNext->modify('+7 day');
            $periodPrev->modify('-7 day');

            $periodStart = clone($periodDateTime);
            $periodStart->modify("this week");

            $periodEnd = clone($periodStart);
            $periodEnd->modify("+6 days");
            ?>
            <div class="period-control">
                <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodPrev->format("Y-m-d");?>"><</a>
                <div class="value"><?=$periodStart->format("Y-m-d")?>&nbsp;-&nbsp;<?=$periodEnd->format("Y-m-d")?></div>
                <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodNext->format("Y-m-d");?>">></a>
            </div>
        <? endif ?>
        <? if($periodType == 'month'):
            $periodNext->modify('+1 month');
            $periodPrev->modify('-1 month');

            $periodStart = clone($periodDateTime);
            $periodStart->modify("first day of");
            $periodMonthStart = clone($periodStart);

            $periodEnd = clone($periodStart);
            $periodEnd->modify("+1 month");

            $periodStart->modify("this week");

            ?>
            <div class="period-control">
                <div class="period-control">
                    <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodPrev->format("Y-m-d");?>"><</a>
                    <div class="value"><?=$monthNames[ $periodDateTime->format("n")];?></div>
                    <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodNext->format("Y-m-d");?>">></a>
                </div>
            </div>
            <div class="period-description">
                <span><?=$monthNames[ $periodDateTime->format("n")];?> <?=$periodDateTime->format("Y");?></span>
            </div>
        <? endif ?>
    </div>

<?php
// Выбираем задачи, для которых пользователь является ответственным или соисполнителем

$arFilter = array(
    '::LOGIC' => 'AND',
    'CHECK_PERMISSIONS' => 'Y',
    'ONLY_ROOT_TASKS' => 'Y',
    'SAME_GROUP_PARENT' => 'Y',
    '::SUBFILTER-1' => array(
        '::LOGIC' => 'OR',
        '::SUBFILTER-1' => array(
            'ACCOMPLICE' => array($USER->GetID()),
            'REAL_STATUS' => array(CTasks::STATE_NEW, CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
        ),
        '::SUBFILTER-2' => array(
            'RESPONSIBLE_ID' => $USER->GetID(),
            'REAL_STATUS' => array(CTasks::STATE_NEW, CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
        ),
    ),
);
// UF_AUTO_851551329931
$res = CTasks::GetList(
    [   'PRIORITY' => 'DESС', 'REAL_STATUS' => 'ASC', 'CREATED_DATE' => 'ASC'],
    $arFilter,
    [   'TITLE',
        'DESCRIPTION',
        'REPLICATE',
        'DEADLINE',
        'START_DATE_PLAN',
        'END_DATE_PLAN',
        'STAGE_ID',
        'REAL_STATUS',
        'STATUS_COMPLETE',
        'STATUS',
        'NOT_VIEWED',
        'IS_PINNED',
        'IS_MUTED',
        'PRIORITY',
        'UF_*'
    ]
);

$arTasks = [];
while ($arTask = $res->GetNext()) {

    $arTasks[$arTask['ID']] = $arTask;
    /*echo '<pre>';
    print_r($arTask);
    echo '</pre>';*/
}

// TaskTemplates

$res = CTaskTemplates::GetList(
    ["TITLE" => "ASC"],
    ["RESPONSIBLE" => $USER->GetID()],
);

while ($arTemplate = $res->GetNext())
{
    $arTemplate['REPLICATE_PARAMS_AR'] = \Bitrix\Tasks\Util\Type::unSerializeArray($arTemplate['~REPLICATE_PARAMS']);

    /*echo '<pre>';
    print_r($arTemplate);
    echo '</pre>';*/

    $nextTime = false;
    for($i=1;$i<8;$i++) {
        $nextTime = CTasks::getNextTime(unserialize($arTemplate['~REPLICATE_PARAMS'], ['allowed_classes' => false]),false,$nextTime); // localtime

        $taskRepeatTime = new DateTime($nextTime);
        $repeatedTask[$taskRepeatTime->format("Y-m-d")][] = [
            'ID'=>$arTemplate['ID'],
            'TITLE' => $arTemplate['TITLE']
        ];


    }
    /*echo '<pre>';
    print_r($repeatedTask);
    echo '</pre>';*/
}

/*$dateStart = "2022-09-26";
$dateEnd = "2022-10-03";

$begin = new DateTime($dateStart);
$end = new DateTime($dateEnd); */

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($periodStart, $interval, $periodEnd);
?>
    <div class="period <?=$periodType?>">
        <? if($periodType == "month"):?>
            <? for($i=1;$i<=7;$i++):?>
                <div class="day weekday-header text-center
                        <? if($i == date("N") ):?>current<? endif ?>
                        <? if($i > 5):?>weekend<?endif;?>
                    ">
                    <? echo $weekDays[$i]; ?>
                </div>
            <?endfor;?>
        <?endif?>
        <? foreach ($period as $dt): ?>

            <div class="day
                    <? $currentPeriod = false;
                       //$previosPeriod = false;
                       if( $dt->format("Y-m-d") == date("Y-m-d") ):
                           $currentPeriod = true;
                           ?>current<? endif ?>
                    <? if($dt->format("N") > 5):?>weekend<?endif;?>
                    <? if($periodMonthStart !== false && $dt < $periodMonthStart):?>day-before <?endif?>
                ">

        <span class="day-title day-title-<?=$periodType;?>">

            <? if($periodType == "month"):?>
                <?=$dt->format("j");?>
            <? else: ?>
                <? echo $weekDays[$dt->format("N")]; ?><br />
                <? echo $dt->format("Y-m-d"); ?>
            <? endif ?>

        </span>
                <? $dayTask = []; ?>
                <? foreach($arTasks as $arTask):
                    if(!$arTask['DEADLINE']) {
                        $unPlannedTaskList[$arTask['ID']] = $arTask;
                        continue;
                    }
                    $taskDeadLine = new DateTime($arTask['DEADLINE']);
                    $taskDeadLine->setTime(23,59,59);
                    $taskPlanStart = $arTask['START_DATE_PLAN'] ? (new DateTime($arTask['START_DATE_PLAN'])) : (new DateTime($arTask['DEADLINE']));
                    $taskPlanStart->setTime(0,0,0);

                    $longTask = false;
                    if($arTask['START_DATE_PLAN'] && (  $taskDeadLine->format("Y-m-d") != $taskPlanStart->format("Y-m-d"))) {
                        $longTask = true;
                    }


                    ?>
                    <? if( (($taskDeadLine > $dt) && ($taskPlanStart <= $dt)) ):
                    $order = 4;
                    $strMark = $arTask['UF_AUTO_851551329931'];
                    if($strMark == "срочная, важная") $order = 1;
                    if($strMark == "важная, не срочная") $order = 2;
                    if($strMark == "срочная, не важная") $order = 3;
                    ?>
                    <div draggable="true" class="task order-<?=$order?> <?=$longTask ? 'long' : '' ?>">
                        <a href="/company/personal/user/<?=$USER->GetID()?>/tasks/task/view/<?=$arTask['ID'];?>/"><?=$arTask['TITLE'];?></a>
                        <div class="user-info">
                            <div class="userpic"></div>
                            <span>&nbsp;>&nbsp;</span>
                            <div class="userpic"></div>
                        </div>
                    </div>
                    <? endif ?>

                <? endforeach ?>





                <? foreach($repeatedTask[$dt->format("Y-m-d")] as $rTask):?>
                    <div class="task">
                        <a href="/company/personal/user/<?=$USER->GetID()?>/tasks/templates/template/view/<?=$rTask['ID'];?>/">
                        <img class="repeat-task" src="/images/repeat.png" />
                        <?=$rTask['TITLE'];?>
                        </a>
                    </div>
                <? endforeach ?>

                <? foreach($arTasks as $arTask):
                    $taskDeadLine = new DateTime($arTask['DEADLINE']);
                    $taskDeadLine->setTime(23,59,59);

                    ?>
                    <? if(($taskDeadLine < $dt) && $currentPeriod):?>
                        <div draggable="true" class="task order-9 task-die">
                            <a href="/company/personal/user/<?=$USER->GetID()?>/tasks/task/view/<?=$arTask['ID'];?>/"><?=$arTask['TITLE'];?></a>
                            <div class="user-info">
                                <div class="userpic"></div>
                                <span>&nbsp;>&nbsp;</span>
                                <div class="userpic"></div>
                            </div>
                        </div>
                    <? endif ?>
                <? endforeach; ?>

            </div>
        <? endforeach ?>
    </div>

<div>
    <h5>Задачи без крайнего срока</h5>
    <? foreach($unPlannedTaskList as $arTask): ?>
    <div draggable="true" class="task">
        <a href="/company/personal/user/<?=$USER->GetID()?>/tasks/task/view/<?=$arTask['ID'];?>/"><?=$arTask['TITLE'];?></a>

    </div>
    <? endforeach ?>
</div>


    <style>
        .period {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 2px;
            margin: 15px 0;
        }
        .day {
            flex: 1;
            background: #e7f0ff;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 4px;
        }
        .day.current {
            background: #b1cff9;

        }
        .day-title {
            color: #777;
            font-weight: bolder;
        }
        .day.current .day-title {
            color: #333;
        }
        .day.weekend {
            background: #fafafa;
        }
        .day.day-before {
            opacity: 0.4;
        }
        .task.order-1 { order: 1; border-left: #e75151 8px solid;}
        .task.order-2 { order: 2; border-left: #7fbd66 8px solid;}
        .task.order-3 { order: 3; border-left: #60a1fb 8px solid;}
        .task.order-4 { order: 4; border-left: #eaeaea 8px solid;}
        .task.order-9 { order: 9; border-left: red 8px solid; background: #ffd7d7;}
        .task {
            filter: drop-shadow(2px 2px 1px #3332);
            background: white;
            border-radius: 0 4px 4px 0;
            padding: 4px;
            margin-left:0px;
            margin-top: 0px;
            margin-right: 1px;
            margin-bottom: 1px;
            transition: 0.1s;
            border-left: #fff 8px solid;

        }
        .task:hover {
            margin-left:1px;
            margin-top: 1px;
            margin-right: 0px;
            margin-bottom: 0px;
            filter:drop-shadow(0px 0px 0px #333f);
        }
        .task.long {
            background: #fdfdd5;
        }
        .repeat-task {
            width: 1rem;
            height: 1rem;
        }
        .task a {
            font-family: "OpenSans-Semibold","Helvetica Neue",Arial,Helvetica,sans-serif;
            color: #333;
            font-size: 13px;

            line-height: 16px;
            display: block;
        }
        .user-info {
            display: flex;
            flex-direction: row;
            margin: 6px 0 2px;

        }
        .userpic {
            width: 1rem;
            height: 1rem;
            border-radius: 50%;

            background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB2aWV3Qm94PSIwIDAgNDAgNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8ZyBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyBpZD0iaWNvbiI+CiAgICAgICAgICAgIDxjaXJjbGUgaWQ9Ik92YWwiIGZpbGw9IiM3Qjg2OTEiIGZpbGwtcnVsZT0ibm9uemVybyIgY3g9IjIwIiBjeT0iMjAiIHI9IjIwIj48L2NpcmNsZT4KICAgICAgICAgICAgPHBhdGggZD0iTTE3LjEyODQwNjksOS4zMDg1NTg3IEMxNi4yMjIyODk0LDcuODcyODY5NyAyMy44NjE1MzA0LDYuNjgwNDc0MTEgMjQuMzY4NzQ5OCwxMS4wNzQzNzAyIEMyNC41NjgxOTg5LDEyLjM5ODI5OSAyNC41NjgxOTg5LDEzLjc0NTQzOTUgMjQuMzY4NzQ5OCwxNS4wNjkzNjgzIEMyNC4zNjg3NDk4LDE1LjA2OTM2ODMgMjUuNTEwNDIzNSwxNC45Mzg2OTQ4IDI0Ljc0ODczNDYsMTcuMTA4NTYyNSBDMjQuNzQ4NzM0NiwxNy4xMDg1NjI1IDI0LjMyOTIwMzksMTguNjY5NzY2OCAyMy42ODQ0MzM0LDE4LjMxOTAxMTYgQzIzLjY4NDQzMzQsMTguMzE5MDExNiAyMy43ODkzMTYxLDIwLjI5Mjg2OTEgMjIuNzc0ODc3MiwyMC42MjcyOTAxIEMyMi43NzQ4NzcyLDIwLjYyNzI5MDEgMjIuODQ3MDkxNCwyMS42Nzg2OTU4IDIyLjg0NzA5MTQsMjEuNzUwMDUwNCBMMjMuNjk0NzQ5NywyMS44NzY0MjU1IEMyMy42OTQ3NDk3LDIxLjg3NjQyNTUgMjMuNjY4OTU4OSwyMi43NTMzMTM0IDIzLjgzODMxODYsMjIuODQ3ODc5NyBDMjQuNjEyMDQzMywyMy4zNDY1MDIyIDI1LjQ1OTcwMTYsMjMuNzI1NjI3MyAyNi4zNTEyMDQyLDIzLjk2OTc4MDQgQzI4Ljk4MzU4NzMsMjQuNjM3NzYyNiAzMC4zMTk1NTE4LDI1Ljc4MzczNDcgMzAuMzE5NTUxOCwyNi43ODc4NTc0IEwzMS4wMjcwOCwzMC4zODkxMTU2IEMyNy45ODAzMjQ0LDMxLjY2NTc2MTIgMjQuNDQ0NDAyOSwzMi40MjkxNjk1IDIwLjY2MzQ2ODcsMzIuNTA5MTIxIEwxOS4zNDIxMTksMzIuNTA5MTIxIEMxNS41Njg5MjE5LDMyLjQyOTE2OTUgMTIuMDQwNzM3NywzMS42NjkyIDksMzAuMzk3NzEyNSBDOS4xMzg0MTA3NCwyOS40MDEzMjcxIDkuMzE4OTQ2NDgsMjguMjc0MjY4MyA5LjUwNTUwMDA4LDI3LjU0NzgyNjkgQzkuOTA2MTE3NDksMjUuOTg2NjIyNSAxMi4xNTkzNzU1LDI0LjgyNjg5NTMgMTQuMjMyMDk3OCwyMy45MzUzOTI2IEMxNS4zMDQ5OTU5LDIzLjQ3MzczNjkgMTUuNTM3MTEzMywyMy4xOTYwNTU4IDE2LjYxNjg4ODksMjIuNzI0MDgzOCBDMTYuNjc3MDY3NSwyMi40MzY5NDYgMTYuNzAxMTM5LDIyLjE0MjkzMDYgMTYuNjg5MTAzMiwyMS44NDk3NzQ5IEwxNy42MDcyNTY0LDIxLjc0MDU5MzggQzE3LjYwNzI1NjQsMjEuNzQwNTkzOCAxNy43Mjc2MTM2LDIxLjk1OTgxNTggMTcuNTM0MTgyNSwyMC42NzAyNzQ4IEMxNy41MzQxODI1LDIwLjY3MDI3NDggMTYuNTAyNTQ5NiwyMC40MDI5MDk5IDE2LjQ1NDQwNjgsMTguMzQ5MTAwOSBDMTYuNDU0NDA2OCwxOC4zNDkxMDA5IDE1LjY3ODk2MjgsMTguNjA3MDA5MSAxNS42MzI1MzkzLDE3LjM2MzAzMTkgQzE1LjU5OTAxMTIsMTYuNTIwNTMxOCAxNC45Mzc5MDY1LDE1Ljc4ODA3MjUgMTUuODg5NTg3OCwxNS4xODE5ODgyIEwxNS40MDQ3MjA0LDEzLjg5MDcyNzggQzE1LjQwNDcyMDQsMTMuODkwNzI3OCAxNC44OTU3ODE1LDguOTA0NTAyNTIgMTcuMTI4NDA2OSw5LjMwODU1ODcgTDE3LjEyODQwNjksOS4zMDg1NTg3IFoiIGlkPSJQYXRoIiBmaWxsPSIjRkZGRkZGIj48L3BhdGg+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=);
        }
        .period-description {
            display: flex;
            align-items: center;
            justify-content: end;
            flex: 1;
            font-size: 2rem;
            color: #333;
        }
    </style>

    <script type="text/javascript">
        console.log("start");

    </script>

    <style>
        #calendar-control {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }
        .period-type-selector {
            display: flex;
            flex-direction: row;
            gap: 10px;
        }
        .period-type-selector .item {
            padding: 10px 15px;
            background: #eaf6ff;
            color: #333;
        }
        .period-type-selector .active {
            background: #b2e421;
            color: #535c69;
            
        }

        .period-control {
            display: flex;
            flex-direction: row;
        }
        .period-control .btn {
            padding: 10px 15px;
            background: #eaf6ff;
            color: #333;
        }
        .period-control .value {
            padding: 10px 15px;
            background: #d3e1ed;
            color: #333;
        }

        .period.month .day {
            min-width: calc(100% / 6 - 10px);
            max-width: calc(100% / 6 - 10px);
        }
        .period.month .day.weekend {
            min-width: calc(100% / 12 - 10px);
            max-width: calc(100% / 12 - 10px);
        }

        .period.month .day .user-info {
            display: none;
        }
        .period.month .day a,.period.month .day .task {
            font-size: 11px;
            line-height: 12px;
        }
        .day-title.day-title-month {
            display: flex;
            justify-content: end;
            color: #939aa1;
        }
        .weekday-header {
            font-size: 10px;
            font-weight: 600;
            text-align: center;
        }
        .pagetitle-inner-container {
            display: flex;
        }
    </style>
<?php
$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleFlexibleSpace = "lists-pagetitle-flexible-space";
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
    $this->SetViewTarget("inside_pagetitle");
}

?>
    <div class="pagetitle-container pagetitle-flexible-space <?=$pagetitleFlexibleSpace?>">
        <div class="ui-btn-split ui-btn-success tasks-interface-filter-btn-add" style="margin-left:1rem">
            <a class="ui-btn-main" id="tasks-buttonAdd" href="/company/personal/user/<?=$USER->GetID()?>/tasks/task/edit/0/">Добавить задачу</a>
        </div>
    </div>
    <div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
        2
    </div>
<?
if($isBitrix24Template)
{
    $this->EndViewTarget();
}
?>

<script type="text/javascript">
    console.log("js start...");
    BX.addCustomEvent("SidePanel.Slider:onCloseComplete", function(event) {
        console.log(event.getSlider()); //получить объект слайдера
        location.reload();
    });
</script>

