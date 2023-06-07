<?php

use HM_Grid_ColumnCallback_Els_CitiesList      as CitiesList;
use HM_Grid_ColumnCallback_Tc_ProviderCardLink as ProviderCardLink;
use HM_Grid_ColumnCallback_Els_UserCardLink    as UserCardLink;
use HM_Role_Abstract_RoleModel as Roles;

class HM_Session_Grid_NewProvidersGrid extends HM_Grid
{
    protected static $_defaultOptions = array(
        'session_id'   => 0,
        'providers'    => array(),
        'defaultOrder' => 'provider_id_ASC'
    );

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_SUPERVISOR))) {
            return;
        }

        $menu->addItem(array(
            'urlParams' => array(
                'module' => 'session',
                'controller' => 'new-providers',
                'action' => 'new'
            ),
            'title' => _('Создать нового провайдера')
        ));
    }



    protected function _initColumns()
    {
        $citiesList       = new CitiesList($this, 'cities');
        $providerCardLink = new ProviderCardLink();
        $userCardLink     = new UserCardLink();

        $this->_columns = array(
            'provider_id' => array('hidden' => true),
            'provider_name' => array(
                'title' => _('Провайдер'),
                'callback' => $providerCardLink->getCallback('{{provider_id}}', '{{provider_name}}'),
            ),
            'status' => array(
                'title' => _('Утвержден'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params'   => array('{{status}}')
                )
            ),
            'cities' => array(
                'title' => _('Город'),
                'callback' => $citiesList->getCallback('{{cities}}')
            ),
            'courses_count' => array(
                'title' => _('Количество курсов'),
                'callback' => array(
                    'function' => array($this, 'updateCoursesCount'),
                    'appendRowToParams' => true
                )
            ),
            'applications_count' => array(
                'title' => _('Кол-во заявок'),
                'callback' => array(
                    'function' => array($this, 'updateAppCount'),
                    'appendRowToParams' => true
                )
            ),
            'creator_id' => array('hidden' => true),
            'creator_name' => array(
                'title' => _('Инициатор'),
                'callback' => $userCardLink->getCallback('{{creator_id}}', '{{creator_name}}')
            ),
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'provider_name' => null,
            'creator_name' => null,
            'status' => array('values' => HM_Tc_Provider_ProviderModel::getStatusesShort())
        ));
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if ($this->getService('TcSession')->applicationsStatus($this->getSessionId()) != HM_Tc_Session_SessionModel::STATE_ACTUAL) {
            return;
        }

        $actions
            ->add('edit', array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'edit',
            ))
            ->setParams(array(
                'provider_id'
            ));

        $actions
            ->add('delete', array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'provider_id'
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
            && ($row['creator_id'] != $this->getService('User')->getCurrentUserId())) {
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
        

        $massActions->add(
            array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'delete-by',
                'grid_id'    => $this->_getApplicationGridId(),
                'session_id' => $this->getSessionId()
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $massActions->add(
            array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'approve',
                'grid_id'    => $this->_getApplicationGridId(),
                'session_id' => $this->getSessionId()
            ),
            _('Утвердить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $massActions
            ->add(
                array(
                    'module'     => 'session',
                    'controller' => 'new-providers',
                    'action'     => 'concatenation',
                    'session_id' => $this->getSessionId()
                ),
                _('Объединить с провайдером'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            )

            ->addSelect('target_provider_id', $this->_options['providers']);

    }

    public function getSessionId()
    {
        return $this->_options['session_id'];
    }

    public function updateCoursesCount($row)
    {
        $url = $this->getView()->url(array(
            'baseUrl' => 'tc',
            'module' => 'session',
            'controller' => 'new-subjects',
            'action' => 'index',
            'session_id' => $this->getSessionId(),
            'provider_namegrid' => $row['provider_name'],
            'no-restore-state' => 'true'
        ));

        return '<a href="'.$this->getView()->escape($url).'">'.$row['courses_count'].'</a>';
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
            'provider_name'.$gridId => $row['provider_name'],
            'no-restore-state' => 'true'
        ));

        return '<a href="'.$url.'">'.$row['applications_count'].'</a>';
    }

    public function updateStatus($status)
    {
        return HM_Tc_Provider_ProviderModel::getStatusShort($status);
    }

} 