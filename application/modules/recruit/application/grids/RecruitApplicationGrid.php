<?php

use HM_Role_Abstract_RoleModel as Roles;
use HM_Recruit_Application_ApplicationModel as Model;

class HM_Application_Grid_RecruitApplicationGrid extends HM_Grid
{
    protected static $_defaultOptions = array();
    protected static $_vacancyArray = array();

    protected function _initCols(HM_Grid_Columns $columns)
    {
        $columns->add(array(
            'recruit_application_id' => array(
                'hidden' => true
            ),
            'vacancy_id' => array(
                'hidden' => true
            ),
            'user_id' => array(
                'hidden' => true
            ),
            'vacancy_name' => array(
                'title' => Model::getLabel('vacancy_name'),
                'callback' => array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{recruit_application_id}}', '{{vacancy_name}}')
                ),
            ),
            'department_path' => array(
                'title' => Model::getLabel('department_path'),
                'callback' => array(
                    'function'=> array('HM_Controller_Action', 'updateDepartmentPath'),
                    'params'=> array('{{department_path}}')
                ),
                'filter' => null,
            ),
            'fio' => array(
                'title' => Model::getLabel('fio'),
                'decorator' => sprintf('<a href="%s">%s</a>',
                    //$this->getView()->cardLink($this->getView()->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{user_id}}'),
                    $this->getView()->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{user_id}}',
                    '{{fio}}'
                ),
            ),
            'rv_name' => array(
                'title' => Model::getLabel('rv_name'),
                'callback' => array(
                    'function' => array($this, 'updateVacancy'),
                    'params' => array('{{rv_name}}', '{{rv_id}}', '{{recruit_application_id}}')
                ),
            ),
//            'recruiter_fio' => array(
//                'title' => Model::getLabel('recruiter_fio'),
//                'callback' => array(
//                    'function' => array($this, 'updateRecruiterFio'),
//                    'params' => array('{{recruiter_fio}}', '{{recruiter_user_id}}')
//                ),
////                'decorator' => sprintf('%s <a href="%s">%s</a>',
////                    $this->getView()->cardLink($this->getView()->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{recruiter_user_id}}'),
////                    $this->getView()->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{recruiter_user_id}}',
////                    '{{recruiter_fio}}'
////                ),
//            ),
            'status' => array(
                'title' => Model::getLabel('status'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                ),
                'filter' => array(
                    'values' => Model::getStatuses()
                ),
                'order' => false
            ),

        ));
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR))) {
            return;
        }

        $actions
            ->add('edit', array(
                'module' => 'application',
                'controller' => 'list',
                'action' => 'edit'
            ))
            ->setParams(array(
                'recruit_application_id'
            ));

        $actions
            ->add('delete', array(
                'module'     => 'application',
                'controller' => 'list',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'recruit_application_id'
            ));

// сознательное скрыл этот идиотский бизнес-процесс
//        $actions
//            ->add(_('Принять в работу'), array(
//                'module'     => 'application',
//                'controller' => 'list',
//                'action'     => 'take-to-work',
//            ))
//            ->setParams(array(
//                'recruit_application_id'
//            ));
//
//        $actions
//            ->add(_('Вернуть в работу'), array(
//                'module'     => 'application',
//                'controller' => 'list',
//                'action'     => 'return-to-work',
//            ))
//            ->setParams(array(
//                'recruit_application_id'
//            ));
//
//        $actions
//            ->add(_('Приостановить'), array(
//                'module'     => 'application',
//                'controller' => 'list',
//                'action'     => 'stop',
//            ))
//            ->setParams(array(
//                'recruit_application_id'
//            ));

        $actions
            ->add(_('Создать сессию подбора'), array(
                'module'     => 'vacancy',
                'controller' => 'list',
                'action'     => 'create-from-application',
            ))
            ->setParams(array(
                'recruit_application_id'
            ));

        $actions
            ->add(_('Просмотр сессии подбора'), array(
                'module'     => 'vacancy',
                'controller' => 'report',
                'action'     => 'card',
            ))
            ->setParams(array(
                'vacancy_id'
            ));
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_HR))) {
            return;
        }

        $massActions
            ->add(
                array(
                    'module'     => 'application',
                    'controller' => 'list',
                    'action' => 'delete-by'
                ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

// сознательное скрыл этот идиотский бизнес-процесс
//        $massActions
//            ->add(
//                array(
//                    'module'     => 'application',
//                    'controller' => 'list',
//                    'action' => 'mass-stop'
//                ),
//                _('Приостановить'),
//                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
//            );
//
//        $massActions
//            ->add(
//                array(
//                    'module'     => 'application',
//                    'controller' => 'list',
//                    'action' => 'mass-take-to-work'
//                ),
//                _('Принять в работу'),
//                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
//            );

    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR))) {
            return;
        }

        $menu->addItem(array(
            'urlParams' => array(
                'module' => 'application',
                'controller' => 'list',
                'action' => 'new'
            ),
            'title' => _('Создать заявку')
        ));
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        $actionsToRemove = array();

        if ($this->sessionExists($row['recruit_application_id'])) {
            $actionsToRemove[] = _('Создать сессию подбора');
        } else {
            $actionsToRemove[] = _('Просмотр сессии подбора');
        }

        $actions->setInvisibleActions(array_unique($actionsToRemove));

        // сознательное скрыл этот идиотский бизнес-процесс
        return true;

        if (
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR)
            && ($row['user_id'] != $this->getService('User')->getCurrentUserId() && $row['created_by'] != $this->getService('User')->getCurrentUserId())
        ){
            $actionsToRemove[] = 'edit';
            $actionsToRemove[] = 'delete';
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $actionsToRemove[] = _('Создать сессию подбора');
            $actionsToRemove[] = _('Принять в работу');
        }

        if ($row['status'] == HM_Recruit_Application_ApplicationModel::STATUS_NEW
        ) {
            $actionsToRemove[] = _('Создать сессию подбора');
        }

        if ($row['status'] == HM_Recruit_Application_ApplicationModel::STATUS_INWORK)  {
            $actionsToRemove[] = _('Принять в работу');
            $actionsToRemove[] = _('Вернуть в работу');
        }

        if ($row['status'] == HM_Recruit_Application_ApplicationModel::STATUS_CREATED)  {
//            $actionsToRemove[] = _('Создать сессию подбора');
            $actionsToRemove[] = _('Принять в работу');
            $actionsToRemove[] = _('Вернуть в работу');
        }

        if ($row['status'] == HM_Recruit_Application_ApplicationModel::STATUS_STOPPED)  {
            $actionsToRemove[] = 'edit';
            $actionsToRemove[] = 'delete';
            $actionsToRemove[] = _('Создать сессию подбора');
            $actionsToRemove[] = _('Приостановить');
            $actionsToRemove[] = _('Принять в работу');
        }

        if ($row['status'] == HM_Recruit_Application_ApplicationModel::STATUS_CLOSED)  {
            $actionsToRemove[] = 'edit';
            $actionsToRemove[] = 'delete';
            $actionsToRemove[] = _('Создать сессию подбора');
            $actionsToRemove[] = _('Принять в работу');
            $actionsToRemove[] = _('Приостановить');
            $actionsToRemove[] = _('Вернуть в работу');
        }

        if ($row['status'] == HM_Recruit_Application_ApplicationModel::STATUS_COMPLETED)  {
            $actionsToRemove[] = 'edit';
            $actionsToRemove[] = 'delete';
            $actionsToRemove[] = _('Создать сессию подбора');
            $actionsToRemove[] = _('Принять в работу');
            $actionsToRemove[] = _('Приостановить');
            $actionsToRemove[] = _('Вернуть в работу');
        }


    }

    public function sessionExists($appId)
    {
        $collection = $this->getService('RecruitVacancy')->fetchAll(
            $this->getService('RecruitVacancy')->quoteInto(
                array('recruit_application_id = ? ', ' AND (deleted IS NULL OR deleted = ?)'),
                array($appId, 0)
            )
        );

        return count($collection);
    }

    public function updateStatus($status)
    {
        return '<span style="white-space: nowrap;">'.Model::getStatus($status).'</span>';
    }




    public function updateVacancy($name, $id, $recruit_application_id)
    {
        $result = '';
        if($id && !$this->deleted($id)){
            $vacancy = $this->_vacancyArray[$recruit_application_id];
            $count = count($vacancy);
            if (empty($vacancy) || $count == 1) {
                return '<a href="/recruit/vacancy/report/card/vacancy_id/' . $id . '">' . $name . '</a>';
            } else {
                $result .= '<p class="total">Количество сессий = ' . $count . '</p>';
                foreach ($vacancy as $item){
                    $result .= '<p><a href="/recruit/vacancy/report/card/vacancy_id/' . $item['vacancy_id'] . '">' . $item['name'] . '</a></p>';
                }
                return $result;
            }
        } else {
            return _('Нет');
        }
    }

    public function deleted($id)
    {
        $collection = $this->getService('RecruitVacancy')->fetchAll(
            $this->getService('RecruitVacancy')->quoteInto(
                array(' vacancy_id = ? ', ' AND (deleted = ?)'),
                array($id, 1)
            )
        );

        return count($collection);
    }

    public function updateRecruiterFio($recruiter_fio,$recruiter_user_id)
    {
        if($recruiter_user_id){
            return sprintf('%s <a href="%s">%s</a>',
                    $this->getView()->cardLink($this->getView()->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . $recruiter_user_id),
                    $this->getView()->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . $recruiter_user_id,
                $recruiter_fio
                );
        } else {
            return _('Нет');
        }
    }

    
    public function updateName($applicationId, $name)
    {
        $view = Zend_Registry::get('view');
        $url = $view->url(array(
            'module'         => 'vacancy',
            'controller'     => 'report',
            'action'         => 'card',
            'application_id' => $applicationId,
        ), null, true);
        return '<a href="' . $url . '">' . $view->escape($name) . '</a>';
    }


}