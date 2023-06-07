<?php

/**
 *
 */
class HM_Assign_DataGrid_MassAction_UnAssignProgram extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $programs = $dataGrid->getServiceContainer()->getService('Programm')->fetchAll(null, 'name');

        if (count($programs)) {
            $self = parent::create($dataGrid, $name, $options);

            $self->setName($name);

            $self->setUrl(
                array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'unassign-programm'
                )
            );

            $self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

            $self->setSub(array(
                'function' => self::SUB_MASS_ACTION_SELECT,
                'params'   => array(
                    'url'     => $dataGrid->getView()->url($self->getUrl()),
                    'name'    => 'programmId[]',
                    'options' => $programs->getList('programm_id', 'name')
                )
            ));

            return $self;
        }
    }
}