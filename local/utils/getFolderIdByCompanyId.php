<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();


/**
 * поиск id папки компании
 *
 * @param $companyId
 * @return bool|string
 * @throws \Bitrix\Main\LoaderException
 */
function getFolderIdByCompanyId($companyId): bool|string
{
    if(!\Bitrix\Main\Loader::includeModule('crm')) return false;

    $storage = \Bitrix\Disk\Driver::getInstance()->getStorageByCommonId('shared_files_s1');
    if(!$storage) return false;

    $folderCrm = $storage->getChild(
        array(
            '=NAME' => 'CRM',
            'TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER
        )
    );
    if(!$folderCrm) return false;
    $folderEntityNameId = '[C'.$companyId.']';
    $folderEntity = $folderCrm->getChild(
        [
            'NAME' => "%".$folderEntityNameId,
            'TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER
        ]
    );
    if($folderEntity) return ''.$folderEntity->getId();
    return false;
}