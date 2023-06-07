<?php

/**
 *
 */
class HM_User_DataGrid_MassAction_BlockUser extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'block']);
        $self->setConfirm(_('Вы подтверждаете блокировку отмеченных пользователей? При этом для данных пользователей будет закрыт доступ к системе, но все данные о пользователе сохранятся.'));

        return $self;
    }
}