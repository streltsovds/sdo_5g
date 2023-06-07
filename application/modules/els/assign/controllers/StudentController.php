<?php
class Assign_StudentController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    /**
     * Только слушателей курса
     */
    const FILTER_LISTENERS_COURSE = 0;
    /**
     * Все пользователи
     */
    const FILTER_ALL = 1;
    /**
     * Все слушатели
     */
    const FILTER_LISTENERS = 2;

    protected $_subjectId      = null;
    protected $_subject        = null;
    protected $_cache          = array();
    protected $_deanSubjectIds = false;
    protected $gridId          = '';
    /**@var  $_serviceSubject HM_Subject_SubjectService | null*/
    protected $_serviceSubject = null;
    protected $_hasErrors      = false;
    protected $_expiredSubjectsNames = array();
    protected $_cacheSubjectExpire   = array();
    protected $_cacheSubjectTitle    = array();
    protected $_periodRestrictionType;

    public function init()
    {
        if (!$this->isAjaxRequest()) {

            $this->_subjectId = $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));

            $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if ($this->_subject) {

                $this->initContext($this->_subject);

                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                ]);

                $this->view->setBackUrl($this->view->url([
                    'module' => 'subject',
                    'controller' => 'list',
                    'action' => 'index',
                    'base' => $this->_subject->base,
                ], null, true));
            }
        }

        parent::init();

        $courseId = (int) $this->_getParam('subject_id', 0);
        $this->gridId = ($courseId) ? "grid{$courseId}" : 'grid'; // ВАЖНО! это не $courseId, а скорее subjectId - id уч.курса, если мы находимся в панели управления;
    }

    public function graduateStudentsAction()
    {
        $subjectId = $this->_getParam('subject_id', 0);
        $ids = explode(',', $this->_getParam('postMassIds_'.$this->gridId, ''));

        $period = $this->_request->getParam('certificate_validity_period');
        if (is_array($period) && count($period)) $period = current($period);
        $period = (int) $period;
        if (empty($period)) $period = -1;

        $result = false;
        if (count($ids)) {
            foreach ($ids as $userId) {

                $this->getService('SubjectMark')->updateWhere(array(
                    'certificate_validity_period' => $period
                ), array(
                    'mid = ?' => intval($userId),
                    'cid = ?' => intval($subjectId)
                ));

                $result = $this->getService('Subject')->assignGraduated($subjectId, $userId, null, $period);
            }
        }
        if ($result) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Слушатели успешно переведены в прошедшие обучение')
            ));
        }

        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }
    
    public function indexAction()
    {
        $courseId = (int) $this->_getParam('subject_id', 0);
        $switcher = $this->getSwitcherSetOrder($courseId, 'fio_ASC', 'notempty DESC');
        try {
            $this->dataGrid = $courseId ?
                new HM_Assign_DataGrid_AssignStudentByCourseDataGrid($this->view, [$switcher], ['courseId' => $courseId]) :
                new HM_Assign_DataGrid_AssignStudentDataGrid(        $this->view, [$switcher]);

            $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

            Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
        } catch (Zend_Exception $e) {}
    }

    public function reAssignOnSessionAction()
    {
        $subject_id = $this->_getParam('subject_id', 0);
        if ($subject_id == 0) {
            return;
        }
        $users_ids = $this->_getParam('postMassIds_grid'.$subject_id, '');
        $users_ids = explode(',', $users_ids);
        $sessions_ids = $this->_getParam('sessionsIds', array());
        $sessions_id = $sessions_ids[0];
        if (count($users_ids) && count($sessions_ids)) {
            $subjectService = $this->getService('Subject');
            foreach ($users_ids as $user_id) {
                $subjectService->unassignStudent($subject_id, $user_id);
                $subjectService->assignUser($sessions_id, $user_id);
            }
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Слушатели успешно переназначены.')
            ));
        }
        $this->_redirector->gotoSimple('index', 'list', 'subject', array('base' => '2'));
    }


 
    public function assignAction()
    {
        if ($this->_serviceSubject === null) $this->_serviceSubject = $this->getService('Subject');

        $subjectId     = (int) $this->_getParam('subject_id', 0);
        $gridId        = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassField = 'postMassIds_' . $gridId;
        $postMassIds   = $this->checkPostIds($this->_getParam($postMassField, ''));
        $courseIds     = $this->checkPostIds($this->_getParam('courseId', array(0)));
        $errors        = false;

        $grdUsers  = $this->_getGraduatedUsers($postMassIds, $courseIds);

        if (count($grdUsers) && !$this->_getParam('agreed', false)) {   // Проверка на повторное назначение
            $usersName = array();
            $users     = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', array_keys($grdUsers)));
            if (count($users)) foreach ($users as $user) $usersName[$user->MID] = $user->getName();

            asort($usersName);

            $formCourseIds = implode(',', $courseIds);
            $filteredUsers = implode(',', array_diff($postMassIds, array_keys($grdUsers)));
            $this->view->usersName     = $usersName;
            $this->view->userList      = $grdUsers;
            $this->view->postMassIds   = $postMassIds;
            $this->view->postMassField = $postMassField;
            $this->view->courseId      = $formCourseIds;
            $this->view->filteredUsers = $filteredUsers;

            $agreedForm  = new HM_Form_Agreed();
            $agreedForm->init();

            $this->view->form = $agreedForm;
        } else {
            // Если пользователь согласен с изменениями
            // или обучающихся ранее пользователей не найдено,
            // работа в обычном режиме.

            if (/*((count($courseIds) == 1) && empty($courseIds[0])) || */!count($postMassIds)) {
                $this->_flashMessenger->addMessage ( _ ( 'Пожалуйста выберите пользователей и укажите курс' ) );
            }

            $count = count($postMassIds);
            foreach ($courseIds as $courseId) {
                if (!$courseId) {
                    $count = 0;
                    continue;
                }
                if (is_array($courseId) && count($courseId) == 1) $courseId = $courseId[0];
                if (!$this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $courseId)) {
//                    $errors = true;
//                    $this->_flashMessenger->addMessage (_('Нет прав на назначение на этот курс'));
                    continue;
                }

                if (count($postMassIds)) {
                    $errors=false;
                    foreach ($postMassIds as $id) {
                        $id = (int) $id;
                        if (method_exists($this, '_preAssign')) {
                            $return = $this->_preAssign($id, $courseId);

                            if ($return === self::RETCODE_DOACTION_END_ITERATION) { // Константы кодов ошибок с описаниями находятся в начале класс
                                $errors = true;
                                continue;
                            }
                            elseif ($return === self::RETCODE_DOACTION_END_LOOP) {
                                $errors = true;
                                break;
                            }
                        }

                        $this->_assign($id, $courseId);
                        $count --;

                        if (method_exists($this, '_postAssign')) {
                            $this->_postAssign($id, $courseId);
                        }
                    }
                }
            }

            if ($errors == false) {
                if (($count == 0)) {
                    $message = count($postMassIds) ? _('Пользователи успешно назначены') : false;
                } elseif (count($postMassIds) == $count) {
                    $message = _('Пользователи не назначены');
                } else {
                    $message = _('Некоторые пользователи успешно назначены');
                }
                if ($message) $this->_flashMessenger->addMessage($message);
            } else {
                $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли ошибки'));
            }
            
            if (method_exists($this, '_finishAssign')) {
                $this->_finishAssign();
            }

            $messenger = $this->getService('Messenger');
            $messenger->sendAllFromChannels();

            $default       = new Zend_Session_Namespace('default');
            $persistentAll = $default->grid['assign-student-index'][$gridId]['all'];
            $persistentAll = isset($persistentAll) ? $persistentAll : null;
            $this->_redirector->gotoSimple('index', null, null, array(
                    'subject_id' => ($subjectId ? $subjectId : null),
                    //'all'        => !$this->_getParam('all', $persistentAll)
                )
            );

            $agreedForm->addElement(
                'hidden',
                'all_users',
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => $postMassIds
                )
            );

            $agreedForm->addElement(
                'hidden',
                'courseId',
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => implode(',', $courseIds)
                )
            );

            $agreedForm->addElement(
                'hidden',
                'filtered_users',
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => implode(',', array_diff(explode(',',$postMassIds), array_keys($grdUsers)))
                )
            );

            $agreedForm -> addElement(
                'hidden',
                $postMassField,
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => ''
                )
            );

            $agreedForm->addElement(
                'submit',
                'filter_submit',
                array(
                    'Label' => _('Продолжить назначение, исключив указанных пользователей')
                )
            );

            $agreedForm->addElement(
                'submit',
                'all_submit',
                array(
                    'Label' => _('Назначить всех, включая вышеуказанных')
                )
            );

            $agreedForm->addDisplayGroup(
                array(
                    'agreed',
                    'all_users',
                    'filtered_users',
                    $postMassField,
                    'all_submit',
                    'filter_submit'
                ),
                'agreedGroup',
                array('legend' => 'Действия')
            );
            $agreedForm->init();
            $this->view->usersName     = $usersName;
            $this->view->form          = $agreedForm;
            $this->view->postMassField = $postMassField;
            $this->view->userList      = $grdUsers;

        }
    }

    /**
     * Функция возвращает информацию о пользователях, которых пытаются назначить слушателями,
     * в случае, если они уже проходили обучение на каких-либо из выбранных тренингов
     * @param array $userIds - ИД пользователей для проверки
     * @param array $courseIds - ИД тренингов и сессий для проверки
     * @return array
     */
    private function _getGraduatedUsers($userIds, $courseIds)
    {
        $result    = array();
        $userIds   = (array) $userIds;
        $userIds   = array_map('intval', $userIds);
        $courseIds = (array) $courseIds;
        $courseIds = array_map('intval', $courseIds);

        $subjects  = $this->_serviceSubject->fetchAllDependence('Graduated', $this->quoteInto(array('subid IN (?)', ' AND base_id IN (?)'), array($courseIds, '0')));

        if (!count($subjects)) return $result;

        $bases     = $this->_serviceSubject->fetchAllDependence('Graduated', $this->quoteInto('subid IN (?)', array_map('intval', array_unique($subjects->getList('base_id')))));

        // if ( count($bases) ) {
        //     $baseSessions = $this->_serviceSubject->fetchAllDependence('Graduated', $this->quoteInto('base_id IN (?)', array_map('intval', array_unique($bases->getList('subid')))));
        // } else {
        //     $baseSessions = $this->_serviceSubject->fetchAll(array('subid = ?' => -1)); // empty collection
        // }

        $subjectsName = array();
        if (count($subjects)) {
            $subjectsName = $subjects->getList('subid','name');
        }
        if (count($bases)) {
            $subjectsName = $subjectsName + $bases->getList('subid','name');
        }
        // if (count($baseSessions)) {
        //     $subjectsName = $subjectsName + $baseSessions->getList('subid','name');
        // }

        $this->_graduatedDataProcess($result, $subjects, $userIds, $subjectsName);
        if ( count($bases) )        $this->_graduatedDataProcess($result, $bases, $userIds, $subjectsName);
        // if ( count($baseSessions) ) $this->_graduatedDataProcess($result, $baseSessions, $userIds, $subjectsName);

        return $result;
    }

    private function _graduatedDataProcess(&$result, $subjects, $userIds, $subjectsName)
    {
        foreach ($subjects as $subject) {
            if (isset($subject->graduated)) {
                if (count($subject->graduated)) {
                    foreach ($subject->graduated as $graduated) {
                        if ( in_array($graduated->MID, $userIds) && !isset($result[$graduated->MID][$subject->subid]) ) {
                            $data = array(
                                'MID'     => $graduated->MID,
                                'endDate' => $graduated->end,
                            );

                            if ($subject->base_id) {
                                $data['training'] = $subjectsName[$subject->base_id];
                                $data['session']  = $subject->name;
                            } else {
                                $data['training'] = $subject->name;
                            }
                            $result[$graduated->MID][$subject->subid] = $data;
                        }
                    }
                }
            }
        }
    }

    protected function _preAssign($personId, $courseId)
    {
        if (isset($this->_cacheSubjectExpire[$courseId])) { // Если имеется результат в кеше
            return $this->_cacheSubjectExpire[$courseId] ? self::RETCODE_DOACTION_END_ITERATION : self::RETCODE_DOACTION_OK;
        }

        $subject = $this->getOne($this->_serviceSubject->find($courseId));

        if (!$subject) {
            return self::RETCODE_DOACTION_END_LOOP;
        }
        elseif ($subject->isExpired()) {
            $this->_hasErrors = true;
            $this->_cacheSubjectExpire[$courseId] = true;
            $this->_expiredSubjectsNames[] = $subject->getName();
            return self::RETCODE_DOACTION_END_ITERATION;
        }

        $this->_cacheSubjectExpire[$courseId] = false;
        return self::RETCODE_DOACTION_OK;
    }

    protected function _postAssign($personId, $courseId)
    {
        return true; //#10357
    }

    protected function _finishAssign()
    {
        if ($this->_hasErrors) {
            $this->_flashMessenger->clearCurrentMessages();
            $this->_flashMessenger->addMessage(array(
                'type'        => HM_Notification_NotificationModel::TYPE_ERROR,
                'message'    => _('Срок действия следующих курсов истёк: '.implode(', ', $this->_expiredSubjectsNames))
            ));
        }

        $this->getService('Eclass')->subjectWebinarsReassign(
            (int) $this->_getParam('subject_id', 0)
        );
    }

    protected function _finishUnassign()
    {

        $this->getService('Eclass')->subjectWebinarsReassign(
            (int) $this->_getParam('subject_id', 0)
        );
    }

    protected function _assign($personId, $subjectId)
    {
        return $this->getService('Subject')->assignUser($subjectId, $personId);
    }

    protected function _unassign($personId, $subjectId)
    {
        return $this->getService('Subject')->unassignStudent($subjectId, $personId);
    }

    public function assignProgrammAction()
    {
        $programmIds = $this->_getParam('programmId', array());

        $subjectId = (int) $this->_getParam('subject_id',0);
        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $ids = explode(',', $this->_getParam('postMassIds_'.$gridId, ''));

        if (count($programmIds)) {
            if (count($ids)) {
                $this->getService('Lesson')->beginProctoringTransaction();
                foreach($ids as $id) {
                    foreach($programmIds as $programmId) {
                        $this->getService('Programm')->assignToUser($id, $programmId);
                    }
                }
                $this->getService('Lesson')->commitProctoringTransaction();

                $this->_flashMessenger->addMessage(_('Слушатели успешно назначены'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один слушатель')
                ));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбрана ни одна программа')
            ));
        }

        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    public function unassignProgrammAction()
    {
        $programmIds = $this->_getParam('programmId', array());

        $subjectId = (int) $this->_getParam('subject_id',0);
        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $ids = explode(',', $this->_getParam('postMassIds_'.$gridId, ''));

        if (count($programmIds)) {
            if (count($ids)) {
                foreach ($ids as $id) {
                    foreach ($programmIds as $programmId) {
                        $this->getService('ProgrammUser')->unassign($id, $programmId);
                    }
                }

                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один слушатель')
                ));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбрана ни одна программа')
            ));
        }

        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

//        $this->getService('Lesson')->beginProctoringTransaction();
//        $this->getService('Lesson')->commitProctoringTransaction();
// И хде эти назначения???
    // работает при назначении курсов через оргструктуру
    public function doSoidsAction()
    {
        $do = '_' . $this->_getParam('do', 'assign');
        $subjectIds = $this->_getParam('subjectId', array());
        $soids = explode(',', $this->_getParam('postMassIds_grid', ''));
        $soids = $this->getService('Orgstructure')->getDescendansForMultipleSoids($soids);
        
        if (!empty($soids)) {
            $positions = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto('soid IN (?)', $soids));
        } else {
            $positions = array();
        }

        if (count($subjectIds) && method_exists($this, $do)) {
            
            $usersExists = false;
            
            if (count($positions)) {
                foreach ($positions as $position) {
                    if (!$position->mid) {
                        continue;
                    }
                    
                    $usersExists = true;
                    
                    foreach ($subjectIds as $subjectId) {
                        $this->$do($position->mid, $subjectId);
                        if (($do == '_unassign') && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
                        ))) {
                            $currentUser = $this->getService('User')->getCurrentUser();
                            $claimant = $this->getService('Claimant')->fetchAll(array(
                                'MID = ?' => $position->mid,
                                'CID = ?' => $subjectId,
                            ));
                            if (count($claimant)) {
                                $this->getService('Claimant')->updateWhere(array(
                                    'status' => HM_Role_ClaimantModel::STATUS_REJECTED,
                                    'changing_date' => date('Y-m-d'),
                                    'comments' => $currentUser->getName() . ': ' . _('отмена заявки')
                                ), array(
                                    'MID = ?' => $position->mid,
                                    'CID = ?' => $subjectId,
                                ));
                            }
                        }
                    }
                }

            }
            
            if ($usersExists) {
                switch ($do) {
                    case '_unassign':
                        $message = _('Назначения успешно удалены');
                        break;

                    case '_assign':
                    default:
                        $message = _('Курсы успешно назначены');
                        break;
                }

                $this->_flashMessenger->addMessage($message);
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один пользователь')
                ));
            }
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбран ни один курс')
            ));
        }

        $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
    }

    public function unassignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id',0);
        $courseIds = $this->checkPostIds($this->_getParam('unCourseId', $this->_getParam('courseId',array(0))));

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->checkPostIds($this->_getParam('postMassIds_' . $gridId, ''));

        if ((((count($courseIds) == 1)) && empty($courseIds[0])) || !count($postMassIds)) {
            $this->_flashMessenger->addMessage (_('Пожалуйста выберите пользователей и укажите курс'));
        }

        $count = count($postMassIds);
        foreach ($courseIds as $courseId) {
//            if (is_array($courseId) && count($courseId) == 1) $courseId = $courseId[0];
            if (!$this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $courseId)) {
//                $this->_flashMessenger->addMessage (_('Нет прав на удаление назначений в курсе'));
                continue;
            }

            if (count($postMassIds)) {
                foreach($postMassIds as $id) {
                    if (method_exists($this, '_preUnassign')) {
                        $this->_preUnassign($id, $courseId);
                    }
                    $this->_unassign($id, $courseId);
                    $count --;
                    if (method_exists($this, '_postUnassign')) {
                        $this->_postUnassign($id, $courseId);
                    }
                }
            }
        }

        if (($count == 0)) {
            $message = count($postMassIds) ? _('Назначения успешно удалены') : false;
        } elseif (count($postMassIds) == $count) {
            $message = _('Назначения не были удалены');
        } else {
            $message = _('Некоторые назначения успешно назначены');
        }
        if ($message) $this->_flashMessenger->addMessage($message);
        $this->_flashMessenger->addMessage($message);

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    protected function _preUnassign($personId, $courseId){}
    protected function _postUnassign($personId, $courseId){}
}