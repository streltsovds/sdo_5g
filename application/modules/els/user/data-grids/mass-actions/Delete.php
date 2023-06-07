<?php

/**
 *
 */
class HM_User_DataGrid_MassAction_Delete extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'delete-by']);
        $self->setConfirm(_('Вы подтверждаете удаление отмеченных пользователей? При этом будут удалены все данные о пользователях, включая статистику обучения.'));

        return $self;
    }
}