<?php


AddEventHandler('crm', 'OnAfterCrmControlPanelBuild', 'OnAfterCrmControlPanelBuildHandler');
function OnAfterCrmControlPanelBuildHandler(& $arMenu) {


    global $USER;
    if(!$USER->isAdmin()) {
        $arMenu = [
            [
                'ID' => 'START',
                'MENU_ID' => 'menu_crm_start',
                'NAME' => 'Старт',
                'TITLE' => 'Старт',
                'URL' => '/crm/start/',
                'ACTIONS' =>
                    [

                    ],
                'IS_ACTIVE' => 1,
                'TEXT' => 'Старт'
            ],
            [
                'ID' => 'COMPANY',
                'MENU_ID' => 'menu_crm_company',
                'NAME' => 'Компании',
                'TITLE' => 'Список компаний',
                'URL' => '/crm/company/list/',
                'COUNTER' => 5590,
                'COUNTER_ID' => 'crm_company_c0_all',
                'ACTIONS' =>
                [
                    [
                        'ID'=> 'CREATE',
                        'URL' => '/crm/company/details/0/?st%5Btool%5D=crm&st%5Bc_section%5D=company_section&st%5Bc_sub_section%5D=list&st%5Bc_element%5D=control_panel_create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=company'
                    ]
                ],
                'IS_ACTIVE' => 1,
                'TEXT' => 'Компании'
            ],
            [
                'ID' => 'CONTACT',
                'MENU_ID' => 'menu_crm_contact',
                'NAME' => 'Контакты',
                'TITLE' => 'Список контактов',
                'URL' => '/crm/contact/list/',
                'ICON' => 'contact',
                'COUNTER' => 4,
                'COUNTER_ID' => 'crm_contact_c0_all',
                'ACTIONS' =>
                    [
                        [
                            'ID'=> 'CREATE',
                            'URL' => '/crm/contact/details/0/?st%5Btool%5D=crm&st%5Bc_section%5D=company_section&st%5Bc_sub_section%5D=list&st%5Bc_element%5D=control_panel_create_button&st%5Bp1%5D=crmMode_classic&st%5Bcategory%5D=entity_operations&st%5Bevent%5D=entity_add_open&st%5Btype%5D=contact'
                        ]
                    ],
                'IS_ACTIVE' => 1,
                'TEXT' => 'Контакты'
            ]
        ];
    }

}