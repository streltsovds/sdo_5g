<?php

/**
 *
 */
class HM_Assign_DataGrid_MassAction_ReAssignOnSession extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $subjectService = $dataGrid->getServiceContainer()->getService('Subject');
        $sessionSubject = $subjectService->find($options['courseId'])->current();

        if (($sessionSubject->base == HM_Subject_SubjectModel::BASETYPE_SESSION)) {
            $self = parent::create($dataGrid, $name, $options);

            $self->setName($name);

            $self->setUrl(
                array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 're-assign-on-session',
                    'subject_id' => $options['courseId']
                )
            );

            $self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

            $sessionsRows = $subjectService->fetchAll(
                $subjectService->quoteInto(
                    array(
                        'subid <> ?',
                        ' AND base_id = ?',
                        ' AND begin > ?',
                    ),
                    array(
                        $options['courseId'],
                        $sessionSubject->base_id,
                        date('Y-m-d')
                    )
                )
            );
            $sessions = array();
            if(count($sessionsRows)){
                foreach($sessionsRows as $session){
                    $sessions[$session->subid] = $session->name;
                }
            }

            $sessions = array(_('Выберите сессию')) + $sessions;

            $self->setSub(array(
                'function' => self::SUB_MASS_ACTION_SELECT,
                'params'   => array(
                    'url'     => $dataGrid->getView()->url($self->getUrl()),
                    'name'    => 'sessionsIds[]',
                    'options' => $sessions,
                    'multiple'=> false
                )
            ));

            return $self;
        }
    }
}