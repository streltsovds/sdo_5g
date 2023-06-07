<?php
class Message_ContactController extends HM_Controller_Action_Activity
{
    use HM_Controller_Action_Trait_Grid;

    public function updateLogicalCell($value) {
        if ($value) return 'ДА'; else return 'НЕТ';
    }

    protected function indexGridAction()
    {
        $currentUser = $this->getService('User')->getCurrentUserId();
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        $subject = $this->_getParam('subject', 'subject');
        $subjectId = (int) $this->_getParam('subject_id', 0);


        $this->getService('Activity')->initializeActivityCabinet('message', $subjectId ? 'subject' : '', $subjectId);
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator($currentUser);
        $enablePersonalInfo = ($isModerator || !$this->getService('Option')->getOption('disable_personal_info'));
        $select = $this->getService('Contact')->getContactSelect($subject, $subjectId);

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'fio_ASC');
        }         
        
        if($select == null){
            $select = $this->getService('User')->getSelect();
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID' => 't1.MID',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'Phone' => 't1.Phone',
                    'EMail' => 't1.Email',
                )
            );
        }

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'role' => array('hidden' => true),
                'Gender' => array('hidden' => true),
                'fio'    => array(
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink($this->view->url(
                        array(
                            'action' => 'view',
                            'controller' => 'list',
                            'module' => 'user',
                            'user_id' => ''
                        )).'{{MID}}',_('Карточка пользователя')).' {{fio}}'),
                'Phone' => array('title' => _('Рабочий телефон'), 'decorator' => $enablePersonalInfo ? '{{Phone}}' : ''),
                /*'isPotentialModerator' => array(
                    'title' => _('Может быть модератором'),
                    'callback' => array(
                        'function' => array($this, 'updateLogicalCell'),
                        'params' => array('{{isPotentialModerator}}')
                    ),
                ),*/
                'Fax' => array('title' => _('Мобильный телефон'), 'decorator' => $enablePersonalInfo ? '{{Fax}}' : ''),
                'EMail' => array('title' => _('E-Mail'), 'decorator' => $enablePersonalInfo ? '{{EMail}}' : '')
            ),
            array(
                'fio' => null,
                'Login' => null,
                'EMail' => null
            ),
            'grid'
        );
        
        
        if (
            ($isModerator || !$this->getService('Option')->getOption('disable_messages')) &&
            Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:message:send:instant-send')
        ) {
            $grid->addMassAction(
                array('module' => 'message', 'controller' => 'send', 'action' => 'index', 'subject' => $subject, 'subject_id' => $subjectId),
                _('Отправить сообщение')
            );
        }

        $grid->setPrimaryKey(array('MID'));

        $this->view->grid = $grid;
        $this->view->subject = $subject;
    }

    protected function indexAction()
    {
        $page = $this->_getParam('page', 1);

        $currentUser = $this->getService('User')->getCurrentUserId();
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        $subject = $this->_getParam('subject', 'subject');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $select = $this->getService('Contact')->getContactSelect($subject, $subjectId);

        $this->getService('Activity')->initializeActivityCabinet('message', $subjectId ? 'subject' : '', $subjectId);
        $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator($currentUser);
        $enablePersonalInfo = ($isModerator || !$this->getService('Option')->getOption('disable_personal_info'));

        $paginator = Zend_Paginator::factory ($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(12);
        
        $items = $paginator->getCurrentItems();
        
        $userModel = new HM_User_UserModel(array());
        $mids      = array();
        $itemIndex = array();

        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);

        $activityService = $this->getService('Activity');
        
        foreach ($items as &$item) {
            
            $itemMid = $item['MID'];
            $userModel->MID = $itemMid;

            $allUserRoles = array_unique(explode(',', $item['role']));
            $allUserRoles = array_intersect($allUserRoles, array(
                HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_HR,
                HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
            ));
//            if (!count($allUserRoles)) continue;

            $userRole = HM_Role_Abstract_RoleModel::getMaxRole($allUserRoles);
            $userRole = $roles[$userRole ? $userRole : HM_Role_Abstract_RoleModel::ROLE_ENDUSER];

            // хотят отображать все роли
            $userRoles = array();
            foreach ($allUserRoles as $role) {
                $userRoles[] = $roles[$role];
            }
            
            $item['photo']        = $userModel->getPhoto();
            $item['role']         = $userRole;
            $item['roles']        = $userRoles;
            $item['is_moderator'] = $activityService->isUserActivityPotentialModerator($itemMid);

            $item['online']       = false;
            $item['last_visit']   = _('Не в сети');
            $item['position']     = '';
            $itemIndex[$itemMid] = $item;
            $mids[$itemMid] = $itemMid;
        }
        
        if (count($mids)) {
            
            $select = $this->getService('User')->getSelect();
            $select->from(array('s' => 'sessions'), array(
                's.mid',
                'position'   => 'p.name',
                'department' => 'pp.name',
                'stop'       => new Zend_Db_Expr('MAX(s.stop)')
            ));
            $select->joinLeft(array('p' => 'structure_of_organ'), 'p.mid = s.mid', array());
            $select->joinLeft(array('pp' => 'structure_of_organ'), 'pp.soid = p.owner_soid', array());
            $select->where('s.mid IN ('.implode(',', $mids).')');
            $select->group(array('s.mid', 'p.name', 'pp.name'));
            
            $userStops = $select->query()->fetchAll();
            
            foreach ($userStops as $stop) {
                $dt = new HM_Date($stop['stop']);
                $itemMid = $stop['mid'];
                
                $itemIndex[$itemMid]['online']     = mktime() - $dt->getTimestamp() < 600;
                $itemIndex[$itemMid]['last_visit'] = (($itemIndex[$itemMid]['Gender'] === '1') ? _('Был онлайн') : (($itemIndex[$itemMid]['Gender'] === '2') ? _('Была онлайн') : _('Был(а) онлайн'))) .' '.$dt->toString();
                $itemIndex[$itemMid]['position']   = ($stop['department'] ? $stop['department'] . ' / ' : '') . $stop['position'];
            }
        }
        
        $this->view->enablePersonalInfo = $enablePersonalInfo;
        $this->view->items = $itemIndex;
        $this->view->paginator = $paginator;
        $this->view->subject = $subject;
    }

    public function updateRole($field, $separator = ', ') {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);
        
        if ($field == '') {
            return $roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER];
        }

        $userRoles = array_unique(explode(',', $field));
        $fields = array();
        
        foreach ($userRoles as $userRole) {
            if (isset($roles[$userRole])) {
                $fields[] = $roles[$userRole];
            }
        }
        
        // #5337 - сворачивание высоких ячеек
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($fields)) . '</p>') : array();
        
        foreach ($fields as $value) {
            $result[] = "<p>{$value}</p>";
        }
        
        if ($result) {
            return implode($result);
        } else {
            return _('Нет');
        }
    }

}