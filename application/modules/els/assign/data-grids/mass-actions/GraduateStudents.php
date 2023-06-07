<?php

/**
 *
 */
class HM_Assign_DataGrid_MassAction_GraduateStudents extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name, $options);

        $self->setName($name);

        $self->setUrl(
            array(
                'module' => 'assign',
                'controller' => 'student',
                'action' => 'graduate-students',
            )
        );

        $self->setSub(array(
            'function' => self::SUB_MASS_ACTION_INPUT,
            'params'   => array(
                'url'     => $dataGrid->getView()->url(array('action' => 'graduate-students')),
                'name'    => 'certificate_validity_period',
                'options' => array(
                    'title' => _('Действ. сертификата, мес'),
                ),
            )
        ));

        return $self;
    }
}