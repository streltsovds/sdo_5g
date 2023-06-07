<?php

/**
 *
 */
class HM_User_DataGrid_MassAction_AssignTag extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'assign-tag']);
        $self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        $self->setSub(array(
            'function' => self::SUB_MASS_ACTION_FCBK,
            'params'   => array(
                'url'     => $dataGrid->getView()->url($self->getUrl()),
                'name'    => 'tags',
                'options' => $options
            )
        ));

        return $self;
    }
}