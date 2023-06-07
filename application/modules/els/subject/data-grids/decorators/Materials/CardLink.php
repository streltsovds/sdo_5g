<?php

/**
 *
 */
class HM_Subject_DataGrid_Decorator_Materials_CardLink extends HM_DataGrid_Column_Decorator_CardLink
{
    /*
     * Плейсхолдеры 'module', 'controller', 'action' меняются на нужные значения
     * в коллбэке HM_Subject_DataGrid_Callback_Materials_CardLink
     *
     * @param HM_DataGrid $dataGrid
     * @param array $data
     * @return string|void
     */
    static public function create(HM_DataGrid $dataGrid, array $data = [])
    {
        $url = $dataGrid->getView()->url([
            'module'     => 'module',
            'controller' => 'controller',
            'action'     => 'action',
            'subject_id' => $dataGrid->getView()->subjectId
        ]);

        return parent::createInstance($dataGrid, [],
            [
                'url'  => $url,
                'text' => '{{title}}'
            ]
        );
    }
}