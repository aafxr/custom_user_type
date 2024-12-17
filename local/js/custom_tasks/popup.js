//Встройка в
//\bitrix\components\bitrix\tasks.task.list\templates\.default\script.js
//\bitrix\components\bitrix\tasks.widget.buttons\templates\task\logic.js

BX.ready(function(){
      addAnswer = new BX.PopupWindow("my_answer", null, {
          titleBar: ' ',
          content: 'Применить для остальных задач?',
          overlay: {
              backgroundColor: 'grey', opacity: '80'
          },
      closeIcon: false,
      zIndex: 0,
      offsetLeft: 0,
      offsetTop: 0,
      draggable: {restrict: false},
      buttons: [
         new BX.PopupWindowButton({
            text: "Да",
            className: "popup-window-button-accept",
            events: {click: function(){
                var input = $('#task-form-bitrix_tasks_task_default_1 input[name="ACTION[0][ARGUMENTS][data][REPLICATE]"]').val('FOR_ALL');
                $('#task-form-bitrix_tasks_task_default_1').unbind('submit').submit();
                    this.popupWindow.close(); // закрытие окна
                }}
         }),
         new BX.PopupWindowButton({
            text: "Нет",
            className: "webform-button-link-cancel",
            events: {click: function(){
                    $('#task-form-bitrix_tasks_task_default_1').unbind('submit').submit();
                    this.popupWindow.hide(); // закрытие окна
            }}
         })
         ]
   });

    TaskListPopupDelete = new BX.PopupWindow("task_list_popup_delete", null, {
        titleBar: ' ',
        content: 'Применить для остальных задач?',
      closeIcon: true,
        zIndex: 0,
      offsetLeft: 0,
      offsetTop: 0,
        draggable: false,
        overlay: {
            backgroundColor: 'grey', opacity: '80'
        },
      buttons: [
         new BX.PopupWindowButton({
            text: "Да",
            className: "popup-window-button-accept",
            events: {click: function(){
                var templateId = customTemplateId;
                var taskId = customTaskId;
                BX.rest.callMethod('tasks.task.list', {filter : {FORKED_BY_TEMPLATE_ID: templateId, '>=ID': taskId}}, function (res) {
                    var tasks = res.answer.result.tasks;
                    for (var i = 0; i < tasks.length; i++) {
                        BX.Tasks.GridActions.doAction("delete", tasks[i].id);
                    }
                });
                    this.popupWindow.close(); // закрытие окна
                }}
         }),
         new BX.PopupWindowButton({
            text: "Нет",
            className: "webform-button-link-cancel",
            events: {click: function(){
                    var taskId = customTaskId;
                    BX.Tasks.GridActions.doAction("delete", taskId);
                    this.popupWindow.close(); // закрытие окна
            }}
         })
         ]
   });

    TaskPopupDelete = new BX.PopupWindow("task_popup_delete", null, {
        titleBar: ' ',
        overlay: {
            backgroundColor: 'grey', opacity: '80'
        },
      content: 'Применить для остальных задач?',
      closeIcon: true,
      zIndex: 0,
      offsetLeft: 0,
      offsetTop: 0,
      draggable: false,
      buttons: [
         new BX.PopupWindowButton({
            text: "Да",
            className: "popup-window-button-accept",
            events: {click: function(){
                var templateId = customTemplateId;
                var taskId = customTaskId;
                BX.rest.callMethod('tasks.task.list', {filter : {FORKED_BY_TEMPLATE_ID: templateId, '>=ID': taskId}}, function (res) {
                    var tasks = res.answer.result.tasks;
                    for (var i = 0; i < tasks.length; i++) {
                       // window.top.BX.UI.Notification.Center.notify({
                       //     content: BX.message('TASKS_DELETE_SUCCESS')
                       // });
                        BX.rest.callMethod('tasks.task.delete', {taskId: tasks[i].id});
                        BX.Tasks.Util.fireGlobalTaskEvent('DELETE', {ID: tasks[i].id});

                    }
                });
                    this.popupWindow.close(); // закрытие окна
                }}
         }),
         new BX.PopupWindowButton({
            text: "Нет",
            className: "webform-button-link-cancel",
            events: {click: function(){
                var taskId = customTaskId;
                BX.rest.callMethod('tasks.task.delete', {taskId: taskId});
                    //window.top.BX.UI.Notification.Center.notify({
                    //    content: BX.message('TASKS_DELETE_SUCCESS')
                    //});
                    BX.Tasks.Util.fireGlobalTaskEvent('DELETE', {ID: taskId});

                    this.popupWindow.close(); // закрытие окна
            }}
         })
         ]
   });

   $('#task-form-bitrix_tasks_task_default_1').submit(function (e) {
       var form = new FormData(this);
       var id = form.get('ACTION[0][ARGUMENTS][id]');
       var operation = form.get('ACTION[0][OPERATION]');
       var ths = $(this);
       if (operation == 'task.update') {
           e.preventDefault();
           var task = BX.rest.callMethod('tasks.task.get', {taskId: id, select: ['*', 'UF_*']}, function(res) {
               var data = res.answer.result.task;
               if (data.forkedByTemplateId != null && data.forkedByTemplateId != '0') {
                   addAnswer.show();
               } else {
                   ths.unbind('submit').submit();
               }
           });
       }
   });
});
