<?php

/**
 *
 */
class HM_DataGrid_Column_Decorator_UserCardLink extends HM_DataGrid_Column_Decorator_CardLink
{
    static public function create($dataGrid, array $data = [])
    {
        return parent::createInstance($dataGrid,
            [
                'url'  => $dataGrid->getView()->url([
                    'module'     => 'user',
                    'controller' => 'list',
                    'action'     => 'view',
                    'user_id'    => '']) . $data['userId'],
                'text' => _('Карточка пользователя')
            ],
            [
                'url'  => $dataGrid->getView()->url([
                    'module'     => 'user',
                    'controller' => 'edit',
                    'action'     => 'card',
                    'report'     => 1,
                    'user_id'    => ''
                    ]) . $data['userId'],
                'text' => $data['userName']
            ]
        );
    }
}