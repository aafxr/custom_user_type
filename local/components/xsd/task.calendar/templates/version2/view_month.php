<?php
/**
 *  Вариант отображения календаря для месяца
 *  @periodNext - копия текущей даты
 *  @periodPrev - копия текущей даты
 */

$periodNext->modify('+1 month');
$periodPrev->modify('-1 month');

$periodStart = clone($periodDateTime);
$periodStart->modify("first day of");
$periodMonthStart = clone($periodStart);

$periodEnd = clone($periodStart);
$periodEnd->modify("+1 month");         // Последний день месяца
$periodEnd->modify("this week");        // Последняя неделя - начало
$periodEnd->modify('+7 days');          // Последняя неделя - последний день (может быть следующий месяц)

$periodStart->modify("this week");      // Первая неделя - первый день (может быть предыдущий месяц)

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($periodStart, $interval, $periodEnd);
?>


<div class="period month">
    <? // Show Month header ?>
    <? for($i=1;$i<=7;$i++):?>
    <div class="day weekday-header text-center
            <? if($i == date("N") ):?>current<? endif ?>
            <? if($i > 5):?>weekend<?endif;?>
        ">
        <? echo $weekDays[$i]; ?>
    </div>
    <?endfor;?>


    <?
    /** Show Month days
    */

    foreach ($period as $dt):

        $arClass = ["day"];

        $currentPeriod = false;
        if( $dt->format("Y-m-d") == date("Y-m-d") ) {
            $currentPeriod = true;
            $arClass[] = "current";
        }

        if($dt->format("N") > 5) {
            $weekend = true;
            $arClass[] = "weekend";
        }

        if($periodMonthStart !== false && $dt < $periodMonthStart) {
            $arClass[] = "day-before";
        }
    ?>
    <div  data-date="<?=$dt->format("d.m.Y");?>" class="<?=implode(" ",$arClass);?>">
        <a href="/company/personal/user/<?=$USER->getId();?>/tasks/task/edit/0/?DEADLINE=<?=$dt->format("d.m.Y 19:00");?>" class="day-title day-title-month"  >
            <?=$dt->format("j");?>
        </a>
        <div class="task-list">
            <div class="load">&nbsp;</div>
        </div>
    </div>

    <? endforeach; ?>
</div>


