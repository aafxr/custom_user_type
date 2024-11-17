<?php
CJSCore::Init(array("jquery"));

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addJs("/local/js/custom_tasks/bptask.js");
Asset::getInstance()->addJs("/local/js/custom_tasks/bptaskTabHistory.js");

Asset::getInstance()->addCss("/local/css/task_custom.css");

/**
 * Правки интерфейса
 */
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/custom/refloor_interface_events.php");
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/custom/refloor_company_events.php");
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/custom/refloor_tasks_events.php");
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/custom/refloor_functions.php");
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/lib/customuf_category.php");

/**
 * Custom Contacts UF
 */
Asset::getInstance()->addCss("/local/css/custom_contact/custom_contact.css");
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/lib/UserType/CustomCompanyContacts.php");
require($_SERVER['DOCUMENT_ROOT']."/local/php_interface/custom/refloor_uf_event_handler.php");

/*

AddEventHandler("main", "OnBuildGlobalMenu", "MyOnBuildGlobalMenu");
function MyOnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
{
    // Убрать "Рабочий стол"
    echo "Admin event";
    die;
}
*/

if (CModule::IncludeModule("tasks")) {
    //AddEventHandler('tasks', 'OnTaskUpdate', ['TasksScheduler', 'OnAfterTaskUpdateHandler']);
    //AddEventHandler('tasks', 'OnTaskAdd', ['TasksScheduler', 'OnAfterTaskAddHandler']);

    AddEventHandler("tasks","OnAfterCommentAdd","MyOnAfterCommentAdd");

    AddEventHandler("tasks","OnTaskUpdate","MyOnAfterTaskUpdate");
    AddEventHandler("tasks","OnTaskAdd","MyOnAfterTaskUpdate");
}

AddEventHandler("tasks", "OnBeforeTaskAdd", array("xsdTask", "OnBeforeTaskAddHandler"));
AddEventHandler("tasks", "OnBeforeTaskUpdate", array("xsdTask", "OnBeforeTaskUpdateHandler"));

use Bitrix\Tasks\Control\Exception\TaskUpdateException;
Cmodule::includeModule("highloadblock");
class xsdTask {

    static function OnBeforeTaskAddHandler(&$arFields) {

            $log = date('d/m/Y H:i:s') . PHP_EOL;
            $log .= print_r($arFields, true) . PHP_EOL;

            if ($arFields["UF_CRM_TASK"][0]) {
                if ($arFields['UF_AUTO_274474131393']) {

                    $taskTypeId = $arFields['UF_AUTO_274474131393'];

                    $hlbl = 2; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
                    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
                    $taskTypesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

                    $taskTypesRes = $taskTypesClass::getList(['filter' => ['ID' => $taskTypeId]]);
                    $arTaskType = $taskTypesRes->Fetch();

                    $hlbl = 8; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
                    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
                    $taskTypesSectionsClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

                    $taskTypeSectionsRes = $taskTypesSectionsClass::getList(['filter' => ['ID' => $arTaskType['UF_SECTION']]]);
                    $arTaskTypesSection = $taskTypeSectionsRes->Fetch();

                    if (strpos($arFields['TITLE'], "#") === false) {
                        $arFields['TITLE'] = $arTaskType['UF_CODE'] . " " . $arTaskType['UF_NAME'] . " # " . $arFields['TITLE'];
                    }

                    if (!$arFields['DEADLINE']) {
                        throw new \Bitrix\Tasks\ActionFailedException("Укажите крайний срок задачи");
                    }

                    //throw new \Bitrix\Tasks\ActionFailedException("Указан тип задачи: ". $arFields['TITLE']);
                } else {
                    throw new \Bitrix\Tasks\ActionFailedException("Укажите тип задачи");
                }
            }

            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/log/add_task.log', $log, FILE_APPEND);


        //if($arFields['UF_AUTO_274474131393']) {

        //}
    }

    static function OnBeforeTaskUpdateHandler($id, &$arFields) {

        //global $USER;
        //$userId = $USER->GetID();
        //if($userId !== null) {

        if($arFields['UF_AUTO_274474131393']) {
            /**
             * Если задача имеет тип в CRM
             */


            /*
            throw new \Bitrix\Tasks\ActionFailedException("Для завершения задачи нужно оставить комментарий");

            $taskTypeId = $arFields['UF_AUTO_274474131393'];

            // hl
            Cmodule::includeModule("highloadblock");
            $hlbl = 2; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
            $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
            $taskTypesClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

            $taskTypesRes = $taskTypesClass::getList(['filter'=>['ID'=>$taskTypeId]]);
            $arTaskType = $taskTypesRes->Fetch();

            $hlbl = 8; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
            $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
            $taskTypesSectionsClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

            $taskTypeSectionsRes =  $taskTypesSectionsClass::getList(['filter'=>['ID'=>$arTaskType['UF_SECTION']]]);
            $arTaskTypesSection = $taskTypeSectionsRes->Fetch();

            if(strpos($arFields['TITLE'],"#") === false) {
                $arFields['TITLE'] = $arTaskType['UF_CODE']." ".$arTaskType['UF_NAME']." # ".$arFields['TITLE'];
            }
            */
            
            $log = date('d/m/Y H:i:s').PHP_EOL;
            //$log .= "taskTypeId:".$taskTypeId.PHP_EOL;
            $log .= print_r($arFields,true).PHP_EOL;
            //$log .= print_r($arTaskType,true).PHP_EOL;
            //$log .= print_r($arTaskTypesSection,true).PHP_EOL;

            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/log/update_task_'.$id.'.log',$log,FILE_APPEND);
        }
    }
}

/**
 * Функция удаляет комментарии системные сообщения кроме попадаюзих в список $exceptionType
 * последний пользовательский комментарий заносит в поле задачи
 * @param $commentId
 * @param $arFields
 * @return void
 */
function MyOnAfterCommentAdd($commentId, &$arFields){

    $arData = [
        'commentId' => $commentId,
        'event' => 'OnAfterCommentAdd',
        'arFields' => $arFields,
    ];

    $log = date('d/m/Y H:i:s').PHP_EOL;
    $log .= print_r($arData,true).PHP_EOL;

    \Bitrix\Main\Loader::IncludeModule('forum');
    $messages = \Bitrix\Forum\MessageTable::getList([
        'select' => ['*'],
        'filter' => [
            'ID' => $commentId
        ],
    ]);
    foreach ($messages as $message)
    {
        $log .= "::getList=".print_r($message,true).PHP_EOL;
        if($message['SERVICE_TYPE'] == 1) {

            $exceptionTypes[] = "COMMENT_POSTER_COMMENT_TASK_PINGED_STATUS";
            $serviceData = $message["SERVICE_DATA"];

            $exceptionFlag = false;
            foreach($exceptionTypes as $exceptionType) {
                if(mb_strpos( $serviceData,$exceptionType) !== false) {
                    $exceptionFlag = true;
                }
            }
            if(!$exceptionFlag) {
                \Bitrix\Forum\MessageTable::delete($message['ID']);
            }
        } else {
            $obTask = new CTasks;
            $success = $obTask->Update($arFields['TASK_ID'],["UF_AUTO_719191965958" => $message["POST_MESSAGE"]]);
        }
    }
    $log .= PHP_EOL;
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/log/comments.log',$log,FILE_APPEND);
}

/**
 * Обработка задает теги к задаче на основе установленного поля приоритет
 * @param $taskId
 * @param $arParams
 * @return void
 */
function MyOnAfterTaskUpdate($taskId, $arParams) {
    //$log = date('d/m/Y H:i:s')." ".$taskId."  MyOnAfterTaskUpdate ".PHP_EOL;
    //$log .= print_r($arParams,true).PHP_EOL;
    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/log/tasks.log',$log,FILE_APPEND);

    $UF_FIELD_PRIORITY_CODE = "UF_AUTO_851551329931";
    $UF_FIELD_PRIORITY_VALUE = mb_strtolower($arParams[$UF_FIELD_PRIORITY_CODE] ? $arParams[$UF_FIELD_PRIORITY_CODE] : $arParams['META:PREV_FIELDS'][$UF_FIELD_PRIORITY_CODE]);

    $taskPriopityTypes = [
        'срочная, важная' => [
            'class' => 'task-important-urgent',
            'title' => 'срочная, важная',
            'tag'   => 'срочная-важная'
        ],
        'важная, не срочная' => [
            'class' => 'task-important',
            'title' => 'важная, не срочная',
            'tag'   => 'важная'
        ],
        'срочная, не важная' => [
            'class' => 'task-urgent',
            'title' => 'срочная, не важная',
            'tag'   => 'срочная'
        ],
        'не указан' => [
            'tag' => false,
            'class' => '',
            'title' => 'не указано',
        ],
    ];

    try {
        foreach ($taskPriopityTypes as $type) {
            if ($type['tag']) {
                CTaskTags::Delete(["TASK_ID" => $taskId, "NAME" => $type['tag']]);
                $log .= "Delete tag:".$type['tag'].PHP_EOL;
            }
        }

        $tag = $taskPriopityTypes[$UF_FIELD_PRIORITY_VALUE]["tag"];
        if ($tag) {
            $arFields = array(
                "TASK_ID" => $taskId,
                "USER_ID" => 1,
                "NAME" => $tag
            );
            $obTaskTags = new CTaskTags;
            $log .= "Add:".$tag.PHP_EOL;
            $ID = $obTaskTags->Add($arFields);
        }

        $log = "tag=" . $arParams['META:PREV_FIELDS'][$UF_FIELD_PRIORITY_CODE] . PHP_EOL;
        $log .= "UF=" . $UF_FIELD_PRIORITY_CODE = 'UF_AUTO_245486950536' . PHP_EOL;
        $log .= "ID=" . $ID . PHP_EOL;
        $success = ($ID > 0);

        if ($success) {
            $log .= "Ok!";
        } else {
            $log .= "Error";
        }
        $log .= PHP_EOL;
        //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/log/tasks.log', $log, FILE_APPEND);
    } catch(Exception $exception) {
        $log .= "Exception".PHP_EOL;
        $log .= print_r($exception, true).PHP_EOL;
        //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/log/tasks.log', $log, FILE_APPEND);
    }
}

// remove xsd 08/11/2022
/*
AddEventHandler('main', 'OnEpilog', function () {
     global $USER;
     $arJsConfig = array(
         'custom_tasks' => array(
             'js' => '/local/js/custom_tasks/popup.js'
         )
     );
     foreach ($arJsConfig as $ext => $arExt) {
         \CJSCore::RegisterExt($ext, $arExt);
     }

     $context = \Bitrix\Main\Application::getInstance()->getContext();
     $server = $context->getServer();
     $url = $server->getRequestUri();
     ll($url, 'url');

     if(strpos($url, '/tasks/') > 0){
         CUtil::InitJSCore(array('custom_tasks'));
     }
 });
*/
class TasksScheduler
{
    function OnAfterTaskUpdateHandler($id, $arFields)
    {
        $data = $_POST['ACTION'][0]['ARGUMENTS']['data'];
       // if (!in_array($arFields['CREATED_BY'], [1,4])) return true;
        if ($data['REPLICATE'] != 'FOR_ALL') return true;
        $_POST['ACTION'][0]['ARGUMENTS']['data']['REPLICATE'] = 'N';
       // self::updateDates($id, $arFields['META:PREV_FIELDS']['FORKED_BY_TEMPLATE_ID']);

        ll(1, 'updateafters');
        $res = self::getTasksByTemplateId($arFields['META:PREV_FIELDS']['FORKED_BY_TEMPLATE_ID'], [">ID" => $id], $arFields['CREATED_BY']);

        ll($res, 'forked');
        $copyFields = $arFields;
        ll($arFields, 'arr');

        while ($arTask = $res->GetNext()) {
            ll($arTask['ID'], 'taskids');
            ll($arTask, 'arTask');
            if (!empty($arFields['DEADLINE'])) {
                $copyFields['DEADLINE'] = self::newDeadline($arFields['DEADLINE'], $arTask['CREATED_DATE']);
            }
            if (!empty($arFields['START_DATE_PLAN']) && !empty($arFields['END_DATE_PLAN'])) {
                list($copyFields['START_DATE_PLAN'], $copyFields['END_DATE_PLAN']) = self::newPlanDate($arTask['CREATED_DATE'], $arFields['START_DATE_PLAN'], $arFields['END_DATE_PLAN']);
                $copyFields['DEADLINE'] = $copyFields['END_DATE_PLAN'];
            }

            ll($copyFields, '$copyFields');
            try {
                $obj = new CTaskItem($arTask['ID'], $arTask['CREATED_BY']);
                $obj->update($copyFields);
            } catch (Exception $e)
            {
                ll($e, 'error');
            }
        }
    }

    function OnAfterTaskAddHandler($taskId, $arParams)
    {
       // if (!in_array($arParams['CREATED_BY'], [1,4])) return true;
        if ($arParams['REPLICATE'] == 'N') return true;

        $r = \CAgent::AddAgent(
            "TasksScheduler::createTaskByTemplate($taskId);",
            "tasks",
            "N",
            10
        );
        ll($r, 'agent');
        return true;
    }

    public static function newDeadline($deadline, $targetDateCreate)
    {
        if (empty($deadline)) return '';

        $target = new DateTime($deadline);

        $targetDateCreate = new DateTime($targetDateCreate);
        $newDeadline = $targetDateCreate->format("d.m.Y " . $target->format('H:i:s'));
        return $newDeadline;
    }
    public static function newPlanDate($dateCreate, $startDate, $endDate)
    {
        $newStartDate = '';
        $newEndDate = '';

        if (empty($startDate) && empty($endDate)) return [$newStartDate, $newEndDate];

        $dateCreate = new DateTime($dateCreate);
        $startDateTime = new DateTime($startDate);

        if (!empty($startDate) && !empty($endDate)) {
            $diff = strtotime($endDate) - strtotime($startDate);
            $newStartDate = $dateCreate->format("d.m.Y " . $startDateTime->format('H:i:s'));
            $startDateTime = new DateTime($newStartDate);
            $newEndDate = $startDateTime->modify("+" . $diff . ' seconds')->format("d.m.Y H:i:s");
        } else if (!empty($startDate))
        {
            $newStartDate = $dateCreate->format("d.m.Y " . $startDateTime->format('H:i:s'));
        }


        return [$newStartDate, $newEndDate];
    }

    public static function newDeadline_OLD($sourceDateCreate, $deadline, $targetDateCreate)
    {
        if (empty($deadline)) return '';

        $target = new DateTime($deadline);
        $sourceDateCreate = new DateTime($sourceDateCreate);
        $targetDateCreate = new DateTime($targetDateCreate);
        $sourceDateCreate = $sourceDateCreate->format("d.m.Y " . $target->format('H:i:s'));
        $sourceDateCreate = new DateTime($sourceDateCreate);

        $diff = $sourceDateCreate->diff($target);
        $days = $diff->format('%a');
        if ($target->format('d.m.Y') == $sourceDateCreate->format('d.m.Y')) {
            return $targetDateCreate->format("d.m.Y " . $target->format('H:i:s'));
        }
        if (!$diff->invert) {
            $newDeadline = $targetDateCreate
                ->modify('+' . $days . ' days');
        } else {
            $newDeadline = $targetDateCreate
                ->modify('-' . ($days). ' days');
        }
        $newDeadline = $newDeadline->format("d.m.Y " . $target->format('H:i:s'));

        return $newDeadline;
    }

    public static function createTaskByTemplate($taskId)
    {
        ll($taskId, 'test');

        $templateDbRes = \CTaskTemplates::getList(
            [],
            ['TASK_ID' => $taskId],
            false,
            ['USER_IS_ADMIN' => true],
            ['*', 'UF_*']
        );

        $template = $templateDbRes->Fetch();

        if (!$template) return '';

        $templateId = $template['ID'];

        $replicateParams = $template['REPLICATE_PARAMS'] = unserialize($template['REPLICATE_PARAMS'], ['allowed_classes' => false]);

        $repeatCount = 10;

        if ($replicateParams['REPEAT_TILL'] == 'times') {
            $repeatCount = $replicateParams['TIMES'];
        }

        $result = "Custom";
        $iterationCount = 0;

        while ($replicateParams['REPEAT_TILL'] == 'date' && $result == 'Custom' || $iterationCount < $repeatCount && $replicateParams['REPEAT_TILL'] != 'date') {
            $result = Bitrix\Tasks\Util\Replicator\Task\FromTemplate::repeatTask(
                $templateId,
                array(
                    'AGENT_NAME_TEMPLATE' => 'Custom',
                )
            );
            ll("RESULT = " . $iterationCount, 'log');
            ll($result, 'log');
            $iterationCount++;
        }

        $oTaskItem = CTaskItem::getInstance($taskId, $template['CREATED_BY']);
        $oTaskItem->Update([
            'FORKED_BY_TEMPLATE_ID' => $templateId,
            'REPLICATE' => 'N',
        ]);

        TasksScheduler::updateDates($taskId, $templateId);

        \CTaskTemplates::Delete($templateId);

        return '';
    }

    public static function updateDates($sourceTaskId, $templateId)
    {
        $sourceTask = new \Bitrix\Tasks\Item\Task($sourceTaskId);
        $sourceTaskData = $sourceTask->getData();

        $tasks = self::getTasksByTemplateId($templateId, [">ID" => $sourceTaskId], $sourceTaskData['CREATED_BY']);

        ll($sourceTaskData, '$sourceTaskData');
        $createdDate = $sourceTaskData['CREATED_DATE']->format('d.m.Y H:i:s');
        if (!empty($sourceTaskData['DEADLINE'])) {
            $deadline = $sourceTaskData['DEADLINE']->format('d.m.Y H:i:s');
        }
        if (!empty($sourceTaskData['START_DATE_PLAN']) && !empty($sourceTaskData['END_DATE_PLAN'])) {
            $startDatePlan = $sourceTaskData['START_DATE_PLAN']->format('d.m.Y H:i:s');
            $endDatePlan = $sourceTaskData['END_DATE_PLAN']->format('d.m.Y H:i:s');
        }
        $arFields = [];
        while ($arTask = $tasks->GetNext()) {
            try {
                if (!empty($sourceTaskData['DEADLINE'])) {
                    $arFields['DEADLINE'] = self::newDeadline($deadline, $arTask['CREATED_DATE']);
                }
                if (!empty($sourceTaskData['START_DATE_PLAN']) && !empty($sourceTaskData['END_DATE_PLAN'])) {
                    list($arFields['START_DATE_PLAN'], $arFields['END_DATE_PLAN'] ) = self::newPlanDate($arTask['CREATED_DATE'], $startDatePlan, $endDatePlan);
                    $arFields['DEADLINE'] = $arFields['END_DATE_PLAN'];
                }
                ll($arFields, '$arFields');
                if (!empty($arFields)) {
                    $obj = new CTaskItem($arTask['ID'], $arTask['CREATED_BY']);
                    $obj->update($arFields);
                }
            } catch (Exception $e) {
                ll($e, 'error_2');
            }
        }
    }

    private static function getTasksByTemplateId($templateId, $filter = [], $userId = 1)
    {
        return CTasks::GetList(
            [],
            array("FORKED_BY_TEMPLATE_ID" => $templateId, $filter),
            ['*', 'UF_*'],
            array("USER_ID" => $userId)
        );
    }
}

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

function ll($data, $fileName)
{
    $log = "\n" . date("Y.m.d G:i:s:v") . "\n";
    $log .= print_r($data, 1);
    file_put_contents(__DIR__ . '/' . $fileName . '.log', $log, FILE_APPEND);
}
