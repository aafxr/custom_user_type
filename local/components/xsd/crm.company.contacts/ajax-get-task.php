<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$result = [
    'status' => 'ok',
    'request' => $_REQUEST
];

$arUsers = [];


$periodStart = new \DateTime($_REQUEST['periodStart']);
$periodEnd = new \DateTime($_REQUEST['periodEnd']);
$currentDate = new \Datetime();

$taskStatusList = [CTasks::STATE_NEW, CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS, ];
if($_REQUEST['showDone'] == 'Y') {
    $taskStatusList[] = CTasks::STATE_COMPLETED;
}
$subFilter1 = ['::LOGIC' => 'OR'];

$indexFilter=1;
if($_REQUEST['showResponsible'] == 'Y') {
    $subFilter1["::SUBFILTER-".$indexFilter] = [
        'ACCOMPLICE' => [$USER->GetID()],
        'REAL_STATUS' => $taskStatusList ,
    ];
    $indexFilter++;
    $subFilter1["::SUBFILTER-".$indexFilter] = [
        'RESPONSIBLE_ID' => $USER->GetID(),
        'REAL_STATUS' => $taskStatusList ,
    ];
    $indexFilter++;
}
if($_REQUEST['showCreator'] == 'Y') {
    $subFilter1["::SUBFILTER-".$indexFilter] = [
        'CREATED_BY' => $USER->GetID(),
        'REAL_STATUS' => $taskStatusList ,
    ];
    $indexFilter++;
}
/*
if($_REQUEST['showAuditor'] == 'Y') {
    $subFilter1["::SUBFILTER-".$indexFilter] = [
        'AUDITOR_ID' => $USER->GetID(),
        'REAL_STATUS' => $taskStatusList ,
    ];
    $indexFilter++;
}
*/

$arFilter = array(
    '::LOGIC' => 'AND',
    'CHECK_PERMISSIONS' => 'Y',
    'ONLY_ROOT_TASKS' => 'Y',
    'SAME_GROUP_PARENT' => 'Y',
    '::SUBFILTER-1' => $subFilter1/*array(
        '::LOGIC' => 'OR',
        '::SUBFILTER-1' => array(
            'ACCOMPLICE' => [$USER->GetID()],
            'REAL_STATUS' => $taskStatusList ,
        ),
        '::SUBFILTER-2' => array(
            'RESPONSIBLE_ID' => $USER->GetID(),
            'REAL_STATUS' => $taskStatusList,
        ),
    ),*/
);
//AUDITOR - идентификатор аудитора;

$result['taskFilter'] = $arFilter;

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
        'ACCOMPLICES', // Соисполнители (идентификаторы пользователей).
        'CREATED_BY', // Идентификатор постановщика.
        'RESPONSIBLE_ID', // Идентификатор ответственного.
        'UF_*'
    ]
);

$arTasks = [];
while ($arTask = $res->GetNext()) {

    $deadLine=new DateTime($arTask['DEADLINE']);
    $deadLine->setTime(23,59,59);
    if(($deadLine < $currentDate) && ($arTask['STATUS'] !=  CTasks::STATE_COMPLETED)) {
        $deadLine = $currentDate;
        $arTask['DIE'] = true;
    }
    if($arTask['STATUS'] ==  CTasks::STATE_COMPLETED) {
        $arTask['DONE'] = true;
    }

    $taskPlanStart = $arTask['START_DATE_PLAN'] ? (new DateTime($arTask['START_DATE_PLAN'])) : (new DateTime($arTask['DEADLINE']));
    $taskPlanStart->setTime(0,0,0);

    if($arTask['START_DATE_PLAN'] && (  $deadLine->format("Y-m-d") != $taskPlanStart->format("Y-m-d"))) {

        $arTask['START_DATE'] =  $taskPlanStart->format("d.m.Y");

        while($taskPlanStart < $deadLine) {
            $arTask['DEADLINE_LONG'][] = $taskPlanStart->format("d.m.Y");
            $taskPlanStart->modify('+1 day');
        }

        $arTask['LONG'] = true;
    }

    $arTask['DEADLINE_DATE'] = $deadLine->format("d.m.Y");


    $rsUser = CUser::GetByID($arTask['CREATED_BY']);
    $arUser = $rsUser->Fetch();
    if($arUser['PERSONAL_PHOTO']) {
        $arFile = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
        if($arFile) {
            $arTask['CREATER_USER_PIC'] = $arFile['SRC'];
        }
    }
    $arTask['CREATER_USER_TITLE'] = $arUser['NAME'].' '.$arUser['LAST_NAME'];

    $rsUser = CUser::GetByID($arTask['RESPONSIBLE_ID']);
    $arUser = $rsUser->Fetch();
    if($arUser['PERSONAL_PHOTO']) {
        $arFile = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
        if($arFile) {
            $arTask['RESPONSIBLE_USER_PIC'] = $arFile['SRC'];
        }
    }
    $arTask['RESPONSIBLE_USER_TITLE'] = $arUser['NAME'].' '.$arUser['LAST_NAME'];

    $arTasks[$arTask['ID']] = $arTask;
    /*echo '<pre>';
    print_r($arTask);
    echo '</pre>';*/



}

$result['tasks'] = $arTasks;

// Templates
$arTemplateTasks = [];

$res = CTaskTemplates::GetList(
    ["TITLE" => "ASC"],
    ["RESPONSIBLE" => $USER->GetID()],
    false,
    false,
    [],
    ['ID','REPLICATE_PARAMS_AR','TITLE','UF_*',
        'ACCOMPLICES', // Соисполнители (идентификаторы пользователей).
        'CREATED_BY', // Идентификатор постановщика.
        'RESPONSIBLE_ID' // Идентификатор ответственного.
    ]
);

while ($arTemplate = $res->GetNext())
{
    $arTemplate['REPLICATE_PARAMS_AR'] = \Bitrix\Tasks\Util\Type::unSerializeArray($arTemplate['~REPLICATE_PARAMS']);

    $taskTemplate = new \Bitrix\Tasks\Item\Task\Template($arTemplate['ID']);


    $rItem = [
        'ID' => $arTemplate['ID'],
        'R' => $arTemplate['REPLICATE_PARAMS_AR']
    ];


    $nextTime = false;

    // итерируем задачу
    if($arTemplate['REPLICATE_PARAMS_AR']['END_DATE'] != "") {
        $repeatedEndDate = new \DateTime($arTemplate['REPLICATE_PARAMS_AR']['END_DATE']);
    } else {
        $repeatedEndDate = false;
    }

    if(($repeatedEndDate === false) || ($repeatedEndDate > $periodStart)) {

        // Если дата окончания шаблона больше даты начала периода
        $repeatFlag = true;
        $i = 0; // количество итераций
        while($repeatFlag) {

            $nextTime = CTasks::getNextTime(unserialize($arTemplate['~REPLICATE_PARAMS'], ['allowed_classes' => false]), false, $nextTime); // localtime
            $taskRepeatTime = new DateTime($nextTime);

            if($taskRepeatTime > $periodEnd) {
                $repeatFlag = false;
            } else {
                $id = (int)$taskRepeatTime->format('Y')*1000;
                $id += (int)$taskRepeatTime->format('z');
                $arTemplateTasks[] = [
                    'ID' => $arTemplate['ID'].$id,
                    'TEMPLATE_ID' => $arTemplate['ID'],
                    'TITLE' => $arTemplate['TITLE'],
                    'DEADLINE' => $taskRepeatTime->format("Y-m-d"),
                    'DEADLINE_DATE' => $taskRepeatTime->format("d.m.Y"),
                    'PRIORITY' => $taskTemplate['UF_AUTO_851551329931'],
                    'DATA'=>$arTemplate,
                    'PARAMS' =>  $arTemplate['REPLICATE_PARAMS_AR']
                ];
            }

            $i++;
            if($i > 3650) {
                // на всякий случай чтобы не было зацикливания
                $repeatFlag = false;
            }
        }
    } else {
        $result['repeatLog'][] = [
            "skip"=>$arTemplate['ID'],
            "params"=>$arTemplate['REPLICATE_PARAMS_AR']
        ];
    }
}

$result['templateTasks'] = $arTemplateTasks;


echo json_encode($result);
?>