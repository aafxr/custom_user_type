<?php

class TaskGridComponent extends \CBitrixComponent
{
    const GRID_ID = 'REFLOOR_TASK_START_GRID';
    const PAGE_SIZE = 15;

    public function executeComponent()
    {
        $grid_id = self::GRID_ID ;
       // $grid_options = new Grid\Options($grid_id);

        
        $this->includeComponentTemplate();
    }
}