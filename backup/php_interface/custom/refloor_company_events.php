<?php
AddEventHandler("crm","OnBeforeCrmCompanyUpdate","RefloorBeforeCrmCompanyUpdate");
AddEventHandler("crm","OnAfterCrmCompanyUpdate","RefloorCompanyReCalcValues");
AddEventHandler("crm","OnAfterCrmCompanyAdd",   "RefloorCompanyReCalcValues");

AddEventHandler("crm","OnBeforeCrmContactUpdate","RefloorBeforeCrmContactUpdate");

/**
 * Поле день месяц рождения
 */
function RefloorBeforeCrmContactUpdate(&$arFields) {
    if(array_key_exists('BIRTHDATE',$arFields)) {
        if($arFields['BIRTHDATE']) {
            $date = DateTime::createFromFormat("d.m.Y", $arFields['BIRTHDATE']);
            $arFields['UF_BIRTH_DM'] = $date->format("dm");
        } else {
            $arFields['UF_BIRTH_DM'] = "";
        }
    }
}

/**
 * Проверка разных регламентов
 * @param $arFields
 * @return false|void
 */
function RefloorBeforeCrmCompanyUpdate(&$arFields) {

    $DEPARTMENT_OPT = 3;

    $bxCompanyId=$arFields['ID'];
    $arCompany = CCrmCompany::GetListEx([], ["ID" => $bxCompanyId],['ID','ASSIGNED_BY_ID'])->fetch();
    $arCompanyUF = CCrmCompany::GetList([], ["ID" => $bxCompanyId],['ID','UF_COMPANY_CATEGORIES','UF_LAST_ACTIVITY','UF_EXT_DATE_CREATE','UF_BLOCK_TYPE'])->fetch();

    /**
     * проверка регламент смены ответственного
     */
    if($arFields['ASSIGNED_BY_ID']) {

        global $USER;
        $bxCurrentUserId = $USER->GetID();
        $userIsAdmin = $USER->IsAdmin();

        $rsUserCurrentUser = CUser::GetByID(23);
        $arUserCurrentUser = $rsUserCurrentUser->Fetch();

        $userIsActive = ($arUserCurrentUser['ACTIVE'] == 'Y');

        $userIsArchive = ($arCompany['ASSIGNED_BY_ID'] == 290);
        $isOpt = userInDept($arCompany['ASSIGNED_BY_ID'],$DEPARTMENT_OPT) &&
                 userInDept($arFields['ASSIGNED_BY_ID'], $DEPARTMENT_OPT);

        if( ($userIsActive) && (!$userIsAdmin) && (!$userIsArchive) && (!$isOpt) &&
            ($bxCurrentUserId != $arCompany['ASSIGNED_BY_ID']) &&
            ($arFields['ASSIGNED_BY_ID'] != $arCompany['ASSIGNED_BY_ID'])) {

            $lastActivity = new \DateTime($arCompanyUF['UF_LAST_ACTIVITY']);
            $currentDay = new \DateTime();
            $interval = $currentDay->diff($lastActivity);

            $dayBlock = 90;
            if($arCompanyUF['UF_BLOCK_TYPE'] == 2) {
                $dayBlock = 180;
            }
            if($interval->days <= $dayBlock) {
                // Если меняет не владелец компании и не администратор
                /*$log = print_r($arFields, true);
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/log/company/onBefore.log', $log, FILE_APPEND);
                $log .= "interval:" . $interval->days . PHP_EOL;
                $log .= "before:" . PHP_EOL;
                $log .= print_r($arCompany, true);
                $log .= print_r($arCompanyUF, true);
                $log .= $bxCurrentUserId . "!=" . $arCompany['ASSIGNED_BY_ID'] . PHP_EOL;
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/log/company/onBefore.log', $log, FILE_APPEND);
                if ($bxCurrentUserId == 245) {*/
                $arFields['RESULT_MESSAGE'] = 'Запрещена смена ответственного лица по регламенту компании '.$interval->days." из ".$dayBlock." дней";
                return false;

            }
        }
    }

    /**
     * Пересчитываем значения категорий
     */
    $arCompanyCategories = $arCompanyUF['UF_COMPANY_CATEGORIES'];

    $hlElID = 9; // CrmCompanyCategorySections
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlElID)->fetch();
    $categorySectionClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

    $hlElID = 10; // CrmCompanyCategories
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlElID)->fetch();
    $categoryClass = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();

    /**
     *
     */
    $arUpdateFields = [];
    $arCategories = [];
    $arSectionsId = [];
    $resProps =$categoryClass::getList(['order'=>['UF_NAME'=>'ASC'],'filter'=>['ID'=>$arCompanyCategories]]);
    $arProps = [];

    while ($arCategory = $resProps->Fetch()) {
        $arCategories[$arCategory['UF_SECTION']][$arCategory['ID']] = $arCategory;
        $arSectionsId[] = $arCategory['UF_SECTION'];
    }

    $resPropValues = $categorySectionClass::getList(['filter' => ['ID'=> $arSectionsId]]);
    $categorySections = [];
    while ($arSection = $resPropValues->Fetch()) {
        $categorySections[$arSection['ID']] = $arSection;
    }

    $ufValueText = "";
    foreach($arCategories as $categoryId => $arSectionCategories) {
        $ufId = $categorySections[$categoryId]['UF_USER_FIELD_ID'];
        $ufCode = $categorySections[$categoryId]['UF_USER_FIELD'];
        $ufValue = [];

        foreach ($arSectionCategories as $arSectionCategory) {
            $value = $arSectionCategory['UF_NAME'];
            $valueId = getUFListValueId($value, $ufId);
            $ufValue[] = $valueId;
            $ufValueText .= $value.PHP_EOL;
        }
        $arFields[$ufCode] = $ufValue;
    }

    $arFields['UF_CATEGORY_TEXT'] = $ufValueText;

    /**
     * Записываем в пользовательское поле значенеи адреса , чтобы можно было вывести его в списке компаний
     */
    $requisite = new \Bitrix\Crm\EntityRequisite();
    $reqRow = $requisite->getList(["filter" => ["ENTITY_ID" => $bxCompanyId, \CCrmOwnerType::Company]])->fetch();
    $arAddr = \Bitrix\Crm\EntityRequisite::getAddresses($reqRow['ID']);
    if(is_array($arAddr)) {
        $firstAddr = current($arAddr);
        $addr = $firstAddr['ADDRESS_1'] . " " . $firstAddr['ADDRESS_2'];
        $arFields['UF_ADDR_TEXT'] = $firstAddr['ADDRESS_1'] . " " . $firstAddr['ADDRESS_2'];
    }


}

// OnAfterCrmCompanyUpdate
// OnAfterCrmCompanyAdd

function RefloorCompanyReCalcValues(&$arFields) {
    global $USER;
    $bxCurrentUserId = $USER->GetID();

    $bxCompanyId = $arFields['ID'];
    $arCompany = CCrmCompany::GetList([], ["ID" => $bxCompanyId],['ID','UF_LAST_ACTIVITY','UF_CITY_LIST'])->fetch();

    if($arCompany['UF_LAST_ACTIVITY'] == '') {
        CompanyLastActivityDateTimeHandler($bxCompanyId);
    }





}
