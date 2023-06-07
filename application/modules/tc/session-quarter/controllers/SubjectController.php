<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 14:02
 */

class SessionQuarter_SubjectController extends HM_Controller_Action {

    /** @var HM_Tc_Session_Department_DepartmentService $_defaultService */
    protected $_defaultService;

    protected $_sessionQuarterId;
    protected $_sessionQuarter;

    public function init()
    {
        $this->_defaultService = $this->getService('TcSessionDepartment');
        $this->_sessionQuarterId = $this->_getParam('session_quarter_id', 0);
        $this->_sessionQuarter = $this->getOne(
            $this->getService('TcSessionQuarter')->find($this->_sessionQuarterId)
        );
        if ($this->_sessionQuarter->session_quarter_id) {
            $this->view->setExtended(
                array(
                    'subjectName' => 'TcSessionQuarter',
                    'subjectId' => $this->_sessionQuarterId,
                    'subjectIdParamName' => 'session_quarter_id',
                    'subjectIdFieldName' => 'session_quarter_id',
                    'subject' => $this->_sessionQuarter
                )
            );
        }
        parent::init();
    }


    public function assignSessionsAction()
    {
        $this->view->setHeader(_('Назначение учебных сессий пользователям'));
        $form = new HM_Form_Sessions();

        $subjects = $users = $applications2subjects = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $collection = $this->getService('TcApplication')->fetchAllDependence(array('Subject', 'Users'), array('application_id IN (?)' => $ids));
                foreach ($collection as $application) {
                    if (count($application->subjects)) {
                        $subject = $application->subjects->current();
                        if ($subject->base_id) {
                            $subject = $this->getService('Subject')->findOne(array('sub_id = ?', $subject->base_id));
                        }

                        $subjects[$subject->subid] = $subject;
                        if (!isset($applications2subjects[$subject->subid])) {
                            $applications2subjects[$subject->subid] = array();
                        }
                        $applications2subjects[$subject->subid][] = $application->application_id;
                        if (count($application->user)) $users[$application->user_id] = $application->user->current();
                     }
                }
            }
        }

        if (!count($subjects)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отмеченным пользователям не назначены курсы.')
            ));
            $this->_redirectToIndex();
        }

        if ($this->_getParam('assignsessions',0)) {

            $post = $this->_processPost($_POST);
            if (is_array($post['subject'])) {

                $subjectIds = array_keys($post['subject']);
                $collection = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $subjectIds));
                if (count($collection)) {
                    foreach ($collection as $subject) {

                        $values = $subject->getValues();
                        $sessionValues = $post['subject'][$subject->subid];

                        $session = null;
                        if ($sessionValues['sessionId']) {
                            $session = $this->getService('Subject')->getOne($this->getService('Subject')->find($sessionValues['sessionId']));
                        } elseif ($sessionValues['begin'] && $sessionValues['end']) {
                            $values['type'] = HM_Subject_SubjectModel::TYPE_FULLTIME;
                            $values['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
                            $dateBegin = new HM_Date($sessionValues['begin']);
                            $dateEnd   = new HM_Date($sessionValues['end']);
                            $values['begin'] = $dateBegin->get('Y-MM-dd');
                            $values['end']   = $dateEnd->get('Y-MM-dd');
                            $values['name'] = sprintf('%s (сессия %s)', $values['name'], $sessionValues['begin']);
                            $values['base'] = HM_Subject_SubjectModel::BASETYPE_SESSION;
                            $values['base_id'] = $values['subid'];
                            if ($subject->is_labor_safety) {
                                $values['is_labor_safety'] = HM_Subject_SubjectModel::BUILTIN_COURSE_LABOR_SAFETY;
                                $values['type'] = HM_Subject_SubjectModel::TYPE_DISTANCE;
                            }
                            unset($values['subid']);
                            $values['created'] = date('Y-m-d H:i:s');
                            $session = $this->getService('Subject')->insert($values);
                            $this->getService('Subject')->linkRoom($session->subid, $sessionValues['roomId']);
                        } else {
                            $this->_flashMessenger->addMessage(array(
                                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                'message' => _('Произошла ошибка при назначении учебных сессий')
                            ));
                            $this->_redirector->gotoSimple('index', 'student', null, array('session_quarter_id' => $this->_sessionQuarterId));
                        }

                        foreach ($users as $user) {
                            if ($session) {
                                $recordExists = $this->getService('Student')->fetchAll(
                                    $this->getService('Student')->quoteInto(
                                        array(
                                            ' MID = ? AND ',
                                            ' CID = ? AND ',
                                            ' application_id IN (?)'
                                        ),
                                        array(
                                            $user->MID,
                                            $session->subid,
                                            $applications2subjects[$subject->subid],
                                        )
                                    )
                                );

                                if (!count($recordExists)) {
                                    $this->getService('Student')->updateWhere(array(
                                        'CID' => $session->subid,
                                    ), array(
                                        'MID = ?' => $user->MID,
                                        'application_id IN (?)' => $applications2subjects[$subject->subid],
                                    ));
                                }

// в заявке остаётся id курса
//                            $this->getService('TcApplication')->updateWhere(array(
//                                'subject_id' => $session->subid,
//                            ), array(
//                                'application_id IN (?)' => $applications2subjects[$subject->subid],
//                            ));
                            }
                        }

                    }

                    // автоперевод на след.этап
                    if ($this->_sessionQuarter) {
                        $processService = $this->getService('Process');
                        $sessionState = $processService->getCurrentState($this->_sessionQuarter);
                        if ($sessionState instanceof HM_Tc_SessionQuarter_State_Analysis) {
                            $processService->goToNextState($this->_sessionQuarter);
                        }
                    }

                    $radioNotifications = $this->_getParam('radio_notifications', 0);

                    switch ($radioNotifications) {
                        case 0:
                            $url = $this->view->url(
                                    array(
                                        'module'       => 'session-quarter',
                                        'controller'   => 'subject',
                                        'action'       => 'send-notifications'
                                    )
                                ) . "/?postMassIds_grid={$postMassIds}&sendnotifications=1";



                            $this->_redirector->gotoUrl($url, array('prependBase' => false));
                            break;
                        case 1:
                            $url = $this->view->url(
                                    array(
                                        'module'       => 'session-quarter',
                                        'controller'   => 'subject',
                                        'action'       => 'send-notifications'
                                    )
                                ) . "/?postMassIds_grid={$postMassIds}&notification=custom";

                            $this->_redirector->gotoUrl($url, array('prependBase' => false));
                            break;
                        case 2:
                            $this->_flashMessenger->addMessage(array(
                                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                                'message' => _('Уведомления о назначениях не отправлялись')
                            ));
                            break;
                    }
                }
            }

            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Назначения успешно выполнены')
            ));
            $this->_redirectToIndex();
        } else {

            $form->initWithData($subjects, $postMassIds);
            $this->view->form = $form;
            $this->view->users = $users;
        }
    }


    public function sendNotificationsAction()
    {
        $this->view->setHeader(_('Отправка уведомлений пользователям'));
        $form = new HM_Form_Notifications();
        $simple = $this->_getParam('simple', 0);

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => $simple ? HM_Messenger::TEMPLATE_PRIVATE : HM_Messenger::TEMPLATE_LEARNING_ASSIGNED
        )));

        if (!$notice) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствует шаблон уведомления')
            ));
            $this->_redirectToIndex();
        }

        $subjects =
        $users =
        $subjectUsers =
        $providers =
        $applications2subjects = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $collection = $this->getService('TcApplication')->fetchAllDependence(array('Subject', 'Users', 'TcProviders'), array('application_id IN (?)' => $ids));
                foreach ($collection as $application) {
                    $student = $this->getService('Student')->getOne(
                        $this->getService('Student')->fetchAll(
                            $this->getService('Student')->quoteInto(
                                array(
                                    'MID = ? AND ',
                                    'application_id = ? '
                                ),
                                array(
                                    $application->user_id,
                                    $application->application_id
                                )
                            )
                        )
                    );
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($student->CID)
                    );
                    $subjects[$subject->subid] = $subject;
                    if (!isset($applications2subjects[$subject->subid])) {
                        $applications2subjects[$subject->subid] = array();
                    }
                    $applications2subjects[$subject->subid][] = $application->application_id;
                    $subjectUsers[$subject->subid][] = $application->user_id;
                    if (count($application->user)) $users[$application->user_id] = $application->user->current();
                    if (count($application->providers)) $providers[$subject->subid] = $application->providers->current();
                }
            }
        }

        if (!count($subjects)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отмеченным пользователям не назначены учебные сессии')
            ));
            $this->_redirectToIndex();
        }

        $fullTimeSubjectsCount = 0;

        if ($this->_getParam('sendnotifications',0) && $this->_getParam('notification', 'default') == 'custom') {
            $request = $this->getRequest();
            if ($form->isValid($request->getParams())) {
                $notice->title = $form->getValue('title');
                $notice->message = $form->getValue('message');

                /** @var HM_Messenger $messenger */
                $messenger = $this->getService('Messenger');
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();
                /*
                [COURSE] - название курса с сылкой на него
                [BEGIN] дата начала курса в человеческом формате dd.mm.YYYY
                [END] дата окончания курса в человеческом формате dd.mm.YYYY
                [INFO] - атрибут "информация для пользователей" объекта "Провайдер обучения".
                */
                foreach ($subjects as $subject) {
                    if ($subject->type == HM_Subject_SubjectModel::TYPE_FULLTIME) {
                        $fullTimeSubjectsCount ++;
                    }
                    $provider = $providers[$subject->subid];
                    foreach ($subjectUsers[$subject->subid] as $userId) {
                        $messenger->setOptions(
                            $subject->base_id ? HM_Messenger::TEMPLATE_LEARNING_ASSIGNED : HM_Messenger::TEMPLATE_ASSIGN_SUBJECT,
                            [
                                'user_id' => $userId,
                                'subject_id' => $subject->subid,
                                'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_STUDENT],
                                'COURSE' => $subject->name,
                                'BEGIN' => $subject->period == HM_Subject_SubjectModel::PERIOD_FREE ? _('Без ограничений') : date('d-m-Y', strtotime($subject->begin)),
                                'END' => $subject->period == HM_Subject_SubjectModel::PERIOD_FREE ? _('Без ограничений') : date('d-m-Y', strtotime($subject->end)),
                                'INFO' => $provider->information ? $provider->information : '',
                            ]
                        );

                        $messenger->forceTemplate($notice);
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
                        $this->setNotified($userId, $subject->subid);
                    }
                }

                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => $fullTimeSubjectsCount ? _('Уведомления успешно отправлены') : _('Уведомления успешно отправлены, кроме уведомлений о внешних курсах')
                ));

                $this->_redirectToIndex();
            }
        } elseif ($this->_getParam('sendnotifications',0) && $this->_getParam('notification', 'default') == 'default') {
            /** @var HM_Messenger $messenger */
            $messenger = $this->getService('Messenger');

            $roles = HM_Role_Abstract_RoleModel::getBasicRoles();
            foreach ($subjects as $subject) {
                if ($subject->type == HM_Subject_SubjectModel::TYPE_FULLTIME) {
                    $fullTimeSubjectsCount ++;
                }
                $provider = $providers[$subject->subid];
                foreach ($subjectUsers[$subject->subid] as $userId) {
                    if ($simple) {
                        $messenger->setOptions(
                            HM_Messenger::TEMPLATE_PRIVATE,
                            [
                                'SUBJECT' => $this->getRequest()->getParam('title'),
                                'TEXT' => $this->getRequest()->getParam('message')
                            ]
//                        ,'subject',
//                        $subject->subid
                        );

                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
                    } else {
                        $messenger->setOptions(
                            $subject->base_id ? HM_Messenger::TEMPLATE_LEARNING_ASSIGNED : HM_Messenger::TEMPLATE_ASSIGN_SUBJECT,
                            [
                                'user_id' => $userId,
                                'subject_id' => $subject->subid,
                                'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_STUDENT],
                                'COURSE' => $subject->name,
                                'BEGIN' => date('d-m-Y', strtotime($subject->begin)),
                                'END' => date('d-m-Y', strtotime($subject->end)),
                                'INFO' => $provider->information ? $provider->information : '',
                            ]
                        );

                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
                        $this->setNotified($userId, $subject->subid);
                    }
                }
            }

            $add = $fullTimeSubjectsCount ? '' : ', '._('кроме уведомлений о внешних курсах');
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => $simple ? _('Уведомления успешно отправлены').$add : _('Типовые уведомления успешно отправлены').$add
            ));

            $this->_redirectToIndex();
        } else {
            $form->initWithData($postMassIds);
            $form->populate(array(
                'title' => $simple ? '' : $notice->title,
                'message' => $simple ? '' : $notice->message,
            ));
        }
        $this->view->form = $form;
        $this->view->users = $users;
    }

    protected function setNotified($mid, $cid)
    {
        $this->getService('Student')->updateWhere(
            array('notified' => 1),
            array(
                'MID = ?' => $mid,
                'CID = ?' => $cid,
            )
        );
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'student', null, array('session_quarter_id' => $this->_sessionQuarterId));

    }


    protected function _processPost($post)
    {
        $newPost = array();
        foreach ($post as $key => $value) {
            $parts = explode('_', $key);
            if (count($parts) == 3) {
                if (!is_array($newPost[$parts[0]])) $newPost[$parts[0]] = array();
                if (!is_array($newPost[$parts[0]][$parts[1]])) $newPost[$parts[0]][$parts[1]] = array();
                $newPost[$parts[0]][$parts[1]][$parts[2]] = $value;
            }
        }
        return $newPost;
    }

    public function indexAction()
    {
        $gridId = 'grid';
        if (!$this->_request->getParam("order{$gridId}")) {
            $this->_request->setParam("order{$gridId}", 'session_department_id_ASC');
        }

        $view = $this->view;

        $grid = HM_SessionQuarter_Grid_SubjectGrid::create(array(
            'sessionQuarterId' => $this->_sessionQuarterId,
            'controller' => $this,
        ));

        $select = $this->getService('Subject')->getSelect();
        $select->from(array(
                    's' => 'subjects'
                ),
                array(
                    'subject_id'=>'s.subid',
                    'subject'=>'s.name',
                    'courses' => new Zend_Db_Expr('GROUP_CONCAT(session.subid)'),
                    'count_total' => new Zend_Db_Expr('COUNT(DISTINCT tca.application_id)'),
                    'count_students' => new Zend_Db_Expr('COUNT(st.MID)'),
                    'count_graduated' => new Zend_Db_Expr('COUNT(g.MID)'),
                )
            )
            ->joinInner(array('tca' => 'tc_applications'), 'tca.subject_id = s.subid', array())
            ->joinLeft(array('session' => 'subjects'), 's.subid = session.base_id', array())
            ->joinLeft(array('st' => 'Students'), 'st.CID = session.subid AND st.application_id = tca.application_id', array())
            ->joinLeft(array('g' => 'graduated'), 'g.CID = session.subid AND g.application_id = tca.application_id', array())
            ->where('s.base != ?', HM_Subject_SubjectModel::BASETYPE_SESSION)
            ->where('tca.session_quarter_id = ?', $this->_sessionQuarterId)
            ->group(array(
                's.subid',
                's.name',
            ));
        ;

        $view->assign(array(
            'grid' => $grid->init($select)
        ));
    }



}
