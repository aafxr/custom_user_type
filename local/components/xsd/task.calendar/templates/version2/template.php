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

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

$APPLICATION->SetAdditionalCSS("/bitrix/js/ui/buttons/src/css/ui.buttons.css");
$APPLICATION->SetTitle("Календарь регламент");

global $APPLICATION;

$APPLICATION->SetAdditionalCSS("/bitrix/js/tasks/css/tasks.css");
Extension::load([
    'ui.counter',
    'ui.entity-selector',
    'ui.icons.b24',
    'ui.label',
    'ui.tour',
]);
?>
<?php

$weekDays = array( 1 => 'Понедельник' , 'Вторник' , 'Среда' , 'Четверг' , 'Пятница' , 'Суббота' , 'Воскресенье' );
$monthNames = array(1=>'Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Августь','Сентябрь','Октябрь','Ноябрь','Декабрь');

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
        <a class="ui-btn-main" id="tasks-buttonAdd" style="margin-left:1rem" href="/company/personal/user/<?=$USER->GetID()?>/tasks/templates/template/edit/0/?replication=Y">Добавить регулярную задачу</a>
    </div>

</div>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
    <div class="task-interface-toolbar">
        &nbsp;
    </div>
</div>
<?
if($isBitrix24Template)
{
    $this->EndViewTarget();
}
$APPLICATION->IncludeComponent(
    'bitrix:tasks.interface.header',
    'reglament',[]);
?>

<?php
    $periodType = $_REQUEST['type'] != '' ? $_REQUEST['type'] : ($_COOKIE['RF_CALENDAR_PERIOD'] ? $_COOKIE['RF_CALENDAR_PERIOD'] : 'month');
    if($_REQUEST['type']) {
        setcookie('RF_CALENDAR_PERIOD',$_REQUEST['type']);
    }
    $period=$_REQUEST['date'] != '' ? $_REQUEST['date'] : date('Y-m-d');
    $periodDateTime = new \DateTime($period);
    $periodNext = new \DateTime($period);
    $periodPrev = new \DateTime($period);
?>
<div id="calendar-control">
    <? if($periodType == 'week'):
        $periodNext->modify('+1 week');
        $periodPrev->modify('-1 week');

        $periodStart = clone($periodDateTime);
        $periodStart->modify("this week");
        $periodMonthStart = clone($periodStart);

        $periodEnd = clone($periodStart);
        $periodEnd->modify("+7 days");

        $periodStart->modify("this week");

        ?>
        <div class="period-control">
            <div class="period-control">
                <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodPrev->format("Y-m-d");?>"><</a>
                <div class="value"><?=$periodStart->format("d/m/Y");?>&nbsp;-&nbsp;<?=$periodEnd->format("d/m/Y");?></div>
                <a class="btn" href="?type=<?=$periodType;?>&date=<?=$periodNext->format("Y-m-d");?>">></a>
            </div>
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
    <? endif ?>

    <? if($periodType == 'month'): ?>
        <div class="period-description">
            <span><?=$monthNames[ $periodDateTime->format("n")];?> <?=$periodDateTime->format("Y");?></span>
        </div>
    <? endif ?>
</div>
<div id="calendar-period">
<?php
        include "view_" . $periodType . ".php";
?>
</div>

<div id="result"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js" type="text/javascript"></script>

<!--<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>-->
<?php
$showResponsible = $_REQUEST['showResponsible'] != '' ? $_REQUEST['showResponsible'] : ($_COOKIE['RF_CALENDAR_RESPONSIBLE'] ? $_COOKIE['RF_CALENDAR_RESPONSIBLE'] : 'Y');
$showCreator = $_REQUEST['showCreator'] != '' ? $_REQUEST['showCreator'] : ($_COOKIE['RF_CALENDAR_CREATOR'] ? $_COOKIE['RF_CALENDAR_CREATOR'] : 'N');
$showAuditor = $_REQUEST['showAuditor'] != '' ? $_REQUEST['showAuditor'] : ($_COOKIE['RF_CALENDAR_AUDITOR'] ? $_COOKIE['RF_CALENDAR_AUDITOR'] : 'N');
$showDone = $_REQUEST['showDone'] != '' ? $_REQUEST['showDone'] : ($_COOKIE['RF_CALENDAR_SHOW_DONE'] ? $_COOKIE['RF_CALENDAR_SHOW_DONE'] : 'Y');
if(($showResponsible == 'N') && ($showCreator == 'N') && ($showAuditor == 'N')) {
    $showResponsible = 'Y';
}
?>
<script type="text/javascript">
    console.log("ajax");
    function updateTaskList() {

        let periodStart = '<?=$periodStart->format("Y-m-d");?>';
        let periodEnd = '<?=$periodEnd->format("Y-m-d");;?>';

        $.ajax({
            type: "POST",
            dataType: "json",
            data: {periodStart: periodStart, periodEnd: periodEnd, showResponsible: '<?=$showResponsible;?>',showCreator: '<?=$showCreator;?>',showAuditor : '<?=$showAuditor;?>',showDone : '<?=$showDone;?>'},
            url: "<?=$componentPath?>/ajax-get-task.php",

            success: function(data) {
                //$('#result').html(data.toString());
                //let res = $.parseJSON(data);

                console.log("R:");
                console.dir(data['TEMPLATE_TASKS']);
                $(".load").remove();
                $(".task").addClass("oldTask");

                if(data.status == 'ok') {
                    // расставляем обычные задачи

                    console.dir(data);

                    for (let key in data.tasks) {
                        let task = data.tasks[key];

                        if(task.DEADLINE != null) {
                            // Если задан deadline

                            let taskClassName = "task ";
                            let taskOrder = 4;
                            let taskMark = task.UF_AUTO_851551329931;
                            if(taskMark == 'срочная, важная') {taskOrder = 1}
                            if(taskMark == 'важная, не срочная') {taskOrder = 2}
                            if(taskMark == 'срочная, не важная') {taskOrder = 3}

                            if(task.DIE) {
                                taskOrder = "die";
                                taskClassName += "task-die ";
                            }
                            if(task.LONG) {
                                taskClassName += "long ";
                                console.dir(task);
                            } else {
                                taskClassName += "deadline ";
                            }
                            if(task.DONE) {
                                taskClassName += "done ";
                            } else {
                                taskClassName += "inprogress ";
                            }


                            taskClassName += "order-"+taskOrder+" ";


                            if($("#task"+task.ID).length > 0) {
                                // если задача есть в списке
                                let taskObj = $("#task"+task.ID).remove();
                                taskObj.appendTo(".day[data-date='" + task.DEADLINE_DATE + "'] .task-list");
                                document.getElementById("task"+task.ID).className = taskClassName;
                                $("#task"+task.ID+" .title").html(task.TITLE);
                                console.log("upd"+task.ID+",set="+taskClassName);
                            } else {

                                // если нет - добавляем

                                let taskUrl = '/company/personal/user/<?=$USER->GetID()?>/tasks/task/view/'+task.ID+'/';

                                let taskUserPics = '';
                                if(task.CREATER_USER_PIC != undefined) {
                                    taskUserPics += '<div class="userpic" title="'+task.CREATER_USER_TITLE+'" style="background-image:url(' + task.CREATER_USER_PIC + ')" ></div>';
                                } else {
                                    taskUserPics += '<div class="userpic" title="'+task.CREATER_USER_TITLE+'"></div>';
                                }
                                taskUserPics += '<span>></span>';
                                if(task.RESPONSIBLE_USER_PIC != undefined) {
                                    taskUserPics += '<div class="userpic" title="'+task.RESPONSIBLE_USER_TITLE+'" style="background-image:url(' + task.RESPONSIBLE_USER_PIC + ')"></div>'
                                } else {
                                    taskUserPics += '<div class="userpic" title="'+task.RESPONSIBLE_USER_TITLE+'" ></div>';
                                }
                                taskUserPics = "<div class='task-users'>"+taskUserPics+"</div>";

                                let taskHtml = '<a draggable="true" href="'+taskUrl+'"><span class="title">'+task.TITLE+'</span>'+taskUserPics+'</a>';

                                if(task.LONG) {
                                    console.log("TASK_LONG");
                                    console.dir(task);
                                    let copyIndex = 1;
                                    task.DEADLINE_LONG.forEach(function(longDeadLine) {
                                        $("#task" + task.ID + "-"+copyIndex).remove();
                                        if(longDeadLine == task.DEADLINE_DATE) {
                                            taskClassName += " deadline";
                                        }
                                        $(".day[data-date='" + longDeadLine + "'] .task-list").append("<div id='task" + task.ID + "-"+copyIndex+"'  data-task-id='"+task.ID+"'  class='"+taskClassName+"'><span class='mark'></span>" + taskHtml + "</div>");
                                        copyIndex++;
                                    })
                                } else {
                                    $(".day[data-date='" + task.DEADLINE_DATE + "'] .task-list").append("<div id='task" + task.ID + "' data-task-id='"+task.ID+"'  class='"+taskClassName+"'><span class='mark'></span>" + taskHtml + "</div>");
                                }
                            }

                            $("#task"+task.ID).removeClass("oldTask");

                        } else {
                            // @todo если дедлайн не задан
                        }
                    }

                    for (let key in data.templateTasks) {

                        let templateTask = data.templateTasks[key];
                        console.dir(templateTask);
                        let templateTaskClassName = "task ";
                        let templateTaskOrder = 4;

                        let taskOrder = 4;
                        let taskTemplateMark = templateTask.PRIORITY;
                        if(taskTemplateMark == 'срочная, важная') {taskOrder = 1}
                        if(taskTemplateMark == 'важная, не срочная') {taskOrder = 2}
                        if(taskTemplateMark == 'срочная, не важная') {taskOrder = 3}


                        templateTaskClassName += "order-"+taskOrder+" ";

                        if($("#task"+templateTask.ID).length > 0) {
                            $("#task"+templateTask.ID).remove();
                        }

                        let templateTaskUrl = '/company/personal/user/<?=$USER->GetID()?>/tasks/templates/template/view/'+templateTask.TEMPLATE_ID+'/';
                        let templateTaskHtml  = '<a  href="'+templateTaskUrl+'"><span class="title"><img class="repeat-task" src="/images/repeat.png" />'+templateTask.TITLE+'</span></a>';

                        $(".day[data-date='" + templateTask.DEADLINE_DATE + "'] .task-list").append("<div id='task" + templateTask.ID + "'   class='"+templateTaskClassName+"'><span class='mark'></span>" + templateTaskHtml + "</div>");


                        $("#task"+templateTask.ID).removeClass("oldTask");
                    }

                    //  Удаляем не обновленные
                    $(".oldTask").remove();
                    $(".deadline.inprogress").draggable({
                        opacity: 0.75,
                        revert: true,
                        revertDuration: 0,
                        stack: ".task"
                    });
                    $(".day").droppable({
                        drop: function( event, ui ) {

                           let taskId = ui.draggable.attr("data-task-id");
                           let newDate = $(this).attr("data-date");
                            console.log("taskId="+taskId+" moveTo="+newDate);

                            console.log("drop");
                           //$(this).append("<p>переносится, но скрипт не доделан</p>");
                           ui.draggable.detach().appendTo($(this).find(".task-list"));

                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                data: {taskId: taskId, newDate: newDate},
                                url: "<?=$componentPath?>/ajax-move-task.php",
                                success: function(data) {
                                    console.log("after ajax move task");
                                    console.dir(data);
                                    updateTaskList();
                                }
                            });


                        }
                    });
                } else {
                    console.log("fail");
                }



            }
        });

        
    }
    updateTaskList();

    BX.addCustomEvent("SidePanel.Slider:onCloseComplete", function(event) {
        console.log(event.getSlider()); //получить объект слайдера
        updateTaskList();
    });

    $(document).ready( function () {
        console.log("ready...");
        let currentDraggable = false;
    })

</script>