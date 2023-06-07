<?php
class Session_UserController extends HM_Controller_Action_Session
{
    use HM_Controller_Action_Trait_Grid;

    public function positionFullFilter($data)
    {
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){

            $value = '%' . $value . '%';

            $select->where("(so.name LIKE ?", $value);
            $select->orWhere("so1.name LIKE ?", $value);
            $select->orWhere("so2.name LIKE ?)", $value);
        }
    }

    public function listAction()
    {
        $gridId = ($this->_session->session_id) ? "grid{$this->_session->session_id}" : 'grid';
        
        $switcher = (int) Bvb_Grid::getGridSwitcherParamById($gridId);
        if (!$switcher) {
            $this->_request->setParam("session{$gridId}", $this->_session->session_id);
        }
         
        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", $sorting = 'fio_ASC');
        }

        $select = $this->getService('AtSessionUser')->getSelect();
        
        $select->from(array('so' => 'structure_of_organ'), array(
                'MID' => 'p.MID',
                'soid' => 'so.soid',
                'session' => 'asu.session_id',
                'asu.session_user_id',
                'workflow_id' => 'asu.session_user_id',
                'ase.user_id',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//                'position_full' => new Zend_Db_Expr("CONCAT(so.name, CONCAT('/', CONCAT(so1.name, CONCAT('/', so2.name))))"),
                'department' => 'so1.name',
                'position' => 'so.soid',
                'events' => new Zend_Db_Expr("COUNT(DISTINCT ase.session_event_id)"),
                'asu.status',
                'status_id' => 'asu.status',
            ))
            ->join(array('p' => 'People'), 'p.MID = so.mid', array())
            ->joinLeft(array('so1' => 'structure_of_organ'), "so.owner_soid = so1.soid", array())
            ->joinLeft(array('so2' => 'structure_of_organ'), "so1.owner_soid = so2.soid", array())
            ->joinLeft(array('asu' => 'at_session_users'), 'so.soid = asu.position_id AND asu.session_id = ' . $this->_session->session_id, array())
            ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_id = asu.session_id AND ase.user_id = p.MID', array())
            ->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'ase.session_user_id', 'asu.session_user_id', 'so.owner_soid', 'so.soid', 'so.name', 'so1.name', 'so2.name', 'ase.user_id', 'asu.status', 'asu.session_id'));

        
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ))) {
            $department = $this->getService('Orgstructure')->getDefaultParent();
            $select->where('so.lft > ?', $department->lft) 
                ->where('so.rgt < ?', $department->rgt); 
        }        

        //exit ($select->__toString());
        $grid = $this->getGrid($select, array(
            'MID' => array('hidden' => true),
            'soid' => array('hidden' => true),
            'status_id' => array('hidden' => true),
            'session_user_id' => array('hidden' => true),
            'session' => array('hidden' => true),
            'workflow_id' => array(
                 'title' => _('Бизнес-процесс'), // бизнес проуцесс
                 'callback' => array(
                     'function' => array($this, 'printWorkflow'),
                     'params' => array('{{workflow_id}}'),
                 ),
             ),
            'user_id' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))) ? $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => '', 'baseUrl' => ''), null, true) . '{{MID}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => '', 'baseUrl' => ''), null, true) . '{{MID}}'.'">'. '{{fio}}</a>' : null,
            ),
//            'position_full' => array('title' => _('Должность')),
//            'department' => array(
//                'title' => _('Подразделение'),
//                'callback' => array(
//                    'function'=> array($this, 'departmentsCache'),
//                    'params' => array('{{department}}', $select)
//                )
//            ),
            'department' => array(
                'title' => _('Подразделение'),
            ),
            'position' => array(
                'title' => _('Должность'),
                'callback' => array(
                    'function'=> array($this, 'departmentsCache'),
                    'params' => array('{{position}}', $select, true)
                )
            ),
            'events' => array(
                'title' => _('Количество оценочных форм'),
                'callback' => array(
                    'function'=> array($this, 'updateEvents'),
                    'params' => array('{{events}}', '{{fio}}', '{{user_id}}')
                )
            ),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            ),
        ),
        array(
            'fio' => null,
            'department' =>  array(
                'render' => 'department'
            ),
            'position' => null,
            'status' => array('values' => HM_At_Session_User_UserModel::getStatuses()),
        ), $gridId);
        
        $grid->addMassAction($this->view->url(array(
                'baseUrl' => '',
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index',
                'session_id' => $this->_session->session_id,
            )),
            _('Отправить сообщение')
        );
        
        if ($switcher === 0) {
            // только участники сессии
            
            if ($this->getService('Acl')->isCurrentAllowed('mca:session:user:change-status')) {
            $grid->addMassAction(
                array(
                    'module' => 'session',
                    'controller' => 'user',
                    'action' => 'unassign',
                ),
                _('Удалить из списка участников'),
                _('Вы действительно хотите исключить выделенных пользователей из числа участников оценочной сессии? При этом будут удалены все результаты, полученные ими в ходе оценочной сессии.')
            );            
            }            
                       
            if (($this->_session->state == HM_At_Session_SessionModel::STATE_ACTUAL) && $this->getService('Acl')->isCurrentAllowed('mca:session:user:change-status')) {
                $grid->addMassAction(
                    array(
                        'module' => 'session',
                        'controller' => 'user',
                        'action' => 'change-status',
                        'status' => HM_At_Session_User_UserModel::STATUS_COMPLETED,
                    ),
                    _('Завершить оценочную сессию'),
                    _('Вы уверены, что хотите принудительно завершить оценочную сессию для отмеченных пользователей?')
//                     _('Вы уверены, что хотите принудительно завершить оценочную сессию для отмеченных пользователей? При этом все незаполненные анкеты по ним будет аннулированы.')
                );
            }
            
            if ($this->_session->state != HM_At_Session_SessionModel::STATE_PENDING) {                
/*                
                $grid->addMassAction(
                    "/merge_pdfs/generate.php?session_id=" . $this->_session->session_id,
                    _('Скачать индивидуальные отчеты как один PDF-файл')
                );

                $grid->addMassAction(
                    array(
                        'module' => 'session',
                        'controller' => 'user',
                        'action' => 'export-zip',
                    'session_id' => $this->_session->session_id,
                    ),
                    _('Скачать индивидуальные отчеты как один ZIP-архив'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
*/
/*
                $grid->addAction(array(
                        'module' => 'session',
                        'controller' => 'report',
                        'action' => 'user-word',
                    ),
                    array('session_user_id'),
                    _('Скачать индивидуальный отчёт')
                );
*/

                $grid->addAction(array(
                    'module' => 'session',
                    'controller' => 'report',
                    'action' => 'user',
                ),
                    array('session_user_id'),
                    _('Индивидуальный отчёт')
                );

                $grid->addAction(array(
                        'module' => 'session',
                        'controller' => 'report',
                        'action' => 'user-analytics',
                    ),
                    array('session_user_id'),
                    _('Анализ результатов')
                );

// ушло в инд.отчёт в виде кнопки
//                 $grid->addAction(
//                     "/merge_pdfs/generate.php?session_id=" . $this->_session->session_id,
//                     array('session_user_id'),
//                     _('Скачать индивидуальный отчет')
//                 );
            }
            
            if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
                $grid->addAction(array(
                    'baseUrl' => '',
                    'module' => 'user',
                    'controller' => 'list',
                    'action' => 'login-as'
                ),
                    array('MID'),
                    _('Войти от имени пользователя'),
                    _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
                ); 
            }
            
        } else {
            
            $grid->updateColumn('status', array(
                'hidden' => true
            ));
            $grid->updateColumn('test_count', array(
                'hidden' => true
            ));
            
            if ($this->getService('Acl')->isCurrentAllowed('mca:session:user:change-status')) {
                $grid->addMassAction(
                    array(
                        'module' => 'session',
                        'controller' => 'user',
                        'action' => 'assign'
                    ),
                    _('Включить пользователей в оценочную сессию'),
                    _('Вы уверены, что хотите включить отмеченных пользователей в оценочную сессию?')
                );
            }
            
        }

        $grid->addAction(array(
            'baseUrl' => '',
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index',
            'session_id' => $this->_session->session_id,
        ),
            array('MID'),
            _('Отправить сообщение')
        );

        if (!$this->view->gridAjaxRequest && $this->getService('Acl')->isCurrentAllowed('mca:session:user:change-status')) {
            $grid->setGridSwitcher(array(
                array(
                    'name'   => 'positions', 
                    'title'  => _('участников данной сессии'), 
                    'params' => array(
                        'all' => 0,
                        'session' => $this->_session->session_id
                    )
                ),
                array(
                    'name'   => 'all_positions', 
                    'title'  => _('всех пользователей'), 
                    'params' => array(
                        'all' => 1,
                        'session' => null,
                    )
                ),
            ));
        }

//         $grid->setActionsCallback(
//             array('function' => array($this,'updateActions'),
//                   'params'   => array('{{status_id}}')
//             )
//         );
        $grid->setClassRowCondition("'{{session}}' != {$this->_session->session_id}", '', 'selected');
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function unassignAction() 
    {
        $gridId = ($this->_session->session_id) ? "grid{$this->_session->session_id}" : 'grid';
        
        $positionIds = explode(',', $this->_request->getParam("postMassIds_{$gridId}"));
        if (is_array($positionIds) && count($positionIds)) {
            $sessionUsers = $this->getService('AtSessionUser')->fetchAll(array(
                'session_id = ?' => $this->_session->session_id,
                'position_id IN (?)' => $positionIds,
            ));
            foreach ($sessionUsers as $sessionUser) {
                $this->getService('AtSessionUser')->delete($sessionUser);
            }
            
            $this->_flashMessenger->addMessage(_('Участники успешно удалены'));

        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Произошла ошибка при удалении участников')));
        }
        $this->_redirector->gotoSimple('list', 'user', 'session', array('session_id' => $this->_session->session_id));
    }
    
    public function assignAction()
    {
        $gridId = ($this->_session->session_id) ? "grid{$this->_session->session_id}" : 'grid';
        
        $soids = explode(',', $this->_request->getParam("postMassIds_{$gridId}"));
        $existingSoids = array();
        $collection = $this->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $this->_session->session_id));
        if (count($collection)) {
            $existingSoids = $collection->getList('position_id');
        }
        if (is_array($soids) && count($soids)) {
	        $soids = array_diff($soids, $existingSoids);
            $messages = array();
            $this->getService('AtSession')->addSoids($this->_session->session_id, $soids, $messages);
            
            $this->_flashMessenger->addMessage(_('Пользователи успешно добавлены'));

        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не отмечены участники')));
        }
        $this->_redirector->gotoSimple('list', 'user', 'session', array('session_id' => $this->_session->session_id));
    }
    
    public function exportZipAction()
	{
        $config = Zend_Registry::get('config');
        $reportsDir = $config->path->upload->reports;

        $sessionId = $this->_request->getParam("session_id");

	$soids = array();
	if (isset($_POST["postMassIds_grid{$sessionId}"])) {
            $soids = explode(",", $_POST["postMassIds_grid{$sessionId}"]);
        }

	$warningMmessage = _("Для выбранных участников отчеты отсутствуют, т.к. процесс оценки для них еще не завершен или сессия еще не закрыта");

	if(!count($soids)){
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не отмечены участники')));
        }
        else
        if (file_exists(APPLICATION_PATH.''.$reportsDir. DIRECTORY_SEPARATOR . $sessionId)) {
            if (count($soids)) {
                $sessionUsers = $this->getService('AtSessionUser')->fetchAllDependence('User', array(
                    'session_id = ?' => $sessionId,
                    'position_id IN (?)' => $soids,
                ));
            }

            if(count($sessionUsers)){
                $countFiles = 0;
                $zip = new ZipArchive();
                $res = $zip->open($fileZip = APPLICATION_PATH.''.$reportsDir . DIRECTORY_SEPARATOR . $sessionId . DIRECTORY_SEPARATOR . 'session.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
                $fileNameReport = APPLICATION_PATH.''.$reportsDir . DIRECTORY_SEPARATOR . $sessionId . DIRECTORY_SEPARATOR . 'readme.xls';
                $readmeData = array();
                if ($res) {
                    foreach($sessionUsers as $sessionUser){
                        $fileName = APPLICATION_PATH.''.$reportsDir . DIRECTORY_SEPARATOR . $sessionId . DIRECTORY_SEPARATOR . $sessionUser->session_user_id. '.pdf';
                        $filePseudoName = count($sessionUser->user) ? $sessionUser->user->current()->Login . '.pdf' : $sessionUser->session_user_id. '.pdf';
                       
                        if($bFileExist = file_exists($fileName)) {
                            $zip->addFile($fileName, $filePseudoName);
                            $countFiles++;
                        }
                        $readmeData[] = array($sessionUser->user[0]->getNameCyr(), _($bFileExist?'':'отчет отсутствует'));//#17288
                    }
                }
//#17288
                $this->export2Excel($readmeData, $fileNameReport, $title='Отчет о выгрузке');
                $zip->addFile($fileNameReport, basename($fileNameReport));
                $zip->close();

                if(!$countFiles) {
                    $this->_flashMessenger->addMessage(array(
                        'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _($warningMmessage),
                    ));
                    $this->_redirector->gotoSimple('list', 'user', 'session', array('session_id' => $this->_session->session_id));
                }
//        
                $this->_helper->SendFile(
                    $fileZip,
                    'application/zip',
                    array('filename' => sprintf('session_%s.zip', $sessionId))
                );
                exit();
            }    
        }
        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _($warningMmessage)));
        $this->_redirector->gotoSimple('list', 'user', 'session', array('session_id' => $this->_session->session_id));
    }
    
    public function changeStatusAction()
    {
        $gridId = ($this->_session->session_id) ? "grid{$this->_session->session_id}" : 'grid';
        
        $status = $this->_getParam('status');
        $soids = explode(',', $this->_request->getParam("postMassIds_{$gridId}"));
        if (is_array($soids) && count($soids)) {
            switch ($status) {
                case HM_At_Session_User_UserModel::STATUS_COMPLETED:
                    $sessionUsers = $this->getService('AtSessionUser')->fetchAllDependence('SessionEvents', array(
                        'position_id IN (?)' => $soids,
                        'session_id = ?' => $this->_session->session_id,
                    ));
                    
                    if (count($sessionUsers)) {
                        foreach ($sessionUsers as $sessionUser) {
                            $this->getService('Process')->goToComplete($sessionUser);
                        }
                    }
                    break;
            }

            $this->_flashMessenger->addMessage(_('Статус оценки пользователей успешно изменён'));

        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не отмечены участники')));
        }
        $this->_redirector->gotoSimple('list', 'user', 'session', array('session_id' => $this->_session->session_id));
    }

    public function updateEvents($events, $fio, $user_id)
    {
        if (!$this->getService('Acl')->isCurrentAllowed('mca:session:user:change-status')) return $events;

        $url = $this->view->url(array(
            'action' => 'list',
            'controller' => 'event',
            'module' => 'session',
            'gridmod' => 'ajax', // только так фильтр устанавливается; надо бы убрать этот костыль
            'usergrid' => $fio
        ));
        $title = _('Список анкет');
// Счетчик учитывал "необычную" форму (она коллективная, в отличие от остальных) для парных сравнений. Предположение - форма нужна для респондента, по привязывать ее к участнику неправильно, 
// тем не менее ее привязывыают к одному из участников, но "не совсем", оставляют пустую ссылку на чела - на нее и ориентируемся (ее вычитаем)
        return "<a href='{$url}' title='{$title}'>".($user_id ? $events : (max(0,$events-1)))."</a>";
//        return "<a href='{$url}' title='{$title}'>{$events}</a>";
    }

    public function updateStatus($status)
    {
        return HM_At_Session_User_UserModel::getStatus($status);
    }
    
    public function updateAssignStatus($status)
    {
        return ($status != '') ?  _('Да') : _('Нет');
    }
    
    public function updateActions($status, $actions) 
    {
        if ( $status == HM_At_Session_User_UserModel::STATUS_COMPLETED ) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            array_pop($tmp); // инд.отчет
            array_pop($tmp); // анализ
            return implode('<li>', $tmp);
        }
    }

    public function printWorkflow($sessionUserId)
    {
        if (in_array($this->_session->state, array(HM_At_Session_SessionModel::STATE_ACTUAL, HM_At_Session_SessionModel::STATE_CLOSED))) {
            if ($this->_sessionUsersCache === null) {
                $this->_sessionUsersCache = array();

                $session = $this->getService('AtSession')->getOne(
                        $this->getService('AtSession')->findMultiDependence(array(
                                'users'    => 'SessionUser',
                                'sessionEvents' => 'SessionEvents',
                                'programmEvent' => array('ProgrammEvent', 'ProgrammEventUser'),
                        ), $this->_session->session_id)
                );

                if (count($session->users)) {
                    foreach ($session->users as $sessionUser) {
                        $this->_sessionUsersCache[$sessionUser->session_user_id] = $sessionUser;
                    }
                }
            }
            if (intval($sessionUserId) > 0 && count($this->_sessionUsersCache) && array_key_exists($sessionUserId, $this->_sessionUsersCache)) {
                $model = $this->_sessionUsersCache[$sessionUserId];
                $this->getService('Process')->initProcess($model);
                return $this->view->workflowBulbs($model);
            }
        }
        return _('не начат');
    }    
    
    public function workflowAction()
    {
        $sessionUserId = $this->_getParam('index', 0);

        if(intval($sessionUserId) > 0){

            $model =  $this->getService('AtSessionUser')->find($sessionUserId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }
    
    public function skipEventAction()
    {
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($sessionUserId = $this->_getParam('session_user_id'))) {
            
            if (count($collection = $this->getService('AtSessionUser')->findDependence('User', $sessionUserId))) {
                $sessionUser = $collection->current();
                
                $processAbstract = $sessionUser->getProcess()->getProcessAbstract();
                if ($processAbstract->isStrict()) {
                    $this->getService('Process')->goToNextState($sessionUser);                
                } else {
                    $stateClass = HM_Process_Type_Programm_AssessmentModel::getStatePrefix() . $programmEventId;
                    $this->getService('Process')->setStateStatus($sessionUser, $stateClass, HM_State_Abstract::STATE_STATUS_PASSED);
                }     

                if ($sessionUser->user_id) {
                    $data['status'] = HM_Programm_Event_User_UserModel::STATUS_PASSED;
                    $this->getService('ProgrammEventUser')->updateWhere($data, array(
                        'programm_event_id = ?' => $programmEventId,
                        'user_id = ?' => $sessionUser->user_id,
                    ));                    
                }

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Мероприятие завершено')));
            }            
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось завершить данное мероприятие')));            
        }
        
        $this->_redirector->gotoSimple('list', 'user', 'session', array('session_id' => $this->_session->session_id));
    }
}
