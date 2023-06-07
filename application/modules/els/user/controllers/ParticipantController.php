<?php
class User_ParticipantController extends HM_Controller_Action_User 
{

    // Сообщения
    
    const MSG_COURSE_NOT_FOUND = 'Следующие курсы не были найдены в базе: %s';
    const MSG_COURSE_SUCCESS_ASSIGNED = 'Следующие курсы были успешно назначены: %s';
    const MSG_SOMEBODY_ALREADY_ASSIGNED = 'Следующие курсы уже были назначены этому слушателю: %s';
    const MSG_SOME_COURSE_EXPIRED = 'Срок действия следующих курсов истёк: %s';
    
    /**
     * Экшн для списка курсов
     */
    public function assignAction() 
    {

        $userId = $this->_getParam('user_id', 0);
        
        $order = $this->_getParam('ordergrid');
        if ($order == ''){
            // @todo: есть подозрение, что в Orcale оно работает наоборот
            $this->_setParam('ordergrid', 'status_DESC');
        }
        
        $select = $this->getService('Project')->getSelect();
        $subSelect = $this->getService('Project')->getSelect();
        
        $subSelect->from(array('Participants'), array('MID', 'CID'))->where('MID = ?', $userId);
        
        $select->from(array('s' => 'projects'), array())
                ->joinLeft(array('d' => $subSelect),
                    's.projid = d.CID',
                    array(
                        'projid' => 's.projid',
                        'name' => 's.name',
                        'status' => 'd.MID'
                    )
                )
                ->group(array('s.projid', 's.name', 'd.MID'));

        // Область ответственности     
        $options = $this->getService('Curator')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());

        if ($options['unlimited_projects'] != 1){
            $select->joinInner(array('d2' => 'curators'), 'd2.project_id = s.projid', array())
                   ->where('d2.MID = ?', $this->getService('User')->getCurrentUserId());            
        }

        //$roles = HM_Role_Abstract_RoleModel::getBasicRoles(false);
        $grid = $this->getGrid($select,
            array('projid' => array('hidden' => true),
                'name' => array('title' => _('Название'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'project', 'controller' => 'list', 'action' => 'card', 'project_id' => ''), null, true) . '{{projid}}') . '<a href="'.$this->view->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'project_id' => ''), null, true) . '{{projid}}'.'">'. ' {{name}}</a>'),
                'login' => array('title' => _('Логин')),
                'role' => array('title' => _('Роли')),
                'status' => array('title' => _('Назначен'))
            ),
            array(
                'name' => null,
                'status' => array('values' => array( $userId => _('Да'), 'ISNULL' => _('Нет'),)),
            )
        );

        //$grid->addMassAction(array('action' => 'index'), _('Выберите действие'));
        
        if ($this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_STUDENT)){
            $grid->addMassAction(array('action' => 'assign-responsibilities'), _('Добавить курсы'));
        }else{
            $grid->addMassAction(array('action' => 'assign-responsibilities'), _('Добавить курсы'), _('Вы уверены, что хотите сделать пользователя слушателем?'));
        }
        $grid->addMassAction(array('action' => 'delete'), _('Удалить курсы'), _('Вы подтверждаете удаление курсов?'));
        

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('role',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateRole'),
                    'params' => array('{{role}}')
                )
            )
        );
        
        if ($userId) $grid->setClassRowCondition("'{{status}}' != ''", "success");

        
        $grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'projid');
        $grid->updateColumn('fixType', array('hidden' => true));
        
/*        $grid->addSubMassActionSelect(array(
            $this->view->url(
                array('action' => 'assign')
            )
        ),
            'role',
            $roles);*/
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;


    }


    /**
     * Экшн для присваивания ответственностей
     */
    public function assignResponsibilitiesAction()
    {
        $userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $participantService    = $this->getService('Participant');
        $projectService    = $this->getService('Project');
        $userService    = $this->getService('User');
        
        $messages = array(
            'not_found'    => array(),
            // 'already'    => array(),
            'expired'    => array(),
            'success'    => array(),
        );
        
        foreach ($ids as $value){
            $project = $this->getOne($projectService->find($value));
            
            if (!$project){ // Курс не найден
                $messages['not_found'][] = $project->getName();
                continue;
            }
            
            if ($project->isExpired()) {  // Истёк срок действия курса            
                $messages['expired'][] = $project->getName();
                continue;
            }

            if ($participantService->isUserExists($value, $userId)){ // Пользователь уже назначен на этот курс
                // $messages['already'][] = $project->getName();
                 continue;
            }

            if ($this->getService('Curator')->isProjectResponsibility($userService->getCurrentUserId(), $value)){
                $projectService->assignParticipant($value, $userId);
                $messages['success'][] = $project->getName();
            }
        }
        
        if (!empty($messages['not_found'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => sprintf(_(self::MSG_COURSE_NOT_FOUND), implode(', ', $messages['not_found']))
        ));
        
        /*if (!empty($messages['already'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
             'message' => sprintf(_(self::MSG_SOMEBODY_ALREADY_ASSIGNED), implode(', ', $messages['already']))
        ));*/
        
        if (!empty($messages['expired'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => sprintf(_(self::MSG_SOME_COURSE_EXPIRED), implode(', ', $messages['expired']))
        ));
        
        if (!empty($messages['success'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => sprintf(_(self::MSG_COURSE_SUCCESS_ASSIGNED), implode(', ', $messages['success']))
        ));
        
        $this->_redirector->gotoSimple('assign', 'participant', 'user', array('user_id' => $userId));
    }
    
    /**
     * Экшн для удаления ответственностей 
     */
    public function deleteAction() {
        $userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
       $service = $this->getService('Participant');
        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value) {
            if ($this->getService('Curator')->isProjectResponsibility($this->getService('User')->getCurrentUserId(), $value)){
                $res = $service->deleteBy(
                    array(
                        'MID = ?' => $userId, 
                        'CID = ?' => $value
                    )
                );
            }
        }

        if ($error === true) {
            $this->_flashMessenger->addMessage(_('На некоторых курсах пользователь не был слушателем'));
        } else {
            $this->_flashMessenger->addMessage(_('Курсы успешно удалены'));
        }
        $this->_redirector->gotoSimple('assign', 'participant', 'user', array('user_id' => $userId));

    }



    //  Функции для обработки полей в таблице


    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($field) {
        $userId = $this->_getParam('user_id', 0);
        //pr($field);
        if ($field == $userId) {
            return _('Да');
        } else {
            return _('Нет');
        }
    }

    public function updateName($name, $projectId) {

        return '<a href="' .
                $this->view->url(
                    array('module' => 'project',
                        'controller' => 'index',
                        'action' => 'index',
                        'project_id' => $projectId
                    )
                ) .
                '">' . $name . '</a>';


    }    
    
}

