<?php

use HM_Grid_ColumnCallback_Els_CitiesList      as CitiesList;
use HM_Grid_ColumnCallback_Tc_ProviderCardLink as ProviderCardLink;
use HM_Grid_ColumnCallback_Els_UserCardLink    as UserCardLink;
use HM_Grid_ColumnCallback_Tc_SubjectCardLink  as SubjectCardLink;

class HM_Session_Grid_NewSubjectsGrid extends HM_Grid
{
    protected static $_defaultOptions = array(
        'session_id'   => 0,
        'defaultOrder' => 'subid_ASC'
    );

    protected function _initColumns()
    {
        $citiesList       = new CitiesList($this, 'cities');
        $providerCardLink = new ProviderCardLink();
        $userCardLink     = new UserCardLink();
        $subjectCardLink  = new SubjectCardLink();

        $this->_columns = array(
            'subid' => array('hidden' => true),
            'name' => array(
                'title' => _('Курс'),
                'callback' => $subjectCardLink->getCallback('{{subid}}', '{{name}}'),
            ),
            'status' => array(
                'title' => _('Утвержден'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params'   => array('{{status}}')
                )
            ),
            'provider_id' => array('hidden' => true),
            'provider_name' => array(
                'title' => _('Провайдер'),
                'callback' => $providerCardLink->getCallback('{{provider_id}}', '{{provider_name}}'),
            ),
            'cities' => array(
                'title' => _('Город'),
                'callback' => $citiesList->getCallback('{{cities}}')
            ),
            'created_by' => array('hidden' => true),
            'created_by_name' => array(
                'title' => _('Инициатор'),
                'callback' => $userCardLink->getCallback('{{created_by}}', '{{created_by_name}}')
            ),
            'applications_count' => array(
                'title' => _('Кол-во заявок'),
                'callback' => array(
                    'function' => array($this, 'updateAppCount'),
                    'appendRowToParams' => true
                )
            ),
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'name' => null,
            'provider_name' => null,
            'status' => array('values' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeStatesSimple'))
        ));
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if ($this->getService('TcSession')->applicationsStatus($this->getSessionId()) != HM_Tc_Session_SessionModel::STATE_ACTUAL) {
            return;
        }

        $actions
            ->add('edit', array(
                'module'     => 'subject',
                'controller' => 'fulltime',
                'action'     => 'edit',
                'is_new_additional_course' => 1,
            ))
            ->setParams(array(
                'subid'
            ));


        $actions
            ->add('delete', array(
                'module'     => 'subject',
                'controller' => 'fulltime',
                'action'     => 'delete',
                'is_new_additional_course' => 1,
            ))
            ->setParams(array(
                'subid'
            ));

    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $actions->setInvisibleActions(array(
                'edit', 'delete'
            ));
        }

        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))
            && ($row['created_by'] != $this->getService('User')->getCurrentUserId())) {
            $actions->setInvisibleActions(array(
                'edit', 'delete'
            ));
        }
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
            )
        ){ 
            return;
        }
        
        if ($this->getService('TcSession')->applicationsStatus($this->getSessionId()) != HM_Tc_Session_SessionModel::STATE_ACTUAL) {
            return;
        }

        
        $massActions
            ->add(
                array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'delete-by',
                    'is_new_additional_course' => 1,
                    'session_id' => $this->getSessionId()
                ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

        $massActions
            ->add(
                array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'approve',
                    'is_new_additional_course' => 1,
                    'session_id' => $this->getSessionId()
                ),
                _('Утвердить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

        $massActions
            ->add(
                array(
                    'module'     => 'session',
                    'controller' => 'new-subjects',
                    'action'     => 'concatenation',
                    'session_id' => $this->getSessionId()
                ),
                _('Объединить с курсом'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            )

            ->addAutoComplete('subject_id', array(
                'DataUrl' => array(
                    'module'     => 'session',
                    'controller' => 'new-subjects',
                    'action'     => 'find-subject-for-concatenation',
                )
            ));

    }

    public function getSessionId()
    {
        return $this->_options['session_id'];
    }

    protected function _getApplicationGridId()
    {
        static $gridId = '';

        if ($gridId === '') {

            $gridId = 'grid'.$this->getSessionId();

            if ($this->currentUserIs(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
                $gridId .= $this->getService('Orgstructure')->getResponsibleDepartments();
            } else {
                $gridId .= '0';
            }

        }

        return $gridId;
    }

    public function updateAppCount($row)
    {
        $gridId = $this->_getApplicationGridId();

        $url = $this->getView()->url(array(
            'baseUrl' => 'tc',
            'module' => 'application',
            'controller' => 'list',
            'action' => 'index',
            'session_id' => $this->getSessionId(),
            'subject'.$gridId => $row['name'],
            'no-restore-state' => 'true'
        ));

        return '<a href="'.$url.'">'.$row['applications_count'].'</a>';
    }

    public function updateStatus($status)
    {
        return HM_Tc_Subject_SubjectModel::getVariant($status, 'FulltimeStatesSimple');
    }

} 