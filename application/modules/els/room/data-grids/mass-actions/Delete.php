<?php

/**
 *
 */
class HM_Room_DataGrid_MassAction_Delete extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'delete-by']);
        $self->setConfirm(_('Вы подтверждаете удаление отмеченных мест проведения обучения?'));

        return $self;
    }
}