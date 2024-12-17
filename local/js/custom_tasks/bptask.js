BX.ready(function(){

    let userId = BX.message('USER_ID');// получим текущего пользователя
    console.log("xTASK userId:"+userId);

    //if(userId == 177) {
        let taskId = getTaskID();
        if ( taskId ) {
            const crmField = document.getElementsByClassName('field_crm_entity');
            const crmStatus = document.getElementById('task-detail-status-below-name');
            const taskIsDone = (crmStatus.innerText == "завершена");

            if(crmField.length > 0) {
                console.log("Start task CRM script l=" + crmField.length);
                console.log("xTASK taskId:" + taskId);
                let buttonTaskId = 6;
                let buttonBar = document.getElementById('bx-component-scope-bitrix_tasks_widget_buttonstask_'+buttonTaskId);
                if (buttonBar === null) {
                    console.log("6 in null ");
                    buttonTaskId = 5;
                    buttonBar = document.getElementById('bx-component-scope-bitrix_tasks_widget_buttonstask_'+buttonTaskId);
                    console.dir(buttonBar);
                }
                if (buttonBar !== null) {
                    console.log("Button Bar no null: "+buttonTaskId+" task is done="+taskIsDone);

                    let spans = buttonBar.getElementsByTagName('span');

                    const completeBtnSet = document.getElementsByClassName("task-view-button complete");
                    console.log("BtnSet=" + completeBtnSet.length);

                    if(taskIsDone) {
                        let buttonBar = document.getElementById('bx-component-scope-bitrix_tasks_widget_buttonstask_'+buttonTaskId);
                        //buttonBar.innerHTML += "";
                    } else {
                        showButtons(taskId, userId,buttonTaskId);
                    }
                }

            }
        }
    //}


    let timeLineNewTask = document.getElementById("crm_scope_timeline_c_company__task");
    if(!!timeLineNewTask) {
        console.log("EXIST!!!");
        //let loc = window.location;
        //let id = loc.replace(/\D/g, "");
        //console.log(id);
    }


});


function getTaskID () {
    var entity_xml_id = document.getElementsByName('ENTITY_XML_ID');

    if ( typeof entity_xml_id === 'object' ) {
        if ( entity_xml_id.length > 0 ) {
            return Number(entity_xml_id[0].value.split("_").pop());
        } else {
            return 0
        }
    }
}

function showButtons( task, user ,buttonTaskId) {

    if ( task ) {

        let fd = new FormData();
        fd.append('task', task);
        fd.append('user', user);

        let buttonBar = document.getElementById('bx-component-scope-bitrix_tasks_widget_buttonstask_'+buttonTaskId);
        let additionalButtons = document.getElementById('additionalButtons');

        if ( additionalButtons != null ) {
            additionalButtons.remove();
        }

        let innerHTML = buttonBar.innerHTML;


        buttonBar.innerHTML = "" +
            "<div id='additionalButtons'>" +
            "<a onclick='openFormStopWork();' class='ui-btn ui-btn-success' title='Завершить'>Завершить</a>"+
            "</div>";

    }

};

function openFormStopWork () {

    const taskTitle = document.getElementById("pagetitle").innerText;

    dialog = new BX.CDialog({
        'title' : 'Отчет по задаче '+taskTitle,
        'content_url': '/local/task/forms/stop.php?taskID=' + getTaskID(),
        'width' : 600,
        'height' : 420,
        'resizable' : true,
        //buttons: ['<input class="modal-save-info ui-btn ui-btn-success" id="SEND" type="button" value="Завершить" onclick="stopTask()" />','<input type="button" class="modal-save-info ui-btn ui-btn-danger" name="close" value="Закрыть" id="close" onclick="BX.WindowManager.Get().Close();">']
        buttons: [
            /*'html_code',
            BX.CDialog.prototype.btnSave, BX.CDialog.prototype.btnCancel, BX.CDialog.prototype.btnClose,*/
            {
                title: "Сохранить отчет",
                name: "name1",
                id: "id1",
                className: 'ui-btn ui-btn-success',
                action: function () {
                    const taskResult = document.getElementById("taskResult").value;
                    const taskNextId = document.getElementById("taskNextId").value;
                    const taskResultIsDone = document.getElementById("taskResultIsDone").checked;
                    const taskClosePrevDate = document.getElementById("taskClosePrevDate").checked;
                    const taskNextDeadLine = document.getElementById("taskNextDeadLine").value;
                    const taskNextDesc = document.getElementById("taskNextDesc").value;
                    const taskNextContact = document.getElementById("taskNextContact").value;
                    const taskNextUser = document.getElementById("taskNextUser").value;

                    const taskNextTypeImportant = document.getElementById("taskTypeImportant").checked;
                    const taskNextTypeUrgent = document.getElementById("taskTypeUrgent").checked;

                    if(!!taskResult && (taskNextId != 0)) {
                        //alert("Есть результат #"+taskNextId);
                        const dataTaskBP = {
                            'taskId' : getTaskID(),
                            'result' : taskResult,
                            'resultIsDone' : taskResultIsDone,
                            'taskClosePrevDate' : taskClosePrevDate,
                            'nextTaskDeadLine' : taskNextDeadLine,
                            'nextTypeId' : taskNextId,
                            'nextTaskDesc' : taskNextDesc,
                            'nextTaskContact' : taskNextContact,
                            'nextTaskUser' : taskNextUser,
                            'nextTaskTypeImportant' : taskNextTypeImportant,
                            'nextTaskTypeUrgent' : taskNextTypeUrgent,
                        };
                        fetch('/local/task/handlers/closeTask.php', {
                            method: 'POST', // Здесь так же могут быть GET, PUT, DELETE
                            body: JSON.stringify(dataTaskBP), // Тело запроса в JSON-формате
                            headers: {
                                // Добавляем необходимые заголовки
                                'Content-type': 'application/json; charset=UTF-8',
                            },
                        }).then((response) => response.json())
                            .then((data) => {
                                if (data.ok) {
                                    //alert("success!");
                                    console.log("success!");
                                    console.dir(data);
                                    BX.WindowManager.Get().AllowClose();
                                    BX.WindowManager.Get().Close();
                                    //location.reload();
                                    const companyId = data?.companyId;
                                    if (companyId) {
                                        if(parseInt(companyId) == 19422) {
                                            window.location.replace("https://crm.refloor-nsk.ru/crm/start/");
                                        } else {
                                            window.location.replace("https://crm.refloor-nsk.ru/crm/company/details/" + companyId + "/");
                                        }
                                    }
                                } else {
                                    console.dir(data);
                                    alert("!Произошла ошибка, сообщите администратору код задачи: "+getTaskID());
                                }
                            })
                    } else {
                        alert("Заполните результат задачи и выберите следующее задачу");
                        return false;
                        //this.Close();
                    }
                },

            },
            {
                title: 'Закрыть',
                className: 'ui-btn ui-btn-default',
                action: function () {
                    BX.WindowManager.Get().Close();
                }
            },
            /*BX.CDialog.prototype.btnClose,*/
        ]
    }).Show();



}