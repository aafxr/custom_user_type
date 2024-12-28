<?php


define("UF_FIELD_RESULT", "UF_AUTO_280393729397");
define("UF_FIELD_SUCCESS", "UF_AUTO_251545709641");
define("UF_FIELD_TIME", "UF_TASK_TIME");
\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.buttons");

$btnOkTitle = $arResult['COMPLETED'] ?  'Завершить' : 'Обновить';
$btnProcessTitle = $arResult['COMPLETED'] ? 'Завершение..' : 'Обновление..' ;

global $USER;
$userId = $USER->GetID();
?>

<!-- .ui-alert.ui-alert-warning-->
<div id="alert" class="alert-container"></div>

<div class="ui-form">

    <?php if($arResult['COMPLETED'] != 'Y'): ?>
        Желаемый результат: <?=TxtToHTML($arResult['TASK']['DESCRIPTION']);?>
    <?else : ?>
        Задача закрыта
    <?php endif ?>

    <div class="ui-form-row">
        <div class="ui-form-label">
            <div class="ui-ctl-label-text">Результат: </div>
        </div>
        <div class="ui-form-content">
            <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
                <? /* <div class="ui-ctl-tag">Новый</div> */ ?>
                <textarea id="taskResult"
                          type="text"
                          class="ui-ctl-element refloor-textarea refloor-textarea-result"

                ><?=$arResult['TASK'][UF_FIELD_RESULT];?></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <label class="ui-ctl ui-ctl-checkbox">
                <input id="taskSuccess"
                       class="ui-ctl-element"
                    <?=(($arResult['TASK'][UF_FIELD_SUCCESS] != 0) || ($arResult['COMPLETED'] != 'Y')) ? 'checked' : '';?>
                       type="checkbox" />
                <div class="ui-ctl-label-text">Цель достигнута</div>

            </label>
        </div>
        <div class="col-4">
            <?php if($arResult['COMPLETED'] != 'Y'): ?>
                <label class="ui-ctl ui-ctl-checkbox">
                    <input id="taskClosePrevDate"
                           class="ui-ctl-element"
                           type="checkbox" />
                    <div class="ui-ctl-label-text">Закрыть вчерашней датой</div>
                </label>
            <?endif ?>
        </div>
        <?php if($arResult['COMPLETED'] != 'Y'): ?>
        <div class="col-4">
            <span id="taskTimeLabel"></span><br />
            <input id="taskTime" type="range" min="1" max="12" value="1"/>
        </div>
        <?endif ?>
    </div>


    <?php if($arResult['COMPLETED'] != 'Y'): ?>
        <div class="next-task-container">
            <div class="ui-form-row">
                <label>Запланировать далее:</label>
                <?
                    $oldType = $arResult['TASK']['UF_AUTO_274474131393'];
                    $selected = false;
                ?>
                <select class="form-control" id="taskNextTypeId">
                    <option value="-1">Не планировать дальнейщую работу</option>
                    <?php // if(!CSite::InGroup([21])): ?>
                    <?php foreach($arResult['TASK_TYPES'] as $taskTypeId => $taskTypeItem) {
                        ?>
                        <option <? if(!$selected &&
                                      (($taskTypeItem['ID'] > $oldType) || ($taskTypeItem['ID'] == 177)) &&
                                      ($taskTypeId != 288) && ($taskTypeId != 289)
                                ) { $selected = true; ?>selected<? } ?>
                                value="<?=$taskTypeItem['ID'];?>">
                            <?=$taskTypeItem['UF_CODE'];?> <?=$taskTypeItem['UF_NAME'];?>
                        </option>
                        <?php
                    } ?>
                    <? //endif ?>
                </select>
            </div>

            <div id="taskNextDetails">
                <div class="row mb-2">
                    <div class="col-4">
                        <label>Срок:</label>
                        <input class="ui-ctl-element"
                               type="date"
                               id="taskNextDeadLine"
                               value="<?=date("Y-m-d", strtotime('+3 days'));?>"
                        />
                    </div>
                    <div class="col-4 d-flex align-items-center">
                        <br />
                        <input id="taskTypeImportant" type="checkbox" />Важная задача
                    </div>
                    <div class="col-4 d-flex align-items-center">
                        <br />
                        <input id="taskTypeUrgent" type="checkbox" />Срочная задача
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-6">
                        <label>Сотрудник:</label>
                        <select class="form-control" id="taskNextUser">
                            <? foreach($arResult['USERS'] as $arUser): ?>
                                <option value="<?=$arUser['ID']; ?>" <? if($arUser['ID'] == $userId): ?>selected<? endif ?> ><?=$arUser['LAST_NAME'];?>&nbsp;<?=$arUser['NAME'];?></option>
                            <? endforeach ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label>Контактное лицо:</label>
                        <select id="taskNextContact"
                                class="ui-ctl-element" >
                            <option value="-1">-</option>
                            <? foreach($arResult['CONTACTS'] as $arContact): ?>
                                <option value="<?=$arContact['ID']; ?>">
                                    <?=$arContact['NAME']; ?>&nbsp;<?=$arContact['LAST_NAME']; ?>
                                </option>
                            <? endforeach ?>
                        </select>
                    </div>

                </div>


                <div>
                    <label>Желаемый результат:</label>
                    <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
                    <textarea id="taskNextDesc"
                              class="ui-ctl-element refloor-textarea"

                              name="taskNextDesc"
                              placeholder="Впишите сюда что требуется сделать"></textarea>
                    </div>
                </div>

            </div>
        </div>


    <?php endif ?>

</div>

<script type="text/javascript">

    var currentWindow = BX.WindowManager.Get();
    currentWindow.SetTitle('<?=$arResult['TASK']['TITLE']?>');

    if(!!document.getElementById("taskTime")) {
        function updateTimeLabel() {
            let v = document.getElementById("taskTime").value;
            let t = v * 20;
            let h = Math.floor(t / 60);
            let m = t % 60;
            let label = m + " мин";
            if (h > 0) {
                label = h + " ч " + label;
            }
            label = "Затрачено " + label;

            document.getElementById("taskTimeLabel").innerText = label;
        }

        document.getElementById("taskTime").addEventListener("change", updateTimeLabel)
        updateTimeLabel();
    }

    if(typeof inUpdate === 'undefined') {
        var inUpdate = false;

        <?php /*fetch('/local/task/handlers/closeTask.php', {
            method: 'POST', // Здесь так же могут быть GET, PUT, DELETE
            body: JSON.stringify(dataTaskBP), // Тело запроса в JSON-формате
            headers: {
                // Добавляем необходимые заголовки
                'Content-type': 'application/json; charset=UTF-8',
            },
        }).then((response) => response.json())
            .then((data) => {
                if (data.ok) {

         */ ?>
    } else {
        inUpdate = false;
    }

    // Next Task Details selector
    if(!!document.getElementById('taskNextTypeId')) {
        document.getElementById("taskNextTypeId").addEventListener("change", function () {
            console.log("next type change event");
            const v = document.getElementById("taskNextTypeId").value;
            if (v == -1) {
                document.getElementById("taskNextDetails").classList.add("hidden")
            } else {
                document.getElementById("taskNextDetails").classList.remove("hidden")
            }
        })
        const v = document.getElementById("taskNextTypeId").value;
        if (v == -1) {
            document.getElementById("taskNextDetails").classList.add("hidden")
        } else {
            document.getElementById("taskNextDetails").classList.remove("hidden")
        }
    }

    function updateTask() {

        let taskResult = document.getElementById('taskResult').value;
        if(taskResult.length < 2) {
            alert('Впишите результат выполнения задачи');
            /*
            var winds = new BX.CDialog({
                'title': 'Предупреждение',
                'content': 'Впишите результат выполнения задачи',
                'width' : '700px',
                'height' : '250px',
                'min-width' : '500px',
                'min-height' : '150px',
                'draggable': true,
                'resizable': true,
                'buttons': [ BX.CDialog.btnClose]
            });

            winds.Show();*/
            return;
        }

        if(!inUpdate) {
            inUpdate = true;
            document.getElementById('adm-btn-popup-save').value = "<?=$btnProcessTitle;?>";

            let taskNextTypeId = !!document.getElementById('taskNextTypeId') ? document.getElementById('taskNextTypeId').value : -1;



            let dataTaskBP = {
                'taskId' : <?=$arResult['TASK']['ID'];?>,
                'fields' : {
                    '<?=UF_FIELD_RESULT?>'  : document.getElementById('taskResult').value ,
                    '<?=UF_FIELD_SUCCESS?>' : document.getElementById('taskSuccess').checked,

                },
                <?php if($arResult['COMPLETED'] != 'Y'): ?>
                'taskClosePrevDate' : document.getElementById('taskClosePrevDate').checked,
                'taskNextTypeId' : parseInt(taskNextTypeId)
                <?endif ?>
            };
            if(!!document.getElementById("taskTime")) {
                dataTaskBP['fields']['<?=UF_FIELD_TIME?>'] = document.getElementById('taskTime').value;
            }


            if(taskNextTypeId > -1) {
                dataTaskBP['nextTask'] = {
                    'deadLine'  : document.getElementById("taskNextDeadLine").value,
                    'description' : document.getElementById("taskNextDesc").value,
                    'contact'   : document.getElementById("taskNextContact").value,
                    'user'      : document.getElementById("taskNextUser").value,
                    'important' : document.getElementById("taskTypeImportant").checked,
                    'urgent'    : document.getElementById("taskTypeUrgent").checked
                }
            }


            fetch('<?=$arResult['COMPONENT_PATH']; ?>/ajax.php', {
                method: 'POST', // Здесь так же могут быть GET, PUT, DELETE
                // Тело запроса в JSON-формате
                body: JSON.stringify(dataTaskBP),
                headers: {
                    // Добавляем необходимые заголовки
                    'Content-type': 'application/json; charset=UTF-8',
                },
            })
            .then((response) => response.json())
            .then((data) => {
                    console.log("response");
                    console.log(data)
                    if(data.ok) {
                       
                        let refloorRefresh = document.getElementById("refloor-refresh")
                        if(!!refloorRefresh) {
                            document.getElementById("refloor-refresh").click();
                        }


                        BX.WindowManager.Get().Close();
                    } else {
                        var myAlert = new BX.UI.Alert({
                            text: "<strong>Ошибка!</strong> "+data.message,
                            /*inline: true,*/
                            color: BX.UI.Alert.Color.WARNING,
                            icon: BX.UI.Alert.Icon.WARNING,
                            closeBtn: true,
                            animate: true
                        });
                        myAlert.renderTo(document.getElementById("alert"));
                    }
                    inUpdate = false;
                    document.getElementById('adm-btn-popup-save').value = "<?=$btnOkTitle;?>";
                }
            )
            .catch((e) => {
                console.log('Error: ' + e.message);
                console.log(e.response);
            });

            console.dir(dataTaskBP);


        } else {
            console.log("STOP!")
        }
    }

    BX.WindowManager.Get().SetButtons([
        {
            title: '<?=$btnOkTitle;?>',
            className: 'ui-btn ui-btn-success ui-btn-sm',
            id: 'adm-btn-popup-save',
            action: () => updateTask()
        },
        {
            title: 'Закрыть',
            className: 'ui-btn ui-btn-default ui-btn-sm',
            action: function () {
                <? if($arParams['SCROLL'] == 'Y'): ?>
                if (document.cookie.includes(window.location.href)) {
                    if (document.cookie.match(/scrollTop=([^;]+)(;|$)/) != null) {
                        var arr = document.cookie.match(/scrollTop=([^;]+)(;|$)/);
                        document.documentElement.scrollTop = parseInt(arr[1]);
                        document.body.scrollTop = parseInt(arr[1]);
                    }
                }
                <? endif ?>
                BX.WindowManager.Get().Close();
            }
        },
    ]);
</script>
<style type="text/css">
    label {
        display: block;
        margin-bottom: 0.1rem;
    }
    .resize-none {
        resize: none;
    }
    .hidden {
        display: none;
    }
    .next-task-container {
        margin: 8px -16px 0;
        padding: 10px 16px 16px;
        border-radius: 4px;
        border: #dce7ed 1px solid;
        background: #e2e8e8;#e2e8e8
    }
    .refloor-textarea {
        flex: none;
        min-height: 100px;
        overflow-y: auto;
    }
</style>